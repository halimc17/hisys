<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>

<script language=javascript1.2 src='js/kebun_5premimandor.js?v=<?php echo time(); ?>'></script>
<?php

OPEN_BOX ('','<span class=judul>'.getMenu('Kebun_5premimandor').'</span><br>');

//query PT
if($_SESSION['empl']['tipelokasitugas']=='HOLDING' || $_SESSION['empl']['tipelokasitugas']=='KEBUN' || $_SESSION['empl']['tipelokasitugas']=='KANWIL') {
	$str = "select * from ".$dbname.".organisasi where tipe = 'KEBUN' order by kodeorganisasi";
} else {
	$str = "select * from ".$dbname.".organisasi where tipe = 'KEBUN' and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."' order by kodeorganisasi";
}

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optunit="<option value='' hidden>" . $_SESSION['lang']['pilihdata'] . "</option>";
foreach ($res as $bar) {
	$optunit .= "<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
}


//Fungsi getENum utk memilih data tipe enum di kolom database jenis
$arrTipe=getEnum($dbname,'kebun_premikemandoran','jabatan');
$optPilihjenis="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
foreach($arrTipe as $barjab){
	@$optPilihjenis.="<option value=".$barjab.">".$barjab."</option>";
}

//button Cari Unit
$namapt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$str = "SELECT distinct kodeorg from ".$dbname.".kebun_5premimandor order by kodeorg";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$optCariUnit="<option value=''>Seluruhnya</option>";
foreach ($res as $bar) {
	$optCariUnit .= "<option value=".$bar['kodeorg'].">".$bar['kodeorg']." - ".$namapt[$bar['kodeorg']]."</option>";
}

//button Cari Jenis
$arrTipe=getEnum($dbname,'kebun_premikemandoran','jabatan');
$optCariJenis = "<option value=''>Seluruhnya</option>";
foreach ($arrTipe as $k => $v) {
	$optCariJenis .= "<option value=".$k.">".ucfirst($v)."</option>";
}

			
// echo ($optPilihjenis);

// echo "<pre>";
// print_r($arrTipe);
// echo "</pre>";

echo"<fieldset>
	<legend><b>".$_SESSION['lang']['form']."</b></legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top;padding-bottom:10px;'>
		<tr>
			<td>
				<table>
					<tr>
						<td>".$_SESSION['lang']['unit']."</td> 
						<td>:</td>
						<td>
							<select style=width:203px id=kodeunit>".$optunit."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['jenis']."</td> 
						<td>:</td>
						<td>
							<select style=width:203px id=jenis>".$optPilihjenis."</select>
						</td>
					</tr>
					<tr>
						<td>Minimal Pembagi</td> 
						<td>:</td>
						<td>
							<input id=minimalpembagi onchange=validasi() class=myinputtextnumber onkeypress='return angka_doang(event)' style=width:200px>
						</td>
					</tr>					
					<tr>
						<td>Nilai Pengali</td> 
						<td>:</td>
						<td>
							<input id=nilaipengali class=myinputtextnumber onkeypress='return angka_doang(event)' style=width:200px>
						</td>
					</tr>			

					<tr>
						<td colspan=2></td>
						<td style=padding-top:10px>
						<input type=hidden class=myinputtext style=width:200px id=metode value='simpandatabkm'>
						<input type=hidden class=myinputtext style=width:200px id=kodepremi value=''>
							<button id=tombolsave class=mybutton onclick=simpandatapremi()>" . $_SESSION['lang']['save'] . "</button>
							<button id=tomboledit hidden class=mybutton onclick=ubahdatapremi()>" . $_SESSION['lang']['edit'] . "</button>
							<button id=tombolcancel class=mybutton onclick=canceldatapremi()>" . $_SESSION['lang']['cancel'] . "</button>
						</td>
					</tr>
				</table>
			</td> 
		</tr>
	</table>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo"<fieldset style=''>
	<legend><b>List Data</b></legend>
			<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top; margin-bottom: 10px;'>
				<tr>
					<td>
						<fieldset>
							<legend>".$_SESSION['lang']['find']."</legend>
							".$_SESSION['lang']['unit']." : <select id=find_unit onchange=loaddata(0) style=width:150px>" . $optCariUnit . "</select>&nbsp;
							".$_SESSION['lang']['jenis']." : <select id=find_jenis onchange=loaddata(0) style=width:150px>" . $optCariJenis . "</select>&nbsp;							

							<button class=mybutton onclick=loaddata(0)>".$_SESSION['lang']['find']."</button>
							<button class=mybutton onclick=batalcari()>".$_SESSION['lang']['cancel']."</button>
							
						</fieldset>
					</td> 
				</tr>
			</table><br>
	<table class=sortable cellspacing=1 cellpadding=3 border=0 style='margin-left:5px;min-width:635px;'>
		<thead>
			<tr class=rowheader style='font-weight:bold'>
				<td style='text-align:center'>".$_SESSION['lang']['nourut']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['unit']."</td>
				<td style='text-align:center'>".$_SESSION['lang']['jenis']."</td>
				<td style='text-align:center'>Minimal Pembagi</td>
				<td style='text-align:center'>Nilai Pengali</td>
				<td style='text-align:center'>Action</td>
			</tr>
		</thead>
		 <tbody id=container>
		 	<script>loaddata()</script>
		 </tbody>
	</table>
</fieldset>";
CLOSE_BOX();

echo close_body();
?>