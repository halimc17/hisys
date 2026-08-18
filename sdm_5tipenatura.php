<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/sdm_5tipenatura.js'></script>
<?php
include('master_mainMenu.php');
// OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['setupnatura']).'</span>');
OPEN_BOX('','<span class=judul>'.getMenu('sdm_5tipenatura').'</span>');


echo"<fieldset style='width:500px;'><table>
	 <tr><td>".$_SESSION['lang']['kode']."</td><td><input type=text id=kode style='width:200px;' size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=45></td></tr>
	 <tr><td>".$_SESSION['lang']['keterangan']."</td><td><input type=text id=keterangan style='width:200px;' size=30 onkeypress=\"return tanpa_kutip(event);\" class=myinputtext maxlength=45></td></tr>
	 </table>
	 <input type=hidden id=method value='insert'>
	 <button class=mybutton onclick=simpanJ()>".$_SESSION['lang']['save']."</button>
	 <button class=mybutton onclick=cancelJ()>".$_SESSION['lang']['cancel']."</button>
	 </fieldset><br>";
echo open_theme($_SESSION['lang']['list']);
echo "<div>";
	$str1="select *  from ".$dbname.".sdm_5catuporsi "; 
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	echo"<table class=sortable cellspacing=1 border=0 style='width:700px;'>
	     <thead>
		 <tr class=rowheader>
			<td>".$_SESSION['lang']['kode']."</td>
			<td>".$_SESSION['lang']['keterangan']."</td>
			<td style='width:30px;'>*</td></tr>
		 </thead>
		 <tbody id=container>"; 
	while($bar1=$res1->fetch())
	{
		echo"<tr class=rowcontent>
				<td align=center>".$bar1->kode."</td>    
				 <td>".$bar1->keterangan."</td>    
				
				<td><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->kode."','".$bar1->keterangan."');\"></td></tr>";
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