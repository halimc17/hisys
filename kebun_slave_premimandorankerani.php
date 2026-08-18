<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$unit=checkPostGet('unitkerani','');
$afd=checkPostGet('afdkerani','');
$prd=checkPostGet('prdkerani','');
$kontanan=checkPostGet('kontanankerani','');
$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik');

$tglmulai=tanggalsystemn(checkPostGet('tglmulaikerani',''));

$tahap        =checkPostGet('tahapkerani','');

if($tahap=='1'){
	$tgl1 = $prd."-01";
	$tgl2 = $prd."-15";
}else{
	$tgl1 = $prd."-16";
	$tgl2 = tglakhir($tgl1);
}

if($kontanan=='KONTAN' and ($tglmulai=='--')){
	exit("Warning : Tanggal wajib diisi.");
}
if($kontanan=='KONTAN' and (substr($tglmulai,0,7)!=$prd)){
	exit("Warning : Tanggal tidak sesuai dengan periode.");
}

//Cek Periode gaji
$str="select max(sudahproses) as prd from ".$dbname.".sdm_5periodegaji where kodeorg='".$unit."' and periode='".$prd."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$prdgaji=$bar['prd'];
}
//Cek Periode akutansi
$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unit."' and periode='".$prd."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$prdakt=$bar['tutupbuku'];
}

if($afd=='' || $unit=='' || $prd==''){
	exit("Warning : Periode, Unit Kerja dan Afdeling wajib di isi.");
}


if($prdgaji=='1' || $prdakt=='1'){
	exit ("Warning : Periode Gaji atau Periode Akutansi sudah ditutup.");
}

$where='';
if($kontanan=='KONTAN'){
	$where=" and a.tanggalkontanan = '".$tglmulai."'";
	$whtgl=$tglmulai;
}else{
	$whtgl=$prd;
}
#ambil mandor1
$w="";
$w=" and a.tahap ='".$tahap."'";
$str="select a.*,b.nikasisten as kerani from ".$dbname.".kebun_premikemandoran a left join ".$dbname.".kebun_aktifitas b on a.karyawanid=b.nikmandor where b.tipetransaksi='PNN' and b.tanggal like '".$whtgl."%' and a.jabatan='MANDORPANEN' and a.periode='".$prd."' and a.kodeorg='".$unit."' and a.kontanan='".$kontanan."' ".$where." ".$w." and b.nikasisten in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and subbagian='".$afd."') group by karyawanid";		
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$dtmandor[$bar['kerani']]=$bar['kerani'];
	$karyawanid[$bar['karyawanid']]=$bar['karyawanid'];
	$listkar[$bar['kerani']][$bar['karyawanid']]=$bar['karyawanid'];
	@$premisumber[$bar['kerani']][$bar['karyawanid']]+=$bar['premisumber'];
	@$premihitung[$bar['kerani']][$bar['karyawanid']]+=$bar['premikomputer'];
	@$denda[$bar['kerani']][$bar['karyawanid']]+=$bar['denda'];
	@$premidapat[$bar['kerani']][$bar['karyawanid']]+=$bar['premiinput'];
	
}
if(empty($dtmandor)){
	exit("Warning : Data Kosong");
}


$stream='';
if(!empty($dtmandor)){
foreach($dtmandor as $mandor){
		
	if ($proses == 'excel') {
		$stream.="<table class=sortable cellspacing=1 border=1>";
	} else 	{
		$stream.="<table class=sortable cellspacing=1 style=min-width:700px>";
	}

	$stream.="<thead>";
	@$no+=1;
	$stream.="<tr class=rowcontent id=rowkerani".$no.">";
	$stream.="<td colspan=7 align=left bgcolor=#CCCCCC align=center>".$_SESSION['lang']['kerani']." : <b>".$nmkar[$mandor]."</b></td>"; 
	$stream.="<td hidden id=mandorkerani".$no.">".$mandor."</td>";
	$stream.="<td hidden id=jabatankerani".$no.">KERANIPANEN</td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowheader>";
	$stream.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['nik2']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['mandor']."</td>";
	$stream.="<td align=center width=100px>Premi<br>Mandor Panen<br>[Kotor]</td>";
	$stream.="<td align=center width=70px>Denda Premi<br>Mandor Panen</td>";
	$stream.="<td align=center width=70px>Premi<br>Mandor Panen<br>[Bersih]</td>";
	$stream.="</tr>";
	$stream.="</thead>";
	$nokar=0;
	$color='';
	foreach($karyawanid as $karid){
		if($listkar[$mandor][$karid]!=''){
		$stream.="<tr class=rowcontent>";
			$nokar+=1;
				$stream.="<td align=center>".$nokar."</td>";
				$stream.="<td align=center>".$nikkar[$karid]."</td>";
				$stream.="<td>".$nmkar[$karid]."</td>";
				$stream.="<td align=right>".@number_format($premihitung[$mandor][$karid])."</td>";
				$stream.="<td align=right>".@number_format($denda[$mandor][$karid])."</td>";
				$stream.="<td align=right>".@number_format($premidapat[$mandor][$karid])."</td>";
			$stream.="</tr>";
			@$tpremihitung[$mandor]+=$premihitung[$mandor][$karid];
			@$tdenda[$mandor]+=$denda[$mandor][$karid];
			@$tpremidapat[$mandor]+=$premidapat[$mandor][$karid];
		}
	}
	$stream.="<tr class=rowcontent>";
	$stream.="<td></td><td colspan=2>Total Premi Mandor</td>";
	$stream.="<td align=right>".@number_format($tpremihitung[$mandor])."</td>";
	$stream.="<td align=right>".@number_format($tdenda[$mandor])."</td>";
	$stream.="<td align=right id=premiawalkerani".$no.">".@number_format($tpremidapat[$mandor])."</td>";
	$stream.="</tr>";
	$stream.="<tr class=rowcontent>";
	$stream.="<td></td><td colspan=4>Pembagi (Jumlah Mandor Panen)</td>";
	$stream.="<td align=right id=pembagikerani".$no.">".@number_format($nokar)."</td>";
	$stream.="</tr>";
	#ambil setup premi mandor
	$str="select * from ".$dbname.".kebun_5premimandor where kodeorg='".$unit."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$nilai[$bar['jenis']]=$bar['nilai'];
		$hadirhk[$bar['jenis']]=$bar['kehadiranhrkerja'];
		$hadirhb[$bar['jenis']]=$bar['kehadrianhmhb'];
	}
	$stream.="<tr class=rowcontent>";
	$stream.="<td></td><td colspan=4>Pengali</td>";
	$stream.="<td align=right>".$nilai['kerani']."</td>";
	$stream.="</tr>";
	
	$tpremidapatbagi[$mandor]=$tpremidapat[$mandor]/$nokar*$nilai['kerani'];
	
	$stream.="<tr class=rowcontent>";
	$stream.="<td></td><td colspan=4>Premi Kotor</td>";
	$stream.="<td align=right id=premikerani".$no.">".@number_format($tpremidapatbagi[$mandor])."</td>";
	$stream.="</tr>";
	$stream.="<tr class=rowcontent>";
	$stream.="<td></td><td colspan=4>Denda / Potongan</td>";
	$stream.="<td align=right><input type=text value=0 onkeyup=\"z.numberFormat('dendakerani".$no."',0);gettotal(".$no.",'premikerani".$no."','dendakerani".$no."','premitotalkerani".$no."');\" id=dendakerani".$no."  size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>";
	$stream.="</tr>";
	$stream.="<tr class=rowcontent>";
	$stream.="<td></td><td colspan=4>Total (Premi Bersih)</td>";
	$stream.="<td align=right id=premitotalkerani".$no.">".@number_format($tpremidapatbagi[$mandor])."</td>";
	$stream.="</tr>";
	$stream.="</tbody></table><br>";
}

	if ($proses != 'excel') {
		$stream.="<button class=mybutton onclick=saveAllKerani(".$no.");>".$_SESSION['lang']['proses']."</button>";
	}
}

switch($proses){
    case'preview':
         echo $stream;
	break;
    
    ######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="daftar_premi_mandor";
		if(strlen($stream)>0){
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						@unlink('tempExcel/'.$file);
					}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream)){
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}else{
				echo "<script language=javascript1.2>
				window.location='tempExcel/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}           
		break;
	default:
}
?>