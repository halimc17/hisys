<?
require_once('master_validation.php');
require_once('config/connection.php');

  $action=$_POST['action'];
  $val  =$_POST['id'];
  $kdpt  =$_POST['kdpt'];
   if($action=='insert'){
   $stra="insert into ".$dbname.".sdm_ho_thr_setup
			(component,kodept) values(".$val.",'".$kdpt."')";
   }
   else
   {
   $stra="delete from ".$dbname.".sdm_ho_thr_setup
			where component=".$val." and kodept='".$kdpt."'";   	
   }
	try{$owlPDO->exec($stra); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		

?>
