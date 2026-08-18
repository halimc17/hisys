<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=checkPostGet('proses','');
$kelaspohon=checkPostGet('kelaspohon','');
$basishari=checkPostGet('basishari','');
$basisbulan=checkPostGet('basisbulan','');
$namakelas=checkPostGet('namakelas','');

switch($proses)
{
	case'loaddata':
		loadlist();
	break;
	
	case'insert':
		if($kelaspohon==''||$namakelas=='')
		{
			echo"warning : Field tidak boleh kosong";
			exit();
		}
		else
		{
			$str="select * from ".$dbname.".kebun_5kelaspohon where kelas='".$kelaspohon."'";
			$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$numrows=owlBaris($qry);
			
			if($numrows>0){
				echo "warning : Gagal. Kelas pohon sudah pernah terdaftar sebelumnya.";
			}else{
				$strIns="insert into ".$dbname.".kebun_5kelaspohon (kelas,basishari,basisbulan,nama) values ('".$kelaspohon."','".$basishari."','".$basisbulan."','".$namakelas."')";
				try{
					$owlPDO->exec($strIns); 
					loadlist();
				}catch (PDOException $e){
					echo"Gagal:Db Error".$strIns."__".$e->getMessage();
					die();
				}
			}
		}
	break;
	
	case'edit':
		$strEdt="update ".$dbname.".kebun_5kelaspohon set nama='".$namakelas."',basishari='".$basishari."',basisbulan='".$basisbulan."' where kelas='".$kelaspohon."'";
		try{
			$owlPDO->exec($strEdt); 
			loadlist();
		}catch (PDOException $e){
			echo"Gagal:Db Error".$strEdt."__".$e->getMessage();
			die();
		}
	break;
	
	case'delete':
		$str="delete from ".$dbname.".kebun_5kelaspohon where kelas='".$kelaspohon."'";
		try{
			$owlPDO->exec($str); 
			loadlist();
		}catch (PDOException $e){
			echo"Gagal:Db Error".$str."__".$e->getMessage();
			die();
		}
	break;
	
	default:
	break;
}

function loadlist(){
	global $owlPDO;
	global $dbname;
	$strList="select * from ".$dbname.".kebun_5kelaspohon";
	$qrtList=$owlPDO->query($strList) or die(print " Gagal: ".PDOException::getMessage());
	$qrtList->setFetchMode(PDO::FETCH_OBJ);
	$nourut=0;
	while($rowList=$qrtList->fetch()){
		$nourut+=1;
		echo"<tr class='rowcontent'>
				<td align=center>".$rowList->kelas."</td>
				<td style='text-align:right;'>".$rowList->basishari."</td>
				<td style='text-align:right;'>".$rowList->basisbulan."</td>
				<td>".$rowList->nama."</td>
				<td style='text-align:center;'><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$rowList->kelas."','".$rowList->basishari."','".$rowList->basisbulan."','".$rowList->nama."')\"></td>
				<td style='text-align:center;'><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$rowList->kelas."')\"></td>
			</tr>";
	}
}
?>