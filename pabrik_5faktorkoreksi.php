<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');

echo open_body();
?>

<script language=javascript1.2 src='js/pabrik_5faktorkoreksi.js'></script>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>

<?php
include('master_mainMenu.php');	
if(empty(getOrgDetail(13))){
	$rusak = "<span class=judul style=color:blue;font-weight:bold;font-size:30px;text-align:center>Anda tidak memiliki detail akses Pabrik, Silahkan hubungi Administrator.</span>";
	exit($rusak);
}
if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
	$rusak = "<span class=judul style=color:black;font-weight:bold;font-size:30px;text-align:center>Lokasi tugas anda bukan di Pabrik, Silahkan pindah lokasitugas <a href=\"javascript:do_load('setup_pindahLokasiTugas')\" title='Klik disini untuk pindah lokasi tugas'>disini</a>.</span>";
	exit($rusak);
}
$frm[0]='';
$frm[1]='';

$opttipe=$optkomoditi=$optpks=$opttangki=$optvhc=$optbrg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$str = "SELECT kodeorganisasi,namaorganisasi FROM ".$dbname.".organisasi where kodeorganisasi='".$_SESSION['empl']['lokasitugas']."'";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $optpks.="<option value=".$bar['kodeorganisasi'].">".$bar['namaorganisasi']."</option>";
}

$str = "SELECT * FROM ".$dbname.".pabrik_5tangki where kodeorg='".$_SESSION['empl']['lokasitugas']."'";   
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $opttangki.="<option value='".$bar['kodetangki']."'>".$bar['keterangan']." [".$bar['komoditi']."]</option>";
}	
?>


<?php
OPEN_BOX('','<span class=judul>'.getMenu('pabrik_5faktorkoreksi').'</span>');
// style=\"width:550px;float:left\" 
echo "<br>";

echo"<fieldset style=float:left>
		<legend>".$_SESSION['lang']['entryForm']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['pabrik']."</td>
					<td>:</td>
					<td><select id=pabrik style=\"width:150px;\" onchange=\"gettangki()\" >".$optpks."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tangki']."</td>
					<td>:</td>
					<td><select id=tangki style=\"width:150px;\" disabled >".$opttangki."</select></td>
				</tr>
				<tr>
					<td width=100>".$_SESSION['lang']['nilai']." Angka</td>
					<td>:</td>
					<td><input type=text id=nilaiangka size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:105px;\" /></td>
				</tr>	
				<tr>
					<td width=100>".$_SESSION['lang']['nilai']."</td>
					<td>:</td>
					<td><input type=text id=nilai size=10 class=myinputtextnumber value=0 maxlength=50 onkeypress=\"return angka_doang(event);\"  style=\"width:105px;\" /></td>
				</tr>			
				<tr>
					<td></td><td></td>
					<td><button class=mybutton onclick=simpan()>Simpan</button>
					<button class=mybutton onclick=hapus()>Batal</button></td>
				</tr>
			</table></fieldset>
					<input type=hidden id=method value='insert'>";
	/*echo"<fieldset >
		<legend>".$_SESSION['lang']['info']."</legend> 
			<table border=0 cellpadding=1 cellspacing=1>
				<tr>
					<td>Untuk <b>CPO Tangki Storage</b></td>
					<td>:</td> 
					<td>Sebagai setup untuk meja ukur dengan satuan centimeter (cm)</td>
				</tr>
				<tr>
					<td>Untuk <b>Kernel Silo</b></td>
					<td>:</td> 
					<td> Sebagai setup moister dengan satuan persen (%)</td>
				</tr>
				
			</table></fieldset>";	*/			
		

CLOSE_BOX();
OPEN_BOX();
// fieldset style=\"width:1000px;\" 
//$optTahunBudgetHeader="<option value=''>".$_SESSION['lang']['all']."</option>";
//ISI UNTUK DAFTAR 
echo"<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<div id=container> 
			<script>loadData(0)</script>
		</div>
	</fieldset>";


CLOSE_BOX();
echo close_body();

?>