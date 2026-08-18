<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$kodeorg = $_POST['kodeorg'];
$where1 = "(tipe='BLOK' or tipe='BIBITAN') and kodeorganisasi='".$kodeorg."'";
$blok = makeOption($dbname,'organisasi','kodeorganisasi,kodeorganisasi',$where1);

$indukblok = makeOption($dbname,'organisasi','kodeorganisasi,indukblok',$where1);

$x=0;
foreach($blok as $key=>$row) {
	//if($x==0){
		echo $indukblok[$key];
		$x=1;
	//}
    //echo "kodeorg.options[kodeorg.options.length] = new Option('".$row."','".$key."');";
}
?>