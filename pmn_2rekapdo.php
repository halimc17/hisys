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
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zReport.js'></script>
<script type="text/javascript" src="js/pmn_2rekapdo.js"></script>
<script>


</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php
$title[1]=$_SESSION['lang']['form'];
$optPeriode="";
$sTgl="select distinct substr(tanggaldo,1,7) as periode from ".$dbname.".pmn_suratperintahpengiriman order by tanggaldo desc";
//$qTgl=mysql_query($sTgl) or die(mysql_error());
//while($rTgl=mysql_fetch_assoc($qTgl))
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while($rTgl=$qTgl->fetch())        
{
   $optPeriode.="<option value='".$rTgl['periode']."'>".substr($rTgl['periode'],5,2)."-".substr($rTgl['periode'],0,4)."</option>";
}

$sBar="select distinct b.kodebarang,c.namabarang from ".$dbname.".pmn_suratperintahpengiriman a
       inner join ".$dbname.".pmn_kontrakjual b on a.nokontrak=b.nokontrak 
       left join ".$dbname.".log_5masterbarang c on b.kodebarang=c.kodebarang
       where left(c.kodebarang,3)='400' and a.nokontrak!=''
	     order by namabarang";
//$qBar=mysql_query($sBar) or die(mysql_error());
$optBar="<option value=''>".$_SESSION['lang']['all']."</option>";;
//while($rBar=mysql_fetch_assoc($qBar))
$qBar=$owlPDO->query($sBar) or die(print " Gagal: ".PDOException::getMessage());
$qBar->setFetchMode(PDO::FETCH_ASSOC);
while($rBar=$qBar->fetch()){
  $scek="";
   $optBar.="<option value='".$rBar['kodebarang']."'>".$rBar['namabarang']."</option>";
}
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['rekap'].'(Delivery Order)').'</span><br>');
$arr="##periode##komoditi";
echo"<fieldset style=\"float: left;\">
<legend><b>".$title[1]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >";
echo"<tr><td>".$_SESSION['lang']['periode']."</td>";
echo"<td><select id=periode style=width:150px;>".$optPeriode."</select></td>";
echo"</tr>";
echo"<tr><td>".$_SESSION['lang']['komoditi']."</td>
          <td><select id=komoditi style=width:150px;>".$optBar."</select></td>
          </tr>";
echo"<tr>
    <td colspan=\"2\"></td>
</tr>
<tr>
    <td><td>
         <button onclick=\"zPreviewd()\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'pmn_slave_2rekapdo.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
    </td>    
</tr>    
</table>
</fieldset>";

CLOSE_BOX();
OPEN_BOX();

echo"



<div id='printContainer' >
</div>";



CLOSE_BOX();
echo close_body();
?>