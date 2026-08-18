<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');

	$newcap		=$_POST['newcap'];
	$newloc 	=$_POST['newloc'];
	$langname	=$_POST['langname'];
	$idx		=$_POST['idx'];
//add column to lang table
   $str="update ".$dbname.".bahasa set location='".$newloc."',
        ".$langname."='".$newcap."' where idx=".$idx;	
	try{
   		$owlPDO->exec($str); //insert hedaer	
	}catch (PDOException $e){
		echo " Gagal,".$e->getMessage();
	}
?>
