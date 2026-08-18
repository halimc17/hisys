<?

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php

$optPeriode = $optPabrik = $frm[0] = $frm[1] = $frm[2] = "";
$str = "select distinct periode from " . $dbname . ".log_5saldobulanan order by periode desc";
//$res=mysql_query($str);
//while($bar=mysql_fetch_object($res))
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optPeriode.="<option value='" . $bar->periode . "'>" . substr($bar->periode, 5, 2) . "-" . substr($bar->periode, 0, 4) . "</option>";
}


$sPabrik = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where tipe='PT'";
//$qPabrik=mysql_query($sPabrik) or die(mysql_error());
//while($rPabrik=mysql_fetch_assoc($qPabrik))
$qPabrik = $owlPDO->query($sPabrik) or die(print " Gagal: " . PDOException::getMessage());
$qPabrik->setFetchMode(PDO::FETCH_ASSOC);
while ($rPabrik = $qPabrik->fetch()) {
    $optPabrik.="<option value=" . $rPabrik['kodeorganisasi'] . ">" . $rPabrik['namaorganisasi'] . "</option>";
}
//$optPabrik="<option value=''>".$_SESSION['lang']['all']."</option>";
$optPabrik1 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sOpt = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PABRIK'";
//$qOpt=mysql_query($sOpt) or die(mysql_error());
//while($rOpt=mysql_fetch_assoc($qOpt))
$qOpt = $owlPDO->query($sOpt) or die(print " Gagal: " . PDOException::getMessage());
$qOpt->setFetchMode(PDO::FETCH_ASSOC);
while ($rOpt = $qOpt->fetch()) {
    $optPabrik1.="<option value=" . $rOpt['kodeorganisasi'] . ">" . $rOpt['namaorganisasi'] . "</option>";
}

$optBrg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sBrg = "select namabarang,kodebarang from " . $dbname . ".log_5masterbarang where kelompokbarang='400'";
//$qBrg=mysql_query($sBrg) or die(mysql_error());
//while($rBrg=mysql_fetch_assoc($qBrg))
$qBrg = $owlPDO->query($sBrg) or die(print " Gagal: " . PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rBrg = $qBrg->fetch()) {
    $optBrg.="<option value=" . $rBrg['kodebarang'] . ">" . $rBrg['namabarang'] . "</option>";
}
$sBrg = "select namabarang,kodebarang from " . $dbname . ".log_5masterbarang where kelompokbarang in ('40000001', '40000002')";
//$qBrg=mysql_query($sBrg) or die(mysql_error());
//while($rBrg=mysql_fetch_assoc($qBrg))
$qBrg = $owlPDO->query($sBrg) or die(print " Gagal: " . PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rBrg = $qBrg->fetch()) {
    $optBrg1.="<option value=" . $rBrg['kodebarang'] . ">" . $rBrg['namabarang'] . "</option>";
}

$arr = "##tanggalmulai##tanggalakhir##idPabrik##kdBrg";
$arr1 = "##kodeorg1##kodebarang1##tgl1_1##tgl2_1";
$arr2 = "##tanggalmulai1##tanggalakhir1##idPabrik1##kdBrg1";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/pmn_2penjualan.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['laporanPenjualan']).'</span>');
$frm[0].="<div style=\"margin-bottom: 30px;\">
<fieldset style=\"float: left;\">
<legend><b>" . $_SESSION['lang']['laporanPenjualan'] . "</b></legend>
<table cellspacing=\"1\" border=\"0\" >

<tr>
	<td style='display:none'><label>" . $_SESSION['lang']['periode'] . "</label></td>
	<td style='display:none;'><select id=\"periode\" name=\"periode\" style=\"width:150px\">" . $optPeriode . "</select></td>
</tr>
<tr>
	<td><label>" . $_SESSION['lang']['tanggal'] . "</label></td>
	<td><input type=text class=myinputtext id=tanggalmulai onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='" . date('d-m-Y') . "' readonly/>
<!--</tr>
<tr>
	<td><label>" . $_SESSION['lang']['tanggalsampai'] . "</label></td>-->
	s/d <input type=text class=myinputtext id=tanggalakhir onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='" . date('d-m-Y') . "' readonly/></td>
</tr>
<tr>
	<td><label>" . $_SESSION['lang']['nm_perusahaan'] . "</label></td>
	<td colspan=6><select id=\"idPabrik\" name=\"idPabrik\" style=\"width:175px\">" . $optPabrik . "</select></td>
</tr>
<tr>
	<td><label>" . $_SESSION['lang']['komoditi'] . "</label></td>
	<td colspan=6><select id=\"kdBrg\" name=\"kdBrg\" style=\"width:175px\">" . $optBrg . "</select></td>
</tr>
<tr>
<td colspan=1>
	<td colspan=\"2\"><button onclick=\"zPreview('pmn_slave_2penjualan','" . $arr . "','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zPdf('pmn_slave_2penjualan','" . $arr . "','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">PDF</button>
    <button onclick=\"zExcel(event,'pmn_slave_2penjualan.php','" . $arr . "')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td>
</tr>
</table>
</fieldset>
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>
</div></fieldset>";

$frm[1].="<div style=\"margin-bottom: 30px;\">
<fieldset style=\"float: left;\">
<legend><b>" . $_SESSION['lang']['rPengiriman'] . " " . $_SESSION['lang']['harian'] . "</b></legend>
<table cellspacing=\"1\" border=\"0\" >

<tr>
	<td><label>" . $_SESSION['lang']['pabrik'] . "</label></td>
	<td><select id=\"kodeorg1\" name=\"kodeorg1\" style=\"width:150px\">" . $optPabrik1 . "</select></td>
</tr>
<tr>
	<td><label>" . $_SESSION['lang']['komoditi'] . "</label></td>
	<td><select id=\"kodebarang1\" name=\"kodebarang1\" style=\"width:150px\">" . $optBrg . "</select></td>
</tr>
<tr>
	<td><label>" . $_SESSION['lang']['tanggal'] . "</label></td>
	<td><input type=text class=myinputtext id=tgl1_1 onchange=bersih_1() onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=7 maxlength=10 readonly> - 
	<input type=text class=myinputtext id=tgl2_1 onchange=bersih_1() onmousemove=setCalendar(this.id); onkeypress=\"return false;\" size=7 maxlength=10 readonly></td>
</tr>
<tr>
<td><td>
<button onclick=\"zPreview('pmn_slave_2penjualan_harian','" . $arr1 . "','printContainer1')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'pmn_slave_2penjualan_harian.php','" . $arr1 . "')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td>
</tr>

</table>
</fieldset>
</div>

<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer1' style='overflow:auto;height:350px;max-width:1220px'>
</div></fieldset>";


$frm[2].="<div style=\"margin-bottom: 30px;\">
<fieldset style=\"float: left;\">
<legend><b>" . $_SESSION['lang']['laporanPenjualan'] ." ". $_SESSION['lang']['harian'] . "</b></legend>
<table cellspacing=\"1\" border=\"0\" >

<tr>
	<td style='display:none'><label>" . $_SESSION['lang']['periode'] . "</label></td>
	<td style='display:none;'><select id=\"periode\" name=\"periode\" style=\"width:150px\">" . $optPeriode . "</select></td>
</tr>
<tr>
	<td><label>" . $_SESSION['lang']['tanggal'] . "</label></td>
	<td><input type=text class=myinputtext id=tanggalmulai1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='" . date('d-m-Y') . "' readonly/>
<!--</tr>
<tr>
	<td><label>" . $_SESSION['lang']['tanggalsampai'] . "</label></td>-->
	s/d <input type=text class=myinputtext id=tanggalakhir1 onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='" . date('d-m-Y') . "' readonly/></td>
</tr>
<tr>
	<td><label>" . $_SESSION['lang']['nm_perusahaan'] . "</label></td>
	<td colspan=6><select id=\"idPabrik1\" name=\"idPabrik1\" style=\"width:175px\">" . $optPabrik . "</select></td>
</tr>
<tr>
	<td><label>" . $_SESSION['lang']['komoditi'] . "</label></td>
	<td colspan=6><select id=\"kdBrg1\" name=\"kdBrg1\" style=\"width:175px\">" . $optBrg . "</select></td>
</tr>
<tr>
<td colspan=1>
	<td colspan=\"2\"><button onclick=\"zPreview('pmn_slave_2penjualanharian','" . $arr2 . "','printContainer2')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zPdf('pmn_slave_2penjualanharian','" . $arr2 . "','printContainer2')\" class=\"mybutton\" name=\"preview\" id=\"preview\">PDF</button>
    <button onclick=\"zExcel(event,'pmn_slave_2penjualanharian.php','" . $arr2 . "')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button></td>
</tr>
</table>
</fieldset>
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer2' style='overflow:auto;height:350px;max-width:1220px'>
</div></fieldset>";
//    <button onclick=\"zPdf('pmn_slave_2penjualan_harian','".$arr1."','printContainer1')\" class=\"mybutton\" name=\"preview\" id=\"preview\">PDF</button>
//========================
$hfrm[0] = $_SESSION['lang']['laporanPenjualan'];
$hfrm[1] = $_SESSION['lang']['rPengiriman'] . " " . $_SESSION['lang']['harian'];
$hfrm[2] = $_SESSION['lang']['laporanPenjualan'] . " " . $_SESSION['lang']['harian'];
//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 200, 1220);
//===============================================

CLOSE_BOX();
echo close_body();
?>