<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/nangkoelib.php');

$parent=$_POST['parent'];
$child=$_POST['child'];

$arrchild = explode('#$#', $child);
/*echo '<pre>';
print_r($arrchild);
echo '</pre>';
exit('error');*/
foreach ($arrchild as $key => $val) {
// exit('error' . $val);
  //get urut
$str=$owlPDO->query("select max(urut) from ".$dbname.".menu where parent=".$parent);
$str->setFetchMode(PDO::FETCH_NUM);
while($bar=$str->fetch())
{
  $urut=$bar[0];
}
//next urut is
$urut+=1;

//update menu table
$str1="update ".$dbname.".menu
      set parent=".$parent.", urut=".$urut.",lastuser='".$_SESSION['standard']['username']."'
    where id=".$val;    
$str2="update ".$dbname.".menu
   set type='parent' where id=".$parent." and type!='master'";
//exit('error '.$str1);
try{
          $owlPDO->exec($str1);
          $owlPDO->exec($str2);          
  }
  catch (PDOException $e) {
             print " Gagal  !: " . $e->getMessage() . "<br/>";
             die();
      }
}

?>
