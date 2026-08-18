<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unitkeranitrk','');
$afd=checkPostGet('afdkeranitrk','');
$prd=checkPostGet('prdkeranitrk','');
$kontanan=checkPostGet('kontanantrk','');
$tglmulai=tanggalsystemn(checkPostGet('tglmulaitrk',''));

if($kontanan=='KONTAN' and ($tglmulai=='--')){
	exit("Warning : Tanggal wajib diisi !!!");
}
if($kontanan=='KONTAN' and (substr($tglmulai,0,7)!=$prd)){
	exit("Warning : Tanggal tidak sesuai dengan periode !!!");
}

$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik');
$divisi=makeOption($dbname,'datakaryawan','karyawanid,subbagian');

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

if($unit=='' || $prd==''){
	exit("Warning : Periode, Unit Kerja dan Afdeling wajib di isi !");
}


if($prdgaji=='1' || $prdakt=='1'){
	exit ("Warning : Periode Gaji atau Periode Akutansi sudah ditutup !");
}

$str="select * from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where a.tanggalkeluar ='0000-00-00' and a.lokasitugas='".$unit."' and b.namajabatan like '%mandor traksi%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$dtmandor[$bar['karyawanid']]=$bar['karyawanid'];
	$jabatan[$bar['karyawanid']]='KERANITRAKSI';
}
$where='';
if($kontanan=='KONTAN'){
	$where=" and a.tanggalkontanan='".$tglmulai."'";
}

$str="select a.*, b.subbagian from ".$dbname.".kebun_premikemandoran a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where a.periode='".$prd."' and a.kodeorg='".$unit."' and a.jabatan ='KERANIPANEN' and a.kontanan='".$kontanan."' ".$where."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	#$dtmandor[$bar['kerani']]=$bar['kerani'];
	#$jabatan[$bar['kerani']]='KERANIPANEN';
	$karyawanid[$bar['karyawanid']]=$bar['karyawanid'];
	$subbagian[$bar['subbagian']]=$bar['subbagian'];
	$listkar[$bar['subbagian']][$bar['karyawanid']]=$bar['karyawanid'];
	@$premikp[$bar['subbagian']][$bar['karyawanid']]+=$bar['premiinput'];
}

if(empty($dtmandor) or empty($karyawanid)){
	exit("warning : Data Kosong silahkan lakukan proses premi kerani panen terlebih dahulu, atau silahkan cek apakah ada jabatan Mandor Traksi di data karyawan");
}

$stream='';
if(!empty($dtmandor)){
foreach($dtmandor as $mandor){
		
	if ($proses == 'excel') {
		$stream.="<table class=sortable cellspacing=1 border=1>";
	} else 	{
		$stream.="<table class=sortable cellspacing=1 width=900px>";
	}

	$stream.="<thead>";
		@$no+=1;
        $stream.="<tr class=rowcontent id=row".$no.">";
		$stream.="<td colspan=5 align=left bgcolor=#CCCCCC align=center>Mandor Traksi : <b>".$nmkar[$mandor]."</b></td>"; 
		$stream.="<td hidden id=mandor".$no.">".$mandor."</td>";
		$stream.="<td hidden id=jabatan".$no.">MANDORTRAKSI</td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowheader>";
	$stream.="<td align=center  width=30px>".$_SESSION['lang']['nourut']."</td>";
	$stream.="<td align=center width=100px>".$_SESSION['lang']['divisi']."</td>";
	$stream.="<td align=center  width=75px>".$_SESSION['lang']['nik2']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['namakaryawan']."</td>";
	$stream.="<td align=center width=100px>".$_SESSION['lang']['premi']." Kerani Panen</td>";
	$stream.="</tr>";
	$stream.="</thead>";
	$nokar=0;
	$color='';
	foreach($karyawanid as $karid){
		foreach($subbagian as $subb){
			if(@$listkar[$subb][$karid]!=''){
			$stream.="<tr class=rowcontent>";
				$nokar+=1;
					$stream.="<td align=center>".$nokar."</td>";
					$stream.="<td align=center>".$divisi[$karid]."</td>";
					$stream.="<td align=center>".$nikkar[$karid]."</td>";
					$stream.="<td>".$nmkar[$karid]."</td>";
					$stream.="<td align=right>".@number_format($premikp[$subb][$karid])."</td>";
				$stream.="</tr>";
				@$tpremikp[$mandor]+=$premikp[$subb][$karid];
			}
		}
	}
	$stream.="<tr class=rowcontent>";
	$stream.="<td></td><td colspan=3>Total Premi Karyawan</td>";
	$stream.="<td align=right id=premiawaltrk".$no.">".@number_format($tpremikp[$mandor])."</td>";
	$stream.="</tr>";
	$stream.="<tr class=rowcontent>";
	$stream.="<td></td><td colspan=3>Pembagi (Jumlah Karyawan)</td>";
	$stream.="<td align=right id=pembagitrk".$no.">".@number_format($nokar)."</td>";
	$stream.="</tr>";

	$tpremitotbagi[$mandor]=($tpremikp[$mandor]/$nokar)*1.25;
	$stream.="<tr class=rowcontent>";
	$stream.="<td></td><td colspan=3>Premi Mandor Traksi (Bruto) <b><i>[1.25 x Rata - Rata premi kerani panen]</i></b></td>";
	$stream.="<td align=right id=premitrk".$no.">".@number_format($tpremitotbagi[$mandor])."</td>";
	$stream.="</tr>";
	$stream.="<tr class=rowcontent>";
	$stream.="<td></td><td colspan=3>Denda / Potongan</td>";
	$stream.="<td align=right><input type=text value=0 onkeyup=\"z.numberFormat('dendatrk".$no."',0);gettotaltrk(".$no.");\" id=dendatrk".$no."  size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>";
	$stream.="</tr>";
	$stream.="<tr class=rowcontent>";
	$stream.="<td></td><td colspan=3>Total (Premi Bersih)</td>";
	$stream.="<td align=right id=premitotaltrk".$no.">".@number_format($tpremitotbagi[$mandor])."</td>";
	$stream.="</tr>";
	$stream.="</tbody></table><br>";
}

	if ($proses != 'excel') {
		$stream.="<button class=mybutton onclick=saveAllKeranitrk(".$no.");>".$_SESSION['lang']['proses']."</button>";
	}
}
		
switch($proses)
{
    
    case'preview':
         echo $stream;
	break;
    
    ######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="daftar_premi_mandor";
		if(strlen($stream)>0)
		{
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					@unlink('tempExcel/'.$file);
				}
				}	
				closedir($handle);
			}
			$handle=fopen("tempExcel/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream))
			{
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}
			else
			{
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