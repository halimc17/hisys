<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');

$proses = checkPostGet('proses', '');
$periode = checkPostGet('periode', '');
$regional = checkPostGet('regional', '');
$unit = checkPostGet('unit', '');
$optNmorg = "";
$optNmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$thn = substr($periode, 0, 4);
$bln = substr($periode, 5, 2);
$start = $thn . "-01-01";
$wktu = mktime(0, 0, 0, $bln, 15, $thn);
$end = $thn . "-" . $bln . "-" . date('t', $wktu);
if ($unit != '') {
    $wher = " and a.kodeorg='" . $unit . "'";
    $whr = " and substr(kodeblok,1,4)='" . $unit . "'";
} else {
    $i = "select count(*) as jumlah from " . $dbname . ".bgt_regional_assignment where regional='" . $regional . "'
    order by regional desc";
	$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
	$n->setFetchMode(PDO::FETCH_ASSOC);
    $d = $n->fetch();
    $jumreg = $d['jumlah'];

    if ($jumreg > 0) {
        $str = "select distinct * from " . $dbname . ".bgt_regional_assignment where regional='" . $regional . "'
        order by regional desc";
        $wher = "and a.kodeorg in (";
        $whr = " and substr(kodeblok,1,4) in (";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        $areder = 0;
        while ($bar = $res->fetch()) {
            $areder+=1;
            if ($areder == 1) {
                $wher.="'" . $bar['kodeunit'] . "'";
                $whr.="'" . $bar['kodeunit'] . "'";
            } else {
                $wher.=",'" . $bar['kodeunit'] . "'";
                $whr.=",'" . $bar['kodeunit'] . "'";
            }
        }
        $wher.=")";
        $whr.=")";
    } else {
        exit("Warning:Tidak ada unit diregional " . $regional . " ");
    }
}
if ($_SESSION['language'] == 'EN') {
    $zz = 'namakegiatan1 as namakegiatan';
} else {
    $zz = "namakegiatan";
}
$str = "select a.notransaksi,a.tanggal,a.nilaikontrak,b.namasupplier,d." . $zz . "
        from " . $dbname . ".log_spkht a 
            left join " . $dbname . ".log_5supplier b on a.koderekanan = b.supplierid
            left JOIN " . $dbname . ".log_spkdt c on c.notransaksi = a.notransaksi
            left JOIN " . $dbname . ".setup_kegiatan d on d.kodekegiatan = c.kodekegiatan
        where a.tanggal  between '" . $start . "' and '" . $end . "' " . $wher . "  group by a.notransaksi";
$query=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $nospk[$res['notransaksi']] = $res['notransaksi'];
    $tgl[$res['notransaksi']] = $res['tanggal'];
    $kontraktor[$res['notransaksi']] = $res['namasupplier'];
    $rpkontrak[$res['notransaksi']] = $res['nilaikontrak'];
    $kegiatan[$res['notransaksi']] = $res['namakegiatan'];
}

$sDataBi = "select notransaksi,sum(jumlahrealisasi) as jml,sum(hkrealisasi) as fisik
           from " . $dbname . ".log_baspk 
           where tanggal like '" . $periode . "%' " . $whr . " group by notransaksi";
$qDataBi=$owlPDO->query($sDataBi) or die(print " Gagal: ".PDOException::getMessage());
$qDataBi->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataBi = $qDataBi->fetch()) {
    $DtBi[$rDataBi['notransaksi']] = $rDataBi['jml'];
    $HkBi[$rDataBi['notransaksi']] = $rDataBi['fisik'];
}

$sDataSBi = "select notransaksi,sum(jumlahrealisasi) as jml,sum(hkrealisasi) as fisik
           from " . $dbname . ".log_baspk 
           where tanggal between '" . $start . "' and '" . $end . "'  " . $whr . " group by notransaksi";
$qDataSBi=$owlPDO->query($sDataSBi) or die(print " Gagal: ".PDOException::getMessage());
$qDataSBi->setFetchMode(PDO::FETCH_ASSOC);
while ($rDataSBi = $qDataSBi->fetch()) {
    $DtSBi[$rDataSBi['notransaksi']] = $rDataSBi['jml'];
    $HkSBi[$rDataSBi['notransaksi']] = $rDataSBi['fisik'];
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
if ($proses != 'getUnit') {
    $tab = "<table class=sortable cellspacing=1 border=".$brdr." width=100%>
        <thead>
            <tr>
                <td align=center rowspan=2>No.</td>
                <td align=center rowspan=2>" . $_SESSION['lang']['nospk'] . "</td>
                <td align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . "</td>
                <td align=center rowspan=2>" . $_SESSION['lang']['kegiatan'] . "</td>
                <td align=center rowspan=2>" . $_SESSION['lang']['kontraktor'] . "</td>
                <td align=center rowspan=2>" . $_SESSION['lang']['rpkontrak'] . "</td>
                <td align=center colspan=2>" . $_SESSION['lang']['rprealisasi'] . "</td>
                <td align=center colspan=2>" . $_SESSION['lang']['fisik'] . " " . $_SESSION['lang']['realisasi'] . "</td>    
                <td align=center rowspan=2>" . $_SESSION['lang']['%'] . "</td>
            </tr>  
            <tr>
                <td align=center>" . $_SESSION['lang']['bi'] . "</td>
                <td align=center>" . $_SESSION['lang']['sbi'] . "</td>
                <td align=center>" . $_SESSION['lang']['bi'] . "</td>
                <td align=center>" . $_SESSION['lang']['sbi'] . "</td> 
            </tr>
        </thead>
        <tbody id=container>";
    $i = 0;
    if (is_array($nospk)) {
        foreach ($nospk as $spk => $lsspk) {
            $i++;
            setIt($DtBi[$lsspk], 0);
            setIt($DtSBi[$lsspk], 0);
            setIt($HkBi[$lsspk], 0);
            setIt($HkSBi[$lsspk], 0);
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $i . "</td>";
            $tab.="<td align=left>" . $lsspk . "</td>";
            $tab.="<td align=center>" . tanggalnormal($tgl[$lsspk]) . "</td>";
            $tab.="<td align=left>" . $kegiatan[$lsspk] . "</td>";
            $tab.="<td align=left>" . $kontraktor[$lsspk] . "</td>";
            $tab.="<td align=right>" . number_format($rpkontrak[$lsspk], 0) . "</td>";
            $tab.="<td align=right>" . number_format($DtBi[$lsspk], 0) . "</td>";
            $tab.="<td align=right>" . number_format($DtSBi[$lsspk], 0) . "</td>";
            $tab.="<td align=right>" . number_format($HkBi[$lsspk], 0) . "</td>";
            $tab.="<td align=right>" . number_format($HkSBi[$lsspk], 0) . "</td>";
            @$persen = number_format((($DtSBi[$lsspk] / $rpkontrak[$lsspk]) * 100), 2);
            $tab.="<td align=right>" . $persen . "</td>";
            $tab.="</tr>";
        }
    }
    $tab.=" </tbody></table>";
}
switch ($proses) {
    case'preview':
        //exit("Error:".$str);
        echo $tab;
        break;

    case'excel':
        if ($periode == '') {
            exit("Error:Field Tidak Boleh Kosong");
        }

        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br />By:" . $_SESSION['empl']['name'];
        $dte = date("YmdHis");
        $nop_ = "Summary_Progress_SPK_" . $periode;
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
                $height = 15;

                $this->SetFont('Arial', 'B', 8);
                $this->Cell($width, $height, strtoupper("CONTRACT PROGRESS SUMMARY"), 0, 1, 'L');
                $this->Cell($width, $height, $_SESSION['lang']['periode'] . ' : ' . substr(tanggalnormal($periode), 1, 7), 0, 1, 'R');
                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);
                $this->Cell(790, $height, ' ', 0, 1, 'R');

                $this->SetFillColor(220, 220, 220);
                $this->SetFont('Arial', 'B', 8);

                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);

                $this->Cell(15, $height, "No.", 'TLR', 0, 'C', 1);
                $this->Cell(110, $height, "No SPK", 'TLR', 0, 'C', 1);
                $this->Cell(50, $height, "Tanggal", 'TLR', 0, 'C', 1);
                $this->Cell(200, $height, "Kegiatan", 'TLR', 0, 'C', 1);
                $this->Cell(100, $height, "Kontraktor", 'TLR', 0, 'C', 1);
                $this->Cell(50, $height, "Rp. Kontrak", 'TLR', 0, 'C', 1);
                $this->Cell(100, $height, "Rp. Realisasi", 'TLR', 0, 'C', 1);
                $this->Cell(100, $height, "Fisik Realisasi", 'TLR', 0, 'C', 1);
                $this->Cell(50, $height, "%", 'TLR', 1, 'C', 1);


                $this->Cell(15, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(110, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(50, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(200, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(100, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(50, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(50, $height, "BI", 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, "S/d BI", 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, "BI", 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, "S/d BI", 'TBLR', 0, 'C', 1);
                $this->Cell(50, $height, " ", 'BLR', 1, 'C', 1);
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

            $pdf->Cell(15, $height, $i, 'TBLR', 0, 'L', 1);
            $pdf->Cell(110, $height, $lsspk, 'TBLR', 0, 'L', 1);
            $pdf->Cell(50, $height, tanggalnormal($tgl[$lsspk]), 'TBLR', 0, 'C', 1);
            $pdf->Cell(200, $height, $kegiatan[$lsspk], 'TBLR', 0, 'L', 1);
            $pdf->Cell(100, $height, $kontraktor[$lsspk], 'TBLR', 0, 'L', 1);
            $pdf->Cell(50, $height, number_format($rpkontrak[$lsspk], 0), 'TBLR', 0, 'R', 1);

            $pdf->Cell(50, $height, number_format($DtBi[$lsspk], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(50, $height, number_format($DtSBi[$lsspk], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(50, $height, number_format($HkBi[$lsspk], 0), 'TBLR', 0, 'R', 1);
            $pdf->Cell(50, $height, number_format($HkSBi[$lsspk], 0), 'TBLR', 0, 'R', 1);
            @$persen = number_format((($DtSBi[$lsspk] / $rpkontrak[$lsspk]) * 100), 2);
            $pdf->Cell(50, $height, $persen, 'TBLR', 1, 'R', 1);
        }
        $pdf->Output();
        break;
    case'getUnit':
        $optUnit = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        $sUnit = "select distinct * from " . $dbname . ".bgt_regional_assignment where regional='" . $regional . "' order by kodeunit ";
		$qUnit=$owlPDO->query($sUnit) or die(print " Gagal: ".PDOException::getMessage());
		$qUnit->setFetchMode(PDO::FETCH_ASSOC);
        while ($rUnit = $qUnit->fetch()) {
            $optUnit.="<option value='" . $rUnit['kodeunit'] . "'>" . $rUnit['kodeunit'] . " - " . $optNmorg[$rUnit['kodeunit']] . "</option>";
        }
        echo $optUnit;
        break;
    default:
        break;
}
?>
