<?php
session_cache_expire(25);//25 minutes cache keep by browser
if(!isset($_SESSION)){
	session_start();
}
$bb='';
if(isset($_POST['par'])){
    $bb= explode("/",$_POST['par']);
}else if(isset($_GET['par'])){
    $bb= explode("/",$_GET['par']);
}

    $dilarang=Array(
        'SELECT(SLEEP',
        'SELECT%28SLEEP',
        'UNION SELECT',
        'UNION(SE',
        'UNION( SE',
        'UNION ALL ',
        'INFORMATION_SCHEMA',
        '(CAST(',
        'SELECT CONCAT(CAST',
        'GTID_SUBSET',
        '%20RLIKE%20',
        ' RLIKE ',
        'DBMS_PIPE',
        'EXTRACTVALUE',
        '%28CHR%2860%29%7C%7CCHR%28',
        '(CHR(',
        '%28CAST%28DATABASE%28',
        'NCHAR)',
        '%20WHERE%20TABLE_NAME%3D',
        'WHERE TABLE_NAME=',
        'DROP TABLE ',
        'DROP DATABASE ',
        '/USR/LOCAL/SBIN',
        '/BIN/BASH',
        'CMD=BASH',
        'CMD = BASH',
        '.BASH_HISTORY',
        'BASH%20-C%20%27BASH',
        'BASH -C \'BASH -I',
        '.BASH_HISTORY',
        '/ETC/PASSWD'        
    );

if (isset($_GET)){
  foreach ($_GET as $key=>$string){
    $string=strtoupper($string);
    foreach($dilarang as $idx=>$chunk)
    if(strpos($string,$chunk)>-1){
        echo "ERROR: Connection abuse:".$idx.":".$chunk;
        exit();
    }
  }
}

if (isset($_POST)){
  foreach ($_POST as $key=>$string){
    if(is_string($string)){
        $string=strtoupper($string);
        foreach($dilarang as $idx=>$chunk)
        if(strpos($string,$chunk)>-1){
            echo "ERROR: Connection abuse:".$idx.":".$chunk;
            exit();
        }
    }
  }
}


if(@count($bb)>2 and $bb[2]!=0){
  	echo " [Gagal/Failed/Error], your session has expired, please press refresh button and login again..!";
	session_destroy();
	#echo "<script>alert('Session expired. You'll be redirect to login page');location.reload(true)</script>";
	exit();  
}
//unset the param par
unset($_POST['par']);
unset($_GET['par']);

//check for liftime session allowed++++++++++++++++++++++
// if(!isset($_SESSION['DIE']) or time()>intval($_SESSION['DIE']))
// {
// 	echo " [Gagal/Failed/Error], your session has expired, please press refresh button and login again..!";
// 	session_destroy();
// 	#echo "<script>alert('Session expired. You'll be redirect to login page');location.reload(true)</script>";
// 	exit();
// }else{
//   $_SESSION['DIE']=time()+$_SESSION['MAXLIFETIME'];
// }

//++++++++++++++++++++++++++++++++++++++++++++++++++++
if(isset($_SESSION['standard']['username']) AND isset($_SESSION['access_type']))
{
	if(strlen($_SESSION['standard']['username'])>=6 AND ($_SESSION['access_type']=='level' OR $_SESSION['access_type']=='detail'))
	{//Go on
	//print_r($_SESSION);
	}
	else
	exit('Sorry, You entering the system like cracker');
}
else  
   {
   	if($_SESSION['security']=='on')
	   {
	    exit('Not Authorized');
		//echo"<pre>";
		//print_r($_SESSION);//exit('Not Authorized');
		//echo"</pre>";
	   }
	else
	{//doing nothing. Just pass away
	}   
   }
if(!isset($_SESSION['org']['holding'])){
 	echo " [Gagal/Failed/Error], your session has expired, please press refresh button and login again..!";
	session_destroy();
	#echo "<script>alert('Session expired. You'll be redirect to login page');location.reload(true)</script>";
	exit();   
}  

$ini_array = parse_ini_file("../lib/nangkoel.ini");
$bStart=str_replace(".","",$ini_array['BACKUP_START']);
$bEnd=str_replace(".","",$ini_array['BACKUP_END']);
$now=date('Hi');
if($now>$bStart and $now<$bEnd){
 	// echo " [Gagal/Failed/Error], Sorry, Server is on routine backup process,\n
                                // Please login after ".$ini_array['BACKUP_END'].", thank you";
	// session_destroy();
	// exit();       
}
?>