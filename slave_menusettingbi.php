<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/admin_validation.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$parent = checkPostGet('id_parent','');
$caption = checkPostGet('caption','');
$caption2 = checkPostGet('caption2','');
$caption3 = checkPostGet('caption3','');
$action = checkPostGet('action','');
$class = checkPostGet('class','');
$createFile = checkPostGet('create','');

switch($method){
	case'saveMenu':
		if($parent==''){
			$parent=0;			
		}
		
		## check menu deep. max 6
		$nex_parent=$parent;
		$deep=0;
		for($x=0;$x<8;$x++){
			$str="select parent from ".$dbname.".menubi where id='".$nex_parent."'";
			$res=fetchdata($str);
			$numrows=count($res);
			if($numrows>0){
				$deep+=1;
				foreach($res as $val){
					$nex_parent=$res['parent'];
				}
			}else{
				break;
			}
		}
		
		if($deep>6){
			echo " Warning: Menu to deep(max 6 child)";
		}else{
			if($parent==0){
				$type='master';
			}else{
				$type='list';  
			}
			
			if($class=='devider'){
				$caption='';
				$action='';
			}
			
			if($class=='title'){
				$action='';
			}

			$str="select max(urut) as urut from ".$dbname.".menubi where parent='".$parent."'";
			$res=fetchdata($str);
			foreach($res as $val){
				$urut=$val['urut'];
			}
			
			if(!isset($urut)){
				$urut=0;
			}
			
			$nex_urut=$urut+1;
			
			$str="insert into ".$dbname.".menubi(type,class,caption,caption2,caption3,action,parent,urut,hide,lastuser) values ('".$type."','".$class."','".$caption."','".$caption2."','".$caption3."','".$action."','".$parent."','".$nex_urut."','1','".$_SESSION['standard']['username']."')";
			try{
				$owlPDO->exec($str);
                
				##set type as parent where id EQ $parent
                if($parent!=0){
					try{
						$str="update ".$dbname.".menubi set type='parent' where id='".$parent."' and type='list'";
						$owlPDO->exec($str);
                    }catch(PDOException $e){
						print " Gagal!: on insert parent " . $e->getMessage() . "<br/>";
						die();
                    }
                }
				
				##create file
                if($createFile=='yes'){
					$filename=$action.".php";
					if(file_exists($filename)){
						//do nothing
					}else{
						//write file
						$defaulContent="<?//@Copy nangkoelframework?>";
						$handle=fopen($filename,'w');
						if(!fwrite($handle,$defaulContent)){
						}else{
						}
						
						fclose($handle);
					}
				}

                ##ambil id terakhir
				$str="select max(id) as maxid from ".$dbname.".menubi";
				$res=fetchdata($str);
                foreach($res as $val){
					$max=$val['maxid'];
                }
				
				if($deep>5){
					echo $max.",stop";
                }else{
                   echo $max.",available";
				}
			}catch(PDOException $e){
				print " Gagal insert !: " . $e->getMessage() . "<br/>";
				die();
			}
		}
	break;
	
	case'activate':
		$id=checkPostGet('id','');
		$hideValue=checkPostGet('setHide','');
		
		try{
			$str="update ".$dbname.".menubi set hide='".$hideValue."', lastuser='".$_SESSION['standard']['username']."' where id='".$id."'";
			$owlPDO->exec($str);
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}    
	break;
	
	case'delet':
		$id=checkPostGet('id','');
		
		$str="select * from ".$dbname.".menubi where parent='".$id."'";
		$res=fetchdata($str);
		$numrows=count($res);
		if($numrows>0){
			echo " Gagal, Hapus dari submenu paling dalam";	
		}else{
			try{
				$owlPDO->exec("delete from ".$dbname.".menubi  where id='".$id."'");
				$owlPDO->exec("delete from ".$dbname.".authbi where menuid='".$id."'");        
			}catch (PDOException $ex){
				print " Gagal  !: " . $ex->getMessage() . "<br/>";
				die();
			}
		}
	break;
	
	case'edit':
		$id=checkPostGet('id','');
		
		$str="select caption,caption2,caption3, action, class as cl,type from ".$dbname.".menubi where id='".$id."'";
		$res=fetchdata($str);	
        $numrows=count($res);
        if($numrows<1){
			echo " Gagal, Item menu tsb sudah dihapus";
        }else{
			foreach($res as $val){
				$caption=$val['caption'];
				$caption2=$val['caption2'];
				$caption3=$val['caption3'];
				$action=$val['action'];
				$class=$val['cl'];
				$type=$val['type'];
			}
			
			if($class=='devider'){
				echo " Gagal, Devider tidak dapat di ganti/edit";
			}else{
				if($class=='title' or $type=='master'){
					$disabled='disabled';
                }else{
					$disabled='';
				}
				
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
	break;
	
	case'saveEditedMenu':
		$id=checkPostGet('id','');
		$caption=checkPostGet('caption','');
		$caption2=checkPostGet('caption2','');
		$caption3=checkPostGet('caption3','');
		$action=checkPostGet('action','');
		
		try{
			$str="update ".$dbname.".menubi set action='".$action."',caption='".$caption."',caption2='".$caption2."',caption3='".$caption3."'  where id='".$id."'";
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
	break;
	
	case'showEditor':
		$parent=checkPostGet('parent','');
		$sub=checkPostGet('sub','');

		if($sub=='true'){
			$str="select * from ".$dbname.".menubi where parent='".$parent."' order by urut";
		}else{
			$str="select * from ".$dbname.".menubi where type='master' order by urut";
		}
		
		$res=fetchdata($str);
		$numrows=count($res);
		
		if($numrows<1){
			echo " Gagal, Menu ini tidak memiliki submenu";
		}else{
			echo"<br>".$_SESSION['lang']['updownmenuitem']."
			<table width=100% cellspacing=1 border=0 class=data>
				<thead>
				<tr>
					<td>".$_SESSION['lang']['menuid']."</td>
					<td>".$_SESSION['lang']['tipe']."</td>
					<td>".$_SESSION['lang']['caption']."</td>
					<td>".$_SESSION['lang']['action']."</td>
					<td>".$_SESSION['lang']['order']."</td>
					<td>".$_SESSION['lang']['move']."</td>
				</tr>
				</thead>
				<tbody>";
				
			$max=$numrows;
			$no=0;
			foreach($res as $val){
				$no+=1;
				if($bar->class=='devider'){
					$val['caption']='----------';
				}
				
				echo"<tr class=rowcontent>";
				echo"<td class=firsttd id=orderid".$no.">".$val['id']."</td>";
				echo"<td id=ordertype".$no.">".$val['class']."</td>";
				echo"<td id=ordercaption".$no.">".$val['caption']."</td>";
				echo"<td id=orderaction".$no.">".$val['action']."</td>";
				echo"<td id=orderurut".$no.">".$val['urut']."</td>";
				echo"<td>";
				if($max>1){
					if($no!=$max){
						echo"<img class=dellicon src=images/menu/arrow_57.gif title='Move down' onclick=change('down','".$no."','".$max."')>&nbsp &nbsp";
						
					}
					if($no>1){
						echo"<img class=dellicon src=images/menu/arrow_58.gif title='Move up' onclick=change('up','".$no."','".$max."')>";	
					}
				}
				echo"</td>";
				echo"</tr>";
			}
			
			echo"</tbody></table><br>";
			if($max>1){
				echo"<input type=button class=mybutton value='".$_SESSION['lang']['done']."' onclick=closeOrderEditor()> ";
			}
			echo"<input type=button class=mybutton value='".$_SESSION['lang']['close']."' onclick=closeOrderEditor()>";
		}
	break;
	
	case'change':
		$fromId=checkPostGet('from','');
		$toId=checkPostGet('to','');
		$orderFrom=checkPostGet('orderfrom','');
		$orderTo=checkPostGet('parent','');
		$parent=checkPostGet('orderto','');
		
		$temp=329027;
		
		$str="update ".$dbname.".menubi set urut='".$temp."' where id=".$toId;
		$str1="update ".$dbname.".menubi set urut='".$orderTo."', lastuser='".$_SESSION['standard']['username']."' where id='".$fromId."'";
		$str2="update ".$dbname.".menubi set urut='".$orderFrom."', lastuser='".$_SESSION['standard']['username']."' where id='".$toId."'";
		try{
			$owlPDO->exec($str);
			$owlPDO->exec($str1);
			$owlPDO->exec($str2);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
	break;
}

?>