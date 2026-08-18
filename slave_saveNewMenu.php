<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/nangkoelib.php');

$parent=$_POST['id_parent'];
$caption=$_POST['caption'];
$caption2=$_POST['caption2'];
$caption3=$_POST['caption3'];
$action=$_POST['action'];
$class=$_POST['class'];
$createFile=$_POST['create'];
if($parent=='')
    $parent=0;
//check menu deep. max 6
$nex_parent=$parent;
$deep=0;
for($x=0;$x<8;$x++)
{
        $st=$owlPDO->query("select parent from ".$dbname.".menu where id=".$nex_parent);
        $st->setFetchMode(PDO::FETCH_NUM);
        $numrows=owlBaris($st);
    if($numrows>0)
        {
          $deep+=1;
          while($ba=$st->fetch())
              {
                $nex_parent=$ba[0];
                  }

        }
        else
        {
                break;
        }
}

if($deep>6)
{
        echo " Warning: Menu to deep(max 6 child)";
}
else
{
if($parent==0)
  $type='master';
else
  $type='list';  

if($class=='devider')
   {
        $caption='';
        $action='';
   }
if($class=='title')
   {
        $action='';
   }   

$str=$owlPDO->query("select max(urut) from ".$dbname.".menu where parent=".$parent);
$str->setFetchMode(PDO::FETCH_NUM);
while($bar=$str->fetch())
{
        $urut=$bar[0];
}

if(!isset($urut))
 {$urut=0;}

  $nex_urut=$urut+1;
  $str="insert into ".$dbname.".menu (
                  type,
                  class,
                  caption,
                caption2,
                caption3,
                  action,
                  parent,
                  urut,
                  hide,
                  lastuser)
                          values(
                      '".$type."',
                          '".$class."',
                          '".$caption."',
                        '".$caption2."',
                        '".$caption3."',     
                          '".$action."',
                           ".$parent.",
                           ".$nex_urut.",
                          1,
                          '".$_SESSION['standard']['username']."'
                          )";
  try{
        $owlPDO->exec($str);
                //set type as parent where id EQ $parent
                if($parent!=0)
                {
                    try{
                        $owlPDO->exec("update ".$dbname.".menu set type='parent'
                                where id=".$parent." and type='list'");
                    }catch(PDOException $e){
                                   print " Gagal!: on insert parent " . $e->getMessage() . "<br/>";
                                   die();
                    }
                }
                //create file
                if($createFile=='yes')
                {
                        $filename=$action.".php";
                                if (file_exists($filename)) {
                                    //do nothing
                                } else {
                                    //write file
                                        $defaulContent="<?//@Copy nangkoelframework?>";
                                        $handle=fopen($filename,'w');
                                         if(!fwrite($handle,$defaulContent))
                                         {					 	
                                         }
                                         else
                                         {
                                         }
                                         fclose($handle);
                                }

                }

                //ambil id terakhir
                $str2=$owlPDO->query("select max(id) from ".$dbname.".menu");
                $str2->setFetchMode(PDO::FETCH_NUM);
                while($bar2=$str2->fetch())
                {
                        $max=$bar2[0];
                }
                if($deep>5)
                   echo $max.",stop";
                else
                   echo $max.",available";		
        }
    catch (PDOException $e) {
           print " Gagal insert !: " . $e->getMessage() . "<br/>";
       die();
    }
}
?>
