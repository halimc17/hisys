<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$uname=trim($_POST['uname']);
$p1=$_POST['p1'];
$p2=$_POST['p2'];

//pastikan penggantian atas nama dia sendiri sama dengan yang login
$str="select * from ".$dbname.".user where namauser='".$uname."' 
      and karyawanid='".$_SESSION['standard']['userid']."'";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
if($numrows<1){
   exit("Error: you are not the user as defined");
}	  

$str="select * from ".$dbname.".user where namauser='".$uname."'
      and password=PASSWORD('".$p1."')";  
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);  
$numrows=owlBaris($res);
if($numrows<1)
{
    echo " Gagal:Old password doesn't match";
}
else
{
$str="update ".$dbname.".user set password=PASSWORD('".$p2."'),
                     lastuser='".$_SESSION['standard']['username']."' where namauser='".$uname."'";
try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
}
?>
