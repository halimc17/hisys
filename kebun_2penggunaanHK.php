<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where  tipe='KEBUN' order by kodeorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['namaorganisasi']."</option>";
}

$arr="##kdUnit##divisi##periode##intiplasma";
$optModel="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPeriode="select distinct substr(tanggal,1,7) as periode from ".$dbname.".kebun_aktifitas order by tanggal desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch())
{
    $optModel.="<option value='".$rPeriode['periode']."'>".$rPeriode['periode']."</option>";
}

$arrOptIP = getEnum($dbname,'setup_blok','intiplasma');
$optIP = '';
$optIP .= "<option value=''>".$_SESSION['lang']['all']."</option>";
foreach($arrOptIP as $val){
	if($val=="I"){
		$optIP .= "<option value='".$val."'>Inti</option>";
	}else{
		$optIP .= "<option value='".$val."'>Plasma</option>";
	}
}

?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/option.js'></script>

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
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['penggunaanhk']).'</span><br>');
?>
<div>
<fieldset style="float: left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td><select onchange='getdivisi()' id='kdUnit'  style="width:150px;"><?php echo $optOrg?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['divisi']?></label></td><td>:</td><td><select id='divisi'  style="width:150px;"><?php echo $optDiv?></select></td></tr>

<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td><select id='periode'  style="width:150px;"><?php echo $optModel?></select></td></tr>
<?php
				echo"<tr>
					<td>".$_SESSION['lang']['intiplasma']."</td><td>:</td>
					
					<td><select id=intiplasma style=width:150px;>".$optIP."</select></td>
				</tr>";
				?>

<tr><td></td><td><td><button onclick="zPreview('kebun_slave_2penggunaanHK','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zPdf('kebun_slave_2penggunaanHK','<?php echo $arr?>','printContainer')" class="mybutton" name="preview" id="preview">PDF</button><button onclick="zExcel(event,'kebun_slave_2penggunaanHK.php','<?php echo $arr?>')" class="mybutton" name="preview" id="preview">Excel</button>
</td></tr>

</table>
</fieldset>
</div>
<?php

CLOSE_BOX();
OPEN_BOX();
?>

<fieldset style='clear:both;max-width:100%'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:380px;max-width:100%'>

</div></fieldset>

<?php

CLOSE_BOX();
echo close_body();
?>