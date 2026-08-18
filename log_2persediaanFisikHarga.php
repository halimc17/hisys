<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src='js/log_laporan.js?v=<?php echo time(); ?>'></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth: true
		});
	});
</script>
<?php
include('master_mainMenu.php');

OPEN_BOX('', '<span class=judul>' . getMenu('log_2persediaanFisikHarga') . '</span>');

## GET PT
$arrorgdet = getOrgDetail(4);
$optpt = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where tipe='PT' and kodeorganisasi in (" . $arrorgdet . ") order by namaorganisasi";
$res = fetchdata($str);
foreach ($res as $val) {
	$optpt .= "<option value='" . $val['kodeorganisasi'] . "'>" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
}

## GET PERIODE
$str = "select distinct periode from " . $dbname . ".log_5saldobulanan order by periode desc";
$res = fetchdata($str);
foreach ($res as $val) {
	$optper .= "<option value='" . $val['periode'] . "'>" . substr($val['periode'], 5, 2) . "-" . substr($val['periode'], 0, 4) . "</option>";
}

## GET PERIODE YEAR
$str = "select distinct left(periode,4) as per from " . $dbname . ".log_5saldobulanan order by periode desc";
$res = fetchdata($str);
foreach ($res as $val) {
	$optper1 .= "<option value='" . $val['per'] . "'>" . $val['per'] . "</option>";
}

## GET GUDANG
$str = "select distinct a.kodeorg,b.namaorganisasi from " . $dbname . ".setup_periodeakuntansi a left join " . $dbname . ".organisasi b on a.kodeorg=b.kodeorganisasi where b.tipe like 'GUDANG%' order by kodeorg";
$res = fetchdata($str);
$optgudang1 = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

## GET KELOMPOK
$optkelompok = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$str = "select kode,kelompok from " . $dbname . ".log_5klbarang where noakun like '115%' and kode like '3%' order by kode asc";
$res = fetchdata($str);
foreach ($res as $val) {
	$optkelompok .= "<option value='" . $val['kode'] . "'>" . $val['kode'] . " - " . $val['kelompok'] . "</option>";
}


$frm = array();
$frm[0] = "<fieldset style=float:left>
	<legend>" . $_SESSION['lang']['find'] . "</legend>
	<table>
		<td>" . $_SESSION['lang']['pt'] . "</td>
		<td>:</td>
		<td>
			<select class=select2 id=pt style='width:150px;' onchange=getGudang()>" . $optpt . "</select>
		</td>
		
		<td style='padding-left:10px'>" . $_SESSION['lang']['sloc'] . "</td>
		<td>:</td>
		<td>
			<select class=select2 id=gudang style='width:150px;' onchange=hideById('printPanel')>" . $optgudang1 . "</select>
		</td>
		
		<td style='padding-left:10px'>Kelompok</td>
		<td>:</td>
		<td>
			<select class=select2 id=kelompok style='width:150px;'>" . $optkelompok . "</select>
		</td>
		
		<td style='padding-left:10px'>" . $_SESSION['lang']['periode'] . "</td>
		<td>:</td>
		<td>
			<select class=select2  style='width:150px;' id=periode onchange=hideById('printPanel')>" . $optper . "</select>
		</td>
		
		<td style='padding-left:10px'>
			<button class=mybutton onclick=getLaporanFisikHarga()>" . $_SESSION['lang']['preview'] . "</button>
			<button class=mybutton onclick=getLaporanFisikHarga_excel()>" . $_SESSION['lang']['excel'] . "</button>
		</td>
	</table>
</fieldset>
<div style=clear:both></div>
<span style='display:none;'>
<span id=printPanel style='display:none;'>
	<span id=orglegend></span>   
	<img onclick=fisikKeExceltab0(event,'log_laporanPersediaanFisikHarga_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	<img onclick=fisikKePDFHarga(event,'log_laporanPersediaanFisikHarga_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
</span> 
</span> 

<div class='table-scroll' style=overflow-x:hidden;>   
	<table class=sortable style='position:absolut;' cellspacing=1 border=0 cellpadding=5>
		<thead>
		<tr>
			<th rowspan=2 align=center style='width:50px;'>No.</th>
			<th rowspan=2 align=center>" . $_SESSION['lang']['periode'] . "</th>
			<th rowspan=2 align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
			<th rowspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</th>
			<th rowspan=2 align=center>" . $_SESSION['lang']['satuan'] . "</th>
			<th colspan=3 align=center>" . $_SESSION['lang']['saldoawal'] . "</th>
			<th colspan=3 align=center>" . $_SESSION['lang']['masuk'] . "</th>
			<th colspan=3 align=center>" . $_SESSION['lang']['keluar'] . "</th>
			<th colspan=3 align=center>" . $_SESSION['lang']['saldoakhir'] . "</th>
		</tr>
		<tr>
			<th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			<th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			<th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			<th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			<th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			<th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			<th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			<th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			<th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>  
		</tr>   
		</thead>
		<tbody id=container></tbody>
	</table>
</div>";


$frm[1] = "<fieldset style=float:left>
     <legend>" . $_SESSION['lang']['find'] . "</legend>
	 " . $_SESSION['lang']['pt'] . ":&nbsp;<select class=select2 id=pt1 style='width:150px;' onchange=getGudang3()>" . $optpt . "</select>
	 " . $_SESSION['lang']['sloc'] . ":&nbsp;<select class=select2 id=gudang1 style='width:150px;' onchange=hideById('printPanel1')>" . $optgudang1 . "</select>
	 " . $_SESSION['lang']['periode'] . ":&nbsp;<select class=select2 id=periode1 style='width:150px;' onchange=hideById('printPanel1')>" . $optper1 . "</select>
		<button class=mybutton onclick=getLaporanFisikHarga1()>" . $_SESSION['lang']['preview'] . "</button>
		<button class=mybutton onclick=getLaporanFisikHarga1_excel()>" . $_SESSION['lang']['excel'] . "</button>
	 </fieldset><div style=clear:both></div>";
//CLOSE_BOX();
//OPEN_BOX('','Result:');
$frm[1] .= "<span style='display:none;'><span id=printPanel1 style='display:none;'>
     <span id=orglegend1></span>   
     <img onclick=fisikKeExcelT1(event,'log_laporanPersediaanFisikHargaTahunan_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 <img onclick=fisikKePDFT1(event,'log_laporanPersediaanFisikHargaTahunan_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </span>
	 </span>
	<div class='table-scroll'  style=overflow-x:hidden;>  
      <table class=sortable style='position:absolut;' cellspacing=1 border=0 cellpadding=5>
	     <thead>
		    <tr>
			  <th rowspan=2 align=center style='width:50px;'>No.</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['periode'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['satuan'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['saldoawal'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['masuk'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['keluar'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['saldoakhir'] . "</th>
			</tr>
			<tr>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>  
			</tr>   
		 </thead>
			<tbody id=container1></tbody>
		 </table>
</div> ";
$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
// if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
// $whereUnit = '';
// } else {
// $whereUnit = " and induk = '" . $_SESSION['empl']['lokasitugas'] . "' ";
// }
$arrorgdet = getOrgDetail(2);
$whereUnit = " and induk in (" . $arrorgdet . ")";
$optUnit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$optGdng = $optUnit;
$sUnit = "select distinct substr(kodeorganisasi,1,4) as kodeorganisasi from " . $dbname . ".organisasi where tipe like 'GUDANG%' " . $whereUnit . " order by namaorganisasi asc";
$qUnit = $owlPDO->query($sUnit) or die(print " Gagal: " . PDOException::getMessage());
$qUnit->setFetchMode(PDO::FETCH_ASSOC);
while ($rUnit = $qUnit->fetch()) {
	$optUnit .= "<option value='" . $rUnit['kodeorganisasi'] . "'>" . $rUnit['kodeorganisasi'] . " - " . $optNmOrg[$rUnit['kodeorganisasi']] . "</option>";
}
$optPeriode = "";
for ($x = 0; $x < 13; $x++) {
	$dt = mktime(0, 0, 0, date('m') - $x, 15, date('Y'));
	$optPeriode .= "<option value=" . date("Y-m", $dt) . ">" . date("m-Y", $dt) . "</option>";
}
$frm[2] = "<fieldset style=float:left>
     <legend>" . $_SESSION['lang']['find'] . "</legend>
	 " . $_SESSION['lang']['unit'] . "&nbsp;:<select class=select2 id=unitht style='width:150px;'>" . $optUnit . "</select>
	 " . $_SESSION['lang']['periode'] . "&nbsp;:<select class=select2 id=periode2 onchange=hideById('printPanel2')>" . $optPeriode . "</select>
	 <button class=mybutton onclick=getLaporanFisikHarga2()>" . $_SESSION['lang']['preview'] . "</button>
	 <button class=mybutton onclick=getLaporanFisikHarga2_excel()>" . $_SESSION['lang']['excel'] . "</button>
	 </fieldset><div style=clear:both></div>";

$frm[2] .= "<span style='display:none;'><span id=printPanel2 style='display:none;'>
     <span id=orglegend2></span>   
     <img onclick=fisikKeExcelT2(event,'log_laporanPersediaanFisikHarga_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 <img onclick=fisikKePDFT2(event,'log_laporanPersediaanFisikHarga_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
	 </span>
	 </span>
	<div class='table-scroll'  style=overflow-x:hidden;>
    <table class=sortable style='position:absolut;' cellspacing=1 border=0 cellpadding=5>
	     <thead>
		    <tr>
			  <th rowspan=2 align=center style='width:50px;'>No.</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['periode'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['satuan'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['saldoawal'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['masuk'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['keluar'] . "</th>
			  <th colspan=3 align=center>" . $_SESSION['lang']['saldoakhir'] . "</th>
			</tr>
			<tr>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>
			   <th align=center>" . $_SESSION['lang']['kuantitas'] . "</th>
			   <th align=center>" . $_SESSION['lang']['hargasatuan'] . "</th>
			   <th align=center>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</th>  
			</tr>   
		 </thead>
			<tbody id=container2></tbody>
		 </table>
</div>";

$frm[3] = "<fieldset style=float:left>
     <legend>" . $_SESSION['lang']['find'] . "</legend>
	 " . $_SESSION['lang']['unit'] . "&nbsp;:&nbsp;<select class=select2 id=unitx style='width:150px;'>" . $optUnit . "</select>
	 " . $_SESSION['lang']['periode'] . "&nbsp;:&nbsp;<select class=select2 id=periodex onchange=hideById('printPanel3')>" . $optPeriode . "</select>
	 <button class=mybutton onclick=getLaporanFisikHargax()>" . $_SESSION['lang']['preview'] . "</button>
	 <button class=mybutton onclick=viewexcel('excel')>" . $_SESSION['lang']['excel'] . "</button>
	 </fieldset><div style=clear:both></div>";

$frm[3] .= "<span id=printPanel3 style='display:none;'>
     <span style='display:none;'>
	 <span id=orglegendx></span>   
     <img onclick=viewexcel('excel') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 </span> 
	 </span> 
	 <div class='table-scroll'  style=overflow-x:hidden;>
     <table class=sortable cellspacing=1 border=0>
	     <thead>
		    <tr>
			  <th rowspan=2 align=center style='width:50px;'>No.</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['periode'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</th>
			  <th rowspan=2 align=center>" . $_SESSION['lang']['satuan'] . "</th>
			  <th rowspan=2 align=center style='width:75px;'>Tgl Terima</th>
			  <th rowspan=2 align=center>0 to 90 days</th>
			  <th rowspan=2 align=center>90 to 180 days</th>
			  <th rowspan=2 align=center>180 t0 360 days</th>
			  <th rowspan=2 align=center>Over 360 days</th>
			  <th rowspan=2 align=center>Days of inventory</th>
			  <th rowspan=2 align=center>Total</th>
			</tr>
		 </thead>
		 <tbody id=containerx></tbody>
		 </table>
		 </div>
		 ";
//========================
$hfrm[0] = $_SESSION['lang']['persediaanfisikharga'] . ' ' . $_SESSION['lang']['bulanan'];
$hfrm[1] = $_SESSION['lang']['persediaanfisikharga'] . ' ' . $_SESSION['lang']['setahun'];
$hfrm[2] = $_SESSION['lang']['persediaanfisikharga'] . ' Per ' . $_SESSION['lang']['unit'];
$hfrm[3] = $_SESSION['lang']['inventoryageing'];

//draw tab, jangan ganti parameter pertama, krn dipakai di javascript
drawTab('FRM', $hfrm, $frm, 250, '100%');
//===============================================
CLOSE_BOX();
close_body();
?>