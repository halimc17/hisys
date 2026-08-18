<?php
$data = json_decode(file_get_contents('http://localhost:300/'));
if(!$data->status){
	sleep(5);
}
$value = $data->status ? str_replace("\nNT,","",$data->msg) : '0';

$value = preg_replace('/[^0-9]/', '', $value);

echo intval($value);

?>