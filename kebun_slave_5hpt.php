<?php
	require_once('master_validation.php');
	require_once('config/connection.php');
	require_once('lib/nangkoelib.php');
	require_once('lib/zLib.php');
	
	$method=checkPostGet('method','');
	$kegiatan=checkPostGet('kegiatan','');
	$tipe=checkPostGet('tipe','');

	switch($method){
		case 'loaddata':
			getContainer();
		break;
		
		case 'simpan':
			if($kegiatan==''){
				echo "Warning : ".$_SESSION['lang']['kegiatan']." required";
				exit();
			}
			
			$str = "select * from ".$dbname.".kebun_5hpt where kodekegiatan='".$kegiatan."' and tipe='".$tipe."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$numrows=owlBaris($res);
			if($numrows >= 1){
				echo "Error: Kode kegiatan dan tipe sudah pernah terdaftar sebelumnya.";
			}else{
				$str = "insert into ".$dbname.".kebun_5hpt (kodekegiatan,tipe) values ('".$kegiatan."','".$tipe."')";
				try{
					$owlPDO->exec($str); 
					getContainer();
				}catch (PDOException $e){
					echo "Gagal : ".$e->getMessage();
				}
			}
		break;
		
		case 'delete':
			$str="delete from ".$dbname.".kebun_5hpt where kodekegiatan='".$kegiatan."' and tipe='".$tipe."'";
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
		
		$str="select * from ".$dbname.".kebun_5hpt order by tipe asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = 0;
		while($bar=$res->fetch())
		{
			$wherex="kodekegiatan='".$bar['kodekegiatan']."'";
			$optNamaKegiatan = makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',$wherex);
			$no++;
			echo"<tr class=rowcontent>
				<td style='text-align:right;'>".$no."</td>
				<td>".$bar['kodekegiatan']."</td>
				<td>".$optNamaKegiatan[$bar['kodekegiatan']]."</td>
				<td style='text-align:left'>".($bar['tipe'] == 's' ? "Sensus" : "Penanggulangan")."</td>
				<td colspan=2 style='text-align:center'><img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"delData('".$bar['kodekegiatan']."','".$bar['tipe']."')\"></td>
			</tr>";
		}
	}

?>