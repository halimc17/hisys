<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$karyawanid = checkPostGet('karyawanid','');
$pt = checkPostGet('pt','');
$lokasitugas = checkPostGet('lokasitugas','');

$str="update ".$dbname.".datakaryawan set kodeorganisasi='".$pt."', subbagian='', lokasitugas='".$lokasitugas."' where karyawanid=".$karyawanid;
try{
	$owlPDO->exec($str); 
}catch (PDOException $e){
	echo " Gagal: ".$e->getMessage();
	die();
}
?>