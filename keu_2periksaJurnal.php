<?//@Copy nangkoelframework 
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src="js/keu_laporan.js"></script>
<?
include('master_mainMenu.php');
//OPEN_BOX('','<b>'.strtoupper($_SESSION['lang']['laporanbukubesar']).'</b>');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['laporanperiksajurnal']).'</span><br>');

//get unit where length=4
if($_SESSION['empl']['tipelokasitugas']=='HOLDING')
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
    where CHAR_LENGTH(kodeorganisasi)=4
    ";
else
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi
    where CHAR_LENGTH(kodeorganisasi)=4 and kodeorganisasi like '%".$_SESSION['empl']['lokasitugas']."%'
    ";
$optunit="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $optunit.="<option value='".$bar->kodeorganisasi."'>".$bar->namaorganisasi."</option>";
}

//get existing period
$str="select distinct periode from ".$dbname.".setup_periodeakuntansi
      order by periode desc";
$optperiode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch())
{
        $optperiode.="<option value='".$bar->periode."'>".$bar->periode."</option>";
}

?>
<fieldset style="float:left;">
<legend><b><?php echo $_SESSION['lang']['form']?></b></legend>
<table cellspacing="1" border="0" >
<tr><td><label><?php echo $_SESSION['lang']['unit']?></label></td><td>:</td><td><select id=unit style='width:200px;' onchange=ambilJurnal()><?php echo $optunit; ?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['periode']?></label></td><td>:</td><td><select id=periode style='width:200px;' onchange=ambilJurnal()><?php echo $optperiode; ?></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['nojurnal']." ".$_SESSION['lang']['dari']?></label></td><td>:</td><td><select id=jurnaldari style='width:200px;' onchange=hideById('printPanel')><option value=""></option></select></td></tr>
<tr><td><label><?php echo $_SESSION['lang']['nojurnal']." ".$_SESSION['lang']['sampai']?></label></td><td>:</td><td><select id=jurnalsampai style='width:200px;' onchange=hideById('printPanel')><option value=""></option></select></td></tr>

<tr><td><td><td><button class=mybutton onclick=getLaporanPeriksaJurnal()><?php echo $_SESSION['lang']['proses'] ?></button></td></tr>


</table>
</fieldset>
<?

CLOSE_BOX();
OPEN_BOX('','');
echo"<fieldset><legend>".$_SESSION['lang']['result']."</legend>
	      <span id=printPanel style='display:none;'>
                  <img onclick=periksajurnalKeExcel(event,'keu_slave_2periksaJurnal_Excel.php') src=images/excel.jpg class=resicon title='MS.Excel'> 
                  <img onclick=periksajurnalKePDF(event,'keu_slave_2periksaJurnal_pdf.php') title='PDF' class=resicon src=images/pdf.jpg>
            </span>    
            <div style='width:100%;height:359px;overflow:auto;'>
                  <div id=containerr></div>
            </div></fieldset>";
CLOSE_BOX();
close_body();
?>