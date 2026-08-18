<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>
<?php
$sPbk="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
$qPbk=$owlPDO->query($sPbk) or die(print " Gagal: ".PDOException::getMessage());
$qPbk->setFetchMode(PDO::FETCH_ASSOC);
$optPabrik="";
while($rPbk=$qPbk->fetch())
{
	$optPabrik.="<option value=".$rPbk['kodeorganisasi'].">".$rPbk['namaorganisasi']."</option>";
}
$arrOptIntex=array("External","Internal","Afliasi");
$optBuah="";
foreach($arrOptIntex as $isi =>$tks)
{
	$optBuah.="<option value=".$isi." >".$tks."</option>";
}
 
$arr="##tglAwal##tglAkhir##statBuah##kdPbrk##suppId##kdOrg";
?>
<script>
optInt="<option value=''><? echo $_SESSION['lang']['all']?></option>";
optExt="<option value=''><? echo $_SESSION['lang']['all']?></option>";
</script>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>

<script language=javascript src='js/pabrik_2laporanSortasiPabrik2.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['laporanSortasi']).' V2</span>');
?>
<div style="margin-bottom: 30px;">
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['kdpabrik']?></label></td><td><select id="kdPbrk" name="kdPbrk" style="width:172px;"  ><option value=""><?php echo $_SESSION['lang']['all']?></option><?php echo $optPabrik?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['statusBuah']?></label></td><td><select id="statBuah" name="statBuah" style="width:172px;" onchange="getKbn()" ><option value="5"><?php echo $_SESSION['lang']['all']?></option><?php echo $optBuah?></select></td></tr>
<tr> 	 
			<td style='valign:top'><?php echo $_SESSION['lang']['kebun']?> </td><td>
			<select id="kdOrg" name="kdOrg"  style="width:172px;"><option value=''><?php echo $_SESSION['lang']['all'];?></option></select></td>
			</tr>
			<tr> 	 
			<td style='valign:top'><?php echo $_SESSION['lang']['namasupplier'] ?> </td><td>
			<select id="suppId" name="suppId"  style="width:172px;"><option value=''><?php echo $_SESSION['lang']['all'];?></option></select></td>
			</tr>
<tr><td><label><?php echo $_SESSION['lang']['tanggal']?></label></td><td><input type="text" class="myinputtext" id="tglAwal" name="tglAwal" onmousemove="setCalendar(this.id)" onkeypress="return false; " size="10" maxlength="4" style="width:70px;" />
s/d
<input type="text" class="myinputtext" id="tglAkhir" name="tglAkhir" onmousemove="setCalendar(this.id)" onkeypress="return false; " size="10" maxlength="4" style="width:70px;" /></td></tr>
<tr><td><td><button onclick="zPreview('pabrik_slave_2laporanSortasiPabrik2','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><!--<button onclick="zPdf('pabrik_slave_2laporanSortasiPabrik2','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>--><button onclick="zExcel(event,'pabrik_slave_2laporanSortasiPabrik2.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>
</table>
</fieldset>
</div>

<?php
CLOSE_BOX();
OPEN_BOX();
?>

<fieldset style='clear:both;max-width:1235px'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1235px'>

</div></fieldset>
<?php
CLOSE_BOX();
echo close_body();
?>