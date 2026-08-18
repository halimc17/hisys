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
	$whtbs=" and a.supplierid in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
	$whhrg=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
}
if($kdorg!=''){
	$whtbs=" and a.supplierid ='".$kdorg."'";
	$whhrg=" and a.kodeorg ='".$kdorg."'";
}

#UNTUK HEADER
$arrsubhead1=array(
	'lab'=>'Labor',
	'mat'=>'Material',
	'tra'=>'Transport',
	'oth'=>'Other',
	'ttl'=>'Total'
);


$str=" select * from ".$dbname.".keu_5akun WHERE noakun LIKE '62%' AND namaakun NOT LIKE '%NON AKTIF%' order by noakun";
$res = fetchdata($str);
foreach($res as $bar){
	//$nmakun[$bar['noakun']]=ucfirst(strtolower($bar['namaakun']));
	$nmakun[$bar['noakun']]=ucwords(strtolower($bar['namaakun']));
	if(strlen($bar['noakun'])=='7'){		
		$headakun[$bar['noakun']]=$bar['noakun'];
	}
}

$arrsubhead2=$rangebln;
$arrsubhead2["ytd_ti"]="YTD-".substr($tahun,2,2);
$arrsubhead2["ytd_ll"]="YTD-".substr(($tahun-1),2,2);
$arrsubhead2["aop_fy"]="AOP FY";


if($proses=='preview'){	
	$tab.="<input hidden id=colhide value=3>";
	$tab.="<input hidden id=colunhide value=".(count($rangebln)+3).">";
	$tab.="<input hidden id=colhead1 value=".count($arrsubhead1).">";
	$tab.="<input hidden id=colhead2 value=".count($arrsubhead2).">";
	$tab.="<input hidden id=jlhbulan value=".count($rangebln).">";
	$tab.="<input hidden id=xxxx value=".$cols.">";
}
if($style=="style=display:none" and $proses=='preview'){
	$colssub1=$cols=$colspan="3";
}else{
	$cols=count($arrsubhead1)*count($arrsubhead2);
	$colssub1=(count($rangebln)+3);
	$colspan=(count($rangebln)+3);
}

$stylefont="style=font-weight:normal;color:#A0A0A0";
$stylefontbln="style=font-weight:normal;color:#02BC28";

if($proses!='excel'){	
	$tab.="<table class=sortable cellspacing=1>";
}else{
	$tab.="<table border=1 class=sortable cellspacing=1>";
}
$tab.="
    <thead>
        <tr class=rowheader title=\"Click untuk show atau hide kolom.\">
            <th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['unit']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['divisi']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['blok']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['tahuntanam']."</th>
            <th align=center rowspan='2' colspan=".$colspan." id=headha onclick=showhide('ha[]','headha','1')>".$_SESSION['lang']['ha']."</th>
			";
			
			foreach($headakun as $akun){
				$tab.="<th align=center colspan=".$cols." id=head".$akun." onclick=showhidev2('".$akun."[]','cost".$akun."[]',this.id)><font>".$nmakun[$akun]."</font></th>";
			}
			
			
			
		$tab.="	
        </tr>
        <tr class=rowheader title=\"Click untuk show atau hide kolom.\">";
			
			foreach($headakun as $akun){				
				foreach($arrsubhead1 as $subhead => $nmsubhead){
					$style2=$style;
					$class="class=".$akun."[]";
					$stylefont2=$stylefont;
					if($subhead=='ttl'){
						$class="";
						$style2="";
						$stylefont2="";
					}
					$tab.="<th align=center colspan=".$colssub1." ".$class." name=cost".$akun."[] id=sub".$akun.$subhead." ".$style2." onclick=showhidesubv2('".$akun."_".$subhead."[]',this.id,'head".$akun."')><font ".$stylefont2.">".$nmsubhead."</font></th>";
				}
			}
			
		$tab.="</tr>
		
        <tr class=rowheader>";
			#HA
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=ha[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
			foreach($headakun as $akun){				
				foreach($arrsubhead1 as $subhead => $nmsubhead){
					foreach($arrsubhead2 as $subhead2 => $nmsubhead2){
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
						$tab.="<th align=center ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".$nmsubhead2."</font></th>";
					}
				}
			}
			
    $tab.="</tr>
		
    </thead>
 <tbody>";

$where=$where2=$where_spb=$whereJ=$whtbs="";
if($pt!=''){
	$where=" and substr(a.kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$where2=" and substr(a.kodeblok,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$where_spb=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe='KEBUN')";
	$whereJ=" and a.kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
	$whtbs=" and a.supplierid in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."'  and tipe='KEBUN')";
}
if($kdorg!=''){
	$where=" and substr(a.kodeorg,1,4) ='".$kdorg."'";
	$where2=" and substr(a.kodeblok,1,4) ='".$kdorg."'";
	$where_spb=" and a.kodeorg ='".$kdorg."'";
	$whereJ=" and a.kodeorg ='".$kdorg."'";
	$whtbs=" and a.supplierid ='".$kdorg."'";
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
}
if($tt!=''){
	$whereJ.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
	$wh.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where tahuntanam='".$tt."')";
	$wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
	$wh2.=" and a.tahuntanam='".$tt."'";
	$whB.=" and a.thntnm='".$tt."'";
	$wh_spb.=" and a.tahuntanam='".$tt."'";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where thntnm='".$tt."')";
}
if($ip!=''){
	$whereJ.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$wh.=" and a.kodeblok in (select kodeorg from ".$dbname.".setup_blok where intiplasma='".$ip."')";
	$wh_bgt.=" and a.kodeblok in (select kodeblok from ".$dbname.".bgt_blok where intiplasma='".$ip."')";
	$wh2.=" and a.intiplasma='".$ip."'";
	$whB.=" and a.intiplasma='".$ip."'";
	$wh_spb.=" and a.intiplasma='".$ip."'";
	$wh_bgtrp.=" and a.kodeorg in (select kodeblok from ".$dbname.".bgt_blok where intiplasma='".$ip."')";
}


#master blok
$str="select * from ".$dbname.".setup_blok_tahunan a where 1=1 ".$wh2." ".$where." and a.statusblok='TM' and (a.tahun like '".$tahun."%' or a.tahun like '".($tahun-1)."%') and a.luasareaproduktif>0 order by a.kodeorg";
$res = fetchdata($str);
$ada=0;$kodeblok=array();
foreach($res as $bar){
	$prdaresta=substr($bar['tahun'],0,4)."-".substr($bar['tahun'],4,2);
	#$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)]=substr($bar['kodeorg'],0,4);
	$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,6)][$bar['kodeorg']]=$bar['kodeorg'];
	$luasblok[$bar['kodeorg']][$prdaresta]=$bar['luasareaproduktif'];
	$pokok[$bar['kodeorg']][$prdaresta]=$bar['jumlahpokok'];
	if($prdaresta==($tahun-1)."-12"){		
		$luaslalu[$bar['kodeorg']]=$bar['luasareaproduktif'];
		$pokoklalu[$bar['kodeorg']]=$bar['jumlahpokok'];
	}
	if($prdaresta==$prd){
		$ada+=1;
	}
}
if($ada==0){
	$str="select * from ".$dbname.".setup_blok a where 1=1 ".$wh2." ".$where." and a.statusblok='TM' and a.luasareaproduktif>0 order by a.kodeorg";
	$res = fetchdata($str);
	foreach($res as $bar){
		#$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)]=substr($bar['kodeorg'],0,4);
		$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,6)][$bar['kodeorg']]=$bar['kodeorg'];
		$luasblok[$bar['kodeorg']][$prd]=$bar['luasareaproduktif'];
		$pokok[$bar['kodeorg']][$prd]=$bar['jumlahpokok'];
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
	$luasaop[$bar['kodeblok']]+=$bar['hathnini'];
	$pokokaop[$bar['kodeblok']]+=$bar['pokokthnini'];
	
	$luasbgtsdbi[$bar['kodeblok']]+=$bar['hathnini'];
	$luasbgtsdbidiv[substr($bar['kodeblok'],0,6)]+=$bar['hathnini'];
	$luasbgtsdbiest[substr($bar['kodeblok'],0,4)]+=$bar['hathnini'];
}

	
	
#ambil prd real
$prdton=$prdtontitle=array();
$str = "select sum(kgwb) as kgwb, substr(tanggal,1,7) as periode, blok from " . $dbname . ".kebun_spb_vw a  where 1=1 ".$wh_spb." ".$where_spb." and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' group by periode, blok"; 
$res = fetchdata($str);
foreach($res as $bar){	
	$prdton[$bar['blok']][$bar['periode']]+=($bar['kgwb']/1000);
	$prdtontitle[$bar['blok']]+=($bar['kgwb']/1000);
}

#ambil prd real tahun lalu
$str = "select sum(kgwb) as kgwb, substr(tanggal,1,7) as periode, blok from " . $dbname . ".kebun_spb_vw a  where 1=1 ".$wh_spb." ".$where_spb." and substr(tanggal,1,7) between '".$periodelalu1."' and  '".$periodelalu2."' group by blok"; 
$res = fetchdata($str);
foreach($res as $bar){	
	$prdtonlalu[$bar['blok']]+=($bar['kgwb']/1000);
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
	
	
	$prdbgtsdbi[$bar['kodeblok']] += ($bar['sdbi']/1000);
	$prdbgtbidiv[substr($bar['kodeblok'],0,6)] += ($bar['sdbi']/1000);
	$prdbgtbiest[substr($bar['kodeblok'],0,4)] += ($bar['sdbi']/1000);
}

#sekarang saya ingat, ini di patok karena yang di ambil cuma Biaya HK dan ini harus sama dengan di lap justifikasi
/* $whrkdj="and (kodejurnal in ('M0','PNN01','PNN02','CT01','BM01','SPK1','M','NOTAD','NOTAK','HPP','TGH01','BM','BK','KM','KK','POT') 
or kodejurnal like 'PRJ%')"; */
#$arrkdjurupah=array('M0','PNN01','PNN02','CT01','BM01','SPK1','M','NOTAD','NOTAK','HPP','TGH01','BM','BK','KM','KK','POT');

$akunupahpnn=array('6110101','6110102');
$akuntranspnn=array('6110103','6110104');

$lbrpupuk=array('621010302','621010305','621010308');
$transpupuk=array('621010323','621010324');

$whakun=" and substr(noakun,1,3) in ('621')";
$whakunumum=" and noakun like '7%'";

# biaya tahun ini
$byypnnlab=array();
$str = "select a.kodekegiatan,kodeorg,a.kodeblok, sum(jumlah) as jumlah, periode,noakun,kodejurnal 
from " . $dbname . ".keu_jurnaldt_vw a   
where 1=1 ".$whereJ." and periode between '".$periode1."' and  '".$periode2."' ".$whakun."     
group by kodeblok,periode,noakun,kodejurnal,kodekegiatan"; 
$res = fetchdata($str);
foreach($res as $bar){
	if($bar['kodeblok']==''){
		$bar['kodeblok']=$bar['kodeorg'];
	}
	#TM
	#biaya pemel
	if(substr($bar['kodejurnal'],0,3)!='INV'){
		#labor
		$biaya[$bar['kodeblok']][$bar['noakun']]['lab'][$bar['periode']]+=($bar['jumlah']/1000);
		$biaya[$bar['kodeblok']][$bar['noakun']]['lab']['ytd_ti']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)=='INV'){
		#material
		$biaya[$bar['kodeblok']][$bar['noakun']]['mat'][$bar['periode']]+=($bar['jumlah']/1000);
		$biaya[$bar['kodeblok']][$bar['noakun']]['mat']['ytd_ti']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)!='VHC'){
		#transport
		$biaya[$bar['kodeblok']][$bar['noakun']]['tra'][$bar['periode']]+=($bar['jumlah']/1000);
		$biaya[$bar['kodeblok']][$bar['noakun']]['tra']['ytd_ti']+=($bar['jumlah']/1000);
	}else{
		$biaya[$bar['kodeblok']][$bar['noakun']]['oth'][$bar['periode']]+=($bar['jumlah']/1000);
		$biaya[$bar['kodeblok']][$bar['noakun']]['oth']['ytd_ti']+=($bar['jumlah']/1000);
	}
	$biaya[$bar['kodeblok']][$bar['noakun']]['ttl'][$bar['periode']]+=($bar['jumlah']/1000);
	$biaya[$bar['kodeblok']][$bar['noakun']]['ttl']['ytd_ti']+=($bar['jumlah']/1000);
	
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
		$bar['kodeblok']=$bar['kodeorg'];
	}
	
	#TM
	#biaya pemel
	if(substr($bar['kodejurnal'],0,3)!='INV'){
		#labor
		$biaya[$bar['kodeblok']][$bar['noakun']]['lab']['ytd_ll']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)=='INV'){
		#material
		$biaya[$bar['kodeblok']][$bar['noakun']]['mat']['ytd_ll']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)!='VHC'){
		#transport
		$biaya[$bar['kodeblok']][$bar['noakun']]['tra']['ytd_ll']+=($bar['jumlah']/1000);
	}else{
		$biaya[$bar['kodeblok']][$bar['noakun']]['oth']['ytd_ll']+=($bar['jumlah']/1000);
	}
	$biaya[$bar['kodeblok']][$bar['noakun']]['ttl']['ytd_ll']+=($bar['jumlah']/1000);
}

$e="("; $s="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
	if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
}
$e.=")"; $s.=")";

$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";
$whereakun = " and substr(noakun,1,3) in ('621')";

#ini khusus budget kebun
$str=" select tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whereakun."";
$res = fetchdata($str);
foreach($res as $bar){
	if((substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK')){
		#LABOUR
		$biaya[$bar['kodeorg']][$bar['noakun']]['lab']['aop_fy']+=($bar['rupiah']/1000);
	}else if((substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL')){
		#MATERIAL			
		$biaya[$bar['kodeorg']][$bar['noakun']]['mat']['aop_fy']+=($bar['rupiah']/1000);
	}else if($bar['kodebudget']=='VHC'){
		#TRANS
		$biaya[$bar['kodeorg']][$bar['noakun']]['tra']['aop_fy']+=($bar['rupiah']/1000);
	}else{
		#OTHER
		$biaya[$bar['kodeorg']][$bar['noakun']]['oth']['aop_fy']+=($bar['rupiah']/1000);
	}
	$biaya[$bar['kodeorg']][$bar['noakun']]['ttl']['aop_fy']+=($bar['rupiah']/1000);
}

	// echo "<pre>";
	// print_r($hargapertgllalu);
	// print_r($hargapertgl);
	// echo "</pre>";

	#number format
	$nf2=2;
	$nf0=0;

	#number format
	if($barishide=='1'){
		$stylerow="";
	}else{	
		$stylerow="style=display:none";
	}	

	$nobrsgreen="";
	$nobrsgreenp="";
	$nobrsyellow="";
	$nobrsred="";
	$nobrsredp="";
	$nobrttl=$nobrttlp="";

	$no=0;$nodiv=0;$gtrluas=0;$green=$yellow=$red=$greenp=$redp=0; 
	$tdluas=$teluas=$tbcluas=$tdcluas=array();
	$stdivbiaya=array();
	$nocol=0;
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
				$title.="\nDouble click untuk melihat detail akun.";
				$title.="\nBlok : ".$blok."";
				$title.="\nLuas : ".numb_format($nmha[$blok],2)." Ha";
				$title.="\nPokok : ".$nmpkk[$blok]."";
				$title.="\nProduksi : ".numb_format($prdtontitle[$blok],2)." Ton";
				
				$tab.="<tr class=rowcontent ".$stylerow." id=row_".$no." name=".$estate."[] onclick=getmark(this.id); title=\"Single click untuk memberi tanda.".$title."\">";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$estate."</td>";
				$tab.="<td>".$div."</td>";
				$tab.="<td id=kdblok_".$no.">".$blok."</td>";
				$tab.="<td align=center>".$nmtt[$blok]."</td>";
				# === ha ===	
				foreach($rangebln as $bln){	
					$tab.="<td align=right name=ha[] ".$style.">".numb_format($luasblok[$blok][$bln],$nf2)."</td>";
					$haytdty[$blok]=$luasblok[$blok][$bln];
					$hattldiv[$div][$bln]+=$luasblok[$blok][$bln];
					$hattlest[$estate][$bln]+=$luasblok[$blok][$bln];
					$hagt[$bln]+=$luasblok[$blok][$bln];
				}	
				$tab.="<td align=right >".numb_format($haytdty[$blok],$nf2)."</td>";	
				$tab.="<td align=right >".numb_format($luaslalu[$blok],$nf2)."</td>";	
				$tab.="<td align=right >".numb_format($luasaop[$blok],$nf2)."</td>";	
					
				#ttl divisi kanan	
				$haknytdtydiv[$div]+=$haytdty[$blok];	
				$haknlaludiv[$div]+=$luaslalu[$blok];	
				$haknaopdiv[$div]+=$luasaop[$blok];	
					
				#ttl estate kanan	
				$haknytdtyest[$estate]+=$haytdty[$blok];	
				$haknlaluest[$estate]+=$luaslalu[$blok];	
				$haknaopest[$estate]+=$luasaop[$blok];	
					
				#grand total	
				$haknytdtygt+=$haytdty[$blok];	
				$haknlalugt+=$luaslalu[$blok];	
				$haknaopgt+=$luasaop[$blok];	
				# === end ha ===	
				
				foreach($headakun as $akun){				
					foreach($arrsubhead1 as $subhead => $nmsubhead){
						foreach($arrsubhead2 as $bln => $nmsubhead2){
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
							$nocol+=1;
							
							$tab.="<td align=right ".$class." ".$name." ".$style2." id=".$akun."_".$nocol."_".$no." ondblclick=getdetail('".$no."','".$blok."','".$akun."');><font ".$stylefontbln2.">".numb_format($biaya[$blok][$akun][$subhead][$bln],2)."</font></td>";
							
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
			$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#E7FCE4; title=\"Click untuk show atau hide baris.\" onclick=hiderow('".$awal."','".$no."','div')>";
			$tab.="<td align=center>".$nodiv."</td>";
			$tab.="<td align=center></td>";
			$tab.="<td align=left colspan=3>Divisi ".$div."</td>";
			
			# === ha divisi ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=ha[] ".$style.">".numb_format($hattldiv[$div][$bln],$nf2)."</td>";
			}	
			$tab.="<td align=right >".numb_format($haknytdtydiv[$div],$nf2)."</td>";	
			$tab.="<td align=right >".numb_format($haknlaludiv[$div],$nf2)."</td>";	
			$tab.="<td align=right >".numb_format($haknaopdiv[$div],$nf2)."</td>";	
			# === end ha divisi ===	
			foreach($headakun as $akun){				
				foreach($arrsubhead1 as $subhead => $nmsubhead){
					foreach($arrsubhead2 as $bln => $nmsubhead2){
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
						$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stdivbiaya[$div][$akun][$subhead][$bln],2)."</font></td>";
					}
				}
			}
			
			

			$tab.="</tr>";
		}
		$nodiv+=1;
		# TOTAL ESTATE
		$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#C9FEFA; title=\"Click untuk show atau hide baris.\" onclick=hiderow('".$awalest."','".$no."','est')>";
		$tab.="<td align=center>".$nodiv."</td>";
		$tab.="<td align=left colspan=4>Unit ".$estate."</td>";
		# === ha estate ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ha[] ".$style.">".numb_format($hattlest[$estate][$bln],$nf2)."</td>";
		}	
		$tab.="<td align=right >".numb_format($haknytdtyest[$estate],$nf2)."</td>";	
		$tab.="<td align=right >".numb_format($haknlaluest[$estate],$nf2)."</td>";	
		$tab.="<td align=right >".numb_format($haknaopest[$estate],$nf2)."</td>";	
		# === end ha estate ===	
		foreach($headakun as $akun){				
			foreach($arrsubhead1 as $subhead => $nmsubhead){
				foreach($arrsubhead2 as $bln => $nmsubhead2){
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
					$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stestbiaya[$estate][$akun][$subhead][$bln],2)."</font></td>";
				}
			}
		}
		

		$tab.="</tr>";
	}
	$nodiv+=1;
	# GRAND TOTAL
	$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#C9FEFA; title=\"Click untuk show atau hide baris.\" onclick=hiderow('1','".$no."','est')>";
	$tab.="<td align=left colspan=5>PT ".$pt."</td>";
	# === ha gt ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ha[] ".$style.">".numb_format($hagt[$bln],$nf2)."</td>";
	}	
	$tab.="<td align=right >".numb_format($haknytdtygt,$nf2)."</td>";	
	$tab.="<td align=right >".numb_format($haknlalugt,$nf2)."</td>";	
	$tab.="<td align=right >".numb_format($haknaopgt,$nf2)."</td>";	
	# === end ha gt ===	
	foreach($headakun as $akun){				
		foreach($arrsubhead1 as $subhead => $nmsubhead){
			foreach($arrsubhead2 as $bln => $nmsubhead2){
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
				$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stgtbiaya[$akun][$subhead][$bln],2)."</font></td>";
			}
		}
	}
	$tab.="</tr>";
	$tab.="</tbody></table>";


switch ($proses) {
######PREVIEW
    case 'preview':
        echo $tab;
        break;

######EXCEL	
    case 'excel':
        $nop_ = "trend_lmto_per_block_maintenance_mature_breakdown";
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
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}


?>