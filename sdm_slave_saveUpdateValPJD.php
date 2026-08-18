<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$newvalpjd	=$_POST['newvalpjd'];
$notransaksi	=$_POST['notransaksi'];
		$strup="update ".$dbname.".sdm_pjdinasht set uangmuka=".$newvalpjd."	
		       where notransaksi='".$notransaksi."'";
try{
	$owlPDO->exec($strup); 
	echo number_format($newvalpjd,2,'.',',');
}catch (PDOException $e){
	echo " Gagal,".addslashes($e->getMessage());
}

?>
