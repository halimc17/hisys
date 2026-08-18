<?
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
<!-- JS ga di pake karena pake zReport.js -->
<!-- Jika ada tambahan fitur bisa pake JS, seperti onchange, dlsb -->
<script language=javascript src=js/kebun_2summaryupahpanen.js?ver=<?= time(); ?>></script>

<?php
#= Make Option
$tipekaryawan = makeOption($dbname, "sdm_5tipekaryawan", "id,tipe");

#= Set Default Option
$optUnit  = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optDiv   = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optTipe   = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

#= untuk unit ht
$arrunit = array();
$arrunit = getOrgDetail(23);
foreach ($arrunit as $val => $nama) {
    // if($val == "")
    $optUnit .= "<option value='" . $val . "'>" . $val . " - " . $nama . "</option>";
    $arrkodeunit[$val] = $val;
}

#= Option Tipe Karyawan
$qTipe = selectQuery($dbname, "sdm_5tipekaryawan", "id", " aktif='1' and id!=0");
$rTipe = $owlPDO->query($qTipe) or die(print " Gagal: " . PDOException::getMessage());
$rTipe->setFetchMode(PDO::FETCH_ASSOC);
while ($resTipe = $rTipe->fetch()) {
    $optTipe .= "<option value=" . $resTipe['id'] . ">" . $tipekaryawan[$resTipe['id']] . "</option>";
}

$kegiatan = makeOption($dbname, "setup_klpkegiatan", "kodeklp,namakelompok");

// kegiiatan
$optkegiatan = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$lksiTugas = substr($_SESSION['empl']['lokasitugas'], 0, 4);
$skegiatan = "select distinct kelompok from " . $dbname . ".setup_kegiatan WHERE `kelompok` IN ('TB','BBT','TBM','TM','PNN') order by kelompok desc";
$qkegiatan = $owlPDO->query($skegiatan) or die(print " Gagal: " . PDOException::getMessage());
$qkegiatan->setFetchMode(PDO::FETCH_ASSOC);
while ($rkegiatan = $qkegiatan->fetch()) {
    $optkegiatan .= "<option value=" . $rkegiatan['kelompok'] . ">" . $kegiatan[$rkegiatan['kelompok']] . "</option>";
}


OPEN_BOX('', '<span class=judul>' . getMenu('kebun_2summaryupahpanen') . '</span>');

$hfrm[0] = "Kegiatan Panen";
$hfrm[1] = "Kegiatan Rawat";
$hfrm[2] = "Kegiatan BM TBS";
$hfrm[3] = "Kegiatan Traksi";
$hfrm[4] = "Kegiatan Umum";
$hfrm[5] = "Semua Data";

for ($i = 0; $i < 6; $i++) {
    $frm[$i] = "
        <fieldset>
            <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id='unit" . $i . "' onchange=\"getDivisi(" . $i . ")\" style=\"width:168px;\">" . $optUnit . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['divisi'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id='div" . $i . "' style=\"width:168px;\">" . $optDiv . "</select></td>
                </tr>
                <tr>
                    <td>Dari " . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td>
                        <input type=\"text\" readonly=\"readonly\" class=\"myinputtext\" style=\"width:165px;padding:2px 0px\" id=\"tgl" . $i . "\" onmousemove=\"setCalendar(this.id);\" onkeypress=\"return false;\" maxlength=\"10\" autocomplete=\"off\" />
                    </td>
                </tr>
                <tr>
                    <td>Sampai " . $_SESSION['lang']['tanggal'] . "</td>
                    <td>:</td>
                    <td>
                        <input type=\"text\" readonly=\"readonly\" class=\"myinputtext\" style=\"width:165px;padding:3px 0px\" id=\"tglx" . $i . "\" onmousemove=\"setCalendar(this.id);\" onkeypress=\"return false;\" maxlength=\"10\" autocomplete=\"off\" />
                    </td>
                </tr>
                <tr>
                    <td colspan=3>
                        <button onclick=\"loadData(" . $i . ")\" class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                        <button onclick=\"loadData(" . $i . ", 'excel')\" class=mybutton name=excel id=excel>" . $_SESSION['lang']['excel'] . "</button>
                    </td>
                </tr>
            </table>
        </fieldset>
        <div id='printContainer" . $i . "' class='z-freeze-scroll' style='overflow:auto;height:60vh;'></div>
        ";
}

drawTab('FRM', $hfrm, $frm, 150, '100%');

CLOSE_BOX();
close_body();
?>