<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses= checkPostGet('proses', '');
$kdorg = checkPostGet('kdorg', '');
$pt    = checkPostGet('pt', '');
$tt    = checkPostGet('tt', '');
$ip    = checkPostGet('ip', '');
$divisi= checkPostGet('divisi', '');
$prd   = checkPostGet('prd', '');
$tipe  = checkPostGet('tipe', '');

$arrbi    = explode('-',$prd); 
$tahun    = $arrbi[0]; 
$bulan    = $arrbi[1];
$periode1 = $tahun."-01";
$periode2 = $prd;
if($kdorg==''){exit("warning : Kode unit harus di pilih.");}

$where='';$where2='';$where_spb='';
if($kdorg!=''){
	$where=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$where2=" and substr(a.millcode,1,4) ='".$kdorg."'";
	$where_spb=" and a.kodeorg ='".$kdorg."'";
}

$wh="";$wh2="";$whB="";$wh_spb="";$wh_bgt=$wh_bgtrp='';
if($divisi!=''){
	$wh.=" and a.kodeblok like '".$divisi."%'";
	$whB.=" and a.kodeblok like '".$divisi."%'";
	$wh_spb.=" and a.divisi like '".$divisi."%'";
	$wh_bgt.=" and a.divisi like '".$divisi."%'";
	$wh_bgtrp.=" and a.kodeorg like '".$divisi."%'";
	
	$whereakun = " and substr(noakun,1,2) in ('63')";
	$arrkode=array('pml'=>'pml');
}else{	
	$whereakun = " and (substr(noakun,1,2) in ('63')  or noakun like '7%')";
	$arrkode=array('pml'=>'pml','umm'=>'umm');
}

#=============== mari kita mulai dari sini ===============#

$nmakun = makeOption($dbname,'keu_5akun','noakun,namaakun');

$arrakun=array();
$str = "select * from " . $dbname . ".keu_5akun  where 1=1 ".$whereakun." and namaakun not like '%NON AKTIF%'"; 
$res = fetchdata($str);
foreach($res as $bar){
	
	if(substr($bar['noakun'],0,2)=='63'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['pml'][$bar['noakun']] = $bar['noakun'];
		}
		if(strlen($bar['noakun'])=='7'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
		}
	}
	
	if(substr($bar['noakun'],0,1)=='7'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['umm'][$bar['noakun']] = $bar['noakun'];
		}
		if(strlen($bar['noakun'])=='7'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['umm'][$bar['noakun']] = $bar['noakun'];
		}
	}
}


#ambil luas realisasi
$str = "select substr(tanggal,1,7) as periode, sum(tbsdiolah) as tbs, sum(oer) as cpo, sum(oerpk) as pk from " . $dbname . ".pabrik_produksi a  where 1=1 ".$where." and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' group by substr(tanggal,1,7)"; #exit("error".$str);
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['periode']==$periode2){
		@$prdrealbi += $bar['tbs'];
		@$cporealbi += $bar['cpo'];
		@$kerrealbi += $bar['pk'];
		@$pprealbi += $bar['cpo']+$bar['pk'];
	}
	@$prdrealsdbi += $bar['tbs'];
	@$cporealsdbi += $bar['cpo'];
	@$kerrealsdbi += $bar['pk'];
	@$pprealsdbi += $bar['cpo']+$bar['pk'];
	
}

#ambil prd bgt
$e="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="olah".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";

$cpo="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="kgcpo".addZero($i,2);
    if($i<intval($bulan)){$cpo.=$r."+";}else{$cpo.=$r;}
}
$cpo.=")";

$ker="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="kgker".addZero($i,2);
    if($i<intval($bulan)){$ker.=$r."+";}else{$ker.=$r;}
}
$ker.=")";


$str=" select millcode,".$e." as sdbi,olah".$bulan." as bi,kgolah as kgsetahun, 
".$cpo." as cposdbi, kgcpo".$bulan." as cpobi,kgcpo as cpokgsetahun,
".$ker." as kersdbi, kgker".$bulan." as kerbi,kgkernel as kerkgsetahun
from ".$dbname.".bgt_produksi_pks_vw a where 1=1 ".$where2." and tahunbudget = '".$tahun."'";
$res = fetchdata($str);
foreach($res as $bar){
	@$prdbgtbi += $bar['bi'];
	@$prdbgtsdbi += $bar['sdbi'];
	@$prdbgtthn += $bar['kgsetahun'];
	
	@$cpobgtbi += $bar['cpobi'];
	@$cpobgtsdbi += $bar['cposdbi'];
	@$cpobgtthn += $bar['cpokgsetahun'];
	
	@$kerbgtbi += $bar['kerbi'];
	@$kerbgtsdbi += $bar['kersdbi'];
	@$kerbgtthn += $bar['kerkgsetahun'];

	$ppbgtbi+= $bar['cpobi']+$bar['kerbi'];
	$ppbgtsdbi+= $bar['cposdbi']+$bar['kersdbi'];
	$ppbgtthn+= $bar['cpokgsetahun']+$bar['kerkgsetahun'];
}



#khusus kegiatan tanaman
$str = "select sum(jumlah) as jumlah,substr(noakun,1,5) as jobgroup, noakun, periode, kodeorg  
from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,2) in ('63')  ".$wh." ".$where." and periode between '".$periode1."' and  '".$periode2."' group by substr(noakun,1,5),noakun,periode"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['periode']==$periode2){
		#jobgroup => akun 5, jobcode => akun
		@$realbi[$bar['jobgroup']] += $bar['jumlah'];
		@$cdrealbi[$bar['noakun']] += $bar['jumlah'];
	}
	@$realsdbi[$bar['jobgroup']] += $bar['jumlah'];
	@$cdrealsdbi[$bar['noakun']] += $bar['jumlah'];
	
	#noakun
	if(substr($bar['noakun'],0,3)=='63'){		
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$listakun['pml'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
	}
	
	
	if($divisi!=''){
		if(substr($bar['noakun'],0,1)!='7'){			
			$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
			$arrakun7[$bar['noakun']] = $bar['noakun'];
		}
	}else{		
		if(substr($bar['noakun'],0,1)=='7'){
			$listakun['umm'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);		
			$cdarrakun['umm'][$bar['noakun']] = $bar['noakun'];
		}
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$arrakun7[$bar['noakun']] = $bar['noakun'];
	}
	#end akun
}

$str = "select sum(jumlah) as jumlah,substr(noakun,1,5) as jobgroup, noakun, periode, kodeorg  
from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,1) in ('7') ".$where." and periode between '".$periode1."' and  '".$periode2."' group by substr(noakun,1,5),noakun,periode"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['periode']==$periode2){
		#jobgroup => akun 5, jobcode => akun
		@$realbi[$bar['jobgroup']] += $bar['jumlah'];
		@$cdrealbi[$bar['noakun']] += $bar['jumlah'];
	}
	@$realsdbi[$bar['jobgroup']] += $bar['jumlah'];
	@$cdrealsdbi[$bar['noakun']] += $bar['jumlah'];
	
	#noakun
	if(substr($bar['noakun'],0,3)=='63'){		
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$listakun['pml'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
	}
	if($divisi!=''){
		if(substr($bar['noakun'],0,1)!='7'){
			$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
			$arrakun7[$bar['noakun']] = $bar['noakun'];
		}
	}else{		
		if(substr($bar['noakun'],0,1)=='7'){
			$listakun['umm'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);		
			$cdarrakun['umm'][$bar['noakun']] = $bar['noakun'];
		}
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$arrakun7[$bar['noakun']] = $bar['noakun'];
	}
	#end akun
}

$e="("; $s="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
}
$e.=")"; $s.=")";

$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";

#ini khusus budget kebun
$str=" select substr(noakun,1,5) as jobgroup,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whereakun."";
$res = fetchdata($str);
foreach($res as $bar){
	@$bgtbi[$bar['jobgroup']] += $bar['bi'];
	@$bgtsdbi[$bar['jobgroup']] += $bar['sdbi'];
	@$bgtthn[$bar['jobgroup']] += $bar['rupiah'];
	
	@$cdbgtbi[$bar['noakun']] += $bar['bi'];
	@$cdbgtsdbi[$bar['noakun']] += $bar['sdbi'];
	@$cdbgtthn[$bar['noakun']] += $bar['rupiah'];
	
	#noakun
	if(substr($bar['noakun'],0,3)=='63'){		
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$listakun['pml'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
	}
	if($divisi!=''){
		if(substr($bar['noakun'],0,1)!='7'){
			$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
			$arrakun7[$bar['noakun']] = $bar['noakun'];
		}
	}else{		
		if(substr($bar['noakun'],0,1)=='7'){
			$listakun['umm'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);		
			$cdarrakun['umm'][$bar['noakun']] = $bar['noakun'];
		}
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$arrakun7[$bar['noakun']] = $bar['noakun'];
	}
	#end akun
}

#ini khusus budget UMUM
$str=" select substr(noakun,1,5) as jobgroup,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." and kodebudget='UMUM' and tahunbudget = '".$tahun."' ".$whereakun."";
$res = fetchdata($str);
foreach($res as $bar){
	@$bgtbi[$bar['jobgroup']] += $bar['bi'];
	@$bgtsdbi[$bar['jobgroup']] += $bar['sdbi'];
	@$bgtthn[$bar['jobgroup']] += $bar['rupiah'];
	
	@$cdbgtbi[$bar['noakun']] += $bar['bi'];
	@$cdbgtsdbi[$bar['noakun']] += $bar['sdbi'];
	@$cdbgtthn[$bar['noakun']] += $bar['rupiah'];
	
	#noakun
	if(substr($bar['noakun'],0,3)=='63'){		
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$listakun['pml'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
	}
	if($divisi!=''){
		if(substr($bar['noakun'],0,1)!='7'){
			$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
			$arrakun7[$bar['noakun']] = $bar['noakun'];
		}
	}else{		
		if(substr($bar['noakun'],0,1)=='7'){
			$listakun['umm'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);		
			$cdarrakun['umm'][$bar['noakun']] = $bar['noakun'];
		}
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$arrakun7[$bar['noakun']] = $bar['noakun'];
	}
	#end akun
}

if ($proses == 'excel') {
	$arrtipe=array("group"=>"Per Job Group","code"=>"Per Nomor Akun");
	$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	if($kdorg!=''){$xkdorg=$nmorg[$kdorg];}else{$xkdorg=$_SESSION['lang']['all'];}
	if($divisi!=''){$xdivisi=$nmorg[$divisi];}else{$xdivisi=$_SESSION['lang']['all'];}
	if($tt!=''){$xtt=$tt;}else{$xtt=$_SESSION['lang']['all'];}
	if($ip!=''){$xip=$ip;}else{$xip=$_SESSION['lang']['all'];}
	
	
	$tab="<table class=sortable cellspacing=1 width=100%>";
	$tab.="<tr><td align=center colspan=22>ANALISA BIAYA PABRIK (".$arrtipe[$tipe].")</td>";
	$tab.="<tr><td align=center colspan=22>".$_SESSION['lang']['unit'] . " : ".$xkdorg."</td></tr>";
	$tab.="<tr><td align=center colspan=22>".$_SESSION['lang']['station'] . " : ".$xdivisi."</td></tr>";
	$tab.="<tr><td align=center colspan=22>".$_SESSION['lang']['periode'] . " : ".$prd."</td></tr>";
    $tab.="<table class=sortable cellspacing=1 border=1>";
} else {
	$tab.="<div class='menu'>
			<div id='btninscmnt' class='menu-item'>Insert Comment</div>
			<div id='btnshowcmn' class='menu-item'>Show Comment</div>
			<div id='btnreloadframe' class='menu-item'>Reload Frame</div>
		</div>";
    $tab.= "<table class=sortable cellpadding=5 cellspacing=1>";
}

$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan='6'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='6'>".$_SESSION['lang']['kode']."</th>
            <th align=center rowspan='2'></th>
            <th align=center rowspan='1' colspan='8'>".$_SESSION['lang']['bulanini']."</th>
            <th align=center rowspan='1' colspan='8'>".$_SESSION['lang']['sdbulanini']."</th>
            <th align=center rowspan='2' colspan='3'>".$_SESSION['lang']['tahunanggaran']."</th>
        </tr>
        <tr>
            <th align=center colspan='3'>".$_SESSION['lang']['realisasi']."</th>  
            <th align=center colspan='3'>".$_SESSION['lang']['budget']."</th>  
            <th align=center colspan='2'>%</th>
			<th align=center colspan='3'>".$_SESSION['lang']['realisasi']."</th>  
            <th align=center colspan='3'>".$_SESSION['lang']['budget']."</th>  
            <th align=center colspan='2'>%</th>
        </tr>
        <tr>
            <th align=left style='padding: 5px 5px 5px 30px;'>".$_SESSION['lang']['tbsdiolah']." (Kg)</th>  
            <th align=center colspan='3'>".@nantozero($prdrealbi,0)."</th>  
            <th align=center colspan='3'>".@nantozero($prdbgtbi,0)."</th>  
            <th align=center colspan='2'>".@nantozero(($prdrealbi-$prdbgtbi)/$prdbgtbi*100,2)."</th>  
            <th align=center colspan='3'>".@nantozero($prdrealsdbi,0)."</th>  
            <th align=center colspan='3'>".@nantozero($prdbgtsdbi,0)."</th>  
            <th align=center colspan='2'>".@nantozero(($prdrealsdbi-$prdbgtsdbi)/$prdbgtsdbi*100,2)."</th>  
            <th align=center colspan='3'>".@nantozero($prdbgtthn,0)."</th>  
        </tr>
		<tr>
            <th align=left style='padding: 5px 5px 5px 30px;'>".$_SESSION['lang']['produksicpo']." (Kg)</th>  
            <th align=center>".@nantozero($cporealbi,0)."</th>  
            <th align=center colspan='2' style=color:orange; title=OER>".@nantozero($cporealbi/$prdrealbi*100,2)."</th>  
			
            <th align=center>".@nantozero($cpobgtbi,0)."</th>  
            <th align=center colspan='2' style=color:orange; title=OER>".@nantozero($cpobgtbi/$prdbgtbi*100,2)."</th>  
            <th align=center colspan='2'>".@nantozero(($cporealbi-$cpobgtbi)/$cpobgtbi*100,2)."</th>  
			
            <th align=center>".@nantozero($cporealsdbi,0)."</th>  
            <th align=center colspan='2' style=color:orange; title=OER>".@nantozero($cporealsdbi/$prdrealsdbi*100,2)."</th>  
			
            <th align=center>".@nantozero($cpobgtsdbi,0)."</th>  
            <th align=center colspan='2' colspan='2' style=color:orange; title=OER>".@nantozero($cpobgtsdbi/$prdbgtsdbi*100,2)."</th>  
            <th align=center colspan='2'>".@nantozero(($cporealsdbi-$cpobgtsdbi)/$cpobgtsdbi*100,2)."</th>  
			
            <th align=center>".@nantozero($cpobgtthn,0)."</th>  
            <th align=center colspan='2' style=color:orange; title=OER>".@nantozero($cpobgtthn/$prdbgtthn*100,2)."</th>  
        </tr>
		<tr>
            <th align=left style='padding: 5px 5px 5px 30px;'>".$_SESSION['lang']['produksikernel']." (Kg)</th>  
            <th align=center>".@nantozero($kerrealbi,0)."</th> 
			<th align=center colspan='2' style=color:cyan; title=KER>".@nantozero($kerrealbi/$prdrealbi*100,2)."</th>  	
			
            <th align=center>".@nantozero($kerbgtbi,0)."</th>  
			<th align=center colspan='2' style=color:cyan; title=KER>".@nantozero($kerbgtbi/$prdbgtbi*100,2)."</th>  	
            <th align=center colspan='2'>".@nantozero(($kerrealbi-$kerbgtbi)/$kerbgtbi*100,2)."</th>  
			
            <th align=center>".@nantozero($kerrealsdbi,0)."</th>  
			<th align=center colspan='2' style=color:cyan; title=KER>".@nantozero($kerrealsdbi/$prdrealsdbi*100,2)."</th>  	
			
            <th align=center>".@nantozero($kerbgtsdbi,0)."</th>  
			<th align=center colspan='2' style=color:cyan; title=KER>".@nantozero($kerbgtsdbi/$prdbgtsdbi*100,2)."</th>  	
            <th align=center colspan='2'>".@nantozero(($kerrealsdbi-$kerbgtsdbi)/$kerbgtsdbi*100,2)."</th>  
			
            <th align=center>".@nantozero($kerbgtthn,0)."</th>  
			<th align=center colspan='2' style=color:cyan; title=KER>".@nantozero($kerbgtthn/$prdbgtthn*100,2)."</th>  	
        </tr>
		<tr>
            <th align=left style='padding: 5px 5px 5px 30px;'>Palm Product (Kg)</th>  
            <th align=center>".@nantozero($kerrealbi+$cporealbi,0)."</th> 
			<th align=center colspan='2' style=color:cyan; title=PP>".@nantozero(($kerrealbi+$cporealbi)/$prdrealbi*100,2)."</th>  	
			
            <th align=center>".@nantozero($kerbgtbi+$cpobgtbi,0)."</th>  
			<th align=center colspan='2' style=color:cyan; title=PP>".@nantozero(($kerbgtbi+$cpobgtbi)/$prdbgtbi*100,2)."</th>  	
            <th align=center colspan='2'>".@nantozero((($kerrealbi+$cporealbi)-($kerbgtbi+$cpobgtbi))/($kerbgtbi+$cpobgtbi)*100,2)."</th>  
			
            <th align=center>".@nantozero($kerrealsdbi+$cporealsdbi,0)."</th>  
			<th align=center colspan='2' style=color:cyan; title=PP>".@nantozero(($kerrealsdbi+$cporealsdbi)/$prdrealsdbi*100,2)."</th>  	
			
            <th align=center>".@nantozero(($kerbgtsdbi+$cpobgtsdbi),0)."</th>  
			<th align=center colspan='2' style=color:cyan; title=PP>".@nantozero(($kerbgtsdbi+$cpobgtsdbi)/$prdbgtsdbi*100,2)."</th>  	
            <th align=center colspan='2'>".@nantozero((($kerrealsdbi+$cporealsdbi)-($kerbgtsdbi+$cpobgtsdbi))/($kerbgtsdbi+$cpobgtsdbi)*100,2)."</th>  
			
            <th align=center>".@nantozero($kerbgtthn+$cpobgtthn,0)."</th>  
			<th align=center colspan='2' style=color:cyan; title=PP>".@nantozero(($kerbgtthn+$cpobgtthn)/$prdbgtthn*100,2)."</th>  	
        </tr>
    </thead>
 <tbody>";

$nmkode=array('pml'=>'Proses Pabrik','pnn'=>'Panen dan Pengangkutan','umm'=>'Tidak Langsung (Umum)');
$str = "select * from ".$dbname.".kebun_2commentreport a where 1=1 ".$whunit." and periode <= '".$prd."' and periode like '".$tahun."%'";
$res = fetchdata($str);
foreach($res as $bar){
	if($tipe=='group'){
		$substr='5';
	}elseif($tipe=='code'){
		$substr='7';
	}elseif($tipe=='keg'){
		$substr='9';
	}
	if($divisi!=''){
		$kdunit=substr($bar['unit'],0,6);
	}elseif($kdorg!=''){
		$kdunit=substr($bar['unit'],0,4);
	}else{
		$kdunit=$bar['pt'];
	}

	$showcomment[$kdunit][substr($bar['kegiatan'],0,$substr)][$bar['bi']][$bar['act']][]=array('id'=>$bar['id'],'user'=>$bar['createdby'],'comment'=>substr($bar['comment'],0,40));
	$showcomment[$kdunit][substr($bar['kegiatan'],0,$substr)]['sdbi'][$bar['act']][]=array('id'=>$bar['id'],'user'=>$bar['createdby'],'comment'=>substr($bar['comment'],0,40));
}

ksort($arrakun);
ksort($listakun);

switch ($tipe) {
	case'group':
		$tab.="<tr class=rowcontent style=background-color:#E8DAEF>
            <td></td>  
            <td align=center><i>Code</i></td>  
            <td align=center><i>Activity Group</i></td>  
            <td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Kg</i></td>
            <td align=center><i>Rp/PP</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Kg</i></td>
            <td align=center><i>Rp/PP</i></td>
            <td align=center><i>Kg</i></td>
            <td align=center><i>PP</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Kg</i></td>
            <td align=center><i>Rp/PP</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Kg</i></td>
            <td align=center><i>Rp/PP</i></td>
            <td align=center><i>Kg</i></td>
            <td align=center><i>PP</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Kg</i></td>
            <td align=center><i>Rp/PP</i></td>
        </tr>";
		foreach($arrkode as $kode){
			$no=0;
			foreach($arrakun as $akun){
				if(@$listakun[$kode][$akun]!=''){					
					$no++;
					$show="";
					if(substr($akun,0,1)=='7'){
						//$show=" style=display:none";
					}
					$tab.="<tr class=rowcontent ".$show.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$akun."</td>";
					$tab.="<td>".$nmakun[$akun]."</td>";
					#===== bi =====
					#actual
					if($divisi!=''){$korg=$divisi; $flag=0;}elseif($kdorg!=''){$korg=$kdorg; $flag=0;}else{$korg=$pt; $flag=2;}
					$click=$adacomment="";
					$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
					if(!empty($showcomment[$korg][$akun]['bi']['real'])){
						$adacomment="class=has_sign";  if($flag!=2){$flag='1';}
						$title=" title='".getKary($showcomment[$korg][$akun]['bi']['real'][0]['user'])."\n".$showcomment[$korg][$akun]['bi']['real'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
					}
					$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$korg."','".$prd."','bi','real')\"";
					
					$detrealbi="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','bi','real')";
					
					
					#budget
					if($divisi!=''){$korg=$divisi; $flag=0;}elseif($kdorg!=''){$korg=$kdorg; $flag=0;}else{$korg=$pt; $flag=2;}
					$click=$adacomment="";
					$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
					if(!empty($showcomment[$korg][$akun]['bi']['bgt'])){
						$adacomment="class=has_sign"; if($flag!=2){$flag='1';}
						$title=" title='".getKary($showcomment[$korg][$akun]['bi']['bgt'][0]['user'])."\n".$showcomment[$korg][$akun]['bi']['bgt'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
					}
					$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$korg."','".$prd."','bi','bgt')\"";					
					
					$detbgtbi="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','bi','budget')";
					
					$tab.="<td ".$detrealbi." align=right>".@nantozero($realbi[$akun])."</td>";
					
					@$rprealbisat=$realbi[$akun]/$prdrealbi;
					@$rprealbisatpp=$realbi[$akun]/$pprealbi;
					@$rpbgtbisat=$bgtbi[$akun]/$prdbgtbi;
					@$rpbgtbisatpp=$bgtbi[$akun]/$ppbgtbi;
					
					$tab.="<td align=right>".@nantozero($rprealbisat,2)."</td>";
					$tab.="<td align=right>".@nantozero($rprealbisatpp,2)."</td>";
					$tab.="<td ".$detbgtbi." align=right>".@nantozero($bgtbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtbisat,2)."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtbisatpp,2)."</td>";
					$persenbi=0;
					if($rprealbisat>0){						
						@$persenbi=(($rpbgtbisat-$rprealbisat)/$rpbgtbisat)*100;
					}
					$persenbipp=0;
					if($rprealbisatpp>0){						
						@$persenbipp=(($rpbgtbisatpp-$rprealbisatpp)/$rpbgtbisatpp)*100;
					}
					$c=$d="";
					if($persenbi<0){$c=" style=color:red;";}
					if($persenbipp<0){$d=" style=color:red;";}
					
					$tab.="<td align=right ".$c.">".@nantozero($persenbi,2)."</td>";
					$tab.="<td align=right ".$d.">".@nantozero($persenbipp,2)."</td>";
					#===== end bi =====
					#====== sdbi ======
					#actual
					if($divisi!=''){$korg=$divisi; $flag=0;}elseif($kdorg!=''){$korg=$kdorg; $flag=0;}else{$korg=$pt; $flag=2;}
					$click=$adacomment="";
					$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
					if(!empty($showcomment[$korg][$akun]['sdbi']['real'])){
						$adacomment="class=has_sign"; if($flag!=2){$flag='1';}
						$title=" title='".getKary($showcomment[$korg][$akun]['sdbi']['real'][0]['user'])."\n".$showcomment[$korg][$akun]['sdbi']['real'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
					}
					$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$korg."','".$prd."','sdbi','real')\"";
					
					$detrealsdbi="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','sdbi','real')";
					#budget
					if($divisi!=''){$korg=$divisi; $flag=0;}elseif($kdorg!=''){$korg=$kdorg; $flag=0;}else{$korg=$pt; $flag=2;}
					$click=$adacomment="";
					$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
					if(!empty($showcomment[$korg][$akun]['sdbi']['bgt'])){
						$adacomment="class=has_sign"; if($flag!=2){$flag='1';}
						$title=" title='".getKary($showcomment[$korg][$akun]['sdbi']['bgt'][0]['user'])."\n".$showcomment[$korg][$akun]['sdbi']['bgt'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
					}
					$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$korg."','".$prd."','sdbi','bgt')\"";
					
					$detbgtsdbi="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','sdbi','budget')";
					
					$tab.="<td align=right ".$detrealsdbi.">".@nantozero($realsdbi[$akun])."</td>";
					
					@$rprealsdbisat=$realsdbi[$akun]/$prdrealsdbi;
					@$rprealsdbisatpp=$realsdbi[$akun]/$pprealsdbi;
					@$rpbgtsdbisat=$bgtsdbi[$akun]/$prdbgtsdbi;
					@$rpbgtsdbisatpp=$bgtsdbi[$akun]/$ppbgtsdbi;
					$tab.="<td align=right>".@nantozero($rprealsdbisat,2)."</td>";
					$tab.="<td align=right>".@nantozero($rprealsdbisatpp,2)."</td>";
					$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($bgtsdbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtsdbisat,2)."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtsdbisatpp,2)."</td>";
					
					$persensdbi=0;
					if($rprealsdbisat>0){						
						@$persensdbi=(($rpbgtsdbisat-$rprealsdbisat)/$rpbgtsdbisat)*100;
					}
					$persensdbipp=0;
					if($rprealsdbisatpp>0){						
						@$persensdbipp=(($rpbgtsdbisatpp-$rprealsdbisatpp)/$rpbgtsdbisatpp)*100;
					}
					$c=$d="";
					if($persensdbi<0){$c=" style=color:red;";}
					if($persensdbipp<0){$d=" style=color:red;";}
					$tab.="<td align=right ".$c.">".@nantozero($persensdbi,2)."</td>";
					$tab.="<td align=right ".$d.">".@nantozero($persensdbipp,2)."</td>";
					#===== end sdbi =====
					
					#====== thn ======
					
					@$rpbgtthnsat=$bgtthn[$akun]/$prdbgtthn;
					@$rpbgtthnsatpp=$bgtthn[$akun]/$ppbgtthn;
					
					#budget
					if($divisi!=''){$korg=$divisi; $flag=0;}elseif($kdorg!=''){$korg=$kdorg; $flag=0;}else{$korg=$pt; $flag=2;}
					$click=$adacomment="";
					$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
					if(!empty($showcomment[$korg][$akun]['thn']['bgt'])){
						$adacomment="class=has_sign"; if($flag!=2){$flag='1';}
						$title=" title='".getKary($showcomment[$korg][$akun]['thn']['bgt'][0]['user'])."\n".$showcomment[$korg][$akun]['thn']['bgt'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
					}
					$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$korg."','".$prd."','thn','bgt')\"";
							
					$detbgtthn="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','thn','budget')";
					
					$tab.="<td align=right ".$detbgtthn.">".@nantozero($bgtthn[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtthnsat,2)."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtthnsatpp,2)."</td>";
					#===== end thn =====
					$tab.="</tr>";
					
					@$ttlrealbi[$kode]+=$realbi[$akun];
					@$ttlbgtbi[$kode]+=$bgtbi[$akun];
					@$ttlrealsdbi[$kode]+=$realsdbi[$akun];
					@$ttlbgtsdbi[$kode]+=$bgtsdbi[$akun];
					@$ttlbgtthn[$kode]+=$bgtthn[$akun];
					
					@$trealbi+=$realbi[$akun];
					@$tbgtbi+=$bgtbi[$akun];
					@$trealsdbi+=$realsdbi[$akun];
					@$tbgtsdbi+=$bgtsdbi[$akun];
					@$tbgtthn+=$bgtthn[$akun];
				}
			}
			
			# sub total
			if($kode=='umm'){
				$detrealbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','real')";
				$detbgtbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','budget')";
				
				$detrealsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','real')";
				$detbgtsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','budget')";
						
				$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','thn','budget')";
			}else{
				$detrealbi=$detbgtbi=$detrealsdbi=$detbgtsdbi=$detbgtthn="";
			}
			$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
			$tab.="<td align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			#===== bi =====
			$tab.="<td align=right ".$detrealbi.">".@nantozero($ttlrealbi[$kode])."</td>";
			
			@$ttlrprealbisat=$ttlrealbi[$kode]/$prdrealbi;
			@$ttlrprealbisatpp=$ttlrealbi[$kode]/$pprealbi;
			@$ttlrpbgtbisat=$ttlbgtbi[$kode]/$prdbgtbi;
			@$ttlrpbgtbisatpp=$ttlbgtbi[$kode]/$ppbgtbi;
			$tab.="<td align=right>".@nantozero($ttlrprealbisat,2)."</td>";
			$tab.="<td align=right>".@nantozero($ttlrprealbisatpp,2)."</td>";
			$tab.="<td align=right ".$detbgtbi.">".@nantozero($ttlbgtbi[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtbisat,2)."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtbisatpp,2)."</td>";
			
			@$ttlpersenbi=(($ttlrpbgtbisat-$ttlrprealbisat)/$ttlrpbgtbisat)*100;
			@$ttlpersenbipp=(($ttlrpbgtbisatpp-$ttlrprealbisatpp)/$ttlrpbgtbisatpp)*100;
			$c=$d="";
			if($ttlpersenbi<0){$c=" style=color:red;";}
			if($ttlpersenbipp<0){$d=" style=color:red;";}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersenbi,2)."</td>";
			$tab.="<td align=right ".$d.">".@nantozero($ttlpersenbipp,2)."</td>";
			#=== end bi ===
			#====== sdbi ======
			$tab.="<td align=right ".$detrealsdbi.">".@nantozero($ttlrealsdbi[$kode])."</td>";
			
			@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$prdrealsdbi;
			@$ttlrprealsdbisatpp=$ttlrealsdbi[$kode]/$pprealsdbi;
			@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$prdbgtsdbi;
			@$ttlrpbgtsdbisatpp=$ttlbgtsdbi[$kode]/$ppbgtsdbi;
			$tab.="<td align=right>".@nantozero($ttlrprealsdbisat,2)."</td>";
			$tab.="<td align=right>".@nantozero($ttlrprealsdbisatpp,2)."</td>";
			$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($ttlbgtsdbi[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtsdbisat,2)."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtsdbisatpp,2)."</td>";
			
			@$ttlpersensdbi=(($ttlrpbgtsdbisat-$ttlrprealsdbisat)/$ttlrpbgtsdbisat)*100;
			@$ttlpersensdbipp=(($ttlrpbgtsdbisatpp-$ttlrprealsdbisatpp)/$ttlrpbgtsdbisatpp)*100;
			$c=$d="";
			if($ttlpersensdbi<0){$c=" style=color:red;";}
			if($ttlpersensdbipp<0){$d=" style=color:red;";}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersensdbi,2)."</td>";
			$tab.="<td align=right ".$d.">".@nantozero($ttlpersensdbipp,2)."</td>";
			#===== end sdbi =====
			#====== thn ======
			
			@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$prdbgtthn;
			@$ttlrpbgtthnsatpp=$ttlbgtthn[$kode]/$ppbgtthn;
			$tab.="<td align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtthnsat,2)."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtthnsatpp,2)."</td>";
			#===== end thn =====
			$tab.="</tr>";
		}
	#grand total
	$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
	$tab.="<td align=center colspan=3>GRAND TOTAL</td>";
	
	#===== bi =====
	$tab.="<td align=right>".@nantozero($trealbi)."</td>";
	@$rpperprdrealbi=$trealbi/@$prdrealbi;
	@$rpperprdrealbipp=$trealbi/@$pprealbi;
	$tab.="<td align=right>".@nantozero($rpperprdrealbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($rpperprdrealbipp,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtbi)."</td>";
	@$rpperprdbgtbi=$tbgtbi/$prdbgtbi;
	@$rpperprdbgtbipp=$tbgtbi/$ppbgtbi;
	$tab.="<td align=right>".@nantozero($rpperprdbgtbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($rpperprdbgtbipp,2)."</td>";
	@$gtpersenbi=(($rpperprdbgtbi-$rpperprdrealbi)/$rpperprdbgtbi)*100;
	@$gtpersenbipp=(($rpperprdbgtbipp-$rpperprdrealbipp)/$rpperprdbgtbipp)*100;
	$tab.="<td align=right>".@nantozero($gtpersenbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($gtpersenbipp,2)."</td>";
	#=== end bi ===
	#====== sdbi ======
	$tab.="<td align=right>".@nantozero($trealsdbi)."</td>";
	@$rpperprdrealsdbi=$trealsdbi/@$prdrealsdbi;
	@$rpperprdrealsdbipp=$trealsdbi/@$pprealsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdrealsdbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($rpperprdrealsdbipp,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtsdbi)."</td>";
	@$rpperprdbgtsdbi=$tbgtsdbi/@$prdbgtsdbi;
	@$rpperprdbgtsdbipp=$tbgtsdbi/@$ppbgtsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdbgtsdbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($rpperprdbgtsdbipp,2)."</td>";
	@$gtpersensdbi=(($rpperprdbgtsdbi-$rpperprdrealsdbi)/$rpperprdbgtsdbi)*100;
	@$gtpersensdbipp=(($rpperprdbgtsdbipp-$rpperprdrealsdbipp)/$rpperprdbgtsdbipp)*100;
	$tab.="<td align=right>".@nantozero($gtpersensdbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($gtpersensdbipp,2)."</td>";
	#===== end sdbi =====
	#====== thn ======
	$tab.="<td align=right>".@nantozero($tbgtthn)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtthn/@$prdbgtthn,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtthn/@$ppbgtthn,2)."</td>";
	#===== end thn =====
	
	break;
	
	case'code':
		$tab.="<tr class=rowcontent style=background-color:#E8DAEF>
            <td></td>  
            <td align=center><i>Code</i></td>  
            <td align=center><i>Activity Group</i></td>  
            <td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Kg</i></td>
            <td align=center><i>Rp/PP</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Kg</i></td>
            <td align=center><i>Rp/PP</i></td>
            <td align=center><i>Kg</i></td>
            <td align=center><i>PP</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Kg</i></td>
            <td align=center><i>Rp/PP</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Kg</i></td>
            <td align=center><i>Rp/PP</i></td>
            <td align=center><i>Kg</i></td>
            <td align=center><i>PP</i></td>
			<td align=center><i>Total</i></td>  
            <td align=center><i>Rp/Kg</i></td>
            <td align=center><i>Rp/PP</i></td>
        </tr>";
		ksort($arrakun7);
		ksort($cdarrakun);
		
		foreach($arrkode as $kode){
			foreach($arrakun7 as $akun){
				if(@$cdarrakun[$kode][$akun]!=''){
					$d=substr($akun,0,5);
					@$subttlrealbi[$d]+=$cdrealbi[$akun];
					@$subttlbgtbi[$d]+=$cdbgtbi[$akun];
					@$subttlrealsdbi[$d]+=$cdrealsdbi[$akun];
					@$subttlbgtsdbi[$d]+=$cdbgtsdbi[$akun];
					@$subttlbgtthn[$d]+=$cdbgtthn[$akun];
				}
			}
		}			
		
		
		foreach($arrkode as $kode){
			$no=0;
			foreach($arrakun7 as $akun){
				if(@$cdarrakun[$kode][$akun]!=''){					
					$no++;
					$show="";
					if(substr($akun,0,1)=='7'){
						// $show=" style=display:none";
					}
					
					
					$d=substr($akun,0,5);
					if($d!=$n){
						$tab.="<tr class=rowcontent ".$show." style=background-color:#ccfffd;font-style:italic;>";
						$tab.="<td></td>";
						$tab.="<td><i>".$d."</i></td>";
						$tab.="<td><i>".ucwords(strtolower(getNamaAkun($d)))."</i></td>";
						@$subrprealbisat=$subttlrealbi[$d]/$prdrealbi;
						@$subrprealbisatpp=$subttlrealbi[$d]/$pprealbi;
						@$subrpbgtbisat=$subttlbgtbi[$d]/$prdbgtbi;
						@$subrpbgtbisatpp=$subttlbgtbi[$d]/$ppbgtbi;
						
						$tab.="<td align=right>".@nantozero($subttlrealbi[$d])."</td>";
						$tab.="<td align=right>".@nantozero($subrprealbisat,2)."</td>";
						$tab.="<td align=right>".@nantozero($subrprealbisatpp,2)."</td>";
						$tab.="<td align=right>".@nantozero($subttlbgtbi[$d])."</td>";
						$tab.="<td align=right>".@nantozero($subrpbgtbisat,2)."</td>";
						$tab.="<td align=right>".@nantozero($subrpbgtbisatpp,2)."</td>";
						
						$subpersenbi=0;
						if($subrprealbisat>0){						
							@$subpersenbi=(($subrpbgtbisat-$subrprealbisat)/$subrpbgtbisat)*100;
						}
						$c="";
						if($subpersenbi<0){$c=" style=color:red;";}
						$tab.="<td align=right ".$c.">".@nantozero($subpersenbi,2)."</td>";
						
						$subpersenbipp=0;
						if($subrprealbisatpp>0){						
							@$subpersenbipp=(($subrpbgtbisatpp-$subrprealbisatpp)/$subrpbgtbisatpp)*100;
						}
						$c="";
						if($subpersenbipp<0){$c=" style=color:red;";}
						$tab.="<td align=right ".$c.">".@nantozero($subpersenbipp,2)."</td>";
						
						
						
						@$subrprealsdbisat=$subttlrealsdbi[$d]/$prdrealsdbi;
						@$subrprealsdbisatpp=$subttlrealsdbi[$d]/$pprealsdbi;
						@$subrpbgtsdbisat=$subttlbgtsdbi[$d]/$prdbgtsdbi;
						@$subrpbgtsdbisatpp=$subttlbgtsdbi[$d]/$ppbgtsdbi;
						
						$tab.="<td align=right>".@nantozero($subttlrealsdbi[$d])."</td>";
						$tab.="<td align=right>".@nantozero($subrprealsdbisat,2)."</td>";
						$tab.="<td align=right>".@nantozero($subrprealsdbisatpp,2)."</td>";
						$tab.="<td align=right>".@nantozero($subttlbgtsdbi[$d])."</td>";
						$tab.="<td align=right>".@nantozero($subrpbgtsdbisat,2)."</td>";
						$tab.="<td align=right>".@nantozero($subrpbgtsdbisatpp,2)."</td>";
						$subpersensdbi=0;
						if($subrprealsdbisat>0){						
							@$subpersensdbi=(($subrpbgtsdbisat-$subrprealsdbisat)/$subrpbgtsdbisat)*100;
						}
						$c="";
						if($subpersensdbi<0){$c=" style=color:red;";}
						$tab.="<td align=right ".$c.">".@nantozero($subpersensdbi,2)."</td>";
						
						$subpersensdbipp=0;
						if($subrprealsdbisatpp>0){						
							@$subpersensdbipp=(($subrpbgtsdbisatpp-$subrprealsdbisatpp)/$subrpbgtsdbisatpp)*100;
						}
						$c="";
						if($subpersensdbipp<0){$c=" style=color:red;";}
						$tab.="<td align=right ".$c.">".@nantozero($subpersensdbipp,2)."</td>";
						
						@$subrpbgtthnsat=$subttlbgtthn[$d]/$prdbgtthn;
						@$subrpbgtthnsatpp=$subttlbgtthn[$d]/$ppbgtthn;
						$tab.="<td align=right>".@nantozero($subttlbgtthn[$d])."</td>";
						$tab.="<td align=right>".@nantozero($subrpbgtthnsat,2)."</td>";
						$tab.="<td align=right>".@nantozero($subrpbgtthnsatpp,2)."</td>";
						$tab.="</tr>";
					}
					
					
					$tab.="<tr class=rowcontent ".$show.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$akun."</td>";
					$tab.="<td>".$nmakun[$akun]."</td>";
					#===== bi =====
					#actual
					if($divisi!=''){$korg=$divisi; $flag=0;}elseif($kdorg!=''){$korg=$kdorg; $flag=0;}else{$korg=$pt; $flag=2;}
					$click=$adacomment="";
					$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
					if(!empty($showcomment[$korg][$akun]['bi']['real'])){
						$adacomment="class=has_sign";  if($flag!=2){$flag='1';}
						$title=" title='".getKary($showcomment[$korg][$akun]['bi']['real'][0]['user'])."\n".$showcomment[$korg][$akun]['bi']['real'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
					}
					$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$korg."','".$prd."','bi','real')\"";
					
					$detrealbi="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','bi','real')";
					#budget
					if($divisi!=''){$korg=$divisi; $flag=0;}elseif($kdorg!=''){$korg=$kdorg; $flag=0;}else{$korg=$pt; $flag=2;}
					$click=$adacomment="";
					$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
					if(!empty($showcomment[$korg][$akun]['bi']['bgt'])){
						$adacomment="class=has_sign"; if($flag!=2){$flag='1';}
						$title=" title='".getKary($showcomment[$korg][$akun]['bi']['bgt'][0]['user'])."\n".$showcomment[$korg][$akun]['bi']['bgt'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
					}
					$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$korg."','".$prd."','bi','bgt')\"";					
					
					$detbgtbi="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','bi','budget')";
					
					$tab.="<td align=right ".$detrealbi.">".@nantozero($cdrealbi[$akun])."</td>";
					
					@$rprealbisat=$cdrealbi[$akun]/$prdrealbi;
					@$rprealbisatpp=$cdrealbi[$akun]/$pprealbi;
					@$rpbgtbisat=$cdbgtbi[$akun]/$prdbgtbi;
					@$rpbgtbisatpp=$cdbgtbi[$akun]/$ppbgtbi;
					$tab.="<td align=right>".@nantozero($rprealbisat,2)."</td>";
					$tab.="<td align=right>".@nantozero($rprealbisatpp,2)."</td>";
					$tab.="<td align=right ".$detbgtbi.">".@nantozero($cdbgtbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtbisat,2)."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtbisatpp,2)."</td>";
					
					$persenbi=0;
					if($rprealbisat>0){						
						@$persenbi=(($rpbgtbisat-$rprealbisat)/$rpbgtbisat)*100;
					}
					$c="";
					if($persenbi<0){$c=" style=color:red;";}
					$tab.="<td align=right ".$c.">".@nantozero($persenbi,2)."</td>";
					
					$persenbipp=0;
					if($rprealbisatpp>0){						
						@$persenbipp=(($rpbgtbisatpp-$rprealbisatpp)/$rpbgtbisatpp)*100;
					}
					$c="";
					if($persenbipp<0){$c=" style=color:red;";}
					$tab.="<td align=right ".$c.">".@nantozero($persenbipp,2)."</td>";
					#===== end bi =====
					#====== sdbi ======
					
					#actual
					if($divisi!=''){$korg=$divisi; $flag=0;}elseif($kdorg!=''){$korg=$kdorg; $flag=0;}else{$korg=$pt; $flag=2;}
					$click=$adacomment="";
					$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
					if(!empty($showcomment[$korg][$akun]['sdbi']['real'])){
						$adacomment="class=has_sign"; if($flag!=2){$flag='1';}
						$title=" title='".getKary($showcomment[$korg][$akun]['sdbi']['real'][0]['user'])."\n".$showcomment[$korg][$akun]['sdbi']['real'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
					}
					$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$korg."','".$prd."','sdbi','real')\"";
					
					$detrealsdbi="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','sdbi','real')";
					#budget
					if($divisi!=''){$korg=$divisi; $flag=0;}elseif($kdorg!=''){$korg=$kdorg; $flag=0;}else{$korg=$pt; $flag=2;}
					$click=$adacomment="";
					$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
					if(!empty($showcomment[$korg][$akun]['sdbi']['bgt'])){
						$adacomment="class=has_sign"; if($flag!=2){$flag='1';}
						$title=" title='".getKary($showcomment[$korg][$akun]['sdbi']['bgt'][0]['user'])."\n".$showcomment[$korg][$akun]['sdbi']['bgt'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
					}
					$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$korg."','".$prd."','sdbi','bgt')\"";
					
					$detbgtsdbi="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','sdbi','budget')";
					
					$tab.="<td align=right ".$detrealsdbi.">".@nantozero($cdrealsdbi[$akun])."</td>";
					
					@$rprealsdbisat=$cdrealsdbi[$akun]/$prdrealsdbi;
					@$rprealsdbisatpp=$cdrealsdbi[$akun]/$pprealsdbi;
					@$rpbgtsdbisat=$cdbgtsdbi[$akun]/$prdbgtsdbi;
					@$rpbgtsdbisatpp=$cdbgtsdbi[$akun]/$ppbgtsdbi;
					$tab.="<td align=right>".@nantozero($rprealsdbisat,2)."</td>";
					$tab.="<td align=right>".@nantozero($rprealsdbisatpp,2)."</td>";
					$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($cdbgtsdbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtsdbisat,2)."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtsdbisatpp,2)."</td>";
					
					$persensdbi=0;
					if($rprealsdbisat>0){						
						@$persensdbi=(($rpbgtsdbisat-$rprealsdbisat)/$rpbgtsdbisat)*100;
					}
					$c="";
					if($persensdbi<0){$c=" style=color:red;";}
					$tab.="<td align=right ".$c.">".@nantozero($persensdbi,2)."</td>";
					
					$persensdbipp=0;
					if($rprealsdbisatpp>0){						
						@$persensdbipp=(($rpbgtsdbisatpp-$rprealsdbisatpp)/$rpbgtsdbisatpp)*100;
					}
					$c="";
					if($persensdbipp<0){$c=" style=color:red;";}
					$tab.="<td align=right ".$c.">".@nantozero($persensdbipp,2)."</td>";
					#===== end sdbi =====
					
					#====== thn ======
					
					@$rpbgtthnsat=$cdbgtthn[$akun]/$prdbgtthn;
					@$rpbgtthnsatpp=$cdbgtthn[$akun]/$ppbgtthn;
					
					#budget
					if($divisi!=''){$korg=$divisi; $flag=0;}elseif($kdorg!=''){$korg=$kdorg; $flag=0;}else{$korg=$pt; $flag=2;}
					$click=$adacomment="";
					$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
					if(!empty($showcomment[$korg][$akun]['thn']['bgt'])){
						$adacomment="class=has_sign"; if($flag!=2){$flag='1';}
						$title=" title='".getKary($showcomment[$korg][$akun]['thn']['bgt'][0]['user'])."\n".$showcomment[$korg][$akun]['thn']['bgt'][0]['comment']."\n\nClick Kanan -> Show Comment.'";
					}
					$click=$title." ".$adacomment." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$korg."','".$prd."','thn','bgt')\"";
							
					$detbgtthn="style=cursor:pointer;color:blue; ".$click." onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','thn','budget')";
					
					$tab.="<td align=right ".$detbgtthn.">".@nantozero($cdbgtthn[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtthnsat,2)."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtthnsatpp,2)."</td>";
					#===== end thn =====
					$tab.="</tr>";
					
					@$ttlrealbi[$kode]+=$cdrealbi[$akun];
					@$ttlbgtbi[$kode]+=$cdbgtbi[$akun];
					@$ttlrealsdbi[$kode]+=$cdrealsdbi[$akun];
					@$ttlbgtsdbi[$kode]+=$cdbgtsdbi[$akun];
					@$ttlbgtthn[$kode]+=$cdbgtthn[$akun];
					
					@$trealbi+=$cdrealbi[$akun];
					@$tbgtbi+=$cdbgtbi[$akun];
					@$trealsdbi+=$cdrealsdbi[$akun];
					@$tbgtsdbi+=$cdbgtsdbi[$akun];
					@$tbgtthn+=$cdbgtthn[$akun];
					$n=$d;
				}
			}
			
			# sub total
			if($kode=='umm'){
				$detrealbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','real')";
				$detbgtbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','budget')";
				
				$detrealsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','real')";
				$detbgtsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','budget')";
						
				$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','thn','budget')";
			}else{
				$detrealbi=$detbgtbi=$detrealsdbi=$detbgtsdbi=$detbgtthn="";
			}
			$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
			$tab.="<td align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			#===== bi =====
			$tab.="<td align=right ".$detrealbi.">".@nantozero($ttlrealbi[$kode])."</td>";
			
			@$ttlrprealbisat=$ttlrealbi[$kode]/$prdrealbi;
			@$ttlrprealbisatpp=$ttlrealbi[$kode]/$pprealbi;
			@$ttlrpbgtbisat=$ttlbgtbi[$kode]/$prdbgtbi;
			@$ttlrpbgtbisatpp=$ttlbgtbi[$kode]/$ppbgtbi;
			$tab.="<td align=right>".@nantozero($ttlrprealbisat,2)."</td>";
			$tab.="<td align=right>".@nantozero($ttlrprealbisatpp,2)."</td>";
			$tab.="<td align=right ".$detbgtbi.">".@nantozero($ttlbgtbi[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtbisat,2)."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtbisatpp,2)."</td>";
			
			@$ttlpersenbi=(($ttlrpbgtbisat-$ttlrprealbisat)/$ttlrpbgtbisat)*100;
			$c="";
			if($ttlpersenbi<0){$c=" style=color:red;";}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersenbi,2)."</td>";
			
			@$ttlpersenbipp=(($ttlrpbgtbisatpp-$ttlrprealbisatpp)/$ttlrpbgtbisatpp)*100;
			$c="";
			if($ttlpersenbipp<0){$c=" style=color:red;";}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersenbipp,2)."</td>";
			#=== end bi ===
			#====== sdbi ======
			$tab.="<td align=right ".$detrealsdbi.">".@nantozero($ttlrealsdbi[$kode])."</td>";
			
			@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$prdrealsdbi;
			@$ttlrprealsdbisatpp=$ttlrealsdbi[$kode]/$pprealsdbi;
			@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$prdbgtsdbi;
			@$ttlrpbgtsdbisatpp=$ttlbgtsdbi[$kode]/$ppbgtsdbi;
			$tab.="<td align=right>".@nantozero($ttlrprealsdbisat,2)."</td>";
			$tab.="<td align=right>".@nantozero($ttlrprealsdbisatpp,2)."</td>";
			$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($ttlbgtsdbi[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtsdbisat,2)."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtsdbisatpp,2)."</td>";
			
			@$ttlpersensdbi=(($ttlrpbgtsdbisat-$ttlrprealsdbisat)/$ttlrpbgtsdbisat)*100;
			$c="";
			if($ttlpersensdbi<0){$c=" style=color:red;";}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersensdbi,2)."</td>";
			
			@$ttlpersensdbipp=(($ttlrpbgtsdbisatpp-$ttlrprealsdbisatpp)/$ttlrpbgtsdbisatpp)*100;
			$c="";
			if($ttlpersensdbipp<0){$c=" style=color:red;";}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersensdbipp,2)."</td>";
			#===== end sdbi =====
			#====== thn ======
			
			@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$prdbgtthn;
			@$ttlrpbgtthnsatpp=$ttlbgtthn[$kode]/$ppbgtthn;
			$tab.="<td align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtthnsat,2)."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtthnsatpp,2)."</td>";
			#===== end thn =====
			$tab.="</tr>";
		}
		
	
	#grand total
	$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
	$tab.="<td align=center colspan=3>GRAND TOTAL</td>";
	#===== bi =====
	$tab.="<td align=right>".@nantozero($trealbi)."</td>";
	@$rpperprdrealbi=$trealbi/@$prdrealbi;
	@$rpperprdrealbipp=$trealbi/@$pprealbi;
	$tab.="<td align=right>".@nantozero($rpperprdrealbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($rpperprdrealbipp,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtbi)."</td>";
	
	@$rpperprdbgtbi=$tbgtbi/$prdbgtbi;
	@$rpperprdbgtbipp=$tbgtbi/$ppbgtbi;
	$tab.="<td align=right>".@nantozero($rpperprdbgtbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($rpperprdbgtbipp,2)."</td>";
	
	@$gtpersenbi=(($rpperprdbgtbi-$rpperprdrealbi)/$rpperprdbgtbi)*100;
	$tab.="<td align=right>".@nantozero($gtpersenbi,2)."</td>";
	
	@$gtpersenbipp=(($rpperprdbgtbipp-$rpperprdrealbipp)/$rpperprdbgtbipp)*100;
	$tab.="<td align=right>".@nantozero($gtpersenbipp,2)."</td>";
	#=== end bi ===
	#====== sdbi ======
	$tab.="<td align=right>".@nantozero($trealsdbi)."</td>";
	@$rpperprdrealsdbi=$trealsdbi/@$prdrealsdbi;
	@$rpperprdrealsdbipp=$trealsdbi/@$pprealsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdrealsdbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($rpperprdrealsdbipp,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtsdbi)."</td>";
	@$rpperprdbgtsdbi=$tbgtsdbi/@$prdbgtsdbi;
	@$rpperprdbgtsdbipp=$tbgtsdbi/@$ppbgtsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdbgtsdbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($rpperprdbgtsdbipp,2)."</td>";
	@$gtpersensdbi=(($rpperprdbgtsdbi-$rpperprdrealsdbi)/$rpperprdbgtsdbi)*100;
	$tab.="<td align=right>".@nantozero($gtpersensdbi,2)."</td>";
	
	@$gtpersensdbipp=(($rpperprdbgtsdbipp-$rpperprdrealsdbipp)/$rpperprdbgtsdbipp)*100;
	$tab.="<td align=right>".@nantozero($gtpersensdbipp,2)."</td>";
	#===== end sdbi =====
	#====== thn ======
	$tab.="<td align=right>".@nantozero($tbgtthn)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtthn/@$prdbgtthn,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtthn/@$ppbgtthn,2)."</td>";
	#===== end thn =====
	
	break;
}
 
$tab.="</tbody></table>";

switch ($proses) {
######PREVIEW
    case 'preview':
    	if ($tipe == 'summary' || $tipe == 'detail') {
    		echo $html;
    	} else {
    		echo $tab;
    	}
    break;

######EXCEL	
    case 'excel':
    	if ($tipe == 'summary' || $tipe == 'detail') {
    		$print = $html;
    	} else {
    		$print = $tab;
    	}
        $nop_ = $tipe;
        if (strlen($print) > 0) {
			$print.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $print)) {
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
		$e="";
	}else if(is_infinite($e)){
		$e="";
	}else{
		$e=$e;
	}
	$n = hidezerodecimal($e,$i);
	if($n==0 or $n==''){
		$n='';
	}
	
	return $n;
}



?>