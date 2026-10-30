<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once('lib/fpdf.php');

$method = checkPostGet('method', '');
$tanggalmulai = tanggalsystemn(checkPostGet('tanggalmulai', ''));
$tanggalsampai = tanggalsystemn(checkPostGet('tanggalsampai', ''));
$divsch = checkPostGet('divsch', '');
$katsrc = checkPostGet('katsrc', '');
$tipe = checkPostGet('tipe', 'html');

#=== Pengamanan: format parameter sebelum dipakai di query (kosong boleh); teks undefined/null dari JS dianggap kosong ===
$polaCSR = array(
	'divsch' => '/^[A-Za-z0-9]{1,6}$/',
	'katsrc' => '/^[A-Za-z0-9\-\.]{1,24}$/',
	'tipe'   => '/^[a-z]{1,10}$/'
);
foreach ($polaCSR as $kunciCSR => $polaX) {
	$nilaiX = $$kunciCSR;
	if ($nilaiX === 'undefined' || $nilaiX === 'null') {
		$nilaiX = $$kunciCSR = '';
	}
	if ($nilaiX !== '' && (!is_string($nilaiX) || !preg_match($polaX, $nilaiX))) {
		exit("Warning : Parameter " . $kunciCSR . " tidak valid.");
	}
}
#tanggal (hasil konversi tanggalsystemn; kosong menjadi --) harus berformat YYYY-MM-DD
foreach (array('tanggalmulai', 'tanggalsampai') as $kunciCSR) {
	if ($$kunciCSR !== '' && $$kunciCSR !== '--' && (!is_string($$kunciCSR) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $$kunciCSR))) {
		exit("Warning : Format tanggal tidak valid.");
	}
}

$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmkat = makeOption($dbname, 'lgl_kategoribansos', 'kode,nama');

#filter daftar laporan; hanya unit yang boleh diakses user (pattern bnsWhereList)
function csrWhereList()
{
	global $divsch, $tanggalmulai, $tanggalsampai, $katsrc, $dbname;
	$where = " and a.kodeorg in (" . getOrgDetail(2) . ")";
	if ($divsch != '') {
		$where .= " and a.kodeorg='" . $divsch . "' ";
	}
	if ($tanggalmulai != '--' and $tanggalsampai == '--') {
		$where .= " and a.tanggal='" . $tanggalmulai . "' ";
	} elseif ($tanggalmulai == '--' and $tanggalsampai != '--') {
		$where .= " and a.tanggal='" . $tanggalsampai . "' ";
	} elseif ($tanggalmulai != '--' and $tanggalsampai != '--') {
		$where .= " and a.tanggal between '" . $tanggalmulai . "' and '" . $tanggalsampai . "'";
	}
	if ($katsrc != '') {
		$where .= " and a.kategori='" . $katsrc . "' ";
	}
	return $where;
}

#info filter aktif untuk kop laporan
function csrInfoFilter()
{
	global $nmorg, $nmkat, $divsch, $katsrc, $tanggalmulai, $tanggalsampai;
	$info = array();
	$info[] = 'Unit: ' . ($divsch != '' ? $divsch . (isset($nmorg[$divsch]) ? ' - ' . $nmorg[$divsch] : '') : 'Semua');
	$info[] = 'Kategori: ' . ($katsrc != '' && isset($nmkat[$katsrc]) ? $nmkat[$katsrc] : 'Semua');
	if ($tanggalmulai != '--' || $tanggalsampai != '--') {
		$info[] = 'Tanggal: ' . ($tanggalmulai != '--' ? tanggalnormal($tanggalmulai) : '...') . ' s/d ' . ($tanggalsampai != '--' ? tanggalnormal($tanggalsampai) : '...');
	}
	return implode(' | ', $info);
}

#seluruh detail kegiatan bansos (satu baris per kegiatan) sesuai filter untuk list/Excel/PDF
function csrDataList()
{
	global $dbname;
	return fetchdata("SELECT a.notransaksi, a.kodeorg, a.tanggal, a.tgldokumenstart, a.tgldokumenend, a.kategori, a.deskripsi, a.rupiah, a.posting, a.statuspersetujuan, a.updateby
		FROM " . $dbname . ".lgl_bansos a WHERE 1=1 " . csrWhereList() . " ORDER BY a.tanggal, a.notransaksi, a.deskripsi");
}

function csrFormatTglDok($tglDok, $tglFallback)
{
	if ($tglDok != '' && $tglDok != '0000-00-00' && $tglDok != '--') {
		return tanggalnormal($tglDok);
	}
	return ($tglFallback != '' && $tglFallback != '0000-00-00' && $tglFallback != '--') ? tanggalnormal($tglFallback) : '-';
}

#teks yang kepanjangan dipotong dan diberi "..." supaya tidak tumpang tindih ke kolom sebelah
function csrFit($pdf, $txt, $w, $f = 7)
{
	$txt = utf8_decode($txt);
	$pdf->SetFont('Arial', '', $f);
	if ($pdf->GetStringWidth($txt) <= $w - 2) {
		return $txt;
	}
	while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 2) {
		$txt = substr($txt, 0, -1);
	}
	return rtrim($txt) . '...';
}

#kolom nomor + nama kategori (kode kalau tidak terdaftar)
function csrNamaKategori($kode)
{
	global $nmkat;
	return isset($nmkat[$kode]) ? $nmkat[$kode] : $kode;
}

switch ($method) {
	case'loaddata':
		$rows = csrDataList();
		$no = 0;
		$total = 0;
		$tab = "";
		foreach ($rows as $r) {
			$no++;
			$a = $no % 2;
			$xx = '';
			if ($a == 1) {
				$xx .= " style=background-color:#F5EEF8 ";
			}
			$total += $r['rupiah'];
			$tab .= "<tr class=rowcontent " . $xx . ">";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=center>" . csrFormatTglDok($r['tgldokumenstart'], $r['tanggal']) . "</td>";
			$tab .= "<td align=center>" . csrFormatTglDok($r['tgldokumenend'], $r['tanggal']) . "</td>";
			$tab .= "<td align=left>" . htmlspecialchars($r['deskripsi']) . "</td>";
			$tab .= "<td align=right>" . hidezerodecimal($r['rupiah'], 2) . "</td>";
			$tab .= "<td align=center>" . tanggalnormal($r['tanggal']) . "</td>";
			$tab .= "<td align=center>" . htmlspecialchars($r['notransaksi']) . "</td>";
			$tab .= "<td>" . htmlspecialchars($r['kodeorg'] . ' - ' . (isset($nmorg[$r['kodeorg']]) ? $nmorg[$r['kodeorg']] : '')) . "</td>";
			$tab .= "<td align=left>" . htmlspecialchars(csrNamaKategori($r['kategori'])) . "</td>";
			$tab .= "</tr>";
		}
		if ($no == 0) {
			$tab .= "<tr class=rowcontent><td colspan=9 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			$tab .= "<tr class=rowheader>
					<td colspan=4 align=center>TOTAL</td>
					<td align=right>" . hidezerodecimal($total, 2) . "</td>
					<td colspan=4 align=center></td>
				</tr>";
		}
		echo $tab;
	break;

	case'excellist':
		$rows = csrDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$kolom = 9;
		$stream = "<table>
			<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
			<tr><td colspan=" . $kolom . "><b>LAPORAN CSR (BANTUAN SOSIAL)</b></td></tr>
			<tr><td colspan=" . $kolom . ">" . htmlspecialchars(csrInfoFilter()) . "</td></tr>
			<tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
			<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
			</table>";
		$stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
			<thead><tr>
				<th align=center bgcolor=#CCCCCC>No</th>
				<th align=center bgcolor=#CCCCCC>Tanggal Dokumen Dikeluarkan</th>
				<th align=center bgcolor=#CCCCCC>Tanggal Dokumen Berakhir</th>
				<th align=center bgcolor=#CCCCCC>Bentuk Kegiatan</th>
				<th align=center bgcolor=#CCCCCC>Nilai (Rp)</th>
				<th align=center bgcolor=#CCCCCC>Waktu Pelaksanaan</th>
				<th align=center bgcolor=#CCCCCC>No Transaksi</th>
				<th align=center bgcolor=#CCCCCC>Unit</th>
				<th align=center bgcolor=#CCCCCC>Kategori</th>
			</tr></thead><tbody>";
		$no = 0;
		$total = 0;
		foreach ($rows as $r) {
			$no++;
			$total += $r['rupiah'];
			$stream .= "<tr>
				<td align=center>" . $no . "</td>
				<td align=center>" . csrFormatTglDok($r['tgldokumenstart'], $r['tanggal']) . "</td>
				<td align=center>" . csrFormatTglDok($r['tgldokumenend'], $r['tanggal']) . "</td>
				<td>" . htmlspecialchars($r['deskripsi']) . "</td>
				<td align=right>" . round($r['rupiah'], 2) . "</td>
				<td align=center>" . tanggalnormal($r['tanggal']) . "</td>
				<td>" . htmlspecialchars($r['notransaksi']) . "</td>
				<td>" . htmlspecialchars($r['kodeorg'] . ' - ' . (isset($nmorg[$r['kodeorg']]) ? $nmorg[$r['kodeorg']] : '')) . "</td>
				<td>" . htmlspecialchars(csrNamaKategori($r['kategori'])) . "</td>
			</tr>";
		}
		if ($no == 0) {
			$stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
		} else {
			$stream .= "<tr><td colspan=3 align=center><b>TOTAL</b></td><td align=right><b>" . round($total, 2) . "</b></td><td colspan=5></td></tr>";
		}
		$stream .= "</tbody></table>";
		$xls = new HtmlExcel();
		$xls->addSheet("Laporan CSR", $stream);
		$xls->headers("Laporan_CSR_" . date('YmdHis') . ".xls");
		echo $xls->buildFile();
		exit;

	case'pdflist':
		$rows = csrDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$infoFilter = csrInfoFilter();
		if (!class_exists('PDFListCSR')) {
			class PDFListCSR extends FPDF
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
					$this->Cell(160, 6, utf8_decode($hdpt['nama']), 0, 1, 'L');
					$this->SetY($this->tMargin + 15);
					$this->SetFont('Arial', 'B', 13);
					$this->Cell($width, 7, 'LAPORAN CSR (BANTUAN SOSIAL)', 0, 1, 'C');
					$this->SetFont('Arial', '', 8);
					$this->Cell($width, 5, utf8_decode($infoFilter), 0, 1, 'C');
					$this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
					$this->Ln(1);
					$this->SetFillColor(220, 220, 220);
					$this->SetFont('Arial', 'B', 8);
					$judul = array('No', 'Tgl Dikeluarkan', 'Tgl Berakhir', 'Bentuk Kegiatan', 'Nilai (Rp)', 'Waktu Pelaksanaan', 'No Transaksi', 'Unit', 'Kategori');
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
		}
		$pdf = new PDFListCSR('L', 'mm', 'A4');
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(false, 16);
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$colFixed = array(8, 24, 24, 68, 28, 26, 38, 20); #semua kolom kecuali Kategori
		$pdf->colW = array_merge($colFixed, array($width - array_sum($colFixed)));
		$pdf->AddPage();
		$h = 6;
		$no = 0;
		$total = 0;
		foreach ($rows as $r) {
			$no++;
			$total += $r['rupiah'];
			if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
				$pdf->AddPage();
			}
			$c = $pdf->colW;
			$pdf->SetFont('Arial', '', 7);
			$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
			$pdf->Cell($c[1], $h, csrFormatTglDok($r['tgldokumenstart'], $r['tanggal']), 1, 0, 'C');
			$pdf->Cell($c[2], $h, csrFormatTglDok($r['tgldokumenend'], $r['tanggal']), 1, 0, 'C');
			$pdf->Cell($c[3], $h, csrFit($pdf, $r['deskripsi'], $c[3]), 1, 0, 'L');
			$pdf->Cell($c[4], $h, number_format($r['rupiah'], 2), 1, 0, 'R');
			$pdf->Cell($c[5], $h, tanggalnormal($r['tanggal']), 1, 0, 'C');
			$pdf->Cell($c[6], $h, csrFit($pdf, $r['notransaksi'], $c[6]), 1, 0, 'C');
			$pdf->Cell($c[7], $h, csrFit($pdf, $r['kodeorg'], $c[7]), 1, 0, 'C');
			$pdf->Cell($c[8], $h, csrFit($pdf, csrNamaKategori($r['kategori']), $c[8]), 1, 1, 'L');
		}
		if ($no == 0) {
			$pdf->Cell(array_sum($pdf->colW), $h, 'Data tidak ditemukan', 1, 1, 'C');
		} else {
			if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
				$pdf->AddPage();
			}
			$pdf->SetFont('Arial', 'B', 7);
			$pdf->Cell($pdf->colW[0] + $pdf->colW[1] + $pdf->colW[2] + $pdf->colW[3], $h, 'TOTAL', 1, 0, 'C');
			$pdf->Cell($pdf->colW[4], $h, number_format($total, 2), 1, 0, 'R');
			$pdf->Cell($pdf->colW[5] + $pdf->colW[6] + $pdf->colW[7] + $pdf->colW[8], $h, '', 1, 1, 'C');
		}
		$pdf->Output();
		exit;
}
?>
