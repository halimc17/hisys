<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');

$id=$_POST['id'];
$caption=$_POST['caption'];
$caption2=$_POST['caption2'];
$caption3=$_POST['caption3'];
$action=$_POST['action'];
try{
        $owlPDO->exec("update ".$dbname.".menu set action='".$action."',caption='".$caption."',caption2='".$caption2."',caption3='".$caption3."'  where id=".$id);
}
catch (PDOException $e) {
           print " Gagal  !: " . $e->getMessage() . "<br/>";
            die();
    }
?>
