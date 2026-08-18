<?php
error_reporting(1);
$mobileValid = false;
if(isset($_POST['par']) or isset($_GET['par'])){
	$validasiPostMobile = explode(" ",$_POST['par']);
	$validasiGetMobile = explode(" ",$_GET['par']);
	if($validasiGetMobile[0] == "owlApp" or $validasiPostMobile[0] == "owlApp"){
		$mobileValid = true;
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php'); 
}
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');
require_once('lib/zLib.php');
include_once('lib/zPdfMaster.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$urlefil=checkPostGet('urlefil','0');

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];

$str_1="select * from ".$dbname.".log_5masterbarang where kodebarang='".$column."'";
$res_1=fetchdata($str_1);
$namabarang = $res_1[0]['namabarang'];
$kodebarang = $res_1[0]['kodebarang'];
$satuan = $res_1[0]['satuan'];

$nmOrg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');


$tab.="<html>
<style type='text/css'>
	@page {
		margin-top: 5px;
		margin-left: 10px;
		margin-right: 10px;
		margin-bottom: 10px;
		
	}
	footer {
		position: fixed; 
		bottom: 10px; 
		left: 0px; 
		right: 0px;
		height: 50px; 
	}
	header {
		position: fixed; 
		top: 0px;
		left: 0px; 
		right: 0px;
		bottom: 0px;
		margin-top: 65px;
		font-weight:bold;
	}
</style>";

$font_size = "font-size:5px";


## CREATE TITLE HEADER

$tab.="<body><header>
<table width=100% cellpadding=0 cellspacing=0 style='text-align:center;".$font_size."'>
	<thead>
	<tr style='font-weight:bold;font-size:10px;'>
		<td style='color:green;font-weight:bold;border-bottom:0.5px solid black' colspan=2>BIN CARD</td>
	</tr>
</table>

	<table width=100% cellpadding=2 cellspacing=2 style='".$font_size."'>
		<tr>
			<td valign=top>
				<table>
					<tr>
						<td colspan=3>". $nmOrg[$_SESSION['org']['kodeorganisasi']]."</td>
					</tr>
					<tr>
						<td>Kode Barang</td>
						<td>:</td>
						<td style='transform:translate(-12,0)'>".$kodebarang."</td>
					</tr>
					<tr>
						<td>Nama Barang</td>
						<td>:</td>
						<td style='transform:translate(-12,0)'>".$namabarang."</td>
					</tr>
					<tr>
						<td>Satuan</td>
						<td>:</td>
						<td style='transform:translate(-12,0)'>".$satuan."</td>
					</tr>
				</table>
			</td>
		</tr>
	</thead>
	</table></header>";

		$tab .= "<table width='100%' cellpadding='1' border='0.5' cellspacing='0' style='".$font_size.";'>
		<thead>
			<tr style='".$font_size.";font-weight:bold;background-color:green;color:white'>
				<th rowspan='2' style='text-align:center;border:0.5px solid black;width:17%'>Tanggal</th>
				<th colspan='2' style='text-align:center;border:0.5px solid black;width:10%'>Perubahan</th>
				<th rowspan='2' style='text-align:center;border:0.5px solid black;width:17%'>Sisa</th>
				<th rowspan='2' style='text-align:center;border:0.5px solid black;'>Ket</th>
			</tr>
			<tr style='".$font_size.";font-weight:bold;background-color:green;color:white'>
				<th style='text-align:center;border:0.5px solid black;'>Masuk</th>
				<th style='text-align:center;border:0.5px solid black;'>Keluar</th>
			</tr>
		</thead>";

			$tab .= "<tbody>";
			for ($i = 0; $i <= 23; $i++) {
			$tab .= "
			<tr>
				<td  style='text-align:center;border:0.5px solid black;height:5px'></td>
				<td  style='text-align:center;border:0.5px solid black;'></td>
				<td  style='text-align:center;border:0.5px solid black;'></td>
				<td  style='text-align:center;border:0.5px solid black;'></td>
				<td  style='text-align:center;border:0.5px solid black;'></td>
			</tr>";
			}
			$tab .= "</tbody>";

		$tab .= "</table>";


$dompdf = new Dompdf();
$dompdf->loadHtml($tab);
$dompdf->setPaper('A4', 'portrait');

// Set custom paper size with width 11 cm and height 21 cm
$dompdf->setPaper([0, 0, 110, 210], 'portrait');

$dompdf->render();
$sizefont=12;
$font = $dompdf->getFontMetrics()->get_font("Times-Roman", "");

## Print Out
if($urlefil=='0'){
	$dompdf->stream("Print_BINCARD_".$namabarang."_".$column,array("Attachment"=>0));
}else{
	file_put_contents($urlefil, $dompdf->output());
}
?>