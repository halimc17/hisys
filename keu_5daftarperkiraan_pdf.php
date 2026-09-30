<?php
#PDF Daftar Perkiraan (COA), dipanggil dari keu_5daftarperkiraan.php.
#Kolom dibatasi (tidak semua flag ditampilkan) supaya muat di landscape A4 - tarikan lengkap semua kolom ada di Excel.
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');

$txt_search = checkPostGet('txtsearch', '');
$txtNoakun  = checkPostGet('txtNoakun', '');

$where = '';
if ($txt_search != '') {
	$where = " and namaakun LIKE '%" . addslashes($txt_search) . "%'";
}
if ($txtNoakun != '') {
	$where = " and noakun LIKE '%" . addslashes($txtNoakun) . "%'";
}

#daftar perkiraan ini data bersama (bukan per-PT), jadi kop pakai org karyawan yang login, bukan org data
$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);

function bbT($v)
{
	return utf8_decode($v);
}

#nama akun yang kepanjangan dipotong+"..." supaya tidak tumpang tindih ke kolom sebelah
function bbFitTeks($pdf, $txt, $w, $f = 6.5)
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
			$this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 16);
		}
		$this->SetFont('Arial', 'B', 10);
		$this->SetXY($this->lMargin + 20, $this->tMargin + 1);
		$this->Cell(160, 5, bbT($hdpt['nama']), 0, 1, 'L');
		$this->SetY($this->tMargin + 12);
		$this->SetFont('Arial', 'B', 12);
		$this->Cell($width, 6, strtoupper($_SESSION['lang']['daftarperkiraan']), 0, 1, 'C');
		$this->SetFont('Arial', '', 7);
		$this->Cell($width, 4, bbT('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
		$this->Ln(1);

		$this->SetFillColor(220, 220, 220);
		$this->SetFont('Arial', 'B', 6.5);
		#judul kolom disingkat (bukan pakai $_SESSION['lang'] penuh) karena banyak kolom sempit - versi lengkap ada di Excel
		$judul = array('No Akun', $_SESSION['lang']['namaakun'], 'Tipe', 'Lvl', 'Mtwg', 'Org', $_SESSION['lang']['pemilik'], 'KB', 'Rincian', 'KB.Rin', 'Kegiatan', 'Blok');
		foreach ($judul as $i => $j) {
			$this->Cell($this->colW[$i], 5, bbT($j), 1, 0, 'C', true);
		}
		$this->Ln();
	}

	function Footer()
	{
		$this->SetY(-10);
		$this->SetFont('Arial', 'I', 6);
		$this->Cell(0, 6, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
	}
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AliasNbPages();
#margin bawah 12 dipakai murni sebagai batas bbMuat (auto-page-break FPDF tetap mati) supaya baris
#terakhir tidak tumpang tindih dengan teks "Halaman.." di Footer() yang ada di -10
$pdf->SetAutoPageBreak(false, 12);
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
#urutan kolom: NoAkun,NamaAkun(flexible),Tipe,Lvl,Mtwg,Org,Pemilik,Kasbank,Detail,KasbankDetail,Kegiatan,Blok
$colFixed = array(18, 16, 8, 10, 12, 16, 12, 12, 12, 14, 10);
$pdf->colW = array_merge(array($colFixed[0], $width - array_sum($colFixed)), array_slice($colFixed, 1));
$pdf->AddPage();
$h = 5;

$input = "select * from " . $dbname . ".keu_5akun where noakun<>'' " . $where . " order by noakun";
$n = $owlPDO->query($input) or die(print " Gagal: " . PDOException::getMessage());
$n->setFetchMode(PDO::FETCH_ASSOC);
$ada = false;
while ($d = $n->fetch()) {
	$ada = true;
	bbMuat($pdf, $h);
	$pdf->SetFont('Arial', '', 6.5);
	$c = $pdf->colW;
	$pdf->Cell($c[0], $h, $d['noakun'], 1, 0, 'L');
	$pdf->Cell($c[1], $h, bbFitTeks($pdf, $d['namaakun'], $c[1]), 1, 0, 'L');
	$pdf->Cell($c[2], $h, bbFitTeks($pdf, $d['tipeakun'], $c[2]), 1, 0, 'L');
	$pdf->Cell($c[3], $h, $d['level'], 1, 0, 'C');
	$pdf->Cell($c[4], $h, $d['matauang'], 1, 0, 'C');
	$pdf->Cell($c[5], $h, $d['kodeorg'], 1, 0, 'C');
	$pdf->Cell($c[6], $h, bbFitTeks($pdf, $d['pemilik'], $c[6]), 1, 0, 'L');
	$pdf->Cell($c[7], $h, $d['kasbank'] == 1 ? 'Ya' : '', 1, 0, 'C');
	$pdf->Cell($c[8], $h, $d['detail'] == 1 ? 'Ya' : '', 1, 0, 'C');
	$pdf->Cell($c[9], $h, $d['kasbankdetail'] == 1 ? 'Ya' : '', 1, 0, 'C');
	$pdf->Cell($c[10], $h, $d['kodekegiatan'] == 1 ? 'Ya' : '', 1, 0, 'C');
	$pdf->Cell($c[11], $h, $d['kodeblok'] == 1 ? 'Ya' : '', 1, 0, 'C');
	$pdf->Ln();
}
if (!$ada) {
	$pdf->SetFont('Arial', '', 9);
	$pdf->Cell($width, 8, 'Data tidak ditemukan', 1, 1, 'C');
}
$pdf->Output();
