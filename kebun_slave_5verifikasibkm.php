<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$karyawanid=checkPostGet('karyawanid','');
$status=checkPostGet('status','');
$pages=checkPostGet('page','0');

switch($proses)
{
	case'loaddata':
		loadlist();
	break;
	
	case'insert':
		// print_r($_SESSION['standard']);
		if($unit == "" || $karyawanid == "")
		{
			// echo"warning : Semua field harus diisi.";
			exit("Warning : Semua field harus diisi.");
		}

		$str="select * from ".$dbname.".kebun_5verifikasibkm where kodeorg='".$unit."' and karyawanid='".$karyawanid."'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=owlBaris($qry);
		
		if($numrows>0){
			// echo "warning : Gagal. Item ini sudah pernah terdaftar sebelumnya.";
			exit("Warning : Gagal. Item ini sudah pernah terdaftar sebelumnya.");
		}else{
			$strIns="insert into ".$dbname.".kebun_5verifikasibkm (`kodeorg`,`karyawanid`,`status`,`createby`,`createtime`,`updateby`,`updatetime`) 
            values ('".$unit."','".$karyawanid."','".$status."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."',
            '".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."')";
			try{
				$owlPDO->exec($strIns); 
				loadlist();
			}catch (PDOException $e){
				echo"Gagal:Db Error".$strIns."__".$e->getMessage();
				die();
			}
		}
	break;
	
	case'edit':
		$strEdt="update ".$dbname.".kebun_5verifikasibkm set status='".$status."', updateby='".$_SESSION['standard']['userid']."', updatetime='".date("Y-m-d H:i:s")."'
        
        where kodeorg='".$unit."' and karyawanid='".$karyawanid."'";
		try{
			$owlPDO->exec($strEdt); 
			loadlist();
		}catch (PDOException $e){
			echo"Gagal:Db Error".$strEdt."__".$e->getMessage();
			die();
		}	
	break;
	
	case'delete':
		$str="delete from ".$dbname.".kebun_5verifikasibkm where kodeorg='".$unit."' and karyawanid='".$karyawanid."'";
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
	global $pages;
	
	echo"<div id=container>
		<table class=sortable cellspacing=1 cellpadding=3 border=0 width=100%>
			<thead>
			<tr class=rowheader>
			   <td style='text-align:center;'>".$_SESSION['lang']['nourut']."</td>
			   <td style='text-align:center;'>".$_SESSION['lang']['unit']."</td>
			   <td style='text-align:center;'>".$_SESSION['lang']['namakaryawan']."</td>
			   <td style='text-align:center;'>".$_SESSION['lang']['status']."</td>
			   <td colspan='2' style='text-align:center;'>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
	
	$limit=15;
	$page=0;
	if(isset($pages)){
		$page=$pages;
		if($page<0){
			$page=0;
		}
	}
	// print_r($pages);
	$offset=$page*$limit;
	
	$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5verifikasibkm";
	$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
	}

    $nmKaryawan = makeOption($dbname,"datakaryawan","karyawanid,namakaryawan");
    $nikKaryawan = makeOption($dbname,"datakaryawan","karyawanid,nik");
    $arrstatus = array ("0"=>"Tidak aktif","1"=>"Aktif");
	
	$strList="select * from ".$dbname.".kebun_5verifikasibkm order by kodeorg asc, karyawanid asc limit ".$offset.",".$limit."";
	$qrtList=$owlPDO->query($strList) or die(print " Gagal: ".PDOException::getMessage());
	$qrtList->setFetchMode(PDO::FETCH_ASSOC);
	$no=0;
	while($rowList=$qrtList->fetch()){
		$no+=1;
		echo"<tr class='rowcontent'>
				<td>".$no."</td>
				<td style='text-align:center;'>".$rowList['kodeorg']."</td>
				<td>".$nikKaryawan[$rowList['karyawanid']]." - ".$nmKaryawan[$rowList['karyawanid']]."</td>
				<td style='text-align:center;'>".$arrstatus[$rowList['status']]."</td>
				<td style='text-align:center;'><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$rowList['kodeorg']."','".$rowList['karyawanid']."','".$rowList['status']."')\"></td>
				<td style='text-align:center;'><img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletefield('".$rowList['kodeorg']."','".$rowList['karyawanid']."')\"></td>
			</tr>";
	}
	echo"<tr class=rowheader><td colspan=6 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo"</tbody></table></div>";
}
?>