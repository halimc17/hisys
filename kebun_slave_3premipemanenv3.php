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

if($prd>'2021-11'){
	exit("Warning : Silahkan menggunakan menu : Kebun - Proses - Premi Pemanen (SPB).");
}

$rangetgl    =rangeTanggal($tgl1, $tgl2);
$rangetglpks =rangeTanggal($tgl1, $tgl2);
$rangetglspb =rangeTanggal($tgl1, $tgl2);

@$nmkar      =makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
@$nikkar     =makeOption($dbname,'datakaryawan','karyawanid,nik');
$optTopografi=makeOption($dbname,'setup_topografi','topografi,keterangan');
$optkodetopo =makeOption($dbname,'setup_blok','kodeorg,topografi');
$nmorg       =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

#Cek Periode gaji
$str="select max(sudahproses) as prd from ".$dbname.".sdm_5periodegaji where kodeorg='".$unit."' and periode='".$prd."'";
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
$w=""; $kgwb=$kgwblalu=$jjgwb=$tglpnn=$ttlkgkrm=$kgwbperblok=$nospbperblok=$tglspbperblok=$jlhblokpertk=$blokspbtanggal=array();
$w=" and tanggalpanen between '".$tgl1."' and '".$tgl2."'";
$str="select * from ".$dbname.".kebun_spb_vw where	1=1 ".$w." and blok like '".$afd."%' and blok like '".$unit."%' order by tahuntanam asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$kgpksplusbrd[$bar['tahuntanam']]+=$bar['kgwbnetto'];
	@$kgkgbrondol[$bar['tahuntanam']]+=$bar['brondolan'];
	@$ttwb[$bar['tahuntanam']]=$bar['tahuntanam'];
	@$jjgkirim[$bar['tanggalpanen']]+=$bar['jjg'];
	
	/* if(substr($bar['tanggalpanen'],0,7)!=$prd){
		// @$kgwblalu[$bar['tahuntanam']][$bar['tanggalpanen']]+=$bar['kgwbnetto'];
		//@$kgwblalu[$bar['tahuntanam']][$bar['tanggalpanen']]+=($bar['kgwbnetto']-$bar['brondolan']);
		$tglpnn[$bar['tanggalpanen']]=$bar['tanggalpanen'];
	}else{
		// @$kgwb[$bar['tahuntanam']][$bar['tanggalpanen']]+=$bar['kgwbnetto'];
	} */
	if($kgbrondol=='1'){
		#Kg Sebelum Potong Brondolan
		@$kgwb[$bar['tahuntanam']][$bar['tanggalpanen']][$bar['blok']]+=($bar['kgwbnetto']);			
		@$kgwbperblok[$bar['blok']][$bar['tanggalpanen']]+=($bar['kgwbnetto']);
		@$ttlkgkrm[$bar['tanggalpanen']]+=($bar['kgwbnetto']);
		@$kgpks[$bar['tahuntanam']]+=($bar['kgwbnetto']);
	}else{		
		@$kgpks[$bar['tahuntanam']]+=($bar['kgwbnetto']-$bar['brondolan']);
		@$ttlkgkrm[$bar['tanggalpanen']]+=($bar['kgwbnetto']-$bar['brondolan']);
		@$kgwb[$bar['tahuntanam']][$bar['tanggalpanen']][$bar['blok']]+=($bar['kgwbnetto']-$bar['brondolan']);			
		@$kgwbperblok[$bar['blok']][$bar['tanggalpanen']]+=($bar['kgwbnetto']-$bar['brondolan']);
	}
	
	@$ttlkgpksnetto[$bar['tahuntanam']]+=($bar['kgwbnetto']-$bar['brondolan']);
	
	@$jjgwb[$bar['tahuntanam']][$bar['tanggalpanen']]+=$bar['jjg'];		
	
	@$blokspb[$bar['blok']]=$bar['blok'];
	
	$blokspbtanggal[$bar['blok']][$bar['tanggalpanen']]=$bar['blok'];
	$nospbperblok[$bar['blok']][$bar['tanggalpanen']]=$bar['nospb'];
	$tglspbperblok[$bar['blok']][$bar['tanggalpanen']]=$bar['tanggal'];
}


$w="";
if(count($tglpnn)!=0){
	$w=" and (b.tanggal between '".$tgl1."' and '".$tgl2."' or b.tanggal in ('".implode("','",$tglpnn)."'))";
}else{
	$w=" and b.tanggal between '".$tgl1."' and '".$tgl2."'";
}
$str="select a.*,b.tanggal, b.nikmandor, b.nikasisten,c.tahuntanam,c.topografi from ".$dbname.".kebun_prestasi a  
	left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi  
	left join ".$dbname.".setup_blok c on a.kodeorg=c.kodeorg  
	where	1=1 ".$w." and b.kodeorg='".$unit."' and a.kodeorg like '".$afd."%' 
	and keterangan!='KONTAN' and b.noreferensi='' and b.tipetransaksi='PNN' order by a.tahuntanam asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$list=$jjgkegpnn=$blokkegpanen=array();
while($bar=$res->fetch()){
	$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
	$karyawanid[$bar['nik']]=$bar['nik'];
	@$list[$bar['tanggal']][$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['nik']][$bar['kodeorg']]=$bar['kodeorg'];
	@$hk[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['nik']][$bar['tanggal']][$bar['kodeorg']]+=$bar['hkpanenperhari'];
	@$jjg[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['nik']][$bar['tanggal']][$bar['kodeorg']]+=$bar['hasilkerja'];
	@$kgbrd[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['nik']][$bar['tanggal']][$bar['kodeorg']]+=$bar['brondolan'];
	@$denda[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['nik']][$bar['tanggal']][$bar['kodeorg']]+=$bar['rupiahpenalty'];
	
	@$jlhblokpertk[$bar['nik']][$bar['tanggal']]+=1;
	
	@$jjgpertt[$bar['tahuntanam']][$bar['tanggal']][$bar['kodeorg']]+=$bar['hasilkerja'];
	@$kodetopo[$bar['kodeorg']]=$bar['topografi'];
	
	@$jjgkegpnn[$bar['tanggal']]+=$bar['hasilkerja'];
	
	$blokkegpanen[$bar['kodeorg']][$bar['tanggal']]=$bar['kodeorg'];
}

// echo"<pre>";
// print_r($jlhblokpertk);
// echo"</pre>";


if(count($list)==0){
	exit("warning : data kosong.");
}
if($kodetopo==''){
	exit("warning : Kode topografi masih ada yang kosong, silahkan cek melalui menu Setup - Blok.");
}

#jjg panen
$w="";
$w=" and tanggal between '".$tgl1."' and '".$tgl2."'";
$str="select * from ".$dbname.".kebun_rekappnn where 1=1 ".$w." and divisi like '".$afd."%' and divisi like '".$unit."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$jjgpanen[$bar['tanggal']]+=$bar['jjgpanen'];

}

$arrtopo=array();
$jlhtopo=array();

#ambil basis wb
$str="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$unit."' and tahun='".$prd."' and posting='1'";
$jlhbss = count(fetchdata($str));
if($jlhbss==0){
	$str="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$unit."' and tahun<='".$prd."'  and posting='1' order by tahun asc";	
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$basiskg[$bar['tahuntanam']][$bar['topografi']]=$bar['basis'];
	$rplb1[$bar['tahuntanam']][$bar['topografi']]=$bar['premilebihbasis'];
	$rpbrd[$bar['tahuntanam']][$bar['topografi']]=$bar['premibrondolan'];
	$rptopo[$bar['tahuntanam']][$bar['topografi']]=$bar['premitopografi'];
	$arrtopo[$bar['topografi']]=$bar['topografi'];
	$jlhtopo[$bar['topografi']]=$bar['topografi'];
	
	$blnbasispanen=$bar['tahun'];
}

#cek transaksi spb belum posting
$row='';
$w="";
$w=" and tanggalpanen between '".$tgl1."' and '".$tgl2."'";
$str="select distinct(tanggal) from ".$dbname.".kebun_spbht a left join ".$dbname.".kebun_spbdt b on a.nospb=b.nospb 
	where 1=1 ".$w." and a.nospb like '%".$afd."%' and a.kodeorg='".$unit."' and a.posting='0'";
$res=fetchdata($str);
$row=count($res);
$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$ttp->setFetchMode(PDO::FETCH_ASSOC);
while ($dspb = $ttp->fetch()) {
	$tglx[substr($dspb['tanggal'],8,2)]=substr($dspb['tanggal'],8,2);
}

#cek keg panen blm posting
$w="";
$w=" and a.tanggal between '".$tgl1."' and '".$tgl2."'";
$str="select distinct(notransaksi) as notransaksi, a.tanggal from ".$dbname.".kebun_prestasi_vs_hk a  
where 1=1 ".$w." and unit='".$unit."' and kodeorg like '".$afd."%' and jurnal='0' and noreferensi=''";
$res=fetchdata($str);
$rowp=count($res);
$ttp=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$ttp->setFetchMode(PDO::FETCH_ASSOC);
while ($dspbx = $ttp->fetch()) {
	$tglxp[substr($dspbx['tanggal'],8,2)]=substr($dspbx['tanggal'],8,2);
}

if(count($kgwblalu)==0 and count($kgwb)==0){
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

if($proses != 'excel'){
	$tglTemp=str_replace('-','',$prd);
	$notr = $tglTemp."/".$afd."/PNN02/".$nomor;
	$tab.="<table><td style=display:none><b>".$_SESSION['lang']['notransaksi']."</b></td><td style=display:none><b>:</b></td>
					 <td style=display:none><input disabled class=myinputtext style=width:170px value='".$notr."' id=notransaksi></td>
					 <td>Untuk <b>menyimpan</b> silahkan click tombol <b>Proses</b> di bawah, tombol ditampilkan jika restan NOL, Transaksi Kegiatan Panen dan Transaksi SPB sudah diposting semua.</td><td></td>
					 </tr>";
	if($row!=''){
		$tab.="<tr><td colspan=10><font color=red>Info : Ada transaksi <b>SPB</b> yang belum di posting sebanyak = ".$row." transaksi, tanggal : ".implode(",",$tglx)." ".substr(tanggalbulan($prd."-01"),3,99)."</font></td></tr>";
	}
	if($rowp!=''){
		$tab.="<tr><td colspan=10><font color=red>Info : Ada transaksi <b>Kegiatan Panen</b> yang belum di posting sebanyak = ".$rowp." transaksi, tanggal : ".implode(",",$tglxp)." ".substr(tanggalbulan($prd."-01"),3,99)."</font></td></tr>";
	}	
	$tab.="</table>";
	
	
	$tab.="<table class=sortable cellspacing=1 cellpadding=5>";
	$tab.="<thead><tr class=rowheader>";
	$tab.="<td align=center rowspan=2>No</td>";
	$tab.="<td align=center rowspan=2>Ket</td>";
	$tab.="<td align=center colspan=".count($rangetgl).">Kg PKS Berdasarkan Tgl PKS</td>";
	$tab.="<td align=center rowspan=2>Total</td>";
	$tab.="</tr><tr>";
	foreach($rangetgl as $rtgl){
		$tab.="<td align=center>".substr($rtgl,8,2)."</td>";
	}
	$tab.="</tr>";
	$tab.="</thead>";
	$kgpksbytglspb=array();
	$w=" and tanggal between '".$tgl1."' and '".$tgl2."'";
	$str="select * from ".$dbname.".kebun_spb_vw where	1=1 ".$w." and blok like '".$afd."%' and divisi like '".$unit."%' order by tahuntanam asc";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		@$kgpksbytglspb[$bar['tanggal']]['kgwb']+=$bar['kgwb'];
		@$kgpksbytglspb[$bar['tanggal']]['kgnet']+=$bar['kgwbnetto'];
		
		$resspb=0;
		$strspb="select * from ".$dbname.".kebun_spb_vw where blok like '".$afd."%' and divisi like '".$unit."%' and tanggal = '".$bar['tanggal']."' group by nospb";
		$resspb = fetchdata($strspb);
		
		$rangetglspb[$bar['tanggal']]=$bar['tanggal'];
		@$kgpksbytglspb[$bar['tanggal']]['rit']=count($resspb);
	}
	
	$tab.="<tbody>";
	
	$tab.="<tr class=rowcontent>";
	$tab.="<td colspan=".(count($rangetgl)+3)." style=color:green;background-color:#AED6F1>Sumber : <b>Timbangan Pabrik</b></td>";
	$tab.="</tr>";
	
	$kgpksbytglspbpks=array(); $n="";
	$listmill=array();
	
	if($afd!=''){		
		$n=" and divcode like '".$afd."%'";
	}
	$w=" and substr(tanggal,1,10) between '".$tgl1."' and '".$tgl2."'";
	$str="select * from ".$dbname.".pabrik_timbangan where	1=1 ".$w." ".$n." and kodeorg like '".$unit."%' and kodebarang='40000003' and nospb!=''";
	$res=fetchdata($str);
	foreach($res as $bar){
		@$kgpksbytglspbpks[$bar['millcode']][substr($bar['tanggal'],0,10)]['kgwb']+=$bar['beratbersih'];
		@$kgpksbytglspbpks[$bar['millcode']][substr($bar['tanggal'],0,10)]['kgsort']+=$bar['kgpotsortasi'];
		@$kgpksbytglspbpks[$bar['millcode']][substr($bar['tanggal'],0,10)]['kgnet']+=($bar['beratbersih']-$bar['kgpotsortasi']);
		
		@$kgpksbytglspbpks[$bar['millcode']][substr($bar['tanggal'],0,10)]['rit']+=1;
		
		$listmill[$bar['millcode']]=$bar['millcode'];
	}
	
	
	$arrdata=array('kgwb'=>'Kg PKS Sebelum Sortasi','kgsort'=>'Kg Sortasi','kgnet'=>'Kg PKS Setelah Sortasi','rit'=>'Jumlah Rit');
	$no="";
	foreach($listmill as $mill){
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center></td>";
		$tab.="<td align=left colspan=".(count($rangetgl)+2).">".$mill."</td>";
		$tab.="</tr>";
		foreach($arrdata as $iddata => $valdata){			
			$no++;			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$valdata."</td>";
			$ttlkgpksbytglspbpks=array();
			foreach($rangetgl as $rtgl){
				$i="";
				$i="style=cursor:pointer;color:blue; onclick=\"getdetailkgpks('".tanggalnormal($rtgl)."','".$afd."','".$unit."','".$mill."','".$iddata."','event')\" title=\"Click untuk melihat detail data\"";
				
				$tab.="<td align=right ".$i.">".number_format($kgpksbytglspbpks[$mill][$rtgl][$iddata])."</td>";
				@$ttlkgpksbytglspbpks[$iddata]+=$kgpksbytglspbpks[$mill][$rtgl][$iddata];
			}
			
			$tab.="<td align=right>".@number_format($ttlkgpksbytglspbpks[$iddata])."</td>";
			$tab.="</tr>";
		}
	}
	$tab.="</tbody>";
	$tab.="</table>";
	$tab.="<div style=clear:both>Note : Total Kg diatas berdasarkan tanggal Timbang PKS</div>";
	$tab.="<hr>";


	$tab.="<table class=sortable cellspacing=1 cellpadding=5>";
	$tab.="<thead><tr class=rowheader>";
	$tab.="<td align=center rowspan=2>No</td>";
	$tab.="<td align=center rowspan=2>Ket</td>";
	$tab.="<td align=center colspan=".count($rangetgl).">Kg PKS Berdasarkan Tgl SPB</td>";
	$tab.="<td align=center rowspan=2>Total</td>";
	$tab.="</tr><tr>";
	foreach($rangetgl as $rtgl){
		$tab.="<td align=center>".substr($rtgl,8,2)."</td>";
	}
	$tab.="</tr>";
	$tab.="</thead>";
	
	$no="";
	$tab.="<tr class=rowcontent>";
	$tab.="<td colspan=".(count($rangetgl)+3)." style=color:green;background-color:#AED6F1>Sumber : <b>Kebun - Trans - Surat Pengantar Buah</b></td>";
	$tab.="</tr>";
	foreach($arrdata as $iddata => $valdata){
		$no++;			
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=left>".$valdata."</td>";
		$ttlkgpksbytglspb=array();
		foreach($rangetgl as $rtgl){
			$i="";
			
			if($afd=='%%'){
				$afd=$unit;
			}else{
				$afd=$afd;
			}
			$i="style=cursor:pointer;color:blue; onclick=\"getdetailkg('".tanggalnormal($rtgl)."','".tanggalnormal($rtgl)."','','".$afd."','event','".$iddata."','tglspb')\" title=\"Click untuk melihat detail data\"";
			@$kgpksbytglspb[$rtgl]['kgsort']=$kgpksbytglspb[$rtgl]['kgwb']-$kgpksbytglspb[$rtgl]['kgnet'];
			
			$tab.="<td align=right ".$i." >".@number_format($kgpksbytglspb[$rtgl][$iddata])."</td>";
			@$ttlkgpksbytglspb[$iddata]+=$kgpksbytglspb[$rtgl][$iddata];
		}
		$tab.="<td align=right>".@number_format($ttlkgpksbytglspb[$iddata])."</td>";
		$tab.="</tr>";

	}			
	
	$tab.="</tbody>";
	$tab.="</table>";
	$tab.="<div style=clear:both>Note : Total Kg diatas berdasarkan Tanggal SPB</div>";
	
	$tab.="<hr>";
	
	if($jlhbss==0){		
		$tab.="<div style=clear:both><font color=orange><b>Info : Harga ongkos panen untuk bulan : ".$prd." belum ada, maka ongkos panen diambil dari periode terakhir : ".$blnbasispanen.", untuk memperbaharui ongkos panen melalui menu : Kebun - Setup - Ongkos Panen</b></font></div>";
	}
	
	$tab.="<table class=sortable cellspacing=1 cellpadding=5>";
	$tab.="<thead><tr class=rowheader>";
	$tab.="<td align=center width=50px rowspan=2>".($_SESSION['lang']['tahuntanam'])."</td>
			  <td align=center width=75px rowspan=2>Kg PKS Sebelum Pot Brondol</td>
			  <td align=center width=75px rowspan=2>Kg Brondol</td>
			  <td align=center width=75px rowspan=2>Kg PKS Netto</td>";
	$jt=0;
	foreach($arrtopo as $topografi){
		$jt++;
		$tab.="<td align=center colspan=4>".$optTopografi[$topografi]."</td>";
	}

	$tab.="</tr><tr>";
	foreach($arrtopo as $topografi){
		$tab.="<td align=center width=50px>Basis Kg</td>";
		$tab.="<td align=center width=50px>Premi Lb Basis Rp/Kg</td>";
		$tab.="<td align=center width=50px>".$_SESSION['lang']['brondol']." Rp / Kg</td>";
		$tab.="<td align=center width=50px>Premi Kehadiran ".$optTopografi[$topografi]."</td>";		
	}
	$tab.="</tr>";
	$tab.="</thead>";
	foreach(@$ttwb as $ttkgwb){
		$i="";
		$i="style=cursor:pointer;color:blue; onclick=\"getdetailkg('".tanggalnormal($tgl1)."','".tanggalnormal($tgl2)."','".$ttkgwb."','".$afd."','event',this.id)\" title=\"Click untuk melihat detail data\"";
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$ttkgwb."</td>";
		$tab.="<td align=right id=sblmbrd ".$i.">".@number_format($kgpksplusbrd[$ttkgwb])."</td>";
		$tab.="<td align=right id=potbrd ".$i.">".@number_format($kgkgbrondol[$ttkgwb])."</td>";
		$tab.="<td align=right id=stlhbrd ".$i.">".@number_format($ttlkgpksnetto[$ttkgwb])."</td>";
		foreach($arrtopo as $topografi){
			$tab.="<td align=right>".number_format(@$basiskg[$ttkgwb][$topografi],2)."</td>";
			$tab.="<td align=right>".number_format(@$rplb1[$ttkgwb][$topografi],2)."</td>";
			$tab.="<td align=right>".number_format(@$rpbrd[$ttkgwb][$topografi],2)."</td>";
			$tab.="<td align=right>".number_format(@$rptopo[$ttkgwb][$topografi],2)."</td>";
		}
		
		@$ttlkgpksplusbrd+=$kgpksplusbrd[$ttkgwb];
		@$ttlkgkgbrondol+=$kgkgbrondol[$ttkgwb];
		@$ttlkgwb+=$ttlkgpksnetto[$ttkgwb];
		$tab.="</tr>";			
	}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>Total</td>";
		$csbl="";
		$csbb="";
		if($kgbrondol=='1'){$csbl="style=background-color:cyan;";}
		if($kgbrondol=='2'){$csbb="style=background-color:cyan;";}
		$tab.="<td align=right ".$csbl.">".@number_format($ttlkgpksplusbrd)."</td>";
		$tab.="<td align=right>".@number_format($ttlkgkgbrondol)."</td>";
		$tab.="<td align=right ".$csbb.">".@number_format($ttlkgwb)."</td>";
		$tab.="<td align=right colspan=".(($jt) * 4) ."></td>";
		$tab.="</tr>";
	$tab.="</table><div style=clear:both></div><hr>";
		
		array_multisort($blokspb,SORT_ASC);
		
		$tab.="<table class=sortable cellspacing=1 cellpadding=5>";
		$tab.="<thead><tr class=rowheader>";
		$tab.="<td align=center rowspan=2>No</td>";
		$tab.="<td align=center rowspan=2>Blok</td>";
		$tab.="<td align=center width=50px rowspan=2>Tahun Tanam</td>";
		$tab.="<td align=center colspan=".count($rangetgl).">Kg PKS Berdasarkan Tgl Panen</td>";
		$tab.="<td align=center rowspan=2>Total</td>";
		$tab.="</tr><tr>";
		foreach($rangetgl as $rtgl){
			$tab.="<td align=center>".substr($rtgl,8,2)."</td>";
		}
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";
		$no="";
		
		$ttlkgwbperblok=array();
		$ttlkgwbperkdblok=array();
		$grandttlkgwb=0;
		
		foreach($blokspb as $blkspb){
			$optthntnm  =makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$blkspb."'");
			$no++;			
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=center>".getNamaOrg($blkspb)."</td>";
			$tab.="<td align=center>".$optthntnm[$blkspb]."</td>";
			foreach($rangetgl as $rtgl){
				
				$tit="";
				if($blokkegpanen[$blkspb][$rtgl]!=$blokspbtanggal[$blkspb][$rtgl]){
					$tit="style=cursor:pointer;color:blue;background-color:red; title='Blok tidak ada dikegiatan panen.'";
				}else{					
					$tit="style=cursor:pointer;color:blue;";
				}
				$tit.=" onclick=\"previewdata('".$blkspb."','".tanggalnormal($rtgl)."','event')\" title=\"".@$nospbperblok[$blkspb][$rtgl]."\"  ";
				$tab.="<td align=right ".$tit." >".@number_format($kgwbperblok[$blkspb][$rtgl])."</td>";
				@$ttlkgwbperblok[$rtgl]+=$kgwbperblok[$blkspb][$rtgl];
				@$ttlkgwbperkdblok[$blkspb]+=$kgwbperblok[$blkspb][$rtgl];
				@$grandttlkgwb+=$kgwbperblok[$blkspb][$rtgl];
			}
			$tab.="<td align=right>".@number_format($ttlkgwbperkdblok[$blkspb])."</td>";
			$tab.="</tr>";
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=3>TOTAL</td>";
			foreach($rangetgl as $rtgl){
				$tab.="<td align=right>".@number_format($ttlkgwbperblok[$rtgl])."</td>";
			}
			$tab.="<td align=right style=background-color:cyan;>".@number_format($grandttlkgwb)."</td>";
		$tab.="</tr>";
			
		$tab.="</tbody>";
		$tab.="</table>";
			
	$tab.="<br><div style=clear:both>Note : Total Kg diatas berdasarkan Tanggal Panen bukan berdasarkan Tanggal SPB</div><hr>";
	
	#$tab.="<div style=clear:both>Note : Total Kg berdasarkan Tanggal SPB</div>";
		
	
	$tab.="<table class=sortable cellspacing=1  cellpadding=5>";
	$tab.="<thead><tr class=rowheader>";
	$tab.="<td align=center rowspan=2 colspan=2>Ket</td>";
	$tab.="<td align=center colspan=".count($rangetgl).">Tanggal Panen</td>";
	$tab.="</tr><tr>";
	foreach($rangetgl as $rtgl){
		$tab.="<td align=center>".substr($rtgl,8,2)."</td>";
		$tglakhir = $rtgl;
	}
	$tab.="</tr>";
	$tab.="</thead>";
	$ttlrestant=0;
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>1</td>";
	$tab.="<td align=left>Janjang Panen (Rekap Panen)</td>";
	foreach($rangetgl as $rtgl){
		$i="";
		$i="style=cursor:pointer;color:blue; onclick=\"prevrekappnn('".$afd."','".tanggalnormal($rtgl)."','".$unit."')\" title=\"Detail Rekap Panen Tanggal : ".$rtgl."\"";
		$tab.="<td align=right ".$i.">".@$jjgpanen[$rtgl]."</td>";
	}
	
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>2</td>";
	$tab.="<td align=left>Janjang Panen (Kegiatan Panen)</td>";
	$cekrpnnvskpnn=0;
	foreach($rangetgl as $rtgl){
		if(@$jjgpanen[$rtgl]!=@$jjgkegpnn[$rtgl]){
			$col="style=cursor:pointer;background-color:red;";
			$cekrpnnvskpnn+=1;
		}else{
			$col="";
		}
		$i="";
		$i="style=cursor:pointer;color:blue; onclick=\"prevdata('".$afd."','".tanggalnormal($rtgl)."','kegpnn','".$unit."')\" title=\"Detail Kegiatan Panen Tanggal : ".$rtgl."\"";
		
		$tab.="<td align=right ".$col." ".$i." >".@$jjgkegpnn[$rtgl]."</td>";
	}
	
	
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>3</td>";
	$tab.="<td align=left>Janjang Kirim (SPB)</td>";
	foreach($rangetgl as $rtgl){
		$i="";
		$i="style=cursor:pointer;color:blue; onclick=\"prevdata('".$afd."','".tanggalnormal($rtgl)."','jjgspb','".$unit."')\" title=\"Detail Janjang Kirim Tanggal : ".$rtgl."\"";
		
		$tab.="<td align=right ".$i.">".@$jjgkirim[$rtgl]."</td>";
	}
	
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>4</td>";
	$tab.="<td align=left>Kg PKS (SPB)</td>";
	foreach($rangetgl as $rtgl){
		$i="";
		$i="style=cursor:pointer;color:blue; onclick=\"prevdata('".$afd."','".tanggalnormal($rtgl)."','jjgspb','".$unit."')\" title=\"Detail Kg PKS Tanggal : ".$rtgl."\"";
		
		$tab.="<td align=right ".$i.">".@hidezerodecimal($ttlkgkrm[$rtgl],0)."</td>";
	}
	
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>5</td>";
	$tab.="<td align=left>Restan (1-3)</td>";
	foreach($rangetgl as $rtgl){
		@$restant = $jjgpanen[$rtgl]-$jjgkirim[$rtgl];
		if($restant==0){
			$restant=''; 
			$i='';
		} else { 
			// $restant=number_format($restant);
			$i="style='font-weight:bold;color:red'";
		}
			
		// $tab.="<td align=right ".$i.">".$restant."</td>";
		$tab.="<td align=right ".$i.">".$restant."</td>";
		
		$ttlrestant+=$restant;
	}			
	$tab.="</tr>";
	$tab.="</table>";
	if($cekrpnnvskpnn!=0){
		$tab.="<br><span style=color:red>Info : Ada jumlah janjang antara Rekap Panen vs Kegiatan Panen tidak sama, ini akan berakibat pada Total Kg per karyawan (lihat Total Kg paling bawah) tidak sama dengan dengan Total Kg PKS</span>";
	}
	
	if($ttlrestant!=0){
		$tab.="<br><span style=color:red>Info : Masih ada TBS yang belum terangkut (restant) sebanyak : <b>".number_format($ttlrestant)."</b> Jjg, proses tidak bisa dilanjutkan, proses hanya bisa dilanjutkan jika restant sudah NOL.</span>";
	}
	$tab.="<hr>";
	
	$str = "select a.*, b.tanggal from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.kodekegiatan='611010206' and tanggal between '".$tgl1."' and '".$tgl2."' and b.kodeorg='".$unit."' and a.kodeorg like '".$afd."%' and b.jurnal in ('1','0')";
	$res = fetchdata($str);
	foreach($res as $val){
		$dtbrd[$val['kodeorg']]=$val['kodeorg'];
		$databrd[$val['kodeorg']][$val['tanggal']]+=$val['hasilkerja'];
	}
	$tab.="<br><span>Data transaksi kutib brondolan di BKM Pemeliharaan</span>";
	$tab.="<table class=sortable cellspacing=1 cellpadding=5>";
	$tab.="<thead><tr class=rowheader>";
	$tab.="<td align=center rowspan=2>No</td>";
	$tab.="<td align=center rowspan=2>Blok</td>";
	$tab.="<td align=center colspan=".count($rangetgl).">Tanggal</td>";
	$tab.="<td align=center rowspan=2>Total</td>";
	$tab.="</tr><tr>";
	foreach($rangetgl as $rtgl){
		$tab.="<td align=center>".substr($rtgl,8,2)."</td>";
	}
	$tab.="</tr>";
	$tab.="</thead>";
	$no=0;
	foreach($dtbrd as $blokbrd){
		$no++;
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=center>".getNamaOrg($blokbrd)."</td>";
		foreach($rangetgl as $rtgl){
			$i="";
			$i="style=cursor:pointer;color:blue; onclick=\"prevbrd('".$blokbrd."','".tanggalnormal($rtgl)."','brd')\" title=\"Detail Transaksi BKM\"";
		
			$tab.="<td align=right ".$i.">".$databrd[$blokbrd][$rtgl]."</td>";			
			$ttlbrd[$blokbrd]+=$databrd[$blokbrd][$rtgl];
			$gttlbrd[$rtgl]+=$databrd[$blokbrd][$rtgl];
			$gbrd+=$databrd[$blokbrd][$rtgl];
		}
		$tab.="<td align=right>".$ttlbrd[$blokbrd]."</td>";			
	}
	$tab.="</tr>";
	
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center colspan=2>TOTAL</td>";
	foreach($rangetgl as $rtgl){
		$tab.="<td align=right>".$gttlbrd[$rtgl]."</td>";			
	}
	$tab.="<td align=right>".$gbrd."</td>";			
	$tab.="</tr>";
		
	$tab.="</table>";
	$tab.="<hr>";
}#tutup if proses != excel
	
	if ($proses == 'excel') {
		$tab.="<table class=sortable cellspacing=1 border=1>";
	} else 	{
		$tab.="<label>Pada saat kolom Potongan Brondolan diisi / berubah maka harus dilakukan Rekalkulasi untuk menghitung ulang premi Tambahan Panen (Tombol akan muncul di bawah)</label>";
		$tab.="<div class=table-scroll><table class=sortable cellspacing=1>";
	}
	$tab.="<thead>";
	$tab.="<tr class=rowheader>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>";
		$tab.="<th align=center rowspan=2>Tanggal Panen</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['mandor']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['kerani']."</th>";
		$tab.="<th align=center rowspan=2 width=50px>".$_SESSION['lang']['tahuntanam']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['nik2']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['topografi']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['blok']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['jjg']."</th>";
		$tab.="<th align=center rowspan=2>Total Kg</th>";
		$tab.="<th align=center rowspan=2>Potongan<br>Brondolan<br>(Kg)</th>";
		$tab.="<th align=center rowspan=2>Total Kg<br>Netto</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['basic']." Kg</th>";
		$tab.="<th align=center colspan=3>Lebih Basis</th>";
		$tab.="<th align=center colspan=3>".$_SESSION['lang']['brondol']."</th>";
		$tab.="<th align=center rowspan=2>Kehadiran</th>";
		$tab.="<th align=center rowspan=2>Tambahan</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['denda']."</th>";
		$tab.="<th align=center rowspan=2>".$_SESSION['lang']['total']."</th>";
	$tab.="</tr>";
	$tab.="<tr>";
		$tab.="<th align=center>".$_SESSION['lang']['kg']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['harga']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['rp']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['kg']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['harga']."</th>";
		$tab.="<th align=center>".$_SESSION['lang']['rp']."</th>";
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
	$bjr=$kgkirim=0;
	foreach($list as $tglpnn => $key ){
		foreach($key as $mdr => $key2){
			foreach($key2 as $krn => $key3){
				foreach($key3 as $tt => $key4){
					foreach($key4 as $kary => $key5){
						foreach($key5 as $kdblok){
							$rppotbrdlama=0;
							if($recal=='recal'){
								foreach($_SESSION['temppnn'] as $val){
									if($val['mandor']==$mdr and $val['kerani']==$krn and $val['periode']==$prd and $val['notransaksi']==$notr and $val['tanggal']==$tglpnn and $val['blok']==$kdblok and $val['karyawanid']==$kary){
										$rppotbrdlama=$val['potbrd'];
									}
								}
							}else{								
								$w=" and kerani='".$krn."' and periode='".$prd."' and mandor='".$mdr."' and karyawanid='".$kary."' and divisi='".$afd."' and tahuntanam='".$tt."' and notransaksi='".$notr."' and tanggalpanen='".$tglpnn."' and blok='".$kdblok."'";
								$strbrd = "select * from ".$dbname.".kebun_3premipemanen where	1=1 ".$w."";
								$resbrd = fetchdata($strbrd);
								$rppotbrdlama=$resbrd[0]['potbrdkg'];
								if($rppotbrdlama>0){									
									$_SESSION['temppnn'][]=array(
										'mandor'     =>$mdr,
										'kerani'     =>$krn,
										'periode'    =>$prd,
										'notransaksi'=>$notr,
										'tanggal'    =>$tglpnn,
										'blok'       =>$kdblok,
										'karyawanid' =>$kary,
										'potbrd'     =>$rppotbrdlama
									);
								}
							}
							
							
							$bjr    =$kgwb[$tt][$tglpnn][$kdblok]/$jjgpertt[$tt][$tglpnn][$kdblok];
							$kgkirim=$bjr*$jjg[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok];
							
							$kgwgperkary[$tglpnn][$kary]+=$kgkirim-$rppotbrdlama;
						}
						
						$prerpkg=0;
						$sql = "select * from ".$dbname.".kebun_5basispanen where kodeorg='".$unit."' and batasbawah <= '".$kgwgperkary[$tglpnn][$kary]."' and batasatas >= '".$kgwgperkary[$tglpnn][$kary]."' and tanggalberlaku <='".$tglpnn."' order by batasatas desc limit 1";
						$req = fetchdata($sql);
						foreach($req as $baq){
							$prerpkg=$baq['harga'];
						}
						if($prerpkg>0){
							$rppretambahan[$tglpnn][$kary]+=$kgwgperkary[$tglpnn][$kary]*$prerpkg;
							$gtttttt+=$rppretambahan[$tglpnn][$kary];
						}
					}
				}	
			}
		}
	}	
	
	// echo "<pre>";
	// print_r($_SESSION['temppnn']);
	// echo "</pre>";
	
	// exit();
	
	
	
	
	$nokar=0;
	$color='';$bjr=$kgkirim=0;
	@$no=$bjr=$ttlrplb1=$ttlrplb2=0;
	$jlhblktk=$rptopoperkary=0;
	$sttljjg=$sttlkgkirim=array();
	foreach($list as $tglpnn => $key ){
		foreach($key as $mdr => $key2){
			foreach($key2 as $krn => $key3){
				foreach($key3 as $tt => $key4){
					foreach($key4 as $kary => $key5){
						foreach($key5 as $kdblok){
							if(@$basiskg[$tt][$optkodetopo[$kdblok]]==''){
								exit('Error : Basis panen tahun tanam '.$tt.', topografi '.$optTopografi[$optkodetopo[$kdblok]].' belum ada.');
							}
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
							@$bjr=$kgwb[$tt][$tglpnn][$kdblok]/$jjgpertt[$tt][$tglpnn][$kdblok];
							$kgkirim=$bjr*$jjg[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok];
							
							$i = "style=display:none";
							
							
							$notr2 = str_replace("-","",$tglpnn)."/".$afd."/PNN02/".$nomor;
							$tab.="<tr class=rowcontent ".$color." id=row".$no.">";	
							$tab.="<td align=center style=display:none><input disabled class=myinputtext style=width:160px;display:none value='".$notr2."' id=notransaksi_".$no."></td>";
							$tab.="<td align=center>".$nokar."</td>";
							$tab.="<td id=tglpnn_".$no." align=center>".$tglpnn."</td>";
							if($proses!='excel'){
								$tab.="<td id=rowmdr_".$no." ".$i.">".$mdr."</td>";
								$tab.="<td id=rowkrn_".$no." ".$i.">".$krn."</td>";
								$tab.="<td id=rowkary_".$no." ".$i.">".$kary."</td>";
								$tab.="<td id=topografi_".$no." ".$i.">".$kdblok."</td>";
								$tab.="<td hidden id=rowblok_".$no.">".$kdblok."</td>";
							}
							
							$simpan[$no]['tglpnn']=$tglpnn;
							$simpan[$no]['mdr']=$mdr;
							$simpan[$no]['krn']=$krn;
							$simpan[$no]['kary']=$kary;
							$simpan[$no]['topografi']=$kdblok;
							$simpan[$no]['blok']=$kdblok;
							$simpan[$no]['tt']=$tt;
							$simpan[$no]['jjg']=nb_format($jjg[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]);
							$simpan[$no]['kgbruto']=nb_format($kgkirim,2);
							
							
							
							$tab.="<td>".@$nmkar[$mdr]."</td>";
							$tab.="<td>".@$nmkar[$krn]."</td>";
							$tab.="<td id=rowtt_".$no." align=center>".$tt."</td>";
							$tab.="<td>".@$nikkar[$kary]."</td><td>".@$nmkar[$kary]."</td>";
							$tab.="<td>".$optTopografi[$optkodetopo[$kdblok]]."</td>";
							$tab.="<td>".$nmorg[$kdblok]."</td>";
							$tab.="<td id=rowjjg_".$no." align=right>".nb_format($jjg[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok])."</td>";
							$n="";
							if($kgkirim==0){
								$n="style='background-color:red' title=\"Janjang belum terkirim.\"";
							}
							
							$tab.="<td id=rowkgbruto_".$no." ".$n." align=right>".nb_format($kgkirim,2)."</td>";
							if($proses!='excel'){
								$rppotbrdlama=$potbrdkg=0;
								if($recal=='recal'){
									foreach($_SESSION['temppnn'] as $val){
										if($val['mandor']==$mdr and $val['kerani']==$krn and $val['periode']==$prd and $val['notransaksi']==$notr and $val['tanggal']==$tglpnn and $val['blok']==$kdblok and $val['karyawanid']==$kary){
											$potbrdkg = $val['potbrd'];
											//$rppotbrdlama=$val['potbrd']*$rpbrd[$tt][$optkodetopo[$kdblok]];
										}
									}
								}else{	
									$w=" and kerani='".$krn."' and periode='".$prd."' and mandor='".$mdr."' and karyawanid='".$kary."' and divisi='".$afd."' and tahuntanam='".$tt."' and notransaksi='".$notr."' and tanggalpanen='".$tglpnn."' and blok='".$kdblok."'";
									$strbrd="select * from ".$dbname.".kebun_3premipemanen where	1=1 ".$w."";
									$resbrd = fetchdata($strbrd);
									$potbrdkg = $resbrd[0]['potbrdkg'];
								}
								
								$tab.="<td ><input id=potbrd_".$no."  type=text value='".$potbrdkg."' class=myinputtextnumber style='width:60px' onkeyup=gethitungpremi(".$no."); onkeypress='return angka_doang(event)'></td>";
								
								$simpan[$no]['potbrd']=$potbrdkg;
							}else{
								$tab.="<td></td>";	
							}
							
							if($perpot=='1'){
								$tab.="<td id=rowkg_".$no." align=right>".nb_format($kgkirim-$potbrdkg,2)."</td>";
								$tab.="<td id=rowkgbss_".$no." align=right>".nb_format($basiskg[$tt][$optkodetopo[$kdblok]])."</td>";
								
								$kglb    = (($kgkirim-$potbrdkg) - $basiskg[$tt][$optkodetopo[$kdblok]]);
								$rplbbss = $kglb * $rplb1[$tt][$optkodetopo[$kdblok]];
								$ttlrpbrd= ($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]+$potbrdkg)*$rpbrd[$tt][$optkodetopo[$kdblok]];
								
								$tab.="<td id=rowkglb1_".$no." align=right>".nb_format($kglb,2)."</td>";
								$tab.="<td id=rowhargarplb1_".$no." align=right>".nb_format($rplb1[$tt][$optkodetopo[$kdblok]])."</td>";
								$tab.="<td id=rowrplb1_".$no." align=right>".nb_format($rplbbss)."</td>";
								$tab.="<td id=rowkgbrd_".$no." align=right>".@nb_format($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]+$potbrdkg)."</td>";
								$tab.="<td id=rowhargabrd_".$no." align=right>".nb_format($rpbrd[$tt][$optkodetopo[$kdblok]])."</td>";
								$tab.="<td id=rowrpbrd_".$no." align=right>".nb_format($ttlrpbrd)."</td>";
								
								$ttlkgkirimnet+=nb_format($kgkirim-$potbrdkg,2);
								$sttlkgkirimnet[$tglpnn]+=nb_format($kgkirim-$potbrdkg,2);
								
								$simpan[$no]['kgnet']      =nb_format($kgkirim-$potbrdkg,2);
								$simpan[$no]['kgbss']      =nb_format($basiskg[$tt][$optkodetopo[$kdblok]]);
								$simpan[$no]['kglb1']      =nb_format($kglb,2);
								$simpan[$no]['hargarplb1_']=nb_format($rplb1[$tt][$optkodetopo[$kdblok]]);
								$simpan[$no]['rplb1']      =nb_format($rplbbss);
								$simpan[$no]['kgbrd']      =nb_format($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]+$potbrdkg);
								$simpan[$no]['hargabrd']   =nb_format($rpbrd[$tt][$optkodetopo[$kdblok]]);
								$simpan[$no]['rpbrd']      =nb_format($ttlrpbrd);
								
							}elseif($perpot=='2'){
								$tab.="<td id=rowkg_".$no." align=right>".nb_format($kgkirim,2)."</td>";
								$tab.="<td id=rowkgbss_".$no." align=right>".nb_format($basiskg[$tt][$optkodetopo[$kdblok]])."</td>";
								
								$kglb    = (($kgkirim) - $basiskg[$tt][$optkodetopo[$kdblok]]);
								$rplbbss = $kglb * $rplb1[$tt][$optkodetopo[$kdblok]];
								$ttlrpbrd= ($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok])*$rpbrd[$tt][$optkodetopo[$kdblok]];
								
								$tab.="<td id=rowkglb1_".$no." align=right>".nb_format($kglb,2)."</td>";
								$tab.="<td id=rowhargarplb1_".$no." align=right>".nb_format($rplb1[$tt][$optkodetopo[$kdblok]])."</td>";
								$tab.="<td id=rowrplb1_".$no." align=right>".nb_format($rplbbss)."</td>";
								$tab.="<td id=rowkgbrd_".$no." align=right>".@nb_format($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok])."</td>";
								$tab.="<td id=rowhargabrd_".$no." align=right>".nb_format($rpbrd[$tt][$optkodetopo[$kdblok]])."</td>";
								$tab.="<td id=rowrpbrd_".$no." align=right>".nb_format($ttlrpbrd)."</td>";
								$rppotbrdlama=$potbrdkg*$rpbrd[$tt][$optkodetopo[$kdblok]];
								
								$ttlkgkirimnet+=nb_format($kgkirim,2);
								$sttlkgkirimnet[$tglpnn]+=nb_format($kgkirim,2);
								
								$simpan[$no]['kgnet']      =nb_format($kgkirim,2);
								$simpan[$no]['kgbss']      =nb_format($basiskg[$tt][$optkodetopo[$kdblok]]);
								$simpan[$no]['kglb1']      =nb_format($kglb,2);
								$simpan[$no]['hargarplb1_']=nb_format($rplb1[$tt][$optkodetopo[$kdblok]]);
								$simpan[$no]['rplb1']      =nb_format($rplbbss);
								$simpan[$no]['kgbrd']      =nb_format($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]);
								$simpan[$no]['hargabrd']   =nb_format($rpbrd[$tt][$optkodetopo[$kdblok]]);
								$simpan[$no]['rpbrd']      =nb_format($ttlrpbrd);

							}elseif($perpot=='3'){
								$tab.="<td id=rowkg_".$no." align=right>".nb_format($kgkirim-$potbrdkg,2)."</td>";
								$tab.="<td id=rowkgbss_".$no." align=right>".nb_format($basiskg[$tt][$optkodetopo[$kdblok]])."</td>";
								
								$kglb    = (($kgkirim-$potbrdkg) - $basiskg[$tt][$optkodetopo[$kdblok]]);
								$rplbbss = $kglb * $rplb1[$tt][$optkodetopo[$kdblok]];
								$ttlrpbrd= ($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok])*$rpbrd[$tt][$optkodetopo[$kdblok]];
								
								$tab.="<td id=rowkglb1_".$no." align=right>".nb_format($kglb,2)."</td>";
								$tab.="<td id=rowhargarplb1_".$no." align=right>".nb_format($rplb1[$tt][$optkodetopo[$kdblok]])."</td>";
								$tab.="<td id=rowrplb1_".$no." align=right>".nb_format($rplbbss)."</td>";
								$tab.="<td id=rowkgbrd_".$no." align=right>".@nb_format($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok])."</td>";
								$tab.="<td id=rowhargabrd_".$no." align=right>".nb_format($rpbrd[$tt][$optkodetopo[$kdblok]])."</td>";
								$tab.="<td id=rowrpbrd_".$no." align=right>".nb_format($ttlrpbrd)."</td>";
								
								$ttlkgkirimnet+=nb_format($kgkirim-$potbrdkg,2);
								$sttlkgkirimnet[$tglpnn]+=nb_format($kgkirim-$potbrdkg,2);
								
								$simpan[$no]['kgnet']      =nb_format($kgkirim-$potbrdkg,2);
								$simpan[$no]['kgbss']      =nb_format($basiskg[$tt][$optkodetopo[$kdblok]]);
								$simpan[$no]['kglb1']      =nb_format($kglb,2);
								$simpan[$no]['hargarplb1_']=nb_format($rplb1[$tt][$optkodetopo[$kdblok]]);
								$simpan[$no]['rplb1']      =nb_format($rplbbss);
								$simpan[$no]['kgbrd']      =nb_format($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]);
								$simpan[$no]['hargabrd']   =nb_format($rpbrd[$tt][$optkodetopo[$kdblok]]);
								$simpan[$no]['rpbrd']      =nb_format($ttlrpbrd);
							}
							
							

							$rptopoperkary=($rptopo[$tt][$optkodetopo[$kdblok]]/$jlhblokpertk[$kary][$tglpnn]);
							$tab.="<td id=rowtopo_".$no." align=right>".@nb_format($rptopoperkary)."</td>";
							
							$simpan[$no]['rptopo']=nb_format($rptopoperkary);
							
							$rppremiplus=(($kgkirim-$potbrdkg)/$kgwgperkary[$tglpnn][$kary])*$rppretambahan[$tglpnn][$kary];
							if($proses!='excel'){
								$tab.="<td hidden id=rowtambahold_".$no.">".nb_format($rppremiplus)."</td>";
							}
							$tab.="<td id=rowtambah_".$no." align=right>".@nb_format($rppremiplus)."</td>";
							
							$simpan[$no]['tambahold']=nb_format($rppremiplus);
							$simpan[$no]['tambah']=nb_format($rppremiplus);
							
							$denda[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]=$denda[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]+$rppotbrdlama;
							if($proses!='excel'){
								$tab.="<td hidden><input id=rowdendalama_".$no." value='".($denda[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]-$rppotbrdlama)."'></td>";
							}
							$tab.="<td id=rowdenda_".$no." align=right>".@nb_format($denda[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok])."</td>";
							
							$simpan[$no]['dendaold']=($denda[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]-$rppotbrdlama);
							$simpan[$no]['denda']=nb_format($denda[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]);
							
							$gt = nb_format($rppremiplus) + nb_format($rplbbss) + nb_format($ttlrpbrd) + nb_format($rptopoperkary) - nb_format($denda[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]);
							$gt=($gt<0?0:$gt);
							$tab.="<td align=right id=gtotal_".$no.">".@nb_format($gt)."</td>";
							$tab.="</tr>";
							
							$simpan[$no]['gtotal']=nb_format($gt);
							
							@$gtbrd+=nb_format($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]);
							@$sgtbrd[$tglpnn]+=nb_format($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]);
							@$gtrpbrd+=nb_format($ttlrpbrd);
							@$sgtrpbrd[$tglpnn]+=nb_format($ttlrpbrd);
							@$sgttopo[$tglpnn]+=nb_format($rptopoperkary);
							@$sgttambah[$tglpnn]+=nb_format($rppremiplus);
							@$gttopo+=nb_format($rptopoperkary);
							@$gttambah+=nb_format($rppremiplus);
							@$sgtdenda[$tglpnn]+=nb_format($denda[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]);
							@$gtdenda+=nb_format($denda[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]);
							@$ttljjg+=nb_format($jjg[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]);
							@$sttljjg[$tglpnn]+=nb_format($jjg[$mdr][$krn][$tt][$kary][$tglpnn][$kdblok]);
							@$ttlkgkirim+=nb_format($kgkirim,2);
							@$ttlpotbrdkg+=nb_format($potbrdkg,2);
							@$sttlkgkirim[$tglpnn]+=nb_format($kgkirim,2);
							
							
							@$ttlkglb+=nb_format($kglb,2);
							@$sttlkglb[$tglpnn]+=nb_format($kglb,2);
							@$ttlrpall+=nb_format($gt);
							@$sttlrpall[$tglpnn]+=nb_format($gt);
							
							@$ttlrplb+=nb_format($rplbbss);
							@$sttlrplb[$tglpnn]+=nb_format($rplbbss);
						}
					}
				}
			}
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan=9 align=center bgcolor=#ADFF2F><b>SUB TOTAL TANGGAL ".$tglpnn."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sttljjg[$tglpnn])."</b></td>";
		$clrtgl="";
		if(abs(round($ttlkgwbperblok[$tglpnn],2)-round($sttlkgkirim[$tglpnn],2))>1){
			$clrtgl="style=background-color:red;";
		}
		$tab.="<td bgcolor=#ADFF2F align=right ".$clrtgl."><b>".@number_format($sttlkgkirim[$tglpnn],2)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sttlkgkirimnet[$tglpnn],2)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sttlkglb[$tglpnn],2)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sttlrplb[$tglpnn])."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sgtbrd[$tglpnn])."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sgtrpbrd[$tglpnn])."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sgttopo[$tglpnn])."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sgttambah[$tglpnn])."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sgtdenda[$tglpnn])."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($sttlrpall[$tglpnn])."</b></td>";
		$tab.="</tr>";
	}
	
	
		$tab.="<tr class=rowcontent>";
		$tab.="<input hidden id=totalbaris value=".$no.">";
		$tab.="<td colspan=9 align=center bgcolor=#ADFF2F><b>GRAND TOTAL</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttljjg)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlkgkirim,2)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlkgkirimnet,2)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlkglb,2)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlrplb)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gtbrd)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gtrpbrd)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gttopo)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gttambah)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gtdenda)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlrpall)."</b></td>";
		$tab.="</tr>";
		$tab.="</tbody></table></div><br>";
		
	
if ($proses != 'excel' and $ttlrestant==0 and $row==0 and $rowp==0 and $afd!='%%') {
	
	$arr="##prd##unit##afd##tahap##tgl1##tgl2##kgbrondol##perpot";
	$r="style=display:none";
	$tab.="<button class=mybutton ".$r." id=recal onclick=zpreviewdata('".$arr."','recal');>Rekalkulasi</button>";
	$tab.="<button class=mybutton id=proses onclick=saveAll(".$no.");>".$_SESSION['lang']['proses']."</button>";
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