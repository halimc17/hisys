<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/sdm_5jamkerja.js'></script>



<?php
include('master_mainMenu.php');		
	
$optunit=$optvhc=$optkop="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "SELECT distinct tipe FROM ".$dbname.".organisasi where length(kodeorganisasi)=4 ORDER BY tipe";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optunit.="<option value=".$bar['tipe'].">".$bar['tipe']."</option>";
}	



// $str="select * from ".$dbname.".log_5supplier where kodekelompok='S004' order by namasupplier asc";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
    // $optkop.="<option value='".$bar['supplierid']."'>" .$bar['namasupplier']. "</option>";
// }


$opthari.="<option value='Sun'>Sunday</option>";
$opthari.="<option value='Mon'>Monday</option>";
$opthari.="<option value='Tue'>Tuesday</option>";
$opthari.="<option value='Wed'>Wednesday</option>";
$opthari.="<option value='Thu'>Thursday</option>";
$opthari.="<option value='Fri'>Friday</option>";
$opthari.="<option value='Sat'>Saturday</option>";
// $opthari.="<option value='Sun'>Sunday</option>";



#buat jam dan menit
$jm=$mnt="";
for($i=0;$i<24;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $jm.="<option value=".$i.">".$i."</option>";
   $i++;
}
for($i=0;$i<60;)
{
	if(strlen($i)<2)
	{
		$i="0".$i;
	}
   $mnt.="<option value=".$i.">".$i."</option>";
   $i++;
}


?>
		

<?php

OPEN_BOX('','<span class=judul>'.getMenu('sdm_5jamkerja').'</span><br>');

echo"<fieldset style='float:left;'>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td>:</td>
					<td><select id=kodeunit style=\"width:150px;\" >".$optunit."</select></td>
				</tr>
				<tr>
					<td>Hari</td>
					<td>:</td>
					<td><select id=hari style=\"width:150px;\">".$opthari."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jammasuk']."</td>
					<td>:</td>
					<td><select id=jmMulai>".$jm."</select>:<select id=mnMulai>".$mnt."</select></td></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jamkeluar']."</td>
					<td>:</td>
					<td><select id=jmSelesai>".$jm."</select>:<select id=mnSelesai>".$mnt."</select></td>
				</tr>
				<tr>
					<td>Jam Kerja</td>
					<td>:</td>
					<td><input type=text id=jamkerja size=10 class=myinputtextnumber maxlength=4 onkeypress=\"return angka_doang(event);\"  style=\"width:75px;\"></td>
				</tr>
				
				<tr>
					<td></td><td></td>
					<td><button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
					<button class=mybutton onclick=hapus()>".$_SESSION['lang']['cancel']."</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>";
					
	
					
CLOSE_BOX();
?>

<?php
OPEN_BOX();
echo "
		<div id=container> 
			<script>loadData()</script>
		</div>
	";
CLOSE_BOX();
echo close_body();					
?>

