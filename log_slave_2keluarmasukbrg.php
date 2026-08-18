<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');

$pt = $_POST['pt'];
$gudang = $_POST['kd_gudang'];
$periode = substr($_POST['periode'], 0, 7);
$kodebarang = $_POST['kodebarang'];
$namabarang = $_POST['namabarang'];
$satuan = $_POST['satuan'];

//======================================
if ($_POST['method'] == 'getGudang') {
    $optGudang = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
    $sgdng = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe like 'GUDANG%' and alokasi = '" . $_POST['company_id'] . "'";
    $qgdng = $owlPDO->query($sgdng) or die(print " Gagal: " . PDOException::getMessage());
    $qgdng->setFetchMode(PDO::FETCH_ASSOC);
    while ($rgdng = $qgdng->fetch()) {
        $optGudang.="<option value=" . $rgdng['kodeorganisasi'] . ">" . $rgdng['namaorganisasi'] . "</option>";
    }
    echo $optGudang;
}

if($periode==''){
	$periode=$_SESSION['org']['period']['tahun']."-".$_SESSION['org']['period']['bulan'];
}

//======================================
$x = str_replace("-", "", $periode);
$x = str_replace("/", "", $x);
$x = mktime(0, 0, 0, (intval(substr($x, 4, 2)) - 1), 15, substr($x, 0, 4));
$prefper = date('Y-m', $x);

//ambil namapt
$str = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $pt . "'";
$namapt = 'COMPANY NAME';
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $namapt = strtoupper($bar->namaorganisasi);
}
//==========================get periode
$str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi
      where kodeorg='" . $gudang . "' and periode='" . $periode . "'";
$awal = '';
$akhir = '';
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $awal = $bar->tanggalmulai;
    $akhir = $bar->tanggalsampai;
}
//ambil saldo awal===============================
if ($gudang == '') {
    $str = "select  sum(saldoakhirqty) as sawal,
                            sum(nilaisaldoakhir) as sawalrp from 
                            " . $dbname . ".log_5saldobulanan
                            where kodebarang='" . $kodebarang . "'
                            and periode='" . $prefper . "'";
    //=========================================
    //ambil transaksi detail
    $strx = "select a.*,b.idsupplier,b.tanggal,b.kodegudang,
                  b.tipetransaksi 
                  from " . $dbname . ".log_transaksidt a
                  left join " . $dbname . ".log_transaksiht b
                      on a.notransaksi=b.notransaksi
                      where kodebarang='" . $kodebarang . "'
                      and kodept='" . $pt . "'
                      and b.tanggal>='" . $awal . "'
                      and b.tanggal<='" . $akhir . "'
                      and b.post=1
                      order by tanggal,waktutransaksi";
} else {
    $str = "select  sum(saldoakhirqty) as sawal,
                            sum(nilaisaldoakhir) as sawalrp from 
                            " . $dbname . ".log_5saldobulanan
                            where kodebarang='" . $kodebarang . "'
                            and periode='" . $prefper . "'
                            and kodegudang='" . $gudang . "'"; 
    //=========================================
    //ambil transaksi detail
    $strx = "select a.*,b.idsupplier,b.tanggal,b.kodegudang,
                  b.tipetransaksi
                      from " . $dbname . ".log_transaksidt a
                  left join " . $dbname . ".log_transaksiht b
                      on a.notransaksi=b.notransaksi
                      where kodebarang='" . $kodebarang . "'
                      and kodept='" . $pt . "'
                      and kodegudang='" . $gudang . "'
                      and b.tanggal>='" . $awal . "'
                      and b.tanggal<='" . $akhir . "'
                      and b.post=1
                      order by tanggal,waktutransaksi";
}
$sawal = 0;
$sawalrp = 0;
$hargasawal = 0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $sawal = $bar->sawal;
    $sawalrp = $bar->sawalrp;
}
if ($sawal > 0) {
    $hargasawal = $sawalrp / $sawal;
}
$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
$resx->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
$saldo = $sawal;
$masuk = 0;
$keluar = 0;
while ($barx = $resx->fetch()) {
    $no+=1;
    if ($barx->tipetransaksi < 5) {
        $saldo = $saldo + $barx->jumlah;
        $masuk = $barx->jumlah;
        $keluar = 0;
    } else {
        $saldo = $saldo - $barx->jumlah;
        $keluar = $barx->jumlah;
        $masuk = 0;
    }

    echo"	<tr class=rowcontent>
            <td>" . $no . "</td>
            <td align=center>" . tanggalnormal($barx->tanggal) . "</td>
            <td align=center>" . number_format($sawal, 2, '.', ',') . "</td>
            <td align=center>" . number_format($sawalrp / $sawal, 2, '.', ',') . "</td>
            <td align=center>" . number_format($sawalrp, 2, '.', ',') . "</td>                
            <td align=center>" . number_format($masuk, 2, '.', ',') . "</td>
            <td align=center>" . number_format($barx->hargasatuan, 2, '.', ',') . "</td>
            <td align=center>" . number_format($masuk * $barx->hargasatuan, 2, '.', ',') . "</td>                
            <td align=center>" . number_format($keluar, 2, '.', ',') . "</td>
             <td align=center>" . number_format($barx->hargarata, 2, '.', ',') . "</td>
            <td align=center>" . number_format($keluar * $barx->hargarata, 2, '.', ',') . "</td>                    
            <td align=center>" . number_format($saldo, 2, '.', ',') . "</td>
            <td align=center>" . number_format($barx->hargarata, 2, '.', ',') . "</td>
            <td align=center>" . number_format($saldo * $barx->hargarata, 2, '.', ',') . "</td>                
            </tr>";
    $sawal = $saldo;
    $sawalrp = $saldo * $barx->hargarata;
}
?>