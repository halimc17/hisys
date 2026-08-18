<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
require_once('lib/zSelect2.php');
?>
	<script language=javascript1.2 src='js/kebun_2bkmglobal.js'></script>
	<script>
		$(document).ready(function() {
			$('.select2').select2({
				dropdownAutoWidth:true
			});
		});
		
		$(document).on('focus', '.select2-selection.select2-selection--single', function (e) {
			$(this).closest(".select2-container").siblings('select:enabled').select2('open');
		});
		
		
		function showupload(notransaksi){
			ev = 'event';
			param='method=showupload&notransaksi='+notransaksi;
			
			tujuan='kebun_slave_bkm.php';
			post_response_text(tujuan, param, respog);
			function respog(){
				if(con.readyState==4){
					if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alertify.alert(con.responseText);
						}else {
							alertify.popup().destroy();
							alertify.popup("Upload",con.responseText).set({'resizable':true, 'overflow':false}).resizeTo('400px','400px');
							
							loadfiles(notransaksi);
						}
					}else {
						busy_off();
						error_catch(con.status);
					}
				}	
			}	
		}
		
		function loadfiles(notransaksi) {
			param = 'method=loadfiles&notransaksi=' + notransaksi;
			tujuan = 'kebun_slave_bkm.php';
			post_response_text(tujuan, param, respog);
			function respog() {
				if (con.readyState == 4) {
					if (con.status == 200) {
						busy_off();
						if (!isSaveResponse(con.responseText)) {
							alertify.alert(con.responseText);
						} else {
							if (document.getElementById('listfiles') !== null) {
								document.getElementById('listfiles').innerHTML = con.responseText;
							}
							if (document.getElementById('loadfilesdetail') !== null) {
								document.getElementById('loadfilesdetail').innerHTML = con.responseText;
							}
						}
					} else {
						busy_off();
						error_catch(con.status);
					}
				}
			}
		}
	</script>
<?php

$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$lksiTugas=substr($_SESSION['empl']['lokasitugas'],0,4);
$sPeriode="select distinct periode from ".$dbname.".sdm_5periodegaji where kodeorg='".$lksiTugas."' order by periode desc";
$qPeriode=$owlPDO->query($sPeriode) or die(print " Gagal: ".PDOException::getMessage());
$qPeriode->setFetchMode(PDO::FETCH_ASSOC);
while($rPeriode=$qPeriode->fetch()){
	$optPeriode.="<option value=".$rPeriode['periode'].">".substr(tanggalnormal($rPeriode['periode']),1,7)."</option>";
}
$optBag="<option value=''>".$_SESSION['lang']['all']."</option>";
$sBag="select kode,nama from ".$dbname.".sdm_5departemen order by nama asc";//$optBag
$qBag=$owlPDO->query($sBag) or die(print " Gagal: ".PDOException::getMessage());
$qBag->setFetchMode(PDO::FETCH_ASSOC);
$optOrg="";
while($rBag=$qBag->fetch()){
	$optBag.="<option value=".$rBag['kode'].">".$rBag['nama']."</option>";
}
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$optOrg="<select id=kdOrg name=kdOrg onchange=getPeriode() style=\"width:150px;\" ><option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe in ('KEBUN','PABRIK','KANWIL') order by namaorganisasi asc ";	
}else{
	$optOrg="<select id=kdOrg name=kdOrg style=\"width:150px;\"><option value=''>".$_SESSION['lang']['all']."</option>";
	$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' or kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi asc";
}
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
$optOrg2="";
while($rOrg=$qOrg->fetch()){
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
	$optOrg2.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}
$optOrg.="</select>";
$optOrg2.="</select>";
$optSisGaji="<option value=''>".$_SESSION['lang']['all']."</option>";
$arrSisGaji=array("0"=>"Harian","1"=>"Bulanan");
foreach($arrSisGaji as $dt => $isi){
    $optSisGaji.="<option value=".$isi.">".$_SESSION['lang'][strtolower($isi)]."</option>";
}

//GET UNIT
if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
	$optUnit.="<option value=''>".$_SESSION['lang']['all']."</option>";
	$whr = " and 1=1";
	
	$optAfd.="<option value=''>".$_SESSION['lang']['all']."</option>";
	$whr2 = " and 1=1";
}else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
	$optUnit.="<option value=''>".$_SESSION['lang']['all']."</option>";
	$whr = " and induk='".$_SESSION['empl']['kodeorganisasi']."'";
	$whr = " and 1=1";
	
	$optAfd.="<option value=''>".$_SESSION['lang']['all']."</option>";
	$whr2 = " and induk in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."' and tipe='KEBUN')";
	$whr2 = " and 1=1";
}else{
	$whr = " and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";
	
	$optAfd.="<option value=''>".$_SESSION['lang']['all']."</option>";
	$whr2 = " and kodeorganisasi like '".$_SESSION['empl']['lokasitugas']."%'";
}
// $str = "select * from ".$dbname.".organisasi where tipe = 'KEBUN' ".$whr." order by namaorganisasi asc";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$optUnit.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
// }
$arrunit = getOrgDetail(1);
foreach ($arrunit as $key => $val) {
	if($key==$_SESSION['empl']['lokasitugas']){
		$optUnit.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$unit=$key;
	}else{
		$optUnit.="<option value='".$key."'>".$key." - ".$val."</option>";			
	}
} 

//GET AFDELING
$str = "select * from ".$dbname.".organisasi where tipe = 'AFDELING' ".$whr2." order by namaorganisasi asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optAfd.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
} 


//FORM HEADER
OPEN_BOX('','<span class=judul>'.getMenu('kebun_2bkmglobal').'</span>');
	
echo "<div>
	<fieldset style='float: left;'>
	<legend><b>".$_SESSION['lang']['form']."</b></legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select class=select2 style=width:173px id='unit' onchange='getafdeling()'>
					".$optUnit."
				</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['divisi']."</td>
			<td>:</td>
			<td>
				<select class=select2 style=width:173px id='afdeling' style='width:150px'>
					".$optAfd."
				</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<input type='text' class='myinputtext' id='tglawal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:70px;' value='".date('01-m-Y')."'  readonly/> 
				s/d 
				<input type='text' class='myinputtext' id='tglakhir' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:70px;' value='".date('d-m-Y')."'  readonly/>
			</td>
		</tr>
		<tr>
			<td>No BKM</td>
			<td>:</td>
			<td>
				<input style=width:168px placeholder='".$_SESSION['lang']['all']."' type='text' class='myinputtext' id='nobkm' />
			</td>
		</tr>
			<input type='hidden' class='myinputtext' id='tgl1' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:150px;' />
			<input type='hidden' class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  maxlength='10' style='width:150px;' />
		<tr>
			<td colspan='2'></td>
			<td>
				<button class='mybutton' onclick='preview()'>Preview</button>
				<button class='mybutton' onclick='excel(event)'>Excel</button>
			</td>
		</tr>
	</table>
</fieldset>
</div>";
CLOSE_BOX();
OPEN_BOX();
echo "<div style=clear:both></div>
	<div id='container' style='overflow:auto;height:63vh;'></div>";
CLOSE_BOX();
echo close_body();
?>