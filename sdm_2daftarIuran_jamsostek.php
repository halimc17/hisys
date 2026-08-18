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
$optTipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$sTipe="select id,tipe from ".$dbname.".sdm_5tipekaryawan where id NOT IN ('2','5','6') order by tipe asc";
$qTipe=$owlPDO->query($sTipe) or die(print " Gagal: ".PDOException::getMessage());
$qTipe->setFetchMode(PDO::FETCH_ASSOC);
while($rTipe=$qTipe->fetch())
{
	$optTipe.="<option value=".$rTipe['id'].">".$rTipe['tipe']."</option>";
}
$optGaji="<option value=''>".$_SESSION['lang']['all']."</option>";
//		$optsisgaji='';
            $arrsgaj=getEnum($dbname,'datakaryawan','sistemgaji');
            foreach($arrsgaj as $kei=>$fal)
            {
                    $optGaji.="<option value='".$kei."'>".$_SESSION['lang'][strtolower($fal)]."</option>";
            }  
$dr='';
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'||$_SESSION['empl']['tipelokasitugas']=='KANWIL')
{
	$optOrg="<select id=kdOrg name=kdOrg onchange=getPeriode() style=\"width:150px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$optOrg2="<select id=kdeOrg name=kdeOrg onchange=getKry() style=\"width:150px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi)='4' and tipe in ('KEBUN','PABRIK','KANWIL','TRAKSI') order by namaorganisasi asc ";
        $dr="##kdOrg";
}
else
{
	$optOrg="<select id=kdOrg name=kdOrg style=\"width:150px;\"><option value=''>".$_SESSION['lang']['all']."</option>";
	$optOrg2="<select id=kdeOrg name=kdeOrg style=\"width:150px;\" onchange=getKry()><option value=''>".$_SESSION['lang']['all']."</option>";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc";
}
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
	$optOrg2.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}
$optOrg.="</select>";
$optOrg2.="</select>";

$arr="".$dr."##periode##tipeKary##sistemGaji";


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/sdm_2rekapabsen.js'></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script>
</script>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['dafJams']).'</span>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<?php if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KANWIL'){?><tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td><?php echo $optOrg?></td></tr><?php } ?>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td><select id="periode" name="periode" style="width:150px"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tipekaryawan']?></label></td><td><select id="tipeKary" name="tipeKary" style="width:150px"><?php echo $optTipe?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['sistemgaji']?></label></td><td><select id="sistemGaji" name="sistemGaji" style="width:150px"><?php echo $optGaji?></select></td></tr>

<tr><td colspan="2" align="right"><button onclick="zPreview('sdm_slave_2daftarIuran_jamsostek','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('sdm_slave_2daftarIuran_jamsostek','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'sdm_slave_2daftarIuran_jamsostek.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>

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