<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/legalnama.js></script>
<?php

include('master_mainMenu.php');

$optKategori = "";
$arrayKategori = makeOption($dbname,"legal_5kategoriijin","kodekategori,namakategori");
//print_r($arrayKategori);
foreach($arrayKategori as $key=>$val) {
    $optKategori.="<option value=".$key.">".$val."</option>";
}

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['namaijin']).'</span>');
echo"<fieldset style='width:500px;'>
	<table>
		 <tr>
			<td>" . $_SESSION['lang']['permitcode'] . "</td>
			<td><input type=text id=kodegolongan size=3 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
		 </tr>
		 <tr>
			<td>" . $_SESSION['lang']['namaijin'] . "</td>
			<td><input type=text id=namagolongan size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
		 </tr>
		 <tr>
			<td>" . $_SESSION['lang']['kategori'] . "</td>
			<td><select id=kodekategori>".$optKategori."</select></td>
		 </tr>
    <tr><td></td><td>
	<input type=hidden id=method value='insert'>
	<button class=mybutton onclick=simpanGolongan()>" . $_SESSION['lang']['save'] . "</button>
	<button class=mybutton onclick=cancelGolongan()>" . $_SESSION['lang']['cancel'] . "</button>
 </td></tr>
	</table>	
	</fieldset>";
echo open_theme($_SESSION['lang']['list']);
echo "<div id=container>";
$str1 = "select * from " . $dbname . ".legal_5nama order by kodeijin";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader>
			<td style='width:100px;' align=center>" . $_SESSION['lang']['kode'] . "</td>
			<td align=center>" . $_SESSION['lang']['nama'] . "</td>
			<td align=center>" . $_SESSION['lang']['kategori'] . "</td>
			<td style='width:30px;'>*</td></tr>
		 </thead>
		 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"<tr class=rowcontent><td align=center>" . $bar1->kodeijin . "</td><td>" . $bar1->namaijin . "</td><td>" . $arrayKategori[$bar1->kodekategori] . "</td><td  align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('" . $bar1->kodeijin . "','" . $bar1->namaijin . "','" . $bar1->kodekategori . "');\"></td></tr>";
}
echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>