<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');

$arr=Array();	
$arrKey=Array();
$arr[0]=$_POST['single'];
$arr[1]=$_POST['k0'];
$arr[2]=$_POST['k1'];
$arr[3]=$_POST['k2'];
$arr[4]=$_POST['k3'];

	$arrKey[0]='T';
	$arrKey[1]='0';
	$arrKey[2]='1';
	$arrKey[3]='2';
	$arrKey[4]='3';

for($x=0;$x<count($arr);$x++)
{
	$str="select * from ".$dbname.".sdm_ho_pph21_ptkp where id='".$arrKey[$x]."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	if(owlBaris($res)>0)
	{
		$str1="update ".$dbname.".sdm_ho_pph21_ptkp 
		       set `value`=".$arr[$x]."
		       where id='".$arrKey[$x]."'";	   
	}
	else
	{
		$str1="insert into ".$dbname.".sdm_ho_pph21_ptkp(`id`,`value`) 
		       values('".$arrKey[$x]."',".$arr[$x].")";		
	}
	try{
		$owlPDO->exec($str1); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage();
		die();
	}			
}			
?>
