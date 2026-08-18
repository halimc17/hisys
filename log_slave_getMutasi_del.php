<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$notransaksi=$_POST['notransaksi'];
$method=$_POST['method'];

switch($method)
{
	case'delete':
		$x="update ".$dbname.".log_transaksiht set notransaksireferensi='' where notransaksireferensi='".$notransaksi."'";	
		try{
			$owlPDO->exec($x); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die();
		}
		
		$i="delete from ".$dbname.".log_transaksiht where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($i); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
}
?>