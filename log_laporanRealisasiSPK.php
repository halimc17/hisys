<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/log_laporanRealisasiSPK.js'></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['realisasispk']).'</span><br>');

//=================ambil unit;  
$str = "select distinct kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi
      where length(kodeorganisasi)=4 order by kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optafd = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
while ($bar = $res->fetch()) {
    $optunit.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->kodeorganisasi . " - " . $bar->namaorganisasi . "</option>";
}


$optposting="<option value=''>".$_SESSION['lang']['all']."</option>";
$optposting.="<option value='0'>".$_SESSION['lang']['belumposting']."</option>";
$optposting.="<option value='1'>".$_SESSION['lang']['posting']."</option>";


echo"<fieldset style=float:left>
     <legend>" . $_SESSION['lang']['form'] . "</legend>
	 " . $_SESSION['lang']['unit'] . " : <select id=unit style='width:250px;' onchange=getafd()>" . $optunit . "</select>
	 " . $_SESSION['lang']['afdeling'] . " : <select id=afd style='width:250px;'>" . $optafd . "</select>
	 " . $_SESSION['lang']['tgldari'] . " : <input type=\"text\" class=\"myinputtext\" id=\"tglAwal\" name=\"tglAwal\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:75px;\" />
         " . $_SESSION['lang']['tglsmp'] . " : <input type=\"text\" class=\"myinputtext\" id=\"tglAkhir\" name=\"tglAkhir\" onmousemove=\"setCalendar(this.id)\" onkeypress=\"return false;\"  maxlength=\"10\" style=\"width:75px;\" />
		 " . $_SESSION['lang']['posting'] . " : <select id=posting style='width:100px;'>" . $optposting . "</select>
	 <button class=mybutton onclick=getBiayaTotalPerKendaraan()>" . $_SESSION['lang']['proses'] . "</button>
	 </fieldset>";
CLOSE_BOX();
OPEN_BOX('', '');

echo"<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend><span id=printPanel style='display:none;'>
     <img onclick=biayaLaporanRealisasiKeExcel(event,'log_slave_laporanRealisasiSPK.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
	 </span>    
      <div id=container style='overflow:auto;height:400px;'>
     </div></fieldset>";
CLOSE_BOX();
close_body();
//<td align=center>".$_SESSION['lang']['periode']."</td>
?>