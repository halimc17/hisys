<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/nangkoelib.php');

$method=checkPostGet('method','');
$dbname_mobile = 'owlMobile';
switch($method){
	case'savemenu':
		$parent=$_POST['id_parent'];
		$caption=$_POST['caption'];
		$caption2=$_POST['caption2'];
		$caption3=$_POST['caption3'];
		$action=$_POST['action'];
		$class=$_POST['class'];
		$createFile=$_POST['create'];
		if($parent==''){
			$parent=0;
		}
		
		//check menu deep. max 6
		$nex_parent=$parent;
		$deep=0;

		for($x=0;$x<8;$x++){
			$st=$owlPDO->query("select parent from ".$dbname_mobile.".menu where id=".$nex_parent);
			$st->setFetchMode(PDO::FETCH_NUM);
			$numrows=owlBaris($st);
			
			if($numrows>0){
				$deep+=1;
				
				while($ba=$st->fetch()){
					$nex_parent=$ba[0];
				}
			}else{
				break;
			}
		}

		if($deep>6){
			echo " Warning: Menu to deep(max 6 child)";
		}else{
			if($parent==0)
				$type='master';
			else
				$type='list';  
			
			if($class=='devider'){
				$caption='';
				$action='';
			}
			
			if($class=='title'){
				$action='';
			}
			
			$str=$owlPDO->query("select max(urut) from ".$dbname_mobile.".menu where parent=".$parent);
			$str->setFetchMode(PDO::FETCH_NUM);
			while($bar=$str->fetch()){
				$urut=$bar[0];
			}
			
			if(!isset($urut)){
				$urut=0;
			}
			
			if($deep=='0'){
				if($caption==""){
					echo "xxx : Caption must be filled.";
					exit(0);
				}
				if($caption2==""){
					echo "xxx : Panel Name must be filled.";
					exit(0);
				}
			}else if($deep=='1'){
				if($caption==""){
					echo "xxx : Caption must be filled.";
					exit(0);
				}
			}else{
				if($caption==""){
					echo "xxx : Caption must be filled.";
					exit(0);
				}
				if($caption2==""){
					echo "xxx : Panel Name must be filled.";
					exit(0);
				}
				if($caption3==""){
					$caption3=null;
				}
				if($action==""){
					$caption3=null;
				}
			}
			
			$nex_urut=$urut+1;
			$str="insert into ".$dbname_mobile.".menu (
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
				if($parent!=0){
					try{
						$owlPDO->exec("update ".$dbname_mobile.".menu set type='parent' where id=".$parent." and type='list'");
					}catch(PDOException $e){
						print " Gagal!: on insert parent " . $e->getMessage() . "<br/>";
						die();
					}
				}
				
				//create file
				if($createFile=='yes'){
					$filename=$action.".php";
					if (file_exists($filename)){
						//do nothing
					}else{
						//write file
						$defaulContent="<?//@Copy nangkoelframework?>";
						$handle=fopen($filename,'w');
						if(!fwrite($handle,$defaulContent)){
						}else{}
						fclose($handle);
					}
					
				}
				
				//ambil id terakhir
				$str2=$owlPDO->query("select max(id) from ".$dbname_mobile.".menu");
				$str2->setFetchMode(PDO::FETCH_NUM);
				while($bar2=$str2->fetch()){
					$max=$bar2[0];
				}
				
				if($deep>1)
					echo $max.",stop,".$deep;
				else
					echo $max.",available,".$deep;		
			}catch (PDOException $e){
				print " Gagal insert !: " . $e->getMessage() . "<br/>";
				die();
			}
		}
		break;
		
	case'menuforedit':
		$id=$_POST['id'];

		$str=$owlPDO->query("select caption,caption2,caption3, action, class as cl,type from ".$dbname_mobile.".menu where id=".$id);
		$str->setFetchMode(PDO::FETCH_OBJ);	
		$numrows=owlBaris($str);

		if($numrows<1){
			echo " Gagal, Item menu tsb sudah dihapus";
		}else{
			while($bar=$str->fetch()){
				$caption=$bar->caption;
				$caption2=$bar->caption2;
				$caption3=$bar->caption3;
				$action=$bar->action;
				$class=$bar->cl;
				$type=$bar->type;
			}
			
			if($class=='devider'){
				echo " Gagal, Devider tidak dapat di ganti/edit";
			}else{
				if($type=='master'){
					$disabled='';
					$disabled2='';
					$disabled3='disabled';
					$disabled4='disabled';
				}else if($type=='parent'){
					$disabled='';
					$disabled2='disabled';
					$disabled3='disabled';
					$disabled4='disabled';
				}else{
					$disabled='';	
					$disabled2='';	
					$disabled3='';	
					$disabled4='';	
				}
				
				echo"<span style='text-align:center;'>
					<input type=text value='".$caption."'  maxlength=40 class=myinputtext title='Text to be shown on menu' id=editcaption".$id." size=12 onkeypress=\"return tanpa_kutip(event);\"  placeholder='Caption...' ".$disabled.">
					<input type=text value='".$caption2."'  maxlength=40 class=myinputtext title='Text to be shown on menu' id=editcaption2".$id."x size=12 onkeypress=\"return tanpa_kutip(event);\" placeholder='PanelName' ".$disabled2.">
					<input type=text value='".$caption3."'  maxlength=200 class=myinputtext title='Text to be shown on menu' id=editcaption3".$id."x size=12 onkeypress=\"return tanpa_kutip(event);\" placeholder='FunctionName' ".$disabled3.">
					<input type=text value='".$action."'  maxlength=40 class=myinputtext title='Form Name' id=editaction".$id." size=12 onkeypress=\"return tanpa_kutip(event);\" placeholder='FormName' ".$disabled4.">
					<input type=button class=mybutton value=Save onclick=saveEditedMenu('".$id."');>
					<input type=button class=mybutton value=Close onclick=\"clearFormEdit('edit".$id."');\">
				</span>";
			}	
		}
		break;
		
	case'editedmenu':
		$id=$_POST['id'];
		$caption=$_POST['caption'];
		$caption2=$_POST['caption2'];
		$caption3=$_POST['caption3'];
		$action=$_POST['action'];

		try{
			$owlPDO->exec("update ".$dbname_mobile.".menu set action='".$action."',caption='".$caption."',caption2='".$caption2."',caption3='".$caption3."'  where id=".$id);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
		
	case'activemenu':
		$id=$_POST['id'];
		$hideValue=$_POST['setHide'];

		try{
			$owlPDO->exec("update ".$dbname_mobile.".menu set hide=".$hideValue.", f_hide=".$hideValue.", lastuser='".$_SESSION['standard']['username']."' where id=".$id);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		} 
		break;
		
	case'deletemenu':
		$id=$_POST['id'];

		$str=$owlPDO->query("select * from ".$dbname_mobile.".menu where parent=".$id);
		$str->setFetchMode(PDO::FETCH_NUM);
		$numrows=owlBaris($str);

		if($numrows>0){
			echo " Gagal, Hapus dari submenu paling dalam";	
		}else{
			try{
				$owlPDO->exec("delete from ".$dbname_mobile.".menu  where id=".$id);
				// $owlPDO->exec("delete from ".$dbname_mobile.".auth where menuid=".$id);        
			}catch (PDOException $ex){
				print " Gagal  !: " . $ex->getMessage() . "<br/>";
				die();
			}
		}
		break;
	
	case'showeditor':
		$parent=checkPostGet('parent','');
		$sub=checkPostGet('sub','');
		if($sub=='true'){
			$str="select * from ".$dbname_mobile.".menu where parent=".$parent." order by urut";
		}else{
			$str="select * from ".$dbname_mobile.".menu where type='master' order by urut";	
		}

		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($res);        
		if($numrows<1){
			echo " Gagal, Menu ini tidak memiliki submenu";
		}else{
			echo"<br>
				".$_SESSION['lang']['updownmenuitem']."
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
						</thead><tbody>";
						$max=$numrows;
						$no=0;
						while($bar=$res->fetch()){
							$no+=1;
							if($bar->class=='devider')
								$bar->caption='----------';
							
							echo"<tr class=rowcontent>
								<td class=firsttd id=orderid".$no.">".$bar->id."</td>
								<td id=ordertype".$no.">".$bar->class."</td>
								<td id=ordercaption".$no.">".$bar->caption."</td>
								<td id=orderaction".$no.">".$bar->action."</td>
								<td id=orderurut".$no.">".$bar->urut."</td>
								<td>";
								if($max>1){
									if($no!=$max)
										echo"<img class=dellicon src=images/menu/arrow_57.gif title='Move down' onclick=change('down','".$no."','".$max."')>&nbsp &nbsp";
									
									if($no>1)
										echo"<img class=dellicon src=images/menu/arrow_58.gif title='Move up' onclick=change('up','".$no."','".$max."')>";
								}
							echo"</td></tr>";	 
						}
						
					echo"</tbody></table>
					<br>";
					
					if($max>1)
						echo"<input type=button class=mybutton value='".$_SESSION['lang']['done']."' onclick=closeOrderEditor()> ";
					
					echo" <input type=button class=mybutton value='".$_SESSION['lang']['close']."' onclick=closeOrderEditor()>";
		}
		break;
		
	case'change':
		$fromId=$_POST['from'];
		$toId=$_POST['to'];
		$orderFrom=$_POST['orderfrom'];
		$orderTo=$_POST['orderto'];
		$temp=329027;
		
		$str="update ".$dbname_mobile.".menu set urut=".$temp." where id=".$toId;
		$str1="update ".$dbname_mobile.".menu set urut=".$orderTo.", lastuser='".$_SESSION['standard']['username']."' where id=".$fromId;
		$str2="update ".$dbname_mobile.".menu set urut=".$orderFrom.", lastuser='".$_SESSION['standard']['username']."' where id=".$toId;
		
		try{
			$owlPDO->exec($str);
			$owlPDO->exec($str1);
			$owlPDO->exec($str2);
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "<br/>";
            die();
		}
		break;
}
?>