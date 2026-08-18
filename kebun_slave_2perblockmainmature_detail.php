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
$nomorakun= checkPostGet('akun', '');
$nomorblok= checkPostGet('blok', '');
$jenis    = checkPostGet('jenis', '');


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
$arrjns=array(
	"rp"   =>"Rupiah (000)",
	"rpha" =>"Rupiah (000) / Ha",
	"rppkk"=>"Rupiah (000) / Pokok",
	"rpprd"=>"Rupiah / Kg"
);



$headakun=array(
	'gtl'=>'gtl'
);
$nmakun['gtl']=ucwords(strtolower('Total Biaya'))."<br><font style=font-size:10px;font-weight:normal;color:grey;>(".$arrjns[$jenis].")</font>";


$str=" select * from ".$dbname.".keu_5akun WHERE noakun LIKE '62%' AND namaakun NOT LIKE '%NON AKTIF%' order by noakun";
$res = fetchdata($str);
foreach($res as $bar){
	$nmakun[$bar['noakun']]=ucwords(strtolower($bar['namaakun']))."<br><font style=font-size:10px;font-weight:normal;color:grey;>(".$arrjns[$jenis].")</font>";
	#$nmakun[$bar['noakun']]=ucwords(strtolower($bar['noakun']))."<br><font style=font-size:10px;font-weight:normal;color:grey;>(".$arrjns[$jenis].")</font>";
	if(strlen($bar['noakun'])=='7'){		
		$headakun[$bar['noakun']]=$bar['noakun'];
	}
}

$arrsubhead2=$rangebln;
$arrsubhead2["ytd_ti"]="YTD-".substr($tahun,2,2);
$arrsubhead2["ytd_ll"]="YTD-".substr(($tahun-1),2,2);
$arrsubhead2["aop_fy"]="AOP FY";


if($proses=='getdetail'){	
	$tab.="<input hidden id=colhide value=3>";
	$tab.="<input hidden id=colunhide value=".(count($rangebln)+3).">";
	$tab.="<input hidden id=colhead1 value=".count($arrsubhead1).">";
	$tab.="<input hidden id=colhead2 value=".count($arrsubhead2).">";
	$tab.="<input hidden id=jlhbulan value=".count($rangebln).">";
	$tab.="<input hidden id=xxxx value=".$cols.">";
}
if($style=="style=display:none" and $proses=='getdetail'){
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
            <!--<th align=center rowspan='3'>".$_SESSION['lang']['namaakun']."</th>-->
            <th align=center rowspan='3'>".$_SESSION['lang']['kegiatan']."</th>
			";
			
			foreach($headakun as $akun){
				$font="style=color:#E67E22;";
				$tab.="<th align=center colspan=".$cols." id=head".$akun."det onclick=showhidev2('".$akun."det[]','cost".$akun."det[]',this.id)><font ".$font.">".$nmakun[$akun]."</font></th>";
			}
			
			
			
		$tab.="	
        </tr>
        <tr class=rowheader title=\"Click untuk show atau hide kolom.\">";
			
			foreach($headakun as $akun){				
				foreach($arrsubhead1 as $subhead => $nmsubhead){
					$style2=$style;
					$class="class=".$akun."det[]";
					$stylefont2=$stylefont.";color:#E67E22;";
					if($subhead=='ttl'){
						$class="";
						$style2="";
						$stylefont2="style=color:#E67E22;";
					}
					$tab.="<th align=center colspan=".$colssub1." ".$class." name=cost".$akun."det[] id=sub".$akun."det".$subhead." ".$style2." onclick=showhidesubv2('".$akun."_".$subhead."det[]',this.id,'head".$akun."det')><font ".$stylefont2.">".$nmsubhead."</font></th>";
				}
			}
			
		$tab.="</tr>
		
        <tr class=rowheader>";
			
			foreach($headakun as $akun){				
				foreach($arrsubhead1 as $subhead => $nmsubhead){
					foreach($arrsubhead2 as $subhead2 => $nmsubhead2){
						$style2=$style; $name="";
						$class="class=".$akun."det[]";
						$stylefontbln2=$stylefontbln;
						if($subhead=='ttl'){
							if(substr($subhead2,0,3)=='ytd' or substr($subhead2,0,3)=='aop'){
								$class="";
								$style2="";
								$stylefontbln2="";
							}else{
								$class="class=bln".$akun."det[]";
								$name="name=".$akun."_".$subhead."det[]";
							}
						}else{
							if(substr($subhead2,0,3)=='ytd' or substr($subhead2,0,3)=='aop'){								
								$stylefontbln2="style=font-weight:normal;";
							}else{
								$class="class=bln".$akun."det[]";
								$name="name=".$akun."_".$subhead."det[]";
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
$str="select * from ".$dbname.".setup_blok_tahunan a where 1=1 ".$wh2." ".$where." and a.statusblok='TM' and (a.tahun like '".$tahun."%' or a.tahun like '".($tahun-1)."%') and a.luasareaproduktif>0 and a.kodeorg = '".$nomorblok."' order by a.kodeorg";
$res = fetchdata($str);
$ada=0;$kodeblok=array();
foreach($res as $bar){
	$prdaresta=substr($bar['tahun'],0,4)."-".substr($bar['tahun'],4,2);
	
	if($prdaresta==($tahun-1)."-".$bulan){
		#luaslalu
		$biaya['ha']['ttl']['ytd_ll']=$bar['luasareaproduktif'];
		#pokoklalu
		$biaya['pkk']['ttl']['ytd_ll']=$bar['jumlahpokok'];
		#sph
		$biaya['sph']['ttl']['ytd_ll']=$bar['jumlahpokok']/$bar['luasareaproduktif'];
	}
	if($prdaresta==$prd){
		$ada+=1;
	}
}
if($ada==0){
	$str="select * from ".$dbname.".setup_blok a where 1=1 ".$wh2." ".$where." and a.statusblok='TM' and a.luasareaproduktif>0 and a.kodeorg = '".$nomorblok."' order by a.kodeorg";
	$res = fetchdata($str);
	foreach($res as $bar){
		foreach ($rangebln as $bln){
			#luas ha
			$biaya['ha']['ttl'][$bln]=$bar['luasareaproduktif'];
			#pokok
			$biaya['pkk']['ttl'][$bln]=$bar['jumlahpokok'];
			#sph
			$biaya['sph']['ttl'][$bln]=$bar['jumlahpokok']/$bar['luasareaproduktif'];
		}
		$biaya['ha']['ttl']['ytd_ti']=$bar['luasareaproduktif'];
		$biaya['pkk']['ttl']['ytd_ti']=$bar['jumlahpokok'];
		$biaya['sph']['ttl']['ytd_ti']=$bar['jumlahpokok']/$bar['luasareaproduktif'];
	}	
}


#total ha estate
$str="select * from ".$dbname.".setup_blok a where 1=1 ".$wh2." ".$where." and a.statusblok='TM' and a.luasareaproduktif>0 and a.kodeorg = '".$nomorblok."' order by a.kodeorg";
$res = fetchdata($str);
foreach($res as $bar){
	$ttlhaest[substr($bar['kodeorg'],0,4)]+=$bar['luasareaproduktif'];
}	

#ambil luas bgt
$str = "select kodeblok, sum(hathnini) as hathnini, sum(pokokthnini) as pokokthnini from " . $dbname . ".bgt_blok a  where 1=1 ".$whB." ".$where2." and tahunbudget='".$tahun."' and a.kodeblok = '".$nomorblok."' group by a.kodeblok"; 
$res = fetchdata($str);
foreach($res as $bar){	
	#luas ha
	$biaya['ha']['ttl']['aop_fy']=$bar['hathnini'];
	#pokok
	$biaya['pkk']['ttl']['aop_fy']=$bar['pokokthnini'];
	#sph
	if($bar['hathnini']!='0'){		
		$biaya['sph']['ttl']['aop_fy']=$bar['pokokthnini']/$bar['hathnini'];	
	}
}

	
	
#ambil prd real
$prdton=$prdtontitle=array();
$str = "select sum(kgwb) as kgwb, substr(tanggal,1,7) as periode, blok from " . $dbname . ".kebun_spb_vw a  where 1=1 ".$wh_spb." ".$where_spb." and substr(tanggal,1,7) between '".$periode1."' and  '".$periode2."' and a.blok = '".$nomorblok."' group by periode, blok"; 
$res = fetchdata($str);
foreach($res as $bar){
	#produksi
	$biaya['prd']['ttl'][$bar['periode']]+=$bar['kgwb']/1000;
	$biaya['prd']['ttl']['ytd_ti']+=$bar['kgwb']/1000;
	
	$prdtontitle[$bar['blok']]+=($bar['kgwb']/1000);
}

#ambil prd real tahun lalu
$str = "select sum(kgwb) as kgwb, substr(tanggal,1,7) as periode, blok from " . $dbname . ".kebun_spb_vw a  where 1=1 ".$wh_spb." ".$where_spb." and substr(tanggal,1,7) between '".$periodelalu1."' and  '".$periodelalu2."' and a.blok = '".$nomorblok."' group by blok"; 
$res = fetchdata($str);
foreach($res as $bar){	
	#produksi
	$biaya['prd']['ttl']['ytd_ll']+=$bar['kgwb']/1000;
}

#ambil prd bgt
$e="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="kg".addZero($i,2);
	if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";

$str=" select kodeblok,".$e." as sdbi,kg".$bulan." as bi,kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw a where 1=1 ".$where2." ".$whB." ".$wh_bgt." and tahunbudget = '".$tahun."' and a.kodeblok = '".$nomorblok."'";
$res = fetchdata($str);
foreach($res as $bar){	
	#produksi bgt
	$biaya['prd']['ttl']['aop_fy']+=$bar['sdbi']/1000;
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
where 1=1 ".$whereJ." and periode between '".$periode1."' and  '".$periode2."' ".$whakun."  and a.kodeblok='".$nomorblok."'    
group by kodeblok,periode,noakun,kodejurnal,kodekegiatan"; 
$res = fetchdata($str);
foreach($res as $bar){
	$kodeblok[substr($bar['noakun'],0,2)][$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
	
	if($bar['kodeblok']==''){
		$bar['kodeblok']=$bar['kodeorg'];
	}
	#TM
	#biaya pemel
	if(substr($bar['kodejurnal'],0,3)!='INV'){
		#labor
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['lab'][$bar['periode']]+=($bar['jumlah']/1000);
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['lab']['ytd_ti']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)=='INV'){
		#material
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['mat'][$bar['periode']]+=($bar['jumlah']/1000);
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['mat']['ytd_ti']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
		#transport
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['tra'][$bar['periode']]+=($bar['jumlah']/1000);
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['tra']['ytd_ti']+=($bar['jumlah']/1000);
	}else{
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['oth'][$bar['periode']]+=($bar['jumlah']/1000);
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['oth']['ytd_ti']+=($bar['jumlah']/1000);
	}
	$biaya[$bar['kodekegiatan']][$bar['noakun']]['ttl'][$bar['periode']]+=($bar['jumlah']/1000);
	$biaya[$bar['kodekegiatan']][$bar['noakun']]['ttl']['ytd_ti']+=($bar['jumlah']/1000);
	
}

# biaya tahun lalu
#KHUSUS BYY LAPANGAN
$str = "select a.kodekegiatan,kodeorg,a.kodeblok, sum(jumlah) as jumlah, periode,noakun,kodejurnal 
from " . $dbname . ".keu_jurnaldt_vw a      
where 1=1 ".$whereJ." and periode between '".$periodelalu1."' and  '".$periodelalu2."' ".$whakun."   and a.kodeblok='".$nomorblok."'   
group by kodeblok,periode,noakun,kodejurnal,kodekegiatan"; 
$res = fetchdata($str);
foreach($res as $bar){
	$kodeblok[substr($bar['noakun'],0,2)][$bar['noakun']][$bar['kodekegiatan']]=$bar['kodekegiatan'];
	if($bar['kodeblok']==''){
		$bar['kodeblok']=$bar['kodeorg'];
	}
	#TM
	#biaya pemel
	if(substr($bar['kodejurnal'],0,3)!='INV'){
		#labor
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['lab']['ytd_ll']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)=='INV'){
		#material
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['mat']['ytd_ll']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
		#transport
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['tra']['ytd_ll']+=($bar['jumlah']/1000);
	}else{
		$biaya[$bar['kodekegiatan']][$bar['noakun']]['oth']['ytd_ll']+=($bar['jumlah']/1000);
	}
	$biaya[$bar['kodekegiatan']][$bar['noakun']]['ttl']['ytd_ll']+=($bar['jumlah']/1000);
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
$str=" select kegiatan,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whereakun."  and a.kodeorg='".$nomorblok."' ";
$res = fetchdata($str);
foreach($res as $bar){
	$kodeblok[substr($bar['noakun'],0,2)][$bar['noakun']][$bar['kegiatan']]=$bar['kegiatan'];

	if((substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK')){
		#LABOUR
		$biaya[$bar['kegiatan']][$bar['noakun']]['lab']['aop_fy']+=($bar['rupiah']/1000);
	}else if((substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL')){
		#MATERIAL			
		$biaya[$bar['kegiatan']][$bar['noakun']]['mat']['aop_fy']+=($bar['rupiah']/1000);
	}else if($bar['kodebudget']=='VHC'){
		#TRANS
		$biaya[$bar['kegiatan']][$bar['noakun']]['tra']['aop_fy']+=($bar['rupiah']/1000);
	}else{
		#OTHER
		$biaya[$bar['kegiatan']][$bar['noakun']]['oth']['aop_fy']+=($bar['rupiah']/1000);
	}
	$biaya[$bar['kegiatan']][$bar['noakun']]['ttl']['aop_fy']+=($bar['rupiah']/1000);
}

#HITUNG UNTUK GTTL
foreach($kodeblok as $estate => $valdiv){
	foreach($valdiv as $div => $valkodeblok){
		foreach($valkodeblok as $blok){
			foreach($headakun as $akun){				
				foreach($arrsubhead1 as $subhead => $nmsubhead){
					foreach($arrsubhead2 as $bln => $nmsubhead2){
						$biaya[$blok]['gtl'][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
					}
				}
			}
		}
	}
}



#number format
$nf2=2;
$nf0=0;

#number format
if($barishide=='1'){
	$stylerow="";
}else{	
	$stylerow="style=display:none";
}	

$nmakn=makeOption($dbname,'keu_5akun','noakun,namaakun');
$nobrsgreen="";
$nobrsgreenp="";
$nobrsyellow="";
$nobrsred="";
$nobrsredp="";
$nobrttl=$nobrttlp="";

$no=0;$nodiv=0;$gtrluas=0;$green=$yellow=$red=$greenp=$redp=0; 
$tdluas=$teluas=$tbcluas=$tdcluas=array();
$stdivbiaya=array();
foreach($kodeblok as $estate => $valdiv){
	$est=0;
	foreach($valdiv as $div => $valkodeblok){
		$row=0;$nodiv+=1;
		foreach($valkodeblok as $blok){
			$row+=1;$est+=1;$no+=1;
			$nmha=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$nomorblok."'");
			$nmpkk=makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$nomorblok."'");
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$blok."'");
			
			$title="";
			$title.="\nBlok : ".$nomorblok."";
			$title.="\nLuas : ".numb_format($nmha[$nomorblok],2)." Ha";
			$title.="\nPokok : ".$nmpkk[$nomorblok]."";
			
			$tab.="<tr class=rowcontent ".$stylerow." id=row_det_".$no." name=".$estate."[] onclick=getmark(this.id); ondblclick=getdetail(this.id,'".$blok."'); title=\"Single click untuk memberi tanda.".$title."\">";
			$tab.="<td align=center>".$no."</td>";
			#$tab.="<td>".$nmakun[$div]."</td>";
			if($nmkeg[$blok]==''){
				$nmkeg[$blok]=$blok;
			}
			$tab.="<td id=kdblok_".$no.">".ucwords(strtolower($nmkeg[$blok]))."</td>";
			
			foreach($headakun as $akun){				
				foreach($arrsubhead1 as $subhead => $nmsubhead){
					foreach($arrsubhead2 as $bln => $nmsubhead2){
						$style2=$style; $name="";
						$class="class=".$akun."det[]";
						$stylefontbln2=$stylefontbln;
						if($subhead=='ttl'){
							if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){
								$class="";
								$style2="";
								$stylefontbln2="";
							}else{
								$class="class=bln".$akun."det[]";
								$name="name=".$akun."_".$subhead."det[]";
							}
						}else{
							if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){								
								$stylefontbln2="style=font-weight:normal;";
							}else{
								$class="class=bln".$akun."det[]";
								$name="name=".$akun."_".$subhead."det[]";
							}
						}
						
						
						$rpperblok=0;
						if($jenis=='rpha'){
							if($biaya['ha']['ttl'][$bln]!=''){									
								$rpperblok=$biaya[$blok][$akun][$subhead][$bln]/$biaya['ha']['ttl'][$bln];
							}
						}else if($jenis=='rppkk'){
							if($biaya['pkk']['ttl'][$bln]!=''){									
								$rpperblok=$biaya[$blok][$akun][$subhead][$bln]/$biaya['pkk']['ttl'][$bln];
							}
						}else if($jenis=='rpprd'){
							if($biaya['prd']['ttl'][$bln]!=''){									
								$rpperblok=$biaya[$blok][$akun][$subhead][$bln]/$biaya['prd']['ttl'][$bln];
							}
						}else{
							#isinya rupiah
							$rpperblok=$biaya[$blok][$akun][$subhead][$bln];
						}
						
						$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($rpperblok,2)."</font></td>";
						
						$stdivbiaya[$div][$akun][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
						$stestbiaya[$estate][$akun][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
						$stgtbiaya[$akun][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
					}
				}
			}
			
			
			$awal=($no-$row)+1;
			$awalest=($no-$est)+1;
		}
		/* # TOTAL PER DIVISI
		$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#E7FCE4; title=\"Click untuk show atau hide baris.\" onclick=hiderow('".$awal."','".$no."','div','det')>";
		$tab.="<td align=center>".$nodiv."</td>";
		$tab.="<td align=left colspan=2>Sub ttl ".$nmakun[$div]."</td>";
		
		
		foreach($headakun as $akun){				
			foreach($arrsubhead1 as $subhead => $nmsubhead){
				foreach($arrsubhead2 as $bln => $nmsubhead2){
					$style2=$style; $name="";
					$class="class=".$akun."det[]";
					$stylefontbln2=$stylefontbln;
					if($subhead=='ttl'){
						if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){
							$class="";
							$style2="";
							$stylefontbln2="";
						}else{
							$class="class=bln".$akun."det[]";
							$name="name=".$akun."_".$subhead."det[]";
						}
					}else{
						if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){								
							$stylefontbln2="style=font-weight:normal;";
						}else{
							$class="class=bln".$akun."det[]";
							$name="name=".$akun."_".$subhead."det[]";
						}
					}
					$stdivrpha=0;
					if($jenis=='rpha'){
						if($biaya['ha']['ttl'][$bln]!=''){
							$stdivrpha=$stdivbiaya[$div][$akun][$subhead][$bln]/$biaya['ha']['ttl'][$bln];
						}
					}elseif($jenis=='rppkk'){
						if($biaya['pkk']['ttl'][$bln]!=''){
							$stdivrpha=$stdivbiaya[$div][$akun][$subhead][$bln]/$biaya['pkk']['ttl'][$bln];
						}
					}elseif($jenis=='rpprd'){
						if($biaya['prd']['ttl'][$bln]!=''){
							$stdivrpha=$stdivbiaya[$div][$akun][$subhead][$bln]/$biaya['prd']['ttl'][$bln];
						}
					}else{
						#isinya rupiah
						$stdivrpha=$stdivbiaya[$div][$akun][$subhead][$bln];
					}
					$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stdivrpha,2)."</font></td>";
				}
			}
		}
		
		
		$tab.="</tr>";
 */
	}
	$nodiv+=1;
	/* # TOTAL ESTATE
	$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#C9FEFA; title=\"Click untuk show atau hide baris.\" onclick=hiderow('".$awalest."','".$no."','est','det')>";
	$tab.="<td align=center>".$nodiv."</td>";
	$tab.="<td align=left colspan=3>Sub ttl ".$nmakun[$estate]."</td>";
	
	foreach($headakun as $akun){				
		foreach($arrsubhead1 as $subhead => $nmsubhead){
			foreach($arrsubhead2 as $bln => $nmsubhead2){
				$style2=$style; $name="";
				$class="class=".$akun."det[]";
				$stylefontbln2=$stylefontbln;
				if($subhead=='ttl'){
					if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){
						$class="";
						$style2="";
						$stylefontbln2="";
					}else{
						$class="class=bln".$akun."det[]";
						$name="name=".$akun."_".$subhead."det[]";
					}
				}else{
					if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){								
						$stylefontbln2="style=font-weight:normal;";
					}else{
						$class="class=bln".$akun."det[]";
						$name="name=".$akun."_".$subhead."det[]";
					}
				}
				$stestrpha=0;
				if($jenis=='rpha'){
					if($biaya['ha']['ttl'][$bln]!=''){
						$stestrpha=$stestbiaya[$estate][$akun][$subhead][$bln]/$biaya['ha']['ttl'][$bln];
					}
				}elseif($jenis=='rppkk'){
					if($biaya['pkk']['ttl'][$bln]!=''){
						$stestrpha=$stestbiaya[$estate][$akun][$subhead][$bln]/$biaya['pkk']['ttl'][$bln];
					}
				}elseif($jenis=='rpprd'){
					if($biaya['prd']['ttl'][$bln]!=''){
						$stestrpha=$stestbiaya[$estate][$akun][$subhead][$bln]/$biaya['prd']['ttl'][$bln];
					}
				}else{
					#isinya rupiah
					$stestrpha=$stestbiaya[$estate][$akun][$subhead][$bln];
				}
				$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stestrpha,2)."</font></td>";
			}
		}
	}
	

	$tab.="</tr>"; */
}
$nodiv+=1;
# GRAND TOTAL
$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#C9FEFA; title=\"Click untuk show atau hide baris.\" onclick=hiderow('1','".$no."','est','det')>";
$tab.="<td align=left colspan=2>Grand total</td>";

foreach($headakun as $akun){				
	foreach($arrsubhead1 as $subhead => $nmsubhead){
		foreach($arrsubhead2 as $bln => $nmsubhead2){
			$style2=$style; $name="";
			$class="class=".$akun."det[]";
			$stylefontbln2=$stylefontbln;
			if($subhead=='ttl'){
				if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){
					$class="";
					$style2="";
					$stylefontbln2="";
				}else{
					$class="class=bln".$akun."det[]";
					$name="name=".$akun."_".$subhead."det[]";
				}
			}else{
				if(substr($bln,0,3)=='ytd' or substr($bln,0,3)=='aop'){								
					$stylefontbln2="style=font-weight:normal;";
				}else{
					$class="class=bln".$akun."det[]";
					$name="name=".$akun."_".$subhead."det[]";
				}
			}
			$stgtrpha=0;
			if($jenis=='rpha'){
				if($biaya['ha']['ttl'][$bln]!=''){
					$stgtrpha=$stgtbiaya[$akun][$subhead][$bln]/$biaya['ha']['ttl'][$bln];
				}
			}elseif($jenis=='rppkk'){
				if($biaya['pkk']['ttl'][$bln]!=''){
					$stgtrpha=$stgtbiaya[$akun][$subhead][$bln]/$biaya['pkk']['ttl'][$bln];
				}
			}elseif($jenis=='rpprd'){
				if($biaya['prd']['ttl'][$bln]!=''){
					$stgtrpha=$stgtbiaya[$akun][$subhead][$bln]/$biaya['prd']['ttl'][$bln];
				}
			}else{
				#isinya rupiah
				$stgtrpha=$stgtbiaya[$akun][$subhead][$bln];
			}
			
			$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stgtrpha,2)."</font></td>";
		}
	}
}
$tab.="</tr>";
$tab.="</tbody></table>";

$tab1.="<button onclick=kembali(); class=mybutton >" . $_SESSION['lang']['back'] . "</button>";
$nmtt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$nomorblok."'");
$nmha=makeOption($dbname,'setup_blok','kodeorg,luasareaproduktif',"kodeorg='".$nomorblok."'");
$nmpkk=makeOption($dbname,'setup_blok','kodeorg,jumlahpokok',"kodeorg='".$nomorblok."'");

if($proses!='excel'){	
	$tab1.="<table class=sortable cellspacing=1>";
}else{
	$tab1.="<table border=1 class=sortable cellspacing=1>";
}

$tab1.="
	<thead><tr class=rowheader style=text-align:center>
	<td rowspan=2>".$_SESSION['lang']['blok']."</td>
	<td rowspan=2>".$_SESSION['lang']['luas']."</td>
	<td rowspan=2>".$_SESSION['lang']['pokok']."</td>
	<td colspan=".(count($arrsubhead2)).">".$_SESSION['lang']['produksi']."</td>
	";
$tab1.="</tr><tr class=rowheader style=text-align:center>";
	foreach($arrsubhead2 as $bln => $nmsubhead2){
		$tab1.="<td align=center>".$nmsubhead2."</td>";
	}
$tab1.="
</tr></thead>";

$tab1.="
		<tr class=rowcontent>
			<td align=left>".$nomorblok."</td>
			<td align=right>".numb_format($nmha[$nomorblok],2)."</td>
			<td align=right>".numb_format($nmpkk[$nomorblok])."</td>";
	foreach($arrsubhead2 as $bln => $nmsubhead2){
		$tab1.="<td align=right>".numb_format($biaya['prd']['ttl'][$bln],2)."</td>";
	}
$tab1.="</tr>
	";
$tab1.="</table>";
$tab1.="<br>";



switch ($proses) {
######PREVIEW
    case 'getdetail':
        echo $tab1.$tab;
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
            if (!fwrite($handle, $tab1.$tab)) {
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