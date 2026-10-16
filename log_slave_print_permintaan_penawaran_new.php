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

$column = isset($_GET['column']) ? $_GET['column'] : '';
if ($column == '' or !preg_match('/^[A-Za-z0-9\/._ -]+$/', $column)) {
	exit('Nomor transaksi tidak valid');
}
$notransaksi = $column;
$urlefil = checkPostGet('urlefil', '0');

#teks aman untuk HTML dan UTF-8 (karakter tidak valid membuat dompdf menghasilkan PDF kosong)
function pdfSafe($t)
{
	$t = iconv('UTF-8', 'UTF-8//IGNORE', (string)$t);
	return htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
}
function pdfTgl($t)
{
	$t = trim((string)$t);
	if ($t == '' or substr($t, 0, 4) == '0000') {
		return '-';
	}
	return tanggalnormal(substr($t, 0, 10));
}
function pdfRp($v)
{
	return number_format((float)$v, 2, '.', ',');
}

#--- nomor penawaran (DPH) yang masuk RPH ini; bila nomor yang dikirim bukan RPH, dianggap nomor DPH langsung
$stmt = $owlPDO->prepare("select distinct nomor from " . $dbname . ".log_permintaanhargadt where norph=?");
$stmt->execute(array($notransaksi));
$daftarNomor = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (count($daftarNomor) == 0) {
	$daftarNomor = array($notransaksi);
}
$phNomor = implode(',', array_fill(0, count($daftarNomor), '?'));

#--- seluruh penawaran (semua supplier, semua barang) yang tidak ditolak
$stmt = $owlPDO->prepare("select a.nomor,a.nourut,a.kodebarang,a.nopp,a.jumlah,a.harga,a.score,a.tanggalverifikasi,b.supplierid,b.purchaser,b.tanggal,b.diskonpersen,b.nilaidiskon,b.ppn,b.pbbkb,b.sisbayar2,b.id_franco,b.catatan,b.catatanmenang,b.nilaipermintaan,b.deliverytime,b.tgltempopembayaran,b.posisistok,b.picverifikasi,b.waktuverifikasi,b.nilai1s,b.nilai2s,b.nilai3s,b.nilai4s,b.nilai5s,b.nilai1f,b.nilai2f,b.nilai3f,b.nilai4f,b.nilai5f from " . $dbname . ".log_permintaanhargadt a left join " . $dbname . ".log_perintaanhargaht b on a.nomor=b.nomor and a.nourut=b.nourut where a.nomor in (" . $phNomor . ") and b.tolakrph='0' order by b.nomor, b.nourut, a.kodebarang");
$stmt->execute($daftarNomor);
$semua = $stmt->fetchAll(PDO::FETCH_ASSOC);

$optnmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optnamabarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$optsatuan = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan');
$optnamasupplier = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
$opttop = makeOption($dbname, 'log_5syaratbayar', 'kode,keterangan');
$franconame = makeOption($dbname, 'setup_franco', 'id_franco,franco_name');
$kary_id = makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan');
$jabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');

$supplier = array();   #penawaran per supplier (satu baris per nomor+nourut)
$barang = array();     #daftar barang: kode => array(nopp, jumlah)
$harga = array();      #harga[kodebarang][kunciSupplier] = baris
$menang = array();     #menang[kodebarang] = kunciSupplier pemenang
foreach ($semua as $r) {
	$ks = $r['nomor'] . '|' . $r['nourut'];
	if (!isset($supplier[$ks])) {
		$supplier[$ks] = $r;
	}
	if (!isset($barang[$r['kodebarang']])) {
		$barang[$r['kodebarang']] = array('nopp' => $r['nopp'], 'jumlah' => $r['jumlah']);
	}
	$harga[$r['kodebarang']][$ks] = $r;
	if ($r['score'] == '1') {
		$menang[$r['kodebarang']] = $ks;
	}
}

#--- identitas dokumen: unit, PT, tanggal, kelompok barang
$unit = '';
$tglRfq = '';
$tglVerifikasi = '';
$nopp0 = '';
foreach ($barang as $b) {
	$nopp0 = $b['nopp'];
	break;
}
$e = explode('/', $nopp0);
$unit = isset($e[4]) ? $e[4] : '';
foreach ($semua as $r) {
	if ($tglRfq == '') {
		$tglRfq = $r['tanggal'];
	}
	if ($tglVerifikasi == '' and $r['tanggalverifikasi'] != '' and substr($r['tanggalverifikasi'], 0, 4) != '0000') {
		$tglVerifikasi = $r['tanggalverifikasi'];
	}
}
if ($tglVerifikasi == '') {
	foreach ($semua as $r) {
		if ($r['waktuverifikasi'] != '' and substr($r['waktuverifikasi'], 0, 4) != '0000') {
			$tglVerifikasi = substr($r['waktuverifikasi'], 0, 10);
			break;
		}
	}
}
$tahun = substr($tglVerifikasi != '' ? $tglVerifikasi : $tglRfq, 0, 4);
$kodept = '';
if ($unit != '') {
	$optInduk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . addslashes($unit) . "'");
	$kodept = isset($optInduk[$unit]) ? $optInduk[$unit] : '';
}
$arrHead = setheadreport($unit, $kodept);
$namaPt = $arrHead['nama'] != '' ? $arrHead['nama'] : getNamaOrg($kodept);
$namaUnit = $unit != '' ? getNamaOrg($unit) : '';
$kontak = array();
if (trim((string)$arrHead['alamat']) != '') {
	$kontak[] = trim($arrHead['alamat']);
}
if (trim((string)$arrHead['telepon']) != '') {
	$kontak[] = 'Telp. ' . trim($arrHead['telepon']);
}
if (trim((string)$arrHead['fax']) != '') {
	$kontak[] = 'Fax. ' . trim($arrHead['fax']);
}
$logoUri = '';
$pathLogo = 'images/logo/' . $kodept . '.jpg';
if ($kodept != '' and file_exists($pathLogo)) {
	$logoUri = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($pathLogo));
}

$stmt = $owlPDO->prepare("select kelompok from " . $dbname . ".log_5klbarang where kode in (" . implode(',', array_fill(0, count($barang) > 0 ? count($barang) : 1, '?')) . ")");
$stmt->execute(count($barang) > 0 ? array_map(function ($k) {
	return substr($k, 0, 3);
}, array_keys($barang)) : array(''));
$kelompok = implode(', ', array_unique($stmt->fetchAll(PDO::FETCH_COLUMN)));

#--- total per supplier dan total pemenang
$totalSup = array();
foreach ($supplier as $ks => $s) {
	$totalSup[$ks] = 0;
}
foreach ($harga as $kd => $offers) {
	foreach ($offers as $ks => $r) {
		if ($r['harga'] > 0) {
			$totalSup[$ks] += $r['jumlah'] * $r['harga'];
		}
	}
}
$subMenang = 0;
$ppnMenang = 0;
$syaratMenang = array();
$francoMenang = array();
$supMenang = array();
foreach ($menang as $kd => $ks) {
	$r = $harga[$kd][$ks];
	$nilai = $r['jumlah'] * $r['harga'];
	$subMenang += $nilai;
	$ppnMenang += $nilai * $r['ppn'] / 100;
	$supMenang[$ks] = 1;
	if (isset($opttop[$r['sisbayar2']])) {
		$syaratMenang[$opttop[$r['sisbayar2']]] = 1;
	}
	if (isset($franconame[$r['id_franco']])) {
		$francoMenang[$franconame[$r['id_franco']]] = 1;
	}
}
$nilaiMenang = $subMenang + $ppnMenang;

#--- persetujuan
$stmt = $owlPDO->prepare("select * from " . $dbname . ".approval where notransaksi=? order by level");
$stmt->execute(array($daftarNomor[0]));
$approval = $stmt->fetchAll(PDO::FETCH_ASSOC);
$komentarDisposisi = array();
foreach ($approval as $ap) {
	if (trim((string)$ap['komentar']) != '') {
		$komentarDisposisi[] = array($optnmkar[$ap['karyawanid']], $ap['komentar']);
	}
}
$purchaserId = '';
$verifikatorId = '';
$catatanMenang = '';
foreach ($supplier as $s) {
	if ($purchaserId == '') {
		$purchaserId = $s['purchaser'];
	}
	if ($verifikatorId == '' and $s['picverifikasi'] != '' and $s['picverifikasi'] != '0000000000') {
		$verifikatorId = $s['picverifikasi'];
	}
	if ($catatanMenang == '' and trim((string)$s['catatanmenang']) != '') {
		$catatanMenang = trim($s['catatanmenang']);
	}
}

#--- kesimpulan (harga terbaik per barang) dan rekomendasi (score terbaik per kriteria)
$terbaik = array();
foreach ($harga as $kd => $offers) {
	$min = null;
	foreach ($offers as $ks => $r) {
		if ($r['harga'] > 0 and ($min === null or $r['harga'] < $min)) {
			$min = $r['harga'];
		}
	}
	foreach ($offers as $ks => $r) {
		if ($min !== null and $r['harga'] == $min) {
			$terbaik[$kd][] = $optnamasupplier[$r['supplierid']];
		}
	}
}
$kriteria = array('1' => 'Harga', '2' => 'Availability', '3' => 'Quality/ Performance/ Integrity', '4' => 'Service', '5' => 'Other');
$scoreTerbaik = array();
foreach ($kriteria as $no => $nm) {
	$max = null;
	foreach ($supplier as $ks => $s) {
		if ($max === null or $s['nilai' . $no . 's'] > $max) {
			$max = $s['nilai' . $no . 's'];
		}
	}
	foreach ($supplier as $ks => $s) {
		if ($max !== null and $s['nilai' . $no . 's'] == $max and $max > 0) {
			$scoreTerbaik[$nm][$optnamasupplier[$s['supplierid']]] = $max;
		}
	}
}

#--- susun HTML
$jmlSup = count($supplier);
$kolomTotal = 4 + ($jmlSup * 2) + 6;
$judulDok = 'PROPOSAL PENUNJUKAN PEMENANG TENDER';
$tab = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
<style type='text/css'>
	@page { margin: 24px 30px 44px 30px; }
	body { font-family: Helvetica, Arial, sans-serif; font-size: 9px; color: #000; }
	table { border-collapse: collapse; }
	td, th { font-size: 9px; }
	h2.bab { font-size: 11px; margin: 7px 0 3px 0; padding: 0; }
	.satu { page-break-inside: avoid; }
	table.grid th { background-color: #e1e1e1; border: 0.5px solid #444; padding: 3px 4px; text-align: center; }
	table.grid td { border: 0.5px solid #444; padding: 3px 4px; vertical-align: top; }
	table.info { width: 100%; }
	table.info td { padding: 1px 4px 1px 0; vertical-align: top; }
	table.info td.col { width: 10px; }
	table.info td.lbl { width: 250px; white-space: nowrap; }
	.r { text-align: right; } .c { text-align: center; } .b { font-weight: bold; }
	.menang { background-color: #c6efce; }
	.ringkas { background-color: #f2f2f2; font-weight: bold; }
</style></head><body>";

#kop surat
$tab .= "<table width='100%' style='border-bottom:2px solid #000;padding-bottom:4px'><tr>";
if ($logoUri != '') {
	$tab .= "<td style='width:70px;vertical-align:middle'><img src='" . $logoUri . "' style='height:48px'></td>";
}
$tab .= "<td style='vertical-align:middle'>
	<div style='font-size:15px;font-weight:bold'>" . pdfSafe($namaPt) . "</div>
	<div style='font-size:10px'>" . pdfSafe($namaUnit) . "</div>" .
	(count($kontak) > 0 ? "<div style='font-size:8px;color:#444'>" . pdfSafe(implode('  |  ', $kontak)) . "</div>" : "") . "
</td></tr></table>
<div style='border-top:0.5px solid #000;margin-top:1px'></div>";

#judul
$tab .= "<div style='text-align:center;margin-top:10px'>
	<div style='font-size:14px;font-weight:bold;text-decoration:underline'>" . $judulDok . "</div>
	<div style='font-size:10px;margin-top:2px'>No. " . pdfSafe($notransaksi) . ($tahun != '' ? " &nbsp;|&nbsp; Tahun " . pdfSafe($tahun) : "") . "</div>
</div>";

#I. pendahuluan
$tab .= "<h2 class='bab'>I. PENDAHULUAN</h2>
<table class='info'>
	<tr><td class='lbl'>Unit yang ditenderkan</td><td class='col'>:</td><td>" . pdfSafe($namaUnit != '' ? $namaUnit . ' (' . $unit . ')' : '-') . ($tahun != '' ? ", Tahun " . pdfSafe($tahun) : "") . "</td></tr>
	<tr><td class='lbl'>Kelompok barang</td><td class='col'>:</td><td>" . pdfSafe($kelompok != '' ? $kelompok : '-') . "</td></tr>
	<tr><td class='lbl'>Tanggal verifikasi</td><td class='col'>:</td><td>" . pdfTgl($tglVerifikasi != '' ? $tglVerifikasi : $tglRfq) . "</td></tr>
	<tr><td class='lbl'>Jumlah supplier yang diundang</td><td class='col'>:</td><td>" . $jmlSup . " supplier</td></tr>
	<tr><td class='lbl'>Jumlah supplier yang memberikan penawaran harga</td><td class='col'>:</td><td>" . $jmlSup . " supplier</td></tr>
	<tr><td class='lbl'>Data kebutuhan yang ditenderkan</td><td class='col'>:</td><td>" . count($barang) . " jenis barang/jasa (rincian pada bab II)</td></tr>
	<tr><td class='lbl'>Penawaran harga</td><td class='col'>:</td><td>Dikirim melalui email sebagaimana terlampir dalam TOR</td></tr>
	<tr><td class='lbl'>Nilai transaksi keseluruhan</td><td class='col'>:</td><td><b>Rp " . pdfRp($nilaiMenang) . "</b> (terbilang: " . pdfSafe(trim(terbilang($nilaiMenang, 3))) . ")</td></tr>
</table>";

#II. rekapitulasi penawaran
$tab .= "<h2 class='bab'>II. DATA REKAPITULASI PENAWARAN HARGA</h2>
<table class='grid' width='100%'>
	<tr><th rowspan='2'>NO</th><th rowspan='2'>KETERANGAN</th><th rowspan='2'>JUMLAH</th><th rowspan='2'>SAT</th>";
foreach ($supplier as $ks => $s) {
	$tab .= "<th colspan='2'>" . pdfSafe($optnamasupplier[$s['supplierid']]) . "</th>";
}
$tab .= "<th colspan='6' class='menang' style='background-color:#c6efce'>PEMENANG / TOTAL PEMBELIAN</th></tr><tr>";
foreach ($supplier as $ks => $s) {
	$tab .= "<th>HARGA</th><th>TOTAL (Rp)</th>";
}
$tab .= "<th style='background-color:#c6efce'>SUPPLIER</th><th style='background-color:#c6efce'>HARGA</th><th style='background-color:#c6efce'>TOTAL (Rp)</th><th style='background-color:#c6efce'>WAKTU TERIMA</th><th style='background-color:#c6efce'>STOCK POINT</th><th style='background-color:#c6efce'>JATUH TEMPO</th></tr>";
$no = 0;
foreach ($barang as $kd => $b) {
	$no++;
	$tab .= "<tr><td class='c'>" . $no . "</td><td>" . pdfSafe($optnamabarang[$kd]) . "</td><td class='r'>" . pdfRp($b['jumlah']) . "</td><td class='c'>" . pdfSafe($optsatuan[$kd]) . "</td>";
	foreach ($supplier as $ks => $s) {
		$r = isset($harga[$kd][$ks]) ? $harga[$kd][$ks] : null;
		$isMenang = (isset($menang[$kd]) && $menang[$kd] == $ks);
		$cls = $isMenang ? " class='r menang'" : " class='r'";
		if ($r && $r['harga'] > 0) {
			$tab .= "<td" . $cls . ">" . pdfRp($r['harga']) . "</td><td" . $cls . ">" . pdfRp($r['jumlah'] * $r['harga']) . "</td>";
		} else {
			$tab .= "<td class='c'>-</td><td class='c'>-</td>";
		}
	}
	if (isset($menang[$kd])) {
		$r = $harga[$kd][$menang[$kd]];
		$wkt = ($tglVerifikasi != '' && $r['deliverytime'] != '' && is_numeric($r['deliverytime'])) ? date('d-m-Y', strtotime($tglVerifikasi . ' +' . (int)$r['deliverytime'] . ' days')) : '-';
		$tab .= "<td class='menang'>" . pdfSafe($optnamasupplier[$r['supplierid']]) . "</td><td class='r menang'>" . pdfRp($r['harga']) . "</td><td class='r menang'>" . pdfRp($r['jumlah'] * $r['harga']) . "</td><td class='c menang'>" . $wkt . "</td><td class='c menang'>" . pdfSafe($r['posisistok'] != '' ? $r['posisistok'] : '-') . "</td><td class='c menang'>" . pdfTgl($r['tgltempopembayaran']) . "</td>";
	} else {
		$tab .= "<td colspan='6' class='c'>Belum ada pemenang</td>";
	}
	$tab .= "</tr>";
}
#ringkasan: subtotal, PPN, total, franco, syarat bayar, catatan
$sisaKol = "<td colspan='6' class='menang'>";
$tab .= "<tr class='ringkas'><td colspan='4' class='r'>Subtotal</td>";
foreach ($supplier as $ks => $s) {
	$tab .= "<td colspan='2' class='r'>" . pdfRp($totalSup[$ks]) . "</td>";
}
$tab .= "<td colspan='3' class='r menang'>Subtotal</td><td colspan='3' class='r menang'>" . pdfRp($subMenang) . "</td></tr>";
$tab .= "<tr><td colspan='4' class='r'>PPN</td>";
foreach ($supplier as $ks => $s) {
	$tab .= "<td colspan='2' class='r'>" . ($s['ppn'] > 0 ? pdfRp($totalSup[$ks] * $s['ppn'] / 100) . " (" . hidezerodecimal($s['ppn'], 2) . "%)" : "Exclude") . "</td>";
}
$tab .= "<td colspan='3' class='r menang'>PPN</td><td colspan='3' class='r menang'>" . pdfRp($ppnMenang) . "</td></tr>";
$tab .= "<tr class='ringkas'><td colspan='4' class='r'>Total</td>";
foreach ($supplier as $ks => $s) {
	$tab .= "<td colspan='2' class='r'>" . pdfRp($totalSup[$ks] + $totalSup[$ks] * $s['ppn'] / 100) . "</td>";
}
$tab .= "<td colspan='3' class='r menang'>Total</td><td colspan='3' class='r menang'><b>" . pdfRp($nilaiMenang) . "</b></td></tr>";
$adaPbbkb = false;
foreach ($supplier as $s) {
	if ($s['pbbkb'] > 0) {
		$adaPbbkb = true;
	}
}
if ($adaPbbkb) {
	$tab .= "<tr><td colspan='4' class='r'>PBBKB</td>";
	foreach ($supplier as $ks => $s) {
		$tab .= "<td colspan='2' class='r'>" . ($s['pbbkb'] > 0 ? pdfRp($s['pbbkb']) : '-') . "</td>";
	}
	$tab .= "<td colspan='6' class='menang'></td></tr>";
	$tab .= "<tr><td colspan='4' class='r'>Nilai Penawaran (termasuk PBBKB)</td>";
	foreach ($supplier as $ks => $s) {
		$tab .= "<td colspan='2' class='r'>" . ($s['nilaipermintaan'] > 0 ? pdfRp($s['nilaipermintaan']) : '-') . "</td>";
	}
	$tab .= "<td colspan='6' class='menang'></td></tr>";
}
$tab .= "<tr><td colspan='4' class='r'>Franco</td>";
foreach ($supplier as $ks => $s) {
	$tab .= "<td colspan='2'>" . pdfSafe(isset($franconame[$s['id_franco']]) ? $franconame[$s['id_franco']] : '-') . "</td>";
}
$tab .= "<td colspan='6' class='menang'>" . pdfSafe(count($francoMenang) > 0 ? implode('; ', array_keys($francoMenang)) : '-') . "</td></tr>";
$tab .= "<tr><td colspan='4' class='r'>Term of Payment</td>";
foreach ($supplier as $ks => $s) {
	$tab .= "<td colspan='2'>" . pdfSafe(isset($opttop[$s['sisbayar2']]) ? $opttop[$s['sisbayar2']] : '-') . "</td>";
}
$tab .= "<td colspan='6' class='menang'>" . pdfSafe(count($syaratMenang) > 0 ? implode('; ', array_keys($syaratMenang)) : '-') . "</td></tr>";
$adaCatatan = false;
foreach ($supplier as $s) {
	if (trim((string)$s['catatan']) != '') {
		$adaCatatan = true;
	}
}
if ($adaCatatan) {
	$tab .= "<tr><td colspan='4' class='r'>Catatan Penawaran</td>";
	foreach ($supplier as $ks => $s) {
		$tab .= "<td colspan='2'>" . pdfSafe(trim((string)$s['catatan'])) . "</td>";
	}
	$tab .= "<td colspan='6' class='menang'></td></tr>";
}
$tab .= "</table>
<div style='margin-top:3px;font-style:italic'>Semua penawaran sudah termasuk segel dan stempel. Kolom berwarna hijau adalah penawaran yang ditunjuk sebagai pemenang.</div>";

#III. kesimpulan
$tab .= "<h2 class='bab'>III. KESIMPULAN</h2><ul style='margin:0 0 0 18px;padding:0'>";
if (count($terbaik) == 0) {
	$tab .= "<li>Belum ada data penawaran.</li>";
}
foreach ($terbaik as $kd => $nm) {
	$tab .= "<li><b>" . pdfSafe($optnamabarang[$kd]) . "</b>: " . pdfSafe(implode(', ', array_unique($nm))) . " memberikan harga terendah.</li>";
}
$tab .= "</ul>";

#IV. rekomendasi
$tab .= "<h2 class='bab'>IV. REKOMENDASI</h2><ul style='margin:0 0 0 18px;padding:0'>";
if (count($supMenang) > 0) {
	$namaMenang = array();
	foreach ($supMenang as $ks => $v) {
		$namaMenang[] = $optnamasupplier[$supplier[$ks]['supplierid']];
	}
	$tab .= "<li>Ditunjuk sebagai pemenang: <b>" . pdfSafe(implode(', ', array_unique($namaMenang))) . "</b> dengan nilai transaksi Rp " . pdfRp($nilaiMenang) . ".</li>";
}
if ($catatanMenang != '') {
	$tab .= "<li>Alasan penunjukan: " . pdfSafe($catatanMenang) . "</li>";
}
foreach ($scoreTerbaik as $nm => $sup) {
	if ($jmlSup > 1 and count($sup) == $jmlSup) {
		continue;
	}
	$tab .= "<li>Supplier " . pdfSafe(implode(', ', array_keys($sup))) . " memiliki score " . pdfSafe($nm) . " terbaik.</li>";
}
$tab .= "</ul>";

#V. persetujuan
$tab .= "<div class='satu'><h2 class='bab'>V. PERSETUJUAN</h2>";
$pihak = array();
$pihak[] = array('Dibuat oleh', $purchaserId, 'Dibuat', $tglRfq);
if ($verifikatorId != '') {
	$pihak[] = array('Diverifikasi oleh', $verifikatorId, 'Diverifikasi', $tglVerifikasi);
}
$stApp = array('0' => 'Menunggu persetujuan', '1' => 'Disetujui', '2' => 'Dikoreksi', '3' => 'Ditolak', '9' => 'Belum diajukan');
foreach ($approval as $ap) {
	$pihak[] = array('Disetujui oleh (Level ' . $ap['level'] . ')', $ap['karyawanid'], isset($stApp[$ap['status']]) ? $stApp[$ap['status']] : '', ($ap['status'] == '1' ? $ap['tanggal'] : ''));
}
$lebarBox = floor(100 / max(1, count($pihak)));
$tab .= "<table class='grid' style='width:100%'><tr>";
foreach ($pihak as $p) {
	$tab .= "<th style='width:" . $lebarBox . "%'>" . pdfSafe($p[0]) . "</th>";
}
$tab .= "</tr><tr>";
foreach ($pihak as $p) {
	$tab .= "<td class='c' style='height:34px;color:#555;vertical-align:middle'><i>" . pdfSafe($p[2]) . ($p[3] != '' ? "<br>" . pdfTgl($p[3]) : "") . "</i></td>";
}
$tab .= "</tr><tr>";
foreach ($pihak as $p) {
	$tab .= "<td class='c'><b>" . pdfSafe(isset($optnmkar[$p[1]]) ? $optnmkar[$p[1]] : '-') . "</b><br>" . pdfSafe(isset($jabatan[$kary_id[$p[1]]]) ? $jabatan[$kary_id[$p[1]]] : '') . "</td>";
}
$tab .= "</tr></table></div>";

$tab .= "</body></html>";

$dompdf = new Dompdf();
$dompdf->loadHtml($tab);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$font = $dompdf->getFontMetrics()->get_font("Helvetica", "");
$dompdf->getCanvas()->page_text(40, 570, $judulDok . " - No. " . $notransaksi, $font, 7, array(0.3, 0.3, 0.3));
$dompdf->getCanvas()->page_text(745, 570, "Halaman {PAGE_NUM} / {PAGE_COUNT}", $font, 7, array(0.3, 0.3, 0.3));

## Print Out
if ($urlefil == '0') {
	$dompdf->stream("PenunjukanPemenangTender_" . preg_replace('/[^A-Za-z0-9_-]/', '_', $column), array("Attachment" => 0));
} else {
	file_put_contents($urlefil, $dompdf->output());
}
