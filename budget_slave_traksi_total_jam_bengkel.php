<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method    =checkPostGet('method','');
$thnbudget =checkPostGet('thnbudget','');
$kdorg     =checkPostGet('kdorg','');
$kdtrak    =checkPostGet('kdtrak','');
$totjamthn =checkPostGet('totjamthn','');
$totRow    =checkPostGet('totRow','');
$kodeorg   =checkPostGet('kodeorg','');
$kodews    =checkPostGet('kodews','');
$kodetraksi=checkPostGet('kodetraksi','');

$total     =checkPostGet('total','');
$totbrtthn =checkPostGet('totbrtthn','');
$totCol    =checkPostGet('totCol','');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$arrBln=array(
		"1"=>substr($_SESSION['lang']['jan'],0,3),
		"2"=>substr($_SESSION['lang']['peb'],0,3),
		"3"=>substr($_SESSION['lang']['mar'],0,3),
		"4"=>substr($_SESSION['lang']['apr'],0,3),
		"5"=>substr($_SESSION['lang']['mei'],0,3),
		"6"=>substr($_SESSION['lang']['jun'],0,3),
		"7"=>substr($_SESSION['lang']['jul'],0,3),
		"8"=>substr($_SESSION['lang']['agt'],0,3),
		"9"=>substr($_SESSION['lang']['sep'],0,3),
		"10"=>substr($_SESSION['lang']['okt'],0,3),
		"11"=>substr($_SESSION['lang']['nov'],0,3),
		"12"=>substr($_SESSION['lang']['dec'],0,3),
	);

$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
//untuk cek

$where="tahunbudget='".$thnbudget."' and kodetraksi='".$kdorg."' and kodews='".$kdtrak."'";

switch($method){
	//buat ambil data dari input kode traksi  untuk pilihan box kode ws
	case'getws':
		$sOpt="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi WHERE induk in ('".$kdorg."','".substr($kdorg,0,4)."') and tipe='WORKSHOP'";
		$qOpt=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
		$qOpt->setFetchMode(PDO::FETCH_ASSOC);
		$optws="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		while($rOpt=$qOpt->fetch()){
			if($kodews!=''){
				$optws.="<option value=".$rOpt['kodeorganisasi']." ".($rOpt['kodeorganisasi']==$kodews?'selected':'').">".$rOpt['namaorganisasi']."</option>";
			}else{
				$optws.="<option value=".$rOpt['kodeorganisasi'].">".$rOpt['namaorganisasi']."</option>";
			}
		}
		echo $optws;
	break;
	//untuk input tahun budget. jadi tidak ada kesamaan data dalam tahun budgetnya. di pakai untuk validasi simpan form 1
	case'cekHead':

	$thisThn=date("Y");
	$dtK=substr($thnbudget,0,1);
	$dtA=substr($thisThn,0,1);
	if($dtK!=$dtA){
		exit("Error : Budget year required");
	}           
	$sGet="select * from ".$dbname.".bgt_ws_jam where ".$where." ";
	$qCek=$owlPDO->query($sGet) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
	$numrows=owlBaris($qCek);
	$rCek=$numrows;
	if($rCek=='1'){
		exit("Error : Data already exist");
	}
	$sBr=floor($totjamthn/12);
	echo $sBr;
break;

case'saveData':
	for($a=1;$a<=$totRow;$a++){
		if($_POST['arrJam'][$a]==''){
			$_POST['arrJam'][$a]=0;
		}
		$totalSum+=$_POST['arrJam'][$a];
	}
	if($totalSum>$totjamthn){
		exit("Error:Mothly working hour greater than annual working hours");
	}
	$sCek="select distinct * from ".$dbname.".bgt_ws_jam where tahunbudget='".$thnbudget."' and kodetraksi='".$kdorg."' and kodews='".$kdtrak."'";
	$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
	$qCek->setFetchMode(PDO::FETCH_ASSOC);
	$numrows=owlBaris($qCek);
	$rCek=$numrows;
	if($rCek<1){
		$sInsert="insert into ".$dbname.".bgt_ws_jam (tahunbudget, kodetraksi, kodews, jampertahun, updateby, jam01, jam02, jam03, jam04, jam05, jam06, jam07, jam08, jam09, jam10, jam11, jam12)";
		$sInsert.=" values ('".$thnbudget."','".$kdorg."','".$kdtrak."','".$totjamthn."','".$_SESSION['standard']['userid']."',";
		for($a=1;$a<$totRow;$a++){
			$sInsert.="'".$_POST['arrJam'][$a]."',";
			if($a==($totRow-1)){
				$sInsert.="'".$_POST['arrJam'][$a]."')";
			}
		}
		try{$owlPDO->exec($sInsert); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	}else{
		exit("Error : Data already exist");
	}
break;
case'loadData':
	$limit=20;
	$page=0;
	if(isset($_POST['page'])){
		$page=$_POST['page'];
		if($page<0)
		$page=0;
	}
	$offset=$page*$limit;

	if ($kodeorg!='') {
		@$wheresch.=" and left(kodetraksi,4) = '".$kodeorg."'";
	}
	if ($kodews!='') {
		@$wheresch.=" and left(kodews,4) = '".$kodews."'";
	}

	$ql2="select count(*) as jmlhrow from ".$dbname.".bgt_ws_jam where substr(kodetraksi,1,4) in (".getOrgDetail(2).") ";
	$query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
	}
	$totRowDlm=count($arrBln);
	
	$tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=0 width=100%>";
	$tab.="<thead><tr class=rowheader><th width=20>".substr($_SESSION['lang']['nomor'],0,2)."</th>";
	$tab.="<th align=center width=50>".$_SESSION['lang']['budgetyear']."</th>";
	$tab.="<th align=center>".$_SESSION['lang']['traksi']." </th>";
	$tab.="<th align=center>".$_SESSION['lang']['workshop']."</th>";
	$tab.="<th align=center width=70px>".$_SESSION['lang']['totJamThn']."</th>";

	foreach($arrBln as $brs5=>$dtBln5){
		$tab.="<th align=center width=50>".$dtBln5."</th>";
	}
	$tab.="<th align=center colspan=2>".$_SESSION['lang']['action']."</th></tr></thead><tbody>";
	$sList="select * from ".$dbname.".bgt_ws_jam where substr(kodetraksi,1,4) in (".getOrgDetail(2).") ".$wheresch." order by tahunbudget desc limit ".$offset.",".$limit."";
	$qList=$owlPDO->query($sList) or die(print " Gagal: ".PDOException::getMessage());
	$qList->setFetchMode(PDO::FETCH_ASSOC);
	$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	while($rList=  $qList->fetch()){
		$no+=1;
		
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=center>".$rList['tahunbudget']."</td>";
		$tab.="<td align=left>".$rList['kodetraksi']." - ".$nmorg[$rList['kodetraksi']]."</td>";
		$tab.="<td align=left>".$rList['kodews']." - ".$nmorg[$rList['kodews']]."</td>";
		$tab.="<td align='right'>".number_format($rList['jampertahun'])."</td>";
		for($a=1;$a<=$totRowDlm;$a++){
			if(strlen($a)=='1'){
				$b="0".$a;
			}else{
				$b=$a;
			}
			if($rList['jam'.$b]==''){
				$rList['jam'.$b]=0;
			}
			$tab.="<td align='right'>".number_format($rList['jam'.$b],2)."</td>";
		}
		$tab.="<td align='center' width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('".$rList['tahunbudget']."','".$rList['kodetraksi']."','".$rList['kodews']."','".$rList['jampertahun']."');\"></td>";

		$tab.="<td align='center' width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('".$rList['tahunbudget']."','".$rList['kodetraksi']."','".$rList['kodews']."');\"></td>";
		$tab.="</tr>";
	}
	$tab.="</tbody>";
	$tab.="<tfoot>";
	$colspan=$totRowDlm+7;
	$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loadData','');
	$tab.="</tfoot></table>";
	echo $tab;
	break;
	case 'delete':
		$str = "delete from " . $dbname . ".bgt_ws_jam where tahunbudget='".$param['tahunbudget']."' and kodetraksi='".$param['kodetraksi']."' and kodews='".$param['kodews']."'";
		try { $owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die();}
	break;

	case 'update':
		if(($totjamthn==0)||($totjamthn=='')){
			exit("Error : Total working hours required");
		}
		for($a=1;$a<=$totRow;$a++){
			if($_POST['arrJam'][$a]==''){
				$_POST['arrJam'][$a]=0;
			}
			$totalSum+=$_POST['arrJam'][$a];
		}
		if($totalSum>$totjamthn){
			exit("Error : Monthly working hours greater than annually working hours");
		}
		$sUpdate="update ".$dbname.".bgt_ws_jam set jampertahun='".$totjamthn."',updateby='".$_SESSION['standard']['userid']."'";
			for($a=1;$a<=$totRow;$a++){
				if(strlen($a)=='1'){
					$c="0".$a;
				}else{
					$c=$a;
				}
				$sUpdate.=" ,jam".$c."='".$_POST['arrJam'][$a]."'";
			}
	 $sUpdate.=" where  ".$where."";
	 try{$owlPDO->exec($sUpdate); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); }
	break;
	case'getDataEdit':
	$sData="select * from ".$dbname.".bgt_ws_jam where ".$where."";
	$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
	$qData->setFetchMode(PDO::FETCH_ASSOC);
	$rData=$qData->fetch();
	for($r=1;$r<13;$r++){
		if(strlen($r)<2){
			$b="0".$r;
		}else{
			$b=$r;
		}
	 echo $rData['jam'.$b]."###";
	}
break;
default:
}
?>