<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<?php
$optBatch="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
    // $sBatch="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
    // $sKodeorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI' order by namaorganisasi asc";
// }else{
    // $sBatch="select distinct periode from ".$dbname.".setup_periodeakuntansi where kodeorg like '%".$_SESSION['empl']['lokasitugas']."%' order by periode desc";
    // $sKodeorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI' and kodeorganisasi like '%".$_SESSION['empl']['lokasitugas']."%' order by namaorganisasi asc";
// }


$sBatch="select distinct periode from ".$dbname.".setup_periodeakuntansi order by periode desc";
$sKodeorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='TRAKSI' order by namaorganisasi asc";
$qBatch=$owlPDO->query($sBatch) or die(print " Gagal: ".PDOException::getMessage());
$qBatch->setFetchMode(PDO::FETCH_ASSOC);
while($rBatch=$qBatch->fetch()){
    if(substr($rBatch['periode'],4,2)=='12'){
     $optBatch.="<option value='".substr($rBatch['periode'],0,4)."'>".substr($rBatch['periode'],0,4)."</option>";   
    }
    $optBatch.="<option value='".$rBatch['periode']."'>".$rBatch['periode']."</option>";
}

$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$qKodeOrg=$owlPDO->query($sKodeorg) or die(print " Gagal: ".PDOException::getMessage());
$qKodeOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rKodeorg=$qKodeOrg->fetch()){
    $optKodeorg.="<option value='".$rKodeorg['kodeorganisasi']."'>".$rKodeorg['namaorganisasi']."</option>";
}
$optKodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,alokasi',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optKodeorg.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optKodeorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optKodeorg.="</optgroup>";
	}
}

$arr="##kdUnit##periode";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<script>    
$(document).ready(function() {
	$('.select2').select2({
		dropdownAutoWidth:true
	});
});
</script>    
<?
OPEN_BOX('','<span class=judul>'.getMenu('vhc2_biayaRinci').'</span><br>');
?>
<div style="margin-bottom: 30px;">
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unitkerja']?></label></td><td>:</td><td><select class='select2' id="kdUnit" name="kdUnit" style="width:150px">
<?php echo $optKodeorg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td><select class='select2' id="periode" name="periode" style="width:150px">
<?php echo $optBatch?></select></td></tr>
<tr><td><td><td><button onclick="zPreview('vhc2_slave_biayaRinci','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
        <!--<button onclick="zPdf('vhc2_slave_biayaRinci','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>-->
        <button onclick="zExcel(event,'vhc2_slave_biayaRinci.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>
</table>
</fieldset>
</div>
<?php
CLOSE_BOX();
OPEN_BOX();
echo "
<div id='printContainer' class='table-scroll' style='overflow:auto;height:420px;max-width:100%'; >
</div>";
?>
<?php
CLOSE_BOX();
echo close_body();
?>