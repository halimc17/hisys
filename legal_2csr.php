<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/legal_2csr.js?v=<?= time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>

<?php
##deklarasi untuk option##
$optorg = $optkat = "<option value=''>Seluruhnya</option>";
$where = "";
if ($_SESSION['empl']['tipelokasitugas'] != 'HOLDING') {
	$where = " and kodeorganisasi = '" . $_SESSION['empl']['lokasitugas'] . "'";
}
$str = "select namaorganisasi,kodeorganisasi from " . $dbname . ".organisasi where length(kodeorganisasi)=4 " . $where . " order by namaorganisasi asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optorg .= "<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$str = "select * from " . $dbname . ".lgl_kategoribansos order by nama asc ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optkat .= "<option value=" . $bar['kode'] . ">" . $bar['kode'] . " - " . $bar['nama'] . "</option>";
}

OPEN_BOX('', '<span class=judul>' . getMenu('legal_2csr') . '</span>');
echo "<div id=action_list>";
echo "<table>
	 <tr valign=middle>
	 <td>
		<fieldset><legend>" . $_SESSION['lang']['find'] . "</legend> 
         <table>
			<tr>
				<td>" . $_SESSION['lang']['kodeorganisasi'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=divsch  style=\"width:170px;\">" . $optorg . "</select></td>
				
				<td>" . $_SESSION['lang']['tanggalmulai'] . "</td> 
				<td>:</td>
				<td><input id='tanggalmulai' type='text' value='" . date('01-m-Y') . "' style='width:95px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/></td>
				
				<td>" . $_SESSION['lang']['tanggalsampai'] . "</td> 
				<td>:</td>
				<td><input id='tanggalsampai' type='text' value='" . date('d-m-Y') . "' style='width:95px;' class='myinputtext' onmousemove='setCalendar(this.id)' onkeypress='return false';  readonly/></td>
				
				<td>" . $_SESSION['lang']['kategori'] . "</td> 
				<td>:</td>
				<td><select class=select2 id=katsrc  style=\"width:170px;\">" . $optkat . "</select></td>
			</tr>
			";
echo "<tr><td></td><td></td><td colspan=11><button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button><button class=mybutton onclick=exportExcelCSR()>" . $_SESSION['lang']['excel'] . "</button><button class=mybutton onclick=exportPdfCSR()>PDF</button></td></tr></table>";
echo "</fieldset></td>
	 </tr>
	 </table> ";
echo "</div>";
CLOSE_BOX();
echo "<div id=listData style=display:block>";
OPEN_BOX();
echo "<fieldset>
		<legend>" . $_SESSION['lang']['list'] . "</legend>
		<div>    
		<table cellpadding=5 cellspacing=1 border=0 class=sortable style=width:100%>
		<thead>
			<tr class=rowheader>
				<td align=center>" . $_SESSION['lang']['nourut'] . "</td>
				<td align=center>Tanggal Dokumen Dikeluarkan</td>
				<td align=center>Tanggal Dokumen Berakhir</td>
				<td align=center>Bentuk Kegiatan</td>
				<td align=center>Nilai (Rp)</td>
				<td align=center>Waktu Pelaksanaan</td>
				<td align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
				<td align=center>" . $_SESSION['lang']['kodeorg'] . "</td>
				<td align=center>" . $_SESSION['lang']['kategori'] . "</td>
		</thead>
		 <tbody id=contain> 
		 </tbody>
		<tfoot id=footData>
		 </tfoot>
		 </table>
		 </div>
	</fieldset>";
CLOSE_BOX();
echo "</div>";
echo close_body();
?>
