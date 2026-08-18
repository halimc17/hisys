<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/rTable.php');

echo open_body();
require_once('master_mainMenu.php');
$optpks="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK' and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optpks="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}
?>

<script language='javascript' src='js/zMaster.js'></script>
<script language='javascript' src='js/zSearch.js'></script>
<script language='javascript' src='js/zTools.js'></script>
<script language='javascript' src='js/pabrik_oilunderflow.js'></script>
<script languange='javascript' src='js/formTable.js'></script>
<link rel='stylesheet' type='text/css' href='style/zTable.css'>
<?php 
OPEN_BOX('','<div class=judul>'.getMenu('pabrik_oilunderflow').'</div>');
?>
<form id="insert_underflow" name="insert_underflow" method="POST" action="#" onsubmit="inputData(this);return false;">
	<fieldset style="float:left;">
		<legend><?php echo $_SESSION['lang']['entryForm']; ?></legend> 
		<table border="0" cellpadding="1" cellspacing="1">
			<tr>
				<td><?php echo $_SESSION['lang']['pabrik']; ?></td>
				<td>:</td>
				<td colspan="4">
				<select id="pabrik" name="pabrik" style="width:135px;" onchange="getdata()" >
					<?php echo $optpks; ?>
				</select>
				<img src="images/obl.png" title="Obligatory"></td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['tanggal']; ?></td>
				<td>:</td>
				<td colspan="4">
				<input id="tanggal" type="text" class="myinputtext" name="tanggal" onmousemove="setCalendar(this)" 
				onkeypress="return false;" size="10" maxlength="10" value="<?php echo date('d-m-Y'); ?>" onchange="getdata()" readonly>
				<img src="images/obl.png" title="Obligatory">
				</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['oil']; ?></td>
				<td>:</td>
				<td colspan="4">
					<input type="text" name="oil" class="myinputtextnumber" onkeypress="return angka_doang(event);">
				</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['moisture']; ?></td>
				<td>:</td>
				<td colspan="4">
					<input type="text" name="moisture" class="myinputtextnumber" onkeypress="return angka_doang(event);">
				</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['sludge']; ?></td>
				<td>:</td>
				<td colspan="4">
					<input name="sludge" type="text" name="sludge" class="myinputtextnumber" onkeypress="return angka_doang(event);">
				</td>
			</tr>
			<tr>
				<td><?php echo $_SESSION['lang']['keterangan']; ?></td>
				<td>:</td>
				<td colspan="4">
					<input type="text" name="keterangan" class="myinputtext">
				</td>
			</tr>
		</table>
		<br/>
		<input type="submit" class="mybutton" value="<?php echo $_SESSION['lang']['save']; ?>">
		<input type="reset" class="mybutton" value="<?php echo $_SESSION['lang']['clear']; ?>">
	</fieldset>
	
</form>
<?php
CLOSE_BOX();
?>
<div id="workwarp"> 
	<script>getSlave();</script>
</div>
<?php
echo close_body();
?>