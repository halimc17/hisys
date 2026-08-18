<?php
//Ind
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once ('config/connection.php');
require_once('lib/zLib.php');
echo open_body();
require_once('master_mainMenu.php');


?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?

##GET PT
$optpt="";
$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi in (select induk from ".$dbname.".organisasi where tipe='Pabrik' order by namaorganisasi asc)";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optpt.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$optperiode="";
$str="select periode from ".$dbname.".setup_periodeakuntansi group by periode order by periode asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optperiode.="<option value=".$bar['periode'].">".$bar['periode']."</option>";
}

OPEN_BOX('','<span class=judul>Data Pembelian TBS Supplier Luar</span>');
echo"<br>";
$arr="##pt##periode";	
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
		<table border=0 cellpadding=1 cellspacing=1>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td><select id=pt style=\"width:168px;\" >".$optpt."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['periode']."</td>
				<td>:</td>
				<td><select id=periode style=\"width:168px;\">".$optperiode."</select></td>
			</tr>
			<tr>
				<td><td><td>
				<button onclick=zPreview('pmn_slave_2datapembeliantbsluar','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
				<button onclick=zExcel(event,'pmn_slave_2datapembeliantbsluar.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
				</td>
			</tr>
		</table>
</fieldset>";

echo"
<fieldset style='clear:both'><legend><b>".$_SESSION['lang']['printArea']."</b></legend>
<div id='printContainer' style='overflow:auto;height:400px;max-width:1220px'; >
</div></fieldset>";

CLOSE_BOX();
echo close_body();

?>