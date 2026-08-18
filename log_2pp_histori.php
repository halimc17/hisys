<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<?php
if ($_SESSION['language'] == 'EN') {
    $zz = 'kelompok1';
} else {
    $zz = 'kelompok';
}

$optKelompok = makeOption($dbname, 'log_5klbarang', "kode," . $zz);
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$opKlmpkBrg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$sKelompokBrg = "select distinct substr(kodebarang,1,3) as kelompokBrg from " . $dbname . ".log_po_vw order by kodebarang asc";
$qKlmpkBrg = $owlPDO->query($sKelompokBrg) or die(print " Gagal: " . PDOException::getMessage());
$qKlmpkBrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlmplkBrg = $qKlmpkBrg->fetch()) {
    $opKlmpkBrg.="<option value='" . $rKlmplkBrg['kelompokBrg'] . "'>" . $rKlmplkBrg['kelompokBrg'] . " - " . $optKelompok[$rKlmplkBrg['kelompokBrg']] . "</option>";
}

if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    $optListUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
    $sListUnit = "select distinct kodeorg from " . $dbname . ".log_prapoht";
} else {
    $sListUnit = "select distinct kodeorg from " . $dbname . ".log_prapoht where kodeorg='" . $_SESSION['empl']['kodeorganisasi'] . "'";
}

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

if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    $optLokal = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
    $arrPo = array("0" => "Head Offcice", "1" => "Local");
} else {
    $arrPo = array("1" => "Local");
}
foreach ($arrPo as $brsLokal => $isiLokal) {
    $optLokal.="<option value=" . $brsLokal . ">" . $isiLokal . "</option>";
}
$optStatusPP = "<option value='4'>" . $_SESSION['lang']['pilihdata'] . "</option>";
$stataPP = array("0" => "On Process", "1" => $_SESSION['lang']['sdhPO'], "2" => "On Proses | Sudah Pembayaran", "3" => "On Proses | Sudah Penerimaan");
foreach ($stataPP as $dataIni => $listNama) {
    $optStatusPP.="<option value='" . $dataIni . "'>" . $listNama . "</option>";
}
$optPur = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

/*
if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    $sPur = "select karyawanid,namakaryawan from " . $dbname . ".datakaryawan 
       where bagian='PRO' 
       and (tanggalkeluar>='" . date('Y-m-d') . "' or tanggalkeluar='0000-00-00')  order by namakaryawan asc";
} else {
    $sPur = "select karyawanid,namakaryawan from " . $dbname . ".datakaryawan 
       where bagian='PRO' and lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "'
       and (tanggalkeluar>='" . date('Y-m-d') . "' or tanggalkeluar='0000-00-00')  order by namakaryawan asc";
}
*/


    $sPur = "select karyawanid,namakaryawan from " . $dbname . ".datakaryawan 
       where bagian='PRO' 
       and (tanggalkeluar>='" . date('Y-m-d') . "' or tanggalkeluar='0000-00-00')  order by namakaryawan asc";

//exit("Error".$sPur);
$qPur = fetchData($sPur);
foreach ($qPur as $brsKary) {
    $optPur.="<option value=" . $brsKary['karyawanid'] . ">" . $brsKary['namakaryawan'] . "</option>";
}
$arr = "##klmpkBrg##kdUnit##periode##periode2##lokasi##statId##purId##nmbarang##keterangan";
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['ppLap']).'</span>');
?>
<div>
    <fieldset style="float: left;">
        <legend><b><?php echo $_SESSION['lang']['form'] ?></b></legend>
        <table cellspacing="1" border="0" >
			<tr><td><label><?php echo $_SESSION['lang']['pt'] ?></label></td><td>:</td><td><select id="kdUnit" name="kdUnit" style="width:164px"><?php echo $optListUnit; ?></select></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['periode'] ?></label></td><td>:</td><td><input type="text" id="periode" name="periode" onmousemove=setCalendar(this.id); style="width:66px" class="myinputtext"> s/d <input type="text" id="periode2" name="periode2" onmousemove=setCalendar(this.id); style="width:66px" class="myinputtext"></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['status'] ?></label></td><td>:</td><td><select id="statId" name="statId" style="width:164px"><?php echo $optStatusPP ?></select></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['kelompokbarang'] ?></label></td><td>:</td><td><select id="klmpkBrg" name="klmpkBrg" style="width:164px"><?php echo $opKlmpkBrg ?></select></td></tr>

            <tr><td><label><?php echo $_SESSION['lang']['namabarang'] ?></label></td><td>:</td><td><input type="text" id="nmbarang" name="nmbarang" style="width:160px" class="myinputtext"></td></tr>

            <tr><td><label><?php echo $_SESSION['lang']['lokasiBeli'] ?></label></td><td>:</td><td><select id="lokasi" name="lokasi" style="width:164px"><?php echo $optLokal ?></select></td></tr>
            <tr><td><label><?php echo $_SESSION['lang']['purchaser'] ?></label></td><td>:</td><td><select id="purId" name="purId" style="width:164px"><?php echo $optPur ?></select></td></tr>
             <tr><td><label><?php echo $_SESSION['lang']['keterangan'] ?></label></td><td>:</td><td><input type="text" id="keterangan" name="keterangan" style="width:160px" class="myinputtext"></td></tr>


         
            <tr><td><td><td colspan="2"><button onclick="zPreview('log_2slave_pp_histori', '<?php echo $arr ?>', 'printContainer')" class="mybutton" name="preview" id="preview">Preview</button><button onclick="zExcel(event, 'log_2slave_pp_histori.php', '<?php echo $arr ?>')" class="mybutton" name="preview" id="preview">Excel</button></td></tr>

        </table>
    </fieldset>
</div>

<?php
CLOSE_BOX();
OPEN_BOX();
?>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
    <div id='printContainer' style='min-height:350px;'>

    </div></fieldset>

<?php
CLOSE_BOX();
echo close_body();
?>