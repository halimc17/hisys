<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$param = $_POST;if(count($param)==0){$param = $_GET;}

try{
	$owlPDO->beginTransaction();
	
	$str="select notransaksi from ".$dbname.".wb where notransaksi='".$param['ticketno']."' and netto <= '0'";
	$res=fetchdata($str);
	$jlhtiket=count($res);
	
	if($jlhtiket<=0){
		throw new PDOException("No. Tiket ".$param['ticketno']." tidak terdaftar di sistem.");
	}
	
	$data = array(
		'deletecomment'=>$param['catatan'],
		'deletetime'=>date('Y-m-d H:i:s'),
		'deleteuser'=>$_SESSION['standard']['username']
	);
	$where = "notransaksi='".$param['ticketno']."'";
	$str = updateQuery($dbname,'wb',$data,$where);
	$owlPDO->exec($str);
	
	$str="insert into ".$dbname.".wb_log select * from ".$dbname.".wb where notransaksi='".$param['ticketno']."'";
	$owlPDO->exec($str);
	
	$where = "notransaksi='".$param['ticketno']."'";
	$str = deleteQuery($dbname,'wb',$where);
	$owlPDO->exec($str);

	$owlPDO->commit();
}catch (PDOException $e){
	$owlPDO->rollback();
	exit("Gagal, ".$e->getMessage());
}
?>
