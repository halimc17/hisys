<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$afd=checkPostGet('afd','');
$prd=checkPostGet('prd','');
$kontanan=checkPostGet('kontanan','');
$tglmulai=tanggalsystemn(checkPostGet('tglmulai',''));

$tahap        =checkPostGet('tahap','');

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

$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nikkar=makeOption($dbname,'datakaryawan','karyawanid,nik');

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


if($prdgaji=='1' || @$prdakt=='1'){
	exit ("Warning : Periode Gaji atau Periode Akutansi sudah ditutup.");
}

if($kontanan=='KONTAN'){
	$str='';
	$str="select * from ".$dbname.".kebun_aktifitas a left join ".$dbname.".kebun_prestasi b on a.notransaksi=b.notransaksi where a.tanggal like '".$prd."%' and b.keterangan='KONTAN' and tanggal = '".$tglmulai."' and nikmandor in (select karyawanid from ".$dbname.".datakaryawan where lokasitugas='".$unit."' and subbagian='".$afd."')";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$dtmandor[$bar['nikmandor']]=$bar['nikmandor'];
		$jabatan[$bar['nikmandor']]='MANDOR';
		$tanggal[$bar['tanggal']]=$bar['tanggal'];
		$listkar[$bar['nikmandor']][$bar['tanggal']]=$bar['tanggal'];
		@$kg[$bar['nikmandor']][$bar['tanggal']]+=$bar['hasilkerjakg'];
		$tt[$bar['nikmandor']][$bar['tanggal']]=$bar['tahuntanam'];
		@$basis[$bar['nikmandor']][$bar['tanggal']]+=$bar['norma'];
		@$rplb1[$bar['nikmandor']][$bar['tanggal']]+=$bar['upahpremilebihbasis'];
		@$rplb2[$bar['nikmandor']][$bar['tanggal']]=0;
		@$rpbrd[$bar['nikmandor']][$bar['tanggal']]=0;
		@$denda[$bar['nikmandor']][$bar['tanggal']]+=$bar['rupiahpenalty'];
	}		
	
	if(empty($dtmandor)){
		exit("Warning : Data Kosong.");
	}

}else{
	$str='';$w='';
	$w=" and tanggalpanen between '".$tgl1."' and '".$tgl2."'";
	$str="select * from ".$dbname.".kebun_3premipemanen where
			periode='".$prd."' and mandor in (select karyawanid from ".$dbname.".datakaryawan 
			where lokasitugas='".$unit."' and subbagian='".$afd."') ".$w." order by tanggalpanen";
			// exit("Error:$str");
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$dtmandor[$bar['mandor']]=$bar['mandor'];
		$jabatan[$bar['mandor']]='MANDOR';
		$tanggal[$bar['tanggalpanen']]=$bar['tanggalpanen'];
		$listkar[$bar['mandor']][$bar['tanggalpanen']]=$bar['tanggalpanen'];
		@$kg[$bar['mandor']][$bar['tanggalpanen']]+=$bar['kgwb'];
		$tt[$bar['mandor']][$bar['tanggalpanen']]=$bar['tahuntanam'];
		@$basis[$bar['mandor']][$bar['tanggalpanen']]+=$bar['basiskg'];
		@$rplb1[$bar['mandor']][$bar['tanggalpanen']]+=$bar['rplb1'];
		@$rplb2[$bar['mandor']][$bar['tanggalpanen']]+=$bar['rplb2'];
		@$rpbrd[$bar['mandor']][$bar['tanggalpanen']]+=$bar['rpbrd'];
		@$denda[$bar['mandor']][$bar['tanggalpanen']]+=$bar['denda'];
	}
	if(empty($dtmandor)){
		exit("Warning : Silahkan lakukan proses premi pemanen terlebih dahulu.");
	}
}

#ambil setup premi mandor
$str="select * from ".$dbname.".kebun_5premimandor where kodeorg='".$unit."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$nilai[$bar['jenis']]=$bar['nilai'];
	$hadirhk[$bar['jenis']]=$bar['kehadiranhrkerja'];
	$hadirhb[$bar['jenis']]=$bar['kehadrianhmhb'];
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
        $stream.="<tr class=rowcontent id=row".$no.">";
		$stream.="<td colspan=10 align=left bgcolor=#CCCCCC align=center>".$_SESSION['lang']['mandor']." : <b>".$nmkar[$mandor]."</b></td>"; 
		$stream.="<td hidden id=mandor".$no.">".$mandor."</td>";
		$stream.="<td hidden id=jabatan".$no.">MANDORPANEN</td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowheader>";
	$stream.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['tanggal']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['hari']."</td>";
	$stream.="<td align=center width=50px>".$_SESSION['lang']['tahuntanam']."</td>";
	$stream.="<td align=center>".$_SESSION['lang']['kg']."</td>";
	$stream.="<td align=center width=50px>Rp/Kg</td>";
	$stream.="<td align=center width=70px>".$_SESSION['lang']['rp']."</td>";
	$stream.="<td align=center width=70px>Kehadiran Hari Kerja</td>";
	$stream.="<td align=center width=70px>Kehadiran HM/Hb</td>";
	$stream.="<td align=center width=70px>".$_SESSION['lang']['total']."</td>";
	$stream.="</tr>";
	$stream.="</thead>";
	$nokar=0;
	$color='';
	foreach($tanggal as $tgl){
		if(@$listkar[$mandor][$tgl]!=''){
			$stream.="<tr class=rowcontent>";
			$nokar+=1;
			
			$day = date('D', strtotime($tgl));
			$strorg="select * from ".$dbname.".sdm_5harilibur where tanggal='".$tgl."' and (kebun='GLOBAL' or kebun='".$unit."')";
			$roworg=fetchData($strorg);
			if(@$roworg[0]['keterangan']=='libur'){
				$libur=true; $hari='HM/HB';
			} else if ($day=='Sun'){
				$libur=true; $hari='HM';
			} else {
				$libur=false; $hari='Kerja';
			}
			$stream.="<td align=center>".$nokar."</td>";
			$stream.="<td align=center>".$tgl."</td>";
			$stream.="<td align=center>".$hari."</td>";
			$stream.="<td align=center><font ".$color.">".$tt[$mandor][$tgl]."</font></td>";
			$stream.="<td align=right>".@number_format($kg[$mandor][$tgl])."</td>";
			$stream.="<td align=right>".@number_format($nilai['mandor'])."</td>";
			$stream.="<td align=right>".@number_format($kg[$mandor][$tgl]*$nilai['mandor'])."</td>";
			$premihb=$premihk=0;
			if($libur==true){
				$premihb=$hadirhb['mandor'];
			}else{
				$premihk=$hadirhk['mandor'];
			}
			$stream.="<td align=right>".@number_format($premihk)."</td>";
			$stream.="<td align=right>".@number_format($premihb)."</td>";
			$premitot=($kg[$mandor][$tgl]*$nilai['mandor'])+$premihk+$premihb;
			
			$stream.="<td align=right>".@number_format($premitot)."</td>";
			$stream.="</tr>";
			@$tjjg[$mandor]+=$kg[$mandor][$tgl];
			@$tluas[$mandor]+=$kg[$mandor][$tgl]*$nilai['mandor'];
			@$trplb1[$mandor]+=$premihk;
			@$trplb2[$mandor]+=$premihb;
			@$tpremitot[$mandor]+=$premitot;
			
		}
	}
	$stream.="<tr class=rowcontent>";
		$stream.="<td></td><td colspan=3>Total Premi</td>";
		$stream.="<td align=right>".@number_format($tjjg[$mandor])."</td>";
		$stream.="<td align=right></td>";
		$stream.="<td align=right>".@number_format($tluas[$mandor])."</td>";
		$stream.="<td align=right>".@number_format($trplb1[$mandor])."</td>";
		$stream.="<td align=right>".@number_format($trplb2[$mandor])."</td>";
		$stream.="<td align=right id=premiawal".$no.">".@number_format($tpremitot[$mandor])."</td>";
	$stream.="</tr>";
	$stream.="<tr class=rowcontent hidden>";
		$stream.="<td></td><td colspan=8>Pembagi (Jumlah Karyawan)</td>";
		$stream.="<td align=right id=pembagi".$no.">0</td>";
	$stream.="</tr>";
	
	$stream.="<tr class=rowcontent hidden>";
		$stream.="<td></td><td colspan=8>Premi Mandor (Bruto) <b><i>[1.5 x Rata - Rata premi pemanen yg diawasi]</i></b></td>";
		$stream.="<td align=right id=premi".$no.">".@number_format($tpremitot[$mandor])."</td>";
	$stream.="</tr>";
	$stream.="<tr class=rowcontent>";
		$stream.="<td></td><td colspan=8>Denda / Potongan</td>";
		$stream.="<td align=right><input type=text value=0 onkeyup=\"z.numberFormat('denda".$no."',0);gettotal(".$no.",'premi".$no."','denda".$no."','premitotal".$no."');\" id=denda".$no."  size=10 onkeypress=\"return angka_doang(event);\" class=myinputtextnumber style=\"width:80px;\"></td>";
	$stream.="</tr>";
	$stream.="<tr class=rowcontent>";
		$stream.="<td></td><td colspan=8>Total (Premi Bersih)</td>";
		$stream.="<td align=right id=premitotal".$no.">".@number_format($tpremitot[$mandor])."</td>";
	$stream.="</tr>";
	$stream.="</tbody></table><br>";
}

if ($proses != 'excel') {
    $stream.="<button class=mybutton onclick=saveAll(".$no.");>".$_SESSION['lang']['proses']."</button>";
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