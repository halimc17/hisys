<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('config/connection.php');
// exit('error '.$str);	  

$tahun1=$_POST['tahun1'];
$tahun2=$_POST['tahun2'];
$kdUnit2=$_POST['kdUnit2'];

$str="select a.*,b.lokasitugas,b.tanggalkeluar 
      from ".$dbname.".sdm_5gajipokokho a  left join 
      ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
      where a.tahun=".$tahun1." and b.lokasitugas='".$kdUnit2."'
      and (b.tanggalkeluar='0000-00-00' or b.tanggalkeluar>='".$tahun2."-01-01' or b.tanggalkeluar is null)
      order by karyawanid";
$xxxx=fetchdata($str);
$numrows=count($xxxx); 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
//$numrows=owlBaris($res);

if($numrows<1)
{
	exit("Error: No data on source");
}
else
{
	$str="delete from ".$dbname.".sdm_5gajipokokho where karyawanid in(
		  select karyawanid from ".$dbname.".datakaryawan 
		  where lokasitugas='".$kdUnit2."')
		  and tahun=".$tahun2;
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		
	}
	while($bar=$res->fetch())
	{
		$str1="insert into ".$dbname.".sdm_5gajipokokho (tahun,karyawanid,idkomponen,jumlah)
			  values(".$tahun2.",".$bar->karyawanid.",".$bar->idkomponen.",".$bar->jumlah.");";
		try{
			$owlPDO->exec($str1); 
		}
		catch (PDOException $e){
			exit("Error: ".$e->getMessage()); 
		}
	}	
}
?>