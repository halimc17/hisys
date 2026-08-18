<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$unit = $_POST['unit'];
$periode = $_POST['periode'];
$periode2 = $_POST['periode2'];
$jenis = $_POST['jenis'];
$kodebarang = $_POST['kodebarang'];

$kamusjenis['0'] = 'Mutasi dalam perjalanan';
$kamusjenis['1'] = 'Penerimaan';
$kamusjenis['2'] = 'Pengembalian pengeluaran';
$kamusjenis['3'] = 'Penerimaan mutasi';
$kamusjenis['4'] = 'Penerimaan Adjustment';
$kamusjenis['5'] = 'Pengeluaran';
$kamusjenis['6'] = 'Pengembalian penerimaan';
$kamusjenis['7'] = 'Pengeluaran mutasi';
$kamusjenis['8'] = 'Pengeluaran Adjustment';
$path   = "fileupload/log_penerimaanx/";
if ($unit == '') {
    echo "Warning: Period is missing";
    exit;
}
if ($periode == '') {
    echo "Warning: Period is missing";
    exit;
}
if ($jenis == '') {
    echo "Warning: trancstion type is missing";
    exit;
}

$optnmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$optkodekenadraan=makeOption($dbname,'vhc_5master','kodevhc,nopol');
$optkodekenadraanD=makeOption($dbname,'vhc_5master','kodevhc,detailvhc');
$optorganisasi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$NOsj=makeOption($dbname,'log_transaksiht','notransaksi,nosj');


if ($jenis == '9')
    $jenis = '';
$tipetransaksi = "a.tipetransaksi like '%" . $jenis . "%'";

$tanggalmulai = substr($periode,6,4)."-".substr($periode,3,2)."-".substr($periode,0,2);
$tanggalsampai = substr($periode2,6,4)."-".substr($periode2,3,2)."-".substr($periode2,0,2);

$periode = substr($periode,6,4)."-".substr($periode,3,2);
$periode2 = substr($periode2,6,4)."-".substr($periode2,3,2);


$str = "select tanggalmulai, tanggalsampai from " . $dbname . ".setup_periodeakuntansi
    where periode between '" . $periode . "' and '" . $periode2 . "' and kodeorg='" . $unit . "' limit 1";
// if ($unit == 'sumatera')
    // $str = "select tanggalmulai, tanggalsampai from " . $dbname . ".setup_periodeakuntansi
        // where periode ='" . $periode . "' and kodeorg in ('MRKE10','SKSE10','SOGM20','SSRO21','WKNE10')";
// if ($unit == 'kalimantan')
    // $str = "select tanggalmulai, tanggalsampai from " . $dbname . ".setup_periodeakuntansi
        // where periode ='" . $periode . "' and kodeorg in ('SBME10','SBNE10','SMLE10','SMTE10','SSGE10','STLE10')";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    // $tanggalmulai = $bar->tanggalmulai;
  
}

$str = "select tanggalsampai from " . $dbname . ".setup_periodeakuntansi
    where periode between '" . $periode . "' and '" . $periode2 . "' and kodeorg='" . $unit . "' order by tanggalsampai desc limit 1";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  
    // $tanggalsampai = $bar->tanggalsampai;
}

if ($kodebarang == '') {
    $str = "select a.tanggal, a.kodebarang, b.namabarang, a.jumlah, a.satuan, a.hargasatuan, a.hargarata, a.nopo, 
        c.namasupplier, a.kodeblok, a.kodemesin, a.notransaksi, a.tipetransaksi,a.gudangx,a.statusjurnal,a.kodekegiatan,a.notransaksireferensi,
		a.keterangan
        from " . $dbname . ".log_transaksi_vw a
        left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang  
        left join " . $dbname . ".log_5supplier c on a.idsupplier=c.supplierid  
        where a.tanggal>='" . $tanggalmulai . "' and a.tanggal<='" . $tanggalsampai . "' 
        and " . $tipetransaksi . " and a.kodegudang = '" . $unit . "'
        order by a.tanggal, a.tipetransaksi";
} else {
     $str = "select a.tanggal, a.kodebarang, b.namabarang, a.jumlah, a.satuan, a.hargasatuan, a.hargarata, a.nopo, 
        c.namasupplier, a.kodeblok, a.kodemesin, a.notransaksi, a.tipetransaksi,a.gudangx,a.statusjurnal,a.kodekegiatan,a.notransaksireferensi,
		a.keterangan
        from " . $dbname . ".log_transaksi_vw a
        left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang  
        left join " . $dbname . ".log_5supplier c on a.idsupplier=c.supplierid  
        where a.tanggal>='" . $tanggalmulai . "' and a.tanggal<='" . $tanggalsampai . "' and " . $tipetransaksi . "
        and a.kodegudang = '" . $unit . "' and a.kodebarang = '" . $kodebarang . "'
        order by a.tanggal, a.tipetransaksi";
		
    $str22 = "select sum(saldoawalqty) as saldoawalqty, avg(hargaratasaldoawal) as hargaratasaldoawal, 
	sum(nilaisaldoawal) as nilaisaldoawal from " . $dbname . ".log_5saldobulanan where kodegudang = '" . $unit . "'
        and kodebarang = '" . $kodebarang . "' and periode = '" . $periode . "'";
		/*
		$str22 = "select sum(saldoawalqty) as saldoawalqty, avg(hargaratasaldoawal) as hargaratasaldoawal, 
	sum(nilaisaldoawal) as nilaisaldoawal from " . $dbname . ".log_5saldobulanan where kodegudang = '" . $unit . "'
        and kodebarang = '" . $kodebarang . "' and periode between '" . $periode . "' and '" . $periode2 . "'";
		*/
		// echo $str22;
	$res22=$owlPDO->query($str22) or die(print " Gagal: ".PDOException::getMessage());
	$res22->setFetchMode(PDO::FETCH_OBJ);
	$numrows=owlBaris($res22);
    if ($numrows > 0)
        while ($bar22 = $res22->fetch()) {
            $saldoawalqty = $bar22->saldoawalqty;
            $hargaratasaldoawal = $bar22->hargaratasaldoawal;
            $nilaisaldoawal = $bar22->nilaisaldoawal;
        }
}

$str44 = "select kodebarang, namabarang, satuan from " . $dbname . ".log_5masterbarang where kodebarang = '" . $kodebarang . "'";
$res44=$owlPDO->query($str44) or die(print " Gagal: ".PDOException::getMessage());
$res44->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res44);
if ($numrows > 0)
    while ($bar44 = $res44->fetch()) {
        $namabarang = $bar44->namabarang;
        $satuan = $bar44->satuan;
    }
// Query Setup kegiatan 
$str_kgt = "select kodekegiatan,namakegiatan from " . $dbname . ".setup_kegiatan";
$res_kgt=$owlPDO->query($str_kgt) or die(print " Gagal: ".PDOException::getMessage());
$res_kgt->setFetchMode(PDO::FETCH_OBJ);
$numrows_kgt=owlBaris($res_kgt);
$data_kgt = array();
if ($numrows_kgt > 0){
	while ($kgt = $res_kgt->fetch()){
		$data['kodekegiatan'] = $kgt->kodekegiatan;
		$data['namakegiatan'] = $kgt->namakegiatan;
		$data_kgt[] = $data;
	}
}	
function get_namakegiatan($array,$val){
	$rasult = "";
	$str = $val;
	for($i=0; $i<count($array); $i++){
		if($val == $array[$i]['kodekegiatan']){
			$rasult = "-".$array[$i]['namakegiatan'];
			break;
		}
	}
	return $rasult;
}
//END
// Query Organisasi
$str_org = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi";
$res_org=$owlPDO->query($str_org) or die(print " Gagal: ".PDOException::getMessage());
$res_org->setFetchMode(PDO::FETCH_OBJ);
$numrows_org=owlBaris($res_org);
$data_org = array();
if ($numrows_org > 0){
	while ($org = $res_org->fetch()){
		$data['kodeorganisasi'] = $org->kodeorganisasi;
		$data['namaorganisasi'] = $org->namaorganisasi;
		$data_org[] = $data;
	}
}	
function get_namaorganisasi($array,$val){
	$rasult = "";
	$str = $val;
	for($i=0; $i<count($array); $i++){
		if($val == $array[$i]['kodeorganisasi']){
			$rasult = "-".$array[$i]['namaorganisasi'];
			break;
		}
	}
	return $rasult;
}

echo"<table class=sortable  cellpadding=5 cellspacing=1 border=0>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
if ($numrows < 1) {
    echo"<tr><td>" . $_SESSION['lang']['tidakditemukan'] . "</td></tr>";
} else {
    echo"<thead><tr>
    <th align=center>No.</th>";
    echo"<th align=center>" . $_SESSION['lang']['tipetransaksi'] . "</th>";
    echo"<th align=center>" . $_SESSION['lang']['tanggal'] . "</th>";
    echo"<th align=center>" . $_SESSION['lang']['kodebarang'] . "</th>";
    echo"<th align=center>" . $_SESSION['lang']['namabarang'] . "</th>";
    if ($jenis == '') {
        echo"<th align=center>" . $_SESSION['lang']['masuk'] . "</th>";
        echo"<th align=center>" . $_SESSION['lang']['keluar'] . "</th>";
        echo"<th align=center>" . $_SESSION['lang']['saldo'] . "</th>";
    } else {
        echo"<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>";
    }
    echo"<th align=center>" . $_SESSION['lang']['satuan'] . "</th>";
    if ($jenis == '') {
        
    } else {
        echo"<th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>";
        echo"<th align=center>" . $_SESSION['lang']['totalharga'] . "</th>";
    }
	if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '2')){
        echo"<th align=center>" . $_SESSION['lang']['nopo'] . "</th>";
	}
    if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '2')){
        echo"<th align=center>" . $_SESSION['lang']['supplier'] . "</th>";
	}
    if ($jenis == ''){
        echo"<th align=center>" . $_SESSION['lang']['tujuan'] . "/" . $_SESSION['lang']['sumber'] . "</th>";
	}
    if ($jenis == '3'){
        echo"<th align=center>" . $_SESSION['lang']['sumber'] . "</th>";
	}
    if ($jenis == '7'){
        echo"<th align=center>" . $_SESSION['lang']['tujuan'] . "</th>";
	}
    if (($jenis == '5')or ( $jenis == '6')){
        echo"<th align=center>" . $_SESSION['lang']['kodeblok'] . "</th>";
	}
	echo"<th align=center>" . $_SESSION['lang']['kodekegiatan'] . "</th>";
    if (($jenis == '5')or ( $jenis == '6')){
        echo"<th align=center>" . $_SESSION['lang']['kodevhc'] . "</th>";
	}
    echo"<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>";
    echo"<th align=center>" . $_SESSION['lang']['noreferensi'] . "</th>";
    echo"<th align=center>" . $_SESSION['lang']['file'] . "</th>";
    echo"<th align=center>" . $_SESSION['lang']['nosj'] . "</th>";
    echo"<th align=center>" . $_SESSION['lang']['catatan'] . "</th>";
    echo"</tr></thead><tbody>";
    if ($jenis == '' and $kodebarang!='') {
        $no = 1;
        $saldo = $saldoawalqty;
        $masuk = 0;
        $keluar = 0;
        $totmas = $totkel = 0;
        echo"<tr class=rowcontent>
            <td align=right>" . $no . "</td>";
        echo"<td>Saldo Awal</td>";
        echo"<td>" . tanggalnormal($periode . "-01") . "</td>";
        echo"<td>" . $kodebarang . "</td>";
        echo"<td nowrap>" . $namabarang . "</td>";
        if ($saldoawalqty >= 0) {
            $masuk = $saldoawalqty;
            $totmas+=$masuk;
        } else {
            $keluar = $saldoawalqty * (-1);
            $totkel+=$keluar;
        }
        echo"<td align=right>" . number_format($masuk, 2) . "</td>";
        echo"<td align=right>" . number_format($keluar, 2) . "</td>";
        echo"<td align=right>" . number_format($saldoawalqty, 2) . "</td>";
        echo"<td>" . $satuan . "</td>";
        if ($jenis == '') {
            
        } else {
            echo"<td align=right>" . number_format($hargaratasaldoawal) . "</td>";
            echo"<td align=right>" . number_format($nilaisaldoawal) . "</td>";
        }
        echo"<td></td>";
        echo"<td></td>";
        echo"<td></td>";
        echo"<td></td>";
        echo"<td></td>";
        echo"<td></td>";
        echo"<td></td>";
        echo"</tr>";
    }
    // 0 = Mutasi dalam perjalanan
    // 1 = Masuk
    // 2 = Pengembalian pengeluaran
    // 3 = Penerimaan mutasi
    // 5 = Pengeluaran
    // 6 = Pengembalian penerimaan
    // 7 = Pengeluaran mutasi
    $no = 0;
    while ($bar = $res->fetch()) {
        $no++;
        $total = 0;
        if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '3'))
            $total = $bar->jumlah * $bar->hargasatuan;
        else
            $total = $bar->jumlah * $bar->hargarata;
        $bgColor = "";
        if ($bar->statusjurnal != 1)
            $bgColor = "style='background-color:#E07C1A'";
        echo"<tr class=rowcontent >
            <td align='right' width='1' " . $bgColor . ">" . $no . "</td>";
        echo"<td  " . $bgColor . ">" . $kamusjenis[$bar->tipetransaksi] . "</td>";
        echo"<td nowrap " . $bgColor . ">" . tanggalnormal($bar->tanggal) . "</td>";
        echo"<td " . $bgColor . ">" . $bar->kodebarang . "</td>";
        echo"<td nowrap  " . $bgColor . ">" . $bar->namabarang . "</td>";


        if($optkodekenadraan[$bar->kodemesin] != ''){
            $keluarmasukKENDARAAN = $optkodekenadraan[$bar->kodemesin] . " - " . $optkodekenadraanD[$bar->kodemesin];
        }else{
            $keluarmasukKENDARAAN = $optkodekenadraanD[$bar->kodemesin];
        }

        if ($jenis == '') {
            $masuk = 0;
            $keluar = 0;
            if ($bar->tipetransaksi < 5)
                $masuk = $bar->jumlah;
            if ($bar->tipetransaksi > 4)
                $keluar = $bar->jumlah;
            $totmas+=$masuk;
            $totkel+=$keluar;
            echo"<td align=right  " . $bgColor . ">" . number_format($masuk, 2) . "</td>";
            // echo"<td align=right  " . $bgColor . ">" . number_format($keluar, 2) . "</td>";
            if(number_format($keluar, 2) != '0.00'){
                echo"<td align=right  " . $bgColor . ">" . number_format($keluar, 2) . "</td>";
            }else{
                echo"<td align=right  " . $bgColor . ">" . number_format($keluar, 5) . "</td>";
            }
            $saldo+=$masuk - $keluar;
            echo"<td align=right  " . $bgColor . ">" . number_format($saldo, 2) . "</td>";
        }else {
            echo"<td align=right  " . $bgColor . ">" . number_format($bar->jumlah, 2) . "</td>";
        }
        echo"<td " . $bgColor . ">" . $bar->satuan . "</td>";
        if ($jenis == '') {
            
        } else {
            if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '3'))
                echo"<td align=right " . $bgColor . ">" . number_format($bar->hargasatuan) . "</td>";
            else
                echo"<td align=right " . $bgColor . ">" . number_format($bar->hargarata) . "</td>";
            echo"<td align=right " . $bgColor . ">" . number_format($total) . "</td>";
        }
		if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '2'))
            echo"<td nowrap " . $bgColor . ">" . $bar->nopo . "</td>";
        if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '2'))
            echo"<td nowrap " . $bgColor . ">" . $bar->namasupplier . "</td>";
        if ($jenis == '7')
            echo"<td " . $bgColor . ">" . ($optorganisasi[$bar->gudangx]) . " - ".$bar->gudangx."</td>";
        if ($jenis == '3')
            echo"<td " . $bgColor . ">" . ($optorganisasi[$bar->gudangx]) . " - ".$bar->gudangx."</td>";
        if (($jenis == '5')or ( $jenis == '6')){
            echo"<td " . $bgColor . ">" . ($bar->kodeblok) . get_namaorganisasi($data_org,$bar->kodeblok) . "</td>";
		}

        if ($jenis == '') {
            if ($bar->tipetransaksi < 5)
                $keluarmasuk = $bar->nopo . " " . $bar->namasupplier . " " . $optorganisasi[$bar->gudangx];
            if ($bar->tipetransaksi > 4)
                $keluarmasuk = $optorganisasi[$bar->kodeblok] . " - " . $keluarmasukKENDARAAN . " " . $optorganisasi[$bar->gudangx];
            echo"<td nowrap " . $bgColor . ">" . trim($keluarmasuk) . "</td>";
        }
		echo"<td " . $bgColor . ">" . $bar->kodekegiatan . get_namakegiatan($data_kgt,$bar->kodekegiatan) . "</td>";
        if (($jenis == '5')or ( $jenis == '6'))
            echo"<td " . $bgColor . ">" . $bar->kodemesin . "-" . $keluarmasukKENDARAAN . "</td>";

		
        echo"<td nowrap " . $bgColor . ">" . $bar->notransaksi . "</td>";
        echo"<td nowrap " . $bgColor . ">" . $bar->notransaksireferensi . "</td>";
		
		// $filedata=$no='';
		$filedata='';
		$strdt="select * from ".$dbname.".listfile_log_penerimaan where notransaksi='".$bar->notransaksi."'";
		$resdt=$owlPDO->query($strdt) or die(print " Gagal: ".PDOException::getMessage());
        $resdt->setFetchMode(PDO::FETCH_ASSOC);
        while($bardt=$resdt->fetch()){
			// $no++;
			$filedata.="<a href='".$path.$bardt['namafile']."'  target=_blank>".$_SESSION['lang']['file']."-".$no."</a><br>";
		}
		
		
		
		 echo"<td nowrap>".$filedata."</td>";
		 echo"<td>".$NOsj[$bar->notransaksi]."</td>";
		 echo"<td>".$bar->keterangan."</td>";
        echo"</tr>";
    }
    if ($jenis == '') {
        echo"<tr class=rowcontent>
            <td align=center colspan=5>Total</td>";
            $saldoakhir = $totmas - $totkel;
        echo"<td align=right>" . number_format($totmas, 2) . "</td>";
        echo"<td align=right>" . number_format($totkel, 2) . "</td>";
        echo"<td align=right>" . number_format($saldoakhir, 2) . "</td>";
		
        echo"<td colspan=8></td>";
        echo"</tr>";
    }
    echo"</tbody<tfoot><tr><td colspan=15>*Baris berwarna oranye berarti transaksi belum di posting</td></tr></tfoot>";
}
?>