<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href='style/zTable.css'>
<?php
#====== Controller ======
# Options
$optPeriod="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optKode=$optJns2=$optJns=$optPeriod;
$sPeriod="select distinct substr(periode,1,4) as thn from ".$dbname.".sdm_5periodegaji order by periode asc";
$qPeriod=$owlPDO->query($sPeriod) or die(print " Gagal: ".PDOException::getMessage());
$qPeriod->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriod=$qPeriod->fetch())
{
    $optPeriod.="<option value=".$rPeriod['thn'].">".$rPeriod['thn']."</option>";
}
$re=array('28'=>'THR','26'=>'Bonus');
$tre=array('Bulanan'=>$_SESSION['lang']['bulanan'],'Harian'=>$_SESSION['lang']['harian']);
foreach($re as $dtr=>$lst)
{
    $optJns.="<option value='".$dtr."'>".$lst."</option>";
}
foreach($tre as $dtr2)
{
    $optJns2.="<option value='".$dtr2."'>".$dtr2."</option>";
}
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
// {
//     $sData="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi 
//             where char_length(kodeorganisasi)=4 and tipe in ('KEBUN','PABRIK','TRAKSI','KANWIL') order by namaorganisasi asc ";
// }
// elseif($_SESSION['empl']['tipelokasitugas']=='KANWIL')
// {
//     $sData="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['org']['kodeorganisasi']."' order by namaorganisasi asc ";
//     if($_SESSION['org']['kodeorganisasi']=='PMO')
//         $sData="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk in ('PMO','GMJ','KUD','KMT') order by namaorganisasi asc ";

// }
// else
// {
//     $sData="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
// }
// $qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
// $qData->setFetchMode(PDO::FETCH_ASSOC);
// while($rData=$qData->fetch())
// {
//     $optKode.="<option value='".$rData['kodeorganisasi']."'>".$rData['namaorganisasi']."</option>";
// }
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optKode.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	if($key==$_SESSION['empl']['lokasitugas']){
		$optKode.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$unit=$key;
	}else{
		$optKode.="<option value='".$key."'>".$key." - ".$val."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optKode.="</optgroup>";
	}
}
$arrData="##kdOrg##periodegaji##jenis##jnsGaji";
$frm="<fieldset style=width:250px><legend>Slyip Bonus/THR</legend>";
$frm.="<table cellpadding=1 cellspacing=1 border=0>";
$frm.="<tr><td>".$_SESSION['lang']['unit']."</td>";
$frm.="<td><select id=kdOrg style='width:150px;'>".$optKode."</select></td></tr>";
$frm.="<tr><td>".$_SESSION['lang']['periodebonus']."</td>";
$frm.="<td><select id=periodegaji style='width:150px;'>".$optPeriod."</select></td></tr>";
$frm.="<tr><td>".$_SESSION['lang']['jenis']."</td>";
$frm.="<td><select id=jenis style='width:150px;'>".$optJns."</select></td></tr>";
$frm.="<tr><td>".$_SESSION['lang']['sistemgaji']."</td>";
$frm.="<td><select id=jnsGaji style='width:150px;'>".$optJns2."</select></td></tr>";
$frm.="<tr><td colspan=2><button class=mybutton onclick=zPreview('sdm_slave_2slipBonusThr','".$arrData."','listPosting')>".$_SESSION['lang']['preview']."</button>";
$frm.="<button class=mybutton onclick=zPdf('sdm_slave_2slipBonusThr','".$arrData."','listPosting')>".$_SESSION['lang']['pdf']."</button>";
$frm.="<button class=mybutton onclick=zExcel(event,'sdm_slave_2slipBonusThr.php','".$arrData."')>".$_SESSION['lang']['excel']."</button></td></tr>";
$frm.="</table>";


$form = "";
$form .= "<h3 align='left'>".$_SESSION['lang']['bonus']."</h3>";


#====== View ======
# Form
OPEN_BOX('','<span class=judul>'.strtoupper('Slyip Bonus/THR').'</span>');
echo $frm;
CLOSE_BOX();

# List
OPEN_BOX();
echo"
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='listPosting' style='overflow:auto;height:350px;max-width:1220px'>

</div></fieldset>";
CLOSE_BOX();

echo close_body();
?>