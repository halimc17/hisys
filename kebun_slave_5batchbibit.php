<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$proses=checkPostGet('proses','');
$kode=checkPostGet('kode','');
$nama=checkPostGet('nama','');
$pages=checkPostGet('page','0');

switch($proses)
{
	case'loaddata':
		loadlist();
	break;
	
	case'insert':
		// print_r($_SESSION['standard']);
		if($kode=='' || $nama=='')
		{
			echo"warning : Semua field harus diisi.";
			exit();
		}

		$str="select * from ".$dbname.".kebun_5batchbibit where kode='".$kode."'";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numrows=owlBaris($qry);
		
		if($numrows>0){
			echo "warning : Gagal. Item ini sudah pernah terdaftar sebelumnya.";
			exit();
		}else{
			$strIns="insert into ".$dbname.".kebun_5batchbibit (kode,nama) values ('".$kode."','".$nama."')";
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
		$strEdt="update ".$dbname.".kebun_5batchbibit set nama='".$nama."' where kode='".$kode."'";
		try{
			$owlPDO->exec($strEdt); 
			loadlist();
		}catch (PDOException $e){
			echo"Gagal:Db Error".$strEdt."__".$e->getMessage();
			die();
		}	
	break;
	
	case'delete':
		$str="delete from ".$dbname.".kebun_5batchbibit where kode='".$kode."' and nama='".$nama."'";
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
			   <td style='text-align:center;'>".$_SESSION['lang']['kode']."</td>
			   <td style='text-align:center;'>".$_SESSION['lang']['nama']."</td>
			   <td style='text-align:center;'>".$_SESSION['lang']['action']."</td>
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
	
	$ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5batchbibit";
	$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
	}

	$strList="select * from ".$dbname.".kebun_5batchbibit order by kode asc, nama asc limit ".$offset.",".$limit."";
	$qrtList=$owlPDO->query($strList) or die(print " Gagal: ".PDOException::getMessage());
	$qrtList->setFetchMode(PDO::FETCH_OBJ);
	$no=0;
	while($rowList=$qrtList->fetch()){
		$no+=1;
		echo"<tr class='rowcontent'>
				<td style='text-align:center;'>".$no."</td>
				<td style='text-align:center;'>".$rowList->kode."</td>
				<td style='text-align:left;'>".$rowList->nama."</td>";
				echo "<td style='text-align:center;'>";
                    echo "<img src='images/skyblue/edit.png' class='resicon' style='padding:0 5px;' title='Edit' onclick=\"fillfield('".$rowList->kode."','".$rowList->nama."')\">";
                    // echo "<img src='images/skyblue/delete.png' class='resicon' style='padding:0 5px;' title='Delete' onclick=\"deletefield('".$rowList->kode."','".$rowList->nama."')\">";
                echo "</td>";
        echo "</tr>";
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