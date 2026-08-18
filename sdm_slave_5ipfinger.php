<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	
	$method = checkPostGet('method','');
	$kdorg = checkPostGet('kdorg','');
	$ip = checkPostGet('ip','');
	$id = checkPostGet('id','');
	$username = checkPostGet('username','');
	$password = checkPostGet('password','');
	$dbnm = checkPostGet('dbnm','');
	$tblnm = checkPostGet('tblnm','');
	$port = checkPostGet('port','');
	
	switch($method){
		case 'loaddata':
			getcontainer();
		break;
		
		case 'insert':
			//Validasi
			// if($kdorg==''||$ip==''||$username==''||$password==''||$dbnm==''||$tblnm==''||$port==''){
			if($kdorg==''||$ip==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			if(!preg_match("/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/",$ip))
			{
				echo"warning : Please Input Valid IP Address";
                exit();
			}
			
			$str="select * from ".$dbname.".sdm_5ipfinger where kodeorg='".$kdorg."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=owlBaris($res);
			if($numrows>=1)
			{
				echo "Error: Setting ip untuk kode organisasi ".$kdorg." sudah pernah terdaftar sebelumnya.";
			}
			else
			{
				$str="insert into ".$dbname.".sdm_5ipfinger(kodeorg,ip,username,password,port,dbname,tblname,updateby,updatetime) 
				values ('".$kdorg."','".$ip."','".$username."','".$password."','".$port."','".$dbnm."','".$tblnm."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
				try
				{
					$owlPDO->exec($str); 
					getcontainer();
				}
				catch (PDOException $e)
				{
					echo "DB Error : ".$e->getMessage();
					die();
				}
			}
		break;
			
		case 'edit':
			//validasi
			// if($kdorg==''||$ip==''||$username==''||$password==''||$dbnm==''||$tblnm==''||$port==''){
			if($kdorg==''||$ip==''){
				echo "Gagal : Semua field harus diisi.";
				exit();
			}
			if(!preg_match("/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/",$ip))
			{
				echo"warning : Please Input Valid IP Address";
                exit();
			}
			$str="update ".$dbname.".sdm_5ipfinger set ip='".$ip."', username='".$username."', password='".$password."', dbname='".$dbnm."', tblname='".$tblnm."', port='".$port."', updateby='".$_SESSION['standard']['userid']."', kodeorg='".$kdorg."' where id='".$id."'";
			try
			{
				$owlPDO->exec($str); 
				getcontainer();
			}
			catch (PDOException $e)
			{
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		case 'delete':
			$str="delete from ".$dbname.".sdm_5ipfinger where id='".$id."'";
			try
			{
				$owlPDO->exec($str); 
			}
			catch (PDOException $e)
			{
				echo "DB Error : ".$e->getMessage();
				die();
			}
		break;
		
		default:
        break;	
	}
	
	function getcontainer(){
		global $owlPDO;
		global $dbname;
		
		$tab = "";
		$tab .= "<table class='sortable' cellspacing='1' cellpadding='5' border='0'>
			<thead>
			<tr class=rowheader>
				<th>".$_SESSION['lang']['nourut']."</th>
				<th>".$_SESSION['lang']['kodeorg']."</th>
				<th>".$_SESSION['lang']['ip']."</th>
				<th>".$_SESSION['lang']['username']."</th>
				<th hidden>".$_SESSION['lang']['password']."</th>
				<th>".$_SESSION['lang']['dbname']."</th>
				<th>".$_SESSION['lang']['nmTabel']."</th>
				<th>".$_SESSION['lang']['port']."</th>
				<th>Last Sync</th>
				<th>".$_SESSION['lang']['status']."</th>
				<th colspan='2' style='text-align:center;'>".$_SESSION['lang']['action']."</th>
			</tr>
			</thead>
			<tbody>";
			
		// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			// $str="select * from ".$dbname.".sdm_5ipfinger order by kodeorg";
		// }else{				
			// $str="select * from ".$dbname.".sdm_5ipfinger where
			// substr(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."') 
			// order by kodeorg";
		// }
		$str="select * from ".$dbname.".sdm_5ipfinger order by kodeorg";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=$res->rowCount();
		$no=0;
		if($numrows <= 0)
		{
			$tab.="<tr class=rowcontent>
				<td colspan=10 style='text-align:center'>".$_SESSION['lang']['datanotfound']."</td>
			</tr>";
		}
		else
		{
			while($bar=$res->fetch())
			{
				$no++;
				$tab.="<tr class=rowcontent>
					<td>".$no."</td>
					<td>".$bar['kodeorg']."</td>
					<td>".$bar['ip']."</td>
					<td>".$bar['username']."</td>
					<td hidden></td>
					<td>".$bar['dbname']."</td>
					<td>".$bar['tblname']."</td>
					<td>".$bar['port']."</td>
					<td>".$bar['lastsync']."</td>
					<td>".$bar['syncstatus']."</td>
					<td>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar['id']."','".$bar['kodeorg']."','".$bar['ip']."','".$bar['username']."','".$bar['password']."','".$bar['dbname']."','".$bar['tblname']."','".$bar['port']."')\">
					</td>
					<td>
						<img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$bar['id']."')\">
					</td>
				</tr>";
			}
		}
		
		$tab.="</tbody><table>";
		
		echo $tab;
	}
?>