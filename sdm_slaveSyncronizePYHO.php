<?php

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');

$userid = checkPostGet('userid', '');
$nama = checkPostGet('nama', '');
$mstatus = checkPostGet('mstatus', '');
$start = checkPostGet('start', '');
$resign = checkPostGet('resign', '');
$npwp = checkPostGet('npwp', '');

$stk = "select * from " . $dbname . ".sdm_ho_employee where karyawanid=" . $userid;
$res=$owlPDO->query($stk) or die(print " Gagal: ".PDOException::getMessage());
$numrows=owlBaris($res);
if ($numrows < 1) {
    $str = "insert into " . $dbname . ".sdm_ho_employee (karyawanid,startdate,enddate,name,taxstatus,npwp)
		      values(" . $userid . ",'" . tanggalsystem($start) . "','" . tanggalsystem($resign) . "','" . $nama . "','" . $mstatus . "','" . $npwp . "')";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Error: " . addslashes($e->getMessage());
		die();
	}
} else {
    $stra = "update " . $dbname . ".sdm_ho_employee set
	        startdate='" . tanggalsystem($start) . "',
			enddate='" . tanggalsystem($resign) . "',
			name='" . $nama . "',
			taxstatus='" . $mstatus . "',
			npwp='" . $npwp . "'
			where karyawanid=" . $userid;
	try{
		$owlPDO->exec($stra); 
	}
	catch (PDOException $e){
		echo " Error: " . addslashes($e->getMessage());
		die();
	}
}
?>
