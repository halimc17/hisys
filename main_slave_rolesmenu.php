<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

if(count($_POST)>0){$param=$_POST;}else{$param=$_GET;}
$method= checkPostGet('method','');
$id    = checkPostGet('id','');
$sclok = checkPostGet('sclok','');
$scjbt = checkPostGet('scjbt','');
$sctpk = checkPostGet('sctpk','');
$scact = checkPostGet('scact','');

$nmrole=makeOption($dbname,'admin_rolemenuht','id,name');

switch($method){
	case'gettpk':
		echo getValue($param);
	break;
	case'setformuser':
		$optact=$opttpk=$optjbt=$optlok=$optnama=$optuser="<option value=''>".$_SESSION['lang']['all']."</option>";
		##GET TIPE KARYAWAN
		$str="select id,tipe from ".$dbname.".sdm_5tipekaryawan where aktif='1' order by tipe asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$opttpk.="<option value='".$val['id']."'>".$val['tipe']."</option>";		
		}
		
		##GET JABATAN
		$str="select kodejabatan,namajabatan from ".$dbname.".sdm_5jabatan where aktif='1' order by namajabatan asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optjbt.="<option value='".$val['kodejabatan']."'>".$val['namajabatan']."</option>";		
		}
		
		##GET NAMA
		$str="select distinct karyawanid,namauser from ".$dbname.".user where status='1' order by namauser asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optnama.="<option value='".$val['karyawanid']."'>".getKary($val['karyawanid'])."</option>";		
			$optuser.="<option value='".$val['namauser']."'>".$val['namauser']."</option>";		
		}
		
		##GET LOKASI TUGAS
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)='4' order by namaorganisasi asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optlok.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']."</option>";		
		}
		
		$optact.="<option value='2'>Uncheck</option>";		
		$optact.="<option value='1'>Checked</option>";		
		
		echo"<input type=hidden id=id_menu value='".$param['idrole']."'>";
		//echo"<div class='table-scroll'>
		echo"<div>
			<table class='sortable' cellspacing=1 cellpadding=5 border=0> 
			<thead>
			<tr class='rowheader'>
				 <th align=center>No</td>
				 <th style=width:50px>".$_SESSION['lang']['action']."
				 <br>
					<select class=select2 id='scact' onchange='gettpk()'>".$optact."</select>
				 </th>
				 <th id=usr_nm_>".$_SESSION['lang']['username']."
					<br>
					<select class=select2 id='scus' onchange='gettpk()'>".$optuser."</select>
				 </th>
				 <th>".$_SESSION['lang']['namakaryawan']."
					<br>
					<select class=select2 id='scnm' onchange='gettpk()'>".$optnama."</select>
				 </th>
				 <th>".$_SESSION['lang']['lokasitugas']."
					<br>
					<select class=select2 id='sclok' onchange='gettpk()'>".$optlok."</select>
				 </th>
				 <th>".$_SESSION['lang']['jabatan']."
					<br>
					<select class=select2 id='scjbt' onchange='gettpk()'>".$optjbt."</select>
				</th>
				 <th>".$_SESSION['lang']['tipekaryawan']."
					<br>
					<select class=select2 id='sctpk' onchange='gettpk()'>".$opttpk."</select>
				</th>
				 </tr>
				 </thead><tbody id='containerdt'>";
					echo getValue($param);
		   echo"</tbody></table></div>
		   <br>";
		   
	break;
	case'loaddata':
		$where="";
		if ($param['nama'] != '') {
            $where.=" and name like '%" . $param['nama'] . "%' ";
        }
		
		$tab  = "";
		$limit= 20;
		$page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
				$page = 0;
        }

		$offset    = floatval($page) * floatval($limit);
		$maxdisplay= (floatval($page) * floatval($limit));
		$no        = $maxdisplay;
		
		$str="select * from ".$dbname.".admin_rolemenuht where 1=1 ".$where."";
        $res = fetchdata($str);
        $jlhbrs = count($res);
		
		$arrtipe=array('1'=>'Aktip','0'=>'Non Aktip');
		
		$str="select * from ".$dbname.".admin_rolemenuht where 1=1 ".$where." order by name limit " . $offset . "," . $limit . "";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td>".$bar['name']."</td>";
			$tab.="<td>".$arrtipe[$bar['status']]."</td>";
			$tab.="<td>".getNamaKaryawan($bar['updateby'])."</td>";
			$tab.="<td>".tanggalnormal($bar['updatetime'])."</td>";
			
			$tab.="<td style='text-align:center;width:25px'><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('".$bar['id']."','".$bar['name']."','".$bar['status']."')\"></td>";
			
			$tab.="<td style='text-align:center;width:25px'><img src='images/skyblue/delete.png' class='zImgBtn' title='Edit' onclick=\"deletefield('".$bar['id']."')\"></td>";
			
			$tab.="<td  align=center width=25px><img src=images/application/application_cascade.png class=zImgBtn  title='Copy dari ???' onclick=\"copyfrom('".$bar['id']."')\"></td>";
			
			$tab.="<td  align=center width=25px><img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"setMapUserMenu('event',this,'".$bar['id']."')\"></td>";
			
			$tab.="<td  align=center width=25px><img src=images/orgicon.png class=zImgBtn  title='Assign' onclick=\"setformuser('".$bar['id']."','".$nmrole[$bar['id']]."')\"></td>";
			
			$tab.="</tr>";
		}
		$colspan=10;
		echo $tab."####";
		echo createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
	break;
	case'copyfrom':
		$optuser="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select DISTINCT (namauser) as namauser from ".$dbname.".user where status='1' order by namauser asc";
		$res = fetchData($str);
		foreach($res as $val){
			$optuser.="<option value=".$val['namauser'].">".$val['namauser']."</option>";
		}
		$optrole="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".admin_rolemenuht where status='1' and id!='".$param['idrole']."' order by name asc";
		$res = fetchData($str);
		foreach($res as $val){
			$optrole.="<option value=".$val['id'].">".$val['name']."</option>";
		}
		echo"<div><input style=display:none id=idrolecopy value=".$param['idrole'].">
					Role Name : <b>".$nmrole[$param['idrole']]."</b><hr>
					Info : <br>Untuk menambahkan detail menu dengan cara<br>meng-copy dari detail menu User atau Role lainnya.<br>
					<hr>
					<input type=radio name=copy1 onclick=showcopy('user')>User
					<input type=radio name=copy1 onclick=showcopy('role')>Role
					<hr></div>";
			echo"<table id=tableuser style='display:none;'>
					<tr>
						<td>Copy dari " . $_SESSION['lang']['dari'] . "</td> 
						<td>:</td>				
						<td><select class=select2 id=fromuser onchange=detailmenucopy(this.value,'user'); style=\"width:150px;\">".$optuser."</select></td>
						<td><button class=mybutton onclick=savecopy('user','fromuser'); class=dt-button buttons-print>Simpan</button></td>
					</tr>
				</table>";
			echo"<table id=tablerole style='display:none;'>
					<tr>
						<td>Copy dari " . $_SESSION['lang']['dari'] . "</td> 
						<td>:</td>				
						<td><select class=select2 id=fromrole onchange=detailmenucopy(this.value,'role'); style=\"width:150px;\">".$optrole."</select></td>
						<td><button class=mybutton onclick=savecopy('role','fromrole'); class=dt-button buttons-print>Simpan</button></td>
					</tr>
				</table>";
		echo"<hr><div id=detailmenucopy></div>";	
	break;
	case'detailmenucopy':
		echo"<div><ul>";
		$_SESSION['upriv']=array();
		if($param['jenis']=='role'){			
			$where="and id in (select idmenu from ".$dbname.".admin_rolemenudt where idrole='".$param['sumber']."')";
			$z=0;
			$str="select * from ".$dbname.".admin_rolemenudt where idrole='".$param['sumber']."'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$_SESSION['upriv'][$z]=$bar['idmenu'];
				$z++;
			}	
		}elseif($param['jenis']=='user'){			
			$where="and id in (select menuid from ".$dbname.".auth where namauser='".$param['sumber']."' and status='1')";
			$z=0;
			$str="select * from ".$dbname.".auth where namauser='".$param['sumber']."' and status='1'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$_SESSION['upriv'][$z]=$bar['menuid'];
				$z++;
			}	
		}
		
		$str=$owlPDO->query("select menu.* from ".$dbname.".menu  where menu.type='master' ".$where." order by urut");	  	  
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

			$str1=$owlPDO->query("select menu.* from ".$dbname.".menu  where parent=".$bar->id." ".$where." order by urut");
			$str1->setFetchMode(PDO::FETCH_OBJ);	
			echo"<ul id=orderchild".$bar->id.")><div id=ordergroup".$bar->id.">";
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
				$str2=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar1->id." ".$where." order by urut");
				$str2->setFetchMode(PDO::FETCH_OBJ);	

				echo"<ul id=orderchild".$bar1->id." )><div id=ordergroup".$bar1->id.">";
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

					$str3=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar2->id." ".$where." order by urut");
					$str3->setFetchMode(PDO::FETCH_OBJ);	
					echo"<ul id=orderchild".$bar2->id." ><div id=ordergroup".$bar2->id.">";
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

						$str4=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar3->id." ".$where." order by urut");
						$str4->setFetchMode(PDO::FETCH_OBJ);	
						echo"<ul id=orderchild".$bar3->id." ><div id=ordergroup".$bar3->id.">";
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

							$str5=$owlPDO->query("select menu.* from ".$dbname.".menu where parent=".$bar4->id." ".$where." order by urut");
							$str5->setFetchMode(PDO::FETCH_OBJ);
							echo"<ul id=orderchild".$bar4->id." ><div id=ordergroup".$bar4->id.">";
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

								$str6=$owlPDO->query("select menu.*  from ".$dbname.".menu  where parent=".$bar5->id." ".$where." order by urut");
								$str6->setFetchMode(PDO::FETCH_OBJ);	
								echo"<ul id=orderchild".$bar5->id." ><div id=ordergroup".$bar5->id.">";
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
		echo"</ul></div>";
	
	
	break;
	case'showmenu':
		$uname=$param['idrole'];
		echo"<div>
				Role Name : <b>".$nmrole[$param['idrole']]."</b><hr>
				Info : <br>Jika <b>Induk</b> di checked maka semua <b>Anak</b> akan ter-checked otomatis.<br>
				Jika <b>Anak</b> di checked maka <b>Induk</b> otomatis ter-checked.
				<hr>
				<input type=radio name=rad1 onclick=expandAllOrder()>Expand All
				<input type=radio name=rad1 onclick=collapsAllOrder() checked>Collaps All
				<hr>";
		echo"<ul>";
	
	
		$z=0;
		$_SESSION['upriv']=array();
		$str="select * from ".$dbname.".admin_rolemenudt where idrole='".$param['idrole']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$_SESSION['upriv'][$z]=$bar['idmenu'];
			$z++;
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
		echo"</ul></div><br>
			<input type=button value=Done class=mybutton onclick=showById('ctrmenu')>
		<br><br>";

	break;
	case'insert':
		try {
		$owlPDO->beginTransaction();
		
			$data = array(
				'id'        => $param['idrole'],
				'name'      => $param['nama'],
				'status'    => $param['sts'],
				'createdby' => $_SESSION['standard']['userid'],
				'updateby'  => $_SESSION['standard']['userid'],
				'createtime'=> date("Y-m-d H:i:s")
			);			
			$str = insertQuery($dbname,'admin_rolemenuht',$data,array_keys($data));
			$owlPDO->exec($str); 
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	break;
	case'delete':
		try {
		$owlPDO->beginTransaction();
			$str = "select * from ".$dbname.".auth_role where  idrole = '".$param['id']."'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Sudah di assign ke ".count($res)." user, untuk menghapus silahkan unsign terlebih dahulu.");
			}
			
			$str="delete from ".$dbname.".admin_rolemenuht where  id = '".$param['id']."'";
			$owlPDO->exec($str); 
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	break;
	case'update':
		try {
		$owlPDO->beginTransaction();
		
			$data = array(
				'name'      => $param['nama'],
				'status'    => $param['sts'],
				'updateby'  => $_SESSION['standard']['userid'],
				'updatetime'=> date("Y-m-d H:i:s")
			);			
			$where = "id='".$param['idrole']."'";
			$str = updateQuery($dbname,'admin_rolemenuht',$data,$where);
			$owlPDO->exec($str); 
			
			addroledt($param);
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
		
	break;
	case'savecopy':
		try {
		$owlPDO->beginTransaction();
			if($param['sumber']=='user'){
				$str = "select * from ".$dbname.".auth where  namauser = '".$param['idsumber']."' and status='1'";
				$res = fetchdata($str);
				foreach($res as $bar){
					$param['idmenu']=$bar['menuid'];
					$param['action']='add';
					$param['sumber']='addroledt';
					addroledt($param);
				}
			}
			if($param['sumber']=='role'){
				$str = "select * from ".$dbname.".admin_rolemenudt where  idrole = '".$param['idsumber']."'";
				$res = fetchdata($str);
				foreach($res as $bar){
					$param['idmenu']=$bar['idmenu'];
					$param['action']='add';
					$param['sumber']='addroledt';
					addroledt($param);
				}
			}
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	break;
	case'addroledt':
		try {
		$owlPDO->beginTransaction();
			
			$_SESSION['idmenu']=array();
			$_SESSION['idmenudt']=array();
			$_SESSION['devider'] =array();
			$_SESSION['idchild'] =array();
			$_SESSION['idchild'][$param['idmenu']]=$param['idmenu'];
			$_SESSION['idmenu']=$param['idmenu'];
			for($i=1;$i<=7;$i++){				
				addparent($param);
				addchild($param);
				$dt[$_SESSION['idmenu']]=$_SESSION['idmenu'];
				addroledt($param);
			}
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
		
		
		// echo"<pre>";
		// print_r($dt);
		// echo"</pre>";
		// exit("error");
		
		$return="";$e=count($dt);
		foreach($dt as $mn){
			$n++;
			if($n<$e){				
				$return.=$mn."####";
			}else{
				$return.=$mn;
			}
		}
		$e=$i=0;
		$e=count($_SESSION['devider']);
		foreach($_SESSION['devider'] as $key => $mn){
			$i++;
			if($n!=''){
				$return.="####".$mn;
			}elseif($i<$e){
				$return.=$mn."####";
			}else{
				$return.=$mn;
			}
		}
		$e=$i=0;
		$e=count($_SESSION['idmenudt']);
		foreach($_SESSION['idmenudt'] as $key => $mn){
			$i++;
			if($return!=''){
				$return.="####".$mn;
			}elseif($i<$e){
				$return.=$mn."####";
			}else{
				$return.=$mn;
			}
		}
		
		echo $return;
	break;
	case'setroleuser':
		try {
		$owlPDO->beginTransaction();
			
			$str = "select * from ".$dbname.".admin_rolemenudt where  idrole = '".$param['idrole']."'";
			$res = fetchdata($str);
			if(count($res)==0){
				throw new PDOException("Detail menu belum ada.");
			}
			addauthrole($param);
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
	break;
	case'setauthrolex':
		try {
		$owlPDO->beginTransaction();
			$err=false;
			addauthrole($param);
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); $err=true; echo "Error, " . addslashes($e->getMessage());die();}	
		
		if($err==false){			
			try {
				$owlPDO->beginTransaction();
				recalauth($param);
				$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	
		}
		
		
		
	break;
}
function addchild($param){
	global $owlPDO;
	global $dbname;
	
	$str = "select * from ".$dbname.".admin_rolemenuht where  id = '".$param['idrole']."'";
	$res = fetchdata($str);
	if($res[0]['status']=='0'){
		$status='0';
	}else{
		$status='1';
	}
	
	foreach($_SESSION['idchild'] as $key => $idmenu){		
		$str   = "select * from ".$dbname.".menu where parent = '".$idmenu."'";
		$res   = fetchdata($str);
		if(count($res)>0){
			unset($_SESSION['idchild'][$key]);
			foreach($res as $bar){			
				$jlh = 0;
				$str = "select * from ".$dbname.".admin_rolemenudt where idrole = '".$param['idrole']."' and idmenu = '".$bar['id']."'";
				$res = fetchdata($str);
				$jlh = count($res);
				if($param['action']=='add' and $jlh == '0'){				
					$data = array(
						'idrole'    => $param['idrole'],
						'idmenu'    => $bar['id'],
						'status'    => $status,
						'createdby' => $_SESSION['standard']['userid'],
						'updateby'  => $_SESSION['standard']['userid'],
						'createtime'=> date("Y-m-d H:i:s")
					);			
					$str = insertQuery($dbname,'admin_rolemenudt',$data,array_keys($data));
					$owlPDO->exec($str); 
				}
				if($param['action']=='remove' and $jlh > '0'){				
					$str="delete from ".$dbname.".admin_rolemenudt where  idrole = '".$param['idrole']."' and idmenu = '".$bar['id']."'";
					$owlPDO->exec($str); 
				}
				$_SESSION['idmenudt'][]=$bar['id'];
				$_SESSION['idchild'][$bar['id']]=$bar['id'];
			}
		}
	}
}

function addparent($param){
	global $owlPDO;
	global $dbname;
	
	$str  = "select * from ".$dbname.".menu where  id = '".$_SESSION['idmenu']."'";
	$res  = fetchdata($str);
	$induk= $res[0]['parent'];
	$urut = $res[0]['urut'];
	$clss = $res[0]['class'];
	if($induk>0){
		
		$str = "select * from ".$dbname.".admin_rolemenuht where  id = '".$param['idrole']."'";
		$res = fetchdata($str);
		if($res[0]['status']=='0'){
			$status='0';
		}else{
			$status='1';
		}
		$class="";
		$str = "select * from ".$dbname.".menu where  parent = '".$induk."' and urut < '".$urut."' and class!='click' order by urut desc limit 2";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['class']!=$class and $clss=='click'){
				if($param['action']=='add'){					
					$_SESSION['devider'][]=$bar['id'];
				}
				
				$jlh = 0;
				$str = "select * from ".$dbname.".admin_rolemenudt where  idrole = '".$param['idrole']."' and idmenu = '".$bar['id']."'";
				$res = fetchdata($str);
				$jlh = count($res);
				if($param['action']=='add' and $jlh == '0'){				
					$data = array(
						'idrole'    => $param['idrole'],
						'idmenu'    => $bar['id'],
						'status'    => $status,
						'createdby' => $_SESSION['standard']['userid'],
						'updateby'  => $_SESSION['standard']['userid'],
						'createtime'=> date("Y-m-d H:i:s")
					);			
					$str = insertQuery($dbname,'admin_rolemenudt',$data,array_keys($data));
					$owlPDO->exec($str);
				}
			}
			$class=$bar['class'];
		}
		
		$jlh = 0;
		$str = "select * from ".$dbname.".admin_rolemenudt where  idrole = '".$param['idrole']."' and idmenu = '".$induk."'";
		$res = fetchdata($str);
		$jlh = count($res);
		if($param['action']=='add' and $jlh == '0'){				
			$data = array(
				'idrole'    => $param['idrole'],
				'idmenu'    => $induk,
				'status'    => $status,
				'createdby' => $_SESSION['standard']['userid'],
				'updateby'  => $_SESSION['standard']['userid'],
				'createtime'=> date("Y-m-d H:i:s")
			);			
			$str = insertQuery($dbname,'admin_rolemenudt',$data,array_keys($data));
			$owlPDO->exec($str); 
			
			$_SESSION['idmenu']=$induk;
		}
	}
}

function addroledt($param){
	global $owlPDO;
	global $dbname;
	
	if($param['sumber']=='addroleht'){
		$str = "select * from ".$dbname.".admin_rolemenudt where idrole = '".$param['idrole']."'";
		$res = fetchdata($str);
		if(count($res)>0){			
			if($param['sts']=='1'){
				$query="update ".$dbname.".admin_rolemenudt set status='1' where idrole = '".$param['idrole']."'"; 
				$owlPDO->exec($query); 
			}
			if($param['sts']=='0'){
				$query="update ".$dbname.".admin_rolemenudt set status='0' where idrole = '".$param['idrole']."'"; 
				$owlPDO->exec($query); 
			}
		}
		addauth($param);
	}
	if($param['sumber']=='addroledt'){		
		$str = "select * from ".$dbname.".admin_rolemenuht where  id = '".$param['idrole']."'";
		$res = fetchdata($str);
		if($res[0]['status']=='0'){
			$status='0';
		}else{
			$status='1';
		}
		
		$str = "select * from ".$dbname.".admin_rolemenudt where  idrole = '".$param['idrole']."' and idmenu = '".$param['idmenu']."'";
		$res = fetchdata($str);
		$jlh = count($res);
		
		
		if($param['action']=='add' and $jlh == '0'){				
			$data = array(
				'idrole'    => $param['idrole'],
				'idmenu'    => $param['idmenu'],
				'status'    => $status,
				'createdby' => $_SESSION['standard']['userid'],
				'updateby'  => $_SESSION['standard']['userid'],
				'createtime'=> date("Y-m-d H:i:s")
			);			
			$str = insertQuery($dbname,'admin_rolemenudt',$data,array_keys($data));
			$owlPDO->exec($str); 
		}
		
		addauth($param);
		if($param['action']=='remove' and $jlh > '0'){
			$str="delete from ".$dbname.".admin_rolemenudt where  idrole = '".$param['idrole']."' and idmenu = '".$param['idmenu']."'";
			$owlPDO->exec($str); 
		}
	}
}

function addauthrole($param){
	global $owlPDO;
	global $dbname;
	
	if($param['sumber']=='addroledt'){
		$str = "select * from ".$dbname.".auth_role where idrole = '".$param['idrole']."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$str = "select * from ".$dbname.".admin_rolemenudt where idrole = '".$param['idrole']."'";
			$res = fetchdata($str);
			$status = $res[0]['status'];
			if($status=='1'){
				$query="update ".$dbname.".auth_role set status='1' where idrole = '".$param['idrole']."'"; 
				$owlPDO->exec($query); 
			}
			if($status=='0'){
				$query="update ".$dbname.".auth_role set status='0' where idrole = '".$param['idrole']."'"; 
				$owlPDO->exec($query); 
			}
		}
	}
	if($param['sumber']=='adduser'){
		
		$str = "select * from ".$dbname.".auth_role where  idrole = '".$param['idrole']."' and namauser = '".$param['username']."'";
		$res = fetchdata($str);
		$jlh = count($res);

		if($param['action']=='add' and $jlh == '0'){				
			$data = array(
				'idrole'    => $param['idrole'],
				'namauser'  => $param['username'],
				'status'    => '1',
				'createdby' => $_SESSION['standard']['userid'],
				'updateby'  => $_SESSION['standard']['userid'],
				'createtime'=> date("Y-m-d H:i:s")
			);			
			$str = insertQuery($dbname,'auth_role',$data,array_keys($data));
			$owlPDO->exec($str); 
		}
		addauth($param);
		
		if($param['action']=='remove' and $jlh > '0'){
			$str="delete from ".$dbname.".auth_role where  idrole = '".$param['idrole']."' and namauser = '".$param['username']."'";
			$owlPDO->exec($str); 
		}
		
	}
	
}

function addauth($param){
	global $owlPDO;
	global $dbname;
	
	
	$wh=$whr="";
	if($param['username']!=''){		
		$wh=" and namauser='".$param['username']."'";
	}
	if($param['idmenu']!=''){		
		$whr=" and idmenu='".$param['idmenu']."'";
	}
	
	$menuauth=$menurole=$statauth=array();
	$str = "select * from ".$dbname.".auth_role where idrole = '".$param['idrole']."' ".$wh."";
	$res = fetchdata($str);
	foreach($res as $bar){
		$sql = "select * from ".$dbname.".auth where namauser = '".$bar['namauser']."'";
		$ves = fetchdata($sql);
		foreach($ves as $val){
			$menuauth[$val['namauser']][$val['menuid']]=$val['menuid'];
			$statauth[$val['namauser']][$val['menuid']]=$val['status'];
		}
		
		$t = "select * from ".$dbname.".auth_role where namauser = '".$bar['namauser']."'";
		$e = fetchdata($t);
		foreach($e as $a){			
			$s = "select * from ".$dbname.".admin_rolemenudt where idrole = '".$param['idrole']."' ".$whr."";
			$r = fetchdata($s);
			foreach($r as $b){
				if($param['action']=='remove'){
					$str="delete from ".$dbname.".auth where  menuid = '".$b['idmenu']."' and namauser = '".$bar['namauser']."'";
					$owlPDO->exec($str); 
				}
			}
			
			$s = "select * from ".$dbname.".admin_rolemenudt where idrole = '".$a['idrole']."'";
			$r = fetchdata($s);
			foreach($r as $b){
				$menurole[$bar['namauser']][$b['idmenu']]=$b['idmenu'];
				$statusrole[$bar['namauser']][$b['idmenu']]=$b['status'];
			}
		}
	}
	
	if(count($menurole)>0){		
		foreach($menurole as $userrole => $valrole){
			foreach($valrole as $mnrole){
				if($menuauth[$userrole][$mnrole]=='' and $statusrole[$userrole][$mnrole]=='1'){
					#auth kosong dan status roledt 1 dan bukan remove = add
					$data = array(
						'namauser'=> $userrole,
						'menuid'  => $mnrole,
						'status'  => '1',
						'lastuser'=> $_SESSION['standard']['username']
					);			
					$str = insertQuery($dbname,'auth',$data,array_keys($data));
					$owlPDO->exec($str); 
				}
				
				if($menuauth[$userrole][$mnrole]!='' and $statauth[$userrole][$mnrole]=='0' and $statusrole[$userrole][$mnrole]=='1'){
					$str="update ".$dbname.".auth set status=1,lastuser='".$_SESSION['standard']['username']."' where namauser='".$userrole."' and menuid='".$mnrole."'";	
					$owlPDO->exec($str);
				}
				if($menuauth[$userrole][$mnrole]!='' and $statusrole[$userrole][$mnrole]=='0'){
					#auth ada dan status roledt 0 = delete
					$str="delete from ".$dbname.".auth where  menuid = '".$mnrole."' and namauser = '".$userrole."'";
					$owlPDO->exec($str); 
				}
			}
		}
	}
	
	if(count($menuauth)>0){		
		foreach($menuauth as $userauth => $valauth){
			foreach($valauth as $mnauth){
				if($menurole[$userauth][$mnauth]==''){
					#role kosong = delete
					$str="delete from ".$dbname.".auth where  menuid = '".$mnauth."' and namauser = '".$userauth."'";
					$owlPDO->exec($str); 
				}
			}
		}
	}
}	

function recalauth($param){
	global $owlPDO;
	global $dbname;
	
	//exit("error");

	$wh=$whr="";
	$wh=" and namauser='".$param['username']."'";
	
	if($param['idmenu']!=''){		
		$whr=" and idmenu='".$param['idmenu']."'";
	}
	
	$menuauth=$menurole=array();
	$str = "select * from ".$dbname.".auth_role where 1=1 ".$wh."";
	$res = fetchdata($str);
	foreach($res as $bar){
		$sql = "select * from ".$dbname.".auth where namauser = '".$bar['namauser']."'";
		$ves = fetchdata($sql);
		foreach($ves as $val){
			$menuauth[$val['namauser']][$val['menuid']]=$val['menuid'];
		}
		
		$t = "select * from ".$dbname.".auth_role where namauser = '".$bar['namauser']."'";
		$e = fetchdata($t);
		foreach($e as $a){			
			$s = "select * from ".$dbname.".admin_rolemenudt where idrole = '".$a['idrole']."'";
			$r = fetchdata($s);
			foreach($r as $b){
				$menurole[$bar['namauser']][$b['idmenu']]=$b['idmenu'];
				$statusrole[$bar['namauser']][$b['idmenu']]=$b['status'];
			}
		}
	}
	
	// echo"<pre>";
	// print_r($str);
	// print_r($sql);
	// print_r($t);
	// print_r($menurole);
	// echo"</pre>";
	// exit("error bdasd");
	if(count($menurole)>0){		
		foreach($menurole as $userrole => $valrole){
			foreach($valrole as $mnrole){
				if($menuauth[$userrole][$mnrole]=='' and $statusrole[$userrole][$mnrole]=='1'){
					#auth kosong dan status roledt 1 dan bukan remove = add
					$data = array(
						'namauser'=> $userrole,
						'menuid'  => $mnrole,
						'status'  => '1',
						'lastuser'=> $_SESSION['standard']['username']
					);			
					$str = insertQuery($dbname,'auth',$data,array_keys($data));
					$owlPDO->exec($str); 
				}
				
				if($menuauth[$userrole][$mnrole]!='' and $statusrole[$userrole][$mnrole]=='0'){
					#auth ada dan status roledt 0 = delete
					$str="delete from ".$dbname.".auth where  menuid = '".$mnrole."' and namauser = '".$userrole."'";
					$owlPDO->exec($str);
				}
			}
		}
	}
	
	if(count($menuauth)>0){		
		foreach($menuauth as $userauth => $valauth){
			foreach($valauth as $mnauth){
				if($menurole[$userauth][$mnauth]==''){
					#role kosong = delete
					$str="delete from ".$dbname.".auth where  menuid = '".$mnauth."' and namauser = '".$userauth."'";
					$owlPDO->exec($str); 
				}
			}
		}
	}
}	



function getValue($param){
	global $sclok;
	global $sctpk;
	global $scjbt;
	global $scact;
	global $dbname;
	global $owlPDO;
	
	$tab="";
	$where="";
	$menu=$param['idrole'];
	if($sctpk!=''){
		$where.=" and b.tipekaryawan='".$sctpk."'";
	}
	if($sclok!=''){
		$where.=" and b.lokasitugas='".$sclok."'";
	}
	if($scjbt!=''){
		$where.=" and b.kodejabatan='".$scjbt."'";
	}
	if($param['scus']!=''){
		$where.=" and a.namauser='".$param['scus']."'";
	}
	if($param['scnm']!=''){
		$where.=" and a.karyawanid='".$param['scnm']."'";
	}
	if($scact=='1'){
		$where.=" and c.idrole='".$menu."'";
	}elseif($scact=='2'){
		$where.=" and (c.idrole!='".$menu."' or c.idrole is null)";
	}
	
	$no=0;
	$sData=$owlPDO->query("select distinct a.karyawanid,a.namauser,b.namakaryawan,b.lokasitugas,b.kodejabatan,b.tipekaryawan from ".$dbname.".user a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid left join ".$dbname.".auth_role c on a.namauser=c.namauser where a.status!='0' ".$where." ");      
	$sData->setFetchMode(PDO::FETCH_ASSOC);
	while($rData=$sData->fetch()){
		$no++;
		$sJbtn=$owlPDO->query("select namajabatan from ".$dbname.".sdm_5jabatan where kodejabatan='".$rData['kodejabatan']."'");
		$sJbtn->setFetchMode(PDO::FETCH_ASSOC);
		$rJbtn=$sJbtn->fetch();
		
		$stpk=$owlPDO->query("select tipe from ".$dbname.".sdm_5tipekaryawan where id='".$rData['tipekaryawan']."'");
		$stpk->setFetchMode(PDO::FETCH_ASSOC);
		$rtpk=$stpk->fetch();
		
		$arrd="";
		$sAuth=$owlPDO->query("select distinct * from ".$dbname.".auth_role where namauser='".$rData['namauser']."' and idrole='".$menu."' and status=1");
		$sAuth->setFetchMode(PDO::FETCH_ASSOC);
		$rAuth=owlBaris($sAuth); 
		if($rAuth==1){
			$arrd="checked";
		}
		
		$tab.="<tr class=rowcontent>
		 <td align=center>".$no."</td>
		 <td style='text-align:center'><input type=checkbox id=adddt_".$no." onclick=setroleuser('".$no."','".$menu."','adduser') ".$arrd." /></td>
		 <td id=username_".$no.">".$rData['namauser']."</td>
		 <td>".$rData['namakaryawan']."</td>
		 <td align=center>".$rData['lokasitugas']."</td>
		 <td>".$rJbtn['namajabatan']."</td>
		 <td>".$rtpk['tipe']."</td>
		 </tr>";
		
	}
				
	return $tab;
}

?>