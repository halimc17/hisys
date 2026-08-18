<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/vhc_5premialatberat.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
$frm[0]='';
$frm[1]='';

$optjenis=$optpt=$optPosition="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where tipe='PT'";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optpt.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str = "SELECT * FROM ".$dbname.".vhc_5jenisvhc where kelompokvhc='AB'"; 
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optjenis.="<option value='".$bar['jenisvhc']."'>".$bar['kelompokvhc']." - ".$bar['jenisvhc']." - ".$bar['namajenisvhc']."</option>";
}	

$optKary="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$arrPos=array("Operator","Helper","Sopir");
foreach($arrPos as $brs => $isi){
	$optPosition.="<option value=".$brs.">".$isi."</option>";
}

?>


<?php

OPEN_BOX('','<span class=judul>'.getMenu('vhc_5premialatberat').'</span>');

echo "<br>";

echo"<fieldset style=float:left>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['pt']."</td>
					<td>:</td>
					<td><select id=pt style=\"width:150px;\" >".$optpt."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jenisvch']."</td>
					<td>:</td>
					<td><select id=jenis style=\"width:150px;\" >".$optjenis."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['vhc_posisi']."</td>
					<td>:</td>
					<td><select id=posisi name=posisi style=width:150px;>".$optPosition."</select></select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['premlebihbasis']." 1 (HM)</td> 
					<td>:</td>
					<td><input type=text id=basis onkeypress=\"return angka_doang(event);\"  class=myinputtextnumber style=\"width:80px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['premlebihbasis']." 1 (Rp)</td> 
					<td>:</td>
					<td><input type=text id=premibasis onkeypress=\"return angka_doang(event);\"  class=myinputtextnumber style=\"width:80px;\"></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['premlebihbasis']." 2 (Rp)</td> 
					<td>:</td>
					<td><input type=text id=premilebihbasis onkeypress=\"return angka_doang(event);\"  class=myinputtextnumber style=\"width:80px;\"></td>	</tr>			
				</tr>
				
				<tr>
					<td></td><td></td>
					<td><button class=mybutton onclick=simpan()>Simpan</button>
					<button class=mybutton onclick=hapus()>Batal</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>";
		
		

CLOSE_BOX();
OPEN_BOX();
echo"<fieldset style=float:left>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData(0)</script>
		</div>
	</fieldset>";


CLOSE_BOX();
echo close_body();

?>