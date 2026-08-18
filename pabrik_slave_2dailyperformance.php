<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses=checkPostGet('proses','');
$unit=checkPostGet('unit','');
$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));



    if($tgl1=='' || $tgl2=='' || $tgl1=='--' || $tgl2=='--'){
		echo"Error: Tanggal tidak boleh kosong"; 
		exit;
    }

    if($tgl1>$tgl2){
        echo"Error: Tanggal pertama tidak boleh lebih besar dari tanggal kedua"; 
		exit;
    }

$dttgl=rangeTanggalarr($tgl1,$tgl2);
	
$stream='';
if ($proses == 'excel') {
	$stream.="<table class=sortable cellspacing=1 cellpadding=3 border=1>";
} else 	{
	$stream.="<table class=sortable cellspacing=1 cellpadding=3 width=100%>";
}
	
$stream.="<thead>
<tr>
	<td rowspan=3 align=center style='min-width:80px'>".$_SESSION['lang']['tanggal']."</td>
	<td rowspan=3 align=center>Budget Harian (Ton)</td>
	<td rowspan=2 colspan=3 align=center>TBS Terima (Ton)</td>
	<td rowspan=3 align=center>TBS Terima (Ton)</td>
	<td rowspan=3 align=center>Kekurangan TBS (Ton)</td>
	<td rowspan=3 align=center>TBS Olah (Ton)</td>
	<td rowspan=3 align=center>Restan PKS (Ton)</td>
	<td rowspan=3 align=center>Jam Olah (Jam)</td>
	<td rowspan=3 align=center>CPO Prod (Ton)</td>
	<td rowspan=3 align=center>OER (%)</td>
	<td rowspan=3 align=center>Kernel Prod (Ton)</td>
	<td rowspan=3 align=center>KER (%)</td>
	<td rowspan=3 align=center>Mill Utilisasi</td>
	<td rowspan=3 align=center>THROUGHPUT</td>
	<td rowspan=3 align=center>MAINT HOURS</td>
	<td rowspan=3 align=center>DOWNTIME</td>
	<td rowspan=3 align=center>FFA</td>
	<td rowspan=3 align=center>Oil Loss</td>
	<td rowspan=3 align=center>Kernel Loss</td>
	<td rowspan=3 align=center>Despatch CPO</td>
	<td rowspan=3 align=center>Stock CPO</td>
	<td rowspan=3 align=center>Despatch Kernel</td>
	<td rowspan=3 align=center>Stok Kernel</td>
	<td colspan=5 align=center>Sortasi Buah Internal</td>
	<td colspan=5 align=center>Sortasi Buah External</td>
</tr>
<tr>
	<td align=center>Mentah</td>
	<td align=center>FR-1</td>
	<td align=center>FR-2</td>
	<td align=center>FR-3</td>
	<td align=center>Brondolan</td>
	<td align=center>Mentah</td>
	<td align=center>FR-1</td>
	<td align=center>FR-2</td>
	<td align=center>FR-3</td>
	<td align=center>Brondolan</td>
</tr>
<tr>
	<td align=center>INTI</td>
	<td align=center>PLASMA</td>
    <td align=center>LUAR</td>
	
	<td align=center>0%</td>
	<td align=center>< 5%</td>
	<td align=center>< 75%</td>
	<td align=center>> 20%</td>
	<td align=center>> 8%</td>
	
	<td align=center>0%</td>
	<td align=center>< 5%</td>
	<td align=center>< 75%</td>
	<td align=center>> 20%</td>
	<td align=center>> 8%</td>
</tr>
</thead>";

$arrData = array();
$str="select * from ".$dbname.".pabrik_timbangan where (left(tanggal,10) between '".$tgl1."' and '".$tgl2."') and millcode='".$unit."'";
$res=fetchdata($str);
foreach($res as $key=>$val){
	$xtgl = substr($val['tanggal'],0,10);
	if($val['kodebarang']=='40000003'){
		if($val['divcode']==''){
			$arrData[$xtgl]['luar'] = $arrData[$xtgl]['luar'] + $val['beratbersih'];		
		}else{
			if($val['divcode']=='TPRE10'){
				$arrData[$xtgl]['plasma'] = $arrData[$xtgl]['plasma'] + $val['beratbersih'];	
			}else{
				$arrData[$xtgl]['inti'] = $arrData[$xtgl]['inti'] + $val['beratbersih'];	
			}
		}
	}
	
	if($val['kodebarang']=='40000001'){
		$arrData[$xtgl]['dispathcpo'] = $arrData[$xtgl]['dispathcpo'] + $val['beratbersih'];
	}
	if($val['kodebarang']=='40000002'){
		$arrData[$xtgl]['dispathpk'] = $arrData[$xtgl]['dispathpk'] + $val['beratbersih'];
	}
}

$str="select * from ".$dbname.".pabrik_produksi where kodeorg='".$unit."' and (tanggal between '".$tgl1."' and '".$tgl2."')";
$res=fetchdata($str);
foreach($res as $key=>$val){
	$arrData[$val['tanggal']]['tbsolah'] = $val['tbsdiolah'];
	$arrData[$val['tanggal']]['restan'] = $val['sisahariini'];
	$arrData[$val['tanggal']]['cpokg'] = $val['oer'];
	$arrData[$val['tanggal']]['pkkg'] = $val['oerph'];
	$arrData[$val['tanggal']]['ffa'] = $val['ffa'];
}

$str="select * from ".$dbname.".pabrik_pengolahan where kodeorg='".$unit."' and (tanggal between '".$tgl1."' and '".$tgl2."')";
$res=fetchdata($str);
foreach($res as $key=>$val){
	$jamolah = $val['jamdinasbruto'] - $val['jamstagnasi'];
	$arrData[$val['tanggal']]['jamolah'] = $arrData[$val['tanggal']]['jamolah'] + $jamolah;
	$arrData[$val['tanggal']]['downtime'] = $arrData[$val['tanggal']]['downtime'] + $val['jamstagnasi'];
}

$str="select * from ".$dbname.".pabrik_rawatmesinht where pabrik='".$unit."' and (tanggal between '".$tgl1."' and '".$tgl2."') and tipeperbaikan='corrective' and downstatus!='EDT'";
$res=fetchdata($str);
foreach($res as $key=>$val){
	$arrData[$val['tanggal']]['maintenance'] = $arrData[$val['tanggal']]['maintenance'] + $val['jumlahjamperbaikan'];
}

$str="select * from ".$dbname.".pabrik_mr_roa where unit='".$unit."' and (tanggal between '".$tgl1."' and '".$tgl2."')";
$res=fetchdata($str);
foreach($res as $key=>$val){
	if(substr($val['parameter'],0,1)=='A'){
		$arrData[$val['tanggal']]['oilloses'] = $arrData[$val['tanggal']]['oilloses'] + $val['nilai'];
	}
	
	if(substr($val['parameter'],0,1)=='B'){
		$arrData[$val['tanggal']]['pkloses'] = $arrData[$val['tanggal']]['pkloses'] + $val['nilai'];
	}
}

$str="select * from ".$dbname.".pabrik_masukkeluartangki where kodeorg='".$unit."' and (tanggal between '".$tgl1."' and '".$tgl2."')";
$res=fetchdata($str);
foreach($res as $key=>$val){
	$optKomoditi = makeOption($dbname,'pabrik_5tangki','kodetangki,komoditi',"kodetangki='".$val['kodetangki']."'");
	if($optKomoditi[$val['kodetangki']]=='CPO'){
		$arrData[$val['tanggal']]['stokcpo'] = $arrData[$val['tanggal']]['stokcpo'] + $val['kuantitas'];
	}
	
	if($optKomoditi[$val['kodetangki']]=='KER'){
		$arrData[$val['tanggal']]['stokpk'] = $arrData[$val['tanggal']]['stokpk'] + $val['kernelquantity'];
	}
}

$str="select * from ".$dbname.".sortasi_harian_kebunht where kodeorg='".$unit."' and (tanggal between '".$tgl1."' and '".$tgl2."')";
$res=fetchdata($str);
foreach($res as $key=>$val){
	$strx="select * from ".$dbname.".sortasi_harian_kebundt where idht='".$val['id']."'";
	$resx=fetchdata($strx);
	foreach($resx as $keyx=>$valx){
		if($val['tipe']=='Internal'){
			$arrData[$val['tanggal']]['intttljjg'] = $arrData[$val['tanggal']]['intttljjg'] + $valx['totjang'];
			$arrData[$val['tanggal']]['intttlkg'] = $arrData[$val['tanggal']]['intttlkg'] + $valx['totalkgtbs'];
			
			$arrData[$val['tanggal']]['intmentah'] = $arrData[$val['tanggal']]['intmentah'] + $valx['mentahjjng'];
			$arrData[$val['tanggal']]['intfr1'] = $arrData[$val['tanggal']]['intfr1'] + $valx['satujjng'];
			$arrData[$val['tanggal']]['intfr2'] = $arrData[$val['tanggal']]['intfr2'] + $valx['duajjng'];
			$arrData[$val['tanggal']]['intfr3'] = $arrData[$val['tanggal']]['intfr3'] + $valx['tigajjng'];
			$arrData[$val['tanggal']]['intbrondol'] = $arrData[$val['tanggal']]['intbrondol'] + $valx['brondolkg'];
		}
		if($val['tipe']=='External'){
			$arrData[$val['tanggal']]['eksttljjg'] = $arrData[$val['tanggal']]['eksttljjg'] + $valx['totjang'];
			$arrData[$val['tanggal']]['eksttlkg'] = $arrData[$val['tanggal']]['eksttlkg'] + $valx['tonase'];
			
			$arrData[$val['tanggal']]['eksmentah'] = $arrData[$val['tanggal']]['eksmentah'] + $valx['mentahjjng'];
			$arrData[$val['tanggal']]['eksfr1'] = $arrData[$val['tanggal']]['eksfr1'] + $valx['satujjng'];
			$arrData[$val['tanggal']]['eksfr2'] = $arrData[$val['tanggal']]['eksfr2'] + $valx['duajjng'];
			$arrData[$val['tanggal']]['eksfr3'] = $arrData[$val['tanggal']]['eksfr3'] + $valx['tigajjng'];
			$arrData[$val['tanggal']]['eksbrondol'] = $arrData[$val['tanggal']]['eksbrondol'] + $valx['brondolkg'];
		}
	}
}

// echo"<pre>";
// print_r($arrData);
// echo"</pre>";

foreach($dttgl as $key){
	## Dalam Ton
	$budget = 0;
	$inti = ($arrData[$key]['inti'] > 0 ? ($arrData[$key]['inti']/1000) : 0);
	$plasma = ($arrData[$key]['plasma'] > 0 ? ($arrData[$key]['plasma']/1000) : 0);
	$luar = ($arrData[$key]['luar'] > 0 ? ($arrData[$key]['luar']/1000) : 0);
	$ttlterima = $inti + $plasma + $luar;
	$ttlkurang = (($budget - $ttlterima) < 0 ? "(".abs($budget - $ttlterima).")" : $budget - $ttlterima);
	
	$tbsolah = ($arrData[$key]['tbsolah'] > 0 ? ($arrData[$key]['tbsolah']/1000) : 0);
	$restan = ($arrData[$key]['restan'] > 0 ? ($arrData[$key]['restan']/1000) : 0);
	
	$cpokg = ($arrData[$key]['cpokg'] > 0 ? ($arrData[$key]['cpokg']/1000) : 0);
	$oercpo = ($cpokg > 0 ? ($cpokg/$tbsolah * 100) : 0);
	$pkkg = ($arrData[$key]['pkkg'] > 0 ? ($arrData[$key]['pkkg']/1000) : 0);
	$oerpk = ($oerpk > 0 ? ($oerpk/$tbsolah * 100) : 0);
	
	$utilasi = (($budget <= 0 or $tbsolah <= 0) ? 0 : ($tbsolah/$budget * 100));
	
	$throughput = ($arrData[$key]['jamolah'] <= 0 ? 0 : ($tbsolah / ($arrData[$key]['jamolah']-$arrData[$key]['maintenance'])));
	
	$stokcpo = ($arrData[$key]['stokcpo'] > 0 ? ($arrData[$key]['stokcpo']/1000) : 0);
	$stokpk = ($arrData[$key]['stokpk'] > 0 ? ($arrData[$key]['stokpk']/1000) : 0);
	
	$dispathcpo = ($arrData[$key]['dispathcpo'] > 0 ? ($arrData[$key]['dispathcpo']/1000) : 0);
	$dispathpk = ($arrData[$key]['dispathpk'] > 0 ? ($arrData[$key]['dispathpk']/1000) : 0);
	
	$intmentahkg = ($arrData[$key]['intmentah'] / $arrData[$key]['intttljjg'] * 100);
	$intfr1 = ($arrData[$key]['intfr1'] / $arrData[$key]['intttljjg'] * 100);
	$intfr2 = ($arrData[$key]['intfr2'] / $arrData[$key]['intttljjg'] * 100);
	$intfr3 = ($arrData[$key]['intfr3'] / $arrData[$key]['intttljjg'] * 100);
	$intbrondol = ($arrData[$key]['intbrondol'] / $arrData[$key]['intttlkg'] * 100);
	
	$eksmentahkg = ($arrData[$key]['eksmentahkg'] / $arrData[$key]['eksttljjg'] * 100);
	$eksfr1 = ($arrData[$key]['eksfr1'] / $arrData[$key]['eksttljjg'] * 100);
	$eksfr2 = ($arrData[$key]['eksfr2'] / $arrData[$key]['eksttljjg'] * 100);
	$eksfr3 = ($arrData[$key]['eksfr3'] / $arrData[$key]['eksttljjg'] * 100);
	$eksbrondol = ($arrData[$key]['eksbrondol'] / $arrData[$key]['eksttlkg'] * 100);
	
	$stream.="<tr class=rowcontent>
		<td align=center>".tanggalnormal($key)."</td>
		<td align=right>".$budget."</td>
		<td align=right>".$inti."</td>
		<td align=right>".$plasma."</td>
		<td align=right>".$luar."</td>
		<td align=right>".$ttlterima."</td>
		<td align=right>".$ttlkurang."</td>
		<td align=right>".$tbsolah."</td>
		<td align=right>".$restan."</td>
		<td align=right>".($arrData[$key]['jamolah'] <= 0 ? 0 : number_format($arrData[$key]['jamolah'],2))."</td>
		<td align=right>".$cpokg."</td>
		<td align=right>".number_format($oercpo,2)."</td>
		<td align=right>".$cpokg."</td>
		<td align=right>".number_format($oerpk,2)."</td>
		<td align=right>".number_format($utilasi,2)."</td>
		<td align=right>".number_format($throughput,2)."</td>
		<td align=right>".($arrData[$key]['maintenance'] <= 0 ? 0 : number_format($arrData[$key]['maintenance'],2))."</td>
		<td align=right>".($arrData[$key]['downtime'] <= 0 ? 0 : number_format($arrData[$key]['downtime'],2))."</td>
		<td align=right>".($arrData[$key]['ffa'] <= 0 ? 0 : number_format($arrData[$key]['ffa'],2))."</td>
		<td align=right>".($arrData[$key]['oilloses'] <= 0 ? 0 : number_format($arrData[$key]['oilloses'],2))."</td>
		<td align=right>".($arrData[$key]['pkloses'] <= 0 ? 0 : number_format($arrData[$key]['pkloses'],2))."</td>
		<td align=right>".$dispathcpo."</td>
		<td align=right>".$stokcpo."</td>
		<td align=right>".$dispathpk."</td>
		<td align=right>".$stokpk."</td>
		<td align=right>".(is_nan($intmentahkg)?0:number_format($intmentahkg,2))."</td>
		<td align=right>".(is_nan($intfr1)?0:number_format($intfr1,2))."</td>
		<td align=right>".(is_nan($intfr2)?0:number_format($intfr2,2))."</td>
		<td align=right>".(is_nan($intfr3)?0:number_format($intfr3,2))."</td>
		<td align=right>".(is_nan($intbrondol)?0:number_format($intbrondol,2))."</td>
		<td align=right>".(is_nan($eksmentahkg)?0:number_format($eksmentahkg,2))."</td>
		<td align=right>".(is_nan($eksfr1)?0:number_format($eksfr1,2))."</td>
		<td align=right>".(is_nan($eksfr2)?0:number_format($eksfr2,2))."</td>
		<td align=right>".(is_nan($eksfr3)?0:number_format($eksfr3,2))."</td>
		<td align=right>".(is_nan($eksbrondol)?0:number_format($eksbrondol,2))."</td>
	</tr>";
}

// #tbs masuk
// $str="select * from ".$dbname.".pabrik_timbangan_vw where kodebarang='40000003' and millcode='".$unit."' and tanggal between '".$tgl1."' and '".$tgl2."' and kodeorg='' ".$where." order by tanggal asc";
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_ASSOC);
// while($bar=$res->fetch()){
	// $arrKebun[$bar['kodeorg']] = $bar['kodeorg'];
	// $arrDivisi[$bar['kodeorg']][$bar['kodecustomer']] = $bar['kodecustomer'];
	// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['tanggal'] = $bar['tanggal'];
	// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['tbsmasuk'] = $bar['beratbersih'];
	// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['jmljjg'] = $bar['jjg'];
	
	// $strx="select * from ".$dbname.".pabrik_sortasi where notiket='".$bar['notiket']."'";
	// $resx=fetchdata($strx);
	// foreach($resx as $keyx=>$valx){
		// if($valx['kodefraksi']=='mentah'){
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['mentahkg'] = $valx['kg'];
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['mentahper'] = $valx['persen'];
		// }
		// if($valx['kodefraksi']=='mengkal'){
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['mengkalkg'] = $valx['kg'];
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['mengkalper'] = $valx['persen'];
		// }
		// if($valx['kodefraksi']=='restan'){
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['restankg'] = $valx['kg'];
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['restanper'] = $valx['persen'];
		// }
		// if($valx['kodefraksi']=='busuk'){
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['busukkg'] = $valx['kg'];
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['busukper'] = $valx['persen'];
		// }
		// if($valx['kodefraksi']=='panjang'){
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['panjangkg'] = $valx['kg'];
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['panjangper'] = $valx['persen'];
		// }
		// if($valx['kodefraksi']=='air'){
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['airkg'] = $valx['kg'];
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['airper'] = $valx['persen'];
		// }
		// if($valx['kodefraksi']=='sampah'){
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['sampahkg'] = $valx['kg'];
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['sampahper'] = $valx['persen'];
		// }
		// if($valx['kodefraksi']=='tandan'){
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['tandankg'] = $valx['kg'];
			// $arrList[$bar['kodeorg']][$bar['kodecustomer']][$bar['notiket']]['tandanper'] = $valx['persen'];
		// }
	// }
// }

// $grdtbsmasuk = $grdjmljjg = $grdmentahkg = $grdmengkalkg = $grdrestankg = $grdbusukkg = $grdpanjangkg = $grdairkg = $grdsampahkg = $grdtandankg = $grdgradkg = 0;
// foreach($arrKebun as $val){
	// asort($arrDivisi[$val]);
	// foreach($arrDivisi[$val] as $val2){
		// $stream.="<tr class=rowcontent>
			// <td colspan='23' style='font-weight:bold'>".getSupNameWb($val2)."</td>";
		// $stream.="</tr>";
		// $ttltbsmasuk = $ttljmljjg = $ttlmentahkg = $ttlmengkalkg = $ttlrestankg = $ttlbusukkg = $ttlpanjangkg = $ttlairkg = $ttlsampahkg = $ttltandankg = $ttlgradkg = 0;
		// foreach($arrList[$val][$val2] as $key3=>$val3){
			// $totalgradkg = $val3['mentahkg'] + $val3['mengkalkg'] + $val3['restankg'] + $val3['busukkg'] + $val3['panjangkg'] + $val3['airkg'] + $val3['sampahkg'] + $val3['tandankg'];
			// $totalgradper = $val3['mentahper'] + $val3['mengkalper'] + $val3['restanper'] + $val3['busukper'] + $val3['panjangper'] + $val3['airper'] + $val3['sampahper'] + $val3['tandanper'];
			
			// $stream.="<tr class=rowcontent>
				// <td></td>
				// <td align='center'>".$key3."</td>
				// <td align='center'>".tanggalnormal($val3['tanggal'])."</td>
				// <td align='right'>".hidezerodecimal($val3['tbsmasuk'],0)."</td>
				// <td align='right'>".hidezerodecimal($val3['jmljjg'],0)."</td>
				// <td align='right'>".hidezerodecimal($val3['mentahkg'],0)."</td>
				// <td align='right'>".hidezerodecimal($val3['mentahper'],2)."</td>
				// <td align='right'>".hidezerodecimal($val3['mengkalkg'],0)."</td>
				// <td align='right'>".hidezerodecimal($val3['mengkalper'],2)."</td>
				// <td align='right'>".hidezerodecimal($val3['restankg'],0)."</td>
				// <td align='right'>".hidezerodecimal($val3['restanper'],2)."</td>
				// <td align='right'>".hidezerodecimal($val3['busukkg'],0)."</td>
				// <td align='right'>".hidezerodecimal($val3['busukper'],2)."</td>
				// <td align='right'>".hidezerodecimal($val3['panjangkg'],0)."</td>
				// <td align='right'>".hidezerodecimal($val3['panjangper'],2)."</td>
				// <td align='right'>".hidezerodecimal($val3['airkg'],0)."</td>
				// <td align='right'>".hidezerodecimal($val3['airper'],2)."</td>
				// <td align='right'>".hidezerodecimal($val3['sampahkg'],0)."</td>
				// <td align='right'>".hidezerodecimal($val3['sampahper'],2)."</td>
				// <td align='right'>".hidezerodecimal($val3['tandankg'],0)."</td>
				// <td align='right'>".hidezerodecimal($val3['tandanper'],0)."</td>
				// <td align='right'>".hidezerodecimal($totalgradkg,0)."</td>
				// <td align='right'>".hidezerodecimal($totalgradper,2)."</td>";
			// $stream.="</tr>";
			
			// $ttltbsmasuk = $ttltbsmasuk + $val3['tbsmasuk'];
			// $ttljmljjg = $ttljmljjg + $val3['jmljjg'];
			// $ttlmentahkg = $ttlmentahkg + $val3['mentahkg'];
			// $ttlmengkalkg = $ttlmengkalkg + $val3['mengkalkg'];
			// $ttlrestankg = $ttlrestankg + $val3['restankg'];
			// $ttlbusukkg = $ttlbusukkg + $val3['busukkg'];
			// $ttlpanjangkg = $ttlpanjangkg + $val3['panjangkg'];
			// $ttlairkg = $ttlairkg + $val3['airkg'];
			// $ttlsampahkg = $ttlsampahkg + $val3['sampahkg'];
			// $ttltandankg = $ttltandankg + $val3['tandankg'];
			// $ttlgradkg = $ttlgradkg + $totalgradkg;
			
			// $grdtbsmasuk = $grdtbsmasuk + $val3['tbsmasuk'];
			// $grdjmljjg = $grdjmljjg + $val3['jmljjg'];
			// $grdmentahkg = $grdmentahkg + $val3['mentahkg'];
			// $grdmengkalkg = $grdmengkalkg + $val3['mengkalkg'];
			// $grdrestankg = $grdrestankg + $val3['restankg'];
			// $grdbusukkg = $grdbusukkg + $val3['busukkg'];
			// $grdpanjangkg = $grdpanjangkg + $val3['panjangkg'];
			// $grdairkg = $grdairkg + $val3['airkg'];
			// $grdsampahkg = $grdsampahkg + $val3['sampahkg'];
			// $grdtandankg = $grdtandankg + $val3['tandankg'];
			// $grdgradkg = $grdgradkg + $totalgradkg;
		// }
		// $stream.="<tr class='rowcontent' style='font-weight:bold'>
			// <td colspan=3 style='text-align:right;'>Total ".getSupNameWb($val2)."</td>
			// <td style='text-align:right'>".hidezerodecimal($ttltbsmasuk,0)."</td>
			// <td style='text-align:right'>".hidezerodecimal($ttljmljjg,0)."</td>
			// <td style='text-align:right'>".hidezerodecimal($ttlmentahkg,0)."</td>
			// <td style='text-align:right'></td>
			// <td style='text-align:right'>".hidezerodecimal($ttlmengkalkg,0)."</td>
			// <td style='text-align:right'></td>
			// <td style='text-align:right'>".hidezerodecimal($ttlrestankg,0)."</td>
			// <td style='text-align:right'></td>
			// <td style='text-align:right'>".hidezerodecimal($ttlbusukkg,0)."</td>
			// <td style='text-align:right'></td>
			// <td style='text-align:right'>".hidezerodecimal($ttlpanjangkg,0)."</td>
			// <td style='text-align:right'></td>
			// <td style='text-align:right'>".hidezerodecimal($ttlairkg,0)."</td>
			// <td style='text-align:right'></td>
			// <td style='text-align:right'>".hidezerodecimal($ttlsampahkg,0)."</td>
			// <td style='text-align:right'></td>
			// <td style='text-align:right'>".hidezerodecimal($ttltandankg,0)."</td>
			// <td style='text-align:right'></td>
			// <td style='text-align:right'>".hidezerodecimal($ttlgradkg,0)."</td>
			// <td style='text-align:right'></td>
		// </tr>";
	// }
// }
// $stream.="<tr class='rowcontent' style='font-weight:bold'>
	// <td colspan=3 style='text-align:right;'>Grand Total</td>
	// <td style='text-align:right'>".hidezerodecimal($grdtbsmasuk,0)."</td>
	// <td style='text-align:right'>".hidezerodecimal($grdjmljjg,0)."</td>
	// <td style='text-align:right'>".hidezerodecimal($grdmentahkg,0)."</td>
	// <td style='text-align:right'></td>
	// <td style='text-align:right'>".hidezerodecimal($grdmengkalkg,0)."</td>
	// <td style='text-align:right'></td>
	// <td style='text-align:right'>".hidezerodecimal($grdrestankg,0)."</td>
	// <td style='text-align:right'></td>
	// <td style='text-align:right'>".hidezerodecimal($grdbusukkg,0)."</td>
	// <td style='text-align:right'></td>
	// <td style='text-align:right'>".hidezerodecimal($grdpanjangkg,0)."</td>
	// <td style='text-align:right'></td>
	// <td style='text-align:right'>".hidezerodecimal($grdairkg,0)."</td>
	// <td style='text-align:right'></td>
	// <td style='text-align:right'>".hidezerodecimal($grdsampahkg,0)."</td>
	// <td style='text-align:right'></td>
	// <td style='text-align:right'>".hidezerodecimal($grdtandankg,0)."</td>
	// <td style='text-align:right'></td>
	// <td style='text-align:right'>".hidezerodecimal($grdgradkg,0)."</td>
	// <td style='text-align:right'></td>
// </tr>";
// $stream.="</tbody></table><br>";
		
switch($proses)
{
    
    case'preview':
         echo $stream;
	break;
    
    ######EXCEL	
	case 'excel':
		$stream.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		$tglSkrg=date("Ymd");
		$nop_="laporan_sortasi_external";
		if(strlen($stream)>0)
		{
			if ($handle = opendir('tempFile')) {
				while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					@unlink('tempFile/'.$file);
				}
				}	
				closedir($handle);
			}
			$handle=fopen("tempFile/".$nop_.".xls",'w');
			if(!fwrite($handle,$stream))
			{
				echo "<script language=javascript1.2>
				parent.window.alert('Can't convert to excel format');
				</script>";
				exit;
			}
			else
			{
				echo "<script language=javascript1.2>
				window.location='tempFile/".$nop_.".xls';
				</script>";
			}
			fclose($handle);
		}           
		break;
                
	default:
}



?>