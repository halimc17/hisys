<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');

  $userid=$_POST['userid'];
  $operator  =$_POST['operator'];

   $stra="update ".$dbname.".sdm_ho_employee set
			operator='".$operator."'
			where karyawanid=".$userid;
  try{$owlPDO->exec($stra); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }		
?>
