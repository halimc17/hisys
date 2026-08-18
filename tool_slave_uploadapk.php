<?php
//ini_set('display_errors',0);
//error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
$method             = checkPostGet('method', '');
$updatelog          = checkPostGet('updatelog', '');
$updatelog          = str_replace("'","",$updatelog);
$updatelog          = str_replace('"',"",$updatelog);
$updatelog          = trim($updatelog);
$updatelog          = replaceEnter($updatelog);
$versi              = checkPostGet('versi', '');
$namaversi          = checkPostGet('namaversi', '');

$path               = "mobile/update/";

$tgl=date('Y-m-d H:i:s');
switch ($method) {
case 'submitfile':
	if($versi==''){
		exit("Warning : Versi wajib diisi.");
	}
	if(strlen($versi)!=5){
		exit("Warning : Format Versi Salah, Format yang benar adalah 10205.");
	}
	$versi=substr($versi,0,1)."0".substr($versi,2,1)."0".substr($versi,4,1);
	
	#http://owl.ksp-agro.com/mobile/update/owl-app-5.apk
	
	$filename = "owl-app-".$versi.".apk";
	$linkfile = "http://owl.ksp-agro.com/".$path.$filename;
	
	$data = $_POST;
	try{
		$owlPDO->beginTransaction();
		$str = "select * from " . $dbname . ".data_versionlog where appversion='".$versi."' and appversion_name='".$namaversi."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$str = "update ".$dbname.".data_versionlog set updatelog='".$updatelog."' where appversion='".$versi."' and appversion_name='".$namaversi."'";
			$owlPDO->exec($str);
		}else{
			$str = "insert into " . $dbname . ".data_versionlog (`id`,`appversion`,`updatelog`,`updatetime`,`appversion_name`)
			values ('','".$versi."','".$updatelog."','".$tgl."','".$namaversi."')"; #exit("error".$str);
			$owlPDO->exec($str);			
		}
		

		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
				if(($filetype=='.apk')){
					#$str = "update ".$dbname.".data_version set urlapp='".$linkfile."'";
					#$owlPDO->exec($str);
					if (!file_exists($path)) {
						mkdir($path, 0777, true);
					}
					file_put_contents($path.$filename,$file_tmpname);
				}else{
					throw new PDOException("Format file harus .apk.");
				}
			}
		}
	
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
	break;
case'release':
	
	try{
	$owlPDO->beginTransaction();
	
	$filename = "owl-app-".$versi.".apk";
	$linkfile = "http://owl.ksp-agro.com/".$path.$filename;

	
	$str = "select * from " . $dbname . ".data_versionlog where appversion='".$versi."' and appversion_name='".$namaversi."'";
	$res = fetchdata($str);
	
	$str = "update ".$dbname.".data_version set appversion_name='".$namaversi."', appversion='".$versi."', urlapp='".$linkfile."'"; 
	$owlPDO->exec($str);
	
	$str = "update ".$dbname.".data_versionlog set tanggalrelease='".$tgl."', releaseby='".$_SESSION['standard']['username']."' where appversion='".$versi."' and appversion_name='".$namaversi."'";
	$owlPDO->exec($str);
	
	$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
	break;
}
?>