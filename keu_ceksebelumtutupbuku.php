<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript1.2" src='js/keu_ceksebelumtutupbuku.js?ver=1.5'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>

<?php

$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(11) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optorg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optorg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optorg.="</optgroup>";
	}
}

#harus pake session karena di slave ada yg pakai session
$optorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$optorg.="<option value=".$_SESSION['empl']['lokasitugas'].">".$_SESSION['empl']['lokasitugas']." - ".getNamaOrg($_SESSION['empl']['lokasitugas'])."</option>";

# Form
OPEN_BOX('','<span class=judul>'.getMenu('keu_ceksebelumtutupbuku').'</span><br>');

echo"<fieldset style=float:left>
		<legend>".$_SESSION['lang']['form']."</legend>
		<table cellspacing='1' border='0'>
			<tr>
				<td>".$_SESSION['lang']['kodeorg']."</td>
				<td>:</td>
				<td>
					<select id='kodeorg' class=select2 onchange=changeperiode(this); style=width:175px>". $optorg."</select>
				</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td>
					<select id='periode' class=select2 style=width:175px>". $optPeriod."</select>
				</td>
			</tr>
			
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick='tutupBuku()'>Cek Tutup Buku</button>
				</td>
				
			</tr>
		</table></fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo"<div id=container></div>";
CLOSE_BOX();
close_body();
?>