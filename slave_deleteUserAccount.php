<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');

$uname=$_POST['uname'];
$userid=$_POST['userid'];

$str="delete from ".$dbname.".user  where namauser='".$uname."'";
$stg="delete from ".$dbname.".auth where namauser='".$uname."'";
try{
          $owlPDO->exec($str);
           $owlPDO->exec($stg);
  }
  catch (PDOException $ex) {
             print " Gagal  !: " . $ex->getMessage() . "<br/>";
              die();
      }
?>
