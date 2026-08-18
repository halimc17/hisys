<?
require_once('master_validation.php');
require_once('config/connection.php');

  $action=$_POST['action'];
  $val  =$_POST['id'];
   if($action=='insert'){
   $stra="insert into ".$dbname.".sdm_ho_bonus_setup
			(component) values(".$val.")";
   }
   else
   {
   $stra="delete from ".$dbname.".sdm_ho_bonus_setup
			where component=".$val;   	
   }
	try{$owlPDO->exec($stra); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }	
?>
