<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/legal_2laparealtanam.js?v=<?= time(); ?>'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language="javascript" src="js/zTools.js"></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php
## option tahun: dari setup_blok_tahunan (pattern kebun_2aresta)
$optTah = "";
$sOrg = "select distinct left(tahun,4) as tahun from " . $dbname . ".setup_blok_tahunan order by tahun desc";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) {
	$optTah .= "<option value=" . $rOrg['tahun'] . ($rOrg['tahun'] == date("Y") ? " selected" : "") . ">" . $rOrg['tahun'] . "</option>";
}

## option kebun: hanya unit yang boleh diakses user (pattern kebun_2aresta: getOrgDetail(1))
$optOrg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optAfd = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optTtm = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$arrunit = getOrgDetail(1);
$n = '';
foreach ($arrunit as $key => $val) {
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $key . "'");
	$d = $induk[$key];
	if ($d != $n) {
		$optOrg .= "<optgroup label='" . $d . " - " . getNamaOrg($d) . "'>";
	}
	$optOrg .= "<option value='" . $key . "'>" . $key . " - " . $val . "</option>";
	$n = $d;
	if ($d != $n) {
		$optOrg .= "</optgroup>";
	}
}
?>
<script>
function getAfdeling() {
	kebun0 = document.getElementById('kebun0').options[document.getElementById('kebun0').selectedIndex].value;
	param = 'kebun0=' + kebun0 + '&proses=getAfdeling0';
	tujuan = 'legal_slave_2laparealtanam.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					cor = con.responseText.split("####");
					document.getElementById('afdeling0').innerHTML = cor[0];
					document.getElementById('tahuntanam0').innerHTML = cor[1];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getTahuntanam() {
	kebun0 = document.getElementById('kebun0').options[document.getElementById('kebun0').selectedIndex].value;
	afdeling0 = document.getElementById('afdeling0').options[document.getElementById('afdeling0').selectedIndex].value;
	param = 'kebun0=' + kebun0 + '&afdeling0=' + afdeling0 + '&proses=getTahuntanam0';
	tujuan = 'legal_slave_2laparealtanam.php';
	post_response_text(tujuan, param, respon);
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert('ERROR TRANSACTION,\n' + con.responseText);
				} else {
					cor = con.responseText.split("####");
					document.getElementById('tahuntanam0').innerHTML = cor[0];
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
</script>

<?php
OPEN_BOX('', '<span class=judul>' . getMenu('legal_2laparealtanam') . '</span><br>');
$title[0] = $_SESSION['lang']['form'];
$arr0 = "##tahun0##kebun0##afdeling0##tahuntanam0##umurtua0";
echo "<fieldset style=\"float: left;\">
<legend><b>" . $title[0] . "</b></legend>
<table cellspacing=\"1\" border=\"0\" >
<tr>
	<td><label>" . $_SESSION['lang']['tahun'] . "</label></td><td>:</td>
	<td><select class=select2 id=\"tahun0\" name=\"tahun0\" style=\"width:80px\">" . $optTah . "</select></td>

	<td><label>" . $_SESSION['lang']['kebun'] . "</label></td><td>:</td>
	<td><select class=select2 id=\"kebun0\" name=\"kebun0\" style=\"width:150px\" onchange=getAfdeling()>" . $optOrg . "</select></td>

	<td><label>Umur Tua/Rusak (Thn)</label></td><td>:</td>
	<td><input type=text id=umurtua0 name=umurtua0 value=25 style=\"width:40px\" class=myinputtext onkeypress=\"return angka saja(event);\"></td>
</tr>
<tr>
	<td><label>" . $_SESSION['lang']['tahuntanam'] . "</label></td><td>:</td>
	<td><select class=select2 id=\"tahuntanam0\" name=\"tahuntanam0\" style=\"width:80px\">" . $optTtm . "</select></td>

	<td><label>" . $_SESSION['lang']['afdeling'] . "</label></td><td>:</td>
	<td><select class=select2 id=\"afdeling0\" name=\"afdeling0\" style=\"width:150px\" onchange=getTahuntanam()>" . $optAfd . "</select></td>
</tr>
<tr>
	<td colspan=\"2\"></td><td colspan=\"4\">
		<button onclick=\"zPreview('legal_slave_2laparealtanam','" . $arr0 . "','printContainer0')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
		<button onclick=\"zExcel(event,'legal_slave_2laparealtanam.php','" . $arr0 . "')\" class=\"mybutton\" name=\"excel\" id=\"excel\">Excel</button>
		<button onclick=\"zPdf('legal_slave_2laparealtanam','" . $arr0 . "','printContainer0')\" class=\"mybutton\" name=\"pdf\" id=\"pdf\">PDF</button>
	</td>
</tr>
</table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();
echo "<div id='both_report'>
	<div id='head_tableboth' align=right>
		<a class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer0' table='sortable' >
			<img title='Full Screen' class='resicon' src='images/full-screen.png'>
		</a>
		<a class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer0' shown='0' >
			<img title='Fixed Header Table' class='resicon' src='images/fix-header.gif'>
		</a>
	</div>
<div id='printContainer0' style='width:100%;'>
</div></div>";

CLOSE_BOX();
echo close_body();
?>
