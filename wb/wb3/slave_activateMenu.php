<?
require_once('master_validation.php');
require_once('config/connection.php');
$id=$_POST['id'];
$hideValue=$_POST['setHide'];
try{
        $owlPDO->exec("update ".$dbname.".menu set hide=".$hideValue.", lastuser='".$_SESSION['standard']['username']."' where id=".$id);
}       
 catch (PDOException $e) {
           print " Gagal  !: " . $e->getMessage() . "<br/>";
       die();
    }        
?>