<?php
## 25 minutes cache keep by browser
// session_cache_expire(25);
if(!isset($_SESSION)){
	session_start();
}

$bb='';
if(isset($_POST['par'])){
    $bb= explode("/",$_POST['par']);
}else if(isset($_GET['par'])){
	$bb= explode("/",$_GET['par']);
}

$msgdestroy="";

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
  	$msgdestroy="[Information] - Your session has expired, please press refresh button and login again..!";  
}

## Unset the Param par
unset($_POST['par']);
unset($_GET['par']);

## check for liftime session allowed ##
// if(!isset($_SESSION['DIE']) or time()>intval($_SESSION['DIE'])){
	// $msgdestroy="[Information]- Your session has expired, please press refresh button and login again..!";
// }else{
	// $_SESSION['DIE']=time()+$_SESSION['MAXLIFETIME'];	
// }

## CHECK SECURITY ##
if(isset($_SESSION['standard']['username']) AND isset($_SESSION['access_type'])){
	if(strlen($_SESSION['standard']['username'])>=6 AND ($_SESSION['access_type']=='level' OR $_SESSION['access_type']=='detail')){
		## Go on
	}else{
		$msgdestroy="[Information] - You entering the system like cracker";		
	}
}else{
	if($_SESSION['security']=='on'){
		$msgdestroy="[Information] - Not Authorized";
	}else{
		## doing nothing. Just pass away
	}   
}

if(!isset($_SESSION['org']['holding'])){
 	$msgdestroy="[Information] - Your session has expired, please press refresh button and login again..!";
}  

## ROUTINE BACKUP ##
$ini_array = parse_ini_file("lib/nangkoel.ini");
$bStart=str_replace(".","",$ini_array['BACKUP_START']);
$bEnd=str_replace(".","",$ini_array['BACKUP_END']);
$now=date('Hi');
if($now>$bStart and $now<$bEnd){
 	$msgdestroy="[Information] - Server is on routine backup process, Please login after ".$ini_array['BACKUP_END'].", Thank You";
	       
}

if($msgdestroy!=''){
	$messageType="error-message";
	?>
	
	<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #76B1DA;
                font-family: "Segoe UI", sans-serif;
            }
            .message-container {
                text-align: center;
            }
            .failed-message {
                color: blue;
            }
            .error-message {
                color: red;
            }
            .success-message {
                color: green;
            }
            .h3 {    
                font-weight: 700;
                font-size: clamp(1.625rem,4vw,1.5rem);
                letter-spacing: -.0425rem;
                color:#1F3036;
            }
            .buttonku {
            appearance: none;
            background-color: transparent;
            border: 2px solid #285769;
            border-radius: 15px;
            box-sizing: border-box;
            color: #1F3036;
            cursor: pointer;
            display: inline-block;
            font-family: "Segoe UI", sans-serif;
            font-size: 25px;
            font-weight: 800;
            line-height: 80%;
            margin: 0;
            min-height: 30px;
            min-width: 180px;
            outline: none;
            padding: 16px 24px;
            text-align: center;
            text-decoration: none;
            transition: all 300ms cubic-bezier(.23, 1, 0.32, 1);
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            width: 110px;
            will-change: transform;
            }
            .buttonku:hover {
            color: #fff;
            background-color: #275370;
            box-shadow: rgba(0.3, 0.3, 0.3, 0.3) 5px 5px 15px;
            transform: translateY(-1px);
            }
            .logonya{
                width:300px;
                height:300px;
                border-image:stretch;
                border-radius:25px ;
            }
			.logox{
                width:300px;
                border-image:stretch;
                border-radius:25px ;
            }
        </style>
    </head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=SegoeUI">
    <body>
        <div class="message-container">
            <img class=logonya src="images/OWL_OV.png" alt="logo">
            <h2 class="<?php echo strtolower($messageType); ?>"><h3 class=h3><?php echo $msgdestroy; ?></h3>
			<br>
			<img class=logox src="images/disconnect.png">
			<br>
			<button type=button class=buttonku onclick="window.location='login.html'">Refresh</button></h2>
        </div>
    </body>
    </html>
	
	<?
	session_destroy();
	exit();
}

?>