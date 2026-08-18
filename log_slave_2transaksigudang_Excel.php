<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$unit = $_GET['unit'];
$periode = $_GET['periode'];
$periode2 = $_GET['periode2'];
$jenis = $_GET['jenis'];
$kodebarang = $_GET['kodebarang'];

$kamusjenis['0'] = 'Mutasi dalam perjalanan';
$kamusjenis['1'] = 'Penerimaan';
$kamusjenis['2'] = 'Pengembalian pengeluaran';
$kamusjenis['3'] = 'Penerimaan mutasi';
$kamusjenis['5'] = 'Pengeluaran';
$kamusjenis['6'] = 'Pengembalian penerimaan';
$kamusjenis['7'] = 'Pengeluaran mutasi';

if ($unit == '') {
    echo "Warning: silakan mengisi gudang";
    exit;
}
if ($periode == '') {
    echo "Warning: silakan mengisi periode";
    exit;
}
if ($jenis == '') {
    echo "Warning: silakan mengisi tipe transaksi";
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
    $str = "select a.keterangan,a.tanggal, a.kodebarang, b.namabarang, a.jumlah, a.satuan, a.hargasatuan, a.hargarata, a.nopo, 
        c.namasupplier, a.kodeblok, a.kodemesin, a.notransaksi, a.gudangx, a.tipetransaksi, a.statusjurnal,a.notransaksireferensi,
        a.kodekegiatan
        from " . $dbname . ".log_transaksi_vw a
        left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang  
        left join " . $dbname . ".log_5supplier c on a.idsupplier=c.supplierid  
        where a.tanggal>='" . $tanggalmulai . "' and a.tanggal<='" . $tanggalsampai . "' 
        and " . $tipetransaksi . " and a.kodegudang = '" . $unit . "'
        order by a.tanggal, a.tipetransaksi";
} else {
    $str = "select a.keterangan,a.tanggal, a.kodebarang, b.namabarang, a.jumlah, a.satuan, a.hargasatuan, a.hargarata, a.nopo, 
        c.namasupplier, a.kodeblok, a.kodemesin, a.notransaksi, a.gudangx, a.tipetransaksi, a.statusjurnal, a.statusjurnal,a.notransaksireferensi,
        a.kodekegiatan
        from " . $dbname . ".log_transaksi_vw a
        left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang  
        left join " . $dbname . ".log_5supplier c on a.idsupplier=c.supplierid  
        where a.tanggal>='" . $tanggalmulai . "' and a.tanggal<='" . $tanggalsampai . "' and " . $tipetransaksi . "
        and a.kodegudang = '" . $unit . "' and a.kodebarang = '" . $kodebarang . "' 
        order by a.tanggal, a.tipetransaksi";
		
    $str22 = "select sum(saldoawalqty) as saldoawalqty, avg(hargaratasaldoawal) as hargaratasaldoawal, sum(nilaisaldoawal) as nilaisaldoawal from " . $dbname . ".log_5saldobulanan where kodegudang = '" . $unit . "'
        and kodebarang = '" . $kodebarang . "' and periode = '" . $periode . "'";
	/*
	 $str22 = "select sum(saldoawalqty) as saldoawalqty, avg(hargaratasaldoawal) as hargaratasaldoawal, sum(nilaisaldoawal) as nilaisaldoawal from " . $dbname . ".log_5saldobulanan where kodegudang = '" . $unit . "'
        and kodebarang = '" . $kodebarang . "' and periode between '" . $periode . "' and '" . $periode2 . "'";
	
	*/

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
$numrows=owlBaris($res);
if ($numrows > 0)
    while ($bar44 = $res44->fetch()) {
        $namabarang = $bar44->namabarang;
        $satuan = $bar44->satuan;
    }

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$no = 0;
if ($numrows < 1) {
    echo"<tr class=rowcontent><td colspan=14>" . $_SESSION['lang']['tidakditemukan'] . "</td></tr>";
} else {
    $stream = $_SESSION['lang']['transaksigudang'] . ": " . $jenis . " : " . $unit . " : " . $periode . " (" . tanggalnormal($tanggalmulai) . " - " . tanggalnormal($tanggalsampai) . ")<br>
		<table border=1>
				    <tr>
			  <td bgcolor=#DEDEDE align=center>No.</td>";
    $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['tipetransaksi'] . "</td>";
    $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['tanggal'] . "</td>";
    $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['kodebarang'] . "</td>";
    $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['namabarang'] . "</td>";
    if ($jenis == '') {
        $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['masuk'] . "</td>";
        $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['keluar'] . "</td>";
        $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['saldo'] . "</td>";
    } else {
        $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['jumlah'] . "</td>";
    }
    $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['satuan'] . "</td>";
    $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['hargasatuan'] . "</td>";
    $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['totalharga'] . "</td>";
    if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '2'))
        $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['nopo'] . "</td>";
    if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '2'))
        $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['supplier'] . "</td>";
    if ($jenis == '')
        $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['sumber'] . "/" . $_SESSION['lang']['tujuan'] . "</td>";
    if ($jenis == '7')
        $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['tujuan'] . "</td>";
    if ($jenis == '3')
        $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['sumber'] . "</td>";
    if (($jenis == '5')or ( $jenis == '6'))
        $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['kodeblok'] . "</td>";
    if (($jenis == '5')or ( $jenis == '6'))
        $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['kodevhc'] . "</td>";
    $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['notransaksi'] . "</td>";
    $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['noreferensi'] . "</td>";
    $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['nosj'] . "</td>";
    $stream.="<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['catatan'] . "</td>";
    $stream.="</tr>";
    if ($jenis == '' and $kodebarang!='') {
        $no = 1;
        $saldo = $saldoawalqty;
        $masuk = 0;
        $keluar = 0;
        $stream.="<tr class=rowcontent>
            <td align=right>" . $no . "</td>";
        $stream.="<td>Saldo Awal</td>";
        $stream.="<td>" . tanggalnormal($periode . "-01") . "</td>";
        $stream.="<td>" . $kodebarang . "</td>";
        $stream.="<td nowrap>" . $namabarang . "</td>";
        if ($saldoawalqty >= 0) {
            $masuk = $saldoawalqty;
            $totmas+=$masuk;
        } else {
            $keluar = $saldoawalqty * (-1);
            $totkel+=$keluar;
        }
        $stream.="<td align=right>" . number_format($masuk, 2) . "</td>";
        $stream.="<td align=right>" . number_format($keluar, 2) . "</td>";
        $stream.="<td align=right>" . number_format($saldoawalqty, 2) . "</td>";
        $stream.="<td>" . $satuan . "</td>";
        $stream.="<td align=right>" . number_format($hargaratasaldoawal) . "</td>";
        $stream.="<td align=right>" . number_format($nilaisaldoawal) . "</td>";
        $stream.="<td></td>";
        $stream.="<td></td>";
        $stream.="<td></td>";
        $stream.="<td></td>";
        $stream.="</tr>";
    }
    while ($bar = $res->fetch()) {
        $no+=1;
        $total = 0;
        if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '2')or ( $jenis == '3'))
            $total = $bar->jumlah * $bar->hargasatuan;
        else
            $total = $bar->jumlah * $bar->hargarata;
        $stream.="<tr>";
        $stream.="<td align=right>" . $no . "</td>";
        $stream.="<td align=right>" . $kamusjenis[$bar->tipetransaksi] . "</td>";
        $stream.="<td>" . $bar->tanggal . "</td>";
        $stream.="<td>" . $bar->kodebarang . "</td>";
        $stream.="<td nowrap>" . $bar->namabarang . "</td>";


        if($optkodekenadraan[$bar->kodemesin] != ''){
            $keluarmasukKENDARAAN = $optkodekenadraan[$bar->kodemesin] . " - " . $optkodekenadraanD[$bar->kodemesin];
        }

        if ($jenis == '') {
            $masuk = 0;
            $keluar = 0;
            if ($bar->tipetransaksi < 4)
                $masuk = $bar->jumlah;
            if ($bar->tipetransaksi > 4)
                $keluar = $bar->jumlah;
            $totmas+=$masuk;
            $totkel+=$keluar;
            $stream.="<td align=right>" . number_format($masuk, 2) . "</td>";
            $stream.="<td align=right>" . number_format($keluar, 2) . "</td>";
            $saldo+=$masuk - $keluar;
            $stream.="<td align=right>" . number_format($saldo, 2) . "</td>";
        }else {
            $stream.="<td align=right>" . number_format($bar->jumlah, 2) . "</td>";
        }

        $stream.="<td>" . $bar->satuan . "</td>";
        if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '2')or ( $jenis == '3'))
            $stream.="<td align=right>" . number_format($bar->hargasatuan) . "</td>";
        else
            $stream.="<td align=right>" . number_format($bar->hargarata) . "</td>";
        $stream.="<td align=right>" . number_format($total) . "</td>";
        if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '2'))
            $stream.="<td nowrap>" . $bar->nopo . "</td>";
        if (($jenis == '0')or ( $jenis == '1')or ( $jenis == '2'))
            $stream.="<td nowrap>" . $bar->namasupplier . "</td>";
        if ($jenis == '7')
            $stream.="<td>" . ($optorganisasi[$bar->gudangx]) . " - ".$bar->gudangx."</td>";
        if ($jenis == '3')
            $stream.="<td>" . ($optorganisasi[$bar->gudangx]) . " - ".$bar->gudangx."</td>";
        if (($jenis == '5')or ( $jenis == '6'))
            $stream.="<td>" . $bar->kodeblok . "</td>";
        if (($jenis == '5')or ( $jenis == '6'))
            $stream.="<td>" . $bar->kodemesin . "</td>";
        if ($jenis == '') {
            if ($bar->tipetransaksi < 5)
                $keluarmasuk = $bar->nopo . " " . $bar->namasupplier . " " . $optorganisasi[$bar->gudangx];
            if ($bar->tipetransaksi > 4)
                $keluarmasuk = $optorganisasi[$bar->kodeblok] . " " . $keluarmasukKENDARAAN . " " . $optorganisasi[$bar->gudangx];
            $stream.="<td nowrap>" . $keluarmasuk . "</td>";
        }
        $stream.="<td nowrap>" . $bar->notransaksi . "</td>";
        $stream.="<td nowrap>" . $bar->notransaksireferensi . "</td>";
        $stream.="<td nowrap>" . $NOsj[$bar->notransaksi] . "</td>";
        $stream.="<td nowrap>" . $bar->keterangan . "</td>";
        $stream.="</tr>";
    }
    if ($jenis == '') {
        $stream.="<tr class=rowcontent>
            <td align=center colspan=5>Total</td>";
            $saldoakhir = $totmas - $totkel;
        $stream.="<td align=right>" . number_format($totmas, 2) . "</td>";
        $stream.="<td align=right>" . number_format($totkel, 2) . "</td>";
        $stream.="<td align=right>" . number_format($saldoakhir, 2) . "</td>";
        $stream.="<td colspan=8>" . $satuan . "</td>";
        $stream.="</tr>";
    }

    $stream.="</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
}

$nop_ = "TransaksiGudang_" . $jenis . "" . $unit . "_" . $periode;
if (strlen($stream) > 0) {
    if ($handle = opendir('tempExcel')) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "index.html") {
                @unlink('tempExcel/' . $file);
            }
        }
        closedir($handle);
    }
    $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
    if (!fwrite($handle, $stream)) {
        echo "<script language=javascript1.2>
        parent.window.alert('Can't convert to excel format');
        </script>";
        exit;
    } else {
        echo "<script language=javascript1.2>
        window.location='tempExcel/" . $nop_ . ".xls';
        </script>";
    }
    fclose($handle);
}
?>