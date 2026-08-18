<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/umm_5mapingnew.js'></script>

<?php
$_SESSION['rabmaterial'] = array();
// make option untuk menampilkan nama supplier di form
$optSatuan = "";
$nmSatuan=makeOption($dbname,'setup_satuan','satuan,satuan');
foreach($nmSatuan as $val)
{
	$optSatuan.="<option value='".$val."'>".$val."</option>";
}

//LIST SUB-ASSET
$optSubAsset = "";
$str="select * from ".$dbname.".sdm_5subtipeasset where kodetipe='BG' order by namasub";
$res=fetchData($str);
foreach($res as $key=>$val)
{
	$optSubAsset.="<option value='".$val['kodesub']."'>".$val['namasub']."</option>";
}

//LIST Tagihan
$optTipe = "";
$str="select * from ".$dbname.".keu_5jenistagihan where status='1' order by namajenis";
$res=fetchData($str);
foreach($res as $key=>$val)
{
	$optTipe.="<option value='".$val['kode']."'>".$val['namajenis']."</option>";
}
$optTipe.="<option value='others'>Others</option>";

OPEN_BOX('','<span class=judul>'.getMenu('umm_5mapingnew').'</span>');
//print_r($_SESSION['empl']['regional']);
echo"<div><fieldset>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['id']."</td> 
			<td>:</td>
			<td>
				<input type=text id=id class=myinputtext style='width:115px;' maxlength=100 disabled>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td> 
			<td>:</td>
			<td>
				<select id=tipe>".$optTipe."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kriteria']."</td> 
			<td>:</td>
			<td>
				<input type=text id=kriteria onkeypress=\"return tanpa_kutip(event);\"   class=myinputtext style=\"width:200px;\" maxlength=100>
			</td>
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
					".$_SESSION['lang']['id']." : Auto Generate<br>
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