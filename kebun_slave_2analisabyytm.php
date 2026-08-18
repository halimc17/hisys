<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses   = checkPostGet('proses', '');
$kdorg    = checkPostGet('kdorg', '');
$pt       = checkPostGet('pt', '');
$tt       = checkPostGet('tt', '');
$ip       = checkPostGet('ip', '');
$divisi   = checkPostGet('divisi', '');
$prd      = checkPostGet('prd', '');
$tipe     = checkPostGet('tipe', '');
$depre    = checkPostGet('depre', '');

$arrbi    = explode('-',$prd);
$tahun    = $arrbi[0];
$bulan    = $arrbi[1];
$periode1 = $tahun."-01";
$periode2 = $prd;
// if($pt==''){exit("warning : Kode PT harus di pilih.");}

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$where='';$where2='';$where_spb='';

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
}

if($kdorg!=''){
	$where=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$where2=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
	$where_spb=" and a.kodeorg ='".$kdorg."'";
	$whunit=" and substr(a.unit,1,4) ='".$kdorg."'";
	$wh2.=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
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
$amortisasi = '715';
if($depre=='0'){
	$whereakun = " and (substr(noakun,1,3) in ('611','621') or noakun like '7%') and substr(noakun,1,3)!='".$amortisasi."'";
}else{
	$whereakun = " and (substr(noakun,1,3) in ('611','621') or noakun like '7%')";
}



$nmakun = makeOption($dbname,'keu_5akun','noakun,namaakun');

$arrakun=array();
$arrkode=array('pml'=>'pml','pnn'=>'pnn','umm'=>'umm');
$str = "select * from " . $dbname . ".keu_5akun  where 1=1 ".$whereakun." and namaakun not like '%NON AKTIF%'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
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

if($tipe=='keg'){
	$arrakun7=array();
	$cdarrakun=array();
	$str = "select * from " . $dbname . ".setup_kegiatan  where 1=1 ".$whereakun." and namakegiatan not like '%NON AKTIF%' order by kodekegiatan";
	$res = fetchdata($str);
	foreach($res as $bar){
		if(substr($bar['noakun'],0,3)=='621'){
			$arrakun7[$bar['kodekegiatan']] = $bar['kodekegiatan'];
			$cdarrakun['pml'][$bar['kodekegiatan']] = $bar['kodekegiatan'];
		}
		if(substr($bar['noakun'],0,3)=='611'){
			$arrakun7[$bar['kodekegiatan']] = $bar['kodekegiatan'];
			$cdarrakun['pnn'][$bar['kodekegiatan']] = $bar['kodekegiatan'];
		}
		// if(substr($bar['noakun'],0,1)=='7'){
			// $arrakun7[$bar['noakun']] = $bar['kodekegiatan'];
			// $cdarrakun['umm'][$bar['noakun']] = $bar['kodekegiatan'];
		// }
	}

	$str = "select * from " . $dbname . ".keu_5akun  where 1=1 ".$whereakun." and namaakun not like '%NON AKTIF%'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		if(substr($bar['noakun'],0,1)=='7'){
			if(strlen($bar['noakun'])=='7'){
				$arrakun7[$bar['noakun']] = $bar['noakun'];
				$cdarrakun['umm'][$bar['noakun']] = $bar['noakun'];
			}
		}
	}
}

#ambil luas realisasi
$str = "select * from " . $dbname . ".setup_blok_tahunan a  where 1=1 ".$wh2." ".$where." and tahun='".$tahun.$bulan."'  and statusblok='TM'";
if(count(fetchdata($str))==0){
	$str = "select * from " . $dbname . ".setup_blok a  where 1=1 ".$wh2." ".$where."  and statusblok='TM'";
}
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$luasreal+=$bar['luasareaproduktif'];
}

#ambil luas bgt
$str = "select sum(hathnini) as hathnini from " . $dbname . ".bgt_blok a  where 1=1 ".$whB." ".$where2." and tahunbudget='".$tahun."'  and statusblok='TM'";
$bar = fetchdata($str);
$luasbudget=$bar[0]['hathnini'];

#ambil prd real

$str = "select sum(kgwb) as kgwb, substr(tanggal,1,7) as periode from " . $dbname . ".kebun_spbdt b
left join " . $dbname . ".kebun_spbht a on a.nospb=b.nospb
where 1=1 ".$wh_spb." ".$where_spb." and substr(a.tanggal,1,7) between '".$periode1."' and  '".$periode2."' group by periode";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
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
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$prdbgtbi += $bar['bi'];
	@$prdbgtsdbi += $bar['sdbi'];
	@$prdbgtthn += $bar['kgsetahun'];
}

#khusus kegiatan tanaman
$str = "select kodekegiatan, sum(jumlah) as jumlah,substr(noakun,1,5) as jobgroup,
substr(kodeblok,1,6) as divisi,noakun,periode,kodeorg
from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,3) in ('611','621') ".$wh." ".$where." and
periode between '".$periode1."' and  '".$periode2."'
group by noakun, periode, kodekegiatan";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if($bar['periode']==$periode2){
		#jobgroup => akun 5, jobcode => akun
		@$realbi[substr($bar['noakun'],0,5)] += $bar['jumlah'];
		if($tipe=='keg'){
			if($bar['kodekegiatan']!=''){
				@$cdrealbi[$bar['kodekegiatan']] += $bar['jumlah'];
			}else{
				@$cdrealbi[$bar['noakun']] += $bar['jumlah'];
			}
		}else{
			@$cdrealbi[$bar['noakun']] += $bar['jumlah'];
		}
	}
	@$realsdbi[substr($bar['noakun'],0,5)] += $bar['jumlah'];
	if($tipe=='keg'){
		if($bar['kodekegiatan']!=''){
			@$cdrealsdbi[$bar['kodekegiatan']] += $bar['jumlah'];
		}else{
			@$cdrealsdbi[$bar['noakun']] += $bar['jumlah'];
		}
	}else{
		@$cdrealsdbi[$bar['noakun']] += $bar['jumlah'];
	}


	#noakun
	if(substr($bar['noakun'],0,3)=='621'){
		$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$listakun['pml'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		if($tipe=='keg'){
			if($bar['kodekegiatan']!=''){
				$cdarrakun['pml'][$bar['kodekegiatan']] = $bar['kodekegiatan'];
			}else{
				$cdarrakun['pml'][$bar['noakun']] = $bar['kodekegiatan'];
			}
		}else{
			$cdarrakun['pml'][$bar['noakun']] = $bar['noakun'];
		}
	}
	if(substr($bar['noakun'],0,3)=='611'){
		$listakun['pnn'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		if($tipe=='keg'){
			if($bar['kodekegiatan']!=''){
				$cdarrakun['pnn'][$bar['kodekegiatan']] = $bar['kodekegiatan'];
			}else{
				$cdarrakun['pnn'][$bar['noakun']] = $bar['kodekegiatan'];
			}
		}else{
			$cdarrakun['pnn'][$bar['noakun']] = $bar['noakun'];
		}
	}
	/* if(substr($bar['noakun'],0,1)=='7'){
		$listakun['umm'][substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
		$cdarrakun['umm'][$bar['noakun']] = $bar['noakun'];
	} */
	if($tipe=='keg'){
		if($bar['kodekegiatan']!=''){
			$arrakun7[$bar['kodekegiatan']] = $bar['kodekegiatan'];
		}else{
			$arrakun7[$bar['noakun']] = $bar['kodekegiatan'];
		}
	}else{
		$arrakun7[$bar['noakun']] = $bar['noakun'];
	}
	$arrakun[substr($bar['noakun'],0,5)] = substr($bar['noakun'],0,5);
	#end akun
}

if($depre=='0'){
	$whereakunumm = " and substr(noakun,1,1) in ('7') and substr(noakun,1,3)!='".$amortisasi."'";
}else{
	$whereakunumm = " and substr(noakun,1,1) in ('7')";
}

#khusus akun umum (Jangan banyak tanya kenapa dipisah !, kalau mau tau juga itu wherenya beda.)
$str = "select sum(jumlah) as jumlah,noakun,periode,kodeorg
from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 ".$whereakunumm." ".$where." and
periode between '".$periode1."' and  '".$periode2."'
group by noakun,periode";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
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


$e="("; $s="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
}
$e.=")"; $s.=")";

$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";

#ini khusus budget kebun
$str=" select kegiatan, substr(noakun,1,5) as jobgroup, tipebudget, kodebudget, noakun, sum(".$e.") as sdbi,sum(rp".$bulan.") as bi,sum(rupiah) as rupiah, tahunbudget, kodeorg, sum(".$s.") as sdbivol, sum(fis".$bulan.") as bivol, sum(jumlah) as jumlah, satuanj, sum(volume) as volume, sum(".$t.") as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whereakun." group by substr(noakun,1,5), noakun,kegiatan";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$bgtbi[$bar['jobgroup']] += $bar['bi'];
	@$bgtsdbi[$bar['jobgroup']] += $bar['sdbi'];
	@$bgtthn[$bar['jobgroup']] += $bar['rupiah'];

	if($tipe=='keg'){
		@$cdbgtbi[$bar['kegiatan']] += $bar['bi'];
		@$cdbgtsdbi[$bar['kegiatan']] += $bar['sdbi'];
		@$cdbgtthn[$bar['kegiatan']] += $bar['rupiah'];
	}else{
		@$cdbgtbi[$bar['noakun']] += $bar['bi'];
		@$cdbgtsdbi[$bar['noakun']] += $bar['sdbi'];
		@$cdbgtthn[$bar['noakun']] += $bar['rupiah'];
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

$subTotalKeg = array();
$subTotalAkun = array();
$gTotalKeg = array();
$gTotalAkun = array();
// Ambil Nilai Labor, Material, dan Transport dari keu_jurnaldt_vw untuk AKUN 611
$strJrn = "select periode,kodejurnal,noakun,kodekegiatan,jumlah from " . $dbname . ".keu_jurnaldt_vw a where 1=1 and substr(noakun,1,3) in ('611') ".$wh." ".$where." and 
periode between '".$periode1."' and  '".$periode2."'  order by periode asc, noreferensi asc, kodejurnal asc, tanggal asc";
$resJrn = fetchdata($strJrn);
foreach ($resJrn as $bar) {
	if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)!='VHC' and substr($bar['kodejurnal'],0,3)!='SPK' and substr($bar['kodejurnal'],0,3)!='PRJ'){
		#labor
		if(substr($bar['noakun'],0,1)=='7' and (substr($bar['kodejurnal'],0,2)=='KK' or substr($bar['kodejurnal'],0,2)=='KM' or substr($bar['kodejurnal'],0,2)=='BK' or substr($bar['kodejurnal'],0,2)=='BM' or substr($bar['kodejurnal'],0,1)=='M')){					
			@$subTotalKeg[$bar['periode']][$bar['kodekegiatan']]['other']+=$bar['jumlah'];
			@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['other']+=$bar['jumlah'];
			@$gTotalKeg[$bar['kodekegiatan']]['other'] += $bar['jumlah'];

			@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['other']+=$bar['jumlah'];
			@$gTotalAkun[substr($bar['noakun'],0,5)]['other'] += $bar['jumlah'];
		}else{
			@$subTotalKeg[$bar['periode']][$bar['kodekegiatan']]['labor']+=$bar['jumlah'];
			@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['labor']+=$bar['jumlah'];
			@$gTotalKeg[$bar['kodekegiatan']]['labor'] += $bar['jumlah'];

			@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['labor']+=$bar['jumlah'];
			@$gTotalAkun[substr($bar['noakun'],0,5)]['labor'] += $bar['jumlah'];
		}
	}else if(substr($bar['kodejurnal'],0,3)=='INV'){
		#material
		@$subTotalKeg[$bar['periode']][$bar['kodekegiatan']]['material']+=$bar['jumlah'];
		@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['material']+=$bar['jumlah'];
		@$gTotalKeg[$bar['kodekegiatan']]['material'] += $bar['jumlah'];

		@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['material']+=$bar['jumlah'];
		@$gTotalAkun[substr($bar['noakun'],0,5)]['material'] += $bar['jumlah'];
	}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
		#transport
		@$subTotalKeg[$bar['periode']][$bar['kodekegiatan']]['transport']+=$bar['jumlah'];
		@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['transport']+=$bar['jumlah'];
		@$gTotalKeg[$bar['kodekegiatan']]['transport'] += $bar['jumlah'];

		@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['transport']+=$bar['jumlah'];
		@$gTotalAkun[substr($bar['noakun'],0,5)]['transport'] += $bar['jumlah'];
	}else{
		#other
		@$subTotalKeg[$bar['periode']][$bar['kodekegiatan']]['other']+=$bar['jumlah'];
		@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['other']+=$bar['jumlah'];
		@$gTotalKeg[$bar['kodekegiatan']]['other'] += $bar['jumlah'];

		@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['other']+=$bar['jumlah'];
		@$gTotalAkun[substr($bar['noakun'],0,5)]['other'] += $bar['jumlah'];
	}
}

// Ambil Nilai Labor, Material, dan Transport dari keu_jurnaldt_vw untuk AKUN 621
$strJrn = "select periode,kodejurnal,noakun,kodekegiatan,jumlah from " . $dbname . ".keu_jurnaldt_vw a where 1=1 and substr(noakun,1,3) in ('621') ".$wh." ".$where." and 
periode between '".$periode1."' and  '".$periode2."'  order by periode asc, noreferensi asc, kodejurnal asc, tanggal asc";
$resJrn = fetchdata($strJrn);
foreach ($resJrn as $bar) {
	if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)!='VHC' and substr($bar['kodejurnal'],0,3)!='SPK' and substr($bar['kodejurnal'],0,3)!='PRJ'){
		#labor
		if(substr($bar['noakun'],0,1)=='7' and (substr($bar['kodejurnal'],0,2)=='KK' or substr($bar['kodejurnal'],0,2)=='KM' or substr($bar['kodejurnal'],0,2)=='BK' or substr($bar['kodejurnal'],0,2)=='BM' or substr($bar['kodejurnal'],0,1)=='M')){					
			// Per Kegiatan
			@$subTotalKeg[$bar['periode']][$bar['kodekegiatan']]['other']+=$bar['jumlah'];
			@$gTotalKeg[$bar['kodekegiatan']]['other'] += $bar['jumlah'];
			
			// Akun 7 angka
			@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['other']+=$bar['jumlah'];
			@$gTotalKeg[substr($bar['noakun'],0,7)]['other'] += $bar['jumlah'];

			@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['other']+=$bar['jumlah'];
			@$gTotalAkun[substr($bar['noakun'],0,5)]['other'] += $bar['jumlah'];
		}else{
			// Per Kegiatan
			@$subTotalKeg[$bar['periode']][$bar['kodekegiatan']]['labor']+=$bar['jumlah'];
			@$gTotalKeg[$bar['kodekegiatan']]['labor'] += $bar['jumlah'];
			
			// Akun 7 angka
			@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['labor']+=$bar['jumlah'];
			@$gTotalKeg[substr($bar['noakun'],0,7)]['labor'] += $bar['jumlah'];

			@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['labor']+=$bar['jumlah'];
			@$gTotalAkun[substr($bar['noakun'],0,5)]['labor'] += $bar['jumlah'];
		}
	}else if(substr($bar['kodejurnal'],0,3)=='INV'){
		#material
		// Per Kegiatan
		@$subTotalKeg[$bar['periode']][$bar['kodekegiatan']]['material']+=$bar['jumlah'];
		@$gTotalKeg[$bar['kodekegiatan']]['material'] += $bar['jumlah'];
		
		// Akun 7 angka
		@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['material']+=$bar['jumlah'];
		@$gTotalKeg[substr($bar['noakun'],0,7)]['material'] += $bar['jumlah'];

		@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['material']+=$bar['jumlah'];
		@$gTotalAkun[substr($bar['noakun'],0,5)]['material'] += $bar['jumlah'];
	}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
		#transport
		// Per Kegiatan
		@$subTotalKeg[$bar['periode']][$bar['kodekegiatan']]['transport']+=$bar['jumlah'];
		@$gTotalKeg[$bar['kodekegiatan']]['transport'] += $bar['jumlah'];
		
		// Akun 7 angka
		@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['transport']+=$bar['jumlah'];
		@$gTotalKeg[substr($bar['noakun'],0,7)]['transport'] += $bar['jumlah'];

		@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['transport']+=$bar['jumlah'];
		@$gTotalAkun[substr($bar['noakun'],0,5)]['transport'] += $bar['jumlah'];
	}else{
		#other
		// Per Kegiatan
		@$subTotalKeg[$bar['periode']][$bar['kodekegiatan']]['other']+=$bar['jumlah'];
		@$gTotalKeg[$bar['kodekegiatan']]['other'] += $bar['jumlah'];
		
		// Akun 7 angka
		@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['other']+=$bar['jumlah'];
		@$gTotalKeg[substr($bar['noakun'],0,7)]['other'] += $bar['jumlah'];

		@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['other']+=$bar['jumlah'];
		@$gTotalAkun[substr($bar['noakun'],0,5)]['other'] += $bar['jumlah'];
	}
}

// Ambil Nilai Labor, Material, dan Transport dari keu_jurnaldt_vw untuk AKUN Kepala 7
$strJrn = "select periode,kodejurnal,noakun,kodekegiatan,jumlah from " . $dbname . ".keu_jurnaldt_vw a where 1=1 ".$whereakunumm." ".$wh." ".$where." and 
periode between '".$periode1."' and  '".$periode2."'  order by periode asc, noreferensi asc, kodejurnal asc, tanggal asc";
$resJrn = fetchdata($strJrn);
foreach ($resJrn as $bar) {
	if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)!='VHC' and substr($bar['kodejurnal'],0,3)!='SPK' and substr($bar['kodejurnal'],0,3)!='PRJ'){
		#labor
		if((substr($bar['kodejurnal'],0,2)=='KK' or substr($bar['kodejurnal'],0,2)=='KM' or substr($bar['kodejurnal'],0,2)=='BK' or substr($bar['kodejurnal'],0,2)=='BM' or substr($bar['kodejurnal'],0,1)=='M')){					
			@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['other']+=$bar['jumlah'];
			@$gTotalKeg[substr($bar['noakun'],0,7)]['other'] += $bar['jumlah'];

			@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['other']+=$bar['jumlah'];
			@$gTotalAkun[substr($bar['noakun'],0,5)]['other'] += $bar['jumlah'];
		}else{
			@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['labor']+=$bar['jumlah'];
			@$gTotalKeg[substr($bar['noakun'],0,7)]['labor'] += $bar['jumlah'];

			@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['labor']+=$bar['jumlah'];
			@$gTotalAkun[substr($bar['noakun'],0,5)]['labor'] += $bar['jumlah'];
		}
	}else if(substr($bar['kodejurnal'],0,3)=='INV'){
		#material
		@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['material']+=$bar['jumlah'];
		@$gTotalKeg[substr($bar['noakun'],0,7)]['material'] += $bar['jumlah'];

		@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['material']+=$bar['jumlah'];
		@$gTotalAkun[substr($bar['noakun'],0,5)]['material'] += $bar['jumlah'];
	}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
		#transport
		@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['transport']+=$bar['jumlah'];
		@$gTotalKeg[substr($bar['noakun'],0,7)]['transport'] += $bar['jumlah'];

		@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['transport']+=$bar['jumlah'];
		@$gTotalAkun[substr($bar['noakun'],0,5)]['transport'] += $bar['jumlah'];
	}else{
		#other
		@$subTotalKeg[$bar['periode']][substr($bar['noakun'],0,7)]['other']+=$bar['jumlah'];
		@$gTotalKeg[substr($bar['noakun'],0,7)]['other'] += $bar['jumlah'];

		@$subTotalAkun[$bar['periode']][substr($bar['noakun'],0,5)]['other']+=$bar['jumlah'];
		@$gTotalAkun[substr($bar['noakun'],0,5)]['other'] += $bar['jumlah'];
	}
}

#ini khusus budget UMUM
$str=" select substr(noakun,1,5) as jobgroup,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." and kodebudget='UMUM' and tahunbudget = '".$tahun."' ".$whereakun."";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
	@$bgtbi[$bar['jobgroup']] += $bar['bi'];
	@$bgtsdbi[$bar['jobgroup']] += $bar['sdbi'];
	@$bgtthn[$bar['jobgroup']] += $bar['rupiah'];

	@$cdbgtbi[$bar['noakun']] += $bar['bi'];
	@$cdbgtsdbi[$bar['noakun']] += $bar['sdbi'];
	@$cdbgtthn[$bar['noakun']] += $bar['rupiah'];

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

if ($proses == 'excel') {
	$arrtipe=array("group"=>"Per Job Group","code"=>"Per Job Code");
	$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
	if($kdorg!=''){$xkdorg=$nmorg[$kdorg];}else{$xkdorg=$_SESSION['lang']['all'];}
	if($divisi!=''){$xdivisi=$nmorg[$divisi];}else{$xdivisi=$_SESSION['lang']['all'];}
	if($tt!=''){$xtt=$tt;}else{$xtt=$_SESSION['lang']['all'];}
	if($ip!=''){$xip=$ip;}else{$xip=$_SESSION['lang']['all'];}


	$tab="<table class=sortable cellspacing=1 width=100%>";
	$tab.="<tr><td align=center colspan=15>ANALISA BIAYA TM (".$arrtipe[$tipe].")</td>";
	$tab.="<tr><td align=center colspan=15>" . $_SESSION['lang']['pt'] . " : ".$nmorg[$pt].";&nbsp;";
	$tab.="" . $_SESSION['lang']['unit'] . " : ".$xkdorg."</td></tr>";
	$tab.="<tr><td align=center colspan=15>" . $_SESSION['lang']['divisi'] . " : ".$xdivisi.";&nbsp;";
	$tab.="" . $_SESSION['lang']['tahuntanam'] . " : ".$xtt."</td></tr>";
	$tab.="<tr><td align=center colspan=15>" . $_SESSION['lang']['intiplasma'] . " : ".$xip.";&nbsp;";
	$tab.="" . $_SESSION['lang']['periode'] . " : ".$prd."</td></tr>";
    $tab.="<table class=sortable cellspacing=1 border=1>";
} else {
	$tab.="<div class='menu'>
			<div id='btninscmnt' class='menu-item'>Insert Comment</div>
			<div id='btnshowcmn' class='menu-item'>Show Comment</div>
			<div id='btnreloadframe' class='menu-item'>Reload Frame</div>
		</div>";
    $tab .= "<table class=sortable cellpadding=5 cellspacing=1>";
}

$lmto = ['labor','material','transport'];

$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan='5'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='5'>".$_SESSION['lang']['kode']."</th>
            <th align=center rowspan='2'></th>
            <th align=center rowspan='1' colspan='".(count($lmto)+5)."'>".$_SESSION['lang']['bulanini']."</th>
            <th align=center rowspan='1' colspan='".(count($lmto)+5)."'>".$_SESSION['lang']['sdbulanini']."</th>
            <th align=center rowspan='2' colspan='2'>".$_SESSION['lang']['tahunanggaran']."</th>
        </tr>
        <tr>";
            $tab.="<th align=center colspan='".(count($lmto)+2)."'>".$_SESSION['lang']['realisasi']."</th>";
            $tab.="<th align=center colspan='2'>".$_SESSION['lang']['budget']."</th>";
            $tab.="<th align=center rowspan='4'>%</th>";
			$tab.="<th align=center colspan='".(count($lmto)+2)."'>".$_SESSION['lang']['realisasi']."</th>";
            $tab.="<th align=center colspan='2'>".$_SESSION['lang']['budget']."</th>";
            $tab.="<th align=center rowspan='4'>%</th>";
        $tab.="</tr>
        <tr>";
            $tab.="<th align=center>".$_SESSION['lang']['luas']." (Ha)</th>";
            $tab.="<th align=center colspan='2'>".@nantozero($luasreal,2)."</th>";
			foreach ($lmto as $lm) {
				$tab.="<th align=center rowspan='3'>".$lm."</th>";
			}
            $tab.="<th align=center colspan='2'>".@nantozero($luasbudget,2)."</th>";
            $tab.="<th align=center colspan='2'>".@nantozero($luasreal,2)."</th>";
			foreach ($lmto as $lm) {
				$tab.="<th align=center rowspan='3'>".$lm."</th>";
			}
            $tab.="<th align=center colspan='2'>".@nantozero($luasbudget,2)."</th>";
            $tab.="<th align=center colspan='2'>".@nantozero($luasbudget,2)."</th>";
        $tab.="</tr>
		<tr>
            <th align=center>".$_SESSION['lang']['produksi']." (Ton)</th>
            <th align=center colspan='2'>".@nantozero(($prdrealbi/1000),2)."</th>
            <th align=center colspan='2'>".@nantozero(($prdbgtbi/1000),2)."</th>
            <th align=center colspan='2'>".@nantozero(($prdrealsdbi/1000),2)."</th>
            <th align=center colspan='2'>".@nantozero(($prdbgtsdbi/1000),2)."</th>
            <th align=center colspan='2'>".@nantozero(($prdbgtthn/1000),2)."</th>
        </tr>
		<tr>
            <th align=center>Yield (Ton /Ha)</th>
            <th align=center colspan='2'>".@nantozero(($prdrealbi/1000)/$luasreal,2)."</th>
            <th align=center colspan='2'>".@nantozero(($prdbgtbi/1000)/$luasbudget,2)."</th>
            <th align=center colspan='2'>".@nantozero(($prdrealsdbi/1000)/$luasreal,2)."</th>
            <th align=center colspan='2'>".@nantozero(($prdbgtsdbi/1000)/$luasbudget,2)."</th>
            <th align=center colspan='2'>".@nantozero(($prdbgtthn/1000)/$luasbudget,2)."</th>
        </tr>

    </thead>
<tbody>";

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

// echo"<pre>";
// print_r($showcomment);
// echo"</pre>";
ksort($arrakun);
ksort($listakun);

$nmkode=array('pml'=>'Pemeliharaan','pnn'=>'Panen dan Pengangkutan','umm'=>'Tidak Langsung (Umum)');

#isinya ada 2 jenis
switch ($tipe) {
	case'group':
		$color=" style=background-color:#E8DAEF";
		$tab.="<tr class=rowcontent>";
            $tab.="<td ".$color."></td>";
            $tab.="<td ".$color." align=center><i>Code</i></td>";
            $tab.="<td ".$color." align=center><i>Activity Group</i></td>";
            $tab.="<td ".$color." align=center><i>Total</i></td>";
            $tab.="<td ".$color." align=center><i>Rp/Ha</i></td>";
			foreach ($lmto as $lm){
				$tab.="<td ".$color." align=center></td>";
			}
			$tab.="<td ".$color." align=center><i>Total</i></td>";
            $tab.="<td ".$color." align=center><i>Rp/Ha</i></td>";
            $tab.="<td ".$color." align=center></td>";
			$tab.="<td ".$color." align=center><i>Total</i></td>";
            $tab.="<td ".$color." align=center><i>Rp/Ha</i></td>";
			foreach ($lmto as $lm){
				$tab.="<td ".$color." align=center></td>";
			}
			$tab.="<td ".$color." align=center><i>Total</i></td>";
            $tab.="<td ".$color." align=center><i>Rp/Ha</i></td>";
            $tab.="<td ".$color." align=center></td>";
			$tab.="<td ".$color." align=center><i>Total</i></td>
            <td ".$color." align=center><i>Rp/Ha</i></td>
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
					if($kode=='pml'){
						@$rprealbisat=$realbi[$akun]/$luasreal;
						@$rpbgtbisat=$bgtbi[$akun]/$luasbudget;
					}else{
						@$rprealbisat=$realbi[$akun]/$prdrealbi;
						@$rpbgtbisat=$bgtbi[$akun]/$prdbgtbi;
					}
					$tab.="<td align=right>".@nantozero($rprealbisat,2)."</td>";
					foreach ($lmto as $lm){
						$tab.="<td align=center>".@nantozero($subTotalAkun[$prd][$akun][$lm])."</td>";
					}
					$tab.="<td ".$detbgtbi." align=right>".@nantozero($bgtbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtbisat,2)."</td>";

					$persenbi=0;
					if($rprealbisat>0){
						@$persenbi=(($rpbgtbisat-$rprealbisat)/$rpbgtbisat)*100;
					}
					$c="";
					if($persenbi<0){
						$c=" style=color:red;";
					}
					$tab.="<td align=right ".$c.">".@nantozero($persenbi,2)."</td>";
					// foreach ($lmto as $lm){
					// 	$tab.="<td align=center>".@nantozero($subTotalAkun[$prd][$akun][$lm])."</td>";
					// }
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
					if($kode=='pml'){
						@$rprealsdbisat=$realsdbi[$akun]/$luasreal;
						@$rpbgtsdbisat=$bgtsdbi[$akun]/$luasbudget;
					}else{
						@$rprealsdbisat=$realsdbi[$akun]/$prdrealsdbi;
						@$rpbgtsdbisat=$bgtsdbi[$akun]/$prdbgtsdbi;
					}
					$tab.="<td align=right>".@nantozero($rprealsdbisat,2)."</td>";
					foreach ($lmto as $lm){
						$tab.="<td align=center>".@nantozero($gTotalAkun[$prd][$akun][$lm])."</td>";
					}
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

					#====== thn ======
					if($kode=='pml'){
						@$rpbgtthnsat=$bgtthn[$akun]/$luasbudget;
					}else{
						@$rpbgtthnsat=$bgtthn[$akun]/$prdbgtthn;
					}

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
			$color=" style=background-color:#D5F5E3";
			$tab.="<tr class=rowcontent>";
			$tab.="<td ".$color." align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			#===== bi =====
			$tab.="<td ".$color." align=right ".$detrealbi.">".@nantozero($ttlrealbi[$kode])."</td>";
			if($kode=='pml'){
				@$ttlrprealbisat=$ttlrealbi[$kode]/$luasreal;
				@$ttlrpbgtbisat=$ttlbgtbi[$kode]/$luasbudget;
			}else{
				@$ttlrprealbisat=$ttlrealbi[$kode]/$prdrealbi;
				@$ttlrpbgtbisat=$ttlbgtbi[$kode]/$prdbgtbi;
			}
			$tab.="<td ".$color." align=right>".@nantozero($ttlrprealbisat,2)."</td>";
			foreach ($lmto as $lm){
				$tab.="<td ".$color." align=center></td>";
			}
			$tab.="<td ".$color." align=right ".$detbgtbi.">".@nantozero($ttlbgtbi[$kode])."</td>";
			$tab.="<td ".$color." align=right>".@nantozero($ttlrpbgtbisat,2)."</td>";

			$ttlpersenbi=0;
			if($ttlrprealbisat>0){
				@$ttlpersenbi=(($ttlrpbgtbisat-$ttlrprealbisat)/$ttlrpbgtbisat)*100;
			}
			$c="";
			if($ttlpersenbi<0){
				$c=" style=color:red;";
			}
			$tab.="<td ".$color." align=right ".$c.">".@nantozero($ttlpersenbi,2)."</td>";
			#=== end bi ===
			#====== sdbi ======
			$tab.="<td ".$color." align=right ".$detrealsdbi.">".@nantozero($ttlrealsdbi[$kode])."</td>";
			if($kode=='pml'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$luasreal;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$luasbudget;
			}else{
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$prdrealsdbi;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$prdbgtsdbi;
			}
			$tab.="<td ".$color." align=right>".@nantozero($ttlrprealsdbisat,2)."</td>";
			foreach ($lmto as $lm){
				$tab.="<td ".$color." align=center></td>";
			}
			$tab.="<td ".$color." align=right ".$detbgtsdbi.">".@nantozero($ttlbgtsdbi[$kode])."</td>";
			$tab.="<td ".$color." align=right>".@nantozero($ttlrpbgtsdbisat,2)."</td>";

			$ttlpersensdbi=0;
			if($ttlrprealsdbisat>0){
				@$ttlpersensdbi=(($ttlrpbgtsdbisat-$ttlrprealsdbisat)/$ttlrpbgtsdbisat)*100;
			}
			$c="";
			if($ttlpersensdbi<0){
				$c=" style=color:red;";
			}
			$tab.="<td ".$color." align=right ".$c.">".@nantozero($ttlpersensdbi,2)."</td>";
			#===== end sdbi =====
			#====== thn ======
			if($kode=='pml'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$luasbudget;
			}else{
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$prdbgtthn;
			}
			$tab.="<td ".$color." align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode])."</td>";
			$tab.="<td ".$color." align=right>".@nantozero($ttlrpbgtthnsat,2)."</td>";
			#===== end thn =====
			$tab.="</tr>";
			if($kode=='pml'){
				$color="style=background-color:#E8DAEF";
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF>";
					$tab.="<td ".$color."></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center><i>Rp/Kg</i></td>";
					foreach ($lmto as $lm){
						$tab.="<td ".$color." align=center></td>";
					}
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center><i>Rp/Kg</i></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center><i>Rp/Kg</i></td>";
					foreach ($lmto as $lm){
						$tab.="<td ".$color." align=center></td>";
					}
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center><i>Rp/Kg</i></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center><i>Rp/Kg</i></td>
				</tr>";
			}
		}
		#grand total
		$color="style=background-color:#27ED1C";
		$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
		$tab.="<td ".$color." align=center colspan=3>GRAND TOTAL</td>";

		#===== bi =====
		$tab.="<td ".$color." align=right>".@nantozero($trealbi)."</td>";
		@$rpperprdrealbi=$trealbi/@$prdrealbi;
		$tab.="<td ".$color." align=right>".@nantozero($rpperprdrealbi,2)."</td>";
		foreach ($lmto as $lm){
			$tab.="<td ".$color." align=center></td>";
		}
		$tab.="<td ".$color." align=right>".@nantozero($tbgtbi)."</td>";
		@$rpperprdbgtbi=$tbgtbi/$prdbgtbi;
		$tab.="<td ".$color." align=right>".@nantozero($rpperprdbgtbi,2)."</td>";
		@$gtpersenbi=(($rpperprdbgtbi-$rpperprdrealbi)/$rpperprdbgtbi)*100;
		$tab.="<td ".$color." align=right>".@nantozero($gtpersenbi,2)."</td>";
		#=== end bi ===
		#====== sdbi ======
		$tab.="<td ".$color." align=right>".@nantozero($trealsdbi)."</td>";
		@$rpperprdrealsdbi=$trealsdbi/@$prdrealsdbi;
		$tab.="<td ".$color." align=right>".@nantozero($rpperprdrealsdbi,2)."</td>";
		foreach ($lmto as $lm){
			$tab.="<td ".$color." align=center></td>";
		}
		$tab.="<td ".$color." align=right>".@nantozero($tbgtsdbi)."</td>";
		@$rpperprdbgtsdbi=$tbgtsdbi/@$prdbgtsdbi;
		$tab.="<td ".$color." align=right>".@nantozero($rpperprdbgtsdbi,2)."</td>";
		@$gtpersensdbi=(($rpperprdbgtsdbi-$rpperprdrealsdbi)/$rpperprdbgtsdbi)*100;
		$tab.="<td ".$color." align=right>".@nantozero($gtpersensdbi,2)."</td>";
		#===== end sdbi =====
		#====== thn ======
		$tab.="<td ".$color." align=right>".@nantozero($tbgtthn)."</td>";
		$tab.="<td ".$color." align=right>".@nantozero($tbgtthn/@$prdbgtthn,2)."</td>";
		#===== end thn =====

	break;

	case'code':
	case'keg':
		$color="style=background-color:#E8DAEF";
		$tab.="<tr class=rowcontent style=background-color:#E8DAEF>";
            $tab.="<td ".$color."></td>";
            $tab.="<td ".$color." align=center><i>Code</i></td>";
            $tab.="<td ".$color." align=center><i>Activity Group</i></td>";
            $tab.="<td ".$color." align=center><i>Total</i></td>";
            $tab.="<td ".$color." align=center><i>Rp/Ha</i></td>";
			foreach ($lmto as $lm){
				$tab.="<td ".$color." align=center></td>";
			}
			$tab.="<td ".$color." align=center><i>Total</i></td>";
            $tab.="<td ".$color." align=center><i>Rp/Ha</i></td>";
            $tab.="<td ".$color." align=center></td>";
			$tab.="<td ".$color." align=center><i>Total</i></td>";
            $tab.="<td ".$color." align=center><i>Rp/Ha</i></td>";
			foreach ($lmto as $lm){
				$tab.="<td ".$color." align=center></td>";
			}
			$tab.="<td ".$color." align=center><i>Total</i></td>";
            $tab.="<td ".$color." align=center><i>Rp/Ha</i></td>";
            $tab.="<td ".$color." align=center></td>";
			$tab.="<td ".$color." align=center><i>Total</i></td>
            <td ".$color." align=center><i>Rp/Ha</i></td>
        </tr>";
		ksort($arrakun7);
		ksort($cdarrakun);

		foreach($arrkode as $kode){
			foreach($arrakun7 as $akun){
				if(@$cdarrakun[$kode][$akun]!=''){
					if($tipe=='code'){
						$d=substr($akun,0,5);
					}else{
						if(substr($akun,0,1)=='7'){
							$d=substr($akun,0,5);
						}else{
							$d=substr($akun,0,7);
						}
					}

					$tempakun[$d][]=$akun;

					$subtotal[$d]['realbi']+=$cdrealbi[$akun];
					$subtotal[$d]['realsdbi']+=$cdrealsdbi[$akun];
					$subtotal[$d]['bgtbi']+=$cdbgtbi[$akun];
					$subtotal[$d]['bgtsdbi']+=$cdbgtsdbi[$akun];
					$subtotal[$d]['bgtthn']+=$cdbgtthn[$akun];

					$tempgt[$akun]+=abs($cdrealbi[$akun]+$cdrealsdbi[$akun]+$cdbgtbi[$akun]+$cdbgtsdbi[$akun]+$cdbgtthn[$akun]);
				}
			}
		}
		ksort($tempakun);

		$arrakun7=[];
		foreach($tempakun as $ak => $v1){
			foreach($v1 as $key => $kg){
				$arrakun7[$kg]=$kg;
				if($tempgt[$kg]==0 and $tipe=='keg' and strlen($kg)<=7){
					unset($arrakun7[$kg]);
				}
			}
		}


		// echo"<pre>";
		// print_r($arrakun7);
		// echo"</pre>";

		foreach($arrkode as $kode){
			$no=0;
			foreach($arrakun7 as $akun){
				if(@$cdarrakun[$kode][$akun]!=''){
					$no++;
					$show="";
					if(substr($akun,0,1)=='7'){
						//$show=" style=display:none";
					}
					if($tipe=='code'){
						$d=substr($akun,0,5);
					}else{
						if(substr($akun,0,1)=='7'){
							$d=substr($akun,0,5);
						}else{
							$d=substr($akun,0,7);
						}
					}
					if($d!=$n){
						$color="style=background-color:#ccfffd;font-style:italic;font-weight:bold;";
						$tab.="<tr class=rowcontent ".$show." style=background-color:#ccfffd;>";
						$tab.="<td ".$color."></td>";
						$tab.="<td ".$color."><i>".$d."</i></td>";
						if(getNamaAkun($d)!=''){
							$tab.="<td ".$color."><i>".ucwords(strtolower(getNamaAkun($d)))."</i></td>";
						}else{
							$tab.="<td ".$color."><i>".ucwords(strtolower(getNamaKeg($d)))."</i></td>";
						}
						if($tipe=='keg'){
							$color="style=background-color:#ccfffd;font-style:italic;";
							if($kode=='pml'){
								@$rpprha[$d][$kode]['realbi']=$subtotal[$d]['realbi']/$luasreal;
								@$rpprha[$d][$kode]['bgtbi']=$subtotal[$d]['bgtbi']/$luasbudget;
								@$rpprha[$d][$kode]['realsdbi']=$subtotal[$d]['realsdbi']/$luasreal;
								@$rpprha[$d][$kode]['bgtsdbi']=$subtotal[$d]['bgtsdbi']/$luasbudget;
								@$rpprha[$d][$kode]['bgtthn']=$subtotal[$d]['bgtthn']/$luasbudget;
							}else{
								@$rpprha[$d][$kode]['realbi']=$subtotal[$d]['realbi']/$prdrealbi;
								@$rpprha[$d][$kode]['bgtbi']=$subtotal[$d]['bgtbi']/$prdbgtbi;
								@$rpprha[$d][$kode]['realsdbi']=$subtotal[$d]['realsdbi']/$prdrealsdbi;
								@$rpprha[$d][$kode]['bgtsdbi']=$subtotal[$d]['bgtsdbi']/$prdbgtsdbi;
								@$rpprha[$d][$kode]['bgtthn']=$subtotal[$d]['bgtthn']/$prdbgtthn;
							}
							
							$persen[$d]['bi']=0;
							if($rpprha[$d][$kode]['realbi']>0){
								@$persen[$d]['bi']=(($rpprha[$d][$kode]['bgtbi']-$rpprha[$d][$kode]['realbi'])/$rpprha[$d][$kode]['bgtbi'])*100;
							}
							if($rpprha[$d][$kode]['realsdbi']>0){
								@$persen[$d]['sdbi']=(($rpprha[$d][$kode]['bgtsdbi']-$rpprha[$d][$kode]['realsdbi'])/$rpprha[$d][$kode]['bgtsdbi'])*100;
							}
							
							# == bi ==
							$tab.="<td ".$color." align=right>".@nantozero($subtotal[$d]['realbi'])."</td>";
							$tab.="<td ".$color." align=right>".@nantozero($rpprha[$d][$kode]['realbi'],2)."</td>";
							foreach ($lmto as $lm){
								$tab.="<td align=center></td>";
							}
							$tab.="<td ".$color." align=right>".@nantozero($subtotal[$d]['bgtbi'])."</td>";
							$tab.="<td ".$color." align=right>".@nantozero($rpprha[$d][$kode]['bgtbi'],2)."</td>";
							$tab.="<td ".$color." align=right>".@nantozero($persen[$d]['bi'],2)."</td>";
							
							$tab.="<td ".$color." align=right>".@nantozero($subtotal[$d]['realsdbi'])."</td>";
							$tab.="<td ".$color." align=right>".@nantozero($rpprha[$d][$kode]['realsdbi'],2)."</td>";
							foreach ($lmto as $lm){
								$tab.="<td align=center></td>";
							}
							$tab.="<td ".$color." align=right>".@nantozero($subtotal[$d]['bgtsdbi'])."</td>";
							$tab.="<td ".$color." align=right>".@nantozero($rpprha[$d][$kode]['bgtsdbi'],2)."</td>";
							$tab.="<td ".$color." align=right>".@nantozero($persen[$d]['sdbi'],2)."</td>";
							
							$tab.="<td ".$color." align=right>".@nantozero($subtotal[$d]['bgtthn'])."</td>";
							$tab.="<td ".$color." align=right>".@nantozero($rpprha[$d][$kode]['bgtthn'],2)."</td>";
							# == sdbi ==
						}else{
							$tab.="<td ".$color."></td>";
							$tab.="<td ".$color."></td>";
							foreach ($lmto as $lm){
								$tab.="<td ".$color." align=center></td>";
							}
							$tab.="<td ".$color."></td>";
							$tab.="<td ".$color."></td>";
							$tab.="<td ".$color."></td>";
							$tab.="<td ".$color."></td>";
							$tab.="<td ".$color."></td>";
							foreach ($lmto as $lm){
								$tab.="<td ".$color." align=center></td>";
							}
							$tab.="<td ".$color."></td>";
							$tab.="<td ".$color."></td>";
							$tab.="<td ".$color."></td>";
							$tab.="<td ".$color."></td>";
							$tab.="<td ".$color."></td>";
						}

						$tab.="</tr>";
					}


					$tab.="<tr class=rowcontent ".$show.">";
					$tab.="<td align=center>".$no."</td>";
					$tab.="<td>".$akun."</td>";
					if($nmakun[$akun]!=''){
						$tab.="<td>".$nmakun[$akun]."</td>";
					}else{
						$tab.="<td><i>".getNamaKeg($akun)."</i></td>";
					}
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
					if($kode=='pml'){
						@$rprealbisat=$cdrealbi[$akun]/$luasreal;
						@$rpbgtbisat=$cdbgtbi[$akun]/$luasbudget;
					}else{
						@$rprealbisat=$cdrealbi[$akun]/$prdrealbi;
						@$rpbgtbisat=$cdbgtbi[$akun]/$prdbgtbi;
					}
					$tab.="<td align=right>".@nantozero($rprealbisat,2)."</td>";
					foreach ($lmto as $lm){
						$tab.="<td align=center>".@nantozero($subTotalKeg[$prd][$akun][$lm])."</td>";
					}
					$tab.="<td align=right ".$detbgtbi.">".@nantozero($cdbgtbi[$akun])."</td>";
					$tab.="<td align=right>".@nantozero($rpbgtbisat,2)."</td>";

					$persenbi=0;
					if($rprealbisat>0){
						@$persenbi=(($rpbgtbisat-$rprealbisat)/$rpbgtbisat)*100;
					}
					$c="";
					if($persenbi<0){
						$c=" style=color:red;";
					}
					$tab.="<td align=right ".$c.">".@nantozero($persenbi,2)."</td>";
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
					if($kode=='pml'){
						@$rprealsdbisat=$cdrealsdbi[$akun]/$luasreal;
						@$rpbgtsdbisat=$cdbgtsdbi[$akun]/$luasbudget;
					}else{
						@$rprealsdbisat=$cdrealsdbi[$akun]/$prdrealsdbi;
						@$rpbgtsdbisat=$cdbgtsdbi[$akun]/$prdbgtsdbi;
					}
					$tab.="<td align=right>".@nantozero($rprealsdbisat,2)."</td>";
					foreach ($lmto as $lm){
						$tab.="<td align=center>".@nantozero($gTotalKeg[$akun][$lm])."</td>";
					}
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

					#====== thn ======
					if($kode=='pml'){
						@$rpbgtthnsat=$cdbgtthn[$akun]/$luasbudget;
					}else{
						@$rpbgtthnsat=$cdbgtthn[$akun]/$prdbgtthn;
					}
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
			}else{
				$detrealbi=$detbgtbi=$detrealsdbi=$detbgtsdbi=$detbgtthn="";
			}
			$color="style=background-color:#D5F5E3";
			$tab.="<tr class=rowcontent style=background-color:#D5F5E3>";
			$tab.="<td ".$color." align=center colspan=3><i>Total Biaya ".$nmkode[$kode]."</i></td>";
			#===== bi =====
			$tab.="<td ".$color." align=right ".$detrealbi.">".@nantozero($ttlrealbi[$kode])."</td>";
			if($kode=='pml'){
				@$ttlrprealbisat=$ttlrealbi[$kode]/$luasreal;
				@$ttlrpbgtbisat=$ttlbgtbi[$kode]/$luasbudget;
			}else{
				@$ttlrprealbisat=$ttlrealbi[$kode]/$prdrealbi;
				@$ttlrpbgtbisat=$ttlbgtbi[$kode]/$prdbgtbi;
			}
			$tab.="<td ".$color." align=right>".@nantozero($ttlrprealbisat,2)."</td>";
			foreach ($lmto as $lm){
				$tab.="<td align=center></td>";
			}
			$tab.="<td ".$color." align=right ".$detbgtbi.">".@nantozero($ttlbgtbi[$kode])."</td>";
			$tab.="<td ".$color." align=right>".@nantozero($ttlrpbgtbisat,2)."</td>";

			$ttlpersenbi=0;
			if($ttlrprealbisat>0){
				@$ttlpersenbi=(($ttlrpbgtbisat-$ttlrprealbisat)/$ttlrpbgtbisat)*100;
			}
			$c="";
			if($ttlpersenbi<0){
				$c=" style=color:red;";
			}
			$tab.="<td ".$color." align=right ".$c.">".@nantozero($ttlpersenbi,2)."</td>";
			#=== end bi ===
			#====== sdbi ======
			$tab.="<td ".$color." align=right ".$detrealsdbi.">".@nantozero($ttlrealsdbi[$kode])."</td>";
			if($kode=='pml'){
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$luasreal;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$luasbudget;
			}else{
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$prdrealsdbi;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$prdbgtsdbi;
			}
			$tab.="<td ".$color." align=right>".@nantozero($ttlrprealsdbisat,2)."</td>";
			foreach ($lmto as $lm){
				$tab.="<td align=center></td>";
			}
			$tab.="<td ".$color." align=right ".$detbgtsdbi.">".@nantozero($ttlbgtsdbi[$kode])."</td>";
			$tab.="<td ".$color." align=right>".@nantozero($ttlrpbgtsdbisat,2)."</td>";

			$ttlpersensdbi=0;
			if($ttlrprealsdbisat>0){
				@$ttlpersensdbi=(($ttlrpbgtsdbisat-$ttlrprealsdbisat)/$ttlrpbgtsdbisat)*100;
			}
			$c="";
			if($ttlpersensdbi<0){
				$c=" style=color:red;";
			}
			$tab.="<td ".$color." align=right ".$c.">".@nantozero($ttlpersensdbi,2)."</td>";
			#===== end sdbi =====
			#====== thn ======
			if($kode=='pml'){
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$luasbudget;
			}else{
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$prdbgtthn;
			}
			$tab.="<td ".$color." align=right ".$detbgtthn.">".@nantozero($ttlbgtthn[$kode])."</td>";
			$tab.="<td ".$color." align=right>".@nantozero($ttlrpbgtthnsat,2)."</td>";
			#===== end thn =====
			$tab.="</tr>";
			if($kode=='pml'){
				@$ttlrprealbisat=$ttlrealbi[$kode]/$prdrealbi;
				@$ttlrpbgtbisat=$ttlbgtbi[$kode]/$prdbgtbi;
				@$ttlrprealsdbisat=$ttlrealsdbi[$kode]/$prdrealsdbi;
				@$ttlrpbgtsdbisat=$ttlbgtsdbi[$kode]/$prdbgtsdbi;
				@$ttlrpbgtthnsat=$ttlbgtthn[$kode]/$prdbgtthn;
				
				$ttlpersenbi=0;
				if($ttlrprealbisat>0){
					@$ttlpersenbi=(($ttlrpbgtbisat-$ttlrprealbisat)/$ttlrpbgtbisat)*100;
				}
				$ttlpersensdbi=0;
				if($ttlrprealsdbisat>0){
					@$ttlpersensdbi=(($ttlrpbgtsdbisat-$ttlrprealsdbisat)/$ttlrpbgtsdbisat)*100;
				}
				
				$color="style=background-color:#ccff66;font-style:italic;font-weight:bold;";
				$tab.="<tr class=rowcontent style=background-color:#ccff66>
					<td ".$color." colspan=3 align=center>Total Biaya ".$nmkode[$kode]." (Rp/Kg)</td>
					<td ".$color." align=center></td>
					<td ".$color." align=right><i>".nantozero($ttlrprealbisat,2)."</i></td>";
					foreach ($lmto as $lm){
						$tab.="<td ".$color." align=center></td>";
					}
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=right><i>".nantozero($ttlrpbgtbisat,2)."</i></td>
					<td ".$color." align=right><i>".nantozero($ttlpersenbi,2)."</i></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=right><i>".nantozero($ttlrprealsdbisat,2)."</i></td>";
					foreach ($lmto as $lm){
						$tab.="<td ".$color." align=center></td>";
					}
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=right><i>".nantozero($ttlrpbgtsdbisat,2)."</i></td>";
					$tab.="<td ".$color." align=right><i>".nantozero($ttlpersensdbi,2)."</i></td>";
					$tab.="<td ".$color." align=center></td>
					<td ".$color." align=right><i>".nantozero($ttlrpbgtthnsat,2)."</i></td>
				</tr>";
				
				
				
				$color="style=background-color:#E8DAEF";
				$tab.="<tr class=rowcontent style=background-color:#E8DAEF>";
					$tab.="<td ".$color."></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center><i>Rp/Kg</i></td>";
					foreach ($lmto as $lm){
						$tab.="<td ".$color." align=center></td>";
					}
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center><i>Rp/Kg</i></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center><i>Rp/Kg</i></td>";
					foreach ($lmto as $lm){
						$tab.="<td ".$color." align=center></td>";
					}
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center><i>Rp/Kg</i></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center></td>";
					$tab.="<td ".$color." align=center><i>Rp/Kg</i></td>
				</tr>";
			}
		}


		#grand total
		$color="style=background-color:#27ED1C";
		$tab.="<tr class=rowcontent style=background-color:#27ED1C>";
		$tab.="<td ".$color." align=center colspan=3>GRAND TOTAL</td>";
		#===== bi =====
		$tab.="<td ".$color." align=right>".@nantozero($trealbi)."</td>";
		@$rpperprdrealbi=$trealbi/@$prdrealbi;
		$tab.="<td ".$color." align=right>".@nantozero($rpperprdrealbi,2)."</td>";
		foreach ($lmto as $lm){
			$tab.="<td align=center></td>";
		}
		$tab.="<td ".$color." align=right>".@nantozero($tbgtbi)."</td>";
		@$rpperprdbgtbi=$tbgtbi/$prdbgtbi;
		$tab.="<td ".$color." align=right>".@nantozero($rpperprdbgtbi,2)."</td>";
		@$gtpersenbi=(($rpperprdbgtbi-$rpperprdrealbi)/$rpperprdbgtbi)*100;
		$tab.="<td ".$color." align=right>".@nantozero($gtpersenbi,2)."</td>";
		#=== end bi ===
		#====== sdbi ======
		$tab.="<td ".$color." align=right>".@nantozero($trealsdbi)."</td>";
		@$rpperprdrealsdbi=$trealsdbi/@$prdrealsdbi;
		$tab.="<td ".$color." align=right>".@nantozero($rpperprdrealsdbi,2)."</td>";
		foreach ($lmto as $lm){
			$tab.="<td align=center></td>";
		}
		$tab.="<td ".$color." align=right>".@nantozero($tbgtsdbi)."</td>";
		@$rpperprdbgtsdbi=$tbgtsdbi/@$prdbgtsdbi;
		$tab.="<td ".$color." align=right>".@nantozero($rpperprdbgtsdbi,2)."</td>";
		@$gtpersensdbi=(($rpperprdbgtsdbi-$rpperprdrealsdbi)/$rpperprdbgtsdbi)*100;
		$tab.="<td ".$color." align=right>".@nantozero($gtpersensdbi,2)."</td>";
		#===== end sdbi =====
		#====== thn ======
		$tab.="<td ".$color." align=right>".@nantozero($tbgtthn)."</td>";
		$tab.="<td ".$color." align=right>".@nantozero($tbgtthn/@$prdbgtthn,2)."</td>";
		#===== end thn =====

	break;

	// Fungsi Baru Rizky
	case'summary':
		if ($divisi != '' || $tt != '' || $ip != '') {
			exit('Error: Mohon Divisi, Tahun Tanam, Inti Plasma di pilih "Seluruhnya" !');
		}

		if ($pt == '') {
			exit('Error: Mohon Pilih Perusahaan !');
		}

		$html = '';

		## BORDER FOR TABLE
			if ($proses != 'excel') {
				$border = '0';
				$style = '';
			} else {
				$border= '1';
				$style = "style='background-color:#275370;color:white;'";
			}
		##

		## WHERE PT, UNIT, REGIONAL
			if ($kdorg != '') {
				$where = "AND a.kodeorg = '".$kdorg."'";
			} else if ($pt != '') {
				$where = "AND a.kodeorg IN (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE induk = '".$pt."' and tipe='KEBUN')";
			}
		##
		$arrprd=explode("-",$prd);
		$bulan=$arrprd[1];


		$str = "select * from " . $dbname . ".keu_saldobulanan a where 1=1 ".$where." and noakun not in (select noakundebet from " . $dbname . ".keu_5parameterjurnal where jurnalid in ('CLM')) and periode='".str_replace("-","",$prd)."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			#$sawal[substr($bar['noakun'],0,2)]+=$bar['awal'.$bulan];
		}

		$str = "select * from " . $dbname . ".keu_5akun where 1=1 and noakun not in (select noakundebet from " . $dbname . ".keu_5parameterjurnal where jurnalid in ('CLM')) and level='2'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$data[$bar['noakun']]=$bar['namaakun'];
		}

		// echo "<pre>";
		// print_r($sawal);

		## GET NOAKUN, DEBET, KREDIT
		$query1 = "SELECT left(a.noakun,2) as noakun, c.namaakun, b.kodeorganisasi, sum(a.debet) as debet, sum(a.kredit) as kredit
				   FROM ".$dbname.".keu_jurnaldt_vw a
				   JOIN ".$dbname.".organisasi b ON a.kodeorg = b.kodeorganisasi
				   JOIN ".$dbname.".keu_5akun c ON left(a.noakun,2) = c.noakun
				   WHERE a.nojurnal NOT LIKE '%CLSM%'
				   AND a.periode = '".$prd."'
				   ".$where."
				   GROUP BY left(a.noakun,2)
				   ORDER BY c.namaakun ASC";
		$res1 = fetchdata($query1);

		foreach ($res1 as $key => $val) {
			$debet[$val['noakun']] = $val['debet'];
			$kredit[$val['noakun']] = $val['kredit'];
		}
		#

		// echo"<pre>";
		// print_r($debet);
		// print_r($kredit);
		// echo"</pre>";

		## TABLE DEBET
			$html .= "<table class=sortable border=".$border." cellspacing=1>
						<thead>
							<tr class=rowheader ".$style.">
								<th align=center>".$_SESSION['lang']['nourut']."</th>
								<th align=center width=50px>".$_SESSION['lang']['noakun']."</th>
								<th align=center>".$_SESSION['lang']['namaakun']."</th>
								<th align=center>".$_SESSION['lang']['debet']."</th>
							</tr>
						</thead>
						<tbody>";

			if (count($data) > 0) {
				foreach ($data as $akun => $nmakun) {
					if($debet[$akun]-$kredit[$akun]>=0){
						$no++;
						$html .= "<tr class=rowcontent>
									<td align=center>".$no."</td>
									<td>".$akun."</td>
									<td>".$nmakun."</td>
									<td align=right>".number_format($debet[$akun]-$kredit[$akun])."</td>
								</tr>";
						$total1 += $debet[$akun]-$kredit[$akun];
					}
				}

				$html .= "<tr class=rowcontent ".$style.">
							<th align=center colspan=3>".$_SESSION['lang']['total']."</th>
							<th align=right>".number_format($total1)."</th>
						</tr>";
			} else {
				$html .= "<tr class=rowcontent>
							<td align=center colspan=4><b>".$_SESSION['lang']['datanotfound']."<b></td>
						</tr>";
			}

			$html .= "</tbody></table>";
		## TABLE DEBET

		## TABLE KREDIT

			$html .= "<hr>
						<table class=sortable border=".$border." cellspacing=1>
							<thead>
								<tr class=rowheader ".$style.">
									<th align=center>".$_SESSION['lang']['nourut']."</th>
									<th align=center width=50px>".$_SESSION['lang']['noakun']."</th>
									<th align=center>".$_SESSION['lang']['namaakun']."</th>
									<th align=center>".$_SESSION['lang']['kredit']."</th>
								</tr>
							</thead>
							<tbody>";

			if (count($data) > 0) {
				foreach ($data as $akun => $nmakun) {
					if($debet[$akun]-$kredit[$akun]<=0){
						$nom ++;
						$html .= "<tr class=rowcontent>
									<td align=center>".$nom."</td>
									<td>".$akun."</td>
									<td>".$nmakun."</td>
									<td align=right>".number_format($debet[$akun]-$kredit[$akun])."</td>
								</tr>";
						@$total2 += $debet[$akun]-$kredit[$akun];
					}
				}

				$html .= "<tr class=rowcontent ".$style.">
							<th align=center colspan=3>".$_SESSION['lang']['total']."</th>
							<th align=right>".number_format($total2)."</th>
						</tr>";
			} else {
				$html .= "<tr class=rowcontent>
							<td align=center colspan=4><b>".$_SESSION['lang']['datanotfound']."<b></td>
						</tr>";
			}

			$html .= "</tbody></table>";
		## TABLE KREDIT
	break;

	// Fungsi Baru Rizky
	case'detail':
		if ($divisi != '' || $tt != '' || $ip != '') {
			exit('Error: Mohon Divisi, Tahun Tanam, Inti Plasma di pilih "Seluruhnya" !');
		}

		if ($pt == '') {
			exit('Error: Mohon Pilih Perusahaan !');
		}

		$html = '';

		## BORDER FOR TABLE
			if ($proses != 'excel') {
				$border = '0';
				$style = '';
			} else {
				$border= '1';
				$style = "style='background-color:#275370;color:white;'";
				$style2 = "style='background-color:#D7EBFA;'";
			}
		##

		## WHERE PT, UNIT, REGIONAL
			if ($kdorg != '') {
				$where = "AND a.kodeorg = '".$kdorg."'";
			} else if ($pt != '') {
				$where = "AND a.kodeorg IN (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE induk = '".$pt."' and tipe='KEBUN')";
			}
		##

		## GET NOAKUN, NAMA AKUN
			$query1 = "SELECT * FROM ".$dbname.".keu_5akun";
			$res1 = fetchdata($query1);
			foreach ($res1 as $key => $val) {
				$nmakun[$val['noakun']] = $val['namaakun'];
			}
		#



		## TABLE DETAIL
			$html .= "
						<table class=sortable border=".$border." cellspacing=1>
							<thead>
								<tr class=rowheader ".$style.">
									<th align=center>".$_SESSION['lang']['nourut']."</th>
									<th align=center>".$_SESSION['lang']['noakun']."</th>
									<th align=center>".$_SESSION['lang']['namaakun']."</th>
									<th align=center>".$_SESSION['lang']['debet']."</th>
									<th align=center>".$_SESSION['lang']['kredit']."</th>
									<th align=center>".$_SESSION['lang']['saldo']."</th>
								</tr>
							</thead>
							<tbody>";

			$str = "SELECT substr(a.noakun,1,2) as groupakun, a.noakun, sum(a.debet) as debet, sum(a.kredit) as kredit
					FROM ".$dbname.".keu_jurnaldt_vw a
					WHERE a.periode = '".$prd."'
					".$where." and a.nojurnal NOT LIKE '%CLSM%'
					GROUP BY substr(a.noakun,1,2),a.noakun";
			$res = fetchdata($str);
			foreach ($res as $bar){
				$data[$bar['groupakun']][$bar['noakun']]+=$bar['noakun'];
				$debet[$bar['groupakun']][$bar['noakun']]+=$bar['debet'];
				$kredit[$bar['groupakun']][$bar['noakun']]+=$bar['kredit'];
			}


			if (count($data) > 0) {
				foreach ($data as $group => $v1) {
					$html .= "<tr class=rowcontent>
								<td></td>
								<td colspan=5><b>".$group." - ".$nmakun[$group]."</b></td>
							</tr>";
					$no=0;
					$subdebet = array();
					$subkredit = array();
					foreach ($v1 as $akun) {
						$no++;
						$html .= "<tr class=rowcontent>
									<td align=center>".$no."</td>
									<td align=center>".$akun."</td>
									<td align=left>".$nmakun[$akun]."</td>
									<td align=right>".number_format($debet[$group][$akun])."</td>
									<td align=right>".number_format($kredit[$group][$akun])."</td>
									<td align=right>".number_format($debet[$group][$akun]-$kredit[$group][$akun])."</td>
								</tr>";

						$subdebet[$group]  += $debet[$group][$akun];
						$subkredit[$group] += $kredit[$group][$akun];

						$totaldebet += $debet[$group][$akun];
						$totalkredit += $kredit[$group][$akun];
					}

					$html .= "<tr class=rowcontent ".$style2.">
								<th align=center colspan=3>".$_SESSION['lang']['subtotal']."</th>
								<th align=right>".number_format($subdebet[$group])."</th>
								<th align=right>".number_format($subkredit[$group])."</th>
								<th align=right>".number_format($subdebet[$group]-$subkredit[$group])."</th>
							</tr>";

				}

				$html .= "<tr class=rowcontent ".$style.">
							<th align=center colspan=3>".$_SESSION['lang']['total']."</th>
							<th align=right>".number_format($totaldebet)."</th>
							<th align=right>".number_format($totalkredit)."</th>
							<th align=right>".number_format($totaldebet-$totalkredit)."</th>
						</tr>";
			} else {
				$html .= "<tr class=rowcontent>
							<td align=center colspan=6><b>".$_SESSION['lang']['datanotfound']."<b></td>
						</tr>";
			}

			$html .= "</tbody></table>";
		## TABLE DEBET
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