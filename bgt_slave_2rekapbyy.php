<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses  = checkPostGet('proses', '');
$kdorg   = checkPostGet('kdorg', '');
$pt      = checkPostGet('pt', '');
$tt      = checkPostGet('tt', '');
$ip      = checkPostGet('ip', '');
$divisi  = checkPostGet('divisi', '');
$prd     = checkPostGet('prd', '');
$tipe    = checkPostGet('tipe', '');

$arrbi   = explode('-',$prd); 
$tahun   = $arrbi[0];
$tahundpn= $tahun+1;
$bulan   = $arrbi[1];
$periode1= $tahun."-01";
$periode2= $prd;
$prddpn  = $tahundpn."-".$bulan;

// if($pt==''){exit("warning : Kode PT harus di pilih.");}

$where='';$where2='';$where_spb=$where_kap='';
$wh="";$wh2="";$whB="";$wh_spb="";$wh_bgt=$wh_bgtrp='';
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
	$str = "select * from " . $dbnamerpt . ".organisasi where induk='".$pt."' and tipe='KEBUN' ".$whipkebun."";
	$res = fetchData($str);
	foreach($res as $bar){
		$listkodeorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	}
	$where=" and substr(a.kodeorg,1,4) in ('".implode("','",$listkodeorg)."')";
	$where2=" and substr(a.kodeblok,1,4) in ('".implode("','",$listkodeorg)."')";
	$where_spb=" and a.kodeorg in ('".implode("','",$listkodeorg)."')";
	$whunit=" and substr(a.unit,1,4) in ('".implode("','",$listkodeorg)."')";
	$where_kap=" and kodeunit in ('".implode("','",$listkodeorg)."')";
}else{
	$listkodeorg = [];
	$str = "select * from " . $dbnamerpt . ".organisasi where tipe='KEBUN' ".$whipkebun."";
	$res = fetchData($str);
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
	$whunit=" and substr(a.unit,1,4) ='".$kdorg."'";
}

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
$whereakun = " and (substr(noakun,1,3) in ('611','621','128','126','127') or noakun like '7%')";
$nmakun = makeOption($dbname,'keu_5akun','noakun,namaakun');

$arrakun=array();
$arrkode=array('kap'=>'kap','umm'=>'umm','bbt'=>'bbt','tbm'=>'tbm','pml'=>'pml','pnn'=>'pnn');
$str = "select * from " . $dbname . ".keu_5akun  where 1=1 ".$whereakun."  and noakun not like '715%'"; 
$res = fetchdata($str);
foreach($res as $bar){
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
		if(strlen($bar['noakun'])=='7'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['bbt'][$bar['noakun']] = $bar['noakun'];
		}
	}
	if(substr($bar['noakun'],0,3)=='126'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['tbm'][$bar['noakun']] = $bar['noakun'];
		}
		if(strlen($bar['noakun'])=='7'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['tbm'][$bar['noakun']] = $bar['noakun'];
		}
	}
	if(substr($bar['noakun'],0,3)=='621'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['pml'][$bar['noakun']] = $bar['noakun'];
		}
		if(strlen($bar['noakun'])=='7'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
		}
	}
	if(substr($bar['noakun'],0,3)=='611'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['pnn'][$bar['noakun']] = $bar['noakun'];
		}
		if(strlen($bar['noakun'])=='7'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['pnn'][$bar['noakun']] = $bar['noakun'];
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
$str = "select * from " . $dbname . ".setup_blok_tahunan a  where 1=1 ".$wh2." ".$where." and tahun='".$tahun.$bulan."'"; 
if(count(fetchdata($str))==0){
	$str = "select * from " . $dbname . ".setup_blok a  where 1=1 ".$wh2." ".$where.""; 
}
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['statusblok']=='TM'){
		@$luasreal+=$bar['luasareaproduktif'];
		@$pkkreal+=$bar['jumlahpokok'];
	}
	if($bar['statusblok']=='BBT'){
		@$luasrealbbt+=$bar['luasareaproduktif'];
	}
	if($bar['statusblok']=='TBM' or $bar['statusblok']=='TB'){		
		@$luasrealtbm+=$bar['luasareaproduktif'];
		@$pkkrealtbm+=$bar['jumlahpokok'];
	}
}

#pokok bibitan
$str = "select * from " . $dbname . ".bibitan_mutasi a  where 1=1 ".$where." and tanggal<='".tglakhir($prd."-01")."'"; 
$res = fetchdata($str);
foreach($res as $bar){
	$pkkrealbbt+=$bar['jumlah'];
}

#ambil luas bgt
$str = "select sum(hathnini) as hathnini, statusblok, sum(pokokthnini) as pokokthnini from " . $dbname . ".bgt_blok a  where 1=1 ".$whB." ".$where2." and tahunbudget='".$tahun."' group by statusblok"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['statusblok']=='TM'){		
		$luasbudget=$bar['hathnini'];
		$pkkbudget=$bar['pokokthnini'];
	}
	if($bar['statusblok']=='BBT'){		
		$luasbudgetbbt=$bar['hathnini'];
		$pkkbudgetbbt=$bar['pokokthnini'];
	}
	if($bar['statusblok']=='TBM' or $bar['statusblok']=='TB'){		
		$luasbudgettbm=$bar['hathnini'];
		$pkkbudgettbm=$bar['pokokthnini'];
	}
}

#ambil luas bgt depan
$str = "select sum(hathnini) as hathnini, statusblok, sum(pokokthnini) as pokokthnini from " . $dbname . ".bgt_blok a  where 1=1 ".$whB." ".$where2." and tahunbudget='".$tahundpn."'  group by statusblok"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['statusblok']=='TM'){		
		$luasbudgetdpn = $bar['hathnini'];
		$pkkbudgetdpn = $bar['pokokthnini'];
	}
	if($bar['statusblok']=='BBT'){		
		$luasbudgetbbtdpn = $bar['hathnini'];
		$pkkbudgetbbtdpn = $bar['pokokthnini'];
	}
	if($bar['statusblok']=='TBM' or $bar['statusblok']=='TB'){		
		$luasbudgettbmdpn=$bar['hathnini'];
		$pkkbudgettbmdpn=$bar['pokokthnini'];
	}
}


#ambil prd real
$str = "select sum(kgwb) as kgwb, substr(tanggal,1,7) as periode from " . $dbname . ".kebun_spbdt b
left join " . $dbname . ".kebun_spbht a on a.nospb=b.nospb
where 1=1 ".$wh_spb." ".$where_spb." and substr(a.tanggal,1,7) between '".$periode1."' and  '".$periode2."' group by periode"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['periode']==$periode2){
		@$prdrealbi += $bar['kgwb'];
	}
	@$prdrealsdbi += $bar['kgwb'];
}

#ambil prd bgt
$e="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="kg".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";

$str=" select kodeunit,divisi,thntnm,kodeblok,".$e." as sdbi,kg".$bulan." as bi,kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." ".$whB." ".$wh_bgt." and tahunbudget = '".$tahun."' ".$wh_bgt."";
$res = fetchdata($str);
foreach($res as $bar){
	@$prdbgtbi += $bar['bi'];
	@$prdbgtsdbi += $bar['sdbi'];
	@$prdbgtthn += $bar['kgsetahun'];
}

#ambil prd bgt depan
$e="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="kg".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";

$str=" select kodeunit,divisi,thntnm,kodeblok,".$e." as sdbi,kg".$bulan." as bi,kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." ".$whB." ".$wh_bgt." and tahunbudget = '".$tahundpn."' ".$wh_bgt."";
$res = fetchdata($str);
foreach($res as $bar){
	@$prdbgtbidpn += $bar['bi'];
	@$prdbgtsdbidpn += $bar['sdbi'];
	@$prdbgtthndpn += $bar['kgsetahun'];
}

#khusus kegiatan tanaman
$str = "select sum(jumlah) as jumlah,substr(noakun,1,5) as jobgroup, 
substr(kodeblok,1,6) as divisi,noakun,periode,kodeorg  
from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,3) in ('611','621','128','126') ".$wh." ".$where." and 
periode between '".$periode1."' and  '".$periode2."' 
group by noakun, periode"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['periode']==$periode2){
		#jobgroup => akun 5, jobcode => akun
		@$realbi[substr($bar['noakun'],0,5)] += $bar['jumlah'];
		@$cdrealbi[$bar['noakun']] += $bar['jumlah'];
	}
	@$realsdbi[substr($bar['noakun'],0,5)] += $bar['jumlah'];
	@$cdrealsdbi[$bar['noakun']] += $bar['jumlah'];
	
	
	#noakun
	if(substr($bar['noakun'],0,3)=='128'){
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$listakun['bbt'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$cdarrakun['bbt'][$bar['noakun']] = $bar['noakun'];
	}
	if(substr($bar['noakun'],0,3)=='126'){
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$listakun['tbm'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$cdarrakun['tbm'][$bar['noakun']] = $bar['noakun'];
	}
	if(substr($bar['noakun'],0,3)=='621'){
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$listakun['pml'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
	}
	if(substr($bar['noakun'],0,3)=='611'){
		$listakun['pnn'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$cdarrakun['pnn'][$bar['noakun']] = $bar['noakun'];
	}
	/* if(substr($bar['noakun'],0,1)=='7'){
		$listakun['umm'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);		
		$cdarrakun['umm'][$bar['noakun']] = $bar['noakun'];
	} */
	$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
	$arrakun7[$bar['noakun']] = $bar['noakun'];
	#end akun
}

#khusus akun umum (Jangan banyak tanya kenapa dipisah !, kalau mau tau juga itu wherenya beda.)
$str = "select kodeasset, sum(jumlah) as jumlah,noakun,periode,kodeorg  
from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,1) in ('7') ".$where." and 
periode between '".$periode1."' and  '".$periode2."' 
group by noakun,periode,kodeasset"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['periode']==$periode2){
		$realbi[substr($bar['noakun'],0,5)] += $bar['jumlah']; #job group
		$cdrealbi[$bar['noakun']] += $bar['jumlah']; #job code
	}
	@$realsdbi[substr($bar['noakun'],0,5)] += $bar['jumlah'];
	@$cdrealsdbi[$bar['noakun']] += $bar['jumlah'];
	
	#noakun
	if(substr($bar['noakun'],0,1)=='7'){
		$listakun['umm'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);		
		$cdarrakun['umm'][$bar['noakun']] = $bar['noakun'];
	}
	$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
	$arrakun7[$bar['noakun']] = $bar['noakun'];
	#end akun
}

#realisasi kapital
$str = "select kodeasset, sum(jumlah) as jumlah,noakun,periode,kodeorg  
from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,3) in ('127') ".$where." and 
periode between '".$periode1."' and  '".$periode2."' and kodeasset!=''
group by noakun,periode,kodeasset"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['periode']==$periode2){
		$realbi[substr($bar['noakun'],0,5)] += $bar['jumlah']; #job group
		$cdrealbi[$bar['noakun']] += $bar['jumlah']; #job code
	}
	@$realsdbi[substr($bar['noakun'],0,5)] += $bar['jumlah'];
	@$cdrealsdbi[$bar['noakun']] += $bar['jumlah'];
	
	#noakun
	if(substr($bar['noakun'],0,3)=='127'){
		$listakun['kap'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);		
		$cdarrakun['kap'][$bar['noakun']] = $bar['noakun'];
	}
	$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
	$arrakun7[$bar['noakun']] = $bar['noakun'];
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
$str=" select substr(noakun,1,5) as jobgroup, tipebudget, kodebudget, noakun, sum(".$e.") as sdbi,sum(rp".$bulan.") as bi,sum(rupiah) as rupiah, tahunbudget, kodeorg, sum(".$s.") as sdbivol, sum(fis".$bulan.") as bivol, sum(jumlah) as jumlah, satuanj, sum(volume) as volume, sum(".$t.") as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whereakun." group by substr(noakun,1,5), noakun";
$res = fetchdata($str);
foreach($res as $bar){
	@$bgtbi[$bar['jobgroup']] += $bar['bi'];
	@$bgtsdbi[$bar['jobgroup']] += $bar['sdbi'];
	@$bgtthn[$bar['jobgroup']] += $bar['rupiah'];
	
	@$cdbgtbi[$bar['noakun']] += $bar['bi'];
	@$cdbgtsdbi[$bar['noakun']] += $bar['sdbi'];
	@$cdbgtthn[$bar['noakun']] += $bar['rupiah'];
	
	if(substr($bar['noakun'],0,3)=='128'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['bbt'][$bar['noakun']] = $bar['noakun'];
		}
		if(strlen($bar['noakun'])=='7'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['bbt'][$bar['noakun']] = $bar['noakun'];
		}
	}
	if(substr($bar['noakun'],0,3)=='126'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['tbm'][$bar['noakun']] = $bar['noakun'];
		}
		if(strlen($bar['noakun'])=='7'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['tbm'][$bar['noakun']] = $bar['noakun'];
		}
	}
	if(substr($bar['noakun'],0,3)=='621'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['pml'][$bar['noakun']] = $bar['noakun'];
		}
		if(strlen($bar['noakun'])=='7'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
		}
	}
	if(substr($bar['noakun'],0,3)=='611'){
		if(strlen($bar['noakun'])=='5'){
			$arrakun[$bar['noakun']] = $bar['noakun'];
			$listakun['pnn'][$bar['noakun']] = $bar['noakun'];
		}
		if(strlen($bar['noakun'])=='7'){
			$arrakun7[$bar['noakun']] = $bar['noakun'];
			$cdarrakun['pnn'][$bar['noakun']] = $bar['noakun'];
		}
	}
}

#ini khusus budget kebun depan
$str=" select substr(noakun,1,5) as jobgroup, tipebudget, kodebudget, noakun, sum(".$e.") as sdbi,sum(rp".$bulan.") as bi,sum(rupiah) as rupiah, tahunbudget, kodeorg, sum(".$s.") as sdbivol, sum(fis".$bulan.") as bivol, sum(jumlah) as jumlah, satuanj, sum(volume) as volume, sum(".$t.") as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahundpn."' ".$whereakun." group by substr(noakun,1,5), noakun";
$res = fetchdata($str);
foreach($res as $bar){
	@$bgtbidpn[$bar['jobgroup']] += $bar['bi'];
	@$bgtsdbidpn[$bar['jobgroup']] += $bar['sdbi'];
	@$bgtthndpn[$bar['jobgroup']] += $bar['rupiah'];
	
	@$cdbgtbidpn[$bar['noakun']] += $bar['bi'];
	@$cdbgtsdbidpn[$bar['noakun']] += $bar['sdbi'];
	@$cdbgtthndpn[$bar['noakun']] += $bar['rupiah'];
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

#ini khusus budget UMUM depan
$str=" select substr(noakun,1,5) as jobgroup,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." and kodebudget='UMUM' and tahunbudget = '".$tahundpn."' ".$whereakun."";
$res = fetchdata($str);
foreach($res as $bar){
	@$bgtbidpn[$bar['jobgroup']] += $bar['bi'];
	@$bgtsdbidpn[$bar['jobgroup']] += $bar['sdbi'];
	@$bgtthndpn[$bar['jobgroup']] += $bar['rupiah'];
	
	@$cdbgtbidpn[$bar['noakun']] += $bar['bi'];
	@$cdbgtsdbidpn[$bar['noakun']] += $bar['sdbi'];
	@$cdbgtthndpn[$bar['noakun']] += $bar['rupiah'];
	
	
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



$e="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="k".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";
#ambil budget kapital thn ini dan tahun depan
$str = "select bgt_kapital_vw.*, ".$e." as sdbi,k".$bulan." as bi from " . $dbname . ".bgt_kapital_vw  where 1=1 ".$where_kap." and (tahunbudget = '".$tahundpn."' or tahunbudget = '".$tahun."') and pta='BGT'"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['tahunbudget']==$tahun){		
		@$bgtbi[substr($bar['noakun'],0,5)] += $bar['bi'];
		@$bgtsdbi[substr($bar['noakun'],0,5)] += $bar['sdbi'];
		@$bgtthn[substr($bar['noakun'],0,5)] += $bar['harga'];
		
		@$cdbgtbi[$bar['noakun']] += $bar['bi'];
		@$cdbgtsdbi[$bar['noakun']] += $bar['sdbi'];
		@$cdbgtthn[$bar['noakun']] += $bar['harga'];
	}
	if($bar['tahunbudget']==$tahundpn){		
		@$bgtbidpn[substr($bar['noakun'],0,5)] += $bar['bi'];
		@$bgtsdbidpn[substr($bar['noakun'],0,5)] += $bar['sdbi'];
		@$bgtthndpn[substr($bar['noakun'],0,5)] += $bar['harga'];
		
		@$cdbgtbidpn[$bar['noakun']] += $bar['bi'];
		@$cdbgtsdbidpn[$bar['noakun']] += $bar['sdbi'];
		@$cdbgtthndpn[$bar['noakun']] += $bar['harga'];
	}
}

if ($proses == 'excel') {
	// $arrtipe=array("group"=>"Per Job Group","code"=>"Per Job Code");
	// $nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	// if($kdorg!=''){$xkdorg=$nmorg[$kdorg];}else{$xkdorg=$_SESSION['lang']['all'];}
	// if($divisi!=''){$xdivisi=$nmorg[$divisi];}else{$xdivisi=$_SESSION['lang']['all'];}
	// if($tt!=''){$xtt=$tt;}else{$xtt=$_SESSION['lang']['all'];}
	// if($ip!=''){$xip=$ip;}else{$xip=$_SESSION['lang']['all'];}
	
	
	// $tab="<table class=sortable cellspacing=1 width=100% >";
	// $tab.="<tr><td align=center colspan=12>REKAP BUDGET (".$arrtipe[$tipe].")</td>";
	// $tab.="<tr><td align=center colspan=12>" . $_SESSION['lang']['pt'] . " : ".$nmorg[$pt].";&nbsp;";
	// $tab.="" . $_SESSION['lang']['unit'] . " : ".$xkdorg."</td></tr>";
	// $tab.="<tr><td align=center colspan=12>" . $_SESSION['lang']['divisi'] . " : ".$xdivisi.";&nbsp;";
	// $tab.="" . $_SESSION['lang']['tahuntanam'] . " : ".$xtt."</td></tr>";
	// $tab.="<tr><td align=center colspan=12>".$_SESSION['lang']['periode'] . " : ".$prd."</td></tr>";
    $tab.="<table class=sortable cellspacing=1 border=1>";
} else {
	$tab.="<div class='menu'>
			<div id='btninscmnt' class='menu-item'>Insert Comment</div>
			<div id='btnshowcmn' class='menu-item'>Show Comment</div>
			<div id='btnreloadframe' class='menu-item'>Reload Frame</div>
		</div>";
    $tab .= "<table class=sortable cellpadding=5 cellspacing=1 width=100%>";
}

$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan='2'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='2'>".$_SESSION['lang']['kode']."</th>
            <th align=center rowspan='2'>Activity</th>
            <th align=center colspan='2' rowspan=2>".$_SESSION['lang']['tahunanggaran']."<br>".($tahun+1)."</th>
            <th align=center colspan='2' rowspan=2>".$_SESSION['lang']['tahunanggaran']."<br>".$tahun."</th>
            
            <th align=center rowspan='1' colspan='5'>".$_SESSION['lang']['sdbulanini']." (".$prd.")</th>
        </tr>
        <tr>
			<th align=center colspan='2'>".$_SESSION['lang']['realisasi']."</th>  
            <th align=center colspan='2'>".$_SESSION['lang']['budget']."</th>  
            <th align=center>%</th>
        </tr>
    </thead>
 <tbody>";
$tab.="<tr class=rowcontent ".$show." style=background-color:#4abdb7;font-weight:bold;>";
$tab.="<td></td>";
$tab.="<td></td>";
$tab.="<td><i>AREAL STATEMENT DAN PRODUKSI</i></td>";
$tab.="<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
$tab.="</tr>";
$tab.="<tr class=rowcontent style=background-color:#E8DAEF>
	<td></td>  
	<td align=center><i></i></td>  
	<td align=center><i>Status</i></td>  
	<td align=center><i>Ha</i></td>  
	<td align=center><i>Pokok</i></td>
	<td align=center><i>Ha</i></td>  
	<td align=center><i>Pokok</i></td>
	<td align=center><i>Ha</i></td>  
	<td align=center><i>Pokok</i></td>
	<td align=center><i>Ha</i></td>  
	<td align=center><i>Pokok</i></td>
	<td align=center></td>
</tr>";
 
$tab.="<tr class=rowcontent>
	<td align=center>1</td><td align=center>TBM</td>
	<td align=left>".strtoupper($_SESSION['lang']['tbm'])."</td>  
	<td align=center>".@nantozero($luasbudgettbmdpn,2)."</td>  
	<td align=center>".@nantozero($pkkbudgettbmdpn,2)."</td>  
	<td align=center>".@nantozero($luasbudgettbm,2)."</td>  
	<td align=center>".@nantozero($pkkbudgettbm,2)."</td>  
	<td align=center>".@nantozero($luasrealtbm,2)."</td>  
	<td align=center>".@nantozero($pkkrealtbm,2)."</td>  
	<td align=center>".@nantozero($luasbudgettbm,2)."</td>  
	<td align=center>".@nantozero($pkkbudgettbm,2)."</td>  
	<td></td>
</tr>";
$tab.="<tr class=rowcontent>
	<td align=center>2</td><td align=center>TM</td>
	<td align=left>".strtoupper($_SESSION['lang']['tm'])."</td>  
	<td align=center>".@nantozero($luasbudgetdpn,2)."</td>  
	<td align=center>".@nantozero($pkkbudgetdpn,2)."</td>  
	<td align=center>".@nantozero($luasbudget,2)."</td>  
	<td align=center>".@nantozero($pkkbudget,2)."</td>  
	<td align=center>".@nantozero($luasreal,2)."</td>  
	<td align=center>".@nantozero($pkkreal,2)."</td>  
	<td align=center>".@nantozero($luasbudget,2)."</td>  
	<td align=center>".@nantozero($pkkbudget,2)."</td>  
	<td></td>
</tr>";
$tab.="<tr class=rowcontent style=background-color:#D5F5E3;font-style:italic;font-weight:bold;>
	<td align=center></td><td align=center>TNM</td>
	<td align=left>TANAMAN (TBM + TM)</td>  
	<td align=center>".@nantozero($luasbudgettbmdpn+$luasbudgetdpn,2)."</td>  
	<td align=center>".@nantozero($pkkbudgettbmdpn+$pkkbudgetdpn,2)."</td>  
	<td align=center>".@nantozero($luasbudgettbm+$luasbudget,2)."</td>  
	<td align=center>".@nantozero($pkkbudgettbm+$pkkbudget,2)."</td>  
	<td align=center>".@nantozero($luasrealtbm+$luasreal,2)."</td>  
	<td align=center>".@nantozero($pkkrealtbm+$pkkreal,2)."</td>  
	<td align=center>".@nantozero($luasbudgettbm+$luasbudget,2)."</td>  
	<td align=center>".@nantozero($pkkbudgettbm+$pkkbudget,2)."</td>  
	<td></td>
</tr>";
$tab.="<tr class=rowcontent>
	<td align=center>3</td><td align=center>BBT</td>
	<td align=left>PEMBIBITAN</td>  
	<td align=center>".@nantozero($luasbudgetbbtdpn,2)."</td>  
	<td align=center>".@nantozero($pkkbudgetbbtdpn,2)."</td>  
	<td align=center>".@nantozero($luasbudgetbbt,2)."</td>  
	<td align=center>".@nantozero($pkkbudgetbbt,2)."</td>  
	<td align=center>".@nantozero($luasrealbbt,2)."</td>  
	<td align=center>".@nantozero($pkkrealbbt,2)."</td>  
	<td align=center>".@nantozero($luasbudgetbbt,2)."</td>  
	<td align=center>".@nantozero($pkkbudgetbbt,2)."</td>  
	<td></td>
</tr>";

$tab.="<tr class=rowcontent style=background-color:#D5F5E3;font-style:italic;font-weight:bold;>
	<td align=center></td><td align=center>USH</td>
	<td align=left>TOTAL AREAL DIUSAHAKAN (BBT + TBM + TM)</td>  
	<td align=center>".@nantozero($luasbudgettbmdpn+$luasbudgetdpn+$luasbudgetbbtdpn,2)."</td>  
	<td align=center>".@nantozero($pkkbudgettbmdpn+$pkkbudgetdpn+$pkkbudgetbbtdpn,2)."</td>  
	<td align=center>".@nantozero($luasbudgettbm+$luasbudget+$luasbudgetbbt,2)."</td>  
	<td align=center>".@nantozero($pkkbudgettbm+$pkkbudget+$pkkbudgetbbt,2)."</td>  
	<td align=center>".@nantozero($luasrealtbm+$luasreal+$luasrealbbt,2)."</td>  
	<td align=center>".@nantozero($pkkrealtbm+$pkkreal+$pkkrealbbt,2)."</td>  
	<td align=center>".@nantozero($luasbudgettbm+$luasbudget+$luasbudgetbbt,2)."</td>  
	<td align=center>".@nantozero($pkkbudgettbm+$pkkbudget+$pkkbudgetbbt,2)."</td>  
	<td></td>
</tr>";

$tab.="<tr class=rowcontent>
	<td align=center>4</td><td align=center>PRD</td>
	<td align=left>".strtoupper($_SESSION['lang']['produksi'])." (Ton)</td>  
	<td align=center colspan='2'>".@nantozero(($prdbgtthndpn/1000),2)."</td>  
	<td align=center colspan='2'>".@nantozero(($prdbgtthn/1000),2)."</td>  
	<td align=center colspan='2'>".@nantozero(($prdrealsdbi/1000),2)."</td>  
	<td align=center colspan='2'>".@nantozero(($prdbgtsdbi/1000),2)."</td>  
	<td></td>
</tr>
<tr class=rowcontent>
	<td align=center>5</td><td align=center>YLD</td>
	<td align=left>YIELD (Ton /Ha)</td>  
	<td align=center colspan='2'>".@nantozero(($prdbgtthndpn/1000)/$luasbudgetdpn,2)."</td>  
	<td align=center colspan='2'>".@nantozero(($prdbgtthn/1000)/$luasbudget,2)."</td>  
	<td align=center colspan='2'>".@nantozero(($prdrealsdbi/1000)/$luasreal,2)."</td>  
	<td align=center colspan='2'>".@nantozero(($prdbgtsdbi/1000)/$luasbudget,2)."</td>  
	<td></td>
</tr>";

$luasbudgetdpnush= $luasbudgettbmdpn+$luasbudgetdpn+$luasbudgetbbtdpn;
$luasrealush     = $luasrealtbm+$luasreal+$luasrealbbt;
$luasbudgetush   = $luasbudgettbm+$luasbudget+$luasbudgetbbt;
$nmkode=array('kap'=>'Kapital','bbt'=>'Pembibitan','tbm'=>'Pemeliharaan TBM','pml'=>'Pemeliharaan TM','pnn'=>'Panen dan Pengangkutan','umm'=>'Tidak Langsung (Umum)');

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

// echo "<pre>";
// print_r($showcomment);

ksort($arrakun);
ksort($listakun);

#isinya ada 2 jenis
switch ($tipe) {
	case'group':
		foreach($arrkode as $kode){
			$tab.="<tr class=rowcontent ".$show." style=background-color:#4abdb7;font-weight:bold;>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td><i>".strtoupper($nmkode[$kode])."</i></td>";
			$tab.="<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
			$tab.="</tr>";
			if($kode=='bbt'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td></td>  
					<td align=center>Code</td>  
					<td align=center>Activity Group</td>  
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Pkk</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Pkk</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Pkk</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Pkk</i></td>
					<td align=center>%</td>  
				</tr>";			
			}
			if($kode=='tbm'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td></td>  
					<td align=center>Code</td>  
					<td align=center>Activity Group</td>  
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TBM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TBM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TBM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TBM</i></td>
					<td align=center>%</td>  
				</tr>";			
			}
			if($kode=='pml'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td></td>  
					<td align=center>Code</td>  
					<td align=center>Activity Group</td>  
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TM</i></td>
					<td align=center>%</td>  
				</tr>";			
			}
			if($kode=='umm' or $kode=='kap'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td></td>  
					<td align=center>Code</td>  
					<td align=center>Activity Group</td>  
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha (ush)</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha (ush)</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha (ush)</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha (ush)</i></td>
					<td align=center>%</td>  
				</tr>";			
			}
			if($kode=='pnn'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td></td>  
					<td align=center>Code</td>  
					<td align=center>Activity Group</td>  
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Kg</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Kg</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Kg</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Kg</i></td>
					<td align=center></td>  
				</tr>";			
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
					
					#====== thn dpn ======
					if($kode=='pnn'){
						@$rpbgtthnsatdpn=$bgtthndpn[$akun]/$prdbgtthndpn;
					}elseif($kode=='kap' or $kode=='umm'){
						@$rpbgtthnsatdpn=$bgtthndpn[$akun]/$luasbudgetdpnush;
					}elseif($kode=='tbm'){
						@$rpbgtthnsatdpn=$bgtthndpn[$akun]/$luasbudgettbmdpn;
					}elseif($kode=='bbt'){
						@$rpbgtthnsatdpn=$bgtthndpn[$akun]/$pkkbudgetbbtdpn;
					}else{
						@$rpbgtthnsatdpn=$bgtthndpn[$akun]/$luasbudgetdpn;
					}
					
					$detbgtthndpn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prddpn."','".$tipe."','".$akun."','html','thn','budget')";
					
					$tab.="<td align=right ".$detbgtthndpn.">".@nantozero($bgtthndpn[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtthnsatdpn,2)."</td>";
					#===== end thn dpn =====
					
					#====== thn ======
					if($kode=='pnn'){
						@$rpbgtthnsat=$bgtthn[$akun]/$prdbgtthn;
					}elseif($kode=='kap' or $kode=='umm'){
						@$rpbgtthnsat=$bgtthn[$akun]/$luasbudgetush;
					}elseif($kode=='tbm'){
						@$rpbgtthnsat=$bgtthn[$akun]/$luasbudgettbm;
					}elseif($kode=='bbt'){
						@$rpbgtthnsat=$bgtthn[$akun]/$pkkbudgetbbt;
					}else{
						@$rpbgtthnsat=$bgtthn[$akun]/$luasbudget;
					}
					
					$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','thn','budget')";
					
					$tab.="<td align=right ".$detbgtthn.">".@nantozero($bgtthn[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtthnsat,2)."</td>";
					#===== end thn =====
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
					if($kode=='pnn'){
						@$rprealsdbisat=$realsdbi[$akun]/$prdrealsdbi;
						@$rpbgtsdbisat=$bgtsdbi[$akun]/$prdbgtsdbi;
					}elseif($kode=='kap' or $kode=='umm'){
						@$rprealsdbisat=$realsdbi[$akun]/$luasrealush;
						@$rpbgtsdbisat=$bgtsdbi[$akun]/$luasrealush;
					}elseif($kode=='tbm'){
						@$rprealsdbisat=$realsdbi[$akun]/$luasrealtbm;
						@$rpbgtsdbisat=$bgtsdbi[$akun]/$luasrealtbm;
					}elseif($kode=='bbt'){
						@$rprealsdbisat=$realsdbi[$akun]/$pkkrealbbt;
						@$rpbgtsdbisat=$bgtsdbi[$akun]/$pkkrealbbt;
					}else{
						@$rprealsdbisat=$realsdbi[$akun]/$luasreal;
						@$rpbgtsdbisat=$bgtsdbi[$akun]/$luasbudget;
					}
					$tab.="<td align=right>".@nantozero($rprealsdbisat,2)."</td>";
					$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($bgtsdbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtsdbisat,2)."</td>";
					
					$persensdbi=0;
					if($rprealsdbisat>0){	
						@$persensdbi=(($rpbgtsdbisat-$rprealsdbisat)/$rpbgtsdbisat)*100;
					}
					$c="";
					if($persensdbi<0){
						$c=" style=color:red;";
					}
					$tab.="<td align=right ".$c.">".@nantozero($persensdbi,2)."</td>";
					#===== end sdbi =====
					
					$tab.="</tr>";
					
					@$ttlrealbi[$kode]+=$realbi[$akun];
					@$ttlbgtbi[$kode]+=$bgtbi[$akun];
					@$ttlbgtbidpn[$kode]+=$bgtbidpn[$akun];
					@$ttlrealsdbi[$kode]+=$realsdbi[$akun];
					@$ttlbgtsdbi[$kode]+=$bgtsdbi[$akun];
					@$ttlbgtsdbidpn[$kode]+=$bgtsdbidpn[$akun];
					@$ttlbgtthn[$kode]+=$bgtthn[$akun];
					@$ttlbgtthndpn[$kode]+=$bgtthndpn[$akun];
					
					@$trealbi+=$realbi[$akun];
					@$tbgtbi+=$bgtbi[$akun];
					@$tbgtbidpn+=$bgtbidpn[$akun];
					@$trealsdbi+=$realsdbi[$akun];
					@$tbgtsdbi+=$bgtsdbi[$akun];
					@$tbgtsdbidpn+=$bgtsdbidpn[$akun];
					@$tbgtthn+=$bgtthn[$akun];
					@$tbgtthndpn+=$bgtthndpn[$akun];
				}
			}
			
			# sub total
			if($kode=='umm'){
				$detrealbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','real')";
				$detbgtbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','budget')";
				
				$detrealsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','real')";
				$detbgtsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','budget')";
						
				$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','thn','budget')";
				
				$detbgtthndpn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prddpn."','".$tipe."','7','html','thn','budget')";
			}else{
				$detrealbi=$detbgtbi=$detrealsdbi=$detbgtsdbi=$detbgtthn=$detbgtthndpn="";
			}
			$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
			$tab.="<td align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			
			#====== thn dpn ======
			if($kode=='pnn'){
				@$ttlrpbgtthnsatdpn=$ttlbgtthndpn[$kode]/$prdbgtthndpn;
			}elseif($kode=='kap' or $kode=='umm'){
				@$ttlrpbgtthnsatdpn=$ttlbgtthndpn[$kode]/$luasbudgetdpnush;
			}elseif($kode=='tbm'){
				@$ttlrpbgtthnsatdpn=$ttlbgtthndpn[$kode]/$luasbudgettbmdpn;
			}elseif($kode=='bbt'){
				@$ttlrpbgtthnsatdpn=$ttlbgtthndpn[$kode]/$pkkbudgetbbtdpn;
			}else{
				@$ttlrpbgtthnsatdpn=$ttlbgtthndpn[$kode]/$luasbudgetdpn;
			}
			$tab.="<td align=right ".$detbgtthndpn.">".@nantozero($ttlbgtthndpn[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtthnsatdpn,2)."</td>";
			#===== end thn =====
			#====== thn ======
			if($kode=='pnn'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$prdbgtthn;
			}elseif($kode=='kap' or $kode=='umm'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$luasbudgetush;
			}elseif($kode=='tbm'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$luasbudgettbm;
			}elseif($kode=='bbt'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$pkkbudgetbbt;
			}else{
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$luasbudget;
			}
			$tab.="<td align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtthnsat,2)."</td>";
			#===== end thn =====
			#====== sdbi ======
			$tab.="<td align=right ".$detrealsdbi.">".@nantozero($ttlrealsdbi[$kode])."</td>";
			if($kode=='pnn'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$prdrealsdbi;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$prdbgtsdbi;
			}elseif($kode=='kap' or $kode=='umm'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$luasrealush;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$luasbudgetush;
			}elseif($kode=='tbm'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$luasrealtbm;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$luasbudgettbm;
			}elseif($kode=='bbt'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$pkkrealbbt;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$pkkbudgetbbt;
			}else{
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$luasreal;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$luasbudget;
			}
			$tab.="<td align=right>".@nantozero($ttlrprealsdbisat,2)."</td>";
			$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($ttlbgtsdbi[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtsdbisat,2)."</td>";
			
			$ttlpersensdbi=0;
			if($ttlrprealsdbisat>0){						
				@$ttlpersensdbi=(($ttlrpbgtsdbisat-$ttlrprealsdbisat)/$ttlrpbgtsdbisat)*100;
			}
			$c="";
			if($ttlpersensdbi<0){
				$c=" style=color:red;";
			}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersensdbi,2)."</td>";
			#===== end sdbi =====
			$tab.="</tr>";
		}
	#grand total
	$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
	$tab.="<td align=center colspan=3>GRAND TOTAL</td>";
	
	
	#====== thn dpn======
	$tab.="<td align=right>".@nantozero($tbgtthndpn)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtthndpn/@$prdbgtthndpn,2)."</td>";
	#===== end thn dpn =====
	#====== thn ======
	$tab.="<td align=right>".@nantozero($tbgtthn)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtthn/@$prdbgtthn,2)."</td>";
	#===== end thn =====
	#====== sdbi ======
	$tab.="<td align=right>".@nantozero($trealsdbi)."</td>";
	@$rpperprdrealsdbi=$trealsdbi/@$prdrealsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdrealsdbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtsdbi)."</td>";
	@$rpperprdbgtsdbi=$tbgtsdbi/@$prdbgtsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdbgtsdbi,2)."</td>";
	@$gtpersensdbi=(($rpperprdbgtsdbi-$rpperprdrealsdbi)/$rpperprdbgtsdbi)*100;
	$tab.="<td align=right>".@nantozero($gtpersensdbi,2)."</td>";
	#===== end sdbi =====
	
	break;
	
	case'code':
		foreach($arrkode as $kode){
			$tab.="<tr class=rowcontent ".$show." style=background-color:#4abdb7;font-weight:bold;>";
			$tab.="<td></td>";
			$tab.="<td></td>";
			$tab.="<td><i>".strtoupper($nmkode[$kode])."</i></td>";
			$tab.="<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>";
			$tab.="</tr>";
			if($kode=='bbt'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td></td>  
					<td align=center>Code</td>  
					<td align=center>Activity Group</td>  
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Pkk</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Pkk</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Pkk</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Pkk</i></td>
					<td align=center>%</td>  
				</tr>";			
			}
			if($kode=='tbm'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td></td>  
					<td align=center>Code</td>  
					<td align=center>Activity Group</td>  
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TBM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TBM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TBM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TBM</i></td>
					<td align=center>%</td>  
				</tr>";			
			}
			if($kode=='pml'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td></td>  
					<td align=center>Code</td>  
					<td align=center>Activity Group</td>  
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TM</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha TM</i></td>
					<td align=center>%</td>  
				</tr>";			
			}
			if($kode=='umm' or $kode=='kap'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td></td>  
					<td align=center>Code</td>  
					<td align=center>Activity Group</td>  
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha (ush)</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha (ush)</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha (ush)</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Ha (ush)</i></td>
					<td align=center>%</td>  
				</tr>";			
			}
			if($kode=='pnn'){
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
					<td></td>  
					<td align=center>Code</td>  
					<td align=center>Activity Group</td>  
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Kg</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Kg</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Kg</i></td>
					<td align=center>Rupiah</td>  
					<td align=center><i>Rp/Kg</i></td>
					<td align=center></td>  
				</tr>";			
			}
			$no=0;
			foreach($arrakun7 as $akun){
				if(@$cdarrakun[$kode][$akun]!=''){					
					$no++;
					$show="";
					if(substr($akun,0,1)=='7'){
						//$show=" style=display:none";
					}
					$d=substr($akun,0,5);
					if($d!=$n){
						$tab.="<tr class=rowcontent ".$show." style=background-color:#ccfffd;font-weight:bold;>";
						$tab.="<td></td>";
						$tab.="<td><i>".$d."</i></td>";
						$tab.="<td><i>".strtoupper(strtolower(getNamaAkun($d)))."</i></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="<td></td>";
						$tab.="</tr>";
					}
					
					
					$tab.="<tr class=rowcontent ".$show.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$akun."</td>";
					$tab.="<td>".$nmakun[$akun]."</td>";
					
					#====== thn dpn======
					
					if($kode=='pnn'){
						@$rpbgtthnsatdpn=$cdbgtthndpn[$akun]/$prdbgtthndpn;
					}elseif($kode=='kap' or $kode=='umm'){
						@$rpbgtthnsatdpn=$cdbgtthndpn[$akun]/$luasbudgetdpnush;
					}elseif($kode=='tbm'){
						@$rpbgtthnsatdpn=$cdbgtthndpn[$akun]/$luasbudgettbmdpn;
					}elseif($kode=='bbt'){
						@$rpbgtthnsatdpn=$cdbgtthndpn[$akun]/$pkkbudgetbbtdpn;
					}else{
						@$rpbgtthnsatdpn=$cdbgtthndpn[$akun]/$luasbudgetdpn;
					}
							
					$detbgtthndpn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prddpn."','".$tipe."','".$akun."','html','thn','budget')";
					
					$tab.="<td align=right ".$detbgtthndpn.">".@nantozero($cdbgtthndpn[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtthnsatdpn,2)."</td>";
					#===== end thn dpn=====
					#====== thn ======
					
					if($kode=='pnn'){
						@$rpbgtthnsat=$cdbgtthn[$akun]/$prdbgtthn;
					}elseif($kode=='kap' or $kode=='umm'){
						@$rpbgtthnsat=$cdbgtthn[$akun]/$luasbudgetush;
					}elseif($kode=='tbm'){
						@$rpbgtthnsat=$cdbgtthn[$akun]/$luasbudgettbm;
					}elseif($kode=='bbt'){
						@$rpbgtthnsat=$cdbgtthn[$akun]/$pkkbudgetbbt;
					}else{
						@$rpbgtthnsat=$cdbgtthn[$akun]/$luasbudget;
					}
							
					$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','".$akun."','html','thn','budget')";
					
					$tab.="<td align=right ".$detbgtthn.">".@nantozero($cdbgtthn[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtthnsat,2)."</td>";
					#===== end thn =====
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
					if($kode=='pnn'){
						@$rprealsdbisat=$cdrealsdbi[$akun]/$prdrealsdbi;
						@$rpbgtsdbisat=$cdbgtsdbi[$akun]/$prdbgtsdbi;
					}elseif($kode=='kap' or $kode=='umm'){
						@$rprealsdbisat=$cdrealsdbi[$akun]/$luasrealush;
						@$rpbgtsdbisat=$cdbgtsdbi[$akun]/$luasrealush;
					}elseif($kode=='tbm'){
						@$rprealsdbisat=$cdrealsdbi[$akun]/$luasrealtbm;
						@$rpbgtsdbisat=$cdbgtsdbi[$akun]/$luasrealtbm;
					}elseif($kode=='bbt'){
						@$rprealsdbisat=$cdrealsdbi[$akun]/$pkkrealbbt;
						@$rpbgtsdbisat=$cdbgtsdbi[$akun]/$pkkrealbbt;
					}else{
						@$rprealsdbisat=$cdrealsdbi[$akun]/$luasreal;
						@$rpbgtsdbisat=$cdbgtsdbi[$akun]/$luasbudget;
					}
					$tab.="<td align=right>".@nantozero($rprealsdbisat,2)."</td>";
					$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($cdbgtsdbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtsdbisat,2)."</td>";
					
					$persensdbi=0;
					if($rprealsdbisat>0){					
						@$persensdbi=(($rpbgtsdbisat-$rprealsdbisat)/$rpbgtsdbisat)*100;
					}	
					$c="";
					if($persensdbi<0){
						$c=" style=color:red;";
					}
					$tab.="<td align=right ".$c.">".@nantozero($persensdbi,2)."</td>";
					#===== end sdbi =====
					
					$tab.="</tr>";
					
					@$ttlrealbi[$kode]+=$cdrealbi[$akun];
					@$ttlbgtbi[$kode]+=$cdbgtbi[$akun];
					@$ttlbgtbidpn[$kode]+=$cdbgtbidpn[$akun];
					@$ttlrealsdbi[$kode]+=$cdrealsdbi[$akun];
					@$ttlbgtsdbi[$kode]+=$cdbgtsdbi[$akun];
					@$ttlbgtsdbidpn[$kode]+=$cdbgtsdbidpn[$akun];
					@$ttlbgtthn[$kode]+=$cdbgtthn[$akun];
					@$ttlbgtthndpn[$kode]+=$cdbgtthndpn[$akun];
					
					@$trealbi+=$cdrealbi[$akun];
					@$tbgtbi+=$cdbgtbi[$akun];
					@$tbgtbidpn+=$cdbgtbidpn[$akun];
					@$trealsdbi+=$cdrealsdbi[$akun];
					@$tbgtsdbi+=$cdbgtsdbi[$akun];
					@$tbgtsdbidpn+=$cdbgtsdbidpn[$akun];
					@$tbgtthn+=$cdbgtthn[$akun];
					@$tbgtthndpn+=$cdbgtthndpn[$akun];
				}
				
				$n=$d;
			}
			
			# sub total
			if($kode=='umm'){
				$detrealbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','real')";
				$detbgtbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','bi','budget')";
				
				$detrealsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','real')";
				$detbgtsdbi="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','sdbi','budget')";
						
				$detbgtthn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prd."','".$tipe."','7','html','thn','budget')";
				
				$detbgtthndpn="style=cursor:pointer;color:blue; onclick=getdetail('".$pt."','".$kdorg."','".$tt."','".$ip."','".$divisi."','".$prddpn."','".$tipe."','7','html','thn','budget')";
			}else{
				$detrealbi=$detbgtbi=$detrealsdbi=$detbgtsdbi=$detbgtthn=$detbgtthndpn="";
			}
			$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
			$tab.="<td align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			
			#====== thn dpn======
			if($kode=='pnn'){
				@$ttlrpbgtthnsatdpn=$ttlbgtthndpn[$kode]/$prdbgtthndpn;
			}elseif($kode=='kap' or $kode=='umm'){
				@$ttlrpbgtthnsatdpn=$ttlbgtthndpn[$kode]/$luasbudgetdpnush;
			}elseif($kode=='tbm'){
				@$ttlrpbgtthnsatdpn=$ttlbgtthndpn[$kode]/$luasbudgettbmdpn;
			}elseif($kode=='bbt'){
				@$ttlrpbgtthnsatdpn=$ttlbgtthndpn[$kode]/$pkkbudgetbbtdpn;
			}else{
				@$ttlrpbgtthnsatdpn=$ttlbgtthndpn[$kode]/$luasbudgetdpn;
			}
			$tab.="<td align=right ".$detbgtthndpn.">".@nantozero($ttlbgtthndpn[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtthnsatdpn,2)."</td>";
			#===== end thn dpn=====
			#====== thn ======
			if($kode=='pnn'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$prdbgtthn;
			}elseif($kode=='kap' or $kode=='umm'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$luasbudgetush;
			}elseif($kode=='tbm'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$luasbudgettbm;
			}elseif($kode=='bbt'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$pkkbudgetbbt;
			}else{
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$luasbudget;
			}
			$tab.="<td align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtthnsat,2)."</td>";
			#===== end thn =====
			#====== sdbi ======
			$tab.="<td align=right ".$detrealsdbi.">".@nantozero($ttlrealsdbi[$kode])."</td>";
			if($kode=='pnn'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$prdrealsdbi;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$prdbgtsdbi;
			}elseif($kode=='kap' or $kode=='umm'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$luasrealush;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$luasbudgetush;
			}elseif($kode=='tbm'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$luasrealtbm;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$luasbudgettbm;
			}elseif($kode=='bbt'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$pkkrealbbt;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$pkkbudgetbbt;
			}else{
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$luasreal;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$luasbudget;
			}
			
			$tab.="<td align=right>".@nantozero($ttlrprealsdbisat,2)."</td>";
			$tab.="<td align=right ".$detbgtsdbi.">".@nantozero($ttlbgtsdbi[$kode])."</td>";
			$tab.="<td align=right>".@nantozero($ttlrpbgtsdbisat,2)."</td>";
			
			$ttlpersensdbi=0;
			if($ttlrprealsdbisat>0){	
				@$ttlpersensdbi=(($ttlrpbgtsdbisat-$ttlrprealsdbisat)/$ttlrpbgtsdbisat)*100;
			}
			$c="";
			if($ttlpersensdbi<0){
				$c=" style=color:red;";
			}
			$tab.="<td align=right ".$c.">".@nantozero($ttlpersensdbi,2)."</td>";
			#===== end sdbi =====
			$tab.="</tr>";
		}
		
	
	#grand total
	$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
	$tab.="<td align=center colspan=3>GRAND TOTAL</td>";
	#====== thn dpn======
	$tab.="<td align=right>".@nantozero($tbgtthndpn)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtthndpn/@$prdbgtthndpn,2)."</td>";
	#===== end thn dpn=====
	#====== thn ======
	$tab.="<td align=right>".@nantozero($tbgtthn)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtthn/@$prdbgtthn,2)."</td>";
	#===== end thn =====
	#====== sdbi ======
	$tab.="<td align=right>".@nantozero($trealsdbi)."</td>";
	@$rpperprdrealsdbi=$trealsdbi/@$prdrealsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdrealsdbi,2)."</td>";
	$tab.="<td align=right>".@nantozero($tbgtsdbi)."</td>";
	@$rpperprdbgtsdbi=$tbgtsdbi/@$prdbgtsdbi;
	$tab.="<td align=right>".@nantozero($rpperprdbgtsdbi,2)."</td>";
	@$gtpersensdbi=(($rpperprdbgtsdbi-$rpperprdrealsdbi)/$rpperprdbgtsdbi)*100;
	$tab.="<td align=right>".@nantozero($gtpersensdbi,2)."</td>";
	$tab.="</tr>";
	#===== end sdbi =====
	
	break;
}

$arrkode=array('ummtbm'=>'7','ummtm'=>'7','pml'=>'621','pnn'=>'611');
$tab.="<tr class=rowcontent>";
$tab.="<td align=left colspan=12>&nbsp;</td>";
$tab.="</tr>";

$tab.="<tr class=rowcontent style=background-color:#4abdb7;font-weight:bold;font-style:italic;>";
$tab.="<td align=left colspan=2></td>";
$tab.="<td align=left colspan=10>REKAPITULASI BIAYA PRODUKSI</td>";
$tab.="</tr>";

$tab.="<tr class=rowcontent style=background-color:#E8DAEF;font-style:italic;>
	<td></td>  
	<td align=center>Code</td>  
	<td align=center>Activity Group</td>  
	<td align=center>Rupiah</td>  
	<td align=center><i>Rp/Kg</i></td>
	<td align=center>Rupiah</td>  
	<td align=center><i>Rp/Kg</i></td>
	<td align=center>Rupiah</td>  
	<td align=center><i>Rp/Kg</i></td>
	<td align=center>Rupiah</td>  
	<td align=center><i>Rp/Kg</i></td>
	<td align=center></td>  
</tr>";	

$ttlpersensdbi=0;
@$ttlpersensdbi=(($prdbgtsdbi-$prdrealsdbi)/$prdbgtsdbi)*100;
$c="";
if($ttlpersensdbi<0){
	$c=" style=color:red;";
}
$tab.="<tr class=rowcontent style=background-color:#D5F5E3;font-weight:bold;>
	<td align=center></td><td></td>
	<td align=left>".strtoupper($_SESSION['lang']['produksi'])." TON (000)</td>  
	<td></td>
	<td align=right>".@nantozero(($prdbgtthndpn/1000),2)."</td>  
	<td></td>
	<td align=right>".@nantozero(($prdbgtthn/1000),2)."</td>  
	<td></td>
	<td align=right>".@nantozero(($prdrealsdbi/1000),2)."</td>  
	<td></td>
	<td align=right>".@nantozero(($prdbgtsdbi/1000),2)."</td>  
	<td align=right ".$c.">".@nantozero($ttlpersensdbi,2)."</td>
</tr>";


	
$no=0; $trealsdbi=$tbgtthndpn=$tbgtthn=$tbgtsdbi=0;
foreach($arrkode as $kode => $value){
	$no++;
	if($kode=='ummtbm'){
		$tab.="<tr class=rowcontent style=font-style:italic;>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=center></td>";
		$kode='umm';
		$tab.="<td>".getNamaAkun($value)." PORSI TBM</td>";
		$tab.="<td align=right>".@nantozero($ttlbgtthndpn[$kode]*($luasbudgettbmdpn/($luasbudgettbmdpn+$luasbudgetdpn)))."</td>";
		$tab.="<td></td>";
		$tab.="<td align=right>".@nantozero($ttlbgtthn[$kode]*($luasbudgettbm/($luasbudgettbm+$luasbudget)))."</td>";
		$tab.="<td></td>";
		$tab.="<td align=right>".@nantozero($ttlrealsdbi[$kode]*($luasrealtbm/($luasrealtbm+$luasreal)))."</td>";
		$tab.="<td></td>";
		$tab.="<td align=right>".@nantozero($ttlbgtsdbi[$kode]*($luasbudgettbm/($luasbudgettbm+$luasbudget)))."</td>";
		$tab.="<td></td>";
		$tab.="<td></td>";
	}elseif($kode=='ummtm'){
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=center></td>";
		$kode='umm';
		$tab.="<td>".getNamaAkun($value)." PORSI TM</td>";
		$tab.="<td align=right>".@nantozero($ttlbgtthndpn[$kode]*($luasbudgetdpn/($luasbudgettbmdpn+$luasbudgetdpn)))."</td>";
		$tab.="<td align=right>".@nantozero(($ttlbgtthndpn[$kode]*($luasbudgetdpn/($luasbudgettbmdpn+$luasbudgetdpn)))/$prdbgtthndpn,2)."</td>";
		
		$tab.="<td align=right>".@nantozero($ttlbgtthn[$kode]*($luasbudget/($luasbudgettbm+$luasbudget)))."</td>";
		$tab.="<td align=right>".@nantozero(($ttlbgtthn[$kode]*($luasbudget/($luasbudgettbm+$luasbudget)))/$prdbgtthn,2)."</td>";
		
		$tab.="<td align=right>".@nantozero($ttlrealsdbi[$kode]*($luasreal/($luasrealtbm+$luasreal)))."</td>";
		$tab.="<td align=right>".@nantozero(($ttlrealsdbi[$kode]*($luasreal/($luasrealtbm+$luasreal)))/$prdrealsdbi,2)."</td>";
		
		$tab.="<td align=right>".@nantozero($ttlbgtsdbi[$kode]*($luasbudget/($luasbudgettbm+$luasbudget)))."</td>";
		$tab.="<td align=right>".@nantozero(($ttlbgtsdbi[$kode]*($luasbudget/($luasbudgettbm+$luasbudget)))/$prdbgtsdbi,2)."</td>";
		
		
		@$ttlrprealsdbisat=($ttlrealsdbi[$kode]*($luasreal/($luasrealtbm+$luasreal)))/$prdrealsdbi;
		@$ttlrpbgtsdbisat=($ttlbgtsdbi[$kode]*($luasbudget/($luasbudgettbm+$luasbudget)))/$prdbgtsdbi;
		
		$ttlpersensdbi=0;
		if($ttlrprealsdbisat>0){	
			@$ttlpersensdbi=(($ttlrpbgtsdbisat-$ttlrprealsdbisat)/$ttlrpbgtsdbisat)*100;
		}
		$c="";
		if($ttlpersensdbi<0){
			$c=" style=color:red;";
		}
		$tab.="<td align=right ".$c.">".@nantozero($ttlpersensdbi,2)."</td>";
		
		@$tbgtthndpn+=$ttlbgtthndpn[$kode]*($luasbudgetdpn/($luasbudgettbmdpn+$luasbudgetdpn));
		$tbgtthn+=$ttlbgtthn[$kode]*($luasbudget/($luasbudgettbm+$luasbudget));
		$trealsdbi+=$ttlrealsdbi[$kode]*($luasreal/($luasrealtbm+$luasreal));
		$tbgtsdbi+=$ttlbgtsdbi[$kode]*($luasbudget/($luasbudgettbm+$luasbudget));
		
	}else{
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center>".$no."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td>".getNamaAkun($value)."</td>";
		$tab.="<td align=right>".@nantozero($ttlbgtthndpn[$kode])."</td>";
		$tab.="<td align=right>".@nantozero(($ttlbgtthndpn[$kode])/$prdbgtthndpn,2)."</td>";
		
		$tab.="<td align=right>".@nantozero($ttlbgtthn[$kode])."</td>";
		$tab.="<td align=right>".@nantozero(($ttlbgtthn[$kode])/$prdbgtthn,2)."</td>";
		
		$tab.="<td align=right>".@nantozero($ttlrealsdbi[$kode])."</td>";
		$tab.="<td align=right>".@nantozero(($ttlrealsdbi[$kode])/$prdrealsdbi,2)."</td>";
		
		$tab.="<td align=right>".@nantozero($ttlbgtsdbi[$kode])."</td>";
		$tab.="<td align=right>".@nantozero(($ttlbgtsdbi[$kode])/$prdbgtsdbi,2)."</td>";
		
		@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$prdrealsdbi;
		@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$prdbgtsdbi;
		
		$ttlpersensdbi=0;
		if($ttlrprealsdbisat>0){	
			@$ttlpersensdbi=(($ttlrpbgtsdbisat-$ttlrprealsdbisat)/$ttlrpbgtsdbisat)*100;
		}
		$c="";
		if($ttlpersensdbi<0){
			$c=" style=color:red;";
		}
		$tab.="<td align=right ".$c.">".@nantozero($ttlpersensdbi,2)."</td>";
		
		$tbgtthndpn+=$ttlbgtthndpn[$kode];
		$tbgtthn+=$ttlbgtthn[$kode];
		$trealsdbi+=$ttlrealsdbi[$kode];
		$tbgtsdbi+=$ttlbgtsdbi[$kode];
	}
	$tab.="</tr>";	
} 

$no++;



$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
$tab.="<td align=center colspan=3>GRAND TOTAL (OH TM + TM + PNN)</td>";
#====== thn dpn======
$tab.="<td align=right>".@nantozero($tbgtthndpn)."</td>";
$tab.="<td align=right>".@nantozero($tbgtthndpn/@$prdbgtthndpn,2)."</td>";
#===== end thn dpn=====
#====== thn ======
$tab.="<td align=right>".@nantozero($tbgtthn)."</td>";
$tab.="<td align=right>".@nantozero($tbgtthn/@$prdbgtthn,2)."</td>";
#===== end thn =====
#====== sdbi ======
$tab.="<td align=right>".@nantozero($trealsdbi)."</td>";
@$rpperprdrealsdbi=$trealsdbi/@$prdrealsdbi;
$tab.="<td align=right>".@nantozero($rpperprdrealsdbi,2)."</td>";
$tab.="<td align=right>".@nantozero($tbgtsdbi)."</td>";
@$rpperprdbgtsdbi=$tbgtsdbi/@$prdbgtsdbi;
$tab.="<td align=right>".@nantozero($rpperprdbgtsdbi,2)."</td>";
@$gtpersensdbi=(($rpperprdbgtsdbi-$rpperprdrealsdbi)/$rpperprdbgtsdbi)*100;
$tab.="<td align=right>".@nantozero($gtpersensdbi,2)."</td>";
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