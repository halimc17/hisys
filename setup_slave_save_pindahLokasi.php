<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$lokasibaru= $_POST['lokasibaru'];
$username  = $_POST['username'];
$method    = $_POST['method'];
$lokasibaru= checkPostGet('lokasibaru','');
$username  = checkPostGet('username','');
$method    = checkPostGet('method','');
$tipe      = checkPostGet('tipe','');
$lokasiasal= checkPostGet('lokasiasal','');
$namauser= checkPostGet('namauser','');

switch($method){
	case'getlokasiawal':
		$str="select * from ".$dbname.".user where namauser = '".$namauser."' order by namauser asc"; #exit("error".$str);
		$res = fetchData($str);
		$optuser="<option value=''></option>";			
		foreach($res as $bar){
			echo $bar['kodeorg'];			
		}
	break;
	default:
		if($tipe=='ybs'){
			$namauser=$_SESSION['standard']['username'];
			$username=$_SESSION['standard']['userid'];
			$lokasiasal=$_SESSION['empl']['lokasitugas'];
		}else{
			$namauser=$namauser;
			$username=$username;
			$lokasiasal=$lokasiasal;
		}
		
		if($lokasiasal==$lokasibaru) exit("Warning: Lokasi Tugas sama dengan lokasi saat ini");

		$str="update ".$dbname.".user set kodeorg='".$lokasibaru."' where karyawanid='".$username."'";	
		try{$owlPDO->exec($str); echo "Updated";}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }	
	break;
}
?>
