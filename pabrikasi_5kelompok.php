<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript1.2 src='js/pabrikasi_5kelompok.js'></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language="javascript" src="js/zMaster.js"></script>
<?php



if($_SESSION['language']=='EN'){
	OPEN_BOX('','<span class=judul>'.strtoupper('Grouping').'</span>');
}else{
	OPEN_BOX('','<span class=judul>'.strtoupper('Kelompok Pabrikasi').'</span>');
}
echo"<br /><fieldset style='float:left;'>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['kode']."</td>
					<td>:</td>
					<td><input type=text id=kode size=4 class=myinputtext maxlength=4 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:125px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['nama']."</td>
					<td>:</td>
					<td><input type=text id=nama size=10 class=myinputtext maxlength=50 onkeypress=\"return tanpa_kutip(event);\"  style=\"width:125px;\"></td>
				</tr>
				<tr>
					<td></td><td></td>
					<td><button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
					<button class=mybutton onclick=hapus()>".$_SESSION['lang']['cancel']."</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>";
					
	
					
CLOSE_BOX();
?>

<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loaddata()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>

