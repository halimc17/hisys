<?php
#PDF list Potongan, dipanggil dari sdm_potongan.php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');

$kdOrgCr = checkPostGet('kdOrgCr', '');
$periodecr = checkPostGet('periodecr', '');
$tipePotCr = checkPostGet('tipePotCr', '');

#filter sama persis dengan sdm_slave_potongan.php (case loadNewData/excellist), supaya tarikan PDF ikut filter yang aktif
$filter = "substring(kodeorg,1,4) in ('" . implode("','", array_map('addslashes', array_keys(getOrgDetail(1)))) . "')";
if ($periodecr != '') {
	$filter .= " and periodegaji like '%" . addslashes($periodecr) . "%'";
}
if ($tipePotCr != '') {
	$filter .= " and tipepotongan= '" . addslashes($tipePotCr) . "'";
}
if ($kdOrgCr != '') {
	$filter .= " and kodeorg= '" . addslashes($kdOrgCr) . "'";
}

$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmPot = makeOption($dbname, 'sdm_ho_component', 'id,name');
$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

#data lintas unit (bukan per-PT), kop pakai org karyawan yang login - sama seperti tarikan lain yang serupa
$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
$infoFilter = 'Unit: ' . ($kdOrgCr != '' ? $kdOrgCr . ' - ' . $nmOrg[$kdOrgCr] : 'Semua')
	. ' | Periode: ' . ($periodecr != '' ? $periodecr : 'Semua')
	. ' | Potongan: ' . ($tipePotCr != '' ? $nmPot[$tipePotCr] : 'Semua');

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
		global $hdpt, $infoFilter;
		$width = $this->w - $this->lMargin - $this->rMargin;
		if (file_exists($hdpt['logo'])) {
			$this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 18);
		}
		$this->SetFont('Arial', 'B', 11);
		$this->SetXY($this->lMargin + 22, $this->tMargin + 2);
		$this->Cell(160, 6, bbT($hdpt['nama']), 0, 1, 'L');
		$this->SetY($this->tMargin + 15);
		$this->SetFont('Arial', 'B', 13);
		$this->Cell($width, 7, 'POTONGAN', 0, 1, 'C');
		$this->SetFont('Arial', '', 8);
		$this->Cell($width, 5, bbT($infoFilter), 0, 1, 'C');
		$this->Cell($width, 5, bbT('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
		$this->Ln(1);

		$this->SetFillColor(220, 220, 220);
		$this->SetFont('Arial', 'B', 8);
		$judul = array('No', $_SESSION['lang']['unit'], $_SESSION['lang']['periodegaji'], $_SESSION['lang']['potongan'], 'Jml Karyawan', $_SESSION['lang']['total'], $_SESSION['lang']['updateby']);
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
#urutan kolom: No,Unit,Periode,Potongan,JmlKaryawan,Total,DibuatOleh
$colFixed = array(9, 70, 22, 70, 24, 36); #semua kolom kecuali Dibuat Oleh
$flex = $width - array_sum($colFixed); #sisa lebar jadi lebar kolom Dibuat Oleh
$pdf->colW = array_merge($colFixed, array($flex));
$pdf->AddPage();
$h = 6;
$no = 0;
$ttl = 0;

$resH = fetchData("select h.*,
		(select sum(d.jumlahpotongan) from " . $dbname . ".sdm_potongandt d where d.kodeorg=h.kodeorg and d.periodegaji=h.periodegaji and d.tipepotongan=h.tipepotongan) as jumlah,
		(select count(*) from " . $dbname . ".sdm_potongandt d where d.kodeorg=h.kodeorg and d.periodegaji=h.periodegaji and d.tipepotongan=h.tipepotongan) as jmlkar
	from " . $dbname . ".sdm_potonganht h where " . $filter . " order by periodegaji desc, kodeorg asc");

$ada = false;
foreach ($resH as $r) {
	$ada = true;
	$no++;
	$ttl += $r['jumlah'];

	bbMuat($pdf, $h);
	$pdf->SetFont('Arial', '', 7);
	$c = $pdf->colW;
	$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
	$pdf->Cell($c[1], $h, bbFitTeks($pdf, $r['kodeorg'] . ' - ' . @$nmOrg[$r['kodeorg']], $c[1]), 1, 0, 'L');
	$pdf->Cell($c[2], $h, $r['periodegaji'], 1, 0, 'C');
	$pdf->Cell($c[3], $h, bbFitTeks($pdf, @$nmPot[$r['tipepotongan']], $c[3]), 1, 0, 'L');
	$pdf->Cell($c[4], $h, $r['jmlkar'], 1, 0, 'R');
	$pdf->Cell($c[5], $h, number_format($r['jumlah']), 1, 0, 'R');
	$pdf->Cell($c[6], $h, bbFitTeks($pdf, @$nmKar[$r['updateby']], $c[6]), 1, 0, 'L');
	$pdf->Ln();
}
if (!$ada) {
	$pdf->SetFont('Arial', '', 9);
	$pdf->Cell($width, 8, 'Data tidak ditemukan', 1, 1, 'C');
} else {
	bbMuat($pdf, $h);
	$c = $pdf->colW;
	$pdf->SetFont('Arial', 'B', 7);
	$pdf->Cell($c[0] + $c[1] + $c[2] + $c[3] + $c[4], $h, 'Total', 1, 0, 'R');
	$pdf->Cell($c[5], $h, number_format($ttl), 1, 0, 'R');
	$pdf->Cell($c[6], $h, '', 1, 1, 'L');
}
$pdf->Output();
