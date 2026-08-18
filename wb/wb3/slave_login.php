<?php
session_start();
require_once('config/connection.php');
require_once('lib/detailSession.php');
require_once('lib/zLib.php');

	$str="select * from ".$dbname.".access_type where status='1'";
	$res=fetchdata($str);
	if(count($res) > 0){
		$_SESSION['security']='on';
	}else{
		$_SESSION['security']='off';
	}
	
	$str="select millcode,idwb from ".$dbname.".mssystem limit 1";
	$res=fetchdata($str);
	$_SESSION['millcode']=$res[0]['millcode'];
	$_SESSION['idwb']=$res[0]['idwb'];
	
	//load local ini++++++++++++++++++++++++++++++++
	$ini_array = parse_ini_file("lib/nangkoel.ini");
	$_SESSION['MAXLIFETIME']=$ini_array['MAXLIFETIME'];
	$_SESSION['DIE']=time()+$_SESSION['MAXLIFETIME'];
	$uname = $_POST['uname'];
	$password = $_POST['password'];
	$str = "select * from ".$dbname.".user where uname='".$uname."' and password=MD5('".$password."')";
	$res=fetchdata($str);
	$uid=0;
	$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	
	if(count($res) > 0){
		//Get ID TIMBANGAN
		$filename = "lib/idtimbangan.txt";
		$fp = fopen($filename, "r");
		$content = fread($fp, filesize($filename));
		$lines = explode("\n", $content);
		fclose($fp);
		$_SESSION['standard']['idtimbangan']=$lines[0];
		
		//update login status
		$stra="update ".$dbname.".user set logged=1,lastupdate='".date("Y-m-d H:i:s")."',lastip='".$_SERVER['REMOTE_ADDR']."',lastcomp='".$hostname."' where uname='".$uname."'";
		try{
			$owlPDO->exec($stra); 
			
			//set standard session
			foreach($res as $val){
				$_SESSION['standard']['username']=$val['uname'];
				$_SESSION['standard']['access_level']=$val['access_level'];
				$_SESSION['standard']['lastupdate']=$val['lastupdate'];
				$_SESSION['standard']['userid']=$val['userid'];
				$_SESSION['standard']['status']=$val['status'];
				$_SESSION['standard']['logged']=$val['logged'];
				$_SESSION['standard']['lastip']=$val['lastip'];
				$_SESSION['standard']['lastcomp']=$val['lastcomp'];
			}
			
			if($_SESSION['standard']['status']==0){
				//if user status is inactive
				echo" Gagal, Account Anda Tidak Aktif";
				session_destroy();
				exit;
			}
			
			//set other sessio and  variables
			if(isset($_SESSION['standard']['username'])){
				//get all data from user_empl table
				//setEmplSession($conn,$_SESSION['standard']['userid'],$dbname);

				if($isPrivillaged=getPrivillageType($conn,$dbname)){
					//get access_type, if nothong then kick
				}else{
					if($_SESSION['security']=='on'){
						//if turned on
						echo" Gagal, Maaf, Anda Tidak Mempunyai Privillage\nHubungi Administrator Terkait";
						session_destroy();
						exit;
					}else{}
				}

				$privable=getPrivillages($conn,$_SESSION['standard']['username'],$dbname);
				//get user privillages
				if(!$privable AND $_SESSION['access_type']=='detail'){
					// if nothong then kick
					echo" Gagal, Maaf, Anda Tidak Mempunyai Privillage\nHubungi Administrator Terkait";
					session_destroy();
					exit;
				}else if($_SESSION['standard']['access_level']==0 AND $_SESSION['access_type']!='detail'){
					if($_SESSION['security']=='on'){
						//if security is turned on
						echo" Gagal, Sorry, System uses Levelization Privillages, but you don't have any.\nContact your Administrator";
						session_destroy();
						exit;
					}else{
						//if turned off, grant all privillages
					}
				}
				//setEmployer($conn,$dbname);//get employer detail and active transaction periode
			}
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";die(); 
		}
	}else{
		echo "<font color=#AA3322 style='background-color:#FFFFFF'>Wrong username and/or password</font><br><span   style='background-color:#FFFFFF'>Att: Case Sensitif</span>";
	}
?>