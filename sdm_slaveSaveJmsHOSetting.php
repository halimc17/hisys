<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');

  $perusahaan=$_POST['perusahaan'];
  $karyawan =$_POST['karyawan'];
  $pphjms =$_POST['pphjms'];
   $stra="update ".$dbname.".sdm_ho_hr_jms_porsi
			set value=".$perusahaan." where id='perusahaan'";
	try{
		$owlPDO->exec($stra);
	}catch (PDOException $e){
		echo "error : ".$e->getMessage();
	}
	
	$stra="update ".$dbname.".sdm_ho_hr_jms_porsi
			set value=".$karyawan." where id='karyawan'";
	try{
		$owlPDO->exec($stra);
	}catch (PDOException $e){
		echo "error : ".$e->getMessage();
	}
	$stra="update ".$dbname.".sdm_ho_hr_jms_porsi
			set value=".$pphjms." where id='pph21'";
	try{
		$owlPDO->exec($stra);
	}catch (PDOException $e){
		echo "error : ".$e->getMessage();
	}
?>
