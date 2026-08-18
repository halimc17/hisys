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


if($style=="style=display:none" and $proses=='preview'){
	$colspan="3";
}else{
	$colspan=(count($rangebln)+3);
}

if($proses=='preview'){	
	$tab.="<input hidden id=colhide value=3>";
	$tab.="<input hidden id=colunhide value=".(count($rangebln)+3).">";
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

#HARGA TBS
#harga jual tbs
$str = "select substr(tanggal,1,7) as periode, a.* from " . $dbname . ".pmn_hargabelitbs a where 1=1 ".$whtbs." and substr(tanggal,1,7) between '".$periodelalu1."' and  '".$periode2."' group by supplierid, tanggal"; 
$res = fetchdata($str);
$jlhpembagi=$dataselltbs=$jlhpembagilalu=$hargatbslalu=$hargatbs=$dataunitharga=array();
foreach($res as $bar){
	if($bar['periode']>=$periode1 and $bar['periode']<=$periode2){		
		$hargapertgl[$bar['supplierid']][$bar['periode']]+=$bar['hargadisbun'];
		$jlhpembagi[$bar['supplierid']][$bar['periode']]+=1;
		$dataselltbs[$bar['supplierid']][$bar['periode']]=$bar['periode'];
	}

	if($bar['periode']>=$periodelalu1 and $bar['periode']<=$periodelalusetahun2){
		$hargapertgllalu[$bar['supplierid']]+=$bar['hargadisbun'];
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
	
	
$tab.="<br>";


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
            <th align=center rowspan='2' colspan=".$colspan." id=headsph onclick=showhide('sph[]','headsph','1')>".$_SESSION['lang']['sph']."</th>
			<th align=center rowspan='2' colspan=".$colspan." id=headpkk onclick=showhide('pkk[]','headpkk','1')>".$_SESSION['lang']['pokok']."</th>
			
			<th align=center rowspan='2' colspan=".$colspan." id=headprd onclick=showhide('prd[]','headprd','1')>Production (Ton)</th>";
			
			#Harvesting Cost (Rp 000)
			$tab.="
			<th align=center colspan=".$colspan." class=pnn[] name=costpnn[] id=headpnnlab ".$style." onclick=showhide('pnnlab[]','headpnnlab#subpnnlbr','1')><font ".$stylefont.">Harvesting Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=pnn[] name=costpnn[] id=headpnnmat ".$style." onclick=showhide('pnnmat[]','headpnnmat#subpnnmat','1')><font ".$stylefont.">Harvesting Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=pnn[] name=costpnn[] id=headpnntrans ".$style." onclick=showhide('pnntrans[]','headpnntrans#subpnntrans','1')><font ".$stylefont.">Harvesting Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=pnn[] name=costpnn[] id=headpnnoth ".$style." onclick=showhide('pnnoth[]','headpnnoth#subpnnoth','1')><font ".$stylefont.">Harvesting Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." id=headpnnttl onclick=showalldetail('costpnn[]','pnn[]',this.id,'headpnnttl#subpnnttl')>Harvesting Cost (Rp 000)</th>";
			
			#Transport Cost (Rp 000)
			/* $tab.="
			<th align=center colspan=".$colspan." class=trans[] name=costtrans[] id=headtranslab ".$style." onclick=showhide('translab[]','headtranslab#subtranslbr','1')><font ".$stylefont.">Transport Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=trans[] name=costtrans[] id=headtransmat ".$style." onclick=showhide('transmat[]','headtransmat#subtransmat','1')><font ".$stylefont.">Transport Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=trans[] name=costtrans[] id=headtranstrans ".$style." onclick=showhide('transtrans[]','headtranstrans#subtranstrans','1')><font ".$stylefont.">Transport Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=trans[] name=costtrans[] id=headtransoth ".$style." onclick=showhide('transoth[]','headtransoth#subtransoth','1')><font ".$stylefont.">Transport Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." id=headtransttl onclick=showalldetail('costtrans[]','trans[]',this.id,'headtransttl#subtransttl')>Transport Cost (Rp 000)</th>"; */

			#Fertilizing Cost (Rp 000)
			$tab.="
			<th align=center colspan=".$colspan." class=ppk[] name=costppk[] id=headppklab ".$style." onclick=showhide('ppklab[]','headppklab#subppklbr','1')><font ".$stylefont.">Fertilizing Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=ppk[] name=costppk[] id=headppkmat ".$style." onclick=showhide('ppkmat[]','headppkmat#subppkmat','1')><font ".$stylefont.">Fertilizing Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=ppk[] name=costppk[] id=headppktrans ".$style." onclick=showhide('ppktrans[]','headppktrans#subppktrans','1')><font ".$stylefont.">Fertilizing Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=ppk[] name=costppk[] id=headppkoth ".$style." onclick=showhide('ppkoth[]','headppkoth#subppkoth','1')><font ".$stylefont.">Fertilizing Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." id=headppkttl onclick=showalldetail('costppk[]','ppk[]',this.id,'headppkttl#subppkttl')>Fertilizing Cost (Rp 000)</th>";

			#Maintenance Mature Cost (Rp 000)
			$tab.="
			<th align=center colspan=".$colspan." class=tm[] name=costtm[] id=headtmlab ".$style." onclick=showhide('tmlab[]','headtmlab#subtmlbr','1')><font ".$stylefont.">Maintenance Mature Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tm[] name=costtm[] id=headtmmat ".$style." onclick=showhide('tmmat[]','headtmmat#subtmmat','1')><font ".$stylefont.">Maintenance Mature Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tm[] name=costtm[] id=headtmtrans ".$style." onclick=showhide('tmtrans[]','headtmtrans#subtmtrans','1')><font ".$stylefont.">Maintenance Mature Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tm[] name=costtm[] id=headtmoth ".$style." onclick=showhide('tmoth[]','headtmoth#subtmoth','1')><font ".$stylefont.">Maintenance Mature Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." id=headtmttl onclick=showalldetail('costtm[]','tm[]',this.id,'headtmttl#subtmttl')>Maintenance Mature Cost (Rp 000)</th>";
			
			#Overhead Excl Depre (Rp 000)
			$tab.="
			<th align=center rowspan='2' colspan=".$colspan." id=headohxdep  onclick=showhide('ohxdep[]','headohxdep','1')>Overhead Excl Depre (Rp 000)</th>";
			
			#Total Production Cost Excl Depre (Rp 000)
			$tab.="
			<th align=center colspan=".$colspan." class=tcostprdxdep[] name=costtcostprdxdep[] id=headtcostprdxdeplab ".$style." onclick=showhide('tcostprdxdeplab[]','headtcostprdxdeplab#subtcostprdxdeplbr','1')><font ".$stylefont.">Total Production Cost Excl Depre (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tcostprdxdep[] name=costtcostprdxdep[] id=headtcostprdxdepmat ".$style." onclick=showhide('tcostprdxdepmat[]','headtcostprdxdepmat#subtcostprdxdepmat','1')><font ".$stylefont.">Total Production Cost Excl Depre (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tcostprdxdep[] name=costtcostprdxdep[] id=headtcostprdxdeptrans ".$style." onclick=showhide('tcostprdxdeptrans[]','headtcostprdxdeptrans#subtcostprdxdeptrans','1')><font ".$stylefont.">Total Production Cost Excl Depre (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tcostprdxdep[] name=costtcostprdxdep[] id=headtcostprdxdepoth ".$style." onclick=showhide('tcostprdxdepoth[]','headtcostprdxdepoth#subtcostprdxdepoth','1')><font ".$stylefont.">Total Production Cost Excl Depre (Rp 000)</font></th>
			<th align=center colspan=".$colspan." id=headtcostprdxdepttl onclick=showalldetail('costtcostprdxdep[]','tcostprdxdep[]',this.id,'headtcostprdxdepttl#subtcostprdxdepttl')>Total Production Cost Excl Depre (Rp 000)</th>";

			#Depreciation (Rp 000)
			$tab.="
			<th align=center rowspan='2' colspan=".$colspan." id=headdep  onclick=showhide('dep[]','headdep','1')>Depreciation (Rp 000)</th>";
			
			#Total Production Cost (Rp 000)
			$tab.="
			<th align=center colspan=".$colspan." class=tcostprd[] name=costtcostprd[] id=headtcostprdlab ".$style." onclick=showhide('tcostprdlab[]','headtcostprdlab#subtcostprdlbr','1')><font ".$stylefont.">Total Production Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tcostprd[] name=costtcostprd[] id=headtcostprdmat ".$style." onclick=showhide('tcostprdmat[]','headtcostprdmat#subtcostprdmat','1')><font ".$stylefont.">Total Production Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tcostprd[] name=costtcostprd[] id=headtcostprdtrans ".$style." onclick=showhide('tcostprdtrans[]','headtcostprdtrans#subtcostprdtrans','1')><font ".$stylefont.">Total Production Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." class=tcostprd[] name=costtcostprd[] id=headtcostprdoth ".$style." onclick=showhide('tcostprdoth[]','headtcostprdoth#subtcostprdoth','1')><font ".$stylefont.">Total Production Cost (Rp 000)</font></th>
			<th align=center colspan=".$colspan." id=headtcostprdttl onclick=showalldetail('costtcostprd[]','tcostprd[]',this.id,'headtcostprdttl#subtcostprdttl')>Total Production Cost (Rp 000)</th>";

			#Total Revenue (Rp 000)
			$tab.="
			<th align=center rowspan='2' colspan=".$colspan." id=headrev  onclick=showhide('rev[]','headrev','1')>Total Revenue (Rp 000)</th>";

			#Total Cash Profit (Rp 000)
			$tab.="
			<th align=center rowspan='2' colspan=".$colspan." id=headcashprofit  onclick=showhide('cashprofit[]','headcashprofit','1')>Total Cash Profit (Rp 000)</th>";
			
			#Total Gross Profit (Rp 000)
			$tab.="
			<th align=center rowspan='2' colspan=".$colspan." id=headgrossprofit  onclick=showhide('grossprofit[]','headgrossprofit','1')>Total Gross Profit (Rp 000)</th>";

			#Total Production Cost Excl Depre/Kg (Rp)
			$tab.="
			<th align=center colspan=".$colspan." class=tcostprdxdepperkg[] name=costtcostprdxdepperkg[] id=headtcostprdxdepperkglab ".$style." onclick=showhide('tcostprdxdepperkglab[]','headtcostprdxdepperkglab#subtcostprdxdepperkglbr','1')><font ".$stylefont.">Total Production Cost Excl Depre/Kg (Rp)</font></th>
			<th align=center colspan=".$colspan." class=tcostprdxdepperkg[] name=costtcostprdxdepperkg[] id=headtcostprdxdepperkgmat ".$style." onclick=showhide('tcostprdxdepperkgmat[]','headtcostprdxdepperkgmat#subtcostprdxdepperkgmat','1')><font ".$stylefont.">Total Production Cost Excl Depre/Kg (Rp)</font></th>
			<th align=center colspan=".$colspan." class=tcostprdxdepperkg[] name=costtcostprdxdepperkg[] id=headtcostprdxdepperkgtrans ".$style." onclick=showhide('tcostprdxdepperkgtrans[]','headtcostprdxdepperkgtrans#subtcostprdxdepperkgtrans','1')><font ".$stylefont.">Total Production Cost Excl Depre/Kg (Rp)</font></th>
			<th align=center colspan=".$colspan." class=tcostprdxdepperkg[] name=costtcostprdxdepperkg[] id=headtcostprdxdepperkgoth ".$style." onclick=showhide('tcostprdxdepperkgoth[]','headtcostprdxdepperkgoth#subtcostprdxdepperkgoth','1')><font ".$stylefont.">Total Production Cost Excl Depre/Kg (Rp)</font></th>
			<th align=center colspan=".$colspan." id=headtcostprdxdepperkgttl onclick=showalldetail('costtcostprdxdepperkg[]','tcostprdxdepperkg[]',this.id,'headtcostprdxdepperkgttl#subtcostprdxdepperkgttl')>Total Production Cost Excl Depre/Kg (Rp)</th>";

			#Total Production Cost/Kg (Rp)
			$tab.="
			<th align=center colspan=".$colspan." class=tcostprdperkg[] name=costtcostprdperkg[] id=headtcostprdperkglab ".$style." onclick=showhide('tcostprdperkglab[]','headtcostprdperkglab#subtcostprdperkglbr','1')><font ".$stylefont.">Total Production Cost/Kg (Rp)</font></th>
			<th align=center colspan=".$colspan." class=tcostprdperkg[] name=costtcostprdperkg[] id=headtcostprdperkgmat ".$style." onclick=showhide('tcostprdperkgmat[]','headtcostprdperkgmat#subtcostprdperkgmat','1')><font ".$stylefont.">Total Production Cost/Kg (Rp)</font></th>
			<th align=center colspan=".$colspan." class=tcostprdperkg[] name=costtcostprdperkg[] id=headtcostprdperkgtrans ".$style." onclick=showhide('tcostprdperkgtrans[]','headtcostprdperkgtrans#subtcostprdperkgtrans','1')><font ".$stylefont.">Total Production Cost/Kg (Rp)</font></th>
			<th align=center colspan=".$colspan." class=tcostprdperkg[] name=costtcostprdperkg[] id=headtcostprdperkgoth ".$style." onclick=showhide('tcostprdperkgoth[]','headtcostprdperkgoth#subtcostprdperkgoth','1')><font ".$stylefont.">Total Production Cost/Kg (Rp)</font></th>
			<th align=center colspan=".$colspan." id=headtcostprdperkgttl onclick=showalldetail('costtcostprdperkg[]','tcostprdperkg[]',this.id,'headtcostprdperkgttl#subtcostprdperkgttl')>Total Production Cost/Kg (Rp)</th>";
	
			#Revenue/Kg (Rp)
			$tab.="
			<th align=center rowspan='2' colspan=".$colspan." id=headrevperkg  onclick=showhide('revperkg[]','headrevperkg','1')>Revenue/Kg (Rp)</th>";

			#Total Cash Profit/Kg (Rp)
			$tab.="
			<th align=center rowspan='2' colspan=".$colspan." id=headcashprofitperkg  onclick=showhide('cashprofitperkg[]','headcashprofitperkg','1')>Total Cash Profit/Kg (Rp)</th>";
			
			#Gross Profit/Kg (Rp)
			$tab.="
			<th align=center rowspan='2' colspan=".$colspan." id=headgrossprofitperkg  onclick=showhide('grossprofitperkg[]','headgrossprofitperkg','1')>Gross Profit/Kg (Rp)</th>";
			
			#Gross Profit %/Kg
			$tab.="
			<th align=center rowspan='2' colspan=".$colspan." id=headgrossprofitpersen  onclick=showhide('grossprofitpersen[]','headgrossprofitpersen','1')>Gross Profit %/Kg</th>";

			#Yield/Ha (Ton)
			$tab.="<th align=center colspan=3>Yield/Ha (Ton)</th>";
			
			#Gross Profit/Ha (Rp Mn)
			$tab.="<th align=center rowspan='2' colspan=3>Gross Profit/Ha (Rp 000)</th>";
			
			#Yield/Pkk (Kg)
			$tab.="<th align=center rowspan='2' colspan=2>Yield/Pkk (Kg)</th>";
			
			#Gross Profit/Pkk (Rp 000)
			$tab.="<th align=center rowspan='2' colspan=3>Gross Profit/Pkk (Rp 000)</th>";
			
		$tab.="	
        </tr>
        <tr class=rowheader title=\"Click untuk show atau hide kolom.\">";
			#Harvesting Cost (Rp Mn)
			$tab.="<th align=center colspan=".$colspan." class=pnn[] name=costpnn[] id=subpnnlbr ".$style." onclick=showhide('pnnlab[]','headpnnlab#subpnnlbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=pnn[] name=costpnn[] id=subpnnmat ".$style." onclick=showhide('pnnmat[]','headpnnmat#subpnnmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=pnn[] name=costpnn[] id=subpnntrans ".$style." onclick=showhide('pnntrans[]','headpnntrans#subpnntrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=pnn[] name=costpnn[] id=subpnnoth ".$style." onclick=showhide('pnnoth[]','headpnnoth#subpnnoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subpnnttl  onclick=showhide('pnnttl[]','headpnnttl#subpnnttl','1')>Total</th>";

			/* #Transport Cost (Rp Mn)
			$tab.="<th align=center colspan=".$colspan." class=trans[]  name=costtrans[] id=subtranslbr ".$style." onclick=showhide('translab[]','headtranslab#subtranslbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=trans[]  name=costtrans[] id=subtransmat ".$style." onclick=showhide('transmat[]','headtransmat#subtransmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=trans[]  name=costtrans[] id=subtranstrans ".$style." onclick=showhide('transtrans[]','headtranstrans#subtranstrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=trans[]  name=costtrans[] id=subtransoth ".$style." onclick=showhide('transoth[]','headtransoth#subtransoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subtransttl  onclick=showhide('transttl[]','headtransttl#subtransttl','1')>Total</th>";
 */
			#Fertilizing Cost (Rp Mn)
			$tab.="<th align=center colspan=".$colspan." class=ppk[]  name=costppk[] id=subppklbr ".$style." onclick=showhide('ppklab[]','headppklab#subppklbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=ppk[]  name=costppk[] id=subppkmat ".$style." onclick=showhide('ppkmat[]','headppkmat#subppkmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=ppk[]  name=costppk[] id=subppktrans ".$style." onclick=showhide('ppktrans[]','headppktrans#subppktrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=ppk[]  name=costppk[] id=subppkoth ".$style." onclick=showhide('ppkoth[]','headppkoth#subppkoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subppkttl  onclick=showhide('ppkttl[]','headppkttl#subppkttl','1')>Total</th>";

			#Maintenance Mature Cost (Rp Mn)
			$tab.="<th align=center colspan=".$colspan." class=tm[]  name=costtm[] id=subtmlbr ".$style." onclick=showhide('tmlab[]','headtmlab#subtmlbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tm[]  name=costtm[] id=subtmmat ".$style." onclick=showhide('tmmat[]','headtmmat#subtmmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tm[]  name=costtm[] id=subtmtrans ".$style." onclick=showhide('tmtrans[]','headtmtrans#subtmtrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tm[]  name=costtm[] id=subtmoth ".$style." onclick=showhide('tmoth[]','headtmoth#subtmoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subtmttl  onclick=showhide('tmttl[]','headtmttl#subtmttl','1')>Total</th>";

			#Total Production Cost Excl Depre (Rp Mn)
			$tab.="<th align=center colspan=".$colspan." class=tcostprdxdep[]  name=costtcostprdxdep[] id=subtcostprdxdeplbr ".$style." onclick=showhide('tcostprdxdeplab[]','headtcostprdxdeplab#subtcostprdxdeplbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprdxdep[]  name=costtcostprdxdep[] id=subtcostprdxdepmat ".$style." onclick=showhide('tcostprdxdepmat[]','headtcostprdxdepmat#subtcostprdxdepmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprdxdep[]  name=costtcostprdxdep[] id=subtcostprdxdeptrans ".$style." onclick=showhide('tcostprdxdeptrans[]','headtcostprdxdeptrans#subtcostprdxdeptrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprdxdep[]  name=costtcostprdxdep[] id=subtcostprdxdepoth ".$style." onclick=showhide('tcostprdxdepoth[]','headtcostprdxdepoth#subtcostprdxdepoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subtcostprdxdepttl  onclick=showhide('tcostprdxdepttl[]','headtcostprdxdepttl#subtcostprdxdepttl','1')>Total</th>";

			#Total Production Cost (Rp Mn)
			$tab.="<th align=center colspan=".$colspan." class=tcostprd[]  name=costtcostprd[] id=subtcostprdlbr ".$style." onclick=showhide('tcostprdlab[]','headtcostprdlab#subtcostprdlbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprd[]  name=costtcostprd[] id=subtcostprdmat ".$style." onclick=showhide('tcostprdmat[]','headtcostprdmat#subtcostprdmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprd[]  name=costtcostprd[] id=subtcostprdtrans ".$style." onclick=showhide('tcostprdtrans[]','headtcostprdtrans#subtcostprdtrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprd[]  name=costtcostprd[] id=subtcostprdoth ".$style." onclick=showhide('tcostprdoth[]','headtcostprdoth#subtcostprdoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subtcostprdttl  onclick=showhide('tcostprdttl[]','headtcostprdttl#subtcostprdttl','1')>Total</th>";

			#Total Production Cost Excl Depre/Kg (Rp)
			$tab.="<th align=center colspan=".$colspan." class=tcostprdxdepperkg[]  name=costtcostprdxdepperkg[] id=subtcostprdxdepperkglbr ".$style." onclick=showhide('tcostprdxdepperkglab[]','headtcostprdxdepperkglab#subtcostprdxdepperkglbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprdxdepperkg[]  name=costtcostprdxdepperkg[] id=subtcostprdxdepperkgmat ".$style." onclick=showhide('tcostprdxdepperkgmat[]','headtcostprdxdepperkgmat#subtcostprdxdepperkgmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprdxdepperkg[]  name=costtcostprdxdepperkg[] id=subtcostprdxdepperkgtrans ".$style." onclick=showhide('tcostprdxdepperkgtrans[]','headtcostprdxdepperkgtrans#subtcostprdxdepperkgtrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprdxdepperkg[]  name=costtcostprdxdepperkg[] id=subtcostprdxdepperkgoth ".$style." onclick=showhide('tcostprdxdepperkgoth[]','headtcostprdxdepperkgoth#subtcostprdxdepperkgoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subtcostprdxdepperkgttl  onclick=showhide('tcostprdxdepperkgttl[]','headtcostprdxdepperkgttl#subtcostprdxdepperkgttl','1')>Total</th>";

			#Total Production Cost/Kg (Rp)
			$tab.="<th align=center colspan=".$colspan." class=tcostprdperkg[]  name=costtcostprdperkg[] id=subtcostprdperkglbr ".$style." onclick=showhide('tcostprdperkglab[]','headtcostprdperkglab#subtcostprdperkglbr','1')><font ".$stylefont.">Labor</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprdperkg[]  name=costtcostprdperkg[] id=subtcostprdperkgmat ".$style." onclick=showhide('tcostprdperkgmat[]','headtcostprdperkgmat#subtcostprdperkgmat','1')><font ".$stylefont.">Material</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprdperkg[]  name=costtcostprdperkg[] id=subtcostprdperkgtrans ".$style." onclick=showhide('tcostprdperkgtrans[]','headtcostprdperkgtrans#subtcostprdperkgtrans','1')><font ".$stylefont.">Transport</font></th>";
			$tab.="<th align=center colspan=".$colspan." class=tcostprdperkg[]  name=costtcostprdperkg[] id=subtcostprdperkgoth ".$style." onclick=showhide('tcostprdperkgoth[]','headtcostprdperkgoth#subtcostprdperkgoth','1')><font ".$stylefont.">Other</font></th>";
			$tab.="<th align=center colspan=".$colspan." id=subtcostprdperkgttl  onclick=showhide('tcostprdperkgttl[]','headtcostprdperkgttl#subtcostprdperkgttl','1')>Total</th>";

			#Yield/Ha (Ton)
			$tab.="<th align=center colspan=2>YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center rowspan=2>AOP FY</th>";

		$tab.="</tr>
		
        <tr class=rowheader>";
			#HA
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=ha[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			#SPH
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=sph[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			#PKK
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=pkk[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
			#PRD
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=prd[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
		
			#Harvesting Cost (Rp Mn)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=pnnlab[] class=pnn[] ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Harvesting Cost (Rp Mn)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=pnnmat[] class=pnn[] ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Harvesting Cost (Rp Mn)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=pnntrans[] class=pnn[] ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Harvesting Cost (Rp Mn)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=pnnoth[] class=pnn[] ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costpnn[] class=pnn[] ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Harvesting Cost (Rp Mn)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=pnnttl[] class=pnn[] ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	

			/* #Transport Cost (Rp Mn)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=translab[] class=trans[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Transport Cost (Rp Mn)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=transmat[]  class=trans[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Transport Cost (Rp Mn)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=transtrans[]  class=trans[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Transport Cost (Rp Mn)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=transoth[]  class=trans[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtrans[] class=trans[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Transport Cost (Rp Mn)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=transttl[]  class=trans[]  ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	
 */
			#Fertilizing Cost (Rp Mn)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=ppklab[] class=ppk[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Fertilizing Cost (Rp Mn)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=ppkmat[]  class=ppk[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Fertilizing Cost (Rp Mn)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=ppktrans[]  class=ppk[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Fertilizing Cost (Rp Mn)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=ppkoth[]  class=ppk[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costppk[] class=ppk[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Fertilizing Cost (Rp Mn)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=ppkttl[]  class=ppk[]  ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	

			#Maintenance Mature Cost (Rp Mn)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tmlab[] class=tm[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Maintenance Mature Cost (Rp Mn)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tmmat[]  class=tm[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Maintenance Mature Cost (Rp Mn)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tmtrans[]  class=tm[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Maintenance Mature Cost (Rp Mn)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tmoth[]  class=tm[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtm[] class=tm[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Maintenance Mature Cost (Rp Mn)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tmttl[]  class=tm[]  ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	

			#Overhead Excl Depre (Rp Mn)
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=ohxdep[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";

			#Total Production Cost Excl Depre (Rp Mn)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdxdeplab[] class=tcostprdxdep[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost Excl Depre (Rp Mn)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdxdepmat[]  class=tcostprdxdep[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost Excl Depre (Rp Mn)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdxdeptrans[]  class=tcostprdxdep[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost Excl Depre (Rp Mn)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdxdepoth[]  class=tcostprdxdep[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost Excl Depre (Rp Mn)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdxdepttl[]  class=tcostprdxdep[]  ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	

			#Depreciation (Rp Mn)
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=dep[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";


			#Total Production Cost (Rp Mn)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdlab[] class=tcostprd[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost (Rp Mn)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdmat[]  class=tcostprd[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost (Rp Mn)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdtrans[]  class=tcostprd[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost (Rp Mn)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdoth[]  class=tcostprd[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprd[] class=tcostprd[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost (Rp Mn)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdttl[]  class=tcostprd[]  ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	

			#Total Revenue (Rp Mn)
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=rev[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
			#Total Cash Profit (Rp Mn)
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=cashprofit[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
			#Total Gross Profit (Rp Mn)
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=grossprofit[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
			#Total Production Cost Excl Depre/Kg (Rp)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdxdepperkglab[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost Excl Depre/Kg (Rp)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdxdepperkgmat[]  class=tcostprdxdepperkg[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost Excl Depre/Kg (Rp)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdxdepperkgtrans[]  class=tcostprdxdepperkg[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost Excl Depre/Kg (Rp)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdxdepperkgoth[]  class=tcostprdxdepperkg[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost Excl Depre/Kg (Rp)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdxdepperkgttl[]  class=tcostprdxdepperkg[]  ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	

			#Total Production Cost/Kg (Rp)#labor	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdperkglab[] class=tcostprdperkg[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost/Kg (Rp)#material	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdperkgmat[]  class=tcostprdperkg[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost/Kg (Rp)#transport	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdperkgtrans[]  class=tcostprdperkg[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost/Kg (Rp)#other	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdperkgoth[]  class=tcostprdperkg[]  ".$style."><font ".$stylefontbln.">".substr($bln,5,2)."</font></th>";
			}	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">YTD-".substr($tahun,2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">YTD-".substr(($tahun-1),2,2)."</font></th>";	
			$tab.="<th align=center name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style."><font ".$stylefont.">AOP FY</font></th>";	
				
			#Total Production Cost/Kg (Rp)#total	
			foreach($rangebln as $bln){	
				$tab.="<th align=center name=tcostprdperkgttl[]  class=tcostprdperkg[]  ".$style.">".substr($bln,5,2)."</th>";
			}	
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";	
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";	
			$tab.="<th align=center >AOP FY</th>";	


			#Revenue/Kg (Rp)
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=revperkg[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
			#Total Cash Profit/Kg (Rp)
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=cashprofitperkg[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
			#Gross Profit/Kg (Rp)
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=grossprofitperkg[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
			#Gross Profit %/Kg
			foreach($rangebln as $bln){			
				$tab.="<th align=center name=grossprofitpersen[] ".$style.">".substr($bln,5,2)."</th>";
			}
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
			#Yield/Ha (Ton)
			$tab.="<th align=center>Actual</th>";
			$tab.="<th align=center>AOP</th>";
			
			#Gross Profit/Ha (Rp Mn)
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
			
			#Yield/Pkk (Kg)
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
			#Gross Profit/Pkk (Rp 000)
			$tab.="<th align=center >YTD-".substr($tahun,2,2)."</th>";
			$tab.="<th align=center >YTD-".substr(($tahun-1),2,2)."</th>";
			$tab.="<th align=center >AOP FY</th>";
			
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
	$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)]=substr($bar['kodeorg'],0,4);
	$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,6)][$bar['kodeorg']]=$bar['kodeorg'];
	$luasblok[$bar['kodeorg']][$prdaresta]=$bar['luasareaproduktif'];
	$pokok[$bar['kodeorg']][$prdaresta]=$bar['jumlahpokok'];
	if($prdaresta==($tahun-1)."-".$bulan){
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
		$kodeblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,4)]=substr($bar['kodeorg'],0,4);
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

$whakun=" and substr(noakun,1,3) in ('611','621')";
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
	
	if(substr($bar['noakun'],0,3)=='611'){
		#biaya panen
		if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akunupahpnn)){
			#labor
			$byypnnlab[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)=='INV'){
			#material
			$byypnnmat[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akuntranspnn)){
			#transport
			$byypnntrans[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
		}else{
			$byypnnoth[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
		}
	}
	
	
	if(substr($bar['noakun'],0,3)=='621'){
		#TM
		if($bar['noakun']=='6210103'){
			#biaya pupuk
			if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['kodekegiatan'],$lbrpupuk)){
				#labor
				$byyppklab[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$byyppkmat[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and (in_array($bar['kodekegiatan'],$transpupuk) or substr($bar['kodejurnal'],0,3)=='VHC')){
				#transport
				$byyppktrans[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
			}else{
				$byyppkoth[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
			}
		}else{
			#biaya pemel
			if(substr($bar['kodejurnal'],0,3)!='INV'){
				#labor
				$byytmlab[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$byytmmat[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
				#transport
				$byytmtrans[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
			}else{
				$byytmoth[$bar['kodeblok']][$bar['periode']]+=($bar['jumlah']/1000);
			}
		}
	}
}

$str = "select kodeorg, sum(jumlah) as jumlah, periode,noakun  
from " . $dbname . ".keu_jurnaldt_vw a      
where 1=1 ".$whereJ." and periode between '".$periode1."' and  '".$periode2."' ".$whakunumum."     
group by noakun,kodeorg,periode"; 
$res = fetchdata($str);
foreach($res as $bar){
	#BIAYA UMUM
	if(substr($bar['noakun'],0,3)=='715'){
		#depre
		$byydep[$bar['kodeorg']][$bar['periode']]+=($bar['jumlah']/1000);
	}else{
		$byyohxdep[$bar['kodeorg']][$bar['periode']]+=($bar['jumlah']/1000);
		
		
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
		$bar['kodeblok']=$bar['kodeorg'];
	}
	
	if(substr($bar['noakun'],0,3)=='611'){
		#biaya panen
		if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akunupahpnn)){
			#labor
			$byypnnlablalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)=='INV'){
			#material
			$byypnnmatlalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
		}else if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['noakun'],$akuntranspnn)){
			#transport
			$byypnntranslalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
		}else{
			$byypnnothlalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
		}
	}
	if(substr($bar['noakun'],0,3)=='621'){
		#TM
		if($bar['noakun']=='6210103'){
			#biaya pupuk
			if(substr($bar['kodejurnal'],0,3)!='INV' and in_array($bar['kodekegiatan'],$lbrpupuk)){
				#labor
				$byyppklablalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$byyppkmatlalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and (in_array($bar['kodekegiatan'],$transpupuk) or substr($bar['kodejurnal'],0,3)=='VHC')){
				#transport
				$byyppktranslalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
			}else{
				$byyppkothlalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
			}
		}else{
			#biaya pemel
			if(substr($bar['kodejurnal'],0,3)!='INV'){
				#labor
				$byytmlablalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)=='INV'){
				#material
				$byytmmatlalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
			}else if(substr($bar['kodejurnal'],0,3)!='INV' and substr($bar['kodejurnal'],0,3)=='VHC'){
				#transport
				$byytmtranslalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
			}else{
				$byytmothlalu[$bar['kodeblok']]+=($bar['jumlah']/1000);
			}
		}
	}
}

#KHUSUS BYY UMUM (KALAU DI JADIKAN SATU MALAH NILAINYA SALAH)
$str = "select kodeorg, sum(jumlah) as jumlah,noakun 
from " . $dbname . ".keu_jurnaldt_vw a      
where 1=1 ".$whereJ." and periode between '".$periodelalu1."' and  '".$periodelalu2."' ".$whakunumum."     
group by noakun,kodeorg"; 
$res = fetchdata($str);
foreach($res as $bar){
	#BIAYA UMUM
	if(substr($bar['noakun'],0,3)=='715'){
		#depre
		$byydeplalu[$bar['kodeorg']]+=($bar['jumlah']/1000);		
	}else{
		$byyohxdeplalu[$bar['kodeorg']]+=($bar['jumlah']/1000);
	}	
}



$e="("; $s="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
    if($i<intval($bulan)){$e.=$r."+";$s.=$n."+";}else{$e.=$r;$s.=$n;}
}
$e.=")"; $s.=")";

$t="(fis01+fis02+fis03+fis04+fis05+fis06+fis07+fis08+fis09+fis10+fis11+fis12)";
$whereakun = " and (substr(noakun,1,3) in ('611','621') or noakun like '7%')";

#ini khusus budget kebun
$str=" select tipebudget,kodebudget,noakun,sum".$e." as sdbi,sum(rp".$bulan.") as bi,sum(rupiah) as rupiah,tahunbudget,kodeorg,sum".$s." as sdbivol,sum(fis".$bulan.") as bivol,sum(jumlah) as jumlah, satuanj,sum(volume) as volume,sum".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." ".$wh_bgtrp." and kodebudget!='UMUM' and tahunbudget = '".$tahun."' ".$whereakun." group by noakun, kodebudget, kodeorg";
$res = fetchdata($str);
foreach($res as $bar){
	if(substr($bar['noakun'],0,3)=='611'){
		if(substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK'){
			#LABOUR
			$byypnnlabaop[$bar['kodeorg']]+=($bar['rupiah']/1000);
		}else if(substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL'){
			#MATERIAL			
			$byypnnmataop[$bar['kodeorg']]+=($bar['rupiah']/1000);
		}else if($bar['kodebudget']=='VHC'){
			#TRANS
			$byypnntransaop[$bar['kodeorg']]+=($bar['rupiah']/1000);
		}else{
			#OTHER
			$byypnnothaop[$bar['kodeorg']]+=($bar['rupiah']/1000);
		}
	}
	
	if(substr($bar['noakun'],0,3)=='621'){
		if($bar['noakun']=='6210103'){
			#PUPUK
			if(substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK'){
				#LABOUR
				$byyppklabaop[$bar['kodeorg']]+=($bar['rupiah']/1000);
			}else if(substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL'){
				#MATERIAL			
				$byyppkmataop[$bar['kodeorg']]+=($bar['rupiah']/1000);
			}else if($bar['kodebudget']=='VHC'){
				#TRANS
				$byyppktransaop[$bar['kodeorg']]+=($bar['rupiah']/1000);
			}else {
				#OTHER
				$byyppkothaop[$bar['kodeorg']]+=($bar['rupiah']/1000);
			}
		}else{			
			if((substr($bar['kodebudget'],0,3)=='SDM' or $bar['kodebudget']=='KONTRAK')){
				#LABOUR
				$byytmlabaop[$bar['kodeorg']]+=($bar['rupiah']/1000);
			}else if((substr($bar['kodebudget'],0,2)=='M-' or $bar['kodebudget']=='TOOL')){
				#MATERIAL			
				$byytmmataop[$bar['kodeorg']]+=($bar['rupiah']/1000);
			}else if($bar['kodebudget']=='VHC'){
				#TRANS
				$byytmtransaop[$bar['kodeorg']]+=($bar['rupiah']/1000);
			}else{
				#OTHER
				$byytmothaop[$bar['kodeorg']]+=($bar['rupiah']/1000);
			}
		}
	}
}

#ini khusus budget UMUM
$str=" select tipebudget,kodebudget,noakun,sum".$e." as sdbi,sum(rp".$bulan.") as bi,sum(rupiah) as rupiah,tahunbudget,kodeorg,sum".$s." as sdbivol,sum(fis".$bulan.") as bivol,sum(jumlah) as jumlah, satuanj,sum(volume) as volume,sum".$t." as fsetahun from ".$dbname.".bgt_budget_detail a where 1=1 ".$where." and kodebudget='UMUM' and tahunbudget = '".$tahun."' ".$whereakun." group by noakun, kodeorg";
$res = fetchdata($str);
foreach($res as $bar){
	if(substr($bar['noakun'],0,3)=='715'){
		#depre
		$byydepaop[$bar['kodeorg']]+=($bar['jumlah']/1000);		
	}else{
		$byyohxdepaop[$bar['kodeorg']]+=($bar['jumlah']/1000);
	}
	
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
			
			$tab.="<tr class=rowcontent ".$stylerow." id=row_".$no." name=".$estate."[] onclick=getmark(this.id); ondblclick=getdetail(this.id,'".$blok."'); title=\"Single click untuk memberi tanda.".$title."\">";
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
			# === sph ===	
			foreach($rangebln as $bln){
				$pkkytdty[$blok]+=$pokok[$blok][$bln];
				$sphblokbln=array();
				if($luasblok[$blok][$bln]>0){	
					$sphblokbln[$blok][$bln]=$pokok[$blok][$bln]/$luasblok[$blok][$bln];
				}
				$tab.="<td align=right name=sph[] ".$style.">".@numb_format($sphblokbln[$blok][$bln],$nf0)."</td>";
			}
			
			$sphblokytd=$sphbloklalu=$sphblokaop=0;
			if($haytdty[$blok]>0){
				$sphblokytd=$pkkytdty[$blok]/$haytdty[$blok];
			}
			if($luaslalu[$blok]>0){
				$sphbloklalu=$pokoklalu[$blok]/$luaslalu[$blok];
			}
			if($luasaop[$blok]>0){				
				$sphblokaop=$pokokaop[$blok]/$luasaop[$blok];
			}
			$tab.="<td align=right >".numb_format($sphblokytd,$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($sphbloklalu,$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($sphblokaop,$nf0)."</td>";	
			
			
			# === end sph ===		

			
			# === pkk ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=pkk[] ".$style.">".numb_format($pokok[$blok][$bln],$nf0)."</td>";
				$pkkttldiv[$div][$bln]+=$pokok[$blok][$bln];
				$pkkttlest[$estate][$bln]+=$pokok[$blok][$bln];
				$pkkgt[$bln]+=$pokok[$blok][$bln];
			}	
			$tab.="<td align=right >".numb_format($pkkytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($pokoklalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($pokokaop[$blok],$nf0)."</td>";	
				
			#ttl divisi kanan	
			$pkkknytdtydiv[$div]+=$pkkytdty[$blok];	
			$pkkknlaludiv[$div]+=$pokoklalu[$blok];	
			$pkkknaopdiv[$div]+=$pokokaop[$blok];	
				
			#ttl estate kanan	
			$pkkknytdtyest[$estate]+=$pkkytdty[$blok];	
			$pkkknlaluest[$estate]+=$pokoklalu[$blok];	
			$pkkknaopest[$estate]+=$pokokaop[$blok];	
				
			#grand total	
			$pkkknytdtygt+=$pkkytdty[$blok];	
			$pkkknlalugt+=$pokoklalu[$blok];	
			$pkkknaopgt+=$pokokaop[$blok];	
			# === end pkk ===	

			# === Production (Ton) ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=prd[] ".$style.">".numb_format($prdton[$blok][$bln],$nf0)."</td>";
				$prdytdty[$blok]+=$prdton[$blok][$bln];
				$prdttldiv[$div][$bln]+=$prdton[$blok][$bln];
				$prdttlest[$estate][$bln]+=$prdton[$blok][$bln];
				$prdgt[$bln]+=$prdton[$blok][$bln];
			}	
			$tab.="<td align=right >".numb_format($prdytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($prdtonlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($prdtonaop[$blok],$nf0)."</td>";	
				
			#ttl divisi kanan	
			$prdknytdtydiv[$div]+=$prdytdty[$blok];	
			$prdknlaludiv[$div]+=$prdtonlalu[$blok];	
			$prdknaopdiv[$div]+=$prdtonaop[$blok];	
				
			#ttl estate kanan	
			$prdknytdtyest[$estate]+=$prdytdty[$blok];	
			$prdknlaluest[$estate]+=$prdtonlalu[$blok];	
			$prdknaopest[$estate]+=$prdtonaop[$blok];	
				
			#grand total	
			$prdknytdtygt+=$prdytdty[$blok];	
			$prdknlalugt+=$prdtonlalu[$blok];	
			$prdknaopgt+=$prdtonaop[$blok];	
			# === end Production (Ton) ===	
			
			# === Harvesting Cost (Rp Mn)==>lab ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=pnnlab[] class=pnn[] ".$style.">".numb_format($byypnnlab[$blok][$bln],$nf0)."</td>";
				$pnnlabytdty[$blok]+=$byypnnlab[$blok][$bln];
				$pnnlabttldiv[$div][$bln]+=$byypnnlab[$blok][$bln];
				$pnnlabttlest[$estate][$bln]+=$byypnnlab[$blok][$bln];
				$pnnlabgt[$bln]+=$byypnnlab[$blok][$bln];
			}	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($pnnlabytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($byypnnlablalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($byypnnlabaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$pnnlabknytdtydiv[$div]+=$pnnlabytdty[$blok];	
			$pnnlabknlaludiv[$div]+=$byypnnlablalu[$blok];	
			$pnnlabknaopdiv[$div]+=$byypnnlabaop[$blok];	
			#ttl estate kanan	
			$pnnlabknytdtyest[$estate]+=$pnnlabytdty[$blok];	
			$pnnlabknlaluest[$estate]+=$byypnnlablalu[$blok];	
			$pnnlabknaopest[$estate]+=$byypnnlabaop[$blok];	
			#grand total	
			$pnnlabknytdtygt+=$pnnlabytdty[$blok];	
			$pnnlabknlalugt+=$byypnnlablalu[$blok];	
			$pnnlabknaopgt+=$byypnnlabaop[$blok];	
			# === end Harvesting Cost (Rp Mn)==>lab ===	
			# === Harvesting Cost (Rp Mn)==>mat ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=pnnmat[] class=pnn[] ".$style.">".numb_format($byypnnmat[$blok][$bln],$nf0)."</td>";
				$pnnmatytdty[$blok]+=$byypnnmat[$blok][$bln];
				$pnnmatttldiv[$div][$bln]+=$byypnnmat[$blok][$bln];
				$pnnmatttlest[$estate][$bln]+=$byypnnmat[$blok][$bln];
				$pnnmatgt[$bln]+=$byypnnmat[$blok][$bln];
			}	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($pnnmatytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($byypnnmatlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($byypnnmataop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$pnnmatknytdtydiv[$div]+=$pnnmatytdty[$blok];	
			$pnnmatknlaludiv[$div]+=$byypnnmatlalu[$blok];	
			$pnnmatknaopdiv[$div]+=$byypnnmataop[$blok];	
			#ttl estate kanan	
			$pnnmatknytdtyest[$estate]+=$pnnmatytdty[$blok];	
			$pnnmatknlaluest[$estate]+=$byypnnmatlalu[$blok];	
			$pnnmatknaopest[$estate]+=$byypnnmataop[$blok];	
			#grand total	
			$pnnmatknytdtygt+=$pnnmatytdty[$blok];	
			$pnnmatknlalugt+=$byypnnmatlalu[$blok];	
			$pnnmatknaopgt+=$byypnnmataop[$blok];	
			# === end Harvesting Cost (Rp Mn)==>mat ===	
			# === Harvesting Cost (Rp Mn)==>trans ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=pnntrans[] class=pnn[] ".$style.">".numb_format($byypnntrans[$blok][$bln],$nf0)."</td>";
				$pnntransytdty[$blok]+=$byypnntrans[$blok][$bln];
				$pnntransttldiv[$div][$bln]+=$byypnntrans[$blok][$bln];
				$pnntransttlest[$estate][$bln]+=$byypnntrans[$blok][$bln];
				$pnntransgt[$bln]+=$byypnntrans[$blok][$bln];
			}	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($pnntransytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($byypnntranslalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($byypnntransaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$pnntransknytdtydiv[$div]+=$pnntransytdty[$blok];	
			$pnntransknlaludiv[$div]+=$byypnntranslalu[$blok];	
			$pnntransknaopdiv[$div]+=$byypnntransaop[$blok];	
			#ttl estate kanan	
			$pnntransknytdtyest[$estate]+=$pnntransytdty[$blok];	
			$pnntransknlaluest[$estate]+=$byypnntranslalu[$blok];	
			$pnntransknaopest[$estate]+=$byypnntransaop[$blok];	
			#grand total	
			$pnntransknytdtygt+=$pnntransytdty[$blok];	
			$pnntransknlalugt+=$byypnntranslalu[$blok];	
			$pnntransknaopgt+=$byypnntransaop[$blok];	
			# === end Harvesting Cost (Rp Mn)==>trans ===	
			# === Harvesting Cost (Rp Mn)==>oth ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=pnnoth[] class=pnn[] ".$style.">".numb_format($byypnnoth[$blok][$bln],$nf0)."</td>";
				$pnnothytdty[$blok]+=$byypnnoth[$blok][$bln];
				$pnnothttldiv[$div][$bln]+=$byypnnoth[$blok][$bln];
				$pnnothttlest[$estate][$bln]+=$byypnnoth[$blok][$bln];
				$pnnothgt[$bln]+=$byypnnoth[$blok][$bln];
			}	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($pnnothytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($byypnnothlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costpnn[] class=pnn[] ".$style.">".numb_format($byypnnothaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$pnnothknytdtydiv[$div]+=$pnnothytdty[$blok];	
			$pnnothknlaludiv[$div]+=$byypnnothlalu[$blok];	
			$pnnothknaopdiv[$div]+=$byypnnothaop[$blok];	
			#ttl estate kanan	
			$pnnothknytdtyest[$estate]+=$pnnothytdty[$blok];	
			$pnnothknlaluest[$estate]+=$byypnnothlalu[$blok];	
			$pnnothknaopest[$estate]+=$byypnnothaop[$blok];	
			#grand total	
			$pnnothknytdtygt+=$pnnothytdty[$blok];	
			$pnnothknlalugt+=$byypnnothlalu[$blok];	
			$pnnothknaopgt+=$byypnnothaop[$blok];	
			# === end Harvesting Cost (Rp Mn)==>oth ===	
			
			# === Harvesting Cost (Rp Mn)==>ttl ===	
			$byypnnttl=$byypnnttllalu=$byypnnttlaop[$blok]=array();
			foreach($rangebln as $bln){	
				$byypnnttl[$blok][$bln]=$byypnnlab[$blok][$bln]+$byypnnmat[$blok][$bln]+$byypnntrans[$blok][$bln]+$byypnnoth[$blok][$bln];
				
				
				$tab.="<td align=right name=pnnttl[] class=pnn[] ".$style.">".numb_format($byypnnttl[$blok][$bln],$nf0)."</td>";
				$pnnttlytdty[$blok]+=$byypnnttl[$blok][$bln];
				$pnnttlttldiv[$div][$bln]+=$byypnnttl[$blok][$bln];
				$pnnttlttlest[$estate][$bln]+=$byypnnttl[$blok][$bln];
				$pnnttlgt[$bln]+=$byypnnttl[$blok][$bln];
			}	
			$byypnnttllalu[$blok]=$byypnnlablalu[$blok]+$byypnnmatlalu[$blok]+$byypnntranslalu[$blok]+$byypnnothlalu[$blok];
			$byypnnttlaop[$blok]=$byypnnlabaop[$blok]+$byypnnmataop[$blok]+$byypnntransaop[$blok]+$byypnnothaop[$blok];
			
			$tab.="<td align=right >".numb_format($pnnttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byypnnttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byypnnttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$pnnttlknytdtydiv[$div]+=$pnnttlytdty[$blok];	
			$pnnttlknlaludiv[$div]+=$byypnnttllalu[$blok];	
			$pnnttlknaopdiv[$div]+=$byypnnttlaop[$blok];	
			#ttl estate kanan	
			$pnnttlknytdtyest[$estate]+=$pnnttlytdty[$blok];	
			$pnnttlknlaluest[$estate]+=$byypnnttllalu[$blok];	
			$pnnttlknaopest[$estate]+=$byypnnttlaop[$blok];	
			#grand total	
			$pnnttlknytdtygt+=$pnnttlytdty[$blok];	
			$pnnttlknlalugt+=$byypnnttllalu[$blok];	
			$pnnttlknaopgt+=$byypnnttlaop[$blok];	
			# === end Harvesting Cost (Rp Mn)==>ttl ===	
			
				
			# === Fertilizing Cost (Rp Mn)==>lab ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=ppklab[] class=ppk[] ".$style.">".numb_format($byyppklab[$blok][$bln],$nf0)."</td>";
				$ppklabytdty[$blok]+=$byyppklab[$blok][$bln];
				$ppklabttldiv[$div][$bln]+=$byyppklab[$blok][$bln];
				$ppklabttlest[$estate][$bln]+=$byyppklab[$blok][$bln];
				$ppklabgt[$bln]+=$byyppklab[$blok][$bln];
			}	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($ppklabytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($byyppklablalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($byyppklabaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$ppklabknytdtydiv[$div]+=$ppklabytdty[$blok];	
			$ppklabknlaludiv[$div]+=$byyppklablalu[$blok];	
			$ppklabknaopdiv[$div]+=$byyppklabaop[$blok];	
			#ttl estate kanan	
			$ppklabknytdtyest[$estate]+=$ppklabytdty[$blok];	
			$ppklabknlaluest[$estate]+=$byyppklablalu[$blok];	
			$ppklabknaopest[$estate]+=$byyppklabaop[$blok];	
			#grand total	
			$ppklabknytdtygt+=$ppklabytdty[$blok];	
			$ppklabknlalugt+=$byyppklablalu[$blok];	
			$ppklabknaopgt+=$byyppklabaop[$blok];	
			# === end Fertilizing Cost (Rp Mn)==>lab ===	
			# === Fertilizing Cost (Rp Mn)==>mat ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=ppkmat[] class=ppk[] ".$style.">".numb_format($byyppkmat[$blok][$bln],$nf0)."</td>";
				$ppkmatytdty[$blok]+=$byyppkmat[$blok][$bln];
				$ppkmatttldiv[$div][$bln]+=$byyppkmat[$blok][$bln];
				$ppkmatttlest[$estate][$bln]+=$byyppkmat[$blok][$bln];
				$ppkmatgt[$bln]+=$byyppkmat[$blok][$bln];
			}	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($ppkmatytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($byyppkmatlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($byyppkmataop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$ppkmatknytdtydiv[$div]+=$ppkmatytdty[$blok];	
			$ppkmatknlaludiv[$div]+=$byyppkmatlalu[$blok];	
			$ppkmatknaopdiv[$div]+=$byyppkmataop[$blok];	
			#ttl estate kanan	
			$ppkmatknytdtyest[$estate]+=$ppkmatytdty[$blok];	
			$ppkmatknlaluest[$estate]+=$byyppkmatlalu[$blok];	
			$ppkmatknaopest[$estate]+=$byyppkmataop[$blok];	
			#grand total	
			$ppkmatknytdtygt+=$ppkmatytdty[$blok];	
			$ppkmatknlalugt+=$byyppkmatlalu[$blok];	
			$ppkmatknaopgt+=$byyppkmataop[$blok];	
			# === end Fertilizing Cost (Rp Mn)==>mat ===	
			# === Fertilizing Cost (Rp Mn)==>trans ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=ppktrans[] class=ppk[] ".$style.">".numb_format($byyppktrans[$blok][$bln],$nf0)."</td>";
				$ppktransytdty[$blok]+=$byyppktrans[$blok][$bln];
				$ppktransttldiv[$div][$bln]+=$byyppktrans[$blok][$bln];
				$ppktransttlest[$estate][$bln]+=$byyppktrans[$blok][$bln];
				$ppktransgt[$bln]+=$byyppktrans[$blok][$bln];
			}	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($ppktransytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($byyppktranslalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($byyppktransaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$ppktransknytdtydiv[$div]+=$ppktransytdty[$blok];	
			$ppktransknlaludiv[$div]+=$byyppktranslalu[$blok];	
			$ppktransknaopdiv[$div]+=$byyppktransaop[$blok];	
			#ttl estate kanan	
			$ppktransknytdtyest[$estate]+=$ppktransytdty[$blok];	
			$ppktransknlaluest[$estate]+=$byyppktranslalu[$blok];	
			$ppktransknaopest[$estate]+=$byyppktransaop[$blok];	
			#grand total	
			$ppktransknytdtygt+=$ppktransytdty[$blok];	
			$ppktransknlalugt+=$byyppktranslalu[$blok];	
			$ppktransknaopgt+=$byyppktransaop[$blok];	
			# === end Fertilizing Cost (Rp Mn)==>trans ===	
			# === Fertilizing Cost (Rp Mn)==>oth ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=ppkoth[] class=ppk[] ".$style.">".numb_format($byyppkoth[$blok][$bln],$nf0)."</td>";
				$ppkothytdty[$blok]+=$byyppkoth[$blok][$bln];
				$ppkothttldiv[$div][$bln]+=$byyppkoth[$blok][$bln];
				$ppkothttlest[$estate][$bln]+=$byyppkoth[$blok][$bln];
				$ppkothgt[$bln]+=$byyppkoth[$blok][$bln];
			}	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($ppkothytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($byyppkothlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costppk[] class=ppk[] ".$style.">".numb_format($byyppkothaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$ppkothknytdtydiv[$div]+=$ppkothytdty[$blok];	
			$ppkothknlaludiv[$div]+=$byyppkothlalu[$blok];	
			$ppkothknaopdiv[$div]+=$byyppkothaop[$blok];	
			#ttl estate kanan	
			$ppkothknytdtyest[$estate]+=$ppkothytdty[$blok];	
			$ppkothknlaluest[$estate]+=$byyppkothlalu[$blok];	
			$ppkothknaopest[$estate]+=$byyppkothaop[$blok];	
			#grand total	
			$ppkothknytdtygt+=$ppkothytdty[$blok];	
			$ppkothknlalugt+=$byyppkothlalu[$blok];	
			$ppkothknaopgt+=$byyppkothaop[$blok];	
			# === end Fertilizing Cost (Rp Mn)==>oth ===	
			
				
			# === Fertilizing Cost (Rp Mn)==>ttl ===	
			foreach($rangebln as $bln){	
				$byyppkttl[$blok][$bln]=$byyppklab[$blok][$bln]+$byyppkmat[$blok][$bln]+$byyppktrans[$blok][$bln]+$byyppkoth[$blok][$bln];
				
				$tab.="<td align=right name=ppkttl[] class=ppk[] ".$style.">".numb_format($byyppkttl[$blok][$bln],$nf0)."</td>";
				$ppkttlytdty[$blok]+=$byyppkttl[$blok][$bln];
				$ppkttlttldiv[$div][$bln]+=$byyppkttl[$blok][$bln];
				$ppkttlttlest[$estate][$bln]+=$byyppkttl[$blok][$bln];
				$ppkttlgt[$bln]+=$byyppkttl[$blok][$bln];
			}	
			$byyppkttllalu[$blok]  =$byyppklablalu[$blok]+$byyppkmatlalu[$blok]+$byyppktranslalu[$blok]+$byyppkothlalu[$blok];
			
			$byyppkttlaop[$blok]   =$byyppklabaop[$blok]+$byyppkmataop[$blok]+$byyppktransaop[$blok]+$byyppkothaop[$blok];
			$tab.="<td align=right >".numb_format($ppkttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byyppkttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byyppkttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$ppkttlknytdtydiv[$div]+=$ppkttlytdty[$blok];	
			$ppkttlknlaludiv[$div]+=$byyppkttllalu[$blok];	
			$ppkttlknaopdiv[$div]+=$byyppkttlaop[$blok];	
			#ttl estate kanan	
			$ppkttlknytdtyest[$estate]+=$ppkttlytdty[$blok];	
			$ppkttlknlaluest[$estate]+=$byyppkttllalu[$blok];	
			$ppkttlknaopest[$estate]+=$byyppkttlaop[$blok];	
			#grand total	
			$ppkttlknytdtygt+=$ppkttlytdty[$blok];	
			$ppkttlknlalugt+=$byyppkttllalu[$blok];	
			$ppkttlknaopgt+=$byyppkttlaop[$blok];	
			# === end Fertilizing Cost (Rp Mn)==>ttl ===	


			
			
			# === Maintenance Mature Cost (Rp Mn)==>lab ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=tmlab[] class=tm[] ".$style.">".numb_format($byytmlab[$blok][$bln],$nf0)."</td>";
				$tmlabytdty[$blok]+=$byytmlab[$blok][$bln];
				$tmlabttldiv[$div][$bln]+=$byytmlab[$blok][$bln];
				$tmlabttlest[$estate][$bln]+=$byytmlab[$blok][$bln];
				$tmlabgt[$bln]+=$byytmlab[$blok][$bln];
			}	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($tmlabytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($byytmlablalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($byytmlabaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tmlabknytdtydiv[$div]+=$tmlabytdty[$blok];	
			$tmlabknlaludiv[$div]+=$byytmlablalu[$blok];	
			$tmlabknaopdiv[$div]+=$byytmlabaop[$blok];	
			#ttl estate kanan	
			$tmlabknytdtyest[$estate]+=$tmlabytdty[$blok];	
			$tmlabknlaluest[$estate]+=$byytmlablalu[$blok];	
			$tmlabknaopest[$estate]+=$byytmlabaop[$blok];	
			#grand total	
			$tmlabknytdtygt+=$tmlabytdty[$blok];	
			$tmlabknlalugt+=$byytmlablalu[$blok];	
			$tmlabknaopgt+=$byytmlabaop[$blok];	
			# === end Maintenance Mature Cost (Rp Mn)==>lab ===	
			# === Maintenance Mature Cost (Rp Mn)==>mat ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=tmmat[] class=tm[] ".$style.">".numb_format($byytmmat[$blok][$bln],$nf0)."</td>";
				$tmmatytdty[$blok]+=$byytmmat[$blok][$bln];
				$tmmatttldiv[$div][$bln]+=$byytmmat[$blok][$bln];
				$tmmatttlest[$estate][$bln]+=$byytmmat[$blok][$bln];
				$tmmatgt[$bln]+=$byytmmat[$blok][$bln];
			}	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($tmmatytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($byytmmatlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($byytmmataop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tmmatknytdtydiv[$div]+=$tmmatytdty[$blok];	
			$tmmatknlaludiv[$div]+=$byytmmatlalu[$blok];	
			$tmmatknaopdiv[$div]+=$byytmmataop[$blok];	
			#ttl estate kanan	
			$tmmatknytdtyest[$estate]+=$tmmatytdty[$blok];	
			$tmmatknlaluest[$estate]+=$byytmmatlalu[$blok];	
			$tmmatknaopest[$estate]+=$byytmmataop[$blok];	
			#grand total	
			$tmmatknytdtygt+=$tmmatytdty[$blok];	
			$tmmatknlalugt+=$byytmmatlalu[$blok];	
			$tmmatknaopgt+=$byytmmataop[$blok];	
			# === end Maintenance Mature Cost (Rp Mn)==>mat ===	
			
			# === Maintenance Mature Cost (Rp Mn)==>trans ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=tmtrans[] class=tm[] ".$style.">".numb_format($byytmtrans[$blok][$bln],$nf0)."</td>";
				$tmtransytdty[$blok]+=$byytmtrans[$blok][$bln];
				$tmtransttldiv[$div][$bln]+=$byytmtrans[$blok][$bln];
				$tmtransttlest[$estate][$bln]+=$byytmtrans[$blok][$bln];
				$tmtransgt[$bln]+=$byytmtrans[$blok][$bln];
			}	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($tmtransytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($byytmtranslalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($byytmtransaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tmtransknytdtydiv[$div]+=$tmtransytdty[$blok];	
			$tmtransknlaludiv[$div]+=$byytmtranslalu[$blok];	
			$tmtransknaopdiv[$div]+=$byytmtransaop[$blok];	
			#ttl estate kanan	
			$tmtransknytdtyest[$estate]+=$tmtransytdty[$blok];	
			$tmtransknlaluest[$estate]+=$byytmtranslalu[$blok];	
			$tmtransknaopest[$estate]+=$byytmtransaop[$blok];	
			#grand total	
			$tmtransknytdtygt+=$tmtransytdty[$blok];	
			$tmtransknlalugt+=$byytmtranslalu[$blok];	
			$tmtransknaopgt+=$byytmtransaop[$blok];	
			# === end Maintenance Mature Cost (Rp Mn)==>trans ===	
			# === Maintenance Mature Cost (Rp Mn)==>oth ===	
			foreach($rangebln as $bln){	
				$tab.="<td align=right name=tmoth[] class=tm[] ".$style.">".numb_format($byytmoth[$blok][$bln],$nf0)."</td>";
				$tmothytdty[$blok]+=$byytmoth[$blok][$bln];
				$tmothttldiv[$div][$bln]+=$byytmoth[$blok][$bln];
				$tmothttlest[$estate][$bln]+=$byytmoth[$blok][$bln];
				$tmothgt[$bln]+=$byytmoth[$blok][$bln];
			}	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($tmothytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($byytmothlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtm[] class=tm[] ".$style.">".numb_format($byytmothaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tmothknytdtydiv[$div]+=$tmothytdty[$blok];	
			$tmothknlaludiv[$div]+=$byytmothlalu[$blok];	
			$tmothknaopdiv[$div]+=$byytmothaop[$blok];	
			#ttl estate kanan	
			$tmothknytdtyest[$estate]+=$tmothytdty[$blok];	
			$tmothknlaluest[$estate]+=$byytmothlalu[$blok];	
			$tmothknaopest[$estate]+=$byytmothaop[$blok];	
			#grand total	
			$tmothknytdtygt+=$tmothytdty[$blok];	
			$tmothknlalugt+=$byytmothlalu[$blok];	
			$tmothknaopgt+=$byytmothaop[$blok];	
			# === end Maintenance Mature Cost (Rp Mn)==>oth ===	
			# === Maintenance Mature Cost (Rp Mn)==>ttl ===	
			foreach($rangebln as $bln){
				$byytmttl[$blok][$bln]=	$byytmlab[$blok][$bln]+$byytmmat[$blok][$bln]+$byytmtrans[$blok][$bln]+$byytmoth[$blok][$bln];

				$tab.="<td align=right name=tmttl[] class=tm[] ".$style.">".numb_format($byytmttl[$blok][$bln],$nf0)."</td>";
				$tmttlytdty[$blok]+=$byytmttl[$blok][$bln];
				$tmttlttldiv[$div][$bln]+=$byytmttl[$blok][$bln];
				$tmttlttlest[$estate][$bln]+=$byytmttl[$blok][$bln];
				$tmttlgt[$bln]+=$byytmttl[$blok][$bln];
			}	
			$byytmttllalu[$blok]=	$byytmlablalu[$blok]+$byytmmatlalu[$blok]+$byytmtranslalu[$blok]+$byytmothlalu[$blok];
			$byytmttlaop[$blok]=	$byytmlabaop[$blok]+$byytmmataop[$blok]+$byytmtransaop[$blok]+$byytmothaop[$blok];
			$tab.="<td align=right >".numb_format($tmttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytmttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytmttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tmttlknytdtydiv[$div]+=$tmttlytdty[$blok];	
			$tmttlknlaludiv[$div]+=$byytmttllalu[$blok];	
			$tmttlknaopdiv[$div]+=$byytmttlaop[$blok];	
			#ttl estate kanan	
			$tmttlknytdtyest[$estate]+=$tmttlytdty[$blok];	
			$tmttlknlaluest[$estate]+=$byytmttllalu[$blok];	
			$tmttlknaopest[$estate]+=$byytmttlaop[$blok];	
			#grand total	
			$tmttlknytdtygt+=$tmttlytdty[$blok];	
			$tmttlknlalugt+=$byytmttllalu[$blok];	
			$tmttlknaopgt+=$byytmttlaop[$blok];	
			# === end Maintenance Mature Cost (Rp Mn)==>ttl ===	

				
			# === Overhead Excl Depre (Rp Mn)==>ttl ===	
			$byyohxdepttl=array();
			foreach($rangebln as $bln){	
				$byyohxdepttl[$blok][$bln] = $nmha[$blok]/$ttlhaest[$estate]*$byyohxdep[$estate][$bln];
				
				$tab.="<td align=right name=ohxdep[] ".$style.">".numb_format($byyohxdepttl[$blok][$bln],$nf0)."</td>";
				$ohxdepttlytdty[$blok]+=$byyohxdepttl[$blok][$bln];
				$ohxdepttlttldiv[$div][$bln]+=$byyohxdepttl[$blok][$bln];
				$ohxdepttlttlest[$estate][$bln]+=$byyohxdepttl[$blok][$bln];
				$ohxdepttlgt[$bln]+=$byyohxdepttl[$blok][$bln];
			}
			
			$byyohxdepttllalu=$byyohxdepttlaop=array();
			$byyohxdepttllalu[$blok] = $nmha[$blok]/$ttlhaest[$estate]*$byyohxdeplalu[$estate];
			$byyohxdepttlaop[$blok] = $nmha[$blok]/$ttlhaest[$estate]*$byyohxdepaop[$estate];
			
			$tab.="<td align=right >".numb_format($ohxdepttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byyohxdepttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byyohxdepttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$ohxdepttlknytdtydiv[$div]+=$ohxdepttlytdty[$blok];	
			$ohxdepttlknlaludiv[$div]+=$byyohxdepttllalu[$blok];	
			$ohxdepttlknaopdiv[$div]+=$byyohxdepttlaop[$blok];	
			#ttl estate kanan	
			$ohxdepttlknytdtyest[$estate]+=$ohxdepttlytdty[$blok];	
			$ohxdepttlknlaluest[$estate]+=$byyohxdepttllalu[$blok];	
			$ohxdepttlknaopest[$estate]+=$byyohxdepttlaop[$blok];	
			#grand total	
			$ohxdepttlknytdtygt+=$ohxdepttlytdty[$blok];	
			$ohxdepttlknlalugt+=$byyohxdepttllalu[$blok];	
			$ohxdepttlknaopgt+=$byyohxdepttlaop[$blok];	
			# === end Overhead Excl Depre (Rp Mn)==>ttl ===	

			# === Total Production Cost Excl Depre (Rp Mn)==>lab ===	
			$byytcostprdxdeplab=$byytcostprdxdeplablalu=$byytcostprdxdeplabaop=array();
			foreach($rangebln as $bln){	
				$byytcostprdxdeplab[$blok][$bln]=$byypnnlab[$blok][$bln]+$byyppklab[$blok][$bln]+$byytmlab[$blok][$bln];
				
				$tab.="<td align=right name=tcostprdxdeplab[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdeplab[$blok][$bln],$nf0)."</td>";
				$tcostprdxdeplabytdty[$blok]+=$byytcostprdxdeplab[$blok][$bln];
				$tcostprdxdeplabttldiv[$div][$bln]+=$byytcostprdxdeplab[$blok][$bln];
				$tcostprdxdeplabttlest[$estate][$bln]+=$byytcostprdxdeplab[$blok][$bln];
				$tcostprdxdeplabgt[$bln]+=$byytcostprdxdeplab[$blok][$bln];
			}	
			
			$byytcostprdxdeplablalu[$blok]=$byypnnlablalu[$blok]+$byyppklablalu[$blok]+$byytmlablalu[$blok];
			$byytcostprdxdeplabaop[$blok]=$byypnnlabaop[$blok]+$byyppklabaop[$blok]+$byytmlabaop[$blok];


			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdeplabytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdeplablalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdeplabaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdxdeplabknytdtydiv[$div]+=$tcostprdxdeplabytdty[$blok];	
			$tcostprdxdeplabknlaludiv[$div]+=$byytcostprdxdeplablalu[$blok];	
			$tcostprdxdeplabknaopdiv[$div]+=$byytcostprdxdeplabaop[$blok];	
			#ttl estate kanan	
			$tcostprdxdeplabknytdtyest[$estate]+=$tcostprdxdeplabytdty[$blok];	
			$tcostprdxdeplabknlaluest[$estate]+=$byytcostprdxdeplablalu[$blok];	
			$tcostprdxdeplabknaopest[$estate]+=$byytcostprdxdeplabaop[$blok];	
			#grand total	
			$tcostprdxdeplabknytdtygt+=$tcostprdxdeplabytdty[$blok];	
			$tcostprdxdeplabknlalugt+=$byytcostprdxdeplablalu[$blok];	
			$tcostprdxdeplabknaopgt+=$byytcostprdxdeplabaop[$blok];	
			# === end Total Production Cost Excl Depre (Rp Mn)==>lab ===	

	
			# === Total Production Cost Excl Depre (Rp Mn)==>mat ===	
			$byytcostprdxdepmat=$byytcostprdxdepmataop=$byytcostprdxdepmatlalu=array();
			foreach($rangebln as $bln){
				$byytcostprdxdepmat[$blok][$bln]=$byypnnmat[$blok][$bln]+$byyppkmat[$blok][$bln]+$byytmmat[$blok][$bln];
				
				$tab.="<td align=right name=tcostprdxdepmat[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdepmat[$blok][$bln],$nf0)."</td>";
				$tcostprdxdepmatytdty[$blok]+=$byytcostprdxdepmat[$blok][$bln];
				$tcostprdxdepmatttldiv[$div][$bln]+=$byytcostprdxdepmat[$blok][$bln];
				$tcostprdxdepmatttlest[$estate][$bln]+=$byytcostprdxdepmat[$blok][$bln];
				$tcostprdxdepmatgt[$bln]+=$byytcostprdxdepmat[$blok][$bln];
			}
			$byytcostprdxdepmatlalu[$blok]=$byypnnmatlalu[$blok]+$byyppkmatlalu[$blok]+$byytmmatlalu[$blok];
			$byytcostprdxdepmataop[$blok]=$byypnnmataop[$blok]+$byyppkmataop[$blok]+$byytmmataop[$blok];
			
			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdepmatytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdepmatlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdepmataop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdxdepmatknytdtydiv[$div]+=$tcostprdxdepmatytdty[$blok];	
			$tcostprdxdepmatknlaludiv[$div]+=$byytcostprdxdepmatlalu[$blok];	
			$tcostprdxdepmatknaopdiv[$div]+=$byytcostprdxdepmataop[$blok];	
			#ttl estate kanan	
			$tcostprdxdepmatknytdtyest[$estate]+=$tcostprdxdepmatytdty[$blok];	
			$tcostprdxdepmatknlaluest[$estate]+=$byytcostprdxdepmatlalu[$blok];	
			$tcostprdxdepmatknaopest[$estate]+=$byytcostprdxdepmataop[$blok];	
			#grand total	
			$tcostprdxdepmatknytdtygt+=$tcostprdxdepmatytdty[$blok];	
			$tcostprdxdepmatknlalugt+=$byytcostprdxdepmatlalu[$blok];	
			$tcostprdxdepmatknaopgt+=$byytcostprdxdepmataop[$blok];	
			# === end Total Production Cost Excl Depre (Rp Mn)==>mat ===
			
			# === Total Production Cost Excl Depre (Rp Mn)==>trans ===	
			$byytcostprdxdeptrans=$byytcostprdxdeptranslalu=$byytcostprdxdeptransaop=array();
			foreach($rangebln as $bln){	
				$byytcostprdxdeptrans[$blok][$bln]=$byypnntrans[$blok][$bln]+$byyppktrans[$blok][$bln]+$byytmtrans[$blok][$bln];
				
				$tab.="<td align=right name=tcostprdxdeptrans[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdeptrans[$blok][$bln],$nf0)."</td>";
				$tcostprdxdeptransytdty[$blok]+=$byytcostprdxdeptrans[$blok][$bln];
				$tcostprdxdeptransttldiv[$div][$bln]+=$byytcostprdxdeptrans[$blok][$bln];
				$tcostprdxdeptransttlest[$estate][$bln]+=$byytcostprdxdeptrans[$blok][$bln];
				$tcostprdxdeptransgt[$bln]+=$byytcostprdxdeptrans[$blok][$bln];
			}
			$byytcostprdxdeptranslalu[$blok]=$byypnntranslalu[$blok]+$byyppktranslalu[$blok]+$byytmtranslalu[$blok];
			$byytcostprdxdeptransaop[$blok]=$byypnntransaop[$blok]+$byyppktransaop[$blok]+$byytmtransaop[$blok];

			
			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdeptransytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdeptranslalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdeptransaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdxdeptransknytdtydiv[$div]+=$tcostprdxdeptransytdty[$blok];	
			$tcostprdxdeptransknlaludiv[$div]+=$byytcostprdxdeptranslalu[$blok];	
			$tcostprdxdeptransknaopdiv[$div]+=$byytcostprdxdeptransaop[$blok];	
			#ttl estate kanan	
			$tcostprdxdeptransknytdtyest[$estate]+=$tcostprdxdeptransytdty[$blok];	
			$tcostprdxdeptransknlaluest[$estate]+=$byytcostprdxdeptranslalu[$blok];	
			$tcostprdxdeptransknaopest[$estate]+=$byytcostprdxdeptransaop[$blok];	
			#grand total	
			$tcostprdxdeptransknytdtygt+=$tcostprdxdeptransytdty[$blok];	
			$tcostprdxdeptransknlalugt+=$byytcostprdxdeptranslalu[$blok];	
			$tcostprdxdeptransknaopgt+=$byytcostprdxdeptransaop[$blok];	
			# === end Total Production Cost Excl Depre (Rp Mn)==>trans ===	
			
			# === Total Production Cost Excl Depre (Rp Mn)==>oth ===	
			$byytcostprdxdepoth=$byytcostprdxdepothaop=$byytcostprdxdepothlalu=array();
			foreach($rangebln as $bln){	
				$byytcostprdxdepoth[$blok][$bln]=$byypnnoth[$blok][$bln]+$byyppkoth[$blok][$bln]+$byytmoth[$blok][$bln]+$byyohxdepttl[$blok][$bln];
			
				$tab.="<td align=right name=tcostprdxdepoth[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdepoth[$blok][$bln],$nf0)."</td>";
				$tcostprdxdepothytdty[$blok]+=$byytcostprdxdepoth[$blok][$bln];
				$tcostprdxdepothttldiv[$div][$bln]+=$byytcostprdxdepoth[$blok][$bln];
				$tcostprdxdepothttlest[$estate][$bln]+=$byytcostprdxdepoth[$blok][$bln];
				$tcostprdxdepothgt[$bln]+=$byytcostprdxdepoth[$blok][$bln];
			}	
			$byytcostprdxdepothlalu[$blok]=$byypnnothlalu[$blok]+$byyppkothlalu[$blok]+$byytmothlalu[$blok]+$byyohxdepttllalu[$blok];
			$byytcostprdxdepothaop[$blok]=$byypnnothaop[$blok]+$byyppkothaop[$blok]+$byytmothaop[$blok]+$byyohxdepttlaop[$blok];

			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdepothytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdepothlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdepothaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdxdepothknytdtydiv[$div]+=$tcostprdxdepothytdty[$blok];	
			$tcostprdxdepothknlaludiv[$div]+=$byytcostprdxdepothlalu[$blok];	
			$tcostprdxdepothknaopdiv[$div]+=$byytcostprdxdepothaop[$blok];	
			#ttl estate kanan	
			$tcostprdxdepothknytdtyest[$estate]+=$tcostprdxdepothytdty[$blok];	
			$tcostprdxdepothknlaluest[$estate]+=$byytcostprdxdepothlalu[$blok];	
			$tcostprdxdepothknaopest[$estate]+=$byytcostprdxdepothaop[$blok];	
			#grand total	
			$tcostprdxdepothknytdtygt+=$tcostprdxdepothytdty[$blok];	
			$tcostprdxdepothknlalugt+=$byytcostprdxdepothlalu[$blok];	
			$tcostprdxdepothknaopgt+=$byytcostprdxdepothaop[$blok];	
			# === end Total Production Cost Excl Depre (Rp Mn)==>oth ===	

				
			# === Total Production Cost Excl Depre (Rp Mn)==>ttl ===
			$byytcostprdxdepttl=$byytcostprdxdepttllalu=$byytcostprdxdepttlaop=array();
			foreach($rangebln as $bln){	
				$byytcostprdxdepttl[$blok][$bln]=$byytcostprdxdeplab[$blok][$bln]+$byytcostprdxdepmat[$blok][$bln]+$byytcostprdxdeptrans[$blok][$bln]+$byytcostprdxdepoth[$blok][$bln];

				$tab.="<td align=right name=tcostprdxdepttl[] class=tcostprdxdep[] ".$style.">".numb_format($byytcostprdxdepttl[$blok][$bln],$nf0)."</td>";
				$tcostprdxdepttlytdty[$blok]+=$byytcostprdxdepttl[$blok][$bln];
				$tcostprdxdepttlttldiv[$div][$bln]+=$byytcostprdxdepttl[$blok][$bln];
				$tcostprdxdepttlttlest[$estate][$bln]+=$byytcostprdxdepttl[$blok][$bln];
				$tcostprdxdepttlgt[$bln]+=$byytcostprdxdepttl[$blok][$bln];
			}	
			$byytcostprdxdepttllalu[$blok]=$byytcostprdxdeplablalu[$blok]+$byytcostprdxdepmatlalu[$blok]+$byytcostprdxdeptranslalu[$blok]+$byytcostprdxdepothlalu[$blok];
			$byytcostprdxdepttlaop[$blok]=$byytcostprdxdeplabaop[$blok]+$byytcostprdxdepmataop[$blok]+$byytcostprdxdeptransaop[$blok]+$byytcostprdxdepothaop[$blok];
			
			$tab.="<td align=right >".numb_format($tcostprdxdepttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytcostprdxdepttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytcostprdxdepttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdxdepttlknytdtydiv[$div]+=$tcostprdxdepttlytdty[$blok];	
			$tcostprdxdepttlknlaludiv[$div]+=$byytcostprdxdepttllalu[$blok];	
			$tcostprdxdepttlknaopdiv[$div]+=$byytcostprdxdepttlaop[$blok];	
			#ttl estate kanan	
			$tcostprdxdepttlknytdtyest[$estate]+=$tcostprdxdepttlytdty[$blok];	
			$tcostprdxdepttlknlaluest[$estate]+=$byytcostprdxdepttllalu[$blok];	
			$tcostprdxdepttlknaopest[$estate]+=$byytcostprdxdepttlaop[$blok];	
			#grand total	
			$tcostprdxdepttlknytdtygt+=$tcostprdxdepttlytdty[$blok];	
			$tcostprdxdepttlknlalugt+=$byytcostprdxdepttllalu[$blok];	
			$tcostprdxdepttlknaopgt+=$byytcostprdxdepttlaop[$blok];	
			# === end Total Production Cost Excl Depre (Rp Mn)==>ttl ===	

	
			# === Depreciation (Rp Mn)==>ttl ===	
			$byydepttl=$byydepttllalu=$byydepttlaop=array();
			foreach($rangebln as $bln){	
				$byydepttl[$blok][$bln] = $nmha[$blok]/$ttlhaest[$estate]*$byydep[$estate][$bln];
				
				$tab.="<td align=right name=dep[] ".$style.">".numb_format($byydepttl[$blok][$bln],$nf0)."</td>";
				$depttlytdty[$blok]+=$byydepttl[$blok][$bln];
				$depttlttldiv[$div][$bln]+=$byydepttl[$blok][$bln];
				$depttlttlest[$estate][$bln]+=$byydepttl[$blok][$bln];
				$depttlgt[$bln]+=$byydepttl[$blok][$bln];
			}	
			$byydepttllalu[$blok] = $nmha[$blok]/$ttlhaest[$estate]*$byydeplalu[$estate];
			$byydepttlaop[$blok] = $nmha[$blok]/$ttlhaest[$estate]*$byydepaop[$estate];
			
			
			$tab.="<td align=right >".numb_format($depttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byydepttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byydepttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$depttlknytdtydiv[$div]+=$depttlytdty[$blok];	
			$depttlknlaludiv[$div]+=$byydepttllalu[$blok];	
			$depttlknaopdiv[$div]+=$byydepttlaop[$blok];	
			#ttl estate kanan	
			$depttlknytdtyest[$estate]+=$depttlytdty[$blok];	
			$depttlknlaluest[$estate]+=$byydepttllalu[$blok];	
			$depttlknaopest[$estate]+=$byydepttlaop[$blok];	
			#grand total	
			$depttlknytdtygt+=$depttlytdty[$blok];	
			$depttlknlalugt+=$byydepttllalu[$blok];	
			$depttlknaopgt+=$byydepttlaop[$blok];	
			# === end Depreciation (Rp Mn)==>ttl ===	

	
			# === Total Production Cost (Rp Mn)==>lab ===	
			$byytcostprdlab=array();
			foreach($rangebln as $bln){	
				$byytcostprdlab[$blok][$bln]=$byytcostprdxdeplab[$blok][$bln];
	
			
				$tab.="<td align=right name=tcostprdlab[] class=tcostprd[] ".$style.">".numb_format($byytcostprdlab[$blok][$bln],$nf0)."</td>";
				$tcostprdlabytdty[$blok]+=$byytcostprdlab[$blok][$bln];
				$tcostprdlabttldiv[$div][$bln]+=$byytcostprdlab[$blok][$bln];
				$tcostprdlabttlest[$estate][$bln]+=$byytcostprdlab[$blok][$bln];
				$tcostprdlabgt[$bln]+=$byytcostprdlab[$blok][$bln];
			}	
			
			$byytcostprdlablalu=array();
			$byytcostprdlablalu[$blok]=$byytcostprdxdeplablalu[$blok];
			$byytcostprdlabaop=array();
			$byytcostprdlabaop[$blok]=$byytcostprdxdeplabaop[$blok];


			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($tcostprdlabytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($byytcostprdlablalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($byytcostprdlabaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdlabknytdtydiv[$div]+=$tcostprdlabytdty[$blok];	
			$tcostprdlabknlaludiv[$div]+=$byytcostprdlablalu[$blok];	
			$tcostprdlabknaopdiv[$div]+=$byytcostprdlabaop[$blok];	
			#ttl estate kanan	
			$tcostprdlabknytdtyest[$estate]+=$tcostprdlabytdty[$blok];	
			$tcostprdlabknlaluest[$estate]+=$byytcostprdlablalu[$blok];	
			$tcostprdlabknaopest[$estate]+=$byytcostprdlabaop[$blok];	
			#grand total	
			$tcostprdlabknytdtygt+=$tcostprdlabytdty[$blok];	
			$tcostprdlabknlalugt+=$byytcostprdlablalu[$blok];	
			$tcostprdlabknaopgt+=$byytcostprdlabaop[$blok];	
			# === end Total Production Cost (Rp Mn)==>lab ===	
			
			# === Total Production Cost (Rp Mn)==>mat ===	
			$byytcostprdmat=array();
			foreach($rangebln as $bln){	
				$byytcostprdmat[$blok][$bln]=$byytcostprdxdepmat[$blok][$bln];

				$tab.="<td align=right name=tcostprdmat[] class=tcostprd[] ".$style.">".numb_format($byytcostprdmat[$blok][$bln],$nf0)."</td>";
				$tcostprdmatytdty[$blok]+=$byytcostprdmat[$blok][$bln];
				$tcostprdmatttldiv[$div][$bln]+=$byytcostprdmat[$blok][$bln];
				$tcostprdmatttlest[$estate][$bln]+=$byytcostprdmat[$blok][$bln];
				$tcostprdmatgt[$bln]+=$byytcostprdmat[$blok][$bln];
			}	
			$byytcostprdmatlalu=array();
			$byytcostprdmatlalu[$blok]=$byytcostprdxdepmatlalu[$blok];
			$byytcostprdmataop=array();
			$byytcostprdmataop[$blok]=$byytcostprdxdepmataop[$blok];


			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($tcostprdmatytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($byytcostprdmatlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($byytcostprdmataop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdmatknytdtydiv[$div]+=$tcostprdmatytdty[$blok];	
			$tcostprdmatknlaludiv[$div]+=$byytcostprdmatlalu[$blok];	
			$tcostprdmatknaopdiv[$div]+=$byytcostprdmataop[$blok];	
			#ttl estate kanan	
			$tcostprdmatknytdtyest[$estate]+=$tcostprdmatytdty[$blok];	
			$tcostprdmatknlaluest[$estate]+=$byytcostprdmatlalu[$blok];	
			$tcostprdmatknaopest[$estate]+=$byytcostprdmataop[$blok];	
			#grand total	
			$tcostprdmatknytdtygt+=$tcostprdmatytdty[$blok];	
			$tcostprdmatknlalugt+=$byytcostprdmatlalu[$blok];	
			$tcostprdmatknaopgt+=$byytcostprdmataop[$blok];	
			# === end Total Production Cost (Rp Mn)==>mat ===	
			
			# === Total Production Cost (Rp Mn)==>trans ===	
			$byytcostprdtrans=array();
			foreach($rangebln as $bln){	
				$byytcostprdtrans[$blok][$bln]=$byytcostprdxdeptrans[$blok][$bln];

				$tab.="<td align=right name=tcostprdtrans[] class=tcostprd[] ".$style.">".numb_format($byytcostprdtrans[$blok][$bln],$nf0)."</td>";
				$tcostprdtransytdty[$blok]+=$byytcostprdtrans[$blok][$bln];
				$tcostprdtransttldiv[$div][$bln]+=$byytcostprdtrans[$blok][$bln];
				$tcostprdtransttlest[$estate][$bln]+=$byytcostprdtrans[$blok][$bln];
				$tcostprdtransgt[$bln]+=$byytcostprdtrans[$blok][$bln];
			}	
			$byytcostprdtranslalu=array();
			$byytcostprdtranslalu[$blok]=$byytcostprdxdeptranslalu[$blok];
			$byytcostprdtransaop=array();
			$byytcostprdtransaop[$blok]=$byytcostprdxdeptransaop[$blok];


			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($tcostprdtransytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($byytcostprdtranslalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($byytcostprdtransaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdtransknytdtydiv[$div]+=$tcostprdtransytdty[$blok];	
			$tcostprdtransknlaludiv[$div]+=$byytcostprdtranslalu[$blok];	
			$tcostprdtransknaopdiv[$div]+=$byytcostprdtransaop[$blok];	
			#ttl estate kanan	
			$tcostprdtransknytdtyest[$estate]+=$tcostprdtransytdty[$blok];	
			$tcostprdtransknlaluest[$estate]+=$byytcostprdtranslalu[$blok];	
			$tcostprdtransknaopest[$estate]+=$byytcostprdtransaop[$blok];	
			#grand total	
			$tcostprdtransknytdtygt+=$tcostprdtransytdty[$blok];	
			$tcostprdtransknlalugt+=$byytcostprdtranslalu[$blok];	
			$tcostprdtransknaopgt+=$byytcostprdtransaop[$blok];	
			# === end Total Production Cost (Rp Mn)==>trans ===	
			# === Total Production Cost (Rp Mn)==>oth ===	
			$byytcostprdoth[$blok][$bln]=array();
			foreach($rangebln as $bln){	
				$byytcostprdoth[$blok][$bln]=$byytcostprdxdepoth[$blok][$bln]+$byydepttl[$blok][$bln];

				$tab.="<td align=right name=tcostprdoth[] class=tcostprd[] ".$style.">".numb_format($byytcostprdoth[$blok][$bln],$nf0)."</td>";
				$tcostprdothytdty[$blok]+=$byytcostprdoth[$blok][$bln];
				$tcostprdothttldiv[$div][$bln]+=$byytcostprdoth[$blok][$bln];
				$tcostprdothttlest[$estate][$bln]+=$byytcostprdoth[$blok][$bln];
				$tcostprdothgt[$bln]+=$byytcostprdoth[$blok][$bln];
			}	
			$byytcostprdothlalu=array();
			$byytcostprdothlalu[$blok]=$byytcostprdxdepothlalu[$blok]+$byydepttllalu[$blok];
			$byytcostprdothaop=array();
			$byytcostprdothaop[$blok]=$byytcostprdxdepothaop[$blok]+$byydepttlaop[$blok];


			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($tcostprdothytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($byytcostprdothlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprd[] class=tcostprd[] ".$style.">".numb_format($byytcostprdothaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdothknytdtydiv[$div]+=$tcostprdothytdty[$blok];	
			$tcostprdothknlaludiv[$div]+=$byytcostprdothlalu[$blok];	
			$tcostprdothknaopdiv[$div]+=$byytcostprdothaop[$blok];	
			#ttl estate kanan	
			$tcostprdothknytdtyest[$estate]+=$tcostprdothytdty[$blok];	
			$tcostprdothknlaluest[$estate]+=$byytcostprdothlalu[$blok];	
			$tcostprdothknaopest[$estate]+=$byytcostprdothaop[$blok];	
			#grand total	
			$tcostprdothknytdtygt+=$tcostprdothytdty[$blok];	
			$tcostprdothknlalugt+=$byytcostprdothlalu[$blok];	
			$tcostprdothknaopgt+=$byytcostprdothaop[$blok];	
			# === end Total Production Cost (Rp Mn)==>oth ===	
			
			# === Total Production Cost (Rp Mn)==>ttl ===
			$byytcostprdttl=$byytcostprdttllalu=$byytcostprdttlaop=array();			
			foreach($rangebln as $bln){	
				$byytcostprdttl[$blok][$bln]=$byytcostprdlab[$blok][$bln]+$byytcostprdmat[$blok][$bln]+$byytcostprdtrans[$blok][$bln]+$byytcostprdoth[$blok][$bln];


				$tab.="<td align=right name=tcostprdttl[] class=tcostprd[] ".$style.">".numb_format($byytcostprdttl[$blok][$bln],$nf0)."</td>";
				$tcostprdttlytdty[$blok]+=$byytcostprdttl[$blok][$bln];
				$tcostprdttlttldiv[$div][$bln]+=$byytcostprdttl[$blok][$bln];
				$tcostprdttlttlest[$estate][$bln]+=$byytcostprdttl[$blok][$bln];
				$tcostprdttlgt[$bln]+=$byytcostprdttl[$blok][$bln];
			}	
			$byytcostprdttllalu[$blok]=$byytcostprdlablalu[$blok]+$byytcostprdmatlalu[$blok]+$byytcostprdtranslalu[$blok]+$byytcostprdothlalu[$blok];
			$byytcostprdttlaop[$blok]=$byytcostprdlabaop[$blok]+$byytcostprdmataop[$blok]+$byytcostprdtransaop[$blok]+$byytcostprdothaop[$blok];
			$tab.="<td align=right >".numb_format($tcostprdttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytcostprdttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytcostprdttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdttlknytdtydiv[$div]+=$tcostprdttlytdty[$blok];	
			$tcostprdttlknlaludiv[$div]+=$byytcostprdttllalu[$blok];	
			$tcostprdttlknaopdiv[$div]+=$byytcostprdttlaop[$blok];	
			#ttl estate kanan	
			$tcostprdttlknytdtyest[$estate]+=$tcostprdttlytdty[$blok];	
			$tcostprdttlknlaluest[$estate]+=$byytcostprdttllalu[$blok];	
			$tcostprdttlknaopest[$estate]+=$byytcostprdttlaop[$blok];	
			#grand total	
			$tcostprdttlknytdtygt+=$tcostprdttlytdty[$blok];	
			$tcostprdttlknlalugt+=$byytcostprdttllalu[$blok];	
			$tcostprdttlknaopgt+=$byytcostprdttlaop[$blok];	
			# === end Total Production Cost (Rp Mn)==>ttl ===	

			# === Total Revenue (Rp Mn)==>ttl ===	
			$byyrevttl=$byyrevttlaop=$byyrevttllalu=array();
			foreach($rangebln as $bln){	
				$byyrevttl[$blok][$bln] = $hargatbs[$estate][$bln]*$prdton[$blok][$bln];
				$tab.="<td align=right name=rev[] ".$style.">".numb_format($byyrevttl[$blok][$bln],$nf0)."</td>";
				$revttlytdty[$blok]+=$byyrevttl[$blok][$bln];
				$revttlttldiv[$div][$bln]+=$byyrevttl[$blok][$bln];
				$revttlttlest[$estate][$bln]+=$byyrevttl[$blok][$bln];
				$revttlgt[$bln]+=$byyrevttl[$blok][$bln];
			}	
			
			$byyrevttllalu[$blok]=$hargatbslalu[$estate]*$prdtonlalu[$blok];
			$byyrevttlaop[$blok]=$hargabgt[$estate]*$prdtonaop[$blok];
			
			$tab.="<td align=right >".numb_format($revttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byyrevttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byyrevttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$revttlknytdtydiv[$div]+=$revttlytdty[$blok];	
			$revttlknlaludiv[$div]+=$byyrevttllalu[$blok];	
			$revttlknaopdiv[$div]+=$byyrevttlaop[$blok];	
			#ttl estate kanan	
			$revttlknytdtyest[$estate]+=$revttlytdty[$blok];	
			$revttlknlaluest[$estate]+=$byyrevttllalu[$blok];	
			$revttlknaopest[$estate]+=$byyrevttlaop[$blok];	
			#grand total	
			$revttlknytdtygt+=$revttlytdty[$blok];	
			$revttlknlalugt+=$byyrevttllalu[$blok];	
			$revttlknaopgt+=$byyrevttlaop[$blok];	
			# === end Total Revenue (Rp Mn)==>ttl ===	
		
			# === Total Cash Profit (Rp Mn)==>ttl ===	
			$byycashprofitttl=$byycashprofitttllalu=$byycashprofitttlaop=array();
			foreach($rangebln as $bln){	
				$byycashprofitttl[$blok][$bln]=$byyrevttl[$blok][$bln]-$byytcostprdxdepttl[$blok][$bln];
			
				$tab.="<td align=right name=cashprofit[] ".$style.">".numb_format($byycashprofitttl[$blok][$bln],$nf0)."</td>";
				$cashprofitttlytdty[$blok]+=$byycashprofitttl[$blok][$bln];
				$cashprofitttlttldiv[$div][$bln]+=$byycashprofitttl[$blok][$bln];
				$cashprofitttlttlest[$estate][$bln]+=$byycashprofitttl[$blok][$bln];
				$cashprofitttlgt[$bln]+=$byycashprofitttl[$blok][$bln];
			}	
			
			$byycashprofitttllalu[$blok]=$byyrevttllalu[$blok]-$byytcostprdxdepttllalu[$blok];
			$byycashprofitttlaop[$blok]=$byyrevttlaop[$blok]-$byytcostprdxdepttlaop[$blok];
			
			$n="";
			if($cashprofitttlytdty[$blok]>0){
				$n="style=background-color:green;";
			}elseif($cashprofitttlytdty[$blok]<0){
				$n="style=background-color:red;";
			}
			$tab.="<td align=right ".$n.">".numb_format($cashprofitttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byycashprofitttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byycashprofitttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$cashprofitttlknytdtydiv[$div]+=$cashprofitttlytdty[$blok];	
			$cashprofitttlknlaludiv[$div]+=$byycashprofitttllalu[$blok];	
			$cashprofitttlknaopdiv[$div]+=$byycashprofitttlaop[$blok];	
			#ttl estate kanan	
			$cashprofitttlknytdtyest[$estate]+=$cashprofitttlytdty[$blok];	
			$cashprofitttlknlaluest[$estate]+=$byycashprofitttllalu[$blok];	
			$cashprofitttlknaopest[$estate]+=$byycashprofitttlaop[$blok];	
			#grand total	
			$cashprofitttlknytdtygt+=$cashprofitttlytdty[$blok];	
			$cashprofitttlknlalugt+=$byycashprofitttllalu[$blok];	
			$cashprofitttlknaopgt+=$byycashprofitttlaop[$blok];	
			# === end Total Cash Profit (Rp Mn)==>ttl ===	
	
			# === Total Gross Profit (Rp Mn)==>ttl ===	
			$byygrossprofitttl=$byygrossprofitttllalu=$byygrossprofitttlaop=array();
			foreach($rangebln as $bln){	
				$byygrossprofitttl[$blok][$bln]=$byyrevttl[$blok][$bln]-$byytcostprdttl[$blok][$bln];
			
				$tab.="<td align=right name=grossprofit[] ".$style.">".numb_format($byygrossprofitttl[$blok][$bln],$nf0)."</td>";
				$grossprofitttlytdty[$blok]+=$byygrossprofitttl[$blok][$bln];
				$grossprofitttlttldiv[$div][$bln]+=$byygrossprofitttl[$blok][$bln];
				$grossprofitttlttlest[$estate][$bln]+=$byygrossprofitttl[$blok][$bln];
				$grossprofitttlgt[$bln]+=$byygrossprofitttl[$blok][$bln];
			}	
			$byygrossprofitttllalu[$blok]=$byyrevttllalu[$blok]-$byytcostprdttllalu[$blok];
			$byygrossprofitttlaop[$blok]=$byyrevttlaop[$blok]-$byytcostprdttlaop[$blok];
			
			$n="";
			if($grossprofitttlytdty[$blok]>0){
				$n="style=background-color:green;";
				$greenp+=1;
				$nobrsgreenp.="row_".$no."##";
				
			}elseif($grossprofitttlytdty[$blok]<=0){
				$n="style=background-color:red;";
				$redp+=1;
				$nobrsredp.="row_".$no."##";
			}
			$nobrttlp.="row_".$no."##";
			
			$tab.="<td align=right ".$n.">".numb_format($grossprofitttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byygrossprofitttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byygrossprofitttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$grossprofitttlknytdtydiv[$div]+=$grossprofitttlytdty[$blok];	
			$grossprofitttlknlaludiv[$div]+=$byygrossprofitttllalu[$blok];	
			$grossprofitttlknaopdiv[$div]+=$byygrossprofitttlaop[$blok];	
			#ttl estate kanan	
			$grossprofitttlknytdtyest[$estate]+=$grossprofitttlytdty[$blok];	
			$grossprofitttlknlaluest[$estate]+=$byygrossprofitttllalu[$blok];	
			$grossprofitttlknaopest[$estate]+=$byygrossprofitttlaop[$blok];	
			#grand total	
			$grossprofitttlknytdtygt+=$grossprofitttlytdty[$blok];	
			$grossprofitttlknlalugt+=$byygrossprofitttllalu[$blok];	
			$grossprofitttlknaopgt+=$byygrossprofitttlaop[$blok];	
			# === end Total Gross Profit (Rp Mn)==>ttl ===	
	
				
			# === Total Production Cost Excl Depre/Kg (Rp)==>lab ===	
			$byytcostprdxdepperkglab=$tcostprdxdepperkglabytdty=$byytcostprdxdepperkglablalu=$byytcostprdxdepperkglabaop=$tcostprdxdepperkglabttldiv=$tcostprdxdepperkglabttlest=$tcostprdxdepperkglabgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byytcostprdxdepperkglab[$blok][$bln]=$byytcostprdxdeplab[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=tcostprdxdepperkglab[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkglab[$blok][$bln],$nf0)."</td>";
				
				if($prdttldiv[$div][$bln]!=0){$tcostprdxdepperkglabttldiv[$div][$bln]=$tcostprdxdeplabttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$tcostprdxdepperkglabttlest[$estate][$bln]=$tcostprdxdeplabttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$tcostprdxdepperkglabgt[$bln]=$tcostprdxdeplabgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$tcostprdxdepperkglabytdty[$blok]=$tcostprdxdeplabytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byytcostprdxdepperkglablalu[$blok]=$byytcostprdxdeplablalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byytcostprdxdepperkglabaop[$blok]=$byytcostprdxdeplabaop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkglabytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkglablalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkglabaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdxdepperkglabknytdtydiv=$tcostprdxdepperkglabknlaludiv=$tcostprdxdepperkglabknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$tcostprdxdepperkglabknytdtydiv[$div]=$tcostprdxdeplabknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$tcostprdxdepperkglabknlaludiv[$div]=$tcostprdxdeplabknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$tcostprdxdepperkglabknaopdiv[$div]=$tcostprdxdeplabknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$tcostprdxdepperkglabknytdtyest=$tcostprdxdepperkglabknlaluest=$tcostprdxdepperkglabknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$tcostprdxdepperkglabknytdtyest[$estate]=$tcostprdxdeplabknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$tcostprdxdepperkglabknlaluest[$estate]=$tcostprdxdeplabknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$tcostprdxdepperkglabknaopest[$estate]=$tcostprdxdeplabknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$tcostprdxdepperkglabknytdtygt=$tcostprdxdepperkglabknlalugt=$tcostprdxdepperkglabknaopgt=0;	
			if($prdknytdtygt!=0){$tcostprdxdepperkglabknytdtygt=$tcostprdxdeplabknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$tcostprdxdepperkglabknlalugt=$tcostprdxdeplabknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$tcostprdxdepperkglabknaopgt=$tcostprdxdeplabknaopgt/$prdknaopgt;};	
			# === end Total Production Cost Excl Depre/Kg (Rp)==>lab ===	
			# === Total Production Cost Excl Depre/Kg (Rp)==>mat ===	
			$byytcostprdxdepperkgmat=$tcostprdxdepperkgmatytdty=$byytcostprdxdepperkgmatlalu=$byytcostprdxdepperkgmataop=$tcostprdxdepperkgmatttldiv=$tcostprdxdepperkgmatttlest=$tcostprdxdepperkgmatgt=array();	
			foreach($rangebln as $bln){	
				if($prdton[$blok][$bln]!=0){$byytcostprdxdepperkgmat[$blok][$bln]=$byytcostprdxdepmat[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=tcostprdxdepperkgmat[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkgmat[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$tcostprdxdepperkgmatttldiv[$div][$bln]=$tcostprdxdepmatttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$tcostprdxdepperkgmatttlest[$estate][$bln]=$tcostprdxdepmatttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$tcostprdxdepperkgmatgt[$bln]=$tcostprdxdepmatgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$tcostprdxdepperkgmatytdty[$blok]=$tcostprdxdepmatytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byytcostprdxdepperkgmatlalu[$blok]=$byytcostprdxdepmatlalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byytcostprdxdepperkgmataop[$blok]=$byytcostprdxdepmataop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgmatytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkgmatlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkgmataop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdxdepperkgmatknytdtydiv=$tcostprdxdepperkgmatknlaludiv=$tcostprdxdepperkgmatknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$tcostprdxdepperkgmatknytdtydiv[$div]=$tcostprdxdepmatknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$tcostprdxdepperkgmatknlaludiv[$div]=$tcostprdxdepmatknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$tcostprdxdepperkgmatknaopdiv[$div]=$tcostprdxdepmatknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$tcostprdxdepperkgmatknytdtyest=$tcostprdxdepperkgmatknlaluest=$tcostprdxdepperkgmatknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$tcostprdxdepperkgmatknytdtyest[$estate]=$tcostprdxdepmatknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$tcostprdxdepperkgmatknlaluest[$estate]=$tcostprdxdepmatknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$tcostprdxdepperkgmatknaopest[$estate]=$tcostprdxdepmatknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$tcostprdxdepperkgmatknytdtygt=$tcostprdxdepperkgmatknlalugt=$tcostprdxdepperkgmatknaopgt=0;	
			if($prdknytdtygt!=0){$tcostprdxdepperkgmatknytdtygt=$tcostprdxdepmatknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$tcostprdxdepperkgmatknlalugt=$tcostprdxdepmatknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$tcostprdxdepperkgmatknaopgt=$tcostprdxdepmatknaopgt/$prdknaopgt;};	
			# === end Total Production Cost Excl Depre/Kg (Rp)==>mat ===	
			# === Total Production Cost Excl Depre/Kg (Rp)==>trans ===	
			$byytcostprdxdepperkgtrans=$tcostprdxdepperkgtransytdty=$byytcostprdxdepperkgtranslalu=$byytcostprdxdepperkgtransaop=$tcostprdxdepperkgtransttldiv=$tcostprdxdepperkgtransttlest=$tcostprdxdepperkgtransgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byytcostprdxdepperkgtrans[$blok][$bln]=$byytcostprdxdeptrans[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=tcostprdxdepperkgtrans[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkgtrans[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$tcostprdxdepperkgtransttldiv[$div][$bln]=$tcostprdxdeptransttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$tcostprdxdepperkgtransttlest[$estate][$bln]=$tcostprdxdeptransttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$tcostprdxdepperkgtransgt[$bln]=$tcostprdxdeptransgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$tcostprdxdepperkgtransytdty[$blok]=$tcostprdxdeptransytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byytcostprdxdepperkgtranslalu[$blok]=$byytcostprdxdeptranslalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byytcostprdxdepperkgtransaop[$blok]=$byytcostprdxdeptransaop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgtransytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkgtranslalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkgtransaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdxdepperkgtransknytdtydiv=$tcostprdxdepperkgtransknlaludiv=$tcostprdxdepperkgtransknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$tcostprdxdepperkgtransknytdtydiv[$div]=$tcostprdxdeptransknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$tcostprdxdepperkgtransknlaludiv[$div]=$tcostprdxdeptransknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$tcostprdxdepperkgtransknaopdiv[$div]=$tcostprdxdeptransknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$tcostprdxdepperkgtransknytdtyest=$tcostprdxdepperkgtransknlaluest=$tcostprdxdepperkgtransknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$tcostprdxdepperkgtransknytdtyest[$estate]=$tcostprdxdeptransknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$tcostprdxdepperkgtransknlaluest[$estate]=$tcostprdxdeptransknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$tcostprdxdepperkgtransknaopest[$estate]=$tcostprdxdeptransknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$tcostprdxdepperkgtransknytdtygt=$tcostprdxdepperkgtransknlalugt=$tcostprdxdepperkgtransknaopgt=0;	
			if($prdknytdtygt!=0){$tcostprdxdepperkgtransknytdtygt=$tcostprdxdeptransknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$tcostprdxdepperkgtransknlalugt=$tcostprdxdeptransknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$tcostprdxdepperkgtransknaopgt=$tcostprdxdeptransknaopgt/$prdknaopgt;};	
			# === end Total Production Cost Excl Depre/Kg (Rp)==>trans ===	
			# === Total Production Cost Excl Depre/Kg (Rp)==>oth ===	
			$byytcostprdxdepperkgoth=$tcostprdxdepperkgothytdty=$byytcostprdxdepperkgothlalu=$byytcostprdxdepperkgothaop=$tcostprdxdepperkgothttldiv=$tcostprdxdepperkgothttlest=$tcostprdxdepperkgothgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byytcostprdxdepperkgoth[$blok][$bln]=$byytcostprdxdepoth[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=tcostprdxdepperkgoth[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkgoth[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$tcostprdxdepperkgothttldiv[$div][$bln]=$tcostprdxdepothttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$tcostprdxdepperkgothttlest[$estate][$bln]=$tcostprdxdepothttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$tcostprdxdepperkgothgt[$bln]=$tcostprdxdepothgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$tcostprdxdepperkgothytdty[$blok]=$tcostprdxdepothytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byytcostprdxdepperkgothlalu[$blok]=$byytcostprdxdepothlalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byytcostprdxdepperkgothaop[$blok]=$byytcostprdxdepothaop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgothytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkgothlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkgothaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdxdepperkgothknytdtydiv=$tcostprdxdepperkgothknlaludiv=$tcostprdxdepperkgothknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$tcostprdxdepperkgothknytdtydiv[$div]=$tcostprdxdepothknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$tcostprdxdepperkgothknlaludiv[$div]=$tcostprdxdepothknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$tcostprdxdepperkgothknaopdiv[$div]=$tcostprdxdepothknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$tcostprdxdepperkgothknytdtyest=$tcostprdxdepperkgothknlaluest=$tcostprdxdepperkgothknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$tcostprdxdepperkgothknytdtyest[$estate]=$tcostprdxdepothknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$tcostprdxdepperkgothknlaluest[$estate]=$tcostprdxdepothknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$tcostprdxdepperkgothknaopest[$estate]=$tcostprdxdepothknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$tcostprdxdepperkgothknytdtygt=$tcostprdxdepperkgothknlalugt=$tcostprdxdepperkgothknaopgt=0;	
			if($prdknytdtygt!=0){$tcostprdxdepperkgothknytdtygt=$tcostprdxdepothknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$tcostprdxdepperkgothknlalugt=$tcostprdxdepothknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$tcostprdxdepperkgothknaopgt=$tcostprdxdepothknaopgt/$prdknaopgt;};	
			# === end Total Production Cost Excl Depre/Kg (Rp)==>oth ===	
			# === Total Production Cost Excl Depre/Kg (Rp)==>ttl ===	
			$byytcostprdxdepperkgttl=$tcostprdxdepperkgttlytdty=$byytcostprdxdepperkgttllalu=$byytcostprdxdepperkgttlaop=$tcostprdxdepperkgttlttldiv=$tcostprdxdepperkgttlttlest=$tcostprdxdepperkgttlgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byytcostprdxdepperkgttl[$blok][$bln]=$byytcostprdxdepttl[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=tcostprdxdepperkgttl[] class=tcostprdxdepperkg[] ".$style.">".numb_format($byytcostprdxdepperkgttl[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$tcostprdxdepperkgttlttldiv[$div][$bln]=$tcostprdxdepttlttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$tcostprdxdepperkgttlttlest[$estate][$bln]=$tcostprdxdepttlttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$tcostprdxdepperkgttlgt[$bln]=$tcostprdxdepttlgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$tcostprdxdepperkgttlytdty[$blok]=$tcostprdxdepttlytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byytcostprdxdepperkgttllalu[$blok]=$byytcostprdxdepttllalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byytcostprdxdepperkgttlaop[$blok]=$byytcostprdxdepttlaop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right >".numb_format($tcostprdxdepperkgttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytcostprdxdepperkgttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytcostprdxdepperkgttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdxdepperkgttlknytdtydiv=$tcostprdxdepperkgttlknlaludiv=$tcostprdxdepperkgttlknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$tcostprdxdepperkgttlknytdtydiv[$div]=$tcostprdxdepttlknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$tcostprdxdepperkgttlknlaludiv[$div]=$tcostprdxdepttlknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$tcostprdxdepperkgttlknaopdiv[$div]=$tcostprdxdepttlknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$tcostprdxdepperkgttlknytdtyest=$tcostprdxdepperkgttlknlaluest=$tcostprdxdepperkgttlknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$tcostprdxdepperkgttlknytdtyest[$estate]=$tcostprdxdepttlknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$tcostprdxdepperkgttlknlaluest[$estate]=$tcostprdxdepttlknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$tcostprdxdepperkgttlknaopest[$estate]=$tcostprdxdepttlknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$tcostprdxdepperkgttlknytdtygt=$tcostprdxdepperkgttlknlalugt=$tcostprdxdepperkgttlknaopgt=0;	
			if($prdknytdtygt!=0){$tcostprdxdepperkgttlknytdtygt=$tcostprdxdepttlknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$tcostprdxdepperkgttlknlalugt=$tcostprdxdepttlknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$tcostprdxdepperkgttlknaopgt=$tcostprdxdepttlknaopgt/$prdknaopgt;};	
			# === end Total Production Cost Excl Depre/Kg (Rp)==>ttl ===	
	
			# === Total Production Cost/Kg (Rp)==>lab ===	
			$byytcostprdperkglab=$tcostprdperkglabytdty=$byytcostprdperkglablalu=$byytcostprdperkglabaop=$tcostprdperkglabttldiv=$tcostprdperkglabttlest=$tcostprdperkglabgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byytcostprdperkglab[$blok][$bln]=$byytcostprdlab[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=tcostprdperkglab[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkglab[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$tcostprdperkglabttldiv[$div][$bln]=$tcostprdlabttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$tcostprdperkglabttlest[$estate][$bln]=$tcostprdlabttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$tcostprdperkglabgt[$bln]=$tcostprdlabgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$tcostprdperkglabytdty[$blok]=$tcostprdlabytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byytcostprdperkglablalu[$blok]=$byytcostprdlablalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byytcostprdperkglabaop[$blok]=$byytcostprdlabaop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkglabytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkglablalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkglabaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdperkglabknytdtydiv=$tcostprdperkglabknlaludiv=$tcostprdperkglabknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$tcostprdperkglabknytdtydiv[$div]=$tcostprdlabknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$tcostprdperkglabknlaludiv[$div]=$tcostprdlabknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$tcostprdperkglabknaopdiv[$div]=$tcostprdlabknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$tcostprdperkglabknytdtyest=$tcostprdperkglabknlaluest=$tcostprdperkglabknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$tcostprdperkglabknytdtyest[$estate]=$tcostprdlabknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$tcostprdperkglabknlaluest[$estate]=$tcostprdlabknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$tcostprdperkglabknaopest[$estate]=$tcostprdlabknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$tcostprdperkglabknytdtygt=$tcostprdperkglabknlalugt=$tcostprdperkglabknaopgt=0;	
			if($prdknytdtygt!=0){$tcostprdperkglabknytdtygt=$tcostprdlabknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$tcostprdperkglabknlalugt=$tcostprdlabknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$tcostprdperkglabknaopgt=$tcostprdlabknaopgt/$prdknaopgt;};	
			# === end Total Production Cost/Kg (Rp)==>lab ===	
			# === Total Production Cost/Kg (Rp)==>mat ===	
			$byytcostprdperkgmat=$tcostprdperkgmatytdty=$byytcostprdperkgmatlalu=$byytcostprdperkgmataop=$tcostprdperkgmatttldiv=$tcostprdperkgmatttlest=$tcostprdperkgmatgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byytcostprdperkgmat[$blok][$bln]=$byytcostprdmat[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=tcostprdperkgmat[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkgmat[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$tcostprdperkgmatttldiv[$div][$bln]=$tcostprdmatttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$tcostprdperkgmatttlest[$estate][$bln]=$tcostprdmatttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$tcostprdperkgmatgt[$bln]=$tcostprdmatgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$tcostprdperkgmatytdty[$blok]=$tcostprdmatytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byytcostprdperkgmatlalu[$blok]=$byytcostprdmatlalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byytcostprdperkgmataop[$blok]=$byytcostprdmataop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgmatytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkgmatlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkgmataop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdperkgmatknytdtydiv=$tcostprdperkgmatknlaludiv=$tcostprdperkgmatknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$tcostprdperkgmatknytdtydiv[$div]=$tcostprdmatknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$tcostprdperkgmatknlaludiv[$div]=$tcostprdmatknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$tcostprdperkgmatknaopdiv[$div]=$tcostprdmatknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$tcostprdperkgmatknytdtyest=$tcostprdperkgmatknlaluest=$tcostprdperkgmatknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$tcostprdperkgmatknytdtyest[$estate]=$tcostprdmatknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$tcostprdperkgmatknlaluest[$estate]=$tcostprdmatknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$tcostprdperkgmatknaopest[$estate]=$tcostprdmatknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$tcostprdperkgmatknytdtygt=$tcostprdperkgmatknlalugt=$tcostprdperkgmatknaopgt=0;	
			if($prdknytdtygt!=0){$tcostprdperkgmatknytdtygt=$tcostprdmatknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$tcostprdperkgmatknlalugt=$tcostprdmatknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$tcostprdperkgmatknaopgt=$tcostprdmatknaopgt/$prdknaopgt;};	
			# === end Total Production Cost/Kg (Rp)==>mat ===	
			# === Total Production Cost/Kg (Rp)==>trans ===	
			$byytcostprdperkgtrans=$tcostprdperkgtransytdty=$byytcostprdperkgtranslalu=$byytcostprdperkgtransaop=$tcostprdperkgtransttldiv=$tcostprdperkgtransttlest=$tcostprdperkgtransgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byytcostprdperkgtrans[$blok][$bln]=$byytcostprdtrans[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=tcostprdperkgtrans[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkgtrans[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$tcostprdperkgtransttldiv[$div][$bln]=$tcostprdtransttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$tcostprdperkgtransttlest[$estate][$bln]=$tcostprdtransttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$tcostprdperkgtransgt[$bln]=$tcostprdtransgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$tcostprdperkgtransytdty[$blok]=$tcostprdtransytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byytcostprdperkgtranslalu[$blok]=$byytcostprdtranslalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byytcostprdperkgtransaop[$blok]=$byytcostprdtransaop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgtransytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkgtranslalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkgtransaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdperkgtransknytdtydiv=$tcostprdperkgtransknlaludiv=$tcostprdperkgtransknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$tcostprdperkgtransknytdtydiv[$div]=$tcostprdtransknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$tcostprdperkgtransknlaludiv[$div]=$tcostprdtransknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$tcostprdperkgtransknaopdiv[$div]=$tcostprdtransknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$tcostprdperkgtransknytdtyest=$tcostprdperkgtransknlaluest=$tcostprdperkgtransknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$tcostprdperkgtransknytdtyest[$estate]=$tcostprdtransknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$tcostprdperkgtransknlaluest[$estate]=$tcostprdtransknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$tcostprdperkgtransknaopest[$estate]=$tcostprdtransknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$tcostprdperkgtransknytdtygt=$tcostprdperkgtransknlalugt=$tcostprdperkgtransknaopgt=0;	
			if($prdknytdtygt!=0){$tcostprdperkgtransknytdtygt=$tcostprdtransknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$tcostprdperkgtransknlalugt=$tcostprdtransknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$tcostprdperkgtransknaopgt=$tcostprdtransknaopgt/$prdknaopgt;};	
			# === end Total Production Cost/Kg (Rp)==>trans ===	
			# === Total Production Cost/Kg (Rp)==>oth ===	
			$byytcostprdperkgoth=$tcostprdperkgothytdty=$byytcostprdperkgothlalu=$byytcostprdperkgothaop=$tcostprdperkgothttldiv=$tcostprdperkgothttlest=$tcostprdperkgothgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byytcostprdperkgoth[$blok][$bln]=$byytcostprdoth[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=tcostprdperkgoth[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkgoth[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$tcostprdperkgothttldiv[$div][$bln]=$tcostprdothttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$tcostprdperkgothttlest[$estate][$bln]=$tcostprdothttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$tcostprdperkgothgt[$bln]=$tcostprdothgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$tcostprdperkgothytdty[$blok]=$tcostprdothytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byytcostprdperkgothlalu[$blok]=$byytcostprdothlalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byytcostprdperkgothaop[$blok]=$byytcostprdothaop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgothytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkgothlalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkgothaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdperkgothknytdtydiv=$tcostprdperkgothknlaludiv=$tcostprdperkgothknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$tcostprdperkgothknytdtydiv[$div]=$tcostprdothknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$tcostprdperkgothknlaludiv[$div]=$tcostprdothknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$tcostprdperkgothknaopdiv[$div]=$tcostprdothknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$tcostprdperkgothknytdtyest=$tcostprdperkgothknlaluest=$tcostprdperkgothknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$tcostprdperkgothknytdtyest[$estate]=$tcostprdothknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$tcostprdperkgothknlaluest[$estate]=$tcostprdothknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$tcostprdperkgothknaopest[$estate]=$tcostprdothknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$tcostprdperkgothknytdtygt=$tcostprdperkgothknlalugt=$tcostprdperkgothknaopgt=0;	
			if($prdknytdtygt!=0){$tcostprdperkgothknytdtygt=$tcostprdothknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$tcostprdperkgothknlalugt=$tcostprdothknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$tcostprdperkgothknaopgt=$tcostprdothknaopgt/$prdknaopgt;};	
			# === end Total Production Cost/Kg (Rp)==>oth ===	
			# === Total Production Cost/Kg (Rp)==>ttl ===	
			$byytcostprdperkgttl=$tcostprdperkgttlytdty=$byytcostprdperkgttllalu=$byytcostprdperkgttlaop=$tcostprdperkgttlttldiv=$tcostprdperkgttlttlest=$tcostprdperkgttlgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byytcostprdperkgttl[$blok][$bln]=$byytcostprdttl[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=tcostprdperkgttl[] class=tcostprdperkg[] ".$style.">".numb_format($byytcostprdperkgttl[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$tcostprdperkgttlttldiv[$div][$bln]=$tcostprdttlttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$tcostprdperkgttlttlest[$estate][$bln]=$tcostprdttlttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$tcostprdperkgttlgt[$bln]=$tcostprdttlgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$tcostprdperkgttlytdty[$blok]=$tcostprdttlytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byytcostprdperkgttllalu[$blok]=$byytcostprdttllalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byytcostprdperkgttlaop[$blok]=$byytcostprdttlaop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right >".numb_format($tcostprdperkgttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytcostprdperkgttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byytcostprdperkgttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$tcostprdperkgttlknytdtydiv=$tcostprdperkgttlknlaludiv=$tcostprdperkgttlknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$tcostprdperkgttlknytdtydiv[$div]=$tcostprdttlknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$tcostprdperkgttlknlaludiv[$div]=$tcostprdttlknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$tcostprdperkgttlknaopdiv[$div]=$tcostprdttlknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$tcostprdperkgttlknytdtyest=$tcostprdperkgttlknlaluest=$tcostprdperkgttlknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$tcostprdperkgttlknytdtyest[$estate]=$tcostprdttlknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$tcostprdperkgttlknlaluest[$estate]=$tcostprdttlknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$tcostprdperkgttlknaopest[$estate]=$tcostprdttlknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$tcostprdperkgttlknytdtygt=$tcostprdperkgttlknlalugt=$tcostprdperkgttlknaopgt=0;	
			if($prdknytdtygt!=0){$tcostprdperkgttlknytdtygt=$tcostprdttlknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$tcostprdperkgttlknlalugt=$tcostprdttlknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$tcostprdperkgttlknaopgt=$tcostprdttlknaopgt/$prdknaopgt;};	
			# === end Total Production Cost/Kg (Rp)==>ttl ===	
	
			# === Revenue/Kg (Rp)==>ttl ===	
			$byyrevperkgttl=$revperkgttlytdty=$byyrevperkgttllalu=$byyrevperkgttlaop=$revperkgttlttldiv=$revperkgttlttlest=$revperkgttlgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byyrevperkgttl[$blok][$bln]=$byyrevttl[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=revperkg[] ".$style.">".numb_format($byyrevperkgttl[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$revperkgttlttldiv[$div][$bln]=$revttlttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$revperkgttlttlest[$estate][$bln]=$revttlttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$revperkgttlgt[$bln]=$revttlgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$revperkgttlytdty[$blok]=$revttlytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byyrevperkgttllalu[$blok]=$byyrevttllalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byyrevperkgttlaop[$blok]=$byyrevttlaop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right >".numb_format($revperkgttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byyrevperkgttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byyrevperkgttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$revperkgttlknytdtydiv=$revperkgttlknlaludiv=$revperkgttlknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$revperkgttlknytdtydiv[$div]=$revttlknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$revperkgttlknlaludiv[$div]=$revttlknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$revperkgttlknaopdiv[$div]=$revttlknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$revperkgttlknytdtyest=$revperkgttlknlaluest=$revperkgttlknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$revperkgttlknytdtyest[$estate]=$revttlknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$revperkgttlknlaluest[$estate]=$revttlknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$revperkgttlknaopest[$estate]=$revttlknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$revperkgttlknytdtygt=$revperkgttlknlalugt=$revperkgttlknaopgt=0;	
			if($prdknytdtygt!=0){$revperkgttlknytdtygt=$revttlknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$revperkgttlknlalugt=$revttlknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$revperkgttlknaopgt=$revttlknaopgt/$prdknaopgt;};	
			# === end Revenue/Kg (Rp)==>ttl ===	
	
			# === Total Cash Profit/Kg (Rp)==>ttl ===	
			$byycashprofitperkgttl=$cashprofitperkgttlytdty=$byycashprofitperkgttllalu=$byycashprofitperkgttlaop=$cashprofitperkgttlttldiv=$cashprofitperkgttlttlest=$cashprofitperkgttlgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byycashprofitperkgttl[$blok][$bln]=$byycashprofitttl[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=cashprofitperkg[] ".$style.">".numb_format($byycashprofitperkgttl[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$cashprofitperkgttlttldiv[$div][$bln]=$cashprofitttlttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$cashprofitperkgttlttlest[$estate][$bln]=$cashprofitttlttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$cashprofitperkgttlgt[$bln]=$cashprofitttlgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$cashprofitperkgttlytdty[$blok]=$cashprofitttlytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byycashprofitperkgttllalu[$blok]=$byycashprofitttllalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byycashprofitperkgttlaop[$blok]=$byycashprofitttlaop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right >".numb_format($cashprofitperkgttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byycashprofitperkgttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byycashprofitperkgttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$cashprofitperkgttlknytdtydiv=$cashprofitperkgttlknlaludiv=$cashprofitperkgttlknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$cashprofitperkgttlknytdtydiv[$div]=$cashprofitttlknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$cashprofitperkgttlknlaludiv[$div]=$cashprofitttlknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$cashprofitperkgttlknaopdiv[$div]=$cashprofitttlknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$cashprofitperkgttlknytdtyest=$cashprofitperkgttlknlaluest=$cashprofitperkgttlknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$cashprofitperkgttlknytdtyest[$estate]=$cashprofitttlknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$cashprofitperkgttlknlaluest[$estate]=$cashprofitttlknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$cashprofitperkgttlknaopest[$estate]=$cashprofitttlknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$cashprofitperkgttlknytdtygt=$cashprofitperkgttlknlalugt=$cashprofitperkgttlknaopgt=0;	
			if($prdknytdtygt!=0){$cashprofitperkgttlknytdtygt=$cashprofitttlknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$cashprofitperkgttlknlalugt=$cashprofitttlknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$cashprofitperkgttlknaopgt=$cashprofitttlknaopgt/$prdknaopgt;};	
			# === end Total Cash Profit/Kg (Rp)==>ttl ===	
			
			# === Gross Profit/Kg (Rp)==>ttl ===	
			$byygrossprofitperkgttl=$grossprofitperkgttlytdty=$byygrossprofitperkgttllalu=$byygrossprofitperkgttlaop=$grossprofitperkgttlttldiv=$grossprofitperkgttlttlest=$grossprofitperkgttlgt=array();	
			foreach($rangebln as $bln){	
			if($prdton[$blok][$bln]!=0){$byygrossprofitperkgttl[$blok][$bln]=$byygrossprofitttl[$blok][$bln]/$prdton[$blok][$bln];};	
				$tab.="<td align=right name=grossprofitperkg[] ".$style.">".numb_format($byygrossprofitperkgttl[$blok][$bln],$nf0)."</td>";
				if($prdttldiv[$div][$bln]!=0){$grossprofitperkgttlttldiv[$div][$bln]=$grossprofitttlttldiv[$div][$bln]/$prdttldiv[$div][$bln];};	
				if($prdttlest[$estate][$bln]!=0){$grossprofitperkgttlttlest[$estate][$bln]=$grossprofitttlttlest[$estate][$bln]/$prdttlest[$estate][$bln];};	
				if($prdgt[$bln]!=0){$grossprofitperkgttlgt[$bln]=$grossprofitttlgt[$bln]/$prdgt[$bln];};	
			}	
			if($prdytdty[$blok]!=0){$grossprofitperkgttlytdty[$blok]=$grossprofitttlytdty[$blok]/$prdytdty[$blok];};	
			if($prdtonlalu[$blok]!=0){$byygrossprofitperkgttllalu[$blok]=$byygrossprofitttllalu[$blok]/$prdtonlalu[$blok];};	
			if($prdtonaop[$blok]!=0){$byygrossprofitperkgttlaop[$blok]=$byygrossprofitttlaop[$blok]/$prdtonaop[$blok];};	
			$tab.="<td align=right >".numb_format($grossprofitperkgttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byygrossprofitperkgttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byygrossprofitperkgttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$grossprofitperkgttlknytdtydiv=$grossprofitperkgttlknlaludiv=$grossprofitperkgttlknaopdiv=array();	
			if($prdknytdtydiv[$div]!=0){$grossprofitperkgttlknytdtydiv[$div]=$grossprofitttlknytdtydiv[$div]/$prdknytdtydiv[$div];};	
			if($prdknlaludiv[$div]!=0){$grossprofitperkgttlknlaludiv[$div]=$grossprofitttlknlaludiv[$div]/$prdknlaludiv[$div];};	
			if($prdknaopdiv[$div]!=0){$grossprofitperkgttlknaopdiv[$div]=$grossprofitttlknaopdiv[$div]/$prdknaopdiv[$div];};	
			#ttl estate kanan	
			$grossprofitperkgttlknytdtyest=$grossprofitperkgttlknlaluest=$grossprofitperkgttlknaopest=array();	
			if($prdknytdtyest[$estate]!=0){$grossprofitperkgttlknytdtyest[$estate]=$grossprofitttlknytdtyest[$estate]/$prdknytdtyest[$estate];};	
			if($prdknlaluest[$estate]!=0){$grossprofitperkgttlknlaluest[$estate]=$grossprofitttlknlaluest[$estate]/$prdknlaluest[$estate];};	
			if($prdknaopest[$estate]!=0){$grossprofitperkgttlknaopest[$estate]=$grossprofitttlknaopest[$estate]/$prdknaopest[$estate];};	
			#grand total	
			$grossprofitperkgttlknytdtygt=$grossprofitperkgttlknlalugt=$grossprofitperkgttlknaopgt=0;	
			if($prdknytdtygt!=0){$grossprofitperkgttlknytdtygt=$grossprofitttlknytdtygt/$prdknytdtygt;};	
			if($prdknlalugt!=0){$grossprofitperkgttlknlalugt=$grossprofitttlknlalugt/$prdknlalugt;};	
			if($prdknaopgt!=0){$grossprofitperkgttlknaopgt=$grossprofitttlknaopgt/$prdknaopgt;};	
			# === end Gross Profit/Kg (Rp)==>ttl ===	
	
			# === Gross Profit %/Kg==>ttl ===	
			$byygrossprofitpersenttl=$grossprofitpersenttlytdty=$byygrossprofitpersenttllalu=$byygrossprofitpersenttlaop=$grossprofitpersenttlttldiv=$grossprofitpersenttlttlest=$grossprofitpersenttlgt=array();	
			foreach($rangebln as $bln){	
				if($byyrevperkgttl[$blok][$bln]!=0){$byygrossprofitpersenttl[$blok][$bln]=$byygrossprofitperkgttl[$blok][$bln]/$byyrevperkgttl[$blok][$bln]*100;};	
				$tab.="<td align=right name=grossprofitpersen[] ".$style.">".numb_format($byygrossprofitpersenttl[$blok][$bln],$nf0)."</td>";
				if($revperkgttlttldiv[$div][$bln]!=0){$grossprofitpersenttlttldiv[$div][$bln]=$grossprofitperkgttlttldiv[$div][$bln]/$revperkgttlttldiv[$div][$bln]*100;};
				if($revperkgttlttlest[$estate][$bln]!=0){$grossprofitpersenttlttlest[$estate][$bln]=$grossprofitperkgttlttlest[$estate][$bln]/$revperkgttlttlest[$estate][$bln]*100;};
				if($revperkgttlgt[$bln]!=0){$grossprofitpersenttlgt[$bln]=$grossprofitperkgttlgt[$bln]/$revperkgttlgt[$bln]*100;};
			}	
			if($revperkgttlytdty[$blok]!=0){$grossprofitpersenttlytdty[$blok]=$grossprofitperkgttlytdty[$blok]/$revperkgttlytdty[$blok]*100;};	
			if($byyrevperkgttllalu[$blok]!=0){$byygrossprofitpersenttllalu[$blok]=$byygrossprofitperkgttllalu[$blok]/$byyrevperkgttllalu[$blok]*100;};	
			if($byyrevperkgttlaop[$blok]!=0){$byygrossprofitpersenttlaop[$blok]=$byygrossprofitperkgttlaop[$blok]/$byyrevperkgttlaop[$blok]*100;};	
			$tab.="<td align=right >".numb_format($grossprofitpersenttlytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byygrossprofitpersenttllalu[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byygrossprofitpersenttlaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$grossprofitpersenttlknytdtydiv=$grossprofitpersenttlknlaludiv=$grossprofitpersenttlknaopdiv=array();	
			if($revperkgttlknytdtydiv[$div]!=0){$grossprofitpersenttlknytdtydiv[$div]=$grossprofitperkgttlknytdtydiv[$div]/$revperkgttlknytdtydiv[$div]*100;};	
			if($revperkgttlknlaludiv[$div]!=0){$grossprofitpersenttlknlaludiv[$div]=$grossprofitperkgttlknlaludiv[$div]/$revperkgttlknlaludiv[$div]*100;};	
			if($revperkgttlknaopdiv[$div]!=0){$grossprofitpersenttlknaopdiv[$div]=$grossprofitperkgttlknaopdiv[$div]/$revperkgttlknaopdiv[$div]*100;};	
			#ttl estate kanan	
			$grossprofitpersenttlknytdtyest=$grossprofitpersenttlknlaluest=$grossprofitpersenttlknaopest=array();	
			if($revperkgttlknytdtyest[$estate]!=0){$grossprofitpersenttlknytdtyest[$estate]=$grossprofitperkgttlknytdtyest[$estate]/$revperkgttlknytdtyest[$estate]*100;};	
			if($revperkgttlknlaluest[$estate]!=0){$grossprofitpersenttlknlaluest[$estate]=$grossprofitperkgttlknlaluest[$estate]/$revperkgttlknlaluest[$estate]*100;};	
			if($revperkgttlknaopest[$estate]!=0){$grossprofitpersenttlknaopest[$estate]=$grossprofitperkgttlknaopest[$estate]/$revperkgttlknaopest[$estate]*100;};	
			#grand total	
			$grossprofitpersenttlknytdtygt=$grossprofitpersenttlknlalugt=$grossprofitpersenttlknaopgt=0;	
			if($revperkgttlknytdtygt!=0){$grossprofitpersenttlknytdtygt=$grossprofitperkgttlknytdtygt/$revperkgttlknytdtygt*100;};	
			if($revperkgttlknlalugt!=0){$grossprofitpersenttlknlalugt=$grossprofitperkgttlknlalugt/$revperkgttlknlalugt*100;};	
			if($revperkgttlknaopgt!=0){$grossprofitpersenttlknaopgt=$grossprofitperkgttlknaopgt/$revperkgttlknaopgt*100;};	
			# === end Gross Profit %/Kg==>ttl ===	
				
			# === Yield/Ha (Ton)==> ===	
			$byyyieldha=$yieldhaytdty=$byyyieldhalalu=$byyyieldhaaop=$yieldhattldiv=$yieldhattlest=$yieldhagt=array();	
			$yieldhaknytdtygt=$yieldhaknlalugt=$yieldhaknaopgt=0;	
			$yieldhaknytdtyest=$yieldhaknlaluest=$yieldhaknaopest=array();	
			$yieldhaknytdtydiv=$yieldhaknlaludiv=$yieldhaknaopdiv=array();	
			
			
			if($haytdty[$blok]!=0){$yieldhaytdty[$blok]=$prdytdty[$blok]/$haytdty[$blok];};	
			
			#yield budget tahun ini sd bulan ini.
			if($luasbgtsdbi[$blok]!=0){$byyyieldhalalu[$blok]=$prdbgtsdbi[$blok]/$luasbgtsdbi[$blok];};	
			if($luasbgtsdbidiv[$div]!=0){$yieldhaknlaludiv[$div]=$prdbgtbidiv[$div]/$luasbgtsdbidiv[$div];};	
			if($luasbgtsdbiest[$estate]!=0){$yieldhaknlaluest[$estate]=$prdbgtbiest[$estate]/$luasbgtsdbiest[$estate];};	
			$prdbgtbigt+=$prdbgtsdbi[$blok];
			$luasbgtsdbigt+=$luasbgtsdbi[$blok];
			if($luasbgtsdbigt!=0){$yieldhaknlalugt=$prdbgtbigt/$luasbgtsdbigt;};	
			
			$n="";
			if($byyyieldhalalu[$blok]!=''){				
				if(($yieldhaytdty[$blok]/$byyyieldhalalu[$blok])*100>100){
					$n="style=background-color:green;";
					$green+=1;
					$nobrsgreen.="row_".$no."##";
				}
				if(($yieldhaytdty[$blok]/$byyyieldhalalu[$blok])*100>=90 and ($yieldhaytdty[$blok]/$byyyieldhalalu[$blok])*100<=100){
					$n="style=background-color:yellow;";
					$yellow+=1;
					$nobrsyellow.="row_".$no."##";
				}
				if(($yieldhaytdty[$blok]/$byyyieldhalalu[$blok])*100<90){
					$n="style=background-color:red;";
					$red+=1;
					$nobrsred.="row_".$no."##";
				}
			}else{
				$nobrsred.="row_".$no."##";
				$red+=1;
				$n="style=background-color:red;";				
			}
			$nobrttl.="row_".$no."##";
			
			
			if($luasaop[$blok]!=0){$byyyieldhaaop[$blok]=$prdtonaop[$blok]/$luasaop[$blok];};	
			$tab.="<td align=right ".$n.">".numb_format($yieldhaytdty[$blok],$nf2)."</td>";	
			$tab.="<td align=right >".numb_format($byyyieldhalalu[$blok],$nf2)."</td>";	
			$tab.="<td align=right >".numb_format($byyyieldhaaop[$blok],$nf2)."</td>";	
			#ttl divisi kanan	
			if($haknytdtydiv[$div]!=0){$yieldhaknytdtydiv[$div]=$prdknytdtydiv[$div]/$haknytdtydiv[$div];};	
			
			if($haknaopdiv[$div]!=0){$yieldhaknaopdiv[$div]=$prdknaopdiv[$div]/$haknaopdiv[$div];};	
			#ttl estate kanan	
			if($haknytdtyest[$estate]!=0){$yieldhaknytdtyest[$estate]=$prdknytdtyest[$estate]/$haknytdtyest[$estate];};	
			if($haknaopest[$estate]!=0){$yieldhaknaopest[$estate]=$prdknaopest[$estate]/$haknaopest[$estate];};	
			#grand total	
			if($haknytdtygt!=0){$yieldhaknytdtygt=$prdknytdtygt/$haknytdtygt;};	
			if($haknaopgt!=0){$yieldhaknaopgt=$prdknaopgt/$haknaopgt;};	
			# === end Yield/Ha (Ton)==> ===	
	
			# === Gross Profit/Ha (Rp Mn)==>ttl ===	
			$byygrossprofitperhattl=$grossprofitperhattlytdty=$byygrossprofitperhattllalu=$byygrossprofitperhattlaop=$grossprofitperhattlttldiv=$grossprofitperhattlttlest=$grossprofitperhattlgt=array();	
			if($haytdty[$blok]!=0){$grossprofitperhattlytdty[$blok]=$grossprofitttlytdty[$blok]/$haytdty[$blok];};	
			if($luaslalu[$blok]!=0){$byygrossprofitperhattllalu[$blok]=$byygrossprofitttllalu[$blok]/$luaslalu[$blok];};	
			if($luasaop[$blok]!=0){$byygrossprofitperhattlaop[$blok]=$byygrossprofitttlaop[$blok]/$luasaop[$blok];};	
			$tab.="<td align=right >".numb_format($grossprofitperhattlytdty[$blok],$nf2)."</td>";	
			$tab.="<td align=right >".numb_format($byygrossprofitperhattllalu[$blok],$nf2)."</td>";	
			$tab.="<td align=right >".numb_format($byygrossprofitperhattlaop[$blok],$nf2)."</td>";	
			#ttl divisi kanan	
			$grossprofitperhattlknytdtydiv=$grossprofitperhattlknlaludiv=$grossprofitperhattlknaopdiv=array();	
			if($haknytdtydiv[$div]!=0){$grossprofitperhattlknytdtydiv[$div]=$grossprofitttlknytdtydiv[$div]/$haknytdtydiv[$div];};	
			if($haknlaludiv[$div]!=0){$grossprofitperhattlknlaludiv[$div]=$grossprofitttlknlaludiv[$div]/$haknlaludiv[$div];};	
			if($haknaopdiv[$div]!=0){$grossprofitperhattlknaopdiv[$div]=$grossprofitttlknaopdiv[$div]/$haknaopdiv[$div];};	
			#ttl estate kanan	
			$grossprofitperhattlknytdtyest=$grossprofitperhattlknlaluest=$grossprofitperhattlknaopest=array();	
			if($haknytdtyest[$estate]!=0){$grossprofitperhattlknytdtyest[$estate]=$grossprofitttlknytdtyest[$estate]/$haknytdtyest[$estate];};	
			if($haknlaluest[$estate]!=0){$grossprofitperhattlknlaluest[$estate]=$grossprofitttlknlaluest[$estate]/$haknlaluest[$estate];};	
			if($haknaopest[$estate]!=0){$grossprofitperhattlknaopest[$estate]=$grossprofitttlknaopest[$estate]/$haknaopest[$estate];};	
			#grand total	
			$grossprofitperhattlknytdtygt=$grossprofitperhattlknlalugt=$grossprofitperhattlknaopgt=0;	
			if($haknytdtygt!=0){$grossprofitperhattlknytdtygt=$grossprofitttlknytdtygt/$haknytdtygt;};	
			if($haknlalugt!=0){$grossprofitperhattlknlalugt=$grossprofitttlknlalugt/$haknlalugt;};	
			if($haknaopgt!=0){$grossprofitperhattlknaopgt=$grossprofitttlknaopgt/$haknaopgt;};	
			# === end Gross Profit/Ha (Rp Mn)==>ttl ===	
		
			# === Yield/Pkk (Kg)==> ===	
			$byyyieldpkk=$yieldpkkytdty=$byyyieldpkklalu=$byyyieldpkkaop=$yieldpkkttldiv=$yieldpkkttlest=$yieldpkkgt=array();	
			if($pkkytdty[$blok]!=0){$yieldpkkytdty[$blok]=$prdytdty[$blok]/$pkkytdty[$blok]*1000;};	
			if($pokokaop[$blok]!=0){$byyyieldpkkaop[$blok]=$prdtonaop[$blok]/$pokokaop[$blok]*1000;};	
			$tab.="<td align=right >".numb_format($yieldpkkytdty[$blok],$nf0)."</td>";	
			$tab.="<td align=right >".numb_format($byyyieldpkkaop[$blok],$nf0)."</td>";	
			#ttl divisi kanan	
			$yieldpkkknytdtydiv=$yieldpkkknlaludiv=$yieldpkkknaopdiv=array();	
			if($pkkknytdtydiv[$div]!=0){$yieldpkkknytdtydiv[$div]=$prdknytdtydiv[$div]/$pkkknytdtydiv[$div]*1000;};	
			if($pkkknaopdiv[$div]!=0){$yieldpkkknaopdiv[$div]=$prdknaopdiv[$div]/$pkkknaopdiv[$div]*1000;};	
			#ttl estate kanan	
			$yieldpkkknytdtyest=$yieldpkkknlaluest=$yieldpkkknaopest=array();	
			if($pkkknytdtyest[$estate]!=0){$yieldpkkknytdtyest[$estate]=$prdknytdtyest[$estate]/$pkkknytdtyest[$estate]*1000;};	
			if($pkkknaopest[$estate]!=0){$yieldpkkknaopest[$estate]=$prdknaopest[$estate]/$pkkknaopest[$estate]*1000;};	
			#grand total	
			$yieldpkkknytdtygt=$yieldpkkknlalugt=$yieldpkkknaopgt=0;	
			if($pkkknytdtygt!=0){$yieldpkkknytdtygt=$prdknytdtygt/$pkkknytdtygt*1000;};	
			if($pkkknaopgt!=0){$yieldpkkknaopgt=$prdknaopgt/$pkkknaopgt*1000;};	
			# === end Yield/Pkk (Kg)==> ===	
	
			# === Gross Profit/Pkk (Rp 000)==> ===	
			$byygrossprofitpkk=$grossprofitpkkytdty=$byygrossprofitpkklalu=$byygrossprofitpkkaop=$grossprofitpkkttldiv=$grossprofitpkkttlest=$grossprofitpkkgt=array();	
			if($pkkytdty[$blok]!=0){$grossprofitpkkytdty[$blok]=$grossprofitttlytdty[$blok]/$pkkytdty[$blok];};	
			if($pokoklalu[$blok]!=0){$byygrossprofitpkklalu[$blok]=$byygrossprofitttllalu[$blok]/$pokoklalu[$blok];};	
			if($pokokaop[$blok]!=0){$byygrossprofitpkkaop[$blok]=$byygrossprofitttlaop[$blok]/$pokokaop[$blok];};	
			$tab.="<td align=right >".numb_format($grossprofitpkkytdty[$blok],$nf2)."</td>";	
			$tab.="<td align=right >".numb_format($byygrossprofitpkklalu[$blok],$nf2)."</td>";	
			$tab.="<td align=right >".numb_format($byygrossprofitpkkaop[$blok],$nf2)."</td>";	
			#ttl divisi kanan	
			$grossprofitpkkknytdtydiv=$grossprofitpkkknlaludiv=$grossprofitpkkknaopdiv=array();	
			if($pkkknytdtydiv[$div]!=0){$grossprofitpkkknytdtydiv[$div]=$grossprofitttlknytdtydiv[$div]/$pkkknytdtydiv[$div];};	
			if($pkkknlaludiv[$div]!=0){$grossprofitpkkknlaludiv[$div]=$grossprofitttlknlaludiv[$div]/$pkkknlaludiv[$div];};	
			if($pkkknaopdiv[$div]!=0){$grossprofitpkkknaopdiv[$div]=$grossprofitttlknaopdiv[$div]/$pkkknaopdiv[$div];};	
			#ttl estate kanan	
			$grossprofitpkkknytdtyest=$grossprofitpkkknlaluest=$grossprofitpkkknaopest=array();	
			if($pkkknytdtyest[$estate]!=0){$grossprofitpkkknytdtyest[$estate]=$grossprofitttlknytdtyest[$estate]/$pkkknytdtyest[$estate];};	
			if($pkkknlaluest[$estate]!=0){$grossprofitpkkknlaluest[$estate]=$grossprofitttlknlaluest[$estate]/$pkkknlaluest[$estate];};	
			if($pkkknaopest[$estate]!=0){$grossprofitpkkknaopest[$estate]=$grossprofitttlknaopest[$estate]/$pkkknaopest[$estate];};	
			#grand total	
			$grossprofitpkkknytdtygt=$grossprofitpkkknlalugt=$grossprofitpkkknaopgt=0;	
			if($pkkknytdtygt!=0){$grossprofitpkkknytdtygt=$grossprofitttlknytdtygt/$pkkknytdtygt;};	
			if($pkkknlalugt!=0){$grossprofitpkkknlalugt=$grossprofitttlknlalugt/$pkkknlalugt;};	
			if($pkkknaopgt!=0){$grossprofitpkkknaopgt=$grossprofitttlknaopgt/$pkkknaopgt;};	
			# === end Gross Profit/Pkk (Rp 000)==> ===	
			
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
		
		# === sph divisi ===	
		foreach($rangebln as $bln){
			$ttlsphdivbln=0;
			if($hattldiv[$div][$bln]>0){
				$ttlsphdivbln=$pkkttldiv[$div][$bln]/$hattldiv[$div][$bln];
			}
			$tab.="<td align=right name=sph[] ".$style.">".numb_format($ttlsphdivbln,$nf0)."</td>";
		}
		$ttlsphdivytd=$ttlsphdivlalu=$ttlsphdivaop=0;
		if($haknytdtydiv[$div]>0){
			$ttlsphdivytd=$pkkknytdtydiv[$div]/$haknytdtydiv[$div];
		}
		if($haknlaludiv[$div]>0){
			$ttlsphdivlalu=$pkkknlaludiv[$div]/$haknlaludiv[$div];
		}
		if($haknaopdiv[$div]>0){
			$ttlsphdivaop=$pkkknaopdiv[$div]/$haknaopdiv[$div];
		}
		$tab.="<td align=right >".numb_format($ttlsphdivytd,$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($ttlsphdivlalu,$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($ttlsphdivaop,$nf0)."</td>";	

		
		# === end sph divisi ===	
		# === pkk divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=pkk[] ".$style.">".numb_format($pkkttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($pkkknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($pkkknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($pkkknaopdiv[$div],$nf0)."</td>";	
		# === end pkk divisi ===	
	
		# === Production (Ton) divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=prd[] ".$style.">".numb_format($prdttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($prdknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($prdknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($prdknaopdiv[$div],$nf0)."</td>";	
		# === end Production (Ton) divisi ===	

		# === Harvesting Cost (Rp Mn)==>lab divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=pnnlab[] class=pnn[] ".$style.">".numb_format($pnnlabttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnlabknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnlabknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnlabknaopdiv[$div],$nf0)."</td>";	
		# === end Harvesting Cost (Rp Mn)==>lab divisi ===	
		# === Harvesting Cost (Rp Mn)==>mat divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=pnnmat[] class=pnn[] ".$style.">".numb_format($pnnmatttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnmatknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnmatknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnmatknaopdiv[$div],$nf0)."</td>";	
		# === end Harvesting Cost (Rp Mn)==>mat divisi ===	
		# === Harvesting Cost (Rp Mn)==>trans divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=pnntrans[] class=pnn[] ".$style.">".numb_format($pnntransttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnntransknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnntransknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnntransknaopdiv[$div],$nf0)."</td>";	
		# === end Harvesting Cost (Rp Mn)==>trans divisi ===	
		# === Harvesting Cost (Rp Mn)==>oth divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=pnnoth[] class=pnn[] ".$style.">".numb_format($pnnothttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnothknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnothknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnothknaopdiv[$div],$nf0)."</td>";	
		# === end Harvesting Cost (Rp Mn)==>oth divisi ===	
		
		# === Harvesting Cost (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=pnnttl[] class=pnn[] ".$style.">".numb_format($pnnttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($pnnttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($pnnttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($pnnttlknaopdiv[$div],$nf0)."</td>";	
		# === end Harvesting Cost (Rp Mn)==>ttl divisi ===	
		
			
		# === Fertilizing Cost (Rp Mn)==>lab divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ppklab[] class=ppk[] ".$style.">".numb_format($ppklabttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppklabknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppklabknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppklabknaopdiv[$div],$nf0)."</td>";	
		# === end Fertilizing Cost (Rp Mn)==>lab divisi ===	
		# === Fertilizing Cost (Rp Mn)==>mat divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ppkmat[] class=ppk[] ".$style.">".numb_format($ppkmatttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkmatknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkmatknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkmatknaopdiv[$div],$nf0)."</td>";	
		# === end Fertilizing Cost (Rp Mn)==>mat divisi ===	
		# === Fertilizing Cost (Rp Mn)==>trans divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ppktrans[] class=ppk[] ".$style.">".numb_format($ppktransttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppktransknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppktransknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppktransknaopdiv[$div],$nf0)."</td>";	
		# === end Fertilizing Cost (Rp Mn)==>trans divisi ===	
		# === Fertilizing Cost (Rp Mn)==>oth divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ppkoth[] class=ppk[] ".$style.">".numb_format($ppkothttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkothknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkothknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkothknaopdiv[$div],$nf0)."</td>";	
		# === end Fertilizing Cost (Rp Mn)==>oth divisi ===	
		
			
		# === Fertilizing Cost (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ppkttl[] class=ppk[] ".$style.">".numb_format($ppkttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right>".numb_format($ppkttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right>".numb_format($ppkttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right>".numb_format($ppkttlknaopdiv[$div],$nf0)."</td>";	
		# === end Fertilizing Cost (Rp Mn)==>ttl divisi ===	


		# === Maintenance Mature Cost (Rp Mn)==>lab divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tmlab[] class=tm[] ".$style.">".numb_format($tmlabttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmlabknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmlabknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmlabknaopdiv[$div],$nf0)."</td>";	
		# === end Maintenance Mature Cost (Rp Mn)==>lab divisi ===	
		# === Maintenance Mature Cost (Rp Mn)==>mat divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tmmat[] class=tm[] ".$style.">".numb_format($tmmatttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmmatknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmmatknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmmatknaopdiv[$div],$nf0)."</td>";	
		# === end Maintenance Mature Cost (Rp Mn)==>mat divisi ===	
		# === Maintenance Mature Cost (Rp Mn)==>trans divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tmtrans[] class=tm[] ".$style.">".numb_format($tmtransttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmtransknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmtransknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmtransknaopdiv[$div],$nf0)."</td>";	
		# === end Maintenance Mature Cost (Rp Mn)==>trans divisi ===	
		# === Maintenance Mature Cost (Rp Mn)==>oth divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tmoth[] class=tm[] ".$style.">".numb_format($tmothttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmothknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmothknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmothknaopdiv[$div],$nf0)."</td>";	
		# === end Maintenance Mature Cost (Rp Mn)==>oth divisi ===	
		# === Maintenance Mature Cost (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tmttl[] class=tm[] ".$style.">".numb_format($tmttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($tmttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tmttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tmttlknaopdiv[$div],$nf0)."</td>";	
		# === end Maintenance Mature Cost (Rp Mn)==>ttl divisi ===	

	
		# === Overhead Excl Depre (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=ohxdep[] ".$style.">".numb_format($ohxdepttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($ohxdepttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($ohxdepttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($ohxdepttlknaopdiv[$div],$nf0)."</td>";	
		# === end Overhead Excl Depre (Rp Mn)==>ttl divisi ===	

	
		# === Total Production Cost Excl Depre (Rp Mn)==>lab divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdxdeplab[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdeplabttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeplabknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeplabknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeplabknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost Excl Depre (Rp Mn)==>lab divisi ===	

		# === Total Production Cost Excl Depre (Rp Mn)==>mat divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdxdepmat[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdepmatttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepmatknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepmatknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepmatknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost Excl Depre (Rp Mn)==>mat divisi ===	
		# === Total Production Cost Excl Depre (Rp Mn)==>trans divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdxdeptrans[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdeptransttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeptransknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeptransknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeptransknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost Excl Depre (Rp Mn)==>trans divisi ===	
		# === Total Production Cost Excl Depre (Rp Mn)==>oth divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdxdepoth[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdepothttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepothknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepothknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepothknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost Excl Depre (Rp Mn)==>oth divisi ===	
		# === Total Production Cost Excl Depre (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdxdepttl[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdepttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($tcostprdxdepttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tcostprdxdepttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tcostprdxdepttlknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost Excl Depre (Rp Mn)==>ttl divisi ===	

		# === Depreciation (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=dep[] ".$style.">".numb_format($depttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($depttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($depttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($depttlknaopdiv[$div],$nf0)."</td>";	
		# === end Depreciation (Rp Mn)==>ttl divisi ===	
	
	
		# === Total Production Cost (Rp Mn)==>lab divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdlab[] class=tcostprd[] ".$style.">".numb_format($tcostprdlabttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdlabknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdlabknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdlabknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost (Rp Mn)==>lab divisi ===	
		# === Total Production Cost (Rp Mn)==>mat divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdmat[] class=tcostprd[] ".$style.">".numb_format($tcostprdmatttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdmatknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdmatknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdmatknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost (Rp Mn)==>mat divisi ===	
		# === Total Production Cost (Rp Mn)==>trans divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdtrans[] class=tcostprd[] ".$style.">".numb_format($tcostprdtransttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdtransknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdtransknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdtransknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost (Rp Mn)==>trans divisi ===	
		# === Total Production Cost (Rp Mn)==>oth divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdoth[] class=tcostprd[] ".$style.">".numb_format($tcostprdothttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdothknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdothknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdothknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost (Rp Mn)==>oth divisi ===	
		# === Total Production Cost (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdttl[] class=tcostprd[] ".$style.">".numb_format($tcostprdttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($tcostprdttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tcostprdttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tcostprdttlknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost (Rp Mn)==>ttl divisi ===	
	
		# === Total Revenue (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=rev[] ".$style.">".numb_format($revttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($revttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($revttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($revttlknaopdiv[$div],$nf0)."</td>";	
		# === end Total Revenue (Rp Mn)==>ttl divisi ===	
	
		# === Total Cash Profit (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=cashprofit[] ".$style.">".numb_format($cashprofitttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($cashprofitttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($cashprofitttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($cashprofitttlknaopdiv[$div],$nf0)."</td>";	
		# === end Total Cash Profit (Rp Mn)==>ttl divisi ===	
	
		# === Total Gross Profit (Rp Mn)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=grossprofit[] ".$style.">".numb_format($grossprofitttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($grossprofitttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($grossprofitttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($grossprofitttlknaopdiv[$div],$nf0)."</td>";	
		# === end Total Gross Profit (Rp Mn)==>ttl divisi ===	
	
		# === Total Production Cost Excl Depre/Kg (Rp)==>lab divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdxdepperkglab[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkglabttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkglabknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkglabknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkglabknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost Excl Depre/Kg (Rp)==>lab divisi ===	
		# === Total Production Cost Excl Depre/Kg (Rp)==>mat divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdxdepperkgmat[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgmatttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgmatknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgmatknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgmatknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost Excl Depre/Kg (Rp)==>mat divisi ===	
		# === Total Production Cost Excl Depre/Kg (Rp)==>trans divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdxdepperkgtrans[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgtransttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgtransknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgtransknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgtransknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost Excl Depre/Kg (Rp)==>trans divisi ===	
		# === Total Production Cost Excl Depre/Kg (Rp)==>oth divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdxdepperkgoth[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgothttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgothknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgothknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgothknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost Excl Depre/Kg (Rp)==>oth divisi ===	
		# === Total Production Cost Excl Depre/Kg (Rp)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdxdepperkgttl[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($tcostprdxdepperkgttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tcostprdxdepperkgttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tcostprdxdepperkgttlknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost Excl Depre/Kg (Rp)==>ttl divisi ===	
	
		# === Total Production Cost/Kg (Rp)==>lab divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdperkglab[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkglabttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkglabknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkglabknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkglabknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost/Kg (Rp)==>lab divisi ===	
		# === Total Production Cost/Kg (Rp)==>mat divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdperkgmat[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgmatttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgmatknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgmatknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgmatknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost/Kg (Rp)==>mat divisi ===	
		# === Total Production Cost/Kg (Rp)==>trans divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdperkgtrans[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgtransttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgtransknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgtransknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgtransknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost/Kg (Rp)==>trans divisi ===	
		# === Total Production Cost/Kg (Rp)==>oth divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdperkgoth[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgothttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgothknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgothknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgothknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost/Kg (Rp)==>oth divisi ===	
		# === Total Production Cost/Kg (Rp)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=tcostprdperkgttl[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($tcostprdperkgttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tcostprdperkgttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($tcostprdperkgttlknaopdiv[$div],$nf0)."</td>";	
		# === end Total Production Cost/Kg (Rp)==>ttl divisi ===	
	
		# === Revenue/Kg (Rp)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=revperkg[] ".$style.">".numb_format($revperkgttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($revperkgttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($revperkgttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($revperkgttlknaopdiv[$div],$nf0)."</td>";	
		# === end Revenue/Kg (Rp)==>ttl divisi ===	
	
		# === Total Cash Profit/Kg (Rp)==>ttl divisi ===	
		foreach($rangebln as $bln){	
		
			$tab.="<td align=right name=cashprofitperkg[] ".$style.">".numb_format($cashprofitperkgttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($cashprofitperkgttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($cashprofitperkgttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($cashprofitperkgttlknaopdiv[$div],$nf0)."</td>";	
		# === end Total Cash Profit/Kg (Rp)==>ttl divisi ===	
		# === Gross Profit/Kg (Rp)==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=grossprofitperkg[] ".$style.">".numb_format($grossprofitperkgttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($grossprofitperkgttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($grossprofitperkgttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($grossprofitperkgttlknaopdiv[$div],$nf0)."</td>";	
		# === end Gross Profit/Kg (Rp)==>ttl divisi ===	
			
		# === Gross Profit %/Kg==>ttl divisi ===	
		foreach($rangebln as $bln){	
			$tab.="<td align=right name=grossprofitpersen[] ".$style.">".numb_format($grossprofitpersenttlttldiv[$div][$bln],$nf0)."</td>";
		}	
		$tab.="<td align=right >".numb_format($grossprofitpersenttlknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($grossprofitpersenttlknlaludiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($grossprofitpersenttlknaopdiv[$div],$nf0)."</td>";	
		# === end Gross Profit %/Kg==>ttl divisi ===	
	
		# === Yield/Ha (Ton)==> divisi ===	
		$tab.="<td align=right >".numb_format($yieldhaknytdtydiv[$div],$nf2)."</td>";	
		$tab.="<td align=right >".numb_format($yieldhaknlaludiv[$div],$nf2)."</td>";	
		$tab.="<td align=right >".numb_format($yieldhaknaopdiv[$div],$nf2)."</td>";	
		# === end Yield/Ha (Ton)==> divisi ===	
	
		# === Gross Profit/Ha (Rp Mn)==>ttl divisi ===	
		$tab.="<td align=right >".numb_format($grossprofitperhattlknytdtydiv[$div],$nf2)."</td>";	
		$tab.="<td align=right >".numb_format($grossprofitperhattlknlaludiv[$div],$nf2)."</td>";	
		$tab.="<td align=right >".numb_format($grossprofitperhattlknaopdiv[$div],$nf2)."</td>";	
		# === end Gross Profit/Ha (Rp Mn)==>ttl divisi ===	
	
		# === Yield/Pkk (Kg)==> divisi ===	
		$tab.="<td align=right >".numb_format($yieldpkkknytdtydiv[$div],$nf0)."</td>";	
		$tab.="<td align=right >".numb_format($yieldpkkknaopdiv[$div],$nf0)."</td>";	
		# === end Yield/Pkk (Kg)==> divisi ===	
	
		# === Gross Profit/Pkk (Rp 000)==> divisi ===	
		$tab.="<td align=right >".numb_format($grossprofitpkkknytdtydiv[$div],$nf2)."</td>";	
		$tab.="<td align=right >".numb_format($grossprofitpkkknlaludiv[$div],$nf2)."</td>";	
		$tab.="<td align=right >".numb_format($grossprofitpkkknaopdiv[$div],$nf2)."</td>";	
		# === end Gross Profit/Pkk (Rp 000)==> divisi ===	


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
	
	# === sph estate ===	
	foreach($rangebln as $bln){	
		$ttlsphestbln=0;
		if($hattlest[$estate][$bln]>0){			
			$ttlsphestbln=$pkkttlest[$estate][$bln]/$hattlest[$estate][$bln];
		}
		$tab.="<td align=right name=sph[] ".$style.">".numb_format($ttlsphestbln,$nf0)."</td>";
	}
	$ttlsphestytd=$ttlsphestlalu=$ttlsphestaop=0;
	if($haknytdtyest[$estate]>0){		
		$ttlsphestytd=$pkkknytdtyest[$estate]/$haknytdtyest[$estate];
	}
	if($haknlaluest[$estate]>0){
		$ttlsphestlalu=$pkkknlaluest[$estate]/$haknlaluest[$estate];
	}
	if($haknaopest[$estate]>0){
		$ttlsphestaop=$pkkknaopest[$estate]/$haknaopest[$estate];
	}
	$tab.="<td align=right >".numb_format($ttlsphestytd,$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($ttlsphestlalu,$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($ttlsphestaop,$nf0)."</td>";	
	
	# === end sph estate ===	

	# === pkk estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=pkk[] ".$style.">".numb_format($pkkttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($pkkknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($pkkknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($pkkknaopest[$estate],$nf0)."</td>";	
	# === end pkk estate ===	

	# === Production (Ton) estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=prd[] ".$style.">".numb_format($prdttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($prdknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($prdknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($prdknaopest[$estate],$nf0)."</td>";	
	# === end Production (Ton) estate ===	

	# === Harvesting Cost (Rp Mn)==>lab estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=pnnlab[] class=pnn[] ".$style.">".numb_format($pnnlabttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnlabknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style." >".numb_format($pnnlabknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnlabknaopest[$estate],$nf0)."</td>";	
	# === end Harvesting Cost (Rp Mn)==>lab estate ===	
	# === Harvesting Cost (Rp Mn)==>mat estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=pnnmat[] class=pnn[] ".$style.">".numb_format($pnnmatttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnmatknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style." >".numb_format($pnnmatknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnmatknaopest[$estate],$nf0)."</td>";	
	# === end Harvesting Cost (Rp Mn)==>mat estate ===	
	# === Harvesting Cost (Rp Mn)==>trans estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=pnntrans[] class=pnn[] ".$style.">".numb_format($pnntransttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnntransknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style." >".numb_format($pnntransknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnntransknaopest[$estate],$nf0)."</td>";	
	# === end Harvesting Cost (Rp Mn)==>trans estate ===	
	# === Harvesting Cost (Rp Mn)==>oth estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=pnnoth[] class=pnn[] ".$style.">".numb_format($pnnothttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnothknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style." >".numb_format($pnnothknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnothknaopest[$estate],$nf0)."</td>";	
	# === end Harvesting Cost (Rp Mn)==>oth estate ===	
	# === Harvesting Cost (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=pnnttl[] class=pnn[] ".$style.">".numb_format($pnnttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($pnnttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($pnnttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($pnnttlknaopest[$estate],$nf0)."</td>";	
	# === end Harvesting Cost (Rp Mn)==>ttl estate ===	
	
		
	# === Fertilizing Cost (Rp Mn)==>lab estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ppklab[] class=ppk[] ".$style.">".numb_format($ppklabttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppklabknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style." >".numb_format($ppklabknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppklabknaopest[$estate],$nf0)."</td>";	
	# === end Fertilizing Cost (Rp Mn)==>lab estate ===	
	# === Fertilizing Cost (Rp Mn)==>mat estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ppkmat[] class=ppk[] ".$style.">".numb_format($ppkmatttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkmatknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style." >".numb_format($ppkmatknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkmatknaopest[$estate],$nf0)."</td>";	
	# === end Fertilizing Cost (Rp Mn)==>mat estate ===	
	# === Fertilizing Cost (Rp Mn)==>trans estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ppktrans[] class=ppk[] ".$style.">".numb_format($ppktransttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppktransknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style." >".numb_format($ppktransknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppktransknaopest[$estate],$nf0)."</td>";	
	# === end Fertilizing Cost (Rp Mn)==>trans estate ===	
	# === Fertilizing Cost (Rp Mn)==>oth estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ppkoth[] class=ppk[] ".$style.">".numb_format($ppkothttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkothknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style." >".numb_format($ppkothknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkothknaopest[$estate],$nf0)."</td>";	
	# === end Fertilizing Cost (Rp Mn)==>oth estate ===	
		
	# === Fertilizing Cost (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ppkttl[] class=ppk[] ".$style.">".numb_format($ppkttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($ppkttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($ppkttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($ppkttlknaopest[$estate],$nf0)."</td>";	
	# === end Fertilizing Cost (Rp Mn)==>ttl estate ===	

	
	# === Maintenance Mature Cost (Rp Mn)==>lab estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tmlab[] class=tm[] ".$style.">".numb_format($tmlabttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmlabknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style." >".numb_format($tmlabknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmlabknaopest[$estate],$nf0)."</td>";	
	# === end Maintenance Mature Cost (Rp Mn)==>lab estate ===	
	# === Maintenance Mature Cost (Rp Mn)==>mat estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tmmat[] class=tm[] ".$style.">".numb_format($tmmatttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmmatknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style." >".numb_format($tmmatknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmmatknaopest[$estate],$nf0)."</td>";	
	# === end Maintenance Mature Cost (Rp Mn)==>mat estate ===	
	# === Maintenance Mature Cost (Rp Mn)==>trans estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tmtrans[] class=tm[] ".$style.">".numb_format($tmtransttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmtransknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style." >".numb_format($tmtransknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmtransknaopest[$estate],$nf0)."</td>";	
	# === end Maintenance Mature Cost (Rp Mn)==>trans estate ===	
	# === Maintenance Mature Cost (Rp Mn)==>oth estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tmoth[] class=tm[] ".$style.">".numb_format($tmothttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmothknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style." >".numb_format($tmothknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmothknaopest[$estate],$nf0)."</td>";	
	# === end Maintenance Mature Cost (Rp Mn)==>oth estate ===	
	# === Maintenance Mature Cost (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tmttl[] class=tm[] ".$style.">".numb_format($tmttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($tmttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tmttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tmttlknaopest[$estate],$nf0)."</td>";	
	# === end Maintenance Mature Cost (Rp Mn)==>ttl estate ===	

	
	# === Overhead Excl Depre (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=ohxdep[] ".$style.">".numb_format($ohxdepttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($ohxdepttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($ohxdepttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($ohxdepttlknaopest[$estate],$nf0)."</td>";	
	# === end Overhead Excl Depre (Rp Mn)==>ttl estate ===	

	# === Total Production Cost Excl Depre (Rp Mn)==>lab estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdxdeplab[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdeplabttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeplabknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style." >".numb_format($tcostprdxdeplabknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeplabknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost Excl Depre (Rp Mn)==>lab estate ===	

	
	# === Total Production Cost Excl Depre (Rp Mn)==>mat estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdxdepmat[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdepmatttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepmatknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style." >".numb_format($tcostprdxdepmatknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepmatknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost Excl Depre (Rp Mn)==>mat estate ===	
	# === Total Production Cost Excl Depre (Rp Mn)==>trans estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdxdeptrans[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdeptransttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeptransknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style." >".numb_format($tcostprdxdeptransknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeptransknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost Excl Depre (Rp Mn)==>trans estate ===	
	# === Total Production Cost Excl Depre (Rp Mn)==>oth estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdxdepoth[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdepothttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepothknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style." >".numb_format($tcostprdxdepothknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepothknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost Excl Depre (Rp Mn)==>oth estate ===	
	# === Total Production Cost Excl Depre (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdxdepttl[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdepttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($tcostprdxdepttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tcostprdxdepttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tcostprdxdepttlknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost Excl Depre (Rp Mn)==>ttl estate ===	

	# === Depreciation (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=dep[] ".$style.">".numb_format($depttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($depttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($depttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($depttlknaopest[$estate],$nf0)."</td>";	
	# === end Depreciation (Rp Mn)==>ttl estate ===	
	
	
	# === Total Production Cost (Rp Mn)==>lab estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdlab[] class=tcostprd[] ".$style.">".numb_format($tcostprdlabttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdlabknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style." >".numb_format($tcostprdlabknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdlabknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost (Rp Mn)==>lab estate ===	
	# === Total Production Cost (Rp Mn)==>mat estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdmat[] class=tcostprd[] ".$style.">".numb_format($tcostprdmatttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdmatknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style." >".numb_format($tcostprdmatknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdmatknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost (Rp Mn)==>mat estate ===	
	# === Total Production Cost (Rp Mn)==>trans estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdtrans[] class=tcostprd[] ".$style.">".numb_format($tcostprdtransttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdtransknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style." >".numb_format($tcostprdtransknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdtransknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost (Rp Mn)==>trans estate ===	
	# === Total Production Cost (Rp Mn)==>oth estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdoth[] class=tcostprd[] ".$style.">".numb_format($tcostprdothttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdothknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style." >".numb_format($tcostprdothknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdothknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost (Rp Mn)==>oth estate ===	
	# === Total Production Cost (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdttl[] class=tcostprd[] ".$style.">".numb_format($tcostprdttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($tcostprdttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tcostprdttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tcostprdttlknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost (Rp Mn)==>ttl estate ===	

	# === Total Revenue (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=rev[] ".$style.">".numb_format($revttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($revttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($revttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($revttlknaopest[$estate],$nf0)."</td>";	
	# === end Total Revenue (Rp Mn)==>ttl estate ===	
		
	# === Total Cash Profit (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=cashprofit[] ".$style.">".numb_format($cashprofitttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($cashprofitttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($cashprofitttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($cashprofitttlknaopest[$estate],$nf0)."</td>";	
	# === end Total Cash Profit (Rp Mn)==>ttl estate ===	
	
	# === Total Gross Profit (Rp Mn)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=grossprofit[] ".$style.">".numb_format($grossprofitttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($grossprofitttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($grossprofitttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($grossprofitttlknaopest[$estate],$nf0)."</td>";	
	# === end Total Gross Profit (Rp Mn)==>ttl estate ===	
	
	# === Total Production Cost Excl Depre/Kg (Rp)==>lab estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdxdepperkglab[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkglabttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkglabknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style." >".numb_format($tcostprdxdepperkglabknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkglabknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost Excl Depre/Kg (Rp)==>lab estate ===	
	# === Total Production Cost Excl Depre/Kg (Rp)==>mat estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdxdepperkgmat[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgmatttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgmatknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style." >".numb_format($tcostprdxdepperkgmatknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgmatknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost Excl Depre/Kg (Rp)==>mat estate ===	
	# === Total Production Cost Excl Depre/Kg (Rp)==>trans estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdxdepperkgtrans[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgtransttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgtransknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style." >".numb_format($tcostprdxdepperkgtransknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgtransknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost Excl Depre/Kg (Rp)==>trans estate ===	
	# === Total Production Cost Excl Depre/Kg (Rp)==>oth estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdxdepperkgoth[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgothttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgothknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style." >".numb_format($tcostprdxdepperkgothknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgothknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost Excl Depre/Kg (Rp)==>oth estate ===	
	# === Total Production Cost Excl Depre/Kg (Rp)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdxdepperkgttl[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($tcostprdxdepperkgttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tcostprdxdepperkgttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tcostprdxdepperkgttlknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost Excl Depre/Kg (Rp)==>ttl estate ===	
	
	# === Total Production Cost/Kg (Rp)==>lab estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdperkglab[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkglabttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkglabknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style." >".numb_format($tcostprdperkglabknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkglabknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost/Kg (Rp)==>lab estate ===	
	# === Total Production Cost/Kg (Rp)==>mat estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdperkgmat[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgmatttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgmatknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style." >".numb_format($tcostprdperkgmatknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgmatknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost/Kg (Rp)==>mat estate ===	
	# === Total Production Cost/Kg (Rp)==>trans estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdperkgtrans[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgtransttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgtransknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style." >".numb_format($tcostprdperkgtransknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgtransknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost/Kg (Rp)==>trans estate ===	
	# === Total Production Cost/Kg (Rp)==>oth estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdperkgoth[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgothttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgothknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style." >".numb_format($tcostprdperkgothknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgothknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost/Kg (Rp)==>oth estate ===	
	# === Total Production Cost/Kg (Rp)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=tcostprdperkgttl[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($tcostprdperkgttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tcostprdperkgttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($tcostprdperkgttlknaopest[$estate],$nf0)."</td>";	
	# === end Total Production Cost/Kg (Rp)==>ttl estate ===	
	
	# === Revenue/Kg (Rp)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=revperkg[] ".$style.">".numb_format($revperkgttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($revperkgttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($revperkgttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($revperkgttlknaopest[$estate],$nf0)."</td>";	
	# === end Revenue/Kg (Rp)==>ttl estate ===	
	
	# === Total Cash Profit/Kg (Rp)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=cashprofitperkg[] ".$style.">".numb_format($cashprofitperkgttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($cashprofitperkgttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($cashprofitperkgttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($cashprofitperkgttlknaopest[$estate],$nf0)."</td>";	
	# === end Total Cash Profit/Kg (Rp)==>ttl estate ===	
	# === Gross Profit/Kg (Rp)==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=grossprofitperkg[] ".$style.">".numb_format($grossprofitperkgttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($grossprofitperkgttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($grossprofitperkgttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($grossprofitperkgttlknaopest[$estate],$nf0)."</td>";	
	# === end Gross Profit/Kg (Rp)==>ttl estate ===	
	
	# === Gross Profit %/Kg==>ttl estate ===	
	foreach($rangebln as $bln){	
		$tab.="<td align=right name=grossprofitpersen[] ".$style.">".numb_format($grossprofitpersenttlttlest[$estate][$bln],$nf0)."</td>";
	}	
	$tab.="<td align=right >".numb_format($grossprofitpersenttlknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($grossprofitpersenttlknlaluest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($grossprofitpersenttlknaopest[$estate],$nf0)."</td>";	
	# === end Gross Profit %/Kg==>ttl estate ===	
	
	# === Yield/Ha (Ton)==> estate ===	
	$tab.="<td align=right >".numb_format($yieldhaknytdtyest[$estate],$nf2)."</td>";	
	$tab.="<td align=right >".numb_format($yieldhaknlaluest[$estate],$nf2)."</td>";	
	$tab.="<td align=right >".numb_format($yieldhaknaopest[$estate],$nf2)."</td>";	
	# === end Yield/Ha (Ton)==> estate ===	
	
	# === Gross Profit/Ha (Rp Mn)==>ttl estate ===	
	$tab.="<td align=right >".numb_format($grossprofitperhattlknytdtyest[$estate],$nf2)."</td>";	
	$tab.="<td align=right >".numb_format($grossprofitperhattlknlaluest[$estate],$nf2)."</td>";	
	$tab.="<td align=right >".numb_format($grossprofitperhattlknaopest[$estate],$nf2)."</td>";	
	# === end Gross Profit/Ha (Rp Mn)==>ttl estate ===	
	
	# === Yield/Pkk (Kg)==> estate ===	
	$tab.="<td align=right >".numb_format($yieldpkkknytdtyest[$estate],$nf0)."</td>";	
	$tab.="<td align=right >".numb_format($yieldpkkknaopest[$estate],$nf0)."</td>";	
	# === end Yield/Pkk (Kg)==> estate ===	
	
	# === Gross Profit/Pkk (Rp 000)==> estate ===	
	$tab.="<td align=right >".numb_format($grossprofitpkkknytdtyest[$estate],$nf2)."</td>";	
	$tab.="<td align=right >".numb_format($grossprofitpkkknlaluest[$estate],$nf2)."</td>";	
	$tab.="<td align=right >".numb_format($grossprofitpkkknaopest[$estate],$nf2)."</td>";	
	# === end Gross Profit/Pkk (Rp 000)==> estate ===	


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
# === sph gt ===	
foreach($rangebln as $bln){	
	$sphgtl=0;
	if($hagt[$bln]>0){
		$sphgtl=$pkkgt[$bln]/$hagt[$bln];
	}
	$tab.="<td align=right name=sph[] ".$style.">".numb_format($sphgtl,$nf0)."</td>";
}	
$gtsphytd=$gtsphlalu=$gtsphaop=0;
if($haknytdtygt>0){	
	$gtsphytd=$pkkknytdtygt/$haknytdtygt;
}
if($haknlalugt>0){	
	$gtsphlalu=$pkkknlalugt/$haknlalugt;
}
if($haknaopgt>0){
	$gtsphaop=$pkkknaopgt/$haknaopgt;	
}

$tab.="<td align=right >".numb_format($gtsphytd,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($gtsphlalu,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($gtsphaop,$nf0)."</td>";	

# === end sph gt ===	


# === pkk gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=pkk[] ".$style.">".numb_format($pkkgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($pkkknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($pkkknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($pkkknaopgt,$nf0)."</td>";	
# === end pkk gt ===	

# === Production (Ton) gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=prd[] ".$style.">".numb_format($prdgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($prdknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($prdknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($prdknaopgt,$nf0)."</td>";	
# === end Production (Ton) gt ===	
# === Harvesting Cost (Rp Mn)==>lab gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=pnnlab[] class=pnn[] ".$style.">".numb_format($pnnlabgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnlabknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnlabknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnlabknaopgt,$nf0)."</td>";	
# === end Harvesting Cost (Rp Mn)==>lab gt ===	
# === Harvesting Cost (Rp Mn)==>mat gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=pnnmat[] class=pnn[] ".$style.">".numb_format($pnnmatgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnmatknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnmatknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnmatknaopgt,$nf0)."</td>";	
# === end Harvesting Cost (Rp Mn)==>mat gt ===	
# === Harvesting Cost (Rp Mn)==>trans gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=pnntrans[] class=pnn[] ".$style.">".numb_format($pnntransgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnntransknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnntransknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnntransknaopgt,$nf0)."</td>";	
# === end Harvesting Cost (Rp Mn)==>trans gt ===	
# === Harvesting Cost (Rp Mn)==>oth gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=pnnoth[] class=pnn[] ".$style.">".numb_format($pnnothgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnothknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnothknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costpnn[] class=pnn[]  ".$style.">".numb_format($pnnothknaopgt,$nf0)."</td>";	
# === end Harvesting Cost (Rp Mn)==>oth gt ===	
# === Harvesting Cost (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=pnnttl[] class=pnn[] ".$style.">".numb_format($pnnttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($pnnttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($pnnttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($pnnttlknaopgt,$nf0)."</td>";	
# === end Harvesting Cost (Rp Mn)==>ttl gt ===	
	
	
# === Fertilizing Cost (Rp Mn)==>lab gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=ppklab[] class=ppk[] ".$style.">".numb_format($ppklabgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppklabknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppklabknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppklabknaopgt,$nf0)."</td>";	
# === end Fertilizing Cost (Rp Mn)==>lab gt ===	
# === Fertilizing Cost (Rp Mn)==>mat gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=ppkmat[] class=ppk[] ".$style.">".numb_format($ppkmatgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkmatknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkmatknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkmatknaopgt,$nf0)."</td>";	
# === end Fertilizing Cost (Rp Mn)==>mat gt ===	
# === Fertilizing Cost (Rp Mn)==>trans gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=ppktrans[] class=ppk[] ".$style.">".numb_format($ppktransgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppktransknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppktransknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppktransknaopgt,$nf0)."</td>";	
# === end Fertilizing Cost (Rp Mn)==>trans gt ===	
# === Fertilizing Cost (Rp Mn)==>oth gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=ppkoth[] class=ppk[] ".$style.">".numb_format($ppkothgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkothknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkothknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costppk[] class=ppk[]  ".$style.">".numb_format($ppkothknaopgt,$nf0)."</td>";	
# === end Fertilizing Cost (Rp Mn)==>oth gt ===	

	
# === Fertilizing Cost (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=ppkttl[] class=ppk[] ".$style.">".numb_format($ppkttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($ppkttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($ppkttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($ppkttlknaopgt,$nf0)."</td>";	
# === end Fertilizing Cost (Rp Mn)==>ttl gt ===	


# === Maintenance Mature Cost (Rp Mn)==>lab gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tmlab[] class=tm[] ".$style.">".numb_format($tmlabgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmlabknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmlabknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmlabknaopgt,$nf0)."</td>";	
# === end Maintenance Mature Cost (Rp Mn)==>lab gt ===	
# === Maintenance Mature Cost (Rp Mn)==>mat gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tmmat[] class=tm[] ".$style.">".numb_format($tmmatgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmmatknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmmatknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmmatknaopgt,$nf0)."</td>";	
# === end Maintenance Mature Cost (Rp Mn)==>mat gt ===	
# === Maintenance Mature Cost (Rp Mn)==>trans gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tmtrans[] class=tm[] ".$style.">".numb_format($tmtransgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmtransknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmtransknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmtransknaopgt,$nf0)."</td>";	
# === end Maintenance Mature Cost (Rp Mn)==>trans gt ===	
# === Maintenance Mature Cost (Rp Mn)==>oth gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tmoth[] class=tm[] ".$style.">".numb_format($tmothgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmothknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmothknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtm[] class=tm[]  ".$style.">".numb_format($tmothknaopgt,$nf0)."</td>";	
# === end Maintenance Mature Cost (Rp Mn)==>oth gt ===	
# === Maintenance Mature Cost (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tmttl[] class=tm[] ".$style.">".numb_format($tmttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($tmttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tmttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tmttlknaopgt,$nf0)."</td>";	
# === end Maintenance Mature Cost (Rp Mn)==>ttl gt ===	
	
	
# === Overhead Excl Depre (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=ohxdep[] ".$style.">".numb_format($ohxdepttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($ohxdepttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($ohxdepttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($ohxdepttlknaopgt,$nf0)."</td>";	
# === end Overhead Excl Depre (Rp Mn)==>ttl gt ===	

	
# === Total Production Cost Excl Depre (Rp Mn)==>lab gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdxdeplab[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdeplabgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeplabknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeplabknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeplabknaopgt,$nf0)."</td>";	
# === end Total Production Cost Excl Depre (Rp Mn)==>lab gt ===	

	
# === Total Production Cost Excl Depre (Rp Mn)==>mat gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdxdepmat[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdepmatgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepmatknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepmatknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepmatknaopgt,$nf0)."</td>";	
# === end Total Production Cost Excl Depre (Rp Mn)==>mat gt ===	
# === Total Production Cost Excl Depre (Rp Mn)==>trans gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdxdeptrans[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdeptransgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeptransknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeptransknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdeptransknaopgt,$nf0)."</td>";	
# === end Total Production Cost Excl Depre (Rp Mn)==>trans gt ===	
# === Total Production Cost Excl Depre (Rp Mn)==>oth gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdxdepoth[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdepothgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepothknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepothknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdep[] class=tcostprdxdep[]  ".$style.">".numb_format($tcostprdxdepothknaopgt,$nf0)."</td>";	
# === end Total Production Cost Excl Depre (Rp Mn)==>oth gt ===	
# === Total Production Cost Excl Depre (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdxdepttl[] class=tcostprdxdep[] ".$style.">".numb_format($tcostprdxdepttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($tcostprdxdepttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tcostprdxdepttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tcostprdxdepttlknaopgt,$nf0)."</td>";	
# === end Total Production Cost Excl Depre (Rp Mn)==>ttl gt ===	

	
# === Depreciation (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=dep[] ".$style.">".numb_format($depttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($depttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($depttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($depttlknaopgt,$nf0)."</td>";	
# === end Depreciation (Rp Mn)==>ttl gt ===	

	
# === Total Production Cost (Rp Mn)==>lab gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdlab[] class=tcostprd[] ".$style.">".numb_format($tcostprdlabgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdlabknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdlabknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdlabknaopgt,$nf0)."</td>";	
# === end Total Production Cost (Rp Mn)==>lab gt ===	
# === Total Production Cost (Rp Mn)==>mat gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdmat[] class=tcostprd[] ".$style.">".numb_format($tcostprdmatgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdmatknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdmatknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdmatknaopgt,$nf0)."</td>";	
# === end Total Production Cost (Rp Mn)==>mat gt ===	
# === Total Production Cost (Rp Mn)==>trans gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdtrans[] class=tcostprd[] ".$style.">".numb_format($tcostprdtransgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdtransknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdtransknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdtransknaopgt,$nf0)."</td>";	
# === end Total Production Cost (Rp Mn)==>trans gt ===	
# === Total Production Cost (Rp Mn)==>oth gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdoth[] class=tcostprd[] ".$style.">".numb_format($tcostprdothgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdothknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdothknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprd[] class=tcostprd[]  ".$style.">".numb_format($tcostprdothknaopgt,$nf0)."</td>";	
# === end Total Production Cost (Rp Mn)==>oth gt ===	
# === Total Production Cost (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdttl[] class=tcostprd[] ".$style.">".numb_format($tcostprdttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($tcostprdttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tcostprdttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tcostprdttlknaopgt,$nf0)."</td>";	
# === end Total Production Cost (Rp Mn)==>ttl gt ===	
	
# === Total Revenue (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=rev[] ".$style.">".numb_format($revttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($revttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($revttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($revttlknaopgt,$nf0)."</td>";	
# === end Total Revenue (Rp Mn)==>ttl gt ===	
	
# === Total Cash Profit (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=cashprofit[] ".$style.">".numb_format($cashprofitttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($cashprofitttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($cashprofitttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($cashprofitttlknaopgt,$nf0)."</td>";	
# === end Total Cash Profit (Rp Mn)==>ttl gt ===	
	
# === Total Gross Profit (Rp Mn)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=grossprofit[] ".$style.">".numb_format($grossprofitttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($grossprofitttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($grossprofitttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($grossprofitttlknaopgt,$nf0)."</td>";	
# === end Total Gross Profit (Rp Mn)==>ttl gt ===	
	
# === Total Production Cost Excl Depre/Kg (Rp)==>lab gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdxdepperkglab[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkglabgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkglabknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkglabknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkglabknaopgt,$nf0)."</td>";	
# === end Total Production Cost Excl Depre/Kg (Rp)==>lab gt ===	
# === Total Production Cost Excl Depre/Kg (Rp)==>mat gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdxdepperkgmat[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgmatgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgmatknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgmatknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgmatknaopgt,$nf0)."</td>";	
# === end Total Production Cost Excl Depre/Kg (Rp)==>mat gt ===	
# === Total Production Cost Excl Depre/Kg (Rp)==>trans gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdxdepperkgtrans[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgtransgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgtransknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgtransknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgtransknaopgt,$nf0)."</td>";	
# === end Total Production Cost Excl Depre/Kg (Rp)==>trans gt ===	
# === Total Production Cost Excl Depre/Kg (Rp)==>oth gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdxdepperkgoth[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgothgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgothknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgothknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdxdepperkg[] class=tcostprdxdepperkg[]  ".$style.">".numb_format($tcostprdxdepperkgothknaopgt,$nf0)."</td>";	
# === end Total Production Cost Excl Depre/Kg (Rp)==>oth gt ===	
# === Total Production Cost Excl Depre/Kg (Rp)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdxdepperkgttl[] class=tcostprdxdepperkg[] ".$style.">".numb_format($tcostprdxdepperkgttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($tcostprdxdepperkgttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tcostprdxdepperkgttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tcostprdxdepperkgttlknaopgt,$nf0)."</td>";	
# === end Total Production Cost Excl Depre/Kg (Rp)==>ttl gt ===	
	
# === Total Production Cost/Kg (Rp)==>lab gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdperkglab[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkglabgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkglabknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkglabknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkglabknaopgt,$nf0)."</td>";	
# === end Total Production Cost/Kg (Rp)==>lab gt ===	
# === Total Production Cost/Kg (Rp)==>mat gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdperkgmat[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgmatgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgmatknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgmatknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgmatknaopgt,$nf0)."</td>";	
# === end Total Production Cost/Kg (Rp)==>mat gt ===	
# === Total Production Cost/Kg (Rp)==>trans gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdperkgtrans[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgtransgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgtransknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgtransknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgtransknaopgt,$nf0)."</td>";	
# === end Total Production Cost/Kg (Rp)==>trans gt ===	
# === Total Production Cost/Kg (Rp)==>oth gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdperkgoth[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgothgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgothknytdtygt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgothknlalugt,$nf0)."</td>";	
$tab.="<td align=right name=costtcostprdperkg[] class=tcostprdperkg[]  ".$style.">".numb_format($tcostprdperkgothknaopgt,$nf0)."</td>";	
# === end Total Production Cost/Kg (Rp)==>oth gt ===	
# === Total Production Cost/Kg (Rp)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=tcostprdperkgttl[] class=tcostprdperkg[] ".$style.">".numb_format($tcostprdperkgttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($tcostprdperkgttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tcostprdperkgttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($tcostprdperkgttlknaopgt,$nf0)."</td>";	
# === end Total Production Cost/Kg (Rp)==>ttl gt ===	
	
# === Revenue/Kg (Rp)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=revperkg[] ".$style.">".numb_format($revperkgttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($revperkgttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($revperkgttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($revperkgttlknaopgt,$nf0)."</td>";	
# === end Revenue/Kg (Rp)==>ttl gt ===	
	
# === Total Cash Profit/Kg (Rp)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=cashprofitperkg[] ".$style.">".numb_format($cashprofitperkgttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($cashprofitperkgttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($cashprofitperkgttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($cashprofitperkgttlknaopgt,$nf0)."</td>";	
# === end Total Cash Profit/Kg (Rp)==>ttl gt ===	
# === Gross Profit/Kg (Rp)==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=grossprofitperkg[] ".$style.">".numb_format($grossprofitperkgttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($grossprofitperkgttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($grossprofitperkgttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($grossprofitperkgttlknaopgt,$nf0)."</td>";	
# === end Gross Profit/Kg (Rp)==>ttl gt ===	
	
# === Gross Profit %/Kg==>ttl gt ===	
foreach($rangebln as $bln){	
	$tab.="<td align=right name=grossprofitpersen[] ".$style.">".numb_format($grossprofitpersenttlgt[$bln],$nf0)."</td>";
}	
$tab.="<td align=right >".numb_format($grossprofitpersenttlknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($grossprofitpersenttlknlalugt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($grossprofitpersenttlknaopgt,$nf0)."</td>";	
# === end Gross Profit %/Kg==>ttl gt ===	
	
# === Yield/Ha (Ton)==> gt ===	
$tab.="<td align=right >".numb_format($yieldhaknytdtygt,$nf2)."</td>";	
$tab.="<td align=right >".numb_format($yieldhaknlalugt,$nf2)."</td>";	
$tab.="<td align=right >".numb_format($yieldhaknaopgt,$nf2)."</td>";	
# === end Yield/Ha (Ton)==> gt ===	
	
# === Gross Profit/Ha (Rp Mn)==>ttl gt ===	
$tab.="<td align=right >".numb_format($grossprofitperhattlknytdtygt,$nf2)."</td>";	
$tab.="<td align=right >".numb_format($grossprofitperhattlknlalugt,$nf2)."</td>";	
$tab.="<td align=right >".numb_format($grossprofitperhattlknaopgt,$nf2)."</td>";	
# === end Gross Profit/Ha (Rp Mn)==>ttl gt ===	
	
# === Yield/Pkk (Kg)==> gt ===	
$tab.="<td align=right >".numb_format($yieldpkkknytdtygt,$nf0)."</td>";	
$tab.="<td align=right >".numb_format($yieldpkkknaopgt,$nf0)."</td>";	
# === end Yield/Pkk (Kg)==> gt ===	

	
# === Gross Profit/Pkk (Rp 000)==> gt ===	
$tab.="<td align=right >".numb_format($grossprofitpkkknytdtygt,$nf2)."</td>";	
$tab.="<td align=right >".numb_format($grossprofitpkkknlalugt,$nf2)."</td>";	
$tab.="<td align=right >".numb_format($grossprofitpkkknaopgt,$nf2)."</td>";	
# === end Gross Profit/Pkk (Rp 000)==> gt ===	

$tab.="</tr>";
$tab.="</tbody></table>";


#HARGA TBS
#harga jual tbs
$tab1.="<table>";
$tab1.="<tr><td valign=top>";

if($proses!='excel'){	
	$tab1.="<table class=sortable cellspacing=1>";
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
	$tab1.="<table class=sortable cellspacing=1>";
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
	$tab1.="<table class=sortable cellspacing=1>";
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
######PREVIEW
    case 'preview':
        echo $tab1.$tab;
        break;

######EXCEL	
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