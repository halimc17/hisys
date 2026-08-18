<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src=js/zReport.js></script>
<script language=javascript1.2 src=js/log_laporan.js></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['hutangsupplierbpb']).'</span><br>');
//get existing period
$str = "select distinct periode from " . $dbname . ".log_5saldobulanan
      order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optper = "<option value=''></option>";
while ($bar = $res->fetch()) {
    $optper.="<option value='" . $bar->periode . "'>" . substr($bar->periode, 5, 2) . "-" . substr($bar->periode, 0, 4) . "</option>";
}

//=================ambil gudang;  
$str = "select distinct a.kodeorg,b.namaorganisasi from " . $dbname . ".setup_periodeakuntansi a
      left join " . $dbname . ".organisasi b
	  on a.kodeorg=b.kodeorganisasi
      where b.tipe='GUDANG'
	  order by kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optgudang = "";
while ($bar = $res->fetch()) {
    $optgudang.="<option value='" . $bar->kodeorg . "'>" . $bar->kodeorg . " - " . $bar->namaorganisasi . "</option>";
}
//	 ".$_SESSION['lang']['pt']."<select id=pt style='width:150px;' onchange=hideById('printPanel')>".$optpt."</select>

$frm[0].="<fieldset style=float:left>
     <legend>" . $_SESSION['lang']['find'] . "</legend>
	 " . $_SESSION['lang']['sloc'] . " : <select id=gudang style='width:250px;' onchange=ambilPeriode(this.options[this.selectedIndex].value)>" . $optgudang . "</select>
	 " . $_SESSION['lang']['periode'] . " : <select id=periode onchange=hideById('printPanel')>" . $optper . "</select>
	 <button class=mybutton onclick=getHutangSupplier()>" . $_SESSION['lang']['proses'] . "</button>
	 </fieldset>";

$frm[0].="<div style=clear:both></div><fieldset><legend>" . $_SESSION['lang']['list'] . "</legend>
	 <span id=printPanel style='display:none;'>
     <img onclick=hutangSupplierKeExcel(event,'log_laporanHutangSupplier_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 <img onclick=hutangSupplierKePDF(event,'log_laporanHutangSupplier_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </span>    
	 <div style='width:100%;height:400px;overflow:auto;'>
       <table class=sortable cellspacing=1 border=0 width=100%>
	     <thead>
		    <tr>
			  <td align=center>No.</td>
			  <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
			  <td align=center>" . $_SESSION['lang']['kodesupplier'] . "</td>
			  <td align=center>" . $_SESSION['lang']['namasupplier'] . "</td>
			  <td align=center>" . $_SESSION['lang']['kodebarang'] . "</td>
			  <td align=center>" . $_SESSION['lang']['namabarang'] . "</td>
			  <td align=center>" . $_SESSION['lang']['satuan'] . "</td>
			  <td align=center>" . $_SESSION['lang']['jumlah'] . "</td>
			  
			  
			  <td align=center>" . $_SESSION['lang']['hargasatuan'] . "</td>
			  <td align=center>" . $_SESSION['lang']['total'] . "</td>
			</tr>  
		 </thead>
		 <tbody id=container>
		 </tbody>
		 <tfoot>
		 </tfoot>		 
	   </table>
     </div></fieldset>";
	#pt select
	//$optPt.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sPt="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
	$rPt=fetchdata($sPt);
	foreach($rPt as $row=>$lstDat){
		$optPt.="<option value='".$lstDat['kodeorganisasi']."'>".$lstDat['namaorganisasi']."</option>";
	}
	#supplier select
	$optSpl.="<option value=''>".$_SESSION['lang']['all']."</option>";
	$sSpl="select supplierid,namasupplier from ".$dbname.".log_5supplier where supplierid='S001'";
	$rSpl=fetchdata($sSpl);
	foreach($rSpl as $row=>$lstDat){
		$optSpl.="<option value='".$lstDat['supplierid']."'>".$lstDat['namasupplier']."</option>";
	}
$arr = "##ptCr##supplierid##tglDr##tglSmp";
$frm[1].="<fieldset style=float:left><legend>".$_SESSION['lang']['find']."</legend>";
$frm[1].="<table border=0 cellspacing=1 cellpadding=1>";
$frm[1].="<tr><td>".$_SESSION['lang']['pt']."</td><td>:</td>";
$frm[1].="<td><select id=ptCr style=width:150px>".$optPt."</select></td></tr>";
$frm[1].="<tr><td>".$_SESSION['lang']['supplier']."</td><td>:</td>";
$frm[1].="<td><select id=supplierid style=width:150px>".$optSpl."</select><img id='supplierid' onclick=z.elSearch('supplierid',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'></td></tr>";
$frm[1].="<tr><td>".$_SESSION['lang']['nopo']."</td><td>:</td>";
$frm[1].="<td><input type=text class=myinputtext id=nopo  style=width:150px onkeypress='return tanpa_kutip(event)' /></td></tr>";
$frm[1].="<tr><td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['dari']."</td><td>:</td>";
$frm[1].="<td><input type=text class=myinputtext id=tglDr  style=width:150px onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>";
$frm[1].="<tr><td>".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['sampai']."</td><td>:</td>";
$frm[1].="<td><input type=text class=myinputtext id=tglSmp  style=width:150px onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 /></td></tr>";
$frm[1].="<tr><td colspan=2>&nbsp;</td>";
$frm[1].="<td><button class=mybutton  onclick=\"zPreview('log_slave_2hutangsupplier', '".$arr."', 'printContainer')\" class=\"mybutton\">".$_SESSION['lang']['preview']."</button>
              <button onclick=\"zExcel(event, 'log_slave_2hutangsupplier.php', '".$arr."')\" class=\"mybutton\">".$_SESSION['lang']['excel']."</button>
          </td></tr>";
$frm[1].="</table>";
$frm[1].="</fieldset>";
$frm[1].="<fieldset style='clear:both'><legend><b>Print Area</b></legend>
    <div id='printContainer' style='overflow:auto;height:350px'>

    </div></fieldset>";
//========================
$hfrm[0]="Per ".$_SESSION['lang']['supplier'];
$hfrm[1]="Per ".$_SESSION['lang']['nopo'];
//$hfrm[1]=$_SESSION['lang']['list'];
//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM',$hfrm,$frm,200,1250);
//===============================================	
CLOSE_BOX();
close_body();
?>