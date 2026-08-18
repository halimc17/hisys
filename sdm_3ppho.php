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
<script language=javascript src='js/sdm_3ppho.js'></script>
<?
include('master_mainMenu.php');		

$frm[0]='';
$frm[1]='';

OPEN_BOX('','<span class=judul>'.getMenu('sdm_3ppho').'<br><br></span>');
	
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

$str="select nilai from ".$dbname.".setup_parameterappl where kodeaplikasi='HR' and	kodeparameter='HRGJPPHO'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();	
$arrbpjs=explode(',',$bar['nilai']);
foreach($arrbpjs as $key){
	// $arrkom[$key]=$key;
	$optkom.="<option value='".$key."'>".$optnmkom[$key]."</option>";
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
?>


<?

$arr="##pt##tgl1##tgl2";	

echo"<fieldset style=width:300px;float:left><legend><b>Form</b></legend>
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
	</tr>
	<tr>
		<td>".$_SESSION['lang']['idkomponen']."</td>
		<td>:</td>
		<td><select id=kom style=\"width:130px;\" >".$optkom."</select></td>
	</tr>";
		// <button onclick=zPreview('keu_slave_5hargatbs','".$arr."','printContainer') class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		
echo"<tr>
		<td colspan=3 align=right>
		<button onclick=preview() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=batal() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";


echo"<fieldset style=width:800px><legend><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
			<li>Hitungan dilakukan perhari setelah melakukan proses rekap absen ho 	<b>(sdm->proses->penggajian ho->rekap absen ho)</b></li>
			<li>Daftarkan juga Rp/Hari <b>(sdm->setup->penggajian ho->gaji pokok ho)</b></li>
			<li>ID Komponen dapat didaftarkan di menu parameter aplikasi dengan kode HRGJPPHO <b>(setup->parametera aplikasi)</b></li>
			<li>Data perhari adalah <b>kehadiran</b> karyawan, sebagai pengali perhari</li>
			<li>Jam Absen : Jam yang akan dibayar</li>
			<li>Jam Bayar : Jam yang akan diperhitungkan</li>
			<li>Rp : Rupiah yang didapatkan dengan perhitungan Jam absen / Jam bayar * Rp perhari</li>
</fieldset>";



echo"
<fieldset style=width:1115px><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainer'  style='overflow:auto;height:300px;max-width:1115px'>
</div></fieldset>";// style='overflow:auto;height:350px;max-width:1220px'; 


/*
$arrv="##ptv##tglv1##tglv2";	

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
	</tr>
	<tr>
		<td>".$_SESSION['lang']['idkomponen']."</td>
		<td>:</td>
		<td><select id=komv style=\"width:130px;\" >".$optkomv."</select></td>
	</tr>";
	
$frm[1].="<tr>
		<td colspan=3 align=right>
		<button onclick=previewv() class=mybutton name=preview id=preview>".$_SESSION['lang']['preview']."</button>
		<button onclick=batalv() class=mybutton name=btnBatal id=btnBatal>".$_SESSION['lang']['cancel']."</button>
		</td>
	</tr>
</table>
</fieldset>";

$frm[1].="<fieldset style=width:800px><legend><img src=images/info.png align=left height=25px valign=asmiddle>[Info]</b></legend>
			
			<li>Hitungan dilakukan perhari setelah melakukan proses perhitungan absensi</li>
			<li>Data perhari adalah <b>ketidak-hadiran</b> karyawan, sebagai pengurang (persen potong)</li>
			<li>Totalan persentase potongan akan dikurangi dengan sisa cuti</li>
			<li>Jika masih ada sisa cuti (sisa cuti - persentase potongan) maka tidak ada pengurang</li>
			<li>Jika tidak ada cuti lagi, maka akan diproporsi dengan jumlah hari</li>
			<li>% (persentase) digunakan untuk pengali komponen gaji; Proporsi dihitung dari jika sisa cuti minus (-); jika masih ada sisa cuti maka 1%</li>
			
</fieldset>";



$frm[1].="
<fieldset style=width:1115px><legend><b>".$_SESSION['lang']['list']."</b></legend>
<div id='printContainerv' style='overflow:auto;height:300px;max-width:1115px'>
</div></fieldset>";








// $hfrm[0]='Komponen Tidak Tetap';
// $hfrm[1]='Komponen Tetap';

// drawTab('FRM',$hfrm,$frm,200,1150);	
*/
CLOSE_BOX();
echo close_body();




?>