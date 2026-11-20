<?php
# CABANG PPP dari keu_slave_prosesDepresiasiAkhirbulanv2.php (Proses Akhir Bulan > 6. Depresiasi). Dipanggil otomatis oleh js/keu_3posting.js
# (tujuanProses) hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_prosesDepresiasiAkhirbulanv2.php.
# Perbedaan khusus PPP ditandai "#PPP": jumlah, kode jurnal dan akun dihitung ulang di server dari data aset (bukan dipercaya dari
# browser), periode akuntansi diperiksa sebelum menghapus apa pun, dan parameter divalidasi.
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/zAlokasiGajiPPP.php');

$param = $_POST;

$tipeorganisasi = makeOption($dbname, "organisasi", "kodeorganisasi,tipe");

$tipeLokasiTugas = isset($tipeorganisasi[$_SESSION['empl']['lokasitugas']]) ? $tipeorganisasi[$_SESSION['empl']['lokasitugas']] : '';
if ($tipeLokasiTugas != 'HOLDING' and $tipeLokasiTugas != 'KANWIL') {
	exit('<label hidden>Warning</label> Untuk Proses Depresiasi Asset, hanya lokasi tugas Holding (HO) dan Kanwil (RO) yang bisa lakukan Proses');
}

#PPP: hanya unit, periode, kode aset dan nomor baris yang dibaca dari browser
$pesanParam = validasiParamDepresiasiPPP($param);
if ($pesanParam != '') {
	exit($pesanParam);
}

$tanggal = $param['periode'] . "-28";

#PPP: periode akuntansi diperiksa SEBELUM menghapus jurnal lama
$res = fetchData("select periode from " . $dbname . ".setup_periodeakuntansi where kodeorg ='" . $param['kodeorg'] . "' and tutupbuku=0 and periode='" . $param['periode'] . "'");
if (count($res) < 1) {
	exit("Error: Accounting has closed transaction of  " . $param['periode']);
}

#PPP: jumlah, kode jurnal dan akun dihitung ulang dari data aset
$dtAset = hitungDepresiasiAssetPPP($param['kodeorg'], $param['periode'], $param['kodeasset']);
if (!isset($dtAset[$param['kodeasset']])) {
	exit("error: Aset " . $param['kodeasset'] . " tidak ditemukan atau tidak perlu disusutkan pada periode " . $param['periode']);
}
$aset = $dtAset[$param['kodeasset']];
$param['kodejurnal'] = $aset['kodejurnal'];
$param['debet'] = $aset['debet'];
$param['kredit'] = $aset['kredit'];
$param['jumlah'] = $aset['jumlah'];
$param['namaaset'] = $aset['namasset'];

if ($param['kodejurnal'] == '') {
	exit("error: Gagal melakukan jurnal untuk asset " . $param['kodeasset'] . ", Kode Jurnal masih kosong");
}
if ($param['debet'] == '' || $param['kredit'] == '') {
	exit("error: Gagal melakukan jurnal untuk asset " . $param['kodeasset'] . ", akun debet/kredit kode jurnal " . $param['kodejurnal'] . " belum diisi di Parameter Jurnal");
}

try {

	$owlPDO->beginTransaction();

	#= delete 1st jika row 1
	if ($param['currRow'] == 1) {
		$str = "select * from " . $dbname . ".sdm_5tipeasset";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$kdjurnal['DPH' . $bar['kodetipe']] = 'DPH' . $bar['kodetipe'];
			$kdjurnal['DEP' . $bar['kodetipe']] = 'DEP' . $bar['kodetipe'];
		}
		$str = "delete from " . $dbname . ".keu_jurnalht where  nojurnal like '%/" . $param['kodeorg'] . "/%' and kodejurnal in ('" . implode("','", $kdjurnal) . "') and  tanggal='" . $tanggal . "' ";
		$owlPDO->exec($str);
	}

	$query = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodekelompok='" . $param['kodejurnal'] . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "'"
	);
	$tmpKonter = fetchData($query);
	$konter = addZero((isset($tmpKonter[0]['nokounter']) ? $tmpKonter[0]['nokounter'] : 0) + 1, 3);
	# Prep No Jurnal
	$nojurnal = str_replace('-', '', $tanggal) . "/" . $param['kodeorg'] . "/" . $param['kodejurnal'] . "/" . $konter;

	$dataRes = array('header' => array(), 'detail' => array());
	$dataRes['header'][] = array(
		'nojurnal' => $nojurnal,
		'kodejurnal' => $param['kodejurnal'],
		'tanggal' => $tanggal,
		'tanggalentry' => date('Ymd'),
		'posting' => '0',
		'totaldebet' => $param['jumlah'],
		'totalkredit' => $param['jumlah'] * -1,
		'amountkoreksi' => '0',
		'noreferensi' => $param['kodeasset'],
		'autojurnal' => '1',
		'matauang' => 'IDR',
		'kurs' => '1',
		'revisi' => ''
	);

	#= update counter jurnal
	$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where
		kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $param['kodejurnal'] . "' and periode='" . $param['periode'] . "' ";
	$owlPDO->exec($str);

	$noUrut = 0;

	#= debet
	$noUrut++;
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $param['debet'],
		'keterangan' => 'Penyusutan [' . $param['kodeasset'] . '] ' . $param['namaaset'],
		'jumlah' => $param['jumlah'],
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => $param['kodeasset'],
		'kodebarang' => '',
		'nik' => '',
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => $param['kodeasset'],
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => '',
		'revisi' => '0',
		'kodesegment' => ''
	);

	#= kredit
	$noUrut++;
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $param['kredit'],
		'keterangan' => 'Penyusutan [' . $param['kodeasset'] . '] ' . $param['namaaset'],
		'jumlah' => $param['jumlah'] * -1,
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => $param['kodeasset'],
		'kodebarang' => '',
		'nik' => '',
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => $param['kodeasset'],
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => '',
		'revisi' => '0',
		'kodesegment' => ''
	);

	$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
	$owlPDO->exec($queryH);

	$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
	$owlPDO->exec($queryD);

	$owlPDO->commit();
} catch (PDOException $e) {

	$owlPDO->rollback();
	echo "Warning Gagal jurnal \n" . addslashes($e->getMessage());
}
