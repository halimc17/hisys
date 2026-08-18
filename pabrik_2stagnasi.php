<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
$optOrg=$optagama=$optPerbaikan=$optPerbaikan=$optTuntas="<option value=''>".$_SESSION['lang']['all']."</option>";
$optagama=$optOrg;
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$arrdwn=array('EDT'=>'EDT - Breakdown','SDT'=>'SDT - Non Breakdown','CDT'=>'-');

$arragama=getEnum($dbname,'pabrik_pengolahanmesin','downstatus');
foreach($arragama as $kei=>$fal){
	$optagama.="<option value='".$kei."'>".$arrdwn[$fal]."</option>";
}  
$arrRe="##kdPabrik##tgl1##tgl2##dwnStatus##tipeperbaikan##statusketuntasan";

$optPabrik="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg2="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
$qOrg2=$owlPDO->query($sOrg2) or die(print " Gagal: ".PDOException::getMessage());
$qOrg2->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg2=$qOrg2->fetch())
{
	$optPabrik.="<option value=".$rOrg2['kodeorganisasi'].">".$rOrg2['namaorganisasi']."</option>";
}

$optPerbaikan.="<option value='prev'>Preventive Maintenance</option>";
$optPerbaikan.="<option value='mayor'>Mayor Maintenance</option>";
$optPerbaikan.="<option value='corrective'>Corrective Maintenance</option>";

$optTuntas.="<option value='Rencana'>Rencana</option>";
$optTuntas.="<option value='Lanjut'>Lanjut</option>";
$optTuntas.="<option value='Selesai'>Selesai</option>";
$optTuntas.="<option value='Tunda'>Tunda</option>";

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
// OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['stagnasi']).'</span>');
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_2stagnasi').'</span>');
?>
      <div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']; ?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['pabrik']?></label></td><td>:</td><td><select id="kdPabrik" name="kdPabrik"  style="width:202px"><? echo $optPabrik?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td><td><input type="text" class="myinputtext" id="tgl1" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="10" maxlength="10" readonly/>
        s.d. <input type="text" class="myinputtext" id="tgl2" onmousemove="setCalendar(this.id)" onkeypress="return false;"  size="10" maxlength="10" readonly/>
</td></tr>
<tr><td><label><?php echo $_SESSION['lang']['downstatus']?></label></td><td>:</td><td><select id="dwnStatus" name="dwnStatus"  style="width:202px"><? echo $optagama?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tipe']?></label></td><td>:</td><td><select id="tipeperbaikan" name="tipeperbaikan"  style="width:202px"><? echo $optPerbaikan?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['statusketuntasan']?></label></td><td>:</td><td><select id="statusketuntasan" name="statusketuntasan"  style="width:202px"><? echo $optTuntas?></select></td></tr>

<tr height="2"><td colspan="2"></td></tr>
<tr><td><td><td><button onclick="zPreview('pabrik_slave_2stagnasi','<?php echo $arrRe?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
        <!--<button onclick="zPdf('pabrik_slave_2loses','<?php echo $arrRe?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>-->
        <button onclick="zExcel(event,'pabrik_slave_2stagnasi.php','<?php echo $arrRe?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>
<?php
CLOSE_BOX();
OPEN_BOX();
?>

             

<!-- <fieldset style='clear:both;max-width:1220px'><legend><b>Print Area</b></legend> -->
<div id='printContainer'>

</div>
</fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>
