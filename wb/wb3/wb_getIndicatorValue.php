<?php

exec('mode COM4: BAUD=2400 PARITY=E data=7 stop=1 xon=on');

$fp = fopen("COM4:", "r");
if (!$fp){
	echo "0";
}else{
	$text=fgets($fp,26);
	$onlynumber=0;
	$jc = 8;
	$str = strpos($text,'Kg');
	$rg = $str - $jc;

	if($rg <= 0){
		$rg = 0;
		$jc = $str;
	}	

	$val = trim(substr($text,$rg,$jc));
	$val = preg_replace('/[^\d-]+/', '', $val); 
	
	if(CheckNumber($val) == '-'){
		$val=preg_replace('/[^0-9]/','',$val);
		$val = ltrim($val,'0');
		$val="-".$val;
	}else{
		$val=preg_replace('/[^0-9]/','',$val);
		$val = ltrim($val,'0');
	}
	
	// $onlynumber = intval(preg_replace('/[^0-9]/','',$val));
	

	if($val == "" || $val == '00')
		$val = 0;

	echo $val;
	
	fclose($fp);
	
}

// echo 4490;

function CheckNumber($x) {
  if ($x > 0)
    {$message = "+";}
  if ($x == 0)
    {$message = "0";}
  if ($x < 0)
    {$message = "-";}
  return $message;
}
?>