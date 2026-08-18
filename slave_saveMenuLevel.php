<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');

$level=trim($_POST['level']);
$id=$_POST['id'];
$str="update ".$dbname.".menu  set access_level=".$level."  where id=".$id;
try{
        $owlPDO->exec($str);
}
catch (PDOException $e) {
           print " Gagal  !: " . $e->getMessage() . "<br/>";
            die();
    }
?>
