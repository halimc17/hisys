<?
$pos='';
foreach($_POST as $key=> $val){
 $pos.="&".$key."=>".$val;
}
$get='';
foreach($_GET as $key=> $val){
 $get.="&".$key."=>".$val;
}
$zt=explode("/",$_SERVER['PHP_SELF']);
$ada=strpos($zt[2],'login');
if($ada===false){
	$ini_array = parse_ini_file("../lib/nangkoel.ini");
	if($ini_array['ACTIVITY_LOG']=='ON' or $ini_array['ACTIVITY_LOG']=='1'){
		$daysKeep=$ini_array['KEEP_LOG_DAYS'];
		$ztu=mktime(0,0,0,date('m'),date('d')-$daysKeep,date('Y'));
		$last3month=date('Y-m-d H:i:s',$ztu);

		#delete old log as configured in ini file for days log kept:
		$str="delete  from ".$dbname.".user_activity where username='".$_SESSION['standard']['username']."'
			  and waktu<'".$last3month."' or username=''";
		 try{$owlPDO->exec($str); }catch (PDOException $e) {}
		$str="insert into ".$dbname.".user_activity (username,file,karyawanid,post,get,ip,compname)
			   values('".$_SESSION['standard']['username']."','".$_SERVER['PHP_SELF']."','".$_SESSION['standard']['userid']."',
			   '".$pos."','".$get."','".$_SERVER['REMOTE_ADDR']."','')";
		try{$owlPDO->exec($str); }catch (PDOException $e) {}		
	} 
}
?>