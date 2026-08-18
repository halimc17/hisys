<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

//	$pt=$_POST['pt'];
$gudang = $_POST['gudang'];
$periode = $_POST['periode'];

$str = "select distinct tanggalmulai, tanggalsampai from " . $dbname . ".setup_periodeakuntansi
      where kodeorg = '" . $gudang . "' and periode = '" . $periode . "'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
if ($periode == '') {
    echo "Warning: silakan mengisi periode";
    exit;
}
while ($bar = $res->fetch()) {
    $tanggalmulai = $bar->tanggalmulai;
    $tanggalsampai = $bar->tanggalsampai;
}

$str = "select distinct kodebarang, namabarang from " . $dbname . ".log_5masterbarang";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optper = "";
while ($bar = $res->fetch()) {
    $barang[$bar->kodebarang] = $bar->namabarang;
}

if ($periode == '')
    $str = "select a.tanggal as tanggal, a.kodebarang as kodebarang, a.satuan as satuan, a.jumlah as jumlah, a.idsupplier as idsupplier, b.namasupplier as namasupplier, a.hargasatuan as hargasatuan 
		  from " . $dbname . ".log_transaksi_vw a
		  left join " . $dbname . ".log_5supplier b on a.idsupplier=b.supplierid
		  where a.kodegudang='" . $gudang . "' and a.tipetransaksi=1 
		  order by a.tanggal, namasupplier";
else
    $str = "select a.tanggal as tanggal, a.kodebarang as kodebarang, a.satuan as satuan, a.jumlah as jumlah, a.idsupplier as idsupplier, b.namasupplier as namasupplier, a.hargasatuan as hargasatuan 
		  from " . $dbname . ".log_transaksi_vw a
		  left join " . $dbname . ".log_5supplier b on a.idsupplier=b.supplierid
		  where a.kodegudang='" . $gudang . "' and a.tanggal>='" . $tanggalmulai . "' and a.tanggal<='" . $tanggalsampai . "' and a.tipetransaksi=1 
		  order by a.tanggal, namasupplier";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$no = 0;
if ($numrows < 1) {
    echo"<tr class=rowcontent><td colspan=17>" . $_SESSION['lang']['tidakditemukan'] . "</td></tr>";
} else {
    $gtotal = 0;
    while ($bar = $res->fetch()) {
        $no+=1;
        $total = 0;
        $total = $bar->jumlah * $bar->hargasatuan;
        echo"<tr class=rowcontent>
				  <td align=center>" . $no . "</td>
				  <td align=center>" . tanggalnormal($bar->tanggal) . "</td>
				  <td align=center>" . $bar->idsupplier . "</td>
				  <td>" . $bar->namasupplier . "</td>
				  <td align=center>" . $bar->kodebarang . "</td>
				  <td>" . $barang[$bar->kodebarang] . "</td>
				  <td>" . $bar->satuan . "</td>
				  <td align=right>" . number_format($bar->jumlah) . "</td>
				  <td align=right>" . number_format($bar->hargasatuan) . "</td>
				  <td align=right>" . number_format($total) . "</td>
				</tr>";
        $gtotal+=$total;
    }
    echo"<tr class=rowheader>
				  <td colspan=9 align=right>TOTAL</td>
				  <td align=right>" . number_format($gtotal) . "</td>
				</tr>";
}
?>