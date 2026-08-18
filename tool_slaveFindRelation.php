<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/admin_validation.php');

$param=$_POST;
if($param['method']==='findtable'){
            #this file contain:=================================
            $arrayChunk=Array();
            $fileContent=file_get_contents($param['string']);
            $fileContent=  str_replace('\n', ";", $fileContent);
            $fileContent=  str_replace('\r', ";", $fileContent);
            $fileContent=  str_replace('<?php', "", $fileContent);            
            $fileContent=  str_replace('<?', "", $fileContent);
           // $fileContent=  str_replace(':', ";", $fileContent);
            $fileContent=  str_replace('global', ";", $fileContent);
            $fileContent=  str_replace('{', ";", $fileContent);
            $fileContent=  str_replace('}', ";", $fileContent);
            $fileContent=  str_replace('//', ";", $fileContent);   
            $fileContent=  str_replace('===', ";", $fileContent);       
            $fileContent=  str_replace('*/', ";", $fileContent);
            $fileContent=  str_replace("':", ";", $fileContent);               
            $fileContent=  str_replace("  ", " ", $fileContent);

            $arrayChunk=explode(";",$fileContent);
            $tableName=Array();
            if(count($arrayChunk)>0){
                    $count=0;
                    foreach($arrayChunk as $key =>$val){
                        #find if any database
                        $val=trim($val);
                        $pos1 = strpos($val, '$dbname');
                        #is comment or not
                        $comment=false;
                        if(substr($val, 0,1)=='/' or substr($val, 0,1)=='#' or substr($val, 0,1)=='*' or substr($val, 0,1)=='='  or substr($val, 0,2)=='/*' or $val=='$dbname'){
                            $comment=true;
                        }
                        if($pos1!==false && !$comment){
                            $uu=str_replace(".","",$val);
                            $uu=str_replace("`","",$uu);                            
                            $uu=str_replace(","," ",$uu);
                            $uu=str_replace('""'," ",$uu);                            
                            $uu=str_replace('"'," ",$uu);
                            $uu=str_replace("'"," ",$uu);                           
                            $uu=str_replace("("," ",$uu);
                            $uu=str_replace(")","",$uu);
                            $uu=str_replace("  "," ",$uu);                            
                            $zz=  explode(" ",$uu);
                            $listtable='';
                            $counted=true;
                            foreach($zz as $mm =>$xx){
//                                echo "<pre>";
//                               print_r($zz);
//                               echo "</pre>";
                                   if($xx=='$dbname' && substr($zz[($mm+1)], 0,1)!='$'){
                                        if($mm<(count($zz)-1)){                                            
                                            if(substr($zz[($mm+1)], 0,1)==''){
                                                   if(isset($zz[($mm+2)])){
                                                                if($listtable==''){
                                                                 $listtable.=$zz[($mm+2)];  
                                                                }else{
                                                                     $listtable.=", ".$zz[($mm+2)]; 
                                                                }                                     
                                                   }else{
                                                   }
                                            }else{
                                                 if(isset($zz[($mm+1)])){
                                                        if($listtable==''){
                                                            $listtable.=$zz[($mm+1)];  
                                                        }else{
                                                             $listtable.=", ".$zz[($mm+1)]; 
                                                        }
                                                 }     
                                            }
                                         }else{
                                            #means this line not included
                                            $counted=false;
                                        }    
                                    }
                            }
                            if($counted){
                                $listtable==''?$listtable='[variable]':null;
                                $tableName[]=$listtable;
                                $filtered[]=$val;
                            }
                        }
                    }
            }
            echo "File :<b>".$param['string']."</b> use the folowing table(s):";
            echo"<table cellspacing=1 border=0><thead><tr><td>No.</td><td>TableName</td><td>Tag</td></tr></thead><tbody>";           
            if(count($filtered)>0){
              $filtered=array_unique($filtered);
                $count=0;
               foreach($filtered as $key=>$val){ 
                    $count++;
                    echo "<tr class=rowcontent><td>".$count."</td><td  style='background-color:#FFFFD4;'>".$tableName[$key]."</td><td style='background-color:#FFFFFF;'>".$val."</td></tr>";                        
               }
            }   
            echo"</tbody><tfoot></tfoot></table>";          
}else if($param['method']==='viewSource'){
            #this file contain:=================================
       $text=show_file($param['string']);
       echo "<span>File <i><b>".$param['string']."</b> </i>source:</span> <button class=mybutton style='color:red;' onclick=\"markDelete('".$param['string']."');\">Mark for deletion</button>".$text;
  
}else if($param['method']==='MarkDelete'){
    $str="insert into ".$dbname.".unused_files(filename,username) values('".$param['string']."','".$_SESSION['standard']['username']."')";
    try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        #update list display
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
            if($bar->filename==$param['string']){
                $color=" style='background-color:orange;'";
            }else{
                $color='';
            }
            $nomor++;
            $markedFiles.="<tr class=rowcontent><td ".$color.">".$nomor."</td><td ".$color.">".$bar->filename."</td><td ".$color.">".$bar->username."</td><td ".$color.">".$bar->lastupdate."</td><td ".$color.">".$fileStatus."</td></tr>";
        }    
        echo    $markedFiles;
}else if($param['method']==='removeMark'){
        #delete from marked table
        $str="delete from ".$dbname.".unused_files where filename='".$param['string']."'";
       try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        #update list display
        $str=$owlPDO->query("select * from ".$dbname.".unused_files order  by lastupdate desc");
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
            echo    $markedFiles;     
}else{//file relations
            $path= realpath(dirname(__FILE__));
            $filelist=Array();
            $nameOnly=Array();
            #PHP Files on root
                if ($handle = opendir($path)) {
                     $count=0;
                    while (false !== ($entry = readdir($handle))) {
                        if($entry!='.' && $entry!='..' &&  !is_dir($entry)){
                            $filelist[]=$entry;
                        }
                    }
                    closedir($handle);
                }
             #Js Files   
                $pathJs=$path."/js";
                 if ($handle = opendir($pathJs)) {
                    while (false !== ($entry = readdir($handle))) {
                        if($entry!='.' && $entry!='..'&&  !is_dir($pathJs."/".$entry)){
                            $filelist[]="js/".$entry;         
                        }
                    }
                    closedir($handle);
                }
              #Config Files   
                $pathConf=$path."/config";
                 if ($handle = opendir($pathConf)) {
                    while (false !== ($entry = readdir($handle))) {
                        if($entry!='.' && $entry!='..' &&  !is_dir($pathConf."/".$entry)){
                            $filelist[]="config/".$entry;
                        }
                    }
                    closedir($handle);
                }   
              #lib Files   
                $pathLib=$path."/lib";
                 if ($handle = opendir($pathLib)) {
                    while (false !== ($entry = readdir($handle))) {
                        if($entry!='.' && $entry!='..' &&  !is_dir($pathLib."/".$entry)){
                            $filelist[]="lib/".$entry;   
                        }
                    }
                    closedir($handle);
                }   
              #style Files   
                $pathStyle=$path."/style";
                 if ($handle = opendir($pathStyle)) {
                    while (false !== ($entry = readdir($handle))) {
                        if($entry!='.' && $entry!='..' &&  !is_dir($pathStyle."/".$entry)){
                            $filelist[]="style/".$entry;   
                        }
                    }
                    closedir($handle);
                }  

             ## Find==============================   
            $found=Array();
            $test=false;
            $test=strpos($param['string'], "/");
            if($test!==false){
                $file=explode("/",$param['string']);
                $findString=$file[1];
            }else{
                $findString=$param['string'];
            }
             foreach($filelist as $key =>$nama){
                    $z=explode(".",$findString);
                    if(isset($z[1]) && $z[1]=='php'){
                        $findString=$z[0];
                    }
                      if(is_file($nama)){
                            $isifile=file_get_contents($nama);
                            $isifile=str_replace('dbname.".'.$findString,' ',$isifile);                       
                            $isifile=str_replace('dbname.".`'.$findString,' ',$isifile);
                            $isifile=str_replace("dbname.'.`".$findString," ",$isifile);
                            $isifile=str_replace("dbname.'.".$findString,' ',$isifile);
                            $isifile=str_replace("table_name='".$findString,' ',$isifile);
                            $isifile=str_replace('`.`'.$findString.'`',' ',$isifile);
                            $isifile=str_replace("'.'".$findString."'"," ",$isifile);
                            $isifile=str_replace("`".$findString."`.`"," ",$isifile);
                            $isifile=str_replace("SESSION['lang']['".$findString," ",$isifile);
                            $isifile=str_replace('table '.$findString,' ',$isifile);
                            
                            
                             if( stristr($isifile, $findString) && $nama!==$param['string']){
                                 $found[]=trim($nama);
                             }   
                      }
                }

             #find on menu table================================
               $nameonly=explode(".",$findString);
               if(!isset($nameonly[1]) || $nameonly[1]==='php'){
                         $str=$owlPDO->query("select id,parent,caption  from ".$dbname.".menu where action like '".$nameonly[0]."%'");
                         $str->setFetchMode(PDO::FETCH_OBJ);
                         while($bar=$str->fetch()){
                             $parentCaption='';
                             $str2=$owlPDO->query("select caption from ".$dbname.".menu where id=".$bar->parent);
                             $str2->setFetchMode(PDO::FETCH_OBJ);
                             while($bar2=$str2->fetch()){
                                $parentCaption=$bar2->caption;
                             }
                              $found[]= 'Menu id ('.$bar->id.') '.$parentCaption."->".$bar->caption;//// beware  of changing this line, affected to line 279
                         }    
               }
            echo  "<div  style='height:150px;width:350px;overflow:scroll;background-color:#D4FFAA'>"; 
            echo "File :<b>".$param['fullname']."</b> used in:";
            echo"<table><thead><tr><td>No.</td><td>File name</td><td>Action</td></tr></thead><tbody>";
            #remove if the file has the same name
            foreach($found as $key =>$val){
                if($val===$param['string']){
                    unset($found[$key]);
                }
            }
            $bb=  explode(".", $param['string']);
            if(count($found)<1 && isset($bb[1]) && ($bb[1]=='php' || $bb[1]=='js' || $bb[1]=='css' || $bb[1]=='html' || $bb[1]=='ini')){
                echo "This file is unused <button  class=mybutton style='color:red;'  onclick=\"markDelete('".$param['string']."');\">Mark for deletion</button>";
            }else{
                $count=0;
                foreach($found as $key =>$val){
                        #is file or menu
                    if($val===$param['string']){
                        #ignore
                    }else{
                                $bb=  explode(".", $val);
                                if($bb[(count($bb)-1)]=='php'){
                                    $stq="<img src='images/zoom.png' style='cursor:pointer;height:16px;' title='Browse table usage' onclick=\"browseTable('".$val."');\">";
                                }else{
                                    $stq='';
                                }
                                $pos=false;
                                $pos= strpos($val, 'Menu id');// beware  of changing line 250
                                if ($pos === false){
                                   $stm="<img src='images/application/application_view_list.png' style='cursor:pointer;height:16px;' title='View Source' onclick=\"browseFile('".$val."');\">"; 
                                }else{
                                    $stm='';
                                }
                                 $count++;
                                 $test=false;
                                 $test=strpos($val, ".");
                             if($test!==false){
                                   echo "<tr class=rowcontent><td>".$count."</td><td style='cursor:pointer;' onclick=\"findStringEnd('".$val."','".$val."');\" title='Find relation of ".$val."'>".$val."</td><td>".$stq." ".$stm."</td></tr>";    
                                }else{
                                    echo "<tr class=rowcontent><td>".$count."</td><td>".$val."</td><td>".$stq." ".$stm."</td></tr>";
                               }
                    }
                }
            }
            echo"</tbody><tfoot></tfoot></table>";
            echo "</div>";


            #this file contain:=================================
            $arrayChunk=Array();
            $fileContent=file_get_contents($param['string']);
            $fileContent=  str_replace('"', " ", $fileContent);
            $fileContent=  str_replace("'", " ", $fileContent);
            $fileContent=  str_replace("?", " ", $fileContent);
            $fileContent=  str_replace(">", " ", $fileContent);
            $fileContent=  str_replace("=", " ", $fileContent);
            $fileContent=  str_replace("\\", " ", $fileContent);
            $fileContent=  str_replace("\n", " ", $fileContent);
            $arrayChunk=explode(" ",$fileContent);
            $pos1=$pos2=$pos3=$pos4=$pos5=false;
            if(count($arrayChunk)>0){
                    $count=0;
                    foreach($arrayChunk as $key =>$val){
                            #is file or menu
                        $pos1 = strpos($val, 'slave');
                        $pos2 = strpos($val, '.php');
                        $pos3 = strpos($val, '.js');
                        $pos4 = strpos($val, '.html');
                        $pos5 = strpos($val, '.css');
                        $pos6 = strpos($val, '.ini');
	   $pos7 = strpos($val, 'lbm_');
                        if($pos1!==false ||$pos2!==false ||$pos3!==false ||$pos4!==false ||$pos5!==false||$pos6!==false||$pos7!==false){
                            if(strpos($val, 'lbm_')!==false)
                                { 
                                 $val=$val.'.php';
                                }
                                if(is_file($val)){
                                $filtered[]=trim($val);
                            }
                        }
                    }
            }        
            echo  "<div  style='height:150px;width:350px;overflow:scroll;background-color:#D4FFFF'>"; 
            echo "File :<b>".$param['fullname']."</b> contain the folowing file(s):";
            echo"<table><thead><tr><td>No.</td><td>File name</td><td>Action</td></tr></thead><tbody>";
            if(count($filtered)>0){
                
              $filter1=$filtered;
              unset($filtered);
              $filtered=Array();
              foreach($filter1 as $key=>$val){
                    $test=false;
                    $test=strpos($val, ".");
                    if($test===false){
                        $val.='.php';
                    }
                    $filtered[]=$val;
              }  
              $filtered=array_unique($filtered);
                $count=0;
               foreach($filtered as $key=>$val){ 
                    $count++;
                                $td=  explode('.',$val);
                                if($td[0]==''){}
                                else{
                                $bb=  explode(".", $val);
                                if($bb[(count($bb)-1)]=='php'){
                                    $stq="<img src='images/zoom.png' style='cursor:pointer;height:16px;' title='Browse table usage' onclick=\"browseTable('".$val."');\">";
                                }else{
                                    $stq='';
                                }       
                                $stm="<img src='images/application/application_view_list.png' style='cursor:pointer;height:16px;' title='View Source' onclick=\"browseFile('".$val."');\">";
                                echo "<tr class=rowcontent><td>".$count."</td><td style='cursor:pointer;' onclick=\"findStringEnd('".$val."','".$val."');\" title='Find relation of ".$val."'>".$val."</td>"
                                        . "<td>".$stq." ".$stm."</td></tr>";    
                                }                     
                       }        
            }
                    echo"</tbody><tfoot></tfoot></table>";
                    echo "</div>";
}        

function show_file($namafile)
   {
    $src='';
            @$file = @highlight_file ( $namafile, true );
            if ( !$file ) {
            die ( 'Empty file to highlight' );
            }
            else {
            $file = explode ( '<br />', $file );
            $i = 1;
            $src.="<table cellspacing=1 border=0>";
            foreach ( $file as $line ) {
                if($i%2==0){
                    $color='#FFFFFF';
                }else{
                    $color='#EFEFEF';
                }
                $src.= '<tr style="background-color:'.$color.';"><td style="background-color:#DEDEDE;">' . $i . '. </td> <td>' . $line . '</td></tr>';
                $i++;
                }
            $src.="</table>";    
            }
     return $src;       
   }
?>