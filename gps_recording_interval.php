<?php
//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src=js/gpsInterval.js></script>
<?php

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>GPS Recosding Interval</span>');

echo"<fieldset style='width:500px;'><table>
	 <tr><td>Interval</td><td><input type=text id=interval size=20 maxlength=8 onkeypress=\"return angka_doang(event);\" class=myinputtext></td></tr>
	 <tr><td>Allow Upload Photo</td><td><select id=alo><option value=0>Not Allowed</option><option value=1>Allow</option></td></tr>
	 <tr><td colspan=2><button class=mybutton onclick=simpanInterval()>" . $_SESSION['lang']['save'] . "</button></td></tr>
     </table>
		 <fieldset><legend>Info</legend>
		  OWL-Mobile Apps will get this information to start Location recording on each devices.<br>
		  1000ms=1sec, default is 300,000 (5 minutes).<br>
		  If you don't want GPS tracking on Client, set this value to 0<br>
		  Allow Upload will enable user to upload photo from mobile transaction, required large space on server.
		  <br>Save to change the value.
		 </fieldset>
	 </fieldset>";
echo open_theme('Current:');

$str1 = "select * from " . $dbname . ".gps_interval";
$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
$res1->setFetchMode(PDO::FETCH_OBJ);
echo"<table class=sortable cellspacing=1 border=0 style='width:500px;'>
	     <thead>
		 <tr class=rowheader><td>Interval</td><td>AlowUpload</td></tr>
		 </thead>
		 <tbody id=container>";
while ($bar1 = $res1->fetch()) {
    echo"<tr class=rowcontent><td>".$bar1->interval."</td><td>".$bar1->enableupload."</td></tr>";
}
echo"	 
		 </tbody>
		 <tfoot>
		 </tfoot>
		 </table>";

echo close_theme();
CLOSE_BOX();
echo close_body();
?>