<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('lib/nangkoelib.php');

$pt = $_POST['pt'];
$gudang = $_POST['kd_gudang'];
$periode = $_POST['periode'];
$kodebarang = $_POST['kodebarang'];
$namabarang = $_POST['namabarang'];
$satuan = $_POST['satuan'];
//======================================
$x = str_replace("-", "", $periode);
$x = str_replace("/", "", $x);
$x = mktime(0, 0, 0, (intval(substr($x, 4, 2)) - 1), 15, substr($x, 0, 4));
$prefper = date('Y-m', $x);

//ambil namapt
$str = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . $pt . "'";
$namapt = 'COMPANY NAME';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $namapt = strtoupper($bar->namaorganisasi);
}
//==========================get periode
$str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi
      where kodeorg='" . $gudang . "' and periode='" . $periode . "'";
$awal = '';
$akhir = '';
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $awal = $bar->tanggalmulai;
    $akhir = $bar->tanggalsampai;
}

//ambil saldo awal===============================
if ($gudang == '') {
    // $str = "select  sum(saldoakhirqty) as sawal,
                            // sum(nilaisaldoakhir) as sawalrp from 
                            // " . $dbname . ".log_5saldobulanan
                            // where kodebarang='" . $kodebarang . "'
                            // and periode='" . $prefper . "'";
	
	$str = "select   sum(saldoawalqty) as sawal,
                            sum(nilaisaldoawal) as sawalrp from 
                            " . $dbname . ".log_5saldobulanan
                            where kodebarang='" . $kodebarang . "'
                            and periode='" . $periode . "'";	
							
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
    // $str = "select  sum(saldoakhirqty) as sawal,
                            // sum(nilaisaldoakhir) as sawalrp from 
                            // " . $dbname . ".log_5saldobulanan
                            // where kodebarang='" . $kodebarang . "'
                            // and periode='" . $prefper . "'
                            // and kodegudang='" . $gudang . "'";
	
		$str = "select  sum(saldoawalqty) as sawal,
                            sum(nilaisaldoawal) as sawalrp from 
                            " . $dbname . ".log_5saldobulanan
                            where kodebarang='" . $kodebarang . "'
                            and periode='" . $periode . "'
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


// echo"<fieldset>";
// echo"<legend>".$_SESSION['lang']['result']."</legend>";



		
// if ($sawal > 0){
	$optNmBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kodebarang."'");
	$optNmSatuan = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$kodebarang."'");
	echo"<img onclick=\"dataKeExcel(event, 'log_laporanKeluarMasukPerBarang_Excel.php')\" src=images/excel.jpg class=resicon title='MS.Excel'> 
	<img onclick=\"dataKePDF(event)\" title='PDF' class=resicon src=images/pdf.jpg>
	
	<table cellspacing=1 cellpadding=5 border=0 id=table_data_barang>
		<tbody id=isi_conten>
		<tr id=isi_data_barang>
			<td>".$_SESSION['lang']['kodebarang']."</td>
			<td>:</td>
            <td id='kd_brg'>".$kodebarang."</td>
		</tr>
		<tr id=isi_data_barang>
			<td>".$_SESSION['lang']['namabarang']."</td>
			<td>:</td>
            <td id=nm_brg>".$optNmBarang[$kodebarang]."</td>
		</tr>
		<tr id=isi_data_barang>
			<td>".$_SESSION['lang']['satuan']."</td>
			<td>:</td>
			<td id=satuan_brg>".$optNmSatuan[$kodebarang]."</td>
		</tr>
		</tbody>
	</table>
<div class='table-scroll'>
	<table class=sortable  cellpadding=5 cellspacing=1 border=0 style='position:absolut;'>
		<thead>
		<tr class='rowheader'>
			<th align='center'  style='width:50px;'>No.</th>
			<th align='center'>".$_SESSION['lang']['notransaksi']."</th>
			<th align='center'>".$_SESSION['lang']['tanggal']."</th>
			<th align='center'>".$_SESSION['lang']['saldoawal']."</th>
			<th align='center'>".$_SESSION['lang']['masuk']."</th>
			<th align='center'>".$_SESSION['lang']['keluar']."</th>
			<th align='center'>".$_SESSION['lang']['saldo']."</th>
		</tr>
		</thead>
	<tbody>";
	$hargasawal = $sawalrp / $sawal;
	$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_OBJ);
	$no = 0;
	$saldo = $sawal;
	$masuk = 0;
	$keluar = $totMasuk = $totKeluar =0;
	while ($barx = $resx->fetch()) {
		$no+=1;
		if ($barx->tipetransaksi < 5) {
			$saldo = $saldo + $barx->jumlah;
			$masuk = $barx->jumlah;
			$keluar = 0;
		}else{
			$saldo = $saldo - $barx->jumlah;
			$keluar = $barx->jumlah;
			$masuk = 0;
		}
		
		echo"<tr class=rowcontent>
			<td align=center style='width:50px;'>" . $no . "</td>
            <td align=center>" . $barx->notransaksi . "</td>    
            <td align=center>" . tanggalnormal($barx->tanggal) . "</td>
            <td align=right>" . number_format($sawal, 3, '.', ',') . "</td>
            <td align=right>" . number_format($masuk, 3, '.', ',') . "</td>
            <td align=right>" . number_format($keluar, 3, '.', ',') . "</td>
            <td align=right>" . number_format($saldo, 3, '.', ',') . "</td>
		</tr>";
		$sawal = $saldo;
		$totMasuk = $totMasuk + $masuk;
		$totKeluar = $totKeluar + $keluar;
	}
	
	echo"<tr class=rowcontent>
		<td align=center colspan=4>".$_SESSION['lang']['total']."</td>
		<td align=right>" . number_format($totMasuk, 3, '.', ',') . "</td>
		<td align=right>" . number_format($totKeluar, 3, '.', ',') . "</td>
		<td align=right></td>
	</tr>
	</tbody>
	</table>
</div>";
// }else{
	// echo"Data tidak ditemukan</td>";
// }

// echo"</fieldset>";
?>