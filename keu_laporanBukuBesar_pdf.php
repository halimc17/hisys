<?php
#PDF Neraca Saldo (nama file lama keu_laporanBukuBesar_pdf.php, dipanggil dari halaman keu_2bukubesar.php),
#mengikuti data yang sama dengan preview/excel (keu_2bukubesarnew_slave.php / keu_slave_2bukubesarrekap.php).
#Sebelumnya PDF ini punya filter "noakun not like '3%'" yang tidak ada di preview/excel, sehingga akun
#golongan 3 (modal/ekuitas) hilang di PDF tapi muncul di preview - sudah dihapus supaya datanya konsisten.
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');

$pt = checkPostGet('pt', '');
$gudang = checkPostGet('gudang', '');
$periode = checkPostGet('periode', '');
$periode1 = checkPostGet('periode1', '');
$revisi = (int)checkPostGet('revisi', '0');
$regional = checkPostGet('regional', '');
$akundari = checkPostGet('akundari', '');
$akunsampai = checkPostGet('akunsampai', '');
$tampilanId = checkPostGet('tampilanId', '');

#cek periode dan periode1, sama seperti di slave preview/excel
if ($periode1 < $periode) {
	$z = $periode;
	$periode = $periode1;
	$periode1 = $z;
}
$whereAkun = '';
if ($akundari != '' && $akunsampai != '') {
	$whereAkun = " and noakun between '" . addslashes($akundari) . "' and '" . addslashes($akunsampai) . "'";
}

#akun laba/rugi tahun berjalan (CLM), dikecualikan dari daftar akun - sama seperti laporan lain
$CLM = '';
$rClm = fetchData("select noakundebet from " . $dbname . ".keu_5parameterjurnal where kodeaplikasi='CLM'");
if (count($rClm) > 0) {
	$CLM = $rClm[0]['noakundebet'];
}

#cakupan unit: PT saja, PT+regional, atau satu unit - daftar kodeorg diambil dulu (query kecil terpisah) lalu
#ditempel sebagai IN(literal) di query besar - jauh lebih cepat daripada IN(select ...) langsung
#(lihat catatan optimasi di keu_2bukubesarnew_slave.php)
if ($regional == '' && $gudang == '') {
	$rUnit = fetchData("select kodeorganisasi from " . $dbname . ".organisasi where induk='" . addslashes($pt) . "' and length(kodeorganisasi)=4");
} elseif ($regional != '' && $gudang == '') {
	$rUnit = fetchData("select kodeunit as kodeorganisasi from " . $dbname . ".bgt_regional_assignment where regional='" . addslashes($regional) . "'"
		. " and kodeunit in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . addslashes($pt) . "')");
} else {
	$rUnit = array(array('kodeorganisasi' => $gudang));
}
$listUnit = array();
foreach ($rUnit as $r) {
	$listUnit[] = "'" . addslashes($r['kodeorganisasi']) . "'";
}
$whereUnit = (count($listUnit) > 0) ? " and kodeorg in (" . implode(',', $listUnit) . ")" : " and 1=0";

#daftar akun dalam rentang, CLM dikecualikan
$TAB = array();
$kolNama = ($_SESSION['language'] == 'ID') ? 'namaakun' : 'namaakun1 as namaakun';
$sqlAkun = "select distinct noakun," . $kolNama . " from " . $dbname . ".keu_5akun where noakun!='" . addslashes($CLM) . "' " . $whereAkun . " order by noakun";
foreach (fetchData($sqlAkun) as $r) {
	$TAB[$r['noakun']] = array('noakun' => $r['noakun'], 'namaakun' => $r['namaakun'], 'sawal' => 0, 'debet' => 0, 'kredit' => 0, 'salak' => 0);
}

#saldo awal periode (kolom awalNN pada baris periode itu sendiri, sudah pasti terisi - lihat catatan di keu_2neracasaldoperiodik_filter.php)
$kolAwal = 'awal' . substr(str_replace('-', '', $periode), 4, 2);
$sqlAwal = "select sum(" . $kolAwal . ") as sawal, noakun from " . $dbname . ".keu_saldobulanan where periode='" . addslashes(str_replace('-', '', $periode)) . "' and noakun!='" . addslashes($CLM) . "' " . $whereUnit . " group by noakun";
foreach (fetchData($sqlAwal) as $r) {
	if (isset($TAB[$r['noakun']])) {
		$TAB[$r['noakun']]['sawal'] = (float)$r['sawal'];
		$TAB[$r['noakun']]['salak'] = (float)$r['sawal'];
	}
}

#debet/kredit dari jurnal, direntang periode s/d periode1
$sqlJurnal = "select sum(debet) as debet, sum(kredit) as kredit, noakun from " . $dbname . ".keu_jurnaldt_vw
	where periode>='" . addslashes($periode) . "' and periode<='" . addslashes($periode1) . "' " . $whereUnit . " " . $whereAkun . "
	and noakun!='" . addslashes($CLM) . "' and revisi<='" . $revisi . "' group by noakun";
foreach (fetchData($sqlJurnal) as $r) {
	if (isset($TAB[$r['noakun']])) {
		$TAB[$r['noakun']]['debet'] = (float)$r['debet'];
		$TAB[$r['noakun']]['kredit'] = (float)$r['kredit'];
		$TAB[$r['noakun']]['salak'] = $TAB[$r['noakun']]['sawal'] + $r['debet'] - $r['kredit'];
	}
}

#buang akun yang nol semua, bila diminta
if ($tampilanId == 1) {
	foreach ($TAB as $k => $d) {
		if ($d['sawal'] == 0 && $d['debet'] == 0 && $d['kredit'] == 0) {
			unset($TAB[$k]);
		}
	}
}

$hdpt = setheadreport($pt, $pt);
if ($gudang != '') {
	$rUnit = fetchData("select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . addslashes($gudang) . "'");
	$nmUnit = $gudang . (count($rUnit) > 0 ? ' - ' . $rUnit[0]['namaorganisasi'] : '');
} else {
	$nmUnit = 'Seluruh unit PT ' . $pt;
}
$infofilter = 'PT: ' . $pt . ' | Unit: ' . $nmUnit . ' | Periode: ' . $periode . ' s/d ' . $periode1 . ' | Revisi: ' . $revisi;

function bbT($v)
{
	return utf8_decode($v);
}

#nama akun yang kepanjangan dipotong+"..." supaya tidak tumpang tindih ke kolom sebelah
function bbFitTeks($pdf, $txt, $w, $f = 8)
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
	public $infofilter = '';
	public $colW = array();

	function Header()
	{
		global $hdpt;
		$width = $this->w - $this->lMargin - $this->rMargin;
		if (file_exists($hdpt['logo'])) {
			$this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 20);
		}
		$this->SetFont('Arial', 'B', 11);
		$this->SetXY($this->lMargin + 24, $this->tMargin + 2);
		$this->Cell(160, 6, bbT($hdpt['nama']), 0, 1, 'L');
		$this->SetY($this->tMargin + 15);
		$this->SetFont('Arial', 'B', 13);
		$this->Cell($width, 7, 'NERACA SALDO', 0, 1, 'C');
		$this->SetFont('Arial', '', 8);
		$this->Cell($width, 5, bbT($this->infofilter), 0, 1, 'C');
		$this->Cell($width, 5, bbT('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
		$this->Ln(1);

		$this->SetFillColor(220, 220, 220);
		$this->SetFont('Arial', 'B', 8);
		$judul = array('No', 'No Akun', 'Nama Akun', 'Saldo Awal', 'Debet', 'Kredit', 'Saldo Akhir');
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

$pdf = new PDF('P', 'mm', 'A4');
$pdf->infofilter = $infofilter;
$pdf->AliasNbPages();
#margin bawah 16 dipakai murni sebagai batas nspMuat/bbMuat (auto-page-break FPDF tetap mati) supaya baris
#terakhir tidak tumpang tindih dengan teks "Halaman.." di Footer() yang ada di -12
$pdf->SetAutoPageBreak(false, 16);
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
$pdf->colW = array(10, 22, $width - 10 - 22 - 30 * 4, 30, 30, 30, 30);
$pdf->AddPage();
$h = 6;
$no = 0;
$tot = array('sawal' => 0, 'debet' => 0, 'kredit' => 0, 'salak' => 0);

if (count($TAB) == 0) {
	$pdf->SetFont('Arial', '', 9);
	$pdf->Cell($width, 8, 'Data tidak ditemukan', 1, 1, 'C');
} else {
	foreach ($TAB as $noakun => $d) {
		$no++;
		bbMuat($pdf, $h);
		$pdf->SetFont('Arial', '', 8);
		$c = $pdf->colW;
		$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
		$pdf->Cell($c[1], $h, $noakun, 1, 0, 'L');
		$pdf->Cell($c[2], $h, bbFitTeks($pdf, $d['namaakun'], $c[2]), 1, 0, 'L');
		$pdf->Cell($c[3], $h, number_format($d['sawal'], 2), 1, 0, 'R');
		$pdf->Cell($c[4], $h, number_format($d['debet'], 2), 1, 0, 'R');
		$pdf->Cell($c[5], $h, number_format($d['kredit'], 2), 1, 0, 'R');
		$pdf->Cell($c[6], $h, number_format($d['salak'], 2), 1, 0, 'R');
		$pdf->Ln();
		$tot['sawal'] += $d['sawal'];
		$tot['debet'] += $d['debet'];
		$tot['kredit'] += $d['kredit'];
		$tot['salak'] += $d['salak'];
	}
	bbMuat($pdf, $h);
	$pdf->SetFont('Arial', 'B', 8);
	$pdf->SetFillColor(240, 240, 240);
	$c = $pdf->colW;
	$pdf->Cell($c[0] + $c[1] + $c[2], $h, 'TOTAL', 1, 0, 'C', true);
	$pdf->Cell($c[3], $h, number_format($tot['sawal'], 2), 1, 0, 'R', true);
	$pdf->Cell($c[4], $h, number_format($tot['debet'], 2), 1, 0, 'R', true);
	$pdf->Cell($c[5], $h, number_format($tot['kredit'], 2), 1, 0, 'R', true);
	$pdf->Cell($c[6], $h, number_format($tot['salak'], 2), 1, 0, 'R', true);
	$pdf->Ln();
}
$pdf->Output();
