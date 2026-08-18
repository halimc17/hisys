<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/set_5propinsi.js'></script>

<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('set_5propinsi').'</span>');



echo"<fieldset>
	<legend>".$_SESSION['lang']['provinsi']."</legend>
	<table>
		<tr>
			<td>".$_SESSION['lang']['id']."</td>
			<td>:</td>
			<td>
				<input id=id class=myinputtext size=10px disabled>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['provinsi']."</td>
			<td>:</td>
			<td>
				<input id=provinsi class=myinputtext size=29px>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type='hidden' id='method' value='insert'>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
		
	</table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();

 
echo "<fieldset> <legend>".$_SESSION['lang']['list']."</legend>
<div id=container style='overflow:auto; height:350px;'>";
$str1 = "select * from " . $dbname . ".provinsi order by provinsi";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 >
	     <thead>
		 <tr class=rowheader>
		 
		 <td align=center>" . $_SESSION['lang']['provinsi'] . "</td>
		<td style='width:10px;' align=center>aksi</td></tr>
		 </thead>
		 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"
		<tr class=rowcontent>
		
		<td align=left>" . $bar1->provinsi . "</td>
			<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->id . "','" . $bar1->provinsi . "');\"></td>
		</tr>";
}

echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>
		 </div>
		 ";
		 
echo "</div>";

CLOSE_BOX();
echo close_body();
?>