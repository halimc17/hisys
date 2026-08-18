<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX(); 
?>
<?php
$optBrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kodebarang in ('40000001', '40000002', '40000003', '40000005')";
$qOrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rBrg=$qOrg->fetch())
{
	$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}

$sPabrik = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='PT'";
$qPabrik = $owlPDO->query($sPabrik) or die(print " Gagal: " . PDOException::getMessage());
$qPabrik->setFetchMode(PDO::FETCH_ASSOC);
while ($rPabrik = $qPabrik->fetch()) {
    $optPabrik.="<option value=" . $rPabrik['kodeorganisasi'] . ">" . $rPabrik['namaorganisasi'] . "</option>";
}

$arr="##tanggalmulai##tanggalakhir##idPabrik##kdBrg";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/pmn_2commercial.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?      
echo"<div style=\"margin-bottom: 30px;\">
<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['laporanPenjualan']."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr><td><label>".$_SESSION['lang']['tanggalmulai']."</label></td><td>:</td><td><input type=text class=myinputtext id=tanggalmulai onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('d-m-Y')."'/>
	s/d <input type=text class=myinputtext id=tanggalakhir onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 value='".date('d-m-Y')."'/></td></tr>
<tr><td><label>".$_SESSION['lang']['nm_perusahaan']."</label></td><td>:</td><td><select id=\"idPabrik\" name=\"idPabrik\" style=\"width:187px\">".$optPabrik."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['komoditi']."</label></td><td>:</td><td><select id=\"kdBrg\" name=\"kdBrg\" style=\"width:187px\">".$optBrg."</select></td></tr>
<tr><td></td><td colspan=\"2\"><button onclick=\"zPreview('pmn_slave_2commercial','".$arr."','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'pmn_slave_2commercial.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td></tr>
</table>
</fieldset>
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow-x:auto'>
</div></fieldset>";

//===============================================

CLOSE_BOX();
echo close_body();
?>