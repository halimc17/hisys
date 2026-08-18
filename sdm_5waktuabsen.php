<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/sdm_5waktuabsen.js'></script>

<?php
$optunit="<option value=''></option>";
$str="select * from ".$dbname.".organisasi where length(kodeorganisasi)='4'";
$res=fetchdata($str);
foreach($res as $key=>$val){
	$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']."-".$val['namaorganisasi']."</option>";
}

OPEN_BOX('','<span class=judul>'.strtoupper(getMenu('sdm_5waktuabsen')).'</span>');
//print_r($_SESSION['empl']['regional']);
echo"<div><fieldset>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td> 
			<td>:</td>
			<td>
				<select id='unit'>".$optunit."</select></select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=preview()>".$_SESSION['lang']['preview']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
				<input type=hidden id=method value='insert'>
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
        <div id=container></div>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>