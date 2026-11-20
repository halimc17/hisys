<?php
# CABANG PPP dari keu_slave_3pengakuanPotongan.php (Proses Akhir Bulan > 5. Penarikan Potongan Gaji Karyawan). Dipanggil otomatis oleh js/keu_3posting.js (tujuanProses)
# hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_3pengakuanPotongan.php. Perubahan khusus PPP tandai "#PPP".
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
require_once('lib/zAlokasiGajiPPP.php'); #PPP: validasi parameter
#cek proses gaji tidak langsung sudah diproses atau belum





#PPP: format unit dan periode divalidasi sebelum dipakai di query; array diinisialisasi (PHP 8)
$pesanParam = validasiParamGajiPPP($_POST);
if ($pesanParam == '' && (!isset($_POST['kodeorg']) || $_POST['kodeorg'] === '' || !isset($_POST['periode']) || $_POST['periode'] === '')) {
	$pesanParam = 'Warning : Unit dan periode wajib diisi.';
}
if ($pesanParam == '' && isset($_POST['method']) && !in_array($_POST['method'], array('', 'post'), true)) {
	$pesanParam = 'Warning : Parameter method tidak valid.';
}
if ($pesanParam != '') {
	exit($pesanParam);
}
$tipeorg = array();
$arrAkun = array();
$namakomponen = array();
$debet = array();
$kredit = array();
$kelompok = array();
$blok = array();
$total = array();
$nama = array();
$rinci = array();
$dtGapok = array();
$headErr = '';

#= bentuk tipeorganisasi	
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)=4";
$res=$owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
   $tipeorg[$bar['kodeorganisasi']]=$bar['tipe'];
}
if (!isset($tipeorg[$_POST['kodeorg']])) {
	exit('Warning : Unit ' . htmlspecialchars($_POST['kodeorg']) . ' tidak ditemukan.');
}




if($tipeorg[$_POST['kodeorg']]!="HOLDING" and $tipeorg[$_POST['kodeorg']]!="BULKING"){
	if($tipeorg[$_POST['kodeorg']]=="RND" or $tipeorg[$_POST['kodeorg']]=="TC"){
		$wh=" and nojurnal like '%RNDB%'";
	}else{
		$wh=" and (nojurnal like '%KBNB%' or nojurnal like '%GJH%')";
	}
	
	$sCek="select * from ".$dbname.".keu_jurnalht where 1=1 ".$wh." and ".sqlRentangPeriode($_POST['periode'])." and nojurnal like '%".$_POST['kodeorg']."%'";
	$rCek=fetchdata($sCek);
	if(count($rCek)==0){
		exit('warning: Jalankan Proses Gaji Karyawan Tidak Langsung');
	}
}
if($tipeorg[$_POST['kodeorg']]=="KEBUN"){
    $sCek="select * from ".$dbname.".keu_jurnalht where 
           (nojurnal like '%M0%' or nojurnal like '%M1%' or nojurnal like '%PKS01%' or nojurnal like '%PKS02%') 
		   and ".sqlRentangPeriode($_POST['periode'])." and nojurnal like '%".$_POST['kodeorg']."%'";
  $rCek=fetchdata($sCek);
  if(count($rCek)==0){
    exit('warning: Jalankan Proses Gaji Karyawan Langsung');
  }
}
#kamus akun
if($_SESSION['language']=='EN'){
    $zz='namaakun1 as namaakun';
}else{
    $zz='namaakun';
}
$str="select noakun,".$zz." from ".$dbname.".keu_5akun where length(noakun)=7 order by namaakun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $arrAkun[$bar->noakun]=$bar->namaakun;
}
#kamus komponen
$sAkun="select  id,name from ".$dbname.".sdm_ho_component where plus=0 order by name";
$res=$owlPDO->query($sAkun) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rAkun=$res->fetch())
{
    $namakomponen[$rAkun['id']]=$rAkun['name'];
}
$nodata=0;
#ambil  noakun debet dan kredit dari setup keu_5pegakuanpotongan
$str="select * from ".$dbname.".keu_5pengakuanpotongan where tipeorganisasi='".$tipeorg[$_POST['kodeorg']]."' order by idkomponen";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	@$nodata++;
    $debet[$bar->idkomponen]=$bar->noakundebet;
    $kredit[$bar->idkomponen]=$bar->noakunkredit;
    $kelompok[$bar->idkomponen]=substr($bar->noakunkredit,0,3);#ini juga tambahan untuk blok

    if($bar->noakundebet=='' or $bar->noakundebet=='')
    {
        // exit(' Error: Setup account number debet/kredit for component '.$bar->idkomponen.' not defined');
		exit("Warning: Maping potongan untuk tipe organisasi ".$tipeorg[$_POST['kodeorg']]." belum disetting, hubungi akunting RO
		untuk melakukan setting di menu : keuangan->setup->maping potongan gaji");
    }
	
}
if($nodata==0){
	exit("Warning: Maping potongan untuk tipe organisasi ".$tipeorg[$_POST['kodeorg']]." belum disetting, hubungi akunting RO
		untuk melakukan setting di menu : keuangan->setup->maping potongan gaji");
}

#==========bahan dasar pengambilan blok TBM,TM,PNN
#perawatan:
$str="select distinct karyawanid,kodeorg,left(kodekegiatan,3) as kelompok from ".$dbname.".kebun_kehadiran_vw
      where kodekegiatan like '126%' or kodekegiatan like '128%' or kodekegiatan like '621%'
      and ".sqlRentangPeriode($_POST['periode'])." and kodeorg like '".$_POST['kodeorg']."%'";
	  // exit("Error:$str");
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $blok[$bar->karyawanid][$bar->kelompok]=$bar->kodeorg;
}

$str="select distinct karyawanid,kodeorg from ".$dbname.".kebun_prestasi_vw
      where ".sqlRentangPeriode($_POST['periode'])." and kodeorg like '".$_POST['kodeorg']."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $blok[$bar->karyawanid]['611']=$bar->kodeorg;
}

#===================end pengambilan blok

$tanggal=  str_replace("-", "",$_POST['periode'])."28";

#$str=" select a.idkomponen,a.karyawanid,a.jumlah,b.namakaryawan from ".$dbname.".sdm_gaji a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.kodeorg='".$_POST['kodeorg']."' and a.periodegaji='".$_POST['periode']."' and a.idkomponen in (select idkomponen from ".$dbname.".keu_5pengakuanpotongan)";

$str=" select a.idkomponen,a.karyawanid,a.jumlah,b.namakaryawan from ".$dbname.".sdm_gaji a
   left join ".$dbname.".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.periodegaji='".$_POST['periode']."' where b.version_type = 'B' and a.kodeorg='".$_POST['kodeorg']."' and a.periodegaji='".$_POST['periode']."' and a.idkomponen in (select idkomponen from ".$dbname.".keu_5pengakuanpotongan)";
   
// echo $str;
		   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    if(!isset($total[$bar->idkomponen])) $total[$bar->idkomponen]=0;
    $total[$bar->idkomponen]+=$bar->jumlah;
    $nama[$bar->karyawanid]=$bar->namakaryawan;
    $rinci[$bar->idkomponen][$bar->karyawanid]=$bar->jumlah;
}
#penambahan gapok untuk perhitungan jamsostek porsi perusahaan
$strGapok=" select idkomponen,karyawanid,jumlah from ".$dbname.".sdm_gaji where kodeorg='".$_POST['kodeorg']."' 
           and periodegaji='".$_POST['periode']."' and idkomponen=1";
$res=$owlPDO->query($strGapok) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $dtGapok[$bar->karyawanid]=$bar->jumlah;
}
#pengambilan persen porsi perusahaan
#jamhari 10-april-2015 penambahan jurnal porsi perusahaan
$persenJamsostek=array();
$loksi='PABRIK';
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
        $loksi='KEBUN';
}
$sPersn="select jenisbpjs,bebanperusahaan from ".$dbname.".sdm_5bpjs where lokasibpjs='".$loksi."'";
$res=$owlPDO->query($sPersn) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rPersn=$res->fetch()){
        if($rPersn['jenisbpjs']=='44'){
                $persenJamsostek[44]=$rPersn['bebanperusahaan'];		
                $awlJrn[44]='002';
                $ketPersn[44]=strtoupper($rPersn['jenisbpjs']);
        }
        if($rPersn['jenisbpjs']=='3'){
                $persenJamsostek[3]=$rPersn['bebanperusahaan'];
                $awlJrn[3]='001';
                $ketPersn[3]=strtoupper($rPersn['jenisbpjs']);
        }
        if($rPersn['jenisbpjs']=='81'){
                $persenJamsostek[81]=$rPersn['bebanperusahaan'];
                $awlJrn[81]='003';
                $ketPersn[81]=strtoupper($rPersn['jenisbpjs']);
        }

}

$jamPrhsn=0;



// Default Segment
$defSegment = colDefaultValue($dbname,'keu_5segment','kodesegment');

if(empty($total))
    exit('Error: No salary data found');
elseif(isset($_POST['method']) and $_POST['method']=='post'){
	
	
	#PPP: periode akuntansi diperiksa SEBELUM menghapus jurnal lama (dulu dihapus dulu, baru dicek, sehingga periode
	#yang sudah tutup buku bisa kehilangan jurnal potongannya); seluruh proses dalam satu transaksi
	$str="select * from ".$dbname.".setup_periodeakuntansi where
	             kodeorg ='".$_POST['kodeorg']."' and tutupbuku=0 and periode='".$_POST['periode']."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$numrows=owlBaris($res);
	if($numrows<1)
	{
	    exit("Error: Accounting has closed transaction of  ".$_POST['periode']);
	}

	$jalankanPPP = function ($query) use ($owlPDO) {
		$hasil = $owlPDO->exec($query);
		if ($hasil === false) {
			$err = $owlPDO->errorInfo();
			throw new Exception(isset($err[2]) ? $err[2] : 'perintah database gagal');
		}
		return $hasil;
	};

	try {
	$owlPDO->beginTransaction();

	#= delete semua query alk_pot
	$nojurnaldelete=$tanggal."/".$_POST['kodeorg']."/POT";
	$str="delete from ".$dbname.".keu_jurnalht where kodejurnal='POT' and tanggal='".$tanggal."' and nojurnal like '".$nojurnaldelete."%'  ";
	$jalankanPPP($str);
        foreach($total as $komponen =>$ttl)
        {
                #bersihkan detail
                $dataRes['detail']=array();
                $dataRes['header']=array();
                $totalJamsostek=0;
                $nojurnal2="";//untuk jurnal jamsostek porsi PT
                #buat nourut
                $noUrut=0;                
                $noUrut++;

                #setup nojurnal
                $nojurnal=$tanggal."/".$_POST['kodeorg']."/POT/".$komponen;
                $ttl=str_replace(",","",$ttl);
                #======================== /Nomor Jurnal ============================
                # Prep Header
                $dataRes['header'][]  = array(
                        'nojurnal'=>$nojurnal,
                        'kodejurnal'=>'POT',
                        'tanggal'=>$tanggal,
                        'tanggalentry'=>date('Ymd'),
                        'posting'=>1,
                        'totaldebet'=>$ttl,
                        'totalkredit'=>-1*$ttl,
                        'amountkoreksi'=>'0',
                        'noreferensi'=>'ALK_POT:'.$komponen,
                        'autojurnal'=>'1',
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'revisi'=>'0'
                );
                # Data Detail
                # Debet
                $dataRes['detail'][] = array(
                        'nojurnal'=>$nojurnal,
                        'tanggal'=>$tanggal,
                        'nourut'=>$noUrut,
                        'noakun'=>$debet[$komponen],
                        'keterangan'=> $namakomponen[$komponen],
                        'jumlah'=>$ttl,
                        'matauang'=>'IDR',
                        'kurs'=>'1',
                        'kodeorg'=>$_POST['kodeorg'],
                        'kodekegiatan'=>'',
                        'kodeasset'=>'',
                        'kodebarang'=>'',
                        'nik'=>'',
                        'kodecustomer'=>'',
                        'kodesupplier'=>'',
                        'noreferensi'=>'ALK_POT:'.$komponen,
                        'noaruskas'=>'',
                        'kodevhc'=>'',
                        'nodok'=>$komponen,
                        'kodeblok'=>'',
                        'revisi'=>'0',
                   'kodesegment'=>$defSegment
                );

                foreach($rinci[$komponen] as $karid =>$jlhperorang) {
                        $noUrut++;
                #tambahan untuk kode blok------------	
                        if(isset($blok[$karid][$kelompok[$komponen]])){
                            $kodeblok=$blok[$karid][$kelompok[$komponen]];
                        }else{
                          $kodeblok=''; 
                        }	
                #========end tambahan kode blok	
                        # Kredit
                        $dataRes['detail'][] = array(
                                'nojurnal'=>$nojurnal,
                                'tanggal'=>$tanggal,
                                'nourut'=>$noUrut,
                                'noakun'=>$kredit[$komponen],
                                'keterangan'=> $namakomponen[$komponen].": ".$nama[$karid],
                                'jumlah'=>-1*$jlhperorang,
                                'matauang'=>'IDR',
                                'kurs'=>'1',
                                'kodeorg'=>$_POST['kodeorg'],
                                'kodekegiatan'=>'',
                                'kodeasset'=>'',
                                'kodebarang'=>'',
                                'nik'=>$karid,
                                'kodecustomer'=>'',
                                'kodesupplier'=>'',
                                'noreferensi'=>'ALK_POT:'.$komponen,
                                'noaruskas'=>'',
                                'kodevhc'=>'',
                                'nodok'=>$komponen,
                                'kodeblok'=>$kodeblok,
                                'revisi'=>'0',
                           'kodesegment'=>$defSegment
                        );

                        #jika potongan jamsoste ditambahkan perhitungan jamsostek perusahan per kary
                        if(($komponen==3)||($komponen==44)||($komponen==81)){
                                @$jamPrhsn=$dtGapok[$karid]*$persenJamsostek[$komponen]/100;
                                $totalJamsostek+=$jamPrhsn;
                        }
                }
				/*
                if(($komponen==3)||($komponen==44)||($komponen==81)){
                        $noUrut2=0;
                        $noUrut2++;
                        $sJrnid="select jurnalid,noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='SDMJP'";
                        $qJrnid=$owlPDO->query($sJrnid) or die(print " Gagal: ".PDOException::getMessage());
                        $qJrnid->setFetchMode(PDO::FETCH_ASSOC);
                        $rJrnid=$qJrnid->fetch();
                                        $nojurnal2=$tanggal."/".$_SESSION['empl']['lokasitugas']."/".$rJrnid['jurnalid']."/".$awlJrn[$komponen];

                                        #======================== /Nomor Jurnal ============================
                                        # Prep Header
                                        $dataRes['header'][] = array(
                                                'nojurnal'=>$nojurnal2,
                                                'kodejurnal'=>$rJrnid['jurnalid'],
                                                'tanggal'=>$tanggal,
                                                'tanggalentry'=>date('Ymd'),
                                                'posting'=>1,
                                                'totaldebet'=>$totalJamsostek,
                                                'totalkredit'=>-1*$totalJamsostek,
                                                'amountkoreksi'=>'0',
                                                'noreferensi'=>'ALK_POT:'.$komponen,
                                                'autojurnal'=>'1',
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'revisi'=>'0'
                                        ); 

                                        # Data Detail
                                        # Debet
                                        $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal2,
                                                'tanggal'=>$tanggal,
                                                'nourut'=>$noUrut2,
                                                'noakun'=>$rJrnid['noakundebet'],
                                                'keterangan'=> "BPJS ".$ketPersn[$komponen]." : PORSI PT UNIT ".$_SESSION['empl']['lokasitugas'],
                                                'jumlah'=>$totalJamsostek,
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$_SESSION['empl']['lokasitugas'],
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>'',
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>'ALK_POT:'.$komponen,
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>'',
                                                'kodeblok'=>'',
                                                'revisi'=>'0',
                                           'kodesegment'=>$defSegment
                                        );
                        $noUrut2++;
                                        # Kredit
                                        $dataRes['detail'][] = array(
                                                'nojurnal'=>$nojurnal2,
                                                'tanggal'=>$tanggal,
                                                'nourut'=>$noUrut2,
                                                'noakun'=>$rJrnid['noakunkredit'],
                                                'keterangan'=> "BPJS ".$ketPersn[$komponen]."  : PORSI PT UNIT ".$_SESSION['empl']['lokasitugas'],
                                                'jumlah'=>-1*$totalJamsostek,
                                                'matauang'=>'IDR',
                                                'kurs'=>'1',
                                                'kodeorg'=>$_SESSION['empl']['lokasitugas'],
                                                'kodekegiatan'=>'',
                                                'kodeasset'=>'',
                                                'kodebarang'=>'',
                                                'nik'=>'',
                                                'kodecustomer'=>'',
                                                'kodesupplier'=>'',
                                                'noreferensi'=>'ALK_POT:'.$komponen,
                                                'noaruskas'=>'',
                                                'kodevhc'=>'',
                                                'nodok'=>'',
                                                'kodeblok'=>'',
                                                'revisi'=>'0',
                                           'kodesegment'=>$defSegment
                                        );
                }    
				*/
                #hapus dulu yang lama
                $jalankanPPP(deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'"));
                if($nojurnal2!=''){
                        $jalankanPPP(deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal2."'"));
                }
                #=====================execute (gagal di mana pun -> semua dibatalkan)
                foreach($dataRes['header'] as $row) {
                        $jalankanPPP(insertQuery($dbname,'keu_jurnalht',$row));
                }
                foreach($dataRes['detail'] as $row) {
                        $jalankanPPP(insertQuery($dbname,'keu_jurnaldt',$row));
                }
                #====================end excute
        }//end for total
	$owlPDO->commit();
	} catch (Exception $e) {
		$owlPDO->rollBack();
		echo "Error, ".addslashes($e->getMessage());
		exit;
	}
} else {
	#PPP: data detail karyawan untuk tampilan (histori periode kalau ada, kalau tidak data master karyawan)
	$infoKar = array();
	foreach (array_chunk(array_keys($nama), 400) as $potong) {
		$in = "'" . implode("','", array_map('addslashes', $potong)) . "'";
		foreach (fetchData("select karyawanid,nik,kodejabatan,kodegolongan,tipekaryawan,subbagian from ".$dbname.".datakaryawan_hist
				where karyawanid in (".$in.") and periodegaji='".$_POST['periode']."' and version_type='B' and lokasitugas='".$_POST['kodeorg']."'") as $barK) {
			$infoKar[$barK['karyawanid']] = $barK;
		}
		foreach (fetchData("select karyawanid,nik,kodejabatan,kodegolongan,tipekaryawan,subbagian from ".$dbname.".datakaryawan where karyawanid in (".$in.")") as $barK) {
			if (!isset($infoKar[$barK['karyawanid']])) {
				$infoKar[$barK['karyawanid']] = $barK;
			}
		}
	}
	$nmJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
	$nmGolongan = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
	$nmTipeKar = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
	$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	$tipeUnit = htmlspecialchars($tipeorg[$_POST['kodeorg']]);

	$barisTab = "";
	$no = 0;
	$ttlDebet = 0;
	$ttlKredit = 0;
	$barisNol = 0;
	foreach ($total as $komponen => $ttl) {
		$no++;
		$ttlDebet += $ttl;
		$barisTab .= "<tr class=rowcontent id='row".$no."'>
				<td>".$no."</td>
				<td>".htmlspecialchars($_POST['periode'])."</td>
				<td></td><td></td><td></td><td></td><td></td><td></td>
				<td>".htmlspecialchars(isset($namakomponen[$komponen]) ? $namakomponen[$komponen] : $komponen)."</td>
				<td>".htmlspecialchars($debet[$komponen])."</td>
				<td>".htmlspecialchars(isset($arrAkun[$debet[$komponen]]) ? $arrAkun[$debet[$komponen]] : '')."</td>
				<td align=right>".number_format($ttl, 2)."</td>
				<td align=right>0.00</td>
			</tr>";
		#loop per orangnya:
		foreach ($rinci[$komponen] as $karid => $jlhperorang) {
			if ($jlhperorang == 0) {
				$barisNol++;
				continue; #potongan bernilai nol tidak ditampilkan
			}
			$no++;
			$ttlKredit += $jlhperorang;
			$kr = isset($infoKar[$karid]) ? $infoKar[$karid] : array();
			$subbag = isset($kr['subbagian']) ? $kr['subbagian'] : '';
			$divisi = ($subbag != '') ? (isset($nmOrg[$subbag]) ? $nmOrg[$subbag] : $subbag) : 'KANTOR';
			$barisTab .= "<tr class=rowcontent id='row".$no."'>
				<td>".$no."</td>
				<td>".htmlspecialchars($_POST['periode'])."</td>
				<td>".htmlspecialchars(isset($kr['nik']) ? $kr['nik'] : '')."</td>
				<td>".htmlspecialchars(isset($nama[$karid]) ? $nama[$karid] : '')."</td>
				<td>".htmlspecialchars((isset($kr['kodejabatan']) && isset($nmJabatan[$kr['kodejabatan']])) ? $nmJabatan[$kr['kodejabatan']] : '')."</td>
				<td>".htmlspecialchars((isset($kr['kodegolongan']) && isset($nmGolongan[$kr['kodegolongan']])) ? $nmGolongan[$kr['kodegolongan']] : '')."</td>
				<td>".htmlspecialchars((isset($kr['tipekaryawan']) && isset($nmTipeKar[$kr['tipekaryawan']])) ? $nmTipeKar[$kr['tipekaryawan']] : '')."</td>
				<td>".htmlspecialchars($divisi)."</td>
				<td>".htmlspecialchars(isset($namakomponen[$komponen]) ? $namakomponen[$komponen] : $komponen)."</td>
				<td>".htmlspecialchars($kredit[$komponen])."</td>
				<td>".htmlspecialchars(isset($arrAkun[$kredit[$komponen]]) ? $arrAkun[$kredit[$komponen]] : '')."</td>
				<td align=right>0.00</td>
				<td align=right>".number_format($jlhperorang, 2)."</td>
			</tr>";
		}
	}

	echo "
		<fieldset style='margin-bottom:8px'><legend><b>Informasi Proses</b></legend>
		<div style='display:flex;flex-wrap:wrap;align-items:stretch;gap:6px;font-size:12px'>
			<div style='flex:1 1 210px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>1. Ambil potongan</b><br>Potongan gaji karyawan unit ".htmlspecialchars($_POST['kodeorg'])." periode ".htmlspecialchars($_POST['periode'])." untuk komponen yang diatur di <b>Mapping Pot Gaji Kar</b> tipe organisasi <b>".$tipeUnit."</b>.</div>
			<div style='align-self:center;font-size:18px'>&rarr;</div>
			<div style='flex:1 1 260px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>2. Buat jurnal per komponen</b><br>Satu jurnal <b>POT</b> per komponen: <b>Debet</b> akun debet mapping sebesar total komponen, <b>Kredit</b> akun kredit mapping untuk tiap karyawan (kode blok diambil dari BKM/prestasi karyawan bila ada).</div>
		</div>
		<div style='font-size:12px;margin-top:6px;line-height:1.5'>
			&bull; Baris pertama tiap komponen adalah debetnya; baris di bawahnya kredit per karyawan. Potongan bernilai nol tidak ditampilkan".($barisNol > 0 ? " (".$barisNol." baris)" : "").".<br>
			&bull; Akun debet dan kredit diatur di menu <i>Keuangan &rarr; Setup &rarr; Mapping Pot Gaji Kar</i>.<br>
			<span style='background:#fff3cd;padding:1px 6px;border:1px solid #e0c36a'><b>Perhatian:</b> Process ulang untuk periode yang sama menghapus jurnal potongan (POT) periode itu lalu membuatnya lagi. Kalau periode akuntansi sudah tutup buku, tidak ada yang dihapus.</span>
		</div>
		</fieldset>
		<style>
			.tombolPotPPP { display:inline-block; padding:7px 22px; margin:0 8px 8px 0; border-radius:6px; font-size:13px; font-weight:bold; cursor:pointer; }
			#btnproses.tombolPotPPP { background:#2e7d32; color:#fff; border:1px solid #1b5e20; }
			#btnproses.tombolPotPPP:hover { background:#388e3c; }
			#btnproses.tombolPotPPP:disabled { background:#9e9e9e; border-color:#757575; color:#eee; cursor:not-allowed; }
			#btnexcel.tombolPotPPP { background:#fff; color:#2e7d32; border:1px solid #2e7d32; }
			#btnexcel.tombolPotPPP:hover { background:#e8f5e9; }
		</style>
		<div>
			<button class='tombolPotPPP' onclick=prosesPotongan('".htmlspecialchars($_POST['periode'])."') id=btnproses title='Buat jurnal potongan untuk semua baris di bawah'>&#9654; Process</button>
			<button class='tombolPotPPP' onclick=exportTableToExcel() id=btnexcel title='Unduh tabel ini ke Excel'>&#11015; Excel</button>
		</div>
		<table class=sortable cellpadding=5 cellspacing=1 border=0 id=mytable>
			<thead>
				<tr class=rowheader>
					<th>No</th>
					<th>".$_SESSION['lang']['periode']."</th>
					<th>NIK</th>
					<th>Nama Karyawan</th>
					<th>Jabatan</th>
					<th>Golongan</th>
					<th>Tipe Karyawan</th>
					<th>Divisi</th>
					<th>Komponen</th>
					<th>".$_SESSION['lang']['noakun']."</th>
					<th>".$_SESSION['lang']['namaakun']."</th>
					<th>".$_SESSION['lang']['debet']."</th>
					<th>".$_SESSION['lang']['kredit']."</th>
				</tr>
			</thead>
			<tbody>".$barisTab."
				<tr class=rowcontent>
					<td colspan=11>Total</td>
					<td align=right>".number_format($ttlDebet, 2)."</td>
					<td align=right>".number_format($ttlKredit, 2)."</td>
				</tr>
			</tbody><tfoot></tfoot>
		</table>";
}
#----------------------------------------------------------------
?>