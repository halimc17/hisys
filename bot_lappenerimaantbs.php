<?php
//if(count($_POST)==0){include('lib/nangkoelib.php');}
include('lib/zLib.php');
require_once('config/connection.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$folder = "imgbot/";
if (!file_exists($folder)){
	mkdir($folder, 0777, true);
}

if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}

$where="";
if($param['periode']!=''){
	$tglawal  = $param['periode']."-01";
	$tglakhir = tglakhir($tglawal);

	$tgl = $tglakhir;
	$where = "and kodeorganisasi='".$param['kodeorg']."'";
}else{	
	$tglawal = '2022-12-01';
	$tglawal  = date("Y-m")."-01";
	$tglakhir = tglakhir($tglawal);

	$tgl = date("Y-m-d");
}

$arrbi    = explode('-',$tglakhir); 
$tahun    = $arrbi[0]; 
$bulan    = $arrbi[1];

$arrHead = setheadreport('',$kodeorg);
$path=$arrHead['logo'];

$str = "SELECT * from " . $dbname . ".organisasi where tipe in ('KEBUN','AFDELING') ".$where."";
$res = fetchdata($str);
foreach($res as $bar){
	$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
	$listkode[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
	$tipeorg[$bar['kodeorganisasi']]=$bar['tipe'];
}

$str = "SELECT * from " . $dbname . ".setup_blok where luasareaproduktif>0 and statusblok='TM'";
$res = fetchdata($str);
foreach($res as $bar){
	# pencapaian jika header divisi maka isinya tt dan blok
	$listblok[substr($bar['kodeorg'],0,6)][$bar['tahuntanam']][$bar['kodeorg']]=$bar['kodeorg'];
	$luas[$bar['tahuntanam']][$bar['kodeorg']]+=$bar['luasareaproduktif'];
	$pokok[$bar['tahuntanam']][$bar['kodeorg']]+=$bar['jumlahpokok'];
	
	
	# pencapaian jika header kebun maka isinya divisi dan tt
	$listblok[substr($bar['kodeorg'],0,4)][substr($bar['kodeorg'],0,6)][$bar['tahuntanam']]=$bar['tahuntanam'];
	$luas[substr($bar['kodeorg'],0,6)][$bar['tahuntanam']]+=$bar['luasareaproduktif'];
	$pokok[substr($bar['kodeorg'],0,6)][$bar['tahuntanam']]+=$bar['jumlahpokok'];
	
}


$rangetgl = rangeTanggal($tglawal,$tglakhir);

$wh =" and substr(tanggal,1,10) between '".$tglawal."' and '".$tglakhir."'";	
$str = "SELECT substr(tanggal,1,10) as tanggal, sum(beratbersih) as kg, sum(jumlahtandan1) as jjg, kodeorg, divcode, intiplasma from " . $dbname . ".pabrik_timbangan where 1=1 ".$wh." and kodebarang='40000003' group by kodeorg, intiplasma, divcode, substr(tanggal,1,10) order by kodeorg desc, intiplasma asc, divcode asc";
$res = fetchdata($str);
$jumlah = count($res);
foreach($res as $bar){
	$actkgpks[$bar['kodeorg']][$bar['tanggal']]+=$bar['kg'];
	$actkgpks[$bar['divcode']][$bar['tanggal']]+=$bar['kg'];
	$actjjgpks[$bar['kodeorg']][$bar['tanggal']]+=$bar['jjg'];
	$actjjgpks[$bar['divcode']][$bar['tanggal']]+=$bar['jjg'];
}


$wh =" and substr(tanggal,1,10) between '".$tglawal."' and '".$tglakhir."'";	
$str = "SELECT substr(tanggal,1,10) as tanggal, sum(kgwb) as kg, sum(jjg) as jjg, substr(divisi,1,4) as kodeorg, divisi, blok from " . $dbname . ".kebun_spb_vw where 1=1 ".$wh." group by substr(divisi,1,4), blok, divisi, substr(tanggal,1,10)";
$res = fetchdata($str);
$jumlah = count($res);
foreach($res as $bar){
	$actkgspb[$bar['kodeorg']][$bar['tanggal']]+=$bar['kg'];
	$actkgspb[$bar['divisi']][$bar['tanggal']]+=$bar['kg'];
	$actjjgspb[$bar['kodeorg']][$bar['tanggal']]+=$bar['jjg'];
	$actjjgspb[$bar['divisi']][$bar['tanggal']]+=$bar['jjg'];
	
	# pencapaian jika header divisi maka isinya tt dan blok
	$listblok[substr($bar['blok'],0,6)][getBlok($bar['blok'],'tahuntanam')][$bar['blok']]=$bar['blok'];
	$actkgbi[getBlok($bar['blok'],'tahuntanam')][$bar['blok']]+=$bar['kg'];
	$actjjgbi[getBlok($bar['blok'],'tahuntanam')][$bar['blok']]+=$bar['jjg'];
	
	# pencapaian jika header kebun maka isinya divisi dan tt
	$listblok[substr($bar['blok'],0,4)][substr($bar['blok'],0,6)][getBlok($bar['blok'],'tahuntanam')]=getBlok($bar['blok'],'tahuntanam');
	$actkgbi[substr($bar['blok'],0,6)][getBlok($bar['blok'],'tahuntanam')]+=$bar['kg'];
	$actjjgbi[substr($bar['blok'],0,6)][getBlok($bar['blok'],'tahuntanam')]+=$bar['jjg'];
	
}

#khusus actual sd bulan ini
$wh =" and substr(tanggal,1,10) between '".substr($tglawal,0,4)."-01-01' and '".$tglakhir."'";	
$str = "SELECT sum(kgwb) as kg, sum(jjg) as jjg, blok from " . $dbname . ".kebun_spb_vw where 1=1 ".$wh." group by blok";
$res = fetchdata($str);
foreach($res as $bar){
	# pencapaian jika header divisi maka isinya tt dan blok
	$listblok[substr($bar['blok'],0,6)][getBlok($bar['blok'],'tahuntanam')][$bar['blok']]=$bar['blok'];
	$actkgsdbi[getBlok($bar['blok'],'tahuntanam')][$bar['blok']]+=$bar['kg'];
	$actjjgsdbi[getBlok($bar['blok'],'tahuntanam')][$bar['blok']]+=$bar['jjg'];
	
	# pencapaian jika header kebun maka isinya divisi dan tt
	$listblok[substr($bar['blok'],0,4)][substr($bar['blok'],0,6)][getBlok($bar['blok'],'tahuntanam')]=getBlok($bar['blok'],'tahuntanam');
	$actkgsdbi[substr($bar['blok'],0,6)][getBlok($bar['blok'],'tahuntanam')]+=$bar['kg'];
	$actjjgsdbi[substr($bar['blok'],0,6)][getBlok($bar['blok'],'tahuntanam')]+=$bar['jjg'];
}


$str = "SELECT * from " . $dbname . ".bgt_blok where tahunbudget='".substr($tglawal,0,4)."'";
$res = fetchdata($str);
foreach($res as $bar){
	$ttbgt[$bar['kodeblok']]=$bar['thntnm'];
}

$str = "SELECT * from " . $dbname . ".bgt_blok where tahunbudget='".substr($tglawal,0,4)."' and kodeblok not in (SELECT kodeorg from " . $dbname . ".setup_blok where luasareaproduktif='0')";
$res = fetchdata($str);
foreach($res as $bar){
	$luas[substr($bar['kodeblok'],0,6)][$bar['thntnm']]+=$bar['hathnini'];
	$pokok[substr($bar['kodeblok'],0,6)][$bar['thntnm']]+=$bar['pokokthnini'];
	$luas[$bar['thntnm']][$bar['kodeblok']]+=$bar['hathnini'];
	$pokok[$bar['thntnm']][$bar['kodeblok']]+=$bar['pokokthnini'];
}

$kgsdbi="(";
$jjgsdbi="(";
for($i=1;$i<=intval(substr($tglawal,5,2));$i++){
	if(intval(substr($tglawal,5,2))==$i){
		$kgsdbi.="kg".addZero($i,2);
		$jjgsdbi.="jjg".addZero($i,2);
		$kgbi="kg".addZero($i,2)." as kgbi,";
		$jjgbi="jjg".addZero($i,2)." as jjgbi,";
	}else{
		$kgsdbi.="kg".addZero($i,2)."+";
		$jjgsdbi.="jjg".addZero($i,2)."+";
	}
}
$kgsdbi.=") as kgsdbi,";
$jjgsdbi.=") as jjgsdbi,";

$str = "SELECT ".$jjgbi." ".$kgbi." ".$kgsdbi." ".$jjgsdbi." kodeunit,totalkg,totaljjg, substr(kodeblok,1,6) as divisi, kodeblok as blok from " . $dbname . ".bgt_produksi_kebun where tahunbudget='".substr($tglawal,0,4)."'";
$res = fetchdata($str);
$kgbi = $kgsdbi = $jjgbi = $jjgsdbi = [];
foreach($res as $bar){
	$kgbi[$bar['kodeunit']]+=$bar['kgbi'];
	$kgbi[$bar['divisi']]+=$bar['kgbi'];
	$kgsdbi[$bar['kodeunit']]+=$bar['kgsdbi'];
	$kgsdbi[$bar['divisi']]+=$bar['kgsdbi'];
	$jjgbi[$bar['kodeunit']]+=$bar['jjgbi'];
	$jjgbi[$bar['divisi']]+=$bar['jjgbi'];
	
	# pencapaian jika header divisi maka isinya tt dan blok
	$listblok[substr($bar['blok'],0,6)][$ttbgt[$bar['blok']]][$bar['blok']]=$bar['blok'];
	$blokbgtbi[$ttbgt[$bar['blok']]][$bar['blok']]+=$bar['kgbi'];
	$blokbgtsdbi[$ttbgt[$bar['blok']]][$bar['blok']]+=$bar['kgsdbi'];
	$blokbgtthn[$ttbgt[$bar['blok']]][$bar['blok']]+=$bar['totalkg'];
	
	$blokjjgbgtbi[$ttbgt[$bar['blok']]][$bar['blok']]+=$bar['jjgbi'];
	$blokjjgbgtsdbi[$ttbgt[$bar['blok']]][$bar['blok']]+=$bar['jjgsdbi'];
	$blokjjgbgtthn[$ttbgt[$bar['blok']]][$bar['blok']]+=$bar['totaljjg'];
	
	
	# pencapaian jika header kebun maka isinya divisi dan tt
	$listblok[substr($bar['blok'],0,4)][substr($bar['blok'],0,6)][$ttbgt[$bar['blok']]]=$ttbgt[$bar['blok']];
	$blokbgtbi[substr($bar['blok'],0,6)][$ttbgt[$bar['blok']]]+=$bar['kgbi'];
	$blokbgtsdbi[substr($bar['blok'],0,6)][$ttbgt[$bar['blok']]]+=$bar['kgsdbi'];
	$blokbgtthn[substr($bar['blok'],0,6)][$ttbgt[$bar['blok']]]+=$bar['totalkg'];
	
	$blokjjgbgtbi[substr($bar['blok'],0,6)][$ttbgt[$bar['blok']]]+=$bar['jjgbi'];
	$blokjjgbgtsdbi[substr($bar['blok'],0,6)][$ttbgt[$bar['blok']]]+=$bar['jjgsdbi'];
	$blokjjgbgtthn[substr($bar['blok'],0,6)][$ttbgt[$bar['blok']]]+=$bar['totaljjg'];
	
}

$e="(";
for($i=1;$i<=intval($bulan);$i++){
	$r="rp".addZero($i,2);$n="fis".addZero($i,2);
	if($i<intval($bulan)){$e.=$r."+";}else{$e.=$r;}
}
$e.=")";

$rpbgtbi=$rpbgtsdbi=$rpbgtthn=[];
#ini khusus budget kebun
$str = " select substr(noakun,1,3) as jobgroup, sum(".$e.") as sdbi,sum(rp".$bulan.") as bi,sum(rupiah) as setahun, kodeorg from ".$dbname.".bgt_budget_detail a where 1=1 and kodebudget!='UMUM' and tahunbudget = '".$tahun."' and substr(noakun,1,3) in ('611') and kodeorg like '".$param['kodeorg']."%' group by substr(noakun,1,3), kodeorg";
$res = fetchdata($str);
foreach($res as $bar){
	# pencapaian jika header divisi maka isinya tt dan blok
	@$rpbgtbi[$ttbgt[$bar['kodeorg']]][$bar['kodeorg']] += $bar['bi'];
	@$rpbgtsdbi[$ttbgt[$bar['kodeorg']]][$bar['kodeorg']] += $bar['sdbi'];
	@$rpbgtthn[$ttbgt[$bar['kodeorg']]][$bar['kodeorg']] += $bar['setahun'];
	
	# pencapaian jika header kebun maka isinya divisi dan tt
	@$rpbgtbi[substr($bar['kodeorg'],0,6)][$ttbgt[$bar['kodeorg']]] += $bar['bi'];
	@$rpbgtsdbi[substr($bar['kodeorg'],0,6)][$ttbgt[$bar['kodeorg']]] += $bar['sdbi'];
	@$rpbgtthn[substr($bar['kodeorg'],0,6)][$ttbgt[$bar['kodeorg']]] += $bar['setahun'];
	
}

$str = "select sum(jumlah) as jumlah, kodeblok, periode  from " . $dbname . ".keu_jurnaldt_vw a  where 1=1 and substr(noakun,1,3) in ('611') and 
periode like '".$tahun."%' and kodeblok like '".$param['kodeorg']."%' group by kodeblok, periode";
$res = fetchdata($str);
foreach($res as $bar){
	# pencapaian jika header divisi maka isinya tt dan blok
	$listblok[substr($bar['kodeblok'],0,6)][getBlok($bar['kodeblok'],'tahuntanam')][$bar['kodeblok']]=$bar['kodeblok'];		
	if($bar['periode']==substr($tglakhir,0,7)){			
		@$rpactbi[getBlok($bar['kodeblok'],'tahuntanam')][$bar['kodeblok']]+=$bar['jumlah'];
	}
	if($bar['periode']<=substr($tglakhir,0,7)){
		@$rpactsdbi[getBlok($bar['kodeblok'],'tahuntanam')][$bar['kodeblok']]+=$bar['jumlah'];
	}
	
	
	# pencapaian jika header kebun maka isinya divisi dan tt
	$listblok[substr($bar['kodeblok'],0,4)][substr($bar['kodeblok'],0,6)][getBlok($bar['kodeblok'],'tahuntanam')]=getBlok($bar['kodeblok'],'tahuntanam');
	if($bar['periode']==substr($tglakhir,0,7)){			
		@$rpactbi[substr($bar['kodeblok'],0,6)][getBlok($bar['kodeblok'],'tahuntanam')]+=$bar['jumlah'];
	}
	if($bar['periode']<=substr($tglakhir,0,7)){
		@$rpactsdbi[substr($bar['kodeblok'],0,6)][getBlok($bar['kodeblok'],'tahuntanam')]+=$bar['jumlah'];
	}
}


foreach($listkode as $kodeorg => $namaorg){
	foreach($rangetgl as $tanggal){
		$harilibur = getjenisharikerja(substr($kodeorg,0,4),$tanggal);
		if($harilibur=='LIBUR'){
		}else{			
			$hke[$kodeorg]+=1;
		}
	}
	$tab="
	<table cellspacing=0 border=0 width=100% align=center style=\"font-family:tahoma,Arial Narrow;font-size:10px;\">
		<tr>
			<td rowspan=3 valign=center style='font-weight:bold;width:100px'><img src='".$path."' height='60' /></td>
		</tr>
	</table>
	<table cellspacing=0 border=0 width=100% style='text-align:center' style='font-family:tahoma,Arial Narrow;font-size:14px;'>
		<tr style='font-family:tahoma,Arial Narrow;font-size:16px;'>
			<td style=font-weight:bold;><b>PRODUKSI TBS HARIAN</b></td>
		</tr>
	</table>
	<table cellspacing=0 border=0 style='text-align:center' style='font-family:tahoma,Arial Narrow;font-size:14px;'>
		<tr style='font-family:tahoma,Arial Narrow;font-size:12px;'>
			<td align=left nowrap>".ucfirst(strtolower(getNamaOrg($kodeorg,'tipe')))."</td><td>:</td><td align=left nowrap><b>".$namaorg."</b></td>
		</tr>
		<tr style='font-family:tahoma,Arial Narrow;font-size:12px;'>
			<td align=left nowrap>Periode</td><td>:</td><td align=left nowrap>".numToMonth($bulan,'E','long').' '.$tahun."</td>
			<td align=left nowrap>Budget BI</td><td>:</td><td align=left nowrap><b>".hidezerodecimal($kgbi[$kodeorg],2)."</b> Kg | <b>".hidezerodecimal($jjgbi[$kodeorg],2)."</b> Jjg | BJR <b>".@hidezerodecimal($kgbi[$kodeorg]/$jjgbi[$kodeorg],2)."</b> Kg</td>
		</tr>
		<tr style='font-family:tahoma,Arial Narrow;font-size:12px;'>
			<td align=left nowrap>Tanggal</td><td>:</td><td align=left nowrap>".tanggalbulan($tgl)."</td>
			<td align=left nowrap>Budget HI</td><td>:</td><td align=left nowrap>".(getjenisharikerja(substr($kodeorg,0,4),$tgl)!='LIBUR'?hidezerodecimal($kgbi[$kodeorg]/$hke[$kodeorg],2):'0')." Kg (HKE = <b>".$hke[$kodeorg]."</b>)</td>
		</tr>
	</table>
	";
			
	$tab.="<table id=pvtTable cellpadding=5 cellspacing=0 border=1 width=100% class='sortable' style='font-family:tahoma,Arial Narrow;font-size:11px;'>
			<thead>
				<tr class=rowheader style=height:25px>
					<th rowspan=3 align=center>Tanggal</th>
					<th rowspan=3 align=center>Hari</th>
					<th colspan=2 align=center>Budget</th>
					<th colspan=6 align=center>Actual</th>
					<th colspan=3 align=center>Varian</th>
				</tr>
				<tr class=rowheader style=height:25px>
					<th rowspan=2 align=center>Today</th>
					<th rowspan=2 align=center>Todate</th>
					<th colspan=3 align=center>Timbangan Pabrik</th>
					<th colspan=3 align=center>SPB Kebun</th>
					<th rowspan=2 align=center>Today</th>
					<th rowspan=2 align=center>Todate</th>
					<th rowspan=2 align=center>(%)</th>
				</tr>
				<tr class=rowheader style=height:25px>
					<th align=center>Today</th>
					<th align=center>Todate</th>
					<th align=center>BJR</th>
					<th align=center>Today</th>
					<th align=center>Todate</th>
					<th align=center>BJR</th>
				</tr>
			</thead>
			<tbody>";
	
	foreach($rangetgl as $tanggal){
		$day = date('D', strtotime($tanggal));
		$harilibur = getjenisharikerja(substr($kodeorg,0,4),$tanggal);
		if($harilibur=='LIBUR'){
			if($tanggal==$tgl){		
				$tab.="<tr class='rowcontent' style=background-color:#cffae7;>";
			}else{
				$tab.="<tr class='rowcontent' style=background-color:#fcbdc8;>";
			}
		}else{
			if($tanggal==$tgl){		
				$tab.="<tr class='rowcontent' style=background-color:#cffae7;>";
			}else{
				$tab.="<tr class='rowcontent'>";
			}
		}
			
		$tab.="<td align=center>".tglnormal($tanggal)."</td>";
		$tab.="<td align=center>".hari($tanggal)."</td>";
				
		#budget harian
		if($harilibur=='LIBUR'){
			$bgtharian[$kodeorg][$tanggal]=0;
		}else{
			$bgtharian[$kodeorg][$tanggal]=$kgbi[$kodeorg]/$hke[$kodeorg];
			$subsdhi[$kodeorg]+=$kgbi[$kodeorg]/$hke[$kodeorg];
		}		
		$tab.="<td align=right>".hidezerodecimal($bgtharian[$kodeorg][$tanggal])."</td>";
		$tab.="<td align=right>".hidezerodecimal($subsdhi[$kodeorg],0)."</td>";
		
		#aktual pks
		$tab.="<td align=right>".hidezerodecimal($actkgpks[$kodeorg][$tanggal],0)."</td>";
		$subkgpkssdhi[$kodeorg]+=$actkgpks[$kodeorg][$tanggal];
		$tab.="<td align=right>".hidezerodecimal($subkgpkssdhi[$kodeorg],0)."</td>";
		
		$subjjgpkssdhi[$kodeorg]+=$actjjgpks[$kodeorg][$tanggal];
		@$bjrpkssdhi[$kodeorg]=$subkgpkssdhi[$kodeorg]/$subjjgpkssdhi[$kodeorg];
		$color="";
		if(@$bjrpkssdhi[$kodeorg]<@$kgbi[$kodeorg]/$jjgbi[$kodeorg]){
			$color="style=color:red;";
		}
		$tab.="<td align=right ".$color.">".hidezerodecimal(@fixnan($bjrpkssdhi[$kodeorg]),2)."</td>";
		
		#aktual spb
		$color="";
		if($actkgspb[$kodeorg][$tanggal]!=$actkgpks[$kodeorg][$tanggal]){
			$color="style=color:red;";
		}
		$tab.="<td align=right ".$color.">".hidezerodecimal($actkgspb[$kodeorg][$tanggal],0)."</td>";
		$subkgspbsdhi[$kodeorg]+=$actkgspb[$kodeorg][$tanggal];
		$color="";
		if($subkgspbsdhi[$kodeorg]!=$subkgpkssdhi[$kodeorg]){
			$color="style=color:red;";
		}
		$tab.="<td align=right ".$color.">".hidezerodecimal($subkgspbsdhi[$kodeorg],0)."</td>";
		
		$subjjgspbsdhi[$kodeorg]+=$actjjgspb[$kodeorg][$tanggal];
		@$bjrspbsdhi[$kodeorg]=$subkgspbsdhi[$kodeorg]/$subjjgspbsdhi[$kodeorg];
		$color="";
		if($bjrspbsdhi[$kodeorg]<@$kgbi[$kodeorg]/$jjgbi[$kodeorg]){
			$color="style=color:red;";
		}
		$tab.="<td align=right ".$color.">".hidezerodecimal(@fixnan($bjrspbsdhi[$kodeorg]),2)."</td>";
		
		
		#varian
		$varian[$kodeorg][$tanggal]=$actkgspb[$kodeorg][$tanggal]-$bgtharian[$kodeorg][$tanggal];
		if($varian[$kodeorg][$tanggal]<0){
			$color="style=color:red;";
		}else{
			$color="style=color:blue;";
		}
		$tab.="<td align=right ".$color.">".hidezerodecimal(@fixnan($varian[$kodeorg][$tanggal]))."</td>";
		
		$variansdhi[$kodeorg]=$subkgspbsdhi[$kodeorg]-$subsdhi[$kodeorg];
		if($variansdhi[$kodeorg]<0){
			$color="style=color:red;";
		}else{
			$color="style=color:blue;";
		}
		$tab.="<td align=right ".$color.">".hidezerodecimal(@fixnan($variansdhi[$kodeorg]))."</td>";
		$tab.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($variansdhi[$kodeorg]/$subsdhi[$kodeorg]*100),2)."</td>";
	}
	
	$tab.="</tbody></table>";
	
	$tab1="<div style='page-break-before: always;'></div>";
	$tab1.="
		<table cellspacing=0 border=0 width=100% align=center style=\"font-family:tahoma,Arial Narrow;font-size:10px;\">
			<tr>
				<td rowspan=3 valign=center style='font-weight:bold;width:100px'><img src='".$path."' height='60' /></td>
			</tr>
		</table>
		<table cellspacing=0 border=0 width=100% style='text-align:center' style='font-family:tahoma,Arial Narrow;font-size:14px;'>
			<tr style='font-family:tahoma,Arial Narrow;font-size:16px;'>
				<td style=font-weight:bold;><b>PRODUKSI VS BUDGET</b></td>
			</tr>
		</table>
		<table cellspacing=0 border=0 style='text-align:center' style='font-family:tahoma,Arial Narrow;font-size:14px;'>
			<tr style='font-family:tahoma,Arial Narrow;font-size:12px;'>
				<td align=left nowrap>".ucfirst(strtolower(getNamaOrg($kodeorg,'tipe')))."</td><td>:</td><td align=left nowrap><b>".$namaorg."</b></td>
			</tr>
			<tr style='font-family:tahoma,Arial Narrow;font-size:12px;'>
				<td align=left nowrap>Periode</td><td>:</td><td align=left nowrap>".numToMonth($bulan,'E','long').' '.$tahun."</td>
			</tr>
			<tr style='font-family:tahoma,Arial Narrow;font-size:12px;'>
				<td align=left nowrap>Tanggal</td><td>:</td><td align=left nowrap>".tanggalbulan($tglawal)." - ".tanggalbulan($tglakhir)."</td>
			</tr>
		</table>
		";
	$tab1.="<table id=pvtTable cellpadding=5 cellspacing=0 border=1 width=100%  class='sortable' style='font-family:tahoma,Arial Narrow;font-size:11px;'>
		<thead>
			<tr class=rowheader style=height:25px>";
				if($tipeorg[$kodeorg]=='AFDELING'){
					$tab1.="<th rowspan=3 align=center>TT</th>";
					$tab1.="<th rowspan=3 align=center>Blok</th>";
				}else{
					$tab1.="<th rowspan=3 align=center>Divisi</th>";
					$tab1.="<th rowspan=3 align=center>TT</th>";
				}	
				$tab1.="<th rowspan=3 align=center>Luas</th>
				<th rowspan=3 align=center>Pokok</th>
				<th rowspan=3 align=center>SPH</th>
				<th colspan=7 align=center>Budget</th>
				<th colspan=6 align=center>Actual</th>
				<th colspan=3 align=center>Varian (%)</th>
			</tr>
			<tr class=rowheader style=height:25px>
				<th align=center colspan=2>BI</th>
				<th align=center colspan=2>SDBI</th>
				<th align=center colspan=3>Setahun</th>
				<th align=center colspan=3>BI</th>
				<th align=center colspan=3>SDBI</th>
				<th align=center rowspan=2>BI</th>
				<th align=center rowspan=2>SDBI</th>
				<th align=center rowspan=2>Setahun</th>
			</tr>
			<tr class=rowheader style=height:25px>
				<th align=center>Jjg</th>
				<th align=center>Kg</th>
				<th align=center>Jjg</th>
				<th align=center>Kg</th>
				<th align=center>Jjg</th>
				<th align=center>Kg</th>
				<th align=center>BJR</th>
				<th align=center>Jjg</th>
				<th align=center>Kg</th>
				<th align=center>BJR</th>
				<th align=center>Jjg</th>
				<th align=center>Kg</th>
				<th align=center>BJR</th>
			</tr>
		</thead>
	<tbody>";
	$stluas=$stpokok=$stblokjjgbgtbi=$stblokbgtbi=$stblokjjgbgtsdbi=$stblokbgtsdbi=$stblokjjgbgtthn=$stblokbgtthn=$stactjjgbi=$stactkgbi=$stactjjgsdbi=$stactkgsdbi=[];
	$gtluas=$gtpokok=$gtblokjjgbgtbi=$gtblokbgtbi=$gtblokjjgbgtsdbi=$gtblokbgtsdbi=$gtblokjjgbgtthn=$gtblokbgtthn=$gtactjjgbi=$gtactkgbi=$gtactjjgsdbi=$gtactkgsdbi=0;
			
	foreach($listblok[$kodeorg] as $tt => $v1){
		$tab1.="<tr class='rowcontent'>";
		$tab1.="<td align=left colspan=21>".getNamaOrg($tt)."</td>";
		$tab1.="</tr>";
		$no=0;
		foreach($v1 as $blok){
			$no++;
			$tab1.="<tr class='rowcontent'>";
			$tab1.="<td align=right>".$no."</td>";
			$tab1.="<td align=center>".getNamaOrg($blok)."</td>";
			$tab1.="<td align=right>".$luas[$tt][$blok]."</td>";
			$tab1.="<td align=right>".hidezerodecimal($pokok[$tt][$blok])."</td>";
			$tab1.="<td align=right>".hidezerodecimal(@fixnan($pokok[$tt][$blok]/$luas[$tt][$blok]))."</td>";
			$tab1.="<td align=right>".hidezerodecimal($blokjjgbgtbi[$tt][$blok])."</td>";
			$tab1.="<td align=right>".hidezerodecimal($blokbgtbi[$tt][$blok])."</td>";
			$tab1.="<td align=right>".hidezerodecimal($blokjjgbgtsdbi[$tt][$blok])."</td>";
			$tab1.="<td align=right>".hidezerodecimal($blokbgtsdbi[$tt][$blok])."</td>";
			$tab1.="<td align=right>".hidezerodecimal($blokjjgbgtthn[$tt][$blok])."</td>";
			$tab1.="<td align=right>".hidezerodecimal($blokbgtthn[$tt][$blok])."</td>";
			$bjrbgtthn[$tt][$blok]=@fixnan($blokbgtthn[$tt][$blok]/$blokjjgbgtthn[$tt][$blok]);
			$tab1.="<td align=right>".hidezerodecimal($bjrbgtthn[$tt][$blok],2)."</td>";
			
			$tab1.="<td align=right>".hidezerodecimal($actjjgbi[$tt][$blok])."</td>";
			$tab1.="<td align=right>".hidezerodecimal($actkgbi[$tt][$blok])."</td>";
			
			$bjractbi[$tt][$blok]=@fixnan($actkgbi[$tt][$blok]/$actjjgbi[$tt][$blok]);
			if($bjractbi[$tt][$blok]<$bjrbgtthn[$tt][$blok]){
				$color="style=color:red;font-weight:normal";
			}else{
				$color="";
			}
			$tab1.="<td align=right ".$color.">".hidezerodecimal($bjractbi[$tt][$blok],2)."</td>";
			$tab1.="<td align=right>".hidezerodecimal($actjjgsdbi[$tt][$blok])."</td>";
			$tab1.="<td align=right>".hidezerodecimal($actkgsdbi[$tt][$blok])."</td>";
			
			$bjractsdbi[$tt][$blok]=@fixnan($actkgsdbi[$tt][$blok]/$actjjgsdbi[$tt][$blok]);
			if($bjractsdbi[$tt][$blok]<$bjrbgtthn[$tt][$blok]){
				$color="style=color:red;font-weight:normal";
			}else{
				$color="";
			}
			$tab1.="<td align=right ".$color.">".hidezerodecimal($bjractsdbi[$tt][$blok],2)."</td>";
			
			
			$varkgbi[$tt][$blok]=$actkgbi[$tt][$blok]-$blokbgtbi[$tt][$blok];
			if($varkgbi[$tt][$blok]<0){
				$color="style=color:red;font-weight:normal";
			}else{
				$color="style=color:blue;";
			}
			//$tab1.="<td align=right ".$color.">".hidezerodecimal($varkgbi[$tt][$blok])."</td>";
			$tab1.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($varkgbi[$tt][$blok]/$blokbgtbi[$tt][$blok]*100),2)."</td>";
			
			$varkgsdbi[$tt][$blok]=$actkgsdbi[$tt][$blok]-$blokbgtsdbi[$tt][$blok];
			if($varkgsdbi[$tt][$blok]<0){
				$color="style=color:red;font-weight:normal";
			}else{
				$color="style=color:blue;";
			}
			// $tab1.="<td align=right ".$color.">".hidezerodecimal($varkgsdbi[$tt][$blok])."</td>";
			$tab1.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($varkgsdbi[$tt][$blok]/$blokbgtsdbi[$tt][$blok]*100),2)."</td>";
			
			$varkgthn[$tt][$blok]=$actkgsdbi[$tt][$blok]-$blokbgtthn[$tt][$blok];
			if($varkgthn[$tt][$blok]<0){
				$color="style=color:red;font-weight:normal";
			}else{
				$color="style=color:blue;";
			}
			// $tab1.="<td align=right ".$color.">".hidezerodecimal($varkgthn[$tt][$blok])."</td>";
			$tab1.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($varkgthn[$tt][$blok]/$blokbgtthn[$tt][$blok]*100),2)."</td>";
			$tab1.="</tr>";
			
			$stluas[$tt]+=$luas[$tt][$blok];
			$stpokok[$tt]+=$pokok[$tt][$blok];
			$stblokjjgbgtbi[$tt]+=$blokjjgbgtbi[$tt][$blok];
			$stblokbgtbi[$tt]+=$blokbgtbi[$tt][$blok];
			$stblokjjgbgtsdbi[$tt]+=$blokjjgbgtsdbi[$tt][$blok];
			$stblokbgtsdbi[$tt]+=$blokbgtsdbi[$tt][$blok];
			$stblokjjgbgtthn[$tt]+=$blokjjgbgtthn[$tt][$blok];
			$stblokbgtthn[$tt]+=$blokbgtthn[$tt][$blok];
			$stactjjgbi[$tt]+=$actjjgbi[$tt][$blok];
			$stactkgbi[$tt]+=$actkgbi[$tt][$blok];
			$stactjjgsdbi[$tt]+=$actjjgsdbi[$tt][$blok];
			$stactkgsdbi[$tt]+=$actkgsdbi[$tt][$blok];
			
			$gtluas+=$luas[$tt][$blok];
			$gtpokok+=$pokok[$tt][$blok];
			$gtblokjjgbgtbi+=$blokjjgbgtbi[$tt][$blok];
			$gtblokbgtbi+=$blokbgtbi[$tt][$blok];
			$gtblokjjgbgtsdbi+=$blokjjgbgtsdbi[$tt][$blok];
			$gtblokbgtsdbi+=$blokbgtsdbi[$tt][$blok];
			$gtblokjjgbgtthn+=$blokjjgbgtthn[$tt][$blok];
			$gtblokbgtthn+=$blokbgtthn[$tt][$blok];
			$gtactjjgbi+=$actjjgbi[$tt][$blok];
			$gtactkgbi+=$actkgbi[$tt][$blok];
			$gtactjjgsdbi+=$actjjgsdbi[$tt][$blok];
			$gtactkgsdbi+=$actkgsdbi[$tt][$blok];
		}
		$tab1.="<tr class='rowcontent' style=background-color:#dcfce8>";
		$tab1.="<td colspan=2>Sub Total</td>";
		$tab1.="<td align=right>".$stluas[$tt]."</td>";
		$tab1.="<td align=right>".hidezerodecimal($stpokok[$tt])."</td>";
		$tab1.="<td align=right>".hidezerodecimal(@fixnan($stpokok[$tt]/$stluas[$tt]))."</td>";
		$tab1.="<td align=right>".hidezerodecimal($stblokjjgbgtbi[$tt])."</td>";
		$tab1.="<td align=right>".hidezerodecimal($stblokbgtbi[$tt])."</td>";
		$tab1.="<td align=right>".hidezerodecimal($stblokjjgbgtsdbi[$tt])."</td>";
		$tab1.="<td align=right>".hidezerodecimal($stblokbgtsdbi[$tt])."</td>";
		$tab1.="<td align=right>".hidezerodecimal($stblokjjgbgtthn[$tt])."</td>";
		$tab1.="<td align=right>".hidezerodecimal($stblokbgtthn[$tt])."</td>";
		
		$stbjrbgtthn[$tt]=@fixnan($stblokbgtthn[$tt]/$stblokjjgbgtthn[$tt]);
		$tab1.="<td align=right>".hidezerodecimal($stbjrbgtthn[$tt],2)."</td>";
			
		$tab1.="<td align=right>".hidezerodecimal($stactjjgbi[$tt])."</td>";
		$tab1.="<td align=right>".hidezerodecimal($stactkgbi[$tt])."</td>";
		$stbjractbi[$tt]=@fixnan($stactkgbi[$tt]/$stactjjgbi[$tt]);
		$tab1.="<td align=right>".hidezerodecimal($stbjractbi[$tt],2)."</td>";
		
		$tab1.="<td align=right>".hidezerodecimal($stactjjgsdbi[$tt])."</td>";
		$tab1.="<td align=right>".hidezerodecimal($stactkgsdbi[$tt])."</td>";
		$stbjractsdbi[$tt]=@fixnan($stactkgsdbi[$tt]/$stactjjgsdbi[$tt]);
		$tab1.="<td align=right>".hidezerodecimal($stbjractsdbi[$tt],2)."</td>";
		
		$stvarkgbi[$tt]=$stactkgbi[$tt]-$stblokbgtbi[$tt];
		if($stvarkgbi[$tt]<0){
			$color="style=color:red;font-weight:normal";
		}else{
			$color="style=color:blue;";
		}
		//$tab1.="<td align=right ".$color.">".hidezerodecimal($stvarkgbi[$tt])."</td>";
		$tab1.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($stvarkgbi[$tt]/$stblokbgtbi[$tt]*100),2)."</td>";
		
		$stvarkgsdbi[$tt]=$stactkgsdbi[$tt]-$stblokbgtsdbi[$tt];
		if($stvarkgsdbi[$tt]<0){
			$color="style=color:red;font-weight:normal";
		}else{
			$color="style=color:blue;";
		}
		// $tab1.="<td align=right ".$color.">".hidezerodecimal($stvarkgsdbi[$tt])."</td>";
		$tab1.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($stvarkgsdbi[$tt]/$stblokbgtsdbi[$tt]*100),2)."</td>";
		
		$stvarkgthn[$tt]=$stactkgsdbi[$tt]-$stblokbgtthn[$tt];
		if($stvarkgthn[$tt]<0){
			$color="style=color:red;font-weight:normal";
		}else{
			$color="style=color:blue;";
		}
		// $tab1.="<td align=right ".$color.">".hidezerodecimal($stvarkgthn[$tt])."</td>";
		$tab1.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($stvarkgthn[$tt]/$stblokbgtthn[$tt]*100),2)."</td>";
		$tab1.="</tr>";
	}
	$tab1.="<tr class='rowcontent'>";
	$tab1.="<td colspan=21></td>";
	$tab1.="</tr>";
	$tab1.="<tr class='rowcontent' style=background-color:#dcfce8>";
	$tab1.="<td colspan=2>Grand Total</td>";
	$tab1.="<td align=right>".$gtluas."</td>";
	$tab1.="<td align=right>".hidezerodecimal($gtpokok)."</td>";
	$tab1.="<td align=right>".hidezerodecimal(@fixnan($gtpokok/$gtluas))."</td>";
	$tab1.="<td align=right>".hidezerodecimal($gtblokjjgbgtbi)."</td>";
	$tab1.="<td align=right>".hidezerodecimal($gtblokbgtbi)."</td>";
	$tab1.="<td align=right>".hidezerodecimal($gtblokjjgbgtsdbi)."</td>";
	$tab1.="<td align=right>".hidezerodecimal($gtblokbgtsdbi)."</td>";
	$tab1.="<td align=right>".hidezerodecimal($gtblokjjgbgtthn)."</td>";
	$tab1.="<td align=right>".hidezerodecimal($gtblokbgtthn)."</td>";
	
	$gtbjrbgtthn=@fixnan($gtblokbgtthn/$gtblokjjgbgtthn);
	$tab1.="<td align=right>".hidezerodecimal($gtbjrbgtthn,2)."</td>";
		
	$tab1.="<td align=right>".hidezerodecimal($gtactjjgbi)."</td>";
	$tab1.="<td align=right>".hidezerodecimal($gtactkgbi)."</td>";
	$gtbjractbi=@fixnan($gtactkgbi/$gtactjjgbi);
	$tab1.="<td align=right>".hidezerodecimal($gtbjractbi,2)."</td>";
	
	$tab1.="<td align=right>".hidezerodecimal($gtactjjgsdbi)."</td>";
	$tab1.="<td align=right>".hidezerodecimal($gtactkgsdbi)."</td>";
	$gtbjractsdbi=@fixnan($gtactkgsdbi/$gtactjjgsdbi);
	$tab1.="<td align=right>".hidezerodecimal($gtbjractsdbi,2)."</td>";
	
	$gtvarkgbi=$gtactkgbi-$gtblokbgtbi;
	if($gtvarkgbi<0){
		$color="style=color:red;font-weight:normal";
	}else{
		$color="style=color:blue;";
	}
	//$tab1.="<td align=right ".$color.">".hidezerodecimal($gtvarkgbi)."</td>";
	$tab1.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($gtvarkgbi/$gtblokbgtbi*100),2)."</td>";
	
	$gtvarkgsdbi=$gtactkgsdbi-$gtblokbgtsdbi;
	if($gtvarkgsdbi<0){
		$color="style=color:red;font-weight:normal";
	}else{
		$color="style=color:blue;";
	}
	// $tab1.="<td align=right ".$color.">".hidezerodecimal($gtvarkgsdbi)."</td>";
	$tab1.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($gtvarkgsdbi/$gtblokbgtsdbi*100),2)."</td>";
	
	$gtvarkgthn=$gtactkgsdbi-$gtblokbgtthn;
	if($gtvarkgthn<0){
		$color="style=color:red;font-weight:normal";
	}else{
		$color="style=color:blue;";
	}
	// $tab1.="<td align=right ".$color.">".hidezerodecimal($gtvarkgthn)."</td>";
	$tab1.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($gtvarkgthn/$gtblokbgtthn*100),2)."</td>";
	$tab1.="</tr>";
	$tab1.="</tbody></table>";
	
	$tab2="<div style='page-break-before: always;'></div>";
	$tab2.="
		<table cellspacing=0 border=0 width=100% align=center style=\"font-family:tahoma,Arial Narrow;font-size:10px;\">
			<tr>
				<td rowspan=3 valign=center style='font-weight:bold;width:100px'><img src='".$path."' height='60' /></td>
			</tr>
		</table>
		<table cellspacing=0 border=0 width=100% style='text-align:center' style='font-family:tahoma,Arial Narrow;font-size:13px;'>
			<tr style='font-family:tahoma,Arial Narrow;font-size:16px;'>
				<td style=font-weight:bold;><b>BIAYA PRODUKSI (RP/KG)</b></td>
			</tr>
			<tr style='font-family:tahoma,Arial Narrow;font-size:12px;'>
				<td style=font-style:italic;>Biaya hanya biaya panen dan pengangkutan<br>(Nomor Akun 611xxxx)</td>
			</tr>
		</table>
		<table cellspacing=0 border=0 style='text-align:center' style='font-family:tahoma,Arial Narrow;font-size:13px;'>
			<tr style='font-family:tahoma,Arial Narrow;font-size:12px;'>
				<td align=left nowrap>".ucfirst(strtolower(getNamaOrg($kodeorg,'tipe')))."</td><td>:</td><td align=left nowrap><b>".$namaorg."</b></td>
			</tr>
			<tr style='font-family:tahoma,Arial Narrow;font-size:12px;'>
				<td align=left nowrap>Periode</td><td>:</td><td align=left nowrap>".numToMonth($bulan,'E','long').' '.$tahun."</td>
			</tr>
			<tr style='font-family:tahoma,Arial Narrow;font-size:12px;'>
				<td align=left nowrap>Tanggal</td><td>:</td><td align=left nowrap>".tanggalbulan($tglawal)." - ".tanggalbulan($tglakhir)."</td>
			</tr>
		</table>
		";
	$tab2.="<table id=pvtTable cellpadding=5 cellspacing=0 border=1 width=100%  class='sortable' style='font-family:tahoma,Arial Narrow;font-size:10px;'>
		<thead>
			<tr class=rowheader style=height:25px>";
				if($tipeorg[$kodeorg]=='AFDELING'){
					$tab2.="<th rowspan=3 align=center>TT</th>";
					$tab2.="<th rowspan=3 align=center>Blok</th>";
				}else{
					$tab2.="<th rowspan=3 align=center>Divisi</th>";
					$tab2.="<th rowspan=3 align=center>TT</th>";
				}	
				$tab2.="<th rowspan=3 align=center>Luas</th>
				<th rowspan=3 align=center>Pokok</th>
				<th rowspan=3 align=center>SPH</th>
				<th colspan=6 align=center>Budget</th>
				<th colspan=4 align=center>Actual</th>
				<th colspan=3 align=center>Varian (%)</th>
			</tr>
			<tr class=rowheader style=height:25px>
				<th align=center colspan=2>BI</th>
				<th align=center colspan=2>SDBI</th>
				<th align=center colspan=2>Setahun</th>
				<th align=center colspan=2>BI</th>
				<th align=center colspan=2>SDBI</th>
				<th align=center rowspan=2>BI</th>
				<th align=center rowspan=2>SDBI</th>
				<th align=center rowspan=2>Setahun</th>
			</tr>
			<tr class=rowheader style=height:25px>
				<th align=center>Rupiah</th>
				<th align=center>Rp/Kg</th>
				<th align=center>Rupiah</th>
				<th align=center>Rp/Kg</th>
				<th align=center>Rupiah</th>
				<th align=center>Rp/Kg</th>
				<th align=center>Rupiah</th>
				<th align=center>Rp/Kg</th>
				<th align=center>Rupiah</th>
				<th align=center>Rp/Kg</th>
			</tr>
		</thead>
	<tbody>";
	
	
	
	// echo"<pre>";
	// print_r($listblok);
	
	$stluas=$stpokok=$stblokjjgbgtbi=$stblokbgtbi=$stblokjjgbgtsdbi=$stblokbgtsdbi=$stblokjjgbgtthn=$stblokbgtthn=$stactjjgbi=$stactkgbi=$stactjjgsdbi=$stactkgsdbi=[];
	$gtluas=$gtpokok=$gtblokjjgbgtbi=$gtblokbgtbi=$gtblokjjgbgtsdbi=$gtblokbgtsdbi=$gtblokjjgbgtthn=$gtblokbgtthn=$gtactjjgbi=$gtactkgbi=$gtactjjgsdbi=$gtactkgsdbi=0;
			
	foreach($listblok[$kodeorg] as $tt => $v1){
		$tab2.="<tr class='rowcontent'>";
		$tab2.="<td align=left colspan=18>".getNamaOrg($tt)."</td>";
		$tab2.="</tr>";
		$no=0;
		foreach($v1 as $blok){
			$no++;
			$tab2.="<tr class='rowcontent'>";
			$tab2.="<td align=right>".$no."</td>";
			$tab2.="<td align=center>".getNamaOrg($blok)."</td>";
			$tab2.="<td align=right>".$luas[$tt][$blok]."</td>";
			$tab2.="<td align=right>".hidezerodecimal($pokok[$tt][$blok])."</td>";
			$tab2.="<td align=right>".hidezerodecimal(@fixnan($pokok[$tt][$blok]/$luas[$tt][$blok]))."</td>";
			$tab2.="<td align=right>".@hidezerodecimal($rpbgtbi[$tt][$blok])."</td>";
			$tab2.="<td align=right>".@hidezerodecimal(fixnan($rpbgtbi[$tt][$blok]/$blokbgtbi[$tt][$blok]))."</td>";
			$tab2.="<td align=right>".@hidezerodecimal($rpbgtsdbi[$tt][$blok])."</td>";
			$tab2.="<td align=right>".@hidezerodecimal(fixnan($rpbgtsdbi[$tt][$blok]/$blokbgtsdbi[$tt][$blok]))."</td>";
			$tab2.="<td align=right>".@hidezerodecimal($rpbgtthn[$tt][$blok])."</td>";
			$tab2.="<td align=right>".@hidezerodecimal(fixnan($rpbgtthn[$tt][$blok]/$blokbgtthn[$tt][$blok]))."</td>";
			$tab2.="<td align=right>".@hidezerodecimal($rpactbi[$tt][$blok])."</td>";
			$tab2.="<td align=right>".@hidezerodecimal(fixnan($rpactbi[$tt][$blok]/$actkgbi[$tt][$blok]))."</td>";
			$tab2.="<td align=right>".@hidezerodecimal($rpactsdbi[$tt][$blok])."</td>";
			$tab2.="<td align=right>".@hidezerodecimal(fixnan($rpactsdbi[$tt][$blok]/$actkgsdbi[$tt][$blok]))."</td>";
			
			$varrpbi[$tt][$blok]=$rpactbi[$tt][$blok]-$rpbgtbi[$tt][$blok];
			if($varrpbi[$tt][$blok]>0 || $rpactbi[$tt][$blok]==0){
				$color="style=color:red;font-weight:normal";
			}else{
				$color="style=color:blue;";
			}
			$tab2.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($varrpbi[$tt][$blok]/$rpbgtbi[$tt][$blok]*100),2)."</td>";
			
			$varrpsdbi[$tt][$blok]=$rpactsdbi[$tt][$blok]-$rpbgtsdbi[$tt][$blok];
			if($varrpsdbi[$tt][$blok]>0 || $rpactsdbi[$tt][$blok]==0){
				$color="style=color:red;font-weight:normal";
			}else{
				$color="style=color:blue;";
			}
			$tab2.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($varrpsdbi[$tt][$blok]/$rpbgtsdbi[$tt][$blok]*100),2)."</td>";
			
			$varrpthn[$tt][$blok]=$rpactsdbi[$tt][$blok]-$rpbgtthn[$tt][$blok];
			if($varrpthn[$tt][$blok]>0 || $rpactsdbi[$tt][$blok]==0){
				$color="style=color:red;font-weight:normal";
			}else{
				$color="style=color:blue;";
			}
			$tab2.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($varrpthn[$tt][$blok]/$rpbgtthn[$tt][$blok]*100),2)."</td>";
			$tab2.="</tr>";
			
			$stluas[$tt]+=$luas[$tt][$blok];
			$stpokok[$tt]+=$pokok[$tt][$blok];
			$strpbgtbi[$tt]+=$rpbgtbi[$tt][$blok];
			$stblokbgtbi[$tt]+=$blokbgtbi[$tt][$blok];
			$strpbgtsdbi[$tt]+=$rpbgtsdbi[$tt][$blok];
			$stblokbgtsdbi[$tt]+=$blokbgtsdbi[$tt][$blok];
			$strpbgtthn[$tt]+=$rpbgtthn[$tt][$blok];
			$stblokbgtthn[$tt]+=$blokbgtthn[$tt][$blok];
			$strpactbi[$tt]+=$rpactbi[$tt][$blok];
			$stactkgbi[$tt]+=$actkgbi[$tt][$blok];
			$strpactsdbi[$tt]+=$rpactsdbi[$tt][$blok];
			$stactkgsdbi[$tt]+=$actkgsdbi[$tt][$blok];
			
			
			$gtluas+=$luas[$tt][$blok];
			$gtpokok+=$pokok[$tt][$blok];
			$gtrpbgtbi+=$rpbgtbi[$tt][$blok];
			$gtblokbgtbi+=$blokbgtbi[$tt][$blok];
			$gtrpbgtsdbi+=$rpbgtsdbi[$tt][$blok];
			$gtblokbgtsdbi+=$blokbgtsdbi[$tt][$blok];
			$gtrpbgtthn+=$rpbgtthn[$tt][$blok];
			$gtblokbgtthn+=$blokbgtthn[$tt][$blok];
			$gtrpactbi+=$rpactbi[$tt][$blok];
			$gtactkgbi+=$actkgbi[$tt][$blok];
			$gtrpactsdbi+=$rpactsdbi[$tt][$blok];
			$gtactkgsdbi+=$actkgsdbi[$tt][$blok];
		}
			
		$tab2.="<tr class='rowcontent' style=background-color:#dcfce8>";
		$tab2.="<td colspan=2>Sub Total</td>";
		$tab2.="<td align=right>".hidezerodecimal($stluas[$tt],2)."</td>";
		$tab2.="<td align=right>".hidezerodecimal($stpokok[$tt])."</td>";
		$tab2.="<td align=right>".hidezerodecimal(@fixnan($stpokok[$tt]/$stluas[$tt]))."</td>";
		$tab2.="<td align=right>".hidezerodecimal($strpbgtbi[$tt])."</td>";
		$tab2.="<td align=right>".hidezerodecimal(@fixnan($strpbgtbi[$tt]/$stblokbgtbi[$tt]))."</td>";
		$tab2.="<td align=right>".hidezerodecimal($strpbgtsdbi[$tt])."</td>";
		$tab2.="<td align=right>".hidezerodecimal(@fixnan($strpbgtsdbi[$tt]/$stblokbgtsdbi[$tt]))."</td>";
		$tab2.="<td align=right>".hidezerodecimal($strpbgtthn[$tt])."</td>";
		$tab2.="<td align=right>".hidezerodecimal(@fixnan($strpbgtthn[$tt]/$stblokbgtthn[$tt]))."</td>";
		$tab2.="<td align=right>".hidezerodecimal($strpactbi[$tt])."</td>";
		$tab2.="<td align=right>".@hidezerodecimal(fixnan(@$strpactbi[$tt]/@$stactkgbi[$tt]))."</td>";
		$tab2.="<td align=right>".hidezerodecimal($strpactsdbi[$tt])."</td>";
		$tab2.="<td align=right>".@hidezerodecimal(@fixnan(@$strpactsdbi[$tt]/@$stactkgsdbi[$tt]))."</td>";
		
		$stvarrpbi[$tt]=$strpactbi[$tt]-$strpbgtbi[$tt];
		if($stvarrpbi[$tt]>0 || $stvarrpbi[$tt]==0){
			$color="style=color:red;font-weight:normal";
		}else{
			$color="style=color:blue;";
		}
		$tab2.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($stvarrpbi[$tt]/$strpbgtbi[$tt]*100),2)."</td>";
		
		$stvarrpsdbi[$tt]=$strpactsdbi[$tt]-$strpbgtsdbi[$tt];
		if($stvarrpsdbi[$tt]>0 || $stvarrpsdbi[$tt]==0){
			$color="style=color:red;font-weight:normal";
		}else{
			$color="style=color:blue;";
		}
		$tab2.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($stvarrpsdbi[$tt]/$strpbgtbi[$tt]*100),2)."</td>";
		
		$stvarrpthn[$tt]=$strpactsdbi[$tt]-$strpbgtthn[$tt];
		if($stvarrpthn[$tt]>0 || $stvarrpthn[$tt]==0){
			$color="style=color:red;font-weight:normal";
		}else{
			$color="style=color:blue;";
		}
		$tab2.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($stvarrpthn[$tt]/$strpbgtthn[$tt]*100),2)."</td>";
		$tab2.="</tr>";
	}
	$tab2.="<tr class='rowcontent'>";
	$tab2.="<td colspan=18></td>";
	$tab2.="</tr>";
	$tab2.="<tr class='rowcontent' style=background-color:#dcfce8>";
	$tab2.="<td colspan=2>Grand Total</td>";
	$tab2.="<td align=right>".$gtluas."</td>";
	$tab2.="<td align=right>".hidezerodecimal($gtpokok)."</td>";
	$tab2.="<td align=right>".hidezerodecimal(@fixnan($gtpokok/$gtluas))."</td>";
	$tab2.="<td align=right>".hidezerodecimal($gtrpbgtbi)."</td>";
	$tab2.="<td align=right>".hidezerodecimal($gtrpbgtbi/$gtblokbgtbi)."</td>";
	$tab2.="<td align=right>".hidezerodecimal($gtrpbgtsdbi)."</td>";
	$tab2.="<td align=right>".hidezerodecimal($gtrpbgtsdbi/$gtblokbgtsdbi)."</td>";
	$tab2.="<td align=right>".hidezerodecimal($gtrpbgtthn)."</td>";
	$tab2.="<td align=right>".hidezerodecimal($gtrpbgtthn/$gtblokbgtthn)."</td>";
	$tab2.="<td align=right>".hidezerodecimal($gtrpactbi)."</td>";
	$tab2.="<td align=right>".hidezerodecimal($gtrpactbi/$gtactkgbi)."</td>";
	$tab2.="<td align=right>".hidezerodecimal($gtrpactsdbi)."</td>";
	$tab2.="<td align=right>".hidezerodecimal($gtrpactsdbi/$gtactkgsdbi)."</td>";
	
	$gtvarrpbi=$gtrpactbi-$gtrpbgtbi;
	if($gtvarrpbi>0 || $gtvarrpbi==0){
		$color="style=color:red;font-weight:normal";
	}else{
		$color="style=color:blue;";
	}
	$tab2.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($gtvarrpbi/$gtrpbgtbi*100),2)."</td>";
	
	$gtvarrpsdbi=$gtrpactsdbi-$gtrpbgtsdbi;
	if($gtvarrpsdbi>0 || $gtvarrpsdbi==0){
		$color="style=color:red;font-weight:normal";
	}else{
		$color="style=color:blue;";
	}
	$tab2.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($gtvarrpsdbi/$gtrpbgtsdbi*100),2)."</td>";
	
	$gtvarrpthn=$gtrpactsdbi-$gtrpbgtthn;
	if($gtvarrpthn>0 || $gtvarrpthn==0){
		$color="style=color:red;font-weight:normal";
	}else{
		$color="style=color:blue;";
	}
	$tab2.="<td align=right ".$color.">".@hidezerodecimal(@fixnan($gtvarrpthn/$gtrpbgtthn*100),2)."</td>";
	$tab2.="</tr>";
	$tab2.="</tbody></table>";
	
	
	$hasil=$tab;
	$hasil.=$tab1;
	$hasil.=$tab2;
	
	$dompdf = new Dompdf();
	$dompdf->load_html($hasil);
	$dompdf->setPaper('A4', 'landscape');
	$dompdf->render();
	$canvas = $dompdf->get_canvas();
	
	if($param['namafile']!=''){
		$filepdf=$param['namafile'];
	}else{		
		$filepdf=$folder.$kodeorg.".pdf";
	}
	
	if (file_exists($filepdf)){
		unlink($filepdf);
	}
	file_put_contents($filepdf, $dompdf->output());
}
// echo"<pre>";
// print_r($listblok[$kodeorg]);

// exit();


$tab="
	<table cellspacing=0 border=0 width=100% align=center style=\"font-family:tahoma,Arial Narrow;font-size:10px;\">
		<tr>
			<td rowspan=3 valign=center style='font-weight:bold;width:100px'><img src='".$path."' height='60' /></td>
		</tr>
	</table>
	<table cellspacing=0 border=0 width=100% style='text-align:center' style='font-family:tahoma,Arial Narrow;font-size:14px;'>
		<tr style='font-family:tahoma,Arial Narrow;font-size:16px;'>
			<td style=font-weight:bold;><b>LAPORAN PENERIMAAN TBS</b></td>
		</tr>
		<tr style='font-family:tahoma,Arial Narrow;font-size:14px;'>
			<td colspan=7 align=center>
				Periode : ".($tgl)." s/d ".($tgl)."
			</td>
		</tr>
	</table>";
		
$tab.="<table id=pvtTable cellpadding=5 cellspacing=0 border=1 width=100% class='sortable' style='font-family:tahoma,Arial Narrow;font-size:14px;'>
		<thead>
			<tr class=rowheader style=height:25px>
				<th rowspan=2 align=center>DIVISI</th>
				<th rowspan=2 align=center>BRUTO<br>(Kg)</th>
				<th rowspan=2 align=center>TARA<br>(Kg)</th>
				<th rowspan=2 align=center>NETTO<br>(SEBELUM<br>GRADING)<br>(Kg)</th>
				<th colspan=2 align=center>SORTASI</th>
				<th rowspan=2 align=center>NETTO<br>(SESUSAH<br>GRADING)<br>(Kg)</th>
				<th rowspan=2 align=center>RITASE</th>
			</tr>
			<tr class=rowheader style=height:25px>
				<th align=center>(Kg)</th>
				<th align=center>(%)</th>
			</tr>
		</thead>
		<tbody>";

$regional=makeOption($dbname,'bgt_regional_assignment','kodeunit,subregional');

$str = "SELECT * from " . $dbname . ".organisasi";
$res = fetchdata($str);
foreach($res as $bar){
	$nmorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}
$nmorg['EXTN']='EXTERNAL / SWADAYA';


$wh =" and tanggal like '".$tgl."%'";	
$str = "SELECT * from " . $dbname . ".pabrik_timbangan where 1=1 ".$wh." and kodebarang='40000003' order by kodeorg desc, intiplasma asc, divcode asc";
$res = fetchdata($str);
$jumlah = count($res);
foreach($res as $bar){
	$nmsup=makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier',"kodetimbangan='".$bar['kodecustomer']."'");
	if($bar['kodeorg']==''){
		$bar['kodeorg']="EXTN";
		if($nmsup[$bar['kodecustomer']]==''){
			// $bar['divcode']=$bar['kodecustomer'];
			$bar['divcode']=$bar['pengirim'];
		}else{					
			// $bar['divcode']=$nmsup[$bar['kodecustomer']];
			$bar['divcode']=$bar['pengirim'];
		}
	}else{
		$bar['divcode']=$nmorg[$bar['divcode']];		
	}
	
	if($regional[$bar['kodeorg']]!=''){
		$reg = $regional[$bar['kodeorg']];
	}else{
		$reg = $regional[$bar['millcode']];
		$bar['intiplasma']='External / Swadaya';
	}

	$namapt = getNamaOrg($bar['kodeorg'],'induk');	
	if($namapt==''){
		$namapt = "EXTN";
	}
	
	$listpks[$bar['millcode']]=$bar['millcode'];
	
	if(getNamaOrg($bar['kodeorg'])==""){
		$bar['kodeorg']="External / Swadaya";
	}else{
		$bar['kodeorg']=getNamaOrg($bar['kodeorg']);
	}	
	$data[$bar['millcode']][$namapt][$bar['intiplasma']][$bar['kodeorg']][$bar['divcode']]=$bar['divcode'];
	$kg[$bar['millcode']][$namapt][$bar['intiplasma']][$bar['kodeorg']][$bar['divcode']]['bruto']+=$bar['beratmasuk'];
	$kg[$bar['millcode']][$namapt][$bar['intiplasma']][$bar['kodeorg']][$bar['divcode']]['tara']+=$bar['beratkeluar'];
	$kg[$bar['millcode']][$namapt][$bar['intiplasma']][$bar['kodeorg']][$bar['divcode']]['netto']+=$bar['beratmasuk']-$bar['beratkeluar'];
	$kg[$bar['millcode']][$namapt][$bar['intiplasma']][$bar['kodeorg']][$bar['divcode']]['pot']+=$bar['kgpotsortasi'];
	$kg[$bar['millcode']][$namapt][$bar['intiplasma']][$bar['kodeorg']][$bar['divcode']]['rit']+=1;
	
	if($bar['intiplasma']=='INTI'){
		$rekap[$bar['millcode']]['inti']+=$bar['beratmasuk']-$bar['beratkeluar'];
	}elseif($bar['intiplasma']=='KUD'){
		$rekap[$bar['millcode']]['plasma']+=$bar['beratmasuk']-$bar['beratkeluar'];
	}else{
		$rekap[$bar['millcode']]['swadaya']+=$bar['beratmasuk']-$bar['beratkeluar'];
	}
}

// echo "<pre>";
// print_r($data);


foreach($data as $mill => $v1){
	$tab.="<tr class='rowcontent' style=font-weight:bold;background-color:#91ffb9>";
	$tab.="<td colspan=8></td>";
	$tab.="</tr>";
	$tab.="<tr class='rowcontent' style=font-weight:bold;background-color:#38b3ff>";
	$tab.="<td colspan=8><b>".$mill." - ".getNamaOrg($mill)."</b></td>";			
	$tab.="</tr>";
	foreach($v1 as $pt => $v2){
		$tab.="<tr class='rowcontent'>";
		$tab.="<td colspan=8 style=padding-left:25px><b>".$nmorg[$pt]."</b></td>";			
		$tab.="</tr>";
		foreach($v2 as $ip => $v3){
			$tab.="<tr class='rowcontent'>";
			$tab.="<td colspan=8 style=padding-left:50px><b>".$ip."</b></td>";			
			$tab.="</tr>";
			foreach($v3 as $kdorg => $v4){				
				$tab.="<tr class='rowcontent'>";
				$tab.="<td colspan=8 style=padding-left:75px><b>".$kdorg."</b></td>";			
				$tab.="</tr>";
				$nomor=0;
				foreach($v4 as $div){
					$nomor++;
					$tab.="<tr class='rowcontent'>";
					$tab.="<td align=left style=padding-left:100px>".$nomor.". ".$div."</td>";
					$tab.="<td align=right>".number_format($kg[$mill][$pt][$ip][$kdorg][$div]['bruto'])."</td>";
					$tab.="<td align=right>".number_format($kg[$mill][$pt][$ip][$kdorg][$div]['tara'])."</td>";
					$tab.="<td align=right>".number_format($kg[$mill][$pt][$ip][$kdorg][$div]['netto'])."</td>";
					$tab.="<td align=right>".number_format($kg[$mill][$pt][$ip][$kdorg][$div]['pot'])."</td>";
					$tab.="<td align=right>".number_format($kg[$mill][$pt][$ip][$kdorg][$div]['pot']/$kg[$mill][$pt][$ip][$kdorg][$div]['netto']*100,2)."</td>";
					$tab.="<td align=right>".number_format($kg[$mill][$pt][$ip][$kdorg][$div]['netto']-$kg[$mill][$pt][$ip][$kdorg][$div]['pot'])."</td>";
					$tab.="<td align=right>".number_format($kg[$mill][$pt][$ip][$kdorg][$div]['rit'])."</td>";
					$tab.="</tr>";
					
					#total
					$ttlkdorg[$mill][$pt][$ip][$kdorg]['bruto']+=$kg[$mill][$pt][$ip][$kdorg][$div]['bruto'];
					$ttlkdorg[$mill][$pt][$ip][$kdorg]['tara']+=$kg[$mill][$pt][$ip][$kdorg][$div]['tara'];
					$ttlkdorg[$mill][$pt][$ip][$kdorg]['netto']+=$kg[$mill][$pt][$ip][$kdorg][$div]['netto'];
					$ttlkdorg[$mill][$pt][$ip][$kdorg]['pot']+=$kg[$mill][$pt][$ip][$kdorg][$div]['pot'];
					$ttlkdorg[$mill][$pt][$ip][$kdorg]['rit']+=$kg[$mill][$pt][$ip][$kdorg][$div]['rit'];
					
					$ttlip[$mill][$pt][$ip]['bruto']+=$kg[$mill][$pt][$ip][$kdorg][$div]['bruto'];
					$ttlip[$mill][$pt][$ip]['tara']+=$kg[$mill][$pt][$ip][$kdorg][$div]['tara'];
					$ttlip[$mill][$pt][$ip]['netto']+=$kg[$mill][$pt][$ip][$kdorg][$div]['netto'];
					$ttlip[$mill][$pt][$ip]['pot']+=$kg[$mill][$pt][$ip][$kdorg][$div]['pot'];
					$ttlip[$mill][$pt][$ip]['rit']+=$kg[$mill][$pt][$ip][$kdorg][$div]['rit'];
					
					$ttlpt[$mill][$pt]['bruto']+=$kg[$mill][$pt][$ip][$kdorg][$div]['bruto'];
					$ttlpt[$mill][$pt]['tara']+=$kg[$mill][$pt][$ip][$kdorg][$div]['tara'];
					$ttlpt[$mill][$pt]['netto']+=$kg[$mill][$pt][$ip][$kdorg][$div]['netto'];
					$ttlpt[$mill][$pt]['pot']+=$kg[$mill][$pt][$ip][$kdorg][$div]['pot'];
					$ttlpt[$mill][$pt]['rit']+=$kg[$mill][$pt][$ip][$kdorg][$div]['rit'];
					
					$ttlmill[$mill]['bruto']+=$kg[$mill][$pt][$ip][$kdorg][$div]['bruto'];
					$ttlmill[$mill]['tara']+=$kg[$mill][$pt][$ip][$kdorg][$div]['tara'];
					$ttlmill[$mill]['netto']+=$kg[$mill][$pt][$ip][$kdorg][$div]['netto'];
					$ttlmill[$mill]['pot']+=$kg[$mill][$pt][$ip][$kdorg][$div]['pot'];
					$ttlmill[$mill]['rit']+=$kg[$mill][$pt][$ip][$kdorg][$div]['rit'];
					
					$gttl['bruto']+=$kg[$mill][$pt][$ip][$kdorg][$div]['bruto'];
					$gttl['tara']+=$kg[$mill][$pt][$ip][$kdorg][$div]['tara'];
					$gttl['netto']+=$kg[$mill][$pt][$ip][$kdorg][$div]['netto'];
					$gttl['pot']+=$kg[$mill][$pt][$ip][$kdorg][$div]['pot'];
					$gttl['rit']+=$kg[$mill][$pt][$ip][$kdorg][$div]['rit'];
				}
				$tab.="<tr class='rowcontent' style=font-weight:bold>";
				$tab.="<td style=padding-left:75px><b>SUB TOTAL - ".$kdorg."</b></td>";
				$tab.="<td align=right>".number_format($ttlkdorg[$mill][$pt][$ip][$kdorg]['bruto'])."</td>";
				$tab.="<td align=right>".number_format($ttlkdorg[$mill][$pt][$ip][$kdorg]['tara'])."</td>";
				$tab.="<td align=right>".number_format($ttlkdorg[$mill][$pt][$ip][$kdorg]['netto'])."</td>";
				$tab.="<td align=right>".number_format($ttlkdorg[$mill][$pt][$ip][$kdorg]['pot'])."</td>";
				$tab.="<td align=right>".number_format($ttlkdorg[$mill][$pt][$ip][$kdorg]['pot']/$ttlkdorg[$mill][$pt][$ip][$kdorg]['netto']*100,2)."</td>";
				$tab.="<td align=right>".number_format($ttlkdorg[$mill][$pt][$ip][$kdorg]['netto']-$ttlkdorg[$mill][$pt][$ip][$kdorg]['pot'])."</td>";
				$tab.="<td align=right>".number_format($ttlkdorg[$mill][$pt][$ip][$kdorg]['rit'])."</td>";
				$tab.="</tr>";
			}
			$tab.="<tr class='rowcontent' style=font-weight:bold>";
			$tab.="<td style=padding-left:50px><b>SUB TOTAL - ".$ip."</b></td>";
			$tab.="<td align=right>".number_format($ttlip[$mill][$pt][$ip]['bruto'])."</td>";
			$tab.="<td align=right>".number_format($ttlip[$mill][$pt][$ip]['tara'])."</td>";
			$tab.="<td align=right>".number_format($ttlip[$mill][$pt][$ip]['netto'])."</td>";
			$tab.="<td align=right>".number_format($ttlip[$mill][$pt][$ip]['pot'])."</td>";
			$tab.="<td align=right>".number_format($ttlip[$mill][$pt][$ip]['pot']/$ttlip[$mill][$pt][$ip]['netto']*100,2)."</td>";
			$tab.="<td align=right>".number_format($ttlip[$mill][$pt][$ip]['netto']-$ttlip[$mill][$pt][$ip]['pot'])."</td>";
			$tab.="<td align=right>".number_format($ttlip[$mill][$pt][$ip]['rit'])."</td>";
			$tab.="</tr>";
		}
		$tab.="<tr class='rowcontent' style=font-weight:bold>";
		$tab.="<td style=padding-left:25px><b>SUB TOTAL - ".$nmorg[$pt]."</b></td>";
		$tab.="<td align=right>".number_format($ttlpt[$mill][$pt]['bruto'])."</td>";
		$tab.="<td align=right>".number_format($ttlpt[$mill][$pt]['tara'])."</td>";
		$tab.="<td align=right>".number_format($ttlpt[$mill][$pt]['netto'])."</td>";
		$tab.="<td align=right>".number_format($ttlpt[$mill][$pt]['pot'])."</td>";
		$tab.="<td align=right>".number_format($ttlpt[$mill][$pt]['pot']/$ttlpt[$mill][$pt]['netto']*100,2)."</td>";
		$tab.="<td align=right>".number_format($ttlpt[$mill][$pt]['netto']-$ttlpt[$mill][$pt]['pot'])."</td>";
		$tab.="<td align=right>".number_format($ttlpt[$mill][$pt]['rit'])."</td>";
		$tab.="</tr>";
	}
	$tab.="<tr class='rowcontent' style=font-weight:bold;background-color:#e6e3e3>";
	$tab.="<td ><b>SUB TOTAL - ".$nmorg[$mill]."</b></td>";
	$tab.="<td align=right>".number_format($ttlmill[$mill]['bruto'])."</td>";
	$tab.="<td align=right>".number_format($ttlmill[$mill]['tara'])."</td>";
	$tab.="<td align=right>".number_format($ttlmill[$mill]['netto'])."</td>";
	$tab.="<td align=right>".number_format($ttlmill[$mill]['pot'])."</td>";
	$tab.="<td align=right>".number_format($ttlmill[$mill]['pot']/$ttlmill[$mill]['netto']*100,2)."</td>";
	$tab.="<td align=right>".number_format($ttlmill[$mill]['netto']-$ttlmill[$mill]['pot'])."</td>";
	$tab.="<td align=right>".number_format($ttlmill[$mill]['rit'])."</td>";
	$tab.="</tr>";
}

$tab.="<tr class='rowcontent' style=font-weight:bold;background-color:#7dffe7>";
$tab.="<td ><b>GRAND TOTAL</b></td>";
$tab.="<td align=right>".number_format($gttl['bruto'])."</td>";
$tab.="<td align=right>".number_format($gttl['tara'])."</td>";
$tab.="<td align=right>".number_format($gttl['netto'])."</td>";
$tab.="<td align=right>".number_format($gttl['pot'])."</td>";
$tab.="<td align=right>".number_format($gttl['pot']/$gttl['netto']*100,2)."</td>";
$tab.="<td align=right>".number_format($gttl['netto']-$gttl['pot'])."</td>";
$tab.="<td align=right>".number_format($gttl['rit'])."</td>";
$tab.="</tr>";

$tab.="</tbody></table><br>";

$wh=" and tanggal like '".substr($tgl,0,7)."%'";	
$str = "SELECT intiplasma,kodecustomer, kodeorg, millcode, sum(beratmasuk) as beratmasuk, sum(beratkeluar) as beratkeluar, sum(kgpotsortasi) as kgpotsortasi from " . $dbname . ".pabrik_timbangan where 1=1 ".$wh." and kodebarang='40000003' group by kodecustomer, kodeorg, millcode, intiplasma";
$res = fetchdata($str);
foreach($res as $bar){
	$nmsup=makeOption($dbname,'log_5supplier','kodetimbangan,namasupplier',"kodetimbangan='".$bar['kodecustomer']."'");
	if($bar['kodeorg']==''){
		$bar['kodeorg']="EXTN";
	}
	
	if($regional[$bar['kodeorg']]!=''){
		$reg = $regional[$bar['kodeorg']];
	}else{
		$reg = $regional[$bar['millcode']];
		$bar['intiplasma']='External / Swadaya';
	}
	
	if(getNamaOrg($bar['kodeorg'])==""){
		$bar['kodeorg']="External / Swadaya";
	}else{
		$bar['kodeorg']=getNamaOrg($bar['kodeorg']);
	}	
	$rekap[$bar['millcode']]['netto']+=$bar['beratmasuk']-$bar['beratkeluar'];
	$rekap[$bar['millcode']]['pot']+=$bar['kgpotsortasi'];
}


$tab.="<table cellpadding=5 cellspacing=0 border=1 class='sortable'  style='font-family:tahoma,Arial Narrow;font-size:14px;'>
		<thead>
			<tr class=rowheader style=height:25px>
				<th align=center>KETERANGAN</th>";
				foreach($listpks as $pks){
					$tab.="<th align=center>".$pks."</th>";
				}
				$tab.="<th align=center>TOTAL</th>";
			$tab.="</tr>
		</thead>
		<tbody>";
	$tab.="<tr class='rowcontent'>";
	$tab.="<td >Penerimaan TBS dari 1 s/d hari ini (".tglnormal($tgl).")</td>";
	foreach($listpks as $pks){		
		$tab.="<td align=right>".number_format($rekap[$pks]['netto'])."</td>";
		$trekap+=$rekap[$pks]['netto'];
	}
	$tab.="<td align=right>".number_format($trekap)."</td>";
	$tab.="</tr>";
	
	$tab.="<tr class='rowcontent'>";
	$tab.="<td >TBS Setelah Grading dari 1 s/d hari ini (".tglnormal($tgl).")</td>";
	foreach($listpks as $pks){		
		$tab.="<td align=right>".number_format($rekap[$pks]['netto']-$rekap[$pks]['pot'])."</td>";
		$tgrading+=$rekap[$pks]['netto']-$rekap[$pks]['pot'];
	}
	$tab.="<td align=right>".number_format($tgrading)."</td>";
	$tab.="</tr>";
	
	$tab.="<tr class='rowcontent'>";
	$tab.="<td >Total Inti hari ini (".tglnormal($tgl).")</td>";
	foreach($listpks as $pks){		
		$tab.="<td align=right>".number_format($rekap[$pks]['inti'])."</td>";
		$tinti+=$rekap[$pks]['inti'];
	}
	$tab.="<td align=right>".number_format($tinti)."</td>";
	$tab.="</tr>";
	
	$tab.="<tr class='rowcontent'>";
	$tab.="<td >Total Plasma hari ini (".tglnormal($tgl).")</td>";
	foreach($listpks as $pks){		
		$tab.="<td align=right>".number_format($rekap[$pks]['plasma'])."</td>";
		$tplasma+=$rekap[$pks]['plasma'];
	}
	$tab.="<td align=right>".number_format($tplasma)."</td>";
	$tab.="</tr>";
	
	$tab.="<tr class='rowcontent'>";
	$tab.="<td >Total External / Swadaya hari ini (".tglnormal($tgl).")</td>";
	foreach($listpks as $pks){		
		$tab.="<td align=right>".number_format($rekap[$pks]['swadaya'])."</td>";
		$tswadaya+=$rekap[$pks]['swadaya'];
	}
	$tab.="<td align=right>".number_format($tswadaya)."</td>";
	$tab.="</tr>";
$tab.="</tbody></table>";

$tab.="<footer  style=\"position: fixed; bottom: 0cm; left: 0cm; right: 0cm;height:0cm;text-align:left;font-style:italic;font-size:8px\">auto generated by owl.ksp-agro.com on ".date("d-m-Y H:i:s")."</footer>";

// echo $tab;
if($jumlah>0){
	$dompdf = new Dompdf();
	$dompdf->load_html($tab);
	$dompdf->setPaper('A4', 'landscape');
	$dompdf->render();
	$canvas = $dompdf->get_canvas();
	
	$filepdf=$folder."laporanpenerimaantbs.pdf";
	if (file_exists($filepdf)){
		unlink($filepdf);
	}
	file_put_contents($filepdf, $dompdf->output());
}


?>