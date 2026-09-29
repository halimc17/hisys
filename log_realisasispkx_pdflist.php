<?php
#PDF list BAPP Kontraktor, mengikuti seluruh filter list
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zMysql.php');
require_once('log_realisasispkx_filter.php');

$dft = spkDaftar($_GET);
if ($dft['flt']['error'] != '') {
	exit($dft['flt']['error']);
}
$rows = $dft['rows'];
$namaRekanan = spkNamaRekanan($rows);
$petaPekerjaan = spkPekerjaan($rows);
$ptkode = getindukPT($_SESSION['empl']['lokasitugas']);
$hdpt = setheadreport($ptkode, $ptkode);
$infofilter = ($dft['flt']['info'] != '') ? $dft['flt']['info'] : 'Seluruh data';

function spkT($v)
{
	return utf8_decode($v);
}

#potong teks agar muat di kolom
function spkFit($pdf, $txt, $w)
{
	$txt = utf8_decode($txt);
	while (strlen($txt) > 1 && $pdf->GetStringWidth($txt) > $w - 3) {
		$txt = substr($txt, 0, -1);
	}
	return $txt;
}

#lebar kolom dalam persen: No, Unit, No Transaksi, Tanggal, Sub Unit, Rekanan, Pekerjaan, Periode SPK, Nilai Kontrak, MU, Aktual, Status Posting, Keterangan
$kolom = array(
	array('No.', 2.3, 'C'), array('Unit', 3.2, 'C'), array('No. Transaksi', 13, 'L'), array('Tanggal', 5.5, 'C'), array('Sub Unit', 5, 'C'),
	array('Rekanan', 12, 'L'), array('Pekerjaan', 12.2, 'L'), array('Periode SPK', 10, 'C'), array('Nilai Kontrak', 7, 'R'), array('MU', 2.3, 'C'),
	array('Aktual', 7, 'R'), array('Status Posting', 10, 'L'), array('Keterangan', 10.5, 'L')
);

class PDF extends FPDF
{
	function Header()
	{
		global $hdpt, $infofilter, $kolom;
		$width = $this->w - $this->lMargin - $this->rMargin;
		if (file_exists($hdpt['logo'])) {
			$this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 42);
		}
		$this->SetFont('Arial', 'B', 10);
		$this->SetXY($this->lMargin + 50, $this->tMargin + 6);
		$this->Cell(400, 12, spkT($hdpt['nama']), 0, 1, 'L');
		$this->SetY($this->tMargin + 40);
		$this->SetFont('Arial', 'B', 11);
		$this->Cell($width, 12, 'BAPP KONTRAKTOR', 0, 1, 'C');
		$this->SetFont('Arial', '', 8);
		$this->Cell($width, 10, spkT($infofilter), 0, 1, 'C');
		$this->Cell($width, 10, spkT('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
		$this->Ln(2);
		$this->SetFont('Arial', 'B', 8);
		$this->SetFillColor(220, 220, 220);
		foreach ($kolom as $k) {
			$this->Cell($k[1] / 100 * $width, 14, $k[0], 1, 0, 'C', true);
		}
		$this->Ln();
		$this->SetFont('Arial', '', 6.5);
	}

	function Footer()
	{
		$this->SetY(-25);
		$this->SetFont('Arial', 'I', 8);
		$this->Cell(0, 10, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
	}

	#pindah halaman bila baris setinggi $h tidak muat
	function Muat($h)
	{
		if ($this->GetY() + $h > $this->PageBreakTrigger) {
			$this->AddPage();
		}
	}
}

$pdf = new PDF('L', 'pt', 'A4');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 35);
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
$pdf->AddPage();
$pdf->SetFont('Arial', '', 6.5);
$h = 12;

$no = 0;
$tot = array();
foreach ($rows as $val) {
	$no++;
	$mu = $val['matauang'];
	if (!isset($tot[$mu])) {
		$tot[$mu] = array('nilai' => 0, 'aktual' => 0);
	}
	$tot[$mu]['nilai'] += $val['nilaikontrak'];
	$tot[$mu]['aktual'] += $val['st']['realisasi'];
	#status posting bisa beberapa baris (per kategori), tinggi baris mengikuti
	$barisPosting = array_map('spkTeks', explode('<br>', $val['st']['posting']));
	$n = max(1, count($barisPosting));
	$isi = array(
		$no, $val['kodeorg'], $val['notransaksi'], tanggalnormal($val['tanggal']), $val['divisi'],
		isset($namaRekanan[$val['koderekanan']]) ? $namaRekanan[$val['koderekanan']] : '',
		spkPekerjaanTeks($val, $petaPekerjaan), preg_replace('/(\d{2})-(\d{2})-\d{2}(\d{2})/', '$1-$2-$3', spkPeriodeTeks($val)),
		number_format($val['nilaikontrak']), $mu, number_format($val['st']['realisasi']), '', spkTeks($val['st']['tagihan'])
	);
	$pdf->Muat($h * $n);
	$x0 = $pdf->GetX();
	$y0 = $pdf->GetY();
	$xk = $x0;
	foreach ($kolom as $i => $k) {
		$w = $k[1] / 100 * $width;
		if ($i == 11) {
			$pdf->Rect($xk, $y0, $w, $h * $n);
			foreach ($barisPosting as $j => $bp) {
				$pdf->SetXY($xk, $y0 + $j * $h);
				$pdf->Cell($w, $h, spkFit($pdf, $bp, $w), 0, 0, 'L');
			}
		} else {
			$pdf->SetXY($xk, $y0);
			$pdf->Cell($w, $h * $n, spkFit($pdf, (string)$isi[$i], $w), 1, 0, $k[2]);
		}
		$xk += $w;
	}
	$pdf->SetXY($x0, $y0 + $h * $n);
}
if ($no == 0) {
	$pdf->Cell($width, $h, 'Data tidak ditemukan', 1, 1, 'C');
} else {
	$pdf->SetFont('Arial', 'B', 6.5);
	$pdf->SetFillColor(240, 240, 240);
	$lblw = 0;
	for ($i = 0; $i <= 7; $i++) {
		$lblw += $kolom[$i][1] / 100 * $width;
	}
	foreach ($tot as $mu => $t) {
		$pdf->Muat($h);
		$pdf->Cell($lblw, $h, 'TOTAL ' . $mu, 1, 0, 'C', true);
		$pdf->Cell($kolom[8][1] / 100 * $width, $h, number_format($t['nilai']), 1, 0, 'R', true);
		$pdf->Cell($kolom[9][1] / 100 * $width, $h, '', 1, 0, 'C', true);
		$pdf->Cell($kolom[10][1] / 100 * $width, $h, number_format($t['aktual']), 1, 0, 'R', true);
		$pdf->Cell(($kolom[11][1] + $kolom[12][1]) / 100 * $width, $h, '', 1, 1, 'L', true);
	}
}

$pdf->Output();
