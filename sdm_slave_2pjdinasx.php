<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
// error_reporting(0);

$method           	= checkPostGet('method','');
$unit             	= checkPostGet('unit','');
$notransaksi      	= checkPostGet('notransaksi','');
$karyawanid       	= checkPostGet('namakarylist','');
$kodeapproval     	= checkPostGet('kodeapproval','');
$kepada           	= checkPostGet('kepada','');
$tglDari 			= tanggalsystemn(checkPostGet('tanggaldari',''));
$tglSampai			= tanggalsystemn(checkPostGet('tanggalsampai',''));
$jabatan			= checkPostGet('jabatan','');
$departemen			= checkPostGet('departemen','');
$jenis				= checkPostGet('jenis','');
$path             	= "fileupload/sdm_pjdinas/";

$arrHsl=array("9"=>$_SESSION['lang']['wait_approval'],"0"=>$_SESSION['lang']['belumdiajukan'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak'],"3"=>$_SESSION['lang']['koreksi']);

$where = "";
// $whereHt = "";
// $whereDt = "";
	
if ($unit != "") {
	$where .= " AND a.kodeorg = '".$unit."' ";
}

if ($notransaksi != "") {
	$where .= " AND a.notransaksi = '".$notransaksi."'";
}

if ($karyawanid != "") {
	$where .= " AND a.karyawanid = '".$karyawanid."' ";
}

if ($tglDari != "" || $tglSampai != "") {
	// $where .= " AND a.tgldinasdarireal >= '".$tglDari."' AND a.tgldinassampaireal <= '".$tglSampai."'";
	// $where .= " AND a.tgldinasdarireal <= '".$tglDari."' AND a.tgldinassampaireal >= '".$tglSampai."'";
	$where .= " AND (( a.tgldinasdarireal <= '".$tglDari."' and ( a.tgldinassampaireal >= '".$tglDari."' and a.tgldinassampaireal <= '".$tglSampai."' ) ) 
	or
	( a.tgldinasdarireal >= '".$tglDari."' and a.tgldinassampaireal <= '".$tglSampai."' ) 
	or
	( ( a.tgldinasdarireal >= '".$tglDari."' and a.tgldinasdarireal <= '".$tglSampai."' ) and a.tgldinassampaireal >= '".$tglSampai."' )
	or
	( a.tgldinasdarireal <= '".$tglDari."' and a.tgldinassampaireal >= '".$tglSampai."'))";
}

if ($departemen != "") {
	$karyawanDepartemen = selectQuery($dbname, 'datakaryawan', 'karyawanid', "bagian = '".$departemen."'");
	$where .= ' AND a.karyawanid IN ('.$karyawanDepartemen.')';
}

if ($jabatan != "") {
	$karyawanJabatan = selectQuery($dbname, 'datakaryawan', 'karyawanid', "kodejabatan = '".$jabatan."'");
	$where .='AND a.karyawanid IN ('.$karyawanJabatan.')';
}

# get jenis biaya 
$sJenisBiaya = selectQuery($dbname, 'sdm_5jenisbiayapjdinas', '*', "keterangan NOT LIKE 'UANG MAKAN%'", 'id ASC');
$rJenisBiaya = fetchData($sJenisBiaya);

// count jenis biaya rows
$countJenisBiaya = count($rJenisBiaya) + 1;
$headJenisBiaya = '';
foreach ($rJenisBiaya as $value) {
	$headJenisBiaya .= "<th align=center>".$value['keterangan']."</th>";
}
$headJenisBiaya .= "<th align=center>PREMI DRIVER</th>";

/*
START TABLE
*/

$arryjnspjd=array('0'=>'Dinas Staff','1'=>'Dinas Non Staff');
$arrytiket=array('0'=>'Tidak','1'=>'Ya');
$nmreg = makeOption($dbname, 'bgt_regional', 'regional,nama');

# Data Jenis Biaya
$qJenisbiaya = selectQuery($dbname, 'sdm_5jenisbiayapjdinas', '*', "keterangan NOT LIKE 'UANG MAKAN%' ORDER BY id ASC");
$rJenisbiaya = fetchData($qJenisbiaya);
foreach ($rJenisbiaya as $bar) {
	$jenisbiaya[$bar['id']] = $bar['id'];
}

# Data Join Pjdinasht dan Pjdinasdt
$qPjdinas = "SELECT a.*, b.jenisbiaya, b.sumber, b.tanggungan, b.statusverifikasihrd, b.jumlahhrd, b.jumlah, b.umdriver FROM ".$dbname.".sdm_pjdinasht a LEFT JOIN ".$dbname.".sdm_pjdinasdt b ON a.notransaksi = b.notransaksi WHERE 1=1 ".$where." ORDER BY b.jenisbiaya ASC ";
$rPjdinas = fetchData($qPjdinas);
foreach ($rPjdinas as $bar) {

	$karyawanidlist[$bar['notransaksi']] = $bar['karyawanid'];
	$tipekary[$bar['notransaksi']] = $bar['tipekary'];
	$unittujuan[$bar['notransaksi']] = $bar['unittujuan'];
	$tgldinasdarireal[$bar['notransaksi']] = $bar['tgldinasdarireal'];
	$tgldinassampaireal[$bar['notransaksi']] = $bar['tgldinassampaireal'];
	$kodeorg[$bar['notransaksi']] = $bar['kodeorg'];
	$tiket[$bar['notransaksi']] = $bar['tiket'];
	$keterangan[$bar['notransaksi']] = $bar['keterangan'];
	$statuspengajuan[$bar['notransaksi']] = $bar['statuspengajuan'];
	$statusrealisasi[$bar['notransaksi']] = $bar['statusrealisasi'];
	$namahrd[$bar['notransaksi']] = $bar['namahrd'];
	$notransaksilist[$bar['notransaksi']] = $bar['notransaksi'];

	if ($bar['sumber'] == 0) {
		@$uangmuka[$bar['notransaksi']] += $bar['jumlah']; // total uang muka pernotransaksi
	}

	if($bar['tanggungan']=='1' and $bar['sumber']=='1'){
		$jumlahklaim[$bar['notransaksi']][$bar['jenisbiaya']] += $bar['jumlah']; // total klaim 
		if ($bar['jenisbiaya'] == '10' || $bar['jenisbiaya'] == '11') {
			if ($bar['umdriver'] == '19') { // Makan pagi driver
				$jumlahklaim[$bar['notransaksi']][7] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '20') { // Makan siang driver
				$jumlahklaim[$bar['notransaksi']][8] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '21') { // Makan malam driver
				$jumlahklaim[$bar['notransaksi']][9] += $bar['jumlah'];	
			} else if ($bar['umdriver'] == '8') { // Premi driver
				$premidriverklaim[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '9') { // Premi driver
				$premidriverklaim[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '10') { // Premi driver
				$premidriverklaim[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '11') { // Premi driver
				$premidriverklaim[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '14') { // Premi driver
				$premidriverklaim[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '15') { // Premi driver
				$premidriverklaim[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '16') { // Premi driver
				$premidriverklaim[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '17') { // Premi driver
				$premidriverklaim[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '18') { // Premi driver
				$premidriverklaim[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '22') { // Makan pagi driver
				$jumlahklaim[$bar['notransaksi']][7] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '23') { // Makan siang driver
				$jumlahklaim[$bar['notransaksi']][8] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '24') { // Makan malam driver
				$jumlahklaim[$bar['notransaksi']][9] += $bar['jumlah'];
			}
		}
	}

	if ($bar['tanggungan'] == 1 AND $bar['statusverifikasihrd'] == 1  AND $bar['sumber'] == 1) {
		$jumlahverifhrd[$bar['notransaksi']][$bar['jenisbiaya']] += $bar['jumlah']; // total verifhrd pernotransaksi
		if ($bar['jenisbiaya'] == '10' || $bar['jenisbiaya'] == '11') {
			if ($bar['umdriver'] == '19') { // Makan pagi driver
				$jumlahverifhrd[$bar['notransaksi']][7] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '20') { // Makan siang driver
				$jumlahverifhrd[$bar['notransaksi']][8] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '21') { // Makan malam driver
				$jumlahverifhrd[$bar['notransaksi']][9] += $bar['jumlah'];	
			} else if ($bar['umdriver'] == '8') { // Premi driver
				$premidriververifikasi[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '9') { // Premi driver
				$premidriververifikasi[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '10') { // Premi driver
				$premidriververifikasi[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '11') { // Premi driver
				$premidriververifikasi[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '14') { // Premi driver
				$premidriververifikasi[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '15') { // Premi driver
				$premidriververifikasi[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '16') { // Premi driver
				$premidriververifikasi[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '17') { // Premi driver
				$premidriververifikasi[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '18') { // Premi driver
				$premidriververifikasi[$bar['notransaksi']] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '22') { // Makan pagi driver
				$jumlahverifhrd[$bar['notransaksi']][7] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '23') { // Makan siang driver
				$jumlahverifhrd[$bar['notransaksi']][8] += $bar['jumlah'];
			} else if ($bar['umdriver'] == '24') { // Makan malam driver
				$jumlahverifhrd[$bar['notransaksi']][9] += $bar['jumlah'];
			}
		}
	}
}

// echo "<pre>"; var_dump($qPjdinasdt); exit;
// echo "<pre>"; print_r($jumlahklaim); exit;

$no = 1;
$total = 0;
$stream = "";
# Stream data to table 

if ($method == "excel") {
	$stream .= "
		<table class=sortable cellpadding=5 cellspacing=1 border='1'>
			<thead>
				<tr class=rowheader>
					<th align=center rowspan=2>".$_SESSION['lang']['nourut']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['notransaksi']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['jenis']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['namakaryawan']."</th>
					<th align=center rowspan=2>".$_SESSION['lang']['unit']."</th>
					<th align=center colspan=3>Real ".$_SESSION['lang']['tanggal']."</th>
					<th align=center rowspan=2>Unit ".$_SESSION['lang']['tujuan']."</th>
					<th align=center rowspan=2>Uang Muka</th>
					<th align=center colspan=".$countJenisBiaya.">Reimburse / Klaim</th>
					<th align=center rowspan=2>".$_SESSION['lang']['total']." Reimburse</th>
					<th align=center colspan=".$countJenisBiaya.">Verifikasi (dibayarkan)</th>
					<th align=center rowspan=2>".$_SESSION['lang']['total']." Verifikasi</th>
					<th align=center rowspan=2>Net Off Uang Muka</th>
					<th align=center rowspan=2>".$_SESSION['lang']['keterangan']."</th>
					<th align=center rowspan=2>Tiket Pesawat</th>
					<th align=center rowspan=2>Status Pengajuan</th>
					<th align=center rowspan=2>Status Pertanggungjawaban</th>
				</tr>
				<tr>
					<th align=center>Dari</th>
					<th align=center>Sampai</th>
					<th align=right>Jumlah</th>
					".$headJenisBiaya."
					".$headJenisBiaya."

				</tr>
			</thead>
			<tbody>
	";
}

if (count($notransaksilist) > 0) {
	foreach ($notransaksilist as $res) {
		$nmKaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='".$karyawanidlist[$res]."'");

		$selisih = strtotime($tgldinassampaireal[$res]) - strtotime($tgldinasdarireal[$res]);
		$selisih = $selisih / (60 * 60 * 24) + 1;

		$exclude = ['7' => 7, '8' => 8, '9' => 9];
		@$jumlahklaim2 = array_diff_key(@$jumlahklaim[$res], $exclude);
		@$jumlahverifhrd2 = array_diff_key($jumlahverifhrd[$res], $exclude);
		// echo "<pre>"; print_r(array_sum($jumlahverifhrd2)); exit;

		$stream .= "
			<tr class='rowcontent'>
				<td align='center'>".$no++."</td>
				<td align='center'>".$res."</td>
				<td align='left'>".$arryjnspjd[$tipekary[$res]]."</td>
				<td align='left'>".$nmKaryawan[$karyawanidlist[$res]]."</td>
				<td align='center'>".$kodeorg[$res]."</td>
				<td align='center'>".tanggalnormal($tgldinasdarireal[$res])."</td>
				<td align='center'>".tanggalnormal($tgldinassampaireal[$res])."</td>
				<td align='center'>".$selisih." Hari</td>
				<td align='center'>".$unittujuan[$res]."</td>
				<td align='right'>".number_format($uangmuka[$res])."</td>";
			foreach ($jenisbiaya as $bar) {
				$stream .= "<td align='right'>".number_format(@$jumlahklaim[$res][$bar])."</td>";
				@$ttlreimburse = array_sum($jumlahklaim2);
			}
			//Umar
			$stream .= "<td align='right'>".number_format(@$premidriverklaim[$res])."</td>";
			//End Umar
			$stream .= "<td align='right'>".number_format($ttlreimburse)."</td>";

			foreach ($jenisbiaya as $bar) {
				$stream .= "<td align='right'>".number_format(@$jumlahverifhrd[$res][$bar])."</td>";
				@$ttlverifikasi = array_sum($jumlahverifhrd2);
			}
			// //Umar
			$stream .= "<td align='right'>".number_format(@$premidriververifikasi[$res])."</td>";
			// //End Umar
		$stream .= "
			<td align='right'>".number_format($ttlverifikasi)."</td>
			<td align='right'>".number_format($uangmuka[$res] - $ttlverifikasi)."</td>
		";
		$stream .= "
				<td align='left'>".$keterangan[$res]."</td>
				<td align='center'>".$arrytiket[$tiket[$res]]."</td>
				<td align=left>".$arrHsl[$statuspengajuan[$res]]."</td>";
				if ($statuspengajuan[$res] == '1' and $statusrealisasi[$res] != '0') {
					$stream .= "<td align='left'>".$arrHsl[$statusrealisasi[$res]]."<br>
						<font style=font-style:italic;font-size:10px;font-weight:bold;>".$nmkar[$namahrd[$res]]."</font></td>";
				} else {
					$stream .= "<td align='left'></td>";				
				}
			
		if ($method != "excel") {
			$stream .= "
					<td align='center' style='width:20px'><img src='images/skyblue/pdf.jpg' class='zImgBtn' height='30' title='PDF' onclick=\"detailPDF('".$res."','event','pdf');\" ></td>
					
					<td align='center' style='width:20px'><img src='images/skyblue/zoom.png' class='zImgBtn' height='30'  title='Preview' onclick=\"detailData('".$res."','event','html');\" ></td>
					
					<td align='center' style='width:20px'><img src='images/excel.jpg' class='zImgBtn' height='30' title='Excel' onclick=\"detailExcel('".$res."','event','excel');\" ></td>
	
				</tr>
			";
		}		
	}

	if ($method == "excel") {
		$stream .= "</tbody>";
	}
} else {
	$stream .= "<tr class='rowcontent'><td colspan='".(count($jenisbiaya)*2+18)."' align='center'>".$_SESSION['lang']['dataempty']."</td></tr>";
}


switch($method){
	case'loaddata':
		// echo "<pre>"; print_r($jumlahklaim['SD1E202200193']); exit;

		echo $stream;
	break;

	case 'excel': 
		$print = $stream;
		$print.="Print Time : ".date('H:i:s, d/m/Y')."<br>By : ".$_SESSION['empl']['name'];	
		
		$nop = "Laporan Pjdinas.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet("Pjdinas", $print);
		$xls->headers($nop);
		echo $xls->buildFile();
	break;
	case'previewdata':
		$tab="";
		$tab2="";
		$tab3="";
		$tab4="";
		$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"length(kodeorganisasi)<=4");
		$nmjab = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
		$nmgol = makeOption($dbname,'sdm_5golongan','kodegolongan,namagolongan');
		$nmdep = makeOption($dbname,'sdm_5departemen','kode,nama');
		$nmlev = makeOption($dbname,'sdm_5levelpjdinas','level,namalevel');
		$jnsapp = makeOption($dbname,'sdm_5levelpjdinas','level,kodeapproval');
		$nmreg = makeOption($dbname,'bgt_regional','regional,nama');
		$str="select * from ".$dbname.".sdm_pjdinasht where notransaksi = '".$notransaksi."'";
		$res=fetchData($str);
		foreach($res as $bar){
			$strx="select * from ".$dbname.".datakaryawan where karyawanid='".$bar['karyawanid']."'";
			$resx=fetchData($strx);
			foreach($resx as $barx){
				$nmkar[$barx['karyawanid']]=$barx['namakaryawan'];
				$nkkar[$barx['karyawanid']]=$barx['nik'];
				$jabkar[$barx['karyawanid']]=$nmjab[$barx['kodejabatan']];
				$golkar[$barx['karyawanid']]=$nmgol[$barx['kodegolongan']];
				$depkar[$barx['karyawanid']]=$nmdep[$barx['bagian']];
			}
			
			$statuspengajuan= $bar['statuspengajuan'];
			$batal  =$bar['keteranganbatal'];
			$kodeorg= $bar['kodeorg'];
			$karyid = $bar['karyawanid'];
			$ket    = $bar['keterangan'];
			if($bar['pttujuan']!='OTH'){
				$pttujuan    = $nmorg[$bar['pttujuan']];
				$unittujuan  = $nmorg[$bar['unittujuan']];
			}else{
				$pttujuan    = $bar['pttujuan'];
				$unittujuan  = $bar['unittujuan'];
			}
			if($bar['tiket']=='1'){
				$tiket="Ya";
			}else{
				$tiket="Tidak";
			}
			
			$regiontujuan= $nmreg[$bar['regiontujuan']];
			$tgldr       = tanggalnormal($bar['tgldinasdari']);
			$tgldrreal   = tanggalnormal($bar['tgldinasdarireal']);
			$tglsd       = tanggalnormal($bar['tgldinassampai']);
			$tglsdreal   = tanggalnormal($bar['tgldinassampaireal']);
			$namakary    = $nmkar[$bar['karyawanid']];
			$nikkar      = $nkkar[$bar['karyawanid']];
			$jabatan     = $jabkar[$bar['karyawanid']];
			$golongan    = $golkar[$bar['karyawanid']];
			$dept        = $depkar[$bar['karyawanid']];
			$level       = $nmlev[$bar['level']];
			$kodeapproval= $jnsapp[$bar['level']];
		}
		
		#cari noreff uang muka
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2='umpjd#".$notransaksi."' and nik='".$karyid."'";
		$resa = fetchdata($stra);
		$umdibayarkan=0;$umnoreff="";
		foreach($resa as $bara){				
			$umdibayarkan+=$bara['jumlah'];
			if($bara['notransaksi']!=''){
				$umnoreff=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}
		
		#cari noreff uang bayar oleh pt
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2 like 'realpjd#".$notransaksi."%' and nik='".$karyid."' group by notransaksi";
		$resa = fetchdata($stra);
		$realpt=0;$realptnoreff="";
		foreach($resa as $bara){				
			$realpt+=$bara['jumlah'];
			if($bara['notransaksi']!=''){
				$realptnoreff.=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}
		
		#cari noreff ptj
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2 like 'claimpjd#".$notransaksi."%' and nik='".$karyid."' group by notransaksi";
		$resa = fetchdata($stra);
		$claimbayar=0;$claimnoreff="";
		foreach($resa as $bara){				
			$claimbayar+=$bara['jumlah'];
			if($bara['notransaksi']!=''){
				$claimnoreff.=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}
		$stra="select sum(jumlah) as jumlah, noakun,notransaksi,kodeorg from ".$dbname.".keu_kasbankdt where keterangan1='".$notransaksi."' and keterangan2 like 'pjdbatal#".$notransaksi."%' and nik='".$karyid."' group by notransaksi";
		$resa = fetchdata($stra);
		foreach($resa as $bara){				
			$claimbayar+=$bara['jumlah'];
			if($bara['notransaksi']!=''){				
				$claimnoreff.=$bara['notransaksi']." = ".number_format($bara['jumlah'])."<br>";
			}
		}
		
		$waktuawal = tanggalsystemn($tgldrreal);
		$waktuakhir = tanggalsystemn($tglsdreal);
		
		$diff = (strtotime($waktuakhir)-strtotime($waktuawal));
		$hari = floor($diff/(60*60*24));
			
		if($jenis=='pdf'){
			$arrHead = setheadreport('',$kodeorg);
			$path=$arrHead['logo'];

			$tab.="<div>
				<table cellspacing=0 border=0 width=100% align=center style=\"font-family:sans-serif;font-size:12px;\">
					<tr>
						<td rowspan=3 valign=center style='font-weight:bold;width:100px'><img src='".$path."' height='60' /></td>
						<td style=font-weight:bold;>".$arrHead['nama']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['alamat']."</td>
					</tr>
					<tr>
						<td style=font-weight:bold;>".$arrHead['telepon']."</td>
					</tr>
				</table><hr>";
		}

	
		$fontsize="13px";
		
		$top=$bottom=$left=$right="";
		// $top     ="border-top:0.5px solid black;";
		$bottom  ="border-bottom:0.5px solid black;";
		// $left    ="border-left:0.5px solid black;";
		// $right   ="border-right:0.5px solid black;";
		
		#style=\"font-family:sans-serif;font-size:13px;font-weight:bold;\"
		$tab.="
		<table cellspacing=0 border=0 width=100% style='text-align:center'>
			<tr>
				<td style=font-weight:bold;font-family:sans-serif;text-decoration:underline;>SURAT PERJALANAN DINAS</td>
			</tr>
			<tr>
				<td style=\"font-family:sans-serif;font-size:13px;font-weight:bold;\">Nomor : ".$notransaksi."</td>
			</tr>
		</table>
		<br>
		<table cellspacing=0 border=0 width=100% style=\"font-family:sans-serif;font-size:".$fontsize.";\">
			<tr>
				<td width=100px>".$_SESSION['lang']['namakaryawan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$namakary."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>PT ".$_SESSION['lang']['tujuan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$pttujuan."</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td width=100px>".$_SESSION['lang']['nik2']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$nikkar."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>".$_SESSION['lang']['unit']." ".$_SESSION['lang']['tujuan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$unittujuan."</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td width=100px>".$_SESSION['lang']['jabatan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$jabatan."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>".$_SESSION['lang']['regional']." ".$_SESSION['lang']['tujuan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$regiontujuan."</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td width=100px>".$_SESSION['lang']['kodegolongan']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$golongan."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>Level / Grade</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$level."</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td width=100px>".$_SESSION['lang']['departemen']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$dept."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>".$_SESSION['lang']['ticket']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$tiket."</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td width=100px>".$_SESSION['lang']['tanggaldinas']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".$tgldrreal." s/d ".$tglsdreal."</td>
				
				<td width=30px>&nbsp;</td>
				
				<td width=100px>".$_SESSION['lang']['jumlah']."</td>
				<td width=10px align=left>:</td>
				<td style='".$left."".$right."".$top."".$bottom."'>".($hari+1)."  (hari)</td>
			</tr>
			<tr>
				<td colspan=7></td>
			</tr>
			<tr>
				<td valign=top width=100px>".$_SESSION['lang']['keterangan']."</td>
				<td valign=top width=10px align=left>:</td>
				<td colspan=5>".$ket."</td>
			</tr>
		</table>
		<br>
		</div>";
		
		$top=$bottom=$left=$right="";
		$top     ="border-top:0.5px solid black;";
		$bottom  ="border-bottom:0.5px solid black;";
		$left    ="border-left:0.5px solid black;";
		$right   ="border-right:0.5px solid black;";
		
		#RUTE PERJALANAN
		$tujoth=makeOption($dbname,'sdm_pjdinasht','notransaksi,pttujuan',"notransaksi = '".$notransaksi."'");
		$tab.="<label style=\"font-family:sans-serif;font-weight:bold;font-size:".$fontsize.";\">".$_SESSION['lang']['rute']." :</label>";
		$str="select * from ".$dbname.".sdm_pjdinasdt_rute where notransaksi = '".$notransaksi."' order by waktu asc";
		$res=fetchData($str);
		if(!empty($res)){
			$tab.="<table cellspacing=0 border=0 width=100% style=\"font-family:sans-serif;font-size:".$fontsize.";\">
				<tr>
					<td align=center style='".$left."".$top."".$bottom.";font-weight:bold;width:30px;'>No</td>
					<td align=center style='".$left."".$top."".$bottom.";font-weight:bold;'>".$_SESSION['lang']['dari']."</td>
					<td align=center style='".$left."".$top."".$bottom.";font-weight:bold;'>".$_SESSION['lang']['tujuan']."</td>
					<td align=center style='".$left."".$top."".$bottom.";font-weight:bold;' width=150px>".$_SESSION['lang']['waktu']."</td>
					<td align=center style='".$left."".$right."".$top."".$bottom.";font-weight:bold;'>".$_SESSION['lang']['transport']."</td>";
				
				if($tujoth[$notransaksi]=='OTH'){
					$tab.="<td align=center style='".$left."".$right."".$top."".$bottom.";font-weight:bold;'>Paraf</td>";
				}	
			$tab.="</tr>";
				
			$no=0;
			foreach($res as $bar){
			$no+=1;
				$tab.="<tr>
					<td width=30px align=center style='".$left."".$bottom."'>".$no."</td>
					<td align=left style='".$left."".$bottom."'>".$bar['dari']."</td>
					<td align=left style='".$left."".$bottom."'>".$bar['tujuan']."</td>
					<td align=center style='".$left."".$bottom."' width=150px>".waktunormal($bar['waktu'])."</td>
					<td align=left style='".$left."".$right."".$bottom."'>".$bar['transportasi']."</td>";
					
					if($tujoth[$notransaksi]=='OTH'){
						$tab.="<td align=left style='".$left."".$right."".$bottom."height:30px'></td>";
					}
				$tab.="</tr>";
			}
			$tab.="</table>";
		}
		
		#BIAYA
		$top=$bottom=$left=$right="";
		/* $top     ="border-top:0.5px solid black;";
		$bottom  ="border-bottom:0.5px solid black;";
		$left    ="border-left:0.5px solid black;";
		$right   ="border-right:0.5px solid black;"; */
		
		$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' order by tanggal,jenisbiaya asc"; 
		$res=fetchdata($str);
		$verhrd=$klaimkary=$sdhrealpt=$umminta=0;$data=$dataisi=$rangetgl=array();
		foreach($res as $bar){
			$databyy[$bar['jenisbiaya']]=$bar['jenisbiaya'];
			$rangetgl[$bar['tanggal']]=$bar['tanggal'];
			
			
			if($bar['sumber']=='0'){
				$umminta+=$bar['jumlah'];
			}
			if($bar['tanggungan']=='0' and $bar['sumber']=='1'){
				$sdhrealpt+=$bar['jumlah'];
			}
			if($bar['tanggungan']=='1' and $bar['sumber']=='1'){
				$klaimkary+=$bar['jumlah'];
			}
			// echo "<pre>"; print_r($res); exit;
			if($bar['tanggungan']=='1' and $bar['statusverifikasihrd']=='1'  and $bar['sumber']=='1'){
				$verhrd+=$bar['jumlahhrd'];
			}
		}
	
		if(!empty($res)){
			$tab.="<br><label style=\"font-family:sans-serif;font-weight:bold;font-size:".$fontsize.";\">".$_SESSION['lang']['biaya']." :</label>";
			if($jenis=='pdf'){				
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=5 cellspacing=1 border=0 class=sortable";
			}
			$tab.="<table ".$style.">
			<tr class=rowcontent>
				<td align=center>Keterangan</td>
				<td align=center></td>
				<td align=center>Diminta</td>
				<td align=center>Dibayar</td>
				<td align=center>No Reff</td>
			</tr>
			<tr class=rowcontent>
				<td >Total uang muka diminta</td>
				<td width=10px align=center>:</td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($umminta)."</td>
				<td align=center></td>
				<td align=center></td>
			</tr>
			<tr class=rowcontent>
				<td >Total uang muka diterima / dibayarkan<br>
				<!--<label style=\"font-family:sans-serif;font-weight:bold;font-style:italic;font-size:12px;\">".$umnoreff."</label>--></td>
				<td width=10px align=center>:</td>
				<td align=center></td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($umdibayarkan)."</td>
				<td align=left>".$umnoreff."</td>
			</tr>
			<tr class=rowcontent>
				<td >Total biaya yang sudah direalisasikan oleh perusahaan</td>
				<td width=10px align=center>:</td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($sdhrealpt)."</td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($realpt)."</td>
				<td align=left>".$realptnoreff."</td>
			</tr>
			<tr class=rowcontent>
				<td >Total biaya yang diajukan reimburse / klaim oleh karyawan</td>
				<td width=10px align=center>:</td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($klaimkary)."</td>
				<td align=center></td>
				<td align=center></td>
			</tr>
			<tr class=rowcontent>
				<td >Total biaya yang telah di verifikasi oleh HCM</td>
				<td width=10px align=center>:</td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($verhrd)."</td>
				<td align=center></td>
				<td align=center></td>
			</tr>
			<tr class=rowcontent>
				<td >Total biaya yang telah dibayarkan / dikembalikan<br>
				<!--<label style=\"font-family:sans-serif;font-weight:bold;font-style:italic;font-size:12px;\">".$claimnoreff."</label>--></td>
				<td width=10px align=center>:</td>
				<td align=center></td>
				<td align=right style='".$left."".$right."".$top."".$bottom."'>".numb_format($claimbayar)."</td>
				<td align=left>".$claimnoreff."</td>
			</tr>
			";
			$tab.="</table>";
		}
		
		if($statuspengajuan!='0'){
			$tab.="<br><label style=\"font-family:sans-serif;font-weight:bold;font-size:".$fontsize.";\">".$_SESSION['lang']['approval_status']." (by system):</label>";
			$countApprove= getCountApproval($kodeapproval,$kodeorg);
			$top=$bottom=$left=$right="";
			$top     ="border-top:0.5px solid black;";
			$bottom  ="border-bottom:0.5px solid black;";
			$left    ="border-left:0.5px solid black;";
			$right   ="border-right:0.5px solid black;";
			
			$tab.="<table cellspacing=0 border=0 style=\"font-family:sans-serif;font-size:".$fontsize.";\">";
			if(($countApprove-1)!=0){			
			$arrHslx=array("9"=>$_SESSION['lang']['wait_approval'],"0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['ditolak'],"3"=>$_SESSION['lang']['koreksi']);
			
				for($i=1;$i<=$countApprove;$i++){
				$arrApp = detailApprove($i,$notransaksi,$kodeapproval);
					$tab.="<tr>
						<td width=30px align=center style='".$left."".$bottom."".$top."'>".$i."</td>
						<td align=left style='".$top."".$bottom."'>".$arrApp['nama']."</td>
						<td align=left style='".$bottom."".$top."'>".$arrHslx[$arrApp['status']]."<br><font style=\"font-size:10px;font-style:italic;\">".$arrApp['tanggal']."</font></td>
						<td align=left style='".$right."".$bottom."".$top."'>".$arrApp['komentar']."</td>
					</tr>";
					
				}
			}
			$tab.="</table>";
		}
		
		if($statuspengajuan=='3'){
			$tab.="<br>Perjalanan dinas telah di batalkan dengan alasan :<br>".$batal."";
		}
		
		
		if($databyy){
			if($jenis!='pdf'){				
				$tab2.="<br>";
			}
			$tab2.="<div style='page-break-before: always;'></div>";
			$tab2.="
			<table cellspacing=0 border=0 width=100% style='text-align:center'>
				<tr>
					<td style=font-weight:bold;font-family:sans-serif;text-decoration:underline;>PERINCIAN BIAYA PERJALANAN DINAS</td>
				</tr>
			</table>";
			
			$top=$bottom=$left=$right="";
			$top     ="border-top:0.5px solid black;";
			$bottom  ="border-bottom:0.5px solid black;";
			$left    ="border-left:0.5px solid black;";
			$right   ="border-right:0.5px solid black;";
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and (umdriver!='' or tujuandriver!='')";
			$res=fetchdata($str);
			$dr=count($res);
			$row="";$con=0;$conttl=0;
			if($dr==0){					
				if($jenis!='pdf'){					
					$con=(count($rangetgl)+6);
				}else{
					$con=6;
				}
				$conttl=0;
			}else{
				if($jenis!='pdf'){					
					$con=(count($rangetgl)+8);
				}else{
					$con=8;
				}
				$conttl=2;
			}
			if($jenis!='pdf'){
				$row="rowspan=2";
			}
			$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
			$optjnsdriver=makeOption($dbname,'sdm_5setupdinasdriver','id,keterangan');
		
			$fontsize="10px";
			if($jenis=='pdf'){				
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=5 cellspacing=1 border=0 class=sortable width=100%";
			}
			$tab2.="<br><table ".$style." width=100%>
				<thead>
				<tr class=rowheader>";
					$tab2.="
					<td align=center style=font-weight:bold; ".$row." width=20px>No</td>
					<td align=center style=font-weight:bold; ".$row." >".$_SESSION['lang']['jenisbiaya']."</td>";
					if($dr>0){
						$tab2.="
						<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['jenis']."</td>
						<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['tujuan']."</td>";
					}		
					$tab2.="
					<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['location']."</td>";
					
					if($jenis!='pdf'){
						$tab2.="
						<td align=center style=font-weight:bold; colspan=".count($rangetgl).">".$_SESSION['lang']['tanggal'] . "</td>";
					}
					$tab2.="
					<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['totalbiaya']."</td>
					<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['keterangan']."</td>
					<td align=center style=font-weight:bold; ".$row.">".$_SESSION['lang']['dibuat']."</td>
				</tr>";
				if($jenis!='pdf'){	
					$tab2.="<tr class=rowheader>";
					foreach($rangetgl as $tgl){	
						$tab2.="<td style=font-weight:bold; align=center>".substr($tgl,8,2)."</td>";
					}
					$tab2.="</tr>";
				}
				$tab2.="</thead>";
				$tab2.="<tbody>";
			
			# UANG MUKA
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and sumber='0' order by tanggal asc";
			$res=fetchdata($str);
			$jumlahum=$ttlbyy=array();
			foreach($res as $bar){
				$datajenisbiaya[$bar['jenisbiaya']][$bar['umdriver']]=$bar['jenisbiaya'];
				#$umdriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['umdriver'];
				$tujuandriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tujuandriver'];
				$t4kunj[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tempatkunjungan'];
				$keterangan[$bar['jenisbiaya']][$bar['umdriver']]=$bar['keterangan'];
				$piclokasi[$bar['jenisbiaya']][$bar['umdriver']]=$bar['updateby'];
				$jumlahum[$bar['jenisbiaya']][$bar['tanggal']][$bar['umdriver']]+=$bar['jumlah'];
				$ttlbyy[$bar['jenisbiaya']][$bar['umdriver']]+=$bar['jumlah'];
			}
			// echo "<pre>"; print_r($str); exit;
			#JIKA ADA DATA MUNCULKAN
			if(count($res)>0){
				$no=0;
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=font-weight:bold;>Uang Muka diminta :</td>";
				$tab2.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy =>$valumdriver){
					foreach($valumdriver as $umdriver =>$jnbyy){
						if($ttlbyy[$jenisbyy][$umdriver]!=0){					
							$no+=1;
							$tab2.="<tr class=rowcontent>";
							$tab2.="<td align=center>".$no."</td>";
							$tab2.="<td align=left>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
							if($dr>0){									
								$tab2.="<td align=left>".$optjnsdriver[$umdriver]."</td>";
								$tab2.="<td align=left>".$optjnsdriver[$tujuandriver[$jenisbyy][$umdriver]]."</td>";
							}
							$tab2.="<td align=left>".$t4kunj[$jenisbyy][$umdriver]."</td>";
							foreach($rangetgl as $tgl){
								if($jenis!='pdf'){
									$tab2.="<td align=right>".numb_format($jumlahum[$jenisbyy][$tgl][$umdriver])."</td>";
								}
								$ttlbyytgl[$tgl]+=$jumlahum[$jenisbyy][$tgl][$umdriver];
							}
							$tab2.="<td align=right>".numb_format($ttlbyy[$jenisbyy][$umdriver])."</td>";

							$tab2.="<td>".$keterangan[$jenisbyy][$umdriver]."</td>";
							$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$piclokasi[$jenisbyy][$umdriver]."'");
							$tab2.="<td>".ucwords(strtolower($nmkar[$piclokasi[$jenisbyy][$umdriver]]))."</td>";
							$tab2.="</tr>";
						}
					}
				}
				
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($conttl+3)." style=font-weight:bold;>SUB TOTAL</td>";
				$gt=0;
				foreach($rangetgl as $tgl){
					if($jenis!='pdf'){
						$tab2.="<td align=right style=font-weight:bold;>".numb_format($ttlbyytgl[$tgl])."</td>";
					}
					$gt+=$ttlbyytgl[$tgl];
				}
				$tab2.="<td align=right style=font-weight:bold;>".numb_format($gt)."</td>";
				$tab2.="<td></td>";
				$tab2.="<td></td>";
				$tab2.="</tr>";
				
			}#TUTUP IF JIKA ADA DATA MUNCULKAN
			
			# UANG REAL BY PT
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and tanggungan='0' and sumber='1' order by tanggal asc";
			$res=fetchdata($str);
			$jumlahrealpt=$ttlbyy=array();
			foreach($res as $bar){
				$datajenisbiaya[$bar['jenisbiaya']][$bar['umdriver']]=$bar['jenisbiaya'];
				#$umdriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['umdriver'];
				$tujuandriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tujuandriver'];
				$t4kunj[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tempatkunjungan'];
				$keterangan[$bar['jenisbiaya']][$bar['umdriver']]=$bar['keterangan'];
				$piclokasi[$bar['jenisbiaya']][$bar['umdriver']]=$bar['updateby'];
				$jumlahrealpt[$bar['jenisbiaya']][$bar['tanggal']][$bar['umdriver']]+=$bar['jumlah'];
				$ttlbyy[$bar['jenisbiaya']][$bar['umdriver']]+=$bar['jumlah'];
			}
			
			#JIKA ADA DATA MUNCULKAN
			if(count($res)>0){
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=background-color:gray;></td>";
				$tab2.="</tr>";

				$no=0;
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=font-weight:bold;>Realisasi oleh perusahaan :</td>";
				$tab2.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy =>$valumdriver){
					foreach($valumdriver as $umdriver =>$jnbyy){
						if($ttlbyy[$jenisbyy][$umdriver]!=0){					
							$no+=1;
							$tab2.="<tr class=rowcontent>";
							$tab2.="<td align=center>".$no."</td>";
							$tab2.="<td align=left>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
							if($dr>0){									
								$tab2.="<td align=left>".$optjnsdriver[$umdriver]."</td>";
								$tab2.="<td align=left>".$optjnsdriver[$tujuandriver[$jenisbyy][$umdriver]]."</td>";
							}
							$tab2.="<td align=left>".$t4kunj[$jenisbyy][$umdriver]."</td>";
							foreach($rangetgl as $tgl){
								if($jenis!='pdf'){
									$tab2.="<td align=right>".numb_format($jumlahrealpt[$jenisbyy][$tgl][$umdriver])."</td>";
								}
								$ttlbyytgl[$tgl]+=$jumlahrealpt[$jenisbyy][$tgl][$umdriver];
							}
							$tab2.="<td align=right>".numb_format($ttlbyy[$jenisbyy][$umdriver])."</td>";
							$tab2.="<td>".$keterangan[$jenisbyy][$umdriver]."</td>";
							$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$piclokasi[$jenisbyy][$umdriver]."'");
							$tab2.="<td>".ucwords(strtolower($nmkar[$piclokasi[$jenisbyy][$umdriver]]))."</td>";
							$tab2.="</tr>";
						}
					}
				}
				
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($conttl+3)." style=font-weight:bold;>SUB TOTAL</td>";
				$gt=0;
				foreach($rangetgl as $tgl){
					if($jenis!='pdf'){
						$tab2.="<td align=right style=font-weight:bold;>".numb_format($ttlbyytgl[$tgl])."</td>";
					}
					$gt+=$ttlbyytgl[$tgl];
				}
				$tab2.="<td align=right style=font-weight:bold;>".numb_format($gt)."</td>";
				$tab2.="<td></td>";
				$tab2.="<td></td>";
				$tab2.="</tr>";
				
			}#TUTUP IF JIKA ADA DATA MUNCULKAN
			
			# KLAIM KARYAWAN
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and tanggungan='1' and sumber='1' order by tanggal asc";
			$res=fetchdata($str);
			$jumlahrealpt=$ttlbyy=array();
			foreach($res as $bar){
				$datajenisbiaya[$bar['jenisbiaya']][$bar['umdriver']]=$bar['jenisbiaya'];
				#$umdriver[$bar['jenisbiaya']]=$bar['umdriver'];
				$tujuandriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tujuandriver'];
				$t4kunj[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tempatkunjungan'];
				$keterangan[$bar['jenisbiaya']][$bar['umdriver']]=$bar['keterangan'];
				$piclokasi[$bar['jenisbiaya']][$bar['umdriver']]=$bar['updateby'];
				$jumlahrealpt[$bar['jenisbiaya']][$bar['tanggal']][$bar['umdriver']]+=$bar['jumlah'];
				$ttlbyy[$bar['jenisbiaya']][$bar['umdriver']]+=$bar['jumlah'];
			}

			#JIKA ADA DATA MUNCULKAN
			if(count($res)>0){
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=background-color:gray;></td>";
				$tab2.="</tr>";
				
				$no=0;
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=font-weight:bold;>Reimburse / Klaim :</td>";
				$tab2.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy => $valumdriver){
					foreach($valumdriver as $umdriver =>$jnbyy){
						if($ttlbyy[$jenisbyy][$umdriver]!=0){	
							$no+=1;
							$tab2.="<tr class=rowcontent>";
							$tab2.="<td align=center>".$no."</td>";
							$tab2.="<td align=left>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
							if($dr>0){									
								$tab2.="<td align=left>".$optjnsdriver[$umdriver]."</td>";
								$tab2.="<td align=left>".$optjnsdriver[$tujuandriver[$jenisbyy][$umdriver]]."</td>";
							}
							$tab2.="<td align=left>".$t4kunj[$jenisbyy][$umdriver]."</td>";
							foreach($rangetgl as $tgl){
								if($jenis!='pdf'){
									$tab2.="<td align=right>".numb_format($jumlahrealpt[$jenisbyy][$tgl][$umdriver])."</td>";
								}
								$ttlbyytgl[$tgl]+=$jumlahrealpt[$jenisbyy][$tgl][$umdriver];
							}
							$tab2.="<td align=right>".numb_format($ttlbyy[$jenisbyy][$umdriver])."</td>";
							$tab2.="<td>".$keterangan[$jenisbyy][$umdriver]."</td>";
							$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$piclokasi[$jenisbyy][$umdriver]."'");
							$tab2.="<td>".ucwords(strtolower($nmkar[$piclokasi[$jenisbyy][$umdriver]]))."</td>";
							$tab2.="</tr>";
						}
					}
				}
				
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($conttl+3)." style=font-weight:bold;>SUB TOTAL</td>";
				$gt=0;
				foreach($rangetgl as $tgl){
					if($jenis!='pdf'){
						$tab2.="<td align=right style=font-weight:bold;>".numb_format($ttlbyytgl[$tgl])."</td>";
					}
					$gt+=$ttlbyytgl[$tgl];
				}
				$tab2.="<td align=right style=font-weight:bold;>".numb_format($gt)."</td>";
				$tab2.="<td></td>";
				$tab2.="<td></td>";
				$tab2.="</tr>";
				
			}#TUTUP IF JIKA ADA DATA MUNCULKAN
			
			# VERIFIKASI
			$str="select * from ".$dbname.".sdm_pjdinasdt where notransaksi='".$notransaksi."' and tanggungan='1' and sumber='1' and statusverifikasihrd='1' and jumlahhrd!='0' order by tanggal asc";
			$res=fetchdata($str);
			$jumlahrealpt=$ttlbyy=array();
			foreach($res as $bar){
				$datajenisbiaya[$bar['jenisbiaya']][$bar['umdriver']]=$bar['jenisbiaya'];
				#$umdriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['umdriver'];
				$tujuandriver[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tujuandriver'];
				$t4kunj[$bar['jenisbiaya']][$bar['umdriver']]=$bar['tempatkunjungan'];
				$keterangan[$bar['jenisbiaya']][$bar['umdriver']]=$bar['keterangan'];
				$piclokasi[$bar['jenisbiaya']][$bar['umdriver']]=$bar['updateby'];
				$jumlahrealpt[$bar['jenisbiaya']][$bar['tanggal']][$bar['umdriver']]+=$bar['jumlahhrd'];
				$ttlbyy[$bar['jenisbiaya']][$bar['umdriver']]+=$bar['jumlahhrd'];
			}
			$nmhrd=makeOption($dbname,'sdm_pjdinasht','notransaksi,namahrd',"notransaksi='".$notransaksi."'");
			#JIKA ADA DATA MUNCULKAN
			if(count($res)>0){
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=background-color:gray;></td>";
				$tab2.="</tr>";
				
				$no=0;
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($con)." style=font-weight:bold;>Verifikasi (dibayarkan) :</td>";
				$tab2.="</tr>";
				$ttlbyytgl=array();
				foreach($datajenisbiaya as $jenisbyy => $valumdriver){
					foreach($valumdriver as $umdriver =>$jnbyy){
						if($ttlbyy[$jenisbyy][$umdriver]!=0){	
						
							$no+=1;
							$tab2.="<tr class=rowcontent>";
							$tab2.="<td align=center>".$no."</td>";
							$tab2.="<td align=left>".ucwords(strtolower($optjns[$jenisbyy]))."</td>";
							if($dr>0){									
								$tab2.="<td align=left>".$optjnsdriver[$umdriver]."</td>";
								$tab2.="<td align=left>".$optjnsdriver[$tujuandriver[$jenisbyy][$umdriver]]."</td>";
							}
							$tab2.="<td align=left>".$t4kunj[$jenisbyy][$umdriver]."</td>";
							foreach($rangetgl as $tgl){
								if($jenis!='pdf'){
									$tab2.="<td align=right>".numb_format($jumlahrealpt[$jenisbyy][$tgl][$umdriver])."</td>";
								}
								$ttlbyytgl[$tgl]+=$jumlahrealpt[$jenisbyy][$tgl][$umdriver];
							}
							$tab2.="<td align=right>".numb_format($ttlbyy[$jenisbyy][$umdriver])."</td>";
							$tab2.="<td></td>";
							$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$piclokasi[$jenisbyy][$umdriver]."'");
							$tab2.="<td>".ucwords(strtolower($nmkar[$piclokasi[$jenisbyy][$umdriver]]))."</td>";
							$tab2.="</tr>";
						}
					}
				}
				
				$tab2.="<tr class=rowcontent>";
				$tab2.="<td colspan=".($conttl+3)." style=font-weight:bold;>SUB TOTAL</td>";
				$gt=0;
				foreach($rangetgl as $tgl){
					if($jenis!='pdf'){
						$tab2.="<td align=right style=font-weight:bold;>".numb_format($ttlbyytgl[$tgl])."</td>";
					}
					$gt+=$ttlbyytgl[$tgl];
				}
				$tab2.="<td align=right style=font-weight:bold;>".numb_format($gt)."</td>";
				$tab2.="<td></td>";
				$tab2.="<td></td>";
				$tab2.="</tr>";
			}#TUTUP IF JIKA ADA DATA MUNCULKAN
			
			$tab2.="</tbody>";
			$tab2.="</table>";
			
			
			#FILE UPLOAD BIAYA
			if($jenis!='pdf'){				
				$tab2.="<br>";
			}
			$fontsize="";
			if($jenis=='pdf'){				
				$fontsize="10px";
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=0 cellspacing=1 border=0 class=sortable";
			}
			$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
			$optjns['realkeg']='Realisasi Kegiatan';
			
			$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' and jenisbiaya!='realkeg'";
			$res=fetchData($str);
			if(!empty($res)){
				$tab2.="<label style=font-weight:bold;font-family:sans-serif;font-size:".$fontsize.";>File Upload (biaya)</label>
				<table ".$style.">
				<thead><tr class=rowheader>";
				$tab2.="<td align=center width=20px style=font-weight:bold;>No</td>";
				if($jenis=='html'){
					$tab2.="<td align='center' style=font-weight:bold;>File Type</td>";
				}
					$tab2.="<td align='center' style=font-weight:bold;>".$_SESSION['lang']['jenisbiaya']."</td>
						<td align='center' style=font-weight:bold;>Jenis</td>
						<td align='center' style=font-weight:bold;>Filename</td>";
				if($jenis=='html'){
					$tab2.="<td align='center' style=font-weight:bold;>Action</td>";
				}
				$tab2.="</tr>
				</thead>";
				$tab2.="<tbody>";
				$no=0;
				foreach($res as $key=>$val){
					$no++;
					$tab2.="<tr class=rowcontent>
							<td style='text-align:center'>".$no."</td>";
					$icon=seticonfile($val['formaticon']);
					if($jenis=='html'){
					$tab2.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
						</td>";
					}
					$nfile = $val['namafile'];
					$tab2.="<td style='text-align:left'>".ucwords(strtolower($optjns[$val['jenisbiaya']]))."</td>";
					$tab2.="<td style='text-align:left'>".ucwords(strtolower($val['jenis']))."</td>";
					$tab2.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>";
					if($jenis=='html'){						
						$tab2.="<td align=center width=20px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a></td>";
					}
					$tab2."</tr>";
				}
			}
			
			
			$tab2.="</tbody>";
			$tab2.="</table>";
		}#tutup id $data
		
		
		#DATA AGENDA
		$str="select * from ".$dbname.".sdm_pjdinasdt2 where notransaksi='".$notransaksi."' order by tanggal asc";
		$res=fetchdata($str);
		if(count($res)>0){
			#JIKA ADA DATA MUNCULKAN
			if($jenis!='pdf'){				
				$tab3.="<br>";
			}
			$tab3.="<div style='page-break-before: always;'></div>";
				$tab3.="
				<table cellspacing=0 border=0 width=100% style='text-align:center'>
					<tr>
						<td style=font-weight:bold;font-family:sans-serif;text-decoration:underline;>KEGIATAN PERJALANAN DINAS</td>
					</tr>
				</table>";
			$fontsize="10px";
			if($jenis=='pdf'){				
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=0 cellspacing=1 border=0 class=sortable width=100%";
			}
			$tab3.="<br><table ".$style." width=100%>
			<thead><tr class=rowheader>";
			$tab3.="<td align=center width=20px style=font-weight:bold;>No</td>
				<td align=center width=55px style=font-weight:bold;>".$_SESSION['lang']['tanggal'] . "</td>
				<td align=center width=50px style=font-weight:bold;>".$_SESSION['lang']['hari'] . "</td>
				<td align=center width=55px style=font-weight:bold;>".$_SESSION['lang']['jenis'] . "</td>
				<td align=center style=font-weight:bold;>".$_SESSION['lang']['location'] . "</td>
				<td align=center style=font-weight:bold;>".$_SESSION['lang']['kegiatan'] . "</td>
				<td align=center style=font-weight:bold;>Koordinasi<br>Dengan</td>
			</tr>
			</thead>";
			$no=0;
			$data=array();
			$arrjns=array();
			foreach($res as $bar){
				$data[$bar['tanggal']]=$bar['tanggal'];
				$lok[$bar['tanggal']][$bar['jenis']]=$bar['lokasi'];
				$ketx[$bar['tanggal']][$bar['jenis']]=$bar['keterangan'];
				$koo[$bar['tanggal']][$bar['jenis']]=$bar['koordinasidengan'];
				$upd[$bar['tanggal']][$bar['jenis']]=$bar['updateby'];
				$tglupd[$bar['tanggal']][$bar['jenis']]=$bar['updatetime'];
				if($bar['statusconfrim']==1){
					$sta='Ya';
				}else{
					$sta='Tidak';
				}
				$stsc[$bar['tanggal']][$bar['jenis']]=$sta;
			}
			
			$arrjns=getEnum($dbname,'sdm_pjdinasdt2','jenis');
			$no=0;
			foreach($data as $tglagen){
				$n="";
				if(hari($tglagen,'ID')=='Minggu'){
					$n="style=color:red";
				}
				$no+=1;
				$tab3.="<tr class=rowcontent style=vertical-align:top>";
				$tab3.="<td align=center rowspan=3>".$no."</td>";
				$tab3.="<td align=center rowspan=3>".$tglagen."</td>";
				$tab3.="<td align=center rowspan=3 ".$n.">".hari($tglagen,'ID')."</td>";
				foreach($arrjns as $jns){
					if($jns=='renc'){
						$tab3.="<td align=left style=font-style:italic;background-color:#CDFED1;>".$jns."</td>";
						$tab3.="<td align=left >".$lok[$tglagen][$jns]."</td>";
						$tab3.="<td align=left >".nl2br($ketx[$tglagen][$jns])."</td>";
						$tab3.="<td align=left >".$koo[$tglagen][$jns]."</td>";
					}
					if($jns=='conf'){
						$optnm=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$upd[$tglagen][$jns]."'");
						$tab3.="<tr class=rowcontent style=vertical-align:top>";
						$tab3.="<td align=left style=font-style:italic;background-color:#E3FFB1;color:blue;>".$jns."</td>";
						$tab3.="<td align=left>".$lok[$tglagen][$jns]."</td>";
						$tab3.="<td align=left>".nl2br($ketx[$tglagen][$jns])."</td>";
						if($stsc[$tglagen][$jns]!=''){
							$color="";
							if($stsc[$tglagen][$jns]!=1){$color="style=color:red;";}
							$tab3.="<td align=left ".$color.">Konfirmasi : ".$stsc[$tglagen][$jns]."<br>Oleh : ".$optnm[$upd[$tglagen][$jns]]."<br>Tanggal : ".tanggalnormal($tglupd[$tglagen][$jns])."</td>";
						}else{							
							$tab3.="<td></td>";
						}
						$tab3.="</tr>";
					}
					
					if($jns=='real'){
						$tab3.="<tr class=rowcontent style=vertical-align:top>";
						$tab3.="<td align=left style=font-style:italic;background-color:green;color:white;>".$jns."</td>";
						$tab3.="<td align=left>".$lok[$tglagen][$jns]."</td>";
						$tab3.="<td align=left>".nl2br($ketx[$tglagen][$jns])."</td>";
						$tab3.="<td align=left>".$koo[$tglagen][$jns]."</td>";
						$tab3.="</tr>";
					}
				}
				$tab3.="</tr>";
			}
			$tab3.="</tbody>";
			$tab3.="</table>";
			
			
		}#TUTUP IF JIKA ADA DATA MUNCULKAN
		
		
		# REAL
		$str="select * from ".$dbname.".file_pjdinas where notransaksi = '".$notransaksi."' and jenisbiaya='realkeg'";
		$res=fetchdata($str);
		if(count($res)>0){
			
			#FILE UPLOAD KEGIATAN
			if($jenis!='pdf'){				
				$tab4.="<br>";
			}
			$fontsize="";
			if($jenis=='pdf'){				
				$fontsize="10px";
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=0 cellspacing=1 border=0 class=sortable";
			}
			$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
			$optjns['realkeg']='Realisasi Kegiatan';
			
			if(!empty($res)){
				$tab4.="<label style=font-weight:bold;font-family:sans-serif;font-size:".$fontsize.";>File Upload (realisasi kegiatan)</label>
				<table ".$style.">
				<thead><tr class=rowheader>";
				$tab4.="<td align=center width=20px style=font-weight:bold;>No</td>";
				if($jenis=='html'){
					$tab4.="<td align='center' style=font-weight:bold;>File Type</td>";
				}
					$tab4.="<td align='center' style=font-weight:bold;>".$_SESSION['lang']['jenisbiaya']."</td>
						<td align='center' style=font-weight:bold;>Filename</td>";
				if($jenis=='html'){
					$tab4.="<td align='center' style=font-weight:bold;>Action</td>";
				}
				$tab4.="</tr>
				</thead>";
				$tab4.="<tbody>";
				$no=0;
				foreach($res as $key=>$val){
					$no++;
					$tab4.="<tr class=rowcontent>
							<td style='text-align:center'>".$no."</td>";
					$icon=seticonfile($val['formaticon']);
					if($jenis=='html'){
					$tab4.="<td style='text-align:center'>
							<a href='".$path.$val['namafile']."' download><img src=".$icon." class=zImgBtn></a>
						</td>";
					}
					$nfile = $val['namafile'];
					$tab4.="<td style='text-align:left'>".ucwords(strtolower($optjns[$val['jenisbiaya']]))."</td>";
					$tab4.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>";
					if($jenis=='html'){						
						$tab4.="<td align=center width=20px><a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a></td>";
					}
					$tab4."</tr>";
				}
			}
			
			
			$tab4.="</tbody>";
			$tab4.="</table>";
			
			
		}#TUTUP IF JIKA ADA DATA MUNCULKAN


		# ABSENSI DARI MOBILE
		$str="select * from ".$dbname.".mobile_trx_sdm_pjdinas where notransaksi = '".$notransaksi."'";
		$res=fetchdata($str);
		if(count($res)>0){
			
			#FILE UPLOAD KEGIATAN
			if($jenis!='pdf'){				
				$tab4.="<br>";
			}
			$fontsize="";
			if($jenis=='pdf'){				
				$fontsize="10px";
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable style=\"font-family:sans-serif;font-size:".$fontsize."\"";
			}elseif($jenis=='excel'){
				$style="cellpadding=1 cellspacing=0 border=1 class=sortable";
			}else{
				$style="cellpadding=0 cellspacing=1 border=0 class=sortable";
			}
			$optjns=makeOption($dbname,'sdm_5jenisbiayapjdinas','id,keterangan');
			$optjns['realkeg']='Realisasi Kegiatan';
			
			if(!empty($res)){
				$tab4.="<label style=font-weight:bold;font-family:sans-serif;font-size:".$fontsize.";>Detail Absensi</label>
				<table ".$style.">
				<thead><tr class=rowheader>";
				$tab4.="<td align=center width=50px style=font-weight:bold;>No</td>";
				if($jenis=='html'){
					$tab4.="<td align='center' width=20px style=font-weight:bold;>Latitude</td>";
				}
					$tab4.="<td align='center' width=20px style=font-weight:bold;>longitude</td>";
				if($jenis=='html'){
					$tab4.="<td align='center' width=20px style=font-weight:bold;>".$_SESSION['lang']['photo']."</td>";
					$tab4.="<td align='center'  style=font-weight:bold;>Jam Absen</td>";
					$tab4.="<td align='center' width=20px style=font-weight:bold;>".$_SESSION['lang']['keterangan']."</td>";
				}
				$tab4.="</tr>
				</thead>";
				$tab4.="<tbody>";
				$no=0;
				foreach($res as $key=>$val){
					$no++;
					$tab4.="<tr class=rowcontent>
							<td style='text-align:center'>".$no."</td>";
					$tab4.="<td style='text-align:center'>".$val['latitude']."</td>";
					$tab4.="<td style='text-align:center'>".$val['longitude']."</td>";
					$tab4.="<td style='text-align:center'>
					<a href=mobile/".$val['photo']." download><img src=mobile/".$val['photo']." class=zImgBtn></a>
					</td>";
					$tab4.="<td style='text-align:center'>".$val['tanggal_absen']."</td>";
					$tab4.="<td style='text-align:center'>".$val['keterangan']."</td>";
					$tab4."</tr>";
				}
			}
			
			
			$tab4.="</tbody>";
			$tab4.="</table>";
			
			
		}#TUTUP IF JIKA ADA DATA MUNCULKAN
		
			
		
		if($jenis=='pdf'){		
			$dompdf = new Dompdf();
			$dompdf->load_html($tab.$tab2.$tab3.$tab4);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();
			#$font = Font_Metrics::get_font("helvetica", "bold");
			$canvas->page_text(16, 800, "Page: {PAGE_NUM} of {PAGE_COUNT}",'', 8, array(0,0,0));
			$dompdf->stream("perjalanan_dinas",array("Attachment"=>0));
		}elseif($jenis=='excel'){
			$nop = "perjalanan_dinas.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			//$xls->addSheet("surat_perjalanan_dinas", $tab);
			$xls->addSheet("biaya_perjalanan_dinas", $tab2);
			$xls->addSheet("kegiatan_perjalanan_dinas", $tab3.$tab4);
			#$xls->addSheet("real_kegiatan_perjalanan_dinas", $tab4);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{
			echo $tab.$tab2.$tab3.$tab4;
		}
	break;
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