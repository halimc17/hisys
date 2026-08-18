<?php
error_reporting(1);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

set_time_limit(0); // unlimited

$str = "select id,name from " . $dbname . ".sdm_ho_component";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$namakomponen[$bar->id] = $bar->name;
}

$arrkomponen = explode('###', $param['komponen']);

$orgpt  = "";
$dataorg = array();
$dtstr = "select * from " . $dbname . ".organisasi where  kodeorganisasi = '" . $param['kodeorg'] . "'";
$str = $owlPDO->query($dtstr);
$str->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $str->fetch()) {
	$dataorg[$bar->kodeorganisasi] = $bar;
	$orgpt = $bar->induk;
}

$tanggal = $param['periode'] . "-28";
$totalxxx = 0;

try {
	$owlPDO->beginTransaction();

	if ($param['row'] == '1') {
		#periksa dan hapus transaksi untuk data yang sudah di proses pada periode yang sama    
		$str = "delete from " . $dbname . ".keu_jurnalht where kodejurnal in ('VHCG0','VHC0','VHCG1','VHCG2','VHCG3','VHCG4','VHCG5','VHCG6','VHCG9','VHC0','VHCU0','VHCU1','VHCU2','VHCU3','VHCU4','VHCU5','VHCU6','VHCU7',
		'WSG0','WSG1','WSG2','WSG3','WSG4','WSG5','WSG6','WSG7') 
		and tanggal='" . $tanggal . "' and (nojurnal like '%/" . $param['kodeorg'] . "/%' or noreferensi like '%/" . $param['kodeorg'] . "/%')";
		$owlPDO->exec($str);


		#= ambil data jika gaji operator berbeda unit dengan kendaraannya
		$str = "select * from " . $dbname . ".keu_5caco where kodeorg='" . $param['kodeorg'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$noakuncacounit[$bar['akunpiutang']] = $bar['akunpiutang'];
			$noakuncacounit[$bar['akunhutang']] = $bar['akunhutang'];
		}
		$str = "select * from " . $dbname . ".keu_jurnaldt_vw where 1=1 and noakun in ('" . implode("','", $noakuncacounit) . "') and noreferensi='ALK_TRK_GYMH' and periode='" . $param['periode'] . "' and kodejurnal='M'";
		// exit("Error:$str");
		$res = fetchdata($str);
		foreach ($res as $bar) {
			#= delete 1st
			#= tambahan delete jurnal interconya
			$strdel = "delete from " . $dbname . ".keu_jurnalht where nojurnal='" . $bar['nojurnal'] . "'  ";
			$owlPDO->exec($strdel);
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
	$str = "select tipe from " . $dbname . ".organisasi where kodeorganisasi='" . $param['kodeorg'] . "'";
	$tip = '';
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		$tip = $bar->tipe;
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
				if($param['karyawanid']=='0000006128') {
					echo "Proses Gaji Traksi WS [Pabrik]<br/>";
				}
				prosesGajiWs();
			} else {
				if($param['karyawanid']=='0000006128') {
					echo "Proses Gaji Traksi Traksi [Pabrik]<br/>";
				}
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
				if($param['karyawanid']=='0000006128') {
					echo "Proses Gaji Traksi Umum [Kebun]<br/>";
				}
				prosesGajiTraksiUmum();
			} else {
				if($param['karyawanid']=='0000006128') {
					echo "Proses Gaji Traksi [Kebun]<br/>";
				}
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
	global $arrkomponen;
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

	#3 output pada jurnal kolom noreferensi ALK_WS_GYMH  
	# Pindahkan ke Bawah
	$groupx = 'WS2';
	#ambil akun alokasi
	$str = "select noakunkredit from " . $dbname . ".keu_5parameterjurnal
		where jurnalid='" . $groupx . "' limit 1";
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
	$str = "select kodevhc from " . $dbname . ".vhc_penggantianht a
		left join " . $dbname . ".vhc_penggantiandt_karyawan b on a.notransaksi=b.notransaksi
		where b.karyawanid='" . $param['karyawanid'] . "'
		and a.tanggal>='" . $tgmulai . "' and a.tanggal <='" . $tgsampai . "'
		group by kodevhc order by tanggal asc";

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
	
	$konter = array();
	$insHead = 'insert into keu_jurnalht (nojurnal,kodeJurnal,tanggal,tanggalentry,posting,totaldebet,totalkredit,amountkoreksi,noreferensi,autojurnal,matauang,kurs,revisi) values ';
	$insHead2 = '';
	$insDet = 'insert into keu_jurnaldt (nojurnal,tanggal,noUrut,noakun,keterangan,jumlah,matauang,kurs,kodeorg,kodekegiatan,kodeasset,kodebarang,nik,kodecustomer,kodesupplier,noreferensi,noaruskas,kodevhc,nodok,kodeblok,revisi,kodesegment) values ';
	$insDet2 = '';
	if ($kodevhcx == '') { # Jika Workshop tidak memperbaiki kendaraan
		#output pada jurnal kolom noreferensi ALK_WS_GYMH 
		for ($i = 0; $i < count($arrkomponen); $i++) {
			if ($arrkomponen[$i] == 1 or $arrkomponen[$i] == 14)
				$group = 'WSG0';
			else if ($arrkomponen[$i] == 16 or $arrkomponen[$i] == 32 or $arrkomponen[$i] == 33)
				$group = 'WSG1';
			else if ($arrkomponen[$i] == 70 or $arrkomponen[$i] == 71 or $arrkomponen[$i] == 72 or $arrkomponen[$i] == 73 or $arrkomponen[$i] == 80)
				$group = 'WSG6';
			else if ($arrkomponen[$i] == 28)
				$group = 'WSG3';
			else if ($arrkomponen[$i] == 26)
				$group = 'WSG4';
			else if ($arrkomponen[$i] == 21)
				$group = 'WSG5';
			else
				$group = 'WSG2';  //defaultnya tunjangan

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
			if (!isset($konter[$kodeJurnal][$param['kodeorg']])) {
				$queryJ = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
				);
				$tmpKonter = fetchData($queryJ);
				$konter[$kodeJurnal][$param['kodeorg']] = addZero($tmpKonter[0]['nokounter'] + 1, 3);
			} else {
				if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
					$tambah = 6;
				} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
					$tambah = 5;
				} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
					$tambah = 4;
				} else {
					$tambah = 3;
				}
				$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($konter[$kodeJurnal][$param['kodeorg']]) + 1, $tambah);
			}

			# Transform No Jurnal dari No Transaksi
			$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$param['kodeorg']];

			#======================== /Nomor Jurnal ============================
			#Prep Header
			if ($insHead2 == '') {
				$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $param['jumlah' . $arrkomponen[$i]] . "','" . (-1 * $param['jumlah' . $arrkomponen[$i]]) . "','0','ALK_WS_GYMH','1','IDR','1','0')";
			} else {
				$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $param['jumlah' . $arrkomponen[$i]] . "','" . (-1 * $param['jumlah' . $arrkomponen[$i]]) . "','0','ALK_WS_GYMH','1','IDR','1','0')";
			}
			//echo '1]'.$insHead;
			# Data Detail
			$noUrut = 1;

			# Debet
			if ($insDet2 == '') {
				$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akundebet . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Bengkel','" . (1 * $param['jumlah' . $arrkomponen[$i]]) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_WS_GYMH','','','','','0','" . $defSegment . "')";
			} else {
				$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akundebet . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Bengkel','" . (1 * $param['jumlah' . $arrkomponen[$i]]) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_WS_GYMH','','','','','0','" . $defSegment . "')";
			}
			$noUrut++;

			# Kredit
			$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunkredit . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Bengkel','" . (-1 * $param['jumlah' . $arrkomponen[$i]]) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_WS_GYMH','','','','','0','" . $defSegment . "')";
			$noUrut++;
			//$insHead = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);


			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
			// $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter[$kodeJurnal][$param['kodeorg']]),
			//     "kodeorg='".$orgpt.
			//     "' and kodekelompok='".$kodeJurnal."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."' ");
			// $owlPDO->exec($updJurnal);      
		}
	} else {
		//$dataRes['header']=Array();
		for ($i = 0; $i < count($arrkomponen); $i++) {
			$dataRes['detail'] = array();

			$param['komponen'] = $arrkomponen[$i];
			$param['jumlah'] = $param['jumlah' . $arrkomponen[$i]];
			# Jika Workshop memperbaiki kendaraan
			# Buat per komponenn nya
			#output pada jurnal kolom noreferensi ALK_WS_GYMH  
			if ($param['komponen'] == 1 or $param['komponen'] == 14)
				$group = 'WSG0';
			else if ($param['komponen'] == 16 or $param['komponen'] == 32 or $param['komponen'] == 33)
				$group = 'WSG1';
			else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
				$group = 'WSG6';
			else if ($param['komponen'] == 71)
				$group = 'WSG7';
			else if ($param['komponen'] == 28)
				$group = 'WSG3';
			else if ($param['komponen'] == 26)
				$group = 'WSG4';
			else if ($param['komponen'] == 21)
				$group = 'WSG5';
			else
				$group = 'WSG2';  //defaultnya tunjangan

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
			//echo '<br>isset :'.$kodeJurnal.'---'.$param['kodeorg'].'<br>';

			if (!isset($konter[$kodeJurnal][$param['kodeorg']])) {
				$queryJ = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
				);
				$tmpKonter = fetchData($queryJ);
				$konter[$kodeJurnal][$param['kodeorg']] = addZero($tmpKonter[0]['nokounter'] + 1, 3);
				//echo 'konterx1 :'.addZero($tmpKonter[0]['nokounter']+1,3).'<br>';
			} else {
				if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
					$tambah = 6;
				} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
					$tambah = 5;
				} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
					$tambah = 4;
				} else {
					$tambah = 3;
				}
				$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($konter[$kodeJurnal][$param['kodeorg']]) + 1, $tambah);
				//echo 'konterx2 :'.addZero(intval($konter[$kodeJurnal][$param['kodeorg']])+1,3).'<br>';
			}

			# Transform No Jurnal dari No Transaksi
			$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$param['kodeorg']];
			#======================== /Nomor Jurnal ============================
			# Prep Header
			if ($insHead2 == '') {
				$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $param['jumlah'] . "','" . (-1 * $param['jumlah']) . "','0','ALK_WS_GYMH','1','IDR','1','0')";
			} else {
				$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $param['jumlah'] . "','" . (-1 * $param['jumlah']) . "','0','ALK_WS_GYMH','1','IDR','1','0')";
			}


			# Data Detail
			$noUrut = 1;

			if ($insDet2 == '') {
				$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akundebet . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Bengkel','" . (1 * $param['jumlah']) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_WS_GYMH','','','','','0','" . $defSegment . "')";
			} else {
				$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akundebet . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Bengkel','" . (1 * $param['jumlah']) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_WS_GYMH','','','','','0','" . $defSegment . "')";
			}
			# Debet
			$noUrut++;

			# Kredit
			$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunkredit . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Bengkel','" . (-1 * $param['jumlah']) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_WS_GYMH','','','','','0','" . $defSegment . "')";
			$noUrut++;


			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail

			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal
			// $updJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter[$kodeJurnal][$param['kodeorg']]),
			// 	"kodeorg='".$orgpt.
			// 	"' and kodekelompok='".$kodeJurnal."' and kodeunit='".$param['kodeorg']."' and periode='".$param['periode']."' ");
			// $owlPDO->exec($updJurnal);


			#3 ambil semua lokasi kegiatan
			$str = "select sum(a.jumlah) as jlh,a.alokasibiaya,b.noakun,b.setupkegiatan from " . $dbname . ".vhc_rundt_detail a
			left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
			left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi     
			where c.kodevhc in (" . $kodevhcx . ")
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

			$akunanak = array();
			$str1 = "select akunak,kodetipe from " . $dbname . ".sdm_5tipeasset";
			$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while ($bar1 = $res1->fetch()) {
				$akunak[$bar1->kodetipe] = $bar1->akunak;
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
						if (!isset($akunanak[$tipeasset])) {
							throw new PDOException("Akun aktiva dalam konstruksi untuk " . $tipeasset . " beum disetting dari keuangan->setup->tipeasset");
						} else {
							if ($akunanak[$tipeasset] == '') {
								throw new PDOException("Akun aktiva dalam konstruksi untuk " . $tipeasset . " belum disetting dari keuangan->setup->tipeasset");
							} else {
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

			$penggunaarr = array();
			$str = "select kodeorg,kode from " . $dbname . ".project";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$penggunaarr[$bar->kode] = $bar->kodeorg;
			}

			$str = "select kodeorg,kodepabrikasi from " . $dbname . ".pabrikasi_5masterht ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$penggunaarr[$bar->kodepabrikasi] = $bar->kodeorg;
			}

			#ambil piutang ke pengguna
			$str = "select akunpiutang,jenis,kodeorg from " . $dbname . ".keu_5caco";
			$intracoarr = array();
			$intercoarr = array();
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				if ($bar->jenis == 'intra')
					$intracoarr[$bar->kodeorg] = $bar->akunpiutang;
				else
					$intercoarr[$bar->kodeorg] = $bar->akunpiutang;
			}

			$supplierarr = array();
			$str = "select * from " . $dbname . ".kebun_5namakud where status='1'";
			$res = fetchData($str);
			foreach ($res as $row => $lsDt) {
				$supplierarr[$lsDt['afdeling']] = $lsDt['kodesupplier'];
			}

			$supplierarr2 = array();
			$penggunaarr2 = array();
			$strx = "select * from " . $dbname . ".kebun_5namakud where status='1'";
			$resx = fetchData($strx);
			foreach ($resx as $rowx => $lsDtx) {
				$supplierarr2[$lsDtx['kodesupplier']] = $lsDtx['kodesupplier'];
				$penggunaarr2[$lsDtx['kodesupplier']] = $lsDtx['kodeunit'];
			}

			$ptpenggunaarr = array();
			$str = "select induk,kodeorganisasi from " . $dbname . ".organisasi";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$ptpenggunaarr[$bar->kodeorganisasi] = $bar->induk;
			}

			foreach ($biaya as $key => $nilai) {
				#periksa unit 
				$dataRes['header'] = array();
				$dataRes['detail'] = array();
				$intern = true;

				$pengguna = substr($lokasi[$key], 0, 4);
				if (substr($lokasi[$key], 0, 2) == 'AK' or substr($lokasi[$key], 0, 2) == 'PB') {
					#khusus project dan pabrikasi
					$pengguna = $penggunaarr[$lokasi[$key]];
				}

				#ambil piutang ke pengguna
				$intraco = $intracoarr[$pengguna];
				$interco = $intercoarr[$pengguna];
				//echo $pengguna.'<== 1Pengguna<br>';
				if ($supplierrxx[substr($lokasi[$key], 0, 6)] != '') {
					$supplierrxx = $supplierarr[substr($lokasi[$key], 0, 6)];
				}
				if ($supplierrxx == '') {
					if ($supplierarr2[substr($lokasi[$key], 0, 6)] != '') {
						$supplierrxx = $supplierarr2[substr($lokasi[$key], 0, 6)];
						$pengguna = $penggunaarr2[substr($lokasi[$key], 0, 6)];
					}
				}



				if ($intraco == '' || $interco == '') {
					if ($supplierrxx == '') {
						throw new PDOException("Jika Unit " . $lokasi[$key] . " adalah KUD maka daftarkan dahulu di KUD di Kebun->Setup->Nama Kud \n Jika unit bukan KUD maka daftarkan COA intra/interco Keuangan->setup->akun inter/intraco");
					}
				}



				#++++++++++++++++++++++++++++++++++++++
				$akunpekerjaan = $akun[$key];
				#++++++++++++++++++++++++++++++++++++++++
				$ptpengguna = $ptpenggunaarr[$pengguna];

				$ptGudang = $ptpenggunaarr[$param['kodeorg']];

				//echo $pengguna.'<== 2Pengguna<br>';

				if ($supplierrxx != '') {
					$ptpengguna = $ptGudang;
					$pengguna = $param['kodeorg'];
					#echo $lokasi[$key]."xxx".$ptpengguna."xxx".$ptGudang."xxx".$supplierrxx."xxx".$pengguna;
					#exit('error');
				}
				//echo $pengguna.'<== 3Pengguna<br>';
				#jika pt tidak sama maka pakai akun interco
				$akunpengguna = '';
				if ($ptGudang != $ptpengguna) {
					#ambil akun interco
					$intern = false;
					$akunpengguna = $intercoarr[$param['kodeorg']];
					$akunsendiri = $interco;
					if ($akunpengguna == '')
						throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
				} else if ($pengguna != $param['kodeorg']) { #jika satu pt beda kebun

					#ambil akun intraco
					$intern = false;
					$akunpengguna = $intracoarr[$param['kodeorg']];
					$akunsendiri = $intraco;
					if ($akunpengguna == '')
						throw new PDOException("Akun intraco  atau interco belum ada untuk unit " . $pengguna);
				} else {
					$intern = true;
				}
				// exit('warning'.$param['kodevhc']);
				if ($intern) {
					#proses data
					$kodeJurnal = $group;
					#======================== Nomor Jurnal =============================
					# Get Journal Counter
					if (!isset($konter[$kodeJurnal][$param['kodeorg']])) {
						$queryJ = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
						);
						$tmpKonter = fetchData($queryJ);
						$konter[$kodeJurnal][$param['kodeorg']] = addZero($tmpKonter[0]['nokounter'] + 1, 3);
					} else {
						if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
							$tambah = 6;
						} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
							$tambah = 5;
						} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}
						$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($konter[$kodeJurnal][$param['kodeorg']]) + 1, $tambah);
					}

					#= cek konter dari jurnal



					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$param['kodeorg']];
					#======================== /Nomor Jurnal ============================
					# Prep Header
					if ($insHead2 == '') {
						$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_WS_GYMH','1','IDR','1','0')";
					} else {
						$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_WS_GYMH','1','IDR','1','0')";
					}

					# Data Detail
					$noUrut = 1;
					# Debet
					if ($insDet2 == '') {
						$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . ":Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','" . $supplierrxx . "','ALK_WS_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $defSegment . "')";
					} else {
						$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . ":Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','" . $supplierrxx . "','ALK_WS_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $defSegment . "')";
					}

					$noUrut++;

					# Kredit
					$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunalokws . "','" . $param['periode'] . ":Alokasi biaya kend " . $param['kodevhc'] . "','" . (-1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_WS_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $defSegment . "')";
					$noUrut++;

					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail

					# Header and Detail inserted
					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal

				} else {
					# Data Detail
					$noUrut = 1;
					#proses data
					$kodeJurnal = $group;
					#======================== Nomor Jurnal =============================
					# Get Journal Counter
					if (!isset($konter[$kodeJurnal][$param['kodeorg']])) {
						$queryJ = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
						);
						$tmpKonter = fetchData($queryJ);
						$konter[$kodeJurnal][$param['kodeorg']] = addZero($tmpKonter[0]['nokounter'] + 1, 3);
					} else {
						if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
							$tambah = 6;
						} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
							$tambah = 5;
						} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}
						$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($konter[$kodeJurnal][$param['kodeorg']]) + 1, $tambah);
					}

					#= cek konter dari jurnal


					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . trim($param['kodeorg']) . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$param['kodeorg']];
					$str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periode'] . "' and tutupbuku = '1' 
						and kodeorg='" . $pengguna . "'";
					$rstr = fetchData($str);
					$arrUnit = array();
					if (count($rstr) == '0') { //klo blm tutup buku
						#======================== /Nomor Jurnal ============================
						# Prep Header
						if ($insHead2 == '') {
							$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_WS_GYMH','1','IDR','1','0')";
						} else {
							$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_WS_GYMH','1','IDR','1','0')";
						}


						# Debet
						if ($insDet2 == '') {
							$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunsendiri . "','" . $param['periode'] . ":Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_WS_GYMH','','" . $param['kodevhc'] . "','','','0','" . $defSegment . "')";
						} else {
							$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunsendiri . "','" . $param['periode'] . ":Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_WS_GYMH','','" . $param['kodevhc'] . "','','','0','" . $defSegment . "')";
						}

						$noUrut++;

						# Kredit

						$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunalokws . "','" . $param['periode'] . ":Alokasi biaya kend " . $param['kodevhc'] . "','" . (-1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_WS_GYMH','','" . $param['kodevhc'] . "','','','0','" . $defSegment . "')";

						$noUrut++;

						#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail

						# Header and Detail inserted
						#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal


						#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
						#sisi Pengguna
						$kodeJurnal = $group;
						#ambil periodeaktif pengguna
						$tgmulaid = $tanggal;


						#======================== Nomor Jurnal =============================
						# Get Journal Counter

						if (!isset($konter[$kodeJurnal][$pengguna])) {
							$queryJ = selectQuery(
								$dbname,
								'keu_5kelompokjurnal',
								'nokounter',
								"kodeorg='" . $ptpengguna . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $pengguna . "' and periode='" . $param['periode'] . "' "
							);
							$tmpKonter = fetchData($queryJ);
							$konter[$kodeJurnal][$pengguna] = addZero($tmpKonter[0]['nokounter'] + 1, 3);
						} else {
							if(intval($konter[$kodeJurnal][$pengguna]) > 99999) {
								$tambah = 6;
							} else if(intval($konter[$kodeJurnal][$pengguna]) > 9999) {
								$tambah = 5;
							} else if (intval($konter[$kodeJurnal][$pengguna]) >= 999 and intval($konter[$kodeJurnal][$pengguna]) <= 9998) {
								$tambah = 4;
							} else {
								$tambah = 3;
							}
							$konter[$kodeJurnal][$pengguna] = addZero(intval($konter[$kodeJurnal][$pengguna]) + 1, $tambah);
						}

						#= cek konter dari jurnal
						$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($pengguna) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$konterjurnal = ($bar['konter'] + 1);
						}

						if ($konterjurnal > $konter[$kodeJurnal][$pengguna]) {
							$konter[$kodeJurnal][$pengguna] = $konterjurnal;
						}


						# Transform No Jurnal dari No Transaksi
						$nojurnal = str_replace("-", "", $tgmulaid) . "/" . trim($pengguna) . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$pengguna];
						// echo $nojurnal . '<br>';
						#======================== /Nomor Jurnal ============================
						# Prep Header
						if ($insHead2 == '') {
							$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tgmulaid . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_WS_GYMH','1','IDR','1','0')";
						} else {
							$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tgmulaid . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_WS_GYMH','1','IDR','1','0')";
						}


						# Debet 1
						$noUrut = 1;
						if ($insDet2 == '') {
							$insDet2 .= "('" . $nojurnal . "','" . $tgmulaid . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . ":Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $pengguna . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','','ALK_WS_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
						} else {
							$insDet2 .= ",('" . $nojurnal . "','" . $tgmulaid . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . ":Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $pengguna . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','','ALK_WS_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
						}

						$noUrut++;

						# Kredit 1
						$insDet2 .= ",('" . $nojurnal . "','" . $tgmulaid . "','" . $noUrut . "','" . $akunpengguna . "','" . $param['periode'] . ":Alokasi biaya kend " . $param['kodevhc'] . "','" . (-1 * $biaya[$key]) . "','IDR','1','" . $pengguna . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_WS_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
						$noUrut++;


						#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail

						# Header and Detail inserted
						#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal

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
	$insHead = $insHead . ' ' . $insHead2;
	$insDet = $insDet . ' ' . $insDet2;
	$owlPDO->exec($insHead);
	$owlPDO->exec($insDet);
	foreach ($konter as $kodejurnalx => $key1) {
		foreach ($key1 as $unit => $val) {
			$updJurnal = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $val),
				"kodeorg='" . $ptpenggunaarr[$unit] .
					"' and kodekelompok='" . $kodejurnalx . "'   and kodeunit='" . trim($unit) . "' and periode='" . $param['periode'] . "'  "
			);
			$owlPDO->exec($updJurnal);
			unset($konter[$kodejurnalx][$unit]);
		}
	}
	$konter = array();
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
	global $arrkomponen;
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
	$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)=4";
	$res = $owlPDO->query($str);
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$kodept[$bar['kodeorganisasi']] = $bar['induk'];
	}
	# Prep Header
	$kodekend = $param['mesin'];
	$konter = array();
	$insHead = 'insert into keu_jurnalht (nojurnal,kodejurnal,tanggal,tanggalentry,posting,totaldebet,totalkredit,amountkoreksi,noreferensi,autojurnal,matauang,kurs,revisi) values ';
	$insHead2 = '';
	$insDet = 'insert into keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan,jumlah,matauang,kurs,kodeorg,kodekegiatan,kodeasset,kodebarang,nik,kodecustomer,kodesupplier,noreferensi,noaruskas,kodevhc,nodok,kodeblok,revisi,kodesegment) values ';
	$insDet2 = '';
	if ($kodekend != 'NOVHC') {

		# Cek
			$str = "select vhc from " . $dbname . ".vhc_5operator 
				where karyawanid='" . $param['karyawanid'] . "' and aktif='1'";

				$kodevhcx = '';
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $res->fetch()) {
					if ($kodevhcx == '') {
						$kodevhcx = "'" . $bar->vhc . "'";
					} else {
						$kodevhcx .= ",'" . $bar->vhc . "'";
					}
				}

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

				$str = "select sum(a.jumlah) as jlh,c.kodevhc from " . $dbname . ".vhc_rundt_detail a
				left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi     
				where c.kodevhc in (" . $kodevhcx . ")
				and c.tanggal>='" . $tgmulai . "' and c.tanggal <='" . $tgsampai . "' and alokasibiaya!='' 
				group by c.kodevhc order by tanggal asc";
				// echo $str;
				$tempjam = 0;
				$counttemp = 0;
				$biayavhc = array();
				$arrvhc = array();
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $res->fetch()) {
					$tempjam += $bar->jlh;
					$counttemp++;
				}

				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $res->fetch()) {
					$no++;
					$biayavhc[$bar->kodevhc] = floor(($bar->jlh / $tempjam) * $param['jumlah']);
					$arrvhc[$bar->kodevhc] = $bar->kodevhc;
				}

				# Jika kosong = tidak ada pekerjaan traksi [Pasti Umum], lempar ke proses Traksi Umum
				// echo "CEK : ".count($arrvhc);

				if(count($arrvhc) <= 0) {
					prosesGajiTraksiUmum();
				}
		# Endd cek

		#output pada jurnal kolom noreferensi ALK_TRK_GYMH 
		for ($i = 0; $i < count($arrkomponen); $i++) {
			$param['komponen'] = $arrkomponen[$i];
			$param['jumlah'] = $param['jumlah' . $arrkomponen[$i]];
			if ($param['komponen'] == 1 or $param['komponen'] == 14)
				$group = 'VHCG0';
			else if ($param['komponen'] == 16 or $param['komponen'] == 32 or $param['komponen'] == 33)
				$group = 'VHCG1';
			else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
				$group = 'VHCG6';
			else if ($param['komponen'] == 71)
				$group = 'VHCG9';
			else if ($param['komponen'] == 28)
				$group = 'VHCG3';
			else if ($param['komponen'] == 26)
				$group = 'VHCG4';
			else if ($param['komponen'] == 21)
				$group = 'VHCG5';
			else
				$group = 'VHCG2';  //defaultnya tunjangan

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





			$str = "select vhc from " . $dbname . ".vhc_5operator 
			where karyawanid='" . $param['karyawanid'] . "' and aktif='1'";

			$kodevhcx = '';
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				if ($kodevhcx == '') {
					$kodevhcx = "'" . $bar->vhc . "'";
				} else {
					$kodevhcx .= ",'" . $bar->vhc . "'";
				}
			}

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

			$str = "select sum(a.jumlah) as jlh,c.kodevhc from " . $dbname . ".vhc_rundt_detail a
			left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi     
			where c.kodevhc in (" . $kodevhcx . ")
			and c.tanggal>='" . $tgmulai . "' and c.tanggal <='" . $tgsampai . "' and alokasibiaya!='' 
			group by c.kodevhc order by tanggal asc";
			//echo $str;
			$tempjam = 0;
			$counttemp = 0;
			$biayavhc = array();
			$arrvhc = array();
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$tempjam += $bar->jlh;
				$counttemp++;
			}

			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$no++;
				$biayavhc[$bar->kodevhc] = floor(($bar->jlh / $tempjam) * $param['jumlah']);
				$arrvhc[$bar->kodevhc] = $bar->kodevhc;
			}

			foreach ($arrvhc as $key => $kodekend) {
				#proses data
				$kodeJurnal = $group;
				#======================== Nomor Jurnal =============================
				# Get Journal Counter

				if (!isset($konter[$kodeJurnal][$param['kodeorg']])) {
					$queryJ = selectQuery(
						$dbname,
						'keu_5kelompokjurnal',
						'nokounter',
						"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
					);
					$tmpKonter = fetchData($queryJ);

					if(intval($tmpKonter[0]['nokounter']) > 9999) {
						$tambah = 5;
					} else if (intval($tmpKonter[0]['nokounter']) >= 999 and intval($tmpKonter[0]['nokounter']) <= 9998) {
						$tambah = 4;
					} else {
						$tambah = 3;
					}

					$konter[$kodeJurnal][$param['kodeorg']] = addZero($tmpKonter[0]['nokounter'] + 1, $tambah);
				} else {
					if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
						$tambah = 6;
					} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
						$tambah = 5;
					} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
						$tambah = 4;
					} else {
						$tambah = 3;
					}

					$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($konter[$kodeJurnal][$param['kodeorg']]) + 1, $tambah);
				}

				#= cek konter dari jurnal
				$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$konterjurnal = ($bar['konter'] + 1);
				}

				if ($konterjurnal > $konter[$kodeJurnal][$param['kodeorg']]) {
					$konter[$kodeJurnal][$param['kodeorg']] = $konterjurnal;
				}

				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$param['kodeorg']];
				#======================== /Nomor Jurnal ============================

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
					if ($insHead2 == '') {
						$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biayavhc[$kodekend] . "','" . (-1 * $biayavhc[$kodekend]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
					} else {
						$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biayavhc[$kodekend] . "','" . (-1 * $biayavhc[$kodekend]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
					}

					# Data Detail
					$noUrut = 1;

					# Debet
					if ($insDet2 == '') {
						$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akundebet . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Kendaraan','" . (1 * $biayavhc[$kodekend]) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_TRK_GYMH','','" . $kodekend . "','','','0','" . $defSegment . "')";
					} else {
						$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akundebet . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Kendaraan','" . (1 * $biayavhc[$kodekend]) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_TRK_GYMH','','" . $kodekend . "','','','0','" . $defSegment . "')";
					}
					$noUrut++;

					# Kredit
					$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunkredit . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Kendaraan','" . (-1 * $biayavhc[$kodekend]) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_TRK_GYMH','','" . $kodekend . "','','','0','" . $defSegment . "')";
					$noUrut++;
				} else {

					#=================================================================================================================================
					#=================================================================================================================================
					#=================================================================================================================================


					#= bentuk data kodept	





					#= jurnal pengirim

					#proses data
					$kodeJurnal = $group;
					#======================== Nomor Jurnal =============================
					if (!isset($konter[$kodeJurnal][$param['kodeorg']])) {
						$queryJ = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
						);
						$tmpKonter = fetchData($queryJ);

						if(intval($tmpKonter[0]['nokounter']) > 9999) {
							$tambah = 5;
						} else if (intval($tmpKonter[0]['nokounter']) >= 999 and intval($tmpKonter[0]['nokounter']) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}

						$konter[$kodeJurnal][$param['kodeorg']] = addZero($tmpKonter[0]['nokounter'] + 1, $tambah);
					} else {
						if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
							$tambah = 6;
						} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
							$tambah = 5;
						} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}
						$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($konter[$kodeJurnal][$param['kodeorg']]) + 1, $tambah);
					}
					#= cek konter dari jurnal
					$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$konterjurnal = ($bar['konter'] + 1);
					}

					if ($konterjurnal > $konter[$kodeJurnal][$param['kodeorg']]) {
						$konter[$kodeJurnal][$param['kodeorg']] = $konterjurnal;
					}

					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$param['kodeorg']];


					#======================== /Nomor Jurnal ============================

					#= jika dia CACO
					if ($kodept[$unitpemilik] == $kodept[$param['kodeorg']]) {
						$jenisinduk = 'intra';
					} else {
						$jenisinduk = 'inter';
					}

					$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $param['kodeorg'] . "'";
					$query = selectQuery($dbname, 'keu_5caco', 'akunpiutang', $whereNocaco);
					$noKon = fetchData($query);
					$noakuncaco = $noKon[0]['akunpiutang'];

					if ($noakuncaco == '') {
						exit("Warningsistem:No. Akun Interco/Intraco masih kosong untuk " . $unitpemilik . " ke " . $param['kodeorg'] . " atau sebaliknya, Hubungi Pihak Accounting / IT");
					}


					#= ht
					if ($insHead2 == '') {
						$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biayavhc[$kodekend] . "','" . (-1 * $biayavhc[$kodekend]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
					} else {
						$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biayavhc[$kodekend] . "','" . (-1 * $biayavhc[$kodekend]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
					}

					# Data Detail
					$noUrut = 1;

					# Debet
					if ($insDet2 == '') {
						$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $noakuncaco . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Kendaraan','" . (1 * $biayavhc[$kodekend]) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_TRK_GYMH','','" . $kodekend . "','','','0','" . $defSegment . "')";
					} else {
						$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $noakuncaco . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Kendaraan','" . (1 * $biayavhc[$kodekend]) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_TRK_GYMH','','" . $kodekend . "','','','0','" . $defSegment . "')";
					}
					$noUrut++;

					# Kredit
					$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunkredit . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Kendaraan','" . (-1 * $biayavhc[$kodekend]) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_TRK_GYMH','','" . $kodekend . "','','','0','" . $defSegment . "')";
					$noUrut++;


					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail


					#===============
					#= sisi penerima
					#===============

					$dataRes = array();

					$kodeJurnal = 'M';

					if (!isset($konter[$kodeJurnal][$unitpemilik])) {
						$queryJ = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodeorg='" . $kodept[$unitpemilik] . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $unitpemilik . "' and periode='" . $param['periode'] . "' "
						);
						$tmpKonter = fetchData($queryJ);

						if(intval($tmpKonter[0]['nokounter']) > 9999) {
							$tambah = 5;
						} else if (intval($tmpKonter[0]['nokounter']) >= 999 and intval($tmpKonter[0]['nokounter']) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}

						$konter[$kodeJurnal][$unitpemilik] = addZero($tmpKonter[0]['nokounter'] + 1, $tambah);
					} else {
						if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
							$tambah = 6;
						} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
							$tambah = 5;
						} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}
						$konter[$kodeJurnal][$unitpemilik] = addZero(intval($konter[$kodeJurnal][$unitpemilik]) + 1, $tambah);
					}
					#= cek konter dari jurnal
					$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($unitpemilik) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$konterjurnal = ($bar['konter'] + 1);
					}

					if ($konterjurnal > $konter[$kodeJurnal][$unitpemilik]) {
						$konter[$kodeJurnal][$unitpemilik] = $konterjurnal;
					}

					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . $unitpemilik . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$unitpemilik];


					$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $unitpemilik . "'";
					$query = selectQuery($dbname, 'keu_5caco', 'akunhutang', $whereNocaco);
					$noKon = fetchData($query);
					$noakuncaco = $noKon[0]['akunhutang'];

					if ($noakuncaco == '') {
						exit("Warningsistem:No Akun Interco/Intraco masih kosong untuk " . $param['kodeorg'] . " ke " . $unitpemilik . " atau sebaliknya, Hubungi Pihak Accounting / IT ");
					}


					#= ht
					if ($insHead2 == '') {
						$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biayavhc[$kodekend] . "','" . (-1 * $biayavhc[$kodekend]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
					} else {
						$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biayavhc[$kodekend] . "','" . (-1 * $biayavhc[$kodekend]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
					}

					# Data Detail
					$noUrut = 1;

					# Debet
					if ($insDet2 == '') {
						$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akundebet . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Kendaraan','" . (1 * $biayavhc[$kodekend]) . "','IDR','1','" . $unitpemilik . "','','','','" . $param['karyawanid'] . "','','','ALK_TRK_GYMH','','" . $kodekend . "','','','0','" . $defSegment . "')";
					} else {
						$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akundebet . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Kendaraan','" . (1 * $biayavhc[$kodekend]) . "','IDR','1','" . $unitpemilik . "','','','','" . $param['karyawanid'] . "','','','ALK_TRK_GYMH','','" . $kodekend . "','','','0','" . $defSegment . "')";
					}
					$noUrut++;

					# Kredit
					$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $noakuncaco . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Kendaraan','" . (-1 * $biayavhc[$kodekend]) . "','IDR','1','" . $unitpemilik . "','','','','" . $param['karyawanid'] . "','','','ALK_TRK_GYMH','','" . $kodekend . "','','','0','" . $defSegment . "')";
					$noUrut++;
					// echo"<pre>";
					// print_r($dataRes);
					// exit("Error:A");


					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail


				}
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

			$str = "select vhc from " . $dbname . ".vhc_5operator 
			where karyawanid='" . $param['karyawanid'] . "' and aktif='1'";

			$kodevhcx = '';
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				if ($kodevhcx == '') {
					$kodevhcx = "'" . $bar->vhc . "'";
				} else {
					$kodevhcx .= ",'" . $bar->vhc . "'";
				}
			}
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
			$str = "select sum(a.jumlah) as jlh,a.alokasibiaya,b.noakun,b.setupkegiatan,c.kodevhc from " . $dbname . ".vhc_rundt_detail a
			left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
			left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi     
			where c.kodevhc in (" . $kodevhcx . ")
			and c.tanggal>='" . $tgmulai . "' and c.tanggal <='" . $tgsampai . "' and alokasibiaya!='' 
			and jenispekerjaan!=''    
			group by b.setupkegiatan,b.noakun,a.alokasibiaya,c.kodevhc ";
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
			$tempjumlah = $biayavhc;
			$totalbis = array();

			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$tempjam += $bar->jlh;
				$counttemp++;
			}
			// if($param['karyawanid']=='0000000702'){
			// 	echo '<br>Data TEMP BIAYA : <br>';
			// 	print_r($tempjumlah);
			// }
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

					if ($no == $counttemp) {
						foreach ($arrvhc as $key => $kodevhcxz) {
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
							$biaya[] = $tempjumlah[$kodevhcxz];
							$totalxxx += $tempjumlah[$kodevhcxz];
							$totalbis[$kodevhcxz] += $tempjumlah[$kodevhcxz];
							$karyawanid[] = $param['karyawanid'];
							$vhcx[] = $kodevhcxz;
							$kegiatan[] = '';
							$segment[] = $bar->kodesegment;
						}
					} else {
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
						$biaya[] = floor(($bar->jlh / $tempjam) * $biayavhc[$bar->kodevhc]);
						$totalxxx += floor(($bar->jlh / $tempjam) * $biayavhc[$bar->kodevhc]);
						$totalbis[$bar->kodevhc] += floor(($bar->jlh / $tempjam) * $biayavhc[$bar->kodevhc]);
						$karyawanid[] = $param['karyawanid'];
						$vhcx[] = $bar->kodevhc;
						$kegiatan[] = '';
						$segment[] = $bar->kodesegment;
					}
					$tempjumlah[$bar->kodevhc] = $tempjumlah[$bar->kodevhc] - floor(($bar->jlh / $tempjam) * $biayavhc[$bar->kodevhc]);
				} else {
					if ($no == $counttemp) {
						foreach ($arrvhc as $key => $kodevhcxz) {
							$lokasi[] = $bar->alokasibiaya;
							$akun[]  = $bar->noakun;
							$jam[] = $bar->jlh;
							$biaya[] = $tempjumlah[$kodevhcxz];
							$totalxxx += $tempjumlah[$kodevhcxz];
							$totalbis[$kodevhcxz] += $tempjumlah[$kodevhcxz];
							$karyawanid[] = $param['karyawanid'];
							$vhcx[] = $kodevhcxz;
							$kegiatan[] = $bar->setupkegiatan;
							$kodeasset[] = '';
							$segment[] = $bar->kodesegment;
						}
					} else {
						$lokasi[] = $bar->alokasibiaya;
						$akun[]  = $bar->noakun;
						$jam[] = $bar->jlh;
						$biaya[] = floor(($bar->jlh / $tempjam) * $biayavhc[$bar->kodevhc]);
						$totalxxx += floor(($bar->jlh / $tempjam) * $biayavhc[$bar->kodevhc]);
						$totalbis[$bar->kodevhc] += floor(($bar->jlh / $tempjam) * $biayavhc[$bar->kodevhc]);
						$karyawanid[] = $param['karyawanid'];
						$vhcx[] = $bar->kodevhc;
						$kegiatan[] = $bar->setupkegiatan;
						$kodeasset[] = '';
						$segment[] = $bar->kodesegment;
					}
					$tempjumlah[$bar->kodevhc] = $tempjumlah[$bar->kodevhc] - floor(($bar->jlh / $tempjam) * $biayavhc[$bar->kodevhc]);
				}
				$totalalokasi += floor($bar->jlh * $biayavhc[$bar->kodevhc]);
			}

			// if($param['karyawanid']=='0000000702'){
			// 	echo '<br>Data BIAYA : <br>';
			// 	print_r($biayavhc);
			// 	echo '<br>Data TEMP BIAYA : <br>';
			// 	print_r($tempjumlah);
			// 	echo '<br>Data Teralokasi vhc : <br>';
			// 	print_r($totalbis);
			// 	echo '<br>Data Teralokasi : '.$totalxxx.'<br>';
			// }else{
			// 	exit('Error');
			// }
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
					if (!isset($konter[$kodeJurnal][$param['kodeorg']])) {
						$queryJ = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
						);
						$tmpKonter = fetchData($queryJ);

						if(intval($tmpKonter[0]['nokounter']) > 9999) {
							$tambah = 5;
						} else if (intval($tmpKonter[0]['nokounter']) >= 999 and intval($tmpKonter[0]['nokounter']) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}

						$konter[$kodeJurnal][$param['kodeorg']] = addZero($tmpKonter[0]['nokounter'] + 1, $tambah);
					} else {
						if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
							$tambah = 6;
						} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
							$tambah = 5;
						} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}
						$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($konter[$kodeJurnal][$param['kodeorg']]) + 1, $tambah);
					}



					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$param['kodeorg']];
					#======================== /Nomor Jurnal ============================
					# Prep Header
					if ($insHead2 == '') {
						$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
					} else {
						$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
					}
					# Data Detail
					$noUrut = 1;
					# Debet

					if ($insDet2 == '') {
						$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . " :Biaya Kendaraan " . $vhcx[$key] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','" . $supplierrxx . "','ALK_TRK_GYMH','','" . $vhcx[$key] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
					} else {
						$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . " :Biaya Kendaraan " . $vhcx[$key] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','" . $supplierrxx . "','ALK_TRK_GYMH','','" . $vhcx[$key] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
					}
					$noUrut++;

					# Kredit
					$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunalok . "','" . $param['periode'] . " :Alokasi biaya kend " . $vhcx[$key] . "','" . (-1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $vhcx[$key] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
					$noUrut++;

					#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail

					# Header and Detail inserted
				} else {
					# Data Detail
					$noUrut = 1;
					#proses data
					$kodeJurnal = $group;
					#======================== Nomor Jurnal =============================
					# Get Journal Counter
					if (!isset($konter[$kodeJurnal][$param['kodeorg']])) {
						$queryJ = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
						);
						$tmpKonter = fetchData($queryJ);

						if(intval($tmpKonter[0]['nokounter']) > 9999) {
							$tambah = 5;
						} else if (intval($tmpKonter[0]['nokounter']) >= 999 and intval($tmpKonter[0]['nokounter']) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}

						$konter[$kodeJurnal][$param['kodeorg']] = addZero($tmpKonter[0]['nokounter'] + 1, $tambah);
					} else {
						if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
							$tambah = 6;
						} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
							$tambah = 5;
						} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}
						$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($konter[$kodeJurnal][$param['kodeorg']]) + 1, $tambah);
					}
					#= cek konter dari jurnal


					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . trim($param['kodeorg']) . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$param['kodeorg']];
					$str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periode'] . "' and tutupbuku = '1' 
						and kodeorg='" . $pengguna . "'";
					$rstr = fetchData($str);
					$arrUnit = array();

					if (count($rstr) == '0') { //klo blm tutup buku
						#======================== /Nomor Jurnal ============================
						# Prep Header
						if ($insHead2 == '') {
							$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
						} else {
							$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
						}
						# Debet
						if ($insDet2 == '') {
							$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . " :Biaya Kendaraan " . $vhcx[$key] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $vhcx[$key] . "','','" . $lokasi[$key] . "','0','" . $defSegment . "')";
						} else {
							$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . " :Biaya Kendaraan " . $vhcx[$key] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $vhcx[$key] . "','','" . $lokasi[$key] . "','0','" . $defSegment . "')";
						}
						$noUrut++;

						# Kredit
						$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunalok . "','" . $param['periode'] . " :Alokasi biaya kend " . $vhcx[$key] . "','" . (-1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $vhcx[$key] . "','','" . $lokasi[$key] . "','0','" . $defSegment . "')";

						$noUrut++;

						#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail

						#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
						#sisi Pengguna
						$kodeJurnal = $group;
						#ambil periodeaktif pengguna
						$tgmulaid = $tanggal;


						#======================== Nomor Jurnal =============================
						# Get Journal Counter
						if (!isset($konter[$kodeJurnal][$pengguna])) {
							$queryJ = selectQuery(
								$dbname,
								'keu_5kelompokjurnal',
								'nokounter',
								"kodeorg='" . $ptpengguna . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $pengguna . "' and periode='" . $param['periode'] . "' "
							);
							$tmpKonter = fetchData($queryJ);

							if(intval($tmpKonter[0]['nokounter']) > 9999) {
								$tambah = 5;
							} else if (intval($tmpKonter[0]['nokounter']) >= 999 and intval($tmpKonter[0]['nokounter']) <= 9998) {
								$tambah = 4;
							} else {
								$tambah = 3;
							}

							$konter[$kodeJurnal][$pengguna] = addZero($tmpKonter[0]['nokounter'] + 1, $tambah);
						} else {
							if(intval($konter[$kodeJurnal][$pengguna]) > 99999) {
								$tambah = 6;
							} else if(intval($konter[$kodeJurnal][$pengguna]) > 9999) {
								$tambah = 5;
							} else if (intval($konter[$kodeJurnal][$pengguna]) >= 999 and intval($konter[$kodeJurnal][$pengguna]) <= 9998) {
								$tambah = 4;
							} else {
								$tambah = 3;
							}
							$konter[$kodeJurnal][$pengguna] = addZero(intval($konter[$kodeJurnal][$pengguna]) + 1, $tambah);
						}



						$nojurnalref = $nojurnal;
						# Transform No Jurnal dari No Transaksi
						$nojurnal = str_replace("-", "", $tgmulaid) . "/" . trim($pengguna) . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$pengguna];
						#======================== /Nomor Jurnal ============================
						# Prep Header
						if ($insHead2 == '') {
							$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tgmulaid . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','" . $nojurnalref . "','1','IDR','1','0')";
						} else {
							$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tgmulaid . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','" . $nojurnalref . "','1','IDR','1','0')";
						}

						# Debet 1
						$noUrut = 1;

						if ($insDet2 == '') {
							$insDet2 .= "('" . $nojurnal . "','" . $tgmulaid . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . " :Biaya Kendaraan " . $vhcx[$key] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $pengguna . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $vhcx[$key] . "','" . $nojurnalref . "','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
						} else {
							$insDet2 .= ",('" . $nojurnal . "','" . $tgmulaid . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . " :Biaya Kendaraan " . $vhcx[$key] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $pengguna . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $vhcx[$key] . "','" . $nojurnalref . "','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
						}
						$noUrut++;

						# Kredit 1
						$insDet2 .= ",('" . $nojurnal . "','" . $tgmulaid . "','" . $noUrut . "','" . $akunpengguna . "','" . $param['periode'] . " :Alokasi biaya kendaraan " . $vhcx[$key] . "','" . (-1 * $biaya[$key]) . "','IDR','1','" . $pengguna . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $vhcx[$key] . "','" . $nojurnalref . "','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
						$noUrut++;


						#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
						// foreach ($dataRes['detail'] as $row) {
						// 	$insDet = insertQuery($dbname, 'keu_jurnaldt', $row, array_keys($row));
						// 	$owlPDO->exec($insDet);
						// }
						# Header and Detail inserted

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

		if($_SESSION['standard']['username']=='tim.owl3') {
			// echo $insHead."<br/>";
			// echo $insHead2."<br/>";
			// echo $insDet."<br/>";
			// echo $insDet2."<br/>";
		}

		if ($insHead != '' && $insHead2 != '' && $insDet != '' && $insDet2 != '') {
			$insHead = $insHead . ' ' . $insHead2;
			$insDet = $insDet . ' ' . $insDet2;
			try {
				$owlPDO->exec($insHead);
			} catch (PDOException $e) {
				echo "Error Head Jurnal, {$insHead} " . addslashes($e->getMessage());
				die();
			}
			try {
				$owlPDO->exec($insDet);
			} catch (PDOException $e) {
				// exit("Warning: " . $insDet);
				echo "Error Detail Jurnal, " . addslashes($e->getMessage());
				die();
			}
			foreach ($konter as $kodejurnalx => $key1) {
				foreach ($key1 as $unit => $val) {
					$updJurnal = updateQuery(
						$dbname,
						'keu_5kelompokjurnal',
						array('nokounter' => $val),
						"kodeorg='" . $kodept[$unit] .
							"' and kodekelompok='" . $kodejurnalx . "'   and kodeunit='" . trim($unit) . "' and periode='" . $param['periode'] . "'  "
					);
					$owlPDO->exec($updJurnal);
					unset($konter[$kodejurnalx][$unit]);
				}
			}
			$konter = array();
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
	global $arrkomponen;
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

	#2.5 ambil semua vhc yang terdapat karywan itu mengerjakannya
	$str = "select kodevhc from " . $dbname . ".vhc_penggantianht a
			left join " . $dbname . ".vhc_penggantiandt_karyawan b on a.notransaksi=b.notransaksi
			where b.karyawanid='" . $param['karyawanid'] . "'
			and a.tanggal>='" . $tgmulai . "' and a.tanggal <='" . $tgsampai . "'
			group by kodevhc order by tanggal asc";

	$kodevhcx = '';
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	while ($bar = $res->fetch()) {
		if ($kodevhcx == '') {
			$kodevhcx = "'" . $bar->kodevhc . "'";
		} else {
			$kodevhcx .= ",'" . $bar->kodevhc . "'";
		}
	}
	#====================================================================================================#
	# END NEW
	#====================================================================================================#
	$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)=4";
	$res = $owlPDO->query($str);
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$kodept[$bar['kodeorganisasi']] = $bar['induk'];
	}
	$konter = array();
	$insHead = 'insert into keu_jurnalht (nojurnal,kodejurnal,tanggal,tanggalentry,posting,totaldebet,totalkredit,amountkoreksi,noreferensi,autojurnal,matauang,kurs,revisi) values ';
	$insHead2 = '';
	$insDet = 'insert into keu_jurnaldt (nojurnal,tanggal,nourut,noakun,keterangan,jumlah,matauang,kurs,kodeorg,kodekegiatan,kodeasset,kodebarang,nik,kodecustomer,kodesupplier,noreferensi,noaruskas,kodevhc,nodok,kodeblok,revisi,kodesegment) values ';
	$insDet2 = '';
	if ($kodevhcx == '') { # Jika Workshop tidak memperbaiki kendaraan

		# Cek Sipil
		$sql = "select sum(umr) as umrgaji,nik from " . $dbname . ".vhc_spl_kehadiran_vw where 5=5 and nik='" . $param['karyawanid'] . "' and tanggal like '" . $param['periode'] . "%'";
		$res = fetchData($sql);
		$umrgaji = $res[0]['umrgaji'];
		for ($i = 0; $i < count($arrkomponen); $i++) {
			$param['komponen'] = $arrkomponen[$i];
			$param['jumlah'] = $param['jumlah' . $arrkomponen[$i]];
			//echo '<br> komp : '.$param['komponen'].', jumlah :'.$param['jumlah'].' ,sisa : '.$param['jumlah']-$umrgaji.'<br>';
			if ($param['periode'] > '2025-07') {
				$umrgajibaru = $param['jumlah'];
			} elseif ($param['komponen'] == 1) {
				$umrgajibaru = $param['jumlah'] - $umrgaji;
			} else {
				$umrgajibaru = $param['jumlah'];
			}

			if ($umrgajibaru > 0) {
				# Sisa

				#output pada jurnal kolom noreferensi ALK_WS_GYMH  
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
					throw new PDOException("No.Akun pada parameterjurnal belum ada untuk " . $param['namakomponen'] . " " . $group);
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
				if (!isset($konter[$kodeJurnal][$param['kodeorg']])) {
					$queryJ = selectQuery(
						$dbname,
						'keu_5kelompokjurnal',
						'nokounter',
						"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
					);
					$tmpKonter = fetchData($queryJ);
					$konter[$kodeJurnal][$param['kodeorg']] = addZero($tmpKonter[0]['nokounter'] + 1, 3);
				} else {
					if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
						$tambah = 6;
					} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
						$tambah = 5;
					} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
						$tambah = 4;
					} else {
						$tambah = 3;
					}

					$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($konter[$kodeJurnal][$param['kodeorg']]) + 1, $tambah);
				}

				#= cek konter dari jurnal
				$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$konterjurnal[$kodeJurnal][$param['kodeorg']] = ($bar['konter'] + 1);
				}

				if ($konterjurnal[$kodeJurnal][$param['kodeorg']] > $konter[$kodeJurnal][$param['kodeorg']]) {
					$konter[$kodeJurnal][$param['kodeorg']] = $konterjurnal[$kodeJurnal][$param['kodeorg']];
				}
				
				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$param['kodeorg']];
				#======================== /Nomor Jurnal ============================
				# Prep Header
				if ($insHead2 == '') {
					$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $umrgajibaru . "','" . (-1 * $umrgajibaru) . "','0','ALK_TRKU_GYMH','1','IDR','1','0')";
				} else {
					$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $umrgajibaru . "','" . (-1 * $umrgajibaru) . "','0','ALK_TRKU_GYMH','1','IDR','1','0')";
				}


				# Data Detail
				$noUrut = 1;

				if($umrgajibaru < 0) {
					$umrgajibaru = abs($umrgajibaru);
				}

				# Debet
				if ($insDet2 == '') {
					$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akundebet . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Traksi Umum','" . (1 * $umrgajibaru) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_TRKU_GYMH','','','','','0','" . $defSegment . "')";
				} else {
					$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akundebet . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Traksi Umum','" . (1 * $umrgajibaru) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_TRKU_GYMH','','','','','0','" . $defSegment . "')";
				}
				$noUrut++;

				# Kredit
				$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunkredit . "','" . $param['namakomponen'] . " " . $param['periode'] . " By.Traksi Umum','" . (-1 * $umrgajibaru) . "','IDR','1','" . $param['kodeorg'] . "','','','','" . $param['karyawanid'] . "','','','ALK_TRKU_GYMH','','','','','0','" . $defSegment . "')";
				$noUrut++;
			}
		}
	} else { # Jika Workshop memperbaiki kendaraan
	
		for ($i = 0; $i < count($arrkomponen); $i++) {
			#3 ambil semua lokasi kegiatan
			$str = "select sum(a.jumlah) as jlh,a.alokasibiaya,b.noakun,b.setupkegiatan from " . $dbname . ".vhc_rundt_detail a
					left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
					left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi     
					where c.kodevhc in (" . $kodevhcx . ")
					and c.tanggal>='" . $tgmulai . "' and c.tanggal <='" . $tgsampai . "' and alokasibiaya!='' 
					# and setupkegiatan!=''
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
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {
				$tempjam += $bar->jlh;
				$counttemp++;
			}
			// exit("Error:".$param['jumlahpembulatan']);

		
			$param['komponen'] = $arrkomponen[$i];
			$param['jumlah'] = $param['jumlah' . $arrkomponen[$i]];

			
			if($param['jumlah'] == 0) {
				continue;
			}

			if($param['jumlah'] < 0) {
				$param['jumlah'] = abs($param['jumlah']);
			}

			$tempjumlah = $param['jumlah'];

			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			while ($bar = $res->fetch()) {

				#= insert pembulatan di baris 1
				$no++;
				// if($no>1){
				// $param['jumlahpembulatan']=0;
				// }

				# Pengecekan jika Akun CIP tetapi Alokasi Biaya nya ke Selain CIP 

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
				}
				$totalalokasi += floor($bar->jlh * $param['jumlah']);
			}

			if($biaya[$i] == 0) {
				continue;
			}

			if($biaya[$i] < 0) {
				$biaya[$i] = abs($biaya[$i]);
			}


			$strh = "select distinct noakundebet,sampaidebet  from " . $dbname . ".keu_5parameterjurnal where  jurnalid='LPVHC'";
			$resh = $owlPDO->query($strh) or die(print " Gagal: " . PDOException::getMessage());
			$resh->setFetchMode(PDO::FETCH_OBJ);
			while ($barh = $resh->fetch()) {
				$akunkdari = $barh->noakundebet;
				$akunksampai = $barh->sampaidebet;
			}


			// echo "<pre>";
			// print_r($lokasi);
			// echo "====================================";
			// echo "<pre>";
			// print_r($biaya);

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

				# Jika Pengguna Kosong [Cek]
				if($pengguna == "") {
					throw new PDOException("Ada unit [Alokasi Biaya] di Transaksi Pekerjaan Traksi yang kosong !");
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
					if (!isset($konter[$kodeJurnal][$param['kodeorg']])) {
						$queryJ = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
						);
						$tmpKonter = fetchData($queryJ);

						if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
							$tambah = 5;
						} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}

						$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($tmpKonter[0]['nokounter']) + 1, $tambah);
					} else {
						if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
							$tambah = 6;
						} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
							$tambah = 5;
						} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}
						$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($konter[$kodeJurnal][$param['kodeorg']]) + 1, $tambah);
					}

					#= cek konter dari jurnal
					$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($param['kodeorg']) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$konterjurnal[$kodeJurnal][$param['kodeorg']] = ($bar['konter'] + 1);
					}

					if ($konterjurnal[$kodeJurnal][$param['kodeorg']] > $konter[$kodeJurnal][$param['kodeorg']]) {
						$konter[$kodeJurnal][$param['kodeorg']] = $konterjurnal[$kodeJurnal][$param['kodeorg']];
					}

					// if($param['karyawanid']=='0000006090') {
					// 	echo "<br/>";
					// 	echo $queryJ;
					// 	echo "<br/>";
					// 	echo $intern."<br/>";
					// 	echo "<br/>";
					// 	echo "<pre>";
					// 	print_r($tmpKonter);
					// 	echo "<pre>";
					// 	print_r($konter);
					// 	// exit('warning');
					// }

					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$param['kodeorg']];
					#======================== /Nomor Jurnal ============================
					# Prep Header
					if ($insHead2 == '') {
						$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
					} else {
						$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
					}
					# Data Detail
					$noUrut = 1;
					# Debet
					if ($insDet2 == '') {
						$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . " :Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','" . $supplierrxx . "','ALK_TRK_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
					} else {
						$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . " :Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','" . $supplierrxx . "','ALK_TRK_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
					}
					$noUrut++;

					# Kredit
					$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunalok . "','" . $param['periode'] . " :Alokasi biaya kend " . $param['kodevhc'] . "','" . (-1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
					$noUrut++;
				} else {
					# Data Detail
					$noUrut = 1;
					#proses data
					$kodeJurnal = $group;
					#======================== Nomor Jurnal =============================
					# Get Journal Counter
					if (!isset($konter[$kodeJurnal][$param['kodeorg']])) {
						$queryJ = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodeorg='" . $orgpt . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
						);
						$tmpKonter = fetchData($queryJ);

						if(intval($tmpKonter[0]['nokounter']) > 9999) {
							$tambah = 5;
						} else if (intval($tmpKonter[0]['nokounter']) >= 999 and intval($tmpKonter[0]['nokounter']) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}

						$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($tmpKonter[0]['nokounter']) + 1, $tambah);
					} else {
						if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
							$tambah = 6;
						} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
							$tambah = 5;
						} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
							$tambah = 4;
						} else {
							$tambah = 3;
						}
						$konter[$kodeJurnal][$param['kodeorg']] = addZero(intval($konter[$kodeJurnal][$param['kodeorg']]) + 1, $tambah);
					}



					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . trim($param['kodeorg']) . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$param['kodeorg']];
					$str = "select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periode'] . "' and tutupbuku = '1' 
						and kodeorg='" . $pengguna . "'";
					$rstr = fetchData($str);
					$arrUnit = array();
					if (count($rstr) == '0') { //klo blm tutup buku
						#======================== /Nomor Jurnal ============================
						# Prep Header

						if ($insHead2 == '') {
							$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
						} else {
							$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tanggal . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
						}
						# Debet
						if ($insDet2 == '') {
							$insDet2 .= "('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunsendiri . "','" . $param['periode'] . " :Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $param['kodevhc'] . "','','','0','" . $defSegment . "')";
						} else {
							$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunsendiri . "','" . $param['periode'] . " :Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $param['kodevhc'] . "','','','0','" . $defSegment . "')";
						}
						$noUrut++;

						# Kredit
						$insDet2 .= ",('" . $nojurnal . "','" . $tanggal . "','" . $noUrut . "','" . $akunalok . "','" . $param['periode'] . " :Alokasi biaya kend " . $param['kodevhc'] . "','" . (-1 * $biaya[$key]) . "','IDR','1','" . $param['kodeorg'] . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $param['kodevhc'] . "','','','0','" . $defSegment . "')";

						$noUrut++;


						#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++    
						#sisi Pengguna
						$kodeJurnal = $group;
						#ambil periodeaktif pengguna
						$tgmulaid = $tanggal;


						#======================== Nomor Jurnal =============================
						# Get Journal Counter
						// $queryJ = selectQuery(
						// 	$dbname,
						// 	'keu_5kelompokjurnal',
						// 	'nokounter',
						// 	"kodeorg='" . $ptpengguna . "' and kodekelompok='" . $kodeJurnal . "'  and kodeunit='" . trim($pengguna) . "' and periode='" . $param['periode'] . "'  "
						// );
						// $tmpKonter = fetchData($queryJ);
						// $konter[$kodeJurnal][$pengguna] = addZero($tmpKonter[0]['nokounter'] + 1, 3);
						if (!isset($konter[$kodeJurnal][$pengguna])) {
							$queryJ = selectQuery(
								$dbname,
								'keu_5kelompokjurnal',
								'nokounter',
								"kodeorg='" . $ptpengguna . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $pengguna . "' and periode='" . $param['periode'] . "' "
							);
							$tmpKonter = fetchData($queryJ);

							if(intval($tmpKonter[0]['nokounter']) > 9999) {
								$tambah = 5;
							} else if (intval($tmpKonter[0]['nokounter']) >= 999 and intval($tmpKonter[0]['nokounter']) <= 9998) {
								$tambah = 4;
							} else {
								$tambah = 3;
							}
							
							$konter[$kodeJurnal][$pengguna] = addZero(intval($tmpKonter[0]['nokounter']) + 1, $tambah);
						} else {
							if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 99999) {
								$tambah = 6;
							} else if(intval($konter[$kodeJurnal][$param['kodeorg']]) > 9999) {
								$tambah = 5;
							} else if (intval($konter[$kodeJurnal][$param['kodeorg']]) >= 999 and intval($konter[$kodeJurnal][$param['kodeorg']]) <= 9998) {
								$tambah = 4;
							} else {
								$tambah = 3;
							}
							$konter[$kodeJurnal][$pengguna] = addZero(intval($konter[$kodeJurnal][$pengguna]) + 1, $tambah);
						}

						#= cek konter dari jurnal
						$str = "select max((substring_index(nojurnal,'/',-1)*1)) as konter from " . $dbname . ".keu_jurnaldt_vw where kodeorg='" . trim($pengguna) . "' and kodejurnal='" . $kodeJurnal . "' and periode='" . $param['periode'] . "'";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$konterjurnal = ($bar['konter'] + 1);
						}

						if ($konterjurnal > $konter[$kodeJurnal][$pengguna]) {
							$konter[$kodeJurnal][$pengguna] = $konterjurnal;
						}


						# Transform No Jurnal dari No Transaksi
						$nojurnal = str_replace("-", "", $tgmulaid) . "/" . trim($pengguna) . "/" . $kodeJurnal . "/" . $konter[$kodeJurnal][$pengguna];
						#======================== /Nomor Jurnal ============================
						# Prep Header

						if ($insHead2 == '') {
							$insHead2 .= " ('" . $nojurnal . "','" . $kodeJurnal . "','" . $tgmulaid . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
						} else {
							$insHead2 .= " ,('" . $nojurnal . "','" . $kodeJurnal . "','" . $tgmulaid . "','" . date('Ymd') . "','1','" . $biaya[$key] . "','" . (-1 * $biaya[$key]) . "','0','ALK_TRK_GYMH','1','IDR','1','0')";
						}

						# Debet 1
						$noUrut = 1;
						if ($insDet2 == '') {
							$insDet2 .= "('" . $nojurnal . "','" . $tgmulaid . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . " :Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $pengguna . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
						} else {
							$insDet2 .= ",('" . $nojurnal . "','" . $tgmulaid . "','" . $noUrut . "','" . $akunpekerjaan . "','" . $param['periode'] . " :Biaya Kendaraan " . $param['kodevhc'] . "','" . (1 * $biaya[$key]) . "','IDR','1','" . $pengguna . "','" . $kegiatan[$key] . "','" . $kodeasset[$key] . "','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
						}
						$noUrut++;

						# Kredit 1
						$insDet2 .= ",('" . $nojurnal . "','" . $tgmulaid . "','" . $noUrut . "','" . $akunpengguna . "','" . $param['periode'] . " :Alokasi biaya kend " . $param['kodevhc'] . "','" . (-1 * $biaya[$key]) . "','IDR','1','" . $pengguna . "','" . $kegiatan[$key] . "','','','" . $karyawanid[$key] . "','','','ALK_TRK_GYMH','','" . $param['kodevhc'] . "','','" . $lokasi[$key] . "','0','" . $segment[$key] . "')";
						$noUrut++;

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

	if($_SESSION['standard']['username']=='tim.owl3') {
		// echo $insHead." [Traksi Umum 1]<br/>";
		// echo $insHead2." [Traksi Umum 2]<br/>";
		// echo $insDet." [Traksi Umum]<br/>";
		// echo $insDet2." [Traksi Umum]<br/>";

		// echo $insHead."<br/>";
		// echo $insHead2."<br/>";
		// echo $insDet2."<br/>";
		// echo ($intern == true ? "TRUE" : "FALSE")." [Traksi Umum]<br/>";
		// echo ($kodevhcx == "" ? "KOSONG" : $kodevhcx)." [Traksi Umum]<br/>";
	}

	// exit('Error');

	if ($insHead2 != '') {
		$insHead = $insHead . ' ' . $insHead2;
		$insDet = $insDet . ' ' . $insDet2;
		$owlPDO->exec($insHead);
		$owlPDO->exec($insDet);
		foreach ($konter as $kodejurnalx => $key1) {
			foreach ($key1 as $unit => $val) {
				$updJurnal = updateQuery(
					$dbname,
					'keu_5kelompokjurnal',
					array('nokounter' => $val),
					"kodeorg='" . $kodept[$unit] .
						"' and kodekelompok='" . $kodejurnalx . "'   and kodeunit='" . trim($unit) . "' and periode='" . $param['periode'] . "'  "
				);
				$owlPDO->exec($updJurnal);
				unset($konter[$kodejurnalx][$unit]);
			}
		}
		$konter = array();
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
		else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
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
		else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
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
	else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
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
	else if ($param['komponen'] == 70 or $param['komponen'] == 72 or $param['komponen'] == 73 or $param['komponen'] == 80)
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
?>