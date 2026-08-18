<?php
session_start();
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('config/connection.php');

$method=checkPostGet('method','');
$statuspajak=checkPostGet('statuspajak','');

switch($method){
	case'getstatuspajak':
		$str="select * from ".$dbname.".sdm_5statuspajak where inisial='".$statuspajak."'";
		$res=fetchdata($str);
		echo $res[0]['kode'];
	break;
}
?>