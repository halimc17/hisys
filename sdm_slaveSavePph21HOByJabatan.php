<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');

$persen=$_POST['persen'];
$max=$_POST['max'];


	$str="delete from ".$dbname.".sdm_ho_pph21jabatan";
	try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage();
		die();
	}

	$str1="insert into ".$dbname.".sdm_ho_pph21jabatan(`persen`,`max`) 
		       values(".$persen.",".$max.")";	
	try{
		$owlPDO->exec($str1); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage();
		die();
	}				
?>
