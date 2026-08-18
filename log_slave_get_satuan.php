<?
require_once('master_validation.php');
require_once('config/connection.php');

    $satuan     =$_POST['satuan'];
    $oldsatuan  =$_POST['oldsatuan'];
    $method =$_POST['method'];


        switch($method){
                case 'delete':
                        $strx="delete from ".$dbname.".setup_satuan where satuan='".$satuan."'";
                break;
                case 'update':
                        $strx="update ".$dbname.".setup_satuan set  satuan='".$satuan."' where satuan='".$oldsatuan."'";
                break;	
                case 'insert':
                        $strx="insert into ".$dbname.".setup_satuan(satuan)  values('".$satuan."')";	   
                break;
                default:
        break;	
        }
try{$owlPDO->exec($strx); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }	

$str=$owlPDO->query("select * from ".$dbname.".setup_satuan order by satuan");
$str->setFetchMode(PDO::FETCH_OBJ);
$no=0;	 
while($bar=$str->fetch())
{
        $no+=1;
        echo"<tr class=rowcontent>
                 <td>
                        ".$no."
                 </td>
                 <td>
                    ".$bar->satuan."
                 </td>
                  <td>
                      <img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar->satuan."');\"> 
                          <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delSatuan('".$bar->satuan."');\">
                  </td>		 
                </tr>";	
}   
?>