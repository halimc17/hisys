<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zFunction.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses', 'excel');
$txtsearch = checkPostGet('txtsearch', '');
$noktp = checkPostGet('noktp', '');
$orgsearch = checkPostGet('orgsearch', '');
$jabatansearch = checkPostGet('jabatansearch', '');
$tipesearch = checkPostGet('tipesearch', '');
$statussearch = checkPostGet('statussearch', '');
$divisisearch = checkPostGet('divisisearch', '');
$golongansearch = checkPostGet('golongansearch', '');

function escSql($v)
{
	global $owlPDO;
	return substr($owlPDO->quote($v), 1, -1);
}

#filter sama dengan list di sdm_slave_load_employee_list.php (case loaddata)
$tglhrini = date('Y-m-d');
$where = '';
if ($txtsearch != '') {
	$where .= " and a.namakaryawan like '%" . escSql($txtsearch) . "%'";
}
if ($noktp != '') {
	$where .= " and a.noktp like '%" . escSql($noktp) . "%'";
}
if ($orgsearch != '') {
	$where .= " and (a.lokasitugas='" . escSql($orgsearch) . "' or a.subbagian='" . escSql($orgsearch) . "') ";
}
if ($jabatansearch != '') {
	$where .= " and a.kodejabatan='" . escSql($jabatansearch) . "'";
}
if ($tipesearch != '') {
	$where .= " and a.tipekaryawan='" . escSql($tipesearch) . "'";
}
if ($divisisearch == 'KANTOR') {
	$where .= " and (a.subbagian='' or a.subbagian is null)";
} elseif ($divisisearch != '') {
	$where .= " and a.subbagian='" . escSql($divisisearch) . "'";
}
if ($golongansearch != '') {
	$where .= " and a.kodegolongan='" . escSql($golongansearch) . "'";
}
if ($statussearch == '*') {
	$where .= " and (tanggalkeluar!='0000-00-00' and tanggalkeluar<'" . $tglhrini . "')";
} elseif ($statussearch == '0000-00-00') {
	$where .= " and (tanggalkeluar>= '" . $tglhrini . "' or tanggalkeluar='0000-00-00')";
}

$str = "select a.*,b.namajabatan,c.namagolongan,d.tipe from " . $dbname . ".datakaryawan a,
	" . $dbname . ".sdm_5jabatan b, " . $dbname . ".sdm_5golongan c, " . $dbname . ".sdm_5tipekaryawan d where
	lokasitugas in(" . getOrgDetail(2) . ") and namakaryawan not like '%ADMINISTRATOR%'
	and a.kodejabatan=b.kodejabatan and a.kodegolongan=c.kodegolongan
	and d.id=a.tipekaryawan
	" . $where . "
	order by a.namakaryawan asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);

#pendidikan terakhir per level (sama dengan list: baris terakhir yang dipakai)
$optPendidikan = array();
$resp = $owlPDO->query("select levelpendidikan,kelompok from " . $dbname . ".sdm_5pendidikan");
$resp->setFetchMode(PDO::FETCH_OBJ);
while ($barp = $resp->fetch()) {
	$optPendidikan[$barp->levelpendidikan] = $barp->kelompok;
}

$data = array();
while ($bar = $res->fetch()) {
	$data[] = $bar;
}

#keterangan filter untuk judul laporan
$optJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$optTipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
$optGolongan = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
$ket = array();
$ket[] = 'Lokasi Tugas: ' . ($orgsearch != '' ? $orgsearch : 'Seluruhnya');
$ket[] = 'Jabatan: ' . ($jabatansearch != '' ? $optJabatan[$jabatansearch] : 'Seluruhnya');
$ket[] = 'Tipe Karyawan: ' . ($tipesearch != '' ? $optTipe[$tipesearch] : 'Seluruhnya');
$ket[] = 'Divisi: ' . ($divisisearch != '' ? $divisisearch : 'Seluruhnya');
$ket[] = 'Golongan: ' . ($golongansearch != '' ? $optGolongan[$golongansearch] : 'Seluruhnya');
$ket[] = 'Status: ' . ($statussearch == '*' ? 'Tidak Aktif' : ($statussearch == '0000-00-00' ? 'Aktif' : 'Seluruhnya'));
if ($txtsearch != '') {
	$ket[] = 'Nama: ' . $txtsearch;
}
if ($noktp != '') {
	$ket[] = 'No. KTP: ' . $noktp;
}
$ketfilter = implode(' | ', $ket);

#PT untuk kop: unit yang dipilih, atau lokasi tugas user
$unitcetak = ($orgsearch != '') ? substr($orgsearch, 0, 4) : $_SESSION['empl']['lokasitugas'];
$ptkode = getindukPT($unitcetak);
$hdpt = setheadreport($ptkode, $ptkode);

function tglKaryawan($tgl)
{
	return ($tgl == '' || $tgl == '0000-00-00') ? '-' : tanggalnormal($tgl);
}

if ($proses == 'pdf') {
	class PDF extends FPDF
	{
		function Header()
		{
			global $hdpt, $ketfilter;
			$width = $this->w - $this->lMargin - $this->rMargin;
			$height = 11;
			if (file_exists($hdpt['logo'])) {
				$this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 42);
			}
			$this->SetFont('Arial', 'B', 10);
			$this->SetXY($this->lMargin + 50, $this->tMargin + 6);
			$this->Cell(400, 12, $hdpt['nama'], 0, 1, 'L');
			$this->SetY($this->tMargin + 40);
			$this->SetFont('Arial', 'B', 11);
			$this->Cell($width, $height, 'Data Karyawan', 0, 1, 'C');
			$this->SetFont('Arial', '', 8);
			$this->Cell($width, $height, $ketfilter, 0, 1, 'C');
			$this->Ln(2);
			$this->SetFont('Arial', 'B', 6);
			$this->SetFillColor(220, 220, 220);
			$this->Cell(3 / 100 * $width, $height, 'No', 1, 0, 'C', 1);
			$this->Cell(9 / 100 * $width, $height, 'NIK', 1, 0, 'C', 1);
			$this->Cell(18 / 100 * $width, $height, 'Nama', 1, 0, 'C', 1);
			$this->Cell(15 / 100 * $width, $height, 'Jabatan', 1, 0, 'C', 1);
			$this->Cell(6 / 100 * $width, $height, 'Golongan', 1, 0, 'C', 1);
			$this->Cell(5 / 100 * $width, $height, 'Lokasi', 1, 0, 'C', 1);
			$this->Cell(6 / 100 * $width, $height, 'Divisi', 1, 0, 'C', 1);
			$this->Cell(11 / 100 * $width, $height, 'No. KTP', 1, 0, 'C', 1);
			$this->Cell(7 / 100 * $width, $height, 'Tipe', 1, 0, 'C', 1);
			$this->Cell(7 / 100 * $width, $height, 'Tgl Masuk', 1, 0, 'C', 1);
			$this->Cell(6 / 100 * $width, $height, 'Status', 1, 0, 'C', 1);
			$this->Cell(7 / 100 * $width, $height, 'Tgl Keluar', 1, 1, 'C', 1);
		}

		function Footer()
		{
			$this->SetY(-15);
			$this->SetFont('Arial', 'I', 7);
			$this->Cell(0, 10, 'Print Time: ' . date('H:i:s, d/m/Y') . ' By: ' . $_SESSION['empl']['name'] . '   Page ' . $this->PageNo(), 0, 0, 'L');
		}
	}

	$pdf = new PDF('L', 'pt', 'A4');
	$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
	$height = 12;
	$pdf->AddPage();
	$pdf->SetFillColor(255, 255, 255);
	$pdf->SetFont('Arial', '', 6);

	$no = 0;
	foreach ($data as $bar) {
		$no++;
		$pdf->Cell(3 / 100 * $width, $height, $no, 1, 0, 'C');
		$pdf->Cell(9 / 100 * $width, $height, substr($bar->nik, 0, 18), 1, 0, 'L');
		$pdf->Cell(18 / 100 * $width, $height, substr(strtoupper($bar->namakaryawan), 0, 36), 1, 0, 'L');
		$pdf->Cell(15 / 100 * $width, $height, substr($bar->namajabatan, 0, 30), 1, 0, 'L');
		$pdf->Cell(6 / 100 * $width, $height, substr($bar->namagolongan, 0, 12), 1, 0, 'L');
		$pdf->Cell(5 / 100 * $width, $height, $bar->lokasitugas, 1, 0, 'C');
		$pdf->Cell(6 / 100 * $width, $height, $bar->subbagian, 1, 0, 'C');
		$pdf->Cell(11 / 100 * $width, $height, $bar->noktp, 1, 0, 'L');
		$pdf->Cell(7 / 100 * $width, $height, substr($bar->tipe, 0, 14), 1, 0, 'L');
		$pdf->Cell(7 / 100 * $width, $height, tglKaryawan($bar->tanggalmasuk), 1, 0, 'C');
		$pdf->Cell(6 / 100 * $width, $height, $bar->statuskaryawan, 1, 0, 'C');
		$pdf->Cell(7 / 100 * $width, $height, tglKaryawan($bar->tanggalkeluar), 1, 1, 'C');
	}
	$pdf->SetFont('Arial', 'B', 6);
	$pdf->Cell(100 / 100 * $width, $height, 'Total Karyawan: ' . $no, 1, 1, 'L');
	$pdf->Output();
	exit();
}

#excel
$stream = "<table border=0>";
$stream .= "<tr><td colspan=18><b>" . $hdpt['nama'] . "</b></td></tr>";
$stream .= "<tr><td colspan=18><b>DATA KARYAWAN</b></td></tr>";
$stream .= "<tr><td colspan=18>" . $ketfilter . "</td></tr>";
$stream .= "</table>";
$stream .= "<table border=1 cellspacing=1 cellpadding=3>";
$stream .= "<thead><tr bgcolor=#dcdcdc>";
$kolom = array('No.', 'NIK', 'Nama', 'Jabatan', 'Golongan', 'Lokasi Tugas', 'Divisi', 'PT', 'No. KTP', 'Pendidikan', 'Status Pajak', 'Status Perkawinan', 'Jumlah Anak', 'Tanggal Masuk', 'Tipe Karyawan', 'Status Karyawan', 'Tanggal Keluar');
foreach ($kolom as $k) {
	$stream .= "<td align=center><b>" . $k . "</b></td>";
}
$stream .= "</tr></thead><tbody>";
$txt = "style=\"mso-number-format:'\\@'\"";
$no = 0;
foreach ($data as $bar) {
	$no++;
	$stream .= "<tr>";
	$stream .= "<td align=center>" . $no . "</td>";
	$stream .= "<td " . $txt . ">" . $bar->nik . "</td>";
	$stream .= "<td>" . strtoupper($bar->namakaryawan) . "</td>";
	$stream .= "<td>" . $bar->namajabatan . "</td>";
	$stream .= "<td>" . $bar->namagolongan . "</td>";
	$stream .= "<td align=center>" . $bar->lokasitugas . "</td>";
	$stream .= "<td align=center>" . $bar->subbagian . "</td>";
	$stream .= "<td align=center>" . $bar->kodeorganisasi . "</td>";
	$stream .= "<td " . $txt . ">" . $bar->noktp . "</td>";
	$stream .= "<td>" . $optPendidikan[$bar->levelpendidikan] . "</td>";
	$stream .= "<td align=center>" . $bar->statuspajak . "</td>";
	$stream .= "<td align=center>" . $bar->statusperkawinan . "</td>";
	$stream .= "<td align=right>" . $bar->jumlahanak . "</td>";
	$stream .= "<td align=center>" . tglKaryawan($bar->tanggalmasuk) . "</td>";
	$stream .= "<td align=center>" . $bar->tipe . "</td>";
	$stream .= "<td align=center>" . $bar->statuskaryawan . "</td>";
	$stream .= "<td align=center>" . tglKaryawan($bar->tanggalkeluar) . "</td>";
	$stream .= "</tr>";
}
$stream .= "<tr><td colspan=17><b>Total Karyawan: " . $no . "</b></td></tr>";
$stream .= "</tbody></table>";
$stream .= "<table border=0><tr><td colspan=17><i>Print Time: " . date('H:i:s, d/m/Y') . " By: " . $_SESSION['empl']['name'] . "</i></td></tr></table>";

$namafile = "DATA_KARYAWAN_" . $unitcetak . "_" . date('Ymd') . ".xls";
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $namafile . '"');
header('Cache-Control: max-age=0');
echo $stream;
?>
