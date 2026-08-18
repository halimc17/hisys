<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<?php
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);


$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc ";	


$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$optOrg="";
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$sOrg="select distinct substr(periodegaji,1,4) as periodegaji from ".$dbname.".sdm_gaji order by periodegaji desc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$optTahun="";
while($rOrg=$qOrg->fetch())
{
	$optTahun.="<option value=".$rOrg['periodegaji'].">".$rOrg['periodegaji']."</option>";
}

$arr="##kodeorg##tahun";

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/sdm_2pajak.js'></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<div>
<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_2pajakho').'</span><br>');
?>
<br><fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']; ?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td><select id="kodeorg" name="kodeorg" style="width:150px"><option value=""></option><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tahun']?></label></td><td>:</td><td><select id="tahun" name="tahun" style="width:150px"><option value=""></option><?php echo $optTahun?></select></td></tr>
<tr><td colspan="3" align="right">
    <button onclick="zPreview('sdm_slave_2pajakho','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="html">Preview</button>
    <button onclick="zExcel(event,'sdm_slave_2pajakho.php','<?php echo $arr?>')" class="mybutton" name="excel" id="excel">Excel</button>
    <button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel'];?></button></td></tr>
</table>
</fieldset>
</div>
<?
CLOSE_BOX();
OPEN_BOX();
?>

<fieldset style='clear:both; width:1210px;'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto; width:1235px;height:400px;'>

</div></fieldset>
<?php

CLOSE_BOX();
echo close_body();
?>