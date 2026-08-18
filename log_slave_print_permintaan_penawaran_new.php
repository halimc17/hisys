<?php
error_reporting(0);
$mobileValid = false;
if (isset($_POST['par']) or isset($_GET['par'])) {
	$validasiPostMobile = explode(" ", $_POST['par']);
	$validasiGetMobile = explode(" ", $_GET['par']);
	if ($validasiGetMobile[0] == "owlApp" or $validasiPostMobile[0] == "owlApp") {
		$mobileValid = true;
	};
}

if ($mobileValid == false) { //untuk redirec dari mobile
	require_once('master_validation.php');
}
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');
require_once('lib/zLib.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');
require_once('dompdf/autoload.inc.php');

use Dompdf\Dompdf;

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];

$notransaksi = $column;
$urlefil = checkPostGet('urlefil', '0');

$tab = "";

$optnmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optnamabarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optnamasupplier = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$opttop = makeOption($dbname, 'log_5syaratbayar', 'kode,keterangan');
$franconame = makeOption($dbname, 'setup_franco', 'id_franco,franco_name');

$kary_id =  makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan');
$jabatan =  makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');

// ambil kelompok barang
$str_0 = "select kelompok from " . $dbname . ".log_5klbarang where kode in (select substr(kodebarang,1,3) from " . $dbname . ".log_permintaanhargadt where norph='" . $notransaksi . "')";
$res_0 = fetchdata($str_0);
$kelompok_0 = $res_0[0]['kelompok'];

// log_permintaanhargadt
$str_1 = "select * from " . $dbname . ".log_permintaanhargadt where norph='" . $notransaksi . "' ";
$res_1 = fetchdata($str_1);
$tahunverifikasi = substr($res_1[0]['tanggalverifikasi'], 0, 4);
$tglverifikasi = $res_1[0]['tanggalverifikasi'];
$nomor_dph = $res_1[0]['nomor'];
$verificator_1 = $res_1[0]['verificator'];

// log_permintaanhargaht
$str_1a = "select * from " . $dbname . ".log_perintaanhargaht where nomor='" . $nomor_dph . "' ";
$res_1a = fetchdata($str_1a);
$purchase_1 = $res_1a[0]['purchaser'];

// jumlah supplier
$str_2 = "select * from " . $dbname . ".log_perintaanhargaht where nomor='" . $nomor_dph . "' group by supplierid ";
$res_2 = fetchdata($str_2);
$total_supplier = count($res_2);

// log_permintaanhargadt
$str_3 = "SELECT b.supplierid,a.*
		FROM " . $dbname . ".`log_permintaanhargadt` a left join " . $dbname . ".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut
		WHERE a.nomor = '" . $nomor_dph . "' group by a.kodebarang";
$res_3 = fetchdata($str_3);

// data persupplier
$str_4 = "SELECT b.supplierid,a.*
		FROM " . $dbname . ".`log_permintaanhargadt` a left join " . $dbname . ".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut
		WHERE a.nomor = '" . $nomor_dph . "' group by b.supplierid,a.nomor,a.nourut order by nourut";
$res_4 = fetchdata($str_4);

// all data
$str_5 = "SELECT b.supplierid,a.*
		FROM " . $dbname . ".`log_permintaanhargadt` a left join " . $dbname . ".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut
		WHERE a.nomor = '" . $nomor_dph . "' order by nourut";
$res_5 = fetchdata($str_5);

// HARGA TERSENDAH
$str_6 = "SELECT b.supplierid, a.* FROM  " . $dbname . ".`log_permintaanhargadt` a 
LEFT JOIN  " . $dbname . ".log_perintaanhargaht b  ON  a.nomor = b.nomor  AND a.nourut = b.nourut 
JOIN ( SELECT nomor, kodebarang, MIN(harga) AS harga_terendah FROM " . $dbname . ".`log_permintaanhargadt` WHERE nomor = '" . $nomor_dph . "' GROUP BY nomor, kodebarang) 
min_price  ON  a.nomor = min_price.nomor 
AND a.kodebarang = min_price.kodebarang  
AND a.harga = min_price.harga_terendah 
WHERE  a.nomor = '" . $nomor_dph . "' ";
$res_6 = fetchdata($str_6);

// nilai keseluruhan
// $str_7 = "SELECT sum(jumlah*harga) as nilai_menang FROM " . $dbname . ".`log_permintaanhargadt` WHERE nomor = '".$nomor_dph."' and flag='1'";
// $res_7 = fetchdata($str_7);

$str_7 = "SELECT ppn,jumlah,harga FROM " . $dbname . ".`log_permintaanhargadt` a left join " . $dbname . ".log_perintaanhargaht b on a.nomor=b.nomor
WHERE a.nomor = '" . $nomor_dph . "' and a.flag='1' and a.score='1' group by a.nomor,a.nourut,a.kodebarang";
$res_7 = fetchdata($str_7);
$nilai_menang = 0;
$nilai_menang_ppn = 0;
$ppn_menang = 0;
foreach ($res_7 as $bar) {
	$nilai_menang = $bar['jumlah'] * $bar['harga'];
	$ppn_menang = $bar['ppn'];
	if ($ppn_menang > 0) {
		$nilai_menang_ppn += $nilai_menang * $ppn_menang / 100;
		$nilai_menang_s += $nilai_menang + ($nilai_menang * $ppn_menang / 100);
	} else {
		$nilai_menang_s += $nilai_menang;
	}
}

// SCORE
$arr_score = array();
$str_8 = "SELECT * FROM " . $dbname . ".log_perintaanhargaht
WHERE nomor = '" . $nomor_dph . "' group by nomor,supplierid,nourut;";
$res_8 = fetchdata($str_8);
foreach ($res_8 as $bar) {
	// score
	$arr_score[$bar['supplierid']]['score_harga'] = $bar['nilai1s'];
	$arr_score[$bar['supplierid']]['persen_harga'] = $bar['nilai1f'];
	// Availability
	$arr_score[$bar['supplierid']]['score_availability'] = $bar['nilai2s'];
	$arr_score[$bar['supplierid']]['persen_availability'] = $bar['nilai2f'];
	// Quality/ Performance/ Integrity
	$arr_score[$bar['supplierid']]['score_qpi'] = $bar['nilai3s'];
	$arr_score[$bar['supplierid']]['persen_qpi'] = $bar['nilai3f'];
	// Service
	$arr_score[$bar['supplierid']]['score_service'] = $bar['nilai4s'];
	$arr_score[$bar['supplierid']]['persen_service'] = $bar['nilai4f'];
	// Other
	$arr_score[$bar['supplierid']]['score_other'] = $bar['nilai5s'];
	$arr_score[$bar['supplierid']]['persen_other'] = $bar['nilai5f'];
}

$max_scores = array();

// Cari score_harga maksimal terlebih dahulu
$max_score_harga = -INF;

foreach ($arr_score as $key => $value) {
	// harga
	if ($value["score_harga"] > $max_score_harga) {
		$max_score_harga = $value["score_harga"];
	}
	// availability
	if ($value["score_availability"] > $max_score_availability) {
		$max_score_availability = $value["score_availability"];
	}
	// Quality/ Performance/ Integrity
	if ($value["score_qpi"] > $max_score_qpi) {
		$max_score_qpi = $value["score_qpi"];
	}
	// Service
	if ($value["score_service"] > $max_score_service) {
		$max_score_service = $value["score_service"];
	}
	// other
	if ($value["score_other"] > $max_score_other) {
		$max_score_other = $value["score_other"];
	}
}

// Buat array supplier => score_harga dengan nilai score_harga paling besar
foreach ($arr_score as $key => $value) {
	// harga
	if ($value["score_harga"] == $max_score_harga) {
		$max_scores['harga'][$optnamasupplier[$key]] = $value["score_harga"];
	}
	// availability
	if ($value["score_availability"] == $max_score_availability) {
		$max_scores['availability'][$optnamasupplier[$key]] = $value["score_availability"];
	}
	// Quality/ Performance/ Integrity
	if ($value["score_qpi"] == $max_score_qpi) {
		$max_scores['qpi'][$optnamasupplier[$key]] = $value["score_qpi"];
	}
	// Service
	if ($value["score_service"] == $max_score_service) {
		$max_scores['service'][$optnamasupplier[$key]] = $value["score_service"];
	}
	// other
	if ($value["score_other"] == $max_score_other) {
		$max_scores['other'][$optnamasupplier[$key]] = $value["score_other"];
	}
}

## GET APPROVAL
$aprvkar = "";
$aprvtgl = "";
$str9 = "select * from " . $dbname . ".approval where notransaksi='" . $nomor_dph . "' order by level desc limit 1";
$res9 = fetchdata($str9);
if ($res9[0]['status'] == '1') {
	$aprvkar = $optnmkar[$res9[0]['karyawanid']];
	$aprvkar_karyid = $res9[0]['karyawanid'];
	$aprvtgl = $res9[0]['tanggal'];
	$komentar_aproval = $res9[0]['komentar'];
	// $expaprvtgl = explode("-",substr($res9[0]['tanggal'],0,10));
	// $bulanaprv=$expaprvtgl[1];
	// $aprvtgl="Date : ".$expaprvtgl[2].' '.getnmbln($bulanaprv).' '.$expaprvtgl[0];
}


// create array
$Aperbandingan = array();
foreach ($res_5 as $bar) {
	$Aperbandingan[$bar['kodebarang']][$bar['supplierid']][$bar['nourut']]['jumlah'] = $bar['jumlah'];
}

// echo "<pre>";
// print_r($Aperbandingan);
// exit;
##Get Supplier & Purchaser
$arrStatus_m = $jumlah_brg = $arrSup = $arrPur = array();
$str = "select * from " . $dbname . ".log_perintaanhargaht where nomor in (select nomor from " . $dbname . ".log_permintaanhargadt where norph='" . $notransaksi . "') and flag='1' and tolakrph='0'";
$res = fetchdata($str);
foreach ($res as $key => $val) {
	$optNmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $val['supplierid'] . "'");
	$optNmPur = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $val['purchaser'] . "'");
	$arrSup[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
	$arrNmSup[$val['purchaser']][$val['supplierid']] = $optNmSup[$val['supplierid']];
	$arrPur[$val['purchaser']] = $val['purchaser'];
	$arrNmKar[$val['purchaser']] = $optNmPur[$val['purchaser']];
	$arrCountSup[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
}

##Get Nomor RPH
// data yg menang
$arrKdBrg0 = array();
$str = "select b.ppn,a.kodebarang,a.nopp,a.jumlah,a.harga,a.nourut,a.ongkir,b.ongkir as totalongkir,b.purchaser,b.diskonpersen,b.nilaidiskon,b.nomor,b.supplierid,a.merk,a.spec,b.matauang,b.kurs,b.tgldari,b.tglsmp,b.pbbkb,b.sisbayar2,b.id_franco,b.catatan, b.tanggal, b.durasipengiriman, b.durasipekerjaan, b.garansiproduk, b.posisistok, b.asuransi from " . $dbname . ".log_permintaanhargadt a
			left join " . $dbname . ".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut and b.nomor in (select nomor from " . $dbname . ".log_permintaanhargadt where norph='" . $notransaksi . "')
			where a.flag='1' and b.tolakrph='0' order by b.nourut asc, a.kodebarang";
$res = fetchdata($str);
$nourut = 0;
foreach ($res as $key => $val) {
	$arrStatus_m[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['harga'] = $val['harga'];
	$arrStatus_m[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['supplier'] = $val['supplierid'];
	$arrStatus_m[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['jumlah'] = $val['jumlah'];
	$arrStatus_m[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['ppn'] = $val['ppn'];
	$franco_m[$val['purchaser']][$val['supplierid']]['lokasikirim'] = $val['id_franco'];
	$syaratbayar_m[$val['purchaser']][$val['supplierid']]['syaratbayar'] = $val['sisbayar2'];
	$keterangan_m[$val['purchaser']][$val['supplierid']]['keterangan'] = $val['catatan'];
	$arrSupplier_m[$val['supplierid']] = $val['supplierid'];
}

$arrKdBrg1 = array();
$str = "select b.tgltempopembayaran,b.deliverytime,b.ppn,a.kodebarang,a.nopp,a.jumlah,a.harga,a.nourut,a.ongkir,b.ongkir as totalongkir,b.purchaser,b.diskonpersen,b.nilaidiskon,b.nomor,b.supplierid,a.merk,a.spec,b.matauang,b.kurs,b.tgldari,b.tglsmp,b.pbbkb,b.sisbayar2,b.id_franco,b.catatan, b.tanggal, b.durasipengiriman, b.durasipekerjaan, b.garansiproduk, b.posisistok, b.asuransi from " . $dbname . ".log_permintaanhargadt a
			left join " . $dbname . ".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut and b.nomor in (select nomor from " . $dbname . ".log_permintaanhargadt where norph='" . $notransaksi . "')
			where a.flag='1' and b.tolakrph='0' order by b.nourut asc, a.kodebarang";
$res = fetchdata($str);
$nourut = 0;
foreach ($res as $key => $val) {

	if ($tglverifikasi != '' || $tglverifikasi != '0000-00-00') {
		$tanggal_hasil_diperlukan = date("Y-m-d", strtotime($tglverifikasi . " +" . $val['deliverytime'] . " days"));
	} else {
		$tanggal_hasil_diperlukan = '';
	}

	$arrStatus_x[$val['purchaser']][$val['nopp']][$val['kodebarang']]['harga'] = $val['harga'];
	$arrStatus_x[$val['purchaser']][$val['nopp']][$val['kodebarang']]['supplier'] = $val['supplierid'];
	$arrStatus_x[$val['purchaser']][$val['nopp']][$val['kodebarang']]['jumlah'] = $val['jumlah'];
	$arrStatus_x[$val['purchaser']][$val['nopp']][$val['kodebarang']]['ppn'] = $val['ppn'];
	$arrStatus_x[$val['purchaser']][$val['nopp']][$val['kodebarang']]['posisistok'] = $val['posisistok'];
	$arrStatus_x[$val['purchaser']][$val['nopp']][$val['kodebarang']]['tgltempopembayaran'] = $val['tgltempopembayaran'];
	$arrStatus_x[$val['purchaser']][$val['nopp']][$val['kodebarang']]['deliverytime'] = $tanggal_hasil_diperlukan;
	$franco_x[$val['purchaser']]['lokasikirim'] = $val['id_franco'];
	$syaratbayar_x[$val['purchaser']]['syaratbayar'] = $val['sisbayar2'];
	$keterangan_x[$val['purchaser']]['keterangan'] = $val['catatan'];
	// $arrSupplier_x = $val['supplierid'];
}

// echo "<pre>";
// print_r($arrStatus_x);
// exit;

// akhir daa yg menang
$arrKdBrg = array();
$str = "select a.kodebarang,a.nopp,a.jumlah,a.harga,a.nourut,a.ongkir,b.ongkir as totalongkir,b.purchaser,b.diskonpersen,b.nilaidiskon,b.nomor,b.supplierid,a.merk,a.spec,b.matauang,b.kurs,b.tgldari,b.tglsmp,b.pbbkb,b.sisbayar2,b.id_franco,b.catatan, b.tanggal, b.durasipengiriman, b.durasipekerjaan, b.garansiproduk, b.posisistok, b.asuransi from " . $dbname . ".log_permintaanhargadt a
			left join " . $dbname . ".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut and b.nomor in (select nomor from " . $dbname . ".log_permintaanhargadt where norph='" . $notransaksi . "')
			where b.tolakrph='0' order by b.nourut asc, a.kodebarang";
$res = fetchdata($str);
$nourut = 0;
foreach ($res as $key => $val) {
	$nourut++;
	$tglrph = $val['tanggal'];
	$nopp = $val['nopp'];
	$arrKdBrg[$val['kodebarang']] = $val['kodebarang'];
	$arrHarga[$val['kodebarang']] = $hrgtrk;
	$unit = explode("/", $val['nopp'])[4];
	$kodept = makeOption($dbname, "organisasi", "kodeorganisasi,induk", "kodeorganisasi='{$unit}'")[$unit];

	$jumlah_brg[$val['kodebarang']] = $val['jumlah'];

	$arrSupplier2[$val['purchaser']][$val['supplierid']] = $val['supplierid'];
	$arrSupplier[$val['supplierid']] = $val['supplierid'];

	if ($val['supplierid'] == $supplierid) {
		$style[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']] = "background-color:#D6F097";
	}

	$optMrk = makeOption($dbname, 'log_5merkbaranght', 'idmerk,merk', "idmerk='" . $val['merk'] . "'");

	$franco_[$val['purchaser']][$val['supplierid']]['lokasikirim'] = $val['id_franco'];
	$syaratbayar_[$val['purchaser']][$val['supplierid']]['syaratbayar'] = $val['sisbayar2'];
	$keterangan_[$val['purchaser']][$val['supplierid']]['keterangan'] = $val['catatan'];

	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['merk'] = $optMrk[$val['merk']];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['spec'] = $val['spec'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['jumlah'] = $val['jumlah'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['harga'] = $val['harga'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['hargadiskon'] = ($val['diskonpersen'] == 0 ? $val['harga'] : ($val['harga'] - $val['nilaidiskon']));
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nodph'] = $val['nomor'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nourut'] = $val['nourut'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['diskonpersen'] = $val['diskonpersen'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['nilaidiskon'] = ($val['diskonpersen'] == 0 ? '0' : ($val['nilaidiskon']));
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['pbbkb'] = $val['pbbkb'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['matauang'] = $val['matauang'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['kurs'] = $val['kurs'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['tgldari'] = $val['tgldari'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['tglsmp'] = $val['tglsmp'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['syaratbayar'] = $val['sisbayar2'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['lokasikirim'] = $val['id_franco'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['keterangan'] = $val['catatan'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['durasipengiriman'] = $val['durasipengiriman'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['durasipekerjaan'] = $val['durasipekerjaan'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['garansiproduk'] = $val['garansiproduk'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['posisistok'] = $val['posisistok'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['asuransi'] = $val['asuransi'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['ongkir'] = $val['ongkir'];
	$arrStatus[$val['purchaser']][$val['nopp']][$val['kodebarang']][$val['supplierid']]['totalongkir'] = ($val['ongkir'] * $val['jumlah']);
}


// echo "<pre>";
// print_r($arrStatus);
// exit;

## HEADER KOP
$arrHead = setheadreport('', $kodept);
$arrHeadUnit = setheadreport('', $kodeunit);
$path = $arrHead['logopalma'];
//@page { margin: 20; }
$tab .= "<html>
<style type='text/css'>
	@page {
		margin-top: 50px;
		margin-left: 20px;
		margin-right: 20px;
		margin-bottom: 200px;
		
	}
	footer {
		position: fixed; 
		bottom: 00px; 
		left: 0px; 
		right: 0px;
		height: 0px; 
	}
</style>";

$spasi = "<span style='color:white'>_</span>";

## CREATE TITLE HEADER
$tab .= "
<table width=100%>
	<tr style='text-align:center;font-weight:bold;font-size:16px'>
		<td style='width:100%;font-size:25px'>
			PROPOSAL PENUNJUKAN PEMENANG TENDER " . $kelompok_0 . " TAHUN " . $tahunverifikasi . "
			<br>
			" . getNamaOrg($kodept) . "
		</td>
	</tr>
</table>
<br>";

// bab 1
$tab .= "
<table width=100%>
	<tr style='text-align:left;font-weight:bold;font-size:16px'>
		<td style='width:100%;font-size:25px'>
			I PENDAHULUAN
		</td>
	</tr>
	<tr>
		<ul>
			<li>Unit yang di tenderkan : " . $kelompok_0 . " TAHUN " . $tahunverifikasi . "</li>
			<li>Jumlah Supplier yang di undang = " . $total_supplier . " Supplier </li>
			<li>Jumlah Supplier yang mengikuti tender dan memberikan penawaran harga = " . $total_supplier . " Supplier </li>
			<li>Penawaran harga dikirim via email sebagaimana terlampir dalam TOR</li>
			<li>Nilai Transaksi keseluruhan <span style='color:blue'>Rp. " . number_format($nilai_menang_s, 2) . " TERBILANG " . terbilang($nilai_menang_s, 3) . "</span></li>
		</ul>
	</tr>
</table>
<table>
	<tr>
		<td style='width:40%'>
			<table style='margin-left:10px'>
				<tr>
					<td>Data Kebutuhan " . $kelompok_0 . " yang ditenderkan (Kg) </td>
				</tr>
			</table>
			<table border=0 cellpadding=5 cellspacing=0 style='margin-left:30px'>
				";
$no_b = 1;
foreach ($res_3 as $bar) {
	$tab .= "<tr>
								<td>" . $no_b++ . "</td>
								<td>" . $optnamabarang[$bar['kodebarang']] . "</td>
								<td style='text-align:right'>" . number_format($bar['jumlah'], 2) . "</td>
							</tr>";
	$total_j_barangpp += $bar['jumlah'];
}
$tab .= "<tr>
								<td></td>
								<td style='font-weight:bold'>Total</td>
								<td style='font-weight:bold;text-align:right'>" . number_format($total_j_barangpp, 2) . "</td>
							</tr>";

$tab .= "</table>
		</td>
		<td style='width:60%;padding-left:30px'>
				<table>
					<tr>
						<td>
							
						</td>
					</tr>
				</table>
		</td>
	</tr>
</table>";


// bab 2
$tab .= "
<table width=100%>
	<tr style='text-align:left;font-weight:bold;font-size:16px'>
		<td style='width:100%;font-size:25px'>
			II DATA REKAPITULASI PENAWARAN HARGA
		</td>
	</tr>
</table>
";
$tab .= "
<table width=100% border=1 cellpadding=0 cellspacing=0 style='text-align:center'>
	<thead>
		<tr>
			<th rowspan=2>NO</th>
			<th rowspan=2>KETERANGAN</th>
			<th rowspan=2>JUMLAH (KG)</th>";

foreach ($arrPur as $val) {
	foreach ($arrSupplier as $val2) {
		if ($val2 == $arrSupplier2[$val][$val2]) {
			$tab .= "<th colspan=2 style='text-align:center'>" . $arrNmSup[$val][$val2] . "</th>";
		}
	}
}
$tab .= "<th rowspan=1 colspan=2 style='background-color:aqua;'>Total Pembelian</th>";
$tab .= "<th rowspan=2>Waktu Penerimaan  " . $kelompok_0 . " </th>";
$tab .= "<th rowspan=2>Stock Point</th>";
$tab .= "<th rowspan=2>Jatuh Tempo</th>";

$tab .= "</tr>";


$tab .= "<tr>";

foreach ($arrPur as $val) {
	foreach ($arrSup[$val] as $val2) {
		$tab .= "
					<th rowspan=1 colspan=1>Harga/Kg</th>
					<th rowspan=1 colspan=1>Total (Rp)</th>
				";
	}
}
$tab .= "
			<th rowspan=1 colspan=1>Harga/Kg</th>
			<th rowspan=1 colspan=1>Total (Rp)</th>
		";

$tab .= "</tr>";

$tab .= "</thead><tbody>";
// $tab.=getdph($norph,$notransaksi,$kd_brg,$supplierid);

$nobrg = 0;
foreach ($arrKdBrg as $val) {
	$str = "select * from " . $dbname . ".log_prapoht where nopp='" . $nopp . "'";
	$res = fetchData($str);
	$tmpTgl = explode('-', $res[0]['tanggal']);
	$tgldari = "01-12-" . $tmpTgl[0];
	$tgldarilalu = "01-01-" . ($tmpTgl[0] - 1);
	$unit = $res[0]['unit'];

	## GET HARGA TERAKHIR
	$hrgtrk = 0;
	$tgltrk = "";
	$strx = "select hargasatuan,tanggal from " . $dbname . ".log_5hargaterakhir where unit='" . $unit . "' and kodebarang='" . $val . "' and status='1'";
	$resx = fetchdata($strx);
	if (count($resx) > 0) {
		$hrgtrk = $resx[0]['hargasatuan'];
	}

	$no++;
	$optNmBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $val . "'");
	$optSatuan = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan', "kodebarang='" . $val . "'");
	$strx = "select satuankonversi from " . $dbname . ".log_prapodt where nopp='" . $nopp . "' and kodebarang='" . $val . "'";
	$resx = fetchdata($strx);
	if ($resx[0]['satuankonversi'] == '' || is_null($resx[0]['satuankonversi'])) {
		$mySatuan = $optSatuan[$val];
	} else {
		$mySatuan = $resx[0]['satuankonversi'];
	}
	$tab .= "<tr class='rowcontent'>
					<td align=center>" . $no . "</td>
					<td colspan=1>" . $optNmBrg[$val] . "</td>
					<td align=right>" . number_format($jumlah_brg[$val], 2) . " " . $spasi . "</td>
					";

	foreach ($arrPur as $val2) {
		foreach ($arrSupplier as $val3) {
			if ($val3 == $arrSupplier2[$val2][$val3]) {
				// if($val3 == $arrSupplier_m[$val3]){
				// 	$style_bg = "style='background-color:yellow;'";
				// }else{
				// 	$style_bg = '';
				// }
				if ($val3 == $arrStatus_m[$val2][$nopp][$val][$val3]['supplier']) {
					$style_bg = "style='background-color:yellow;'";
				} else {
					$style_bg = '';
				}

				$subtotal = ($arrStatus[$val2][$nopp][$val][$val3]['jumlah'] * $arrStatus[$val2][$nopp][$val][$val3]['harga']);
				$tab .= "
								<td align=right " . $style_bg . ">" . number_format($arrStatus[$val2][$nopp][$val][$val3]['harga'], 2) . " " . $spasi . " </td>
								<td align=right " . $style_bg . ">" . number_format($subtotal, 2) . " " . $spasi . " </td>
							";
				$arrSubtotal[$val2][$nopp][$val][$val3]['subtotal'] = ($subtotal + $arrStatus[$val2][$nopp][$val][$val3]['totalongkir']);
			}
		}
	}
	// $nilaippn=0;
	foreach ($arrPur as $val2) {
		if ($val3 == $arrSupplier2[$val2][$val3]) {
			// exit("warning : ".$arrStatus_x[$val2][$nopp][$val]['jumlah']." ");
			$subtotal = ($arrStatus[$val2][$nopp][$val]['jumlah'] * $arrStatus[$val2][$nopp][$val]['harga']);
			$subtotal_x = ($arrStatus_x[$val2][$nopp][$val]['jumlah'] * $arrStatus_x[$val2][$nopp][$val]['harga']);
			$tab .= "
								<td align=right style='background-color:green;color:white;border:1px solid black'>" . number_format($arrStatus_x[$val2][$nopp][$val]['harga'], 2) . " <span style='color:green'>_</span> </td>
								<td align=right style='background-color:green;color:white;border:1px solid black'>" . number_format($subtotal_x, 2) . " <span style='color:green'>_</span> </td>
								<td>" . $arrStatus_x[$val2][$nopp][$val]['deliverytime'] . "</td>
								<td>" . $arrStatus_x[$val2][$nopp][$val]['posisistok'] . "</td>
								<td>" . $arrStatus_x[$val2][$nopp][$val]['tgltempopembayaran'] . "</td>
							";
			// if($arrStatus_x[$val2][$nopp][$val]['ppn'] > 0){
			// 	$nilaippn +=  $subtotal_x * $arrStatus_x[$val2][$nopp][$val]['ppn'] / 100; 
			// 	$perenppn = $arrStatus_x[$val2][$nopp][$val]['ppn']."%";
			// }
			$arrSubtotal_m += $subtotal_x;
		}
	}


	// foreach($arrPur as $val2){
	// 	foreach($arrSupplier_m as $val3)
	// 	{
	// 		if($val3==$arrSupplier2[$val2][$val3])
	// 		{
	// 			$subtotal = ($arrStatus[$val2][$nopp][$val][$val3]['jumlah'] * $arrStatus[$val2][$nopp][$val][$val3]['harga']);
	// 			$subtotal_m = ($arrStatus_m[$val2][$nopp][$val][$val3]['jumlah'] * $arrStatus[$val2][$nopp][$val][$val3]['harga']);
	// 			$tab.="
	// 				<td align=right style='background-color:green;color:white;border:1px solid black'>".number_format($arrStatus_m[$val2][$nopp][$val][$val3]['harga'],2)." <span style='color:green'>_</span> </td>
	// 				<td align=right style='background-color:green;color:white;border:1px solid black'>".number_format($subtotal_m,2)." <span style='color:green'>_</span> </td>
	// 				<td></td>
	// 				<td></td>
	// 				<td></td>
	// 			";
	// 			if($arrStatus_m[$val2][$nopp][$val][$val3]['ppn'] > 0){
	// 				$nilaippn +=  $subtotal_m * $arrStatus_m[$val2][$nopp][$val][$val3]['ppn'] / 100; 
	// 			}
	// 			$arrSubtotal_m += $subtotal_m;
	// 		}

	// 	}
	// }

}
$tab .= "</tr>";

// PPN
$no = $no + 1;
$tab .= "<tr class='rowcontent'>
					<td align=center>" . $no . "</td>
					<td align=center>PPN " . $perenppn . "</td>
					<td align=center></td>
			";
foreach ($arrPur as $val2) {
	foreach ($arrSupplier as $val3) {
		if ($val3 == $arrSupplier2[$val2][$val3]) {
			$tab .= "
							<td colspan=2>Exlude</td>
						";
		}
	}
}
$exlude_include = "";
$Franco_a = "";
$SyaratBayar_a = "";
// $Keterangan_a ="";
foreach ($arrPur as $val2) {
	foreach ($arrSupplier_m as $val3) {
		if ($val3 == $arrSupplier2[$val2][$val3]) {
			$exlude_include .= "Exlude </br>";
			$Franco_a .= " " . $franconame[$franco_m[$val2][$val3]['lokasikirim']] . " </br>";
			$SyaratBayar_a .= " " . $opttop[$syaratbayar_m[$val2][$val3]['syaratbayar']] . " </br>";
			// $Keterangan_a .=" ".$keterangan_[$val2][$val3]['keterangan']." </br>";

		}
	}
}
$tab .= "
				<td colspan=2>" . $exlude_include . "</td>
			";
$tab .= "
				<td colspan=3 align=left>
					<span style='text-align:left;color:blue;font-weight:bold'>
					" . $spasi . "	Semua penawaran include segel dan stempel
					</span> 
				</td>
			";
// akhir ppn

// franco
$no = $no + 1;
$tab .= "<tr class='rowcontent'>
					<td align=center>" . $no . "</td>
					<td align=center>Franco</td>
					<td align=center></td>
			";
foreach ($arrPur as $val2) {
	foreach ($arrSupplier as $val3) {
		if ($val3 == $arrSupplier2[$val2][$val3]) {
			$tab .= "
							<td colspan=2>" . $franconame[$franco_[$val2][$val3]['lokasikirim']] . "</td>
						";
		}
	}
}

$tab .= "
				<td colspan=2>" . $Franco_a . "</td>
			";

$tab .= "
				<td colspan=3 align=left></td>
			";
// akhir franco

// term of payment
$no = $no + 1;
$tab .= "<tr class='rowcontent'>
					<td align=center>" . $no . "</td>
					<td align=center>Term of Payment Condition</td>
					<td align=center></td>
			";
foreach ($arrPur as $val2) {
	foreach ($arrSupplier as $val3) {
		if ($val3 == $arrSupplier2[$val2][$val3]) {
			$tab .= "
							<td colspan=2>" . $opttop[$syaratbayar_[$val2][$val3]['syaratbayar']] . "</td>
						";
		}
	}
}
$tab .= "
				<td colspan=2>" . $SyaratBayar_a . "</td>
			";
$tab .= "
				<td colspan=3 align=left></td>
			";
// akhir term of payment

// total
$tab .= "<tr class='rowcontent'>
					<td align=center></td>
					<td align=center></td>
					<td align=center></td>
			";
foreach ($arrPur as $val2) {
	foreach ($arrSupplier as $val3) {
		if ($val3 == $arrSupplier2[$val2][$val3]) {
			$tab .= "
							<td colspan=2 style='color:red'>" . $keterangan_[$val2][$val3]['keterangan'] . "</td>
						";
		}
	}
}

$tab .= "<td colspan=1 style='background-color:yellow'>Total</td>
			<td colspan=1 style='text-align:right;background-color:yellow'>" . number_format($arrSubtotal_m, 2) . "  " . $spasi . " </td>";

$tab .= "
				<td colspan=3 align=left></td>
			";
// akhir total
// ppn total
$tab .= "<tr class='rowcontent'>
					<td align=center></td>
					<td align=center></td>
					<td align=center></td>
			";
foreach ($arrPur as $val2) {
	foreach ($arrSupplier as $val3) {
		if ($val3 == $arrSupplier2[$val2][$val3]) {
			$tab .= "
							<td colspan=2></td>
						";
		}
	}
}

$tab .= "<td colspan=1 style='background-color:yellow'>PPN " . $perenppn . "</td>
			<td colspan=1 style='text-align:right;background-color:yellow'>" . number_format($nilai_menang_ppn, 2) . "  " . $spasi . " </td>";

$tab .= "
				<td colspan=3 align=left></td>
			";
// akhir ppn total
// grand total
$tab .= "<tr class='rowcontent'>
					<td align=center></td>
					<td align=center></td>
					<td align=center></td>
			";
foreach ($arrPur as $val2) {
	foreach ($arrSupplier as $val3) {
		if ($val3 == $arrSupplier2[$val2][$val3]) {
			$tab .= "
							<td colspan=2></td>
						";
		}
	}
}

$tab .= "<td colspan=1 style='background-color:yellow'>Total</td>
			<td colspan=1 style='text-align:right;background-color:yellow'>" . number_format($arrSubtotal_m + $nilai_menang_ppn, 2) . " " . $spasi . " </td>";

$tab .= "
				<td colspan=3 align=left></td>
			";
// akhir grand total

$tab .= "</tbody>

	
</table>";


// bab III
$tab .= "
<table width=100%>
	<tr style='text-align:left;font-weight:bold;font-size:16px'>
		<td style='width:100%;font-size:25px'>
			III KESIMPULAN
		</td>
	</tr>
	<tr>
		<ul>";
foreach ($res_6 as $bar) {
	$tab .= "<li>
					<span style='font-weight:bold'>" . $optnamabarang[$bar['kodebarang']] . "</span> " . $optnamasupplier[$bar['supplierid']] . " memberikan harga terbaik
				</li>";
}
$tab .= "</ul>
	</tr>
</table>";


// BAB IV
$tab .= "
<table width=100%>
	<tr style='text-align:left;font-weight:bold;font-size:16px'>
		<td style='width:100%;font-size:25px'>
			IV REKOMENDASI
		</td>
	</tr>
	<tr>
		<ul>";

foreach ($max_scores as $category => $suppliers) {
	$supplier_list = implode(", ", array_keys($suppliers));
	if ($category == 'qpi') {
		$category = 'Quality/ Performance/ Integrity';
	}

	$tab .= "<li><span>Supplier $supplier_list memiliki score $category terbaik.</li>";
}

$tab .= "</ul>
	</tr>
</table>";

// BAB V
$tab .= "
<table width=100%>
	<tr style='text-align:left;font-weight:bold;font-size:16px'>
		<td style='width:100%;font-size:25px'>
			V PERSETUJUAN
		</td>
	</tr>
	<tr>";
$tab .= "<table width=30% cellspacing=0 cellpadding=1>
	<tr style='text-align:center;vertical-align:top'>
		<!--<td style='width:30%;font-weight:bold;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;'>PREPARED BY</td>-->
		<td style='width:50%;font-weight:bold;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;'>
		Dibuat Oleh
		</td>
		<td style='width:50%;font-weight:bold;border-left:0.5px solid #000;border-top:0.5px solid #000;border-right:0.5px solid #000;'>
		Disetujui Oleh
		</td>
	</tr>
	<tr>
		<!--<td style='width:30%;height:40px;border-left:0.5px solid #000;border-right:0.5px solid #000;'>&nbsp;</td>-->
		<td style='width:40%;height:70px;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center;color:gray;font-size:16px'><i>" . ($purchase_1 != '' ? 'ELECTRONICALLY SIGNED BY' : '') . "<i></td>
		<td style='width:40%;height:70px;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center;color:gray;font-size:16px'><i>" . ($aprvkar != '' ? 'ELECTRONICALLY SIGNED BY' : '') . "<i></td>
	</tr>
	<tr>
		<!--<td style='width:30%;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>" . $optnmkar[$purchaser] . "</td>-->
		<td style='width:40%;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>" . $optnmkar[$purchase_1] . "</td>
		<td style='width:40%;border-left:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>" . $aprvkar . "</td>
	</tr>
	<tr>
		<!--<td style='width:30%;border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>Date : " . $tgleditbaru . "</td>-->
		<td style='width:40%;border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>" . $jabatan[$kary_id[$purchase_1]] . "</td>
		<td style='width:40%;border-left:0.5px solid #000;border-bottom:0.5px solid #000;border-right:0.5px solid #000;text-align:center'>" . $jabatan[$kary_id[$aprvkar_karyid]] . "</td>
	</tr>
</table>";
$tab .= "</tr>
</table>";

// BAB VI
$tab .= "
<br>
<table width=80%>
	<tr style='text-align:left;font-weight:bold;font-size:16px'>
		<td style='width:100%;font-size:25px'>
			VI DISPOSISI
		</td>
	</tr>
	<tr>
		<td style='padding:20px;border:1px solid black' >" . $komentar_aproval . "</td>
	</tr>
</table>";


$dompdf = new Dompdf();
$dompdf->loadHtml($tab);
$dompdf->setPaper('A2', 'landscape');
$dompdf->render();
$sizefont = 12;
$font = $dompdf->getFontMetrics()->get_font("Times-Roman", "");

$dompdf->getCanvas()->page_text('1575', '15', "" . $notransaksi . "", $font, ($sizefont - 4), array(0, 0, 0), 0, 0, 0);
$dompdf->getCanvas()->page_text('1575', '25', "Page : {PAGE_NUM} / {PAGE_COUNT} ", $font, ($sizefont - 4), array(0, 0, 0), 0, 0, 0);
// $dompdf->getCanvas()->text(56, 20, 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz', $font, 12);

## Print Out
if ($urlefil == '0') {
	$dompdf->stream("PrintPOSO_" . $column, array("Attachment" => 0));
} else {
	file_put_contents($urlefil, $dompdf->output());
}

// $dompdf = new Dompdf();
// $options = $dompdf->getOptions();
// $options->set(array('isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true));
// $dompdf->loadHtml($dokumen);
// $dompdf->setPaper('A4', 'portrait');
// $dompdf->setOptions($options);
// $dompdf->render();
// $dompdf->stream('Dayoff_Nonstaff.pdf', array('Attachment' => 0));
