<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$lksiTugas."' order by periode desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
	$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
}
$optBag="<option value=''>".$_SESSION['lang']['all']."</option>";
$sBag="select kode,nama from ".$dbname.".sdm_5departemen order by nama asc";//$optBag
$qBag=$owlPDO->query($sBag) or die(print " Gagal: ".PDOException::getMessage());
$qBag->setFetchMode(PDO::FETCH_ASSOC);
$optOrg="";
while($rBag=$qBag->fetch())
{
	$optBag.="<option value=".$rBag['kode'].">".$rBag['nama']."</option>";
}
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
{
	$optOrg="<select id=kdOrg name=kdOrg onchange=getPeriode() style=\"width:150px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN','PABRIK','KANWIL') order by namaorganisasi asc ";	
}
else
{
	$optOrg="<select id=kdOrg name=kdOrg style=\"width:150px;\"><option value=''>".$_SESSION['lang']['all']."</option>";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc";
}
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$optOrg2="";
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
	$optOrg2.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}
$optOrg.="</select>";
$optOrg2.="</select>";
$optSisGaji="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrSisGaji=array("0"=>"Harian","1"=>"Bulanan");
foreach($arrSisGaji as $dt => $isi)
{
    $optSisGaji.="<option value=".$isi.">".$_SESSION['lang'][strtolower($isi)]."</option>";
}
$arr="##kdOrg##periode##kdBag##tgl1##tgl2##sisGaji";


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/sdm_2rekapabsen.js'></script>
<link rel=stylesheet type='text/css href=style/zTable.css'>
<script>
function bersihForm()
{
    document.getElementById('tgl1').value='';
    document.getElementById('tgl2').value='';
    document.getElementById('tgl1').disabled=false;
    document.getElementById('tgl2').disabled=false;
    document.getElementById('kdOrg').value='';
    document.getElementById('sisGaji').value='';
    document.getElementById('kdBag').value='';
    document.getElementById('periode').value='';
    document.getElementById('printContainer').innerHTML='';
}
</script>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['rinciGajiBag']).'</span>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td><?php echo $optOrg?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td><select id="periode" name="periode" style="width:150px" onchange="getTgl()"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['bagian']?></label></td><td>:</td><td><select id="kdBag" name="kdBag" style="width:150px"><?php echo $optBag?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['sistemgaji']?></label></td><td>:</td><td>
        <select id="sisGaji" name="sisGaji" style="width:150px">
        <?php echo $optSisGaji; ?>
        </select></td></tr>

<input type="hidden" class="myinputtext" id="tgl1" name="tgl1" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:150px;" />
<input type="hidden" class="myinputtext" id="tgl2" name="tgl2" onmousemove="setCalendar(this.id)" onkeypress="return false;"  maxlength="10" style="width:150px;" />

<tr><td colspan="3" align="right"><button onclick="zPreview('sdm_slave_2rincianGajiBagian','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('sdm_slave_2rincianGajiBagian','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2rincianGajiBagian.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="bersihForm()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>

</table>
</fieldset>
</div>
<div style="margin-bottom: 30px;">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1220px'>
<?php
//echo"<pre>";
//print_r($_SESSION);
//echo"</pre>";
?>
</div></fieldset>

<?php

CLOSE_BOX();
echo close_body();
?>