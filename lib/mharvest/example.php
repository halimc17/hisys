<?php
/** INCLUDE CLASS*/
include("getContentAPI.php");


$getApi = new getContentAPI;
$options = array(
    'client_id' => 'USERSYSTEM',
    'client_secret' => 'a09c394c8f065c7de7109fd9c634d9dd',
    'username' => 'tim.owl'
);
/** GET API KEY */
$url = "http://62.72.29.201/owl/mobile/index.php/api/access_token/api_key";
$getApi->init($url,$options);
/** 
 * Jika Sudah memiliki API_KEY 
 * =================================
 * $getApi->api_key = 'dc90ed68c4bd17cd8e6fa6c4aa0fff68';

 * Parametr Api Key
 * =================================
 * echo $getApi->api_key;
 

 * GET METHOD
 * =================================
 * $url = 'http://62.72.29.201/owl/mobile/index.php/api/module/sdm/karyawan/load';
 * $data = $getApi->get($url)->result;
  
 
 *  POST METHOD 
 * ================================= */
// $url = 'http://62.72.29.201/owl/mobile/index.php/api/module/setupversion/setupversion/load';
$url = 'http://62.72.29.201/owl/mobile/index.php/api/module/mharvest/getHeader/send';
$dataParam = array(
    'method'=>'transaction',
    'dateFrom' => '2024-01-01',
    'dateTo' => '2024-10-01'
);
$data = $getApi->post($url,$dataParam);


/** RESULT 
 * =================================
 * Default ARRAY 
 * Check status | Only Result | Response is Status and Result Data
*/
/** Example 
 * =================================
*/
var_dump($data->response);

/***
 Response Keys
 =================================
$data->response(
    'status'=>200,
    'error'=>true,
    'message'=>'',
    'result'=>[]
);
$data->state(
    'status'=>200,
    'error'=>true,
    'message'=>''
);
$data->result;// Array
*/
?>