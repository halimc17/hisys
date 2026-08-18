<?//@Copy nangkoelframework//-----------------ind
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript src='js/zReport.js'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<script language=javascript src='js/sdm_3rekapabsenho.js'></script>
<?
include('master_mainMenu.php');		


$frm[0]='';
$frm[1]='';

OPEN_BOX('','<span class=judul>'.getMenu('sdm_3rekapabsenho').'<br><br></span>');
	
$optper=$optpt=$optkom=$optkomv="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

$optnmkom=  makeOption($dbname, 'sdm_ho_component', 'id,name');

$str="SELECT * FROM ".$dbname.".organisasi where tipe='PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$scek="select distinct * from ".$dbname.".sdm_5periodegaji where kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$bar['kodeorganisasi']."' and tipe='HOLDING')";
	$rcek=fetchData($scek);
	if(count($rcek)>0){
		$optpt.="<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";	
	}
}

$str="SELECT distinct(periode) as periode FROM ".$dbname.".sdm_5periodegaji where sudahproses=0 order by periode desc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$optper.="<option value='".$bar['periode']."'>".$bar['periode']."</option>";
}


$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRGJPTHO'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
$arrbpjs=explode(',',$bar['nilai']);
foreach($arrbpjs as $key){
	// $arrkomv[$key]=$key;
	$optkomv.="<option value='".$key."'>".$optnmkom[$key]."</option>";
}	

$frm[0].="<fieldset style=width:300px;float:left><legend><b>Form</b></legend>
<table>
    <tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=per style=\"width:130px;\" >".$optper."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['pt']."</td>
		<td>:</td>
		<td><select id=pt style=\"width:130px;\" >".$optpt."</select></td>
	</tr>";
		// <button onclick=zPreview('keu_slave_5hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		
$frm[0].="<tr>
		<td colspan=3 align=right>
		<button onclick=preview() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[0].="<fieldset style=width:860px><legend><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
			<li>Menu ini digunakan untuk melakukan rekap absensi</li>
			<li>Hanya berlaku untuk penggajian yang dilakukan di Head Office</li>
</fieldset>";


$frm[0].="
<fieldset style=width:1175px><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer' style='overflow:auto;height:350px;max-width:1175px'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 



$frm[1].="<fieldset style=width:300px;float:left><legend><b>Form</b></legend>
<table>
    <tr>
		<td>".$_SESSION['lang']['periode']."</td>
		<td>:</td>
		<td><select id=perv style=\"width:130px;\" >".$optper."</select></td>
	</tr>
	<tr>
		<td>".$_SESSION['lang']['pt']."</td>
		<td>:</td>
		<td><select id=ptv style=\"width:130px;\" >".$optpt."</select></td>
	</tr>";
		// <button onclick=zPreview('keu_slave_5hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		
$frm[1].="<tr>
		<td colspan=3 align=right>
		<button onclick=previewv() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=batalv() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[1].="<fieldset style=width:860px><legend><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
			<li>Menu ini digunakan untuk melakukan rekap absensi bulanan</li>
			<li>Jalankan dahulu rekap absensi harian</li>
			<li>Hanya berlaku untuk penggajian yang dilakukan di Head Office</li>
			<li>Potongan : Total potongan dalam 1 bulan (dari proses rekap harian)</li>
			<li>Cuti Awal : Sisa cuti <b>sebelum</b> proses rekap bulanan (sisa saldo awal)</li>
			<li>Pengali : Digunakan untuk perkalian gaji seperti gaji pokok, dengan rumus (jumlah hari 1 bulan - (potongan-cuti))/ jumlah hari 1 bulan</li>
			<li>Sisa cuti  : Sisa setelah dikurangi potongan</li>
</fieldset>";


$frm[1].="
<fieldset style=width:1175px><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainerv' style='overflow:auto;height:350px;max-width:1175px'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 

$hfrm[0]='Harian';
$hfrm[1]='Bulanan';
drawTab('FRM',$hfrm,$frm,200,1150);	



CLOSE_BOX();
echo close_body();




?>