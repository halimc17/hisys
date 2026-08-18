<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');


$str="delete from ".$dbname.".gps_interval";
try{
	$owlPDO->exec($str); 
}
catch (PDOException $e){
	echo " Gagal,".addslashes($e->getMessage());
	die();
}

$str="insert into ".$dbname.".gps_interval values('".$_POST['interval']."','".$_POST['alo']."')";
    
try{
	$owlPDO->exec($str); 
}
catch (PDOException $e){
	echo " Gagal,".addslashes($e->getMessage());
	die();
}


$str1 = "select * from " . $dbname . ".gps_interval";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);

while ($bar1 = $res1->fetch()) {
    echo"<tr class=rowcontent><td>".$bar1->interval."</td><td>".$bar1->enableupload."</td></tr>";
}
?>
