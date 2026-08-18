<?
require_once('master_validation.php');
require_once('config/connection.php');

$notransaksi=$_POST['notransaksi'];

  $str="select hasilkerja from ".$dbname.".sdm_pjdinasht 
	      where notransaksi='".$notransaksi."'";
  $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  
  while($bar=$res->fetch())
  {
	  echo $bar->hasilkerja;
  }  
?>



