<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include('lib/zLib.php');
?>
<script language=javascript1.2 src='js/setup_validasianggaran.js?v=1.4'></script>
<?php

OPEN_BOX('','<span class=judul>'.getMenu('setup_validasianggaran').'</span><br>');

$optCrOrg = $optCrkar = $optCrjnspstj = $optCrapp ="<option value=''>".$_SESSION['lang']['all']."</option>";
$optjab=$opttipe=$optdep=$optkar=$optOrg=$optjnspstj=$optCrkarlama=$optCrkarbaru="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

##KODE ORGANISASI
$str=$owlPDO->query("select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)=4 order by induk, kodeorganisasi");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk',"kodeorganisasi='".$bar->kodeorganisasi."'");
	$d=$induk[$bar->kodeorganisasi];
	if($d!=$n){			
		$optOrg.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
	}
	$optOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	$optCrOrg.="<option value='".$bar->kodeorganisasi."'>".$bar->kodeorganisasi." - ".$bar->namaorganisasi."</option>";
	$n=$d;
	if($d!=$n){			
		$optOrg.="</optgroup>";
	}
}

##DATA KARYAWAN
$str=$owlPDO->query("select karyawanid,namakaryawan, lokasitugas from ".$dbname.".datakaryawan 
      where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan in ('0','1','7','8','9') order by namakaryawan");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    //$optkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."- ".$bar->lokasitugas."</option>";
    $optCrkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."- ".$bar->lokasitugas."</option>";
    $optCrkarbaru.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."- ".$bar->lokasitugas."</option>";
}

##DATA KARYAWAN replace
$str=$owlPDO->query("select karyawanid,namakaryawan, lokasitugas from ".$dbname.".datakaryawan 
      where tipekaryawan in ('0','1','7','8','9') order by namakaryawan");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    //$optkar.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."- ".$bar->lokasitugas."</option>";
    $optCrkarlama.="<option value='".$bar->karyawanid."'>".$bar->namakaryawan."- ".$bar->lokasitugas."</option>";
}

##MODUL ANGGARAN
$str="select * from ".$dbname.".setup_modulanggaran where status='1' order by modul asc";
$res=fetchdata($str);
foreach($res as $val){
    $optjnspstj.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['modul']."</option>";
    $optCrjnspstj.="<option value='".$val['kode']."'>".$val['kode']." - ".$val['modul']."</option>";
}

##LEVEL APPROVAL
$optapp="";
for($i=1;$i<=10;$i++){
	$optapp.="<option value='".$i."'>Level ".$i."</option>";
	$optCrapp.="<option value='".$i."'>Level ".$i."</option>";
}

##DEPARTEMEN
$str=$owlPDO->query("select kode,nama from ".$dbname.".sdm_5departemen order by nama asc");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $optdep.="<option value='".$bar->kode."'>".$bar->kode." - ".$bar->nama."</option>";
}

##TIPE KARYAWAN
$str=$owlPDO->query("select id,tipe from ".$dbname.".sdm_5tipekaryawan order by id asc");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $opttipe.="<option value='".$bar->id."'>".$bar->tipe."</option>";
}

##JABATAN
$str=$owlPDO->query("select kodejabatan,namajabatan from ".$dbname.".sdm_5jabatan order by kodejabatan asc");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch()){
    $optjab.="<option value='".$bar->kodejabatan."'>".$bar->kodejabatan." - ".$bar->namajabatan."</option>";
}

$optdigit="<option value='3'>3 Digit / Per Kelompok Barang</option>";
//$optdigit.="<option value='4'>4 Digit</option>";
$optdigit.="<option value='5'>5 Digit / Per Sub Kelompok Barang</option>";
// $optdigit.="<option value='6'>6 Digit</option>";
// $optdigit.="<option value='7'>7 Digit</option>";
// $optdigit.="<option value='8'>8 Digit</option>";
$optdigit.="<option value='9'>9 Digit / Per Kode Barang</option>";
//$optdigit.="<option value='10'>10 Digit</option>";

$arrjns=array('3'=>'3 Digit / Per Kelompok Barang','5'=>'5 Digit / Per Sub Kelompok Barang','9'=>'9 Digit / Per Kode Barang');

##FORM
echo"<fieldset>
	<legend><b>".$_SESSION['lang']['form']."</b></legend>
	<table border=0 style='display: inline-block;vertical-align:top'>
		<tr>
			<td>".$_SESSION['lang']['kodeorg']."</td>
			<td>:</td>
			<td>
				<select style=width:200px id=kodeorg>".$optOrg."</select>
				<img id='kodeorg' onclick=z.elSearch('kodeorg',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['modul']."</td> 
			<td>:</td>
			<td>
				<select style=width:200px id=jenispersetujuan>".$optjnspstj."</select>
				<img id='jenispersetujuan' onclick=z.elSearch('jenispersetujuan',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
			</td>
		</tr>
		<tr>
			<td>Digit Kode Barang</td> 
			<td>:</td>
			<td>
				<select style=width:200px id=digit>".$optdigit."</select>
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['toleransi']." (%)</td> 
			<td>:</td>
			<td>
				<input type=text class=myinputtextnumber id=toleransi onkeypress=\"return angka_doang(event);\" style=\"width:80px;\" placeholder='0' />
			</td>
		</tr>
		<tr>
			<td>".$_SESSION['lang']['status']." Aktif / Non-Aktif</td> 
			<td>:</td>
			<td>
				<input type=checkbox id=status checked>
			</td>
		</tr>
		<tr>
			<td colspan=2></td>
			<td>
				<input type=hidden id=method value='insert'>
				<input type=hidden id=myid value=''>
				<button class=mybutton onclick=simpan()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton onclick=batal()>".$_SESSION['lang']['cancel']."</button>
			</td>
		</tr>
	</table>
</fieldset>";
CLOSE_BOX ();

OPEN_BOX ();
echo"<fieldset style=''>
	<legend><b>".$_SESSION['lang']['list']."</b></legend>
	<table border=0 cellpadding=1 cellspacing=1 style='display: inline-block;vertical-align:top;padding-bottom:10px;'>
		<tr>
			<td>
				<fieldset style=float:left><legend><b>".$_SESSION['lang']['find']."</b></legend>
				<table>
					<tr>
						<td>".$_SESSION['lang']['kodeorg']."</td>
						<td>:</td>
						<td>
							<select style=width:200px id=crkodeorg onchange='loaddata()'>".$optCrOrg."</select>
						</td>
					
						<td>".$_SESSION['lang']['jenispersetujuan']."</td> 
						<td>:</td>
						<td>
							<select style=width:200px id=crjenispersetujuan onchange='loaddata()'>".$optCrjnspstj."</select>
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick='batalcari();loaddata()'>".$_SESSION['lang']['resetvariableoutput']."</button>
						</td>
					</tr>
				 </table>
				 </fieldset>
			</td> 
		</tr>
	</table>
	<div class='table-scroll' style=height:60vh>
	<table class=sortable cellspacing=1 cellpadding=5 border=0 style='margin-left:5px;'>
		<thead>
		<tr class=rowheader style='font-weight:bold'>
			<th style='text-align:center'>".$_SESSION['lang']['nomor']."</th>
			<th style='text-align:center'>".$_SESSION['lang']['kodeorg']."</th>
			<th style='text-align:center'>".$_SESSION['lang']['namaorganisasi']."</th>
			<th style='text-align:center'>".$_SESSION['lang']['modul']."</th>
			<th style='text-align:center'>Digit Kode Barang</th>
			<th style='text-align:center'>".$_SESSION['lang']['toleransi']." (%)</th>
			<th style='text-align:center'>".$_SESSION['lang']['status']."</th>
			<th style='text-align:center'>Action</th></tr>
		 </thead>
		 <tbody id=container>
			<script>loaddata()</script>
		 </tbody>
	</table>
	</div>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>