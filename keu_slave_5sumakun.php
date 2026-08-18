<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$noakun=checkPostGet('noakun','');
$namaakun=checkPostGet('namaakun','');

$pages=checkPostGet('page','0');

switch($proses)
{
	case'loaddata':
		loadlist();
	break;
	
	case'insert':
	
			$strIns="insert into ".$dbname.".keu_5sumakun (noakun) values ('".$noakun."')";
			//exit('error'.$strIns);
			try{
				$owlPDO->exec($strIns); 
				loadlist();
			}catch (PDOException $e){
				echo"Gagal:Db Error".$strIns."__".$e->getMessage();
				die();
			}
		
	break;
	
	
	
	case'delete':
		$str="delete from ".$dbname.".keu_5sumakun where noakun='".$noakun."'";
		//exit('error'.$str);
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
	global $noakun;
	global $namaakun;
	
	
	echo"<div id=container>
		<table class=sortable cellspacing=1 cellpadding=3 border=0 width=100%>
			<thead>
			<tr class=rowheader>
			   <td align=center>".$_SESSION['lang']['noakun']."</td>
			   <td align=center>".$_SESSION['lang']['namaakun']."</td>
			   <td style='text-align:center;'>".$_SESSION['lang']['action']."</td>
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
	
	if($noakun!=''){
		$where= " and a.noakun like '".$noakun."%' ";
	}
	if($namaakun!=''){
		$where.= " and b.namaakun like '%".$namaakun."%' ";
	}
	$ql2="select count(*) as jmlhrow from  ".$dbname.".keu_5sumakun a left join ".$dbname.".keu_5akun b on a.noakun=b.noakun where 1=1 
			".$where;
	$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
	}
	
	$strList="select * from ".$dbname.".keu_5sumakun a left join ".$dbname.".keu_5akun b on a.noakun=b.noakun where 1=1 
			".$where." limit ".$offset.",".$limit."";
	//echo $strList;
	//exit('Error : '.$strList);
	$qrtList=$owlPDO->query($strList) or die(print " Gagal: ".PDOException::getMessage());
	$qrtList->setFetchMode(PDO::FETCH_OBJ);
	$no=$maxdisplay;
	while($rowList=$qrtList->fetch()){
		$no+=1;
		echo"<tr class='rowcontent'>
				<td>".$rowList->noakun."</td>
				<td>".$rowList->namaakun."</td>
				
			
				
				<td style='text-align:center;'><img src='images/skyblue/delete.png' class='resicon' title='Delete' onclick=\"deletefield('".$rowList->noakun."')\"></td>
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