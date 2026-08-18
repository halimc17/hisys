<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
$uname=$_POST['uname'];

        $str=$owlPDO->query("select * from ".$dbname.".user where uname like '%".$uname."%'");
        $str->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($str);
        if($numrows>0)
        {
                echo"<b>Click on choosen row to show \"reset password form\".</b><hr>
            <table class=sortable cellspacing=1 border=0 onmousedown=sorttable.makeSortable(this)>
                     <thead>
                           <tr>
                           <td>Nama Pengguna</td>
                           <td>Status</td>
                           </tr>
                         </theader>
                         <tbody>";
                while($bar=$str->fetch())
                 {
                        $opt='';
                        if($bar->status==0)
                        {
                                $opt.="<font color=#aa3333>Not Active</font>"; 
                        }
                        else
                        {
                                $opt.="<font color=#00ff00>Active</font>"; 
                        }
                        echo" <tr class=rowcontent id='row".$bar->uname."' title='Click to show dialog' style='cursor:pointer;' onclick=\"showDial('".$bar->uname."',event,this);\">
                              <td class=firsttd>".$bar->uname."</td>
                                  <td align=center>".$opt."</td>
                         </tr>";
              }
                echo"	 
                         </tbody>
                    </table>
                        ";
        }
        else
        {
                echo "No data found..";
        }
?>
