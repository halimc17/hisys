<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/nangkoelib.php');

$id=$_POST['id'];

$str=$owlPDO->query("select * from ".$dbname.".menu where parent=".$id);
$str->setFetchMode(PDO::FETCH_NUM);
$numrows=owlBaris($str);
if($numrows>0)
{
  echo " Gagal, Hapus dari submenu paling dalam";	
}
else
{
    try{
         $owlPDO->exec("delete from ".$dbname.".menu  where id=".$id);
         $owlPDO->exec("delete from ".$dbname.".auth where menuid=".$id);        
    } catch (PDOException $ex) {
           print " Gagal  !: " . $ex->getMessage() . "<br/>";
            die();
    }
}
?>
