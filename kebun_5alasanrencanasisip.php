<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script type="text/javascript" src="js/kebun_5alasanrencanasisip.js"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['alasanrencanasisip']).'</span>');
?>
<fieldset style="width:250px;">
	<legend><?php echo $_SESSION['lang']['form'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['kode']?></td>
			<td>:</td>
			<td><input type="text" id="kd_alasan" size="5" class="myinputtext" maxlength="4" /></td>
		</tr>
		<tr>
			<td style="vertical-align:top;"><?php echo $_SESSION['lang']['deskripsi']?></td>
			<td style="vertical-align:top;">:</td>
			<td><textarea id='deskripsi' onkeypress='return tanpa_kutip(event);' maxlength=40></textarea></td>
		</tr>
		<tr>
			<td><td><td>
			<input type="hidden" value="insert" id="method"  />
			<button class=mybutton onclick=simpanalasanrencanasisip()><?php echo $_SESSION['lang']['save']?></button>
			<button class=mybutton onclick=btlalasanrencanasisip()><?php echo $_SESSION['lang']['cancel']?></button>
			</td>
		</tr>
	</table>
</fieldset>
<?php 
	CLOSE_BOX();
	OPEN_BOX();
?>
<fieldset style="width:250px;">
	<legend><?php echo $_SESSION['lang']['list']." ".$_SESSION['lang']['alasanrencanasisip'] ?></legend>
	<table class="sortable" cellspacing="1" cellpadding="3" border="0" width="100%">
		<thead>
			<tr class=rowheader>
				<td><?php echo $_SESSION['lang']['nourut']?></td>
				<td><?php echo $_SESSION['lang']['kode']?></td>
				<td><?php echo $_SESSION['lang']['deskripsi']?></td>
				<td colspan="2" style="text-align:center;"><?php echo $_SESSION['lang']['action']; ?></td>
			</tr>
		</thead>
		<tbody id="container">
		<script>loadData()</script>
		</tbody>
		<tfoot>
		</tfoot>
	</table>
</fieldset>
<?
CLOSE_BOX();
echo close_body();
?>