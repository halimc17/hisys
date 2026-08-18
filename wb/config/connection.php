<?php
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


$dbserver='localhost';
$dbport  ='3306';
$dbname  ='wb';
$uname	='owlApplication';
$passwd	='P4lm@pR!maN3';
try{
$owlPDO = new PDO('mysql:host='.$dbserver.';dbname='.$dbname, $uname, $passwd, array(PDO::ATTR_PERSISTENT => false));
$owlPDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch (PDOException $e) {
       print " Gagal, could not connect\n";	
       print "Error!: " . $e->getMessage() . "<br/>";
   die();
}

@require_once('activity_log.php');
?>
