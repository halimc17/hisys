<? //@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src='js/keu_2mutasibank.js?v=<?php echo time(); ?>'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<?
include('master_mainMenu.php');
OPEN_BOX('', '<span class=judul><b>' . getMenu('keu_2mutasibank') . '</b></span><br>');
$optpt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optunit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optper = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$str = "select distinct(periode) as periode from " . $dbname . ".setup_periodeakuntansi order by periode desc";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optper .= "<option value='" . $bar['periode'] . "'>" . $bar['periode'] . "</option>";
}

$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PT' order by namaorganisasi";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optpt .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['namaorganisasi'] . "</option>";
}

$opttipelaporan = "<option value='default'>" . $_SESSION['lang']['default'] . "</option>";
$opttipelaporan .= "<option value='detail'>" . $_SESSION['lang']['detail'] . "</option>";

$optTampilDt0 = "<option value='1'>Tampilkan 0</option>";
$optTampilDt0 .= "<option value='0'>Tidak Tampilkan 0</option>";

echo "<fieldset style=float:left>
    <legend>" . $_SESSION['lang']['form'] . "</legend>
		<table>
			<tr>
				<td>" . $_SESSION['lang']['pt'] . "</td>
				<td>:</td>
				<td><select class='select2' id=kodept onchange=getunit(); style='width:200px;'>" . $optpt . "</select></td>
				
				<td>" . $_SESSION['lang']['periode'] . "</td>
				<td>:</td>
				<td><select class='select2' id=periode style='width:200px;'>" . $optper . "</select></td>
			</tr>	
			<tr>
				<td>" . $_SESSION['lang']['unit'] . "</td>
				<td>:</td>
				<td><select class='select2' id=kodeunit style='width:200px;'>" . $optunit . "</select></td>
				
				<td>Digit (Desimal)</td>
				<td>:</td>
				<td><input class=myinputtextnumber id=digit value=2 style=\"width: 195px;\" onkeypress='return_tanpa_kutip_dan_sepasi(event)' /></td>
			</tr>
		
			<tr hidden>
				<td>" . $_SESSION['lang']['tipe'] . " " . $_SESSION['lang']['laporan'] . "</td>
				<td>:</td>
				<td><select class='select2' id=tipelaporan style='width:200px;'>" . $opttipelaporan . "</select></td>
			</tr>
			
			<tr>
				<td></td>
				<td></td>
				<td><button class=mybutton onclick=getlaporan('html')>" . $_SESSION['lang']['preview'] . "</button>
				<button class=mybutton onclick=getlaporan('excel')>" . $_SESSION['lang']['excel'] . "</button>
				<button hidden class=mybutton onclick=getlaporan('pdf')>" . $_SESSION['lang']['pdf'] . "</button></td>

				<td>Status Saldo</td>
				<td>:</td>
				<td><select class='select2' id=tampilData0 style='width:200px;'>" . $optTampilDt0 . "</select></td>
			</tr>
		</table>
	
    </fieldset>";
CLOSE_BOX();
OPEN_BOX('', '');
echo "
    <div id=container  class='table-scroll'  style='width:100%;height:450px;overflow:auto;'>
    </div>";
CLOSE_BOX();
close_body();
?>