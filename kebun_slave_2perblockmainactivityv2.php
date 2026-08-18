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
$kolomhide= checkPostGet('kolomhide', '');
$barishide= checkPostGet('barishide', '');
$jenis    = checkPostGet('jenis', '');
$status   = checkPostGet('status', '');

$arrbi    = explode('-',$prd); 
$tahun    = $arrbi[0]; 
$bulan    = $arrbi[1];
$periode1 = $tahun."-01";
$periode2 = $tahun."-12";
$periode2 = $prd;

$periodelalu1 = ($tahun-1)."-01";
$periodelalu2 = ($tahun-1)."-".$bulan;
$periodelalusetahun2 = ($tahun-1)."-12";

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

$where=$where2=$where_spb=$whereJ=$whtbs=$whrpkmat=$wheremat="";
if($pt!=''){
	$where=" and substr(a.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$where2=" and substr(a.kodeblok,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$where_spb=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$whereJ=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
	$whtbs=" and a.supplierid in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
	$whrpkmat=" and substr(a.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
}
if($kdorg!=''){
	$where=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$where2=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
	$where_spb=" and a.kodeorg ='".$kdorg."'";
	$whereJ=" and a.kodeorg ='".$kdorg."'";
	$whtbs=" and a.supplierid ='".$kdorg."'";
	$whrpkmat=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
}

$wh="";$wh2="";$whB="";$wh_spb="";$wh_bgt=$wh_bgtrp='';
if($divisi!=''){
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
	$wh2.=" and a.tahuntanam='".$tt."'";
	$whB.=" and a.thntnm='".$tt."'";
	$wh_spb.=" and a.tahuntanam='".$tt."'";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
	$whrpkmat.=" and a.kodeorg in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
}
if($ip!=''){
	$whereJ.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$wh.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbname.".bgt_blok where intiplasma='".$ip."')";
	$wh2.=" and a.intiplasma='".$ip."'";
	$whB.=" and a.intiplasma='".$ip."'";
	$wh_spb.=" and a.intiplasma='".$ip."'";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where intiplasma='".$ip."')";
	$whrpkmat.=" and a.kodeorg in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
}


#HARGA TBS
#harga jual tbs
$str = "select substr(tanggal,1,7) as periode, a.* from " . $dbname . ".pmn_hargabelitbs a where 1=1 ".$whtbs." and substr(tanggal,1,7) between '".$periodelalu1."' and  '".$periode2."' group by supplierid, tanggal"; 
$res = fetchdata($str);
$jlhpembagi=$dataselltbs=$jlhpembagilalu=$hargatbslalu=$hargatbs=$dataunitharga=array();
foreach($res as $bar){
	if($bar['periode']>=$periode1 and $bar['periode']<=$periode2){		
		$hargapertgl[$bar['supplierid']][$bar['periode']]+=$bar['harga'];
		$jlhpembagi[$bar['supplierid']][$bar['periode']]+=1;
		$dataselltbs[$bar['supplierid']][$bar['periode']]=$bar['periode'];
	}

	if($bar['periode']>=$periodelalu1 and $bar['periode']<=$periodelalusetahun2){
		$hargapertgllalu[$bar['supplierid']]+=$bar['harga'];
		$jlhpembagilalu[$bar['supplierid']]+=1;
	}
	
	$dataunitharga[$bar['supplierid']]=$bar['supplierid'];
}
foreach($dataselltbs as $kdunit => $valprd){
	foreach($valprd as $prdtbs){
		if($jlhpembagi[$kdunit][$prdtbs]>0){			
			$hargatbs[$kdunit][$prdtbs]=$hargapertgl[$kdunit][$prdtbs]/$jlhpembagi[$kdunit][$prdtbs];
		}
		if($jlhpembagilalu[$kdunit]>0){			
			$hargatbslalu[$kdunit]=$hargapertgllalu[$kdunit]/$jlhpembagilalu[$kdunit];
		}
	}
}
$adaharga=0;$hargabgt=array();
$str = "select * from " . $dbname . ".bgt_hargatbs a where 1=1 ".$whhrg." and tahun = '".$tahun."'"; 
$res = fetchdata($str);
foreach($res as $bar){
	$hargabgt[$bar['kodeorg']]=$bar['rupiah'];
}
#HARGA TBS

$klpbyy=array('126'=>'TBM','621'=>'TM');
if($status=='TBM'){
	$whakunmat=" and substr(kodekegiatan,1,7) in ('1260108')";
	$whakun=" and noakun in ('1260108')";
}elseif($status=='TM'){
	$whakunmat=" and substr(kodekegiatan,1,7) in ('6210103')";
	$whakun=" and noakun in ('6210103')";
}else{	
	$whakunmat=" and substr(kodekegiatan,1,7) in ('6210103','1260108')";
	$whakun=" and noakun in ('6210103','1260108')";
}
#UNTUK HEADER

$arrjns=array(
	"rp"   =>"Rupiah (000)",
	"rpha" =>"Rupiah (000) / Ha",
	"rppkk"=>"Rupiah (000) / Pokok",
	"rpprd"=>"Rupiah / Kg"
);
if($jenis!='rp'){
	$nmjns="<br><font style=font-size:10px;font-weight:normal;color:grey;>(".$arrjns[$jenis].")</font>";
	
	$headakun=array(
		'ha'               =>'Ha',
		'sph'              =>'SPH',
		'pkk'              =>'Pokok',
		'prd'              =>'Production (Ton)',
		'pnn'              =>'Harvesting Cost '.$nmjns,
		'ppk'              =>'Fertilizing Cost '.$nmjns,
		'tm'               =>'Maintenance Mature Cost '.$nmjns,
		'ohxdep'           =>'Overhead Excl Depre '.$nmjns,
		'tcostprdxdep'     =>'Total Production Cost Excl Depre '.$nmjns,
		'dep'              =>'Depreciation '.$nmjns,
		'tcostprd'         =>'Total Production Cost '.$nmjns,
		'rev'              =>'Total Revenue '.$nmjns,
		'cashprofit'       =>'Total Cash Profit '.$nmjns,
		'grossprofit'      =>'Total Gross Profit '.$nmjns
	);
}else{	
	$headakun=array(
		'ha'               =>'Ha',
		'sph'              =>'SPH',
		'pkk'              =>'Pokok',
		'prd'              =>'Production (Ton)',
		'pnn'              =>'Harvesting Cost (Rp Mn)',
		'ppk'              =>'Fertilizing Cost (Rp Mn)',
		'tm'               =>'Maintenance Mature Cost (Rp Mn)',
		'ohxdep'           =>'Overhead Excl Depre (Rp Mn)',
		'tcostprdxdep'     =>'Total Production Cost Excl Depre (Rp Mn)',
		'dep'              =>'Depreciation (Rp Mn)',
		'tcostprd'         =>'Total Production Cost (Rp Mn)',
		'rev'              =>'Total Revenue (Rp Mn)',
		'cashprofit'       =>'Total Cash Profit (Rp Mn)',
		'grossprofit'      =>'Total Gross Profit (Rp Mn)',
		'tcostprdxdepperkg'=>'Total Production Cost Excl Depre/Kg (Rp)',
		'tcostprdperkg'    =>'Total Production Cost/Kg (Rp)',
		'revperkg'         =>'Revenue/Kg (Rp)',
		'cashprofitperkg'  =>'Total Cash Profit/Kg (Rp)',
		'grossprofitperkg' =>'Gross Profit/Kg (Rp)',
		'grossprofitpersen'=>'Gross Profit %/Kg',
		'yieldha'          =>'Yield/Ha (Ton)',
		'grossprofitperha' =>'Gross Profit/Ha (Rp Mn)',
		'yieldpkk'         =>'Yield/Pkk (Kg)',
		'grossprofitpkk'   =>'Gross Profit/Pkk (Rp Mn)'
	);
}
$headtnpasub=array(
	'ha'               =>'ha',
	'sph'              =>'sph',
	'pkk'              =>'pkk',
	'prd'              =>'prd',
	'ohxdep'           =>'ohxdep',
	'dep'              =>'dep',
	'rev'              =>'rev',
	'cashprofit'       =>'cashprofit',
	'grossprofit'      =>'grossprofit',
	'revperkg'         =>'revperkg',
	'cashprofitperkg'  =>'cashprofitperkg',
	'grossprofitperkg' =>'grossprofitperkg',
	'grossprofitpersen'=>'grossprofitpersen',
	'yieldha'          =>'yieldha',
	'grossprofitperha' =>'grossprofitperha',
	'yieldpkk'         =>'yieldpkk',
	'grossprofitpkk'   =>'grossprofitpkk'
);
$headmaster=array(
	'ha'               =>'ha',
	'sph'              =>'sph',
	'pkk'              =>'pkk',
	'prd'              =>'prd'
);


$arrsubheader=array(
	'lab'  =>'Labor',
	'mat'  =>'Material',
	'tra'  =>'Transport',
	'oth'  =>'Other',
	'ttl'  =>'Total'
);
$arrsubhead1mat=array(
	'ton'  =>'Ton',
	'kgpkk'=>'Kg/Pkk',
	'ttl'  =>'Rupiah'
);

$headnohide=array(
	'yieldha'          =>'yieldha',
	'grossprofitperha' =>'grossprofitperha',
	'yieldpkk'         =>'yieldpkk',
	'grossprofitpkk'   =>'grossprofitpkk'
);

$subheadnohide=array(
	'ytd_ti'=>"YTD-".substr($tahun,2,2),
	'ytd_ll'=>"YTD-".substr(($tahun-1),2,2),
	'aop_fy'=>"AOP FY"
);


$arrsubhead2=$rangebln;
$arrsubhead2["ytd_ti"]="YTD-".substr($tahun,2,2);
$arrsubhead2["ytd_ll"]="YTD-".substr(($tahun-1),2,2);
$arrsubhead2["aop_fy"]="AOP FY";


if($proses=='preview'){	
	$tab.="<input hidden id=colhide value=3>";
	$tab.="<input hidden id=colunhide value=".(count($rangebln)+3).">";
	$tab.="<input hidden id=colhead2 value=".count($arrsubhead2).">";
	$tab.="<input hidden id=jlhbulan value=".count($rangebln).">";
	$tab.="<input hidden id=colhead1 value=".count($arrsubheader).">";
	$tab.="<input hidden id=colhead1mat value=".count($arrsubhead1mat).">";
	$tab.="<input hidden id=xxxx value=".$cols.">";
}
if($style=="style=display:none" and $proses!='excel'){
	$colsmat=$colssub1=$cols=$colspan="3";
}else{
	$cols=count($arrsubheader)*count($arrsubhead2);
	$colsmat=count($arrsubhead1mat)*count($arrsubhead2);
	$colssub1=(count($rangebln)+3);
	$colspan=(count($rangebln)+3);
}

$stylefont="style=font-weight:normal;color:#A0A0A0";
$stylefontbln="style=font-weight:normal;color:#02BC28";
//$tab.="<div class='table-scroll'>";
if($proses!='excel'){	
	$tab.="<table class=sortable cellspacing=1  cellpadding=5>";
}else{
	$tab.="<table border=1 class=sortable cellspacing=1 cellpadding=5>";
}
$tab.="
    <thead>
        <tr class=rowheader>
            <th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['unit']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['divisi']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['blok']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['tahuntanam']."</th>
			";
			foreach($headakun as $akun => $nmakun){
				$font="";
				if(in_array($akun,$headtnpasub)){
					$onclick="";
					$colshead=$colssub1;
					$title="title=\"Click pada kolom Total untuk show atau hide kolom.\"";
				}else{
					$title="title=\"Click untuk show atau hide kolom.\"";
					$onclick="onclick=showhidev2('".$akun."[]','cost".$akun."[]',this.id)";
					$colshead=$cols;
					$font="style=color:#E67E22;";
				}
				if(in_array($akun,$headnohide)){					
					$font=$title="";$colshead=3;
				}
				$tab.="<th align=center colspan=".$colshead." id=head".$akun." ".$onclick." ".$title."><font ".$font.">".$nmakun."</font></th>";
			}
			
			
			
		$tab.="	
        </tr>
        <tr class=rowheader title=\"Click untuk show atau hide kolom.\">";
			
			foreach($headakun as $akun => $nmakun){
				foreach($arrsubheader as $subhead => $nmsubhead){
					$onclicksub="onclick=showhidesubv2('".$akun."_".$subhead."[]',this.id,'head".$akun."')";
					$style2=$style;
					$class="class=".$akun."[]";
					$stylefont2=$stylefont.";color:#E67E22;";
					if($subhead=='ttl'){
						$class="";
						$style2="";
						$stylefont2="style=color:#E67E22;";
					}
					if(in_array($akun,$headnohide)){					
						$onclicksub=$stylefont2="";
						$colssub1=3;
					}
					if(in_array($akun,$headtnpasub)){
						if($subhead=='ttl'){
							$tab.="<th align=center colspan=".$colssub1." ".$class." name=cost".$akun."[] id=sub".$akun.$subhead." ".$style2." ".$onclicksub."><font ".$stylefont2.">".$nmsubhead."</font></th>";
						}
					}else{
						$tab.="<th align=center colspan=".$colssub1." ".$class." name=cost".$akun."[] id=sub".$akun.$subhead." ".$style2." ".$onclicksub."><font ".$stylefont2.">".$nmsubhead."</font></th>";
					}
				}
			}
			
		$tab.="</tr>
		
        <tr class=rowheader>";
			foreach($headakun as $akun => $nmakun){
				foreach($arrsubheader as $subhead => $nmsubhead){
					$arrbulan=$arrsubhead2;
					if(in_array($akun,$headnohide)){
						$arrbulan=$subheadnohide;
					}
					foreach($arrbulan as $subhead2 => $nmsubhead2){
						if($akun=='yieldha' or $akun=='yieldpkk'){
							if($subhead2=='ytd_ll'){
								$nmsubhead2='AOP'; #AOP == budget sdbi
							}
						}
						$style2=$style; $name="";
						$class="class=".$akun."[]";
						$stylefontbln2=$stylefontbln;
						if($subhead=='ttl'){
							if(substr($subhead2,0,3)=='ytd' or substr($subhead2,0,3)=='aop'){
								$class="";
								$style2="";
								$stylefontbln2="";
							}else{
								$class="class=bln".$akun."[]";
								$name="name=".$akun."_".$subhead."[]";
							}
						}else{
							if(substr($subhead2,0,3)=='ytd' or substr($subhead2,0,3)=='aop'){								
								$stylefontbln2="style=font-weight:normal;";
							}else{
								$class="class=bln".$akun."[]";
								$name="name=".$akun."_".$subhead."[]";
							}
						}
						if(in_array($akun,$headtnpasub)){
							if($subhead=='ttl'){								
								$tab.="<th align=center ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".$nmsubhead2."</font></th>";
							}
						}else{
							$tab.="<th align=center ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".$nmsubhead2."</font></th>";
						}
					}
				}
			}
			
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
	
	#luas ha
	$biaya[$bar['kodeorg']]['ha']['ttl'][$prdaresta]=$bar['luasareaproduktif'];
	#pokok
	$biaya[$bar['kodeorg']]['pkk']['ttl'][$prdaresta]=$bar['jumlahpokok'];	
	#sph
	$biaya[$bar['kodeorg']]['sph']['ttl'][$prdaresta]=$bar['jumlahpokok']/$bar['luasareaproduktif'];
	
	if($prdaresta==$prd){
		$biaya[$bar['kodeorg']]['ha']['ttl']['ytd_ti']=$bar['luasareaproduktif'];
		$biaya[$bar['kodeorg']]['pkk']['ttl']['ytd_ti']=$bar['jumlahpokok'];
		$biaya[$bar['kodeorg']]['sph']['ttl']['ytd_ti']=$bar['jumlahpokok']/$bar['luasareaproduktif'];		
	}
	if($prdaresta==($tahun-1)."-".$bulan){
		#luaslalu
		$biaya[$bar['kodeorg']]['ha']['ttl']['ytd_ll']=$bar['luasareaproduktif'];
		#pokoklalu
		$biaya[$bar['kodeorg']]['pkk']['ttl']['ytd_ll']=$bar['jumlahpokok'];
		#sph
		$biaya[$bar['kodeorg']]['sph']['ttl']['ytd_ll']=$bar['jumlahpokok']/$bar['luasareaproduktif'];
	}
	if($prdaresta==$prd){
		$ada+=1;
	}
}
if($ada==0){
	$str="select * from ".$dbname.".setup_blok a where 1=1 ".$wh2." ".$where." and a.statusblok='TM' and a.luasareaproduktif>0 order by a.kodeorg";
	$res = fetchdata($str);
	foreach($res as $bar){
		$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,6)][$bar['kodeorg']]=$bar['kodeorg'];
		
		#luas ha
		$biaya[$bar['kodeorg']]['ha']['ttl'][$prd]=$bar['luasareaproduktif'];
		$biaya[$bar['kodeorg']]['ha']['ttl']['ytd_ti']=$bar['luasareaproduktif'];
		
		#pokok
		$biaya[$bar['kodeorg']]['pkk']['ttl'][$prd]=$bar['jumlahpokok'];
		$biaya[$bar['kodeorg']]['pkk']['ttl']['ytd_ti']=$bar['jumlahpokok'];
		
		#sph
		$biaya[$bar['kodeorg']]['sph']['ttl'][$prd]=$bar['jumlahpokok']/$bar['luasareaproduktif'];
		$biaya[$bar['kodeorg']]['sph']['ttl']['ytd_ti']=$bar['jumlahpokok']/$bar['luasareaproduktif'];
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
	$biaya[$bar['kodeblok']]['ha']['ttl']['aop_fy']=$bar['hathnini'];
	#pokok
	$biaya[$bar['kodeblok']]['pkk']['ttl']['aop_fy']=$bar['pokokthnini'];
	#sph
	if($bar['hathnini']!='0'){		
		$biaya[$bar['kodeblok']]['sph']['ttl']['aop_fy']=$bar['pokokthnini']/$bar['hathnini'];	
	}
}
	
	
#ambil prd real
$prdton=$prdtontitle=array();
$str = "select sum(kgwb) as kgwb, substr(tanggal,1,7) as periode, blok from " . $dbname . ".kebun_spb_vw a  where 1=1 ".$wh_spb." ".$where_spb." and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' group by periode, blok"; 
$res = fetchdata($str);
foreach($res as $bar){
	#produksi
	$biaya[$bar['blok']]['prd']['ttl'][$bar['periode']]+=$bar['kgwb']/1000;
	$biaya[$bar['blok']]['prd']['ttl']['ytd_ti']+=$bar['kgwb']/1000;
	
	$prdtontitle[$bar['blok']]+=($bar['kgwb']/1000);
}

#ambil prd real tahun lalu
$str = "select sum(kgwb) as kgwb, substr(tanggal,1,7) as periode, blok from " . $dbname . ".kebun_spb_vw a  where 1=1 ".$wh_spb." ".$where_spb." and substr(tanggal,1,7) between '".$periodelalu1."' and  '".$periodelalu2."' group by blok"; 
$res = fetchdata($str);
foreach($res as $bar){	
	#produksi
	$biaya[$bar['blok']]['prd']['ttl']['ytd_ll']+=$bar['kgwb']/1000;
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
	
	
	
	#produksi bgt
	$biaya[$bar['kodeblok']]['prd']['ttl']['aop_fy']+=$bar['kgsetahun']/1000;
	$prdbgtsdbi[$bar['kodeblok']]['prd']['ttl']['ytd_ll']+=$bar['sdbi']/1000; #isinya aop = bgt sdbi
	$prdbgtbidiv[substr($bar['kodeblok'],0,6)]['prd']['ttl']['ytd_ll'] += ($bar['sdbi']/1000);
	$prdbgtbiest[substr($bar['kodeblok'],0,4)]['prd']['ttl']['ytd_ll'] += ($bar['sdbi']/1000);
	$prdbgtbigt['prd']['ttl']['ytd_ll'] += ($bar['sdbi']/1000);
}


$akunupahpnn =array('6110101','6110102');
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
		$kodeblok[$bar['kodeorg']][$bar['kodeorg']][$bar['kodeorg']]=$bar['kodeorg'];
		$bar['kodeblok']=$bar['kodeorg'];
	}
	if(substr($bar['noakun'],0,3)=='611'){
		#biaya panen
		if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akunupahpnn)){
			#labor
			$biaya[$bar['kodeblok']]['pnn']['lab'][$bar['periode']]+=($bar['jumlah']/1000);
			$biaya[$bar['kodeblok']]['pnn']['lab']['ytd_ti']+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)=='INV'){
			#material
			$biaya[$bar['kodeblok']]['pnn']['mat'][$bar['periode']]+=($bar['jumlah']/1000);
			$biaya[$bar['kodeblok']]['pnn']['mat']['ytd_ti']+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akuntranspnn)){
			#transport
			$biaya[$bar['kodeblok']]['pnn']['tra'][$bar['periode']]+=($bar['jumlah']/1000);
			$biaya[$bar['kodeblok']]['pnn']['tra']['ytd_ti']+=($bar['jumlah']/1000);
		}else{
			$biaya[$bar['kodeblok']]['pnn']['oth'][$bar['periode']]+=($bar['jumlah']/1000);
			$biaya[$bar['kodeblok']]['pnn']['oth']['ytd_ti']+=($bar['jumlah']/1000);
		}
		$biaya[$bar['kodeblok']]['pnn']['ttl'][$bar['periode']]+=($bar['jumlah']/1000);
		$biaya[$bar['kodeblok']]['pnn']['ttl']['ytd_ti']+=($bar['jumlah']/1000);
	}
	
	
	if(substr($bar['noakun'],0,3)=='621'){
		#TM
		if($bar['noakun']=='6210103'){
			#biaya pupuk
			if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['kodekegiatan'],$lbrpupuk)){
				#labor
				$biaya[$bar['kodeblok']]['ppk']['lab'][$bar['periode']]+=($bar['jumlah']/1000);
				$biaya[$bar['kodeblok']]['ppk']['lab']['ytd_ti']+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$biaya[$bar['kodeblok']]['ppk']['mat'][$bar['periode']]+=($bar['jumlah']/1000);
				$biaya[$bar['kodeblok']]['ppk']['mat']['ytd_ti']+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and (in_array($bar['kodekegiatan'],$transpupuk) or substr($bar['kodejurnal'],0,3)=='VHC')){
				#transport
				$biaya[$bar['kodeblok']]['ppk']['tra'][$bar['periode']]+=($bar['jumlah']/1000);
				$biaya[$bar['kodeblok']]['ppk']['tra']['ytd_ti']+=($bar['jumlah']/1000);
			}else{
				$biaya[$bar['kodeblok']]['ppk']['oth'][$bar['periode']]+=($bar['jumlah']/1000);
				$biaya[$bar['kodeblok']]['ppk']['oth']['ytd_ti']+=($bar['jumlah']/1000);
			}
			$biaya[$bar['kodeblok']]['ppk']['ttl'][$bar['periode']]+=($bar['jumlah']/1000);
			$biaya[$bar['kodeblok']]['ppk']['ttl']['ytd_ti']+=($bar['jumlah']/1000);
		}else{
			#biaya pemel
			if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)!='VHC'){
				#labor
				$biaya[$bar['kodeblok']]['tm']['lab'][$bar['periode']]+=($bar['jumlah']/1000);
				$biaya[$bar['kodeblok']]['tm']['lab']['ytd_ti']+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$biaya[$bar['kodeblok']]['tm']['mat'][$bar['periode']]+=($bar['jumlah']/1000);
				$biaya[$bar['kodeblok']]['tm']['mat']['ytd_ti']+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
				#transport
				$biaya[$bar['kodeblok']]['tm']['tra'][$bar['periode']]+=($bar['jumlah']/1000);
				$biaya[$bar['kodeblok']]['tm']['tra']['ytd_ti']+=($bar['jumlah']/1000);
			}else{
				$biaya[$bar['kodeblok']]['tm']['oth'][$bar['periode']]+=($bar['jumlah']/1000);
				$biaya[$bar['kodeblok']]['tm']['oth']['ytd_ti']+=($bar['jumlah']/1000);
			}
			$biaya[$bar['kodeblok']]['tm']['ttl'][$bar['periode']]+=($bar['jumlah']/1000);
			$biaya[$bar['kodeblok']]['tm']['ttl']['ytd_ti']+=($bar['jumlah']/1000);
		}
	}
}

$str = "select kodeorg, sum(jumlah) as jumlah, periode,noakun  
from " . $dbname . ".keu_jurnaldt_vw a      
where 1=1 ".$whereJ." and periode between '".$periode1."' and  '".$periode2."' ".$whakunumum."     
group by noakun,kodeorg,periode"; 
$res = fetchdata($str);
foreach($res as $bar){
	#BIAYA UMUM TAHUN INI
	if(substr($bar['noakun'],0,3)=='715'){
		#depre
		$biayaoh[$bar['kodeorg']]['dep']['ttl'][$bar['periode']]+=($bar['jumlah']/1000);
		$biayaoh[$bar['kodeorg']]['dep']['ttl']['ytd_ti']+=($bar['jumlah']/1000);
	}else{
		$biayaoh[$bar['kodeorg']]['ohxdep']['ttl'][$bar['periode']]+=($bar['jumlah']/1000);
		$biayaoh[$bar['kodeorg']]['ohxdep']['ttl']['ytd_ti']+=($bar['jumlah']/1000);
	}
}

$str = "select kodeorg, sum(jumlah) as jumlah, periode,noakun  
from " . $dbname . ".keu_jurnaldt_vw a      
where 1=1 ".$whereJ." and periode between '".$periodelalu1."' and  '".$periodelalu2."' ".$whakunumum."     
group by noakun,kodeorg,periode"; 
$res = fetchdata($str);
foreach($res as $bar){
	#BIAYA UMUM TAHUN LALU
	if(substr($bar['noakun'],0,3)=='715'){
		#depre
		$biayaoh[$bar['kodeorg']]['dep']['ttl']['ytd_ll']+=($bar['jumlah']/1000);
	}else{
		$biayaoh[$bar['kodeorg']]['ohxdep']['ttl']['ytd_ll']+=($bar['jumlah']/1000);
	}
}


# biaya tahun lalu
#KHUSUS BYY LAPANGAN
$str = "select a.kodekegiatan,kodeorg,a.kodeblok, sum(jumlah) as jumlah, periode,noakun,kodejurnal 
from " . $dbname . ".keu_jurnaldt_vw a   
where 1=1 ".$whereJ." and periode between '".$periodelalu1."' and  '".$periodelalu2."' ".$whakun."     
group by kodeblok,periode,noakun,kodejurnal,kodekegiatan"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['kodeblok']==''){
		$kodeblok[$bar['kodeorg']][$bar['kodeorg']][$bar['kodeorg']]=$bar['kodeorg'];
		$bar['kodeblok']=$bar['kodeorg'];
	}
	if(substr($bar['noakun'],0,3)=='611'){
		#biaya panen
		if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akunupahpnn)){
			#labor
			$biaya[$bar['kodeblok']]['pnn']['lab']['ytd_ll']+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)=='INV'){
			#material
			$biaya[$bar['kodeblok']]['pnn']['mat']['ytd_ll']+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akuntranspnn)){
			#transport
			$biaya[$bar['kodeblok']]['pnn']['tra']['ytd_ll']+=($bar['jumlah']/1000);
		}else{
			$biaya[$bar['kodeblok']]['pnn']['oth']['ytd_ll']+=($bar['jumlah']/1000);
		}
		$biaya[$bar['kodeblok']]['pnn']['ttl']['ytd_ll']+=($bar['jumlah']/1000);
	}
	
	
	if(substr($bar['noakun'],0,3)=='621'){
		#TM
		if($bar['noakun']=='6210103'){
			#biaya pupuk
			if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['kodekegiatan'],$lbrpupuk)){
				#labor
				$biaya[$bar['kodeblok']]['ppk']['lab']['ytd_ll']+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$biaya[$bar['kodeblok']]['ppk']['mat']['ytd_ll']+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and (in_array($bar['kodekegiatan'],$transpupuk) or substr($bar['kodejurnal'],0,3)=='VHC')){
				#transport
				$biaya[$bar['kodeblok']]['ppk']['tra']['ytd_ll']+=($bar['jumlah']/1000);
			}else{
				$biaya[$bar['kodeblok']]['ppk']['oth']['ytd_ll']+=($bar['jumlah']/1000);
			}
			$biaya[$bar['kodeblok']]['ppk']['ttl']['ytd_ll']+=($bar['jumlah']/1000);
		}else{
			#biaya pemel
			if(substr($bar['kodejurnal'],0,3)!='INV'){
				#labor
				$biaya[$bar['kodeblok']]['tm']['lab']['ytd_ll']+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$biaya[$bar['kodeblok']]['tm']['mat']['ytd_ll']+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
				#transport
				$biaya[$bar['kodeblok']]['tm']['tra']['ytd_ll']+=($bar['jumlah']/1000);
			}else{
				$biaya[$bar['kodeblok']]['tm']['oth']['ytd_ll']+=($bar['jumlah']/1000);
			}
			$biaya[$bar['kodeblok']]['tm']['ttl']['ytd_ll']+=($bar['jumlah']/1000);
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

#ini khusus budget kebun
$str=" select tipebudget,kodebudget,noakun,sum".$e." as sdbi,sum(rp".$bulan.") as bi,sum(rupiah) as rupiah,tahunbudget,kodeorg,sum".$s." as sdbivol,sum(fis".$bulan.") as bivol,sum(jumlah) as jumlah, satuanj,sum(volume) as volume,sum".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whakun." group by noakun, kodebudget, kodeorg";
$res = fetchdata($str);
foreach($res as $bar){
	if(substr($bar['noakun'],0,3)=='611'){
		if(substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK'){
			#LABOUR
			$biaya[$bar['kodeorg']]['pnn']['lab']['aop_fy']+=($bar['rupiah']/1000);
		}else if(substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL'){
			#MATERIAL			
			$biaya[$bar['kodeorg']]['pnn']['mat']['aop_fy']+=($bar['rupiah']/1000);
		}else if($bar['kodebudget']=='VHC'){
			#TRANS
			$biaya[$bar['kodeorg']]['pnn']['tra']['aop_fy']+=($bar['rupiah']/1000);
		}else{
			#OTHER
			$biaya[$bar['kodeorg']]['pnn']['oth']['aop_fy']+=($bar['rupiah']/1000);
		}
		$biaya[$bar['kodeorg']]['pnn']['ttl']['aop_fy']+=($bar['rupiah']/1000);
	}
	
	if(substr($bar['noakun'],0,3)=='621'){
		if($bar['noakun']=='6210103'){
			#PUPUK
			if(substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK'){
				#LABOUR
				$biaya[$bar['kodeorg']]['ppk']['lab']['aop_fy']+=($bar['rupiah']/1000);
			}else if(substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL'){
				#MATERIAL			
				$biaya[$bar['kodeorg']]['ppk']['mat']['aop_fy']+=($bar['rupiah']/1000);
			}else if($bar['kodebudget']=='VHC'){
				#TRANS
				$biaya[$bar['kodeorg']]['ppk']['tra']['aop_fy']+=($bar['rupiah']/1000);
			}else {
				#OTHER
				$biaya[$bar['kodeorg']]['ppk']['oth']['aop_fy']+=($bar['rupiah']/1000);
			}
			$biaya[$bar['kodeorg']]['ppk']['ttl']['aop_fy']+=($bar['rupiah']/1000);
		}else{			
			if((substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK')){
				#LABOUR
				$biaya[$bar['kodeorg']]['tm']['lab']['aop_fy']+=($bar['rupiah']/1000);
			}else if((substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL')){
				#MATERIAL			
				$biaya[$bar['kodeorg']]['tm']['mat']['aop_fy']+=($bar['rupiah']/1000);
			}else if($bar['kodebudget']=='VHC'){
				#TRANS
				$biaya[$bar['kodeorg']]['tm']['tra']['aop_fy']+=($bar['rupiah']/1000);
			}else{
				#OTHER
				$biaya[$bar['kodeorg']]['tm']['oth']['aop_fy']+=($bar['rupiah']/1000);
			}
			$biaya[$bar['kodeorg']]['tm']['ttl']['aop_fy']+=($bar['rupiah']/1000);
		}
	}
}

#ini khusus budget UMUM
$str=" select tipebudget,kodebudget,noakun,sum".$e." as sdbi,sum(rp".$bulan.") as bi,sum(rupiah) as rupiah,tahunbudget,kodeorg,sum".$s." as sdbivol,sum(fis".$bulan.") as bivol,sum(jumlah) as jumlah, satuanj,sum(volume) as volume,sum".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." and kodebudget='UMUM' and tahunbudget = '".$tahun."' ".$whereakun." group by noakun, kodeorg";
$res = fetchdata($str);
foreach($res as $bar){
	if(substr($bar['noakun'],0,3)=='715'){
		#depre
		$biayaoh[$bar['kodeorg']]['dep']['ttl']['ytd_aop']+=($bar['rupiah']/1000);
	}else{
		$biayaoh[$bar['kodeorg']]['ohxdep']['ttl']['ytd_aop']+=($bar['rupiah']/1000);
	}	
}
	
#sekarang saya ingat, ini di patok karena yang di ambil cuma Biaya HK dan ini harus sama dengan di lap justifikasi
/* $whrkdj="and (kodejurnal in ('M0','PNN01','PNN02','CT01','BM01','SPK1','M','NOTAD','NOTAK','HPP','TGH01','BM','BK','KM','KK','POT') 
or kodejurnal like 'PRJ%')"; */
#$arrkdjurupah=array('M0','PNN01','PNN02','CT01','BM01','SPK1','M','NOTAD','NOTAK','HPP','TGH01','BM','BK','KM','KK','POT');

$arrtcostprdxdep=array('pnn','ppk','tm','ohxdep');
$arrtcostprd=array('tcostprdxdep','dep');

#HITUNG UNTUK GTTL
foreach($kodeblok as $estate => $valdiv){
	foreach($valdiv as $div => $valkodeblok){
		foreach($valkodeblok as $blok){
			foreach($headakun as $akun => $nmakun){
				foreach($arrsubheader as $subhead => $nmsubhead){
					$arrbulan=$arrsubhead2;
					if(in_array($akun,$headnohide)){
						$arrbulan=$subheadnohide;
					}
					foreach($arrbulan as $bln => $nmsubhead2){
						if(!in_array($akun,$headtnpasub)){
							$biaya[$blok]['gtl'][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
						}else{							
							#proporsi perblok = luas perblok / total luas 1 estate * biaya oh
							$biaya[$blok]['ohxdep']['ttl'][$bln]=($biaya[$blok]['ha']['ttl'][$prd]/$ttlhaest[$estate])*$biayaoh[$estate]['ohxdep']['ttl'][$bln];
							$biaya[$blok]['dep']['ttl'][$bln]=($biaya[$blok]['ha']['ttl'][$prd]/$ttlhaest[$estate])*$biayaoh[$estate]['dep']['ttl'][$bln];
						}
						
						#Total Production Cost Excl Depre (Rp Mn)
						if(in_array($akun,$arrtcostprdxdep)){
							$biaya[$blok]['tcostprdxdep'][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
						}
						#Total Production Cost (Rp Mn)
						if(in_array($akun,$arrtcostprd)){
							$biaya[$blok]['tcostprd'][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
						}
						#Total Revenue (Rp Mn)
						if($akun=='prd'){
							if($bln!='aop_fy' or $bln!='ytd_ll' or $bln!='ytd_ti'){								
								$biaya[$blok]['rev'][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln]*$hargatbs[$estate][$bln];
								$biaya[$blok]['rev'][$subhead]['ytd_ti']+=$biaya[$blok][$akun][$subhead][$bln]*$hargatbs[$estate][$bln];
							}
							if($bln=='ytd_ll'){								
								$biaya[$blok]['rev'][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln]*$hargatbslalu[$estate];
							}
							if($bln=='aop_fy'){								
								$biaya[$blok]['rev'][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln]*$hargabgt[$estate];
							}
						}
						#Cashprofit = (Rev - Tcostprdxdep)
						$biaya[$blok]['cashprofit'][$subhead][$bln]=$biaya[$blok]['rev'][$subhead][$bln]-$biaya[$blok]['tcostprdxdep'][$subhead][$bln];
						#Grossprofit = (Rev - tcostprd)
						$biaya[$blok]['grossprofit'][$subhead][$bln]=$biaya[$blok]['rev'][$subhead][$bln]-$biaya[$blok]['tcostprd'][$subhead][$bln];
					}
				}
			}
		}
	}
}

$hasilbagi=array(
	'tcostprdxdepperkg'=>'tcostprdxdepperkg',
	'tcostprdperkg'    =>'tcostprdperkg',
	'revperkg'         =>'revperkg',
	'cashprofitperkg'  =>'cashprofitperkg',
	'grossprofitperkg' =>'grossprofitperkg',
	'grossprofitpersen'=>'grossprofitpersen',
	'yieldha'          =>'yieldha',
	'grossprofitperha' =>'grossprofitperha',
	'yieldpkk'         =>'yieldpkk',
	'grossprofitpkk'   =>'grossprofitpkk'
);

$digitnf=array(
	'ha'               =>'ha',
	'prd'              =>'prd',
	'yieldha'          =>'yieldha',
	'yieldpkk'         =>'yieldpkk',
	'grossprofitpersen'=>'grossprofitpersen',
	'grossprofitpkk'   =>'grossprofitpkk'
);

if($barishide=='1'){$stylerow="";}else{	$stylerow="style=display:none";}
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
			
			$title="";
			$title.="title=\"Single click untuk memberi tanda.";
			$title.="\nDouble click untuk melihat detail akun.";
			$title.="\nBlok : ".getNamaOrg($blok)."";
			$title.="\nLuas : ".numb_format($nmha[$blok],2)." Ha";
			$title.="\nPokok : ".$nmpkk[$blok]."";
			$title.="\nProduksi : ".numb_format($prdtontitle[$blok],2)." Ton";
			
			$tab.="<tr class=rowcontent ".$stylerow." id=row_".$no." name=".$estate."[] onclick=getmark(this.id); ondblclick=getdetail('".$no."','".$blok."'); ".$title."\">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$estate."</td>";
			$tab.="<td>".$div."</td>";
			$tab.="<td id=kdblok_".$no.">".$blok."</td>";
			$tab.="<td align=center>".$nmtt[$blok]."</td>";
			
			foreach($headakun as $akun => $nmakun){
				foreach($arrsubheader as $subhead => $nmsubhead){
					$arrbulan=$arrsubhead2;
					if(in_array($akun,$headnohide)){
						$arrbulan=$subheadnohide;
					}
					foreach($arrbulan as $bln => $nmsubhead2){
						$style2=$style; $name="";
						$class="class=".$akun."[]";
						$stylefontbln2=$stylefontbln;
						if($subhead=='ttl'){
							if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){
								$class="";
								$style2="";
								$stylefontbln2="";
							}else{
								$class="class=bln".$akun."[]";
								$name="name=".$akun."_".$subhead."[]";
							}
						}else{
							if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){								
								$stylefontbln2="style=font-weight:normal;";
							}else{
								$class="class=bln".$akun."[]";
								$name="name=".$akun."_".$subhead."[]";
							}
						}
						if(in_array($akun,$digitnf)){$nf=2;}else{$nf=0;}
						
						$nocol+=1;
						#=============================================================================================================================
						$biaya[$blok]['tcostprdxdepperkg'][$subhead][$bln] = 0;
						$biaya[$blok]['tcostprdperkg'][$subhead][$bln] = 0;
						$biaya[$blok]['yieldha'][$subhead][$bln] = 0;
						$biaya[$blok]['revperkg'][$subhead][$bln] = 0;
						$biaya[$blok]['cashprofitperkg'][$subhead][$bln] = 0;
						$biaya[$blok]['grossprofitperkg'][$subhead][$bln] = 0;
						$biaya[$blok]['grossprofitpersen'][$subhead][$bln] = 0;
						$biaya[$blok]['grossprofitperha'][$subhead][$bln] = 0;
						$biaya[$blok]['yieldpkk'][$subhead][$bln] = 0;
						$biaya[$blok]['grossprofitpkk'][$subhead][$bln] = 0;
						
						if($biaya[$blok]['prd']['ttl'][$bln]!=''){
							if($subhead!='ttl'){
								$biaya[$blok]['tcostprdxdepperkg'][$subhead][$bln] = $biaya[$blok]['tcostprdxdep'][$subhead][$bln]/$biaya[$blok]['prd']['ttl'][$bln];
								$biaya[$blok]['tcostprdperkg'][$subhead][$bln] = $biaya[$blok]['tcostprd'][$subhead][$bln]/$biaya[$blok]['prd']['ttl'][$bln];
								$biaya[$blok]['revperkg'][$subhead][$bln] = $biaya[$blok]['rev'][$subhead][$bln]/$biaya[$blok]['prd']['ttl'][$bln];
								$biaya[$blok]['cashprofitperkg'][$subhead][$bln] = $biaya[$blok]['cashprofit'][$subhead][$bln]/$biaya[$blok]['prd']['ttl'][$bln];
								$biaya[$blok]['grossprofitperkg'][$subhead][$bln] = $biaya[$blok]['grossprofit'][$subhead][$bln]/$biaya[$blok]['prd']['ttl'][$bln];
								
								if($biaya[$blok]['revperkg'][$subhead][$bln]!=''){									
									$biaya[$blok]['grossprofitpersen'][$subhead][$bln] = ($biaya[$blok]['grossprofitperkg'][$subhead][$bln]/$biaya[$blok]['revperkg'][$subhead][$bln])*100;
								}
								if($biaya[$blok]['ha'][$subhead][$bln]!=''){									
									$biaya[$blok]['grossprofitperha'][$subhead][$bln] = $biaya[$blok]['grossprofit'][$subhead][$bln]/$biaya[$blok]['ha'][$subhead][$bln];
								}
								if($biaya[$blok]['pkk'][$subhead][$bln]!=''){									
									$biaya[$blok]['grossprofitpkk'][$subhead][$bln] = $biaya[$blok]['grossprofit'][$subhead][$bln]/$biaya[$blok]['pkk'][$subhead][$bln];
								}
								
							}
							if($subhead=='ttl'){
								$biaya[$blok]['tcostprdxdepperkg']['ttl'][$bln] = $biaya[$blok]['tcostprdxdep'][$subhead][$bln]/$biaya[$blok]['prd']['ttl'][$bln];
								$biaya[$blok]['tcostprdperkg']['ttl'][$bln] = $biaya[$blok]['tcostprd'][$subhead][$bln]/$biaya[$blok]['prd']['ttl'][$bln];
								$biaya[$blok]['revperkg']['ttl'][$bln] = $biaya[$blok]['rev'][$subhead][$bln]/$biaya[$blok]['prd']['ttl'][$bln];
								$biaya[$blok]['cashprofitperkg']['ttl'][$bln] = $biaya[$blok]['cashprofit'][$subhead][$bln]/$biaya[$blok]['prd']['ttl'][$bln];
								$biaya[$blok]['grossprofitperkg']['ttl'][$bln] = $biaya[$blok]['grossprofit'][$subhead][$bln]/$biaya[$blok]['prd']['ttl'][$bln];
								
								if($biaya[$blok]['revperkg']['ttl'][$bln]!=''){									
									$biaya[$blok]['grossprofitpersen'][$subhead][$bln] = ($biaya[$blok]['grossprofitperkg'][$subhead][$bln]/$biaya[$blok]['revperkg']['ttl'][$bln])*100;
								}
								if($biaya[$blok]['ha']['ttl'][$bln]!=''){									
									$biaya[$blok]['grossprofitperha'][$subhead][$bln] = $biaya[$blok]['grossprofit'][$subhead][$bln]/$biaya[$blok]['ha']['ttl'][$bln];
								}
								if($biaya[$blok]['pkk']['ttl'][$bln]!=''){									
									$biaya[$blok]['grossprofitpkk'][$subhead][$bln] = $biaya[$blok]['grossprofit'][$subhead][$bln]/$biaya[$blok]['pkk']['ttl'][$bln];
								}
							}
						}
						if($bln=='ytd_ll'){
							#isinya bgt sdbi
							if($biaya[$blok]['ha']['ttl']['aop_fy']!=''){
								$biaya[$blok]['yieldha']['ttl'][$bln] = $prdbgtsdbi[$blok]['prd']['ttl'][$bln]/$biaya[$blok]['ha']['ttl']['aop_fy'];
							}
							if($biaya[$blok]['pkk']['ttl']['aop_fy']!=''){
								$biaya[$blok]['yieldpkk']['ttl'][$bln] = ($prdbgtsdbi[$blok]['prd']['ttl'][$bln]/$biaya[$blok]['pkk']['ttl']['aop_fy'])*1000;
							}
						}else{								
							if($biaya[$blok]['ha']['ttl'][$bln]!=''){
								$biaya[$blok]['yieldha']['ttl'][$bln] = $biaya[$blok]['prd']['ttl'][$bln]/$biaya[$blok]['ha']['ttl'][$bln];
							}
							if(isset($biaya)){
								@$biaya[$blok]['yieldpkk']['ttl'][$bln] = (@$biaya[$blok]['prd']['ttl'][$bln]/@$biaya[$blok]['pkk']['ttl'][$bln])*1000;
							}								
						}
						
						
						#=============================================================================================================================
						if(!in_array($akun,$headmaster)){
							$rpperblok=0;
							if($jenis=='rpha'){
								if($biaya[$blok]['ha']['ttl'][$bln]!=''){									
									$rpperblok=$biaya[$blok][$akun][$subhead][$bln]/$biaya[$blok]['ha']['ttl'][$bln];
								}
							}else if($jenis=='rppkk'){
								if($biaya[$blok]['pkk']['ttl'][$bln]!=''){
									$rpperblok=$biaya[$blok][$akun][$subhead][$bln]/$biaya[$blok]['pkk']['ttl'][$bln];
								}
							}else if($jenis=='rpprd'){
								if($biaya[$blok]['prd']['ttl'][$bln]!=''){									
									$rpperblok=$biaya[$blok][$akun][$subhead][$bln]/$biaya[$blok]['prd']['ttl'][$bln];
								}
							}else{
								#isinya rupiah
								$rpperblok=$biaya[$blok][$akun][$subhead][$bln];
							}
						}else{
							#khusus untuk ha, pkk, prd
							$rpperblok=$biaya[$blok][$akun][$subhead][$bln];							
						}
						
						#ISI YG GAK PAKE LABOR MAT DKK
						if(in_array($akun,$headtnpasub)){
							if($subhead=='ttl'){
								$ny="";
								if($biaya[$blok]['yieldha']['ttl']['ytd_ll']!=''){				
									if(($biaya[$blok]['yieldha']['ttl']['ytd_ti']/$biaya[$blok]['yieldha']['ttl']['ytd_ll'])*100>100){
										if($akun=='yieldha' and $bln=='ytd_ti'){
											$ny="style=background-color:green;";
											$green+=1;
											$nobrsgreen.="row_".$no."##";
										}
									}
									if(($biaya[$blok]['yieldha']['ttl']['ytd_ti']/$biaya[$blok]['yieldha']['ttl']['ytd_ll'])*100>=90 and ($biaya[$blok]['yieldha']['ttl']['ytd_ti']/$biaya[$blok]['yieldha']['ttl']['ytd_ll'])*100<=100){
										if($akun=='yieldha' and $bln=='ytd_ti'){											
											$ny="style=background-color:yellow; title=".$biaya[$blok]['yieldha']['ttl']['ytd_ll']."";
											$yellow+=1;
											$nobrsyellow.="row_".$no."##";
										}
									}
									if(($biaya[$blok]['yieldha']['ttl']['ytd_ti']/$biaya[$blok]['yieldha']['ttl']['ytd_ll'])*100<90){
										if($akun=='yieldha' and $bln=='ytd_ti'){
											$ny="style=background-color:red;";
											$red+=1;
											$nobrsred.="row_".$no."##";
										}
									}
								}else{
									if($akun=='yieldha' and $bln=='ytd_ti'){										
										$ny="style=background-color:red;";				
										$nobrsred.="row_".$no."##";
										$red+=1;
									}
								}
								$nobrttl.="row_".$no."##";
								
								#warna cashprofit
								$nc="";
								if($akun=='cashprofit' and $bln=='ytd_ti'){									
									if($biaya[$blok]['cashprofit'][$subhead][$bln]>0){
										$nc="style=background-color:green;";
									}elseif($biaya[$blok]['cashprofit'][$subhead][$bln]<=0){
										$nc="style=background-color:red;";
									}
								}
								#warna grossprofit
								$n="";
								if($akun=='grossprofit' and $bln=='ytd_ti'){
									if($biaya[$blok]['grossprofit'][$subhead][$bln]>0){
										$n="style=background-color:green;";
										$greenp+=1;
										$nobrsgreenp.="row_".$no."##";
										
									}elseif($biaya[$blok]['grossprofit'][$subhead][$bln]<=0){
										$n="style=background-color:red;";
										$redp+=1;
										$nobrsredp.="row_".$no."##";
									}
									$nobrttlp.="row_".$no."##";								
								}
								$tab.="<td align=right ".$class." ".$name." ".$style2." id=".$akun."_".$nocol."_".$no." ".$n." ".$nc." ".$ny."><font ".$stylefontbln2.">".numb_format($rpperblok,$nf)."</font></td>";
							}
						}else{
							$tab.="<td align=right ".$class." ".$name." ".$style2." id=".$akun."_".$nocol."_".$no."><font ".$stylefontbln2.">".numb_format($rpperblok,$nf)."</font></td>";
						}
						
						#TOTAL
						$stdivbiaya[$div][$akun][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
						$stestbiaya[$estate][$akun][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
						$stgtbiaya[$akun][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
					}
				}
			}
			$awal=($no-$row)+1;
			$awalest=($no-$est)+1;
		}
		
		# TOTAL PER DIVISI
		$tab.="<tr class=rowcontent name=baristotal[] style=cursor:pointer;background-color:#E7FCE4; title=\"Click untuk show atau hide baris.\" onclick=hiderow('".$awal."','".$no."','div')>";
		$tab.="<td align=center>".$nodiv."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=left colspan=3>Divisi ".$div."</td>";
	
		foreach($headakun as $akun => $nmakun){
			foreach($arrsubheader as $subhead => $nmsubhead){
				$arrbulan=$arrsubhead2;
				if(in_array($akun,$headnohide)){
					$arrbulan=$subheadnohide;
				}
				foreach($arrbulan as $bln => $nmsubhead2){
					$style2=$style; $name="";
					$class="class=".$akun."[]";
					$stylefontbln2=$stylefontbln;
					if($subhead=='ttl'){
						if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){
							$class="";
							$style2="";
							$stylefontbln2="";
						}else{
							$class="class=bln".$akun."[]";
							$name="name=".$akun."_".$subhead."[]";
						}
					}else{
						if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){								
							$stylefontbln2="style=font-weight:normal;";
						}else{
							$class="class=bln".$akun."[]";
							$name="name=".$akun."_".$subhead."[]";
						}
					}
					if(in_array($akun,$digitnf)){$nf=2;}else{$nf=0;}
					#SPH
					if($akun=='sph'){
						if($stdivbiaya[$div]['ha']['ttl'][$bln]!=''){
							$stdivbiaya[$div][$akun][$subhead][$bln]=$stdivbiaya[$div]['pkk']['ttl'][$bln]/$stdivbiaya[$div]['ha']['ttl'][$bln];
						}
						if($stestbiaya[$estate]['ha']['ttl'][$bln]!=''){
							$stestbiaya[$estate][$akun][$subhead][$bln]=$stestbiaya[$estate]['pkk']['ttl'][$bln]/$stestbiaya[$estate]['ha']['ttl'][$bln];
						}
						if($stgtbiaya['ha']['ttl'][$bln]!=''){
							$stgtbiaya[$akun][$subhead][$bln]=$stgtbiaya['pkk']['ttl'][$bln]/$stgtbiaya['ha']['ttl'][$bln];
						}
					}
					#=============================================================================================================================
/* 					'tcostprdxdepperkg'=>'Total Production Cost Excl Depre/Kg (Rp)',
					'tcostprdperkg'    =>'Total Production Cost/Kg (Rp)',
					'revperkg'         =>'Revenue/Kg (Rp)',
					'cashprofitperkg'  =>'Total Cash Profit/Kg (Rp)',
					'grossprofitperkg' =>'Gross Profit/Kg (Rp)',
					'grossprofitpersen'=>'Gross Profit %/Kg',
					'yieldha'          =>'Yield/Ha (Ton)',
					'grossprofitperha' =>'Gross Profit/Ha (Rp Mn)',
					'yieldpkk'         =>'Yield/Pkk (Kg)',
					'grossprofitpkk'   =>'Gross Profit/Pkk (Rp Mn)'
					
 */					#Tcostprdxdepperkg = (tcostprdxdep / prd)
					#Tcostprdperkg = tcostprd / prd
					#yieldha = prd / ha
					if(in_array($akun,$hasilbagi)){
						if($stdivbiaya[$div]['prd']['ttl'][$bln]!=''){
							$stdivbiaya[$div]['tcostprdxdepperkg'][$subhead][$bln] = $stdivbiaya[$div]['tcostprdxdep'][$subhead][$bln]/$stdivbiaya[$div]['prd']['ttl'][$bln];
							$stdivbiaya[$div]['tcostprdperkg'][$subhead][$bln] = $stdivbiaya[$div]['tcostprd'][$subhead][$bln]/$stdivbiaya[$div]['prd']['ttl'][$bln];
							$stdivbiaya[$div]['revperkg'][$subhead][$bln] = $stdivbiaya[$div]['rev'][$subhead][$bln]/$stdivbiaya[$div]['prd']['ttl'][$bln];
							$stdivbiaya[$div]['cashprofitperkg'][$subhead][$bln] = $stdivbiaya[$div]['cashprofit'][$subhead][$bln]/$stdivbiaya[$div]['prd']['ttl'][$bln];
							$stdivbiaya[$div]['grossprofitperkg'][$subhead][$bln] = $stdivbiaya[$div]['grossprofit'][$subhead][$bln]/$stdivbiaya[$div]['prd']['ttl'][$bln];
							
							if($stdivbiaya[$div]['revperkg']['ttl'][$bln]!=''){								
								$stdivbiaya[$div]['grossprofitpersen'][$subhead][$bln] = ($stdivbiaya[$div]['grossprofitperkg'][$subhead][$bln]/$stdivbiaya[$div]['revperkg']['ttl'][$bln])*100;
							}
							if($stdivbiaya[$div]['ha']['ttl'][$bln]!=''){								
								$stdivbiaya[$div]['grossprofitperha'][$subhead][$bln] = $stdivbiaya[$div]['grossprofit'][$subhead][$bln]/$stdivbiaya[$div]['ha']['ttl'][$bln];
							}
							if($stdivbiaya[$div]['pkk']['ttl'][$bln]!=''){								
								$stdivbiaya[$div]['grossprofitpkk'][$subhead][$bln] = $stdivbiaya[$div]['grossprofit'][$subhead][$bln]/$stdivbiaya[$div]['pkk']['ttl'][$bln];
							}
							
							
						}
						if($stestbiaya[$estate]['prd']['ttl'][$bln]!=''){
							$stestbiaya[$estate]['tcostprdxdepperkg'][$subhead][$bln] = $stestbiaya[$estate]['tcostprdxdep'][$subhead][$bln]/$stestbiaya[$estate]['prd']['ttl'][$bln];
							$stestbiaya[$estate]['tcostprdperkg'][$subhead][$bln] = $stestbiaya[$estate]['tcostprd'][$subhead][$bln]/$stestbiaya[$estate]['prd']['ttl'][$bln];
							$stestbiaya[$estate]['revperkg'][$subhead][$bln] = $stestbiaya[$estate]['rev'][$subhead][$bln]/$stestbiaya[$estate]['prd']['ttl'][$bln];
							$stestbiaya[$estate]['cashprofitperkg'][$subhead][$bln] = $stestbiaya[$estate]['cashprofit'][$subhead][$bln]/$stestbiaya[$estate]['prd']['ttl'][$bln];
							$stestbiaya[$estate]['grossprofitperkg'][$subhead][$bln] = $stestbiaya[$estate]['grossprofit'][$subhead][$bln]/$stestbiaya[$estate]['prd']['ttl'][$bln];
							
							if($stestbiaya[$estate]['revperkg']['ttl'][$bln]!=''){								
								$stestbiaya[$estate]['grossprofitpersen'][$subhead][$bln] = ($stestbiaya[$estate]['grossprofitperkg'][$subhead][$bln]/$stestbiaya[$estate]['revperkg']['ttl'][$bln])*100;
							}
							
							if($stestbiaya[$estate]['ha']['ttl'][$bln]!=''){								
								$stestbiaya[$estate]['grossprofitperha'][$subhead][$bln] = $stestbiaya[$estate]['grossprofit'][$subhead][$bln]/$stestbiaya[$estate]['ha']['ttl'][$bln];
							}
							
							if($stestbiaya[$estate]['pkk']['ttl'][$bln]!=''){								
								$stestbiaya[$estate]['grossprofitpkk'][$subhead][$bln] = $stestbiaya[$estate]['grossprofit'][$subhead][$bln]/$stestbiaya[$estate]['pkk']['ttl'][$bln];
							}
							
						}
						if($stgtbiaya['prd']['ttl'][$bln]!=''){
							$stgtbiaya['tcostprdxdepperkg'][$subhead][$bln] = $stgtbiaya['tcostprdxdep'][$subhead][$bln]/$stgtbiaya['prd']['ttl'][$bln];
							$stgtbiaya['tcostprdperkg'][$subhead][$bln] = $stgtbiaya['tcostprd'][$subhead][$bln]/$stgtbiaya['prd']['ttl'][$bln];
							$stgtbiaya['revperkg'][$subhead][$bln] = $stgtbiaya['rev'][$subhead][$bln]/$stgtbiaya['prd']['ttl'][$bln];
							$stgtbiaya['cashprofitperkg'][$subhead][$bln] = $stgtbiaya['cashprofit'][$subhead][$bln]/$stgtbiaya['prd']['ttl'][$bln];
							$stgtbiaya['grossprofitperkg'][$subhead][$bln] = $stgtbiaya['grossprofit'][$subhead][$bln]/$stgtbiaya['prd']['ttl'][$bln];
						
							if($stgtbiaya['revperkg']['ttl'][$bln]!=''){								
								$stgtbiaya['grossprofitpersen'][$subhead][$bln] = ($stgtbiaya['grossprofitperkg'][$subhead][$bln]/$stgtbiaya['revperkg']['ttl'][$bln])*100;
							}
							
							if($stgtbiaya['ha']['ttl'][$bln]!=''){								
								$stgtbiaya['grossprofitperha'][$subhead][$bln] = $stgtbiaya['grossprofit'][$subhead][$bln]/$stgtbiaya['ha']['ttl'][$bln];
							}
							
							if($stgtbiaya['pkk']['ttl'][$bln]!=''){								
								$stgtbiaya['grossprofitpkk'][$subhead][$bln] = $stgtbiaya['grossprofit'][$subhead][$bln]/$stgtbiaya['pkk']['ttl'][$bln];
							}
							
						}
						
						if($bln=='ytd_ll'){
							if($stdivbiaya[$div]['ha']['ttl']['aop_fy']!=''){									
								$stdivbiaya[$div]['yieldha'][$subhead][$bln] = $prdbgtbidiv[$div]['prd']['ttl'][$bln]/$stdivbiaya[$div]['ha']['ttl']['aop_fy'];
							}
							if($stdivbiaya[$div]['pkk']['ttl']['aop_fy']!=''){									
								$stdivbiaya[$div]['yieldpkk'][$subhead][$bln] = ($prdbgtbidiv[$div]['prd']['ttl'][$bln]/$stdivbiaya[$div]['pkk']['ttl']['aop_fy'])*1000;
							}
						}else{								
							if($stdivbiaya[$div]['ha']['ttl'][$bln]!=''){
								$stdivbiaya[$div]['yieldha'][$subhead][$bln] = $stdivbiaya[$div]['prd']['ttl'][$bln]/$stdivbiaya[$div]['ha']['ttl'][$bln];
							}
							if($stdivbiaya[$div]['pkk']['ttl'][$bln]!=''){
								$stdivbiaya[$div]['yieldpkk'][$subhead][$bln] = ($stdivbiaya[$div]['prd']['ttl'][$bln]/$stdivbiaya[$div]['pkk']['ttl'][$bln])*1000;
							}
						}
						if($bln=='ytd_ll'){
							if($stestbiaya[$estate]['ha']['ttl']['aop_fy']!=''){									
								$stestbiaya[$estate]['yieldha'][$subhead][$bln] = $prdbgtbiest[$estate]['prd']['ttl'][$bln]/$stestbiaya[$estate]['ha']['ttl']['aop_fy'];
							}
							if($stestbiaya[$estate]['pkk']['ttl']['aop_fy']!=''){									
								$stestbiaya[$estate]['yieldpkk'][$subhead][$bln] = ($prdbgtbiest[$estate]['prd']['ttl'][$bln]/$stestbiaya[$estate]['pkk']['ttl']['aop_fy'])*1000;
							}
						}else{								
							if($stestbiaya[$estate]['ha']['ttl'][$bln]!=''){
								$stestbiaya[$estate]['yieldha'][$subhead][$bln] = $stestbiaya[$estate]['prd']['ttl'][$bln]/$stestbiaya[$estate]['ha']['ttl'][$bln];
							}
							if($stestbiaya[$estate]['pkk']['ttl'][$bln]!=''){
								$stestbiaya[$estate]['yieldpkk'][$subhead][$bln] = ($stestbiaya[$estate]['prd']['ttl'][$bln]/$stestbiaya[$estate]['pkk']['ttl'][$bln])*1000;
							}
						}
							
						if($bln=='ytd_ll'){
							if($stgtbiaya['ha']['ttl']['aop_fy']!=''){
								$stgtbiaya['yieldha'][$subhead][$bln] = $prdbgtbigt['prd']['ttl'][$bln]/$stgtbiaya['ha']['ttl']['aop_fy'];
							}
							if($stgtbiaya['pkk']['ttl']['aop_fy']!=''){
								$stgtbiaya['yieldpkk'][$subhead][$bln] = ($prdbgtbigt['prd']['ttl'][$bln]/$stgtbiaya['pkk']['ttl']['aop_fy'])*1000;
							}
						}else{
							if($stgtbiaya['ha']['ttl'][$bln]!=''){
								$stgtbiaya['yieldha'][$subhead][$bln] = $stgtbiaya['prd']['ttl'][$bln]/$stgtbiaya['ha']['ttl'][$bln];
							}
							if($stgtbiaya['pkk']['ttl'][$bln]!=''){
								$stgtbiaya['yieldpkk'][$subhead][$bln] = ($stgtbiaya['prd']['ttl'][$bln]/$stgtbiaya['pkk']['ttl'][$bln])*1000;
							}
						}
					}
					#=============================================================================================================================
					if(!in_array($akun,$headmaster)){
						$stdivrpha=0;
						if($jenis=='rpha'){
							if($stdivbiaya[$div]['ha']['ttl'][$bln]!=''){
								$stdivrpha=$stdivbiaya[$div][$akun][$subhead][$bln]/$stdivbiaya[$div]['ha']['ttl'][$bln];
							}
						}elseif($jenis=='rppkk'){
							if($stdivbiaya[$div]['pkk']['ttl'][$bln]!=''){
								$stdivrpha=$stdivbiaya[$div][$akun][$subhead][$bln]/$stdivbiaya[$div]['pkk']['ttl'][$bln];
							}
						}elseif($jenis=='rpprd'){
							if($stdivbiaya[$div]['prd']['ttl'][$bln]!=''){
								$stdivrpha=$stdivbiaya[$div][$akun][$subhead][$bln]/$stdivbiaya[$div]['prd']['ttl'][$bln];
							}
						}else{
							#isinya rupiah
							$stdivrpha=$stdivbiaya[$div][$akun][$subhead][$bln];
						}
					}else{
						#khusus untuk ha, pkk, prd
						$stdivrpha=$stdivbiaya[$div][$akun][$subhead][$bln];
					}
					if(in_array($akun,$headtnpasub)){
						if($subhead=='ttl'){
							$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stdivrpha,$nf)."</font></td>";
						}
					}else{
						$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stdivrpha,$nf)."</font></td>";
						
					}
				}
			}
		}
		
		

		$tab.="</tr>";
	}
	$nodiv+=1;
	# TOTAL ESTATE
	$tab.="<tr class=rowcontent name=baristotal[] style=cursor:pointer;background-color:#C9FEFA; title=\"Click untuk show atau hide baris.\" onclick=hiderow('".$awalest."','".$no."','est')>";
	$tab.="<td align=center>".$nodiv."</td>";
	$tab.="<td align=left colspan=4>Unit ".$estate."</td>";
	
	foreach($headakun as $akun => $nmakun){			
		foreach($arrsubheader as $subhead => $nmsubhead){
			$arrbulan=$arrsubhead2;
			if(in_array($akun,$headnohide)){
				$arrbulan=$subheadnohide;
			}
			foreach($arrbulan as $bln => $nmsubhead2){
				$style2=$style; $name="";
				$class="class=".$akun."[]";
				$stylefontbln2=$stylefontbln;
				if($subhead=='ttl'){
					if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){
						$class="";
						$style2="";
						$stylefontbln2="";
					}else{
						$class="class=bln".$akun."[]";
						$name="name=".$akun."_".$subhead."[]";
					}
				}else{
					if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){								
						$stylefontbln2="style=font-weight:normal;";
					}else{
						$class="class=bln".$akun."[]";
						$name="name=".$akun."_".$subhead."[]";
					}
				}
				
				if(in_array($akun,$digitnf)){$nf=2;}else{$nf=0;}
				if(!in_array($akun,$headmaster)){
					$stestrpha=0;
					if($jenis=='rpha'){
						if($stestbiaya[$estate]['ha']['ttl'][$bln]!=''){
							$stestrpha=$stestbiaya[$estate][$akun][$subhead][$bln]/$stestbiaya[$estate]['ha']['ttl'][$bln];
						}
					}elseif($jenis=='rppkk'){
						if($stestbiaya[$estate]['pkk']['ttl'][$bln]!=''){
							$stestrpha=$stestbiaya[$estate][$akun][$subhead][$bln]/$stestbiaya[$estate]['pkk']['ttl'][$bln];
						}
					}elseif($jenis=='rpprd'){
						if($stestbiaya[$estate]['prd']['ttl'][$bln]!=''){
							$stestrpha=$stestbiaya[$estate][$akun][$subhead][$bln]/$stestbiaya[$estate]['prd']['ttl'][$bln];
						}
					}else{
						#isinya rupiah
						$stestrpha=$stestbiaya[$estate][$akun][$subhead][$bln];
					}
				}else{
					$stestrpha=$stestbiaya[$estate][$akun][$subhead][$bln];					
				}
				if(in_array($akun,$headtnpasub)){
					if($subhead=='ttl'){							
						$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stestrpha,$nf)."</font></td>";
					}
				}else{
					$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stestrpha,$nf)."</font></td>";
				}
			}
		}
	}
	

	$tab.="</tr>";
}
$nodiv+=1;
# GRAND TOTAL
$tab.="<tr class=rowcontent name=baristotal[] style=cursor:pointer;background-color:#C9FEFA; title=\"Click untuk show atau hide baris.\" onclick=hiderow('1','".$no."','est')>";
$tab.="<td align=center id=jumlahbaris colspan=1>".$no."</td>";
$tab.="<td align=left colspan=4>PT ".$pt."</td>";

foreach($headakun as $akun => $nmakun){
	foreach($arrsubheader as $subhead => $nmsubhead){
		$arrbulan=$arrsubhead2;
		if(in_array($akun,$headnohide)){
			$arrbulan=$subheadnohide;
		}
		foreach($arrbulan as $bln => $nmsubhead2){
			$style2=$style; $name="";
			$class="class=".$akun."[]";
			$stylefontbln2=$stylefontbln;
			if($subhead=='ttl'){
				if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){
					$class="";
					$style2="";
					$stylefontbln2="";
				}else{
					$class="class=bln".$akun."[]";
					$name="name=".$akun."_".$subhead."[]";
				}
			}else{
				if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){								
					$stylefontbln2="style=font-weight:normal;";
				}else{
					$class="class=bln".$akun."[]";
					$name="name=".$akun."_".$subhead."[]";
				}
			}
			if(in_array($akun,$digitnf)){$nf=2;}else{$nf=0;}
			if(!in_array($akun,$headmaster)){				
				$stgtrpha=0;
				if($jenis=='rpha'){
					if($stgtbiaya['ha']['ttl'][$bln]!=''){
						$stgtrpha=$stgtbiaya[$akun][$subhead][$bln]/$stgtbiaya['ha']['ttl'][$bln];
					}
				}elseif($jenis=='rppkk'){
					if($stgtbiaya['pkk']['ttl'][$bln]!=''){
						$stgtrpha=$stgtbiaya[$akun][$subhead][$bln]/$stgtbiaya['pkk']['ttl'][$bln];
					}
				}elseif($jenis=='rpprd'){
					if($stgtbiaya['prd']['ttl'][$bln]!=''){
						$stgtrpha=$stgtbiaya[$akun][$subhead][$bln]/$stgtbiaya['prd']['ttl'][$bln];
					}
				}else{
					#isinya rupiah
					$stgtrpha=$stgtbiaya[$akun][$subhead][$bln];
				}
			}else{
					$stgtrpha=$stgtbiaya[$akun][$subhead][$bln];
			}
			if(in_array($akun,$headtnpasub)){
				if($subhead=='ttl'){						
					$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stgtrpha,$nf)."</font></td>";
				}
			}else{
				$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stgtrpha,$nf)."</font></td>";
			}
		}
	}
}
$tab.="</tr>";
$tab.="</tbody></table>";

#HARGA TBS
#harga jual tbs
$tab1.="<table>";
$tab1.="<tr><td valign=top>";

if($proses!='excel'){	
	$tab1.="<table class=sortable cellspacing=1 cellpadding=5>";
}else{
	$tab1.="<table border=1 class=sortable cellspacing=1>";
}

$tab1.="<thead>";
$tab1.="<tr class=rowheader>";
$tab1.="<td width=100px></td><td width=100px>>100% AOP</td><td width=100px>>=90% - 100%</td><td width=100px><90% AOP</td><td width=100px>Total</td>";
$tab1.="</tr>";
$tab1.="</thead>";

$tab1.="<tbody>";
$tab1.="<tr class=rowcontent style=font-weight:bold;text-align:center;>";
$tab1.="<td>Productivity</td>
		<td style=background-color:green;cursor:pointer; title=\"Click untuk menampilkan blok.\" onclick=showme('".$nobrsgreen."',this)>".$green."</td>
		<td style=background-color:yellow;cursor:pointer; title=\"Click untuk menampilkan blok.\" onclick=showme('".$nobrsyellow."',this)>".$yellow."</td>
		<td style=background-color:red;cursor:pointer; title=\"Click untuk menampilkan blok.\" onclick=showme('".$nobrsred."',this)>".$red."</td>
		<td style=cursor:pointer; title=\"Click untuk menampilkan blok.\" onclick=showme('".$nobrttl."',this)>".($green+$yellow+$red)."</td>";
$tab1.="</tr>";
$tab1.="</tbody>";

$tab1.="</table>";

if($proses!='excel'){	
	$tab1.="<table class=sortable cellspacing=1 cellpadding=5>";
}else{
	$tab1.="<table border=1 class=sortable cellspacing=1>";
}

$tab1.="<thead>";
$tab1.="<tr class=rowheader>";
$tab1.="<td width=100px></td><td width=100px>Gross Profit</td><td width=100px>Gross Loss</td><td width=100px>Total</td>";
$tab1.="</tr>";
$tab1.="</thead>";

$tab1.="<tbody>";
$tab1.="<tr class=rowcontent style=font-weight:bold;text-align:center;>";
$tab1.="<td>Profitability</td>
		<td style=background-color:green;cursor:pointer; title=\"Click untuk menampilkan blok.\" onclick=showme('".$nobrsgreenp."',this)>".$greenp."</td>
		<td style=background-color:red;cursor:pointer; title=\"Click untuk menampilkan blok.\" onclick=showme('".$nobrsredp."',this)>".$redp."</td>
		<td style=cursor:pointer; title=\"Click untuk menampilkan blok.\" onclick=showme('".$nobrttlp."',this)>".($greenp+$redp)."</td>";
$tab1.="</tr>";
$tab1.="</tbody>";
$tab1.="</table>";

$tab1.="</td>";
$tab1.="<td valign=top>";


if($proses!='excel'){	
	$tab1.="<table class=sortable cellspacing=1 cellpadding=5>";
}else{
	$tab1.="<table border=1 class=sortable cellspacing=1>";
}
$tab1.="
    <thead>
        <tr class=rowheader>
            <td align=center rowspan=2>".$_SESSION['lang']['nourut']."</td>
            <td align=center rowspan=2>".$_SESSION['lang']['unit']."</td>
            <td align=center colspan=".(count($rangebln)+2).">Average Selling Price</td>
		</tr>
        <tr class=rowheader>";
		foreach($rangebln as $bln){			
			$tab1.="<td align=center>".$bln."</td>";
		}
		$tab1.="<td align=center>YTD LY</td>";
		$tab1.="<td align=center>AOP YTD</td>";
		
	$tab1.="</tr>";
	$tab1.="</thead>";
	$tab1.="<tbody>";
	$no=0;
	foreach($dataunitharga as $kdunit){
		$no++;
		$tab1.="<tr class=rowcontent>";
		$tab1.="<td align=center>".$no."</td>";
		$tab1.="<td align=left id=kodeorghrgbgt".$no.">".$kdunit."</td>";
			foreach($rangebln as $bln){
				$tab1.="<td align=right>".numb_format($hargatbs[$kdunit][$bln])."</td>";
			}
		$tab1.="<td align=right>".numb_format($hargatbslalu[$kdunit])."</td>";
		
		if($hargabgt[$kdunit]!=0){
			$inp="";
			if($proses=='preview'){				
				$inp="<input disabled hidden class=myinputtextnumber style='width:50px' id=harga".$no." onkeypress='return angka_doang(event)' value=".$hargabgt[$kdunit].">";
			}
			
			$tab1.="<td align=right>".numb_format($hargabgt[$kdunit])."".$inp."</td>";
		}else{
			$adaharga++;
			$tab1.="<td><input class=myinputtextnumber style='width:50px' id=harga".$no." onkeypress='return angka_doang(event)'></td>";
		}
		
		$tab1.="</tr>";
	}
	if($adaharga>0){
		$tab1.="<tr class=rowcontent>";
		$tab1.="<td align=right colspan=".(count($rangebln)+4)."><button class=mybutton onclick=simpanhrgbgt(".$no.")>Update</button></td>";
		$tab1.="</tr>";
	}
	
	
$tab1.="</tbody>
</table>";

$tab1.="</td>";
$tab1.="</table>";



switch ($proses) {
    case 'preview':
        echo $tab1.$tab;
	break;

    case 'excel':
        $nop_ = "trend_lmto_per_block_main_activity";
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
	#$n = hidezerodecimal($a,$d);
	$n = number_format($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
?>