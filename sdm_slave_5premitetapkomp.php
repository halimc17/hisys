<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

#Setup Komponen Tetap (khusus PT PPP), semua unit PPP. Kunci baris: organisasi (unit) + komponen + karyawan.
$method = checkPostGet('method', '');
$unitPilih = trim((string)checkPostGet('unit', ''));
$idkomponen = trim((string)checkPostGet('idkomponen', ''));
$karyawanid = trim((string)checkPostGet('karyawanid', ''));
$nominal = str_replace(',', '', (string)checkPostGet('nominal', ''));
$aktif = checkPostGet('aktif', '1');
$unit = $_SESSION['empl']['lokasitugas'];

if (!in_array($method, array('loadData', 'loadDetail', 'preview', 'saveBanyak', 'delete', 'excel', 'pdf'), true)) {
	exit("Warning : Method tidak dikenal.");
}
if (!preg_match('/^[A-Za-z0-9]{1,4}$/', (string)$unit) || getindukPT($unit) != 'PPP') {
	exit("Warning : Menu ini khusus PT PPP.");
}

#semua unit (organisasi) milik PT PPP
function daftarUnitPPP()
{
	global $dbname, $owlPDO;
	$stm = $owlPDO->prepare("select kodeorganisasi from " . $dbname . ".organisasi where induk='PPP'");
	$stm->execute();
	return $stm->fetchAll(PDO::FETCH_COLUMN);
}

#organisasi harus milik PPP
function cekUnit($u)
{
	if ($u === '' || !preg_match('/^[A-Za-z0-9]{1,4}$/', $u) || !in_array($u, daftarUnitPPP(), true)) {
		exit("Warning : Organisasi tidak valid atau bukan unit PT PPP.");
	}
}

#komponen harus ada, bukan basic (Setup Upah), dan bukan komponen sistem (lock); mengembalikan baris komponen
function cekKomponen($idk)
{
	global $dbname, $owlPDO;
	if (!preg_match('/^[0-9]{1,10}$/', $idk)) {
		exit("Warning : Komponen wajib dipilih.");
	}
	$stm = $owlPDO->prepare("select id,name,plus,type,`lock` from " . $dbname . ".sdm_ho_component where id=?");
	$stm->execute(array($idk));
	$komp = $stm->fetch(PDO::FETCH_ASSOC);
	if (!$komp) {
		exit("Warning : Komponen tidak ditemukan.");
	}
	if ($komp['type'] == 'basic') {
		exit("Warning : Komponen ini diatur di Setup Upah, tidak bisa disetup di sini.");
	}
	if ($komp['lock'] == 1) {
		exit("Warning : Komponen ini dikelola sistem dan tidak bisa disetup di sini.");
	}
	return $komp;
}

#karyawan harus bertugas di organisasi yang dipilih
function cekKaryawan($kid, $u)
{
	global $dbname, $owlPDO;
	if (!preg_match('/^[0-9]{1,10}$/', $kid)) {
		exit("Warning : Karyawan wajib dipilih.");
	}
	$stm = $owlPDO->prepare("select karyawanid from " . $dbname . ".datakaryawan where karyawanid=? and lokasitugas=?");
	$stm->execute(array($kid, $u));
	if (!$stm->fetchColumn()) {
		exit("Warning : Karyawan tidak ditemukan di organisasi " . htmlspecialchars($u) . ".");
	}
}

#filter List Data (organisasi, komponen, status, karyawan), dipakai bersama oleh list, Excel, dan PDF; selalu dibatasi unit PT PPP
function filterKT()
{
	global $unitPilih, $idkomponen;
	$daftarUnit = daftarUnitPPP();
	if (count($daftarUnit) == 0) {
		exit("Warning : Unit PT PPP tidak ditemukan.");
	}
	$where = " s.kodeorg in (" . implode(',', array_fill(0, count($daftarUnit), '?')) . ")";
	$par = $daftarUnit;
	if ($unitPilih != '') {
		cekUnit($unitPilih);
		$where .= " and s.kodeorg=?";
		$par[] = $unitPilih;
	}
	if ($idkomponen != '') {
		if (!preg_match('/^[0-9]{1,10}$/', $idkomponen)) {
			exit("Warning : Komponen tidak valid.");
		}
		$where .= " and s.idkomponen=?";
		$par[] = $idkomponen;
	}
	$aktifCari = trim((string)checkPostGet('aktifcari', ''));
	if ($aktifCari != '') {
		if (!in_array($aktifCari, array('0', '1'), true)) {
			exit("Warning : Status tidak valid.");
		}
		$where .= " and s.aktif=?";
		$par[] = (int)$aktifCari;
	}
	$cari = trim((string)checkPostGet('cari', ''));
	if ($cari != '') {
		$like = '%' . str_replace(array('%', '_'), array('\\%', '\\_'), $cari) . '%';
		$where .= " and (k.namakaryawan like ? or k.nik like ?)";
		$par[] = $like;
		$par[] = $like;
	}
	return array($where, $par);
}

#keterangan filter aktif untuk kop Excel/PDF
function infoFilterKT()
{
	global $unitPilih, $idkomponen, $dbname;
	$nmUnit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	$nmKomp = makeOption($dbname, 'sdm_ho_component', 'id,name');
	$aktifCari = trim((string)checkPostGet('aktifcari', ''));
	$cari = trim((string)checkPostGet('cari', ''));
	return 'Organisasi: ' . ($unitPilih == '' ? 'Semua' : $unitPilih . ' - ' . (string)@$nmUnit[$unitPilih]) .
		' | Komponen: ' . ($idkomponen == '' ? 'Semua' : trim((string)@$nmKomp[$idkomponen])) .
		' | Status: ' . ($aktifCari === '1' ? 'Aktif' : ($aktifCari === '0' ? 'Tidak Aktif' : 'Semua')) .
		' | Karyawan: ' . ($cari == '' ? 'Semua' : $cari);
}

#baris detail karyawan menurut filter, maksimal 3000 baris (sisanya dicatat di kop)
function dataTarikKT(&$terpotong)
{
	global $dbname, $owlPDO;
	list($where, $par) = filterKT();
	try {
		$stm = $owlPDO->prepare("select s.kodeorg, s.idkomponen, s.nominal, s.aktif, s.updatetime, k.nik, k.namakaryawan, k.kodejabatan
			from " . $dbname . ".sdm_5premitetapkomp s left join " . $dbname . ".datakaryawan k on k.karyawanid=s.karyawanid
			where" . $where . " order by s.kodeorg, s.idkomponen, k.namakaryawan limit 3001");
		$stm->execute($par);
		$res = $stm->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		exit("Gagal : Tabel setup belum ada. Jalankan sql_ppp_premi_genset.sql terlebih dahulu.");
	}
	$terpotong = count($res) > 3000;
	return array_slice($res, 0, 3000);
}

switch ($method) {
	case 'excel':
	case 'pdf':
		$terpotong = false;
		$rows = dataTarikKT($terpotong);
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$nmKomponen = makeOption($dbname, 'sdm_ho_component', 'id,name');
		$plusKomponen = makeOption($dbname, 'sdm_ho_component', 'id,plus');
		$nmUnit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$nmJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
		$info = infoFilterKT() . ($terpotong ? ' | Catatan: ditampilkan 3000 baris pertama, persempit filter untuk data selengkapnya' : '');
		$ditarik = 'Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s');
		$sub = array('P' => array(0, 0, 0), 'K' => array(0, 0, 0)); #baris, aktif, nominal aktif
		foreach ($rows as $r) {
			$kj = ((string)@$plusKomponen[$r['idkomponen']] === '0') ? 'K' : 'P';
			$sub[$kj][0]++;
			if ($r['aktif'] == 1) {
				$sub[$kj][1]++;
				$sub[$kj][2] += $r['nominal'];
			}
		}
		$totalBaris = array('P' => 'Total Pendapatan', 'K' => 'Total Potongan');

		if ($method == 'excel') {
			include_once('lib/HtmlExcel.php');
			$kolom = 10;
			$stream = "<table>
				<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
				<tr><td colspan=" . $kolom . "><b>SETUP KOMPONEN TETAP</b></td></tr>
				<tr><td colspan=" . $kolom . ">" . htmlspecialchars($info) . "</td></tr>
				<tr><td colspan=" . $kolom . ">" . htmlspecialchars($ditarik) . "</td></tr>
				<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
				</table>";
			$stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
				<thead><tr>
					<th align=center bgcolor=#CCCCCC>No</th>
					<th align=center bgcolor=#CCCCCC>Organisasi</th>
					<th align=center bgcolor=#CCCCCC>Komponen</th>
					<th align=center bgcolor=#CCCCCC>Jenis</th>
					<th align=center bgcolor=#CCCCCC>NIK</th>
					<th align=center bgcolor=#CCCCCC>Nama Karyawan</th>
					<th align=center bgcolor=#CCCCCC>Jabatan</th>
					<th align=center bgcolor=#CCCCCC>Nominal / Bulan</th>
					<th align=center bgcolor=#CCCCCC>Aktif</th>
					<th align=center bgcolor=#CCCCCC>Waktu Diubah</th>
				</tr></thead><tbody>";
			$no = 0;
			foreach ($rows as $r) {
				$no++;
				$potongan = ((string)@$plusKomponen[$r['idkomponen']] === '0');
				$stream .= "<tr>
					<td align=center>" . $no . "</td>
					<td>" . htmlspecialchars($r['kodeorg'] . ' - ' . (string)@$nmUnit[$r['kodeorg']]) . "</td>
					<td>" . htmlspecialchars(trim((string)@$nmKomponen[$r['idkomponen']])) . " (" . (int)$r['idkomponen'] . ")</td>
					<td align=center>" . ($potongan ? 'Potongan' : 'Pendapatan') . "</td>
					<td align=left style='mso-number-format:\"\\@\"'>" . htmlspecialchars((string)$r['nik']) . "</td>
					<td>" . htmlspecialchars((string)$r['namakaryawan']) . "</td>
					<td>" . htmlspecialchars((string)@$nmJabatan[$r['kodejabatan']]) . "</td>
					<td align=right>" . (float)$r['nominal'] . "</td>
					<td align=center>" . ($r['aktif'] == 1 ? 'Ya' : 'Tidak') . "</td>
					<td align=center>" . htmlspecialchars((string)$r['updatetime']) . "</td>
				</tr>";
			}
			if ($no == 0) {
				$stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
			} else {
				foreach ($totalBaris as $kj => $label) {
					if ($sub[$kj][0] == 0) {
						continue;
					}
					$stream .= "<tr><td colspan=7 align=right><b>" . $label . " (" . $sub[$kj][1] . " dari " . $sub[$kj][0] . " karyawan aktif)</b></td><td align=right><b>" . (float)$sub[$kj][2] . "</b></td><td colspan=2></td></tr>";
				}
			}
			$stream .= "</tbody></table>";
			$xls = new HtmlExcel();
			$xls->addSheet("SetupKomponenTetap", $stream);
			$xls->headers("SetupKomponenTetap_" . date('YmdHis') . ".xls");
			echo $xls->buildFile();
			exit;
		}

		require_once('lib/fpdf.php');
		if (!class_exists('PDFListKT')) {
			class PDFListKT extends FPDF
			{
				public $colW = array();
				public $kopNama = '';
				public $kopLogo = '';
				public $kopInfo = '';
				public $kopDitarik = '';

				function Header()
				{
					$width = $this->w - $this->lMargin - $this->rMargin;
					if ($this->kopLogo != '' && file_exists($this->kopLogo)) {
						$this->Image($this->kopLogo, $this->lMargin, $this->tMargin, 18);
					}
					$this->SetFont('Arial', 'B', 11);
					$this->SetXY($this->lMargin + 22, $this->tMargin + 2);
					$this->Cell(160, 6, utf8_decode($this->kopNama), 0, 1, 'L');
					$this->SetY($this->tMargin + 15);
					$this->SetFont('Arial', 'B', 13);
					$this->Cell($width, 7, 'SETUP KOMPONEN TETAP', 0, 1, 'C');
					$this->SetFont('Arial', '', 8);
					$this->Cell($width, 5, utf8_decode($this->kopInfo), 0, 1, 'C');
					$this->Cell($width, 5, utf8_decode($this->kopDitarik), 0, 1, 'C');
					$this->Ln(1);
					$this->SetFillColor(220, 220, 220);
					$this->SetFont('Arial', 'B', 8);
					$judul = array('No', 'Organisasi', 'Komponen', 'Jenis', 'NIK', 'Nama Karyawan', 'Jabatan', 'Nominal / Bulan', 'Aktif');
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

				function fit($txt, $w, $f = 7)
				{
					$txt = utf8_decode($txt);
					$this->SetFont('Arial', '', $f);
					if ($this->GetStringWidth($txt) <= $w - 2) {
						return $txt;
					}
					while (strlen($txt) > 1 && $this->GetStringWidth($txt . '...') > $w - 2) {
						$txt = substr($txt, 0, -1);
					}
					return rtrim($txt) . '...';
				}
			}
		}
		$pdf = new PDFListKT('L', 'mm', 'A4');
		$pdf->kopNama = $hdpt['nama'];
		$pdf->kopLogo = isset($hdpt['logo']) ? $hdpt['logo'] : '';
		$pdf->kopInfo = $info;
		$pdf->kopDitarik = $ditarik;
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(false, 16);
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$colFixed = array(8, 38, 48, 20, 28, 55, 0, 28, 12); #Jabatan (indeks 6) mengisi sisa lebar
		$colFixed[6] = $width - array_sum($colFixed);
		$pdf->colW = $colFixed;
		$pdf->AddPage();
		$h = 6;
		$no = 0;
		foreach ($rows as $r) {
			$no++;
			if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
				$pdf->AddPage();
			}
			$c = $pdf->colW;
			$potongan = ((string)@$plusKomponen[$r['idkomponen']] === '0');
			$pdf->SetFont('Arial', '', 7);
			$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
			$pdf->Cell($c[1], $h, $pdf->fit($r['kodeorg'] . ' - ' . (string)@$nmUnit[$r['kodeorg']], $c[1]), 1, 0, 'L');
			$pdf->Cell($c[2], $h, $pdf->fit(trim((string)@$nmKomponen[$r['idkomponen']]) . ' (' . (int)$r['idkomponen'] . ')', $c[2]), 1, 0, 'L');
			$pdf->Cell($c[3], $h, $potongan ? 'Potongan' : 'Pendapatan', 1, 0, 'C');
			$pdf->Cell($c[4], $h, $pdf->fit((string)$r['nik'], $c[4]), 1, 0, 'L');
			$pdf->Cell($c[5], $h, $pdf->fit((string)$r['namakaryawan'], $c[5]), 1, 0, 'L');
			$pdf->Cell($c[6], $h, $pdf->fit((string)@$nmJabatan[$r['kodejabatan']], $c[6]), 1, 0, 'L');
			$pdf->Cell($c[7], $h, number_format($r['nominal'], 0), 1, 0, 'R');
			$pdf->Cell($c[8], $h, $r['aktif'] == 1 ? 'Ya' : 'Tidak', 1, 1, 'C');
		}
		if ($no == 0) {
			$pdf->Cell(array_sum($pdf->colW), $h, 'Data tidak ditemukan', 1, 1, 'C');
		} else {
			foreach ($totalBaris as $kj => $label) {
				if ($sub[$kj][0] == 0) {
					continue;
				}
				if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
					$pdf->AddPage();
				}
				$c = $pdf->colW;
				$pdf->SetFont('Arial', 'B', 7);
				$pdf->Cell(array_sum(array_slice($c, 0, 7)), $h, $label . ' (' . $sub[$kj][1] . ' dari ' . $sub[$kj][0] . ' karyawan aktif)', 1, 0, 'R');
				$pdf->Cell($c[7], $h, number_format($sub[$kj][2], 0), 1, 0, 'R');
				$pdf->Cell($c[8], $h, '', 1, 1, 'C');
			}
		}
		$pdf->Output();
		exit;

	case 'saveBanyak':
		cekUnit($unitPilih);
		cekKomponen($idkomponen);
		$data = json_decode((string)checkPostGet('data', ''), true);
		if (!is_array($data) || count($data) == 0) {
			exit("Warning : Tidak ada nominal yang diisi.");
		}
		if (count($data) > 2000) {
			exit("Warning : Terlalu banyak baris sekaligus (maksimal 2000).");
		}
		try {
			$owlPDO->beginTransaction();
			$cekKar = $owlPDO->prepare("select count(*) from " . $dbname . ".datakaryawan where karyawanid=? and lokasitugas=?");
			$simpan = $owlPDO->prepare("insert into " . $dbname . ".sdm_5premitetapkomp (kodeorg,idkomponen,karyawanid,nominal,aktif,createdby,updateby)
				values (?,?,?,?,?,?,?)
				on duplicate key update nominal=values(nominal), aktif=values(aktif), updateby=values(updateby)");
			$n = 0;
			foreach ($data as $b) {
				$kid = isset($b['id']) ? trim((string)$b['id']) : '';
				$nom = isset($b['nominal']) ? str_replace(',', '', trim((string)$b['nominal'])) : '';
				$akt = (isset($b['aktif']) && (string)$b['aktif'] === '0') ? 0 : 1;
				if (!preg_match('/^[0-9]{1,10}$/', $kid)) {
					$owlPDO->rollBack();
					exit("Warning : Data karyawan tidak valid.");
				}
				if (!preg_match('/^[0-9]{1,10}(\.[0-9]{1,2})?$/', $nom) || (float)$nom <= 0) {
					$owlPDO->rollBack();
					exit("Warning : Nominal karyawan " . htmlspecialchars($kid) . " harus angka lebih dari 0.");
				}
				if ((float)$nom > 100000000) {
					$owlPDO->rollBack();
					exit("Warning : Nominal terlalu besar (maksimal 100.000.000 per bulan).");
				}
				$cekKar->execute(array($kid, $unitPilih));
				if (!$cekKar->fetchColumn()) {
					$owlPDO->rollBack();
					exit("Warning : Karyawan " . htmlspecialchars($kid) . " tidak ditemukan di organisasi " . htmlspecialchars($unitPilih) . ".");
				}
				$simpan->execute(array($unitPilih, $idkomponen, $kid, $nom, $akt, $_SESSION['standard']['userid'], $_SESSION['standard']['userid']));
				$n++;
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			if ($owlPDO->inTransaction()) {
				$owlPDO->rollBack();
			}
			exit("Gagal : Setup tidak bisa disimpan. Pastikan sql_ppp_premi_genset.sql sudah dijalankan.");
		}
		echo "Selesai : " . $n . " karyawan tersimpan.";
		break;

	case 'delete':
		cekUnit($unitPilih);
		if (!preg_match('/^[0-9]{1,10}$/', $idkomponen) || !preg_match('/^[0-9]{1,10}$/', $karyawanid)) {
			exit("Warning : Data tidak valid.");
		}
		$stm = $owlPDO->prepare("delete from " . $dbname . ".sdm_5premitetapkomp where kodeorg=? and idkomponen=? and karyawanid=?");
		try {
			$stm->execute(array($unitPilih, $idkomponen, $karyawanid));
		} catch (PDOException $e) {
			exit("Gagal : Setup tidak bisa dihapus.");
		}
		break;

	case 'preview':
		#pratinjau hanya-baca: karyawan yang sudah disetup pada organisasi + komponen (ikon kaca pembesar di List Data)
		cekUnit($unitPilih);
		$komp = cekKomponen($idkomponen);
		try {
			$stm = $owlPDO->prepare("select s.nominal,s.aktif,s.updatetime,k.nik,k.namakaryawan,k.kodejabatan from " . $dbname . ".sdm_5premitetapkomp s left join " . $dbname . ".datakaryawan k on k.karyawanid=s.karyawanid where s.kodeorg=? and s.idkomponen=? order by k.namakaryawan");
			$stm->execute(array($unitPilih, $idkomponen));
			$res = $stm->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			exit("Gagal : Tabel setup belum ada. Jalankan sql_ppp_premi_genset.sql terlebih dahulu.");
		}
		$nmJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
		$nmUnit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$no = 0;
		$jmlAktif = 0;
		$total = 0;
		$tab = "<div style='font-size:12px;margin-bottom:6px'><b>" . htmlspecialchars($unitPilih . ' - ' . (string)@$nmUnit[$unitPilih]) . "</b> | <b>" . htmlspecialchars(trim($komp['name'])) . "</b> (" . ($komp['plus'] == 1 ? 'Pendapatan' : 'Potongan') . ")</div>
			<div style='max-height:340px;overflow:auto'><table class=sortable cellspacing=1 cellpadding=4 border=0 style='width:100%'>
			<thead><tr class=rowheader><td align=center>No</td><td align=center>NIK</td><td align=center>Nama Karyawan</td><td align=center>Jabatan</td><td align=center>Nominal / Bulan</td><td align=center>Aktif</td><td align=center>Waktu Diubah</td></tr></thead><tbody>";
		foreach ($res as $r) {
			$no++;
			if ($r['aktif'] == 1) {
				$jmlAktif++;
				$total += $r['nominal'];
			}
			$tab .= "<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td>" . htmlspecialchars((string)$r['nik']) . "</td>
				<td>" . htmlspecialchars((string)$r['namakaryawan']) . "</td>
				<td>" . htmlspecialchars((string)@$nmJabatan[$r['kodejabatan']]) . "</td>
				<td align=right>" . number_format($r['nominal'], 0) . "</td>
				<td align=center>" . ($r['aktif'] == 1 ? 'Ya' : 'Tidak') . "</td>
				<td align=center nowrap>" . htmlspecialchars((string)$r['updatetime']) . "</td>
			</tr>";
		}
		if ($no == 0) {
			$tab .= "<tr class=rowcontent><td colspan=7 align=center>Belum ada karyawan.</td></tr>";
		}
		$tab .= "</tbody></table></div><div style='font-size:12px;margin-top:6px'>" . $no . " karyawan (" . $jmlAktif . " aktif) | total aktif per bulan: <b>Rp " . number_format($total, 0) . "</b></div>";
		echo $tab;
		break;

	case 'loadDetail':
		cekUnit($unitPilih);
		cekKomponen($idkomponen);
		#semua karyawan aktif organisasi itu + yang sudah punya setup (walau sudah keluar), nominal terisi bila sudah disetup
		try {
			$stm = $owlPDO->prepare("select k.karyawanid,k.nik,k.namakaryawan,k.kodejabatan,k.lokasitugas,s.nominal,s.aktif,s.updatetime,s.updateby, (s.karyawanid is not null) as sudah,
				(select group_concat(distinct o.kodeorg order by o.kodeorg separator ', ') from " . $dbname . ".sdm_5premitetapkomp o where o.idkomponen=? and o.karyawanid=k.karyawanid and o.kodeorg<>?) as unitlain
				from " . $dbname . ".datakaryawan k
				left join " . $dbname . ".sdm_5premitetapkomp s on s.kodeorg=? and s.idkomponen=? and s.karyawanid=k.karyawanid
				where k.lokasitugas=? and (k.tanggalkeluar>=? or k.tanggalkeluar='0000-00-00' or k.tanggalkeluar is null or s.karyawanid is not null)
				order by (s.karyawanid is null), k.namakaryawan");
			$stm->execute(array($idkomponen, $unitPilih, $unitPilih, $idkomponen, $unitPilih, date('Y-m-d')));
			$res = $stm->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			exit("Gagal : Tabel setup belum ada. Jalankan sql_ppp_premi_genset.sql terlebih dahulu.");
		}
		$nmJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
		$no = 0;
		$jmlSetup = 0;
		$jmlAktif = 0;
		$totalAktif = 0;
		$tab = '';
		foreach ($res as $r) {
			$no++;
			$sudah = ($r['sudah'] == 1);
			if ($sudah) {
				$jmlSetup++;
				if ($r['aktif'] == 1) {
					$jmlAktif++;
					$totalAktif += $r['nominal'];
				}
			}
			$kid = (int)$r['karyawanid'];
			#catatan mutasi: setup ada di unit ini tapi karyawan sudah bertugas di unit lain, atau karyawan sudah punya setup komponen ini di unit lain
			$catatan = '';
			if ($sudah && $r['lokasitugas'] != $unitPilih) {
				$catatan = "<br><span style='font-size:10px;color:#b42318'>Sudah pindah ke " . htmlspecialchars((string)$r['lokasitugas']) . " - setup tetap dibayar oleh unit tugasnya</span>";
			} elseif (!$sudah && (string)$r['unitlain'] !== '') {
				$catatan = "<br><span style='font-size:10px;color:#b42318'>Sudah disetup di " . htmlspecialchars((string)$r['unitlain']) . "</span>";
			}
			$tab .= "<tr class='rowcontent" . ($sudah ? '' : ' belumKT') . "' data-setup='" . ($sudah ? 1 : 0) . "' data-cari=\"" . htmlspecialchars(strtolower($r['nik'] . ' ' . $r['namakaryawan'] . ' ' . (string)@$nmJabatan[$r['kodejabatan']]), ENT_QUOTES) . "\">
				<td align=center>" . $no . "</td>
				<td>" . htmlspecialchars((string)$r['nik']) . "</td>
				<td>" . htmlspecialchars((string)$r['namakaryawan']) . $catatan . "</td>
				<td>" . htmlspecialchars((string)@$nmJabatan[$r['kodejabatan']]) . "</td>
				<td align=center><input type=text class=myinputtextnumber data-kid='" . $kid . "' value='" . ($sudah ? number_format($r['nominal'], 0) : '') . "' onkeyup='formatNominalKT(this)' onkeypress='return angka_doang(event)' maxlength=14></td>
				<td align=center><input type=checkbox " . (!$sudah || $r['aktif'] == 1 ? 'checked' : '') . "></td>
				<td align=center nowrap>" . ($sudah ? htmlspecialchars((string)$r['updatetime']) : '') . "</td>
				<td align=center>" . ($sudah ? "<img src=images/application/application_delete.png class=zImgBtn title='Hapus dari setup' onclick=\"hapusKT('" . $kid . "');\">" : "") . "</td>
			</tr>";
		}
		if ($no == 0) {
			$tab = "<tr class=rowcontent><td colspan=8 align=center>Tidak ada karyawan di organisasi ini.</td></tr>";
		}
		echo $tab . "##" . $no . " karyawan | sudah disetup: " . $jmlSetup . " (aktif " . $jmlAktif . ") | total aktif per bulan: <b>Rp " . number_format($totalAktif, 0) . "</b>";
		break;

	case 'loadData':
		list($where, $par) = filterKT();
		try {
			$stm = $owlPDO->prepare("select s.kodeorg, s.idkomponen, count(*) as jml, sum(s.aktif=1) as jmlaktif, sum(case when s.aktif=1 then s.nominal else 0 end) as total
				from " . $dbname . ".sdm_5premitetapkomp s left join " . $dbname . ".datakaryawan k on k.karyawanid=s.karyawanid
				where" . $where . " group by s.kodeorg, s.idkomponen order by s.kodeorg, s.idkomponen");
			$stm->execute($par);
			$res = $stm->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			exit("Gagal : Tabel setup belum ada. Jalankan sql_ppp_premi_genset.sql terlebih dahulu.");
		}
		$nmKomponen = makeOption($dbname, 'sdm_ho_component', 'id,name');
		$plusKomponen = makeOption($dbname, 'sdm_ho_component', 'id,plus');
		$nmUnit = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$no = 0;
		$sub = array('P' => array(0, 0, 0), 'K' => array(0, 0, 0)); #per jenis (P=pendapatan, K=potongan): karyawan, aktif, nominal aktif
		$tab = '';
		foreach ($res as $r) {
			$no++;
			$potongan = ((string)@$plusKomponen[$r['idkomponen']] === '0');
			$kj = $potongan ? 'K' : 'P';
			$sub[$kj][0] += $r['jml'];
			$sub[$kj][1] += $r['jmlaktif'];
			$sub[$kj][2] += $r['total'];
			$u = htmlspecialchars($r['kodeorg'], ENT_QUOTES);
			$tab .= "<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td>" . htmlspecialchars($r['kodeorg'] . ' - ' . (string)@$nmUnit[$r['kodeorg']]) . "</td>
				<td>" . htmlspecialchars((string)@$nmKomponen[$r['idkomponen']]) . " (" . (int)$r['idkomponen'] . ")</td>
				<td align=center>" . ($potongan ? "<span style='color:#b42318'>Potongan</span>" : "Pendapatan") . "</td>
				<td align=center>" . (int)$r['jml'] . "</td>
				<td align=center>" . (int)$r['jmlaktif'] . "</td>
				<td align=right>" . number_format($r['total'], 0) . "</td>
				<td align=center width=25px><img src=images/skyblue/zoom.png class=zImgBtn title='Preview' onclick=\"previewKT('" . $u . "','" . (int)$r['idkomponen'] . "',event);\"></td>
				<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn title='Buka' onclick=\"bukaKT('" . $u . "','" . (int)$r['idkomponen'] . "');\"></td>
			</tr>";
		}
		if ($no == 0) {
			$tab .= "<tr class=rowcontent><td colspan=9 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		} else {
			#total dipisah per jenis agar pendapatan dan potongan tidak tercampur
			foreach (array('P' => 'Total Pendapatan', 'K' => 'Total Potongan') as $kj => $label) {
				if ($sub[$kj][0] == 0) {
					continue;
				}
				$warna = $kj == 'K' ? 'color:#b42318;' : '';
				$tab .= "<tr class=rowcontent style='font-weight:bold;" . $warna . "'><td colspan=4 align=right>" . $label . "</td><td align=center>" . $sub[$kj][0] . "</td><td align=center>" . $sub[$kj][1] . "</td><td align=right>" . number_format($sub[$kj][2], 0) . "</td><td colspan=2></td></tr>";
			}
		}
		echo $tab;
		break;
}
