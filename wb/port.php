<?php
require_once('lib/zLib.php');
###setting visudo : www-data ALL=NOPASSWD: ALL
###Data timbangan dapat dari databse
$query = "SELECT * FROM mssystem";
$dataTimbangan = fetchdata($query)[0];
$port = $dataTimbangan['port'];
$baudrate = $dataTimbangan['baudrate'];
$databit = $dataTimbangan['databit'];
$parity = $dataTimbangan['parity'];
$stopbit = $dataTimbangan['stopbit'];

$newdatabit = 'cs'.$databit;
if ($parity == 'NONE') {
	$newparity = '-parenb';
}else{
	$newparity = 'parenb';
}

if ($stopbit == '1') {
	$newstopbit = '-cstopb';
}else if ($stopbit == '2'){
	$newstopbit = 'cstopb';
}

shell_exec('sudo -S chmod 666 '.$port);
shell_exec('stty raw -F '.$port.' '.$baudrate. ' '.$newdatabit. ' '.$newparity.' '.$newstopbit.' -iexten -echo -echoe -echok -echoctl -echoke');

//echo 'stty raw -F '.$port.' '.$baudrate. ' '.$newdatabit. ' '.$newparity.' '.$newstopbit.' -iexten -echo -echoe -echok -echoctl -echoke';

$fp=fopen($port,'r');
$text=fread($fp,26);


// $val = trim($text);
// $val = preg_replace('/[\x00-\x1F\x80-\xFF]/','',$val);
// if (substr($val, 0,7)=="ST,GS,+") {
// 	$hasil = substr($val,8,7);
// 	$hasil = ltrim($hasil,'0');
// }

// //$hasil="";
// //if($hasil == '0000000'){
// //	$hasil = 0;
// //}

// echo trim($hasil);

//$jc = 7;
//$str = strpos($text,'kg');
//$rg = $str - $jc;

//if($rg <= 0)
//{
	//$rg = 0;
	//$jc = $str;
//}	

//$val = trim(substr($text,$rg,$jc));
//$val = preg_replace('/[^0-9]/','',$val);
//$val = ltrim($val,'0');

 //if($val == "" || $val == '00')
 	$val = 0;*/
$val='0da';
echo $text;

@fclose($fp);


?>
