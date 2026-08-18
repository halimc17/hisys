<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script language=javascript src='js/sdm_5jenistraining.js?v=<?php echo time(); ?>'></script>

<link rel=stylesheet type=text/css href=style/zTable.css>
<?
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5jenistraining').'</span><br>');
?>
<fieldset style='width:500px;'>
	<legend><?php echo $_SESSION['lang']['traininginternal'] ?></legend>
	<table cellspacing="1" border="0">
		<tr>
			<td><?php echo $_SESSION['lang']['kode'] ?></td>
			<td>:</td>
			<td>
				<input type="text" id="kode" class="myinputtext" size="10" maxlength="10" />
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['kelompok'] ?></td>
			<td>:</td>
			<td>
				<input type="text" id="kelompok" class="myinputtext" size="50" maxlength="40" />
			</td>
		</tr>
		<tr>
			<td><?php echo $_SESSION['lang']['jeniskursus'] ?></td>
			<td>:</td>
			<td>
				<input type="text" id="jenistraining" class="myinputtext" size="50" maxlength="40" />
			</td>
		</tr>
		<tr>
			<td colspan="2"></td>
			<td>
			<input type="hidden" value="insert" id="method"  />
			<button class=mybutton onclick=simpantraining()><?php echo $_SESSION['lang']['save']?></button>
			<button class=mybutton onclick=bataltraining()><?php echo $_SESSION['lang']['cancel']?></button>
			</td>
		</tr>
	</table>
</fieldset>
<?php 
	CLOSE_BOX();
	OPEN_BOX();
?>
<table class="sortable" cellspacing="1" cellpadding="7" style="width:100%;" border="0">
	<thead>
		<tr align=center class=rowheader>
			<th><?php echo $_SESSION['lang']['nourut']?></th>
			<th><?php echo $_SESSION['lang']['kode']?></th>
			<th><?php echo $_SESSION['lang']['kelompok']?></th>
			<th><?php echo $_SESSION['lang']['jeniskursus']?></th>
			<th><?php echo $_SESSION['lang']['status'];?></th>
			<th colspan="2" style="text-align:center;"><?php echo $_SESSION['lang']['action']; ?></th>
		</tr>
	</thead>
	<tbody id="container">
	<script>loadData()</script>
	</tbody>
	<tfoot>
	</tfoot>
</table>
<?
CLOSE_BOX();
echo close_body();
?>