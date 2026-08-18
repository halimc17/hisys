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
$status    = checkPostGet('status', '');


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

#UNTUK HEADER
$headtnpasub=array(
	'ha'=>'ha',
	'sph'=>'sph',
	'pkk'=>'pkk',
	'prd'=>'prd'
);

$arrjns=array(
	"rp"   =>"Rupiah (000)",
	"rpha" =>"Rupiah (000) / Ha",
	"rppkk"=>"Rupiah (000) / Pokok",
	"rpprd"=>"Rupiah / Kg"
);



$headakun=array(
	'ha'=>'ha',
	'sph'=>'sph',
	'pkk'=>'pkk',
	'prd'=>'prd',
	'gtl'=>'gtl'
);

$nmakun=array(
	'ha'=>'Ha',
	'sph'=>'SPH',
	'pkk'=>'Pokok',
	'prd'=>'Production (Ton)'
);
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

$nmakun['gtl']=ucwords(strtolower('Total Biaya'))."<br><font style=font-size:10px;font-weight:normal;color:grey;>(".$arrjns[$jenis].")</font>";	
$str=" select * from ".$dbname.".keu_5akun WHERE 1=1 ".$whakun." order by noakun";
$res = fetchdata($str);
foreach($res as $bar){
	$nmakun[$bar['noakun']]=$klpbyy[substr($bar['noakun'],0,3)]." - ".ucwords(strtolower($bar['namaakun']))."<br><font style=font-size:10px;font-weight:normal;color:grey;>(".$arrjns[$jenis].")</font>";
	if(strlen($bar['noakun'])=='7'){		
		$headakun[$bar['noakun']]=$bar['noakun'];
	}
}

$headakun['3gtmat']='3gtmat';

$nmakun['3gtmat']=ucwords(strtolower('Total Material'));	
$str=" select * from ".$dbname.".kebun_pakaimaterial a WHERE 1=1 ".$whrpkmat." ".$whakunmat." order by kodebarang";
$res = fetchdata($str);
foreach($res as $bar){
	$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
	$nmakun[$bar['kodebarang']]=ucwords(strtolower($optnmbrg[$bar['kodebarang']]));
	$headakun[$bar['kodebarang']]=$bar['kodebarang'];
}

$str=" select distinct kodebarang from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whakun."";
$res = fetchdata($str);
foreach($res as $bar){
	$optnmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
	$nmakun[$bar['kodebarang']]=ucwords(strtolower($optnmbrg[$bar['kodebarang']]));
	$headakun[$bar['kodebarang']]=$bar['kodebarang'];
}


$arrsubhead1=array(
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

$arrsubhead2=$rangebln;
$arrsubhead2["ytd_ti"]="YTD-".substr($tahun,2,2);
$arrsubhead2["ytd_ll"]="YTD-".substr(($tahun-1),2,2);
$arrsubhead2["aop_fy"]="AOP FY";


if($proses=='preview'){	
	$tab.="<input hidden id=colhide value=3>";
	$tab.="<input hidden id=colunhide value=".(count($rangebln)+3).">";
	$tab.="<input hidden id=colhead2 value=".count($arrsubhead2).">";
	$tab.="<input hidden id=jlhbulan value=".count($rangebln).">";
	$tab.="<input hidden id=colhead1 value=".count($arrsubhead1).">";
	$tab.="<input hidden id=colhead1mat value=".count($arrsubhead1mat).">";
	$tab.="<input hidden id=xxxx value=".$cols.">";
}
if($style=="style=display:none" and $proses!='excel'){
	$colsmat=$colssub1=$cols=$colspan="3";
}else{
	$cols=count($arrsubhead1)*count($arrsubhead2);
	$colsmat=count($arrsubhead1mat)*count($arrsubhead2);
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
        <tr class=rowheader>
            <th align=center rowspan='3'>".$_SESSION['lang']['nourut']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['unit']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['divisi']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['blok']."</th>
            <th align=center rowspan='3'>".$_SESSION['lang']['tahuntanam']."</th>
			";
			foreach($headakun as $akun){				
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
				if(substr($akun,0,1)=='3'){
					#harusnya isinya material
					$colshead=$colsmat;
				}
				$tab.="<th align=center colspan=".$colshead." id=head".$akun." ".$onclick." ".$title."><font ".$font.">".$nmakun[$akun]."</font></th>";
			}
			
			
			
		$tab.="	
        </tr>
        <tr class=rowheader title=\"Click untuk show atau hide kolom.\">";
			
			foreach($headakun as $akun){
				$arrsubheader=$arrsubhead1;
				if(substr($akun,0,1)=='3'){
					$arrsubheader=$arrsubhead1mat;
				}
				foreach($arrsubheader as $subhead => $nmsubhead){
					$style2=$style;
					$class="class=".$akun."[]";
					$stylefont2=$stylefont.";color:#E67E22;";
					if($subhead=='ttl'){
						$class="";
						$style2="";
						$stylefont2="style=color:#E67E22;";
					}
					if(in_array($akun,$headtnpasub)){
						if($subhead=='ttl'){
							$tab.="<th align=center colspan=".$colssub1." ".$class." name=cost".$akun."[] id=sub".$akun.$subhead." ".$style2." onclick=showhidesubv2('".$akun."_".$subhead."[]',this.id,'head".$akun."')><font ".$stylefont2.">".$nmsubhead."</font></th>";
						}
					}else{
						$tab.="<th align=center colspan=".$colssub1." ".$class." name=cost".$akun."[] id=sub".$akun.$subhead." ".$style2." onclick=showhidesubv2('".$akun."_".$subhead."[]',this.id,'head".$akun."')><font ".$stylefont2.">".$nmsubhead."</font></th>";
					}
				}
			}
			
		$tab.="</tr>
		
        <tr class=rowheader>";
			foreach($headakun as $akun){
				$arrsubheader=$arrsubhead1;
				if(substr($akun,0,1)=='3'){
					$arrsubheader=$arrsubhead1mat;
				}
				foreach($arrsubheader as $subhead => $nmsubhead){
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

#fisik dan biaya
$str = "select a.*,b.tanggal from " . $dbname . ".kebun_pakaimaterial a 
left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
where 1=1 ".$whrpkmat." and substr(b.tanggal,1,7) between '".$periode1."' 
and  '".$periode2."' and jurnal='1' and (a.kodekegiatan like '621%' or a.kodekegiatan like '126%')"; 
$res = fetchdata($str);
foreach($res as $bar){
	$periodemat=substr($bar['tanggal'],0,7);
	$biaya[$bar['kodeorg']][$bar['kodebarang']]['ton'][$periodemat]+=($bar['kwantitas']/1000);
	$biaya[$bar['kodeorg']][$bar['kodebarang']]['ton']['ytd_ti']+=($bar['kwantitas']/1000);
	
	$biaya[$bar['kodeorg']][$bar['kodebarang']]['ttl'][$periodemat]+=(($bar['kwantitas']*$bar['hargasatuan'])/1000);
	$biaya[$bar['kodeorg']][$bar['kodebarang']]['ttl']['ytd_ti']+=(($bar['kwantitas']*$bar['hargasatuan'])/1000);
}

#fisik dan biaya lalu
$str = "select a.*,b.tanggal from " . $dbname . ".kebun_pakaimaterial a 
left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
where 1=1 ".$whrpkmat." and substr(b.tanggal,1,7) between '".$periodelalu1."' 
and  '".$periodelalu2."' and jurnal='1' and (a.kodekegiatan like '621%' or a.kodekegiatan like '126%')"; 
$res = fetchdata($str);
foreach($res as $bar){
	$periodemat=substr($bar['tanggal'],0,7);
	$biaya[$bar['kodeorg']][$bar['kodebarang']]['ton']['ytd_ll']+=($bar['kwantitas']/1000);
	
	$biaya[$bar['kodeorg']][$bar['kodebarang']]['ttl']['ytd_ll']+=(($bar['kwantitas']*$bar['hargasatuan'])/1000);
}
// echo"<pre>";
// print_r($biaya);
// echo"</pre>";

$akunupahpnn=array('6110101','6110102');
$akuntranspnn=array('6110103','6110104');

$lbrpupuk=array('621010302','621010305','621010308','126010802','126010805');
$transpupuk=array('621010323','621010324','126010823','126010824');

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
	#biaya pupuk
	if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['kodekegiatan'],$lbrpupuk)){
		#labor
		$biaya[$bar['kodeblok']][$bar['noakun']]['lab'][$bar['periode']]+=($bar['jumlah']/1000);
		$biaya[$bar['kodeblok']][$bar['noakun']]['lab']['ytd_ti']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)=='INV'){
		#material
		$biaya[$bar['kodeblok']][$bar['noakun']]['mat'][$bar['periode']]+=($bar['jumlah']/1000);
		$biaya[$bar['kodeblok']][$bar['noakun']]['mat']['ytd_ti']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)!='INV' and (in_array($bar['kodekegiatan'],$transpupuk) or substr($bar['kodejurnal'],0,3)=='VHC')){
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
	if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['kodekegiatan'],$lbrpupuk)){
		#labor
		$biaya[$bar['kodeblok']][$bar['noakun']]['lab']['ytd_ll']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)=='INV'){
		#material
		$biaya[$bar['kodeblok']][$bar['noakun']]['mat']['ytd_ll']+=($bar['jumlah']/1000);
	}else if(substr($bar['kodejurnal'],0,3)!='INV' and (in_array($bar['kodekegiatan'],$transpupuk) or substr($bar['kodejurnal'],0,3)=='VHC')){
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

#ini khusus budget kebun
$str=" select kodebarang,tipebudget,kodebudget,noakun,".$e." as sdbi,rp".$bulan." as bi,rupiah,tahunbudget,kodeorg,".$s." as sdbivol,fis".$bulan." as bivol,jumlah, satuanj,volume,".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whakun."";
$res = fetchdata($str);
foreach($res as $bar){
	if((substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK')){
		#LABOUR
		$biaya[$bar['kodeorg']][$bar['noakun']]['lab']['aop_fy']+=($bar['rupiah']/1000);
	}else if((substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL')){
		#MATERIAL			
		$biaya[$bar['kodeorg']][$bar['noakun']]['mat']['aop_fy']+=($bar['rupiah']/1000);
		
		#perbarang
		$biaya[$bar['kodeorg']][$bar['kodebarang']]['ttl']['aop_fy']+=($bar['rupiah']/1000); #rupiah
		$biaya[$bar['kodeorg']][$bar['kodebarang']]['ton']['aop_fy']+=($bar['jumlah']/1000); #fisik
	}else if($bar['kodebudget']=='VHC'){
		#TRANS
		$biaya[$bar['kodeorg']][$bar['noakun']]['tra']['aop_fy']+=($bar['rupiah']/1000);
	}else{
		#OTHER
		$biaya[$bar['kodeorg']][$bar['noakun']]['oth']['aop_fy']+=($bar['rupiah']/1000);
	}
	$biaya[$bar['kodeorg']][$bar['noakun']]['ttl']['aop_fy']+=($bar['rupiah']/1000);
}



#master blok
$str="select * from ".$dbname.".setup_blok_tahunan a where 1=1 ".$wh2." ".$where." and a.statusblok='TM' and (a.tahun like '".$tahun."%' or a.tahun like '".($tahun-1)."%') and a.luasareaproduktif>0 order by a.kodeorg";
$res = fetchdata($str);
$ada=0;$kodeblok=array();
foreach($res as $bar){
	$prdaresta=substr($bar['tahun'],0,4)."-".substr($bar['tahun'],4,2);
	#$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)]=substr($bar['kodeorg'],0,4);
	$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,6)][$bar['kodeorg']]=$bar['kodeorg'];
	
	#luas ha
	$biaya[$bar['kodeorg']]['ha']['ttl'][$prdaresta]=$bar['luasareaproduktif'];
	$biaya[$bar['kodeorg']]['ha']['ttl']['ytd_ti']=$bar['luasareaproduktif'];
	
	#pokok
	$biaya[$bar['kodeorg']]['pkk']['ttl'][$prdaresta]=$bar['jumlahpokok'];
	$biaya[$bar['kodeorg']]['pkk']['ttl']['ytd_ti']=$bar['jumlahpokok'];
	
	#sph
	$biaya[$bar['kodeorg']]['sph']['ttl'][$prdaresta]=$bar['jumlahpokok']/$bar['luasareaproduktif'];
	$biaya[$bar['kodeorg']]['sph']['ttl']['ytd_ti']=$bar['jumlahpokok']/$bar['luasareaproduktif'];
	
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
		#$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)]=substr($bar['kodeorg'],0,4);
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
	
	
	$prdbgtsdbi[$bar['kodeblok']] += ($bar['sdbi']/1000);
	$prdbgtbidiv[substr($bar['kodeblok'],0,6)] += ($bar['sdbi']/1000);
	$prdbgtbiest[substr($bar['kodeblok'],0,4)] += ($bar['sdbi']/1000);
	
	#produksi bgt
	$biaya[$bar['kodeblok']]['prd']['ttl']['aop_fy']+=$bar['sdbi']/1000;
}

#sekarang saya ingat, ini di patok karena yang di ambil cuma Biaya HK dan ini harus sama dengan di lap justifikasi
/* $whrkdj="and (kodejurnal in ('M0','PNN01','PNN02','CT01','BM01','SPK1','M','NOTAD','NOTAK','HPP','TGH01','BM','BK','KM','KK','POT') 
or kodejurnal like 'PRJ%')"; */
#$arrkdjurupah=array('M0','PNN01','PNN02','CT01','BM01','SPK1','M','NOTAD','NOTAK','HPP','TGH01','BM','BK','KM','KK','POT');


#HITUNG UNTUK GTTL
foreach($kodeblok as $estate => $valdiv){
	foreach($valdiv as $div => $valkodeblok){
		foreach($valkodeblok as $blok){
			foreach($headakun as $akun){
				$arrsubheader=$arrsubhead1;
				if(substr($akun,0,1)=='3'){
					$arrsubheader=$arrsubhead1mat;
				}
				foreach($arrsubheader as $subhead => $nmsubhead){
					foreach($arrsubhead2 as $bln => $nmsubhead2){
						if(!in_array($akun,$headtnpasub)){
							if(substr($akun,0,1)=='3'){
								$biaya[$blok]['3gtmat'][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
							}else{
								$biaya[$blok]['gtl'][$subhead][$bln]+=$biaya[$blok][$akun][$subhead][$bln];
								
							}
						}
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
			$title.="title=\"Single click untuk memberi tanda.";
			#$title.="\nDouble click untuk melihat detail akun.";
			$title.="\nBlok : ".$blok."";
			$title.="\nLuas : ".numb_format($nmha[$blok],2)." Ha";
			$title.="\nPokok : ".$nmpkk[$blok]."";
			$title.="\nProduksi : ".numb_format($prdtontitle[$blok],2)." Ton";
			
			$tab.="<tr class=rowcontent ".$stylerow." id=row_".$no." name=".$estate."[] onclick=getmark(this.id); ".$title."\">";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td>".$estate."</td>";
			$tab.="<td>".$div."</td>";
			$tab.="<td id=kdblok_".$no.">".$blok."</td>";
			$tab.="<td align=center>".$nmtt[$blok]."</td>";
			
			foreach($headakun as $akun){
				$arrsubheader=$arrsubhead1;
				if(substr($akun,0,1)=='3'){
					$arrsubheader=$arrsubhead1mat;
				}				
				foreach($arrsubheader as $subhead => $nmsubhead){
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
						#ISI
						if(in_array($akun,$headtnpasub)){
							if($subhead=='ttl'){
								$tab.="<td align=right ".$class." ".$name." ".$style2." id=".$akun."_".$nocol."_".$no."><font ".$stylefontbln2.">".numb_format($biaya[$blok][$akun][$subhead][$bln],2)."</font></td>";
							}
						}else{
							if(substr($akun,0,1)=='3'){
								#kg/pkk
								if($biaya[$blok]['pkk']['ttl'][$bln]!=''){									
									$biaya[$blok][$akun]['kgpkk'][$bln]=($biaya[$blok][$akun]['ton'][$bln]/$biaya[$blok]['pkk']['ttl'][$bln])*1000;
								}
							}
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
							$tab.="<td align=right ".$class." ".$name." ".$style2." id=".$akun."_".$nocol."_".$no."><font ".$stylefontbln2.">".numb_format($rpperblok,2)."</font></td>";
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
		$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#E7FCE4; title=\"Click untuk show atau hide baris.\" onclick=hiderow('".$awal."','".$no."','div')>";
		$tab.="<td align=center>".$nodiv."</td>";
		$tab.="<td align=center></td>";
		$tab.="<td align=left colspan=3>Divisi ".$div."</td>";
	
		foreach($headakun as $akun){
			$arrsubheader=$arrsubhead1;
			if(substr($akun,0,1)=='3'){
				$arrsubheader=$arrsubhead1mat;
			}	
			foreach($arrsubheader as $subhead => $nmsubhead){
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
					
					if(in_array($akun,$headtnpasub)){
						if($subhead=='ttl'){
							$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stdivbiaya[$div][$akun][$subhead][$bln],2)."</font></td>";
						}
					}else{
						if(substr($akun,0,1)=='3'){
							#kg/pkk
							if($stdivbiaya[$div]['pkk']['ttl'][$bln]!=''){
								$stdivbiaya[$div][$akun]['kgpkk'][$bln]=($stdivbiaya[$div][$akun]['ton'][$bln]/$stdivbiaya[$div]['pkk']['ttl'][$bln])*1000;
							}
						}
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
						$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stdivrpha,2)."</font></td>";
						
					}
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
	
	foreach($headakun as $akun){
		$arrsubheader=$arrsubhead1;
		if(substr($akun,0,1)=='3'){
			$arrsubheader=$arrsubhead1mat;
		}			
		foreach($arrsubheader as $subhead => $nmsubhead){
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
				if(in_array($akun,$headtnpasub)){
					if($subhead=='ttl'){							
						$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stestbiaya[$estate][$akun][$subhead][$bln],2)."</font></td>";
					}
				}else{
					if(substr($akun,0,1)=='3'){
						#kg/pkk
						if($stestbiaya[$estate]['pkk']['ttl'][$bln]!=''){
							$stestbiaya[$estate][$akun]['kgpkk'][$bln]=($stestbiaya[$estate][$akun]['ton'][$bln]/$stestbiaya[$estate]['pkk']['ttl'][$bln])*1000;
						}
					}
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
					$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stestrpha,2)."</font></td>";
				}
			}
		}
	}
	

	$tab.="</tr>";
}
$nodiv+=1;
# GRAND TOTAL
$tab.="<tr class=rowcontent style=cursor:pointer;background-color:#C9FEFA; title=\"Click untuk show atau hide baris.\" onclick=hiderow('1','".$no."','est')>";
$tab.="<td align=left colspan=5>PT ".$pt."</td>";

foreach($headakun as $akun){
	$arrsubheader=$arrsubhead1;
	if(substr($akun,0,1)=='3'){
		$arrsubheader=$arrsubhead1mat;
	}		
	foreach($arrsubheader as $subhead => $nmsubhead){
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
			if(in_array($akun,$headtnpasub)){
				if($subhead=='ttl'){						
					$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stgtbiaya[$akun][$subhead][$bln],2)."</font></td>";
				}
			}else{
				if(substr($akun,0,1)=='3'){
					#kg/pkk
					if($stgtbiaya['pkk']['ttl'][$bln]!=''){
						$stgtbiaya[$akun]['kgpkk'][$bln]=($stgtbiaya[$akun]['ton'][$bln]/$stgtbiaya['pkk']['ttl'][$bln])*1000;
					}
				}
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
				$tab.="<td align=right ".$class." ".$name." ".$style2."><font ".$stylefontbln2.">".numb_format($stgtrpha,2)."</font></td>";
			}
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
        $nop_ = "trend_lmto_per_block_breakdown_fertilizer";
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