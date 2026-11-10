<?php
/**
 * Slave PPh 21 Gaji Unit  (SDM > Laporan > Penggajian (Unit) > Print Slip Gaji > PPh 21 Gaji Unit)
 *
 * proses=preview  tipe=previewawal | excel | pdf
 * Isi laporan per karyawan pada periode terpilih (semua dari data gaji yang sudah tersimpan):
 *   - Gaji / Upah        : komponen tunjangan tetap (parameter KOMTJTETAP) yang ikut perhitungan PPh 21
 *   - Premi/Lembur/Dll   : komponen pendapatan lain yang ikut perhitungan PPh 21
 *   - Jumlah bruto       : Gaji/Upah + Premi/Lembur/Dll
 *   - PPh 21             : potongan PPh 21 (komponen 42) pada periode tersebut
 * Unit: satu unit atau "ALL" (seluruh unit yang boleh diakses user). Karyawan Staff tidak termasuk (sama seperti perhitungan PPh 21).
 */

ini_set('display_errors', 0);
error_reporting(0);

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/HtmlExcel.php');

$proses  = checkPostGet('proses', '');
$unit    = addslashes(trim(checkPostGet('unit', '')));
$per     = addslashes(trim(checkPostGet('per', '')));
$tipekar = addslashes(trim(checkPostGet('tipekar', '')));
$tipe    = checkPostGet('tipe', '');

const AMP_BATAS_BARIS = 3000;

switch ($proses) {
	case 'preview':
		ampCekInput($unit, $per, $tipekar);
		ampLaporan($unit, $per, $tipekar, $tipe);
		break;

	default:
		break;
}


#=== Fungsi bantu ===

/** Jalankan query; jika gagal tampilkan pesan umum (detail ke log, tidak ke layar) */
function ampAmbilSemua($sql)
{
	global $owlPDO;
	try {
		$res = $owlPDO->query($sql);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		return $res->fetchAll();
	} catch (PDOException $e) {
		error_log('sdm_slave_2amprahgaji: ' . $e->getMessage());
		exit('Error, terjadi kesalahan pada database.');
	}
}

/** Daftar tipe karyawan yang boleh dipilih (id => nama), tanpa Staff (id 0) */
function ampTipeKaryawan()
{
	global $dbname;
	$hasil = array();
	foreach (ampAmbilSemua("select id,tipe from " . $dbname . ".sdm_5tipekaryawan where aktif='1' and id<>'0' order by id") as $bar) {
		$hasil[$bar['id']] = $bar['tipe'];
	}
	return $hasil;
}

/** Validasi input: unit (atau ALL) harus unit yang boleh diakses, periode yyyy-mm, tipe karyawan dari daftar */
function ampCekInput($unit, $per, $tipekar)
{
	if ($unit == '') {
		exit('Error, Unit belum dipilih.');
	}
	if ($unit != 'ALL') {
		$unitBoleh = getOrgDetail(1);
		if (!isset($unitBoleh[$unit])) {
			exit('Error, Unit ' . htmlspecialchars($unit) . ' tidak boleh diakses.');
		}
	}
	if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $per)) {
		exit('Error, Periode belum dipilih atau tidak valid.');
	}
	if ($tipekar != '') {
		$tipeBoleh = ampTipeKaryawan();
		if (!isset($tipeBoleh[$tipekar])) {
			exit('Error, Tipe karyawan tidak valid.');
		}
	}
}

/** Kode unit yang dilaporkan */
function ampDaftarUnit($unit)
{
	if ($unit == 'ALL') {
		return array_keys(getOrgDetail(1));
	}
	return array($unit);
}

/** Id komponen tunjangan tetap dari parameter KOMTJTETAP (hanya angka, aman dipakai di SQL) */
function ampKomponenTunjanganTetap()
{
	global $dbname;
	$res = ampAmbilSemua("select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='KOMTJTETAP'");
	$id = array();
	if (count($res) > 0) {
		foreach (explode(',', str_replace("'", '', $res[0]['nilai'])) as $nilai) {
			$nilai = trim($nilai);
			if (preg_match('/^\d+$/', $nilai)) {
				$id[] = $nilai;
			}
		}
	}
	return count($id) > 0 ? "'" . implode("','", $id) . "'" : "''";
}

/** Data laporan: array baris + jumlah karyawan sebelum dibatasi */
function ampAmbilData($units, $per, $tipekar)
{
	global $dbname;

	$daftarUnit = "'" . implode("','", $units) . "'";
	$tjTetap = ampKomponenTunjanganTetap();

	## Unit yang punya data histori bulanan memakai histori, selain itu memakai data karyawan saat ini
	$unitHist = array();
	foreach (ampAmbilSemua("select distinct lokasitugas from " . $dbname . ".datakaryawan_hist where approval_status='8' and version_type='B' and periodegaji='" . $per . "' and statuspajak <> '' and tipekaryawan != 0 and lokasitugas in (" . $daftarUnit . ")") as $bar) {
		$unitHist[] = $bar['lokasitugas'];
	}
	$unitSkrg = array_values(array_diff($units, $unitHist));

	$filterTipe = ($tipekar != '') ? " and tipekaryawan='" . $tipekar . "'" : " and tipekaryawan != 0";
	$syarat = " and statuspajak <> '' and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and (tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null)" . $filterTipe;
	$kolom = "karyawanid,nik,namakaryawan,statuspajak,kodejabatan,lokasitugas";

	$karyawan = array();
	if (count($unitHist) > 0) {
		foreach (ampAmbilSemua("select " . $kolom . " from " . $dbname . ".datakaryawan_hist where approval_status='8' and version_type='B' and periodegaji='" . $per . "' and lokasitugas in ('" . implode("','", $unitHist) . "')" . $syarat) as $bar) {
			$karyawan[$bar['karyawanid']] = $bar;
		}
	}
	if (count($unitSkrg) > 0) {
		foreach (ampAmbilSemua("select " . $kolom . " from " . $dbname . ".datakaryawan where lokasitugas in ('" . implode("','", $unitSkrg) . "')" . $syarat) as $bar) {
			$karyawan[$bar['karyawanid']] = $bar;
		}
	}

	## Gaji/upah (tunjangan tetap), premi/lembur/dll, dan PPh 21 per karyawan
	$gajiUpah = array();
	$premi = array();
	foreach (ampAmbilSemua("select a.karyawanid, (a.idkomponen in (" . $tjTetap . ")) as tetap, sum(a.jumlah) as jml from " . $dbname . ".sdm_gaji a join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id
		where a.periodegaji='" . $per . "' and a.kodeorg in (" . $daftarUnit . ") and b.plus=1 and b.pph21='1' and a.jumlah > 0 group by a.karyawanid, tetap") as $bar) {
		if ($bar['tetap'] == 1) {
			$gajiUpah[$bar['karyawanid']] = $bar['jml'];
		} else {
			$premi[$bar['karyawanid']] = $bar['jml'];
		}
	}
	$pph = array();
	foreach (ampAmbilSemua("select karyawanid, sum(jumlah) as jml from " . $dbname . ".sdm_gaji
		where idkomponen='42' and periodegaji='" . $per . "' and kodeorg in (" . $daftarUnit . ") and jumlah > 0 group by karyawanid") as $bar) {
		$pph[$bar['karyawanid']] = $bar['jml'];
	}

	$nmJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');

	## Hanya karyawan yang punya gaji / PPh pada periode ini
	$baris = array();
	foreach ($karyawan as $id => $k) {
		$g = isset($gajiUpah[$id]) ? $gajiUpah[$id] : 0;
		$p = isset($premi[$id]) ? $premi[$id] : 0;
		$x = isset($pph[$id]) ? $pph[$id] : 0;
		if ($g <= 0 && $p <= 0 && $x <= 0) {
			continue;
		}
		$baris[] = array(
			'unit' => $k['lokasitugas'],
			'nik' => $k['nik'],
			'nama' => trim($k['namakaryawan']),
			'status' => $k['statuspajak'],
			'jabatan' => isset($nmJabatan[$k['kodejabatan']]) ? $nmJabatan[$k['kodejabatan']] : '',
			'gaji' => $g,
			'premi' => $p,
			'bruto' => $g + $p,
			'pph' => $x,
		);
	}
	usort($baris, function ($a, $b) {
		$c = strcmp($a['unit'], $b['unit']);
		return $c != 0 ? $c : strcasecmp($a['nama'], $b['nama']);
	});

	$jumlahSemua = count($baris);
	return array('baris' => array_slice($baris, 0, AMP_BATAS_BARIS), 'jumlah' => $jumlahSemua);
}

/** Keterangan filter yang dipakai (untuk layar, Excel, dan PDF) */
function ampInfoFilter($unit, $per, $tipekar, $jumlah)
{
	$tipeBoleh = ampTipeKaryawan();
	$info = 'Unit: ' . ($unit == 'ALL' ? 'SEMUA UNIT' : $unit . ' - ' . getNamaOrg($unit));
	$info .= ' | Periode: ' . $per;
	$info .= ' | Tipe karyawan: ' . ($tipekar != '' ? $tipeBoleh[$tipekar] : 'SEMUA (SELAIN STAFF)');
	$info .= ' | Jumlah karyawan: ' . number_format($jumlah);
	if ($jumlah > AMP_BATAS_BARIS) {
		$info .= ' (ditampilkan ' . number_format(AMP_BATAS_BARIS) . ' pertama, persempit filter untuk melihat sisanya)';
	}
	return $info;
}


#=== Laporan ===
function ampLaporan($unit, $per, $tipekar, $tipe)
{
	$units = ampDaftarUnit($unit);
	$data = ampAmbilData($units, $per, $tipekar);
	$baris = $data['baris'];
	$info = ampInfoFilter($unit, $per, $tipekar, $data['jumlah']);
	$tampilUnit = count($units) > 1;

	$total = array('gaji' => 0, 'premi' => 0, 'bruto' => 0, 'pph' => 0);
	foreach ($baris as $b) {
		foreach ($total as $kunci => $nilai) {
			$total[$kunci] += $b[$kunci];
		}
	}

	if ($tipe == 'pdf') {
		ampKeluarPdf($baris, $total, $unit, $per, $info, $tampilUnit);
		return;
	}

	$adaExcel = ($tipe == 'excel');
	$judulKolom = array('NO');
	if ($tampilUnit) {
		$judulKolom[] = 'UNIT';
	}
	$judulKolom = array_merge($judulKolom, array('NIK', 'NAMA KARYAWAN', 'STATUS PAJAK', 'JABATAN', 'GAJI / UPAH', 'PREMI / LEMBUR / DLL', 'JUMLAH BRUTO', 'PPH 21'));
	$kolom = count($judulKolom);

	$html = '';
	if ($adaExcel) {
		$html .= "<table class=sortable cellspacing=1 border=1>";
	} else {
		$html .= "<div style='margin:6px 2px;font-size:12px'>" . htmlspecialchars($info) . "</div>";
		$html .= "<div class='table-scroll' style='max-height:75vh;overflow-y:auto'><table class=sortable cellspacing=1 cellpadding=5 style='width:100%;'>";
	}

	$html .= "<thead><tr class=rowcontent>";
	foreach ($judulKolom as $judul) {
		$html .= "<th align=center>" . $judul . "</th>";
	}
	$html .= "</tr></thead>";

	$no = 0;
	foreach ($baris as $b) {
		$no++;
		$html .= "<tr class=rowcontent>";
		$html .= "<td align=center>" . $no . "</td>";
		if ($tampilUnit) {
			$html .= "<td align=center>" . htmlspecialchars($b['unit']) . "</td>";
		}
		$html .= "<td style='mso-number-format:\"\\@\"'>" . htmlspecialchars($b['nik']) . "</td>";
		$html .= "<td>" . htmlspecialchars($b['nama']) . "</td>";
		$html .= "<td align=center>" . htmlspecialchars($b['status']) . "</td>";
		$html .= "<td>" . htmlspecialchars($b['jabatan']) . "</td>";
		$html .= "<td align=right>" . number_format($b['gaji'], 0) . "</td>";
		$html .= "<td align=right>" . number_format($b['premi'], 0) . "</td>";
		$html .= "<td align=right>" . number_format($b['bruto'], 0) . "</td>";
		$html .= "<td align=right>" . number_format($b['pph'], 0) . "</td>";
		$html .= "</tr>";
	}
	if ($no == 0) {
		$html .= "<tr class=rowcontent><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
	} else {
		$html .= "<tr class=rowcontent><td align=center colspan=" . ($kolom - 4) . "><b>TOTAL</b></td>"
			. "<td align=right><b>" . number_format($total['gaji'], 0) . "</b></td>"
			. "<td align=right><b>" . number_format($total['premi'], 0) . "</b></td>"
			. "<td align=right><b>" . number_format($total['bruto'], 0) . "</b></td>"
			. "<td align=right><b>" . number_format($total['pph'], 0) . "</b></td></tr>";
	}
	$html .= $adaExcel ? "</table>" : "</table></div>";

	if ($adaExcel) {
		ampKeluarExcel($html, $unit, $per, $info, $kolom);
		return;
	}
	echo $html . "####" . $no;
}


/** Kop laporan: nama PT dari induk unit (untuk semua unit: PT milik user) */
function ampKop($unit, $per, $info)
{
	global $dbname;

	if ($unit == 'ALL') {
		$hdpt = setheadreport($_SESSION['empl']['lokasitugas'], $_SESSION['empl']['kodeorganisasi']);
	} else {
		$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $unit . "'");
		$pt = isset($induk[$unit]) ? $induk[$unit] : $_SESSION['empl']['kodeorganisasi'];
		$hdpt = setheadreport($unit, $pt);
	}

	return array(
		'nama' => $hdpt['nama'],
		'logo' => isset($hdpt['logo']) ? $hdpt['logo'] : '',
		'judul' => 'PPH 21 GAJI UNIT - PERIODE ' . $per,
		'info' => $info,
		'tarik' => 'Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s'),
	);
}


#=== Excel ===
function ampKeluarExcel($html, $unit, $per, $info, $kolom)
{
	$kop = ampKop($unit, $per, $info);
	$kopHtml = "<table>
		<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($kop['nama']) . "</b></td></tr>
		<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($kop['judul']) . "</b></td></tr>
		<tr><td colspan=" . $kolom . ">" . htmlspecialchars($kop['info']) . "</td></tr>
		<tr><td colspan=" . $kolom . ">" . htmlspecialchars($kop['tarik']) . "</td></tr>
		<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
		</table>";

	$xls = new HtmlExcel();
	$xls->addSheet("PPh21GajiUnit", $kopHtml . $html);
	$xls->headers("PPh21GajiUnit_" . ($unit == 'ALL' ? 'SEMUAUNIT' : $unit) . "_" . $per . "_" . date('YmdHis') . ".xls");
	echo $xls->buildFile();
	exit;
}


/** Potong teks agar muat di sel PDF */
function ampFit($pdf, $teks, $lebar, $ukuran = 7)
{
	$teks = utf8_decode($teks);
	$pdf->SetFont('Arial', '', $ukuran);
	if ($pdf->GetStringWidth($teks) <= $lebar - 2) {
		return $teks;
	}
	while (strlen($teks) > 1 && $pdf->GetStringWidth($teks . '...') > $lebar - 2) {
		$teks = substr($teks, 0, -1);
	}
	return $teks . '...';
}


#=== PDF (landscape A4) ===
function ampKeluarPdf($baris, $total, $unit, $per, $info, $tampilUnit)
{
	$kop = ampKop($unit, $per, $info);

	if (!class_exists('PDFPph21GajiUnit')) {
		class PDFPph21GajiUnit extends FPDF
		{
			public $kop = array();
			public $judulKolom = array();
			public $lebar = array();

			function Header()
			{
				$width = $this->w - $this->lMargin - $this->rMargin;
				if ($this->kop['logo'] != '' && file_exists($this->kop['logo'])) {
					$this->Image($this->kop['logo'], $this->lMargin, $this->tMargin, 18);
				}
				$this->SetFont('Arial', 'B', 11);
				$this->SetXY($this->lMargin + 22, $this->tMargin + 2);
				$this->Cell(160, 6, utf8_decode($this->kop['nama']), 0, 1, 'L');
				$this->SetY($this->tMargin + 15);
				$this->SetFont('Arial', 'B', 13);
				$this->Cell($width, 7, utf8_decode($this->kop['judul']), 0, 1, 'C');
				$this->SetFont('Arial', '', 8);
				$this->Cell($width, 5, utf8_decode($this->kop['info']), 0, 1, 'C');
				$this->Cell($width, 5, utf8_decode($this->kop['tarik']), 0, 1, 'C');
				$this->Ln(1);
				$this->SetFillColor(220, 220, 220);
				$this->SetFont('Arial', 'B', 7);
				foreach ($this->judulKolom as $i => $judul) {
					$this->Cell($this->lebar[$i], 8, $judul, 1, 0, 'C', true);
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

	# kolom: No, [Unit], NIK, Nama, Status, Jabatan, Gaji/Upah, Premi/Lembur/Dll, Jumlah Bruto, PPh 21
	if ($tampilUnit) {
		$judulKolom = array('No', 'Unit', 'NIK', 'Nama Karyawan', 'Status', 'Jabatan', 'Gaji / Upah', 'Premi/Lembur/Dll', 'Jumlah Bruto', 'PPh 21');
		$lebar = array(9, 14, 26, 56, 14, 40, 30, 30, 30, 28);
		$kunci = array(null, 'unit', 'nik', 'nama', 'status', 'jabatan', 'gaji', 'premi', 'bruto', 'pph');
	} else {
		$judulKolom = array('No', 'NIK', 'Nama Karyawan', 'Status', 'Jabatan', 'Gaji / Upah', 'Premi/Lembur/Dll', 'Jumlah Bruto', 'PPh 21');
		$lebar = array(9, 28, 62, 16, 46, 32, 32, 32, 20);
		$kunci = array(null, 'nik', 'nama', 'status', 'jabatan', 'gaji', 'premi', 'bruto', 'pph');
	}
	$angka = array('gaji', 'premi', 'bruto', 'pph');

	$pdf = new PDFPph21GajiUnit('L', 'mm', 'A4');
	$pdf->kop = $kop;
	$pdf->judulKolom = $judulKolom;
	$pdf->lebar = $lebar;
	$pdf->AliasNbPages();
	$pdf->SetAutoPageBreak(false, 16);
	$pdf->AddPage();

	$tinggi = 6;
	$no = 0;
	foreach ($baris as $b) {
		$no++;
		if ($pdf->GetY() + $tinggi > $pdf->h - $pdf->bMargin) {
			$pdf->AddPage();
		}
		$pdf->SetFont('Arial', '', 7);
		foreach ($kunci as $i => $k) {
			if ($k === null) {
				$pdf->Cell($lebar[$i], $tinggi, $no, 1, 0, 'C');
			} elseif (in_array($k, $angka)) {
				$pdf->Cell($lebar[$i], $tinggi, number_format($b[$k], 0), 1, 0, 'R');
			} else {
				$pdf->Cell($lebar[$i], $tinggi, ampFit($pdf, (string)$b[$k], $lebar[$i]), 1, 0, ($k == 'nama' || $k == 'jabatan') ? 'L' : 'C');
			}
		}
		$pdf->Ln();
	}

	if ($no == 0) {
		$pdf->Cell(array_sum($lebar), $tinggi, 'Data tidak ditemukan', 1, 1, 'C');
	} else {
		if ($pdf->GetY() + $tinggi > $pdf->h - $pdf->bMargin) {
			$pdf->AddPage();
		}
		$n = count($lebar);
		$lebarLabel = array_sum($lebar) - $lebar[$n - 4] - $lebar[$n - 3] - $lebar[$n - 2] - $lebar[$n - 1];
		$pdf->SetFont('Arial', 'B', 7);
		$pdf->Cell($lebarLabel, $tinggi, 'TOTAL', 1, 0, 'C');
		$pdf->Cell($lebar[$n - 4], $tinggi, number_format($total['gaji'], 0), 1, 0, 'R');
		$pdf->Cell($lebar[$n - 3], $tinggi, number_format($total['premi'], 0), 1, 0, 'R');
		$pdf->Cell($lebar[$n - 2], $tinggi, number_format($total['bruto'], 0), 1, 0, 'R');
		$pdf->Cell($lebar[$n - 1], $tinggi, number_format($total['pph'], 0), 1, 1, 'R');
	}

	$pdf->Ln(2);
	$pdf->SetFont('Arial', 'I', 7);
	$pdf->MultiCell(0, 4, utf8_decode('Gaji/Upah = tunjangan tetap | Premi/Lembur/Dll = pendapatan lain yang ikut perhitungan PPh 21 | Jumlah Bruto = Gaji/Upah + Premi/Lembur/Dll | PPh 21 = potongan PPh 21 pada periode ini'));
	$pdf->Output();
	exit;
}
