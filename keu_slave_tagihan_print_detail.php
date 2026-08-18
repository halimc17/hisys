<?php
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');

$proses = $_GET['proses'];
$param = $_GET;
$urlefil = checkPostGet('urlefil', '0');

/** Report Prep **/
$cols = array();

#=============================== Header =======================================
$where = "noinvoice='" . $param['noinvoice'] . "'";
$queryH = selectQuery($dbname, 'keu_tagihanht', '*', $where);
$resH = fetchData($queryH);
$dataH = $resH[0];

#=============================== Detail =======================================
# Data
$query = selectQuery($dbname, 'keu_tagihandt', '*', $where);
$res = fetchData($query);

# Options
$optAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun like '116%' and detail=1");
$optPt = makeOption(
	$dbname,
	'organisasi',
	'kodeorganisasi,namaorganisasi',
	"kodeorganisasi='" . $dataH['kodeorg'] . "'"
);
$tab = ($dataH['tipeinvoice'] == 'p') ? 'log_poht' : 'log_spkht';
$cond = ($dataH['tipeinvoice'] == 'p') ? 'nopo' : 'notransaksi';

if ($tab == 'log_poht') {
	$qSupp = "select b.namasupplier" . (($dataH['tipeinvoice'] == 'p') ? ',a.matauang' : '') . "
        from " . $dbname . "." . $tab . " a
        left join " . $dbname . ".log_5supplier b on a.kodesupplier=b.supplierid
        where " . $cond . "='" . $dataH['nopo'] . "'";
} else {
	$qSupp = "select b.namasupplier" . (($dataH['tipeinvoice'] == 'p') ? ',a.matauang' : '') . "
        from " . $dbname . "." . $tab . " a
        left join " . $dbname . ".log_5supplier b on a.koderekanan=b.supplierid
        where " . $cond . "='" . $dataH['nopo'] . "'";
}

//exit("Error:$qSupp");

$resSupp = fetchData($qSupp);
if (!isset($resSupp[0]['namasupplier']) || $resSupp[0]['namasupplier'] == '') {
	$str = $owlPDO->query("select   b.namasupplier,a.kodesupplier from " . $dbname . ".keu_tagihanht a left join log_5supplier b
              on a.kodesupplier=b.supplierid where a.noinvoice='" . $param['noinvoice'] . "'");
	$str->setFetchMode(PDO::FETCH_OBJ);
	$res = $str->fetch();
	if ($res->namasupplier == '') {
		$optNmsp = makeOption($dbname, 'log_5klsupplier', 'kode,kelompok', "kode='" . $res->kodesupplier . "'");
		$resSupp[0]['namasupplier'] = $optNmsp[$res->kodesupplier];
	} else {
		$resSupp[0]['namasupplier'] = $res->namasupplier;
	}
}
#=============================== Detail =======================================
# Data
$optJenis = makeOption($dbname, "keu_5jenistagihan", "kode,source", "kode='" . $dataH['tipeinvoice'] . "'");
if (substr($optJenis[$dataH['tipeinvoice']], 0, 3) == 'htg') {
	$col1 = 'noinvoice,noakun,sum(nilai) as nilai,keterangan,kodevhc,kodeasset';
	$where .= " group by noakun";
} else {
	$col1 = '*';
}
$query_detail = selectQuery($dbname, 'keu_tagihandt', $col1, $where);
$red 		  = fetchData($query_detail);

$col2 = 'noakun,namaakun';
$queryakun = selectQuery($dbname, 'keu_5akun', $col2, '');
$rea 		  = fetchData($queryakun);

$col3 = 'kode,jurnal';
$queryjenis = selectQuery($dbname, 'keu_5jenistagihan', $col3, '');
$rej 		  = fetchData($queryjenis);


function find_dom($array, $code, $compare, $findout)
{
	$result = "";
	foreach ($array as $r) {
		if ($code == $r[$compare]) {
			$result = $r[$findout];
			break;
		}
	}
	return $result;
}


# Data Empty
//if(empty($red)) {
//    echo 'Data Empty';
//    exit;
//}

#================================ Prep Data ===================================
if (substr($optJenis[$dataH['tipeinvoice']], 0, 3) == 'htg') {
	$title = "NOTA HUTANG PAJAK";
} else {
	$title = "NOTA HUTANG";
}


/** Output Format **/
switch ($proses) {
	case 'pdf':

		if (!class_exists('PDFINV')) {
			class PDFINV extends FPDF
			{
				function Header()
				{
					global $conn;
					global $dbname;
					global $userid;
					global $notransaksi;
					global $kodevhc;
					global $posting;
					global $kodept;
					global $param;
					global $owlPDO;
					global $dataH;
					global $title;

					//ambil nama pt
					$arrHead = setheadreport($dataH['kodeorg'], $dataH['unit']);


					$width = $this->w - $this->lMargin - $this->rMargin;
					$height = 5;
					$path = $arrHead['logo'];
					$this->Image($path, $this->lMargin, ($this->tMargin - 8), 0, 20);
					$this->SetFont('Arial', 'B', 9);
					$this->SetFillColor(255, 255, 255);
					$this->Ln(-5);
					$this->SetX(45);
					$this->Cell($width - 100, $height, $arrHead['nama'], 0, 1, 'L');
					$this->SetX(45);
					$this->Cell($width - 100, $height, $arrHead['alamat'], 0, 1, 'L');
					$this->SetX(45);
					$this->Cell($width - 100, $height, "Tel: " . $arrHead['telepon'], 0, 1, 'L');
					$this->Line(
						$this->lMargin,
						$this->tMargin + ($height * 3),
						$this->lMargin + $width,
						$this->tMargin + ($height * 3)
					);
					$this->Ln();
					$this->Ln();
					$this->SetFont('Arial', '', 10);
					$this->Cell($width, $height, $title, 0, 1, 'C');

					$this->Ln();
				}

				function Footer()
				{
					$width = $this->w - $this->lMargin - $this->rMargin;
					$height = 12;
					$this->SetY(-20);
					$this->SetFont('Arial', 'I', 7);
					$this->Cell(1, $height, 'Page ' . $this->PageNo(), 'T', 0, 'L');
					$str = "Printed by " . $_SESSION['standard']['username'] . "[" . $_SESSION['empl']['lokasitugas'] . "]" .
						":" . @$rPeriode['periode'] . " at " . date('d-m-Y H:i:s');
					$this->Cell($width - 1, $height, $str, 'T', 0, 'R');
				}
			}
		}


		$pdf = new PDFINV('P', 'mm', 'A4');
		// $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		$height = 4;

		// $pdf->_noThead=true;
		$pdf->_title = $title;
		// $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;

		$pdf->AddPage();
		$pdf->SetFillColor(255, 255, 255);
		$pdf->SetFont('Arial', '', 9);

		switch ($dataH['tipeinvoice']) {
			case 'p':
				$tipe = 'PO';
				break;
			case 'k':
				$tipe = 'SPK';
				break;
		}

		$pdf->Ln();
		// Header
		$startY = $pdf->GetY();
		$width1 = 30;
		$pdf->Cell($width1, $height, $_SESSION['lang']['noinvoice'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(25, $height, $dataH['noinvoice'], 0, 1, 'L');

		$pdf->Cell($width1, $height, $_SESSION['lang']['pt'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(25, $height, $optPt[$dataH['kodeorg']], 0, 1, 'L');

		$pdf->Cell($width1, $height, $_SESSION['lang']['tanggal'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(25, $height, tanggalnormal($dataH['tanggal']), 0, 1, 'L');

		$pdf->Cell($width1, $height, $_SESSION['lang']['keterangan'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->MultiCell(70, $height, $dataH['keterangan2'], 0, 'J');

		$pdf->Cell($width1, $height, $_SESSION['lang']['jatuhtempo'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(25, $height, tanggalnormal($dataH['jatuhtempo']), 0, 1, 'L');

		if ($dataH['historynofp'] == '') {
			$nofaktur = $dataH['nofp'];
		} else {
			$nofaktur = $dataH['historynofp'];
		}

		$pdf->Cell($width1, $height, $_SESSION['lang']['nofp'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(25, $height, $nofaktur, 0, 1, 'L');

		$pdf->Cell($width1, $height, $_SESSION['lang']['nilaiinvoice'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(25, $height, number_format($dataH['nilaiinvoice'], 2), 0, 1, 'L');

		$pdf->Cell($width1, $height, $_SESSION['lang']['unit'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(25, $height, $dataH['unit'], 0, 1, 'L');

		$nojurnal = "";
		switch (find_dom($rej, $dataH['tipeinvoice'], 'kode', 'jurnal')) {
			case 0:
				$nojurnal = $_SESSION['lang']['no'];
				break;
			case 1:
				$nojurnal = $_SESSION['lang']['yes'];
				break;
		}

		$pdf->Cell($width1, $height, $_SESSION['lang']['jurnal'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(25, $height, $nojurnal, 0, 1, 'L');
		$optNmPosting = makeOption($dbname, "datakaryawan", "karyawanid,namakaryawan", "karyawanid='" . $dataH['postingby'] . "'");
		$pdf->Cell($width1, $height, $_SESSION['lang']['keterangan'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(25, $height, $dataH['keterangan'], 0, 1, 'L');
		$pdf->Cell($width1, $height, $_SESSION['lang']['dipostingoleh'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(25, $height, $optNmPosting[$dataH['postingby']], 0, 1, 'L');

		$startYhdetail = $pdf->GetY() + 10;
		//## Data Detail ##
		if (count($red) > 0) {
			$pdf->SetXY(10, $startYhdetail);
			$awalxdetail = $pdf->GetX();
			$setpanjang = 200;
			$pdf->Cell(80, $height, $_SESSION['lang']['noakun'], 1, 0, 'L');
			$onexdetail = $pdf->GetX();
			$pdf->Cell(30, $height, $_SESSION['lang']['nilai'], 1, 0, 'L');
			$twoxdetail = $pdf->GetX();
			$pdf->Cell(40, $height, $_SESSION['lang']['kodevhc'], 1, 0, 'L');
			$treexdetail = $pdf->GetX();
			$pdf->Cell(40, $height, $_SESSION['lang']['kodeasset'], 1, 0, 'L');
			$akhirYhdetail = $pdf->GetY();
			$akhirXhdetail = $pdf->GetX();
			$jml = 0;
			$nourut = 0;

			#= ambil data uang muka yang akan ditambajhkan ke dpp detail baris pertama
			// foreach($red as $row) {
			// if(substr($row['noakun'],0,3)=='118'){
			// $dtnilaium=$row['nilai']*-1;
			// }
			// }

			foreach ($red as $row) {
				if (substr($row['noakun'], 0, 3) == '118') {
					if ($dataH['tipeinvoice'] == 'um') {
						$dtnilaium = $row['nilai'];
					} else {
						$dtnilaium = $row['nilai'] * -1;
					}
				}
			}


			foreach ($red as $row) {
				$nourut += 1;
				$heightdetail = $height;
				$no = $heightdetail;
				$startYdetail = $pdf->GetY() + 7;
				$pdf->SetXY(10, $startYdetail);
				$awalxdetailL = $pdf->GetX();
				$awalydetailL = $pdf->GetY();


				// $pdf->MultiCell(50,$height,$resSupp[0]['namasupplier'],0,'L');

				$optNmakun = makeOption($dbname, "keu_5akun", "noakun,namaakun", "noakun='" . $row['noakun'] . "'");
				$y1 = $pdf->GetY();
				$pdf->MultiCell(90, $heightdetail, $row['noakun'] . "-" . htmlentities($optNmakun[$row['noakun']]), 0, 'L');
				$y2 = $pdf->GetY();
				$pdf->SetY($y1);
				$pdf->SetX(90);
				if ($nourut == 1 and substr($row['noakun'], 0, 3) != '118') {
					$row['nilai'] = $row['nilai'] + $dtnilaium;
				}
				$pdf->Cell(30, $heightdetail, number_format($row['nilai'], 2), 0, 0, 'R');
				$pdf->SetX(120);
				$pdf->Cell(40, $heightdetail, $row['kodevhc'], 0, 0, 'L');
				$pdf->SetX(160);
				$pdf->Cell(40, $heightdetail, $row['kodeasset'], 0, 0, 'L');
				// $jml = $jml + $row['nilai'];
				@$jml += $row['nilai'];
				@$no += $no;
			}
			#cek apakah ada nota debet
			$sDet = "select sum(nilai_detail) as nilaiNota,noakun_detail as noakun from " . $dbname . ".keu_notadebet_vw where noinvoice_referensi='" . $param['noinvoice'] . "' and noakun_detail='1160202' group by noakun_detail";
			// echo $sDet;exit("Error:A");
			$rDet = fetchData($sDet);
			$heightdetail = $height;
			$no = $heightdetail;
			$startYdetail = $pdf->GetY() + 5;
			$pdf->SetXY(10, $startYdetail);
			$awalxdetailL = $pdf->GetX();
			$awalydetailL = $pdf->GetY();
			$nilaiNotaDebet = 0;
			if (count($rDet) != 0) {
				$nilaiNotaDebet = $rDet[0]['nilaiNota'] * -1;
			}
			if ($nilaiNotaDebet != '0') {
				$pdf->Cell(80, $heightdetail, $rDet[0]['noakun'] . "-" . find_dom($rea, $rDet[0]['noakun'], 'noakun', 'namaakun'), 0, 0, 'L');
				$pdf->Cell(30, $heightdetail, number_format($nilaiNotaDebet, 0), 0, 0, 'R');
				$pdf->Cell(40, $heightdetail, '', 0, 0, 'L');
				$pdf->Cell(40, $heightdetail, '', 0, 0, 'L');
				$jml = $jml + $nilaiNotaDebet;
			}
			$akhirydetailL = $pdf->GetY() + $no;
			$pdf->Line($onexdetail, $akhirYhdetail, $onexdetail, $akhirydetailL);
			$pdf->Line($twoxdetail, $akhirYhdetail, $twoxdetail, $akhirydetailL);
			$pdf->Line($treexdetail, $akhirYhdetail, $treexdetail, $akhirydetailL);

			$pdf->Line($awalxdetail, $akhirYhdetail, $awalxdetail, $akhirydetailL);
			$pdf->Line($akhirXhdetail, $akhirYhdetail, $akhirXhdetail, $akhirydetailL);
			$pdf->Line($awalxdetail, $akhirydetailL, $akhirXhdetail, $akhirydetailL);

			$startYhdetail = $pdf->GetY() + $no;
			$pdf->SetXY(10, $startYhdetail);
			$totalXhdetail = $pdf->GetX();
			$pdf->Cell(80, $height, $_SESSION['lang']['total'], 1, 0, 'L');
			$pdf->Cell(30, $height, number_format($jml, 2), 1, 0, 'R');
			$pdf->Cell(80, $height, '', 1, 1, 'L');
		}

		$optNmuser = makeOption($dbname, "datakaryawan", "karyawanid,namakaryawan", "karyawanid='" . $dataH['updateby'] . "'");

		$pdf->Ln(15);
		$pdf->SetX(40);
		$pdf->Cell(50, $height, 'Pembuat', 0, 0, 'L');
		$pdf->Cell(50, $height, '', 0, 0, 'L');
		$pdf->Cell(50, $height, $_SESSION['lang']['menyetujui'], 0, 1, 'L');
		$pdf->SetX(40);
		$pdf->Cell(50, 20, '', 0, 0, 'L');
		$pdf->Cell(50, 20, '', 0, 0, 'L');
		$pdf->Cell(50, 20, '', 0, 1, 'L');
		$pdf->SetX(40);
		$pdf->Cell(50, $height, $optNmuser[$dataH['updateby']], 0, 0, 'L');
		$pdf->Cell(50, $height, '', 0, 'L');
		$pdf->Cell(50, $height, $optNmPosting[$dataH['postingby']], 0, 1, 'L');

		$pdf->SetX(40);
		$pdf->Cell(50, $height, '', 0, 'L');
		$pdf->Cell(50, $height, '', 0, 0, 'L');
		$pdf->Cell(50, $height, $dataH['postingdate'], 0, 1, 'L');

		//sisi kanan
		$xawalkanan = 115;
		$pdf->SetXY($xawalkanan, $startY);
		$awalx = $pdf->GetX();
		$setpanjang = 85;

		$pdf->Cell(85, $height, $_SESSION['lang']['po'], 1, 1, 'L');


		$pdf->SetX($xawalkanan);
		$pdf->Cell(30, $height, $_SESSION['lang']['nopo'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(50, $height, $dataH['nopo'], 0, 1, 'L');
		$pdf->SetX($xawalkanan);
		$pdf->Cell(30, $height, $_SESSION['lang']['noinvoice'] . ' Supplier', 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(50, $height, $dataH['noinvoicesupplier'], 0, 1, 'L');

		$pdf->SetX($xawalkanan);
		$pdf->Cell(30, $height, $_SESSION['lang']['supplier'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		//$pdf->Cell(25,$height,$resSupp[0]['namasupplier'],0,1,'L');
		$pdf->MultiCell(50, $height, $resSupp[0]['namasupplier'], 0, 'L');

		$pdf->SetX($xawalkanan);
		$pdf->Cell(30, $height, $_SESSION['lang']['matauang'], 0, 0, 'L');
		$pdf->Cell(5, $height, ':', 0, 0, 'L');
		$pdf->Cell(50, $height, (isset($dataH['matauang']) ? $dataH['matauang'] : 'IDR'), 0, 1, 'L');

		$pdf->SetX($xawalkanan);
		$pdf->Cell(30, $height, $_SESSION['lang']['kurs'], 'B', 0, 'L');
		$pdf->Cell(5, $height, ':', 'B', 0, 'L');
		$pdf->Cell(50, $height, $dataH['kurs'], 'B', 1, 'L');

		$endY = $pdf->GetY();

		//$pdf->Rect($pdf->lMargin+49.5/100*$width,$startY-1,50.5/100*$width,$endY-$startY-7);
		//$pdf->Line($pdf->lMargin+49.5/100*$width,$startY+11,$pdf->lMargin+$width,$startY+11);

		$pdf->Line($awalx, $startY, $awalx, $endY);
		$pdf->Line($awalx + $setpanjang, $startY, $awalx + $setpanjang, $endY);

		$pdf->Ln($height);

		# Print Out
		if ($urlefil == '0') {
			$pdf->Output();
		} else {
			$pdf->Output($urlefil);
		}
		break;

	case 'file':
		if ($dataH['uploadinvoice'] != "") {
			$doc = $dataH['uploadinvoice'];
			$potong = explode('.', $doc);
			if ($potong[1] == 'pdf') {
				echo "<embed src=\"filegis/" . $doc . "\" width=780px height=370px>";
			} else {
				echo "<img src=\"filegis/" . $doc . "\">";
			}
		} else {
			echo $_SESSION['lang']['tidakditemukan'];
		}

		break;

	default:
		break;
}
