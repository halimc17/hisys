<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_5jeniskriteria.js></script>
<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5jeniskriteria').'</span>');
?>
<br><fieldset style="float:left">
	<legend><?echo $_SESSION['lang']['form'];?></legend>
<table border="0" cellspacing="0">
  <tr>
    <td><?echo $_SESSION['lang']['kode'];?>
    </td><td> : </td>
    <td colspan=4><input type="text" id="kode" size="3" maxlength="2"  class="myinputtext"></td>
  </tr>
  <tr>
    <td><?echo $_SESSION['lang']['kriteria'];?></td><td> : </td>
    <td colspan=4><input type="text" id="kriteria" size="40" class="myinputtext"></td>
  </tr>

  <input type=hidden id=method value='insert'>
  <tr>
  	<td><td>
  	<td><button class=mybutton onclick=simpan()><?php echo $_SESSION['lang']['save'] ?></button>
	<button class=mybutton onclick=cancel()><?php echo $_SESSION['lang']['cancel'] ?></button></td>
  </tr>
	 
</table>
<?php
CLOSE_BOX();
OPEN_BOX();

echo "
<fieldset>
	<legend><b>".$_SESSION['lang']['list']."</legend>
	<table class=sortable cellspacing=1 cellspacing=1 border=0>
		<thead>
			<tr class=rowheader>
				<td align=center>".$_SESSION['lang']['nourut']."</td>
				<td align=center>".$_SESSION['lang']['kode']."</td>
				<td align=center>".$_SESSION['lang']['kriteria']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['action']."</td>
			</tr>
		</thead>
		<tbody id=container>
			<script>loadData(0)</script>
		<tfoot id='footData'>
		</tfoot>
		</tbody>
	</table>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>