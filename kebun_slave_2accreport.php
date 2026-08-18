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
} else {
    $param = $_POST;
}

if($_GET['level']==4){
	$param = $_GET;
}

if($mode=='excel'){
	if($_GET['level']==4){
		$param = $_POST;
	}
}

// Clear 'Rep'
foreach($param as $k=>$r) {
	if(strpos($k,'Rep')) {
		$param[str_replace('Rep','',$k)] = $r;
		unset($param[$k]);
	}
}

// Level Report
if(!isset($_GET['level'])) $level = 1;
else $level = $_GET['level'];
if($level<1) $level = 1;

// Tanggal
$tgl = tanggalsystem($param['tanggal']);
$tglArr = explode('-',$param['tanggal']);
$day = $tglArr[0];
$month = $tglArr[1];
$year = $tglArr[2];
$monthPad = str_pad($month,2,'0',STR_PAD_LEFT);

// List Kebun
if(empty($param['kebun'])) {
	$qKebun = selectQuery($dbname,'organisasi','kodeorganisasi,namaorganisasi',
						  "induk = '".$param['pt']."' and tipe = 'KEBUN'");
	$resKebun = fetchData($qKebun);
	if(!empty($resKebun)) {
		$param['kebun'] = array();
		foreach($resKebun as $row) {
			$param['kebun'][$row['kodeorganisasi']] = $row['kodeorganisasi'];
		}
	}
}

if(empty($param['kebun'])) exit("Warning: PT.".$param['pt']." tidak memiliki kebun");

// Options
if(is_array($param['kebun'])) {
	$whereBlok = "LEFT(kodeorg,4) in ('".implode("','",$param['kebun'])."')";
	$whereBlokBud = "LEFT(kodeblok,4) in ('".implode("','",$param['kebun'])."')";
	$whereBud = "LEFT(a.kodeorg,4) in ('".implode("','",$param['kebun'])."')";
} else {
	$whereBlok = "LEFT(kodeorg,4) = '".$param['kebun']."'";
	$whereBlokBud = "LEFT(kodeblok,4) = '".$param['kebun']."'";
	$whereBud = "LEFT(a.kodeorg,4) = '".$param['kebun']."'";
}

// Master Blok & Satuan Kegiatan
if($level==3) {
	$optSat = makeOption($dbname,'setup_kegiatan','kodekegiatan,satuan');
	foreach($optSat as $code=>$val) {
		$optSat[substr($code,0,7)] = $val;
		unset($optSat[$code]);
	}
}
if($param['tipe']=='BIBIT') {
	$qBlok = selectQuery($dbname,'setup_blok',"kodeorg,tahuntanam,statusblok,jumlahpokok,luasareaproduktif",
						   $whereBlok." and statusblok = 'BBT'");
} else {
	$qBlok = selectQuery($dbname,'setup_blok',"kodeorg,tahuntanam,statusblok,jumlahpokok,luasareaproduktif",
						   $whereBlok." and statusblok != 'BBT'");
}
$resBlok = fetchData($qBlok);
$optBlok = $pokokBlok = $luasBlok = array();
$allPokok = 0;
foreach($resBlok as $blok) {
	$optBlok[$blok['kodeorg']] = $blok;
	
	if($blok['statusblok']=='TM') $blok['tahuntanam'] = "";
	
	setIt($pokokBlok[$blok['statusblok'].$blok['tahuntanam']],0);
	setIt($luasBlok[$blok['statusblok'].$blok['tahuntanam']],0);
	
	if($param['tipe']=='BIBIT'){
		$sPokok = "select kodeorg,sum(jumlah) as jumlah from ".$dbname.".bibitan_mutasi where kodeorg='".$blok['kodeorg']."' and post = '1' and tanggal <= '".$tgl."' group by kodeorg";
		$qPokok=$owlPDO->query($sPokok) or die(print " Gagal: ".PDOException::getMessage());
		$qPokok->setFetchMode(PDO::FETCH_ASSOC);
		$rPokok = $qPokok->fetch();
		// echo $sPokok;
		// echo $rPokok['kodeorg']."__".$rPokok['jumlah']."<br>";
		$pokokBlok[$blok['statusblok'].$blok['tahuntanam']] += $rPokok['jumlah'];
	}else{
		$pokokBlok[$blok['statusblok'].$blok['tahuntanam']] += $blok['jumlahpokok'];
	}
	$luasBlok[$blok['statusblok'].$blok['tahuntanam']] += $blok['luasareaproduktif'];
	if($blok['statusblok']=='TM') {
		$allPokok += $blok['jumlahpokok'];
	}
}

// Master Blok & Kegiatan Budget
if($param['tipe']=='BIBIT') {
	$qBlokBud = selectQuery($dbname,'bgt_blok',"kodeblok,thntnm as tahuntanam,statusblok,".
							"pokokthnini as jumlahpokok,hathnini as luasareaproduktif",
							$whereBlokBud." and statusblok = 'BBT' and tahunbudget='".$year."'");
} else {
	$qBlokBud = selectQuery($dbname,'bgt_blok',"kodeblok,thntnm as tahuntanam,statusblok,".
							"pokokthnini as jumlahpokok,hathnini as luasareaproduktif",
							$whereBlokBud." and statusblok != 'BBT' and tahunbudget='".$year."'");
}
$resBlokBud = fetchData($qBlokBud);
$optBlokBud = $pokokBlokBud = $luasBlokBud = array();
$allPokokBud = 0;
foreach($resBlokBud as $blok) {
	$optBlokBud[$blok['kodeblok']] = $blok;
	
	if($blok['statusblok']=='TM') $blok['tahuntanam'] = "";
	
	setIt($pokokBlokBud[$blok['statusblok'].$blok['tahuntanam']],0);
	setIt($luasBlokBud[$blok['statusblok'].$blok['tahuntanam']],0);
	
	$pokokBlokBud[$blok['statusblok'].$blok['tahuntanam']] += $blok['jumlahpokok'];
	$luasBlokBud[$blok['statusblok'].$blok['tahuntanam']] += $blok['luasareaproduktif'];
	if($blok['statusblok']=='TM') {
		$allPokokBud += $blok['jumlahpokok'];
	}
}

// Akun
if($param['tipe']=='BIBIT') {
	$listAkun = "'128'";
	$stsBlok = "'BBT'";
} else {
	if($level==3 || $level==4) {
		if($param['statustanam'] == 'TM') {
			$listAkun = "'621'";
			$stsBlok = "'TM'";
		} elseif($param['statustanam'] == 'TBM') {
			$listAkun = "'126'";
			$stsBlok = "'TBM'";
		} else {
			$listAkun = "'128'";
			$stsBlok = "'TB'";
		}
	} else {
		$listAkun = "'126','128','621','611'";
		$stsBlok = "'TBM','TM','TB'";
	}
}
$optAkun = makeOption($dbname,'keu_5akun','noakun,namaakun',
					  "LENGTH(noakun) = 5 AND LEFT(noakun,3) in (".$listAkun.")");
ksort($optAkun);

if($level==3) { // Untuk Level 3, sediakan master nama akun length 7
	$optAkun7 = makeOption($dbname,'keu_5akun','noakun,namaakun',
						   "LENGTH(noakun) = 7 AND LEFT(noakun,3) in (".$listAkun.")");
	ksort($optAkun7);
}

/*******************************************************************************
 ** Data Jurnal ****************************************************************
 *******************************************************************************/
// Get Data Jurnal
if(is_array($param['kebun'])) {
	$whereKodeorg = "a.kodeorg in ('".implode("','",$param['kebun'])."')";
	$whereKodeorg2 = "c.kodeorg in ('".implode("','",$param['kebun'])."')";
	// $whereKodeorg3 = "a.alokasibiaya in ('".implode("','",$param['kebun'])."')";
	$whereKodeorg4 = "LEFT(a.kodeorg,4) in ('".implode("','",$param['kebun'])."')";
	$whereKodeorg5 = "LEFT(a.kodeblok,4) in ('".implode("','",$param['kebun'])."')";
	$countArray=0;
	foreach($param['kebun'] as $val){
		$countArray += 1;
		if($countArray==1){
			$whereKodeorg3 .= "a.alokasibiaya like '%".$val."%'";
		}else{
			$whereKodeorg3 .= " or a.alokasibiaya like '%".$val."%'";
		}
	}
} else {
	$whereKodeorg = "a.kodeorg = '".$param['kebun']."'";
	$whereKodeorg2 = "c.kodeorg = '".$param['kebun']."'";
	$whereKodeorg3 = "a.alokasibiaya like '%".$param['kebun']."%'";
	$whereKodeorg4 = "LEFT(a.kodeorg,4) = '".$param['kebun']."'";
	$whereKodeorg5 = "LEFT(a.kodeblok,4) = '".$param['kebun']."'";
}
//$month3 = ($level<3) ? "": " AND MONTH(a.tanggal)='".$month."'";
$month3 = "";
$tt3 = (($level==3 || $level==4) and $param['statustanam']!='TM') ?" AND b.tahuntanam='".$param['tahuntanam']."'": "";
$akun3 = ($level<3) ? "LEFT(a.noakun,5)": "LEFT(a.noakun,7)";
$akun4 = ($level==4) ? "LEFT(a.noakun,7) in (".$param['noakun'].")" : "LEFT(a.noakun,3) in (".$listAkun.")";
$hgroupBy = ($level==4) ? "GROUP BY a.kodeblok, a.kodevhc, a.tanggal, a.nojurnal, c.kodejurnal, ".$akun3.", nik" : "GROUP BY a.tanggal,  c.kodejurnal, ".$akun3.", kodeblok, a.kodevhc, nik, kodebarang";
// $sumJum = ($level<4) ? "sum(a.jumlah)" : "a.jumlah";
$qData = "SELECT a.tanggal, a.noreferensi, f.kodebarang as kdbrg2, c.kodejurnal, ".$akun3." as noakun, kodeblok, a.kodevhc, nik, a.kodebarang, sum(a.jumlah) as jumlah, e.namajenisvhc, d.jenisvhc
	FROM ".$dbname.".keu_jurnaldt_vw a
	INNER JOIN ".$dbname.".setup_blok b ON a.kodeblok = b.kodeorg AND b.intiplasma like '%".$param['tipekebun']."%'
	LEFT JOIN ".$dbname.".keu_jurnalht c ON a.nojurnal = c.nojurnal
	LEFT JOIN ".$dbname.".vhc_5master d ON a.kodevhc = d.kodevhc 
	LEFT JOIN ".$dbname.".vhc_5jenisvhc e ON d.jenisvhc = e.jenisvhc 
	LEFT JOIN ".$dbname.".kebun_pakaimaterial f ON a.noreferensi = f.notransaksi 
	WHERE YEAR(a.tanggal) = '".$year."'".$month3." AND a.tanggal <= '".$tgl."' AND ".$whereKodeorg."
	AND ".$akun4." AND kodeblok != '' AND statusblok in (".$stsBlok.") ".$tt3."
	".$hgroupBy."
	ORDER BY kodeblok ASC, b.statusblok ASC,  b.tahuntanam DESC, a.noakun ASC";
$resData = fetchData($qData);
// echo $qData;

// Rearrange Data
$data = $data2 = $data3 = $data4 = $dataPnn = $data2Pnn = $noPanen = array();
$dataMatJur = array(); // Init Data Material dari Jurnal
foreach($resData as $row) {
	if($level < 3) {
		// Report Level 1 & 2
		$tmpSts = $optBlok[$row['kodeblok']]['statusblok'];
		$tmpTT = ($tmpSts=='TM')? '': $optBlok[$row['kodeblok']]['tahuntanam'];
		
		// Flag ada panen atau tidak
		if(!isset($allPanen[$tmpSts.$tmpTT])) $allPanen[$tmpSts.$tmpTT] = true;
		if(substr($row['noakun'],0,3)!='611') $allPanen[$tmpSts.$tmpTT] = false;
		
		// To date Data
		// Rawat
		if(!isset($data[$tmpSts][$tmpTT][$row['noakun']]['todate'])) {
			$data[$tmpSts][$tmpTT][$row['noakun']]['todate'] = $row['jumlah'];
			
			$data2[$tmpSts][$tmpTT][$row['noakun']]['todate'] = array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
			$data2[$tmpSts][$tmpTT][$row['noakun']]['todate']['total'] = $row['jumlah'];
			
			if(substr($row['kodejurnal'],0,3) == 'SPK') $data2[$tmpSts][$tmpTT][$row['noakun']]['todate']['lain'] = $row['jumlah'];
			elseif(substr($row['kodejurnal'],0,3) == 'VHC') $data2[$tmpSts][$tmpTT][$row['noakun']]['todate']['transport'] = $row['jumlah'];
			elseif(substr($row['kodejurnal'],0,4) == 'INVK') $data2[$tmpSts][$tmpTT][$row['noakun']]['todate']['material'] = $row['jumlah'];
			else $data2[$tmpSts][$tmpTT][$row['noakun']]['todate']['upah'] = $row['jumlah'];
		} else {
			$data[$tmpSts][$tmpTT][$row['noakun']]['todate'] += $row['jumlah'];
			
			$data2[$tmpSts][$tmpTT][$row['noakun']]['todate']['total'] += $row['jumlah'];
			if(substr($row['kodejurnal'],0,3) == 'SPK') $data2[$tmpSts][$tmpTT][$row['noakun']]['todate']['lain'] += $row['jumlah'];
			elseif(substr($row['kodejurnal'],0,3) == 'VHC') $data2[$tmpSts][$tmpTT][$row['noakun']]['todate']['transport'] += $row['jumlah'];
			elseif(substr($row['kodejurnal'],0,4) == 'INVK') $data2[$tmpSts][$tmpTT][$row['noakun']]['todate']['material'] += $row['jumlah'];
			else $data2[$tmpSts][$tmpTT][$row['noakun']]['todate']['upah'] += $row['jumlah'];
		}
		// Panen
		if(substr($row['noakun'],0,3)=='611') {
			if(!isset($dataPnn[$row['noakun']]['todate'])) {
				$dataPnn[$row['noakun']]['todate'] = $row['jumlah'];
				
				$data2Pnn[$row['noakun']]['todate'] = array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
				$data2Pnn[$row['noakun']]['todate']['total'] = $row['jumlah'];
				if($row['noakun']=='61101'){
					if(substr($row['kodejurnal'],0,3) == 'INV'){
						$data2Pnn[$row['noakun']]['todate']['material'] = $row['jumlah'];
					}else{
						$data2Pnn[$row['noakun']]['todate']['upah'] = $row['jumlah'];
					}
				}
				else{
					$data2Pnn[$row['noakun']]['todate']['transport'] = $row['jumlah'];
				}
			} else {
				$dataPnn[$row['noakun']]['todate'] += $row['jumlah'];
				
				$data2Pnn[$row['noakun']]['todate']['total'] += $row['jumlah'];
				if($row['noakun']=='61101'){
					if(substr($row['kodejurnal'],0,3) == 'INV'){
						$data2Pnn[$row['noakun']]['todate']['material'] += $row['jumlah'];
					}else{
						$data2Pnn[$row['noakun']]['todate']['upah'] += $row['jumlah'];
					}
				}
				else{
					$data2Pnn[$row['noakun']]['todate']['transport'] += $row['jumlah'];
				}
			}
		}
		
		// This Month Data
		if(substr(str_replace('-','',$row['tanggal']),0,6) == substr($tgl,0,6)) {
			// Rawat
			if(!isset($data[$tmpSts][$tmpTT][$row['noakun']]['month'])) {
				$data[$tmpSts][$tmpTT][$row['noakun']]['month'] = $row['jumlah'];
				
				$data2[$tmpSts][$tmpTT][$row['noakun']]['month'] = array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
				$data2[$tmpSts][$tmpTT][$row['noakun']]['month']['total'] = $row['jumlah'];
				if(substr($row['kodejurnal'],0,3) == 'SPK') $data2[$tmpSts][$tmpTT][$row['noakun']]['month']['lain'] = $row['jumlah'];
				elseif(substr($row['kodejurnal'],0,3) == 'VHC') $data2[$tmpSts][$tmpTT][$row['noakun']]['month']['transport'] = $row['jumlah'];
				elseif(substr($row['kodejurnal'],0,4) == 'INVK') $data2[$tmpSts][$tmpTT][$row['noakun']]['month']['material'] = $row['jumlah'];
				else $data2[$tmpSts][$tmpTT][$row['noakun']]['month']['upah'] = $row['jumlah'];
			} else {
				$data[$tmpSts][$tmpTT][$row['noakun']]['month'] += $row['jumlah'];
				
				$data2[$tmpSts][$tmpTT][$row['noakun']]['month']['total'] += $row['jumlah'];
				if(substr($row['kodejurnal'],0,3) == 'SPK') $data2[$tmpSts][$tmpTT][$row['noakun']]['month']['lain'] += $row['jumlah'];
				elseif(substr($row['kodejurnal'],0,3) == 'VHC') $data2[$tmpSts][$tmpTT][$row['noakun']]['month']['transport'] += $row['jumlah'];
				elseif(substr($row['kodejurnal'],0,4) == 'INVK') $data2[$tmpSts][$tmpTT][$row['noakun']]['month']['material'] += $row['jumlah'];
				else $data2[$tmpSts][$tmpTT][$row['noakun']]['month']['upah'] += $row['jumlah'];
			}
			// Panen
			if(substr($row['noakun'],0,3)=='611') {
				if(!isset($dataPnn[$row['noakun']]['month'])) {
					$dataPnn[$row['noakun']]['month'] = $row['jumlah'];
					
					$data2Pnn[$row['noakun']]['month'] = array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
					$data2Pnn[$row['noakun']]['month']['total'] = $row['jumlah'];
					if($row['noakun']=='61101'){
						if(substr($row['kodejurnal'],0,3) == 'INV'){
							$data2Pnn[$row['noakun']]['month']['material'] = $row['jumlah'];
						}else{
							$data2Pnn[$row['noakun']]['month']['upah'] = $row['jumlah'];
						}
					}
					else{
						$data2Pnn[$row['noakun']]['month']['transport'] = $row['jumlah'];
					} 
				} else {
					$dataPnn[$row['noakun']]['month'] += $row['jumlah'];
					
					$data2Pnn[$row['noakun']]['month']['total'] += $row['jumlah'];
					if($row['noakun']=='61101'){
						if(substr($row['kodejurnal'],0,3) == 'INV'){
							$data2Pnn[$row['noakun']]['month']['material'] += $row['jumlah'];
						}else{
							$data2Pnn[$row['noakun']]['month']['upah'] += $row['jumlah'];
						}
					}
					else{
						$data2Pnn[$row['noakun']]['month']['transport'] += $row['jumlah'];
					}
				}
			}
		}
	} else if($level == 3) {
		// Report Level 3
		if(!isset($data3[substr($row['noakun'],0,5)][$row['noakun']])) {
			$data3[substr($row['noakun'],0,5)][$row['noakun']] = array(
				'todate' => array('upah' => 0, 'material' => array(), 'transport' => array(), 'lain'=>0, 'total' => 0 ),
				'today' => array('upah' => 0, 'material' => array(), 'transport' => array(), 'lain'=>0, 'total' => 0 )
			);
		}
		
		if(substr($row['kodejurnal'],0,3) == 'SPK') $data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['lain'] += $row['jumlah'];
		elseif(substr($row['kodejurnal'],0,3) == 'VHC') {
			setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['transport'][$row['kodevhc']],0);
			setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['transport']['total'],0);
			$data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['kodeblok'] = $row['kodeblok'];
			$data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['myvhc'] = $row['kodevhc'];
			$data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['transport'][$row['kodevhc']] += $row['jumlah'];
			$data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['transport']['total'] += $row['jumlah'];
		}
		elseif(substr($row['kodejurnal'],0,4) == 'INVK') {
			if($row['kodebarang'] != ""){
				setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['material'][$row['kodebarang']],0);
				$data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['material'][$row['kodebarang']] += $row['jumlah'];
			}else{
				setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['material'][$row['kdbrg2']],0);
				$data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['material'][$row['kdbrg2']] += $row['jumlah'];
			}
			setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['material']['total'],0);
			$data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['material']['total'] += $row['jumlah'];
			
			/**** Mark ada Barang di Akun tersebut ****/
			// $dataMatJur[$row['noakun']][$row['kodebarang']] = 1;
		}
		else {
			setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['upah'],0);
			setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['total'],0);			
			$data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['upah'] += $row['jumlah'];
			$data3[substr($row['noakun'],0,5)][$row['noakun']]['todate']['total'] += $row['jumlah'];
		}
		
		//if(str_replace('-','',$row['tanggal']) == $tgl) {
		if(substr(str_replace('-','',$row['tanggal']),0,6) == substr($tgl,0,6)) {
			if(substr($row['kodejurnal'],0,3) == 'SPK') $data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['lain'] += $row['jumlah'];
			elseif(substr($row['kodejurnal'],0,3) == 'VHC') {
				setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['transport'][$row['kodevhc']],0);
				setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['transport']['total'],0);
				$data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['transport'][$row['kodevhc']] += $row['jumlah'];
				$data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['transport']['total'] += $row['jumlah'];
			}
			elseif(substr($row['kodejurnal'],0,4) == 'INVK') {
				if($row['kodebarang'] != ""){
					setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['material'][$row['kodebarang']],0);
					$data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['material'][$row['kodebarang']] += $row['jumlah'];
				}else{
					setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['material'][$row['kdbrg2']],0);
					$data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['material'][$row['kdbrg2']] += $row['jumlah'];
				}
				setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['material']['total'],0);
				$data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['material']['total'] += $row['jumlah'];
			}
			else {
				setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['upah'],0);
				setIt($data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['total'],0);
				$data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['upah'] += $row['jumlah'];
				$data3[substr($row['noakun'],0,5)][$row['noakun']]['today']['total'] += $row['jumlah'];
			}
		}
	} else {
		// Report Level 4
		if(!isset($data4[$row['kodeblok']])) {
			$data4[$row['kodeblok']] = array(
				'todate' => array('upah' => 0, 'material' => array(), 'transport' => array(), 'lain'=>0, 'total' => 0 ),
				'today' => array('upah' => 0, 'material' => array(), 'transport' => array(), 'lain'=>0, 'total' => 0 )
			);
		}
		
		if(!isset($data5[$row['kodeblok']."".$row['vhc']])) {
			$data5[$row['kodeblok']."".$row['vhc']] = array(
				'todate' => 0,
				'today' => 0
			);
		}
		
		$data5[$row['kodeblok']."".$row['kodevhc']]['blok'] = $row['kodeblok'];
		$data5[$row['kodeblok']."".$row['kodevhc']]['vhc'] = $row['kodevhc'];
		$data5[$row['kodeblok']."".$row['kodevhc']]['type'] = $row['jenisvhc']." - ".$row['namajenisvhc'];
		
		if(substr($row['kodejurnal'],0,3) == 'SPK') $data4[$row['kodeblok']]['todate']['lain'] += $row['jumlah'];
		elseif(substr($row['kodejurnal'],0,3) == 'VHC') {
			setIt($data4[$row['kodeblok']]['todate']['transport'][$row['kodevhc']],0);
			setIt($data4[$row['kodeblok']]['todate']['transport']['total'],0);
			
			setIt($data5[$row['kodeblok']."".$row['kodevhc']]['todate'],0);
			
			$data5[$row['kodeblok']."".$row['kodevhc']]['todate'] += $row['jumlah'];
			
			$data4[$row['kodeblok']]['transport']['vhc'] = $row['kodevhc'];
			$data4[$row['kodeblok']]['transport']['type'] = $row['jenisvhc']." - ".$row['namajenisvhc'];
			$data4[$row['kodeblok']]['todate']['transport'][$row['kodevhc']] += $row['jumlah'];
			$data4[$row['kodeblok']]['todate']['transport']['total'] += $row['jumlah'];
			// echo $row['kodeblok']."__".$row['kodevhc']."__".$row['jumlah']."<p>";
		}
		elseif(substr($row['kodejurnal'],0,4) == 'INVK') {
			setIt($data4[$row['kodeblok']]['todate']['material'][$row['kodebarang']],0);
			setIt($data4[$row['kodeblok']]['todate']['material']['total'],0);
			$data4[$row['kodeblok']]['todate']['material'][$row['kodebarang']] += $row['jumlah'];
			$data4[$row['kodeblok']]['todate']['material']['total'] += $row['jumlah'];
			
			/**** Mark ada Barang di Akun tersebut ****/
			//$dataMatJur[$row['noakun']][$row['kodebarang']] = 1;
		}
		else {
			setIt($data4[$row['kodeblok']]['todate']['upah'],0);
			setIt($data4[$row['kodeblok']]['todate']['total'],0);
			$data4[$row['kodeblok']]['todate']['upah'] += $row['jumlah'];
			$data4[$row['kodeblok']]['todate']['total'] += $row['jumlah'];
		}
		
		//if(str_replace('-','',$row['tanggal']) == $tgl) {
		if(substr(str_replace('-','',$row['tanggal']),0,6) == substr($tgl,0,6)) {
			if(substr($row['kodejurnal'],0,3) == 'SPK') $data4[$row['kodeblok']]['today']['lain'] += $row['jumlah'];
			elseif(substr($row['kodejurnal'],0,3) == 'VHC') {
				setIt($data4[$row['kodeblok']]['today']['transport'][$row['kodevhc']],0);
				setIt($data4[$row['kodeblok']]['today']['transport']['total'],0);
				setIt($data5[$row['kodeblok']."".$row['kodevhc']]['today'],0);
				
				$data5[$row['kodeblok']."".$row['kodevhc']]['today'] += $row['jumlah'];
				
				$data4[$row['kodeblok']]['today']['transport'][$row['kodevhc']] += $row['jumlah'];
				$data4[$row['kodeblok']]['today']['transport']['total'] += $row['jumlah'];
			}
			elseif(substr($row['kodejurnal'],0,4) == 'INVK') {
				setIt($data4[$row['kodeblok']]['today']['material'][$row['kodebarang']],0);
				setIt($data4[$row['kodeblok']]['today']['material']['total'],0);
				$data4[$row['kodeblok']]['today']['material'][$row['kodebarang']] += $row['jumlah'];
				$data4[$row['kodeblok']]['today']['material']['total'] += $row['jumlah'];
			}
			else {
				setIt($data4[$row['kodeblok']]['today']['upah'],0);
				setIt($data4[$row['kodeblok']]['today']['total'],0);
				$data4[$row['kodeblok']]['today']['upah'] += $row['jumlah'];
				$data4[$row['kodeblok']]['today']['total'] += $row['jumlah'];
			}
		}
	}
}

/*******************************************************************************
 ** Data Kebun Prestasi ********************************************************
 *******************************************************************************/
if($level==3) {
	$qPres = "SELECT c.tanggal,LEFT(a.kodekegiatan,7) as akun,sum(a.hasilkerja) as hasilkerja, sum(jumlahhk) as hk
		FROM ".$dbname.".kebun_prestasi a
		INNER JOIN ".$dbname.".setup_blok b ON a.kodeorg = b.kodeorg AND b.intiplasma like '%".$param['tipekebun']."%' 
		LEFT JOIN ".$dbname.".kebun_aktifitas c ON a.notransaksi = c.notransaksi
		WHERE YEAR(c.tanggal) = '".$year."'
		AND c.tanggal <= '".$tgl."' AND ".$whereKodeorg2." AND b.statusblok in (".$stsBlok.") ".$tt3.
		" AND a.kodekegiatan <> 0
		GROUP BY c.tanggal,LEFT(a.kodekegiatan,7)";
	$resPres = fetchData($qPres);
	$dataPres = array();
	foreach($resPres as $row) {
		if(!isset($dataPres[$row['akun']])) {
			$dataPres[$row['akun']] = array(
				'todate' => array('hasil'=>0, 'hk' => 0),
				'today' => array('hasil'=>0, 'hk' => 0)
			);
		}
		$dataPres[$row['akun']]['todate']['hasil'] += $row['hasilkerja'];
		$dataPres[$row['akun']]['todate']['hk'] += $row['hk'];
		
		if(substr(str_replace('-','',$row['tanggal']),0,6) == substr($tgl,0,6)) {
			$dataPres[$row['akun']]['today']['hasil'] += $row['hasilkerja'];
			$dataPres[$row['akun']]['today']['hk'] += $row['hk'];
		}
	}
}

/*******************************************************************************
 ** Data Kebun Pakai Material **************************************************
 *******************************************************************************/
if($level==3) {
	$qMat2 = "SELECT x.tanggal, LEFT(x.kodekegiatan,7) as akun,a.kodebarang,
		e.namabarang, e.satuan, (a.jumlah) as vol, (a.jumlah * hargarata) as rupiah 
		FROM ".$dbname.".keu_jurnaldt_vw x 
		LEFT JOIN ".$dbname.".log_transaksidt a ON x.noreferensi = a.notransaksi AND x.kodebarang = a.kodebarang 
		INNER JOIN ".$dbname.".setup_blok b ON a.kodeblok = b.kodeorg AND b.intiplasma like '%".$param['tipekebun']."%' 
		LEFT JOIN ".$dbname.".log_5masterbarang e ON a.kodebarang = e.kodebarang
		WHERE YEAR(x.tanggal) = '".$year."' 
		AND x.tanggal <= '".$tgl."' AND x.nojurnal like '%INVK%' AND (x.kodebarang != '' OR x.kodebarang != null) AND ".$whereKodeorg5." AND b.statusblok in (".$stsBlok.") ".$tt3."";
	$resMat2 = fetchData($qMat2);
	// echo $qMat2."<p><p>";
	
	$qMat = "SELECT x.tanggal,LEFT(x.kodekegiatan,7) as akun,y.kodebarang,
		e.namabarang, e.satuan, (a.kwantitas) as vol, (kwantitas * hargasatuan) as rupiah
		FROM ".$dbname.".keu_jurnaldt_vw x
		LEFT JOIN ".$dbname.".keu_jurnaldt_vw y on x.nojurnal = y.nojurnal AND y.kodebarang != ''
		LEFT JOIN ".$dbname.".kebun_pakai_material_vw a on y.noreferensi = a.notransaksi AND y.kodebarang = a.kodebarang
		INNER JOIN ".$dbname.".setup_blok b ON a.kodeorg = b.kodeorg AND b.intiplasma like '%".$param['tipekebun']."%' 
		LEFT JOIN ".$dbname.".log_5masterbarang e ON a.kodebarang = e.kodebarang
		WHERE YEAR(x.tanggal) = '".$year."'
		AND x.tanggal <= '".$tgl."' AND x.nojurnal like '%INVK%' AND (x.kodebarang = '' OR x.kodebarang = null) AND ".$whereKodeorg4." AND b.statusblok in (".$stsBlok.") ".$tt3."";
	$resMat = fetchData($qMat);
	// echo $qMat;
	$dataMat = array();
	foreach($resMat as $row) {
		if(!isset($dataMat[$row['akun']][$row['kodebarang']])) {
			$dataMat[$row['akun']][$row['kodebarang']] = array(
				'nama' => $row['namabarang'],
				'satuan' => $row['satuan'],
				'todate' => array('vol'=>0,'rupiah'=>0),
				'today' => array('vol'=>0,'rupiah'=>0),
			);
		}
		$dataMat[$row['akun']][$row['kodebarang']]['todate']['vol'] += $row['vol'];
		$dataMat[$row['akun']][$row['kodebarang']]['todate']['rupiah'] += $row['rupiah'];
		
		if(substr(str_replace('-','',$row['tanggal']),0,6) == substr($tgl,0,6)) {
			$dataMat[$row['akun']][$row['kodebarang']]['today']['vol'] += $row['vol'];
			$dataMat[$row['akun']][$row['kodebarang']]['today']['rupiah'] += $row['rupiah'];
		}
	}
	
	foreach($resMat2 as $row) {
		if(!isset($dataMat[$row['akun']][$row['kodebarang']])) {
			$dataMat[$row['akun']][$row['kodebarang']] = array(
				'nama' => $row['namabarang'],
				'satuan' => $row['satuan'],
				'todate' => array('vol'=>0,'rupiah'=>0),
				'today' => array('vol'=>0,'rupiah'=>0),
			);
		}
		$dataMat[$row['akun']][$row['kodebarang']]['todate']['vol'] += $row['vol'];
		$dataMat[$row['akun']][$row['kodebarang']]['todate']['rupiah'] += $row['rupiah'];
		
		if(substr(str_replace('-','',$row['tanggal']),0,6) == substr($tgl,0,6)) {
			$dataMat[$row['akun']][$row['kodebarang']]['today']['vol'] += $row['vol'];
			$dataMat[$row['akun']][$row['kodebarang']]['today']['rupiah'] += $row['rupiah'];
		}
	}
}

/*******************************************************************************
 ** Data Kebun Traksi **********************************************************
 *******************************************************************************/
if($level==3) {
	$qVhc = "SELECT a.tanggal,LEFT(c.noakun,7) as akun, sum(a.kmkeluar - a.kmmasuk) as vol, a.kodevhc
		FROM ".$dbname.".vhc_run_vw a
		INNER JOIN ".$dbname.".setup_blok b ON a.alokasibiaya = b.kodeorg AND b.intiplasma like '%".$param['tipekebun']."%' 
		LEFT JOIN ".$dbname.".vhc_kegiatan c ON a.jenispekerjaan = c.kodekegiatan
		WHERE YEAR(a.tanggal) = '".$year."' 
		AND a.tanggal <= '".$tgl."' AND (".$whereKodeorg3.") AND b.statusblok in (".$stsBlok.") ".$tt3.
		" AND length(a.alokasibiaya) > 6
		GROUP BY a.tanggal, LEFT(c.noakun,7), a.alokasibiaya"; 
		// echo $qVhc;
	$resVhc = fetchData($qVhc);
	$dataVhc = array();
	foreach($resVhc as $row) {
		
		if(!isset($dataVhc[$row['akun']])) {
			$dataVhc[$row['akun']] = array('todate'=>0,'today'=>0);
		}
		$dataVhc[$row['akun']]['todate'] += $row['vol'];
		
		$tglAwal = substr($tgl,0,6)."01";
		if(str_replace('-','',$row['tanggal']) >= $tglAwal and str_replace('-','',$row['tanggal']) <= $tgl) {
			$dataVhc[$row['akun']]['today'] += $row['vol'];
		}
	}
}
//uje
 
/*******************************************************************************
 ** Data Budget ****************************************************************
 *******************************************************************************/

if($level < 3) {
	$qBud = "SELECT a.tahunbudget, a.kodebudget, a.kodeorg, LEFT(noakun,5) as noakun, sum(rupiah) as rptotal,
		sum(rp01) as rp01, sum(rp02) as rp02, sum(rp03) as rp03, sum(rp04) as rp04,
		sum(rp05) as rp05, sum(rp06) as rp06, sum(rp07) as rp07, sum(rp08) as rp08,
		sum(rp09) as rp09, sum(rp10) as rp10, sum(rp11) as rp11, sum(rp12) as rp12
		FROM ".$dbname.".bgt_budget a
		INNER JOIN ".$dbname.".setup_blok b ON a.kodeorg = b.kodeorg AND b.intiplasma like '%".$param['tipekebun']."%' 
		LEFT JOIN ".$dbname.".bgt_distribusi c ON a.kunci = c.kunci
		WHERE tahunbudget = '".$year."' and ".$whereBud." AND LEFT(noakun,3) in (".$listAkun.")
		AND statusblok in (".$stsBlok.") and a.kodeorg != ''
		GROUP BY a.kodebudget, a.kodeorg, LEFT(noakun,5)";
	$resBud = fetchData($qBud);
	
	// Rearrange Data Budget
	$dataBud = $data2Bud = $dataPnnBud = $data2PnnBud = array();
	if(!empty($resBud[0]['tahunbudget'])) {
		foreach($resBud as $row) {
			$tmpSts = $optBlok[$row['kodeorg']]['statusblok'];
			$tmpTT = ($tmpSts=='TM')? '': $optBlok[$row['kodeorg']]['tahuntanam'];
			
			// All Year Data
			if(!isset($dataBud[$tmpSts][$tmpTT][$row['noakun']]['all'])) {
				$dataBud[$tmpSts][$tmpTT][$row['noakun']]['all'] = $row['rptotal'];
				$data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all'] = array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
				$data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all']['total'] = $row['rptotal'];
				
				if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all']['upah'] = $row['rptotal'];
				elseif(substr($row['kodebudget'],0,3)=='VHC') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all']['transport'] = $row['rptotal'];
				elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all']['material'] = $row['rptotal'];
				else $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all']['lain'] = $row['rptotal'];
			} else {
				$dataBud[$tmpSts][$tmpTT][$row['noakun']]['all'] += $row['rptotal'];
				$data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all'] += array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
				$data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all']['total'] += $row['rptotal'];
				if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all']['upah'] += $row['rptotal'];
				elseif(substr($row['kodebudget'],0,3)=='VHC') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all']['transport'] += $row['rptotal'];
				elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all']['material'] += $row['rptotal'];
				else $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['all']['lain'] += $row['rptotal'];
			}
			if(substr($row['noakun'],0,3)=='611') {
				if(!isset($dataPnnBud[$row['noakun']]['all'])) {
					$dataPnnBud[$row['noakun']]['all'] = $row['rptotal'];
					
					$data2PnnBud[$row['noakun']]['all'] = array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
					$data2PnnBud[$row['noakun']]['all']['total'] = $row['rptotal'];
					
					if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2PnnBud[$row['noakun']]['all']['upah'] = $row['rptotal'];
					elseif(substr($row['kodebudget'],0,3)=='VHC') $data2PnnBud[$row['noakun']]['all']['transport'] = $row['rptotal'];
					elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2PnnBud[$row['noakun']]['all']['material'] = $row['rptotal'];
					else $data2PnnBud[$row['noakun']]['all']['lain'] = $row['rptotal'];
				} else {
					$dataPnnBud[$row['noakun']]['all'] += $row['rptotal'];
					
					$data2PnnBud[$row['noakun']]['all']['total'] += $row['rptotal'];
					if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2PnnBud[$row['noakun']]['all']['upah'] += $row['rptotal'];
					elseif(substr($row['kodebudget'],0,3)=='VHC') $data2PnnBud[$row['noakun']]['all']['transport'] += $row['rptotal'];
					elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2PnnBud[$row['noakun']]['all']['material'] += $row['rptotal'];
					else $data2PnnBud[$row['noakun']]['all']['lain'] += $row['rptotal'];
				}
			}
			
			// To Date Data
			$tmpJumlah = 0;
			for($i=1; $i<=$month; $i++) {
				$tmpJumlah += $row['rp'.str_pad($i,2,'0',STR_PAD_LEFT)];
			}
			if(!isset($dataBud[$tmpSts][$tmpTT][$row['noakun']]['todate'])) {
				$dataBud[$tmpSts][$tmpTT][$row['noakun']]['todate'] = $tmpJumlah;
				
				$data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate'] = array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
				$data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate']['total'] = $tmpJumlah;
				if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate']['upah'] = $tmpJumlah;
				elseif(substr($row['kodebudget'],0,3)=='VHC') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate']['transport'] = $tmpJumlah;
				elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate']['material'] = $tmpJumlah;
				else $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate']['lain'] = $tmpJumlah;
			} else {
				$dataBud[$tmpSts][$tmpTT][$row['noakun']]['todate'] += $tmpJumlah;
				
				$data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate'] += array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
				$data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate']['total'] += $tmpJumlah;
				if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate']['upah'] += $tmpJumlah;
				elseif(substr($row['kodebudget'],0,3)=='VHC') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate']['transport'] += $tmpJumlah;
				elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate']['material'] += $tmpJumlah;
				else $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['todate']['lain'] += $tmpJumlah;
			}
			if(substr($row['noakun'],0,3)=='611') {
				$tmpJumlah = 0;
				for($i=1; $i<=$month; $i++) {
					$tmpJumlah += $row['rp'.str_pad($i,2,'0',STR_PAD_LEFT)];
				}
				if(!isset($dataPnnBud[$row['noakun']]['todate'])) {
					$dataPnnBud[$row['noakun']]['todate'] = $tmpJumlah;
					
					$data2PnnBud[$row['noakun']]['todate'] = array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
					$data2PnnBud[$row['noakun']]['todate']['total'] = $tmpJumlah;
					if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2PnnBud[$row['noakun']]['todate']['upah'] = $tmpJumlah;
					elseif(substr($row['kodebudget'],0,3)=='VHC') $data2PnnBud[$row['noakun']]['todate']['transport'] = $tmpJumlah;
					elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2PnnBud[$row['noakun']]['todate']['material'] = $tmpJumlah;
					else $data2PnnBud[$row['noakun']]['todate']['lain'] = $tmpJumlah;
				} else {
					$dataPnnBud[$row['noakun']]['todate'] += $tmpJumlah;
					
					$data2PnnBud[$row['noakun']]['todate'] += array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
					$data2PnnBud[$row['noakun']]['todate']['total'] += $tmpJumlah;
					if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2PnnBud[$row['noakun']]['todate']['upah'] += $tmpJumlah;
					elseif(substr($row['kodebudget'],0,3)=='VHC') $data2PnnBud[$row['noakun']]['todate']['transport'] += $tmpJumlah;
					elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2PnnBud[$row['noakun']]['todate']['material'] += $tmpJumlah;
					else $data2PnnBud[$row['noakun']]['todate']['lain'] += $tmpJumlah;
				}
			}
			
			// This Month Data
			if(!isset($dataBud[$tmpSts][$tmpTT][$row['noakun']]['month'])) {
				$dataBud[$tmpSts][$tmpTT][$row['noakun']]['month'] =
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				
				$data2Bud[$tmpSts][$tmpTT][$row['noakun']]['month'] = array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
				$data2Bud[$tmpSts][$tmpTT][$row['noakun']]['month']['total'] = 
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['month']['upah'] = 
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				elseif(substr($row['kodebudget'],0,3)=='VHC') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['month']['transport'] = 
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['month']['material'] = 
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				else $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['month']['lain'] = 
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
			} else {
				$dataBud[$tmpSts][$tmpTT][$row['noakun']]['month'] +=
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				
				$data2Bud[$tmpSts][$tmpTT][$row['noakun']]['month']['total'] += 
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['month']['upah'] += 
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				elseif(substr($row['kodebudget'],0,3)=='VHC') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['month']['transport'] += 
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['month']['material'] += 
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				else $data2Bud[$tmpSts][$tmpTT][$row['noakun']]['month']['lain'] += 
					$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
			}
			if(substr($row['noakun'],0,3)=='611') {
				if(!isset($dataPnnBud[$row['noakun']]['month'])) {
					$dataPnnBud[$row['noakun']]['month'] =
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
					
					$data2PnnBud[$row['noakun']]['month'] = array('total'=>0,'upah'=>0,'transport'=>0,'material'=>0,'lain'=>0);
					$data2PnnBud[$row['noakun']]['month']['total'] = 
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
					
					if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2PnnBud[$row['noakun']]['month']['upah'] = 
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
					elseif(substr($row['kodebudget'],0,3)=='VHC') $data2PnnBud[$row['noakun']]['month']['transport'] = 
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
					elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2PnnBud[$row['noakun']]['month']['material'] = 
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
					else $data2PnnBud[$row['noakun']]['month']['lain'] = 
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				} else {
					$dataPnnBud[$row['noakun']]['month'] +=
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
					
					$data2PnnBud[$row['noakun']]['month']['total'] += 
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
					if(substr($row['kodebudget'],0,3)=='SDM' or substr($row['kodebudget'],0,9)=='SUPERVISI') $data2PnnBud[$row['noakun']]['month']['upah'] += 
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
					elseif(substr($row['kodebudget'],0,3)=='VHC') $data2PnnBud[$row['noakun']]['month']['transport'] += 
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
					elseif(substr($row['kodebudget'],0,2)=='M-' or substr($row['kodebudget'],0,4)=='TOOL') $data2PnnBud[$row['noakun']]['month']['material'] += 
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
					else $data2PnnBud[$row['noakun']]['month']['lain'] += 
						$row['rp'.str_pad($month,2,'0',STR_PAD_LEFT)];
				}
			}
		}
	}
}

/*******************************************************************************
 ** Data KG (SPB & Budget) *****************************************************
 *******************************************************************************/

if($level < 3) {
	if(is_array($param['kebun'])) {
		$whereSPB = " IN ('".implode("','",$param['kebun'])."')";
	} else {
		$whereSPB = " = '".$param['kebun']."'";
	}
	$qSPB = "SELECT LEFT(tanggal,7) as periode, sum(totalkg) as totalkg
		FROM ".$dbname.".`kebun_spb_vw`
		WHERE LEFT(blok,4) ".$whereSPB." AND YEAR(tanggal) = '".$year."'
		AND tanggal <= '".$tgl."'
		GROUP BY LEFT(tanggal,7)";
	$resSPB = fetchData($qSPB);
	$optKg = array();
	foreach($resSPB as $r) {
		setIt($optKg['month'],0);
		setIt($optKg['todate'],0);
		
		$optKg['todate'] += $r['totalkg'];
		if($r['periode'] == $year.'-'.$month) {
			$optKg['month'] += $r['totalkg'];
		}
	}
	
	// Budget Produksi
	$qProdBud = "SELECT SUM(kgsetahun) AS kgsetahun,
		SUM(kg01) AS kg01, SUM(kg02) AS kg02, SUM(kg03) AS kg03,
		SUM(kg04) AS kg04, SUM(kg05) AS kg05, SUM(kg06) AS kg06,
		SUM(kg07) AS kg07, SUM(kg08) AS kg08, SUM(kg09) AS kg09,
		SUM(kg10) AS kg10, SUM(kg11) AS kg11, SUM(kg12) AS kg12
		FROM ".$dbname.".`bgt_produksi_kbn_kg_vw` a
		WHERE a.kodeunit ".$whereSPB." AND a.tahunbudget = '".$year."'";
	$resProdBud = fetchData($qProdBud);
	$kgBudTahun = $resProdBud[0]['kgsetahun'];
	$kgBudBulanIni = $resProdBud[0]['kg'.$monthPad];
	$kgBudToDate = 0;
	for($i=1;$i<=12;$i++) {
		if($i<=$month) {
			$kgBudToDate += $resProdBud[0]['kg'.str_pad($i,2,'0',STR_PAD_LEFT)];
		}
	}
}

/*******************************************************************************
 ** LEVEL 4 Detail    **********************************************************
 *******************************************************************************/
 if($level==4){
	if($param['title']=='upah'){
		$qPres = "SELECT a.kodeorg,c.tanggal,LEFT(a.kodekegiatan,7) as akun,sum(a.hasilkerja) as hasilkerja, sum(jumlahhk) as hk, d.satuan
		FROM ".$dbname.".kebun_prestasi a
		INNER JOIN ".$dbname.".setup_blok b ON a.kodeorg = b.kodeorg AND b.intiplasma like '%".$param['tipekebun']."%' 
		LEFT JOIN ".$dbname.".kebun_aktifitas c ON a.notransaksi = c.notransaksi
		LEFT JOIN ".$dbname.".setup_kegiatan d ON a.kodekegiatan = d.kodekegiatan 
		WHERE YEAR(c.tanggal) = '".$year."'
		AND c.tanggal <= '".$tgl."' AND ".$whereKodeorg2." AND b.statusblok in (".$stsBlok.") ".$tt3.
		" AND a.kodekegiatan <> 0 
		AND LEFT(a.kodekegiatan,7) in (".$param['noakun'].") 
		GROUP BY c.tanggal,LEFT(a.kodekegiatan,7), a.kodeorg 
		ORDER BY a.kodeorg";
		// echo $qPres;
		// $qPres = "SELECT b.kodeorg,c.tanggal,LEFT(a.kodekegiatan,7) as akun, sum(a.hasilkerja) as hasilkerja, sum(jumlahhk) as hk, d.satuan
			// FROM ".$dbname.".kebun_prestasi a
			// INNER JOIN ".$dbname.".setup_blok b ON a.kodeorg = b.kodeorg
			// LEFT JOIN ".$dbname.".kebun_aktifitas c ON a.notransaksi = c.notransaksi 
			// LEFT JOIN ".$dbname.".setup_kegiatan d ON a.kodekegiatan = d.kodekegiatan
			// WHERE YEAR(c.tanggal) = '".$year."'
			// AND c.tanggal <= '".$tgl."' 
			// AND ".$whereKodeorg2." 
			// AND a.kodekegiatan like '%".$param['noakun']."%' 
			// AND b.statusblok in (".$stsBlok.") ".$tt3." 
			// GROUP BY b.kodeorg
			// ORDER BY b.kodeorg ASC";
		$resPres = fetchData($qPres);
		$dataPres = array();
		foreach($resPres as $row) {
			if(!isset($dataPres[$row['kodeorg']])) {
				$dataPres[$row['kodeorg']] = array(
					'todate' => array('hasil'=>0, 'hk' => 0),
					'today' => array('hasil'=>0, 'hk' => 0)
				);
			}
			
			$dataPres[$row['kodeorg']]['todate']['kodeorg'] = $row['kodeorg'];
			$dataPres[$row['kodeorg']]['todate']['akun'] = $row['akun'];
			$dataPres[$row['kodeorg']]['todate']['satuan'] = $row['satuan'];
			$dataPres[$row['kodeorg']]['todate']['hasil'] += $row['hasilkerja'];
			$dataPres[$row['kodeorg']]['todate']['hk'] += $row['hk'];
			
			if(substr(str_replace('-','',$row['tanggal']),0,6) == substr($tgl,0,6)) {
				$dataPres[$row['kodeorg']]['today']['hasil'] += $row['hasilkerja'];
				$dataPres[$row['kodeorg']]['today']['hk'] += $row['hk'];
			}
		}
	}else if($param['title']=='material'){
		$qMat2 = "SELECT b.kodeorg, x.tanggal, LEFT(x.kodekegiatan,7) as akun,a.kodebarang,
			e.namabarang, e.satuan, (a.jumlah) as vol, (a.jumlah * hargarata) as rupiah 
			FROM ".$dbname.".keu_jurnaldt_vw x 
			LEFT JOIN ".$dbname.".log_transaksidt a ON x.noreferensi = a.notransaksi AND x.kodebarang = a.kodebarang 
			INNER JOIN ".$dbname.".setup_blok b ON a.kodeblok = b.kodeorg AND b.intiplasma like '%".$param['tipekebun']."%' 
			LEFT JOIN ".$dbname.".log_5masterbarang e ON a.kodebarang = e.kodebarang
			WHERE YEAR(x.tanggal) = '".$year."' 
			AND x.tanggal <= '".$tgl."' AND x.nojurnal like '%INVK%' AND (x.kodebarang != '' OR x.kodebarang != null) AND ".$whereKodeorg5." AND b.statusblok in (".$stsBlok.") ".$tt3."";
		$resMat2 = fetchData($qMat2);
		// echo $qMat2;
		
		$qMat = "SELECT b.kodeorg, x.tanggal,LEFT(a.kodekegiatan,7) as akun,a.kodebarang,
			e.namabarang, e.satuan, (a.kwantitas) as vol, (kwantitas * hargasatuan) as rupiah 
			FROM ".$dbname.".keu_jurnaldt_vw x 
			LEFT JOIN ".$dbname.".keu_jurnaldt_vw y on x.nojurnal = y.nojurnal AND y.kodebarang != '' 
			LEFT JOIN ".$dbname.".kebun_pakai_material_vw a on x.noreferensi = a.notransaksi AND y.kodebarang = a.kodebarang
			INNER JOIN ".$dbname.".setup_blok b ON a.kodeorg = b.kodeorg AND b.intiplasma like '%".$param['tipekebun']."%' 
			LEFT JOIN ".$dbname.".log_5masterbarang e ON a.kodebarang = e.kodebarang
			WHERE YEAR(a.tanggal) = '".$year."'
			AND a.tanggal <= '".$tgl."' AND x.nojurnal like '%INVK%' AND (x.kodebarang = '' OR x.kodebarang = null) AND ".$whereKodeorg4." AND b.statusblok in (".$stsBlok.") ".$tt3."";
			// echo "<br>".$qMat;
		$resMat = fetchData($qMat);
		$dataMat = array();
		
		foreach($resMat as $row) {
			if($row['kodebarang']==$param['kodebarang'] && $row['akun']==$param['noakun']){
				if(!isset($dataMat[$row['kodeorg']])) {
					$dataMat[$row['kodeorg']] = array(
						'todate' => array('vol'=>0,'rupiah'=>0),
						'today' => array('vol'=>0,'rupiah'=>0),
					);
				}
			
				$dataMat[$row['kodeorg']]['todate']['blok'] = $row['kodeorg'];
				$dataMat[$row['kodeorg']]['todate']['namabarang'] = $row['namabarang'];
				$dataMat[$row['kodeorg']]['todate']['satuan'] = $row['satuan'];
				$dataMat[$row['kodeorg']]['todate']['vol'] += $row['vol'];
				$dataMat[$row['kodeorg']]['todate']['rupiah'] += $row['rupiah'];
				
				if(substr(str_replace('-','',$row['tanggal']),0,6) == substr($tgl,0,6)) {
					$dataMat[$row['kodeorg']]['today']['vol'] += $row['vol'];
					$dataMat[$row['kodeorg']]['today']['rupiah'] += $row['rupiah'];
				}
			}
		}
		foreach($resMat2 as $row) {
			if($row['kodebarang']==$param['kodebarang'] && $row['akun']==$param['noakun']){
				if(!isset($dataMat[$row['kodeorg']])) {
					$dataMat[$row['kodeorg']] = array(
						'todate' => array('vol'=>0,'rupiah'=>0),
						'today' => array('vol'=>0,'rupiah'=>0),
					);
				}
			
				$dataMat[$row['kodeorg']]['todate']['blok'] = $row['kodeorg'];
				$dataMat[$row['kodeorg']]['todate']['namabarang'] = $row['namabarang'];
				$dataMat[$row['kodeorg']]['todate']['satuan'] = $row['satuan'];
				$dataMat[$row['kodeorg']]['todate']['vol'] += $row['vol'];
				$dataMat[$row['kodeorg']]['todate']['rupiah'] += $row['rupiah'];
				
				if(substr(str_replace('-','',$row['tanggal']),0,6) == substr($tgl,0,6)) {
					$dataMat[$row['kodeorg']]['today']['vol'] += $row['vol'];
					$dataMat[$row['kodeorg']]['today']['rupiah'] += $row['rupiah'];
				}
			}
		}
	}else if($param['title']=='vhc'){
		// $qVhc = "SELECT a.tanggal,LEFT(c.noakun,7) as akun, sum(a.kmkeluar - a.kmmasuk) as vol, a.kodevhc
		// FROM ".$dbname.".vhc_run_vw a
		// INNER JOIN ".$dbname.".setup_blok b ON a.alokasibiaya = b.kodeorg
		// LEFT JOIN ".$dbname.".vhc_kegiatan c ON a.jenispekerjaan = c.kodekegiatan
		// WHERE YEAR(a.tanggal) = '".$year."' 
		// AND a.tanggal <= '".$tgl."' AND (".$whereKodeorg3.") AND b.statusblok in (".$stsBlok.") ".$tt3.
		// " AND length(a.alokasibiaya) > 6
		// GROUP BY LEFT(c.noakun,7)"; 
		$qVhc = "SELECT a.alokasibiaya,a.tanggal,LEFT(c.noakun,7) as akun, sum(a.kmkeluar - a.kmmasuk) as vol, a.kodevhc
				FROM ".$dbname.".vhc_run_vw a
				INNER JOIN ".$dbname.".setup_blok b ON a.alokasibiaya = b.kodeorg AND b.intiplasma like '%".$param['tipekebun']."%' 
				LEFT JOIN ".$dbname.".vhc_kegiatan c ON a.jenispekerjaan = c.kodekegiatan
				WHERE YEAR(a.tanggal) = '".$year."' 
				AND a.tanggal <= '".$tgl."' AND (".$whereKodeorg3.") AND b.statusblok in (".$stsBlok.") ".$tt3.
				" AND length(a.alokasibiaya) > 6 AND LEFT(c.noakun,7) = '".$param['noakun']."'
				GROUP BY a.kodevhc, a.alokasibiaya, a.tanggal, LEFT(c.noakun,7) ";
			// echo $qVhc;
		$resVhc = fetchData($qVhc);
		$dataVhc = array();
		foreach($resVhc as $row) {
			if(!isset($dataVhc[$row['alokasibiaya']."".$row['kodevhc']])) {
				$dataVhc[$row['alokasibiaya']."".$row['kodevhc']] = array('todate'=>0,'today'=>0);
			}
			$dataVhc[$row['alokasibiaya']."".$row['kodevhc']]['todate'] += $row['vol'];
			
			$tglAwal = substr($tgl,0,6)."01";
			if(str_replace('-','',$row['tanggal']) >= $tglAwal and str_replace('-','',$row['tanggal']) <= $tgl) {
				$dataVhc[$row['alokasibiaya']."".$row['kodevhc']]['today'] += $row['vol'];
			}
		}
	}
 }
 

// Header Table
if($param['tipe']=='BIBIT') {
	$strPembagi = "Pokok";
} else {
	$strPembagi = "Ha";
}

// Init
$tab = "";
$missedRow = array();
$total = array();
switch($level) {
	case 1:
		/***********************************************************************
		 ** [LEVEL 1] Table Content ********************************************
		 ***********************************************************************/
		if($mode=='excel') {
			$tab .= "<table class=sortable cellpadding=1 border=1>";
		} else {
			$tab .= "<h3><a id='navLevel1'>Level 1</a>";
			$tab .= "<a id='navLevel2' style='cursor:pointer;color:blue;font-weight:normal' onclick='level2()'> > Level 2</a>";
			$tab .= "<a id='navLevel3' style='display:none'> > Level 3</a>";
			$tab .= "</h3>";
			$tab .= "<div>
					<table>
						<tr>
							<td>".$_SESSION['lang']['pt']."</td>
							<td>:</td>
							<td>".$_POST['pt']."</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['kebun']."</td>
							<td>:</td>
							<td>".(empty($_POST['kebun'])? $_SESSION['lang']['all']: $_POST['kebun'])."</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['tanggal']."</td>
							<td>:</td>
							<td>".$_POST['tanggal']."</td>
						</tr>
						<tr>
							<td>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['laporan']."</td>
							<td>:</td>
							<td>".$_POST['tipe']."</td>
						</tr>
						<tr id='showLuas' style='display:none;'>
							<td>".$_SESSION['lang']['luas']."</td>
							<td>:</td>
							<td><label id=labelluas></label></td>
						</tr>
					</table></div>";
			$tab .= "<input id='ptRep' type=hidden value='".$_POST['pt']."'>";
			$tab .= "<input id='kebunRep' type=hidden value='".$_POST['kebun']."'>";
			$tab .= "<input id='tanggalRep' type=hidden value='".$_POST['tanggal']."'>";
			$tab .= "<input id='tipeRep' type=hidden value='".$_POST['tipe']."'>";
			$tab .= "<div id='report-level-1'>";
			$tab .= "<button class=mybutton onclick=\"formPrint('excel',1,'##ptRep##kebunRep##tanggalRep##tipeRep','','kebun_slave_2accreport',event)\">";
			$tab .= "Excel</button>";
			$tab .= "<table class=sortable cellspacing=1 cellpadding=1>";
		}
		
		// Header
		$tab .= "<thead style='text-align:center'>";
		
		// Header - Row 1
		$tab .= "<tr class=rowheader><td rowspan=3 colspan=2>DESCRIPTION</td>";
		$tab .= "<td colspan=6>THIS MONTH</td>";
		$tab .= "<td colspan=6>TO DATE</td>";
		$tab .= "<td colspan=2>Annual Budget</td></tr>";
		
		// Header - Row 2
		$tab .= "<tr class=rowheader><td rowspan=2>ACT</td>";
		$tab .= "<td rowspan=2>Rp/".$strPembagi."</td>";
		$tab .= "<td rowspan=2>BUD</td>";
		$tab .= "<td rowspan=2>Rp/".$strPembagi."</td>";
		$tab .= "<td colspan=2>Var</td>";
		$tab .= "<td rowspan=2>ACT</td>";
		$tab .= "<td rowspan=2>Rp/".$strPembagi."</td>";
		$tab .= "<td rowspan=2>BUD</td>";
		$tab .= "<td rowspan=2>Rp/".$strPembagi."</td>";
		$tab .= "<td colspan=2>Var</td>";
		$tab .= "<td rowspan=2>BUD</td>";
		$tab .= "<td rowspan=2>Rp/".$strPembagi."</td></tr>";
		
		// Header - Row 3
		$tab .= "<tr class=rowheader><td>Value</td>";
		$tab .= "<td>%</td>";
		$tab .= "<td>Value</td>";
		$tab .= "<td>%</td></tr>";
		
		$tab .= "</thead>";
		
		// Init Grand Total
		$gTotal = array(0,'',0,'',0,0,0,'',0,'',0,0,0,'');
		
		if($param['tipe']!='BIBIT') {
			if(!empty($dataPnn)) {
				$totalPnn = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0);
				//$pembagiBud = $kgBud;
				
				$tab .= "<tr class=rowcontent style='background:orange;font-weight:bold'><td colspan=2>Panen</td>";
				$tab .= "<td colspan=2>KG: ".number_format($optKg['month'],0)."</td>";
				$tab .= "<td colspan=4>KG: ".number_format($kgBudBulanIni,0)."</td>";
				$tab .= "<td colspan=2>KG: ".number_format($optKg['todate'],0)."</td>";
				$tab .= "<td colspan=4>KG: ".number_format($kgBudToDate,0)."</td>";
				$tab .= "<td colspan=2>KG: ".number_format($kgBudTahun,0)."</td></tr>";
				
				// Content Panen
				foreach($optAkun as $akun=>$namaakun) {
					if(substr($akun,0,3)=='611') {
						// Set Default if not set
						setIt($dataPnn[$akun]['month'],0);
						setIt($dataPnnBud[$akun]['month'],0);
						setIt($dataPnn[$akun]['todate'],0);
						setIt($dataPnnBud[$akun]['todate'],0);
						setIt($dataPnnBud[$akun]['all'],0);
						
						// Calculation This Month
						$varBud = $dataPnnBud[$akun]['month'] - $dataPnn[$akun]['month'];
						$varBudPer = (empty($dataPnnBud[$akun]['month']))? 0:$varBud / $dataPnnBud[$akun]['month'];
						
						// Calculation To Date
						$varBudToDate = $dataPnnBud[$akun]['todate'] - $dataPnn[$akun]['todate'];
						$varBudToDatePer = (empty($dataPnnBud[$akun]['todate']))? 0:$varBud / $dataPnnBud[$akun]['todate'];
						
						// Insert to Content
						$tab .= "<tr class=rowcontent>";
						$tab .= "<td>".$akun."</td>";
						$tab .= "<td>".$optAkun[$akun]."</td>";
						
						$tab .= "<td align=right>".number_format($dataPnn[$akun]['month'],2)."</td>";
						$tab .= "<td align=right>".number_format(($optKg['month'])? $dataPnn[$akun]['month'] / $optKg['month']: 0,2)."</td>";
						$tab .= "<td align=right>".number_format($dataPnnBud[$akun]['month'],2)."</td>";
						$tab .= "<td align=right>".number_format(($kgBudBulanIni>0)? $dataPnnBud[$akun]['month'] / $kgBudBulanIni: 0,2)."</td>";
						$tab .= "<td align=right>".number_format($varBud,2)."</td>";
						$tab .= "<td align=right>".number_format($varBudPer,2)."</td>";
						
						$tab .= "<td align=right>".number_format($dataPnn[$akun]['todate'],2)."</td>";
						$tab .= "<td align=right>".number_format(($optKg['todate'])? $dataPnn[$akun]['todate'] / $optKg['todate']: 0,2)."</td>";
						$tab .= "<td align=right>".number_format($dataPnnBud[$akun]['todate'],2)."</td>";
						$tab .= "<td align=right>".number_format(($kgBudToDate>0)? $dataPnnBud[$akun]['todate'] / $kgBudToDate: 0,2)."</td>";
						$tab .= "<td align=right>".number_format($varBudToDate,2)."</td>";
						$tab .= "<td align=right>".number_format($varBudToDatePer,2)."</td>";
						
						$tab .= "<td align=right>".number_format($dataPnnBud[$akun]['all'],2)."</td>";
						$tab .= "<td align=right>".number_format(($kgBudTahun>0)? $dataPnnBud[$akun]['all'] / $kgBudTahun: 0,2)."</td>";
						
						$tab .= "</tr>";
						
						// Hitung Total Panen
						$totalPnn[0] += $dataPnn[$akun]['month'];
						$totalPnn[2] += $dataPnnBud[$akun]['month'];
						$totalPnn[4] += $varBud;
						$totalPnn[6] += $dataPnn[$akun]['todate'];
						$totalPnn[8] += $dataPnnBud[$akun]['todate'];
						$totalPnn[10] += $varBudToDate;
						$totalPnn[12] += $dataPnnBud[$akun]['all'];
						
						$gTotal[0] += $dataPnn[$akun]['month'];
						$gTotal[2] += $dataPnnBud[$akun]['month'];
						$gTotal[4] += $varBud;
						$gTotal[6] += $dataPnn[$akun]['todate'];
						$gTotal[8] += $dataPnnBud[$akun]['todate'];
						$gTotal[10] += $varBudToDate;
						$gTotal[12] += $dataPnnBud[$akun]['all'];
					}
				}
				
				// Average Total
				$totalPnn[1] = ($optKg['month'])? $totalPnn[0] / $optKg['month']: 0;
				$totalPnn[3] = ($kgBudBulanIni)? $totalPnn[2] / $kgBudBulanIni: 0;
				$totalPnn[5] = (empty($totalPnn[2]))? 0: $totalPnn[4] / $totalPnn[2];
				$totalPnn[7] = ($optKg['todate'])? $totalPnn[6] / $optKg['todate']: 0;
				$totalPnn[9] = ($kgBudToDate)? $totalPnn[8] / $kgBudToDate: 0;
				$totalPnn[11] = (empty($totalPnn[8]))? 0: $totalPnn[10] / $totalPnn[8];
				$totalPnn[13] = ($kgBudTahun>0)? $totalPnn[12] / $kgBudTahun: 0;
				
				// Show Total
				$tab .= "<tr class=rowcontent style='font-weight:bold;background:yellow'>";
				$tab .= "<td colspan=2>Sub Total Panen</td>";
				foreach($totalPnn as $t) {
					$tab .= "<td align=right>".number_format($t,2)."</td>";
				}
				$tab .= "</tr>";
			}
		}
		
		// Content Perawatan
		foreach($data as $sts=>$row) {
			foreach($row as $tt=>$row1) {
				if(!$allPanen[$sts.$tt]) {
					// Init Sub Total
					$sTotal = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0);
					
					$labelTT = (empty($tt))? "": " [".$tt."]";
					$tab .= "<tr class=rowcontent style='background:orange;font-weight:bold'><td colspan=2>".$sts.$labelTT."</td>";
					if($param['tipe']=='BIBIT') {
						$tab .= "<td colspan=14>Pokok: ".number_format($pokokBlok[$sts.$tt],0)."</td></tr>";
						$pembagi = $pokokBlok[$sts.$tt];
					} else {
						setIt($luasBlokBud[$sts.$tt],0);
						$tab .= "<td colspan=2>HA: ".number_format($luasBlok[$sts.$tt],0)."</td>";
						$tab .= "<td colspan=4>HA: ".number_format($luasBlokBud[$sts.$tt],0)."</td>";
						$tab .= "<td colspan=2>HA: ".number_format($luasBlok[$sts.$tt],0)."</td>";
						$tab .= "<td colspan=4>HA: ".number_format($luasBlokBud[$sts.$tt],0)."</td>";
						$tab .= "<td colspan=2>HA: ".number_format($luasBlokBud[$sts.$tt],0)."</td></tr>";
						$pembagi = $luasBlok[$sts.$tt];
					}
					foreach($optAkun as $akun=>$namaakun) {
						if(substr($akun,0,3) != '611') {
							if($sts=='TM' and substr($akun,0,1)=='1') {
								continue;
							} elseif($sts=='TBM' and substr($akun,0,3)!='126') {
								continue;
							}
							
							// Set Default if not set
							setIt($data[$sts][$tt][$akun]['month'],0);
							setIt($dataBud[$sts][$tt][$akun]['month'],0);
							setIt($data[$sts][$tt][$akun]['todate'],0);
							setIt($dataBud[$sts][$tt][$akun]['todate'],0);
							setIt($dataBud[$sts][$tt][$akun]['all'],0);
							
							// Calculation This Month
							$varBud = $dataBud[$sts][$tt][$akun]['month'] - $data[$sts][$tt][$akun]['month'];
							$varBudPer = (empty($dataBud[$sts][$tt][$akun]['month']))? 0:$varBud / $dataBud[$sts][$tt][$akun]['month'];
							
							// Calculation To Date
							$varBudToDate = $dataBud[$sts][$tt][$akun]['todate'] - $data[$sts][$tt][$akun]['todate'];
							$varBudToDatePer = (empty($dataBud[$sts][$tt][$akun]['todate']))? 0:$varBud / $dataBud[$sts][$tt][$akun]['todate'];
							
							// Insert to Content
							$tab .= "<tr class=rowcontent>";
							$tab .= "<td>".$akun."</td>";
							$tab .= "<td>".$optAkun[$akun]."</td>";
							
							$tab .= "<td align=right>".number_format($data[$sts][$tt][$akun]['month'],2)."</td>";
							$tab .= "<td align=right>".number_format(($pembagi>0?$data[$sts][$tt][$akun]['month'] / $pembagi:0),2)."</td>";
							$tab .= "<td align=right>".number_format($dataBud[$sts][$tt][$akun]['month'],2)."</td>";
							$tab .= "<td align=right>".number_format(($pembagi>0?$dataBud[$sts][$tt][$akun]['month'] / $pembagi:0),2)."</td>";
							$tab .= "<td align=right>".number_format($varBud,2)."</td>";
							$tab .= "<td align=right>".number_format($varBudPer,2)."</td>";
							
							$tab .= "<td align=right>".number_format($data[$sts][$tt][$akun]['todate'],2)."</td>";
							$tab .= "<td align=right>".number_format(($pembagi>0?$data[$sts][$tt][$akun]['todate'] / $pembagi:0),2)."</td>";
							$tab .= "<td align=right>".number_format($dataBud[$sts][$tt][$akun]['todate'],2)."</td>";
							$tab .= "<td align=right>".number_format(($pembagi>0?$dataBud[$sts][$tt][$akun]['todate'] / $pembagi:0),2)."</td>";
							$tab .= "<td align=right>".number_format($varBudToDate,2)."</td>";
							$tab .= "<td align=right>".number_format($varBudToDatePer,2)."</td>";
							
							$tab .= "<td align=right>".number_format($dataBud[$sts][$tt][$akun]['all'],2)."</td>";
							$tab .= "<td align=right>".number_format(($pembagi>0?$dataBud[$sts][$tt][$akun]['all'] / $pembagi:0),2)."</td>";
							
							$tab .= "</tr>";
							
							// Hitung Total Panen
							$sTotal[0] += $data[$sts][$tt][$akun]['month'];
							$sTotal[2] += $dataBud[$sts][$tt][$akun]['month'];
							$sTotal[4] += $varBud;
							$sTotal[6] += $data[$sts][$tt][$akun]['todate'];
							$sTotal[8] += $dataBud[$sts][$tt][$akun]['todate'];
							$sTotal[10] += $varBudToDate;
							$sTotal[12] += $dataBud[$sts][$tt][$akun]['all'];
							
							$gTotal[0] += $data[$sts][$tt][$akun]['month'];
							$gTotal[2] += $dataBud[$sts][$tt][$akun]['month'];
							$gTotal[4] += $varBud;
							$gTotal[6] += $data[$sts][$tt][$akun]['todate'];
							$gTotal[8] += $dataBud[$sts][$tt][$akun]['todate'];
							$gTotal[10] += $varBudToDate;
							$gTotal[12] += $dataBud[$sts][$tt][$akun]['all'];
						}
					}
					
					// Average Total
					$sTotal[1] = ($pembagi>0)? $sTotal[0] / $pembagi: 0;
					$sTotal[3] = ($pembagi>0)? $sTotal[2] / $pembagi: 0;
					$sTotal[5] = ($sTotal[2]>0)? $sTotal[4] / $sTotal[2]: 0;
					$sTotal[7] = ($pembagi>0)? $sTotal[6] / $pembagi: 0;
					$sTotal[9] = ($pembagi>0)? $sTotal[8] / $pembagi: 0;
					$sTotal[11] = ($sTotal[8]>0)? $sTotal[10] / $sTotal[8]: 0;
					$sTotal[13] = ($pembagi>0)? $sTotal[12] / $pembagi: 0;
					
					// Show Total
					$tab .= "<tr class=rowcontent style='font-weight:bold;background:yellow'>";
					$tab .= "<td colspan=2>Sub Total ".$sts.(!empty($tt)? " [".$tt."]":"")."</td>";
					foreach($sTotal as $t) {
						$tab .= "<td align=right>".number_format($t,2)."</td>";
					}
					$tab .= "</tr>";
				}
			}
		}
		
		$gTotal[5] = ($gTotal[2]>0)? $gTotal[4] / $gTotal[2]: 0;
		$gTotal[11] = ($gTotal[8]>0)? $gTotal[10] / $gTotal[8]: 0;
		
		// Show Total
		$tab .= "<tr class=rowcontent style='font-weight:bold;background:blue;color:white'>";
		$tab .= "<td colspan=2>Grand Total</td>";
		foreach($gTotal as $t) {
			$tab .= "<td align=right>".((is_float($t) or is_int($t))? number_format($t,2): $t)."</td>";
		}
		$tab .= "</tr>";
		
		$tab .= "</table></div>";
		$tab .= "<div id='report-level-2'></div>";
		$tab .= "<div id='report-level-3'></div>";
		break;
	case 2:
		/***********************************************************************
		 ** [LEVEL 2] Table Content ********************************************
		 ***********************************************************************/
		if($mode=='excel') {
			$tab .= "<table class=sortable cellpadding=3 border=1>";
		} else {
			$tab .= "<button class=mybutton onclick=\"formPrint('excel',2,'##ptRep##kebunRep##tanggalRep##tipeRep','','kebun_slave_2accreport',event)\">";
			$tab .= "Excel</button>";
			$tab .= "<table  class=sortable cellspacing=1 cellpadding=1>";
		}
		
		// Header
		$tab .= "<thead style='text-align:center'>";
		
		// Header - Row 1
		$tab .= "<tr class=rowheader><td rowspan=3 colspan=2>DESCRIPTION</td>";
		$tab .= "<td colspan=10>THIS MONTH</td>";
		$tab .= "<td colspan=10>TO DATE</td>";
		$tab .= "<td colspan=5 rowspan=2>Annual Budget</td></tr>";
		
		// Header - Row 2
		$tab .= "<tr class=rowheader><td colspan=5>ACTUAL</td>";
		$tab .= "<td colspan=5>BUDGET</td>";
		$tab .= "<td colspan=5>ACTUAL</td>";
		$tab .= "<td colspan=5>BUDGET</td></tr>";
		
		// Header - Row 3
		$tab .= "<tr class=rowheader>";
		for($i=0;$i<5;$i++) {
			$tab .= "<td>".$_SESSION['lang']['upah']."</td>";
			$tab .= "<td>".$_SESSION['lang']['material']."</td>";
			$tab .= "<td>".$_SESSION['lang']['transport']."</td>";
			$tab .= "<td>".$_SESSION['lang']['lain']."</td>";
			$tab .= "<td>TOTAL</td>";
		}
		$tab .= "</tr>";
		
		$tab .= "</thead>";
		
		// Init Grand Total
		$gTotal = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		
		// Content
		if($param['tipe']!='BIBIT') {
			if(!empty($data2Pnn)) {
				//$pembagiBud = $kgBud;
				
				$tab .= "<tr class=rowcontent style='background:orange;font-weight:bold'><td colspan=2>Panen</td>";
				$tab .= "<td colspan=5>KG: ".number_format($optKg['month'],0)."</td>";
				$tab .= "<td colspan=5>KG: ".number_format($kgBudBulanIni,0)."</td>";
				$tab .= "<td colspan=5>KG: ".number_format($optKg['todate'],0)."</td>";
				$tab .= "<td colspan=5>KG: ".number_format($kgBudToDate,0)."</td>";
				$tab .= "<td colspan=5>KG: ".number_format($kgBudTahun,0)."</td></tr>";
				
				$totalPnn = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
				
				// Content Panen
				foreach($optAkun as $akun=>$namaakun) {
					if(substr($akun,0,3)=='611') {
						// Set Default if not set
						setIt($data2PnnBud[$akun],array());
						setIt($data2Pnn[$akun]['month'],0);
						setIt($data2PnnBud[$akun]['month'],0);
						setIt($data2Pnn[$akun]['todate'],0);
						setIt($data2PnnBud[$akun]['todate'],0);
						setIt($data2PnnBud[$akun]['all'],0);
						
						// Insert to Content
						$tab .= "<tr class=rowcontent>";
						$tab .= "<td>".$akun."</td>";
						$tab .= "<td>".$optAkun[$akun]."</td>";
						
						$tab .= "<td align=right>".number_format($data2Pnn[$akun]['month']['upah'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2Pnn[$akun]['month']['material'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2Pnn[$akun]['month']['transport'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2Pnn[$akun]['month']['lain'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2Pnn[$akun]['month']['total'],2)."</td>";
						
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['month']['upah'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['month']['material'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['month']['transport'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['month']['lain'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['month']['total'],2)."</td>";
						
						$tab .= "<td align=right>".number_format($data2Pnn[$akun]['todate']['upah'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2Pnn[$akun]['todate']['material'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2Pnn[$akun]['todate']['transport'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2Pnn[$akun]['todate']['lain'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2Pnn[$akun]['todate']['total'],2)."</td>";
						
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['todate']['upah'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['todate']['material'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['todate']['transport'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['todate']['lain'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['todate']['total'],2)."</td>";
						
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['all']['upah'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['all']['material'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['all']['transport'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['all']['lain'],2)."</td>";
						$tab .= "<td align=right>".number_format($data2PnnBud[$akun]['all']['total'],2)."</td>";
						$tab .= "</tr>";
						
						$totalPnn[0] += $data2Pnn[$akun]['month']['upah'];
						$totalPnn[1] += $data2Pnn[$akun]['month']['material'];
						$totalPnn[2] += $data2Pnn[$akun]['month']['transport'];
						$totalPnn[3] += $data2Pnn[$akun]['month']['lain'];
						$totalPnn[4] += $data2Pnn[$akun]['month']['total'];
						$totalPnn[5] += $data2PnnBud[$akun]['month']['upah'];
						$totalPnn[6] += $data2PnnBud[$akun]['month']['material'];
						$totalPnn[7] += $data2PnnBud[$akun]['month']['transport'];
						$totalPnn[8] += $data2PnnBud[$akun]['month']['lain'];
						$totalPnn[9] += $data2PnnBud[$akun]['month']['total'];
						$totalPnn[10] += $data2Pnn[$akun]['todate']['upah'];
						$totalPnn[11] += $data2Pnn[$akun]['todate']['material'];
						$totalPnn[12] += $data2Pnn[$akun]['todate']['transport'];
						$totalPnn[13] += $data2Pnn[$akun]['todate']['lain'];
						$totalPnn[14] += $data2Pnn[$akun]['todate']['total'];
						$totalPnn[15] += $data2PnnBud[$akun]['todate']['upah'];
						$totalPnn[16] += $data2PnnBud[$akun]['todate']['material'];
						$totalPnn[17] += $data2PnnBud[$akun]['todate']['transport'];
						$totalPnn[18] += $data2PnnBud[$akun]['todate']['lain'];
						$totalPnn[19] += $data2PnnBud[$akun]['todate']['total'];
						$totalPnn[20] += $data2PnnBud[$akun]['all']['upah'];
						$totalPnn[21] += $data2PnnBud[$akun]['all']['material'];
						$totalPnn[22] += $data2PnnBud[$akun]['all']['transport'];
						$totalPnn[23] += $data2PnnBud[$akun]['all']['lain'];
						$totalPnn[24] += $data2PnnBud[$akun]['all']['total'];
					}
				}
				// Show Total
				$tab .= "<tr class=rowcontent style='font-weight:bold;background:yellow'>";
				$tab .= "<td colspan=2>Sub Total Panen</td>";
				foreach($totalPnn as $t) {
					$tab .= "<td align=right>".number_format($t,2)."</td>";
				}
				$tab .= "</tr>";
				$gTotal = $totalPnn;
			}
		}
			
		// Content Perawatan
		foreach($data2 as $sts=>$row) {
			foreach($row as $tt=>$row1) {
				if(!$allPanen[$sts.$tt]) {
					$labelTT = (empty($tt))? "": " [".$tt."]";
					$tab .= "<tr class=rowcontent style='background:orange;font-weight:bold'>";
					if($param['tipe']!='BIBIT') {
						$tab .= "<td colspan=2 onclick=\"level3('".$sts."','".$tt."','".number_format($luasBlok[$sts.$tt],0)."')\" style='cursor:pointer'";
						$tab .= ">".$sts.$labelTT." [click to Level 3]</td>";
					} else {
						$tab .= "<td colspan=2 style='cursor:pointer'>".$sts.$labelTT."</td>";
					}
					if($param['tipe']=='BIBIT') {
						$tab .= "<td colspan=25>Pokok: ".number_format($pokokBlok[$sts.$tt],0)."</td></tr>";
						$pembagi = $pokokBlok[$sts.$tt];
					} else {
						setIt($luasBlokBud[$sts.$tt],0);
						$tab .= "<td colspan=5>HA: ".number_format($luasBlok[$sts.$tt],0)."</td>";
						$tab .= "<td colspan=5>HA: ".number_format($luasBlokBud[$sts.$tt],0)."</td>";
						$tab .= "<td colspan=5>HA: ".number_format($luasBlok[$sts.$tt],0)."</td>";
						$tab .= "<td colspan=5>HA: ".number_format($luasBlokBud[$sts.$tt],0)."</td>";
						$tab .= "<td colspan=5>HA: ".number_format($luasBlokBud[$sts.$tt],0)."</td></tr>";
						$pembagi = $luasBlok[$sts.$tt];
					}
					
					// Sub Total
					$sTotal = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
					
					foreach($optAkun as $akun=>$namaakun) {
						if(substr($akun,0,3) != '611') {
							if($sts=='TM' and substr($akun,0,1)=='1') {
								continue;
							} elseif($sts=='TBM' and substr($akun,0,3)!='126') {
								continue;
							}
							
							// Set Default if not set
							setIt($data2[$sts][$tt][$akun]['month'],0);
							setIt($data2Bud[$sts][$tt][$akun]['month'],0);
							setIt($data2[$sts][$tt][$akun]['todate'],0);
							setIt($data2Bud[$sts][$tt][$akun]['todate'],0);
							setIt($data2Bud[$sts][$tt][$akun]['all'],0);
							
							// Insert to Content
							$tab .= "<tr class=rowcontent>";
							$tab .= "<td>".$akun."</td>";
							$tab .= "<td>".$optAkun[$akun]."</td>";
							
							$tab .= "<td align=right>".number_format($data2[$sts][$tt][$akun]['month']['upah'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2[$sts][$tt][$akun]['month']['material'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2[$sts][$tt][$akun]['month']['transport'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2[$sts][$tt][$akun]['month']['lain'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2[$sts][$tt][$akun]['month']['total'],2)."</td>";
							
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['month']['upah'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['month']['material'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['month']['transport'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['month']['lain'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['month']['total'],2)."</td>";
							
							$tab .= "<td align=right>".number_format($data2[$sts][$tt][$akun]['todate']['upah'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2[$sts][$tt][$akun]['todate']['material'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2[$sts][$tt][$akun]['todate']['transport'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2[$sts][$tt][$akun]['todate']['lain'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2[$sts][$tt][$akun]['todate']['total'],2)."</td>";
							
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['todate']['upah'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['todate']['material'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['todate']['transport'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['todate']['lain'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['todate']['total'],2)."</td>";
							
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['all']['upah'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['all']['material'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['all']['transport'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['all']['lain'],2)."</td>";
							$tab .= "<td align=right>".number_format($data2Bud[$sts][$tt][$akun]['all']['total'],2)."</td>";
							$tab .= "</tr>";
							
							$sTotal[0] += $data2[$sts][$tt][$akun]['month']['upah'];
							$sTotal[1] += $data2[$sts][$tt][$akun]['month']['material'];
							$sTotal[2] += $data2[$sts][$tt][$akun]['month']['transport'];
							$sTotal[3] += $data2[$sts][$tt][$akun]['month']['lain'];
							$sTotal[4] += $data2[$sts][$tt][$akun]['month']['total'];
							$sTotal[5] += $data2Bud[$sts][$tt][$akun]['month']['upah'];
							$sTotal[6] += $data2Bud[$sts][$tt][$akun]['month']['material'];
							$sTotal[7] += $data2Bud[$sts][$tt][$akun]['month']['transport'];
							$sTotal[8] += $data2Bud[$sts][$tt][$akun]['month']['lain'];
							$sTotal[9] += $data2Bud[$sts][$tt][$akun]['month']['total'];
							$sTotal[10] += $data2[$sts][$tt][$akun]['todate']['upah'];
							$sTotal[11] += $data2[$sts][$tt][$akun]['todate']['material'];
							$sTotal[12] += $data2[$sts][$tt][$akun]['todate']['transport'];
							$sTotal[13] += $data2[$sts][$tt][$akun]['todate']['lain'];
							$sTotal[14] += $data2[$sts][$tt][$akun]['todate']['total'];
							$sTotal[15] += $data2Bud[$sts][$tt][$akun]['todate']['upah'];
							$sTotal[16] += $data2Bud[$sts][$tt][$akun]['todate']['material'];
							$sTotal[17] += $data2Bud[$sts][$tt][$akun]['todate']['transport'];
							$sTotal[18] += $data2Bud[$sts][$tt][$akun]['todate']['lain'];
							$sTotal[19] += $data2Bud[$sts][$tt][$akun]['todate']['total'];
							$sTotal[20] += $data2Bud[$sts][$tt][$akun]['all']['upah'];
							$sTotal[21] += $data2Bud[$sts][$tt][$akun]['all']['material'];
							$sTotal[22] += $data2Bud[$sts][$tt][$akun]['all']['transport'];
							$sTotal[23] += $data2Bud[$sts][$tt][$akun]['all']['lain'];
							$sTotal[24] += $data2Bud[$sts][$tt][$akun]['all']['total'];
							
							$gTotal[0] += $data2[$sts][$tt][$akun]['month']['upah'];
							$gTotal[1] += $data2[$sts][$tt][$akun]['month']['material'];
							$gTotal[2] += $data2[$sts][$tt][$akun]['month']['transport'];
							$gTotal[3] += $data2[$sts][$tt][$akun]['month']['lain'];
							$gTotal[4] += $data2[$sts][$tt][$akun]['month']['total'];
							$gTotal[5] += $data2Bud[$sts][$tt][$akun]['month']['upah'];
							$gTotal[6] += $data2Bud[$sts][$tt][$akun]['month']['material'];
							$gTotal[7] += $data2Bud[$sts][$tt][$akun]['month']['transport'];
							$gTotal[8] += $data2Bud[$sts][$tt][$akun]['month']['lain'];
							$gTotal[9] += $data2Bud[$sts][$tt][$akun]['month']['total'];
							$gTotal[10] += $data2[$sts][$tt][$akun]['todate']['upah'];
							$gTotal[11] += $data2[$sts][$tt][$akun]['todate']['material'];
							$gTotal[12] += $data2[$sts][$tt][$akun]['todate']['transport'];
							$gTotal[13] += $data2[$sts][$tt][$akun]['todate']['lain'];
							$gTotal[14] += $data2[$sts][$tt][$akun]['todate']['total'];
							$gTotal[15] += $data2Bud[$sts][$tt][$akun]['todate']['upah'];
							$gTotal[16] += $data2Bud[$sts][$tt][$akun]['todate']['material'];
							$gTotal[17] += $data2Bud[$sts][$tt][$akun]['todate']['transport'];
							$gTotal[18] += $data2Bud[$sts][$tt][$akun]['todate']['lain'];
							$gTotal[19] += $data2Bud[$sts][$tt][$akun]['todate']['total'];
							$gTotal[20] += $data2Bud[$sts][$tt][$akun]['all']['upah'];
							$gTotal[21] += $data2Bud[$sts][$tt][$akun]['all']['material'];
							$gTotal[22] += $data2Bud[$sts][$tt][$akun]['all']['transport'];
							$gTotal[23] += $data2Bud[$sts][$tt][$akun]['all']['lain'];
							$gTotal[24] += $data2Bud[$sts][$tt][$akun]['all']['total'];
						}
					}
					
					// Show Total
					$tab .= "<tr class=rowcontent style='font-weight:bold;background:yellow'>";
					$tab .= "<td colspan=2>Sub Total ".$sts.(!empty($tt)? " [".$tt."]":"")."</td>";
					foreach($sTotal as $t) {
						$tab .= "<td align=right>".number_format($t,2)."</td>";
					}
					$tab .= "</tr>";
				}
			}
		}
		
		// Show Total
		$tab .= "<tr class=rowcontent style='font-weight:bold;background:blue;color:white'>";
		$tab .= "<td colspan=2>Grand Total</td>";
		foreach($gTotal as $t) {
			$tab .= "<td align=right>".((is_float($t) or is_int($t))? number_format($t,2): $t)."</td>";
		}
		$tab .= "</tr>";
		
		$tab .= "</table>";
		break;
	case 3:
		/***********************************************************************
		 ** [LEVEL 3] Table Content ********************************************
		 ***********************************************************************/
		$tab .= "<input type=hidden id=statustanam value='".$param['statustanam']."'>";
		$tab .= "<input type=hidden id=tahuntanam value='".$param['tahuntanam']."'>";
		$tab .= "<input type=hidden id=title>";
		$tab .= "<input type=hidden id=noakun>";
		$tab .= "<input type=hidden id=namakegiatan>";
		$tab .= "<input type=hidden id=kodebarang>";
		
		if($mode=='excel') {
			$tab .= "<table class=sortable cellpadding=3 border=1>";
		} else {
			$tab .= "<button class=mybutton onclick=\"formPrint('excel',3,'##ptRep##kebunRep##tanggalRep##tipeRep##statustanam##tahuntanam','','kebun_slave_2accreport',event)\">";
			$tab .= "Excel</button>";
			$tab .= "<table  class=sortable cellspacing=1 cellpadding=1>";
		}
		
		// Header
		$tab .= "<thead style='text-align:center'>";
		
		// Header - Row 1
		$tab .= "<tr class=rowheader>";
		$tab .= "<td rowspan=3>PARENT ACC.</td>";
		$tab .= "<td rowspan=3>DESCRIPTION ACTIVITY</td>";
		$tab .= "<td rowspan=3>".$_SESSION['lang']['noakun']."</td>";
		$tab .= "<td rowspan=3>DESCRIPTION SUB ACTIVITY</td>";
		$tab .= "<td rowspan=3>".$_SESSION['lang']['satuan']."</td>";
		$tab .= "<td colspan=6>".$_SESSION['lang']['upah']."</td>";
		$tab .= "<td colspan=6>".$_SESSION['lang']['material']."</td>";
		$tab .= "<td colspan=4>".$_SESSION['lang']['transport']."</td>";
		$tab .= "<td colspan=2>".$_SESSION['lang']['lain']."</td>";
		$tab .= "<td colspan=2>TOTAL ".$_SESSION['lang']['biaya']."</td></tr>";
		
		// Header - Row 2
		$tab .= "<tr class=rowheader>";
		$tab .= "<td colspan=3>TO DAY</td>";
		$tab .= "<td colspan=3>TO DATE</td>";
		$tab .= "<td rowspan=2>".$_SESSION['lang']['nama']."</td>";
		$tab .= "<td rowspan=2>UNIT</td>";
		$tab .= "<td colspan=2>TO DAY</td>";
		$tab .= "<td colspan=2>TO DATE</td>";
		//$tab .= "<td rowspan=2>TYPE TRANSPORT</td>";
		//$tab .= "<td rowspan=2>UNIT</td>";
		$tab .= "<td colspan=2>TO DAY</td>";
		$tab .= "<td colspan=2>TO DATE</td>";
		$tab .= "<td rowspan=2>TO DAY</td>";
		$tab .= "<td rowspan=2>TO DATE</td>";
		$tab .= "<td rowspan=2>TO DAY</td>";
		$tab .= "<td rowspan=2>TO DATE</td></tr>";
		
		// Header - Row 3
		$tab .= "<tr class=rowheader>";
		$tab .= "<td>HA DONE</td>";
		$tab .= "<td>HK</td>";
		$tab .= "<td>".$_SESSION['lang']['biaya']."</td>";
		$tab .= "<td>HA DONE</td>";
		$tab .= "<td>".$_SESSION['lang']['jhk']."</td>";
		$tab .= "<td>".$_SESSION['lang']['biaya']."</td>";
		$tab .= "<td>VOL</td>";
		$tab .= "<td>".$_SESSION['lang']['biaya']."</td>";
		$tab .= "<td>VOL</td>";
		$tab .= "<td>".$_SESSION['lang']['biaya']."</td>";
		$tab .= "<td>VOL</td>";
		$tab .= "<td>".$_SESSION['lang']['biaya']."</td>";
		$tab .= "<td>VOL</td>";
		$tab .= "<td>".$_SESSION['lang']['biaya']."</td>";
		
		$tab .= "</thead>";
		
		// Init Grand Total
		$gTotal = array(0,0,0,0,0,0,0,0,0,0);
		
		// Content
		$tab .= "<tbody>";
		foreach($optAkun as $akun=>$namaakun) {
			$first7 = 0;
			$tab .= "<tr class='rowcontent'>";
			$tab .= "<td>".$akun."</td>";
			$tab .= "<td>".$namaakun."</td>";
			
			$sTotal = array(0,0,0,0,0,0,0,0,0,0);
			foreach($optAkun7 as $akun7=>$namaakun7) {
				if(substr($akun7,0,5) == $akun) {
					setIt($data3[$akun][$akun7]['today']['upah'],0);
					setIt($data3[$akun][$akun7]['todate']['upah'],0);
					setIt($dataPres[$akun][$akun7]['today']['hasil'],0);
					setIt($dataPres[$akun][$akun7]['today']['hk'],0);
					setIt($dataPres[$akun][$akun7]['todate']['hasil'],0);
					setIt($dataPres[$akun][$akun7]['todate']['hk'],0);
					
					if(!empty($first7)) {
						$tab .= "<tr class='rowcontent'><td colspan=2></td>";
					}
					$tab .= "<td>".$akun7."</td>";
					$tab .= "<td>".$namaakun7."</td>";
					$tab .= "<td>".(isset($optSat[$akun7])? $optSat[$akun7]: '')."</td>";
					
					setIt($dataPres[$akun7]['today']['hasil'],0);
					setIt($dataPres[$akun7]['today']['hk'],0);
					setIt($dataPres[$akun7]['todate']['hasil'],0);
					setIt($dataPres[$akun7]['todate']['hk'],0);
					// Level 4 Function
					$lv4Upah = "level4(event,'upah','".$akun7."','".$namaakun7."')";
					$tab .= "<td onclick=\"".$lv4Upah."\" style='cursor:pointer' align=right>".number_format($dataPres[$akun7]['today']['hasil'],2)."</td>";
					$tab .= "<td onclick=\"".$lv4Upah."\" style='cursor:pointer' align=right>".number_format($dataPres[$akun7]['today']['hk'],2)."</td>";
					$tab .= "<td onclick=\"".$lv4Upah."\" style='cursor:pointer' align=right>".number_format($data3[$akun][$akun7]['today']['upah'],2)."</td>";
					$tab .= "<td onclick=\"".$lv4Upah."\" style='cursor:pointer' align=right>".number_format($dataPres[$akun7]['todate']['hasil'],2)."</td>";
					$tab .= "<td onclick=\"".$lv4Upah."\" style='cursor:pointer' align=right>".number_format($dataPres[$akun7]['todate']['hk'],2)."</td>";
					$tab .= "<td onclick=\"".$lv4Upah."\" style='cursor:pointer' align=right>".number_format($data3[$akun][$akun7]['todate']['upah'],2)."</td>";
					
					if(empty($dataMat[$akun7])) {
						$tab .= "<td colspan=6></td>";
					} else {
						// Baris pertama Material
						foreach($dataMat[$akun7] as $brg => $r) {
							$lv4Mat = "level4(event,'material','".$akun7."','".$namaakun7."','".$brg."')";
							$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer'>".$r['nama']."</td>";
							$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer'>".$r['satuan']."</td>";
							$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer' align=right>".number_format($r['today']['vol'],2)."</td>";
							$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer' align=right>".number_format($r['today']['rupiah'],2)."</td>";
							$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer' align=right>".number_format($r['todate']['vol'],2)."</td>";
							$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer' align=right>".number_format($r['todate']['rupiah'],2)."</td>";
							break;
						}
					}
					
					// VHC (Transport)
					setIt($dataVhc[$akun7]['todate'],0);
					setIt($dataVhc[$akun7]['today'],0);
					setIt($data3[$akun][$akun7]['todate']['transport']['total'],0);
					setIt($data3[$akun][$akun7]['today']['transport']['total'],0);
					
					$lv4Vhc = "level4(event,'vhc','".$akun7."','".$namaakun7."')";
					$tab .= "<td onclick=\"".$lv4Vhc."\" style='cursor:pointer' align=right>".number_format($dataVhc[$akun7]['today'],2)."</td>";//dodol
					$tab .= "<td onclick=\"".$lv4Vhc."\" style='cursor:pointer' align=right>".number_format($data3[$akun][$akun7]['today']['transport']['total'],2)."</td>";
					$tab .= "<td onclick=\"".$lv4Vhc."\" style='cursor:pointer' align=right>".number_format($dataVhc[$akun7]['todate'],2)."</td>";
					$tab .= "<td onclick=\"".$lv4Vhc."\" style='cursor:pointer' align=right>".number_format($data3[$akun][$akun7]['todate']['transport']['total'],2)."</td>";
					
					// Biaya Lain
					setIt($data3[$akun][$akun7]['todate']['lain'],0);
					setIt($data3[$akun][$akun7]['today']['lain'],0);
					$lv4Lain = "level4(event,'lain','".$akun7."','".$namaakun7."')";
					$tab .= "<td onclick=\"".$lv4Lain."\" style='cursor:pointer' align=right>".number_format($data3[$akun][$akun7]['today']['lain'],2)."</td>";
					$tab .= "<td onclick=\"".$lv4Lain."\" style='cursor:pointer' align=right>".number_format($data3[$akun][$akun7]['todate']['lain'],2)."</td>";
					
					// Total
					setIt($data3[$akun][$akun7]['todate']['total'],0);
					setIt($data3[$akun][$akun7]['today']['total'],0);
					$lv4Total = "level4(event,'total','".$akun7."','".$namaakun7."')";
					$tab .= "<td onclick=\"".$lv4Total."\" style='cursor:pointer' align=right>".number_format($data3[$akun][$akun7]['today']['total'],2)."</td>";
					$tab .= "<td onclick=\"".$lv4Total."\" style='cursor:pointer' align=right>".number_format($data3[$akun][$akun7]['todate']['total'],2)."</td>";
					
					// Jika material lebih dari 1
					if(!empty($dataMat[$akun7]) and count($dataMat[$akun7]) > 1) {
						$i = 0;
						foreach($dataMat[$akun7] as $brg => $r) {
							if($i>0) {
								$lv4Mat = "level4(event,'material','".$akun7."','".$namaakun7."','".$brg."')";
								$tab .= "</tr><tr class=rowcontent>";
								$tab .= "<td colspan=11></td>";
								$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer'>".$r['nama']."</td>";
								$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer'>".$r['satuan']."</td>";
								$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer' align=right>".number_format($r['today']['vol'],2)."</td>";
								$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer' align=right>".number_format($r['today']['rupiah'],2)."</td>";
								$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer' align=right>".number_format($r['todate']['vol'],2)."</td>";
								$tab .= "<td onclick=\"".$lv4Mat."\" style='cursor:pointer' align=right>".number_format($r['todate']['rupiah'],2)."</td>";
								$tab .= "<td colspan=10></td>";
							}
							$i++;
						}
					}
					
					$tab .= "</tr>";
					$first7++;
					
					setIt($data3[$akun][$akun7]['today']['material']['total'],0);
					setIt($data3[$akun][$akun7]['todate']['material']['total'],0);
					
					// Hitung Total
					$sTotal[0] += $dataPres[$akun7]['today']['hk'];
					$sTotal[1] += $data3[$akun][$akun7]['today']['upah'];
					$sTotal[2] += $dataPres[$akun7]['todate']['hk'];
					$sTotal[3] += $data3[$akun][$akun7]['todate']['upah'];
					$sTotal[4] += $data3[$akun][$akun7]['today']['material']['total'];
					$sTotal[5] += $data3[$akun][$akun7]['todate']['material']['total'];
					$sTotal[6] += $data3[$akun][$akun7]['today']['lain'];
					$sTotal[7] += $data3[$akun][$akun7]['todate']['lain'];
					$sTotal[8] += $data3[$akun][$akun7]['today']['total'];
					$sTotal[9] += $data3[$akun][$akun7]['todate']['total'];
					// Hitung Total Transport
					$sTotal[10] += $dataVhc[$akun7]['today'];
					$sTotal[11] += $data3[$akun][$akun7]['today']['transport']['total'];
					$sTotal[12] += $dataVhc[$akun7]['todate'];
					$sTotal[13] += $data3[$akun][$akun7]['todate']['transport']['total'];
					
					$gTotal[0] += $dataPres[$akun7]['today']['hk'];
					$gTotal[1] += $data3[$akun][$akun7]['today']['upah'];
					$gTotal[2] += $dataPres[$akun7]['todate']['hk'];
					$gTotal[3] += $data3[$akun][$akun7]['todate']['upah'];
					$gTotal[4] += $data3[$akun][$akun7]['today']['material']['total'];
					$gTotal[5] += $data3[$akun][$akun7]['todate']['material']['total'];
					$gTotal[6] += $data3[$akun][$akun7]['today']['lain'];
					$gTotal[7] += $data3[$akun][$akun7]['todate']['lain'];
					$gTotal[8] += $data3[$akun][$akun7]['today']['total'];
					$gTotal[9] += $data3[$akun][$akun7]['todate']['total'];
					//Hitung Grantotal Transport
					$gTotal[10] += $dataVhc[$akun7]['today'];
					$gTotal[11] += $data3[$akun][$akun7]['today']['transport']['total'];
					$gTotal[12] += $dataVhc[$akun7]['todate'];;
					$gTotal[13] += $data3[$akun][$akun7]['todate']['transport']['total'];
				}
			}
			$tab .= "</tr>";
			
			// Show Total
			$tab .= "<tr class=rowcontent style='font-weight:bold;background:yellow'>";
			$tab .= "<td colspan=5>Sub Total ".$akun."</td><td></td>";
			$tab .= "<td align=right>".number_format($sTotal[0],2)."</td>";
			$tab .= "<td align=right>".number_format($sTotal[1],2)."</td><td></td>";
			$tab .= "<td align=right>".number_format($sTotal[2],2)."</td>";
			$tab .= "<td align=right>".number_format($sTotal[3],2)."</td><td colspan=3></td>";
			$tab .= "<td align=right>".number_format($sTotal[4],2)."</td><td></td>";
			$tab .= "<td align=right>".number_format($sTotal[5],2)."</td>";
			$tab .= "<td align=right>".number_format($sTotal[10],2)."</td>";
			$tab .= "<td align=right>".number_format($sTotal[11],2)."</td>";
			$tab .= "<td align=right>".number_format($sTotal[12],2)."</td>";
			$tab .= "<td align=right>".number_format($sTotal[13],2)."</td>";
			$tab .= "<td align=right>".number_format($sTotal[6],2)."</td>";
			$tab .= "<td align=right>".number_format($sTotal[7],2)."</td>";
			$tab .= "<td align=right>".number_format($sTotal[8],2)."</td>";
			$tab .= "<td align=right>".number_format($sTotal[9],2)."</td>";
			$tab .= "</tr>";
		}
		
		// Show Grand Total
		$tab .= "<tr class=rowcontent style='font-weight:bold;background:blue;color:white'>";
		$tab .= "<td colspan=5>Grand Total</td><td></td>";
		$tab .= "<td align=right>".number_format($gTotal[0],2)."</td>";
		$tab .= "<td align=right>".number_format($gTotal[1],2)."</td><td></td>";
		$tab .= "<td align=right>".number_format($gTotal[2],2)."</td>";
		$tab .= "<td align=right>".number_format($gTotal[3],2)."</td><td colspan=3></td>";
		$tab .= "<td align=right>".number_format($gTotal[4],2)."</td><td></td>";
		$tab .= "<td align=right>".number_format($gTotal[5],2)."</td>";
		$tab .= "<td align=right>".number_format($gTotal[10],2)."</td>";
		$tab .= "<td align=right>".number_format($gTotal[11],2)."</td>";
		$tab .= "<td align=right>".number_format($gTotal[12],2)."</td>";
		$tab .= "<td align=right>".number_format($gTotal[13],2)."</td>";
		$tab .= "<td align=right>".number_format($gTotal[6],2)."</td>";
		$tab .= "<td align=right>".number_format($gTotal[7],2)."</td>";
		$tab .= "<td align=right>".number_format($gTotal[8],2)."</td>";
		$tab .= "<td align=right>".number_format($gTotal[9],2)."</td>";
		$tab .= "</tr>";
		
		$tab .= "</tbody>";
		
		$tab .= "</table>";
		break;
	
	case 4:
		/***********************************************************************
		 ** [LEVEL 4] Table Content ********************************************
		 ***********************************************************************/
		$tab .= "<link rel=stylesheet type='text/css' href='style/generic.css'>";
		// exit("error : ".$param['noakun']);
		if($param['title']=='upah'){
			if(count($dataPres) <= 0){
				$tab .= $_SESSION['lang']['datanotfound'];
			}else{
				if($mode=='excel') {
					$tab .= "<table class=sortable cellpadding=3 border=1>";
					$tab .= "<tr>";
					$tab .= "<td colspan=10 style='text-align:left;font-weight:bold'>Account Report Deatails Upah</td>";
					$tab .= "</tr>";
				} else {
					$tab .= "<button class=mybutton onclick=\"parent.formPrint('excel',4,'##ptRep##kebunRep##tipeRep##tanggalRep##statustanam##tahuntanam##noakun##namakegiatan##kodebarang##title','','kebun_slave_2accreport',event)\" >";
					$tab .= "Excel</button>";
					$tab .= "<table class=sortable cellspacing=1 width=100%>";
				}
				
				// Header
				$tab .= "<thead style='text-align:center'>";
				
				//Header 1
				$tab .= "<tr class=rowheader>";
				$tab .= "<td rowspan=3>".$_SESSION['lang']['noakun']."</td>";
				$tab .= "<td rowspan=3>DESCRIPTION SUB ACTIVITY</td>";
				$tab .= "<td rowspan=3>".$_SESSION['lang']['blok']."</td>";
				$tab .= "<td rowspan=3>".$_SESSION['lang']['satuan']."</td>";
				$tab .= "<td colspan=6>".$_SESSION['lang']['upah']."</td></tr>";
				
				//Header 2
				$tab .= "<tr class=rowheader>";
				$tab .= "<td colspan=3>TO DAY</td>";
				$tab .= "<td colspan=3>TO DATE</td></tr>";
				
				//Header 3
				$tab .= "<tr class=rowheader>";
				$tab .= "<td>HA DONE</td>";
				$tab .= "<td>".$_SESSION['lang']['jhk']."</td>";
				$tab .= "<td>".$_SESSION['lang']['biaya']."</td>";
				$tab .= "<td>HA DONE</td>";
				$tab .= "<td>HK</td>";
				$tab .= "<td>".$_SESSION['lang']['biaya']."</td></tr>";
				
				$tab .= "</thead>";
				$tab .= "<tbody>";
				
					$nourut = 0;
					$sTotal = array(0,0,0,0,0,0);
					foreach($dataPres as $kodeorg=>$val){
						$nourut += 1;
						$tab .= "<tr class='rowcontent'>";
						if($nourut == 1){
							$tab .= "<td rowspan='".count($dataPres)."' style='vertical-align:top;'>".$param['noakun']."</td>";
							$tab .= "<td rowspan='".count($dataPres)."' style='vertical-align:top;'>".$param['namakegiatan']."</td>";
						}
						$tab .= "<td>".$val['todate']['kodeorg']."</td>";
						$tab .= "<td>".$val['todate']['satuan']."</td>";
						$tab .= "<td style='text-align:right;'>".number_format($val['today']['hasil'],2)."</td>";
						$tab .= "<td style='text-align:right;'>".number_format($val['today']['hk'],2)."</td>";
						$tab .= "<td style='text-align:right;'>".number_format($data4[$kodeorg]['today']['upah'],2)."</td>";
						$tab .= "<td style='text-align:right;'>".number_format($val['todate']['hasil'],2)."</td>";
						$tab .= "<td style='text-align:right;'>".number_format($val['todate']['hk'],2)."</td>";
						$tab .= "<td style='text-align:right;'>".number_format($data4[$kodeorg]['todate']['upah'],2)."</td>";
						$tab .= "</tr>";
						
						$sTotal[0] += $val['today']['hasil'];
						$sTotal[1] += $val['today']['hk'];
						$sTotal[2] += $data4[$kodeorg]['today']['upah'];
						$sTotal[3] += $val['todate']['hasil'];
						$sTotal[4] += $val['todate']['hk'];
						$sTotal[5] += $data4[$kodeorg]['todate']['upah'];
					}
				$tab .= "<tr class='rowcontent' style='font-weight:bold;'>";
				$tab .= "<td colspan=4 style='text-align:center;'>TOTAL</td>";
				$tab .= "<td style='text-align:right;'>".number_format($sTotal[0],2)."</td>";
				$tab .= "<td style='text-align:right;'>".number_format($sTotal[1],2)."</td>";
				$tab .= "<td style='text-align:right;'>".number_format($sTotal[2],2)."</td>";
				$tab .= "<td style='text-align:right;'>".number_format($sTotal[3],2)."</td>";
				$tab .= "<td style='text-align:right;'>".number_format($sTotal[4],2)."</td>";
				$tab .= "<td style='text-align:right;'>".number_format($sTotal[5],2)."</td>";
				$tab .= "</tr>";
				
				$tab .=	"</tbody>";
				$tab .= "<table>";
			}
		}else if($param['title']=='material'){
			if(count($dataMat) <= 0){
				$tab .= $_SESSION['lang']['datanotfound'];
			}else{
				if($mode=='excel') {
					$tab .= "<table class=sortable cellpadding=3 border=1>";
					$tab .= "<tr>";
					$tab .= "<td colspan=9 style='text-align:left;font-weight:bold'>Account Report Deatails Upah</td>";
					$tab .= "</tr>";
				} else {
					$tab .= "<button class=mybutton onclick=\"parent.formPrint('excel',4,'##ptRep##kebunRep##tipeRep##tanggalRep##statustanam##tahuntanam##noakun##namakegiatan##kodebarang##title','','kebun_slave_2accreport',event)\" >";
					$tab .= "Excel</button>";
					$tab .= "<table class=sortable cellspacing=1 width=100%>";
				}
				
				// Header
				$tab .= "<thead style='text-align:center'>";
				
				//Header 1
				$tab .= "<tr class=rowheader>";
				$tab .= "<td rowspan=3>".$_SESSION['lang']['noakun']."</td>";
				$tab .= "<td rowspan=3>DESCRIPTION SUB ACTIVITY</td>";
				$tab .= "<td rowspan=3>".$_SESSION['lang']['blok']."</td>";
				$tab .= "<td colspan=6>".$_SESSION['lang']['material']."</td></tr>";
				
				//Header 2
				$tab .= "<tr class=rowheader>";
				$tab .= "<td rowspan=2>".$_SESSION['lang']['nama']."</td>";
				$tab .= "<td rowspan=2>UNIT</td>";
				$tab .= "<td colspan=2>TO DAY</td>";
				$tab .= "<td colspan=2>TO DATE</td></tr>";
				
				//Header 3
				$tab .= "<tr class=rowheader>";
				$tab .= "<td>VOL</td>";
				$tab .= "<td>".$_SESSION['lang']['biaya']."</td>";
				$tab .= "<td>VOL</td>";
				$tab .= "<td>".$_SESSION['lang']['biaya']."</td></tr>";
				$tab .= "</thead>";
				$tab .= "<tbody>";
				
				
				$nourut = 0;
				$sTotal = array(0,0,0,0);
				foreach($dataMat as $kodeorg=>$val) {
					$nourut += 1;
					$tab .= "<tr class='rowcontent'>";
					if($nourut==1){
						$tab .= "<td rowspan='".count($dataMat)."' style='vertical-align:top;'>".$param['noakun']."</td>";
						$tab .= "<td rowspan='".count($dataMat)."' style='vertical-align:top;'>".$param['namakegiatan']."</td>";
					}		
					$tab .= "<td>".$val['todate']['blok']."</td>";
					$tab .= "<td>".$val['todate']['namabarang']."</td>";
					$tab .= "<td>".$val['todate']['satuan']."</td>";
					$tab .= "<td style='text-align:right'>".number_format($val['today']['vol'],2)."</td>";
					$tab .= "<td style='text-align:right'>".number_format($val['today']['rupiah'],2)."</td>";
					$tab .= "<td style='text-align:right'>".number_format($val['todate']['vol'],2)."</td>";
					$tab .= "<td style='text-align:right'>".number_format($val['todate']['rupiah'],2)."</td>";
					$tab .= "</tr>";
					
					$sTotal[0] += $val['today']['vol'];
					$sTotal[1] += $val['today']['rupiah'];
					$sTotal[2] += $val['todate']['vol'];
					$sTotal[3] += $val['todate']['rupiah'];
				}
				
				$tab .= "<tr class='rowcontent' style='font-weight:bold'>";
				$tab .= "<td colspan=5 style='text-align:center'>TOTAL</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[0],2)."</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[1],2)."</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[2],2)."</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[3],2)."</td>";
				$tab .= "</tr>";
				
				$tab .= "</tbody>";
				$tab .= "<table>";
			}
		}else if($param['title']=='vhc'){
			$cHidden = 0;
			foreach($data5 as $kodeorg=>$val) {
				if($val['today']==0 && $val['todate']==0){
					$cHidden += 1;
				}else{
					$cHidden += 0;
				}
			}
			if((count($data5)-$cHidden) <= 0){
				$tab .= $_SESSION['lang']['datanotfound'];
			}else{
				if($mode=='excel') {
					$tab .= "<table class=sortable cellpadding=3 border=1>";
					$tab .= "<tr>";
					$tab .= "<td colspan=9 style='text-align:left;font-weight:bold'>Account Report Deatails Upah</td>";
					$tab .= "</tr>";
				} else {
					$tab .= "<button class=mybutton onclick=\"parent.formPrint('excel',4,'##ptRep##kebunRep##tipeRep##tanggalRep##statustanam##tahuntanam##noakun##namakegiatan##kodebarang##title','','kebun_slave_2accreport',event)\" >";
					$tab .= "Excel</button>";
					$tab .= "<table class=sortable cellspacing=1 width=100%>";
				}
				
				// Header
				$tab .= "<thead style='text-align:center'>";
				
				//Header 1
				$tab .= "<tr class=rowheader>";
				$tab .= "<td rowspan=3>".$_SESSION['lang']['noakun']."</td>";
				$tab .= "<td rowspan=3>DESCRIPTION SUB ACTIVITY</td>";
				$tab .= "<td rowspan=3>".$_SESSION['lang']['blok']."</td>";
				$tab .= "<td colspan=6>".$_SESSION['lang']['transport']."</td></tr>";
				
				//Header 2
				$tab .= "<tr class=rowheader>";
				$tab .= "<td rowspan=2>TYPE TRANSPORT</td>";
				$tab .= "<td rowspan=2>UNIT</td>";
				$tab .= "<td colspan=2>TO DAY</td>";
				$tab .= "<td colspan=2>TO DATE</td></tr>";
				
				//Header 3
				$tab .= "<tr class=rowheader>";
				$tab .= "<td>VOL</td>";
				$tab .= "<td>".$_SESSION['lang']['biaya']."</td>";
				$tab .= "<td>VOL</td>";
				$tab .= "<td>".$_SESSION['lang']['biaya']."</td></tr>";
				$tab .= "</thead>";
				$tab .= "<tbody>";
				
				
				$nourut = 0;
				$sTotal = array(0,0,0,0);
				foreach($data5 as $kodeorg=>$val) {
					if($val['today']==0 && $val['todate']==0){}else{
					$nourut += 1;
					$tab .= "<tr class='rowcontent'>";
					if($nourut==1){
						$tab .= "<td rowspan='".(count($data5)-$cHidden)."' style='vertical-align:top;'>".$param['noakun']."</td>";
						$tab .= "<td rowspan='".(count($data5)-$cHidden)."' style='vertical-align:top;'>".$param['namakegiatan']."</td>";
					}
					$tab .= "<td>".$val['blok']."</td>";
					$tab .= "<td>".$val['type']."</td>";
					$tab .= "<td>".$val['vhc']."</td>";
					$tab .= "<td style='text-align:right'>".number_format($dataVhc[$kodeorg]['today'],2)."</td>";
					$tab .= "<td style='text-align:right'>".number_format($val['today'],2)."</td>";
					$tab .= "<td style='text-align:right'>".number_format($dataVhc[$kodeorg]['todate'],2)."</td>";
					$tab .= "<td style='text-align:right'>".number_format($val['todate'],2)."</td>";
					$tab .= "</tr>";
					
					setIt($val['today'],0);
					setIt($val['todate'],0);
					setIt($dataVhc[$kodeorg]['today'],0);
					
					$sTotal[0] += $dataVhc[$kodeorg]['today'];
					$sTotal[1] += $val['today'];
					$sTotal[2] += $dataVhc[$kodeorg]['todate'];
					$sTotal[3] += $val['todate'];
					}
				}
				
				$tab .= "<tr class='rowcontent' style='font-weight:bold'>";
				$tab .= "<td colspan=5 style='text-align:center'>TOTAL</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[0],2)."</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[1],2)."</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[2],2)."</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[3],2)."</td>";
				$tab .= "</tr>";
				
				$tab .= "</tbody>";
				$tab .= "<table>";
			}
		}else if($param['title']=='lain'){
			$cHidden = 0;
			foreach($data4 as $kodeorg=>$val) {
				if($val['today']['lain']==0 && $val['todate']['lain']==0){
					$cHidden += 1;
				}
			}
			if((count($data4)-$cHidden) <= 0){
				$tab .= $_SESSION['lang']['datanotfound'];
			}else{
				if($mode=='excel') {
					$tab .= "<table class=sortable cellpadding=3 border=1>";
					$tab .= "<tr>";
					$tab .= "<td colspan=2 style='text-align:left;font-weight:bold'>Account Report Deatails Upah</td>";
					$tab .= "</tr>";
				} else {
					$tab .= "<button class=mybutton onclick=\"parent.formPrint('excel',4,'##ptRep##kebunRep##tipeRep##tanggalRep##statustanam##tahuntanam##noakun##namakegiatan##kodebarang##title','','kebun_slave_2accreport',event)\" >";
					$tab .= "Excel</button>";
					$tab .= "<table class=sortable cellspacing=1 width=100%>";
				}
				
				// Header
				$tab .= "<thead style='text-align:center'>";
				
				//Header 1
				$tab .= "<tr class=rowheader>";
				$tab .= "<td rowspan=2>".$_SESSION['lang']['noakun']."</td>";
				$tab .= "<td rowspan=2>DESCRIPTION SUB ACTIVITY</td>";
				$tab .= "<td rowspan=2>".$_SESSION['lang']['blok']."</td>";
				$tab .= "<td colspan=2>".$_SESSION['lang']['lain']."</td></tr>";
				
				//Header 2
				$tab .= "<tr class=rowheader>";
				$tab .= "<td>TO DAY</td>";
				$tab .= "<td>TO DATE</td></tr>";
				$tab .= "</thead>";
				$tab .= "<tbody>";
				
				
				$nourut = 0;
				$sTotal = array(0,0);
				foreach($data4 as $kodeorg=>$val) {
					if($val['today']['lain']==0 && $val['todate']['lain']==0){}else{
					$nourut += 1;
					$tab .= "<tr class='rowcontent'>";
					if($nourut==1){
						$tab .= "<td rowspan='".(count($data4)-$cHidden)."' style='vertical-align:top;'>".$param['noakun']."</td>";
						$tab .= "<td rowspan='".(count($data4)-$cHidden)."' style='vertical-align:top;'>".$param['namakegiatan']."</td>";
					}		
					$tab .= "<td>".$kodeorg."</td>";
					$tab .= "<td style='text-align:right'>".number_format($val['today']['lain'],2)."</td>";
					$tab .= "<td style='text-align:right'>".number_format($val['todate']['lain'],2)."</td>";
					$tab .= "</tr>";
					
					$sTotal[0] += $val['today']['lain'];
					$sTotal[1] += $val['todate']['lain'];
					}
				}
				
				$tab .= "<tr class='rowcontent' style='font-weight:bold'>";
				$tab .= "<td colspan=3 style='text-align:center'>TOTAL</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[0],2)."</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[1],2)."</td>";
				$tab .= "</tr>";
				
				$tab .= "</tbody>";
				$tab .= "<table>";
			}
		}else{
			$cHidden = 0;
			foreach($data4 as $kodeorg=>$val) {
				if($val['today']['total']==0 && $val['todate']['total']==0){
					$cHidden += 1;
				}
			}
			if((count($data4)-$cHidden) <= 0){
				$tab .= $_SESSION['lang']['datanotfound'];
			}else{
				if($mode=='excel') {
					$tab .= "<table class=sortable cellpadding=3 border=1>";
					$tab .= "<tr>";
					$tab .= "<td colspan=2 style='text-align:left;font-weight:bold'>Account Report Deatails Upah</td>";
					$tab .= "</tr>";
				} else {
					$tab .= "<button class=mybutton onclick=\"parent.formPrint('excel',4,'##ptRep##kebunRep##tipeRep##tanggalRep##statustanam##tahuntanam##noakun##namakegiatan##kodebarang##title','','kebun_slave_2accreport',event)\" >";
					$tab .= "Excel</button>";
					$tab .= "<table class=sortable cellspacing=1 width=100%>";
				}
				
				// Header
				$tab .= "<thead style='text-align:center'>";
				
				//Header 1
				$tab .= "<tr class=rowheader>";
				$tab .= "<td rowspan=2>".$_SESSION['lang']['noakun']."</td>";
				$tab .= "<td rowspan=2>DESCRIPTION SUB ACTIVITY</td>";
				$tab .= "<td rowspan=2>".$_SESSION['lang']['blok']."</td>";
				$tab .= "<td colspan=2>TOTAL</td></tr>";
				
				//Header 2
				$tab .= "<tr class=rowheader>";
				$tab .= "<td>TO DAY</td>";
				$tab .= "<td>TO DATE</td></tr>";
				$tab .= "</thead>";
				$tab .= "<tbody>";
				
				
				$nourut = 0;
				$sTotal = array(0,0);
				foreach($data4 as $kodeorg=>$val) {
					if($val['today']['total']==0 && $val['todate']['total']==0){}else{
					$nourut += 1;
					$tab .= "<tr class='rowcontent'>";
					if($nourut==1){
						$tab .= "<td rowspan='".(count($data4)-$cHidden)."' style='vertical-align:top;'>".$param['noakun']."</td>";
						$tab .= "<td rowspan='".(count($data4)-$cHidden)."' style='vertical-align:top;'>".$param['namakegiatan']."</td>";
					}		
					$tab .= "<td>".$kodeorg."</td>";
					$tab .= "<td style='text-align:right'>".number_format($val['today']['total'],2)."</td>";
					$tab .= "<td style='text-align:right'>".number_format($val['todate']['total'],2)."</td>";
					$tab .= "</tr>";
					
					$sTotal[0] += $val['today']['total'];
					$sTotal[1] += $val['todate']['total'];
					}
				}
				
				$tab .= "<tr class='rowcontent' style='font-weight:bold'>";
				$tab .= "<td colspan=3 style='text-align:center'>TOTAL</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[0],2)."</td>";
				$tab .= "<td style='text-align:right'>".number_format($sTotal[1],2)."</td>";
				$tab .= "</tr>";
				
				$tab .= "</tbody>";
				$tab .= "<table>";
			}
		}
		break;
}


/** Output Type **/
if($mode=='excel') {
	if($level==4){
		$title = ucfirst($param['title']);
	}else{
		$title = "";
	}
	$stream = $tab;
	$nop_="LevelReport_Level_".$level."_".$title."_".date('Ymd_His');
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