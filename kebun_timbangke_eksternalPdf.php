<?php
#cetak per tiket Hasil Timbang TBS ke Eksternal
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');
include_once('lib/zMysql.php');

$notransaksi = isset($_GET['column']) ? $_GET['column'] : '';
$rows = fetchData("select * from " . $dbname . ".pabrik_timbangan where notransaksi='" . addslashes($notransaksi) . "' and millcode='EXTM' and kodeorg IN (" . getOrgDetail(2) . ")");
if (count($rows) == 0) {
	exit('Data tidak ditemukan');
}
$r = $rows[0];

$ptkode = getindukPT($r['kodeorg']);
$hd = setheadreport($ptkode, $ptkode);
$rCus = fetchData("select namacustomer from " . $dbname . ".pmn_4customer where kodecustomer='" . addslashes($r['pabriktujuan']) . "'");
$namaTujuan = (count($rCus) > 0) ? $rCus[0]['namacustomer'] : $r['pabriktujuan'];
$rOrg = fetchData("select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . addslashes($r['kodeorg']) . "'");
$namaUnit = (count($rOrg) > 0) ? $r['kodeorg'] . ' - ' . $rOrg[0]['namaorganisasi'] : $r['kodeorg'];
$fraksi = fetchData("select kode,keterangan from " . $dbname . ".pabrik_5fraksi2 where pt='" . addslashes($ptkode) . "'");
$nilaiFraksi = array();
foreach (fetchData("select kodefraksi,kg from " . $dbname . ".pabrik_sortasi where notiket='" . addslashes($r['notransaksi']) . "'") as $rs) {
	$nilaiFraksi[$rs['kodefraksi']] = $rs['kg'];
}
$waktucetak = date('d-m-Y H:i:s');

function tkeT($v)
{
	return utf8_decode($v);
}
function tkeN($v)
{
	return number_format((float)$v, 0);
}

class PDF extends FPDF
{
	function Header()
	{
		global $hd;
		if (file_exists($hd['logo'])) {
			$this->Image($hd['logo'], 12, 8, 26);
		}
		$this->SetFont('Arial', 'B', 12);
		$this->SetXY(42, 9);
		$this->Cell(0, 6, tkeT($hd['nama']), 0, 1, 'L');
		$this->SetFont('Arial', '', 8);
		$this->SetX(42);
		$this->Cell(0, 4, tkeT($hd['alamat']), 0, 1, 'L');
		if (trim($hd['telepon']) != '') {
			$this->SetX(42);
			$this->Cell(0, 4, 'Telp: ' . $hd['telepon'], 0, 1, 'L');
		}
		$this->Line(10, 30, 200, 30);
		$this->SetY(34);
	}

	function Footer()
	{
		global $waktucetak;
		$this->SetY(-15);
		$this->SetFont('Arial', 'I', 8);
		$this->Cell(0, 10, 'Dicetak: ' . $waktucetak . ' oleh ' . tkeT($_SESSION['empl']['name']) . ' (' . $_SESSION['standard']['username'] . ')     Halaman ' . $this->PageNo(), 0, 0, 'L');
	}

	function Baris($l1, $v1, $l2, $v2)
	{
		$this->SetFont('Arial', '', 9);
		$this->Cell(32, 6, tkeT($l1), 0, 0, 'L');
		$this->Cell(3, 6, ':', 0, 0, 'L');
		$this->Cell(60, 6, tkeT($v1), 0, 0, 'L');
		$this->Cell(32, 6, tkeT($l2), 0, 0, 'L');
		$this->Cell(3, 6, ':', 0, 0, 'L');
		$this->Cell(60, 6, tkeT($v2), 0, 1, 'L');
	}

	function Timbang($label, $nilai, $tebal = false)
	{
		$this->SetFont('Arial', $tebal ? 'B' : '', 9);
		$this->Cell(70, 6, tkeT($label), 1, 0, 'L');
		$this->Cell(40, 6, $nilai, 1, 1, 'R');
	}
}

$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'HASIL TIMBANG TBS KE EKSTERNAL', 0, 1, 'C');
$pdf->Ln(3);

$pdf->Baris('No. Transaksi', $r['notransaksi'], 'Tanggal', tanggalnormal(substr($r['tanggal'], 0, 10)));
$pdf->Baris('Unit', $namaUnit, 'SPB No.', $r['nospb']);
$pdf->Baris('Nomor Ticket', $r['norefrensi'], 'SPB Pabrik', $r['spbpabrik']);
$pdf->Baris('Tujuan Pabrik', $namaTujuan, 'Tahun Tanam', $r['tahuntanam']);
$pdf->Baris('No. Polisi', $r['nokendaraan'], 'Supir', $r['supir']);
$pdf->Baris('Jam Masuk', $r['jammasuk'], 'Jam Keluar', $r['jamkeluar']);
$pdf->Baris('Jumlah Jjg', tkeN($r['jumlahtandan1']), 'Buah Dikembalikan', tkeN($r['buahdikembalikan']));
$pdf->Ln(4);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(110, 6, 'Rincian Timbangan', 1, 1, 'C', true);
$pdf->Timbang('Berat Masuk (Kg)', tkeN($r['beratmasuk']));
$pdf->Timbang('Berat Keluar (Kg)', tkeN($r['beratkeluar']));
$pdf->Timbang('Berat Bruto (Kg)', tkeN($r['beratmasuk'] - $r['beratkeluar']), true);
foreach ($fraksi as $fr) {
	$pdf->Timbang('Potongan ' . $fr['keterangan'] . ' (Kg)', tkeN(isset($nilaiFraksi[$fr['kode']]) ? $nilaiFraksi[$fr['kode']] : 0));
}
$pdf->Timbang('Total Potongan (Kg)', tkeN($r['kgpotsortasi']), true);
$pdf->Timbang('Berat Bersih (Kg)', tkeN($r['beratbersih']), true);
$pdf->Ln(6);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, 'Diinput oleh: ' . tkeT($r['username']), 0, 1, 'L');

$pdf->Output();
