<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
$optNamaOrganisasi=array();
$optUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
$optOrg="<select id=kdPt name=kdPt style=\"width:200px;\" onchange=getUnit() ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select distinct kodept from ".$dbname.".log_transaksi_vw where tipetransaksi=1 order by kodept asc ";	
$res=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$res->fetch()){
	$optNamaOrganisasi=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$rOrg['kodept']."'");
 	$optOrg.="<option value=".$rOrg['kodept'].">".$rOrg['kodept']."-".$optNamaOrganisasi[$rOrg['kodept']]."</option>";
}
$optOrg.="</select>"; 

//$arr="##kdOrg##tgl1##tgl2##statTagihan";
$arr="##kdPt##unitId##tgl1";
  

?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/keu_2apschedule.js?v=1.8'></script>
<script language=javascript>
notifpopilih="<?php echo $_SESSION['lang']['notifpopilih']; ?>";
notiftagihtanggal="<?php echo $_SESSION['lang']['notiftagihtanggal']; ?>";
notifpostingpenagihan="<?php echo $_SESSION['lang']['notifpostingpenagihan']; ?>";
</script>


<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper(getMenu('keu_2apschedule')).'</span><br>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['pt']?></label></td><td>:</td><td><?php echo $optOrg?></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td><select id=unitId><?php echo $optUnit?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td>:</td><td><input type="text" class="myinputtext" id="tgl1" name="tgl1" onmousemove="setCalendar(this.id)" onkeypress="return false;" readonly=readonly  maxlength="10" value='<?php echo date('d-m-Y');?>' style="width:150px;" /></td></tr>
<tr><td><td><td><button onclick="zPreview('keu_slave_2apschedule','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event,'keu_slave_2tagihan.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

</table>
</fieldset>
</div>

<div style="margin-bottom: 30px;">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:680px;width:auto;'>

</div></fieldset>

<script language=javascript>zPreview('keu_slave_2apschedule','<?php echo $arr?>','printContainer')</script>
<?php

CLOSE_BOX();
echo close_body();
?>