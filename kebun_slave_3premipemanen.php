<?php
//ini_set('display_errors',0);
//error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses       =checkPostGet('proses','');
$unit         =checkPostGet('unit','');
$afd          =checkPostGet('afd','');
$prd          =checkPostGet('prd','');
$tahap        =checkPostGet('tahap','');
$tgl1        =tanggalsystemn(checkPostGet('tgl1',''));
$tgl2        =tanggalsystemn(checkPostGet('tgl2',''));

// if($tahap=='1'){
	// $tgl1 = $prd."-01";
	// $tgl2 = $prd."-15";
// }else{
	// $tgl1 = $prd."-16";
	// $tgl2 = tglakhir($tgl1);
// }

$rangetgl     =rangeTanggal($tgl1, $tgl2);

@$nmkar       =makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
@$nikkar      =makeOption($dbname,'datakaryawan','karyawanid,nik');
$optTopografi =makeOption($dbname,'setup_topografi','topografi,keterangan');
$optkodetopo  =makeOption($dbname,'setup_blok','kodeorg,topografi');

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
if($afd=='' || $unit=='' || $prd==''){
	exit("Warning : Tanggal, Unit Kerja dan Divisi wajib di isi !");
}
if(@$prdgaji=='1' || @$prdakt=='1'){
	exit ("Warning : Periode Gaji atau Periode Akutansi sudah ditutup !");
}



/* 
$w="";
if($tgl1!='--' or $tgl2!='--'){
	$w=" and a.tanggal between '".$tgl1."' and '".$tgl2."'";
}else{
	$w=" and a.tanggal like '".$prd."%'";	
}
$str="select a.*, b.nikmandor, b.nikasisten from ".$dbname.".kebun_prestasi_vs_hk a  
	left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi  
	where	1=1 ".$w." and a.unit='".$unit."' and a.kodeorg like '".$afd."%' and keterangan!='KONTAN' and b.noreferensi='' order by a.tahuntanam asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
	$karyawanid[$bar['karyawanid']]=$bar['karyawanid'];
	$list[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['karyawanid']][$bar['tanggal']][$optkodetopo[$bar['kodeorg']]]=$optkodetopo[$bar['kodeorg']];
	@$hk[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['karyawanid']][$bar['tanggal']][$optkodetopo[$bar['kodeorg']]]+=$bar['hkpanenperhari'];
	@$jjg[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['karyawanid']][$bar['tanggal']][$optkodetopo[$bar['kodeorg']]]+=$bar['hasilkerja'];
	@$kgbrd[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['karyawanid']][$bar['tanggal']][$optkodetopo[$bar['kodeorg']]]+=$bar['brondolan'];
	@$denda[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['karyawanid']][$bar['tanggal']][$optkodetopo[$bar['kodeorg']]]+=$bar['rupiahpenalty'];
	@$jjgpertt[$bar['tahuntanam']][$bar['tanggal']][$optkodetopo[$bar['kodeorg']]]+=$bar['hasilkerja'];
}
 */
#ambil kg wb
$w=""; $kgwb=$kgwblalu=$jjgwb=$tglpnn=$ttlkgkrm=array();
$w=" and tanggalpanen between '".$tgl1."' and '".$tgl2."'";
$str="select * from ".$dbname.".kebun_spb_vw where	1=1 ".$w." and blok like '".$afd."%' order by tahuntanam asc";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$kgpksplusbrd[$bar['tahuntanam']]+=$bar['kgwbnetto'];
	@$kgkgbrondol[$bar['tahuntanam']]+=$bar['brondolan'];
	@$kgpks[$bar['tahuntanam']]+=($bar['kgwbnetto']-$bar['brondolan']);
	@$ttwb[$bar['tahuntanam']]=$bar['tahuntanam'];
	@$jjgkirim[$bar['tanggalpanen']]+=$bar['jjg'];
	@$ttlkgkrm[$bar['tanggalpanen']]+=($bar['kgwbnetto']-$bar['brondolan']);
	
	/* if(substr($bar['tanggalpanen'],0,7)!=$prd){
		// @$kgwblalu[$bar['tahuntanam']][$bar['tanggalpanen']]+=$bar['kgwbnetto'];
		//@$kgwblalu[$bar['tahuntanam']][$bar['tanggalpanen']]+=($bar['kgwbnetto']-$bar['brondolan']);
		$tglpnn[$bar['tanggalpanen']]=$bar['tanggalpanen'];
	}else{
		// @$kgwb[$bar['tahuntanam']][$bar['tanggalpanen']]+=$bar['kgwbnetto'];
	} */
	@$kgwb[$bar['tahuntanam']][$bar['tanggalpanen']]+=($bar['kgwbnetto']-$bar['brondolan']);			
	@$jjgwb[$bar['tahuntanam']][$bar['tanggalpanen']]+=$bar['jjg'];		
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
$list=$jjgkegpnn=array();
while($bar=$res->fetch()){
	$tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
	$karyawanid[$bar['nik']]=$bar['nik'];
	@$list[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['nik']][$bar['tanggal']][$bar['topografi']]=$bar['topografi'];
	@$hk[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['nik']][$bar['tanggal']][$bar['topografi']]+=$bar['hkpanenperhari'];
	@$jjg[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['nik']][$bar['tanggal']][$bar['topografi']]+=$bar['hasilkerja'];
	@$kgbrd[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['nik']][$bar['tanggal']][$bar['topografi']]+=$bar['brondolan'];
	@$denda[$bar['nikmandor']][$bar['nikasisten']][$bar['tahuntanam']][$bar['nik']][$bar['tanggal']][$bar['topografi']]+=$bar['rupiahpenalty'];
	@$jjgpertt[$bar['tahuntanam']][$bar['tanggal']][$bar['topografi']]+=$bar['hasilkerja'];
	@$kodetopo[$bar['kodeorg']]=$bar['topografi'];
	
	@$jjgkegpnn[$bar['tanggal']]+=$bar['hasilkerja'];
}

// echo"<pre>";
// print_r($list);
// echo"</pre>";


if(count($list)==0){
	exit("warning : data kosong !!!");
}
if($kodetopo==''){
	exit("warning : Kode topografi masih ada yang kosong, silahkan cek melalui menu Setup - Blok !!!");
}

#jjg panen
$w="";
$w=" and tanggal between '".$tgl1."' and '".$tgl2."'";
$str="select * from ".$dbname.".kebun_rekappnn where 1=1 ".$w." and divisi like '".$afd."%'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$jjgpanen[$bar['tanggal']]+=$bar['jjgpanen'];

}

$arrtopo=array();
$jlhtopo=array();

#ambil basis wb
$str="select * from ".$dbname.".kebun_5basispanen2 where afdeling='".$unit."' and tahun='".$prd."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$basiskg[$bar['tahuntanam']][$bar['topografi']]=$bar['basis'];
	$rplb1[$bar['tahuntanam']][$bar['topografi']]=$bar['premilebihbasis'];
	$rpbrd[$bar['tahuntanam']][$bar['topografi']]=$bar['premibrondolan'];
	$rptopo[$bar['tahuntanam']][$bar['topografi']]=$bar['premitopografi'];
	$arrtopo[$bar['topografi']]=$bar['topografi'];
	$jlhtopo[$bar['topografi']]=$bar['topografi'];
}

#cek transaksi spb belum posting
$row='';
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
where 1=1 ".$w." and unit='".$unit."' and kodeorg like '".$afd."%' and jurnal='0'";
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
	$tglTemp=str_replace('-','',$tgl1);
	$tab.="<table><td><b>".$_SESSION['lang']['notransaksi']."</b></td><td><b>:</b></td>
					 <td><input disabled class=myinputtext style=width:170px value='".$tglTemp."/".$afd."/PNN02/".$nomor."' id=notransaksi></td>
					 <td>Untuk <b>menyimpan</b> silahkan click tombol <b>Proses</b> di bawah, tombol ditampilkan jika restan NOL</td><td></td>
					 </tr>";
	if($row!=''){
		$tab.="<tr><td colspan=10><font color=red>Info : Ada transaksi <b>SPB</b> yang belum di posting sebanyak = ".$row." transaksi, tanggal : ".implode(",",$tglx)." ".substr(tanggalbulan($prd."-01"),3,99)."</font></td></tr>";
	}
	if($rowp!=''){
		$tab.="<tr><td colspan=10><font color=red>Info : Ada transaksi <b>Kegiatan Panen</b> yang belum di posting sebanyak = ".$rowp." transaksi, tanggal : ".implode(",",$tglxp)." ".substr(tanggalbulan($prd."-01"),3,99)."</font></td></tr>";
	}
	
	
	
	$tab.="</table>";
	$tab.="<table class=sortable cellspacing=1>";
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
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$ttkgwb."</td>";
		$tab.="<td align=right>".@number_format($kgpksplusbrd[$ttkgwb])."</td>";
		$tab.="<td align=right>".@number_format($kgkgbrondol[$ttkgwb])."</td>";
		$tab.="<td align=right>".@number_format($kgpks[$ttkgwb])."</td>";
		foreach($arrtopo as $topografi){
			$tab.="<td align=right>".number_format(@$basiskg[$ttkgwb][$topografi],2)."</td>";
			$tab.="<td align=right>".number_format(@$rplb1[$ttkgwb][$topografi],2)."</td>";
			$tab.="<td align=right>".number_format(@$rpbrd[$ttkgwb][$topografi],2)."</td>";
			$tab.="<td align=right>".number_format(@$rptopo[$ttkgwb][$topografi],2)."</td>";
		}
		
		@$ttlkgpksplusbrd+=$kgpksplusbrd[$ttkgwb];
		@$ttlkgkgbrondol+=$kgkgbrondol[$ttkgwb];
		@$ttlkgwb+=$kgpks[$ttkgwb];
		$tab.="</tr>";			
	}
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>Total</td>";
		$tab.="<td align=right>".@number_format($ttlkgpksplusbrd)."</td>";
		$tab.="<td align=right>".@number_format($ttlkgkgbrondol)."</td>";
		$tab.="<td align=right>".@number_format($ttlkgwb)."</td>";
		$tab.="<td align=right colspan=".(($jt) * 4) ."></td>";
		$tab.="</tr>";
	$tab.="</table>
			<div style=clear:both>Note : Total Kg berdasarkan Tanggal Panen bukan berdasarkan Tanggal SPB</div>
			<hr>";
	
	
	$tab.="<table class=sortable cellspacing=1>";
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
	$tab.="<td align=left>Panen (Rekap Panen)</td>";
	foreach($rangetgl as $rtgl){
		$tab.="<td align=right>".@$jjgpanen[$rtgl]."</td>";
	}
	
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>2</td>";
	$tab.="<td align=left>Panen (Kegiatan Panen)</td>";
	foreach($rangetgl as $rtgl){
		if(@$jjgpanen[$rtgl]!=@$jjgkegpnn[$rtgl]){
			$col="style=background-color:red;";
		}else{
			$col="";
		}
		
		$tab.="<td align=right ".$col.">".@$jjgkegpnn[$rtgl]."</td>";
	}
	
	
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>3</td>";
	$tab.="<td align=left>Kirim (SPB)</td>";
	foreach($rangetgl as $rtgl){
		$tab.="<td align=right>".@$jjgkirim[$rtgl]."</td>";
	}
	
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>4</td>";
	$tab.="<td align=left>Kg PKS (SPB)</td>";
	foreach($rangetgl as $rtgl){
		$tab.="<td align=right>".@hidezerodecimal($ttlkgkrm[$rtgl],0)."</td>";
	}
	
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>5=1-4</td>";
	$tab.="<td align=left>Restan</td>";
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
	if($ttlrestant!=0){
		$tab.="<span style=color:red>Masih ada TBS yang belum terangkut (restant) sebanyak : <b>".number_format($ttlrestant)."</b> Jjg, proses tidak bisa dilanjutkan, proses hanya bisa dilanjutkan jika restant sudah NOL.</span>";
	}
	$tab.="<hr>";
	
}#tutup if proses != excel
	
	if ($proses == 'excel') {
		$tab.="<table class=sortable cellspacing=1 border=1>";
	} else 	{
		$tab.="<table class=sortable cellspacing=1>";
	}
	$tab.="<thead>";
	$tab.="<tr class=rowheader>";
		$tab.="<td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>";
		$tab.="<td align=center rowspan=2>Tanggal Panen</td>";
		$tab.="<td align=center rowspan=2>".$_SESSION['lang']['mandor']."</td>";
		$tab.="<td align=center rowspan=2>".$_SESSION['lang']['kerani']."</td>";
		$tab.="<td align=center rowspan=2 width=50px>".$_SESSION['lang']['tahuntanam']."</td>";
		$tab.="<td align=center rowspan=2>".$_SESSION['lang']['nik2']."</td>";
		$tab.="<td align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</td>";
		$tab.="<td align=center rowspan=2>".$_SESSION['lang']['topografi']."</td>";
		$tab.="<td align=center rowspan=2>".$_SESSION['lang']['jjg']."</td>";
		$tab.="<td align=center rowspan=2>Total Kg</td>";
		$tab.="<td align=center rowspan=2>Potongan<br>Brondolan<br>(Kg)</td>";
		$tab.="<td align=center rowspan=2>Total Kg<br>Netto</td>";
		$tab.="<td align=center rowspan=2>".$_SESSION['lang']['basic']." Kg</td>";
		$tab.="<td align=center colspan=3>Lebih Basis</td>";
		$tab.="<td align=center colspan=3>".$_SESSION['lang']['brondol']."</td>";
		$tab.="<td align=center rowspan=2>Kehadiran</td>";
		$tab.="<td align=center rowspan=2>".$_SESSION['lang']['denda']."</td>";
		$tab.="<td align=center rowspan=2>".$_SESSION['lang']['total']."</td>";
	$tab.="</tr>";
	$tab.="<tr>";
		$tab.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		$tab.="<td align=center>".$_SESSION['lang']['harga']."</td>";
		$tab.="<td align=center>".$_SESSION['lang']['rp']."</td>";
		$tab.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		$tab.="<td align=center>".$_SESSION['lang']['harga']."</td>";
		$tab.="<td align=center>".$_SESSION['lang']['rp']."</td>";
	$tab.="</tr>";
	$tab.="</thead>";
	$nokar=0;
	$color='';
	@$no=$bjr=$ttlrplb1=$ttlrplb2=0;
	// echo "<pre>";
	// print_r([$list]);
	// echo "</pre>";
	foreach($list as $mdr => $key ){
		foreach($key as $krn => $key2){
			foreach($key2 as $tt => $key3){
				foreach($key3 as $kary => $key4){
					foreach($key4 as $tglpnn => $key5){
						foreach($key5 as $topografi){
							if(@$basiskg[$tt][$topografi]==''){
								exit('Error : Basis panen tahun tanam '.$tt.', topografi '.$optTopografi[$topografi].' belum ada.');
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
							
							@$bjr=$kgwb[$tt][$tglpnn]/$jjgpertt[$tt][$tglpnn][$topografi];
							$color='';
							$kgkirim=$bjr*$jjg[$mdr][$krn][$tt][$kary][$tglpnn][$topografi];
							
							$i = "style=display:none";
							
							$tab.="<tr class=rowcontent ".$color." id=row".$no.">";	
							$tab.="<td align=center>".$nokar."</td>";
							$tab.="<td id=tglpnn_".$no." align=center>".$tglpnn."</td>";
							if($proses!='excel'){
								$tab.="<td id=rowmdr_".$no." ".$i.">".$mdr."</td>";
								$tab.="<td id=rowkrn_".$no." ".$i.">".$krn."</td>";
								$tab.="<td id=rowkary_".$no." ".$i.">".$kary."</td>";
								$tab.="<td id=topografi_".$no." ".$i.">".$topografi."</td>";
							}
							
							$tab.="<td>".@$nmkar[$mdr]."</td>";
							$tab.="<td>".@$nmkar[$krn]."</td>";
							$tab.="<td id=rowtt_".$no." align=center>".$tt."</td>";
							$tab.="<td>".@$nikkar[$kary]."</td><td>".@$nmkar[$kary]."</td>";
							$tab.="<td>".$optTopografi[$topografi]."</td>";
							$tab.="<td id=rowjjg_".$no." align=right>".nb_format($jjg[$mdr][$krn][$tt][$kary][$tglpnn][$topografi])."</td>";
							$tab.="<td id=rowkgbruto_".$no." align=right>".nb_format($kgkirim,2)."</td>";
							$tab.="<td ><input id=potbrd_".$no."  type=text value='0' class=myinputtextnumber style='width:50px' onkeyup=gethitungpremi(".$no."); onkeypress='return angka_doang(event)'></td>";
							$tab.="<td id=rowkg_".$no." align=right>".nb_format($kgkirim,2)."</td>";
							$tab.="<td id=rowkgbss_".$no." align=right>".nb_format($basiskg[$tt][$topografi])."</td>";
							
							$kglb = ($kgkirim - $basiskg[$tt][$topografi]);
							$rplbbss = $kglb * $rplb1[$tt][$topografi];
							$ttlrpbrd = $kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$topografi]*$rpbrd[$tt][$topografi];
							
							$tab.="<td id=rowkglb1_".$no." align=right>".nb_format($kglb,2)."</td>";
							$tab.="<td id=rowhargarplb1_".$no." align=right>".nb_format($rplb1[$tt][$topografi])."</td>";
							$tab.="<td id=rowrplb1_".$no." align=right>".nb_format($rplbbss)."</td>";
							$tab.="<td id=rowkgbrd_".$no." align=right>".@nb_format($kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$topografi])."</td>";
							$tab.="<td id=rowhargabrd_".$no." align=right>".nb_format($rpbrd[$tt][$topografi])."</td>";
							$tab.="<td id=rowrpbrd_".$no." align=right>".nb_format($ttlrpbrd)."</td>";
							$tab.="<td id=rowtopo_".$no." align=right>".@nb_format($rptopo[$tt][$topografi])."</td>";
							$tab.="<td hidden><input id=rowdendalama_".$no." value='".$denda[$mdr][$krn][$tt][$kary][$tglpnn][$topografi]."'></td>";
							$tab.="<td id=rowdenda_".$no." align=right>".@nb_format($denda[$mdr][$krn][$tt][$kary][$tglpnn][$topografi])."</td>";
							
							$gt = $rplbbss + $ttlrpbrd +$rptopo[$tt][$topografi]- $denda[$mdr][$krn][$tt][$kary][$tglpnn][$topografi];
							$gt=($gt<0?0:$gt);
							$tab.="<td align=right id=gtotal_".$no.">".@number_format($gt)."</td>";
							$tab.="</tr>";
							
							@$gtbrd+=$kgbrd[$mdr][$krn][$tt][$kary][$tglpnn][$topografi];
							@$gtrpbrd+=$ttlrpbrd;
							@$gttopo+=$rptopo[$tt][$topografi];
							@$gtdenda+=$denda[$mdr][$krn][$tt][$kary][$tglpnn][$topografi];
							@$ttljjg+=$jjg[$mdr][$krn][$tt][$kary][$tglpnn][$topografi];
							@$ttlkgkirim+=$kgkirim;
							@$ttlrpall+=$gt;
							@$ttlkglb+=$kglb;
							@$ttlrplb+=$rplbbss;
						}
					}
				}
			}
		}
	}
	
	
		$tab.="<tr class=rowcontent>";
		$tab.="<input hidden id=totalbaris value=".$no.">";
		$tab.="<td colspan=8 align=center bgcolor=#ADFF2F><b>GRAND TOTAL</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttljjg)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlkgkirim)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlkgkirim)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlkglb)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlrplb)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gtbrd)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b></b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gtrpbrd)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gttopo)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($gtdenda)."</b></td>";
		$tab.="<td bgcolor=#ADFF2F align=right><b>".@number_format($ttlrpall)."</b></td>";
		$tab.="</tr>";
		$tab.="</tbody></table><br>";
if ($proses != 'excel' and $ttlrestant==0) {
    $tab.="<button class=mybutton onclick=deleteTrans(".$no.");>".$_SESSION['lang']['proses']."</button>";
}

function nb_format($e,$i=0,$proses='preview'){
	if($proses=='preview' or $proses=='excel'){
		$n = number_format($e,$i);
	}else{
		$n = $e;
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