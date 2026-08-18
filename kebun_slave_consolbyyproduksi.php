<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses  = checkPostGet('proses', '');
$kdorg   = checkPostGet('kdorg', '');
$regional= checkPostGet('regional', '');
$prd     = checkPostGet('prd', '');
$tipe    = checkPostGet('tipe', '');
$tipex    = checkPostGet('tipe', '');
$depre    = checkPostGet('depre', '');

$arrbi   = explode('-',$prd); 
$tahun   = $arrbi[0];
$tahundpn= $tahun+1;
$bulan   = '12';
$periode1= $tahun."-01";
$periode2= $prd;
$prddpn  = $tahundpn."-".$bulan;

$where='';
$where2='';
if($regional!=''){
	$whreg="and subregional='".$regional."'";
}

$listkodeorg = [];
$datakodeorg = [];
$datakodeorg['GRANDTOTAL']['HHO']='HIPBONE INDONESIA AGRO';
// $datakodeorg['GRANDTOTAL']['KSPAGRO']='KSP AGRO';
$str="select * from ".$dbname.".bgt_regional_assignment where 1=1 ".$whreg."";
$res = fetchdata($str);
foreach($res as $bar){
	if(getNamaOrg($bar['kodeunit'],'tipe')=='KEBUN' and getNamaOrg($bar['kodeunit'],'inti')=='1'){		
		$datakodeorg[$bar['subregional']][$bar['kodeunit']]=$bar['kodeunit'];
		$colspan[$bar['subregional']]++;
		$listkodeorg[$bar['kodeunit']]=$bar['kodeunit'];
		$getregion[$bar['kodeunit']]=$bar['subregional'];
		$listreg[$bar['subregional']]=$bar['subregional'];		
	}
}

foreach($listreg as $reg){
	$datakodeorg[$reg]['TOTAL'.$reg]='TOTAL';
}

$where=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
$whunit=" and substr(a.unit,1,4) in ('".implode("','",$listkodeorg)."')";
$where2=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";


if($kdorg!=''){
	$where.=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$whunit.=" and substr(a.unit,1,4) ='".$kdorg."'";
	$where2.=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
}

$whereakun = " and (substr(noakun,1,3) in ('611','621') or noakun like '7%')";
$amortisasi = '715';


$str = "select * from " . $dbname . ".keu_5akun"; 
$req = fetchdata($str);
foreach($req as $val){					
	$nmakun[$val['noakun']]=$val['namaakun'];
}

$str = "select * from " . $dbname . ".vhc_kegiatan"; 
$req = fetchdata($str);
foreach($req as $val){					
	$nmakun[$val['kodekegiatan']]=strtoupper($val['namakegiatan']);
}

$str = "select * from " . $dbname . ".setup_kegiatan"; 
$req = fetchdata($str);
foreach($req as $val){					
	$nmakun[$val['kodekegiatan']]=$val['namakegiatan'];
}




$arrakun=array();
if($depre=='1'){
	$arrkode=array('pml'=>'pml','pnn'=>'pnn','umm'=>'umm','amor'=>'amor');
}else{	
	$arrkode=array('pml'=>'pml','pnn'=>'pnn','umm'=>'umm');
}
$str = "select * from " . $dbname . ".keu_5akun  where 1=1 ".$whereakun.""; 
$res = fetchdata($str);
foreach($res as $bar){
	if(substr($bar['noakun'],0,3)=='621'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['pml'][$bar['noakun']] = $bar['noakun'];
		}
		if($tipe=='kegiatan'){
			if(strlen($bar['noakun'])=='7'){
				$sql = "select * from " . $dbname . ".setup_kegiatan  where 1=1 and noakun='".$bar['noakun']."'";
				$req = fetchdata($sql);
				foreach($req as $val){					
					$arrakun7[$val['kodekegiatan']] = $val['kodekegiatan'];
					$cdarrakun['pml'][$val['kodekegiatan']] = $val['kodekegiatan'];
				}
			}
		}else{			
			if(strlen($bar['noakun'])=='7'){
				$arrakun7[$bar['noakun']] = $bar['noakun'];
				$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
			}
		}
	}
	
	if(substr($bar['noakun'],0,3)=='611'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['pnn'][$bar['noakun']] = $bar['noakun'];
		}
		if($tipe=='kegiatan'){
			if(strlen($bar['noakun'])=='7'){
				$sql = "select * from " . $dbname . ".setup_kegiatan  where 1=1 and noakun='".$bar['noakun']."'";
				$req = fetchdata($sql);
				foreach($req as $val){					
					$arrakun7[$val['kodekegiatan']] = $val['kodekegiatan'];
					$cdarrakun['pnn'][$val['kodekegiatan']] = $val['kodekegiatan'];
				}
			}
		}else{			
			if(strlen($bar['noakun'])=='7'){
				$arrakun7[$bar['noakun']] = $bar['noakun'];
				$cdarrakun['pnn'][$bar['noakun']] = $bar['noakun'];
			}
		}
	}
	if(substr($bar['noakun'],0,3)==$amortisasi){
		if(substr($bar['noakun'],0,1)=='7'){
			if(strlen($bar['noakun'])=='3'){
				$arrakun[$bar['noakun']] = $bar['noakun'];
				$listakun['amor'][$bar['noakun']] = $bar['noakun'];			
			}
			if(strlen($bar['noakun'])=='7'){
				$arrakun7[$bar['noakun']] = $bar['noakun'];
				$cdarrakun['amor'][$bar['noakun']] = $bar['noakun'];
			}
		}
	}else{		
		if(substr($bar['noakun'],0,1)=='7'){
			if(strlen($bar['noakun'])=='3'){
				$arrakun[$bar['noakun']] = $bar['noakun'];
				$listakun['umm'][$bar['noakun']] = $bar['noakun'];
			}
			if(strlen($bar['noakun'])=='7'){
				$arrakun7[$bar['noakun']] = $bar['noakun'];
				$cdarrakun['umm'][$bar['noakun']] = $bar['noakun'];
			}
		}
	}
}

$luasreal=$pkkreal=[];
#ambil luas realisasi
$str = "select substr(a.kodeorg,1,4) as kodeorg, statusblok, sum(luasareaproduktif) as luasareaproduktif, sum(jumlahpokok) as jumlahpokok from " . $dbname . ".setup_blok_tahunan a  where 1=1 ".$where." and tahun='".$tahun.$bulan."' group by statusblok,substr(a.kodeorg,1,4)"; 
if(count(fetchdata($str))==0){
	$str = "select substr(a.kodeorg,1,4) as kodeorg, statusblok, sum(luasareaproduktif) as luasareaproduktif, sum(jumlahpokok) as jumlahpokok from " . $dbname . ".setup_blok a  where 1=1 ".$where." group by statusblok,substr(a.kodeorg,1,4)"; 
}
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['statusblok']=='TM'){
		$luasreal[$getregion[$bar['kodeorg']]][$bar['kodeorg']]+=$bar['luasareaproduktif'];
		$pkkreal[$getregion[$bar['kodeorg']]][$bar['kodeorg']]+=$bar['jumlahpokok'];
		
		$luasreal[$getregion[$bar['kodeorg']]]['TOTAL'.$getregion[$bar['kodeorg']]]+=$bar['luasareaproduktif'];
		$pkkreal[$getregion[$bar['kodeorg']]]['TOTAL'.$getregion[$bar['kodeorg']]]+=$bar['jumlahpokok'];
		
		$luasreal['GRANDTOTAL']['HHO']+=$bar['luasareaproduktif'];
		$pkkreal['GRANDTOTAL']['HHO']+=$bar['jumlahpokok'];
	}
}

/* 
#ambil luas bgt
$str = "select substr(a.kodeblok,1,4) as kodeorg,sum(hathnini) as hathnini, statusblok, sum(pokokthnini) as pokokthnini from " . $dbname . ".bgt_blok a  where 1=1 ".$whB." ".$where2." and tahunbudget='".$tahun."' group by statusblok, substr(a.kodeblok,1,4)"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['statusblok']=='TM'){		
		$luasbudget[$bar['kodeorg']]+=$bar['hathnini'];
		$pkkbudget[$bar['kodeorg']]+=$bar['pokokthnini'];
		
		$luasbudget['TOTAL']+=$bar['hathnini'];
		$pkkbudget['TOTAL']+=$bar['pokokthnini'];
	}
	if($bar['statusblok']=='BBT'){		
		$luasbudgetbbt[$bar['kodeorg']]+=$bar['hathnini'];
		$pkkbudgetbbt[$bar['kodeorg']]+=$bar['pokokthnini'];
		
		$luasbudgetbbt['TOTAL']+=$bar['hathnini'];
		$pkkbudgetbbt['TOTAL']+=$bar['pokokthnini'];
	}
	if($bar['statusblok']=='TBM' or $bar['statusblok']=='TB'){		
		$luasbudgettbm[$bar['kodeorg']]+=$bar['hathnini'];
		$pkkbudgettbm[$bar['kodeorg']]+=$bar['pokokthnini'];
		
		$luasbudgettbm['TOTAL']+=$bar['hathnini'];
		$pkkbudgettbm['TOTAL']+=$bar['pokokthnini'];
	}
}

#ambil prd bgt
$e="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="kg".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";

$str = " select kodeunit,divisi,thntnm,kodeblok,".$e." as sdbi,kg".$bulan." as bi,kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." and tahunbudget = '".$tahun."'";
$res = fetchdata($str);
foreach($res as $bar){
	@$prdbgtbi[$bar['kodeunit']] += $bar['bi'];
	@$prdbgtsdbi[$bar['kodeunit']] += $bar['sdbi'];
	@$prdbgtthn[$bar['kodeunit']] += $bar['kgsetahun'];
	
	@$prdbgtbi['TOTAL'] += $bar['bi'];
	@$prdbgtsdbi['TOTAL'] += $bar['sdbi'];
	@$prdbgtthn['TOTAL'] += $bar['kgsetahun'];
}




$e="("; $s="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
}
$e.=")"; $s.=")";
$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";

#ini khusus budget kebun
$str = " select substr(kodeorg,1,4) as kebun, substr(noakun,1,5) as jobgroup, tipebudget, kodebudget, noakun, kegiatan, sum(".$e.") as sdbi,sum(rp".$bulan.") as bi,sum(rupiah) as rupiah, tahunbudget, kodeorg, sum(".$s.") as sdbivol, sum(fis".$bulan.") as bivol, sum(jumlah) as jumlah, satuanj, sum(volume) as volume, sum(".$t.") as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whereakun." group by substr(noakun,1,5), noakun,kegiatan, substr(kodeorg,1,4)";
$res = fetchdata($str);
foreach($res as $bar){
	@$bgtbi[$bar['jobgroup']][$bar['kebun']] += $bar['bi'];
	@$bgtsdbi[$bar['jobgroup']][$bar['kebun']] += $bar['sdbi'];
	@$bgtthn[$bar['jobgroup']][$bar['kebun']] += $bar['rupiah'];
	
	if($tipe=='kegiatan'){
		$bar['noakun']=$bar['kegiatan'];
	}
	
	@$cdbgtbi[$bar['noakun']][$bar['kebun']] += $bar['bi'];
	@$cdbgtsdbi[$bar['noakun']][$bar['kebun']] += $bar['sdbi'];
	@$cdbgtthn[$bar['noakun']][$bar['kebun']] += $bar['rupiah'];
	
	
	@$bgtbi[$bar['jobgroup']]['TOTAL'] += $bar['bi'];
	@$bgtsdbi[$bar['jobgroup']]['TOTAL'] += $bar['sdbi'];
	@$bgtthn[$bar['jobgroup']]['TOTAL'] += $bar['rupiah'];
	
	@$cdbgtbi[$bar['noakun']]['TOTAL'] += $bar['bi'];
	@$cdbgtsdbi[$bar['noakun']]['TOTAL'] += $bar['sdbi'];
	@$cdbgtthn[$bar['noakun']]['TOTAL'] += $bar['rupiah'];
	
	$listkodeorg[$bar['kebun']]=$bar['kebun'];
}

#ini khusus budget UMUM
$str=" select substr(kodeorg,1,4) as kebun, substr(noakun,1,5) as jobgroup,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." and kodebudget='UMUM' and tahunbudget = '".$tahun."' ".$whereakun."";
$res = fetchdata($str);
foreach($res as $bar){
	@$bgtbi[$bar['jobgroup']][$bar['kebun']] += $bar['bi'];
	@$bgtsdbi[$bar['jobgroup']][$bar['kebun']] += $bar['sdbi'];
	@$bgtthn[$bar['jobgroup']][$bar['kebun']] += $bar['rupiah'];
	
	@$cdbgtbi[$bar['noakun']][$bar['kebun']] += $bar['bi'];
	@$cdbgtsdbi[$bar['noakun']][$bar['kebun']] += $bar['sdbi'];
	@$cdbgtthn[$bar['noakun']][$bar['kebun']] += $bar['rupiah'];
	
	@$bgtbi[$bar['jobgroup']]['TOTAL'] += $bar['bi'];
	@$bgtsdbi[$bar['jobgroup']]['TOTAL'] += $bar['sdbi'];
	@$bgtthn[$bar['jobgroup']]['TOTAL'] += $bar['rupiah'];
	
	@$cdbgtbi[$bar['noakun']]['TOTAL'] += $bar['bi'];
	@$cdbgtsdbi[$bar['noakun']]['TOTAL'] += $bar['sdbi'];
	@$cdbgtthn[$bar['noakun']]['TOTAL'] += $bar['rupiah'];
	
	$listkodeorg[$bar['kebun']]=$bar['kebun'];
} */

$produksi=[];
$str = "select sum(totalkg) as kg, left(tanggal,7) as periode,substr(blok,1,4) as kodeorg from ".$dbname.".kebun_spb_vw a where 1=1 ".$where." and substr(tanggal,1,7) <= '".$prd."' and tanggal like '".$tahun."%' group by substr(blok,1,4)";
$res = fetchdata($str);
foreach($res as $bar){
    $produksi[$getregion[$bar['kodeorg']]][$bar['kodeorg']]+=$bar['kg'];
	$produksi[$getregion[$bar['kodeorg']]]['TOTAL'.$getregion[$bar['kodeorg']]]+=$bar['kg'];
	$produksi['GRANDTOTAL']['HHO']+=$bar['kg'];
}


sort($listkodeorg);
$listkodeorg["TOTAL"]="TOTAL";

if ($proses == 'excel') {
    $tab.="<table class=sortable cellspacing=1 border=1>";
} else {
	
	$tab.="<div class='menu'>
		<div id='btninscmnt' class='menu-item'>Insert Comment</div>
		<div id='btnshowcmn' class='menu-item'>Show Comment</div>
		<div id='btnreloadframe' class='menu-item'>Reload Frame</div>
	</div>";
    $tab.= "<table class=sortable cellpadding=5 cellspacing=1>";
}

// echo"<pre>";
// print_r($datakodeorg);

$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan='2'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['kode']."</th>
            <th align=center rowspan='2'>Activity</th>";
			foreach($datakodeorg as $regional => $val1){				
				$tab.="<th align=center colspan=".(($colspan[$regional]*2)+2).">".$regional."</th>";
				foreach($val1 as $kebun){
					$jlhcol+=2;
				}				
			}
        $tab.="</tr>";
        $tab.="<tr class=rowheader>";
			foreach($datakodeorg as $regional => $val1){				
				foreach($val1 as $kebun => $namakebun){
					$tab.="<th align=center title='".getNamaOrg($namakebun)."' colspan=2>".$namakebun."</th>";
				}				
			}
        $tab.="</tr>";
     $tab.="</thead>
	<tbody>";
	$tab.="<tr class=rowcontent ".$show." style=background-color:#4abdb7;font-weight:bold;>";
	$tab.="<td></td>";
	$tab.="<td></td>";
	$tab.="<td colspan=8><i>AREAL STATEMENT DAN PRODUKSI</i></td>";
	for($i=1;$i<=($jlhcol-7);$i++){
		$tab.="<td></td>";		
	}
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>
		<td style=background-color:#E8DAEF></td>  
		<td style=background-color:#E8DAEF align=center><i></i></td>  
		<td style=background-color:#E8DAEF align=center><i>Status</i></td>";
		foreach($datakodeorg as $regional => $val1){				
			foreach($val1 as $kebun => $namakebun){			
				$tab.="<td style=background-color:#E8DAEF align=center><i>Ha</i></td>  
				<td style=background-color:#E8DAEF align=center><i>Pokok</i></td>";
			}	
		}
	$tab.="</tr>";

	$tab.="<tr class=rowcontent>
		<td align=center>1</td><td align=center>TM</td>
		<td align=left>".strtoupper($_SESSION['lang']['tm'])."</td>";
		foreach($datakodeorg as $regional => $val1){				
			foreach($val1 as $kebun => $namakebun){	
				$tab.="  
				<td align=center>".@nantozero($luasreal[$regional][$kebun],2)."</td>  
				<td align=center>".@nantozero($pkkreal[$regional][$kebun],2)."</td>  
				";
			}
		}
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>
		<td align=center>4</td><td align=center>PRD</td>
		<td align=left>".strtoupper($_SESSION['lang']['produksi'])." (Kg)</td>";
		foreach($datakodeorg as $regional => $val1){				
			foreach($val1 as $kebun => $namakebun){	
				$clickproduksi="";
				if($kebun!='HHO' and substr($kebun,0,5)!='TOTAL'){	
					$clickproduksi="onclick=getproduksi('".$prd."','".$kebun."','I'); title='Click it' style=color:blue;cursor:pointer;";
				}
				$tab.="  
				<td align=center colspan='2' ".$clickproduksi.">".@nantozero(($produksi[$regional][$kebun]))."</td>  
				";
			}
		}
	$tab.="</tr>
	<tr class=rowcontent>
		<td align=center>5</td><td align=center>YLD</td>
		<td align=left>YIELD (Ton /Ha)</td>";
		foreach($datakodeorg as $regional => $val1){				
			foreach($val1 as $kebun => $namakebun){	
				$clickproduksi="";
				if($kebun!='HHO' and substr($kebun,0,5)!='TOTAL'){	
					$clickproduksi="onclick=gettonperha('".getNamaOrg($kebun,'induk')."','".$kebun."','".$prd."','I'); title='Click it' style=color:blue;cursor:pointer;";
				}
				$tab.="  
				<td align=center colspan='2' ".$clickproduksi.">".@nantozero(($produksi[$regional][$kebun]/1000)/$luasreal[$regional][$kebun],2)."</td>  
				";
			}
		}
	$tab.="</tr>";


$sql = "select * from " . $dbname . ".setup_kegiatan";
$req = fetchdata($sql);
foreach($req as $val){					
	$datakegiatan[$val['kodekegiatan']] = $val['kodekegiatan'];
}

$nmkode=array('pml'=>'Pemeliharaan TM','pnn'=>'Panen dan Pengangkutan','umm'=>'Tidak Langsung (Umum)','amor'=>'Biaya Amortisasi dan Depresiasi');

$biaya=[];
$str = "select substr(noakun,1,5) as jobgroup, noakun,kodekegiatan, sum(jumlah) as rupiah, kodeorg from ".$dbname.".keu_jurnaldt_vw a where 1=1 ".$where." and periode <= '".$prd."' and periode like '".$tahun."%' ".$whereakun." group by kodeorg, substr(noakun,1,5), noakun,kodekegiatan";
$res = fetchdata($str);
foreach($res as $bar){
	if($tipe=='group' and substr($bar['noakun'],0,1)=='7'){
		$bar['jobgroup']=substr($bar['jobgroup'],0,3);
	}
	
	$biaya[$bar['jobgroup']][$getregion[$bar['kodeorg']]][$bar['kodeorg']] += $bar['rupiah'];
	$biaya[$bar['jobgroup']][$getregion[$bar['kodeorg']]]['TOTAL'.$getregion[$bar['kodeorg']]] += $bar['rupiah'];
	$biaya[$bar['jobgroup']]['GRANDTOTAL']['HHO'] += $bar['rupiah'];
	
	if($tipe=='kegiatan'){
		if(substr($bar['noakun'],0,1)!='7'){
			$bar['noakun']=$bar['kodekegiatan'];
		}
	}
	
	$cdbgtthn[$bar['noakun']][$getregion[$bar['kodeorg']]][$bar['kodeorg']] += $bar['rupiah'];
	$cdbgtthn[$bar['noakun']][$getregion[$bar['kodeorg']]]['TOTAL'.$getregion[$bar['kodeorg']]] += $bar['rupiah'];
	$cdbgtthn[$bar['noakun']]['GRANDTOTAL']['HHO'] += $bar['rupiah'];
	
	if($datakegiatan[$bar['kodekegiatan']]=='' and $tipe=='kegiatan'){		
		if(substr($bar['noakun'],0,3)=='621'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
		}
		
		if(substr($bar['noakun'],0,3)=='611'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['pnn'][$bar['noakun']] = $bar['noakun'];
		}
	}
}

ksort($arrakun7);
ksort($cdarrakun);


$str = "select * from ".$dbname.".kebun_2commentreport a where 1=1 ".$whunit." and periode <= '".$prd."' and periode like '".$tahun."%' and bi='sdbi' and act='real' ";
$res = fetchdata($str);
foreach($res as $bar){
	if($tipe=='group'){
		$substr='5';
	}elseif($tipe=='code'){
		$substr='7';
	}elseif($tipe=='kegiatan'){
		$substr='9';
	}
	
	$showcomment[substr($bar['unit'],0,4)][substr($bar['kegiatan'],0,$substr)][]=array('id'=>$bar['id'],'user'=>$bar['createdby'],'comment'=>substr($bar['comment'],0,40));
	$showcomment[substr($bar['unit'],0,4)][substr($bar['kegiatan'],0,3)][]=array('id'=>$bar['id'],'user'=>$bar['createdby'],'comment'=>substr($bar['comment'],0,40));
}

// echo"<pre>";
// print_r($showcomment);
// echo"</pre>";





ksort($arrakun);
ksort($listakun);


#isinya ada 2 jenis
if($tipe=='kegiatan'){
	$tipe='code';
}
switch ($tipe) {
	case'group':
		foreach($arrkode as $kode){
			$tab.="<tr class=rowcontent ".$show." style=background-color:#4abdb7;font-weight:bold;>";
			$tab.="<td style=background-color:#4abdb7;font-weight:bold;></td>";
			$tab.="<td style=background-color:#4abdb7;font-weight:bold;></td>";
			$tab.="<td style=background-color:#4abdb7;font-weight:bold; colspan=8><i>".strtoupper($nmkode[$kode])."</i></td>";
			for($i=1;$i<=($jlhcol-7);$i++){
				$tab.="<td style=background-color:#4abdb7;font-weight:bold;><i></i></td>";
			}
			$tab.="</tr>";
			$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
				<td style=background-color:#E8DAEF;font-style:italic;></td>  
				<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
				<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
				foreach($datakodeorg as $regional => $val1){				
					foreach($val1 as $kebun => $namakebun){	
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
						<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Kg</i></td>";
					}	
				}
			$tab.="</tr>";			
			$no=0;
			foreach($arrakun as $akun){
				if(@$listakun[$kode][$akun]!=''){					
					$no++;
					$show="";
					if(substr($akun,0,1)=='7'){
						#$show=" style=display:none";
					}
					$tab.="<tr class=rowcontent ".$show.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$akun."</td>";
					$tab.="<td nowrap>".$nmakun[$akun]."</td>";
					foreach($datakodeorg as $regional => $val1){				
						foreach($val1 as $kebun => $namakebun){						
							@$rpbgtthnsat[$kebun]=$biaya[$akun][$regional][$kebun]/$produksi[$regional][$kebun];
							
							$adacomment=""; $flag=0;
							$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
							if(!empty($showcomment[$kebun][$akun])){
								$adacomment="class=has_sign"; $flag='1';
								$title=" title='".getKary($showcomment[$kebun][$akun][0]['user'])."\n".$showcomment[$kebun][$akun][0]['comment']."\n\nClick Kanan -> Show Comment.'";
							}
							$detbgtthn="";
							if($kebun!='HHO' and substr($kebun,0,5)!='TOTAL'){							
								$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$akun."','".$kebun."','".$prd."','sdbi')";
								$detbgtthn.=$title." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$kebun."','".$prd."','sdbi','real')\"";
							}
							
							
							
							$tab.="<td align=right ".$detbgtthn." ".$adacomment.">".@nantozero($biaya[$akun][$regional][$kebun])."</td>";
							$tab.="<td align=right>".@nantozero($rpbgtthnsat[$kebun],2)."</td>";

							@$ttlbgtthn[$kode][$kebun]+=$biaya[$akun][$regional][$kebun];
							@$tbgtthn[$kebun]+=$biaya[$akun][$regional][$kebun];
						}						
					}
					$tab.="</tr>";
				}
			}
			# sub total
			$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
			$tab.="<td style=background-color:#D5F5E3 align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			foreach($datakodeorg as $regional => $val1){				
				foreach($val1 as $kebun => $namakebun){	
					if($kode=='umm'){
						$detbgtthn="";
						if($kebun!='HHO' and substr($kebun,0,5)!='TOTAL'){						
							$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$akun."','".$kebun."','".$prd."','sdbi')";
						}
					}else{
						$detbgtthn="";
					}
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$produksi[$regional][$kebun];
					
					$tab.="<td style=background-color:#D5F5E3 align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode][$kebun])."</td>";
					$tab.="<td style=background-color:#D5F5E3 align=right>".@nantozero($ttlrpbgtthnsat[$kebun],2)."</td>";
				}
			}
			$tab.="</tr>";
		}
	#grand total
	$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
	$tab.="<td style=background-color:#27ED1C align=center colspan=3>GRAND TOTAL</td>";
	
	foreach($datakodeorg as $regional => $val1){				
		foreach($val1 as $kebun => $namakebun){	
			$tab.="<td style=background-color:#27ED1C align=right>".@nantozero($tbgtthn[$kebun])."</td>";
			$tab.="<td style=background-color:#27ED1C align=right>".@nantozero($tbgtthn[$kebun]/@$produksi[$regional][$kebun],2)."</td>";
		}
	}
	
	break;
	
	case'code':
		foreach($arrkode as $kode){
			$tab.="<tr class=rowcontent ".$show." style=background-color:#4abdb7;font-weight:bold;>";
			$tab.="<td style=background-color:#4abdb7;font-weight:bold;></td>";
			$tab.="<td style=background-color:#4abdb7;font-weight:bold;></td>";
			$tab.="<td style=background-color:#4abdb7;font-weight:bold; colspan=8><i>".strtoupper($nmkode[$kode])."</i></td>";
			for($i=1;$i<=($jlhcol-7);$i++){
				$tab.="<td style=background-color:#4abdb7;font-weight:bold;><i></i></td>";
			}
			$tab.="</tr>";
			$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
				<td style=background-color:#E8DAEF;font-style:italic;></td>  
				<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
				<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
				foreach($datakodeorg as $regional => $val1){				
					foreach($val1 as $kebun => $namakebun){	
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
						<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Kg</i></td>";
					}	
				}
			$tab.="</tr>";			
			
			$no=0;
			foreach($arrakun7 as $akun){
				if(@$cdarrakun[$kode][$akun]!=''){					
					$no++;
					$show="";
					if(substr($akun,0,1)=='7'){
						#$show=" style=display:none";
					}
					if($tipex=='kegiatan' and substr($akun,0,1)!='7'){
						$d=substr($akun,0,7);
					}else{						
						$d=substr($akun,0,5);
					}
					if($d!=$n){
						$tab.="<tr class=rowcontent ".$show." style=background-color:#ccfffd;font-weight:bold;>";
						$tab.="<td style=background-color:#ccfffd;font-weight:bold;></td>";
						$tab.="<td style=background-color:#ccfffd;font-weight:bold;><i>".$d."</i></td>";
						$tab.="<td style=background-color:#ccfffd;font-weight:bold; colspan=8><i>".strtoupper(strtolower(getNamaAkun($d)))."</i></td>";
						for($i=1;$i<=($jlhcol-7);$i++){
							$tab.="<td style=background-color:#ccfffd;font-weight:bold;><i></i></td>";
						}
						$tab.="</tr>";
					}
					
					
					$tab.="<tr class=rowcontent ".$show.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$akun."</td>";
					$tab.="<td>".$nmakun[$akun]."</td>";
					foreach($datakodeorg as $regional => $val1){				
						foreach($val1 as $kebun => $namakebun){						
							@$rpbgtthnsat[$kebun]=$cdbgtthn[$akun][$regional][$kebun]/$produksi[$regional][$kebun];
							
							$adacomment=""; $flag=0;
							$title=" title='Click kiri untuk melihat detail.\nClick kanan untuk Add Comment.'";
							if(!empty($showcomment[$kebun][$akun])){
								$adacomment="class=has_sign"; $flag=1;
								$title=" title='".getKary($showcomment[$kebun][$akun][0]['user'])."\n".$showcomment[$kebun][$akun][0]['comment']."\n\nClick Kanan -> Show Comment.'";
							}
							
							$detbgtthn="";
							if($kebun!='HHO' and substr($kebun,0,5)!='TOTAL'){							
								$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$akun."','".$kebun."','".$prd."','sdbi')";
								$detbgtthn.=$title." onmousedown=\"rightclick(event,this,'".$flag."','".$akun."','".$kebun."','".$prd."','sdbi','real')\"";
							}
							
							$tab.="<td align=right ".$detbgtthn."  ".$adacomment.">".@nantozero($cdbgtthn[$akun][$regional][$kebun])."</td>";
							$tab.="<td align=right>".@nantozero($rpbgtthnsat[$kebun],2)."</td>";

							@$ttlbgtthn[$kode][$kebun]+=$cdbgtthn[$akun][$regional][$kebun];
							@$tbgtthn[$kebun]+=$cdbgtthn[$akun][$regional][$kebun];
						}
					}					
					$tab.="</tr>";
				}
				$n=$d;
			}
			
			# sub total
			$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
			$tab.="<td style=background-color:#D5F5E3 align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			
			foreach($datakodeorg as $regional => $val1){				
				foreach($val1 as $kebun => $namakebun){					
					if($kode=='umm'){
						$detbgtthn="";
						if($kebun!='HHO' and substr($kebun,0,5)!='TOTAL'){						
							$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$akun."','".$kebun."','".$prd."','sdbi')";
						}
					}else{
						$detbgtthn="";
					}
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$produksi[$regional][$kebun];
					
					$tab.="<td style=background-color:#D5F5E3 align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode][$kebun])."</td>";
					$tab.="<td style=background-color:#D5F5E3 align=right>".@nantozero($ttlrpbgtthnsat[$kebun],2)."</td>";
				}
			}
			$tab.="</tr>";
		}
		
	#grand total
	$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
	$tab.="<td style=background-color:#27ED1C align=center colspan=3>GRAND TOTAL</td>";
	
	foreach($datakodeorg as $regional => $val1){				
		foreach($val1 as $kebun => $namakebun){	
			$tab.="<td style=background-color:#27ED1C align=right>".@nantozero($tbgtthn[$kebun])."</td>";
			$tab.="<td style=background-color:#27ED1C align=right>".@nantozero($tbgtthn[$kebun]/@$produksi[$regional][$kebun],2)."</td>";
		}
	}
	
	break;
}
 
$tab.="</tbody></table>";

switch ($proses) {
######PREVIEW
    case 'preview':
		$tab.="<br><br>";
		echo $tab;
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