<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zDatatables.php');
require_once('lib/zSelect2Lite.php');

OPEN_BOX('', '<span class=judul>' . getMenu('kebun_2laporananalisaproduksi') . '</span><br>');

$arr="##kodeorg##tanggal";

$optPT = "<option value = 'all'>".$_SESSION['lang']['all']."</option>";

$str = selectQuery($dbname, 'organisasi', 'kodeorganisasi, namaorganisasi', "tipe='PT'");
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $s = "";
    if ($_SESSION['empl']['kodeorganisasi'] == $bar['kodeorganisasi']) {
        #$s="selected";
    }
    $optPT .= "<option value=" . $bar['kodeorganisasi'] . " " . $s . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<script language=javascript src=js/kebun_2laporananalisaproduksi.js></script>
<script language=javascript src=js/option.js></script>
<script language=javascript src=js/Chart.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
echo"<div id=tableheader>";
echo "<fieldset style=float:left><legend>Form</legend>";
echo "<table cellspacing=1 border=0>";
echo "<tr><td>".$_SESSION['lang']['pt']."</td><td>:</td><td><select class=select2 id=kodeorg style=width:200px>".$optPT."</select></td></tr>";
echo "<tr><td>".$_SESSION['lang']['tanggal']."</td><td>:</td><td><input type=text class=myinputtext id=tanggal onmousemove=setCalendar(this.id) onkeypress=return false;  size=7 maxlength=10 readonly/></td></tr>";
echo "<tr><td></td><td></td><td colspan=3>

<button onclick=\"zPreview('kebun_slave_2laporananalisaproduksi','".$arr."','printContainer2');showheader();batal2();\" class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['preview']."</button>

<button onclick=\"zExcel(event,'kebun_slave_2laporananalisaproduksi.php','".$arr."')\" class=\"mybutton\">".$_SESSION['lang']['excel']."</button>

<button onclick=batal2() class=\"mybutton\" name=\"preview\" id=\"preview\">".$_SESSION['lang']['cancel']."</button></td></tr>";
echo "</table></fieldset>";
echo "<div style='clear:both'></div>";

CLOSE_BOX();
OPEN_BOX();
echo"<div id=tombolexport style=display:none;>
	<table>
		<tr><td>
			<button onclick='showheader()' class=\"mybutton\" id=showhead>Show Filter</button>
		</td>
	</table>
	</div>";
echo"<div id='printContainer2' class='table-scroll' style='height:73vh;'></div>";
CLOSE_BOX();
echo close_body();
?>