<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/zLib.php');

if(count($_POST)>0){$param=$_POST;}else{$param=$_GET;}
$uname=$_POST['uname'];



switch($param['sumber']){
	case'role':
		echo"<div>
			<fieldset style='width:200px;color:#333399;'>
			Map user<b> ".$uname."</b> Roles <img src=images/info.png height=30px style='vertical-align:middle;cursor:pointer;' title='Click for help..!'></fieldset><br>";
		
		echo"<input type=radio name=rad1 onclick=expandsemua()>Expand All
			 <input type=radio name=rad1 onclick=collapssemua() checked>Collaps All
			 <hr>
			 ";
		$_SESSION['roles']=array();
		$str="select * from ".$dbname.".auth_role where namauser='".$param['uname']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$_SESSION['roles'][]=$bar['idrole'];
		}
		
		echo "<ul>";
		$str="select * from ".$dbname.".admin_rolemenuht order by id";
		$res=fetchdata($str);
		foreach($res as $bar){
			$idrole=$bar['id'];
			$no++;
			$cx='';
			for($x=0;$x<count($_SESSION['roles']);$x++){
				if($_SESSION['roles'][$x]==$bar['id']){
					$cx='checked';
				}
			}
			echo "<li class=mmgr>
					<img title=expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchildx".$bar['id']."',this);>
						<input type=checkbox id='cx".$bar['id']."' value='".$bar['id']."' onclick=setroleuser(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">
						<a class=lab id=orderlab".$bar['id']."><b>".$bar['name']."</b></a>
						";
			if($bar['status']==0){
				echo" <font color=#CC0000>(Inactive)</font>";
			}else{
				echo" <font color=#009900>(Active)</font>";   
			}
			#=====================================================
			
			echo"<ul id=orderchildx".$bar['id']." name=child[] style=display:none><div id=ordergroup".$bar['id'].">";
			$z=0;
			$_SESSION['upriv']=array();
			$str="select * from ".$dbname.".admin_rolemenudt where idrole='".$bar['id']."'";
			$res = fetchdata($str);
			foreach($res as $val){
				$_SESSION['upriv'][$z]=$val['idmenu'];
				$z++;
			}	
			
			$str=$owlPDO->query("select menu.* from ".$dbname.".menu  where menu.type='master' and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$idrole."') order by urut");	  	  
			$str->setFetchMode(PDO::FETCH_OBJ);
			while($bar=$str->fetch()){
				$cx='';
				for($x=0;$x<count($_SESSION['upriv']);$x++){
					if($_SESSION['upriv'][$x]==$bar->id){
						$cx='checked disabled';
					}
				}
				echo "<li class=mmgr>
						<img title=expand class=arrow  src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$idrole.".".$bar->id."',this);>
							<input type=checkbox name=checkbox[] id='cx".$bar->id."' value='".$bar->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">
							<a class=lab id=orderlab".$bar->id.">".$bar->caption."</a>
								";
				if($bar->hide==1){
					echo" <font color=#CC0000>(Inactive)</font>";
				}else{
					echo" <font color=#009900>(Active)</font>";   
				}

				$str1=$owlPDO->query("select menu.* from ".$dbname.".menu  where parent=".$bar->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$idrole."') order by urut");
				$str1->setFetchMode(PDO::FETCH_OBJ);	
				echo"<ul id=orderchild".$idrole.".".$bar->id." style='display:none;')><div id=ordergroup".$bar->id.">";
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
						echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$idrole.".".$bar1->id."',this);>";					
						echo "<input type=checkbox name=checkbox[] id='cx".$bar1->id."' value='".$bar1->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
						echo "<a class=lab id=orderlab".$bar1->id.">".$bar1->caption."</a>";
					}
				
					if($bar1->hide==1){
					   echo" <font color=#CC0000>(Inactive)</font>";
					}else{
					   echo" <font color=#009900>(Active)</font>"; 
					}
					$str2=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar1->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$idrole."') order by urut");
					$str2->setFetchMode(PDO::FETCH_OBJ);	

					echo"<ul id=orderchild".$idrole.".".$bar1->id." style='display:none;')><div id=ordergroup".$bar1->id.">";
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
							echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$idrole.".".$bar2->id."',this);>";						
							echo"<input type=checkbox name=checkbox[] id='cx".$bar2->id."' value='".$bar2->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
							echo "<a class=lab id=orderlab".$bar2->id.">".$bar2->caption."</a>";			
						}
						
						if($bar2->hide==1){				
							echo" <font color=#CC0000>(Inactive)</font>";
						}else{
							echo" <font color=#009900>(Active)</font>"; 
						}

						$str3=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar2->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$idrole."') order by urut");
						$str3->setFetchMode(PDO::FETCH_OBJ);	
						echo"<ul id=orderchild".$idrole.".".$bar2->id." style='display:none;'><div id=ordergroup".$bar2->id.">";
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
								echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$idrole.".".$bar3->id."',this);>";
								echo"<input type=checkbox name=checkbox[] id='cx".$bar3->id."' value='".$bar3->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
								echo "<a class=lab id=orderlab".$bar3->id.">".$bar3->caption."</a>";	
							}
						
							if($bar3->hide==1){
							   echo" <font color=#CC0000>(Inactive)</font>";
							}else{
							   echo" <font color=#009900>(Active)</font>"; 
							}

							$str4=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar3->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$idrole."') order by urut");
							$str4->setFetchMode(PDO::FETCH_OBJ);	
							echo"<ul id=orderchild".$idrole.".".$bar3->id." style='display:none;'><div id=ordergroup".$bar3->id.">";
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
									echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$idrole.".".$bar4->id."',this);>";
									echo"<input type=checkbox name=checkbox[] id='cx".$bar4->id."' value='".$bar4->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
									echo"<a class=lab id=orderlab".$bar4->id.">".$bar4->caption."</a>";
								}
								
								if($bar4->hide==1){						
								   echo" <font color=#CC0000>(Inactive)</font>";
								}else{
								   echo" <font color=#009900>(Active)</font>"; 
								}

								$str5=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar4->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$idrole."') order by urut");
								$str5->setFetchMode(PDO::FETCH_OBJ);
								echo"<ul id=orderchild".$idrole.".".$bar4->id." style='display:none;'><div id=ordergroup".$bar4->id.">";
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
										echo "<li class=mmgr><img class=arrow title='Expand'  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$idrole.".".$bar5->id."',this);>";
										echo"<input type=checkbox name=checkbox[] id='cx".$bar5->id."' value='".$bar5->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";					
										echo"<a class=lab id=orderlab".$bar5->id.">".$bar5->caption."</a>";
									}
								
									if($bar5->hide==1){
									   echo" <font color=#CC0000>(Inactive)</font>";
									}else{
									   echo" <font color=#009900>(Active)</font>"; 
									}

									$str6=$owlPDO->query("select menu.*  from ".$dbname.".menu  where parent=".$bar5->id." and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$idrole."') order by urut");
									$str6->setFetchMode(PDO::FETCH_OBJ);	
									echo"<ul id=orderchild".$idrole.".".$bar5->id." style='display:none;'><div id=ordergroup".$bar5->id.">";
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
			echo"</div></ul>";
			#=====================================================
			
					
			echo "</li>";
		}
		
		echo "</ul>";
	break;
	default:
		echo"<div>
			<fieldset style='color:#333399;'>Map user<b> ".$uname."</b> Privileges</fieldset>";#<img src=images/info.png height=30px style='vertical-align:middle;cursor:pointer;' title='Click for help..!'></fieldset><br>";
		  
		echo"<hr>Info :<br>Jika <b>Induk</b> di checked maka semua <b>Anak</b> akan ter-checked otomatis.<br>
				Jika <b>Anak</b> di checked maka <b>Induk</b> otomatis ter-checked.
				<hr>";
		echo"<input type=radio name=rad1 onclick=expandAllOrder()>Expand All
			 <input type=radio name=rad1 onclick=collapsAllOrder() checked>Collaps All
			 &nbsp &nbsp <a href=# onclick=\"resetDetailPrivillage('".$uname."')\" title='Clear All ".$uname." privileges'>Clear All</a>
			 <hr>
			 ";
		echo"<ul>";

		//get current auth for this user
		   $_SESSION['upriv']=array();
		   $stu=$owlPDO->query("select * from ".$dbname.".auth where namauser='".$uname."'  and status=1");
		   $stu->setFetchMode(PDO::FETCH_OBJ);
		   $z=0;
		   while($bau=$stu->fetch())
		   {
				$_SESSION['upriv'][$z]=$bau->menuid;
				$z+=1;
		   }	

		$opt='<option>0</option>';
		for($d=1;$d<25;$d++)
		{
				$opt.="<option>".$d."</option>";
		}

		$str=$owlPDO->query("select menu.* from ".$dbname.".menu  where menu.type='master' order by urut");	  	  
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
			$cx='';
			for($x=0;$x<count($_SESSION['upriv']);$x++){
				if($_SESSION['upriv'][$x]==$bar->id){
					$cx='checked';
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

			$str1=$owlPDO->query("select menu.* from ".$dbname.".menu  where parent=".$bar->id." order by urut");
			$str1->setFetchMode(PDO::FETCH_OBJ);	
			echo"<ul id=orderchild".$bar->id." style='display:none;')><div id=ordergroup".$bar->id.">";
			while($bar1=$str1->fetch()){
				$cx='';
				for($x=0;$x<count($_SESSION['upriv']);$x++){
					if($_SESSION['upriv'][$x]==$bar1->id)
						$cx='checked';
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
				$str2=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar1->id." order by urut");
				$str2->setFetchMode(PDO::FETCH_OBJ);	

				echo"<ul id=orderchild".$bar1->id." style='display:none;')><div id=ordergroup".$bar1->id.">";
				while($bar2=$str2->fetch()){
					$cx='';
					for($x=0;$x<count($_SESSION['upriv']);$x++){
						if($_SESSION['upriv'][$x]==$bar2->id)
							$cx='checked';
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

					$str3=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar2->id." order by urut");
					$str3->setFetchMode(PDO::FETCH_OBJ);	
					echo"<ul id=orderchild".$bar2->id." style='display:none;'><div id=ordergroup".$bar2->id.">";
					while($bar3=$str3->fetch()){
						$cx='';
						for($x=0;$x<count($_SESSION['upriv']);$x++){
							if($_SESSION['upriv'][$x]==$bar3->id)
								$cx='checked';
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

						$str4=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar3->id." order by urut");
						$str4->setFetchMode(PDO::FETCH_OBJ);	
						echo"<ul id=orderchild".$bar3->id." style='display:none;'><div id=ordergroup".$bar3->id.">";
						while($bar4=$str4->fetch()){
							$cx='';
							for($x=0;$x<count($_SESSION['upriv']);$x++){
								if($_SESSION['upriv'][$x]==$bar4->id)
									$cx='checked';
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

							$str5=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar4->id." order by urut");
							$str5->setFetchMode(PDO::FETCH_OBJ);
							echo"<ul id=orderchild".$bar4->id." style='display:none;'><div id=ordergroup".$bar4->id.">";
							while($bar5=$str5->fetch()){
								$cx='';
								for($x=0;$x<count($_SESSION['upriv']);$x++){
									if($_SESSION['upriv'][$x]==$bar5->id)
										$cx='checked';
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

								$str6=$owlPDO->query("select menu.*  from ".$dbname.".menu  where parent=".$bar5->id." order by urut");
								$str6->setFetchMode(PDO::FETCH_OBJ);	
								echo"<ul id=orderchild".$bar5->id." style='display:none;'><div id=ordergroup".$bar5->id.">";
								while($bar6=$str6->fetch()){
									$cx='';
									for($x=0;$x<count($_SESSION['upriv']);$x++){
										if($_SESSION['upriv'][$x]==$bar6->id)
											$cx='checked';
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
}	  



echo "</ul></div><br>
<input type=button value=Done class=mybutton onclick=showById('ctrmenu','ctr')>
<br><br>";
?>
