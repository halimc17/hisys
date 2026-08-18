<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/PHPExcel.php');

// --- HELPER FUNCTION: WRITE TABLE TO EXCEL ---
function writeExcelTable($sheet, &$row, $startCol, $title, $data, $totalCol, $isPercent=false, $decimals=0, $rowHeader="Tahun Tanam") {
    $colWidth = 12; 
    $months = array(
        $_SESSION['lang']['jan'], 
        $_SESSION['lang']['peb'], 
        $_SESSION['lang']['mar'], 
        $_SESSION['lang']['apr'], 
        $_SESSION['lang']['mei'], 
        $_SESSION['lang']['jun'], 
        $_SESSION['lang']['jul'], 
        $_SESSION['lang']['agt'], 
        $_SESSION['lang']['sep'], 
        $_SESSION['lang']['okt'], 
        $_SESSION['lang']['nov'], 
        $_SESSION['lang']['dec']
    );
    
    // TITLE
    $startColIdx = PHPExcel_Cell::columnIndexFromString($startCol) - 1;
    $endColIdx = $startColIdx + 13; 
    $colEnd = PHPExcel_Cell::stringFromColumnIndex($endColIdx);
    
    // Title Row
    $sheet->setCellValue($startCol.$row, $title);
    $sheet->mergeCells($startCol.$row.':'.$colEnd.$row);
    $sheet->getStyle($startCol.$row)->getFont()->setBold(true)->setSize(11);
    $sheet->getStyle($startCol.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
    $sheet->getStyle($startCol.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $row++;

    // Header Row
    $currCol = $startCol;
    $currColIdx = $startColIdx;
    
    // Header 1: Row Header (Tahun Tanam)
    $sheet->setCellValue($currCol.$row, $rowHeader); 
    $sheet->getColumnDimension($currCol)->setWidth($colWidth);
    $currCol++; $currColIdx++;
    
    // Header 2: Months
    foreach($months as $m) {
        $sheet->setCellValue($currCol.$row, $m); 
        $sheet->getColumnDimension($currCol)->setWidth($colWidth);
        $currCol++; $currColIdx++;
    }
    
    // Header 3: Total
    $sheet->setCellValue($currCol.$row, "TOTAL");
    $sheet->getColumnDimension($currCol)->setWidth($colWidth);
    
    // Style Header
    $sheet->getStyle($startCol.$row.':'.$colEnd.$row)->getFont()->setBold(true);
    $sheet->getStyle($startCol.$row.':'.$colEnd.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
    $sheet->getStyle($startCol.$row.':'.$colEnd.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $row++;

    // Body
    foreach($data as $key => $d) {
        $currCol = $startCol;
        $sheet->setCellValue($currCol.$row, $key); 
        $sheet->getStyle($currCol.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $currCol++;
        
        $rowSum = 0; 
        
        for($i=1; $i<=12; $i++) {
            $val = isset($d[$i]) ? $d[$i] : 0;
            $rowSum += $val;
            
            $sheet->setCellValue($currCol.$row, $val);
            if($isPercent) {
                $sheet->getStyle($currCol.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            } else {
                $sheet->getStyle($currCol.$row)->getNumberFormat()->setFormatCode('#,##0'. ($decimals>0?'.'.str_repeat('0',$decimals):'') );
            }
            $currCol++;
        }
        
        // Total Row Column
        $sheet->setCellValue($currCol.$row, $rowSum);
        if($isPercent) $sheet->getStyle($currCol.$row)->getNumberFormat()->setFormatCode('#,##0.00');
        else $sheet->getStyle($currCol.$row)->getNumberFormat()->setFormatCode('#,##0'. ($decimals>0?'.'.str_repeat('0',$decimals):'') );
        
        $row++;
    }
    
    // Footer
    if(!is_null($totalCol)) {
        $currCol = $startCol;
        $sheet->setCellValue($currCol.$row, "TOTAL"); 
        $currCol++;
        
        $ftSum = 0;
        for($i=1; $i<=12; $i++) {
            $val = isset($totalCol[$i]) ? $totalCol[$i] : 0;
            $ftSum += $val;
            $sheet->setCellValue($currCol.$row, $val);
             if($isPercent) $sheet->getStyle($currCol.$row)->getNumberFormat()->setFormatCode('#,##0.00');
            else $sheet->getStyle($currCol.$row)->getNumberFormat()->setFormatCode('#,##0'. ($decimals>0?'.'.str_repeat('0',$decimals):'') );
            $currCol++;
        }
        $sheet->setCellValue($currCol.$row, $ftSum);
         if($isPercent) $sheet->getStyle($currCol.$row)->getNumberFormat()->setFormatCode('#,##0.00');
        else $sheet->getStyle($currCol.$row)->getNumberFormat()->setFormatCode('#,##0'. ($decimals>0?'.'.str_repeat('0',$decimals):'') );
        
        $sheet->getStyle($startCol.$row.':'.$colEnd.$row)->getFont()->setBold(true);
        $sheet->getStyle($startCol.$row.':'.$colEnd.$row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEEEEE');
        $row++;
    }
    
    // Borders
    $styleArray = array(
      'borders' => array(
        'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
        )
      )
    );
}

// --- MAIN LOGIC ---

$proses          = checkPostGet('proses', '');
$prd             = checkPostGet('periode', '');
$periode         = substr($prd, 0, 4); 
$unit            = checkPostGet('unit', '');
$intiplasmatahun = checkPostGet('intiplasma', '');
$sumberNorma     = checkPostGet('sumbernorma', 'SIMALUNGUN');

if ($prd == '' || $unit == '') exit("Error: Periode dan Unit tidak boleh kosong.");

$inplas = "";
if ($intiplasmatahun != '') $inplas = " and intiplasma='" . $intiplasmatahun . "'";

// 1. Ambil Blok TM
$kodeblok = array();
$str = "SELECT kodeorg, tahuntanam, luasareaproduktif, intiplasma FROM " . $dbname . ".setup_blok WHERE kodeorg LIKE '" . $unit . "%' " . $inplas . " AND statusblok='TM' ORDER BY substr(kodeorg,1,6) ASC, tahuntanam ASC";
$res = $owlPDO->query($str);
if($res) while ($bar = $res->fetch(PDO::FETCH_OBJ)) $kodeblok[$bar->kodeorg] = $bar->kodeorg;

// Init Data
$aggData = array();
$totalTarget = array();
$totalActual = array();

if (!empty($kodeblok)) {
    // A. Query Budget
    $strTarget = "SELECT tahuntanam, SUM(kg01) AS kg01, SUM(kg02) AS kg02, SUM(kg03) AS kg03, SUM(kg04) AS kg04, SUM(kg05) AS kg05, SUM(kg06) AS kg06, SUM(kg07) AS kg07, SUM(kg08) AS kg08, SUM(kg09) AS kg09, SUM(kg10) AS kg10, SUM(kg11) AS kg11, SUM(kg12) AS kg12 FROM " . $dbname . ".bgt_produksi_kebun WHERE tahunbudget = '" . $periode . "' AND kodeblok LIKE '" . $unit . "%' GROUP BY tahuntanam ORDER BY tahuntanam";
    $resTarget = $owlPDO->query($strTarget);
    if ($resTarget) {
        while ($bar = $resTarget->fetch(PDO::FETCH_OBJ)) {
            $thn = $bar->tahuntanam;
            if (empty($thn) || $thn == '0') continue;
            for ($i = 1; $i <= 12; $i++) {
                $col = "kg" . str_pad($i, 2, "0", STR_PAD_LEFT);
                $val = $bar->$col;
                if (!isset($aggData[$thn]['budget'][$i])) $aggData[$thn]['budget'][$i] = 0;
                $aggData[$thn]['budget'][$i] = $val;
                if (!isset($totalTarget[$i])) $totalTarget[$i] = 0;
                $totalTarget[$i] += $val;
            }
        }
    }

    // B. Query Actual (Pabrik Timbangan)
    $strB = "SELECT tahuntanam, LEFT(tanggal, 7) as periode, SUM(beratbersih) as total_brutto FROM " . $dbname . ".pabrik_timbangan WHERE kodeorg LIKE '" . $unit . "%' AND tanggal LIKE '" . $periode . "%' GROUP BY tahuntanam, LEFT(tanggal, 7)";
    $resB = $owlPDO->query($strB);
    if ($resB) {
        while ($barB = $resB->fetch(PDO::FETCH_OBJ)) {
            $y = $barB->tahuntanam;
            $m = intval(substr($barB->periode, 5, 2));
            if (!empty($y) && $y != '0') {
                if (!isset($aggData[$y]['actual'][$m])) $aggData[$y]['actual'][$m] = 0;
                $aggData[$y]['actual'][$m] += $barB->total_brutto;
                if (!isset($totalActual[$m])) $totalActual[$m] = 0;
                $totalActual[$m] += $barB->total_brutto;
            }
        }
    }

    // Normalizations
    foreach ($aggData as $y => &$d) {
        for ($i = 1; $i <= 12; $i++) {
            if (!isset($d['actual'][$i])) $d['actual'][$i] = 0;
            if (!isset($d['budget'][$i])) $d['budget'][$i] = 0;
        }
    }
    ksort($aggData);
    ksort($totalActual);
    
    // C. Data Grading for Ton/Ha
    $gradingData = array(); $totalGrading = array();
    $strG = "SELECT tahuntanam, LEFT(tanggal, 7) as periode, SUM(kgpotsortasi) as total_grading FROM " . $dbname . ".pabrik_timbangan WHERE kodeorg LIKE '" . $unit . "%' AND tanggal LIKE '" . $periode . "%' GROUP BY tahuntanam, LEFT(tanggal, 7)";
    $resG = $owlPDO->query($strG);
    if ($resG) while ($bar = $resG->fetch(PDO::FETCH_OBJ)) {
        $y = $bar->tahuntanam; $m = intval(substr($bar->periode, 5, 2));
        if (!empty($y) && $y != '0') {
            $gradingData[$y][$m] = $bar->total_grading;
            if (!isset($totalGrading[$m])) $totalGrading[$m] = 0;
            $totalGrading[$m] += $bar->total_grading;
        }
    }

    // D. Data Tandan (Janjang)
    $tandanData = array(); $totalTandan = array();
    $strT = "SELECT LEFT(tanggal, 4) as tahun, LEFT(tanggal, 7) as periode, SUM(jumlahtandan1) as jjg FROM " . $dbname . ".pabrik_timbangan WHERE kodeorg LIKE '" . $unit . "%' AND LEFT(tanggal, 4) <= '" . $periode . "' GROUP BY LEFT(tanggal, 4), LEFT(tanggal, 7) ORDER BY LEFT(tanggal, 4) ASC";
    $resT = $owlPDO->query($strT);
    if ($resT) while ($bar = $resT->fetch(PDO::FETCH_OBJ)) {
        $y = $bar->tahun; $m = intval(substr($bar->periode, 5, 2));
        $tandanData[$y][$m] = $bar->jjg;
        if (!isset($totalTandan[$m])) $totalTandan[$m] = 0;
        $totalTandan[$m] += $bar->jjg;
    }
    
    // E. Data Brutto Histori (For BJR & Trend Brutto)
    $bruttoHistData = array(); $totalBruttoHist = array();
    $startYear = intval($periode) - 2;
    $strBh = "SELECT LEFT(tanggal, 4) as tahun, LEFT(tanggal, 7) as periode_bln, SUM(beratbersih) as kg FROM " . $dbname . ".pabrik_timbangan WHERE kodeorg LIKE '" . $unit . "%' AND LEFT(tanggal, 4) >= '" . $startYear . "' AND LEFT(tanggal, 4) <= '" . $periode . "' AND tahuntanam != '' AND tahuntanam != '0' GROUP BY LEFT(tanggal, 4), LEFT(tanggal, 7) ORDER BY LEFT(tanggal, 4) ASC";
    $resBh = $owlPDO->query($strBh);
    if ($resBh) while ($bar = $resBh->fetch(PDO::FETCH_OBJ)) {
        $y = $bar->tahun; $m = intval(substr($bar->periode_bln, 5, 2));
        $ton = $bar->kg / 1000;
        $bruttoHistData[$y][$m] = $ton; // In Tons
        if (!isset($totalBruttoHist[$m])) $totalBruttoHist[$m] = 0;
        $totalBruttoHist[$m] += $ton;
    }
}

// === START EXCEL CREATION ===

$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setTitle("Laporan Produksi ".$unit." ".$periode);
$objPHPExcel->setActiveSheetIndex(0);
$sheet = $objPHPExcel->getActiveSheet();
$sheet->setTitle("Laporan Produksi");

// 1. Header Report
$sheet->setCellValue('A1', "LAPORAN PRODUKSI KEBUN - " . $unit . " PERIODE " . $periode);
$sheet->mergeCells('A1:AC1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$currentRow = 4;

// DATA ADAPTERS & RENDERING

// 1. Target Produksi
$dataTarget = [];
foreach($aggData as $y => $d) $dataTarget[$y] = $d['budget'];

// Sebaran Target
$dataSebaranBgt = [];
$totalSebaranBgt = array_fill(1, 12, 0); 
$grandTotalBgt = array_sum($totalTarget);
foreach($aggData as $y => $d) {
    $rowSum = array_sum($d['budget']);
    for($i=1; $i<=12; $i++) {
        $val = isset($d['budget'][$i]) ? $d['budget'][$i] : 0;
        $perc = ($rowSum > 0) ? ($val/$rowSum)*100 : 0;
        $dataSebaranBgt[$y][$i] = $perc;
    }
}
for($i=1; $i<=12; $i++) {
    $val = $totalTarget[$i];
    $perc = ($grandTotalBgt > 0) ? ($val/$grandTotalBgt)*100 : 0;
    $totalSebaranBgt[$i] = $perc;
}

// Render Table 1 (Side-by-Side)
$startRow = $currentRow; // Capture Alignment Point

$rowLeft = $startRow;
writeExcelTable($sheet, $rowLeft, 'A', "Target Produksi Kebun (Budget - Kg)", $dataTarget, $totalTarget, false, 0);

$rowRight = $startRow; // Reset
writeExcelTable($sheet, $rowRight, 'P', "% SEBARAN TARGET", $dataSebaranBgt, null, true, 2); // Null Footer

$currentRow = max($rowLeft, $rowRight) + 2; // Sync Next Block


// 2. Actual Produksi (Brutto)
$dataActual = [];
foreach($aggData as $y => $d) $dataActual[$y] = $d['actual'];

// Sebaran Actual
$dataSebaranAct = [];
$totalSebaranAct = array_fill(1, 12, 0);
$grandTotalAct = array_sum($totalActual);
foreach($aggData as $y => $d) {
    $rowSum = array_sum($d['actual']);
    for($i=1; $i<=12; $i++) {
        $val = isset($d['actual'][$i]) ? $d['actual'][$i] : 0;
        $perc = ($rowSum > 0) ? ($val/$rowSum)*100 : 0;
        $dataSebaranAct[$y][$i] = $perc;
    }
}
for($i=1; $i<=12; $i++) {
    $val = $totalActual[$i];
    $perc = ($grandTotalAct > 0) ? ($val/$grandTotalAct)*100 : 0;
    $totalSebaranAct[$i] = $perc;
}

// Render Table 2
$startRow = $currentRow;
$rowLeft = $startRow;
writeExcelTable($sheet, $rowLeft, 'A', "Realisasi Produksi (Aktual Brutto - Kg)", $dataActual, $totalActual, false, 0);

$rowRight = $startRow;
writeExcelTable($sheet, $rowRight, 'P', "% SEBARAN ACTUAL", $dataSebaranAct, null, true, 2); // Null Footer

$currentRow = max($rowLeft, $rowRight) + 2;


// 3. Tandan (History)
// Sebaran Tandan
$dataSebaranTandan = [];
$totalSebaranTandan = array_fill(1, 12, 0);
$grandTotalTandan = array_sum($totalTandan);
foreach($tandanData as $y => $m) {
    $rowSum = array_sum($m);
    for($i=1; $i<=12; $i++) {
        $val = isset($m[$i]) ? $m[$i] : 0;
        $perc = ($rowSum > 0) ? ($val/$rowSum)*100 : 0;
        $dataSebaranTandan[$y][$i] = $perc;
    }
}
for($i=1; $i<=12; $i++) {
    $val = isset($totalTandan[$i]) ? $totalTandan[$i] : 0;
    $perc = ($grandTotalTandan > 0) ? ($val/$grandTotalTandan)*100 : 0;
    $totalSebaranTandan[$i] = $perc;
}

// Render Table 3
$startRow = $currentRow;
$rowLeft = $startRow;
writeExcelTable($sheet, $rowLeft, 'A', "Tren Produksi Kebun - Tandan (Janjang)", $tandanData, null, false, 0, "Tahun Histori");

$rowRight = $startRow;
writeExcelTable($sheet, $rowRight, 'P', "% SEBARAN TANDAN", $dataSebaranTandan, null, true, 2, "Tahun Histori"); // Null Footer

$currentRow = max($rowLeft, $rowRight) + 2;


// 4. Brutto Histori (Tonase)
// Sebaran Brutto Histori
$dataSebaranBH = [];
$totalSebaranBH = array_fill(1, 12, 0);
$grandTotalBH = array_sum($totalBruttoHist);
foreach($bruttoHistData as $y => $m) {
    $rowSum = array_sum($m);
    for($i=1; $i<=12; $i++) {
        $val = isset($m[$i]) ? $m[$i] : 0;
        $perc = ($rowSum > 0) ? ($val/$rowSum)*100 : 0;
        $dataSebaranBH[$y][$i] = $perc;
    }
}
for($i=1; $i<=12; $i++) {
    $val = isset($totalBruttoHist[$i]) ? $totalBruttoHist[$i] : 0;
    $perc = ($grandTotalBH > 0) ? ($val/$grandTotalBH)*100 : 0;
    $totalSebaranBH[$i] = $perc;
}

// Render Table 4
$startRow = $currentRow;
$rowLeft = $startRow;
writeExcelTable($sheet, $rowLeft, 'A', "Tren Produksi Kebun (Tonase Brutto)", $bruttoHistData, null, false, 2, "Tahun Histori");

$rowRight = $startRow;
writeExcelTable($sheet, $rowRight, 'P', "% SEBARAN TONASE", $dataSebaranBH, null, true, 2, "Tahun Histori"); // Null Footer

$currentRow = max($rowLeft, $rowRight) + 2;


// 5. BJR
// Prepare BJR Data
$bjrDataSimple = [];
$totalBjrSimple = [];
$allYears = array_unique(array_merge(array_keys($tandanData), array_keys($bruttoHistData)));
sort($allYears);

foreach($allYears as $y) {
    for($i=1; $i<=12; $i++) {
        $jjg = isset($tandanData[$y][$i]) ? $tandanData[$y][$i] : 0;
        $ton = isset($bruttoHistData[$y][$i]) ? $bruttoHistData[$y][$i] : 0;
        $kg = $ton * 1000;
        $val = ($jjg > 0) ? $kg/$jjg : 0;
        $bjrDataSimple[$y][$i] = $val;
    }
}
for($i=1; $i<=12; $i++) {
    $totJjg = isset($totalTandan[$i]) ? $totalTandan[$i] : 0;
    $totTon = isset($totalBruttoHist[$i]) ? $totalBruttoHist[$i] : 0;
    $totKg = $totTon * 1000;
    $val = ($totJjg > 0) ? $totKg/$totJjg : 0;
    $totalBjrSimple[$i] = $val;
}

// Sebaran BJR
$dataSebaranBJR = [];
$totalSebaranBJR = []; // Placeholder
$grandTotalBJR = array_sum($totalBjrSimple); 
foreach($bjrDataSimple as $y => $m) {
    $rowSum = array_sum($m);
    for($i=1; $i<=12; $i++) {
        $val = isset($m[$i]) ? $m[$i] : 0;
        $perc = ($rowSum > 0) ? ($val/$rowSum)*100 : 0;
        $dataSebaranBJR[$y][$i] = $perc;
    }
}
for($i=1; $i<=12; $i++) {
    $val = isset($totalBjrSimple[$i]) ? $totalBjrSimple[$i] : 0;
    $perc = ($grandTotalBJR > 0) ? ($val/$grandTotalBJR)*100 : 0;
    $totalSebaranBJR[$i] = $perc;
}

// Render Table 5
$startRow = $currentRow;
$rowLeft = $startRow;
writeExcelTable($sheet, $rowLeft, 'A', "Tren Produksi Kebun - BJR (Kg/Janjang)", $bjrDataSimple, null, false, 2, "Tahun Histori");

$rowRight = $startRow;
writeExcelTable($sheet, $rowRight, 'P', "% SEBARAN BJR", $dataSebaranBJR, null, true, 2, "Tahun Histori"); // Null Footer

$currentRow = max($rowLeft, $rowRight) + 2;


// 6. Target & Actual Ton/Ha
// Calc Ton/Ha Logic
$dataTargetHa = [];
$totalTargetHa = [];
$dataActualHa = [];
$totalActualHa = [];

foreach($aggData as $y => $d) {
    for($i=1; $i<=12; $i++) {
        $gradKg = isset($gradingData[$y][$i]) ? $gradingData[$y][$i] : 0;
        $divisor = ($gradKg > 0) ? $gradKg/1000 : 0;
        
        $bgtKg = isset($d['budget'][$i]) ? $d['budget'][$i] : 0;
        $tHa = ($divisor > 0) ? ($bgtKg/1000)/$divisor : 0;
        $dataTargetHa[$y][$i] = $tHa;
        
        $actKg = isset($d['actual'][$i]) ? $d['actual'][$i] : 0;
        $aHa = ($divisor > 0) ? ($actKg/1000)/$divisor : 0;
        $dataActualHa[$y][$i] = $aHa;
    }
}
for($i=1; $i<=12; $i++) {
    $gradKg = isset($totalGrading[$i]) ? $totalGrading[$i] : 0;
    $div = ($gradKg > 0) ? $gradKg/1000 : 0;
    
    $bgtKg = isset($totalTarget[$i]) ? $totalTarget[$i] : 0;
    $totalTargetHa[$i] = ($div > 0) ? ($bgtKg/1000)/$div : 0;
    
    $actKg = isset($totalActual[$i]) ? $totalActual[$i] : 0;
    $totalActualHa[$i] = ($div > 0) ? ($actKg/1000)/$div : 0;
}

// Sebaran Target Ha
$dataSebaranHa = [];
$totalSebaranHa = []; // Placeholder
$grandTotalHa = array_sum($totalTargetHa);
foreach($dataTargetHa as $y => $m) {
    $rowSum = array_sum($m);
    for($i=1; $i<=12; $i++) {
        $val = $m[$i];
        $perc = ($rowSum > 0) ? ($val/$rowSum)*100 : 0;
        $dataSebaranHa[$y][$i] = $perc;
    }
}
for($i=1; $i<=12; $i++) {
    $val = $totalTargetHa[$i];
    $perc = ($grandTotalHa > 0) ? ($val/$grandTotalHa)*100 : 0;
    $totalSebaranHa[$i] = $perc;
}

// Render Table 6 (Target Ton/Ha)
$startRow = $currentRow;
$rowLeft = $startRow;
writeExcelTable($sheet, $rowLeft, 'A', "Target Produksi Ton/Ha", $dataTargetHa, $totalTargetHa, false, 2);

$rowRight = $startRow;
writeExcelTable($sheet, $rowRight, 'P', "% SEBARAN TARGET HA", $dataSebaranHa, null, true, 2); // Null Footer

$currentRow = max($rowLeft, $rowRight) + 2;


// Sebaran Actual Ha
$dataSebaranActHa = [];
$totalSebaranActHa = []; // Placeholder
$grandTotalActHa = array_sum($totalActualHa);
foreach($dataActualHa as $y => $m) {
    $rowSum = array_sum($m);
    for($i=1; $i<=12; $i++) {
        $val = $m[$i];
        $perc = ($rowSum > 0) ? ($val/$rowSum)*100 : 0;
        $dataSebaranActHa[$y][$i] = $perc;
    }
}
for($i=1; $i<=12; $i++) {
    $val = $totalActualHa[$i];
    $perc = ($grandTotalActHa > 0) ? ($val/$grandTotalActHa)*100 : 0;
    $totalSebaranActHa[$i] = $perc;
}

// Render Table 7 (Actual Ton/Ha)
$startRow = $currentRow;
$rowLeft = $startRow;
writeExcelTable($sheet, $rowLeft, 'A', "Actual Produksi Ton/Ha", $dataActualHa, $totalActualHa, false, 2);

$rowRight = $startRow;
writeExcelTable($sheet, $rowRight, 'P', "% SEBARAN ACTUAL HA", $dataSebaranActHa, null, true, 2); // Null Footer

$currentRow = max($rowLeft, $rowRight) + 2;


// 7. Variance Ton/Ha (No Sebaran)
$dataVarianceHa = [];
$totalVarianceHa = [];
foreach($aggData as $y => $d) {
    for($i=1; $i<=12; $i++) {
        $act = isset($dataActualHa[$y][$i]) ? $dataActualHa[$y][$i] : 0;
        $bgt = isset($dataTargetHa[$y][$i]) ? $dataTargetHa[$y][$i] : 0;
        $dataVarianceHa[$y][$i] = $act - $bgt;
    }
}
for($i=1; $i<=12; $i++) {
    $act = isset($totalActualHa[$i]) ? $totalActualHa[$i] : 0;
    $bgt = isset($totalTargetHa[$i]) ? $totalTargetHa[$i] : 0;
    $totalVarianceHa[$i] = $act - $bgt;
}

$rowLeft = $currentRow;
writeExcelTable($sheet, $rowLeft, 'A', "Variance Ton/Ha (Actual - Target)", $dataVarianceHa, $totalVarianceHa, false, 2);
$currentRow = $rowLeft + 2;


// 8. Norma PPKS
$sebaranData = array(6.745979734, 7.173113727, 7.685969321, 8.924026753, 9.25610709, 8.827819127, 8.691440674, 8.748010427, 8.204249104, 8.064988386, 8.367831727, 9.310463929);
$refNorma = array(
    'TOPAZ' => array(
        3=>19.40, 4=>28.70, 5=>33.00, 6=>32.10, 7=>31.50, 8=>30.60, 9=>33.90, 10=>32.10,
        11=>34.70, 12=>32.20, 13=>31.60, 14=>31.80, 15=>33.30, 16=>33.60, 17=>32.20, 18=>30.80,
        19=>29.40, 20=>28.00, 21=>26.60, 22=>25.20, 23=>23.80, 24=>22.40, 25=>21.00
    ),
    'SIMALUNGUN' => array(
        3=>9.00, 4=>15.00, 5=>18.00, 6=>21.10, 7=>26.00, 8=>30.00, 9=>31.00, 10=>31.00,
        11=>31.00, 12=>31.00, 13=>31.00, 14=>30.00, 15=>27.90, 16=>27.10, 17=>26.00, 18=>24.90,
        19=>24.10, 20=>23.10, 21=>19.80, 22=>19.80, 23=>18.90, 24=>18.10, 25=>17.10
    ),
    'SRIWIJAYA' => array( 
        3=>15.10, 4=>21.80, 5=>24.20, 6=>27.90, 7=>30.90, 8=>31.50, 9=>31.60, 10=>33.40,
        11=>35.90, 12=>35.90, 13=>35.20, 14=>35.00, 15=>32.90, 16=>31.80, 17=>30.90, 18=>29.50,
        19=>28.90, 20=>28.70, 21=>28.50, 22=>28.20, 23=>27.90, 24=>27.30, 25=>27.00
    )
);
if(!isset($refNorma[$sumberNorma])) $sumberNorma = 'SIMALUNGUN';

$dataNorma = [];
$totalNorma = array_fill(1, 12, 0); 
$dataNormaCompare = [];
$totalNormaCompare = [];
$currentYear = intval($periode);

foreach($aggData as $y => $d) {
    $plantingYear = intval($y);
    $umur = $currentYear - $plantingYear;
    $normaYearVal = isset($refNorma[$sumberNorma][$umur]) ? $refNorma[$sumberNorma][$umur] : 0;
    
    for($i=1; $i<=12; $i++) {
        $perc = isset($sebaranData[$i-1]) ? $sebaranData[$i-1] : 0;
        $valMonth = ($normaYearVal * $perc) / 100;
        $dataNorma[$y][$i] = $valMonth;
        
        if(!isset($totalNorma[$i])) $totalNorma[$i]=0; $totalNorma[$i] += $valMonth; 
        
        $actHa = isset($dataActualHa[$y][$i]) ? $dataActualHa[$y][$i] : 0;
        $dataNormaCompare[$y][$i] = ($valMonth > 0) ? ($actHa/$valMonth)*100 : 0;
    }
}
for($i=1; $i<=12; $i++) {
    $act = isset($totalActualHa[$i]) ? $totalActualHa[$i] : 0;
    $nrm = isset($totalNorma[$i]) ? $totalNorma[$i] : 0;
    $totalNormaCompare[$i] = ($nrm > 0) ? ($act/$nrm)*100 : 0;
}

// Side-by-Side Norma
$startRow = $currentRow;

$rowLeft = $startRow;
writeExcelTable($sheet, $rowLeft, 'A', "Norma PPKS / " . $sumberNorma . " (Ton/Ha)", $dataNorma, null, false, 2);

$rowRight = $startRow;
writeExcelTable($sheet, $rowRight, 'P', "Perbandingan Aktual dengan Norma PPKS (%)", $dataNormaCompare, null, false, 2); // Display as comparison? Assuming %.

$currentRow = max($rowLeft, $rowRight) + 2;


// OUTPUT
$filename = "Laporan_Produksi_" . $unit . "_" . $periode . ".xlsx";
ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$filename.'"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;
?>
