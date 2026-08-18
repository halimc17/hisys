<?
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

$arr0="##tanggal"; 
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script type="text/javascript" src="js/sdm_2rekapperjalanandinas.js"></script>
<script>


</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['rekap']." ".$_SESSION['lang']['perjalanandinas']).'</span>');
$title[1]=$_SESSION['lang']['rekap']." ".$_SESSION['lang']['perjalanandinas'];

$sTgl="select distinct substr(tanggalperjalanan,1,7) as periode from ".$dbname.".sdm_pjdinasht order by tanggalperjalanan desc";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
$optPeriode="";
while($rTgl=$qTgl->fetch())
{
   $optPeriode.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}

$sLoc="select kodeorganisasi,namaorganisasi,alokasi from ".$dbname.".organisasi 
      where length(kodeorganisasi)=4 
	  order by namaorganisasi";
$qLoc=$owlPDO->query($sLoc) or die(print " Gagal: ".PDOException::getMessage());
$qLoc->setFetchMode(PDO::FETCH_ASSOC);    
$optLoc="<option value=''>".$_SESSION['lang']['all']."</option>";;
while($rLoc=$qLoc->fetch())
{
   $optLoc.="<option value='".$rLoc['kodeorganisasi']."'>".$rLoc['kodeorganisasi']."-".$rLoc['namaorganisasi']."</option>";
}

$arr="##periode##lokasitugas##namakaryawan";
echo"<br><fieldset style=\"float: left;\">
<legend><b>Form</b></legend>
<table cellspacing=\"1\" border=\"0\" >";
echo"<tr><td>".$_SESSION['lang']['periode']."</td>";
echo"<td><select id=periode style=width:150px;>".$optPeriode."</select></td>";
echo"</tr>";
echo"<tr><td>".$_SESSION['lang']['lokasitugas']."</td>
          <td><select id=lokasitugas style=width:150px;>".$optLoc."</select></td>
          </tr>";
echo"<tr><td>".$_SESSION['lang']['namakaryawan']."</td>
          <td><input placeholder='".$_SESSION['lang']['all']."' type=text id=namakaryawan class=myinputtext style=width:145px;></td>
          </tr>";
echo"
<tr>
    <td></td><td>
    <button onclick=\"zPreviewd()\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'sdm_slave_2rekapperjalanandinas.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    </td>    
</tr>    
</table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo"

<fieldset style='clear:both'><legend><b>Print Area</b></legend>

<div id='printContainer' style='overflow:auto;height:350px;max-width:100%;'>
</div>
</fieldset>";



CLOSE_BOX();
echo close_body();
?>