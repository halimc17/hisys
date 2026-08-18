<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$kontaktor = checkPostGet('kontaktor', '');
$tahun = checkPostGet('tahun', '');
$periode = checkPostGet('periode', '');

// $eret = " kodekelompok='K001'";
// $optNmorg = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', $eret);
$optNmKdorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$optKeg = makeOption($dbname, 'log_baspk', 'notransaksi,kodekegiatan');
$optNmKeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
$thn = substr($periode, 0, 4);
$bln = substr($periode, 5, 2);
$start = $thn . "-01-01";
// $wktu = mktime(0, 0, 0, $bln, 15, $thn);
// $end = $thn . "-" . $bln . "-" . date('t', $wktu);
if ($proses != 'getUnit') {
    //getSPK
    $hwr = $hwr1 = $hwr2 = '';
    if ($kontaktor != '') {
        $hwr = "and koderekanan='" . $kontaktor . "'";
        $hwr1 = " and kodesupplier='" . $kontaktor . "' and kodesupplier!=''";
        $hwr2 = " and a.kodesupplier='" . $kontaktor . "' and b.kodesupplier!=''";
    }
    $sSpk = "select distinct a.notransaksi,b.namasupplier,a.keterangan,a.kodeorg,c.kodekegiatan,nilaikontrak
        from " . $dbname . ".log_spkht a 
            left join " . $dbname . ".log_5supplier b on a.koderekanan = b.supplierid
            left JOIN " . $dbname . ".log_spkdt c on c.notransaksi = a.notransaksi
            left JOIN " . $dbname . ".setup_kegiatan d on d.kodekegiatan = c.kodekegiatan
            where left(a.tanggal,4)='" . $tahun . "'  " . $hwr . " group by a.notransaksi";
    $qSpk=$owlPDO->query($sSpk) or die(print " Gagal: ".PDOException::getMessage());
	$qSpk->setFetchMode(PDO::FETCH_ASSOC);
	while ($rSpk = $qSpk->fetch()) {
        $noTrans[$rSpk['notransaksi']] = $rSpk['notransaksi'];
        $noSupp[$rSpk['notransaksi']] = $rSpk['namasupplier'];
        $noKeg[$rSpk['notransaksi']] = $rSpk['keterangan'];
        $noUnit[$rSpk['notransaksi']] = $rSpk['kodeorg'];
        $nilKontrak[$rSpk['notransaksi']] = $rSpk['nilaikontrak'];
    }
    $sBspk = "select distinct sum(jumlahrealisasi) as nilaiKont,tanggal,kodekegiatan, notransaksi 
            from " . $dbname . ".log_baspk where left(tanggal,4)='" . $tahun . "'  
            group by notransaksi";
    $qBspk=$owlPDO->query($sBspk) or die(print " Gagal: ".PDOException::getMessage());
	$qBspk->setFetchMode(PDO::FETCH_ASSOC);
	while ($rBspk = $qBspk->fetch()) {
        $noUang[$rBspk['notransaksi']] = $rBspk['nilaiKont'];
        $noTglBpp[$rBspk['notransaksi']] = $rBspk['tanggal'];
        //$noTrans[$rBspk['notransaksi']]=$rBspk['notransaksi'];
    }

    $sInvoice = "select distinct noinvoice,nopo,sum(nilaiinvoice+nilaippn) as jmlhTag,jatuhtempo,keterangan from " . $dbname . ".keu_tagihanht where 
               tipeinvoice='K' and left(tanggal,4)='" . $tahun . "' and  posting=1 " . $hwr1 . " group by noinvoice,nopo order by nopo asc";
    $ert = '';
    $qInvoice=$owlPDO->query($sInvoice) or die(print " Gagal: ".PDOException::getMessage());
	$qInvoice->setFetchMode(PDO::FETCH_ASSOC);
	while ($rInvoice = $qInvoice->fetch()) {
        if ($ert != $rInvoice['nopo']) {//013/GMO/MA-BTA/XII/2011
            $ert = $rInvoice['nopo'];
            $totRw2 = 1;
        }

        $noInvoice[$rInvoice['nopo']][$totRw2] = $rInvoice['noinvoice'];
        $noInvoiceSupp[$rInvoice['nopo']][$totRw2] = $rInvoice['keterangan'];
        $noTglJthtem[$rInvoice['nopo']][$totRw2] = $rInvoice['jatuhtempo'];
        $noNilInv[$rInvoice['nopo']][$totRw2] = $rInvoice['jmlhTag'];
        $noTrans[$rInvoice['nopo']] = $rInvoice['nopo'];
        $jmlhRow2[$rInvoice['nopo']] = $totRw2;
        $totRw2+=1;
    }
	
	$sJur = "select distinct noinvoice,nopo,sum(b.jumlah) as jmlhTag,c.tanggal from " . $dbname . ".keu_tagihanht a
           left join " . $dbname . ".keu_kasbankdt b on a.noinvoice=b.keterangan1
           left join " . $dbname . ".keu_kasbankht c on b.notransaksi=c.notransaksi
           where  tipeinvoice='K' and left(a.tanggal,4)='" . $tahun . "' and a.posting=1
           and (keterangan1!='' and keterangan1!=0) and c.posting=1
           and b.tipetransaksi !='M'  " . $hwr2 . "  group by noinvoice order by nopo asc";
    $qJur=$owlPDO->query($sJur) or die(print " Gagal: ".PDOException::getMessage());
	$qJur->setFetchMode(PDO::FETCH_ASSOC);
	while ($rJur = $qJur->fetch()) {
        $arrtglbyr[$rJur['nopo']][$rJur['noinvoice']] = $rJur['tanggal'];
        $dByr[$rJur['nopo']][$rJur['noinvoice']] = $rJur['jmlhTag'];
    }

    if ($proses == 'excel') {
        $bg = " bgcolor=#DEDEDE";
        $brdr = 1;
    } else {
        $bg = "";
        $brdr = 0;
    }

    if ($proses == 'excel') {
        $bgcoloraja = "bgcolor=#DEDEDE ";
        $brdr = 1;
        $tab = "
    <table>
    <tr><td colspan=5 align=center><b>Contract Progress Summary</b></td><td colspan=6 align=right><b>" . $_SESSION['lang']['periode'] . " : " . substr(tanggalnormal($periode), 1, 7) . "</b></td></tr>
    <tr><td colspan=5 align=left>&nbsp;</td></tr>
    </table>";
    }

    $tab = "<table class=sortable cellspacing=1 border=".$brdr.">
        <thead>
            <tr>
               
                <td align=center>" . $_SESSION['lang']['nospk'] . "</td>
                <td align=center>" . $_SESSION['lang']['kontraktor'] . "</td>
                <td align=center>" . $_SESSION['lang']['nilaikontrak'] . "</td>
                <td align=center>" . $_SESSION['lang']['lokasi'] . "</td>
                <td align=center>" . $_SESSION['lang']['pt'] . "</td>
                <td align=center>" . $_SESSION['lang']['kegiatan'] . "</td>
                <td align=center>" . $_SESSION['lang']['tanggal'] . "</td>
                <td align=center>" . $_SESSION['lang']['kontrak'] . " " . $_SESSION['lang']['jumlah'] . "</td>  
                <td align=center>" . $_SESSION['lang']['noinvoice'] . " " . $_SESSION['lang']['supplier'] . " </td>
                <td align=center>" . $_SESSION['lang']['noinvoice'] . " Internal</td>
                <td align=center>" . $_SESSION['lang']['nilaiinvoice'] . " (Rp.)</td>
                <td align=center>" . $_SESSION['lang']['tgljatuhtempo'] . "</td>
                <td align=center>" . $_SESSION['lang']['pembayaran'] . "</td>
                <td align=center>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['pembayaran'] . "</td>
                <td align=center>" . $_SESSION['lang']['sisa'] . "</td> 
            </tr>
        </thead>
        <tbody id=container>";
    foreach ($noTrans as $der => $isi) {
        $aawal = 1;
        setIt($noInvoiceSupp[$isi][$aawal], '');
        setIt($noKeg[$isi], '');
        setIt($noTglBpp[$isi], '');
        setIt($noUang[$isi], 0);
        setIt($noUnit[$isi], '');
        setIt($noSupp[$isi], '');
        setIt($nilKontrak[$isi], 0);

        setIt($noInvoice[$isi][$aawal], '');
        setIt($noNilInv[$isi][$aawal], 0);
        setIt($noTglJthtem[$isi][$aawal], 0);

        if (empty($jmlhRow2[$isi])) {
            $tab.="<tr class=rowcontent>";
            $tab.="<td>" . $isi . "</td>";
            $tab.="<td>" . $noSupp[$isi] . "</td>";
            $tab.="<td align=right>" . number_format($nilKontrak[$isi], 0) . "</td>";
            $tab.="<td>" . $noUnit[$isi] . "</td>";
            $sInduk = "select distinct induk from " . $dbname . ".organisasi where kodeorganisasi='" . $noUnit[$isi] . "'";
            $qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
			$qInduk->setFetchMode(PDO::FETCH_ASSOC);
			$rInduk = $qInduk->fetch();
            $tab.="<td>" . $rInduk['induk'] . "</td>";
            if (empty($noKeg[$isi])) {
                $tab.="<td>" . $noInvoiceSupp[$isi][$aawal] . "</td>";
            } else {
                $tab.="<td>" . $noKeg[$isi] . "</td>";
            }
            $tab.="<td>" . $noTglBpp[$isi] . "</td>";
            $tab.="<td align=right>" . number_format($noUang[$isi], 0) . "</td>";
            $tab.="<td>" . $noInvoiceSupp[$isi][$aawal] . "</td>";
            $tab.="<td>" . $noInvoice[$isi][$aawal] . "</td>";

            $inv = $noInvoice[$isi][$aawal];
            setIt($dByr[$isi][$inv], 0);
            setIt($arrtglbyr[$isi][$inv], 0);
            $tab.="<td align=right>" . number_format($noNilInv[$isi][$aawal], 0) . "</td>";
            $tab.="<td>" . $noTglJthtem[$isi][$aawal] . "</td>";
            $sisa = $noNilInv[$isi][$aawal] - $dByr[$isi][$inv];
            $tab.="<td align=right>" . number_format($dByr[$isi][$inv], 0) . "</td>";
            $tab.="<td>" . $arrtglbyr[$isi][$inv] . "</td>";
            $tab.="<td align=right>" . number_format($sisa, 0) . "</td>";
            $tab.="</tr>";
        } else {
            for ($aawal = 1; $aawal <= $jmlhRow2[$isi]; $aawal++) {
                $sInduk = "select distinct induk from " . $dbname . ".organisasi where kodeorganisasi='" . $noUnit[$isi] . "'";
                $qInduk=$owlPDO->query($sInduk) or die(print " Gagal: ".PDOException::getMessage());
				$qInduk->setFetchMode(PDO::FETCH_ASSOC);
				$rInduk = $qInduk->fetch();
                $tab.="<tr class=rowcontent>";
                if ($aawal == 1) {
                    $tab.="<td>" . $isi . "</td>";
                    $tab.="<td>" . $noSupp[$isi] . "</td>";
                    $tab.="<td align=right>" . number_format($nilKontrak[$isi], 0) . "</td>";
                    $tab.="<td>" . $noUnit[$isi] . "</td>";
                    setIt($optNmKdorg[$rInduk['induk']], '');
                    $tab.="<td>" . $rInduk['induk'] . "</td>";
                } else if ($aawal == 2) {
                    $tab.="<td rowspan='" . ($jmlhRow2[$isi] - 1) . "' colspan=5>&nbsp;</td>";
                }
                if (empty($noKeg[$isi])) {
                    $tab.="<td>" . $noInvoiceSupp[$isi][$aawal] . "</td>";
                } else {
                    $tab.="<td>" . $noKeg[$isi] . "</td>";
                }
                if (!isset($tglBapp) or $tglBapp != $noTglBpp[$isi]) {
                    $tglBapp = $noTglBpp[$isi];
                    $tab.="<td>" . $noTglBpp[$isi] . "</td>";
                    $tab.="<td align=right>" . number_format($noUang[$isi], 0) . "</td>";
                } else {
                    $tab.="<td colspan=2>&nbsp</td>";
                }

                $tab.="<td>" . $noInvoiceSupp[$isi][$aawal] . "</td>";
                if ($noInvoice[$isi][$aawal] != '') {
                    $tab.="<td>" . $noInvoice[$isi][$aawal] . "</td>";
                } else {
                    $tab.="<td>" . $noInvoice[$isi][$aawal] . "</td>";
                }
                $inv = $noInvoice[$isi][$aawal];
                setIt($dByr[$isi][$inv], 0);
                setIt($arrtglbyr[$isi][$inv], 0);
                $tab.="<td align=right>" . number_format($noNilInv[$isi][$aawal], 0) . "</td>";
                $tab.="<td>" . $noTglJthtem[$isi][$aawal] . "</td>";
                $sisa = $noNilInv[$isi][$aawal] - $dByr[$isi][$inv];
                $tab.="<td align=right>" . number_format($dByr[$isi][$inv], 0) . "</td>";
                $tab.="<td>" . $arrtglbyr[$isi][$inv] . "</td>";
                $tab.="<td align=right>" . number_format($sisa, 0) . "</td>";
                $tab.="</tr>";
            }
        }
        $sisa = 0;
    }


    $tab.=" </tbody></table>";
}
switch ($proses) {
    case'preview':
        //exit("Error:".$str);
        echo $tab;
        break;

    case'excel':
//    if($periode=='')
//    {
//        exit("Error:Field Tidak Boleh Kosong");
//    }

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
        $dte = date("YmdHis");
        $nop_ = "Summary_Progress_SPK_2" . $periode;
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
            parent.window.alert('Can't convert to excel format');
            </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls';
            </script>";
            }
            fclose($handle);
        }
        break;

    case'pdf':
        if ($periode == '') {
            exit("Error:Field Tidak Boleh Kosong");
        }

        $cols = 247.5;
        $wkiri = 50;
        $wlain = 11;

        class PDF extends FPDF {

            function Header() {
                global $periode;
                global $dbname;
                global $wkiri, $wlain;
                $width = $this->w - $this->lMargin - $this->rMargin;

                $this->SetFont('Arial', 'B', 8);
                $this->Cell($width, $height, strtoupper("SUMMARY PROGRESS SPK"), 0, 1, 'L');
                $this->Cell($width, $height, $_SESSION['lang']['periode'] . ' : ' . substr(tanggalnormal($periode), 1, 7), 0, 1, 'R');
                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);
                $this->Cell(790, $height, ' ', 0, 1, 'R');

                $height = 15;
                $this->SetFillColor(220, 220, 220);
                $this->SetFont('Arial', 'B', 8);

                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);

                $this->Cell(15, $height, "No.", TLR, 0, 'C', 1);
                $this->Cell(110, $height, "No SPK", TLR, 0, 'C', 1);
                $this->Cell(50, $height, "Tanggal", TLR, 0, 'C', 1);
                $this->Cell(200, $height, "Kegiatan", TLR, 0, 'C', 1);
                $this->Cell(100, $height, "Kontraktor", TLR, 0, 'C', 1);
                $this->Cell(50, $height, "Rp. Kontrak", TLR, 0, 'C', 1);
                $this->Cell(100, $height, "Rp. Realisasi", TLR, 0, 'C', 1);
                $this->Cell(100, $height, "Fisik Realisasi", TLR, 0, 'C', 1);
                $this->Cell(50, $height, "%", TLR, 1, 'C', 1);


                $this->Cell(15, $height, " ", BLR, 0, 'C', 1);
                $this->Cell(110, $height, " ", BLR, 0, 'C', 1);
                $this->Cell(50, $height, " ", BLR, 0, 'C', 1);
                $this->Cell(200, $height, " ", BLR, 0, 'C', 1);
                $this->Cell(100, $height, " ", BLR, 0, 'C', 1);
                $this->Cell(50, $height, " ", BLR, 0, 'C', 1);
                $this->Cell(50, $height, "BI", TBLR, 0, 'C', 1);
                $this->Cell(50, $height, "S/d BI", TBLR, 0, 'C', 1);
                $this->Cell(50, $height, "BI", TBLR, 0, 'C', 1);
                $this->Cell(50, $height, "S/d BI", TBLR, 0, 'C', 1);
                $this->Cell(50, $height, " ", BLR, 1, 'C', 1);
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 11);
                $this->Cell(10, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
            }

        }

        //================================

        $pdf = new PDF('L', 'pt', 'A4');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 10;
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', '', 7);
        $i = 0;
        foreach ($nospk as $spk => $lsspk) {
            $i++;

            $pdf->Cell(15, $height, $i, TBLR, 0, 'L', 1);
            $pdf->Cell(110, $height, $lsspk, TBLR, 0, 'L', 1);
            $pdf->Cell(50, $height, tanggalnormal($tgl[$lsspk]), TBLR, 0, 'C', 1);
            $pdf->Cell(200, $height, $kegiatan[$lsspk], TBLR, 0, 'L', 1);
            $pdf->Cell(100, $height, $kontraktor[$lsspk], TBLR, 0, 'L', 1);
            $pdf->Cell(50, $height, number_format($rpkontrak[$lsspk], 0), TBLR, 0, 'R', 1);

            $pdf->Cell(50, $height, number_format($DtBi[$lsspk], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(50, $height, number_format($DtSBi[$lsspk], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(50, $height, number_format($HkBi[$lsspk], 0), TBLR, 0, 'R', 1);
            $pdf->Cell(50, $height, number_format($HkSBi[$lsspk], 0), TBLR, 0, 'R', 1);
            @$persen = number_format((($DtSBi[$lsspk] / $rpkontrak[$lsspk]) * 100), 2);
            $pdf->Cell(50, $height, $persen, TBLR, 1, 'R', 1);
        }
        $pdf->Output();
        break;
    case'getUnit':
        $optUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $sUnit = "select distinct koderekanan,namasupplier from 
            " . $dbname . ".log_spkht a left join " . $dbname . ".log_5supplier b
            on a.koderekanan=b.supplierid where left(a.tanggal,4)='" . $tahun . "' order by namasupplier asc";
		$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
		$qUnit->setFetchMode(PDO::FETCH_ASSOC);
        while ($rUnit = $qUnit->fetch()) {
            $optUnit.="<option value='" . $rUnit['koderekanan'] . "'>" . $rUnit['namasupplier'] . "</option>";
        }
        echo $optUnit;
        break;
    default:
        break;
}
?>
