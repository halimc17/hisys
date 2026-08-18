<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript1.2 src="js/log_2transaksigudang.js?v=<?php echo time(); ?>" /></script>
<script language=javascript1.2 src="js/zSelect2.js?ver=1.9"></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('log_2transaksigudang').'</span><br>');
$unitDetailAkses = getOrgDetail(2);
$str = "select distinct kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi where tipe like 'GUDANG%' and induk in (".$unitDetailAkses.") and kodeorganisasi in (select kodegudang  from " . $dbname . ".log_transaksiht) order by kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ); 
$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
while ($bar = $res->fetch()) {
	$d=substr($bar->kodeorganisasi,0,4);
	if($d!=$n){			
		$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi',"kodeorganisasi='".$d."'");
		$optunit.="<optgroup label='".$d." - ".$nmorg[$d]."'>";
	}
	$optunit.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->kodeorganisasi . " (" . $bar->namaorganisasi . ")</option>";
	$n=$d;
	if($d!=$n){			
		$optunit.="</optgroup>";
	}
}

//Get Kodebarang
$str = "select distinct(kodebarang) as kodebarang, namabarang, satuan from ".$dbname.".log_5masterbarang order by namabarang";
// $str = "select a.kodebarang, a.namabarang, a.satuan from " . $dbname . ".log_5masterbarang a order by namabarang";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optBrg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
while ($bar = $res->fetch()) {
    $optBrg.="<option value='" . $bar->kodebarang . "'>" . $bar->namabarang . " (" . $bar->kodebarang . ")</option>";
}

//	 ".$_SESSION['lang']['pt']."<select id=pt style='width:150px;' onchange=hideById('printPanel')>".$optpt."</select>
$optper = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
if ($_SESSION['language'] == 'EN') {
    $optjenis = "<option value='9'>All</option>
                <option value='0'>Goods movement on the way</option>
                <option value='1'>Goods Receipt(GR)</option>
                <option value='2'>Return of GI</option>
                <option value='3'>Goods movement receipt</option>
                
                <option value='5'>Good Issue(GI)</option>
                <option value='6'>Return of GR</option>
                <option value='7'>Goods movement issue</option> 
                ";
} else {
    $optjenis = "<option value='9'>Seluruhnya</option>
                <option value='0'>Mutasi dalam perjalanan</option>
                <option value='1'>Penerimaan</option>
                <option value='2'>Pengembalian pengeluaran</option>
                <option value='3'>Penerimaan mutasi</option>
                
                <option value='5'>Pengeluaran</option>
                <option value='6'>Pengembalian penerimaan</option>
                <option value='7'>Pengeluaran mutasi</option>
                ";
}

$optbarang = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";

$datefirst = date("01-m-Y");
$datenow = date("d-m-Y");

echo"<fieldset style=float:left>
     <legend>" . $_SESSION['lang']['form'] . "</legend>
	 <table cellspacing=1 border=0>
	 <tr>
	   <td>" . $_SESSION['lang']['unit'] . "</td><td>:</td>
	   <td>
	     <select class='select2' id=unit style='width:170px;' onchange=ambilPeriode(this.options[this.selectedIndex].value)>" . $optunit . "</select></td>
	 	</tr>
	 <tr>
	 <!--<tr>
	   <td>" . $_SESSION['lang']['periode'] . "</td><td>:</td>
	   <td><select  style='width:80px;' id=periode onchange=hideById('printPanel')>" . $optper . "</select> - <select style='width:80px;' id=periode2 onchange=hideById('printPanel')>" . $optper . "</select></td>
	 </tr>-->
	 <tr>
	   <td>" . $_SESSION['lang']['periode'] . "</td><td>:</td>
	   <td>
	   	<input type=text class=myinputtext id=periode onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".$datefirst."' readonly/> S/D
    	<input type=text class=myinputtext id=periode2 onmousemove=setCalendar(this.id) onkeypress=return false;  size=8 maxlength=10 value='".$datenow."' readonly/></td>
	 </tr>
	 <tr>
	   <td>" . $_SESSION['lang']['tipetransaksi'] . "</td><td>:</td>
	   <td><select  class='select2' style='width:170px;' id=jenis onchange=hideById('printPanel')>" . $optjenis . "</select></td>
	 </tr><tr>
	   <td>" . $_SESSION['lang']['kodebarang'] . "</td><td>:</td>
       <td>
			<select  class='select2' style='width:170px;' id=kodebarang>" . $optBrg . "</select>
			<img id=kodebarang_find onclick=z.elSearch('kodebarang',event) class=resicon src=images/onebit_02.png style=position:relative;top:3px;left:3px;>
	   </td>
	 </tr><tr>
	   <td><td><td><button class=mybutton onclick=getTransaksiGudang()>" . $_SESSION['lang']['preview'] . "</button>
			<button class=mybutton onclick=transaksiGudangKeExcel(event,'log_slave_2transaksigudang_Excel.php')>" . $_SESSION['lang']['excel'] . "</button></td></td></td>
	 </tr></table>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX();

echo"<div id='printContainer'  class='table-scroll' style='height:63vh;overflow:auto;'></div>";
CLOSE_BOX();
close_body();
?>