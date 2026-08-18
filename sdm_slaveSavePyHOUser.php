<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$user = checkPostGet('user','');
$type = checkPostGet('type','');

   $stra="insert into ".$dbname.".sdm_ho_payroll_user
			(uname,type) values('".$user."','".$type."')";
	try{
		$owlPDO->exec($stra); 
		
		$str="select * from ".$dbname.".sdm_ho_payroll_user order by uname";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($bar=$res->fetch())
		{
			$no+=1;
			echo "<tr class=rowcontent><td class=fisttd>".$no."</td>
				  <td id='uname".$no."'>".$bar->uname."</td>
				  <td>".$bar->type."</td>	
				  <td align=center><img src=images/close.png  height=11px class=dellicon title=Delete  onclick=\"delPyUser('".$bar->uname."')\"></td>  
				  </tr>";
		}
	}
	catch (PDOException $e){
		echo " Error: ".addslashes($e->getMessage());
		die();
	}
?>
