<?php
ini_set('display_errors',0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$method = checkPostGet('method', '');
$tipe = checkPostGet('tipe', '');
$periode = checkPostGet('periode', '');
$tanggal = tanggalsystemn(checkPostGet('tanggal', ''));
$bulan = substr($tanggal, 5,2);
$tahun = substr($tanggal, 0,4);
$jumlahharitodate = floatval(substr($tanggal, 8,2));
$jumlahhari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
$jumlahhari = 25;
$monthbudget = "olah".$bulan;
$cpobudget = "kgcpo".$bulan;
$pkbudget = "kgker".$bulan;

switch ($method) {
	case 'loaddata':
		$tab='
		<table class=sortable cellspacing=1 cellpadding=5>
		<thead>
		<tr class=rowheader>
		<th>No</th>
		<th>Tanggal</th>
		<th colspan=2>Cetak</th>
		</tr>
		</thead>
		<tbody>';

		$tgl1 = $periode."-01";
		$tgl2 = tglakhir($periode."-01");
		
		$arrtgl=rangeTanggal($tgl1,$tgl2);
		
		// $str="select distinct(tanggal) as tanggal from ".$dbname.".pabrik_stokforsale where tanggal like '".$periode."%'";
		// $res=fetchdata($str);
		foreach ($arrtgl as $val) {
			$no++;
			$tab.="
			<tr class=rowcontent>
			<td align=center>".$no."</td>
			<td>".tanggalnormal($val)."</td>
			<td><img src=images/skyblue/zoom.png class=resicon title=preview onclick=preview('".tanggalnormal($val)."')></td>
			<td><img src=images/skyblue/excel.jpg class=resicon title=Excel onclick=excel('".tanggalnormal($val)."',Event)></td>
			</tr>";
		}

		$tab.='</tbody></table>';

		echo $tab;
		
	break;
	case 'preview':
		$theme=$_SESSION['theme'];
        if($theme=='skyblue' || $theme==''){
          $gen='generic.css';
        }else if($theme=='red'){
          $gen='genericRed.css';  
        }else{
          $gen='genericGray.css';  
        }          
        $tab="";
		
		if($tipe!='excel'){
			$tab.="<link rel=stylesheet type=text/css href=style/".$gen.">";
		}
		
		$tanggal1 = substr($tanggal,0,7)."-01";
		
		if ($tanggal > date('Y-m-d')) {
			exit('Warning, Data kosong');
		}
		
		if($tipe=='excel'){
			$border="border=1";
		}else{
			$border="border=0";
		}
		
		$tab.="
		<table class=sortable cellspacing=1 cellpadding=5 ".$border.">
		<thead>
		<tr class=rowheader>
		<th colspan=17 style=backgorund-color:red;>CONSOL DAILY PROD REPORT KSP AGRO ".tanggalnormal($tanggal)."</th>
		</tr>
		<tr class=rowheader>
		<th rowspan=2>ITEMS</th>
		<th colspan=4>REGION SEKADAU - MILL 60 TPH</th>
		<th colspan=4>REGION BONTI - MILL 30 TPH</th>
		<th colspan=4>REGION KAPUAS - MILL 90 TPH</th>
		<th colspan=4>KSP AGRO - MILL 180 TPH</th>
		</tr>";
		for ($i=1; $i < 5; $i++) {
			$tab.='
			<th>ACTUAL</th>
			<th>BUDGET</th>
			<th>%</th>
			<th>% UTIL</th>';
		}
		$tab.="
		</thead>
		<thead>
			<tr class=rowheader>
				<th colspan=17>
					<button class=mybutton onclick=showfilter()>Show Filter</button>
					<button class=mybutton onclick=preview('".tanggalnormal(tglkemarin($tanggal))."')>Back ".tanggalnormal(tglkemarin($tanggal))."</button>
					<button class=mybutton style=color:blue;font-weight:bold; onclick=preview('".tanggalnormal($tanggal)."')>To Day ".tanggalnormal($tanggal)."</button>
					<button class=mybutton onclick=preview('".tanggalnormal(tglbesok($tanggal))."')>Next ".tanggalnormal(tglbesok($tanggal))."</button>
				</th>
			</tr>
		</thead>
		<tbody>";

		#TBS INIT (TON)
		$tab.='
		<tr class=rowcontent>
		<td><b>TBS INTI (TON)</b></td>
		<td colspan=16></td>
		</tr>';

		##millcode
		// $str="select kodeorganisasi from ".$dbname.".organisasi where tipe='PABRIK'";
		// $res=fetchdata($str);
		// foreach ($res as $val) {
		// 	$millcodes[$val['kodeorganisasi']]=$val['kodeorganisasi'];
		// }
		$millcodes = array('KSPM' => 'KSPM', 'BPJM' => 'BPJM', 'SDKM' => 'SDKM');
		$jumlahpks=count($millcodes);


		##prod budget to day
		$prodbudgets=array();
		$prodbudgetskspagro=0;
		$prodbudgetstodate=array();
		$prodbudgetskspagrotodate=0;
		$str="select millcode, ".$monthbudget." as dailybudget from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' and inti='1') and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			@$prodbudgets[$val['millcode']]+=($val['dailybudget']/$jumlahhari)/1000;
			$prodbudgetskspagro+=($val['dailybudget']/$jumlahhari)/1000;

			@$prodbudgetstodate[$val['millcode']]+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
			$prodbudgetskspagrotodate+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
		}

		##prod actual to day
		$prodactuals=[];
		$prodactualskspagro=0;
		$str="select millcode,beratbersih from ".$dbname.".pabrik_timbangan where kodebarang = '40000003' and substr(tanggal,1,10) = '".$tanggal."' and intiplasma='INTI'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodactuals[$val['millcode']]+=$val['beratbersih']/1000;
			$prodactualskspagro+=$val['beratbersih']/1000;
		}

		##prod actual to date
		$prodactualstodate=[];
		$prodactualskspagrotodate=0;
		$str="select millcode,beratbersih from ".$dbname.".pabrik_timbangan where kodebarang = '40000003' and substr(tanggal,1,10) between '".$tanggal1."' and '".$tanggal."' and intiplasma='INTI'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			@$prodactualstodate[$val['millcode']]+=$val['beratbersih']/1000;
			$prodactualskspagrotodate+=$val['beratbersih']/1000;
		}

		##prod actual to day transferred from sdk
		//$prodactualsfromsdk=[];
		$str="select millcode,beratbersih from ".$dbname.".pabrik_timbangan where kodebarang = '40000003' and tanggal like '".$tanggal."%' and intiplasma='INTI' and millcode!='SDKM' and kodeorg like 'SD%'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodactualsfromsdk[$val['millcode']]+=$val['beratbersih']/1000;
		}

		##prod actual to date transferred from sdk
		//$prodactualsfromsdktodate=[];
		$str="select millcode,beratbersih from ".$dbname.".pabrik_timbangan where kodebarang = '40000003' and substr(tanggal,1,10) between '".$tanggal1."' and '".$tanggal."' and intiplasma='INTI' and millcode!='SDKM' and kodeorg like 'SD%'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodactualsfromsdktodate[$val['millcode']]+=$val['beratbersih']/1000;
		}

		##prod budget transferred from sdk
		/*$prodbudgetsfromsdk=[];
		$prodbudgetstodatefromsdk=[];*/
		$str="select millcode, ".$monthbudget." as dailybudget from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' and inti='1') and millcode!='SDKM' and kodeunit like 'SD%'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodbudgetsfromsdk[$val['millcode']]+=($val['dailybudget']/$jumlahhari)/1000;

			$prodbudgetstodatefromsdk[$val['millcode']]+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
		}

		##To Day
		$tab.='
		<tr class=rowcontent>
		<td>To Day</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if (@$prodactuals[$millcode]) {
				$persen=($prodactuals[$millcode]/$prodbudgets[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format(@$prodactuals[$millcode],2).'</td>
			<td align=right>'.number_format($prodbudgets[$millcode],2).'</td>
			<td align=right>'.number_format($persen,2).'</td>
			<td></td>
			';
		}
		$persenkspagro=0;
		if ($prodactualskspagro) {
			$persenkspagro=($prodactualskspagro/$prodbudgetskspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($prodactualskspagro,2).'</td>
		<td align=right>'.number_format($prodbudgetskspagro,2).'</td>
		<td align=right>'.number_format($persenkspagro,2).'</td>
		<td></td>
		';
		$tab.='
		</tr>';


		##To Day Transferred from SDK
		$tab.='
		<tr class=rowcontent>
		<td>To Day Transferred from SDK</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($millcode != 'SDKM') {
				if (@$prodactualsfromsdk[$millcode]) {
					$persen=($prodactualsfromsdk[$millcode]/$prodbudgetsfromsdk[$millcode])*100;
				}
				$tab.='
				<td align=right>'.number_format(@$prodactualsfromsdk[$millcode],2).'</td>
				<td align=right>'.number_format($prodbudgetsfromsdk[$millcode],2).'</td>
				<td align=right>'.number_format(fixnan($persen),2).'</td>
				<td></td>
				';
			}
		}
		$tab.='
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td align=right>-</td>
		<td align=right>-</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';
	
		##Total tbs inti to day
		$tab.='
		<tr class=rowcontent>
		<td><b>TOTAL</b></td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($prodactuals[$millcode] != 0 || $prodactualsfromsdk[$millcode] != 0) {
				$persen=(($prodactuals[$millcode]+$prodactualsfromsdk[$millcode])/($prodbudgets[$millcode]+$prodbudgetsfromsdk[$millcode]))*100;
			}
			$tab.='
			<td align=right><b>'.number_format($prodactuals[$millcode]+$prodactualsfromsdk[$millcode],2).'</b></td>
			<td align=right><b>'.number_format($prodbudgets[$millcode]+$prodbudgetsfromsdk[$millcode],2).'</b></td>
			<td align=right><b>'.number_format(fixnan($persen),2).'</b></td>
			<td></td>
			';
		}
		$persenkspagro=0;
		if ($prodactualskspagro) {
			$persenkspagro=($prodactualskspagro/$prodbudgetskspagro)*100;
		}
		$tab.='
		<td align=right><b>'.number_format($prodactualskspagro,2).'</b></td>
		<td align=right><b>'.number_format($prodbudgetskspagro,2).'</b></td>
		<td align=right><b>'.number_format(fixnan($persenkspagro),2).'</b></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##To Date
		$tab.='
		<tr class=rowcontent>
		<td>To Date</td>';
		$persentodate=0;
		foreach ($millcodes as $millcode) {
			if ($prodactualstodate[$millcode]) {
				$persentodate=($prodactualstodate[$millcode]/$prodbudgetstodate[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($prodactualstodate[$millcode],2).'</td>
			<td align=right>'.number_format($prodbudgetstodate[$millcode],2).'</td>
			<td align=right>'.number_format($persentodate,2).'</td>
			<td></td>
			';
		}
		$persenkspagrotodate=0;
		if ($prodactualskspagrotodate) {
			$persenkspagrotodate=($prodactualskspagrotodate/$prodbudgetskspagrotodate)*100;
		}
		$tab.='
		<td align=right>'.number_format($prodactualskspagrotodate,2).'</td>
		<td align=right>'.number_format($prodbudgetskspagrotodate,2).'</td>
		<td align=right>'.number_format($persenkspagrotodate,2).'</td>
		<td></td>
		';
		$tab.='
		</tr>';


		##To Date Transferred from SDK
		$tab.='
		<tr class=rowcontent>
		<td>To Day Transferred from SDK</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($millcode != 'SDKM') {
				if ($prodactualsfromsdk[$millcode]) {
					$persen=($prodactualsfromsdk[$millcode]/$prodbudgetstodatefromsdk[$millcode])*100;
				}
				$tab.='
				<td align=right>'.number_format($prodactualsfromsdktodate[$millcode],2).'</td>
				<td align=right>'.number_format($prodbudgetstodatefromsdk[$millcode],2).'</td>
				<td align=right>'.number_format(fixnan($persen),2).'</td>
				<td></td>
				';
			}
		}
		$tab.='
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td align=right>-</td>
		<td align=right>-</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';
	
		##Total tbs inti to date
		$tab.='
		<tr class=rowcontent>
		<td><b>TOTAL</b></td>';
		$persentodate=0;
		foreach ($millcodes as $millcode) {
			if ($prodactualstodate[$millcode] != 0 || $prodactualsfromsdktodate[$millcode] != 0) {
				$persentodate=(($prodactualstodate[$millcode]+$prodactualsfromsdktodate[$millcode])/($prodbudgetstodate[$millcode]+$prodbudgetstodatefromsdk[$millcode]))*100;
			}
			$tab.='
			<td align=right><b>'.number_format($prodactualstodate[$millcode]+$prodactualsfromsdktodate[$millcode],2).'</b></td>
			<td align=right><b>'.number_format($prodbudgetstodate[$millcode]+$prodbudgetstodatefromsdk[$millcode],2).'</b></td>
			<td align=right><b>'.number_format($persentodate,2).'</b></td>
			<td></td>
			';
		}
		$persenkspagrotodate=0;
		if ($prodactualskspagrotodate) {
			$persenkspagrotodate=($prodactualskspagrotodate/$prodbudgetskspagrotodate)*100;
		}
		$tab.='
		<td align=right><b>'.number_format($prodactualskspagrotodate,2).'</b></td>
		<td align=right><b>'.number_format($prodbudgetskspagrotodate,2).'</b></td>
		<td align=right><b>'.number_format($persenkspagrotodate,2).'</b></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##monthly budget
		// $monthlybudget=[];
		$monthlybudgetkspagro=0;
		$str="select millcode, ".$monthbudget." as monthlybudget 
		from ".$dbname.".bgt_produksi_pks 
		where tahunbudget='".$tahun."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' and inti='1') and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$monthlybudget[$val['millcode']]+=$val['monthlybudget']/1000;
			$monthlybudgetkspagro+=$val['monthlybudget']/1000;
		}

		##yearly budget
		//$yearlybudget=[];
		$yearlybudgetkspagro=0;
		$str="select millcode, kgolah as yearlybudget
		from ".$dbname.".bgt_produksi_pks
		where tahunbudget='".$tahun."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' and inti='1') and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$yearlybudget[$val['millcode']]+=$val['yearlybudget']/1000;
			$yearlybudgetkspagro+=$val['yearlybudget']/1000;
		}
		
		##monthly budget
		$tab.='
		<tr class=rowcontent>
		<td>Monthly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($monthlybudget[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($monthlybudgetkspagro,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##yearly budget
		$tab.='
		<tr class=rowcontent>
		<td>Yearly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($yearlybudget[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($yearlybudgetkspagro,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';
		
		
		$tab.='
		<tr class=rowcontent>
		<td></td>
		<td colspan=17></td>
		</tr>';


		#TBS PLASMA (TON)
		$tab.='
		<tr class=rowcontent>
		<td><b>TBS PLASMA (TON)</b></td>
		<td colspan=16></td>
		</tr>';
		##prod budget to day
		// $prodbudgets=[];
		$prodbudgetskspagro=0;
		//$prodbudgetstodate=[];
		$prodbudgetskspagrotodate=0;
		$str="select millcode, ".$monthbudget." as dailybudget from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' and inti='0') and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodbudgets[$val['millcode']]+=($val['dailybudget']/$jumlahhari)/1000;
			$prodbudgetskspagro+=($val['dailybudget']/$jumlahhari)/1000;

			$prodbudgetstodate[$val['millcode']]+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
			$prodbudgetskspagrotodate+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
		}

		##prod actual to day
		//$prodactuals=[];
		$prodactualskspagro=0;
		$prodactuals=array();
		$str="select millcode,beratbersih from ".$dbname.".pabrik_timbangan where kodebarang = '40000003' and tanggal like '".$tanggal."%' and intiplasma='KUD'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodactuals[$val['millcode']]+=$val['beratbersih']/1000;
			$prodactualskspagro+=$val['beratbersih']/1000;
		}

		##prod actual to date
		///$prodactualstodate=[];
		$prodactualskspagrotodate=0;
		$prodactualstodate=array();
		$str="select millcode,beratbersih from ".$dbname.".pabrik_timbangan where kodebarang = '40000003' and substr(tanggal,1,10) between '".$tanggal1."' and '".$tanggal."' and intiplasma='KUD'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodactualstodate[$val['millcode']]+=$val['beratbersih']/1000;
			$prodactualskspagrotodate+=$val['beratbersih']/1000;
		}

		##prod actual to day transferred from swadaya
		//$prodactualsfromswadaya=[];
		$prodactualsfromswadayakspagro=0;
		$str="select millcode,beratbersih from ".$dbname.".pabrik_timbangan where kodebarang = '40000003' and tanggal like '".$tanggal."%' and kodeorg = ''";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodactualsfromswadaya[$val['millcode']]+=$val['beratbersih']/1000;
			$prodactualsfromswadayakspagro+=$val['beratbersih']/1000;
		}

		##prod actual to date transferred from swadaya
		//$prodactualsfromswadayatodate=[];
		$prodactualsfromswadayatodatekspagro=0;
		$str="select millcode,beratbersih from ".$dbname.".pabrik_timbangan where kodebarang = '40000003' and substr(tanggal,1,10) between '".$tanggal1."' and '".$tanggal."' and kodeorg = ''";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodactualsfromswadayatodate[$val['millcode']]+=$val['beratbersih']/1000;
			$prodactualsfromswadayatodatekspagro+=$val['beratbersih']/1000;
		}

		##prod budget transferred from swadaya
		//$prodbudgetsfromswadaya=[];
		//$prodbudgetstodatefromswadaya=[];
		$prodbudgetsfromswadayakspagro=0;
		$prodbudgetsfromswadayakspagrotodate=0;
		$str="select millcode, ".$monthbudget." as dailybudget,kgolah from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and kodeunit = 'tbsexternal' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodbudgetsfromswadaya[$val['millcode']]+=($val['dailybudget']/$jumlahhari)/1000;
			$prodbudgetstodatefromswadaya[$val['millcode']]+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
			$prodbudgetsfromswadayakspagro+=($val['dailybudget']/$jumlahhari)/1000;
			$prodbudgetsfromswadayakspagrotodate+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
			
			
			$prodbudgetsfromswadayabi[$val['millcode']]+=($val['dailybudget'])/1000;
			@$prodbudgetstodatefromswadayabi[$val['millcode']]+=($val['kgolah'])/1000;
			
		}

		##To Day
		$tab.='
		<tr class=rowcontent>
		<td>To Day</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($prodactuals[$millcode]) {
				$persen=($prodactuals[$millcode]/$prodbudgets[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($prodactuals[$millcode],2).'</td>
			<td align=right>'.number_format($prodbudgets[$millcode],2).'</td>
			<td align=right>'.number_format($persen,2).'</td>
			<td></td>
			';
		}
		$persenkspagro=0;
		if ($prodactualskspagro) {
			$persenkspagro=($prodactualskspagro/$prodbudgetskspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($prodactualskspagro,2).'</td>
		<td align=right>'.number_format($prodbudgetskspagro,2).'</td>
		<td align=right>'.number_format(fixnan($persenkspagro),2).'</td>
		<td></td>
		';
		$tab.='
		</tr>';

		##To Day Transferred from Swadaya
		$tab.='
		<tr class=rowcontent>
		<td>To Day Transferred from Swadaya</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($prodactualsfromswadaya[$millcode]) {
				$persen=($prodactualsfromswadaya[$millcode]/$prodbudgetsfromswadaya[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($prodactualsfromswadaya[$millcode],2).'</td>
			<td align=right>'.number_format($prodbudgetsfromswadaya[$millcode],2).'</td>
			<td align=right>'.number_format(fixnan($persen),2).'</td>
			<td></td>
			';
		}
		$persenfromswadayakspagro=0;
		if ($prodactualsfromswadayakspagro) {
			$persenfromswadayakspagro=($prodactualsfromswadayakspagro/$prodbudgetsfromswadayakspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($prodactualsfromswadayakspagro,2).'</td>
		<td align=right>'.number_format($prodbudgetsfromswadayakspagro,2).'</td>
		<td align=right>'.number_format(fixnan($persenfromswadayakspagro),2).'</td>
		<td></td>
		';
		$tab.='
		</tr>';
	
		##Total tbs plasma to day
		$tab.='
		<tr class=rowcontent>
		<td><b>TOTAL</b></td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($prodactuals[$millcode] != 0 || $prodactualsfromswadaya[$millcode] != 0) {
				$persen=(($prodactuals[$millcode]+$prodactualsfromswadaya[$millcode])/$prodbudgets[$millcode]+$prodbudgetsfromswadaya[$millcode])*100;
			}
			$tab.='
			<td align=right><b>'.number_format($prodactuals[$millcode]+$prodactualsfromswadaya[$millcode],2).'</b></td>
			<td align=right><b>'.number_format($prodbudgets[$millcode]+$prodbudgetsfromswadaya[$millcode],2).'</b></td>
			<td align=right><b>'.number_format($persen,2).'</b></td>
			<td></td>
			';
		}
		$persenkspagro=0;
		if ($prodactualskspagro != 0 || $prodactualsfromswadayakspagro != 0) {
			$persenkspagro=(($prodactualskspagro+$prodactualsfromswadayakspagro)/($prodbudgetskspagro+$prodbudgetsfromswadayakspagro))*100;
		}
		$tab.='
		<td align=right><b>'.number_format($prodactualskspagro+$prodactualsfromswadayakspagro,2).'</b></td>
		<td align=right><b>'.number_format($prodbudgetskspagro+$prodbudgetsfromswadayakspagro,2).'</b></td>
		<td align=right><b>'.number_format($persenkspagro,2).'</b></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##To Date
		$tab.='
		<tr class=rowcontent>
		<td>To Date</td>';
		$persentodate=0;
		foreach ($millcodes as $millcode) {
			if ($prodactualstodate[$millcode]) {
				$persentodate=($prodactualstodate[$millcode]/$prodbudgetstodate[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($prodactualstodate[$millcode],2).'</td>
			<td align=right>'.number_format($prodbudgetstodate[$millcode],2).'</td>
			<td align=right>'.number_format($persentodate,2).'</td>
			<td></td>
			';
		}
		$persenkspagrotodate=0;
		if ($prodactualskspagrotodate) {
			$persenkspagrotodate=($prodactualskspagrotodate/$prodbudgetskspagrotodate)*100;
		}
		$tab.='
		<td align=right>'.number_format($prodactualskspagrotodate,2).'</td>
		<td align=right>'.number_format($prodbudgetskspagrotodate,2).'</td>
		<td align=right>'.number_format($persenkspagrotodate,2).'</td>
		<td></td>
		';
		$tab.='
		</tr>';


		##To Date Transferred from Swadaya
		$tab.='
		<tr class=rowcontent>
		<td>To Date Transferred from Swadaya</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($prodactualsfromswadayatodate[$millcode]) {
				$persen=($prodactualsfromswadayatodate[$millcode]/$prodbudgetstodatefromswadaya[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($prodactualsfromswadayatodate[$millcode],2).'</td>
			<td align=right>'.number_format($prodbudgetstodatefromswadaya[$millcode],2).'</td>
			<td align=right>'.number_format(fixnan($persen),2).'</td>
			<td></td>
			';
		}
		$persen=0;
		if ($prodactualsfromswadayatodatekspagro) {
			$persen=($prodactualsfromswadayatodatekspagro/$prodbudgetsfromswadayakspagrotodate)*100;
		}
		$tab.='
		<td align=right>'.number_format($prodactualsfromswadayatodatekspagro,2).'</td>
		<td align=right>'.number_format($prodbudgetsfromswadayakspagrotodate,2).'</td>
		<td align=right>'.number_format(fixnan($persen),2).'</td>
		<td></td>
		';
		$tab.='
		</tr>';
	
		##Total tbs plasma to date
		$tab.='
		<tr class=rowcontent>
		<td><b>TOTAL</b></td>';
		$persentodate=0;
		foreach ($millcodes as $millcode) {
			if ($prodactualstodate[$millcode] != 0 || $prodactualsfromswadayatodate[$millcode] != 0) {
				$persentodate=(($prodactualstodate[$millcode]+$prodactualsfromswadayatodate[$millcode])/($prodbudgetstodate[$millcode]+$prodbudgetstodatefromswadaya[$millcode]))*100;
			}
			$tab.='
			<td align=right><b>'.number_format($prodactualstodate[$millcode]+$prodactualsfromswadayatodate[$millcode],2).'</b></td>
			<td align=right><b>'.number_format($prodbudgetstodate[$millcode]+$prodbudgetstodatefromswadaya[$millcode],2).'</b></td>
			<td align=right><b>'.number_format(fixnan($persentodate),2).'</b></td>
			<td></td>
			';
		}
		$persenkspagrotodate=0;
		if ($prodactualskspagrotodate) {
			$persenkspagrotodate=($prodactualskspagrotodate/$prodbudgetskspagrotodate)*100;
		}
		$tab.='
		<td align=right><b>'.number_format($prodactualskspagrotodate,2).'</b></td>
		<td align=right><b>'.number_format($prodbudgetskspagrotodate,2).'</b></td>
		<td align=right><b>'.number_format($persenkspagrotodate,2).'</b></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##monthly budget
		
		$monthlybudget=[];
		$monthlybudgetkspagro=0;
		$str="select millcode, ".$monthbudget." as monthlybudget 
		from ".$dbname.".bgt_produksi_pks 
		where tahunbudget='".$tahun."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' and inti='0') and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$monthlybudget[$val['millcode']]+=$val['monthlybudget']/1000;
			$monthlybudgetkspagro+=$val['monthlybudget']/1000;
		}

		##yearly budget
		$yearlybudget=[];
		$yearlybudgetkspagro=0;
		$str="select millcode, kgolah as yearlybudget
		from ".$dbname.".bgt_produksi_pks
		where tahunbudget='".$tahun."' and kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where tipe = 'KEBUN' and inti='0') and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$yearlybudget[$val['millcode']]+=$val['yearlybudget']/1000;
			$yearlybudgetkspagro+=$val['yearlybudget']/1000;
		}
		
		##monthly budget
		$tab.='
		<tr class=rowcontent>
		<td>Monthly Budget</td>';
		//indraaaa
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($monthlybudget[$millcode]+$prodbudgetsfromswadayabi[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
			@$tmonthlybudget+=$monthlybudget[$millcode]+$prodbudgetsfromswadayabi[$millcode];
		}//<td align=right>'.number_format($monthlybudgetkspagro,2).'</td>
		$tab.='
		<td></td>
		
		<td align=right>'.number_format($tmonthlybudget,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##yearly budget
		$tab.='
		<tr class=rowcontent>
		<td>Yearly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($yearlybudget[$millcode]+$prodbudgetstodatefromswadayabi[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
			@$tyearlybudget+=$yearlybudget[$millcode]+$prodbudgetstodatefromswadayabi[$millcode];
		}//<td align=right>'.number_format($yearlybudgetkspagro,2).'</td>
		$tab.='
		<td></td>
		<td align=right>'.number_format($tyearlybudget,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';
		
		
		$tab.='
		<tr class=rowcontent>
		<td ></td>
		<td colspan=16></td>
		</tr>';


		#TOTAL TBS INTI PLASMA (TON)
		$tab.='
		<tr class=rowcontent>
		<td ><b>TOTAL TBS INTI PLASMA (TON)</b></td>
		<td colspan=16></td>
		</tr>';
		##prod budget to day
		$prodbudgets=[];
		$prodbudgetskspagro=0;
		$prodbudgetstodate=[];
		$prodbudgetskspagrotodate=0;
		$str="select millcode, ".$monthbudget." as dailybudget from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodbudgets[$val['millcode']]+=($val['dailybudget']/$jumlahhari)/1000;
			$prodbudgetskspagro+=($val['dailybudget']/$jumlahhari)/1000;

			$prodbudgetstodate[$val['millcode']]+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
			$prodbudgetskspagrotodate+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
		}

		##prod actual to day
		$prodactuals=array();
		$prodactualskspagro=0;
		$prodactuals=array();
		$str="select millcode,beratbersih from ".$dbname.".pabrik_timbangan where kodebarang = '40000003' and tanggal like '".$tanggal."%'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodactuals[$val['millcode']]+=$val['beratbersih']/1000;
			$prodactualskspagro+=$val['beratbersih']/1000;
		}

		##prod actual to date
		$prodactualstodate=array();
		$prodactualskspagrotodate=0;
		$str="select millcode,beratbersih from ".$dbname.".pabrik_timbangan where kodebarang = '40000003' and substr(tanggal,1,10) between '".$tanggal1."' and '".$tanggal."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$prodactualstodate[$val['millcode']]+=$val['beratbersih']/1000;
			$prodactualskspagrotodate+=$val['beratbersih']/1000;
		}

		##To Day
		$tab.='
		<tr class=rowcontent>
		<td>To Day</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($prodactuals[$millcode]) {
				$persen=($prodactuals[$millcode]/$prodbudgets[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($prodactuals[$millcode],2).'</td>
			<td align=right>'.number_format($prodbudgets[$millcode],2).'</td>
			<td align=right>'.number_format($persen,2).'</td>
			<td></td>
			';
		}
		$persenkspagro=0;
		if ($prodactualskspagro) {
			$persenkspagro=($prodactualskspagro/$prodbudgetskspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($prodactualskspagro,2).'</td>
		<td align=right>'.number_format($prodbudgetskspagro,2).'</td>
		<td align=right>'.number_format($persenkspagro,2).'</td>
		<td></td>
		';
		$tab.='
		</tr>';

		##To Date
		$tab.='
		<tr class=rowcontent>
		<td>To Date</td>';
		$persentodate=0;
		foreach ($millcodes as $millcode) {
			if ($prodactualstodate[$millcode]) {
				$persentodate=($prodactualstodate[$millcode]/$prodbudgetstodate[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($prodactualstodate[$millcode],2).'</td>
			<td align=right>'.number_format($prodbudgetstodate[$millcode],2).'</td>
			<td align=right>'.number_format($persentodate,2).'</td>
			<td></td>
			';
		}
		$persenkspagrotodate=0;
		if ($prodactualskspagrotodate) {
			$persenkspagrotodate=($prodactualskspagrotodate/$prodbudgetskspagrotodate)*100;
		}
		$tab.='
		<td align=right>'.number_format($prodactualskspagrotodate,2).'</td>
		<td align=right>'.number_format($prodbudgetskspagrotodate,2).'</td>
		<td align=right>'.number_format($persenkspagrotodate,2).'</td>
		<td></td>
		';
		$tab.='
		</tr>';

		##monthly budget
		$monthlybudget=[];
		$monthlybudgetkspagro=0;
		$str="select millcode, ".$monthbudget." as monthlybudget 
		from ".$dbname.".bgt_produksi_pks 
		where tahunbudget='".$tahun."'
		and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$monthlybudget[$val['millcode']]+=$val['monthlybudget']/1000;
			$monthlybudgetkspagro+=$val['monthlybudget']/1000;
		}

		##yearly budget
		$yearlybudget=[];
		$yearlybudgetkspagro=0;
		$str="select millcode, kgolah as yearlybudget
		from ".$dbname.".bgt_produksi_pks
		where tahunbudget='".$tahun."'
		and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$yearlybudget[$val['millcode']]+=$val['yearlybudget']/1000;
			$yearlybudgetkspagro+=$val['yearlybudget']/1000;
		}
		
		##monthly budget
		$tab.='
		<tr class=rowcontent>
		<td>Monthly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($monthlybudget[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($monthlybudgetkspagro,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##yearly budget
		$tab.='
		<tr class=rowcontent>
		<td>Yearly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($yearlybudget[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($yearlybudgetkspagro,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';
		
		$tab.='
		<tr class=rowcontent>
		<td ></td>
		<td colspan=16></td>
		</tr>';

		##total tbs processed
		$tab.='
		<tr class=rowcontent>
		<td ><b>TOTAL TBS PROCESSED (TON)</b></td>
		<td colspan=16></td>
		</tr>';

		##tbs processed To Day
		//$processactual=[];
		$processactualkspagro=0;
		$str="select kodeorg,tbsdiolah from ".$dbname.".pabrik_produksi where tanggal='".$tanggal."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$processactual[$val['kodeorg']]=$val['tbsdiolah']/1000;
			$processactualkspagro+=$val['tbsdiolah']/1000;
		}

		##tbs processed To Date
		//$processactualtodate=[];
		$processactualkspagrotodate=0;
		$str="select kodeorg,tbsdiolah from ".$dbname.".pabrik_produksi where substr(tanggal,1,10) between '".$tanggal1."' and '".$tanggal."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$processactualtodate[$val['kodeorg']]+=$val['tbsdiolah']/1000;
			$processactualkspagrotodate+=$val['tbsdiolah']/1000;
		}

		##process budget to day
		//$processbudgets=[];
		//$processbudgetstodate=[];
		$processbudgetskspagro=0;
		$processbudgetstodatekspagro=0;
		$str="select millcode, ".$monthbudget." as dailybudget from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$processbudgets[$val['millcode']]+=($val['dailybudget']/$jumlahhari)/1000;
			$processbudgetskspagro+=($val['dailybudget']/$jumlahhari)/1000;

			$processbudgetstodate[$val['millcode']]+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
			$processbudgetstodatekspagro+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
		}
		
		
		
		#= query ambil data untuk kebutuhan utility
		/*
		to day : tbs olah today / ( troughput(30ton) * 20 (jam olah hari) ) * 100
		todate : tbs olah todate / ( troughput(30ton) * 20 (jam olah hari) * 24(HKE perbulan) ) * 100
		*/
		$str="SELECT * from ".$dbname.".pabrik_5defaultolah";
		$res=fetchdata($str);
		foreach ($res as $bar) {
			$dtkapasitas[$bar['kodeunit']]=$bar['kapasitas']/1000; //karna satuan ton
			$dtjamolah[$bar['kodeunit']]=$bar['jamolah'];
		}
		
	
		##To Day
		$tab.='
		<tr class=rowcontent>
		<td>To Day</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($processactual[$millcode]) {
				$persen=($processactual[$millcode]/$processbudgets[$millcode])*100;
			}
			$util=$processactual[$millcode]/($dtkapasitas[$millcode]*$dtjamolah[$millcode])*100;
			@$tutilhi+=$util;
			
			
			$tab.='
			<td align=right>'.number_format($processactual[$millcode],2).'</td>
			<td align=right>'.number_format($processbudgets[$millcode],2).'</td>
			<td align=right>'.number_format($persen,2).'</td>
			<td align=right>'.hidezerodecimal(fixnan($util),2).'</td>
		
			';
		}
		$persenkspagro=0;
		if ($processactualkspagro) {
			$persenkspagro=($processactualkspagro/$processbudgetskspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($processactualkspagro,2).'</td>
		<td align=right>'.number_format($processbudgetskspagro,2).'</td>
		<td align=right>'.number_format($persenkspagro,2).'</td>
		<td align=right>'.hidezerodecimal(fixnan($tutilhi/$jumlahpks),2).'</td>
		';
		$tab.='</tr>';


		##To Date
		$tab.='
		<tr class=rowcontent>
		<td>To Date</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($processactualtodate[$millcode]) {
				$persen=($processactualtodate[$millcode]/$processbudgetstodate[$millcode])*100;
			}
			//<td align=right>'.hidezerodecimal(fixnan($util),2).' - '.$dtkapasitas[$millcode].'_'.$dtjamolah[$millcode].'_'.$jumlahhke.'</td>
			$jumlahhke=hketanggal($tanggal1,$tanggal,$millcode);
			$util=$processactualtodate[$millcode]/($dtkapasitas[$millcode]*$dtjamolah[$millcode]*$jumlahhke)*100;
			@$tutilsdhi+=$util;
			
			$tab.='
			<td align=right>'.number_format($processactualtodate[$millcode],2).'</td>
			<td align=right>'.number_format($processbudgetstodate[$millcode],2).'</td>
			<td align=right>'.number_format($persen,2).'</td>
			<td align=right>'.hidezerodecimal(fixnan($util),2).'</td>
			';
		}
		$persenkspagro=0;
		if ($processactualkspagrotodate) {
			$persenkspagro=($processactualkspagrotodate/$processbudgetstodatekspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($processactualkspagrotodate,2).'</td>
		<td align=right>'.number_format($processbudgetstodatekspagro,2).'</td>
		<td align=right>'.number_format($persenkspagro,2).'</td>
		<td align=right>'.hidezerodecimal(fixnan($tutilsdhi/$jumlahpks),2).'</td>
		';
		$tab.='</tr>';

		##monthly budget
		//$processmonthlybudget=[];
		$processmonthlybudgetkspagro=0;
		$str="select millcode, ".$monthbudget." as monthlybudget 
		from ".$dbname.".bgt_produksi_pks 
		where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$processmonthlybudget[$val['millcode']]+=$val['monthlybudget']/1000;
			$processmonthlybudgetkspagro+=$val['monthlybudget']/1000;
		}

		##yearly budget
		//$processyearlybudget=[];
		$processyearlybudgetkspagro=0;
		$str="select millcode, kgolah as yearlybudget
		from ".$dbname.".bgt_produksi_pks
		where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$processyearlybudget[$val['millcode']]+=$val['yearlybudget']/1000;
			$processyearlybudgetkspagro+=$val['yearlybudget']/1000;
		}
		
		##monthly budget
		$tab.='
		<tr class=rowcontent>
		<td>Monthly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($processmonthlybudget[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($processmonthlybudgetkspagro,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##yearly budget
		$tab.='
		<tr class=rowcontent>
		<td>Yearly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($processyearlybudget[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($processyearlybudgetkspagro,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##total cpo
		$tab.='
		<tr class=rowcontent>
		<td ><b>TOTAL CPO (TON)</b></td>
		<td colspan=16></td>
		</tr>';

		##cpo To Day
		//$cpoactual=[];
		$cpoactualkspagro=0;
		$str="select kodeorg,oer from ".$dbname.".pabrik_produksi where tanggal='".$tanggal."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$cpoactual[$val['kodeorg']]=$val['oer']/1000;
			$cpoactualkspagro+=$val['oer']/1000;
		}

		##cpo To Date
		//$cpoactualtodate=[];
		$cpoactualkspagrotodate=0;
		$str="select kodeorg,oer from ".$dbname.".pabrik_produksi where substr(tanggal,1,10) between '".$tanggal1."' and '".$tanggal."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$cpoactualtodate[$val['kodeorg']]+=$val['oer']/1000;
			$cpoactualkspagrotodate+=$val['oer']/1000;
		}

		##cpo budget to day
		//$cpobudgets=[];
		//$cpobudgetstodate=[];
		$cpobudgetskspagro=0;
		$cpobudgetstodatekspagro=0;
		$str="select millcode, ".$cpobudget." as dailybudget from ".$dbname.".bgt_produksi_pks_vw where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$cpobudgets[$val['millcode']]+=($val['dailybudget']/$jumlahhari)/1000;
			$cpobudgetskspagro+=($val['dailybudget']/$jumlahhari)/1000;

			$cpobudgetstodate[$val['millcode']]+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
			$cpobudgetstodatekspagro+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
		}

		##To Day
		$tab.='
		<tr class=rowcontent>
		<td>To Day</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($cpoactual[$millcode]) {
				$persen=($cpoactual[$millcode]/$cpobudgets[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($cpoactual[$millcode],2).'</td>
			<td align=right>'.number_format($cpobudgets[$millcode],2).'</td>
			<td align=right>'.number_format($persen,2).'</td>
			<td align=right></td>
			';
		}
		$persenkspagro=0;
		if ($cpoactualkspagro) {
			$persenkspagro=($cpoactualkspagro/$cpobudgetskspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($cpoactualkspagro,2).'</td>
		<td align=right>'.number_format($cpobudgetskspagro,2).'</td>
		<td align=right>'.number_format($persenkspagro,2).'</td>
		<td align=right></td>
		';
		$tab.='</tr>';


		##To Date
		$tab.='
		<tr class=rowcontent>
		<td>To Date</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($cpoactualtodate[$millcode]) {
				$persen=($cpoactualtodate[$millcode]/$cpobudgetstodate[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($cpoactualtodate[$millcode],2).'</td>
			<td align=right>'.number_format($cpobudgetstodate[$millcode],2).'</td>
			<td align=right>'.number_format($persen,2).'</td>
			<td align=right></td>
			';
		}
		$persenkspagro=0;
		if ($cpoactualkspagrotodate) {
			$persenkspagro=($cpoactualkspagrotodate/$cpobudgetstodatekspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($cpoactualkspagrotodate,2).'</td>
		<td align=right>'.number_format($cpobudgetstodatekspagro,2).'</td>
		<td align=right>'.number_format($persenkspagro,2).'</td>
		<td align=right></td>
		';
		$tab.='</tr>';

		##monthly budget
		//$cpomonthlybudget=[];
		$cpomonthlybudgetkspagro=0;
		$str="select millcode, ".$cpobudget." as monthlybudget 
		from ".$dbname.".bgt_produksi_pks_vw 
		where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$cpomonthlybudget[$val['millcode']]+=$val['monthlybudget']/1000;
			$cpomonthlybudgetkspagro+=$val['monthlybudget']/1000;
		}

		##yearly budget
		//$cpoyearlybudget=[];
		$cpoyearlybudgetkspagro=0;
		$str="select millcode, kgcpo as yearlybudget
		from ".$dbname.".bgt_produksi_pks_vw
		where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$cpoyearlybudget[$val['millcode']]+=$val['yearlybudget']/1000;
			$cpoyearlybudgetkspagro+=$val['yearlybudget']/1000;
		}
		
		##monthly budget
		$tab.='
		<tr class=rowcontent>
		<td>Monthly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($cpomonthlybudget[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($cpomonthlybudgetkspagro,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##yearly budget
		$tab.='
		<tr class=rowcontent>
		<td>Yearly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($cpoyearlybudget[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($cpoyearlybudgetkspagro,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##total PK
		$tab.='
		<tr class=rowcontent>
		<td ><b>TOTAL PK (TON)</b></td>
		<td colspan=16></td>
		</tr>';

		##pk To Day
		//$pkactual=[];
		$pkactualkspagro=0;
		$str="select kodeorg,oerpk from ".$dbname.".pabrik_produksi where tanggal='".$tanggal."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$pkactual[$val['kodeorg']]=$val['oerpk']/1000;
			$pkactualkspagro+=$val['oerpk']/1000;
		}

		##pk To Date
		//$pkactualtodate=[];
		$pkactualkspagrotodate=0;
		$str="select kodeorg,oerpk from ".$dbname.".pabrik_produksi where substr(tanggal,1,10) between '".$tanggal1."' and '".$tanggal."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$pkactualtodate[$val['kodeorg']]+=$val['oerpk']/1000;
			$pkactualkspagrotodate+=$val['oerpk']/1000;
		}

		##pk budget to day
		//$pkbudgets=[];
		//$pkbudgetstodate=[];
		$pkbudgetskspagro=0;
		$pkbudgetstodatekspagro=0;
		$str="select millcode, ".$pkbudget." as dailybudget from ".$dbname.".bgt_produksi_pks_vw where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$pkbudgets[$val['millcode']]+=($val['dailybudget']/$jumlahhari)/1000;
			$pkbudgetskspagro+=($val['dailybudget']/$jumlahhari)/1000;

			$pkbudgetstodate[$val['millcode']]+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
			$pkbudgetstodatekspagro+=(($val['dailybudget']/$jumlahhari)*$jumlahharitodate)/1000;
		}

		##To Day
		$tab.='
		<tr class=rowcontent>
		<td>To Day</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($pkactual[$millcode]) {
				$persen=($pkactual[$millcode]/$pkbudgets[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($pkactual[$millcode],2).'</td>
			<td align=right>'.number_format($pkbudgets[$millcode],2).'</td>
			<td align=right>'.number_format($persen,2).'</td>
			<td align=right></td>
			';
		}
		$persenkspagro=0;
		if ($pkactualkspagro) {
			$persenkspagro=($pkactualkspagro/$pkbudgetskspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($pkactualkspagro,2).'</td>
		<td align=right>'.number_format($pkbudgetskspagro,2).'</td>
		<td align=right>'.number_format($persenkspagro,2).'</td>
		<td align=right></td>
		';
		$tab.='</tr>';


		##To Date
		$tab.='
		<tr class=rowcontent>
		<td>To Date</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($pkactualtodate[$millcode]) {
				$persen=($pkactualtodate[$millcode]/$pkbudgetstodate[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($pkactualtodate[$millcode],2).'</td>
			<td align=right>'.number_format($pkbudgetstodate[$millcode],2).'</td>
			<td align=right>'.number_format($persen,2).'</td>
			<td align=right></td>
			';
		}
		$persenkspagro=0;
		if ($pkactualkspagrotodate) {
			$persenkspagro=($pkactualkspagrotodate/$pkbudgetstodatekspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($pkactualkspagrotodate,2).'</td>
		<td align=right>'.number_format($pkbudgetstodatekspagro,2).'</td>
		<td align=right>'.number_format($persenkspagro,2).'</td>
		<td align=right></td>
		';
		$tab.='</tr>';

		##monthly budget
		//$pkmonthlybudget=[];
		$pkmonthlybudgetkspagro=0;
		$str="select millcode, ".$pkbudget." as monthlybudget from ".$dbname.".bgt_produksi_pks_vw where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$pkmonthlybudget[$val['millcode']]+=$val['monthlybudget']/1000;
			$pkmonthlybudgetkspagro+=$val['monthlybudget']/1000;
		}

		##yearly budget
		//$pkyearlybudget=[];
		$pkyearlybudgetkspagro=0;
		$str="select millcode, kgkernel as yearlybudget from ".$dbname.".bgt_produksi_pks_vw where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$pkyearlybudget[$val['millcode']]+=$val['yearlybudget']/1000;
			$pkyearlybudgetkspagro+=$val['yearlybudget']/1000;
		}
		
		##monthly budget
		$tab.='
		<tr class=rowcontent>
		<td>Monthly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($pkmonthlybudget[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($pkmonthlybudgetkspagro,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##yearly budget
		$tab.='
		<tr class=rowcontent>
		<td>Yearly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($pkyearlybudget[$millcode],2).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($pkyearlybudgetkspagro,2).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';


		##OER
		$tab.='
		<tr class=rowcontent>
		<td ><b>OER (%)</b></td>
		<td colspan=16></td>
		</tr>';

		##oer To Day
		//$oeractual=[];
		$oeractualkspagro=0;
		$str="select kodeorg,(sum(oer)/sum(tbsdiolah))*100 as oer from ".$dbname.".pabrik_produksi where tanggal='".$tanggal."' group by kodeorg";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$oeractual[$val['kodeorg']]=$val['oer'];
			$oeractualkspagro=$val['oer'];
		}

		##oer To Date
		//$oeractualtodate=[];
		$oeractualkspagrotodate=0;
		$str="select kodeorg,(sum(oer)/sum(tbsdiolah))*100 as oer from ".$dbname.".pabrik_produksi where substr(tanggal,1,10) between '".$tanggal1."' and '".$tanggal."' group by kodeorg";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$oeractualtodate[$val['kodeorg']]=$val['oer'];
			$oeractualkspagrotodate=$val['oer'];
		}



		##oer budget to day
		//$oerbudgets=[];
		//$oerbudgetstodate=[];
		$oerbudgetskspagro=0;
		$oerbudgetstodatekspagro=0;
		$str="select millcode, oerbunch as dailybudget from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$oerbudgets[$val['millcode']]=$val['dailybudget'];
			$oerbudgetskspagro=$val['dailybudget'];

			$oerbudgetstodate[$val['millcode']]=$val['dailybudget'];
			$oerbudgetstodatekspagro=$val['dailybudget'];
		}

		##To Day
		$tab.='
		<tr class=rowcontent>
		<td>To Day</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($oeractual[$millcode]) {
				$persen=($oeractual[$millcode]/$oerbudgets[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($oeractual[$millcode],1).'</td>
			<td align=right>'.number_format($oerbudgets[$millcode],1).'</td>
			<td align=right>'.number_format($persen,1).'</td>
			<td align=right></td>
			';
		}
		$persenkspagro=0;
		if ($oeractualkspagro) {
			$persenkspagro=($oeractualkspagro/$oerbudgetskspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($oeractualkspagro,1).'</td>
		<td align=right>'.number_format($oerbudgetskspagro,1).'</td>
		<td align=right>'.number_format($persenkspagro,1).'</td>
		<td align=right></td>
		';
		$tab.='</tr>';


		##To Date
		$tab.='
		<tr class=rowcontent>
		<td>To Date</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($oeractualtodate[$millcode]) {
				$persen=($oeractualtodate[$millcode]/$oerbudgetstodate[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($oeractualtodate[$millcode],1).'</td>
			<td align=right>'.number_format($oerbudgetstodate[$millcode],1).'</td>
			<td align=right>'.number_format($persen).'</td>
			<td align=right></td>
			';
		}
		$persenkspagro=0;
		if ($oeractualkspagrotodate) {
			$persenkspagro=($oeractualkspagrotodate/$oerbudgetstodatekspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($oeractualkspagrotodate,1).'</td>
		<td align=right>'.number_format($oerbudgetstodatekspagro,1).'</td>
		<td align=right>'.number_format($persenkspagro).'</td>
		<td align=right></td>
		';
		$tab.='</tr>';

		##monthly budget
		//$oermonthlybudget=[];
		$oermonthlybudgetkspagro=0;
		$str="select millcode, oerbunch as monthlybudget from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$oermonthlybudget[$val['millcode']]=$val['monthlybudget'];
			$oermonthlybudgetkspagro=$val['monthlybudget'];
		}

		##yearly budget
		//$oeryearlybudget=[];
		$oeryearlybudgetkspagro=0;
		$str="select millcode, oerbunch as yearlybudget from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$oeryearlybudget[$val['millcode']]=$val['yearlybudget'];
			$oeryearlybudgetkspagro=$val['yearlybudget'];
		}
		
		##monthly budget
		$tab.='
		<tr class=rowcontent>
		<td>Monthly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($oermonthlybudget[$millcode],1).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($oermonthlybudgetkspagro,1).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##yearly budget
		$tab.='
		<tr class=rowcontent>
		<td>Yearly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($oeryearlybudget[$millcode]).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($oeryearlybudgetkspagro).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';


		$tab.='
		<tr class=rowcontent>
		<td><b>KER (%)</b></td>
		<td colspan=16></td>
		</tr>';

		##oer To Day
		//$keractual=[];
		$keractualkspagro=0;
		$str="select kodeorg,(sum(oerpk)/sum(tbsdiolah))*100 as ker from ".$dbname.".pabrik_produksi where tanggal='".$tanggal."' group by kodeorg";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$keractual[$val['kodeorg']]=$val['ker'];
			$keractualkspagro=$val['ker'];
		}

		##oer To Date
		//$keractualtodate=[];
		$keractualkspagrotodate=0;
		$str="select kodeorg,(sum(oerpk)/sum(tbsdiolah))*100 as ker from ".$dbname.".pabrik_produksi where substr(tanggal,1,10) between '".$tanggal1."' and '".$tanggal."' group by kodeorg";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$keractualtodate[$val['kodeorg']]=$val['ker'];
			$keractualkspagrotodate=$val['ker'];
		}

		##oer budget to day
		//$kerbudgets=[];
		//$kerbudgetstodate=[];
		$kerbudgetskspagro=0;
		$kerbudgetstodatekspagro=0;
		$str="select millcode, oerkernel as dailybudget from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$kerbudgets[$val['millcode']]=$val['dailybudget'];
			$kerbudgetskspagro=$val['dailybudget'];

			$kerbudgetstodate[$val['millcode']]=$val['dailybudget'];
			$kerbudgetstodatekspagro=$val['dailybudget'];
		}

		##To Day
		$tab.='
		<tr class=rowcontent>
		<td>To Day</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($keractual[$millcode]) {
				$persen=($keractual[$millcode]/$kerbudgets[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($keractual[$millcode],1).'</td>
			<td align=right>'.number_format($kerbudgets[$millcode],1).'</td>
			<td align=right>'.number_format($persen).'</td>
			<td align=right></td>
			';
		}
		$persenkspagro=0;
		if ($keractualkspagro) {
			$persenkspagro=($keractualkspagro/$kerbudgetskspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($keractualkspagro,1).'</td>
		<td align=right>'.number_format($kerbudgetskspagro,1).'</td>
		<td align=right>'.number_format($persenkspagro).'</td>
		<td align=right></td>
		';
		$tab.='</tr>';


		##To Date
		$tab.='
		<tr class=rowcontent>
		<td>To Date</td>';
		$persen=0;
		foreach ($millcodes as $millcode) {
			if ($keractualtodate[$millcode]) {
				$persen=($keractualtodate[$millcode]/$kerbudgetstodate[$millcode])*100;
			}
			$tab.='
			<td align=right>'.number_format($keractualtodate[$millcode],1).'</td>
			<td align=right>'.number_format($kerbudgetstodate[$millcode],1).'</td>
			<td align=right>'.number_format($persen).'</td>
			<td align=right></td>
			';
		}
		$persenkspagro=0;
		if ($keractualkspagrotodate) {
			$persenkspagro=($keractualkspagrotodate/$kerbudgetstodatekspagro)*100;
		}
		$tab.='
		<td align=right>'.number_format($keractualkspagrotodate,1).'</td>
		<td align=right>'.number_format($kerbudgetstodatekspagro,1).'</td>
		<td align=right>'.number_format($persenkspagro).'</td>
		<td align=right></td>
		';
		$tab.='</tr>';

		##monthly budget
		//$kermonthlybudget=[];
		$kermonthlybudgetkspagro=0;
		$str="select millcode, oerkernel as monthlybudget from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$kermonthlybudget[$val['millcode']]=$val['monthlybudget'];
			$kermonthlybudgetkspagro=$val['monthlybudget'];
		}

		##yearly budget
		//$keryearlybudget=[];
		$keryearlybudgetkspagro=0;
		$str="select millcode, oerkernel as yearlybudget from ".$dbname.".bgt_produksi_pks where tahunbudget='".$tahun."' and millcode in (SELECT kodeorganisasi FROM ".$dbname.".organisasi WHERE `tipe` = 'PABRIK')";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$keryearlybudget[$val['millcode']]=$val['yearlybudget'];
			$keryearlybudgetkspagro=$val['yearlybudget'];
		}
		
		##monthly budget
		$tab.='
		<tr class=rowcontent>
		<td>Monthly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($kermonthlybudget[$millcode],1).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($kermonthlybudgetkspagro,1).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';

		##yearly budget
		$tab.='
		<tr class=rowcontent>
		<td>Yearly Budget</td>';
		
		foreach ($millcodes as $millcode) {
			$tab.='
			<td></td>
			<td align=right>'.number_format($keryearlybudget[$millcode]).'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td></td>
		<td align=right>'.number_format($keryearlybudgetkspagro).'</td>
		<td></td>
		<td></td>
		';
		$tab.='
		</tr>';

		
		##ffa prod To Day
		//$ffaactual=[];
		$str="select kodeorg, ffa from ".$dbname.".pabrik_produksi where tanggal='".$tanggal."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$ffaactual[$val['kodeorg']]=$val['ffa'];
		}	
		

		##FFA PROD
		$tab.='
		<tr class=rowcontent>
		<td ><b>FFA PROD (%)</b></td>
		<td colspan=16></td>
		</tr>';

		##To Day
		$tab.='
		<tr class=rowcontent>
		<td>To Day</td>';
		foreach ($millcodes as $millcode) {
			$tab.='
			<td align=right>'.$ffaactual[$millcode].'</td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			';
		}

		$tab.='
		<td align=right></td>
		<td align=right></td>
		<td align=right></td>
		<td align=right></td>
		';
		$tab.='</tr>';

		##To Date
		$tab.='
		<tr class=rowcontent>
		<td>To Date</td>';
		foreach ($millcodes as $millcode) {
			$tab.='
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			<td align=right></td>
			';
		}

		$tab.='
		<td align=right></td>
		<td align=right></td>
		<td align=right></td>
		<td align=right></td>
		';
		$tab.='</tr>';



		##STOCK CPO (TON)
		$tab.='
		<tr class=rowcontent style="background-color:#275370;color:#A5C1D6">
		<td rowspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>STOCK CPO (TON)</b></td>
		<td colspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA < 5% AFS</b></td>
		<td rowspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA > 5%</b></td>
		<td rowspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA TANK</b></td>

		<td colspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA < 5% AFS</b></td>
		<td rowspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA > 5%</b></td>
		<td rowspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA TANK</b></td>

		<td colspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA < 5% AFS</b></td>
		<td rowspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA > 5%</b></td>
		<td rowspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA TANK</b></td>

		<td colspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA < 5% AFS</b></td>
		<td rowspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA > 5%</b></td>
		<td rowspan=2 style="background-color:#275370;color:#A5C1D6" align=center><b>FFA TANK</b></td>
		</tr>
		<tr class=rowcontent style="background-color:#275370;color:#A5C1D6">
		<td style="background-color:#275370;color:#A5C1D6" align=center>AFS</td>
		<td style="background-color:#275370;color:#A5C1D6" align=center>RFS</td>
		<td style="background-color:#275370;color:#A5C1D6" align=center>AFS</td>
		<td style="background-color:#275370;color:#A5C1D6" align=center>RFS</td>
		<td style="background-color:#275370;color:#A5C1D6" align=center>AFS</td>
		<td style="background-color:#275370;color:#A5C1D6" align=center>RFS</td>
		<td style="background-color:#275370;color:#A5C1D6" align=center>AFS</td>
		<td style="background-color:#275370;color:#A5C1D6" align=center>RFS</td>
		</tr>';

		$str="select kodetangki,komoditi,keterangan from ".$dbname.".pabrik_5tangki where komoditi='CPO' order by kodetangki";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$tangkicpo[$val['kodetangki']]=$val['kodetangki'];
			$nmtangkicpo[$val['kodetangki']]=$val['keterangan'];
		}

		$str="select kodetangki,komoditi,keterangan from ".$dbname.".pabrik_5tangki where komoditi='KER' order by kodetangki";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$tangkikernel[$val['kodetangki']]=$val['kodetangki'];
			$nmtangkikernel[$val['kodetangki']]=$val['keterangan'];
		}
		
		$str="select * from ".$dbname.".pabrik_stokforsale where tanggal='".$tanggal."'";
		// echo $str;
		$res=fetchdata($str);
		foreach ($res as $val) {
			$ac[$val['kodetangki']][$val['kodeorg']]=$val['alreadycontract'];
			$rfs[$val['kodetangki']][$val['kodeorg']]=$val['readyforsale'];
			$upper5persen[$val['kodetangki']][$val['kodeorg']]=$val['upper5persen'];
			$inprocessac[$val['kodetangki']][$val['kodeorg']]=$val['inprocessac'];
			$inprocessrfs[$val['kodetangki']][$val['kodeorg']]=$val['inprocessrfs'];
			$sold[$val['kodetangki']][$val['kodeorg']]=$val['sold'];

			$inprocessackspagro+=$val['inprocessac'];
			$inprocessrfskspagro+=$val['inprocessrfs'];

			$ackspagro[$val['kodetangki']]+=$val['alreadycontract'];
			$rfskspagro[$val['kodetangki']]+=$val['readyforsale'];
			$upper5persenkspagro[$val['kodetangki']]+=$val['upper5persen'];
			$soldkspagro[$val['kodetangki']]+=$val['sold'];


			$ttlactualstok[$val['kodeorg']]+=$val['upper5persen']; 
			$ttlsoldac[$val['kodeorg']]+=$val['alreadycontract'];
			$ttlsoldrfs[$val['kodeorg']]+=$val['readyforsale'];  //ngaco

			$ttlactualstokkspagro+=$val['upper5persen'];
			$ttlsoldackspagro+=$val['alreadycontract']; 
			$ttlsoldrfskspagro+=$val['readyforsale']; 
		}

		// echo"<pre>";
		// print_r($ttlsoldrfs);
		// echo"</pre>";

		$str="select kodeorg,kodetangki,cpoffa,kernelkdair from ".$dbname.".pabrik_masukkeluartangki where tanggal ='".$tanggal."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$ffatank[$val['kodetangki']][$val['kodeorg']]=$val['cpoffa'];

			$moisttank[$val['kodetangki']][$val['kodeorg']]=$val['kernelkdair'];
		}

		foreach ($tangkicpo as $cpotangki) {
			$tab.='<tr class=rowcontent>
			<td>'.$cpotangki.' - '.$nmtangkicpo[$cpotangki].'</td>
			';

			foreach ($millcodes as $millcode) {
				$tab.='
				<td align=right>'.$ac[$cpotangki][$millcode].'</td>
				<td align=right>'.$rfs[$cpotangki][$millcode].'</td>
				<td align=right>'.$upper5persen[$cpotangki][$millcode].'</td>
				<td align=right>'.number_format($ffatank[$cpotangki][$millcode],1).'</td>';
				
				
				@$trfs[$millcode]+=$rfs[$cpotangki][$millcode];
			}

			$tab.='
			<td align=right>'.$ackspagro[$cpotangki].'</td>
			<td align=right>'.$rfskspagro[$cpotangki].'</td>
			<td align=right>'.$upper5persenkspagro[$cpotangki].'</td>
			<td></td>';

			$tab.='</tr>';
		}
		$tab.='<tr class=rowcontent>
		<td>IN PROCESS</td>';
		foreach ($millcodes as $millcode) {
			$tab.='
			<td align=right>'.$inprocessac['INP'][$millcode].'</td>
			<td align=right>'.$inprocessrfs['INP'][$millcode].'</td>
			<td></td>
			<td></td>
			';
		}
		$tab.='
		<td align=right>'.$inprocessackspagro.'</td>
		<td align=right>'.$inprocessrfskspagro.'</td>
		<td></td>
		<td></td>
		';
		$tab.='</tr>';

		$tab.='
		<tr class=rowcontent style="background-color:#275370;color:#A5C1D6">
		<td style="background-color:#275370;color:#A5C1D6"><b>TOTAL ACTUAL STOCK</b></td>';
		foreach ($millcodes as $millcode) {
			$tab.='
			<td style="background-color:#275370;color:#A5C1D6"></td>
			<td style="background-color:#275370;color:#A5C1D6"></td>
			<td align=right style="background-color:#275370;color:#A5C1D6"><b>'.$ttlactualstok[$millcode].'</b></td>
			<td style="background-color:#275370;color:#A5C1D6"></td>
			';
		}
		$tab.='
		<td style="background-color:#275370;color:#A5C1D6"></td>
		<td style="background-color:#275370;color:#A5C1D6"></td>
		<td align=right style="background-color:#275370;color:#A5C1D6"><b>'.$ttlactualstokkspagro.'</b></td>
		<td style="background-color:#275370;color:#A5C1D6"></td>';
		$tab.='</tr>';

		$tab.='
		<tr class=rowcontent style="background-color:#275370;color:#A5C1D6">
		<td style="background-color:#275370;color:#A5C1D6"><b>TOTAL AFS DAN RFS</b></td>';
		foreach ($millcodes as $millcode) {
			$tab.='
			<td align=right style="background-color:#275370;color:#A5C1D6"><b>'.$ttlsoldac[$millcode].'</b></td>
			<td align=right style="background-color:#275370;color:#A5C1D6"><b>'.$trfs[$millcode].'</b></td>
			<td style="background-color:#275370;color:#A5C1D6"></td>
			<td style="background-color:#275370;color:#A5C1D6"></td>
			';
		}
		$tab.='
		<td align=right style="background-color:#275370;color:#A5C1D6"><b>'.$ttlsoldackspagro.'</b></td>
		<td align=right style="background-color:#275370;color:#A5C1D6"><b>'.$ttlsoldrfskspagro.'</b></td>
		<td style="background-color:#275370;color:#A5C1D6"></td>
		<td style="background-color:#275370;color:#A5C1D6"></td>';
		$tab.='</tr>';


		$str="select * from ".$dbnmae.".pmn_bapengiriman where tanggal = '".$tanggal."'";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$nokontrak[$val['unit']][$val['kodebarang']]=$val['nokontrak'];
			$keterangan[$val['unit']][$val['kodebarang']]=$val['keteranganht'];
			$jumlahkirim[$val['unit']][$val['kodebarang']]+=$val['jumlah'];
			$baris[$val['unit']][$val['kodebarang']]+=1;
		}


		##contract
		$tab.='<tr class=rowcontent>
		<td><b>CONTRACT</b></td>';

		foreach ($millcodes as $millcode) {
			if ($baris[$millcode]['40000001'] > 0) {
				$tab.='<td colspan=4>'.$keterangan[$millcode]['40000001'].'</br> Total kirim : '.($jumlahkirim[$millcode]['40000001']/1000).' Ton</td>';
			}else{
				$tab.='<td colspan=4></td>';
			}
		}
		
		$tab.='<td colspan=4></td>';
		$tab.='</tr>';

		##STOCK KERNEL (TON)
		$tab.='
		<tr class=rowcontent style="background-color:#275370;color:#A5C1D6">
		<td rowspan=2 align=center style="background-color:#275370;color:#A5C1D6"><b>STOCK KERNEL (TON)</b></td>
		<td colspan=2 align=center style="background-color:#275370;color:#A5C1D6">STOCK</td>
		<td colspan=2 align=center style="background-color:#275370;color:#A5C1D6">%</td>
		<td colspan=2 align=center style="background-color:#275370;color:#A5C1D6">STOCK</td>
		<td colspan=2 align=center style="background-color:#275370;color:#A5C1D6">%</td>
		<td colspan=2 align=center style="background-color:#275370;color:#A5C1D6">STOCK</td>
		<td colspan=2 align=center style="background-color:#275370;color:#A5C1D6">%</td>
		<td colspan=2 align=center style="background-color:#275370;color:#A5C1D6">STOCK</td>
		<td colspan=2 align=center style="background-color:#275370;color:#A5C1D6">%</td>
		</tr>
		<tr class=rowcontent style="background-color:#275370;color:#A5C1D6">
		<td align=center style="background-color:#275370;color:#A5C1D6">AFS</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">RFS</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">M &</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">I</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">AFS</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">RFS</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">M &</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">I</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">AFS</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">RFS</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">M &</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">I</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">AFS</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">RFS</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">M &</td>
		<td align=center style="background-color:#275370;color:#A5C1D6">I</td>
		</tr>';

		foreach ($tangkikernel as $kerneltangki) {
				$tab.='<tr class=rowcontent>
				<td>'.$kerneltangki.' - '.$nmtangkikernel[$kerneltangki].'</td>
				';

				foreach ($millcodes as $millcode) {
					$tab.='
					<td align=right>'.$sold[$kerneltangki][$millcode].'</td>
					<td align=right>'.$rfs[$kerneltangki][$millcode].'</td>
					<td align=right>'.$moisttank[$kerneltangki][$millcode].'</td>
					<td align=right></td>
					';
				}

				$tab.='
					<td align=right>'.$soldkspagro[$kerneltangki].'</td>
					<td align=right>'.$rfskspagro[$kerneltangki].'</td>
					<td align=right></td>
					<td align=right></td>
					';

				$tab.='</tr>';
		}

		##contract
		$tab.='<tr class=rowcontent>
		<td><b>CONTRACT</b></td>';

		foreach ($millcodes as $millcode) {
			if ($baris[$millcode]['40000002'] > 0) {
				$tab.='<td colspan=4>'.$keterangan[$millcode]['40000002'].'</br> Total kirim : '.($jumlahkirim[$millcode]['40000002']/1000).' Ton</td>';
			}else{
				$tab.='<td colspan=4></td>';
			}
		}
		
		$tab.='<td colspan=4></td>';
		$tab.='</tr>';

		$tab.='
		</tbody>
		</table>';

		if($tipe!='excel'){
			echo $tab;
		}else{
			$dte=date("YmdHis");
			$nop_="CONSOL_DAILY_PRODUCTION";
			if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')){
					while (false !== ($file = readdir($handle))){
						if ($file != "." && $file != ".."){
							@unlink('tempExcel/'.$file);
						}
					}
					closedir($handle);
				}
				
				$handle=fopen("tempExcel/".$nop_.".xls",'w');
				if(!fwrite($handle,$tab)){
					echo "<script language=javascript1.2>
						parent.window.alert('Can't convert to excel format');
					</script>";
					exit;
				}else{
					echo "<script language=javascript1.2>
						window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
				fclose($handle);
			}
		}

		
		break;
}

?>