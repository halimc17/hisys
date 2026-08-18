<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/pmn_5lokasikontrak.js'></script>

<?php

// make option untuk menampilkan nama supplier di form
$nmOrg1=  makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');

$str2=$owlPDO->query("select supplierid,namasupplier from ".$dbname.".log_5supplier 
      order by namasupplier");
$str2->setFetchMode(PDO::FETCH_OBJ);
$optkeg='';
while($bar=$str2->fetch()){
    $optkeg.="<option value='".$bar->supplierid."'>".$bar->namasupplier."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('pmn_5lokasikontrak').' CPO/PK</span>');
//print_r($_SESSION['empl']['regional']);
echo"<div><fieldset>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['lokasikontrak']."</td> 
			<td>:</td>
			<td><input type=text  id=lokasi nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" maxlength=100></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['inisial']."</td> 
			<td>:</td>
			<td><input type=text  id=inisial nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:80px;\" maxlength=24 onkeydown=\"upperCaseF(this)\"></td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['status']." Aktif / Non-Aktif</td> 
			<td>:</td>
			<td>
				<input type=checkbox id=status checked>
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
					&nbsp;- Aktif : Check, CheckBox <input type='checkbox' checked disabled><br>
					&nbsp;- Non Aktif : UnCheck, CheckBox <input type='checkbox' disabled>
				</fieldset>
			</td> 
		</tr>
	</table>
</fieldset><div>";

CLOSE_BOX();
?>



<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
        <div id=container> 
            <script>loaddata(0)</script>
        </div>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>