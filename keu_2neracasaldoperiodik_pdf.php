<?php
#PDF Neraca Saldo Periodik, mengikuti filter yang sama dengan preview.
#12 bulan sekaligus terlalu sempit untuk dibaca dalam satu halaman A4, jadi dipecah 2 halaman per baris akun (6 bulan
#tiap halaman): Jan-Jun lalu Jul-Des. Bulan Des (paruh kedua) punya 1 sub-kolom ekstra "Saldo Akhir", jadi lebar
#sub-kolom dihitung terpisah per paruh supaya kedua paruh tetap penuh selebar halaman.
#Auto-page-break FPDF DIMATIKAN dan diganti pengecekan manual per baris (lihat nspMuat) karena auto-page-break yang memutus
#di TENGAH baris (antar sel) menyebabkan Y yang sudah tersimpan di $y jadi basi lalu dipaksakan lagi ke halaman baru oleh
#SetXY, sehingga setiap sel sesudahnya langsung memicu halaman baru lagi (pernah menghasilkan puluhan ribu halaman kosong).
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('keu_2neracasaldoperiodik_filter.php');

$flt = nspFilter($_GET);
if ($flt['error'] != '') {
	exit($flt['error']);
}
$data = nspData($flt);

$bulanLabel = array();
for ($m = 1; $m <= 12; $m++) {
	$nn = addZero($m, 2);
	$bulanLabel[$nn] = numToMonth($m, ($_SESSION['language'] == 'ID' ? 'I' : 'E'));
}
$paruh = array(
	'01' => array_slice($bulanLabel, 0, 6, true),
	'02' => array_slice($bulanLabel, 6, 6, true),
);
$labelParuh = array('01' => 'Jan - Jun', '02' => 'Jul - Des');

$hdpt = setheadreport($flt['pt'], $flt['pt']);
$infofilter = $flt['info'];

function nspT($v)
{
	return utf8_decode($v);
}

#font terbesar (tidak melebihi $max) yang muat di lebar $w; angka tidak pernah dipotong, hanya diperkecil
function nspFitFont($pdf, $txt, $w, $max = 7, $min = 4.5)
{
	$txt = nspT($txt);
	for ($f = $max; $f >= $min; $f -= 0.5) {
		$pdf->SetFont('Arial', '', $f);
		if ($pdf->GetStringWidth($txt) <= $w - 3) {
			return $f;
		}
	}
	return $min;
}

#pindah halaman sendiri kalau baris setinggi $h tidak muat lagi (auto-page-break FPDF dimatikan, lihat catatan di atas)
function nspMuat($pdf, $h)
{
	if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
		$pdf->AddPage();
	}
}

#teks (bukan angka) pada font tetap $f; nama akun/bank yang panjang dipotong dan diberi "..." supaya tidak pernah tumpang tindih ke kolom sebelah
function nspFitTeks($pdf, $txt, $w, $f = 6.5)
{
	$txt = nspT($txt);
	$pdf->SetFont('Arial', '', $f);
	if ($pdf->GetStringWidth($txt) <= $w - 3) {
		return $txt;
	}
	while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 3) {
		$txt = substr($txt, 0, -1);
	}
	return rtrim($txt) . '...';
}

#lebar kolom dalam persen: No, No Akun, Nama Akun, sisanya dibagi rata untuk sub-kolom bulan (dihitung per paruh, lihat bawah)
$kolomAwal = array(array('No.', 1.6, 'C'), array('No Akun', 4.4, 'L'), array('Nama Akun', 14, 'L'));
$lebarBulanTetap = array_sum(array_column($kolomAwal, 1));
$kolomBulan = array('Awal', 'Debet', 'Kredit');

class PDF extends FPDF
{
	public $labelBulan = array();
	public $keteranganParuh = '';
	public $lebarBulan = 0;

	function Header()
	{
		global $hdpt, $infofilter, $kolomAwal, $kolomBulan;
		$width = $this->w - $this->lMargin - $this->rMargin;
		if (file_exists($hdpt['logo'])) {
			$this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 36);
		}
		$this->SetFont('Arial', 'B', 9);
		$this->SetXY($this->lMargin + 42, $this->tMargin + 4);
		$this->Cell(400, 10, nspT($hdpt['nama']), 0, 1, 'L');
		$this->SetY($this->tMargin + 32);
		$this->SetFont('Arial', 'B', 10);
		$this->Cell($width, 11, 'NERACA SALDO PERIODIK', 0, 1, 'C');
		$this->SetFont('Arial', '', 7);
		$this->Cell($width, 9, nspT($infofilter . ($this->keteranganParuh != '' ? ' | Bulan: ' . $this->keteranganParuh : '')), 0, 1, 'C');
		$this->Cell($width, 9, nspT('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
		$this->Ln(1);

		#baris header 1: No/No Akun/Nama Akun (tinggi 2 baris) + nama bulan (lebar 3 sub-kolom, 4 untuk Des karena ada Saldo Akhir)
		$this->SetFillColor(220, 220, 220);
		$this->SetFont('Arial', 'B', 8);
		$h1 = 11;
		$x0 = $this->GetX();
		foreach ($kolomAwal as $k) {
			$this->Cell($k[1] / 100 * $width, $h1 * 2, $k[0], 1, 0, 'C', true);
		}
		foreach ($this->labelBulan as $nn => $lbl) {
			$n = ($nn == '12') ? 4 : 3;
			$this->Cell($this->lebarBulan * $n / 100 * $width, $h1, nspT($lbl), 1, 0, 'C', true);
		}
		$this->Ln($h1);
		$this->SetX($x0 + array_sum(array_column($kolomAwal, 1)) / 100 * $width);
		$this->SetFont('Arial', 'B', 7);
		foreach ($this->labelBulan as $nn => $lbl) {
			foreach ($kolomBulan as $kb) {
				$this->Cell($this->lebarBulan / 100 * $width, $h1, $kb, 1, 0, 'C', true);
			}
			if ($nn == '12') {
				#"Saldo Akhir" (lang) kepanjangan untuk sub-kolom sesempit ini dan tumpang tindih ke kolom sebelah;
				#label dipendekkan jadi "Akhir" (sepasang dengan "Awal") + font ikut menyusut seperti sel angka
				$wAkhir = $this->lebarBulan / 100 * $width;
				$fAkhir = nspFitFont($this, 'Akhir', $wAkhir, 7, 5);
				$this->SetFont('Arial', 'B', $fAkhir);
				$this->Cell($wAkhir, $h1, 'Akhir', 1, 0, 'C', true);
				$this->SetFont('Arial', 'B', 7);
			}
		}
		$this->Ln($h1);
	}

	function Footer()
	{
		$this->SetY(-16);
		$this->SetFont('Arial', 'I', 7);
		$this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
	}
}

#cetak satu paruh tahun (6 bulan) untuk seluruh akun, dengan baris TOTAL di akhir. bulan '12' (bila ada di paruh ini)
#mendapat 1 sel tambahan Saldo Akhir (tebal) setelah sel Kredit-nya
function nspCetakParuh($pdf, $data, $labelBulan, $keterangan, $kolomAwal, $lebarBulan, $width)
{
	$pdf->labelBulan = $labelBulan;
	$pdf->keteranganParuh = $keterangan;
	$pdf->lebarBulan = $lebarBulan;
	$pdf->AddPage();
	$h = 11;
	$no = 0;
	$totBulan = array();
	foreach ($labelBulan as $nn => $lbl) {
		$totBulan[$nn] = array('awal' => 0, 'debet' => 0, 'kredit' => 0);
	}
	$totAkhir = 0;
	if (count($data) == 0) {
		$pdf->SetFont('Arial', '', 8);
		$pdf->Cell($width, $h, 'Data tidak ditemukan', 1, 1, 'C');
		return;
	}
	foreach ($data as $noakun => $d) {
		$no++;
		nspMuat($pdf, $h);
		$x = $pdf->lMargin;
		$y = $pdf->GetY();
		$isiAwal = array($no, $noakun, $d['namaakun']);
		foreach ($kolomAwal as $i => $k) {
			$w = $k[1] / 100 * $width;
			$pdf->SetXY($x, $y);
			if ($i == 2) {
				#nama akun: teks dipotong+"..." bila kepanjangan, bukan font diperkecil (memperkecil tidak menolong nama yang sangat panjang)
				$pdf->SetFont('Arial', '', 6.5);
				$pdf->Cell($w, $h, nspFitTeks($pdf, (string)$isiAwal[$i], $w), 1, 0, $k[2]);
			} else {
				$pdf->SetFontSize(nspFitFont($pdf, (string)$isiAwal[$i], $w));
				$pdf->Cell($w, $h, nspT((string)$isiAwal[$i]), 1, 0, $k[2]);
			}
			$x += $w;
		}
		foreach ($labelBulan as $nn => $lbl) {
			$b = $d['bulan'][$nn];
			$totBulan[$nn]['awal'] += $b['awal'];
			$totBulan[$nn]['debet'] += $b['debet'];
			$totBulan[$nn]['kredit'] += $b['kredit'];
			foreach (array($b['awal'], $b['debet'], $b['kredit']) as $v) {
				$w = $lebarBulan / 100 * $width;
				$txt = number_format($v, 0);
				$pdf->SetXY($x, $y);
				$pdf->SetFontSize(nspFitFont($pdf, $txt, $w));
				$pdf->Cell($w, $h, $txt, 1, 0, 'R');
				$x += $w;
			}
			if ($nn == '12') {
				$totAkhir += $d['saldoakhir'];
				$w = $lebarBulan / 100 * $width;
				$txt = number_format($d['saldoakhir'], 0);
				$pdf->SetXY($x, $y);
				$f = nspFitFont($pdf, $txt, $w);
				$pdf->SetFont('Arial', 'B', $f);
				$pdf->Cell($w, $h, $txt, 1, 0, 'R');
				$x += $w;
			}
		}
		$pdf->SetXY($x, $y);
		$pdf->Ln($h);
	}

	nspMuat($pdf, $h);
	$pdf->SetFont('Arial', 'B', 7);
	$pdf->SetFillColor(240, 240, 240);
	$lblw = array_sum(array_column($kolomAwal, 1)) / 100 * $width;
	$pdf->Cell($lblw, $h, 'TOTAL', 1, 0, 'C', true);
	foreach ($labelBulan as $nn => $lbl) {
		foreach (array($totBulan[$nn]['awal'], $totBulan[$nn]['debet'], $totBulan[$nn]['kredit']) as $v) {
			$w = $lebarBulan / 100 * $width;
			$txt = number_format($v, 0);
			$pdf->SetFontSize(nspFitFont($pdf, $txt, $w));
			$pdf->Cell($w, $h, $txt, 1, 0, 'R', true);
		}
		if ($nn == '12') {
			$w = $lebarBulan / 100 * $width;
			$txt = number_format($totAkhir, 0);
			$f = nspFitFont($pdf, $txt, $w);
			$pdf->SetFont('Arial', 'B', $f);
			$pdf->Cell($w, $h, $txt, 1, 0, 'R', true);
		}
	}
	$pdf->Ln($h);
}

$pdf = new PDF('L', 'pt', 'A4');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(false);
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;

foreach ($paruh as $kp => $labelBulanParuh) {
	$adaAkhir = array_key_exists('12', $labelBulanParuh);
	$subkolom = count($labelBulanParuh) * 3 + ($adaAkhir ? 1 : 0);
	$lebarBulanParuh = (100 - $lebarBulanTetap) / $subkolom;
	nspCetakParuh($pdf, $data, $labelBulanParuh, $labelParuh[$kp], $kolomAwal, $lebarBulanParuh, $width);
}

$pdf->Output();
