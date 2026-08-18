<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/nangkoelib.php');

$id=$_POST['id'];

        $str=$owlPDO->query("select caption,caption2,caption3, action, class as cl,type from ".$dbname.".menu    where id=".$id);
        $str->setFetchMode(PDO::FETCH_OBJ);	
        $numrows=owlBaris($str);
        if($numrows<1)
        {

                echo " Gagal, Item menu tsb sudah dihapus";
        }
        else
        {
                while($bar=$str->fetch())
                {
                        $caption=$bar->caption;
                        $caption2=$bar->caption2;
                        $caption3=$bar->caption3;
                        $action=$bar->action;
                        $class=$bar->cl;
                        $type=$bar->type;
                }
        if($class=='devider')
          {
                echo " Gagal, Devider tidak dapat di ganti/edit";
          }
          else
          {
                if($class=='title' or $type=='master')
                        $disabled='disabled';
                else
                    $disabled='';	
                echo"<span style='text-align:center;'>
                  <input type=text value='".$caption."'  maxlength=40 class=myinputtext title='Text to be shown on menu' id=editcaption".$id." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
                                          <input type=text value='".$caption2."'  maxlength=40 class=myinputtext title='Text to be shown on menu' id=editcaption2".$id."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
                                          <input type=text value='".$caption3."'  maxlength=40 class=myinputtext title='Text to be shown on menu' id=editcaption3".$id."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>    
                              <input type=text value='".$action."'  maxlength=40 class=myinputtext title='Filename (without extension) that will be execute when menu clicked' id=editaction".$id." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this) ".$disabled.">
                  <input type=button class=mybutton value=Save onclick=saveEditedMenu('".$id."');>
                  <input type=button class=mybutton value=Close onclick=\"clearFormEdit('edit".$id."');\">
                  </span>";      
          }	
        }
?>
