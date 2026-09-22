<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/zForm.php');
require_once('lib/zTable.php');

$kodeunit	=checkPostGet('kodeunit','%%');
$periode	=checkPostGet('periode','%%');

$myUnit = getOrgDetail(2);

if($kodeunit=='%%'){
	$cond = "kodeunit in (".$myUnit.")";
}else{
	$cond = "kodeunit='".$kodeunit."' and kodeunit in (".$myUnit.")";
}
if($periode!='%%' && $periode!=''){
	$cond .= " and periode='".$periode."'";
}

$optUnitFilter = array('%%'=>$_SESSION['lang']['all']);
$strUnitFilter = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (".$myUnit.") and tipe in ('KEBUN','PABRIK') order by namaorganisasi";
$resUnitFilter = fetchData($strUnitFilter);
foreach($resUnitFilter as $bar){
	$optUnitFilter[$bar['kodeorganisasi']] = $bar['kodeorganisasi']." - ".$bar['namaorganisasi'];
}

$optPerFilter = array();
$strPerFilter = "select distinct periode from ".$dbname.".setup_periodeakuntansi where kodeorg in (".$myUnit.") order by periode desc";
$resPerFilter = fetchData($strPerFilter);
foreach($resPerFilter as $bar){
	$optPerFilter[$bar['periode']] = $bar['periode'];
}
if($optPerFilter==array()){
	$optPerFilter = array('%%'=>$_SESSION['lang']['all']);
}

$freezeFieldEdit = "kodeorg##kodeunit##kodekelompok##periode##keterangan";

$filterRow = "<div style='padding:6px 0 10px 0'>";
$filterRow .= makeElement('kodeunitfilter','label',$_SESSION['lang']['unit'].'&nbsp;');
$filterRow .= makeElement('kodeunitfilter','select',$kodeunit,array('style'=>'width:220px;margin-right:15px'),$optUnitFilter);
$filterRow .= makeElement('periodefilter','label',$_SESSION['lang']['periode'].'&nbsp;');
$filterRow .= makeElement('periodefilter','select',$periode,array('style'=>'width:100px;margin-right:15px'),$optPerFilter);
$filterRow .= makeElement('btnFilterKelJurnal','button',$_SESSION['lang']['preview'],array('onclick'=>'filterKelJurnal()'));
$filterRow .= "</div>";

$tableHtml = masterTable($dbname,'keu_5kelompokjurnal',"*",array(),array(),$cond,array(),null,$freezeFieldEdit);
$tableHtml = str_replace("<table id='masterTable'",$filterRow."<table id='masterTable'",$tableHtml);
$tableHtml = preg_replace("/<td><img id='delRow\d+'.*?<\/td>/s",'',$tableHtml);
$tableHtml = str_replace("<td colspan='2'>".$_SESSION['lang']['action']."</td>","<td colspan='1'>".$_SESSION['lang']['action']."</td>",$tableHtml);

echo $tableHtml;
?>
