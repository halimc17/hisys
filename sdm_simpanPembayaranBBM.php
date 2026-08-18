<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$notransaksi=$_POST['notransaksi'];
$bayar		=$_POST['bayar'];
$tglbayar	=tanggalsystem($_POST['tglbayar']);
$str="update ".$dbname.".sdm_penggantiantransport set dibayar=".$bayar.",
      tanggalbayar=".$tglbayar.",
	  posting=1
	  where notransaksi='".$notransaksi."'";
try{
	$owlPDO->exec($str); 
}catch (PDOException $e){
	echo " Gagal ".addslashes($e->getMessage());
	die();
}
?>
