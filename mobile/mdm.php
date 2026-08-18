<?php
include("../lib/mharvest/getContentAPI.php");


$getApi = new getContentAPI;
$options = array(
	'client_id' => 'USERSYSTEM',
    'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
    'username' => 'tim.owl'
);
/** GET API KEY */
$url = "http://62.72.29.201/owl/mobile/index.php/api/access_token/api_key";
$getApi->init($url,$options);

//GET MDM DATA
$url = "http://62.72.29.201/owl/mobile/index.php/api/module/Mmdm/getAdminMdm/load";
$data=$getApi->get($url,$options);
// echo "<pre>";
// print_r($data->response['result']);
// echo "</pre>";

echo json_encode($data->response['result']);
?>