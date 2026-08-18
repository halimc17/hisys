<?php
//@Copy nangkoelframework 
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/zTools.js'></script>
<script language=javascript1.2 src='js/bgt_btl_kebun.js'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('bgt_laporan_rp_kg_kebun').'</span><br>');
$str="select distinct(tahunbudget) as tahunbudget from  ".$dbname.".bgt_budget order by tahunbudget desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$opttahun="<option value=''>Pilih..</option>";
while($bar=$res->fetch()){
    $opttahun.="<option value='".$bar->tahunbudget."'>".$bar->tahunbudget."</option>";
}
#ambil kode kebun
$str="select kodeorganisasi as kodeorg, namaorganisasi from  ".$dbname.".organisasi where tipe='KEBUN' order by kodeorganisasi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$optunit="<option value=''>Pilih..</option>";
while($bar=$res->fetch()){
    $optunit.="<option value='".$bar->kodeorg."'>".$bar->kodeorg." - ".$bar->namaorganisasi."</option>";
}

echo"<fieldset><table>
     <tr><td>".$_SESSION['lang']['budgetyear']."</td><td>:</td><td><select id=thnbudget style='width:150px'>".$opttahun."</select></td></tr>
     <tr><td>".$_SESSION['lang']['kodeorganisasi']."</td><td>:</td><td><select id=kodeunit style='width:150px'>".$optunit."</select></td></tr>
     <tr><td>".$_SESSION['lang']['jenisbiaya']."</td><td>:</td><td><select id=jenis style='width:150px'>
     <option value=''>Pilih jenis biaya..</option>
     <option value='LANGSUNG'>LANGSUNG</option>
     <option value='UMUM'>UMUM</option>
     <option value='LANGSUNG DAN UMUM'>LANGSUNG DAN UMUM</option>
     </select></td></tr>
	 <input type=hidden id=method value='insert'>
	 <tr>
	 <td colspan=2></td>
	 <td>
		<button class=mybutton onclick=tampilkanRPKGKebun()>".$_SESSION['lang']['save']."</button>
	 </td>
	 </tr>
     </table>
	 </fieldset>";


CLOSE_BOX();
OPEN_BOX();
     echo"<div id=container style='width:100%; overflow:auto;'>
          </div>";

CLOSE_BOX();

echo close_body();
?>