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
</style>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<script language=javascript src='js/zSelect2.js?ver=1'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript1.2 src='js/sdm_3pph21_v3.js?v=<?php echo time(); ?>'></script>

<?php
## Pilihan unit (dikelompokkan per induk)
$optunit = "<option value=''>Pilih Data</option>";
$indukSebelumnya = '';
foreach (getOrgDetail(1) as $key => $val) {
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


OPEN_BOX('', '<span class=judul>' . getMenu('sdm_3pph21_v3') . '</span>');

echo "<br>";
echo "<fieldset style='float:left;'>
        <legend>Form</legend>
            <table border=0 cellpadding=1 cellspacing=1>
                <tr>
                    <td>" . $_SESSION['lang']['unit'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=unit style=\"width:200px;\">" . $optunit . "</select></td>
                </tr>
                <tr>
                    <td>" . $_SESSION['lang']['periode'] . "</td>
                    <td>:</td>
                    <td><select class=select2 id=per style=\"width:200px;\">" . $optPer . "</select></td>
                </tr>
                <tr hidden>
                    <td>Tunjangan Pph21 </td>
                    <td>:</td>
                    <td><input type='checkbox' id='istjpph21' onclick='makeenabled()'></td>
                </tr>
                <tr hidden>
                    <td>Nilai Batas Pendapatan (Pertahun)</td>
                    <td>:</td>
                    <td><input type=text class=myinputtextnumber style=\"width:164px;\" id='nilaitjpph21' onkeypress=\"return angka_doang(event);\" disabled></td>
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
        </fieldset>
        <span>
            <ol style='margin-left:20%'>INFO :</ol>
            <li style='margin-left:25%'>Sebelum melakukan proses PPH21 harap dipastikan :</li>
            <li style='margin-left:25%'>1. Lakukan proses gaji Harian dan Bulan, melalui SDM - Proses - Penggajian Harian / Bulanan.</li>
            <li style='margin-left:25%'>2. Tutup periode penggajian unit, melalui menu SDM - Setup - Periode Penggajian Unit.</li>
            <li style='margin-left:25%'>3. Jalankan proses PPH21</li><br>
        </span>";
CLOSE_BOX();

OPEN_BOX();
echo "<div id='printContainer'></div>";
CLOSE_BOX();

echo close_body();
