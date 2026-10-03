<?php
#PDF Rekap Sewa HM, dipanggil dari lgl_rekapsewahm.php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');

$param = $_POST; if(count($param)==0){ $param = $_GET; }

#tarikan per-baris (ikon PDF di Aksi) kirim kodeorgx/periodex/spkx/periodebyrx buat exact-match 1 baris saja -
#kalau gak ada, ikut filter yang lagi aktif di form Cari
$where = '';
if ($param['kodeorgx'] != '' && $param['periodex'] != '' && $param['periodebyrx'] != '' && $param['spkx'] != '') {
	$where = " and kodeorg='" . $param['kodeorgx'] . "' and periode='" . $param['periodex'] . "' and periodebyr='" . $param['periodebyrx'] . "' and spk='" . $param['spkx'] . "'";
} elseif ($param['nospkcr'] != '' || $param['divsch'] != '' || $param['tglsch'] != '' || $param['kontrakcr'] != '' || $param['notraksicr'] != '') {
	$where .= "and spk LIKE '%" . $param['nospkcr'] . "%' and kodeorg LIKE '%" . $param['divsch'] . "%' and periode LIKE '%" . $param['tglsch'] . "%'";
	if ($param['notraksicr'] != '') {
		$where .= " and notraksi LIKE '%" . addslashes($param['notraksicr']) . "%'";
	}
	if ($param['kontrakcr'] != '') {
		$rSupp = fetchData("select supplierid from " . $dbname . ".log_5supplier where namasupplier LIKE '%" . addslashes($param['kontrakcr']) . "%'");
		$listSupp = array();
		foreach ($rSupp as $r) { $listSupp[] = "'" . addslashes($r['supplierid']) . "'"; }
		if (count($listSupp) > 0) {
			$rSpk = fetchData("select notransaksi from " . $dbname . ".lgl_pengajuanspkht where koderekanan in (" . implode(',', $listSupp) . ")");
			$listSpk = array();
			foreach ($rSpk as $r) { $listSpk[] = "'" . addslashes($r['notransaksi']) . "'"; }
			$where .= (count($listSpk) > 0) ? " and spk in (" . implode(',', $listSpk) . ")" : " and 1=0";
		} else {
			$where .= " and 1=0";
		}
	}
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
		$this->Cell($width, 7, 'REKAP SEWA HM', 0, 1, 'C');
		$this->SetFont('Arial', '', 8);
		$this->Cell($width, 5, bbT('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
		$this->Ln(1);

		$this->SetFillColor(220, 220, 220);
		$this->SetFont('Arial', 'B', 8);
		$judul = array('No', $_SESSION['lang']['unit'], $_SESSION['lang']['bulan'], $_SESSION['lang']['periode'], $_SESSION['lang']['nospk'], $_SESSION['lang']['notransaksi'] . ' Traksi', $_SESSION['lang']['kontraktor'], $_SESSION['lang']['rupiah'], 'No BAPP', 'Create By', 'Create Time', 'Posted By');
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
#urutan kolom: No,Unit(flexible),Bulan,Periode,NoSPK,NoTraksi,Kontraktor,Rupiah,NoBAPP,CreateBy,CreateTime,PostedBy
$pdf->colW = array(8, 0, 14, 32, 22, 24, 28, 22, 26, 22, 20, 22);
$diff = $width - array_sum($pdf->colW);
$pdf->colW[1] = $diff; #sisa lebar jadi lebar kolom Unit
$pdf->AddPage();
$h = 6;
$no = 0;
$ttlrupiah = 0;

$iList = "SELECT *,SUM(totalprestasi) AS totalprestasi FROM " . $dbname . ".lgl_rekapsewahm WHERE 1=1 $where GROUP BY periodebyr,periode,spk ORDER BY periode DESC,kodeorg";
$hasil = fetchdata($iList);
$nmkaryawan = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');

if (count($hasil) == 0) {
	$pdf->SetFont('Arial', '', 9);
	$pdf->Cell($width, 8, 'Data tidak ditemukan', 1, 1, 'C');
} else {
	foreach ($hasil as $dList) {
		$optOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $dList['kodeorg'] . "'");
		$optKontraktor = makeOption($dbname, 'lgl_pengajuanspkht', 'notransaksi,koderekanan', "notransaksi='" . $dList['spk'] . "'");
		$qr = "SELECT SUM(rupiah) AS rupiah FROM " . $dbname . ".lgl_rekapsewahmdt WHERE notraksi IN (SELECT notraksi FROM " . $dbname . ".lgl_rekapsewahm WHERE spk='{$dList['spk']}' AND periodebyr='{$dList['periodebyr']}' AND periode='{$dList['periode']}')";
		$rs = fetchdata($qr);
		$rupiah = (float)$rs[0]['rupiah'];

		$no++;
		bbMuat($pdf, $h);
		$pdf->SetFont('Arial', '', 7);
		$c = $pdf->colW;
		$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
		$pdf->Cell($c[1], $h, bbFitTeks($pdf, $dList['kodeorg'] . ' - ' . $optOrg[$dList['kodeorg']], $c[1]), 1, 0, 'L');
		$pdf->Cell($c[2], $h, $dList['periode'], 1, 0, 'C');
		$pdf->Cell($c[3], $h, bbT(tanggalnormal($dList['tgldari']) . ' s.d ' . tanggalnormal($dList['tglsampai'])), 1, 0, 'C');
		$pdf->Cell($c[4], $h, bbFitTeks($pdf, $dList['spk'], $c[4]), 1, 0, 'L');
		$pdf->Cell($c[5], $h, bbFitTeks($pdf, $dList['notraksi'], $c[5]), 1, 0, 'L');
		$pdf->Cell($c[6], $h, bbFitTeks($pdf, getNamaSupplier($optKontraktor[$dList['spk']]), $c[6]), 1, 0, 'L');
		$pdf->Cell($c[7], $h, number_format($rupiah, 0), 1, 0, 'R');
		$pdf->Cell($c[8], $h, bbFitTeks($pdf, $dList['nobapp'], $c[8]), 1, 0, 'L');
		$pdf->Cell($c[9], $h, bbFitTeks($pdf, @$nmkaryawan[$dList['createby']], $c[9]), 1, 0, 'L');
		$pdf->Cell($c[10], $h, $dList['createtime'], 1, 0, 'C');
		$pdf->Cell($c[11], $h, bbFitTeks($pdf, ($dList['posting']==1 ? @$nmkaryawan[$dList['postingby']] : '-'), $c[11]), 1, 0, 'L');
		$pdf->Ln();
		$ttlrupiah += $rupiah;
	}
	bbMuat($pdf, $h);
	$pdf->SetFont('Arial', 'B', 7);
	$pdf->SetFillColor(240, 240, 240);
	$c = $pdf->colW;
	$pdf->Cell($c[0] + $c[1] + $c[2] + $c[3] + $c[4] + $c[5] + $c[6], $h, 'TOTAL', 1, 0, 'C', true);
	$pdf->Cell($c[7], $h, number_format($ttlrupiah, 0), 1, 0, 'R', true);
	$pdf->Cell($c[8] + $c[9] + $c[10] + $c[11], $h, '', 1, 0, 'C', true);
	$pdf->Ln();
}
$pdf->Output();
