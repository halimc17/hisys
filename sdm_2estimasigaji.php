<?php

require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
include('master_mainMenu.php');
?>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/sdm_2estimasigaji.js?v=<?php echo time(); ?>'></script>

<?php


OPEN_BOX('','<span class=judul>'.getMenu('sdm_2estimasigaji').'</span><br>');

## GET UNIT
$optUnit='';
$unit='';
$arrUnit = getOrgDetail(1);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optUnit.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	if($key==$_SESSION['empl']['lokasitugas']){
		$optUnit.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$unit=$key;
	}else{
		$optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optUnit.="</optgroup>";
	}
}

##GET SUBUNIT
$optSubUnit="<option value=''>".$_SESSION['lang']['all']."</option>";
$optSubUnit.="<option value='KANTOR'>".$unit." - KANTOR</option>";
$str="select kodeorganisasi,namaorganisasi,tipe from ".$dbname.".organisasi where induk='".$unit."' and  tipe not like '%GUDANG%' order by kodeorganisasi";
$res=fetchdata($str);
foreach($res as $val){
	$optSubUnit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
}

## GET PERIODE
$optPeriode="";
$str="select periode from ".$dbname.".sdm_5periodegaji group by periode order by periode desc";
$res=fetchdata($str);
foreach($res as $val){
	$optPeriode.="<option value='".$val['periode']."'>".$val['periode']."</option>";
}

## GET TiPEKARYAWaN
$optTipe="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".sdm_5tipekaryawan where aktif=1 and id != 0 ";
$res=fetchdata($str);
foreach($res as $val){
	$optTipe.="<option value='".$val['id']."'>".$val['tipe']."</option>";
}

## GET JABATAN
$optJab="<option value=''>".$_SESSION['lang']['all']."</option>";
$str="select * from ".$dbname.".sdm_5jabatan where aktif=1 order by namajabatan asc";
$res=fetchdata($str);
foreach($res as $val){
	$optJab.="<option value='".$val['kodejabatan']."'>".$val['namajabatan']."</option>";
}

## FILTER REPORT ##
echo"
<fieldset style=float:left>
	<table cellspacing=1 cellpadding=2>
		<tr>
			<td>".$_SESSION['lang']['kodeorg']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='kodeorg' style='width:246px' >".$optUnit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']." Gaji</td>
			<td>:</td>
			<td>
				<select class=select2 id='periodegaji' style='width:246px'>".$optPeriode."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipekaryawan']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='tipekar' style='width:246px'>".$optTipe."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['subbagian']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='subbagian' style='width:246px'>".$optSubUnit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['jabatan']."</td>
			<td>:</td>
			<td>
				<select class=select2 id='jabatan' style='width:246px'>".$optJab."</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button onclick=\"preview('html',event)\" class='mybutton'>".$_SESSION['lang']['preview']."</button>
				<button onclick=\"preview('excel',event)\" class='mybutton'>".$_SESSION['lang']['excel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";

echo "<fieldset style='float:left; margin-left:20px; width:280px; font-size:12px; line-height:1.5;'>
		<legend>Info</legend>
			<div>Ini ESTIMASI, bukan data gaji yang sudah/akan dibayarkan.</div>
			<div id='infoDinamis' style='margin-top:8px; color:#b06000;'></div>
</fieldset>";

CLOSE_BOX();

OPEN_BOX();
echo"<div class='table-scroll' style='height:750px;overflow:auto;' id=printContainer></div>";
CLOSE_BOX();
echo close_body();
?>