<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
// OPEN_BOX();
?>
<script language=javascript src='js/legal_2lpsipditjenbun.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php

//=================dropdown periode (tahun) dari tanggal timbangan;
$optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct substr(tanggal,1,4) as tahun from " . $dbname . ".pabrik_timbangan where tanggal!='' order by tanggal desc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$optPeriode .= "<option value='" . $bar->tahun . "'>" . $bar->tahun . "</option>";
}

//=================dropdown unit (kebun) dengan optgroup per PT;
$optUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
foreach (getOrgDetail(23) as $key => $val) {
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
	$d = $induk[$key];
	if ($d != $n) {
		$optUnit .= "<optgroup label='" . $d . " - " . getNamaOrg($d) . "'>";
	}
	$optUnit .= "<option value='" . $key . "'>" . $key . " - " . $val . "</option>";
	$n = $d;
	if ($d != $n) {
		$optUnit .= "</optgroup>";
	}
}

//=================dropdown inti/plasma;
$optIntiplasma = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optIntiplasma .= "<option value='I'>Inti</option>";
$optIntiplasma .= "<option value='P'>Plasma</option>";

//=================dropdown jenis produksi (barang produksi yang ada di timbangan);
$optJenis = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct b.kodebarang, b.namabarang from " . $dbname . ".pabrik_timbangan a
		join " . $dbname . ".log_5masterbarang b on b.kodebarang=a.kodebarang
		where b.kodebarang like '4000%'
		order by a.kodebarang asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$optJenis .= "<option value='" . $bar->kodebarang . "'>" . $bar->kodebarang . " - " . $bar->namabarang . "</option>";
}

$frm[0] = "";
$frm[0] .= "<fieldset style=\"float: left;\">
<legend><b>Laporan Produksi Kebun SIP DITJENBUN</b></legend>
<table cellspacing=\"1\" border=\"0\">
<tr>
	<td>
		<label>" . $_SESSION['lang']['periode'] . "</label>
	</td>
	<td>:</td>
	<td>
		<select id=periode style='width:170px;'>" . $optPeriode . "</select>
	</td>
</tr>
<tr>
	<td>
		<label>" . $_SESSION['lang']['unit'] . "</label>
	</td>
	<td>:</td>
	<td>
		<select id=unit style='width:170px;'>" . $optUnit . "</select>
	</td>
</tr>
<tr>
	<td>
		<label>" . $_SESSION['lang']['intiplasma'] . "</label>
	</td>
	<td>:</td>
	<td>
		<select id=intiplasma style='width:170px;'>" . $optIntiplasma . "</select>
	</td>
</tr>
<tr>
	<td>
		<label>Jenis Produksi</label>
	</td>
	<td>:</td>
	<td>
		<select id=jenis style='width:170px;'>" . $optJenis . "</select>
	</td>
</tr>
<tr>
	<td colspan=2></td>
	<td>
		<button onclick=\"loaddata('preview')\" class=\"mybutton\" name=\"preview\" id=\"preview\">" . $_SESSION['lang']['preview'] . "</button>
		<button onclick=\"loaddata('excel')\" class=\"mybutton\" name=\"excel\" id=\"excel\">" . $_SESSION['lang']['excel'] . "</button>
		<button onclick=batal() class=\"mybutton\" name=\"batal\" id=\"batal\">" . $_SESSION['lang']['cancel'] . "</button>
	</td>
</tr>
</table>
</fieldset>

<div style=\"margin-bottom: 30px;\">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id=showHeader>
<div id='printContainer0' style='min-height:65vh;' class='table-scroll'>
</div>
</div>

</fieldset>";
OPEN_BOX('', '<span class=judul>' . getMenu('legal_2lpsipditjenbun') . '</span>');
//========================
$hfrm[0] = "Laporan Produksi Kebun SIP DITJENBUN";
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 200, '100%');
//===============================================

CLOSE_BOX();
echo close_body();
?>
