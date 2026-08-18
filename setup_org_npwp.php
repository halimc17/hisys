<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['setupnpwporg']).'</span>');
?>

<script language=javascript src='js/org_npwp.js?ver=1.0'></script>

<?php
$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier');

$strnama = array ("0"=>"tidak aktif","1"=>"aktif");
$strdefault = array ("0"=>"tidak","1"=>"ya");
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$res=fetchdata($str);
foreach($res as $val){
	$opt.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
}

$str="select supplierid from ".$dbname.".log_5supkelompok where tipe='PAJAK' and status='1'";
$res=fetchdata($str);
foreach($res as $val){
	$optsup.="<option value='".$val['supplierid']."'>".$nmsup[$val['supplierid']]."</option>";
}

echo"<fieldset>
<legend><b>".$_SESSION['lang']['setupnpwporg']."</b></legend>
<table>
	<tr>	
		<td>".$_SESSION['lang']['namaorganisasi']."</td>
		<td>:</td>
		<td>
			<select id=org>".$opt."</select>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['npwp']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=npwp onkeypress=\"return tanpa_kutip(event)\" size=45 maxlength=100>
		</td>
	</tr>
	<tr>	
		<td>".$_SESSION['lang']['inisial']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=inisial onkeypress=\"return tanpa_kutip(event)\" size=45 maxlength=20>
		</td>
	</tr>
	<tr>
		<td style='vertical-align:top'>".$_SESSION['lang']['alamatnpwp']."</td>
		<td style='vertical-align:top'>:</td>
		<td>
			<textarea id='alamatnpwp' onkeypress=\"return tanpa_kutip(event)\" cols=34></textarea>
		</td>
	</tr>	
	<tr>
		<td style='vertical-align:top'>".$_SESSION['lang']['domisili']."</td>
		<td style='vertical-align:top'>:</td>
		<td>
			<textarea id='alamatdomisili' onkeypress=\"return tanpa_kutip(event)\" cols=34></textarea>
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['nopkp']."</td>
		<td>:</td>
		<td>
			<input type=text class=myinputtext id=nopkp onkeypress=\"return tanpa_kutip(event)\" size=45 maxlength=100>
		</td>
	</tr>
	<tr style='display:none'>
		<td>Nama KPP</td>
		<td>:</td>
		<td>
			<select id=namakpp>".$optsup."
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['status']."</td>
		<td>:</td>
		<td>
			<input type=checkbox id=statuss onkeypress=\"return tanpa_kutip(event)\" size=45 maxlength=100>Akftif/Tidak Aktif
		</td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['default']."</td>
		<td>:</td>
		<td>
			<input type=checkbox id=defaultyo onkeypress=\"return tanpa_kutip(event)\" size=45 maxlength=100>Ya/Tidak
		</td>
	</tr>
	<tr>
		<td colspan=2></td>
		<td>
			<input type='hidden' id='method' value='insert'>
			<button class=mybutton onclick=savenpwp()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=cancelnpwp()>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

echo"<br><table class=sortable cellspacing=1 cellpadding=3 border=0>
	<thead>
	<tr class=rowheader style='text-align:center'>
		<td>".$_SESSION['lang']['kodeorg']."</td>
		<td>".$_SESSION['lang']['namaorganisasi']."</td>
		<td>".$_SESSION['lang']['npwp']."</td>
		<td>".$_SESSION['lang']['inisial']."</td>
		<td>".$_SESSION['lang']['alamatnpwp']."</td>
		<td>".$_SESSION['lang']['domisili']."</td>
		<td>".$_SESSION['lang']['nopkp']."</td>
		<td>".$_SESSION['lang']['status']."</td>
		<td>".$_SESSION['lang']['default']."</td>
		<td style='display:none'>Nama KPP</td>
		<td colspan=2>".$_SESSION['lang']['action']."</td>
	</tr>
	</thead>
	<tbody id=container>
		<script>loaddata()</script>
	</tbody>
</table>";
CLOSE_BOX();
?>