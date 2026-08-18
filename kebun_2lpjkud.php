<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
$arr0="##tanggal"; 
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript src='js/zReport.js'></script>
<script type="text/javascript" src="js/kebun_2lpjkud.js"></script>
<script>


</script>

<link rel='stylesheet' type='text/css' href='style/zTable.css'>

<?php
$title[1]=$_SESSION['lang']['laporan']." LPJ KUD";

$optPeriode="<option value=''>".$_SESSION['lang']['all']."</option>";
$iPer="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode desc limit 12 ";
$nPer=$owlPDO->query($iPer) or die(print " Gagal: ".PDOException::getMessage());
$nPer->setFetchMode(PDO::FETCH_ASSOC);
while($dPer=$nPer->fetch())
{
    $optPeriode.="<option value='".$dPer['periode']."'>".$dPer['periode']."</option>";
}

$optKud = "<option value=''>".$_SESSION['lang']['all']."</option>";
$sKud = "select * from ".$dbname.".log_5supplier where kodekelompok = 'S004'";
$qKud=$owlPDO->query($sKud) or die(print " Gagal: ".PDOException::getMessage());
$qKud->setFetchMode(PDO::FETCH_ASSOC);
while($rKud = $qKud->fetch()){
	$optKud .= "<option value='".$rKud['supplierid']."'>".$rKud['namasupplier']."</option>";
}

$arr="##periode##namakud";
echo"<fieldset style=\"float: left;\">
<legend><b>".$title[1]."</b></legend>
<table cellspacing=\"1\" border=\"0\" >";
echo"<tr><td>".$_SESSION['lang']['periode']."</td>";
echo"<td><select id=periode style=width:150px;>".$optPeriode."</select></td>";
echo"</tr>";
echo"<tr><td>".$_SESSION['lang']['namakud']."</td>
          <td><select id=namakud style=\"width:150px;\">".$optKud."</select></td>
          </tr>";
echo"<tr height=\"1\">
    <td colspan=\"2\">&nbsp;</td>
</tr>
<tr>
    <td colspan=\"2\">
         <button onclick=\"priview()\" class=\"mybutton\" name=\"preview\" id=\"preview\">Preview</button>
    <button onclick=\"zExcel(event,'kebun_slave_2lpjkud.php','".$arr."')\" class=\"mybutton\" name=\"preview\" id=\"preview\">Excel</button>
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



CLOSE_BOX();
echo close_body();
?>