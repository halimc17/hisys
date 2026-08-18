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
$url = "http://182.23.67.40/dma/mobile/index.php/api/access_token/api_key";
$getApi->init($url, $options);
/** 
 * Jika Sudah memiliki API_KEY 
 * =================================
 * $getApi->api_key = 'dc90ed68c4bd17cd8e6fa6c4aa0fff68';

 * Parametr Api Key
 * =================================
 * echo $getApi->api_key;
 

 * GET METHOD
 * =================================
 * $url = 'http://182.23.67.40/dma/mobile/index.php/api/module/sdm/karyawan/load';
 * $data = $getApi->get($url)->result;
  
 
 *  POST METHOD 
 * ================================= */
$url = 'http://182.23.67.40/dma/mobile/index.php/api/module/Mrawat/getheadererp/load';
$dataParam = array(
    'divisi' => 'SD3E01',
    'mandor' => '0000000180'
);
$dataH = $getApi->post($url, $dataParam);

$url = 'http://182.23.67.40/dma/mobile/index.php/api/module/Mrawat/getdetailerp/load';
$dataParam = array(
    'notransaksi' => '20250512232116-1331'
);
$dataD = $getApi->post($url, $dataParam);



/** RESULT 
 * =================================
 * Default ARRAY 
 * Check status | Only Result | Response is Status and Result Data
 */
/** Example 
 * =================================
 */
var_dump($dataH->response);
var_dump($dataD->response);

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
