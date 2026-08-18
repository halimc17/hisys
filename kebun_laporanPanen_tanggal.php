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
$jenis = checkPostGet('jenis','');


$namaOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$dzArr=array();
#=========== Ambil KG WB ==============#
$whwb='';
if($unit!=''){
	$whwb.=" and kodeorg='".$unit."'";
}else {
	$whwb.=" and kodeorg IN (".getOrgDetail(24).")";
}

if($afdeling!=''){
	$whwb.=" and divisi='".$afdeling."'";
}else {
	$whwb.=" and divisi IN (".getOrgDetail(26).")";
}

if($intiplasma!=''){
	$whwb.=" and intiplasma='".$intiplasma."'";
}
$str="select * from ".$dbname.".kebun_spb_vw where tanggal between ".tanggalsystem($tgl1)." and ".tanggalsystem($tgl2)." ".$whwb."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$dzArr[$bar->blok][$bar->tanggal.'k']+=$bar->kgwb;
}

#=========== End Ambil KG WB ==============#

// kamus luas
$str2="select indukblok, SUM(luasareaproduktif) as luasareaproduktif from ".$dbname.".setup_blok
where indukblok like '".$unit."%' and (".date("Y")." - tahuntanam >= 3 or statusblok='TM')
group by indukblok";
$qKmrn=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
$qKmrn->setFetchMode(PDO::FETCH_OBJ);
while($rKmr=$qKmrn->fetch()){
	$luasblok[$rKmr->indukblok]=$rKmr->luasareaproduktif;
}

// olah tanggal
$tanggal1=explode('-',$tgl1);
$tanggal2=explode('-',$tgl2);
$date1=$tanggal1[2].'-'.$tanggal1[1].'-'.$tanggal1[0];
$tanggalterakhir=date('t', strtotime($date1));

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
	
if($unit=='') // script copy-an dari kebun_laporanPanen.php
{
	$where2 = "";
	if($afdeling!=''){
		$where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
	} else {
		$where2 = " and substr(a.kodeorg,1,6) IN (".getOrgDetail(26).")";
	}
	$str="SELECT 
			a.tanggal,
			a.tahuntanam,
			a.unit,
			a.kodeorg,
			SUM(a.hasilkerja) AS jjg,
			SUM(a.hasilkerjakg) AS berat,
			SUM(a.upahkerja) AS upah,
			SUM(a.luaspanen) AS luas,
			SUM(a.upahpremi) AS premi,
			SUM(a.rupiahpenalty) AS penalty,
			COUNT(DISTINCT a.karyawanid) AS jumlahhk,
			SUM(a.hkpanenperhari) AS hkpanenperhari,
			IF(b.intiplasma = 'I', 'Inti', 'Plasma') AS intiplasma
		FROM ".$dbname.".kebun_prestasi_vs_hk a
		LEFT JOIN ".$dbname.".organisasi c 
			ON SUBSTR(a.kodeorg, 1, 4) = c.kodeorganisasi
		LEFT JOIN (
			SELECT DISTINCT indukblok, intiplasma
			FROM ".$dbname.".setup_blok
		) b ON a.kodeorg = b.indukblok 
		where c.induk = '".$pt."'  and a.tanggal between '".tanggalsystem($tgl1)."' and '".tanggalsystem($tgl2)."' and b.intiplasma like '%".$intiplasma."%' ".$where2."
		and a.jurnal=1
		group by a.tanggal,a.kodeorg";
}else{
	$where='';
	$where2='';               
	if(!in_array($unit,getOrgDetail(28))){                
		$where=" and a.jurnal=1";
	}
	if($afdeling!=''){
		$where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
	} else {
		$where2 = " and substr(a.kodeorg,1,6) IN (".getOrgDetail(26).")";
	}
	$str="SELECT 
		a.tanggal,
		a.tahuntanam,
		a.unit,
		a.kodeorg,
		SUM(a.hasilkerja) AS jjg,
		SUM(a.hasilkerjakg) AS berat,
		SUM(a.upahkerja) AS upah,
		SUM(a.upahpremi) AS premi,
		SUM(a.rupiahpenalty) AS penalty,
		COUNT(DISTINCT a.karyawanid) AS jumlahhk,
		SUM(a.hkpanenperhari) AS hkpanenperhari,
		SUM(a.luaspanen) AS luas,
		IF(b.intiplasma = 'I', 'Inti', 'Plasma') AS intiplasma
			FROM ".$dbname.".kebun_prestasi_vs_hk a
			LEFT JOIN (
				SELECT DISTINCT indukblok, intiplasma
				FROM ".$dbname.".setup_blok
			) b ON a.kodeorg = b.indukblok
		  where unit = '".$unit."'  and a.tanggal between '".tanggalsystem($tgl1)."' and '".tanggalsystem($tgl2)."' and b.intiplasma like '%".$intiplasma."%' 
		  ".$where." ".$where2."
		  group by a.tanggal, a.kodeorg";
}

// ni kemarin buat apa ya?
// $kmrn=strtotime ('-1 day',strtotime ($date1));
// $kmrn=date ('Y-m-d', $kmrn );
// if($unit=='') // script copy-an dari kebun_laporanPanen.php
// {
	// $where2="";
	// if($afdeling!='')
	// {
		// $where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
	// }
	// $str2="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
		   // sum(a.luaspanen) as luas,sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,
		   // count(a.karyawanid) as jumlahhk,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma 
		   // from ".$dbname.".kebun_prestasi_vs_hk a 
		   // left join ".$dbname.".organisasi c on substr(a.kodeorg,1,4)=c.kodeorganisasi
		   // left join ".$dbname.".setup_blok b
		   // on a.kodeorg = b.kodeorg
		   // where c.induk = '".$pt."'  and a.tanggal ='".$kmrn."' 
		   // and a.jurnal=1 and b.intiplasma like '%".$intiplasma."%' ".$where2."
		   // group by a.tanggal,a.kodeorg";
// }else{
	// $where='';
	// $where2='';
	// if($unit != $_SESSION['empl']['lokasitugas']){                
		// $where=" and a.jurnal=1";
	// }
	// if($afdeling!='')
	// {
		// $where2 = " and substr(a.kodeorg,1,6) = '".$afdeling."'";
	// }
	// $str2="select a.tanggal,a.tahuntanam,a.unit,a.kodeorg,sum(a.hasilkerja) as jjg,sum(a.hasilkerjakg) as berat,sum(a.upahkerja) as upah,
		   // sum(a.luaspanen) as luas,sum(a.upahpremi) as premi,sum(a.rupiahpenalty) as penalty,
		   // count(a.karyawanid) as jumlahhk,sum(hkpanenperhari) as hkpanenperhari, if(b.intiplasma='I','Inti','Plasma') as intiplasma
		   // from ".$dbname.".kebun_prestasi_vs_hk a
		   // left join ".$dbname.".setup_blok b
		   // on a.kodeorg = b.kodeorg
		   // where unit = '".$unit."'  and a.tanggal = '".$kmrn."'  and b.intiplasma like '%".$intiplasma."%' 
		   // ".$where." ".$where2."
		   // group by a.tanggal, a.kodeorg";
// }
// $qKmrn=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
// $qKmrn->setFetchMode(PDO::FETCH_OBJ);
// while($rKmr=$qKmrn->fetch())
// {
		// //$dzArrk[$rKmr->kodeorg][$rKmr->tanggal.'j']=$rKmr->jjg;
// }

if($jenis=='excel'){
	$tab.="<table class=sortable cellspacing=1 border=1 width=100%>";
}else{
	$tab.="<table class=sortable cellspacing=1 border=0>";
}
// isi array
$jumlahhari=count($tanggal);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows=owlBaris($res);
if($numrows<1){
	$jukol=6+($jumlahhari*4)+5;
	$tab.="<tr class=rowcontent><td colspan=".$jukol.">".$_SESSION['lang']['tidakditemukan']."</td></tr>";
}else{
	while($bar=$res->fetch()){
		$dzArrk[$bar->kodeorg][$bar->tanggal.'j']=$bar->jjg;
		$dzArr[$bar->kodeorg][$bar->tanggal]=$bar->tanggal;
		$dzArr[$bar->kodeorg]['kodeorg']=$bar->kodeorg;
		$dzArr[$bar->kodeorg]['intiplasma']=$bar->intiplasma;
		$dzArr[$bar->kodeorg]['tahuntanam']=$bar->tahuntanam;
		$dzArr[$bar->kodeorg][$bar->tanggal.'j']=$bar->jjg;
		//$dzArr[$bar->kodeorg][$bar->tanggal.'k']=$bar->berat;
		$dzArr[$bar->kodeorg][$bar->tanggal.'h']=$bar->hkpanenperhari;
		$dzArr[$bar->kodeorg][$bar->tanggal.'l']=$bar->luas;
	}	
} 
if(!empty($dzArr)) { // list isi data on kodeorg
	foreach($dzArr as $c=>$key) { // list tanggal
		$sort_kodeorg[] = $key['kodeorg'];
		$sort_tahuntanam[] = $key['tahuntanam'];
	}
	// array_multisort($sort_kodeorg, SORT_ASC, $sort_tahuntanam, SORT_ASC, $dzArr); // urut kodeorg, terus tahun tanam
	array_multisort($sort_kodeorg, SORT_ASC, $dzArr); // urut kodeorg
}
	
// header
$tab.="<thead>
	<tr>
		<th rowspan=2 align=center>No.</th>
		<th rowspan=2 align=center>".$_SESSION['lang']['afdeling']."</th>
		<th rowspan=2 align=center>".$_SESSION['lang']['kodeblok']."</th>
		<th rowspan=2 align=center>".$_SESSION['lang']['intiplasma']."</th>
		<th rowspan=2 align=center>".$_SESSION['lang']['luas']."</th>";    
foreach($tanggal as $tang){
	$ting=explode('-',$tang);
	$qwe=date('D', strtotime($tang));
	$tab.="<th colspan=4 align=center>";
	if($qwe=='Sun')$tab.="<font color=red>".$ting[2]."</font>"; else $tab.= $ting[2]; 
	$tab.="</th>";
}
$tab.="<th colspan=4 align=center>Total</th><th rowspan=2 align=center>BJR</th></tr><tr>";  
foreach($tanggal as $tang){
	$tab.="<th align=center>".$_SESSION['lang']['jjg']."</th>
		<th align=center>".$_SESSION['lang']['kgwb']."</th>
		<th align=center>".$_SESSION['lang']['luas']."</th>
		<th align=center>".$_SESSION['lang']['jumlahhk']."</th>";    
}
$tab.="<th align=center>".$_SESSION['lang']['jjg']."</th>
	<th align=center>".$_SESSION['lang']['kg']."</th>
		<th align=center>".$_SESSION['lang']['luas']."</th>
	<th align=center>".$_SESSION['lang']['jumlahhk']."</th></tr>  
	</thead>
<tbody>";

// content
$no=0;
foreach($dzArr as $arey){ // list isi data on kodeorg
	$no+=1;
	$tab.="<tr class='rowcontent'>
		<td align=center>".$no."</td>
		<td align=center>".$namaOrg[substr($arey['kodeorg'],0,6)]."</td>
		<td align=center>".getIndukBlok($arey['kodeorg'])."</td>
		<td align=center>".$arey['intiplasma']."</td>
		<td align=center>".number_format($luasblok[$arey['kodeorg']],2)."</td>";    
	$totalj=0;
	$totalk=0;
	$totalh=0;
	$totall=0;
	$totaltanpanol=0;
	$jumlahtanpanol=0;
	foreach($tanggal as $tang){ // list tanggal
		$dbg="";
		$tglkmrn=strtotime ('-1 day',strtotime ($tang));
		$tglkmrn2=date ('Y-m-d', $tglkmrn );
		setIt($dzArrk[$arey['kodeorg']][$tglkmrn2.'j'],0);
		setIt($arey[$tang.'j'],0);
		setIt($arey[$tang.'k'],0);
		setIt($arey[$tang.'l'],0);
		setIt($arey[$tang.'h'],0);
		$tittle="";
		if(($dzArrk[$arey['kodeorg']][$tglkmrn2.'j']!=0)&&($arey[$tang.'j']!=0))
		{
			$dbg="bgcolor=red";
			$tittle='title="panen di blok yang sama lebih dari satu hari" ';
		}
		$qwe=date('D', strtotime($tang));
		if($qwe=='Sun'){
			$tab.="<td align=right ".$dbg." ".$tittle."><font color=red>".number_format($arey[$tang.'j'])."</font></td>";
			$tab.="<td align=right ><font color=red>".number_format($arey[$tang.'k'])."</font></td>";
			$tab.="<td align=right ><font color=red>".number_format($arey[$tang.'l'],2)."</font></td>";
			$tab.="<td align=right ><font color=red>".number_format($arey[$tang.'h'])."</font></td>";
		}else{
			$tab.="<td align=right ".$dbg." ".$tittle.">".number_format($arey[$tang.'j'])."</td>";
			$tab.="<td align=right >".number_format($arey[$tang.'k'])."</td>";
			$tab.="<td align=right >".number_format($arey[$tang.'l'],2)."</td>";
			$tab.="<td align=right >".number_format($arey[$tang.'h'])."</td>";
		}
		setIt($total[$tang.'j'],0);
		setIt($total[$tang.'k'],0);
		setIt($total[$tang.'l'],0);
		setIt($total[$tang.'h'],0);
		setIt($totalj,0);
		setIt($totalk,0);
		setIt($totall,0);
		setIt($totalh,0);
		
		$total[$tang.'j']+=$arey[$tang.'j']; // tambahin total bawah
		$total[$tang.'k']+=$arey[$tang.'k']; // tambahin total bawah
		$total[$tang.'h']+=$arey[$tang.'h']; // tambahin total bawah
		$total[$tang.'l']+=$arey[$tang.'l']; // tambahin total bawah
		
		$totalj+=$arey[$tang.'j']; // tambahin total kanan
		$totalk+=$arey[$tang.'k']; // tambahin total kanan
		$totalh+=$arey[$tang.'h']; // tambahin total kanan
		$totall+=$arey[$tang.'l']; // tambahin total kanan
		
		if($arey[$tang.'j']>0){
			$totaltanpanol+=$arey[$tang.'j'];
			$jumlahtanpanol+=1;
		}
	}
	@$rataj=$totaltanpanol/$jumlahtanpanol;
	
	@$bjr=$totalk/$totalj;
	
	$tab.="<td align=right>".number_format($totalj)."</td>
		<td align=right>".number_format($totalk,2)."</td>
		<td align=right>".number_format($totall,2)."</td>
		<td align=right>".number_format($totalh)."</td><td align=right>".number_format($bjr,2)."</td></tr>";
}

// tampilin total
$tab.="<tr class='rowcontent'>
	<td colspan=6 align=center>Total</td>";
$totalj=0;
$totalk=0;
$totalh=0;
$totall=0;
foreach($tanggal as $tang){ // list tanggal
	$tab.="<td align=right>".number_format($total[$tang.'j'])."</td>";   
	$tab.="<td align=right>".number_format($total[$tang.'k'])."</td>";    
	$tab.="<td align=right>".number_format($total[$tang.'l'],2)."</td>";    
	$tab.="<td align=right>".number_format($total[$tang.'h'])."</td>";    
	$totalj+=$total[$tang.'j']; // tambahin total kanan
	$totalk+=$total[$tang.'k']; // tambahin total kanan
	$totalh+=$total[$tang.'h']; // tambahin total kanan
	$totall+=$total[$tang.'l']; // tambahin total kanan
}
$tab.="<td align=right>".number_format($totalj)."</td>
	<td align=right>".number_format($totalk,2)."</td>
	<td align=right>".number_format($totall,2)."</td>
	<td align=right>".number_format($totalh)."</td><td></td></tr>";


$tab.="</tbody>
	<tfoot>
	</tfoot>";

if($jenis!='excel'){
	echo $tab;
}else{
	
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