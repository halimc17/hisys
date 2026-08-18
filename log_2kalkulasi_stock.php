<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/log_2kalkulasi_stock.js'></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['mutasi']).'</span>');

//=================ambil unit;
if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING') {
    $newWhere = " ";
} else {
    $newWhere = " and induk = '" . $_SESSION['empl']['lokasitugas'] . "'";
}
$str = "select distinct kodeorganisasi, namaorganisasi from " . $dbname . ".organisasi
      where tipe like 'GUDANG%' " . $newWhere . "
      order by kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optunit = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
while ($bar = $res->fetch()) {
    $optunit.="<option value='" . $bar->kodeorganisasi . "'>" . $bar->namaorganisasi . "</option>";
}

//=================ambil periode;  
$str = "select distinct SUBSTR(tanggal, 1, 4) as tahun from " . $dbname . ".log_transaksiht
      order by tahun";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$opttahun = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
while ($bar = $res->fetch()) {
    $opttahun.="<option value='" . $bar->tahun . "'>" . $bar->tahun . "</option>";
}

//=================ambil kelompok; 
if ($_SESSION['language'] == 'EN') {
    $str = "select kode, kelompok1 as kelompok from " . $dbname . ".log_5klbarang
      order by kode";
} else {
    $str = "select kode, kelompok from " . $dbname . ".log_5klbarang
      order by kode";
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optkelompok = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
while ($bar = $res->fetch()) {
    $optkelompok.="<option value='" . $bar->kode . "'>" . $bar->kode . " - " . $bar->kelompok . "</option>";
}

$optpilih = "<option value='volume'>" . $_SESSION['lang']['volume'] . "</option>";
$optpilih.="<option value='nilai'>" . $_SESSION['lang']['harga'] . "</option>";

echo"<fieldset>
     <legend>" . $_SESSION['lang']['mutasi'] . "</legend>
     <table cellspacing=1 border=0>
     <tr>
        <td>" . $_SESSION['lang']['pilihgudang'] . "</td>
        <td><select id=unit style='width:150px;'>" . $optunit . "</select></td>
     </tr>
     <tr>
        <td>" . $_SESSION['lang']['periode'] . "</td>
        <td><select id=tahun style='width:150px;'>" . $opttahun . "</select></td>
     </tr>
     <tr>
        <td>" . $_SESSION['lang']['kelompokbarang'] . "</td>
        <td><select id=kelompok onchange=clearkobar() style='width:150px;'>" . $optkelompok . "</select></td>
     </tr>
     <tr>
        <td>" . $_SESSION['lang']['kodebarang'] . "</td>
        <td>
            <input type=\"text\" class=\"myinputtextnumber\" id=\"kodebarang\" name=\"kodebarang\" onkeypress=\"return angka_doang(event);\" value=\"\" maxlength=\"10\" style=\"width:150px;\" disabled=true/>
            <img src=images/search.png class=dellicon title=" . $_SESSION['lang']['find'] . " onclick=\"searchBrg('" . $_SESSION['lang']['findBrg'] . "','<fieldset><legend>" . $_SESSION['lang']['findnoBrg'] . "</legend>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor>',event)\";>
        </td>
     </tr>
     <tr>
        <td>Display</td>
        <td><select id=pilih style='width:150px;'>" . $optpilih . "</select></td>
     </tr>
     <tr>
        <td>Per Mayor</td>
        <td><input type=\"checkbox\" name=mayor id=mayor value=\"Major\" onclick=pilihMayor()></td>
     </tr>
     <tr>
        <td colspan=2><button class=mybutton onclick=getTransaksiGudang()>" . $_SESSION['lang']['proses'] . "</button></td>
     </tr></table>
    </fieldset>";
CLOSE_BOX();
OPEN_BOX('', 'Result:');
echo"<span id=printPanel style='display:none;'>
        <img onclick=rekalkulasiStockKeExcel(event,'log_slave_2kalkulasi_stock.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
     </span>

<div id='both_report'>
  <div id='head_tableboth' style='height:30px;'>
  <a title='Full Screen' class='fc_btn mybutton'  idboth='both_report' idbothhead='head_tableboth' idbothbody='printContainer' table='sortable' style='float:right;margin-right:10px'>
    <img title='Full Screen' class=resicon src='images/full-screen.png'>
  </a>
  <a title='Fixed Header Table' class='fixheadbtn mybutton' table='sortable' idbothbody='printContainer' shown='0' style='float:right;margin-right:10px;' >
    <img title='Fixed Header Table' class=resicon src=images/fix-header.gif>
  </a>
  </div>    
     <div id='printContainer' style='width:100%;height:359px;overflow:scroll;'>
        <table class=sortable cellspacing=1 border=0 width=100% id=container2></table>
     </div>
     </div>
     ";
CLOSE_BOX();
close_body();
?>