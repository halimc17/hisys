<?
function antiinjection($data){
	global $dbserver;
	global $uname;
	global $passwd;
	global $dbname;
	$conTest=mysqli_connect($dbserver,$uname,$passwd,$dbname);
	$filter_sql = mysqli_real_escape_string($conTest,stripslashes(strip_tags(htmlspecialchars($data,ENT_QUOTES))));
	return $filter_sql;
}
function injetionDataSend($Var){
	foreach($Var as $k => $v){
		if(!is_array($v)){
			$Var[$k] = antiinjection($v);
		}
	}
	return $Var;
}
if(isset($_GET)){
	$_GET = injetionDataSend($_GET);
}
if(isset($_GET)){
	$_POST = injetionDataSend($_POST);
}

 ?>
