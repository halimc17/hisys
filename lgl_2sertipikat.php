<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi','',2);
$optListUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sListUnit = "select distinct kodept from " . $dbname . ".lgl_sertipikat";
$qListUnit=$owlPDO->query($sListUnit) or die(print " Gagal: ".PDOException::getMessage());
$qListUnit->setFetchMode(PDO::FETCH_ASSOC);
while ($rListUnit = $qListUnit->fetch()) {
    if (isset($optNmOrg[$rListUnit['kodept']]))
        $optListUnit.="<option value='" . $rListUnit['kodept'] . "'>" . $optNmOrg[$rListUnit['kodept']] . "</option>";
}
$optPeriodeCari = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sPeriodeCari = "select distinct substr(masaberlaku,1,7) as periode from " . $dbname . ".lgl_sertipikat where masaberlaku!='' order by substr(masaberlaku,1,7) desc";
$qPeriodeCari=$owlPDO->query($sPeriodeCari) or die(print " Gagal: ".PDOException::getMessage());
$qPeriodeCari->setFetchMode(PDO::FETCH_ASSOC);
while ($rPeriodeCari = $qPeriodeCari->fetch()) {
    $optPeriodeCari.="<option value='" . $rPeriodeCari['periode'] . "'>" . $rPeriodeCari['periode'] . "</option>";
}
$optFilter = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$arrfilter = makeOption($dbname,'lgl_5jenissertipikat','nama,nama');
foreach ($arrfilter as $brsfilter => $isifilter) {
    $optFilter.="<option value=" . $brsfilter . ">" . $isifilter . "</option>";
}
$arr = "##kdUnit##periode##filter";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/lgl_2sertipikat.js></script>


<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.getMenu('lgl_2sertipikat').'</span>');
?>
<div>
    <fieldset style="float: left;">
        <legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>
        <table cellspacing="1" border="0" >
            <tr><td><label><?php echo $_SESSION['lang']['pt'] ?></label></td><td>:</td><td><select id="kdUnit" name="kdUnit" style="width:150px"><?php echo $optListUnit; ?></select></td></tr>
            <tr><td><label>Filter</label></td><td>:</td><td><select id="filter" name="filter" style="width:150px"><?php echo $optFilter ?></select></td></tr>
			<tr><td><label><?php echo $_SESSION['lang']['periode'] ?></label></td><td>:</td><td><select id="periode" name="periode" style="width:150px"><?php echo $optPeriodeCari ?></select></td></tr>
            
            
            <tr><td><td><td colspan="2"><button onclick="zPreview('lgl_2slave_sertipikat', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event, 'lgl_2slave_sertipikat.php', '<?php echo $arr ?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

        </table>
    </fieldset>
</div>

<?php
CLOSE_BOX();
OPEN_BOX();
?>

<fieldset style='clear:both;max-width:1235px'><legend><b>Print Area</b></legend>
    <div id='printContainer' style='overflow:auto;height:400px;width:100%'>

    </div></fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>