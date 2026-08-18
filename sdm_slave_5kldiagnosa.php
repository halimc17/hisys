<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$kodekelompok=checkPostGet('kodekelompok','');
$deskripsi=checkPostGet('deskripsi','');
$method=checkPostGet('method','');

switch($method){
	case 'loadData':  
		listData();
	break;
	
	case 'insert':
		if($kodekelompok==''){
			echo "Gagal : Kode kelompok harus diisi.";
			exit();
		}
		$str="select * from ".$dbname.".sdm_5kldiagnosa where kodekelompok='".$kodekelompok."'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_OBJ);	
		if(owlBaris($qry)>0){
			echo "Error: Kode kelompok sudah pernah terdaftar sebelumnya.";
		}else{
			$strIns="insert into ".$dbname.".sdm_5kldiagnosa (kodekelompok,deskripsi) 
			values ('".$kodekelompok."','".$deskripsi."')";
			echo $strIns;
			exit("Error");
			try{$owlPDO->exec($strIns); listData();}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
	break;
	
	case 'update':
		$str="update ".$dbname.".sdm_5kldiagnosa set deskripsi='".$deskripsi."' where kodekelompok='".$kodekelompok."'";
		try{$owlPDO->exec($str); listData();}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".sdm_5kldiagnosa where kodekelompok='".$kodekelompok."'";
		try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	
	default:
	break;
}

function listData(){
	global $dbname;
	global $conn;
	global $owlPDO;
	$str="select * from ".$dbname.".sdm_5kldiagnosa order by deskripsi";
	$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$qry->setFetchMode(PDO::FETCH_ASSOC);	
		while($row=$qry->fetch()){
			echo"<tr class=rowcontent>
				<td>".$row['kodekelompok']."</td>
				<td>".$row['deskripsi']."</td>
				<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$row['kodekelompok']."','".$row['deskripsi']."')\"></td>
				<td><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deleteData('".$row['kodekelompok']."')\"></td>
			<tr>";
		}
}

?>