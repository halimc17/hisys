<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>

<script language=javascript1.2 src='js/pabrik_5shiftv.js'></script>



<?php
include('master_mainMenu.php');		
	
$optpks="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str="SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PABRIK'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optpks.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}

?>


<?php

#buat jam dan menit
$jm=$mnt="";
for($i=0;$i<24;){
	if(strlen($i)<2){
		$i="0".$i;
	}
   $jm.="<option value=".$i.">".$i."</option>";
   $i++;
}
for($i=0;$i<60;){
	if(strlen($i)<2){
		$i="0".$i;
	}
   $mnt.="<option value=".$i.">".$i."</option>";
   $i++;
}

OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5shiftv').'<br></span>');
echo"<fieldset style='float:left;'>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td colspan=4><select id=pks style=\"width:135px;\" >".$optpks."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['shift']."</td>
					<td>:</td>
					<td><input type=text id=shift class=myinputtextnumber maxlength=1 onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jammulai']."</td>
					<td>:</td>
					<td><select id=jmmulai>".$jm."</select>:<select id=mnmulai>".$mnt."</select>
					</td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jamselesai']."</td>
					<td>:</td>
					<td><select id=jmselesai>".$jm."</select>:<select id=mnselesai>".$mnt."</select></td>
				</tr>
				
				
                                    <td></td><td></td>
                                    <td colspan=5><button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
                                    <button class=mybutton onclick=hapus()>".$_SESSION['lang']['cancel']."</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>
					<input type=hidden id=oldtgl value='insert'>
					<input type=hidden id=oldbjr value='insert'>
					<input type=hidden id=oldkdorg value='insert'>";
					
	
					
CLOSE_BOX();
?>

<?php
OPEN_BOX();
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo "<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData()</script>
		</div>
	</fieldset>";
CLOSE_BOX();
echo close_body();					
?>

