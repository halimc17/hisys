<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');

$acname=$_POST['acname'];
$str="update ".$dbname.".tipeakses set status=0";
$str1="update ".$dbname.".tipeakses set status=1 where access_name='".$acname."'";
try{
          $owlPDO->exec($str);
          $owlPDO->exec($str1);
  }
  catch (PDOException $ex) {
             print " Gagal  !: " . $ex->getMessage() . "<br/>";
              die();
      }
?>
