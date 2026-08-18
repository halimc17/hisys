<?
//@Copy nangkoelframework
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
?>
<script language=javascript1.2 src='js/mr_biayaProduksiTbs.js'></script>
<script language=javascript src='js/zReport.js'></script>
<?

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['managerialreport']) . ' : '.getMenu('mr_biayaProduksiTbs').'</span>');
$optper = $frm[0] = $frm[1] = $frm[2] = '';
$optunit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optafd = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$arr = "##pt##unit##afdeling##periode";
$arr1 = "##pt1##unit1##afdeling1##periode1";
$arr2 = "##pt2##unit2##afdeling2##periode2";

//get existing period
$str = "select distinct periode from " . $dbname . ".setup_periodeakuntansi
    order by periode desc";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optper.="<option value='" . $bar->periode . "'>" . $bar->periode . "</option>";
}

$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi
    where tipe='PT'
    order by namaorganisasi";

$optpt = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
    $optpt.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}


$frm[0].="<fieldset style=float:left><legend>" . $_SESSION['lang']['form'] . "</legend>";
$frm[0].="<table cellspacing=1 border=0>
    <tr><td>" . $_SESSION['lang']['perusahaan'] . "</td><td>:</td><td>
    <select id=pt name=pt style=width:150px; onchange=getkebun()>" . $optpt . "</select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['unit'] . "</td><td>:</td><td>
    <select id=unit name=unit style=width:150px; onchange=getafdeling()>" . $optunit . "</select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['afdeling'] . "</td><td>:</td><td>
    <select id=afdeling name=afdeling style=width:150px; onchange=bersih()>" . $optafd . "</select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['periode'] . "</td><td>:</td><td>
    <select id=periode name=periode style=width:150px; onchange=bersih()>" . $optper . "</select>
    </td></tr>
    <tr><td>Inti/Plasma</td><td>:</td><td>
    <select id=inti name=inti style=width:150px; onchange=bersih()>
        <option value=''>" . $_SESSION['lang']['all'] . "</option>
        <option value='inti'>Inti</option>
        <option value='plasma'>Plasma</option>
    </select>
    </td></tr>
    <tr><td></td><td></td><td colspan=3>
        <button onclick=\"getpreview()\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['preview'] . "</button>
        <button onclick=\"getexcel(event,'mr_slave_biayaProduksiTbs.php')\" class=\"mybutton\" name=\"excel\" id=\"excel\">" . $_SESSION['lang']['excel'] . "</button>    
        <!--<button onclick=\"getpdf(event,'mr_slave_biayaTm.php')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">" . $_SESSION['lang']['pdf'] . "</button>-->
        <!--<button onclick=\"batal()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">" . $_SESSION['lang']['cancel'] . "</button>-->
    <input type=hidden name=hidden id=hidden value=hiddenvalue />
    </td></tr>
</table></fieldset><div style=clear:both></div>";
$frm[0].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
			<div id='container' style='overflow:auto;height:350px;max-width:100%'>
			</div></fieldset>";

$frm[1].="<fieldset style=float:left><legend>" . $_SESSION['lang']['form'] . "</legend>";
$frm[1].="<table cellspacing=1 border=0>
    <!--<tr><td>" . $_SESSION['lang']['perusahaan'] . "</td><td>:</td><td>
    <select id=pt1 name=pt1 style=width:200px; onchange=getkebun1()>" . $optpt . "</select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['unit'] . "</td><td>:</td><td>
    <select id=unit1 name=unit1 style=width:150px; onchange=getafdeling1()>" . $optunit . "</select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['afdeling'] . "</td><td>:</td><td>
    <select id=afdeling1 name=afdeling1 style=width:150px; onchange=bersih1()>" . $optafd . "</select>
    </td></tr>-->
    <tr><td>" . $_SESSION['lang']['periode'] . "</td><td>:</td><td>
    <select id=periode1 name=periode1 style=width:150px; onchange=bersih1()>" . $optper . "</select>
    </td></tr>
    <tr><td></td><td><td colspan=3>
        <button onclick=\"getpreview1()\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['preview'] . "</button>
        <button onclick=\"getexcel1(event,'mr_slave_biayaProduksiTbsPT.php')\" class=\"mybutton\" name=\"excel\" id=\"excel\">" . $_SESSION['lang']['excel'] . "</button>    
        <!--<button onclick=\"getpdf(event,'mr_slave_biayaTm.php')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">" . $_SESSION['lang']['pdf'] . "</button>-->
        <!--<button onclick=\"batal()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">" . $_SESSION['lang']['cancel'] . "</button>-->
    <input type=hidden name=hidden1 id=hidden1 value=hiddenvalue1 />
    </td></tr>
</table></fieldset><div style=clear:both></div>";
$frm[1].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
		  <div id='container1' style='overflow:auto;height:350px;max-width:100%'>
          </div></fieldset>";

$frm[2].="<fieldset  style=float:left><legend>" . $_SESSION['lang']['form'] . "</legend>";
$frm[2].="<table cellspacing=1 border=0>
    <tr><td>" . $_SESSION['lang']['perusahaan'] . "</td><td>:</td><td>
    <select id=pt2 name=pt2 style=width:150px; onchange=getkebun2()>" . $optpt . "</select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['unit'] . "</td><td>:</td><td>
    <select id=unit2 name=unit2 style=width:150px; onchange=getafdeling2()>" . $optunit . "</select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['afdeling'] . "</td><td>:</td><td>
    <select id=afdeling2 name=afdeling2 style=width:150px; onchange=bersih2()>" . $optafd . "</select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['periode'] . "</td><td>:</td><td>
    <select id=periode2 name=periode2 style=width:150px; onchange=bersih2()>" . $optper . "</select>
    </td></tr>
    <tr><td>Inti/Plasma</td><td>:</td><td>
    <select id=inti2 name=inti2 style=width:150px; onchange=bersih()>
        <option value=''>" . $_SESSION['lang']['all'] . "</option>
        <option value='inti'>Inti</option>
        <option value='plasma'>Plasma</option>
    </select>
    </td></tr>
    <tr><td></td><td><td colspan=3>
        <button onclick=\"getpreview2()\" class=\"mybutton\" name=\"preview2\" id=\"preview2\">" . $_SESSION['lang']['preview'] . "</button>
        <button onclick=\"getexcel2(event,'mr_slave_biayaProduksiTbsDetail.php')\" class=\"mybutton\" name=\"excel2\" id=\"excel2\">" . $_SESSION['lang']['excel'] . "</button>    
        <!--<button onclick=\"getpdf(event,'mr_slave_biayaTm.php')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">" . $_SESSION['lang']['pdf'] . "</button>-->
        <!--<button onclick=\"batal()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">" . $_SESSION['lang']['cancel'] . "</button>-->
    <input type=hidden name=hidden2 id=hidden2 value=hiddenvalue />
    </td></tr>
</table></fieldset><div style=clear:both></div>";
$frm[2].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
			<div id='container2' style='overflow:auto;height:350px;max-width:100%'>
			</div></fieldset>";

//========================
$hfrm[0] = $_SESSION['lang']['biaya'] . " " . $_SESSION['lang']['produksitbs'];
$hfrm[1] = $_SESSION['lang']['biaya'] . " " . $_SESSION['lang']['produksitbs'] . " per PT";
$hfrm[2] = $_SESSION['lang']['biaya'] . " " . $_SESSION['lang']['produksitbs'] . " " . $_SESSION['lang']['detail'] . "";
//$hfrm[1] = $_SESSION['lang']['biayatbm'] . " " . $_SESSION['lang']['detail'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 200, '100%');
//===============================================
CLOSE_BOX();
close_body();
?>