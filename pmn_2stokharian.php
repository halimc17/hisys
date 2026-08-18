<?

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');

$arr0 = "##tanggal";
?>
<script language=javascript src='js/zTools.js'></script>
<script language="javascript" src="js/zMaster.js"></script>
<script language=javascript src='js/zReport.js'></script>
<script type="text/javascript" src="js/pmn_2stokharian.js?ver=1.4"></script>
<script>


</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php

$title[1] = $_SESSION['lang']['form'];

$sTgl = "select distinct substr(tanggalsisa,1,7) as periode from " . $dbname . ".sdm_pjdinasht order by tanggalsisa desc";
//$qTgl=mysql_query($sTgl) or die(mysql_error());
$optPeriode = "";
//while($rTgl=mysql_fetch_assoc($qTgl))
$qTgl = $owlPDO->query($sTgl) or die(print " Gagal: " . PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while ($rTgl = $qTgl->fetch()) {
    $optPeriode.="<option value='" . $rTgl['periode'] . "'>" . substr($rTgl['periode'], 5, 2) . "-" . substr($rTgl['periode'], 0, 4) . "</option>";
}

$sLoc = "select kodeorganisasi,namaorganisasi,alokasi from " . $dbname . ".organisasi 
      where namaorganisasi like '%BULKING%' 
	  order by namaorganisasi";
$optLoc = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
$qLoc = $owlPDO->query($sLoc) or die(print " Gagal: " . PDOException::getMessage());
$qLoc->setFetchMode(PDO::FETCH_ASSOC);
while ($rLoc = $qLoc->fetch()) {
    $optLoc.="<option value='" . $rLoc['kodeorganisasi'] . "'>" . $rLoc['kodeorganisasi'] . "-" . $rLoc['namaorganisasi'] . "</option>";
}




OPEN_BOX('','<span class=judul>'.getMenu('pmn_2stokharian').'</span>');

$arr = "##tanggaldari##tanggalsampai##unit";
echo"<br><fieldset style=\"float: left;\">
<legend><b>" . $title[1] . "</b></legend>
<table cellspacing=\"1\" border=\"0\" >";

echo"<tr><td>" . $_SESSION['lang']['pt'] . "</td>
          <td colspan=3><select id=unit style=width:100px;>" . $optLoc . "</select></td>
          </tr>";

echo"<tr><td>" . $_SESSION['lang']['tanggal'] . "</td>";
echo"<td><input type=text class=myinputtext id=tanggaldari readonly onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:100px;  maxlength=10 /></td>";
echo"<td hidden>" . $_SESSION['lang']['sd'] . "</td>
          <td hidden><input  type=text class=myinputtext id=tanggalsampai readonly onmousemove=setCalendar(this.id) onkeypress=return false;  style=width:70px;  maxlength=10 /></td>
          </tr>";






echo"<tr>
    <td></td>
</tr>
<tr>
    <td><td colspan=3>

<button onclick=zPreview('pmn_slave_2stokharian','" . $arr . "','printContainer') class=mybutton name=preview id=preview>" . $_SESSION['lang']['preview'] . "</button>
		

      <button onclick=\"zExcel(event,'pmn_slave_2stokharian.php','" . $arr . "')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
<button class=mybutton onclick=\"pdf('event')\">".$_SESSION['lang']['pdf']."</button>
    </td>    
</tr>    
</table>
</fieldset>

<div style=\"margin-bottom: 30px;\">
</div>
<fieldset style='clear:both'><legend><b>Print Area</b></legend>

<div id='printContainer' style='overflow:auto;height:250px;max-width:1220px;'>
</div>
</fieldset>";


//   <button onclick=\"zPreviewd()\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>

CLOSE_BOX();
echo close_body();
?>