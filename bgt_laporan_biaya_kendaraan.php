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
$sOrg="select namaorganisasi, kodeorganisasi from ".$dbname.".organisasi where  tipe='TRAKSI' order by namaorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
    $optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
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

$optThn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sThn="select distinct  tahunbudget from ".$dbname.".bgt_budget order by tahunbudget desc";
$qThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
$qThn->setFetchMode(PDO::FETCH_ASSOC);
while($rThn=$qThn->fetch())
{
    $optThn.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
}
$arr="##thnBudget##kdUnit";
$arr1="##thnBudget1##kdUnit1";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script>

function Clear1() {
    document.getElementById('thnBudget').value = '';
    document.getElementById('kdUnit').value = '';
    document.getElementById('printContainer').innerHTML = '';
}
function Clear2() {
    document.getElementById('thnBudget1').value = '';
    document.getElementById('kdUnit1').value = '';
    document.getElementById('printContainer1').innerHTML = '';
}
</script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?

$frm[0]="
<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0 >
<tr><td><label>".$_SESSION['lang']['budgetyear']."</label></td><td>:</td><td><select class=select2 id='thnBudget' style=\"width:175px;\">".$optThn."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['kodetraksi']."</label></td><td>:</td><td><select class=select2 id='kdUnit'  style=\"width:175px;\">".$optOrg."</select></td></tr>

<tr><td colspan=3  align=right><button onclick=\"zPreview('bgt_slave_laporan_biaya_kendaraan','".$arr."','printContainer')\" class=mybutton name=preview id=preview>Preview</button>
<!--<button onclick=\"zPdf('bgt_slave_laporan_biaya_kendaraan','".$arr."','printContainer')\" class=mybutton name=preview id=preview>PDF</button>-->
<button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_kendaraan.php','".$arr."')\" class=mybutton name=preview id=preview>Excel</button>
<button onclick=Clear1() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button></td></tr>

</table>
</fieldset>
";

$frm[0].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'>
</div></fieldset>
";

$frm[1]="
<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0 >
<tr><td><label>".$_SESSION['lang']['budgetyear']."</label></td><td>:</td><td><select class=select2 id='thnBudget1' style=\"width:175px;\">".$optThn."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['kodetraksi']."</label></td><td>:</td><td><select class=select2 id='kdUnit1'  style=\"width:175px;\">".$optOrg."</select></td></tr>

<tr><td colspan=3 align=right><button onclick=\"zPreview('bgt_slave_laporan_biaya_kendaraan1','".$arr1."','printContainer1')\" class=mybutton name=preview id=preview>Preview</button>
<button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_kendaraan1.php','".$arr1."')\" class=mybutton name=preview id=preview>Excel</button>
<button onclick=Clear2() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button></td></tr>

</table>
</fieldset>
";

$frm[1].="<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer1' style='overflow:auto;height:400px;max-width:100%'>
</div></fieldset>
";
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_biaya_kendaraan').'</span><br>');
//========================
$hfrm[0]=$_SESSION['lang']['byTraski'];
$hfrm[1]=$_SESSION['lang']['rekap']." ".$_SESSION['lang']['byTraski'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,300,'100%');
//===============================================

CLOSE_BOX();
echo close_body();
?>