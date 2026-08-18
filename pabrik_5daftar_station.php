<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zMysql.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/pabrik_5daftar_station.js'></script>

<?php

$arr = "##stationId##method";

$optKebun = "";
$strKebun = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['lokasitugas']."' and tipe='STATION'";
$resKebun=$owlPDO->query($strKebun) or die(print " Gagal: ".PDOException::getMessage());
$resKebun->setFetchMode(PDO::FETCH_OBJ);
while ($barKebun = $resKebun->fetch()) {
    $optKebun.="<option value='" . $barKebun->kodeorganisasi . "'>" . $barKebun->kodeorganisasi . " - " . $barKebun->namaorganisasi . "</option>";
}
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper(getMenu('pabrik_5daftar_station')).'</span>');

echo"<div style=clear:both></div><fieldset style=float:left;>
     <legend>".$_SESSION['lang']['form']."</legend>
	 <table>
	 <tr>
	   <td>" . $_SESSION['lang']['station'] . "</td>
	   <td><select id=\"stationId\" name=\"stationId\" style=\"width:150px\">" . $optKebun . "</select></td>
	 </tr>
	 </table>
         <input type=hidden value=insert id=method>
         <button class=mybutton onclick=savehk('pabrik_slave_5daftar_station','" . $arr . "')>" . $_SESSION['lang']['save'] . "</button>
         <button class=mybutton onclick=cancelIsi()>" . $_SESSION['lang']['cancel'] . "</button>
     </fieldset><input type='hidden' id=oldtanggal name=oldtanggal />";
CLOSE_BOX();

OPEN_BOX();
echo"<fieldset><legend>" . $_SESSION['lang']['list'] . "</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td>No</td>
	   <td>" . $_SESSION['lang']['station'] . "</td>
	   <td>" . $_SESSION['lang']['action'] . "</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
echo"<script>loadData()</script>";
echo"</tbody>
     <tfoot>
     </tfoot>
     </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>