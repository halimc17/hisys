<?php
//15 minutes cache keep by browser
// session_cache_expire(120);
if(!isset($_SESSION)){
	session_start();
}
//session_start();
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


//check for liftime session allowed++++++++++++++++++++++
if(time()>intval($_SESSION['DIE'])){
	echo " [Gagal/Failed/Error], your session has expired, please press refresh button and login again..!";
	session_destroy();
	exit();
}else{
	$_SESSION['DIE']=time()+$_SESSION['MAXLIFETIME'];	
}
//++++++++++++++++++++++++++++++++++++++++++++++++++++

if(isset($_SESSION['standard']['username']) AND isset($_SESSION['access_type'])){
	if(strlen($_SESSION['standard']['username'])>=3 AND ($_SESSION['access_type']=='level' OR $_SESSION['access_type']=='detail')){
		//Go on
		//print_r($_SESSION);
	}else{
		exit('Sorry, You entering the system like cracker');		
	}
}else{
	if($_SESSION['security']=='on'){
	   exit('Not Authorized');		
	}else{
		//doing nothing. Just pass away
	}
}

?>
