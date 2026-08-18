<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/pabrik_mr_bfwt.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';



$optkode=$opttipe="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$arrtipe=getEnum($dbname,'pabrik_mr_bfwt','tipe');
foreach($arrtipe as $kei=>$fal){
	$opttipe.="<option value='".$kei."'>".$fal."</option>";
}


	
?>


<?php


OPEN_BOX('','<span class=judul>'.getMenu('pabrik_mr_bfwt').'</span><br><br>');
echo "<fieldset style='float:left;'><legend><b>".$_SESSION['lang']['form']."</b></legend>
		<table border=0 cellpadding=1 cellspacing=1>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td><input type='text' class='myinputtext' id='tgl' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:125px; /></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tipe']."</td>
				<td>:</td>
				<td><select onchange=getkode(); id=tipe style=\"width:125px;\">".$opttipe."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['kode']."</td>
				<td>:</td>
				<td><select id=kode style=\"width:125px;\">".$optkode."</select></td>
			</tr>
			<tr>
				<td width=100>".$_SESSION['lang']['nilai']."</td>
				<td>:</td>
				<td><input type=text id=nilai size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:125px;\"></td>
			</tr>
			<tr>
				<td width=100>".$_SESSION['lang']['keterangan']."</td>
				<td>:</td>
				<td><input type=text id=ket size=10 class=myinputtext maxlength=50 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:125px;\"></td>
			</tr>
			<tr>
				<td></td><td></td>
				<td><br /><button class=mybutton onclick=simpan()>Simpan</button>
				<button class=mybutton onclick=hapus()>Batal</button></td>
			</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>
	</fieldset>";
	CLOSE_BOX();
	OPEN_BOX();

	echo"<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['list']."</b></legend>
		<table border=0 cellpadding=1 cellspacing=1>
			<tr>
				<td>".$_SESSION['lang']['tipe']."</td>
				<td>:</td>
				<td><select id='tipesch' style='width:150px;'>".$opttipe."</select></td>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td><input type='text' class='myinputtext' id='tglsch' readonly onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='10' maxlength='10' style=width:125px; /></td>
				<td><button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>&nbsp;<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button></td>
			</tr>
		</table>
		<div id=container> 
			<script>loaddata(0)</script>
		</div>
		</fieldset>";

	CLOSE_BOX();					
?>