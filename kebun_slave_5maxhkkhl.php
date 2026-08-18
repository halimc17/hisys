<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kodeorg=checkPostGet('kodeorg','');
$stts   =checkPostGet('stts','');
$nilai  =checkPostGet('nilai','');
$method =checkPostGet('method','');
$jenis =checkPostGet('jenis','');
$kecuali =checkPostGet('kecuali','');
$tanggalberlaku =tanggalsystemn(checkPostGet('tanggalberlaku',''));



switch($method){
	case 'loaddata':
		getContainer();
	break;
	
	case 'insert':
		if($kodeorg==''||$stts==''||$nilai=='' || $tanggalberlaku=='--'){
			echo "Gagal : Semua field harus diisi.";
			exit();
		}
		$str="select * from ".$dbname.".kebun_5maxhkkarykhl where kodeorg='".$kodeorg."'";
		$res = fetchdata($str);
		if(count($res)>0){
			echo "Error : Data sudah ada.";
		}else{
			$str="INSERT INTO ".$dbname.".kebun_5maxhkkarykhl (kodeorg,nilai,status,updateby,lastupdate,tanggalberlaku,jenis,excludejabatan) 
			VALUES ('".$kodeorg."','".$nilai."','".$stts."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."','".$tanggalberlaku."','".$jenis."','".$kecuali."')";
			try{
				$owlPDO->exec($str); 
				getContainer();
			}catch (PDOException $e){
				echo "DB Error : ".$e->getMessage();
				die();
			}
		}
	break;
		
	case 'edit':
		if($kodeorg==''||$stts==''||$nilai=='' || $tanggalberlaku=='--'){
			echo "Gagal : Semua field harus diisi.";
			exit();
		}
		$str="UPDATE ".$dbname.".kebun_5maxhkkarykhl SET 
				nilai='".$nilai."', 
				status='".$stts."',
				tanggalberlaku='".$tanggalberlaku."',
				jenis='".$jenis."',
				excludejabatan='".$kecuali."',
				updateby='" . $_SESSION['standard']['userid'] . "',
				lastupdate='" .date('Y-m-d H:i:s'). "' WHERE 
				kodeorg='".$kodeorg."'";
		try{
			$owlPDO->exec($str); 
			getContainer();
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}
	break;
	
	case 'delete':
		$str="delete from ".$dbname.".kebun_5maxhkkarykhl where kodeorg='".$kodeorg."'";
		try{
			$owlPDO->exec($str); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}
	break;
	
	default:
	break;	
}

function getContainer(){
	global $owlPDO;
	global $dbname;
	
	$arrsts=array('1'=>'Aktif','0'=>'Non Aktif');
	$arrjns=array('hk'=>'HK','hadir'=>'Kehadiran');
	$no=0;
	$str="select * from ".$dbname.".kebun_5maxhkkarykhl";
	$bar = fetchdata($str);
	foreach($bar as $res){
		$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$res['kodeorg']."'");
		$kecuali = $noj ="";
		if($res['excludejabatan']!=''){			
			$kec = explode(",",$res['excludejabatan']);
			foreach($kec as $k){
				$noj++;
				$kecuali.=$noj.". ".getNamaJabatan($k)."<br>"; 
			}
		}
		
		
		$no+=1;
		echo"<tr class=rowcontent>
				<td style='text-align:center;'>".$no."</td>
				<td>".$res['kodeorg']." - ".$nmorg[$res['kodeorg']]."</td>
				<td>".$arrjns[$res['jenis']]."</td>
				<td>".$kecuali."</td>
				<td style='text-align:right;'>".$res['nilai']."</td>
				<td align=center>".tanggalnormal($res['tanggalberlaku'])."</td>
				<td>".$arrsts[$res['status']]."</td>
				<td>".getNamaKaryawan($res['updateby'])."</td>
				<td>".tanggalnormal($res['lastupdate'])."</td>
				<td style='text-align:center;width:25px'><img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('".$res['kodeorg']."','".$res['nilai']."','".$res['status']."','".tanggalnormal($res['tanggalberlaku'])."','".$res['jenis']."','".$res['excludejabatan']."')\"></td>
				<td style='text-align:center;width:25px'><img src='images/skyblue/delete.png' class='zImgBtn' title='Edit' onclick=\"deletefield('".$res['kodeorg']."')\"></td>
			</tr>";
	}
}
?>