<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses        = checkPostGet('proses', '');
$kdorg         = checkPostGet('kdorg', '');
$prd           = checkPostGet('prd', '');
$param = $_POST;


if($kdorg==''){exit("warning : Kode Unit harus di pilih !!!");}
if($prd==''){exit("warning : Periode harus di pilih !!!");}

$tab="";
if ($proses == 'excel') {
    $tab.="<table class=sortable cellspacing=1 border=1>";
} else {
    $tab = "<table class=sortable cellspacing=1 cellpadding=5>";
}

$tab .= "<thead style='text-align:center'>";
$tab .= "<tr class=rowheader>";
$tab .= "<th rowspan=3>".$_SESSION['lang']['divisi']."</th>";
$tab .= "<th width=50px rowspan=3>".$_SESSION['lang']['tahuntanam']."</th>";
$tab .= "<th rowspan=3>".$_SESSION['lang']['kodeblok']."</th>";
$tab .= "<th rowspan=3>".$_SESSION['lang']['luas']."</th>";
$tab .= "<th rowspan=3>".$_SESSION['lang']['jenisbibit']."</th>";
$tab .= "<th width=50px rowspan=3>".$_SESSION['lang']['jumlahpokok']."</th>";
$tab .= "<th rowspan=3>Pkk / Ha</th>";
$tab .= "<th rowspan=3>Ha Panen</th>";
$tab .= "<th rowspan=3>Rotasi</th>";
$tab .= "<th colspan=5>Jlh HK</th>";
$tab .= "<th colspan=5>Jjg TBS</th>";
$tab .= "<th colspan=5>Kg</th>";
$tab .= "<th colspan=2>Kg / HK</th>";
$tab .= "<th colspan=2>Jjg / HK</th>";
$tab .= "<th colspan=2>BJR</th>";
$tab .= "<th colspan=5>Yield Per Ha (Kg)</th>";
$tab .= "<th colspan=5>Upah Mengumpul</th>";
$tab .= "<th colspan=5>Biaya / Kg</th>";
$tab .= "<th colspan=5>Upah Pemanen</th>";
$tab .= "<th colspan=5>Rata2 Upah/HK</th>";

$tab .= "</tr>";
$tab .= "<tr class=rowheader>";
$tab .= "<th colspan=2>Real</th>";
$tab .= "<th colspan=3>Budget</th>";
$tab .= "<th colspan=2>Dibayar</th>";
$tab .= "<th colspan=2>Dikirim</th>";
$tab .= "<th width=40px  rowspan=2>% Sel SD BI</th>";
$tab .= "<th colspan=2>Real</th>";
$tab .= "<th colspan=3>Budget</th>";
$tab .= "<th rowspan=2>BI</th>";
$tab .= "<th rowspan=2>SD BI</th>";
$tab .= "<th rowspan=2>BI</th>";
$tab .= "<th rowspan=2>SD BI</th>";
$tab .= "<th rowspan=2>BI</th>";
$tab .= "<th rowspan=2>SD BI</th>";
$tab .= "<th colspan=2>BI</th>";
$tab .= "<th colspan=3>SD BI</th>";
$tab .= "<th colspan=2>Real</th>";
$tab .= "<th colspan=3>Budget</th>";
$tab .= "<th colspan=2>Real</th>";
$tab .= "<th colspan=3>Budget</th>";
$tab .= "<th colspan=2>Real</th>";
$tab .= "<th colspan=3>Budget</th>";
$tab .= "<th colspan=2>Real</th>";
$tab .= "<th colspan=3>Budget</th>";
$tab .= "</tr>";

$tab .= "<tr class=rowheader>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>Setahun</th>";
$tab .= "<th >BI</th>";
$tab .= "<th >SD BI</th>";
$tab .= "<th >BI</th>";
$tab .= "<th >SD BI</th>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>Setahun</th>";
$tab .= "<th>Real</th>";
$tab .= "<th>Bgt</th>";
$tab .= "<th>Real</th>";
$tab .= "<th>Bgt</th>";
$tab .= "<th>Bgt Thn</th>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>Setahun</th>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>Setahun</th>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>Setahun</th>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>BI</th>";
$tab .= "<th>SD BI</th>";
$tab .= "<th>Setahun</th>";
$tab .= "</tr>";

$tab.="</thead>";
$tab.="<tbody>";

# Parameter
$strPeriod = str_replace('-','',$prd);
$tmpPeriod = explode('-',$prd);
$year = $tmpPeriod[0];
$month = $tmpPeriod[1];

$tgl1=$prd."-01";
$tgl1jan=$year."-01-01";
$tgl2=tglakhir($prd);

$periode1=$year."-01";
$periode2=$prd;


$qBlok = selectQuery($dbname,'setup_blok_tahunan','*',"kodeorg like '".$kdorg."%' and tahun='".$strPeriod."' and luasareaproduktif>0");
$numrows = count(fetchData($qBlok));
if($numrows==0){
	$qBlok = selectQuery($dbname,'setup_blok','*',"kodeorg like '".$kdorg."%' and luasareaproduktif>0");
}
$res = $owlPDO->query($qBlok) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$datablok[substr($bar['kodeorg'],0,6)][$bar['tahuntanam']][$bar['kodeorg']]=$bar['kodeorg'];
	@$luas[substr($bar['kodeorg'],0,6)][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['luasareaproduktif'];
	@$pokok[substr($bar['kodeorg'],0,6)][$bar['tahuntanam']][$bar['kodeorg']]+=$bar['jumlahpokok'];
	@$jnsbbt[substr($bar['kodeorg'],0,6)][$bar['tahuntanam']][$bar['kodeorg']]=$bar['jenisbibit'];
}

#panen
$str = "select divisi,tahuntanam,blok,substr(tanggal,1,7) as periode, sum(luaspanen) as luaspanen,  
sum(tenagakerja) as tenagakerja,sum(jjgpanen) as jjgpanen    
from " . $dbname . ".kebun_rekappnn_vw a  where divisi like '".$kdorg."%' and tanggal between '".$tgl1jan."' and  '".$tgl2."'  
group by divisi,tahuntanam,blok,periode"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if($bar['periode']==$prd){
		@$hapnnbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['luaspanen'];
		@$hkbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['tenagakerja'];
		@$jjgpnnbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['jjgpanen'];
	}
	@$hapnnsbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['luaspanen'];
	@$hksbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['tenagakerja'];
	@$jjgpnnsbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['jjgpanen'];
}

#kirim
$str = "select divisi,tahuntanam,blok,substr(tanggal,1,7) as periode, sum(jjg) as jjg,  
sum(kgwb) as kgwb       
from " . $dbname . ".kebun_spb_vw a  where divisi like '".$kdorg."%'  
and tanggal between '".$tgl1jan."' and  '".$tgl2."'  and posting='1'  
group by divisi,tahuntanam,blok,periode"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if($bar['periode']==$prd){
		@$jjgkrmbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['jjg'];
		@$kgkrmbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['kgwb'];
	}
	@$jjgkrmsbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['jjg'];
	@$kgkrmsbi[$bar['divisi']][$bar['tahuntanam']][$bar['blok']]+=$bar['kgwb'];
}

#bgt
$e="(";$n="(";
for($i=1;$i<=intval($month);$i++){
	$r="kg".addZero($i,2);
	$s="rp".addZero($i,2);
    if($i<intval($month)){$e.=$r."+";$n.=$s."+";}else{$e.=$r;$n.=$s;}
}
$e.=")";$n.=")";

$t="(kg01+kg02+kg03+kg04+kg05+kg06+kg07+kg08+kg09+kg10+kg11+kg12)";
$str=" select divisi,kodeblok,thntnm,".$e." as sdbi,kg".$month." as bi,".$t." as setahun from ".$dbname.".bgt_produksi_kbn_kg_vw a where kodeunit = '".$kdorg."' and tahunbudget = '".$year."'";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$datablok[$bar['divisi']][$bar['thntnm']][$bar['kodeblok']]=$bar['kodeblok'];
	
	@$kgbgtbi[$bar['divisi']][$bar['thntnm']][$bar['kodeblok']]+=$bar['bi'];
	@$kgbgtsbi[$bar['divisi']][$bar['thntnm']][$bar['kodeblok']]+=$bar['sdbi'];
	@$kgbgtthn[$bar['divisi']][$bar['thntnm']][$bar['kodeblok']]+=$bar['setahun'];
}


$p="(rp01+rp02+rp03+rp04+rp05+rp06+rp07+rp08+rp09+rp10+rp11+rp12)";
$str=" select kodebudget,jumlah,noakun,kodeorg,substr(kodeorg,1,6) as divisi,".$n." as sdbi,rp".$month." as bi,rupiah as setahun from ".$dbname.".bgt_budget_detail a where kodeorg like '".$kdorg."%' and tahunbudget = '".$year."' and noakun like '611%'";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	$nmttb=makeOption($dbname,'bgt_blok','kodeblok,thntnm',"kodeblok='".$bar['kodeorg']."' and tahunbudget = '".$year."'");
	$datablok[$bar['divisi']][$nmttb[$bar['kodeorg']]][$bar['kodeorg']]=$bar['kodeorg'];	
	@$rpbgtbi[$bar['divisi']][$nmttb[$bar['kodeorg']]][$bar['kodeorg']]+=$bar['bi'];
	@$rpbgtsbi[$bar['divisi']][$nmttb[$bar['kodeorg']]][$bar['kodeorg']]+=$bar['sdbi'];
	@$rpbgtthn[$bar['divisi']][$nmttb[$bar['kodeorg']]][$bar['kodeorg']]+=$bar['setahun'];		

	if($bar['noakun']=='6110101' or $bar['noakun']=='6110102' or $bar['noakun']=='6110103'){
		@$rpbgtuphbi[$bar['divisi']][$nmttb[$bar['kodeorg']]][$bar['kodeorg']]+=$bar['bi'];
		@$rpbgtuphsbi[$bar['divisi']][$nmttb[$bar['kodeorg']]][$bar['kodeorg']]+=$bar['sdbi'];
		@$rpbgtuphthn[$bar['divisi']][$nmttb[$bar['kodeorg']]][$bar['kodeorg']]+=$bar['setahun'];		
	}
	
	if($bar['kodebudget']=='SDM-KBL' or $bar['kodebudget']=='SDM-KHT' or $bar['kodebudget']=='SDM-KNT' or $bar['kodebudget']=='SDM-PHL'){
		@$hkbgtthn[$bar['divisi']][$nmttb[$bar['kodeorg']]][$bar['kodeorg']]+=$bar['jumlah'];
	}
}

#biaya
$str = "select noakun,sum(jumlah) as jumlah, substr(kodeblok,1,6) as divisi,kodeblok,periode,kodeorg  
from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,3) in ('611') and kodeorg='".$kdorg."' and periode between '".$periode1."' and  '".$periode2."' 
group by periode,divisi,kodeblok,noakun"; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$nmtt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$bar['kodeblok']."'");
	
	if($bar['periode']==$prd and $bar['kodeblok']!=''){
		@$rprealbi[$bar['divisi']][$nmtt[$bar['kodeblok']]][$bar['kodeblok']]+=$bar['jumlah'];
		
		if($bar['noakun']=='6110101' or $bar['noakun']=='6110102' or $bar['noakun']=='6110103'){
			@$rprealuphbi[$bar['divisi']][$nmtt[$bar['kodeblok']]][$bar['kodeblok']]+=$bar['jumlah'];
		}
	}
	
	if($bar['kodeblok']!=''){
		@$rprealsbi[$bar['divisi']][$nmtt[$bar['kodeblok']]][$bar['kodeblok']]+=$bar['jumlah'];
		$datablok[$bar['divisi']][$nmtt[$bar['kodeblok']]][$bar['kodeblok']]=$bar['kodeblok'];

		if($bar['noakun']=='6110101' or $bar['noakun']=='6110102' or $bar['noakun']=='6110103'){
			@$rprealuphsbi[$bar['divisi']][$nmtt[$bar['kodeblok']]][$bar['kodeblok']]+=$bar['jumlah'];
		}
	}
	
	
	if($bar['periode']==$prd and $bar['kodeblok']==''){
		@$rprealnbbi+=$bar['jumlah'];
	}
	if($bar['kodeblok']==''){
		@$rprealnbsbi+=$bar['jumlah'];
	}
	
}

// echo"<pre>";
// print_r($datablokx);
// echo"</pre>";

// exit();

if(count($datablok)==0){
	exit("Warning : Data Kosong !!!");
}

$no=0;
foreach ($datablok as $divisi => $valtt){
	foreach($valtt as $tt => $valblok){
		foreach($valblok as $blok){
			$no++;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$divisi."</td>";
			$tab.="<td align=center>".$tt."</td>";
			$tab.="<td align=center>".getNamaOrg($blok)."</td>";
			$tab.="<td align=right>".@nantozero($luas[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=center>".$jnsbbt[$divisi][$tt][$blok]."</td>";
			$tab.="<td align=right>".@nantozero($pokok[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($pokok[$divisi][$tt][$blok]/$luas[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($hapnnbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($hapnnbi[$divisi][$tt][$blok]/$luas[$divisi][$tt][$blok],2)."</td>";
			$tab.="<td align=right>".@nantozero($hkbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($hksbi[$divisi][$tt][$blok])."</td>";
			
			if(@$rpbgtuphthn[$divisi][$tt][$blok]!=0){				
				$hkbgtbi[$divisi][$tt][$blok]=$rpbgtuphbi[$divisi][$tt][$blok]/$rpbgtuphthn[$divisi][$tt][$blok]*$hkbgtthn[$divisi][$tt][$blok];
				$hkbgtsbi[$divisi][$tt][$blok]=$rpbgtuphsbi[$divisi][$tt][$blok]/$rpbgtuphthn[$divisi][$tt][$blok]*$hkbgtthn[$divisi][$tt][$blok];
			}
			
			$tab.="<td align=right>".@nantozero($hkbgtbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($hkbgtsbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($hkbgtthn[$divisi][$tt][$blok])."</td>";
			
			$tab.="<td align=right>".@nantozero($jjgpnnbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($jjgpnnsbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($jjgkrmbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($jjgkrmsbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero(abs((($jjgkrmsbi[$divisi][$tt][$blok]-$jjgpnnsbi[$divisi][$tt][$blok])/$jjgkrmsbi[$divisi][$tt][$blok])*100),2)."</td>";
			$tab.="<td align=right>".@nantozero($kgkrmbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($kgkrmsbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($kgbgtbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($kgbgtsbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($kgbgtthn[$divisi][$tt][$blok])."</td>";
			
			#KG/HK
			if($hkbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($kgkrmbi[$divisi][$tt][$blok]/$hkbi[$divisi][$tt][$blok])."</td>";
			}
			if($hksbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($kgkrmsbi[$divisi][$tt][$blok]/$hksbi[$divisi][$tt][$blok])."</td>";
			}
			#JJG/HK
			if($hkbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($jjgpnnbi[$divisi][$tt][$blok]/$hkbi[$divisi][$tt][$blok])."</td>";
			}
			if($hksbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($jjgpnnsbi[$divisi][$tt][$blok]/$hksbi[$divisi][$tt][$blok])."</td>";
			}
			
			#BJR
			if($jjgkrmbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($kgkrmbi[$divisi][$tt][$blok]/$jjgkrmbi[$divisi][$tt][$blok],2)."</td>";
			}
			if($jjgkrmsbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($kgkrmsbi[$divisi][$tt][$blok]/$jjgkrmsbi[$divisi][$tt][$blok],2)."</td>";
			}
			
			$tab.="<td align=right>".@nantozero($kgkrmbi[$divisi][$tt][$blok]/$luas[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($kgbgtbi[$divisi][$tt][$blok]/$luas[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($kgkrmsbi[$divisi][$tt][$blok]/$luas[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($kgbgtsbi[$divisi][$tt][$blok]/$luas[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($kgbgtthn[$divisi][$tt][$blok]/$luas[$divisi][$tt][$blok])."</td>";
			
			$cr=" style='cursor:pointer;text-align:right;color:blue' onclick=mengumpul('".$blok."','".$prd."','real','')";
			$cb=" style='cursor:pointer;text-align:right;color:blue' onclick=mengumpul('".$blok."','".$prd."','bgt','')";
			$tab.="<td ".$cr.">".@nantozero($rprealbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td ".$cr.">".@nantozero($rprealsbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td ".$cb.">".@nantozero($rpbgtbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td ".$cb.">".@nantozero($rpbgtsbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td ".$cb.">".@nantozero($rpbgtthn[$divisi][$tt][$blok])."</td>";
			
			#Biaya / KG
			if($kgkrmbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($rprealbi[$divisi][$tt][$blok]/$kgkrmbi[$divisi][$tt][$blok],2)."</td>";
			}
			if($kgkrmsbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($rprealsbi[$divisi][$tt][$blok]/$kgkrmsbi[$divisi][$tt][$blok],2)."</td>";
			}
			
			if($kgbgtbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($rpbgtbi[$divisi][$tt][$blok]/$kgbgtbi[$divisi][$tt][$blok],2)."</td>";
			}
			if($kgbgtsbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($rpbgtsbi[$divisi][$tt][$blok]/$kgbgtsbi[$divisi][$tt][$blok],2)."</td>";
			}
			if($kgbgtthn[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($rpbgtthn[$divisi][$tt][$blok]/$kgbgtthn[$divisi][$tt][$blok],2)."</td>";
			}
			
			$tab.="<td align=right>".@nantozero($rprealuphbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($rprealuphsbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($rpbgtuphbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($rpbgtuphsbi[$divisi][$tt][$blok])."</td>";
			$tab.="<td align=right>".@nantozero($rpbgtuphthn[$divisi][$tt][$blok])."</td>";
			
			#Rata2 Upah/HK
			if($hkbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($rprealuphbi[$divisi][$tt][$blok]/$hkbi[$divisi][$tt][$blok])."</td>";
			}
			if($hksbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($rprealuphsbi[$divisi][$tt][$blok]/$hksbi[$divisi][$tt][$blok])."</td>";
			}
			
			if(@$hkbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($rpbgtuphbi[$divisi][$tt][$blok]/$hkbgtbi[$divisi][$tt][$blok])."</td>";
			}
			if(@$hkbgtsbi[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($rpbgtuphsbi[$divisi][$tt][$blok]/$hkbgtsbi[$divisi][$tt][$blok])."</td>";
			}
			if(@$hkbgtthn[$divisi][$tt][$blok]==0){
				$tab.="<td align=right></td>";
			}else{
				$tab.="<td align=right>".@nantozero($rpbgtuphthn[$divisi][$tt][$blok]/$hkbgtthn[$divisi][$tt][$blok])."</td>";
			}
			
			#subtotal tahuntanam
			@$stluas[$divisi][$tt]        +=$luas[$divisi][$tt][$blok];
			@$stpokok[$divisi][$tt]       +=$pokok[$divisi][$tt][$blok];
			@$sthapnnbi[$divisi][$tt]     +=$hapnnbi[$divisi][$tt][$blok];
			@$sthkbi[$divisi][$tt]        +=$hkbi[$divisi][$tt][$blok];
			@$sthksbi[$divisi][$tt]       +=$hksbi[$divisi][$tt][$blok];
			@$sthkbgtbi[$divisi][$tt]     +=$hkbgtbi[$divisi][$tt][$blok];
			@$sthkbgtsbi[$divisi][$tt]    +=$hkbgtsbi[$divisi][$tt][$blok];
			@$sthkbgtthn[$divisi][$tt]    +=$hkbgtthn[$divisi][$tt][$blok];
			@$stjjgpnnbi[$divisi][$tt]    +=$jjgpnnbi[$divisi][$tt][$blok];
			@$stjjgpnnsbi[$divisi][$tt]   +=$jjgpnnsbi[$divisi][$tt][$blok];
			@$stjjgkrmbi[$divisi][$tt]    +=$jjgkrmbi[$divisi][$tt][$blok];
			@$stjjgkrmsbi[$divisi][$tt]   +=$jjgkrmsbi[$divisi][$tt][$blok];
			@$stkgkrmbi[$divisi][$tt]     +=$kgkrmbi[$divisi][$tt][$blok];
			@$stkgkrmsbi[$divisi][$tt]    +=$kgkrmsbi[$divisi][$tt][$blok];
			@$stkgbgtbi[$divisi][$tt]     +=$kgbgtbi[$divisi][$tt][$blok];
			@$stkgbgtsbi[$divisi][$tt]    +=$kgbgtsbi[$divisi][$tt][$blok];
			@$stkgbgtthn[$divisi][$tt]    +=$kgbgtthn[$divisi][$tt][$blok];
			@$strprealbi[$divisi][$tt]    +=$rprealbi[$divisi][$tt][$blok];
			@$strprealsbi[$divisi][$tt]   +=$rprealsbi[$divisi][$tt][$blok];
			@$strpbgtbi[$divisi][$tt]     +=$rpbgtbi[$divisi][$tt][$blok];
			@$strpbgtsbi[$divisi][$tt]    +=$rpbgtsbi[$divisi][$tt][$blok];
			@$strpbgtthn[$divisi][$tt]    +=$rpbgtthn[$divisi][$tt][$blok];
			@$strprealuphbi[$divisi][$tt] +=$rprealuphbi[$divisi][$tt][$blok];
			@$strprealuphsbi[$divisi][$tt]+=$rprealuphsbi[$divisi][$tt][$blok];
			@$strpbgtuphbi[$divisi][$tt]  +=$rpbgtuphbi[$divisi][$tt][$blok];
			@$strpbgtuphsbi[$divisi][$tt] +=$rpbgtuphsbi[$divisi][$tt][$blok];
			@$strpbgtuphthn[$divisi][$tt] +=$rpbgtuphthn[$divisi][$tt][$blok];

			#Subtotal divisi
			@$sdluas[$divisi]        +=$luas[$divisi][$tt][$blok];
			@$sdpokok[$divisi]       +=$pokok[$divisi][$tt][$blok];
			@$sdhapnnbi[$divisi]     +=$hapnnbi[$divisi][$tt][$blok];
			@$sdhkbi[$divisi]        +=$hkbi[$divisi][$tt][$blok];
			@$sdhksbi[$divisi]       +=$hksbi[$divisi][$tt][$blok];
			@$sdhkbgtbi[$divisi]     +=$hkbgtbi[$divisi][$tt][$blok];
			@$sdhkbgtsbi[$divisi]    +=$hkbgtsbi[$divisi][$tt][$blok];
			@$sdhkbgtthn[$divisi]    +=$hkbgtthn[$divisi][$tt][$blok];
			@$sdjjgpnnbi[$divisi]    +=$jjgpnnbi[$divisi][$tt][$blok];
			@$sdjjgpnnsbi[$divisi]   +=$jjgpnnsbi[$divisi][$tt][$blok];
			@$sdjjgkrmbi[$divisi]    +=$jjgkrmbi[$divisi][$tt][$blok];
			@$sdjjgkrmsbi[$divisi]   +=$jjgkrmsbi[$divisi][$tt][$blok];
			@$sdkgkrmbi[$divisi]     +=$kgkrmbi[$divisi][$tt][$blok];
			@$sdkgkrmsbi[$divisi]    +=$kgkrmsbi[$divisi][$tt][$blok];
			@$sdkgbgtbi[$divisi]     +=$kgbgtbi[$divisi][$tt][$blok];
			@$sdkgbgtsbi[$divisi]    +=$kgbgtsbi[$divisi][$tt][$blok];
			@$sdkgbgtthn[$divisi]    +=$kgbgtthn[$divisi][$tt][$blok];
			@$sdrprealbi[$divisi]    +=$rprealbi[$divisi][$tt][$blok];
			@$sdrprealsbi[$divisi]   +=$rprealsbi[$divisi][$tt][$blok];
			@$sdrpbgtbi[$divisi]     +=$rpbgtbi[$divisi][$tt][$blok];
			@$sdrpbgtsbi[$divisi]    +=$rpbgtsbi[$divisi][$tt][$blok];
			@$sdrpbgtthn[$divisi]    +=$rpbgtthn[$divisi][$tt][$blok];
			@$sdrprealuphbi[$divisi] +=$rprealuphbi[$divisi][$tt][$blok];
			@$sdrprealuphsbi[$divisi]+=$rprealuphsbi[$divisi][$tt][$blok];
			@$sdrpbgtuphbi[$divisi]  +=$rpbgtuphbi[$divisi][$tt][$blok];
			@$sdrpbgtuphsbi[$divisi] +=$rpbgtuphsbi[$divisi][$tt][$blok];
			@$sdrpbgtuphthn[$divisi] +=$rpbgtuphthn[$divisi][$tt][$blok];

			#grandtotal
			@$gtluas        +=$luas[$divisi][$tt][$blok];
			@$gtpokok       +=$pokok[$divisi][$tt][$blok];
			@$gthapnnbi     +=$hapnnbi[$divisi][$tt][$blok];
			@$gthkbi        +=$hkbi[$divisi][$tt][$blok];
			@$gthksbi       +=$hksbi[$divisi][$tt][$blok];
			@$gthkbgtbi     +=$hkbgtbi[$divisi][$tt][$blok];
			@$gthkbgtsbi    +=$hkbgtsbi[$divisi][$tt][$blok];
			@$gthkbgtthn    +=$hkbgtthn[$divisi][$tt][$blok];
			@$gtjjgpnnbi    +=$jjgpnnbi[$divisi][$tt][$blok];
			@$gtjjgpnnsbi   +=$jjgpnnsbi[$divisi][$tt][$blok];
			@$gtjjgkrmbi    +=$jjgkrmbi[$divisi][$tt][$blok];
			@$gtjjgkrmsbi   +=$jjgkrmsbi[$divisi][$tt][$blok];
			@$gtkgkrmbi     +=$kgkrmbi[$divisi][$tt][$blok];
			@$gtkgkrmsbi    +=$kgkrmsbi[$divisi][$tt][$blok];
			@$gtkgbgtbi     +=$kgbgtbi[$divisi][$tt][$blok];
			@$gtkgbgtsbi    +=$kgbgtsbi[$divisi][$tt][$blok];
			@$gtkgbgtthn    +=$kgbgtthn[$divisi][$tt][$blok];
			@$gtrprealbi    +=$rprealbi[$divisi][$tt][$blok];
			@$gtrprealsbi   +=$rprealsbi[$divisi][$tt][$blok];
			@$gtrpbgtbi     +=$rpbgtbi[$divisi][$tt][$blok];
			@$gtrpbgtsbi    +=$rpbgtsbi[$divisi][$tt][$blok];
			@$gtrpbgtthn    +=$rpbgtthn[$divisi][$tt][$blok];
			@$gtrprealuphbi +=$rprealuphbi[$divisi][$tt][$blok];
			@$gtrprealuphsbi+=$rprealuphsbi[$divisi][$tt][$blok];
			@$gtrpbgtuphbi  +=$rpbgtuphbi[$divisi][$tt][$blok];
			@$gtrpbgtuphsbi +=$rpbgtuphsbi[$divisi][$tt][$blok];
			@$gtrpbgtuphthn +=$rpbgtuphthn[$divisi][$tt][$blok];

			
			@$trprealbi+=$rprealbi[$divisi][$tt][$blok];
			@$trprealsbi+=$rprealsbi[$divisi][$tt][$blok];
		}
		$tab.="</tr>";
		$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#DCFEDA>";
		$tab.="<td align=center></td>";
		$tab.="<td align=left colspan=2>Sub Total ".$tt."</td>";
		$tab.="<td align=right>".@nantozero($stluas[$divisi][$tt])."</td>";
		$tab.="<td></td>";
		$tab.="<td align=right>".@nantozero($stpokok[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stpokok[$divisi][$tt]/$stluas[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($sthapnnbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($sthapnnbi[$divisi][$tt]/$stluas[$divisi][$tt],2)."</td>";
		$tab.="<td align=right>".@nantozero($sthkbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($sthksbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($sthkbgtbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($sthkbgtsbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($sthkbgtthn[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stjjgpnnbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stjjgpnnsbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stjjgkrmbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stjjgkrmsbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero(abs((($stjjgkrmsbi[$divisi][$tt]-$stjjgpnnsbi[$divisi][$tt])/$stjjgkrmsbi[$divisi][$tt])*100),2)."</td>";
		$tab.="<td align=right>".@nantozero($stkgkrmbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stkgkrmsbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stkgbgtbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stkgbgtsbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stkgbgtthn[$divisi][$tt])."</td>";
		#KG/HK
		if($sthkbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($stkgkrmbi[$divisi][$tt]/$sthkbi[$divisi][$tt])."</td>";
		}
		if($sthksbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($stkgkrmsbi[$divisi][$tt]/$sthksbi[$divisi][$tt])."</td>";
		}
		
		#JJG/HK
		if($sthkbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($stjjgpnnbi[$divisi][$tt]/$sthkbi[$divisi][$tt])."</td>";
		}
		if($sthksbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($stjjgpnnsbi[$divisi][$tt]/$sthksbi[$divisi][$tt])."</td>";
		}
		#BJR
		if($stjjgkrmbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($stkgkrmbi[$divisi][$tt]/$stjjgkrmbi[$divisi][$tt],2)."</td>";
		}
		if($stjjgkrmsbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($stkgkrmsbi[$divisi][$tt]/$stjjgkrmsbi[$divisi][$tt],2)."</td>";
		}
		#Yield
		$tab.="<td align=right>".@nantozero($stkgkrmbi[$divisi][$tt]/$stluas[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stkgkrmsbi[$divisi][$tt]/$stluas[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stkgbgtbi[$divisi][$tt]/$stluas[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stkgbgtsbi[$divisi][$tt]/$stluas[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($stkgbgtthn[$divisi][$tt]/$stluas[$divisi][$tt])."</td>";
		
		$cr=" style='cursor:pointer;text-align:right;color:blue' onclick=mengumpul('".$divisi."','".$prd."','real','".$tt."')";
		$cb=" style='cursor:pointer;text-align:right;color:blue' onclick=mengumpul('".$divisi."','".$prd."','bgt','".$tt."')";
		$tab.="<td ".$cr.">".@nantozero($strprealbi[$divisi][$tt])."</td>";
		$tab.="<td ".$cr.">".@nantozero($strprealsbi[$divisi][$tt])."</td>";
		$tab.="<td ".$cb.">".@nantozero($strpbgtbi[$divisi][$tt])."</td>";
		$tab.="<td ".$cb.">".@nantozero($strpbgtsbi[$divisi][$tt])."</td>";
		$tab.="<td ".$cb.">".@nantozero($strpbgtthn[$divisi][$tt])."</td>";
		
		#Biaya / Kg
		if($stkgkrmbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($strprealbi[$divisi][$tt]/$stkgkrmbi[$divisi][$tt],2)."</td>";
		}
		if($stkgkrmsbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($strprealsbi[$divisi][$tt]/$stkgkrmsbi[$divisi][$tt],2)."</td>";
		}
		if($stkgbgtbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($strpbgtbi[$divisi][$tt]/$stkgbgtbi[$divisi][$tt],2)."</td>";
		}
		if($stkgbgtsbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($strpbgtsbi[$divisi][$tt]/$stkgbgtsbi[$divisi][$tt],2)."</td>";
		}
		if($stkgbgtthn[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($strpbgtthn[$divisi][$tt]/$stkgbgtthn[$divisi][$tt],2)."</td>";
		}
		
		$tab.="<td align=right>".@nantozero($strprealuphbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($strprealuphsbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($strpbgtuphbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($strpbgtuphsbi[$divisi][$tt])."</td>";
		$tab.="<td align=right>".@nantozero($strpbgtuphthn[$divisi][$tt])."</td>";
		
		#Rata2 Upah/HK
		if($sthkbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($strprealuphbi[$divisi][$tt]/$sthkbi[$divisi][$tt])."</td>";
		}
		if($sthksbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($strprealuphsbi[$divisi][$tt]/$sthksbi[$divisi][$tt])."</td>";
		}
		if($sthkbgtbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($strpbgtuphbi[$divisi][$tt]/$sthkbgtbi[$divisi][$tt])."</td>";
		}
		if($sthkbgtsbi[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($strpbgtuphsbi[$divisi][$tt]/$sthkbgtsbi[$divisi][$tt])."</td>";
		}
		if($sthkbgtthn[$divisi][$tt]==0){
			$tab.="<td align=right></td>";
		}else{
			$tab.="<td align=right>".@nantozero($strpbgtuphthn[$divisi][$tt]/$sthkbgtthn[$divisi][$tt])."</td>";
		}
	}
	$tab.="</tr>";
	$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#C4FCA7>";
	$tab.="<td align=left colspan=3>Sub Total ".$divisi."</td>";
	$tab.="<td align=right>".@nantozero($sdluas[$divisi])."</td>";
	$tab.="<td></td>";
	$tab.="<td align=right>".@nantozero($sdpokok[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdpokok[$divisi]/$sdluas[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdhapnnbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdhapnnbi[$divisi]/$sdluas[$divisi],2)."</td>";
	$tab.="<td align=right>".@nantozero($sdhkbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdhksbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdhkbgtbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdhkbgtsbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdhkbgtthn[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdjjgpnnbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdjjgpnnsbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdjjgkrmbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdjjgkrmsbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero(abs((($sdjjgkrmsbi[$divisi]-$sdjjgpnnsbi[$divisi])/$sdjjgkrmsbi[$divisi])*100),2)."</td>";
	$tab.="<td align=right>".@nantozero($sdkgkrmbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdkgkrmsbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdkgbgtbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdkgbgtsbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdkgbgtthn[$divisi])."</td>";
	#KG/HK
	if($sdhkbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdkgkrmbi[$divisi]/$sdhkbi[$divisi])."</td>";
	}
	if($sdhksbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdkgkrmsbi[$divisi]/$sdhksbi[$divisi])."</td>";
	}
	
	#JJG/HK
	if($sdhkbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdjjgpnnbi[$divisi]/$sdhkbi[$divisi])."</td>";
	}
	if($sdhksbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdjjgpnnsbi[$divisi]/$sdhksbi[$divisi])."</td>";
	}
	#BJR
	if($sdjjgkrmbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdkgkrmbi[$divisi]/$sdjjgkrmbi[$divisi],2)."</td>";
	}
	if($sdjjgkrmsbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdkgkrmsbi[$divisi]/$sdjjgkrmsbi[$divisi],2)."</td>";
	}
	#Yield
	$tab.="<td align=right>".@nantozero($sdkgkrmbi[$divisi]/$sdluas[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdkgkrmsbi[$divisi]/$sdluas[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdkgbgtbi[$divisi]/$sdluas[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdkgbgtsbi[$divisi]/$sdluas[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdkgbgtthn[$divisi]/$sdluas[$divisi])."</td>";
	
	$cr=" style='cursor:pointer;text-align:right;color:blue' onclick=mengumpul('".$divisi."','".$prd."','real','')";
	$cb=" style='cursor:pointer;text-align:right;color:blue' onclick=mengumpul('".$divisi."','".$prd."','bgt','')";
	$tab.="<td ".$cr.">".@nantozero($sdrprealbi[$divisi])."</td>";
	$tab.="<td ".$cr.">".@nantozero($sdrprealsbi[$divisi])."</td>";
	$tab.="<td ".$cb.">".@nantozero($sdrpbgtbi[$divisi])."</td>";
	$tab.="<td ".$cb.">".@nantozero($sdrpbgtsbi[$divisi])."</td>";
	$tab.="<td ".$cb.">".@nantozero($sdrpbgtthn[$divisi])."</td>";
	
	#Biaya / Kg
	if($sdkgkrmbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdrprealbi[$divisi]/$sdkgkrmbi[$divisi],2)."</td>";
	}
	if($sdkgkrmsbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdrprealsbi[$divisi]/$sdkgkrmsbi[$divisi],2)."</td>";
	}
	if($sdkgbgtbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdrpbgtbi[$divisi]/$sdkgbgtbi[$divisi],2)."</td>";
	}
	if($sdkgbgtsbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdrpbgtsbi[$divisi]/$sdkgbgtsbi[$divisi],2)."</td>";
	}
	if($sdkgbgtthn[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdrpbgtthn[$divisi]/$sdkgbgtthn[$divisi],2)."</td>";
	}
	
	$tab.="<td align=right>".@nantozero($sdrprealuphbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdrprealuphsbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdrpbgtuphbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdrpbgtuphsbi[$divisi])."</td>";
	$tab.="<td align=right>".@nantozero($sdrpbgtuphthn[$divisi])."</td>";
	
	#Rata2 Upah/HK
	if($sdhkbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdrprealuphbi[$divisi]/$sdhkbi[$divisi])."</td>";
	}
	if($sdhksbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdrprealuphsbi[$divisi]/$sdhksbi[$divisi])."</td>";
	}
	if($sdhkbgtbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdrpbgtuphbi[$divisi]/$sdhkbgtbi[$divisi])."</td>";
	}
	if($sdhkbgtsbi[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdrpbgtuphsbi[$divisi]/$sdhkbgtsbi[$divisi])."</td>";
	}
	if($sdhkbgtthn[$divisi]==0){
		$tab.="<td align=right></td>";
	}else{
		$tab.="<td align=right>".@nantozero($sdrpbgtuphthn[$divisi]/$sdhkbgtthn[$divisi])."</td>";
	}
}

if($rprealnbbi>0 or $rprealnbsbi>0){
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>";
	$tab.="<td align=center>".$kdorg."</td>";
	$tab.="<td colspan=34><i>Transaksi jurnal tidak memiliki blok</i></td>";

	$cr=" style='cursor:pointer;text-align:right;color:blue' onclick=mengumpul('".$kdorg."','".$prd."','real','nonblok')";
	$tab.="<td ".$cr.">".nantozero($rprealnbbi)."</td>";
	$tab.="<td ".$cr.">".nantozero($rprealnbsbi)."</td>";
	$tab.="<td colspan=18></td>";	
}

$tab.="<tr class=rowcontent style=font-weight:bold;background-color:#09F472>";
$tab.="<td align=left colspan=3>Grand Total</td>";
$tab.="<td align=right>".@nantozero($gtluas)."</td>";
$tab.="<td></td>";
$tab.="<td align=right>".@nantozero($gtpokok)."</td>";
$tab.="<td align=right>".@nantozero($gtpokok/$gtluas)."</td>";
$tab.="<td align=right>".@nantozero($gthapnnbi)."</td>";
$tab.="<td align=right>".@nantozero($gthapnnbi/$gtluas,2)."</td>";
$tab.="<td align=right>".@nantozero($gthkbi)."</td>";
$tab.="<td align=right>".@nantozero($gthksbi)."</td>";
$tab.="<td align=right>".@nantozero($gthkbgtbi)."</td>";
$tab.="<td align=right>".@nantozero($gthkbgtsbi)."</td>";
$tab.="<td align=right>".@nantozero($gthkbgtthn)."</td>";
$tab.="<td align=right>".@nantozero($gtjjgpnnbi)."</td>";
$tab.="<td align=right>".@nantozero($gtjjgpnnsbi)."</td>";
$tab.="<td align=right>".@nantozero($gtjjgkrmbi)."</td>";
$tab.="<td align=right>".@nantozero($gtjjgkrmsbi)."</td>";
$tab.="<td align=right>".@nantozero(abs((($gtjjgkrmsbi-$gtjjgpnnsbi)/$gtjjgkrmsbi)*100),2)."</td>";
$tab.="<td align=right>".@nantozero($gtkgkrmbi)."</td>";
$tab.="<td align=right>".@nantozero($gtkgkrmsbi)."</td>";
$tab.="<td align=right>".@nantozero($gtkgbgtbi)."</td>";
$tab.="<td align=right>".@nantozero($gtkgbgtsbi)."</td>";
$tab.="<td align=right>".@nantozero($gtkgbgtthn)."</td>";
#KG/HK
if($gthkbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtkgkrmbi/$gthkbi)."</td>";
}
if($gthksbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtkgkrmsbi/$gthksbi)."</td>";
}

#JJG/HK
if($gthkbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtjjgpnnbi/$gthkbi)."</td>";
}
if($gthksbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtjjgpnnsbi/$gthksbi)."</td>";
}
#BJR
if($gtjjgkrmbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtkgkrmbi/$gtjjgkrmbi,2)."</td>";
}
if($gtjjgkrmsbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtkgkrmsbi/$gtjjgkrmsbi,2)."</td>";
}
#Yield
$tab.="<td align=right>".@nantozero($gtkgkrmbi/$gtluas)."</td>";
$tab.="<td align=right>".@nantozero($gtkgkrmsbi/$gtluas)."</td>";
$tab.="<td align=right>".@nantozero($gtkgbgtbi/$gtluas)."</td>";
$tab.="<td align=right>".@nantozero($gtkgbgtsbi/$gtluas)."</td>";
$tab.="<td align=right>".@nantozero($gtkgbgtthn/$gtluas)."</td>";

$cr=" style='cursor:pointer;text-align:right;color:blue' onclick=mengumpul('".$kdorg."','".$prd."','real','')";
$cb=" style='cursor:pointer;text-align:right;color:blue' onclick=mengumpul('".$kdorg."','".$prd."','bgt','')";
$tab.="<td ".$cr.">".@nantozero($gtrprealbi+$rprealnbbi)."</td>";
$tab.="<td ".$cr.">".@nantozero($gtrprealsbi+$rprealnbsbi)."</td>";
$tab.="<td ".$cb.">".@nantozero($gtrpbgtbi)."</td>";
$tab.="<td ".$cb.">".@nantozero($gtrpbgtsbi)."</td>";
$tab.="<td ".$cb.">".@nantozero($gtrpbgtthn)."</td>";

#Biaya / Kg
if($gtkgkrmbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero(($gtrprealbi+$rprealnbbi)/$gtkgkrmbi,2)."</td>";
}
if($gtkgkrmsbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero(($gtrprealsbi+$rprealnbsbi)/$gtkgkrmsbi,2)."</td>";
}
if($gtkgbgtbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtrpbgtbi/$gtkgbgtbi,2)."</td>";
}
if($gtkgbgtsbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtrpbgtsbi/$gtkgbgtsbi,2)."</td>";
}
if($gtkgbgtthn==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtrpbgtthn/$gtkgbgtthn,2)."</td>";
}

$tab.="<td align=right>".@nantozero($gtrprealuphbi)."</td>";
$tab.="<td align=right>".@nantozero($gtrprealuphsbi)."</td>";
$tab.="<td align=right>".@nantozero($gtrpbgtuphbi)."</td>";
$tab.="<td align=right>".@nantozero($gtrpbgtuphsbi)."</td>";
$tab.="<td align=right>".@nantozero($gtrpbgtuphthn)."</td>";

#Rata2 Upah/HK
if($gthkbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtrprealuphbi/$gthkbi)."</td>";
}
if($gthksbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtrprealuphsbi/$gthksbi)."</td>";
}
if($gthkbgtbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtrpbgtuphbi/$gthkbgtbi)."</td>";
}
if($gthkbgtsbi==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtrpbgtuphsbi/$gthkbgtsbi)."</td>";
}
if($gthkbgtthn==0){
	$tab.="<td align=right></td>";
}else{
	$tab.="<td align=right>".@nantozero($gtrpbgtuphthn/$gthkbgtthn)."</td>";
}

$tab.="</tbody></table>";

	
switch ($proses) {
######PREVIEW
    case 'preview':
        echo $tab;
        break;

######EXCEL	
    case 'excel':
        $nop_ = strtolower(getMenu('kebun_2cropx'));
        if (strlen($tab) > 0) {
			$tab.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab)) {
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

function nantozero($e,$i=0){
	if(is_nan($e)){
		$e=0;
	}else{
		$e=$e;
	}
	return number_format($e,$i);
}
?>