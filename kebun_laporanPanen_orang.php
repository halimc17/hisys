<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// ambil yang dilempar javascript
$pt = checkPostGet('pt','');
$unit = checkPostGet('unit','');
$afdeling = checkPostGet('afdeling','');
$intiplasma = checkPostGet('intiplasma','');
$tgl1 = checkPostGet('tgl1','');
$tgl2 = checkPostGet('tgl2','');
$pil = checkPostGet('pil','');
$jenis = checkPostGet('jenis','');

$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

#=========== Ambil KG WB ==============#
$whwb='';
if($unit!=''){
	$whwb.=" and kodeorg='".$unit."'";
}
if($afdeling!=''){
	$whwb.=" and divisi='".$afdeling."'";
}
if($intiplasma!=''){
	$whwb.=" and intiplasma='".$intiplasma."'";
}
$str="select * from ".$dbname.".kebun_spb_vw where tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." ".$whwb."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$kgwbx+=$bar['kgwb'];
}
#=========== End Ambil KG WB ==============#

// olah tanggal
$tanggal1=explode('-',$tgl1);
$tanggal2=explode('-',$tgl2);
$date1=$tanggal1[2].'-'.$tanggal1[1].'-'.$tanggal1[0];
$tanggalterakhir=date('t', strtotime($date1));

$tanggal1_1=date( "Y-m-d", mktime(0,0,0 ,$tanggal1[1]-1, $tanggal1[0], $tanggal1[2]) );
$tahuntahuntahun=substr($tanggal1_1,0,4);
$bulanbulanbulan=substr($tanggal1_1,5,2); 

// ambil bjr sesuaikan dengan algoritma LBM (lbm_slave_produksi_perblok.php) 
if($afdeling==''){       
$sProd="select distinct * from ".$dbname.".kebun_spb_bulanan_vw where blok like '".$unit."%' and periode = '".$tahuntahuntahun."-".$bulanbulanbulan."'";
}
else
{
$sProd="select distinct * from ".$dbname.".kebun_spb_bulanan_vw where blok like '".$afdeling."' and periode = '".$tahuntahuntahun."-".$bulanbulanbulan."'";
}
$qProd=$owlPDO->query($sProd) or die(print " Gagal: ".PDOException::getMessage());
$qProd->setFetchMode(PDO::FETCH_ASSOC);
while($rProd=$qProd->fetch()){
	$blok[$rProd['blok']]=$rProd['blok'];
	$kgwb[$rProd['blok']]=$rProd['nettotimbangan'];
}

if($afdeling==''){
$sJjg="select distinct sum(hasilkerja) as jjg,left(tanggal,7) as periode,kodeorg from ".$dbname.".kebun_prestasi_vw 
   where kodeorg like '".$unit."%' and left(tanggal,7) = '".$tahuntahuntahun."-".$bulanbulanbulan."' and jurnal=1
   group  by kodeorg";
}
else{
$sJjg="select distinct sum(hasilkerja) as jjg,left(tanggal,7) as periode,kodeorg from ".$dbname.".kebun_prestasi_vw 
   where kodeorg like '".$afdeling."' and left(tanggal,7) = '".$tahuntahuntahun."-".$bulanbulanbulan."' and jurnal=1
   group  by kodeorg";
}
$qJjg=$owlPDO->query($sJjg) or die(print " Gagal: ".PDOException::getMessage());
$qJjg->setFetchMode(PDO::FETCH_ASSOC);
while($rJjg=$qJjg->fetch()){
	$blok[$rJjg['kodeorg']]=$rJjg['kodeorg'];
	$jjg[$rJjg['kodeorg']]=$rJjg['jjg'];
}


if(!empty($blok))foreach($blok as $blk)
{
	@$bjrlalu[$blk]=$kgwb[$blk]/$jjg[$blk];
}

// urutin tanggal
$tanggal=Array();
if($tanggal2[1]>$tanggal1[1]){ // beda bulan
	for ($i = $tanggal1[0]; $i <= $tanggalterakhir; $i++) {
		if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
		$tanggal[$tanggal1[2].'-'.$tanggal1[1].'-'.$ii]=$tanggal1[2].'-'.$tanggal1[1].'-'.$ii;
	}
	for ($i = 1; $i <= $tanggal2[0]; $i++) {
		if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
		$tanggal[$tanggal2[2].'-'.$tanggal2[1].'-'.$ii]=$tanggal2[2].'-'.$tanggal2[1].'-'.$ii;
	}
}else{ // sama bulan
	for ($i = $tanggal1[0]; $i <= $tanggal2[0]; $i++) {
		if(strlen($i)==1)$ii='0'.$i; else $ii=$i;
		$tanggal[$tanggal1[2].'-'.$tanggal1[1].'-'.$ii]=$tanggal1[2].'-'.$tanggal1[1].'-'.$ii;
	}
}

// kamus karyawan --- ga dibatesin, batesin untuk optimize (kalo dah yakin)
$sdakar="select karyawanid, namakaryawan, tipekaryawan, subbagian from ".$dbname.".datakaryawan";
$qdakar=$owlPDO->query($sdakar) or die(print " Gagal: ".PDOException::getMessage());
$qdakar->setFetchMode(PDO::FETCH_ASSOC);
while($rdakar=$qdakar->fetch())
{
	$dakar[$rdakar['karyawanid']]['karyawanid']=$rdakar['karyawanid'];
	$dakar[$rdakar['karyawanid']]['namakaryawan']=$rdakar['namakaryawan'];
	$dakar[$rdakar['karyawanid']]['tipekaryawan']=$rdakar['tipekaryawan'];
	$dakar[$rdakar['karyawanid']]['subbagian']=$rdakar['subbagian'];
}

$stikar="select id, tipe from ".$dbname.".sdm_5tipekaryawan";
$qtikar=$owlPDO->query($stikar) or die(print " Gagal: ".PDOException::getMessage());
$qtikar->setFetchMode(PDO::FETCH_ASSOC);
while($rtikar=$qtikar->fetch())
{
	$tikar[$rtikar['id']]=$rtikar['tipe'];
}

//bjr aktual bulan lalu

if($unit=='') // script copy-an dari kebun_laporanPanen.php
{
	// $str="select z.nikmandor, a.tanggal,GROUP_CONCAT(a.tahuntanam SEPARATOR ' ') as tahuntanam,a.unit,
	// 	GROUP_CONCAT(a.kodeorg SEPARATOR ' ') as kodeorg,sum(a.hasilkerja) as jjg,
	// 	sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
	// 	sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,sum(a.luaspanen) as luaspanen, 
	// 	a.karyawanid 
	// 	from ".$dbname.".kebun_prestasi_vw a
	// 	left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi
	// 	left join ".$dbname.".kebun_aktifitas z on a.notransaksi=z.notransaksi 
	// 	left join ".$dbname.".setup_blok b on a.kodeorg = b.indukblok 
	// 	where c.induk = '".$pt."' and substr(a.kodeorg,1,6) IN (".getOrgDetail(26).") and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." and b.intiplasma like '%".$intiplasma."%'  
	// 	and a.jurnal=1
	// 	group by a.tanggal,a.karyawanid";
	
	$str="SELECT 
		z.nikmandor,
		a.tanggal,
		GROUP_CONCAT(DISTINCT a.tahuntanam SEPARATOR ' ') AS tahuntanam,
		a.unit,
		GROUP_CONCAT(DISTINCT a.kodeorg SEPARATOR ' ') AS kodeorg,
		SUM(a.hasilkerja) AS jjg,
		SUM(a.hasilkerjakg) AS berat,
		SUM(a.brondolan) AS brondolankg,
		SUM(a.upahkerja) AS upah,
		SUM(a.upahpremi) AS premi,
		SUM(a.rupiahpenalty) AS penalty,
		SUM(a.luaspanen) AS luaspanen,
		a.karyawanid
	FROM $dbname.kebun_prestasi_vw a
	LEFT JOIN $dbname.organisasi c 
		ON SUBSTR(a.kodeorg, 1, 4) = c.kodeorganisasi
	LEFT JOIN $dbname.kebun_aktifitas z
		ON a.notransaksi = z.notransaksi
	LEFT JOIN (
		SELECT DISTINCT indukblok, intiplasma 
		FROM $dbname.setup_blok
	) b 
		ON a.kodeorg = b.indukblok
	WHERE c.induk = '".$pt."'
		AND substr(a.kodeorg,1,6) IN (".getOrgDetail(26).")
		AND a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)."
		AND (b.intiplasma LIKE '%".$intiplasma."%' OR b.intiplasma IS NULL)
		AND a.jurnal = '1'
	GROUP BY a.tanggal, a.karyawanid";
}else{
	$where='';               
	if(!in_array($unit,getOrgDetail(28))){                
		$where=" and a.jurnal=1";
	}
	if($afdeling==''){
		$str="SELECT 
			z.nikmandor,
			a.tanggal,
			GROUP_CONCAT(DISTINCT a.tahuntanam SEPARATOR ' ') AS tahuntanam,
			a.unit,
			GROUP_CONCAT(DISTINCT a.kodeorg SEPARATOR ' ') AS kodeorg,
			SUM(a.hasilkerja) AS jjg,
			SUM(a.hasilkerjakg) AS berat,
			SUM(a.brondolan) AS brondolankg,
			SUM(a.upahkerja) AS upah,
			SUM(a.upahpremi) AS premi,
			SUM(a.rupiahpenalty) AS penalty,
			SUM(a.luaspanen) AS luaspanen,
			a.karyawanid
		FROM $dbname.kebun_prestasi_vw a
		LEFT JOIN $dbname.kebun_aktifitas z 
			ON a.notransaksi = z.notransaksi
		LEFT JOIN (
			SELECT DISTINCT indukblok, intiplasma 
			FROM $dbname.setup_blok
		) b
			ON a.kodeorg = b.indukblok
		WHERE a.unit = '".$unit."'
			AND SUBSTR(a.kodeorg, 1, 6) IN (".getOrgDetail(26).") and a.tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)."
			AND (b.intiplasma LIKE '%".$intiplasma."%' OR b.intiplasma IS NULL)
			".$where."
		GROUP BY a.tanggal, a.karyawanid";
	}
	else
	{
		$str="SELECT 
			z.nikmandor,
			a.tanggal,
			GROUP_CONCAT(DISTINCT a.tahuntanam SEPARATOR ' ') AS tahuntanam,
			a.unit,
			GROUP_CONCAT(DISTINCT a.kodeorg SEPARATOR ' ') AS kodeorg,
			SUM(a.hasilkerja) AS jjg,
			SUM(a.hasilkerjakg) AS berat,
			SUM(a.brondolan) AS brondolankg,
			SUM(a.upahkerja) AS upah,
			SUM(a.upahpremi) AS premi,
			SUM(a.rupiahpenalty) AS penalty,
			SUM(a.luaspanen) AS luaspanen,
			a.karyawanid
			FROM $dbname.kebun_prestasi_vw a
			LEFT JOIN $dbname.kebun_aktifitas z 
				ON a.notransaksi = z.notransaksi
			LEFT JOIN (
				SELECT DISTINCT indukblok, intiplasma 
				FROM $dbname.setup_blok
			) b 
				ON a.kodeorg = b.indukblok
			WHERE SUBSTR(a.kodeorg, 1, 6) = '".$afdeling."'
				AND a.tanggal BETWEEN ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)."
				AND (b.intiplasma LIKE '%".$intiplasma."%' OR b.intiplasma IS NULL)
				".$where."
			GROUP BY a.tanggal, a.karyawanid";
	}
}
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$jjgx+=$bar['jjg'];
}
$bjrx=$kgwbx/$jjgx;

$jumlahhari=count($tanggal);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
$dzArr=array();

if($jenis=='excel'){
	$tab.="<table class=sortable cellspacing=1 border=1 width=100%>";
}else{
	$tab.="<table class=sortable cellspacing=1 border=0 cellpadding=3>";
}
if($numrows<1){
	$jukol=($jumlahhari*3)+9;
	$tab.="<tr class=rowcontent><td colspan=".$jukol.">".$_SESSION['lang']['tidakditemukan']."</td></tr>";
	
}else{
	while($bar=$res->fetch()){
		$dzArr[$bar->karyawanid][$bar->tanggal]=$bar->tanggal;
		$dzArr[$bar->karyawanid]['karyawanid']=$bar->karyawanid;
		//$dzArr[$bar->karyawanid]['tahuntanam']=$bar->tahuntanam;
		$dzArr[$bar->karyawanid][$bar->tanggal.'j']=$bar->jjg;
		$dzArr[$bar->karyawanid][$bar->tanggal.'k']=$bar->jjg*$bjrx;
		$dzArr[$bar->karyawanid][$bar->tanggal.'brd']=$bar->brondolankg;
		//$dzArr[$bar->karyawanid][$bar->tanggal.'k']=$bar->berat;
		$dzArr[$bar->karyawanid][$bar->tanggal.'h']=$bar->luaspanen;
		$dzArr[$bar->karyawanid][$bar->tanggal.'b']=$bar->kodeorg;
		$dzArr[$bar->karyawanid][$bar->tanggal.'t']=$bar->tahuntanam;
		setIt($dzArr[$bar->karyawanid]['mandor'],'');
		if($dzArr[$bar->karyawanid]['mandor']!=$bar->nikmandor)$dzArr[$bar->karyawanid]['mandor']=$bar->nikmandor;
	}	
} 
if(!empty($dzArr)) { // list isi data on kodeorg
	foreach($dzArr as $c=>$key) { // list tanggal
		$sort_kodeorg[] = $key['karyawanid'];
//            $sort_tahuntanam[] = $key['tahuntanam'];
	}
	array_multisort($sort_kodeorg, SORT_ASC, $dzArr); // urut kodeorg, terus tahun tanam
}
	
// header
$tab.="<thead>
	<tr>
		<th rowspan=2 align=center>No.</th>
		<th rowspan=2 align=center>".$_SESSION['lang']['nik2']."</th>
		<th rowspan=2 align=center>".$_SESSION['lang']['namakaryawan']."</th>
		<th rowspan=2 align=center>".$_SESSION['lang']['afdeling']."</th>
		<th rowspan=2 align=center>".$_SESSION['lang']['mandor']."</th>    
		<th rowspan=2 align=center>".$_SESSION['lang']['tipekaryawan']."</th>";    
foreach($tanggal as $tang){
	$ting=explode('-',$tang);
	$qwe=date('D', strtotime($tang));
	if($pil=='fisik')$kolspan=3;
	else $kolspan=3; // lokasi
	$tab.="<th colspan=".$kolspan." align=center>";
	if($qwe=='Sun')$tab.="<font color=red>".$ting[2]."</font>"; else $tab.= $ting[2]; 
	$tab.="</th>";
}
if($pil=='fisik')$tab.="<th colspan='2' align=center>Total</th><th align=center colspan='2'>Rata2</th>"; // lokasi ga pake total 
$tab.= "</tr><tr>";
foreach($tanggal as $tang){
	if($pil=='fisik'){
		$tab.="<th align=center>".$_SESSION['lang']['jjg']."</th>";
		// $tab.="<th align=center>".$_SESSION['lang']['kgwb']."</th>";
		$tab.="<th align=center>Brd Kg</th>";
		$tab.="	<th align=center>".$_SESSION['lang']['ha']."</th>";
	}else{ // lokasi
		$tab.="<th align=center>".$_SESSION['lang']['blok']."</th>
			<th align=center>".$_SESSION['lang']['tahuntanam']."</th>
		<th align=center>".$_SESSION['lang']['bjr']." Akt ".$_SESSION['lang']['bulanlalu']."</th>";
	}
}
if($pil=='fisik'){
	$tab.="<th align=center>".$_SESSION['lang']['jjg']."</th>";
	// $tab.="<th align=center>".$_SESSION['lang']['kgwb']."</th>";
	$tab.="<th align=center>Brd Kg</th>";
	$tab.="<th align=center>".$_SESSION['lang']['ha']."</th><th align=center>".$_SESSION['lang']['jjg']."</th>";
}else{
	
}
$tab.="</tr></thead>
<tbody>";

// content
$no=0;
foreach($dzArr as $arey){ // list isi data on kodeorg
	$no+=1;
	$tab.="<tr class='rowcontent'>
		<td align=center>".$no."</td>
		<td align=left>".getKary($arey['karyawanid'],'nik')."</td>
		<td align=left>".$dakar[$arey['karyawanid']]['namakaryawan']."</td>
		<td align=left>".$dakar[$arey['karyawanid']]['subbagian']."</td>
		<td align=left>".@$dakar[$arey['mandor']]['namakaryawan']."</td>
		<td align=center>".$tikar[$dakar[$arey['karyawanid']]['tipekaryawan']]."</td>";
	$totalj=0;
	$totalk=0;
	$totalh=0;
	$totaltanpanol=0;
	$jumlahtanpanol=0;
	foreach($tanggal as $tang){ // list tanggal
		$qwe=date('D', strtotime($tang));
		setIt($arey[$tang.'j'],0);
		setIt($arey[$tang.'brd'],0);
		setIt($arey[$tang.'k'],0);
		setIt($arey[$tang.'h'],0);
		setIt($arey[$tang.'b'],0);
		setIt($arey[$tang.'t'],0);
		setIt($bjrlalu[$arey[$tang.'b']],0);
		$color="";
		$title="";
		if($arey[$tang.'j']>100 and $arey[$tang.'h']<=1){
			$color=" style=background-color:yellow";
			$title="JJG lebih dari 100 namun total luas panen <= 1";		
		}
		if($qwe=='Sun'){
			if($pil=='fisik'){
				$tab.="<td align=right ".$color." title='".$title."'><font color=red>".number_format($arey[$tang.'j'])."</font></td>";    
				//$tab.="<td align=right><font color=red>".number_format($arey[$tang.'k'])."</font></td>";    
				$tab.="<td align=right><font color=red>".number_format($arey[$tang.'brd'])."</font></td>";    
				$tab.="<td align=right ".$color." title='".$title."'><font color=red>".number_format($arey[$tang.'h'],2)."</font></td>";    
			}else{
				$tab.="<td align=left><font color=red>".$namaOrg[$arey[$tang.'b']]."</font></td>";    
				$tab.="<td align=right><font color=red>".$arey[$tang.'t']."</font></td>";    
				$tampil=number_format($bjrlalu[$arey[$tang.'b']],2);
			if($tampil==0)$tampil='';
				$tab.="<td align=right><font color=red>".$tampil."</font></td>";    
			}
		}else{
			if($pil=='fisik'){
				$tab.="<td align=right  ".$color." title='".$title."'>".number_format($arey[$tang.'j'])."</td>";    
				$tab.="<td align=right>".number_format($arey[$tang.'brd'])."</td>";    
				//$tab.="<td align=right>".number_format($arey[$tang.'k'])."</td>";    
				$tab.="<td align=right  ".$color." title='".$title."'>".number_format($arey[$tang.'h'],2)."</td>";    
			}else{
				$tab.="<td align=left>".$namaOrg[$arey[$tang.'b']]."</td>";  
				$tab.="<td align=right>".$arey[$tang.'t']."</td>";    
				$tampil=number_format($bjrlalu[$arey[$tang.'b']],2);
			if($tampil==0)$tampil='';
				$tab.="<td align=right>".$tampil."</td>";    
			}
		}
		$tab.="</td>";
		setIt($total[$tang.'j'],0);
		setIt($total[$tang.'brd'],0);
		setIt($total[$tang.'k'],0);
		setIt($total[$tang.'h'],0);
		setIt($totalj,0);
		setIt($totalbrd,0);
		setIt($totalk,0);
		setIt($totalh,0);
		
		$total[$tang.'j']+=$arey[$tang.'j']; // tambahin total bawah
		//$total[$tang.'k']+=$arey[$tang.'k']; // tambahin total bawah
		$total[$tang.'brd']+=$arey[$tang.'brd']; // tambahin total bawah
		$total[$tang.'h']+=$arey[$tang.'h']; // tambahin total bawah
		
		$totalj+=$arey[$tang.'j']; // tambahin total kanan
		//$totalk+=$arey[$tang.'k']; // tambahin total kanan
		$totalbrd+=$arey[$tang.'brd']; // tambahin total kanan
		$totalh+=$arey[$tang.'h']; // tambahin total kanan
		
		if($arey[$tang.'j']>0){
			$totaltanpanol+=$arey[$tang.'j'];
			$jumlahtanpanol+=1;
		}
		
	}
	@$rataj=$totaltanpanol/$jumlahtanpanol;
	if($pil=='fisik')$tab.="<td align=right>".number_format($totalj)."</td>";
		$tab.="<td align=right>".number_format($totalbrd)."</td>";
		$tab.="<td align=right>".number_format($totalh,2)."</td><td align=right>".number_format($rataj)."</td>";
	$tab.="</tr>";
}

if($pil=='fisik'){
// tampilin total
$tab.="<tr class='rowcontent'>
	<td colspan=6 align=center>Total</td>";
$totalj=0;
$totalbrd=0;
$totalk=0;
$totalh=0;
foreach($tanggal as $tang){ // list tanggal
	$tab.="<td align=right>".number_format($total[$tang.'j'])."</td>";   
	//$tab.="<td align=right>".number_format($total[$tang.'k'])."</td>";    
	$tab.="<td align=right>".number_format($total[$tang.'brd'])."</td>";    
	$tab.="<td align=right>".number_format($total[$tang.'h'],2)."</td>";    
	$totalj+=$total[$tang.'j']; // tambahin total kanan
	$totalbrd+=$total[$tang.'brd']; // tambahin total kanan
	$totalk+=$total[$tang.'k']; // tambahin total kanan
	$totalh+=$total[$tang.'h']; // tambahin total kanan
}
$tab.="<td align=right>".number_format($totalj)."</td>";
$tab.="<td align=right>".number_format($totalbrd)."</td>";
$tab.="<td align=right>".number_format($totalh,2)."</td><td></td></tr>";        
}

$tab.="</tbody>
	</table>";
if($jenis!='excel'){
	echo $tab; 
}else{
	#exit('error'.$jenis);
	$stream = $tab;
	$nop_ = "lappanen_" . date('Ymd_His');
	if (strlen($stream) > 0) {
		if ($handle = opendir('tempExcel')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "index.html") {
					@unlink('tempExcel/' . $file);
				}
			}
			closedir($handle);
		}
		$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
		if (!fwrite($handle, $stream)) {
			echo "<script language=javascript1.2>
					parent.window.alert('Cant convert to excel format');
					</script>";
			exit;
		} else {
			echo "<script language=javascript1.2>
					window.location='tempExcel/" . $nop_ . ".xls';
					</script>";
		}
		closedir($handle);
	}
}

?>