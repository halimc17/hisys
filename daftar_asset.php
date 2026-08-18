<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
include('lib/zFunction.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');

$frm[0] = '';
$frm[1] = '';
$frm[2] = '';
$frm[3] = '';
$frm[4] = '';
$frm[5] = '';
$frm[6] = '';
?>
<script language="javascript" src="js/zMaster.js"></script>
<script type="text/javascript" src="js/daftar_asset.js?v=<?php echo time(); ?>"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sOrg = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PT'";
$qOrg=$owlPDO->query($sOrg);
while ($rOrg = $qOrg->fetch(PDO::FETCH_ASSOC)) {
    $optOrg.="<option value='" . $rOrg['kodeorganisasi'] . "'>" . $rOrg['namaorganisasi'] . "</option>";
}

echo"<div id='formIsian' style='display:block;'>";
OPEN_BOX('','<span class=judul>'.getMenu('daftar_asset').'</span>');

// Tab 0: Bangunan
$frm[0].="<fieldset><legend>Report</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['pt'] . " : </td>
        <td><select id=statCari2 class=select2 multiple style=\"width:200px;\">" . $optOrg . "</select></td>
    <td>
    <button class=mybutton id=btn0 name=btn0 onclick=loadData1()>" . $_SESSION['lang']['find'] . "</button>
    <button class=mybutton id=btnExcel0 name=btnExcel0 onclick=loadData1('excel')>" . $_SESSION['lang']['excel'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<div id=containData1 style='overflow:auto;width:100%;height:450px'>
    </div></fieldset>
		";

// Tab 1: Kapasitas Bangunan
$frm[1].="<fieldset><legend>Report</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['pt'] . " : </td>
        <td><select id=statCari3 class=select2 multiple style=\"width:200px;\">" . $optOrg . "</select></td>
    <td>
    <button class=mybutton id=btn1 name=btn1 onclick=loadData2()>" . $_SESSION['lang']['find'] . "</button>
    <button class=mybutton id=btnExcel1 name=btnExcel1 onclick=loadData2('excel')>" . $_SESSION['lang']['excel'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<div id=containData2 style='overflow:auto;width:100%;height:450px'>
    </div></fieldset>
		";

// Tab 2: Mesin
$frm[2].="<fieldset><legend>Report</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['pt'] . " : </td>
        <td><select id=statCari4 class=select2 multiple style=\"width:200px;\">" . $optOrg . "</select></td>
    <td>
    <button class=mybutton id=btn2 name=btn2 onclick=loadData3()>" . $_SESSION['lang']['find'] . "</button>
    <button class=mybutton id=btnExcel2 name=btnExcel2 onclick=loadData3('excel')>" . $_SESSION['lang']['excel'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<div id=containData3 style='overflow:auto;width:100%;height:450px'>
    </div></fieldset>
		";

// Tab 3: Transport
$frm[3].="<fieldset><legend>Report</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['pt'] . " : </td>
        <td><select id=statCari5 class=select2 multiple style=\"width:200px;\">" . $optOrg . "</select></td>
    <td>
    <button class=mybutton id=btn3 name=btn3 onclick=loadData4()>" . $_SESSION['lang']['find'] . "</button>
    <button class=mybutton id=btnExcel3 name=btnExcel3 onclick=loadData4('excel')>" . $_SESSION['lang']['excel'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<div id=containData4 style='overflow:auto;width:100%;height:450px'>
    </div></fieldset>
		";

// Tab 4: Alat Berat
$frm[4].="<fieldset><legend>Report</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['pt'] . " : </td>
        <td><select id=statCari6 class=select2 multiple style=\"width:200px;\">" . $optOrg . "</select></td>
    <td>
    <button class=mybutton id=btn4 name=btn4 onclick=loadData5()>" . $_SESSION['lang']['find'] . "</button>
    <button class=mybutton id=btnExcel4 name=btnExcel4 onclick=loadData5('excel')>" . $_SESSION['lang']['excel'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<div id=containData5 style='overflow:auto;width:100%;height:450px'>
    </div></fieldset>
		";

// Tab 5: Riwayat Service (Placeholder)
$frm[5].="<fieldset><legend>Report</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['pt'] . " : </td>
        <td><select id=statCari7 class=select2 multiple style=\"width:200px;\">" . $optOrg . "</select></td>
    <td>
    <button class=mybutton id=btn5 name=btn5 onclick=loadData6()>" . $_SESSION['lang']['find'] . "</button>
    <button class=mybutton id=btnExcel5 name=btnExcel5 onclick=loadData6('excel')>" . $_SESSION['lang']['excel'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<div id=containData6 style='overflow:auto;width:100%;height:450px'>
    </div></fieldset>
		";

// Tab 6: Infrastruktur
$frm[6].="<fieldset><legend>Report</legend>
    <fieldset style=float:left><table cellpadding=1 cellspacing=1 border=0>
    <tr>
        <td>" . $_SESSION['lang']['pt'] . " : </td>
        <td><select id=statCari8 class=select2 multiple style=\"width:200px;\">" . $optOrg . "</select></td>
    <td>
    <button class=mybutton id=btn6 name=btn6 onclick=loadData7()>" . $_SESSION['lang']['find'] . "</button>
    <button class=mybutton id=btnExcel6 name=btnExcel6 onclick=loadData7('excel')>" . $_SESSION['lang']['excel'] . "</button>
    </td></tr>
    </table></fieldset><div style=clear:both></div>
	<div id=containData7 style='overflow:auto;width:100%;height:450px'>
    </div></fieldset>
		";

$hfrm[0] = "Bangunan";
$hfrm[1] = "Kapasitas Bangunan";
$hfrm[2] = $_SESSION['lang']['mesin'];
$hfrm[3] = $_SESSION['lang']['transport'];
$hfrm[4] = "Alat Berat";
$hfrm[5] = "Riwayat Service Alber";
$hfrm[6] = "Infrastruktur";

drawTab('FRM', $hfrm, $frm, 180, '100%');

CLOSE_BOX();
echo "</div>";
echo close_body();
?>