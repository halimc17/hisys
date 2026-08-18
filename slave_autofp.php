<?
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

######################################
############## SERVER 1 HO ###########
######################################

$dbserverfp='10.1.1.63';
$dbportfp  ='3306';
$dbnamefp  ='fin_pro';
$unamefp   ='uploader';
$passwdfp  ='!0987654321';
$error     =false;
$location  ='HO';

$tahun 	     = date('Y');
$bulan 	     = date('M');
$tahunlalu   = $tahun-1;
$startupload = $tahunlalu."-06-01 23:59:59";

try{
	$owlPDOFP = new PDO('mysql:host='.$dbserverfp.';dbname='.$dbnamefp, $unamefp, $passwdfp, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error=true;
	print " Gagal, ".$dbserverfp." could not connect\n";	
	print "Error!: " . $e->getMessage() . "<br/>";
	die();
}

if($error==false){
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP->beginTransaction();
		

		$str="select * from ".$dbnamefp.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP->rollback();
		echo "Error, att_log ".$dbserverfp." " . addslashes($e->getMessage());
		die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP->beginTransaction();

		$str="select * from ".$dbnamefp.".device where flag='0' limit 1";
		$res=$owlPDOFP->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP->rollback();
		echo "Error, device ".$dbserverfp." " . addslashes($e->getMessage());
		die();
	}
	echo "Sukses ",$dbserverfp." => ".$location."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp." => ".$location."<br>";
	$gagal++;
}

######################################
########### SERVER 2 SDKM ############
######################################
/* $dbserverfp2='10.7.1.4';
$dbportfp2  ='3306';
$dbnamefp2  ='fin_pro';
$unamefp2   ='uploader';
$passwdfp2  ='!0987654321';
$error2     = false;
$location2	= 'SDKM';

try{
	$owlPDOFP2 = new PDO('mysql:host='.$dbserverfp2.';dbname='.$dbnamefp2, $unamefp2, $passwdfp2, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP2->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error2=true;
}

if($error2==false){
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP2->beginTransaction();

		$str="select * from ".$dbnamefp2.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP2->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp2.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP2->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP2->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP2->rollback();
		echo "Error, att_log ".$dbserverfp2." " . addslashes($e->getMessage());
		die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP2->beginTransaction();

		$str="select * from ".$dbnamefp2.".device where flag='0' limit 1";
		$res=$owlPDOFP2->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp2.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP2->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP2->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP2->rollback();
		echo "Error, device ".$dbserverfp2." " . addslashes($e->getMessage());
		die();
	}
	echo "Sukses ",$dbserverfp2." => ".$location2."<br>"; 
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp2." => ".$location2."<br>"; 
	$gagal++;
} */


######################################
########### SERVER 2 SDKM ############
######################################
$dbserverfp5='10.7.1.3';
$dbportfp5  ='3306';
$dbnamefp5  ='fin_pro';
$unamefp5   ='uploader';
$passwdfp5  ='!0987654321';
$error5     = false;
$location5	='SDKM2';

try{
	$owlPDOFP5 = new PDO('mysql:host='.$dbserverfp5.';dbname='.$dbnamefp5, $unamefp5, $passwdfp5, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP5->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error5=true;
	// print " Gagal, ".$dbserverfp5." could not connect\n";	
	// print "Error!: " . $e->getMessage() . "<br/>";
	// die();
}

if($error5==false){
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP5->beginTransaction();
		
		$str="select * from ".$dbnamefp5.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP5->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp5.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP5->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP5->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP5->rollback();
		echo "Error, att_log ".$dbserverfp5." " . addslashes($e->getMessage());
		die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP5->beginTransaction();

		$str="select * from ".$dbnamefp5.".device where flag='0' limit 1";
		$res=$owlPDOFP5->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp5.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP5->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP5->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP5->rollback();
		echo "Error, device ".$dbserverfp5." " . addslashes($e->getMessage());
		die();
	}
	echo "Sukses ",$dbserverfp5." => ".$location5."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp5." => ".$location5."<br>";
	$gagal++;
}


######################################
########### SERVER 3 BPJM ############
######################################
//$dbserverfp3='10.7.28.99';
$dbserverfp3='10.7.28.18';
$dbportfp3  ='3306';
$dbnamefp3  ='fin_pro';
$unamefp3   ='uploader';
$passwdfp3  ='!0987654321';
$error3     =false;
$location3	='BPJM';

try{
	$owlPDOFP3 = new PDO('mysql:host='.$dbserverfp3.';dbname='.$dbnamefp3, $unamefp3, $passwdfp3, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP3->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error3 = true;
	// print " Gagal, ".$dbserverfp3." could not connect\n";	
	// print "Error!: " . $e->getMessage() . "<br/>";
	// die();
}
if($error3 == false){	
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP3->beginTransaction();
		
		$str="select * from ".$dbnamefp3.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP3->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp3.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP3->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP3->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP3->rollback();
		echo "Error, att_log ".$dbserverfp3." " . addslashes($e->getMessage());
		die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP3->beginTransaction();

		$str="select * from ".$dbnamefp3.".device where flag='0' limit 1";
		$res=$owlPDOFP3->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp3.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP3->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP3->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP3->rollback();
		echo "Error, device ".$dbserverfp3." " . addslashes($e->getMessage());
		die();
	}
	echo "Sukses ",$dbserverfp3." => ".$location3."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp3." => ".$location3."<br>";
	$gagal++;
}

######################################
########### SERVER 4 KSPM ############
######################################
$dbserverfp4='10.7.12.99';
$dbportfp4  ='3306';
$dbnamefp4  ='fin_pro';
$unamefp4   ='uploader';
$passwdfp4  ='!0987654321';
$error4     = false;
$location4	= 'KSPM';

try{
	$owlPDOFP4 = new PDO('mysql:host='.$dbserverfp4.';dbname='.$dbnamefp4, $unamefp4, $passwdfp4, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP4->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error4 = true;
}
if($error4 == false){	
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP4->beginTransaction();
		
		$str="select * from ".$dbnamefp4.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP4->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp4.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP4->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP4->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP4->rollback();
		echo "Error, att_log ".$dbserverfp4." " . addslashes($e->getMessage());
		die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP4->beginTransaction();

		$str="select * from ".$dbnamefp4.".device where flag='0' limit 1";
		$res=$owlPDOFP4->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp4.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP4->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP4->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP4->rollback();
		echo "Error, device ".$dbserverfp4." " . addslashes($e->getMessage());
		die();
	}
	echo "Sukses ",$dbserverfp4." => ".$location4."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp4." => ".$location4."<br>";
	$gagal++;
}

########################################
########### SERVER HO (FP5) ############
########################################
$dbserverfp5='10.1.1.7\\IDS00005SQL';
$unamefp5   ='LIS';
$dbfp5  	='HR_FINGERPRINT';
$dbnamefp5  ='HR_FINGERPRINT.dbo';
$passwdfp5  ='1th4c4r3s0urce';
$error5     = false;
$location5	= 'HO2';

try{
	$owlPDOFP5 = new PDO("sqlsrv:Server=$dbserverfp5,1433;Database=$dbfp5", "$unamefp5", "$passwdfp5");
    $owlPDOFP5->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e){
	$error5 = true;
}
if($error5 == false){			
		// $tahun=date('Y');
		// $tahunlalu=$tahun-1;
		// $startupload = $tahunlalu."-12-31 23:59:59";

		$tglskrg = date('Y-m-d');
		$tglkmrn = date('Y-m-d', strtotime("-2 day", strtotime(date("Y-m-d"))));
		$str="select * from ".$dbnamefp5.".EVAATT where source_app='ZKBio IVS' and (DATE between '".$tglkmrn."' and '".$tglskrg."') and (CIN!='00:00:00' or COUT!='00:00:00')";
		$arrdata=fetchdatax($str);
		foreach($arrdata as $val){
			try{
				$owlPDORPT->beginTransaction();
				$owlPDOFP5->beginTransaction();
				
				$strx="select count(nik) as countnik from ".$dbnamerpt.".datakaryawan where nik='".$val['ID']."'";
				$resx=fetchdatarpt($strx);
				$jlhnik=($resx[0]['countnik']==''?0:$resx[0]['countnik']);
				
				if($jlhnik > 0){
					if($val['CIN']!='00:00:00'){
						$scandate=$val['DATE']." ".$val['CIN'];
						$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['source_app']."' and scan_date='".$val['scan_date']."' and pin='".$val['ID']."' and scan_date='".$scandate."'";
						$owlPDORPT->exec($strx);
						
						$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['source_app']."','".$scandate."','".$val['ID']."','1','1','0','0','0')";
						$owlPDORPT->exec($strx);
						
						echo getKary($val['ID'])." IN => ".$scandate."<br>";
					}
					
					if($val['COUT']!='00:00:00'){
						$scandate=$val['DATE']." ".$val['COUT'];
						$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['source_app']."' and scan_date='".$val['scan_date']."' and pin='".$val['ID']."' and scan_date='".$scandate."'";
						$owlPDORPT->exec($strx);
						
						$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['source_app']."','".$scandate."','".$val['ID']."','1','2','0','0','0')";
						$owlPDORPT->exec($strx);
						echo getKary($val['ID'])." OUT => ".$scandate."<br>";
					}
					
					$query = "select * from ".$dbnamerpt.".att_pegawai where sn='".$val['source_app']."' and pin='".$val['ID']."'";
					$req = fetchdatarpt($query);
					if(count($req)==0){
						$strx="select karyawanid from ".$dbnamerpt.".datakaryawan where nik='".$val['ID']."'";
						$resx=fetchdatarpt($strx);
						$karyid=$resx[0]['karyawanid'];
						
						$strx="insert into ".$dbnamerpt.".att_pegawai (sn,pin,namafp,nik,karyawan) values ('".$val['source_app']."','".$val['ID']."','".$val['NAME']."','".$val['ID']."','".$karyid."')";
						$owlPDORPT->exec($strx);
					}
				}
				
				$owlPDORPT->commit();
				$owlPDOFP5->commit();
			}catch (PDOException $e){
				$owlPDORPT->rollback();
				$owlPDOFP5->rollback();
				continue;
			}
		}
	echo "Sukses ",$dbserverfp5." => ".$location5."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp5." => ".$location5."<br>";
	$gagal++;
}


########################################
########### SERVER HO (FP) ############
########################################
$dbserverfpksp= '10.1.1.7\\IDS00005SQL';
$unamefpksp   = 'LIS';
$dbfpksp      = 'HR_FINGERPRINT_KSP';
$dbnamefpksp  = 'HR_FINGERPRINT_KSP.dbo';
$passwdfpksp  = '1th4c4r3s0urce';
$errorksp     = false;
$locationksp  = 'HO2';

try{
	$owlPDOFPksp = new PDO("sqlsrv:Server=$dbserverfpksp,1433;Database=$dbfpksp", "$unamefpksp", "$passwdfpksp");
    $owlPDOFPksp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e){
	$errorksp = true;
}
if($errorksp == false){	
	
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFPksp->beginTransaction();
		
		$strx="insert into ".$dbnamefpksp.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id,flag,latitude,longitude,waktuupload,nik,nama) 
		values (:sn,:scan_date,:pin,:verifymode,:inoutmode,:reserved,:work_code,:att_id,:flag,:latitude,:longitude,:waktuupload,:nik,:nama)";
		$stmt = $owlPDOFPksp->prepare($strx);
		
		$str="select * from ".$dbnamerpt.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=fetchdatarpt($str);
		foreach($res as $val){			
			$data = [];
			$data = [
				'sn' => $val['sn'],
				'scan_date' => $val['scan_date'],
				'pin' => $val['pin'],
			];
			$sqldel = "DELETE FROM ".$dbnamefpksp.".att_log WHERE sn = :sn AND scan_date = :scan_date AND pin = :pin";
			$owlPDOFPksp->prepare($sqldel)->execute($data);
			
			
			$query = "select nik,karyawan from ".$dbnamerpt.".att_pegawai where sn='".$val['sn']."' and pin='".$val['pin']."'";
			$req = fetchdatarpt($query);
			$nik = $req[0]['nik'];
			$karyawan = $req[0]['karyawan'];
			
			$stmt->execute([$val['sn'],$val['scan_date'],$val['pin'],$val['verifymode'],$val['inoutmode'],$val['reserved'],$val['work_code'],$val['att_id'],'0',$val['latitude'],$val['longitude'],date('Y-m-d H:i:s'),$nik,getKary($karyawan)]);
			
			if($nik!=''){				
				$strx="update ".$dbnamerpt.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
				$owlPDORPT->exec($strx);
			}else{
				$strx="update ".$dbnamerpt.".att_log set flag='9' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
				$owlPDORPT->exec($strx);
			}
		}
		
		$owlPDORPT->commit();
		$owlPDOFPksp->commit();
	}catch (PDOException $e){
		echo $e->getMessage();
		$owlPDORPT->rollback();
		$owlPDOFPksp->rollback();
	}		
	#==============================================================#
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFPksp->beginTransaction();
		
		$strx="insert into ".$dbnamefpksp.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id,flag,latitude,longitude,waktuupload,nik,nama) 
		values (:sn,:scan_date,:pin,:verifymode,:inoutmode,:reserved,:work_code,:att_id,:flag,:latitude,:longitude,:waktuupload,:nik,:nama)";
		$stmt = $owlPDOFPksp->prepare($strx);
		
		$str="select * from ".$dbnamerpt.".att_log where flag='9' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=fetchdatarpt($str);
		foreach($res as $val){			
			$data = [];
			$data = [
				'sn' => $val['sn'],
				'scan_date' => $val['scan_date'],
				'pin' => $val['pin'],
			];
			$sqldel = "DELETE FROM ".$dbnamefpksp.".att_log WHERE sn = :sn AND scan_date = :scan_date AND pin = :pin";
			$owlPDOFPksp->prepare($sqldel)->execute($data);
			
			
			$query = "select nik,karyawan from ".$dbnamerpt.".att_pegawai where sn='".$val['sn']."' and pin='".$val['pin']."'";
			$req = fetchdatarpt($query);
			$nik = $req[0]['nik'];
			$karyawan = $req[0]['karyawan'];
			
			$stmt->execute([$val['sn'],$val['scan_date'],$val['pin'],$val['verifymode'],$val['inoutmode'],$val['reserved'],$val['work_code'],$val['att_id'],'0',$val['latitude'],$val['longitude'],date('Y-m-d H:i:s'),$nik,getKary($karyawan)]);
			
			if($nik!=''){				
				$strx="update ".$dbnamerpt.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
				$owlPDORPT->exec($strx);
			}else{
				$strx="update ".$dbnamerpt.".att_log set flag='9' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
				$owlPDORPT->exec($strx);
			}
		}
		
		
		$owlPDORPT->commit();
		$owlPDOFPksp->commit();
	}catch (PDOException $e){
		echo $e->getMessage();
		$owlPDORPT->rollback();
		$owlPDOFPksp->rollback();
	}	
	
	
	echo "Sukses ",$dbserverfpksp." => ".$locationksp."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfpksp." => ".$locationksp."<br>";
	$gagal++;
}


######################################
########### SERVER 6 RO ##############
######################################
$dbserverfp6='10.6.1.9';
$dbportfp6  ='3306';
$dbnamefp6  ='fin_pro';
$unamefp6   ='root';
$passwdfp6  ='root';
$error6     = false;
$location6	= 'SDRO';

try{
	$owlPDOFP6 = new PDO('mysql:host='.$dbserverfp6.';dbname='.$dbnamefp6, $unamefp6, $passwdfp6, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP6->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error6 = true;
}
if($error6 == false){	
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP6->beginTransaction();
		
		$str="select * from ".$dbnamefp6.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP6->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp6.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP6->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP6->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP6->rollback();
		echo "Error, att_log ".$dbserverfp6." " . addslashes($e->getMessage());
		die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP6->beginTransaction();

		$str="select * from ".$dbnamefp6.".device where flag='0' limit 1";
		$res=$owlPDOFP6->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp6.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP6->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP6->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP6->rollback();
		echo "Error, device ".$dbserverfp6." " . addslashes($e->getMessage());
		die();
	}
	echo "Sukses ",$dbserverfp6." => ".$location6."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp6." => ".$location6."<br>";
	$gagal++;
}


######################################
########### SERVER 7 GAS #############
######################################
$dbserverfp7='10.8.0.21';
$dbportfp7  ='3306';
$dbnamefp7  ='fin_pro';
$unamefp7   ='uploader';
$passwdfp7  ='!0987654321';
$error7     = false;
$location7	= 'GAS';

try{
	$owlPDOFP7 = new PDO('mysql:host='.$dbserverfp7.';dbname='.$dbnamefp7, $unamefp7, $passwdfp7, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP7->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error7 = true;
}
if($error7 == false){	
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP7->beginTransaction();
		
		$str="select * from ".$dbnamefp7.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP7->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp7.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP7->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP7->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP7->rollback();
		echo "Error, att_log ".$dbserverfp7." " . addslashes($e->getMessage());
		die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP7->beginTransaction();

		$str="select * from ".$dbnamefp7.".device where flag='0' limit 1";
		$res=$owlPDOFP7->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp7.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP7->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP7->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP7->rollback();
		echo "Error, device ".$dbserverfp7." " . addslashes($e->getMessage());
		die();
	}
	echo "Sukses ",$dbserverfp7." => ".$location7."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp7." => ".$location7."<br>";
	$gagal++;
}


######################################
########### SERVER 8 SNP #############
######################################
$dbserverfp8='10.7.34.21';
$dbportfp8  ='3306';
$dbnamefp8  ='fin_pro';
$unamefp8   ='uploader';
$passwdfp8  ='!0987654321';
$error8     = false;
$location8  = 'SNPE';
try{
	$owlPDOFP8 = new PDO('mysql:host='.$dbserverfp8.';dbname='.$dbnamefp8, $unamefp8, $passwdfp8, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP8->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error8 = true;
}
if($error8 == false){	
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP8->beginTransaction();
		
		$str="select * from ".$dbnamefp8.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP8->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp8.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP8->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP8->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP8->rollback();
		echo "Error, att_log ".$dbserverfp8." " . addslashes($e->getMessage());
		// die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP8->beginTransaction();

		$str="select * from ".$dbnamefp8.".device where flag='0' limit 1";
		$res=$owlPDOFP8->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp8.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP8->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP8->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP8->rollback();
		echo "Error, device ".$dbserverfp8." " . addslashes($e->getMessage());
		// die();
	}
	echo "Sukses ",$dbserverfp8." => ".$location8."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp8." => ".$location8."<br>";
	$gagal++;
}



######################################
########### SERVER 9 GAS #############
######################################
$dbserverfp9='10.8.1.6';
$dbportfp9  ='3306';
$dbnamefp9  ='fin_pro';
$unamefp9   ='uploader';
$passwdfp9  ='!0987654321';
$error9     = false;
$location9  = "GAS2";

try{
	$owlPDOFP9 = new PDO('mysql:host='.$dbserverfp9.';dbname='.$dbnamefp9, $unamefp9, $passwdfp9, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP9->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error9 = true;
}
if($error9 == false){	
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP9->beginTransaction();
		
		$str="select * from ".$dbnamefp9.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP9->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp9.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP9->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP9->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP9->rollback();
		echo "Error, att_log ".$dbserverfp9." " . addslashes($e->getMessage());
		die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP9->beginTransaction();

		$str="select * from ".$dbnamefp9.".device where flag='0' limit 1";
		$res=$owlPDOFP9->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp9.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP9->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP9->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP9->rollback();
		echo "Error, device ".$dbserverfp9." " . addslashes($e->getMessage());
		die();
	}
	echo "Sukses ",$dbserverfp9." => ".$location9."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp9." => ".$location9."<br>";
	$gagal++;
}

######################################
########### SERVER 10 SD4E #############
######################################
$dbserverfp10='10.7.8.22';
$dbportfp10  ='3306';
$dbnamefp10  ='fin_pro';
$unamefp10   ='root';
$passwdfp10  ='!0987654321';
$error10     = false;
$location10  = "SD4E";

try{
	$owlPDOFP10 = new PDO('mysql:host='.$dbserverfp10.';dbname='.$dbnamefp10, $unamefp10, $passwdfp10, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP10->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error10 = true;
}
if($error10 == false){	
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP10->beginTransaction();
		
		$str="select * from ".$dbnamefp10.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP10->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp10.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP10->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP10->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP10->rollback();
		echo "Error, att_log ".$dbserverfp10." " . addslashes($e->getMessage());
		die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP10->beginTransaction();

		$str="select * from ".$dbnamefp10.".device where flag='0' limit 1";
		$res=$owlPDOFP10->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp10.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP10->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP10->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP10->rollback();
		echo "Error, device ".$dbserverfp10." " . addslashes($e->getMessage());
		die();
	}
	echo "Sukses ",$dbserverfp10." => ".$location10."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp10." => ".$location10."<br>";
	$gagal++;
}


######################################
########### SERVER 11 KSPE #############
######################################
$dbserverfp11='10.7.12.21';
$dbportfp11  ='3306';
$dbnamefp11  ='fin_pro';
$unamefp11   ='root';
$passwdfp11  ='!0987654321';
$error11     = false;
$location11  = "KSPE";

try{
	$owlPDOFP11 = new PDO('mysql:host='.$dbserverfp11.';dbname='.$dbnamefp11, $unamefp11, $passwdfp11, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP11->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error11 = true;
}
if($error11 == false){	
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP11->beginTransaction();
		
		$str="select * from ".$dbnamefp11.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP11->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp11.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP11->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP11->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP11->rollback();
		echo "Error, att_log ".$dbserverfp11." " . addslashes($e->getMessage());
		die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP11->beginTransaction();

		$str="select * from ".$dbnamefp11.".device where flag='0' limit 1";
		$res=$owlPDOFP11->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp11.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP11->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP11->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP11->rollback();
		echo "Error, device ".$dbserverfp11." " . addslashes($e->getMessage());
		die();
	}
	echo "Sukses ",$dbserverfp11." => ".$location11."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp11." => ".$location11."<br>";
	$gagal++;
}


######################################
########### SERVER 12 AA1E #############
######################################
$dbserverfp12= '10.7.41.21';
$dbportfp12  = '3306';
$dbnamefp12  = 'fin_pro';
$unamefp12   = 'uploader';
$passwdfp12  = '!0987654321';
$error12     = false;
$location12  = "AA1E";

try{
	$owlPDOFP12 = new PDO('mysql:host='.$dbserverfp12.';dbname='.$dbnamefp12, $unamefp12, $passwdfp12, array(PDO::ATTR_PERSISTENT => false));
	$owlPDOFP12->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	$error12 = true;
}
if($error12 == false){	
	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP12->beginTransaction();
		
		$str="select * from ".$dbnamefp12.".att_log where flag='0' and scan_date >= '".$startupload."' order by scan_date desc limit 100";
		$res=$owlPDOFP12->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_log where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_log (sn,scan_date,pin,verifymode,inoutmode,reserved,work_code,att_id) values ('".$val['sn']."','".$val['scan_date']."','".$val['pin']."','".$val['verifymode']."','".$val['inoutmode']."','".$val['reserved']."','".$val['work_code']."','".$val['att_id']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp12.".att_log set flag='1' where sn='".$val['sn']."' and scan_date='".$val['scan_date']."' and pin='".$val['pin']."'";
			$owlPDOFP12->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP12->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP12->rollback();
		echo "Error, att_log ".$dbserverfp12." " . addslashes($e->getMessage());
		die();
	}

	try{
		$owlPDORPT->beginTransaction();
		$owlPDOFP12->beginTransaction();

		$str="select * from ".$dbnamefp12.".device where flag='0' limit 1";
		$res=$owlPDOFP12->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($val=$res->fetch()){
			$strx="delete from ".$dbnamerpt.".att_device where sn='".$val['sn']."'";
			$owlPDORPT->exec($strx);
			
			$strx="insert into ".$dbnamerpt.".att_device (sn,device_name) values ('".$val['sn']."','".$val['device_name']."')";
			$owlPDORPT->exec($strx);
			
			$strx="update ".$dbnamefp12.".device set flag='1' where sn='".$val['sn']."'";
			$owlPDOFP12->exec($strx);
		}

		$owlPDORPT->commit();
		$owlPDOFP12->commit();
	}catch (PDOException $e){
		$owlPDORPT->rollback();
		$owlPDOFP12->rollback();
		echo "Error, device ".$dbserverfp12." " . addslashes($e->getMessage());
		die();
	}
	echo "Sukses ",$dbserverfp12." => ".$location12."<br>";
	$sukses++;
}else{
	echo "Gagal ",$dbserverfp12." => ".$location12."<br>";
	$gagal++;
}




echo "Selesai<br>";
echo "Sukses = ".$sukses."<br>";
echo "Gagal = ".$gagal."<br>";

function fetchDatax($query=null){
    global $owlPDOFP5;
	
	$result = array();
    
	# Arrange to Array
    if($query==null){
		echo "Error";
	}else{
		try{
			$str=$owlPDOFP5->query($query);
			$str->setFetchMode(PDO::FETCH_ASSOC);
		}catch (PDOException $e){
			echo " Gagal: ".$e->getMessage(); //return exception
            exit;
		}

		while($bar=$str->fetch()){
		     $result[] = $bar;
		}
	}
	return $result;
}

?>
