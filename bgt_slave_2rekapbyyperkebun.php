<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

// ini_set('display_errors',1);
// error_reporting(1);

$proses  = checkPostGet('proses', '');
$kdorg   = checkPostGet('kdorg', '');
$pt      = checkPostGet('pt', '');
$tt      = checkPostGet('tt', '');
$ip      = checkPostGet('ip', '');
$divisi  = checkPostGet('divisi', '');
$prd     = checkPostGet('prd', '');
$tipe    = checkPostGet('tipe', '');
$tipex    = checkPostGet('tipe', '');

$arrbi   = explode('-',$prd); 
$tahun   = $arrbi[0];
$tahundpn= $tahun+1;
$bulan   = '12';
$periode1= $tahun."-01";
$periode2= $prd;
$prddpn  = $tahundpn."-".$bulan;

// if($pt==''){exit("warning : Kode PT harus di pilih.");}

$whlistkdorg="";
$where='';$where2='';$where_spb=$where_kap='';

$ip = 'I';

if($ip!=''){
	$whip=" and intiplasma='".$ip."'";
	if($ip=='I'){
		$inti='1';
	}else{
		$inti='0';
	}
	$whipkebun=" and inti='".$inti."'";
}
$listblokip = [];
if($pt!=''){
	$listkodeorg = [];
	$str = "select * from " . $dbname . ".organisasi where induk='".$pt."' and tipe='KEBUN' ".$whipkebun."";
	$res = fetchdata($str);
	foreach($res as $bar){
		$listkodeorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	}
	$where=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
	$where2=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
	$where_spb=" and a.kodeorg in ('".implode("','",$listkodeorg)."')";
	$whunit=" and substr(a.unit,1,4) in ('".implode("','",$listkodeorg)."')";
	$where_kap=" and kodeunit in ('".implode("','",$listkodeorg)."')";
	$whlistkdorg.=" and induk='".$pt."'";
}else{
	$listkodeorg = [];
	$str = "select * from " . $dbname . ".organisasi where tipe='KEBUN' ".$whipkebun."";
	$res = fetchdata($str);
	foreach($res as $bar){
		$listkodeorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	}

	$where=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
	$where2=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
	$where_spb=" and a.kodeorg in ('".implode("','",$listkodeorg)."')";
	$whunit=" and substr(a.unit,1,4) in ('".implode("','",$listkodeorg)."')";
	
	$where_kap=" and kodeunit in ('".implode("','",$listkodeorg)."')";
}


if($kdorg!=''){
	$where=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$where2=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
	$where_spb=" and a.kodeorg ='".$kdorg."'";
	$where_kap=" and kodeunit ='".$kdorg."'";
	
	$whlistkdorg.=" and kodeorganisasi='".$kdorg."'";
}

$wh="";$wh2="";$whB="";$wh_spb="";$wh_bgt=$wh_bgtrp='';
if($divisi!=''){
	$wh.=" and a.kodeblok like '".$divisi."%'";
	$whB.=" and a.kodeblok like '".$divisi."%'";
	$wh2.=" and a.kodeorg like '".$divisi."%'";
	$wh_spb.=" and b.blok like '".$divisi."%'";
	$wh_bgt.=" and a.divisi like '".$divisi."%'";
	$wh_bgtrp.=" and a.kodeorg like '".$divisi."%'";
}
if($tt!=''){
	$wh.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
	$wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
	$wh2.=" and a.tahuntanam='".$tt."'";
	$whB.=" and a.thntnm='".$tt."'";
	$wh_spb.=" and b.blok in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
}
if($ip!=''){
	$wh.=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
	$wh_bgt.=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
	$wh2.=" and a.intiplasma='".$ip."'";
	$whB.=" and a.intiplasma='".$ip."'";
	$wh_spb.=" and substr(b.blok,1,4) in ('".implode("','",$listkodeorg)."')";
	$wh_bgtrp.=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
}


#=============== mari kita mulai dari sini ===============#

$str = "select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN' ".$whlistkdorg." and inti='1' order by induk, kodeorganisasi";
$res = fetchdata($str);
foreach($res as $bar){
	//$listkodeorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
}

$whereakun = " and (substr(noakun,1,3) in ('611','621','128','126','127') or noakun like '7%')";
// $nmakun = makeOption($dbname,'keu_5akun','noakun,namaakun');

$str = "select * from " . $dbname . ".keu_5akun"; 
$req = fetchdata($str);
foreach($req as $val){					
	$nmakun[$val['noakun']]=$val['namaakun'];
}

$str = "select * from " . $dbname . ".setup_kegiatan"; 
$req = fetchdata($str);
foreach($req as $val){					
	$nmakun[$val['kodekegiatan']]=$val['namakegiatan'];
}

$arrakun=array();
$arrkode=array('kap'=>'kap','umm'=>'umm','bbt'=>'bbt','tbm'=>'tbm','pml'=>'pml','pnn'=>'pnn');
$str = "select * from " . $dbname . ".keu_5akun  where 1=1 ".$whereakun.""; 
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if(substr($bar['noakun'],0,3)=='127'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['kap'][$bar['noakun']] = $bar['noakun'];
		}
		if(strlen($bar['noakun'])=='7'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['kap'][$bar['noakun']] = $bar['noakun'];
		}
	}
	if(substr($bar['noakun'],0,3)=='128'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['bbt'][$bar['noakun']] = $bar['noakun'];
		}
		if($tipe=='kegiatan'){
			if(strlen($bar['noakun'])=='7'){
				$sql = "select * from " . $dbname . ".setup_kegiatan  where 1=1 and noakun='".$bar['noakun']."'";
				$req = fetchdata($sql);
				foreach($req as $val){					
					$arrakun7[$val['kodekegiatan']] = $val['kodekegiatan'];
					$cdarrakun['bbt'][$val['kodekegiatan']] = $val['kodekegiatan'];
				}
			}
		}else{			
			if(strlen($bar['noakun'])=='7'){
				$arrakun7[$bar['noakun']] = $bar['noakun'];
				$cdarrakun['bbt'][$bar['noakun']] = $bar['noakun'];
			}
		}
	}
	if(substr($bar['noakun'],0,3)=='126'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['tbm'][$bar['noakun']] = $bar['noakun'];
		}
		if($tipe=='kegiatan'){
			if(strlen($bar['noakun'])=='7'){
				$sql = "select * from " . $dbname . ".setup_kegiatan  where 1=1 and noakun='".$bar['noakun']."'";
				$req = fetchdata($sql);
				foreach($req as $val){					
					$arrakun7[$val['kodekegiatan']] = $val['kodekegiatan'];
					$cdarrakun['tbm'][$val['kodekegiatan']] = $val['kodekegiatan'];
				}
			}
		}else{			
			if(strlen($bar['noakun'])=='7'){
				$arrakun7[$bar['noakun']] = $bar['noakun'];
				$cdarrakun['tbm'][$bar['noakun']] = $bar['noakun'];
			}
		}
	}
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
	
	//$listkodeorg[$bar['kodeorg']]=$bar['kodeorg'];
}

#ambil prd bgt
$e="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="kg".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";

$str=" select kodeunit,divisi,thntnm,kodeblok,".$e." as sdbi,kg".$bulan." as bi,kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." ".$whB." ".$wh_bgt." and tahunbudget = '".$tahun."'";
// exit("Error: ".$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$prdbgtbi[$bar['kodeunit']] += $bar['bi'];
	@$prdbgtsdbi[$bar['kodeunit']] += $bar['sdbi'];
	@$prdbgtthn[$bar['kodeunit']] += $bar['kgsetahun'];
	
	@$prdbgtbi['TOTAL'] += $bar['bi'];
	@$prdbgtsdbi['TOTAL'] += $bar['sdbi'];
	@$prdbgtthn['TOTAL'] += $bar['kgsetahun'];
	
	//$listkodeorg[$bar['kodeunit']]=$bar['kodeunit'];
}

$e="("; $s="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
}
$e.=")"; $s.=")";
$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";

#ini khusus budget kebun
$str=" select substr(kodeorg,1,4) as kebun, substr(noakun,1,5) as jobgroup, tipebudget, kodebudget, noakun, kegiatan, sum(".$e.") as sdbi,sum(rp".$bulan.") as bi,sum(rupiah) as rupiah, tahunbudget, kodeorg, sum(".$s.") as sdbivol, sum(fis".$bulan.") as bivol, sum(jumlah) as jumlah, satuanj, sum(volume) as volume, sum(".$t.") as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whereakun." group by substr(noakun,1,5), noakun,kegiatan, substr(kodeorg,1,4)";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
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
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
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
}


$e="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="k".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";
#ambil budget kapital thn ini dan tahun depan
$str = "select bgt_kapital_vw.*, ".$e." as sdbi,k".$bulan." as bi from " . $dbname . ".bgt_kapital_vw  where 1=1 ".$where_kap." and tahunbudget = '".$tahun."' and pta='BGT'"; 
$res = fetchdata($str);
foreach($res as $bar){
	@$bgtbi[substr($bar['noakun'],0,5)][$bar['kodeunit']] += $bar['bi'];
	@$bgtsdbi[substr($bar['noakun'],0,5)][$bar['kodeunit']] += $bar['sdbi'];
	@$bgtthn[substr($bar['noakun'],0,5)][$bar['kodeunit']] += $bar['harga'];
	
	@$cdbgtbi[$bar['noakun']][$bar['kodeunit']] += $bar['bi'];
	@$cdbgtsdbi[$bar['noakun']][$bar['kodeunit']] += $bar['sdbi'];
	@$cdbgtthn[$bar['noakun']][$bar['kodeunit']] += $bar['harga'];
	
	@$bgtbi[substr($bar['noakun'],0,5)]['TOTAL'] += $bar['bi'];
	@$bgtsdbi[substr($bar['noakun'],0,5)]['TOTAL'] += $bar['sdbi'];
	@$bgtthn[substr($bar['noakun'],0,5)]['TOTAL'] += $bar['harga'];
	
	@$cdbgtbi[$bar['noakun']]['TOTAL'] += $bar['bi'];
	@$cdbgtsdbi[$bar['noakun']]['TOTAL'] += $bar['sdbi'];
	@$cdbgtthn[$bar['noakun']]['TOTAL'] += $bar['harga'];
}

sort($listkodeorg);
$listkodeorg["TOTAL"]="TOTAL";

if ($proses == 'excel') {
	$arrtipe=array("group"=>"Per Job Group","code"=>"Per Job Code");
	$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	if($kdorg!=''){$xkdorg=$nmorg[$kdorg];}else{$xkdorg=$_SESSION['lang']['all'];}
	if($divisi!=''){$xdivisi=$nmorg[$divisi];}else{$xdivisi=$_SESSION['lang']['all'];}
	if($tt!=''){$xtt=$tt;}else{$xtt=$_SESSION['lang']['all'];}
	if($ip!=''){$xip=$ip;}else{$xip=$_SESSION['lang']['all'];}
	
	
	$tab="<table class=sortable cellspacing=1 width=100%>";
	$tab.="<tr><td align=center colspan=12>REKAP BUDGET (".$arrtipe[$tipe].")</td>";
	$tab.="<tr><td align=center colspan=12>" . $_SESSION['lang']['pt'] . " : ".$nmorg[$pt].";&nbsp;";
	$tab.="" . $_SESSION['lang']['unit'] . " : ".$xkdorg."</td></tr>";
	$tab.="<tr><td align=center colspan=12>" . $_SESSION['lang']['divisi'] . " : ".$xdivisi.";&nbsp;";
	$tab.="" . $_SESSION['lang']['tahuntanam'] . " : ".$xtt."</td></tr>";
	$tab.="<tr><td align=center colspan=12>".$_SESSION['lang']['periode'] . " : ".$prd."</td></tr>";
    $tab.="<table class=sortable cellspacing=1 border=1>";
} else {
    $tab = "<div class='table-scroll'><table class=sortable cellpadding=5 cellspacing=1>";
}

$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan='2'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['kode']."</th>
            <th align=center rowspan='2'>Activity</th>";
			foreach($listkodeorg as $kebun){				
				$tab.="<th align=center rowspan=2 colspan=2>".$kebun."</th>";
			}
        $tab.="</tr>
    </thead>
 <tbody>";
$tab.="<tr class=rowcontent ".$show." style=background-color:#4abdb7;font-weight:bold;>";
$tab.="<td></td>";
$tab.="<td></td>";
$tab.="<td colspan=".((count($listkodeorg)*2)+1)."><i>AREAL STATEMENT DAN PRODUKSI</i></td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent>
	<td style=background-color:#E8DAEF></td>  
	<td style=background-color:#E8DAEF align=center><i></i></td>  
	<td style=background-color:#E8DAEF align=center><i>Status</i></td>";
	foreach($listkodeorg as $kebun){		
		$tab.="<td style=background-color:#E8DAEF align=center><i>Ha</i></td>  
		<td style=background-color:#E8DAEF align=center><i>Pokok</i></td>";
	}
$tab.="</tr>";


	$tab.="<tr class=rowcontent>
		<td align=center>1</td><td align=center>TBM</td>
		<td align=left>".strtoupper($_SESSION['lang']['tbm'])."</td>";
		foreach($listkodeorg as $kebun){	
			$tab.="<td align=center>".@nantozero($luasbudgettbm[$kebun],2)."</td>  
			<td align=center>".@nantozero($pkkbudgettbm[$kebun],2)."</td>";
		}
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>
		<td align=center>2</td><td align=center>TM</td>
		<td align=left>".strtoupper($_SESSION['lang']['tm'])."</td>";
		foreach($listkodeorg as $kebun){	
			$tab.="  
			<td align=center>".@nantozero($luasbudget[$kebun],2)."</td>  
			<td align=center>".@nantozero($pkkbudget[$kebun],2)."</td>  
			";
		}
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>
		<td style=background-color:#D5F5E3;font-style:italic;font-weight:bold; align=center></td>
		<td style=background-color:#D5F5E3;font-style:italic;font-weight:bold; align=center>TNM</td>
		<td style=background-color:#D5F5E3;font-style:italic;font-weight:bold; align=left>TANAMAN (TBM + TM)</td>";
		foreach($listkodeorg as $kebun){	
			$tab.="  
			<td style=background-color:#D5F5E3;font-style:italic;font-weight:bold; align=center>".@nantozero($luasbudgettbm[$kebun]+$luasbudget[$kebun],2)."</td>  
			<td style=background-color:#D5F5E3;font-style:italic;font-weight:bold; align=center>".@nantozero($pkkbudgettbm[$kebun]+$pkkbudget[$kebun],2)."</td>  
			";
		}
	$tab.="</tr>";
	$tab.="<tr class=rowcontent>
		<td align=center>3</td><td align=center>BBT</td>
		<td align=left>PEMBIBITAN</td>";
		foreach($listkodeorg as $kebun){	
			$tab.="
			<td align=center>".@nantozero($luasbudgetbbt[$kebun],2)."</td>  
			<td align=center>".@nantozero($pkkbudgetbbt[$kebun],2)."</td>  
			";
		}
	$tab.="</tr>";

	$tab.="<tr class=rowcontent>
		<td style=background-color:#D5F5E3;font-style:italic;font-weight:bold; align=center></td>
		<td style=background-color:#D5F5E3;font-style:italic;font-weight:bold; align=center>USH</td>
		<td style=background-color:#D5F5E3;font-style:italic;font-weight:bold; align=left>TOTAL AREAL DIUSAHAKAN (BBT + TBM + TM)</td>";
		foreach($listkodeorg as $kebun){	
			$tab.="  
			<td style=background-color:#D5F5E3;font-style:italic;font-weight:bold; align=center>".@nantozero($luasbudgettbm[$kebun]+$luasbudget[$kebun]+$luasbudgetbbt[$kebun],2)."</td>  
			<td style=background-color:#D5F5E3;font-style:italic;font-weight:bold; align=center>".@nantozero($pkkbudgettbm[$kebun]+$pkkbudget[$kebun]+$pkkbudgetbbt[$kebun],2)."</td>  
			";
		}
	$tab.="</tr>";

	$tab.="<tr class=rowcontent>
		<td align=center>4</td><td align=center>PRD</td>
		<td align=left>".strtoupper($_SESSION['lang']['produksi'])." (Ton)</td>";
		foreach($listkodeorg as $kebun){	
			$tab.="  
			<td align=center colspan='2'>".@nantozero(($prdbgtthn[$kebun]/1000),2)."</td>  
			";
		}
	$tab.="</tr>
	<tr class=rowcontent>
		<td align=center>5</td><td align=center>YLD</td>
		<td align=left>YIELD (Ton /Ha)</td>";
		foreach($listkodeorg as $kebun){	
			$tab.="  
			<td align=center colspan='2'>".@nantozero(($prdbgtthn[$kebun]/1000)/$luasbudget[$kebun],2)."</td>  
			";
		}
	$tab.="</tr>";

foreach($listkodeorg as $kebun){	
	$luasbudgetdpnush[$kebun]= $luasbudgettbmdpn[$kebun]+$luasbudgetdpn[$kebun]+$luasbudgetbbtdpn[$kebun];
	$luasrealush[$kebun]     = $luasrealtbm[$kebun]+$luasreal[$kebun]+$luasrealbbt[$kebun];
	$luasbudgetush[$kebun]   = $luasbudgettbm[$kebun]+$luasbudget[$kebun]+$luasbudgetbbt[$kebun];
}


$nmkode=array('kap'=>'Kapital','bbt'=>'Pembibitan','tbm'=>'Pemeliharaan TBM','pml'=>'Pemeliharaan TM','pnn'=>'Panen dan Pengangkutan','umm'=>'Tidak Langsung (Umum)');

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
			$tab.="<td style=background-color:#4abdb7;font-weight:bold; colspan=".((count($listkodeorg)*2)+1)."><i>".strtoupper($nmkode[$kode])."</i></td>";
			$tab.="</tr>";
			if($kode=='bbt'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td style=background-color:#E8DAEF;font-style:italic;></td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
					foreach($listkodeorg as $kebun){
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
							<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Pkk</i></td>";
					}
				$tab.="</tr>";			
			}
			if($kode=='tbm'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td style=background-color:#E8DAEF;font-style:italic;></td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
					foreach($listkodeorg as $kebun){
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
						<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Ha TBM</i></td>";
					}
				$tab.="</tr>";			
			}
			if($kode=='pml'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td style=background-color:#E8DAEF;font-style:italic;></td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
					foreach($listkodeorg as $kebun){
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
							<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Ha TM</i></td>";
					}
				$tab.="</tr>";			
			}
			if($kode=='umm' or $kode=='kap'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td style=background-color:#E8DAEF;font-style:italic;></td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
					foreach($listkodeorg as $kebun){
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
							<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Ha (ush)</i></td>";
					}
				$tab.="</tr>";			
			}
			if($kode=='pnn'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td style=background-color:#E8DAEF;font-style:italic;></td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
					foreach($listkodeorg as $kebun){
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
							<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Kg</i></td>";
					}
				$tab.="</tr>";			
			}
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
					
					#====== thn ======
					foreach($listkodeorg as $kebun){						
						if($kode=='pnn'){
							@$rpbgtthnsat[$kebun]=$bgtthn[$akun][$kebun]/$prdbgtthn[$kebun];
						}elseif($kode=='kap' or $kode=='umm'){
							@$rpbgtthnsat[$kebun]=$bgtthn[$akun][$kebun]/$luasbudgetush[$kebun];
						}elseif($kode=='tbm'){
							@$rpbgtthnsat[$kebun]=$bgtthn[$akun][$kebun]/$luasbudgettbm[$kebun];
						}elseif($kode=='bbt'){
							@$rpbgtthnsat[$kebun]=$bgtthn[$akun][$kebun]/$pkkbudgetbbt[$kebun];
						}else{
							@$rpbgtthnsat[$kebun]=$bgtthn[$akun][$kebun]/$luasbudget[$kebun];
						}
						
						$detbgtthn="";
						if($kebun!='TOTAL'){							
							$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kebun."','".$tt."','".$ip."','".$divisi."','".$prd."-12','".$tipe."','".$akun."','html','thn','budget')";
						}
						
						$tab.="<td align=right ".$detbgtthn.">".@nantozero($bgtthn[$akun][$kebun])."</td>";
						$tab.="<td align=right>".@nantozero($rpbgtthnsat[$kebun],2)."</td>";

						@$ttlbgtthn[$kode][$kebun]+=$bgtthn[$akun][$kebun];
						@$tbgtthn[$kebun]+=$bgtthn[$akun][$kebun];
					}
					#===== end thn =====
					
					$tab.="</tr>";
					
				}
			}
			
			# sub total
			
			
			$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
			$tab.="<td style=background-color:#D5F5E3 align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			foreach($listkodeorg as $kebun){
				if($kode=='umm'){
					$detbgtthn="";
					if($kebun!='TOTAL'){						
						$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kebun."','".$tt."','".$ip."','".$divisi."','".$prd."-12','".$tipe."','7','html','thn','budget')";
					}
				}else{
					$detbgtthn="";
				}
				
				
				#====== thn ======
				if($kode=='pnn'){
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$prdbgtthn[$kebun];
				}elseif($kode=='kap' or $kode=='umm'){
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$luasbudgetush[$kebun];
				}elseif($kode=='tbm'){
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$luasbudgettbm[$kebun];
				}elseif($kode=='bbt'){
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$pkkbudgetbbt[$kebun];
				}else{
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$luasbudget[$kebun];
				}
				$tab.="<td style=background-color:#D5F5E3 align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode][$kebun])."</td>";
				$tab.="<td style=background-color:#D5F5E3 align=right>".@nantozero($ttlrpbgtthnsat[$kebun],2)."</td>";
				#===== end thn =====
				
			}
			
			$tab.="</tr>";
		}
	#grand total
	$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
	$tab.="<td style=background-color:#27ED1C align=center colspan=3>GRAND TOTAL</td>";
	
	foreach($listkodeorg as $kebun){		
		#====== thn ======
		$tab.="<td style=background-color:#27ED1C align=right>".@nantozero($tbgtthn[$kebun])."</td>";
		$tab.="<td style=background-color:#27ED1C align=right>".@nantozero($tbgtthn[$kebun]/@$prdbgtthn[$kebun],2)."</td>";
		#===== end thn =====
	}
	
	break;
	
	case'code':
		foreach($arrkode as $kode){
			$tab.="<tr class=rowcontent ".$show." style=background-color:#4abdb7;font-weight:bold;>";
			$tab.="<td style=background-color:#4abdb7;font-weight:bold;></td>";
			$tab.="<td style=background-color:#4abdb7;font-weight:bold;></td>";
			$tab.="<td style=background-color:#4abdb7;font-weight:bold; colspan=".((count($listkodeorg)*2)+1)."><i>".strtoupper($nmkode[$kode])."</i></td>";
			$tab.="</tr>";
			if($kode=='bbt'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td style=background-color:#E8DAEF;font-style:italic;></td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
					foreach($listkodeorg as $kebun){
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
							<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Pkk</i></td>";
					}
				$tab.="</tr>";			
			}
			if($kode=='tbm'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td style=background-color:#E8DAEF;font-style:italic;></td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
					foreach($listkodeorg as $kebun){
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
						<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Ha TBM</i></td>";
					}
				$tab.="</tr>";			
			}
			if($kode=='pml'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td style=background-color:#E8DAEF;font-style:italic;></td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
					foreach($listkodeorg as $kebun){
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
							<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Ha TM</i></td>";
					}
				$tab.="</tr>";			
			}
			if($kode=='umm' or $kode=='kap'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td style=background-color:#E8DAEF;font-style:italic;></td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
					foreach($listkodeorg as $kebun){
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
							<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Ha (ush)</i></td>";
					}
				$tab.="</tr>";			
			}
			if($kode=='pnn'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td style=background-color:#E8DAEF;font-style:italic;></td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Code</td>  
					<td style=background-color:#E8DAEF;font-style:italic; align=center>Activity Group</td>";
					foreach($listkodeorg as $kebun){
						$tab.="<td style=background-color:#E8DAEF;font-style:italic; align=center>Rupiah</td>  
							<td style=background-color:#E8DAEF;font-style:italic; align=center><i>Rp/Kg</i></td>";
					}
				$tab.="</tr>";			
			}
			$no=0;
			foreach($arrakun7 as $akun){
				if(@$cdarrakun[$kode][$akun]!=''){					
					$no++;
					$show="";
					if(substr($akun,0,1)=='7'){
						//$show=" style=display:none";
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
						$tab.="<td style=background-color:#ccfffd;font-weight:bold; colspan=".((count($listkodeorg)*2)+1)."><i>".strtoupper(strtolower(getNamaAkun($d)))."</i></td>";
						$tab.="</tr>";
					}
					
					
					$tab.="<tr class=rowcontent ".$show.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$akun."</td>";
					$tab.="<td>".$nmakun[$akun]."</td>";
					
					#====== thn ======
					foreach($listkodeorg as $kebun){						
						if($kode=='pnn'){
							@$rpbgtthnsat[$kebun]=$cdbgtthn[$akun][$kebun]/$prdbgtthn[$kebun];
						}elseif($kode=='kap' or $kode=='umm'){
							@$rpbgtthnsat[$kebun]=$cdbgtthn[$akun][$kebun]/$luasbudgetush[$kebun];
						}elseif($kode=='tbm'){
							@$rpbgtthnsat[$kebun]=$cdbgtthn[$akun][$kebun]/$luasbudgettbm[$kebun];
						}elseif($kode=='bbt'){
							@$rpbgtthnsat[$kebun]=$cdbgtthn[$akun][$kebun]/$pkkbudgetbbt[$kebun];
						}else{
							@$rpbgtthnsat[$kebun]=$cdbgtthn[$akun][$kebun]/$luasbudget[$kebun];
						}
						$detbgtthn="";
						if($kebun!='TOTAL'){							
							$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".getNamaOrg($kebun,'induk')."','".$kebun."','".$tt."','".$ip."','".$divisi."','".$prd."-12','".$tipe."','".$akun."','html','thn','budget')";
						}
						
						$tab.="<td align=right ".$detbgtthn.">".@nantozero($cdbgtthn[$akun][$kebun])."</td>";
						$tab.="<td align=right>".@nantozero($rpbgtthnsat[$kebun],2)."</td>";

						@$ttlbgtthn[$kode][$kebun]+=$cdbgtthn[$akun][$kebun];
						@$tbgtthn[$kebun]+=$cdbgtthn[$akun][$kebun];
					}
					#===== end thn =====					
					$tab.="</tr>";
				}
				
				$n=$d;
			}
			
			# sub total
			$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
			$tab.="<td style=background-color:#D5F5E3 align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			
			foreach($listkodeorg as $kebun){				
				if($kode=='umm'){
					$detbgtthn="";
					if($kebun!='TOTAL'){						
						$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".getNamaOrg($kebun,'induk')."','".$kebun."','".$tt."','".$ip."','".$divisi."','".$prd."-12','".$tipe."','7','html','thn','budget')";
					}
				}else{
					$detbgtthn="";
				}
				
				#====== thn ======
				if($kode=='pnn'){
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$prdbgtthn[$kebun];
				}elseif($kode=='kap' or $kode=='umm'){
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$luasbudgetush[$kebun];
				}elseif($kode=='tbm'){
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$luasbudgettbm[$kebun];
				}elseif($kode=='bbt'){
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$pkkbudgetbbt[$kebun];
				}else{
					@$ttlrpbgtthnsat[$kebun]=$ttlbgtthn[$kode][$kebun]/$luasbudget[$kebun];
				}
				$tab.="<td style=background-color:#D5F5E3 align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode][$kebun])."</td>";
				$tab.="<td style=background-color:#D5F5E3 align=right>".@nantozero($ttlrpbgtthnsat[$kebun],2)."</td>";
				#===== end thn =====
			}
			
			$tab.="</tr>";
		}
		
	
	#grand total
	$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
	$tab.="<td style=background-color:#27ED1C align=center colspan=3>GRAND TOTAL</td>";
	
	#====== thn ======
	foreach($listkodeorg as $kebun){		
		$tab.="<td style=background-color:#27ED1C align=right>".@nantozero($tbgtthn[$kebun])."</td>";
		$tab.="<td style=background-color:#27ED1C align=right>".@nantozero($tbgtthn[$kebun]/@$prdbgtthn[$kebun],2)."</td>";
	}
	#===== end thn =====
	
	break;
}
 
$tab.="</tbody></table></div>";

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
