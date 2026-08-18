<?php
//Umar
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include('lib/zFunction.php');

//Header
OPEN_BODY();
require_once('lib/zSelect2.php'); 
include('master_mainMenu.php');

//First Container
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5ttdpesangon').'</span>');

/* LEVEL */
$optLev = $optTipe = $optUnit = "<option value='' hidden>".$_SESSION['lang']['pilihdata']."</option>";
for ($i=1; $i < 11; $i++) { 
	$optLev .= "<option value='".$i."'>".$i."</option>";
}

/* TIPE */
$tipe = getEnum($dbname, 'sdm_5ttdpesangon', 'tipe');
foreach ($tipe as $tp) {
	$optTipe .= "<option value='".$tp."'>".$tp."</option>";
}

/* UNIT */
$sUnt = selectQuery($dbname,"organisasi","kodeorganisasi,namaorganisasi", "LENGTH(kodeorganisasi)='4'");
$qUnt = fetchData($sUnt);
foreach ($qUnt as $unt) {
	$optUnit .= "<option value='".$unt['kodeorganisasi']."'>".$unt['namaorganisasi']."</option>";	
}

?>

<script type="text/javascript" src="js/sdm_5ttdpesangon.js?v=<?= time()?>"/></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php $mandatory = "<span style='color: red;'>*</span>" ?>

<div id='action_list'>
    <fieldset style='margin-top:10px'>
		<table>
			<input type="hidden" id="method" value="insert">
			<input type="hidden" id="id">
			<tr>
				<td><?= $_SESSION['lang']['namakaryawan']; ?> <?= $mandatory; ?></td>
				<td>:</td>
				<td> <input id='karyawan' class="myinputtext add" type='text' style="width:175px"> </td>
			</tr>
			<tr>
				<td>Level <?= $mandatory; ?></td>
				<td>:</td>
				<td> <select class="add select2" id="level" style="width:179px"> <?= $optLev; ?> </select> </td>
			</tr>
			<tr>
				<td><?= $_SESSION['lang']['tipe']; ?> <?= $mandatory; ?></td>
				<td>:</td>
				<td> <select class="add select2" id="tipe" style="width:179px"> <?= $optTipe; ?> </select> </td>
			</tr>
			<tr>
				<td><?= $_SESSION['lang']['unit']; ?> <?= $mandatory; ?></td>
				<td>:</td>
				<td> <select class="add select2" id="unit" style="width:179px"> <?= $optUnit; ?> </select> </td>
			</tr>
			<tr>
				<td valign='top'><?= $_SESSION['lang']['keterangan']; ?> <?= $mandatory; ?></td>
				<td valign='top'>:</td>
				<td> <textarea class="add" name="ket" id="ket" rows="3" style="width:157px"></textarea> </td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td style='margin-right:100px'>
					<button class='mybutton' onclick="save()"><?= $_SESSION['lang']['save']; ?></button>
					<button class='mybutton' onclick="batal()"><?= $_SESSION['lang']['cancel']; ?></button>
				</td>
			</tr>
		</table>
	</fieldset>
</div>

<script>loadData(0)</script>

<?php CLOSE_BOX(); ?>

<div id='list'>
	<?php OPEN_BOX("", "<span class='judul'>".$_SESSION['lang']['list']."</span>"); ?>
			<div id="listForm" style='margin-top:25px'></div>
	<?php CLOSE_BOX(); ?>
</div>

<?php CLOSE_BODY(); ?>