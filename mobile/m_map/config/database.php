<?
if(file_exists("config/database.php")){
	include("config/database.php");
	print_r($active_group);
	if(!empty($config[$active_group]) && !empty($config[$active_group][ENVIRONMENT])){
		$db = (object)$config[$active_group][ENVIRONMENT];
	}else{
		header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
		//echo "Database Config ".$active_group."/".ENVIRONMENT." Undefined !!";
		exit(1);
	}
}else{
	header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
	echo "Database Config Undefined !!";
	exit(1);
}

?>