<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
$frm[0]='';
$frm[1]='';
$frm[2]='';
$frm[3]='';

?>
<script>
pilh=" <? echo $_SESSION['lang']['pilihdata'] ?>";
</script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>

<script>
dataKdvhc = "<?php echo $_SESSION['lang']['pilihdata']?>";
function Clear1() {
    document.getElementById('thnBudget').value = '';
    document.getElementById('kdUnit').value = '';
    document.getElementById('printContainer').innerHTML = '';
}
function Clear2() {
    document.getElementById('thnBudget_afd').value = '';
    document.getElementById('kdUnit_afd').value = '';
    document.getElementById('printContainer2').innerHTML = '';
}
function Clear3() {
    document.getElementById('thnBudget_sebaran').value = '';
    document.getElementById('kdUnit_sebaran').value = '';
    document.getElementById('printContainer3').innerHTML = '';
}
function Clear5() {
    document.getElementById('thnBudgetCst').value = '';
    document.getElementById('kdUnitCst').value = '';
    document.getElementById('printContainer5').innerHTML = '';
}
</script>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi)='4' and tipe='KEBUN' order by kodeorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch())
{
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
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
$arr2="##thnBudget_afd##kdUnit_afd";
$arr3="##thnBudget_sebaran##kdUnit_sebaran";
$arr5="##thnBudgetCst##kdUnitCst";

OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_biaya_lngs_kebun2').'</span><br>');
$frm[0].="<fieldset style=\"float: left;\"><legend>".$_SESSION['lang']['thntnm']."</legend>";
$frm[0].="<table cellspacing=1 border=0>
<tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
<select id='thnBudget' style='width:150px;'>".$optThn."</select></td></tr>
<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select id='kdUnit'  style=\"width:150px;\">".$optOrg."</select></td></tr>
<tr><td colspan=3>
<button onclick=\"zPreview('bgt_slave_laporan_biaya_lngs_kebun2','".$arr."','printContainer')\" class=\"mybutton\" >Preview</button>
    <button onclick=\"zPdf('bgt_slave_laporan_biaya_lngs_kebun2','".$arr."','printContainer')\" class=\"mybutton\">PDF</button>
    <button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_lngs_kebun2.php','".$arr."')\" class=\"mybutton\" >Excel</button>
    <button onclick=\"Clear1()\" class=\"mybutton\" >".$_SESSION['lang']['cancel']."</button></td></tr></table>
";
$frm[0].="</fieldset><fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:100%'>

</div></fieldset>";

$frm[1].="<fieldset style=\"float: left;\"><legend>".$_SESSION['lang']['afdeling']."</legend>";
$frm[1].="<table cellspacing=1 border=0>
<tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
<select id='thnBudget_afd' style='width:150px;'>".$optThn."</select></td></tr>
<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select id='kdUnit_afd'  style=\"width:150px;\">".$optOrg."</select></td></tr>
<tr><td colspan=3>
<button onclick=\"zPreview('bgt_slave_laporan_biaya_lngs_kebunAfd2','".$arr2."','printContainer2')\" class=\"mybutton\" >Preview</button>
    <button onclick=\"zPdf('bgt_slave_laporan_biaya_lngs_kebunAfd2','".$arr2."','printContainer2')\" class=\"mybutton\">PDF</button>
    <button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_lngs_kebunAfd2.php','".$arr2."')\" class=\"mybutton\" >Excel</button>
    <button onclick=\"Clear2()\" class=\"mybutton\" >".$_SESSION['lang']['cancel']."</button></td></tr></table>
";
$frm[1].="</fieldset><fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer2' style='overflow:auto;height:350px;width:100%'>

</div></fieldset>";



$frm[2].="<fieldset style=\"float: left;\"><legend>".$_SESSION['lang']['sebaran']."</legend>";
$frm[2].="<table cellspacing=1 border=0>
<tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
<select id='thnBudget_sebaran' style='width:150px;'>".$optThn."</select></td></tr>
<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select id='kdUnit_sebaran'  style=\"width:150px;\">".$optOrg."</select></td></tr>
<tr><td colspan=3>
<button onclick=\"zPreview('bgt_slave_laporan_biaya_lngs_kebunSbrn','".$arr3."','printContainer3')\" class=\"mybutton\" >Preview</button>
    <button onclick=\"zPdf('bgt_slave_laporan_biaya_lngs_kebunSbrn','".$arr3."','printContainer3')\" class=\"mybutton\">PDF</button>
    <button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_lngs_kebunSbrn.php','".$arr3."')\" class=\"mybutton\" >Excel</button>
    <button onclick=\"Clear3()\" class=\"mybutton\" >".$_SESSION['lang']['cancel']."</button></td></tr></table>
";
$frm[2].="</fieldset><fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer3' style='overflow:auto;height:350px;max-width:100%'>

</div></fieldset>";
//echo $sOrgs;
$optKd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$_SESSION['empl']['tipelokasitugas']=='HOLDING'?$sKd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'":$sKd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$qList=$owlPDO->query($sKd) or die(print " Gagal: ".PDOException::getMessage());
$qList->setFetchMode(PDO::FETCH_ASSOC);
while($rKd=$qList->fetch())
{
    $optKd.="<option value='".$rKd['kodeorganisasi']."'>".$rKd['namaorganisasi']."</option>";
}
$frm[3].="<fieldset style=\"float: left;\"><legend>".$_SESSION['lang']['costelement']."</legend>";
$frm[3].="<table cellspacing=1 border=0>
<tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
<select id='thnBudgetCst' style='width:150px;'>".$optThn."</select></td></tr>
<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select id='kdUnitCst'  style=\"width:150px;\">".$optOrg."</select></td></tr>
<tr><td colspan=3>
<button onclick=\"zPreview('bgt_slave_laporan_biaya_lngs_kebunCst2','".$arr5."','printContainer5')\" class=\"mybutton\" >Preview</button>
    <button onclick=\"zPdf('bgt_slave_laporan_biaya_lngs_kebunCst2','".$arr5."','printContainer5')\" class=\"mybutton\">PDF</button>
    <button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_lngs_kebunCst2.php','".$arr5."')\" class=\"mybutton\" >Excel</button>
    <button onclick=\"Clear5()\" class=\"mybutton\" >".$_SESSION['lang']['cancel']."</button></td></tr></table>
";
$frm[3].="</fieldset><fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer5' style='overflow:auto;height:350px;max-width:100%'>

</div></fieldset>";

//========================
$hfrm[0]=$_SESSION['lang']['thntnm'];
$hfrm[1]=$_SESSION['lang']['afdeling'];
$hfrm[2]=$_SESSION['lang']['sebaran'];
$hfrm[3]=$_SESSION['lang']['costelement'];

//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,150,'100%');
//===============================================	
?>


<?php
CLOSE_BOX();
echo"</div>";
echo close_body();
?>