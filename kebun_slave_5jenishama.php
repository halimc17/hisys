<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

$method = checkPostGet('method','');
$kodehama = checkPostGet('kodehama','');
$namahama = checkPostGet('namahama','');
$satuan = checkPostGet('satuan','');
	
switch($method){
	case 'loaddata':
		getContainer();
	break;
		
	case 'insert':
		if($kodehama == '' || $namahama == ''){
			echo "Gagal : Kode dan nama hama harus diisi.";
			exit();
		}
		if($satuan == ''){
			echo "Gagal : Satuan harus dipilih.";
			exit();
		}
		
		$str = "select * from ".$dbname.".kebun_5jenishama where kodehama = '".$kodehama."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
		if($numrows>=1){
			echo "Error: Kode hama sudah pernah terdaftar sebelumnya.";
		}else{
			$str = "insert into ".$dbname.".kebun_5jenishama (kodehama,namahama,satuan) 
			values ('".$kodehama."','".$namahama."','".$satuan."')";
			try{
				$owlPDO->exec($str); 
			}catch (PDOException $e){
				echo "Gagal : ".$e->getMessage();
			}
		}
	break;
			
	case 'update':
		if($namahama == ''){
			echo "Gagal : Nama hama harus diisi.";
			exit();
		}
		
		if($satuan == ''){
			echo "Gagal : Satuan harus dipilih.";
			exit();
		}
		$str="update ".$dbname.".kebun_5jenishama set namahama='".$namahama."', satuan='".$satuan."' where kodehama='".$kodehama."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
		}
	break;
			
	default:
		break;	
	}
	
function getContainer(){
	global $conn;
	global $dbname;
	global $owlPDO;
	
	$str="select * from ".$dbname.".kebun_5jenishama order by namahama";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$no = 0;
	while($bar = $res->fetch()){
		$no++;
		echo"<tr class=rowcontent>
			<td style='text-align:right;'>".$no."</td>
			<td>".$bar['kodehama']."</td>
			<td>".$bar['namahama']."</td>
			<td>".$bar['satuan']."</td>
			<td colspan=2 style='text-align:center'>
				<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar['kodehama']."','".$bar['namahama']."','".$bar['satuan']."')\">
			</td>
		</tr>";
	}
}
?>