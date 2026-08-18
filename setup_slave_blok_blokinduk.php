<?php
error_reporting(0);
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

$afdeling = $_POST['afdeling'];
$kodeorg = $_POST['kodeorg'];
$where1 = "(tipe='BLOK' or tipe='BIBITAN') and kodeorganisasi='".$kodeorg."'";
$blok = makeOption($dbname,'organisasi','indukblok,kodeorganisasi',$where1);

$x=0;
foreach($blok as $key=>$row) {
	echo substr($key,0,9);
}
?>