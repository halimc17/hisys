<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');
require_once('lib/zSelect2.php');

?>

<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript1.2 src='js/kebun_2laporanupahpremipanenV2.js?v=<?php echo time(); ?>'></script>

<?
#= Make Option
$tipekaryawan = makeOption($dbname, "sdm_5tipekaryawan", "id,tipe");

#= Set Default Option
$optUnit  = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optDiv   = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optTipe   = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

#= untuk unit ht
$arrunit = array();
$arrunit = getOrgDetail(23);
foreach ($arrunit as $val => $nama) {
	// if($val == "")
	$optUnit .= "<option value='" . $val . "'>" . $val . " - " . $nama . "</option>";
	$arrkodeunit[$val] = $val;
}


OPEN_BOX('', '<span class=judul>' . getMenu('kebun_2laporanupahpremipanenV2') . '</span>');

#= Kirim Data
// $arr = "##unit##div##tgl##tglx##tipe##kegiatan";
$arr = "##unit##div##tgl##tglx";
?>
<fieldset>
	<legend>Form</legend>
	<table border=0 cellpadding=1 cellspacing=1>
		<tr>
			<td><?= $_SESSION['lang']['unit']; ?></td>
			<td>:</td>
			<td><select class=select2 id=unit onchange="getDivisi()" style="\width:168px;\"><?= $optUnit; ?></select></td>
		</tr>
		<tr>
			<td><?= $_SESSION['lang']['divisi']; ?></td>
			<td>:</td>
			<td><select class=select2 id=div style="\width:168px;\"><?= $optDiv; ?></select></td>
		</tr>
		<tr>
			<td>Dari <?= $_SESSION['lang']['tanggal']; ?></td>
			<td>:</td>
			<td>
				<input type="text" readonly="readonly" class="myinputtext" style="width:165px;padding:2px 0px" id="tgl" onmousemove="setCalendar(this.id);" onkeypress="return" false;="" maxlength="10" autocomplete="off" />
			</td>
		</tr>

		<tr>
			<td>Sampai <?= $_SESSION['lang']['tanggal']; ?></td>
			<td>:</td>
			<td>
				<input type="text" readonly="readonly" class="myinputtext" style="width:165px;padding:3px 0px" id="tglx" onmousemove="setCalendar(this.id);" onkeypress="return" false;="" maxlength="10" autocomplete="off" />
			</td>
		</tr>
		<tr>
			<td>
			<td>
			<td>
				<button onclick="loadUpahPremiPanen()" class=mybutton name=preview id=preview><?= $_SESSION['lang']['preview']; ?></button>
				<button onclick="zExcel('event','kebun_2laporanupahpremipanenV2_slave.php','<?= $arr; ?>')" class=mybutton name=preview id=preview><?= $_SESSION['lang']['excel']; ?></button>
			</td>
		</tr>
	</table>
</fieldset>
<?
CLOSE_BOX();
OPEN_BOX();
?>
<div id="printContainer" class="z-freeze-scroll" style="height:60vh;"></div>
<?

CLOSE_BOX();
close_body();
?>