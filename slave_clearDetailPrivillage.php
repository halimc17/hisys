<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
$uname=trim($_POST['uname']);

$str="delete from ".$dbname.".auth
	        where namauser='".$uname."'";
try{
	$owlPDO->exec($str);
}catch (PDOException $e){
	echo "error : ".$e->getMessage();
}
?>
