<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');

$arr=Array();	
$arrKey=Array();
$arrR[0]=$_POST['r0'];
$arrR[1]=$_POST['r1'];
$arrR[2]=$_POST['r2'];
$arrR[3]=$_POST['r3'];
$arrR[4]=$_POST['r4'];

	$arrP[0]=$_POST['p0'];
	$arrP[1]=$_POST['p1'];
	$arrP[2]=$_POST['p2'];
	$arrP[3]=$_POST['p3'];
	$arrP[4]=$_POST['p4'];


	$arrPn[0]=$_POST['pn0'];
	$arrPn[1]=$_POST['pn1'];
	$arrPn[2]=$_POST['pn2'];
	$arrPn[3]=$_POST['pn3'];
	$arrPn[4]=$_POST['pn4'];

$arrSign[0]=$_POST['s0'];
$arrSign[1]=$_POST['s1'];
$arrSign[2]=$_POST['s2'];
$arrSign[3]=$_POST['s3'];
$arrSign[4]=$_POST['s4'];	

	$arrLevel[0]='A';
	$arrLevel[1]='B';
	$arrLevel[2]='C';
	$arrLevel[3]='D';
	$arrLevel[4]='E';

for($x=0;$x<count($arrP);$x++)
{
	$str="select * from ".$dbname.".sdm_ho_pph21_kontribusi where level='".$arrLevel[$x]."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	if(owlBaris($res)>0){
		$str1="update ".$dbname.".sdm_ho_pph21_kontribusi 
		       set `percent`=".$arrP[$x].",
			   `upto`=".$arrR[$x].",
			   `penambah`=".$arrPn[$x].",
			   `sign`='".$arrSign[$x]."'
		       where level='".$arrLevel[$x]."'";	   
	}
	else
	{
		$str1="insert into ".$dbname.".sdm_ho_pph21_kontribusi(`level`,`percent`,`upto`,`sign`,`penambah`) 
		       values('".$arrLevel[$x]."',".$arrP[$x].",".$arrR[$x].",'".$arrSign[$x]."','".$arrPn[$x]."')";		
	}
	try{
		$owlPDO->exec($str1); 
	}catch (PDOException $e){
		echo "DB Error : " . $e->getMessage();
		die();
	}		
}			
?>
