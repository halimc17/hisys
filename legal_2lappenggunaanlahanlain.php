<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
// OPEN_BOX();
?>
<script language=javascript src='js/legal_2lappenggunaanlahanlain.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php

//=================dropdown PT;
$optpt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$whrpt = getOrgDetail(4);
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PT' and kodeorganisasi in (" . $whrpt . ") order by namaorganisasi";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$optpt .= "<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}

//=================dropdown unit (diisi via getunit slave);
$optunit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

//=================dropdown periode tahun-bulan (diisi via getperiode slave);
$optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$frm[0] = "";
$frm[0] .= "<fieldset style=\"float: left;\">
<legend><b>Laporan Penggunaan Lahan Lainnya</b></legend>
<table cellspacing=\"1\" border=\"0\">
<tr>
	<td>
		<label>" . $_SESSION['lang']['pt'] . "</label>
	</td>
	<td>:</td>
	<td>
		<select id=pt style='width:170px;' onchange=\"getunit()\">" . $optpt . "</select>
	</td>
</tr>
<tr>
	<td>
		<label>" . $_SESSION['lang']['unit'] . "</label>
	</td>
	<td>:</td>
	<td>
		<select id=unit style='width:170px;' onchange=\"getperiode()\">" . $optunit . "</select>
	</td>
</tr>
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
OPEN_BOX('', '<span class=judul>' . getMenu('legal_2lappenggunaanlahanlain') . '</span>');
//========================
$hfrm[0] = "Laporan Penggunaan Lahan Lainnya";
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 200, '100%');
//===============================================

CLOSE_BOX();
echo close_body();
?>
