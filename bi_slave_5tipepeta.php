<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
require_once('lib/nangkoelib.php');
	
$proses=checkPostGet('proses','');
$id=checkPostGet('id','');
$tipe=checkPostGet('tipe','');
$tipedokumen=checkPostGet('tipedokumen','');
$tipefeature=checkPostGet('tipefeature','');
$deskripsi=checkPostGet('deskripsi','');
	
switch($proses){
	case 'loaddata':
		$tab = "";
		
		$str = "select * from ".$dbname.".bi_5tipepeta order by tipekelompok ASC";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res);
		
		if($numrows == 0){
			$tab .= $_SESSION['lang']['datanotfound'];
		}else{
			$tab .= "<table class=sortable cellspacing=1 cellpadding=3 border=0>
				<thead>
				<tr class=rowheader>
					<td style='text-align:center'>".$_SESSION['lang']['nourut']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['id']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['tipe']."</td>
					<!-- <td style='text-align:center'>".$_SESSION['lang']['deskripsi']."</td>  --!>
					<!-- <td style='text-align:center'>Tipe Feature</td> --!>
					<td style='text-align:center' colspan=2>".$_SESSION['lang']['action']."</td>
				</tr>
				</thead>
				<tbody>";
			$no = 0;
			$arrTipePeta = array('0'=>'Dasar','1'=>'PT','2'=>'Kegiatan','3'=>'Laporan');
			while($bar = $res->fetch()){
				if($bar['tipekelompok'] == '2'){
					$optTipeDok = makeOption($dbname,'bi_5tipedok','id_tipedok,nama_tipe',"id_tipedok='".$bar['keterangan']."'");
					$hKeterangan = $optTipeDok[$bar['keterangan']];
				}else{
					$hKeterangan = $bar['keterangan'];
				}
				$no++;
				$tab .= "<tr class=rowcontent>
					<td style='text-align:right'>".$no."</td>
					<td>".$bar['id_tipepeta']."</td>
					<!-- <td>".$arrTipePeta[$bar['tipekelompok']]."</td> --!>
					<td>".$hKeterangan."</td>
					<!-- <td>".$bar['tipefeature']."</td> --!>
					<td style='text-align:center'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar['id_tipepeta']."','".$bar['tipekelompok']."','".$bar['keterangan']."','".$bar['tipefeature']."')\">
					</td>
					<!-- <td style='text-align:center'>
						<img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$bar['id_tipepeta']."')\"> --!>
					</td>
				</tr>";
			}
			
			$tab .= "</tbody>
			</table>";
		}
		
		echo $tab;
	break;
	
	case 'insert':
		if(($tipe == '0' || $tipe == '1' || $tipe == '3') && $deskripsi==''){
			echo "warning : Please complete this form.";
			exit();
		}
		
		$str = "insert into ".$dbname.".bi_5tipepeta (id_tipepeta,tipekelompok,keterangan,tipefeature) values ('".getID()."','".$tipe."','".($tipe == '2' ? $tipedokumen : $deskripsi)."','".$tipefeature."')";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
		
	case 'update':
		if(($tipe == '0' || $tipe == '1' || $tipe == '3') && $deskripsi==''){
			echo "warning : Please complete this form.";
			exit();
		}
		
		$str = "update ".$dbname.".bi_5tipepeta set tipekelompok = '".$tipe."', keterangan = '".($tipe == '2' ? $tipedokumen : $deskripsi)."', tipefeature = '".$tipefeature."' where id_tipepeta = '".$id."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".bi_5tipepeta where id_tipepeta='".$id."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	default:
	break;	
}

function getID(){
	global $conn;
	global $dbname;
	global $owlPDO;
	
	$str = "select id_tipepeta from ".$dbname.".bi_5tipepeta order by id_tipepeta DESC LIMIT 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$numrow=owlBaris($res);
	
	if($numrow == 0){
		$idno = "MAP001";
	}else{
		$bar = $res->fetch();
		$expNo = explode('MAP',$bar['id_tipepeta']);
		$idno = 'MAP'.addZero(intval($expNo[1]) + 1,3);
	}
	
	return $idno;
}
?>