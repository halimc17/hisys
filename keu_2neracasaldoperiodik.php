<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/keu_laporan.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/keu_2neracasaldoperiodik.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
echo "<script language=javascript src=js/zSelect2.js?ver=1></script>";
OPEN_BOX('', '<span class=judul><b>' . getMenu('keu_2neracasaldoperiodik') . '</b></span><br>');

#Tahun (dari periode akuntansi yang ada)
$optTahun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$rTahun = fetchdata("select distinct left(periode,4) as tahun from " . $dbname . ".setup_periodeakuntansi order by tahun desc");
$tahunSkrg = date('Y');
$adaTahunSkrg = false;
foreach ($rTahun as $rt) {
	if ($rt['tahun'] == $tahunSkrg) {
		$adaTahunSkrg = true;
	}
}
if (!$adaTahunSkrg) {
	$optTahun .= "<option value='" . $tahunSkrg . "'>" . $tahunSkrg . "</option>";
}
foreach ($rTahun as $rt) {
	$optTahun .= "<option value='" . $rt['tahun'] . "'" . ($rt['tahun'] == $tahunSkrg ? ' selected' : '') . ">" . $rt['tahun'] . "</option>";
}

$optrev = '';
for ($i = 0; $i <= 5; $i++) {
	$optrev .= "<option value='" . $i . "'>" . $i . "</option>";
}

if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' || $_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
	$optpt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	foreach (fetchdata("select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PT' order by namaorganisasi") as $bar) {
		$optpt .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
	}
	$optgudang = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
} else {
	$optpt = "<option value='" . $_SESSION['empl']['kodeorganisasi'] . "'>" . $_SESSION['empl']['kodeorganisasi'] . "</option>";
	$optgudang = "<option value='" . $_SESSION['empl']['lokasitugas'] . "'>" . $_SESSION['empl']['lokasitugas'] . "</option>";
}
$optReg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

$CLM = '';
$rClm = fetchdata("select noakundebet from " . $dbname . ".keu_5parameterjurnal where kodeaplikasi='CLM'");
if (count($rClm) > 0) {
	$CLM = $rClm[0]['noakundebet'];
}
$optakun = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach (fetchdata("select noakun,namaakun from " . $dbname . ".keu_5akun where level='5' and noakun!='" . $CLM . "' order by noakun") as $bar) {
	$optakun .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
}

$optTampilan = "<option value='0'>Tampilkan Nol</option><option value='1'>Tidak Tampilkan Nol</option>";

echo "<fieldset style=float:left>
	<legend>" . $_SESSION['lang']['form'] . "</legend>
	<table border=0 cellpadding=3>
	<colgroup><col style='width:105px'><col style='width:10px'><col style='width:220px'><col style='width:24px'><col style='width:105px'><col style='width:10px'><col style='width:220px'></colgroup>
	<tr>
		<td>" . $_SESSION['lang']['pt'] . "</td>
		<td>:</td>
		<td><select class='select2' id=pt style='width:220px;' onchange=getReg()>" . $optpt . "</select></td>
		<td></td>
		<td>" . $_SESSION['lang']['regional'] . "</td>
		<td>:</td>
		<td><select class='select2' id=regional style='width:220px;' onchange=getUnit()>" . $optReg . "</select></td>
	</tr><tr>
		<td>" . $_SESSION['lang']['unit'] . "</td>
		<td>:</td>
		<td><select class='select2' id=gudang style='width:220px'>" . $optgudang . "</select></td>
		<td></td>
		<td>Tahun</td>
		<td>:</td>
		<td><select class='select2' id=tahun style='width:220px'>" . $optTahun . "</select></td>
	</tr><tr>
		<td>" . $_SESSION['lang']['noakun'] . "</td>
		<td>:</td>
		<td><select class='select2' id=akundari style='width:220px;'>" . $optakun . "</select></td>
		<td></td>
		<td>" . $_SESSION['lang']['noakunsampai'] . "</td>
		<td>:</td>
		<td><select class='select2' id=akunsampai style='width:220px;'>" . $optakun . "</select></td>
	</tr><tr>
		<td>" . $_SESSION['lang']['revisi'] . "</td>
		<td>:</td>
		<td><select class='select2' id=revisi style='width:220px'>" . $optrev . "</select></td>
		<td></td>
		<td>" . $_SESSION['lang']['statussaldo'] . "</td>
		<td>:</td>
		<td><select class='select2' id=tampilanId style='width:220px'>" . $optTampilan . "</select></td>
	</tr><tr>
		<td colspan=3 style='padding-top:6px'>
			<button onclick=preview() class=mybutton id=preview>" . $_SESSION['lang']['preview'] . "</button>
			<button onclick=excelNsp() class=mybutton id=excel>" . $_SESSION['lang']['excel'] . "</button>
			<button onclick=pdfNsp() class=mybutton id=pdfnsp>PDF</button>
			<button onclick=batal() class=mybutton id=btnBatal>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
	</tr></table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX('', '');
echo "<div class='table-scroll' style='width:100%;height:400px;overflow:scroll;' id=printContainer></div>";
CLOSE_BOX();
echo "<script>if (window.nspIsiSisaLayar) { nspIsiSisaLayar(); }</script>";
echo close_body();
?>
