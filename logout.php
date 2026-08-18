<?
session_start();
require_once('config/connection.php');
// require_once('master_validation.php');
include('lib/nangkoelib.php');
$str=$owlPDO->query("update ".$dbname.".user set logged=0 where namauser='".$_SESSION['standard']['username']."'");
$str->setFetchMode(PDO::FETCH_OBJ);
$count = owlBaris($str);
if($count>0)
   session_destroy();   
echo"<script language=javascript1.2>
     window.location='.';
	 </script>";   

?>
