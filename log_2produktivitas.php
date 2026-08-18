<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
$optKelompok = makeOption($dbname, 'log_5klbarang', 'kode,kelompok');
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$opKlmpkBrg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sKelompokBrg = "select distinct substr(kodebarang,1,3) as kelompokBrg from " . $dbname . ".log_po_vw order by kodebarang asc";
$qKlmpkBrg=$owlPDO->query($sKelompokBrg) or die(print " Gagal: ".PDOException::getMessage());
$qKlmpkBrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlmplkBrg = $qKlmpkBrg->fetch()) {
    $opKlmpkBrg.="<option value='" . $rKlmplkBrg['kelompokBrg'] . "'>" . $optKelompok[$rKlmplkBrg['kelompokBrg']] . "</option>";
}
$optListUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sListUnit = "select distinct unit as kodeorg from " . $dbname . ".log_prapoht where close='2'";
$qListUnit=$owlPDO->query($sListUnit) or die(print " Gagal: ".PDOException::getMessage());
$qListUnit->setFetchMode(PDO::FETCH_ASSOC);
while ($rListUnit = $qListUnit->fetch()) {
    if (isset($optNmOrg[$rListUnit['kodeorg']]))
        $optListUnit.="<option value='" . $rListUnit['kodeorg'] . "'>" . $optNmOrg[$rListUnit['kodeorg']] . "</option>";
}
$optPeriodeCari = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sPeriodeCari = "select distinct substr(tanggal,1,7) as periode from " . $dbname . ".log_prapoht order by substr(tanggal,1,7) desc";
$qPeriodeCari=$owlPDO->query($sPeriodeCari) or die(print " Gagal: ".PDOException::getMessage());
$qPeriodeCari->setFetchMode(PDO::FETCH_ASSOC);
while ($rPeriodeCari = $qPeriodeCari->fetch()) {
    $optPeriodeCari.="<option value='" . $rPeriodeCari['periode'] . "'>" . $rPeriodeCari['periode'] . "</option>";
}
$optLokal = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$arrPo = array("0" => "Pusat", "1" => "Lokal");
foreach ($arrPo as $brsLokal => $isiLokal) {
    $optLokal.="<option value=" . $brsLokal . ">" . $isiLokal . "</option>";
}
$optStatusPP = "<option value='2'>" . $_SESSION['lang']['pilihdata'] . "</option>";
$stataPP = array("0" => "Belum PO", "1" => $_SESSION['lang']['sdhPO']);
foreach ($stataPP as $dataIni => $listNama) {
    $optStatusPP.="<option value='" . $dataIni . "'>" . $listNama . "</option>";
}
$optFilter = "";
$arrfilter = array("Bulanan" => "Bulanan", "Tahunan" => "Tahunan");
foreach ($arrfilter as $brsfilter => $isifilter) {
    $optFilter.="<option value=" . $brsfilter . ">" . $isifilter . "</option>";
}
$optPur = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sPur = "select karyawanid,namakaryawan from " . $dbname . ".datakaryawan 
where (bagian='PRO'or kodejabatan='17') and kodejabatan!='5' and (tanggalkeluar>='" . date('Y-m-d') . "' or tanggalkeluar='0000-00-00')  order by namakaryawan asc";

$qPur = fetchData($sPur);
foreach ($qPur as $brsKary) {
    $optPur.="<option value=" . $brsKary['karyawanid'] . ">" . $brsKary['namakaryawan'] . "</option>";
}
$arr = "##kdUnit##periode##filter";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src=js/log_2produktivitas.js></script>


<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['lapproduktivitaspur']).'</span>');
?>
<div>
    <fieldset style="float: left;">
        <legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>
        <table cellspacing="1" border="0" >
            <tr><td><label><?php echo $_SESSION['lang']['pt'] ?></label></td><td>:</td><td><select id="kdUnit" name="kdUnit" style="width:150px"><?php echo $optListUnit; ?></select></td></tr>
            <tr><td><label>Filter</label></td><td>:</td><td><select id="filter" name="filter" style="width:150px" onchange="changefilter()"><?php echo $optFilter ?></select></td></tr>
			<tr><td><label><?php echo $_SESSION['lang']['periode'] ?></label></td><td>:</td><td><select id="periode" name="periode" style="width:150px"><?php echo $optPeriodeCari ?></select></td></tr>
            
            
            <tr><td><td><td colspan="2"><button onclick="zPreview('log_2slave_produktivitas', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event, 'log_2slave_produktivitas.php', '<?php echo $arr ?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

        </table>
    </fieldset>
</div>

<?php
CLOSE_BOX();
OPEN_BOX();
?>

<fieldset><legend><b>Print Area</b></legend>
    <div id='printContainer' style='overflow:auto;height:400px;width:100%'>

    </div></fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>