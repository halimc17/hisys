<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript src='js/zTools.js'></script>
<script language=javascript1.2 src='js/set_5desa.js'></script>

<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('set_5desa').'</span>');
$sOrg = "select idkec,kecamatan from " . $dbname . ".kecamatan";
$qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
$qOrg->setFetchMode(PDO::FETCH_ASSOC);
while ($rOrg = $qOrg->fetch()) 
{
    $optOrg.="<option value='" . $rOrg['idkec'] . "'>" . $rOrg['kecamatan'] ."</option>";
}


echo"<fieldset>
	<legend>".$_SESSION['lang']['desa']."</legend>
	<table>
		
		<tr>
			<td>".$_SESSION['lang']['id']."</td>
			
			<td>
				<input id=id class=myinputtext size=10px disabled>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['id']."_Kec</td>
			
			<td>
				<input id=id_kec class=myinputtext size=10px disabled>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['kecamatan']."</td>
            <td><select id=kecamatan name=kecamatan  style=width:200px>".$optOrg ."</select></td>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['desa']."</td>
            <td><input id=desa class=myinputtext style=width:195px></td>
			
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
$str1 = "select * from " . $dbname . ".desa a left join " . $dbname . ".kecamatan b on a.id_kec=b.idkec
order by a.id_kec";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 cellpadding=3 border=0 >
	     <thead>
		 <tr class=rowheader>
		 <td style='width:100px;' align=center>" . $_SESSION['lang']['kecamatan'] ."</td>
		 <td style='width:100px;' align=center>" . $_SESSION['lang']['desa'] . "</td>
		<td style='width:10px;'>aksi</td></tr>
		 </thead>
		 <tbody>";
while ($bar1 = $res1->fetch()) {
    echo"
		<tr class=rowcontent>
		<td align=left>" . $bar1->kecamatan . "</td>
		<td align=left>" . $bar1->desa . "</td>
			<td align=center>
				<img src=images/application/application_edit.png class=resicon title='Edit' onclick=\"fillField('" . $bar1->id_kec . "','" . $bar1->iddes . "','" . $bar1->idkec . "','" . $bar1->desa . "');\">&nbsp;
				<img src=images/application/application_delete.png class=resicon title='Delete' onclick=\"deletefield('".$bar1->iddes."','".$bar1->idkec."');\">
			</td>
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