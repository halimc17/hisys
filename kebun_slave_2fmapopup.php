<?php
require_once('master_validation.php');
require_once('lib/zLib.php');

$proses   = checkPostGet('proses', '');
$pt       = checkPostGet('pt', '');
$region   = checkPostGet('region', '');
$kdorg    = checkPostGet('kdorg', '');
$prd      = checkPostGet('prd', '');
$iddesc   = checkPostGet('iddesc', '');
$head     = checkPostGet('head', '');

$arrbi    = explode('-',$prd); 
$tahun    = $arrbi[0]; 
$bulan    = $arrbi[1];
$periode1 = $tahun."-01";
$periode2 = $prd;

$periodelalu1 = ($tahun-1)."-01";
$periodelalu2 = ($tahun-1)."-".$bulan;

$rangebln = month_inbetween($periode1,$periode2);

if($region==''){exit("warning : Kode PT harus di pilih.");}

$whtbs=$whhrg=$where=$where2=$where_spb=$whereJ=$whtbs=$whrpkmat=$wheremat=$whtbsext=$whproses=$whtbsplasma=$wh=$wh2=$whB=$wh_spb=$wh_bgt=$wh_bgtrp=$whbgtproses='';
$listkebun = $listbbt = $listpks = $listkodeorg = [];

if($region!=''){
	$str = "select * from ".$dbname.".organisasi a left join ".$dbname.".bgt_regional_assignment b on a.kodeorganisasi=b.kodeunit where subregional='".$region."'";
}else{
	$str = "select * from ".$dbname.".organisasi where length(kodeorganisasi)='4' and namaorganisasi not like '%NON AKTI%'";
}
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['tipe']=='KEBUN'){			
		if($bar['inti']==0){			
			$kebunplasma[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
		}
		if($bar['inti']==1){
			$kebuninti[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
		}
		
		$listkebun[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	}
	if($bar['tipe']=='BIBITAN'){	
		$listbbt[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	}
	if($bar['tipe']=='PABRIK'){	
		$listpks[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	}
	$listkodeorg[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];		
	$listpt[$bar['induk']]=$bar['induk'];		
}	

$str = "select * from ".$dbname.".organisasi where induk in ('".implode("','",$listpt)."') and namaorganisasi not like '%NON AKTI%'";
$res = fetchdata($str);
foreach($res as $bar){
	$listunitjurnal[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
}


$whhrg      =" and a.kodeorg in ('".implode("','",$listkebun)."')";
$where      =" and (substr(a.kodeorg,1,4) in ('".implode("','",$listkebun)."') or substr(a.kodeorg,1,4) in ('".implode("','",$listbbt)."') or substr(a.kodeorg,1,4) in ('".implode("','",$listpks)."'))";
$where2     =" and substr(a.kodeblok,1,4) in ('".implode("','",$listkebun)."')";
$where_spb  =" and a.kodeorg in ('".implode("','",$listkebun)."')";
$whtbsext   =" and a.unit in ('".implode("','",$listkodeorg)."')";
$whtbsplasma=" and a.divisi in ('".implode("','",$listkebun)."')";
$whproses   =" and a.kodeorg in ('".implode("','",$listkodeorg)."')";
$whbgtproses=" and a.millcode in ('".implode("','",$listkodeorg)."')";
$whereJ     =" and a.kodeorg in ('".implode("','",$listunitjurnal)."')";
$whtbs      =" and a.supplierid in ('".implode("','",$listkebun)."')";
$whrpkmat   =" and substr(a.kodeorg,1,4) in ('".implode("','",$listkebun)."')";
$whhargatbs =" and pabrik in ('".implode("','",$listkodeorg)."')";	



#jika mau edit supaya lebih gampang komen ini
$hide = "hidden";

if($proses!='excel'){	
	$tab.="<table class=sortable cellspacing=1>";
}else{
	$tab.="<table border=1 class=sortable cellspacing=1>";
}
$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center style=background-color:green; rowspan='2' ".$hide.">Description</th>
            <th align=center style=background-color:green; rowspan='2'>Description</th>
            <th align=center style=background-color:green; rowspan='2'>UoM</th>
            <th align=center style=background-color:green; rowspan='1' colspan=3>This Month</th>
            <th align=center style=background-color:green; rowspan='1' colspan=3>Year To Date</th>
            <th align=center style=background-color:green; rowspan='2'>YTD SMLY</th>
            <th align=center style=background-color:green; rowspan='2'>Bgt This Year</th>
            <th align=center style=background-color:green; rowspan='2'>% TDTM  vs<br>TD SMLY</th>
            <th align=center style=background-color:green; rowspan='2'>% TDTM  vs<br>Budget TY</th>
			";
    $tab.="</tr>";
    $tab.="<tr class=rowheader>";
    $tab.="<th align=center style=background-color:green;>Actual</th>";
    $tab.="<th align=center style=background-color:green;>Budget</th>";
    $tab.="<th align=center style=background-color:green;>%Var</th>";
	$tab.="<th align=center style=background-color:green;>Actual</th>";
    $tab.="<th align=center style=background-color:green;>Budget</th>";
    $tab.="<th align=center style=background-color:green;>%Var</th>";
    $tab.="</tr>";
    $tab.="</thead>
 <tbody>";

		
if(!empty($_SESSION['fma'])){
	echo"<pre>";
	print_r($_SESSION['fma']);
	echo"</pre>";		
}else{
	$str="select * from ".$dbname.".kebun_2fma";
	$res = fetchdata($str);
	foreach($res as $bar){
		$descid[$bar['kode']]=$bar['kode'];
		$descnm[$bar['kode']]=$bar['desc'];
		$style[$bar['kode']]=$bar['style'];
		$sat[$bar['kode']]=$bar['sat'];
	}

	#ambil luas bgt
	$str = "select * from " . $dbname . ".bgt_blok a  where 1=1 ".$whB." ".$where2." and tahunbudget='".$tahun."' group by a.kodeblok"; 
	$res = fetchdata($str);
	foreach($res as $bar){
		$kodeorg = substr($bar['kodeblok'],0,4);
		if($bar['statusblok']=='TM'){
			if($bar['intiplasma']=='I'){			
				$data['tmi']['bgtbi'][$kodeorg]+=$bar['hathnini'];
				$data['tmi']['bgtsdbi'][$kodeorg]+=$bar['hathnini'];
				$data['tmi']['bgtthn'][$kodeorg]+=$bar['hathnini'];
				
				$data['ttlplanti']['bgtbi'][$kodeorg]+=$bar['hathnini'];
				$data['ttlplanti']['bgtsdbi'][$kodeorg]+=$bar['hathnini'];
				$data['ttlplanti']['bgtthn'][$kodeorg]+=$bar['hathnini'];
			}elseif($bar['intiplasma']=='P'){
				$data['tmp']['bgtbi'][$kodeorg]+=$bar['hathnini'];
				$data['tmp']['bgtsdbi'][$kodeorg]+=$bar['hathnini'];
				$data['tmp']['bgtthn'][$kodeorg]+=$bar['hathnini'];
				
				$data['ttlplantp']['bgtbi'][$kodeorg]+=$bar['hathnini'];
				$data['ttlplantp']['bgtsdbi'][$kodeorg]+=$bar['hathnini'];
				$data['ttlplantp']['bgtthn'][$kodeorg]+=$bar['hathnini'];
			}
		}elseif($bar['statusblok']=='TBM'){
			if($bar['intiplasma']=='I'){			
				$data['tbmi']['bgtbi'][$kodeorg]+=$bar['hathnini'];
				$data['tbmi']['bgtsdbi'][$kodeorg]+=$bar['hathnini'];
				$data['tbmi']['bgtthn'][$kodeorg]+=$bar['hathnini'];
				
				$data['ttlplanti']['bgtbi'][$kodeorg]+=$bar['hathnini'];
				$data['ttlplanti']['bgtsdbi'][$kodeorg]+=$bar['hathnini'];
				$data['ttlplanti']['bgtthn'][$kodeorg]+=$bar['hathnini'];
			}elseif($bar['intiplasma']=='P'){
				$data['tbmp']['bgtbi'][$kodeorg]+=$bar['hathnini'];
				$data['tbmp']['bgtsdbi'][$kodeorg]+=$bar['hathnini'];
				$data['tbmp']['bgtthn'][$kodeorg]+=$bar['hathnini'];
				
				$data['ttlplantp']['bgtbi'][$kodeorg]+=$bar['hathnini'];
				$data['ttlplantp']['bgtsdbi'][$kodeorg]+=$bar['hathnini'];
				$data['ttlplantp']['bgtthn'][$kodeorg]+=$bar['hathnini'];
			}
		}elseif($bar['statusblok']=='TB'){
			$data['tb']['bgtbi'][$kodeorg]+=$bar['hathnini'];
			$data['tb']['bgtsdbi'][$kodeorg]+=$bar['hathnini'];
			$data['tb']['bgtthn'][$kodeorg]+=$bar['hathnini'];
		}elseif($bar['statusblok']=='BBT'){
			$data['bbt']['bgtbi'][$kodeorg]+=$bar['pokokthnini'];
			$data['bbt']['bgtsdbi'][$kodeorg]+=$bar['pokokthnini'];
			$data['bbt']['bgtthn'][$kodeorg]+=$bar['pokokthnini'];
		}
	}
	
	

	#total ha estate
	$str = "select * from ".$dbname.".setup_blok a where 1=1 ".$wh2." ".$where." and a.luasareaproduktif>0 order by a.kodeorg";
	$res = fetchdata($str);
	foreach($res as $bar){
		$kodeorg = substr($bar['kodeblok'],0,4);
		if($bar['statusblok']=='TM'){
			if($bar['intiplasma']=='I'){			
				$data['tmi']['actbi'][$kodeorg]+=$bar['luasareaproduktif'];
				$data['tmi']['actsdbi'][$kodeorg]+=$bar['luasareaproduktif'];
				
				$data['ttlplanti']['actbi'][$kodeorg]+=$bar['luasareaproduktif'];
				$data['ttlplanti']['actsdbi'][$kodeorg]+=$bar['luasareaproduktif'];
			}elseif($bar['intiplasma']=='P'){
				$data['tmp']['actbi'][$kodeorg]+=$bar['luasareaproduktif'];
				$data['tmp']['actsdbi'][$kodeorg]+=$bar['luasareaproduktif'];
				
				$data['ttlplantp']['actbi'][$kodeorg]+=$bar['luasareaproduktif'];
				$data['ttlplantp']['actsdbi'][$kodeorg]+=$bar['luasareaproduktif'];
			}
		}elseif($bar['statusblok']=='TBM'){
			if($bar['intiplasma']=='I'){			
				$data['tbmi']['actbi'][$kodeorg]+=$bar['luasareaproduktif'];
				$data['tbmi']['actsdbi'][$kodeorg]+=$bar['luasareaproduktif'];
				
				$data['ttlplanti']['actbi'][$kodeorg]+=$bar['luasareaproduktif'];
				$data['ttlplanti']['actsdbi'][$kodeorg]+=$bar['luasareaproduktif'];
			}elseif($bar['intiplasma']=='P'){
				$data['tbmp']['actbi'][$kodeorg]+=$bar['luasareaproduktif'];
				$data['tbmp']['actsdbi'][$kodeorg]+=$bar['luasareaproduktif'];
				
				$data['ttlplantp']['actbi'][$kodeorg]+=$bar['luasareaproduktif'];
				$data['ttlplantp']['actsdbi'][$kodeorg]+=$bar['luasareaproduktif'];
			}
		}elseif($bar['statusblok']=='TB'){
				$data['tb']['actbi'][$kodeorg]+=$bar['luasareaproduktif'];
				$data['tb']['actsdbi'][$kodeorg]+=$bar['luasareaproduktif'];
		}
	}
	#total ha estate smly
	$str="select * from ".$dbname.".setup_blok_tahunan a where 1=1 ".$wh2." ".$where." and a.luasareaproduktif>0 and tahun='".($tahun-1).$bulan."' order by a.kodeorg";
	$res = fetchdata($str);
	foreach($res as $bar){
		$kodeorg = substr($bar['kodeblok'],0,4);
		if($bar['statusblok']=='TM'){
			if($bar['intiplasma']=='I'){			
				$data['tmi']['ytdsmly'][$kodeorg]+=$bar['luasareaproduktif'];
				
				$data['ttlplanti']['ytdsmly'][$kodeorg]+=$bar['luasareaproduktif'];
			}elseif($bar['intiplasma']=='P'){
				$data['tmp']['ytdsmly'][$kodeorg]+=$bar['luasareaproduktif'];
				
				$data['ttlplantp']['ytdsmly'][$kodeorg]+=$bar['luasareaproduktif'];
			}
		}elseif($bar['statusblok']=='TBM'){
			if($bar['intiplasma']=='I'){			
				$data['tbmi']['ytdsmly'][$kodeorg]+=$bar['luasareaproduktif'];
				
				$data['ttlplanti']['ytdsmly'][$kodeorg]+=$bar['luasareaproduktif'];
			}elseif($bar['intiplasma']=='P'){
				$data['tbmp']['ytdsmly'][$kodeorg]+=$bar['luasareaproduktif'];
				
				$data['ttlplantp']['ytdsmly'][$kodeorg]+=$bar['luasareaproduktif'];
			}
		}elseif($bar['statusblok']=='TB'){
				$data['tb']['ytdsmly'][$kodeorg]+=$bar['luasareaproduktif'];
		}
	}	


	#ambil prd real
	$prdton=$prdtontitle=array();
	$str = "select sum(kgwb) as kgwb, substr(tanggal,1,7) as periode,substr(tanggal,1,4) as tahun,intiplasma,kodeorg from " . $dbname . ".kebun_spb_vw a  where 1=1 ".$wh_spb." ".$where_spb." and (substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' or substr(tanggal,1,7) between '".$periodelalu1."' and  '".$periodelalu2."') group by kodeorg, periode,intiplasma"; 
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['tahun']==$tahun){
		#tahun ini
			if($bar['periode']==$prd){
				if($bar['intiplasma']=='P'){
					$data['tbsp']['actbi'][$bar['kodeorg']]+=$bar['kgwb']/1000;
				}else{			
					$data['tbsi']['actbi'][$bar['kodeorg']]+=$bar['kgwb']/1000;
				}
				$data['ttlffb']['actbi'][$bar['kodeorg']]+=$bar['kgwb']/1000;		
			}
			if($bar['intiplasma']=='P'){
				$data['tbsp']['actsdbi'][$bar['kodeorg']]+=$bar['kgwb']/1000;
			}else{		
				$data['tbsi']['actsdbi'][$bar['kodeorg']]+=$bar['kgwb']/1000;
			}
			$data['ttlffb']['actsdbi'][$bar['kodeorg']]+=$bar['kgwb']/1000;		
		}else{
		#tahun lalu
			if($bar['intiplasma']=='P'){
				$data['tbsp']['ytdsmly'][$bar['kodeorg']]+=$bar['kgwb']/1000;
			}else{		
				$data['tbsi']['ytdsmly'][$bar['kodeorg']]+=$bar['kgwb']/1000;
			}
			$data['ttlffb']['ytdsmly'][$bar['kodeorg']]+=$bar['kgwb']/1000;		
		}
	}

	#TBS External
	$str = "select a.*,substr(tanggalpks,1,7) as periode,substr(tanggalpks,1,4) as tahun from " . $dbname . ".kebun_tbsexternal a  where 1=1 ".$whtbsext." and (substr(tanggalpks,1,7) between '".$periode1."' and  '".$periode2."' or substr(tanggalpks,1,7) between '".$periodelalu1."' and  '".$periodelalu2."')"; 
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['tahun']==$tahun){		
			if($bar['periode']==$prd){
				$data['tbso']['actbi']+=$bar['kgbruto']/1000;
				$data['ttlffb']['actbi']+=$bar['kgbruto']/1000;
			}
			$data['tbso']['actsdbi']+=$bar['kgbruto']/1000;
			$data['ttlffb']['actsdbi']+=$bar['kgbruto']/1000;
		}else{
			$data['tbso']['ytdsmly']+=$bar['kgbruto']/1000;
			$data['ttlffb']['ytdsmly']+=$bar['kgbruto']/1000;
		}
	}

	#ambil prd bgt
	$e="(";$n="(";$cp="(";$kr="(";
	for($i=1;$i<=intval($bulan);$i++){
		$r="kg".addZero($i,2);$s="olah".addZero($i,2);
		$t="kgcpo".addZero($i,2);$u="kgker".addZero($i,2);
		if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
		if($i<intval($bulan)){$n.=$s."+";}else{$n.=$s;}
		if($i<intval($bulan)){$cp.=$t."+";}else{$cp.=$t;}
		if($i<intval($bulan)){$kr.=$u."+";}else{$kr.=$u;}
	}
	$e.=")";$n.=")";$cp.=")";$kr.=")";

	$str=" select intiplasma,kodeblok,".$e." as sdbi,kg".$bulan." as bi,kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." and tahunbudget = '".$tahun."'";
	$res = fetchdata($str);
	foreach($res as $bar){	
		if($bar['intiplasma']=='I'){		
			$data['tbsi']['bgtbi']+=$bar['bi']/1000;
			$data['tbsi']['bgtsdbi']+=$bar['sdbi']/1000;
			$data['tbsi']['bgtthn']+=$bar['kgsetahun']/1000;
		}elseif($bar['intiplasma']=='P'){
			#jika tahun 2021 jangan ambil dari sini tapi ambil dari budget pks
			if($tahun>'2021'){			
				$data['tbsp']['bgtbi']+=$bar['bi']/1000;
				$data['tbsp']['bgtsdbi']+=$bar['sdbi']/1000;
				$data['tbsp']['bgtthn']+=$bar['kgsetahun']/1000;
			}
		}
		$data['ttlffb']['bgtbi']+=$bar['bi']/1000;
		$data['ttlffb']['bgtsdbi']+=$bar['sdbi']/1000;
		$data['ttlffb']['bgtthn']+=$bar['kgsetahun']/1000;
		
		$data['ttlffbip']['bgtbi']+=$bar['bi']/1000;
		$data['ttlffbip']['bgtsdbi']+=$bar['sdbi']/1000;
		$data['ttlffbip']['bgtthn']+=$bar['kgsetahun']/1000;
		
		// $data['ffbp']['bgtbi']+=$bar['bi']/1000;
		// $data['ffbp']['bgtsdbi']+=$bar['sdbi']/1000;
		// $data['ffbp']['bgtthn']+=$bar['kgsetahun']/1000;
	}

	$str="select a.*,substr(tanggal,1,7) as periode,substr(tanggal,1,4) as tahun from ".$dbname.".pabrik_produksi a where 1=1 and (substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' or substr(tanggal,1,7) between '".$periodelalu1."' and  '".$periodelalu2."') ".$whproses."";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['tahun']==$tahun){		
			if($bar['periode']==$prd){
				$data['ffbp']['actbi']+=$bar['tbsdiolah']/1000;
				$data['ttlcpo']['actbi']+=$bar['oer']/1000;
				$data['ttlpk']['actbi']+=$bar['oerpk']/1000;
			}
			$data['ffbp']['actsdbi']+=$bar['tbsdiolah']/1000;
			$data['ttlcpo']['actsdbi']+=$bar['oer']/1000;
			$data['ttlpk']['actsdbi']+=$bar['oerpk']/1000;
		}else{
			$data['ffbp']['ytdsmly']+=$bar['tbsdiolah']/1000;
			$data['ttlcpo']['ytdsmly']+=$bar['oer']/1000;
			$data['ttlpk']['ytdsmly']+=$bar['oerpk']/1000;
		}
	}

	$hargabgt=$no=0;
	$str = "select * from " . $dbname . ".bgt_hargatbs a where 1=1 ".$whhrg." and tahun = '".$tahun."'"; 
	$res = fetchdata($str);
	foreach($res as $bar){
		$no+=1;
		$hargabgt+=$bar['rupiah'];
	}
	$hargabgt=$hargabgt/$no;

	$str = " select millcode,kodeunit,".$n." as sdbi,olah".$bulan." as bi,kgolah,kgcpo,".$cp." as cposdbi,kgcpo".$bulan." as cpobi,kgkernel,".$kr." as kersdbi,kgker".$bulan." as kerbi from ".$dbname.".bgt_produksi_pks_vw a where 1=1 ".$whbgtproses." and tahunbudget = '".$tahun."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['kodeunit']=='tbsexternal'){
			$data['tbso']['bgtbi']+=$bar['bi']/1000;
			$data['tbso']['bgtsdbi']+=$bar['sdbi']/1000;
			$data['tbso']['bgtthn']+=$bar['kgolah']/1000;
			
			$data['belitbs']['bgtbi']+=($bar['bi']*$hargabgt)/1000;
			$data['belitbs']['bgtsdbi']+=($bar['sdbi']*$hargabgt)/1000;
			$data['belitbs']['bgtthn']+=($bar['kgolah']*$hargabgt)/1000;
		}elseif(in_array($bar['kodeunit'],$kebunplasma)){
			#jika tahun 2021 ambil dari budget pks
			if($tahun<='2021'){			
				$data['tbsp']['bgtbi']+=$bar['bi']/1000;
				$data['tbsp']['bgtsdbi']+=$bar['sdbi']/1000;
				$data['tbsp']['bgtthn']+=$bar['kgolah']/1000;
			}
			
			$data['belitbs']['bgtbi']+=($bar['bi']*$hargabgt)/1000;
			$data['belitbs']['bgtsdbi']+=($bar['sdbi']*$hargabgt)/1000;
			$data['belitbs']['bgtthn']+=($bar['kgolah']*$hargabgt)/1000;
		}elseif(in_array($bar['kodeunit'],$kebuninti)){		
		}
		
		$data['ffbp']['bgtbi']+=$bar['bi']/1000;
		$data['ffbp']['bgtsdbi']+=$bar['sdbi']/1000;
		$data['ffbp']['bgtthn']+=$bar['kgolah']/1000;
		
		$data['ttlcpo']['bgtbi']+=$bar['cpobi']/1000;
		$data['ttlcpo']['bgtsdbi']+=$bar['cposdbi']/1000;
		$data['ttlcpo']['bgtthn']+=$bar['kgcpo']/1000;
		
		$data['ttlpk']['bgtbi']+=$bar['kerbi']/1000;
		$data['ttlpk']['bgtsdbi']+=$bar['kersdbi']/1000;
		$data['ttlpk']['bgtthn']+=$bar['kgkernel']/1000;
	}



	$whakunumum  =" and noakun like '7%'";
	// $whakunumum .=" and noakun not like '715%'";
	$str = "select sum(jumlah) as jumlah, periode, substr(periode,1,4) as tahun,a.kodeorg as kodeorg from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 ".$whereJ." and (periode between '".$periode1."' and  '".$periode2."' or periode between '".$periodelalu1."' and  '".$periodelalu2."') ".$whakunumum." group by periode,kodeorg"; 
	$res = fetchdata($str);
	foreach($res as $bar){
		$tipeOrg = makeOption($dbname,"organisasi","kodeorganisasi,tipe","kodeorganisasi='".$bar['kodeorg']."'");
		if($bar['tahun']==$tahun){		
			if($bar['periode']==$prd){
				if($tipeOrg[$bar['kodeorg']]=='PABRIK'){
					$data['prosescost']['actbi']+=$bar['jumlah']/1000;
				}else{
					$data['gc']['actbi']+=$bar['jumlah']/1000;
				}
			}
			if($tipeOrg[$bar['kodeorg']]=='PABRIK'){
				$data['prosescost']['actsdbi']+=$bar['jumlah']/1000;
			}else{
				$data['gc']['actsdbi']+=$bar['jumlah']/1000;
			}
		}else{
			if($tipeOrg[$bar['kodeorg']]=='PABRIK'){
				$data['prosescost']['ytdsmly']+=$bar['jumlah']/1000;
			}else{
				$data['gc']['ytdsmly']+=$bar['jumlah']/1000;
			}
		}
	}

	$e="("; $s="(";
	for($i=1;$i<=intval($bulan);$i++){
		$r="rp".addZero($i,2);$n="fis".addZero($i,2);
		if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
	}
	$e.=")"; $s.=")";

	$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";
	#ini khusus budget UMUM
	$str=" select tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." and kodebudget='UMUM' and tahunbudget = '".$tahun."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$data['gc']['bgtbi']+=$bar['bi']/1000;
		$data['gc']['bgtsdbi']+=$bar['sdbi']/1000;
		$data['gc']['bgtthn']+=$bar['rupiah']/1000;
	}

	$whakun    = " and (substr(noakun,1,3) in ('611','621','631','632','126','128') or substr(noakun,1,5) in('64101'))";
	$whakunrev = " and noakun in('5110101','5110201','5115101')";
	$akunppk   = array('6210103');
	$akuntrans = array('6110103','6110104');
	$akunbltbs = array('6410101','6410102','6410103');
	$akunrevcpo= array('5110101');
	$akunrevpk = array('5110201');
	$akunklaim = array('5115101');

	$kdbrgcpo  = array('40000001');
	$kdbrgpk   = array('40000002');

	# biaya tahun ini
	$str = "select substr(noakun,1,3) as akun, noakun, sum(jumlah) as jumlah, periode,substr(periode,1,4) as tahun,tahuntanam from " . $dbname . ".keu_jurnaldt_vw a left join ".$dbname.".setup_blok b on a.kodeblok=b.kodeorg where 1=1 ".$whereJ." and (periode between '".$periode1."' and  '".$periode2."' or periode between '".$periodelalu1."' and  '".$periodelalu2."') ".$whakun."   group by periode,noakun,tahuntanam"; 
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['akun']=='621'){
			if(in_array($bar['noakun'],$akunppk)){			
				if($bar['tahun']==$tahun){		
					if($bar['periode']==$prd){
						$data['ppk']['actbi']+=$bar['jumlah']/1000;
					}
					$data['ppk']['actsdbi']+=$bar['jumlah']/1000;
				}else{
					$data['ppk']['ytdsmly']+=$bar['jumlah']/1000;
				}
			}else{
				if($bar['tahun']==$tahun){		
					if($bar['periode']==$prd){
						$data['tmc']['actbi']+=$bar['jumlah']/1000;
					}
					$data['tmc']['actsdbi']+=$bar['jumlah']/1000;
				}else{
					$data['tmc']['ytdsmly']+=$bar['jumlah']/1000;
				}
			}
		}else if($bar['akun']=='611'){
			if(in_array($bar['noakun'],$akuntrans)){			
				if($bar['tahun']==$tahun){		
					if($bar['periode']==$prd){
						$data['atbs']['actbi']+=$bar['jumlah']/1000;
					}
					$data['atbs']['actsdbi']+=$bar['jumlah']/1000;
				}else{
					$data['atbs']['ytdsmly']+=$bar['jumlah']/1000;
				}
			}else{
				if($bar['tahun']==$tahun){		
					if($bar['periode']==$prd){
						$data['pnn']['actbi']+=$bar['jumlah']/1000;
					}
					$data['pnn']['actsdbi']+=$bar['jumlah']/1000;
				}else{
					$data['pnn']['ytdsmly']+=$bar['jumlah']/1000;
				}
			}
		}else if(substr($bar['akun'],0,2)=='63'){
			if($bar['tahun']==$tahun){	
				if($bar['periode']==$prd){
					$data['prosescost']['actbi']+=$bar['jumlah']/1000;
				}
				$data['prosescost']['actsdbi']+=$bar['jumlah']/1000;
			}else{
				$data['prosescost']['ytdsmly']+=$bar['jumlah']/1000;
			}
		}else if($bar['akun']=='126'){
			if(($tahun-1)==$bar['tahuntanam']){
				if($bar['tahun']==$tahun){
					if($bar['periode']==$prd){
						$data['tbm1']['actbi']+=$bar['jumlah']/1000;
					}
					$data['tbm1']['actsdbi']+=$bar['jumlah']/1000;
				}else{
					$data['tbm1']['ytdsmly']+=$bar['jumlah']/1000;
				}
			}elseif(($tahun-2)==$bar['tahuntanam']){
				if($bar['tahun']==$tahun){
					if($bar['periode']==$prd){
						$data['tbm2']['actbi']+=$bar['jumlah']/1000;
					}
					$data['tbm2']['actsdbi']+=$bar['jumlah']/1000;
				}else{
					$data['tbm2']['ytdsmly']+=$bar['jumlah']/1000;
				}
			}else{
				if($bar['tahun']==$tahun){
					if($bar['periode']==$prd){
						$data['tbm3']['actbi']+=$bar['jumlah']/1000;
					}
					$data['tbm3']['actsdbi']+=$bar['jumlah']/1000;
				}else{
					$data['tbm3']['ytdsmly']+=$bar['jumlah']/1000;
				}
			}
		}else if($bar['akun']=='128'){ #BIBITAN
			if($bar['tahun']==$tahun){
				if($bar['periode']==$prd){
					$data['nurs']['actbi']+=$bar['jumlah']/1000;
				}
				$data['nurs']['actsdbi']+=$bar['jumlah']/1000;
			}else{
				$data['nurs']['ytdsmly']+=$bar['jumlah']/1000;
			}
		}else if($bar['akun']=='641'){ #BELI TBS
			if($bar['tahun']==$tahun){
				if($bar['periode']==$prd){
					$data['belitbs']['actbi']+=$bar['jumlah']/1000;
				}
				$data['belitbs']['actsdbi']+=$bar['jumlah']/1000;
			}else{
				$data['belitbs']['ytdsmly']+=$bar['jumlah']/1000;
			}
		}
	}

	$str = "select substr(periode,1,4) as tahun, sum(jumlah) as jumlah,periode,kodebarang,noakun from " . $dbname . ".keu_jurnaldt_vw a where 1=1 ".$whereJ." and (periode between '".$periode1."' and  '".$periode2."' or periode between '".$periodelalu1."' and  '".$periodelalu2."') ".$whakunrev." group by noakun, periode, kodebarang"; 
	$res = fetchdata($str);
	foreach($res as $bar){
		#akun revenue
		$bar['jumlah']=$bar['jumlah']*(-1);
		if(in_array($bar['noakun'],$akunrevcpo)){			
			if($bar['tahun']==$tahun){		
				if($bar['periode']==$prd){
					$data['revenuecpo']['actbi']+=$bar['jumlah']/1000;
				}
				$data['revenuecpo']['actsdbi']+=$bar['jumlah']/1000;
			}else{
				$data['revenuecpo']['ytdsmly']+=$bar['jumlah']/1000;
			}
		}elseif(in_array($bar['noakun'],$akunrevpk)){
			if($bar['tahun']==$tahun){		
				if($bar['periode']==$prd){
					$data['revenuepk']['actbi']+=$bar['jumlah']/1000;
				}
				$data['revenuepk']['actsdbi']+=$bar['jumlah']/1000;
			}else{
				$data['revenuepk']['ytdsmly']+=$bar['jumlah']/1000;
			}
		}elseif(in_array($bar['noakun'],$akunklaim)){
			if(in_array($bar['kodebarang'],$kdbrgcpo)){
				if($bar['tahun']==$tahun){		
					if($bar['periode']==$prd){
						$data['revenuecpo']['actbi']+=$bar['jumlah']/1000;
					}
					$data['revenuecpo']['actsdbi']+=$bar['jumlah']/1000;
				}else{
					$data['revenuecpo']['ytdsmly']+=$bar['jumlah']/1000;
				}
			}else{			
				if($bar['tahun']==$tahun){		
					if($bar['periode']==$prd){
						$data['revenuepk']['actbi']+=$bar['jumlah']/1000;
					}
					$data['revenuepk']['actsdbi']+=$bar['jumlah']/1000;
				}else{
					$data['revenuepk']['ytdsmly']+=$bar['jumlah']/1000;
				}
			}
		}
	}


	#ini khusus budget kebun
	$str=" select kodebarang,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,a.tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun,thntnm from ".$dbname.".bgt_budget_detail a left join ".$dbname.".bgt_blok b on a.kodeorg=b.kodeblok and a.tahunbudget=b.tahunbudget where 1=1 ".$where." and kodebudget!='UMUM' and a.tahunbudget = '".$tahun."' ".$whakun."";
	$res = fetchdata($str);
	foreach($res as $bar){
		if(substr($bar['noakun'],0,3)=='621'){
			if(in_array($bar['noakun'],$akunppk)){			
				$data['ppk']['bgtbi']+=$bar['bi']/1000;
				$data['ppk']['bgtsdbi']+=$bar['sdbi']/1000;
				$data['ppk']['bgtthn']+=$bar['rupiah']/1000;
			}else{
				$data['tmc']['bgtbi']+=$bar['bi']/1000;
				$data['tmc']['bgtsdbi']+=$bar['sdbi']/1000;
				$data['tmc']['bgtthn']+=$bar['rupiah']/1000;
			}
		}else if(substr($bar['noakun'],0,3)=='611'){
			if(in_array($bar['noakun'],$akuntrans)){
				$data['atbs']['bgtbi']+=$bar['bi']/1000;
				$data['atbs']['bgtsdbi']+=$bar['sdbi']/1000;
				$data['atbs']['bgtthn']+=$bar['rupiah']/1000;
			}else{
				$data['pnn']['bgtbi']+=$bar['bi']/1000;
				$data['pnn']['bgtsdbi']+=$bar['sdbi']/1000;
				$data['pnn']['bgtthn']+=$bar['rupiah']/1000;
			}
		}else if(substr($bar['noakun'],0,3)=='631'){
			$data['prosescost']['bgtbi']+=$bar['bi']/1000;
			$data['prosescost']['bgtsdbi']+=$bar['sdbi']/1000;
			$data['prosescost']['bgtthn']+=$bar['rupiah']/1000;
		}else if(substr($bar['noakun'],0,3)=='126'){
			if(($tahun-1)==$bar['thntnm']){
				$data['tbm1']['bgtbi']+=$bar['bi']/1000;
				$data['tbm1']['bgtsdbi']+=$bar['sdbi']/1000;
				$data['tbm1']['bgtthn']+=$bar['rupiah']/1000;			
			}elseif(($tahun-2)==$bar['thntnm']){
				$data['tbm2']['bgtbi']+=$bar['bi']/1000;
				$data['tbm2']['bgtsdbi']+=$bar['sdbi']/1000;
				$data['tbm2']['bgtthn']+=$bar['rupiah']/1000;			
			}else{
				$data['tbm3']['bgtbi']+=$bar['bi']/1000;
				$data['tbm3']['bgtsdbi']+=$bar['sdbi']/1000;
				$data['tbm3']['bgtthn']+=$bar['rupiah']/1000;			
			}
		}else if(substr($bar['noakun'],0,3)=='128'){
			$data['nurs']['bgtbi']+=$bar['bi']/1000;
			$data['nurs']['bgtsdbi']+=$bar['sdbi']/1000;
			$data['nurs']['bgtthn']+=$bar['rupiah']/1000;
		}
	}

	#budget harga cpo, pk, tbs
	$str = "select * from ".$dbname.".bgt_hargatbs where 1=1 ".$whhargatbs." and tahun = '".$tahun."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['kodebarang']=='CPO'){
			$hargacpo=$bar['rupiah'];
		}
		if($bar['kodebarang']=='KER'){
			$hargaker=$bar['rupiah'];
		}
	}

	$_SESSION['fma']=$data;
	echo"<pre>";
	print_r($_SESSION['fma']);
	exit();


	$arrheader=array('actbi','bgtbi','varbi','actsdbi','bgtsdbi','varsdbi','ytdsmly','bgtthn','tdtmsmly','tdtmbthn');
	foreach($descid as $iddesc){
		$tab.="<tr class=rowcontent ".$style[$iddesc].">";
		$tab.="<td ".$style[$iddesc]." ".$hide.">".$iddesc."</td>";
		$tab.="<td ".$style[$iddesc].">".$descnm[$iddesc]."</td>";
		$tab.="<td align=center ".$style[$iddesc].">".$sat[$iddesc]."</td>";
		foreach($arrheader as $head){
			if($data['tmp']['bgtbi']==0){
				$data['tmp']['bgtbi']=$data['tmp']['actbi'];
				$data['ttlplantp']['bgtbi']=$data['ttlplantp']['actbi'];
			}
			if($data['tmp']['bgtsdbi']==0){
				$data['tmp']['bgtsdbi']=$data['tmp']['actsdbi'];
				$data['ttlplantp']['bgtsdbi']=$data['ttlplantp']['actsdbi'];
			}
			if($data['tmp']['bgtthn']==0){
				$data['tmp']['bgtthn']=$data['tmp']['actsdbi'];
				$data['ttlplantp']['bgtthn']=$data['ttlplantp']['actsdbi'];
			}
			
			$data['uc'][$head]=$data['tmc'][$head]+$data['ppk'][$head];
			$data['coll'][$head]=$data['pnn'][$head]+$data['atbs'][$head];
			@$data['gcperha'][$head]=$data['gc'][$head]/$data['ttlplanti'][$head];
			
			if(isset($data['tmi'][$head])){
				@$data['yieldi'][$head]=$data['tbsi'][$head]/$data['tmi'][$head];
				@$data['cpo'][$head]=$data['ttlcpo'][$head]/$data['tmi'][$head];
				@$data['pk'][$head]=$data['ttlpk'][$head]/$data['tmi'][$head];
				@$data['tmcperha'][$head]=$data['tmc'][$head]/$data['tmi'][$head];
				@$data['ppkperha'][$head]=$data['ppk'][$head]/$data['tmi'][$head];
				@$data['ucperha'][$head]=$data['uc'][$head]/$data['tmi'][$head];
				
				@$data['cpoperha'][$head]=$data['ttlcpo'][$head]/($data['tmi'][$head]+$data['tmp'][$head]);
				@$data['pkperha'][$head]=$data['ttlpk'][$head]/($data['tmi'][$head]+$data['tmp'][$head]);
				@$data['ppperha'][$head]=($data['ttlcpo'][$head]+$data['ttlpk'][$head])/($data['tmi'][$head]+$data['tmp'][$head]);
				@$data['tbsperha'][$head]=$data['tbsi'][$head]/$data['tmi'][$head];
			}
			if(isset($data['tbsi'][$head]) or isset($data['tbsp'][$head])){
				@$data['tmcperkg'][$head]=$data['tmc'][$head]/$data['tbsi'][$head];
				@$data['ppkperkg'][$head]=$data['ppk'][$head]/$data['tbsi'][$head];
				@$data['gcperkg'][$head]=$data['gc'][$head]/($data['tbsi'][$head]);//($data['tbsi'][$head]+$data['tbsp'][$head])
				@$data['ucperkg'][$head]=$data['uc'][$head]/($data['tbsi'][$head]);//($data['tbsi'][$head]+$data['tbsp'][$head])
				@$data['pnnperkg'][$head]=$data['pnn'][$head]/($data['tbsi'][$head]);//($data['tbsi'][$head]+$data['tbsp'][$head])
				@$data['atbsperkg'][$head]=$data['atbs'][$head]/($data['tbsi'][$head]);//($data['tbsi'][$head]+$data['tbsp'][$head])
				@$data['collperkg'][$head]=$data['coll'][$head]/($data['tbsi'][$head]);//($data['tbsi'][$head]+$data['tbsp'][$head])
			}
			if(isset($data['tbmi'][$head])){
				@$data['tbm1perha'][$head]=$data['tbm1'][$head]/$data['tbmi'][$head];
				@$data['tbm2perha'][$head]=$data['tbm2'][$head]/$data['tbmi'][$head];
				@$data['tbm3perha'][$head]=$data['tbm3'][$head]/$data['tbmi'][$head];
			}
			if(isset($data['bbt'][$head])){
				@$data['nursperpkk'][$head]=$data['nurs'][$head]/$data['bbt'][$head];
			}
			$data['dcost'][$head]=$data['coll'][$head]+$data['uc'][$head]+$data['gc'][$head]+$data['belitbs'][$head]+$data['prosescost'][$head];
			@$data['dcosti'][$head]=$data['coll'][$head]+$data['uc'][$head]+$data['gc'][$head]+(@$data['prosescost'][$head]*(@$data['tbsi'][$head]/@$data['ttlffb'][$head]));
			@$data['dcostp'][$head]=$data['belitbs'][$head]+(@$data['prosescost'][$head]*(@($data['tbsp'][$head]+$data['tbso'][$head])/@$data['ttlffb'][$head]));
			
			@$data['belitbsperkg'][$head]=$data['belitbs'][$head]/($data['tbsp'][$head]+$data['tbso'][$head]);
			if(isset($data['ttlcpo'][$head])){
				@$data['prosescostpercpo'][$head]=$data['prosescost'][$head]/$data['ttlcpo'][$head];
				@$data['dcostpercpo'][$head]=$data['dcost'][$head]/$data['ttlcpo'][$head];
			}
			
			
			$data['dcostest'][$head]=$data['coll'][$head]+$data['uc'][$head]+$data['gc'][$head];
			if(isset($data['ttlplanti'][$head])){
				@$data['dcostestperha'][$head]=$data['dcostest'][$head]/$data['ttlplanti'][$head];			
			}			
			if(isset($data['tbsi'][$head])){			
				@$data['dcostestperkg'][$head]=$data['dcostest'][$head]/$data['tbsi'][$head];
				@$data['dcostipertbs'][$head]=$data['dcosti'][$head]/$data['tbsi'][$head];
			}
			if(isset($data['tbsp'][$head])){			
				@$data['dcostppertbs'][$head]=$data['dcostp'][$head]/$data['tbsp'][$head];
			}
			
			if(isset($data['tmp'][$head])){
				@$data['yieldp'][$head]=$data['tbsp'][$head]/$data['tmp'][$head];
			}
			$data['ttlpp'][$head]=$data['ttlpk'][$head]+$data['ttlcpo'][$head];
			if(isset($data['ffbp'][$head])){
				@$data['prosescostpertbs'][$head]=$data['prosescost'][$head]/($data['ffbp'][$head]);
				@$data['oer'][$head]=$data['cpo'][$head]*$data['tmi'][$head]/$data['ffbp'][$head]*100;
				@$data['ker'][$head]=$data['pk'][$head]*$data['tmi'][$head]/$data['ffbp'][$head]*100;
			}
			$data['cpoi'][$head]=$data['oer'][$head]*$data['tbsi'][$head]/100;
			$data['cpop'][$head]=$data['oer'][$head]*$data['tbsp'][$head]/100;
			$data['cpoo'][$head]=$data['oer'][$head]*$data['tbso'][$head]/100;
			$data['pki'][$head]=$data['ker'][$head]*$data['tbsi'][$head]/100;
			$data['pkp'][$head]=$data['ker'][$head]*$data['tbsp'][$head]/100;
			$data['pko'][$head]=$data['ker'][$head]*$data['tbso'][$head]/100;
			
			@$data['dcostipercpo'][$head]=$data['dcosti'][$head]/$data['cpoi'][$head];
			@$data['dcostppercpo'][$head]=$data['dcostp'][$head]/$data['cpop'][$head];
			
			
			$data['revenuecpo']['bgtbi']=$data['ttlcpo']['bgtbi']*$hargacpo;
			$data['revenuecpo']['bgtsdbi']=$data['ttlcpo']['bgtsdbi']*$hargacpo;
			$data['revenuecpo']['bgtthn']=$data['ttlcpo']['bgtthn']*$hargacpo;
			
			$data['revenuepk']['bgtbi']=$data['ttlpk']['bgtbi']*$hargaker;
			$data['revenuepk']['bgtsdbi']=$data['ttlpk']['bgtsdbi']*$hargaker;
			$data['revenuepk']['bgtthn']=$data['ttlpk']['bgtthn']*$hargaker;
			
			$data['revenuettl'][$head]=$data['revenuecpo'][$head]+$data['revenuepk'][$head];
			
			@$data['revenueicpo'][$head]=$data['revenuecpo'][$head]*($data['cpoi'][$head]/@$data['ttlcpo'][$head]);
			@$data['revenueipk'][$head]=$data['revenuepk'][$head]*($data['pki'][$head]/@$data['ttlpk'][$head]);
			@$data['revenueittl'][$head]=$data['revenueipk'][$head]+$data['revenueicpo'][$head];

			@$data['revenuepcpo'][$head]=$data['revenuecpo'][$head]*(($data['cpop'][$head]+$data['cpoo'][$head])/$data['ttlcpo'][$head]);
			@$data['revenueppk'][$head]=$data['revenuepk'][$head]*(($data['pkp'][$head]+$data['pko'][$head])/$data['ttlpk'][$head]);
			@$data['revenuepttl'][$head]=$data['revenueppk'][$head]+$data['revenuepcpo'][$head];
			
			
			
			$data['gpm'][$head]=$data['revenuettl'][$head]-$data['dcost'][$head];
			$data['gpmi'][$head]=$data['revenueittl'][$head]-$data['dcosti'][$head];
			$data['gpmp'][$head]=$data['revenuepttl'][$head]-$data['dcostp'][$head];
			if(isset($data['revenuettl'][$head])){			
				@$data['mrgn'][$head]=$data['gpm'][$head]/$data['revenuettl'][$head]*100;
			}
			if(isset($data['revenueittl'][$head])){			
				@$data['mrgni'][$head]=$data['gpmi'][$head]/$data['revenueittl'][$head]*100;
			}
			if(isset($data['revenuepttl'][$head])){			
				@$data['mrgnp'][$head]=$data['gpmp'][$head]/$data['revenuepttl'][$head]*100;
			}
			if(isset($data['ttlffb'][$head])){			
				@$data['dcostpertbs'][$head]=$data['dcost'][$head]/($data['ttlffb'][$head]);
				@$data['gpmpertbs'][$head]=$data['gpm'][$head]/$data['ttlffb'][$head];
			}
			if(isset($data['ttlcpo'][$head])){			
				@$data['gpmpercpo'][$head]=$data['gpm'][$head]/$data['ttlcpo'][$head];
			}
			
			
			#% var bi
			if(isset($data[$iddesc]['bgtbi'])){			
				@$data[$iddesc]['varbi']=($data[$iddesc]['actbi']-$data[$iddesc]['bgtbi'])/$data[$iddesc]['bgtbi']*100;
			}
			#% var sdbi
			if(isset($data[$iddesc]['bgtsdbi'])){
				@$data[$iddesc]['varsdbi']=($data[$iddesc]['actsdbi']-$data[$iddesc]['bgtsdbi'])/$data[$iddesc]['bgtsdbi']*100;
			}
			#% TDTM Vs TD SMLY
			if(isset($data[$iddesc]['ytdsmly'])){			
				@$data[$iddesc]['tdtmsmly']=$data[$iddesc]['actsdbi']/$data[$iddesc]['ytdsmly']*100;
			}
			#% TDTM Vs Budget TY
			if(isset($data[$iddesc]['bgtthn'])){			
				@$data[$iddesc]['tdtmbthn']=$data[$iddesc]['actsdbi']/$data[$iddesc]['bgtthn']*100;
			}
			
		}
	}	
	$_SESSION['fma']=$data;
}

$tab.="</tbody></table>";


switch ($proses) {
    case 'preview':
        echo $tab;
	break;

    case 'excel':
        $nop_ = "fma";
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

function numb_format($a,$d=0){
	#$n = number_format($a,$d);
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n=='' or $n=='inf' or is_nan($a)){
		$n="";
	}else{
		$n=$n;
	}
	
	return $n;
}
?>