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


	$str = "select * from ".$dbname.".user where telegramid='".$_GET['telid']."' and telegramid!=''";
	$res = fetchdata($str);
	if(count($res)>0){
		$_SESSION['standard']['username']=$res[0]['namauser'];
		$_SESSION['standard']['userid']=$res[0]['karyawanid'];
		$_SESSION['standard']['access_level']=$res[0]['hak'];
		$_SESSION['standard']['status']=$res[0]['status'];
		$_SESSION['standard']['logged']=$res[0]['logged'];

		$_SESSION['language']="ID";
		$strlang=$owlPDO->query("select legend,ID from ".$dbname.".bahasa order by legend");
		$strlang->setFetchMode(PDO::FETCH_NUM);
		while($barlang=$strlang->fetch()){
			$_SESSION['lang'][$barlang[0]]=$barlang[1];
		}
	}else{
		echo" Gagal, Sorry, anda tidak terdaftar.";
		exit();
	}

	if(isset($_SESSION['standard']['username'])){
		include_once('lib/zFunction.php');
		require_once('lib/detailSession.php');
		$jab = getPostingJabatan('rawatkebun');
		
		//get all data from user_empl table
		setEmplSession($owlPDO,$_SESSION['standard']['userid'],$dbname);
		if($isPrivillaged=getPrivillageType($owlPDO,$dbname)){
		}else{
			if($_SESSION['security']=='on'){
				echo" Gagal, Sorry, No Privillage available for all\ncontact Administrator";
				session_destroy();
				exit;
			}
		}
		
		$privable=getPrivillages($owlPDO,$_SESSION['standard']['username'],$dbname);
		if(!$privable AND $_SESSION['access_type']=='detail'){
			echo" Gagal, Sorry, No Privillage available for your account";
			session_destroy();
			exit;				
		}else if($_SESSION['standard']['access_level']==0 AND $_SESSION['access_type']!='detail'){
			if($_SESSION['security']=='on'){//if security is turned on
				echo" Gagal, Sorry, System uses Levelization Privillages, but you don't have any.\nContact your Administrator";
				session_destroy();
				exit;
			}
		}
		setEmployer($owlPDO,$dbname);//get employer detail and active transaction periode
		if(!in_array($_SESSION['empl']['jabatan'],$jab)){
			echo" Gagal, Anda tidak memiliki akses untuk melakukan Posting.";
			exit();
		}
	}
?>