<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi)='4' and tipe='KEBUN' order by kodeorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(23) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}


$optThn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sThn="select distinct  tahunbudget from ".$dbname.".bgt_budget order by tahunbudget desc";
$qThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
$qThn->setFetchMode(PDO::FETCH_ASSOC);
while($rThn=$qThn->fetch())
{
    $optThn.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
}
$arr="##thnBudget##kdUnit##modPil";
$optModel="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrModel=array("0"=>$_SESSION['lang']['tahuntanam']."/".$_SESSION['lang']['afdeling'],"1"=>$_SESSION['lang']['detail'],"3"=>$_SESSION['lang']['tahuntanam'],"4"=>$_SESSION['lang']['blok']."/".$_SESSION['lang']['sebaran']);
foreach($arrModel as $listModel=>$dtModel)
{
    $optModel.="<option value='".$listModel."'>".$dtModel."</option>";
}
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script>
function Clear1()
{
    document.getElementById('thnBudget').value='';
    document.getElementById('kdUnit').value='';
    document.getElementById('printContainer').innerHTML='';
}
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_produksi').'</span>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['budgetyear']?></label></td><td><select class=select2 id='thnBudget' style="width:170px;"><?php echo $optThn?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td><select class=select2 id='kdUnit'  style="width:170px;"><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['list']?> By.</label></td><td><select class=select2 id='modPil'  style="width:170px;"><?php echo $optModel?></select></td></tr>

<tr><td colspan="2" align=right><button onclick="zPreview('bgt_slave_laporan_produksi','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button>
<!--<button onclick="zPdf('bgt_slave_laporan_produksi','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button>--><button onclick="zExcel(event,'bgt_slave_laporan_produksi.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>

</table>
</fieldset>
</div>
<?php
CLOSE_BOX();
OPEN_BOX();
?>

<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'></div>

<?php

CLOSE_BOX();
echo close_body();
?>