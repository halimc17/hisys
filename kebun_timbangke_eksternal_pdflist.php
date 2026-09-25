<?php
#PDF list Hasil Timbang TBS ke Eksternal, mengikuti seluruh filter list
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');
require_once('kebun_timbangke_eksternal_filter.php');

$flt = tkeFilter($_GET);
if ($flt['error'] != '') {
	exit($flt['error']);
}
$ptkode = getindukPT($_SESSION['empl']['lokasitugas']);
$hdpt = setheadreport($ptkode, $ptkode);
$waktucetak = date('d-m-Y H:i:s');
$infofilter = ($flt['info'] != '') ? $flt['info'] : 'Seluruh data';

$mapTujuan = array();
foreach (fetchData("select distinct kodecustomer,namacustomer from " . $dbname . ".pmn_4customer") as $rCus) {
	$mapTujuan[$rCus['kodecustomer']] = $rCus['namacustomer'];
}

function tkeT($v)
{
	return utf8_decode($v);
}

#potong teks agar muat di kolom
function tkeFit($pdf, $txt, $w)
{
	$txt = utf8_decode($txt);
	while (strlen($txt) > 1 && $pdf->GetStringWidth($txt) > $w - 3) {
		$txt = substr($txt, 0, -1);
	}
	return $txt;
}

#lebar kolom dalam persen: No, Tiket, Tanggal, SPB, SPB Pabrik, Tujuan, No Ticket, Nopol, Supir, TT, Jjg, Masuk, Keluar, Bruto, Pot, Bersih, User
$kolom = array(
	array('No.', 3, 'C'), array('Tiket', 7, 'C'), array('Tanggal', 6, 'C'), array('SPB No.', 11, 'L'), array('SPB Pabrik', 7, 'L'),
	array('Tujuan Pabrik', 11, 'L'), array('No. Ticket', 6, 'L'), array('No. Polisi', 7, 'L'), array('Supir', 8, 'L'), array('TT', 3.5, 'C'),
	array('Jjg', 3.5, 'R'), array('Masuk', 4.5, 'R'), array('Keluar', 4.5, 'R'), array('Bruto', 4.5, 'R'), array('Pot.', 3.5, 'R'),
	array('Bersih', 4.5, 'R'), array('User', 6, 'L')
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
		$this->Cell(400, 12, tkeT($hdpt['nama']), 0, 1, 'L');
		$this->SetY($this->tMargin + 40);
		$this->SetFont('Arial', 'B', 11);
		$this->Cell($width, 12, 'HASIL TIMBANG TBS KE EKSTERNAL', 0, 1, 'C');
		$this->SetFont('Arial', '', 8);
		$this->Cell($width, 10, tkeT($infofilter), 0, 1, 'C');
		$this->Cell($width, 10, tkeT('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
		$this->Ln(2);
		$this->SetFont('Arial', 'B', 8);
		$this->SetFillColor(220, 220, 220);
		foreach ($kolom as $k) {
			$this->Cell($k[1] / 100 * $width, 14, $k[0], 1, 0, 'C', true);
		}
		$this->Ln();
	}

	function Footer()
	{
		$this->SetY(-25);
		$this->SetFont('Arial', 'I', 8);
		$this->Cell(0, 10, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
	}
}

$pdf = new PDF('L', 'pt', 'A4');
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 35);
$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
$pdf->AddPage();
$pdf->SetFont('Arial', '', 7);
$h = 12;

$sql = "select * from " . $dbname . ".pabrik_timbangan where kodeorg IN (" . getOrgDetail(2) . ") and millcode='EXTM' and char_length(notransaksi)>7 " . $flt['where'] . " order by left(`tanggal`,10) desc, notransaksi desc";
$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$no = 0;
$tot = array('jjg' => 0, 'masuk' => 0, 'keluar' => 0, 'bruto' => 0, 'pot' => 0, 'bersih' => 0);
while ($r = $res->fetch()) {
	$no++;
	$bruto = $r['beratmasuk'] - $r['beratkeluar'];
	$tot['jjg'] += $r['jumlahtandan1'];
	$tot['masuk'] += $r['beratmasuk'];
	$tot['keluar'] += $r['beratkeluar'];
	$tot['bruto'] += $bruto;
	$tot['pot'] += $r['kgpotsortasi'];
	$tot['bersih'] += $r['beratbersih'];
	$tujuan = isset($mapTujuan[$r['pabriktujuan']]) ? $mapTujuan[$r['pabriktujuan']] : $r['pabriktujuan'];
	$isi = array(
		$no, $r['notransaksi'], tanggalnormal(substr($r['tanggal'], 0, 10)), $r['nospb'], $r['spbpabrik'], $tujuan, $r['norefrensi'],
		$r['nokendaraan'], $r['supir'], $r['tahuntanam'], number_format($r['jumlahtandan1']), number_format($r['beratmasuk']),
		number_format($r['beratkeluar']), number_format($bruto), number_format($r['kgpotsortasi']), number_format($r['beratbersih']), $r['username']
	);
	foreach ($kolom as $i => $k) {
		$w = $k[1] / 100 * $width;
		$pdf->Cell($w, $h, tkeFit($pdf, (string)$isi[$i], $w), 1, 0, $k[2]);
	}
	$pdf->Ln();
}
if ($no == 0) {
	$pdf->Cell($width, $h, 'Data tidak ditemukan', 1, 1, 'C');
} else {
	$pdf->SetFont('Arial', 'B', 7);
	$pdf->SetFillColor(240, 240, 240);
	$lblw = 0;
	for ($i = 0; $i <= 9; $i++) {
		$lblw += $kolom[$i][1] / 100 * $width;
	}
	$pdf->Cell($lblw, $h, 'TOTAL', 1, 0, 'C', true);
	$vals = array($tot['jjg'], $tot['masuk'], $tot['keluar'], $tot['bruto'], $tot['pot'], $tot['bersih']);
	foreach ($vals as $j => $v) {
		$pdf->Cell($kolom[10 + $j][1] / 100 * $width, $h, number_format($v), 1, 0, 'R', true);
	}
	$pdf->Cell($kolom[16][1] / 100 * $width, $h, '', 1, 1, 'L', true);
}

$pdf->Output();
