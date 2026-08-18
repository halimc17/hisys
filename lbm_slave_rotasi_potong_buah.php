<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$optNmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$proses = checkPostGet('proses', '');
$kdUnit = checkPostGet('kdUnit', '');
$periode = checkPostGet('periode', '');
$judul = checkPostGet('judul', '');
$afdId = checkPostGet('afdId', '');

$qwe = explode('-', $periode);
$tahun = $qwe[0];
$bulan = $qwe[1];

if ($bulan == '01') {
    $tahunlalu = $tahun - 1;
    $bulanlalu = '12';
} else {
    $tahunlalu = $tahun;
    $bulanlalu = $bulan - 1;
    if (strlen($bulanlalu) == 1)
        $bulanlalu = '0' . $bulanlalu;
}

$periodelalu = $tahunlalu . '-' . $bulanlalu;

if ($periode == '') {
    exit("Error: " . $_SESSION['lang']['periode'] . " required");
}
if ($kdUnit != '') {
    $unitId = $optNmOrg[$kdUnit];
} else {
    exit("Error:" . $_SESSION['lang']['unit'] . " required");
}

// cari tanggal buat mencari apakah tanggal terakhir sebelum bulan lalu panen?
$tanggalakhir = strtotime('-2 month', strtotime($periode . '-01'));
$tanggalakhir = date('Y-m-t', $tanggalakhir);

// data panen hari terakhir 2 bulan lalu
$panen = "select kodeorg,tanggal from " . $dbname . ".kebun_interval_panen_vw
    where  tanggal = '" . $tanggalakhir . "' and kodeorg like '" . $kdUnit . "%'";
if ($afdId != '') {
    $panen = "select kodeorg,tanggal from " . $dbname . ".kebun_interval_panen_vw
            where  tanggal = '" . $tanggalakhir . "' and kodeorg like '" . $afdId . "%'";
}

$query = $owlPDO->query($panen) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $dzArr[$res['kodeorg']][$res['tanggal']] = 'P';
}

// data panen bulan lalu
$panen = "select kodeorg,tanggal from " . $dbname . ".kebun_interval_panen_vw
    where  tanggal like '" . $periodelalu . "%' and kodeorg like '" . $kdUnit . "%'";
if ($afdId != '') {
    $panen = "select kodeorg,tanggal from " . $dbname . ".kebun_interval_panen_vw
            where  tanggal like '" . $periodelalu . "%' and kodeorg like '" . $afdId . "%'";
}

$query = $owlPDO->query($panen) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $kodeorgArr[$res['kodeorg']] = $res['kodeorg'];
    $tanggallaluArr[$res['tanggal']] = $res['tanggal'];
    $dzArr[$res['kodeorg']][$res['tanggal']] = 'P';
}

// data panen bulan ini
$panen = "select kodeorg,tanggal from " . $dbname . ".kebun_interval_panen_vw
    where  tanggal like '" . $periode . "%' and kodeorg like '" . $kdUnit . "%'";
if ($afdId != '') {
    $panen = "select kodeorg,tanggal from " . $dbname . ".kebun_interval_panen_vw
    where  tanggal like '" . $periode . "%' and kodeorg like '" . $afdId . "%'";
}

$query = $owlPDO->query($panen) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
//    $kodeorgArr[$res['kodeorg']]=$res['kodeorg'];
    $tanggalArr[$res['tanggal']] = $res['tanggal'];
//    $dzArr[$res['kodeorg']][$res['tanggal']]='P';
}

// data panen sd bulan ini
$panen = "select kodeorg,tanggal,tahuntanam from " . $dbname . ".kebun_interval_panen_vw
    where (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $kdUnit . "%'";
if ($afdId != '') {
    $panen = "select kodeorg,tanggal,tahuntanam from " . $dbname . ".kebun_interval_panen_vw
    where (tanggal between '" . $tahun . "-01-01' and LAST_DAY('" . $periode . "-15')) and kodeorg like '" . $afdId . "%'";
}

$query = $owlPDO->query($panen) or die(print " Gagal: " . PDOException::getMessage());
$query->setFetchMode(PDO::FETCH_ASSOC);
while ($res = $query->fetch()) {
    $kodeorgArr[$res['kodeorg']] = $res['kodeorg'];
    $tanggalsdArr[$res['tanggal']] = $res['tanggal'];
    $dzArr[$res['kodeorg']][$res['tanggal']] = 'P';
    $ttArr[$res['kodeorg']] = $res['tahuntanam'];
}

$dzRot = array();
// susun rotasi
if (!empty($kodeorgArr))
    foreach ($kodeorgArr as $koko) {
        // bulan lalu
        if (!empty($tanggallaluArr))
            foreach ($tanggallaluArr as $tata) {
                $kemarin = strtotime('-1 day', strtotime($tata));
                $kemarin = date('Y-m-d', $kemarin);
                $dzArr[$koko][$tata] = isset($dzArr[$koko][$tata]) ? $dzArr[$koko][$tata] : '';
                $dzArr[$koko][$kemarin] = isset($dzArr[$koko][$kemarin]) ? $dzArr[$koko][$kemarin] : '';
                if (($dzArr[$koko][$tata] == 'P')and ( $dzArr[$koko][$kemarin] != 'P'))
                    @$dzRot[$koko]['bl']+=1;
            }
        // bulan ini
        if (!empty($tanggalArr))
            foreach ($tanggalArr as $tata) {
                $kemarin = strtotime('-1 day', strtotime($tata));
                $kemarin = date('Y-m-d', $kemarin);
                $dzArr[$koko][$tata] = isset($dzArr[$koko][$tata]) ? $dzArr[$koko][$tata] : '';
                $dzArr[$koko][$kemarin] = isset($dzArr[$koko][$kemarin]) ? $dzArr[$koko][$kemarin] : '';
                if (($dzArr[$koko][$tata] == 'P')and ( $dzArr[$koko][$kemarin] != 'P'))
//            $dzRot[$koko].=$tata."_".$kemarin.' .';
                    @$dzRot[$koko]['bi']+=1;
            }
        // sd bulan ini
        if (!empty($tanggalsdArr))
            foreach ($tanggalsdArr as $tata) {
                $kemarin = strtotime('-1 day', strtotime($tata));
                $kemarin = date('Y-m-d', $kemarin);
                $dzArr[$koko][$tata] = isset($dzArr[$koko][$tata]) ? $dzArr[$koko][$tata] : '';
                $dzArr[$koko][$kemarin] = isset($dzArr[$koko][$kemarin]) ? $dzArr[$koko][$kemarin] : '';
                if (($dzArr[$koko][$tata] == 'P')and ( $dzArr[$koko][$kemarin] != 'P'))
                    @$dzRot[$koko]['sd']+=1;
            }
    }


$sRotasiBudget = "select distinct rotasi,kodeorg from " . $dbname . ".bgt_budget 
                where tahunbudget='" . $tahun . "' and kodeorg like '" . $kdUnit . "%' and kegiatan=611010101";
if ($afdId != '') {
    $sRotasiBudget = "select distinct rotasi,kodeorg from " . $dbname . ".bgt_budget 
                where tahunbudget='" . $tahun . "' and kodeorg like '" . $afdId . "%' and kegiatan=611010101";
}

$qRotasiBudget = $owlPDO->query($sRotasiBudget) or die(print " Gagal: " . PDOException::getMessage());
$qRotasiBudget->setFetchMode(PDO::FETCH_ASSOC);
while ($rRotasiBudget = $qRotasiBudget->fetch()) {
    @$dzRot[$rRotasiBudget['kodeorg']]['bib']+=$rRotasiBudget['rotasi'] / 12;
    $dzRot[$rRotasiBudget['kodeorg']]['sdb']+=$dzRot[$rRotasiBudget['kodeorg']]['bib'] * (intval($bulan));
}


// urut blok
if (!empty($kodeorgArr))
    asort($kodeorgArr);

$varCek = count($kodeorgArr);
if ($varCek < 1) {
    exit("Error: No data found");
}
$brdr = 0;
$bgcoloraja = '';
@$cols = count($dataAfd) * 3;
if ($proses == 'excel') {
    $bgcoloraja = "bgcolor=#DEDEDE align=center";
    $brdr = 1;
    $tab.="
    <table>
    <tr><td colspan=5 align=left><b>07. " . $_SESSION['lang']['rotasi'] . " " . $_SESSION['lang']['panen'] . "</b></td><td colspan=7 align=right><b>" . $_SESSION['lang']['bulan'] . " : " . substr(tanggalnormal($periode), 1, 7) . "</b></td></tr>
    <tr><td colspan=5 align=left>" . $_SESSION['lang']['unit'] . " : " . $optNmOrg[$kdUnit] . " </td></tr>";
    if ($afdId != '') {
        $tab.="<tr><td colspan=5 align=left>" . $_SESSION['lang']['afdeling'] . " : " . $optNmOrg[$afdId] . " </td></tr>";
    }
    $tab.="<tr><td colspan=5 align=left>&nbsp;</td></tr>
    </table>";
}

$tab.="<table cellspacing=1 border=" . $brdr . " class=sortable>
	<thead class=rowheader>
	<tr>
        <td " . $bgcoloraja . " colspan=8>" . $_SESSION['lang']['rotasi'] . " " . $_SESSION['lang']['panen'] . " (KALI(Times))</td></tr>";
$tab.="<tr>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['blok'] . "</td>
        <td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['thntanam'] . "</td><td " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['bulanlalu'] . "</td>";
$tab.="<td " . $bgcoloraja . " colspan=2>" . $_SESSION['lang']['budget'] . "</td><td " . $bgcoloraja . " colspan=2>" . $_SESSION['lang']['realisasi'] . "</td>";
$tab.="<td  " . $bgcoloraja . " rowspan=2>" . $_SESSION['lang']['ratarata'] . " " . $_SESSION['lang']['rotasi'] . "/" . $_SESSION['lang']['bulan'] . "</td></tr>";

$tab.="<tr><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . " </td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . " </td><td " . $bgcoloraja . ">" . $_SESSION['lang']['bi'] . " </td><td " . $bgcoloraja . ">" . $_SESSION['lang']['sbi'] . " </td></tr>";
$tab.="</thead>
	<tbody>";
foreach ($kodeorgArr as $lsBlok) {
    $tab.="<tr class=rowcontent>";
    $tab.="<td>" . $lsBlok . "</td>";
    $tab.="<td align=right>" . $ttArr[$lsBlok] . "</td>";
    $tab.="<td align=right>" . @number_format($dzRot[$lsBlok]['bl'], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzRot[$lsBlok]['bib'], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzRot[$lsBlok]['sdb'], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzRot[$lsBlok]['bi'], 2) . "</td>";
    $tab.="<td align=right>" . @number_format($dzRot[$lsBlok]['sd'], 2) . "</td>";
    @$rtBln[$lsBlok] = $dzRot[$lsBlok]['sd'] / (intval($bulan));
    $tab.="<td align=right>" . @number_format($rtBln[$lsBlok], 2) . "</td>";
    @$totBlnLalu+=$dzRot[$lsBlok]['bl'];
    @$totBudgetBi+=$dzRot[$lsBlok]['bib'];
    @$totBudgetSbi+=$dzRot[$lsBlok]['sdb'];
    @$totRealBi+=$dzRot[$lsBlok]['bi'];
    @$totRealSbi+=$dzRot[$lsBlok]['sd'];
    @$totRata+=$rtBln[$lsBlok];
}
$tab.="<tr class=rowcontent>";
$tab.="<td colspan=2>" . $_SESSION['lang']['total'] . "</td>";
$tab.="<td  align=right>" . @number_format($totBlnLalu, 2) . "</td>";
$tab.="<td  align=right>" . @number_format($totBudgetBi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totBudgetSbi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totRealBi, 2) . "</td>";
$tab.="<td align=right>" . @number_format($totRealSbi, 2) . "</td>";
$tab.="<td align=right></td>";
//        $tab.="<td align=right>".@number_format($totRata,2)."</td>";
$tab.="</tbody></table>";

switch ($proses) {
    case'preview':
        echo $tab;
        break;
    case'excel':
        $tab.="Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $dte = date("Hms");
        $nop_ = "rotasipotongbuah_" . $dte;
        $gztralala = gzopen("tempExcel/" . $nop_ . ".xls.gz", "w9");
        gzwrite($gztralala, $tab);
        gzclose($gztralala);
        echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls.gz';
            </script>";
        break;
    case'pdf':

        class PDF extends FPDF {

            function Header() {
                global $periode;
                global $dataAfd;
                global $kdUnit;
                global $optNmOrg;
                global $dbname;
                global $thn;
                global $afdId;
                global $pdf;

                $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
                $height = 5;

                $this->SetFont('Arial', 'B', 8);
                $this->Cell($width, $height, strtoupper("07. " . $_SESSION['lang']['rotasi'] . " " . $_SESSION['lang']['panen']), 0, 1, 'L');
                $this->Cell(790, $height, $_SESSION['lang']['bulan'] . ' : ' . substr(tanggalnormal($periode), 1, 7), 0, 1, 'R');
                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);
                $this->Cell($width, $height, $_SESSION['lang']['unit'] . ' : ' . $optNmOrg[$kdUnit], 0, 1, 'L');
                if ($afdId != '') {
                    $tinggiAkr = $this->GetY();
                    $ksamping = $this->GetX();
                    $this->SetY($tinggiAkr + 20);
                    $this->SetX($ksamping);
                    $this->Cell($width, $height, $_SESSION['lang']['afdeling'] . ' : ' . $optNmOrg[$afdId], 0, 1, 'L');
                }
                $this->Cell(790, $height, ' ', 0, 1, 'R');

                $height = 15;
                $this->SetFillColor(220, 220, 220);
                $this->SetFont('Arial', 'B', 7);

                $tinggiAkr = $this->GetY();
                $ksamping = $this->GetX();
                $this->SetY($tinggiAkr + 20);
                $this->SetX($ksamping);
                $this->Cell(360, $height, $_SESSION['lang']['rotasi'] . " " . $_SESSION['lang']['panen'] . "(kali(Times))", 'TBLR', 1, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['blok'], 'TLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['tahun'], 'TLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['bulanlalu'], 'TLR', 0, 'C', 1);
                $this->Cell(70, $height, $_SESSION['lang']['anggaran'], 'TBLR', 0, 'C', 1);
                $this->Cell(70, $height, $_SESSION['lang']['realisasi'], 'TBLR', 0, 'C', 1);
                $this->Cell(70, $height, $_SESSION['lang']['ratarata'], 'TLR', 1, 'C', 1);

                $this->Cell(50, $height, " ", 'BLR', 0, 'C', 1);
                $this->Cell(50, $height, $_SESSION['lang']['tanam'], 'BLR', 0, 'C', 1);
                $this->Cell(50, $height, "", 'BLR', 0, 'C', 1);
                $this->Cell(35, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(35, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(35, $height, $_SESSION['lang']['bi'], 'TBLR', 0, 'C', 1);
                $this->Cell(35, $height, $_SESSION['lang']['sbi'], 'TBLR', 0, 'C', 1);
                $this->Cell(70, $height, $_SESSION['lang']['ratarata'] . "/" . $_SESSION['lang']['bulan'], 'BLR', 1, 'C', 1);
            }

            function Footer() {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(10, 10, 'Page ' . $this->PageNo() . " / {totalPages}", 0, 0, 'L');
            }

        }

        //================================

        $pdf = new PDF('L', 'pt', 'A4');
        $pdf->AliasNbPages('{totalPages}');
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $height = 10;
        @$tnggi = $jmlHari * $height;
        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('Arial', 'B', 6);

        foreach ($kodeorgArr as $lsBlok) {
            $pdf->Cell(50, $height, $lsBlok, 'TBLR', 0, 'L', 1);
            $pdf->Cell(50, $height, $ttArr[$lsBlok], 'TBLR', 0, 'C', 1);
            $pdf->Cell(50, $height, @number_format($dzRot[$lsBlok]['bl'], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(35, $height, @number_format($dzRot[$lsBlok]['bib'], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(35, $height, @number_format($dzRot[$lsBlok]['sdb'], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(35, $height, @number_format($dzRot[$lsBlok]['bi'], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(35, $height, @number_format($dzRot[$lsBlok]['sd'], 2), 'TBLR', 0, 'R', 1);
            $pdf->Cell(70, $height, @number_format($rtBln[$lsBlok], 2), 'TBLR', 1, 'R', 1);
        }
        $pdf->Cell(100, $height, $_SESSION['lang']['total'], 'TBLR', 0, 'L', 1);
        $pdf->Cell(50, $height, @number_format($totBlnLalu, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(35, $height, @number_format($totBudgetBi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(35, $height, @number_format($totBudgetSbi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(35, $height, @number_format($totRealBi, 2), 'TBLR', 0, 'R', 1);
        $pdf->Cell(35, $height, @number_format($totRealSbi, 2), 'TBLR', 0, 'R', 1);
//            $pdf->Cell(70,$height,@number_format($totRata,2),'TBLR',1,'R',1);
        $pdf->Cell(70, $height, '', 'TBLR', 1, 'R', 1);
        $pdf->Output();
        break;



    default:
        break;
}
?>