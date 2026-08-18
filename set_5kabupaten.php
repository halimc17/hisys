<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/set_5kabupaten.js'></script>

<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('set_5kabupaten').'</span>');

$sOrg = "select id,provinsi from " . $dbname . ".provinsi";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) 
{
    $optOrg.="<option value='" . $rOrg['id'] . "'>" . $rOrg['provinsi'] ."</option>";
}


echo"<fieldset>
	<legend>".$_SESSION['lang']['kabupaten']."</legend>
	<table>
		
		<tr>
			<td>".$_SESSION['lang']['id']."</td>
			
			<td>
				<input id=id class=myinputtext size=10px disabled>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['id']."_Prov</td>
			
			<td>
				<input id=id_prov class=myinputtext size=10px disabled>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['provinsi']."</td>
            <td><select id=provinsi name=provinsi  style=width:200px>".$optOrg ."</select></td>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kabupaten']."</td>
            <td><input id=kabupaten class=myinputtext style=width:195px></td>
			
		</tr>
		<tr>
			<td></td>
			<td>
				<input type='hidden' id='method' value='insert'>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
		<tr>
			<td></td>
			<td><div id=anggota style='display:none'></td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX();
OPEN_BOX();
echo "<fieldset> <legend>".$_SESSION['lang']['list']."</legend>
<div id=container style='overflow:auto; height:350px;'>";
$str1 = "select provinsi,a.id,a.id_prov,kabupaten from " . $dbname . ".kabupaten a left join " . $dbname . ".provinsi b on a.id_prov=b.id";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0>
	     <thead>
		 <tr class=rowheader>
		 <td style='width:100px;' align=center>" . $_SESSION['lang']['provinsi'] ."</td>
		 <td style='width:100px;' align=center>" . $_SESSION['lang']['kabupaten'] . "</td>
		<td style='width:30px;'>aksi</td></tr>
		 </thead>
		 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"
		<tr class=rowcontent>
		<td align=left>" . $bar1->provinsi . "</td>
		<td align=left>" . $bar1->kabupaten . "</td>
			<td align=center><img src=images/application/application_edit.png class=resicon caption='Edit' onclick=\"fillField('" . $bar1->id . "','" . $bar1->id_prov . "','" . $bar1->kabupaten . "','" . $bar1->id_prov . "');\"></td>
		</tr>";
}

echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
		 
echo "</div>";
CLOSE_BOX();
echo close_body();
?>