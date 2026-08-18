<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zFunction.php');
include('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
$frm[0]='';
$frm[1]='';
$frm[2]='';
$frm[3]='';
$frm[4]='';

?>
<script>
pilh=" <? echo $_SESSION['lang']['pilihdata'] ?>";
</script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script>
dataKdvhc="<?php echo $_SESSION['lang']['pilihdata']?>";
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
    document.getElementById('pilTampilan').value = '';
    document.getElementById('thnBudget_sebaran').innerHTML = '<option value=>' + dataKdvhc + '</option>';
    document.getElementById('printContainer3').innerHTML = '';
    document.getElementById('pdfSbrn').disabled = false;
}
function Clear5() {
    document.getElementById('thnBudgetCst').value = '';
    document.getElementById('kdUnitCst').value = '';
    document.getElementById('printContainer5').innerHTML = '';
}
function getTahunTanam() {

    pil = document.getElementById('pilTampilan').options[document.getElementById('pilTampilan').selectedIndex].value;
    th = document.getElementById('kdUnit_sebaran').options[document.getElementById('kdUnit_sebaran').selectedIndex].value;
    thh = document.getElementById('thnBudget_sebaran').options[document.getElementById('thnBudget_sebaran').selectedIndex].value;
    param = 'kdUnit_sebaran=' + th + '&thnBudget_sebaran=' + thh + '&pilTampilan=' + pil;
    if (th == '' || thh == '') {
        alert("Tahun Budget dan Unit Tidak Boleh Kosong");
        return;
    }
    tujuan = 'bgt_slave_laporan_biaya_lngs_kebunSbrn.php';
    post_response_text(tujuan + '?proses=getThnTanam', param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('thnTanamSeb').innerHTML = con.responseText;
                    if (pil != '') {
                        document.getElementById('pdfSbrn').disabled = true;
                    } else {
                        document.getElementById('pdfSbrn').disabled = false;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
</script>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where CHAR_LENGTH(kodeorganisasi)='4' and tipe='KEBUN' order by namaorganisasi asc";
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
$arr="##thnBudget##kdUnit";
$arr2="##thnBudget_afd##kdUnit_afd";
$arr3="##thnBudget_sebaran##kdUnit_sebaran##pilTampilan##thnTanamSeb";
$arr5="##thnBudgetCst##kdUnitCst";
$arr6="##thnBudgetRincian##kdUnitRincian";

OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_biaya_lngs_kebun').'</span><br>');
$frm[0].="<fieldset ><legend>".$_SESSION['lang']['thntnm']."</legend>";
$frm[0].="<table cellspacing=1 border=0>
<tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
<select class=select2 id='thnBudget' style='width:175px;'>".$optThn."</select></td></tr>
<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select class=select2 id='kdUnit'  style=\"width:175px;\">".$optOrg."</select></td></tr>
<tr>
	<td></td>
	<td></td>
	<td colspan=3>
	<button onclick=\"zPreview('bgt_slave_laporan_biaya_lngs_kebun','".$arr."','printContainer')\" class=\"mybutton\" >Preview</button>
    <!--<button onclick=\"zPdf('bgt_slave_laporan_biaya_lngs_kebun','".$arr."','printContainer')\" class=\"mybutton\">PDF</button>-->
    <button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_lngs_kebun.php','".$arr."')\" class=\"mybutton\" >Excel</button>
    <button onclick=\"Clear1()\" class=\"mybutton\" >".$_SESSION['lang']['cancel']."</button></td></tr></table>
";
$frm[0].="</fieldset><fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'>

</div></fieldset>";

$frm[1].="<fieldset ><legend>".$_SESSION['lang']['afdeling']."</legend>";
$frm[1].="<table cellspacing=1 border=0>
<tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
<select class=select2 id='thnBudget_afd' style='width:175px;'>".$optThn."</select></td></tr>
<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select class=select2 id='kdUnit_afd'  style=\"width:175px;\">".$optOrg."</select></td></tr>
<tr><td></td>
	<td></td>
	<td colspan=3>
	<button onclick=\"zPreview('bgt_slave_laporan_biaya_lngs_kebunAfd','".$arr2."','printContainer2')\" class=\"mybutton\" >Preview</button>
    <!--<button onclick=\"zPdf('bgt_slave_laporan_biaya_lngs_kebunAfd','".$arr2."','printContainer2')\" class=\"mybutton\">PDF</button>-->
    <button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_lngs_kebunAfd.php','".$arr2."')\" class=\"mybutton\" >Excel</button>
    <button onclick=\"Clear2()\" class=\"mybutton\" >".$_SESSION['lang']['cancel']."</button></td></tr></table>
";
$frm[1].="</fieldset><fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer2' style='overflow:auto;height:400px;width:100%'>

</div></fieldset>";
$arrPilDat=array("1"=>"Per Tahun Tanam");
$optPilD="<option value=''>Default</option>";
$optthntanam="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrPilDat as $trPil=>$ertl)
{
    $optPilD.="<option value='".$trPil."'>".$ertl."</option>";
}


$frm[2].="<fieldset ><legend>".$_SESSION['lang']['sebaran']."</legend>";
$frm[2].="<table cellspacing=1 border=0>
<tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
<select class=select2 id='thnBudget_sebaran' style='width:175px;'>".$optThn."</select></td></tr>
<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select class=select2 id='kdUnit_sebaran'  style=\"width:175px;\">".$optOrg."</select></td></tr>
<tr><td>Pilih Tampilan</td><td>:</td><td><select class=select2 id='pilTampilan' onchange=getTahunTanam()  style=\"width:175px;\">".$optPilD."</select></td></tr>
<tr style=display:none><td>".$_SESSION['lang']['thntnm']."</td><td>:</td><td><select class=select2 id='thnTanamSeb'  style=\"width:175px;\">".$optthntanam."</select></td></tr>
<tr><td></td>
	<td></td>
	<td colspan=3>
	<button onclick=\"zPreview('bgt_slave_laporan_biaya_lngs_kebunSbrn','".$arr3."','printContainer3')\" class=\"mybutton\" >Preview</button>
    <!--<button onclick=\"zPdf('bgt_slave_laporan_biaya_lngs_kebunSbrn','".$arr3."','printContainer3')\" class=\"mybutton\" id=pdfSbrn>PDF</button>-->
    <button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_lngs_kebunSbrn.php','".$arr3."')\" class=\"mybutton\" >Excel</button>
    <button onclick=\"Clear3()\" class=\"mybutton\" >".$_SESSION['lang']['cancel']."</button></td></tr></table>
";
$frm[2].="</fieldset><fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer3' style='overflow:auto;height:400px;max-width:100%'>

</div></fieldset>";
//echo $sOrgs;
$optKd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//$_SESSION['empl']['tipelokasitugas']=='HOLDING'?$sKd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'":$sKd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$sKd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
$qList=$owlPDO->query($sKd) or die(print " Gagal: ".PDOException::getMessage());
$qList->setFetchMode(PDO::FETCH_ASSOC);
while($rKd=$qList->fetch())
{
    $optKd.="<option value='".$rKd['kodeorganisasi']."'>".$rKd['kodeorganisasi']." - ".$rKd['namaorganisasi']."</option>";
}
$frm[3].="<fieldset ><legend>".$_SESSION['lang']['costelement']."</legend>";
$frm[3].="<table cellspacing=1 border=0>
<tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
<select class=select2 id='thnBudgetCst' style='width:175px;'>".$optThn."</select></td></tr>
<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select class=select2 id='kdUnitCst'  style=\"width:175px;\">".$optOrg."</select></td></tr>
<tr><td></td>
	<td></td>
	<td colspan=3>
	<button onclick=\"zPreview('bgt_slave_laporan_biaya_lngs_kebunCst','".$arr5."','printContainer5')\" class=\"mybutton\" >Preview</button>
    <!--<button onclick=\"zPdf('bgt_slave_laporan_biaya_lngs_kebunCst','".$arr5."','printContainer5')\" class=\"mybutton\">PDF</button>-->
    <button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_lngs_kebunCst.php','".$arr5."')\" class=\"mybutton\" >Excel</button>
    <button onclick=\"Clear5()\" class=\"mybutton\" >".$_SESSION['lang']['cancel']."</button></td></tr></table>
";
$frm[3].="</fieldset><fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer5' style='overflow:auto;height:400px;max-width:100%'>

</div></fieldset>";


//echo $sOrgs;
$optKd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
//$_SESSION['empl']['tipelokasitugas']=='HOLDING'?$sKd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'":$sKd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
$sKd="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='KEBUN'";
$qList=$owlPDO->query($sKd) or die(print " Gagal: ".PDOException::getMessage());
$qList->setFetchMode(PDO::FETCH_ASSOC);
while($rKd=$qList->fetch())
{
    $optKd.="<option value='".$rKd['kodeorganisasi']."'>".$rKd['namaorganisasi']."</option>";
}
$frm[4].="<fieldset ><legend>Budget Rincian</legend>";
$frm[4].="<table cellspacing=1 border=0>
<tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td>
<select class=select2 id='thnBudgetRincian' style='width:175px;'>".$optThn."</select></td></tr>
<tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select class=select2 id='kdUnitRincian'  style=\"width:175px;\">".$optOrg."</select></td></tr>
<tr><td colspan=2></td><td colspan=3>
<button onclick=\"zPreview('bgt_slave_laporan_biaya_lngs_kebunRincian','".$arr6."','printContainer6')\" class=\"mybutton\" >Preview</button>
    <!--<button onclick=\"zPdf('bgt_slave_laporan_biaya_lngs_kebunRincian','".$arr6."','printContainer6')\" class=\"mybutton\">PDF</button>
    <button onclick=\"Clear6()\" class=\"mybutton\" >".$_SESSION['lang']['cancel']."</button>-->
    <button onclick=\"zExcel(event,'bgt_slave_laporan_biaya_lngs_kebunRincian.php','".$arr6."')\" class=\"mybutton\" >Excel</button></td></tr></table>
";
$frm[4].="</fieldset><fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer6' style='overflow:auto;height:400px;max-width:100%'>

</div></fieldset>";
//========================
$hfrm[0]=$_SESSION['lang']['thntnm'];
$hfrm[1]=$_SESSION['lang']['afdeling'];
$hfrm[2]=$_SESSION['lang']['sebaran'];
$hfrm[3]=$_SESSION['lang']['costelement'];
$hfrm[4]="Budget Rincian";

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