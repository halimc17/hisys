<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
require_once('lib/nangkoelib.php');
	
$proses=checkPostGet('proses','');
$tipe=checkPostGet('tipe','');
$deskripsi=checkPostGet('deskripsi','');
$tabel=checkPostGet('tabel','');
$nodok=checkPostGet('nodok','');
$jnskgtn=checkPostGet('jnskgtn','');
$kodeorg=checkPostGet('kodeorg','');
$periode=checkPostGet('periode','');
	
switch($proses){
	case 'loaddata':
		$tab = "";
		
		$str = "select * from ".$dbname.".bi_5tipedok order by id_tipedok ASC";
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
					<td style='text-align:center'>".$_SESSION['lang']['tipe']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['deskripsi']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['tabel']."</td>
					<td style='text-align:center'>Column (".$_SESSION['lang']['nodok'].")</td>
					<td style='text-align:center'>Column (J".$_SESSION['lang']['kegiatan'].")</td>
					<td style='text-align:center'>Column (".$_SESSION['lang']['kodeorg'].")</td>
					<td style='text-align:center'>Column (".$_SESSION['lang']['periode'].")</td>
					<td style='text-align:center' colspan=2>".$_SESSION['lang']['action']."</td>
				</tr>
				</thead>
				<tbody>";
			$no = 0;
			while($bar = $res->fetch()){
				$no++;
				$tab .= "<tr class=rowcontent>
					<td style='text-align:right'>".$no."</td>
					<td>".$bar['id_tipedok']."</td>
					<td>".$bar['nama_tipe']."</td>
					<td>".$bar['tabel']."</td>
					<td>".$bar['nodok']."</td>
					<td>".$bar['jnskgtn']."</td>
					<td>".$bar['kodeorg']."</td>
					<td>".$bar['periode']."</td>
					<td style='text-align:center'>
						<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$bar['id_tipedok']."','".$bar['nama_tipe']."','".$bar['tabel']."','".$bar['nodok']."','".$bar['jnskgtn']."','".$bar['kodeorg']."','".$bar['periode']."')\">
					</td>
					<td style='text-align:center'>
						<img src='images/skyblue/delete.png' class='resicon' title='Edit' onclick=\"deletefield('".$bar['id_tipedok']."')\">
					</td>
				</tr>";
			}
			
			$tab .= "</tbody>
			</table>";
		}
		
		echo $tab;
	break;
	
	case 'insert':
		if($deskripsi=='' || $tabel=='' || $nodok=='' || $jnskgtn=='' || $kodeorg==''){
			echo "warning : Please complete this form.";
			exit();
		}
		
		$str = "insert into ".$dbname.".bi_5tipedok (id_tipedok,nama_tipe,tabel,nodok,jnskgtn,kodeorg,periode) values ('".getID()."','".$deskripsi."','".$tabel."','".$nodok."','".$jnskgtn."','".$kodeorg."','".$periode."')";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
		
	case 'update':
		if($deskripsi==''){
			echo "warning : Please complete this form.";
			exit();
		}
		
		$str = "update ".$dbname.".bi_5tipedok set nama_tipe = '".$deskripsi."', tabel = '".$tabel."', nodok = '".$nodok."', jnskgtn = '".$jnskgtn."', kodeorg = '".$kodeorg."', periode = '".$periode."' where id_tipedok = '".$tipe."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".bi_5tipedok where id_tipedok='".$tipe."'";
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
	break;
	
	case 'getfield':
		$optField = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($tabel == ''){
			
		}else{
			$str="SHOW COLUMNS FROM ".$dbname.".".$tabel."";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_NUM);
			$optField='';
			while($bar=$res->fetch()){
				$optField.="<option value='".$bar[0]."'>".$bar[0]."</option>";
			}
		}
		
		echo $optField;
	break;
	
	default:
	break;	
}

function getID(){
	global $conn;
	global $dbname;
	global $owlPDO;
	
	$str = "select id_tipedok from ".$dbname.".bi_5tipedok order by id_tipedok DESC LIMIT 1";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$numrow=owlBaris($res);
	
	if($numrow == 0){
		$idno = "T001";
	}else{
		$bar = $res->fetch();
		$expNo = explode('T',$bar['id_tipedok']);
		$idno = 'T'.addZero(intval($expNo[1]) + 1,3);
	}
	
	return $idno;
}
?>