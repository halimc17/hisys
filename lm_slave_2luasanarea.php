<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
require_once('lib/zLib.php');	
require_once('dompdfv2/autoload.inc.php');
use Dompdf\Dompdf;

$method=checkPostGet('method','');
$tipeprint=checkPostGet('tipeprint','');

$pt=checkPostGet('pt','');
$unit=checkPostGet('unit','');
$divisi=checkPostGet('divisi','');
$periode=checkPostGet('periode','');


$arealNonProduktif = [
    'cadangan'     => 'Areal Cadangan',
    'arealberbatu' => 'Areal Berbatu',
    'konservasi'   => 'Areal Konservasi',
    'enclave'      => 'Enclave',
    'okupasi'      => 'Areal Okupasi',
    'rendahan'     => 'Areal Rendahan',
    'sungai'       => 'Sungai',
    'rumah'        => 'Perumahan',
    'kantor'       => 'Perkantoran',
    'pabrik'       => 'Pabrik',
    'jalan'        => 'Jalan',
    'kolam'        => 'Kolam',
    'umum'         => 'Fasilitas Umum'
];

$dataAreal = array();

// inisialisasi nilai awal
foreach ($arealNonProduktif as $field => $label) {
    $dataAreal[$field] = [
        'label' => $label,
        'value' => 0
    ];
}



switch($method){
	case'getUnit':
		$optunit="<option value='all'>".$_SESSION['lang']['all']."</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$pt."' and tipe = 'KEBUN' order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optunit;
	break;

	case'getDivisi':
		$optdivisi="<option value='all'>".$_SESSION['lang']['all']."</option>";
		$whereUnit = "";
		if($unit != 'all' && $unit != ''){
			$arrUnit = explode(',', $unit);
			$unitList = "";
			foreach($arrUnit as $u){
				if($unitList != "") $unitList .= ",";
				$unitList .= "'".$u."'";
			}
			if($unitList != ""){
				$whereUnit = " and induk in (".$unitList.")";
			}
		}

		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe = 'AFDELING' ".$whereUnit." order by kodeorganisasi";
		$res=fetchdata($str);
		foreach($res as $val){
			$optdivisi.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." - ".$val['namaorganisasi']."</option>";
		}
		
		echo $optdivisi;
	break;
	
	case'preview':
		$tab="";

		
		## AMBIL PENGELOMPOKAN INDUK KE UNIT
		$kodeKebun = array();
		$intiPlasma = array();
		$whereUnit = "";
		if($unit != 'all' && $unit != ''){
			$arrUnit = explode(',', $unit);
			$unitList = "";
			$allSelected = false;
			foreach($arrUnit as $u){
				if($u == 'all') {
					$allSelected = true;
					break;
				}
				if($unitList != "") $unitList .= ",";
				$unitList .= "'".$u."'";
			}
			if(!$allSelected && $unitList != ""){
				$whereUnit = " and kodeorganisasi in (".$unitList.")";
			}
		}
		
		$str = "select kodeorganisasi,inti from ".$dbname.".organisasi where induk ='".$pt."' and tipe = 'KEBUN' ".$whereUnit." order by kodeorganisasi";
		$res = fetchdata($str);
		foreach($res as $val){
			$kodeKebun[] = "'".$val['kodeorganisasi']."'";
			$intiPlasma[$val['kodeorganisasi']] = $val['inti'];
		}

		$whereInDivisi = "";
		if($divisi != 'all' && $divisi != ''){
			$arrDiv = explode(',', $divisi);
			$divList = "";
			$allDivSelected = false;
			foreach($arrDiv as $d){
				if($d == 'all') {
					$allDivSelected = true;
					break;
				}
				if($divList != "") $divList .= ",";
				$divList .= "'".$d."'";
			}
			if(!$allDivSelected && $divList != ""){
				$whereInDivisi = " and left(indukblok,6) in (".$divList.")";
			}
		}

		## AMBIL JENIS IZIN
		$str = "select * from ".$dbname.".lgl_5jenissertipikat ";
		$res = fetchdata($str);
		foreach($res as $val){
			$jenisIzin[$val['kode']] = $val['nama'];
		} 

		## AMBIL IZIN LOKASI DARI LEGAL
		$str = "select * from ".$dbname.".lgl_sertipikat where kodept ='".$pt."'";
		$res = fetchdata($str);
		foreach($res as $val){
			$luasIzinLokasi[$val['jenis']][$intiPlasma[$val['unit']]] += $val['luas'];
		}
		
		
		## CEK TABLE BLOK
		if(!empty($kodeKebun)){
			$kodeKebunIn = implode(',', $kodeKebun);
			$whereInKebun = "and left(indukblok,4) in (".$kodeKebunIn.") ".$whereInDivisi;
		}else{
			$whereInKebun = "and 1=0";
		}
		
		$periodex = str_replace('-', '', $periode);

		$str = "select * from ".$dbname.".setup_blok_tahunan where tahun ='".$periodex."' ".$whereInKebun." order by kodeorg";
		$res = fetchdata($str);
		$jumlahDataPeriode = count($res);

		if($jumlahDataPeriode > 0){
			$str = "select * from ".$dbname.".setup_blok_tahunan where tahun ='".$periodex."' ".$whereInKebun." order by tahuntanam";
			$res = fetchdata($str);

		}else{
			$str = "select * from ".$dbname.".setup_blok where 1=1 ".$whereInKebun." order by tahuntanam";
			$res = fetchdata($str);
		}

		foreach($res as $val){
			$listTahuntanam[$val['tahuntanam']] = $val['tahuntanam'] ;
			
			## LC
			if($val['intiplasma'] == 'P'){
				$totalLC['0'] += $val['LC'] ;
			}else{
				$totalLC['1'] += $val['LC'] ;
			}

			## Pembibitan
			if($val['statusblok'] == 'BBT'){
				if($val['intiplasma'] == 'P'){
					$totalBibitan['0'] += $val['luasareaproduktif'];
				}else{
					$totalBibitan['1'] += $val['luasareaproduktif'];
				}
			}

			## TBM
			if($val['statusblok'] == 'TBM'){
				if((substr($periode,0,4)-$val['tahuntanam'] + 1) <= 1){
					$TBM1[$val['tahuntanam']] += $val['luasareaproduktif'];

					if($val['intiplasma'] == 'P'){
						$TotalTbm1['0']+= $val['luasareaproduktif'];
					}else{
						$TotalTbm1['1']+= $val['luasareaproduktif'];
					}
					
				}elseif((substr($periode,0,4)-$val['tahuntanam'] + 1) == 2){
					$TBM2[$val['tahuntanam']] += $val['luasareaproduktif'];

					if($val['intiplasma'] == 'P'){
						$TotalTbm2['0']+= $val['luasareaproduktif'];
					}else{
						$TotalTbm2['1']+= $val['luasareaproduktif'];
					}
				}else{
					$TBM3[$val['tahuntanam']] += $val['luasareaproduktif'];

					if($val['intiplasma'] == 'P'){
						$TotalTbm3['0']+= $val['luasareaproduktif'];
					}else{
						$TotalTbm3['1']+= $val['luasareaproduktif'];
					}
				}
			}	

			if($val['statusblok'] == 'TM'){

				$listDivisi[substr($val['indukblok'],0,6)] = substr($val['indukblok'],0,6);
				$Totdivisi_tt[$val['tahuntanam']] +=$val['luasareaproduktif'];
				$Totdivisi_tt2[substr($val['kodeorg'],0,6)][$val['tahuntanam']] +=$val['luasareaproduktif'];

					if($val['intiplasma'] == 'P'){
						$Totdivisi[substr($val['indukblok'],0,6)]['0'] +=$val['luasareaproduktif'];
					}else{
						$Totdivisi[substr($val['indukblok'],0,6)]['1'] +=$val['luasareaproduktif'];
					}
			}

			 foreach ($arealNonProduktif as $field => $label) {
					if($val['intiplasma'] == 'P'){
						$dataAreal[$field]['value']['0'] += (float)($val[$field] ?? 0);
					}else{
						$dataAreal[$field]['value']['1'] += (float)($val[$field] ?? 0);
					}
			}
		}

		$ColspanTahuntanam = count($listTahuntanam); 
		$tab.="<table cellpadding=5 cellspacing=1 ".$border." class=sortable style='width:100%'>
			<thead>
			<tr class=rowheader style='text-align:center ;font-weight:bold'>
				<th rowspan='2'>".$_SESSION['lang']['nourut']."</th>
				<th rowspan='2'>".$_SESSION['lang']['deskripsi']."</th>
				<th colspan='3'>".$_SESSION['lang']['total']."</th>
				<th colspan='".$ColspanTahuntanam."'>".$_SESSION['lang']['tahuntanam']."</th>";
		$tab.="</tr>";
		$tab.="<tr class=rowheader style='text-align:center ;font-weight:bold'>";
			$tab.= "<th>Inti</th>";
			$tab.= "<th>Plasma</th>";
			$tab.= "<th>".$_SESSION['lang']['total']."</th>";

			foreach($listTahuntanam as $tahuntanam ){
				$tab.= "<th>".$tahuntanam."</th>";
			}

		$tab.="</tr>";
		$tab.="</thead><tbody>";
			$tab.="<tr class='rowcontent'>";
				$tab.="<td align=center><b>A</b></td>";
				$tab.="<td ><b>Izin Lokasi Area ( a )</b></td>";
				foreach($jenisIzin as $jenis => $nama){					
					$luasInti += $luasIzinLokasi[$jenis]['1'];
					$luasPlasma += $luasIzinLokasi[$jenis]['0'];
					$luasTotal += $luasIzinLokasi[$jenis]['1'] + $luasIzinLokasi[$jenis]['0'];
				}

				## INTI
				$tab.="<td align=right>".number_format($luasInti)."</td>";

				## PLASMA
				$tab.="<td align=right>".number_format($luasPlasma)."</td>";

				## TOTAL
				$tab.="<td align=right>".number_format($luasTotal)."</td>";
				
				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td align=center>-</td>";
				}
			$tab.="</tr>";
			## Izin Lokasi
			foreach($jenisIzin as $jenis => $nama){
				$tab.="<tr class='rowcontent'>";
					$tab.="<td ></td>";
					$tab.="<td ><b>".$nama."</b></td>";

					## INTI
					$tab.="<td align=right>".number_format($luasIzinLokasi[$jenis]['1'])."</td>";

					## PLASMA
					$tab.="<td align=right>".number_format($luasIzinLokasi[$jenis]['0'])."</td>";

					## TOTAL
					$tab.="<td align=right>".number_format(($luasIzinLokasi[$jenis]['1'] + $luasIzinLokasi[$jenis]['0'] ))."</td>";

					foreach($listTahuntanam as $tahuntanam ){
						$tab.="<td align=center>-</td>";
					}


				$tab.="</tr>";
			}

			$tab.="<tr class='rowcontent'>";
				$tab.="<td align=center><b>B</b></td>";
				$tab.="<td ><b>Areal Pembangunan Lain - lain ( b )</b></td>";
				foreach($totalLC as $tot => $value){
					$totalIntiLC += $totalLC['1'];
					$totalPlasmaLC += $totalLC['0'];
					$totalTotalLC += $totalLC['1'] + $totalLC['0'];
				}

				## INTI
				$tab.="<td align=right><b>".number_format($totalIntiLC)."</b></td>";

				## PLASMA
				$tab.="<td align=right><b>".number_format($totalPlasmaLC)."</b></td>";

				## TOTAL
				$tab.="<td align=right><b>".number_format($totalTotalLC)."</b></td>";

				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td align=center>-</td>";
				}

			$tab.="</tr>";
			$tab.="<tr class='rowcontent'>";
				$tab.="<td align=center><b></b></td>";
				$tab.="<td >Land Clearing</td>";
				$tab.="<td align=right>".number_format($totalLC['1'])."</td>";
				$tab.="<td align=right>".number_format($totalLC['0'])."</td>";
				$tab.="<td align=right>".number_format(($totalLC['1'] + $totalLC['0'] ))."</td>";
				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td align=center>-</td>";
				}

			$tab.="</tr>";
			$tab.="<tr class='rowcontent'>";
				$tab.="<td></td>";
				$tab.="<td>Pembibitan</td>";
				$tab.="<td align=right>".number_format($totalBibitan['1'])."</td>";
				$tab.="<td align=right>".number_format($totalBibitan['0'])."</td>";
				$tab.="<td align=right>".number_format(($totalBibitan['1'] + $totalBibitan['0'] ))."</td>";
				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td align=center>-</td>";
				}
			$tab.="</tr>";

			
			$tab.="<tr class='rowcontent'>";
				$tab.="<td align=center><b>C</b></td>";
				$tab.="<td ><b>AREA TERTANAM</b></td>";
				$tab.="<td colspan = '".($ColspanTahuntanam+3)."'></td>";
			$tab.="</tr>";
			$tab.="<tr class='rowcontent'>";
				$tab.="<td></td>";
				$tab.="<td >TBM 1</td>";

				$tab.="<td align=right>".number_format($TotalTbm1['1'])."</td>";
				$tab.="<td align=right>".number_format($TotalTbm1['0'])."</td>";
				$tab.="<td align=right>".number_format(($TotalTbm1['1'] + $TotalTbm1['0'] ))."</td>";

				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td align=right>".number_format($TBM1[$tahuntanam])."</td>";
				}
			$tab.="</tr>";
			$tab.="<tr class='rowcontent'>";
				$tab.="<td></td>";
				$tab.="<td >TBM 2</td>";

				$tab.="<td align=right>".number_format($TotalTbm2['1'])."</td>";
				$tab.="<td align=right>".number_format($TotalTbm2['0'])."</td>";
				$tab.="<td align=right>".number_format(($TotalTbm2['1'] + $TotalTbm2['0'] ))."</td>";

				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td align=right>".number_format($TBM2[$tahuntanam])."</td>";
				}
			$tab.="</tr>";
			$tab.="<tr class='rowcontent'>";
				$tab.="<td></td>";
				$tab.="<td >TBM 3</td>";

				$tab.="<td align=right>".number_format($TotalTbm3['1'])."</td>";
				$tab.="<td align=right>".number_format($TotalTbm3['0'],2)."</td>";
				$tab.="<td align=right>".number_format(($TotalTbm3['1'] + $TotalTbm3['0']),2)."</td>";

				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td align=right>".number_format($TBM3[$tahuntanam],2)."</td>";
				}
			$tab.="</tr>";
			$tab.="<tr class='rowcontent'>";
				$tab.="<td></td>";
				$tab.="<td align=center><b> Sub Total Tanaman Belum Menghasilkan</b></td>";
				$tab.="<td align=right><b>".number_format(($TotalTbm1['1'] + $TotalTbm2['1']+ $TotalTbm3['1'] ),2)."</b></td>";
				$tab.="<td align=right><b>".number_format(($TotalTbm1['0'] + $TotalTbm2['0']+ $TotalTbm3['0'] ),2)."</b></td>";
				$tab.="<td align=right><b>".number_format(($TotalTbm1['1'] + $TotalTbm2['1']+ $TotalTbm3['1'] + $TotalTbm1['0'] + $TotalTbm2['0']+ $TotalTbm3['0']),2)."</b></td>";

				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td align=right><b>".number_format($TBM1[$tahuntanam]+$TBM2[$tahuntanam]+$TBM3[$tahuntanam])."</b></td>";
				}
			$tab.="</tr>";
			
			foreach($listDivisi as $divisi){
				$tab.="<tr class='rowcontent'>";
				$tab.="<td></td>";
				$tab.="<td>".$divisi." - ".getNamaOrg($divisi)."</td>";
				$tab.="<td align=right>".number_format($Totdivisi[$divisi]['1'],2)."</td>";
				$tab.="<td align=right>".number_format($Totdivisi[$divisi]['0'],2)."</td>";
				$tab.="<td align=right>".number_format($Totdivisi[$divisi]['1'] + $Totdivisi[$divisi]['0'],2)."</td>";

				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td align=right>".number_format($Totdivisi_tt2[$divisi][$tahuntanam],2)."</td>";
				}

				$tab.="</tr>";
			}

			$tab.="<tr class='rowcontent'>";
				$tab.="<td></td>";
				$tab.="<td align=center><b>  Sub Total Tanaman Menghasilkan </b></td>";
				
				foreach($listDivisi as $divisi){
					$totalSB_Inti['1'] += $Totdivisi[$divisi]['1'] ;
					$totalSB_Plasma['0'] += $Totdivisi[$divisi]['0'];
				}

				$tab.="<td align=right><b>".number_format($totalSB_Inti['1'],2)."</b></td>";
				$tab.="<td align=right><b>".number_format($totalSB_Plasma['0'],2)."</b></td>";
				$tab.="<td align=right><b>".number_format($totalSB_Inti['1'] + $totalSB_Plasma['0'] ,2)."</b></td>";

				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td align=right><b>".number_format($Totdivisi_tt[$tahuntanam],2)."</b></td>";
				}
				
			$tab.="</tr>";

			$tab.="<tr class='rowcontent'>";
				$tab.="<td></td>";
				$tab.="<td align=center><b>   Total Area Tertanam ( c ) </b></td>";
				
				foreach($listDivisi as $divisi){
					$totalSBX_Inti['1'] += $Totdivisi[$divisi]['1'] ;
					$totalSBX_Plasma['0'] += $Totdivisi[$divisi]['0'];
				}

				$tab.="<td align=right><b>".number_format($totalSBX_Inti['1'] + $TotalTbm1['1'] + $TotalTbm2['1']+ $TotalTbm3['1'],2)."</b></td>";
				$tab.="<td align=right><b>".number_format($totalSBX_Plasma['0'] + $TotalTbm1['0'] + $TotalTbm2['0']+ $TotalTbm3['0'],2)."</b></td>";
				$tab.="<td align=right><b>".number_format($totalSBX_Inti['1'] + $totalSBX_Plasma['0'] + $TotalTbm1['1'] + $TotalTbm2['1']+ $TotalTbm3['1'] + $TotalTbm1['0'] + $TotalTbm2['0']+ $TotalTbm3['0'] ,2)."</b></td>";

				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td align=right><b>".number_format($Totdivisi_tt[$tahuntanam]+$TBM1[$tahuntanam]+$TBM2[$tahuntanam]+$TBM3[$tahuntanam],2)."</b></td>";
				}
				
			$tab.="</tr>";


			$tab.="<tr class='rowcontent'>";
				$tab.="<td align=center><b>D</b></td>";
				$tab.="<td ><b>AREA TIDAK TERTANAM</b></td>";
				$tab.="<td colspan = '".($ColspanTahuntanam+3)."'></td>";
			$tab.="</tr>";

			
			foreach ($arealNonProduktif as $field => $label) {
				$tab.="<tr class='rowcontent'>";
					$tab.="<td></td>";
					$tab .="<td>".$label."</td>";

					$nilai1 = $dataAreal[$field]['value']['1'] ?? 0;
					$nilai2 = $dataAreal[$field]['value']['0'] ?? 0;
					$tab .= "<td align='right'>".number_format($nilai1, 2)."</td>";
					$tab .= "<td align='right'>".number_format($nilai2, 2)."</td>";
					$tab .= "<td align='right'>".number_format($nilai1+$nilai2, 2)."</td>";
					foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td></td>";
				}
				$tab.="</tr>";
				
			}

			

			$tab.="<tr class='rowcontent'>";
				$tab.="<td></td>";
				$tab.="<td align=center><b>    Total Area Tidak Tertanam ( d ) </b></td>";

				foreach ($arealNonProduktif as $field => $label) {
					$nilai1X += $dataAreal[$field]['value']['1'] ?? 0;
					$nilai2X += $dataAreal[$field]['value']['0'] ?? 0;
				}

				$tab .= "<td align='right'>".number_format($nilai1X, 2)."</td>";
				$tab .= "<td align='right'>".number_format($nilai2X, 2)."</td>";
				$tab .= "<td align='right'>".number_format($nilai1X+$nilai2X, 2)."</td>";

				foreach($listTahuntanam as $tahuntanam ){
					$tab.="<td></td>";
				}
			$tab.="</tr>";







		$tab.="</tbody></table>";
	




		if($tipeprint=='html'){
			echo $tab;
		}else{
			$nop_="Laporan_LuasanArae_".$pt."_".$periode;
			if(strlen($tab)>0){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				 $handle=fopen("tempExcel/".$nop_.".xls",'w');
				 if(!fwrite($handle,$tab))
				 {
				  echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
				   exit;
				 }
				 else
				 {
				  echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
			}
		}
	break;
}


?>
