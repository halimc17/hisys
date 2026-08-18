<?php
function getPrivillageType($conn,$dbname){
	$str="select access_name from ".$dbname.".access_type where status=1";
	$res = fetchdata($str);
	if(count($res) > 0){
		$_SESSION['access_type'] = $res[0]['access_name'];
		
		if(isset($_SESSION['access_type'])){
			return true;
		}else{
		    return false;
		}
	}else{
		return false;
	}
}

function getPrivillages($conn,$username,$dbname){
	//get user privillages
	$str="select * from ".$dbname.".auth where uname='".$username."' and status=1";
	$res=fetchdata($str);
	if(count($res) > 0){
		$x=0;
		foreach($res as $val){
			if($x==0){
				$c_o=$val['menuid'];
			}else{
				$c_o.=",".$val['menuid'];
			}
			$_SESSION['priv'][$x]=$val['menuid'];
			$x+=1;
		}
		
		$_SESSION['allpriv']=$c_o;
		
		if(count($_SESSION['priv']) > 0){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}
?>

