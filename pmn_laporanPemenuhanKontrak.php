<?
//@Copy nangkoelframework
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');

?>
<?php

//$arr="##periode";
$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPeriode="select distinct substr(tanggalkontrak,1,4) as periode from ".$dbname.".pmn_kontrakjual order by substr(tanggalkontrak,1,4) desc";

$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
    $optPeriode.="<option value='".$rPeriode['periode']."'>".$rPeriode['periode']."</option>";
}
$optPeriode2="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPeriode2="select distinct substr(tanggalkontrak,1,4) as periode from ".$dbname.".pmn_kontrakjual order by substr(tanggalkontrak,1,7) desc";

$qPeriode2=$owlPDO->query($sPeriode2) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode2->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode2=$qPeriode2->fetch())
{
    $optPeriode2.="<option value='".$rPeriode2['periode']."'>".$rPeriode2['periode']."</option>";
}

$optBrg="<option value=''>".$_SESSION['lang']['all']."</option>";
$optBrg2="<option value=''>".$_SESSION['lang']['all']."</option>";
$sBrg="select namabarang,kodebarang from ".$dbname.".log_5masterbarang where kelompokbarang='400'";

$qBrg=$owlPDO->query($sBrg) or die(print " Gagal: ".PDOException::getMessage());
$qBrg->setFetchMode(PDO::FETCH_ASSOC);
while($rBrg=$qBrg->fetch())
{
		$optBrg.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
                $optBrg2.="<option value=".$rBrg['kodebarang'].">".$rBrg['namabarang']."</option>";
}

$optPt="<option value=''>".$_SESSION['lang']['all']."</option>";
$sOrg="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'"; //echo $sOrg;

$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
        $optPt.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$arr="##periode##kdBrg##pt";
$arr2="##thn##kdBrg2##pt2";
$arr3="##kdBrg3##tgl_dr##tgl_samp##pt3";
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/pmn_laporanPemenuhanKontrak.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['laporanPemenuhanKontrak']).'</span>');
?>
<div style="margin-bottom: 30px;">
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['laporanPemenuhanKontrak']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['perusahaan']?></label></td><td><select id="pt" name="pt" style="width:173px"><?php echo $optPt?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td><select id="periode" name="periode" style="width:173px"><?php echo $optPeriode?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['komoditi']?></label></td><td><select id="kdBrg" name="kdBrg" style="width:173px"><?php echo $optBrg?></select></td></tr>
<tr><td><td><button onclick="zPreview('pmn_slave_laporanPemenuhanKontrak','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button hidden onclick="zPdf('pmn_slave_laporanPemenuhanKontrak','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'pmn_slave_laporanPemenuhanKontrak.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>
</table>
</fieldset>
<fieldset style="float: left;">
<legend><b>Uncomplete Contract</b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['perusahaan']?></label></td><td><select id="pt2" name="pt2" style="width:173px"><?php echo $optPt?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tahun']?></label></td><td><select id="thn" name="thn" style="width:173px"><?php echo $optPeriode2?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['komoditi']?></label></td><td><select id="kdBrg2" name="kdBrg2" style="width:173px"><?php echo $optBrg2?></select></td></tr>
<tr><td><td><button onclick="zPreview2('pmn_slave_laporanPemenuhanKontrak','<?php echo $arr2?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel2(event,'pmn_slave_laporanPemenuhanKontrak.php','<?php echo $arr2?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>
</table>
</fieldset>
<fieldset style="float: left;" hidden>
<legend><b>Range Delivery</b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['perusahaan']?></label></td><td><select id="pt3" name="pt3" style="width:173px"><?php echo $optPt?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['komoditi']?></label></td><td><select id="kdBrg3" name="kdBrg3" style="width:173px"><?php echo $optBrg2?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td><input type=text  style="width:70px" class=myinputtext id=tgl_dr onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 />
s/d <input type=text  style="width:70px" class=myinputtext id=tgl_samp onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>
<tr><td><td><button onclick="zPreview3('pmn_slave_laporanPemenuhanKontrak','<?php echo $arr3?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel3(event,'pmn_slave_laporanPemenuhanKontrak.php','<?php echo $arr3?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>
</table>
</fieldset>
</div>
<?php
CLOSE_BOX();
OPEN_BOX();
?>
<div id='printContainer'>

</div>

<?php
CLOSE_BOX();
echo close_body();
?>