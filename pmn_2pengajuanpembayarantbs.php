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
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT' order by namaorganisasi asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$optpt.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

OPEN_BOX('','<span class=judul>Pengajuan Pembayaran TBS</span>');
echo"<br>";
$arr="##pt##tanggal";	
echo"<fieldset style='float:left;'>
        <legend>Form</legend>
		<table border=0 cellpadding=1 cellspacing=1>
			<tr>
				<td>".$_SESSION['lang']['pt']."</td>
				<td>:</td>
				<td><select id=pt style=\"width:168px;\">".$optpt."</select></td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']."</td>
				<td>:</td>
				<td>
					<input type=text class='myinputtext' readonly id='tanggal' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' value='".date('d-m-Y')."'/>
				</td>	
			</tr>
			<tr>
				<td><td><td>
				<button onclick=zPreview('pmn_slave_2pengajuanpembayarantbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
				<button onclick=zExcel(event,'pmn_slave_2pengajuanpembayarantbs.php','".$arr."') class=mybutton name=preview id=preview>".$_SESSION['lang']['excel']."</button>
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