<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<?
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['hargaminyakdunia']).'</span>');
?>
<fieldset style='float:left'>
<legend><?php echo $_SESSION['lang']['list']?></legend>
<div style="clear:both;">
<script language=javascript src='js/pmn_minyakdunia.js'></script>
<button type="button" style="float:right;" onclick="getDataOnline();" >GET DATA ONLINE</button>
</div>
<table cellspacing="1" border="0" class="sortable">
<thead>
<tr class="rowheader">
	<th align=center style=width:30px >No</th>
	<th align=center style=width:180px >Name</th>
	<th align=center style=width:100px >Month</th>
	<th align=center style=width:80px >Open</th>
	<th align=center style=width:80px >Hight</th>
	<th align=center style=width:80px >Low</th>
	<th align=center style=width:80px >Bid</th>
	<th align=center style=width:80px >Ask</th>
	<th align=center style=width:80px >Last done</th>
	<th align=center style=width:80px >Sett.Price</th>
	<th align=center style=width:80px >Change</th>
	<th align=center style=width:80px >OI*</th>
	<th align=center style=width:80px >Vol</th>
</tr>
</thead>
<tbody id="content">

</tbody>
</table>



<?php
CLOSE_BOX();
echo close_body('');
?>