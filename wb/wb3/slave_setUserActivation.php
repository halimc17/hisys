<?
require_once('master_validation.php');
require_once('config/connection.php');
$uname=$_POST['uname'];
$setstatus=$_POST['setstatus'];
$str="update ".$dbname.".user   set status=".$setstatus.",  lastuser='".$_SESSION['standard']['username']."'  where uname='".$uname."'";
try{
          $owlPDO->exec($str);
  }
  catch (PDOException $ex) {
             print " Gagal  !: " . $ex->getMessage() . "<br/>";
              die();
      }
?>
