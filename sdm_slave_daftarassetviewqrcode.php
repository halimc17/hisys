<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
require_once('phpqrcode/qrlib.php');

$namafile= checkPostGet('namafile', '');
$folder="fileupload/qrcodeasset/";

$tab="";
if(file_exists($folder.$namafile)){
	$tab.="<img src='".$folder.$namafile."' >";	
}else{
	$tab.="<img src='images/question.png' style='width:130px;height:83px;'>";
}

echo $tab;

?>