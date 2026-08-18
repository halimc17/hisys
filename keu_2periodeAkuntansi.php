<?//@Copy nangkoelframework 
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/keu_laporan.js?v=<?php echo time(); ?>></script>
<?
include('master_mainMenu.php'); 
OPEN_BOX('','<span class=judul>'.getMenu('keu_2periodeAkuntansi').'</span><br>');
//=================ambil PT;  
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' or $_SESSION['empl']['tipelokasitugas']=='KANWIL'){
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where tipe='PT'
        order by namaorganisasi";
}else{
    $str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
        where tipe='PT' and kodeorganisasi = '".$_SESSION['empl']['kodeorganisasi']."'
        order by namaorganisasi";
}
$optpt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
    $optpt.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

?>
<fieldset style="width: 450px;"> 
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr>
    <td><label><?php echo $_SESSION['lang']['pt']?></label></td><td>:</td>
    <td><select id=kodept style='width:200px;' onchange=ambilAnakPA(this.options[this.selectedIndex].value)><?php echo $optpt; ?></select></td>
</tr>
<tr>
    <td><label><?php echo $_SESSION['lang']['kodeorganisasi']?></label></td><td>:</td>
    <td><select id=kodeunit style='width:200px;' onchange=document.getElementById('container').innerHTML=''><?php echo $optunit; ?></select></td>
</tr>
<tr><td><td><td>
<button class=mybutton onclick=getPeriodeAkuntansi("html")><?php echo $_SESSION['lang']['preview'] ?></button>
<button class=mybutton onclick=getPeriodeAkuntansi("excel")><?php echo $_SESSION['lang']['excel'] ?></button>
</td></tr>
</table>
</fieldset>
<?

CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset> 
	 <div id=container style='width:100%;height:300px;overflow:auto;'>
</div>";
CLOSE_BOX();
close_body();

?>
