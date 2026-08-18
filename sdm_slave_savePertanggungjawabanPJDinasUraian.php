<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$notransaksi=$_POST['notransaksi'];
$uraian=$_POST['uraian'];
	
	$str="update ".$dbname.".sdm_pjdinasht 
	      set hasilkerja='".$uraian."'
		  where notransaksi='".$notransaksi."'";
	try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo " Gagal:".addslashes($e->getMessage());	  
		exit(0);
	}

?>