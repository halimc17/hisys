<?php
//@Copy nangkoelframework
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

#============================================================
# LKPM - Kegiatan Penanaman Modal (Transaksi)
# Tahap 1: kerangka + header dokumen
#============================================================

$method = checkPostGet('method', '');
$notransaksi = checkPostGet('notransaksi', '');
$kodept = checkPostGet('kodept', '');
$tahun = checkPostGet('tahun', '');
$triwulan = checkPostGet('triwulan', '');
$tanggallapor = tanggalsystemn(checkPostGet('tanggallapor', ''));
$periodedari = tanggalsystemn(checkPostGet('periodedari', ''));
$periodesampai = tanggalsystemn(checkPostGet('periodesampai', ''));
$akumulasidari = tanggalsystemn(checkPostGet('akumulasidari', ''));
$kota = checkPostGet('kota', '');
$namapenandatangan = checkPostGet('namapenandatangan', '');
$jabatan = checkPostGet('jabatan', '');
$notelepon = checkPostGet('notelepon', '');
$email = checkPostGet('email', '');
$penjelasan = checkPostGet('penjelasan', '');
$tklokalsetempat = checkPostGet('tklokalsetempat', '0');
$catatantk = checkPostGet('catatantk', '');

#Tab 2 - rincian realisasi
$idrow = checkPostGet('idrow', '');
$jenis = checkPostGet('jenis', '');
$noakun = checkPostGet('noakun', '');
$uraian = checkPostGet('uraian', '');
$nilaiperiode = checkPostGet('nilaiperiode', '0');
$nilaiakumulasi = checkPostGet('nilaiakumulasi', '0');
$mode = checkPostGet('mode', 'TAMBAH');
$datajson = checkPostGet('datajson', '');
$dtjson = checkPostGet('dtjson', '');
$msjson = checkPostGet('msjson', '');
$sertakan = checkPostGet('sertakan', '0');

#Tab 3 - tenaga kerja & permasalahan
$tkjson = checkPostGet('tkjson', '');
$idmasalah = checkPostGet('idmasalah', '');
$mstanggal = tanggalsystemn(checkPostGet('mstanggal', ''));
$msjenis = checkPostGet('msjenis', '');
$msdetail = checkPostGet('msdetail', '');
$msketerangan = checkPostGet('msketerangan', '');

#profil perusahaan (Bagian I)
$nib = checkPostGet('nib', '');
$nokegiatanusaha = checkPostGet('nokegiatanusaha', '');
$noizin = checkPostGet('noizin', '');
$kegiatanusaha = checkPostGet('kegiatanusaha', '');
$bidangusaha = checkPostGet('bidangusaha', '');
$alamatproyek = checkPostGet('alamatproyek', '');
$alamatkantor = checkPostGet('alamatkantor', '');

#filter list
$ptsrc = checkPostGet('ptsrc', '');
$tahunsrc = checkPostGet('tahunsrc', '');
$triwulansrc = checkPostGet('triwulansrc', '');
$statussrc = checkPostGet('statussrc', '');
$tanggalmulai = tanggalsystemn(checkPostGet('tanggalmulai', ''));
$tanggalsampai = tanggalsystemn(checkPostGet('tanggalsampai', ''));
$page = checkPostGet('page', '0');
$tipe = checkPostGet('tipe', 'html');

$userid = $_SESSION['standard']['userid'];
$now = date('Y-m-d H:i:s');

#------------------------------------------------------------
# Pengamanan parameter: whitelist regex sebelum dipakai di query
#------------------------------------------------------------
$polaKGPN = array(
	'kodept' => '/^[A-Za-z0-9]{1,10}$/',
	'ptsrc' => '/^[A-Za-z0-9]{1,10}$/',
	'notransaksi' => '/^[A-Za-z0-9\-\/]{1,50}$/',
	'tahun' => '/^[0-9]{4}$/',
	'tahunsrc' => '/^[0-9]{4}$/',
	'triwulan' => '/^[0-4]{1}$/',
	'triwulansrc' => '/^[0-4]{1}$/',
	'statussrc' => '/^[01]{1}$/',
	'idrow' => '/^[0-9]{1,11}$/',
	'jenis' => '/^[12]{1}$/',
	'mode' => '/^(TAMBAH|KURANGI|GANTI)$/',
	'noakun' => '/^[0-9]{0,16}$/',
	'idmasalah' => '/^[0-9]{0,11}$/',
	'tipe' => '/^[a-z]{1,10}$/',
	'sertakan' => '/^[01]{1}$/'
);
foreach ($polaKGPN as $kunciKGPN => $polaX) {
	$nilaiX = $$kunciKGPN;
	if ($nilaiX === 'undefined' || $nilaiX === 'null') {
		$nilaiX = $$kunciKGPN = '';
	}
	if ($nilaiX !== '' && (!is_string($nilaiX) || !preg_match($polaX, $nilaiX))) {
		exit("Warning : Parameter " . $kunciKGPN . " tidak valid.");
	}
}
#tanggal hasil konversi tanggalsystemn harus YYYY-MM-DD (kosong menjadi --)
foreach (array('tanggallapor', 'periodedari', 'periodesampai', 'akumulasidari', 'tanggalmulai', 'tanggalsampai', 'mstanggal') as $kunciTgl) {
	if ($$kunciTgl !== '' && $$kunciTgl !== '--' && (!is_string($$kunciTgl) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $$kunciTgl))) {
		exit("Warning : Format tanggal tidak valid.");
	}
}
if ($page === '') {
	$page = '0';
}
$page = (int)$page;
if ($page < 0) {
	$page = 0;
}
$tklokalsetempat = (int)str_replace(',', '', $tklokalsetempat);

#------------------------------------------------------------
# Helper
#------------------------------------------------------------

#tanggal DB dari hasil tanggalsystemn; kosong/'--' disimpan sebagai 0000-00-00
function kgpnTglDb($tgl)
{
	if ($tgl == '' || $tgl == '--') {
		return '0000-00-00';
	}
	return $tgl;
}

#tanggal tampilan: 0000-00-00 / kosong jadi '-'
function kgpnTglView($tgl)
{
	if ($tgl == '' || $tgl == '0000-00-00' || $tgl == '--') {
		return '-';
	}
	return tanggalnormal($tgl);
}

#tanggal + jam tampilan (kolom audit)
function kgpnTglJam($tgl)
{
	if ($tgl == '' || $tgl == '0000-00-00 00:00:00' || $tgl == '0000-00-00') {
		return '-';
	}
	return date('d-m-Y H:i', strtotime($tgl));
}

#validasi PT: harus ada di organisasi dan tipenya PT
function kgpnCekPT($kodept)
{
	global $owlPDO, $dbname;
	if ($kodept == '') {
		return false;
	}
	$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".organisasi where kodeorganisasi=? and tipe='PT'");
	$stmt->execute(array($kodept));
	return ((int)$stmt->fetchColumn()) > 0;
}

#nama karyawan untuk kolom audit
function kgpnNamaUser($userid)
{
	global $owlPDO, $dbname;
	static $cache = array();
	if ($userid == '' || $userid == '0000000000') {
		return '-';
	}
	if (!isset($cache[$userid])) {
		$stmt = $owlPDO->prepare("select namakaryawan from " . $dbname . ".datakaryawan where karyawanid=?");
		$stmt->execute(array($userid));
		$nm = (string)$stmt->fetchColumn();
		$cache[$userid] = ($nm != '') ? $nm : '-';
	}
	return $cache[$userid];
}

#status posting dalam teks
function kgpnStatusTeks($posting)
{
	return ($posting == 1) ? 'Sudah Posting' : 'Belum Posting';
}

#filter list dokumen
function kgpnWhereList()
{
	global $ptsrc, $tahunsrc, $triwulansrc, $statussrc, $tanggalmulai, $tanggalsampai;
	$where = "";
	if ($ptsrc != '') {
		$where .= " and h.kodept='" . $ptsrc . "' ";
	}
	if ($tahunsrc != '') {
		$where .= " and h.tahun='" . $tahunsrc . "' ";
	}
	if ($triwulansrc != '') {
		$where .= " and h.triwulan='" . $triwulansrc . "' ";
	}
	if ($statussrc != '') {
		$where .= " and h.posting='" . $statussrc . "' ";
	}
	if ($tanggalmulai != '--' and $tanggalsampai == '--') {
		$where .= " and h.periodedari='" . $tanggalmulai . "' ";
	} elseif ($tanggalmulai == '--' and $tanggalsampai != '--') {
		$where .= " and h.periodesampai='" . $tanggalsampai . "' ";
	} elseif ($tanggalmulai != '--' and $tanggalsampai != '--') {
		$where .= " and h.periodedari>='" . $tanggalmulai . "' and h.periodesampai<='" . $tanggalsampai . "' ";
	}
	return $where;
}

#info filter aktif untuk kop Excel/PDF (dipakai tahap berikutnya)
function kgpnInfoFilter()
{
	global $ptsrc, $tahunsrc, $triwulansrc, $statussrc, $tanggalmulai, $tanggalsampai, $dbname, $owlPDO;
	$info = array();
	if ($ptsrc != '') {
		$stmt = $owlPDO->prepare("select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi=?");
		$stmt->execute(array($ptsrc));
		$nm = (string)$stmt->fetchColumn();
		$info[] = 'PT: ' . $ptsrc . ($nm != '' ? ' - ' . $nm : '');
	} else {
		$info[] = 'PT: Semua';
	}
	$info[] = 'Tahun: ' . ($tahunsrc != '' ? $tahunsrc : 'Semua');
	if ($triwulansrc != '') {
		$info[] = 'Triwulan: ' . $triwulansrc;
	}
	if ($statussrc != '') {
		$info[] = 'Status: ' . kgpnStatusTeks($statussrc);
	}
	if ($tanggalmulai != '--' || $tanggalsampai != '--') {
		$info[] = 'Periode: ' . ($tanggalmulai != '--' ? tanggalnormal($tanggalmulai) : '...') . ' s/d ' . ($tanggalsampai != '--' ? tanggalnormal($tanggalsampai) : '...');
	}
	return implode(' | ', $info);
}

#simpan / perbarui profil perusahaan (Bagian I) - satu baris per PT
function kgpnSimpanProfil()
{
	global $owlPDO, $dbname, $kodept, $nib, $nokegiatanusaha, $noizin, $kegiatanusaha, $bidangusaha, $alamatproyek, $alamatkantor, $namapenandatangan, $jabatan, $notelepon, $email, $userid, $now;
	$sql = "insert into " . $dbname . ".lgl_kgpnmodal_5perusahaan
			(kodept,nib,nokegiatanusaha,noizin,kegiatanusaha,bidangusaha,alamatproyek,alamatkantor,
			 namapenandatangan,jabatan,notelepon,email,createby,createtime,updateby,updatetime)
			values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
			on duplicate key update
			 nib=values(nib), nokegiatanusaha=values(nokegiatanusaha), noizin=values(noizin),
			 kegiatanusaha=values(kegiatanusaha), bidangusaha=values(bidangusaha),
			 alamatproyek=values(alamatproyek), alamatkantor=values(alamatkantor),
			 namapenandatangan=values(namapenandatangan), jabatan=values(jabatan),
			 notelepon=values(notelepon), email=values(email),
			 updateby=values(updateby), updatetime=values(updatetime)";
	$stmt = $owlPDO->prepare($sql);
	$stmt->execute(array($kodept, $nib, $nokegiatanusaha, $noizin, $kegiatanusaha, $bidangusaha,
		$alamatproyek, $alamatkantor, $namapenandatangan, $jabatan, $notelepon, $email,
		$userid, $now, $userid, $now));
}

#validasi isian inti (Tab 1 Identitas); kembalikan pesan error atau '' kalau lolos
function kgpnValidasiInti()
{
	global $kodept, $tahun, $triwulan, $periodedari, $periodesampai;
	global $nib, $nokegiatanusaha, $noizin, $kegiatanusaha, $bidangusaha, $alamatproyek, $alamatkantor;

	if ($kodept == '') {
		return "Warning : PT wajib dipilih.";
	}
	if (!kgpnCekPT($kodept)) {
		return "Warning : PT tidak valid (harus terdaftar di organisasi dengan tipe PT).";
	}
	if ($tahun == '' || (int)$tahun < 2000 || (int)$tahun > 2100) {
		return "Warning : Tahun wajib diisi dengan benar.";
	}
	if ($triwulan == '') {
		return "Warning : Triwulan wajib dipilih.";
	}
	if ($periodedari == '--' || $periodesampai == '--') {
		return "Warning : Periode laporan (dari dan sampai) wajib diisi.";
	}
	if ($periodedari > $periodesampai) {
		return "Warning : Tanggal periode dari tidak boleh melebihi tanggal sampai.";
	}
	#batas panjang kolom Bagian I
	$batas = array(
		'nib' => array($nib, 30, 'NIB'),
		'nokegiatanusaha' => array($nokegiatanusaha, 60, 'Nomor Kegiatan Usaha'),
		'noizin' => array($noizin, 60, 'No Izin'),
		'kegiatanusaha' => array($kegiatanusaha, 160, 'Kegiatan Usaha'),
		'bidangusaha' => array($bidangusaha, 160, 'Bidang Usaha'),
		'alamatproyek' => array($alamatproyek, 500, 'Alamat Lokasi Proyek'),
		'alamatkantor' => array($alamatkantor, 500, 'Alamat Kantor')
	);
	foreach ($batas as $key => $arr) {
		if (strlen($arr[0]) > $arr[1]) {
			return "Warning : " . $arr[2] . " maksimal " . $arr[1] . " karakter.";
		}
	}
	return '';
}

#validasi isian pengesahan (Tab 5); kembalikan pesan error atau '' kalau lolos
function kgpnValidasiSah()
{
	global $tanggallapor, $namapenandatangan, $jabatan, $notelepon, $email, $penjelasan, $catatantk, $kota;

	if ($tanggallapor == '--' || $tanggallapor == '') {
		return "Warning : Tanggal lapor wajib diisi.";
	}
	if (trim($namapenandatangan) == '' || trim($jabatan) == '') {
		return "Warning : Nama penandatangan dan jabatan wajib diisi.";
	}
	$batas = array(
		'kota' => array($kota, 60, 'Kota'),
		'namapenandatangan' => array($namapenandatangan, 100, 'Nama Penandatangan'),
		'jabatan' => array($jabatan, 100, 'Jabatan'),
		'notelepon' => array($notelepon, 30, 'No Telepon'),
		'email' => array($email, 100, 'Email'),
		'penjelasan' => array($penjelasan, 1000, 'Penjelasan'),
		'catatantk' => array($catatantk, 255, 'Catatan Tenaga Kerja')
	);
	foreach ($batas as $key => $arr) {
		if (strlen($arr[0]) > $arr[1]) {
			return "Warning : " . $arr[2] . " maksimal " . $arr[1] . " karakter.";
		}
	}
	return '';
}

#validasi lengkap (dipakai insert/detail lama)
function kgpnValidasiHeader()
{
	$err = kgpnValidasiInti();
	if ($err != '') {
		return $err;
	}
	return kgpnValidasiSah();
}

#ambil dokumen untuk pengeditan rincian; tolak kalau tidak ada / sudah posting
function kgpnDokumenEdit($notransaksi)
{
	global $owlPDO, $dbname;
	$stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_kgpnmodal_ht where notransaksi=?");
	$stmt->execute(array($notransaksi));
	$d = $stmt->fetch(PDO::FETCH_ASSOC);
	if (!$d) {
		exit("Warning : Dokumen " . $notransaksi . " tidak ditemukan.");
	}
	if ($d['posting'] == 1) {
		exit("Warning : Dokumen sudah diposting, rincian tidak bisa diubah.");
	}
	return $d;
}

#konteks dokumen untuk generate: dari DB kalau dokumen sudah tersimpan,
#dari parameter form kalau masih pengisian baru (belum disimpan)
function kgpnKonteksGenerate()
{
	global $owlPDO, $dbname, $notransaksi, $kodept, $periodedari, $periodesampai, $akumulasidari, $tahun;

	if ($notransaksi != '') {
		$stmt = $owlPDO->prepare("select kodept,periodedari,periodesampai,akumulasidari,tahun,posting from " . $dbname . ".lgl_kgpnmodal_ht where notransaksi=?");
		$stmt->execute(array($notransaksi));
		$d = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($d) {
			if ($d['posting'] == 1) {
				exit("Warning : Dokumen sudah diposting, tidak bisa di-generate ulang.");
			}
			return $d;
		}
	}
	return array(
		'kodept' => $kodept,
		'periodedari' => ($periodedari == '--') ? '0000-00-00' : $periodedari,
		'periodesampai' => ($periodesampai == '--') ? '0000-00-00' : $periodesampai,
		'akumulasidari' => ($akumulasidari == '--') ? '0000-00-00' : $akumulasidari,
		'tahun' => $tahun,
		'posting' => 0
	);
}

#daftar unit di bawah PT (untuk filter kodeorg di jurnal)
function kgpnUnitPT($kodept)
{
	global $owlPDO, $dbname;
	$stmt = $owlPDO->prepare("select kodeorganisasi from " . $dbname . ".organisasi where induk=? or kodeorganisasi=?");
	$stmt->execute(array($kodept, $kodept));
	$arr = array();
	while ($r = $stmt->fetch(PDO::FETCH_COLUMN)) {
		$arr[] = $r;
	}
	return $arr;
}

#daftar akun PT dengan tanda kutip aman untuk IN ()
function kgpnUnitIn($kodept)
{
	$arr = kgpnUnitPT($kodept);
	$out = array();
	foreach ($arr as $u) {
		$out[] = "'" . str_replace("'", "", $u) . "'";
	}
	return implode(',', $out);
}

#validasi nomor akun terhadap bagan akun asli (keu_5akun) dan mapping LKPM
#jenis=1 (Modal Tetap) maupun jenis=2 (Modal Kerja) wajib terdaftar & aktif
#di lgl_kgpnmodal_5coa sesuai jenisnya (sumber angka = mapping COA)
function kgpnAkunValid($noakun, $jenis)
{
	global $owlPDO, $dbname;
	$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".keu_5akun where noakun=?");
	$stmt->execute(array($noakun));
	if ($stmt->fetchColumn() == 0) {
		return false;
	}
	$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_kgpnmodal_5coa where noakun=? and jenis=? and aktif=1");
	$stmt->execute(array($noakun, (int)$jenis));
	if ($stmt->fetchColumn() == 0) {
		return false;
	}
	return true;
}

#format rupiah untuk tampilan grid
function kgpnRp($v)
{
	return number_format((float)$v, 2, ',', '.');
}

#nama jenis
function kgpnJenisTeks($jenis)
{
	return ($jenis == 1) ? 'Modal Tetap' : (($jenis == 2) ? 'Modal Kerja' : '-');
}

#------------------------------------------------------------
# Routing
#------------------------------------------------------------
switch ($method) {

	#--------------------------------------------------------
	# Profil perusahaan + data organisasi (prefill form header)
	#--------------------------------------------------------
	case 'getprofilpt':
		if (!kgpnCekPT($kodept)) {
			exit("Warning : PT tidak valid.");
		}
		$stmt = $owlPDO->prepare("select kodeorganisasi,namaorganisasi,alamat,inisialisasiorganisasi from " . $dbname . ".organisasi where kodeorganisasi=?");
		$stmt->execute(array($kodept));
		$org = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_kgpnmodal_5perusahaan where kodept=?");
		$stmt->execute(array($kodept));
		$prof = $stmt->fetch(PDO::FETCH_ASSOC);
		$hasil = array(
			'kodept' => $kodept,
			'namaorganisasi' => isset($org['namaorganisasi']) ? $org['namaorganisasi'] : '',
			'inisial' => isset($org['inisialisasiorganisasi']) ? $org['inisialisasiorganisasi'] : '',
			'nib' => isset($prof['nib']) ? $prof['nib'] : '',
			'nokegiatanusaha' => isset($prof['nokegiatanusaha']) ? $prof['nokegiatanusaha'] : '',
			'noizin' => isset($prof['noizin']) ? $prof['noizin'] : '',
			'kegiatanusaha' => isset($prof['kegiatanusaha']) ? $prof['kegiatanusaha'] : '',
			'bidangusaha' => isset($prof['bidangusaha']) ? $prof['bidangusaha'] : '',
			'alamatproyek' => isset($prof['alamatproyek']) ? $prof['alamatproyek'] : '',
			#prefill alamat kantor dari organisasi kalau profil belum pernah disimpan
			'alamatkantor' => (isset($prof['alamatkantor']) && $prof['alamatkantor'] != '') ? $prof['alamatkantor'] : (isset($org['alamat']) ? $org['alamat'] : ''),
			'namapenandatangan' => isset($prof['namapenandatangan']) ? $prof['namapenandatangan'] : '',
			'jabatan' => isset($prof['jabatan']) ? $prof['jabatan'] : '',
			'notelepon' => isset($prof['notelepon']) ? $prof['notelepon'] : '',
			'email' => isset($prof['email']) ? $prof['email'] : ''
		);
		echo json_encode($hasil);
		break;

	#--------------------------------------------------------
	# Nomor transaksi otomatis: YYYYMMDD/KGPN/<kodept>/NNN
	#--------------------------------------------------------
	case 'getnotransaksi':
		if ($periodedari == '--' || $kodept == '') {
			exit();
		}
		if (!kgpnCekPT($kodept)) {
			exit("Warning : PT tidak valid.");
		}
		$tempPrd = str_replace('-', '', $periodedari);
		$awalanNo = $tempPrd . "/KGPN/" . $kodept . "/";
		$stmtN = $owlPDO->prepare("select max(cast(substring_index(notransaksi,'/',-1) as unsigned)) from " . $dbname . ".lgl_kgpnmodal_ht where notransaksi like ?");
		$stmtN->execute(array($awalanNo . '%'));
		$urutBaru = ((int)$stmtN->fetchColumn()) + 1;
		$notranbaru = $awalanNo . addZero($urutBaru, 3);
		#dicatat di sesi; kalau nomor sudah dipakai user lain saat simpan, insert ditolak
		$_SESSION['kgpnbaru'][$notranbaru] = 1;
		echo $notranbaru;
		break;

	#--------------------------------------------------------
	# Ambil satu dokumen untuk form edit (header + profil PT)
	#--------------------------------------------------------
	case 'getdokumen':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi kosong.");
		}
		$stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_kgpnmodal_ht where notransaksi=?");
		$stmt->execute(array($notransaksi));
		$d = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$d) {
			exit("Warning : Dokumen " . $notransaksi . " tidak ditemukan.");
		}
		$stmt = $owlPDO->prepare("select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi=?");
		$stmt->execute(array($d['kodept']));
		$namaorg = (string)$stmt->fetchColumn();
		$stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_kgpnmodal_5perusahaan where kodept=?");
		$stmt->execute(array($d['kodept']));
		$prof = $stmt->fetch(PDO::FETCH_ASSOC);

		#tanggal kosong dikirim sebagai string kosong supaya input date bersih
		$tglOut = function ($tgl) {
			return ($tgl == '' || $tgl == '0000-00-00') ? '' : tanggalnormal($tgl);
		};

		#ringkasan Bagian II (dari tabel penyesuaian) dan Bagian III (snapshot tenaga kerja)
		$stmt = $owlPDO->prepare("select
				ifnull(sum(case when jenis=1 then (case when mode='KURANGI' then -nilaiperiode else nilaiperiode end) else 0 end),0) mt_periode,
				ifnull(sum(case when jenis=1 then (case when mode='KURANGI' then -nilaiakumulasi else nilaiakumulasi end) else 0 end),0) mt_akumulasi,
				ifnull(sum(case when jenis=2 then (case when mode='KURANGI' then -nilaiperiode else nilaiperiode end) else 0 end),0) mk_periode,
				ifnull(sum(case when jenis=2 then (case when mode='KURANGI' then -nilaiakumulasi else nilaiakumulasi end) else 0 end),0) mk_akumulasi
			from " . $dbname . ".lgl_kgpnmodal_dt where notransaksi=?");
		$stmt->execute(array($d['notransaksi']));
		$sum = $stmt->fetch(PDO::FETCH_ASSOC);

		$stmt = $owlPDO->prepare("select kategori,sebelum,tambahan,pengurangan,sampai from " . $dbname . ".lgl_kgpnmodal_tk where notransaksi=?");
		$stmt->execute(array($d['notransaksi']));
		$tk = array();
		while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$tk[strtolower($r['kategori'])] = $r;
		}
		#nilai 0 kalau kategori belum ada
		foreach (array('lk', 'pr', 'asg', 'unk') as $kat) {
			if (!isset($tk[$kat])) {
				$tk[$kat] = array('sebelum' => 0, 'tambahan' => 0, 'pengurangan' => 0, 'sampai' => 0);
			}
		}

		#rincian realisasi + permasalahan, dipakai untuk mengisi semua tab sekaligus
		$stmt = $owlPDO->prepare("select id,jenis,noakun,uraian,nilaiperiode,nilaiakumulasi,mode
				from " . $dbname . ".lgl_kgpnmodal_dt where notransaksi=? order by jenis, noakun='' desc, noakun, id");
		$stmt->execute(array($d['notransaksi']));
		$arrDt = array();
		while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$arrDt[] = array(
				'id' => (int)$r['id'],
				'jenis' => (int)$r['jenis'],
				'noakun' => $r['noakun'],
				'uraian' => $r['uraian'],
				'nilaiperiode' => (float)$r['nilaiperiode'],
				'nilaiakumulasi' => (float)$r['nilaiakumulasi'],
				'mode' => $r['mode']
			);
		}
		$stmt = $owlPDO->prepare("select id,urut,tanggal,jenismasalah,detail,keterangan
				from " . $dbname . ".lgl_kgpnmodal_masalah where notransaksi=? order by urut, id");
		$stmt->execute(array($d['notransaksi']));
		$arrMs = array();
		while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$arrMs[] = array(
				'id' => (int)$r['id'],
				'tanggal' => ($r['tanggal'] == '0000-00-00') ? '' : tanggalnormal($r['tanggal']),
				'jenismasalah' => $r['jenismasalah'],
				'detail' => $r['detail'],
				'keterangan' => $r['keterangan']
			);
		}

		$hasil = array(
			'notransaksi' => $d['notransaksi'],
			'kodept' => $d['kodept'],
			'namaorganisasi' => $namaorg,
			'tahun' => $d['tahun'],
			'triwulan' => $d['triwulan'],
			'periodedari' => $tglOut($d['periodedari']),
			'periodesampai' => $tglOut($d['periodesampai']),
			'akumulasidari' => $tglOut($d['akumulasidari']),
			'tanggallapor' => $tglOut($d['tanggallapor']),
			'kota' => $d['kota'],
			'namapenandatangan' => $d['namapenandatangan'],
			'jabatan' => $d['jabatan'],
			'notelepon' => $d['notelepon'],
			'email' => $d['email'],
			'penjelasan' => $d['penjelasan'],
			'tklokalsetempat' => $d['tklokalsetempat'],
			'catatantk' => $d['catatantk'],
			'posting' => $d['posting'],
			'nib' => isset($prof['nib']) ? $prof['nib'] : '',
			'nokegiatanusaha' => isset($prof['nokegiatanusaha']) ? $prof['nokegiatanusaha'] : '',
			'noizin' => isset($prof['noizin']) ? $prof['noizin'] : '',
			'kegiatanusaha' => isset($prof['kegiatanusaha']) ? $prof['kegiatanusaha'] : '',
			'bidangusaha' => isset($prof['bidangusaha']) ? $prof['bidangusaha'] : '',
			'alamatproyek' => isset($prof['alamatproyek']) ? $prof['alamatproyek'] : '',
			'alamatkantor' => isset($prof['alamatkantor']) ? $prof['alamatkantor'] : '',
			'mt_periode' => (float)$sum['mt_periode'],
			'mt_akumulasi' => (float)$sum['mt_akumulasi'],
			'mk_periode' => (float)$sum['mk_periode'],
			'mk_akumulasi' => (float)$sum['mk_akumulasi'],
			'tk_lk_sebelum' => (int)$tk['lk']['sebelum'],
			'tk_lk_tambahan' => (int)$tk['lk']['tambahan'],
			'tk_lk_pengurangan' => (int)$tk['lk']['pengurangan'],
			'tk_lk_sampai' => (int)$tk['lk']['sampai'],
			'tk_pr_sebelum' => (int)$tk['pr']['sebelum'],
			'tk_pr_tambahan' => (int)$tk['pr']['tambahan'],
			'tk_pr_pengurangan' => (int)$tk['pr']['pengurangan'],
			'tk_pr_sampai' => (int)$tk['pr']['sampai'],
			'tk_asg_sebelum' => (int)$tk['asg']['sebelum'],
			'tk_asg_tambahan' => (int)$tk['asg']['tambahan'],
			'tk_asg_pengurangan' => (int)$tk['asg']['pengurangan'],
			'tk_asg_sampai' => (int)$tk['asg']['sampai'],
			'tk_unk_sebelum' => (int)$tk['unk']['sebelum'],
			'tk_unk_tambahan' => (int)$tk['unk']['tambahan'],
			'tk_unk_pengurangan' => (int)$tk['unk']['pengurangan'],
			'tk_unk_sampai' => (int)$tk['unk']['sampai'],
			'dt' => $arrDt,
			'masalah' => $arrMs
		);
		echo json_encode($hasil);
		break;

	#--------------------------------------------------------
	# TAB 2 - grid rincian realisasi + form tambah baris
	#--------------------------------------------------------
	case 'getbaris':
		if ($notransaksi == '' || $idrow == '') {
			exit("Warning : Parameter baris tidak lengkap.");
		}
		$stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_kgpnmodal_dt where id=? and notransaksi=?");
		$stmt->execute(array((int)$idrow, $notransaksi));
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$r) {
			exit("Warning : Baris tidak ditemukan pada dokumen ini.");
		}
		echo json_encode(array(
			'id' => $r['id'],
			'jenis' => $r['jenis'],
			'noakun' => $r['noakun'],
			'uraian' => $r['uraian'],
			'nilaiperiode' => (float)$r['nilaiperiode'],
			'nilaiakumulasi' => (float)$r['nilaiakumulasi'],
			'mode' => $r['mode']
		));
		break;

	#--------------------------------------------------------
	# Simpan satu baris rincian (insert / update)
	#--------------------------------------------------------
	case 'savedetail':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi kosong.");
		}
		kgpnDokumenEdit($notransaksi);
		if ($jenis != '1' && $jenis != '2') {
			exit("Warning : Jenis harus Modal Tetap atau Modal Kerja.");
		}
		if (trim($uraian) == '') {
			exit("Warning : Uraian wajib diisi.");
		}
		if (strlen($uraian) > 255) {
			exit("Warning : Uraian maksimal 255 karakter.");
		}
		if ($mode != 'TAMBAH' && $mode != 'KURANGI' && $mode != 'GANTI') {
			exit("Warning : Mode tidak valid.");
		}
		$np = str_replace(',', '', $nilaiperiode);
		$na = str_replace(',', '', $nilaiakumulasi);
		if (!is_numeric($np) || !is_numeric($na)) {
			exit("Warning : Nilai periode / akumulasi harus angka.");
		}
		$np = (float)$np;
		$na = (float)$na;
		if ($np < 0 || $na < 0) {
			exit("Warning : Nilai tidak boleh negatif (pakai Mode KURANGI untuk pengurangan).");
		}
		#noakun hanya boleh diisi kalau memang ada di master mapping
		$noakun = trim($noakun);
		if ($noakun != '' && !kgpnAkunValid($noakun, (int)$jenis)) {
			exit("Warning : No akun " . $noakun . " tidak valid untuk jenis " . kgpnJenisTeks($jenis) . ".");
		}

		if ($idrow != '' && $idrow != '0') {
			$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_kgpnmodal_dt where id=? and notransaksi=?");
			$stmt->execute(array((int)$idrow, $notransaksi));
			if ($stmt->fetchColumn() == 0) {
				exit("Warning : Baris tidak ditemukan pada dokumen ini.");
			}
			$sql = "update " . $dbname . ".lgl_kgpnmodal_dt set jenis=?, noakun=?, uraian=?, nilaiperiode=?, nilaiakumulasi=?, mode=?, updateby=?, updatetime=? where id=? and notransaksi=?";
			$stmt = $owlPDO->prepare($sql);
			$stmt->execute(array((int)$jenis, $noakun, $uraian, $np, $na, $mode, $userid, $now, (int)$idrow, $notransaksi));
			echo "OK";
		} else {
			$sql = "insert into " . $dbname . ".lgl_kgpnmodal_dt (notransaksi,jenis,noakun,uraian,nilaiperiode,nilaiakumulasi,mode,createby,createtime,updateby,updatetime) values (?,?,?,?,?,?,?,?,?,?,?)";
			$stmt = $owlPDO->prepare($sql);
			$stmt->execute(array($notransaksi, (int)$jenis, $noakun, $uraian, $np, $na, $mode, $userid, $now, $userid, $now));
			echo "OK";
		}
		break;

	#--------------------------------------------------------
	# Hapus satu baris rincian
	#--------------------------------------------------------
	case 'deletedetail':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi kosong.");
		}
		kgpnDokumenEdit($notransaksi);
		if ($idrow == '' || $idrow == '0') {
			exit("Warning : Baris yang dihapus tidak ditentukan.");
		}
		$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_dt where id=? and notransaksi=?");
		$stmt->execute(array((int)$idrow, $notransaksi));
		echo "OK";
		break;

	#--------------------------------------------------------
	# Hapus semua baris hasil generate (noakun terisi), baris manual tetap
	#--------------------------------------------------------
	case 'hapusgenerate':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi kosong.");
		}
		kgpnDokumenEdit($notransaksi);
		$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_dt where notransaksi=? and noakun<>''");
		$stmt->execute(array($notransaksi));
		echo "OK";
		break;

	#--------------------------------------------------------
	# Generate rincian dari jurnal (preview, belum disimpan)
	#   jenis=2 Modal Kerja  : akun sesuai master COA jenis=2
	#   jenis=1 Modal Tetap  : CIP 12903* DEBET SAJA
	#--------------------------------------------------------
	case 'generate':
		if ($jenis != '1' && $jenis != '2') {
			exit("Warning : Jenis harus 1 (Modal Tetap) atau 2 (Modal Kerja).");
		}
		$dok = kgpnKonteksGenerate();
		if ($dok['periodedari'] == '0000-00-00' || $dok['periodesampai'] == '0000-00-00') {
			exit("Warning : Periode laporan belum lengkap. Isi PT, Tahun, dan Triwulan di Tab Identitas dulu.");
		}
		if ($dok['kodept'] == '') {
			exit("Warning : PT belum dipilih.");
		}
		#akumulasi mulai kosong = awal tahun berjalan
		$akumDari = ($dok['akumulasidari'] == '0000-00-00') ? $dok['tahun'] . '-01-01' : $dok['akumulasidari'];
		$unitIn = kgpnUnitIn($dok['kodept']);
		if ($unitIn == '') {
			exit("Warning : Unit di bawah PT " . $dok['kodept'] . " tidak ditemukan.");
		}
		$periodeDari = $dok['periodedari'];
		$periodeSampai = $dok['periodesampai'];

		$rows = array();
		if ($jenis == '2') {
			#Modal Kerja: netto (debet - kredit) per akun, hanya jurnal terposting
			#alias agregat tidak boleh dipakai di HAVING (MariaDB error 1247) -> bungkus derived table
			$sql = "select x.noakun, x.namaakun, x.periode, x.akumulasi from (
						select c.noakun, c.namaakun,
							sum(case when h.tanggal between ? and ? then d.jumlah else 0 end) periode,
							sum(d.jumlah) akumulasi
						from " . $dbname . ".keu_jurnaldt d
						join " . $dbname . ".keu_jurnalht h on h.nojurnal=d.nojurnal
						join " . $dbname . ".lgl_kgpnmodal_5coa c on c.noakun=d.noakun and c.jenis=2 and c.aktif=1
						where h.posting=1 and h.tanggal between ? and ?
						  and d.kodeorg in (" . $unitIn . ")
						group by c.noakun, c.namaakun
					) x where x.periode<>0 or x.akumulasi<>0
					order by abs(x.akumulasi) desc, x.noakun";
			$stmt = $owlPDO->prepare($sql);
			$stmt->execute(array($periodeDari, $periodeSampai, $akumDari, $periodeSampai));
		} else {
			#Modal Tetap: akun sesuai master mapping COA jenis=1 (bisa ditambah/dikurangi user).
			#DEBET SAJA, karena keterangan mapping = "Nilai Perolehan" (nilai perolehan aset).
			#Kredit pada akun aset = penyusutan / transfer keluar, bukan realisasi investasi.
			#sertakan=1 -> ikut membaca jurnal yang BELUM diposting (opsi khusus Modal Tetap,
			#dipakai kalau jurnal aset memang tidak pernah diposting di modul Keuangan).
			$filterPosting = ($sertakan == '1') ? "" : " and h.posting=1 ";
			$sql = "select x.noakun, x.namaakun, x.periode, x.akumulasi from (
						select c.noakun, c.namaakun,
							sum(case when h.tanggal between ? and ? and d.jumlah>0 then d.jumlah else 0 end) periode,
							sum(case when d.jumlah>0 then d.jumlah else 0 end) akumulasi
						from " . $dbname . ".keu_jurnaldt d
						join " . $dbname . ".keu_jurnalht h on h.nojurnal=d.nojurnal
						join " . $dbname . ".lgl_kgpnmodal_5coa c on c.noakun=d.noakun and c.jenis=1 and c.aktif=1
						where 1=1 " . $filterPosting . " and h.tanggal between ? and ?
						  and d.kodeorg in (" . $unitIn . ")
						group by c.noakun, c.namaakun
					) x where x.periode<>0 or x.akumulasi<>0
					order by abs(x.akumulasi) desc, x.noakun";
			$stmt = $owlPDO->prepare($sql);
			$stmt->execute(array($periodeDari, $periodeSampai, $akumDari, $periodeSampai));
		}
		$totP = 0;
		$totA = 0;
		while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$rows[] = array(
				'noakun' => $r['noakun'],
				'namaakun' => $r['namaakun'],
				'periode' => (float)$r['periode'],
				'akumulasi' => (float)$r['akumulasi']
			);
			$totP += (float)$r['periode'];
			$totA += (float)$r['akumulasi'];
		}
		echo json_encode(array(
			'jenis' => (int)$jenis,
			'periodedari' => tanggalnormal($periodeDari),
			'periodesampai' => tanggalnormal($periodeSampai),
			'akumulasidari' => tanggalnormal($akumDari),
			'rows' => $rows,
			'totalperiode' => $totP,
			'totalakumulasi' => $totA,
			'sertakan' => ($jenis == '1' && $sertakan == '1') ? 1 : 0
		));
		break;

	#--------------------------------------------------------
	# Simpan hasil generate ke grid (ganti baris ber-noakun lama)
	#--------------------------------------------------------
	case 'simpangenerate':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi kosong.");
		}
		kgpnDokumenEdit($notransaksi);
		if ($jenis != '1' && $jenis != '2') {
			exit("Warning : Jenis tidak valid.");
		}
		$arr = json_decode($datajson, true);
		if (!is_array($arr) || count($arr) == 0) {
			exit("Warning : Tidak ada data hasil generate untuk disimpan.");
		}
		if (count($arr) > 600) {
			exit("Warning : Jumlah baris hasil generate terlalu banyak.");
		}
		try {
			$owlPDO->beginTransaction();
			#hapus baris lama yang berasal dari generate (noakun terisi) untuk jenis ini
			$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_dt where notransaksi=? and jenis=? and noakun<>''");
			$stmt->execute(array($notransaksi, (int)$jenis));
			#validasi akun terhadap bagan akun + mapping, lalu simpan
			$stmtIns = $owlPDO->prepare("insert into " . $dbname . ".lgl_kgpnmodal_dt (notransaksi,jenis,noakun,uraian,nilaiperiode,nilaiakumulasi,mode,createby,createtime,updateby,updatetime) values (?,?,?,?,?,?,?,?,?,?,?)");
			$simpan = 0;
			$dilewati = 0;
			foreach ($arr as $r) {
				$nk = isset($r['noakun']) ? trim($r['noakun']) : '';
				$nm = isset($r['namaakun']) ? trim($r['namaakun']) : '';
				$np = isset($r['periode']) ? (float)$r['periode'] : 0;
				$na = isset($r['akumulasi']) ? (float)$r['akumulasi'] : 0;
				if ($nk == '' || !preg_match('/^[0-9]{1,16}$/', $nk)) {
					continue;
				}
				if (!kgpnAkunValid($nk, (int)$jenis)) {
					$dilewati++;
					continue;
				}
				if (strlen($nm) > 255) {
					$nm = substr($nm, 0, 255);
				}
				#nilai negatif disimpan sebagai baris mode KURANGI dengan nilai positif
				$mode = ($np < 0 || $na < 0) ? 'KURANGI' : 'TAMBAH';
				$stmtIns->execute(array($notransaksi, (int)$jenis, $nk, $nm, abs($np), abs($na), $mode, $userid, $now, $userid, $now));
				$simpan++;
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollBack();
			exit("Warning : Gagal menyimpan hasil generate. " . $e->getMessage());
		}
		echo "OK|" . $simpan . "|" . $dilewati;
		break;

	#--------------------------------------------------------
	# TAB 3a - GENERATE TENAGA KERJA dari data SDM
	#   rumus terverifikasi: sebelum + tambahan - pengurangan = sampai
	#   Komisaris & Direksi dikecualikan via lgl_kgpnmodal_5jabatanexclude
	#--------------------------------------------------------
	case 'generatetk':
		$dok = kgpnKonteksGenerate();
		if ($dok['periodedari'] == '0000-00-00' || $dok['periodesampai'] == '0000-00-00') {
			exit("Warning : Periode laporan belum lengkap. Isi PT, Tahun, dan Triwulan di Tab Identitas dulu.");
		}
		if ($dok['kodept'] == '') {
			exit("Warning : PT belum dipilih.");
		}
		$unitIn = kgpnUnitIn($dok['kodept']);
		if ($unitIn == '') {
			exit("Warning : Unit di bawah PT " . $dok['kodept'] . " tidak ditemukan.");
		}
		$awal = $dok['periodedari'];
		$akhir = $dok['periodesampai'];

		$sql = "select
					case when k.warganegara not in ('ID','Indonesia','') then 'ASG'
						 when k.jeniskelamin='L' then 'LK'
						 when k.jeniskelamin='P' then 'PR'
						 else 'UNK' end kategori,
					sum(case when k.tanggalmasuk < ?
							  and (k.tanggalkeluar='0000-00-00' or k.tanggalkeluar is null or k.tanggalkeluar >= ?)
							 then 1 else 0 end) sebelum,
					sum(case when k.tanggalmasuk between ? and ? then 1 else 0 end) tambahan,
					sum(case when k.tanggalkeluar between ? and ? then 1 else 0 end) pengurangan,
					sum(case when k.tanggalmasuk <= ?
							  and (k.tanggalkeluar='0000-00-00' or k.tanggalkeluar is null or k.tanggalkeluar > ?)
							 then 1 else 0 end) sampai
				from " . $dbname . ".datakaryawan k
				where k.kodeorganisasi in (" . $unitIn . ")
				  and k.kodejabatan not in (select kodejabatan from " . $dbname . ".lgl_kgpnmodal_5jabatanexclude where aktif=1)
				  #buang baris data mustahil: tanggal keluar lebih awal dari tanggal masuk
				  #(tanpa ini identitas sebelum+tambahan-pengurangan = sampai tidak balance)
				  and (k.tanggalkeluar='0000-00-00' or k.tanggalkeluar is null or k.tanggalkeluar >= k.tanggalmasuk)
				group by kategori";
		$stmt = $owlPDO->prepare($sql);
		$stmt->execute(array($awal, $awal, $awal, $akhir, $awal, $akhir, $akhir, $akhir));

		$hasil = array('LK' => 0, 'PR' => 0, 'ASG' => 0, 'UNK' => 0);
		while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$kat = $r['kategori'];
			$hasil[$kat] = array(
				'sebelum' => (int)$r['sebelum'],
				'tambahan' => (int)$r['tambahan'],
				'pengurangan' => (int)$r['pengurangan'],
				'sampai' => (int)$r['sampai']
			);
		}
		#kategori tanpa baris -> nol semua
		foreach ($hasil as $kat => $v) {
			if (!is_array($v)) {
				$hasil[$kat] = array('sebelum' => 0, 'tambahan' => 0, 'pengurangan' => 0, 'sampai' => 0);
			}
		}
		#info jumlah karyawan bergender kosong untuk peringatan
		$stmtW = $owlPDO->prepare("select count(*) from " . $dbname . ".datakaryawan k
				where k.kodeorganisasi in (" . $unitIn . ") and k.jeniskelamin=''
				  and k.kodejabatan not in (select kodejabatan from " . $dbname . ".lgl_kgpnmodal_5jabatanexclude where aktif=1)
				  and (k.tanggalkeluar='0000-00-00' or k.tanggalkeluar is null or k.tanggalkeluar >= k.tanggalmasuk)");
		$stmtW->execute();
		$jmlKosong = (int)$stmtW->fetchColumn();

		echo json_encode(array(
			'periodedari' => tanggalnormal($awal),
			'periodesampai' => tanggalnormal($akhir),
			'lk' => $hasil['LK'],
			'pr' => $hasil['PR'],
			'asg' => $hasil['ASG'],
			'unk' => $hasil['UNK'],
			'genderkosong' => $jmlKosong
		));
		break;

	#--------------------------------------------------------
	# TAB 3b - simpan tenaga kerja (upsert 4 kategori)
	#--------------------------------------------------------
	case 'savetk':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi kosong.");
		}
		kgpnDokumenEdit($notransaksi);
		$arr = json_decode($tkjson, true);
		if (!is_array($arr)) {
			exit("Warning : Data tenaga kerja tidak valid.");
		}
		$valid = array('LK', 'PR', 'ASG', 'UNK');
		try {
			$owlPDO->beginTransaction();
			$stmt = $owlPDO->prepare("insert into " . $dbname . ".lgl_kgpnmodal_tk
					(notransaksi,kategori,sebelum,tambahan,pengurangan,sampai,updateby,updatetime)
					values (?,?,?,?,?,?,?,?)
					on duplicate key update sebelum=values(sebelum), tambahan=values(tambahan),
						pengurangan=values(pengurangan), sampai=values(sampai),
						updateby=values(updateby), updatetime=values(updatetime)");
			foreach ($valid as $kat) {
				$v = isset($arr[$kat]) ? $arr[$kat] : array();
				$seb = isset($v['sebelum']) ? (int)$v['sebelum'] : 0;
				$tam = isset($v['tambahan']) ? (int)$v['tambahan'] : 0;
				$pen = isset($v['pengurangan']) ? (int)$v['pengurangan'] : 0;
				$sam = isset($v['sampai']) ? (int)$v['sampai'] : 0;
				if ($seb < 0 || $tam < 0 || $pen < 0 || $sam < 0) {
					$owlPDO->rollBack();
					exit("Warning : Jumlah tenaga kerja tidak boleh negatif.");
				}
				$stmt->execute(array($notransaksi, $kat, $seb, $tam, $pen, $sam, $userid, $now));
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollBack();
			exit("Warning : Gagal menyimpan tenaga kerja. " . $e->getMessage());
		}
		echo "OK";
		break;

	#--------------------------------------------------------
	# TAB 3c - ambil satu permasalahan untuk form edit
	#--------------------------------------------------------
	case 'getmasalah':
		if ($notransaksi == '' || $idmasalah == '') {
			exit("Warning : Parameter permasalahan tidak lengkap.");
		}
		$stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_kgpnmodal_masalah where id=? and notransaksi=?");
		$stmt->execute(array((int)$idmasalah, $notransaksi));
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$r) {
			exit("Warning : Data permasalahan tidak ditemukan.");
		}
		echo json_encode(array(
			'id' => $r['id'],
			'tanggal' => ($r['tanggal'] == '0000-00-00') ? '' : tanggalnormal($r['tanggal']),
			'jenismasalah' => $r['jenismasalah'],
			'detail' => $r['detail'],
			'keterangan' => $r['keterangan']
		));
		break;

	#--------------------------------------------------------
	# TAB 3d - simpan permasalahan (insert / update)
	#--------------------------------------------------------
	case 'savemasalah':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi kosong.");
		}
		kgpnDokumenEdit($notransaksi);
		if ($mstanggal == '--') {
			exit("Warning : Tanggal melaporkan wajib diisi.");
		}
		if (trim($msjenis) == '') {
			exit("Warning : Jenis masalah wajib diisi.");
		}
		if (strlen($msjenis) > 200 || strlen($msdetail) > 500 || strlen($msketerangan) > 500) {
			exit("Warning : Isian terlalu panjang (jenis maks 200, detail & keterangan maks 500).");
		}
		if ($idmasalah != '' && $idmasalah != '0') {
			$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_kgpnmodal_masalah where id=? and notransaksi=?");
			$stmt->execute(array((int)$idmasalah, $notransaksi));
			if ($stmt->fetchColumn() == 0) {
				exit("Warning : Data permasalahan tidak ditemukan pada dokumen ini.");
			}
			$sql = "update " . $dbname . ".lgl_kgpnmodal_masalah set tanggal=?, jenismasalah=?, detail=?, keterangan=?, updateby=?, updatetime=? where id=? and notransaksi=?";
			$stmt = $owlPDO->prepare($sql);
			$stmt->execute(array($mstanggal, $msjenis, $msdetail, $msketerangan, $userid, $now, (int)$idmasalah, $notransaksi));
		} else {
			#nomor urut = maksimum + 1
			$stmt = $owlPDO->prepare("select ifnull(max(urut),0)+1 from " . $dbname . ".lgl_kgpnmodal_masalah where notransaksi=?");
			$stmt->execute(array($notransaksi));
			$urut = (int)$stmt->fetchColumn();
			$sql = "insert into " . $dbname . ".lgl_kgpnmodal_masalah
					(notransaksi,urut,tanggal,jenismasalah,detail,keterangan,createby,createtime,updateby,updatetime)
					values (?,?,?,?,?,?,?,?,?,?)";
			$stmt = $owlPDO->prepare($sql);
			$stmt->execute(array($notransaksi, $urut, $mstanggal, $msjenis, $msdetail, $msketerangan, $userid, $now, $userid, $now));
		}
		echo "OK";
		break;

	#--------------------------------------------------------
	# TAB 3e - hapus permasalahan
	#--------------------------------------------------------
	case 'deletemasalalah':
		if ($notransaksi == '' || $idmasalah == '') {
			exit("Warning : Parameter tidak lengkap.");
		}
		kgpnDokumenEdit($notransaksi);
		$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_masalah where id=? and notransaksi=?");
		$stmt->execute(array((int)$idmasalah, $notransaksi));
		echo "OK";
		break;

	#--------------------------------------------------------
	# TAB 4 - POSTING (hanya pembuat transaksi)
	#   guard di WHERE supaya tidak bisa diakali lewat request manual
	#--------------------------------------------------------
	case 'posting':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi kosong.");
		}
		$stmt = $owlPDO->prepare("select posting,createby from " . $dbname . ".lgl_kgpnmodal_ht where notransaksi=?");
		$stmt->execute(array($notransaksi));
		$h = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$h) {
			exit("Warning : Dokumen " . $notransaksi . " tidak ditemukan.");
		}
		if ($h['posting'] == 1) {
			exit("Warning : Dokumen sudah diposting.");
		}
		if ($h['createby'] != $userid) {
			exit("Warning : Hanya pembuat transaksi yang boleh memposting dokumen ini.");
		}
		#validasi minimal sebelum posting
		$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_kgpnmodal_dt where notransaksi=?");
		$stmt->execute(array($notransaksi));
		if ($stmt->fetchColumn() == 0) {
			exit("Warning : Rincian realisasi masih kosong, tidak bisa diposting.");
		}
		$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_kgpnmodal_tk where notransaksi=?");
		$stmt->execute(array($notransaksi));
		if ($stmt->fetchColumn() == 0) {
			exit("Warning : Data tenaga kerja belum diisi, tidak bisa diposting.");
		}
		$sql = "update " . $dbname . ".lgl_kgpnmodal_ht
				   set posting=1, postingby=?, postingdate=?
				 where notransaksi=? and posting=0 and createby=?";
		$stmt = $owlPDO->prepare($sql);
		$stmt->execute(array($userid, $now, $notransaksi, $userid));
		if ($stmt->rowCount() == 0) {
			exit("Warning : Posting gagal. Pastikan Anda pembuat transaksi dan dokumen belum diposting.");
		}
		echo "OK";
		break;

	#--------------------------------------------------------
	# TAB 4b - BATAL POSTING (hanya pembuat transaksi)
	#--------------------------------------------------------
	case 'unposting':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi kosong.");
		}
		$stmt = $owlPDO->prepare("select posting,createby from " . $dbname . ".lgl_kgpnmodal_ht where notransaksi=?");
		$stmt->execute(array($notransaksi));
		$h = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$h) {
			exit("Warning : Dokumen " . $notransaksi . " tidak ditemukan.");
		}
		if ($h['posting'] == 0) {
			exit("Warning : Dokumen belum diposting.");
		}
		if ($h['createby'] != $userid) {
			exit("Warning : Hanya pembuat transaksi yang boleh membatalkan posting dokumen ini.");
		}
		$sql = "update " . $dbname . ".lgl_kgpnmodal_ht
				   set posting=0, postingby=0, postingdate='0000-00-00 00:00:00'
				 where notransaksi=? and posting=1 and createby=?";
		$stmt = $owlPDO->prepare($sql);
		$stmt->execute(array($notransaksi, $userid));
		if ($stmt->rowCount() == 0) {
			exit("Warning : Batal posting gagal.");
		}
		echo "OK";
		break;

	#--------------------------------------------------------
	# SIMPAN SEMUA TAB sekaligus (alur: input dulu, simpan sekali)
	#   satu transaksi DB: header + rincian + tenaga kerja + permasalahan
	#--------------------------------------------------------
	case 'simpanSemua':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi belum terbentuk. Pilih PT, Tahun, dan Triwulan di Tab 1.");
		}
		$err = kgpnValidasiInti();
		if ($err != '') {
			exit(str_replace("Warning : ", "Warning : Tab 1 Identitas - ", $err));
		}
		$err = kgpnValidasiSah();
		if ($err != '') {
			exit(str_replace("Warning : ", "Warning : Tab 5 Pengesahan - ", $err));
		}

		#--- rincian realisasi (Tab 2) ---
		$arrDt = json_decode($dtjson, true);
		if (!is_array($arrDt)) {
			$arrDt = array();
		}
		$dtBersih = array();
		foreach ($arrDt as $r) {
			$j = isset($r['jenis']) ? (int)$r['jenis'] : 0;
			$nk = isset($r['noakun']) ? trim($r['noakun']) : '';
			$ur = isset($r['uraian']) ? trim($r['uraian']) : '';
			$np = isset($r['nilaiperiode']) ? (float)str_replace(',', '', $r['nilaiperiode']) : 0;
			$na = isset($r['nilaiakumulasi']) ? (float)str_replace(',', '', $r['nilaiakumulasi']) : 0;
			$md = isset($r['mode']) ? strtoupper(trim($r['mode'])) : 'TAMBAH';
			if ($j != 1 && $j != 2) {
				exit("Warning : Tab 2 Realisasi - jenis baris harus Modal Tetap atau Modal Kerja.");
			}
			if ($ur == '') {
				exit("Warning : Tab 2 Realisasi - ada baris tanpa uraian.");
			}
			if (strlen($ur) > 255) {
				$ur = substr($ur, 0, 255);
			}
			if ($md != 'TAMBAH' && $md != 'KURANGI' && $md != 'GANTI') {
				exit("Warning : Tab 2 Realisasi - mode baris tidak valid.");
			}
			if ($np < 0 || $na < 0) {
				exit("Warning : Tab 2 Realisasi - nilai tidak boleh negatif (pakai Mode KURANGI).");
			}
			if ($nk != '') {
				if (!preg_match('/^[0-9]{1,16}$/', $nk) || !kgpnAkunValid($nk, $j)) {
					exit("Warning : Tab 2 Realisasi - no akun " . $nk . " tidak valid untuk " . kgpnJenisTeks($j) . ".");
				}
			}
			$dtBersih[] = array($j, $nk, $ur, $np, $na, $md);
		}

		#--- tenaga kerja (Tab 3) ---
		$arrTk = json_decode($tkjson, true);
		if (!is_array($arrTk)) {
			$arrTk = array();
		}
		$tkBersih = array();
		foreach (array('LK', 'PR', 'ASG', 'UNK') as $kat) {
			$v = isset($arrTk[strtolower($kat)]) ? $arrTk[strtolower($kat)] : array();
			$seb = isset($v['sebelum']) ? (int)$v['sebelum'] : 0;
			$tam = isset($v['tambahan']) ? (int)$v['tambahan'] : 0;
			$pen = isset($v['pengurangan']) ? (int)$v['pengurangan'] : 0;
			$sam = isset($v['sampai']) ? (int)$v['sampai'] : 0;
			if ($seb < 0 || $tam < 0 || $pen < 0 || $sam < 0) {
				exit("Warning : Tab 3 Tenaga Kerja - jumlah tidak boleh negatif.");
			}
			$tkBersih[$kat] = array($seb, $tam, $pen, $sam);
		}

		#--- permasalahan (Tab 4) ---
		$arrMs = json_decode($msjson, true);
		if (!is_array($arrMs)) {
			$arrMs = array();
		}
		$msBersih = array();
		foreach ($arrMs as $r) {
			$msTgl = tanggalsystemn(isset($r['tanggal']) ? $r['tanggal'] : '');
			$msJen = isset($r['jenismasalah']) ? trim($r['jenismasalah']) : '';
			$msDet = isset($r['detail']) ? trim($r['detail']) : '';
			$msKet = isset($r['keterangan']) ? trim($r['keterangan']) : '';
			if ($msTgl == '--' || $msTgl == '') {
				exit("Warning : Tab 4 Permasalahan - tanggal melaporkan wajib diisi.");
			}
			if ($msJen == '') {
				exit("Warning : Tab 4 Permasalahan - jenis masalah wajib diisi.");
			}
			if (strlen($msJen) > 200 || strlen($msDet) > 500 || strlen($msKet) > 500) {
				exit("Warning : Tab 4 Permasalahan - isian terlalu panjang (jenis maks 200, detail & keterangan maks 500).");
			}
			$msBersih[] = array($msTgl, $msJen, $msDet, $msKet);
		}

		#--- dokumen baru atau sudah ada ---
		$stmtH = $owlPDO->prepare("select posting,createby from " . $dbname . ".lgl_kgpnmodal_ht where notransaksi=?");
		$stmtH->execute(array($notransaksi));
		$hdr = $stmtH->fetch(PDO::FETCH_ASSOC);
		$baru = ($hdr === false);

		if ($baru) {
			$awalanHarus = str_replace('-', '', $periodedari) . "/KGPN/" . $kodept . "/";
			if (strpos($notransaksi, $awalanHarus) !== 0) {
				exit("Warning : Nomor transaksi tidak sesuai dengan PT / periode yang dipilih. Tutup form lalu buat dokumen baru.");
			}
		} else {
			if ($hdr['posting'] == 1) {
				exit("Warning : Dokumen sudah diposting, tidak bisa diubah.");
			}
		}

		try {
			$owlPDO->beginTransaction();

			if ($baru) {
				$sql = "insert into " . $dbname . ".lgl_kgpnmodal_ht
						(notransaksi,kodept,tahun,triwulan,periodedari,periodesampai,akumulasidari,tanggallapor,
						 kota,namapenandatangan,jabatan,notelepon,email,penjelasan,tklokalsetempat,catatantk,
						 posting,postingby,postingdate,createby,createtime,updateby,updatetime)
						values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,0,'0000-00-00 00:00:00',?,?,?,?)";
				$stmt = $owlPDO->prepare($sql);
				$stmt->execute(array(
					$notransaksi, $kodept, (int)$tahun, (int)$triwulan,
					kgpnTglDb($periodedari), kgpnTglDb($periodesampai), kgpnTglDb($akumulasidari),
					kgpnTglDb($tanggallapor), $kota, $namapenandatangan, $jabatan, $notelepon, $email,
					$penjelasan, $tklokalsetempat, $catatantk,
					$userid, $now, $userid, $now
				));
			} else {
				$sql = "update " . $dbname . ".lgl_kgpnmodal_ht set
						 tahun=?, triwulan=?, periodedari=?, periodesampai=?, akumulasidari=?, tanggallapor=?,
						 kota=?, namapenandatangan=?, jabatan=?, notelepon=?, email=?, penjelasan=?,
						 tklokalsetempat=?, catatantk=?, updateby=?, updatetime=?
						 where notransaksi=? and posting=0";
				$stmt = $owlPDO->prepare($sql);
				$stmt->execute(array(
					(int)$tahun, (int)$triwulan,
					kgpnTglDb($periodedari), kgpnTglDb($periodesampai), kgpnTglDb($akumulasidari),
					kgpnTglDb($tanggallapor), $kota, $namapenandatangan, $jabatan, $notelepon, $email,
					$penjelasan, $tklokalsetempat, $catatantk, $userid, $now, $notransaksi
				));
			}

			#rincian realisasi diganti seluruhnya
			$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_dt where notransaksi=?");
			$stmt->execute(array($notransaksi));
			$stmtIns = $owlPDO->prepare("insert into " . $dbname . ".lgl_kgpnmodal_dt
					(notransaksi,jenis,noakun,uraian,nilaiperiode,nilaiakumulasi,mode,createby,createtime,updateby,updatetime)
					values (?,?,?,?,?,?,?,?,?,?,?)");
			foreach ($dtBersih as $r) {
				$stmtIns->execute(array($notransaksi, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $userid, $now, $userid, $now));
			}

			#tenaga kerja diganti seluruhnya
			$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_tk where notransaksi=?");
			$stmt->execute(array($notransaksi));
			$stmtIns = $owlPDO->prepare("insert into " . $dbname . ".lgl_kgpnmodal_tk
					(notransaksi,kategori,sebelum,tambahan,pengurangan,sampai,updateby,updatetime)
					values (?,?,?,?,?,?,?,?)");
			foreach ($tkBersih as $kat => $v) {
				$stmtIns->execute(array($notransaksi, $kat, $v[0], $v[1], $v[2], $v[3], $userid, $now));
			}

			#permasalahan diganti seluruhnya
			$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_masalah where notransaksi=?");
			$stmt->execute(array($notransaksi));
			$stmtIns = $owlPDO->prepare("insert into " . $dbname . ".lgl_kgpnmodal_masalah
					(notransaksi,urut,tanggal,jenismasalah,detail,keterangan,createby,createtime,updateby,updatetime)
					values (?,?,?,?,?,?,?,?,?,?)");
			$urut = 0;
			foreach ($msBersih as $r) {
				$urut++;
				$stmtIns->execute(array($notransaksi, $urut, $r[0], $r[1], $r[2], $r[3], $userid, $now, $userid, $now));
			}

			#profil PT ikut diperbarui dalam transaksi yang sama
			kgpnSimpanProfil();

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollBack();
			exit("Warning : Gagal menyimpan dokumen. " . $e->getMessage());
		}

		unset($_SESSION['kgpnbaru'][$notransaksi]);
		echo $notransaksi;
		break;

	#--------------------------------------------------------
	# Simpan dokumen baru
	#--------------------------------------------------------
	case 'insert':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi belum ada, pilih PT dan periode terlebih dahulu.");
		}
		$err = kgpnValidasiHeader();
		if ($err != '') {
			exit($err);
		}
		#nomor harus berpola tanggal/PT yang sama dengan isian
		$awalanHarus = str_replace('-', '', $periodedari) . "/KGPN/" . $kodept . "/";
		if (strpos($notransaksi, $awalanHarus) !== 0) {
			exit("Warning : Nomor transaksi tidak sesuai dengan PT / periode yang dipilih. Tutup form lalu buat dokumen baru.");
		}
		$stmtC = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_kgpnmodal_ht where notransaksi=?");
		$stmtC->execute(array($notransaksi));
		if ($stmtC->fetchColumn() > 0) {
			exit("Warning : Nomor " . $notransaksi . " sudah dipakai. Tutup form ini lalu buat dokumen baru.");
		}
		$sql = "insert into " . $dbname . ".lgl_kgpnmodal_ht
				(notransaksi,kodept,tahun,triwulan,periodedari,periodesampai,akumulasidari,tanggallapor,
				 kota,namapenandatangan,jabatan,notelepon,email,penjelasan,tklokalsetempat,catatantk,
				 posting,postingby,postingdate,createby,createtime,updateby,updatetime)
				values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0,0,'0000-00-00 00:00:00',?,?,?,?)";
		try {
			$stmt = $owlPDO->prepare($sql);
			$stmt->execute(array(
				$notransaksi, $kodept, (int)$tahun, (int)$triwulan,
				kgpnTglDb($periodedari), kgpnTglDb($periodesampai), kgpnTglDb($akumulasidari),
				kgpnTglDb($tanggallapor), $kota, $namapenandatangan, $jabatan, $notelepon, $email,
				$penjelasan, $tklokalsetempat, $catatantk,
				$userid, $now, $userid, $now
			));
		} catch (PDOException $e) {
			exit("Warning : Gagal menyimpan header. " . $e->getMessage());
		}
		kgpnSimpanProfil();
		unset($_SESSION['kgpnbaru'][$notransaksi]);
		echo $notransaksi;
		break;

	#--------------------------------------------------------
	# Ubah header dokumen
	#--------------------------------------------------------
	case 'detail':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi kosong.");
		}
		$stmtH = $owlPDO->prepare("select posting,createby from " . $dbname . ".lgl_kgpnmodal_ht where notransaksi=?");
		$stmtH->execute(array($notransaksi));
		$hdr = $stmtH->fetch(PDO::FETCH_ASSOC);
		if (!$hdr) {
			exit("Warning : Dokumen " . $notransaksi . " tidak ditemukan.");
		}
		if ($hdr['posting'] == 1) {
			exit("Warning : Dokumen sudah diposting, tidak bisa diubah.");
		}
		$err = kgpnValidasiHeader();
		if ($err != '') {
			exit($err);
		}
		$sql = "update " . $dbname . ".lgl_kgpnmodal_ht set
				 tahun=?, triwulan=?, periodedari=?, periodesampai=?, akumulasidari=?, tanggallapor=?,
				 kota=?, namapenandatangan=?, jabatan=?, notelepon=?, email=?, penjelasan=?,
				 tklokalsetempat=?, catatantk=?, updateby=?, updatetime=?
				where notransaksi=? and posting=0";
		try {
			$stmt = $owlPDO->prepare($sql);
			$stmt->execute(array(
				(int)$tahun, (int)$triwulan,
				kgpnTglDb($periodedari), kgpnTglDb($periodesampai), kgpnTglDb($akumulasidari),
				kgpnTglDb($tanggallapor), $kota, $namapenandatangan, $jabatan, $notelepon, $email,
				$penjelasan, $tklokalsetempat, $catatantk, $userid, $now, $notransaksi
			));
		} catch (PDOException $e) {
			exit("Warning : Gagal mengubah header. " . $e->getMessage());
		}
		kgpnSimpanProfil();
		echo $notransaksi;
		break;

	#--------------------------------------------------------
	# Hapus dokumen (hanya pembuat, hanya kalau belum posting)
	#--------------------------------------------------------
	case 'delete':
		if ($notransaksi == '') {
			exit("Warning : Nomor transaksi kosong.");
		}
		$stmtH = $owlPDO->prepare("select posting,createby from " . $dbname . ".lgl_kgpnmodal_ht where notransaksi=?");
		$stmtH->execute(array($notransaksi));
		$hdr = $stmtH->fetch(PDO::FETCH_ASSOC);
		if (!$hdr) {
			exit("Warning : Dokumen " . $notransaksi . " tidak ditemukan.");
		}
		if ($hdr['posting'] == 1) {
			exit("Warning : Dokumen sudah diposting, tidak bisa dihapus.");
		}
		if ($hdr['createby'] != $userid) {
			exit("Warning : Hanya pembuat transaksi yang boleh menghapus dokumen ini.");
		}
		try {
			$owlPDO->beginTransaction();
			$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_dt where notransaksi=?");
			$stmt->execute(array($notransaksi));
			$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_tk where notransaksi=?");
			$stmt->execute(array($notransaksi));
			$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_masalah where notransaksi=?");
			$stmt->execute(array($notransaksi));
			$stmt = $owlPDO->prepare("delete from " . $dbname . ".lgl_kgpnmodal_ht where notransaksi=? and posting=0");
			$stmt->execute(array($notransaksi));
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollBack();
			exit("Warning : Gagal menghapus dokumen. " . $e->getMessage());
		}
		echo "OK";
		break;

	#--------------------------------------------------------
	# List dokumen
	#--------------------------------------------------------
	case 'loaddata':
		if ($tipe == 'excel') {
			exit("Warning : Gunakan tombol Excel pada halaman ini.");
		}
		$limit = 20;
		$offset = $page * $limit;
		$where = kgpnWhereList();

		$sqlC = "select count(*) from " . $dbname . ".lgl_kgpnmodal_ht h where 1=1 " . $where;
		$stmtC = $owlPDO->query($sqlC);
		$jlhbrs = (int)$stmtC->fetchColumn();

		$sql = "select h.notransaksi, h.kodept, h.tahun, h.triwulan, h.periodedari, h.periodesampai,
					h.tanggallapor, h.posting, h.postingby, h.postingdate,
					h.createby, h.createtime,
					ifnull((select sum(d.nilaiperiode) from " . $dbname . ".lgl_kgpnmodal_dt d
							where d.notransaksi=h.notransaksi and d.jenis=1),0) totalmt,
					ifnull((select sum(d.nilaiperiode) from " . $dbname . ".lgl_kgpnmodal_dt d
							where d.notransaksi=h.notransaksi and d.jenis=2),0) totalmk,
					ifnull(o.namaorganisasi,'') namaorg
				from " . $dbname . ".lgl_kgpnmodal_ht h
				left join " . $dbname . ".organisasi o on o.kodeorganisasi=h.kodept
				where 1=1 " . $where . "
				order by h.notransaksi desc
				limit " . $offset . "," . $limit;
		$res = $owlPDO->query($sql);
		$res->setFetchMode(PDO::FETCH_ASSOC);

		$tab = "";
		$no = $offset;
		while ($bar = $res->fetch()) {
			$no++;
			$xx = ($no % 2 == 1) ? " style=background-color:#F5EEF8 " : "";
			$tab .= "<tr class=rowcontent " . $xx . " id=tr_" . $no . ">";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=center>" . htmlspecialchars($bar['notransaksi']) . "</td>";
			$tab .= "<td>" . htmlspecialchars($bar['kodept'] . ' - ' . $bar['namaorg']) . "</td>";
			$tab .= "<td align=center>" . kgpnTglView($bar['periodedari']) . " s/d " . kgpnTglView($bar['periodesampai']) . "</td>";
			$tab .= "<td align=center>" . $bar['tahun'] . "</td>";
			$tab .= "<td align=center>Q" . $bar['triwulan'] . "</td>";
			$tab .= "<td align=right>" . number_format($bar['totalmt'], 2) . "</td>";
			$tab .= "<td align=right>" . number_format($bar['totalmk'], 2) . "</td>";

			#status posting
			if ($bar['posting'] == 1) {
				$tab .= "<td align=center><span style='color:#006600;font-weight:bold;'>Sudah Posting</span></td>";
			} else {
				$tab .= "<td align=center><span style='color:#990000;'>Belum Posting</span></td>";
			}

			#jejak audit
			$tab .= "<td>" . htmlspecialchars(kgpnNamaUser($bar['createby'])) . "</td>";
			$tab .= "<td align=center>" . kgpnTglJam($bar['createtime']) . "</td>";
			$tab .= "<td>" . htmlspecialchars(kgpnNamaUser($bar['postingby'])) . "</td>";
			$tab .= "<td align=center>" . kgpnTglJam($bar['postingdate']) . "</td>";

			#aksi: edit + hapus hanya kalau belum posting; posting hanya pembuat
			$aksi = "";
			$pembuat = ($bar['createby'] == $userid);
			if ($bar['posting'] == 0) {
				$aksi .= "<td align=center><img src=images/application/application_edit.png class=resicon title='Edit' "
					. "onclick=\"edit('" . $bar['notransaksi'] . "','" . $bar['kodept'] . "');\" ></td>";
				if ($pembuat) {
					$aksi .= "<td align=center><img src=images/application/application_delete.png class=resicon title='Delete' "
						. "onclick=\"del('" . $bar['notransaksi'] . "');\" ></td>";
					$aksi .= "<td align=center><img src=images/red/posting.png class=resicon title='Posting' "
						. "onclick=\"kgpnPosting('" . $bar['notransaksi'] . "');\" ></td>";
				} else {
					$aksi .= "<td></td><td></td>";
				}
			} else {
				$aksi .= "<td></td><td></td>";
				if ($pembuat) {
					$aksi .= "<td align=center><img src=images/icons/04/16/04.png class=resicon title='Batal Posting' "
						. "onclick=\"kgpnUnposting('" . $bar['notransaksi'] . "');\" ></td>";
				} else {
					$aksi .= "<td align=center><img src=images/icons/04/16/02.png class=resicon title='Posted'></td>";
				}
			}
			$tab .= $aksi;
			$tab .= "</tr>";
		}

		if ($no == $offset) {
			$tab .= "<tr class=rowcontent><td colspan=16 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}

		#footer paging
		$totrows = (int)ceil($jlhbrs / $limit);
		if ($totrows < 1) {
			$totrows = 1;
		}
		$footd = "";
		$footd .= "<tr><td colspan=16 align=center>";
		if ($page == 0) {
			$footd .= "<button class=mybutton disabled=true>Prev</button>";
		} else {
			$footd .= "<button class=mybutton onclick=loaddata(" . ($page - 1) . ")>Prev</button>";
		}
		$footd .= " Page <select class=myinputtext onchange=loaddata(this.value)>";
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$footd .= "<option value='" . ($er - 1) . "' " . $sel . ">" . $er . "</option>";
		}
		$footd .= "</select> of " . $totrows;
		if ($page >= $totrows - 1) {
			$footd .= " <button class=mybutton disabled=true>Next</button>";
		} else {
			$footd .= " <button class=mybutton onclick=loaddata(" . ($page + 1) . ")>Next</button>";
		}
		$footd .= "</td></tr>";
		$footd .= "<tr><td colspan=16 align=right><i>Total dokumen: " . $jlhbrs . "</i></td></tr>";

		echo $tab . "#####" . $footd;
		break;

	default:
		exit("Warning : Method tidak dikenal.");
}
?>
