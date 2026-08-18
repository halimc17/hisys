<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/kebun_2monitorhpt.js'></script>

<?php
include('master_mainMenu.php');

#TAHUN SENSUS
$optTahun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select distinct(left(nosensus,4)) as tahun from ".$dbname.".kebun_hpt_sensus_dt";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $optTahun.="<option value='".$bar['tahun']."'>".$bar['tahun']."</option>";
}

$optHama = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select * from ".$dbname.".kebun_5jenishama";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$optHama .= "<option value='".$bar['kodehama']."'>".$bar['namahama']."</option>";
}

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['hpt']).'</span>');
//Form Pencarian
echo "<fieldset style='width:450px;'><legend><b>".$_SESSION['lang']['hpt']."</b></legend>
<table>
	<tr>
		<td>" . $_SESSION['lang']['tahun'] . "</td>
		<td>:</td>
		<td><select id=tahun style='width:200px;'>".$optTahun."</select></td>
	</tr>
	<tr>
		<td>" . $_SESSION['lang']['jenishama'] . "</td>
		<td>:</td>
		<td><select id=jenishama style='width:200px;'>".$optHama."</select></td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td>
			<button class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button>
			<button class=mybutton onclick=fisikKeExcel(event)>".$_SESSION['lang']['excel']."</button>
			<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

CLOSE_BOX();

OPEN_BOX();
echo "<fieldset>
	<legend><b>".$_SESSION['lang']['result']."</b></legend>
	<div id='showGraphic'>
	</div>
	<div style=clear:both;>&nbsp;</div>
	<div id='showTable' style='min-height:200px;padding-top:50px;'>
		
	</div>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>