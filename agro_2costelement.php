<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('agro_2costelement').'</span><br>');
?>

<?php

// periode 
$frm[0] = '';
$sOrg = "select distinct substr(tanggal,1,7) as tahun from " . $dbname . ".keu_jurnalht order by tanggal desc";

$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$optPeriode = "";
while ($rOrg = $qOrg->fetch()) {
    $optPeriode.="<option value=" . $rOrg['tahun'] . ">" . $rOrg['tahun'] . "</option>";
}

//$arr0="##kebun0##afdeling0##mandor0##periode0"; 
$arr0 = "##periode0";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php

$title[0] = $_SESSION['lang']['form'];

$frm[0].="<fieldset style=\"float: left;\">
<legend><b>" . $title[0] . "</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr>
    <td><label>" . $_SESSION['lang']['periode'] . "</label></td>
    <td><select id=\"periode0\" name=\"periode0\"  style=\"width:150px\">" . $optPeriode . "</select></td>
</tr>


<tr>
    <td></td><td colspan=\"2\">
        <button onclick=\"zPreview('agro_slave_2costelement0','" . $arr0 . "','printContainer0')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
        <button onclick=\"zExcel(event,'agro_slave_2costelement0.php','" . $arr0 . "')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    </td>    
</tr>    
</table>
</fieldset>";
echo $frm[0];
CLOSE_BOX();
OPEN_BOX();
echo "<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer0' style='overflow:auto;height:400px;max-width:1220px'>
</div></fieldset>";

//========================
// $hfrm[0] = $title[0];
// //draw tab, jangan ganti parameter pertama, krn dipakai di javascript
// drawTab('FRM', $hfrm, $frm, 200, 1100);
// //===============================================


CLOSE_BOX();
echo close_body();
?>