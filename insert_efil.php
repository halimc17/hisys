<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
//	
echo open_body();
?>

<script language=javascript1.2 src='js/insert_efil.js'></script>


<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('insert_efil').'</span>','judul_header');

echo"<br /><fieldset style='float:left;'>
	<table border=0 cellpadding=3 cellspacing=1>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>:</td>
			<td>
				<input type=text class=myinputtext id=notransaksi onkeypress=\"return tanpa_kutip(event);\" style=\"width:250px;\">
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td>
				<select id=tipe>
					<option value='k'>Kasbank</option>
					<option value='t'>Tagihan</option>
					<option value='p'>Penagihan</option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=simpan()>Execute</button>
                <button class=mybutton onclick=hapus()>Batal</button>
			</td>
		</tr>
	</table>
</fieldset>";

CLOSE_BOX();
echo close_body();
?>