<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth:true
		});
	});
</script>
<script language=javascript src='js/monitoring_transaksi.js?v=<?php echo time() ?>'></script>
<?

include('master_mainMenu.php');

$frm[0] = '';
$frm[1] = '';
$frm[2] = '';
$frm[3] = '';
$frm[4] = '';
$frm[5] = '';
$frm[6] = '';
$frm[7] = '';
$frm[8] = '';

OPEN_BOX('','<span class=judul>'.getMenu('monitoring_transaksi').'</span>');

//Get Perusahaan
// $strkar="select lokasitugas from ".$dbname.".datakaryawan where karyawanid='".$_SESSION['standard']['userid']."'";
$strkar="select kodeorg as lokasitugas from ".$dbname.".user where karyawanid='".$_SESSION['standard']['userid']."'";
$res=$owlPDO->query($strkar) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$lokasitugas=$bar['lokasitugas'];
}

$namaorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');


$lokasi=$_SESSION['empl']['lokasitugas'];
//exit('error'.substr($lokasi,2));

// if (substr($lokasitugas,2)=='HO' || substr($lokasi,2)=='HO' || substr($lokasitugas,2)=='RO' || substr($lokasi,2)=='RO') {

// 	//Get Perusahaan
// 	$str="select distinct (kodeorganisasi) as kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=3 order by namaorganisasi ASC";
// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 	$res->setFetchMode(PDO::FETCH_ASSOC);
// 	$optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
// 	while($bar=$res->fetch()){
// 		$optpt.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
// 	}

// }else{

// 	//Get Perusahaan
// 	$str="select * from ".$dbname.".organisasi a 
// 	left join ".$dbname.".user b on a.kodeorganisasi=b.kodeorg where b.karyawanid='".$_SESSION['standard']['userid']."' group by induk ";
// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// 	$res->setFetchMode(PDO::FETCH_ASSOC);

// 	while($bar=$res->fetch()){
// 		$optpt.="<option value=".$bar['induk'].">".$namaorg[$bar['induk']]."</option>";
// 	}
// }


## GET UNIT
$optpt="<option value=''>".$_SESSION['lang']['all']."</option>";
$unitx='';
$arrUnit = getOrgDetail(3);
foreach($arrUnit as $key=>$val){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$key."'");
	$d=$induk[$key];
	if($d!=$n){			
		$optpt.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	
	if($key==$_SESSION['empl']['lokasitugas']){
		$optpt.="<option value='".$key."' selected>".$key." - ".$val."</option>";	
		$unitx=$key;
	}else{
		$optpt.="<option value='".$key."'>".$key." - ".$val."</option>";			
	}
	$n=$d;
	if($d!=$n){			
		$optpt.="</optgroup>";
	}
}

//Get Periode
$arrperiode=array();
$str="select distinct(periode) as periode from ".$dbname.".setup_periodeakuntansi order by periode DESC";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$arrperiode[$bar['periode']]=$bar['periode'];
}

// GET PERIODE LAST LOGIN USER
$str="select distinct(left(lastupdate,7)) as periode from ".$dbname.".user order by lastupdate";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch())
{
	$arrperiode[$bar['periode']]=$bar['periode'];
}

krsort($arrperiode);

function puterperiode($periode){
	$qwe=explode("-",$periode);
	$hasil=$qwe[1]."-".$qwe[0];
	return $hasil;
}

foreach($arrperiode as $val){
	$optperiode.="<option value=".$val.">".puterperiode($val)."</option>";
}

//Get Tipe
$arrtipe = array('PNN'=>'Panen','TM'=>'TM','TBM'=>'TBM','BBT'=>'BBT');
foreach($arrtipe as $key=>$val)
{
	$opttipe.="<option value=".$key.">".$val."</option>";
}
//Get SPB
$arrspb = array('inp'=>'Input','pos'=>'Posting');
foreach($arrspb as $key=>$val)
{
	$optspb.="<option value=".$key.">".$val."</option>";
}

$frm[0].="<fieldset>
	<legend>Filter</legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select class='select2' style=\"width: 150px;\" id='perusahaan1'>".$optpt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='periode1' style=\"width: 150px;\">".$optperiode."</select>
			</td>
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=preview1()>".$_SESSION['lang']['preview']."</button>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id='container1'></div>
</fieldset>";

$frm[1].="<fieldset>
	<legend>Filter</legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='perusahaan2' style=\"width: 150px;\">".$optpt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='periode2' style=\"width: 150px;\">".$optperiode."</select>
			</td>
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=preview2()>".$_SESSION['lang']['preview']."</button>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id='container2'></div>
</fieldset>";
		
$frm[2].="<fieldset>
	<legend>Filter</legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='perusahaan3' style=\"width: 150px;\">".$optpt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='periode3' style=\"width: 150px;\">".$optperiode."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='tipe3' style=\"width: 150px;\">".$opttipe."</select>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=preview3()>".$_SESSION['lang']['preview']."</button>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id='container3'></div>
</fieldset>";

$frm[3].="<fieldset>
	<legend>Filter</legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='perusahaan4' style=\"width: 150px;\">".$optpt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='periode4' style=\"width: 150px;\">".$optperiode."</select>
			</td>
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=preview4()>".$_SESSION['lang']['preview']."</button>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id='container4'></div>
</fieldset>";

$frm[4].="<fieldset>
	<legend>Filter</legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='perusahaan5' style=\"width: 150px;\">".$optpt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='periode5' style=\"width: 150px;\">".$optperiode."</select>
			</td>
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=preview5()>".$_SESSION['lang']['preview']."</button>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id='container5'></div>
</fieldset>";

$frm[5].="<fieldset>
	<legend>Filter</legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='perusahaan6' style=\"width: 150px;\">".$optpt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='periode6' style=\"width: 150px;\">".$optperiode."</select>
			</td>
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=preview6()>".$_SESSION['lang']['preview']."</button>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id='container6'></div>
</fieldset>";

$frm[6].="<fieldset>
	<legend>Filter</legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='perusahaan7' style=\"width: 150px;\">".$optpt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='periode7' style=\"width: 150px;\">".$optperiode."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['tipe']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='tipe7' style=\"width: 150px;\">".$optspb."</select>
			</td>
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=preview7()>".$_SESSION['lang']['preview']."</button>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id='container7'></div>
</fieldset>";


$frm[7].="<fieldset>
	<legend>Filter</legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td>".$_SESSION['lang']['perusahaan']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='perusahaan8' style=\"width: 150px;\">".$optpt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='periode8' style=\"width: 150px;\">".$optperiode."</select>
			</td>
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=preview8()>".$_SESSION['lang']['preview']."</button>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div  id='container8'></div>
</fieldset>";

$frm[8].="<fieldset>
	<legend>Filter</legend>
	<table cellspacing='1' border='0'>
		<tr>
			<td>".$_SESSION['lang']['unit']."</td>
			<td>:</td>
			<td>
				<select class='select2' style=\"width: 150px;\" id='perusahaan9'>".$optpt."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['periode']."</td>
			<td>:</td>
			<td>
				<select class='select2' id='periode9' style=\"width: 150px;\">".$optperiode."</select>
			</td>
		</tr>
		
		<tr>
			<td colspan=2></td>
			<td>
				<button class=mybutton onclick=preview9()>".$_SESSION['lang']['preview']."</button>
			</td>
		</tr>
	</table>
</fieldset>

<fieldset>
	<legend>".$_SESSION['lang']['list']."</legend>
	<div id='container9'></div>
</fieldset>";

$hfrm[0] = "Keuangan";
$hfrm[1] = "Pengadaan";
$hfrm[2] = "Kebun";
$hfrm[3] = "Pabrik";
$hfrm[4] = "Traksi";
$hfrm[5] = "SDM";
$hfrm[6] = "SPB";
$hfrm[7] = "Rekap Panen";
$hfrm[8] = "Kasir";

drawTab('FRM', $hfrm, $frm, 100, '');

CLOSE_BOX();
 
echo close_body();
?>