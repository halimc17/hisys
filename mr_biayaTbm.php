<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
?>
<script language=javascript1.2 src='js/mr_biayaTbm.js'></script>
<script language=javascript src='js/zReport.js'></script>
<? 

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['managerialreport']) . ' : '.getMenu('mr_biayaTbm').'</span>');


$arr = "##pt##unit##periode";
$optpt=$optper =$optDtTp= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
//get existing period
$str = "select distinct periode from " . $dbname . ".setup_periodeakuntansi
    order by periode desc";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){	
    $optper.="<option value='" . $bar->periode . "'>" . $bar->periode . "</option>";
}

$optTp = array(""=>$_SESSION['lang']['all'],"I"=>"Inti","P"=>"Plasma");
foreach($optTp as $lst=>$lstNm){
    $optDtTp.="<option value='".$lst."'>".$lstNm."</option>";
}
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi
    where tipe='PT'
    order by namaorganisasi";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){		
    $optpt.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}

$optunit = "<option value=''></option>";
$frm[0]=$frm[1]='';
$frm[0].="<fieldset style=float:left><legend>" . $_SESSION['lang']['form'] . "</legend>";
$frm[0].="<table cellspacing=1 border=0>
    <tr><td>" . $_SESSION['lang']['perusahaan'] . "</td><td>:</td><td>
    <select id=pt name=pt style='width:150px;' onchange=getkebun()>" . $optpt . "</select></select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['unit'] . "</td><td>:</td><td>
    <select id=unit name=unit style=width:150px; onchange=bersih()>" . $optunit . "</select></select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['periode'] . "</td><td>:</td><td>
    <select id=periode name=periode style=width:150px; onchange=bersih()>" . $optper . "</select></select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['intiplasma'] . "</td><td>:</td><td>
    <select id=intiplasma name=intiplasma style=width:150px; onchange=bersih()>" . $optDtTp . "</select></select>
    </td></tr>
    <tr><td colspan=2></td><td colspan=3>
        <button onclick=\"getpreview()\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['preview'] . "</button>
        <button onclick=\"getexcel(event,'mr_slave_biayaTbm.php')\" class=\"mybutton\" name=\"excel\" id=\"excel\">" . $_SESSION['lang']['excel'] . "</button>    
        <!--<button onclick=\"getpdf(event,'mr_slave_biayaTbm.php')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">" . $_SESSION['lang']['pdf'] . "</button>-->
        <!--<button onclick=\"batal()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">" . $_SESSION['lang']['cancel'] . "</button>-->
    <input type=hidden name=hidden_1 id=hidden_1 value=hiddenvalue1 />
    </td></tr>
</table></fieldset><div style=clear:both></div>";
$frm[0].="<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
		<div id='container' style='overflow:auto;height:350px;max-width:100%'>
		</div></fieldset>";

$frm[1].="<fieldset style=float:left><legend>" . $_SESSION['lang']['form']."</legend>";
$frm[1].="<table cellspacing=1 border=0>
    <tr><td>" . $_SESSION['lang']['perusahaan'] . "</td><td>:</td><td>
    <select id=pt1 name=pt1 style='width:150px;' onchange=getkebun1()>" . $optpt . "</select></select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['unit'] . "</td><td>:</td><td>
    <select id=unit1 name=unit1 style=width:150px; onchange=bersih1()>" . $optunit . "</select></select>
    </td></tr>
    <tr><td>" . $_SESSION['lang']['periode'] . "</td><td>:</td><td>
    <select id=periode1 name=periode1 style=width:150px; onchange=bersih1()>" . $optper . "</select></select>
    </td></tr>
	<tr><td>" . $_SESSION['lang']['intiplasma'] . "</td><td>:</td><td>
    <select id=intiplasma1 name=intiplasma1 style=width:150px; onchange=bersih1()>" . $optDtTp . "</select></select>
    </td></tr>
    <tr><td></td><td></td><td colspan=3>
        <button onclick=\"getpreview1()\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['preview'] . "</button>
        <button onclick=\"getexcel1(event,'mr_slave_biayaTbmDetail.php')\" class=\"mybutton\" name=\"excel\" id=\"excel\">" . $_SESSION['lang']['excel'] . "</button>    
        <!--<button onclick=\"getpdf(event,'mr_slave_biayaTbmDetail.php')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">" . $_SESSION['lang']['pdf'] . "</button>-->
        <!--<button onclick=\"batal()\" class=\"mybutton\" name=\"btnBatal\" id=\"btnBatal\">" . $_SESSION['lang']['cancel'] . "</button>-->
    <input type=hidden name=hidden_1 id=hidden_1 value=hiddenvalue1 />
    </td></tr>
</table></fieldset><div style=clear:both></div>";
$frm[1].="<fieldset><legend>" . $_SESSION['lang']['list']."</legend>
			<div id='container1' style='overflow:auto;height:350px;max-width:100%'>
			</div></fieldset>"; 



//========================
$hfrm[0] = $_SESSION['lang']['biayatbm'] . " " . $_SESSION['lang']['summary'];
$hfrm[1] = $_SESSION['lang']['biayatbm'] . " " . $_SESSION['lang']['detail'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 200, '100%');
//===============================================
CLOSE_BOX();
close_body();
?>