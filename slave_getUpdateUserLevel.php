<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');

$newlevel=$_POST['newlevel'];
$uname=$_POST['un'];
$str="update ".$dbname.".user set hak=".$newlevel.",
      lastuser='".$_SESSION['standard']['username']."',
          lastupdate='".date('Y-m-d H:i:s')."'
       where namauser='".$uname."'";
 try{
        $owlPDO->exec($str);
}
catch (PDOException $e) {
           print " Gagal  !: " . $e->getMessage() . "<br/>";
            die();
    }       
?>
