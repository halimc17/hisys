<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/kebun_5namakud.js?v=<?php echo time(); ?>'></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('kebun_5namakud').'</span>');
$optkebun="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";


$optStat=$optgudang=$optunit=$optnoakuninvestasi="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";


$str = "SELECT * FROM " . $dbname . ".admin_list where username='".$_SESSION['standard']['username']."'";
$adm = fetchData($str);
if(count($adm)>0 or $_SESSION['empl']['tipelokasitugas']!='HOLDING'){
	$where="";
}else{
	$where = " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
}

// $skebun="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('AFDELING') ".$where." and namaorganisasi like '%KUD%'";
$skebun="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN') ".$where." and inti='0'";
$qkebun=$owlPDO->query($skebun) or die(print " Gagal: ".PDOException::getMessage());
$qkebun->setFetchMode(PDO::FETCH_ASSOC);
while($rkebun=$qkebun->fetch()){
	$optkebun.="<option value=".$rkebun['kodeorganisasi'].">".$rkebun['kodeorganisasi']." - ".$rkebun['namaorganisasi']."</option>";
}

$skebun="select * from ".$dbname.".log_5supplier where status='1' and supplierid in (select supplierid from ".$dbname.".log_5supkelompok
		where tipe  like '%TBS%')";
$qkebun=$owlPDO->query($skebun) or die(print " Gagal: ".PDOException::getMessage());
$qkebun->setFetchMode(PDO::FETCH_ASSOC);
while($rkebun=$qkebun->fetch()){
	$optgudang.="<option value=".$rkebun['supplierid'].">".$rkebun['namasupplier']."</option>";
}

$snoakuninvest="select * from ".$dbname.".keu_5akun where 5=5 and level='5' and noakun LIKE '12201%'";
$qnoakuninvest=$owlPDO->query($snoakuninvest) or die(print " Gagal: ".PDOException::getMessage());
$qnoakuninvest->setFetchMode(PDO::FETCH_ASSOC);
while($rinvest=$qnoakuninvest->fetch()){
	$optnoakuninvestasi.="<option value=".$rinvest['noakun'].">[".$rinvest['noakun']."] ".$rinvest['namaakun']."</option>";
}

$skebun="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where inti=1 and tipe in ('KEBUN') ".$where." ";
$qkebun=$owlPDO->query($skebun) or die(print " Gagal: ".PDOException::getMessage());
$qkebun->setFetchMode(PDO::FETCH_ASSOC);
while($rkebun=$qkebun->fetch()){
	$optunit.="<option value=".$rkebun['kodeorganisasi'].">".$rkebun['kodeorganisasi']." - ".$rkebun['namaorganisasi']."</option>";
}

$arrStat=array("1"=>$_SESSION['lang']['aktif'],"0"=>$_SESSION['lang']['tidakaktif']);
foreach($arrStat as $rw=>$lst){
	$optStat.="<option value='".$rw."'>".$lst."</option>";
}

echo"<fieldset style='width:350px;'><legend>".$_SESSION['lang']['form']."</legend>
		<table>
     		<tr>
     			<td>Nama KUD Organisasi</td>
     			<td>:</td>
     			<td><select id=afdeling style=width:180px>".$optkebun."</select>
				<img id='afdeling' onclick=z.elSearch('afdeling',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
				</td>
     		</tr>
	 		<tr>
	 			<td>Nama KUD Supplier</td>
	 			<td>:</td>
	 			<td><select id=kodegudang style=width:180px>".$optgudang."</select>
				<img id='kodegudang' onclick=z.elSearch('kodegudang',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
	 		</tr>
	 		<tr>
	 			<td>".$_SESSION['lang']['noakuninvestasi']."</td>
	 			<td>:</td>
	 			<td><select id=noakuninvestasi style=width:180px>".$optnoakuninvestasi."</select>
				<img id='noakuninvestasi' onclick=z.elSearch('noakuninvestasi',event) class='resicon' src='images/skyblue/zoom.png' style='position:relative;top:2px;'></td>
	 		</tr>
			<tr>
	 			<td>".$_SESSION['lang']['unit']."</td>
	 			<td>:</td>
	 			<td><select id=kodeunit style=width:180px>".$optunit."</select></td>
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