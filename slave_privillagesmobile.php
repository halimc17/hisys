<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
$dbname_mobile = 'owlMobile';
$method=checkPostGet('method','');
switch($method){
	case'mapusermenu':
		$uname=$_POST['uname'];
		echo"<div>
			<fieldset style='width:200px;color:#333399;'>Map user<b> '".$uname."'</b> Privileges <img src=images/info.png height=30px style='vertical-align:middle;cursor:pointer;' title='Click for help..!'></fieldset>
			<br>
			<input type=radio name=rad1 onclick=expandAllOrder()>Expand All
			<input type=radio name=rad1 onclick=collapsAllOrder() checked>Collaps All &nbsp &nbsp <a href=# onclick=\"resetDetailPrivillage('".$uname."')\" title='Clear All ".$uname." privileges'>Clear All</a>
			<hr>
			<ul>";
			
			//get current auth for this user
			$_SESSION['upriv']=array();
			$stu=$owlPDO->query("select * from ".$dbname_mobile.".auth where namauser='".$uname."' and status=1");
			$stu->setFetchMode(PDO::FETCH_OBJ);
			$z=0;
			while($bau=$stu->fetch()){
				$_SESSION['upriv'][$z]=$bau->menuid;
				$z++;
			}
			
			$opt='<option>0</option>';
			for($d=1;$d<25;$d++){
				$opt.="<option>".$d."</option>";
			}
			
			$str=$owlPDO->query("select menu.* from ".$dbname_mobile.".menu where menu.type='master' order by urut");
			$str->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$str->fetch()){
				$cx='';
				for($x=0;$x<@count($_SESSION['upriv']);$x++){
					if($_SESSION['upriv'][$x]==$bar->id)
						$cx='checked';
				}
				
				echo "<li class=mmgr><img title=expand class=arrow  src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$bar->id."',this);>
					<a class=lab id=orderlab".$bar->id.">".$bar->caption."</a>
					<input type=checkbox id='cx".$bar->id."' value='".$bar->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
					
					if($bar->hide==1)
						echo" <font color=#CC0000>(Inactive)</font>";
					else
						echo" <font color=#009900>(Active)</font>";   
		//=========================================================
					$str1=$owlPDO->query("select menu.* from ".$dbname_mobile.".menu  where parent=".$bar->id." order by urut");
					$str1->setFetchMode(PDO::FETCH_OBJ);
					
					echo"<ul id=orderchild".$bar->id." style='display:none;')>
						<div id=ordergroup".$bar->id.">";
						while($bar1=$str1->fetch()){
							$cx='';
							for($x=0;$x<@count($_SESSION['upriv']);$x++){
								if($_SESSION['upriv'][$x]==$bar1->id)
									$cx='checked';
							}
							
							if(strtolower($bar1->class)=='devider'){
								$bar1->caption="------------";	
							}
							
							if(strtolower($bar1->class)=='title' or strtolower($bar1->class)=='devider'){
								echo "<li class=mmgr><img src='images/menu/arrow_10.gif'>
									<a class=lab id=orderlab".$bar1->id.">".$bar1->caption."</a>";		
							}else{
								echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$bar1->id."',this);>
								<a class=lab id=orderlab".$bar1->id.">".$bar1->caption."</a>";
							}
							
							echo "<input type=checkbox id='cx".$bar1->id."' value='".$bar1->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
							
							if($bar1->hide==1)
								echo" <font color=#CC0000>(Inactive)</font>";
							else
								echo" <font color=#009900>(Active)</font>"; 
							//=========================================================
							$str2=$owlPDO->query("select menu.* from ".$dbname_mobile.".menu where parent=".$bar1->id." order by urut");
							$str2->setFetchMode(PDO::FETCH_OBJ);
							
							echo"<ul id=orderchild".$bar1->id." style='display:none;')>
								<div id=ordergroup".$bar1->id.">";
								while($bar2=$str2->fetch()){
									$cx='';
									for($x=0;$x<@count($_SESSION['upriv']);$x++){
										if($_SESSION['upriv'][$x]==$bar2->id)
											$cx='checked';
									}
									
									if(strtolower($bar2->class)=='devider')
										$bar2->caption="------------";							
									
									if(strtolower($bar2->class)=='title' or strtolower($bar2->class)=='devider'){
										echo "<li class=mmgr><img src='images/menu/arrow_10.gif'> 
											<a class=lab id=orderlab".$bar2->id.">".$bar2->caption."</a>";		
									}else{
										echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar2->id."',this);>
											<a class=lab id=orderlab".$bar2->id.">".$bar2->caption."</a>";
									}
									
									echo"<input type=checkbox id='cx".$bar2->id."' value='".$bar2->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
									
									if($bar2->hide==1)
										echo" <font color=#CC0000>(Inactive)</font>";
									else
										echo" <font color=#009900>(Active)</font>"; 
									
									echo "</li>";
								}
							echo"</div></ul>";
							//========================================================
							echo "</li>";
						}
					echo"</div></ul>";
					//========================================================
				echo "</li>";
			}
		echo "</ul></div><br>
		<input type=button value=Done class=mybutton onclick=showById('ctrmenu','ctr')>
		<br><br>";
		break;
		
	case'resetdetailprivillage':
		$uname=trim($_POST['uname']);
		
		$str="delete from ".$dbname_mobile.".auth where namauser='".$uname."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
	
		break;
		
	case'changeprivillage':
		$menuid=trim($_POST['menuid']);
		$uname=trim($_POST['uname']);
		$action=$_POST['action'];
		//check if exist
		$status=false;

		$str=$owlPDO->query("select * from ".$dbname_mobile.".auth where namauser='".$uname."' and menuid=".$menuid); 
		$str->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($str); 

		if($numrows>0)
			$status=true;
		else
			$status=false;

		if(!$status and $action=='remove'){//if not exist
			$str="insert into ".$dbname_mobile.".auth (namauser,menuid,status,lastuser) values('".$uname."',".$menuid.",0,'".$_SESSION['standard']['username']."')";
			$s=5;
		}

		if($status and $action=='remove'){//if exist and set to deactive
			$str="update ".$dbname_mobile.".auth set status=0,lastuser='".$_SESSION['standard']['username']."' where namauser='".$uname."' and menuid=".$menuid;
			$s=2;
		}else if(!$status and $action=='add'){
			$str="insert into ".$dbname_mobile.".auth (namauser,menuid,status,lastuser) values('".$uname."',".$menuid.",1,'".$_SESSION['standard']['username']."')";
			$s=3;
		}else{//if exist and set to active
			$str="update ".$dbname_mobile.".auth set status=1,lastuser='".$_SESSION['standard']['username']."' where namauser='".$uname."' and menuid=".$menuid;
			$s=4;
		}

		// exit("error : ".$str);

		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "<br/>";
			die();
		}
		break;
}

?>
