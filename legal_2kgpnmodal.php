<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
?>
<script language=javascript1.2 src='js/legal_2kgpnmodal.js?v=<?= time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
##PT dari organisasi (tipe='PT')
$optpt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PT' order by namaorganisasi asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optpt .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

OPEN_BOX('', '<span class=judul>' . getMenu('legal_2kgpnmodal') . '</span>');
echo "<div id=action_list>";
echo "<table>
	 <tr valign=middle>
	 <td>
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend>
         <table>
			<tr>
				<td>" . $_SESSION['lang']['pt'] . "</td>
				<td>:</td>
				<td><select class=select2 id=ptsrc style=\"width:230px;\">" . $optpt . "</select></td>

				<td>Periode Dari</td>
				<td>:</td>
				<td><input id='periodedari' type='text' value='" . date('01-01-Y') . "' style='width:95px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false'; readonly/></td>

				<td>Periode Sampai</td>
				<td>:</td>
				<td><input id='periodesampai' type='text' value='" . date('d-m-Y') . "' style='width:95px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false'; readonly/></td>
			</tr>
			<tr>
				<td colspan=9 style='font-size:11px;color:#555;'>
					Laporan disusun dari dokumen LKPM yang sudah disimpan pada rentang periode di atas.
					Angka periode dijumlahkan; angka akumulasi diambil dari dokumen terakhir (bersifat kumulatif).
				</td>
			</tr>
			";
echo "<tr><td></td><td></td><td colspan=7>
	<button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
	<button class=mybutton onclick=exportExcelKGPN()>" . $_SESSION['lang']['excel'] . "</button>
	<button class=mybutton onclick=exportPdfKGPN()>PDF</button>
	</td></tr></table>";
echo "</fieldset></td>
	 </tr>
	 </table> ";
echo "</div>";
CLOSE_BOX();

echo "<div id=printArea>";
OPEN_BOX();
echo "<fieldset>
		<legend>Print Area</legend>
		<div id=printContainer0 style='min-height:65vh;' class='table-scroll'>
		</div>
	</fieldset>";
CLOSE_BOX();
echo "</div>";

echo close_body();
?>
