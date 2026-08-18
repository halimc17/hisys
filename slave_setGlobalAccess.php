<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
$menuid=$_POST['menuid'];
$defaultMenu=$_POST['menuid'];
#get user  list 
$str=$owlPDO->query("select namauser from ".$dbname.".user where status=1");
$str->setFetchMode(PDO::FETCH_OBJ);
while($barus=$str->fetch())
{
    $resuser[]=$barus->namauser;
}
#get menu and all  it's parent
$menu[]=$menuid;
for($x=0;$x<=7;$x++){
    if($menuid!=''){
            $str=$owlPDO->query("select parent from ".$dbname.".menu where id=".$menuid);
            $str->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$str->fetch()){
                    if($bar->parent!=0){
                        $menu[]=$bar->parent;
                        $menuid=$bar->parent;                    
                    }
                }
    }
}

if($_POST['aksi']=='remove')
{
#remove first
#on removal, only detail , not removing parent, because parent may be a host for some other menu
        $str="delete from ".$dbname.".auth where menuid=".$defaultMenu;
        try{
            $owlPDO->exec($str);
    }
    catch (PDOException $ex) {
               print " Gagal  !: " . $ex->getMessage() . "<br/>";
                die();
        }
}   
#if action ==add then add
 if($_POST['aksi']=='add')
{

foreach($menu as $key=>$val){      
    $owlPDO->exec("delete from ".$dbname.".auth where menuid=".$val);     
    foreach($resuser as $kunci =>$namauser){
           $str="insert into ".$dbname.".auth(namauser, menuid, status, lastuser, detail)
                     values('".$namauser."',".$val.",1,".$_SESSION['standard']['userid'].",0)";
            try{
                      $owlPDO->exec($str);
              }
              catch (PDOException $ex) {
                         print " Gagal  !: " . $ex->getMessage() . "<br/>";
                          die();
                  }
       }    
   }
}
?>
