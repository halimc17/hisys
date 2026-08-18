<?php
include('lib/nangkoelib.php');
include('lib/zLib.php');
require_once('config/connection.php');
$folder = "imgbot/";

$file = glob($folder."*");
foreach($file as $f){
	$no++;
	echo $no.". ".$f."<br>";
}


?>	