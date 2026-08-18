<?
require_once('master_validation.php');
require_once('config/connection.php');

$fromId   =$_POST['from'];
$toId     =$_POST['to'];
$orderFrom=$_POST['orderfrom'];
$orderTo  =$_POST['orderto'];
$temp     =329027;
try {
	$owlPDO->beginTransaction();
	
$str="update ".$dbname.".menu set urut=".$temp." where id=".$toId;
$str1="update ".$dbname.".menu set urut=".$orderTo.", lastuser='".$_SESSION['standard']['username']."' where id=".$fromId;
$str2="update ".$dbname.".menu set urut=".$orderFrom.", lastuser='".$_SESSION['standard']['username']."' where id=".$toId;
	$owlPDO->exec($str);
	$owlPDO->exec($str1);
	$owlPDO->exec($str2);
		#execute
	$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Error, " . addslashes($e->getMessage());
	die();
}
?>
