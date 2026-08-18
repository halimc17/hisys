<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/log_5merk.js?v=<?= time() ?>'></script>
<script language="javascript" src='js/zTools.js'></script>

<?php
OPEN_BOX('', '<span class=judul>' . getMenu('log_5merk') . '</span>');
echo "<div><fieldset>
    <legend>" . $_SESSION['lang']['form'] . "</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>ID " . $_SESSION['lang']['merk'] . "</td> 
			<td>:</td>
			<td><input type=text  id=idmerk nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" maxlength=6 disabled placeholder=\"auto generate\"></td>
		</tr>
		<tr>
			<td class=bintang>" . $_SESSION['lang']['merk'] . "</td> 
			<td>:</td>
			<td><input type=text  id=merk nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" onkeydown=\"upperCaseF(this)\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){simpan()}\"></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['status'] . " Aktif / Non-Aktif</td> 
			<td>:</td>
			<td>
				<input type=checkbox id=status checked>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=simpan()>" . $_SESSION['lang']['save'] . "</button>
				<button class=mybutton onclick=batal()>" . $_SESSION['lang']['cancel'] . "</button>
				<input type=hidden id=method value='insert'>
			</td>
		</tr>
	</table>
	
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>
				<fieldset>
					<legend>" . $_SESSION['lang']['keterangan'] . "</legend>
					Status :<br>
					&nbsp;- Aktif : Centang CheckBox <input type='checkbox' checked disabled><br>
					&nbsp;- Non Aktif : Uncentang CheckBox <input type='checkbox' disabled>
				</fieldset>
			</td> 
		</tr>
	</table>
</fieldset><div>";

CLOSE_BOX();
?>

<?php
OPEN_BOX();
echo "<fieldset>
        <legend>" . $_SESSION['lang']['list'] . "</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>" . $_SESSION['lang']['find'] . "</legend>
							" . $_SESSION['lang']['merk'] . " : 
							<input type=text  id=find_merk nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" onkeydown=\"upperCaseF(this)\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){loaddata(0)}\">
							
							<button class=mybutton onclick=loaddata(0)>" . $_SESSION['lang']['find'] . "</button>
							
						</fieldset>
					</td> 
				</tr>
			</table>
		
        <div id=container> 
            <script>loaddata(0)</script>
        </div>
    </fieldset>";
CLOSE_BOX();
echo close_body();
?>