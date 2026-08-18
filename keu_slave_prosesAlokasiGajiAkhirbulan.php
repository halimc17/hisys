<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;
$orgpt  = "";
$dataorg = array();
$dtstr = "SELECT DISTINCT kodeorganisasi,induk FROM {$dbname}.organisasi 
					WHERE kodeorganisasi = '{$param['kodeorg']}'";
$res = fetchData($dtstr);
foreach ($res as $dt) {
	$dataorg[$dt['kodeorganisasi']] = $dt;
	$orgpt = $dt['induk'];
}

$tanggal = $param['periode'] . "-28";
$totalxxx = 0;

$strGetParamKodejurnal = "SELECT nilai FROM {$dbname}.setup_parameterappl WHERE kodeparameter='KDJURGJDEL'";
$res = fetchData($strGetParamKodejurnal);
$arrKodeJurnal = explode(',', $res[0]['nilai']);

try {
	$owlPDO->beginTransaction();
	if ($param['row'] == '1') {

		if (count($arrKodeJurnal) > 0) {
			$strKDJur = implode("','", $arrKodeJurnal);
			#periksa dan hapus transaksi untuk data yang sudah di proses pada periode yang sama    
			$str = "DELETE FROM {$dbname}.keu_jurnalht 
							WHERE kodejurnal in ('{$strKDJur}') 
							AND tanggal='{$tanggal}' 
							AND nojurnal like '%/{$param['kodeorg']}/%'";
			$owlPDO->exec($str);

			#= ambil data jika gaji operator berbeda unit dengan kendaraannya
			$str = "SELECT akunpiutang,akunhutang FROM {$dbname}.keu_5caco 
						WHERE kodeorg='{$param['kodeorg']}'";
			$res = fetchData($str);
			foreach ($res as $bar) {
				$noakuncacounit[$bar['akunpiutang']] = $bar['akunpiutang'];
				$noakuncacounit[$bar['akunhutang']] = $bar['akunhutang'];
			}

			$strakuncaco = implode("','", $noakuncacounit);
			$strdel = "	DELETE FROM {$dbname}.keu_jurnalht WHERE nojurnal IN (
										SELECT DISTINCT b.nojurnal
										FROM {$dbname}.keu_jurnaldt as a
										LEFT JOIN {$dbname}.keu_jurnalht as b ON a.nojurnal=b.nojurnal
										WHERE a.noakun IN ('{$strakuncaco}')
										AND a.noreferensi = 'ALK_TRK_GYMH'
										AND a.tanggal like '{$param['periode']}%' 
										AND b.kodejurnal = 'M'
									)";
			$owlPDO->exec($strdel);
		} else {
			throw new PDOException("kode jurnal pada parameter belum disetup");
		}
	}

	// echo "<pre>";
	// print_r($str);
	// echo "</pre>";
	// exit('error');

	#==========================================konfigurasi database
	/*
	BPJS Plus
	WSG6
	VHCG6
	KBNB6
	KBNL4
	GJHO7
	PKS09
	PKS10
	BLK09
	BLK10
	RNDB6
	PBK07
	*/
	# KBNB0	Gaji BTL Kebun/Pabrik
	# KBNB1	Premi/Lebur BTL Kebun/Pabrik
	# KBNB2	Tunjangan Lain
	# KBNB3	THR BTL
	# KBNB4	Bonus BTL
	# KBNB5	Pengobatan BTL
	# VHCG0	Gaji Kendaraan/A.Berat
	# VHCG1	Biaya Lebur Kendaraan/A.Berat
	# VHCG2	Biaya Tunjangan Lain Kend./A.Berat
	# VHCG3	THR Kend./A.Berat
	# VHCG4	Bonus Kend. A.Berat
	# VHCG5	Pengobatan Kend./A.Berat
	# WSG0	Biaya Gaji Bengkel
	# WSG1	Biaya Premi/Lembur Bengkel
	# WSG2	Tunjangan Lain Bengkel
	# WSG3	THR Traksi
	# WSG4	Bonus Traksi
	# WSG5	Pengobatan Traksi
	# KBNL0	Biaya pengawasan BBT
	# KBNL1	Biaya pengawasan TBM
	# KBNL2	Biaya pengawasan TM
	# KBNL3	Biaya Pengawasan Panen
	# PKS01	Gaji Non Staff PKS
	# PKS02	Lembur PKS
	# PKS03	Transport dan Uang Makan
	# PKS04	Pengobatan
	# PKS05	Gaji Non Staff Maintenance PKS
	# PKS06	Lembur maintenance PKS
	# PKS07	Transport dan Uang Makan Maintenance
	# PKS08	Pengobatan Maintenance
	# BLK01	Gaji Non Staff BLK
	# BLK02	Lembur BLK
	# BLK03	Transport dan Uang Makan
	# BLK04	Pengobatan
	# BLK05	Gaji Non Staff umum BLK
	# BLK06	Lembur umum BLK
	# BLK07	Transport dan Uang Makan umum
	# BLK08	Pengobatan umum

	/*
	mainten dihilangkan digabung dengan workshop
	*/

	#============================================konfigurasi database

	#==Komfigurasi komponen gaji
	# 1	Gaji Pokok
	# 2	Tunjangan Jabatan
	# 14	Rapel
	# 16	Premi Pengawasan
	# 21	Klaim Pengobatan
	# 26	Bonus
	# 27	Tunjangan Fasilitas
	# 28	THR
	# 30	Tunjangan Profesi
	# 31	Tunjangan Masa Kerja
	# 32	Premi
	# 33	Lembur
	# 34	Penalti
	#
	#=======================================================
	#parameter
	#   namakaryawan  
	#   karyawanid   
	#   komponen     
	#   namakomponen  
	#   subbagian      
	#   mesin       
	#   jumlah         
	#   tipeorganisasi 
	#   periode


	#=================================================
	#periksa jika tipe unit adalah traksi
	$str = "SELECT DISTINCT tipe FROM {$dbname}.organisasi WHERE kodeorganisasi='{$param['kodeorg']}'";
	$tip = '';
	$res = fetchData($str);
	foreach ($res as $bar) {
		$tip = $bar['tipe'];
	}

	$param['tipeorganisasi'] = str_replace(" ", "", $param['tipeorganisasi']);
	if (substr($param['tipeorganisasi'], 0, 3) == 'PBR') {
		$tip = 'PABRIKASI';
	}
	# Default Segment
	$defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');


	if ($tip == 'PABRIK') {
		if ($param['tipeorganisasi'] == 'TRAKSI') {
			if ($param['mesin'] == '') {
				prosesGajiWs();
			} else {
				prosesGajiTraksi();
			}
		} else if ($param['tipeorganisasi'] == 'WORKSHOP') {
			prosesGajiWs();
		} else if ($param['tipeorganisasi'] == 'STATION') {
			prosesGajiPabrik();
		} else if ($param['tipeorganisasi'] == 'SIPIL') {
			prosesGajiSipil();
		} else {
			prosesGajiKebun();
		}
	} else if ($tip == 'KEBUN') {
		if ($param['tipeorganisasi'] == 'TRAKSI') {
			if ($param['mesin'] == '') {
				// prosesGajiWs(); # Sementara
				prosesGajiTraksiUmum();
			} else {
				prosesGajiTraksi();
			}
		} else if ($param['tipeorganisasi'] == 'WORKSHOP') {
			prosesGajiWs();
		} else if ($param['tipeorganisasi'] == 'AFDELING') {
			prosesGajiAfdeling();
		} else if ($param['tipeorganisasi'] == 'BIBITAN') {
			prosesGajiAfdeling();
		} else if ($param['tipeorganisasi'] == 'SIPIL') {
			prosesGajiSipil();
		} else {
			prosesGajiKebun();
		}
	} else if ($tip == 'BULKING') {
		if ($param['tipeorganisasi'] == 'TRAKSI') {
			if ($param['mesin'] == '') {
				prosesGajiWs();
			} else {
				prosesGajiTraksi();
			}
		} else if ($param['tipeorganisasi'] == 'WORKSHOP') {
			prosesGajiWs();
		} else if ($param['tipeorganisasi'] == 'STATION') {
			prosesbulking();
		} else if ($param['tipeorganisasi'] == 'SIPIL') {
			prosesGajiSipil();
		} else {
			prosesbulking();
		}
	} else if ($tip == 'RND') {
		prosesrnd();
	} else if ($tip == 'KANWIL') {
		prosesho();
	} else if ($tip == 'HOLDING') {
		prosesho();
	} else if ($tip == 'TC') {
		prosesrnd();
	} else {
		throw new PDOException("PROSES GAJI TIDAK TERDAFTAR UNTUK " . $tip . " ");
	}

	#execute
	$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Error, " . addslashes($e->getMessage());
	die();
}

function prosesGajiSipil()
{
	global $conn;
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;
	#WSG0	Gaji Bengkel	
	#WSG1	Biaya Lebur Bengkel
	#WSG2	Biaya Tunjangan Lain Bengkel
	#WSG3	THR Bengkel	
	#WSG4	Bonus Bengkel
	#WSG5	Pengobatan Bengkel

	#output pada jurnal kolom noreferensi ALK_SIPL_GYMH  
	$group = 'SIPL1';  //defaultnya tunjangan

	$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $group . "' limit 1";
	$rs = $owlPDO->query($str);
	$rs->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($rs);
	if ($numrows < 1) {
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen']);
	} else {
		$akundebet = '';
		$akunkredit = '';
		$bar = $rs->fetch();
		$akundebet = $bar->noakundebet;
		$akunkredit = $bar->noakunkredit;
	}

	#proses data
	$kodeJurnal = $group;
	#======================== Nomor Jurnal =============================
	# Get Journal Counter
	$queryJ = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$tmpKonter = fetchData($queryJ);
	$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

	# Transform No Jurnal dari No Transaksi
	$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
	#======================== /Nomor Jurnal ============================
	# Prep Header
	$dataRes['header'] = array(
		'nojurnal' => $nojurnal,
		'kodejurnal' => $kodeJurnal,
		'tanggal' => $tanggal,
		'tanggalentry' => date('Ymd'),
		'posting' => 1,
		'totaldebet' => $param['jumlah'],
		'totalkredit' => -1 * $param['jumlah'],
		'amountkoreksi' => '0',
		'noreferensi' => 'ALK_SIPL_GYMH',
		'autojurnal' => '1',
		'matauang' => 'IDR',
		'kurs' => '1',
		'revisi' => '0'
	);
	# Data Detail
	$noUrut = 1;

	# Debet
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $akundebet,
		'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By. Perumahan",
		'jumlah' => $param['jumlah'],
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => '',
		'kodebarang' => '',
		'nik' => $param['karyawanid'],
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => 'ALK_SIPL_GYMH',
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => '',
		'revisi' => '0',
		'kodesegment' => $defSegment
	);
	$noUrut++;

	# Kredit
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $akunkredit,
		'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Perumahan",
		'jumlah' => -1 * $param['jumlah'],
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => '',
		'kodebarang' => '',
		'nik' => $param['karyawanid'],
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => 'ALK_SIPL_GYMH',
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => '',
		'revisi' => '0',
		'kodesegment' => $defSegment
	);
	$noUrut++;
	$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
	$owlPDO->exec($insHead);

	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
	foreach ($dataRes['detail'] as $row) {
		$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
		$owlPDO->exec($insDet);
	}
	# Header and Detail inserted
	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
	$updJurnal = updateQuery(
		$dbname,
		'keu_5kelompokjurnal',
		array('nokounter' => $konter),
		"kodeorg='" . $orgpt .
			"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$owlPDO->exec($updJurnal);
}
function prosesGajiWs()
{
	global $conn;
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;
	#WSG0	Gaji Bengkel	
	#WSG1	Biaya Lebur Bengkel
	#WSG2	Biaya Tunjangan Lain Bengkel
	#WSG3	THR Bengkel	
	#WSG4	Bonus Bengkel
	#WSG5	Pengobatan Bengkel

	#====================================================================================================#
	# NEW
	#====================================================================================================#
	$kodekend = $param['mesin'];

	$arrUnit = array();
	$param['kodevhc'] = $kodekend;
	#1 ambil periode akuntansi
	$str = "SELECT tanggalmulai,tanggalsampai FROM {$dbname}.setup_periodeakuntansi 
					WHERE 1=1 
					AND kodeorg = '{$param['kodeorg']}' 
					AND tutupbuku = 0 
					AND periode = '{$param['periode']}'";
	$tgmulai = '';
	$tgsampai = '';
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($res);
	if ($numrows < 1) {
		throw new PDOException("Tidak ada periode akuntansi untuk induk " . $param['kodeorg']);
	}
	while ($bar = $res->fetch()) {
		$tgsampai   = $bar->tanggalsampai;
		$tgmulai    = $bar->tanggalmulai;
	}
	if ($tgmulai == '' || $tgsampai == '')
		throw new PDOException("Periode akuntasi tidak terdaftar");

	#2 output pada jurnal kolom noreferensi ALK_KERJA_AB  
	# Pindahkan ke Bawah
	$group = 'VHC0';
	#ambil akun alokasi
	$str = "SELECT noakundebet FROM {$dbname}.keu_5parameterjurnal
					WHERE 1=1 
					AND jurnalid = '{$group}' ";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($res);
	if ($numrows < 1)
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk VHC0");
	else {
		$bar = $res->fetch();
		$akunalok = $bar->noakundebet;
	}

	#3 output pada jurnal kolom noreferensi ALK_WS_GYMH  
	# Pindahkan ke Bawah
	$groupx = 'WS2';
	#ambil akun alokasi
	$str = "SELECT noakunkredit FROM {$dbname}.keu_5parameterjurnal
					WHERE 1=1 
					AND jurnalid = '{$groupx}' ";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($res);
	if ($numrows < 1)
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk WS2");
	else {
		$bar = $res->fetch();
		$akunalokws = $bar->noakunkredit;
	}

	#2.5 ambil semua vhc yang terdapat karywan itu mengerjakannya
	$kodevhcx = '';
	$str = "SELECT distinct kodevhc FROM {$dbname}.vhc_penggantianht a
					LEFT JOIN {$dbname}.vhc_penggantiandt_karyawan b ON a.notransaksi=b.notransaksi
					WHERE 1=1 
					AND b.karyawanid = '{$param['karyawanid']}'
					AND a.tanggal >= '{$tgmulai}' 
					AND a.tanggal <= '{$tgsampai}'
					GROUP BY kodevhc 
					ORDER BY tanggal ASC";

	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		if ($kodevhcx == '') {
			$kodevhcx = "'" . $bar->kodevhc . "'";
		} else {
			$kodevhcx .= ",'" . $bar->kodevhc . "'";
		}
	}

	if ($kodevhcx == '') {
		$kodevhcx = "'" . $kodekend . "'";
	}

	#====================================================================================================#
	# END NEW
	#====================================================================================================#

	# Buat per komponenn nya
	#output pada jurnal kolom noreferensi ALK_WS_GYMH 
	$arrKodeGroup = [
		'1' => 'WSG0',
		'14' => 'WSG0',
		'16' => 'WSG1',
		'32' => 'WSG1',
		'33' => 'WSG1',
		'70' => 'WSG6',
		'71' => 'WSG6',
		'72' => 'WSG6',
		'73' => 'WSG6',
		'80' => 'WSG6',
		'28' => 'WSG3',
		'26' => 'WSG4',
		'21' => 'WSG5'
	];

	if (isset($arrKodeGroup[$param['komponen']])) {
		$group = $arrKodeGroup[$param['komponen']];
	} else {
		$group = 'WSG2';  //defaultnya tunjangan
	}

	$arrKodeGroup[''] = 'WSG2'; // dimasukin agar bisa dicari konternya


	$str = "SELECT noakundebet,noakunkredit from {$dbname}.keu_5parameterjurnal where jurnalid='{$group}' limit 1";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($res);
	if ($numrows < 1) {
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen']);
	} else {
		$akundebet = '';
		$akunkredit = '';
		$bar = $res->fetch();
		$akundebet = $bar->noakundebet;
		$akunkredit = $bar->noakunkredit;
	}

	if ($kodevhcx == '') { # Jika Workshop tidak memperbaiki kendaraan
		#proses data
		$kodeJurnal = $group;
		#======================== Nomor Jurnal =============================
		# Get Journal Counter
		$queryJ = selectQuery(
			$dbname,
			'keu_5kelompokjurnal',
			'nokounter',
			"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
		);
		$tmpKonter = fetchData($queryJ);
		$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

		# Transform No Jurnal dari No Transaksi
		$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
		#======================== /Nomor Jurnal ============================
		# Prep Header
		$dataRes['header'] = array(
			'nojurnal' => $nojurnal,
			'kodejurnal' => $kodeJurnal,
			'tanggal' => $tanggal,
			'tanggalentry' => date('Ymd'),
			'posting' => 1,
			'totaldebet' => $param['jumlah'],
			'totalkredit' => -1 * $param['jumlah'],
			'amountkoreksi' => '0',
			'noreferensi' => 'ALK_WS_GYMH',
			'autojurnal' => '1',
			'matauang' => 'IDR',
			'kurs' => '1',
			'revisi' => '0'
		);
		# Data Detail
		$noUrut = 1;

		# Debet
		$dataRes['detail'][] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => $tanggal,
			'nourut' => $noUrut,
			'noakun' => $akundebet,
			'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Bengkel",
			'jumlah' => $param['jumlah'],
			'matauang' => 'IDR',
			'kurs' => '1',
			'kodeorg' => $param['kodeorg'],
			'kodekegiatan' => '',
			'kodeasset' => '',
			'kodebarang' => '',
			'nik' => $param['karyawanid'],
			'kodecustomer' => '',
			'kodesupplier' => '',
			'noreferensi' => 'ALK_WS_GYMH',
			'noaruskas' => '',
			'kodevhc' => '',
			'nodok' => '',
			'kodeblok' => '',
			'revisi' => '0',
			'kodesegment' => $defSegment
		);
		$noUrut++;

		# Kredit
		$dataRes['detail'][] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => $tanggal,
			'nourut' => $noUrut,
			'noakun' => $akunkredit,
			'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Bengkel",
			'jumlah' => -1 * $param['jumlah'],
			'matauang' => 'IDR',
			'kurs' => '1',
			'kodeorg' => $param['kodeorg'],
			'kodekegiatan' => '',
			'kodeasset' => '',
			'kodebarang' => '',
			'nik' => $param['karyawanid'],
			'kodecustomer' => '',
			'kodesupplier' => '',
			'noreferensi' => 'ALK_WS_GYMH',
			'noaruskas' => '',
			'kodevhc' => '',
			'nodok' => '',
			'kodeblok' => '',
			'revisi' => '0',
			'kodesegment' => $defSegment
		);
		$noUrut++;

		$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
		$owlPDO->exec($insHead);

		#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
		foreach ($dataRes['detail'] as $row) {
			$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
			$owlPDO->exec($insDet);
		}
		# Header and Detail inserted
		#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
		$updJurnal = updateQuery(
			$dbname,
			'keu_5kelompokjurnal',
			array('nokounter' => $konter),
			"kodeorg='" . $orgpt .
				"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
		);
		$owlPDO->exec($updJurnal);
	} else { # Jika Workshop memperbaiki kendaraan
		#proses data
		$kodeJurnal = $group;
		#======================== Nomor Jurnal =============================
		# Get Journal Counter
		$queryJ = selectQuery(
			$dbname,
			'keu_5kelompokjurnal',
			'nokounter',
			"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
		);
		$tmpKonter = fetchData($queryJ);
		$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

		# Transform No Jurnal dari No Transaksi
		$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
		#======================== /Nomor Jurnal ============================
		# Prep Header
		$dataRes['header'] = array(
			'nojurnal' => $nojurnal,
			'kodejurnal' => $kodeJurnal,
			'tanggal' => $tanggal,
			'tanggalentry' => date('Ymd'),
			'posting' => 1,
			'totaldebet' => $param['jumlah'],
			'totalkredit' => -1 * $param['jumlah'],
			'amountkoreksi' => '0',
			'noreferensi' => 'ALK_WS_GYMH',
			'autojurnal' => '1',
			'matauang' => 'IDR',
			'kurs' => '1',
			'revisi' => '0'
		);
		# Data Detail
		$noUrut = 1;

		# Debet
		$dataRes['detail'][] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => $tanggal,
			'nourut' => $noUrut,
			'noakun' => $akundebet,
			'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Bengkel",
			'jumlah' => $param['jumlah'],
			'matauang' => 'IDR',
			'kurs' => '1',
			'kodeorg' => $param['kodeorg'],
			'kodekegiatan' => '',
			'kodeasset' => '',
			'kodebarang' => '',
			'nik' => $param['karyawanid'],
			'kodecustomer' => '',
			'kodesupplier' => '',
			'noreferensi' => 'ALK_WS_GYMH',
			'noaruskas' => '',
			'kodevhc' => '',
			'nodok' => '',
			'kodeblok' => '',
			'revisi' => '0',
			'kodesegment' => $defSegment
		);
		$noUrut++;

		# Kredit
		$dataRes['detail'][] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => $tanggal,
			'nourut' => $noUrut,
			'noakun' => $akunkredit,
			'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Bengkel",
			'jumlah' => -1 * $param['jumlah'],
			'matauang' => 'IDR',
			'kurs' => '1',
			'kodeorg' => $param['kodeorg'],
			'kodekegiatan' => '',
			'kodeasset' => '',
			'kodebarang' => '',
			'nik' => $param['karyawanid'],
			'kodecustomer' => '',
			'kodesupplier' => '',
			'noreferensi' => 'ALK_WS_GYMH',
			'noaruskas' => '',
			'kodevhc' => '',
			'nodok' => '',
			'kodeblok' => '',
			'revisi' => '0',
			'kodesegment' => $defSegment
		);
		$noUrut++;

		$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
		$owlPDO->exec($insHead);

		#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
		foreach ($dataRes['detail'] as $row) {
			$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
			$owlPDO->exec($insDet);
		}
		# Header and Detail inserted
		#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
		$updJurnal = updateQuery(
			$dbname,
			'keu_5kelompokjurnal',
			array('nokounter' => $konter),
			"kodeorg='" . $orgpt .
				"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
		);
		$owlPDO->exec($updJurnal);

		#3 ambil semua lokasi kegiatan
		$str = "SELECT SUM(a.jumlah) as jlh,a.alokasibiaya,b.noakun,b.setupkegiatan,a.kodesegment 
						FROM {$dbname}.vhc_rundt_detail a
						LEFT JOIN {$dbname}.vhc_kegiatan b ON a.jenispekerjaan=b.kodekegiatan
						LEFT JOIN {$dbname}.vhc_runht c ON a.notransaksi=c.notransaksi     
						WHERE c.kodevhc IN ({$kodevhcx})
						AND c.tanggal>='{$tgmulai}' AND c.tanggal <='{$tgsampai}' AND alokasibiaya!='' 
						AND jenispekerjaan!=''    
						GROUP BY jenispekerjaan,noakun,alokasibiaya,kodesegment ORDER BY tanggal ASC";

		$lokasi = array();
		$biaya = array();
		$karyawanid = array();
		$jam  = array();
		$akun = array();
		$kodeasset = array();
		$segment = array();
		$ttl = 0;
		$no = 0;
		$tempjam = 0;
		$counttemp = 0;
		$tempjumlah = $param['jumlah'];

		$dtKegiatan = [];

		$res = fetchData($str);
		$cekAlokasiAK = false;
		$cekAlokasiPB = false;
		$arrKodeAlokasiAK = [];
		$arrKodeAlokasiPB = [];
		$dtOrgPengguna = [];
		foreach ($res as $dt) {
			$tempjam += $dt['jlh'];
			$counttemp++;
			$dtKegiatan[] = $dt;

			if (substr($dt['alokasibiaya'], 0, 2) != 'AK' && substr($dt['alokasibiaya'], 0, 2) != 'PB') {
				$dtOrgPengguna[substr($dt['alokasibiaya'], 0, 4)] = substr($dt['alokasibiaya'], 0, 4);
			}

			if (substr($dt['alokasibiaya'], 0, 2) == 'AK') {
				$cekAlokasiAK = true;
				$arrKodeAlokasiAK[] = $dt['alokasibiaya'];
			}
			if (substr($dt['alokasibiaya'], 0, 2) == 'PB') {
				$cekAlokasiPB = true;
				$arrKodeAlokasiPB[] = $dt['alokasibiaya'];
			}
		}

		$arrKodeAlokasiAK = array_unique($arrKodeAlokasiAK);
		$arrKodeAlokasiPB = array_unique($arrKodeAlokasiPB);

		$dtTipeAsset = [];
		$dtPenggunaAKPB = [];
		if ($cekAlokasiAK) {
			$dtTipeAsset = makeOption($dbname, 'sdm_5tipeasset', 'kodetipe', 'akunak');
			$strGetPengguna = "select DISTINCT kode,kodeorg FROM " . $dbname . ".project where kode in ('" . implode("','", $arrKodeAlokasiAK) . "')";
			$resGetPengguna = fetchData($strGetPengguna);
			foreach ($resGetPengguna as $dt) {
				$dtPenggunaAKPB[$dt['kode']] = $dt['kodeorg'];
				$dtOrgPengguna[$dt['kodeorg']] = $dt['kodeorg'];
			}
		}

		$dtAkunPB = [];
		if ($cekAlokasiPB) {
			$sData = "SELECT DISTINCT noakundebet FROM {$dbname}.keu_5parameterjurnal WHERE jurnalid='PBR3'";
			$rData = fetchData($sData);
			if (count($rData) == 0) {
				throw new PDOException("Akun untuk alokasi traksi pabrikasi belum di setting pada keuangan->setup->parameter jurnal");
			} else {
				foreach ($rData as $dt) {
					$dtAkunPB[] = $dt['noakundebet'];
				}
			}

			$strGetPengguna = "select DISTINCT kodepabrikasi,kodeorg FROM " . $dbname . ".pabrikasi_5masterht where kodepabrikasi in ('" . implode("','", $arrKodeAlokasiPB) . "')";
			$resGetPengguna = fetchData($strGetPengguna);
			foreach ($resGetPengguna as $dt) {
				$dtPenggunaAKPB[$dt['kodepabrikasi']] = $dt['kodeorg'];
				$dtOrgPengguna[$dt['kodeorg']] = $dt['kodeorg'];
			}
		}

		$dtCaco = [];
		$dtAkunHutangPengguna = [];
		$dtIndukPengguna = [];
		$dtSuppXX = [];
		$dtSuppX = [];
		if (count($dtOrgPengguna) > 0) {

			// ambil caco nya
			$uniqOrgPengguna = array_unique(array_values($dtOrgPengguna));
			$str = "select akunpiutang,akunhutang,jenis,kodeorg from " . $dbname . ".keu_5caco where kodeorg IN ('" . implode("','", $uniqOrgPengguna) . "')";
			$res = fetchData($str);
			foreach ($res as $dt) {
				$dtCaco[$dt['kodeorg']][$dt['jenis']] = $dt['akunpiutang'];
				$dtAkunHutangPengguna[$dt['kodeorg']][$dt['jenis']] = $dt['akunhutang'];
			}

			// ambil induk org pengguna
			$str = "select kodeorganisasi,induk from " . $dbname . ".organisasi where kodeorganisasi IN ('" . implode("','", $uniqOrgPengguna) . "')";
			$res = fetchData($str);
			foreach ($res as $dt) {
				$dtIndukPengguna[$dt['kodeorganisasi']] = $dt['induk'];
			}

			// ambil supplier pengguna
			if ($orgpt == 'PPP') {
				$str = "select afdeling,kodesupplier from " . $dbname . ".kebun_5namakud where status='1' and afdeling IN ('" . implode("','", $uniqOrgPengguna) . "')";
				$res = fetchData($str);
				foreach ($res as $row => $lsDt) {
					$dtSuppX[$lsDt['afdeling']] = $lsDt['kodesupplier'];
				}

				if (count($dtSuppX) == 0) {
					$str = "select kodeunit,kodesupplier from " . $dbname . ".kebun_5namakud where status='1' and kodesupplier IN ('" . implode("','", $uniqOrgPengguna) . "')";
					$res = fetchData($str);
					foreach ($res as $row => $lsDt) {
						$dtSuppXX[$lsDt['kodesupplier']] = $lsDt['kodeunit'];
					}
				}
			}
		}

		// ambil seluruh konter berdasarkan induk dan kodeorg, supaya nanti tinggal dipake tanpa loop2 ulang
		$dtAllKonter = [];
		$dtAllKonterJurnal = [];
		if (count($dtIndukPengguna) > 0) {
			$strGetKodeGroup = array_unique(array_values($arrKodeGroup));
			foreach ($dtIndukPengguna as $org => $ind) {
				$strGetKonter = "SELECT nokounter,kodekelompok FROM {$dbname}.keu_5kelompokjurnal where kodeorg='{$ind}' and kodekelompok IN ('" . implode("','", $strGetKodeGroup) . "') and kodeunit='{$org}' and periode='{$param['periode']}'  ";
				$resGetKonter = fetchData($strGetKonter);
				foreach ($resGetKonter as $row => $lsDt) {
					$dtAllKonter[$ind][$org][$lsDt['kodekelompok']] = addZero($lsDt['nokounter'], 3);
				}
			}

			// ambil seluruh jurnal berdasarkan induk dan kodeorg, supaya nanti tinggal dipake tanpa loop2 ulang
			$strKonterJurnal = "select max((substring_index(nojurnal,'/',-1)*1)) as konter, SUBSTRING_INDEX(SUBSTRING_INDEX(nojurnal, '/', 2), '/', -1) as kodeorg, kodejurnal from {$dbname}.keu_jurnalht where SUBSTRING_INDEX(SUBSTRING_INDEX(nojurnal, '/', 2), '/', -1) IN ('" . implode("','", $uniqOrgPengguna) . "') and kodejurnal IN ('" . implode("','", $strGetKodeGroup) . "') and tanggal like '{$param['periode']}%' GROUP BY kodeorg,kodejurnal";
			$resKonterJurnal = fetchData($strKonterJurnal);
			foreach ($resKonterJurnal as $dt) {
				$dtAllKonterJurnal[$dt['kodeorg']][$dt['kodejurnal']][] = addZero($dt['konter'], 3);
			}
		}

		$dtArrIndukOrg = makeOption($dbname, 'organisasi', "kodeorganisasi,induk", "1=1 AND LENGTH(kodeorganisasi)=4");

		foreach ($dtKegiatan as $no => $dt) {

			$cekAlokasiBiaya = substr($dt['alokasibiaya'], 0, 2);

			#kusus jika project
			if ($cekAlokasiBiaya == 'AK' or $cekAlokasiBiaya == 'PB') {
				if ($cekAlokasiBiaya == 'AK') {
					#ambil akun aktiva dalam konstruksi
					// alokasi ke AK-BG98000008, kalo 3,3 dapetnya BG9... sementara setupnya cuman ada BG. jadi ganti 3,2
					$tipeasset = substr($dt['alokasibiaya'], 3, 2);
					$tipeasset =  str_replace("0", "", $tipeasset);
					if (count($dtTipeAsset) == 0 || $dtTipeAsset[$tipeasset] == '') {
						throw new PDOException("Akun aktiva dalam konstruksi untuk " . $tipeasset . " belum disetting dari keuangan->setup->tipeasset");
					}
					$akun[] = $dtTipeAsset[$tipeasset];
					$kodeasset[] = $dt['alokasibiaya'];
				} else if ($cekAlokasiBiaya == 'PB') {
					foreach ($dtAkunPB as $dt) {
						$akun[] = $dt;
					}
					$kodeasset[] = '';
				}
				$lokasi[] = $dt['alokasibiaya'];
				$jam[] = $dt['jlh'];
				if ($no == $counttemp) {
					$biaya[] = $tempjumlah;
				} else {
					$biaya[] = floor(($dt['jlh'] / $tempjam) * $param['jumlah']);
				}
				$tempjumlah = $tempjumlah - floor(($dt['jlh'] / $tempjam) * $param['jumlah']);
				$karyawanid[] = $param['karyawanid'];
				$kegiatan[] = '';
				$segment[] = $defSegment;
			} else {
				$lokasi[] = $dt['alokasibiaya'];
				$akun[]  = $dt['noakun'];
				$jam[] = $dt['jlh'];
				if ($no == $counttemp) {
					$biaya[] = $tempjumlah;
				} else {
					// echo "<pre>" . var_export("{$dt['jlh']}/{$tempjam} * {$param['jumlah']}", true);
					$biaya[] = floor(($dt['jlh'] / $tempjam) * $param['jumlah']);
				}
				$tempjumlah = $tempjumlah - floor(($dt['jlh'] / $tempjam) * $param['jumlah']);
				// $biaya[] =floor(($bar->jlh/$tempjam)*$param['jumlah']); 
				$karyawanid[] = $param['karyawanid'];
				// $kegiatan[]=$bar->noakun."01";
				$kegiatan[] = $dt['setupkegiatan'];
				$kodeasset[] = '';
				$segment[] = $defSegment;
			}

			$totalalokasi += floor($dt['jlh'] * $param['jumlah']);
		}

		$cekKodeorg = trim($param['kodeorg']);
		$kodeJurnal = $group;
		#======================== Nomor Jurnal =============================
		# Get Journal Counter
		$tmpKonter = (int) $dtAllKonter[$orgpt][$cekKodeorg][$kodeJurnal] ?: 0;
		$konter = addZero($tmpKonter + 10, 3);  // kenapa + 10 ? biar GAK KETIMPA ATAU ADA YANG MASUK KETIKA PROSES INI JALAN

		#= cek konter dari jurnal
		$tmpKonterJurnal = (int) $dtAllKonterJurnal[$cekKodeorg][$kodeJurnal] ?: 0;
		$konterjurnal = ($tmpKonterJurnal + 10);  // kenapa + 10 ? biar GAK KETIMPA ATAU ADA YANG MASUK KETIKA PROSES INI JALAN

		if ($konterjurnal > $konter) {
			$konter = $konterjurnal;
		}
		$dataResInternal['header'] = array();
		$dataResInternal['detail'] = array();
		$dataResEksternal['header'] = array();
		$dataResEksternal['detail'] = array();
		$updateKonterInternal = [];
		$updateKonterEksternal = [];
		$konterSisiPengguna = addZero(10, 3);
		$konterInternal = addZero(10, 3);

		foreach ($biaya as $key => $nilai) {
			#periksa unit 
			$intern = true;

			$pengguna = substr($lokasi[$key], 0, 4);
			$cekLokasi = substr($lokasi[$key], 0, 2);
			if ($cekLokasi == 'AK' or $cekLokasi == 'PB') {
				if ($cekLokasi == 'AK') {
					#khusus project
					$pengguna = $dtPenggunaAKPB[$lokasi[$key]];
				}
				if ($cekLokasi == 'PB') {
					#khusus project
					$pengguna = $dtPenggunaAKPB[$lokasi[$key]];
				}
			}

			#ambil piutang ke pengguna
			$intraco = '';
			$interco = '';
			foreach ($dtCaco[$pengguna] as $cek => $val) {
				if ($cek == 'intra') {
					$intraco = $val;
				} else if ($cek == 'inter') {
					$interco = $val;
				}
			}

			$supplierrxx = '';
			if (isset($dtSuppX[$pengguna])) {
				$supplierrxx = $dtSuppX[$pengguna];
			} else if (isset($dtSuppXX[$pengguna])) {
				$supplierrxx = $pengguna;
				$pengguna = $dtSuppXX[$pengguna];
			}
			/*
			if ($intraco=='' || $interco==''){
				if($supplierrxx==''){
					throw new PDOException("EN : KUD code ".$lokasi[$key]." not register, please register KUD in Kebun->Setup->Nama Kud \n IND : KUD kode ".$lokasi[$key]."  belum didaftarkan, silahkan daftarkan KUD di Kebun->Setup->Nama Kud");	
				}else{
					throw new PDOException("EN : Account intraco or interco not available for ".$pengguna.". Please setting on menu Finance->setup->COA for Intra/Interco \n IN : Akun intraco datau interco belum didaftarkan ".$pengguna.". Silahkan hubungi Accounting untuk mendaftarkan akun interco dan intraco.");
				}
			}
			*/


			if ($intraco == '' || $interco == '') {
				if ($supplierrxx == '') {
					throw new PDOException("Jika Unit " . $lokasi[$key] . " adalah KUD maka daftarkan dahulu di KUD di Kebun->Setup->Nama Kud \n Jika unit bukan KUD maka daftarkan COA intra/interco Keuangan->setup->akun inter/intraco");
				}
			}

			#++++++++++++++++++++++++++++++++++++++
			$akunpekerjaan = $akun[$key];
			#++++++++++++++++++++++++++++++++++++++++
			$ptpengguna = '';
			if (count($dtIndukPengguna) > 0 && isset($dtIndukPengguna[$pengguna])) {
				$ptpengguna = $dtIndukPengguna[$pengguna];
			}

			$ptGudang = '';
			if (isset($dtArrIndukOrg[$param['kodeorg']])) {
				$ptGudang = $dtArrIndukOrg[$param['kodeorg']];
			}


			if ($supplierrxx != '') {
				$ptpengguna = $ptGudang;
				$pengguna = $param['kodeorg'];
				#echo $lokasi[$key]."xxx".$ptpengguna."xxx".$ptGudang."xxx".$supplierrxx."xxx".$pengguna;
				#exit('error');
			}
			#jika pt tidak sama maka pakai akun interco
			$akunpengguna = '';
			if ($ptGudang != $ptpengguna) {
				#ambil akun interco
				$intern = false;
				// $str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . $param['kodeorg'] . "' and jenis='inter'";
				if ($dtAkunHutangPengguna[$param['kodeorg']]['inter']) {
					$akunpengguna = $dtAkunHutangPengguna[$param['kodeorg']]['inter'];
				}
				$akunsendiri = $interco;
				if ($akunpengguna == '')
					throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
			} else if ($pengguna != $param['kodeorg']) { #jika satu pt beda kebun

				#ambil akun intraco
				$intern = false;
				if ($dtAkunHutangPengguna[$param['kodeorg']]['intra']) {
					$akunpengguna = $dtAkunHutangPengguna[$param['kodeorg']]['intra'];
				}
				$akunsendiri = $intraco;
				if ($akunpengguna == '')
					throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
			} else {
				$intern = true;
			}

			// echo "<pre>" . var_export($intern, true);
			// echo "<pre>" . var_export($akunpengguna, true);
			// echo "<pre>" . var_export($akunsendiri, true);
			// echo "<pre>" . var_export($akun[$key], true);
			// throw new PDOException("ERROR!");

			// exit('warning'.$param['kodevhc']);
			if ($intern) {
				#proses data
				$kodeJurnal = $group;
				#======================== Nomor Jurnal =============================

				$konterInternal = $konter;

				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konterInternal;
				#======================== /Nomor Jurnal ============================
				# Prep Header
				$dataResInternal['header'][] = array(
					'nojurnal' => $nojurnal,
					'kodejurnal' => $kodeJurnal,
					'tanggal' => $tanggal,
					'tanggalentry' => date('Ymd'),
					'posting' => 1,
					'totaldebet' => $biaya[$key],
					'totalkredit' => -1 * $biaya[$key],
					'amountkoreksi' => '0',
					'noreferensi' => 'ALK_WS_GYMH',
					'autojurnal' => '1',
					'matauang' => 'IDR',
					'kurs' => '1',
					'revisi' => '0'
				);
				# Data Detail
				$noUrut = 1;
				# Debet
				$dataResInternal['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggal,
					'nourut' => $noUrut,
					'noakun' => $akunpekerjaan,
					'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
					'jumlah' => $biaya[$key],
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $param['kodeorg'],
					'kodekegiatan' => $kegiatan[$key],
					'kodeasset' => $kodeasset[$key],
					'kodebarang' => '',
					'nik' => $karyawanid[$key],
					'kodecustomer' => '',
					'kodesupplier' => $supplierrxx,
					'noreferensi' => 'ALK_WS_GYMH',
					'noaruskas' => '',
					'kodevhc' => $param['kodevhc'],
					'nodok' => '',
					'kodeblok' => $lokasi[$key],
					'revisi' => '0',
					'kodesegment' => $segment[$key]
				);
				$noUrut++;

				# Kredit
				$dataResInternal['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggal,
					'nourut' => $noUrut,
					'noakun' => $akunalokws,
					'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
					'jumlah' => -1 * $biaya[$key],
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $param['kodeorg'],
					'kodekegiatan' => $kegiatan[$key],
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => $karyawanid[$key],
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => 'ALK_WS_GYMH',
					'noaruskas' => '',
					'kodevhc' => $param['kodevhc'],
					'nodok' => '',
					'kodeblok' => $lokasi[$key],
					'revisi' => '0',
					'kodesegment' => $segment[$key]
				);
				$noUrut++;
				$konter = addZero($konter + 1, 3);

				$updateKonterInternal[$param['kodeorg']][$kodeJurnal][] = $konterInternal;

				// $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
				// $owlPDO->exec($insHead);

				// #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
				// foreach ($dataRes['detail'] as $row) {
				// 	$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
				// 	$owlPDO->exec($insDet);
				// }
				// # Header and Detail inserted
				// #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
				// $updJurnal = updateQuery(
				// 	$dbname,
				// 	'keu_5kelompokjurnal',
				// 	array('nokounter' => $konter),
				// 	"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
				// );
				// $owlPDO->exec($updJurnal);
			} else {
				# Data Detail
				$noUrut = 1;
				#proses data

				$konterInternal = $konter;

				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . trim($param['kodeorg']) . "/" . $kodeJurnal . "/" . $konterInternal;
				$str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periode'] . "' and tutupbuku = '1' and kodeorg='" . $pengguna . "'";
				$rstr = fetchData($str);
				$arrUnit = array();
				if (count($rstr) == '0') { //klo blm tutup buku
					#======================== /Nomor Jurnal ============================
					# Prep Header
					$dataResEksternal['header'][] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => $tanggal,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $biaya[$key],
						'totalkredit' => -1 * $biaya[$key],
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_WS_GYMH',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);
					# Debet
					$dataResEksternal['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunsendiri,
						'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
						'jumlah' => $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_WS_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;

					# Kredit
					$dataResEksternal['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunalokws,
						'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
						'jumlah' => -1 * $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_WS_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);

					$noUrut++;
					$konter = addZero($konter + 1, 3);

					// variable ini yang dikirim buat update konter terbaru
					$updateKonterInternal[$param['kodeorg']][$kodeJurnal][] = $konterInternal;



					// $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					// $owlPDO->exec($insHead);

					// #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
					// foreach ($dataRes['detail'] as $row) {
					// 	$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
					// 	$owlPDO->exec($insDet);
					// }
					// # Header and Detail inserted
					// #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
					// $updJurnal = updateQuery(
					// 	$dbname,
					// 	'keu_5kelompokjurnal',
					// 	array('nokounter' => $konter),
					// 	"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
					// );
					// $owlPDO->exec($updJurnal);

					#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
					#sisi Pengguna
					$kodeJurnal = $group;
					#ambil periodeaktif pengguna
					$tgmulaid = $tanggal;

					$indukPengguna = $dtIndukPengguna[$pengguna];
					$konterSisiPengguna = (isset($dtAllKonter[$indukPengguna][$cekKodeorg][$kodeJurnal])) ? $dtAllKonter[$indukPengguna][$cekKodeorg][$kodeJurnal] : $konterSisiPengguna;

					$konterjurnalpengguna = (isset($dtAllKonterJurnal[$cekKodeorg][$kodeJurnal])) ? $dtAllKonterJurnal[$cekKodeorg][$kodeJurnal] : $konterSisiPengguna;

					if ($konterjurnalpengguna > $konterSisiPengguna) {
						$konterSisiPengguna = $konterjurnalpengguna;
					}

					$konterSisiPengguna = addZero($konterSisiPengguna, 3);


					// #======================== Nomor Jurnal =============================
					// # Get Journal Counter
					// $queryJ = selectQuery(
					// 	$dbname,
					// 	'keu_5kelompokjurnal',
					// 	'nokounter',
					// 	"kodeorg='" . $ptpengguna . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($pengguna) . "' and periode='" . $param['periode'] . "'  "
					// );
					// $tmpKonter = fetchData($queryJ);
					// $konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

					// #= cek konter dari jurnal
					// $str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($pengguna) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
					// $res = fetchdata($str);
					// foreach ($res as $bar) {
					// 	$konterjurnal = ($bar['konter'] + 1);
					// }

					// if ($konterjurnal > $konter) {
					// 	$konter = $konterjurnal;
					// }



					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tgmulaid) . "/" . trim($pengguna) . "/" . $kodeJurnal . "/" . $konterSisiPengguna;
					#======================== /Nomor Jurnal ============================
					# Prep Header
					// unset($dataRes['header']); //ganti header   
					$dataResEksternal['header'][] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => $tgmulaid,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $biaya[$key],
						'totalkredit' => -1 * $biaya[$key],
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_WS_GYMH',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);

					# Debet 1
					$noUrut = 1;
					// unset($dataRes['detail']); //ganti header 
					$dataResEksternal['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tgmulaid,
						'nourut' => $noUrut,
						'noakun' => $akunpekerjaan,
						'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
						'jumlah' => $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $pengguna,
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => $kodeasset[$key],
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_WS_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => $lokasi[$key],
						'revisi' => '0',
						'kodesegment' => $segment[$key]
					);
					$noUrut++;

					# Kredit 1
					$dataResEksternal['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tgmulaid,
						'nourut' => $noUrut,
						'noakun' => $akunpengguna,
						'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
						'jumlah' => -1 * $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $pengguna,
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_WS_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => $lokasi[$key],
						'revisi' => '0',
						'kodesegment' => $segment[$key]
					);
					$noUrut++;
					$konterSisiPengguna = addZero($konterSisiPengguna + 1, 3);

					$updateKonterEksternal[$pengguna][$kodeJurnal][] = $konterSisiPengguna;

					// $insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					// $owlPDO->exec($insHead);

					// #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
					// foreach ($dataRes['detail'] as $row) {
					// 	$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
					// 	$owlPDO->exec($insDet);
					// }
					// # Header and Detail inserted
					// #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
					// $updJurnal = updateQuery(
					// 	$dbname,
					// 	'keu_5kelompokjurnal',
					// 	array('nokounter' => $konter),
					// 	"kodeorg='" . $ptpengguna .
					// 		"' and kodekelompok='" . $kodeJurnal . "'   and kodeunit='" . trim($pengguna) . "' and periode='" . $param['periode'] . "'  "
					// );
					// $owlPDO->exec($updJurnal);

					//tutup 
				} else {
					$arrUnit[$pengguna] = $pengguna;
				}
			}
		}

		$updKonterInternal = false;
		$updKonterEksternal = false;
		if (count($dataResInternal['header']) > 0) {
			insertBatchDataJurnal('keu_jurnalht', $dataResInternal['header']);
			insertBatchDataJurnal('keu_jurnaldt', $dataResInternal['detail']);
			$updKonterInternal = true;
		}
		if (count($dataResEksternal['header']) > 0) {
			insertBatchDataJurnal('keu_jurnalht', $dataResEksternal['header']);
			insertBatchDataJurnal('keu_jurnaldt', $dataResEksternal['detail']);
			$updKonterEksternal = true;
		}

		if ($updKonterInternal) {
			echo $konterUpd = (int) max(array_values($updateKonterInternal[$param['kodeorg']][$kodeJurnal]));
			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
			$updJurnal = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $konterUpd),
				"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'   and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
			);
			$owlPDO->exec($updJurnal);
		}

		if ($updKonterEksternal) {
			foreach ($updateKonterEksternal as $kdorg => $valKonter) {
				$konterUpd = (int) max(array_values($updateKonterEksternal[$kdorg][$kodeJurnal]));
				$indukPTPengguna = $dtIndukPengguna[$kdorg];
				# Header and Detail inserted
				#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
				$updJurnal = updateQuery(
					$dbname,
					'keu_5kelompokjurnal',
					array('nokounter' => $konterUpd),
					"kodeorg='" . $indukPTPengguna . "' and kodekelompok='" . $kodeJurnal . "'   and kodeunit='" . trim($kdorg) . "' and periode='" . $param['periode'] . "'  "
				);
				$owlPDO->exec($updJurnal);
			}
		}

		if (count($arrUnit) != 0) {
			$tttttt = "Ada Unit Tidak Menerima Biaya Karna Sudah Tutup Buku : ";
			foreach ($arrUnit as $unitddd) {
				$tttttt .= $unitddd . "\n";
			}
			throw new PDOException($tttttt);
		}
	}
}

/**
 * Insert banyak row dengan multi-values INSERT per chunk.
 * - Kolom diambil dari keys baris pertama.
 * - Chunk default 500 row.
 */
function insertBatchDataJurnal($table, $rows, $chunkSize = 500)
{
	global $owlPDO;
	if (empty($rows)) return true;

	// ambil kolom dari baris pertama
	$columns = array_keys($rows[0]);
	$colSql  = '`' . implode('`,`', $columns) . '`';

	// pecah per chunk
	$chunks = array_chunk($rows, $chunkSize);

	foreach ($chunks as $chunk) {
		$valuesSqlParts = [];

		foreach ($chunk as $row) {
			$vals = [];

			foreach ($columns as $col) {
				$v = $row[$col] ?? '';

				// NULL dianggap string kosong
				if ($v === null || $v === '') {
					$vals[] = "''";
				}
				// string → escape + quote
				else {
					$v = trim((string)$v);
					$v = str_replace("'", "''", $v);
					$vals[] = "'" . $v . "'";
				}
			}

			$valuesSqlParts[] = '(' . implode(',', $vals) . ')';
		}

		$sql = "INSERT INTO `$table` ($colSql) VALUES " . implode(',', $valuesSqlParts);

		// debug jika perlu
		// if ($table === 'keu_jurnaldt') { throw new PDOException($sql); }

		$owlPDO->exec($sql);
	}

	return true;
}




function prosesGajiTraksi()
{
	global $conn;
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;
	#VHCG0	Gaji Kendaraan/A.Berat		
	#VHCG1	Biaya Lebur Kendaraan/A.Berat	
	#VHCG2	Biaya Tunjangan Lain Kend./A.Berat	
	#VHCG3	THR Kend./A.Berat	
	#VHCG4	Bonus Kend. A.Berat	
	#VHCG5	Pengobatan Kend./A.Berat
	#VHCG6

	/*
	karyawan BPJM
	Jika kendaraan milik SNPE 
	Jurnal BPJM
	D: interco
	K: hutang gaji =>akunkredit
	
	jurnal SNPE
	D:jurnal gaji =>akundebet
	K:interco
	*/

	/*
	D: jurnal gaji => akun debet
	K: hutang gaji => akun kredit
	*/


	#output pada jurnal kolom noreferensi ALK_TRK_GYMH  
	$komponenMap = [
		1  => 'VHCG0',
		14 => 'VHCG0',
		16 => 'VHCG1',
		32 => 'VHCG1',
		33 => 'VHCG1',
		70 => 'VHCG6',
		71 => 'VHCG6',
		72 => 'VHCG6',
		73 => 'VHCG6',
		80 => 'VHCG6',
		28 => 'VHCG3',
		26 => 'VHCG4',
		21 => 'VHCG5'
	];
	$group = isset($komponenMap[$param['komponen']]) ? $komponenMap[$param['komponen']] : 'VHCG2';

	$strInGroupKodeJurnal = "'" . implode("','", array_unique(array_values($komponenMap))) . "'";


	$akundebet = '';
	$akunkredit = '';
	$noakunParamJurnal['noakundebet'] = makeOption($dbname, "keu_5parameterjurnal", "jurnalid,noakundebet", "1=1 and jurnalid in (" . $strInGroupKodeJurnal . ")");
	$noakunParamJurnal['noakunkredit'] = makeOption($dbname, "keu_5parameterjurnal", "jurnalid,noakunkredit", "1=1 and jurnalid in (" . $strInGroupKodeJurnal . ")");

	$akundebet = isset($noakunParamJurnal['noakundebet'][$group]) ? $noakunParamJurnal['noakundebet'][$group] : '';
	$akunkredit = isset($noakunParamJurnal['noakunkredit'][$group]) ? $noakunParamJurnal['noakunkredit'][$group] : '';

	if ($akundebet == '' || $akunkredit == '') {
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen']);
	}

	#proses data
	$kodeJurnal = $group;
	#======================== Nomor Jurnal =============================
	# Get Journal Counter
	$queryJ = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$tmpKonter = fetchData($queryJ);
	$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

	# Transform No Jurnal dari No Transaksi
	$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
	#======================== /Nomor Jurnal ============================
	# Prep Header
	$kodekend = $param['mesin'];
	// echo "<pre>" . var_export($kodekend, true) . "</pre>";
	// throw new PDOException("TEST");


	if ($kodekend != 'NOVHC') {

		#= cek pemilik kendaraan ditraksi mana
		$str = "select kodetraksi from " . $dbname . ".vhc_5master_hist where periode='" . $param['periode'] . "' and kodevhc='" . $kodekend . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$unitpemilik = substr($bar['kodetraksi'], 0, 4);
		}
		if (count($res) == 0) {
			throw new PDOException("Jalankan proses tutup buku kendaraan, Traksi - Proses - Tutup Buku Kendaraan.");
		}


		if ($unitpemilik == $param['kodeorg']) {

			#= jika pemilik sama dengan pemilik traksinya
			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodeJurnal,
				'tanggal' => $tanggal,
				'tanggalentry' => date('Ymd'),
				'posting' => 1,
				'totaldebet' => $param['jumlah'],
				'totalkredit' => -1 * $param['jumlah'],
				'amountkoreksi' => '0',
				'noreferensi' => 'ALK_TRK_GYMH',
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);


			# Data Detail
			$noUrut = 1;

			# Debet
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akundebet,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;

			# Kredit
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akunkredit,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => -1 * $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;




			$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			$owlPDO->exec($insHead);

			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
			$detailErr = '';
			foreach ($dataRes['detail'] as $row) {
				$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
				$owlPDO->exec($insDet);
			}
			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
			$updJurnal = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $konter),
				"kodeorg='" . $orgpt .
					"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
			);
			$owlPDO->exec($updJurnal);
		} else {

			#=================================================================================================================================
			#=================================================================================================================================
			#=================================================================================================================================


			#= bentuk data kodept	
			$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)=4";
			$res = $owlPDO->query($str);
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$kodept[$bar['kodeorganisasi']] = $bar['induk'];
			}



			/*
			$akundebet=$bar->noakundebet;
			$akunkredit=$bar->noakunkredit;
			*/

			/*
			karyawan BPJM
			Jika kendaraan milik SNPE 
			Jurnal BPJM
			D: interco
			K: hutang gaji =>akunkredit
			
			jurnal SNPE
			D:jurnal gaji =>akundebet
			K:interco
			*/


			#= jurnal pengirim

			#proses data
			$kodeJurnal = $group;
			#======================== Nomor Jurnal =============================
			# Get Journal Counter
			$queryJ = selectQuery(
				$dbname,
				'keu_5kelompokjurnal',
				'nokounter',
				"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
			);
			$tmpKonter = fetchData($queryJ);
			$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

			# Transform No Jurnal dari No Transaksi
			$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
			#======================== /Nomor Jurnal ============================

			#= jika dia CACO
			if ($kodept[$unitpemilik] == $kodept[$param['kodeorg']]) {
				$jenisinduk = 'intra';
			} else {
				$jenisinduk = 'inter';
			}

			$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $unitpemilik . "'";
			$query = selectQuery($dbname, 'keu_5caco', 'akunpiutang', $whereNocaco);
			$noKon = fetchData($query);
			$noakuncaco = $noKon[0]['akunpiutang'];

			if ($noakuncaco == '') {
				exit("Warningsistem:No. Akun Interco/Intraco masih kosong untuk " . $param['kodeorg'] . " ke " . $unitpemilik . " atau sebaliknya, Hubungi Pihak Accounting / IT");
			}


			#= ht
			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodeJurnal,
				'tanggal' => $tanggal,
				'tanggalentry' => date('Ymd'),
				'posting' => 1,
				'totaldebet' => $param['jumlah'],
				'totalkredit' => -1 * $param['jumlah'],
				'amountkoreksi' => '0',
				'noreferensi' => 'ALK_TRK_GYMH',
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);

			# Data Detail
			$noUrut = 1;

			# Debet
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $noakuncaco,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;

			# Kredit
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akunkredit,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => -1 * $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;

			$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			$owlPDO->exec($insHead);

			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
			$detailErr = '';
			foreach ($dataRes['detail'] as $row) {
				$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
				$owlPDO->exec($insDet);
			}
			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
			$updJurnal = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $konter),
				"kodeorg='" . $orgpt .
					"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
			);
			$owlPDO->exec($updJurnal);



			#===============
			#= sisi penerima
			#===============

			$dataRes = array();

			$kodeJurnal = 'M';
			$queryJ = selectQuery(
				$dbname,
				'keu_5kelompokjurnal',
				'nokounter',
				"kodeorg='" . $kodept[$unitpemilik] . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $unitpemilik . "' and periode='" . $param['periode'] . "' "
			);
			$tmpKonter = fetchData($queryJ);
			$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

			# Transform No Jurnal dari No Transaksi
			$nojurnal = str_replace("-", "", $tanggal) . "/" . $unitpemilik . "/" . $kodeJurnal . "/" . $konter;


			$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $param['kodeorg'] . "'";
			$query = selectQuery($dbname, 'keu_5caco', 'akunhutang', $whereNocaco);
			$noKon = fetchData($query);
			$noakuncaco = $noKon[0]['akunhutang'];

			if ($noakuncaco == '') {
				exit("Warningsistem:No Akun Interco/Intraco masih kosong untuk " . $param['kodeorg'] . " ke " . $unitpemilik . " atau sebaliknya, Hubungi Pihak Accounting / IT ");
			}


			#= ht
			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodeJurnal,
				'tanggal' => $tanggal,
				'tanggalentry' => date('Ymd'),
				'posting' => 1,
				'totaldebet' => $param['jumlah'],
				'totalkredit' => -1 * $param['jumlah'],
				'amountkoreksi' => '0',
				'noreferensi' => 'ALK_TRK_GYMH',
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);

			# Data Detail
			$noUrut = 1;

			# Debet
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akundebet,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $unitpemilik,
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;

			# Kredit
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $noakuncaco,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => -1 * $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $unitpemilik,
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
			// echo"<pre>";
			// print_r($dataRes);
			// exit("Error:A");

			$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			$owlPDO->exec($insHead);

			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
			$detailErr = '';
			foreach ($dataRes['detail'] as $row) {
				$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
				$owlPDO->exec($insDet);
			}
			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
			$updJurnal = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $konter),
				"kodeorg='" . $kodept[$unitpemilik] .
					"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $unitpemilik . "' and periode='" . $param['periode'] . "' "
			);
			$owlPDO->exec($updJurnal);
		}



		$arrUnit = array();
		$param['kodevhc'] = $kodekend;
		#1 ambil periode akuntansi
		$str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi where 
		kodeorg ='" . $param['kodeorg'] . "' and tutupbuku=0 and periode='" . $param['periode'] . "'";
		$tgmulai = '';
		$tgsampai = '';
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows = owlBaris($res);
		if ($numrows < 1) {
			throw new PDOException("Tidak ada periode akuntansi untuk induk " . $param['kodeorg']);
		}
		while ($bar = $res->fetch()) {
			$tgsampai   = $bar->tanggalsampai;
			$tgmulai    = $bar->tanggalmulai;
		}
		if ($tgmulai == '' || $tgsampai == '')
			throw new PDOException("Periode akuntasi tidak terdaftar");

		#2 output pada jurnal kolom noreferensi ALK_KERJA_AB  

		$group = 'VHC0';
		#ambil akun alokasi
		$str = "select noakundebet from " . $dbname . ".keu_5parameterjurnal
		where jurnalid='" . $group . "' limit 1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows = owlBaris($res);
		if ($numrows < 1)
			throw new PDOException("No.Akun pada parameterjurnal belum ada untuk VHC0");
		else {
			$bar = $res->fetch();
			$akunalok = $bar->noakundebet;
		}
		#3 ambil semua lokasi kegiatan
		$str = "select sum(a.jumlah) as jlh,a.alokasibiaya,b.noakun,b.setupkegiatan from " . $dbname . ".vhc_rundt_detail a
		left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
		left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi     
		where c.kodevhc='" . $param['kodevhc'] . "'
		and c.tanggal>='" . $tgmulai . "' and c.tanggal <='" . $tgsampai . "' and alokasibiaya!='' 
		and jenispekerjaan!=''    
		group by jenispekerjaan,noakun,alokasibiaya,kodesegment order by tanggal asc";

		//exit("Error:".$str);
		$lokasi = array();
		$biaya = array();
		$karyawanid = array();
		$jam  = array();
		$akun = array();
		$kodeasset = array();
		$segment = array();
		$ttl = 0;
		$no = 0;
		$tempjam = 0;
		$counttemp = 0;
		$tempjumlah = $param['jumlah'];
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$tempjam += $bar->jlh;
			$counttemp++;
		}
		// exit("Error:".$param['jumlahpembulatan']);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {

			#= insert pembulatan di baris 1
			$no++;
			// if($no>1){
			// $param['jumlahpembulatan']=0;
			// }


			#kusus jika project
			if (substr($bar->alokasibiaya, 0, 2) == 'AK' or substr($bar->alokasibiaya, 0, 2) == 'PB') {
				if (substr($bar->alokasibiaya, 0, 2) == 'AK') {
					#ambil akun aktiva dalam konstruksi
					// alokasi ke AK-BG98000008, kalo 3,3 dapetnya BG9... sementara setupnya cuman ada BG. jadi ganti 3,2
					$tipeasset = substr($bar->alokasibiaya, 3, 2);
					$tipeasset =  str_replace("0", "", $tipeasset);
					$str1 = "select akunak from " . $dbname . ".sdm_5tipeasset where kodetipe='" . $tipeasset . "'";
					$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
					$res1->setFetchMode(PDO::FETCH_OBJ);
					$numrows1 = owlBaris($res1);
					if ($numrows1 < 1) {
						throw new PDOException("Akun aktiva dalam konstruksi untuk " . $tipeasset . " beum disetting dari keuangan->setup->tipeasset");
					} else {
						while ($bar1 = $res1->fetch()) {
							if ($bar1->akunak == '')
								throw new PDOException("Akun aktiva dalam konstruksi untuk " . $tipeasset . " belum disetting dari keuangan->setup->tipeasset");
							else
								$akun[] = $bar1->akunak;
						}
					}
					$kodeasset[] = $bar->alokasibiaya;
				}
				#jika pabrikasi
				if (substr($bar->alokasibiaya, 0, 2) == 'PB') {
					$sData = "select noakundebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='PBR3'";
					$rData = fetchData($sData);
					if (count($rData) == 0) {
						throw new PDOException("Akun untuk alokasi traksi pabrikasi belum di setting pada keuangan->setup->parameter jurnal");
					} else {
						$akun[] = $rData[0]['noakundebet'];
						$kodeasset[] = '';
					}
				}
				$lokasi[] = $bar->alokasibiaya;
				$jam[] = $bar->jlh;
				if ($no == $counttemp) {
					$biaya[] = $tempjumlah;
					$totalxxx += $tempjumlah;
				} else {
					$biaya[] = floor(($bar->jlh / $tempjam) * $param['jumlah']);
					$totalxxx += floor(($bar->jlh / $tempjam) * $param['jumlah']);
				}
				$tempjumlah = $tempjumlah - floor(($bar->jlh / $tempjam) * $param['jumlah']);
				$karyawanid[] = $param['karyawanid'];
				$kegiatan[] = '';
				$segment[] = $bar->kodesegment;
			} else {
				$lokasi[] = $bar->alokasibiaya;
				$akun[]  = $bar->noakun;
				$jam[] = $bar->jlh;
				if ($no == $counttemp) {
					$biaya[] = $tempjumlah;
					$totalxxx += $tempjumlah;
				} else {
					$biaya[] = floor(($bar->jlh / $tempjam) * $param['jumlah']);
					$totalxxx += floor(($bar->jlh / $tempjam) * $param['jumlah']);
				}
				$tempjumlah = $tempjumlah - floor(($bar->jlh / $tempjam) * $param['jumlah']);
				// $biaya[] =floor(($bar->jlh/$tempjam)*$param['jumlah']); 
				$karyawanid[] = $param['karyawanid'];
				// $kegiatan[]=$bar->noakun."01";
				$kegiatan[] = $bar->setupkegiatan;
				$kodeasset[] = '';
				$segment[] = $bar->kodesegment;


				// $templokasi=$bar->alokasibiaya;
				// $tempakun=$bar->noakun;
				// $tempkegiatan=$bar->setupkegiatan;
				// $tempbiaya=0;
				// $tempkodeasset=0;
				// $tempsegment=$bar->kodesegment;
			}
			$totalalokasi += floor($bar->jlh * $param['jumlah']);
		}

		// if($totalxxx==''){
		// 	$totalxxx=0;
		// }
		// $datatotalx = array(
		//                 'id'=>'',
		//                 'kodetraksi'=>$param['kodevhc'],
		//                 'jumlah' =>$totalxxx
		//             );
		//             $insHead = insertQuery($dbname,'totalbiayatraksi',$datatotalx);
		// $owlPDO->exec($insHead); 

		$strh = "select distinct noakundebet,sampaidebet  from " . $dbname . ".keu_5parameterjurnal where  jurnalid='LPVHC'";
		$resh = $owlPDO->query($strh) or die(print " Gagal: " . PDOException::getMessage());
		$resh->setFetchMode(PDO::FETCH_OBJ);
		while ($barh = $resh->fetch()) {
			$akunkdari = $barh->noakundebet;
			$akunksampai = $barh->sampaidebet;
		}

		// $str = "select sum(jumlah) as jumlah, kodevhc from ".$dbname.".keu_jurnaldt_vw where  noakun not in (4110299,4110199)  and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' and (noakun between '".$akunkdari."' and '".$akunksampai."') and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL) and kodevhc='".$param['kodevhc']."' ";

		// $res=fetchdata($str);
		// foreach($res as $bar){
		// 	$param['jumlahbiayakendaraan']=$bar['jumlah'];
		// }

		// if($param['kodevhc']=='SD4ETR0009'){
		// echo $param['jumlahbiayakendaraan']._.$totalalokasi;
		// exit("Error:a".$str);
		// }

		//if($param['jumlahbiayakendaraan']-$totalalokasi!='0'){
		// $lokasi[]=$templokasi;
		// $akun[]  =$tempakun;
		// $jam[] =$tempjam;
		// // 
		// // exit("Error:".$param['jumlahbiayakendaraan']._.$totalalokasi);
		// $kegiatan[]=$tempkegiatan;
		// $kodeasset[]=$tempkodeasset;
		// $segment[]=$tempsegment;
		//}
		// echo "<pre>";
		// print_r($karyawanid);
		// echo "</pre>";
		//throw new PDOException("Error");


		foreach ($biaya as $key => $nilai) {
			#periksa unit 
			$dataRes['header'] = array();
			$dataRes['detail'] = array();
			$intern = true;

			$pengguna = substr($lokasi[$key], 0, 4);
			if (substr($lokasi[$key], 0, 2) == 'AK' or substr($lokasi[$key], 0, 2) == 'PB') {
				if (substr($lokasi[$key], 0, 2) == 'AK') {
					#khusus project
					$str = "select kodeorg from " . $dbname . ".project where kode='" . $lokasi[$key] . "'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_OBJ);
					while ($bar = $res->fetch()) {
						$pengguna = $bar->kodeorg;
						//$lokasi[$key]=$lokasi[$key];
					}
				}
				if (substr($lokasi[$key], 0, 2) == 'PB') {
					#khusus project
					$str = "select kodeorg from " . $dbname . ".pabrikasi_5masterht where kodepabrikasi='" . $lokasi[$key] . "'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_OBJ);
					while ($bar = $res->fetch()) {
						$pengguna = $bar->kodeorg;
						//$lokasi[$key]=$lokasi[$key];
					}
				}
			}

			#ambil piutang ke pengguna
			$str = "select akunpiutang,jenis from " . $dbname . ".keu_5caco where kodeorg='" . $pengguna . "'";
			$intraco = '';
			$interco = '';
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				if ($bar->jenis == 'intra')
					$intraco = $bar->akunpiutang;
				else
					$interco = $bar->akunpiutang;
			}

			$supplierrxx = '';
			$str = "select * from " . $dbname . ".kebun_5namakud where status='1' and afdeling='" . substr($lokasi[$key], 0, 6) . "'";
			$res = fetchData($str);
			foreach ($res as $row => $lsDt) {
				$supplierrxx = $lsDt['kodesupplier'];
			}
			if ($supplierrxx == '') {
				$strx = "select * from " . $dbname . ".kebun_5namakud where status='1' and kodesupplier='" . substr($lokasi[$key], 0, 6) . "'";
				$resx = fetchData($strx);
				foreach ($resx as $rowx => $lsDtx) {
					$supplierrxx = $lsDtx['kodesupplier'];
					$pengguna = $lsDtx['kodeunit'];
				}
			}
			/*
				if ($intraco=='' || $interco==''){
					if($supplierrxx==''){
						throw new PDOException("EN : KUD code ".$lokasi[$key]." not register, please register KUD in Kebun->Setup->Nama Kud \n IND : KUD kode ".$lokasi[$key]."  belum didaftarkan, silahkan daftarkan KUD di Kebun->Setup->Nama Kud");	
					}else{
						throw new PDOException("EN : Account intraco or interco not available for ".$pengguna.". Please setting on menu Finance->setup->COA for Intra/Interco \n IN : Akun intraco datau interco belum didaftarkan ".$pengguna.". Silahkan hubungi Accounting untuk mendaftarkan akun interco dan intraco.");
					}
				}
				*/


			if ($intraco == '' || $interco == '') {
				if ($supplierrxx == '') {
					throw new PDOException("Jika Unit " . $lokasi[$key] . " adalah KUD maka daftarkan dahulu di KUD di Kebun->Setup->Nama Kud \n Jika unit bukan KUD maka daftarkan COA intra/interco Keuangan->setup->akun inter/intraco");
				}
			}



			#++++++++++++++++++++++++++++++++++++++
			$akunpekerjaan = $akun[$key];
			#++++++++++++++++++++++++++++++++++++++++
			$ptpengguna = '';
			$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $pengguna . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$ptpengguna = $bar->induk;
			}

			$ptGudang = '';
			$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $param['kodeorg'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$ptGudang = $bar->induk;
			}


			if ($supplierrxx != '') {
				$ptpengguna = $ptGudang;
				$pengguna = $param['kodeorg'];
				#echo $lokasi[$key]."xxx".$ptpengguna."xxx".$ptGudang."xxx".$supplierrxx."xxx".$pengguna;
				#exit('error');
			}
			#jika pt tidak sama maka pakai akun interco
			$akunpengguna = '';
			if ($ptGudang != $ptpengguna) {
				#ambil akun interco
				$intern = false;
				$str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . $param['kodeorg'] . "' and jenis='inter'";
				$akunpengguna = '';
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $res->fetch()) {
					$akunpengguna = $bar->akunhutang;
				}
				$akunsendiri = $interco;
				if ($akunpengguna == '')
					throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
			} else if ($pengguna != $param['kodeorg']) { #jika satu pt beda kebun
				#ambil akun intraco
				$intern = false;
				$str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . $param['kodeorg'] . "' and jenis='intra'";
				$akunpengguna = '';
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $res->fetch()) {
					$akunpengguna = $bar->akunhutang;
				}
				$akunsendiri = $intraco;
				if ($akunpengguna == '')
					throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
			} else {
				$intern = true;
			}

			if ($intern) {
				#proses data
				$kodeJurnal = $group;
				#======================== Nomor Jurnal =============================
				# Get Journal Counter
				$queryJ = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
				);
				$tmpKonter = fetchData($queryJ);
				$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);


				#= cek konter dari jurnal
				$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$konterjurnal = ($bar['konter'] + 1);
				}

				if ($konterjurnal > $konter) {
					$konter = $konterjurnal;
				}


				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
				#======================== /Nomor Jurnal ============================
				# Prep Header
				$dataRes['header'] = array(
					'nojurnal' => $nojurnal,
					'kodejurnal' => $kodeJurnal,
					'tanggal' => $tanggal,
					'tanggalentry' => date('Ymd'),
					'posting' => 1,
					'totaldebet' => $biaya[$key],
					'totalkredit' => -1 * $biaya[$key],
					'amountkoreksi' => '0',
					'noreferensi' => 'ALK_TRK_GYMH',
					'autojurnal' => '1',
					'matauang' => 'IDR',
					'kurs' => '1',
					'revisi' => '0'
				);
				# Data Detail
				$noUrut = 1;
				# Debet
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggal,
					'nourut' => $noUrut,
					'noakun' => $akunpekerjaan,
					'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
					'jumlah' => $biaya[$key],
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $param['kodeorg'],
					'kodekegiatan' => $kegiatan[$key],
					'kodeasset' => $kodeasset[$key],
					'kodebarang' => '',
					'nik' => $karyawanid[$key],
					'kodecustomer' => '',
					'kodesupplier' => $supplierrxx,
					'noreferensi' => 'ALK_TRK_GYMH',
					'noaruskas' => '',
					'kodevhc' => $param['kodevhc'],
					'nodok' => '',
					'kodeblok' => $lokasi[$key],
					'revisi' => '0',
					'kodesegment' => $segment[$key]
				);
				$noUrut++;

				# Kredit
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggal,
					'nourut' => $noUrut,
					'noakun' => $akunalok,
					'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
					'jumlah' => -1 * $biaya[$key],
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $param['kodeorg'],
					'kodekegiatan' => $kegiatan[$key],
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => $karyawanid[$key],
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => 'ALK_TRK_GYMH',
					'noaruskas' => '',
					'kodevhc' => $param['kodevhc'],
					'nodok' => '',
					'kodeblok' => $lokasi[$key],
					'revisi' => '0',
					'kodesegment' => $segment[$key]
				);
				$noUrut++;
				$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
				$owlPDO->exec($insHead);

				#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
				foreach ($dataRes['detail'] as $row) {
					$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
					$owlPDO->exec($insDet);
				}
				# Header and Detail inserted
				#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
				$updJurnal = updateQuery(
					$dbname,
					'keu_5kelompokjurnal',
					array('nokounter' => $konter),
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
				);
				$owlPDO->exec($updJurnal);
			} else {
				# Data Detail
				$noUrut = 1;
				#proses data
				$kodeJurnal = $group;
				#======================== Nomor Jurnal =============================
				# Get Journal Counter
				$queryJ = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
				);
				$tmpKonter = fetchData($queryJ);
				$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

				#= cek konter dari jurnal
				$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$konterjurnal = ($bar['konter'] + 1);
				}

				if ($konterjurnal > $konter) {
					$konter = $konterjurnal;
				}



				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . trim($param['kodeorg']) . "/" . $kodeJurnal . "/" . $konter;
				$str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periode'] . "' and tutupbuku = '1' 
	         	and kodeorg='" . $pengguna . "'";
				$rstr = fetchData($str);
				$arrUnit = array();
				if (count($rstr) == '0') { //klo blm tutup buku
					#======================== /Nomor Jurnal ============================
					# Prep Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => $tanggal,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $biaya[$key],
						'totalkredit' => -1 * $biaya[$key],
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_TRK_GYMH',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);
					# Debet
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunsendiri,
						'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
						'jumlah' => $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_TRK_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;

					# Kredit
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunalok,
						'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
						'jumlah' => -1 * $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_TRK_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);

					$noUrut++;
					$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($insHead);

					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
					foreach ($dataRes['detail'] as $row) {
						$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
						$owlPDO->exec($insDet);
					}
					# Header and Detail inserted
					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
					$updJurnal = updateQuery(
						$dbname,
						'keu_5kelompokjurnal',
						array('nokounter' => $konter),
						"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
					);
					$owlPDO->exec($updJurnal);

					#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
					#sisi Pengguna
					$kodeJurnal = $group;
					#ambil periodeaktif pengguna
					$tgmulaid = $tanggal;


					#======================== Nomor Jurnal =============================
					# Get Journal Counter
					$queryJ = selectQuery(
						$dbname,
						'keu_5kelompokjurnal',
						'nokounter',
						"kodeorg='" . $ptpengguna . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($pengguna) . "' and periode='" . $param['periode'] . "'  "
					);
					$tmpKonter = fetchData($queryJ);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

					#= cek konter dari jurnal
					$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($pengguna) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$konterjurnal = ($bar['konter'] + 1);
					}

					if ($konterjurnal > $konter) {
						$konter = $konterjurnal;
					}



					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tgmulaid) . "/" . trim($pengguna) . "/" . $kodeJurnal . "/" . $konter;
					#======================== /Nomor Jurnal ============================
					# Prep Header
					unset($dataRes['header']); //ganti header   
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => $tgmulaid,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $biaya[$key],
						'totalkredit' => -1 * $biaya[$key],
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_TRK_GYMH',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);

					# Debet 1
					$noUrut = 1;
					unset($dataRes['detail']); //ganti header 
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tgmulaid,
						'nourut' => $noUrut,
						'noakun' => $akunpekerjaan,
						'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
						'jumlah' => $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $pengguna,
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => $kodeasset[$key],
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_TRK_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => $lokasi[$key],
						'revisi' => '0',
						'kodesegment' => $segment[$key]
					);
					$noUrut++;

					# Kredit 1
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tgmulaid,
						'nourut' => $noUrut,
						'noakun' => $akunpengguna,
						'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
						'jumlah' => -1 * $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $pengguna,
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_TRK_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => $lokasi[$key],
						'revisi' => '0',
						'kodesegment' => $segment[$key]
					);
					$noUrut++;

					$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($insHead);

					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
					foreach ($dataRes['detail'] as $row) {
						$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
						$owlPDO->exec($insDet);
					}
					# Header and Detail inserted
					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
					$updJurnal = updateQuery(
						$dbname,
						'keu_5kelompokjurnal',
						array('nokounter' => $konter),
						"kodeorg='" . $ptpengguna .
							"' and kodekelompok='" . $kodeJurnal . "'   and kodeunit='" . trim($pengguna) . "' and periode='" . $param['periode'] . "'  "
					);
					$owlPDO->exec($updJurnal);

					//tutup 
				} else {
					$arrUnit[$pengguna] = $pengguna;
				}
			}
		}
		if (count($arrUnit) != 0) {
			$tttttt = "Ada Unit Tidak Menerima Biaya Karna Sudah Tutup Buku : ";
			foreach ($arrUnit as $unitddd) {
				$tttttt .= $unitddd . "\n";
			}
			throw new PDOException($tttttt);
		}
	} else { #jika tidak maka jika workshop proses ke workshop, jika tidak maka miaya umum
		if ($param['tipeorganisasi'] == 'WORKSHOP') {
			prosesGajiWs();
		} else if ($param['tipeorganisasi'] == 'TRAKSI') {
			// prosesGajiWs(); # Sementara
			prosesGajiTraksiUmum();
		} else {
			prosesGajiKebun();
		}
	}
}

function prosesGajiTraksiUmum()
{
	global $conn;
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;
	#WSG0	Gaji Bengkel	
	#WSG1	Biaya Lebur Bengkel
	#WSG2	Biaya Tunjangan Lain Bengkel
	#WSG3	THR Bengkel	
	#WSG4	Bonus Bengkel
	#WSG5	Pengobatan Bengkel

	#====================================================================================================#
	# NEW
	#====================================================================================================#
	$kodekend = $param['mesin'];
	$param['kodevhc'] = $kodekend;

	$arrUnit = array();
	#1 ambil periode akuntansi
	$str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi where 
	kodeorg ='" . $param['kodeorg'] . "' and tutupbuku=0 and periode='" . $param['periode'] . "'";
	$tgmulai = '';
	$tgsampai = '';
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($res);
	if ($numrows < 1) {
		throw new PDOException("Tidak ada periode akuntansi untuk induk " . $param['kodeorg']);
	}
	while ($bar = $res->fetch()) {
		$tgsampai   = $bar->tanggalsampai;
		$tgmulai    = $bar->tanggalmulai;
	}
	if ($tgmulai == '' || $tgsampai == '')
		throw new PDOException("Periode akuntasi tidak terdaftar");

	#2 output pada jurnal kolom noreferensi ALK_KERJA_AB  
	# Pindahkan ke Bawah
	$group = 'VHC0';


	$arrKodeGroupKodeJurnal = array('VHC0', 'PBR3', 'LPVHC');
	$strInGroupKodeJurnal = implode("', '", $arrKodeGroupKodeJurnal);

	$noakunParamJurnal = [];
	$noakunParamJurnal['noakundebet'] = makeOption($dbname, "keu_5parameterjurnal", "jurnalid,noakundebet", "1=1 and jurnalid in ('" . $strInGroupKodeJurnal . "')");
	$noakunParamJurnal['noakunkredit'] = makeOption($dbname, "keu_5parameterjurnal", "jurnalid,noakunkredit", "1=1 and jurnalid in ('" . $strInGroupKodeJurnal . "')");

	// echo "<pre>" . var_export($noakunParamJurnal, true);
	// throw new PDOException("No.Akun pada parameterjurnal belum ada untuk VHC0");



	#ambil akun alokasi
	$akunalok = (isset($noakunParamJurnal['noakundebet'][$group])) ? $noakunParamJurnal['noakundebet'][$group] : '';

	if ($akunalok == '') {
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk VHC0");
	}

	#2.5 ambil semua vhc yang terdapat karywan itu mengerjakannya
	$str = "SELECT DISTINCT a.kodevhc FROM {$dbname}.vhc_penggantianht a
					LEFT JOIN {$dbname}.vhc_penggantiandt_karyawan b ON a.notransaksi=b.notransaksi
					WHERE b.karyawanid='{$param['karyawanid']}'
					AND a.tanggal BETWEEN '{$tgmulai}' AND '{$tgsampai}'
					GROUP BY a.kodevhc ORDER BY a.tanggal ASC";

	$kodevhcx = '';
	$arrKodeVHC = [];
	$res = fetchData($str);
	foreach ($res as $dt) {
		$arrKodeVHC[] = $dt['kodevhc'];
	}
	if (count($arrKodeVHC) > 0) {
		$kodevhcx = implode("', '", $arrKodeVHC);
	}
	#====================================================================================================#
	# END NEW
	#====================================================================================================#

	if ($kodevhcx == '') { # Jika Workshop tidak memperbaiki kendaraan

		# Cek Sipil
		$sql = "SELECT SUM(umr) as umrgaji,nik 
						FROM {$dbname}.vhc_spl_kehadiran_vw 
						WHERE 5=5 and nik='{$param['karyawanid']}' 
						and tanggal LIKE '{$param['periode']}%'";
		$res = fetchData($sql);
		$umrgaji = $res[0]['umrgaji'];

		if ($param['jumlah'] - $umrgaji > 0) {
			# Sisa
			$umrgajibaru = $param['jumlah'] - $umrgaji;

			#output pada jurnal kolom noreferensi ALK_TRK_GYMH  
			$komponenMap = [
				1  => 'VHCU0',
				14 => 'VHCU0',
				16 => 'VHCU1',
				32 => 'VHCU1',
				33 => 'VHCU1',
				70 => 'VHCU6',
				71 => 'VHCU6',
				72 => 'VHCU6',
				73 => 'VHCU6',
				80 => 'VHCU6',
				28 => 'VHCU3',
				26 => 'VHCU4',
				21 => 'VHCU5'
			];
			#output pada jurnal kolom noreferensi ALK_WS_GYMH  
			$group = $komponenMap[$param['komponen']];

			if ($group == '') {
				$group = 'VHCU2';  //defaultnya tunjangan
			}

			$akundebet = (isset($noakunParamJurnal['noakundebet'][$group])) ? $noakunParamJurnal['noakundebet'][$group] : '';
			$akunkredit = (isset($noakunParamJurnal['noakunkredit'][$group])) ? $noakunParamJurnal['noakunkredit'][$group] : '';

			if ($akundebet == '' || $akunkredit == '') {
				throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen'] . " " . $group);
			}

			#proses data
			$kodeJurnal = $group;
			#======================== Nomor Jurnal =============================
			# Get Journal Counter
			$queryJ = selectQuery(
				$dbname,
				'keu_5kelompokjurnal',
				'nokounter',
				"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
			);
			$tmpKonter = fetchData($queryJ);
			$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

			# Transform No Jurnal dari No Transaksi
			$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
			#======================== /Nomor Jurnal ============================
			# Prep Header
			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodeJurnal,
				'tanggal' => $tanggal,
				'tanggalentry' => date('Ymd'),
				'posting' => 1,
				'totaldebet' => $umrgajibaru,
				'totalkredit' => -1 * $umrgajibaru,
				'amountkoreksi' => '0',
				'noreferensi' => 'ALK_TRKU_GYMH',
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);
			# Data Detail
			$noUrut = 1;

			# Debet
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akundebet,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Traksi Umum",
				'jumlah' => $umrgajibaru,
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRKU_GYMH',
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;

			# Kredit
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akunkredit,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Traksi Umum",
				'jumlah' => -1 * $umrgajibaru,
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRKU_GYMH',
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;

			$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			$owlPDO->exec($insHead);

			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
			foreach ($dataRes['detail'] as $row) {
				$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
				$owlPDO->exec($insDet);
			}
			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
			$updJurnal = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $konter),
				"kodeorg='" . $orgpt .
					"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
			);
			$owlPDO->exec($updJurnal);
		}
	} else { # Jika Workshop memperbaiki kendaraan
		#3 ambil semua lokasi kegiatan
		$str = "select sum(a.jumlah) as jlh,a.alokasibiaya,b.noakun,b.setupkegiatan from " . $dbname . ".vhc_rundt_detail a
				left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
				left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi     
				where c.kodevhc in (" . $kodevhcx . ")
				and c.tanggal>='" . $tgmulai . "' and c.tanggal <='" . $tgsampai . "' and alokasibiaya!='' 
				and jenispekerjaan!=''    
				group by jenispekerjaan,noakun,alokasibiaya,kodesegment order by tanggal asc";

		// exit("Error:".$str);

		$lokasi = array();
		$biaya = array();
		$karyawanid = array();
		$jam  = array();
		$akun = array();
		$kodeasset = array();
		$segment = array();
		$ttl = 0;
		$no = 0;
		$tempjam = 0;
		$counttemp = 0;
		$tempjumlah = $param['jumlah'];
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$tempjam += $bar->jlh;
			$counttemp++;
		}
		// exit("Error:".$param['jumlahpembulatan']);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {

			#= insert pembulatan di baris 1
			$no++;
			// if($no>1){
			// $param['jumlahpembulatan']=0;
			// }


			#kusus jika project
			if (substr($bar->alokasibiaya, 0, 2) == 'AK' or substr($bar->alokasibiaya, 0, 2) == 'PB') {
				if (substr($bar->alokasibiaya, 0, 2) == 'AK') {
					#ambil akun aktiva dalam konstruksi
					// alokasi ke AK-BG98000008, kalo 3,3 dapetnya BG9... sementara setupnya cuman ada BG. jadi ganti 3,2
					$tipeasset = substr($bar->alokasibiaya, 3, 2);
					$tipeasset =  str_replace("0", "", $tipeasset);
					$str1 = "select akunak from " . $dbname . ".sdm_5tipeasset where kodetipe='" . $tipeasset . "'";
					$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
					$res1->setFetchMode(PDO::FETCH_OBJ);
					$numrows1 = owlBaris($res1);
					if ($numrows1 < 1) {
						throw new PDOException("Akun aktiva dalam konstruksi untuk " . $tipeasset . " belum disetting dari keuangan->setup->tipeasset");
					} else {
						while ($bar1 = $res1->fetch()) {
							if ($bar1->akunak == '')
								throw new PDOException("Akun aktiva dalam konstruksi untuk " . $tipeasset . " belum disetting dari keuangan->setup->tipeasset");
							else
								$akun[] = $bar1->akunak;
						}
					}
					$kodeasset[] = $bar->alokasibiaya;
				}
				#jika pabrikasi
				if (substr($bar->alokasibiaya, 0, 2) == 'PB') {
					$sData = "select noakundebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='PBR3'";
					$rData = fetchData($sData);
					if (count($rData) == 0) {
						throw new PDOException("Akun untuk alokasi traksi pabrikasi belum di setting pada keuangan->setup->parameter jurnal");
					} else {
						$akun[] = $rData[0]['noakundebet'];
						$kodeasset[] = '';
					}
				}
				$lokasi[] = $bar->alokasibiaya;
				$jam[] = $bar->jlh;
				if ($no == $counttemp) {
					$biaya[] = $tempjumlah;
				} else {
					$biaya[] = floor(($bar->jlh / $tempjam) * $param['jumlah']);
				}
				$tempjumlah = $tempjumlah - floor(($bar->jlh / $tempjam) * $param['jumlah']);
				$karyawanid[] = $param['karyawanid'];
				$kegiatan[] = '';
				$segment[] = $bar->kodesegment;
			} else {
				$lokasi[] = $bar->alokasibiaya;
				$akun[]  = $bar->noakun;
				$jam[] = $bar->jlh;
				if ($no == $counttemp) {
					$biaya[] = $tempjumlah;
				} else {
					$biaya[] = floor(($bar->jlh / $tempjam) * $param['jumlah']);
				}
				$tempjumlah = $tempjumlah - floor(($bar->jlh / $tempjam) * $param['jumlah']);
				// $biaya[] =floor(($bar->jlh/$tempjam)*$param['jumlah']); 
				$karyawanid[] = $param['karyawanid'];
				// $kegiatan[]=$bar->noakun."01";
				$kegiatan[] = $bar->setupkegiatan;
				$kodeasset[] = '';
				$segment[] = $bar->kodesegment;


				// $templokasi=$bar->alokasibiaya;
				// $tempakun=$bar->noakun;
				// $tempkegiatan=$bar->setupkegiatan;
				// $tempbiaya=0;
				// $tempkodeasset=0;
				// $tempsegment=$bar->kodesegment;
			}
			$totalalokasi += floor($bar->jlh * $param['jumlah']);
		}


		$strh = "select distinct noakundebet,sampaidebet  from " . $dbname . ".keu_5parameterjurnal where  jurnalid='LPVHC'";
		$resh = $owlPDO->query($strh) or die(print " Gagal: " . PDOException::getMessage());
		$resh->setFetchMode(PDO::FETCH_OBJ);
		while ($barh = $resh->fetch()) {
			$akunkdari = $barh->noakundebet;
			$akunksampai = $barh->sampaidebet;
		}

		// $str = "select sum(jumlah) as jumlah, kodevhc from ".$dbname.".keu_jurnaldt_vw where  noakun not in (4110299,4110199)  and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' and (noakun between '".$akunkdari."' and '".$akunksampai."') and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL) and kodevhc='".$param['kodevhc']."' ";

		// $res=fetchdata($str);
		// foreach($res as $bar){
		// 	$param['jumlahbiayakendaraan']=$bar['jumlah'];
		// }

		// if($param['kodevhc']=='SD4ETR0009'){
		// echo $param['jumlahbiayakendaraan']._.$totalalokasi;
		// exit("Error:a".$str);
		// }

		//if($param['jumlahbiayakendaraan']-$totalalokasi!='0'){
		// $lokasi[]=$templokasi;
		// $akun[]  =$tempakun;
		// $jam[] =$tempjam;
		// // 
		// // exit("Error:".$param['jumlahbiayakendaraan']._.$totalalokasi);
		// $kegiatan[]=$tempkegiatan;
		// $kodeasset[]=$tempkodeasset;
		// $segment[]=$tempsegment;
		//}
		// echo "<pre>";
		// print_r($karyawanid);
		// echo "</pre>";
		//throw new PDOException("Error");


		foreach ($biaya as $key => $nilai) {
			#periksa unit 
			$dataRes['header'] = array();
			$dataRes['detail'] = array();
			$intern = true;

			$pengguna = substr($lokasi[$key], 0, 4);
			if (substr($lokasi[$key], 0, 2) == 'AK' or substr($lokasi[$key], 0, 2) == 'PB') {
				if (substr($lokasi[$key], 0, 2) == 'AK') {
					#khusus project
					$str = "select kodeorg from " . $dbname . ".project where kode='" . $lokasi[$key] . "'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_OBJ);
					while ($bar = $res->fetch()) {
						$pengguna = $bar->kodeorg;
						//$lokasi[$key]=$lokasi[$key];
					}
				}
				if (substr($lokasi[$key], 0, 2) == 'PB') {
					#khusus project
					$str = "select kodeorg from " . $dbname . ".pabrikasi_5masterht where kodepabrikasi='" . $lokasi[$key] . "'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_OBJ);
					while ($bar = $res->fetch()) {
						$pengguna = $bar->kodeorg;
						//$lokasi[$key]=$lokasi[$key];
					}
				}
			}

			#ambil piutang ke pengguna
			$str = "select akunpiutang,jenis from " . $dbname . ".keu_5caco where kodeorg='" . $pengguna . "'";
			$intraco = '';
			$interco = '';
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				if ($bar->jenis == 'intra')
					$intraco = $bar->akunpiutang;
				else
					$interco = $bar->akunpiutang;
			}

			$supplierrxx = '';
			$str = "select * from " . $dbname . ".kebun_5namakud where status='1' and afdeling='" . substr($lokasi[$key], 0, 6) . "'";
			$res = fetchData($str);
			foreach ($res as $row => $lsDt) {
				$supplierrxx = $lsDt['kodesupplier'];
			}
			if ($supplierrxx == '') {
				$strx = "select * from " . $dbname . ".kebun_5namakud where status='1' and kodesupplier='" . substr($lokasi[$key], 0, 6) . "'";
				$resx = fetchData($strx);
				foreach ($resx as $rowx => $lsDtx) {
					$supplierrxx = $lsDtx['kodesupplier'];
					$pengguna = $lsDtx['kodeunit'];
				}
			}
			/*
					if ($intraco=='' || $interco==''){
						if($supplierrxx==''){
							throw new PDOException("EN : KUD code ".$lokasi[$key]." not register, please register KUD in Kebun->Setup->Nama Kud \n IND : KUD kode ".$lokasi[$key]."  belum didaftarkan, silahkan daftarkan KUD di Kebun->Setup->Nama Kud");	
						}else{
							throw new PDOException("EN : Account intraco or interco not available for ".$pengguna.". Please setting on menu Finance->setup->COA for Intra/Interco \n IN : Akun intraco datau interco belum didaftarkan ".$pengguna.". Silahkan hubungi Accounting untuk mendaftarkan akun interco dan intraco.");
						}
					}
					*/


			if ($intraco == '' || $interco == '') {
				if ($supplierrxx == '') {
					throw new PDOException("Jika Unit " . $lokasi[$key] . " adalah KUD maka daftarkan dahulu di KUD di Kebun->Setup->Nama Kud \n Jika unit bukan KUD maka daftarkan COA intra/interco Keuangan->setup->akun inter/intraco");
				}
			}



			#++++++++++++++++++++++++++++++++++++++
			$akunpekerjaan = $akun[$key];
			#++++++++++++++++++++++++++++++++++++++++
			$ptpengguna = '';
			$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $pengguna . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$ptpengguna = $bar->induk;
			}

			$ptGudang = '';
			$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $param['kodeorg'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$ptGudang = $bar->induk;
			}


			if ($supplierrxx != '') {
				$ptpengguna = $ptGudang;
				$pengguna = $param['kodeorg'];
				#echo $lokasi[$key]."xxx".$ptpengguna."xxx".$ptGudang."xxx".$supplierrxx."xxx".$pengguna;
				#exit('error');
			}
			#jika pt tidak sama maka pakai akun interco
			$akunpengguna = '';
			if ($ptGudang != $ptpengguna) {
				#ambil akun interco
				$intern = false;
				$str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . $param['kodeorg'] . "' and jenis='inter'";
				$akunpengguna = '';
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $res->fetch()) {
					$akunpengguna = $bar->akunhutang;
				}
				$akunsendiri = $interco;
				if ($akunpengguna == '')
					throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
			} else if ($pengguna != $param['kodeorg']) { #jika satu pt beda kebun

				#ambil akun intraco
				$intern = false;
				$str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . $param['kodeorg'] . "' and jenis='intra'";
				$akunpengguna = '';
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $res->fetch()) {
					$akunpengguna = $bar->akunhutang;
				}
				$akunsendiri = $intraco;
				if ($akunpengguna == '')
					throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
			} else {
				$intern = true;
			}

			if ($intern) {
				#proses data
				$kodeJurnal = $group;
				#======================== Nomor Jurnal =============================
				# Get Journal Counter
				$queryJ = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
				);
				$tmpKonter = fetchData($queryJ);
				$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);


				#= cek konter dari jurnal
				$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$konterjurnal = ($bar['konter'] + 1);
				}

				if ($konterjurnal > $konter) {
					$konter = $konterjurnal;
				}


				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
				#======================== /Nomor Jurnal ============================
				# Prep Header
				$dataRes['header'] = array(
					'nojurnal' => $nojurnal,
					'kodejurnal' => $kodeJurnal,
					'tanggal' => $tanggal,
					'tanggalentry' => date('Ymd'),
					'posting' => 1,
					'totaldebet' => $biaya[$key],
					'totalkredit' => -1 * $biaya[$key],
					'amountkoreksi' => '0',
					'noreferensi' => 'ALK_TRK_GYMH',
					'autojurnal' => '1',
					'matauang' => 'IDR',
					'kurs' => '1',
					'revisi' => '0'
				);
				# Data Detail
				$noUrut = 1;
				# Debet
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggal,
					'nourut' => $noUrut,
					'noakun' => $akunpekerjaan,
					'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
					'jumlah' => $biaya[$key],
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $param['kodeorg'],
					'kodekegiatan' => $kegiatan[$key],
					'kodeasset' => $kodeasset[$key],
					'kodebarang' => '',
					'nik' => $karyawanid[$key],
					'kodecustomer' => '',
					'kodesupplier' => $supplierrxx,
					'noreferensi' => 'ALK_TRK_GYMH',
					'noaruskas' => '',
					'kodevhc' => $param['kodevhc'],
					'nodok' => '',
					'kodeblok' => $lokasi[$key],
					'revisi' => '0',
					'kodesegment' => $segment[$key]
				);
				$noUrut++;

				# Kredit
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggal,
					'nourut' => $noUrut,
					'noakun' => $akunalok,
					'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
					'jumlah' => -1 * $biaya[$key],
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $param['kodeorg'],
					'kodekegiatan' => $kegiatan[$key],
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => $karyawanid[$key],
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => 'ALK_TRK_GYMH',
					'noaruskas' => '',
					'kodevhc' => $param['kodevhc'],
					'nodok' => '',
					'kodeblok' => $lokasi[$key],
					'revisi' => '0',
					'kodesegment' => $segment[$key]
				);
				$noUrut++;
				$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
				$owlPDO->exec($insHead);

				#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
				foreach ($dataRes['detail'] as $row) {
					$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
					$owlPDO->exec($insDet);
				}
				# Header and Detail inserted
				#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
				$updJurnal = updateQuery(
					$dbname,
					'keu_5kelompokjurnal',
					array('nokounter' => $konter),
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
				);
				$owlPDO->exec($updJurnal);
			} else {
				# Data Detail
				$noUrut = 1;
				#proses data
				$kodeJurnal = $group;
				#======================== Nomor Jurnal =============================
				# Get Journal Counter
				$queryJ = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
				);
				$tmpKonter = fetchData($queryJ);
				$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

				#= cek konter dari jurnal
				$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$konterjurnal = ($bar['konter'] + 1);
				}

				if ($konterjurnal > $konter) {
					$konter = $konterjurnal;
				}

				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . trim($param['kodeorg']) . "/" . $kodeJurnal . "/" . $konter;
				$str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periode'] . "' and tutupbuku = '1' 
							and kodeorg='" . $pengguna . "'";
				$rstr = fetchData($str);
				$arrUnit = array();
				if (count($rstr) == '0') { //klo blm tutup buku
					#======================== /Nomor Jurnal ============================
					# Prep Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => $tanggal,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $biaya[$key],
						'totalkredit' => -1 * $biaya[$key],
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_TRK_GYMH',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);
					# Debet
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunsendiri,
						'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
						'jumlah' => $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_TRK_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;

					# Kredit
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunalok,
						'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
						'jumlah' => -1 * $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_TRK_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);

					$noUrut++;
					$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($insHead);

					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
					foreach ($dataRes['detail'] as $row) {
						$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
						$owlPDO->exec($insDet);
					}
					# Header and Detail inserted
					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
					$updJurnal = updateQuery(
						$dbname,
						'keu_5kelompokjurnal',
						array('nokounter' => $konter),
						"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
					);
					$owlPDO->exec($updJurnal);

					#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
					#sisi Pengguna
					$kodeJurnal = $group;
					#ambil periodeaktif pengguna
					$tgmulaid = $tanggal;


					#======================== Nomor Jurnal =============================
					# Get Journal Counter
					$queryJ = selectQuery(
						$dbname,
						'keu_5kelompokjurnal',
						'nokounter',
						"kodeorg='" . $ptpengguna . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($pengguna) . "' and periode='" . $param['periode'] . "'  "
					);
					$tmpKonter = fetchData($queryJ);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

					#= cek konter dari jurnal
					$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($pengguna) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$konterjurnal = ($bar['konter'] + 1);
					}

					if ($konterjurnal > $konter) {
						$konter = $konterjurnal;
					}



					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tgmulaid) . "/" . trim($pengguna) . "/" . $kodeJurnal . "/" . $konter;
					#======================== /Nomor Jurnal ============================
					# Prep Header
					unset($dataRes['header']); //ganti header   
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => $tgmulaid,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $biaya[$key],
						'totalkredit' => -1 * $biaya[$key],
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_TRK_GYMH',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);

					# Debet 1
					$noUrut = 1;
					unset($dataRes['detail']); //ganti header 
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tgmulaid,
						'nourut' => $noUrut,
						'noakun' => $akunpekerjaan,
						'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
						'jumlah' => $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $pengguna,
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => $kodeasset[$key],
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_TRK_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => $lokasi[$key],
						'revisi' => '0',
						'kodesegment' => $segment[$key]
					);
					$noUrut++;

					# Kredit 1
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tgmulaid,
						'nourut' => $noUrut,
						'noakun' => $akunpengguna,
						'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
						'jumlah' => -1 * $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $pengguna,
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_TRK_GYMH',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => $lokasi[$key],
						'revisi' => '0',
						'kodesegment' => $segment[$key]
					);
					$noUrut++;

					$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($insHead);

					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
					foreach ($dataRes['detail'] as $row) {
						$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
						$owlPDO->exec($insDet);
					}
					# Header and Detail inserted
					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
					$updJurnal = updateQuery(
						$dbname,
						'keu_5kelompokjurnal',
						array('nokounter' => $konter),
						"kodeorg='" . $ptpengguna .
							"' and kodekelompok='" . $kodeJurnal . "'   and kodeunit='" . trim($pengguna) . "' and periode='" . $param['periode'] . "'  "
					);
					$owlPDO->exec($updJurnal);

					//tutup 
				} else {
					$arrUnit[$pengguna] = $pengguna;
				}
			}
		}

		if (count($arrUnit) != 0) {
			$tttttt = "Ada Unit Tidak Menerima Biaya Karna Sudah Tutup Buku : ";
			foreach ($arrUnit as $unitddd) {
				$tttttt .= $unitddd . "\n";
			}
			throw new PDOException($tttttt);
		}
	}
}

function prosesGajiTraksiUmumv2()
{
	global $conn;
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;
	#VHCG0	Gaji Kendaraan/A.Berat		
	#VHCG1	Biaya Lebur Kendaraan/A.Berat	
	#VHCG2	Biaya Tunjangan Lain Kend./A.Berat	
	#VHCG3	THR Kend./A.Berat	
	#VHCG4	Bonus Kend. A.Berat	
	#VHCG5	Pengobatan Kend./A.Berat
	#VHCG6

	/*
	karyawan BPJM
	Jika kendaraan milik SNPE 
	Jurnal BPJM
	D: interco
	K: hutang gaji =>akunkredit
	
	jurnal SNPE
	D:jurnal gaji =>akundebet
	K:interco
	*/

	/*
	D: jurnal gaji => akun debet
	K: hutang gaji => akun kredit
	*/


	#output pada jurnal kolom noreferensi ALK_TRK_GYMH  
	if ($param['komponen'] == 1 or $param['komponen'] == 14)
		$group = 'VHCU0';
	else if ($param['komponen'] == 16 or $param['komponen'] == 32 or $param['komponen'] == 33)
		$group = 'VHCU1';
	else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
		$group = 'VHCU6';
	else if ($param['komponen'] == 71)
		$group = 'VHCU7';
	else if ($param['komponen'] == 28)
		$group = 'VHCU3';
	else if ($param['komponen'] == 26)
		$group = 'VHCU4';
	else if ($param['komponen'] == 21)
		$group = 'VHCU5';
	else
		$group = 'VHCU2';  //defaultnya tunjangan

	$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $group . "' limit 1";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($res);
	if ($numrows < 1) {
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen']);
	} else {
		$bar = $res->fetch();
		$akundebet = '';
		$akunkredit = '';
		$akundebet = $bar->noakundebet;
		$akunkredit = $bar->noakunkredit;
	}


	#proses data
	$kodeJurnal = $group;
	#======================== Nomor Jurnal =============================
	# Get Journal Counter
	$queryJ = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$tmpKonter = fetchData($queryJ);
	$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

	# Transform No Jurnal dari No Transaksi
	$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
	#======================== /Nomor Jurnal ============================
	# Prep Header
	$kodekend = $param['mesin'];

	if ($kodekend == '') {
		$kodekend = 'NOVHC';
	}

	if ($kodekend != 'NOVHC') {

		#= cek pemilik kendaraan ditraksi mana
		$str = "select kodetraksi from " . $dbname . ".vhc_5master_hist where periode='" . $param['periode'] . "' and kodevhc='" . $kodekend . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$unitpemilik = substr($bar['kodetraksi'], 0, 4);
		}
		if (count($res) == 0) {
			throw new PDOException("Jalankan proses tutup buku kendaraan, Traksi - Proses - Tutup Buku Kendaraan.");
		}


		if ($unitpemilik == $param['kodeorg']) {

			#= jika pemilik sama dengan pemilik traksinya
			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodeJurnal,
				'tanggal' => $tanggal,
				'tanggalentry' => date('Ymd'),
				'posting' => 1,
				'totaldebet' => $param['jumlah'],
				'totalkredit' => -1 * $param['jumlah'],
				'amountkoreksi' => '0',
				'noreferensi' => 'ALK_TRK_GYMH',
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);


			# Data Detail
			$noUrut = 1;

			# Debet
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akundebet,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;

			# Kredit
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akunkredit,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => -1 * $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;




			$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			$owlPDO->exec($insHead);

			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
			$detailErr = '';
			foreach ($dataRes['detail'] as $row) {
				$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
				$owlPDO->exec($insDet);
			}
			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
			$updJurnal = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $konter),
				"kodeorg='" . $orgpt .
					"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
			);
			$owlPDO->exec($updJurnal);
		} else {

			#=================================================================================================================================
			#=================================================================================================================================
			#=================================================================================================================================


			#= bentuk data kodept	
			$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)=4";
			$res = $owlPDO->query($str);
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$kodept[$bar['kodeorganisasi']] = $bar['induk'];
			}



			/*
			$akundebet=$bar->noakundebet;
			$akunkredit=$bar->noakunkredit;
			*/

			/*
			karyawan BPJM
			Jika kendaraan milik SNPE 
			Jurnal BPJM
			D: interco
			K: hutang gaji =>akunkredit
			
			jurnal SNPE
			D:jurnal gaji =>akundebet
			K:interco
			*/


			#= jurnal pengirim

			#proses data
			$kodeJurnal = $group;
			#======================== Nomor Jurnal =============================
			# Get Journal Counter
			$queryJ = selectQuery(
				$dbname,
				'keu_5kelompokjurnal',
				'nokounter',
				"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
			);
			$tmpKonter = fetchData($queryJ);
			$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

			# Transform No Jurnal dari No Transaksi
			$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
			#======================== /Nomor Jurnal ============================

			#= jika dia CACO
			if ($kodept[$unitpemilik] == $kodept[$param['kodeorg']]) {
				$jenisinduk = 'intra';
			} else {
				$jenisinduk = 'inter';
			}

			$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $unitpemilik . "'";
			$query = selectQuery($dbname, 'keu_5caco', 'akunpiutang', $whereNocaco);
			$noKon = fetchData($query);
			$noakuncaco = $noKon[0]['akunpiutang'];

			if ($noakuncaco == '') {
				exit("Warningsistem:No. Akun Interco/Intraco masih kosong untuk " . $param['kodeorg'] . " ke " . $unitpemilik . " atau sebaliknya, Hubungi Pihak Accounting / IT");
			}


			#= ht
			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodeJurnal,
				'tanggal' => $tanggal,
				'tanggalentry' => date('Ymd'),
				'posting' => 1,
				'totaldebet' => $param['jumlah'],
				'totalkredit' => -1 * $param['jumlah'],
				'amountkoreksi' => '0',
				'noreferensi' => 'ALK_TRK_GYMH',
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);

			# Data Detail
			$noUrut = 1;

			# Debet
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $noakuncaco,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;

			# Kredit
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akunkredit,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => -1 * $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;

			$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			$owlPDO->exec($insHead);

			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
			$detailErr = '';
			foreach ($dataRes['detail'] as $row) {
				$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
				$owlPDO->exec($insDet);
			}
			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
			$updJurnal = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $konter),
				"kodeorg='" . $orgpt .
					"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
			);
			$owlPDO->exec($updJurnal);



			#===============
			#= sisi penerima
			#===============

			$dataRes = array();

			$kodeJurnal = 'M';
			$queryJ = selectQuery(
				$dbname,
				'keu_5kelompokjurnal',
				'nokounter',
				"kodeorg='" . $kodept[$unitpemilik] . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $unitpemilik . "' and periode='" . $param['periode'] . "' "
			);
			$tmpKonter = fetchData($queryJ);
			$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

			# Transform No Jurnal dari No Transaksi
			$nojurnal = str_replace("-", "", $tanggal) . "/" . $unitpemilik . "/" . $kodeJurnal . "/" . $konter;


			$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $param['kodeorg'] . "'";
			$query = selectQuery($dbname, 'keu_5caco', 'akunhutang', $whereNocaco);
			$noKon = fetchData($query);
			$noakuncaco = $noKon[0]['akunhutang'];

			if ($noakuncaco == '') {
				exit("Warningsistem:No Akun Interco/Intraco masih kosong untuk " . $param['kodeorg'] . " ke " . $unitpemilik . " atau sebaliknya, Hubungi Pihak Accounting / IT ");
			}


			#= ht
			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodeJurnal,
				'tanggal' => $tanggal,
				'tanggalentry' => date('Ymd'),
				'posting' => 1,
				'totaldebet' => $param['jumlah'],
				'totalkredit' => -1 * $param['jumlah'],
				'amountkoreksi' => '0',
				'noreferensi' => 'ALK_TRK_GYMH',
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);

			# Data Detail
			$noUrut = 1;

			# Debet
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akundebet,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $unitpemilik,
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;

			# Kredit
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $noakuncaco,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Kendaraan",
				'jumlah' => -1 * $param['jumlah'],
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $unitpemilik,
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_TRK_GYMH',
				'noaruskas' => '',
				'kodevhc' => $kodekend,
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
			// echo"<pre>";
			// print_r($dataRes);
			// exit("Error:A");

			$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			$owlPDO->exec($insHead);

			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
			$detailErr = '';
			foreach ($dataRes['detail'] as $row) {
				$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
				$owlPDO->exec($insDet);
			}
			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
			$updJurnal = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $konter),
				"kodeorg='" . $kodept[$unitpemilik] .
					"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $unitpemilik . "' and periode='" . $param['periode'] . "' "
			);
			$owlPDO->exec($updJurnal);
		}



		$arrUnit = array();
		$param['kodevhc'] = $kodekend;
		#1 ambil periode akuntansi
		$str = "select tanggalmulai,tanggalsampai from " . $dbname . ".setup_periodeakuntansi where 
		kodeorg ='" . $param['kodeorg'] . "' and tutupbuku=0 and periode='" . $param['periode'] . "'";
		$tgmulai = '';
		$tgsampai = '';
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows = owlBaris($res);
		if ($numrows < 1) {
			throw new PDOException("Tidak ada periode akuntansi untuk induk " . $param['kodeorg']);
		}
		while ($bar = $res->fetch()) {
			$tgsampai   = $bar->tanggalsampai;
			$tgmulai    = $bar->tanggalmulai;
		}
		if ($tgmulai == '' || $tgsampai == '')
			throw new PDOException("Periode akuntasi tidak terdaftar");

		#2 output pada jurnal kolom noreferensi ALK_KERJA_AB  

		$group = 'VHC0';
		#ambil akun alokasi
		$str = "select noakundebet from " . $dbname . ".keu_5parameterjurnal
		where jurnalid='" . $group . "' limit 1";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$numrows = owlBaris($res);
		if ($numrows < 1)
			throw new PDOException("No.Akun pada parameterjurnal belum ada untuk VHC0");
		else {
			$bar = $res->fetch();
			$akunalok = $bar->noakundebet;
		}
		#3 ambil semua lokasi kegiatan
		$str = "select sum(a.jumlah) as jlh,a.alokasibiaya,b.noakun,b.setupkegiatan from " . $dbname . ".vhc_rundt_detail a
		left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
		left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi     
		where c.kodevhc='" . $param['kodevhc'] . "'
		and c.tanggal>='" . $tgmulai . "' and c.tanggal <='" . $tgsampai . "' and alokasibiaya!='' 
		and jenispekerjaan!=''    
		group by jenispekerjaan,noakun,alokasibiaya,kodesegment order by tanggal asc";

		//exit("Error:".$str);

		$lokasi = array();
		$biaya = array();
		$karyawanid = array();
		$jam  = array();
		$akun = array();
		$kodeasset = array();
		$segment = array();
		$ttl = 0;
		$no = 0;
		$tempjam = 0;
		$counttemp = 0;
		$tempjumlah = $param['jumlah'];
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$tempjam += $bar->jlh;
			$counttemp++;
		}
		// exit("Error:".$param['jumlahpembulatan']);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {

			#= insert pembulatan di baris 1
			$no++;
			// if($no>1){
			// $param['jumlahpembulatan']=0;
			// }


			#kusus jika project
			if (substr($bar->alokasibiaya, 0, 2) == 'AK' or substr($bar->alokasibiaya, 0, 2) == 'PB') {
				if (substr($bar->alokasibiaya, 0, 2) == 'AK') {
					#ambil akun aktiva dalam konstruksi
					// alokasi ke AK-BG98000008, kalo 3,3 dapetnya BG9... sementara setupnya cuman ada BG. jadi ganti 3,2
					$tipeasset = substr($bar->alokasibiaya, 3, 2);
					$tipeasset =  str_replace("0", "", $tipeasset);
					$str1 = "select akunak from " . $dbname . ".sdm_5tipeasset where kodetipe='" . $tipeasset . "'";
					$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
					$res1->setFetchMode(PDO::FETCH_OBJ);
					$numrows1 = owlBaris($res1);
					if ($numrows1 < 1) {
						throw new PDOException("Akun aktiva dalam konstruksi untuk " . $tipeasset . " beum disetting dari keuangan->setup->tipeasset");
					} else {
						while ($bar1 = $res1->fetch()) {
							if ($bar1->akunak == '')
								throw new PDOException("Akun aktiva dalam konstruksi untuk " . $tipeasset . " belum disetting dari keuangan->setup->tipeasset");
							else
								$akun[] = $bar1->akunak;
						}
					}
					$kodeasset[] = $bar->alokasibiaya;
				}
				#jika pabrikasi
				if (substr($bar->alokasibiaya, 0, 2) == 'PB') {
					$sData = "select noakundebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='PBR3'";
					$rData = fetchData($sData);
					if (count($rData) == 0) {
						throw new PDOException("Akun untuk alokasi traksi pabrikasi belum di setting pada keuangan->setup->parameter jurnal");
					} else {
						$akun[] = $rData[0]['noakundebet'];
						$kodeasset[] = '';
					}
				}
				$lokasi[] = $bar->alokasibiaya;
				$jam[] = $bar->jlh;
				if ($no == $counttemp) {
					$biaya[] = $tempjumlah;
				} else {
					$biaya[] = floor(($bar->jlh / $tempjam) * $param['jumlah']);
				}
				$tempjumlah = $tempjumlah - floor(($bar->jlh / $tempjam) * $param['jumlah']);
				$karyawanid[] = $param['karyawanid'];
				$kegiatan[] = '';
				$segment[] = $bar->kodesegment;
			} else {
				$lokasi[] = $bar->alokasibiaya;
				$akun[]  = $bar->noakun;
				$jam[] = $bar->jlh;
				if ($no == $counttemp) {
					$biaya[] = $tempjumlah;
				} else {
					$biaya[] = floor(($bar->jlh / $tempjam) * $param['jumlah']);
				}
				$tempjumlah = $tempjumlah - floor(($bar->jlh / $tempjam) * $param['jumlah']);
				// $biaya[] =floor(($bar->jlh/$tempjam)*$param['jumlah']); 
				$karyawanid[] = $param['karyawanid'];
				// $kegiatan[]=$bar->noakun."01";
				$kegiatan[] = $bar->setupkegiatan;
				$kodeasset[] = '';
				$segment[] = $bar->kodesegment;


				// $templokasi=$bar->alokasibiaya;
				// $tempakun=$bar->noakun;
				// $tempkegiatan=$bar->setupkegiatan;
				// $tempbiaya=0;
				// $tempkodeasset=0;
				// $tempsegment=$bar->kodesegment;
			}
			$totalalokasi += floor($bar->jlh * $param['jumlah']);
		}


		$strh = "select distinct noakundebet,sampaidebet  from " . $dbname . ".keu_5parameterjurnal where  jurnalid='LPVHC'";
		$resh = $owlPDO->query($strh) or die(print " Gagal: " . PDOException::getMessage());
		$resh->setFetchMode(PDO::FETCH_OBJ);
		while ($barh = $resh->fetch()) {
			$akunkdari = $barh->noakundebet;
			$akunksampai = $barh->sampaidebet;
		}

		// $str = "select sum(jumlah) as jumlah, kodevhc from ".$dbname.".keu_jurnaldt_vw where  noakun not in (4110299,4110199)  and tanggal>='".$tgmulai."' and tanggal<='".$tgsampai."' and (noakun between '".$akunkdari."' and '".$akunksampai."') and (noreferensi not like '%ALK_KERJA_AB%' or noreferensi is NULL) and kodevhc='".$param['kodevhc']."' ";

		// $res=fetchdata($str);
		// foreach($res as $bar){
		// 	$param['jumlahbiayakendaraan']=$bar['jumlah'];
		// }

		// if($param['kodevhc']=='SD4ETR0009'){
		// echo $param['jumlahbiayakendaraan']._.$totalalokasi;
		// exit("Error:a".$str);
		// }

		//if($param['jumlahbiayakendaraan']-$totalalokasi!='0'){
		// $lokasi[]=$templokasi;
		// $akun[]  =$tempakun;
		// $jam[] =$tempjam;
		// // 
		// // exit("Error:".$param['jumlahbiayakendaraan']._.$totalalokasi);
		// $kegiatan[]=$tempkegiatan;
		// $kodeasset[]=$tempkodeasset;
		// $segment[]=$tempsegment;
		//}
		// echo "<pre>";
		// print_r($karyawanid);
		// echo "</pre>";
		//throw new PDOException("Error");


		foreach ($biaya as $key => $nilai) {
			#periksa unit 
			$dataRes['header'] = array();
			$dataRes['detail'] = array();
			$intern = true;

			$pengguna = substr($lokasi[$key], 0, 4);
			if (substr($lokasi[$key], 0, 2) == 'AK' or substr($lokasi[$key], 0, 2) == 'PB') {
				if (substr($lokasi[$key], 0, 2) == 'AK') {
					#khusus project
					$str = "select kodeorg from " . $dbname . ".project where kode='" . $lokasi[$key] . "'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_OBJ);
					while ($bar = $res->fetch()) {
						$pengguna = $bar->kodeorg;
						//$lokasi[$key]=$lokasi[$key];
					}
				}
				if (substr($lokasi[$key], 0, 2) == 'PB') {
					#khusus project
					$str = "select kodeorg from " . $dbname . ".pabrikasi_5masterht where kodepabrikasi='" . $lokasi[$key] . "'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_OBJ);
					while ($bar = $res->fetch()) {
						$pengguna = $bar->kodeorg;
						//$lokasi[$key]=$lokasi[$key];
					}
				}
			}

			#ambil piutang ke pengguna
			$str = "select akunpiutang,jenis from " . $dbname . ".keu_5caco where kodeorg='" . $pengguna . "'";
			$intraco = '';
			$interco = '';
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				if ($bar->jenis == 'intra')
					$intraco = $bar->akunpiutang;
				else
					$interco = $bar->akunpiutang;
			}

			$supplierrxx = '';
			$str = "select * from " . $dbname . ".kebun_5namakud where status='1' and afdeling='" . substr($lokasi[$key], 0, 6) . "'";
			$res = fetchData($str);
			foreach ($res as $row => $lsDt) {
				$supplierrxx = $lsDt['kodesupplier'];
			}
			if ($supplierrxx == '') {
				$strx = "select * from " . $dbname . ".kebun_5namakud where status='1' and kodesupplier='" . substr($lokasi[$key], 0, 6) . "'";
				$resx = fetchData($strx);
				foreach ($resx as $rowx => $lsDtx) {
					$supplierrxx = $lsDtx['kodesupplier'];
					$pengguna = $lsDtx['kodeunit'];
				}
			}
			/*
				if ($intraco=='' || $interco==''){
					if($supplierrxx==''){
						throw new PDOException("EN : KUD code ".$lokasi[$key]." not register, please register KUD in Kebun->Setup->Nama Kud \n IND : KUD kode ".$lokasi[$key]."  belum didaftarkan, silahkan daftarkan KUD di Kebun->Setup->Nama Kud");	
					}else{
						throw new PDOException("EN : Account intraco or interco not available for ".$pengguna.". Please setting on menu Finance->setup->COA for Intra/Interco \n IN : Akun intraco datau interco belum didaftarkan ".$pengguna.". Silahkan hubungi Accounting untuk mendaftarkan akun interco dan intraco.");
					}
				}
				*/


			if ($intraco == '' || $interco == '') {
				if ($supplierrxx == '') {
					throw new PDOException("Jika Unit " . $lokasi[$key] . " adalah KUD maka daftarkan dahulu di KUD di Kebun->Setup->Nama Kud \n Jika unit bukan KUD maka daftarkan COA intra/interco Keuangan->setup->akun inter/intraco");
				}
			}



			#++++++++++++++++++++++++++++++++++++++
			$akunpekerjaan = $akun[$key];
			#++++++++++++++++++++++++++++++++++++++++
			$ptpengguna = '';
			$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $pengguna . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$ptpengguna = $bar->induk;
			}

			$ptGudang = '';
			$str = "select induk from " . $dbname . ".organisasi where kodeorganisasi='" . $param['kodeorg'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$ptGudang = $bar->induk;
			}


			if ($supplierrxx != '') {
				$ptpengguna = $ptGudang;
				$pengguna = $param['kodeorg'];
				#echo $lokasi[$key]."xxx".$ptpengguna."xxx".$ptGudang."xxx".$supplierrxx."xxx".$pengguna;
				#exit('error');
			}
			#jika pt tidak sama maka pakai akun interco
			$akunpengguna = '';
			if ($ptGudang != $ptpengguna) {
				#ambil akun interco
				$intern = false;
				$str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . $param['kodeorg'] . "' and jenis='inter'";
				$akunpengguna = '';
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $res->fetch()) {
					$akunpengguna = $bar->akunhutang;
				}
				$akunsendiri = $interco;
				if ($akunpengguna == '')
					throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
			} else if ($pengguna != $param['kodeorg']) { #jika satu pt beda kebun
				#ambil akun intraco
				$intern = false;
				$str = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . $param['kodeorg'] . "' and jenis='intra'";
				$akunpengguna = '';
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $res->fetch()) {
					$akunpengguna = $bar->akunhutang;
				}
				$akunsendiri = $intraco;
				if ($akunpengguna == '')
					throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
			} else {
				$intern = true;
			}

			if ($intern) {
				#proses data
				$kodeJurnal = $group;
				#======================== Nomor Jurnal =============================
				# Get Journal Counter
				$queryJ = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
				);
				$tmpKonter = fetchData($queryJ);
				$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);


				#= cek konter dari jurnal
				$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$konterjurnal = ($bar['konter'] + 1);
				}

				if ($konterjurnal > $konter) {
					$konter = $konterjurnal;
				}


				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
				#======================== /Nomor Jurnal ============================
				# Prep Header
				$dataRes['header'] = array(
					'nojurnal' => $nojurnal,
					'kodejurnal' => $kodeJurnal,
					'tanggal' => $tanggal,
					'tanggalentry' => date('Ymd'),
					'posting' => 1,
					'totaldebet' => $biaya[$key],
					'totalkredit' => -1 * $biaya[$key],
					'amountkoreksi' => '0',
					'noreferensi' => 'ALK_KERJA_AB',
					'autojurnal' => '1',
					'matauang' => 'IDR',
					'kurs' => '1',
					'revisi' => '0'
				);
				# Data Detail
				$noUrut = 1;
				# Debet
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggal,
					'nourut' => $noUrut,
					'noakun' => $akunpekerjaan,
					'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
					'jumlah' => $biaya[$key],
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $param['kodeorg'],
					'kodekegiatan' => $kegiatan[$key],
					'kodeasset' => $kodeasset[$key],
					'kodebarang' => '',
					'nik' => $karyawanid[$key],
					'kodecustomer' => '',
					'kodesupplier' => $supplierrxx,
					'noreferensi' => 'ALK_KERJA_AB',
					'noaruskas' => '',
					'kodevhc' => $param['kodevhc'],
					'nodok' => '',
					'kodeblok' => $lokasi[$key],
					'revisi' => '0',
					'kodesegment' => $segment[$key]
				);
				$noUrut++;

				# Kredit
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggal,
					'nourut' => $noUrut,
					'noakun' => $akunalok,
					'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
					'jumlah' => -1 * $biaya[$key],
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $param['kodeorg'],
					'kodekegiatan' => $kegiatan[$key],
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => $karyawanid[$key],
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => 'ALK_KERJA_AB',
					'noaruskas' => '',
					'kodevhc' => $param['kodevhc'],
					'nodok' => '',
					'kodeblok' => $lokasi[$key],
					'revisi' => '0',
					'kodesegment' => $segment[$key]
				);
				$noUrut++;
				$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
				$owlPDO->exec($insHead);

				#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
				foreach ($dataRes['detail'] as $row) {
					$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
					$owlPDO->exec($insDet);
				}
				# Header and Detail inserted
				#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
				$updJurnal = updateQuery(
					$dbname,
					'keu_5kelompokjurnal',
					array('nokounter' => $konter),
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
				);
				$owlPDO->exec($updJurnal);
			} else {
				# Data Detail
				$noUrut = 1;
				#proses data
				$kodeJurnal = $group;
				#======================== Nomor Jurnal =============================
				# Get Journal Counter
				$queryJ = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
				);
				$tmpKonter = fetchData($queryJ);
				$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

				#= cek konter dari jurnal
				$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$konterjurnal = ($bar['konter'] + 1);
				}

				if ($konterjurnal > $konter) {
					$konter = $konterjurnal;
				}



				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . trim($param['kodeorg']) . "/" . $kodeJurnal . "/" . $konter;
				$str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periode'] . "' and tutupbuku = '1' 
	         	and kodeorg='" . $pengguna . "'";
				$rstr = fetchData($str);
				$arrUnit = array();
				if (count($rstr) == '0') { //klo blm tutup buku
					#======================== /Nomor Jurnal ============================
					# Prep Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => $tanggal,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $biaya[$key],
						'totalkredit' => -1 * $biaya[$key],
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_KERJA_AB',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);
					# Debet
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunsendiri,
						'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
						'jumlah' => $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_KERJA_AB',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;

					# Kredit
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunalok,
						'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
						'jumlah' => -1 * $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_KERJA_AB',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);

					$noUrut++;
					$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($insHead);

					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
					foreach ($dataRes['detail'] as $row) {
						$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
						$owlPDO->exec($insDet);
					}
					# Header and Detail inserted
					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
					$updJurnal = updateQuery(
						$dbname,
						'keu_5kelompokjurnal',
						array('nokounter' => $konter),
						"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($param['kodeorg']) . "' and periode='" . $param['periode'] . "'  "
					);
					$owlPDO->exec($updJurnal);

					#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
					#sisi Pengguna
					$kodeJurnal = $group;
					#ambil periodeaktif pengguna
					$tgmulaid = $tanggal;


					#======================== Nomor Jurnal =============================
					# Get Journal Counter
					$queryJ = selectQuery(
						$dbname,
						'keu_5kelompokjurnal',
						'nokounter',
						"kodeorg='" . $ptpengguna . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($pengguna) . "' and periode='" . $param['periode'] . "'  "
					);
					$tmpKonter = fetchData($queryJ);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

					#= cek konter dari jurnal
					$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($pengguna) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$konterjurnal = ($bar['konter'] + 1);
					}

					if ($konterjurnal > $konter) {
						$konter = $konterjurnal;
					}



					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tgmulaid) . "/" . trim($pengguna) . "/" . $kodeJurnal . "/" . $konter;
					#======================== /Nomor Jurnal ============================
					# Prep Header
					unset($dataRes['header']); //ganti header   
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodeJurnal,
						'tanggal' => $tgmulaid,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $biaya[$key],
						'totalkredit' => -1 * $biaya[$key],
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_KERJA_AB',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);

					# Debet 1
					$noUrut = 1;
					unset($dataRes['detail']); //ganti header 
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tgmulaid,
						'nourut' => $noUrut,
						'noakun' => $akunpekerjaan,
						'keterangan' => $param['periode'] . ':Biaya Kendaraan ' . $param['kodevhc'],
						'jumlah' => $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $pengguna,
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => $kodeasset[$key],
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_KERJA_AB',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => $lokasi[$key],
						'revisi' => '0',
						'kodesegment' => $segment[$key]
					);
					$noUrut++;

					# Kredit 1
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tgmulaid,
						'nourut' => $noUrut,
						'noakun' => $akunpengguna,
						'keterangan' => $param['periode'] . ':Alokasi biaya kend ' . $param['kodevhc'],
						'jumlah' => -1 * $biaya[$key],
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $pengguna,
						'kodekegiatan' => $kegiatan[$key],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $karyawanid[$key],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_KERJA_AB',
						'noaruskas' => '',
						'kodevhc' => $param['kodevhc'],
						'nodok' => '',
						'kodeblok' => $lokasi[$key],
						'revisi' => '0',
						'kodesegment' => $segment[$key]
					);
					$noUrut++;

					$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($insHead);

					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
					foreach ($dataRes['detail'] as $row) {
						$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
						$owlPDO->exec($insDet);
					}
					# Header and Detail inserted
					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
					$updJurnal = updateQuery(
						$dbname,
						'keu_5kelompokjurnal',
						array('nokounter' => $konter),
						"kodeorg='" . $ptpengguna .
							"' and kodekelompok='" . $kodeJurnal . "'   and kodeunit='" . trim($pengguna) . "' and periode='" . $param['periode'] . "'  "
					);
					$owlPDO->exec($updJurnal);

					//tutup 
				} else {
					$arrUnit[$pengguna] = $pengguna;
				}
			}
		}
		if (count($arrUnit) != 0) {
			$tttttt = "Ada Unit Tidak Menerima Biaya Karna Sudah Tutup Buku : ";
			foreach ($arrUnit as $unitddd) {
				$tttttt .= $unitddd . "\n";
			}
			throw new PDOException($tttttt);
		}
	} else { #jika tidak maka jika workshop proses ke workshop, jika tidak maka miaya umum
		if ($param['tipeorganisasi'] == 'WORKSHOP') {
			prosesGajiWs();
		} else if ($param['tipeorganisasi'] == 'TRAKSI') {
			// prosesGajiWs(); # Sementara
			prosesGajiTraksiUmum();
		} else {
			prosesGajiKebun();
		}
	}
}


function prosesGajiAfdeling()
{
	global $conn;
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;

	// KBNL0	Biaya pengawasan BBT
	// KBNL1	Biaya pengawasan TBM
	// KBNL2	Biaya pengawasan TM
	// KBNL3     Pengawasan Panen
	#karyawan afdelin pengawasan
	#output pada jurnal kolom noreferensi ALK_WAS  
	#pastikan jenis pekerjaan untuk karyawan bersangkutan apakah PNN TM TBM atau BBT
	#ambil tanggal periode gaji
	$str = "select tanggalmulai,tanggalsampai from " . $dbname . ".sdm_5periodegaji 
	where periode='" . $param['periode'] . "' and kodeorg='" . $param['kodeorg'] . "'
          and jenisgaji='B'"; #bulanan
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($res);
	if ($numrows < 1) {
		throw new PDOException("Belum ada periode gaji untuk unit " . $param['kodeorg']);
	}
	$bar = $res->fetch();
	$tanggalmulai = $bar->tanggalmulai;
	$tanggalsampai = $bar->tanggalsampai;

	## Untuk Kutip Brondolan
	## GET KEGIATAN YANG TIDAK DIALOKASI GAJI DLL
	$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='SNALOC' and kodeorg='" . $param['kodeorg'] . "'";
	$res = fetchdata($str);
	$wherekegx = "";
	if ($res[0]['nilai'] == '') {
		$wherekegx = "";
	} else {
		$wherekegx = " and b.kodekegiatan not in (" . $res[0]['nilai'] . ")";
	}

	$totalluas = 0;
	$str = "select distinct b.kodekegiatan,a.tipetransaksi,b.kodeorg,b.kodesegment,c.luasareaproduktif from " . $dbname . ".kebun_aktifitas a 
          left join " . $dbname . ".kebun_prestasi_detail b on a.notransaksi=b.notransaksi 
          left join " . $dbname . ".setup_blok c on b.kodeorg=c.kodeorg 
          where (nikmandor='" . $param['karyawanid'] . "' or nikmandor1='" . $param['karyawanid'] . "' or keranimuat='" . $param['karyawanid'] . "' or nikasisten='" . $param['karyawanid'] . "') " . $wherekegx . " and a.kodeorg='" . $param['kodeorg'] . "' and a.tanggal >='" . $tanggalmulai . "' and tanggal <='" . $tanggalsampai . "' having kodeorg is not null";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);

	$rowdt  = fetchData($str);
	foreach ($rowdt as $bar) {
		$totalluas += $bar['luasareaproduktif'];
	}
	$numrows = count($rowdt);
	$numblk = $numrows;
	$porsi = $no = $sisapembulatan = 0;
	// exit("Error:$numblk");
	if ($numblk > 0) {
		$noUrut = 0;
		while ($bar = $res->fetch()) {


			$porsi = floor($bar->luasareaproduktif / $totalluas * $param['jumlah']);
			$tporsi += $porsi;

			$no++;
			if ($no == $numblk) {
				#= jika nomor terakhir maka tambahkan sisa kurang dari pembulatan
				#= algoritma ambil total rupiah dan bandingkan dengan yang sudah dialokasi
				#= kurangnya ditambahkan di blok dan kegiatan terakhir 
				$sisapembulatan = $param['jumlah'] - $tporsi;
			}

			$dataRes['header'] = array();
			$dataRes['detail'] = array();
			//buat header
			if ($bar->tipetransaksi == 'BBT') {
				$group = 'KBNL0';
				if ($param['komponen'] == 71) {
					$group = 'KBNL4';
				} else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80) {
					$group = 'KBNL5';
				}
			} else if ($bar->tipetransaksi == 'TBM' or $bar->tipetransaksi == 'TB') {
				$group = 'KBNL1';
				if ($param['komponen'] == 71) {
					$group = 'KBNL4';
				} else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80) {
					$group = 'KBNL5';
				}
			} else if ($bar->tipetransaksi == 'TM') {
				$group = 'KBNL2';
				if ($param['komponen'] == 71) {
					$group = 'KBNL4';
				} else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80) {
					$group = 'KBNL5';
				}
			} else if ($bar->tipetransaksi == 'PNN') {
				$group = 'KBNL3';
				if ($param['komponen'] == 71) {
					$group = 'KBNL4';
				} else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80) {
					$group = 'KBNL5';
				}
			} else {
				#lempar ke biaya umum
				prosesGajiKebun();
			}

			if ($bar->tipetransaksi != 'BBT' and strlen($bar->kodeorg) < 7) {
				//jika lokasinya bukan blok
				//belum dialokasi
			}

			#jika panen ambil dari parameter
			$str2 = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal  where jurnalid='" . $group . "' limit 1";
			$res2 = $owlPDO->query($str2) or die(print " Gagal: " . PDOException::getMessage());
			$res2->setFetchMode(PDO::FETCH_OBJ);
			$numrows = owlBaris($res2);
			if ($numrows < 1) {
				throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $group);
			} else {
				$akundebet = '';
				$akunkredit = '';
				$bar1 = $res2->fetch();
				$akundebet = $bar1->noakundebet;
				$akunkredit = $bar1->noakunkredit;
				$kdkegiatan = $bar1->noakundebet . "01";
			}

			#jika bukan panen ambil akun kegiatannya 
			if ($group != 'KBNL3') {
				if ($bar->tipetransaksi != 'PNN') {
					$nmakun = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,noakun', "kodekegiatan='" . $bar->kodekegiatan . "'");
					$akundebet = $nmakun[$bar->kodekegiatan];
					$kdkegiatan = $bar->kodekegiatan;
				}
			}

			#proses data
			$kodeJurnal = $group;
			#======================== Nomor Jurnal =============================
			# Get Journal Counter
			$queryJ = selectQuery(
				$dbname,
				'keu_5kelompokjurnal',
				'nokounter',
				"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
			);
			$tmpKonter = fetchData($queryJ);
			$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

			# Transform No Jurnal dari No Transaksi
			$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
			#======================== /Nomor Jurnal ============================


			# Prep Header
			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodeJurnal,
				'tanggal' => $tanggal,
				'tanggalentry' => date('Ymd'),
				'posting' => 1,
				'totaldebet' => ($porsi + $sisapembulatan),
				'totalkredit' => -1 * ($porsi + $sisapembulatan),
				'amountkoreksi' => '0',
				'noreferensi' => 'ALK_WAS',
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);
			# Data Detail
			$noUrut++;
			# Debet
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akundebet,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . ' (ALK)',
				'jumlah' => ($porsi + $sisapembulatan),
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => $kdkegiatan,
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_WAS',
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => '',
				'kodeblok' => $bar->kodeorg,
				'revisi' => '0',
				'kodesegment' => $bar->kodesegment
			);
			$noUrut++;

			# Kredit
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $noUrut,
				'noakun' => $akunkredit,
				'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . ' (ALK)',
				'jumlah' => -1 * ($porsi + $sisapembulatan),
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $param['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_WAS',
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => '',
				'kodeblok' => $bar->kodeorg,
				'revisi' => '0',
				'kodesegment' => $bar->kodesegment
			);
			$noUrut++;

			$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
			$owlPDO->exec($insHead);

			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
			foreach ($dataRes['detail'] as $row) {
				$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
				$owlPDO->exec($insDet);
			}

			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
			$updJurnal = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $konter),
				"kodeorg='" . $orgpt .
					"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
			);
			$owlPDO->exec($updJurnal);
		}
	} else {
		#lempar ke biaya umum
		prosesGajiKebun();
	}
}

function prosesGajiPabrik()
{
	global $conn;
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;

	#karyawan kebun
	#output pada jurnal kolom noreferensi ALK_GAJI  
	if ($param['tipeorganisasi'] == 'MAINTENANCE') {
		if ($param['komponen'] == 33 or $param['komponen'] == 32 or $param['komponen'] == 40 or $param['komponen'] == 58) #premi dan lembur
			$group = 'PKS06';
		else if ($param['komponen'] == 45 or $param['komponen'] == 56 or $param['komponen'] == 57 or $param['komponen'] == 69) #tunjangan transport dan makan
			$group = 'PKS07';
		else if ($param['komponen'] == 21) #klaim pengobatan 
			$group = 'PKS08';
		else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
			$group = 'PKS10';
		else if ($param['komponen'] == 71)
			$group = 'PKS11';
		else
			$group = 'PKS05'; #gaji
	} else {
		if ($param['komponen'] == 33 or $param['komponen'] == 32 or $param['komponen'] == 40 or $param['komponen'] == 58) #premi dan lembur
			$group = 'PKS02';
		else if ($param['komponen'] == 45 or $param['komponen'] == 56 or $param['komponen'] == 57 or $param['komponen'] == 69) #tunjangan transport dan makan
			$group = 'PKS03';
		else if ($param['komponen'] == 21) #klaim pengobatan 
			$group = 'PKS04';
		else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
			$group = 'PKS09';
		else if ($param['komponen'] == 71)
			$group = 'PKS12';
		else
			$group = 'PKS01'; #gaji	
	}

	$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $group . "' limit 1";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($res);
	if ($numrows < 1) {
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen']);
	} else {
		$akundebet = '';
		$akunkredit = '';
		$bar = $res->fetch();
		$akundebet = $bar->noakundebet;
		$akunkredit = $bar->noakunkredit;
	}

	#proses data
	$kodeJurnal = $group;
	#======================== Nomor Jurnal =============================
	# Get Journal Counter
	$queryJ = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$tmpKonter = fetchData($queryJ);
	$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

	# Transform No Jurnal dari No Transaksi
	$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
	#======================== /Nomor Jurnal ============================


	# Prep Header
	$dataRes['header'] = array(
		'nojurnal' => $nojurnal,
		'kodejurnal' => $kodeJurnal,
		'tanggal' => $tanggal,
		'tanggalentry' => date('Ymd'),
		'posting' => 1,
		'totaldebet' => $param['jumlah'],
		'totalkredit' => -1 * $param['jumlah'],
		'amountkoreksi' => '0',
		'noreferensi' => 'ALK_GAJI',
		'autojurnal' => '1',
		'matauang' => 'IDR',
		'kurs' => '1',
		'revisi' => '0'
	);

	# Data Detail
	$noUrut = 1;

	# Debet
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $akundebet,
		'keterangan' => $param['namakomponen'] . ' ' . $param['periode'],
		'jumlah' => $param['jumlah'],
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => '',
		'kodebarang' => '',
		'nik' => $param['karyawanid'],
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => 'ALK_GAJI',
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => $param['subbagian'],
		'revisi' => '0',
		'kodesegment' => $defSegment
	);
	$noUrut++;

	# Kredit
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $akunkredit,
		'keterangan' => $param['namakomponen'] . ' ' . $param['periode'],
		'jumlah' => -1 * $param['jumlah'],
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => '',
		'kodebarang' => '',
		'nik' => $param['karyawanid'],
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => 'ALK_GAJI',
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => $param['subbagian'],
		'revisi' => '0',
		'kodesegment' => $defSegment
	);
	$noUrut++;

	$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
	$owlPDO->exec($insHead);

	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
	foreach ($dataRes['detail'] as $row) {
		$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
		$owlPDO->exec($insDet);
	}

	# Header and Detail inserted
	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
	$updJurnal = updateQuery(
		$dbname,
		'keu_5kelompokjurnal',
		array('nokounter' => $konter),
		"kodeorg='" . $orgpt .
			"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$owlPDO->exec($updJurnal);
}

function prosesbulking()
{
	global $conn;
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;

	#karyawan kebun
	#output pada jurnal kolom noreferensi ALK_GAJI  
	if ($param['tipeorganisasi'] == '') {
		if ($param['komponen'] == 33 or $param['komponen'] == 32 or $param['komponen'] == 40 or $param['komponen'] == 58) #premi dan lembur
			$group = 'BLK06';
		else if ($param['komponen'] == 45 or $param['komponen'] == 56 or $param['komponen'] == 57 or $param['komponen'] == 69) #tunjangan transport dan makan
			$group = 'BLK07';
		else if ($param['komponen'] == 21) #klaim pengobatan 
			$group = 'BLK08';
		else if ($param['komponen'] == 70 or $param['komponen'] == 71 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
			$group = 'BLK10';
		else
			$group = 'BLK05'; #gaji
	} else {
		if ($param['komponen'] == 33 or $param['komponen'] == 32 or $param['komponen'] == 40 or $param['komponen'] == 58) #premi dan lembur
			$group = 'BLK02';
		else if ($param['komponen'] == 45 or $param['komponen'] == 56 or $param['komponen'] == 57 or $param['komponen'] == 69) #tunjangan transport dan makan
			$group = 'BLK03';
		else if ($param['komponen'] == 21) #klaim pengobatan 
			$group = 'BLK04';
		else if ($param['komponen'] == 70 or $param['komponen'] == 71 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
			$group = 'BLK09';
		else
			$group = 'BLK01'; #gaji	
	}

	$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $group . "' limit 1";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($res);
	if ($numrows < 1) {
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen']);
	} else {
		$akundebet = '';
		$akunkredit = '';
		$bar = $res->fetch();
		$akundebet = $bar->noakundebet;
		$akunkredit = $bar->noakunkredit;
	}

	#proses data
	$kodeJurnal = $group;
	#======================== Nomor Jurnal =============================
	# Get Journal Counter
	$queryJ = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$tmpKonter = fetchData($queryJ);
	$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

	# Transform No Jurnal dari No Transaksi
	$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
	#======================== /Nomor Jurnal ============================


	# Prep Header
	$dataRes['header'] = array(
		'nojurnal' => $nojurnal,
		'kodejurnal' => $kodeJurnal,
		'tanggal' => $tanggal,
		'tanggalentry' => date('Ymd'),
		'posting' => 1,
		'totaldebet' => $param['jumlah'],
		'totalkredit' => -1 * $param['jumlah'],
		'amountkoreksi' => '0',
		'noreferensi' => 'ALK_GAJI',
		'autojurnal' => '1',
		'matauang' => 'IDR',
		'kurs' => '1',
		'revisi' => '0'
	);

	# Data Detail
	$noUrut = 1;

	# Debet
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $akundebet,
		'keterangan' => $param['namakomponen'] . ' ' . $param['periode'],
		'jumlah' => $param['jumlah'],
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => '',
		'kodebarang' => '',
		'nik' => $param['karyawanid'],
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => 'ALK_GAJI',
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => $param['subbagian'],
		'revisi' => '0',
		'kodesegment' => $defSegment
	);
	$noUrut++;

	# Kredit
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $akunkredit,
		'keterangan' => $param['namakomponen'] . ' ' . $param['periode'],
		'jumlah' => -1 * $param['jumlah'],
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => '',
		'kodebarang' => '',
		'nik' => $param['karyawanid'],
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => 'ALK_GAJI',
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => $param['subbagian'],
		'revisi' => '0',
		'kodesegment' => $defSegment
	);
	$noUrut++;

	$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
	$owlPDO->exec($insHead);

	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
	foreach ($dataRes['detail'] as $row) {
		$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
		$owlPDO->exec($insDet);
	}
	# Header and Detail inserted
	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
	$updJurnal = updateQuery(
		$dbname,
		'keu_5kelompokjurnal',
		array('nokounter' => $konter),
		"kodeorg='" . $orgpt .
			"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$owlPDO->exec($updJurnal);
}

function prosesGajiKebun()
{
	global $conn;
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;


	#karyawan kebun
	#output pada jurnal kolom noreferensi ALK_GAJI  
	if ($param['komponen'] == 1 or $param['komponen'] == 14) #gapok dan rapel gaji
		$group = 'KBNB0';
	elseif ($param['komponen'] == 16 or $param['komponen'] == 32 or $param['komponen'] == 33) #Premi Pengawasan,Premi,Lembur
		$group = 'KBNB1';
	elseif ($param['komponen'] == 28) #THR
		$group = 'KBNB3';
	elseif ($param['komponen'] == 26) #Bonus
		$group = 'KBNB4';
	elseif ($param['komponen'] == 21) #Klaim Pengobatan
		$group = 'KBNB5';
	else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
		$group = 'KBNB6';
	else if ($param['komponen'] == 71)
		$group = 'KBNB7';
	else
		$group = 'KBNB2';  //defaultnya tunjangan

	$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $group . "' limit 1";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($res);
	if ($numrows < 1) {
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen']);
	} else {
		$akundebet = '';
		$akunkredit = '';
		$bar = $res->fetch();
		$akundebet = $bar->noakundebet;
		$akunkredit = $bar->noakunkredit;
	}

	$ttldata = 0;
	$data = array();
	$str = "select * from " . $dbname . ".sdm_absensidt where tanggal like '" . $param['periode'] . "%' and karyawanid='" . $param['karyawanid'] . "'";
	$res = fetchData($str);
	foreach ($res as $bar) {
		#kolom noakun isinya bisa kode kegiatan!
		if ($bar['noakun'] == '') {
			$bar['noakun'] = $akundebet;
		}
		if ($group == 'KBNB0') {
			if (($bar['umr']) > 0) {
				$data[$bar['noakun']][$bar['alokasi']] += $bar['umr'];
				$ttldata += $bar['umr'];
			}
		}
		if ($group == 'KBNB1') {
			$ttlpre = $bar['premi'] + $bar['insentif'] + $bar['insentiflibur'];
			if ($ttlpre > 0) {
				$data[$bar['noakun']][$bar['alokasi']] += $bar['premi'] + $bar['insentif'] + $bar['insentiflibur'];
				$ttldata += $bar['premi'] + $bar['insentif'] + $bar['insentiflibur'];
			}
		}
	}

	$str = "select * from " . $dbname . ".sdm_lemburdt where tanggal like '" . $param['periode'] . "%' and karyawanid='" . $param['karyawanid'] . "'";
	$res = fetchData($str);
	foreach ($res as $bar) {
		#kolom noakun isinya bisa kode kegiatan!
		if ($bar['noakun'] == '') {
			$bar['noakun'] = $akundebet;
		}
		if ($group == 'KBNB1') {
			if ($bar['uangkelebihanjam'] > 0) {
				$data[$bar['noakun']][$bar['alokasi']] += $bar['uangkelebihanjam'];
				$ttldata += $bar['uangkelebihanjam'];
			}
		}
	}
	$kodeJurnal = $group;

	#proses data
	#======================== Nomor Jurnal =============================
	# Get Journal Counter
	$queryJ = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$tmpKonter = fetchData($queryJ);
	$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

	# Transform No Jurnal dari No Transaksi
	$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
	#======================== /Nomor Jurnal ============================


	$noUrut = 1;
	# Prep Header
	$dataRes['header'] = array(
		'nojurnal'     => $nojurnal,
		'kodejurnal'   => $kodeJurnal,
		'tanggal'      => $tanggal,
		'tanggalentry' => date('Ymd'),
		'posting'      => 1,
		'totaldebet'   => $param['jumlah'],
		'totalkredit'  => -1 * $param['jumlah'],
		'amountkoreksi' => '0',
		'noreferensi'  => 'ALK_GAJI',
		'autojurnal'   => '1',
		'matauang'     => 'IDR',
		'kurs'         => '1',
		'revisi'       => '0'
	);

	#DATA DETAIL DEBET
	if (($group == 'KBNB0' or $group == 'KBNB1') and !empty($data)) {
		# Data Detail
		# Debet
		foreach ($data as $akun => $valokasi) {
			foreach ($valokasi as $blok => $rupiah) {
				if (strlen($akun) > 7) {
					$kegiatan = $akun;
					$akundebet = substr($akun, 0, 7);
				} else {
					$kegiatan = "";
					$akundebet = $akun;
				}

				// $tempakundebet=$akundebet;
				// $tempkegiatan=$kegiatan;

				if ($blok == '') {
					$blok = $param['subbagian'];
				}
				$rpakun = floor($rupiah / $ttldata * $param['jumlah']);

				@$totalrpakun += $rpakun;

				$dataRes['detail'][] = array(
					'nojurnal'    => $nojurnal,
					'tanggal'     => $tanggal,
					'nourut'      => $noUrut,
					'noakun'      => $akundebet,
					'keterangan'  => $param['namakomponen'] . ' ' . $param['periode'],
					'jumlah'      => $rpakun,
					'matauang'    => 'IDR',
					'kurs'        => '1',
					'kodeorg'     => $param['kodeorg'],
					'kodekegiatan' => $kegiatan,
					'kodeasset'   => '',
					'kodebarang'  => '',
					'nik'         => $param['karyawanid'],
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => 'ALK_GAJI',
					'noaruskas'   => '',
					'kodevhc'     => '',
					'nodok'       => '',
					'kodeblok'    => $blok,
					'revisi'      => '0',
					'kodesegment' => $defSegment
				);
				$noUrut++;
			}
		}

		if ($param['jumlah'] - $totalrpakun > 0) {
			$dataRes['detail'][] = array(
				'nojurnal'    => $nojurnal,
				'tanggal'     => $tanggal,
				'nourut'      => $noUrut,
				'noakun'      => $akundebet,
				'keterangan'  => $param['namakomponen'] . ' ' . $param['periode'],
				'jumlah'      => $param['jumlah'] - $totalrpakun,
				'matauang'    => 'IDR',
				'kurs'        => '1',
				'kodeorg'     => $param['kodeorg'],
				'kodekegiatan' => $kegiatan,
				'kodeasset'   => '',
				'kodebarang'  => '',
				'nik'         => $param['karyawanid'],
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => 'ALK_GAJI',
				'noaruskas'   => '',
				'kodevhc'     => '',
				'nodok'       => '',
				'kodeblok'    => $blok,
				'revisi'      => '0',
				'kodesegment' => $defSegment
			);
			$noUrut++;
		}
	} else {
		# Debet
		$dataRes['detail'][] = array(
			'nojurnal'    => $nojurnal,
			'tanggal'     => $tanggal,
			'nourut'      => $noUrut,
			'noakun'      => $akundebet,
			'keterangan'  => $param['namakomponen'] . ' ' . $param['periode'],
			'jumlah'      => $param['jumlah'],
			'matauang'    => 'IDR',
			'kurs'        => '1',
			'kodeorg'     => $param['kodeorg'],
			'kodekegiatan' => '',
			'kodeasset'   => '',
			'kodebarang'  => '',
			'nik'         => $param['karyawanid'],
			'kodecustomer' => '',
			'kodesupplier' => '',
			'noreferensi' => 'ALK_GAJI',
			'noaruskas'   => '',
			'kodevhc'     => '',
			'nodok'       => '',
			'kodeblok'    => $param['subbagian'],
			'revisi'      => '0',
			'kodesegment' => $defSegment
		);
		$noUrut++;
	}

	# Kredit
	$dataRes['detail'][] = array(
		'nojurnal'    => $nojurnal,
		'tanggal'     => $tanggal,
		'nourut'      => $noUrut,
		'noakun'      => $akunkredit,
		'keterangan'  => $param['namakomponen'] . ' ' . $param['periode'],
		'jumlah'      => -1 * $param['jumlah'],
		'matauang'    => 'IDR',
		'kurs'        => '1',
		'kodeorg'     => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset'   => '',
		'kodebarang'  => '',
		'nik'         => $param['karyawanid'],
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => 'ALK_GAJI',
		'noaruskas'   => '',
		'kodevhc'     => '',
		'nodok'       => '',
		'kodeblok'    => $param['subbagian'],
		'revisi'      => '0',
		'kodesegment' => $defSegment
	);
	$noUrut++;

	$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
	$owlPDO->exec($insHead);

	#Insert Detail
	foreach ($dataRes['detail'] as $row) {
		$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
		$owlPDO->exec($insDet);
	}
	# Header and Detail inserted
	# Update Kode Jurnal
	$updJurnal = updateQuery($dbname, 'keu_5kelompokjurnal', array('nokounter' => $konter), "kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' ");
	$owlPDO->exec($updJurnal);
}

function prosesrnd()
{
	global $conn;
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;

	if ($param['komponen'] == 1 or $param['komponen'] == 14) #gapok dan rapel gaji
		$group = 'RNDB0';
	elseif ($param['komponen'] == 16 or $param['komponen'] == 32 or $param['komponen'] == 33) #Premi Pengawasan,Premi,Lembur
		$group = 'RNDB1';
	elseif ($param['komponen'] == 28) #THR
		$group = 'RNDB3';
	elseif ($param['komponen'] == 26) #Bonus
		$group = 'RNDB4';
	elseif ($param['komponen'] == 21) #Klaim Pengobatan
		$group = 'RNDB5';
	else if ($param['komponen'] == 70 or $param['komponen'] == 71 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
		$group = 'RNDB6';
	else
		$group = 'RNDB2';  //defaultnya tunjangan

	$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $group . "' limit 1";
	$res = fetchdata($str);
	$numrows = count($res);
	if ($numrows < 1) {
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen']);
	} else {
		$akundebet = '';
		$akunkredit = '';
		$akundebet = $res[0]['noakundebet'];
		$akunkredit = $res[0]['noakunkredit'];
	}

	#proses data
	$kodeJurnal = $group;
	#======================== Nomor Jurnal =============================
	# Get Journal Counter
	$queryJ = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$tmpKonter = fetchData($queryJ);
	if (count($tmpKonter) == 0) {
		throw new PDOException("Kelompok Jurnal : " . $kodeJurnal . " dan unit : " . $param['kodeorg'] . " dan periode : " . $param['periode'] . " belum ada.");
	}
	$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

	# Transform No Jurnal dari No Transaksi
	$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
	#======================== /Nomor Jurnal ============================


	# Prep Header
	$dataRes['header'] = array(
		'nojurnal' => $nojurnal,
		'kodejurnal' => $kodeJurnal,
		'tanggal' => $tanggal,
		'tanggalentry' => date('Ymd'),
		'posting' => 1,
		'totaldebet' => $param['jumlah'],
		'totalkredit' => -1 * $param['jumlah'],
		'amountkoreksi' => '0',
		'noreferensi' => 'ALK_GAJI',
		'autojurnal' => '1',
		'matauang' => 'IDR',
		'kurs' => '1',
		'revisi' => '0'
	);

	# Data Detail
	$noUrut = 1;

	# Debet
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $akundebet,
		'keterangan' => $param['namakomponen'] . ' ' . $param['periode'],
		'jumlah' => $param['jumlah'],
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => '',
		'kodebarang' => '',
		'nik' => $param['karyawanid'],
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => 'ALK_GAJI',
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => $param['subbagian'],
		'revisi' => '0',
		'kodesegment' => $defSegment
	);
	$noUrut++;

	# Kredit
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $akunkredit,
		'keterangan' => $param['namakomponen'] . ' ' . $param['periode'],
		'jumlah' => -1 * $param['jumlah'],
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => '',
		'kodebarang' => '',
		'nik' => $param['karyawanid'],
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => 'ALK_GAJI',
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => $param['subbagian'],
		'revisi' => '0',
		'kodesegment' => $defSegment
	);
	$noUrut++;

	$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
	$owlPDO->exec($insHead);

	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
	foreach ($dataRes['detail'] as $row) {
		$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
		$owlPDO->exec($insDet);
	}
	# Header and Detail inserted
	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
	$updJurnal = updateQuery(
		$dbname,
		'keu_5kelompokjurnal',
		array('nokounter' => $konter),
		"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$owlPDO->exec($updJurnal);
}

function prosesho()
{
	global $conn;
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;

	if ($param['komponen'] == 1 or $param['komponen'] == 14) #gapok dan rapel gaji
		$group = 'GJHO1';
	elseif ($param['komponen'] == 16 or $param['komponen'] == 32 or $param['komponen'] == 33) #Premi Pengawasan,Premi,Lembur
		$group = 'GJHO2';
	elseif ($param['komponen'] == 28) #THR
		$group = 'GJHO4';
	elseif ($param['komponen'] == 26) #Bonus
		$group = 'GJHO5';
	elseif ($param['komponen'] == 21) #Klaim Pengobatan
		$group = 'GJHO6';
	else if ($param['komponen'] == 70 or $param['komponen'] == 71 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
		$group = 'GJHO7';
	else
		$group = 'GJHO3';  //defaultnya tunjangan

	$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $group . "' limit 1";
	$res = fetchdata($str);
	$numrows = count($res);
	if ($numrows < 1) {
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen']);
	} else {
		$akundebet = '';
		$akunkredit = '';
		$akundebet = $res[0]['noakundebet'];
		$akunkredit = $res[0]['noakunkredit'];
	}

	#proses data
	$kodeJurnal = $group;
	#======================== Nomor Jurnal =============================
	# Get Journal Counter
	$queryJ = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$tmpKonter = fetchData($queryJ);
	if (count($tmpKonter) == 0) {
		throw new PDOException("Kelompok Jurnal : " . $kodeJurnal . " dan unit : " . $param['kodeorg'] . " dan periode : " . $param['periode'] . " belum ada.");
	}
	$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

	# Transform No Jurnal dari No Transaksi
	$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
	#======================== /Nomor Jurnal ============================


	# Prep Header
	$dataRes['header'] = array(
		'nojurnal' => $nojurnal,
		'kodejurnal' => $kodeJurnal,
		'tanggal' => $tanggal,
		'tanggalentry' => date('Ymd'),
		'posting' => 1,
		'totaldebet' => $param['jumlah'],
		'totalkredit' => -1 * $param['jumlah'],
		'amountkoreksi' => '0',
		'noreferensi' => 'ALK_GAJI',
		'autojurnal' => '1',
		'matauang' => 'IDR',
		'kurs' => '1',
		'revisi' => '0'
	);

	# Data Detail
	$noUrut = 1;

	# Debet
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $akundebet,
		'keterangan' => $param['namakomponen'] . ' ' . $param['periode'],
		'jumlah' => $param['jumlah'],
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => '',
		'kodebarang' => '',
		'nik' => $param['karyawanid'],
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => 'ALK_GAJI',
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => $param['subbagian'],
		'revisi' => '0',
		'kodesegment' => $defSegment
	);
	$noUrut++;

	# Kredit
	$dataRes['detail'][] = array(
		'nojurnal' => $nojurnal,
		'tanggal' => $tanggal,
		'nourut' => $noUrut,
		'noakun' => $akunkredit,
		'keterangan' => $param['namakomponen'] . ' ' . $param['periode'],
		'jumlah' => -1 * $param['jumlah'],
		'matauang' => 'IDR',
		'kurs' => '1',
		'kodeorg' => $param['kodeorg'],
		'kodekegiatan' => '',
		'kodeasset' => '',
		'kodebarang' => '',
		'nik' => $param['karyawanid'],
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => 'ALK_GAJI',
		'noaruskas' => '',
		'kodevhc' => '',
		'nodok' => '',
		'kodeblok' => $param['subbagian'],
		'revisi' => '0',
		'kodesegment' => $defSegment
	);
	$noUrut++;

	$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
	$owlPDO->exec($insHead);

	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
	foreach ($dataRes['detail'] as $row) {
		$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
		$owlPDO->exec($insDet);
	}

	# Header and Detail inserted
	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
	$updJurnal = updateQuery(
		$dbname,
		'keu_5kelompokjurnal',
		array('nokounter' => $konter),
		"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$owlPDO->exec($updJurnal);
}


function prosesPabrikasi()
{
	global $tanggal;
	global $param;
	global $dbname;
	global $defSegment;
	global $owlPDO;
	global $orgpt;
	#PBK01   Gaji Pabrikasi
	#PBK02   Biaya Lembur Pabrikasi
	#PBK03   Biaya Tunjangan Lain Pabrikasi
	#PBK04   THR Pabrikasi 
	#PBK05   Bonus Pabrikasi
	#PBK06   Pengobatan Pabrikasi
	#karyawan kebun
	#output pada jurnal kolom noreferensi ALK_GAJI  
	$gajiLangsung = 0;
	$totPabrik = array();
	$kdPabrikasi = array();
	$totalPabrikasi = 0;
	if (($param['komponen'] == 1) || ($param['komponen'] == 32)) {
		if ($param['komponen'] == 1) {
			$gajiabpabrikasi = "umr as gaji";
		}
		if ($param['komponen'] == 32) {
			$gajiabpabrikasi = "premi as gaji";
		}
		$sGaji = "select kodepabrikasi," . $gajiabpabrikasi . ",jhk  from " . $dbname . ".pabrikasi_absensidt a left join 
		" . $dbname . ".pabrikasi_absensiht b on a.notransaksi=b.notransaksi where left(tanggal,7)='" . $param['periode'] . "'
		and karyawanid='" . $param['karyawanid'] . "' ";
		$rGaji = fetchdata($sGaji);
		foreach ($rGaji as $lstGaji) {
			$gajiLangsung += $lstGaji['gaji'];
			$totPabrik[$lstGaji['kodepabrikasi']] += $lstGaji['jhk'];
			$kdPabrikasi[$lstGaji['kodepabrikasi']] = $lstGaji['kodepabrikasi'];
			$totalPabrikasi += $lstGaji['jhk'];
		}
	}
	if ($param['komponen'] == 1 or $param['komponen'] == 14)
		$group = 'PBK01';
	elseif ($param['komponen'] == 16 or $param['komponen'] == 32 or $param['komponen'] == 33)
		$group = 'PBK02';
	elseif ($param['komponen'] == 28)
		$group = 'PBK04';
	elseif ($param['komponen'] == 26)
		$group = 'PBK05';
	elseif ($param['komponen'] == 21)
		$group = 'PBK06';
	else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
		$group = 'PBK07';
	else if ($param['komponen'] == 71)
		$group = 'PBK08';
	else
		$group = 'PBK02';  //defaultnya tunjangan


	$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $group . "' limit 1";
	$rs = $owlPDO->query($str);
	$rs->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($rs);
	if ($numrows < 1) {
		throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen']);
	} else {
		$akundebet = '';
		$akunkredit = '';
		$bar = $rs->fetch();
		$akundebet = $bar->noakundebet;
		$akunkredit = $bar->noakunkredit;
	}

	#proses data
	$kodeJurnal = $group;
	#======================== Nomor Jurnal =============================
	# Get Journal Counter
	$queryJ = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$tmpKonter = fetchData($queryJ);
	$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

	# Transform No Jurnal dari No Transaksi
	$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
	#======================== /Nomor Jurnal ============================
	# Prep Header
	$dataRes['header'] = array(
		'nojurnal' => $nojurnal,
		'kodejurnal' => $kodeJurnal,
		'tanggal' => $tanggal,
		'tanggalentry' => date('Ymd'),
		'posting' => 1,
		'totaldebet' => ($param['jumlah'] - $gajiLangsung),
		'totalkredit' => -1 * ($param['jumlah'] - $gajiLangsung),
		'amountkoreksi' => '0',
		'noreferensi' => 'ALK_PBR_GYMH',
		'autojurnal' => '1',
		'matauang' => 'IDR',
		'kurs' => '1',
		'revisi' => '0'
	);
	# Data Detail
	$noUrut = 1;
	foreach ($kdPabrikasi as $lstPabrikasi) {
		# Debet
		@$nilRp = ($param['jumlah'] - $gajiLangsung) * ($totPabrik[$lstPabrikasi] / $totalPabrikasi);
		$dataRes['detail'][] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => $tanggal,
			'nourut' => $noUrut,
			'noakun' => $akundebet,
			'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By. Pabrikasi ",
			'jumlah' => $nilRp,
			'matauang' => 'IDR',
			'kurs' => '1',
			'kodeorg' => $param['kodeorg'],
			'kodekegiatan' => '',
			'kodeasset' => '',
			'kodebarang' => '',
			'nik' => $param['karyawanid'],
			'kodecustomer' => '',
			'kodesupplier' => '',
			'noreferensi' => 'ALK_PBR_GYMH',
			'noaruskas' => '',
			'kodevhc' => '',
			'nodok' => '',
			'kodeblok' => $lstPabrikasi,
			'revisi' => '0',
			'kodesegment' => $defSegment
		);
		$noUrut++;

		# Kredit
		$dataRes['detail'][] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => $tanggal,
			'nourut' => $noUrut,
			'noakun' => $akunkredit,
			'keterangan' => $param['namakomponen'] . ' ' . $param['periode'] . " By.Pabrikasi",
			'jumlah' => (-1) * $nilRp,
			'matauang' => 'IDR',
			'kurs' => '1',
			'kodeorg' => $param['kodeorg'],
			'kodekegiatan' => '',
			'kodeasset' => '',
			'kodebarang' => '',
			'nik' => $param['karyawanid'],
			'kodecustomer' => '',
			'kodesupplier' => '',
			'noreferensi' => 'ALK_PBR_GYMH',
			'noaruskas' => '',
			'kodevhc' => '',
			'nodok' => '',
			'kodeblok' => '',
			'revisi' => '0',
			'kodesegment' => $defSegment
		);
		$noUrut++;
	}

	$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
	$owlPDO->exec($insHead);

	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
	foreach ($dataRes['detail'] as $row) {
		$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
		$owlPDO->exec($insDet);
	}

	# Header and Detail inserted
	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
	$updJurnal = updateQuery(
		$dbname,
		'keu_5kelompokjurnal',
		array('nokounter' => $konter),
		"kodeorg='" . $orgpt .
			"' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
	);
	$owlPDO->exec($updJurnal);
}
