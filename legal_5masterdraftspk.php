<?php

//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/legal_5masterdraftspk.js></script>
<?php

include('master_mainMenu.php');
function checkstat($statt)
{
	$retstatt='';
	if($statt==1)
	{
		$retstatt = 'Aktif';
	}
	else
	{
		$retstatt = 'Tidak Aktif';
	}

	return $retstatt;

}

OPEN_BOX('','<span class=judul>'.strtoupper('Master Draft SPK').'</span>');
echo"<fieldset style='width:500px;'>
	<table>
		 <tr>
			<td>Nama Jenis</td>
			<td><input type=text id=namajenis size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td>
		 </tr>
		 <tr>
			<td>" . $_SESSION['lang']['status'] . "</td>
			<td><input type=checkbox id=status > Aktif </td>
		 </tr>
		 <tr><td></td><td>
	<input type=hidden id=kodeid >
	<input type=hidden id=proses value='insert'/>
	<button class=mybutton onclick=saveMD()>" . $_SESSION['lang']['save'] . "</button>
	<button class=mybutton onclick=cancelMD()>" . $_SESSION['lang']['cancel'] . "</button>
    </td></tr>
	</table>
	 </fieldset>";
echo open_theme($_SESSION['lang']['list']);
echo "<div id=container>";
$str1 = "select * from " . $dbname . ".legal_5masterdraftspk order by namajenis";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader>
			<td style='width:150px;' align=center>Nama Jenis</td>
			<td align=center>" . $_SESSION['lang']['status'] . "</td>
			<td  style='width:30px;'>*</td>
		 </thead>
		 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"
		<tr class=rowcontent>
			<td align=center>" . $bar1->namajenis . "</td>
			<td>" . checkstat($bar1->status) . "</td>
			<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('" . $bar1->id . "','" . $bar1->namajenis . "','" . $bar1->status . "');\"></td>
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