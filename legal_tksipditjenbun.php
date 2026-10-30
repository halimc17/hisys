<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
// OPEN_BOX();
?>
<script language=javascript src='js/legal_tksipditjenbun.js?v=<?php echo time(); ?>'></script>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php

//=================ambil PT;
$optpt = $optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$whrpt = getOrgDetail(4);
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PT' and kodeorganisasi in (" . $whrpt . ") order by namaorganisasi";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$optpt .= "<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}
$optunit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optstatus = "<option value='aktif'>Aktif</option>";
$optstatus .= "<option value='tidakaktif'>Tidak Aktif</option>";
$optstatus .= "<option value='seluruhnya'>Seluruhnya</option>";

$frm[0] = "";
$frm[0] .= "<fieldset style=\"float: left;\">
<legend><b>Summary Karyawan TK SIP DITJENBUN</b></legend>
<table cellspacing=\"1\" border=\"0\">
<tr>
	<td>
		<label>" . $_SESSION['lang']['pt'] . "</label>
	</td>
	<td>:</td>
	<td>
		<select id=pt style='width:170px;' onchange=getunit()>" . $optpt . "</select>
	</td>
</tr>
<tr>
	<td>
		<label>" . $_SESSION['lang']['unit'] . "</label>
	</td>
	<td>:</td>
	<td>
		<select id=unit style='width:170px;' onchange=getperiode()>" . $optunit . "</select>
	</td>
</tr>
<tr>
	<td>
		<label>Periode</label>
	</td>
	<td>:</td>
	<td>
		<select id=periode style='width:170px;'>" . $optPeriode . "</select>
	</td>
</tr>
<tr>
	<td>
		<label>Status</label>
	</td>
	<td>:</td>
	<td>
		<select id=status style='width:170px;'>" . $optstatus . "</select>
	</td>
</tr>
<tr>
	<td colspan=2></td>
	<td>
		<button onclick=\"loaddata('preview')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
		<button onclick=\"loaddata('excel')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
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
<div id='printContainer1' style='min-height:auto;' class='table-scroll'>
</div>
</div>

</fieldset>";
OPEN_BOX('', '<span class=judul>' . getMenu('legal_tksipditjenbun') . '</span>');
//========================
$hfrm[0] = "Summary Karyawan TK SIP DITJENBUN";
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 200, '100%');
//===============================================

CLOSE_BOX();
echo close_body();
?>
