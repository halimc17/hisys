<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

switch($method){
	
}

echo 'xxxx';
?>