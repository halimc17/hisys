<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

?>
<script type="text/javascript" src="js/sdm_5ipfinger.js?v=1.1"></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php

//GET KODE ORGANISASI
$idOrg = substr($_SESSION['empl']['lokasitugas'], 0, 4);
//print_r($_SESSION['empl']);
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe IN ('KEBUN','PABRIK','AFDELING','HOLDING')	ORDER BY kodeorganisasi ASC";
}else{
	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe IN ('KEBUN','PABRIK','AFDELING') and
		substr(kodeorganisasi,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."') 
		ORDER BY kodeorganisasi ASC";
}

// if(strlen($_SESSION['empl']['subbagian']) == 6) 
// {
	// $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where (kodeorganisasi='".$idOrg."' or induk='".$_SESSION['empl']['lokasitugas']."') and kodeorganisasi like '" . $_SESSION['empl']['subbagian'] . "%' and tipe NOT LIKE '%GUDANG%' ORDER BY `kodeorganisasi` ASC";
// }
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optOrg = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
while ($bar = $res->fetch())
{
	$optOrg.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5ipfinger').'</span><br>');

//<select style='width:175px' id='kdorg' name='kd_org'>".$optOrg."</select>
echo"<fieldset style='width:450px;'>
	<legend>".$_SESSION['lang']['form']."</legend>
	<table cellspacing=1 border=0>
		<tr>
			<td>".$_SESSION['lang']['kodeorg']."</td>
			<td>:</td>
			<td>
				<input type='text' style='width:170px' id='kd_org' class='myinputtext' />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['ip']."</td>
			<td>:</td>
			<td>
				<input type='text' style='width:170px' id='ip' class='myinputtext' />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['username']."</td>
			<td>:</td>
			<td>
				<input type='text' style='width:170px' id='username' class='myinputtext' onKeyPress='return tanpa_kutip(event)' />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['password']."</td>
			<td>:</td>
			<td>
				<input type='password' style='width:170px' id='password' class='myinputtext' onKeyPress='return tanpa_kutip(event)' />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['dbname']."</td>
			<td>:</td>
			<td>
				<input type='text' style='width:170px' id='dbnm' class='myinputtext' onKeyPress='return tanpa_kutip(event)' />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['nmTabel']."</td>
			<td>:</td>
			<td>
				<input type='text' style='width:170px' id='tblnm' class='myinputtext' onKeyPress='return tanpa_kutip(event)' />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['port']."</td>
			<td>:</td>
			<td>
				<input type='text' style='width:70px' id='port' class='myinputtextnumber' maxlength='5' onKeyPress='return angka_doang(event)' />
			</td>
		</tr>
		<tr>
			<td><td><td>
			<input type='hidden' value='insert' id='method'  />
			<input type='hidden' value='' id='id'  />
			<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
			<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo"
	<div style='height:45vh;overflow:auto;' id='container'>
	<script>loaddata()</script>
	</div>";

CLOSE_BOX();
echo close_body();
?>