<?php
//@Copy nangkoelframework
//-----------------ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
require_once('lib/zSelect2.php');
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/kebun_3updkg.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?php

$frm[0] = '';
$frm[1] = '';

### Get Value Enum Suppllier
$optTipeSup = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select distinct(badanusaha) from ".$dbname.".log_5supplier order by badanusaha";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optTipeSup.="<option value='".$bar->badanusaha."'>".$bar->badanusaha."</option>";
}

### Get List Kelompok
$str = "select distinct(tipe) ,kode from ".$dbname.".log_5klsupplier";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optKelompok = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
while ($bar = $res->fetch()) {
    $optKelompok.="<option value='".$bar->tipe."'>".$bar->kode."</option>";
}

$buyer = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$iBuy = "select * from " . $dbname . ".pmn_4customer order by namacustomer asc";
$nBuy=$owlPDO->query($iBuy) or die(print " Gagal: ".PDOException::getMessage());
$nBuy->setFetchMode(PDO::FETCH_ASSOC);
while ($dBuy = $nBuy->fetch()) {
    $buyer.="<option value='" . $dBuy['kodecustomer'] . "'>" . $dBuy['namacustomer'] . "</option>";
}

#komoditi
$komoditi = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$iBrg = "select * from " . $dbname . ".log_5masterbarang where kelompokbarang='400' order by namabarang asc ";
$nBrg=$owlPDO->query($iBrg) or die(print " Gagal: ".PDOException::getMessage());
$nBrg->setFetchMode(PDO::FETCH_ASSOC);
while ($dBrg = $nBrg->fetch()) {
    $komoditi.="<option value='" . $dBrg['kodebarang'] . "'>" . $dBrg['namabarang'] . "</option>";
}

include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('log_2skc').'</span><br>');

$arr = "##namasupplier##tipe##kdkelompok";
$frm[0].="<fieldset style=float:left;>
	<legend><b>" . $_SESSION['lang']['form'] . "</b></legend>
	<table border=0 cellpadding=1 cellspacing=1>
		<tr>
			<td>" . $_SESSION['lang']['namasupplier'] . "</td><td>:</td><td><input type=text class=myinputtext id=namasupplier onkeypress=\return tanpa_kutip(event);\" class=select2 style=width:170px maxlength=45 placeholder='" . $_SESSION['lang']['all'] . "'></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['Type'] . "</td><td>:</td><td><select class=select2 style=width:175px id=tipe>" . $optTipeSup . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['kodekelompok'] . "</td><td>:</td><td><select class=select2 style=width:175px id=kdkelompok>" . $optKelompok . "</select></td>
		</tr>
		<tr>
			<td></td>
				<td><td><button onclick=\"zPreview('log_slave_2daftarsupplier','" . $arr . "','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['preview'] . "</button>
					<button onclick=\"zExcel(event,'log_slave_2daftarsupplier.php','" . $arr . "')\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['excel'] . "</button>
				</td>
		</tr>
		</table>
	</fieldset>
	<fieldset style='clear:both'><legend><b>Print Area</b></legend>
	
	<div id='printContainer' style='overflow:auto;height:350px;max-width:100%'>
	</fieldset>";


##############buyer
$arrbuy = "##buy##brg";
$frm[1].="<fieldset style='float:left;'><legend><b>Form</b></legend>
<table>";


$frm[1].="<tr>
            <td>Buyer</td>
            <td>:</td>
            <td><select class=select2 id=buy style='width:200px;'>" . $buyer . "</select></td>
	</tr>";

$frm[1].="<tr>
            <td>Komoditi</td>
            <td>:</td>
            <td><select class=select2 id=brg style='width:200px;'>" . $komoditi . "</select></td>
	</tr>";


$frm[1].="<tr>
		<td><td><td>
		<button onclick=zPreview('log_slave_2skcbuy','" . $arrbuy . "','printContainerBuy') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
		<button onclick=zExcel(event,'log_slave_2skcbuy.php','" . $arrbuy . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
		
		
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
	</tr>
</table>
</fieldset>";



$frm[1].="
<fieldset style='clear:both'><legend><b>" . $_SESSION['lang']['list'] . " Buyer</b></legend>
<div id='printContainerBuy' style='height:350px;overflow:auto;width:100%;'></div></fieldset>"; // style='overflow:auto;height:350px;max-width:1220px'; 





/* $hfrm[0]='Supplier';
  $hfrm[1]='Kontraktor';
  $hfrm[2]='Buyer';
 */

$hfrm[0] = $_SESSION['lang']['supplier'].' , '.$_SESSION['lang']['kontraktor'].' , '.$_SESSION['lang']['transporter'];
$hfrm[1] = 'Buyer';

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 250, '100%');

CLOSE_BOX();
echo close_body();
?>
