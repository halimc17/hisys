
<?
require_once('master_validation.php');
require_once('config/connection.php');

$type='regular';
$userid=$_POST['userid'];
$component  =$_POST['component'];
$plus  =$_POST['plus'];
$val  =$_POST['val'];
$periode=$_SESSION['pyperiode'];
$terbilang=$_POST['terbilang'];

if($plus==0 or $plus=='0')
{
	$val=$val*-1;
}

if($val==0)
{
//if 0 leave it, do nothing	
}
else
{		
	if(isset($_POST['replace']))
	{
/* update mejadi menghapus semua gaji kary bersangkutan dan menginput ulang
		$str="delete from ".$dbname1.".detailmonthly
	      where userid=".$userid." and component=".$component."
		  and periode='".$periode."'";
*/
		$str="delete from ".$dbname.".sdm_ho_detailmonthly
	      where karyawanid=".$userid."
		  and periode='".$periode."'";
		try{
			$owlPDO->exec($str); 
			$str="insert into ".$dbname.".sdm_ho_detailmonthly 
			(karyawanid,component,value,periode,plus,updatedby) 
			values(".$userid.",".$component.",".$val.",'".$periode."',".$plus.",'".$_SESSION['standard']['username']."')";	
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			}
		}catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
			die();
		} 
		

	}
	else
	{
	
		$str="select * from ".$dbname.".sdm_ho_detailmonthly
		      where karyawanid=".$userid." and component=".$component."
			  and periode='".$periode."'";  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);	  
		if(owlBaris($res)>0){
	       echo " Double";		
		}
		else
		{		  
			$str="insert into ".$dbname.".sdm_ho_detailmonthly 
			(karyawanid,component,value,periode,plus,updatedby) 
			values(".$userid.",".$component.",".$val.",'".$periode."',".$plus.",'".$_SESSION['standard']['username']."')";	
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
				die();
			}
		}
//write TERBILANG
	
			if($component==1)//insert or update only on gaji pokok
			{
				$stu="select * from ".$dbname.".sdm_ho_payrollterbilang
				where userid=".$userid." and periode='".$periode."'
				and `type`='".$type."'";
				$resu=$owlPDO->query($stu) or die(print " Gagal: ".PDOException::getMessage());
				$resu->setFetchMode(PDO::FETCH_OBJ);	  
				if(owlBaris($resu)>0)
				{ 
					$stre="update ".$dbname.".sdm_ho_payrollterbilang
					set terbilang='".$terbilang."'
					where userid=".$userid." and periode='".$periode."'
					and `type`='".$type."'";
				}
				else
				{
					$stre="insert into ".$dbname.".sdm_ho_payrollterbilang
					(userid,periode,`type`,terbilang)
					values(".$userid.",'".$periode."','".$type."','".$terbilang."')";
				}
				try{
					$owlPDO->exec($stre); 
				}catch (PDOException $e){
					echo "DB Error : " . $e->getMessage();
					die();
				}
			}	
	}
}
?>
