<?php
//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?

## GET UNIT
$optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$unit = '';
$arrUnit = getOrgDetail(1);
foreach ($arrUnit as $key => $val) {
    $induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
    $d = $induk[$key];
    if ($d != $n) {
        $optOrg .= "<optgroup label='" . $d . " - " . getNamaOrg($d) . "'>";
    }

    if ($key == $_SESSION['empl']['lokasitugas']) {
        $optOrg .= "<option value='" . $key . "' selected>" . $key . " - " . $val . "</option>";
        $unit = $key;
    } else {
        $optOrg .= "<option value='" . $key . "'>" . $key . " - " . $val . "</option>";
    }
    $n = $d;
    if ($d != $n) {
        $optOrg .= "</optgroup>";
    }
}

$optPer = "";
$iPer = "select distinct periode as periode from " . $dbname . ".sdm_5periodegaji order by periode desc";
$nPer = $owlPDO->query($iPer) or die(print " Gagal: " . PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while ($dPer = $nPer->fetch()) {
    $optPer .= "<option value=" . $dPer['periode'] . ">" . $dPer['periode'] . "</option>";
}

$optTipe = "<option value='a'>" . $_SESSION['lang']['all'] . "</option>";
#tipekaryawan
$sTipe = "select distinct tipekaryawan from  " . $dbname . ".sdm_gaji_vw where left(periodegaji,4)>='2021' order by tipekaryawan asc";
$rTipe = fetchData($sTipe);
if (count($rTipe) != 0) {
    foreach ($rTipe as $brs => $val) {
        if ($val['tipekaryawan'] != '') {
            $optNmTipe = makeOption($dbname, "sdm_5tipekaryawan", "id,tipe", "id='" . $val['tipekaryawan'] . "'");
            $optTipe .= "<option value='" . $val['tipekaryawan'] . "'>" . $optNmTipe[$val['tipekaryawan']] . "</option>";
        }
    }
}

$optJab = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$iPer = "select * from " . $dbname . ".sdm_5jabatan where aktif = 1 order by namajabatan asc";
$nPer = $owlPDO->query($iPer) or die(print " Gagal: " . PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while ($dPer = $nPer->fetch()) {
    $optJab .= "<option value=" . $dPer['kodejabatan'] . ">" . $dPer['namajabatan'] . "</option>";
}

$optTipeGaji = "<option value=0>GAJI BESAR </option>";
$optTipeGaji .= "<option value=1>GAJI KECIL</option>";

OPEN_BOX('', '<span class=judul>' . getMenu('sdm_2aktualrekapgajikaryawan') . '</span>');


echo "<br>";
$arr = "##unit##per##tpKary##jabatan##tipegaji";
echo "<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=unit style=\"width:168px;\">" . $optOrg . "</select></td>
                </tr>
				<tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=per style=\"width:168px;\">" . $optPer . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tipekaryawan'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=tpKary style=\"width:168px;\">" . $optTipe . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['jabatan'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=jabatan style=\"width:168px;\">" . $optJab . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['tipe'] . " Gaji</td>
                    <td>:</td>
                    <td><select class=select2 id=tipegaji style=\"width:168px;\">" . $optTipeGaji . "</select></td>
                </tr>
                <tr>
                    <td><td><td>
                    <button onclick=zPreview('sdm_2aktualrekapgajikaryawan_slave','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                    <button onclick=zExcel(event,'sdm_2aktualrekapgajikaryawan_slave.php','" . $arr . "') class=mybutton name=preview id=preview>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
</fieldset>";

echo "<fieldset style='float:left;'>
        <legend>Info</legend>
            <li>Laporan ini berupa aktual transaksi berjalan, nilai angka yang tampil disini sama dengan nilai hutang gaji di TB</li>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo "<div id='printContainer'></div>";

CLOSE_BOX();
echo close_body();








?>