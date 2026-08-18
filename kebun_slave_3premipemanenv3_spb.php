<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses   =checkPostGet('proses','');
$unit     =checkPostGet('unit','');
$afd      =checkPostGet('afd','');
$prd      =checkPostGet('prd','');
$tahap    =checkPostGet('tahap','');
$kgbrondol=checkPostGet('kgbrondol','');
$perpot   =checkPostGet('perpot','');
$recal    =checkPostGet('recal','');
$tgl1     =tanggalsystemn(checkPostGet('tgl1',''));
$tgl2     =tanggalsystemn(checkPostGet('tgl2',''));

$tglawal1 = explode("-",$tgl1);
$tglawal2 = explode("-",$tgl2);

$prdtgl1  = $tglawal1[0]."-".$tglawal1[1];
$prdtgl2  = $tglawal2[0]."-".$tglawal2[1];

if($prd!=$prdtgl1 or $prd!=$prdtgl2){
	exit("Warning : Periode dan tanggal tidak sesuai.");
}

if($tahap=='1'){
	$rangetgl1=rangeTanggal($prdtgl1."-01", $prdtgl1."-15");
}else{
	$rangetgl2=rangeTanggal($prdtgl1."-16", tglakhir($tgl1));
}

// if($tahap=='1' and (!in_array($tgl1,$rangetgl1) or !in_array($tgl2,$rangetgl1))){
// 	exit("Warning : Range tanggal harus di antara ".$prdtgl1."-01 s/d ".$prdtgl1."-15"."");
// }elseif($tahap=='2' and (!in_array($tgl1,$rangetgl2) or !in_array($tgl2,$rangetgl2))){
// 	exit("Warning : Range tanggal harus di antara ".$prdtgl1."-16 s/d ".tglakhir($tgl1)."");
// }
/* 
if($tahap=='1'){
	$tgl1 = $prd."-01";
	$tgl2 = $prd."-15";
}else{
	$tgl1 = $prd."-16";
	$tgl2 = tglakhir($tgl1);
} */

if($prd<'2021-12'){
	exit("Warning : Proses hanya bisa dilakukan untuk periode Desember 2021 keatas.");
}

$rangetgl    =rangeTanggal($tgl1, $tgl2);
$rangetglpks =rangeTanggal($tgl1, $tgl2);
$rangetglspb =rangeTanggal($tgl1, $tgl2);

@$nmkar      =makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
@$nikkar     =makeOption($dbname,'datakaryawan','karyawanid,nik');
$nmorg2       =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmorg       =makeOption($dbname,'organisasi','indukblok,namaindukblok');


#Ambil data basis per blok kecil
$datablokbasis=array();
$str="select kodeorg,buahkecil from ".$dbname.".setup_blok where kodeorg  like '".$afd."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$basisoo='';
	if($bar['buahkecil']=='0'){
		$basisoo='BESAR';
	}elseif($bar['buahkecil']=='1'){
		$basisoo='KECIL';
	}
	$datablokbasis[$bar['kodeorg']]=$basisoo;
}

#Cek Periode gaji
$str="select max(sudahproses) as prd from ".$dbname.".sdm_5periodegaji where kodeorg='".$unit."' and periode='".$prd."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$prdgaji=$bar['prd'];
}
#Cek Periode akutansi
$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$unit."' and periode='".$prd."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$prdakt=$bar['tutupbuku'];
}

if($afd=='%%' || $unit=='' || $prd=='' || $afd==''){
	exit("Warning : Tanggal, Unit Kerja dan Divisi wajib di isi.");
}
if(@$prdgaji=='1' || @$prdakt=='1'){

	#exit ("Warning : Periode Gaji atau Periode Akutansi sudah ditutup !");
}


#ambil kg wb
$databasisbloktidakada=0;
$w=""; $kgwb=$kgwblalu=$jjgwb=$tglpnn=$ttlkgkrm=$kgwbperblok=$nospbperblok=$tglspbperblok=$jlhblokpertk=$blokspbtanggal=array();
$w=" and tanggalpanen between '".$tgl1."' and '".$tgl2."'";
$str="select * from ".$dbname.".kebun_spbdt_detail where	1=1 ".$w." and blok like '".$afd."%' and blok like '".$unit."%' order by nospb asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	if($datablokbasis[$bar['blok']]=='' and $bar['kgwbnetto']>1){
		echo 'Data Basis Pada Blok '.$bar['blok'].' Belum Ada, Silahkan Setup di Setup Blok';
		$databasisbloktidakada=1;
	}else{
		if(($bar['kgwbnetto']-$bar['brondolan'])<0){
			@$kgwbnetto[$bar['nospb']][$bar['tanggalpanen']][$bar['indukblok']][$datablokbasis[$bar['blok']]]+=($bar['kgwbnetto']);
		}else{
			@$kgwbnetto[$bar['nospb']][$bar['tanggalpanen']][$bar['indukblok']][$datablokbasis[$bar['blok']]]+=($bar['kgwbnetto']-$bar['brondolan']);
		}
		@$ttwb[$bar['nospb']][$bar['tanggalpanen']][$bar['indukblok']][$datablokbasis[$bar['blok']]]=$bar['blok'];
		@$jjgkirim[$bar['nospb']][$bar['tanggalpanen']][$bar['indukblok']][$datablokbasis[$bar['blok']]]+=$bar['jjg'];

	}
	
	// /* if(substr($bar['tanggalpanen'],0,7)!=$prd){
	// 	// @$kgwblalu[$bar['tahuntanam']][$bar['tanggalpanen']]+=$bar['kgwbnetto'];
	//@$kgwblalu[$bar['tahuntanam']][$bar['tanggalpanen']]+=($bar['kgwbnetto']-$bar['brondolan']);
	// 	$tglpnn[$bar['tanggalpanen']]=$bar['tanggalpanen'];
	// }else{
	// 	// @$kgwb[$bar['tahuntanam']][$bar['tanggalpanen']]+=$bar['kgwbnetto'];
	// } */
	// // if($kgbrondol=='1'){
	// // 	#Kg Sebelum Potong Brondolan
	// // 	@$kgwb[$bar['tahuntanam']][$bar['tanggalpanen']][$bar['blok']][$bar['nospb']]+=($bar['kgwbnetto']);			
	// // 	@$kgwbperblok[$bar['blok']][$bar['tanggalpanen']]+=($bar['kgwbnetto']);
	// // 	@$ttlkgkrm[$bar['tanggalpanen']]+=($bar['kgwbnetto']);
	// // 	@$kgpks[$bar['tahuntanam']]+=($bar['kgwbnetto']);
	// // }else{		
	// 	@$kgwb[$bar['tanggalpanen'][$bar['blok']]+=($bar['kgwbnetto']-$bar['brondolan']);
	// 	//@$jjgwb[$bar['tanggalpanen'][$bar['blok']]+=($bar['kgwbnetto']-$bar['brondolan']);
	// 	//@$ttlkgkrm[$bar['tanggalpanen']]+=($bar['kgwbnetto']-$bar['brondolan']);
	// 	// @$kgwb[$bar['tahuntanam']][$bar['tanggalpanen']][$bar['blok']][$bar['nospb']]+=($bar['kgwbnetto']-$bar['brondolan']);			
	// 	// @$kgwbperblok[$bar['blok']][$bar['tanggalpanen']]+=($bar['kgwbnetto']-$bar['brondolan']);
	// //}
	
	// @$jjgwbperblok[$bar['tanggalpanen']][$bar['blok']]+=($bar['jjg']);
	// @$ttlkgpksnetto[$bar['tahuntanam']]+=($bar['kgwbnetto']-$bar['brondolan']);
	
	// @$jjgwb[$bar['tahuntanam']][$bar['tanggalpanen']]+=$bar['jjg'];		
	
	// @$blokspb[$bar['blok']]=$bar['blok'];
	// $spbvspnn[$bar['blok']][$bar['nospb']]=$bar['nospb'];
	
	// $blokspbtanggal[$bar['blok']][$bar['tanggalpanen']]=$bar['blok'];
	// $nospbperblok[$bar['blok']][$bar['tanggalpanen']]=$bar['nospb'];
	// $tglspbperblok[$bar['blok']][$bar['tanggalpanen']]=$bar['tanggal'];
}

// echo "<pre>";
// print_r($kgwb);
$cek=0;
$w="";
if(count($tglpnn)!=0){
	$w=" and (b.tanggal between '".$tgl1."' and '".$tgl2."' or b.tanggal in ('".implode("','",$tglpnn)."'))";
}else{
	$w=" and b.tanggal between '".$tgl1."' and '".$tgl2."'";
}
$optspb=makeOption($dbname,'kebun_spbht','nospb,tujuan');
$str="select a.*,b.tanggal, b.nikmandor, b.nikasisten,c.tahuntanam,c.topografi,d.nospb from ".$dbname.".kebun_prestasi a  
	left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi  
	left join ".$dbname.".kebun_spbdt d on a.nik=d.pemanen and a.kodeorg=d.blok and a.tph=d.tph and b.tanggal=d.tanggalpanen and a.sesi=d.sesi
	left join ".$dbname.".kebun_spbht e on d.nospb=e.nospb 
	left join ".$dbname.".setup_blok c on a.kodeorg=c.kodeorg  
	where	1=1 ".$w." and b.kodeorg='".$unit."' and a.kodeorg like '".$afd."%'  and e.tujuan!='4' 
	 and ((b.noreferensi='' and b.deviceid is null) or (b.noreferensi!='' and b.deviceid!='')) 
	 and b.tipetransaksi='PNN'
	 group by a.notransaksi,a.nourut,a.kodeorg,a.tph,a.sesi,b.tanggal,d.nospb 
	  order by b.tanggal asc";
//echo $str;

// Get Periode From Tanggal
// $exptgl = explode("-","2024-12-28");
// $prdgj = $exptgl[0]."-".$exptgl[1];

// // Cek Hist Karyawannya untuk membaca periode gaji apakah ada perubahan
// $cekHistNik = getCekHistKary("0000000884",$prdgj,"kodejabatan");
// echo "============================== YESSSS ===============================";
// // echo $cekHistNik;
// echo count($cekHistNik);
// echo "============================== YESSSS ===============================";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$list=$jjgkegpnn=$blokkegpanen=array();
while($bar=$res->fetch()){
	if($optspb[$bar['nospb']]!='4'){
		$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
		$karyawanid[$bar['nik']]=$bar['nik'];
		@$list[$bar['tanggal']][$bar['nik']][$bar['nospb']][$bar['nikmandor']][$bar['nikasisten']][$bar['kodeorg']]+=$bar['jjgbuahbesar'];
		@$listkecil[$bar['tanggal']][$bar['nik']][$bar['nospb']][$bar['nikmandor']][$bar['nikasisten']][$bar['kodeorg']]+=$bar['jjgbuahkecil'];

		// Get Periode From Tanggal
		$exptgl = explode("-",$bar['tanggal']);
		$prdgj = $exptgl[0]."-".$exptgl[1];

		// Cek Hist Karyawannya untuk membaca periode gaji apakah ada perubahan
		$cekHistNik = getCekHistKary($bar['nik'],$prdgj,"kodejabatan");
		$countHisNik =  count($cekHistNik);

		// Jika ada perubahan data history di periode gaji tersebut maka munculkan yang versi historynya
		if ($countHisNik == "1") {
			if(($bar['jjgbuahbesar']+$bar['jjgbuahkecil'])>0 or getCekHistKary($bar['nik'],$prdgj,"kodejabatan")=='15'){
				@$list2[$bar['tanggal']][$bar['nik']][$bar['nospb']][$bar['nikmandor']][$bar['nikasisten']][$bar['kodeorg']]+=$bar['jjgbuahbesar'];
				@$listkecil2[$bar['tanggal']][$bar['nik']][$bar['nospb']][$bar['nikmandor']][$bar['nikasisten']][$bar['kodeorg']]+=$bar['jjgbuahkecil'];
			}
		} else {
			// Jika tidak ada perubahan data, maka munculkan datakaryawan saja
			if(($bar['jjgbuahbesar']+$bar['jjgbuahkecil'])>0 or getkary($bar['nik'],'kodejabatan')=='15'){
				@$list2[$bar['tanggal']][$bar['nik']][$bar['nospb']][$bar['nikmandor']][$bar['nikasisten']][$bar['kodeorg']]+=$bar['jjgbuahbesar'];
				@$listkecil2[$bar['tanggal']][$bar['nik']][$bar['nospb']][$bar['nikmandor']][$bar['nikasisten']][$bar['kodeorg']]+=$bar['jjgbuahkecil'];
			}
		}
		
		@$listbrondol[$bar['tanggal']][$bar['nik']][$bar['nospb']][$bar['nikmandor']][$bar['nikasisten']][$bar['kodeorg']]+=$bar['brondolan'];
		//@$listbrondolx[$bar['tanggal']][$bar['nik']][$bar['nospb']][$bar['nikmandor']][$bar['nikasisten']][$bar['kodeorg']]+=$bar['brondolan'];

		@$jlhblokpertk[$bar['nik']][$bar['tanggal']]+=1;
		
		
		@$jjgkegpnn[$bar['tanggal']]+=$bar['hasilkerja'];
		
		$blokkegpanen[$bar['kodeorg']][$bar['tanggal']]=$bar['kodeorg'];
		$jjgpnnperblok[$bar['kodeorg']][$bar['tanggal']]+=$bar['hasilkerja'];

	}
}

// echo"<pre>";
// print_r($list);
// echo"</pre>";


if(count($list2)==0){
	exit("warning : data kosong.");
}


#jjg panen
$w="";
$w=" and tanggal between '".$tgl1."' and '".$tgl2."'";
$str="select * from ".$dbname.".kebun_rekappnn where 1=1 ".$w." and divisi like '".$afd."%' and divisi like '".$unit."%' order by tanggal asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$jjgpanen[$bar['tanggal']]+=$bar['jjgpanen'];

}

$arrtopo=array();
$jlhtopo=array();

#ambil data Hektar panen dan denda hancak
$str = "select * from ".$dbname.".kebun_rekaphancakpanen where tanggal between '".$tgl1."' and '".$tgl2."' and kodeorg like '".$afd."%' order by tanggal asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	// $dendapanen[$bar['kodeorg']][$bar['tanggal']][$bar['nik']]+=$bar['penalti1']+$bar['penalti2']+$bar['penalti3']+$bar['penalti4']+$bar['penalti5']+$bar['penalti6']+$bar['penalti7']+$bar['penalti8']+$bar['penalti9']+$bar['penalti10']+$bar['penalti11']+$bar['penalti12']+$bar['penalti13'];
	$Hektarpanen[$bar['tanggal']][$bar['nik']]+=$bar['hapanen'];
	$Hektarpanenx[$bar['tanggal']][$bar['kodeorg']][$bar['nik']]+=$bar['hapanen'];
	$Hektarpanenx2[$bar['tanggal']][$bar['kodeorg']][$bar['nik']]+=$bar['hapanen'];

}

$datapanentgl=array();
$datapanentglxz=array();

foreach($list as $tglpnn => $key ){
		foreach ($key as $kary => $key2) {
			foreach($key2 as $nospb => $key3){
				foreach($key3 as $mdr => $key4){
						foreach($key4 as $krn => $key5){
							foreach($key5 as $kdblok => $jjgbuahbesar){
								$jjgbuahkecil=$listkecil[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
								
								if(!isset($datapanentgl[$tglpnn][$kary])){
									$datapanentgl[$tglpnn][$kary]=0;
								}if(!isset($datapanentglxz[$tglpnn][$kary][$kdblok])){
									$datapanentglxz[$tglpnn][$kary][$kdblok]=0;
								}
								$datapanentgl[$tglpnn][$kary]+=($jjgbuahbesar+$jjgbuahkecil+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]);
								$datapanentglxz[$tglpnn][$kary][$kdblok]+=($jjgbuahbesar+$jjgbuahkecil+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]);
								//unset($Hektarpanenx2[$blok][$tglpnn][$kary]);
							}
						}
					}
				}
			}
		}
if ($proses == 'excel') {$brd="border=1";}else{$brd='';}

$tab.="<table class=sortable cellspacing=1 cellpadding=5 $brd>";
	$tab.="<thead><tr class=rowheader><td align='center' colspan='5'>HA Panen Yang Tidak Memiliki Janjang dan Brondolan Dalam Keseluruhan Tanggal</td></tr>
	<tr class=rowheader>";
	$tab.="<td align=center width=100px>Tanggal Panen</td>
			  <td align=center width=120px>NIK</td>
			  <td align=center width=120px>Nama Karyawan</td>
			  <td align=center width=75px>HA</td>";
	// $jt=0;
	// foreach($arrtopo as $topografi){
	// 	$jt++;
	// 	$tab.="<td align=center colspan=4>".$optTopografi[$topografi]."</td>";
	// }

	$tab.="</tr>";
	$tab.="</thead>";
$dataxx=0;
foreach($Hektarpanen as $tglpnn => $key ){
		foreach ($key as $kary => $val) {
								if($datapanentgl[$tglpnn][$kary]==0 or !isset($datapanentgl[$tglpnn][$kary])){
									$tab.="<tr class=rowcontent>";
									$tab.="<td align=center>".tanggalnormal($tglpnn)."</td>";
									$tab.="<td align=center>".$nikkar[$kary]."</td>";
									$tab.="<td align=center>".$nmkar[$kary]."</td>";
									$tab.="<td align=center>".@number_format($val,2)."</td>";
									$tab.="</tr>";		
										$dataxx+=1;
								}
			}
		}
$tab.="</table><div style=clear:both></div><hr>";


$tab.="<table class=sortable cellspacing=1 cellpadding=5 $brd>";
	$tab.="<thead><tr class=rowheader><td align='center' colspan='5'>HA Panen Jajang 0 dan Brondolan 0 Tapi Memiliki Janjang dan Brondolan di Blok Lain</td></tr>
	<tr class=rowheader>";
	$tab.="<td align=center width=100px>Tanggal Panen</td>
			  <td align=center width=120px>NIK</td>
			  <td align=center width=120px>Nama Karyawan</td>
			  <td align=center width=120px>BLOK</td>
			  <td align=center width=75px>HA</td>";
	$tab.="</tr>";
	$tab.="</thead>";
foreach($Hektarpanenx as $tglpnn => $key ){
		foreach($key as $kdblok => $key2 ){
				foreach ($key2 as $kary => $val) {
						if($datapanentgl[$tglpnn][$kary]>0 and !isset($datapanentglxz[$tglpnn][$kary][$kdblok])){
							$tab.="<tr class=rowcontent>";
							$tab.="<td align=center>".tanggalnormal($tglpnn)."</td>";
							$tab.="<td align=center>".$nikkar[$kary]."</td>";
							$tab.="<td align=center>".$nmkar[$kary]."</td>";
							$tab.="<td align=center>".$nmorg[$kdblok]."</td>";
							$tab.="<td align=center>".@number_format($val,2)."</td>";
							$tab.="</tr>";		
						}
				}
		}
	}
	

// $tab.="</table><div style=clear:both></div><hr>";

$sDenda = "SELECT id AS nourut, kodedenda, deskripsi, status FROM ".$dbname.".kebun_5kodedendapanen order by id asc";
$rDenda = fetchData($sDenda);
$kodeurut= array();
foreach ($rDenda as $val) {
	$kodeurut[$val['nourut']]=$val['kodedenda'];
}

$sDenda = "SELECT kodedenda, denda FROM ".$dbname.".kebun_5dendapanen where kodeorg='".$unit."' order by kodedenda asc";
$rDenda = fetchData($sDenda);
$pengalidenda= array();
foreach ($rDenda as $val) {
	$pengalidenda[$val['kodedenda']]=$val['denda'];
}


$dendapanen=array();
$str = "select * from ".$dbname.".kebun_rekapmutuhancakpanen where tanggal between '".$tgl1."' and '".$tgl2."' and kodeorg like '".$unit."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	// if($bar['tanggal']=='2024-08-01' and $bar['nik']=='0000000762'){
	// 	echo '1$'.$bar['penalti1'].'###'.$kodeurut['1'].'###'.$pengalidenda[$kodeurut['1']].'=='.($bar['penalti1']*$pengalidenda[$kodeurut['1']]).'<br>';
	// 	echo '2$'.$bar['penalti2'].'###'.$kodeurut['2'].'###'.$pengalidenda[$kodeurut['2']].'=='.($bar['penalti2']*$pengalidenda[$kodeurut['2']]).'<br>';
	// 	echo '3$'.$bar['penalti3'].'###'.$kodeurut['3'].'###'.$pengalidenda[$kodeurut['3']].'=='.($bar['penalti3']*$pengalidenda[$kodeurut['3']]).'<br>';
	// 	echo '4$'.$bar['penalti4'].'###'.$kodeurut['4'].'###'.$pengalidenda[$kodeurut['4']].'=='.($bar['penalti4']*$pengalidenda[$kodeurut['4']]).'<br>';
	// 	echo '5$'.$bar['penalti5'].'###'.$kodeurut['5'].'###'.$pengalidenda[$kodeurut['5']].'=='.($bar['penalti5']*$pengalidenda[$kodeurut['5']]).'<br>';
	// 	echo '6$'.$bar['penalti6'].'###'.$kodeurut['6'].'###'.$pengalidenda[$kodeurut['6']].'=='.($bar['penalti6']*$pengalidenda[$kodeurut['6']]).'<br>';
	// 	echo '7$'.$bar['penalti7'].'###'.$kodeurut['7'].'###'.$pengalidenda[$kodeurut['7']].'=='.($bar['penalti7']*$pengalidenda[$kodeurut['7']]).'<br>';
	// 	echo '8$'.$bar['penalti8'].'###'.$kodeurut['8'].'###'.$pengalidenda[$kodeurut['8']].'=='.($bar['penalti8']*$pengalidenda[$kodeurut['8']]).'<br>';
	// 	echo '9$'.$bar['penalti9'].'###'.$kodeurut['9'].'###'.$pengalidenda[$kodeurut['9']].'=='.($bar['penalti9']*$pengalidenda[$kodeurut['9']]).'<br>';
	// 	echo '10$'.$bar['penalti10'].'###'.$kodeurut['10'].'###'.$pengalidenda[$kodeurut['10']].'=='.($bar['penalti10']*$pengalidenda[$kodeurut['10']]).'<br>';
	// 	echo '11$'.$bar['penalti11'].'###'.$kodeurut['11'].'###'.$pengalidenda[$kodeurut['11']].'=='.($bar['penalti11']*$pengalidenda[$kodeurut['11']]).'<br>';
	// 	echo '12$'.$bar['penalti12'].'###'.$kodeurut['12'].'###'.$pengalidenda[$kodeurut['12']].'=='.($bar['penalti12']*$pengalidenda[$kodeurut['12']]).'<br>';
	// }

	$dendapanen[$bar['tanggal']][$bar['nik']]+=(($bar['penalti1']*$pengalidenda[$kodeurut['1']])+($bar['penalti2']*$pengalidenda[$kodeurut['2']])+($bar['penalti3']*$pengalidenda[$kodeurut['3']])+($bar['penalti4']*$pengalidenda[$kodeurut['4']])+($bar['penalti5']*$pengalidenda[$kodeurut['5']])+($bar['penalti6']*$pengalidenda[$kodeurut['6']])+($bar['penalti7']*$pengalidenda[$kodeurut['7']])+($bar['penalti8']*$pengalidenda[$kodeurut['8']])+($bar['penalti9']*$pengalidenda[$kodeurut['9']])+($bar['penalti10']*$pengalidenda[$kodeurut['10']])+($bar['penalti11']*$pengalidenda[$kodeurut['11']])+($bar['penalti12']*$pengalidenda[$kodeurut['12']]));

	// if($bar['tanggal']=='2024-08-01' and $bar['nik']=='0000000762'){
	// 	echo '<pre>';
	// 	print_r($dendapanen);
	// 	echo '</pre>';
	// }
	// $Hektarpanen[$bar['tanggal']][$bar['nik']]+=$bar['hapanen'];
	// $Hektarpanenx[$bar['kodeorg']][$bar['tanggal']][$bar['nik']]+=$bar['hapanen'];
}

//print_r($basiskg);
#ambil data denda mutubuah
// $str = "select * from ".$dbname.".kebun_prestasi_vw where tanggal between '".$tgl1."' and '".$tgl2."' and kodeorg like '".$unit."%'";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
// 	$dendapanen[$bar['tanggal']][$bar['karyawanid']]+=$bar['rupiahpenalty'];
// }

#ambil basis wb
$str = "select * from ".$dbname.".kebun_5basispanen2 where tahun<='".$prd."' and posting='1'";
$res = fetchdata($str);


// $str = "select * from ".$dbname.".kebun_5basispanen2 where 1=1 and tahun='".$prd."' and posting='1'";
// $jlhbss = count(fetchdata($str));
// if($jlhbss==0){
	$str="select * from ".$dbname.".kebun_5basispanen2 where 1=1 and tahun<='".$prd."'  and posting='1' order by tahun asc";	
//}

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$basiskg[$bar['kodeorg']][$bar['tipehari']][$bar['tipebuah']]=$bar['basis'];
	$basisha[$bar['kodeorg']][$bar['tipehari']][$bar['tipebuah']]=$bar['basisha'];
	$rplb1[$bar['kodeorg']][$bar['tipehari']][$bar['tipebuah']]=$bar['premilebihbasis'];
	$rpbrd[$bar['kodeorg']][$bar['tipehari']][$bar['tipebuah']]=$bar['premibrondolan'];
	
	$blnbasispanen=$bar['tahun'];
}
// echo '<pre>';
// print_r($basiskg);
// echo '<pre>';
// echo '------------------';
// echo '<pre>';
// print_r($basisha);
// echo '<pre>';
#cek transaksi spb belum posting
$row='';
$w="";
$w=" and tanggalpanen between '".$tgl1."' and '".$tgl2."'";
$str="select distinct(tanggal),a.nospb from ".$dbname.".kebun_spbht a left join ".$dbname.".kebun_spbdt b on a.nospb=b.nospb 
	where 1=1 ".$w." and a.nospb like '%".$unit."%' and a.kodeorg='".$unit."' and a.posting='0' and a.tujuan!='4'";
// echo $str;
$res=fetchdata($str);
$row=count($res);
$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$ttp->setFetchMode(PDO::FETCH_ASSOC);
while ($dspb = $ttp->fetch()) {
	$tglx[substr($dspb['tanggal'],8,2)]=substr($dspb['tanggal'],8,2);
	if (!empty($dspb['nospb'])) {
		echo 'NOSPB YANG BELUM DIPOSTING: '.$dspb['nospb']."<br>";
		$cek = 1;
	}
}

#cek keg panen blm posting
$w="";
$w=" and a.tanggal between '".$tgl1."' and '".$tgl2."'";
$str="select distinct(notransaksi) as notransaksi, a.tanggal from ".$dbname.".kebun_prestasi_vs_hk a  
where 1=1 ".$w." and unit='".$unit."' and kodeorg like '".$afd."%' and jurnal='0' and notransaksi like '%/PNN/%' ";
$res=fetchdata($str);
$rowp=count($res);
$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$ttp->setFetchMode(PDO::FETCH_ASSOC);
while ($dspbx = $ttp->fetch()) {
	$tglxp[substr($dspbx['tanggal'],8,2)]=substr($dspbx['tanggal'],8,2);
	if (!empty($dspb['notransaksi'])) {
		echo 'NOTRANSAKSI PANEN YANG BELUM DIPOSTING: '.$dspb['notransaksi']."<br>";
		$cek = 1;
	}
}

if(count($kgwbnetto)==0){
	exit('Warning : Data kosong, cek SPB apakah sudah di posting ?');
}
	$stream='';
	if($tahap=='1'){
		$nomor="001";
	}elseif($tahap=='2'){
		$nomor="002";
	}else{
		$nomor="003";
	}

// if($proses != 'excel'){
	
	
	$tab.="<table class=sortable cellspacing=1 cellpadding=5 $brd>";
	$tab.="<thead><tr class=rowheader>";
	$tab.="<td align=center width=50px>No.SPB</td>
			  <td align=center width=50px>Tanggal Panen</td>
			  <td align=center width=75px>BLOK</td>
			  <td align=center width=75px>BASIS</td>
			  <td align=center width=75px>JJG</td>
			  <td align=center width=75px>BJR</td>
			  <td align=center width=75px>Kg PKS Setelah Pot Brondol</td>";
	// $jt=0;
	// foreach($arrtopo as $topografi){
	// 	$jt++;
	// 	$tab.="<td align=center colspan=4>".$optTopografi[$topografi]."</td>";
	// }

	$tab.="</tr>";
	$tab.="</thead>";
	$bjrspb=array();
	foreach(@$ttwb as $nospb => $key1){
		foreach ($key1 as $tglpanen => $key2) {
			foreach ($key2 as $blok => $key3) {
				foreach ($key3 as $basisx => $value) {
					if($kgwbnetto[$nospb][$tglpanen][$blok][$basisx]>0 and $jjgkirim[$nospb][$tglpanen][$blok][$basisx]>0){
						$bjrspb[$nospb][$tglpanen][$blok][$basisx]=$kgwbnetto[$nospb][$tglpanen][$blok][$basisx]/$jjgkirim[$nospb][$tglpanen][$blok][$basisx];
					}else{
						$bjrspb[$nospb][$tglpanen][$blok][$basisx]=0;
					}

					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center>".$nospb."</td>";
					$tab.="<td align=center>".tanggalnormal($tglpanen)."</td>";
					$tab.="<td align=center>".$nmorg[$blok]."</td>";
					$tab.="<td align=center>".$basisx."</td>";
					$tab.="<td align=right>".@number_format($jjgkirim[$nospb][$tglpanen][$blok][$basisx],2)."</td>";
					$tab.="<td align=right>".@number_format($bjrspb[$nospb][$tglpanen][$blok][$basisx],2)."</td>";
					$tab.="<td align=right>".@number_format($kgwbnetto[$nospb][$tglpanen][$blok][$basisx],2)."</td>";
					$tab.="</tr>";	
				}	
			}
		}
	}
		//print_r($bjrspb);
	$tab.="</table><div style=clear:both></div><hr>";
		
		
// }#tutup if proses != excel
	
	if ($proses == 'excel') {
		$tab.="<table class=sortable cellspacing=1 border=1>";
	} else 	{
		$tab.="<label>Pada saat buah pemanen sudah dikirimkan seluruhnya maka Tombol proses akan muncul di bawah dan yang muncul hanya yang memiliki janjang , untuk yang hanya brondolan tidak muncul , inputkan di bkm rawat untuk rupiah nya</label>";
		$tab.="<div class=table-scroll><table class=sortable cellspacing=1>";
	}
	$tab.="<thead>";
	$tab.="<tr class=rowheader>";
		$tab.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
		$tab.="<th align=center>Tanggal Panen</th>";
		$tab.="<th align=center>No.SPB</th>";
		$tab.="<th align=center>".$_SESSION['lang']['mandor']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['kerani']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['nik2']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['namakaryawan']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['blok']."</th>";
		$tab.="<th align=center>HA Panen</th>";
		$tab.="<th align=center>".$_SESSION['lang']['jjg']." Buah Besar</th>";
		$tab.="<th align=center>".$_SESSION['lang']['bjr']."</th>";
		$tab.="<th align=center>Total Kg</th>";
		$tab.="<th align=center>Total Kg + Brondolan</th>";
		$tab.="<th align=center>".$_SESSION['lang']['basic']." Kg</th>";
		$tab.="<th align=center> Proporsi Basis ".$_SESSION['lang']['basic']." Kg</th>";
		$tab.="<th align=center>HK</th>";
		$tab.="<th align=center>Rp. Upah</th>";
		$tab.="<th align=center>POT.HK</th>";
		$tab.="<th align=center>Rp. POT.Upah</th>";
		$tab.="<th align=center>Lebih Basis</th>";
		$tab.="<th align=center>Rp. Lebih Basis</th>";
		$tab.="<th align=center>".$_SESSION['lang']['jjg']." Buah Kecil</th>";
		$tab.="<th align=center>".$_SESSION['lang']['bjr']."</th>";
		$tab.="<th align=center>Total Kg</th>";
		$tab.="<th align=center>Total Kg + Brondolan</th>";
		$tab.="<th align=center>".$_SESSION['lang']['basic']." Kg</th>";
		$tab.="<th align=center> Proporsi Basis ".$_SESSION['lang']['basic']." Kg</th>";
		$tab.="<th align=center>HK</th>";
		$tab.="<th align=center>Rp. Upah</th>";
		$tab.="<th align=center>POT.HK</th>";
		$tab.="<th align=center>Rp. POT.Upah</th>";
		$tab.="<th align=center>Lebih Basis</th>";
		$tab.="<th align=center>Rp. Lebih Basis</th>";
		$tab.="<th align=center>".$_SESSION['lang']['brondol']." Kg</th>";
		$tab.="<th align=center>Rp. ".$_SESSION['lang']['brondol']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['denda']." panen</th>";
		$tab.="<th align=center>".$_SESSION['lang']['total']."</th>";
	$tab.="</tr>";
	$tab.="</thead>";
	
	
	// echo "<pre>";
	// print_r([$list]);
	// echo "</pre>";
	//$sql = "select * from ".$dbname.".kebun_5basispanen where kodeorg='".$unit."' and batasbawah <= '".$kgkirim."' and batasatas >= '".$kgkirim."' and tanggalberlaku <='".$tglpnn."' order by batasatas desc limit 1";
	//$recal='recal';
	if($recal!='recal'){		
		$_SESSION['temppnn']=array();
	}
	
	
	// echo "<pre>";
	// print_r($kgwb);
	// echo "</pre>";
	
	// exit();
	
	
	
	
	$nokar=0;
	$color='';$bjr=$kgkirim=0;
	$counthkpanen=array();
	@$no=$bjr=$ttlrplb1=$ttlrplb2=0;
	$jlhblktk=$rptopoperkary=0;
	$sttljjg=$sttlkgkirim=$persentasekarybrond=$persentasekary=$persentasekarykg=$persentasekarykg2=$persentasekary2=$persentasekarytotbrond=$persentasekarytot=$persentasekarytotkg=$persentasekarytotkg2=$persentasekarytot2=$basispanentotal=$countbasispanen=array();
	$datamasukke2=array();
	$listbrondolx=array();
	foreach($list2 as $tglpnn => $key ){
			$jenispremi = getjenisharikerja($unit,$tglpnn);
			if($jenispremi=='JUMAT'){
				$jenispremi='KERJA';
			}
		foreach ($key as $kary=> $key2) {
			foreach($key2 as $nospb => $key3){
				foreach($key3 as $mdr => $key4){
						foreach($key4 as $krn => $key5){
							foreach($key5 as $kdblok => $jjgbuahbesar){
									$datamasukke2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]=0;
									if(!isset($kgwbnetto[$nospb][$tglpnn][$kdblok])){
										echo 'NOSPB : '.$nospb.' BLOK'.$kdblok.' TANGGAL PANEN :'.$tglpnn.'<br>';
										$cek=1;
									}
									$counthkpanen[$tglpnn][$kary]+=1;
								if($jenispremi=='LIBUR' and $basiskg[$kdblok][$jenispremi]['BESAR']==0){
									if($jjgbuahbesar>0){
										$listbrondolzx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]=$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];

										$persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']= ($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR'])+$listbrondolzx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
										$persentasekarytotkg[$tglpnn][$kary]+= ($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR'])+$listbrondolzx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
										$listbrondolzx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]=0;
									}else{
										$listbrondolzx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]=$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
										$persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']= 0;
										$persentasekarytotkg[$tglpnn][$kary]+= 0;
									}
								}else{

									if(($jjgbuahbesar+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok])>0){
										$persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']= ($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR']);
									 	$persentasekary[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']= (($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR'])/$basiskg[$kdblok][$jenispremi]['BESAR']);
									 	$persentasekarytot[$tglpnn][$kary]+= (($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR'])/$basiskg[$kdblok][$jenispremi]['BESAR']);
									 	$persentasekarytotkg[$tglpnn][$kary]+= ($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR']);
										$basispanentotal[$tglpnn][$kary]['BESAR']+=$basiskg[$kdblok][$jenispremi]['BESAR'];
										$countbasispanen[$tglpnn][$kary]['BESAR']+=1;

										$persentasekarybrond[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']=$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
										$persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']= (($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR'])+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]);

										$persentasekary2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']= ((($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR'])+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok])/$basiskg[$kdblok][$jenispremi]['BESAR']);
										$persentasekarytotbrond[$tglpnn][$kary]+=$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
										$persentasekarytot2[$tglpnn][$kary]+= ((($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR'])+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok])/$basiskg[$kdblok][$jenispremi]['BESAR']);
												
										$persentasekarytotkg2[$tglpnn][$kary]+=(($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR'])+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]);

										$listbrondolx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']= $listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
										$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]=0;
									}else{
										$persentasekary[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']= 0;
										$persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']= 0;
									 	$persentasekarytot[$tglpnn][$kary]+= 0;
									 	$persentasekarytotkg[$tglpnn][$kary]+= 0;

									 	$persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']=0;
										$persentasekary2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']=0;
										$persentasekarybrond[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']=0;
										$persentasekarytot2[$tglpnn][$kary]+=0;
										$persentasekarytotkg2[$tglpnn][$kary]+=0;
										$persentasekarytotbrond[$tglpnn][$kary]+=0;
									}
									

									// echo $basiskg[$kdblok][$jenispremi]['BESAR']."<br>";
									// echo $bjrspb[$tglpnn][$kdblok]."<br>";
									// echo "====".$jjgbuahbesar."<br>";
									// exit("warning");
									


									// if($nospb=='0011/PPPE/08/2024' and $kary=='0000000777'){
									// 	echo '1#'.$kdblok.'#'.$jenispremi.'#'.$persentasekary[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR'].'<br>';
									// 	echo '1#'.$kdblok.'#'.$jenispremi.'#'.$persentasekarytot[$tglpnn][$kary].'<br>';
									// 	echo '1#'.$kdblok.'#'.$jenispremi.'#'.$basisha[$kdblok][$jenispremi]['BESAR'].'<br>';
									// }
								}

								if($jenispremi=='LIBUR' and $basiskg[$kdblok][$jenispremi]['KECIL']==0){
									$jjgbuahkecil=0;
									$jjgbuahkecil=$listkecil2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
									if($jjgbuahkecil>0){
										$persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']= ($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL'])+$listbrondolzx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
										$persentasekarytotkg[$tglpnn][$kary]+= ($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL'])+$listbrondolzx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
									}else{
										$persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']= 0;
										$persentasekarytotkg[$tglpnn][$kary]+= 0;
									}
								}else{
									$jjgbuahkecil=0;
									$jjgbuahkecil=$listkecil2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
									if(($jjgbuahkecil+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok])>0){
									 	$persentasekary[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']= (($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL'])/$basiskg[$kdblok][$jenispremi]['KECIL']);
									 	$persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']= ($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL']);
									 	$persentasekarytot[$tglpnn][$kary]+= (($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL'])/$basiskg[$kdblok][$jenispremi]['KECIL']);
									 	$persentasekarytotkg[$tglpnn][$kary]+= ($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL']);
									 	$basispanentotal[$tglpnn][$kary]['KECIL']+=$basiskg[$kdblok][$jenispremi]['KECIL'];
										$countbasispanen[$tglpnn][$kary]['KECIL']+=1;

										$persentasekarybrond[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']=$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];

										$persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']= (($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL'])+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]);

										$persentasekary2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']= ((($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL'])+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok])/$basiskg[$kdblok][$jenispremi]['KECIL']);

										$persentasekarytotbrond[$tglpnn][$kary]+=$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
										$persentasekarytot2[$tglpnn][$kary]+= ((($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL'])+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok])/$basiskg[$kdblok][$jenispremi]['KECIL']);
										$persentasekarytotkg2[$tglpnn][$kary]+= (($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL'])+$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]);
										$listbrondolx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']= $listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
										$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]=0;
									}else{
										$persentasekary[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']= 0;
										$persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']= 0;
									 	$persentasekarytot[$tglpnn][$kary]+= 0;
									 	$persentasekarytotkg[$tglpnn][$kary]+= 0;

									 	$persentasekarybrond[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']=0;
										$persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']=0;
										$persentasekary2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']+=0;
										$persentasekarytot2[$tglpnn][$kary]+=0;
										$persentasekarytotbrond[$tglpnn][$kary]+=0;
										$persentasekarytotkg2[$tglpnn][$kary]+=0;
									}
								}
							}
						}
					}
				}
			}
			
		}

	
//exit('error');
		
	$cekbasis=0;
	$cekbasisblok=$basisbaru=array();
	foreach($list2 as $tglpnn => $key ){
			$jenispremi = getjenisharikerja($unit,$tglpnn);
			if($jenispremi=='JUMAT'){
				$jenispremi='KERJA';
			}
		foreach ($key as $kary => $key2) {
			foreach($key2 as $nospb => $key3){
				foreach($key3 as $mdr => $key4){
						foreach($key4 as $krn => $key5){
							foreach($key5 as $kdblok => $jjgbuahbesar){
							$counthkpanenx[$tglpnn][$kary]+=1;
										// if(@$basiskg[$tt][$optkodetopo[$kdblok]]==''){
										
										// }
										@$no+=1;
										$nokar+=1;
										
										/* if(substr($tglpnn,0,7)!=$prd){
											$bjr=$kgwblalu[$tt][$tglpnn]/$jjgpertt[$tt][$tglpnn][$topografi];
											$color=" style=background-color:yellow";
										}else{
											@$bjr=$kgwb[$tt][$tglpnn]/$jjgpertt[$tt][$tglpnn][$topografi];
											$color='';
										} */
										

										$color='';
										
										//$kgkirim=$kgwb[$tt][$tglpnn][$kdblok][$nospb];
										
										$i = "style=display:none";
										
										
										$notr2 = str_replace("-","",$tglpnn)."/".$afd."/PNN02/".$nomor;
										$tab.="<tr class=rowcontent ".$color." id=row".$no.">";	
										if($proses!='excel'){
											$tab.="<td align=center style=display:none><input disabled class=myinputtext style=width:160px;display:none value='".$notr2."' id=notransaksi_".$no."></td>";
										}	
										$tab.="<td align=center>".$nokar."</td>";
										$tab.="<td id=tglpnn_".$no." align=center>".$tglpnn."</td>";
										$tab.="<td id=nospb_".$no." align=center>".$nospb."</td>";
										if($proses!='excel'){
											$tab.="<td id=rowmdr_".$no." ".$i.">".$mdr."</td>";
											$tab.="<td id=rowkrn_".$no." ".$i.">".$krn."</td>";
											$tab.="<td id=rowkary_".$no." ".$i.">".$kary."</td>";
											$tab.="<td hidden id=rowblok_".$no.">".$kdblok."</td>";
										}
										
										
										
										$upah1hk=getUpahKary(substr($tglpnn, 0,7),$kary);
										//$persentase= (($jjgbuahbesar*$bjrspb[$tglpnn][$kdblok])/$basiskg[$kdblok][$jenispremi]['KECIL']);
										//$kglbbesar    = (($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]) - $basiskg[$kdblok][$jenispremi]['BESAR']);
										$HKbesar=0; 
										$HKbesarpot=0; 
										if(!isset($hkpanen[$tglpnn][$kary])){
											$hkpanen[$tglpnn][$kary]=0;
										}
									if($jenispremi=='LIBUR' and $basiskg[$kdblok][$jenispremi]['BESAR']==0){
										$HKbesar=0;
										$HKbesarpot=0;
										$kglbbesar= ($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR']);
									}else{
										if($persentasekarytot[$tglpnn][$kary]<1){
											$listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']=0;
											$HKbesar=0;
											$HKbesarpot=0;
											$kglbbesar=0;
											@$sbttkglbbuahbesar[$tglpnn][$kary]+=$kglbbesar;
												if($persentasekarytot2[$tglpnn][$kary]<1){
													if(getkary($kary,'tipekaryawan')!='4'){
														$HKbesar=($persentasekary2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']/$persentasekarytot2[$tglpnn][$kary]*1);

														$hkpanen[$tglpnn][$kary]+=$HKbesar;
														if($hkpanen[$tglpnn][$kary]>1){
															$lebih=$hkpanen[$tglpnn][$kary]-1;
															$hkpanen[$tglpnn][$kary]=1;
															$HKbesar=$HKbesar-$lebih;
														}
														$HKbesarpot=($persentasekary2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']/$persentasekarytot2[$tglpnn][$kary]*(1-($persentasekarytot2[$tglpnn][$kary])));
														$listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']=0;
													}else{
														$HKbesar=($persentasekary2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']/$persentasekarytot2[$tglpnn][$kary]*$persentasekarytot2[$tglpnn][$kary]);
													}
													$basisbaru['BESAR']=(($persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']/$persentasekarytotkg2[$tglpnn][$kary])*$basiskg[$kdblok][$jenispremi]['BESAR']);
													$kglbbesar=0;
													@$sbttkglbbuahbesar[$tglpnn][$kary]+=$kglbbesar;
												}else{
													$HKbesar=($persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']/$persentasekarytotkg2[$tglpnn][$kary]);
													$basisbaru['BESAR']=(($persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']/$persentasekarytotkg[$tglpnn][$kary])*$persentasekarytotkg[$tglpnn][$kary])+((($persentasekarytotkg2[$tglpnn][$kary]/$persentasekarytot2[$tglpnn][$kary])-$persentasekarytotkg[$tglpnn][$kary])*($persentasekarybrond[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']/$persentasekarytotbrond[$tglpnn][$kary]));

													$kglbbesar=0;
													$listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']=$persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']-$basisbaru['BESAR']-$kglbbesar;
												    @$sbttkglbbuahbesar[$tglpnn][$kary]+=$kglbbesar;

													


												}

										}else{
											$HKbesar=0;
											$HKbesarpot=0;
											$kglbbesar=0;
											@$sbttkglbbuahbesar[$tglpnn][$kary]+=$kglbbesar;
												    $HKbesar=($persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']/$persentasekarytotkg[$tglpnn][$kary]);
													$basisbaru['BESAR']=(($persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']/$persentasekarytotkg[$tglpnn][$kary])*($persentasekarytotkg2[$tglpnn][$kary]/$persentasekarytot2[$tglpnn][$kary]));
													$kglbbesar    = (($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR']) - $basisbaru['BESAR']);
													if($kglbbesar<=0){
														$kglbbesar=0;
													}
													$listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']=$persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']-$basisbaru['BESAR']-$kglbbesar;
												//@$sbttkglbbuahbesar[$tglpnn][$kary]=$kglbbesar;

												$hkpanen[$tglpnn][$kary]+=$HKbesar;

												if($hkpanen[$tglpnn][$kary]>1){
														$lebih=$hkpanen[$tglpnn][$kary]-1;
														$hkpanen[$tglpnn][$kary]=1;
														$HKbesar=$HKbesar-$lebih;
													}

											

											
										}
									}
										

													// if($nospb=='0011/PPPE/08/2024' and $kary=='0000000777' and getkary($kary,'tipekaryawan')!='4'){
													// 	echo '1#'.$persentasekary[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR'].'<br>';
													// 	echo '1#'.$persentasekarytot[$tglpnn][$kary].'<br>';
													// 	echo '1#'.(1-($hektarbesar/$basisha[$kdblok][$jenispremi]['BESAR'])).'<br>';
													// }

													// if($nospb=='0011/PPPE/08/2024' and $kary=='0000000777' and getkary($kary,'tipekaryawan')=='4'){
													// 	echo '2#'.$persentasekary[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR'].'<br>';
													// 	echo '2#'.$persentasekarytot[$tglpnn][$kary].'<br>';
													// 	echo '2#'.($hektarbesar/$basisha[$kdblok][$jenispremi]['BESAR']).'<br>';
													// }

										if(is_nan($HKbesar) and !isset($cekbasisblok[$kdblok]['BESAR'][$tglpnn][$kary])){
											echo "Terdapat basis panen yang belum terdaftar : Basis panen blok ".$kdblok." jenis basis Besar atau SPB belum diposting <br>";
											$cekbasis=1;
											$cekbasisblok[$kdblok]['BESAR'][$tglpnn][$kary]=1;
										}

										$HKkecil=0; 
										$HKkecilpot=0; 
										$jjgbuahkecil=0;
										$jjgbuahkecil=$listkecil2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
										if(!isset($hkpanen[$tglpnn][$kary])){
											$hkpanen[$tglpnn][$kary]=0;
										}

									if($jenispremi=='LIBUR' and $basiskg[$kdblok][$jenispremi]['KECIL']==0){
										$HKkecil=0;
										$HKkecilpot=0;
										$kglbkecil= ($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL']);
									}else{
										if($persentasekarytot[$tglpnn][$kary]<1){
											$listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']=0;
											$HKkecil=0;
											$kglbkecil=0;
											$HKkecilpot=0;
											@$sbttkglbbuahkecil[$tglpnn][$kary]+=$kglbkecil;
												if($persentasekarytot2[$tglpnn][$kary]<1){
													if(getkary($kary,'tipekaryawan')!='4'){
													$HKkecil=($persentasekary2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']/$persentasekarytot2[$tglpnn][$kary]*1);

													$hkpanen[$tglpnn][$kary]+=$HKkecil;
													if($hkpanen[$tglpnn][$kary]>1){
														$lebih=$hkpanen[$tglpnn][$kary]-1;
														$hkpanen[$tglpnn][$kary]=1;
														$HKkecil=$HKkecil-$lebih;
													}
													$HKkecilpot=($persentasekary2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']/$persentasekarytot2[$tglpnn][$kary]*(1-($persentasekarytot2[$tglpnn][$kary])));
													}else{
														$HKkecil=($persentasekary2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']/$persentasekarytot2[$tglpnn][$kary]*$persentasekarytot2[$tglpnn][$kary]);
													}
													$listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']=0;
													$kglbkecil=0;
													$basisbaru['KECIL']=(($persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']/$persentasekarytotkg2[$tglpnn][$kary])*$basiskg[$kdblok][$jenispremi]['KECIL']);
													@$sbttkglbbuahkecil[$tglpnn][$kary]+=$kglbkecil;
												}else{
													$HKkecil=($persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']/$persentasekarytotkg2[$tglpnn][$kary]);
													$basisbaru['KECIL']=(($persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']/$persentasekarytotkg[$tglpnn][$kary])*$persentasekarytotkg[$tglpnn][$kary])+((($persentasekarytotkg2[$tglpnn][$kary]/$persentasekarytot2[$tglpnn][$kary])-$persentasekarytotkg[$tglpnn][$kary])*($persentasekarybrond[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']/$persentasekarytotbrond[$tglpnn][$kary]));

													$kglbkecil=0;
													$listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']= $persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']-$basisbaru['KECIL']-$kglbkecil;
													@$sbttkglbbuahkecil[$tglpnn][$kary]+=$kglbkecil;



												}

										}else{
											$HKkecil=0;
											$kglbkecil=0;
											$HKkecilpot=0;
											@$sbttkglbbuahkecil[$tglpnn][$kary]+=$kglbkecil;
												$HKkecil=($persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']/$persentasekarytotkg[$tglpnn][$kary]);
													$basisbaru['KECIL']=(($persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']/$persentasekarytotkg[$tglpnn][$kary])*($persentasekarytotkg2[$tglpnn][$kary]/$persentasekarytot2[$tglpnn][$kary]));
													$kglbkecil    = (($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL']) - $basisbaru['KECIL']);

												if($kglbkecil<=0){
													$kglbkecil=0;
												}

												$listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']=$persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']-$basisbaru['KECIL']-$kglbkecil;

												@$sbttkglbbuahkecil[$tglpnn][$kary]+=$kglbkecil;

													$hkpanen[$tglpnn][$kary]+=$HKkecil;
													if($hkpanen[$tglpnn][$kary]>1){
														$lebih=$hkpanen[$tglpnn][$kary]-1;
														$hkpanen[$tglpnn][$kary]=1;
														$HKkecil=$HKkecil-$lebih;
													}
											

										}
									}

										if(is_nan($HKkecil) and !isset($cekbasisblok[$kdblok]['KECIL'][$tglpnn][$kary])){
											echo "Terdapat basis panen yang belum terdaftar : Basis panen blok ".$kdblok." jenis basis kecil atau spb belum diposting  <br>";
											$cekbasis=1;
											$cekbasisblok[$kdblok]['KECIL'][$tglpnn][$kary]=1;

										}

										if($jenispremi=='LIBUR'){
											if($basiskg[$kdblok][$jenispremi]['BESAR']>0){
												$rplbbssbesar = (($kglbbesar * $rplb1[$kdblok][$jenispremi]['BESAR'])+(($persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']/$persentasekarytotkg[$tglpnn][$kary])*$upah1hk));	
												$HKbesar=$HKbesarpot=$basisbaru['BESAR']=$listbrondolx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']=0;
											}else{
												$rplbbssbesar = (($kglbbesar * $rplb1[$kdblok][$jenispremi]['BESAR'])+($HKbesar*$upah1hk)-($HKbesarpot*$upah1hk));	
												$HKbesar=$HKbesarpot=0;
												$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]+=($listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']);
											}


											if($basiskg[$kdblok][$jenispremi]['KECIL']>0){
												$rplbbsskecil = (($kglbkecil * $rplb1[$kdblok][$jenispremi]['KECIL'])+(($persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']/$persentasekarytotkg[$tglpnn][$kary])*$upah1hk));	
												$HKkecil=$HKkecilpot=$basisbaru['KECIL']=$listbrondolx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']=0;
											}else{
												$rplbbsskecil = (($kglbkecil * $rplb1[$kdblok][$jenispremi]['KECIL'])+($HKkecil*$upah1hk)-($HKkecilpot*$upah1hk));	
												$HKkecil=$HKkecilpot=0;
												$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]+=($listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']);
											}

											$dendaproporsi=0;
											$dendaproporsi=((($persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']+$persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR'])/$persentasekarytotkg[$tglpnn][$kary])*$dendapanen[$tglpnn][$kary]);
										}else{
											$rplbbssbesar = $kglbbesar * $rplb1[$kdblok][$jenispremi]['BESAR'];	
											$rplbbsskecil = $kglbkecil * $rplb1[$kdblok][$jenispremi]['KECIL'];
											$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]+=($listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']+$listbrondol2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']);
											$dendaproporsi=0;
											$dendaproporsi=((($persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']+$persentasekarykg2[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR'])/$persentasekarytotkg2[$tglpnn][$kary])*$dendapanen[$tglpnn][$kary]);
										}

										// if($kary=='0000000773' and $tglpnn=='2024-10-12'){
										// 	echo ($persentasekarykg[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']/$persentasekarytotkg[$tglpnn][$kary]).'xxx<br>';
										// 	echo ($persentasekarytotkg2[$tglpnn][$kary]/$persentasekarytot2[$tglpnn][$kary]).'yyy<br>';
										// 	echo ($persentasekarytotkg2[$tglpnn][$kary]).'yyy1<br>';
										// 	echo ($persentasekarytot2[$tglpnn][$kary]).'yyy2<br>';
										// }
										$hektarproprosi=0;
										$hektarproprosi=$Hektarpanenx[$tglpnn][$kdblok][$kary];
										unset($Hektarpanenx[$tglpnn][$kdblok][$kary]);
										$tab.="<td>".@$nmkar[$mdr]."</td>";
										$tab.="<td>".@$nmkar[$krn]."</td>";
										$tab.="<td>".@$nikkar[$kary]."</td><td>".@$nmkar[$kary]."</td>";
										$tab.="<td>".$nmorg[$kdblok]."</td>";
										$tab.="<td id=hapanen_".$no." align=right>".nb_format($hektarproprosi,2)."</td>";
										$tab.="<td id=rowjjgbesar_".$no." align=right>".nb_format($jjgbuahbesar)."</td>";
										$tab.="<td id=bjrjjgbesar_".$no." align=right>".nb_format($bjrspb[$nospb][$tglpnn][$kdblok]['BESAR'],2)."</td>";
										
										
										$tab.="<td id=rowkgnettobesar_".$no." ".$n." align=right>".nb_format(($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR']),2)."</td>";
										$tab.="<td id=rowkgnettobesarplusbrondol_".$no." ".$n." align=right>".nb_format((($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR'])+$listbrondolx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']),2)."</td>";
										$tab.="<td id=rowkgbssbesar_".$no." align=right>".nb_format($basiskg[$kdblok][$jenispremi]['BESAR'])."</td>";
										$tab.="<td id=rowkgbssbesarpro_".$no." align=right>".nb_format($basisbaru['BESAR'])."</td>";



										$tab.="<td id=HKbesar_".$no." ".$n." align=right>".nb_format($HKbesar,2)."</td>";
										$tab.="<td id=rpHKbesar_".$no." ".$n." align=right>".($HKbesar*$upah1hk)."</td>";
										$tab.="<td id=HKbesarpot_".$no." ".$n." align=right>".$HKbesarpot."</td>";
										$tab.="<td id=rpHKbesarpot_".$no." ".$n." align=right>".($HKbesarpot*$upah1hk)."</td>";
										$tab.="<td id=rowkglb1besar_".$no." align=right>".nb_format($kglbbesar,2)."</td>";

										$tab.="<td id=rowhargarplb1besar_".$no." align=right>".nb_format($rplbbssbesar,2)."</td>";


										
										$tab.="<td id=rowjjgkecil_".$no." align=right>".nb_format($jjgbuahkecil)."</td>";
										$tab.="<td id=bjrjjgkecil_".$no." align=right>".nb_format($bjrspb[$nospb][$tglpnn][$kdblok]['KECIL'],2)."</td>";
										
										
										$tab.="<td id=rowkgnettokecil_".$no." ".$n." align=right>".nb_format(($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL']),2)."</td>";
										$tab.="<td id=rowkgnettokecilplusbrondol_".$no." ".$n." align=right>".nb_format((($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL'])+$listbrondolx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']),2)."</td>";
										
										$tab.="<td id=rowkgbsskecil_".$no." align=right>".nb_format($basiskg[$kdblok][$jenispremi]['KECIL'])."</td>";
										$tab.="<td id=rowkgbsskecilpro_".$no." align=right>".nb_format($basisbaru['KECIL'])."</td>";
										//$persentase= (($jjgbuahkecil*$bjrspb[$tglpnn][$kdblok])/$basiskg[$kdblok][$jenispremi]['KECIL']);
										
										$tab.="<td id=HKkecil_".$no." ".$n." align=right>".nb_format($HKkecil,2)."</td>";
										$tab.="<td id=rpHKkecil_".$no." ".$n." align=right>".($HKkecil*$upah1hk)."</td>";
										$tab.="<td id=HKkecilpot_".$no." ".$n." align=right>".$HKkecilpot."</td>";
										$tab.="<td id=rpHKkecilpot_".$no." ".$n." align=right>".($HKkecilpot*$upah1hk)."</td>";
										$tab.="<td id=rowkglb1kecil_".$no." align=right>".nb_format($kglbkecil,2)."</td>";
										$tab.="<td id=rowhargarplb1kecil_".$no." align=right>".nb_format($rplbbsskecil,2)."</td>";


											
										
										$tab.="<td id=brondolankg_".$no." ".$n." align=right>".nb_format($listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok],2)."</td>";
										$tab.="<td id=rpbrondolan_".$no." ".$n." align=right>".($listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]*$rpbrd[$kdblok][$jenispremi]['BESAR'])."</td>";
										$tab.="<td id=dendapanen_".$no." align=right>".nb_format($dendaproporsi,2)."</td>";
										$totaldapat=0;
										$totaldapat=(($HKbesar*$upah1hk)+($HKkecil*$upah1hk)+$rplbbssbesar+$rplbbsskecil+($listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]*$rpbrd[$kdblok][$jenispremi]['BESAR'])-$dendaproporsi-($HKbesarpot*$upah1hk)-($HKkecilpot*$upah1hk));
										$tab.="<td id=total_".$no." ".$n." align=right>".nb_format($totaldapat,2)."</td>";
										
										
										
										
										@$sbtthektarpanenkary[$tglpnn][$kary]+=$hektarproprosi;
										@$sbtthkkaryawanbesar[$tglpnn][$kary]+=$HKbesar;
										@$sbtthkkupaharyawanbesar[$tglpnn][$kary]+=($HKbesar*$upah1hk);
										@$sbtthkkaryawanbesarpot[$tglpnn][$kary]+=$HKbesarpot;
										@$sbtthkkupaharyawanbesarpot[$tglpnn][$kary]+=($HKbesarpot*$upah1hk);
										@$sbtthkkaryawankecil[$tglpnn][$kary]+=$HKkecil;
										@$sbtthkkupaharyawankecil[$tglpnn][$kary]+=($HKkecil*$upah1hk);
										@$sbtthkkaryawankecilpot[$tglpnn][$kary]+=$HKkecilpot;
										@$sbtthkkupaharyawankecilpot[$tglpnn][$kary]+=($HKkecilpot*$upah1hk);
										@$sbttjjgbuahbesar[$tglpnn][$kary]+=$jjgbuahbesar;
										@$sbttjjgbuahkecil[$tglpnn][$kary]+=$jjgbuahkecil;
										@$sbttkgbuahbesar[$tglpnn][$kary]+=($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR']);
										@$sbttkgbuahkecil[$tglpnn][$kary]+=($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL']);
										@$sbttkgbrondbuahbesar[$tglpnn][$kary]+=(($jjgbuahbesar*$bjrspb[$nospb][$tglpnn][$kdblok]['BESAR'])+$listbrondolx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['BESAR']);
										@$sbttkgbrondbuahkecil[$tglpnn][$kary]+=(($jjgbuahkecil*$bjrspb[$nospb][$tglpnn][$kdblok]['KECIL'])+$listbrondolx[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]['KECIL']);
										@$sbttkglbbuahbesar[$tglpnn][$kary]+=$kglbbesar;
										@$sbttkglbbuahkecil[$tglpnn][$kary]+=$kglbkecil;
										@$sbttrplbbssbesar[$tglpnn][$kary]+=$rplbbssbesar;
										@$sbttrplbbsskecil[$tglpnn][$kary]+=$rplbbsskecil;
										@$sbttbrondolkg[$tglpnn][$kary]+=$listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok];
										@$sbttrpbrondolkg[$tglpnn][$kary]+=($listbrondol[$tglpnn][$kary][$nospb][$mdr][$krn][$kdblok]*$rpbrd[$kdblok][$jenispremi]['BESAR']);
										@$sbttdendapanenkary[$tglpnn][$kary]+=$dendaproporsi;
										@$sbtthtotaldapatpanenkary[$tglpnn][$kary]+=$totaldapat;
										
								


									
								
							}
							
						}
					
				}
			}
							$tab.="<tr class=rowcontent>";
							$tab.="<td colspan=8 align=center bgcolor=#ADFF2F><b>SUB TOTAL  TANGGAL ".tanggalnormal($tglpnn)." Karyawan : ".$nmkar[$kary]."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbtthektarpanenkary[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttjjgbuahbesar[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttkgbuahbesar[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttkgbrondbuahbesar[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbtthkkaryawanbesar[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbtthkkupaharyawanbesar[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbtthkkaryawanbesarpot[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbtthkkupaharyawanbesarpot[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttkglbbuahbesar[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttrplbbssbesar[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttjjgbuahkecil[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttkgbuahkecil[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttkgbrondbuahkecil[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbtthkkaryawankecil[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbtthkkupaharyawankecil[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbtthkkaryawankecilpot[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbtthkkupaharyawankecilpot[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttkglbbuahkecil[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttrplbbsskecil[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttbrondolkg[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttrpbrondolkg[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbttdendapanenkary[$tglpnn][$kary],2)."</b></td>";
							$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sbtthtotaldapatpanenkary[$tglpnn][$kary],2)."</b></td>";
							
							$tab.="</tr>";
		}
	}
	
	
		// $tab.="<tr class=rowcontent>";
		// $tab.="<input hidden id=totalbaris value=".$no.">";
		// $tab.="<td colspan=10 align=center bgcolor=#ADFF2F><b>GRAND TOTAL</b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttljjg)."</b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlkgkirim,2)."</b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlkgkirimnet,2)."</b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlkglb,2)."</b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlrplb)."</b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gtbrd)."</b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gtrpbrd)."</b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gttopo)."</b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gttambah)."</b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gtdenda)."</b></td>";
		// $tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlrpall)."</b></td>";
		// $tab.="</tr>";
		$tab.="</tbody></table></div><br>";
		
	//echo $cek.'xxxx'.$rowp.'xxxxx'.$row;
if ($proses != 'excel' and $row==0 and $rowp==0 and $afd!='%%' and $cek==0 and $cekbasis==0 and $dataxx==0 and $databasisbloktidakada==0 and @$prdgaji=='0' and @$prdakt=='0') {
	
	$arr="##prd##unit##afd##tahap##tgl1##tgl2##kgbrondol##perpot";
	//$r="style=display:none";
	//$tab.="<button class=mybutton ".$r." id=recal onclick=zpreviewdata('".$arr."','recal');>Rekalkulasi</button>";
	$tab.="<button class=mybutton id=proses onclick=saveAll(".$no.");>".$_SESSION['lang']['proses']."</button>";
}
else{
	// echo $proses.'{}'.$row.'{}'.$rowp.'{}'.$afd.'{}'.$cek.'<br>';
	// echo $cekbasis.'{}'.$dataxx.'{}'.$prdgaji.'{}'.$prdakt.'<br>';
	if($row!=0){
		echo "Data SPB Ada Yang Belum Diposting<br>";
	}
	if($rowp!=0){
		echo "Data Panen Ada Yang Belum Diposting<br>";
	}
	if($afd=='%%'){
		echo "Divisi tidak boleh kosong<br>";
	}
	if($databasisbloktidakada==1){
		echo "Ada Data Basis Blok Yang Tidak Ada<br>";
	}
	if($cek==1){
		echo "Ada Data Panen Dan SPB Belum Diposting<br>";
	}
	if($dataxx==1){
		echo "Ada Data Panen Yang Memiliki Hektar Dan Janjang Kosong Keseluruhan<br>";
	}
	if($cekbasis==1){
		echo "Ada Basis Panen Belum Terdaftar Atau SPB Belum Diposting<br>";
	}
	if($prdgaji=='1'){
		echo "Sudah Proses Gaji Transaksi Tidak Bisa Diproses<br>";
	}

	if($prdgaji==''){
		echo "Periode gaji tidak ada, Transaksi Tidak Bisa Diproses<br>";
	}

	if($prdakt=='1'){
		echo "Sudah Tutup Buku Transaksi Tidak Bisa Diproses<br>";
	}

	if($prdakt==''){
		echo "Periode akutansi tidak ada, Transaksi Tidak Bisa Diproses<br>";
	}
}

function nb_format($e,$i=0,$proses='preview'){
	if($proses=='preview' or $proses=='excel'){
		$n = round($e,$i);
	}else{
		$n = round($e,$i);
	}
	return $n;
}
switch($proses){
    case'preview':
         echo $tab;
	break;
    ######EXCEL
	case 'excel':
		$stream=$tab;;
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];
		$tglSkrg=date("Ymd");
		$nop_="daftar_premi_pemanen";
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