<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('FIND FILE RELATIONS').'</span>');
?>
<script>
     function findString(strToFind,fullName){
               document.getElementById('resulteend').innerHTML='';
              param='string='+strToFind+'&fullname='+fullName;
              tujuan='tool_slaveFindRelation.php'; 
              post_response_text(tujuan, param, respog);

              function respog(){
                  if (con.readyState == 4) {
                      if (con.status == 200) {
                          busy_off();
                         // if (!isSaveResponse(con.responseText)) {
                          //    alert('ERROR TRANSACTION,\n' + con.responseText);
                         // }
                        //  else {
          //                    alert(con.responseText);
                              document.getElementById('resulte').innerHTML=con.responseText;
                        //  }
                      }
                      else {
                          busy_off();
                          error_catch(con.status);
                  }
                  }
              }
     }
     function findStringEnd(strToFind,fullName){
              param='string='+strToFind+'&fullname='+fullName;
              tujuan='tool_slaveFindRelation.php'; 
              post_response_text(tujuan, param, respog);

              function respog(){
                  if (con.readyState == 4) {
                      if (con.status == 200) {
                          busy_off();
             //             if (!isSaveResponse(con.responseText)) {
             //                 alert('ERROR TRANSACTION,\n' + con.responseText);
            //              }
           //               else {
          //                    alert(con.responseText);
                              document.getElementById('resulteend').innerHTML=con.responseText;
            //              }
                      }
                      else {
                          busy_off();
                          error_catch(con.status);
                  }
                  }
              }
     }     
     
 function browseTable(filename){
              param='string='+filename+'&method=findtable';
              tujuan='tool_slaveFindRelation.php'; 
              post_response_text(tujuan, param, respog);

              function respog(){
                  if (con.readyState == 4) {
                      if (con.status == 200) {
                          busy_off();
         //                   alert(con.responseText);
                              document.getElementById('tableUsageDisplay').innerHTML=con.responseText;
                      }
                      else {
                          busy_off();
                          error_catch(con.status);
                  }
                  }
              }     
 }   
 
 function browseFile(filename){
              param='string='+filename+'&method=viewSource';
              tujuan='tool_slaveFindRelation.php'; 
             post_response_text(tujuan, param, respog);
             function respog(){
                  if (con.readyState == 4) {
                      if (con.status == 200) {
                          busy_off();
          //                    alert(con.responseText);
                              document.getElementById('scriptExplorer').innerHTML=con.responseText;

                      }
                      else {
                          busy_off();
                          error_catch(con.status);
                  }
                  }
              }   
 }   
 
 function markDelete(filename){
              param='string='+filename+'&method=MarkDelete';
              tujuan='tool_slaveFindRelation.php'; 
              
                if(confirm('Mark this file :'+filename+' for deletion?')){
                     post_response_text(tujuan, param, respog);
                }
              function respog(){
                  if (con.readyState == 4) {
                      if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                              alert('ERROR TRANSACTION,\n' + con.responseText);
                             } 
                            else {
                                  alert('Marked');
                                  document.getElementById('markedFiles').innerHTML=con.responseText;
                            }
                      }
                      else {
                          busy_off();
                          error_catch(con.status);
                  }
                  }
              }      
 }
 
  function removeMark(filename){
   param='string='+filename+'&method=removeMark';
              tujuan='tool_slaveFindRelation.php'; 
              
                if(confirm('Remove :'+filename+' from marked list?')){
                     post_response_text(tujuan, param, respog);
                }
              function respog(){
                  if (con.readyState == 4) {
                      if (con.status == 200) {
                            busy_off();
                            if (!isSaveResponse(con.responseText)) {
                              alert('ERROR TRANSACTION,\n' + con.responseText);
                             } 
                            else {
                                  alert('Mark removed');
                                  document.getElementById('markedFiles').innerHTML=con.responseText;
                            }
                      }
                      else {
                          busy_off();
                          error_catch(con.status);
                  }
                  }
              }    
 }
  
 </script>   
<?
echo "<fieldset><legend><b>File Relations</b></legend>"
. "Click <b>Filename</b> to display file relation, click <b>Browse Icon</b> to display table usage, click <b>Source Icon</b> to display source codes";
echo "<table><tr><td><div style='height:300px;width:490px;overflow:scroll'>";
echo"<table><thead><tr><td>No.</td><td>File name</td><td>Action</td></tr></thead><tbody>";
$path= realpath(dirname(__FILE__));
$filelist=Array();
$nameOnly=Array();
#PHP Files on root
    if ($handle = opendir($path)) {
         $count=0;
        while (false !== ($entry = readdir($handle))) {
            if($entry!='.' && $entry!='..' &&  !is_dir($entry) && substr($entry,0,5)!='index'){
                $filelist[]=$entry;
            }
        }
        closedir($handle);
    }
 #Js Files   
    $pathJs=$path."/js";
     if ($handle = opendir($pathJs)) {
        while (false !== ($entry = readdir($handle))) {
            if($entry!='.' && $entry!='..'&&  !is_dir($pathJs."/".$entry) && substr($entry,0,5)!='index'){
                $filelist[]="js/".$entry;         
            }
        }
        closedir($handle);
    }
  #Config Files   
    $pathConf=$path."/config";
     if ($handle = opendir($pathConf)) {
        while (false !== ($entry = readdir($handle))) {
            if($entry!='.' && $entry!='..' &&  !is_dir($pathConf."/".$entry) && substr($entry,0,5)!='index'){
                $filelist[]="config/".$entry;
            }
        }
        closedir($handle);
    }   
  #lib Files   
    $pathLib=$path."/lib";
     if ($handle = opendir($pathLib)) {
        while (false !== ($entry = readdir($handle))) {
            if($entry!='.' && $entry!='..' &&  !is_dir($pathLib."/".$entry) && substr($entry,0,5)!='index'){
                $filelist[]="lib/".$entry;   
            }
        }
        closedir($handle);
    }   
  #StyleFiles   
    $pathStyle=$path."/style";
     if ($handle = opendir($pathStyle)) {
        while (false !== ($entry = readdir($handle))) {
            if($entry!='.' && $entry!='..' &&  !is_dir($pathStyle."/".$entry) && substr($entry,0,5)!='index'){
                $filelist[]="style/".$entry;   
            }
        }
        closedir($handle);
    }    
 foreach($filelist as $key =>$filename){    
     $bb=  explode(".", $filename);
     if($bb[(count($bb)-1)]=='php'){
         $stq="<img src='images/zoom.png' style='cursor:pointer;height:16px;' title='Browse table usage' onclick=\"browseTable('".$filename."');\">";
     }else{
         $stq='';
     }
     $stm="<img src='images/application/application_view_list.png' style='cursor:pointer;height:16px;' title='View Source' onclick=\"browseFile('".trim($filename)."');\">";
     echo "<tr class=rowcontent><td>".($key+1)."</td><td style='cursor:pointer;' onclick=\"findString('".trim($filelist[$key])."','".trim($filelist[$key])."');\" title='Find relation of ".$filelist[$key]."'>".trim($filename)."</td>"
             . "<td align=right>".$stq." ".$stm."</td></tr>";
 }   
echo"</tbody><tfoot></tfoot></table>";   
echo"</div></td><td id=resulte></td>
                                <td id=resulteend></td></tr></table>";
echo "</fieldset>";
#get Marked Files
$str=$owlPDO->query("select * from ".$dbname.".unused_files order by lastupdate desc");
$str->setFetchMode(PDO::FETCH_OBJ);
$markedFiles="<table class=sortable border=0 cellspacing=1><thead><tr class=rowheader><td>No.</td><td>Filename</td><td>MarkedBy</td><td>MarkDate</td><td>FileStatus</td></tr></thead><tbody>";
$nomor=0;
while($bar=$str->fetch()){
    if(is_file($bar->filename)){
        $fileStatus="File Exist <button class=mybutton onclick=\"removeMark('".$bar->filename."');\">Remove mark</button>";
    }else{
        $fileStatus='File Deleted';
    }
    $nomor++;
    $markedFiles.="<tr class=rowcontent><td>".$nomor."</td><td>".$bar->filename."</td><td>".$bar->username."</td><td>".$bar->lastupdate."</td><td>".$fileStatus."</td></tr>";
}
$markedFiles.="</tbody></table>";
echo"<fieldset>
           <legend><b>Table Usage</b> (<a style='cursor:pointer;font-size:18px;color:blue' onclick=document.getElementById('tableUsageDisplay').style.display=''; title='Show'>+</a> / <a style='cursor:pointer;font-size:18px;color:blue'  onclick=document.getElementById('tableUsageDisplay').style.display='none'; title='Hide'>-</a>)</legend>
           <div id=tableUsageDisplay style='width:1000px;height:150px;overflow:scroll'>
           </div>
          </fieldset>
          <fieldset>
           <legend><b>Script Explorer</b> (<a style='cursor:pointer;font-size:18px;color:blue' onclick=document.getElementById('scriptExplorer').style.display=''; title='Show'>+</a> / <a style='cursor:pointer;font-size:18px;color:blue'  onclick=document.getElementById('scriptExplorer').style.display='none'; title='Hide'>-</a>)</legend>
           <div id=scriptExplorer style='background-color:#FFFFFF;width:1000px;height:200px;overflow:scroll'>
           </div>
          </fieldset>
          <fieldset>
           <legend><b>Marked file(s)</b> (<a style='cursor:pointer;font-size:18px;color:blue' onclick=document.getElementById('markedFiles').style.display=''; title='Show'>+</a> / <a style='cursor:pointer;font-size:18px;color:blue'  onclick=document.getElementById('markedFiles').style.display='none'; title='Hide'>-</a>)</legend>
           <div id=markedFiles style='width:1000px;height:150px;overflow:scroll'>".
           $markedFiles
           ."</div>
          </fieldset>";   
CLOSE_BOX();
echo close_body();
?>