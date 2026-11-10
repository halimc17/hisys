<?php
//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
require_once('master_mainMenu.php');
?>
<style>
/* dropdown select2 di atas dialog (Excel/PDF) */
body > .select2-container{z-index:2100}
/* teks pilihan select2 huruf kapital semua */
.select2-container .select2-selection__rendered, .select2-container .select2-results__option{text-transform:uppercase}
</style>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/zSelect2.js?ver=1'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src='js/sdm_2laporanpph21.js?v=<?php echo time(); ?>'></script>

<?php
## Pilihan unit: Semua Unit (bila lebih dari satu unit) lalu unit dikelompokkan per induk
$arrUnit = getOrgDetail(1);
$optunit = "<option value=''>PILIH DATA</option>";
if (count($arrUnit) > 1) {
	$optunit .= "<option value='ALL'>SEMUA UNIT</option>";
}
$indukSebelumnya = '';
foreach ($arrUnit as $key => $val) {
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
	$indukUnit = $induk[$key];
	if ($indukUnit != $indukSebelumnya) {
		if ($indukSebelumnya != '') {
			$optunit .= "</optgroup>";
		}
		$optunit .= "<optgroup label='" . $indukUnit . " - " . getNamaOrg($indukUnit) . "'>";
		$indukSebelumnya = $indukUnit;
	}

	$terpilih = ($key == $_SESSION['empl']['lokasitugas']) ? " selected" : "";
	$optunit .= "<option value='" . $key . "'" . $terpilih . ">" . $key . " - " . $val . "</option>";
}
if ($indukSebelumnya != '') {
	$optunit .= "</optgroup>";
}

## Pilihan periode (12 periode gaji terakhir)
$optPer = "";
$str = "select distinct periode as periode from " . $dbname . ".sdm_5periodegaji order by periode desc limit 12";
$res = $owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optPer .= "<option value=" . $bar['periode'] . ">" . $bar['periode'] . "</option>";
}

## Pilihan tipe karyawan (tanpa Staff, sama seperti perhitungan PPh 21)
$optTipe = "<option value=''>SEMUA TIPE (SELAIN STAFF)</option>";
$str = "select id,tipe from " . $dbname . ".sdm_5tipekaryawan where aktif='1' and id<>'0' order by id";
$res = $owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optTipe .= "<option value='" . $bar['id'] . "'>" . $bar['tipe'] . "</option>";
}


OPEN_BOX('', '<span class=judul>' . getMenu('sdm_2laporanpph21') . '</span>');

echo "<br>";
echo "<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=unit style=\"width:220px;\">" . $optunit . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=per style=\"width:220px;\">" . $optPer . "</select></td>
                </tr>
                <tr>
                    <td>Tipe Karyawan</td>
                    <td>:</td>
                    <td><select class=select2 id=tipekar style=\"width:220px;\">" . $optTipe . "</select></td>
                </tr>
                <tr>
                    <td></td><td></td>
                    <td>
                        <button onclick=preview('previewawal') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
                        <button onclick=excel() class=mybutton name=excel id=excel>" . $_SESSION['lang']['excel'] . "</button>
                        <button onclick=pdf() class=mybutton name=pdf id=pdf>PDF</button>
                    </td>
                </tr>
            </table>
        </fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<div id='printContainer'></div>";
CLOSE_BOX();

echo close_body();
