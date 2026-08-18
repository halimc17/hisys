<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$bayar=$_POST['bayar'];
$tglbayar=tanggalsystem($_POST['tglbayar']);
$notransaksi=$_POST['notransaksi'];

	$str="update ".$dbname.".sdm_pjdinasht set dibayar=".$bayar."
	      ,tglbayar=".$tglbayar." where notransaksi='".$notransaksi."'";
	
	try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
?>
