<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');

$to=$_POST['to'];
$idx=$_POST['idx'];


		$str1="update ".$dbname.".sdm_ho_component 
		       set `pph21`=".$to."
		       where id=".$idx;	   
	try{
		$owlPDO->exec($str1); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage();
		die();
	}			
?>
