<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/legalpihak.js></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['pihakygproses']).'</span>');
echo"<fieldset style='width:500px;'>
	<table>
		 <tr>
			<td>" . $_SESSION['lang']['kodepihak'] . "</td>
			<td><input type=text id=kodegolongan size=3 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
		 </tr>
		 <tr>
			<td>" . $_SESSION['lang']['namapihak'] . "</td>
			<td><input type=text id=namagolongan size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
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
$str1 = "select * from " . $dbname . ".legal_5pihak order by kodepihak";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader>
			<td style='width:150px;' align=center>" . $_SESSION['lang']['kodepihak'] . "</td>
			<td align=center>" . $_SESSION['lang']['namapihak'] . "</td>
			<td style='width:30px;'>*</td></tr>
		 </thead>
		 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"
		<tr class=rowcontent>
			<td align=center>" . $bar1->kodepihak . "</td>
			<td>" . $bar1->namapihak . "</td>
			<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('" . $bar1->kodepihak . "','" . $bar1->namapihak . "');\"></td>
		</tr>";
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