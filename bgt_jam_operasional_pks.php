<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/bgt_jam_operasional_pks.js?ver=1.5></script>
<?php
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('BUDGET '.$_SESSION['lang']['jamoperasional']).'</span><br>');

$optOrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$sql = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK' and kodeorganisasi='".$_SESSION['empl']['lokasitugas']."' ORDER BY kodeorganisasi";
$qry=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($data=$qry->fetch()){
	$optOrg.="<option value=".$data['kodeorganisasi'].">".$data['namaorganisasi']."</option>";
}
$optws="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

echo"<fieldset style='float:left;'>
          <legend><b>".$_SESSION['lang']['jamoperasional']."</b></legend><table> 	

             <tr><td width=100>".$_SESSION['lang']['budgetyear']."<td width=10>:</td></td><td><input type=text id=tahunbudget size=10 onkeypress=\"return angka(event,'0123456789');validatefn(event);\" class=myinputtext maxlength=4 style=\"width:145px;\"></td></tr>
                 <tr><td>".$_SESSION['lang']['unit']."</td><td>:</td><td><select id=kodeorg name=kodeorg style=\"width:150px;\">".$optOrg."</select></td></tr>
                 
                 <tr><td>Kapasitas Pabrik</td><td>:</td><td><input type=text class=myinputtextnumber id=kapspabrik name=kapspabrik onkeypress=\"return angka_doang(event);\" style=\"width:145px;\"  /></td></tr>

                 <tr><td>Througput Effeciency</td><td>:</td><td><input type=text class=myinputtextnumber id=threff name=threff onkeypress=\"return angka_doang(event);\" style=\"width:145px;\"  /></td></tr>

                 <tr><td>Commercial Factor</td><td>:</td><td><input type=text class=myinputtextnumber id=commfac name=commfac onkeypress=\"return angka_doang(event);\" style=\"width:145px;\"  /></td></tr>

                 <tr><td>".$_SESSION['lang']['totJamThn']."</td><td>:</td><td><input type=text class=myinputtextnumber id=jamo name=jmo onkeypress=\"return angka_doang(event);\" style=\"width:145px;\"  /></td></tr>
                 <tr><td>".$_SESSION['lang']['totbreak']."</td><td>:</td><td><input type=text class=myinputtextnumber id=jamb name=jamb onkeypress=\"return angka_doang(event);\" style=\"width:145px;\"  /></td></tr>
     </table> 
         <table>
          <tr>
                 <td style='width:122px;'></td>
                         <input type=hidden id=method value='insert'>
                                <input type=hidden id=oldtahunbudget value='insert'>
                                <input type=hidden id=oldkodeorg value='insert'>
                 <td>
                         <button class=mybutton onclick=simpanpks()>".$_SESSION['lang']['save']."</button>
                         <button class=mybutton onclick=batalpks()>".$_SESSION['lang']['cancel']."</button>
                 <td>
          <tr>
         </table>
         </fieldset>";

echo"<div style=clear:both></div>";
echo open_theme($_SESSION['lang']['datatersimpan']);
echo"<div id=container>";
echo"<table class=sortable cellspacing=1 border=0>
	 <thead>
		 <tr class=rowheader>
			 <td style='width:5px'>".substr($_SESSION['lang']['nomor'],0,2)."</td>
				 <td style='width:50px'>".$_SESSION['lang']['budgetyear']."</td>
				 <td>".$_SESSION['lang']['unit']."</td>
				 <td>Kapasitas Pabrik</td>
                 <td>Througput Effeciency</td>
                 <td>Commercial Factor</td>
                 <td>".$_SESSION['lang']['totJamThn']."</td>
				 <td>".$_SESSION['lang']['totbreak']."</td>
				 <td style='width:30px;'>".$_SESSION['lang']['edit']."</td>
		 </thead>
		 <tbody id='containerData'><script>loadData()</script>";

echo"</tbody><tfoot></tfoot></table>";
echo "</div>";
echo close_theme();
CLOSE_BOX();
echo close_body();
?>