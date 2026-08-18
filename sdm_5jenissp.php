<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript src='js/sdm_jenissp.js?v=<?php echo time(); ?>'></script>

<?
include('master_mainMenu.php');

OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['jenissp']).'</span>');

echo"<fieldset style='width:500px;'><table>
     <tr><td>".$_SESSION['lang']['tipe']."</td><td><input type=text id=kodejabatan size=3 maxlength=4 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
	 <tr><td>".$_SESSION['lang']['keterangan']."</td><td><input type=text id=namajabatan size=40 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext></td></tr>
     </table>
	 <input type=hidden id=method value='insert'>
	 <button class=mybutton onclick=simpanJSP()>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelJSP()>".$_SESSION['lang']['cancel']."</button>
	 </fieldset>";
// echo open_theme($_SESSION['lang']['availvhc']);
echo "<div id=container>";
	$str1="select * from ".$dbname.".sdm_5jenissp order by kode";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	echo"<table class=sortable cellspacing=1 cellpadding=7 border=0 style='width:100%;'>
	     <thead>
		 <tr align=center class=rowheader>
			<td style='width:150px;'>".$_SESSION['lang']['tipe']."</td><td>".$_SESSION['lang']['keterangan']."</td>
			<td style='width:30px;'>*</td></tr>
		 </thead>
		 <tbody>";
	while($bar1=$res1->fetch())
	{
		echo"<tr  class=rowcontent>
		<td align=center>".$bar1->kode."</td>
		<td>".$bar1->keterangan."</td>
		<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->keterangan."');\"></td></tr>";
	}	 
	echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";
echo "</div>";
// echo close_theme();
CLOSE_BOX();
echo close_body();
?>