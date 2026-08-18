<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;
// error_reporting(0);

$method 	 = checkPostGet('method','');
$ymd 		 = checkPostGet('periode','');
$keBun 		 = checkPostGet('kebuN','');
$tipeprint	 = checkPostGet('tipeprint','');

if(tanggalsystemn($ymd)<"2021-01-01"){
	exit("warning : Tanggal mulai harus 01-01-2021");
}

switch ($method) {
	case 'preview':
	// 
	$periode=date("Y-m-d",strtotime($ymd));
	$hari=substr($periode,8,2);
	$bulan=substr($periode,5,2);
	$tahun=substr($periode,0,4);

	$days=cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);
	##GET RKAP 1 TAHUN
	$whr='';

		if($keBun!=''){
			$whr.=" and kodeorg LIKE '".$keBun."%'";
		}


	
		$str1="select substr(kodeorg,1,6) as kodeorg, sum(luasareaproduktif) as luas from ".$dbname.".setup_blok where 1=1  ".$whr." group by kodeorg"; 

		$res1=fetchdata($str1);
		$checkdata=count($res1);

		foreach ($res1 as $value) {
		@$arrdiv[$value['kodeorg']] =$value['kodeorg'];
		@$arrluas[$value['kodeorg']] +=$value['luas'];
		} 

		// echo"<pre>";
		// print_r($arrdiv);
		// print_r($arrluas);
		// echo"</pre>";
		// exit("error");

	$whr01='';

		if($keBun!=''){
			$whr01.=" and divisi LIKE '".$keBun."%'";
		}


	
		// $str01="select divisi, sum(kgsetahun) as kgsetahun from ".$dbname.".bgt_produksi_kbn_kg_vw where 1=1  ".$whr01." group by divisi"; 

		// $res01=fetchdata($str01);
		// $checkdata=count($res01);

		// foreach ($res01 as $value) {
	
		// @$arrkgsetahun[$value['divisi']]+=$value['kgsetahun'];
		// } 


		// echo "<pre>";
		// // print_r($arrluas);
		// print_r($arrkgsetahun);
		// // print_r($arrdiv);
		// echo "</pre>";
		// exit("error");
	##GET RKAP
		$whr2='';
		if($keBun!=''){
			$whr2.=" and  divisi LIKE '".$keBun."%'";
		}

		#get prd bgt
		if (intval($bulan) > 1) {
			$e="(";
			for($i=1;$i<=intval($bulan-1);$i++){
				$r="kg".addZero($i,2);
				if($i<intval($bulan-1)){$e.=$r."+";}else{$e.=$r;}
			}
			$e.=")";
		} else {
			$e='(kg01)';
		}
		
		

		$str2="select divisi, ".$hari." as hari ,".$e." as sdbi, kg".$bulan." as bi, kgsetahun from  ".$dbname.".bgt_produksi_kbn_kg_vw  where 1=1 ".$whr2." and tahunbudget = '".$tahun."'"; 
		
		// exit("Error: ".$e);
		$res2=fetchdata($str2);
		$numrows=owlBaris($res2);

		foreach ($res2 as $value2) {
			@$rkapbi[$value2['divisi']] +=$value2['bi'];
			$sdbitemp = ($value2['bi']/$days)*$hari;
			$sdbi= $sdbitemp + (($bulan != 01) ? $value2['sdbi'] : 0);
			@$arrkgsetahun[$value2['divisi']] += $value2['kgsetahun'];

			// @$rkaphi[$key] = $rkapbi[$key] / $days;
			// @$rkaphi[$value2['divisi']] = $rkapbi[$value2['divisi']] / $days;
			if (intval($bulan) > 1) {				
				$rkapsdbi[$value2['divisi']] += $value2['sdbi'];
			}else{
				$rkapsdbi[$value2['divisi']] = 0;
			}
		} 


	## GET 'REAL H.I'
	$whr3='';
		if($keBun!=''){
			$whr3.=" and divcode LIKE '".$keBun."%'";
		}

		if($periode!=''){
			$whr3.=" and left(tanggal,10)  = '".$periode."'";
		} 
		$str3="select sum(beratbersih) as beratbersih,divcode,left(tanggal,10) as tanggal from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$whr3." group by divcode, left(tanggal,10)  ";
		$res3=$owlPDO->query($str3) or die(print " Gagal: ".PDOException::getMessage());
		$res3->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res3);
		// exit();

		foreach ($res3 as $value3) {
			@$realhi[$value3['divcode']][$value3['tanggal']]+=$value3['beratbersih'];
			@$tgl[$value3['divcode']] =$value3['tanggal'];
		} 
	## GET 'REAL B.I'	
	$whr4='';
		if($keBun!=''){
			$whr4.=" and divcode LIKE '".$keBun."%'";
		}

		if($periode!=''){
			$whr4.=" and left(tanggal,10) between '".$tahun."-".$bulan."-01' and '".$periode."'";
		} 
		
		
		$str4="select sum(beratbersih) as beratbersih,divcode,left(tanggal,7) as tanggal from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$whr4." group by divcode, left(tanggal,7)  ";
		$res4=$owlPDO->query($str4) or die(print " Gagal: ".PDOException::getMessage());
		$res4->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res4);

		foreach ($res4 as $value4) {
			@$realbi[$value4['divcode']][$value4['tanggal']] =$value4['beratbersih'];
			@$blntgl[$value4['divcode']] =$value4['tanggal'];
		}
	##GET REALBULAN LALU
	$whr41='';
		if($keBun!=''){
			$whr41.=" and divcode LIKE '".$keBun."%'";
		}

		$bulan41=substr($periode,0,7);

		$blalu1=date("Y-m",strtotime("-1 Month",strtotime($bulan41)));


		if($periode!=''){
			$whr41.=" and left(tanggal,7)  = '".$blalu1."'";
		} 
		$str41="select sum(beratbersih) as beratbersih,divcode,left(tanggal,7) as tanggal from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$whr41." group by divcode";
		$res41=$owlPDO->query($str41) or die(print " Gagal: ".PDOException::getMessage());
		$res41->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res41);

		foreach ($res41 as $value41) {
		@$realblnlalu[$value41['divcode']] =$value41['beratbersih'];

		}
				
	#GET REAL SDBI
	$whr5='';
		if($keBun!=''){
			$whr5.=" and divcode LIKE '".$keBun."%'";
		}

		if($periode!=''){
			$whr5.=" and left(tanggal,10) BETWEEN '".$tahun."-01-01' AND '".$periode."'";
		
		} 

		$str5="select sum(beratbersih) as beratbersih,divcode,left(tanggal,10) as tanggal from ".$dbname.".pabrik_timbangan where kodebarang='40000003' ".$whr5." group by divcode";
		$res5=$owlPDO->query($str5) or die(print " Gagal: ".PDOException::getMessage());
		$res5->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res5);

		foreach ($res5 as $value5) {
		@$realsdbi[$value5['divcode']] +=$value5['beratbersih'];
		// @$realsdbi12[$value5['divcode']][$value5['tanggal']] +=$value5['beratbersih'];
		@$thntgl[$value5['divcode']] =$value5['tanggal'];

		}
		// echo "<pre>";
		// print_r($realsdbi);
		// echo "</pre>";
	
	#HITUNG TENAGA PANEN

	// $whr6='';
	// 	if($keBun!=''){
	// 		$whr6.=" and divisi LIKE '".$keBun."%'";
	// 	}


	// 	if($periode!=''){
	// 		$whr6.=" and tanggal BETWEEN '".$tahun."-01-01' AND '".$periode."'";
		
	// 	}

	// 	$str6="select tenagakerja ,divisi from ".$dbname.".kebun_rekappnn where 1=1 ".$whr6." ";
	// 	$res6=$owlPDO->query($str6) or die(print " Gagal: ".PDOException::getMessage());
	// 	$res6->setFetchMode(PDO::FETCH_ASSOC);
	// 	$numrows=owlBaris($res6);

	// 	foreach ($res6 as $value6) {
	// 		@$tngpanen[$value6['divisi']] +=$value6['tenagakerja'];

	// 	}
	$whr06='';
		if($keBun!=''){
			$whr06.=" and divisi LIKE '".$keBun."%'";
		}


		if($periode!=''){
			$whr06.=" and left(tanggal,10) = '".$periode."'";
		
		}

		$str06="select sum(tenagakerja) as tenagakerja ,divisi from ".$dbname.".kebun_rekappnn where 1=1 ".$whr06." group by divisi";
		$res06=$owlPDO->query($str06) or die(print " Gagal: ".PDOException::getMessage());
		$res06->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res06);

		foreach ($res06 as $value06) {
			@$tngpanenhi[$value06['divisi']] +=$value06['tenagakerja'];

		}
	$whr016='';
		if($keBun!=''){
			$whr016.=" and divisi LIKE '".$keBun."%'";
		}

		$bulan016=substr($periode,0,7);

		if($periode!=''){
			//$whr016.=" and left(tanggal,7)  = '".$bulan016."'";
			
			$whr016.=" and left(tanggal,10) between '".$tahun."-".$bulan."-01' and '".$periode."'";
		} 

		$str016="select sum(tenagakerja) as tenagakerja,divisi from ".$dbname.".kebun_rekappnn where 1=1 ".$whr016." group by divisi";
		$res016=$owlPDO->query($str016) or die(print " Gagal: ".PDOException::getMessage());
		$res016->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res016);

		foreach ($res016 as $value016) {
			@$tngpanenbi[$value016['divisi']] +=$value016['tenagakerja'];

		}

	$whr026='';
		if($keBun!=''){
			$whr026.=" and divisi LIKE '".$keBun."%'";
		}

		$bulan026=substr($periode,0,7);

		$blalu3=date("Y-m",strtotime("-1 Month",strtotime($bulan026)));


		if($periode!=''){
			$whr026.=" and left(tanggal,7)  = '".$blalu3."'";
		} 

		$str026="select sum(tenagakerja) as tenagakerja ,substr(tanggal,6,2) as tgl026, divisi from ".$dbname.".kebun_rekappnn where 1=1 ".$whr026." group by divisi";
		$res026=$owlPDO->query($str026) or die(print " Gagal: ".PDOException::getMessage());
		$res026->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res026);

		foreach ($res026 as $value026) {
			@$tngpanenblnlalu[$value026['divisi']]  +=$value026['tenagakerja'];
		}	

		// echo "<pre>";
		// print_r($tngpanenblnlalu);
		// echo "</pre>";
		// exit("error:");
	$whr7='';
		if($keBun!=''){
			$whr7.=" and divisi LIKE '".$keBun."%'";
		}

		if($periode!=''){
			$whr7.=" and left(tanggal,10)  = '".$periode."'";
		} 

		$str7="select sum(tenagakerja) as tenagakerja,substr(tanggal,9,2) as tgl7, divisi from ".$dbname.".kebun_rekappnn where 1=1 ".$whr7." group by divisi, substr(tanggal,9,2)";
		$res7=$owlPDO->query($str7) or die(print " Gagal: ".PDOException::getMessage());
		$res7->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res7);

		foreach ($res7 as $value7) {
			@$ttngpanen[$value7['divisi']][$value7['tgl7']]  +=$value7['tenagakerja'];
		}
	#HITUNG JUMLAH TENAGA PANEN

	$whr8='';
		if($keBun!=''){
			$whr8.=" and divisi LIKE '".$keBun."%'";
		}
		$bulan8=substr($periode,0,7);

		if($periode!=''){
			$whr8.=" and left(tanggal,7)  = '".$bulan8."'";
		} 

		$str8="select sum(tenagakerja) as tenagakerja,substr(tanggal,6,2) as tgl8, divisi from ".$dbname.".kebun_rekappnn where 1=1 ".$whr8." group by divisi, substr(tanggal,6,2)";
		$res8=$owlPDO->query($str8) or die(print " Gagal: ".PDOException::getMessage());
		$res8->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res8);

		foreach ($res8 as $value8) {
			@$ttngpanenbln[$value8['divisi']][$value8['tgl8']]  +=$value8['tenagakerja'];
		}

		// echo"<pre>";
		// // print_r($realbi)."<br>";
		// print_r($ttngpanenbln)."<br>";
		// echo"</pre>";


	$whr81='';
		if($keBun!=''){
			$whr81.=" and divisi LIKE '".$keBun."%'";
		}

		$bulan81=substr($periode,0,7);

		$blalu2=date("Y-m",strtotime("-1 Month",strtotime($bulan81)));


		if($periode!=''){
			$whr81.=" and left(tanggal,7)  = '".$blalu2."'";
		} 

		$str81="select sum(tenagakerja) as tenagakerja ,substr(tanggal,6,2) as tgl81, divisi from ".$dbname.".kebun_rekappnn where 1=1 ".$whr81." group by divisi";
		$res81=$owlPDO->query($str81) or die(print " Gagal: ".PDOException::getMessage());
		$res81->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res81);

		foreach ($res81 as $value81) {
			@$tpnnblnlalu[$value81['divisi']]  +=$value81['tenagakerja'];
		}


	$whr9='';
		if($keBun!=''){
			$whr9.=" and divisi LIKE '".$keBun."%'";
		}

		if($periode!=''){
			$whr9.=" and left(tanggal,10)  = '".$periode."'";
		} 

		$str9="select sum(brondolan) as brondolan,substr(tanggal,9,2) as tgl9, divisi from ".$dbname.".kebun_spb_vw where 1=1 ".$whr9." group by divisi, substr(tanggal,9,2)";
		$res9=$owlPDO->query($str9) or die(print " Gagal: ".PDOException::getMessage());
		$res9->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res9);

		foreach ($res9 as $value9) {
			@$brondolhi[$value9['divisi']][$value9['tgl9']]  +=$value9['brondolan'];
			@$tgl9[$value9['divisi']][$value9['tgl9']] =$value9['tgl9'];
		}

	$whr10='';
		if($keBun!=''){
			$whr10.=" and divisi LIKE '".$keBun."%'";
		}
		
		$whr10.=" and left(tanggal,10) between '".$tahun."-".$bulan."-01' and '".$periode."'";
		
		$str10="select sum(luaspanen) as luaspanen, divisi from ".$dbname.".kebun_rekappnn where 1=1 ".$whr10." group by divisi";
		$res10=$owlPDO->query($str10) or die(print " Gagal: ".PDOException::getMessage());
		$res10->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res10);

		foreach ($res10 as $value10) {
			@$luaspanen[$value10['divisi']]  +=$value10['luaspanen'];		
		}

	$whr11='';
		if($keBun!=''){
			$whr11.=" and divisi LIKE '".$keBun."%'";
		}
		$bulan11=substr($periode,0,7);

		if($periode!=''){
			$whr11.=" and tanggal BETWEEN '".$tahun."-01-01' AND '".$periode."'";
		
		} 

		$str11="select  sum(brondolan) as brondol, divisi,tanggal from kebun_spb_vw where 1=1 ".$whr11." group by divisi";
		$res11=$owlPDO->query($str11) or die(print " Gagal: ".PDOException::getMessage());
		$res11->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res11);

		foreach ($res11 as $value11) {
			@$brondolsdbi[$value11['divisi']] =$value11['brondol'];	
		}


	$whr12='';
		if($keBun!=''){
			$whr12.=" and divisi LIKE '".$keBun."%'";
		}

		if($periode!=''){
			$whr12.=" and tanggal BETWEEN '2021-01-01' AND '".$periode."'";
		
		}

		$str12="select  sum(jjgpanen-jjgafkir) as jjgpanen, divisi from kebun_rekappnn where 1=1 ".$whr12." group by divisi";
		$res12=$owlPDO->query($str12) or die(print " Gagal: ".PDOException::getMessage());
		$res12->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res12);

		foreach ($res12 as $value12) {
			@$jjgpanen[$value12['divisi']] =$value12['jjgpanen'];		
		}

	$whr13='';
		if($keBun!=''){
			$whr13.=" and divisi LIKE '".$keBun."%'";
		}

		if($periode!=''){
			$whr13.=" and tanggal BETWEEN '2021-01-01' AND '".$periode."'";
		
		}
		$str13="select  sum(jjg) as jjgkirim, divisi from kebun_spb_vw  where 1=1 ".$whr13." group by divisi";
		$res13=$owlPDO->query($str13) or die(print " Gagal: ".PDOException::getMessage());
		$res13->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res13);

		foreach ($res13 as $value13) {	
			@$jjgkirim[$value13['divisi']] =$value13['jjgkirim'];			
		}

	#KAPASITAS BULAN LALU
	$whr14='';
		if($keBun!=''){
			$whr14.=" and divisi LIKE '".$keBun."%'";
		}

		$bulan14=substr($periode,0,7);

		if($periode!=''){
			$whr14.=" and left(tanggal,7)  < '".$bulan14."'";
		
		}  
		$str14="select sum(tenagakerja) as tenagakerja, substr(tanggal,6,2) as tgl14, divisi from ".$dbname.".kebun_rekappnn where 1=1 ".$whr14." group by substr(tanggal,6,2), divisi";
		$res14=$owlPDO->query($str14) or die(print " Gagal: ".PDOException::getMessage());
		$res14->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res14);

		foreach ($res14 as $value14) {
			@$ttlkpblalu[$value14['divisi']][$value14['tgl14']]  +=$value14['tenagakerja'];
		}

	#CURAH HUJAN

	$whr15='';
		if($keBun!=''){
			$whr15.=" and kodeorg LIKE '".$keBun."%'";
		}

		
		if($periode!=''){
			$whr15.=" and left(tanggal,10)  = '".$periode."'";
		
		}  
		$str15="select  sum(pagi) as curahpagi, sum(sore) as curahsore, sum(malam) as curahmalam, kodeorg,tanggal from ".$dbname.".kebun_curahhujan where 1=1 ".$whr15." group by kodeorg, tanggal";
		$res15=$owlPDO->query($str15) or die(print " Gagal: ".PDOException::getMessage());
		$res15->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res15);

		foreach ($res15 as $value15) {
			@$curahhari[$value15['kodeorg']][$value15['tanggal']] =$value15['curahpagi']+$value15['curahsore']+$value15['curahmalam'];
		}
	$whr16='';
		if($keBun!=''){
			$whr16.=" and kodeorg LIKE '".$keBun."%'";
		}

		$bulan16=substr($periode,0,7);

		if($periode!=''){
			$whr16.=" and left(tanggal,7)  = '".$bulan16."'";
		}
	
		  
		$str16="select  sum(pagi) as curahpagi, sum(sore) as curahsore, 
		sum(malam) as curahmalam, kodeorg,substr(tanggal,6,2) as tgl16
		from ".$dbname.".kebun_curahhujan where 1=1 ".$whr16." group by kodeorg, substr(tanggal,6,2)";
		$res16=$owlPDO->query($str16) or die(print " Gagal: ".PDOException::getMessage());
		$res16->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res16);

		foreach ($res16 as $value16) {
			@$curahbln[$value16['kodeorg']][$value16['tgl16']] =$value16['curahpagi']+$value16['curahsore']+$value16['curahmalam'];
		}


	$whr17='';
		if($keBun!=''){
			$whr17.=" and kodeorg LIKE '".$keBun."%'";
		}

		$tahun17=substr($periode,0,4);

		if($periode!=''){
			$whr17.=" and left(tanggal,4)  = '".$tahun17."'";
		}
	
		
		$str17="select  sum(pagi) as curahpagi, sum(sore) as curahsore, 
		sum(malam) as curahmalam, kodeorg,substr(tanggal,1,4) as tgl17
		from ".$dbname.".kebun_curahhujan where 1=1 ".$whr17." group by kodeorg, substr(tanggal,1,4)";
		$res17=$owlPDO->query($str17) or die(print " Gagal: ".PDOException::getMessage());
		$res17->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res17);

		foreach ($res17 as $value17) {
			@$curahsdbi[$value17['kodeorg']][$value17['tgl17']] =$value17['curahpagi']+$value17['curahsore']+$value17['curahmalam'];
		}
	$whr18='';
		if($keBun!=''){
			$whr18.=" and kodeorg LIKE '".$keBun."%'";
		}

		

		if($periode!=''){
			$whr18.=" and left(tanggal,10)  = '".$periode."'";
		}
	
		
		$str18="select  sum(pagi) as curahpagi, kodeorg,tanggal
		from ".$dbname.".kebun_curahhujan where 1=1 ".$whr18." group by kodeorg";
		$res18=$owlPDO->query($str18) or die(print " Gagal: ".PDOException::getMessage());
		$res18->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlBaris($res18);

		foreach ($res18 as $value18) {
			@$cuacapagi[$value18['kodeorg']][$value18['tanggal']] =$value18['curahpagi'];
		}	


		$tab = '';
		$rowspan = '';
		
		if ($tipeprint=='excel') {
			$tab.= "<table border=1 class=sortable cellpadding=0 cellspacing=1>";
		}else{
			$tab.= "<table border=0 class=sortable cellpadding=5 cellspacing=1>";
		}
		//ROW KE-1
		$tab.= "<thead><tr class=rowheader >";
        $tab.= "<th align=center rowspan=3 >".$_SESSION['lang']['nourut']."</th>";
        $tab.= "<th align=center rowspan=3 >".$_SESSION['lang']['unit']."</th>";
        $tab.= "<th align=center rowspan=3 >".$_SESSION['lang']['luas']."</th>";
        $tab.= "<th align=center rowspan=1 colspan=2>".$_SESSION['lang']['budget']." 1 ".$_SESSION['lang']['tahun']."</th>";
        $tab.= "<th align=center colspan=4 >".$_SESSION['lang']['budget']."</th>";
        $tab.= "<th align=center colspan=4 >".$_SESSION['lang']['aktual']."</th>";
        $tab.= "<th align=center colspan=3 >% ".$_SESSION['lang']['budget']."</th>";
        $tab.= "<th align=center rowspan=2 colspan=4 >Tenaga Panen</th>";
        $tab.= "<th align=center rowspan=2 colspan=3>Kapasitas Panen</th>";
        $tab.= "<th align=center rowspan=2 colspan=3>Kg Brondol</th>";
        $tab.= "<th align=center rowspan=2 colspan=2>% Brond</th>";
        $tab.= "<th align=center rowspan=3>Ha Panen</th>";
        $tab.= "<th align=center rowspan=3>".$_SESSION['lang']['rotasi']."</th>";
        $tab.= "<th align=center rowspan=2 colspan=2>Jumlah Restan</th>";
        $tab.= "<th align=center colspan=4 >Cuaca </th>";
		$tab.="</tr>";
		//ROW KE-2
		$tab.="<tr>";
		$tab.= "<th align=center rowspan=2>KG</th>";
		$tab.= "<th align=center rowspan=2>KG/Ha</th>";

		$tab.= "<th align=center >H.I</th>";
		$tab.= "<th align=center >B.I</th>";
		$tab.= "<th align=center colspan=2>SD B.I</th>";

		$tab.= "<th align=center >H.I</th>";
		$tab.= "<th align=center >B.I</th>";
		$tab.= "<th align=center colspan=2>SD B.I</th>";

		$tab.= "<th align=center rowspan=2>H.I</th>";
		$tab.= "<th align=center rowspan=2>B.I</th>";
		$tab.= "<th align=center rowspan=2>SD B.I</th>";
		$tab.= "<th align=center colspan=3>Curah Hujan (mm)</th>";
		$tab.= "<th align=center rowspan=2>(Pagi)</th>";
		$tab.="</tr>";

		//ROW KE-3
		$tab.="<tr>";
		$tab.= "<th align=center >KG </th>";
		$tab.= "<th align=center >KG </th>";
		$tab.= "<th align=center >KG </th>";
		$tab.= "<th align=center >KG/HA </th>";

		$tab.= "<th align=center >KG </th>";
		$tab.= "<th align=center >KG </th>";
		$tab.= "<th align=center >KG </th>";
		$tab.= "<th align=center  >KG/HA </th>";
		$tab.= "<th align=center  >H.I </th>";
		$tab.= "<th align=center  >B.I </th>";
		$tab.= "<th align=center colspan=2 >B.Lalu </th>";

		$tab.= "<th align=center >H.I </th>";
		$tab.= "<th align=center >B.I </th>";
		$tab.= "<th align=center >B.Lalu </th>";

		$tab.= "<th align=center >H.I </th>";
		$tab.= "<th align=center colspan=2 >SD B.I </th>";

		$tab.= "<th align=center >H.I </th>";
		$tab.= "<th align=center >SD B.I </th>";
		
		$tab.= "<th align=center >Jjg</th>";
		$tab.= "<th align=center >Kg</th>";
		
		$tab.= "<th align=center >H.I</th>";
		$tab.= "<th align=center >B.I</th>";
		$tab.= "<th align=center >SD B.I </th>";



		$tab.="</tr>";


		$tab.= "</thead>";
		$tab.= "<tbody id=container>";
		// exit();
		
		$str = "select sum(kgwb) as kgwb, sum(jjg) as jjg, substr(blok,1,6) as divisi from " . $dbname . ".kebun_spbdt b
		left join " . $dbname . ".kebun_spbht a on a.nospb=b.nospb
		where 1=1 and blok like '".$keBun."%' and substr(a.tanggal,1,7) like '".periodelalu($tahun."-".$bulan)."' group by divisi"; 
		$res = fetchdata($str);
		foreach($res as $bar){
			$bjr[$bar['divisi']]=$bar['kgwb']/$bar['jjg'];
		}
		
		
		

		if($checkdata>0){
			foreach(@$arrdiv as $key => $arrunit){

				$no++;	
				$tab.="<tr class=rowcontent id=baris".$no.">";
				$tab.="<td align=center >".@$no."</td>";					
				$tab.="<td align=center >".@$key."</td>";
				@$tluas+=$arrluas[$key];
				$tab.="<td align=center >".number_format(@$arrluas[$key])."</td>";

				@$tkgsthn+=@$arrkgsetahun[$key];
				$tab.="<td align=right >".number_format(@$arrkgsetahun[$key])."</td>"; #RKAP 1 TAHUN

				@$kgha[$key]=$arrkgsetahun[$key]/$arrluas[$key];

				@$tkgha=$tkgsthn/$tluas;

				$tab.="<td align=right >".number_format(fixnan(@$kgha[$key]))."</td>";  
				@$rkaphi[$key]=$rkapbi[$key]/$days;
				@$trkaphi+=$rkaphi[$key];
				$tab.="<td align=right >".number_format(fixnan(@$rkaphi[$key]))."</td>"; #RKAP

				@$trkapbi+=$rkaphi[$key]*$hari;
				$rkapbi2[$key]=$rkaphi[$key]*$hari;
				$tab.="<td align=right >".number_format(@$rkapbi2[$key])."</td>"; 

				@$trkapsdbi+=$rkapsdbi[$key]+$rkapbi2[$key];
				$tab.="<td align=right >".number_format(@$rkapsdbi[$key]+$rkapbi2[$key])."</td>";  #RKAP SDBI

				@$kgharap[$key]=($rkapsdbi[$key]+$rkapbi2[$key])/$arrluas[$key];

				@$tkgharap=$trkapsdbi/$tluas;
				$tab.="<td align=right >".number_format(fixnan(@$kgharap[$key]))."</td>";  

				@$trealhi+=$realhi[$key][$tgl[$key]];
				$tab.="<td align=right >".number_format(@$realhi[$key][$tgl[$key]])."</td>"; #REAL

				@$trealbi+=$realbi[$key][$blntgl[$key]];
				$tab.="<td align=right >".number_format(@$realbi[$key][$blntgl[$key]])."</td>"; 

				@$trealsdbi+=$realsdbi[$key];
				$tab.="<td align=right >".number_format(@$realsdbi[$key])."</td>";  

				$kghareal[$key]=$realsdbi[$key]/$arrluas[$key];
				@$tkghareal=$trealsdbi/$tluas;
				$tab.="<td align=right >".number_format(fixnan(@$kghareal[$key]))."</td>";  


				#PENGURANGAN %RKAP
				@$prkaphi[$key][$hari]=$realhi[$key][$tgl[$key]]-$rkaphi[$key];
				@$prkapbi[$key][$bulan]=$realbi[$key][$blntgl[$key]]-$rkapbi[$key];
				@$prkapsdbi[$key]=$realsdbi[$key]-$rkapsdbi[$key];


				#%RKAP
				$k1=$trealhi-$trkaphi;
				$k2=$trealbi-$trkapbi;
				$k3=$trealsdbi-$trkapsdbi;
				@$tprkaphi=($k1/$trkaphi)*100;
				$tab.="<td align=right >".@number_format(fixnan((@$realhi[$key][$tgl[$key]]/$rkaphi[$key])*100),2)."</td>"; #%RKAP hi
		
				@$tprkapbi=($k2/$trkapbi)*100;
				$tab.="<td align=right >".@number_format(fixnan((@$realbi[$key][$blntgl[$key]]/$rkapbi2[$key])*100),2)."</td>";  #%RKAP bi

				@$tprkapsdbi=($k3/$trkapsdbi)*100;
				$tab.="<td align=right >".@number_format(fixnan((@$realsdbi[$key]/($rkapsdbi[$key]+$rkapbi2[$key]))*100),2)."</td>"; #%RKAP sdbi  

				@$tTngpanenhi+=$tngpanenhi[$key];
				@$tTngpanenbi+=$tngpanenbi[$key];
				@$tTngapanenblnlalu+=$tngpanenblnlalu[$key];
				$tab.="<td align=right>".number_format(@$tngpanenhi[$key])." </td>"; #TENAGA PANEN REAL hi
				$tab.="<td align=right>".number_format(@$tngpanenbi[$key])." </td>"; #TENAGA PANEN REAL bi
				$tab.="<td align=right colspan=2>".number_format(@$tngpanenblnlalu[$key])." </td>"; #TENAGA PANEN REAL bulanlalu



				@$kphi[$key][$hari]=$realhi[$key][$periode]/$tngpanenhi[$key];
				@$tkphi+=fixnan($kphi[$key][$hari]);
				$tab.="<td align=right >".number_format(fixnan(@$kphi[$key][$hari]))."</td>"; #KAPASTIAS PANEN hi
			
				$prdpanen=substr($periode, 0,7);
				@$kpbi[$key][$bulan]=$realbi[$key][$prdpanen]/$tngpanenbi[$key];
				@$tkkpbi+=fixnan($kpbi[$key][$bulan]);
				$tab.="<td align=right >".number_format(fixnan(@$kpbi[$key][$bulan]))."</td>"; #KAPASTIAS PANEN bi
			
				@$tkkpbnlalu+=fixnan($realblnlalu[$key]/$tpnnblnlalu[$key]);
				@$prdlalu= date("Y-m",strtotime("-1 Month",strtotime($prdpanen)));
				$tab.="<td align=right >".@number_format(fixnan(@$realblnlalu[$key]/$tngpanenblnlalu[$key]))."</td>"; #KAPASTIAS PANEN bln lalu

				#KG BRONDOL
				@$tbrondolhi+=$brondolhi[$key][$hari];
				@$tbrondolsdbi+=$brondolsdbi[$key];

				$tab.="<td align=right >".number_format(@$brondolhi[$key][$hari])."</td>"; #KG BRONDOLAN hi
				$tab.="<td align=right colspan=2>".number_format(@$brondolsdbi[$key])." </td>"; #KG BRONDOLAN SDBI



				#%BRONDOL 
				// $brondolprhi=@number_format($brondolhi[$key][$hari]/$realhi[$key][$tgl[$key]]*100,3);
				// $brondolprsdbi=@number_format(@$brondolsdbi[$key]/$realsdbi[$key]*100,3);
				$brhi=0;
				if (@$brondolhi[$key][$hari]!=0)  {
				@$bhi= @$brondolhi[$key][$hari]/@$realhi[$key][$tgl[$key]]*100; 

				}

				@$tprbrondolhi+=($tbrondolhi/$trealhi)*100;
				$tab.="<td align=right >".number_format(fixnan(@$bhi),2)." </td>"; #%HI


				$brsdbi=0;
				if (@$brondolsdbi[$key]!=0)  {
				$brsdbi= @$brondolsdbi[$key]/@$realsdbi[$key]*100; 

				}
				@$tprbrondolsdbi+=($tbrondolsdbi/$trealsdbi)*100;
				$tab.="<td align=right>".number_format(fixnan($brsdbi),2)."  </td>"; #%SDBI
				@$tluaspanen+=$luaspanen[$key];
				$tab.="<td align=right>".number_format(@$luaspanen[$key])."  </td>"; #Ha Panen

				@$ttlrot+=$luaspanen[$key]/@$arrluas[$key]; 
				$tab.="<td align=right >".number_format(@$luaspanen[$key]/@$arrluas[$key],2)."</td>"; #ROTASI
	
				@$tjlhrestan+=$jjgpanen[$key]-@$jjgkirim[$key]; 	
				@$tjlhrestankg+=round(($jjgpanen[$key]-@$jjgkirim[$key])*$bjr[$key],-1); 	
				$tab.="<td align=right >".number_format(@$jjgpanen[$key]-@$jjgkirim[$key])."</td>"; #JUMLAH RESTAN
				$tab.="<td align=right >".number_format(round(($jjgpanen[$key]-@$jjgkirim[$key])*$bjr[$key],-1))."</td>"; #JUMLAH RESTAN
				

				#CUACA
				@$tcurahhari+=$curahhari[$key][$periode];
				@$tcurahbln+=$curahbln[$key][$bulan];
				@$tcurahsdbi+=$curahsdbi[$key][$tahun];
				@$tcuacapagi+=$cuacapagi[$key][$periode];
				$tab.="<td align=right >".number_format(@$curahhari[$key][$periode])."</td>"; #CURAH HUJAN H.I
				$tab.="<td align=right >".number_format(@$curahbln[$key][$bulan])."</td>"; #CURAH HUJAN B.I
				$tab.="<td align=right >".number_format(@$curahsdbi[$key][$tahun])."</td>"; #CURAH HUJAN SD.BI
				$tab.="<td align=right >".number_format(@$cuacapagi[$key][$periode])."</td>"; #Cuaca pagi
				$tab.="</tr>";

			
			}
				// echo "<pre>";
				// print_r($trkaphi)."<br>";
				// print_r($trkapbi)."<br>";
				// print_r($trkapsdbi)."<br>";
				// echo "</pre>";

			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center colspan=2>Total</td>"; 
			// $tab.="<td align=right hidden > </td>";
			$tab.="<td align=right >".number_format(@$tluas)."</td>";
			$tab.="<td align=right >".number_format(@$tkgsthn)."</td>";
			$tab.="<td align=right >".number_format(@$tkgha)."</td>";
			$tab.="<td align=right >".number_format(@$trkaphi)."</td>";
			$tab.="<td align=right >".number_format(@$trkapbi)."</td>";
			$tab.="<td align=right >".number_format(@$trkapsdbi)."</td>";
			$tab.="<td align=right >".number_format(@$tkgharap)."</td>";
			$tab.="<td align=right >".number_format(@$trealhi)."</td>";
			$tab.="<td align=right >".number_format(@$trealbi)."</td>";
			$tab.="<td align=right >".number_format(@$trealsdbi)."</td>";
			$tab.="<td align=right >".number_format(fixnan(@$tkghareal))."</td>";
			$tab.="<td align=right >".number_format(fixnan(@$trealhi/$trkaphi)*100,2)." </td>";
			$tab.="<td align=right >".number_format(fixnan(@$trealbi/$trkapbi)*100,2)." </td>";
			$tab.="<td align=right >".number_format(fixnan(@$trealsdbi/$trkapsdbi)*100,2)." </td>";
			$tab.="<td align=right >".number_format(@$tTngpanenhi)."</td>";
			$tab.="<td align=right >".number_format(@$tTngpanenbi)."</td>";
			$tab.="<td align=right colspan=2>".number_format(@$tTngapanenblnlalu)."</td>";

			$tab.="<td align=right >".number_format(@$tkphi)." </td>";
			$tab.="<td align=right >".number_format(@$tkkpbi)." </td>";
			$tab.="<td align=right >".number_format(@$tkkpbnlalu)." </td>";

			$tab.="<td align=right >".number_format(@$tbrondolhi)." </td>";
			$tab.="<td align=right colspan=2>".number_format(@$tbrondolsdbi)." </td>";
			$tab.="<td align=right >".number_format(@$tprbrondolhi,2)." </td>";
			$tab.="<td align=right >".number_format(@$tprbrondolsdbi,2)." </td>";

			$tab.="<td align=right >".number_format(@$tluaspanen)." </td>";
			$tab.="<td align=right >".number_format(@$tluaspanen/$tluas,2)." </td>";
			$tab.="<td align=right >".number_format(@$tjlhrestan)." </td>";
			$tab.="<td align=right >".number_format(@$tjlhrestankg)." </td>";
			$tab.="<td align=right >".number_format(@$tcurahhari)." </td>";
			$tab.="<td align=right >".number_format(@$tcurahbln)." </td>";
			$tab.="<td align=right >".number_format(@$tcurahsdbi)." </td>";
			$tab.="<td align=right >".number_format(@$tcuacapagi)." </td>";

		
			$tab.="</tr>";
   				 
		} else {
			$tab.="<tr class=rowcontent>
				<td colspan=35>".$_SESSION['lang']['errdatanotexist']."</td>
				</tr>";
		}

				
	if($tipeprint=='html'){
			echo $tab;			
	}else if($tipeprint=='excel'){
			
		$tab.="</tbody></table>";			
		$nop = "LAPORAN PRODUKSI PERDIVISI.xls"; //Nama file
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("1", $tab);
		$xls->headers($nop);
		echo $xls->buildFile();
	}

	break;	

}

?>