<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/keu_3hpp.js?v=<?php echo time(); ?>'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('', '<span class=judul><b>' . getMenu('keu_3hpp') . '</b></span><br>');
$optper = $optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str = "SELECT * FROM " . $dbname . ".organisasi where  tipe='PABRIK' and length(kodeorganisasi)=4 and induk in (select induk from " . $dbname . ".organisasi where tipe='PABRIK')";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optunit .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['namaorganisasi'] . "</option>";
}

$str = "SELECT distinct(periode) as periode FROM " . $dbname . ".setup_periodeakuntansi order by periode desc limit 24";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optper .= "<option value='" . $bar['periode'] . "'>" . $bar['periode'] . "</option>";
}

echo "<fieldset style=width:300px;float:left><legend><b>Form</b></legend>
<table>
    <tr>
		<td>" . $_SESSION['lang']['periode'] . "</td>
		<td>:</td>
		<td><select id=per style=\"width:150px;\" >" . $optper . "</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['unit'] . "</td>
		<td>:</td>
		<td><select id=unit style=\"width:150px;\" >" . $optunit . "</select></td>
	</tr>";
echo "<tr>
		<td></td><td></td>
		<td colspan=3 align=right>
		<button onclick=preview() class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
		<button onclick=excel() class=mybutton name=preview id=excel>" . $_SESSION['lang']['excel'] . "</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>" . $_SESSION['lang']['cancel'] . "</button>
		</td>
	</tr>
</table>
</fieldset>";
/*
echo"<fieldset style=width:800px><legend><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
			<li>Lakukan Proses ini sebelum tutup buku Head Office</li>
			<li>Pastikan semua unit kebun maupun pabrik sudah close keuangannya</li>
			<li>Rumus saldo akhir = saldoawal + produksi - sales - cuci tangki + lost in transit.</li>
			<li>Data Unit yang diambil hanya <b>Internal</b></li>
			<li>Pengaturan COA ada di menu : keuangan->setup->Alokasi, dengan nama <b>HPP</b<</li>
</fieldset>";
*/
CLOSE_BOX();
OPEN_BOX();
echo "<div id='printContainer'  class=table-scroll  style='height:650px;width:100%'></div>";
CLOSE_BOX();
echo close_body();

?>