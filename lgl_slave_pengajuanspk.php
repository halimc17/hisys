<?php
ini_set('display_errors',0);error_reporting(0);
$mobileValid = false;
if(isset($_POST['par']) || isset($_GET['par'])){
	$parSPK = isset($_POST['par']) ? $_POST['par'] : $_GET['par'];
	$validasiPostMobile = explode(" ", is_string($parSPK) ? $parSPK : '');
	#jalur internal hanya sah bila file ini di-include skrip server lain (mis. generatepdf.php), bukan dipanggil langsung oleh klien
	if($validasiPostMobile[0] == "owlApp" && basename($_SERVER['SCRIPT_FILENAME']) != basename(__FILE__)){
		$mobileValid = true;
		$session_id = '';
	};
}

if($mobileValid == false){//untuk redirec dari mobile
	require_once('master_validation.php');
}else{
	if(!empty($_POST['namafile']) || !empty($_GET['namafile'])){		
		$str="select legend,ID from ".$dbname.".bahasa order by legend";
		$res=fetchdata($str);
		foreach($res as $bar){
			$_SESSION['lang'][$bar['legend']]=$bar['ID'];
		}
	}
}
// echo"<pre>";
// print_r($_GET);
// print_r($_POST);
// exit("error");

require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$method             = checkPostGet('method', '');
$param              = $_POST;
$tipe               = checkPostGet('tipe', '');
$jenis              = checkPostGet('jenis', '');
$notransaksi        = checkPostGet('notransaksi', '');
$rupiah             = checkPostGet('rupiah', '');
$rupiah             = str_replace(',','',$rupiah);
$keterangan         = checkPostGet('keterangan', '');
$nourut             = checkPostGet('nourut', '');
$row                = checkPostGet('row', '');
$pt                 = checkPostGet('pt', '');
$unit               = checkPostGet('unit', '');
$kategori           = checkPostGet('kategori', '');
$tanggalsurat       = tanggalsystemn(checkPostGet('tanggalsurat', ''));
$tanggaldari        = tanggalsystemn(checkPostGet('tanggaldari', ''));
$tanggalsampai      = tanggalsystemn(checkPostGet('tanggalsampai', ''));
$tanggal            = tanggalsystemn(checkPostGet('tanggal', ''));
$divisi             = checkPostGet('divisi', '');
$bagian             = checkPostGet('bagian', '');
$project            = checkPostGet('project', '');
$koderekanan        = checkPostGet('koderekanan', '');
$perjanjianinduk    = checkPostGet('perjanjianinduk', '');
$perjanjianperubahan= checkPostGet('perjanjianperubahan', '');
$retensi            = checkPostGet('retensi', '');
$denda              = checkPostGet('denda', '');
$jangkawaktu        = checkPostGet('jangkawaktu', '');
$garansi            = checkPostGet('garansi', '');
$namafile           = checkPostGet('namafile', '');
$divsch             = checkPostGet('divsch', '');
$jenissch           = checkPostGet('jenissch', '');
$nohaksch           = checkPostGet('nohaksch', '');
$unitsch            = checkPostGet('unitsch', '');
$projectsch         = checkPostGet('projectsch', '');
$koderekanansch     = checkPostGet('koderekanansch', '');
$statussch          = checkPostGet('statussch', '');
$subunit            = checkPostGet('subunit', '');
$kegiatan           = checkPostGet('kegiatan', '');
$total              = checkPostGet('total', '');
$hk                 = checkPostGet('hk', '');
$volume             = checkPostGet('volume', '');
$satuan             = checkPostGet('satuan', '');
$kepada             = checkPostGet('kepada', '');
$numrow             = checkPostGet('numrow', '');
$rptermin           = checkPostGet('rptermin', '');
$jenissupplier      = checkPostGet('jenissupplier', '');
$nopol              = checkPostGet('nopol', '');
$tipeangkut         = checkPostGet('tipeangkut', '');
$pendukung          = checkPostGet('pendukung', '');
$alasanclose        = checkPostGet('alasanclose', '');
$jenisupload        = checkPostGet('jenisupload', '');
$rupiahttl        	= checkPostGet('rupiahttl', '');
$jlhhm        		= checkPostGet('jlhhm', '');
$notransaksiold     = checkPostGet('notransaksiold', '');
$rptermin           = str_replace(',','',$rptermin);
$rupiahttl          = str_replace(',','',$rupiahttl);
$param['nilaipajak']= str_replace(',','',$param['nilaipajak']);
$volume             = str_replace(',','',$volume);
$total              = str_replace(',','',$total);
$path               = "fileupload/lgl_pengajuanspk/";
$today              = date('Y-m-d');
$todayhis           = date('Y-m-d H:i:s');
$spesifikasi        = trim(checkPostGet('spesifikasi', ''));

$kriteriaefil = checkPostGet('kriteriaefil', '0');
$urlefil = checkPostGet('urlefil', '0');

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmakun= makeOption($dbname, 'keu_5akun', 'noakun,namaakun');

$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
$emodul = "SPK";
$jnsupspkfinal='ESPKF';
$maksUpload = 10 * 1024 * 1024; #batas ukuran file upload (byte)
$formatUpload = array('.jpeg', '.jpg', '.png', '.pdf', '.xls', '.xlsx', '.doc', '.docx');

#=== Pengamanan: format parameter, hak akses unit, dan status ===
#jalur internal (par=owlApp) hanya untuk tampilan SPK
if ($mobileValid && $method != 'html') {
	exit("Warning : Akses tidak diizinkan.");
}

#format parameter diperiksa sebelum dipakai di query (kosong boleh)
$polaId    = '/^[A-Za-z0-9\/\-_\.]{1,40}$/';
$polaOrg   = '/^[A-Za-z0-9]{1,6}$/';
$polaKode  = '/^[A-Za-z0-9\/\-_\. ]{1,50}$/';
$polaJenis = '/^(HOLDING|KANWIL|KEBUN|PABRIK|PO\/SO|PROJECT|SEWA\.HM|ANGKUTTBS|UMUM|JUALTBS)$/';
$polaSPK = array(
	'notransaksi'         => $polaId,
	'notransaksiold'      => $polaId,
	'perjanjianinduk'     => $polaId,
	'perjanjianperubahan' => $polaId,
	'pt'                  => $polaOrg,
	'divsch'              => $polaOrg,
	'unit'                => $polaOrg,
	'unitsch'             => $polaOrg,
	'kategori'            => '/^(LOKAL|PUSAT)$/',
	'jenis'               => $polaJenis,
	'jenissch'            => $polaJenis,
	'divisi'              => $polaKode,
	'bagian'              => $polaKode,
	'subunit'             => $polaKode,
	'kegiatan'            => $polaKode,
	'koderekanan'         => '/^[A-Za-z0-9\/\-_]{1,32}$/',
	'jenissupplier'       => '/^[A-Za-z0-9 _\-\.\(\)]{1,32}$/',
	'pendukung'           => '/^[01]$/',
	'statussch'           => '/^[0-9]$/',
	'jenisupload'         => '/^[0-9]$/',
	'row'                 => '/^[0-9]{1,6}$/',
	'numrow'              => '/^[0-9]{1,6}$/',
	'kepada'              => '/^[0-9]{1,10}$/',
	'tipe'                => '/^[A-Za-z]{1,10}$/',
	'kriteriaefil'        => '/^[A-Za-z0-9_]{1,20}$/'
);
foreach ($polaSPK as $kunciSPK => $polaX) {
	$nilaiX = $$kunciSPK;
	#JS lama kadang mengirim teks undefined/null untuk nilai yang tidak ada; dianggap kosong
	if ($nilaiX === 'undefined' || $nilaiX === 'null') {
		$nilaiX = $$kunciSPK = '';
	}
	if ($nilaiX !== '' && (!is_string($nilaiX) || !preg_match($polaX, $nilaiX))) {
		exit("Warning : Parameter " . $kunciSPK . " tidak valid.");
	}
}

#angka: koma ribuan dibuang, sisanya harus angka
foreach (array('rupiah', 'rptermin', 'rupiahttl', 'volume', 'total', 'hk', 'jlhhm') as $kunciSPK) {
	$$kunciSPK = is_string($$kunciSPK) ? str_replace(',', '', $$kunciSPK) : $$kunciSPK;
	if ($$kunciSPK !== '' && (!is_string($$kunciSPK) || !preg_match('/^-?[0-9]+(\.[0-9]+)?$/', $$kunciSPK))) {
		exit("Warning : Nilai " . $kunciSPK . " harus berupa angka.");
	}
}

#tanggal (hasil konversi tanggalsystemn; kosong menjadi --) harus berformat YYYY-MM-DD
foreach (array('tanggalsurat', 'tanggaldari', 'tanggalsampai', 'tanggal') as $kunciSPK) {
	if ($$kunciSPK !== '' && $$kunciSPK !== '--' && (!is_string($$kunciSPK) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $$kunciSPK))) {
		exit("Warning : Format tanggal tidak valid.");
	}
}

#tarikan efil hanya boleh menulis file .xls di folder efillinv
if ($urlefil !== '0' && (!is_string($urlefil) || !preg_match('#^fileupload/efillinv/[^\x00-\x1f\\\\:*?"<>|]+\.xls$#', $urlefil) || strpos($urlefil, '..') !== false)) {
	exit("Warning : Parameter urlefil tidak valid.");
}

#teks bebas: karakter khusus SQL di-escape sekali di sini, query di bawah memakai nilai ini
$namafileAsli = is_string($namafile) ? $namafile : '';
$spkRaw = array('projectsch' => is_string($projectsch) ? $projectsch : '', 'koderekanansch' => is_string($koderekanansch) ? $koderekanansch : '', 'nohaksch' => is_string($nohaksch) ? $nohaksch : '');
foreach (array('keterangan', 'project', 'satuan', 'spesifikasi', 'retensi', 'denda', 'garansi', 'jangkawaktu', 'alasanclose', 'projectsch', 'koderekanansch', 'nohaksch', 'tipeangkut', 'nopol', 'namafile') as $kunciSPK) {
	$$kunciSPK = is_string($$kunciSPK) ? substr($owlPDO->quote($$kunciSPK), 1, -1) : '';
}

if (!function_exists('spkHeader')) {
	#nama sub unit untuk daftar detail (kendaraan, organisasi, project, blok, atau barang)
	function spkNamaSubunit($kode)
	{
		global $owlPDO, $dbname, $nmorg;
		static $cache = array();
		if (isset($cache[$kode])) {
			return $cache[$kode];
		}
		$nama = '';
		if (getNopol($kode, 'kodevhc') != '') {
			$nama = getNopol($kode);
		} elseif (isset($nmorg[$kode]) && $nmorg[$kode] != '') {
			$nama = $nmorg[$kode];
		} else {
			$stmt = $owlPDO->prepare("select nama from " . $dbname . ".project where kode=?");
			$stmt->execute(array($kode));
			$nama = (string)$stmt->fetchColumn();
			if ($nama == '') {
				$nama = spkNamaBlok($kode);
			}
			if ($nama == '') {
				$stmt = $owlPDO->prepare("select namabarang from " . $dbname . ".log_5masterbarang where kodebarang=?");
				$stmt->execute(array($kode));
				$nama = (string)$stmt->fetchColumn();
			}
		}
		$cache[$kode] = $kode . ' - ' . $nama;
		return $cache[$kode];
	}

	#nama kegiatan untuk daftar detail (master kegiatan, atau detail project / capex)
	function spkNamaKegiatan($kegiatan, $subunit)
	{
		global $owlPDO, $dbname;
		$stmt = $owlPDO->prepare("select namakegiatan from " . $dbname . ".setup_kegiatan where kodekegiatan=?");
		$stmt->execute(array($kegiatan));
		$nama = (string)$stmt->fetchColumn();
		if ($nama == '') {
			$stmt = $owlPDO->prepare("select kodecapex from " . $dbname . ".project where kode=?");
			$stmt->execute(array($subunit));
			$kodecapex = (string)$stmt->fetchColumn();
			$tabel = ($kodecapex == '') ? 'project_dt' : 'spl_capexbangunandt';
			$stmt = $owlPDO->prepare("select namakegiatan from " . $dbname . "." . $tabel . " where kegiatan=?");
			$stmt->execute(array($kegiatan));
			$nama = (string)$stmt->fetchColumn();
		}
		return $kegiatan . ' - ' . $nama;
	}

	#seluruh isi dokumen satu SPK untuk tarikan PDF/Excel resmi (hak akses dicek lewat spkHeader)
	function spkDataDokumen($notransaksi)
	{
		global $owlPDO, $dbname, $nmorg, $nmkar, $nmakun;
		$hdr = spkHeader($notransaksi, 'baca');
		$d = array('hdr' => $hdr);
		$d['pt'] = isset($nmorg[$hdr['pt']]) ? $nmorg[$hdr['pt']] : $hdr['pt'];
		$d['unit'] = isset($nmorg[$hdr['unit']]) ? $nmorg[$hdr['unit']] : $hdr['unit'];
		$stmt = $owlPDO->prepare("select namasupplier from " . $dbname . ".log_5supplier where supplierid=?");
		$stmt->execute(array($hdr['koderekanan']));
		$d['rekanan'] = (string)$stmt->fetchColumn();
		if ($d['rekanan'] == '') {
			$stmt = $owlPDO->prepare("select namacustomer from " . $dbname . ".pmn_4customer where kodecustomer=?");
			$stmt->execute(array($hdr['koderekanan']));
			$d['rekanan'] = (string)$stmt->fetchColumn();
		}
		$stmt = $owlPDO->prepare("select kode from " . $dbname . ".log_5klsupplier where tipe=?");
		$stmt->execute(array($hdr['jenissupplier']));
		$d['klsup'] = (string)$stmt->fetchColumn();
		$tgl = function ($x) {
			return ($x == '' || $x == '0000-00-00') ? '' : tanggalnormal($x);
		};
		$d['tanggal'] = $tgl($hdr['tanggal']);
		$d['periode'] = ($hdr['tanggaldari'] == '0000-00-00' && $hdr['tanggalsampai'] == '0000-00-00') ? '' : $tgl($hdr['tanggaldari']) . ' s/d ' . $tgl($hdr['tanggalsampai']);
		//nilai kontrak dan pajak
		$d['nilai'] = 0;
		$d['pajak'] = array();
		$stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_pengajuanspkdt where notransaksi=? order by tipe, nourut");
		$stmt->execute(array($notransaksi));
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			if ($r['tipe'] == 'rupiah') {
				$d['nilai'] += floatval($r['nilai']);
			}
			if ($r['tipe'] == 'pajak') {
				$d['pajak'][] = array('akun' => $r['nourut'], 'nama' => isset($nmakun[$r['nourut']]) ? $nmakun[$r['nourut']] : '', 'persen' => floatval($r['nilai']));
			}
		}
		//detail kegiatan
		$d['keg'] = array();
		$stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_pengajuanspk_keg where notransaksi=?");
		$stmt->execute(array($notransaksi));
		$d['totalkeg'] = 0;
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$vol = floatval($r['volume']);
			$tot = floatval($r['total']);
			$d['keg'][] = array('subunit' => spkNamaSubunit($r['subunit']), 'kegiatan' => spkNamaKegiatan($r['kegiatan'], $r['subunit']), 'satuan' => $r['satuan'], 'hk' => floatval($r['hk']), 'volume' => $vol, 'rpsat' => $vol > 0 ? $tot / $vol : 0, 'total' => $tot);
			$d['totalkeg'] += $tot;
		}
		//kendaraan (angkut TBS)
		$d['nopol'] = array();
		if ($hdr['jenis'] == 'ANGKUTTBS') {
			$stmt = $owlPDO->prepare("select * from " . $dbname . ".log_spknopol where notransaksi=? order by nopol");
			$stmt->execute(array($notransaksi));
			foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
				$d['nopol'][] = array('nopol' => $r['nopol'], 'supir' => $r['supir'], 'status' => ($r['status'] == 'A' ? 'Aktif' : 'Nonaktif'));
			}
		}
		//pembuat dan persetujuan
		$d['dibuat'] = array('nama' => isset($nmkar[$hdr['updateby']]) ? $nmkar[$hdr['updateby']] : '', 'waktu' => ($hdr['createtime'] == '0000-00-00 00:00:00' ? '' : waktunormal($hdr['createtime'])));
		$d['approval'] = array();
		$stmt = $owlPDO->prepare("select a.level,a.karyawanid,a.status,a.komentar,a.tanggal,b.namakaryawan,j.namajabatan from " . $dbname . ".approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid left join " . $dbname . ".sdm_5jabatan j on b.kodejabatan=j.kodejabatan where a.notransaksi=? and a.jenispersetujuan='SPK' order by a.level");
		$stmt->execute(array($notransaksi));
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$tglApp = ($r['status'] != '0' && $r['tanggal'] != '' && $r['tanggal'] != '0000-00-00 00:00:00') ? date('d-m-Y H:i', strtotime($r['tanggal'])) : '';
			$d['approval'][] = array('level' => $r['level'], 'nama' => (string)$r['namakaryawan'], 'jabatan' => (string)$r['namajabatan'], 'status' => $r['status'], 'tanggal' => $tglApp, 'komentar' => (string)$r['komentar']);
		}
		return $d;
	}

	#angka tanpa nol di belakang koma yang tidak perlu
	function spkAngka($v, $dec = 2)
	{
		$s = number_format($v, $dec, '.', ',');
		return ($dec > 0 && strpos($s, '.') !== false) ? rtrim(rtrim($s, '0'), '.') : $s;
	}
	#filter daftar SPK (dipakai daftar, Excel, dan PDF supaya isinya sama); hanya unit yang boleh diakses user
	function spkWhereList()
	{
		global $projectsch, $koderekanansch, $divsch, $jenissch, $unitsch, $nohaksch, $statussch, $dbname;
		$where = " and unit in (" . getOrgDetail(2) . ")";
		if ($projectsch != '') {
			$where .= " and project like '%" . $projectsch . "%' ";
		}
		if ($koderekanansch != '') {
			$where .= " and koderekanan in (select supplierid from " . $dbname . ".log_5supplier where namasupplier like '%" . $koderekanansch . "%') ";
		}
		if ($divsch != '') {
			$where .= " and pt='" . $divsch . "' ";
		}
		if ($jenissch != '') {
			$where .= " and jenis='" . $jenissch . "' ";
		}
		if ($unitsch != '') {
			$where .= " and unit='" . $unitsch . "' ";
		}
		if ($nohaksch != '') {
			$where .= " and notransaksi like '%" . $nohaksch . "%' ";
		}
		if ($statussch != '') {
			$where .= " and statuspersetujuan='" . $statussch . "' ";
		}
		return $where;
	}

	#teks status SPK tanpa tag HTML (sama dengan kolom Status di daftar)
	function spkLabelStatus($r)
	{
		global $arrHsl;
		if ($r['statuspersetujuan'] == 0 && $r['posting'] == 0) {
			$s = 'Belum diajukan';
		} else {
			$s = isset($arrHsl[$r['statuspersetujuan']]) ? strip_tags($arrHsl[$r['statuspersetujuan']]) : '';
		}
		if ($r['close'] == 1) {
			$s .= ' (CLOSE)';
		}
		return $s;
	}

	#keterangan filter yang aktif untuk kop tarikan
	function spkInfoFilter()
	{
		global $nmorg, $arrHsl, $unitsch, $divsch, $jenissch, $statussch, $spkRaw;
		$info = array();
		$info[] = 'Unit: ' . ($unitsch != '' ? $unitsch . (isset($nmorg[$unitsch]) ? ' - ' . $nmorg[$unitsch] : '') : 'Semua');
		$info[] = 'Jenis: ' . ($jenissch != '' ? $jenissch : 'Semua');
		$info[] = 'Status: ' . ($statussch != '' && isset($arrHsl[$statussch]) ? strip_tags($arrHsl[$statussch]) : 'Semua');
		if ($divsch != '') {
			$info[] = 'PT: ' . $divsch;
		}
		if ($spkRaw['projectsch'] != '') {
			$info[] = 'Project: ' . $spkRaw['projectsch'];
		}
		if ($spkRaw['koderekanansch'] != '') {
			$info[] = 'Rekanan: ' . $spkRaw['koderekanansch'];
		}
		if ($spkRaw['nohaksch'] != '') {
			$info[] = 'Nomor: ' . $spkRaw['nohaksch'];
		}
		return implode(' | ', $info);
	}

	#seluruh SPK sesuai filter untuk tarikan Excel/PDF
	function spkDataList()
	{
		global $owlPDO, $dbname;
		$rows = fetchdata("SELECT h.*,
				(SELECT SUM(d.nilai) FROM " . $dbname . ".lgl_pengajuanspkdt d WHERE d.notransaksi=h.notransaksi AND d.tipe='rupiah') AS nilaispk,
				COALESCE((SELECT s.namasupplier FROM " . $dbname . ".log_5supplier s WHERE s.supplierid=h.koderekanan LIMIT 1),
						 (SELECT c.namacustomer FROM " . $dbname . ".pmn_4customer c WHERE c.kodecustomer=h.koderekanan LIMIT 1), '') AS namarekanan,
				(SELECT k.notransaksi FROM " . $dbname . ".log_spkht k WHERE k.nopengajuan=h.notransaksi LIMIT 1) AS nospk
			FROM " . $dbname . ".lgl_pengajuanspkht h WHERE 1=1 " . spkWhereList() . " ORDER BY h.createtime DESC");
		$daftarSpk = array();
		foreach ($rows as $r) {
			if ($r['nospk'] != '') {
				$daftarSpk[] = $owlPDO->quote($r['nospk']);
			}
		}
		$adaBapp = array();
		if (count($daftarSpk) > 0) {
			foreach (fetchdata("SELECT notransaksi FROM " . $dbname . ".log_baspk WHERE notransaksi IN (" . implode(',', $daftarSpk) . ")") as $b) {
				$adaBapp[$b['notransaksi']] = true;
			}
		}
		foreach ($rows as $n => $r) {
			$rows[$n]['adabapp'] = ($r['nospk'] != '' && isset($adaBapp[$r['nospk']]));
		}
		return $rows;
	}

	#teks yang kepanjangan dipotong dan diberi "..." supaya tidak tumpang tindih ke kolom sebelah
	function spkFit($pdf, $txt, $w, $f = 7)
	{
		$txt = utf8_decode($txt);
		$pdf->SetFont('Arial', '', $f);
		if ($pdf->GetStringWidth($txt) <= $w - 2) {
			return $txt;
		}
		while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 2) {
			$txt = substr($txt, 0, -1);
		}
		return rtrim($txt) . '...';
	}
	#nama blok (kode induk blok -> nama) untuk kolom Sub Unit pada daftar detail
	function spkNamaBlok($kode)
	{
		global $owlPDO, $dbname;
		static $cache = array();
		if (!isset($cache[$kode])) {
			$stmt = $owlPDO->prepare("select namaindukblok from " . $dbname . ".organisasi where indukblok=? limit 1");
			$stmt->execute(array($kode));
			$nama = $stmt->fetchColumn();
			$cache[$kode] = $nama ? $nama : '';
		}
		return $cache[$kode];
	}
	#header SPK + cek hak akses unit. $mode 'baca' juga mengizinkan penyetuju SPK; $harusEdit = belum diajukan; $bolehKosong = header belum ada tidak dianggap salah
	function spkHeader($notransaksi, $mode = 'ubah', $harusEdit = false, $bolehKosong = false)
	{
		global $owlPDO, $dbname, $mobileValid;
		$stmt = $owlPDO->prepare("select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi=?");
		$stmt->execute(array($notransaksi));
		$hdr = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$hdr) {
			if ($bolehKosong) {
				return null;
			}
			exit("Warning : Data SPK tidak ditemukan.");
		}
		if ($mobileValid) {
			return $hdr;
		}
		$unitAkses = getOrgDetail(1);
		$boleh = isset($unitAkses[$hdr['unit']]);
		if (!$boleh && $mode == 'baca') {
			$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".approval where notransaksi=? and karyawanid=?");
			$stmt->execute(array($notransaksi, $_SESSION['standard']['userid']));
			$boleh = $stmt->fetchColumn() > 0;
		}
		if (!$boleh) {
			exit("Warning : Unit " . $hdr['unit'] . " tidak sesuai hak akses Anda.");
		}
		if ($harusEdit && ($hdr['posting'] != '0' || $hdr['statuspersetujuan'] != '0')) {
			exit("Warning : SPK ini sudah diajukan atau disetujui, tidak dapat diubah.");
		}
		return $hdr;
	}

	#PT dan unit harus terisi, unit sesuai hak akses user, dan unit milik PT tersebut
	function spkCekPtUnit($pt, $unit)
	{
		global $owlPDO, $dbname;
		if ($pt == '' || $unit == '') {
			exit("Warning : Kode PT dan Kode Unit wajib diisi.");
		}
		$unitAkses = getOrgDetail(1);
		if (!isset($unitAkses[$unit])) {
			exit("Warning : Unit " . $unit . " tidak sesuai hak akses Anda.");
		}
		$stmt = $owlPDO->prepare("select induk from " . $dbname . ".organisasi where kodeorganisasi=?");
		$stmt->execute(array($unit));
		if ($stmt->fetchColumn() !== $pt) {
			exit("Warning : Unit " . $unit . " bukan bagian dari PT " . $pt . ".");
		}
	}

	#kategori mengikuti lokasi tugas: Holding hanya PUSAT, selain itu hanya LOKAL
	function spkCekKategori($kategori)
	{
		$holding = ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING');
		if (($holding && $kategori != 'PUSAT') || (!$holding && $kategori != 'LOKAL')) {
			exit("Warning : Kategori SPK " . $kategori . " tidak sesuai lokasi tugas Anda.");
		}
	}

	#nilai SPK = jumlah total kegiatan
	function spkNilai($notransaksi)
	{
		global $owlPDO, $dbname;
		$stmt = $owlPDO->prepare("select sum(total) from " . $dbname . ".lgl_pengajuanspk_keg where notransaksi=?");
		$stmt->execute(array($notransaksi));
		return floatval($stmt->fetchColumn());
	}

	#calon penyetuju level 1: approver project bila ada, selain itu setup approval unit sesuai rentang nilai
	function spkDaftarPenyetuju($hdr, $nilai)
	{
		global $owlPDO, $dbname;
		$idSendiri = $_SESSION['standard']['userid'];
		$hasil = array();
		$kodeunit = $hdr['unit'];
		if ($hdr['kategori'] == 'PUSAT' && $hdr['divisi'] == '') {
			$stmt = $owlPDO->prepare("select kodeorganisasi from " . $dbname . ".organisasi where induk=? and tipe='HOLDING'");
			$stmt->execute(array($hdr['pt']));
			$holding = $stmt->fetchColumn();
			if ($holding) {
				$kodeunit = $holding;
			}
		}
		if ($hdr['divisi'] != '') {
			$stmt = $owlPDO->prepare("select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from " . $dbname . ".project_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!=? and a.level='1' and a.kode=? order by b.namakaryawan asc");
			$stmt->execute(array($idSendiri, $hdr['divisi']));
			foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
				$hasil[$r['karyawanid']] = $r['namakaryawan'] . " [" . $r['lokasitugas'] . "]";
			}
			if (count($hasil) > 0) {
				return $hasil;
			}
		}
		$stmt = $owlPDO->prepare("select distinct a.karyawanid,a.nilaidari,a.nilaisampai,b.namakaryawan,b.lokasitugas from " . $dbname . ".setup_approval a left join " . $dbname . ".datakaryawan b on a.karyawanid=b.karyawanid where a.karyawanid!=? and a.jenispersetujuan='SPK' and a.level='1' and a.kodeunit=? order by b.namakaryawan asc");
		$stmt->execute(array($idSendiri, $kodeunit));
		foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
			if ($r['nilaidari'] == '0' && $r['nilaisampai'] == '0') {
				$hasil[$r['karyawanid']] = $r['namakaryawan'] . " [" . $r['lokasitugas'] . "]";
			} elseif ($r['nilaidari'] < $nilai && $nilai < $r['nilaisampai']) {
				$hasil[$r['karyawanid']] = $r['namakaryawan'] . " [" . $r['lokasitugas'] . "]";
			}
		}
		return $hasil;
	}
}
#=== akhir pengamanan ===

switch ($method) {
	case'jumlahhari':
			$date1 = $tanggaldari;
			$date2 = $tanggalsampai;
			$a = datediff($date1, $date2);
			echo @$a[years]." tahun, ".@$a[months]." bulan, ".@$a[days]." hari";
		break;
		case'getnotransaksi':
			#form belum lengkap (mis. baru ganti jenis): nomor dikosongkan tanpa pesan
			if ($pt == '' || $unit == '' || $tanggalsurat == '' || $tanggalsurat == '--') {
				exit();
			}
			spkCekPtUnit($pt, $unit);
			#001/EXT/LGL/BOD/BJHO/IX/2017
			$tempPrd=explode('-',$tanggalsurat);
			$str=" select notransaksi from ".$dbname.".lgl_pengajuanspkht where pt='".$pt."' and unit='".$unit."' and tanggal like '".$tempPrd[0]."%' order by notransaksi desc limit 1 "; //exit('error'.$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$tempNo1=explode('/',$bar['notransaksi']);
			if(intval($bar['notransaksi'])==0 or intval($bar['notransaksi'])==999){
				$nomorsurat = "001";
			}else{
				$nomorsurat = addZero(intval($tempNo1[0])+1,3);
			}
			$_SESSION['pajak']=array();
			$_SESSION['nopol']=array();
	$nomorBaruSPK = $nomorsurat."/SPK/".$pt."/".$unit."/".romawi($tempPrd[1])."/".$tempPrd[0];
	#nomor dicatat untuk sesi ini; kalau saat disimpan nomor sudah dipakai SPK lain, simpan ditolak
	$_SESSION['spkbaru'][$nomorBaruSPK] = 1;
	$_SESSION['pajak_untuk'] = $nomorBaruSPK;
	$_SESSION['nopol_untuk'] = $nomorBaruSPK;
	echo $nomorBaruSPK;
	break;
	case'getjenis':
		$optjns="<option value=''></option>";
		$arrtipe=getEnum($dbname,'lgl_pengajuanspkht','jenis');
		foreach( $arrtipe as $key => $val){
			
			if($_SESSION['empl']['tipelokasitugas'] == 'HOLDING'){
				if($val=='HOLDING'){
					$optjns.="<option value=".$val.">".$val."</option>";
				}
			}elseif($_SESSION['empl']['tipelokasitugas'] == 'KANWIL'){
				if($val=='KANWIL'){
					$optjns.="<option value=".$val.">".$val."</option>";
				}
			}elseif($_SESSION['empl']['tipelokasitugas'] == 'KEBUN'){
				if($val=='KEBUN'){
					$optjns.="<option value=".$val.">".$val."</option>";
				}
			}elseif($_SESSION['empl']['tipelokasitugas'] == 'PABRIK'){
				if($val=='PABRIK' and $val!='BELITBS'){
					$optjns.="<option value=".$val.">".$val."</option>";
				}
			}


			if($val!='PABRIK' and $val!='HOLDING' and $val!='KANWIL' and $val!='KEBUN' and $val!='BELITBS'){
				$optjns.="<option value=".$val.">".$val."</option>";
			}
		}
	echo $optjns;
	break;
	case'getunit':
	$where = $whp = ''; $isi = '';
	if ($jenis != '') {
		if ($_SESSION['empl']['tipelokasitugas'] == 'HOLDING' or $_SESSION['empl']['tipelokasitugas'] == 'KANWIL') {
			if ($jenis =='KANWIL' or $jenis =='KEBUN' or $jenis =='PABRIK') {
				$where.=" and tipe ='".$jenis."'";
			}else{
				$where.=" and tipe in ('PABRIK','KEBUN','KANWIL','HOLDING')";
			}
		} else {
			if ($jenis =='KANWIL' or $jenis =='KEBUN' or $jenis =='PABRIK') {
				$where.=" and tipe ='".$jenis."' and kodeorganisasi = '".$_SESSION['empl']['lokasitugas']."'";
			}else{
				#$where.=" and tipe = '".$_SESSION['empl']['tipelokasitugas']."'";
				$where.=" and kodeorganisasi in (".getOrgDetail(2).")";
			}
		}
	}

	#exit("error".$isi);
	$optun="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."' and length(kodeorganisasi)=4 ".$where." order by namaorganisasi asc "; #exit('error'.$str);
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar=$res->fetch()){
		$sel='';
		if($_SESSION['empl']['lokasitugas']==$bar['kodeorganisasi']){
			#$sel=" selected ";
		}
		$optun.="<option ".$sel." value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
	}
	echo $optun;
	break; 
	case'getdivisi':
	$optdiv=$optspk="<option value=''></option>";
	if($jenis=='PO/SO'){
		$str="select a.nopo from ".$dbname.".log_podt a left join ".$dbname.".log_poht b on a.nopo=b.nopo where b.stat_release='1' and a.spk='1' and b.nopo not in (select divisi from ".$dbname.".lgl_pengajuanspkht where jenis='PO/SO' and statuspersetujuan!='3')";
		// $str="select * from ".$dbname.".log_prapo_vw where kodeorg='".$pt."' and nopp like '%".$unit."%' and close='2' and nopp not in (select distinct(nopp) from log_podt) order by nopp asc "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optdiv.="<option value=".$bar['nopo'].">".$bar['nopo']."</option>";
		}
	}elseif($jenis=='PROJECT'){
		$str="select * from ".$dbname.".project where kodeorg='".$unit."' and posting='0' and pekerjaan='External'  order by kode asc"; 
		$count=fetchData($str);
		if(count($count)<=0){
			// exit('Warning : Silahkan buat project terlebih dahulu !');
		}
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optdiv.="<option value=".$bar['kode'].">".$bar['kode']." - ".$bar['nama']."</option>";
		}
		// $optdiv="<option value='PROJECT'>Project</option>";
	}else{
		$wh='';
		if($jenis=='KEBUN'){
			$wh.=" and (tipe ='AFDELING' and induk='".$unit."') or (kodeorganisasi='".$unit."') ";
		}else if($jenis=='PABRIK'){
			$wh.=" and tipe ='STATION' and induk='".$unit."'";
		}else{
			$wh.=" and kodeorganisasi='".$unit."'";
		}
		$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where 1=1 ".$wh." order by namaorganisasi asc "; #exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optdiv.="<option value=".$bar['kodeorganisasi'].">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		}
	}
	echo $optdiv;
	break;
	case'loaddatapajak':

		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['namaakun']."</td>";
		$tab.="<td>".$_SESSION['lang']['pajak']." (%)</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		foreach($_SESSION['pajak'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$optakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$row['nourut']."'");

				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$optakun[$row['nourut']]."</td>";
				$tab.="<td style='text-align:right'>".$row['nilai']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepajak('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($param);
		// print_r($_SESSION['pajak']);
		// echo"</pre>";

		echo $tab;
	break;

	case'loaddatanopol':
		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['nopol']."</td>";
		$tab.="<td>".$_SESSION['lang']['sopir']."</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		foreach($_SESSION['nopol'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$row['nopol']."</td>";
				$tab.="<td style='text-align:left'>".$row['supir']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletenopol('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($param);
		// print_r($_SESSION['pajak']);
		// echo"</pre>";

		echo $tab;
	break;

	case'deletenopol':
			if (isset($param['no']) && preg_match('/^[0-9]{1,6}$/', $param['no']) && isset($_SESSION['nopol'][$param['no']]) && $_SESSION['nopol'][$param['no']]['notransaksi'] == $param['notransaksi']) {
				unset($_SESSION['nopol'][$param['no']]);
			}

		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['nopol']."</td>";
		$tab.="<td>".$_SESSION['lang']['sopir']."</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		foreach($_SESSION['nopol'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$row['nopol']."</td>";
				$tab.="<td style='text-align:left'>".$row['supir']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletenopol('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($param);
		// print_r($_SESSION['pajak']);
		// echo"</pre>";

		echo $tab;
	break;
	case'addnopol':
		if (!isset($_SESSION['nopol_untuk']) || $_SESSION['nopol_untuk'] !== $param['notransaksi'] || $param['notransaksi'] == '') {
			exit("Warning : Lengkapi Jenis, PT, Unit, dan Tanggal dulu (atau buka ulang SPK ini) sebelum menambah no polisi.");
		}
		if (isset($param['supir']) && !preg_match('/^[A-Za-z0-9 \.\-]{0,50}$/', $param['supir'])) {
			exit("Warning : Nama sopir hanya boleh huruf, angka, spasi, titik, dan strip.");
		}
		if (isset($param['supir']) && strlen($param['supir']) > 24) {
			exit("Warning : Nama sopir maksimal 24 karakter.");
		}
		if (isset($param['nopol']) && strlen($param['nopol']) > 12) {
			exit("Warning : No polisi maksimal 12 karakter.");
		}

		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['nopol']."</td>";
		$tab.="<td>".$_SESSION['lang']['sopir']."</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		if(!preg_match("/^[a-zA-Z0-9]*$/", $param['nopol'])){
			exit("Warning : Hanya boleh Huruf dan Angka.");
		}
		
		if(is_numeric(substr($param['nopol'],0,1))){
			exit("error : No polisi salah, lengkapi kembali No polisi.");
		}

		$newdata = array();
		$newdata = array(
			'notransaksi'=>$param['notransaksi'],
			'nopol'      =>$param['nopol'],
			'supir'      =>$param['supir']
		);
		if($_SESSION['nopol'] != array()){
			foreach($_SESSION['nopol'] as $key=>$row){
				if($row['notransaksi'] == $param['notransaksi'] && $row['nopol'] == $param['nopol']){
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			if($newdata['nopol']!=''){
				array_push($_SESSION['nopol'],$newdata);
			}
		}else{
			if($newdata['nopol']!=''){
				array_push($_SESSION['nopol'],$newdata);
			}
		}

		foreach($_SESSION['nopol'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$row['nopol']."</td>";
				$tab.="<td style='text-align:left'>".$row['supir']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletenopol('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($_SESSION['nopol']);
		// print_r($param);
		// echo"</pre>";

		echo $tab;
	break;
	case'deletepajak':
			if (isset($param['no']) && preg_match('/^[0-9]{1,6}$/', $param['no']) && isset($_SESSION['pajak'][$param['no']]) && $_SESSION['pajak'][$param['no']]['notransaksi'] == $param['notransaksi']) {
				unset($_SESSION['pajak'][$param['no']]);
			}

		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['namaakun']."</td>";
		$tab.="<td>".$_SESSION['lang']['pajak']." (%)</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		foreach($_SESSION['pajak'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$optakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$row['nourut']."'");

				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$optakun[$row['nourut']]."</td>";
				$tab.="<td style='text-align:right'>".$row['nilai']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepajak('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($param);
		// print_r($_SESSION['pajak']);
		// echo"</pre>";

		echo $tab;
	break;

	case'addpajak':
		if (!isset($_SESSION['pajak_untuk']) || $_SESSION['pajak_untuk'] !== $param['notransaksi'] || $param['notransaksi'] == '') {
			exit("Warning : Lengkapi Jenis, PT, Unit, dan Tanggal dulu (atau buka ulang SPK ini) sebelum menambah pajak.");
		}
		if (!isset($param['jenispajak']) || $param['jenispajak'] === '' || !isset($param['nilaipajak']) || $param['nilaipajak'] === '') {
			exit("Warning : Pilih jenis pajak dan isi nilainya.");
		}
		$stmtP = $owlPDO->prepare("select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='PPHSPK'");
		$stmtP->execute();
		if (!in_array($param['jenispajak'], explode(',', (string)$stmtP->fetchColumn()))) {
			exit("Warning : Jenis pajak tidak valid.");
		}
		if (!preg_match('/^[0-9]+(\.[0-9]+)?$/', $param['nilaipajak']) || $param['nilaipajak'] <= 0 || $param['nilaipajak'] > 100) {
			exit("Warning : Nilai pajak harus lebih dari 0 dan maksimal 100.");
		}
		$jumlahPajak = $param['nilaipajak'];
		if (isset($_SESSION['pajak'])) {
			foreach ($_SESSION['pajak'] as $rowP) {
				if ($rowP['notransaksi'] == $param['notransaksi']) {
					$jumlahPajak += $rowP['nilai'];
				}
			}
		}
		if ($jumlahPajak > 100) {
			exit("Warning : Total pajak tidak boleh lebih dari 100%.");
		}
		$tab="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$tab.="<thead><tr class=rowheader style=text-align:center>";
		$tab.="<td>".$_SESSION['lang']['namaakun']."</td>";
		$tab.="<td>".$_SESSION['lang']['pajak']." (%)</td>";
		$tab.="<td width=18px>#</td>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody>";

		$newdata = array();
		$newdata = array(
			'notransaksi'=>$param['notransaksi'],
			'tipe'       =>'pajak',
			'nourut'     =>$param['jenispajak'],
			'nilai'      =>$param['nilaipajak']
		);
		if($_SESSION['pajak'] != array()){
			foreach($_SESSION['pajak'] as $key=>$row){
				if($row['notransaksi'] == $param['notransaksi'] && $row['nourut'] == $param['jenispajak']){
					exit("Warning : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			if($newdata['nourut']!='' and $newdata['nilai']!=''){
				array_push($_SESSION['pajak'],$newdata);
			}
		}else{
			if($newdata['nourut']!='' and $newdata['nilai']!=''){
				array_push($_SESSION['pajak'],$newdata);
			}
		}

		foreach($_SESSION['pajak'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$optakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$row['nourut']."'");

				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:left'>".$optakun[$row['nourut']]."</td>";
				$tab.="<td style='text-align:right'>".$row['nilai']."</td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepajak('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$tab.="</tr>";
			}
		}
		$tab.="</tbody>";
		$tab.="</table>";

		// echo"<pre>";
		// print_r($_SESSION['pajak']);
		// print_r($param);
		// echo"</pre>";

		echo $tab;
	break;
	case'getsubunit':

	$namaproject = "x";
	$tgldari = "x";
	$tglsampai = "x";
	$supplierid = "x";
	$totalrupiah = "x";
	$kodecapex = "x";
	if($notransaksi!=''){
		$sSub="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$rSub=fetchData($sSub);
		$isiHt=$rSub[0];
	}

	$opt="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$optkeg="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	if($jenis=='PO/SO'){
		$str="select * from ".$dbname.".log_podt where nopo='".$divisi."' order by nopo asc "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			$opt.="<option value=".$bar['kodebarang'].">".$bar['kodebarang']." - ".$nmbrg[$bar['kodebarang']]."</option>";
			$optkeg.="<option value=".$bar['catatan'].">".$bar['catatan']."</option>";
		}
	}else if($jenis=='PROJECT'){
		$str="select * from ".$dbname.".project_dt where kodeproject='".$divisi."'"; //exit('error'.$str);
		$count=fetchData($str);
		if(count($count)<=0){
			// exit('Warning : Tidak ada detail project !');
		}
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optkeg.="<option value=".$bar['kegiatan'].">".$bar['namakegiatan']."</option>";
		}
		$nmpro=makeOption($dbname,'project','kode,nama',"kode='".$divisi."'");
		$opt.="<option value=".$divisi.">".$nmpro[$divisi]."</option>";
		// $opt.="<option value='Project'>Project</option>";

		##GET DETAIL PROJECT
		$str="select * from ".$dbname.".project where kode='".$divisi."'";
		$res=fetchData($str);
		$namaproject = $res[0]['nama'];
		$tgldari = $res[0]['tanggalmulai'];
		$tglsampai = $res[0]['tanggalselesai'];
		$kodecapex = $res[0]['kodecapex'];

		if($kodecapex!=''){
			$str="select * from ".$dbname.".spl_capexbangunan where kode='".$kodecapex."'";
			$res=fetchData($str);
			$supplierid = $res[0]['kontraktor'];

			$str="select * from ".$dbname.".spl_tendercapex where kode='".$kodecapex."' and supplierid='".$supplierid."'";
			$res=fetchData($str);
			$idtender = $res[0]['id'];

			$str="select * from ".$dbname.".spl_tendercapexdt where tendercapexid='".$idtender."'";
			$res=fetchData($str);
			$totalrupiah = 0;
			foreach($res as $key=>$val){
				$optVolume = makeOption($dbname,'spl_capexbangunandt','kegiatan,volume',"kegiatan='".addZero($val['kodekegiatan'],8)."'");
				$volume = $optVolume[addZero($val['kodekegiatan'],8)];

				if($val['tipedata']=='kegiatan'){
					$totalrupiah = $totalrupiah + (($volume*$val['hargasatuan']) + ($val['hk']*$val['rphk']));
				}

				if($val['tipedata']=='material'){
					$totalrupiah = $totalrupiah + (($val['jumlah']*$val['hargasatuan']));
				}
			}
		}
	}else{
		$wh='';
		$kol='namaorganisasi,kodeorganisasi,induk';
		if($divisi!=''){
			if($jenis=='KEBUN'){
				$wh.=" and indukblok like '".$divisi."%'";
				$kol = "distinct indukblok,namaindukblok";
			}else if($jenis=='PABRIK'){
				$wh.=" and induk='".$divisi."'";
			}else{
				if($jenis=='SEWA.HM'){
					$wh.=" and kodeorganisasi like '".$divisi."%'";
				}else{					
					$wh.=" and kodeorganisasi='".$divisi."'";
				}
			}
		}else{
			if($jenis=='KEBUN'){
				$wh.=" and indukblok like '".$unit."%' and indukblok in (select indukblok from ".$dbname.".setup_blok where (luasareaproduktif > 0 OR lc > 0 OR luasbloking > 0) and status='A')";
			}else if($jenis=='PABRIK'){
				$wh.=" and induk='".$unit."'";
			}else{
				$wh.=" and kodeorganisasi='".$unit."'";
			}
		}
		if($jenis=='SEWA.HM'){
			$str="select * from ".$dbname.".vhc_5master where 1=1 and kodetraksi like '".$unit."%' and kepemilikan='0' and status='1'"; #exit('error'.$str);
			$res = fetchdata($str);
			$opt.="<optgroup label='KENDARAAN'>";
			foreach($res as $bar){
				$sttdt="";
				if($isiHt['divisi']==$bar['kodevhc']){
					$sttdt="selected";
				}
				$opt.="<option value=".$bar['kodevhc']." ".$sttdt.">".$bar['kodevhc']." - ".$bar['nopol']." (".$bar['detailvhc'].")</option>";
			}
			$opt.="</optgroup>";
			
			$str="select namaorganisasi,kodeorganisasi,induk from ".$dbname.".organisasi where 1=1 ".$wh." and tipe not like '%GUDANG%'  order by induk, namaorganisasi asc "; #exit('error'.$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);$n='';
			while($bar=$res->fetch()){
				$d=$bar['induk'];
				if ($d !== $n && $n !== "") {
					$opt .= "</optgroup>";
				}
				if($d!=$n){			
					$opt.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
				}
				
				$sttdt="";
				if($isiHt['divisi']==$bar['kodeorganisasi']){
					//$sttdt="selected";
				}
				$opt.="<option value=".$bar['kodeorganisasi']." ".$sttdt.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
				
				$n=$d;
				if($d!=$n){			
					$opt.="</optgroup>";
				}
			}
		}else{			
			$str="select ".$kol." from ".$dbname.".organisasi where 1=1 ".$wh." and tipe not like '%GUDANG%'  order by induk, namaorganisasi asc "; #exit('error'.$str);
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);$n='';
			while($bar=$res->fetch()){
				$d=$induk[$bar['induk']];
				if ($d !== $n && $n !== "") {
					$opt .= "</optgroup>";
				}
				if($d!=$n){			
					$opt.="<optgroup label='".$d." - ".getNamaOrg($d)."'>";
				}
				
				$sttdt="";
				if($isiHt['divisi']==$bar['kodeorganisasi']){
					$sttdt="selected";
				}

				if($jenis != 'KEBUN'){
					$opt.="<option value=".$bar['kodeorganisasi']." ".$sttdt.">".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
				}else{
					$opt.="<option value=".$bar['indukblok']." ".$sttdt.">".$bar['indukblok']." - ".$bar['namaindukblok']."</option>";
				}
				$n=$d;
				if($d!=$n){			
					$opt.="</optgroup>";
				}
			}
		}
		
		$whr='';
		if($jenis=='KEBUN' and $divisi!=''){
			$whr.=" and kelompok in ('BBT','PNN','TB','TBM','TM','LC')";
		}elseif($jenis=='KEBUN' and $divisi==''){
			$whr.=" and kelompok in ('KNT')";
		}elseif($jenis=='SEWA.HM' and $divisi!=''){
			$whr.=" and kelompok in ('TRK','BBT','PNN','TB','TBM','TM','LC')";
		}elseif($jenis=='ANGKUTTBS' and $divisi!=''){
			$whr.=" and kelompok in ('PNN')";
		}else{
			$whr.=" and left(kelompok,3) in ('MIL','KNT')";
		}
		$str="select * from ".$dbname.".setup_kegiatan where status='1' ".$whr." order by kodekegiatan asc"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);$n='';
		while($bar=$res->fetch()){
			$d=substr($bar['kodekegiatan'],0,3);
			if ($d !== $n && $n !== "") {
				$optkeg .= "</optgroup>";
			}
			if($d!=$n){			
				$optkeg.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
			}
			$optkeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
			$n=$d;
			if($d!=$n){			
				$optkeg.="</optgroup>";
			}			
		}
	}
	
	$strspk="select notransaksi from ".$dbname.".lgl_pengajuanspkht where jenis = '".$jenis."' and unit='".$unit."'";
	//echo $strspk;
	$rtrcust=fetchData($strspk);
	foreach ($rtrcust as $key => $val) {
		$optspk.="<option value=".$val['notransaksi'].">".$val['notransaksi']."</option>";
	}
	
	echo $opt."####".$optkeg."####".$namaproject."####".tanggalnormal($tgldari)."####".tanggalnormal($tglsampai)."####".$totalrupiah."####".$supplierid."####".$kodecapex;
	break;

	case'getsatuan':
	$opt=$optkeg="";
	$optkeg="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	$opt="<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
	if($jenis=='PO/SO'){
		$opt=makeOption($dbname,'log_5masterbarang','satuan,satuan',"kodebarang='".$subunit."'");
		foreach($opt as $key => $val){
			$optsat.="<option value=".$key.">".$key."</option>";
		}
	}elseif($jenis=='PROJECT'){
		$opt=makeOption($dbname,'project_dt','satuan,satuan',"kegiatan='".$kegiatan."'");
		foreach($opt as $key => $val){
			$optsat.="<option value=".$key.">".$key."</option>";
		}
	}elseif($jenis=='SEWA.HM'){
		$kend = makeOption($dbname,'vhc_5master','kodevhc,kodevhc',"kodevhc='".$subunit."'");
		$whr="";
		if($kend[$subunit]!=''){
			$whr.=" and kelompok in ('TRK')";
		}elseif(strlen($subunit)<10){
			$whr.="";
			if(getNamaOrg(substr($divisi,0,4),'tipe')=='PABRIK'){
				$whr.=" and noakun like '7%'";
			}
			if(getNamaOrg(substr($divisi,0,4),'tipe')=='KEBUN'){
				$whr.="";
			}
			if(getNamaOrg(substr($divisi,0,4),'tipe')=='TC'){
				$whr.=" and noakun like '82%'";
			}

			if(getNamaOrg(substr($divisi,0,4),'tipe')=='RND'){
				$whr.=" and noakun like '82%'";
			}

			if(getNamaOrg(substr($divisi,0,4),'tipe')=='KANWIL'){
				$whr.=" and noakun like '82%'";
			}

			if(getNamaOrg(substr($divisi,0,4),'tipe')=='BULKING'){
				$whr.=" and noakun like '81%'";
			}
		}else{
			if(getBlok($subunit,'statusblok')=='TM'){
				$whr.=" and kelompok in ('TM','PNN')";
			}else{				
				$whr.=" and kelompok in ('".getBlok($subunit,'statusblok')."')";
			}
		}
		
		$str="select * from ".$dbname.".setup_kegiatan where status='1' ".$whr." order by kodekegiatan asc"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);$n='';
		while($bar=$res->fetch()){
			$d=substr($bar['kodekegiatan'],0,3);
			if ($d !== $n && $n !== "") {
				$optkeg .= "</optgroup>";
			}
			if($d!=$n){			
				$optkeg.="<optgroup label='".$d." - ".getNamaAkun($d)."'>";
			}
			$optkeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']." - ".$bar['namakegiatan']."</option>";
			$n=$d;
			if($d!=$n){			
				$optkeg.="</optgroup>";
			}			
		}
		
		$whr='';
		$whr.=" and kodekegiatan ='".$kegiatan."'";
		$str="select * from ".$dbname.".setup_kegiatan where status='1' ".$whr." "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optsat.="<option value=".$bar['satuan'].">".$bar['satuan']."</option>";
		}
	}else{
		$whr='';
		$whr.=" and kodekegiatan ='".$kegiatan."'";
		$str="select * from ".$dbname.".setup_kegiatan where status='1' ".$whr." "; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optsat.="<option value=".$bar['satuan'].">".$bar['satuan']."</option>";
		}
	}
	echo $optsat."##".$optkeg;
	break;



	case'html':
	spkHeader($notransaksi, 'baca', false, true);
	$tab=$brd='';
	if($tipe=='html'){
		#$tab= "<img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"viewexcel('".$notransaksi."','excel');\">";
		$tab.="<link rel=stylesheet type=text/css href=style/".$gen.">";
	}
	
	$theme=$_SESSION['theme'];
	if($theme=='skyblue' || $theme==''){
	  $men='menu.css';
	  $gen='generic.css';
	}else if($theme=='red'){
	  $men='menuRed.css';
	  $gen='genericRed.css';  
	}else{
	  $men='menuGray.css';
	  $gen='genericGray.css';  
	}  
		
	$countApprove = getCountApproval('SPK',$unit);
	$str=" select * from ".$dbname.".lgl_pengajuanspkht where  notransaksi='".$notransaksi."' ";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$res->fetch();

	if($bar['kategori']=='PUSAT'){
		$strx="select kodeorganisasi from ".$dbname.".organisasi where induk='".$bar['pt']."' and tipe='HOLDING'";
		$resx=fetchdata($strx);
		$bar['unit'] = $resx[0]['kodeorganisasi'];
	}else{
		$bar['unit'] = $bar['unit'];
	}
	if($tipe != 'pdf'){

		$tab.= "
			".($tipe=='html' ? "<div style='font-weight:bold;margin:2px 0 4px 2px;'>Persetujuan</div>" : "")."<table width=100% border=0 cellspacing=1 cellpadding=5 class=sortable>
			<thead>
			<tr style='font-weight:bold'>
				<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
				for($i=1;$i<=$countApprove;$i++){
					$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
				}
		$tab.= "
			</tr>
			</thead>
			<tbody>";
			$tab.= "<tr class=rowcontent>
					<td>".$nmkar[$bar['updateby']]."<br>
						".waktunormal($bar['createtime'])."</td>";
				for($i=1;$i<=$countApprove;$i++){
					$strx="select * from ".$dbname.".setup_approval where jenispersetujuan='SPK' and level='".$i."' and kodeunit='".$bar['unit']."'";
					$resx=fetchData($strx);
					$tipeapp = $resx[0]['tipe'];
					$departemenapp = $resx[0]['departemen'];
					$tipekaryawanapp = $resx[0]['tipekaryawan'];
					$jabatanapp = $resx[0]['jabatan'];
	
					$arrApp = detailApprove($i,$notransaksi,'SPK');
	
					if($tipeapp=='1' && $arrApp['status']!=''){
						if($arrApp['status']!='1'){
							if($departemenapp!=''){
								$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$departemenapp."'");
								$arrApp['nama'] = $opttipe[$departemenapp];
							}
	
							if($tipekaryawanapp!=''){
								$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$tipekaryawanapp."'");
								$arrApp['nama'] = $opttipe[$tipekaryawanapp];
							}
	
							if($jabatanapp!='0'){
								$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$jabatanapp."'");
								$arrApp['nama'] = $opttipe[$jabatanapp];
							}
						}
					}
	
					if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
						$tngl='';
					}else{
						$tngl=tanggalnormal($arrApp['tanggal']);
					}
	
					if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
						$tab.= "<td>".$arrApp['nama']."
							<br />".$arrHsl[$arrApp['status']]."
							<br />".$tngl."
							<br />".$arrApp['komentar']."
						</td>";
					}else{
						$tab.= "<td>&nbsp;</td>";
					}
				}
	
	
			$tab.= "</tbody>
			</table><div style=clear:both><br></div>";
	}


	$nmrekanan=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
	$nmklsup=makeOption($dbname,'log_5klsupplier','tipe,kode');

	$no = 0;
	$str = "select * from " . $dbname . ".lgl_pengajuanspkht where notransaksi='" . $notransaksi . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	$jenis = $bar['jenis'];
	$rp='';
	$strx = "SELECT * FROM " . $dbname . ".lgl_pengajuanspkdt	 where notransaksi='".$notransaksi."'";
	$resx = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
	$resx->setFetchMode(PDO::FETCH_ASSOC);
	$groupArr = array();
	while($barx = $resx->fetch()){
		$d['nourut']= $barx['nourut'];
		$d['tipe']  = $barx['tipe'];
		$d['nilai'] = $barx['nilai'];
		$d['keterangan'] = $barx['keterangan'];
		$groupArr[] = $d;
	}
	if($tipe=='html'){
		$tab.= "<div style='font-weight:bold;margin:2px 0 4px 2px;'>Header</div><table width=100% cellpadding=5 cellspacing=1 border=0 class=sortable>";
	} elseif ($tipe == 'pdf') {
		$brd = " style='border:0.5px solid black;margin:0 auto!important;'";
		$tab.= "<h3 align=center>Pengajuan SPK</h3>";
		$tab.= "<table cellpadding=1.5 cellspacing=0 ".$brd.">";
	} else{
		$tab.= "<table cellpadding=1 cellspacing=1 border=1>";
	}
	$nmsupprekanan=$nmrekanan[$bar['koderekanan']];
	if($nmsupprekanan==""){
		$optnmcust=makeOption($dbname,"pmn_4customer","kodecustomer,namacustomer");
		$nmsupprekanan=$optnmcust[$bar['koderekanan']];
	}
	#tanggal tampil dd-mm-yyyy; kosong bila belum diisi
	$tglF = function($d) {
		return ($d == '' || $d == '0000-00-00') ? '' : tanggalnormal($d);
	};
	$stLabel = ($brd == '') ? " style='white-space:nowrap;'" : $brd;
	#satu pasang label : isi
	$kv = function($label, $isi, $colspan = 1) use ($brd, $stLabel) {
		return "<td " . $stLabel . ">" . $label . "</td><td " . $brd . ">:</td><td " . $brd . " colspan=" . $colspan . ">" . $isi . "</td>";
	};
	$nilaiTxt = array();
	$pajakTxt = array();
	foreach($groupArr as $key => $val){
		if($val['tipe']=='rupiah'){
			$nilaiTxt[] = hidezerodecimal($val['nilai'],2);
		}
		if($val['tipe']=='pajak'){
			$pajakTxt[] = $val['nourut']." ".$nmakun[$val['nourut']]." = ".hidezerodecimal($val['nilai'],2)." %";
		}
	}
	$periodeTxt = ($bar['tanggaldari'] == '0000-00-00' && $bar['tanggalsampai'] == '0000-00-00') ? '' : $tglF($bar['tanggaldari'])." s/d ".$tglF($bar['tanggalsampai']);
	$tab.= "<tr class=rowcontent>
			<td " . $stLabel . ">Nomor</td><td " . $brd . ">:</td><td " . $brd . " id=notranpopup>".$notransaksi."</td>
			".$kv('Kategori', $bar['kategori'])."
			".$kv('Jenis', $bar['jenis'])."
		</tr>
		<tr class=rowcontent>
			".$kv($_SESSION['lang']['pt'], $nmorg[$bar['pt']])."
			".$kv($_SESSION['lang']['unit'], $nmorg[$bar['unit']])."
			".$kv($_SESSION['lang']['divisi'], $bar['divisi'])."
		</tr>
		<tr class=rowcontent>
			".$kv('Tanggal', $tglF($bar['tanggal']))."
			".$kv($_SESSION['lang']['project'], $bar['project'])."
			".$kv($_SESSION['lang']['koderekanan'], $nmsupprekanan)."
		</tr>
		<tr class=rowcontent>
			".$kv($_SESSION['lang']['jenis']." ".$_SESSION['lang']['supplier'], $nmklsup[$bar['jenissupplier']])."
			".$kv('Tanggal Mulai s/d Selesai', $periodeTxt)."
			".$kv('Jangka Waktu', $bar['jangkawaktu'])."
		</tr>
		<tr class=rowcontent>
			".$kv('Retensi (%)', $bar['retensi'])."
			".$kv('SPK Lama', $bar['notransaksiold'])."
			".$kv('Jumlah HM', ($bar['jlhhm'] > 0 ? hidezerodecimal($bar['jlhhm'],2) : ''))."
		</tr>
		<tr class=rowcontent>
			".$kv('Total Nilai Kontrak (Rp)', implode('<br>', $nilaiTxt))."
			".$kv('Pajak (%)', implode('<br>', $pajakTxt), 4)."
		</tr>";
	if($bar['jenis']=='ANGKUTTBS'){
		$tab.= "<tr class=rowcontent>
			<td " . $stLabel . " valign=top>No Polisi</td><td " . $brd . " valign=top>:</td>
			<td " . $brd . " valign=top colspan=7><div style=overflow:auto;max-height:140px>";
		$tab.="<table width=100% border=0 cellpadding=3 cellspacing=1 class=sortable>";
		$tab.="<thead><tr class=rowheader>";
		$tab.="<td ".$brd.">" . $_SESSION['lang']['nopol'] . "</td>";
		$tab.="<td ".$brd.">" . $_SESSION['lang']['sopir'] . "</td>";
		$tab.="<td ".$brd." width=30px>" . $_SESSION['lang']['status'] . "</td>";
		$tab.="<td ".$brd." width=20px>" . $_SESSION['lang']['action'] . "</td>";
		$tab.="</tr></thead>";
		
		$tab.="<tr class=rowcontent>";
		$tab.="<td ".$brd." style=\"width:80px\"><input id=nopol2 class=myinputtext maxlength=9 onkeydown=\"upperCaseF(this)\" style=\"width:80px;align:left;\"></td>";
		$tab.="<td ".$brd."><input id=supir2 class=myinputtext placeholder='Sopir' onkeydown=\"upperCaseF(this)\" style=\"width:99%;align:left;\"></td>";
		$opt.="<option value='A'>Aktif</option><option value='D'>Nonaktif</option>";
		$tab.="<td ".$brd." width=30px><select style=\"width:50px;\" id=status2>".$opt."</select>
		</td>";
		$tab.="<td ".$brd." width=20px align=center><img src='images/plus.png' class='zImgBtn' title='Tambah Nopol'; onclick=addnopol2('".$notransaksi."'); style='position:relative;top:3px;left:3px;'></td>";
		$tab.="</tr>";
		
		$str = "select * from " . $dbname . ".log_spknopol where notransaksi='" . $notransaksi . "'";
		$res = fetchdata($str);
		foreach($res as $val){
			$tab.="<tr  class=rowcontent>";
			$tab.="<td ".$brd.">".$val['nopol']."</td>";
			$tab.="<td ".$brd.">".$val['supir']."</td>";
			if($val['status']=='A'){$n="Aktif";}else{$n='Nonaktif';}
			$tab.="<td ".$brd.">".$n."</td>";
			$tab.="<td ".$brd." align=center><img src='images/application/application_edit.png' class='zImgBtn' title='Edit' onclick=\"editnopol('".$val['nopol']."','".$val['supir']."','".$val['status']."')\"></td>";
			$tab.="</tr>";
		}
		$tab.="</table>";
		$tab.="</div></td>
		</tr>";
	}
	$tab.= "<tr class=rowcontent>
			<td " . $stLabel . " valign=top>Spesifikasi<br>Pekerjaan</td>
			<td " . $brd . " valign=top>:</td>
			<td " . $brd . " valign=top colspan=7>".nl2br($bar['spesifikasi'])."</td>
		</tr>";
		$tab.="</table>
		<div style=clear:both><br></div>
		".($tipe=='html' ? "<div style='font-weight:bold;margin:2px 0 4px 2px;'>Detail</div>" : "")."<table class='sortable' width=100% cellspacing='1' cellpadding=5 border='0' >
			<thead>
			<tr class=rowheader>
				<th align=center ".$brd.">No</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['subunit'] . "</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['kegiatan'] . "</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['satuan'] . "</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['hk'] . "</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['volume'] . "</th>
				<th align=center ".$brd.">Rp / Sat</th>
				<th align=center ".$brd.">" . $_SESSION['lang']['total'] . "</th>
			</tr>
			</thead>
			<tbody>";

		$no = 0;
		$str = "SELECT * FROM " . $dbname . ".lgl_pengajuanspk_keg	 where 1=1 and notransaksi='" . $notransaksi . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$row=$res->rowCount();
		$res->setFetchMode(PDO::FETCH_ASSOC);
		if(empty($row)){
			$tab.="<tr class=rowcontent><td ".$brd." colspan=9 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$nmkeg='';
			while ($bar = $res->fetch()) {
				$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kegiatan']."'");
				$isi = '';
				$no+=1;
				$a=$no%2;
				$xx='';
				if($a==1){
					//$xx.=" style=background-color:#F5EEF8";
				}
				$namakegiatan = '';
				## CEK PROJECT
				$strx="select * from ".$dbname.".project where kode='".$bar['subunit']."'";
				$resx=fetchData($strx);
				$namaproject = $resx[0]['nama'];
				$kodecapex = $resx[0]['kodecapex'];

				if($kodecapex==''){
					$strx="select * from ".$dbname.".project_dt where kegiatan='".$bar['kegiatan']."'";
					$resx=fetchData($strx);
					$namakegiatan = $resx[0]['namakegiatan'];
				}else{
					$strx="select * from ".$dbname.".spl_capexbangunandt where kegiatan='".$bar['kegiatan']."'";
					$resx=fetchData($strx);
					$namakegiatan = $resx[0]['namakegiatan'];
				}
				$optNamaBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['subunit']."'");
				$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
				$tab.="<td ".$brd." align=center>" . $no . "</td>";
				if($jenis=='SEWA.HM' and $nmorg[$bar['subunit']]==''){
					$tab.="<td ".$brd.">" . $bar['subunit'] . " - " .getNopol($bar['subunit']). "</td>";
				}else{					
					$tab.="<td ".$brd.">" . $bar['subunit'] . " - " .($nmorg[$bar['subunit']]==''?($namaproject==''?(spkNamaBlok($bar['subunit'])!=''?spkNamaBlok($bar['subunit']):$optNamaBarang[$bar['subunit']]):$namaproject):$nmorg[$bar['subunit']]). "</td>";
				}
				$tab.="<td ".$brd.">" . $bar['kegiatan'] . " - " . ($nmkeg[$bar['kegiatan']]==''?$namakegiatan:$nmkeg[$bar['kegiatan']]) . "</td>";
				$tab.="<td ".$brd." align=center>" . $bar['satuan'] . "</td>";
				$tab.="<td ".$brd." align=right>".hidezerodecimal($bar['hk'])."</td>";
				$tab.="<td ".$brd." align=right>".hidezerodecimal($bar['volume'],2)."</td>";
				$tab.="<td ".$brd." align=right>".hidezerodecimal(@($bar['total']/$bar['volume']),2)."</td>";
				$tab.="<td ".$brd." align=right>".hidezerodecimal($bar['total'])."</td>";
				$tab.="</tr>";

				$ttl+=$bar['total'];
			}

			$tab.="<tr class=rowcontent>";
			$tab.="<td ".$brd." align=center colspan=7>TOTAL</td>";
			$tab.="<td ".$brd." align=right>".hidezerodecimal($ttl)."</td>";
			$tab.="</tr>";
		}

		$tab.="</tbody>
		</table>";
	if($tipe=='html'){
		$namafile=checkPostGet('namafile','');
			#file PDF hanya ditulis oleh jalur internal (bot) dan hanya ke folder imgbot/pdf
			if($namafile!='' && (!$mobileValid || !preg_match('#^imgbot/pdf/[A-Za-z0-9_\-\.]+\.pdf$#', $namafile))){
				$namafile='';
			}
		if($namafile!=''){
			$dompdf = new Dompdf();
			$dompdf->load_html($tab);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$canvas = $dompdf->get_canvas();
			if (file_exists($namafile)){
				unlink($namafile);
			}
			file_put_contents($namafile, $dompdf->output());
		}else{			
			echo $tab;
			echo @$isi.="<div style=clear:both><br></div><div style='font-weight:bold;margin:2px 0 4px 2px;'>File</div><table class='sortable' width=100% cellpadding=5 cellspacing='1' border='0'>
						<thead>
						<tr class=rowheader>
							<td align='center' width=50px>No.</td>
							<td align='center' width=50px>File Type</td>
							<td align='center' width=100px>Kriteria</td>
							<td align='center'>Filename</td>
							<td align='center' width=50px>Action</td>
						</tr>
						</thead>
						<tbody id='loadfilesdetail'>
						</tbody>
					</table>";
		}
	}else if($tipe =='pdf'){
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'potrait');
		$dompdf->render();
		$dompdf->stream("Realisasi BAPP",array("Attachment"=>0));
	}else {
		$stream = $tab;
		$nop_ = "pengajuan_spk";
		if (strlen($stream) > 0) {
			if($urlefil=='0'){
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							 @ unlink('tempExcel/'.$file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/".$nop_.".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
					parent.window.alert('Cant convert to excel format');
					</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
					window.location='tempExcel/".$nop_.".xls';
					</script>";
				}
				closedir($handle);
			}else{
				$handle=fopen($urlefil, 'w');
				fwrite($handle, $stream);
				fclose($handle);
			}
		}
	}
	break;
	case'addnopol2':
			spkHeader($notransaksi, 'ubah');
			if (isset($param['supir']) && !preg_match('/^[A-Za-z0-9 \.\-]{0,50}$/', $param['supir'])) {
				exit("Warning : Nama sopir hanya boleh huruf, angka, spasi, titik, dan strip.");
			}
		if (isset($param['supir']) && strlen($param['supir']) > 24) {
			exit("Warning : Nama sopir maksimal 24 karakter.");
		}
		if (isset($param['nopol']) && strlen($param['nopol']) > 12) {
			exit("Warning : No polisi maksimal 12 karakter.");
		}
			if (isset($param['stat']) && !preg_match('/^[A-Za-z0-9]{0,3}$/', $param['stat'])) {
				exit("Warning : Status nomor polisi tidak valid.");
			}
		if(!preg_match("/^[a-zA-Z0-9]*$/", $param['nopol'])){
			exit("Warningcode : Hanya boleh Huruf dan Angka.");
		}
		
		if(is_numeric(substr($param['nopol'],0,1))){
			exit("errorcode : No polisi salah, lengkapi kembali No polisi.");
		}
		
		$str = "select * from " . $dbname . ".log_spknopol where notransaksi='" . $notransaksi . "' and nopol='".$param['nopol']."'";
		$res = fetchdata($str);
		if(count($res)>0){
			$str="update ".$dbname.".log_spknopol set supir='".$param['supir']."', status='".$param['stat']."' where notransaksi='".$notransaksi."' and nopol='".$param['nopol']."'";
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}else{
			if($param['nopol']=='' and $param['supir']==''){
				exit("errorcode : No polisi dan sopir tidak boleh kosong.");
			}
			
			$data = array(
				'notransaksi'=> $notransaksi,
				'nopol'      => $param['nopol'],
				'supir'      => $param['supir'],
				'status'      => $param['stat'],
				'createby'   => $_SESSION['standard']['userid'],
				'createdate' => date("YmdHis")
			);

			$cols = array();
			foreach($data as $keyn=>$rown) {
					$cols[] = $keyn;
			}
			$str = insertQuery($dbname,'log_spknopol',$data,$cols);
			try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}
	break;
	case'viewlistfile':
	spkHeader($notransaksi, 'baca', false, true);
	$tab.="<fieldset>
				<legend>".$_SESSION['lang']['list']."</legend>
				<table class='sortable' cellspacing='1' border='0' style=min-width:350px>
					<thead>
					<tr class=rowheader>
						<td align='center' width=50px>No.</td>
						<td align='center' width=50px>File Type</td>
						<td align='center' width=750px>Kriteria</td>
						<td align='center'>Filename</td>
						<td align='center' width=50px>Action</td>
					</tr>
					</thead>
					<tbody id='loadfilesdetail'>
					</tbody>
				</table>
			</fieldset> ";
	echo $tab;
	break;
	// case'editdetail':
		// $rupiah=$pajak=$termin=$no='';
		// $str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='rupiah'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $rows=owlBaris($res);
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
			// $no+=1;
			// if($no==$rows){
				// $rupiah.=$bar['tipe']."##".$bar['nourut']."##".$bar['nilai']."##".$bar['keterangan'];
			// }else{
				// $rupiah.=$bar['tipe']."##".$bar['nourut']."##".$bar['nilai']."##".$bar['keterangan']."#$#";
			// }
		// }
		// $str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='pajak'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $rows=owlBaris($res); $no='';
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
			// $no+=1;
			// if($no==$rows){
				// $pajak.=$bar['tipe']."####".$bar['nourut']."####".$bar['nilai'];
			// }else{
				// $pajak.=$bar['tipe']."####".$bar['nourut']."####".$bar['nilai']."#$$#";
			// }
		// }
		// $str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='termin'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $rows=owlBaris($res); $no='';
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// while($bar=$res->fetch()){
			// $no+=1;
			// if($no==$rows){
				// $termin.=$bar['tipe']."######".$bar['nourut']."######".number_format($bar['nilai'],2)."######".$bar['keterangan']."######".$bar['rptermin'];
			// }else{
				// $termin.=$bar['tipe']."######".$bar['nourut']."######".number_format($bar['nilai'],2)."######".$bar['keterangan']."######".$bar['rptermin']."#$$$#";
			// }
		// }
		// echo $rupiah."########".$pajak."########".$termin;
	// break;
	case'insertdetail':
		$hdrD = spkHeader($notransaksi, 'ubah', true, true);
		if (!$hdrD) {
			exit("Warning : Silahkan simpan Header terlebih dahulu !");
		}
		#detail jenis ini datang dari rekap (angkut TBS, sewa HM) atau tidak dipakai
		if (in_array($hdrD['jenis'], array('ANGKUTTBS', 'SEWA.HM', 'HOLDING', 'JUALTBS'))) {
			exit("Warning : Jenis " . $hdrD['jenis'] . " tidak memakai detail kegiatan.");
		}
		if ($subunit == '' || $kegiatan == '' || $satuan == '' || $volume === '' || $total === '') {
			exit("Warning : Sub Unit, Kegiatan, Satuan, Volume, dan Total wajib diisi.");
		}
		if (strlen(checkPostGet('satuan', '')) > 12) {
			exit("Warning : Satuan maksimal 12 karakter.");
		}
		if ($volume <= 0) {
			exit("Warning : Volume harus lebih dari 0.");
		}
		if ($total < 0 || ($hk !== '' && $hk < 0)) {
			exit("Warning : Total dan Hari Kerja tidak boleh negatif.");
		}
		$stmtD = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_pengajuanspk_keg where notransaksi=? and subunit=? and kegiatan=?");
		$stmtD->execute(array($notransaksi, $subunit, $kegiatan));
		if ($stmtD->fetchColumn() > 0) {
			exit("Warning : Sub Unit dan Kegiatan ini sudah ada pada SPK.");
		}
	try {
		$owlPDO->beginTransaction();

		$str="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		if(count($res)==''){
			exit("Warning : Silahkan simpan Header terlebih dahulu !");
		}

		$str = "insert into " . $dbname . ".lgl_pengajuanspk_keg (`notransaksi`,`subunit`,`kegiatan`,`satuan`,`volume`,`total`,`hk`)
		values ('".$notransaksi."','".$subunit."','".$kegiatan."','".$satuan."','".$volume."','".$total."','".$hk."')";//exit('error'.$str);
		$owlPDO->exec($str);
		

		#ambil total
		$str="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$totalrupiah = $res[0]['total'];
		
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='rupiah' and nourut='1'";
		$res=fetchdata($str);
		if(count($res)>0){
			#update total rupiah
			$str="update ".$dbname.".lgl_pengajuanspkdt set nilai='".$totalrupiah."' where notransaksi='".$notransaksi."' and tipe='rupiah' and nourut='1'";
			$owlPDO->exec($str);			
		}else{
			$str = "insert into " . $dbname . ".lgl_pengajuanspkdt (`notransaksi`,`tipe`,`nourut`,`nilai`,`rptermin`,`keterangan`)
			values ('".$notransaksi."','rupiah','1','".$totalrupiah."','','')";//exit('error'.$str);
			$owlPDO->exec($str);
		}
		

		#update nilai termin
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='termin'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){

			$str="update ".$dbname.".lgl_pengajuanspkdt set rptermin='".$totalrupiah * $bar['nilai'] / 100 ."' where notransaksi='".$notransaksi."' and tipe='termin' and nourut='".$bar['nourut']."'";
			$owlPDO->exec($str);
			#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}

		$owlPDO->commit();
		
		echo $totalrupiah;
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;
	case'editdetail2':
	spkHeader($notransaksi, 'ubah', true);
		if ($total === '' || $total < 0 || ($hk !== '' && $hk < 0)) {
			exit("Warning : Total wajib diisi dan tidak boleh negatif.");
		}
		$stmtD = $owlPDO->prepare("select count(*) from " . $dbname . ".lgl_pengajuanspk_keg where notransaksi=? and subunit=? and kegiatan=?");
		$stmtD->execute(array($notransaksi, $subunit, $kegiatan));
		if ($stmtD->fetchColumn() == 0) {
			exit("Warning : Detail kegiatan tidak ditemukan.");
		}
	try {
		$owlPDO->beginTransaction();

		$hk = str_replace(',','',$hk);

		$str="update ".$dbname.".lgl_pengajuanspk_keg set total='".$total."',hk='".$hk."' where notransaksi='".$notransaksi."' and subunit='".$subunit."' and kegiatan='".$kegiatan."'";
		// echo $str;
		$owlPDO->exec($str);
		#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

		#ambil total
		$str="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$totalrupiah = $res[0]['total'];

		#update total rupiah
		$str="update ".$dbname.".lgl_pengajuanspkdt set nilai='".$totalrupiah."' where notransaksi='".$notransaksi."' and tipe='rupiah' and nourut='1'";
		$owlPDO->exec($str);
		#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

		#update nilai termin
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='termin'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){

			$str="update ".$dbname.".lgl_pengajuanspkdt set rptermin='".$totalrupiah * $bar['nilai'] / 100 ."' where notransaksi='".$notransaksi."' and tipe='termin' and nourut='".$bar['nourut']."'";
			$owlPDO->exec($str);
			#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}

		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}

		echo $totalrupiah;
	break;
	case'deldetail':
	spkHeader($notransaksi, 'ubah', true);
	try {
		$owlPDO->beginTransaction();

		$str = "delete from " . $dbname . ".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."' and kegiatan='".$kegiatan."' and subunit='".$subunit."'";
		$owlPDO->exec($str);
		#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

		#ambil total
		$str="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$totalrupiah = $res[0]['total'];

		#update total rupiah
		$str="update ".$dbname.".lgl_pengajuanspkdt set nilai='".$totalrupiah."' where notransaksi='".$notransaksi."' and tipe='rupiah' and nourut='1'";
		$owlPDO->exec($str);
		#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}

		#update nilai termin
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='termin'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){

			$str="update ".$dbname.".lgl_pengajuanspkdt set rptermin='".$totalrupiah * $bar['nilai'] / 100 ."' where notransaksi='".$notransaksi."' and tipe='termin' and nourut='".$bar['nourut']."'";
			$owlPDO->exec($str);
			#try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		}

		$owlPDO->commit();
		echo $totalrupiah;
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}

	break;
	// case'insertdt':
	// try {
		// $owlPDO->beginTransaction();
		// # Delete dulu
		// if($row==1){
			// $str = "delete from " . $dbname . ".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='".$jenis."'";
			// $owlPDO->exec($str);
			// #try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		// }
		// # Jika data sudah ada maka langsung Insert
		// //exit('error'.$rupiah);
		// //if($rupiah!=''){
			// $str = "insert into " . $dbname . ".lgl_pengajuanspkdt (`notransaksi`,`tipe`,`nourut`,`nilai`,`keterangan`,`rptermin`)
			// values ('".$notransaksi."','".$jenis."','".$nourut."','".floatval($rupiah)."','".$keterangan."','".floatval($rptermin)."')"; #exit('error'.$str);
			// $owlPDO->exec($str);
			// #try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		// //}

			// $owlPDO->commit();
		// } catch (PDOException $e) {
			// $owlPDO->rollback();
			// echo "Error, " . addslashes($e->getMessage());
			// die();
		// }
	// break;
	case'insert':
		spkCekPtUnit($pt, $unit);
		spkCekKategori($kategori);
		if (!preg_match('#^[0-9]{3,4}/SPK/' . $pt . '/' . $unit . '/[IVX]{1,5}/[0-9]{4}$#', $notransaksi)) {
			exit("Warning : Format nomor SPK tidak valid.");
		}
		$createbySPK = $_SESSION['standard']['userid'];
		$createtimeSPK = $todayhis;
		$hdrAda = spkHeader($notransaksi, 'ubah', true, true);
		if ($hdrAda) {
			if (isset($_SESSION['spkbaru'][$notransaksi])) {
				exit("Warning : Nomor SPK " . $notransaksi . " sudah dipakai SPK lain. Tutup form ini lalu buat SPK baru.");
			}
			if ($hdrAda['pt'] != $pt || $hdrAda['unit'] != $unit) {
				exit("Warning : PT dan Unit SPK tidak dapat diubah.");
			}
			$createbySPK = $hdrAda['createby'];
			$createtimeSPK = $hdrAda['createtime'];
		}
		#isian wajib dan format (sama dengan pengecekan di form)
		if ($tanggal == '--') {
			exit("Warning : Tanggal wajib diisi.");
		}
		if (trim($project) == '') {
			exit("Warning : Project wajib diisi.");
		}
		if (strlen(checkPostGet('project', '')) > 256) {
			exit("Warning : Project maksimal 256 karakter.");
		}
		if ($koderekanan == '') {
			exit("Warning : Kode Rekanan wajib diisi.");
		}
		foreach (array($tanggal, $tanggaldari, $tanggalsampai) as $tglX) {
			if ($tglX != '--' && !checkdate((int)substr($tglX, 5, 2), (int)substr($tglX, 8, 2), (int)substr($tglX, 0, 4))) {
				exit("Warning : Tanggal tidak valid.");
			}
		}
		if (($tanggaldari != '--') != ($tanggalsampai != '--')) {
			exit("Warning : Tanggal Mulai dan Tanggal Selesai harus diisi bersama.");
		}
		if ($tanggaldari != '--' && $tanggalsampai < $tanggaldari) {
			exit("Warning : Tanggal Selesai tidak boleh sebelum Tanggal Mulai.");
		}
		$retensi = rtrim($retensi, '.');
		if ($retensi !== '' && (!preg_match('/^[0-9]+(\.[0-9]+)?$/', $retensi) || $retensi > 100)) {
			exit("Warning : Retensi harus berupa angka 0 sampai 100.");
		}
		if ($rupiahttl !== '' && $rupiahttl < 0) {
			exit("Warning : Total nilai kontrak tidak boleh negatif.");
		}
	try {
		$owlPDO->beginTransaction();

		$tempdt = array();
		$str="select * from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$no=0;
		foreach($res as $key=>$val){
			$tempdt[$no]['notransaksi'] = $val['notransaksi'];
			$tempdt[$no]['subunit'] = $val['subunit'];
			$tempdt[$no]['kegiatan'] = $val['kegiatan'];
			$tempdt[$no]['satuan'] = $val['satuan'];
			$tempdt[$no]['volume'] = $val['volume'];
			$tempdt[$no]['total'] = $val['total'];
			$tempdt[$no]['hk'] = $val['hk'];
			$no++;
		}
		if($jenis!='JUALTBS'){
			if($divisi=='') {
				throw new PDOException("Divisi ".$_SESSION['lang']['kosong']);
			}
			if($jenis=='') {
				throw new PDOException("Jenis Supplier ".$_SESSION['lang']['kosong']);
			}
			
			$scek="select * from ".$dbname.".pmn_4customer where kodecustomer='".$koderekanan."'";
			$rcek=fetchData($scek);
			if(count($rcek)>0){
				throw new PDOException("".$_SESSION['lang']['koderekanan']." harus terisi Supplier, karena jenis :".$jenis);
			}
		}else{
			$scek="select * from ".$dbname.".pmn_4customer where kodecustomer='".$koderekanan."'";
			$rcek=fetchData($scek);
			if(count($rcek)==0){
				throw new PDOException("".$_SESSION['lang']['koderekanan']." harus terisi customer, karena jenis :".$jenis);
			}
		}

		if($jenissupplier==''){
			throw new PDOException("Jenis Asignment wajib diisi.");
		}

		#hapus header memotong detail lewat FK; pajak, termin, dan nilai rupiah yang lama disimpan dulu supaya tidak hilang
		$stmtLama = $owlPDO->prepare("select * from " . $dbname . ".lgl_pengajuanspkdt where notransaksi=? and tipe in ('pajak','termin','rupiah')");
		$stmtLama->execute(array($notransaksi));
		$dtLama = $stmtLama->fetchAll(PDO::FETCH_ASSOC);
		# Delete dulu
		$str = "delete from " . $dbname . ".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$owlPDO->exec($str);


		# Jika data sudah ada maka langsung Insert
		$str = "insert into " . $dbname . ".lgl_pengajuanspkht (`notransaksi`,`kategori`,`jenis`,`pt`,`unit`,`divisi`,`bagian`,`tanggal`,`koderekanan`,`perjanjianinduk`,`perjanjianperubahan`,`project`,`spesifikasi`,`jangkawaktu`,`tanggaldari`,`tanggalsampai`,`garansi`,`retensi`,`denda`,`statuspersetujuan`,`createby`,`createtime`,`updateby`,`jenissupplier`,`pendukung`,`notransaksiold`,`jlhhm`)
		values ('".$notransaksi."','".$kategori."','".$jenis."','".$pt."','".$unit."','".$divisi."','".$bagian."','".$tanggal."','".$koderekanan."','".$perjanjianinduk."','".$perjanjianperubahan."','".$project."','".$spesifikasi."','".$jangkawaktu."','".$tanggaldari."','".$tanggalsampai."','".$garansi."','".$retensi."','".$denda."','0','".$createbySPK."','".$createtimeSPK."','" . $_SESSION['standard']['userid'] . "','".$jenissupplier."','".$pendukung."','".$notransaksiold."','".$jlhhm."')";
		$owlPDO->exec($str);

		#kembalikan pajak (bila daftar di layar bukan milik SPK ini), termin, dan nilai rupiah (bila tidak diisi ulang di bawah)
		$pajakDariSesi = isset($_SESSION['pajak_untuk']) && $_SESSION['pajak_untuk'] === $notransaksi;
		$stmtKembali = $owlPDO->prepare("insert into " . $dbname . ".lgl_pengajuanspkdt (notransaksi,tipe,nourut,nilai,rptermin,keterangan) values (?,?,?,?,?,?)");
		foreach ($dtLama as $rowLama) {
			if ($rowLama['tipe'] == 'pajak' && $pajakDariSesi) {
				continue;
			}
			if ($rowLama['tipe'] == 'rupiah' && $rupiahttl != 0) {
				continue;
			}
			$stmtKembali->execute(array($rowLama['notransaksi'], $rowLama['tipe'], $rowLama['nourut'], $rowLama['nilai'], $rowLama['rptermin'], $rowLama['keterangan']));
		}


		if($tempdt!=array()){
			foreach($tempdt as $key=>$val){
				$str="insert into " . $dbname . ".lgl_pengajuanspk_keg (notransaksi,subunit,kegiatan,satuan,volume,total,hk)
					values ('".$notransaksi."','".$val['subunit']."','".$val['kegiatan']."','".$val['satuan']."','".$val['volume']."','".$val['total']."','".$val['hk']."')";
				$owlPDO->exec($str);
			}
		}else{
			if($jenis=='PROJECT'){
				$str="select * from ".$dbname.".project where kode='".$divisi."'";
				$res=fetchData($str);
				$kodecapex = $res[0]['kodecapex'];
				$subunit = $res[0]['kode'];

				if($kodecapex!=''){
					$str="select * from ".$dbname.".spl_capexbangunan where kode='".$kodecapex."'";
					$res=fetchData($str);
					$supplierid = $res[0]['kontraktor'];

					$str="select * from ".$dbname.".spl_tendercapex where kode='".$kodecapex."' and supplierid='".$supplierid."'";
					$res=fetchData($str);
					$idtender = $res[0]['id'];

					$str="select * from ".$dbname.".spl_tendercapexdt where tendercapexid='".$idtender."' order by tipedata asc";
					$res=fetchData($str);
					foreach($res as $key=>$val){
						$kegiatan = addZero($val['kodekegiatan'],8);
						$strx = "select * from ".$dbname.".spl_capexbangunandt where kegiatan='".$kegiatan."'";
						$resx = fetchData($strx);
						$satuan = $resx[0]['satuan'];
						if($val['tipedata']=='kegiatan'){
							$volume = $resx[0]['volume'];
							$total = round($volume*$val['hargasatuan'],2) + round($val['hk']*$val['rphk'],2);
						}else{
							$volume = $val['jumlah'];
							$total = round($volume*$val['hargasatuan'],2);
						}

						if($val['tipedata']=='kegiatan'){
							$strx = "insert into " . $dbname . ".lgl_pengajuanspk_keg (notransaksi,subunit,kegiatan,satuan,volume,total,hk)
							values ('".$notransaksi."','".$subunit."','".$kegiatan."','".$satuan."','".$volume."','".$total."','".$val['hk']."')";
							$owlPDO->exec($strx);
						}else{
							$strx="select * from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."' and kegiatan='".$kegiatan."'";
							$resx=fetchData($strx);
							$temptotal = $resx[0]['total'];
							$valtotal = $total + $temptotal;
							$strx="update ".$dbname.".lgl_pengajuanspk_keg set total='".$valtotal."' where notransaksi='".$notransaksi."' and kegiatan='".$kegiatan."'";
							$owlPDO->exec($strx);
						}
					}
				}else{
					$str="select * from ".$dbname.".project_dt where kodeproject='".$divisi."'";
					$res=fetchData($str);
					foreach($res as $key=>$val){
						$strx = "insert into " . $dbname . ".lgl_pengajuanspk_keg (notransaksi,subunit,kegiatan,satuan,volume,total)
						values ('".$notransaksi."','".$val['kodeproject']."','".$val['kegiatan']."','".$val['satuan']."','".$val['volume']."','0')";
						$owlPDO->exec($strx);
					}
				}
			}
		}



		if($rupiahttl!=0){
			$str = "delete from " . $dbname . ".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='rupiah'";
			$data = array(
				'notransaksi'=> $notransaksi,
				'tipe'       => 'rupiah',
				'nourut'     => '1',
				'nilai'      => $rupiahttl
			);

			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}
			$str = insertQuery($dbname,'lgl_pengajuanspkdt',$data,$cols);
			$owlPDO->exec($str);
		}

		// echo"<pre>";
		// print_r($_SESSION['pajak']);
		// echo"</pre>";
		if(isset($_SESSION['pajak_untuk']) && $_SESSION['pajak_untuk'] === $notransaksi){
			$str = "delete from " . $dbname . ".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='pajak'";
			$owlPDO->exec($str);

			$datapajak=array();
			foreach($_SESSION['pajak'] as $key=>$row){
				if($row['notransaksi'] == $notransaksi){
					$datapajak = array(
						'notransaksi'=> $notransaksi,
						'tipe'       => $row['tipe'],
						'nourut'     => $row['nourut'],
						'nilai'      => $row['nilai']
					);

					$colspajak = array();
					foreach($datapajak as $keyn=>$rown) {
							$colspajak[] = $keyn;
					}
					$str = insertQuery($dbname,'lgl_pengajuanspkdt',$datapajak,$colspajak);
					$owlPDO->exec($str);

					#unset($_SESSION['pajak'][$key]);
				}
			}
		}

		if(isset($_SESSION['nopol_untuk']) && $_SESSION['nopol_untuk'] === $notransaksi){
			$str = "delete from " . $dbname . ".log_spknopol where notransaksi='".$notransaksi."'";
			$owlPDO->exec($str);

			$data=array();
			foreach($_SESSION['nopol'] as $key=>$row){
				if($row['notransaksi'] == $notransaksi){
					$data = array(
						'notransaksi'=> $notransaksi,
						'nopol'      => $row['nopol'],
						'supir'      => $row['supir'],
						'createby'   => $_SESSION['standard']['userid'],
						'createdate' => $todayhis
					);

					$cols = array();
					foreach($data as $keyn=>$rown) {
							$cols[] = $keyn;
					}
					$str = insertQuery($dbname,'log_spknopol',$data,$cols);
					$owlPDO->exec($str);

					#unset($_SESSION['nopol'][$key]);
				}
			}
		}
		$owlPDO->commit();
			unset($_SESSION['spkbaru'][$notransaksi]);
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
		#ambil total
		$str="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$totalrupiah = $res[0]['total'];
		// exit('warning'.$totalrupiah);
		echo $totalrupiah;
	break;
	case'delete':
	spkHeader($notransaksi, 'ubah', true);
	$str = "delete from " . $dbname . ".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
	try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	# delete file
	$sql = "select * from " . $dbname . ".listfile_lgl_pengajuanspk where notransaksi='".$notransaksi."'";
	$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$str="delete from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi='".$notransaksi."'and namafile='".$bar['namafile']."'";
		try{$owlPDO->exec($str);
			$pathx = $path.basename($bar['namafile']);
			if(is_file($pathx)){unlink($pathx);}
		}catch (PDOException $e) {print " Gagal	 !: " . $e->getMessage() . "\n";die();}
	}
	break;
	case'loaddata':
	$where = spkWhereList();

	$limit = 20;
	$page = 0;
	$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
	if (isset($_POST['page'])) {
		$page = intval($_POST['page']);
		if ($page < 0)
		$page = 0;
	}
	$offset = $page * $limit;
	$maxdisplay = ($page * $limit);
	$sql = "SELECT * FROM " . $dbname . ".lgl_pengajuanspkht where 1=1 " . $where . "";
	$res = fetchData($sql);
	$jlhbrs = count($res);
	$no = 0;
	$str = "SELECT * FROM " . $dbname . ".lgl_pengajuanspkht where 1=1 " . $where . " order by createtime desc limit " . $offset . "," . $limit . ""; //exit("error $str");
	$tab = "";
	$no = $maxdisplay;
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$row=$res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if(empty($row)){
		$tab.="<tr class=rowcontent><td colspan=20 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		while ($bar = $res->fetch()) {
			$nmsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['koderekanan']."'");
			$nmcus=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer',"kodecustomer='".$bar['koderekanan']."'");
			$strx = "SELECT sum(nilai) as nilai FROM " . $dbname . ".lgl_pengajuanspkdt	 where notransaksi='".$bar['notransaksi']."' and tipe='rupiah'";$arrtipe=getEnum($dbname,'lgl_pengajuanspkht','jenis');
			$resx = fetchData($strx);
			$isi = '';
			$no+=1;
			$a=$no%2;
			$xx='';
			if($a==1){
				//$xx.=" style=background-color:#F5EEF8 ";
			}
			
			$xxx='';
			if($bar['statuspersetujuan']==3){
				$xxx=" style=background-color:red ";
			}
			if($bar['statuspersetujuan']==1){
				$xxx=" style=background-color:green ";
			}
			if($bar['statuspersetujuan']==0){
				$xxx=" style=background-color:yellow ";
			}

			$sqlapp = "SELECT notransaksi FROM " . $dbname . ".approval where notransaksi='".$bar['notransaksi']."' and status='0'";
			$resapp = fetchData($sqlapp);

			## CEK SPK
			$sttSpk = "";
			$sttBapp = "";
			$optSpk = makeOption($dbname,'log_spkht','nopengajuan,notransaksi',"nopengajuan='".$bar['notransaksi']."'");
			if(@$optSpk[$bar['notransaksi']]!=''){
				$sttSpk = "v";
				$optBapp = makeOption($dbname,'log_baspk','notransaksi,notransaksi',"notransaksi='".$optSpk[$bar['notransaksi']]."'");
				if($optBapp[$optSpk[$bar['notransaksi']]]!=''){
					$sttBapp = "v";
				}
			}


			$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
			$tab.="<td align=center>" . $no . "</td>";
			$tab.="<td>" . $bar['pt'] ."</td>";
			$tab.="<td nowrap>" . $bar['unit'] . " - " . $nmorg[$bar['unit']] . "</td>";
			$tab.="<td>" . $bar['notransaksi'] . "</td>";
			$tab.="<td align=center nowrap>" . tanggalnormal($bar['tanggal']) . "</td>";
			if (@$nmsup[$bar['koderekanan']]!='') {
				$tab.="<td>" . @$nmsup[$bar['koderekanan']] . "</td>";
			}else{
				$tab.="<td>" . @$nmcus[$bar['koderekanan']] . "</td>";
			}
			$tab.="<td align=center>" . $arrtipe[$bar['jenis']] . "</td>";
			$tab.="<td>" . htmlspecialchars(strtoupper($bar['project'])) . "</td>";
			#$tab.="<td>" . $bar['perjanjianinduk'] . "</td>";
			$tab.="<td align=right>".hidezerodecimal($resx[0]['nilai'])."</td>";

			if($bar['pendukung']==1){
				$e="Hanya Pendukung";$q="style=background-color:yellow";
			}else{
				$q=$e="";
			}

			$tab.="<td hidden align=center ".$q.">".$e."</td>";

			if($bar['close']==1){
				$e="\nCLOSE";$q="style=background-color:red";
			}else{
				$q=$e="";
			}
			
			if($bar['statuspersetujuan']==0 and $bar['posting']==0){
				$tab.="<td align=center nowrap>Belum diajukan".$e."</td>";
			}else{
				$tab.="<td align=center nowrap align=left ".$q." ".$xxx.">".$arrHsl[$bar['statuspersetujuan']]."".$e."</td>";
			}

			$tab.="<td align=center>".$sttSpk."</td>";
			$tab.="<td align=center style=cursor:pointer;color:blue;font-weight:bold; title=\"Click untuk melihat BAPP\" onclick=\"viewdetailbapp('".$optBapp[$optSpk[$bar['notransaksi']]]."','".$bar['unit']."','viewhtml','event')\">".$sttBapp."</td>";
			$tab.="<td align=left>" . $nmkar[$bar['updateby']] . "</td>";

			#ambil total
			$stri="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$bar['notransaksi']."'";
			$resi=fetchData($stri);
			if($resi[0]['total'] != 0 || $resi[0]['total'] == ''){
				$totalrupiah = $resi[0]['total'];
			}else{
				$totalrupiah = 0;
			}
			if($bar['posting']==0){
				$isi.="<td align=center width=20px><img src=images/application/application_edit.png class=zImgBtn	title='Edit'
				onclick=\"fillfield('".$bar['notransaksi']."');\" ></td>";

				$isi.="<td align=center width=20px><img class=zImgBtn src=images/application/application_delete.png onclick=\"del('".$bar['notransaksi']."');\" title='Delete'></td>";

				$isi.="<td align=center width=20px><img src=images/skyblue/submit.jpg class=zImgBtn class=zImgBtn height='30'  title='Ajukan ???'
				onclick=\"form_ajukan_spk('" . $bar['notransaksi'] . "','" . $bar['unit'] . "','" . $no . "','".$totalrupiah."');\" ></td>";
			}elseif($bar['close']==0 and $bar['statuspersetujuan']==1){
				$isi.="<td width=20px></td><td width=20px></td>
				<td  align=center width=20px><img src=images/icons/book_previous.png class=zImgBtn class=zImgBtn height='30'  title='Close ???'
				onclick=\"form_tutup('".$bar['notransaksi']."','".$bar['unit']."','".$no."');\" ></td>";
			}else{
				$isi.="<td width=20px></td><td width=20px></td>";
				$isi.="<td width=20px>".htmlspecialchars($bar['alasanclose'])."</td>";
			}

			$isi.="<td align=center width=20px><img src=images/zoom.png class=zImgBtn	title='View' onclick=\"html('".$bar['notransaksi']."','html');\"></td>";
			$isi.="<td align=center width=20px><img src=images/pdf.jpg class=zImgBtn	title='View' onclick=\"html('".$bar['notransaksi']."','pdf');\"></td>";
			$isi.="<td align=center width=20px><img src=images/excel.jpg class=zImgBtn	title='Excel' onclick=\"exportSPK('".$bar['notransaksi']."','excel');\"></td>";

			if ($optBapp[$optSpk[$bar['notransaksi']]]!='') {
				$isi.="<td align=center width=20px><img src=images/upload-2-xxl.png class=zImgBtn	title='Upload' onclick=\"showupload('event','".$bar['notransaksi']."','1');\"></td>";
			}else{
				$isi.="<td align=center  width=20px></td>";
			}

			$tab.=$isi;
			$tab.="</tr>";
		}
	}
	$totrows = ceil($jlhbrs / $limit);
	if ($totrows == 0) {
		$totrows = 1;
	}
	$isiRow = '';
	for ($er = 1; $er <= $totrows; $er++) {
		$sel = ($page == $er - 1) ? 'selected' : '';
		$isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
	}
	$footd = createpaging($jlhbrs,$limit,$page,'20','loaddata','getPage');
	echo $tab . "####" . $footd;
	break;
	case'fillfield':
		spkHeader($notransaksi, 'ubah', true);
		$str="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$hsl['notransaksi'] 		= $bar['notransaksi'];
			$hsl['kategori'] 			= $bar['kategori'];
			$hsl['jenis'] 				= $bar['jenis'];
			$hsl['pt'] 					= $bar['pt'];
			$hsl['unit'] 				= $bar['unit'];
			$hsl['divisi'] 				= $bar['divisi'];
			$hsl['bagian'] 				= $bar['bagian'];
			$hsl['tanggal'] 			= tanggalnormal($bar['tanggal']);
			$hsl['koderekanan']			= $bar['koderekanan'];
			$hsl['perjanjianinduk'] 	= $bar['perjanjianinduk'];
			$hsl['perjanjianperubahan'] = $bar['perjanjianperubahan'];
			$hsl['project'] 			= $bar['project'];
			$hsl['spesifikasi'] 		= $bar['spesifikasi'];
			$hsl['jangkawaktu'] 		= $bar['jangkawaktu'];
			$hsl['tanggaldari']			= tanggalnormal($bar['tanggaldari']);
			$hsl['tanggalsampai']		= tanggalnormal($bar['tanggalsampai']);
			$hsl['garansi']				= $bar['garansi'];
			$hsl['retensi']				= $bar['retensi'];
			$hsl['denda']				= $bar['denda'];
			$hsl['jenissupplier']		= $bar['jenissupplier'];
			$hsl['pendukung']			= $bar['pendukung'];
			$hsl['notransaksiold'] 		= $bar['notransaksiold'];
			$hsl['jlhhm']				= $bar['jlhhm'];
		}

		## Cek kategori jika orang HO tidak bisa edit SPK KEBUN, dan begitu juga kebalikan nya (Biar gak dikira bug)
		if($hsl['kategori'] == 'LOKAL' and $_SESSION['empl']['tipelokasitugas'] == 'HOLDING' ){
				exit("Warning: Lokasi tugas Holding tidak dapat edit SPK kategori Lokal ");
		}	

		if($hsl['kategori'] == 'PUSAT' and $_SESSION['empl']['tipelokasitugas'] != 'HOLDING' ){
				exit("Warning: Lokasi tugas Holding tidak dapat edit SPK kategori Lokal ");
		}	

		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."' and tipe='rupiah' and nourut='1'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$hsl['nilai']=number_format($bar['nilai'],2);
		}

		$_SESSION['pajak']=array();
		$_SESSION['pajak_untuk'] = $notransaksi;
		$str="select * from ".$dbname.".lgl_pengajuanspkdt where notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			if($bar['tipe']=='pajak'){
				$_SESSION['pajak'][]=array(
					'notransaksi'=>$bar['notransaksi'],
					'tipe'       =>$bar['tipe'],
					'nourut'     =>$bar['nourut'],
					'nilai'      =>$bar['nilai']
				);
			}
		}

		$hsl['pajak']="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$hsl['pajak'].="<thead><tr class=rowheader style=text-align:center>";
		$hsl['pajak'].="<td>".$_SESSION['lang']['namaakun']."</td>";
		$hsl['pajak'].="<td>".$_SESSION['lang']['pajak']." (%)</td>";
		$hsl['pajak'].="<td width=18px>#</td>";
		$hsl['pajak'].="</tr>";
		$hsl['pajak'].="</thead>";
		$hsl['pajak'].="<tbody>";

		foreach($_SESSION['pajak'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$optakun=makeOption($dbname,'keu_5akun','noakun,namaakun',"noakun='".$row['nourut']."'");

				$hsl['pajak'].="<tr class='rowcontent'>";
				$hsl['pajak'].="<td style='text-align:left'>".$optakun[$row['nourut']]."</td>";
				$hsl['pajak'].="<td style='text-align:right'>".$row['nilai']."</td>";
				$hsl['pajak'].="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletepajak('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$hsl['pajak'].="</tr>";
			}
		}
		$hsl['pajak'].="</tbody>";
		$hsl['pajak'].="</table>";

		$_SESSION['nopol']=array();
		$_SESSION['nopol_untuk'] = $notransaksi;
		$str="select * from ".$dbname.".log_spknopol where notransaksi='".$notransaksi."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$_SESSION['nopol'][]=array(
				'notransaksi'=>$bar['notransaksi'],
				'nopol'      =>$bar['nopol'],
				'supir'      =>$bar['supir']
			);
		}


		$hsl['nopol']="<table border=0 cellpadding=1 cellspacing=1 class=sortable width=100%>";
		$hsl['nopol'].="<thead><tr class=rowheader style=text-align:center>";
		$hsl['nopol'].="<td>".$_SESSION['lang']['nopol']."</td>";
		$hsl['nopol'].="<td>".$_SESSION['lang']['sopir']."</td>";
		$hsl['nopol'].="<td width=18px>#</td>";
		$hsl['nopol'].="</tr>";
		$hsl['nopol'].="</thead>";
		$hsl['nopol'].="<tbody>";

		foreach($_SESSION['nopol'] as $key => $row){
			if($row['notransaksi'] == $param['notransaksi']){
				$hsl['nopol'].="<tr class='rowcontent'>";
				$hsl['nopol'].="<td style='text-align:left'>".$row['nopol']."</td>";
				$hsl['nopol'].="<td style='text-align:left'>".$row['supir']."</td>";
				$hsl['nopol'].="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletenopol('".$key."','".$row['notransaksi']."')\" src='images/delete_32.png'/>
				</td>";
				$hsl['nopol'].="</tr>";
			}
		}
		$hsl['nopol'].="</tbody>";
		$hsl['nopol'].="</table>";


		#ambil total
		$str="select sum(total) as total from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchData($str);
		$hsl['totalrupiah'] = $res[0]['total'];
		
		// echo $n1."##".$n2."##".$pajak."##".$tab."##".$totalrupiah;
		echo json_encode($hsl);
	break;
	case'form_tutup':
		$hdrT = spkHeader($notransaksi, 'ubah');
		if ($hdrT['posting'] != '1' || $hdrT['statuspersetujuan'] != '1' || $hdrT['close'] == '1') {
			exit("Warning : SPK hanya dapat ditutup bila sudah disetujui dan belum ditutup.");
		}
		$unit = $hdrT['unit'];
		$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<input hidden id=notran_tutup value=".$notransaksi.">
					<input hidden id=unit_tutup value=".$unit.">
					<td colspan=2><textarea rows='2' maxlength=1024 id=alasanclose type=text onkeypress=\"return tanpa_kutip(event)\" style=\"width:255px;\"></textarea></td>
				</tr>
				<tr class=rowcontent>
					<input id=numrow_tutup style=display:none value=".$numrow.">
					<td colspan=2 align=center>
					<button class=mybutton onclick=tutup()>" . $_SESSION['lang']['close'] . "</button></td>
				</tr>
				</table>";

        echo $tab;

	break;
	case'tutup':
		$hdrT = spkHeader($notransaksi, 'ubah');
		if ($hdrT['posting'] != '1' || $hdrT['statuspersetujuan'] != '1' || $hdrT['close'] == '1') {
			exit("Warning : SPK hanya dapat ditutup bila sudah disetujui dan belum ditutup.");
		}
		$unit = $hdrT['unit'];
		if($alasanclose==''){
			exit("Warning : Alasan Harus diisi !");
		}

		#cek apakah sudah ada di spk
		$str="select * from ".$dbname.".log_spkht where notransaksi='".$notransaksi."' and kodeorg='".$unit."'";
		$res=fetchdata($str);
		if(count($res)>0){
			#exit("Warning : Surat Perjanjian Kerja (SPK) sudah dibuat, Proses di Batalkan !");
		}

		#cek apakah sudah ada di bapp
		$str="select * from ".$dbname.".log_baspk where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		if(count($res)>0){
			exit("Warning : BAPP sudah dibuat, Proses di Batalkan !");
		}

		try {
			$owlPDO->beginTransaction();
			$owlPDO->exec("update ".$dbname.".log_spkht set posting='2' where notransaksi='".$notransaksi."'");
			$owlPDO->exec("update " . $dbname . ".lgl_pengajuanspkht set close='1',alasanclose='".$alasanclose."' where notransaksi = '" . $notransaksi . "'");
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollBack();
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	case'form_ajukan';
		$hdr = spkHeader($notransaksi, 'ubah', true);
		$unit = $hdr['unit'];
		$optKry = "";
		foreach (spkDaftarPenyetuju($hdr, spkNilai($notransaksi)) as $idKry => $labelKry) {
			$optKry .= "<option value='" . $idKry . "'>" . htmlspecialchars($labelKry) . "</option>";
		}
		$dok = spkDataDokumen($notransaksi);
		$jmlKeg = count($dok['keg']);
		$barisInfo = array(
			'No Transaksi' => "<span id=notran_aju>" . htmlspecialchars($notransaksi) . "</span>",
			'Tanggal' => htmlspecialchars($dok['tanggal']),
			'Kategori / Jenis' => htmlspecialchars($hdr['kategori'] . ' / ' . $hdr['jenis']),
			'Perusahaan' => htmlspecialchars($dok['pt']),
			'Unit' => htmlspecialchars($dok['unit']),
			'Project' => htmlspecialchars($hdr['project']),
			'Rekanan' => htmlspecialchars($dok['rekanan']),
			'Periode' => htmlspecialchars($dok['periode']),
			'Nilai (Rp)' => "<b>" . number_format(spkNilai($notransaksi)) . "</b>",
			'Jumlah Kegiatan' => $jmlKeg . ($jmlKeg == 0 && !in_array($hdr['jenis'], array('ANGKUTTBS', 'SEWA.HM', 'HOLDING', 'JUALTBS')) ? " <font color=red>(detail kegiatan belum ada)</font>" : "")
		);
		#tabel berborder (kotak-kotak) tanpa efek hover baris
		$selLabel = "padding:5px 8px;border:1px solid #8fb2d6;background:#d4e4f5;white-space:nowrap;";
		$selIsi = "padding:5px 8px;border:1px solid #8fb2d6;background:#eef5fc;";
		$tab = "<table cellspacing=0 cellpadding=0 border=0 width=100% style='border-collapse:collapse;'>";
		foreach ($barisInfo as $labelInfo => $isiInfo) {
			$tab .= "<tr><td width=130px style='" . $selLabel . "'>" . $labelInfo . "</td><td style='" . $selIsi . "'>" . $isiInfo . "</td></tr>";
		}
		if ($optKry == '') {
			$tab .= "<tr><td style='" . $selLabel . "'>" . $_SESSION['lang']['kepada'] . "</td><td style='" . $selIsi . "'><font color=red>Belum ada penyetuju yang terdaftar untuk unit ini.</font></td></tr>";
		} else {
			$tab .= "<tr><td style='" . $selLabel . "'>" . $_SESSION['lang']['kepada'] . "</td><td style='" . $selIsi . "'><select id=kepada style='width:100%;'>".$optKry."</select></td></tr>";
		}
		$tab .= "</table>
				<input id=numrow style=display:none value=".$numrow.">
				<div style='padding-top:10px;text-align:center;'><button id=tomboldetail class=mybutton onclick=ajukan() " . ($optKry == '' ? 'disabled' : '') . ">" . $_SESSION['lang']['diajukan'] . "</button></div>";

        echo $tab;
	break;
	case'getJenisSup':
		if($notransaksi!=''){
			$sSub="select * from ".$dbname.".lgl_pengajuanspkht where notransaksi='".$notransaksi."'";
			$rSub=fetchData($sSub);
			$isiHt=$rSub[0];
		}
		$optjenis=$optspk="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$strkel="select a.tipe,b.kode, b.noakun from ".$dbname.".log_5supkelompok a left join ".$dbname.".log_5klsupplier b on a.tipe=b.tipe where a.supplierid = '".$koderekanan."' and status='1'";
        // exit('warning : '.$strkel);
        $reskel=fetchData($strkel);
        foreach ($reskel as $key => $barkel) {
            if($isiHt['jenissupplier']==$barkel['tipe']){
                $optjenis.="<option value='".$barkel['tipe']."' selected>".$barkel['kode']." - ".getNamaAkun($barkel['noakun'])." (".$barkel['noakun'].")</option>";
            }else{
                $optjenis.="<option value='".$barkel['tipe']."'>".$barkel['kode']." - ".getNamaAkun($barkel['noakun'])." (".$barkel['noakun'].")</option>";
            }
        }
		
		$strspk="select notransaksi from ".$dbname.".lgl_pengajuanspkht where jenis = '".$jenis."'";
		//echo $strspk;
		$rtrcust=fetchData($strspk);
		foreach ($rtrcust as $key => $val) {
			$optspk.="<option value=".$val['notransaksi'].">".$val['notransaksi']."</option>";
		}
        echo $optjenis."##".$optspk;
	break;

    case'ajukan':
	$hdrA = spkHeader($notransaksi, 'ubah', true);
	if ($kepada == '') {
		exit("Warning : Isikan nama penyetuju.");
	}
	$kepadaAsli = null;
	foreach (spkDaftarPenyetuju($hdrA, spkNilai($notransaksi)) as $idPenyetuju => $labelPenyetuju) {
		if ((int)$idPenyetuju === (int)$kepada) {
			$kepadaAsli = $idPenyetuju;
		}
	}
	if ($kepadaAsli === null) {
		exit("Warning : Penyetuju tidak valid untuk SPK ini.");
	}
	$kepada = $kepadaAsli;

	#= cari unit
	$str = "select * from " . $dbname . ".lgl_pengajuanspkht  where notransaksi = '" . $notransaksi . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
		$jenis=$bar['jenis'];
		$pendukung=$bar['pendukung'];


	try {
	$owlPDO->beginTransaction();
		$str="select * from ".$dbname.".lgl_pengajuanspk_keg where notransaksi='".$notransaksi."'";
		$res=fetchdata($str);
		if(($jenis!='HOLDING' and $jenis!='JUALTBS') and ($jenis!='ANGKUTTBS' && $jenis != 'SEWA.HM')){
			// if($pendukung!='1'){
				if(count($res)==''){
					throw new PDOException('Detail kegiatan tidak ada.');
				}
				if (spkNilai($notransaksi) <= 0) {
					throw new PDOException('Nilai SPK masih 0, lengkapi Total pada detail kegiatan.');
				}
			// }
		}

		if($kepada=='' or $notransaksi==''){
			throw new PDOException('Isikan nama penyetuju.');
		}
		//update flag menjadi 1
        $str = "update " . $dbname . ".lgl_pengajuanspkht set posting='1' where notransaksi = '" . $notransaksi . "'";
		$owlPDO->exec($str);
		//insert ke table approval
		$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`,
                `level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
            values ('','".$notransaksi."','SPK','1','" . $kepada."','0','','','')";
		$owlPDO->exec($str);
		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
	break;

	case'loaddatadetail':
	spkHeader($notransaksi, 'baca', false, true);
	$tab = "";
	$tab.= "<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>";
	$tab.= "<thead>";
	$tab.= "<tr class=rowheader>";
	$tab.= "<th align=center>No</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['subunit'] . "</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['kegiatan'] . "</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['satuan'] . "</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['hk'] . "</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['volume'] . "</th>";
	$tab.= "<th align=center>Rp / Sat</th>";
	$tab.= "<th align=center>" . $_SESSION['lang']['total'] . "</th>";
	$tab.= "<th align=center colspan=2 >Action</th>";
	$tab.= "</tr>";
	$tab.= "</thead>";

	$no = 0;
	$str = "SELECT * FROM " . $dbname . ".lgl_pengajuanspk_keg	 where 1=1 and notransaksi='" . $notransaksi . "'"; //exit("error $str");
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$row=$res->rowCount();
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if(empty($row)){
		$tab.="<tr class=rowcontent><td colspan=9 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		$nmkeg='';
		while ($bar = $res->fetch()) {
			$nmkeg=makeOption($dbname,'setup_kegiatan','kodekegiatan,namakegiatan',"kodekegiatan='".$bar['kegiatan']."'");
			$isi = '';
			$no+=1;
			$a=$no%2;
			$xx='';
			if($a==1){
				#$xx.=" style=background-color:#F5EEF8";
			}
			$namakegiatan = '';
			## CEK PROJECT
			$strx="select * from ".$dbname.".project where kode='".$bar['subunit']."'";
			$resx=fetchData($strx);
			$namaproject = $resx[0]['nama'];
			$kodecapex = $resx[0]['kodecapex'];

			if($kodecapex==''){
				$strx="select * from ".$dbname.".project_dt where kegiatan='".$bar['kegiatan']."'";
				$resx=fetchData($strx);
				$namakegiatan = $resx[0]['namakegiatan'];
			}else{
				$strx="select * from ".$dbname.".spl_capexbangunandt where kegiatan='".$bar['kegiatan']."'";
				$resx=fetchData($strx);
				$namakegiatan = $resx[0]['namakegiatan'];
			}

			$tab.="<tr class=rowcontent ".$xx." id=tr_$no>";
			$tab.="<td align=center>" . $no . "</td>";
			if(getNopol($bar['subunit'],'kodevhc')!=''){
				$tab.="<td>" . $bar['subunit'] . " - " .getNopol($bar['subunit']). "</td>";
			}else{				
				$tab.="<td>" . $bar['subunit'] . " - " .($nmorg[$bar['subunit']]==''?($namaproject==''?spkNamaBlok($bar['subunit']):$namaproject):$nmorg[$bar['subunit']]). "</td>";
			}
			
			$tab.="<td>" . $bar['kegiatan'] . " - " . ($nmkeg[$bar['kegiatan']]==''?$namakegiatan:$nmkeg[$bar['kegiatan']]) . "</td>";
			$tab.="<td align=center>" . $bar['satuan'] . "</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['hk'])."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['volume'])."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['total']/$bar['volume'])."</td>";
			$tab.="<td align=right>".hidezerodecimal($bar['total'])."</td>";

			$ttl+=$bar['total'];

			if($kodecapex==''){
				if($namakegiatan==''){
					$tab.="<td align=center width=20px>";
					$tab.="<img class=zImgBtn src=images/application/application_edit.png onclick=\"editdetail2('".$bar['notransaksi']."','".$bar['subunit']."','".$bar['kegiatan']."','".$bar['satuan']."','".$bar['volume']."','".$bar['total']."','".$bar['hk']."');\" title='Edit'></td>";
					$tab.="<td align=center width=20px>";
					$tab.="<img class=zImgBtn src=images/application/application_delete.png onclick=\"deldetail('".$bar['notransaksi']."','".$bar['subunit']."','".$bar['kegiatan']."');\" title='Delete'>";
					$tab.="</td>";
				}else{
					$tab.="<td align=center colspan=2 width=20px>";
					$tab.="<img class=zImgBtn src=images/application/application_edit.png onclick=\"editdetail2('".$bar['notransaksi']."','".$bar['subunit']."','".$bar['kegiatan']."','".$bar['satuan']."','".$bar['volume']."','".$bar['total']."','".$bar['hk']."');\" title='Edit'>";
					$tab.="</td>";
				}
			}
			$tab.="</tr>";
		}


		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=7>TOTAL</td>";
		$tab.="<td align=right>".hidezerodecimal($ttl)."</td>";
		$tab.="<td align=right></td>";
		$tab.="<td align=right></td>";
		$tab.="</tr>";
	}
	$tab.= "</table>";

	echo $tab;
	break;

case 'showupload':
		spkHeader($notransaksi, 'ubah', false, true);
		$optkriteria = "";
		$optf = "";
		foreach (getmodulefil($emodul) as $key => $val) {
			$opsi = "<option value='" . $key . "'>" . htmlspecialchars($val['kriteria']) . "</option>";
			if ($key == $jnsupspkfinal) {
				$optf = $opsi;
			} else {
				$optkriteria .= $opsi;
			}
		}
		//upload dari daftar (SPK final) memakai kriteria SPK final bila ada
		if ($jenisupload == '1' && $optf != '') {
			$optkriteria = $optf;
		}

		$tab="";
		$tab.="<fieldset><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>Kriteria</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>". $optkriteria."</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td></td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>Format " . implode(', ', $formatUpload) . ", maksimal " . ($maksUpload / 1048576) . " MB</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile('".$notransaksi."','".$jenisupload."')\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>
			<p />";

		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Kriteria</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";

		echo $tab;
	break;
case 'submitfile':
	//melebihi post_max_size: PHP mengosongkan seluruh $_POST dan $_FILES
	if (empty($_FILES) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
		exit("Warning : Ukuran file melebihi batas, maksimal " . ($maksUpload / 1048576) . " MB.");
	}
	if($notransaksi==''){
		exit("Warning : Silahkan isikan detail transaksi terlebih dahulu.");
	}
	#cek data
	if (!spkHeader($notransaksi, 'ubah', false, true)) {
		exit('Warning : Silahkan isikan dan save detail transaksi terlebih dahulu.');
	}
	$arrkriteria = getmodulefil($emodul);
	if ($kriteriaefil == '' || $kriteriaefil == '0') {
		$kriteriaefil = 'others';
	}
	if (!isset($arrkriteria[$kriteriaefil])) {
		exit("Warning : Kriteria file tidak valid.");
	}
	$stmtF = $owlPDO->prepare("select count(*) from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi=? and status='1' and termin=''");
	$stmtF->execute(array($notransaksi));
	if ($stmtF->fetchColumn() >= 10) {
		exit("Warning : Limit upload hanya 10 file.");
	}
	if (!isset($_FILES['file']) || !is_array($_FILES['file']) || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE) {
		exit("Warning : Silahkan pilih file yang akan diupload.");
	}
	$errUpload = $_FILES['file']['error'];
	if ($errUpload == UPLOAD_ERR_INI_SIZE || $errUpload == UPLOAD_ERR_FORM_SIZE) {
		exit("Warning : Ukuran file melebihi batas, maksimal " . ($maksUpload / 1048576) . " MB.");
	}
	if ($errUpload != UPLOAD_ERR_OK) {
		exit("Warning : Upload file gagal (kode " . $errUpload . "), silahkan coba lagi.");
	}
	if ($_FILES['file']['size'] > $maksUpload) {
		exit("Warning : Ukuran file melebihi batas, maksimal " . ($maksUpload / 1048576) . " MB.");
	}
	$filetype = strtolower('.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
	if (!in_array($filetype, $formatUpload)) {
		exit("Warning : Format file upload harus " . implode(', ', $formatUpload) . ".");
	}
	if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
		exit("Warning : Upload file tidak valid.");
	}
	//nama file tetap dikenali dari nama aslinya, ditambah penanda unik supaya tidak menimpa file SPK lain
	$namaAsli = preg_replace('/[^A-Za-z0-9 ._\-\(\)]/', '_', pathinfo($_FILES['file']['name'], PATHINFO_FILENAME));
	$namaAsli = trim(substr($namaAsli, 0, 80));
	if ($namaAsli == '') {
		$namaAsli = 'SPK';
	}
	$filename = $namaAsli . '_' . date('Ymd_His') . '_' . substr(md5(uniqid('', true)), 0, 6) . $filetype;
	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}
	if (!move_uploaded_file($_FILES['file']['tmp_name'], $path . $filename)) {
		exit("Gagal, file tidak dapat disimpan.");
	}
	try {
		$owlPDO->prepare("insert into ".$dbname.".listfile_lgl_pengajuanspk (notransaksi,namafile,formaticon,kriteriaefil,status,createdby,createdtime,termin) values (?,?,?,?,'1',?,?,'')")
			->execute(array($notransaksi, $filename, $filetype, $kriteriaefil, $_SESSION['standard']['userid'], date('Y-m-d H:i:s')));
	} catch (PDOException $e) {
		unlink($path . $filename);
		echo " Gagal," . addslashes($e->getMessage());
	}
	break;
case 'loadfiles':
	spkHeader($notransaksi, 'baca', false, true);
	$no = 0;
	$tab = "";
	$arrkriteria = getmodulefil($emodul);
	$sqlF = "select * from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi=? and status='1'";
	if ($jenisupload != '1') {
		$sqlF .= " and termin=''";
	}
	$stmtF = $owlPDO->prepare($sqlF . " order by id");
	$stmtF->execute(array($notransaksi));
	$res = $stmtF->fetchAll(PDO::FETCH_ASSOC);
	if(empty($res)){
		$tab.="<tr class=rowcontent><td colspan=5 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		foreach($res as $key=>$val){
			$no++;
			$icon=seticonfile($val['formaticon']);
			$nfile = htmlspecialchars($val['namafile']);
			$jsFile = htmlspecialchars(addslashes($val['namafile']), ENT_QUOTES);
			$linkFile = htmlspecialchars($path . rawurlencode($val['namafile']), ENT_QUOTES);
			$namaKriteria = isset($arrkriteria[$val['kriteriaefil']]) ? $arrkriteria[$val['kriteriaefil']]['kriteria'] : 'Others';
			$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>
					<td style='text-align:center'>
						<a href='".$linkFile."' download><img src=".$icon." class=zImgBtn></a>
					</td>
					<td style='text-align:left'>".htmlspecialchars($namaKriteria)."</td>
					<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$jsFile."')\">".$nfile."</td>
					<td align=center>
						<a href='".$linkFile."' download><img src=images/uploader/dwnld8.png class=zImgBtn	 title='download'></a>&nbsp";
			$tab.="<img src=images/application/application_delete.png class=zImgBtn	 title='delete' onclick=\"deletefile('".$val['notransaksi']."','".$jsFile."','".$jenisupload."');\" >";
			$tab.="</td>
				</tr>";
		}
	}
	echo $tab;
	break;
	case'viewfile':
	$tab="";
	$stmtF = $owlPDO->prepare("select notransaksi from ".$dbname.".listfile_lgl_pengajuanspk where namafile=? and status='1' limit 1");
	$stmtF->execute(array(basename($namafileAsli)));
	$barisF = $stmtF->fetch(PDO::FETCH_ASSOC);
	if (!$barisF) {
		exit("Warning : File tidak ditemukan.");
	}
	spkHeader($barisF['notransaksi'], 'baca');
	$tab.="<img src='".htmlspecialchars($path.rawurlencode(basename($namafileAsli)), ENT_QUOTES)."' style='width:600px;height:400px;'>";
	echo $tab;
	break;
case 'deletefile':
	spkHeader($notransaksi, 'ubah');
	$str="delete from ".$dbname.".listfile_lgl_pengajuanspk where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
	try{
		$owlPDO->exec($str);
		$stmtF = $owlPDO->prepare("select count(*) from ".$dbname.".listfile_lgl_pengajuanspk where namafile=?");
		$stmtF->execute(array(basename($namafileAsli)));
		$pathx = $path.basename($namafileAsli);
		#file hanya dihapus bila tidak dipakai SPK lain (nama file asli bisa sama antar SPK)
		if ($stmtF->fetchColumn() == 0 && is_file($pathx)) {
			unlink($pathx);
		}
	}
	catch(PDOException $e){
		echo " Gagal," . addslashes($e->getMessage());
	}
	break;

case 'excellist':
		$rows = spkDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$kolom = 13;
		$stream = "<table>
			<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
			<tr><td colspan=" . $kolom . "><b>PENGAJUAN SPK</b></td></tr>
			<tr><td colspan=" . $kolom . ">" . htmlspecialchars(spkInfoFilter()) . "</td></tr>
			<tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
			<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
			</table>";
		$stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
			<thead><tr>
				<th align=center bgcolor=#CCCCCC>No</th>
				<th align=center bgcolor=#CCCCCC>PT</th>
				<th align=center bgcolor=#CCCCCC>Unit</th>
				<th align=center bgcolor=#CCCCCC>Nomor SPK</th>
				<th align=center bgcolor=#CCCCCC>Tanggal</th>
				<th align=center bgcolor=#CCCCCC>Rekanan</th>
				<th align=center bgcolor=#CCCCCC>Jenis</th>
				<th align=center bgcolor=#CCCCCC>Project</th>
				<th align=center bgcolor=#CCCCCC>Nilai (Rp)</th>
				<th align=center bgcolor=#CCCCCC>Status</th>
				<th align=center bgcolor=#CCCCCC>SPK</th>
				<th align=center bgcolor=#CCCCCC>BAPP</th>
				<th align=center bgcolor=#CCCCCC>Dibuat Oleh</th>
			</tr></thead><tbody>";
		$no = 0;
		foreach ($rows as $r) {
			$no++;
			$stream .= "<tr>
				<td align=center>" . $no . "</td>
				<td align=center>" . htmlspecialchars($r['pt']) . "</td>
				<td align=center>" . htmlspecialchars($r['unit']) . "</td>
				<td>" . htmlspecialchars($r['notransaksi']) . "</td>
				<td align=center>" . tanggalnormal($r['tanggal']) . "</td>
				<td>" . htmlspecialchars($r['namarekanan']) . "</td>
				<td align=center>" . htmlspecialchars($r['jenis']) . "</td>
				<td>" . htmlspecialchars($r['project']) . "</td>
				<td align=right>" . round($r['nilaispk']) . "</td>
				<td>" . htmlspecialchars(spkLabelStatus($r)) . "</td>
				<td align=center>" . ($r['nospk'] != '' ? 'v' : '') . "</td>
				<td align=center>" . ($r['adabapp'] ? 'v' : '') . "</td>
				<td>" . htmlspecialchars(isset($nmkar[$r['updateby']]) ? $nmkar[$r['updateby']] : '') . "</td>
			</tr>";
		}
		if ($no == 0) {
			$stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
		}
		$stream .= "</tbody></table>";

		$xls = new HtmlExcel();
		$xls->addSheet("Pengajuan SPK", $stream);
		$xls->headers("Pengajuan_SPK_" . date('YmdHis') . ".xls");
		echo $xls->buildFile();
		exit;

case 'pdflist':
		require_once('lib/fpdf.php');
		$rows = spkDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$infoFilter = spkInfoFilter();

		if (!class_exists('PDFListSPK')) {
			class PDFListSPK extends FPDF
			{
				public $colW = array();

				function Header()
				{
					global $hdpt, $infoFilter;
					$width = $this->w - $this->lMargin - $this->rMargin;
					if (file_exists($hdpt['logo'])) {
						$this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 18);
					}
					$this->SetFont('Arial', 'B', 11);
					$this->SetXY($this->lMargin + 22, $this->tMargin + 2);
					$this->Cell(160, 6, utf8_decode($hdpt['nama']), 0, 1, 'L');
					$this->SetY($this->tMargin + 15);
					$this->SetFont('Arial', 'B', 13);
					$this->Cell($width, 7, 'PENGAJUAN SPK', 0, 1, 'C');
					$this->SetFont('Arial', '', 8);
					$this->Cell($width, 5, utf8_decode($infoFilter), 0, 1, 'C');
					$this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
					$this->Ln(1);
					$this->SetFillColor(220, 220, 220);
					$this->SetFont('Arial', 'B', 8);
					$judul = array('No', 'PT', 'Unit', 'Nomor SPK', 'Tanggal', 'Rekanan', 'Jenis', 'Project', 'Nilai (Rp)', 'Status', 'SPK', 'BAPP', 'Dibuat Oleh');
					foreach ($judul as $i => $j) {
						$this->Cell($this->colW[$i], 6, $j, 1, 0, 'C', true);
					}
					$this->Ln();
				}

				function Footer()
				{
					$this->SetY(-12);
					$this->SetFont('Arial', 'I', 7);
					$this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
				}
			}
		}

		$pdf = new PDFListSPK('L', 'mm', 'A4');
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(false, 16);
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$colFixed = array(7, 9, 12, 44, 18, 38, 16, 40, 26, 22, 8, 8); #semua kolom kecuali Dibuat Oleh
		$pdf->colW = array_merge($colFixed, array($width - array_sum($colFixed)));
		$pdf->AddPage();
		$h = 6;
		$no = 0;
		foreach ($rows as $r) {
			$no++;
			if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
				$pdf->AddPage();
			}
			$c = $pdf->colW;
			$pdf->SetFont('Arial', '', 7);
			$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
			$pdf->Cell($c[1], $h, $r['pt'], 1, 0, 'C');
			$pdf->Cell($c[2], $h, $r['unit'], 1, 0, 'C');
			$pdf->Cell($c[3], $h, spkFit($pdf, $r['notransaksi'], $c[3]), 1, 0, 'L');
			$pdf->Cell($c[4], $h, tanggalnormal($r['tanggal']), 1, 0, 'C');
			$pdf->Cell($c[5], $h, spkFit($pdf, $r['namarekanan'], $c[5]), 1, 0, 'L');
			$pdf->Cell($c[6], $h, spkFit($pdf, $r['jenis'], $c[6]), 1, 0, 'C');
			$pdf->Cell($c[7], $h, spkFit($pdf, $r['project'], $c[7]), 1, 0, 'L');
			$pdf->Cell($c[8], $h, number_format($r['nilaispk']), 1, 0, 'R');
			$pdf->Cell($c[9], $h, spkFit($pdf, spkLabelStatus($r), $c[9]), 1, 0, 'L');
			$pdf->Cell($c[10], $h, $r['nospk'] != '' ? 'v' : '', 1, 0, 'C');
			$pdf->Cell($c[11], $h, $r['adabapp'] ? 'v' : '', 1, 0, 'C');
			$pdf->Cell($c[12], $h, spkFit($pdf, isset($nmkar[$r['updateby']]) ? $nmkar[$r['updateby']] : '', $c[12]), 1, 1, 'L');
		}
		if ($no == 0) {
			$pdf->Cell(array_sum($pdf->colW), $h, 'Data tidak ditemukan', 1, 1, 'C');
		}
		$pdf->Output();
		exit;
case 'pdfSPK':
		require_once('lib/fpdf.php');
		$d = spkDataDokumen($notransaksi);
		$hdr = $d['hdr'];
		$hdpt = setheadreport($hdr['pt'], $hdr['pt']);

		if (!class_exists('PDFDokSPK')) {
			class PDFDokSPK extends FPDF
			{
				public $hdpt = array();
				public $dicetak = '';
				public $wKeg = array(8, 38, 52, 12, 14, 16, 20, 20);

				function Header()
				{
					$w = $this->w - $this->lMargin - $this->rMargin;
					$y0 = $this->tMargin;
					if (file_exists($this->hdpt['logo'])) {
						$this->Image($this->hdpt['logo'], $this->lMargin, $y0, 18);
					}
					$this->SetXY($this->lMargin + 22, $y0 + 1);
					$this->SetFont('Arial', 'B', 13);
					$this->Cell($w - 22, 6, utf8_decode(strtoupper($this->hdpt['nama'])), 0, 1, 'L');
					$this->SetX($this->lMargin + 22);
					$this->SetFont('Arial', '', 8);
					$kontak = trim((string)$this->hdpt['alamat'] . ($this->hdpt['telepon'] != '' ? '  Telp. ' . $this->hdpt['telepon'] : '') . ($this->hdpt['fax'] != '' ? '  Fax. ' . $this->hdpt['fax'] : ''));
					$this->MultiCell($w - 22, 4, utf8_decode($kontak), 0, 'L');
					$yGaris = max($this->GetY(), $y0 + 19) + 1;
					$this->SetLineWidth(0.6);
					$this->Line($this->lMargin, $yGaris, $this->w - $this->rMargin, $yGaris);
					$this->SetLineWidth(0.2);
					$this->SetY($yGaris + 4);
				}

				function Footer()
				{
					$this->SetY(-14);
					$this->SetFont('Arial', 'I', 7);
					$this->Cell(0, 5, utf8_decode($this->dicetak), 0, 0, 'L');
					$this->SetX($this->lMargin);
					$this->Cell(0, 5, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
				}

				function Cukup($h)
				{
					return ($this->GetY() + $h <= $this->PageBreakTrigger);
				}

				#jumlah baris yang dibutuhkan teks pada lebar tertentu (dari contoh resmi FPDF)
				function NbLines($w, $txt)
				{
					$cw = &$this->CurrentFont['cw'];
					if ($w == 0) {
						$w = $this->w - $this->rMargin - $this->x;
					}
					$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
					$s = str_replace("\r", '', $txt);
					$nb = strlen($s);
					if ($nb > 0 && $s[$nb - 1] == "\n") {
						$nb--;
					}
					$sep = -1;
					$i = 0;
					$j = 0;
					$l = 0;
					$nl = 1;
					while ($i < $nb) {
						$ch = $s[$i];
						if ($ch == "\n") {
							$i++;
							$sep = -1;
							$j = $i;
							$l = 0;
							$nl++;
							continue;
						}
						if ($ch == ' ') {
							$sep = $i;
						}
						$l += $cw[$ch];
						if ($l > $wmax) {
							if ($sep == -1) {
								if ($i == $j) {
									$i++;
								}
							} else {
								$i = $sep + 1;
							}
							$sep = -1;
							$j = $i;
							$l = 0;
							$nl++;
						} else {
							$i++;
						}
					}
					return $nl;
				}

				function Bagian($judul)
				{
					if (!$this->Cukup(14)) {
						$this->AddPage();
					}
					$this->Ln(2);
					$this->SetFont('Arial', 'B', 9);
					$this->SetFillColor(225, 232, 240);
					$this->Cell(0, 6, utf8_decode($judul), 0, 1, 'L', true);
					$this->Ln(1);
				}

				#satu baris "label : isi" dengan isi yang bisa lebih dari satu baris
				function KV($label, $isi, $tebal = false)
				{
					$lw = 44;
					$iw = $this->w - $this->lMargin - $this->rMargin - $lw - 4;
					$isi = ($isi === '' || $isi === null) ? '-' : $isi;
					$this->SetFont('Arial', $tebal ? 'B' : '', 9);
					$nb = $this->NbLines($iw, utf8_decode($isi));
					if (!$this->Cukup(5 * $nb)) {
						$this->AddPage();
					}
					$y = $this->GetY();
					$this->SetFont('Arial', '', 9);
					$this->SetXY($this->lMargin, $y);
					$this->Cell($lw, 5, utf8_decode($label), 0, 0, 'L');
					$this->Cell(4, 5, ':', 0, 0, 'C');
					$this->SetFont('Arial', $tebal ? 'B' : '', 9);
					$this->MultiCell($iw, 5, utf8_decode($isi), 0, 'L');
				}

				function HeaderKeg()
				{
					$this->SetFont('Arial', 'B', 8);
					$this->SetFillColor(225, 232, 240);
					$judul = array('No', 'Sub Unit', 'Kegiatan', 'Satuan', 'HK', 'Volume', 'Rp / Sat', 'Total');
					foreach ($judul as $i => $j) {
						$this->Cell($this->wKeg[$i], 6, $j, 1, 0, 'C', true);
					}
					$this->Ln();
				}

				#baris tabel dengan tinggi mengikuti sel yang paling panjang
				function Baris($cells, $widths, $aligns, $h = 4.6)
				{
					$this->SetFont('Arial', '', 8);
					$nb = 1;
					foreach ($cells as $i => $c) {
						$nb = max($nb, $this->NbLines($widths[$i], $c));
					}
					$hh = $h * $nb;
					if (!$this->Cukup($hh)) {
						$this->AddPage();
						if (count($widths) == count($this->wKeg)) {
							$this->HeaderKeg();
						}
					}
					$x = $this->lMargin;
					$y = $this->GetY();
					foreach ($cells as $i => $c) {
						$this->Rect($x, $y, $widths[$i], $hh);
						$this->SetXY($x, $y);
						$this->MultiCell($widths[$i], $h, $c, 0, $aligns[$i]);
						$x += $widths[$i];
					}
					$this->SetXY($this->lMargin, $y + $hh);
				}

				function Fit($txt, $w, $f = 8, $gaya = '')
				{
					$txt = utf8_decode($txt);
					$this->SetFont('Arial', $gaya, $f);
					if ($this->GetStringWidth($txt) <= $w - 2) {
						return $txt;
					}
					while (strlen($txt) > 1 && $this->GetStringWidth($txt . '...') > $w - 2) {
						$txt = substr($txt, 0, -1);
					}
					return rtrim($txt) . '...';
				}

				#kotak pembuat / persetujuan digital
				function Kotak($x, $y, $w, $h, $judul, $kode, $nama, $jabatan, $tanggal, $komentar)
				{
					$warna = array(
						'dibuat' => array(array(227, 242, 253), array(21, 101, 192), 'DIBUAT', ''),
						'1' => array(array(232, 245, 233), array(27, 94, 32), 'DISETUJUI', 'Disetujui secara digital'),
						'2' => array(array(255, 243, 224), array(230, 81, 0), 'DIKOREKSI', ''),
						'3' => array(array(255, 235, 238), array(183, 28, 28), 'DITOLAK', ''),
						'0' => array(array(245, 245, 245), array(97, 97, 97), 'MENUNGGU', '')
					);
					$w0 = isset($warna[$kode]) ? $warna[$kode] : $warna['0'];
					$this->SetDrawColor(120, 120, 120);
					$this->Rect($x, $y, $w, $h);
					$this->SetFillColor(230, 230, 230);
					$this->SetXY($x, $y);
					$this->SetFont('Arial', 'B', 7.5);
					$this->SetTextColor(0, 0, 0);
					$this->Cell($w, 5.5, utf8_decode($judul), 1, 0, 'C', true);
					$this->SetFillColor($w0[0][0], $w0[0][1], $w0[0][2]);
					$this->SetXY($x, $y + 5.5);
					$this->Rect($x, $y + 5.5, $w, 11, 'F');
					$this->Rect($x, $y + 5.5, $w, 11);
					$this->SetTextColor($w0[1][0], $w0[1][1], $w0[1][2]);
					$this->SetFont('Arial', 'B', 9);
					$this->SetXY($x, $y + 6.5);
					$this->Cell($w, 5, $w0[2], 0, 0, 'C');
					if ($w0[3] != '') {
						$this->SetFont('Arial', 'I', 6.5);
						$this->SetXY($x, $y + 11.5);
						$this->Cell($w, 4, $w0[3], 0, 0, 'C');
					}
					$this->SetTextColor(0, 0, 0);
					$this->SetXY($x, $y + 18);
					$this->SetFont('Arial', 'B', 8);
					$this->Cell($w, 4.5, $this->Fit($nama, $w, 8, 'B'), 0, 2, 'C');
					$this->SetFont('Arial', 'I', 7);
					$this->Cell($w, 4, $this->Fit($jabatan, $w, 7, 'I'), 0, 2, 'C');
					$this->SetFont('Arial', '', 7);
					$this->Cell($w, 4, utf8_decode($tanggal), 0, 2, 'C');
					if ($komentar != '') {
						$this->SetFont('Arial', 'I', 6.5);
						$this->Cell($w, 4, $this->Fit('"' . $komentar . '"', $w, 6.5, 'I'), 0, 2, 'C');
					}
					$this->SetDrawColor(0, 0, 0);
				}
			}
		}

		$pdf = new PDFDokSPK('P', 'mm', 'A4');
		$pdf->hdpt = $hdpt;
		$pdf->dicetak = 'Dicetak oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s');
		$pdf->SetMargins(15, 12, 15);
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(true, 18);
		$pdf->AddPage();
		$lebar = $pdf->w - $pdf->lMargin - $pdf->rMargin;

		$pdf->SetFont('Arial', 'B', 14);
		$pdf->Cell(0, 7, 'PENGAJUAN SPK', 0, 1, 'C');
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(0, 5, 'Nomor : ' . $notransaksi, 0, 1, 'C');

		$pdf->Bagian('DATA UMUM');
		$pdf->KV('Kategori', $hdr['kategori']);
		$pdf->KV('Jenis', $hdr['jenis']);
		$pdf->KV('Perusahaan', $d['pt']);
		$pdf->KV('Unit', $d['unit']);
		$pdf->KV('Divisi', $hdr['divisi']);
		$pdf->KV('Tanggal', $d['tanggal']);
		$pdf->KV('Nama Project / Nama SPK', $hdr['project']);
		$pdf->KV('Rekanan', $d['rekanan']);
		$pdf->KV('Jenis Assignment', $d['klsup']);
		$pdf->KV('Periode Pekerjaan', $d['periode']);
		$pdf->KV('Jangka Waktu', $hdr['jangkawaktu']);
		$pdf->KV('Retensi (%)', $hdr['retensi']);
		$pdf->KV('SPK Lama', $hdr['notransaksiold']);
		if ($hdr['jenis'] == 'SEWA.HM' || $hdr['jlhhm'] > 0) {
			$pdf->KV('Jumlah HM', spkAngka($hdr['jlhhm']));
		}

		$pdf->Bagian('NILAI KONTRAK DAN PAJAK');
		$pdf->KV('Total Nilai Kontrak (Rp)', $d['nilai'] > 0 ? spkAngka($d['nilai']) : '', true);
		if (count($d['pajak']) == 0) {
			$pdf->KV('Pajak (%)', '');
		}
		foreach ($d['pajak'] as $i => $pj) {
			$pdf->KV($i == 0 ? 'Pajak (%)' : '', $pj['nama'] . ' (' . $pj['akun'] . ') = ' . spkAngka($pj['persen']) . ' %');
		}

		$pdf->Bagian('SPESIFIKASI PEKERJAAN');
		$pdf->SetFont('Arial', '', 9);
		$spek = trim(str_replace("\r", '', $hdr['spesifikasi']));
		$pdf->MultiCell(0, 5, utf8_decode($spek == '' ? '-' : $spek), 0, 'L');

		if (count($d['keg']) > 0) {
			$pdf->Bagian('RINCIAN KEGIATAN');
			$pdf->HeaderKeg();
			$no = 0;
			foreach ($d['keg'] as $r) {
				$no++;
				$pdf->Baris(array($no, utf8_decode($r['subunit']), utf8_decode($r['kegiatan']), utf8_decode($r['satuan']), spkAngka($r['hk']), spkAngka($r['volume']), spkAngka($r['rpsat']), number_format($r['total'])), $pdf->wKeg, array('C', 'L', 'L', 'C', 'R', 'R', 'R', 'R'));
			}
			$pdf->SetFont('Arial', 'B', 8);
			$pdf->Cell(array_sum($pdf->wKeg) - $pdf->wKeg[7], 6, 'TOTAL', 1, 0, 'C');
			$pdf->Cell($pdf->wKeg[7], 6, number_format($d['totalkeg']), 1, 1, 'R');
		}

		if (count($d['nopol']) > 0) {
			$pdf->Bagian('DAFTAR KENDARAAN');
			$wNp = array(10, 50, 80, 40);
			$pdf->SetFont('Arial', 'B', 8);
			$pdf->SetFillColor(225, 232, 240);
			foreach (array('No', 'No Polisi', 'Sopir', 'Status') as $i => $j) {
				$pdf->Cell($wNp[$i], 6, $j, 1, 0, 'C', true);
			}
			$pdf->Ln();
			$no = 0;
			foreach ($d['nopol'] as $r) {
				$no++;
				$pdf->Baris(array($no, $r['nopol'], utf8_decode($r['supir']), $r['status']), $wNp, array('C', 'L', 'L', 'C'));
			}
		}

		//persetujuan
		$jmlKotak = 1 + count($d['approval']);
		if (!$pdf->Cukup(50)) {
			$pdf->AddPage();
		}
		$pdf->Bagian('PERSETUJUAN');
		$gap = 3;
		$wKotak = min(58, ($lebar - $gap * ($jmlKotak - 1)) / $jmlKotak);
		$tinggi = 38;
		$x = $pdf->lMargin;
		$y = $pdf->GetY() + 1;
		$pdf->Kotak($x, $y, $wKotak, $tinggi, 'Dibuat oleh', 'dibuat', $d['dibuat']['nama'], '', $d['dibuat']['waktu'], '');
		foreach ($d['approval'] as $ap) {
			$x += $wKotak + $gap;
			$pdf->Kotak($x, $y, $wKotak, $tinggi, 'Persetujuan ' . $ap['level'], $ap['status'], $ap['nama'], $ap['jabatan'], $ap['tanggal'], $ap['komentar']);
		}
		$pdf->SetY($y + $tinggi + 2);
		if (count($d['approval']) == 0) {
			$pdf->SetFont('Arial', 'I', 8);
			$pdf->Cell(0, 5, 'SPK ini belum diajukan untuk persetujuan.', 0, 1, 'L');
		}
		$pdf->Output();
		exit;

case 'excelSPK':
		$d = spkDataDokumen($notransaksi);
		$hdr = $d['hdr'];
		$hdpt = setheadreport($hdr['pt'], $hdr['pt']);
		$e = function ($v) {
			return htmlspecialchars((string)$v);
		};
		$stream = "<table>
			<tr><td colspan=8><b>" . $e($hdpt['nama']) . "</b></td></tr>
			<tr><td colspan=8>" . $e(trim((string)$hdpt['alamat'] . ' ' . ($hdpt['telepon'] != '' ? 'Telp. ' . $hdpt['telepon'] : ''))) . "</td></tr>
			<tr><td colspan=8><b>PENGAJUAN SPK</b></td></tr>
			<tr><td colspan=8>Nomor : " . $e($notransaksi) . "</td></tr>
			<tr><td colspan=8>Ditarik oleh " . $e($_SESSION['empl']['name']) . " (" . $e($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
			<tr><td colspan=8>&nbsp;</td></tr>
			</table>";
		$kv = function ($label, $isi) use ($e) {
			return "<tr><td colspan=2 bgcolor=#EEEEEE>" . $e($label) . "</td><td colspan=6 align=left>" . $e($isi) . "</td></tr>";
		};
		$stream .= "<table cellspacing=1 cellpadding=3 border=1>
			<tr><td colspan=8 bgcolor=#CCCCCC><b>DATA UMUM</b></td></tr>"
			. $kv('Kategori', $hdr['kategori']) . $kv('Jenis', $hdr['jenis']) . $kv('Perusahaan', $d['pt']) . $kv('Unit', $d['unit'])
			. $kv('Divisi', $hdr['divisi']) . $kv('Tanggal', $d['tanggal']) . $kv('Nama Project / Nama SPK', $hdr['project'])
			. $kv('Rekanan', $d['rekanan']) . $kv('Jenis Assignment', $d['klsup']) . $kv('Periode Pekerjaan', $d['periode'])
			. $kv('Jangka Waktu', $hdr['jangkawaktu']) . $kv('Retensi (%)', $hdr['retensi']) . $kv('SPK Lama', $hdr['notransaksiold'])
			. $kv('Jumlah HM', $hdr['jlhhm'] > 0 ? spkAngka($hdr['jlhhm']) : '')
			. "<tr><td colspan=8 bgcolor=#CCCCCC><b>NILAI KONTRAK DAN PAJAK</b></td></tr>
			<tr><td colspan=2 bgcolor=#EEEEEE>Total Nilai Kontrak (Rp)</td><td colspan=6 align=left>" . round($d['nilai']) . "</td></tr>";
		if (count($d['pajak']) == 0) {
			$stream .= $kv('Pajak (%)', '');
		}
		foreach ($d['pajak'] as $i => $pj) {
			$stream .= $kv($i == 0 ? 'Pajak (%)' : '', $pj['nama'] . ' (' . $pj['akun'] . ') = ' . spkAngka($pj['persen']) . ' %');
		}
		$stream .= "<tr><td colspan=8 bgcolor=#CCCCCC><b>SPESIFIKASI PEKERJAAN</b></td></tr>
			<tr><td colspan=8>" . nl2br($e($hdr['spesifikasi'])) . "</td></tr>
			</table><br />";
		if (count($d['keg']) > 0) {
			$stream .= "<table cellspacing=1 cellpadding=3 border=1>
				<tr><td colspan=8 bgcolor=#CCCCCC><b>RINCIAN KEGIATAN</b></td></tr>
				<tr>
					<th bgcolor=#EEEEEE>No</th><th bgcolor=#EEEEEE>Sub Unit</th><th bgcolor=#EEEEEE>Kegiatan</th><th bgcolor=#EEEEEE>Satuan</th>
					<th bgcolor=#EEEEEE>Hari Kerja</th><th bgcolor=#EEEEEE>Volume</th><th bgcolor=#EEEEEE>Rp / Sat</th><th bgcolor=#EEEEEE>Total</th>
				</tr>";
			$no = 0;
			foreach ($d['keg'] as $r) {
				$no++;
				$stream .= "<tr><td align=center>" . $no . "</td><td>" . $e($r['subunit']) . "</td><td>" . $e($r['kegiatan']) . "</td><td align=center>" . $e($r['satuan']) . "</td>
					<td align=right>" . $r['hk'] . "</td><td align=right>" . $r['volume'] . "</td><td align=right>" . round($r['rpsat'], 2) . "</td><td align=right>" . round($r['total'], 2) . "</td></tr>";
			}
			$stream .= "<tr><td colspan=7 align=center><b>TOTAL</b></td><td align=right><b>" . round($d['totalkeg'], 2) . "</b></td></tr></table><br />";
		}
		if (count($d['nopol']) > 0) {
			$stream .= "<table cellspacing=1 cellpadding=3 border=1>
				<tr><td colspan=4 bgcolor=#CCCCCC><b>DAFTAR KENDARAAN</b></td></tr>
				<tr><th bgcolor=#EEEEEE>No</th><th bgcolor=#EEEEEE>No Polisi</th><th bgcolor=#EEEEEE>Sopir</th><th bgcolor=#EEEEEE>Status</th></tr>";
			$no = 0;
			foreach ($d['nopol'] as $r) {
				$no++;
				$stream .= "<tr><td align=center>" . $no . "</td><td>" . $e($r['nopol']) . "</td><td>" . $e($r['supir']) . "</td><td align=center>" . $r['status'] . "</td></tr>";
			}
			$stream .= "</table><br />";
		}
		$arrStatusDok = array('0' => 'Menunggu', '1' => 'Disetujui secara digital', '2' => 'Dikoreksi', '3' => 'Ditolak');
		$stream .= "<table cellspacing=1 cellpadding=3 border=1>
			<tr><td colspan=6 bgcolor=#CCCCCC><b>PERSETUJUAN</b></td></tr>
			<tr><th bgcolor=#EEEEEE>Tahap</th><th bgcolor=#EEEEEE>Nama</th><th bgcolor=#EEEEEE>Jabatan</th><th bgcolor=#EEEEEE>Status</th><th bgcolor=#EEEEEE>Tanggal</th><th bgcolor=#EEEEEE>Komentar</th></tr>
			<tr><td>Dibuat oleh</td><td>" . $e($d['dibuat']['nama']) . "</td><td></td><td>Dibuat</td><td>" . $e($d['dibuat']['waktu']) . "</td><td></td></tr>";
		foreach ($d['approval'] as $ap) {
			$stream .= "<tr><td>Persetujuan " . $ap['level'] . "</td><td>" . $e($ap['nama']) . "</td><td>" . $e($ap['jabatan']) . "</td><td>" . $arrStatusDok[$ap['status']] . "</td><td>" . $e($ap['tanggal']) . "</td><td>" . $e($ap['komentar']) . "</td></tr>";
		}
		$stream .= "</table>";

		$xls = new HtmlExcel();
		$xls->addSheet("SPK", $stream);
		$xls->headers("SPK_" . preg_replace('/[^A-Za-z0-9]/', '', $notransaksi) . "_" . date('YmdHis') . ".xls");
		echo $xls->buildFile();
		exit;
case'carinoperbandingan':
	$nmpro=makeOption($dbname,'project','kode,nama');
	$optSupplierCr="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$str="select b.supplierid,b.notransaksi,a.keterangan from ".$dbname.".lgl_penawaranharga a left join ".$dbname.".lgl_penawaranhargadt b on a.notransaksi=b.notransaksi and a.nourut=b.nourut where tanggal='".$tanggalsurat."' and statuspersetujuan='1' and flag='1'";
	$res=fetchData($str);
	$form="<fieldset><legend><i>".$_SESSION['lang']['result']."</i></legend>
			<div>
				<table class=sortable cellpadding=2 cellspacing=1>
					<tr class=rowheader>
						<td align=center>".$_SESSION['lang']['notransaksi']."</td>
						<td align=center>".$_SESSION['lang']['kodesupplier']."</td>
					</tr>";
					if(count($res)>0){
						foreach ($res as $v) {
							$form.="<tr class=rowcontent style='cursor:pointer' onclick=\"getrekanan('".$v['notransaksi']."','".$v['supplierid']."','".$nmpro[$v['keterangan']]."');\">
								<td align=center>".$v['notransaksi']."</td>
								<td align=center>".getNamaSupplier($v['supplierid'])."</td>
							</tr>";
						}
					}else{
						$form.="<tr class=rowcontent><td colspan=2 align=center><i>".$_SESSION['lang']['errdatanotexist']." perbandingan harga project pada tanggal </i> <b>".tglnmbln($tanggalsurat,'I','long')."</b></td></tr>";
					}
				$form.="
				</table>
			</div>
			</fieldset>";
	echo $form;
    break;
}
function encrypt( $q, $key='') {
	if($key!=''){
		$cryptKey = md5($key);
	}else{
		$cryptKey = '87774318AA8719589D26D02FDEB5F79B1EC6A98C';
	}
    $qEncoded = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
    return( $qEncoded );
}
function decrypt( $q, $key='') {
    if($key!=''){
		$cryptKey = md5($key);
	}else{
		$cryptKey = '87774318AA8719589D26D02FDEB5F79B1EC6A98C';
	}
    $qDecoded = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
    return( $qDecoded );
}

# Fungsi di pakai pada saat menyimpan hasil dari <textarea></textarea>
# $a => value yang akan di rubah
# $x => akan di replace menggunakan ??, default = ####
/* function replaceEnter($a, $x="####"){
	$a = nl2br($a);
	$i = explode('<br />',$a);
	$no =''; $t='';
	foreach($i as $r => $e){
		$no+=1;
		if($no < count($i)){
			$t.=trim($e).$x;
		}else{
			$t.=trim($e);
		}
	}
	return $t;
} */
?>