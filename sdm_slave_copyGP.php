<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('config/connection.php');

$tahun1=$_POST['tahun1'];
$tahun2=$_POST['tahun2'];
$kdUnit2=$_POST['kdUnit2'];

$str="select a.*,b.lokasitugas,b.tanggalkeluar 
      from ".$dbname.".sdm_5gajipokok a  left join 
      ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid
      where a.tahun='".$tahun1."' and b.lokasitugas='".$kdUnit2."'
       and b.statuskaryawan != 'Keluar' and (b.tanggalkeluar='0000-00-00' or b.tanggalkeluar>='".$tahun2."-01-01' or b.tanggalkeluar is null)
      order by karyawanid";
	  
$xxxx=fetchdata($str);
$numrows=count($xxxx); 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
//$numrows=owlBaris($res);


if($numrows<1)
{
	exit("Error: Datakaryawan tidak ada...");
}
else
{
	try 
	{
		$owlPDO->beginTransaction();
			$str="delete from ".$dbname.".sdm_5gajipokok where karyawanid in(
				  select karyawanid from ".$dbname.".datakaryawan 
				  where lokasitugas='".$kdUnit2."')
				  and tahun='".$tahun2."'";
			
				$owlPDO->exec($str); 
			
			while($bar=$res->fetch())
			{
				$str1="insert into ".$dbname.".sdm_5gajipokok (tahun,karyawanid,idkomponen,jumlah,kodeorg,updateby) 
				values('".$tahun2."',".$bar->karyawanid.",".$bar->idkomponen.",".$bar->jumlah.",'".$bar->kodeorg."','".$_SESSION['standard']['userid']."');";
				$owlPDO->exec($str1); 
				
			}	
		$owlPDO->commit();
	} catch (PDOException $e) {$owlPDO->rollback();echo "Error, " . addslashes($e->getMessage());die();}

}
?>