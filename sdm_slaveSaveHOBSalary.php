<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');

  $userid=$_POST['userid'];
  $component  =$_POST['component'];
  $value=$_POST['value'];

   $stra="select * from ".$dbname.".sdm_ho_basicsalary where 
         karyawanid=".$userid." and component=".$component;
   $res=$owlPDO->query($stra) or die(print " Gagal: ".PDOException::getMessage());
   $res->setFetchMode(PDO::FETCH_OBJ);	 
		if(owlBaris($res)>0)
		{
		 //update
		 $str="update ".$dbname.".sdm_ho_basicsalary
		       set value=".$value.",updateby='".$_SESSION['standard']['username']."'
			   where karyawanid=".$userid."
			   and component=".$component;
		}
		else
		{
		 //insert
		 $str="insert into ".$dbname.".sdm_ho_basicsalary (karyawanid,component,value,updateby)
		       values(".$userid.",".$component.",".$value.",'".$_SESSION['standard']['username']."')";	
		}
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		}
?>
