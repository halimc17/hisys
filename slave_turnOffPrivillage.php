<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
$str="update  ".$dbname.".tipeakses set status =0 where status!=0";
      try{
                $owlPDO->exec($str);
        }
        catch (PDOException $e) {
                   print " Gagal  !: " . $e->getMessage() . "<br/>";
                    die();
        }
?>
