<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/admin_validation.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');

switch($method){
	case'resetDetailPrivillage':
		$uname = checkPostGet('uname','');
		$str="delete from ".$dbname.".authbi where namauser='".$uname."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	case'changePrivillage':
		$menuid = checkPostGet('menuid','');
		$uname = checkPostGet('uname','');
		$action = checkPostGet('action','');
		
		##check if exist
		$status=false;
		
		$str=$owlPDO->query("select * from ".$dbname.".authbi where namauser='".$uname."' and menuid=".$menuid); 
        $str->setFetchMode(PDO::FETCH_OBJ);
        $numrows=owlBaris($str); 
        if($numrows>0)
           $status=true;
        else
           $status=false; 
	   
		if(!$status and $action=='remove'){
			//if not exist
			$str="insert into ".$dbname.".authbi (namauser,menuid,status,lastuser) values('".$uname."',".$menuid.",0,'".$_SESSION['standard']['username']."')";
			$s=5;
		}
		
		if($status and $action=='remove'){
			//if exist and set to deactive
			$str="update ".$dbname.".authbi set status=0, lastuser='".$_SESSION['standard']['username']."' where namauser='".$uname."' and menuid=".$menuid;			
			$s=2;
		}else if(!$status and $action=='add'){
			$str="insert into ".$dbname.".authbi (namauser,menuid,status,lastuser) values('".$uname."',".$menuid.",1,'".$_SESSION['standard']['username']."')";	
			$s=3;
		}else{
			//if exist and set to active
			$str="update ".$dbname.".authbi set status=1, lastuser='".$_SESSION['standard']['username']."' where namauser='".$uname."' and menuid=".$menuid;	
			$s=4;
		}
		
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "<br/>";
            die();
		}
	break;
	
	case'setMapUserMenu':
		$uname = checkPostGet('uname','');
		echo"<div>
		<fieldset style='width:200px;color:#333399;'>Map user<b> '".$uname."'</b> Privileges <img src=images/info.png height=30px style='vertical-align:middle;cursor:pointer;' title='Click for help..!'></fieldset><br>
		
		<input type=radio name=rad1 onclick=expandAllOrder()>Expand All
		<input type=radio name=rad1 onclick=collapsAllOrder() checked>Collaps All
        &nbsp;&nbsp;<a href=# onclick=\"resetDetailPrivillage('".$uname."')\" title='Clear All ".$uname." privileges'>Clear All</a>
		<hr>
		<ul>";

		## get current auth for this user
		$_SESSION['upriv']=array();
		$stu=$owlPDO->query("select * from ".$dbname.".authbi where namauser='".$uname."'  and status=1");
		$stu->setFetchMode(PDO::FETCH_OBJ);
		$z=0;
		while($bau=$stu->fetch())
		{
			$_SESSION['upriv'][$z]=$bau->menuid;
			$z+=1;
		}

		$opt='<option>0</option>';
		for($d=1;$d<25;$d++){
			$opt.="<option>".$d."</option>";
		}
		
		$str=$owlPDO->query("select menubi.* from ".$dbname.".menubi where menubi.type='master' order by urut");	  	  
		$str->setFetchMode(PDO::FETCH_OBJ);
		while($bar=$str->fetch()){
			$cx='';
            for($x=0;$x<count($_SESSION['upriv']);$x++){
				if($_SESSION['upriv'][$x]==$bar->id){
					$cx='checked';					
				}
			}
			
			echo "<li class=mmgr><img title=expand class=arrow  src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$bar->id."',this);>
				<a class=lab id=orderlab".$bar->id.">".$bar->caption."</a>
				<input type=checkbox id='cx".$bar->id."' value='".$bar->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
				
				if($bar->hide==1)
					echo" <font color=#CC0000>(Inactive)</font>";
				else
					echo" <font color=#009900>(Active)</font>";   
				
				//=========================================================
				$str1=$owlPDO->query("select menubi.* from ".$dbname.".menubi  where parent=".$bar->id." order by urut");
				$str1->setFetchMode(PDO::FETCH_OBJ);	
				echo"<ul id=orderchild".$bar->id." style='display:none;')>
				<div id=ordergroup".$bar->id.">";
				while($bar1=$str1->fetch()){
					$cx='';
                    for($x=0;$x<count($_SESSION['upriv']);$x++){
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
					$str2=$owlPDO->query("select menubi.* from ".$dbname.".menubi where parent=".$bar1->id." order by urut");
					$str2->setFetchMode(PDO::FETCH_OBJ);	
					echo"<ul id=orderchild".$bar1->id." style='display:none;')>
					<div id=ordergroup".$bar1->id.">";
					while($bar2=$str2->fetch()){
						$cx='';
						for($x=0;$x<count($_SESSION['upriv']);$x++){
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
						
						//=========================================================
						$str3=$owlPDO->query("select menubi.* from ".$dbname.".menubi where parent=".$bar2->id." order by urut");
						$str3->setFetchMode(PDO::FETCH_OBJ);
						echo"<ul id=orderchild".$bar2->id." style='display:none;'>
						<div id=ordergroup".$bar2->id.">";
						while($bar3=$str3->fetch()){
							$cx='';
							for($x=0;$x<count($_SESSION['upriv']);$x++){
								if($_SESSION['upriv'][$x]==$bar3->id)
									$cx='checked';
							}
							
							if(strtolower($bar3->class)=='devider')
								$bar3->caption="------------";							
							
							if(strtolower($bar3->class)=='title' or strtolower($bar3->class)=='devider'){
								echo "<li class=mmgr><img src='images/menu/arrow_10.gif'> 
								<a class=lab id=orderlab".$bar3->id.">".$bar3->caption."</a>";		
							}else{
								echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar3->id."',this);>
								<a class=lab id=orderlab".$bar3->id.">".$bar3->caption."</a>";	
							}
							echo"<input type=checkbox id='cx".$bar3->id."' value='".$bar3->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
							if($bar3->hide==1)
								echo" <font color=#CC0000>(Inactive)</font>";
							else
								echo" <font color=#009900>(Active)</font>"; 
							
							//=========================================================
							$str4=$owlPDO->query("select menubi.* from ".$dbname.".menubi where parent=".$bar3->id." order by urut");
							$str4->setFetchMode(PDO::FETCH_OBJ);
							echo"<ul id=orderchild".$bar3->id." style='display:none;'>
							<div id=ordergroup".$bar3->id.">";
							while($bar4=$str4->fetch()){
								$cx='';
								for($x=0;$x<count($_SESSION['upriv']);$x++){
									if($_SESSION['upriv'][$x]==$bar4->id)
										$cx='checked';
								}
								if(strtolower($bar4->class)=='devider')
									$bar4->caption="------------";							
								
								if(strtolower($bar4->class)=='title' or strtolower($bar4->class)=='devider'){
									echo "<li class=mmgr><img src='images/menu/arrow_10.gif'>
									<a class=lab id=orderlab".$bar4->id.">".$bar4->caption."</a>";	
								}else{
									echo "<li class=mmgr><img title=Expand class=arrow  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar4->id."',this);>
									<a class=lab id=orderlab".$bar4->id.">".$bar4->caption."</a>";
								}
								echo"<input type=checkbox id='cx".$bar4->id."' value='".$bar4->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
								if($bar4->hide==1)
									echo" <font color=#CC0000>(Inactive)</font>";
								else
									echo" <font color=#009900>(Active)</font>"; 
								
								//=========================================================
								$str5=$owlPDO->query("select menubi.* from ".$dbname.".menubi where parent=".$bar4->id." order by urut");
								$str5->setFetchMode(PDO::FETCH_OBJ);
								echo"<ul id=orderchild".$bar4->id." style='display:none;'>
								<div id=ordergroup".$bar4->id.">";
								while($bar5=$str5->fetch()){
									$cx='';
									for($x=0;$x<count($_SESSION['upriv']);$x++){
										if($_SESSION['upriv'][$x]==$bar5->id)
											$cx='checked';
									}
									if(strtolower($bar5->class)=='devider')
										$bar5->caption="------------";							
									
									if(strtolower($bar5->class)=='title' or strtolower($bar5->class)=='devider'){
										echo "<li class=mmgr><img  src='images/menu/arrow_10.gif'> 
										<a class=lab id=orderlab".$bar5->id.">".$bar5->caption."</a>";		
									}else{
										echo "<li class=mmgr><img class=arrow title='Expand'  src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$bar5->id."',this);>
										<a class=lab id=orderlab".$bar5->id.">".$bar5->caption."</a>";
									}
									echo"<input type=checkbox id='cx".$bar5->id."' value='".$bar5->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";					
									if($bar5->hide==1)
										echo" <font color=#CC0000>(Inactive)</font>";
									else
										echo" <font color=#009900>(Active)</font>"; 
									
									//=========================================================
									$str6=$owlPDO->query("select menubi.*  from ".$dbname.".menubi  where parent=".$bar5->id." order by urut");
									$str6->setFetchMode(PDO::FETCH_OBJ);
									echo"<ul id=orderchild".$bar5->id." style='display:none;'>
									<div id=ordergroup".$bar5->id.">";
									while($bar6=$str6->fetch()){
										$cx='';
										for($x=0;$x<count($_SESSION['upriv']);$x++){
											if($_SESSION['upriv'][$x]==$bar6->id)
												$cx='checked';
										}
										if(strtolower($bar6->class)=='devider')
											$bar6->caption="------------";
										
										echo "<li><a class=lab id=orderlab".$bar6->id.">".$bar6->caption."</a>"; 
										echo "<input type=checkbox id='cx".$bar6->id."' value='".$bar6->id."' onclick=changePrivillage(this.value,'".$uname."',this) title='user:".$uname."'  ".$cx.">";
										if($bar->hide==1)
											echo" <font color=#CC0000>(Inactive)</font>";
										else
											echo" <font color=#009900>(Active)</font>";  
										
										echo " </li>";
									}
									echo"</div></ul></li>";
								}
								echo"</div></ul></li>";
							}
							echo"</div></ul></li>";
						}
						echo"</div></ul></li>";
					}
					echo"</div></ul></li>";
				}
				echo"</div></ul></li>";
		}
		
		echo "</ul></div><br>
			<input type=button value=Done class=mybutton onclick=showById('ctrmenu','ctr')>
			<br><br>";
	break;
}

?>