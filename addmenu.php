<?
#require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
?>

<?
$str="select * from ".$dbname.".menu";  
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$del="delete from ".$dbname.".auth where menuid='".$bar['id']."'";
	try{$owlPDO->exec($del);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
	
	$ins="INSERT INTO `auth` (`namauser`, `menuid`, `status`, `lastuser`, `detail`) 
		  VALUES ('uharyanto','".$bar['id']."','1','','0')";
	try{$owlPDO->exec($ins);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
} 


?>