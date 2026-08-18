<?
require_once('master_validation.php');
require_once('config/connection.php');

$kelompok=$_POST['kelompok'];
  $str="select * from ".$dbname.".vhc_5jenisvhc where kelompokvhc='".$kelompok."' order  by namajenisvhc";
  $optjnsvhc="<option value=''></option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
  {
  	$optjnsvhc.="<option value='".$bar->jenisvhc."'>".$bar->namajenisvhc."</option>";
  }
echo  $optjnsvhc; 
?>
