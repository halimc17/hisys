<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$regional=checkPostGet('regional','');
$status=checkPostGet('status','');
$periode=checkPostGet('periode','');
$hargasatuan=checkPostGet('hargasatuan','');
$pages=checkPostGet('page','0');

switch($proses)
{
	case'loaddata':
		loadlist();
	break;
	
	case'insert':
		// print_r($_SESSION['standard']);
		if($periode==''||$hargasatuan=='' || $hargasatuan=='0')
		{
			echo"warning : Semua field harus diisi.";
			exit();
		}
		// echo "warning : ".substr($periode,4,1);
		if(substr($periode,4,1) != '-' || !is_numeric(substr($periode,0,4)) || !is_numeric(substr($periode,5,2)) || substr($periode,5,2) > 12 || strlen(substr($periode,5,2)) != 2){
			echo"warning : Periksa kembali format periode.";
			exit();
		}
		$str="select * from ".$dbname.".kebun_5hargabibit where regional='".$regional."' and status='".$status."' and periode='".$periode."'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=owlBaris($qry);
		
		if($numrows>0){
			echo "warning : Gagal. Item ini sudah pernah terdaftar sebelumnya.";
			exit();
		}else{
			$strIns="insert into ".$dbname.".kebun_5hargabibit (regional,status,periode,hargasatuan,updateby) values ('".$regional."','".$status."','".$periode."','".$hargasatuan."','".$_SESSION['standard']['userid']."')";
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
		$strEdt="update ".$dbname.".kebun_5hargabibit set hargasatuan='".$hargasatuan."' where regional='".$regional."' and status='".$status."' and periode='".$periode."'";
		try{
			$owlPDO->exec($strEdt); 
			loadlist();
		}catch (PDOException $e){
			echo"Gagal:Db Error".$strEdt."__".$e->getMessage();
			die();
		}	
	break;
	
	case'delete':
		$str="delete from ".$dbname.".kebun_5hargabibit where regional='".$regional."' and status='".$status."' and periode='".$periode."'";
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
			   <td>".$_SESSION['lang']['regional']."</td>
			   <td>".$_SESSION['lang']['status']."</td>
			   <td>".$_SESSION['lang']['periode']."</td>
			   <td>".$_SESSION['lang']['hargasatuan']."</td>
			   <td colspan='2' style='text-align:center;'>".$_SESSION['lang']['action']."</td>
			</tr>
			</thead>
			<tbody>";
	
	$limit=12;
	$page=0;
	if(isset($pages)){
		$page=$pages;
		if($page<0){
			$page=0;
		}
	}
	// print_r($pages);
	$offset=$page*$limit;
	$maxdisplay=($page*$limit);
	
	$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5hargabibit";
	$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
	}
	
	$strList="select * from ".$dbname.".kebun_5hargabibit order by periode desc limit ".$offset.",".$limit."";
	$qrtList=$owlPDO->query($strList) or die(print " Gagal: ".PDOException::getMessage());
	$qrtList->setFetchMode(PDO::FETCH_OBJ);
	$no=$maxdisplay;
	while($rowList=$qrtList->fetch()){
		$no+=1;
		if($rowList->status == 'I'){
			$hStatus = "Inti";
		}else if($rowList->status == 'P'){
			$hStatus = "Plasma";
		}else{
			$hStatus = "Eksternal";
		}
		echo"<tr class='rowcontent'>
				<td>".$rowList->regional."</td>
				<td>".$hStatus."</td>
				<td>".$rowList->periode."</td>
				<td style='text-align:right;'>".number_format($rowList->hargasatuan,2)."</td>
				<td style='text-align:center;'><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$rowList->regional."','".$rowList->status."','".$rowList->periode."','".$rowList->hargasatuan."')\"></td>
				<td style='text-align:center;'><img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletefield('".$rowList->regional."','".$rowList->status."','".$rowList->periode."')\"></td>
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