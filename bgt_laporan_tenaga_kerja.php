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
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi)='4' order by kodeorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(1) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".substr($key,0,4)."'");
	$d=$induk[substr($key,0,4)];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

$arr="##kdUnit";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<script language=javascript>

function Clear1() {
		document.getElementById('kdUnit').value='';	
		document.getElementById('printContainer').innerHTML='';
}


</script>


<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_tenaga_kerja').'</span><br>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['kodeorganisasi']?></label></td><td>:</td><td><select class=select2 id='kdUnit'><?php echo $optOrg?></select></td></tr>

<tr><td colspan=2></td><td colspan="3"><button onclick="zPreview('bgt_slave_laporan_tenaga_kerja','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['preview']?></button><button onclick="zPdf('bgt_slave_laporan_tenaga_kerja','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['pdf']?></button><button onclick="zExcel(event,'bgt_slave_laporan_tenaga_kerja.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview"><?php echo $_SESSION['lang']['excel']?></button><button onclick="Clear1()" class="mybutton" name="btnBatal" id="btnBatal"><?php echo $_SESSION['lang']['cancel']?></button></td></tr>

</table>
</fieldset>
</div>
<?
CLOSE_BOX();
OPEN_BOX();
?>

<div id='printContainer' style='overflow:auto;height:450px;'>
<?php
//echo"<pre>";
//print_r($_SESSION);
//echo"</pre>";
?>
</div>

<?php

CLOSE_BOX();
echo close_body();
?>