<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$proses = checkPostGet('proses', '');
$per2 = checkPostGet('per2', '');
$kdorg = checkPostGet('kdorg2', '');
$pt2 = checkPostGet('pt2', '');
$tt = checkPostGet('tt', '');
$ip = checkPostGet('ip', '');

$where='';
if($pt2!=''){
	$where.=" and b.alokasi='".$pt2."'";
}
$whtt='';
if($tt!=''){
	$whtt.=" and c.tahuntanam='".$tt."'";
}
$whip='';
if($ip!=''){
	$whip.=" and c.intiplasma='".$ip."'";
}

######################################
############# prepare data ###########
######################################



if ($proses == 'excel') {
    $stream = "<table class=sortable cellspacing=1 border=1>";
} else {
    $stream = "<br><table class=sortable cellspacing=1 cellpadding=5>";
}

$stream.="
    <thead>
        <tr class=rowheader bgcolor='Gainsboro'>
            <th align=center  rowspan='3'>".$_SESSION['lang']['divisi']."</th>
			<th align=center  rowspan='3'>".$_SESSION['lang']['tahuntanam']."</th>
			<th align=center  rowspan='3'>".$_SESSION['lang']['intiplasma']." (Ha)</th>
			<th align=center  rowspan='3'>".$_SESSION['lang']['luas']." (Ha)</th>
			
			<th align=center colspan=5>".$_SESSION['lang']['budget']." (Ton)</th>
			<th align=center colspan=5>".$_SESSION['lang']['sensus']." (Ton)</th>
			<th align=center colspan=6>".$_SESSION['lang']['realisasi']." (Ton)</th>
			<th align=center colspan=5>".$_SESSION['lang']['pencapaian']." - Budget %</th>
			<th align=center colspan=5>".$_SESSION['lang']['pencapaian']." - ".$_SESSION['lang']['sensus']." %</th>
			<th align=center colspan=16>TON / HA</th>
         </tr>
		 			

		 <tr bgcolor='Gainsboro'>
			<th align=center rowspan='2'>".$_SESSION['lang']['setahun']."</th>
			<th align=center  rowspan='2'>SM I</th>
			<th align=center  rowspan='2'>SM II</th>
			<th align=center  rowspan='2'>".$_SESSION['lang']['bi']."</th>
			<th align=center  rowspan='2'>".$_SESSION['lang']['sbi']."</th>
			
			<th align=center  rowspan='2'>".$_SESSION['lang']['setahun']."</th>
			<th align=center rowspan='2' >SM I</th>
			<th align=center rowspan='2' >SM II</th>
			<th align=center rowspan='2' >".$_SESSION['lang']['bi']."</th>
			<th align=center rowspan='2' >".$_SESSION['lang']['sbi']."</th>
			
			<th align=center  rowspan='2'>".$_SESSION['lang']['setahun']."</th>
			<th align=center  rowspan='2'>".$_SESSION['lang']['sampai']." ".$_SESSION['lang']['bulanlalu']."</th>
			<th align=center rowspan='2' >SM I</th>
			<th align=center rowspan='2' >SM II</th>
			<th align=center rowspan='2' >".$_SESSION['lang']['bi']."</th>
			<th align=center rowspan='2' >".$_SESSION['lang']['sbi']."</th>
			
			<th align=center  rowspan='2'>".$_SESSION['lang']['setahun']."</th>
			<th align=center rowspan='2' >SM I</th>
			<th align=center rowspan='2' >SM II</th>
			<th align=center rowspan='2' >".$_SESSION['lang']['bi']."</th>
			<th align=center rowspan='2' >".$_SESSION['lang']['sbi']."</th>
			
			<th align=center  rowspan='2'>".$_SESSION['lang']['setahun']."</th>
			<th align=center rowspan='2' >SM I</th>
			<th align=center rowspan='2' >SM II</th>
			<th align=center rowspan='2' >".$_SESSION['lang']['bi']."</th>
			<th align=center rowspan='2' >".$_SESSION['lang']['sbi']."</th>
			
			<th align=center colspan=5>".$_SESSION['lang']['budget']." (Ton)</th>
			<th align=center colspan=5>".$_SESSION['lang']['sensus']." (Ton)</th>
			<th align=center colspan=6>".$_SESSION['lang']['realisasi']." (Ton)</th>
			
		 </tr>
		 
		 <tr bgcolor='Gainsboro'>
			<th align=center >".$_SESSION['lang']['setahun']."</th>
			<th align=center  >SM I</th>
			<th align=center  >SM II</th>
			<th align=center  >".$_SESSION['lang']['bi']."</th>
			<th align=center  >".$_SESSION['lang']['sbi']."</th>
			
			<th align=center >".$_SESSION['lang']['setahun']."</th>
			<th align=center  >SM I</th>
			<th align=center  >SM II</th>
			<th align=center  >".$_SESSION['lang']['bi']."</th>
			<th align=center  >".$_SESSION['lang']['sbi']."</th>
			
			<th align=center >".$_SESSION['lang']['setahun']."</th>
			<th align=center  >".$_SESSION['lang']['sampai']." ".$_SESSION['lang']['bulanlalu']."</th>
			<th align=center  >SM I</th>
			<th align=center  >SM II</th>
			<th align=center  >".$_SESSION['lang']['bi']."</th>
			<th align=center  >".$_SESSION['lang']['sbi']."</th>
		 </tr>
		 
    </thead>
 <tbody>";

$tahun=substr($per2,0,4);
$expblnbgt=  explode('-', $per2);
$blnbgt=$expblnbgt[1];


//$persm1awal=$thn.'-01';
//$persm1akhir=$thn.'-06';


 

#sdbi
$addstr="(";
for($i=1;$i<=intval($blnbgt);$i++)
{
    if($i<10)
    {
        $isi="kg0".$i;
    }
    else 
    {
        $isi="kg".$i;
    }
    if($i<intval($blnbgt))
    {
        $addstr.=$isi."+";
    }
    else
    {
        $addstr.=$isi;
    }
}
$addstr.=")";

#1thn
$addstrthn="(";
for($i=1;$i<=12;$i++)
{
    if($i<10)
    {
        $isi="kg0".$i;
    }
    else 
    {
        $isi="kg".$i;
    }
    if($i<12)
    {
        $addstrthn.=$isi."+";
    }
    else
    {
        $addstrthn.=$isi;
    }
}
$addstrthn.=")";


#sm1
$addsm1="(";
for($i=1;$i<=6;$i++)
{
	$isi="kg0".$i;
    if($i<intval(6))
    {
        $addsm1.=$isi."+";
    }
    else
    {
        $addsm1.=$isi;
    }
}
$addsm1.=")";

#sm2
$addsm2="(";
for($i=7;$i<=12;$i++)
{
    if($i<10)
    {
        $isi="kg0".$i;
    }
    else 
    {
        $isi="kg".$i;
    }
    if($i<intval(12))
    {
        $addsm2.=$isi."+";
    }
    else
    {
        $addsm2.=$isi;
    }
}
$addsm2.=")";

$str="select substr(kodeorg,1,6) as divisi,kodeorg as blok,tahuntanam as tahuntanam,"
        . " statusblok as statusblok,luasareaproduktif as luasareaproduktif,intiplasma "
        . " from ".$dbname.".setup_blok_tahunan where kodeorg like '".$kdorg."%' and tahun='".str_replace('-', '', $per2)."'  ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$numrows=count(fetchdata($str)); 
if($numrows==0){
	$str="select substr(kodeorg,1,6) as divisi,kodeorg as blok,tahuntanam as tahuntanam,"
        . " statusblok as statusblok,luasareaproduktif as luasareaproduktif,intiplasma"
        . " from ".$dbname.".setup_blok where kodeorg like '".$kdorg."%'  ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
}
//exit('Error : '.$str);
while($bar=$res->fetch()){
    @$luas[$bar['divisi']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['luasareaproduktif'];  
}


$str=" select a.tahunbudget,a.kodeunit,a.divisi,a.kodeblok,a.thntnm, c.intiplasma,
		kg".$blnbgt." as bi,".$addstr." as sdbi,".$addstrthn." as thn,
		".$addsm1." as sm1,".$addsm2." as sm2	"
        . " from ".$dbname.".bgt_produksi_kbn_kg_vw a 
		left join ".$dbname.".organisasi b on a.kodeblok=b.kodeorganisasi 
		left join ".$dbname.".setup_blok c on a.kodeblok=c.kodeorg
		where 1=1 ".$where." ".$whtt." ".$whip." and a.divisi like '".$kdorg."%' and a.tahunbudget='".$tahun."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $kddivisi[$bar['divisi']]=$bar['divisi'];
    $tahuntanam[$bar['thntnm']]=$bar['thntnm'];
    $intPlasm[$bar['intiplasma']]=$bar['intiplasma'];
	$listtahuntanam[$bar['divisi']][$bar['thntnm']][$bar['intiplasma']]=$bar['thntnm'];
	$listip[$bar['divisi']][$bar['thntnm']][$bar['intiplasma']]=$bar['intiplasma'];
	@$bgtthn[$bar['divisi']][$bar['thntnm']][$bar['intiplasma']]+=$bar['thn'];
	@$bgtsm1[$bar['divisi']][$bar['thntnm']][$bar['intiplasma']]+=$bar['sm1'];
	@$bgtsm2[$bar['divisi']][$bar['thntnm']][$bar['intiplasma']]+=$bar['sm2'];
	@$bgtbi[$bar['divisi']][$bar['thntnm']][$bar['intiplasma']]+=$bar['bi'];
	@$bgtsdbi[$bar['divisi']][$bar['thntnm']][$bar['intiplasma']]+=$bar['sdbi'];
} 
 
 
 
##realisasi

#untuk tanggal awal menjadi awalan periode
$tglawalthn=$tahun.'-01-01';
$tglakhirthn=$tahun.'-12-31';



#bi
$str=" select * from ".$dbname.".kebun_spb_vw a
	   left join ".$dbname.".setup_blok c on a.blok=c.kodeorg
	   where tanggal like '".$per2."%' and divisi like '".$kdorg."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    @$realbi[$bar['divisi']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['kgwb'];
}

# thn
$str=" select a.*, c.intiplasma from ".$dbname.".kebun_spb_vw a 
	   left join ".$dbname.".organisasi b on a.blok=b.kodeorganisasi
	   left join ".$dbname.".setup_blok c on a.blok=c.kodeorg
	   where 1=1 ".$where." ".$whtt." ".$whip." and a.tanggal between '".$tglawalthn."' and '".$tglakhirthn."' "
        . " and a.divisi like '".$kdorg."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$kddivisi[$bar['divisi']]=$bar['divisi'];
	$intPlasm[$bar['intiplasma']]=$bar['intiplasma'];
    $tahuntanam[$bar['tahuntanam']]=$bar['tahuntanam'];
	$listip[$bar['divisi']][$bar['tahuntanam']][$bar['intiplasma']]=$bar['intiplasma'];
	$listtahuntanam[$bar['divisi']][$bar['tahuntanam']]=$bar['tahuntanam'];
	@$realthn[$bar['divisi']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['kgwb'];
}

##sdbi
$str="select tanggalsampai from ".$dbname.".setup_periodeakuntansi where periode='".$per2."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar=$res->fetch();
    $tglsampaibi=$bar['tanggalsampai'];
		
$str=" select * from ".$dbname.".kebun_spb_vw a
	   left join ".$dbname.".setup_blok c on a.blok=c.kodeorg
	   where tanggal between '".$tglawalthn."' and '".$tglsampaibi."' "
        . " and divisi like '".$kdorg."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$realsdbi[$bar['divisi']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['kgwb'];
}

##sampai dengan periode lalu
$perlalu=periodelalu($per2);

$str=" select * from ".$dbname.".kebun_spb_vw a 
	   left join ".$dbname.".setup_blok c on a.blok=c.kodeorg
	   where tanggal like '".$perlalu."%' and divisi like '".$kdorg."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    @$realsdbl[$bar['divisi']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['kgwb'];
}

#sm1
$str=" select * from ".$dbname.".kebun_spb_vw a
	   left join ".$dbname.".setup_blok c on a.blok=c.kodeorg
	   where tanggal between '".$tglawalthn."' and '".$tahun."-06-30' "
        . " and divisi like '".$kdorg."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$realsm1[$bar['divisi']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['kgwb'];
}

#sm2
$str=" select * from ".$dbname.".kebun_spb_vw a
	   left join ".$dbname.".setup_blok c on a.blok=c.kodeorg
	   where tanggal between '".$tahun."-07-01' and '".$tahun."-12-31' "
        . " and divisi like '".$kdorg."%' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$realsm2[$bar['divisi']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['kgwb'];
}

######sesus
#thn
$whsns="";
if($kdorg!=''){
	$whsns="and a.kodeorg like '".$kdorg."%'";
}
$str=" select a.*,c.intiplasma from ".$dbname.".kebun_rencanapanen_vw a
		left join ".$dbname.".setup_blok c on a.kodeblok=c.kodeorg
		where tahun='".$tahun."' ".$whsns." ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$sensusthn[$bar['kodeorg']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['kgsensus'];
}

#sm1
$str=" select a.*,c.intiplasma from ".$dbname.".kebun_rencanapanen_vw a
		left join ".$dbname.".setup_blok c on a.kodeblok=c.kodeorg
		where tahun='".$tahun."' ".$whsns." and bulan between 1 and 6 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$sensussm1[$bar['kodeorg']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['kgsensus'];
}
 
#sm2 
$str=" select a.*,c.intiplasma from ".$dbname.".kebun_rencanapanen_vw a
	   left join ".$dbname.".setup_blok c on a.kodeblok=c.kodeorg
	   where tahun='".$tahun."' ".$whsns." and bulan between 7 and 12 ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$sensussm2[$bar['kodeorg']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['kgsensus'];
}
//exit("error :".$str);
#bi 
$str=" select a.*,c.intiplasma from ".$dbname.".kebun_rencanapanen_vw a
	   left join ".$dbname.".setup_blok c on a.kodeblok=c.kodeorg
	   where tahun='".$tahun."' ".$whsns." and bulan=".intval($blnbgt)." ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$sensusbi[$bar['kodeorg']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['kgsensus'];
} 

#sdbi 
$str=" select a.*,c.intiplasma from ".$dbname.".kebun_rencanapanen_vw a
	   left join ".$dbname.".setup_blok c on a.kodeblok=c.kodeorg
	   where tahun='".$tahun."' ".$whsns." and bulan between 1 and ".intval($blnbgt)." ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$sensussdbi[$bar['kodeorg']][$bar['tahuntanam']][$bar['intiplasma']]+=$bar['kgsensus'];
}

// echo "<pre>";
// print_r($sensusthn);
// echo "</pre>";
 
$cek=count(@$kddivisi);

if($cek<1)
{
	exit("Error:Data Kosong");
}
 
array_multisort($kddivisi,SORT_ASC);
array_multisort($tahuntanam,SORT_ASC);

foreach($kddivisi as $divisi){
    foreach($tahuntanam as $thntnm){
        if(@$listtahuntanam[$divisi][$thntnm]!=''){
			foreach($intPlasm as $intpls){
				if(@$listip[$divisi][$thntnm][$intpls]!=''){	
					$stream.="<tr class=rowcontent>
						<td align=center>".$divisi."</td>
						<td align=center>".$thntnm."</td>
						<td align=center>".$intpls."</td>
						<td align=right>".@number_format($luas[$divisi][$thntnm][$intpls],2)."</td>
						
						<td align=right>".@number_format($bgtthn[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($bgtsm1[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($bgtsm2[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($bgtbi[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($bgtsdbi[$divisi][$thntnm][$intpls]/1000,2)."</td>
						
						<td align=right>".@number_format($sensusthn[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($sensussm1[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($sensussm2[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($sensusbi[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($sensussdbi[$divisi][$thntnm][$intpls]/1000,2)."</td>
						
						<td align=right>".@number_format($realthn[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($realsdbl[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($realsm1[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($realsm2[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($realbi[$divisi][$thntnm][$intpls]/1000,2)."</td>
						<td align=right>".@number_format($realsdbi[$divisi][$thntnm][$intpls]/1000,2)."</td>
						
						<td align=right>".@number_format($realthn[$divisi][$thntnm][$intpls]/$bgtthn[$divisi][$thntnm][$intpls]*100,2)."</td>
						<td align=right>".@number_format($realsm1[$divisi][$thntnm][$intpls]/$bgtsm1[$divisi][$thntnm][$intpls]*100,2)."</td>
						<td align=right>".@number_format($realsm2[$divisi][$thntnm][$intpls]/$bgtsm2[$divisi][$thntnm][$intpls]*100,2)."</td>
						<td align=right>".@number_format($realbi[$divisi][$thntnm][$intpls]/$bgtbi[$divisi][$thntnm][$intpls]*100,2)."</td>
						<td align=right>".@number_format($realsdbi[$divisi][$thntnm][$intpls]/$bgtsdbi[$divisi][$thntnm][$intpls]*100,2)."</td>
						
						<td align=right>".@number_format($realthn[$divisi][$thntnm][$intpls]/$sensusthn[$divisi][$thntnm][$intpls]*100,2)."</td>
						<td align=right>".@number_format($realsm1[$divisi][$thntnm][$intpls]/$sensussm1[$divisi][$thntnm][$intpls]*100,2)."</td>
						<td align=right>".@number_format($realsm2[$divisi][$thntnm][$intpls]/$sensussm2[$divisi][$thntnm][$intpls]*100,2)."</td>
						<td align=right>".@number_format($realbi[$divisi][$thntnm][$intpls]/$sensusbi[$divisi][$thntnm][$intpls]*100,2)."</td>
						<td align=right>".@number_format($realsdbi[$divisi][$thntnm][$intpls]/$sensussdbi[$divisi][$thntnm][$intpls]*100,2)."</td>
						
						<td align=right>".@number_format($bgtthn[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($bgtsm1[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($bgtsm2[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($bgtbi[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($bgtsdbi[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						
						<td align=right>".@number_format($sensusthn[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($sensussm1[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($sensussm2[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($sensusbi[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($sensussdbi[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						
						<td align=right>".@number_format($realthn[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($realsdbl[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($realsm1[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($realsm2[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($realbi[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>
						<td align=right>".@number_format($realsdbi[$divisi][$thntnm][$intpls]/1000/$luas[$divisi][$thntnm][$intpls],2)."</td>

						
						";
						
						@$stluas[$divisi]+=$luas[$divisi][$thntnm][$intpls];
						
						@$stbgtthn[$divisi]+=$bgtthn[$divisi][$thntnm][$intpls];
						@$stbgtsm1[$divisi]+=$bgtsm1[$divisi][$thntnm][$intpls];
						@$stbgtsm2[$divisi]+=$bgtsm2[$divisi][$thntnm][$intpls];
						@$stbgtbi[$divisi]+=$bgtbi[$divisi][$thntnm][$intpls];
						@$stbgtsdbi[$divisi]+=$bgtsdbi[$divisi][$thntnm][$intpls];
					
						@$strealthn[$divisi]+=$realthn[$divisi][$thntnm][$intpls];
						@$strealsdbl[$divisi]+=$realsdbl[$divisi][$thntnm][$intpls];
						@$strealsm1[$divisi]+=$realsm1[$divisi][$thntnm][$intpls];
						@$strealsm2[$divisi]+=$realsm2[$divisi][$thntnm][$intpls];
						@$strealbi[$divisi]+=$realbi[$divisi][$thntnm][$intpls];
						@$strealsdbi[$divisi]+=$realsdbi[$divisi][$thntnm][$intpls];
						
						@$stsensusthn[$divisi]+=$sensusthn[$divisi][$thntnm][$intpls];
						@$stsensussm1[$divisi]+=$sensussm1[$divisi][$thntnm][$intpls];
						@$stsensussm2[$divisi]+=$sensussm2[$divisi][$thntnm][$intpls];
						@$stsensusbi[$divisi]+=$sensusbi[$divisi][$thntnm][$intpls];
						@$stsensussdbi[$divisi]+=$sensussdbi[$divisi][$thntnm][$intpls];
						
						#rekap tahun tanam
						@$ttlTTluas[$thntnm][$intpls]+=$luas[$divisi][$thntnm][$intpls];
						@$ttlTTbgtthn[$thntnm][$intpls]+=$bgtthn[$divisi][$thntnm][$intpls];
						@$ttlTTbgtsm1[$thntnm][$intpls]+=$bgtsm1[$divisi][$thntnm][$intpls];
						@$ttlTTbgtsm2[$thntnm][$intpls]+=$bgtsm2[$divisi][$thntnm][$intpls];
						@$ttlTTbgtbi[$thntnm][$intpls]+=$bgtbi[$divisi][$thntnm][$intpls];
						@$ttlTTbgtsdbi[$thntnm][$intpls]+=$bgtsdbi[$divisi][$thntnm][$intpls];
					
						@$ttlTTrealthn[$thntnm][$intpls]+=$realthn[$divisi][$thntnm][$intpls];
						@$ttlTTrealsdbl[$thntnm][$intpls]+=$realsdbl[$divisi][$thntnm][$intpls];
						@$ttlTTrealsm1[$thntnm][$intpls]+=$realsm1[$divisi][$thntnm][$intpls];
						@$ttlTTrealsm2[$thntnm][$intpls]+=$realsm2[$divisi][$thntnm][$intpls];
						@$ttlTTrealbi[$thntnm][$intpls]+=$realbi[$divisi][$thntnm][$intpls];
						@$ttlTTrealsdbi[$thntnm][$intpls]+=$realsdbi[$divisi][$thntnm][$intpls];
						
						@$ttlTTsensusthn[$thntnm][$intpls]+=$sensusthn[$divisi][$thntnm][$intpls];
						@$ttlTTsensussm1[$thntnm][$intpls]+=$sensussm1[$divisi][$thntnm][$intpls];
						@$ttlTTsensussm2[$thntnm][$intpls]+=$sensussm2[$divisi][$thntnm][$intpls];
						@$ttlTTsensusbi[$thntnm][$intpls]+=$sensusbi[$divisi][$thntnm][$intpls];
						@$ttlTTsensussdbi[$thntnm][$intpls]+=$sensussdbi[$divisi][$thntnm][$intpls];
						
					}
			}	
			
		}
	}
	
          $stream.="
                <tr  bgcolor=#80FFFE>
                    <td colspan=3>".$_SESSION['lang']['subtotal']." ".$_SESSION['lang']['divisi']."  ".$divisi."</td>
                    <td align=right>".@number_format($stluas[$divisi],2)."</td>
					
                    <td align=right>".@number_format($stbgtthn[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($stbgtsm1[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($stbgtsm2[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($stbgtbi[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($stbgtsdbi[$divisi]/1000,2)."</td>
					
					<td align=right>".@number_format($stsensusthn[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($stsensussm1[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($stsensussm2[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($stsensusbi[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($stsensussdbi[$divisi]/1000,2)."</td>
					
					<td align=right>".@number_format($strealthn[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($strealsdbl[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($strealsm1[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($strealsm2[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($strealbi[$divisi]/1000,2)."</td>
					<td align=right>".@number_format($strealsdbi[$divisi]/1000,2)."</td>					
					
					<td align=right>".@number_format($strealthn[$divisi]/$stbgtthn[$divisi]*100,2)."</td>
					<td align=right>".@number_format($strealsm1[$divisi]/$stbgtsm1[$divisi]*100,2)."</td>
					<td align=right>".@number_format($strealsm2[$divisi]/$stbgtsm2[$divisi]*100,2)."</td>
					<td align=right>".@number_format($strealbi[$divisi]/$stbgtbi[$divisi]*100,2)."</td>
					<td align=right>".@number_format($strealsdbi[$divisi]/$stbgtsdbi[$divisi]*100,2)."</td>
					
					<td align=right>".@number_format($strealthn[$divisi]/$stsensusthn[$divisi]*100,2)."</td>
					<td align=right>".@number_format($strealsm1[$divisi]/$stsensussm1[$divisi]*100,2)."</td>
					<td align=right>".@number_format($strealsm2[$divisi]/$stsensussm2[$divisi]*100,2)."</td>
					<td align=right>".@number_format($strealbi[$divisi]/$stsensusbi[$divisi]*100,2)."</td>
					<td align=right>".@number_format($strealsdbi[$divisi]/$stsensussdbi[$divisi]*100,2)."</td>
					
					
					<td align=right>".@number_format($stbgtthn[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($stbgtsm1[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($stbgtsm2[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($stbgtbi[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($stbgtsdbi[$divisi]/1000/$stluas[$divisi],2)."</td>
					
					<td align=right>".@number_format($stsensusthn[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($stsensussm1[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($stsensussm2[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($stsensusbi[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($stsensussdbi[$divisi]/1000/$stluas[$divisi],2)."</td>
					
					<td align=right>".@number_format($strealthn[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($strealsdbl[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($strealsm1[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($strealsm2[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($strealbi[$divisi]/1000/$stluas[$divisi],2)."</td>
					<td align=right>".@number_format($strealsdbi[$divisi]/1000/$stluas[$divisi],2)."</td>
					
					";
				
				@$gtluas+=$stluas[$divisi];	
					
				@$gtbgtthn+=$stbgtthn[$divisi];
				@$gtbgtsm1+=$stbgtsm1[$divisi];
				@$gtbgtsm2+=$stbgtsm2[$divisi];
				@$gtbgtbi+=$stbgtbi[$divisi];
				@$gtbgtsdbi+=$stbgtsdbi[$divisi];
			
				@$gtrealthn+=$strealthn[$divisi];
				@$gtrealsdbl+=$strealsdbl[$divisi];
				@$gtrealsm1+=$strealsm1[$divisi];
				@$gtrealsm2+=$strealsm2[$divisi];
				@$gtrealbi+=$strealbi[$divisi];
				@$gtrealsdbi+=$strealsdbi[$divisi];
				
				@$gtsensusthn+=$stsensusthn[$divisi];
				@$gtsensussm1+=$stsensussm1[$divisi];
				@$gtsensussm2+=$stsensussm2[$divisi];
				@$gtsensusbi+=$stsensusbi[$divisi];
				@$gtsensussdbi+=$stsensussdbi[$divisi];
        
} 

 $stream.="
                <tr  bgcolor=#48D1CC>
                    <td colspan=3>".$_SESSION['lang']['grnd_total']."</td>
                    <td align=right>".@number_format($gtluas,2)."</td>
					
                    <td align=right>".@number_format($gtbgtthn/1000,2)."</td>
					<td align=right>".@number_format($gtbgtsm1/1000,2)."</td>
					<td align=right>".@number_format($gtbgtsm2/1000,2)."</td>
					<td align=right>".@number_format($gtbgtbi/1000,2)."</td>
					<td align=right>".@number_format($gtbgtsdbi/1000,2)."</td>
					
					<td align=right>".@number_format($gtsensusthn/1000,2)."</td>
					<td align=right>".@number_format($gtsensussm1/1000,2)."</td>
					<td align=right>".@number_format($gtsensussm2/1000,2)."</td>
					<td align=right>".@number_format($gtsensusbi/1000,2)."</td>
					<td align=right>".@number_format($gtsensussdbi/1000,2)."</td>
					
					<td align=right>".@number_format($gtrealthn/1000,2)."</td>
					<td align=right>".@number_format($gtrealsdbl/1000,2)."</td>
					<td align=right>".@number_format($gtrealsm1/1000,2)."</td>
					<td align=right>".@number_format($gtrealsm2/1000,2)."</td>
					<td align=right>".@number_format($gtrealbi/1000,2)."</td>
					<td align=right>".@number_format($gtrealsdbi/1000,2)."</td>
					
					
					<td align=right>".@number_format($gtrealthn/$gtbgtthn*100,2)."</td>
					<td align=right>".@number_format($gtrealsm1/$gtbgtsm1*100,2)."</td>
					<td align=right>".@number_format($gtrealsm2/$gtbgtsm2*100,2)."</td>
					<td align=right>".@number_format($gtrealbi/$gtbgtbi*100,2)."</td>
					<td align=right>".@number_format($gtrealsdbi/$gtbgtsdbi*100,2)."</td>
					
					
					<td align=right>".@number_format($gtrealthn/$gtsensusthn*100,2)."</td>
					<td align=right>".@number_format($gtrealsm1/$gtsensussm1*100,2)."</td>
					<td align=right>".@number_format($gtrealsm2/$gtsensussm2*100,2)."</td>
					<td align=right>".@number_format($gtrealbi/$gtsensusbi*100,2)."</td>
					<td align=right>".@number_format($gtrealsdbi/$gtsensussdbi*100,2)."</td>
					
					
					<td align=right>".@number_format($gtbgtthn/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtbgtsm1/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtbgtsm2/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtbgtbi/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtbgtsdbi/1000/$gtluas,2)."</td>
					
					<td align=right>".@number_format($gtsensusthn/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtsensussm1/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtsensussm2/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtsensusbi/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtsensussdbi/1000/$gtluas,2)."</td>
					
					<td align=right>".@number_format($gtrealthn/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtrealsdbl/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtrealsm1/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtrealsm2/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtrealbi/1000/$gtluas,2)."</td>
					<td align=right>".@number_format($gtrealsdbi/1000/$gtluas,2)."</td>
					
					
					";

$stream.="
                <tr></tr><tr class=rowcontent>
                    <td colspan=46><b>".$_SESSION['lang']['rekap']." ".$_SESSION['lang']['tahuntanam']."</b></td>";
@$nomor="";
foreach($tahuntanam as $thntnm){
	foreach($intPlasm as $intpls){
		@$nomor+=1;
		$stream.="<tr class=rowcontent>
					<td align=center>".$nomor."</td>
					<td align=center>".$thntnm."</td>
					<td align=center>".$intpls."</td>
					<td align=right>".@number_format($ttlTTluas[$thntnm][$intpls],2)."</td>
					
					<td align=right>".@number_format($ttlTTbgtthn[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTbgtsm1[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTbgtsm2[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTbgtbi[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTbgtsdbi[$thntnm][$intpls]/1000,2)."</td>
					
					<td align=right>".@number_format($ttlTTsensusthn[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTsensussm1[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTsensussm2[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTsensusbi[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTsensussdbi[$thntnm][$intpls]/1000,2)."</td>

					<td align=right>".@number_format($ttlTTrealthn[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTrealsdbl[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTrealsm1[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTrealsm2[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTrealbi[$thntnm][$intpls]/1000,2)."</td>
					<td align=right>".@number_format($ttlTTrealsdbi[$thntnm][$intpls]/1000,2)."</td>

<td align=right>".@number_format($ttlTTrealthn[$thntnm][$intpls]/$ttlTTbgtthn[$thntnm][$intpls]*100,2)."</td>
<td align=right>".@number_format($ttlTTrealsm1[$thntnm][$intpls]/$ttlTTbgtsm1[$thntnm][$intpls]*100,2)."</td>
<td align=right>".@number_format($ttlTTrealsm2[$thntnm][$intpls]/$ttlTTbgtsm2[$thntnm][$intpls]*100,2)."</td>
<td align=right>".@number_format($ttlTTrealbi[$thntnm][$intpls]/$ttlTTbgtbi[$thntnm][$intpls]*100,2)."</td>
<td align=right>".@number_format($ttlTTrealsdbi[$thntnm][$intpls]/$ttlTTbgtsdbi[$thntnm][$intpls]*100,2)."</td>

<td align=right>".@number_format($ttlTTrealthn[$thntnm][$intpls]/$ttlTTsensusthn[$thntnm][$intpls]*100,2)."</td>
<td align=right>".@number_format($ttlTTrealsm1[$thntnm][$intpls]/$ttlTTsensussm1[$thntnm][$intpls]*100,2)."</td>
<td align=right>".@number_format($ttlTTrealsm2[$thntnm][$intpls]/$ttlTTsensussm2[$thntnm][$intpls]*100,2)."</td>
<td align=right>".@number_format($ttlTTrealbi[$thntnm][$intpls]/$ttlTTsensusbi[$thntnm][$intpls]*100,2)."</td>
<td align=right>".@number_format($ttlTTrealsdbi[$thntnm][$intpls]/$ttlTTsensussdbi[$thntnm][$intpls]*100,2)."</td>

<td align=right>".@number_format($ttlTTbgtthn[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTbgtsm1[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTbgtsm2[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTbgtbi[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTbgtsdbi[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>

<td align=right>".@number_format($ttlTTsensusthn[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTsensussm1[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTsensussm2[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTsensusbi[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTsensussdbi[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>

<td align=right>".@number_format($ttlTTrealthn[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTrealsdbl[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTrealsm1[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTrealsm2[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTrealbi[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
<td align=right>".@number_format($ttlTTrealsdbi[$thntnm][$intpls]/$ttlTTluas[$thntnm][$intpls]/1000,2)."</td>
";
	
	@$gtTTluas[$thntnm]+=$ttlTTluas[$thntnm][$intpls];

	@$gtTTbgtthn[$thntnm]+=$ttlTTbgtthn[$thntnm][$intpls];
	@$gtTTbgtsm1[$thntnm]+=$ttlTTbgtsm1[$thntnm][$intpls];
	@$gtTTbgtsm2[$thntnm]+=$ttlTTbgtsm2[$thntnm][$intpls];
	@$gtTTbgtbi[$thntnm]+=$ttlTTbgtbi[$thntnm][$intpls];
	@$gtTTbgtsdbi[$thntnm]+=$ttlTTbgtsdbi[$thntnm][$intpls];

	@$gtTTsensusthn[$thntnm]+=$ttlTTsensusthn[$thntnm][$intpls];
	@$gtTTsensussm1[$thntnm]+=$ttlTTsensussm1[$thntnm][$intpls];
	@$gtTTsensussm2[$thntnm]+=$ttlTTsensussm2[$thntnm][$intpls];
	@$gtTTsensusbi[$thntnm]+=$ttlTTsensusbi[$thntnm][$intpls];
	@$gtTTsensussdbi[$thntnm]+=$ttlTTsensussdbi[$thntnm][$intpls];

	@$gtTTrealthn[$thntnm]+=$ttlTTrealthn[$thntnm][$intpls];
	@$gtTTrealsdbl[$thntnm]+=$ttlTTrealsdbl[$thntnm][$intpls];
	@$gtTTrealsm1[$thntnm]+=$ttlTTrealsm1[$thntnm][$intpls];
	@$gtTTrealsm2[$thntnm]+=$ttlTTrealsm2[$thntnm][$intpls];
	@$gtTTrealbi[$thntnm]+=$ttlTTrealbi[$thntnm][$intpls];
	@$gtTTrealsdbi[$thntnm]+=$ttlTTrealsdbi[$thntnm][$intpls];

	
	
	}
	$stream.="<tr  bgcolor=#80FFFE>
				<td colspan=3>".$_SESSION['lang']['total']." ".$_SESSION['lang']['tahuntanam']."</td>
				<td align=right>".@number_format($gtTTluas[$thntnm],2)."</td>
				<td align=right>".@number_format($gtTTbgtthn[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTbgtsm1[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTbgtsm2[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTbgtbi[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTbgtsdbi[$thntnm]/1000,2)."</td>

				<td align=right>".@number_format($gtTTsensusthn[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTsensussm1[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTsensussm2[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTsensusbi[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTsensussdbi[$thntnm]/1000,2)."</td>

				<td align=right>".@number_format($gtTTrealthn[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTrealsdbl[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTrealsm1[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTrealsm2[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTrealbi[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTrealsdbi[$thntnm]/1000,2)."</td>
				
				<td align=right>".@number_format($gtTTrealthn[$thntnm]/$gtTTbgtthn[$thntnm]*100,2)."</td>
				<td align=right>".@number_format($gtTTrealsm1[$thntnm]/$gtTTbgtsm1[$thntnm]*100,2)."</td>
				<td align=right>".@number_format($gtTTrealsm2[$thntnm]/$gtTTbgtsm2[$thntnm]*100,2)."</td>
				<td align=right>".@number_format($gtTTrealbi[$thntnm]/$gtTTbgtbi[$thntnm]*100,2)."</td>
				<td align=right>".@number_format($gtTTrealsdbi[$thntnm]/$gtTTbgtsdbi[$thntnm]*100,2)."</td>
				
				<td align=right>".@number_format($gtTTrealthn[$thntnm]/$gtTTsensusthn[$thntnm]*100,2)."</td>
				<td align=right>".@number_format($gtTTrealsm1[$thntnm]/$gtTTsensussm1[$thntnm]*100,2)."</td>
				<td align=right>".@number_format($gtTTrealsm2[$thntnm]/$gtTTsensussm2[$thntnm]*100,2)."</td>
				<td align=right>".@number_format($gtTTrealbi[$thntnm]/$gtTTsensusbi[$thntnm]*100,2)."</td>
				<td align=right>".@number_format($gtTTrealsdbi[$thntnm]/$gtTTsensussdbi[$thntnm]*100,2)."</td>
				
				<td align=right>".@number_format($gtTTbgtthn[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTbgtsm1[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTbgtsm2[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTbgtbi[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTbgtsdbi[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				
				<td align=right>".@number_format($gtTTsensusthn[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTsensussm1[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTsensussm2[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTsensusbi[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTsensussdbi[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				
				<td align=right>".@number_format($gtTTrealthn[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTrealsdbl[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTrealsm1[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTrealsm2[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTrealbi[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>
				<td align=right>".@number_format($gtTTrealsdbi[$thntnm]/$gtTTluas[$thntnm]/1000,2)."</td>

		";
			@$gttTTluas+=$gtTTluas[$thntnm];
			@$gttTTbgtthn+=$gtTTbgtthn[$thntnm];
			@$gttTTbgtsm1+=$gtTTbgtsm1[$thntnm];
			@$gttTTbgtsm2+=$gtTTbgtsm2[$thntnm];
			@$gttTTbgtbi+=$gtTTbgtbi[$thntnm];
			@$gttTTbgtsdbi+=$gtTTbgtsdbi[$thntnm];

			@$gttTTsensusthn+=$gtTTsensusthn[$thntnm];
			@$gttTTsensussm1+=$gtTTsensussm1[$thntnm];
			@$gttTTsensussm2+=$gtTTsensussm2[$thntnm];
			@$gttTTsensusbi+=$gtTTsensusbi[$thntnm];
			@$gttTTsensussdbi+=$gtTTsensussdbi[$thntnm];

			@$gttTTrealthn+=$gtTTrealthn[$thntnm];
			@$gttTTrealsdbl+=$gtTTrealsdbl[$thntnm];
			@$gttTTrealsm1+=$gtTTrealsm1[$thntnm];
			@$gttTTrealsm2+=$gtTTrealsm2[$thntnm];
			@$gttTTrealbi+=$gtTTrealbi[$thntnm];
			@$gttTTrealsdbi+=$gtTTrealsdbi[$thntnm];
	
}
			$stream.="
                <tr  bgcolor=#48D1CC>
                    <td colspan=3>".$_SESSION['lang']['grnd_total']."</td>
					<td align=right>".@number_format($gttTTluas,2)."</td>
					<td align=right>".@number_format($gttTTbgtthn/1000,2)."</td>
					<td align=right>".@number_format($gttTTbgtsm1/1000,2)."</td>
					<td align=right>".@number_format($gttTTbgtsm2/1000,2)."</td>
					<td align=right>".@number_format($gttTTbgtbi/1000,2)."</td>
					<td align=right>".@number_format($gttTTbgtsdbi/1000,2)."</td>

					<td align=right>".@number_format($gttTTsensusthn/1000,2)."</td>
					<td align=right>".@number_format($gttTTsensussm1/1000,2)."</td>
					<td align=right>".@number_format($gttTTsensussm2/1000,2)."</td>
					<td align=right>".@number_format($gttTTsensusbi/1000,2)."</td>
					<td align=right>".@number_format($gttTTsensussdbi/1000,2)."</td>

					<td align=right>".@number_format($gttTTrealthn/1000,2)."</td>
					<td align=right>".@number_format($gttTTrealsdbl/1000,2)."</td>
					<td align=right>".@number_format($gttTTrealsm1/1000,2)."</td>
					<td align=right>".@number_format($gttTTrealsm2/1000,2)."</td>
					<td align=right>".@number_format($gttTTrealbi/1000,2)."</td>
					<td align=right>".@number_format($gttTTrealsdbi/1000,2)."</td>
					
					<td align=right>".@number_format($gttTTrealthn/$gttTTbgtthn*100,2)."</td>
					<td align=right>".@number_format($gttTTrealsm1/$gttTTbgtsm1*100,2)."</td>
					<td align=right>".@number_format($gttTTrealsm2/$gttTTbgtsm2*100,2)."</td>
					<td align=right>".@number_format($gttTTrealbi/$gttTTbgtbi*100,2)."</td>
					<td align=right>".@number_format($gttTTrealsdbi/$gttTTbgtsdbi*100,2)."</td>
					
					<td align=right>".@number_format($gttTTrealthn/$gttTTsensusthn*100,2)."</td>
					<td align=right>".@number_format($gttTTrealsm1/$gttTTsensussm1*100,2)."</td>
					<td align=right>".@number_format($gttTTrealsm2/$gttTTsensussm2*100,2)."</td>
					<td align=right>".@number_format($gttTTrealbi/$gttTTsensusbi*100,2)."</td>
					<td align=right>".@number_format($gttTTrealsdbi/$gttTTsensussdbi*100,2)."</td>
					
					<td align=right>".@number_format($gttTTbgtthn/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTbgtsm1/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTbgtsm2/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTbgtbi/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTbgtsdbi/$gttTTluas/1000,2)."</td>
					
					<td align=right>".@number_format($gttTTsensusthn/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTsensussm1/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTsensussm2/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTsensusbi/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTsensussdbi/$gttTTluas/1000,2)."</td>
					
					<td align=right>".@number_format($gttTTrealthn/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTrealsdbl/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTrealsm1/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTrealsm2/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTrealbi/$gttTTluas/1000,2)."</td>
					<td align=right>".@number_format($gttTTrealsdbi/$gttTTluas/1000,2)."</td>

					";
         
                  
$stream.="
 </tbody>
     </table>";

switch ($proses) {
######PREVIEW
    case 'preview':
        echo $stream;
        break;

######EXCEL	
    case 'excel':
        $tglSkrg = date("Ymd");
        $nop_ = "rekap penc prod per div " . $kdorg;
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
                        parent.window.alert('Can't convert to excel format');
                        </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                        window.location='tempExcel/" . $nop_ . ".xls';
                        </script>";
            }
            fclose($handle);
        }
        break;
}
?>