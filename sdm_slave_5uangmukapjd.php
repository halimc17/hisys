<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$method=checkPostGet('method','');
$regional=checkPostGet('regional','');
$kodegolongan=checkPostGet('kodegolongan','');
$kode=checkPostGet('kode','');
$jenis=checkPostGet('jenis','');
$sekali=checkPostGet('sekali','0');
$perhari=checkPostGet('perhari','0');
$hariketiga=checkPostGet('hariketiga','0');

switch($method){
	case 'loadData':  
		listData();
	break;
	
	case 'insert':
		$str="select * from ".$dbname.".sdm_5uangmukapjd where regional='".$regional."' and
			kodegolongan='".$kodegolongan."' and jenis = '".$jenis."'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_OBJ);
		if(owlBaris($qry)>0){
			echo "Error: Jenis Uang Muka untuk Regional ".$regional." dan Golongan ".$kodegolongan." sudah pernah terdaftar sebelumnya.";
		}else{
			$strIns="insert into ".$dbname.".sdm_5uangmukapjd (kode,regional,kodegolongan,jenis,sekali,perhari,hariketiga) 
			values ('".countKode()."','".$regional."','".$kodegolongan."','".$jenis."','".$sekali."','".$perhari."','".$hariketiga."')";
			try{$owlPDO->exec($strIns); listData();}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
		}
	break;
	
	case 'update':
		$str="update ".$dbname.".sdm_5uangmukapjd set 
			jenis='".$jenis."',
			sekali='".$sekali."',
			perhari='".$perhari."',
			hariketiga='".$hariketiga."' 
			where kode='".$kode."'";
		try{$owlPDO->exec($str); listData();}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".sdm_5uangmukapjd where kode='".$kode."'";
		try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	
	default:
	break;
}

function listData(){
	global $dbname;
	global $conn;
	global $owlPDO;
	$str="select a.*,b.kodegolongan,b.namagolongan,c.keterangan from ".$dbname.".sdm_5uangmukapjd a 
		left join ".$dbname.".sdm_5golongan b on a.kodegolongan = b.kodegolongan
		left join ".$dbname.".sdm_5jenisbiayapjdinas c on a.jenis = c.id 
		order by a.regional asc";
	$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$qry->setFetchMode(PDO::FETCH_ASSOC);
	
	if(owlBaris($qry)<1){
		echo"<tr class=rowcontent><td colspan='10' style='text-align:center;'>".$_SESSION['lang']['datanotfound']."</td></tr>";
	}else{
		while($row=$qry->fetch()){
			echo"<tr class=rowcontent>
				<td>".$row['kode']."</td>
				<td>".$row['regional']."</td>
				<td>".$row['namagolongan']."</td>
				<td>".$row['keterangan']."</td>
				<td style='text-align:right;'>".number_format($row['sekali'],2)."</td>
				<td style='text-align:right;'>".number_format($row['perhari'],2)."</td>
				<td style='text-align:right;'>".number_format($row['hariketiga'],2)."</td>
				<td><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$row['kode']."','".$row['regional']."','".$row['kodegolongan']."','".$row['jenis']."','".$row['sekali']."','".$row['perhari']."','".$row['hariketiga']."')\"></td>
				<td><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deleteData('".$row['kode']."')\"></td>
			<tr>";
		}
	}
}

function countKode(){
	global $dbname;
	global $conn;
	global $regional;
	global $owlPDO;
	$optReg = makeOption($dbname,'bgt_regional','regional,kodepenerimaankaryawan');
	
	$sCount="select substring(kode,2,3) as kodeku from ".$dbname.".sdm_5uangmukapjd where regional = '".$regional."' order by kode desc limit 1";
	$qCount=$owlPDO->query($sCount) or die(print " Gagal: ".PDOException::getMessage());
	$qCount->setFetchMode(PDO::FETCH_ASSOC);
	$rCount=$qCount->fetch();
	$nextKode = $optReg[$regional]."".addZero($rCount['kodeku'] + 1,3);
	
	return $nextKode;
}

?>