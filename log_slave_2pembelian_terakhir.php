<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if (isset($_POST['proses'])) {
    $proses = $_POST['proses'];
} else {
    $proses = $_GET['proses'];
}

$klmpkBrg = empty($_POST['klmpkBrg']) ? (isset($_GET['klmpkBrg']) ? $_GET['klmpkBrg'] : '') : $_POST['klmpkBrg'];
$kdBrg = empty($_POST['kdBrg']) ? (isset($_GET['kdBrg']) ? $_GET['kdBrg'] : '') : $_POST['kdBrg'];
$tglDr = empty($_POST['tglDr']) ? (isset($_GET['tglDr']) ? $_GET['tglDr'] : '') : $_POST['tglDr'];
$tanggalSampai = empty($_POST['tanggalSampai']) ? (isset($_GET['tanggalSampai']) ? $_GET['tanggalSampai'] : '') : $_POST['tanggalSampai'];
$lokBeli = empty($_POST['lokBeli']) ? (isset($_GET['lokBeli']) ? $_GET['lokBeli'] : '') : $_POST['lokBeli'];

if ($tglDr == '' || $tanggalSampai == '') {
    exit("Error: Period required");
}
$sKlmpk = "select kode,kelompok from " . $dbname . ".log_5klbarang order by kode";
$qKlmpk = $owlPDO->query($sKlmpk) or die(print " Gagal: " . PDOException::getMessage());
$qKlmpk->setFetchMode(PDO::FETCH_ASSOC);
while ($rKlmpk = $qKlmpk->fetch()) {
    $rKelompok[$rKlmpk['kode']] = $rKlmpk['kelompok'];
}
$arrStatus = array("" => "", "0" => "Head Office", "1" => "Local");
$sTgl = "select nopp,tanggal from " . $dbname . ".log_prapoht order by tanggal";
$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
$qTgl->setFetchMode(PDO::FETCH_ASSOC);
while ($rTgl = $qTgl->fetch()) {
    $rTglNopp[$rTgl['nopp']] = $rTgl['tanggal'];
}

$where = "";
if ($klmpkBrg != '' && $kdBrg != '') {
    $where.=" and kodebarang='" . $kdBrg . "'";
} elseif ($klmpkBrg != '') {
    $where.=" and substr(kodebarang,1,3)='" . $klmpkBrg . "'";
}

if (($tglDr != '') || ($tanggalSampai != '')) {
    if ((strlen($tglDr) > '4') && (strlen($tanggalSampai) > '4')) {
        $where.=" and substr(tanggal,1,7) between '" . $tglDr . "' and '" . $tanggalSampai . "'";
        $blnThn = "  substr(tanggal,1,7) between '" . $tglDr . "' and '" . $tanggalSampai . "'";
    } elseif ((strlen($tglDr) == '4') && (strlen($tanggalSampai) == '4')) {
        $where.=" and substr(tanggal,1,4) between '" . $tglDr . "' and '" . $tanggalSampai . "'";
        $blnThn = " substr(tanggal,1,4) between '" . $tglDr . "' and '" . $tanggalSampai . "'";
    } else {
        exit("Error: period required");
    }
}
if ($lokBeli != '') {
    $where.=" and lokalpusat='" . $lokBeli . "'";
}
$sBln = "select distinct substring(tanggal,1,7) as periode from " . $dbname . ".log_poht where " . $blnThn . " order by substring(tanggal,1,7) asc";
$qBln=$owlPDO->query($sBln) or die(print " Gagal: ".PDOException::getMessage());
$qBln->setFetchMode(PDO::FETCH_ASSOC);
$totPeriode = 0;
while ($rBln = $qBln->fetch()) {
    $dtBLn[] = $rBln['periode'];
    $totPeriode+=1;
}

switch ($proses) {
    case'getBrg':
        //echo "warning:masuk";

        $optorg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $sOrg = "select kodebarang,namabarang from " . $dbname . ".log_5masterbarang where kelompokbarang='" . $klmpkBrg . "'";
		$qOrg=$owlPDO->query($sOrg) or die(print " Gagal: ".PDOException::getMessage());
		$qOrg->setFetchMode(PDO::FETCH_ASSOC);
        while ($rOrg = $qOrg->fetch()) {
            $optorg.="<option value=" . $rOrg['kodebarang'] . ">" . $rOrg['namabarang'] . "</option>";
        }
        echo $optorg;
        break;

    case'preview':
        //$totPeriode=count($dtBln);

        if (($tglDr == '') || ($tanggalSampai == '')) {
            echo"warning: Period required";
            exit();
        }
        $tab = "<table cellspacing=1 border=0 class=sortable>
        <thead >
        <tr class=rowheader >
                <th rowspan=2 align=center>No.</th>
                <th rowspan=2 align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
                <th rowspan=2 align=center>" . $_SESSION['lang']['namabarang'] . "</th>
                <th rowspan=2 align=center>" . $_SESSION['lang']['satuan'] . "</th>";
        foreach ($dtBLn as $brsBln) {
            $tab.="<th align=center colspan=5>" . $brsBln . "</th>";
        }
        $tab.="</tr>";
        $tab.="<tr class=rowheader >";
        for ($a = 1; $a <= $totPeriode; $a++) {
            $tab.="<th align=center>" . $_SESSION['lang']['tanggal'] . "</th><th colspan=2 align=center>" . $_SESSION['lang']['harga'] . "</th><th align=center>" . $_SESSION['lang']['namasupplier'] . "</th><th align=center>" . $_SESSION['lang']['status'] . "</th>";
        }
        $tab.="</tr>";
        $tab.="
        </thead>
        <tbody>";
        $data = array();
        $brs = 1;
        $sData = "SELECT * FROM " . $dbname . ".`log_po_vw` WHERE statuspo>1 " . $where . " order by kodebarang,tanggal asc";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
        while ($rData = $qData->fetch()) {
            $periode = substr($rData['tanggal'], 0, 7);
            $dataPrice[$periode][$rData['kodebarang']] = $rData['hargasatuan'];
            $dataNmSupplier[$periode][$rData['kodebarang']] = $rData['namasupplier'];
            $dataTgl[$periode][$rData['kodebarang']] = $rData['tanggal'];
            $dataMtUang[$periode][$rData['kodebarang']] = $rData['matauang'];
            $dataStatus[$periode][$rData['kodebarang']] = $rData['lokalpusat'];
        }
        $sData2 = "SELECT kodebarang,namabarang,satuan FROM " . $dbname . ".`log_po_vw` WHERE statuspo>1 " . $where . " group by kodebarang order by kodebarang,tanggal asc";
		$qData2=$owlPDO->query($sData2) or die(print " Gagal: ".PDOException::getMessage());
		$qData2->setFetchMode(PDO::FETCH_ASSOC);
		$brsData=owlBaris($qData2);
        if ($brsData < 1) {
            exit("Error: No data found");
        } else {
            while ($rData2 = $qData2->fetch()) {
                $dataBrg[$rData2['kodebarang']] = $rData2['kodebarang'];
                $namaBrg[$rData2['kodebarang']] = $rData2['namabarang'];
                $satuanBrg[$rData2['kodebarang']] = $rData2['satuan'];
            }
            $sData3 = "SELECT tanggal FROM " . $dbname . ".`log_po_vw` WHERE statuspo>1 " . $where . " order by kodebarang,tanggal asc";
			$qData3=$owlPDO->query($sData3) or die(print " Gagal: ".PDOException::getMessage());
			$qData3->setFetchMode(PDO::FETCH_ASSOC);
            while ($rData3 = $qData3->fetch()) {
                $dataTgl[] = $rData3['tanggal'];
            }
            $no = 0;
            foreach ($dataBrg as $row => $rList) {
                $no+=1;
                $tab.="<tr class='rowcontent'>";
                $tab.="<td align=center>" . $no . "</td>";
                $tab.="<td align=center>" . $rList . "</td>";
                $tab.="<td>" . $namaBrg[$rList] . "</td>";
                $tab.="<td>" . $satuanBrg[$rList] . "</td>";
                foreach ($dtBLn as $brsBln) {
                    if (!isset($dataTgl[$brsBln][$rList]))
                        $dataTgl[$brsBln][$rList] = '';
                    if (!isset($dataMtUang[$brsBln][$rList]))
                        $dataMtUang[$brsBln][$rList] = '';
                    if (!isset($dataPrice[$brsBln][$rList]))
                        $dataPrice[$brsBln][$rList] = 0;
                    if (!isset($dataNmSupplier[$brsBln][$rList]))
                        $dataNmSupplier[$brsBln][$rList] = '';
                    if (!isset($dataStatus[$brsBln][$rList]))
                        $dataStatus[$brsBln][$rList] = '';
                    $tab.="<td>" . tanggalnormal($dataTgl[$brsBln][$rList]) . "</td>";
                    $tab.="<td>" . $dataMtUang[$brsBln][$rList] . "</td>";
                    $tab.="<td align=right>" . number_format($dataPrice[$brsBln][$rList], 2) . "</td>";
                    $tab.="<td>" . $dataNmSupplier[$brsBln][$rList] . "</td>";
                    $tab.="<td>" . $arrStatus[$dataStatus[$brsBln][$rList]] . "</td>";
                }
                $tab.="</tr>";
            }
            $tab.="</tbody></table>";
            echo $tab;
        }
        break;
    case'pdf':
        if (($tglDr == '') || ($tanggalSampai == '')) {
            echo"warning: Period required";
            exit();
        }

        class PDF extends FPDF {

            function Header() {
                global $conn;
                global $dbname;
                global $align;
                global $length;
                global $colArr;
                global $title;
                global $klmpkBrg;
                global $kdBrg;
                global $tglDr;
                global $tanggalSampai;
                global $where;
                global $isi;
                global $rNamaBarang;
                global $rNamaSupplier;
                global $where;
                global $owlPDO;

                $isi = array();

                # Alamat & No Telp
                /*         $query = selectQuery($dbname,'organisasi','namaorganisasi,alamat,telepon',
                  "kodeorganisasi='".$kdPt."'");
                  $orgData = fetchData($query); */
                $sAlmat = "select namaorganisasi,alamat,telepon from " . $dbname . ".organisasi where kodeorganisasi='" . $_SESSION['org']['kodeorganisasi'] . "'";
				$qAlamat=$owlPDO->query($sAlmat) or die(print " Gagal: ".PDOException::getMessage());
				$qAlamat->setFetchMode(PDO::FETCH_ASSOC);
                $rAlamat = $qAlamat->fetch();

                $width = $this->w - $this->lMargin - $this->rMargin;
                $height = 11;
                $path = 'images/logo.jpg';
                $this->Image($path, $this->lMargin, $this->tMargin, 0, 55);
                $this->SetFont('Arial', 'B', 9);
                $this->SetFillColor(255, 255, 255);
                $this->SetX(100);
                $this->Cell($width - 100, $height, $rAlamat['namaorganisasi'], 0, 1, 'L');
                $this->SetX(100);
                $this->Cell($width - 100, $height, $rAlamat['alamat'], 0, 1, 'L');
                $this->SetX(100);
                $this->Cell($width - 100, $height, "Tel: " . $rAlamat['telepon'], 0, 1, 'L');
                $this->Line($this->lMargin, $this->tMargin + ($height * 4), $this->lMargin + $width, $this->tMargin + ($height * 4));
                $this->Ln();
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'B', 11);
                $this->Cell($width, $height, $_SESSION['lang']['detPembBrg'], 0, 1, 'C');
                $this->SetFont('Arial', '', 8);
                $this->Cell($width, $height, "Periode : " . $_GET['tglDr'] . " s.d. " . $_GET['tanggalSampai'], 0, 1, 'C');
                $this->Ln();
                $this->Ln();
                $this->SetFont('Arial', 'B', 7);
                $this->SetFillColor(220, 220, 220);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['kodebarang'], 1, 0, 'C', 1);
                $this->Cell(20 / 100 * $width, $height, $_SESSION['lang']['namabarang'], 1, 0, 'C', 1);
                $this->Cell(14 / 100 * $width, $height, $_SESSION['lang']['nopo'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['tanggal'], 1, 0, 'C', 1);
                $this->Cell(5 / 100 * $width, $height, $_SESSION['lang']['jumlah'], 1, 0, 'C', 1);
                $this->Cell(4 / 100 * $width, $height, $_SESSION['lang']['satuan'], 1, 0, 'C', 1);
                $this->Cell(7 / 100 * $width, $height, $_SESSION['lang']['hargasatuan'], 1, 0, 'C', 1);
                $this->Cell(7 / 100 * $width, $height, $_SESSION['lang']['total'], 1, 0, 'C', 1);
                $this->Cell(15 / 100 * $width, $height, $_SESSION['lang']['namasupplier'], 1, 0, 'C', 1);
                $this->Cell(12 / 100 * $width, $height, $_SESSION['lang']['nopp'], 1, 0, 'C', 1);
                $this->Cell(6 / 100 * $width, $height, $_SESSION['lang']['tanggal'] . " PP", 1, 1, 'C', 1);
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
            }

        }

        $pdf = new PDF('L', 'pt', 'A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 11;
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 7);
        $sData = "select distinct kodebarang,namasupplier,namabarang,kurs,nopo,jumlahpesan,hargasatuan,nopp,satuan,tanggal from " . $dbname . ".log_po_vw  
        where statuspo>1 " . $where . " order by kodebarang ,tanggal asc";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
        $kdBrng = "";
        while ($rData = $qData->fetch()) {
            $data[] = $rData;
        }
        $totalAll = array();
        foreach ($data as $test => $dt) {

            if ($dt['kodebarang'] != '') {

                if ($dt['matauang'] != 'IDR') {
                    if ($dt['kurs'] == 1 || $dt['kurs'] == '') {
                        $sKurs = "select kurs from " . $dbname . ".setup_matauangrate where kode='" . $dt['matauang'] . "' and daritanggal='" . $dt['tanggal'] . "'";
						$qKurs=$owlPDO->query($sKurs) or die(print " Gagal: ".PDOException::getMessage());
						$qKurs->setFetchMode(PDO::FETCH_ASSOC);
						$rKurs = $qKurs->fetch();
                        if ($rKurs['kurs'] != '') {
                            $hrg = $rKurs['kurs'] * $dt['hargasatuan'];
                            $totHrg = $dt['jumlahpesan'] * $hrg;
                        } else {
                            if ($dt['matauang'] == 'USD') {
                                $hrg = $dt['hargasatuan'] * 8850;
                                $totHrg = $dt['jumlahpesan'] * $hrg;
                                $dt['matauang'] = "IDR";
                            } elseif ($dt['matauang'] == 'EUR') {
                                $hrg = $dt['hargasatuan'] * 12643;
                                $totHrg = $dt['jumlahpesan'] * $hrg;
                                $dt['matauang'] = "IDR";
                            } elseif (($dt['matauang'] == '') || ($dt['matauang'] == 'NULL')) {
                                $totHrg = $dt['jumlahpesan'] * $dt['hargasatuan'];
                            }
                        }
                    } else {
                        $hrg = $dt['kurs'] * $dt['hargasatuan'];
                        $totHrg = $dt['jumlahpesan'] * $hrg;
                    }
                } else {
                    $totHrg = $dt['jumlahpesan'] * $dt['hargasatuan'];
                }
                //$totHrg=$rList['jumlahpesan']*$rList['hargasatuan'];
                $grandTot['total']+=$totHrg;
                if ($dt['nopp'] != "") {
                    if (($rTglNopp[$dt['nopp']] != "") || ($rTglNopp[$dt['nopp']] != "000-00-00")) {
                        $tglPP = tanggalnormal($rTglNopp[$dt['nopp']]);
                    } else {
                        $tglPP = "";
                    }
                } else {
                    $tglPP = "";
                }



                if ($klmpkBarang != substr($dt['kodebarang'], 0, 3)) {
                    $brs = 1;
                }
                if ($brs == 1) {
                    $pdf->SetFont('Arial', 'B', 8);
                    $klmpkBarang = substr($dt['kodebarang'], 0, 3);
                    $pdf->Cell(6 / 100 * $width, $height, substr($dt['kodebarang'], 0, 3), 'TLR', 0, 'C', 1);
                    $pdf->Cell(20 / 100 * $width, $height, $rKelompok[$klmpkBarang], 'TLR', 0, 'L', 1);
                    $pdf->Cell(76 / 100 * $width, $height, '', 'TLR', 1, 'C', 1);
                    $brs = 0;
                }
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(6 / 100 * $width, $height, $dt['kodebarang'], 1, 0, 'C', 1);
                $pdf->Cell(20 / 100 * $width, $height, $dt['namabarang'], 1, 0, 'L', 1);
                $pdf->Cell(14 / 100 * $width, $height, $dt['nopo'], 1, 0, 'L', 1);
                $pdf->Cell(6 / 100 * $width, $height, tanggalnormal($dt['tanggal']), 1, 0, 'C', 1);
                $pdf->Cell(5 / 100 * $width, $height, $dt['jumlahpesan'], 1, 0, 'R', 1);
                $pdf->Cell(4 / 100 * $width, $height, $dt['satuan'], 1, 0, 'C', 1);
                $pdf->Cell(7 / 100 * $width, $height, number_format($dt['hargasatuan'], 0), 1, 0, 'R', 1);
                $pdf->Cell(7 / 100 * $width, $height, number_format($totHrg, 0), 1, 0, 'R', 1);
                $pdf->Cell(15 / 100 * $width, $height, $dt['namasupplier'], 1, 0, 'L', 1);
                $pdf->Cell(12 / 100 * $width, $height, $dt['nopp'], 1, 0, 'L', 1);
                $pdf->Cell(6 / 100 * $width, $height, $tglPP, 1, 1, 'C', 1);
            }
        }


        $pdf->Output();
        break;

    case'excel':
        if (($tglDr == '') || ($tanggalSampai == '')) {
            echo"warning: Period required";
            exit();
        }
        $tab = "<table cellspacing=1 border=1 class=sortable>
        <thead >
        <tr class=rowheader >
                <th rowspan=2 bgcolor=#DEDEDE align=center >No.</th>
                <th rowspan=2 bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['kodebarang'] . "</th>
                <th rowspan=2 bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['namabarang'] . "</th>
                <th rowspan=2 bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['satuan'] . "</th>";
        foreach ($dtBLn as $brsBln) {
            $tab.="<th bgcolor=#DEDEDE align=center colspan=5>" . $brsBln . "</th>";
        }
        $tab.="</tr>";
        $tab.="<tr class=rowheader >";
        for ($a = 1; $a <= $totPeriode; $a++) {
            $tab.="<th bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['tanggal'] . "</th><th colspan=2 bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['harga'] . "</th><th bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['namasupplier'] . "</th><th bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['status'] . "</th>";
        }
        $tab.="</tr>";
        $tab.="
        </thead>
        <tbody>";
        $data = array();
        $brs = 1;
        $sData = "SELECT * FROM " . $dbname . ".`log_po_vw` WHERE statuspo>1 " . $where . " order by kodebarang,tanggal asc";
		$qData=$owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
		$qData->setFetchMode(PDO::FETCH_ASSOC);
        while ($rData = $qData->fetch()) {
            $periode = substr($rData['tanggal'], 0, 7);
            $dataPrice[$periode][$rData['kodebarang']] = $rData['hargasatuan'];
            $dataNmSupplier[$periode][$rData['kodebarang']] = $rData['namasupplier'];
            $dataTgl[$periode][$rData['kodebarang']] = $rData['tanggal'];
            $dataMtUang[$periode][$rData['kodebarang']] = $rData['matauang'];
            $dataStatus[$periode][$rData['kodebarang']] = $rData['lokalpusat'];
        }
        $sData2 = "SELECT kodebarang,namabarang,satuan FROM " . $dbname . ".`log_po_vw` WHERE statuspo>1 " . $where . " group by kodebarang order by kodebarang,tanggal asc";
        $qData2=$owlPDO->query($sData2) or die(print " Gagal: ".PDOException::getMessage());
		$qData2->setFetchMode(PDO::FETCH_ASSOC);
		$brsData=owlBaris($qData2);
		if ($brsData < 1) {
            exit("Error: No data found");
        } else {
            while ($rData2 = $qData2->fetch()) {
                $dataBrg[$rData2['kodebarang']] = $rData2['kodebarang'];
                $namaBrg[$rData2['kodebarang']] = $rData2['namabarang'];
                $satuanBrg[$rData2['kodebarang']] = $rData2['satuan'];
            }
            $sData3 = "SELECT tanggal FROM " . $dbname . ".`log_po_vw` WHERE statuspo>1 " . $where . " order by kodebarang,tanggal asc";
			$qData3=$owlPDO->query($sData3) or die(print " Gagal: ".PDOException::getMessage());
			$qData3->setFetchMode(PDO::FETCH_ASSOC);
            while ($rData3 = $qData3->fetch()) {
                $dataTgl[] = $rData3['tanggal'];
            }
            $no = 0;
            foreach ($dataBrg as $row => $rList) {
                $no+=1;
                $tab.="<tr class='rowcontent'>";
                $tab.="<td>" . $no . "</td>";
                $tab.="<td>" . $rList . "</td>";
                $tab.="<td>" . $namaBrg[$rList] . "</td>";
                $tab.="<td>" . $satuanBrg[$rList] . "</td>";
                foreach ($dtBLn as $brsBln) {
                    if (!isset($dataTgl[$brsBln][$rList]))
                        $dataTgl[$brsBln][$rList] = '';
                    if (!isset($dataMtUang[$brsBln][$rList]))
                        $dataMtUang[$brsBln][$rList] = '';
                    if (!isset($dataPrice[$brsBln][$rList]))
                        $dataPrice[$brsBln][$rList] = 0;
                    if (!isset($dataNmSupplier[$brsBln][$rList]))
                        $dataNmSupplier[$brsBln][$rList] = '';
                    if (!isset($dataStatus[$brsBln][$rList]))
                        $dataStatus[$brsBln][$rList] = '';
                    $tab.="<td>" . tanggalnormal($dataTgl[$brsBln][$rList]) . "</td>";
                    $tab.="<td>" . $dataMtUang[$brsBln][$rList] . "</td>";
                    $tab.="<td align=right>" . number_format($dataPrice[$brsBln][$rList], 2) . "</td>";
                    $tab.="<td>" . $dataNmSupplier[$brsBln][$rList] . "</td>";
                    $tab.="<td>" . $arrStatus[$dataStatus[$brsBln][$rList]] . "</td>";
                }
                $tab.="</tr>";
            }
            $tab.="</tbody></table>";
            // echo $tab;
        }


        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $thisDate = date("YmdHms");
        //$nop_="Laporan_Pembelian";
        $nop_ = "Laporan_Pembelian_Terakhir_" . $thisDate;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls.gz';
                            </script>";

        break;
    case'getTgl':
        if ($periode != '') {
            $tgl = $periode;
            $tanggal = $tgl[0] . "-" . $tgl[1];
        } elseif ($period != '') {
            $tgl = $period;
            $tanggal = $tgl[0] . "-" . $tgl[1];
        }
        if ($kdUnit == '') {
            $kdUnit = $_SESSION['lang']['lokasitugas'];
        }
        $sTgl = "select distinct tanggalmulai,tanggalsampai from " . $dbname . ".sdm_5periodegaji where kodeorg='" . substr($kdUnit, 0, 4) . "' and periode='" . $tanggal . "' ";
		$qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
		$qTgl->setFetchMode(PDO::FETCH_ASSOC);
        $rTgl = $qTgl->fetch();
        echo tanggalnormal($rTgl['tanggalmulai']) . "###" . tanggalnormal($rTgl['tanggalsampai']);
        break;

    default:
        break;
}
?>