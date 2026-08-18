<?
require_once('master_validation.php');
require_once('config/connection.php');
//penetration_prev('1',$_SESSION['standard']['username']);
$karyawanid=$_POST['karyawanid'];
$kodeorg=$_POST['kodeorg'];

	$str="update ".$dbname.".user set karyawanid='".$karyawanid."',kodeorg='{$kodeorg}'
	       where namauser='".$_SESSION['standard']['username']."'";
	// if(mysql_query($str))
	// {
	// 	echo "Updated";
	// }
	// else
	// {echo " Gagal,".addslashes(mysql_error($conn));}

	try{$owlPDO->exec($str); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
?>
