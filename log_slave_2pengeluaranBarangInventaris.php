<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = $_REQUEST['proses'];
$kodebarang = $_REQUEST['kodebarang'];
$nopo = $_REQUEST['nopo'];
$periode = $_REQUEST['periode'];

### begin get nama barang ###
$str2 = "select namabarang,kodebarang from " . $dbname . ".log_5masterbarang";
$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$res2->setFetchMode(PDO::FETCH_OBJ);
while ($bar2 = $res2->fetch()) {
    $namabarang[$bar2->kodebarang] = $bar2->namabarang;
}
### end get nama barang ###
### begin get tanggal po ###
$str3 = "select tanggal,nopo from " . $dbname . ". log_poht";
$res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
$res3->setFetchMode(PDO::FETCH_OBJ);
while ($bar3 = $res3->fetch()) {
    $tanggalpo[$bar3->nopo] = $bar3->tanggal;
}
### end get tanggal po ###
### begin get tanggal transaksi po ###
$str4 = "select waktutransaksi,notransaksi,kodebarang from " . $dbname . ". log_transaksidt";
$res4=$owlPDO->query($str4) or die(print " Gagal: ".PDOException::getMessage());
$res4->setFetchMode(PDO::FETCH_OBJ);
while ($bar4 = $res4->fetch()) {
    $tanggaltransaksi[$bar4->notransaksi][$bar4->kodebarang] = $bar4->waktutransaksi;
}
### end get tanggal transaksi po ###
### BEGIN GET DATA FILTER ###
$str = "select * from " . $dbname . ".log_transaksi_vw where
		tipetransaksi=1 and
		kodebarang like '%" . $kodebarang . "%' and
		left(kodebarang,1) = '9' and
		nopo like '%" . $nopo . "%' and
		tanggal like '%" . $periode . "%'
	order by tanggal desc";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$num_rows=owlBaris($res);
$stream="";
if ($proses == 'showdata'){
	$border=0;
}else {
	$border=1;
}

$stream.= "<table class=sortable cellspacing=1 cellpadding=5 border=".$border.">";
$stream.= "
		<thead class=rowheader>
		<tr>
		<td style='text-align:center;'>No</td>
		<td style='text-align:center;'>" . $_SESSION['lang']['kodebarang'] . "</td>
		<td style='text-align:center;'>" . $_SESSION['lang']['namabarang'] . "</td>
		
		<td style='text-align:center;'>" . $_SESSION['lang']['nopo'] . "</td>
		<td style='text-align:center;'>" . $_SESSION['lang']['tanggalpo'] . "</td>
		<td style='text-align:center;'>" . $_SESSION['lang']['tanggalpenerimaan'] . "</td>
		<td style='text-align:center;'>" . $_SESSION['lang']['satuan'] . "</td>
		<td style='text-align:center;'>" . $_SESSION['lang']['jumlah'] . "</td>
		<td style='text-align:center;'>" . $_SESSION['lang']['harga'] . "</td>
		
		<td style='text-align:center;'>" . $_SESSION['lang']['nilai'] . "</td>
		</tr>
		</thead>";
$nourut = 0;
while ($bar = $res->fetch()) {
    $nourut+=1;
    $stream.="<tbody><tr class=rowcontent>
		<td style='text-align:right;'>" . $nourut . "</td>
		<td>" . $bar->kodebarang . "</td>
		<td>" . $namabarang[$bar->kodebarang] . "</td>
		
		<td>" . $bar->nopo . "</td>
		<td style='text-align:center;'>" . tanggalnormal($tanggalpo[$bar->nopo]) . "</td>
		<td style='text-align:center;'>" . tanggalnormal($tanggaltransaksi[$bar->notransaksi][$bar->kodebarang]) . "</td>
		
		<td style='text-align:center;'>" . $bar->satuan . "</td>
		<td style='text-align:right'>" . $bar->jumlah . "</td>
		<td style='text-align:right'>" . number_format($bar->hargasatuan) . "</td>
		<td style='text-align:right'>" . number_format($bar->hargasatuan * $bar->jumlah) . "</td>
		</tr></tbody>
		<tfoot>
		</tfoot>
		";
		$ttl+=$bar->hargasatuan * $bar->jumlah;
}
	$stream.="<tr class=rowcontent>
		<td colspan=9 align=center>T O T A L</td>
		<td align=right>".number_format($ttl)."</td>
	
	
	</tr>";
if ($num_rows < 1) {
    echo "<p />Data not Found";
} else {
    switch ($proses) {
        case 'showdata':
            echo $stream;
            break;

        case 'excel':
			$nop_ = "Laporan Penerimaan Barang Inventaris_";
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
			
			
            break;

        default:

            break;
    }
}
### END GET DATA FILTER ###
?>