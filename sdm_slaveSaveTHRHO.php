<?
require_once('master_validation.php');
require_once('config/connection.php');

  $periode= $_SESSION['thrperiode'];
  $userid=$_POST['userid'];
  $val  =$_POST['val'];
  $terbilang=$_POST['terbilang'];
   
   $str="delete from ".$dbname.".sdm_ho_detailmonthly where karyawanid=".$userid." 
         and periode='".$periode."' and type='thr'";
    try{
		$owlPDO->exec($str); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage();
		die();
	}
   $str="insert into ".$dbname.".sdm_ho_detailmonthly
   (karyawanid,component,value,periode,plus,updatedby,type)
   value(".$userid.",1,".$val.",'".$periode."',1,'".$_SESSION['standard']['username']."','thr')";	 
   try{
		$owlPDO->exec($str); 
		$str1="delete from ".$dbname.".sdm_ho_payrollterbilang where periode='".$periode."'
	      and userid=".$userid." and type='thr'";
	    try{
			$owlPDO->exec($str1);
			$str2="insert into ".$dbname.".sdm_ho_payrollterbilang (userid,periode,terbilang,type)
		       values(".$userid.",'".$periode."','".$terbilang."','thr')";
		    try{
				$owlPDO->exec($str2); 
			}catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			} 
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage();
		die();
	}	
?>
