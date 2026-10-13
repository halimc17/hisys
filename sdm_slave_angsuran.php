<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}
$arrtipeangsuran=array('angsuranjangkawaktu'=>'Angsuran Jangka Waktu','angsurannominal'=>'Angsuran Nominal');

$path	= "fileupload/angsurankaryawan/";

#akses unit sesuai detail unit user (sama seperti halaman lain): setiap aksi per transaksi/karyawan dicek ke lokasi tugas karyawannya
$unitAkses = getOrgDetail(1);

#format parameter dicek sebelum dipakai di query
foreach (array('notransaksi', 'nomotopup') as $kunciParam) {
	if (isset($param[$kunciParam]) && $param[$kunciParam] !== '' && (!is_string($param[$kunciParam]) || !preg_match('/^[A-Za-z0-9\/_\-]{1,50}$/', $param[$kunciParam]))) {
		exit("Warning : Parameter " . $kunciParam . " tidak valid.");
	}
}
foreach (array('karyawanid', 'jenisangsuran') as $kunciParam) {
	if (isset($param[$kunciParam]) && $param[$kunciParam] !== '' && (!is_string($param[$kunciParam]) || !preg_match('/^[0-9]{1,10}$/', $param[$kunciParam]))) {
		exit("Warning : Parameter " . $kunciParam . " tidak valid.");
	}
}
foreach (array('bulandari', 'bulansampai', 'bulanmulaiperubahan', 'bulansampaitopup', 'periodeakhir') as $kunciParam) {
	if (isset($param[$kunciParam]) && $param[$kunciParam] !== '' && (!is_string($param[$kunciParam]) || !preg_match('/^[0-9]{4}-[0-9]{2}$/', $param[$kunciParam]))) {
		exit("Warning : Parameter " . $kunciParam . " tidak valid.");
	}
}
if (isset($param['tipeangsuran']) && $param['tipeangsuran'] !== '' && (!is_string($param['tipeangsuran']) || !isset($arrtipeangsuran[$param['tipeangsuran']]))) {
	exit("Warning : Tipe angsuran tidak valid.");
}

#lokasi tugas karyawan harus termasuk unit yang boleh diakses user
function cekAksesKaryawan($karyawanid)
{
	global $owlPDO, $dbname, $unitAkses;
	$stmt = $owlPDO->prepare("select lokasitugas from " . $dbname . ".datakaryawan where karyawanid=?");
	$stmt->execute(array($karyawanid));
	$lokasi = $stmt->fetchColumn();
	if ($lokasi === false || !isset($unitAkses[$lokasi])) {
		exit("Warning : Karyawan tidak sesuai hak akses unit Anda.");
	}
	return $lokasi;
}

#baris angsuran berdasarkan nomor transaksi, sekaligus cek akses unit karyawannya
function barisAngsuran($notransaksi)
{
	global $owlPDO, $dbname;
	$stmt = $owlPDO->prepare("select * from " . $dbname . ".sdm_angsuran where notransaksi=?");
	$stmt->execute(array($notransaksi));
	$baris = $stmt->fetch(PDO::FETCH_ASSOC);
	if (!$baris) {
		exit("Warning : Data angsuran tidak ditemukan.");
	}
	cekAksesKaryawan($baris['karyawanid']);
	return $baris;
}

#bulan awal angsuran tidak boleh di periode gaji yang sudah ditutup (cicilan bulan itu tidak akan pernah terpotong)
function cekBulanTutup($lokasitugas, $bulanList)
{
	global $owlPDO, $dbname;
	$q = array();
	foreach ($bulanList as $bulan) {
		$q[$bulan] = $owlPDO->quote($bulan);
	}
	if (count($q) == 0) {
		return;
	}
	$res = fetchdata("select distinct periode from " . $dbname . ".sdm_5periodegaji where kodeorg=" . $owlPDO->quote($lokasitugas) . " and sudahproses='1' and periode in (" . implode(',', $q) . ") order by periode");
	if (count($res) > 0) {
		exit("Warning : Periode gaji " . $res[0]['periode'] . " unit " . $lokasitugas . " sudah ditutup, bulan awal angsuran tidak boleh di periode yang sudah tutup.");
	}
}

#detail per bulan dari parameter (bulanN, rpbulandetN), bulan dan nilai divalidasi
function detailAngsuranDariParam($param)
{
	$jumlah = isset($param['totalcountdetail']) ? $param['totalcountdetail'] : '';
	if (!is_string($jumlah) || !preg_match('/^[0-9]{1,3}$/', $jumlah) || intval($jumlah) < 1) {
		exit("Warning : Data detail masih kosong.");
	}
	$detail = array();
	for ($i = 1; $i <= intval($jumlah); $i++) {
		$bulan = isset($param['bulan' . $i]) ? $param['bulan' . $i] : '';
		$rp = isset($param['rpbulandet' . $i]) ? str_replace(',', '', $param['rpbulandet' . $i]) : '';
		if (!is_string($bulan) || !preg_match('/^[0-9]{4}-[0-9]{2}$/', $bulan) || !is_numeric($rp) || $rp < 0) {
			exit("Warning : Detail angsuran ke-" . $i . " tidak valid.");
		}
		if (!isset($detail[$bulan])) {
			$detail[$bulan] = 0;
		}
		$detail[$bulan] += doubleval($rp);
	}
	return $detail;
}

#komponen angsuran (nama jenis dan inisial nomor) per PT: komponengaji yang sama (120) dipakai semua PT, jadi harus dipilih per PT
function komponenAngsuranPT($pt, $komponengaji)
{
	global $owlPDO, $dbname;
	$stmt = $owlPDO->prepare("select jenisangsuran,inisial,status from " . $dbname . ".sdm_angsuran_komponen where kodeorg=? and komponengaji=?");
	$stmt->execute(array($pt, $komponengaji));
	$baris = $stmt->fetch(PDO::FETCH_ASSOC);
	return $baris ? $baris : array('jenisangsuran' => '', 'inisial' => '', 'status' => '0');
}

#filter daftar angsuran, dipakai daftar, Excel, dan PDF supaya isinya sama
function whereListAngsuran($param)
{
	global $owlPDO;
	$where = '';
	if (isset($param['notransaksisch']) && $param['notransaksisch'] !== '') {
		$where .= " and a.notransaksi like " . $owlPDO->quote('%' . $param['notransaksisch'] . '%');
	}
	if (isset($param['namasch']) && $param['namasch'] !== '') {
		$where .= " and b.namakaryawan like " . $owlPDO->quote('%' . $param['namasch'] . '%');
	}
	if (isset($param['lokasitugassch']) && $param['lokasitugassch'] !== '') {
		$where .= " and b.lokasitugas = " . $owlPDO->quote($param['lokasitugassch']);
	}
	if (isset($param['jenisangsuransch']) && $param['jenisangsuransch'] !== '') {
		$where .= " and a.jenis = " . $owlPDO->quote($param['jenisangsuransch']);
	}
	if (isset($param['statussch'])) {
		if ($param['statussch'] == 'belum') {
			$where .= " and a.status in (0,1)";
		} elseif ($param['statussch'] == 'lunas') {
			$where .= " and a.status = 3";
		}
	}
	return $where;
}

#seluruh baris daftar (tanpa paging) untuk tarikan Excel/PDF, batas unit sama dengan daftar
function dataExportAngsuran($param)
{
	global $dbname;
	return fetchdata("SELECT a.*,b.nik,b.lokasitugas,b.namakaryawan,b.kodeorganisasi FROM " . $dbname . ".sdm_angsuran a
		left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid
		WHERE b.lokasitugas in (" . getOrgDetail(2) . ") " . whereListAngsuran($param) . " ORDER BY a.start DESC, a.notransaksi ASC");
}

function labelStatusAngsuran($status)
{
	$label = array('0' => 'Belum Aktif/Berjalan', '1' => 'Aktif/Sudah Berjalan', '3' => 'Sudah Lunas');
	return isset($label[$status]) ? $label[$status] : '';
}

#keterangan filter yang aktif untuk kop tarikan
function infoFilterAngsuran($param)
{
	global $owlPDO, $dbname, $unitAkses;
	$info = array();
	$lokasi = (isset($param['lokasitugassch']) && $param['lokasitugassch'] !== '') ? $param['lokasitugassch'] : '';
	$info[] = 'Lokasi Tugas: ' . ($lokasi != '' ? $lokasi . (isset($unitAkses[$lokasi]) ? ' - ' . $unitAkses[$lokasi] : '') : 'Semua');
	$jenis = 'Semua';
	if (isset($param['jenisangsuransch']) && $param['jenisangsuransch'] !== '') {
		$rj = fetchdata("select jenisangsuran from " . $dbname . ".sdm_angsuran_komponen where komponengaji=" . $owlPDO->quote($param['jenisangsuransch']) . " and kodeorg in (" . getOrgDetail(4) . ") limit 1");
		$jenis = isset($rj[0]['jenisangsuran']) ? $rj[0]['jenisangsuran'] : $param['jenisangsuransch'];
	}
	$info[] = 'Jenis: ' . $jenis;
	$statusSch = isset($param['statussch']) ? $param['statussch'] : '';
	$info[] = 'Status: ' . ($statusSch == 'belum' ? 'Belum Lunas' : ($statusSch == 'lunas' ? 'Lunas' : 'Semua'));
	if (isset($param['notransaksisch']) && $param['notransaksisch'] !== '') {
		$info[] = 'No Transaksi: ' . $param['notransaksisch'];
	}
	if (isset($param['namasch']) && $param['namasch'] !== '') {
		$info[] = 'Nama: ' . $param['namasch'];
	}
	return implode(' | ', $info);
}

#nama karyawan untuk sekumpulan id (mis. pembuat transaksi), satu query; kunci = id sebagai angka
function namaKaryawanById($ids)
{
	global $owlPDO, $dbname;
	$q = array();
	foreach ($ids as $id) {
		if ($id !== '' && $id !== null) {
			$q[$id] = $owlPDO->quote($id);
		}
	}
	$peta = array();
	if (count($q) == 0) {
		return $peta;
	}
	foreach (fetchdata("select karyawanid,namakaryawan from " . $dbname . ".datakaryawan where karyawanid in (" . implode(',', $q) . ")") as $r) {
		$peta[intval($r['karyawanid'])] = $r['namakaryawan'];
	}
	return $peta;
}

#jumlah yang sudah dibayar per transaksi = potongan gaji (komponen jenis angsuran, periode dalam rentang angsuran) + pembayaran kas/bank,
#sama dengan "Data Angsuran telah dibayar" di preview; dihitung per kumpulan baris dengan beberapa query, bukan per baris
function terbayarAngsuran($rows)
{
	global $owlPDO, $dbname;
	$hasil = array();
	if (count($rows) == 0) {
		return $hasil;
	}
	$idKar = array();
	$idJenis = array();
	$idTrx = array();
	$karTrx = array();
	foreach ($rows as $r) {
		$idKar[$r['karyawanid']] = $owlPDO->quote($r['karyawanid']);
		$idJenis[$r['jenis']] = $owlPDO->quote($r['jenis']);
		$idTrx[] = $owlPDO->quote($r['notransaksi']);
		$karTrx[$r['notransaksi']] = intval($r['karyawanid']);
		$hasil[$r['notransaksi']] = 0;
	}
	$gaji = array();
	foreach (fetchdata("select karyawanid,idkomponen,periodegaji,sum(jumlah) as jumlah from " . $dbname . ".sdm_gaji where idkomponen in (" . implode(',', $idJenis) . ") and karyawanid in (" . implode(',', $idKar) . ") group by karyawanid,idkomponen,periodegaji") as $g) {
		$gaji[intval($g['karyawanid']) . '|' . $g['idkomponen']][$g['periodegaji']] = $g['jumlah'];
	}
	foreach ($rows as $r) {
		$kunci = intval($r['karyawanid']) . '|' . $r['jenis'];
		if (isset($gaji[$kunci])) {
			foreach ($gaji[$kunci] as $periode => $jml) {
				if ($periode >= $r['start'] && $periode <= $r['end']) {
					$hasil[$r['notransaksi']] += $jml;
				}
			}
		}
	}
	foreach (fetchdata("select nodok,nik,sum(jumlah) as jumlah from " . $dbname . ".keu_kasbankdt where nodok in (" . implode(',', $idTrx) . ") group by nodok,nik") as $k) {
		if (isset($karTrx[$k['nodok']]) && intval($k['nik']) == $karTrx[$k['nodok']]) {
			$hasil[$k['nodok']] += $k['jumlah'];
		}
	}
	return $hasil;
}

#data satu transaksi untuk tarikan Excel/PDF: header, jadwal per bulan, potongan gaji dan kas/bank per bulan, sisa berjalan
function dataTransaksiAngsuran($notransaksi)
{
	global $owlPDO, $dbname;
	barisAngsuran($notransaksi);
	$h = fetchdata("select a.*,b.nik,b.namakaryawan,b.lokasitugas,b.kodeorganisasi from " . $dbname . ".sdm_angsuran a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.notransaksi=" . $owlPDO->quote($notransaksi));
	$hdr = $h[0];
	$komp = komponenAngsuranPT($hdr['kodeorganisasi'], $hdr['jenis']);
	$hdr['namajenis'] = $komp['jenisangsuran'];
	$nm = namaKaryawanById(array($hdr['updateby']));
	$hdr['pembuat'] = isset($nm[intval($hdr['updateby'])]) ? $nm[intval($hdr['updateby'])] : '';

	$bulanData = array();
	foreach (fetchdata("select bulan,jumlah from " . $dbname . ".sdm_angsurandt where notransaksi=" . $owlPDO->quote($notransaksi)) as $r) {
		$bulanData[$r['bulan']]['jadwal'] = $r['jumlah'];
	}
	foreach (fetchdata("select periodegaji as bulan,sum(jumlah) as jumlah from " . $dbname . ".sdm_gaji where karyawanid=" . $owlPDO->quote($hdr['karyawanid']) . " and idkomponen=" . $owlPDO->quote($hdr['jenis']) . " and periodegaji>=" . $owlPDO->quote($hdr['start']) . " and periodegaji<=" . $owlPDO->quote($hdr['end']) . " group by periodegaji") as $r) {
		$bulanData[$r['bulan']]['gaji'] = $r['jumlah'];
	}
	foreach (fetchdata("select substr(tanggal,1,7) as bulan,sum(jumlah) as jumlah from " . $dbname . ".keu_kasbankdt where nodok=" . $owlPDO->quote($notransaksi) . " and nik=" . $owlPDO->quote($hdr['karyawanid']) . " group by substr(tanggal,1,7)") as $r) {
		$bulanData[$r['bulan']]['kas'] = $r['jumlah'];
	}
	ksort($bulanData);

	$baris = array();
	$sisa = $hdr['totalpinjaman'];
	$tot = array('jadwal' => 0, 'gaji' => 0, 'kas' => 0, 'dibayar' => 0);
	foreach ($bulanData as $bulan => $d) {
		$jadwal = isset($d['jadwal']) ? $d['jadwal'] : 0;
		$gaji = isset($d['gaji']) ? $d['gaji'] : 0;
		$kas = isset($d['kas']) ? $d['kas'] : 0;
		$sisa -= ($gaji + $kas);
		$baris[] = array('bulan' => $bulan, 'jadwal' => $jadwal, 'gaji' => $gaji, 'kas' => $kas, 'dibayar' => $gaji + $kas, 'sisa' => $sisa);
		$tot['jadwal'] += $jadwal;
		$tot['gaji'] += $gaji;
		$tot['kas'] += $kas;
		$tot['dibayar'] += $gaji + $kas;
	}
	return array('hdr' => $hdr, 'baris' => $baris, 'tot' => $tot, 'sisa' => $hdr['totalpinjaman'] - $tot['dibayar']);
}

switch ($method) {
	case'loadjenisangsuran':
		#karyawan belum dipilih (form dikosongkan): cukup pilihan kosong, bukan peringatan akses
		if (!isset($param['karyawanid']) || $param['karyawanid'] === '') {
			echo "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			break;
		}
		cekAksesKaryawan($param['karyawanid']);

		$arrlokasitugas=makeOption($dbname, 'datakaryawan', 'karyawanid,kodeorganisasi'," karyawanid='".$param['karyawanid']."'");
        $lokasitugas=$arrlokasitugas[$param['karyawanid']];

        $str="select komponengaji,jenisangsuran from ".$dbname.".sdm_angsuran_komponen where kodeorg='".$lokasitugas."' and status='1'";
        $res=fetchdata($str);
        foreach($res as $val){
            $optjenisangsuran.="<option value='".$val['komponengaji']."'>".$val['jenisangsuran']."</option>";
        }

        echo $optjenisangsuran;
	break;	

    case'hitungbulan':
        $totalhutang=doubleval(str_replace(',', '', $param['totalhutang']));
        $rpbulan=doubleval(str_replace(',', '',$param['rpbulan']));
        if($rpbulan>$totalhutang){
            exit('Warning : Total hutang lebih kecil dari rupiah/bulan');
        }
        if($rpbulan<=0){
            exit('Warning : Rupiah/bulan harus lebih besar dari 0');
        }
        $jumlahbulan=ceil($totalhutang/$rpbulan)-1;

        $tglx=$param['bulandari'].'-01';
        $tglxnext = date('Y-m-d', strtotime($tglx . ' +'.$jumlahbulan.' month'));

        echo substr($tglxnext, 0,7);
    break;

	case'getdetail':
		$lokasiKar = cekAksesKaryawan(isset($param['karyawanid']) ? $param['karyawanid'] : '');
		if (!empty($param['notransaksi'])) {
			$hdrAkses = barisAngsuran($param['notransaksi']);
			if (intval($hdrAkses['karyawanid']) != intval($param['karyawanid'])) {
				exit("Warning : Karyawan tidak sesuai dengan data angsuran.");
			}
		}
		
        $strApp="select * from ".$dbname.".sdm_angsuran where karyawanid='".$param['karyawanid']."' and jenis='".$param['jenisangsuran']."' and (status='1' or status='0') and notransaksi!='".$param['notransaksi']."' ";
        $resApp=fetchData($strApp);
        if(count($resApp) > 0){
            exit("Warning : Sudah ada data yang tersimpan atas karyawan dan jenis angsuran ini yang belum lunas/belum diposting , edit data yang belum diposting");
        }else{

            $strApp="select * from ".$dbname.".sdm_angsuran where  notransaksi='".$param['notransaksi']."' ";
            $resApp=fetchData($strApp);
            $lokasitugas=getKary($resApp[0]['karyawanid'],'lokasitugas');
            $rangebulan = month_inbetween($param['bulandari'],$param['bulansampai']);
            $jumlahbulan=count($rangebulan);
            $jumlahbulanx=count($rangebulan);
            if($resApp[0]['status']=='1'){
                $tab="<thead>";
                $tab.="<tr class=rowcontent>";
                if($jumlahbulan>=5){
                    $pembagi=5;
                    $tab.="<td align=center>No</td>";
                    $tab.="<td align=center>Bulan</td>";
                    $tab.="<td align=center>Rupiah</td>";
                    $tab.="<td align=center>No</td>";
                    $tab.="<td align=center>Bulan</td>";
                    $tab.="<td align=center>Rupiah</td>";
                    $tab.="<td align=center>No</td>";
                    $tab.="<td align=center>Bulan</td>";
                    $tab.="<td align=center>Rupiah</td>";
                    $tab.="<td align=center>No</td>";
                    $tab.="<td align=center>Bulan</td>";
                    $tab.="<td align=center>Rupiah</td>";
                    $tab.="<td align=center>No</td>";
                    $tab.="<td align=center>Bulan</td>";
                    $tab.="<td align=center>Rupiah</td>";
                }else{
                    $pembagi=$jumlahbulan;
                    for ($i=1; $i <=$jumlahbulan; $i++) { 
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                    }
                }
                $tab.="</tr>";
                $tab.="</thead>";
                $tab.="<tbody>";
                $no=0;
                $trx=0;
                #kodeorg='".$lokasitugas."' and 
                $datagajitutup=array();
                $str="select * from ".$dbname.".sdm_5periodegaji where sudahproses='1'";
                $res=fetchdata($str);
                foreach($res as $keyx=>$valx){
                    $datagajitutup[$valx['periode']]=1;
                }
                $bulanakhirx='';
                $str="select * from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
                $res=fetchdata($str);
                foreach($res as $keyx=>$valx){
                    $bulanakhirx=$valx['bulan'];
                    $jumlahbulanx--;
                    $disabled='';
                    if(isset($datagajitutup[$valx['bulan']])){
                        $disabled='disabled';
                    }
                    $no++;
                    if($no%$pembagi==0){
                        $trx=$no+1;
                    }
                    if($no==$trx){
                        $tab.="<tr class=rowcontent>";
                    }
                    $tab.="<td align=center><b>".$no."</b></td>";
                    $tab.="<td id=bulan_".$no." align=center>".$valx['bulan']."</td>";
                    $tab.="<td align=center><input id=rpdetailx_".$no." style=width:75px class=myinputtextnumber onkeypress=\"return angka_doang(event);\" value=".number_format($valx['jumlah'],0)." ".$disabled."><input type=hidden id=rpdetail_".$no."></td>";
                    
                    if($no%$pembagi==0){
                        $tab.="</tr>";
                    }

                }

                if($jumlahbulanx>0){

                    $rangebulanx = month_inbetween($bulanakhirx,$param['bulansampai']);
                    foreach($rangebulanx as $bulan){
                        if($bulan!=$bulanakhirx){
                            $no++;
                            if($no%$pembagi==0){
                                $trx=$no+1;
                            }
                            if($no==$trx){
                                $tab.="<tr class=rowcontent>";
                            }
                            $tab.="<td align=center><b>".$no."</b></td>";
                            $tab.="<td id=bulan_".$no." align=center>".$bulan."</td>";
                            $tab.="<td align=center><input id=rpdetailx_".$no." style=width:75px class=myinputtextnumber onkeypress=\"return angka_doang(event);\" value=0 ><input type=hidden id=rpdetail_".$no."></td>";
                            
                            if($no%$pembagi==0){
                                $tab.="</tr>";
                            }

                        }

                    }
                }

                    $tab.="<input hidden id=totaldetail value=".$no."></tbody>";
            }else{
                    cekBulanTutup($lokasiKar, array($param['bulandari']));
                 $totalhutang=doubleval(str_replace(',', '', $param['tothutang']));
                    $rpbulan=doubleval(str_replace(',', '',$param['rpbulan']));
                    $rangebulan = month_inbetween($param['bulandari'],$param['bulansampai']);
                    if($totalhutang<=0){
                        exit("Warning : Total hutang harus lebih besar dari 0");
                    }
                    if($param['tipeangsuran']=='angsurannominal' and $rpbulan<=0){
                        exit("Warning : Rupiah/bulan harus lebih besar dari 0");
                    }
                    
                    if($param['tipeangsuran']=='angsurannominal'){
                        $nilaiperbulan=$rpbulan;
                    }else{
                        $jumlahbulan=count($rangebulan);
                        
                        $rpbulan=ceil($totalhutang/$jumlahbulan);
                        if(substr($rpbulan,-2)>0){
                            $pengurang=substr($rpbulan,-2);
                            $rpbulan=$rpbulan-$pengurang;
                        }
                        $nilaiperbulan=$rpbulan;
                    }
                    //$tab.="<table cellspacing='1' border='0'>";
                    $tab.="<thead>";
                    $tab.="<tr class=rowcontent>";
                    $jumlahbulan=count($rangebulan);
                    if($jumlahbulan>=5){
                        $pembagi=5;
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                    }else{
                        $pembagi=$jumlahbulan;
                        for ($i=1; $i <=$jumlahbulan; $i++) { 
                            $tab.="<td align=center>No</td>";
                            $tab.="<td align=center>Bulan</td>";
                            $tab.="<td align=center>Rupiah</td>";
                        }
                    }
                    $tab.="</tr>";
                    $tab.="</thead>";
                    $tab.="<tbody>";
                    $no=0;
                    $trx=0;
                    foreach($rangebulan as $bulan){
                        if($param['tipeangsuran']=='angsurannominal'){
                            if($totalhutang>$nilaiperbulan){
                                $totalhutang-=$nilaiperbulan;
                            }else{
                                $nilaiperbulan=$totalhutang;
                            }
                        }else{
                           if($totalhutang>$nilaiperbulan){
                                if($bulan==$param['bulansampai']){
                                    $nilaiperbulan=$totalhutang;
                                }else{
                                    $totalhutang-=$nilaiperbulan;
                                }
                            }else{
                                $nilaiperbulan=$totalhutang;
                            }
                        }
                        $no++;
                        if($no%$pembagi==0){
                            $trx=$no+1;
                        }
                        if($no==$trx){
                            $tab.="<tr class=rowcontent>";
                        }
                        $tab.="<td align=center><b>".$no."</b></td>";
                        $tab.="<td id=bulan_".$no." align=center>".$bulan."</td>";
                        $tab.="<td align=center><input id=rpdetailx_".$no." style=width:75px class=myinputtextnumber onkeypress=\"return angka_doang(event);\" value=".number_format($nilaiperbulan,0)."><input type=hidden id=rpdetail_".$no."></td>";
                        
                        if($no%$pembagi==0){
                            $tab.="</tr>";
                        }

                    }
                    $tab.="<input hidden id=totaldetail value=".$no."></tbody>";
            }

           
            //$tab.="</table>";

            echo $tab;
        }
	break;		
	
    case'insert':
        
        $lokasiKar = cekAksesKaryawan(isset($param['karyawanid']) ? $param['karyawanid'] : '');
        cekBulanTutup($lokasiKar, array($param['bulandari']));
        $strApp="select * from ".$dbname.".sdm_angsuran where karyawanid='".$param['karyawanid']."' and jenis='".$param['jenisangsuran']."' and (status='1' or status='0') ";
        $resApp=fetchData($strApp);
        if(count($resApp) > 0){
            exit("Warning : Sudah ada data yang tersimpan atas karyawan dan jenis angsuran ini yang belum lunas/belum diposting , silahkan edit data yang belum diposting");
        }
        $totalhutang=doubleval(str_replace(',', '', $param['tothutang']));
        $arrdetail=detailAngsuranDariParam($param);
        $totaldetail=array_sum($arrdetail);
        if($totalhutang!=$totaldetail){
            exit("Warning : Total hutang dengan total detail tidak sama dengan varian : ".($totalhutang-$totaldetail)."<br> silahkan dilakukan pengecekan kembali");
        }
        #nama jenis dan inisial nomor diambil dari komponen milik PT karyawan (komponengaji 120 dipakai semua PT)
        $ptKar = fetchdata("select kodeorganisasi from ".$dbname.".datakaryawan where karyawanid='".$param['karyawanid']."'");
        $komponenKar = komponenAngsuranPT($ptKar[0]['kodeorganisasi'], $param['jenisangsuran']);
        if($komponenKar['status'] != '1'){
            exit("Warning : Jenis angsuran tidak sesuai dengan PT karyawan.");
        }
        #bulanawal/jenisangsuran/counter dimana counter per periode tahun-bulan dan jenis angsuran
        $str    = "SELECT max(right(notransaksi,5)) as jmlhrow FROM " . $dbname . ".sdm_angsuran 
        WHERE notransaksi like '".str_replace('-', '', $param['bulandari'])."%' and jenis = '".$param['jenisangsuran']."'";
        $res    = fetchData($str);
        $jlhbrs     = $res[0]['jmlhrow'];
        $counterid  = intval($res[0]['jmlhrow'])+1;
        $idbaru = addZero($counterid,5);
        #inisial dibersihkan ke huruf/angka saja supaya nomor transaksi selalu lolos validasi parameter; kosong -> ANG
        $inisialNo = preg_replace('/[^A-Za-z0-9]/', '', $komponenKar['inisial']);
        if ($inisialNo == '') {
            $inisialNo = 'ANG';
        }
        $notransaksi=str_replace('-', '', $param['bulandari'])."/".$inisialNo."/".$idbaru;

        $owlPDO->beginTransaction();
        try{
            $insertht = $owlPDO->prepare("INSERT INTO " . $dbname . ".sdm_angsuran 
            (`notransaksi`,`karyawanid`,`jenis`,`tipe`,`start`,`end`,`total`,`totalpinjaman`,`bulanan`,`keterangan`,`status`,`updatetime`,`updateby`)
            VALUES (?,?,?,?,?,?,?,?,?,?,'0',?,?)");
            $insertht->execute(array($notransaksi, $param['karyawanid'], $param['jenisangsuran'], $param['tipeangsuran'], $param['bulandari'], $param['bulansampai'], doubleval(str_replace(',', '', $param['tothutang'])), doubleval(str_replace(',', '', $param['tothutang'])), doubleval(str_replace(',', '', $param['rpbulan'])), $param['ket'], date('Y-m-d H:i:s'), $_SESSION['standard']['userid']));
        }catch (PDOException $e) {
            $owlPDO->rollBack();
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        try{
            $insertdt = $owlPDO->prepare("INSERT INTO " . $dbname . ".sdm_angsurandt (`notransaksi`,`bulan`,`jumlah`) VALUES (?,?,?)");
            foreach ($arrdetail as $key => $val) {
                $insertdt->execute(array($notransaksi, $key, $val));
            }
            $owlPDO->commit();
        }catch (PDOException $e) {
            $owlPDO->rollBack();
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;
    case'inserttopup':
        barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $rpbulantopup=$param['rpbulantopup'];
        $tothutangtopup=$param['tothutangtopup'];
        if($rpbulantopup>$tothutangtopup and $param['tipeangsuran']=='angsurannominal'){
            exit('Warning : Total hutang lebih kecil dari rupiah/bulan');
        }

        $strApp="select * from ".$dbname.".sdm_angsuran_topup where notransaksi='".$param['notransaksi']."' and post='0' ";
        $resApp=fetchData($strApp);
        if(count($resApp) > 0){
            exit("Warning : Ada data topup yang belum diposting,silahkan diposting terlebih dahulu");
        }

        if($param['tipeangsuran']=='angsuranjangkawaktu'){
            $strApp="select * from ".$dbname.".sdm_angsuran_topup where notransaksi='".$param['notransaksi']."' and bulanmulaiperubahan>='".$param['bulanmulaiperubahan']."'  ";
            $resApp=fetchData($strApp);
            if(count($resApp) > 0 and $param['jenistopup']==0){
                exit("Warning : Sudah ada data topup bulan mulai perubahan yang sama atau lebih tinggi dari bulan mulai perubahan yang dipilih, silahkan mengganti bulan mulai perubahan");
            }

        }else{
            $strApp="select * from ".$dbname.".sdm_angsuran_topup where notransaksi='".$param['notransaksi']."' and bulanmulaiperubahan>'".$param['bulanmulaiperubahan']."'  ";
            $resApp=fetchData($strApp);
            if(count($resApp) > 0  and $param['jenistopup']==0){
                exit("Warning : Sudah ada data topup bulan mulai perubahan yang sama atau lebih tinggi dari bulan mulai perubahan yang dipilih, silahkan mengganti bulan mulai perubahan");
            }
        }

        
        #bulanawal/jenisangsuran/counter dimana counter per periode tahun-bulan dan jenis angsuran
        $str    = "SELECT count(*)as jmlhrow FROM " . $dbname . ".sdm_angsuran_topup 
        WHERE notransaksi = '".$param['notransaksi']."'";
        $res    = fetchData($str);
        $counteridx  = intval($res[0]['jmlhrow'])+1;
        $idbarux = addZero($counteridx,5);
        //echo intval(count($res)).'##'.$counteridx;
        $nomotopup=str_replace('-', '', $param['bulanmulaiperubahan'])."/TopupAngsuran/".$idbarux;

        $insertht     = "INSERT INTO " . $dbname . ".sdm_angsuran_topup 
        (`notransaksi`,`nomotopup`,`tipetopup`,`bulanmulaiperubahan`,`bulansampaiangsuran`,`rupiah`,`rupiahbulanan`,`post`,`updatetime`,`updatedby`)
        VALUES 
        ('" . $param['notransaksi'] . "','" . $nomotopup. "','" . $param['jenistopup']. "','" . $param['bulanmulaiperubahan']. "','" . $param['bulansampaitopup']. "','" . $param['tothutangtopup']. "','" . $param['rpbulantopup']. "','0','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."')";
        //echo $insertht;
        try{
            $owlPDO->exec($insertht);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;
    case'loadData':
		
        $tab        ="";
        $footer     ="";
		$limit      = 25;
        $page       = 0;
        $colspan    = 17;

		if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        
		$offset     = floatval($page) * $limit;
		$maxdisplay =(floatval($page) * $limit);
        $no         =((floatval($page) * $limit));
        $arrorgdet  = getOrgDetail(2);
		
        $where = whereListAngsuran($param);
        $str        = "SELECT COUNT(*) AS jmlhrow FROM ".$dbname.".sdm_angsuran a 
        left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
        WHERE b.lokasitugas in (".$arrorgdet.")  ".$where." ORDER BY start DESC";
        $res        = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs     = owlBaris($res);
		$res        = fetchdata($str);
		$jlhbrs     = $res[0]['jmlhrow'];
        $totrows    = ceil($jlhbrs / $limit);
        
        if($totrows == 0){
            $totrows = 1;
        }
                
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++){
            $sel    = ($page==$er-1)? 'selected': '';
            $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        
        $frompage   = ((floatval($page)*$limit)+1);
        if(((floatval($page)+1)*$limit) > $jlhbrs){
            $topage = $jlhbrs;
        }else{
            $topage = ((floatval($page)+1)*$limit);
        }

        if($jlhbrs < 1){
            $tab.="<tr class=rowcontent>
                        <td style='text-align:center' colspan=".$colspan.">" . $_SESSION['lang']['errdatanotexist'] . "</td>
                    </tr>";
        }else{
            $iList  = "SELECT a.*,b.lokasitugas,b.namakaryawan,b.kodeorganisasi FROM ".$dbname.".sdm_angsuran a 
        left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
        WHERE b.lokasitugas in (".$arrorgdet.")  ".$where." ORDER BY start DESC LIMIT ".$offset.",".$limit." ";
            $hasil  = fetchdata($iList);
            $bayar = terbayarAngsuran($hasil);
            $namaPembuat = namaKaryawanById(array_column($hasil, 'updateby'));
            foreach ($hasil as $dList){
                if($dList['status'] == 0){
                    $status = "Belum Aktif/Berjalan";
                }
                elseif ($dList['status'] == 1) {
                    $status = "Aktif/Sudah Berjalan";
                }
                elseif ($dList['status'] == 3) {
                    $status = 'Sudah Lunas';
                }
                $komponenRow = komponenAngsuranPT($dList['kodeorganisasi'], $dList['jenis']);
                $arrjenisangsuran = array($dList['jenis'] => $komponenRow['jenisangsuran']);
                $no+=1;
                $tab.="<tr class=rowcontent>";
                    $tab.="<td align=left>".$dList['notransaksi']."</td>";
                    $tab.="<td align=center>".$dList['namakaryawan']."</td>";
                    $tab.="<td align=center>".$arrjenisangsuran[$dList['jenis']]."</td>";
                    $tab.="<td align=center>".$arrtipeangsuran[$dList['tipe']]."</td>";
                    $tab.="<td align=center>".$dList['start']."</td>";
                    $tab.="<td align=center>".$dList['end']."</td>";
                    $tab.="<td align=right>".number_format($dList['totalpinjaman'])."</td>";
                    $tab.="<td align=right>".number_format($bayar[$dList['notransaksi']])."</td>";
                    $tab.="<td align=center>".$dList['keterangan']."</td>";
                    $tab.="<td align=center>".(isset($namaPembuat[intval($dList['updateby'])]) ? $namaPembuat[intval($dList['updateby'])] : '')."</td>";
                    #warna status: belum aktif kuning, aktif biru, lunas hijau
                    $warnaStatus = array('0' => '#fff59d', '1' => '#90caf9', '3' => '#a5d6a7');
                    $tab.="<td align=center style=\"background-color:".(isset($warnaStatus[$dList['status']]) ? $warnaStatus[$dList['status']] : '')."\">".$status."</td>";

                    if($dList['status']==0){
                        $tab.="<td align=center>
                                <img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"edit('" . $dList['notransaksi'] . "','" . $dList['status'] . "');\">
                            </td>";
                        $tab.="<td align=center>
                                <img src=images/application/application_delete.png class=zImgBtn  caption='Delete' onclick=\"del('" . $dList['notransaksi'] . "');\">
                            </td>";
                        $tab.="<td align=center>
                                <img src=images/skyblue/posting.png class=zImgBtn  caption='Posting' onclick=\"postdata('" . $dList['notransaksi'] . "');\">
                            </td>";
                        $tab.="<td align=center><img src=images/zoom.png class=zImgBtn title=Detail onclick=previewDetail('".$dList['notransaksi']."');></td>";
                        
                    }

                    if($dList['status']==1){
                        $tab.="<td align=center>
                                <img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"edit('" . $dList['notransaksi'] . "','" . $dList['status'] . "');\">
                            </td>";
                        $tab.="<td hidden align=center>
                                <img src=images/plus.png class=zImgBtn title=Topup onclick=topup('".$dList['notransaksi']."');>
                            </td>";
                        $tab.="<td align=center>
                                <img src=images/close.png class=zImgBtn title=close onclick=close_a('".$dList['notransaksi']."','" . $dList['end'] . "');>
                            </td>";
                        $tab.="<td align=center><img src=images/zoom.png class=zImgBtn title=Detail onclick=previewDetail('".$dList['notransaksi']."');></td>";
                        $tab.="<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$dList['notransaksi']."','".$dList['karyawanid']."')\" src='images/upload-2-xxl.png'/></td>";
                    }

                    if($dList['status']==3){
                        $tab.="<td align=center></td>";
                        $tab.="<td align=center></td>";
                        $tab.="<td align=center><img src=images/zoom.png class=zImgBtn title=Detail onclick=previewDetail('".$dList['notransaksi']."');></td>";
                        $tab.="<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$dList['notransaksi']."','".$dList['karyawanid']."')\" src='images/upload-2-xxl.png'/></td>";
                    }                

                $tab.="<td align=center><img src=images/excel.jpg class=zImgBtn title='Excel' onclick=\"exportTrx('".$dList['notransaksi']."','excel');\"></td>";
                $tab.="<td align=center><img src=images/pdf.jpg class=zImgBtn title='PDF' onclick=\"exportTrx('".$dList['notransaksi']."','pdf');\"></td>";
                $tab.="</tr></tbody>";
            }
            $footer .= "<tr>
                            <td colspan=".$colspan." align=center>
                                ".$frompage." to ".$topage." Of ".  $jlhbrs."
                            </td>
                        </tr>";
            $footer .= "<tr>
                            <td colspan=".$colspan." align=center>";
                            if($page!=0){
                                $footer .= "<button class=mybutton onclick=loadData(" . (floatval($page) - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
                            }
                $footer  .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
                            if((floatval($page)+1) != $totrows){
                                $footer .="<button class=mybutton onclick=loadData(" . (floatval($page) + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
                            }
            $footer .=     "</td>
                        </tr>";
        }
        echo $tab."####".$footer;
    break;

    case 'excellist':
        $rows = dataExportAngsuran($param);
        $bayar = terbayarAngsuran($rows);
        $namaPembuat = namaKaryawanById(array_column($rows, 'updateby'));
        #kop/ditarik-oleh format standar, pakai org karyawan yang login (data lintas unit)
        $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
        $kolom = 16;
        $stream = "<table>
            <tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
            <tr><td colspan=" . $kolom . "><b>ANGSURAN KARYAWAN</b></td></tr>
            <tr><td colspan=" . $kolom . ">" . htmlspecialchars(infoFilterAngsuran($param)) . "</td></tr>
            <tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
            <tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
            </table>";
        $stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
            <thead><tr>
                <th align=center bgcolor=#CCCCCC>No</th>
                <th align=center bgcolor=#CCCCCC>No Transaksi</th>
                <th align=center bgcolor=#CCCCCC>NIK</th>
                <th align=center bgcolor=#CCCCCC>Nama</th>
                <th align=center bgcolor=#CCCCCC>Lokasi Tugas</th>
                <th align=center bgcolor=#CCCCCC>Jenis Angsuran</th>
                <th align=center bgcolor=#CCCCCC>Tipe Angsuran</th>
                <th align=center bgcolor=#CCCCCC>Bulan Awal</th>
                <th align=center bgcolor=#CCCCCC>Bulan Akhir</th>
                <th align=center bgcolor=#CCCCCC>Total Hutang</th>
                <th align=center bgcolor=#CCCCCC>Sudah Dibayar</th>
                <th align=center bgcolor=#CCCCCC>Sisa Hutang</th>
                <th align=center bgcolor=#CCCCCC>Rupiah/Bulan</th>
                <th align=center bgcolor=#CCCCCC>Status</th>
                <th align=center bgcolor=#CCCCCC>Dibuat Oleh</th>
                <th align=center bgcolor=#CCCCCC>Keterangan</th>
            </tr></thead><tbody>";
        $no = 0;
        $ttl = 0;
        $ttlBayar = 0;
        foreach ($rows as $r) {
            $no++;
            $ttl += $r['totalpinjaman'];
            $ttlBayar += $bayar[$r['notransaksi']];
            $komponenRow = komponenAngsuranPT($r['kodeorganisasi'], $r['jenis']);
            $stream .= "<tr>
                <td align=center>" . $no . "</td>
                <td>" . $r['notransaksi'] . "</td>
                <td>" . $r['nik'] . "</td>
                <td>" . htmlspecialchars($r['namakaryawan']) . "</td>
                <td align=center>" . $r['lokasitugas'] . "</td>
                <td>" . htmlspecialchars($komponenRow['jenisangsuran']) . "</td>
                <td>" . (isset($arrtipeangsuran[$r['tipe']]) ? $arrtipeangsuran[$r['tipe']] : '') . "</td>
                <td align=center>" . $r['start'] . "</td>
                <td align=center>" . $r['end'] . "</td>
                <td align=right>" . round($r['totalpinjaman']) . "</td>
                <td align=right>" . round($bayar[$r['notransaksi']]) . "</td>
                <td align=right>" . round($r['totalpinjaman'] - $bayar[$r['notransaksi']]) . "</td>
                <td align=right>" . round($r['bulanan']) . "</td>
                <td>" . labelStatusAngsuran($r['status']) . "</td>
                <td>" . htmlspecialchars(isset($namaPembuat[intval($r['updateby'])]) ? $namaPembuat[intval($r['updateby'])] : '') . "</td>
                <td>" . htmlspecialchars($r['keterangan']) . "</td>
            </tr>";
        }
        if ($no == 0) {
            $stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
        } else {
            $stream .= "<tr>
                <td colspan=9 align=right><b>Total</b></td>
                <td align=right><b>" . round($ttl) . "</b></td>
                <td align=right><b>" . round($ttlBayar) . "</b></td>
                <td align=right><b>" . round($ttl - $ttlBayar) . "</b></td>
                <td colspan=4></td>
            </tr>";
        }
        $stream .= "</tbody></table>";

        $xls = new HtmlExcel();
        $xls->addSheet("Angsuran", $stream);
        $xls->headers("Angsuran_" . date('YmdHis') . ".xls");
        echo $xls->buildFile();
        exit;

    case 'pdflist':
        require_once('lib/fpdf.php');
        $rows = dataExportAngsuran($param);
        $bayar = terbayarAngsuran($rows);
        $namaPembuat = namaKaryawanById(array_column($rows, 'updateby'));
        $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
        $infoFilter = infoFilterAngsuran($param);

        #teks yang kepanjangan dipotong dan diberi "..." supaya tidak tumpang tindih ke kolom sebelah
        function angFitTeks($pdf, $txt, $w, $f = 7)
        {
            $txt = utf8_decode($txt);
            $pdf->SetFont('Arial', '', $f);
            if ($pdf->GetStringWidth($txt) <= $w - 2) {
                return $txt;
            }
            while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 2) {
                $txt = substr($txt, 0, -1);
            }
            return rtrim($txt) . '...';
        }

        class PDFAngsuran extends FPDF
        {
            public $colW = array();

            function Header()
            {
                global $hdpt, $infoFilter;
                $width = $this->w - $this->lMargin - $this->rMargin;
                if (file_exists($hdpt['logo'])) {
                    $this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 18);
                }
                $this->SetFont('Arial', 'B', 11);
                $this->SetXY($this->lMargin + 22, $this->tMargin + 2);
                $this->Cell(160, 6, utf8_decode($hdpt['nama']), 0, 1, 'L');
                $this->SetY($this->tMargin + 15);
                $this->SetFont('Arial', 'B', 13);
                $this->Cell($width, 7, 'ANGSURAN KARYAWAN', 0, 1, 'C');
                $this->SetFont('Arial', '', 8);
                $this->Cell($width, 5, utf8_decode($infoFilter), 0, 1, 'C');
                $this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
                $this->Ln(1);

                $this->SetFillColor(220, 220, 220);
                $this->SetFont('Arial', 'B', 8);
                $judul = array('No', 'No Transaksi', 'Nama', 'Lokasi', 'Awal', 'Akhir', 'Total Hutang', 'Sudah Dibayar', 'Sisa Hutang', 'Status', 'Dibuat Oleh', 'Keterangan');
                foreach ($judul as $i => $j) {
                    $this->Cell($this->colW[$i], 6, $j, 1, 0, 'C', true);
                }
                $this->Ln();
            }

            function Footer()
            {
                $this->SetY(-12);
                $this->SetFont('Arial', 'I', 7);
                $this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
            }
        }

        $pdf = new PDFAngsuran('L', 'mm', 'A4');
        $pdf->AliasNbPages();
        #pindah halaman manual per baris (margin bawah hanya batas), supaya aman untuk data banyak dan tidak menimpa footer
        $pdf->SetAutoPageBreak(false, 16);
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $colFixed = array(7, 33, 36, 13, 14, 14, 22, 22, 22, 26, 40); #semua kolom kecuali Keterangan
        $pdf->colW = array_merge($colFixed, array($width - array_sum($colFixed)));
        $pdf->AddPage();
        $h = 6;
        $no = 0;
        $ttl = 0;
        $ttlBayar = 0;
        foreach ($rows as $r) {
            $no++;
            $ttl += $r['totalpinjaman'];
            $ttlBayar += $bayar[$r['notransaksi']];
            if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
                $pdf->AddPage();
            }
            $c = $pdf->colW;
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell($c[0], $h, $no, 1, 0, 'C');
            $pdf->Cell($c[1], $h, $r['notransaksi'], 1, 0, 'L');
            $pdf->Cell($c[2], $h, angFitTeks($pdf, $r['namakaryawan'], $c[2]), 1, 0, 'L');
            $pdf->Cell($c[3], $h, $r['lokasitugas'], 1, 0, 'C');
            $pdf->Cell($c[4], $h, $r['start'], 1, 0, 'C');
            $pdf->Cell($c[5], $h, $r['end'], 1, 0, 'C');
            $pdf->Cell($c[6], $h, number_format($r['totalpinjaman']), 1, 0, 'R');
            $pdf->Cell($c[7], $h, number_format($bayar[$r['notransaksi']]), 1, 0, 'R');
            $pdf->Cell($c[8], $h, number_format($r['totalpinjaman'] - $bayar[$r['notransaksi']]), 1, 0, 'R');
            $pdf->Cell($c[9], $h, angFitTeks($pdf, labelStatusAngsuran($r['status']), $c[9]), 1, 0, 'L');
            $pdf->Cell($c[10], $h, angFitTeks($pdf, isset($namaPembuat[intval($r['updateby'])]) ? $namaPembuat[intval($r['updateby'])] : '', $c[10]), 1, 0, 'L');
            $pdf->Cell($c[11], $h, angFitTeks($pdf, $r['keterangan'], $c[11]), 1, 1, 'L');
        }
        if ($no == 0) {
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell($width, 8, 'Data tidak ditemukan', 1, 1, 'C');
        } else {
            if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
                $pdf->AddPage();
            }
            $c = $pdf->colW;
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell($c[0] + $c[1] + $c[2] + $c[3] + $c[4] + $c[5], $h, 'Total', 1, 0, 'R');
            $pdf->Cell($c[6], $h, number_format($ttl), 1, 0, 'R');
            $pdf->Cell($c[7], $h, number_format($ttlBayar), 1, 0, 'R');
            $pdf->Cell($c[8], $h, number_format($ttl - $ttlBayar), 1, 0, 'R');
            $pdf->Cell($c[9] + $c[10] + $c[11], $h, '', 1, 1, 'L');
        }
        $pdf->Output();
        exit;

    case 'excelTrx':
        $d = dataTransaksiAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $hdr = $d['hdr'];
        $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
        $kolom = 7;
        $info = array(
            'No Transaksi' => $hdr['notransaksi'],
            'Nama / NIK' => $hdr['namakaryawan'] . ' / ' . $hdr['nik'],
            'Lokasi Tugas' => $hdr['lokasitugas'],
            'Jenis Angsuran' => $hdr['namajenis'],
            'Tipe Angsuran' => isset($arrtipeangsuran[$hdr['tipe']]) ? $arrtipeangsuran[$hdr['tipe']] : '',
            'Periode' => $hdr['start'] . ' s/d ' . $hdr['end'],
            'Total Hutang Awal' => number_format($hdr['total']),
            'Total Hutang' => number_format($hdr['totalpinjaman']),
            'Sudah Dibayar' => number_format($d['tot']['dibayar']),
            'Sisa Hutang' => number_format($d['sisa']),
            'Status' => labelStatusAngsuran($hdr['status']),
            'Dibuat Oleh' => $hdr['pembuat'],
            'Keterangan' => $hdr['keterangan']
        );
        $stream = "<table>
            <tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
            <tr><td colspan=" . $kolom . "><b>ANGSURAN KARYAWAN</b></td></tr>
            <tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
            <tr><td colspan=" . $kolom . ">&nbsp;</td></tr>";
        foreach ($info as $label => $nilai) {
            $stream .= "<tr><td colspan=2><b>" . $label . "</b></td><td colspan=" . ($kolom - 2) . ">" . htmlspecialchars($nilai) . "</td></tr>";
        }
        $stream .= "<tr><td colspan=" . $kolom . ">&nbsp;</td></tr></table>";
        $stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
            <thead><tr>
                <th align=center bgcolor=#CCCCCC>No</th>
                <th align=center bgcolor=#CCCCCC>Bulan</th>
                <th align=center bgcolor=#CCCCCC>Jadwal Angsuran</th>
                <th align=center bgcolor=#CCCCCC>Potongan Gaji</th>
                <th align=center bgcolor=#CCCCCC>Kas/Bank</th>
                <th align=center bgcolor=#CCCCCC>Total Dibayar</th>
                <th align=center bgcolor=#CCCCCC>Sisa Hutang</th>
            </tr></thead><tbody>";
        $no = 0;
        foreach ($d['baris'] as $r) {
            $no++;
            $stream .= "<tr>
                <td align=center>" . $no . "</td>
                <td align=center>" . $r['bulan'] . "</td>
                <td align=right>" . round($r['jadwal']) . "</td>
                <td align=right>" . round($r['gaji']) . "</td>
                <td align=right>" . round($r['kas']) . "</td>
                <td align=right>" . round($r['dibayar']) . "</td>
                <td align=right>" . round($r['sisa']) . "</td>
            </tr>";
        }
        if ($no == 0) {
            $stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
        } else {
            $stream .= "<tr>
                <td colspan=2 align=right><b>Total</b></td>
                <td align=right><b>" . round($d['tot']['jadwal']) . "</b></td>
                <td align=right><b>" . round($d['tot']['gaji']) . "</b></td>
                <td align=right><b>" . round($d['tot']['kas']) . "</b></td>
                <td align=right><b>" . round($d['tot']['dibayar']) . "</b></td>
                <td align=right><b>" . round($d['sisa']) . "</b></td>
            </tr>";
        }
        $stream .= "</tbody></table>";

        $xls = new HtmlExcel();
        $xls->addSheet("Angsuran", $stream);
        $xls->headers("Angsuran_" . str_replace('/', '-', $hdr['notransaksi']) . ".xls");
        echo $xls->buildFile();
        exit;

    case 'pdfTrx':
        require_once('lib/fpdf.php');
        $d = dataTransaksiAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $hdr = $d['hdr'];
        $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);

        #teks yang kepanjangan dipotong dan diberi "..." supaya tidak tumpang tindih ke kolom sebelah
        function angFitTeks($pdf, $txt, $w, $f = 8)
        {
            $txt = utf8_decode($txt);
            $pdf->SetFont('Arial', '', $f);
            if ($pdf->GetStringWidth($txt) <= $w - 2) {
                return $txt;
            }
            while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 2) {
                $txt = substr($txt, 0, -1);
            }
            return rtrim($txt) . '...';
        }

        class PDFTrxAngsuran extends FPDF
        {
            public $colW = array();

            function Header()
            {
                global $hdpt;
                $width = $this->w - $this->lMargin - $this->rMargin;
                if (file_exists($hdpt['logo'])) {
                    $this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 18);
                }
                $this->SetFont('Arial', 'B', 11);
                $this->SetXY($this->lMargin + 22, $this->tMargin + 2);
                $this->Cell(150, 6, utf8_decode($hdpt['nama']), 0, 1, 'L');
                $this->SetY($this->tMargin + 15);
                $this->SetFont('Arial', 'B', 13);
                $this->Cell($width, 7, 'ANGSURAN KARYAWAN', 0, 1, 'C');
                $this->SetFont('Arial', '', 8);
                $this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
                $this->Ln(2);
            }

            function Footer()
            {
                $this->SetY(-12);
                $this->SetFont('Arial', 'I', 7);
                $this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
            }
        }

        function angTrxJudul($pdf)
        {
            $pdf->SetFillColor(220, 220, 220);
            $pdf->SetFont('Arial', 'B', 8);
            $judul = array('No', 'Bulan', 'Jadwal Angsuran', 'Potongan Gaji', 'Kas/Bank', 'Total Dibayar', 'Sisa Hutang');
            foreach ($judul as $i => $j) {
                $pdf->Cell($pdf->colW[$i], 6, $j, 1, 0, 'C', true);
            }
            $pdf->Ln();
        }

        $pdf = new PDFTrxAngsuran('P', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(false, 16);
        $pdf->colW = array(10, 25, 31, 31, 31, 31, 31);
        $pdf->AddPage();
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;

        #info transaksi: dua kolom label : nilai
        $kiri = array(
            'No Transaksi' => $hdr['notransaksi'],
            'Nama' => $hdr['namakaryawan'],
            'NIK' => $hdr['nik'],
            'Lokasi Tugas' => $hdr['lokasitugas'],
            'Jenis Angsuran' => $hdr['namajenis'],
            'Tipe Angsuran' => isset($arrtipeangsuran[$hdr['tipe']]) ? $arrtipeangsuran[$hdr['tipe']] : '',
            'Periode' => $hdr['start'] . ' s/d ' . $hdr['end']
        );
        $kanan = array(
            'Total Hutang Awal' => number_format($hdr['total']),
            'Total Hutang' => number_format($hdr['totalpinjaman']),
            'Sudah Dibayar' => number_format($d['tot']['dibayar']),
            'Sisa Hutang' => number_format($d['sisa']),
            'Status' => labelStatusAngsuran($hdr['status']),
            'Dibuat Oleh' => $hdr['pembuat'],
            'Rupiah/Bulan' => number_format($hdr['bulanan'])
        );
        $kunciKiri = array_keys($kiri);
        $kunciKanan = array_keys($kanan);
        $wLabel = 30;
        $wNilai = $width / 2 - $wLabel;
        for ($i = 0; $i < count($kunciKiri); $i++) {
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell($wLabel, 5.5, $kunciKiri[$i], 0, 0, 'L');
            $pdf->Cell($wNilai, 5.5, ': ' . angFitTeks($pdf, $kiri[$kunciKiri[$i]], $wNilai, 8), 0, 0, 'L');
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell($wLabel, 5.5, $kunciKanan[$i], 0, 0, 'L');
            $pdf->Cell($wNilai, 5.5, ': ' . angFitTeks($pdf, $kanan[$kunciKanan[$i]], $wNilai, 8), 0, 1, 'L');
        }
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell($wLabel, 5.5, 'Keterangan', 0, 0, 'L');
        $pdf->Cell($width - $wLabel, 5.5, ': ' . angFitTeks($pdf, $hdr['keterangan'], $width - $wLabel, 8), 0, 1, 'L');
        $pdf->Ln(3);

        angTrxJudul($pdf);
        $h = 6;
        $no = 0;
        foreach ($d['baris'] as $r) {
            $no++;
            if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
                $pdf->AddPage();
                angTrxJudul($pdf);
            }
            $c = $pdf->colW;
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell($c[0], $h, $no, 1, 0, 'C');
            $pdf->Cell($c[1], $h, $r['bulan'], 1, 0, 'C');
            $pdf->Cell($c[2], $h, number_format($r['jadwal']), 1, 0, 'R');
            $pdf->Cell($c[3], $h, number_format($r['gaji']), 1, 0, 'R');
            $pdf->Cell($c[4], $h, number_format($r['kas']), 1, 0, 'R');
            $pdf->Cell($c[5], $h, number_format($r['dibayar']), 1, 0, 'R');
            $pdf->Cell($c[6], $h, number_format($r['sisa']), 1, 1, 'R');
        }
        if ($no == 0) {
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell($width, 8, 'Data tidak ditemukan', 1, 1, 'C');
        } else {
            if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
                $pdf->AddPage();
            }
            $c = $pdf->colW;
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->Cell($c[0] + $c[1], $h, 'Total', 1, 0, 'R');
            $pdf->Cell($c[2], $h, number_format($d['tot']['jadwal']), 1, 0, 'R');
            $pdf->Cell($c[3], $h, number_format($d['tot']['gaji']), 1, 0, 'R');
            $pdf->Cell($c[4], $h, number_format($d['tot']['kas']), 1, 0, 'R');
            $pdf->Cell($c[5], $h, number_format($d['tot']['dibayar']), 1, 0, 'R');
            $pdf->Cell($c[6], $h, number_format($d['sisa']), 1, 1, 'R');
        }
        $pdf->Output();
        exit;

    case 'showupload':
        $hdrFile = barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $param['karyawanid'] = $hdrFile['karyawanid'];
            
        $tab="";
        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
        $tab.="<tr>
                <td>".$_SESSION['lang']['notransaksi']."</td>
                <td>:</td>
                <td>
                    <label id='notransaksix' style='font-weight:bold'>".$param['notransaksi']."</label>
                </td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['nama']."</td>
                <td>:</td>
                <td>
                    <label id='karyawanidx' hidden style='font-weight:bold'>".$param['karyawanid']."</label>
                    <label style='font-weight:bold'>".getNamaKaryawan($param['karyawanid'])."</label>
                </td>
            </tr>";
        $tab.="<tr><td colspan=4><hr></td></tr>
                <tr>
                    <td>Filename</td>
                    <td>:</td>
                    <td>
                        <input type='file' name='upload' id='upload' >
                    </td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td>
                        <button class=mybutton onclick=\"submitfile()\">Submit</button>
                    </td>
                </tr>
            </table>
            <p />";
            
        $tab.="<fieldset>
            <legend>".$_SESSION['lang']['list']."</legend>
            <table class='sortable' cellspacing='1' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <td align='center' width=50px>No.</td>
                    <td align='center' width=50px>File Type</td>
                    <td align='center'>Filename</td>
                    <td align='center' width=50px>Action</td>
                </tr>
                </thead>
                <tbody id='listfiles'>
                </tbody>
            </table>
        </fieldset> ";
            
        echo $tab;
    break;

    case 'loadfiles':
        $hdrFile = barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $param['karyawanid'] = $hdrFile['karyawanid'];
        $no = 0;
        $tab = "";	
        $str="select * from ".$dbname.".listfile_sdm_angsurankaryawan where notransaksi = '".$param['notransaksi']."' and status='1' and karyawanid='".$param['karyawanid']."'";
        $res=fetchData($str);
        if(empty($res)){
            $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }else{
            foreach($res as $key=>$val){
                $no++;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center'>".$no."</td>";
                    
                if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg'){
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
                    </td>";
                }elseif($val['formaticon']=='.png'){
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
                    </td>";
                }elseif($val['formaticon']=='.pdf'){
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
                    </td>";
                }elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx'){
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
                    </td>";
                }elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx'){
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
                    </td>";
                }else{
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
                    </td>";
                }
                
                $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$path.str_replace('/','',$val['namafile'])."')\">".$val['namafile']."</td>
                    <td align=center>
                    <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                
                $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['karyawanid']."','".$val['namafile']."');\" >";
                
                $tab."	</td>
                </tr>";
            }	
        }
        
        echo $tab;
    break;

    case 'deletefile':
        $hdrFile = barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $namafile = basename(isset($param['namafile']) ? $param['namafile'] : '');
        #hanya file yang tercatat untuk transaksi ini yang boleh dihapus
        $cekFile = $owlPDO->prepare("select namafile from ".$dbname.".listfile_sdm_angsurankaryawan where notransaksi=? and status='1' and karyawanid=? and namafile=?");
        $cekFile->execute(array($hdrFile['notransaksi'], $hdrFile['karyawanid'], $namafile));
        if(!$cekFile->fetch()){
            exit("Warning : File tidak ditemukan.");
        }
        try{
            $delRow = $owlPDO->prepare("delete from ".$dbname.".listfile_sdm_angsurankaryawan where notransaksi=? and status='1' and karyawanid=? and namafile=?");
            $delRow->execute(array($hdrFile['notransaksi'], $hdrFile['karyawanid'], $namafile));
            if(is_file($path.$namafile)){
                unlink($path.$namafile);
            }
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;

    case'submitfile':
        $hdrFile = barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $param['karyawanid'] = $hdrFile['karyawanid'];
        $tgl = date("YmdHis");
        $his = date("His");
        $data = $_POST;
        
        if($data['fileupload']!=''){
            if($_FILES['file']['error']==0){	
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $filename = str_replace('/', '-', $param['notransaksi'])."_".$tgl."_".substr(md5(uniqid('', true)), 0, 6).$filetype;
                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	
                
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                    if($_FILES['file']['size'] <= 2500000){
                        $str = "insert into ".$dbname.".listfile_sdm_angsurankaryawan (notransaksi,karyawanid,namafile,formaticon,status,createdby,createdtime) values ('".$param['notransaksi']."','".$param['karyawanid']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
                        if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        }
                        if (file_put_contents($path.$filename,$file_tmpname) === false) {
                            exit("Warning : File gagal disimpan.");
                        }
                        try{
                            $owlPDO->exec($str);
                        }
                        catch(PDOException $e){
                            @unlink($path.$filename);
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }else{
                        exit("warning : Ukuran file upload maksimal 2,5 MB");
                    }
                }else{
                    exit("Warning : Format file upload salah");
                }
            }
        }
    break;

    case'viewfile':
        $tab="";
        $namafile = basename(isset($param['namafile']) ? $param['namafile'] : '');
        #hanya file yang tercatat di angsuran dan boleh diakses user
        $cekFile = $owlPDO->prepare("select notransaksi from ".$dbname.".listfile_sdm_angsurankaryawan where namafile=? and status='1' limit 1");
        $cekFile->execute(array($namafile));
        $barisFile = $cekFile->fetch(PDO::FETCH_ASSOC);
        if(!$barisFile){
            exit("Warning : File tidak ditemukan.");
        }
        barisAngsuran($barisFile['notransaksi']);
        $res[0]['formaticon'] = strtolower('.'.substr($namafile,strripos($namafile,'.')+1));
        
        if($res[0]['formaticon']=='.xls' or $res[0]['formaticon']=='.xlsx' or $res[0]['formaticon']=='.doc' or $res[0]['formaticon']=='.docx'){
            exit("Warning: Tidak bisa ditampilkan, silahkan download.");
        }
        
        if($res[0]['formaticon']=='.pdf'){
            $tab.="<embed src='".$path.$namafile."' style='width:100%;height:97%;' type='application/pdf'>";
        }else{			
            $tab.="<img src='".$path.$namafile."'>";
        }
        
        echo $tab;
    break;	
    
    case 'delete':
        $hdrDel = barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        if ($hdrDel['status'] != '0') {
            exit("Warning : Hanya angsuran yang belum diposting yang bisa dihapus.");
        }
        $owlPDO->beginTransaction();
        try{
            $owlPDO->prepare("Delete FROM " . $dbname . ".sdm_angsurandt WHERE notransaksi=?")->execute(array($hdrDel['notransaksi']));
            $owlPDO->prepare("Delete FROM " . $dbname . ".sdm_angsuran WHERE notransaksi=? and status='0'")->execute(array($hdrDel['notransaksi']));
            $owlPDO->commit();
        }catch (PDOException $e) {
            $owlPDO->rollBack();
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break; 
    case 'deltopup':
        barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $str    = "Delete FROM " . $dbname . ".sdm_angsuran_topup WHERE notransaksi ='".$param['notransaksi']."' and nomotopup='".$param['nomotopup']."'";
        try{
            $owlPDO->exec($str);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break; 
    case 'postdata':
        $hdrPost = barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        if ($hdrPost['status'] != '0') {
            exit("Warning : Angsuran ini sudah diposting.");
        }
        cekBulanTutup(cekAksesKaryawan($hdrPost['karyawanid']), array($hdrPost['start']));
        #detail harus ada dan jumlahnya sama dengan total pinjaman sebelum diposting
        $cekDt = $owlPDO->prepare("select count(*) as jml, coalesce(sum(jumlah),0) as total, coalesce(min(jumlah),0) as terkecil from ".$dbname.".sdm_angsurandt where notransaksi=?");
        $cekDt->execute(array($hdrPost['notransaksi']));
        $rowDt = $cekDt->fetch(PDO::FETCH_ASSOC);
        if (intval($rowDt['jml']) == 0) {
            exit("Warning : Angsuran belum punya detail per bulan.");
        }
        if ($rowDt['terkecil'] < 0) {
            exit("Warning : Ada detail angsuran bernilai negatif.");
        }
        if (abs($rowDt['total'] - $hdrPost['totalpinjaman']) > 0.5) {
            exit("Warning : Total detail (".number_format($rowDt['total'],0).") tidak sama dengan total pinjaman (".number_format($hdrPost['totalpinjaman'],0).").");
        }
        try{
            $owlPDO->prepare("update " . $dbname . ".sdm_angsuran set status='1' WHERE notransaksi=? and status='0'")->execute(array($hdrPost['notransaksi']));
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break; 
    case 'posttopup':
        barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $str="select * from ".$dbname.".sdm_angsuran_topup where nomotopup='".$param['nomotopup']."' and notransaksi='".$param['notransaksi']."'";
        $res=fetchData($str);
        $notransaksi=$res[0]['notransaksi'];
        $nomotopup=$res[0]['nomotopup'];
        $tipetopup=$res[0]['tipetopup'];
        $bulanmulaiperubahan=$res[0]['bulanmulaiperubahan'];
        $bulansampaiangsuran=$res[0]['bulansampaiangsuran'];
        $rupiah=$res[0]['rupiah'];
        $rupiahbulanan=$res[0]['rupiahbulanan'];
        
        if($tipetopup==0){

            $str="select sum(jumlah) as total from ".$dbname.".sdm_angsurandt where notransaksi='".$notransaksi."' and bulan>='".$bulanmulaiperubahan."'";
            $res=fetchdata($str);
            $totalsisahutangsebelumtopup=$res[0]['total'];
            $totalhutangyangakandiubah=$totalsisahutangsebelumtopup+$rupiah;

            
            // echo $totalsisahutangsebelumtopup;
            // exit('Error');
            $totalhutang=$totalhutangyangakandiubah;

            $str    = "Delete FROM " . $dbname . ".sdm_angsurandt WHERE notransaksi ='".$notransaksi."' and bulan>='".$bulanmulaiperubahan."'";
            //echo $str;
            try{
                $owlPDO->exec($str);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

            
            if($param['tipeangsuran']=='angsurannominal'){
                $nilaiperbulan=$rupiahbulanan;
                $jumlahbulan=ceil($totalhutang/$rupiahbulanan)-1;

                $tglx=$bulanmulaiperubahan.'-01';
                $tglxnext = date('Y-m-d', strtotime($tglx . ' +'.$jumlahbulan.' month'));

                $bulansampaiangsuran=substr($tglxnext, 0,7);
            }else{
                $rangebulan = month_inbetween($bulanmulaiperubahan,$bulansampaiangsuran);

                $jumlahbulan=count($rangebulan);
                
                $rpbulan=ceil($totalhutangyangakandiubah/$jumlahbulan);
                if(substr($rpbulan,-2)>0){
                    $pengurang=substr($rpbulan,-2);
                    $rpbulan=$rpbulan-$pengurang;
                }
                $nilaiperbulan=$rpbulan;
            }

            $rangebulan = month_inbetween($bulanmulaiperubahan,$bulansampaiangsuran);
            foreach($rangebulan as $bulan){
                 if($param['tipeangsuran']=='angsurannominal'){
                    if($totalhutangyangakandiubah>$nilaiperbulan){
                        $totalhutangyangakandiubah-=$nilaiperbulan;
                    }else{
                        $nilaiperbulan=$totalhutangyangakandiubah;
                    }
                }else{
                   if($totalhutangyangakandiubah>$nilaiperbulan){
                        if($bulan==$bulansampaiangsuran){
                            $nilaiperbulan=$totalhutangyangakandiubah;
                        }else{
                            $totalhutangyangakandiubah-=$nilaiperbulan;
                        }
                    }else{
                        $nilaiperbulan=$totalhutangyangakandiubah;
                    }
                }

                $insertdt     = "INSERT INTO " . $dbname . ".sdm_angsurandt 
                (`notransaksi`,`bulan`,`jumlah`) VALUES ('" . $notransaksi . "','" . $bulan. "','" . $nilaiperbulan. "')";
                //echo $insertdt;
                try{
                    $owlPDO->exec($insertdt);
                }catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            }


            $str="select totalpinjaman from ".$dbname.".sdm_angsuran where notransaksi='".$notransaksi."'";
            $res=fetchdata($str);
            $totalhutangx=$res[0]['totalpinjaman'];
            $totalhutangxdiubah=$totalhutangx+$rupiah;

            $str    = "update " . $dbname . ".sdm_angsuran set totalpinjaman='".$totalhutangxdiubah."',bulanan='".$rupiahbulanan."',end='".$bulansampaiangsuran."' WHERE notransaksi ='".$notransaksi."'";
            try{
                $owlPDO->exec($str);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }else{
            $str    = "update " . $dbname . ".sdm_angsuran set status='3' WHERE notransaksi ='".$notransaksi."'";
            try{
                $owlPDO->exec($str);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }

        $str    = "update " . $dbname . ".sdm_angsuran_topup set post='1' WHERE nomotopup ='".$param['nomotopup']."' and notransaksi='".$notransaksi."'";
        try{
            $owlPDO->exec($str);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break; 
    case'update':
        $hdr = barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        if ($hdr['status'] == '3') {
            exit("Warning : Angsuran sudah lunas, tidak dapat diubah.");
        }
        if (intval($hdr['karyawanid']) != intval(isset($param['karyawanid']) ? $param['karyawanid'] : 0)) {
            exit("Warning : Karyawan tidak boleh diubah.");
        }
        if (!isset($param['bulansampai']) || !preg_match('/^[0-9]{4}-[0-9]{2}$/', $param['bulansampai']) || empty($param['tipeangsuran'])) {
            exit("Warning : Bulan akhir dan tipe angsuran wajib diisi.");
        }
        #karyawan, jenis, dan bulan awal terkunci saat edit, pakai nilai yang tersimpan
        $param['jenisangsuran'] = $hdr['jenis'];
        $param['bulandari'] = $hdr['start'];
        if ($hdr['status'] == '0') {
            cekBulanTutup(cekAksesKaryawan($hdr['karyawanid']), array($hdr['start']));
        }

        $strApp="select * from ".$dbname.".sdm_angsuran where karyawanid='".$hdr['karyawanid']."' and jenis='".$hdr['jenis']."' and (status='1' or status='0') and notransaksi!='".$hdr['notransaksi']."' ";
        $resApp=fetchData($strApp);
        if(count($resApp) > 0){
            exit("Warning : Sudah ada data yang tersimpan atas karyawan dan jenis angsuran ini yang belum lunas/belum diposting , silahkan edit data yang belum diposting");
        }


        $totalhutang=doubleval(str_replace(',', '', $param['tothutang']));
        $arrdetail=detailAngsuranDariParam($param);
        $totaldetail=array_sum($arrdetail);
        if($totalhutang!=$totaldetail){
            exit("Warning : Total hutang dengan total detail tidak sama dengan varian : ".($totalhutang-$totaldetail)."<br> silahkan dilakukan pengecekan kembali");
        }

        if ($hdr['status'] == '1') {
            #bulan yang periode gajinya sudah diproses tidak boleh berubah (sama dengan input yang dinonaktifkan di form)
            $bulanTutup = array();
            foreach (fetchdata("select distinct periode from ".$dbname.".sdm_5periodegaji where sudahproses='1'") as $barTutup) {
                $bulanTutup[$barTutup['periode']] = 1;
            }
            $detailLama = array();
            foreach (fetchdata("select bulan,jumlah from ".$dbname.".sdm_angsurandt where notransaksi=".$owlPDO->quote($hdr['notransaksi'])) as $barLama) {
                $detailLama[$barLama['bulan']] = doubleval($barLama['jumlah']);
            }
            foreach ($bulanTutup as $bulanX => $tandaX) {
                $adaLama = isset($detailLama[$bulanX]);
                $adaBaru = isset($arrdetail[$bulanX]);
                if ($adaLama != $adaBaru || ($adaLama && abs($detailLama[$bulanX] - $arrdetail[$bulanX]) > 0.005)) {
                    exit("Warning : Bulan ".$bulanX." sudah diproses gaji, nilainya tidak boleh diubah.");
                }
            }
        }

        $notransaksi = $hdr['notransaksi'];
        $owlPDO->beginTransaction();
        try{
            if ($hdr['status'] == '1') {
                $owlPDO->prepare("update " . $dbname . ".sdm_angsuran set `end`=? WHERE notransaksi=?")->execute(array($param['bulansampai'], $notransaksi));
            } else {
                $owlPDO->prepare("Delete FROM " . $dbname . ".sdm_angsuran WHERE notransaksi=?")->execute(array($notransaksi));
                $insertht = $owlPDO->prepare("INSERT INTO " . $dbname . ".sdm_angsuran 
                (`notransaksi`,`karyawanid`,`jenis`,`tipe`,`start`,`end`,`total`,`totalpinjaman`,`bulanan`,`keterangan`,`status`,`updatetime`,`updateby`)
                VALUES (?,?,?,?,?,?,?,?,?,?,'0',?,?)");
                $insertht->execute(array($notransaksi, $hdr['karyawanid'], $hdr['jenis'], $param['tipeangsuran'], $hdr['start'], $param['bulansampai'], doubleval(str_replace(',', '', $param['tothutang'])), doubleval(str_replace(',', '', $param['tothutang'])), doubleval(str_replace(',', '', $param['rpbulan'])), $param['ket'], date('Y-m-d H:i:s'), $_SESSION['standard']['userid']));
            }

            $owlPDO->prepare("Delete FROM " . $dbname . ".sdm_angsurandt WHERE notransaksi=?")->execute(array($notransaksi));
            $insertdt = $owlPDO->prepare("INSERT INTO " . $dbname . ".sdm_angsurandt (`notransaksi`,`bulan`,`jumlah`) VALUES (?,?,?)");
            foreach ($arrdetail as $key => $val) {
                $insertdt->execute(array($notransaksi, $key, $val));
            }
            $owlPDO->commit();
        }catch (PDOException $e) {
            $owlPDO->rollBack();
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
        
    break;

	case 'preview':
        barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $str="select a.*,b.namakaryawan,b.kodeorganisasi from ".$dbname.".sdm_angsuran a 
        left join  ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
        where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        $karyawanid=$res[0]['karyawanid'];
        $komponengaji=$res[0]['jenis'];
        $bulandari=$res[0]['start'];
        $bulansampai=$res[0]['end'];
        $komponenPrev = komponenAngsuranPT($res[0]['kodeorganisasi'], $res[0]['jenis']);
        $arrjenisangsuran = array($res[0]['jenis'] => $komponenPrev['jenisangsuran']);
        $tabx="<legend>Data Angsuran</legend><br><br>
        <table cellspacing=0 border=0 width=100% style='text-align:left'>";
        $tabx.="<tr>";
        $tabx.="<td> No.Transaksi </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['notransaksi']."</td>";
        $tabx.="<td> Nama Karyawan </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['namakaryawan']."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Jenis Angsuran </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$arrjenisangsuran[$res[0]['jenis']]."</td>";
        $tabx.="<td> Tipe Angsuran </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$arrtipeangsuran[$res[0]['tipe']]."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Bulan Awal </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['start']."</td>";
        $tabx.="<td> Bulan Sampai </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['end']."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Total Hutang Awal </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".number_format($res[0]['total'],2)."</td>";
        $tabx.="<td> Total Hutang </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".number_format($res[0]['totalpinjaman'],2)."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Keterangan </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td colspan='4'>".$res[0]['keterangan']."</td>";
        $tabx.="</tr></table>";

        $str="select count(*) as jumlahbulan from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        $jumlahbulan=intval($res[0]['jumlahbulan']);

        $tabx.="<table cellspacing=0 border=0 width=50% style='text-align:left'><thead>";
        $tabx.="<tr>";
        if($jumlahbulan>=5){
            $pembagi=5;
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
        }else{
            $pembagi=$jumlahbulan;
            for ($i=1; $i <=$jumlahbulan; $i++) { 
                $tabx.="<td align=center>No</td>";
                $tabx.="<td align=center>Bulan</td>";
                $tabx.="<td align=center>Rupiah</td>";
            }
        }
        $tabx.="</tr>";
        $tabx.="</thead>";
        $tabx.="<tbody>";
        $no=0;
        $trx=0;

        $str="select * from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $no++;
            if($no%$pembagi==0){
                $trx=$no+1;
            }
            if($no==$trx){
                $tabx.="<tr>";
            }
            $tabx.="<td align=center><b>".$no."</b></td>";
            $tabx.="<td id=bulan_".$no." align=center>".$valx['bulan']."</td>";
            $tabx.="<td align=center>".number_format($valx['jumlah'],0)."</td>";
            
            if($no%$pembagi==0){
                $tabx.="</tr>";
            }

        }

        $tabx.="</tbody>";
        $databayar=array();
        $str="select * from ".$dbname.".sdm_gaji where karyawanid='".$karyawanid."' and idkomponen='".$komponengaji."' 
        and periodegaji>='".$bulandari."' and periodegaji<='".$bulansampai."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $databayar[$valx['periodegaji']]['Gaji']=$valx['jumlah'];
        }

         $str="select * from ".$dbname.".keu_kasbankdt where nodok='".$param['notransaksi']."' and nik='".$karyawanid."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $databayar[substr($valx['tanggal'],0,7)]['Kas/Bank']=$valx['jumlah'];
        }

        $tabx.="</table>";
        $tabx.="<legend>Data Angsuran telah dibayar</legend><br><br>
        <table cellspacing=0 border=0 width=50% style='text-align:left'><thead>";
        $tabx.="<tr>";
        $tabx.="<td>Bulan</td>";
        $tabx.="<td>Jumlah</td>";
        $tabx.="<td>Source</td>";
        $tabx.="</tr>";
        $tabx.="</thead>";
        $tabx.="<tbody>";
        foreach ($databayar as $bulan => $key) {
            foreach ($key as $source => $val) {
                $tabx.="<tr>";
                $tabx.="<td>".$bulan."</td>";
                $tabx.="<td>".$val."</td>";
                $tabx.="<td>".$source."</td>";
                $tabx.="</tr>";
            }
        }

        $tabx.="</tbody>";
        $tabx.="</table>";

        // $arrtipetopup=array('0'=>'Penambahan Hutang','1'=>'Pelunasan Hutang');
        // $tabx.="</table><br><br><legend>List Data Top-up</legend><br><br>
        // <table cellspacing=0 border=1 width=40% style='text-align:center'>";
        // $tabx.="<thead><tr>";
        // $tabx.="<td>No. Top-up</td>";
        // $tabx.="<td>Jenis Top-up</td>";
        // $tabx.="<td>Bulan Mulai Perubahan</td>";
        // $tabx.="<td>Bulan Sampai Angsuran</td>";
        // $tabx.="<td>Rupiah/Bulan</td>";
        // $tabx.="<td>Total Top-up</td>";
        // $tabx.="</tr></thead>";
        // $str="select * from ".$dbname.".sdm_angsuran_topup where notransaksi='".$param['notransaksi']."'";
        // $res=fetchdata($str);
        // foreach($res as $keyx=>$valx){
        //     $tabx.="<tr>";
        //     $tabx.="<td>".$valx['nomotopup']."</td>";
        //     $tabx.="<td>".$arrtipetopup[$valx['tipetopup']]."</td>";
        //     $tabx.="<td>".$valx['bulanmulaiperubahan']."</td>";
        //     $tabx.="<td>".$valx['bulansampaiangsuran']."</td>";
        //     $tabx.="<td>".number_format($valx['rupiahbulanan'])."</td>";
        //     $tabx.="<td>".number_format($valx['rupiah'])."</td>";
        // }
        // $tabx.="</table>";

        echo $tabx;

    break;
    case 'close_a':
        $hdrClose = barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        if ($hdrClose['status'] != '1') {
            exit("Warning : Hanya angsuran yang aktif yang bisa ditutup.");
        }
        
        // ambil periode gaji saat ini
        // $str0="SELECT * FROM ".$dbname.".`sdm_5periodegaji` WHERE 1=1 order by periode desc limit 1 ";
        // $res0=fetchdata($str0);
        // $periode_gaji_0=$res0[0]['periode'];

        // if($param['periodeakhir'] > $periode_gaji_0){
        //     exit("Warning : periode gaji saat ini ".$periode_gaji_0." ");
        // }

        $ubah_d_ = "UPDATE " . $dbname . ".sdm_angsuran SET status='3' WHERE notransaksi= '" . $param['notransaksi'] . "'";
        try {
            $owlPDO->exec($ubah_d_);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;
    case 'topup':
        barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $str="select a.*,b.namakaryawan,b.kodeorganisasi from ".$dbname.".sdm_angsuran a 
        left join  ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
        where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        $karyawanid=$res[0]['karyawanid'];
        $komponengaji=$res[0]['jenis'];
        $tipeangsuran=$res[0]['tipe'];
        $bulandari=$res[0]['start'];
        $bulansampai=$res[0]['end'];


        $arrjenisangsuran=makeOption($dbname,'sdm_angsuran_komponen','komponengaji,jenisangsuran',"kodeorg='".$res[0]['kodeorganisasi']."'");
        $tabx="<legend>Data Angsuran</legend><br><br>
        <table cellspacing=0 border=0 width=100% style='text-align:left'>";
        $tabx.="<tr>";
        $tabx.="<td> No.Transaksi </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['notransaksi']."</td>";
        $tabx.="<td> Nama Karyawan </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['namakaryawan']."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Jenis Angsuran </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$arrjenisangsuran[$res[0]['jenis']]."</td>";
        $tabx.="<td> Tipe Angsuran </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$arrtipeangsuran[$res[0]['tipe']]."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Bulan Awal </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['start']."</td>";
        $tabx.="<td> Bulan Sampai </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td id='bulanakhirsebenarnya'>".$res[0]['end']."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Total Hutang Awal </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".number_format($res[0]['total'],2)."</td>";
        $tabx.="<td> Total Hutang </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".number_format($res[0]['totalpinjaman'],2)."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Keterangan </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td colspan='4'>".$res[0]['keterangan']."</td>";
        $tabx.="</tr></table>";

        $str="select count(*) as jumlahbulan from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        $jumlahbulan=intval($res[0]['jumlahbulan']);

        $tabx.="<table cellspacing=0 border=1 width=50% style='text-align:left'><thead>";
        $tabx.="<tr>";
        if($jumlahbulan>=5){
            $pembagi=5;
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
        }else{
            $pembagi=$jumlahbulan;
            for ($i=1; $i <=$jumlahbulan; $i++) { 
                $tabx.="<td align=center>No</td>";
                $tabx.="<td align=center>Bulan</td>";
                $tabx.="<td align=center>Rupiah</td>";
            }
        }

        $tabx.="</tr>";
        $tabx.="</thead>";
        $tabx.="<tbody>";
        $no=0;
        $trx=0;

        $str="select * from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $no++;
            if($no%$pembagi==0){
                $trx=$no+1;
            }
            if($no==$trx){
                $tabx.="<tr>";
            }
            $tabx.="<td align=center><b>".$no."</b></td>";
            $tabx.="<td id=bulan_".$no." align=center>".$valx['bulan']."</td>";
            $tabx.="<td align=center>".number_format($valx['jumlah'],0)."</td>";
            
            if($no%$pembagi==0){
                $tabx.="</tr>";
            }

        }

        $tabx.="</tbody></table><br><br>";
        $optjenistopup = "<option value=''>Pilih Data</option>";
        $optjenistopup.="<option value='0'>Penambahan Hutang</option>";
        $optjenistopup.="<option value='1'>Pelunasan Hutang</option>";


        $periodemulai='';
        $str="select min(periode) as periodegajimin from ".$dbname.".sdm_5periodegaji where  periode>='".$bulandari."' and periode<='".$bulansampai."' and sudahproses='0' ";
        $res=fetchdata($str);
        $periodemulai=$res[0]['periodegajimin'];

        $optbulan2 = "";
        for ($z = -240; $z <= 0; $z++) {
            $da = mktime(0, 0, 0, substr($bulandari, 5,7) - $z, '1', substr($bulandari, 0,4));
            
            if(date('Y-m', $da)==$bulandari){
                $optbulan2.="<option value='" . date('Y-m', $da) . "' selected>" . date('m-Y', $da) . "</option>";
            }else{
                $optbulan2.="<option value='" . date('Y-m', $da) . "'>" . date('m-Y', $da) . "</option>";
            }
        }

        $optbulan = "<option value=''>Pilih Data</option>";
        if($periodemulai!=''){
            $rangebulan = month_inbetween($periodemulai,$bulansampai);
            $datarange='';
            foreach($rangebulan as $bulan){
                $datarange.="<option value='".$bulan."'>".substr($bulan, 5,7)."-".substr($bulan, 0,4)."</option>";
            }
            for ($z = -240; $z <= 0; $z++) {
                $da = mktime(0, 0, 0, substr($periodemulai, 5,7) - $z, '1', substr($bulandari, 0,4));
                
                if(date('Y-m', $da)==$periodemulai){
                    $optbulan.="<option value='" . date('Y-m', $da) . "'>" . date('m-Y', $da) . "</option>";
                }else{
                    $optbulan.="<option value='" . date('Y-m', $da) . "'>" . date('m-Y', $da) . "</option>";
                }
            }

        }else{
            $optbulan=$optbulan2;
            $rangebulan = month_inbetween($bulandari,$bulansampai);
            $datarange='';
            foreach($rangebulan as $bulan){
                $datarange.="<option value='".$bulan."'>".substr($bulan, 5,7)."-".substr($bulan, 0,4)."</option>";
            }
        }



        $tabx.="<legend>Top-up transaksi</legend><br><br>
        <textarea id='datarange'  style='width:228px;'  onkeypress='return tanpa_kutip(event);' hidden>".$datarange."</textarea>
        <textarea id='datarange2'  style='width:228px;'  onkeypress='return tanpa_kutip(event);' hidden>".$optbulan."</textarea>
        <table cellspacing=0 border=1 width=40% style='text-align:center'>";
        if($tipeangsuran=='angsuranjangkawaktu'){
            $tabx.="<thead><tr>";
            $tabx.="<td>Jenis Top-up</td>";
            $tabx.="<td>Bulan Mulai Perubahan</td>";
            $tabx.="<td id='bulansampaitopupth'>Bulan Sampai Angsuran</td>";
            $tabx.="<td>Total Top-up</td>";
            $tabx.="<td>Action</td>";
            $tabx.="</tr></thead>";
            $tabx.="<tr>";
            $tabx.="<td><select id='jenistopup' name='jenistopup' class='select2' onchange=\"perbuahanjenistopup('".$param['notransaksi']."');\">".$optjenistopup."</select></td>";
            $tabx.="<td><select id='bulanmulaiperubahan' name='bulanmulaiperubahan' class='select2' onchange=\"hitunghutangsisa('".$param['notransaksi']."');\">".$optbulan."</select></td>";
            $tabx.="<td id='bulansampaitopuptd'><select id='bulansampaitopup' name='bulansampai' class='select2'>".$optbulan2."</select></td>";
            $tabx.="<td><input type=text id=tothutangtopup  class=myinputtextnumber   onkeypress=\"return angka_doang(event);\" value=0 onblur=change_number(this)></td>";
            $tabx.="<td hidden><input type=text id=rpbulantopup  class=myinputtextnumber size=9  onkeypress=\"return angka_doang(event);\" value=0 onblur=change_number(this) onkeyup='hitungbulantopup()'></td>";
            $tabx.="<td><img id='detail_add' title='Simpan' class='zImgBtn' onclick=\"addTopup('".$param['notransaksi']."','".$tipeangsuran."');\" src='images/save.png'</td>";
            $tabx.="</tr>";


        }elseif($tipeangsuran=='angsurannominal'){
            $tabx.="<thead><tr>";
            $tabx.="<td>Jenis Top-up</td>";
            $tabx.="<td>Bulan Mulai Perubahan</td>";
            $tabx.="<td>Total Top-up</td>";
            $tabx.="<td>Rupiah/Bulan</td>";
            $tabx.="<td>Action</td>";
            $tabx.="</tr></thead>";
            $tabx.="<tr>";
            $tabx.="<td><select id='jenistopup' name='jenistopup' class='select2' onchange=\"perbuahanjenistopup('".$param['notransaksi']."');\">".$optjenistopup."</select></td>";
            $tabx.="<td><select id='bulanmulaiperubahan' name='bulanmulaiperubahan' class='select2' onchange=\"hitunghutangsisa('".$param['notransaksi']."');\">".$optbulan."</select></td>";
            $tabx.="<td hidden><select id='bulansampaitopup' name='bulansampai' class='select2'>".$optbulan2."</select></td>";
            $tabx.="<td><input type=text id=tothutangtopup  class=myinputtextnumber   onkeypress=\"return angka_doang(event);\" value=0 onblur=change_number(this)></td>";
            $tabx.="<td><input type=text id=rpbulantopup  class=myinputtextnumber size=9  onkeypress=\"return angka_doang(event);\" value=0 onblur=change_number(this) onkeyup='hitungbulantopup()'></td>";
            $tabx.="<td><img id='detail_add' title='Simpan' class='zImgBtn' onclick=\"addTopup('".$param['notransaksi']."','".$tipeangsuran."');\" src='images/save.png'</td>";
            $tabx.="</tr>";
        }
        $arrtipetopup=array('0'=>'Penambahan Hutang','1'=>'Pelunasan Hutang');
        $tabx.="</table><legend>List Data Top-up</legend><br><br>
        <table cellspacing=0 border=1 width=40% style='text-align:center'>";
        $tabx.="<thead><tr>";
        $tabx.="<td>No. Top-up</td>";
        $tabx.="<td>Jenis Top-up</td>";
        $tabx.="<td>Bulan Mulai Perubahan</td>";
        $tabx.="<td>Bulan Sampai Angsuran</td>";
        $tabx.="<td>Rupiah/Bulan</td>";
        $tabx.="<td>Total Top-up</td>";
        $tabx.="<td>Action</td>";
        $tabx.="</tr></thead>";
        $str="select * from ".$dbname.".sdm_angsuran_topup where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $tabx.="<tr>";
            $tabx.="<td>".$valx['nomotopup']."</td>";
            $tabx.="<td>".$arrtipetopup[$valx['tipetopup']]."</td>";
            $tabx.="<td>".$valx['bulanmulaiperubahan']."</td>";
            $tabx.="<td>".$valx['bulansampaiangsuran']."</td>";
            $tabx.="<td>".number_format($valx['rupiahbulanan'])."</td>";
            $tabx.="<td>".number_format($valx['rupiah'])."</td>";
            if($valx['post']==0){
                $tabx.="<td><img src=images/skyblue/posting.png class=zImgBtn  caption='Posting' onclick=\"posttopup('" . $param['notransaksi'] . "','" . $valx['nomotopup'] . "','" . $tipeangsuran . "');\"><br><img src=images/application/application_delete.png class=zImgBtn  caption='Delete TopUp' onclick=\"deltopup('" . $param['notransaksi'] . "','" . $valx['nomotopup'] . "','" . $tipeangsuran . "');\"></td>";
            }else{
                $tabx.="<td><img src=images/skyblue/posted.png class=zImgBtn  caption='Sudah diposting'></td>";
            }
        }
        $tabx.="</table>";
        echo $tabx;

    break;
    case 'hitunghutangsisa':
        barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
    $str="select sum(jumlah) as total from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."' and bulan>='".$param['bulanmulaiperubahan']."'";
    $res=fetchdata($str);

    echo $res[0]['total'];
    break;
    case 'getedit':
        barisAngsuran(isset($param['notransaksi']) ? $param['notransaksi'] : '');
        $str1="select * from ".$dbname.".sdm_angsuran where notransaksi ='".$param['notransaksi']."' ";
        $res1=fetchData($str1);
        $datasend=$res1[0]['notransaksi'].'###'.$res1[0]['karyawanid'].'###'.$res1[0]['jenis'].'###'.$res1[0]['tipe'].'###'.$res1[0]['start'].'###'.$res1[0]['end'].'###'.$res1[0]['totalpinjaman'].'###'.$res1[0]['bulanan'].'###'.$res1[0]['keterangan'];

        $str="select count(*) as jumlahbulan from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        $jumlahbulan=intval($res[0]['jumlahbulan']);

        $datasend2="<thead>";
        $datasend2.="<tr class=rowcontent>";
        if($jumlahbulan>=5){
            $pembagi=5;
            $datasend2.="<td align=center>No</td>";
            $datasend2.="<td align=center>Bulan</td>";
            $datasend2.="<td align=center>Rupiah</td>";
            $datasend2.="<td align=center>No</td>";
            $datasend2.="<td align=center>Bulan</td>";
            $datasend2.="<td align=center>Rupiah</td>";
            $datasend2.="<td align=center>No</td>";
            $datasend2.="<td align=center>Bulan</td>";
            $datasend2.="<td align=center>Rupiah</td>";
            $datasend2.="<td align=center>No</td>";
            $datasend2.="<td align=center>Bulan</td>";
            $datasend2.="<td align=center>Rupiah</td>";
            $datasend2.="<td align=center>No</td>";
            $datasend2.="<td align=center>Bulan</td>";
            $datasend2.="<td align=center>Rupiah</td>";
        }else{
            $pembagi=$jumlahbulan;
            for ($i=1; $i <=$jumlahbulan; $i++) { 
                $datasend2.="<td align=center>No</td>";
                $datasend2.="<td align=center>Bulan</td>";
                $datasend2.="<td align=center>Rupiah</td>";
            }
        }
        $datasend2.="</tr>";
        $datasend2.="</thead>";
        $datasend2.="<tbody>";
        $no=0;
        $trx=0;

        $datagajitutup=array();
        $str="select * from ".$dbname.".sdm_5periodegaji where periode>='".$res1[0]['start']."' and periode<='".$res1[0]['end']."' and sudahproses='1'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $datagajitutup[$valx['periode']]=1;
        }

        $str="select * from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $disabled='';
            if(isset($datagajitutup[$valx['bulan']])){
                $disabled='disabled';
            }
            $no++;
            if($no%$pembagi==0){
                $trx=$no+1;
            }
            if($no==$trx){
                $datasend2.="<tr class=rowcontent>";
            }
            $datasend2.="<td align=center><b>".$no."</b></td>";
            $datasend2.="<td id=bulan_".$no." align=center>".$valx['bulan']."</td>";
            $datasend2.="<td align=center><input id=rpdetailx_".$no." style=width:75px class=myinputtextnumber onkeypress=\"return angka_doang(event);\" value=".number_format($valx['jumlah'],0)." ".$disabled."><input type=hidden id=rpdetail_".$no."></td>";
            
            if($no%$pembagi==0){
                $datasend2.="</tr>";
            }

        }
            $datasend2.="<input hidden id=totaldetail value=".$no."></tbody>";

        $arrlokasitugas=makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas'," karyawanid='".$res1[0]['karyawanid']."'");
        $lokasitugas=$arrlokasitugas[$res1[0]['karyawanid']];
        $arrpt=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk'," kodeorganisasi='".$lokasitugas."'");
        $pt=$arrpt[$lokasitugas];
        
        $str="select komponengaji,jenisangsuran from ".$dbname.".sdm_angsuran_komponen where kodeorg='".$pt."' and status='1'";
        $res=fetchdata($str);
        foreach($res as $val){
            if($res1[0]['jenis']==$val['komponengaji']){
                $optjenisangsuran.="<option value='".$val['komponengaji']."' selected>".$val['jenisangsuran']."</option>";
            }else{
                $optjenisangsuran.="<option value='".$val['komponengaji']."'>".$val['jenisangsuran']."</option>";
            }
        }


        echo $datasend.'$$$'.$datasend2.'$$$'.$optjenisangsuran;
    break;
    case 'pdf':
        $pattern = "/[\s,]+/";//space Regex
        $str="select a.*,b.namakaryawan,b.nik,b.bagian from ".$dbname.".sdm_ijin a 
        left join ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
        where a.notransaksi='".$param['notransaksi']."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $karyawancuti=$bar['karyawanid'];
        $idjenis=$bar['idjenis'];
        $periodecuti=$bar['periodecuti'];
        $keperluan=$bar['keperluan'];
        $jumlahhari=$bar['jumlahhari'];
        $drtglnya=$bar['darijam'];
        $sptglnya=$bar['sampaijam'];
       
        //tanggal pengajuan
        $tglPengajuan = preg_split($pattern, date("d n Y",strtotime($bar['tanggal'])));
        $tglPengajuan[1] = numToMonth($tglPengajuan[1],substr($_SESSION['language'],0,1),'long');
        $tglPengajuan = implode(" ",$tglPengajuan);
        //dari tanggal
        $daritanggal = preg_split($pattern, date("d n Y",strtotime($bar['darijam'])));
        $daritanggal[1] = numToMonth($daritanggal[1],substr($_SESSION['language'],0,1),'long');
        $daritanggal = implode(" ",$daritanggal);

        $sampaitanggal = preg_split($pattern, date("d n Y",strtotime($bar['sampaijam'])));
        $sampaitanggal[1] = numToMonth($sampaitanggal[1],substr($_SESSION['language'],0,1),'long');
        $sampaitanggal = implode(" ",$sampaitanggal);
        
        //$sampaitanggal=substr($bar['sampaijam'],0,10);
        $periodegaji=$bar['periodecuti'];
        $statuscuti=$bar['statuspersetujuan'];
        $statusbatalcuti=$bar['statuspersetujuan_cancel'];
        $statuspotongan=$bar['statuspotongan'];
        $potonggaji=$bar['potonggaji'];
        

        $hakcutiawal=0;
        $diambilsebelumnya=0;
        
        if($statuspotongan=='1' or $statuspotongan=='2'){
            $str="select * from ".$dbname.".sdm_cutiht where karyawanid ='".$karyawancuti."' and periodecuti='".$periodecuti."'";
            $res=fetchData($str);
            if($res[0]['hakcuti']>0){
                $hakcutiawal=$res[0]['hakcuti'];
            }

            $str="select sum(jumlahhari) as diambil from ".$dbname.".sdm_ijin where karyawanid ='".$karyawancuti."' and statuspotongan in (1,2) and tanggal<='".$bar['tanggal']."' and notransaksi!='".$param['notransaksi']."' and statuspersetujuan!=2 and statuspersetujuan_cancel!=1";
            $res=fetchData($str);
            if($res[0]['diambil']>0){
                $diambilsebelumnya=$res[0]['diambil'];
            }
        }
        

        

        

        // echo $arrdataperiode[$karyawancuti];
        // exit();
        $sisa=$hakcutiawal-$diambilsebelumnya;
        
        $sisaAfter = $sisa-$jumlahhari;
        
        
        $arrjenis = makeOption($dbname,'sdm_5jenisijin','idjenis,jenisijin',"idjenis='".$idjenis."'");

        $string = "SELECT karyawanid FROM ".$dbname.".`approval` WHERE `notransaksi` = '".$param['notransaksi']."' AND `status` = '1'";
        $hasil = fetchdata($string);

        $ttdpembuat = makeOption($dbname,'setup_ttd','karyawanid,file',"kode='TTD'");

        $style = "style='border:1px solid #000;'";
        $border = "style='border:1px solid #000;'";
        $tab="<style>table{border:1px solid #000;}th{padding:10px;}th.right{border-left:1px solid #000;}td{padding:15px;}th{padding:15px;}
        td.isi{padding:5px 15px;}.title{font-size:25px;}
        td.ttd{height:70px;border-bottom:solid 1px #000;}td.jedattd{height:2%;}td.batas{width:10px;}</style>";
        $tab .="<table class=sortable cellspacing=0 border=0 width='100%'>
                    <thead ".$border.">
                         <tr class=rowcontent>
                            <th colspan='14' align=center ".$border." class='title'><b>Leave Application Form</b></th>
                        </tr>
                         <tr class=rowcontent >
                            <th width='5%'></th>
                            <th colspan='6'>Name: ".@$bar['namakaryawan']."</th>
                            <th colspan='6' class=right>Bagian: ".@$bar['bagian']."</th>
                            <th width='5%'></th>
                        </tr>
                    </thead>
                    <thead ".$border.">
                        <tr class=rowcontent>
                            <th></th>
                            <th colspan='6'>NIK: ".@$bar['nik']."</th>
                            <th colspan='6' class=right>Form Submission Date: ".@$tglPengajuan."</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                         <tr class=rowcontent>
                            <td colspan='14' class='jedattd'></td>
                        </tr>";

                        if($statuspotongan==1 or $statuspotongan==2){
                        $tab .="<tr class=rowcontent>
                            <td></td>
                            <td colspan='12' class='isi'>Balance of Annual Leave: ".$sisa." days</td>
                            <td></td>
                        </tr>";

                        }
                        $tab .="<tr class=rowcontent>
                            <td></td>
                            <td colspan='6' class='isi'>Date of Leave: <br><b>".$daritanggal."-".$sampaitanggal."</b></td>
                            <td colspan='6' class='isi'>/ ".$jumlahhari." days</td>
                            <td></td>
                        </tr>
                        <tr class=rowcontent>
                            <td></td>
                            <td colspan='6' class='isi'>Reason: <b>".$keperluan."</b></td>
                            <td colspan='6' class='isi'></td>
                            <td></td>
                        </tr>
                        <tr class=rowcontent>
                            <td></td>
                            <td colspan='12'><u>".$arrjenis[$idjenis]."</u></td>
                            <td></td>
                        </tr>
                        ";

                        if($statuspotongan==1 or $statuspotongan==2){
                        $tab .="<tr class=rowcontent>
                            <td></td>
                            <td colspan='6'><b>Balance After: ".$sisaAfter." days</b></td>
                            <td colspan='6'></td>
                            <td></td>
                        </tr>
                        ";

                        }
                        
                       
                $tab.=" 
                    </tbody>
                </table>";
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($tab);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("Pengobatan", array("Attachment" => false));
    break;
    
   

}




?>