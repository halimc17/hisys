<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
// Query Data PT
$optPt = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPt = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PT' order by namaorganisasi asc";
try {
    $qPt = $owlPDO->query($sPt);
    if ($qPt) {
        $qPt->setFetchMode(PDO::FETCH_ASSOC);
        while ($rPt = $qPt->fetch()) {
            $optPt .= "<option value='" . $rPt['kodeorganisasi'] . "'>" . $rPt['kodeorganisasi'] . " - " . $rPt['namaorganisasi'] . "</option>";
        }
    }
} catch (Exception $e) {}

// Query Data Periode Akuntansi
$optPeriode = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sPer = "select distinct(periode) as periode from " . $dbname . ".setup_periodeakuntansi order by periode desc limit 36";
try {
    $qPer = $owlPDO->query($sPer);
    if ($qPer) {
        $qPer->setFetchMode(PDO::FETCH_ASSOC);
        while ($rPer = $qPer->fetch()) {
            $optPeriode .= "<option value='" . $rPer['periode'] . "'>" . $rPer['periode'] . "</option>";
        }
    }
} catch (Exception $e) {}

$arr = "##pt##periode";

OPEN_BOX('', '<span class=judul>' . getMenu('kebun_2realisasibiayakebun') . '</span>');

echo "<fieldset>
<legend>" . $_SESSION['lang']['find'] . "</legend>
<table border=0 cellpadding=1 cellspacing=1>
    <tr>
        <td>" . $_SESSION['lang']['pt'] . "</td>
        <td>:</td>
        <td><select class=select2 id='pt' style='width:220px;'>" . $optPt . "</select></td>
    </tr>
    <tr>
        <td>" . $_SESSION['lang']['periode'] . "</td>
        <td>:</td>
        <td><select class=select2 id='periode' style='width:220px;'>" . $optPeriode . "</select></td>
    </tr>
    <tr>
        <td colspan=2></td>
        <td>
            <button onclick=\"zPreview('kebun_slave_2realisasibiayakebun','" . $arr . "','printContainer')\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['preview'] . "</button>
            <button onclick=\"zExcel(event,'kebun_slave_2realisasibiayakebun.php','" . $arr . "')\" class=\"mybutton\" name=\"excel\" id=\"excel\">" . $_SESSION['lang']['excel'] . "</button>
        </td>
    </tr>
</table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo "<div id='both_report'>
    <div id='head_tableboth' align=right>
        <a class='fc_btn mybutton' idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' >
            <img title='Full Screen' class='resicon' src='images/full-screen.png'>
        </a>
        <a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' >
            <img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
        </a>
    </div>
    <div>
        <div id='printContainer'></div>
    </div>
</div>";

CLOSE_BOX();
echo close_body();
?>
