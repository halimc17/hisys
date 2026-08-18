<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$proses=checkPostGet('proses','');
$kelaslahan=checkPostGet('kelaslahan','');
$tahuntanam=checkPostGet('tahuntanam','');
$nilai=checkPostGet('nilai','');
$periode1=checkPostGet('periode1','');
$periode2=checkPostGet('periode2','');
$pages=checkPostGet('page','0');

switch($proses)
{
	case'loaddata':
		loadlist();
	break;
	
	case'insert':
		if($nilai=='' || $periode1 == '' || $periode2 == '' || $kelaslahan == '')
		{
			echo"warning : Semua field harus diisi.";
			exit();
		}

		$str="select * from ".$dbname.".kebun_5tandanhathn where kodelahan='".$kelaslahan."' and tahuntanam='".$tahuntanam."' and periode1 = '".$periode1."' and periode2 = '".$periode2."'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=owlBaris($qry);
		
		if($numrows>0){
			echo "warning : Gagal. Item ini sudah pernah terdaftar sebelumnya.";
			exit();
		}else{
			$strIns="insert into ".$dbname.".kebun_5tandanhathn (periode1,periode2,kodelahan,tahuntanam,nilai) values ('".$periode1."','".$periode2."','".$kelaslahan."','".$tahuntanam."','".$nilai."')";
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
		$strEdt="update ".$dbname.".kebun_5tandanhathn set nilai='".$nilai."' where periode1 = '".$periode1."' and  periode2 = '".$periode2."' and kodelahan='".$kelaslahan."' and tahuntanam='".$tahuntanam."'";
		try{
			$owlPDO->exec($strEdt); 
			loadlist();
		}catch (PDOException $e){
			echo"Gagal:Db Error".$strEdt."__".$e->getMessage();
			die();
		}	
	break;
	
	case'delete':
		$str="delete from ".$dbname.".kebun_5tandanhathn where periode1 = '".$periode1."' and periode2 = '".$periode2."' and kodelahan='".$kelaslahan."' and tahuntanam='".$tahuntanam."' and nilai='".$nilai."'";
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
		<table class=sortable cellspacing=1 cellpadding=5 border=0 width=100%>
			<thead>
			<tr class=rowheader>
			   <td style='text-align:center;'>".$_SESSION['lang']['nourut']."</td>
			   <td style='text-align:center;'>".$_SESSION['lang']['periode']." Awal </td>
			   <td style='text-align:center;'>".$_SESSION['lang']['periode']." Akhir </td>
			   <td style='text-align:center;'>".$_SESSION['lang']['kelaslahan']."</td>
			   <td style='text-align:center;'>".$_SESSION['lang']['tahuntanam']."</td>
			   <td style='text-align:center;'>".$_SESSION['lang']['nilai']."</td>
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
	
	$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5tandanhathn";
	$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
	}

    $nmkelaslahan = makeOption($dbname,"setup_kelaslahan","kode,nama");
	
	$strList="select * from ".$dbname.".kebun_5tandanhathn order by kodelahan asc, tahuntanam asc limit ".$offset.",".$limit."";
	$qrtList=$owlPDO->query($strList) or die(print " Gagal: ".PDOException::getMessage());
	$qrtList->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($rowList=$qrtList->fetch()){
		$no+=1;
		echo"<tr class='rowcontent'>
				<td>".$no."</td>
				<td style='text-align:center;'>".$rowList->periode1."</td>
				<td style='text-align:center;'>".$rowList->periode2."</td>
				<td>".$rowList->kodelahan." - ".$nmkelaslahan[$rowList->kodelahan]."</td>
				<td style='text-align:center;'>".$rowList->tahuntanam."</td>
				<td style='text-align:right;'>".number_format($rowList->nilai,2)."</td>
				<td style='text-align:center;'><img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"fillfield('".$rowList->periode1."','".$rowList->periode2."','".$rowList->kodelahan."','".$rowList->tahuntanam."','".$rowList->nilai."')\"></td>
				<td style='text-align:center;'><img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletefield('".$rowList->periode1."','".$rowList->periode2."','".$rowList->kodelahan."','".$rowList->tahuntanam."','".$rowList->nilai."')\"></td>
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