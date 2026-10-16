<?php
    require_once('master_validation.php');
    include('lib/nangkoelib.php');
    require_once('config/connection.php');
    include_once('lib/zLib.php');
    include_once('lib/HtmlExcel.php');
    require_once('lib/fpdf.php');

	$method=checkPostGet('method','');
	$namasupplier = makeOption($dbname,'log_5supplier','supplierid,namasupplier');

	#=== Filter list PO serta tarikan Excel/PDF (dipakai bersama oleh list, Excel, dan PDF) ===
	#filter dibersihkan dulu: no PO bebas (di-escape), tanggal dd-mm-yyyy, status 1-5, vendor alfanumerik, tahun 4 digit (kosong = semua)
	if (!function_exists('cpoFilter')) {
		function cpoFilter()
		{
			global $owlPDO;
			$f = array('nopo' => '', 'tgl' => '', 'status' => '', 'supplier' => '', 'tahun' => '');
			$nopo = trim((string)checkPostGet('txtSearch', ''));
			if ($nopo != '' and $nopo != 'undefined' and $nopo != 'null') {
				$f['nopo'] = substr($owlPDO->quote($nopo), 1, -1);
			}
			$tgl = trim((string)checkPostGet('tglCari', ''));
			if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $tgl, $m)) {
				$f['tgl'] = $m[3] . '-' . $m[2] . '-' . $m[1];
			}
			$st = (string)checkPostGet('statusreal', '');
			if (in_array($st, array('1', '2', '3', '4', '5'), true)) {
				$f['status'] = $st;
			}
			$sp = trim((string)checkPostGet('kodesupplier', ''));
			if ($sp != '' and preg_match('/^[A-Za-z0-9_\-\.]+$/', $sp)) {
				$f['supplier'] = $sp;
			}
			$th = trim((string)checkPostGet('filterTahun', ''));
			if (preg_match('/^\d{4}$/', $th)) {
				$f['tahun'] = $th;
			}
			return $f;
		}
	}
	if (!function_exists('cpoWhereList')) {
		function cpoWhereList()
		{
			$f = cpoFilter();
			$where = " and kodeorg in (" . getOrgDetail(4) . ")";
			if ($f['nopo'] != '') {
				$where .= " and nopo LIKE '%" . $f['nopo'] . "%'";
			}
			if ($f['supplier'] != '') {
				$where .= " and kodesupplier='" . $f['supplier'] . "'";
			}
			if ($f['tgl'] != '') {
				$where .= " and tanggal='" . $f['tgl'] . "'";
			}
			if ($f['tahun'] != '') {
				$where .= " and tanggal>='" . $f['tahun'] . "-01-01' and tanggal<='" . $f['tahun'] . "-12-31'";
			}
			if ($f['status'] == '1') {
				##RELEASE
				$where .= " and statuspo in ('2','3') and stat_release='1' and closed='0'";
			}
			if ($f['status'] == '2') {
				##UNRELEASE
				$where .= " and statuspo in ('0','1')";
			}
			if ($f['status'] == '3') {
				##BECOME OUT STANDING
				$where .= " and statuspo in ('2','3') and closed='1' and keteranganclose like '%,tanggal tutup : %'";
			}
			if ($f['status'] == '4') {
				##CLOSE
				$where .= " and statuspo in ('2','3') and closed='1' and (keterangan like '%,tanggal tutup : %' or keteranganclose like '%Tutup By System%')";
			}
			if ($f['status'] == '5') {
				##CENCEL
				$where .= " and statuspo in ('4') and closed='1' and (keteranganclose like '%,tanggal cancel : %')";
			}
			return $where;
		}
	}
	#status satu PO (dipakai tampilan list dan tarikan Excel/PDF)
	if (!function_exists('cpoStatusPo')) {
		function cpoStatusPo($bar)
		{
			$postatus = "";
			if ($bar['statuspo'] == '0') {
				$postatus = "Unrelease";
			} else if ($bar['statuspo'] == '1') {
				$postatus = "Unrelease";
			} else if ($bar['statuspo'] == '2' or $bar['statuspo'] == '3') {
				if ($bar['closed'] == '0') {
					$postatus = "Release";
				} else {
					if (strpos($bar['keteranganclose'], ",tanggal tutup : ")) {
						$postatus = "Become Out Standing";
					}
					if (strpos($bar['keterangan'], ",tanggal tutup : ")) {
						$postatus = "Close";
					}
					if (strpos($bar['keteranganclose'], "Tutup By System")) {
						$postatus = "Close";
					}
				}
			} else if ($bar['statuspo'] == '4') {
				$postatus = "Cancel";
			}
			return $postatus;
		}
	}
	#nomor transaksi penerimaan gudang sebuah PO
	if (!function_exists('cpoGudang')) {
		function cpoGudang($nopo)
		{
			global $dbname, $owlPDO;
			$hasil = array();
			foreach (array('log_transaksiht', 'log_noninventory') as $tb) {
				$stmt = $owlPDO->prepare("select notransaksi from " . $dbname . "." . $tb . " where nopo=?");
				$stmt->execute(array($nopo));
				foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
					$hasil[] = $b['notransaksi'];
				}
			}
			return $hasil;
		}
	}
	#keterangan filter yang aktif untuk kop tarikan
	if (!function_exists('cpoInfoFilter')) {
		function cpoInfoFilter()
		{
			global $namasupplier, $cpoTerpotong;
			$f = cpoFilter();
			$arrStatus = array('1' => 'Release', '2' => 'Unrelease', '3' => 'Become Out Standing', '4' => 'Close', '5' => 'Cancel');
			$info = array();
			$info[] = 'Tahun: ' . ($f['tahun'] != '' ? $f['tahun'] : 'Semua');
			$info[] = 'Status: ' . ($f['status'] != '' ? $arrStatus[$f['status']] : 'Semua');
			if ($f['supplier'] != '') {
				$info[] = 'Vendor: ' . (isset($namasupplier[$f['supplier']]) ? trim($namasupplier[$f['supplier']]) : $f['supplier']);
			}
			if ($f['nopo'] != '') {
				$info[] = 'No. PO: ' . stripslashes($f['nopo']);
			}
			if ($f['tgl'] != '') {
				$info[] = 'Tanggal: ' . tanggalnormal($f['tgl']);
			}
			if (!empty($cpoTerpotong)) {
				$info[] = 'Dibatasi 3000 data terbaru, persempit filter bila perlu';
			}
			return implode(' | ', $info);
		}
	}
	#PO sesuai filter untuk tarikan Excel/PDF (dibatasi supaya tidak berat)
	if (!function_exists('cpoDataList')) {
		function cpoDataList($maks = 3000)
		{
			global $dbname, $cpoTerpotong;
			$rows = fetchdata("select * from " . $dbname . ".log_poht where nopo!='' " . cpoWhereList() . " order by tanggal desc limit " . ((int)$maks + 1));
			$cpoTerpotong = count($rows) > $maks;
			return array_slice($rows, 0, $maks);
		}
	}
	#warna status PO: latar dan teks (hijau Release, kuning Unrelease, oranye Become Out Standing, abu Close, merah Cancel)
	if (!function_exists('cpoWarnaStatus')) {
		function cpoWarnaStatus($status)
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
		#atribut sel Excel untuk status (latar dan huruf tebal berwarna)
	if (!function_exists('cpoStyleExcel')) {
		function cpoStyleExcel($status)
		{
			$w = cpoWarnaStatus($status);
			return $w[0] != '' ? " bgcolor=" . $w[0] . " style='color:" . $w[1] . ";font-weight:bold'" : '';
		}
	}
	#teks yang kepanjangan dipotong dan diberi "..." supaya tidak tumpang tindih ke kolom sebelah
	if (!function_exists('cpoFit')) {
		function cpoFit($pdf, $txt, $w, $f = 7)
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

	switch($method) {

	case 'list_new_data':
        $limit=20;
        $page=0;
        if(isset($_POST['page']))
        {
			$page=(int)$_POST['page'];
			if($page<0)
				$page=0;
        }
        $offset=$page*$limit;
		$maxdisplay=($page*$limit);
		$where = cpoWhereList();

		$sql2="SELECT count(*) as jmlhrow FROM ".$dbname.".log_poht where nopo!='' ".$where;
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		$jlhbrs=0;
		while($jsl=$query2->fetch()){
			$jlhbrs= $jsl->jmlhrow;
		}

		$strx="SELECT * FROM ".$dbname.".log_poht where nopo!=''  ".$where." order by tanggal desc limit ".$offset.",".$limit."";
		$res=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

		$no=$maxdisplay;
		$data =  array();
		$data['tbody'] = array();
		$data['tfoot'] = array();
		$nopp='';
		while($bar=$res->fetch()) {
			$stmtx=$owlPDO->prepare("select a.nomor from ".$dbname.".log_permintaanhargadt a left join ".$dbname.".log_perintaanhargaht b on a.nomor=b.nomor where a.norph=? and b.supplierid=?");
			$stmtx->execute(array($bar['nodph'],$bar['kodesupplier']));
			$nopp=$bar['nopp'];
			$no+=1;

			$body = array();
			$body['no'] = $no;
			$body['nopo'] = $bar['nopo'];
			$body['tanggal'] = tanggalnormal($bar['tanggal']);
			$body['namaorganisasi'] = isset($nmorg[$bar['kodeorg']]) ? $nmorg[$bar['kodeorg']] : '';
			$body['vendor'] = $namasupplier[$bar['kodesupplier']];
			$body['kodesupplier'] = $bar['kodesupplier'];
			$body['nomordph'] = $bar['nodph'];
			$body['tipepo'] = $bar['tipepo'];
			$body['st'] = cpoStatusPo($bar);
			$wrn = cpoWarnaStatus($body['st']);
			$body['stwarna'] = $wrn[0] != '' ? 'background-color:' . $wrn[0] . ';color:' . $wrn[1] . ';font-weight:bold' : '';
			$gudang = '';
			foreach(cpoGudang($bar['nopo']) as $nt){
				$gudang .= $nt."<br>";
			}
			$body['gudang'] = $gudang;
			$body['print'] = $_SESSION['lang']['print'];
			$data['tbody'][] = $body;
		}

		$data['tfoot']['page']		= $page;
		$data['tfoot']['limit']		= $limit;
		$data['tfoot']['jlhbrs']	= $jlhbrs;
		$data['tfoot']['pref']		= $_SESSION['lang']['pref'];
		$data['tfoot']['lanjut']	= $_SESSION['lang']['lanjut'];
		$data['tfoot']['nopp']		= $nopp;
		$data['tfoot']['nopp_no']	= "nopp_".$no;

		echo json_encode($data);
		break;

	case 'excellist':
		$rows = cpoDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
		$kolom = 8;
		$stream = "<table>
			<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
			<tr><td colspan=" . $kolom . "><b>CETAK PO</b></td></tr>
			<tr><td colspan=" . $kolom . ">" . htmlspecialchars(cpoInfoFilter()) . "</td></tr>
			<tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
			<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
			</table>";
		$stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
			<thead><tr>
				<th align=center bgcolor=#CCCCCC>No</th>
				<th align=center bgcolor=#CCCCCC>No. PO</th>
				<th align=center bgcolor=#CCCCCC>Tanggal PO</th>
				<th align=center bgcolor=#CCCCCC>Perusahaan</th>
				<th align=center bgcolor=#CCCCCC>Vendor</th>
				<th align=center bgcolor=#CCCCCC>Tipe</th>
				<th align=center bgcolor=#CCCCCC>Status</th>
				<th align=center bgcolor=#CCCCCC>Gudang (No. Transaksi Penerimaan)</th>
			</tr></thead><tbody>";
		$no = 0;
		foreach ($rows as $bar) {
			$no++;
			$stream .= "<tr>
				<td align=center>" . $no . "</td>
				<td>" . htmlspecialchars($bar['nopo']) . "</td>
				<td align=center>" . tanggalnormal($bar['tanggal']) . "</td>
				<td>" . htmlspecialchars(isset($nmorg[$bar['kodeorg']]) ? $nmorg[$bar['kodeorg']] : $bar['kodeorg']) . "</td>
				<td>" . htmlspecialchars(isset($namasupplier[$bar['kodesupplier']]) ? trim($namasupplier[$bar['kodesupplier']]) : $bar['kodesupplier']) . "</td>
				<td align=center>" . htmlspecialchars($bar['tipepo']) . "</td>
				<td align=center" . cpoStyleExcel(cpoStatusPo($bar)) . ">" . htmlspecialchars(cpoStatusPo($bar)) . "</td>
				<td>" . nl2br(htmlspecialchars(implode("\n", cpoGudang($bar['nopo'])))) . "</td>
			</tr>";
		}
		if ($no == 0) {
			$stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
		}
		$stream .= "</tbody></table>";
		$xls = new HtmlExcel();
		$xls->addSheet("CetakPO", $stream);
		$xls->headers("CetakPO_" . date('YmdHis') . ".xls");
		echo $xls->buildFile();
		exit;

	case 'pdflist':
		$rows = cpoDataList();
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$nmorg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
		$infoFilter = cpoInfoFilter();
		if (!class_exists('PDFListCPO')) {
			class PDFListCPO extends FPDF
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
					$this->Cell($width, 7, 'CETAK PO', 0, 1, 'C');
					$this->SetFont('Arial', '', 8);
					$this->Cell($width, 5, utf8_decode($infoFilter), 0, 1, 'C');
					$this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
					$this->Ln(1);
					$this->SetFillColor(220, 220, 220);
					$this->SetFont('Arial', 'B', 8);
					$judul = array('No', 'No. PO', 'Tanggal', 'Perusahaan', 'Vendor', 'Tipe', 'Status', 'Gudang');
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
		$pdf = new PDFListCPO('L', 'mm', 'A4');
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(false, 16);
		$width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$colFixed = array(8, 52, 20, 55, 60, 14, 26); #semua kolom kecuali Gudang
		$pdf->colW = array_merge($colFixed, array($width - array_sum($colFixed)));
		$pdf->AddPage();
		$h = 6;
		$no = 0;
		foreach ($rows as $bar) {
			$no++;
			if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
				$pdf->AddPage();
			}
			$c = $pdf->colW;
			$pdf->SetFont('Arial', '', 7);
			$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
			$pdf->Cell($c[1], $h, cpoFit($pdf, $bar['nopo'], $c[1]), 1, 0, 'L');
			$pdf->Cell($c[2], $h, tanggalnormal($bar['tanggal']), 1, 0, 'C');
			$pdf->Cell($c[3], $h, cpoFit($pdf, isset($nmorg[$bar['kodeorg']]) ? $nmorg[$bar['kodeorg']] : $bar['kodeorg'], $c[3]), 1, 0, 'L');
			$pdf->Cell($c[4], $h, cpoFit($pdf, isset($namasupplier[$bar['kodesupplier']]) ? trim($namasupplier[$bar['kodesupplier']]) : $bar['kodesupplier'], $c[4]), 1, 0, 'L');
			$pdf->Cell($c[5], $h, $bar['tipepo'], 1, 0, 'C');
			$wrn = cpoWarnaStatus(cpoStatusPo($bar));
			if ($wrn[0] != '') {
				sscanf($wrn[0], '#%02x%02x%02x', $br, $bg, $bb);
				sscanf($wrn[1], '#%02x%02x%02x', $fr, $fg, $fb);
				$pdf->SetFillColor($br, $bg, $bb);
				$pdf->SetTextColor($fr, $fg, $fb);
			}
			$pdf->Cell($c[6], $h, cpoFit($pdf, cpoStatusPo($bar), $c[6]), 1, 0, 'L', $wrn[0] != '');
			$pdf->SetTextColor(0, 0, 0);
			$pdf->Cell($c[7], $h, cpoFit($pdf, implode('; ', cpoGudang($bar['nopo'])), $c[7]), 1, 1, 'L');
		}
		if ($no == 0) {
			$pdf->Cell(array_sum($pdf->colW), $h, 'Data tidak ditemukan', 1, 1, 'C');
		}
		$pdf->Output();
		exit;

	default:
	break;
	}
?>