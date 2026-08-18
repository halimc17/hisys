<?
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$periodeskrg = date('Y-m');
$createdtime=date('Y-m-d H:i:s');
$path = "fileupload/filingsystem/";

$arrtipekb = array("Bank");

try{
	$owlPDO->beginTransaction();

	$strx="select * from ".$dbname.".filemanager where (sourceid!='x' and sourceid!='0' and sourceid!='folder') and level <= '2'";
	$resx=fetchdata($strx);
	foreach($resx as $keyx=>$valx){
		$str="select * from ".$dbname.".filemanager where induk='".$valx['id']."' and namafile='".$periodeskrg."'";
		$res=fetchdata($str);
		$count=count($res);
		
		if($count <= 0){
			$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$valx['id']."','3','".$periodeskrg."','folder','','1','','".$createdtime."','','".$createdtime."','folder')";
			$owlPDO->exec($str);
			$idresult = $owlPDO->lastInsertId();
			
			$optindukfolder = makeOption($dbname,'filemanager','id,namafile',"id='".$valx['induk']."'");
			
			## Get PT
			$optPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$optindukfolder[$valx['induk']]."'");
			
			$structure = $path."".$optPt[$optindukfolder[$valx['induk']]]."/".$optindukfolder[$valx['induk']]."/".$valx['namafile']."/".$periodeskrg;
			if (!mkdir($structure, 0777, true)){
				// throw new PDOException('Failed to create folders...');
			}
			
			foreach($arrtipekb as $val){
				$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','4','".$val."','folder','','1','','".$createdtime."','','".$createdtime."','folder')";
				$owlPDO->exec($str);
				
				$structure = $path."".$optPt[$optindukfolder[$valx['induk']]]."/".$optindukfolder[$valx['induk']]."/".$valx['namafile']."/".$periodeskrg."/".$val;
				if (!mkdir($structure, 0777, true)){
					// throw new PDOException('Failed to create folders...');
				}
			}
		}
	}
	
	$strx="select * from ".$dbname.".filemanager where sourceid='x' and level = '1'";
	$resx=fetchdata($strx);
	foreach($resx as $keyx=>$valx){
		$idresult="";
		$str="select * from ".$dbname.".filemanager where induk='".$valx['id']."' and namafile='KAS KECIL'";
		$res=fetchdata($str);
		$count=count($res);
		$idresult=$res[0]['id'];
		
		if($count <= 0){
			$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$valx['id']."','2','KAS KECIL','folder','','1','','".$createdtime."','','".$createdtime."','folder')";
			$owlPDO->exec($str);
			$idresult = $owlPDO->lastInsertId();
			
			$optindukfolder = makeOption($dbname,'filemanager','id,namafile',"id='".$valx['induk']."'");
						
			$structure = $path."".$optindukfolder[$valx['induk']]."/".$valx['namafile']."/KAS KECIL";
			
			if (!mkdir($structure, 0777, true)){
				// throw new PDOException('Failed to create folders...');
			}
		}
		
		$count=0;
		$str="select * from ".$dbname.".filemanager where induk='".$idresult."' and namafile='".$periodeskrg."'";
		$res=fetchdata($str);
		$count=count($res);
		if($count <= 0){
			$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idresult."','3','".$periodeskrg."','folder','','1','','".$createdtime."','','".$createdtime."','folder')";
			$owlPDO->exec($str);
			
			$optindukfolder = makeOption($dbname,'filemanager','id,namafile',"id='".$valx['induk']."'");
						
			$structure = $path."".$optindukfolder[$valx['induk']]."/".$valx['namafile']."/KAS KECIL/".$periodeskrg;
			if (!mkdir($structure, 0777, true)){
				// throw new PDOException('Failed to create folders...');
			}
		}
	}

	$owlPDO->commit();
}catch (PDOException $e){
	$owlPDO->rollback();
	echo "Error, " . addslashes($e->getMessage());
	die();
}

?>