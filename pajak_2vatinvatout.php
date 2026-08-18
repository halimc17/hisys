<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
?>

<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript src='js/pajak_2vatinvatout.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php

$opttipe=$optunit=$optflag="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$opttipe.="<option value=1>Vat In</option>";
$opttipe.="<option value=2>Vat Out</option>";
$opttipe.="<option value=3>Pembetulan Vat IN</option>";
$opttipe.="<option value=5>Pembetulan Vat Out</option>";

$optflag.="<option value=0>Belum</option>";
$optflag.="<option value=1>Sudah</option>";
  
$arrtipe=getOrgDetail(1);
foreach($arrtipe as $kei=>$fal){
    if (substr($kei, 2,2)=='RO' || substr($kei, 2,2)=='HO') {
		$optunit.="<option value='".$kei."'>".$fal."</option>";
    }
}

OPEN_BOX('','<span class=judul>'.getMenu('pajak_2vatinvatout').'</span><br><br>');
echo"
<fieldset style=\"float: left;\">
<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=\"1\" border=\"0\" >
	<tr>  
		<td>".$_SESSION['lang']['tipe']."</td>
		<td>:</td>
		<td><select id=tipe style=width:150px;>".$opttipe."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['unit']."</td> 
		<td>:</td>
		<td><select id=unit style=width:150px; onchange='getperiode()'>".$optunit."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['npwp']."</td>
		<td>:</td>
		<td><select id=npwp style=width:150px; >".$optNpwp."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['tanggal']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=tanggal1 onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:60px; maxlength=10 /> s/d
			<input type=text class=myinputtext id=tanggal2 onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:60px; maxlength=10 />
		</td>
	</tr>
	<tr>
		<td>Vat in/out</td>
		<td>:</td>
		<td><select id=flag style=width:150px; >".$optflag."</select></td>
	</tr>
<tr>
	<td colspan=2></td>
	<td><button onclick=\"preview()\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
	<button onclick=\"excel()\" class=\"mybutton\" name=\"Excel\" id=\"Excel\">Excel</button></td>
</tr>

</table>
</fieldset>

<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:300px;max-width:1150px'>
</div></fieldset>";
//========================



CLOSE_BOX();
echo close_body();
?>