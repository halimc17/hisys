<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');

$code=$_POST['code'];
   $sta="select * from ".$dbname.".sdm_strukturjabatan where kodestruktur='".$code."'";
   $re=$owlPDO->query($sta) or die(print " Gagal: ".PDOException::getMessage());
   $re->setFetchMode(PDO::FETCH_OBJ);
   $numrows=owlBaris($re);
if($numrows>0){
   while($be=$re->fetch())
   {
	 //detail
	 echo $be->kodestruktur."|".$be->karyawanid."|".$be->kodejabatan."|".$be->email."|".$be->detail."|".$be->kodept; 	
   }
 }
else
{
	echo "-1";
} 
?>
