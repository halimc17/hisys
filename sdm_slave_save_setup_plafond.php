<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$regional = checkPostGet('regional','');
$kodegolongan = checkPostGet('kodegolongan','');
$rupiah = checkPostGet('rupiah','');
$satuan = checkPostGet('satuan','');
$method = checkPostGet('method','');
$jenisbiaya = checkPostGet('jenisbiaya','');

switch($method)
{
case 'update':	
	$str="update ".$dbname.".sdm_pengobatanplafond set rupiah='".$rupiah."', satuan='".$satuan."'  
	       where regional='".$regional."' and kodegolongan='".$kodegolongan."' 
		   and kodejenisbiaya='".$jenisbiaya."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
	break;
case 'insert':
	$sCount="select * from ".$dbname.".sdm_pengobatanplafond where regional='".$regional."' and kodegolongan='".$kodegolongan."' and kodejenisbiaya='".$jenisbiaya."'";
	$res=$owlPDO->query($sCount) or die(print " Gagal: ".PDOException::getMessage());
	$numrows=owlBaris($res);
	if($numrows >= 1){
		echo " Gagal, Item ini sudah pernah terdaftar sebelumnya.";
		exit(0);
	}else{
		$str="insert into ".$dbname.".sdm_pengobatanplafond (regional,kodegolongan,rupiah,kodejenisbiaya,satuan)
			  values('".$regional."','".$kodegolongan."','".$rupiah."','".$jenisbiaya."','".$satuan."')";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal,".addslashes($e->getMessage());
			die();
		}
	}	
	break;
case 'delete':
	$str="delete from ".$dbname.".sdm_pengobatanplafond 
	where kodegolongan='".$kodegolongan."' and kodejenisbiaya='".$jenisbiaya."'";
	try{
		$owlPDO->exec($str); 
	}
	catch (PDOException $e){
		echo " Gagal,".addslashes($e->getMessage());
		die();
	}
	break;
default:
   break;					
}
$str1="select * from ".$dbname.".sdm_pengobatanplafond order by regional asc, kodegolongan asc, kodejenisbiaya asc";
$vGolognan=makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');
$vJenisBiaya=makeOption($dbname,'sdm_5jenisbiayapengobatan','kode,nama');
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);

while($bar1=$res1->fetch())
{
		if($bar1->satuan==1){
			$hVal='per tahun';
		}else if($bar1->satuan==2){
			$hVal='per hari';
		}else if($bar1->satuan==3){
			$hVal='1 tahun sekali';
		}else{
			$hVal='3 tahun sekali';
		}
		echo"<tr class=rowcontent>
			<td>".$bar1->regional."</td>
			<td>".$vGolognan[$bar1->kodegolongan]." - ".$bar1->kodegolongan."</td>
			<td>".$vJenisBiaya[$bar1->kodejenisbiaya]."</td>
			<td>".$hVal."</td>
			<td  align=right>".number_format($bar1->rupiah,2)."</td>
			<td><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('".$bar1->regional."','".$bar1->kodegolongan."','".$bar1->rupiah."','".$bar1->kodejenisbiaya."','".$bar1->satuan."');\"></td></tr>";
}	 
?>