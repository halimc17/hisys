<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/sdm_rumahsakit.js></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['diagnosabaru']).'</span>');
$strKlDiag = "select kodekelompok,deskripsi from " . $dbname . ".sdm_5kldiagnosa order by deskripsi";
$resKlDiag=$owlPDO->query($strKlDiag) or die(print " Gagal: ".PDOException::getMessage());
$resKlDiag->setFetchMode(PDO::FETCH_OBJ);
$optKlDiagnosa='';
while ($barKlDiag = $resKlDiag->fetch()) {
    $optKlDiagnosa.="<option value='" . $barKlDiag->kodekelompok . "'>" . $barKlDiag->kodekelompok . "-" . $barKlDiag->deskripsi . "</option>";
}

echo OPEN_THEME($_SESSION['lang']['formdiagnosa']);
echo "<table>
		<tr>
			<td>" . $_SESSION['lang']['kelompokdiagnosa'] . "</td>
			<td>:</td>
			<td><select id='kodekelompok'>" . $optKlDiagnosa . "</select></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['id'] . "</td>
			<td>:</td>
			<td><input type=text class=myinputtext id=idx disabled size=3></td>
		</tr>
		<tr>
			<td>" . $_SESSION['lang']['nama'] . "</td>
			<td>:</td>
			<td><input type=text class=myinputtext id=name maxlength=100 size=45 onkeypress=\"return tanpa_kutip(event);\"></td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td><button class=mybutton onclick=saveDiagnosa()>" . $_SESSION['lang']['save'] . "</button></td>
		</tr>
	</table>";

echo CLOSE_THEME();
CLOSE_BOX();
OPEN_BOX('', $_SESSION['lang']['list'] . ':');
$str = "select * from " . $dbname . ".sdm_5diagnosa order by diagnosa";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
echo"<div style='wicth=100%; height:300px;overflow:scroll;'>
     <table class=sortable cellspacing=1 border=0>
     <thead>
	   <tr class=rowheader>
		<td>" . $_SESSION['lang']['id'] . "</td>
		<td>" . $_SESSION['lang']['kelompokdiagnosa'] . "</td>
		<td>" . $_SESSION['lang']['nama'] . "</td>
		<td>Edit</td></tr>
	 </tr>
	 </thead>
	 <tbody id=tbody>";
while ($bar = $res->fetch()) {
    echo"<tr class=rowcontent>
	      <td class=firsttd>" . $bar->id . "</td>
		  <td>" . $bar->kodekelompok . "</td>
		  <td>" . $bar->diagnosa . "</td>
		  <td><img src=images/edit.png align=middle style='cursor:pointer;' onclick=\"editDiagnosa('" . $bar->id . "','" . $bar->kodekelompok . "','" . $bar->diagnosa . "');\" height=17px align=right title='Edit data for " . $bar->diagnosa . "'></td>
	     </tr>";
}
echo "</tbody>
      <tfoot>
	  </tfoot>
	  </table>
	  </div>";
CLOSE_BOX();
echo close_body();
?>