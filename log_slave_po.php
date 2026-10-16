<?php
error_reporting(E_ERROR & ~E_NOTICE);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('lib/fpdf.php');

$method = checkPostGet('method', '');
$nopo = checkPostGet('nopo', '');
$matauang = checkPostGet('matauang', '');
$angkurs = checkPostGet('angkurs', '');
$supplierid = checkPostGet('supplierid', '');
$sjx = checkPostGet('sjx', '');
$idxz = checkPostGet('idxz', '');

$txtsearch_nopp = checkPostGet('txtsearch_nopp', '');

$crnopo = checkPostGet('crnopo', '');
$crtanggal = checkPostGet('crtanggal', '');
$tglrilis_cari = checkPostGet('tglrilis_cari', '');
$txtnamsupsch = checkPostGet('txtnamsupsch', '');
$pages = checkPostGet('page', '');

$persentermin = str_replace(',', '', checkPostGet('persentermin', ''));
$nilaitermin = str_replace(',', '', checkPostGet('nilaitermin', ''));
$termin = checkPostGet('termin', '');

$kodebarang = checkPostGet('kodebarang', '');

$expnopo = explode('/', $nopo);

$arrcheck = array("0" => "", "1" => "√");

$tipeApp = "PO";
$jenisApp = "PO";

#=== Filter list PO serta tarikan Excel/PDF (dipakai bersama oleh list, Excel, dan PDF) ===
#teks bebas di-escape sekali; tanggal dd-mm-yyyy; status 1-5; tahun 4 digit; supplier alfanumerik
if (!function_exists('poEsc')) {
	function poEsc($v)
	{
		global $owlPDO;
		return substr($owlPDO->quote((string)$v), 1, -1);
	}
}
if (!function_exists('poTglSql')) {
	function poTglSql($v)
	{
		return preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', trim((string)$v), $m) ? $m[3] . '-' . $m[2] . '-' . $m[1] : '';
	}
}
if (!function_exists('poWhereList')) {
	function poWhereList()
	{
		global $dbname;
		$where = "";
		$v = trim((string)checkPostGet('crnopo', ''));
		if ($v != '') {
			$where .= " and a.nopo LIKE '%" . poEsc($v) . "%'";
		}
		$v = trim((string)checkPostGet('txtsearch_nopp', ''));
		if ($v != '') {
			$where .= " and b.nopp LIKE '%" . poEsc($v) . "%'";
		}
		$v = poTglSql(checkPostGet('crtanggal', ''));
		if ($v != '') {
			$where .= " and a.tanggal='" . $v . "'";
		}
		$v = poTglSql(checkPostGet('tglrilis_cari', ''));
		if ($v != '') {
			$where .= " and a.tglrelease LIKE '%" . $v . "%'";
		}
		$v = trim((string)checkPostGet('txtnamsupsch', ''));
		if ($v != '') {
			$where .= " and a.kodesupplier in (select supplierid from " . $dbname . ".log_5supplier where namasupplier like '%" . poEsc($v) . "%')";
		}
		$v = trim((string)checkPostGet('filterSupplier', ''));
		if ($v != '' and preg_match('/^[A-Za-z0-9_\-\.]+$/', $v)) {
			$where .= " and a.kodesupplier='" . $v . "'";
		}
		$st = (string)checkPostGet('filterId', '');
		if ($st == '1') {
			##RELEASE
			$where .= " and a.statuspo in ('2','3') and a.stat_release='1' and a.closed='0'";
		} elseif ($st == '2') {
			##UNRELEASE
			$where .= " and a.statuspo in ('0','1')";
		} elseif ($st == '3') {
			##BECOME OUT STANDING
			$where .= " and a.statuspo in ('2','3') and a.closed='1' and a.keteranganclose like '%,tanggal tutup : %'";
		} elseif ($st == '4') {
			##CLOSE
			$where .= " and a.statuspo in ('2','3') and a.closed='1' and (a.keterangan like '%,tanggal tutup : %' or a.keteranganclose like '%Tutup By System%')";
		} elseif ($st == '5') {
			##CANCEL
			$where .= " and a.statuspo in ('4') and a.closed='1' and (a.keteranganclose like '%,tanggal cancel : %')";
		}
		$th = trim((string)checkPostGet('filterTahun', ''));
		if (preg_match('/^\d{4}$/', $th)) {
			$where .= " and a.tanggal>='" . $th . "-01-01' and a.tanggal<='" . $th . "-12-31'";
		}
		return $where;
	}
}
#PO milik purchaser yang login (jabatan 5 melihat semua)
if (!function_exists('poScopeList')) {
	function poScopeList()
	{
		if ($_SESSION['empl']['kodejabatan'] == '5') {
			return "";
		}
		return " and a.purchaser='" . poEsc($_SESSION['standard']['userid']) . "'";
	}
}
#status, PIC, dan keterangan satu PO (dipakai tampilan list dan tarikan Excel/PDF)
if (!function_exists('poStatusInfo')) {
	function poStatusInfo($bar)
	{
		global $dbname;
		$pic = "";
		$optPic = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['useridreleasae'] . "'");
		$picrelease = $optPic[$bar['useridreleasae']];
		$optPic = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan', "karyawanid='" . $bar['userclosed'] . "'");
		$picclose = $optPic[$bar['userclosed']];

		##STATUS PO
		$keterangan = "";
		$postatus = "";
		if ($bar['statuspo'] == '0') {
			$postatus = "Unrelease";
			$keterangan .= "- Belum diajukan";
		} else if ($bar['statuspo'] == '1') {
			$postatus = "Unrelease";
			$keterangan .= "- Proses persetujuan";
		} else if ($bar['statuspo'] == '2') {
			if ($bar['closed'] == '0') {
				$postatus = "Release";
				$keterangan .= "";
			} else {
				if (strpos($bar['keteranganclose'], ",tanggal tutup : ")) {
					$postatus = "Become Out Standing";
					$keterangan .= $bar['keteranganclose'];
					$pic = $picclose;
				}
				if (strpos($bar['keterangan'], ",tanggal tutup : ")) {
					$postatus = "Close";
					$keterangan .= $bar['keterangan'];
					$pic = $picclose;
				}
				if (strpos($bar['keteranganclose'], "Tutup By System")) {
					$postatus = "Close";
					$keterangan .= "- Semua Barang Sudah Diterimakan";
					$pic = "bysystem";
				}
			}
		} else if ($bar['statuspo'] == '3') {
			if ($bar['closed'] == '0') {
				$postatus = "Release";
				$keterangan .= "- Sudah ada penerimaan";
				$pic = $picrelease;
			} else {
				if (strpos($bar['keteranganclose'], ",tanggal tutup : ")) {
					$postatus = "Become Out Standing";
					$keterangan .= "- Sudah ada penerimaan<br>";
					$keterangan .= "- " . $bar['keteranganclose'];
					$pic = $picclose;
				}
				if (strpos($bar['keterangan'], ",tanggal tutup : ")) {
					$postatus = "Close";
					$keterangan .= "- Sudah ada penerimaan<br>";
					$keterangan .= "- " . $bar['keterangan'];
					$pic = $picclose;
				}
				if (strpos($bar['keteranganclose'], "Tutup By System")) {
					$postatus = "Close";
					$keterangan .= "- Semua Barang Sudah Diterimakan";
					$pic = "bysystem";
				}
			}
		} else if ($bar['statuspo'] == '4') {
			$postatus = "Cancel";
			$keterangan .= $bar['keteranganclose'];
			$pic = $picclose;
		}
		return array('postatus' => $postatus, 'keterangan' => $keterangan, 'pic' => $pic);
	}
}
#teks keterangan (berisi tag br) menjadi teks biasa
if (!function_exists('poTeksDatar')) {
	function poTeksDatar($html, $pemisah)
	{
		$t = preg_replace('/<br\s*\/?>/i', "\n", (string)$html);
		$t = trim(strip_tags($t));
		$t = preg_replace('/\n+/', $pemisah, $t);
		return trim($t);
	}
}
#warna latar status persetujuan satu tingkat (0 menunggu, 1 disetujui, 2 dikoreksi, 3 ditolak)
if (!function_exists('poWarnaApproval')) {
	function poWarnaApproval($status)
	{
		$w = array('0' => '#ffeb9c', '1' => '#c6efce', '2' => '#fce4d6', '3' => '#ffc7ce');
		return isset($w[(string)$status]) ? $w[(string)$status] : '';
	}
}
#warna status PO: latar dan teks (hijau Release, kuning Unrelease, oranye Become Out Standing, abu Close, merah Cancel)
if (!function_exists('poWarnaStatus')) {
	function poWarnaStatus($status)
	{
		$w = array(
			'Release' => array('#c6efce', '#006100'),
			'Unrelease' => array('#ffeb9c', '#9c5700'),
			'Become Out Standing' => array('#fce4d6', '#a04000'),
			'Close' => array('#d9d9d9', '#404040'),
			'Cancel' => array('#ffc7ce', '#9c0006')
		);
		return isset($w[$status]) ? $w[$status] : array('', '');
	}
}
#PO sesuai filter untuk tarikan Excel/PDF (dibatasi supaya tidak berat)
if (!function_exists('poDataList')) {
	function poDataList($maks = 3000)
	{
		global $dbname, $poTerpotong;
		$rows = fetchdata("select a.* from " . $dbname . ".log_poht a left join " . $dbname . ".log_podt b on a.nopo=b.nopo where 1=1 " . poScopeList() . poWhereList() . " group by a.nopo order by a.tanggal desc limit " . ((int)$maks + 1));
		$poTerpotong = count($rows) > $maks;
		return array_slice($rows, 0, $maks);
	}
}
#keterangan filter yang aktif untuk kop tarikan
if (!function_exists('poInfoFilter')) {
	function poInfoFilter()
	{
		global $dbname, $poTerpotong;
		$arrStatus = array('1' => 'Release', '2' => 'Unrelease', '3' => 'Become Out Standing', '4' => 'Close', '5' => 'Cancel');
		$info = array();
		$th = trim((string)checkPostGet('filterTahun', ''));
		$info[] = 'Tahun: ' . (preg_match('/^\d{4}$/', $th) ? $th : 'Semua');
		$st = (string)checkPostGet('filterId', '');
		$info[] = 'Status: ' . (isset($arrStatus[$st]) ? $arrStatus[$st] : 'Semua');
		$sp = trim((string)checkPostGet('filterSupplier', ''));
		if ($sp != '' and preg_match('/^[A-Za-z0-9_\-\.]+$/', $sp)) {
			$nmsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
			$info[] = 'Supplier: ' . (isset($nmsup[$sp]) ? trim($nmsup[$sp]) : $sp);
		}
		$v = trim((string)checkPostGet('crnopo', ''));
		if ($v != '') {
			$info[] = 'No. PO: ' . $v;
		}
		$v = trim((string)checkPostGet('txtsearch_nopp', ''));
		if ($v != '') {
			$info[] = 'No. PP: ' . $v;
		}
		if (poTglSql(checkPostGet('crtanggal', '')) != '') {
			$info[] = 'Tanggal PO: ' . trim(checkPostGet('crtanggal', ''));
		}
		if (poTglSql(checkPostGet('tglrilis_cari', '')) != '') {
			$info[] = 'Tanggal Rilis: ' . trim(checkPostGet('tglrilis_cari', ''));
		}
		if (!empty($poTerpotong)) {
			$info[] = 'Dibatasi 3000 data terbaru, persempit filter bila perlu';
		}
		return implode(' | ', $info);
	}
}
#nomor PP sebuah PO (teks biasa) dan syarat pembayaran
if (!function_exists('poNomorPp')) {
	function poNomorPp($nopo)
	{
		global $dbname, $owlPDO;
		$stmt = $owlPDO->prepare("select distinct nopp from " . $dbname . ".log_podt where nopo=?");
		$stmt->execute(array($nopo));
		return implode(', ', $stmt->fetchAll(PDO::FETCH_COLUMN));
	}
}
if (!function_exists('poSyaratBayar')) {
	function poSyaratBayar($kode)
	{
		global $dbname, $owlPDO;
		$stmt = $owlPDO->prepare("select jenis, keterangan from " . $dbname . ".log_5syaratbayar where kode=?");
		$stmt->execute(array($kode));
		$r = $stmt->fetch(PDO::FETCH_ASSOC);
		return $r ? $r['keterangan'] . ' (' . $r['jenis'] . ')' : '';
	}
}
#teks yang kepanjangan dipotong dan diberi "..." supaya tidak tumpang tindih ke kolom sebelah
if (!function_exists('poFit')) {
	function poFit($pdf, $txt, $w, $f = 7)
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
}
switch ($method) {

	case 'excellist':
		$rows = poDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$nmsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
		$kolom = 11;
		$stream = "<table>
			<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
			<tr><td colspan=" . $kolom . "><b>PO / SO</b></td></tr>
			<tr><td colspan=" . $kolom . ">" . htmlspecialchars(poInfoFilter()) . "</td></tr>
			<tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
			<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
			</table>";
		$stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
			<thead><tr>
				<th align=center bgcolor=#CCCCCC>No</th>
				<th align=center bgcolor=#CCCCCC>No. PO</th>
				<th align=center bgcolor=#CCCCCC>No. PP</th>
				<th align=center bgcolor=#CCCCCC>Supplier</th>
				<th align=center bgcolor=#CCCCCC>Tanggal PO</th>
				<th align=center bgcolor=#CCCCCC>Syarat Pembayaran</th>
				<th align=center bgcolor=#CCCCCC>Status</th>
				<th align=center bgcolor=#CCCCCC>PIC</th>
				<th align=center bgcolor=#CCCCCC>Keterangan</th>
				<th align=center bgcolor=#CCCCCC>Tanggal Rilis</th>
				<th align=center bgcolor=#CCCCCC>Tanggal Cetak</th>
			</tr></thead><tbody>";
		$no = 0;
		foreach ($rows as $val) {
			$no++;
			$stPo = poStatusInfo($val);
			$wrn = poWarnaStatus($stPo['postatus']);
			$tglrel = tanggalnormal($val['tglrelease']);
			$tglcetak = tanggalnormal($val['waktucetak']);
			$stream .= "<tr>
				<td align=center>" . $no . "</td>
				<td>" . htmlspecialchars($val['nopo']) . "</td>
				<td>" . htmlspecialchars(poNomorPp($val['nopo'])) . "</td>
				<td>" . htmlspecialchars(isset($nmsup[$val['kodesupplier']]) ? $nmsup[$val['kodesupplier']] : $val['kodesupplier']) . "</td>
				<td align=center>" . tanggalnormal($val['tanggal']) . "</td>
				<td>" . htmlspecialchars(poSyaratBayar($val['syaratbayar'])) . "</td>
				<td align=center" . ($wrn[0] != '' ? " bgcolor=" . $wrn[0] . " style='color:" . $wrn[1] . ";font-weight:bold'" : "") . ">" . htmlspecialchars($stPo['postatus']) . "</td>
				<td align=center>" . htmlspecialchars($stPo['pic']) . "</td>
				<td>" . nl2br(htmlspecialchars(poTeksDatar($stPo['keterangan'], "\n"))) . "</td>
				<td align=center>" . ($tglrel == '--' || $tglrel == '00-00-0000' ? '' : $tglrel) . "</td>
				<td align=center>" . ($tglcetak == '--' || $tglcetak == '00-00-0000' ? '' : $tglcetak) . "</td>
			</tr>";
		}
		if ($no == 0) {
			$stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
		}
		$stream .= "</tbody></table>";
		$xls = new HtmlExcel();
		$xls->addSheet("PO", $stream);
		$xls->headers("PO_" . date('YmdHis') . ".xls");
		echo $xls->buildFile();
		exit;

	case 'pdflist':
		$rows = poDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$nmsup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');
		$infoFilter = poInfoFilter();
		if (!class_exists('PDFListPO')) {
			class PDFListPO extends FPDF
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
					$this->Cell($width, 7, 'PO / SO', 0, 1, 'C');
					$this->SetFont('Arial', '', 8);
					$this->Cell($width, 5, utf8_decode($infoFilter), 0, 1, 'C');
					$this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
					$this->Ln(1);
					$this->SetFillColor(220, 220, 220);
					$this->SetFont('Arial', 'B', 8);
					$judul = array('No', 'No. PO', 'No. PP', 'Supplier', 'Tanggal', 'Status', 'PIC', 'Tgl Rilis', 'Keterangan');
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
		$pdf = new PDFListPO('L', 'mm', 'A4');
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(false, 16);
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$colFixed = array(8, 46, 40, 46, 18, 24, 26, 18); #semua kolom kecuali Keterangan
		$pdf->colW = array_merge($colFixed, array($width - array_sum($colFixed)));
		$pdf->AddPage();
		$h = 6;
		$no = 0;
		foreach ($rows as $val) {
			$no++;
			if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
				$pdf->AddPage();
			}
			$stPo = poStatusInfo($val);
			$c = $pdf->colW;
			$tglrel = tanggalnormal($val['tglrelease']);
			$pdf->SetFont('Arial', '', 7);
			$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
			$pdf->Cell($c[1], $h, poFit($pdf, $val['nopo'], $c[1]), 1, 0, 'L');
			$pdf->Cell($c[2], $h, poFit($pdf, poNomorPp($val['nopo']), $c[2]), 1, 0, 'L');
			$pdf->Cell($c[3], $h, poFit($pdf, isset($nmsup[$val['kodesupplier']]) ? $nmsup[$val['kodesupplier']] : $val['kodesupplier'], $c[3]), 1, 0, 'L');
			$pdf->Cell($c[4], $h, tanggalnormal($val['tanggal']), 1, 0, 'C');
			$wrn = poWarnaStatus($stPo['postatus']);
			if ($wrn[0] != '') {
				sscanf($wrn[0], '#%02x%02x%02x', $br, $bg, $bb);
				sscanf($wrn[1], '#%02x%02x%02x', $fr, $fg, $fb);
				$pdf->SetFillColor($br, $bg, $bb);
				$pdf->SetTextColor($fr, $fg, $fb);
			}
			$pdf->Cell($c[5], $h, poFit($pdf, $stPo['postatus'], $c[5]), 1, 0, 'L', $wrn[0] != '');
			$pdf->SetTextColor(0, 0, 0);
			$pdf->Cell($c[6], $h, poFit($pdf, $stPo['pic'], $c[6]), 1, 0, 'L');
			$pdf->Cell($c[7], $h, ($tglrel == '--' || $tglrel == '00-00-0000') ? '' : $tglrel, 1, 0, 'C');
			$pdf->Cell($c[8], $h, poFit($pdf, poTeksDatar($stPo['keterangan'], '; '), $c[8]), 1, 1, 'L');
		}
		if ($no == 0) {
			$pdf->Cell(array_sum($pdf->colW), $h, 'Data tidak ditemukan', 1, 1, 'C');
		}
		$pdf->Output();
		exit;

	case 'updatecetak':
		## Cek PO
		$str = "select * from " . $dbname . ".log_poht where nopo='" . $nopo . "'";
		$res = fetchdata($str);
		$strelease = $res[0]['stat_release'];
		$cetak = $res[0]['cetak'];
		$purchaser = $res[0]['purchaser'];

		if ($strelease == '1') {
			$waktucetak = "";
			if ($cetak == '0') {
				$waktucetak = ",waktucetak='" . date('Y-m-d H:i:s') . "'";
			}

			if ($_SESSION['standard']['userid'] == $purchaser) {
				#= tiap masuk ke sini update flag cetak
				$str = "update " . $dbname . ".log_poht set cetak=1 " . $waktucetak . " where nopo='" . $nopo . "'";
				try {
					$owlPDO->exec($str);
				} catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "\n";
					die();
				}
			}
		}
		break;

	case 'previewsj':
		echo "<table border=0 cellspacing=1 class=sortable>
				<thead>
				<tr class='rowcontent'>
					<td style='text-align:center'>No PO</td>
					<td style='text-align:center'>No SJ</td>
					<td style='text-align:center'>Action</td>
				</tr>
				</thead>
				<tbody>";
		$sDet = "select * from " . $dbname . ".log_po_sj where nopo='" . $nopo . "'";
		$qDet = $owlPDO->query($sDet) or die(print " Gagal: " . PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		$no = 0;
		while ($res = $qDet->fetch()) {

			echo "<tr class='rowcontent'>";
			echo "<td hidden id=idxz>" . $res['id'] . "</td>";
			echo "<td id=nopox>" . $res['nopo'] . "</td>";
			echo "<td id=nojsx>" . $res['nosj'] . "</td>";
			echo "<td><img src='images/application/application_delete.png' class='zImgBtn' title='Delete' onclick=deletenosjnopo()></td>";
			echo "</tr>";
		}

		echo "</tbody></table>";

		break;


	case 'tambahsj':
		if ($sjx == '') {
			exit('Error : No. SJ is Empty');
		}
		$idx = str_replace('PO', 'SJ', $nopo);
		$query = selectQuery($dbname, "log_po_sj", "id");
		$id = fetchData($query);
		$maxid = 1;
		if (!empty($id)) {
			foreach ($id as $row) {
				$noid = substr($row['id'], -3);
				intval($noid) >= $maxid ? $maxid = intval($noid) : false;
			}
			$maxid++;
		}
		$konter = addZero($maxid, 3);
		$ids = $idx . '/' . $konter;

		$strCount = "select * from " . $dbname . ".log_po_sj where nopo='" . $nopo . "' and nosj='" . $sjx . "' ";
		$resCount = $owlPDO->query($strCount) or die(print " Gagal: " . PDOException::getMessage());
		$resCount->setFetchMode(PDO::FETCH_ASSOC);
		$jumlahx = owlBaris($resCount);
		if ($jumlahx > 0) {
			exit('Error : nosj already exist for this po');
		}

		$str = "insert into " . $dbname . ".log_po_sj (id,nopo,nosj) values ('" . $ids . "','" . $nopo . "','" . $sjx . "')";

		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		break;

	case 'deletenosjnopo':

		$str = "delete from " . $dbname . ".log_po_sj where id='" . $idxz . "'";
		//exit('Error : '.$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}

		break;

	case 'loaddata':
		$tab = "";

		$limit = 25;
		$page = 0;
		if (isset($pages)) {
			$page = $pages;
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;

		$where = poWhereList() . poScopeList();

		$strCount = "select count(distinct a.nopo) as jmlhrow from " . $dbname . ".log_poht a left join " . $dbname . ".log_podt b on a.nopo=b.nopo where 1=1 " . $where;
		$str = "select * from " . $dbname . ".log_poht a left join " . $dbname . ".log_podt b on a.nopo=b.nopo where 1=1 " . $where . " group by a.nopo order by tanggal desc limit " . $offset . "," . $limit . "";

		$resCount = $owlPDO->query($strCount) or die(print " Gagal: " . PDOException::getMessage());
		$resCount->setFetchMode(PDO::FETCH_ASSOC);
		while ($barCount = $resCount->fetch()) {
			$jlhbrs = $barCount['jmlhrow'];
		}

		$no = 0;
		$no = $maxdisplay; #exit("error".$str);
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no += 1;
			$stdt = "select * from " . $dbname . ".log_transaksidt where nopo='" . $bar['nopo'] . "'";
			$qtdt = $owlPDO->query($stdt) or die(print " Gagal: " . PDOException::getMessage());
			$numrowtdt = owlBaris($qtdt);

			$skeu = "select * from " . $dbname . ".keu_tagihanht where nopo='" . $bar['nopo'] . "'";
			$qkeu = $owlPDO->query($skeu) or die(print " Gagal: " . PDOException::getMessage());
			$numrowkeu = owlBaris($qkeu);

			$sSyp = "select kode,jenis,keterangan from " . $dbname . ".log_5syaratbayar where kode='" . $bar['syaratbayar'] . "'";
			$qSyp = $owlPDO->query($sSyp) or die(print " Gagal: " . PDOException::getMessage());
			$qSyp->setFetchMode(PDO::FETCH_ASSOC);
			$rSyp = $qSyp->fetch();

			if ($bar['stat_release'] == 0) {
				$stat_po = $_SESSION['lang']['un_release_po'];
				if (($res->hasilpersetujuan1 == "2") || ($res->hasilpersetujuan2 == "2")) {
					$stat_po = "<a href=# onclick=getKoreksi('" . $res->nopo . "')>" . $_SESSION['lang']['ditolak'] . "</a>";
				}
			} elseif ($bar['stat_release'] == 1) {
				$stat_po = $_SESSION['lang']['release_po'];
			}

			$bgcolor = "";
			if ($bar['stat_release'] == 2) {
				$bgcolor = "bgcolor='orange' onclick=getKoreksi('" . $res->nopo . "')";
			}

			$stInfo = poStatusInfo($bar);
			$pic = $stInfo['pic'];
			$postatus = $stInfo['postatus'];
			$keterangan = $stInfo['keterangan'];

			#periksa chat
			$strChat = "select *  from " . $dbname . ".log_po_chat where nopo='" . $bar['nopo'] . "'";
			$resChat = $owlPDO->query($strChat) or die(print " Gagal: " . PDOException::getMessage());
			if (owlBaris($resChat) > 0) {
				$ingChat = "<img src='images/chat1.png' onclick=\"loadPOChat('" . $bar['nopo'] . "',event);\" class=zImgBtn>";
			} else {
				$ingChat = "<img src='images/chat0.png'  onclick=\"loadPOChat('" . $bar['nopo'] . "',event);\" class=zImgBtn>";
			}

			$strx = "select a.nomor from " . $dbname . ".log_permintaanhargadt a left join " . $dbname . ".log_perintaanhargaht b on a.nomor=b.nomor where a.norph='" . $bar['nodph'] . "' and b.supplierid='" . $bar['kodesupplier'] . "'";
			$resx = fetchData($strx);
			$nomordph = $resx[0]['nomor'];

			$optNmSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $bar['kodesupplier'] . "'");
			$tab .= "<tr class=rowcontent>
				<td " . $bgcolor . "  align=center>" . $no . "</td>
				<td " . $bgcolor . " style='white-space:nowrap;min-width:190px'>" . $bar['nopo'] . "</td>";
			$tab .= "<td " . $bgcolor . " style='min-width:230px'>
				<ul style='margin:0;padding:0;list-style:none'>";
			$str_nopp = "select distinct nopp from " . $dbname . ".log_podt where nopo='" . $bar['nopo'] . "' ";
			$res_nopp = $owlPDO->query($str_nopp) or die(print " Gagal: " . PDOException::getMessage());
			$res_nopp->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar_nopp = $res_nopp->fetch()) {
				$tab .= "<li style='display:flex;align-items:center;justify-content:space-between;gap:6px;white-space:nowrap;padding:1px 0'><span style='cursor:pointer' onclick=\"previewDetail2('" . $bar_nopp['nopp'] . "','',event)\" title='Detail PR/SR'>" . $bar_nopp['nopp'] . "</span><img src=images/pdf.jpg class=zImgBtn title='Print' onclick=\"masterPDF('log_prapoht','" . $bar_nopp['nopp'] . "','','log_slave_print_log_pp',event);\"></li>";
			}

			$tab .= "</ul>";

			$tab .= "</td>";

			$tab .= "<td " . $bgcolor . " ><img onclick=\"previewlinkpemenang('" . $bar['nodph'] . "', '" . $bar['kodesupplier'] . "', 'Detail Riwayat Perbandingan Harga' ,event)\" class='zImgBtn' src='images/zoom.png' title='Detail'></td>
				<td " . $bgcolor . " >" . $optNmSup[$bar['kodesupplier']] . "</td>
				<td " . $bgcolor . " style='text-align:center;min-width:70px;'>" . tanggalnormal($bar['tanggal']) . "</td>
				<td " . $bgcolor . " >" . $rSyp['keterangan'] . " (" . $rSyp['jenis'] . ")</td>
				<td " . $bgcolor . " align=center>" . $ingChat . "</td>
				";
			##BEGIN APPROVAL##
			$countApp = getCountApproval($jenisApp, '');
			for ($i = 1; $i <= $countApp; $i++) {
				$strx = "select * from " . $dbname . ".setup_approval where jenispersetujuan='" . $jenisApp . "' and level='" . $i . "' and kodeunit='" . $bar['kodeunit'] . "'";
				$resx = fetchData($strx);
				$tipeapp = $resx[0]['tipe'];
				$departemenapp = $resx[0]['departemen'];
				$tipekaryawanapp = $resx[0]['tipekaryawan'];
				$jabatanapp = $resx[0]['jabatan'];

				$arrDetail = detailApprove($i, $bar['nopo'], $jenisApp);
				if ($tipeapp == '1' && $arrDetail['status'] != '') {
					if ($arrDetail['status'] != '1') {
						if ($departemenapp != '') {
							$opttipe = makeOption($dbname, 'sdm_5departemen', 'kode,nama', "kode='" . $departemenapp . "'");
							$arrDetail['nama'] = $opttipe[$departemenapp];
						}

						if ($tipekaryawanapp != '') {
							$opttipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe', "id='" . $tipekaryawanapp . "'");
							$arrDetail['nama'] = $opttipe[$tipekaryawanapp];
						}

						if ($jabatanapp != '0') {
							$opttipe = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan', "kodejabatan='" . $jabatanapp . "'");
							$arrDetail['nama'] = $opttipe[$jabatanapp];
						}
					}
				}
				$bgApp = ($arrDetail['nama'] != '') ? poWarnaApproval($arrDetail['status']) : '';
				$tab .= "<td align=center style='vertical-align:top" . ($bgApp != '' ? ";background-color:" . $bgApp : "") . "'>
					<b>" . $arrDetail['nama'] . "</b>";
				if ($arrDetail['nama'] != '') {
					$tab .= "<br>Status : " . (($arrDetail['status'] == '9' || $arrDetail['status'] == '') ? "" : $arrDetail['namastatus']);
					if ($arrDetail['komentar'] != '') {
						$tab .= "<br>Comment : " . $arrDetail['komentar'];
					}
				}
				$tab .= "</td>";
			}
			##END APPROVAL##


			## Cek Termin ##
			$qTermin = "select * from " . $dbname . ".log_potermin where nopo='" . $bar['nopo'] . "'";
			$resTermin = fetchData($qTermin);

			$termin = array();
			foreach ($resTermin as $key => $terminnya) {
				$termin[$terminnya['nopo']]['termin'] .= "Termin Ke : " . $terminnya['termin'] . " 
					" . (strlen($terminnya['persen']) > 1 ? $terminnya['persen'] : "&nbsp;&nbsp;" . $terminnya['persen']) . "% Rp. " . number_format($terminnya['rupiah']) . "<br/>";
				// $termin[$terminnya['nopo']]['persen'] .= $terminnya['persen']."%<br/>";
				// $termin[$terminnya['nopo']]['rupiah'] .= $terminnya['rupiah']."<br/>";
			}

			// echo "<pre>";
			// print_r($termin);

			$wrn = poWarnaStatus($postatus);
			$ketHtml = "";
			foreach (preg_split('/<br\s*\/?>/i', (string)$keterangan) as $ln) {
				$ln = trim(preg_replace('/^-\s*/', '', trim($ln)));
				if ($ln == '' or $ln == 'Sudah ada penerimaan') {
					continue;
				}
				$ketHtml .= "<div style='margin-bottom:4px;line-height:1.35'>" . $ln . "</div>";
			}
			$tab .= "<td " . $bgcolor . " style='text-align:center" . ($wrn[0] != '' ? ";background-color:" . $wrn[0] . ";color:" . $wrn[1] . ";font-weight:bold" : "") . "'>" . $postatus . "</td>
				<td " . $bgcolor . " style='max-width:200px'>" . $pic . "</td>
				<td " . $bgcolor . " width=200px>" . $termin[$bar['nopo']]['termin'] . "</td>
				<td " . $bgcolor . " style='min-width:200px;vertical-align:top;text-align:center'>" . $ketHtml . "</td>
				<td align=center>";
			//<td ".$bgcolor." >".$stat_po."</td>

			$file = '';
			#cek apakah ada file
			if (@$res->filememo != '') {
				$file = "<img src=images/onebit_02.png title='View Memo' class=zImgBtn onclick=\"lihatfile('" . $res->filememo . "','event')\">";

				if ($res->stat_release == 0) {
					$file .= "<img src=images/application/application_delete_lama.png title='Delete Memo' class=zImgBtn onclick=deletefile('" . $res->nopo . "')>";
				}
			}
			if ($bar['closed'] == 0) {
				if (($bar['purchaser'] == $_SESSION['standard']['userid']) || ($_SESSION['empl']['kodejabatan'] == '5')) {
					if ($bar['statuspo'] == 0 && $numrowtdt == 0 && $numrowkeu == 0) {

						// if ($bar['pph'] > 0) {
						// 	$bar['pph'] = $bar['pph'];
						// } else {
						// 	$bar['pph'] = $bar['pph22'];
						// }

						$tab .= "<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"fillField('" . $bar['nopo'] . "','" . tanggalnormal($bar['tanggal']) . "','" . $bar['kodesupplier'] . "','" . $bar['subtotal'] . "','" . $bar['diskonpersen'] . "','" . $bar['pbbkb'] . "','" . $bar['pphfinal'] . "','" . $bar['pph'] . "','" . $bar['chkppn'] . "','" . $bar['ppn'] . "','" . $bar['nilaipo'] . "','" . $bar['nilaidiskon'] . "','" . $stat . "','" . tanggalnormal($bar['tanggalkirim']) . "','" . $bar['matauang'] . "','" . $bar['kurs'] . "','" . $bar['idFranco'] . "','" . $bar['addcost'] . "','" . $bar['deliverytime'] . "','" . $bar['norefrensi'] . "','" . $bar['persenppn'] . "','" . $bar['persenpph'] . "','".$bar['pph22']."');\">
							<img src=images/icons/04/16/01.png class=zImgBtn  title='ajukan' onclick=\"ajukan('" . $bar['nopo'] . "');\" >";
					}
				}
			}
			if ($bar['stat_release'] == 1) {
				// $tab.=" <img src=images/pdf.jpg class=zImgBtn title='Riwayat Perbandingan Harga' onclick=\"masterPDF('log_poht','".$bar['nopo']."','','log_slave_print_permintaan_penawaran_new2',event);\"> ".$file;	
				$tab .= " <img src=images/pdf.jpg class=zImgBtn  title='PO/SO' onclick=\"masterPDF('log_poht','" . $bar['nopo'] . "','','log_slave_print_detail_po',event);updatecetak('" . $bar['nopo'] . "');\"> " . $file;
			}
			$tab .= "</td>";



			$tab .= "<td " . $bgcolor . " align=center style='min-width:70px'>" . (tanggalnormal($bar['tglrelease']) == '--' ? '' : tanggalnormal($bar['tglrelease'])) . "</td>";

			$wktcetak = tanggalnormal($bar['waktucetak']);
			$wktcetak = ($wktcetak == '00-00-0000' ? '' : $wktcetak);
			$tab .= "<td " . $bgcolor . " align=center style='min-width:70px'>" . @$wktcetak . "</td>";
			$tab .= "</tr>";
		}

		$tab .= "<input type=hidden id=nopp_" . $no . " name=nopp_" . $no . " value='' />";

		$totrows = ceil($jlhbrs / $limit);
		if ($totrows == 0) {
			$totrows = 1;
		}

		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
		}

		$frompage = (($page * $limit) + 1);
		if ((($page + 1) * $limit) > $jlhbrs) {
			$topage = $jlhbrs;
		} else {
			$topage = (($page + 1) * $limit);
		}
		$tab .= "</tr>";
		$tabx .= "<tr>
			<td colspan='" . (getCountApproval($jenisApp, '') + 15) . "' align=center>
				" . $frompage . " to " . $topage . " Of " .  $jlhbrs . "
			</td>
		</tr>
		<tr>
			<td colspan='" . (getCountApproval($jenisApp, '') + 15) . "' align=center>";

		if ($page == '0') {
			$tabx .= "";
		} else {
			$tabx .= "<button class=mybutton onclick=load_new_data(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
		}

		$tabx .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";

		if (($page + 1) == $totrows) {
			$tabx .= "";
		} else {
			$tabx .= "<button class=mybutton onclick=load_new_data(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
		}
		$tabx .= "</td></tr>";
		//$tab.="</tbody> </table>";

		echo $tab . "####" . $tabx;
		break;

	case 'edit_po':
		$_SESSION['sorefrensi'] = array();
		$str = "select * from " . $dbname . ".log_sorefrensi where noso='" . $nopo . "'";
		$res = fetchdata($str);
		foreach ($res as $key => $val) {
			// $newdata = array('nopo'=>$val['nopo'],'kodebarang'=>$val['kodebarang'],'namabarang'=>$val['namabarang'],'jumlah'=>$val['jumlah'],'jumlahmax'=>$val['jumlah']);
			$newdata = array('nopo' => $val['nopo'], 'nopp' => $val['nopp'], 'kodebarang' => $val['kodebarang'], 'namabarang' => $val['namabarang'], 'jumlah' => $val['jumlah'], 'jumlahmax' => $val['jumlah'], 'hargasatuan' => $val['hargasatuan'], 'total' => $val['hargasatuan'] * $val['jumlah']);
			array_push($_SESSION['sorefrensi'], $newdata);
		}

		$_SESSION['somaterial'] = array();
		$str = "select * from " . $dbname . ".log_somaterial where nopo='" . $nopo . "'";
		$res = fetchdata($str);
		foreach ($res as $key => $val) {
			$newdata = array('namabarang' => $val['namabarang'], 'jumlah' => $val['jumlah'], 'hargasatuan' => $val['harga']);
			array_push($_SESSION['somaterial'], $newdata);
		}

		$query = "select * from " . $dbname . ".log_podt where nopo='" . $_POST['nopo'] . "'";
		$data = fetchData($query);
		createTabEditDetail($_POST['nopo'], $data);
		break;

	case 'getpoheader':
		$str = "select nilaipo,nopo,npwporg,alamatsup,rekening,npwpsup,idFranco,idFrancoinvc,syaratbayar,deliverytime,uraian from " . $dbname . ".log_poht where nopo='" . $nopo . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$npwporg = $bar['npwporg'];
		$alamatsup = $bar['alamatsup'];
		$rekening = $bar['rekening'];
		$npwpsup = $bar['npwpsup'];
		$idFranco = $bar['idFranco'];
		$syaratbayar = $bar['syaratbayar'];
		$deliverytime = $bar['deliverytime'];
		$keterangan = $bar['uraian'];
		$nilaipo = $bar['nilaipo'];
		$idFrancoinvc = $bar['idFrancoinvc'];

		//NPWP ORGANISASI (PERUSAHAAN)
		$str = "select npwp from " . $dbname . ".setup_org_npwp where kodeorg='" . $expnopo[5] . "' order by npwp desc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($npwporg == $bar['npwp']) {
				$optNpwpPerusahaan .= "<option value='" . $bar['npwp'] . "' selected>" . $bar['npwp'] . "</option>";
			} else {
				$optNpwpPerusahaan .= "<option value='" . $bar['npwp'] . "'>" . $bar['npwp'] . "</option>";
			}
		}

		// sub kelompok
		$optsubkelompok = "";
		$namakelompoksup = makeOption($dbname, 'log_5klsupplier', 'tipe,kode');
		$sql1 = "select * from " . $dbname . ".log_5supkelompok where supplierid='" . $supplierid . "'";
		$res1 = $owlPDO->query($sql1) or die(print " Gagal: " . PDOException::getMessage());
		$res1->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar1 = $res1->fetch()) {
			$optsubkelompok .= "<option value='" . $bar1['tipe'] . "'>" . $namakelompoksup[$bar1['tipe']] . "</option>";
		}
		//MATAUANG
		$optMataUang = "<option value='" . $matauang . "'>" . $matauang . "</option>";

		//SUPPLIER
		$optSup = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $supplierid . "'");
		$optSupplier = "<option value='" . $supplierid . "'>" . $optSup[$supplierid] . "</option>";

		//Alamat Supplier
		$str = "select id_alamat,alamat,kota from " . $dbname . ".log_5supalamat where supplierid='" . $supplierid . "' order by id_alamat desc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($alamatsup == $bar['id_alamat']) {
				$optAlamatSup .= "<option value='" . $bar['id_alamat'] . "' selected>" . $bar['alamat'] . "," . $bar['kota'] . "</option>";
			} else {
				$optAlamatSup .= "<option value='" . $bar['id_alamat'] . "'>" . $bar['alamat'] . "," . $bar['kota'] . "</option>";
			}
		}

		//NPWP Supplier
		// $optSupNpwp="<option value='' selected>".$_SESSION['lang']['pilihdata']."</option>";
		$str = "select npwp from " . $dbname . ".log_5supnpwp where supplierid='" . $supplierid . "' order by npwp desc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($npwpsup == $bar['npwp']) {
				$optSupNpwp .= "<option value='" . $bar['npwp'] . "' selected>" . $bar['npwp'] . "</option>";
			} else {
				$optSupNpwp .= "<option value='" . $bar['npwp'] . "'>" . $bar['npwp'] . "</option>";
			}
		}

		//Rekening Supplier
		$str = "select bank,rekening,an from " . $dbname . ".log_5rekbank where supplierid='" . $supplierid . "' and isactive='1' order by rekening desc";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($rekening == $bar['rekening']) {
				$optRekening .= "<option value='" . $bar['rekening'] . "' selected>" . $bar['rekening'] . "-" . $bar['bank'] . "-" . $bar['an'] . "</option>";
			} else {
				$optRekening .= "<option value='" . $bar['rekening'] . "'>" . $bar['rekening'] . "-" . $bar['bank'] . "-" . $bar['an'] . "</option>";
			}
		}

		//Franco
		$str = "select id_franco,franco_name from " . $dbname . ".setup_franco";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optFrancoinvc .= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		while ($bar = $res->fetch()) {
			if ($bar['id_franco'] == $idFranco) {
				$optFranco = "<option value='" . $bar['id_franco'] . "'>" . $bar['franco_name'] . "</option>";
			}
			if ($bar['id_franco'] == $idFrancoinvc) {
				$optFrancoinvc .= "<option value='" . $bar['id_franco'] . "' selected>" . $bar['franco_name'] . "</option>";
			} else {
				$optFrancoinvc .= "<option value='" . $bar['id_franco'] . "'>" . $bar['franco_name'] . "</option>";
			}
		}

		//Syarat Bayar
		$str = "select kode,jenis,keterangan from " . $dbname . ".log_5syaratbayar where kode='" . $syaratbayar . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optSyaratBayar = "<option value='" . $bar['kode'] . "'>" . $bar['keterangan'] . " (" . $bar['jenis'] . ")</option>";
		}

		$optBlank = "<option value=''></option>";
		$showtermin = "display:none;";
		// if(substr($expnopo[3],0,2) == 'SO'){
		if (strpos($nopo, "SO") !== false) {
			$showtermin = "";
		}

		// cek nilai
		$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='NILAIVPOAP' ";
		$res = fetchdata($str);
		$nilai_appHO = $res[0]['nilai'];

		$induk_unit = makeOption($dbname, "organisasi", "kodeorganisasi,induk");

		if (strpos($nopo, "-HO") !== false) {
			$str_k = "SELECT kodeorganisasi FROM " . $dbname . ".organisasi WHERE tipe = 'HOLDING' and induk in (SELECT induk FROM " . $dbname . ".organisasi WHERE kodeorganisasi = '" . $expnopo[4] . "') ";
			$res_k = fetchData($str_k);
			$kodeorganisasi_app = $res_k[0]['kodeorganisasi'];
			$countListApproval = getCountApproval('PO', $kodeorganisasi_app);
		} else {

			// CEK DULU APAKAH MELEBIH 10JUTA
			if ($nilaipo >= $nilai_appHO and $expnopo[4] == 'PPPE') {
				// ambil unit HO dari PT tersebut
				$str = "select kodeorganisasi from " . $dbname . ".organisasi where tipe='HOLDING' and induk='" . $induk_unit[$expnopo[4]] . "'  ";
				$res = fetchdata($str);
				$unit_HO = $res[0]['kodeorganisasi'];
				$countListApproval = getCountApproval('PO', $unit_HO);
				$namaorg_unit = makeOption($dbname, "organisasi", "kodeorganisasi,namaorganisasi");
				if ($countListApproval <= 0) {
					exit("warning : Untuk Unit " . $namaorg_unit[$unit_HO] . " jenis persetujuan RFQ belum di setupkan.... ");
				}

				$i = 0;
				$arrList = listNextApprove($i, 'PO', $unit_HO, $nilaipo);

				if (count($arrList) == 0) {
					exit("warning : Belum ada setup approval Unit " . $namaorg_unit[$unit_HO] . " dengan range nilai " . number_format($nilaipo) . " ");
				} else {
					if ($arrList[0]['level'] != '1') {
						exit("warning : Belum ada setup approval Unit " . $namaorg_unit[$unit_HO] . " dengan range nilai " . number_format($nilaipo) . " Pada Level 1");
					}
				}
			} else {
				$countListApproval = getCountApproval('PO', $expnopo[4]);
				$i = 0;
				$arrList = listNextApprove($i, 'PO', $expnopo[4], $nilaipo);

				if (count($arrList) == 0) {
					exit("warning : Belum ada setup approval Unit " . $namaorg_unit[$expnopo[4]] . " dengan range nilai " . number_format($nilaipo) . " ");
				} else {
					if ($arrList[0]['level'] != '1') {
						exit("warning : Belum ada setup approval Unit " . $namaorg_unit[$expnopo[4]] . " dengan range nilai " . number_format($nilaipo) . " Pada Level 1");
					}
				}
			}
		}

		## NO Referensi
		// $showApprove.="<table id='tblreferensi' style='display:none'>
		$showApprove .= "<table id='tblreferensi' style='" . $showtermin . "width:100%;margin-bottom:12px'>
			<tr>
				<td style='width:140px;vertical-align:top;padding-top:4px'>" . $_SESSION['lang']['noreferensi'] . "</td>
				<td style='width:10px;vertical-align:top;padding-top:4px'>:</td>
				<td>
					<table cellspacing=1 class=sortable style='width:100%'>
						<thead>
						<tr class=rowheader style='text-align:center'>
						<td>" . $_SESSION['lang']['nopo'] . "</td>
						<td>" . $_SESSION['lang']['nopp'] . "</td>
						<td>" . $_SESSION['lang']['kodebarang'] . "</td>
						<td>" . $_SESSION['lang']['namabarang'] . "</td>
						<td>" . $_SESSION['lang']['jumlah'] . "</td>
						<td>" . $_SESSION['lang']['hargasatuan'] . "</td>
						<td>" . $_SESSION['lang']['total'] . "</td>
						<td>" . $_SESSION['lang']['action'] . "</td>
						</tr>
						</thead>
						<tbody id='refrensidt'>
						</tbody>
						<tbody>
						<tr>
							<td colspan=8 style='text-align:center;font-weight:bold'>
								<label style='cursor:pointer;color:#F9CC9D' onclick='carinoref(event)'>Cari No. PO</label>
							</td>
						</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>";

		$showApprove .= "<table id='tbltermin' style='width:100%;margin-bottom:12px'>
			<tr>
				<td style='width:140px;vertical-align:top;padding-top:4px'>Termin Pembayaran</td>
				<td style='width:10px;vertical-align:top;padding-top:4px'>:</td>
				<td>
					<table cellspacing=1 class=sortable style='width:100%'>
						<thead>
						<tr class=rowheader style='text-align:center'>
							<td style='width:60px'>" . $_SESSION['lang']['termin'] . "</td>
							<td style='width:90px'>" . $_SESSION['lang']['nilai'] . " (%)</td>
							<td>" . $_SESSION['lang']['nilai'] . "</td>
							<td>" . $_SESSION['lang']['action'] . "</td>
						</tr>
						</thead>
						<tbody id='termindt'>
						</tbody>
						<tbody>
						<tr>
							<td></td>
							<td>
								<input type='number' min='1' max='100' class=myinputtextnumber onkeypress='return angka_doang(event)' placeholder='0' style=\"width:100%;box-sizing:border-box;\" value='' id=persentermin onblur=\"getnilaitermin()\" onkeyup=\"z.numberFormat('persentermin',2);getnilaitermin()\">
							</td>
							<td>
								<input type=text class=myinputtextnumber onkeypress='return angka_doang(event)' placeholder='0' style=\"width:100%;box-sizing:border-box;\" value='' id=nilaitermin onblur=\"getpersentermin()\"  onkeyup=\"z.numberFormat('nilaitermin',2);getpersentermin()\">
							</td>
							<td style='text-align:center'>
								<img onclick=\"tambahtermin()\" class='zImgBtn' src='images/addplus.png' title='Add'>
							</td>
						</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</table>";
		$showApprove .= "<table id='tblpersetujuan' style='width:100%'>";
		$arrDetail = array();
		for ($i = 1; $i <= $countListApproval; $i++) {

			if (strpos($nopo, "-HO") !== false) {
				$str_k = "SELECT kodeorganisasi FROM " . $dbname . ".organisasi WHERE tipe = 'HOLDING' and induk in (SELECT induk FROM " . $dbname . ".organisasi WHERE kodeorganisasi = '" . $expnopo[4] . "') ";
				$res_k = fetchData($str_k);
				$kodeorganisasi_app = $res_k[0]['kodeorganisasi'];
				$arrList = listApprove($i, 'PO', $kodeorganisasi_app);
			} else {
				if ($nilaipo >= $nilai_appHO and $expnopo[4] == 'PPPE') {
					$arrList = listApprove($i, 'PO', $unit_HO);
				} else {
					$x = $i - 1;
					$arrList = listNextApprove($x, 'PO', $expnopo[4], $nilaipo);
				}
			}



			$optPersetujuan = "";
			$arrDetail = detailApprove($i, $nopo, 'PO');
			foreach ($arrList as $key => $val) {
				if ($arrDetail['karyawanid'] == $val['karyawanid']) {
					$optPersetujuan .= "<option value='" . $val['karyawanid'] . "' selected>" . $val['nama'] . "</option>";
				} else {
					$optPersetujuan .= "<option value='" . $val['karyawanid'] . "'>" . $val['nama'] . "</option>";
				}
			}
			$showApprove .= "<tr>";
			$showApprove .= "<td style='width:140px'>Persetujuan " . $i . "</td>";
			$showApprove .= "<td style='width:10px'>:</td>";
			$showApprove .= "<td>
				<select class=select2po style='width:300px' id='persetujuan_" . $i . "'>" . $optPersetujuan . "</select>
			</td>";
			$showApprove .= "</tr>";
		}
		$showApprove .= "</table>";

		//Get Waktu Penyerahan
		$str = "select *  from " . $dbname . ".log_5delivtime";
		$optWaktuPenyerahan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($deliverytime == $bar['kode']) {
				$optWaktuPenyerahan .= "<option value='" . $bar['kode'] . "' selected>" . $bar['nama'] . "</option>";
			} else {
				$optWaktuPenyerahan .= "<option value='" . $bar['kode'] . "'>" . $bar['nama'] . "</option>";
			}
		}

		//Get Surat Jalan
		$str = "select *  from " . $dbname . ".log_suratjalanht";
		$optSuratJalan = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {

			$optSupx = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $bar['expeditor'] . "'");
			$optSuratJalan .= "<option value='" . $bar['nosj'] . "' selected>" . $bar['nosj'] . " [" . $optSupx[$bar['expeditor']] . "]</option>";
		}

		## GET TERMIN PEMBAYARAN
		$_SESSION['potermin'] = array();
		$str = "select * from " . $dbname . ".log_potermin where nopo='" . $nopo . "'";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$newdata = array('termin' => $val['termin'], 'persen' => $val['persen'], 'nilai' => $val['rupiah']);
			array_push($_SESSION['potermin'], $newdata);
		}

		echo ($optNpwpPerusahaan == '' ? $optBlank : $optNpwpPerusahaan) . "####" .
			$optMataUang . "####" . $optSupplier . "####" . ($optAlamatSup == '' ? $optBlank : $optAlamatSup) . "####" . ($optRekening == '' ? $optBlank : $optRekening) . "####" .
			($optSupNpwp == '' ? $optBlank : $optSupNpwp) . "####" . $optFranco . "####" . $optSyaratBayar . "####" . $showApprove . "####" . $optWaktuPenyerahan . "####" . $optSuratJalan . "####" .
			$keterangan . "####" . $optFrancoinvc . "####" . $optsubkelompok;
		break;

	case 'save_headher':
		$tglpo = checkPostGet('tglpo', '');
		$npwporg = checkPostGet('npwporg', '');
		$mtUang = checkPostGet('mtUang', '');
		$Kurs = checkPostGet('Kurs', '');
		$supplier_id = checkPostGet('supplier_id', '');
		$alamat_sup = checkPostGet('alamat_sup', '');
		$npwp = checkPostGet('npwp', '');
		$rek = checkPostGet('rek', '');
		$delivtime = checkPostGet('delivtime', '');
		$lok_kirim = checkPostGet('lok_kirim', '');
		$lok_invc = checkPostGet('lok_invc', '');
		$cara_pembayarn = checkPostGet('cara_pembayarn', '');
		$noreferensix = checkPostGet('noreferensix', '');
		$ketUraian = checkPostGet('ketUraian', '');
		$purchaser_id = checkPostGet('purchaser_id', '');
		$subtot = checkPostGet('subtot', '');
		$angDiskon = checkPostGet('angDiskon', '');
		$diskon = checkPostGet('diskon', '');
		$pbbkb = checkPostGet('pbbkb', '');
		$pphfinal = checkPostGet('pphfinal', '');
		$ppn = checkPostGet('ppn', '');
		$pph = checkPostGet('pph', '');
		$pph22 = checkPostGet('pph22', '');
		$chkppn = checkPostGet('chkppn', '');
		$addcost = checkPostGet('addcost', '');
		$grand_total = checkPostGet('grand_total', '');
		$hslPPn = checkPostGet('hslPPn', '');
		$hslPPh = checkPostGet('hslPPh', '');
		$subkelompok = checkPostGet('subkelompok', '');
		// $countpersetujuan=checkPostGet('countpersetujuan','');
		$notecost = checkPostGet('notecost', '');
		$countpersetujuan = getCountApproval('PO', $expnopo[4], '', '', '', $grand_total);
		$tglSkrng = date("Y-m-d");

		if ($npwporg == '') {
			exit("warning: NPWP Perusahaan belum dipilih");
		}

		if ($alamat_sup == '') {
			exit("warning: Alamat supplier belum dipilih");
		}

		if ($npwp == '') {
			exit("warning: NPWP Supplier belum dipilih");
		}

		if ($rek == '') {
			exit("warning: Rekening bank supplier belum dipilih");
		}

		if ($delivtime == '') {
			exit("warning: Waktu penyerahan belum dipilih");
		}

		if ($lok_invc == '') {
			exit("warning: Lokasi pengiriman tagihan belum dipilih");
		}

		if (count($_SESSION['potermin']) > 0) {
			$totpersen = 0;
			$totnilai = 0;
			foreach ($_SESSION['potermin'] as $key => $val) {
				$totpersen += $val['persen'];
				$totnilai += $val['nilai'];
			}
			if ($totpersen != 100) {
				if ($totnilai != str_replace(',', '', $subtot)) // antisipasi pembulatan, total persen 99.98 tapi nilai sudah 100%
					exit("Gagal, Jumlah Persen termin harus 100%.");
			}
			// $subtot= str_replace(',','',$subtot);
			// echo $totnilai == $subtotal ? "benar" : "salah <br/>";
			// echo $totnilai."<br/>";
			// echo $subtotal;
			## Fungsi bccomp (by Abdul) - Mbak Ella
			## Compares the num1 to the num2 and returns the result as an integer.
			// $compareData = var_dump( bccomp($totnilai, $subtot)==0 );

			#= Buat perbandingan, untuk ubah nilainya menjadi int dengan int
			if ((int)$totnilai != (int)str_replace(',', '', $subtot)) {
				// if($compareData){
				exit("Gagal, Jumlah Nilai termin harus " . (int)str_replace(',', '', $subtot) . "." . (int)$totnilai);
				// exit("Gagal, Jumlah Nilai termin harus || Total nilainya : ".var_dump($totnilai). "|| Sub totalnya : ".str_replace(',','',var_dump(floatval($subtot))));
			}
		}

		if ($countpersetujuan <= 0) {
			exit("warning: Persetujuan belum disetup. Silahkan hubungi administrator.");
		}

		#semua pilihan penyetuju dicek dulu sebelum approval lama dihapus
		for ($i = 1; $i <= $countpersetujuan; $i++) {
			if ($_POST['persetujuan' . $i] == '') {
				exit("warning: Persetujuan " . $i . " belum dipilih.");
			}
		}

		//cek matauang dan kurs
		if ($mtUang != 'IDR') {
			$sGetKurs = "select distinct kurs,kode from " . $dbname . ".setup_matauangrate where kode='" . $mtUang . "' and daritanggal='" . $tglpo . "' order by daritanggal desc";
			$qGetKurs = $owlPDO->query($sGetKurs) or die(print " Gagal: " . PDOException::getMessage());
			$qGetKurs->setFetchMode(PDO::FETCH_ASSOC);
			$rGetKurs = $qGetKurs->fetch();
			if ($Kurs < $rGetKurs['kurs']) {
				exit("Error: Please provide curs corrensponding to currency, curs for " . $rGetKurs['kode'] . " :" . $rGetKurs['kurs']);
			}
		} else {
			$Kurs = 1;
		}

		#PO yang sudah release tidak boleh diubah
		$scek = "select stat_release from " . $dbname . ".log_poht where nopo='" . $nopo . "'";
		$qcek = $owlPDO->query($scek) or die(print " Gagal: " . PDOException::getMessage());
		$qcek->setFetchMode(PDO::FETCH_ASSOC);
		$rcek = $qcek->fetch();
		if ($rcek['stat_release'] == 1) {
			exit("warning : PO : " . $nopo . " has been released");
		}

		#semua perubahan data PO dalam satu transaksi: bila gagal, approval lama tidak ikut hilang
		$ketUraian = substr($owlPDO->quote($ketUraian), 1, -1);
		$notecost = substr($owlPDO->quote($notecost), 1, -1);
		$owlPDO->beginTransaction();
		try {
		$str = "delete from " . $dbname . ".approval where notransaksi='" . $nopo . "' and jenispersetujuan='PO'";
		$owlPDO->exec($str);

		for ($i = 1; $i <= $countpersetujuan; $i++) {
			if ($_POST['persetujuan' . $i] == '') {
				exit("warning: Persetujuan " . $i . " belum dipilih.");
			} else {
				if (strpos($nopo, "-HO") !== false) {
					$str_k = "SELECT kodeorganisasi FROM " . $dbname . ".organisasi WHERE tipe = 'HOLDING' and induk in (SELECT induk FROM " . $dbname . ".organisasi WHERE kodeorganisasi = '" . $expnopo[4] . "') ";
					$res_k = fetchData($str_k);
					$kodeorganisasi_app = $res_k[0]['kodeorganisasi'];
					$arrList = listApprove($i, 'PO', $kodeorganisasi_app);
				} else {
					$arrList = listApprove($i, 'PO', $expnopo[4]);
				}
				$str = "select * from " . $dbname . ".setup_approval where jenispersetujuan='PO' and level='" . $i . "' and kodeunit='" . $expnopo[4] . "'";
				$res = fetchData($str);
				$tipeapp = $res[0]['tipe'];
				$departemenapp = $res[0]['departemen'];
				$tipekaryawanapp = $res[0]['tipekaryawan'];
				$jabatanapp = $res[0]['jabatan'];

				if ($tipeapp == '1') {
					if ($departemenapp != '') {
						$str = "select * from " . $dbname . ".datakaryawan where bagian='" . $departemenapp . "'";
						$res = fetchdata($str);
						foreach ($res as $key => $val) {
							$str = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('" . $nopo . "','PO','" . $i . "','" . $val['karyawanid'] . "','9')";
							$owlPDO->exec($str);
						}
					}
					if ($tipekaryawanapp != '') {
						$str = "select * from " . $dbname . ".datakaryawan where tipekaryawan='" . $tipekaryawanapp . "'";
						$res = fetchdata($str);
						foreach ($res as $key => $val) {
							$str = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('" . $nopo . "','PO','" . $i . "','" . $val['karyawanid'] . "','9')";
							$owlPDO->exec($str);
						}
					}
					if ($jabatanapp != '0') {
						$str = "select * from " . $dbname . ".datakaryawan where kodejabatan='" . $jabatanapp . "'";
						$res = fetchdata($str);
						foreach ($res as $key => $val) {
							$str = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('" . $nopo . "','PO','" . $i . "','" . $val['karyawanid'] . "','9')";
							$owlPDO->exec($str);
						}
					}
				} else {
					$str = "select count(notransaksi) as jlhtrk from " . $dbname . ".approval where notransaksi='" . $nopo . "' and jenispersetujuan='PO' and level='" . $i . "'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar = $res->fetch();
					if ($bar['jlhtrk'] <= 0) {
						$str = "insert into " . $dbname . ".approval (notransaksi,jenispersetujuan,level,karyawanid,status) values ('" . $nopo . "','PO','" . $i . "','" . $_POST['persetujuan' . $i] . "','9')";
					} else {
						$str = "update " . $dbname . ".approval set karyawanid='" . $_POST['persetujuan' . $i] . "',status='9' where notransaksi='" . $nopo . "' and level='" . $i . "' and jenispersetujuan='PO'";
					}

					try {
						$owlPDO->exec($str);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n";
						if ($owlPDO->inTransaction()) {
							$owlPDO->rollBack();
						}
						die();
					}
				}
			}
		}

		foreach ($_POST['kdbrg'] as $row => $isi) {
			$kdbrg = $isi;
			$nopp = $_POST['nopp'][$row];
			$jmlh_pesan = $_POST['rjmlh_psn'][$row];
			$hrg_satuan = $_POST['rhrg_sat'][$row];
			$spk = $_POST['rspk'][$row];
			$hrg_sblmdiskon = str_replace(',', '', $hrg_satuan);

			$_POST['rmat_uang'][$row] = "IDR";
			$_POST['rongank'][$row] = 0;
			$diskon = ($hrg_sblmdiskon * $disc) / 100;
			$hrg_diskon = $hrg_sblmdiskon - $diskon;
			$mat_uang = $_POST['rmat_uang'][$row];
			$satuan = $_POST['rsatuan_unit'][$row];
			$spekBrg = $_POST['spekBrg'][$row];
			$rongank = str_replace(',', '', $_POST['rongank'][$row]);
			$rongank == '' ? $rongank = 0 : $rongank = $rongank;
			$hrgSat = $hrg_diskon + $rongank;

			$setLokasi = '';
			if ($lok_kirim != '') {
				$setLokasi = (intval($lok_kirim) ? "`idFranco`" : "`lokasipengiriman`") . "='" . $lok_kirim . "',";
			}

			$strx = "update " . $dbname . ".log_poht set 
				tanggal='" . tanggalsystem($tglpo) . "',
				tgledit='" . $tglSkrng . "',
				kodesupplier='" . $supplier_id . "',
				subtotal='" . $subtot . "',
				chkppn='" . $chkppn . "',
				ppn='" . str_replace(',', '', $hslPPn) . "',
				pph='" . str_replace(',', '', $pph) . "',
				pph22='" . str_replace(',', '', $pph22) . "',
				persenppn='" . $ppn . "',
				persenpph='" . $hslPPh . "',
				nilaipo='" . $grand_total . "',
				uraian='" . $ketUraian . "',
				" . $setLokasi . "
				addcost='" . $addcost . "',
				idFrancoinvc='" . $lok_invc . "',
				notecost='" . $notecost . "',
				rekening='" . $rek . "',
				alamatsup='" . $alamat_sup . "',
				npwpsup='" . $npwp . "',
				npwporg='" . $npwporg . "',
				deliverytime='" . $delivtime . "',
				norefrensi='" . $noreferensix . "',
				tipesub='" . $subkelompok . "'
				where nopo='" . $nopo . "'";

			try {
				$owlPDO->exec($strx);
				foreach ($_POST['kdbrg'] as $row => $isi) {
					$kdbrg = $isi;
					$nopp = $_POST['nopp'][$row];
					$jmlh_pesan = $_POST['rjmlh_psn'][$row];
					$merk = $_POST['merk'][$row];
					$hrg_satuan = $_POST['rhrg_sat'][$row];

					$hrg_sblmdiskon = str_replace(',', '', $hrg_satuan);
					$diskon = ($hrg_sblmdiskon * $disc) / 100;
					$hrg_diskon = $hrg_sblmdiskon - $diskon;
					$mat_uang = $_POST['rmat_uang'][$row];
					$satuan = $_POST['rsatuan_unit'][$row];
					$spekBrg = substr($owlPDO->quote($_POST['spekBrg'][$row]), 1, -1);
					$spk = $_POST['rspk'][$row];
					$rongank = str_replace(',', '', $_POST['rongank'][$row]);
					$hrgSat = $hrg_diskon + @($rongank / $jmlh_pesan);

					$tharga = $hrg_sblmdiskon * $jmlh_pesan;

					@$proppbbkb = (($tharga / $sub_total * ($pbbkb + $sub_total)) / $jmlh_pesan) - $hrg_sblmdiskon;
					@$propaddcost = (($tharga / $sub_total * ($addcost + $sub_total)) / $jmlh_pesan) - $hrg_sblmdiskon;

					$hargasatbaru = $hrg_sblmdiskon - $diskon + $proppbbkb + $propaddcost;

					$sql = "update " . $dbname . ".log_podt set 
						catatan='" . $spekBrg . "',
						idmerk='" . $merk . "',
						spk='" . $spk . "' 
						where nopo='" . $nopo . "' and kodebarang='" . $kdbrg . "' and nopp='" . $nopp . "'";

					try {
						$owlPDO->exec($sql);
						$sUpdate = "update " . $dbname . ".log_prapodt set create_po=1 where nopp='" . $_POST['nopp'][$row] . "' and kodebarang='" . $isi . "'";
						try {
							$owlPDO->exec($sUpdate);
						} catch (PDOException $e) {
							print " Gagal  !: " . $e->getMessage() . "\n";
							if ($owlPDO->inTransaction()) {
								$owlPDO->rollBack();
							}
							die();
						}
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n";
						if ($owlPDO->inTransaction()) {
							$owlPDO->rollBack();
						}
						die();
					}
				}
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n";
				if ($owlPDO->inTransaction()) {
					$owlPDO->rollBack();
				}
				die();
			}
		}

		$str = "delete from " . $dbname . ".log_potermin where nopo='" . $nopo . "'";
		$owlPDO->exec($str);
		if (count($_SESSION['potermin']) > 0) {
			$no = 0;
			foreach ($_SESSION['potermin'] as $key => $val) {
				$no++;
				$rptermin = 0;
				$rptermin = (($val['nilai'] / 100) * $subtot);
				$str = "insert into " . $dbname . ".log_potermin (nopo,termin,persen,rupiah) values ('" . $nopo . "','" . $val['termin'] . "','" . $val['persen'] . "','" . $val['nilai'] . "')";
				$owlPDO->exec($str);
			}
		}

		// $str="delete from ".$dbname.".log_sorefrensi where noso='".$nopo."'";
		// $owlPDO->exec($str);
		// if(count($_SESSION['sorefrensi']) > 0){			
		// 	foreach($_SESSION['sorefrensi'] as $key=>$val){
		// 		$str="insert into ".$dbname.".log_sorefrensi (noso,nopo,kodebarang,namabarang,jumlah) values ('".$nopo."','".$val['nopo']."','".$val['kodebarang']."','".$val['namabarang']."','".$val['jumlah']."')";
		// 		$owlPDO->exec($str);
		// 	}
		// }
		$str = "delete from " . $dbname . ".log_sorefrensi where noso='" . $nopo . "'";
		$owlPDO->exec($str);
		if (count($_SESSION['sorefrensi']) > 0) {

			// ambil grandtotal sorefrensi
			foreach ($_SESSION['sorefrensi'] as $key => $val) {
				$grand_total_sorefrensix += $val['jumlah'] * $val['hargasatuan'];
			}

			foreach ($_SESSION['sorefrensi'] as $key => $val) {
				// nilai proporsi
				// $nilai_proporsix = ($val['hargasatuan']*$val['jumlah'])/$grand_total_sorefrensix*$grand_total;
				// $nilai_proporsix = ($val['hargasatuan']*$val['jumlah'])/$grand_total_sorefrensix*($grand_total);
				$nilai_proporsix = ($val['hargasatuan'] * $val['jumlah']) / $grand_total_sorefrensix * ($subtot);
				// bulat 2
				$nilai_proporsix = round($nilai_proporsix, 2);
				$str = "insert into " . $dbname . ".log_sorefrensi (noso,nopo,nopp,kodebarang,namabarang,jumlah,hargasatuan,total,nilai_proporsi) values ('" . $nopo . "','" . $val['nopo'] . "','" . $val['nopp'] . "','" . $val['kodebarang'] . "','" . $val['namabarang'] . "','" . $val['jumlah'] . "','" . $val['hargasatuan'] . "','" . $val['hargasatuan'] * $val['jumlah'] . "','" . $nilai_proporsix . "')";
				$owlPDO->exec($str);

				$ongkos_angkutx = ($nilai_proporsix / $val['jumlah']);
				// bulat 2
				$ongkos_angkutx = round($ongkos_angkutx, 2);
				// update onkos angkot PO
				$str = "update " . $dbname . ".log_podt set ongkangkut='" . $ongkos_angkutx . "' where nopo='" . $val['nopo'] . "' and kodebarang='" . $val['kodebarang'] . "' ";
				$owlPDO->exec($str);
			}
		}

		$str = "delete from " . $dbname . ".log_somaterial where nopo='" . $nopo . "'";
		$owlPDO->exec($str);
		if (count($_SESSION['somaterial']) > 0) {
			foreach ($_SESSION['somaterial'] as $key => $val) {
				$str = "insert into " . $dbname . ".log_somaterial (nopo,namabarang,jumlah,harga) values ('" . $nopo . "','" . $val['namabarang'] . "','" . $val['jumlah'] . "','" . $val['hargasatuan'] . "')";
				$owlPDO->exec($str);
			}
		}

			$owlPDO->commit();
		} catch (PDOException $e) {
			if ($owlPDO->inTransaction()) {
				$owlPDO->rollBack();
			}
			print " Gagal  !: " . $e->getMessage() . "\n";
			if ($owlPDO->inTransaction()) {
				$owlPDO->rollBack();
			}
			die();
		}

		$_SESSION['potermin'] = array();
		$_SESSION['sorefrensi'] = array();
		$_SESSION['somaterial'] = array();
		break;

	case 'ajukan':
		$errShow = "";

		$str = "select * from " . $dbname . ".log_poht where nopo='" . $nopo . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		if ($bar['npwporg'] == '') {
			$errShow .= "- NPWP Organisasi belum dipilih\n";
		}

		if ($bar['alamatsup'] == '') {
			$errShow .= "- Alamat Supplier belum dipilih\n";
		}

		if ($bar['npwpsup'] == '') {
			$errShow .= "- NPWP Supplier belum dipilih\n";
		}

		if ($bar['rekening'] == '') {
			$errShow .= "- Rekening Supplier belum dipilih\n";
		}

		if ($bar['deliverytime'] == '') {
			$errShow .= "- Waktu Penyerahan belum dipilih\n";
		}

		if ($bar['idFrancoinvc'] == '') {
			$errShow .= "- Lokasi Pengiriman Invoice belum dipilih\n";
		}

		if (strpos($nopo, "-HO") !== false) {
			$str_k = "SELECT kodeorganisasi FROM " . $dbname . ".organisasi WHERE tipe = 'HOLDING' and induk in (SELECT induk FROM " . $dbname . ".organisasi WHERE kodeorganisasi = '" . $expnopo[4] . "') ";
			$res_k = fetchData($str_k);
			$kodeorganisasi_app = $res_k[0]['kodeorganisasi'];
			$countListApproval = getCountApproval('PO', $kodeorganisasi_app);
		} else {
			$countListApproval = getCountApproval('PO', $expnopo[4]);
		}

		// $countListApproval = getCountApproval('PO',$expnopo[4]);
		$arrDetail = array();

		if ($countListApproval <= 0) {
			exit("warning: Persetujuan belum disetup. Silahkan hubungi administrator.");
		}

		for ($i = 1; $i <= $countListApproval; $i++) {
			$arrDetail = detailApprove($i, $nopo, 'PO');
			if ($arrDetail['karyawanid'] == '') {
				$errShow .= "- Persetujuan " . $i . " belum dipilih\n";
			}
		}

		if ($errShow != "") {
			exit("warning: \n" . $errShow);
		}

		$tglpo = $bar['tanggal'];
		$purchaser = getNamaKaryawan($bar['purchaser']);
		$namasupplier = getNamaSupplier($bar['kodesupplier']);

		// $str = "update ".$dbname.".log_poht set statuspo='1',tanggal='".date('Y-m-d')."' where nopo='".$nopo."'";
		$str = "update " . $dbname . ".log_poht set statuspo='1' where nopo='" . $nopo . "'";
		$owlPDO->beginTransaction();
		try {
			$owlPDO->exec($str);

			$arrDetail = array();
			for ($i = 1; $i <= $countListApproval; $i++) {
				$arrDetail = detailApprove($i, $nopo, 'PO');
				$namaorganisasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $expnopo[4] . "'");

				$str = "update " . $dbname . ".approval set status='0' where notransaksi='" . $nopo . "'";
				$owlPDO->exec($str);

				if ($i == 1) {
					$str = "select karyawanid from " . $dbname . ".approval where notransaksi='" . $nopo . "' and level='" . $i . "'";
					$res = fetchdata($str);
					foreach ($res as $key => $val) {
						##send an email to incharge person
						// notifemailpo($nopo,'1',$val['karyawanid']);
					}
				}
			}
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollBack();
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;

	case 'loadtermin':
		$tab = "";
		foreach ($_SESSION['potermin'] as $key => $row) {
			$tab .= "<tr class='rowcontent'>
				<td class='termin' value='" . $row['termin'] . "' style='text-align:center'>" . $row['termin'] . "</td>
				<td class='persentermin' value='" . $row['persen'] . "' style='text-align:center'>" . hidezerodecimal($row['persen'], 2) . "</td>
				<td class='nilaitermin' value='" . $row['nilai'] . "' style='text-align:center'>" . hidezerodecimal($row['nilai'], 2) . "</td>
				<td style='text-align:center'>
					<img onclick=\"deletetermin('" . $row['termin'] . "')\" class='zImgBtn' src='images/delete1.png' title='Hapus' style='cursor:pointer'>
				</td>
			</tr>";
		}

		echo $tab;
		break;

	case 'tambahtermin':
		if ($persentermin <= 0) {
			exit("Gagal, Persen termin harus lebih besar dari 0");
		}
		$maxtermin = 0;
		$totnilai = 0;
		foreach ($_SESSION['potermin'] as $key => $row) {
			if ($row['termin'] > $maxtermin) {
				$maxtermin = $row['termin'];
			}
			$totnilai += $row['persen'];
		}
		$maxtermin = $maxtermin + 1;
		$totnilai = $totnilai + $persentermin;
		if ($totnilai > 100) {
			exit("Gagal, Jumlah nilai termin tidak bisa lebih dari 100%.\nNilai = " . $totnilai . "%");
		}

		$newdata = array('termin' => $maxtermin, 'persen' => $persentermin, 'nilai' => round($nilaitermin, 2));
		array_push($_SESSION['potermin'], $newdata);
		break;

	case 'deletetermin':
		$arrtermin = array();
		$notermin = 0;
		foreach ($_SESSION['potermin'] as $key => $row) {
			if ($row['termin'] == $termin) {
				unset($_SESSION['potermin'][$key]);
			}
			if ($row['termin'] > $termin) {
				$newdata = array('termin' => ($row['termin'] - 1), 'persen' => $row['persen'], 'nilai' => $row['nilai']);
				array_push($arrtermin, $newdata);
			}

			if ($row['termin'] < $termin) {
				$newdata = array('termin' => ($row['termin']), 'persen' => $row['persen'], 'nilai' => $row['nilai']);
				array_push($arrtermin, $newdata);
			}
		}

		$_SESSION['potermin'] = array();
		foreach ($arrtermin as $key => $row) {
			$newdata = array('termin' => ($row['termin']), 'persen' => $row['persen'], 'nilai' => $row['nilai']);
			array_push($_SESSION['potermin'], $newdata);
		}
		break;

	case 'caripodt':
		if (strlen($nopo) < 3) {
			exit("Warning : Jumlah karakter pencarian minimal 3 karakter.");
		}

		$nou = 0;
		$datanopoS = "''";
		foreach ($_SESSION['sorefrensi'] as $key => $row) {
			if ($nou == 0) {
				$datanopoS = "'" . $row['nopo'] . "'";
			} else {
				$datanopoS .= ",'" . $row['nopo'] . "'";
			}
			$nou++;
		}

		$arrhasil = array();
		// $str="select a.nopo,a.jumlahpesan,b.jumlah from ".$dbname.".log_podt a left join ".$dbname.".log_sorefrensi b on a.nopo=b.nopo and a.kodebarang=b.kodebarang where a.nopo like '%PO%' and a.nopo like '%".$nopo."%' order by a.nopo desc";
		$str = "select a.nopo,a.jumlahpesan,b.jumlah,a.hargasatuan,a.kodebarang,a.nopp from " . $dbname . ".log_podt a 
		left join " . $dbname . ".log_sorefrensi b on a.nopo=b.nopo and a.kodebarang=b.kodebarang 
		left join " . $dbname . ".log_poht e on a.nopo=e.nopo
		where  a.nopo like '%" . $nopo . "%' and (a.nopo like '%PO%' or a.nopo like '%NO%')
		AND NOT EXISTS (
			SELECT *
			FROM " . $dbname . ".log_sorefrensi d
			WHERE a.nopo = d.nopo
		  ) 
		AND a.nopo NOT IN (" . $datanopoS . ")
		AND e.stat_release='1' 
		AND a.nopo NOT IN (SELECT nopo FROM " . $dbname . ".log_transaksi_vw)
		AND e.tipepo='PO'
		order by a.nopo desc";
		$res = fetchdata($str);
		foreach ($res as $key => $val) {
			if ($val['jumlah'] == '') {
				$val['jumlah'] = 0;
			}
			$hasil = $val['jumlahpesan'] - $val['jumlah'];
			if ($hasil > 0) {
				if (count($_SESSION['sorefrensi']) > 0) {
					foreach ($_SESSION['sorefrensi'] as $keyx => $rowx) {
						if ($rowx['nopo'] == $val['nopo']) {
						} else {
							$arrhasil[$val['nopo']] = $val['nopo'];
						}
					}
				} else {
					$arrhasil[$val['nopo']] = $val['nopo'];
				}
			}
		}

		if (count($arrhasil) <= 0) {
			$tab .= $_SESSION['lang']['errdatanotexist'];
		} else {
			$tab .= "<table cellspacing='1' border='0' class='sortable'>
				<thead>
					<tr class=rowheader>
						<td align=center>No. PO</td>
					</tr>
				</thead>
				<tbody>";
			foreach ($arrhasil as $val) {
				$tab .= "<tr class='rowcontent'>
					<td style='cursor:pointer' title='Klik untuk memilih No. PO' onclick=\"enterpo('" . $val . "')\">" . $val . "</td>
				</tr>";
			}
			$tab .= "</tbody>
			</table>";
		}
		echo $tab;
		break;

	case 'enterpo':
		$str = "select a.nopo,a.nopp,a.kodebarang,a.jumlahpesan,b.jumlah,a.hargasatuan from " . $dbname . ".log_podt a left join " . $dbname . ".log_sorefrensi b on a.nopo=b.nopo and a.kodebarang=b.kodebarang where a.nopo='" . $nopo . "' order by a.nopo desc";
		$res = fetchdata($str);
		foreach ($res as $key => $val) {
			if ($val['jumlah'] == '') {
				$val['jumlah'] = 0;
			}
			$hasil = $val['jumlahpesan'] - $val['jumlah'];
			if ($hasil > 0) {
				$optnmbarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $val['kodebarang'] . "'");
				// $newdata = array('nopo'=>$val['nopo'],'kodebarang'=>$val['kodebarang'],'namabarang'=>$optnmbarang[$val['kodebarang']],'jumlah'=>$hasil,'jumlahmax'=>$hasil);
				$newdata = array('nopo' => $val['nopo'], 'nopp' => $val['nopp'], 'kodebarang' => $val['kodebarang'], 'namabarang' => $optnmbarang[$val['kodebarang']], 'jumlah' => $hasil, 'jumlahmax' => $hasil, 'hargasatuan' => $val['hargasatuan'], 'total' => $val['hargasatuan'] * $val['jumlah']);
				array_push($_SESSION['sorefrensi'], $newdata);
			}
		}
		break;

	case 'loadrefrensi':
		// $tab="";
		// foreach($_SESSION['sorefrensi'] as $key=>$row){
		// 	$tab.="<tr class='rowcontent'>
		// 		<td style='text-align:center'>".$row['nopo']."</td>
		// 		<td style='text-align:center'>".$row['kodebarang']."</td>
		// 		<td style='text-align:left'>".$row['namabarang']."</td>
		// 		<td style='text-align:center'>
		// 			<input type='number' id='jumlah_".$row['nopo']."_".$row['kodebarang']."' class='myinputtextnumber' onkeypress='return angka_doang(event)' value='".$row['jumlah']."' style='width:60px;' onkeyup=\"updatesorefrensi('".$row['nopo']."','".$row['kodebarang']."','".$row['jumlahmax']."',this.value)\">
		// 		</td>
		// 		<td style='text-align:center'>
		// 			<img onclick=\"deletesorefrensi('".$row['nopo']."','".$row['kodebarang']."')\" class='zImgBtn' src='images/delete1.png' title='Hapus' style='cursor:pointer'>
		// 		</td>
		// 	</tr>";
		// }
		$tab = "";
		// inii
		// echo "<pre>";
		// print_r($_SESSION['sorefrensi']);
		// exit("warning :a");
		foreach ($_SESSION['sorefrensi'] as $key => $row) {
			// this is bb
			$tab .= "<tr class='rowcontent'>
				<td style='text-align:center'>" . $row['nopo'] . "</td>
				<td style='text-align:center'>" . $row['nopp'] . "</td>
				<td style='text-align:center'>" . $row['kodebarang'] . "</td>
				<td style='text-align:left'>" . $row['namabarang'] . "</td>
				<td style='text-align:center'>
					<input id='jumlah_" . $row['nopo'] . "_" . $row['kodebarang'] . "' class='myinputtextnumber' onkeypress='return angka_doang(event)' value='" . $row['jumlah'] . "' style='width:60px;' onblur=\"updatesorefrensi('" . $row['nopo'] . "','" . $row['kodebarang'] . "','" . $row['jumlahmax'] . "','" . $val['jumlah'] . "','" . $row['hargasatuan'] . "',this.value)\">
				</td>
				<td style='text-align:center'>
					<input type='text' disabled id='harga_" . $row['nopo'] . "_" . $row['kodebarang'] . "' class='myinputtextnumber' onkeypress='return angka_doang(event)' value='" . number_format($row['hargasatuan'], 2) . "' style='width:80px;'>
				</td>
				<td style='text-align:center'>
					<input type='text' disabled id='total_" . $row['nopo'] . "_" . $row['kodebarang'] . "' class='myinputtextnumber' onkeypress='return angka_doang(event)' value='" . number_format($row['jumlah'] * $row['hargasatuan'], 2) . "' style='width:80px;'>
				</td>
				<td style='text-align:center'>
					<img onclick=\"deletesorefrensi('" . $row['nopo'] . "','" . $row['kodebarang'] . "')\" class='resicon' src='images/delete1.png' title='Hapus' style='cursor:pointer'>
				</td>
			</tr>";
			$grandtotal_sorefrensi += $row['jumlah'] * $row['hargasatuan'];
		}

		$tab .= "
		<tr style='color:#A5C1D6 !important;background-color:#275270;text-align:center;text-transform: capitalize;'>
			<td colspan=4>Grand Total</td>
			<td colspan=4>" . number_format($grandtotal_sorefrensi, 2) . "</td>
		</tr>
		";

		echo $tab;
		break;


	case 'deletesorefrensi':
		foreach ($_SESSION['sorefrensi'] as $key => $row) {
			if ($row['nopo'] == $nopo && $row['kodebarang'] == $kodebarang) {
				unset($_SESSION['sorefrensi'][$key]);
			}
		}
		break;

	case 'updatesorefrensi':
		$jumlah = checkPostGet('jumlah', '');
		$hargasatuan = checkPostGet('hargasatuan', '');
		foreach ($_SESSION['sorefrensi'] as $key => $row) {
			if ($row['nopo'] == $nopo && $row['kodebarang'] == $kodebarang) {
				$_SESSION['sorefrensi'][$key]['jumlah'] = $jumlah;
				$_SESSION['sorefrensi'][$key]['total'] = $jumlah * $hargasatuan;
			}
		}
		// foreach($_SESSION['sorefrensi'] as $key=>$row){
		// 	if($row['nopo'] == $nopo && $row['kodebarang']==$kodebarang){
		// 		$_SESSION['sorefrensi'][$key]['jumlah']=$jumlah;
		// 	}
		// }
		break;

	case 'addmaterialso':
		$namabarangso = checkPostGet('namabarangso', '');
		$jlhpesanso = checkPostGet('jlhpesanso', '');
		$hargasatuanso = checkPostGet('hargasatuanso', '');
		$jlhpesanso = str_replace(',', '', $jlhpesanso);
		$hargasatuanso = str_replace(',', '', $hargasatuanso);

		if ($namabarangso == '') {
			exit("Warning : Nama Barang harus diisi.");
		}
		if ($jlhpesanso == '' || $jlhpesanso == 0) {
			exit("Warning : Jumlah Dipesan harus diisi dan lebih besar dari 0.");
		}
		if ($hargasatuanso == '' || $hargasatuanso == 0) {
			exit("Warning : Harga Satuan harus diisi dan lebih besar dari 0.");
		}

		$newdata = array('namabarang' => $namabarangso, 'jumlah' => $jlhpesanso, 'hargasatuan' => $hargasatuanso);
		array_push($_SESSION['somaterial'], $newdata);
		break;

	case 'loadmaterialso':
		$tab = "";
		$no = 0;
		$grdtotal = 0;
		foreach ($_SESSION['somaterial'] as $key => $row) {
			$no++;
			$subttl = $row['jumlah'] * $row['hargasatuan'];
			$tab .= "<tr class='rowcontent'>
				<td></td>
				<td style='text-align:right'>" . $no . ".</td>
				<td>
					<input class='myinputtext' type='text' onkeypress=\"return tanpa_kutip(event)\" style='width:385px' value='" . $row['namabarang'] . "' disabled>
				</td>
				<td colspan=3></td>
				<td>
					<input class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" type='text' style='width:60px;' onblur=\"display_number_so('jmlhDimintaso','1')\" onkeyup=\"calculateso('0')\" value='" . hidezerodecimal($row['jumlah'], 2) . "' disabled>
				</td>
				<td></td>
				<td>
					<input class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" type='text' style='width:100px' onkeyup=\"calculateso('0')\" onblur=\"display_number_so('harga_satuan_so','2')\" value='" . number_format($row['hargasatuan'], 2) . "' disabled>
				</td>
				<td>
					<input class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" type='text' style='width:100px' disabled='disabled' value='" . number_format($subttl, 2) . "'>
				</td>
				<!--<td>
					<img onclick=\"deletesomaterial('" . $row['namabarang'] . "')\" class='zImgBtn' src='images/delete1.png' title='Hapus' style='cursor:pointer'>
				</td>-->
			</tr>";
			$grdtotal += $subttl;
		}

		$totalpo = 0;
		// $str="select (jumlahpesan*hargasatuan) as total from ".$dbname.".log_podt where nopo='".$nopo."'";
		#= kasus kejadian PO no 004/01/2022/SO/KSPM/KSP, ada diskon, dan harga satuan sudah potong diskon, jadi diganti harga satuan menjadi harga sebelum diskon
		$str = "select (jumlahpesan*hargasbldiskon) as total from " . $dbname . ".log_podt where nopo='" . $nopo . "'";
		$res = fetchdata($str);
		foreach ($res as $val) {
			$totalpo += $val['total'];
		}
		$hasiltotal = $grdtotal + $totalpo;

		echo $tab . "####" . number_format($hasiltotal, 2);
		break;

	case 'deletesomaterial':
		$namabarangso = checkPostGet('namabarang', '');
		foreach ($_SESSION['somaterial'] as $key => $row) {
			if ($row['namabarang'] == $namabarangso) {
				unset($_SESSION['somaterial'][$key]);
			}
		}
		break;
}

function createTabEditDetail($id, $data)
{
	global $conn;
	global $dbname;
	global $owlPDO;
	global $expnopo;

	//  $table .= "<table class=sortable>";
	# Header
	$table = "<thead>";
	$table .= "<tr class=rowheader>";
	$table .= "<td align=center>" . $_SESSION['lang']['nopp'] . "</td>";
	$table .= "<td align=center width=50px>" . $_SESSION['lang']['kodebarang'] . "</td>";
	$table .= "<td align=center>" . $_SESSION['lang']['namabarang'] . "</td>";
	$table .= "<td align=center style='display:none'>SPK</td>";
	$table .= "<td align=center>Prioritas</td>";
	$table .= "<td align=center>" . $_SESSION['lang']['merk'] . "</td>";
	$table .= "<td align=center>" . $_SESSION['lang']['spesifikasi'] . "</td>";
	$table .= "<td align=center width=40px>" . $_SESSION['lang']['jmlhPesan'] . "</td>";
	$table .= "<td align=center>" . $_SESSION['lang']['satuan'] . "</td>";
	$table .= "<td style='display:none'>" . $_SESSION['lang']['ongkoskirim'] . "/Brg</td>";
	$table .= "<td align=center>" . $_SESSION['lang']['hargasatuan'] . "</td>";
	$table .= "<td align=center width=100px>" . $_SESSION['lang']['subtotal'] . "</td>";
	$table .= "<td align=center style='display:none'></td>";
	$table .= "</tr>";
	$table .= "</thead>";

	# Data
	$table .= "<tbody id='detailBody'>";

	$i = 0;
	$jmlhPesan = 0;

	#======= Display Data =======
	$ongkir = 0;
	if ($data != array()) {
		foreach ($data as $key => $row) {
			//Get Merk
			$optMerk = "<option value=''></option>";
			$str = "select b.idmerk,b.merk from " . $dbname . ".log_5merkbarangdt a 
			left join " . $dbname . ".log_5merkbaranght b on a.idmerk=b.idmerk where a.kodebarang='" . $row['kodebarang'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				if ($row['idmerk'] == $bar['idmerk']) {
					$optMerk .= "<option value='" . $bar['idmerk'] . "' selected>" . $bar['merk'] . "</option>";
				} else {
					$optMerk .= "<option value='" . $bar['idmerk'] . "'>" . $bar['merk'] . "</option>";
				}
			}

			//get satuan dan nama barang di log_5masterbarang
			$ql = "select satuan,namabarang from " . $dbname . ".`log_5masterbarang` where `kodebarang`='" . $row['kodebarang'] . "'";
			$qry = $owlPDO->query($ql) or die(print " Gagal: " . PDOException::getMessage());
			$qry->setFetchMode(PDO::FETCH_ASSOC);
			$res = $qry->fetch();


			//get satuan konversi di log_5stkonversi
			$sSat = "select satuan from " . $dbname . ".log_5masterbarang where kodebarang='" . $row['kodebarang'] . "'";
			$qSat = $owlPDO->query($sSat) or die(print " Gagal: " . PDOException::getMessage());
			$qSat->setFetchMode(PDO::FETCH_ASSOC);
			$rSat = $qSat->fetch();
			$optSatuan = "<option value=" . $rSat['satuan'] . ">" . $rSat['satuan'] . "</option>";
			$where = " kodebarang='" . $row['kodebarang'] . "' and darisatuan='" . $res['satuan'] . "'";

			$sSknv = "select satuankonversi from " . $dbname . ".log_5stkonversi where " . $where . "";
			$qSknv = $owlPDO->query($sSknv) or die(print " Gagal: " . PDOException::getMessage());
			$qSknv->setFetchMode(PDO::FETCH_ASSOC);
			while ($rSknv = $qSknv->fetch()) {
				$optSatuan .= "<option value=" . $rSknv['satuankonversi'] . ">" . $rSknv['satuankonversi'] . "</option>";
			}


			$optTest = makeOption($dbname, 'setup_matauang', 'kode,kodeiso');
			$sqpp = "select * from  " . $dbname . ".log_sudahpo_vsrealisasi_vw where nopp='" . $row['nopp'] . "' and kodebarang='" . $row['kodebarang'] . "'";
			$qpp = $owlPDO->query($sqpp) or die(print " Gagal: " . PDOException::getMessage());
			$qpp->setFetchMode(PDO::FETCH_ASSOC);
			$rpp = $qpp->fetch();
			$sub_tot = ($row['jumlahpesan'] * $row['hargasbldiskon']);
			$ongkir += $row['jumlahpesan'] * $row['ongkangkut'];
			$sub_tot_nor = ($row['jumlahpesan'] * $row['hargasbldiskon']) + ($row['jumlahpesan'] * $row['ongkangkut']);


			$sjmlh = "select sum(jumlahpesan) as jumlahPesan from " . $dbname . ".log_podt where kodebarang='" . $row['kodebarang'] . "' and nopp='" . $row['nopp'] . "'";
			$qjmlh = $owlPDO->query($sjmlh) or die(print " Gagal: " . PDOException::getMessage());
			$qjmlh->setFetchMode(PDO::FETCH_ASSOC);
			$resjmlh = $qjmlh->fetch();

			$sEdit = "select jumlahpesan,spk from " . $dbname . ".log_podt where nopo='" . $id . "' and kodebarang='" . $row['kodebarang'] . "' and nopp='" . $row['nopp'] . "'";
			$qEdit = $owlPDO->query($sEdit) or die(print " Gagal: " . PDOException::getMessage());
			$qEdit->setFetchMode(PDO::FETCH_ASSOC);
			$rEdit = $qEdit->fetch();
			$tmpil = ($rpp['realisasi'] - $resjmlh['jumlahPesan']) + $rEdit['jumlahpesan'];

			if ($rEdit['spk'] == '1') {
				$spkcheck = "Checked";
			} else {
				$spkcheck = "";
			}

			if ($row['harganormal'] == 0) {
				$row['harganormal'] = $row['hargasatuan'];
			}
			$sMtUang = "select matauang from " . $dbname . ".log_poht where nopo = '" . $id . "' ";
			$qMtUang = $owlPDO->query($sMtUang) or die(print " Gagal: " . PDOException::getMessage());
			$qMtUang->setFetchMode(PDO::FETCH_ASSOC);
			$rMtUang = $qMtUang->fetch();

			$strSat = "select satuanpp,satuankonversi,prioritas from " . $dbname . ".log_prapodt where nopp='" . $row['nopp'] . "' and kodebarang='" . $row['kodebarang'] . "'";
			$qrySat = $owlPDO->query($strSat) or die(print " Gagal: " . PDOException::getMessage());
			$qrySat->setFetchMode(PDO::FETCH_ASSOC);
			$resSat = $qrySat->fetch();
			if ($resSat['satuankonversi'] == '' || is_null($resSat['satuankonversi'])) {
				$mySatuan = "<option value='" . $resSat['satuanpp'] . "'>" . $resSat['satuanpp'] . "</option>";
			} else {
				$mySatuan = "<option value='" . $resSat['satuankonversi'] . "'>" . $resSat['satuankonversi'] . "</option>";
			}

			$table .= "<tr id='detail_tr_" . $key . "' class='rowcontent'>";
			$table .= "<td id='dtNopp_" . $key . "'>" . makeElement(
				"rnopp_" . $key . "",
				'txt',
				$row['nopp'],
				array('style' => 'width:120px', 'disabled' => 'disabled')
			) . "</td>";
			$table .= "<td id='dtKdbrg_" . $key . "'>" . makeElement(
				"rkdbrg_" . $key . "",
				'txt',
				$row['kodebarang'],
				array('style' => 'width:60px', 'disabled' => 'disabled')
			) . "</td>";
			$table .= "<td>" . makeElement(
				"nm_brg_" . $key . "",
				'txt',
				$res['namabarang'],
				array('style' => 'width:385px', 'disabled' => 'disabled')
			) . "</td>";
			$table .= "<td style='display:none'>
				<input type='checkbox' name='spk' id=spk_" . $key . " " . $spkcheck . ">
			</td>";
			$table .= "<td><input class=myinputtext style='width:30px;text-align:center' id=\"prioritas_" . $key . "\" cols=\"25\" value='" . $resSat['prioritas'] . "' disabled></td>";
			$table .= "<td><select id=merk_" . $key . " style='width:70px'>" . $optMerk . "</td>";
			$table .= "<td><textarea style='width:300px' id=\"spek_brg_" . $key . "\">" . $row['catatan'] . "</textarea></td>";
			$table .= "<td>" . makeElement(
				"jmlhDiminta_" . $key . "",
				'textnum',
				$row['jumlahpesan'],
				array('style' => 'width:60px;d', 'disabled' => 'disabled', 'onkeypress' => 'return angka_doang(event)', 'onblur' => "display_number('" . $key . "')", 'onkeyup' => "calculate('" . $key . "')")
			) . "</td>";
			$table .= "<td><select id=sat_" . $key . " style='width:70px' disabled>" . $mySatuan . "</td>";

			if ($rMtUang['matauang'] == 'IDR') {
				$table .= "<td style='display:none'>" . makeElement(
					"ongkos_angkut_" . $key . "",
					'textnum',
					number_format($row['ongkangkut'], 2, '.', ','),
					array('style' => 'width:80px', 'disabled' => 'disabled')
				) . "</td>";
				$table .= "<td>" . makeElement(
					"harga_satuan_" . $key . "",
					'textnum',
					number_format($row['hargasbldiskon'], 2, '.', ','),
					array('style' => 'width:100px', 'disabled' => 'disabled', 'onkeypress' => 'return angka_doang(event)', 'onkeyup' => "calculate('" . $key . "')", 'onblur' => "periksa_isi(this)", 'onblur' => "display_number('" . $key . "')", 'onfocus' => "normal_number('" . $key . "')")
				) . "<br>" . makeElement(
					"hidden_harga_satuan_" . $key . "",
					'hidden',
					number_format($row['hargasbldiskon'], 2, '.', ','),
					array('style' => 'width:100px;style:none')
				) . "</td>";
				$table .= "<td>" . makeElement(
					"total_" . $key . "",
					'textnum',
					number_format($sub_tot, 2, '.', ','),
					array('style' => 'width:100px', 'onkeypress' => 'return angka_doang(event)', 'disabled' => 'disabled')
				) . "<input type=hidden id=subTotal_" . $key . " value=" . $sub_tot_nor . " /></td>";
			} else {
				$table .= "<td style='display:none'>" . makeElement(
					"ongkos_angkut_" . $key . "",
					'textnum',
					$row['ongkangkut'],
					array('style' => 'width:80px', 'disabled' => 'disabled')
				) . "</td>";
				$table .= "<td>" . makeElement(
					"harga_satuan_" . $key . "",
					'textnum',
					$row['hargasbldiskon'],
					array('style' => 'width:100px', 'disabled' => 'disabled', 'onkeypress' => 'return angka_doang(event)', 'onkeyup' => "calculate('" . $key . "')", 'onblur' => "periksa_isi(this)", 'onblur' => "display_number('" . $key . "')", 'onfocus' => "normal_number('" . $key . "')")
				) . "<br>" . makeElement(
					"hidden_harga_satuan_" . $key . "",
					'hidden',
					number_format($row['hargasbldiskon'], 2, '.', ','),
					array('style' => 'width:100px;display:none')
				) . "</td>";
				$table .= "<td>" . makeElement(
					"total_" . $key . "",
					'textnum',
					$sub_tot,
					array('style' => 'width:100px', 'onkeypress' => 'return angka_doang(event)', 'disabled' => 'disabled')
				) . "<input type=hidden id=subTotal_" . $key . " value=" . $sub_tot_nor . " /></td>";
			}


			$table .= "<td align=center><!--<img id='detail_delete_" . $key . "' title='Hapus' class=zImgBtn onclick=\"deleteDetail('" . $key . "')\" src='images/delete_32.png'/>--></td>";
			$table .= "</tr>";
			$i = $key;
			$subTotHrgSblmDis = $subTotHrgSblmDis + $sub_tot_nor;
		}
		$i++;
	}

	// if($expnopo[3]=='SO'){
	if (strpos($expnopo, "SO") !== false) {

		$table .= "<tr>
			<td colspan=12><hr></td>
		</tr>
		<tbody id=listmaterialso>
		</tbody>
		<tr class='rowcontent' style='display:none'>
			<td colspan=2></td>
			<td>
				<input id='nm_brg_so' class='myinputtext' type='text' onkeypress=\"return tanpa_kutip(event)\" style='width:385px' placeholder='Nama Barang'>
			</td>
			<td colspan=4></td>
			<td>
				<input id='jmlhDimintaso' class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" type='text' style='width:60px;' onblur=\"display_number_so('jmlhDimintaso','1')\" onkeyup=\"calculateso('0')\" placeholder='0'>
			</td>
			<td></td>
			<td>
				<input id='harga_satuan_so' class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" type='text' style='width:100px' onkeyup=\"calculateso('0')\" onblur=\"display_number_so('harga_satuan_so','2')\" placeholder='0'>
			</td>
			<td>
				<input id='total_so' class='myinputtextnumber' onkeypress=\"return angka_doang(event)\" type='text' style='width:100px' disabled='disabled' placeholder='0'>
			</td>
			<td>
				<img src='images/plus.png' class='zImgBtn' title='Tambah Material' onclick=\"addmaterialso()\">
			</td>
		</tr>
		<tr>
			<td colspan=12><hr></td>
		</tr>";
	}

	$str = "select diskonpersen,nilaidiskon,pbbkb,addcost,ppn,notecost,pph,pph22,penambahpph22, pphfinal from " . $dbname . ".log_poht where nopo='" . $id . "' ";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();

	if ($bar['diskonpersen'] == 0 || $bar['diskonpersen'] == '') {
		$diskonpersen = 0;
		$diskonrupiah = 0;
	} else {
		$diskonpersen = $bar['diskonpersen'];
		$diskonrupiah = $bar['nilaidiskon'];
	}
	$pbbkb = $bar['pbbkb'];
	$pphfinal = $bar['pphfinal'];
	$addcost = $bar['addcost'];
	$ppn = $bar['ppn'];
	$notecost = $bar['notecost'];
	$pph = $bar['pph'];
	$pph22 = $bar['pph22'];
	$penambahpph22 = $bar['penambahpph22'];

	//PPH Final
	$pphfinal  = $bar['pphfinal']; 

	if ($pph > 0) {
		$pph = $bar['pph'];
		$grandTotalAll = $subTotHrgSblmDis - $diskonrupiah + $pbbkb + $ppn + $addcost - $pph - $pphfinal;
	} else {
		// $pph = $bar['pph22'];
		// $grandTotalAll = $subTotHrgSblmDis - $diskonrupiah + $pbbkb + $ppn + $addcost + $pph;
		if ($penambahpph22 == '1') {
			$grandTotalAll = $subTotHrgSblmDis - $diskonrupiah + $pbbkb + $ppn + $addcost + $bar['pph22'] - $pphfinal;
		} else {
			$grandTotalAll = $subTotHrgSblmDis - $diskonrupiah + $pbbkb + $ppn + $addcost - $bar['pph22'] - $pphfinal;
		}
	}



	$clspn = 8;
	if ($ongkir > 0) {
		$table .= "<tr><td>&nbsp;</td>
            <td colspan=" . $clspn . " align=right>" . $_SESSION['lang']['ongkoskirim'] . "</td>
            <td><input type=text disabled value='" . number_format($ongkir, 2) . "' class=myinputtextnumber  style=width:100px /></td>
        </tr>";
	}
	$table .= "<tr><td>&nbsp;</td>
            <td colspan=" . $clspn . " align=right>" . $_SESSION['lang']['subtotal'] . "</td>
            <td><input type=text id=total_harga_po name=total_harga_po disabled value2='" . hidezerodecimal($subTotHrgSblmDis, 2) . "' class=myinputtextnumber  style=width:100px /></td>
        </tr>
        <tr>
            <td >&nbsp;</td>
            <td colspan=" . $clspn . " align=right>" . $_SESSION['lang']['diskon'] . "</td>
            <td><input type='text'  id='angDiskon' name='angDiskon' class='myinputtextnumber' style='width:100px' onkeyup='calculate_angDiskon()' onkeypress='return angka_doang(event)' disabled value='" . number_format($diskonrupiah, 2) . "' onblur=\"getZero()\" /></td>
        </tr>
		    <tr>
            <td >&nbsp;</td>
            <td colspan=" . $clspn . " align=right>Diskon (%)</td>
            <td><input type='text'  id='diskon' name='diskon' class='myinputtextnumber' style='width:100px' onkeyup='calculate_diskon()' maxlength='5' onkeypress='return angka_doang(event)' disabled value='" . $diskonpersen . "' onblur=\"getZero()\" /> </td>
        </tr>
		<tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=" . $clspn . " align=right>PBBKB</td>
            <td><input type=text id='pbbkb' name='pbbkb' class='myinputtextnumber'  style='width:100px' onkeyup='calculatePbbkb()' onkeypress='return angka_doang(event)' disabled value='" . number_format($pbbkb, 2) . "' onblur=\"getZero()\" /></td>
        </tr>

		<!-- PPH Final -->
		<tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=" . $clspn . " align=right>PPH Final (RP)</td>
            <td><input type=text id='pphfinal' name='pphfinal' class='myinputtextnumber'  style='width:100px' onkeyup='calculatePphfinal()' onkeypress='return angka_doang(event)' disabled value='" . number_format($pphfinal, 2) . "' onblur=\"getZero()\" /></td>
        </tr>

		<tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=" . $clspn . " align=right>PPn (%)</td>
            <!--<td><input type=text id='ppN' name='ppN'  class='myinputtextnumber' style='width:100px' readonly onkeyup='calculatePpn()' disabled  maxlength='5'  onkeypress='return angka_doang(event)' onblur=\"getZero()\" />  <br><span style='display:none' id='hslPPn'> </span> </td>-->
            <td><input type=text id='ppN' name='ppN'  class='myinputtextnumber' style='width:100px;background-color:#CCCCCC !important' readonly disabled  maxlength='5'  onkeypress='return angka_doang(event)' />  <br><span style='display:none' id='hslPPn'> </span> </td>
			<td style='vertical-align:top;display:none'><input type=checkbox title='Check => Include PPn\nUncheck => Excelude Ppn' id=chkPpn name=chkPpn onclick=\"checkChkPpn()\" /></td>
        </tr>
		<tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=" . $clspn . " align=right>PPn (Rp)</td>
            <!--<td><input type='text' id='ppn' name='ppn' class='myinputtextnumber' onkeypress='return angka_doang(event)' disabled style='width:100px' onkeyup=\"z.numberFormat('ppn',2);\"  onblur=\"grandTotal()\" /></td>-->
            <td><input type='text' id='ppn' name='ppn' class='myinputtextnumber' onkeypress='return angka_doang(event)' disabled style='width:100px' onblur=\"grandTotal()\" /></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=" . $clspn . " align=right>PPh (%)</td>
            <td><input type=text id='ppH' name='ppH'  class='myinputtextnumber' style='width:100px' onkeyup='calculatePph()' disabled  maxlength='5'  onkeypress='return angka_doang(event)' onblur=\"getZero()\" /><br><span id='hslPPh' style='display:none'> </span> </td>
        </tr>
		<tr>";
		// $ketpph = '23';
		// if ($pph22>0) {
		// 	$ketpph = '22';
		// }
        $table .= "<td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=" . $clspn . " align=right>PPh23 (Rp)</td>
            <td><input type='text' id='pph' name='pph' class='myinputtextnumber' onkeypress='return angka_doang(event)' disabled style='width:100px' onblur='calculatepph()' /></td>
        </tr>";
		$table .= "<td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=" . $clspn . " align=right>PPh22 (Rp)</td>
            <td><input type='text' id='pph22' name='pph22' class='myinputtextnumber' onkeypress='return angka_doang(event)' disabled style='width:100px' onblur='calculatepph()' /></td>
        </tr>";
		$table .="<tr style='display:none'>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=" . $clspn . " align=right>Add Cost</td>
            <td><input type=text id='addcost' onkeyup=grandTotal(); value=0 name='addcost'  class='myinputtextnumber' disabled style='width:100px'  maxlength='12' onkeypress='return angka_doang(event)' onblur=\"getZero()\" /></td>
        </tr>
		<tr style='display:none'>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=" . $clspn . " align=right>Note Cost</td>
            <td><input type=text id='notecost' name='notecost' value='" . $notecost . "' class='myinputtext' style='width:100px' /></td>
        </tr>
         <tr>
            <td>&nbsp;</td>
            <td colspan=" . $clspn . " align=right>" . $_SESSION['lang']['grnd_total'] . "</td>
            <td><input type=text id='grand_total' name='grand_total' disabled  class='myinputtextnumber' value='" . number_format($grandTotalAll, 2) . "' style=width:100px /></td>
        </tr><input type=hidden id='sub_total' name='sub_total' ><input type=hidden id='nilai_diskon' name='nilai_diskon'  />";
	$table .= "</tbody>";

	echo $table;
}
