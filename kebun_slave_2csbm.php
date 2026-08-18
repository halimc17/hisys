<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$proses   = checkPostGet('proses', '');
$kdorg    = checkPostGet('kdorg', '');
$pt       = checkPostGet('pt', '');
$tt       = checkPostGet('tt', '');
$ip       = checkPostGet('ip', '');
$divisi   = checkPostGet('divisi', '');
$prd      = checkPostGet('prd', '');
$tipe     = checkPostGet('tipe', '');
$kolomhide= checkPostGet('kolomhide', '');
$barishide= checkPostGet('barishide', '');
$jenis    = checkPostGet('jenis', '');
$status   = checkPostGet('status', '');
$prod     = checkPostGet('prod', '');

$arrbi    = explode('-',$prd); 
$tahun    = $arrbi[0]; 
$bulan    = $arrbi[1];
$periode1 = $tahun."-01";
$periode2 = $tahun."-12";
$periode2 = $prd;

$periodelalu1       = ($tahun-1)."-01";
$periodelalu2       = ($tahun-1)."-".$bulan;
$periodelalusetahun2= ($tahun-1)."-12";

$arrbd   = explode('-',periodeberikut($prd)); 
$tahundpn= $arrbd[0]; 
$bulandpn= $arrbd[1];


$rangebln = month_inbetween($periode1,$periode2);

if($pt==''){exit("warning : Kode PT harus di pilih.");}
if($kolomhide=='1'){	
	$style="";
}else{	
	$style="style=display:none";
}

$whtbs=$whhrg="";
if($pt!=''){
	$whhrg=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
}
if($kdorg!=''){
	$whhrg=" and a.kodeorg ='".$kdorg."'";
}

$where=$where2=$where_spb=$whereJ=$whtbs=$whrpkmat=$wheremat=$whpremipnn=$whrkppnn=$whissue="";
if($pt!=''){
	$where=" and substr(a.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$where2=" and substr(a.kodeblok,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$where_spb=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$whereJ=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
	$whtbs=" and a.supplierid in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
	$whrpkmat=" and substr(a.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	
	$whpremipnn=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$whissue=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$whrkppnn=" and substr(a.divisi,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
}
if($kdorg!=''){
	$where=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$where2=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
	$where_spb=" and a.kodeorg ='".$kdorg."'";
	$whereJ=" and a.kodeorg ='".$kdorg."'";
	$whtbs=" and a.supplierid ='".$kdorg."'";
	$whrpkmat=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$whpremipnn=" and a.kodeorg ='".$kdorg."'";
	$whissue=" and a.kodeorg ='".$kdorg."'";
	$whrkppnn=" and a.divisi like '".$kdorg."%'";
}

$wh="";$wh2="";$whB="";$wh_spb="";$wh_bgt=$wh_bgtrp='';
if($divisi!=''){
	$whpremipnn.=" and a.divisi like '".$divisi."%'";
	$whissue.=" and a.divisi like '".$divisi."%'";
	$whrkppnn.=" and a.divisi like '".$divisi."%'";
	$wh.=" and a.kodeblok like '".$divisi."%'";
	$whB.=" and a.kodeblok like '".$divisi."%'";
	$wh2.=" and a.kodeorg like '".$divisi."%'";
	$wh_spb.=" and a.divisi like '".$divisi."%'";
	$wh_bgt.=" and a.divisi like '".$divisi."%'";
	$wh_bgtrp.=" and a.kodeorg like '".$divisi."%'";
	$whereJ.=" and a.kodeblok like '".$divisi."%'";
	$whrpkmat.=" and a.kodeorg like '".$divisi."%'";
}
if($tt!=''){
	$whereJ.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
	$wh.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
	$wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
	$whissue.=" and a.blok in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
	$whpremipnn.=" and a.tahuntanam='".$tt."'";
	$whrkppnn.=" and a.tahuntanam='".$tt."'";
	$wh2.=" and a.tahuntanam='".$tt."'";
	$whB.=" and a.thntnm='".$tt."'";
	$wh_spb.=" and a.tahuntanam='".$tt."'";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
	$whrpkmat.=" and a.kodeorg in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
}
if($ip!=''){
	$whpremipnn.=" and a.blok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$whissue.=" and a.blok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$whrkppnn.=" and a.blok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$whereJ.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$wh.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbname.".bgt_blok where intiplasma='".$ip."')";
	$wh2.=" and a.intiplasma='".$ip."'";
	$whB.=" and a.intiplasma='".$ip."'";
	$wh_spb.=" and a.intiplasma='".$ip."'";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where intiplasma='".$ip."')";
	$whrpkmat.=" and a.kodeorg in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
}


$stylefont="style=font-weight:normal;color:#A0A0A0";
$stylefontbln="style=font-weight:normal;color:#02BC28";


$kolom=array();
$str = "SELECT *  FROM " . $dbname . ".kebun_5csbm_issues order by nomor";
$res = fetchdata($str);
foreach ($res as $bar){
	if($bar['level']=='2'){				
		$kolom[$bar['induk']]+=1;
	}
	$idhd[$bar['id']]=$bar['id'];
	$head[$bar['id']][$bar['level']]=$bar['nama'];
}


if($proses!='excel'){	
	//$tab.="<table id=mytable class='sortable nowrap' cellspacing=1>";
	$tab.="<table id=mytable class='sortable' cellspacing=1>";
}else{
	$tab.="<table border=1 class=sortable cellspacing=1>";
}
$tab.="
    <thead>
        <tr class=rowheader title=\"Double click untuk freeze column.\">
            <th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='3'>Estate</th>
            <th align=center rowspan='3'>Division</th>
            <th align=center rowspan='3'>Block Code</th>
            <th align=center rowspan='3'>Plant Year</th>
            <th align=center rowspan='3'>Replanted Prog</th>
            <th align=center rowspan='3'>Date of Maturity</th>
            <th align=center rowspan='3'>Planting Material</th>
            <th align=center colspan='2' rowspan=2>Hectares</th>
            <th align=center colspan='3' rowspan=2>Palm Density</th>
            <th align=center colspan='2' rowspan=2>Harvesters</th>
            <th align=center colspan='2' rowspan=2>Ha Harvested</th>
            <th align=center colspan='2' rowspan=2>Harv. Round</th>
            <th align=center colspan='2' rowspan=2>Ha Covered / Harvester</th>
            <th align=center colspan='7'>FFB in Ton</th>
            <th align=center colspan='7'>Yield per Ha in Ton</th>
            <th align=center colspan='2' rowspan=2>Var % YTD</th>
            <th align=center colspan='5'>FFB per Harvester (Ton)</th>
            <th align=center colspan='4'>Total Loose Fruit</th>
            <th align=center colspan='2' rowspan=2>Total FFB</th>
            <th align=center colspan='2' rowspan=2>FFB/Tree</th>
            <th align=center colspan='3' rowspan=2>Average FFB in Kg</th>
            <th align=center colspan='14'>Harvesting & Collection Cost (In Rp)</th>
            <th align=center colspan='2' rowspan=2>Estimate FFB in Ton Next Mth</th>
			";
			$colom=0;
			foreach($head as $id => $vlvl){
				foreach($vlvl as $lvl => $name){
					$colom=$kolom[$id];
					if($lvl=='1'){								
						$tab.="<th colspan=".$colom." rowspan=2 align=center>".$name."</th>";
					}
				}						
			}
        $tab.="<th align=center rowspan='2' colspan='2'>PICA</th>";
		$tab.="	</tr>";
        $tab.="<tr class=rowheader>
				<th align=center colspan=3>Budget</th>
				<th align=center colspan=2>Actual</th>
				<th align=center colspan=2>SMLY</th>
				<th align=center colspan=3>Budget</th>
				<th align=center colspan=2>Actual</th>
				<th align=center colspan=2>SMLY</th>
				<th align=center colspan=3>Budget</th>
				<th align=center colspan=2>Actual</th>
				<th align=center colspan=2>Ton</th>
				<th align=center colspan=2>%</th>
				<th align=center colspan=2>Ttl. Harv Cost</th>
				<th align=center colspan=2>Harv Cost / Kg FFB</th>
				<th align=center colspan=3>Ttl. Bgt Harv Cost</th>
				<th align=center colspan=3>Bgt Harv Cost / Kg FFB</th>
				<th align=center colspan=2>Ttl. Trans Cost</th>
				<th align=center colspan=2>Trans Cost / Kg FFB</th>
				";
		$tab.="	</tr>";
		
		
		
		
        $tab.="<tr class=rowheader>";
		$tab.="<th align=center>Total</th>";
		$tab.="<th align=center>Harvested</th>";
		$tab.="<th align=center>Ttl Palms</th>";
		$tab.="<th align=center>Productive Palms</th>";
		$tab.="<th align=center>SPH</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>1 Year</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>1 Year</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>Act vs Bgt</th>";
		$tab.="<th align=center>Act vs SMLY</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>1 Year</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>Std</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>1 Year</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>1 Year</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		$tab.="<th align=center>TM</th>";
		$tab.="<th align=center>TD</th>";
		
			foreach($head as $id => $vlvl){
				foreach($vlvl as $lvl => $name){
					if($lvl=='2'){								
						$tab.="<th align=center>".$name."</th>";
					}
				}
			}
		
		$tab.="<th align=center></th>";
		$tab.="<th align=center></th>";
		$tab.="</tr>
		
		
    </thead>
 <tbody>";


#master blok
$str="select * from ".$dbname.".setup_blok_tahunan a where 1=1 ".$wh2." ".$where." and a.statusblok='TM' and (a.tahun like '".$tahun."%' or a.tahun like '".($tahun-1)."%') and a.luasareaproduktif>0 order by a.kodeorg";
$res = fetchdata($str);
$ada=0;$kodeblok=array();
foreach($res as $bar){
	$prdaresta=substr($bar['tahun'],0,4)."-".substr($bar['tahun'],4,2);
	$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,6)][$bar['kodeorg']]=$bar['kodeorg'];
	if($prdaresta==$prd){
		#luas bulan ini
		#luas ha
		$biaya[$bar['kodeorg']]['ha']['tm']=$bar['luasareaproduktif'];
		#pokok
		$biaya[$bar['kodeorg']]['pkk']['tm']=$bar['jumlahpokok'];		
		$biaya[$bar['kodeorg']]['pkkprd']['tm']=$bar['jumlahpokok'];		
		#sph
		$biaya[$bar['kodeorg']]['sph']['tm']=$bar['jumlahpokok']/$bar['luasareaproduktif'];

		$ada+=1;
	}
	if($prdaresta==($tahun-1)."-".$bulan){
		#luaslalu
		$biaya[$bar['kodeorg']]['ha']['smly']=$bar['luasareaproduktif'];
		#pokoklalu
		$biaya[$bar['kodeorg']]['pkk']['smly']=$bar['jumlahpokok'];
		$biaya[$bar['kodeorg']]['pkkprd']['smly']=$bar['jumlahpokok'];
		#sph
		$biaya[$bar['kodeorg']]['sph']['smly']=$bar['jumlahpokok']/$bar['luasareaproduktif'];
	}
}

if($ada==0){
	$str="select * from ".$dbname.".setup_blok a where 1=1 ".$wh2." ".$where." and a.statusblok='TM' and a.luasareaproduktif>0 order by a.kodeorg";
	$res = fetchdata($str);
	foreach($res as $bar){
		$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,6)][$bar['kodeorg']]=$bar['kodeorg'];
		#luas ha
		$biaya[$bar['kodeorg']]['ha']['td']=$bar['luasareaproduktif'];
		$biaya[$bar['kodeorg']]['ha']['tm']=$bar['luasareaproduktif'];
		#pokok
		$biaya[$bar['kodeorg']]['pkk']['td']=$bar['jumlahpokok'];
		$biaya[$bar['kodeorg']]['pkk']['tm']=$bar['jumlahpokok'];
		$biaya[$bar['kodeorg']]['pkkprd']['td']=$bar['jumlahpokok'];
		$biaya[$bar['kodeorg']]['pkkprd']['tm']=$bar['jumlahpokok'];
		#sph
		$biaya[$bar['kodeorg']]['sph']['td']=$bar['jumlahpokok']/$bar['luasareaproduktif'];
		$biaya[$bar['kodeorg']]['sph']['tm']=$bar['jumlahpokok']/$bar['luasareaproduktif'];
	}	
}

#total ha estate
$str="select * from ".$dbname.".setup_blok a where 1=1 ".$wh2." ".$where." and a.statusblok='TM' and a.luasareaproduktif>0 order by a.kodeorg";
$res = fetchdata($str);
foreach($res as $bar){
	$ttlhaest[substr($bar['kodeorg'],0,4)]+=$bar['luasareaproduktif'];
}	

#ambil luas bgt
$str = "select kodeblok, sum(hathnini) as hathnini, sum(pokokthnini) as pokokthnini from " . $dbname . ".bgt_blok a  where 1=1 ".$whB." ".$where2." and tahunbudget='".$tahun."' group by a.kodeblok"; 
$res = fetchdata($str);
foreach($res as $bar){	
	#luas ha
	$biaya[$bar['kodeblok']]['ha']['bgt_ti']=$bar['hathnini'];
	#pokok
	$biaya[$bar['kodeblok']]['pkk']['bgt_ti']=$bar['pokokthnini'];
	#sph
	if($bar['hathnini']!='0'){		
		$biaya[$bar['kodeblok']]['sph']['bgt_ti']=$bar['pokokthnini']/$bar['hathnini'];	
	}
}

#ambil prd real
if($prod=='1'){
	$produksi="sum(kgwbnetto) as kgwb,";
}else{
	$produksi="sum(kgwb) as kgwb,";
}

$prdton=$prdtontitle=array();
$str = "select ".$produksi." substr(tanggal,1,7) as periode, blok, sum(brondolan) as brondolan, sum(jjg) as jjg from " . $dbname . ".kebun_spb_vw a  where 1=1 ".$wh_spb." ".$where_spb." and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' group by periode, blok"; 
$res = fetchdata($str);
foreach($res as $bar){
	#produksi
	if($bar['periode']==$prd){		
		$biaya[$bar['blok']]['prd']['tm']+=$bar['kgwb']/1000;
		$biaya[$bar['blok']]['brd']['tm']+=$bar['brondolan']/1000;
		$biaya[$bar['blok']]['jjg']['tm']+=$bar['jjg'];
	}
	$biaya[$bar['blok']]['prd']['td']+=$bar['kgwb']/1000;
	$biaya[$bar['blok']]['brd']['td']+=$bar['brondolan']/1000;
	$biaya[$bar['blok']]['jjg']['td']+=$bar['jjg'];
	$prdtontitle[$bar['blok']]+=($bar['kgwb']/1000);
}

#ambil prd real tahun lalu
$str = "select ".$produksi." substr(tanggal,1,7) as periode, blok, sum(brondolan) as brondolan, sum(jjg) as jjg from " . $dbname . ".kebun_spb_vw a  where 1=1 ".$wh_spb." ".$where_spb." and substr(tanggal,1,7) between '".$periodelalu1."' and  '".$periodelalu2."' group by blok, periode"; 
$res = fetchdata($str);
foreach($res as $bar){	
	#produksi
	if($bar['periode']==$periodelalu2){		
		$biaya[$bar['blok']]['prd_smly']['tm']+=$bar['kgwb']/1000;
		$biaya[$bar['blok']]['brd_smly']['tm']+=$bar['brondolan']/1000;
	}
	$biaya[$bar['blok']]['prd_smly']['td']+=$bar['kgwb']/1000;
	$biaya[$bar['blok']]['brd_smly']['td']+=$bar['brondolan']/1000;
}

#ambil prd bgt
$e="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="kg".addZero($i,2);
	if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";

$str=" select kodeblok,".$e." as sdbi,kg".$bulan." as bi,kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." ".$whB." ".$wh_bgt." and tahunbudget = '".$tahun."'";
$res = fetchdata($str);
foreach($res as $bar){	
	@$prdbgtbi += $bar['bi'];
	@$prdtonaop[$bar['kodeblok']] += ($bar['kgsetahun']/1000);
	@$prdbgtthn += $bar['kgsetahun'];
	
	$biaya[$bar['kodeblok']]['prd_bgt']['tm']+=$bar['bi']/1000;
	$biaya[$bar['kodeblok']]['prd_bgt']['td']+=$bar['sdbi']/1000;
	$biaya[$bar['kodeblok']]['prd_bgt']['thn']+=$bar['kgsetahun']/1000;
}

#ambil Estimate FFB In Ton Next Mth
$e="(";
for($i=1;$i<=intval($bulandpn);$i++){
	$r="kg".addZero($i,2);
	if($i<intval($bulandpn)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";

$str=" select kodeblok,".$e." as sdbi,kg".$bulandpn." as bi,kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." ".$whB." ".$wh_bgt." and tahunbudget = '".$tahundpn."'";
$res = fetchdata($str);
foreach($res as $bar){	
	$biaya[$bar['kodeblok']]['prd_bgt_dpn']['tm']+=$bar['bi']/1000;
	$biaya[$bar['kodeblok']]['prd_bgt_dpn']['td']+=$bar['sdbi']/1000;
	$biaya[$bar['kodeblok']]['prd_bgt_dpn']['thn']+=$bar['kgsetahun']/1000;
}


#ambil tk panen
$str = "select * from " . $dbname . ".kebun_3premipemanen a  where 1=1 ".$whpremipnn." and periode between '".$periode1."' and  '".$periode2."' group by karyawanid, blok, tanggalpanen"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['periode']==$prd){		
		$datatkbi[$bar['blok']][$bar['tanggalpanen']][$bar['karyawanid']]=1;
	}
	$datatk[$bar['blok']][$bar['tanggalpanen']][$bar['karyawanid']]=$bar['karyawanid'];
}

foreach($datatk as $blk => $vtpnn){
	foreach($vtpnn as $tglpnn => $vkar){
		foreach($vkar as $kary){
			$biaya[$blk]['tkpnn']['td']+=1;
			$biaya[$blk]['tkpnn']['tm']+=$datatkbi[$blk][$tglpnn][$kary];
		}
	}
}

#ambil luas panen
$str = "select substr(tanggal,1,7) as periode, sum(luaspanen) as luas,blok from " . $dbname . ".kebun_rekappnn a  where 1=1 ".$whrkppnn." and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' group by periode, blok"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['periode']==$prd){		
		$biaya[$bar['blok']]['luaspnn']['tm']+=$bar['luas'];
	}
	$biaya[$bar['blok']]['luaspnn']['td']+=$bar['luas'];
}	
	
$str = "select * from " . $dbname . ".keu_5parameterjurnal a  where jurnalid in ('PNN01','PNN02')"; 
$res = fetchdata($str);
foreach($res as $bar){
	$akunupahpnn[$bar['noakundebet']]=$bar['noakundebet'];
}	
	
$akuntranspnn=array('6110103','6110104');
$lbrpupuk    =array('621010302','621010305','621010308');
$transpupuk  =array('621010323','621010324');
$whakun      =" and substr(noakun,1,3) in ('611','621')";
$whakunumum  =" and noakun like '7%'";

# biaya tahun ini
$str = "select a.kodekegiatan,kodeorg,a.kodeblok, sum(jumlah) as jumlah, periode,noakun,kodejurnal 
from " . $dbname . ".keu_jurnaldt_vw a   
where 1=1 ".$whereJ." and periode between '".$periode1."' and  '".$periode2."' ".$whakun."     
group by kodeblok,periode,noakun,kodejurnal,kodekegiatan"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['kodeblok']==''){
		$kodeblok[$bar['kodeorg']][$bar['kodeorg']][$bar['kodeorg']]=$bar['kodeorg']."99";
		$bar['kodeblok']=$bar['kodeorg']."99";
	}
	if(substr($bar['noakun'],0,3)=='611' and !in_array($bar['noakun'],$akuntranspnn)){
		#biaya panen
		if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akunupahpnn)){
			#labor
			if($bar['periode']==$prd){
				$biaya[$bar['kodeblok']]['byy_lab']['tm']+=($bar['jumlah']);
			}
			$biaya[$bar['kodeblok']]['byy_lab']['td']+=($bar['jumlah']);
		}else if(substr($bar['kodejurnal'],0,3)=='INV'){
			#material
			if($bar['periode']==$prd){
				$biaya[$bar['kodeblok']]['byy_mat']['tm']+=($bar['jumlah']);
			}
			$biaya[$bar['kodeblok']]['byy_mat']['td']+=($bar['jumlah']);
		}else{
			if($bar['periode']==$prd){
				$biaya[$bar['kodeblok']]['byy_oth']['tm']+=($bar['jumlah']);
			}
			$biaya[$bar['kodeblok']]['byy_oth']['td']+=($bar['jumlah']);
		}
		if($bar['periode']==$prd){
			$biaya[$bar['kodeblok']]['ttlbyypnn']['tm']+=($bar['jumlah']);
		}
		$biaya[$bar['kodeblok']]['ttlbyypnn']['td']+=($bar['jumlah']);
	}
	if(substr($bar['noakun'],0,3)=='611' and in_array($bar['noakun'],$akuntranspnn)){
		#transport
		if($bar['periode']==$prd){
			$biaya[$bar['kodeblok']]['byy_tra']['tm']+=($bar['jumlah']);
		}
		$biaya[$bar['kodeblok']]['byy_tra']['td']+=($bar['jumlah']);
		// #biaya panen
		// if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akunupahpnn)){
			// #labor
			// if($bar['periode']==$prd){
				// $biaya[$bar['kodeblok']]['byy_lab']['tm']+=($bar['jumlah']);
			// }
			// $biaya[$bar['kodeblok']]['byy_lab']['td']+=($bar['jumlah']);
		// }else if(substr($bar['kodejurnal'],0,3)=='INV'){
			// #material
			// if($bar['periode']==$prd){
				// $biaya[$bar['kodeblok']]['byy_mat']['tm']+=($bar['jumlah']);
			// }
			// $biaya[$bar['kodeblok']]['byy_mat']['td']+=($bar['jumlah']);
		// }else if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akuntranspnn)){
			// #transport
			// if($bar['periode']==$prd){
				// $biaya[$bar['kodeblok']]['byy_tra']['tm']+=($bar['jumlah']);
			// }
			// $biaya[$bar['kodeblok']]['byy_tra']['td']+=($bar['jumlah']);
		// }else{
			// if($bar['periode']==$prd){
				// $biaya[$bar['kodeblok']]['byy_oth']['tm']+=($bar['jumlah']);
			// }
			// $biaya[$bar['kodeblok']]['byy_oth']['td']+=($bar['jumlah']);
		// }
		// if($bar['periode']==$prd){
			// $biaya[$bar['kodeblok']]['ttlbyypnn']['tm']+=($bar['jumlah']);
		// }
		// $biaya[$bar['kodeblok']]['ttlbyypnn']['td']+=($bar['jumlah']);
	}
}

$e="("; $s="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
	if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
}
$e.=")"; $s.=")";

$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";

#ini khusus budget kebun
$str=" select kodebarang,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whakun."";
$res = fetchdata($str);
foreach($res as $bar){
	if(substr($bar['noakun'],0,3)=='611'){
		if(substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK'){
			if(in_array($bar['noakun'],$akunupahpnn) and substr($bar['kodebudget'],0,3)=='SDM'){
				#khusus HK panen
				$biaya[$bar['kodeorg']]['tkbgt']['tm']+=$bar['bivol'];
				$biaya[$bar['kodeorg']]['tkbgt']['td']+=$bar['sdbivol'];
				$biaya[$bar['kodeorg']]['tkbgt']['thn']+=$bar['jumlah'];
			}
			#LABOUR
			$biaya[$bar['kodeorg']]['bgt_lab']['tm']+=($bar['bi']);
			$biaya[$bar['kodeorg']]['bgt_lab']['td']+=($bar['sdbi']);
			$biaya[$bar['kodeorg']]['bgt_lab']['thn']+=($bar['rupiah']);
		}else if(substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL'){
			#MATERIAL			
			$biaya[$bar['kodeorg']]['bgt_mat']['tm']+=($bar['bi']);
			$biaya[$bar['kodeorg']]['bgt_mat']['td']+=($bar['sdbi']);
			$biaya[$bar['kodeorg']]['bgt_mat']['thn']+=($bar['rupiah']);
		}else if($bar['kodebudget']=='VHC'){
			#TRANS
			$biaya[$bar['kodeorg']]['bgt_tra']['tm']+=($bar['bi']);
			$biaya[$bar['kodeorg']]['bgt_tra']['td']+=($bar['sdbi']);
			$biaya[$bar['kodeorg']]['bgt_tra']['thn']+=($bar['rupiah']);
		}else{
			#OTHER
			$biaya[$bar['kodeorg']]['bgt_oth']['tm']+=($bar['bi']);
			$biaya[$bar['kodeorg']]['bgt_oth']['td']+=($bar['sdbi']);
			$biaya[$bar['kodeorg']]['bgt_oth']['thn']+=($bar['rupiah']);
		}
		$biaya[$bar['kodeorg']]['ttlbyybgt']['tm']+=($bar['bi']);
		$biaya[$bar['kodeorg']]['ttlbyybgt']['td']+=($bar['sdbi']);
		$biaya[$bar['kodeorg']]['ttlbyybgt']['thn']+=($bar['rupiah']);
	}
}


$str = "SELECT *  FROM " . $dbname . ".kebun_csbm_issues a where 1=1 ".$whissue." and periode = '".$periode2."'";
$res = fetchdata($str);
foreach ($res as $bar){
	$nilai[$bar['blok']][$bar['id']]=$bar['nilai'];
}

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

$style="style=cursor:pointer;color:blue; title=\"Click untuk detail...\"";
if($barishide=='1'){$stylerow="";}else{	$stylerow="style=display:none";}
$style=$stylerow="";

$nobrsgreen=$nobrsgreenp=$nobrsyellow=$nobrsred=$nobrsredp=$nobrttl=$nobrttlp="";
$no=0;$nodiv=0;$gtrluas=0;$green=$yellow=$red=$greenp=$redp=$nocol=0;
$tdluas=$teluas=$tbcluas=$tdcluas=$stdivbiaya=array();
foreach($kodeblok as $estate => $valdiv){
	$est=0;
	foreach($valdiv as $div => $valkodeblok){
		$row=0;$nodiv+=1;
		foreach($valkodeblok as $blok){
			$row+=1;$est+=1;$no+=1;
			$nmtt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$blok."'");
			$nmha=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$blok."'");
			$nmpkk=makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$blok."'");
			$bbt=makeOption($dbname,'setup_blok','kodeorg,jenisbibit',"kodeorg='".$blok."'");
			
			$title="";
			#$title.="title=\"Single click untuk memberi tanda.";
			$title.="\nBlok : ".$nmorg[$blok]."";
			$title.="\nLuas : ".numb_format($nmha[$blok],2)." Ha";
			$title.="\nPokok : ".$nmpkk[$blok]."";
			$title.="\nProduksi : ".numb_format($prdtontitle[$blok],2)." Ton";
			
			$tab.="<tr class=rowcontent ".$stylerow." id=row_".$no." name=".$estate."[] onclick=getmark(this.id); ".$title."\">";
			
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$estate."</td>";
			$tab.="<td>".$div."</td>";
			$tab.="<td id=kdblok_".$no.">".$nmorg[$blok]."</td>";
			$tab.="<td align=center>".$nmtt[$blok]."</td>";
			if(strlen($blok)==10){				
				$tab.="<td align=center>".($nmtt[$blok]+31)."</td>";
				$tab.="<td align=center>".($nmtt[$blok]+3)."</td>";
			}else{
				$tab.="<td align=center></td>";
				$tab.="<td align=center></td>";
			}
			$tab.="<td align=left>".$bbt[$blok]."</td>";
			$tab.="<td align=right>".numb_format($biaya[$blok]['ha']['tm'],2)."</td>";
			$tab.="<td align=right>".numb_format($biaya[$blok]['ha']['tm'],2)."</td>";
			$tab.="<td align=right>".numb_format($biaya[$blok]['pkk']['tm'],0)."</td>";
			$tab.="<td align=right>".numb_format($biaya[$blok]['pkkprd']['tm'],0)."</td>";
			$tab.="<td align=right>".numb_format($biaya[$blok]['sph']['tm'],0)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','tkpnn','tm');>".numb_format($biaya[$blok]['tkpnn']['tm'],0)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','tkpnn','td');>".numb_format($biaya[$blok]['tkpnn']['td'],0)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','luaspnn','tm');>".numb_format($biaya[$blok]['luaspnn']['tm'],2)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','luaspnn','td');>".numb_format($biaya[$blok]['luaspnn']['td'],2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['luaspnn']['tm'],$biaya[$blok]['ha']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['luaspnn']['td'],$biaya[$blok]['ha']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['luaspnn']['tm'],$biaya[$blok]['tkpnn']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['luaspnn']['td'],$biaya[$blok]['tkpnn']['td']),2)."</td>";
			$tab.="<td align=right>".numb_format($biaya[$blok]['prd_bgt']['tm'],2)."</td>";
			$tab.="<td align=right>".numb_format($biaya[$blok]['prd_bgt']['td'],2)."</td>";
			$tab.="<td align=right>".numb_format($biaya[$blok]['prd_bgt']['thn'],2)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','prd','tm','".$prod."');>".numb_format($biaya[$blok]['prd']['tm'],2)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','prd','td','".$prod."');>".numb_format($biaya[$blok]['prd']['td'],2)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','prd_smly','tm','".$prod."');>".numb_format($biaya[$blok]['prd_smly']['tm'],2)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','prd_smly','td','".$prod."');>".numb_format($biaya[$blok]['prd_smly']['td'],2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd_bgt']['tm'],$biaya[$blok]['ha']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd_bgt']['td'],$biaya[$blok]['ha']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd_bgt']['thn'],$biaya[$blok]['ha']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd']['tm'],$biaya[$blok]['ha']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd']['td'],$biaya[$blok]['ha']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd_smly']['tm'],$biaya[$blok]['ha']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd_smly']['td'],$biaya[$blok]['ha']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi(bagi($biaya[$blok]['prd']['td'],$biaya[$blok]['ha']['tm']),bagi($biaya[$blok]['prd_bgt']['td'],$biaya[$blok]['ha']['tm']))*100-100,2)."</td>";
			$tab.="<td align=right>".numb_format(bagi(bagi($biaya[$blok]['prd']['td'],$biaya[$blok]['ha']['tm']),bagi($biaya[$blok]['prd_smly']['td'],$biaya[$blok]['ha']['tm']))*100-100,2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd_bgt']['tm'],$biaya[$blok]['tkbgt']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd_bgt']['td'],$biaya[$blok]['tkbgt']['td']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd_bgt']['thn'],$biaya[$blok]['tkbgt']['thn']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd']['tm'],$biaya[$blok]['tkpnn']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd']['td'],$biaya[$blok]['tkpnn']['td']),2)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','brd','tm','".$prod."');>".numb_format($biaya[$blok]['brd']['tm'],2)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','brd','td','".$prod."');>".numb_format($biaya[$blok]['brd']['td'],2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['brd']['tm'],$biaya[$blok]['prd']['tm'])*100,2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['brd']['td'],$biaya[$blok]['prd']['td'])*100,2)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','jjg','tm','".$prod."');>".numb_format($biaya[$blok]['jjg']['tm'],2)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','jjg','td','".$prod."');>".numb_format($biaya[$blok]['jjg']['td'],2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['jjg']['tm'],$biaya[$blok]['pkkprd']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['jjg']['td'],$biaya[$blok]['pkkprd']['tm']),2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd']['tm'],$biaya[$blok]['jjg']['tm'])*1000,2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['prd']['td'],$biaya[$blok]['jjg']['td'])*1000,2)."</td>";
			$tab.="<td align=right></td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','ttlbyypnn','tm','".$prod."');>".numb_format($biaya[$blok]['ttlbyypnn']['tm'],0)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','ttlbyypnn','td','".$prod."');>".numb_format($biaya[$blok]['ttlbyypnn']['td'],0)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['ttlbyypnn']['tm'],$biaya[$blok]['prd']['tm'])/1000,2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['ttlbyypnn']['td'],$biaya[$blok]['prd']['td'])/1000,2)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','ttlbyybgt','tm','".$prod."');>".numb_format($biaya[$blok]['ttlbyybgt']['tm'],0)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','ttlbyybgt','td','".$prod."');>".numb_format($biaya[$blok]['ttlbyybgt']['td'],0)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','ttlbyybgt','thn','".$prod."');>".numb_format($biaya[$blok]['ttlbyybgt']['thn'],0)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['ttlbyybgt']['tm'],$biaya[$blok]['prd_bgt']['tm'])/1000,2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['ttlbyybgt']['td'],$biaya[$blok]['prd_bgt']['td'])/1000,2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['ttlbyybgt']['thn'],$biaya[$blok]['prd_bgt']['thn'])/1000,2)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','byy_tra','tm','".$prod."');>".numb_format($biaya[$blok]['byy_tra']['tm'],0)."</td>";
			$tab.="<td align=right ".$style." onclick=getpopup('".$nmorg[$blok]."','".$prd."','".$blok."','byy_tra','td','".$prod."');>".numb_format($biaya[$blok]['byy_tra']['td'],0)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['byy_tra']['tm'],$biaya[$blok]['prd']['tm'])/1000,2)."</td>";
			$tab.="<td align=right>".numb_format(bagi($biaya[$blok]['byy_tra']['td'],$biaya[$blok]['prd']['td'])/1000,2)."</td>";
			$tab.="<td align=right>".numb_format($biaya[$blok]['prd_bgt_dpn']['tm'],0)."</td>";
			$tab.="<td align=right>".numb_format($biaya[$blok]['prd_bgt_dpn']['td'],0)."</td>";
			
			foreach($head as $id => $vlvl){
				foreach($vlvl as $lvl => $name){
					if($lvl=='2'){
						$nmisi=makeOption($dbname,'kebun_5csbm_issues','nilai,nama',"induk='".$id."' and nilai='".$nilai[$blok][$id]."'");
						$tab.="<td align=center>".$nmisi[$nilai[$blok][$id]]."</td>";
					}
				}						
			}
			if($proses!='excel'){				
				$tab.="<td align=center><img src=images/skyblue/zoom.png class=zImgBtn onclick=previewpica('".$estate."','".$div."','".$prd."','html','pica')></td>";
				$tab.="<td align=center><img src=images/excel.jpg class=zImgBtn onclick=detailExcel('".$estate."','".$div."','".$prd."','excel','pica')></td>";
			}else{
				$tab.="<td></td>";
				$tab.="<td></td>";
			}
			
			
				
			$tab.="</tr>";
			#total divisi
			$biaya[$div]['ha']['tm']+=$biaya[$blok]['ha']['tm'];
			$biaya[$div]['pkk']['tm']+=$biaya[$blok]['pkk']['tm'];
			$biaya[$div]['pkkprd']['tm']+=$biaya[$blok]['pkkprd']['tm'];
			$biaya[$div]['sph']['tm']+=$biaya[$blok]['pkk']['tm']/$biaya[$blok]['ha']['tm'];
			$biaya[$div]['tkpnn']['tm']+=$biaya[$blok]['tkpnn']['tm'];
			$biaya[$div]['tkpnn']['td']+=$biaya[$blok]['tkpnn']['td'];
			$biaya[$div]['luaspnn']['tm']+=$biaya[$blok]['luaspnn']['tm'];
			$biaya[$div]['luaspnn']['td']+=$biaya[$blok]['luaspnn']['td'];
			$biaya[$div]['prd_bgt']['tm']+=$biaya[$blok]['prd_bgt']['tm'];
			$biaya[$div]['prd_bgt']['td']+=$biaya[$blok]['prd_bgt']['td'];
			$biaya[$div]['prd_bgt']['thn']+=$biaya[$blok]['prd_bgt']['thn'];
			$biaya[$div]['prd']['tm']+=$biaya[$blok]['prd']['tm'];
			$biaya[$div]['prd']['td']+=$biaya[$blok]['prd']['td'];
			$biaya[$div]['prd_smly']['tm']+=$biaya[$blok]['prd_smly']['tm'];
			$biaya[$div]['prd_smly']['td']+=$biaya[$blok]['prd_smly']['td'];
			$biaya[$div]['brd']['tm']+=$biaya[$blok]['brd']['tm'];
			$biaya[$div]['brd']['td']+=$biaya[$blok]['brd']['td'];
			$biaya[$div]['jjg']['tm']+=$biaya[$blok]['jjg']['tm'];
			$biaya[$div]['jjg']['td']+=$biaya[$blok]['jjg']['td'];
			$biaya[$div]['ttlbyypnn']['tm']+=$biaya[$blok]['ttlbyypnn']['tm'];
			$biaya[$div]['ttlbyypnn']['td']+=$biaya[$blok]['ttlbyypnn']['td'];
			$biaya[$div]['ttlbyybgt']['tm']+=$biaya[$blok]['ttlbyybgt']['tm'];
			$biaya[$div]['ttlbyybgt']['td']+=$biaya[$blok]['ttlbyybgt']['td'];
			$biaya[$div]['ttlbyybgt']['thn']+=$biaya[$blok]['ttlbyybgt']['thn'];
			$biaya[$div]['byy_tra']['tm']+=$biaya[$blok]['byy_tra']['tm'];
			$biaya[$div]['byy_tra']['td']+=$biaya[$blok]['byy_tra']['td'];
			$biaya[$div]['prd_bgt_dpn']['tm']+=$biaya[$blok]['prd_bgt_dpn']['tm'];
			$biaya[$div]['prd_bgt_dpn']['td']+=$biaya[$blok]['prd_bgt_dpn']['td'];

			#total estate
			$biaya[$estate]['ha']['tm']+=$biaya[$blok]['ha']['tm'];
			$biaya[$estate]['pkk']['tm']+=$biaya[$blok]['pkk']['tm'];
			$biaya[$estate]['pkkprd']['tm']+=$biaya[$blok]['pkkprd']['tm'];
			$biaya[$estate]['sph']['tm']+=$biaya[$blok]['pkk']['tm']/$biaya[$blok]['ha']['tm'];
			$biaya[$estate]['tkpnn']['tm']+=$biaya[$blok]['tkpnn']['tm'];
			$biaya[$estate]['tkpnn']['td']+=$biaya[$blok]['tkpnn']['td'];
			$biaya[$estate]['luaspnn']['tm']+=$biaya[$blok]['luaspnn']['tm'];
			$biaya[$estate]['luaspnn']['td']+=$biaya[$blok]['luaspnn']['td'];
			$biaya[$estate]['prd_bgt']['tm']+=$biaya[$blok]['prd_bgt']['tm'];
			$biaya[$estate]['prd_bgt']['td']+=$biaya[$blok]['prd_bgt']['td'];
			$biaya[$estate]['prd_bgt']['thn']+=$biaya[$blok]['prd_bgt']['thn'];
			$biaya[$estate]['prd']['tm']+=$biaya[$blok]['prd']['tm'];
			$biaya[$estate]['prd']['td']+=$biaya[$blok]['prd']['td'];
			$biaya[$estate]['prd_smly']['tm']+=$biaya[$blok]['prd_smly']['tm'];
			$biaya[$estate]['prd_smly']['td']+=$biaya[$blok]['prd_smly']['td'];
			$biaya[$estate]['brd']['tm']+=$biaya[$blok]['brd']['tm'];
			$biaya[$estate]['brd']['td']+=$biaya[$blok]['brd']['td'];
			$biaya[$estate]['jjg']['tm']+=$biaya[$blok]['jjg']['tm'];
			$biaya[$estate]['jjg']['td']+=$biaya[$blok]['jjg']['td'];
			$biaya[$estate]['ttlbyypnn']['tm']+=$biaya[$blok]['ttlbyypnn']['tm'];
			$biaya[$estate]['ttlbyypnn']['td']+=$biaya[$blok]['ttlbyypnn']['td'];
			$biaya[$estate]['ttlbyybgt']['tm']+=$biaya[$blok]['ttlbyybgt']['tm'];
			$biaya[$estate]['ttlbyybgt']['td']+=$biaya[$blok]['ttlbyybgt']['td'];
			$biaya[$estate]['ttlbyybgt']['thn']+=$biaya[$blok]['ttlbyybgt']['thn'];
			$biaya[$estate]['byy_tra']['tm']+=$biaya[$blok]['byy_tra']['tm'];
			$biaya[$estate]['byy_tra']['td']+=$biaya[$blok]['byy_tra']['td'];
			$biaya[$estate]['prd_bgt_dpn']['tm']+=$biaya[$blok]['prd_bgt_dpn']['tm'];
			$biaya[$estate]['prd_bgt_dpn']['td']+=$biaya[$blok]['prd_bgt_dpn']['td'];

			#gt biaya
			$biaya_gt['ha']['tm']+=$biaya[$blok]['ha']['tm'];
			$biaya_gt['pkk']['tm']+=$biaya[$blok]['pkk']['tm'];
			$biaya_gt['pkkprd']['tm']+=$biaya[$blok]['pkkprd']['tm'];
			$biaya_gt['sph']['tm']+=$biaya[$blok]['pkk']['tm']/$biaya[$blok]['ha']['tm'];
			$biaya_gt['tkpnn']['tm']+=$biaya[$blok]['tkpnn']['tm'];
			$biaya_gt['tkpnn']['td']+=$biaya[$blok]['tkpnn']['td'];
			$biaya_gt['luaspnn']['tm']+=$biaya[$blok]['luaspnn']['tm'];
			$biaya_gt['luaspnn']['td']+=$biaya[$blok]['luaspnn']['td'];
			$biaya_gt['prd_bgt']['tm']+=$biaya[$blok]['prd_bgt']['tm'];
			$biaya_gt['prd_bgt']['td']+=$biaya[$blok]['prd_bgt']['td'];
			$biaya_gt['prd_bgt']['thn']+=$biaya[$blok]['prd_bgt']['thn'];
			$biaya_gt['prd']['tm']+=$biaya[$blok]['prd']['tm'];
			$biaya_gt['prd']['td']+=$biaya[$blok]['prd']['td'];
			$biaya_gt['prd_smly']['tm']+=$biaya[$blok]['prd_smly']['tm'];
			$biaya_gt['prd_smly']['td']+=$biaya[$blok]['prd_smly']['td'];
			$biaya_gt['brd']['tm']+=$biaya[$blok]['brd']['tm'];
			$biaya_gt['brd']['td']+=$biaya[$blok]['brd']['td'];
			$biaya_gt['jjg']['tm']+=$biaya[$blok]['jjg']['tm'];
			$biaya_gt['jjg']['td']+=$biaya[$blok]['jjg']['td'];
			$biaya_gt['ttlbyypnn']['tm']+=$biaya[$blok]['ttlbyypnn']['tm'];
			$biaya_gt['ttlbyypnn']['td']+=$biaya[$blok]['ttlbyypnn']['td'];
			$biaya_gt['ttlbyybgt']['tm']+=$biaya[$blok]['ttlbyybgt']['tm'];
			$biaya_gt['ttlbyybgt']['td']+=$biaya[$blok]['ttlbyybgt']['td'];
			$biaya_gt['ttlbyybgt']['thn']+=$biaya[$blok]['ttlbyybgt']['thn'];
			$biaya_gt['byy_tra']['tm']+=$biaya[$blok]['byy_tra']['tm'];
			$biaya_gt['byy_tra']['td']+=$biaya[$blok]['byy_tra']['td'];
			$biaya_gt['prd_bgt_dpn']['tm']+=$biaya[$blok]['prd_bgt_dpn']['tm'];
			$biaya_gt['prd_bgt_dpn']['td']+=$biaya[$blok]['prd_bgt_dpn']['td'];


			$awal=($no-$row)+1;
			$awalest=($no-$est)+1;
		}
		
		# TOTAL PER DIVISI
		//$tab.="<tr class=rowcontent name=baristotal[] style=cursor:pointer;background-color:#E7FCE4; title=\"Click untuk show atau hide baris.\" onclick=hiderow('".$awal."','".$no."','div')>";
		$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#E7FCE4;>";
		$tab.="<td align=center>".$nodiv."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=left>TTL ".$div."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['ha']['tm'],2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['ha']['tm'],2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['pkk']['tm'],0)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['pkkprd']['tm'],0)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['pkk']['tm']/$biaya[$div]['ha']['tm'],0)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['tkpnn']['tm'],0)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['tkpnn']['td'],0)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['luaspnn']['tm'],2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['luaspnn']['td'],2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['luaspnn']['tm'],$biaya[$div]['ha']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['luaspnn']['td'],$biaya[$div]['ha']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['luaspnn']['tm'],$biaya[$div]['tkpnn']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['luaspnn']['td'],$biaya[$div]['tkpnn']['td']),2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['prd_bgt']['tm'],2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['prd_bgt']['td'],2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['prd_bgt']['thn'],2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['prd']['tm'],2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['prd']['td'],2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['prd_smly']['tm'],2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['prd_smly']['td'],2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd_bgt']['tm'],$biaya[$div]['ha']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd_bgt']['td'],$biaya[$div]['ha']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd_bgt']['thn'],$biaya[$div]['ha']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd']['tm'],$biaya[$div]['ha']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd']['td'],$biaya[$div]['ha']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd_smly']['tm'],$biaya[$div]['ha']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd_smly']['td'],$biaya[$div]['ha']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi(bagi($biaya[$div]['prd']['td'],$biaya[$div]['ha']['tm']),bagi($biaya[$div]['prd_bgt']['td'],$biaya[$div]['ha']['tm']))*100-100,2)."</td>";
		$tab.="<td align=right>".numb_format(bagi(bagi($biaya[$div]['prd']['td'],$biaya[$div]['ha']['tm']),bagi($biaya[$div]['prd_smly']['td'],$biaya[$div]['ha']['tm']))*100-100,2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd_bgt']['tm'],$biaya[$div]['tkbgt']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd_bgt']['td'],$biaya[$div]['tkbgt']['td']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd_bgt']['thn'],$biaya[$div]['tkbgt']['thn']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd']['tm'],$biaya[$div]['tkpnn']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd']['td'],$biaya[$div]['tkpnn']['td']),2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['brd']['tm'],2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['brd']['td'],2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['brd']['tm'],$biaya[$div]['prd']['tm'])*100,2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['brd']['td'],$biaya[$div]['prd']['td'])*100,2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['jjg']['tm'],2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['jjg']['td'],2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['jjg']['tm'],$biaya[$div]['pkkprd']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['jjg']['td'],$biaya[$div]['pkkprd']['tm']),2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd']['tm'],$biaya[$div]['jjg']['tm'])*1000,2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['prd']['td'],$biaya[$div]['jjg']['td'])*1000,2)."</td>";
		$tab.="<td align=right></td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['ttlbyypnn']['tm'],0)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['ttlbyypnn']['td'],0)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['ttlbyypnn']['tm'],$biaya[$div]['prd']['tm'])/1000,2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['ttlbyypnn']['td'],$biaya[$div]['prd']['td'])/1000,2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['ttlbyybgt']['tm'],0)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['ttlbyybgt']['td'],0)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['ttlbyybgt']['thn'],0)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['ttlbyybgt']['tm'],$biaya[$div]['prd_bgt']['tm'])/1000,2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['ttlbyybgt']['td'],$biaya[$div]['prd_bgt']['td'])/1000,2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['ttlbyybgt']['thn'],$biaya[$div]['prd_bgt']['thn'])/1000,2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['byy_tra']['tm'],0)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['byy_tra']['td'],0)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['byy_tra']['tm'],$biaya[$div]['prd']['tm'])/1000,2)."</td>";
		$tab.="<td align=right>".numb_format(bagi($biaya[$div]['byy_tra']['td'],$biaya[$div]['prd']['td'])/1000,2)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['prd_bgt_dpn']['tm'],0)."</td>";
		$tab.="<td align=right>".numb_format($biaya[$div]['prd_bgt_dpn']['td'],0)."</td>";
		foreach($head as $id => $vlvl){
			foreach($vlvl as $lvl => $name){
				if($lvl=='2'){
					$tab.="<td align=center></td>";
				}
			}						
		}
		if($proses!='excel'){				
			$tab.="<td align=center><img src=images/skyblue/zoom.png class=zImgBtn onclick=previewpica('".$estate."','".$div."','".$prd."','html','pica')></td>";
			$tab.="<td align=center><img src=images/excel.jpg class=zImgBtn onclick=detailExcel('".$estate."','".$div."','".$prd."','excel','pica')></td>";
		}else{
			$tab.="<td></td>";
			$tab.="<td></td>";
		}
		
		$tab.="</tr>";
	}
	$nodiv+=1;
	# TOTAL ESTATE
	$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#C9FEFA;>";
	// $tab.="<tr class=rowcontent name=baristotal[] style=cursor:pointer;background-color:#C9FEFA; title=\"Click untuk show atau hide baris.\" onclick=hiderow('".$awalest."','".$no."','est')>";
	$tab.="<td align=center>".$nodiv."</td>";
	$tab.="<td align=left>TTL ".$estate."</td>";
	
	$tab.="<td align=center></td>";
	$tab.="<td align=center></td>";
	$tab.="<td align=center></td>";
	$tab.="<td align=center></td>";
	$tab.="<td align=center></td>";
	$tab.="<td align=center></td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['ha']['tm'],2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['ha']['tm'],2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['pkk']['tm'],0)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['pkkprd']['tm'],0)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['pkk']['tm']/$biaya[$estate]['ha']['tm'],0)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['tkpnn']['tm'],0)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['tkpnn']['td'],0)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['luaspnn']['tm'],2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['luaspnn']['td'],2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['luaspnn']['tm'],$biaya[$estate]['ha']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['luaspnn']['td'],$biaya[$estate]['ha']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['luaspnn']['tm'],$biaya[$estate]['tkpnn']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['luaspnn']['td'],$biaya[$estate]['tkpnn']['td']),2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['prd_bgt']['tm'],2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['prd_bgt']['td'],2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['prd_bgt']['thn'],2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['prd']['tm'],2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['prd']['td'],2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['prd_smly']['tm'],2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['prd_smly']['td'],2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd_bgt']['tm'],$biaya[$estate]['ha']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd_bgt']['td'],$biaya[$estate]['ha']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd_bgt']['thn'],$biaya[$estate]['ha']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd']['tm'],$biaya[$estate]['ha']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd']['td'],$biaya[$estate]['ha']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd_smly']['tm'],$biaya[$estate]['ha']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd_smly']['td'],$biaya[$estate]['ha']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi(bagi($biaya[$estate]['prd']['td'],$biaya[$estate]['ha']['tm']),bagi($biaya[$estate]['prd_bgt']['td'],$biaya[$estate]['ha']['tm']))*100-100,2)."</td>";
	$tab.="<td align=right>".numb_format(bagi(bagi($biaya[$estate]['prd']['td'],$biaya[$estate]['ha']['tm']),bagi($biaya[$estate]['prd_smly']['td'],$biaya[$estate]['ha']['tm']))*100-100,2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd_bgt']['tm'],$biaya[$estate]['tkbgt']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd_bgt']['td'],$biaya[$estate]['tkbgt']['td']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd_bgt']['thn'],$biaya[$estate]['tkbgt']['thn']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd']['tm'],$biaya[$estate]['tkpnn']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd']['td'],$biaya[$estate]['tkpnn']['td']),2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['brd']['tm'],2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['brd']['td'],2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['brd']['tm'],$biaya[$estate]['prd']['tm'])*100,2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['brd']['td'],$biaya[$estate]['prd']['td'])*100,2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['jjg']['tm'],2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['jjg']['td'],2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['jjg']['tm'],$biaya[$estate]['pkkprd']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['jjg']['td'],$biaya[$estate]['pkkprd']['tm']),2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd']['tm'],$biaya[$estate]['jjg']['tm'])*1000,2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['prd']['td'],$biaya[$estate]['jjg']['td'])*1000,2)."</td>";
	$tab.="<td align=right></td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['ttlbyypnn']['tm'],0)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['ttlbyypnn']['td'],0)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['ttlbyypnn']['tm'],$biaya[$estate]['prd']['tm'])/1000,2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['ttlbyypnn']['td'],$biaya[$estate]['prd']['td'])/1000,2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['ttlbyybgt']['tm'],0)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['ttlbyybgt']['td'],0)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['ttlbyybgt']['thn'],0)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['ttlbyybgt']['tm'],$biaya[$estate]['prd_bgt']['tm'])/1000,2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['ttlbyybgt']['td'],$biaya[$estate]['prd_bgt']['td'])/1000,2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['ttlbyybgt']['thn'],$biaya[$estate]['prd_bgt']['thn'])/1000,2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['byy_tra']['tm'],0)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['byy_tra']['td'],0)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['byy_tra']['tm'],$biaya[$estate]['prd']['tm'])/1000,2)."</td>";
	$tab.="<td align=right>".numb_format(bagi($biaya[$estate]['byy_tra']['td'],$biaya[$estate]['prd']['td'])/1000,2)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['prd_bgt_dpn']['tm'],0)."</td>";
	$tab.="<td align=right>".numb_format($biaya[$estate]['prd_bgt_dpn']['td'],0)."</td>";
	foreach($head as $id => $vlvl){
		foreach($vlvl as $lvl => $name){
			if($lvl=='2'){
				$tab.="<td align=center></td>";
			}
		}						
	}
	if($proses!='excel'){				
		$tab.="<td align=center><img src=images/skyblue/zoom.png class=zImgBtn onclick=previewpica('".$estate."','".$div."','".$prd."','html','pica')></td>";
		$tab.="<td align=center><img src=images/excel.jpg class=zImgBtn onclick=detailExcel('".$estate."','".$div."','".$prd."','excel','pica')></td>";
	}else{
		$tab.="<td></td>";
		$tab.="<td></td>";
	}
	
	$tab.="</tr>";
}
$nodiv+=1;
# GRAND TOTAL
// $tab.="<tr class=rowcontent name=baristotal[] style=cursor:pointer;background-color:#C9FEFA; title=\"Click untuk show atau hide baris.\" onclick=hiderow('1','".$no."','est')>";
$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#C9FEFA;>";
$tab.="<td align=center id=jumlahbaris colspan=1>".$no."</td>";
$tab.="<td align=left>PT ".$pt."</td>";
$tab.="<td align=center></td>";

$tab.="<td align=center></td>";
$tab.="<td align=center></td>";
$tab.="<td align=center></td>";
$tab.="<td align=center></td>";
$tab.="<td align=center></td>";
$tab.="<td align=right>".numb_format($biaya_gt['ha']['tm'],2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['ha']['tm'],2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['pkk']['tm'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['pkkprd']['tm'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['pkk']['tm']/$biaya_gt['ha']['tm'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['tkpnn']['tm'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['tkpnn']['td'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['luaspnn']['tm'],2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['luaspnn']['td'],2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['luaspnn']['tm'],$biaya_gt['ha']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['luaspnn']['td'],$biaya_gt['ha']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['luaspnn']['tm'],$biaya_gt['tkpnn']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['luaspnn']['td'],$biaya_gt['tkpnn']['td']),2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['prd_bgt']['tm'],2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['prd_bgt']['td'],2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['prd_bgt']['thn'],2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['prd']['tm'],2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['prd']['td'],2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['prd_smly']['tm'],2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['prd_smly']['td'],2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd_bgt']['tm'],$biaya_gt['ha']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd_bgt']['td'],$biaya_gt['ha']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd_bgt']['thn'],$biaya_gt['ha']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd']['tm'],$biaya_gt['ha']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd']['td'],$biaya_gt['ha']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd_smly']['tm'],$biaya_gt['ha']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd_smly']['td'],$biaya_gt['ha']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi(bagi($biaya_gt['prd']['td'],$biaya_gt['ha']['tm']),bagi($biaya_gt['prd_bgt']['td'],$biaya_gt['ha']['tm']))*100-100,2)."</td>";
$tab.="<td align=right>".numb_format(bagi(bagi($biaya_gt['prd']['td'],$biaya_gt['ha']['tm']),bagi($biaya_gt['prd_smly']['td'],$biaya_gt['ha']['tm']))*100-100,2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd_bgt']['tm'],$biaya_gt['tkbgt']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd_bgt']['td'],$biaya_gt['tkbgt']['td']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd_bgt']['thn'],$biaya_gt['tkbgt']['thn']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd']['tm'],$biaya_gt['tkpnn']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd']['td'],$biaya_gt['tkpnn']['td']),2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['brd']['tm'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['brd']['td'],0)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['brd']['tm'],$biaya_gt['prd']['tm'])*100,2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['brd']['td'],$biaya_gt['prd']['td'])*100,2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['jjg']['tm'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['jjg']['td'],0)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['jjg']['tm'],$biaya_gt['pkkprd']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['jjg']['td'],$biaya_gt['pkkprd']['tm']),2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd']['tm'],$biaya_gt['jjg']['tm'])*1000,2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['prd']['td'],$biaya_gt['jjg']['td'])*1000,2)."</td>";
$tab.="<td align=right></td>";
$tab.="<td align=right>".numb_format($biaya_gt['ttlbyypnn']['tm'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['ttlbyypnn']['td'],0)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['ttlbyypnn']['tm'],$biaya_gt['prd']['tm'])/1000,2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['ttlbyypnn']['td'],$biaya_gt['prd']['td'])/1000,2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['ttlbyybgt']['tm'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['ttlbyybgt']['td'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['ttlbyybgt']['thn'],0)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['ttlbyybgt']['tm'],$biaya_gt['prd_bgt']['tm'])/1000,2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['ttlbyybgt']['td'],$biaya_gt['prd_bgt']['td'])/1000,2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['ttlbyybgt']['thn'],$biaya_gt['prd_bgt']['thn'])/1000,2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['byy_tra']['tm'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['byy_tra']['td'],0)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['byy_tra']['tm'],$biaya_gt['prd']['tm'])/1000,2)."</td>";
$tab.="<td align=right>".numb_format(bagi($biaya_gt['byy_tra']['td'],$biaya_gt['prd']['td'])/1000,2)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['prd_bgt_dpn']['tm'],0)."</td>";
$tab.="<td align=right>".numb_format($biaya_gt['prd_bgt_dpn']['td'],0)."</td>";
foreach($head as $id => $vlvl){
	foreach($vlvl as $lvl => $name){
		if($lvl=='2'){
			$tab.="<td align=center></td>";
		}
	}						
}
if($proses!='excel'){				
	$tab.="<td align=center><img src=images/skyblue/zoom.png class=zImgBtn onclick=previewpica('".$estate."','".$div."','".$prd."','html','pica')></td>";
	$tab.="<td align=center><img src=images/excel.jpg class=zImgBtn onclick=detailExcel('".$estate."','".$div."','".$prd."','excel','pica')></td>";
}else{
	$tab.="<td></td>";
	$tab.="<td></td>";
}
	
$tab.="</tr>";
$tab.="</tbody></table>";



switch ($proses) {
    case 'preview':
        echo $tab1.$tab.$tab2;
	break;

    case 'excel':
		$style="cellpadding=5 cellspacing=1 border=1 class=sortable";
		$tab2="<div style=clear:both></div>
			<label>PICA</label>
				<table ".$style.">
				<thead><tr class=rowheader>
					<th align=center>No</th>
					<th align=center>Problem Identification</th>
					<th align=center>Corrective Action (at each problem)</th>
					<th align=center>Outcome</th>
					<th align=center>Mile Stone</th>
					<th align=center>Related Dept Support</th>
					<th align=center>PIC</th>
				</tr>";
				
			$tab2.="
				</thead>
				<tbody>";
			
			$datax = array();
			$str = "SELECT *  FROM " . $dbname . ".kebun_csbm_pica a where 1=1 ".$where." and periode = '".$prd."' order by divisi asc";
			$res = fetchdata($str);
			foreach ($res as $bar){
				$datax[$bar['kodeorg']][$bar['divisi']]=$bar['divisi'];
				$dtisi[$bar['kodeorg']][$bar['divisi']]['problem']=$bar['problem'];
				$dtisi[$bar['kodeorg']][$bar['divisi']]['corrective']=$bar['corrective'];
				$dtisi[$bar['kodeorg']][$bar['divisi']]['outcome']=$bar['outcome'];
				$dtisi[$bar['kodeorg']][$bar['divisi']]['milestone']=$bar['milestone'];
				$dtisi[$bar['kodeorg']][$bar['divisi']]['deptsupport']=$bar['deptsupport'];
				$dtisi[$bar['kodeorg']][$bar['divisi']]['pic']=$bar['pic'];
			}
			
			foreach($datax as $kdorg => $vdiv){
				$no=0;
				foreach($vdiv as $divisi){
					$no++;
					$tab2.="<tr class=rowcontent>";
					$tab2.="<td colspan=7><b>".getNamaOrg($divisi)."</b></td>";
					$tab2.="</tr>";
					$tab2.="<tr class=rowcontent>";
					$tab2.="<td valign=top align=center>".$no."</td>";
					$tab2.="<td valign=top align=left>".nl2br($dtisi[$kdorg][$divisi]['problem'])."</td>";
					$tab2.="<td valign=top align=left>".nl2br($dtisi[$kdorg][$divisi]['corrective'])."</td>";
					$tab2.="<td valign=top align=left>".nl2br($dtisi[$kdorg][$divisi]['outcome'])."</td>";
					$tab2.="<td valign=top align=left>".$dtisi[$kdorg][$divisi]['milestone']."</td>";
					$tab2.="<td valign=top align=left>".$dtisi[$kdorg][$divisi]['deptsupport']."</td>";
					$tab2.="<td valign=top align=left>".$dtisi[$kdorg][$divisi]['pic']."</td>";							
					$tab2.="</tr>";
				}
			}
			
			$tab2.="
			</tbody>
		</table>";
	
        $nop = "csbm.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet('csbm', $tab);
		$xls->addSheet('pica', $tab2);
		$xls->headers($nop);
		echo $xls->buildFile();
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
	$n = hidezerodecimal($a,$d);
	#$n = number_format($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
function bagi($e,$i){
	if($i!='' and $i!='0'){
		$n=$e/$i;
	}else{
		$n=0;
	}
	return $n;
}
?>
