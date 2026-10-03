<?php
#PDF Kasir, dipanggil dari keu_kasir.php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');

$param = $_POST; if(count($param)==0){ $param = $_GET; }

$kodeorg       = checkPostGet('kodeorg', '');
$notransaksi   = checkPostGet('notransaksi', '');
$novoucher     = checkPostGet('novoucher', '');
$tanggal1      = tanggalsystemn(checkPostGet('tanggal1', ''));
$tanggal2      = tanggalsystemn(checkPostGet('tanggal2', ''));
$noakun        = checkPostGet('noakun', '');
$bayarke       = checkPostGet('bayarke', '');
$tipetransaksi = checkPostGet('tipetransaksi', '');
$nocek         = checkPostGet('nocek', '');
$supplier      = checkPostGet('supplier', '');
$pembayaran    = checkPostGet('pembayaran', '');
$cgttu         = checkPostGet('cgttu', '');
$rekening      = checkPostGet('rekening', '');
$jumlah        = str_replace(',', '', checkPostGet('jumlah', ''));
$keterangan    = checkPostGet('keterangan', '');

#filter sama persis dengan keu_kasir_slave.php (case loaddata/excel), supaya tarikan PDF ikut filter yang aktif
$where = "";
$optOrg = getOrgDetail(10);
ksort($optOrg);
$where .= "and kodeorg in ('" . implode("','", $optOrg) . "')";
if ($kodeorg != '') { $where .= " and kodeorg = '" . $kodeorg . "'"; }
if ($notransaksi != '') { $where .= " and notransaksi like '%" . trim($notransaksi) . "%' "; }
if ($novoucher != '') { $where .= " and novoucher like '%" . $novoucher . "%' "; }
if ($noakun != '') { $where .= " and noakun = '" . $noakun . "' "; }
if ($tipetransaksi != '') { $where .= " and tipetransaksi = '" . $tipetransaksi . "' "; }
if ($tanggal1 != '--' and $tanggal2 != '--') { $where .= " and tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "' "; }
if ($supplier != '') { $where .= " and notransaksi in (select notransaksi from " . $dbname . ".keu_kasbankdt where kodesupplier='" . $supplier . "')"; }
if ($pembayaran != '') { $where .= " and pembayaran='" . trim($pembayaran) . "'"; }
if ($nocek != '') { $where .= " and nocek like '%" . trim($nocek) . "%' "; }
if ($cgttu != '') { $where .= " and cgttu='" . trim($cgttu) . "' "; }
if ($bayarke != '') { $where .= " and bayarkepada like '%" . trim($bayarke) . "%' "; }
if ($rekening != '') { $where .= " and rekening = '" . trim($rekening) . "'"; }
if ($jumlah != '') { $where .= " and jumlah like '%" . trim($jumlah) . "%'"; }
if ($keterangan != '') { $where .= " and keterangan like '%" . trim($keterangan) . "%'"; }

$whereJam = " kasbank=1 and detail=1 and (pemilik='" . $_SESSION['empl']['tipelokasitugas'] . "' or pemilik='GLOBAL' or pemilik='" . $_SESSION['empl']['lokasitugas'] . "')";
if ($_SESSION['language'] == 'EN') {
	$optAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun1', $whereJam, null, true);
} else {
	$optAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', $whereJam, null, true);
}

$norekening = array();
$str = "SELECT * from " . $dbname . ".keu_5akunbank_vw";
$res = fetchData($str);
foreach ($res as $bar) {
	$norekening[$bar['noakun']] = $bar['rekening'];
}

#data lintas unit (bukan per-PT), kop pakai org karyawan yang login - sama seperti tarikan lain yang serupa
$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);

function bbT($v)
{
	return utf8_decode($v);
}

#teks yang kepanjangan dipotong+"..." supaya tidak tumpang tindih ke kolom sebelah
function bbFitTeks($pdf, $txt, $w, $f = 7)
{
	$txt = bbT($txt);
	$pdf->SetFont('Arial', '', $f);
	if ($pdf->GetStringWidth($txt) <= $w - 2) {
		return $txt;
	}
	while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 2) {
		$txt = substr($txt, 0, -1);
	}
	return rtrim($txt) . '...';
}

#pindah halaman manual per baris (bukan auto-page-break FPDF) supaya aman untuk data banyak baris
function bbMuat($pdf, $h)
{
	if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
		$pdf->AddPage();
	}
}

class PDF extends FPDF
{
	public $colW = array();

	function Header()
	{
		global $hdpt;
		$width = $this->w - $this->lMargin - $this->rMargin;
		if (file_exists($hdpt['logo'])) {
			$this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 18);
		}
		$this->SetFont('Arial', 'B', 11);
		$this->SetXY($this->lMargin + 22, $this->tMargin + 2);
		$this->Cell(160, 6, bbT($hdpt['nama']), 0, 1, 'L');
		$this->SetY($this->tMargin + 15);
		$this->SetFont('Arial', 'B', 13);
		$this->Cell($width, 7, 'KASIR', 0, 1, 'C');
		$this->SetFont('Arial', '', 8);
		$this->Cell($width, 5, bbT('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
		$this->Ln(1);

		$this->SetFillColor(220, 220, 220);
		$this->SetFont('Arial', 'B', 8);
		$judul = array('No', $_SESSION['lang']['notransaksi'], $_SESSION['lang']['unit'], $_SESSION['lang']['tanggal'], $_SESSION['lang']['noakun'], $_SESSION['lang']['rekening'], $_SESSION['lang']['tipe'], $_SESSION['lang']['jumlah'], $_SESSION['lang']['bayarke'], $_SESSION['lang']['novoucher'], 'Kasir');
		foreach ($judul as $i => $j) {
			$this->Cell($this->colW[$i], 6, bbT($j), 1, 0, 'C', true);
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

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
#margin bawah 16 dipakai murni sebagai batas bbMuat (auto-page-break FPDF tetap mati) supaya baris
#terakhir tidak tumpang tindih dengan teks "Halaman.." di Footer() yang ada di -12
$pdf->SetAutoPageBreak(false, 16);
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
#urutan kolom: No,NoTransaksi,Unit,Tanggal,NoAkun(flexible),Rekening,Tipe,Jumlah,BayarKe,NoVoucher,Kasir
$colFixed = array(8, 30, 12, 18, 26, 10, 24, 30, 22, 24); #semua kolom kecuali No Akun
$flex = $width - array_sum($colFixed); #sisa lebar jadi lebar kolom No Akun
$pdf->colW = array_merge(array_slice($colFixed, 0, 4), array($flex), array_slice($colFixed, 4));
$pdf->AddPage();
$h = 6;
$no = 0;
$ttljumlah = 0;

$str = "SELECT * from " . $dbname . ".keu_kasbankht where 1=1 and posting=1 " . $where . " order by notransaksi desc, pembayaran asc, novoucher desc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$ada = false;
while ($bar = $res->fetch()) {
	$ada = true;
	$nmkarkasir = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', " karyawanid='" . $bar['kasir'] . "'");
	$no++;
	bbMuat($pdf, $h);
	$pdf->SetFont('Arial', '', 7);
	$c = $pdf->colW;
	$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
	$pdf->Cell($c[1], $h, bbFitTeks($pdf, $bar['notransaksi'], $c[1]), 1, 0, 'L');
	$pdf->Cell($c[2], $h, $bar['kodeorg'], 1, 0, 'C');
	$pdf->Cell($c[3], $h, tanggalnormal($bar['tanggal']), 1, 0, 'C');
	$pdf->Cell($c[4], $h, bbFitTeks($pdf, @$optAkun[$bar['noakun']], $c[4]), 1, 0, 'L');
	$pdf->Cell($c[5], $h, bbFitTeks($pdf, @$norekening[$bar['rekening']], $c[5]), 1, 0, 'L');
	$pdf->Cell($c[6], $h, $bar['tipetransaksi'], 1, 0, 'C');
	$pdf->Cell($c[7], $h, number_format($bar['jumlah'], 0), 1, 0, 'R');
	$pdf->Cell($c[8], $h, bbFitTeks($pdf, $bar['bayarkepada'], $c[8]), 1, 0, 'L');
	$pdf->Cell($c[9], $h, bbFitTeks($pdf, $bar['novoucher'], $c[9]), 1, 0, 'L');
	$pdf->Cell($c[10], $h, bbFitTeks($pdf, @$nmkarkasir[$bar['kasir']], $c[10]), 1, 0, 'L');
	$pdf->Ln();
	$ttljumlah += $bar['jumlah'];
}
if (!$ada) {
	$pdf->SetFont('Arial', '', 9);
	$pdf->Cell($width, 8, 'Data tidak ditemukan', 1, 1, 'C');
} else {
	bbMuat($pdf, $h);
	$pdf->SetFont('Arial', 'B', 7);
	$pdf->SetFillColor(240, 240, 240);
	$c = $pdf->colW;
	$pdf->Cell($c[0] + $c[1] + $c[2] + $c[3] + $c[4] + $c[5] + $c[6], $h, 'TOTAL', 1, 0, 'C', true);
	$pdf->Cell($c[7], $h, number_format($ttljumlah, 0), 1, 0, 'R', true);
	$pdf->Cell($c[8] + $c[9] + $c[10], $h, '', 1, 0, 'C', true);
	$pdf->Ln();
}
$pdf->Output();
