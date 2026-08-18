<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/keu_5keterangan.js'></script>
<script language="javascript" src='js/zTools.js'></script>

<?php
$otpAKas = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT * FROM " . $dbname . ".keu_5aruskas where status=1 order by nama_aruskas asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $otpAKas.="<option value=" . $bar['noaruskas'] . ">" . $bar['noaruskas'] . " - " . $bar['nama_aruskas'] . "</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_5keterangan').'</span>');
echo"<div><fieldset>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['notransaksi']."</td> 
			<td>:</td>
			<td><input type=text  id=notransaksi nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" placeholder=\"auto generate\" disabled></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['aruskas']."</td> 
			<td>:</td>
			<td><select id=aruskas  style=\"width:205px;\">" . $otpAKas . "</select>
				<img id='aruskas' onclick=z.elSearch('aruskas',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['keterangan']."</td> 
			<td>:</td>
			<td><input type=text  id=keterangan nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" onkeydown=\"upperCaseF(this)\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){simpan()}\"></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['status']." Aktif / Non-Aktif</td> 
			<td>:</td>
			<td>
				<input type=checkbox id=aktif checked>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
				<input type=hidden id=method value='insert'>
			</td>
		</tr>
	</table>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>
				<fieldset>
					<legend>".$_SESSION['lang']['keterangan']."</legend>
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
        <legend>".$_SESSION['lang']['list']."</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>".$_SESSION['lang']['find']."</legend>
							".$_SESSION['lang']['aruskas']." : 
							<select id=find_aruskas  onchange=loaddata(0) style=\"width:155px;\">" . $otpAKas . "</select>
							<img id='find_aruskas' onclick=z.elSearch('find_aruskas',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
							 &nbsp".$_SESSION['lang']['keterangan']." : 
							<input type=text  id=find_keterangan nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:150px;\" onkeydown=\"upperCaseF(this)\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){loaddata(0)}\">
							
							<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
							<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
							
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