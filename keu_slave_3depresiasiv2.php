<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');

$param = $_POST;
$kodeorg  = $param['kodeorg'];
$dataorg = array();
$str = "select * from " . $dbname . ".organisasi where kodeorganisasi = '" . $kodeorg . "'";
$res = fetchdata($str);
$tipeorg = $res[0]['tipe'];

$tipeorganisasi = makeOption($dbname, "organisasi", "kodeorganisasi,tipe");

if ($tipeorganisasi[$_SESSION['empl']['lokasitugas']] != 'HOLDING' and $tipeorganisasi[$_SESSION['empl']['lokasitugas']] != 'KANWIL') {
	exit('<label hidden>Warning</label> Untuk Melihat List Data Depresiasi Asset, hanya lokasi tugas Holding (HO) atau KANWIL (RO) yang bisa lakukan Proses');
}

$strdprp = "select nilai from " . $dbname . ".setup_parameterappl WHERE kodeaplikasi = 'DP' AND kodeparameter = 'DPRP'";
$resdprp = fetchdata($strdprp);

$arrdprp = explode(',', $resdprp[0]['nilai']);
foreach ($arrdprp as $key) {
	$arrkbn[$key] = $key;
}

$strdpsr = "select nilai from " . $dbname . ".setup_parameterappl WHERE kodeaplikasi = 'DP' AND kodeparameter = 'DPSR'";
$resdpsr = fetchdata($strdpsr);

$arrdpsr = explode(',', $resdpsr[0]['nilai']);
foreach ($arrdpsr as $key) {
	$arrponti[$key] = $key;
}

// echo"<pre>";
// print_r($arrkbn);
// echo"</pre>";


$tahunbulan = implode("", explode('-', $param['periode']));
#1. Ambil semua aktiva yang aktif
if ($_SESSION['language'] == 'EN') {
	$zz = "b.namatipe1 as namatipe";
} else {
	$zz = "b.namatipe";
}
//<th align=center>".$_SESSION['lang']['tipe']." ".$_SESSION['lang']['alokasi']."</th>
$tabledt = "
	<table class=sortable cellspacing=1 border=0>
	<thead>
	<tr class=rowheader>
		<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
		<th align=center>Asset Type</th>
		<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
		<th align=center>" . $_SESSION['lang']['kodeasset'] . "</th>
		<th align=center>" . $_SESSION['lang']['nama'] . "</th>
		
		
		<th align=center>" . $_SESSION['lang']['awalpenyusutan'] . "</th>
		<th align=center>" . $_SESSION['lang']['jumlahbulan'] . "</th>
		<th align=center>" . $_SESSION['lang']['akhirpenyusutan'] . "</th>
		<th align=center hidden>" . $_SESSION['lang']['selesai'] . "</th>
		
		<th align=center>" . $_SESSION['lang']['hargaperolehan'] . "</th>
		<th align=center>" . $_SESSION['lang']['total'] . "<br>(" . $_SESSION['lang']['bulanan'] . " * " . $_SESSION['lang']['jumlahbulan'] . ")</th>
		<th align=center>" . $_SESSION['lang']['selisih'] . " (Akan Ditambahkan diakhir peiode susut)</th>
		
		<th align=center>Journal Code</th>
		
		<th align=center>" . $_SESSION['lang']['bulanan'] . "</th>
		<th align=center>" . $_SESSION['lang']['nilai'] . "<br>" . $_SESSION['lang']['selisih'] . "<br>(" . $_SESSION['lang']['bulanakhir'] . ")</th>
		<th align=center>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['jurnal'] . " " . $param['periode'] . " (Bulanan+selisih jika akhir periode susut)</th>
		
		<th align=center>" . $_SESSION['lang']['noakundebet'] . "</th>
		<th align=center>" . $_SESSION['lang']['noakunkredit'] . "</th>
	</tr>
	</thead>
	<tbody>";

// echo $str;exit();

$rinci = array();
$str = "select a.namasset,a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,a.hargaperolehan," . $zz . ",a.jenis_biaya, a.tipelokasi,
left(a.tanggaldisposal,7) as periodenonaktif
       from " . $dbname . ".sdm_daftarasset a left join " . $dbname . ".sdm_5tipeasset b
       on a.tipeasset=b.kodetipe    
       where a.kodeorg='" . $kodeorg . "' 
       and status=1 and a.awalpenyusutan <= '" . $param['periode'] . "'  and persendecline=0";
// echo $str;
$res = fetchdata($str);
foreach ($res as $bar) {

	$x = mktime(0, 0, 0,  intval(substr($bar['awalpenyusutan'], 5, 2) + ($bar['jlhblnpenyusutan'])), 15, intval(substr($bar['awalpenyusutan'], 0, 4)));
	$maxperiod = date('Y-m', $x);
	if ($bar['periodenonaktif'] != "0000-00") {
		if ($param['periode'] >= $bar['periodenonaktif']) {
			continue;
		}
	}

	$kodejurnal = '';
	// if($tipeorg == 'HOLDING' || $tipeorg == 'KANWIL' || $tipeorg == 'BULKING' || $tipeorg == 'RND'){
	// $kodejurnal='DPH'.substr($bar['tipeasset'],0,2);
	// }else{
	// $kodejurnal='DEP'.substr($bar['tipeasset'],0,2);
	// }

	if ($tipeorg == 'HOLDING') {
		$kodejurnal = 'DPH' . substr($bar['tipeasset'], 0, 2);
	} else if ($tipeorg == 'KANWIL') {
		$kodejurnal = 'DPH' . substr($bar['tipeasset'], 0, 2);
	} else {
		$kodejurnal = 'DEP' . substr($bar['tipeasset'], 0, 2);
	}


	#= coa debet credit
	$strakun = "select * from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "'";
	// echo $strakun;
	$resakun = fetchdata($strakun);
	$debet = $resakun[0]['noakundebet'];
	$kredit = $resakun[0]['noakunkredit'];
	// $tplok = ($bar['tipelokasi'] == 'E') ? 'E' : 'M';

	if ($param['periode'] < $maxperiod) {
		@$no += 1;
		$tabledt .= "<tr class=rowcontent id='row" . $no . "'>";
		$tabledt .= "<td>" . $no . "</td>";
		$tabledt .= "<td id='tipeasset" . $no . "'>" . $bar['tipeasset'] . "</td>";
		$tabledt .= "<td id='keterangan" . $no . "'>" . $bar['namatipe'] . "</td>";
		$tabledt .= "<td id='kodeasset" . $no . "'>" . $bar['kodeasset'] . "</td>";
		$tabledt .= "<td id='namaaset" . $no . "'>" . $bar['namasset'] . "</td>";
		$tabledt .= "<td id='tipealokasi" . $no . "' hidden></td>";

		$tabledt .= "<td>" . $bar['awalpenyusutan'] . "</td>";
		$tabledt .= "<td align=right>" . $bar['jlhblnpenyusutan'] . "</td>";
		$tabledt .= "<td>" . periodelalu($maxperiod) . "</td>";
		$tabledt .= "<td hidden>" . $maxperiod . "</td>";

		$totalsudahsusut = ($bar['bulanan'] * $bar['jlhblnpenyusutan']);
		$selisihsusut = $bar['hargaperolehan'] - $totalsudahsusut;
		$tabledt .= "<td align=right>" . number_format($bar['hargaperolehan'], 2) . "</td> ";
		$tabledt .= "<td align=right>" . number_format($totalsudahsusut, 2) . "</td> ";
		$tabledt .= "<td align=right>" . number_format($selisihsusut, 2) . "</td> ";

		$tabledt .= "<td id='kodejurnal" . $no . "'>" . $kodejurnal . "</td> ";

		if ($param['periode'] != periodelalu($maxperiod)) {
			$selisihsusut = 0;
		}

		$tabledt .= "<td align=right>" . number_format($bar['bulanan'], 2) . "</td>";
		$tabledt .= "<td align=right>" . number_format($selisihsusut, 2) . "</td>";
		$tabledt .= "<td align=right id='jumlah" . $no . "' bgcolor=lime>" . number_format($bar['bulanan'] + $selisihsusut, 2) . "</td>";

		$tabledt .= "<td align=right id='debet" . $no . "' bgcolor=lime>" . $debet . "</td>";
		$tabledt .= "<td align=right id='kredit" . $no . "' bgcolor=lime>" . $kredit . "</td>";
		$tabledt .= "</tr>";
		@$tbulan += $bar['bulanan'];
		@$tnilaijurnal += $bar['bulanan'] + $selisihsusut;
	}
}
$tabledt .= "<tr class=rowcontent id='row" . $no . "'>";
$tabledt .= "<td>Total</td>";
$tabledt .= "<td align=right colspan=12>" . number_format($tbulan, 2) . "</td>";
$tabledt .= "<td align=right></td>";
$tabledt .= "<td align=right>" . number_format($tnilaijurnal, 2) . "</td>";
$tabledt .= "<td align=right colspan=2></td>";
$tabledt .= "</tr>";


//Ambil double declining
/*
  $str=$owlPDO->query("select a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,a.persendecline,a.hargaperolehan,".$zz.",a.jenis_biaya 
       from ".$dbname.".sdm_daftarasset a left join ".$dbname.".sdm_5tipeasset b
       on a.tipeasset=b.kodetipe    
       where a.kodeorg='".$kodeorg."' 
       and status=1 and a.awalpenyusutan <= '".$param['periode']."' and a.tipeasset<>'MS'  and a.persendecline>'0'");
 $str->setFetchMode(PDO::FETCH_OBJ);
 while($bar=$str->fetch()){
	$thnawal=substr($bar->awalpenyusutan,0,4);
	$blnawal=substr($bar->awalpenyusutan,5,2);
	$total=($thnawal*12)+$blnawal;

	$thnNow=substr($param['periode'],0,4);
	$blnNow=substr($param['periode'],5,2);
	
	$totalBulanAwal = 12-$blnawal+1;
	$totalTahun = $thnNow-$thnawal-1;
	
	$totalNow=($thnNow*12)+$blnNow+1;
	$selisih=$totalNow-$total;
	$out=0;
	$akumNow = $sekarang = 0;
	
	// Depresiasi s/d akhir tahun
	$before = $sekarang = $bar->hargaperolehan;
	if($totalTahun>-1) {
		$akumNow += $totalBulanAwal/12 * $bar->persendecline/100 * $sekarang;
	}
	$sekarang -= $akumNow;
	
	// Depresiasi per Tahun
	if($totalTahun>0) {
		for($i=0;$i<$totalTahun;$i++) {
			$before = $sekarang;
			$akumNow += $sekarang*$bar->persendecline/100;
			$sekarang -= $sekarang*$bar->persendecline/100;
		}
	}
	
	// Depresiasi per Bulan
	$out = $sekarang*($bar->persendecline/100)/12;
	if($bar->jlhblnpenyusutan<$selisih) {
		$sekarang = $out = 0;
	}
	
	if(isset($ass[$bar->tipeasset])) {
		$ass[$bar->tipeasset]+=$out;
	} else {
		$ass[$bar->tipeasset]=$out;
	}
	if($bar->jenis_biaya=='1'){//MS01=index array untuk ambil jenis biaya langsung
        	$ass[$bar->tipeasset.$bar->jenis_biaya]+=$out;
    }
	//$rinci[] = array($bar->kodeasset, $out);
	$nama[$bar->tipeasset]=$bar->namatipe;
	$pass[$bar->tipeasset]='DEP'.substr($bar->tipeasset,0,2);      
}
*/
$tabledt .= "<tr class=rowcontent>";
$tabledt .= "<td align=left colspan=18><button class=mybutton  onclick=savedep(" . $no . ") id=btnproses>Process</button></td>";
$tabledt .= "</tr>";
// $tabledt.="<button class=mybutton onclick=savedep(".$no.") id=btnproses>Process</button>";

echo $tabledt;
