<?php
#PDF Pekerjaan, dipanggil dari vhc_pekerjaan_v2.php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
include_once('lib/zLib.php');

$tgl_cari      = checkPostGet('tgl_cari', '');
$tgl_carisd    = tanggalsystemn(checkPostGet('tgl_carisd', ''));
$txtCari       = checkPostGet('txtCari', '');
$kodevhc_cari  = checkPostGet('kodevhc_cari', '');
$kontanan_cari = checkPostGet('kontanan_cari', '%');
$posting_cari  = checkPostGet('posting_cari', '');

#filter sama persis dengan vhc_slave_pekerjaan_v2.php (case loaddata/excel), supaya tarikan PDF ikut filter yang aktif
$where = "";
if ($tgl_cari != '') {
	$txtTgl = tanggalsystemn($tgl_cari);
	if ($tgl_carisd != '--') {
		$where .= " AND tanggal BETWEEN '" . $txtTgl . "' AND '" . $tgl_carisd . "' ";
	} else {
		$where .= " and tanggal='" . $txtTgl . "'";
	}
}
if ($txtCari != '') {
	$where .= " and notransaksi like '%" . addslashes(trim($txtCari)) . "%'";
}
if ($kodevhc_cari != '') {
	$where .= " and kodevhc like '%" . addslashes(trim($kodevhc_cari)) . "%'";
}
if ($kontanan_cari != '%') {
	$where .= " and kontanan = '" . addslashes($kontanan_cari) . "'";
}
if (in_array($posting_cari, array('0', '1'), true)) {
	$where .= " and posting = '" . $posting_cari . "'";
}
if (checkPostGet('periode_cari', '') != '') {
	$where .= " and left(tanggal,7) = '" . addslashes(checkPostGet('periode_cari', '')) . "'";
}
if (checkPostGet('operator_cari', '') != '') {
	$where .= " and notransaksi in (select notransaksi from " . $dbname . ".vhc_rundt where operator = '" . addslashes(checkPostGet('operator_cari', '')) . "')";
}
$whereOrg = "substr(kodeorg,1,4) regexp '" . str_replace(',', '|', str_replace("'", "", getOrgDetail(2))) . "' " . $where;

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
		$this->Cell($width, 7, 'PEKERJAAN', 0, 1, 'C');
		$this->SetFont('Arial', '', 8);
		$this->Cell($width, 5, bbT('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
		$this->Ln(1);

		$this->SetFillColor(220, 220, 220);
		$this->SetFont('Arial', 'B', 8);
		$judul = array('No', $_SESSION['lang']['notransaksi'], $_SESSION['lang']['kodevhc'], $_SESSION['lang']['nopol'], $_SESSION['lang']['detail'], $_SESSION['lang']['operator'], $_SESSION['lang']['tanggal'], $_SESSION['lang']['vhc_jumlah_bbm'], $_SESSION['lang']['kontanan'], 'Status Posting', 'Diposting Oleh');
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
#urutan kolom: No,NoTransaksi,KodeVhc,NoPol,Rincian,Operator,Tanggal,JmlBBM,Kontanan,StatusPosting,DipostingOleh
$colFixed = array(8, 36, 24, 22, 44, 44, 18, 18, 15, 22); #semua kolom kecuali Diposting Oleh
$flex = $width - array_sum($colFixed); #sisa lebar jadi lebar kolom Diposting Oleh
$pdf->colW = array_merge($colFixed, array($flex));
$pdf->AddPage();
$h = 6;
$no = 0;

$resH = fetchData("select * from " . $dbname . ".vhc_runht where " . $whereOrg . " order by tanggal desc, notransaksi desc");

#nama operator per transaksi (unik, urut sesuai input), diambil sekali untuk semua transaksi yang difilter
$arOpt = array();
$resD = fetchData("select notransaksi,operator from " . $dbname . ".vhc_rundt where notransaksi in (select notransaksi from " . $dbname . ".vhc_runht where " . $whereOrg . ") order by notransaksi");
foreach ($resD as $d) {
	if ($d['operator'] != '' && $d['operator'] != '0000000000') {
		$arOpt[$d['notransaksi']][$d['operator']] = $d['operator'];
	}
}
$nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmVhc = makeOption($dbname, 'vhc_5master', 'kodevhc,nopol');
$nmDet = makeOption($dbname, 'vhc_5master', 'kodevhc,detailvhc');

$ada = false;
foreach ($resH as $r) {
	$ada = true;
	$no++;
	$oper = array();
	if (isset($arOpt[$r['notransaksi']])) {
		foreach ($arOpt[$r['notransaksi']] as $kid) {
			$oper[] = @$nmKar[$kid];
		}
	}
	$posted = ($r['posting'] == 1);

	bbMuat($pdf, $h);
	$pdf->SetFont('Arial', '', 7);
	$c = $pdf->colW;
	$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
	$pdf->Cell($c[1], $h, bbFitTeks($pdf, $r['notransaksi'], $c[1]), 1, 0, 'L');
	$pdf->Cell($c[2], $h, bbFitTeks($pdf, $r['kodevhc'], $c[2]), 1, 0, 'L');
	$pdf->Cell($c[3], $h, bbFitTeks($pdf, @$nmVhc[$r['kodevhc']], $c[3]), 1, 0, 'L');
	$pdf->Cell($c[4], $h, bbFitTeks($pdf, @$nmDet[$r['kodevhc']], $c[4]), 1, 0, 'L');
	$pdf->Cell($c[5], $h, bbFitTeks($pdf, implode(', ', $oper), $c[5]), 1, 0, 'L');
	$pdf->Cell($c[6], $h, tanggalnormal($r['tanggal']), 1, 0, 'C');
	$pdf->Cell($c[7], $h, $r['jlhbbm'], 1, 0, 'R');
	$pdf->Cell($c[8], $h, ($r['kontanan'] != '' ? 'YA' : 'TIDAK'), 1, 0, 'C');
	$pdf->Cell($c[9], $h, ($posted ? 'Posted' : 'Belum Posting'), 1, 0, 'C');
	$pdf->Cell($c[10], $h, bbFitTeks($pdf, ($posted ? @$nmKar[$r['postingby']] : ''), $c[10]), 1, 0, 'L');
	$pdf->Ln();
}
if (!$ada) {
	$pdf->SetFont('Arial', '', 9);
	$pdf->Cell($width, 8, 'Data tidak ditemukan', 1, 1, 'C');
}
$pdf->Output();
