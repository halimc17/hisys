<?php
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

## GET FROM DATA KARYAWAN FP ##
$str="select * from ".$dbnamefp.".pegawai where flag='0'";
$res=fetchdata($str);
if(count($res) > 0){
	foreach($res as $val){
		echo $val['pegawai_pin']."__".$val['pegawai_nama']."<br>";
	}
}

?>