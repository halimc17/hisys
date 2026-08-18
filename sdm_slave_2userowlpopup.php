<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$theme=$_SESSION['theme'];
if($theme=='skyblue' || $theme==''){
  $gen='generic.css';
}else if($theme=='red'){
  $gen='genericRed.css';  
}else{
  $gen='genericGray.css';  
} 
echo"<link rel=stylesheet type=text/css href=style/".$gen.">";

$username = checkPostGet('username', '');
$uname = checkPostGet('username', '');
$proses = checkPostGet('proses', '');

switch ($proses){
	case'detailrole':
		//get current auth for this user
	   $_SESSION['upriv']=array();
	   $_SESSION['detailrole']=array();
	   $stu=$owlPDO->query("select * from ".$dbname.".admin_rolemenudt where idrole='".$uname."'  and status=1");
	   $stu->setFetchMode(PDO::FETCH_OBJ);
	   $z=0;
	   while($bau=$stu->fetch()){
			$_SESSION['upriv'][$z]=$bau->idmenu;
			$z+=1;
			$_SESSION['detailrole'][$bau->idmenu]['update']=$bau->updateby;
			$_SESSION['detailrole'][$bau->idmenu]['time']=$bau->updatetime;
	   }	

		$opt='<option>0</option>';
		for($d=1;$d<25;$d++){
				$opt.="<option>".$d."</option>";
		}

		$str=$owlPDO->query("select menu.* from ".$dbname.".menu  where menu.type='master' and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$uname."'  and status=1) and hide=0 order by urut");	  	  
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
			$cx='';
			for($x=0;$x<count($_SESSION['upriv']);$x++){
				if($_SESSION['upriv'][$x]==$bar->id){
					$cx='checked disabled';
				}
			}
			echo "<li class=mmgr>
					<img title=expand class=arrow  src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$bar->id."',this);>
						<input type=checkbox name=checkbox[] id='cx".$bar->id."' value='".$bar->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">
						<a class=lab id=orderlab".$bar->id.">".$bar->caption."</a>
							";
			if($bar->hide==1){
				echo" <font color=#CC0000>(Inactive)</font>";
			}else{
				echo" <font color=#009900>(Active)</font>";   
			}
			echo" <font style=font-size:10px;font-style:italic;>".getKary($_SESSION['detailrole'][$bar->id]['update']).", ".$_SESSION['detailrole'][$bar->id]['time']."</font>";   

			$str1=$owlPDO->query("select menu.* from ".$dbname.".menu  where parent=".$bar->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$uname."'  and status=1) and hide=0 order by urut");
			$str1->setFetchMode(PDO::FETCH_OBJ);	
			echo"<ul id=orderchild".$bar->id." style='display:'';')><div id=ordergroup".$bar->id.">";
			while($bar1=$str1->fetch()){
				$cx='';
				for($x=0;$x<count($_SESSION['upriv']);$x++){
					if($_SESSION['upriv'][$x]==$bar1->id)
						$cx='checked disabled';
				}
				if(strtolower($bar1->class)=='devider'){
					$bar1->caption="------------";	
				}
				if(strtolower($bar1->class)=='title' or strtolower($bar1->class)=='devider' or strtolower($bar1->type)=='list'){
					echo "<li class=mmgr>";#<img src='images/menu/arrow_10.gif'>";
					echo "<input type=checkbox name=checkbox[] id='cx".$bar1->id."' value='".$bar1->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
					echo "<a class=lab id=orderlab".$bar1->id.">".$bar1->caption."</a>";		
				}else{
					echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$bar1->id."',this);>";					
					echo "<input type=checkbox name=checkbox[] id='cx".$bar1->id."' value='".$bar1->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
					echo "<a class=lab id=orderlab".$bar1->id.">".$bar1->caption."</a>";
				}
			
				if($bar1->hide==1){
				   echo" <font color=#CC0000>(Inactive)</font>";
				}else{
				   echo" <font color=#009900>(Active)</font>"; 
				}
				echo" <font style=font-size:10px;font-style:italic;>".getKary($_SESSION['detailrole'][$bar->id]['update']).", ".$_SESSION['detailrole'][$bar->id]['time']."</font>";   
				
				$str2=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar1->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$uname."'  and status=1) and hide=0 order by urut");
				$str2->setFetchMode(PDO::FETCH_OBJ);	

				echo"<ul id=orderchild".$bar1->id." style='display:'';')><div id=ordergroup".$bar1->id.">";
				while($bar2=$str2->fetch()){
					$cx='';
					for($x=0;$x<count($_SESSION['upriv']);$x++){
						if($_SESSION['upriv'][$x]==$bar2->id)
							$cx='checked disabled';
					}
					if(strtolower($bar2->class)=='devider'){
					   $bar2->caption="------------";							
					}
					if(strtolower($bar2->class)=='title' or strtolower($bar2->class)=='devider' or strtolower($bar2->type)=='list'){
						echo "<li class=mmgr>";#<img src='images/menu/arrow_10.gif'>";
						echo"<input type=checkbox name=checkbox[] id='cx".$bar2->id."' value='".$bar2->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
						
						echo "<a class=lab id=orderlab".$bar2->id.">".$bar2->caption."</a>";		
					}else{
						echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar2->id."',this);>";						
						echo"<input type=checkbox name=checkbox[] id='cx".$bar2->id."' value='".$bar2->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
						echo "<a class=lab id=orderlab".$bar2->id.">".$bar2->caption."</a>";			
					}
					
					if($bar2->hide==1){				
						echo" <font color=#CC0000>(Inactive)</font>";
					}else{
						echo" <font color=#009900>(Active)</font>"; 
					}
					echo" <font style=font-size:10px;font-style:italic;>".getKary($_SESSION['detailrole'][$bar->id]['update']).", ".$_SESSION['detailrole'][$bar->id]['time']."</font>";   
					
					$str3=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar2->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$uname."'  and status=1) and hide=0 order by urut");
					$str3->setFetchMode(PDO::FETCH_OBJ);	
					echo"<ul id=orderchild".$bar2->id." style='display:'';'><div id=ordergroup".$bar2->id.">";
					while($bar3=$str3->fetch()){
						$cx='';
						for($x=0;$x<count($_SESSION['upriv']);$x++){
							if($_SESSION['upriv'][$x]==$bar3->id)
								$cx='checked disabled';
						}
						
						if(strtolower($bar3->class)=='devider'){					
							$bar3->caption="------------";							
						}
						if(strtolower($bar3->class)=='title' or strtolower($bar3->class)=='devider' or strtolower($bar3->type)=='list'){
							echo "<li class=mmgr>";#<img src='images/menu/arrow_10.gif'>";
							echo"<input type=checkbox name=checkbox[] id='cx".$bar3->id."' value='".$bar3->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
							echo "<a class=lab id=orderlab".$bar3->id.">".$bar3->caption."</a>";		
						}else{
							echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar3->id."',this);>";
							echo"<input type=checkbox name=checkbox[] id='cx".$bar3->id."' value='".$bar3->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
							echo "<a class=lab id=orderlab".$bar3->id.">".$bar3->caption."</a>";	
						}
					
						if($bar3->hide==1){
						   echo" <font color=#CC0000>(Inactive)</font>";
						}else{
						   echo" <font color=#009900>(Active)</font>"; 
						}
						echo" <font style=font-size:10px;font-style:italic;>".getKary($_SESSION['detailrole'][$bar->id]['update']).", ".$_SESSION['detailrole'][$bar->id]['time']."</font>";   
						
						$str4=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar3->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$uname."'  and status=1) and hide=0 order by urut");
						$str4->setFetchMode(PDO::FETCH_OBJ);	
						echo"<ul id=orderchild".$bar3->id." style='display:'';'><div id=ordergroup".$bar3->id.">";
						while($bar4=$str4->fetch()){
							$cx='';
							for($x=0;$x<count($_SESSION['upriv']);$x++){
								if($_SESSION['upriv'][$x]==$bar4->id)
									$cx='checked disabled';
							}
						 
							if(strtolower($bar4->class)=='devider'){						
								$bar4->caption="------------";							
							}
							
							if(strtolower($bar4->class)=='title' or strtolower($bar4->class)=='devider' or strtolower($bar4->type)=='list'){
								echo "<li class=mmgr>";#<img src='images/menu/arrow_10.gif'>";
								echo"<input type=checkbox name=checkbox[] id='cx".$bar4->id."' value='".$bar4->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
								echo"<a class=lab id=orderlab".$bar4->id.">".$bar4->caption."</a>";	
							}else{
								echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar4->id."',this);>";
								echo"<input type=checkbox name=checkbox[] id='cx".$bar4->id."' value='".$bar4->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
								echo"<a class=lab id=orderlab".$bar4->id.">".$bar4->caption."</a>";
							}
							
							if($bar4->hide==1){						
							   echo" <font color=#CC0000>(Inactive)</font>";
							}else{
							   echo" <font color=#009900>(Active)</font>"; 
							}
							echo" <font style=font-size:10px;font-style:italic;>".getKary($_SESSION['detailrole'][$bar->id]['update']).", ".$_SESSION['detailrole'][$bar->id]['time']."</font>";   
							
							$str5=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar4->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$uname."'  and status=1) and hide=0 order by urut");
							$str5->setFetchMode(PDO::FETCH_OBJ);
							echo"<ul id=orderchild".$bar4->id." style='display:'';'><div id=ordergroup".$bar4->id.">";
							while($bar5=$str5->fetch()){
								$cx='';
								for($x=0;$x<count($_SESSION['upriv']);$x++){
									if($_SESSION['upriv'][$x]==$bar5->id)
										$cx='checked disabled';
								}
								if(strtolower($bar5->class)=='devider'){
									$bar5->caption="------------";							
								}
								if(strtolower($bar5->class)=='title' or strtolower($bar5->class)=='devider'  or strtolower($bar5->type)=='list'){
									echo "<li class=mmgr>";#<img  src='images/menu/arrow_10.gif'>";
									echo"<input type=checkbox name=checkbox[] id='cx".$bar5->id."' value='".$bar5->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";					
									echo"<a class=lab id=orderlab".$bar5->id.">".$bar5->caption."</a>";
								}else{
									echo "<li class=mmgr><img class=arrow title='Expand'  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar5->id."',this);>";
									echo"<input type=checkbox name=checkbox[] id='cx".$bar5->id."' value='".$bar5->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";					
									echo"<a class=lab id=orderlab".$bar5->id.">".$bar5->caption."</a>";
								}
							
								if($bar5->hide==1){
								   echo" <font color=#CC0000>(Inactive)</font>";
								}else{
								   echo" <font color=#009900>(Active)</font>"; 
								}
								echo" <font style=font-size:10px;font-style:italic;>".getKary($_SESSION['detailrole'][$bar->id]['update']).", ".$_SESSION['detailrole'][$bar->id]['time']."</font>";   
								
								$str6=$owlPDO->query("select menu.*  from ".$dbname.".menu  where parent=".$bar5->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$uname."'  and status=1) and hide=0 order by urut");
								$str6->setFetchMode(PDO::FETCH_OBJ);	
								echo"<ul id=orderchild".$bar5->id." style='display:'';'><div id=ordergroup".$bar5->id.">";
								while($bar6=$str6->fetch()){
									$cx='';
									for($x=0;$x<count($_SESSION['upriv']);$x++){
										if($_SESSION['upriv'][$x]==$bar6->id)
											$cx='checked disabled';
									}				
									if(strtolower($bar6->class)=='devider'){								
									   $bar6->caption="------------";							
									}
									echo "<li>";
										echo "<input type=checkbox name=checkbox[] id='cx".$bar6->id."' value='".$bar6->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
										echo "<a class=lab id=orderlab".$bar6->id.">".$bar6->caption."</a>"; 
										
										if($bar->hide==1){
										   echo" <font color=#CC0000>(Inactive)</font>";
										}else{
										   echo" <font color=#009900>(Active)</font>";  
										}
										echo" <font style=font-size:10px;font-style:italic;>".getKary($_SESSION['detailrole'][$bar->id]['update']).", ".$_SESSION['detailrole'][$bar->id]['time']."</font>";   
									echo " </li>";
								}
								echo"</div></ul>";

								echo "</li>";
							}
							echo"</div></ul>";
							echo "</li>";
						}
						echo"</div></ul>";
						echo "</li>";
					}
					echo"</div></ul>";
					echo "</li>";
				}
				echo"</div></ul>";
				echo "</li>";
			}
			echo"</div></ul>";
			echo "</li>";
		}
	break;
	case'role':
		//get current auth for this user
	   $_SESSION['upriv']=array();
	   $stu=$owlPDO->query("select * from ".$dbname.".auth where namauser='".$uname."'  and status=1");
	   $stu->setFetchMode(PDO::FETCH_OBJ);
	   $z=0;
	   while($bau=$stu->fetch()){
			$_SESSION['upriv'][$z]=$bau->menuid;
			$z+=1;
	   }	

		$opt='<option>0</option>';
		for($d=1;$d<25;$d++){
				$opt.="<option>".$d."</option>";
		}

		$str=$owlPDO->query("select menu.* from ".$dbname.".menu  where menu.type='master' and id in (select menuid from ".$dbname.".auth where namauser='".$uname."'  and status=1) and hide=0 order by urut");	  	  
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
			$cx='';
			for($x=0;$x<count($_SESSION['upriv']);$x++){
				if($_SESSION['upriv'][$x]==$bar->id){
					$cx='checked disabled';
				}
			}
			echo "<li class=mmgr>
					<img title=expand class=arrow  src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$bar->id."',this);>
						<input type=checkbox name=checkbox[] id='cx".$bar->id."' value='".$bar->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">
						<a class=lab id=orderlab".$bar->id.">".$bar->caption."</a>
							";
			if($bar->hide==1){
				echo" <font color=#CC0000>(Inactive)</font>";
			}else{
				echo" <font color=#009900>(Active)</font>";   
			}

			$str1=$owlPDO->query("select menu.* from ".$dbname.".menu  where parent=".$bar->id." and id in (select menuid from ".$dbname.".auth where namauser='".$uname."'  and status=1) and hide=0 order by urut");
			$str1->setFetchMode(PDO::FETCH_OBJ);	
			echo"<ul id=orderchild".$bar->id." style='display:'';')><div id=ordergroup".$bar->id.">";
			while($bar1=$str1->fetch()){
				$cx='';
				for($x=0;$x<count($_SESSION['upriv']);$x++){
					if($_SESSION['upriv'][$x]==$bar1->id)
						$cx='checked disabled';
				}
				if(strtolower($bar1->class)=='devider'){
					$bar1->caption="------------";	
				}
				if(strtolower($bar1->class)=='title' or strtolower($bar1->class)=='devider' or strtolower($bar1->type)=='list'){
					echo "<li class=mmgr>";#<img src='images/menu/arrow_10.gif'>";
					echo "<input type=checkbox name=checkbox[] id='cx".$bar1->id."' value='".$bar1->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
					echo "<a class=lab id=orderlab".$bar1->id.">".$bar1->caption."</a>";		
				}else{
					echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$bar1->id."',this);>";					
					echo "<input type=checkbox name=checkbox[] id='cx".$bar1->id."' value='".$bar1->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
					echo "<a class=lab id=orderlab".$bar1->id.">".$bar1->caption."</a>";
				}
			
				if($bar1->hide==1){
				   echo" <font color=#CC0000>(Inactive)</font>";
				}else{
				   echo" <font color=#009900>(Active)</font>"; 
				}
				$str2=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar1->id." and id in (select menuid from ".$dbname.".auth where namauser='".$uname."'  and status=1) and hide=0 order by urut");
				$str2->setFetchMode(PDO::FETCH_OBJ);	

				echo"<ul id=orderchild".$bar1->id." style='display:'';')><div id=ordergroup".$bar1->id.">";
				while($bar2=$str2->fetch()){
					$cx='';
					for($x=0;$x<count($_SESSION['upriv']);$x++){
						if($_SESSION['upriv'][$x]==$bar2->id)
							$cx='checked disabled';
					}
					if(strtolower($bar2->class)=='devider'){
					   $bar2->caption="------------";							
					}
					if(strtolower($bar2->class)=='title' or strtolower($bar2->class)=='devider' or strtolower($bar2->type)=='list'){
						echo "<li class=mmgr>";#<img src='images/menu/arrow_10.gif'>";
						echo"<input type=checkbox name=checkbox[] id='cx".$bar2->id."' value='".$bar2->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
						
						echo "<a class=lab id=orderlab".$bar2->id.">".$bar2->caption."</a>";		
					}else{
						echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar2->id."',this);>";						
						echo"<input type=checkbox name=checkbox[] id='cx".$bar2->id."' value='".$bar2->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
						echo "<a class=lab id=orderlab".$bar2->id.">".$bar2->caption."</a>";			
					}
					
					if($bar2->hide==1){				
						echo" <font color=#CC0000>(Inactive)</font>";
					}else{
						echo" <font color=#009900>(Active)</font>"; 
					}

					$str3=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar2->id." and id in (select menuid from ".$dbname.".auth where namauser='".$uname."'  and status=1) and hide=0 order by urut");
					$str3->setFetchMode(PDO::FETCH_OBJ);	
					echo"<ul id=orderchild".$bar2->id." style='display:'';'><div id=ordergroup".$bar2->id.">";
					while($bar3=$str3->fetch()){
						$cx='';
						for($x=0;$x<count($_SESSION['upriv']);$x++){
							if($_SESSION['upriv'][$x]==$bar3->id)
								$cx='checked disabled';
						}
						
						if(strtolower($bar3->class)=='devider'){					
							$bar3->caption="------------";							
						}
						if(strtolower($bar3->class)=='title' or strtolower($bar3->class)=='devider' or strtolower($bar3->type)=='list'){
							echo "<li class=mmgr>";#<img src='images/menu/arrow_10.gif'>";
							echo"<input type=checkbox name=checkbox[] id='cx".$bar3->id."' value='".$bar3->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
							echo "<a class=lab id=orderlab".$bar3->id.">".$bar3->caption."</a>";		
						}else{
							echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar3->id."',this);>";
							echo"<input type=checkbox name=checkbox[] id='cx".$bar3->id."' value='".$bar3->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
							echo "<a class=lab id=orderlab".$bar3->id.">".$bar3->caption."</a>";	
						}
					
						if($bar3->hide==1){
						   echo" <font color=#CC0000>(Inactive)</font>";
						}else{
						   echo" <font color=#009900>(Active)</font>"; 
						}

						$str4=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar3->id." and id in (select menuid from ".$dbname.".auth where namauser='".$uname."'  and status=1) and hide=0 order by urut");
						$str4->setFetchMode(PDO::FETCH_OBJ);	
						echo"<ul id=orderchild".$bar3->id." style='display:'';'><div id=ordergroup".$bar3->id.">";
						while($bar4=$str4->fetch()){
							$cx='';
							for($x=0;$x<count($_SESSION['upriv']);$x++){
								if($_SESSION['upriv'][$x]==$bar4->id)
									$cx='checked disabled';
							}
						 
							if(strtolower($bar4->class)=='devider'){						
								$bar4->caption="------------";							
							}
							
							if(strtolower($bar4->class)=='title' or strtolower($bar4->class)=='devider' or strtolower($bar4->type)=='list'){
								echo "<li class=mmgr>";#<img src='images/menu/arrow_10.gif'>";
								echo"<input type=checkbox name=checkbox[] id='cx".$bar4->id."' value='".$bar4->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
								echo"<a class=lab id=orderlab".$bar4->id.">".$bar4->caption."</a>";	
							}else{
								echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar4->id."',this);>";
								echo"<input type=checkbox name=checkbox[] id='cx".$bar4->id."' value='".$bar4->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
								echo"<a class=lab id=orderlab".$bar4->id.">".$bar4->caption."</a>";
							}
							
							if($bar4->hide==1){						
							   echo" <font color=#CC0000>(Inactive)</font>";
							}else{
							   echo" <font color=#009900>(Active)</font>"; 
							}

							$str5=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar4->id." and id in (select menuid from ".$dbname.".auth where namauser='".$uname."'  and status=1) and hide=0 order by urut");
							$str5->setFetchMode(PDO::FETCH_OBJ);
							echo"<ul id=orderchild".$bar4->id." style='display:'';'><div id=ordergroup".$bar4->id.">";
							while($bar5=$str5->fetch()){
								$cx='';
								for($x=0;$x<count($_SESSION['upriv']);$x++){
									if($_SESSION['upriv'][$x]==$bar5->id)
										$cx='checked disabled';
								}
								if(strtolower($bar5->class)=='devider'){
									$bar5->caption="------------";							
								}
								if(strtolower($bar5->class)=='title' or strtolower($bar5->class)=='devider'  or strtolower($bar5->type)=='list'){
									echo "<li class=mmgr>";#<img  src='images/menu/arrow_10.gif'>";
									echo"<input type=checkbox name=checkbox[] id='cx".$bar5->id."' value='".$bar5->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";					
									echo"<a class=lab id=orderlab".$bar5->id.">".$bar5->caption."</a>";
								}else{
									echo "<li class=mmgr><img class=arrow title='Expand'  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar5->id."',this);>";
									echo"<input type=checkbox name=checkbox[] id='cx".$bar5->id."' value='".$bar5->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";					
									echo"<a class=lab id=orderlab".$bar5->id.">".$bar5->caption."</a>";
								}
							
								if($bar5->hide==1){
								   echo" <font color=#CC0000>(Inactive)</font>";
								}else{
								   echo" <font color=#009900>(Active)</font>"; 
								}

								$str6=$owlPDO->query("select menu.*  from ".$dbname.".menu  where parent=".$bar5->id." and id in (select menuid from ".$dbname.".auth where namauser='".$uname."'  and status=1) and hide=0 order by urut");
								$str6->setFetchMode(PDO::FETCH_OBJ);	
								echo"<ul id=orderchild".$bar5->id." style='display:'';'><div id=ordergroup".$bar5->id.">";
								while($bar6=$str6->fetch()){
									$cx='';
									for($x=0;$x<count($_SESSION['upriv']);$x++){
										if($_SESSION['upriv'][$x]==$bar6->id)
											$cx='checked disabled';
									}				
									if(strtolower($bar6->class)=='devider'){								
									   $bar6->caption="------------";							
									}
									echo "<li>";
										echo "<input type=checkbox name=checkbox[] id='cx".$bar6->id."' value='".$bar6->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
										echo "<a class=lab id=orderlab".$bar6->id.">".$bar6->caption."</a>"; 
										
										if($bar->hide==1){
										   echo" <font color=#CC0000>(Inactive)</font>";
										}else{
										   echo" <font color=#009900>(Active)</font>";  
										}
									echo " </li>";
								}
								echo"</div></ul>";

								echo "</li>";
							}
							echo"</div></ul>";
							echo "</li>";
						}
						echo"</div></ul>";
						echo "</li>";
					}
					echo"</div></ul>";
					echo "</li>";
				}
				echo"</div></ul>";
				echo "</li>";
			}
			echo"</div></ul>";
			echo "</li>";
		}
	break;
	case 'Privilages':
		$tab='';
		$tab.="<table cellpadding=1 cellspacing=1 class=sortable>
			<thead> 
			<tr class=rowheader>
			<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
			<td align=center>" . $_SESSION['lang']['username'] . "</td>
			
			<td align=center>" . $_SESSION['lang']['menu'] . "</td>
	
			</tr>
			</thead>
			";
		$str="select * from ".$dbname.".auth a 
		left join ".$dbname.".menu b on a.menuid=b.id 
		left join ".$dbname.".user c on a.namauser=c.namauser
		where 1=1 and a.namauser='".$username."' and b.action!='null' and b.action!='' and b.type='list' and b.class='click' order by parent asc, urut asc";

		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$no++;
			$menux[$bar['namauser']][$bar['parent']][$bar['menuid']]=$bar['caption'];
			$strx="SELECT f.*
				FROM (
					SELECT @id AS _id, (SELECT @id := parent FROM ".$dbname.".menu WHERE id = _id)
					FROM (SELECT @id := ".$bar['menuid']." ) tmp1
					JOIN ".$dbname.".menu ON @id <> 0
					) tmp2
				JOIN ".$dbname.".menu f ON tmp2._id = f.Id
				order by action,parent";
			$resx=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
			$resx->setFetchMode(PDO::FETCH_ASSOC);
			$menu=array();
			while($barx=$resx->fetch()){
				if ($_SESSION['language']=='EN'){
					$menu[$barx['caption2']]=ucfirst((strtolower($barx['caption2'])));
				} else if ($_SESSION['language']=='MY'){
					$menu[$barx['caption3']]=ucfirst((strtolower($barx['caption3'])));
				} else {
					$menu[$barx['caption']]=ucfirst((strtolower($barx['caption'])));
				}
					
			}
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td align=left>" . $username . "</td>";
			$tab.="<td align=left>".implode(" > ",$menu)."</td>";
			$tab.="</tr>";
		}
		$tab.="</table>";
		
		echo $tab;
	break;
	case 'Log':
		$tab='Daftar menu yang pernah di akses.<br>';
		$tab.="<table cellpadding=5 cellspacing=1 class=sortable>
			<thead> 
			<tr class=rowheader>
			<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
			<td align=center>" . $_SESSION['lang']['waktu'] . "</td>
			<td align=center>" . $_SESSION['lang']['menu'] . "</td>
			</tr>
			</thead>
			";
		$str="select * from ".$dbname.".user_activity where 1=1 and username='".$username."' order by waktu desc limit 50";
		$res = fetchdata($str);
		$no=0;
		foreach($res as $bar){
			$nmfile = explode("/",$bar['file']);
			$namafile = substr($nmfile[2],0,strripos($nmfile[2],"."));
			$strn = "select * from ".$dbname.".menu where 1=1 and action='".$namafile."'";
			$resn = fetchdata($strn);
			if(!empty($resn)){
				$menu=array();
				$strx="SELECT f.*
					FROM (
						SELECT @id AS _id, (SELECT @id := parent FROM ".$dbname.".menu WHERE id = _id)
						FROM (SELECT @id := ".$resn[0]['id']." ) tmp1
						JOIN ".$dbname.".menu ON @id <> 0
						) tmp2
					JOIN ".$dbname.".menu f ON tmp2._id = f.Id
					order by action,parent";
				$resx=fetchdata($strx);
				foreach($resx as $barx){					
					$menu[$barx['caption']]=$barx['caption'];
				}
				
				$no++;
				$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=left>" . $bar['waktu'] . "</td>";
				$tab.="<td align=left>".implode(" - ",$menu)."</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</table>";
		
		echo $tab;
	break;
}
?>