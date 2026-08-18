<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

if(isset($_GET['mode'])) {
    $mode = $_GET['mode'];
} else {
    $mode = 'preview';
}
if($mode=='pdf') {
    $param = $_GET;
    unset($param['mode']);
    unset($param['level']);
} else {
    $param = $_POST;
}

// Parameter
$strPeriod = str_replace('-','',$param['periode']);
$tmpPeriod = explode('-',$param['periode']);
$year = $tmpPeriod[0];
$month = $tmpPeriod[1];

// Get Range Tanggal Periode
$qPeriod = selectQuery($dbname,'setup_periodeakuntansi','*',
	"kodeorg='".$param['kebun']."' and periode='".$param['periode']."'",
	"periode desc",1,1);
$resPeriod = fetchData($qPeriod);
$tgl1 = $resPeriod[0]['tanggalmulai'];
$tgl2 = $resPeriod[0]['tanggalsampai'];

/*************************************************** 
 ** [START] Get Data *******************************
 ***************************************************/
// 1. Setup Blok
if($param['intiplasma']!=''){
	$addInti=" and intiplasma='".$param['intiplasma']."'";
}
$qBlok = selectQuery($dbname,'setup_blok_tahunan','*',"kodeorg like '".$param['kebun']."%' and tahun='".$strPeriod."' ".$addInti."");
$resBlok = fetchData($qBlok);
$numrows = count($resBlok);
//exit('error : '.$numrows);
if($numrows==0){
	$qBlok = selectQuery($dbname,'setup_blok','*',"kodeorg like '".$param['kebun']."%' ".$addInti."");
	$resBlok = fetchData($qBlok);
}
// Get Upah Harian
$qGP = "SELECT a.* FROM ".$dbname.".sdm_5gajipokok a
	WHERE tahun in ('".$year."','".($year-1)."') and idkomponen = 1";
$resGP = fetchData($qGP);
$optGP = array();
foreach($resGP as $row) {
	$optGP[$row['tahun']][$row['karyawanid']] = round($row['jumlah'] / 25);
}


// 2. Kebun Prestasi
$qPres = selectQuery($dbname,'kebun_prestasi2_vw','*',
	"kodeorg like '".$param['kebun']."%' and nik <> '-' 
	and (notransaksi like '".$year."%' or notransaksi like '".($year-1)."%')");
$resPres = fetchData($qPres);
// echo $qPres;
$optNik = array();
$optPres = array();
$totalHaPanen = array();
foreach($resPres as $row) {
	if(empty($optGP[substr($row['notransaksi'],0,4)][$row['nik']])) {
		$hk = 0;
	} else {
		$hk = round($row['upahkerja'] - $row['upahpenalty']) / $optGP[substr($row['notransaksi'],0,4)][$row['nik']];
	}
    if(!isset($optPres[$row['kodeorg']][substr($row['notransaksi'],0,6)])) {
		$optPres[$row['kodeorg']][substr($row['notransaksi'],0,6)] = array(
			'hasilkerja' => $row['hasilkerja'],
            'hasilkerjakg' => $row['hasilkerjakg'],
            'jumlahhk' => $hk,
            'upahkerja' => $row['upahkerja'],
            'upahpenalty' => $row['upahpenalty'],
            'upahpremi' => $row['upahpremi'],
            'rupiahpenalty' => $row['rupiahpenalty'],
            'luaspanen' => $row['luaspanen'],
			'brondolan' => $row['brondolan'],
		);
	} else {
        $optPres[$row['kodeorg']][substr($row['notransaksi'],0,6)]['hasilkerja'] += $row['hasilkerja'];
        $optPres[$row['kodeorg']][substr($row['notransaksi'],0,6)]['hasilkerjakg'] += $row['hasilkerjakg'];
        $optPres[$row['kodeorg']][substr($row['notransaksi'],0,6)]['jumlahhk'] += $hk;
        $optPres[$row['kodeorg']][substr($row['notransaksi'],0,6)]['upahkerja'] += $row['upahkerja'];
        $optPres[$row['kodeorg']][substr($row['notransaksi'],0,6)]['upahpenalty'] += $row['upahpenalty'];
        $optPres[$row['kodeorg']][substr($row['notransaksi'],0,6)]['upahpremi'] += $row['upahpremi'];
        $optPres[$row['kodeorg']][substr($row['notransaksi'],0,6)]['rupiahpenalty'] += $row['rupiahpenalty'];
        $optPres[$row['kodeorg']][substr($row['notransaksi'],0,6)]['luaspanen'] += $row['luaspanen'];
		$optPres[$row['kodeorg']][substr($row['notransaksi'],0,6)]['brondolan'] += $row['brondolan'];
	}
    
	if(substr($row['notransaksi'],0,6) == $strPeriod) {
        setIt($totalHaPanen[$row['kodeorg']],0);
        $totalHaPanen[$row['kodeorg']] += $row['luaspanen'];
    }
}

foreach($optPres as $blok=>$row) {
    foreach($row as $p=>$row1) {
		if($year==substr($p,0,4) and $p<=$strPeriod) {
            if(!isset($optPres[$blok][$year.'bef'])) {
                $optPres[$blok][$year.'bef'] = $row1;
            } else {
                $optPres[$blok][$year.'bef']['hasilkerja'] += $row1['hasilkerja'];
                $optPres[$blok][$year.'bef']['hasilkerjakg'] += $row1['hasilkerjakg'];
                $optPres[$blok][$year.'bef']['jumlahhk'] += $row1['jumlahhk'];
                $optPres[$blok][$year.'bef']['upahkerja'] += $row1['upahkerja'];
                $optPres[$blok][$year.'bef']['upahpenalty'] += $row1['upahpenalty'];
                $optPres[$blok][$year.'bef']['upahpremi'] += $row1['upahpremi'];
                $optPres[$blok][$year.'bef']['rupiahpenalty'] += $row1['rupiahpenalty'];
                $optPres[$blok][$year.'bef']['luaspanen'] += $row1['luaspanen'];
				$optPres[$blok][$year.'bef']['brondolan'] += $row1['brondolan'];
            }
			if($strPeriod!=$p) unset($optPres[$blok][$p]);
        } elseif(($year-1)==substr($p,0,4) and $month<=substr($strPeriod,4,2)) {
            if(!isset($optPres[$blok][substr($p,0,4).'bef'])) {
                $optPres[$blok][substr($p,0,4).'bef'] = $row1;
            } else {
                $optPres[$blok][substr($p,0,4).'bef']['hasilkerja'] += $row1['hasilkerja'];
                $optPres[$blok][substr($p,0,4).'bef']['hasilkerjakg'] += $row1['hasilkerjakg'];
                $optPres[$blok][substr($p,0,4).'bef']['jumlahhk'] += $row1['jumlahhk'];
                $optPres[$blok][substr($p,0,4).'bef']['upahkerja'] += $row1['upahkerja'];
                $optPres[$blok][substr($p,0,4).'bef']['upahpenalty'] += $row1['upahpenalty'];
                $optPres[$blok][substr($p,0,4).'bef']['upahpremi'] += $row1['upahpremi'];
                $optPres[$blok][substr($p,0,4).'bef']['rupiahpenalty'] += $row1['rupiahpenalty'];
                $optPres[$blok][substr($p,0,4).'bef']['luaspanen'] += $row1['luaspanen'];
				$optPres[$blok][substr($p,0,4).'bef']['brondolan'] += $row1['brondolan'];
            }
            if($month!=substr($strPeriod,4,2))unset($optPres[$blok][$p]);
        }
    }
}

// 3. Blok Budget
$qBlokBgt = selectQuery($dbname,'bgt_blok',"*",
    "tahunbudget='".$year."' and kodeblok like '".$param['kebun']."%' ".$addInti."");
$resBlokBgt = fetchData($qBlokBgt);
$optBlokBgt = array();
foreach($resBlokBgt as $row) {
    $optBlokBgt[$row['kodeblok']] = $row;
}

$akunUpah = '611';
$akunTransport = '61102';

// 4.1 Biaya Upah
$qUpah = selectQuery($dbname,'keu_jurnaldt',"SUM(jumlah) as cost,kodeblok,tanggal",
					"kodeorg like '".$param['kebun']."' and
					tanggal like '".$year."%' and
					noakun like '".$akunUpah."%' and
					noakun not like '".$akunTransport."%'").
					" group by kodeblok,tanggal";
$resUpah = fetchData($qUpah);
$optUpah = array();
foreach($resUpah as $row) {
	if(!isset($optUpah[$row['kodeblok']][substr($row['tanggal'],0,4)."".substr($row['tanggal'],5,2)])) {
		$optUpah[$row['kodeblok']][substr($row['tanggal'],0,4)."".substr($row['tanggal'],5,2)]['cost'] = $row['cost'];
	} else {
        $optUpah[$row['kodeblok']][substr($row['tanggal'],0,4)."".substr($row['tanggal'],5,2)]['cost'] += $row['cost'];
	}
}

foreach($optUpah as $blok=>$row) {
	foreach($row as $p=>$row1) {
		if($year==substr($p,0,4) and $p<=$strPeriod) {
            if(!isset($optUpah[$blok][$year.'bef'])) {
                $optUpah[$blok][$year.'bef']['cost'] = $row1['cost'];
            } else {
                $optUpah[$blok][$year.'bef']['cost'] += $row1['cost'];
            }
			if($strPeriod!=$p) unset($optUpah[$blok][$p]);
        } elseif(($year-1)==substr($p,0,4) and $month<=substr($strPeriod,4,2)) {
            if(!isset($optUpah[$blok][substr($p,0,4).'bef'])) {
                $optUpah[$blok][substr($p,0,4).'bef']['cost'] = $row1['cost'];
            } else {
                $optUpah[$blok][substr($p,0,4).'bef']['cost'] += $row1['cost'];
            }
            if($month!=substr($strPeriod,4,2))unset($optUpah[$blok][$p]);
        }
    }
}

// 4.2 Biaya Transport
$qTrans = selectQuery($dbname,'keu_jurnaldt',"SUM(jumlah) as cost,kodeblok,tanggal",
					"kodeorg like '".$param['kebun']."' and
					tanggal like '".$year."%' and
					noakun like '".$akunTransport."%'").
					" group by kodeblok,tanggal";
$resTrans = fetchData($qTrans);
$optTrans = array();

foreach($resTrans as $row) {
	if(!isset($optTrans[$row['kodeblok']][substr($row['tanggal'],0,4)."".substr($row['tanggal'],5,2)])) {
		$optTrans[$row['kodeblok']][substr($row['tanggal'],0,4)."".substr($row['tanggal'],5,2)]['cost'] = $row['cost'];
	} else {
        $optTrans[$row['kodeblok']][substr($row['tanggal'],0,4)."".substr($row['tanggal'],5,2)]['cost'] += $row['cost'];
	}
}

foreach($optTrans as $blok=>$row) {
	foreach($row as $p=>$row1) {
		if($year==substr($p,0,4) and $p<=$strPeriod) {
            if(!isset($optTrans[$blok][$year.'bef'])) {
                $optTrans[$blok][$year.'bef']['cost'] = $row1['cost'];
            } else {
                $optTrans[$blok][$year.'bef']['cost'] += $row1['cost'];
            }
			if($strPeriod!=$p) unset($optTrans[$blok][$p]);
        } elseif(($year-1)==substr($p,0,4) and $month<=substr($strPeriod,4,2)) {
            if(!isset($optTrans[$blok][substr($p,0,4).'bef'])) {
                $optTrans[$blok][substr($p,0,4).'bef']['cost'] = $row1['cost'];
            } else {
                $optTrans[$blok][substr($p,0,4).'bef']['cost'] += $row1['cost'];
            }
            if($month!=substr($strPeriod,4,2))unset($optTrans[$blok][$p]);
        }
    }
}

// 5. Budget SDM
$qBgtSdm = selectQuery($dbname,'bgt_budget_detail',
					   "SUM(jumlah) as total,
					   SUM(fis01) as hk01,
					   SUM(fis02) as hk02,
					   SUM(fis03) as hk03,
					   SUM(fis04) as hk04,
					   SUM(fis05) as hk05,
					   SUM(fis06) as hk06,
					   SUM(fis07) as hk07,
					   SUM(fis08) as hk08,
					   SUM(fis09) as hk09,
					   SUM(fis10) as hk10,
					   SUM(fis11) as hk11,
					   SUM(fis12) as hk12,
					   kodeorg",
					   "kodebudget in ('SDM-KBL','SDM-KHT','SDM-KNT','SDM-PHL') and kegiatan = '611010101' and
					   kodeorg like '".$param['kebun']."%' and
					   tahunbudget = '".$year."'")." group by kodeorg";
$resBgtSdm = fetchData($qBgtSdm);
$optBgtSdm = array();
foreach($resBgtSdm as $row) {
	$hkYTD = 0;
	for($i=1;$i<13;$i++) {
		if($i<=$month) {
			$hkYTD += $row['hk'.str_pad($i,2,'0',STR_PAD_LEFT)];
		}
	}
	$optBgtSdm[$row['kodeorg']] = array(
		'total' => $row['total'],
		'hk01' => $row['hk01'],
		'hk02' => $row['hk02'],
		'hk03' => $row['hk03'],
		'hk04' => $row['hk04'],
		'hk05' => $row['hk05'],
		'hk06' => $row['hk06'],
		'hk07' => $row['hk07'],
		'hk08' => $row['hk08'],
		'hk09' => $row['hk09'],
		'hk10' => $row['hk10'],
		'hk11' => $row['hk11'],
		'hk12' => $row['hk12'],
		'hkYTD' => $hkYTD
	);
}

// 6. Budget Produksi Kebun
$qBgtProd = selectQuery($dbname,'bgt_produksi_kbn_vw','*,
						((jjgperpkk * hathnini)*1000 / (jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12)) as bjrBgt,
						(jjg01+jjg02+jjg03+jjg04+jjg05+jjg06+jjg07+jjg08+jjg09+jjg10+jjg11+jjg12) as jjgperthn',
						"kodeunit = '".$param['kebun']."' and tahunbudget='".$year."'");
$resBgtProd = fetchData($qBgtProd);
$optBgtProd = array();
foreach($resBgtProd as $row) {
	$jjgYTD = 0;
	for($i=1;$i<13;$i++) {
		if($i<=$month) {
			$jjgYTD += $row['jjg'.str_pad($i,2,'0',STR_PAD_LEFT)];
		}
	}
	$optBgtProd[$row['kodeblok']] = $row;
	$optBgtProd[$row['kodeblok']]['jjgYTD'] = $jjgYTD;
}

// 7. Kebun Spbdt
// $akunTransport = '61102';
$qSpbdt = selectQuery($dbname,'kebun_spbdt',"SUM(totalkg) as totalkg,SUM(brondolan) as brondolan,SUM(jjg) as jjg,blok,nospb",
					"blok like '".$param['kebun']."%' and 
					(nospb like '%".$year."%' or nospb like '%".($year-1)."%')")." group by blok,nospb";
$resSpbdt = fetchData($qSpbdt);
$optSpbdt = array();
foreach($resSpbdt as $row) {
	if(!isset($optSpbdt[$row['blok']][(substr($row['nospb'],-4)."".substr($row['nospb'],-7,2))])) {
		$optSpbdt[$row['blok']][(substr($row['nospb'],-4)."".substr($row['nospb'],-7,2))]['totalkg'] = $row['totalkg'];
		$optSpbdt[$row['blok']][(substr($row['nospb'],-4)."".substr($row['nospb'],-7,2))]['brondolan'] = $row['brondolan'];
		$optSpbdt[$row['blok']][(substr($row['nospb'],-4)."".substr($row['nospb'],-7,2))]['jjg'] = $row['jjg'];
	} else {
        $optSpbdt[$row['blok']][(substr($row['nospb'],-4)."".substr($row['nospb'],-7,2))]['totalkg'] += $row['totalkg'];
        $optSpbdt[$row['blok']][(substr($row['nospb'],-4)."".substr($row['nospb'],-7,2))]['brondolan'] += $row['brondolan'];
        $optSpbdt[$row['blok']][(substr($row['nospb'],-4)."".substr($row['nospb'],-7,2))]['jjg'] += $row['jjg'];
	}
}

foreach($optSpbdt as $blok=>$row) {
	foreach($row as $p=>$row1) {
		if($year==substr($p,0,4) and $p<=$strPeriod) {
            if(!isset($optSpbdt[$blok][$year.'bef'])) {
                $optSpbdt[$blok][$year.'bef']['totalkg'] = $row1['totalkg'];
                $optSpbdt[$blok][$year.'bef']['brondolan'] = $row1['brondolan'];
                $optSpbdt[$blok][$year.'bef']['jjg'] = $row1['jjg'];
            } else {
                $optSpbdt[$blok][$year.'bef']['totalkg'] += $row1['totalkg'];
                $optSpbdt[$blok][$year.'bef']['brondolan'] += $row1['brondolan'];
                $optSpbdt[$blok][$year.'bef']['jjg'] += $row1['jjg'];
            }
			if($strPeriod!=$p) unset($optSpbdt[$blok][$p]);
        } elseif(($year-1)==substr($p,0,4) and $month<=substr($strPeriod,4,2)) {
            if(!isset($optSpbdt[$blok][substr($p,0,4).'bef'])) {
                $optSpbdt[$blok][substr($p,0,4).'bef']['totalkg'] = $row1['totalkg'];
                $optSpbdt[$blok][substr($p,0,4).'bef']['brondolan'] = $row1['brondolan'];
                $optSpbdt[$blok][substr($p,0,4).'bef']['jjg'] = $row1['jjg'];
            } else {
                $optSpbdt[$blok][substr($p,0,4).'bef']['totalkg'] += $row1['totalkg'];
                $optSpbdt[$blok][substr($p,0,4).'bef']['brondolan'] += $row1['brondolan'];
                $optSpbdt[$blok][substr($p,0,4).'bef']['jjg'] += $row1['jjg'];
            }
            if($month!=substr($strPeriod,4,2))unset($optSpbdt[$blok][$p]);
        }
    }
}

/*************************************************** 
 ** [END] Get Data *********************************
 ***************************************************/

// Masking
$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,kodeorganisasi',
	"kodeorganisasi like '".$param['kebun']."%'");

// Rearrange Data
$data = array();
foreach($resBlok as $row) {
    $haMature = $row['statusblok']=='TM' ? $row['luasareaproduktif'] : 0; //== 7
    $haHarvest = $row['statusblok']=='TM' ? $row['luasareaproduktif'] : 0; //== 8
    // $haHarvest = @(($sisaLuas>0)? $sisaLuas: $row['luasareaproduktif']);
    // $sisaLuas = @(($row['luasareaproduktif']>0)? $totalHaPanen[$row['kodeorg']] % $row['luasareaproduktif']: 0);
    if(!empty($totalHaPanen[$row['kodeorg']]))
	{
		$sisaLuas = @($totalHaPanen[$row['kodeorg']] % $row['luasareaproduktif']);
	}
	else
	{
		if(!empty($row['luasareaproduktif']))
		{
			$sisaLuas = @(0 % $row['luasareaproduktif']);
		}
		else
		{
			$sisaLuas = 0;
		}
	}
    
    setIt($optBlokBgt[$row['kodeorg']]['hathnini'],0); //== 9
    
    setIt($optPres[$row['kodeorg']][$strPeriod]['jumlahhk'],0);
    setIt($optPres[$row['kodeorg']][$year.'bef']['jumlahhk'],0);
    setIt($optPres[$row['kodeorg']][($year-1).$month]['jumlahhk'],0);
    setIt($optPres[$row['kodeorg']][($year-1).'bef']['jumlahhk'],0);
    
    setIt($optPres[$row['kodeorg']][$strPeriod]['hasilkerjakg'],0);
    setIt($optPres[$row['kodeorg']][$year.'bef']['hasilkerjakg'],0);
    setIt($optPres[$row['kodeorg']][($year-1).$month]['hasilkerjakg'],0);
    setIt($optPres[$row['kodeorg']][($year-1).'bef']['hasilkerjakg'],0);
	
	setIt($optPres[$row['kodeorg']][$strPeriod]['brondolan'],0);
	setIt($optPres[$row['kodeorg']][$year.'bef']['brondolan'],0);
	
	setIt($optPres[$row['kodeorg']][$strPeriod]['hasilkerja'],0);
    setIt($optPres[$row['kodeorg']][$year.'bef']['hasilkerja'],0);
    setIt($optPres[$row['kodeorg']][($year-1).$month]['hasilkerja'],0);
    setIt($optPres[$row['kodeorg']][($year-1).'bef']['hasilkerja'],0);
	
	setIt($optBgtSdm[$row['kodeorg']]['hk'.$month],0);
	setIt($optBgtSdm[$row['kodeorg']]['hkYTD'],0);
	setIt($optBgtSdm[$row['kodeorg']]['total'],0);
	
	setIt($optUpah[$row['kodeorg']][$strPeriod]['cost'],0);
	setIt($optUpah[$row['kodeorg']][$year.'bef']['cost'],0);
	
	setIt($optPres[$row['kodeorg']][$strPeriod]['luaspanen'],0);
	setIt($optPres[$row['kodeorg']][$strPeriod]['jumlahhk'],0);
	setIt($optPres[$row['kodeorg']][$year.'bef']['jumlahhk'],0);
	setIt($optPres[$row['kodeorg']][($year-1).$month]['jumlahhk'],0);
	setIt($optPres[$row['kodeorg']][($year-1).'bef']['jumlahhk'],0);
	
	setIt($optSpbdt[$row['kodeorg']][$strPeriod]['totalkg'],0);
	setIt($optSpbdt[$row['kodeorg']][$year.'bef']['totalkg'],0);
	setIt($optSpbdt[$row['kodeorg']][($year-1).$month]['totalkg'],0);
	setIt($optSpbdt[$row['kodeorg']][($year-1).'bef']['totalkg'],0);
	setIt($optBgtProd[$row['kodeorg']]['jjg'.$month],0);
	setIt($optBgtProd[$row['kodeorg']]['jjgYTD'],0);
	setIt($optBgtProd[$row['kodeorg']]['jjgperthn'],0);
	setIt($optBgtProd[$row['kodeorg']]['bjrBgt'],0);
	
	setIt($optTrans[$row['kodeorg']][$strPeriod]['cost'],0);
	setIt($optTrans[$row['kodeorg']][$year.'bef']['cost'],0);
	setIt($optSpbdt[$row['kodeorg']][$strPeriod]['brondolan'],0);
	setIt($optSpbdt[$row['kodeorg']][$year.'bef']['brondolan'],0);
	setIt($optSpbdt[$row['kodeorg']][$strPeriod]['jjg'],0);
	setIt($optSpbdt[$row['kodeorg']][$year.'bef']['jjg'],0);
	setIt($optSpbdt[$row['kodeorg']][($year-1).$month]['jjg'],0);
	setIt($optSpbdt[$row['kodeorg']][($year-1).'bef']['jjg'],0);
    
	// Cost
	$costLabour1 = $optUpah[$row['kodeorg']][$strPeriod]['cost'];
	$costLabour2 = $optUpah[$row['kodeorg']][$year.'bef']['cost'];
	
	$costHarvest1 = 0;
	if($optPres[$row['kodeorg']][$strPeriod]['hasilkerjakg'] > 0) {
		$costHarvest1 = $costLabour1 + $optTrans[$row['kodeorg']][$strPeriod]['cost'] / $optPres[$row['kodeorg']][$strPeriod]['hasilkerjakg'];
	}
	$costHarvest2 = 0;
	if($optPres[$row['kodeorg']][$year.'bef']['hasilkerjakg'] > 0) {
		$costHarvest2 = $costLabour1 + $optTrans[$row['kodeorg']][$year.'bef']['cost'] / $optPres[$row['kodeorg']][$year.'bef']['hasilkerjakg'];
	}
	
	// Persen Brondolan
	$brondolanPercent1 = 0;
	if($optPres[$row['kodeorg']][$strPeriod]['hasilkerjakg'] > 0) {
		$brondolanPercent1 = $optPres[$row['kodeorg']][$strPeriod]['brondolan'] * 100 /
			$optPres[$row['kodeorg']][$strPeriod]['hasilkerjakg'];
	}
	$brondolanPercent2 = 0;
	if($optPres[$row['kodeorg']][$year.'bef']['hasilkerjakg'] > 0) {
		$brondolanPercent2 = $optPres[$row['kodeorg']][$year.'bef']['brondolan'] * 100 /
			$optPres[$row['kodeorg']][$year.'bef']['hasilkerjakg'];
	}
	
	// BJR
	$bjr1 = 0;
	if($optPres[$row['kodeorg']][$strPeriod]['hasilkerja'] > 0) {
		$bjr1 = $optPres[$row['kodeorg']][$strPeriod]['hasilkerjakg'] * 100 /
			$optPres[$row['kodeorg']][$strPeriod]['hasilkerja'];
	}
	$bjr2 = 0;
	if($optPres[$row['kodeorg']][$year.'bef']['hasilkerja'] > 0) {
		$bjr2 = $optPres[$row['kodeorg']][$year.'bef']['hasilkerjakg'] * 100 /
			$optPres[$row['kodeorg']][$year.'bef']['hasilkerja'];
	}
	
	$data[substr($row['kodeorg'],0,6)][$row['kodeorg']] = array(
		'kodeblok'		=> $row['kodeorg'],
		'tahuntanam'	=> $row['tahuntanam'],
		'statusblok'	=> $row['statusblok'],
		'budgetreplant'	=> $row['tahuntanam']+25,
		'maturitydate'	=> $row['tahunmulaipanen'].'-'.str_pad($row['bulanmulaipanen'],2,'0',STR_PAD_LEFT),
		'jenisbibit'	=> $row['jenisbibit'],
		'hamature'		=> $haMature, 																//== 7
		'haharvest'		=> $haHarvest, 																//== 8
		'habudget'		=> $optBlokBgt[$row['kodeorg']]['hathnini'], 								//== 9
		'haavg'			=> ($haMature + $haHarvest + $optBlokBgt[$row['kodeorg']]['hathnini']) / 3, //== 10
		'jumlahpokok'	=> $row['jumlahpokok'], 													//== 11
		'pokokperha'	=> @($row['jumlahpokok'] / $row['luasareaproduktif']), 						//== 12
		'luaspanen'		=> $optPres[$row['kodeorg']][$strPeriod]['luaspanen'], 						//== 12
		'harvestround'	=> @(($row['luasareaproduktif']>0) ? number_format($totalHaPanen[$row['kodeorg']] / $row['luasareaproduktif'],2) : 0),
        
        // Mandays
        'mandays11'     => $optPres[$row['kodeorg']][$strPeriod]['jumlahhk'],					//== 14	
        'mandays12'     => $optPres[$row['kodeorg']][$year.'bef']['jumlahhk'],					//== 15
        'mandays21'     => $optPres[$row['kodeorg']][($year-1).$month]['jumlahhk'],				//== 16
        'mandays22'     => $optPres[$row['kodeorg']][($year-1).'bef']['jumlahhk'],				//== 17
        'mandays31'     => $optBgtSdm[$row['kodeorg']]['hk'.$month],								//== 18
        'mandays32'     => $optBgtSdm[$row['kodeorg']]['hkYTD'],									//== 19
        'mandays33'     => $optBgtSdm[$row['kodeorg']]['total'],									//== 20
        
        // Yield Ton
        'kg11'     => $optSpbdt[$row['kodeorg']][$strPeriod]['totalkg'] / 1000,					//== 21
        'kg12'     => $optSpbdt[$row['kodeorg']][$year.'bef']['totalkg'] / 1000,					//== 22
        'kg21'     => $optSpbdt[$row['kodeorg']][($year-1).$month]['totalkg'] / 1000,			//== 23
        'kg22'     => $optSpbdt[$row['kodeorg']][($year-1).'bef']['totalkg'] / 1000,				//== 24
        'kg31'     => ($optBgtProd[$row['kodeorg']]['jjg'.$month] * $optBgtProd[$row['kodeorg']]['bjrBgt']) / 1000,//== 25
        'kg32'     => ($optBgtProd[$row['kodeorg']]['jjgYTD'] * $optBgtProd[$row['kodeorg']]['bjrBgt']) / 1000,	//== 26
        'kg33'     => ($optBgtProd[$row['kodeorg']]['jjgperthn'] * $optBgtProd[$row['kodeorg']]['bjrBgt']) / 1000,	//== 27
        
        // Yield Ton / Ha
        'yield11'     => $haMature > 0 ? ($optSpbdt[$row['kodeorg']][$strPeriod]['totalkg'] / 1000) / $haMature : 0,//== 28
        'yield12'     => $haMature > 0 ? ($optSpbdt[$row['kodeorg']][$year.'bef']['totalkg'] / 1000) / $haMature : 0,//== 29
        'yield21'     => $haMature > 0 ? ($optSpbdt[$row['kodeorg']][($year-1).$month]['totalkg'] / 1000) / $haMature : 0,//== 30
        'yield22'     => $haMature > 0 ? ($optSpbdt[$row['kodeorg']][($year-1).'bef']['totalkg'] / 1000) / $haMature : 0,//== 31
        'yield31'     => $optBlokBgt[$row['kodeorg']]['hathnini'] > 0 ? (($optBgtProd[$row['kodeorg']]['jjg'.$month] * $optBgtProd[$row['kodeorg']]['bjrBgt']) / 1000) / $optBlokBgt[$row['kodeorg']]['hathnini'] : 0, //== 32
        'yield32'     => $optBlokBgt[$row['kodeorg']]['hathnini'] > 0 ? (($optBgtProd[$row['kodeorg']]['jjgYTD'] * $optBgtProd[$row['kodeorg']]['bjrBgt']) / 1000) / $optBlokBgt[$row['kodeorg']]['hathnini'] : 0, //== 33
        'yield33'     => $optBlokBgt[$row['kodeorg']]['hathnini'] > 0 ? (($optBgtProd[$row['kodeorg']]['jjgperthn'] * $optBgtProd[$row['kodeorg']]['bjrBgt']) / 1000) / $optBlokBgt[$row['kodeorg']]['hathnini'] : 0, //== 34
        
        // Yield Ton / Pemanen
        'perorang11' 	=> $optPres[$row['kodeorg']][$strPeriod]['jumlahhk'] > 0 ? ($optSpbdt[$row['kodeorg']][$strPeriod]['totalkg'] / 1000) / $optPres[$row['kodeorg']][$strPeriod]['jumlahhk'] : 0, //== 35
        'perorang12'    => $optPres[$row['kodeorg']][$year.'bef']['jumlahhk'] > 0 ? ($optSpbdt[$row['kodeorg']][$year.'bef']['totalkg'] / 1000) / $optPres[$row['kodeorg']][$year.'bef']['jumlahhk'] : 0, //== 36
        'perorang21'    => $optPres[$row['kodeorg']][($year-1).$month]['jumlahhk'] > 0 ? ($optSpbdt[$row['kodeorg']][($year-1).$month]['totalkg'] / 1000) / $optPres[$row['kodeorg']][($year-1).$month]['jumlahhk'] : 0, //== 37
        'perorang22'    => $optPres[$row['kodeorg']][($year-1).'bef']['jumlahhk'] > 0 ? ($optSpbdt[$row['kodeorg']][($year-1).'bef']['totalkg'] / 1000) / $optPres[$row['kodeorg']][($year-1).'bef']['jumlahhk'] : 0,//== 38
        'perorang31'    => $optBgtSdm[$row['kodeorg']]['hk'.$month] > 0 ? (($optBgtProd[$row['kodeorg']]['jjg'.$month] * $optBgtProd[$row['kodeorg']]['bjrBgt']) / 1000) / $optBgtSdm[$row['kodeorg']]['hk'.$month] : 0,//== 39
        'perorang32'    => $optBgtSdm[$row['kodeorg']]['hkYTD'] > 0 ? (($optBgtProd[$row['kodeorg']]['jjgYTD'] * $optBgtProd[$row['kodeorg']]['bjrBgt']) / 1000) / $optBgtSdm[$row['kodeorg']]['hkYTD'] : 0,//== 40
        'perorang33'    => $optBgtSdm[$row['kodeorg']]['total'] > 0 ? (($optBgtProd[$row['kodeorg']]['jjgperthn'] * $optBgtProd[$row['kodeorg']]['bjrBgt']) / 1000) / $optBgtSdm[$row['kodeorg']]['total'] : 0,//== 41
		
		// Cost
		'costlabour1'	=> $costLabour1,											//== 42
		'costlabour2'	=> $costLabour2,											//== 43
		'costvhc1'		=> $optTrans[$row['kodeorg']][$strPeriod]['cost'],					//== 44
		'costvhc2'		=> $optTrans[$row['kodeorg']][$year.'bef']['cost'],					//== 45
		'costtotal1'	=> $costLabour1 + $optTrans[$row['kodeorg']][$strPeriod]['cost'],	//== 46
		'costtotal2'	=> $costLabour2 + $optTrans[$row['kodeorg']][$year.'bef']['cost'],	//== 47
		
		'costharv1'		=> $optSpbdt[$row['kodeorg']][$strPeriod]['totalkg'] > 0 ? ($costLabour1 + $optTrans[$row['kodeorg']][$strPeriod]['cost']) / $optSpbdt[$row['kodeorg']][$strPeriod]['totalkg'] : 0,//== 48
		'costharv2'		=> $optSpbdt[$row['kodeorg']][$year.'bef']['totalkg'] > 0 ? ($costLabour2 + $optTrans[$row['kodeorg']][$year.'bef']['cost']) / $optSpbdt[$row['kodeorg']][$year.'bef']['totalkg'] : 0,//== 49
		
		// Total Brondolan
		'brondolan1'		=> $optSpbdt[$row['kodeorg']][$strPeriod]['brondolan'] / 1000,		//== 50
		'brondolan2'		=> $optSpbdt[$row['kodeorg']][$year.'bef']['brondolan'] / 1000,		//== 51
		
		'brondolanpercent1'	=> ($optSpbdt[$row['kodeorg']][$strPeriod]['totalkg'] / 1000) > 0 ? ($optSpbdt[$row['kodeorg']][$strPeriod]['brondolan'] / 1000) / ($optSpbdt[$row['kodeorg']][$strPeriod]['totalkg'] / 1000) * 100 : 0,//== 52
		'brondolanpercent2'	=> ($optSpbdt[$row['kodeorg']][$year.'bef']['totalkg'] / 1000) > 0 ? ($optSpbdt[$row['kodeorg']][$year.'bef']['brondolan'] / 1000) / ($optSpbdt[$row['kodeorg']][$year.'bef']['totalkg'] / 1000) * 100 : 0,//== 53
		
		// Total TBS
		'tbs11'     => $optSpbdt[$row['kodeorg']][$strPeriod]['jjg'],//== 54
        'tbs12'     => $optSpbdt[$row['kodeorg']][$year.'bef']['jjg'],//== 55
        'tbs21'     => $optSpbdt[$row['kodeorg']][($year-1).$month]['jjg'],//== 56
        'tbs22'     => $optSpbdt[$row['kodeorg']][($year-1).'bef']['jjg'],//== 57
		
		// BJR
		'bjr1'	=> $optSpbdt[$row['kodeorg']][$strPeriod]['jjg'] > 0 ? ($optSpbdt[$row['kodeorg']][$strPeriod]['totalkg']) / ($optSpbdt[$row['kodeorg']][$strPeriod]['jjg']) : 0,//== 58
		'bjr2'	=> $optSpbdt[$row['kodeorg']][$year.'bef']['jjg'] > 0 ? ($optSpbdt[$row['kodeorg']][$year.'bef']['totalkg']) / ($optSpbdt[$row['kodeorg']][$year.'bef']['jjg']) : 0,//== 59
	);
}

$dataShow = $data;
$dataExcel = $data;

foreach($data as $afd=>$row) {
	foreach($row as $key=>$row1) {
		$dataShow[$afd][$key]['kodeblok'] = $optOrg[$row1['kodeblok']];
		$dataShow[$afd][$key]['pokokperha'] = number_format($row1['pokokperha'],2);
	}
}

/** Mode Header **/
if($mode=='excel') {
	$tab = strtoupper($_SESSION['lang']['cropstatistic'])."<br>".
		strtoupper($_SESSION['lang']['kebun'])." : ".$param['kebun']."<br>".
		strtoupper($_SESSION['lang']['periode'])." : ".$param['periode'].
	"<table border='1'>";
	$tab .= "<thead style=\"background-color:#222222\">";
} else {
	/*$tab = "<div style='overflow:auto; max-width:1235px;'>";*/
	$tab .= "<table id='periksabuah' class='sortable' cellpadding=3 cellspacing=1 border=0>";
	$tab .= "<thead style='text-align:center'>";
}

/** Generate Table **/
// Header
$tab .= "<tr class=rowheader>";
$tab .= "<th rowspan=4>".$_SESSION['lang']['kodeblok']."</th>";
$tab .= "<th rowspan=4>".$_SESSION['lang']['tahuntanam']."</th>";
$tab .= "<th rowspan=4>".$_SESSION['lang']['statusblok']."</th>";
$tab .= "<th rowspan=4>Budget Replanting Year</th>";
$tab .= "<th rowspan=4>Date of Maturity</th>";
$tab .= "<th rowspan=4>".$_SESSION['lang']['jenisbibit']."</th>";
$tab .= "<th colspan=4>Hectare</th>";
$tab .= "<th rowspan=4>".$_SESSION['lang']['jumlahpokok']."</th>";
$tab .= "<th rowspan=4>Stand / Ha</th>";
$tab .= "<th rowspan=4>Luas di Panen</th>";
$tab .= "<th rowspan=4>Harvesting Rounds</th>";
$tab .= "<th colspan=7>No of Mandays</th>";
$tab .= "<th colspan=7>Yield in Ton of FFB Total</th>";
$tab .= "<th colspan=7>Yield in Ton of FFB Per Ha</th>";
$tab .= "<th colspan=7>Yield in Ton of FFB per Harvester</th>";
$tab .= "<th colspan=6 rowspan=2>Total Cost Collection</th>";
$tab .= "<th colspan=2 rowspan=2>Harvesting Cost (Rp/Kg)</th>";
$tab .= "<th colspan=4>Total Loose Fruit</th>";
$tab .= "<th colspan=4>Total Bunches (in pcs)</th>";
$tab .= "<th colspan=2>Avg Kg / Bunch</th></tr>";

$tab .= "<tr class=rowheader>";
$tab .= "<th rowspan=3>Mature</th>";
$tab .= "<th rowspan=3>Harvest</th>";
$tab .= "<th rowspan=3>Budget</th>";
$tab .= "<th rowspan=3>Average</th>";
$tab .= "<th colspan=2>Actual</th>";
$tab .= "<th colspan=2>SMLY</th>";
$tab .= "<th colspan=3>Budget</th>";
$tab .= "<th colspan=2>Actual</th>";
$tab .= "<th colspan=2>SMLY</th>";
$tab .= "<th colspan=3>Budget</th>";
$tab .= "<th colspan=2>Actual</th>";
$tab .= "<th colspan=2>SMLY</th>";
$tab .= "<th colspan=3>Budget</th>";
$tab .= "<th colspan=2>Actual</th>";
$tab .= "<th colspan=2>SMLY</th>";
$tab .= "<th colspan=3>Budget</th>";
$tab .= "<th colspan=2>In Tonnage</th>";
$tab .= "<th colspan=2>In Percent</th>";
$tab .= "<th colspan=2>Actual</th>";
$tab .= "<th colspan=2>SMLY</th>";
$tab .= "<th rowspan=3>This Month</th>";
$tab .= "<th rowspan=3>YTD</th></tr>";

$tab .= "<tr class=rowheader>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>1 Year</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>1 Year</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>1 Year</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>1 Year</th>";
$tab .= "<th colspan=2>Labour</th>";
$tab .= "<th colspan=2>Transport</th>";
$tab .= "<th colspan=2>Total</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th>";
$tab .= "<th rowspan=2>This Month</th>";
$tab .= "<th rowspan=2>YTD</th></tr>";

$tab .= "<tr class=rowheader>";
$tab .= "<th>This Month</th>";
$tab .= "<th>YTD</th>";
$tab .= "<th>This Month</th>";
$tab .= "<th>YTD</th>";
$tab .= "<th>This Month</th>";
$tab .= "<th>YTD</th></tr>";

// Init Grand Total
$grandtotal = array();
$grandtotal['hamature']=0;
$grandtotal['haharvest']=0;
$grandtotal['habudget']=0;
$grandtotal['haavg']=0;
$grandtotal['jumlahpokok']=0;
$grandtotal['pokokperha']=0;
$grandtotal['luaspanen']=0;
$grandtotal['harvestround']=0;
$grandtotal['mandays11']=0;
$grandtotal['mandays12']=0;
$grandtotal['mandays21']=0;
$grandtotal['mandays22']=0;
$grandtotal['mandays31']=0;
$grandtotal['mandays32']=0;
$grandtotal['mandays33']=0;
$grandtotal['kg11']=0;
$grandtotal['kg12']=0;
$grandtotal['kg21']=0;
$grandtotal['kg22']=0;
$grandtotal['kg31']=0;
$grandtotal['kg32']=0;
$grandtotal['kg33']=0;
$grandtotal['yield11']=0;
$grandtotal['yield12']=0;
$grandtotal['yield21']=0;
$grandtotal['yield22']=0;
$grandtotal['yield31']=0;
$grandtotal['yield32']=0;
$grandtotal['yield33']=0;
$grandtotal['perorang11']=0;
$grandtotal['perorang12']=0;
$grandtotal['perorang21']=0;
$grandtotal['perorang22']=0;
$grandtotal['perorang31']=0;
$grandtotal['perorang32']=0;
$grandtotal['perorang33']=0;
$grandtotal['costlabour1']=0;
$grandtotal['costlabour2']=0;
$grandtotal['costvhc1']=0;
$grandtotal['costvhc2']=0;
$grandtotal['costtotal1']=0;
$grandtotal['costtotal2']=0;
$grandtotal['costharv1']=0;
$grandtotal['costharv2']=0;
$grandtotal['brondolan1']=0;
$grandtotal['brondolan2']=0;
$grandtotal['brondolanpercent1']=0;
$grandtotal['brondolanpercent2']=0;
$grandtotal['tbs11']=0;
$grandtotal['tbs12']=0;
$grandtotal['tbs21']=0;
$grandtotal['tbs22']=0;
$grandtotal['bjr1']=0;
$grandtotal['bjr2']=0;

// Content
$countAfd = 0;
$tab .= "</thead><tbody>";
foreach($dataShow as $afd=>$row) {
	$tab .= "<tr class=rowcontent><td colspan=60>".$optOrg[$afd]."</td></tr>";
	foreach($row as $row1) {
		$tab .= "<tr class=rowcontent>";
        $col = 0;
		foreach($row1 as $val) {
			if(strstr($val,'!')) {
				$tab .= "<td style='color:red'>".$val."</td>";
			} else 
			{
				if($col>5)
				{
					
					
					if($val==floatval($val))
					{
						
						$vals = number_format(floatval($val),2);
					}
				}
                $tab .= ($col>5)? "<td style='text-align:right'>": "<td>"; 
				$tab .= $vals."</td>";
			}
            $col++;
		}
		
		setIt($subTotal,array());
		setIt($subTotal[$afd]['hamature'],0);
		setIt($subTotal[$afd]['haharvest'],0);
		setIt($subTotal[$afd]['habudget'],0);
		setIt($subTotal[$afd]['haavg'],0);
		setIt($subTotal[$afd]['jumlahpokok'],0);
		setIt($subTotal[$afd]['pokokperha'],0);
		setIt($subTotal[$afd]['luaspanen'],0);
		setIt($subTotal[$afd]['harvestround'],0);
		$subTotal[$afd]['hamature']+=$row1['hamature'];
		$subTotal[$afd]['haharvest']+=$row1['haharvest'];
		$subTotal[$afd]['habudget']+=$row1['habudget'];
		$subTotal[$afd]['haavg']+=$row1['haavg'];
		$subTotal[$afd]['jumlahpokok']+=$row1['jumlahpokok'];
		$subTotal[$afd]['pokokperha']+=$row1['pokokperha'];
		$subTotal[$afd]['luaspanen']+=$row1['luaspanen'];
		$subTotal[$afd]['harvestround']+=$row1['harvestround'];
		setIt($subTotal[$afd]['mandays11'],0);
		setIt($subTotal[$afd]['mandays12'],0);
		setIt($subTotal[$afd]['mandays21'],0);
		setIt($subTotal[$afd]['mandays22'],0);
		setIt($subTotal[$afd]['mandays31'],0);
		setIt($subTotal[$afd]['mandays32'],0);
		setIt($subTotal[$afd]['mandays33'],0);
		$subTotal[$afd]['mandays11']+=$row1['mandays11'];
		$subTotal[$afd]['mandays12']+=$row1['mandays12'];
		$subTotal[$afd]['mandays21']+=$row1['mandays21'];
		$subTotal[$afd]['mandays22']+=$row1['mandays22'];
		$subTotal[$afd]['mandays31']+=$row1['mandays31'];
		$subTotal[$afd]['mandays32']+=$row1['mandays32'];
		$subTotal[$afd]['mandays33']+=$row1['mandays33'];
		setIt($subTotal[$afd]['kg11'],0);
		setIt($subTotal[$afd]['kg12'],0);
		setIt($subTotal[$afd]['kg21'],0);
		setIt($subTotal[$afd]['kg22'],0);
		setIt($subTotal[$afd]['kg31'],0);
		setIt($subTotal[$afd]['kg32'],0);
		setIt($subTotal[$afd]['kg33'],0);
		$subTotal[$afd]['kg11']+=$row1['kg11'];
		$subTotal[$afd]['kg12']+=$row1['kg12'];
		$subTotal[$afd]['kg21']+=$row1['kg21'];
		$subTotal[$afd]['kg22']+=$row1['kg22'];
		$subTotal[$afd]['kg31']+=$row1['kg31'];
		$subTotal[$afd]['kg32']+=$row1['kg32'];
		$subTotal[$afd]['kg33']+=$row1['kg33'];
		setIt($subTotal[$afd]['yield11'],0);
		setIt($subTotal[$afd]['yield12'],0);
		setIt($subTotal[$afd]['yield21'],0);
		setIt($subTotal[$afd]['yield22'],0);
		setIt($subTotal[$afd]['yield31'],0);
		setIt($subTotal[$afd]['yield32'],0);
		setIt($subTotal[$afd]['yield33'],0);
		$subTotal[$afd]['yield11']+=$row1['yield11'];
		$subTotal[$afd]['yield12']+=$row1['yield12'];
		$subTotal[$afd]['yield21']+=$row1['yield21'];
		$subTotal[$afd]['yield22']+=$row1['yield22'];
		$subTotal[$afd]['yield31']+=$row1['yield31'];
		$subTotal[$afd]['yield32']+=$row1['yield32'];
		$subTotal[$afd]['yield33']+=$row1['yield33'];
		setIt($subTotal[$afd]['perorang11'],0);
		setIt($subTotal[$afd]['perorang12'],0);
		setIt($subTotal[$afd]['perorang21'],0);
		setIt($subTotal[$afd]['perorang22'],0);
		setIt($subTotal[$afd]['perorang31'],0);
		setIt($subTotal[$afd]['perorang32'],0);
		setIt($subTotal[$afd]['perorang33'],0);
		$subTotal[$afd]['perorang11']+=$row1['perorang11'];
		$subTotal[$afd]['perorang12']+=$row1['perorang12'];
		$subTotal[$afd]['perorang21']+=$row1['perorang21'];
		$subTotal[$afd]['perorang22']+=$row1['perorang22'];
		$subTotal[$afd]['perorang31']+=$row1['perorang31'];
		$subTotal[$afd]['perorang32']+=$row1['perorang32'];
		$subTotal[$afd]['perorang33']+=$row1['perorang33'];
		setIt($subTotal[$afd]['costlabour1'],0);
		setIt($subTotal[$afd]['costlabour2'],0);
		setIt($subTotal[$afd]['costvhc1'],0);
		setIt($subTotal[$afd]['costvhc2'],0);
		setIt($subTotal[$afd]['costtotal1'],0);
		setIt($subTotal[$afd]['costtotal2'],0);
		setIt($subTotal[$afd]['costharv1'],0);
		setIt($subTotal[$afd]['costharv2'],0);
		setIt($subTotal[$afd]['brondolan1'],0);
		setIt($subTotal[$afd]['brondolan2'],0);
		setIt($subTotal[$afd]['brondolanpercent1'],0);
		setIt($subTotal[$afd]['brondolanpercent2'],0);
		$subTotal[$afd]['costlabour1']+=$row1['costlabour1'];
		$subTotal[$afd]['costlabour2']+=$row1['costlabour2'];
		$subTotal[$afd]['costvhc1']+=$row1['costvhc1'];
		$subTotal[$afd]['costvhc2']+=$row1['costvhc2'];
		$subTotal[$afd]['costtotal1']+=$row1['costtotal1'];
		$subTotal[$afd]['costtotal2']+=$row1['costtotal2'];
		$subTotal[$afd]['costharv1']+=$row1['costharv1'];
		$subTotal[$afd]['costharv2']+=$row1['costharv2'];
		$subTotal[$afd]['brondolan1']+=$row1['brondolan1'];
		$subTotal[$afd]['brondolan2']+=$row1['brondolan2'];
		$subTotal[$afd]['brondolanpercent1']+=$row1['brondolanpercent1'];
		$subTotal[$afd]['brondolanpercent2']+=$row1['brondolanpercent2'];
		setIt($subTotal[$afd]['tbs11'],0);
		setIt($subTotal[$afd]['tbs12'],0);
		setIt($subTotal[$afd]['tbs21'],0);
		setIt($subTotal[$afd]['tbs22'],0);
		setIt($subTotal[$afd]['bjr1'],0);
		setIt($subTotal[$afd]['bjr2'],0);
		$subTotal[$afd]['tbs11']+=$row1['tbs11'];
		$subTotal[$afd]['tbs12']+=$row1['tbs12'];
		$subTotal[$afd]['tbs21']+=$row1['tbs21'];
		$subTotal[$afd]['tbs22']+=$row1['tbs22'];
		//$subTotal[$afd]['bjr1']+=$row1['bjr1'];
		//$subTotal[$afd]['bjr2']+=$row1['bjr2'];
		
		$grandtotal['hamature']+=$row1['hamature'];
		$grandtotal['haharvest']+=$row1['haharvest'];
		$grandtotal['habudget']+=$row1['habudget'];
		$grandtotal['haavg']+=$row1['haavg'];
		$grandtotal['jumlahpokok']+=$row1['jumlahpokok'];
		$grandtotal['pokokperha']+=$row1['pokokperha'];
		$grandtotal['luaspanen']+=$row1['luaspanen'];
		$grandtotal['harvestround']+=$row1['harvestround'];
		$grandtotal['mandays11']+=$row1['mandays11'];
		$grandtotal['mandays12']+=$row1['mandays12'];
		$grandtotal['mandays21']+=$row1['mandays21'];
		$grandtotal['mandays22']+=$row1['mandays22'];
		$grandtotal['mandays31']+=$row1['mandays31'];
		$grandtotal['mandays32']+=$row1['mandays32'];
		$grandtotal['mandays33']+=$row1['mandays33'];
		$grandtotal['kg11']+=$row1['kg11'];
		$grandtotal['kg12']+=$row1['kg12'];
		$grandtotal['kg21']+=$row1['kg21'];
		$grandtotal['kg22']+=$row1['kg22'];
		$grandtotal['kg31']+=$row1['kg31'];
		$grandtotal['kg32']+=$row1['kg32'];
		$grandtotal['kg33']+=$row1['kg33'];
		$grandtotal['yield11']+=$row1['yield11'];
		$grandtotal['yield12']+=$row1['yield12'];
		$grandtotal['yield21']+=$row1['yield21'];
		$grandtotal['yield22']+=$row1['yield22'];
		$grandtotal['yield31']+=$row1['yield31'];
		$grandtotal['yield32']+=$row1['yield32'];
		$grandtotal['yield33']+=$row1['yield33'];
		$grandtotal['perorang11']+=$row1['perorang11'];
		$grandtotal['perorang12']+=$row1['perorang12'];
		$grandtotal['perorang21']+=$row1['perorang21'];
		$grandtotal['perorang22']+=$row1['perorang22'];
		$grandtotal['perorang31']+=$row1['perorang31'];
		$grandtotal['perorang32']+=$row1['perorang32'];
		$grandtotal['perorang33']+=$row1['perorang33'];
		$grandtotal['costlabour1']+=$row1['costlabour1'];
		$grandtotal['costlabour2']+=$row1['costlabour2'];
		$grandtotal['costvhc1']+=$row1['costvhc1'];
		$grandtotal['costvhc2']+=$row1['costvhc2'];
		$grandtotal['costtotal1']+=$row1['costtotal1'];
		$grandtotal['costtotal2']+=$row1['costtotal2'];
		$grandtotal['costharv1']+=$row1['costharv1'];
		$grandtotal['costharv2']+=$row1['costharv2'];
		$grandtotal['brondolan1']+=$row1['brondolan1'];
		$grandtotal['brondolan2']+=$row1['brondolan2'];
		$grandtotal['brondolanpercent1']+=$row1['brondolanpercent1'];
		$grandtotal['brondolanpercent2']+=$row1['brondolanpercent2'];
		$grandtotal['tbs11']+=$row1['tbs11'];
		$grandtotal['tbs12']+=$row1['tbs12'];
		$grandtotal['tbs21']+=$row1['tbs21'];
		$grandtotal['tbs22']+=$row1['tbs22'];
		$grandtotal['bjr1']+=$row1['bjr1'];
		$grandtotal['bjr2']+=$row1['bjr2'];
		
		$tab .= "</tr>";
	}
	
	$subTotal12 = empty($subTotal[$afd]['hamature'])? 0: $subTotal[$afd]['jumlahpokok'] / $subTotal[$afd]['hamature'];
	$subTotalLuasPanen = $subTotal[$afd]['luaspanen'];
	$subTotal13 = empty($subTotal[$afd]['hamature'])? 0: $subTotal[$afd]['luaspanen'] / $subTotal[$afd]['hamature'];
	
	$subTotal28 = empty($subTotal[$afd]['hamature'])? 0: $subTotal[$afd]['kg11'] / $subTotal[$afd]['hamature'];
	$subTotal29 = empty($subTotal[$afd]['hamature'])? 0: $subTotal[$afd]['kg12'] / $subTotal[$afd]['hamature'];
	$subTotal30 = empty($subTotal[$afd]['hamature'])? 0: $subTotal[$afd]['kg21'] / $subTotal[$afd]['hamature'];
	$subTotal31 = empty($subTotal[$afd]['hamature'])? 0: $subTotal[$afd]['kg22'] / $subTotal[$afd]['hamature'];
	$subTotal32 = empty($subTotal[$afd]['habudget'])? 0: $subTotal[$afd]['kg31'] / $subTotal[$afd]['habudget'];
	$subTotal33 = empty($subTotal[$afd]['habudget'])? 0: $subTotal[$afd]['kg32'] / $subTotal[$afd]['habudget'];
	$subTotal34 = empty($subTotal[$afd]['habudget'])? 0: $subTotal[$afd]['kg33'] / $subTotal[$afd]['habudget'];
	
	$subTotal35 = $subTotal[$afd]['mandays11'] > 0 ? $subTotal[$afd]['kg11'] / $subTotal[$afd]['mandays11'] : 0;
	$subTotal36 = @($subTotal[$afd]['kg12'] / $subTotal[$afd]['mandays12']);
	$subTotal37 = @($subTotal[$afd]['kg21'] / $subTotal[$afd]['mandays21']);
	$subTotal38 = @($subTotal[$afd]['kg22'] / $subTotal[$afd]['mandays22']);
	$subTotal39 = @($subTotal[$afd]['kg31'] / $subTotal[$afd]['mandays31']);
	$subTotal40 = @($subTotal[$afd]['kg32'] / $subTotal[$afd]['mandays32']);
	$subTotal41 = @($subTotal[$afd]['kg33'] / $subTotal[$afd]['mandays33']);
	
	$subTotal48 = @($subTotal[$afd]['costtotal1'] / ($subTotal[$afd]['kg11'] * 1000));
	$subTotal49 = @($subTotal[$afd]['costtotal2'] / ($subTotal[$afd]['kg12'] * 1000));
	
	$subTotal52 = @(($subTotal[$afd]['brondolan1'] / $subTotal[$afd]['kg11']) * 100);
	$subTotal53 = @(($subTotal[$afd]['brondolan2'] / $subTotal[$afd]['kg12']) * 100);
	
	$subTotal58 = ($subTotal[$afd]['tbs11']==0)? 0: ($subTotal[$afd]['kg11'] * 1000) / $subTotal[$afd]['tbs11'];
	$subTotal59 = ($subTotal[$afd]['tbs12']==0)? 0: ($subTotal[$afd]['kg12'] * 1000) / $subTotal[$afd]['tbs12'];
	
	$tab .= "<tr class=rowcontent>
				<td colspan=6 style='text-align:center; font-weight:bold;'>SUB TOTAL ".$optOrg[$afd]."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['hamature'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['haharvest'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['habudget'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['haavg'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['jumlahpokok'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal12,2)."</td>
				<td style='text-align:right'>".number_format($subTotalLuasPanen,2)."</td>
				<td style='text-align:right'>".number_format($subTotal13,2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['mandays11'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['mandays12'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['mandays21'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['mandays22'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['mandays31'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['mandays32'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['mandays33'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['kg11'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['kg12'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['kg21'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['kg22'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['kg31'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['kg32'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['kg33'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal28,2)."</td>
				<td style='text-align:right'>".number_format($subTotal29,2)."</td>
				<td style='text-align:right'>".number_format($subTotal30,2)."</td>
				<td style='text-align:right'>".number_format($subTotal31,2)."</td>
				<td style='text-align:right'>".number_format($subTotal32,2)."</td>
				<td style='text-align:right'>".number_format($subTotal33,2)."</td>
				<td style='text-align:right'>".number_format($subTotal34,2)."</td>
				<td style='text-align:right'>".number_format($subTotal35,2)."</td>
				<td style='text-align:right'>".number_format($subTotal36,2)."</td>
				<td style='text-align:right'>".number_format($subTotal37,2)."</td>
				<td style='text-align:right'>".number_format($subTotal38,2)."</td>
				<td style='text-align:right'>".number_format($subTotal39,2)."</td>
				<td style='text-align:right'>".number_format($subTotal40,2)."</td>
				<td style='text-align:right'>".number_format($subTotal41,2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['costlabour1'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['costlabour2'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['costvhc1'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['costvhc2'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['costtotal1'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['costtotal2'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal48,2)."</td>
				<td style='text-align:right'>".number_format($subTotal49,2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['brondolan1'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['brondolan2'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal52,2)."</td>
				<td style='text-align:right'>".number_format($subTotal53,2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['tbs11'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['tbs12'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['tbs21'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal[$afd]['tbs22'],2)."</td>
				<td style='text-align:right'>".number_format($subTotal58,2)."</td>
				<td style='text-align:right'>".number_format($subTotal59,2)."</td>
			</tr>";
	$countAfd += count($afd);
}
// echo($countAfd);

$grandtotal['hamature']>0?$grandtotal12 = $grandtotal['jumlahpokok'] / $grandtotal['hamature']:$grandtotal12=0;
$grandtotalLuasPanen = $grandtotal['luaspanen'];
$grandtotal['hamature']>0?$grandtotal13 = $grandtotal['luaspanen'] / $grandtotal['hamature']:$grandtotal13=0;

$grandtotal['hamature']>0?$grandtotal28 = $grandtotal['kg11'] / $grandtotal['hamature']:$grandtotal28=0;
$grandtotal['hamature']>0?$grandtotal29 = $grandtotal['kg12'] / $grandtotal['hamature']:$grandtotal29=0;
$grandtotal['hamature']>0?$grandtotal30 = $grandtotal['kg21'] / $grandtotal['hamature']:$grandtotal30=0;
$grandtotal['hamature']>0?$grandtotal31 = $grandtotal['kg22'] / $grandtotal['hamature']:$grandtotal31=0;
$grandtotal['habudget']>0?$grandtotal32 = $grandtotal['kg31'] / $grandtotal['habudget']:$grandtotal32=0;
$grandtotal['habudget']>0?$grandtotal33 = $grandtotal['kg32'] / $grandtotal['habudget']:$grandtotal33=0;
$grandtotal['habudget']>0?$grandtotal34 = $grandtotal['kg33'] / $grandtotal['habudget']:$grandtotal34=0;

$grandtotal35 = $grandtotal['mandays11'] > 0 ? $grandtotal['kg11'] / $grandtotal['mandays11'] : 0;
$grandtotal36 = $grandtotal['mandays12']>0?($grandtotal['kg12'] / $grandtotal['mandays12']):0;
$grandtotal37 = $grandtotal['mandays21']>0?($grandtotal['kg21'] / $grandtotal['mandays21']):0;
$grandtotal38 = $grandtotal['mandays22']>0?($grandtotal['kg22'] / $grandtotal['mandays22']):0;
$grandtotal39 = $grandtotal['mandays31']>0?($grandtotal['kg31'] / $grandtotal['mandays31']):0;
$grandtotal40 = $grandtotal['mandays32']>0?($grandtotal['kg32'] / $grandtotal['mandays32']):0;
$grandtotal41 = $grandtotal['mandays33']>0?($grandtotal['kg33'] / $grandtotal['mandays33']):0;

$grandtotal48 = $grandtotal['kg11']>0?($grandtotal['costtotal1'] / ($grandtotal['kg11'] * 1000)):0;
$grandtotal49 = $grandtotal['kg12']>0?($grandtotal['costtotal2'] / ($grandtotal['kg12'] * 1000)):0;

$grandtotal52 = $grandtotal['kg11']>0?(($grandtotal['brondolan1'] / $grandtotal['kg11']) * 100):0;
$grandtotal53 = $grandtotal['kg12']>0?(($grandtotal['brondolan2'] / $grandtotal['kg12']) * 100):0;

$grandtotal58 = ($grandtotal['tbs11']==0)? 0: ($grandtotal['kg11'] * 1000) / $grandtotal['tbs11'];
$grandtotal59 = ($grandtotal['tbs12']==0)? 0: ($grandtotal['kg12'] * 1000) / $grandtotal['tbs12'];

$tab .= "<tr class=rowcontent>
			<td colspan=6 style='text-align:center; font-weight:bold;'>GRAND TOTAL</td>
			<td style='text-align:right'>".number_format($grandtotal['hamature'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['haharvest'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['habudget'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['haavg'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['jumlahpokok'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal12,2)."</td>
			<td style='text-align:right'>".number_format($grandtotalLuasPanen,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal13,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['mandays11'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['mandays12'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['mandays21'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['mandays22'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['mandays31'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['mandays32'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['mandays33'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['kg11'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['kg12'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['kg21'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['kg22'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['kg31'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['kg32'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['kg33'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal28,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal29,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal30,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal31,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal32,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal33,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal34,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal35,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal36,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal37,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal38,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal39,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal40,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal41,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['costlabour1'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['costlabour2'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['costvhc1'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['costvhc2'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['costtotal1'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['costtotal2'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal48,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal49,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['brondolan1'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['brondolan2'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal52,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal53,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['tbs11'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['tbs12'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['tbs21'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal['tbs22'],2)."</td>
			<td style='text-align:right'>".number_format($grandtotal58,2)."</td>
			<td style='text-align:right'>".number_format($grandtotal59,2)."</td>
		</tr>";
$tab .= "</tbody>";

$tab .= "</table>";

/** Output Type **/
if($mode=='excel') {
	$stream = $tab;
	// exit("warning: test");
	$nop_="CropStatistic_".date('Ymd_His');
	if(strlen($stream)>0) {
		# Delete if exist
		if ($handle = opendir('tempExcel')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != "index.html") {
					@unlink('tempExcel/'.$file);
				}
			}	
			closedir($handle);
		}
		
		# Write to File
		$handle=fopen("tempExcel/".$nop_.".xls",'w');
		if(!fwrite($handle,$stream)) {
			echo "Error : Tidak bisa menulis ke format excel";
			exit;
		} else {
			echo $nop_;
		}
		fclose($handle);
	}
} else {
	echo $tab;
}