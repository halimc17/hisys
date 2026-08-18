<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param =$_POST;
$menuid=trim($_POST['menuid']);
$uname =trim($_POST['uname']);
$action=$_POST['action'];
$status=false;

try {
	$owlPDO->beginTransaction();
		
		$str=$owlPDO->query("select * from ".$dbname.".auth  where namauser='".$uname."'  and menuid=".$menuid); 
		$str->setFetchMode(PDO::FETCH_OBJ);
		$numrows=owlBaris($str); 
		if($numrows>0){
		   $status=true;
		}else{
		   $status=false;   	 
		}

		//if not exist
		if(!$status and $action=='remove'){
			$str="insert into ".$dbname.".auth(namauser,menuid,status,lastuser) 
			values('".$uname."',".$menuid.",0,'".$_SESSION['standard']['username']."')";
			$owlPDO->exec($str);
			$s=5;
		}

		//if exist and set to deactive
		if($status and $action=='remove'){
			$str="update ".$dbname.".auth set status=0, lastuser='".$_SESSION['standard']['username']."' 
			where namauser='".$uname."'and menuid=".$menuid;
			$owlPDO->exec($str);
			$s=2;
		}else if(!$status and $action=='add'){
			$str="insert into ".$dbname.".auth (namauser,menuid,status,lastuser) 
			values('".$uname."',".$menuid.",1,'".$_SESSION['standard']['username']."')";
			$owlPDO->exec($str);
			$s=3;
		}else {
		//if exist and set to active
			$str="update ".$dbname.".auth set status=1,lastuser='".$_SESSION['standard']['username']."'  
			where namauser='".$uname."' and menuid=".$menuid;
			$owlPDO->exec($str);
			$s=4;
		}

		$_SESSION['menuid']=array();
		$_SESSION['idmenudt']=array();
		$_SESSION['devider'] =array();
		$_SESSION['idchild'] =array();
		$_SESSION['idchild'][$param['menuid']]=$param['menuid'];
		$_SESSION['menuid']=$param['menuid'];
		for($i=1;$i<=7;$i++){				
			addparent($param);
			addchild($param);
			$dt[$_SESSION['menuid']]=$_SESSION['menuid'];
		}
		
	$owlPDO->commit();
} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}	

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


function addchild($param){
	global $owlPDO;
	global $dbname;
	
	// echo"<pre>";
	// print_r($param);
	// echo"</pre>";
	foreach($_SESSION['idchild'] as $key => $idmenu){		
		$str   = "select * from ".$dbname.".menu where parent = '".$idmenu."'";
		$res   = fetchdata($str);
		if(count($res)>0){
			unset($_SESSION['idchild'][$key]);
			foreach($res as $bar){			
				$jlh = 0;
				$str = "select * from ".$dbname.".auth where namauser='".$param['uname']."' and menuid = '".$bar['id']."'";
				$res = fetchdata($str);
				$jlh = count($res);
				if($param['action']=='add' and $jlh == '0'){				
					$str="insert into ".$dbname.".auth (namauser,menuid,status,lastuser) 
					values('".$param['uname']."',".$bar['id'].",1,'".$_SESSION['standard']['username']."')";	
					$owlPDO->exec($str); 
				}elseif($param['action']=='add' and $jlh > '0' and $res[0]['status']=='0'){
					$str="update ".$dbname.".auth set status=1,lastuser='".$_SESSION['standard']['username']."'  
					where namauser='".$param['uname']."' and menuid=".$bar['id'];	
					$owlPDO->exec($str); 
				}
				if($param['action']=='remove' and $jlh > '0'){
					$str="delete from ".$dbname.".auth where namauser='".$param['uname']."' and menuid='".$bar['id']."'";
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
	global $status;
	
	$str  = "select * from ".$dbname.".menu where  id = '".$_SESSION['menuid']."'";
	$res  = fetchdata($str);
	$induk= $res[0]['parent'];
	$urut = $res[0]['urut'];
	$clss = $res[0]['class'];
	if($induk>0){
		$class="";
		$str = "select * from ".$dbname.".menu where  parent = '".$induk."' and urut < '".$urut."' and class!='click' order by urut desc limit 2";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['class']!=$class and $clss=='click'){
				if($param['action']=='add'){					
					$_SESSION['devider'][]=$bar['id'];
				}
				
				$jlh = 0;
				$str = "select * from ".$dbname.".auth where namauser='".$param['uname']."' and menuid = '".$bar['id']."'";
				$res = fetchdata($str);
				$jlh = count($res);
				if($param['action']=='add' and $jlh == '0'){				
					$str="insert into ".$dbname.".auth (namauser,menuid,status,lastuser) 
					values('".$param['uname']."',".$bar['id'].",1,'".$_SESSION['standard']['username']."')";	
					$owlPDO->exec($str);
				}elseif($param['action']=='add' and $jlh > '0' and $res[0]['status']=='0'){
					$str="update ".$dbname.".auth set status=1,lastuser='".$_SESSION['standard']['username']."'  
					where namauser='".$param['uname']."' and menuid=".$bar['id'];	
					$owlPDO->exec($str); 
				}
				if($param['action']=='remove' and $jlh > '0'){
					$str="delete from ".$dbname.".auth where namauser='".$param['uname']."' and menuid='".$bar['id']."'";
					$owlPDO->exec($str);
				}
				
			}
			$class=$bar['class'];
		}
		
		$jlh = 0;
		$str = "select * from ".$dbname.".auth where namauser='".$param['uname']."' and menuid = '".$induk."'";
		$res = fetchdata($str);
		$jlh = count($res);
		if($param['action']=='add' and $jlh == '0'){				
			$str="insert into ".$dbname.".auth (namauser,menuid,status,lastuser) 
			values('".$param['uname']."',".$induk.",1,'".$_SESSION['standard']['username']."')";	
			$owlPDO->exec($str);
			$_SESSION['menuid']=$induk;
		}elseif($param['action']=='add' and $jlh > '0' and $res[0]['status']=='0'){
			$str="update ".$dbname.".auth set status=1,lastuser='".$_SESSION['standard']['username']."'  
			where namauser='".$param['uname']."' and menuid=".$induk;	
			$owlPDO->exec($str); 
			$_SESSION['menuid']=$induk;
		}
		if($param['action']=='remove' and $jlh > '0'){				
			$str="update ".$dbname.".auth set status=0,lastuser='".$_SESSION['standard']['username']."'  
			where namauser='".$param['uname']."' and menuid=".$induk;	
			#$owlPDO->exec($str);
		}
		
	}
}
?>
