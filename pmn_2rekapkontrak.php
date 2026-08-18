<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php

$optPeriode =$optPabrik =$optBrg1 ="";
$frm[0]="";
$str="select distinct periode from ".$dbname.".log_5saldobulanan order by periode desc";
//$res=mysql_query($str);
//while($bar=mysql_fetch_object($res))
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
	$optPeriode.="<option value='".$bar->periode."'>".substr($bar->periode,5,2)."-".substr($bar->periode,0,4)."</option>";
}	

$sPabrik="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT'";
//$qPabrik=mysql_query($sPabrik) or die(mysql_error());
//while($rPabrik=mysql_fetch_assoc($qPabrik))
$qPabrik=$owlPDO->query($sPabrik) or die(print " Gagal: ".PDOException::getMessage());
$qPabrik->setFetchMode(PDO::FETCH_ASSOC);
while($rPabrik=$qPabrik->fetch())
{
	$optPabrik.="<option value=".$rPabrik['kodeorganisasi'].">".$rPabrik['namaorganisasi']."</option>";
}
//$optPabrik="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPabrik1="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOpt="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
//$qOpt=mysql_query($sOpt) or die(mysql_error());
//while($rOpt=mysql_fetch_assoc($qOpt))
$qOpt=$owlPDO->query($sOpt) or die(print " Gagal: ".PDOException::getMessage());
$qOpt->setFetchMode(PDO::FETCH_ASSOC);
while($rOpt=$qOpt->fetch())
{
	$optPabrik1.="<option value=".$rOpt['kodeorganisasi'].">".$rOpt['namaorganisasi']."</option>";
}

$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
//$qBrg=mysql_query($sBrg) or die(mysql_error());
$optBrg = "<option value=''>".$_SESSION['lang']['all']."</option>";
//while($rBrg=mysql_fetch_assoc($qBrg))
$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
while($rBrg=$qBrg->fetch())
{
	$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}

$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang in ('40000001', '40000002')";
//$qBrg=mysql_query($sBrg) or die(mysql_error());
//while($rBrg=mysql_fetch_assoc($qBrg))
$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
while($rBrg=$qBrg->fetch())
{
	$optBrg1.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/pmn_2rekapkontrak.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?      


$frm[0].="<div style=\"margin-bottom: 30px;\">
<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=\"1\" border=\"0\" >

<tr>
	<td><label>".$_SESSION['lang']['tanggal']."</label></td>
	<td>:</td>
	<td><input type=text class=myinputtext id=tanggal1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=7 maxlength=10 readonly/>
		".$_SESSION['lang']['sd']."
	<input type=text class=myinputtext id=tanggal2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=7 maxlength=10 readonly/></td>
</tr>
<tr>
	<td><label>".$_SESSION['lang']['nm_perusahaan']."</label></td>
	<td>:</td>
	<td><select id=\"kodept\" name=\"kodept\" style=\"width:165px\">".$optPabrik."</select></td>
</tr>
<tr>
	<td><label>".$_SESSION['lang']['komoditi']."</label></td>
	<td>:</td>
	<td><select id=\"kodebarang\" name=\"kodebarang\" style=\"width:165px\">".$optBrg."</select></td>
</tr>
<tr>

	<td  colspan=4><button onclick=\"preview()\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
	<button onclick=\"excel()\" class=\"mybutton\" name=\"Excel\" id=\"Excel\">Excel</button></td>
</tr>
</table>
</fieldset>
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1150px'>
</div></fieldset>";
//========================
if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.getMenu('pmn_2rekapkontrak').'</span>');
	$hfrm[0]="Sales Recap";
}else{
	OPEN_BOX('','<span class=judul>'.getMenu('pmn_2rekapkontrak').'</span>');
	$hfrm[0]="Rekap Kontrak";
}
drawTab('FRM',$hfrm,$frm,320,1150);
//===============================================

CLOSE_BOX();
echo close_body();
?>