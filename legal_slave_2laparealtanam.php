<?php
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$tahun = checkPostGet('tahun0', '');
$kebun = checkPostGet('kebun0', '');
$afdeling = checkPostGet('afdeling0', '');
$tahuntanam = checkPostGet('tahuntanam0', '');
$umurtua = checkPostGet('umurtua0', '25');

#=== Pengamanan: format parameter sebelum dipakai di query (pattern legal_slave_2csr) ===
$polaAreal = array(
	'tahun'     => '/^[0-9]{4}$/',
	'kebun'     => '/^[A-Za-z0-9]{1,6}$/',
	'afdeling'  => '/^[A-Za-z0-9]{1,6}$/',
	'tahuntanam'=> '/^[0-9]{1,4}$/',
	'umurtua'   => '/^[0-9]{1,3}$/',
	'proses'    => '/^[a-zA-Z0-9]{1,20}$/'
);
foreach ($polaAreal as $kunciAreal => $polaX) {
	$nilaiX = $$kunciAreal;
	if ($nilaiX === 'undefined' || $nilaiX === 'null') {
		$nilaiX = $$kunciAreal = '';
	}
	if ($kunciAreal == 'umurtua' && ($nilaiX === '' || $nilaiX === '0')) {
		$nilaiX = $$kunciAreal = '25'; #default batas umur tanaman tua
	}
	if ($nilaiX !== '' && (!is_string($nilaiX) || !preg_match($polaX, $nilaiX))) {
		exit("Warning : Parameter " . $kunciAreal . " tidak valid.");
	}
}

$namaOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

if ($proses == 'preview' || $proses == 'excel' || $proses == 'pdf' || $proses == 'getAfdeling0' || $proses == 'getTahuntanam0') {
	if (($proses != 'getAfdeling0' && $proses != 'getTahuntanam0') && $kebun == '') {
		exit("Error: Kebun wajib dipilih");
	}
}

if ($proses == 'preview' || $proses == 'excel' || $proses == 'pdf') {
	$tahunlalu = $tahun - 1;
	$umurtua = intval($umurtua);

	#where blok: afdeling kosong = seluruh afdeling di bawah kebun yang boleh diakses (pattern kebun_slave_2aresta)
	$where = "";
	if ($afdeling == '') {
		$where = " and substr(kodeorg,1,6) in (" . getOrgDetail(26) . ")";
	} else {
		$where = " and kodeorg like '" . $afdeling . "%'";
	}

	#sumber data pokok per blok: setup_blok_tahunan snapshot tahun-1; jika tahun berjalan pakai setup_blok teraktual (pattern kebun_slave_2aresta)
	if ($tahun == date("Y")) {
		$sTabel = " from " . $dbname . ".setup_blok where kodeorg like '" . $kebun . "%' " . $where . " and tahuntanam like '" . $tahuntanam . "%' and (luasareaproduktif+luasareanonproduktif)>0 ";
	} else {
		$sTabel = " from " . $dbname . ".setup_blok_tahunan where kodeorg like '" . $kebun . "%' " . $where . " and tahuntanam like '" . $tahuntanam . "%' and (luasareaproduktif+luasareanonproduktif)>0 and tahun like '" . $tahun . "%' ";
	}
	$sWhat = "select kodeorg, tahuntanam, statusblok, luasareaproduktif, jumlahpokok " . $sTabel;
	$rWhat = $owlPDO->query($sWhat) or die(print " Gagal: " . PDOException::getMessage());
	$rWhat->setFetchMode(PDO::FETCH_OBJ);
	while ($bWhat = $rWhat->fetch()) {
		$tT = $bWhat->tahuntanam;
		$kD = substr($bWhat->kodeorg, 0, 6); #afdeling
		$umur = $tahun - $tT;

		#klasifikasi kelas (keputusan user): bibitan terpisah, tua/rusak dari input umur
		if ($tT == 0 || $bWhat->statusblok == 'BBT') {
			$kelas = 'BIBITAN';
		} elseif ($umur > $umurtua) {
			$kelas = 'TUA';
		} elseif ($umur >= 1 && $umur <= 3) {
			$kelas = 'TBM';
		} else {
			$kelas = 'TM';
		}

		setIt($data[$kD][$tT]['tbm'], 0);
		setIt($data[$kD][$tT]['tm'], 0);
		setIt($data[$kD][$tT]['tua'], 0);
		setIt($data[$kD][$tT]['pokok'], 0);
		$data[$kD][$tT][$kelas == 'BIBITAN' ? 'bibitan' : ($kelas == 'TBM' ? 'tbm' : ($kelas == 'TUA' ? 'tua' : 'tm'))] += $bWhat->luasareaproduktif;
		$data[$kD][$tT]['pokok'] += $bWhat->jumlahpokok;
		$data[$kD][$tT]['tahun'] = $tT;
		$data[$kD][$tT]['kelas'] = ($tT == 0 || $bWhat->statusblok == 'BBT') ? 'BIBITAN' : (($umur >= 1 && $umur <= 3 && $kelas != 'TUA') ? 'TBM ' . $umur : ($kelas == 'TUA' ? 'TUA > ' . $umurtua . ' Thn' : 'TM'));
		if ($tT == 0 || $bWhat->statusblok == 'BBT') {
			$data[$kD][$tT]['kelas'] = 'BIBITAN';
		}
	}
	ksort($data);

	#header tabel
	$tab = $_SESSION['lang']['laporan'] . " Areal Tanam<br>" . $_SESSION['lang']['unitkerja'] . ": " . $kebun . " " . $afdeling . " " . $tahuntanam . ". Batas Umur Tua/Rusak: " . $umurtua . " tahun.";
	$tab .= "<div class='table-scroll'><table width=100% class=sortable border=0 cellspacing=1>
	<thead>
	<tr class=rowheader>
		<th align=center>Afdeling</th>
		<th align=center>" . $_SESSION['lang']['tahuntanam'] . "</th>
		<th align=center>Kelas</th>
		<th align=center>TBM (Ha)</th>
		<th align=center>TM (Ha)</th>
		<th align=center>Tua/Rusak (Ha)</th>
		<th align=center>Bibitan (Ha)</th>
		<th align=center>Total (Ha)</th>
		<th align=center>Jumlah Pokok</th>
	</tr>
	</thead>
	<tbody>";

	$grand = array('tbm' => 0, 'tm' => 0, 'tua' => 0, 'bibitan' => 0, 'total' => 0, 'pokok' => 0);
	$adaData = false;
	foreach ($data as $kD => $arrTT) {
		#subtotal afdeling
		$sub = array('tbm' => 0, 'tm' => 0, 'tua' => 0, 'bibitan' => 0, 'total' => 0, 'pokok' => 0);
		foreach ($arrTT as $tT => $r) {
			$sub['tbm'] += $r['tbm'];
			$sub['tm'] += $r['tm'];
			$sub['tua'] += $r['tua'];
			$sub['bibitan'] += $r['bibitan'];
			$sub['pokok'] += $r['pokok'];
		}
		$sub['total'] = $sub['tbm'] + $sub['tm'] + $sub['tua'] + $sub['bibitan'];

		#baris detail per tahun tanam, afdeling di-rowspan
		$rowspan = count($arrTT);
		$first = true;
		foreach ($arrTT as $tT => $r) {
			$total = $r['tbm'] + $r['tm'] + $r['tua'] + $r['bibitan'];
			$tab .= "<tr class=rowcontent>";
			if ($first) {
				$tab .= "<td rowspan=" . $rowspan . " align=left valign=top><b>" . $kD . (isset($namaOrg[$kD]) ? " - " . $namaOrg[$kD] : "") . "</b></td>";
				$first = false;
			}
			$tab .= "<td align=center>" . $tT . "</td>
				<td align=center>" . $r['kelas'] . "</td>
				<td align=right>" . number_format($r['tbm'], 2) . "</td>
				<td align=right>" . number_format($r['tm'], 2) . "</td>
				<td align=right>" . number_format($r['tua'], 2) . "</td>
				<td align=right>" . number_format($r['bibitan'], 2) . "</td>
				<td align=right>" . number_format($total, 2) . "</td>
				<td align=right>" . number_format($r['pokok']) . "</td>
				</tr>";
			$adaData = true;
		}
		#subtotal afdeling
		$tab .= "<tr class=rowcontent>
			<td colspan=3 align=left><b>Subtotal " . $kD . "</b></td>
			<td align=right><b>" . number_format($sub['tbm'], 2) . "</b></td>
			<td align=right><b>" . number_format($sub['tm'], 2) . "</b></td>
			<td align=right><b>" . number_format($sub['tua'], 2) . "</b></td>
			<td align=right><b>" . number_format($sub['bibitan'], 2) . "</b></td>
			<td align=right><b>" . number_format($sub['total'], 2) . "</b></td>
			<td align=right><b>" . number_format($sub['pokok']) . "</b></td>
			</tr>";

		$grand['tbm'] += $sub['tbm'];
		$grand['tm'] += $sub['tm'];
		$grand['tua'] += $sub['tua'];
		$grand['bibitan'] += $sub['bibitan'];
		$grand['total'] += $sub['total'];
		$grand['pokok'] += $sub['pokok'];
	}

	if (!$adaData) {
		$tab .= "<tr class=rowcontent><td colspan=9 align=center>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
	} else {
		$tab .= "<tr class=rowheader>
			<td colspan=3 align=right><b>GRAND TOTAL</b></td>
			<td align=right><b>" . number_format($grand['tbm'], 2) . "</b></td>
			<td align=right><b>" . number_format($grand['tm'], 2) . "</b></td>
			<td align=right><b>" . number_format($grand['tua'], 2) . "</b></td>
			<td align=right><b>" . number_format($grand['bibitan'], 2) . "</b></td>
			<td align=right><b>" . number_format($grand['total'], 2) . "</b></td>
			<td align=right><b>" . number_format($grand['pokok']) . "</b></td>
			</tr>";
	}
	$tab .= "</tbody></table></div>";
}

switch ($proses) {
	case 'preview':
		echo $tab;
	break;

	case 'excel':
		$tab .= "Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
		$nop_ = "laparealtanam_" . $tahun . "_" . $kebun . "_" . $afdeling . "_" . $tahuntanam;
		if (strlen($tab) > 0) {
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						@unlink('tempExcel/' . $file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
			if (!fwrite($handle, $tab)) {
				echo "<script language=javascript1.2>
				parent.window.alert('Can\'t convert to excel format');
				</script>";
				exit;
			} else {
				echo "<script language=javascript1.2>
				window.location='tempExcel/" . $nop_ . ".xls';
				</script>";
			}
		}
	break;

	case 'pdf':
		class PDFArealTanam extends FPDF
		{
			function Header()
			{
				global $owlPDO, $dbname, $tahun, $kebun, $afdeling, $tahuntanam, $umurtua;
				$query = selectQuery($dbname, 'organisasi', 'alamat,telepon', "kodeorganisasi='" . $_SESSION['org']['kodeorganisasi'] . "'");
				$orgData = fetchData($query);
				$width = $this->w - $this->lMargin - $this->rMargin;
				$height = 15;
				$path = 'images/logo.jpg';
				if (file_exists($path)) {
					$this->Image($path, $this->lMargin, $this->tMargin, 0, 40);
				}
				$this->SetFont('Arial', 'B', 9);
				$this->SetX(60);
				$this->Cell($width - 60, $height, $_SESSION['org']['namaorganisasi'], 0, 1, 'L');
				$this->SetX(60);
				$this->Cell($width - 60, $height, $orgData[0]['alamat'], 0, 1, 'L');
				$this->SetX(60);
				$this->Cell($width - 60, $height, "Tel: " . $orgData[0]['telepon'], 0, 1, 'L');
				$this->Line($this->lMargin, $this->tMargin + ($height * 3.4), $this->lMargin + $width, $this->tMargin + ($height * 3.4));
				$this->Ln(2);
				$this->SetFont('Arial', 'B', 12);
				$this->Cell($width, $height, 'Laporan Areal Tanam ' . $tahun, 0, 1, 'C');
				$this->SetFont('Arial', '', 8);
				$this->Cell($width, $height / 2, $_SESSION['lang']['kebun'] . ': ' . $kebun . ($afdeling != '' ? '   ' . $_SESSION['lang']['afdeling'] . ': ' . $afdeling : '') . ($tahuntanam != '' ? '   ' . $_SESSION['lang']['tahuntanam'] . ': ' . $tahuntanam : '') . '   Batas Umur Tua/Rusak: ' . $umurtua . ' tahun', 0, 1, 'C');
				$this->Ln(2);
				#header tabel
				$this->SetFillColor(220, 220, 220);
				$this->SetFont('Arial', 'B', 8);
				$w = $this->colW;
				$j = array('Afdeling', 'Tahun Tanam', 'Kelas', 'TBM (Ha)', 'TM (Ha)', 'Tua/Rusak (Ha)', 'Bibitan (Ha)', 'Total (Ha)', 'Jumlah Pokok');
				foreach ($j as $i => $v) {
					$this->Cell($w[$i], 8, $v, 1, 0, 'C', true);
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
		$pdf = new PDFArealTanam('L', 'pt', 'A4');
		$pdf->AliasNbPages();
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$colFixed = array(90, 55, 70, 60, 60, 70, 65, 65); #semua kecuali Jumlah Pokok
		$pdf->colW = array_merge($colFixed, array($width - array_sum($colFixed)));
		$pdf->AddPage();
		$height = 12;
		$pdf->SetFont('Arial', '', 8);
		if (!$adaData) {
			$pdf->Cell(array_sum($pdf->colW), $height, $_SESSION['lang']['errdatanotexist'], 1, 1, 'C');
		}
		foreach ($data as $kD => $arrTT) {
			$sub = array('tbm' => 0, 'tm' => 0, 'tua' => 0, 'bibitan' => 0, 'total' => 0, 'pokok' => 0);
			foreach ($arrTT as $tT => $r) {
				$sub['tbm'] += $r['tbm'];
				$sub['tm'] += $r['tm'];
				$sub['tua'] += $r['tua'];
				$sub['bibitan'] += $r['bibitan'];
				$sub['pokok'] += $r['pokok'];
			}
			$sub['total'] = $sub['tbm'] + $sub['tm'] + $sub['tua'] + $sub['bibitan'];
			#pastikan blok afdeling + detail muat 1 halaman (rowspan tidak bisa dipecah PDF)
			if ($pdf->GetY() + $height * (count($arrTT) + 2) > $pdf->h - $pdf->bMargin - 20) {
				$pdf->AddPage();
			}
			$w = $pdf->colW;
			$c = $pdf->GetX();
			$yStart = $pdf->GetY();
			$pdf->Cell($w[0], $height * $rowspan = count($arrTT), $kD, 1, 0, 'L');
			$first = true;
			foreach ($arrTT as $tT => $r) {
				$total = $r['tbm'] + $r['tm'] + $r['tua'] + $r['bibitan'];
				if (!$first) {
					$pdf->SetX($c + $w[0]);
				}
				$pdf->Cell($w[1], $height, $tT, 1, 0, 'C');
				$pdf->Cell($w[2], $height, $r['kelas'], 1, 0, 'C');
				$pdf->Cell($w[3], $height, number_format($r['tbm'], 2), 1, 0, 'R');
				$pdf->Cell($w[4], $height, number_format($r['tm'], 2), 1, 0, 'R');
				$pdf->Cell($w[5], $height, number_format($r['tua'], 2), 1, 0, 'R');
				$pdf->Cell($w[6], $height, number_format($r['bibitan'], 2), 1, 0, 'R');
				$pdf->Cell($w[7], $height, number_format($total, 2), 1, 0, 'R');
				$pdf->Cell($w[8], $height, number_format($r['pokok']), 1, 0, 'R');
				$pdf->Ln();
				$first = false;
			}
			$pdf->SetX($c);
			$pdf->SetFont('Arial', 'B', 8);
			$pdf->Cell($w[0] + $w[1] + $w[2], $height, 'Subtotal ' . $kD, 1, 0, 'L');
			$pdf->Cell($w[3], $height, number_format($sub['tbm'], 2), 1, 0, 'R');
			$pdf->Cell($w[4], $height, number_format($sub['tm'], 2), 1, 0, 'R');
			$pdf->Cell($w[5], $height, number_format($sub['tua'], 2), 1, 0, 'R');
			$pdf->Cell($w[6], $height, number_format($sub['bibitan'], 2), 1, 0, 'R');
			$pdf->Cell($w[7], $height, number_format($sub['total'], 2), 1, 0, 'R');
			$pdf->Cell($w[8], $height, number_format($sub['pokok']), 1, 0, 'R');
			$pdf->Ln();
			$pdf->SetFont('Arial', '', 8);
		}
		if ($adaData) {
			$pdf->SetFont('Arial', 'B', 8);
			$w = $pdf->colW;
			$pdf->Cell($w[0] + $w[1] + $w[2], $height, 'GRAND TOTAL', 1, 0, 'R');
			$pdf->Cell($w[3], $height, number_format($grand['tbm'], 2), 1, 0, 'R');
			$pdf->Cell($w[4], $height, number_format($grand['tm'], 2), 1, 0, 'R');
			$pdf->Cell($w[5], $height, number_format($grand['tua'], 2), 1, 0, 'R');
			$pdf->Cell($w[6], $height, number_format($grand['bibitan'], 2), 1, 0, 'R');
			$pdf->Cell($w[7], $height, number_format($grand['total'], 2), 1, 0, 'R');
			$pdf->Cell($w[8], $height, number_format($grand['pokok']), 1, 0, 'R');
			$pdf->Ln();
		}
		$pdf->Output();
	break;

	case 'getAfdeling0':
		$optAfd = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$sPrd = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi
			where induk = '" . $kebun . "' and tipe='afdeling' and kodeorganisasi in (" . getOrgDetail(26) . ") order by namaorganisasi asc";
		$qPrd = $owlPDO->query($sPrd) or die(print " Gagal: " . PDOException::getMessage());
		$qPrd->setFetchMode(PDO::FETCH_ASSOC);
		while ($rPrd = $qPrd->fetch()) {
			$optAfd .= "<option value=" . $rPrd['kodeorganisasi'] . ">" . $rPrd['namaorganisasi'] . "</option>";
		}

		$optafd2 = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$sorg2 = "select distinct tahuntanam from " . $dbname . ".setup_blok
			where kodeorg like '" . $kebun . "%' and tahuntanam>0 and (luasareaproduktif+luasareanonproduktif)>0 order by tahuntanam asc";
		$qorg2 = $owlPDO->query($sorg2) or die(print " Gagal: " . PDOException::getMessage());
		$qorg2->setFetchMode(PDO::FETCH_ASSOC);
		while ($rorg2 = $qorg2->fetch()) {
			$optafd2 .= "<option value='" . $rorg2['tahuntanam'] . "'>" . $rorg2['tahuntanam'] . "</option>";
		}
		echo $optAfd . "####" . $optafd2;
	break;

	case 'getTahuntanam0':
		$optafd2 = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$sorg2 = "select distinct tahuntanam from " . $dbname . ".setup_blok
			where kodeorg like '" . $kebun . "%' and kodeorg like '" . $afdeling . "%' and tahuntanam>0 and (luasareaproduktif+luasareanonproduktif)>0 order by tahuntanam asc";
		$qorg2 = $owlPDO->query($sorg2) or die(print " Gagal: " . PDOException::getMessage());
		$qorg2->setFetchMode(PDO::FETCH_ASSOC);
		while ($rorg2 = $qorg2->fetch()) {
			$optafd2 .= "<option value='" . $rorg2['tahuntanam'] . "'>" . $rorg2['tahuntanam'] . "</option>";
		}
		echo $optafd2 . "####";
	break;

	default:
	break;
}
?>
