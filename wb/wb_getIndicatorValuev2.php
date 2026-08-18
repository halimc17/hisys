<?php
$data = json_decode(file_get_contents('http://localhost:300/'));
if(!$data->status){
	sleep(20);
}
$value = $data->status ? $data->msg : '0';
echo $value;