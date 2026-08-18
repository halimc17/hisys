<?php
//@Copy nangkoelframework
// ---- ind ----

require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<?php
$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sOrg="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where  tipe='TRAKSI' order by namaorganisasi asc";
$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while($rOrg=$qOrg->fetch()){
	$optOrg.="<option value=".$rOrg['kodeorganisasi'].">".$rOrg['kodeorganisasi']." - ".$rOrg['namaorganisasi']."</option>";
}

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach(getOrgDetail(18) as $key => $val){
	$tipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe',"kodeorganisasi='".$key."'");
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".substr($key,0,4)."'");
	$d=$induk[substr($key,0,4)];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optOrg.="<option value=".$key.">".$key." - ".$val."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

$optThn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sThn="select distinct  tahunbudget from ".$dbname.".bgt_budget  order by tahunbudget desc";
$qThn=$owlPDO->query($sThn) or die(print " Gagal: ".PDOException::getMessage());
$qThn->setFetchMode(PDO::FETCH_ASSOC);
while($rThn=$qThn->fetch())
{
    $optThn.="<option value='".$rThn['tahunbudget']."'>".$rThn['tahunbudget']."</option>";
}
$arr="##thnBudget##kdUnit";
$arr2="##thnBudget2##kdUnit2";
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script>
function summForm() {
	//closeDialog();
	width = '750';
	height = '350';
	content = "<div id=container style='overflow:auto;width:100%;height:330px;'></div>";
	ev = 'event';
	title = "Detail Alokasi";
	showDialog1(title, content, width, height, ev);
}

function getAlokasi(kdTraksi, kdkend, thnbdget) {
	//summForm();
	kodeTraksi = kdTraksi;
	kdVhc = kdkend;
	thnBudget = thnbdget;
	param = 'kdTraksi=' + kodeTraksi + '&kdVhc=' + kdVhc + '&thnBudget=' + thnBudget;
	tujuan = 'bgt_slave_laporan_rp_jam_kendaraan.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					// document.getElementById('container').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					//return con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan + '?' + 'proses=getAlokasi', param, respog);

}
function summForm2() {
	//closeDialog();
	width = '750';
	height = '350';
	content = "<div id=container2 style='overflow:auto;width:100%;height:330px;'></div>";
	ev = 'event';
	title = "Detail Alokasi";
	showDialog2(title, content, width, height, ev);
}
function getBiaya(kdTraksi, kdkend, thnbdget) {
	//summForm2();
	kodeTraksi = kdTraksi;
	kdVhc = kdkend;
	thnBudget = thnbdget;
	param = 'kdTraksi=' + kodeTraksi + '&kdVhc=' + kdVhc + '&thnBudget=' + thnBudget;
	tujuan = 'bgt_slave_laporan_rp_jam_kendaraan.php';
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					// alert(con.responseText);
					// document.getElementById('container2').innerHTML = con.responseText;
					alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
					//return con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan + '?' + 'proses=getBiaya', param, respog);

}
function printFile(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '200';
	height = '150';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}

function dataKeExcelAlokasi(ev, kdTraksi, kdkend, thnbdget) {
	kodeTraksi = kdTraksi;
	kdVhc = kdkend;
	thnBudget = thnbdget;
	param = 'kdTraksi=' + kodeTraksi + '&kdVhc=' + kdVhc + '&thnBudget=' + thnBudget + '&getExcelAlokasi' + '&proses=excelAlokasi';
	tujuan = 'bgt_slave_laporan_rp_jam_kendaraan.php';
	judul = 'Report Ms.Excel';
	printFile(param, tujuan, judul, ev)
}
function dataKeExcel(ev, kdTraksi, kdkend, thnbdget) {
	kodeTraksi = kdTraksi;
	kdVhc = kdkend;
	thnBudget = thnbdget;
	param = 'kdTraksi=' + kodeTraksi + '&kdVhc=' + kdVhc + '&thnBudget=' + thnBudget + '&getExcelAlokasi' + '&proses=excelBiaya';
	tujuan = 'bgt_slave_laporan_rp_jam_kendaraan.php';
	judul = 'Report Ms.Excel';
	printFile(param, tujuan, judul, ev)
}
function Clear1() {
	document.getElementById('thnBudget').value = '';
	document.getElementById('kdUnit').value = '';
	document.getElementById('printContainer').innerHTML = '';
}

function Clear2() {
	document.getElementById('thnBudget2').value = '';
	document.getElementById('kdUnit2').value = '';
	document.getElementById('printContainer2').innerHTML = '';
}

function printFileAlokasiPdf(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '1250';
	height = '500';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}

function dataKePdfAlokasi(ev, kdTraksi, kdkend, thnbdget) {
	kodeTraksi = kdTraksi;
	kdVhc = kdkend;
	thnBudget = thnbdget;
	param = 'kdTraksi=' + kodeTraksi + '&kdVhc=' + kdVhc + '&thnBudget=' + thnBudget + '&getExcelAlokasi' + '&proses=pdfAlokasi';
	tujuan = 'bgt_slave_laporan_rp_jam_kendaraan.php';
	judul = 'Report Detail PDF';
	printFileAlokasiPdf(param, tujuan, judul, ev)
	//alert (param);
	//alert (param);

}

function dataKePdfBiaya(ev, kdTraksi, kdkend, thnbdget) {
	kodeTraksi = kdTraksi;
	kdVhc = kdkend;
	thnBudget = thnbdget;
	param = 'kdTraksi=' + kodeTraksi + '&kdVhc=' + kdVhc + '&thnBudget=' + thnBudget + '&getExcelAlokasi' + '&proses=pdfBiaya';
	tujuan = 'bgt_slave_laporan_rp_jam_kendaraan.php';
	judul = 'Report Detail PDF';
	printFileBiayaPdf(param, tujuan, judul, ev)
	//alert (param);
	//alert (param);

}

function printFileBiayaPdf(param, tujuan, title, ev) {
	tujuan = tujuan + "?" + param;
	width = '1250';
	height = '500';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog1(title, content, width, height, ev);
}
</script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?php

$frm[0]="<div><fieldset style=float:left>
<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0 >
<tr><td><label>".$_SESSION['lang']['budgetyear']."</label></td><td>:</td><td><select class=select2 id='thnBudget' style=\"width:175px;\">".$optThn."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['kodetraksi']."</label></td><td>:</td><td><select class=select2 id='kdUnit'  style=\"width:175px;\">".$optOrg."</select></td></tr>
<tr><td></td><td></td><td colspan=2><button onclick=\"zPreview('bgt_slave_laporan_rp_jam_kendaraan','".$arr."','printContainer')\" class=mybutton name=preview id=preview>Preview</button>
		<!--
		<button onclick=\"zPdf('bgt_slave_laporan_rp_jam_kendaraan','".$arr."','printContainer')\" class=mybutton name=preview id=preview>PDF</button>
		-->
        <button onclick=\"zExcel(event,'bgt_slave_laporan_rp_jam_kendaraan.php','".$arr."')\" class=mybutton name=preview id=preview>Excel</button>
        <button onclick=\"Clear1()\" class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button></td></tr>        
</table>
</fieldset>
</div>

<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:100%'>

</div></fieldset>";

$frm[1]="<div><fieldset style=float:left>
<legend><b>".$_SESSION['lang']['form']."</b></legend>
<table cellspacing=1 border=0 >
<tr><td><label>".$_SESSION['lang']['budgetyear']."</label></td><td>:</td><td><select class=select2 id='thnBudget2' style=\"width:175px;\">".$optThn."</select></td></tr>
<tr><td><label>".$_SESSION['lang']['kodetraksi']."</label></td><td>:</td><td><select class=select2 id='kdUnit2'  style=\"width:175px;\">".$optOrg."</select></td></tr>

<tr><td></td><td></td><td colspan=2><button onclick=\"zPreview('bgt_slave_laporan_rp_jam_kendaraan2','".$arr2."','printContainer2')\" class=mybutton name=preview id=preview>Preview</button>
<!--<button onclick=\"zPdf('bgt_slave_laporan_rp_jam_kendaraan2','".$arr2."','printContainer2')\" class=mybutton name=preview id=preview>PDF</button>-->
        <button onclick=\"zExcel(event,'bgt_slave_laporan_rp_jam_kendaraan2.php','".$arr2."')\" class=mybutton name=preview id=preview>Excel</button>
        <button onclick=\"Clear2()\" class=mybutton name=btnBatal2 id=btnBatal2>".$_SESSION['lang']['cancel']."</button></td></tr>        
</table>
</fieldset>
</div>

<fieldset style='clear:both'><legend><b>Print Area</b></legend>
<div id='printContainer2' style='overflow:auto;height:400px;max-width:100%'>

</div></fieldset>";
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_rp_jam_kendaraan').'</span>');

$hfrm[0]=$_SESSION['lang']['rpperjamKend'];
$hfrm[1]=$_SESSION['lang']['alokasi'].' '.$_SESSION['lang']['rpperjamKend'];
drawTab('FRM',$hfrm,$frm,300,'100%');

CLOSE_BOX();
echo close_body();
?>