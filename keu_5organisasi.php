<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/keu_5organisasi.js'></script>
<script language="javascript" src='js/zTools.js'></script>

<?php
$otpPt = $optAkun =$Pt= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$sql = "SELECT * FROM " . $dbname . ".organisasi where tipe='PT' order by kodeorganisasi asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $otpPt.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
}

$sql = "SELECT * FROM " . $dbname . ".keu_5akun where level='5' and substr(noakun,1,3) in ('114') order by noakun asc";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
    $optAkun.="<option value=" . $bar['noakun'] . ">" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
}

$optTipe="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
$arrTipe=getEnum($dbname,'keu_5organisasi','tipe');
foreach($arrTipe as $kei=>$fal)
{
	$optTipe.="<option value='".$kei."'>".$fal."</option>";
}

OPEN_BOX('','<span class=judul>'.getMenu('keu_5organisasi').'</span>');
echo"<div><fieldset>
    <legend>".$_SESSION['lang']['form']."</legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td> 
			<td>:</td>
			<td>
				<select id=tipe onchange=getPT() style=\"width:205px;\">" . $optTipe . "</select>
				
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['pt']." ".$_SESSION['lang']['induk']."</td> 
			<td>:</td>
			<td><select id=ptoptinduk onchange=getkodeorg() style=\"width:205px;\">" . $otpPt . "</select>
				<input type=text hidden id=ptfreeinduk nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\" onkeydown=\"upperCaseF(this)\" maxlength=3>
				<input type=text hidden id=namaptfreeinduk placeholder='Nama Perusahaan Induk' nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\" onkeydown=\"upperCaseF(this)\" >
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['pt']."</td> 
			<td>:</td>
			<td><select id=ptopt  style=\"width:205px;\">" . $otpPt . "</select>
				<input type=text hidden id=ptfree nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\" onkeydown=\"upperCaseF(this)\" maxlength=3>
				<input type=text hidden id=namaptfree placeholder='Nama Perusahaan' nkeypress=\"return_tanpa_kutip(event);\" class=myinputtext style=\"width:200px;\" onkeydown=\"upperCaseF(this)\" >
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['noakun']."</td> 
			<td>:</td>
			<td><select id=noakun  style=\"width:205px;\">" . $optAkun . "</select>
				<img id='noakun' onclick=z.elSearch('noakun',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td colspan=3>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
				<input type=hidden id=method value='insert'>
			</td>
		</tr>
	</table>
</fieldset><div>";

CLOSE_BOX();
?>

<?php
OPEN_BOX();
echo "<fieldset>
        <legend>".$_SESSION['lang']['list']."</legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top'>
				<tr>
					<td>
						<fieldset>
							<legend>".$_SESSION['lang']['find']."</legend>
							".$_SESSION['lang']['tipe']." : 
							<select id=find_tipe  onchange=loaddata(0) style=\"width:100px;\">" . $optTipe . "</select>
							
							&nbsp".$_SESSION['lang']['pt']." ".$_SESSION['lang']['induk']." : 
							<input type=text  id=find_ptinduk nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:75px;\" onkeydown=\"upperCaseF(this)\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){loaddata(0)}\">
							
							&nbsp".$_SESSION['lang']['pt']." : 
							<input type=text  id=find_pt nkeypress=\"return_tanpa_kutip(event);\"   class=myinputtext style=\"width:75px;\" onkeydown=\"upperCaseF(this)\" maxlength=100 onkeypress=\"key=getKey(event);if(key==13){loaddata(0)}\">
							
							<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
							<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
							
						</fieldset>
					</td> 
				</tr>
			</table>
		
        <div id=container> 
            <script>loaddata(0)</script>
        </div>
    </fieldset>";
CLOSE_BOX();
echo close_body();                  
?>