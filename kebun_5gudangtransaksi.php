<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/kebun_5gudangtransaksi.js></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_5gudangtransaksi').'</span>');
$optkebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$skebun="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('AFDELING','BIBITAN','WORKSHOP') and induk='".$_SESSION['empl']['lokasitugas']."'";
$qkebun=$owlPDO->query($skebun) or die(print " Gagal: ".PDOException::getMessage());
$qkebun->setFetchMode(PDO::FETCH_ASSOC);
while($rkebun=$qkebun->fetch()){
	$optkebun.="<option value=".$rkebun['kodeorganisasi'].">".$rkebun['kodeorganisasi']." - ".$rkebun['namaorganisasi']."</option>";
}

$optStat=$optgudang="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$skebun="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='GUDANGTEMP' and induk like '".$_SESSION['empl']['lokasitugas']."%'";
$qkebun=$owlPDO->query($skebun) or die(print " Gagal: ".PDOException::getMessage());
$qkebun->setFetchMode(PDO::FETCH_ASSOC);
while($rkebun=$qkebun->fetch()){
	$optgudang.="<option value=".$rkebun['kodeorganisasi'].">".$rkebun['kodeorganisasi']." - ".$rkebun['namaorganisasi']."</option>";
}

$arrStat=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);
foreach($arrStat as $rw=>$lst){
	$optStat.="<option value='".$rw."'>".$lst."</option>";
}

echo"<fieldset style='width:250px;'><legend>".$_SESSION['lang']['form']."</legend>
		<table>
     		<tr>
     			<td>".$_SESSION['lang']['afdeling']."</td>
     			<td>:</td>
     			<td><select id=afdeling style=width:180px>".$optkebun."</select></td>
     		</tr>
	 		<tr>
	 			<td>".$_SESSION['lang']['gudang']."</td>
	 			<td>:</td>
	 			<td><select id=kodegudang style=width:180px>".$optgudang."</select></td>
	 		</tr>
	 		<tr>
	 			<td>".$_SESSION['lang']['status']."</td>
	 			<td>:</td>
	 			<td><select id=status style=width:180px>".$optStat."</select></td>
	 		</tr>
	 		<input type=hidden id=method value='insert'>
	 		<tr>
	 		<td></td>
	 		<td></td>
	 		<td><button class=mybutton onclick=simpangudang()>" . $_SESSION['lang']['save'] . "</button>
	 		<button class=mybutton onclick=cancelgudang()>" . $_SESSION['lang']['cancel'] . "</button></td>
	 		</tr>
	 	</table>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX('','');

echo"<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData(0)</script>
		</div>
	</fieldset>";

CLOSE_BOX();
echo close_body();
?>