<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

ini_set('display_errors', 0);
error_reporting(0);

$proses          = checkPostGet('proses', '');
$prd             = checkPostGet('periode', '');
$periode         = substr($prd, 0, 4); // Tahun YYYY
$unit            = checkPostGet('unit', '');

$unitIds = explode(',', $unit);

$intiplasmatahun = checkPostGet('intiplasma', '');
$sumberNorma     = checkPostGet('sumbernorma', 'SIMALUNGUN'); // Hanya untuk tabel Norma PPKS

if ($proses == 'grafik') {
    $typ = checkPostGet('typ', 'bar');
    
    echo "<!DOCTYPE html>
    <html>
    <head>
        <script src='js/Chart.js'></script>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; margin: 0; padding: 10px; }
            .chart-container { position: relative; height: 300px; width: 95%; margin: auto; }
            .combo-container { position: relative; height: 350px; width: 95%; margin: auto; }
            table { width: 90%; margin: 10px auto; border-collapse: collapse; font-size: 11px; }
            th, td { border: 1px solid #ddd; padding: 5px; text-align: center; }
            th { background-color: #f2f2f2; }
        </style>
    </head>
    <body>";

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

    if ($typ == 'bar') {
        // --- FETCH REAL DATA DARI SESSION (DARI MAIN TABLE) ---

        $aggData = isset($_SESSION['mr_trenProduksiTbs_aggData'][$unit]) ? $_SESSION['mr_trenProduksiTbs_aggData'][$unit] : array();
        
        $datasets = [];
        // Palette dari Chart.js utils atau standar
        $colors = array("#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF", "#FF9F40", "#C9CBCF", "#000000");
        $colorIdx = 0;

        if (!empty($aggData)) {
            foreach($aggData as $thn => $data) {
                $dataPerc = [];
                for($i=1; $i<=12; $i++) {
                    $bgt = isset($data['budget'][$i]) ? $data['budget'][$i] : 0;
                    $act = isset($data['actual'][$i]) ? $data['actual'][$i] : 0;
                    
                    // Calc Percentage
                    $val = ($bgt > 0) ? ($act / $bgt) * 100 : 0;
                    $dataPerc[] = round($val, 2);
                }
                
                $datasets[] = array(
                    'label' => (string)$thn,
                    'backgroundColor' => $colors[$colorIdx % count($colors)],
                    'data' => $dataPerc
                );
                $colorIdx++;
            }
        } else {
            // Fallback or Empty
             echo "<h3>Data tidak ditemukan. Silakan klik Preview lagi.</h3>";
        }
        
        echo "<h3>Tren % Produksi Kebun Actual vs Target " . checkPostGet('periode','') . "</h3>";
        echo "<div class='chart-container'><canvas id='barChart'></canvas></div>";
        
        $jsConfig = json_encode(array(
            'type' => 'bar',
            'data' => array(
                'labels' => $months,
                'datasets' => $datasets
            ),
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => array(
                    'yAxes' => array(array('ticks' => array('beginAtZero' => true)))
                )
            ),
            'legend' => array('position' => 'bottom')
        ));
        
        echo "<script>
            var ctx = document.getElementById('barChart').getContext('2d');
            new Chart(ctx, $jsConfig);
        </script>";


    } elseif ($typ == 'line') {
        // --- AMBIL DATA HISTORY ASLI DARI SESSION (DATA TABEL 8) ---
        $histData = isset($_SESSION['mr_trenProduksiTbs_bruttoHistData'][$unit]) ? $_SESSION['mr_trenProduksiTbs_bruttoHistData'][$unit] : array();
        // echo "DEBUG CHART 2 (JSON): " . json_encode($histData);
        
        $datasets = [];
        $colors = array("#3366cc", "#dc3912", "#ff9900", "#109618", "#990099", "#0099c6", "#dd4477", "#66aa00");
        $colorIdx = 0;
        
        if (!empty($histData)) {
            foreach($histData as $thn => $data) {
                // Pastikan tahun terurut di query utama
                $dataTon = [];
                for($i=1; $i<=12; $i++) {
                    
                    $val = isset($data['actual'][$i]) ? $data['actual'][$i] : 0;
                    $dataTon[] = round($val, 2);
                }
                
                $datasets[] = array(
                    'label' => (string)$thn,
                    'borderColor' => $colors[$colorIdx % count($colors)],
                    'backgroundColor' => 'transparent',
                    'data' => $dataTon,
                    'borderWidth' => 2,
                    'lineTension' => 0.1 
                );
                $colorIdx++;
            }
        } else {
             echo "<h3>Data sesi history tidak ditemukan. Silakan klik Preview.</h3>";
        }
        
        echo "<h3>Tren Produksi Kebun per Tahun (Tonase)</h3>";
        echo "<div class='chart-container'><canvas id='lineChart'></canvas></div>";
        
        $jsConfig = json_encode(array(
            'type' => 'line',
            'data' => array(
                'labels' => $months,
                'datasets' => $datasets
            ),
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false,
                'elements' => array('line' => array('tension' => 0)), 
                'legend' => array('position' => 'bottom'),
                'tooltips' => array('mode' => 'index', 'intersect' => false),
                'hover' => array('mode' => 'nearest', 'intersect' => true)
            )
        ));
        
        echo "<script>
            var ctx = document.getElementById('lineChart').getContext('2d');
            new Chart(ctx, $jsConfig);
        </script>";

    } elseif ($typ == 'combo') {
        // --- AMBIL DATA ASLI DARI SESSION (Total Tahunan untuk Combo Chart) ---
        $aggData = isset($_SESSION['mr_trenProduksiTbs_aggData'][$unit]) ? $_SESSION['mr_trenProduksiTbs_aggData'][$unit] : array();
        // echo "DEBUG CHART 3 (JSON): " . json_encode($aggData) . "<br>";
        
        $thnTanam   = [];
        $dataTarget = [];
        $dataReal   = [];
        
        if (!empty($aggData)) {
            foreach($aggData as $thn => $data) {
                // Label
                $thnTanam[] = (string)$thn;
                
                // Sum Target
                $sumBgt = 0;
                for($i=1; $i<=12; $i++) $sumBgt += isset($data['budget'][$i]) ? $data['budget'][$i] : 0;
                $dataTarget[] = $sumBgt;
                
                // Sum Actual
                $sumAct = 0;
                for($i=1; $i<=12; $i++) $sumAct += isset($data['actual'][$i]) ? $data['actual'][$i] : 0;
                $dataReal[] = $sumAct;
            }
        } else {
             $thnTanam[] = "No Data";
             $dataTarget[] = 0;
             $dataReal[] = 0;
        }

        $datasets = array(
            array(
                'type' => 'line',
                'label' => 'Realisasi (Actual)', // Ungu/Purple
                'borderColor' => 'purple',
                'backgroundColor' => 'transparent',
                'borderWidth' => 2,
                'pointBackgroundColor' => 'black',
                'pointRadius' => 4, 
                'data' => $dataReal
            ),
            array(
                'type' => 'bar',
                'label' => 'Target (Budget)', // Orange
                'backgroundColor' => 'orange',
                'borderColor' => 'red',
                'borderWidth' => 1,
                'data' => $dataTarget
            )
        );

        echo "<h3>Budget Produksi vs Realisasi (Total per Tahun Tanam)</h3>";
        echo "<div class='combo-container'><canvas id='comboChart'></canvas></div>";
        
        // HTML Data Table
        echo "<table style='margin-bottom: 15px;'>"; 
        echo "<tr><th>Keterangan</th>";
        foreach($thnTanam as $th) echo "<th>$th</th>";
        echo "</tr>";
        
        echo "<tr><td style='text-align:left; font-weight:bold;'>Target Produksi</td>";
        foreach($dataTarget as $v) echo "<td>".number_format($v, 0)."</td>";
        echo "</tr>";

        echo "<tr><td style='text-align:left; font-weight:bold;'>Realisasi</td>";
        foreach($dataReal as $v) echo "<td>".number_format($v, 0)."</td>";
        echo "</tr>";
        echo "</table>";    
        
        $jsConfig = json_encode(array(
            'type' => 'bar',
            'data' => array(
                'labels' => $thnTanam,
                'datasets' => $datasets
            ),
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false,
                'legend' => array('position' => 'top'),
                'scales' => array(
                    'yAxes' => array(array('ticks' => array('beginAtZero' => true)))
                )
            )
        ));
        
        echo "<script>
            var ctx = document.getElementById('comboChart').getContext('2d');
            new Chart(ctx, $jsConfig);
        </script>";
        
    } elseif ($typ == 'monthly_ton') {
        // --- AMBIL DATA ASLI DARI SESSION (Total Bulanan Budget vs Aktual) ---
        $aggData = isset($_SESSION['mr_trenProduksiTbs_aggData'][$unit]) ? $_SESSION['mr_trenProduksiTbs_aggData'][$unit] : array();
        
        $sumBudget = array_fill(1, 12, 0);
        $sumActual = array_fill(1, 12, 0);

        if (!empty($aggData)) {
            foreach($aggData as $thn => $data) {
                for($i=1; $i<=12; $i++) {
                    $bgt = isset($data['budget'][$i]) ? $data['budget'][$i] : 0;
                    $act = isset($data['actual'][$i]) ? $data['actual'][$i] : 0;
                    
                    $sumBudget[$i] += $bgt;
                    $sumActual[$i] += $act;
                }
            }
        }
        
        $dataBudgetFinal = [];
        $dataActualFinal = [];
        for($i=1; $i<=12; $i++) {
            $dataBudgetFinal[] = round($sumBudget[$i] / 1000, 0);
            $dataActualFinal[] = round($sumActual[$i] / 1000, 0);
        }

        $datasets = array(
            array(
                'label' => 'Budget',
                'backgroundColor' => '#A9A9A9', // Grey
                'data' => $dataBudgetFinal
            ),
            array(
                'label' => 'Aktual',
                'backgroundColor' => '#5B9BD5', // Blue
                'data' => $dataActualFinal
            )
        );

        echo "<h3>Budget vs Aktual<br>Produksi TBS - Ton</h3>";
        echo "<div class='chart-container'><canvas id='monthlyTonChart'></canvas></div>";
        
        $jsConfig = json_encode(array(
            'type' => 'bar',
            'data' => array(
                'labels' => $months,
                'datasets' => $datasets
            ),
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => array(
                    'yAxes' => array(array('ticks' => array('beginAtZero' => true)))
                ),
                'legend' => array('position' => 'bottom')
            )
        ));
        
        echo "<script>
            var ctx = document.getElementById('monthlyTonChart').getContext('2d');
            new Chart(ctx, $jsConfig);
        </script>";

    } elseif ($typ == 'monthly_tanam') {
        // --- AMBIL DATA ASLI DARI SESSION (Bar Group: Aktual Bulanan per Tahun Tanam) ---

        $aggData = isset($_SESSION['mr_trenProduksiTbs_aggData'][$unit]) ? $_SESSION['mr_trenProduksiTbs_aggData'][$unit] : array();
        $periode = checkPostGet('periode', '');
        
        $monthsShort = array($_SESSION['lang']['jan'], $_SESSION['lang']['peb'], $_SESSION['lang']['mar'], $_SESSION['lang']['apr'], $_SESSION['lang']['mei'], $_SESSION['lang']['jun']);
        
        $datasets = [];
        // Cycle colors
        $colors = array("#ED7D31", "#A5A5A5", "#FFC000", "#4472C4", "#70AD47", "#255E91", "#9E480E", "#636363");
        $colorIdx = 0;

        if (!empty($aggData)) {
            foreach($aggData as $thn => $data) {
                $dataTon = [];
                // BATASI loop sampai 6 bulan (Jan-Jun)
                for($i=1; $i<=6; $i++) {
                    $actKg = isset($data['actual'][$i]) ? $data['actual'][$i] : 0;
                    $actTon = ($actKg > 0) ? $actKg / 1000 : 0; // Convert to Ton
                    $dataTon[] = round($actTon, 0);
                }
                
                $datasets[] = array(
                    'label' => (string)$thn,
                    'backgroundColor' => $colors[$colorIdx % count($colors)],
                    'data' => $dataTon
                );
                $colorIdx++;
            }
        } else {
             echo "<h3>Data tidak ditemukan. Silakan klik Preview lagi.</h3>";
        }

        echo "<h3>Tren Produksi Kebun Aktual Per Tahun Tanam<br>Periode: Jan - Jun " . $periode . " (Tonase)</h3>";
        echo "<div class='chart-container'><canvas id='monthlyTanamChart'></canvas></div>";
        
        $jsConfig = json_encode(array(
            'type' => 'bar',
            'data' => array(
                'labels' => $monthsShort,
                'datasets' => $datasets
            ),
            'options' => array(
                'responsive' => true,
                'maintainAspectRatio' => false,
                'scales' => array(
                    'yAxes' => array(array('ticks' => array('beginAtZero' => true)))
                ),
                'legend' => array('position' => 'bottom')
            )
        ));
        
        echo "<script>
            var ctx = document.getElementById('monthlyTanamChart').getContext('2d');
            new Chart(ctx, $jsConfig);
        </script>";
    }

    echo "</body>
    </html>";
    exit();
}

if ($proses == 'excel' || $proses == 'preview') {
    if ($prd == '' || $unit == '') {
        exit("Error: Periode dan Unit tidak boleh kosong.");
    }
}

// Ambil Referensi Tambahan
$jenisbibit = makeOption($dbname, 'setup_blok', 'kodeorg,jenisbibit');
$namaOrg    = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$inplas = "";
if ($intiplasmatahun != '') {
    $inplas = " AND intiplasma='" . $intiplasmatahun . "'";
    // echo "INPAS: ".$inplas;
}

$months = array($_SESSION['lang']['jan'], $_SESSION['lang']['peb'], $_SESSION['lang']['mar'], $_SESSION['lang']['apr'], $_SESSION['lang']['mei'], $_SESSION['lang']['jun'], $_SESSION['lang']['jul'], $_SESSION['lang']['agt'], $_SESSION['lang']['sep'], $_SESSION['lang']['okt'], $_SESSION['lang']['nov'], $_SESSION['lang']['dec']);

// Tabel Helper
function generateTable($title, $dataType, $aggData, $totalCol, $months, $isPercent = false, $compareData = null, $decimals = 0, $rowHeader = "Tahun Tanam") {
    global $proses, $unit, $periode;
    $html = "";
    if ($proses == 'excel') {
        $html .= "<table border=1 cellpadding=5>";
        $html .= "<tr><td colspan=14 style='border:none; height:10px;'></td></tr>"; // Spacing antar tabel utama
        $html .= "<tr><td colspan=14 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff;'>" . $title . "</td></tr>";
        $html .= "<thead>";
    } else {
        $html .= "<br><table class=sortable cellspacing=1 border=0 cellpadding=5>";
        $html .= "<thead>";
        $html .= "<tr><td colspan=14 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff; padding:5px;'>" . $title . "</td></tr>";
    }
    
    // Header Tabel
    $html .= "<tr class=rowheader style='background-color:#444; color:#fff;'>
              <td align=center style='width:15%'>" . $rowHeader . "</td>";
    foreach ($months as $mon) $html .= "<td align=center style='width:6%'>" . $mon . "</td>";
    $html .= "<td align=center style='width:10%'>TOTAL</td></tr></thead><tbody>";

    // Isi Tabel (Baris per Tahun)
    if(empty($aggData)){
       $html .= "<tr class=rowcontent><td colspan=14 align=center>Data Empty for ".$unit." ".$periode."</td></tr>";
    }
    foreach ($aggData as $thn => $d) {
        $html .= "<tr class=rowcontent><td align=center>" . $thn . "</td>";
        $rowTotal = 0;

        for ($i = 1; $i <= 12; $i++) {
            $val = 0;
            if ($isPercent && $compareData) {
                // Mode Persentase (Actual / Target)
                $num = isset($d['actual'][$i]) ? $d['actual'][$i] : 0;
                $den = isset($d['budget'][$i]) ? $d['budget'][$i] : 0;
                $val = ($den > 0) ? ($num / $den) * 100 : 0;
                
                $display = number_format($val, 2, ',', '.') . "%";

                // Warna Cell
                $bg = "";
                if ($val < 80) $bg = "background-color:pink";
                else if ($val >= 100) $bg = "background-color:lightblue";

                $html .= "<td align=right style='" . $bg . "'>" . $display . "</td>";
            } else {
                // Mode Angka Biasa
                $val = isset($d[$dataType][$i]) ? $d[$dataType][$i] : 0;
                $rowTotal += $val;
                $html .= "<td align=right>" . number_format($val, $decimals, ',', '.') . "</td>";
            }
        }

        // Kolom Total di Kanan
        if ($isPercent && $compareData) {
            $totAct = 0; $totBgt = 0;
            for ($k = 1; $k <= 12; $k++) {
                $totAct += isset($d['actual'][$k]) ? $d['actual'][$k] : 0;
                $totBgt += isset($d['budget'][$k]) ? $d['budget'][$k] : 0;
            }
            $finalPerc = ($totBgt > 0) ? ($totAct / $totBgt) * 100 : 0;

            $bg = "";
            if ($finalPerc < 80) $bg = "background-color:pink";
            else if ($finalPerc >= 100) $bg = "background-color:lightblue";

            $html .= "<td align=right style='" . $bg . "'>" . number_format($finalPerc, 2, ',', '.') . "%</td>";
        } else {
            $html .= "<td align=right>" . number_format($rowTotal, $decimals, ',', '.') . "</td>";
        }
        $html .= "</tr>";
    }

    // Baris Total (Footer)
    if (!empty($aggData) && !is_null($totalCol)) {
        $html .= "<tr class=rowcontent style='font-weight:bold; background-color:#eee;'><td align=center>Total</td>";
        $ftTotal = 0;

        for ($i = 1; $i <= 12; $i++) {
            if ($isPercent && $compareData) {
                // Mengambil variabel global sementara untuk total footer persentase
                global $totalActual, $totalTarget;
                $tAct = isset($totalActual[$i]) ? $totalActual[$i] : 0;
                $tBgt = isset($totalTarget[$i]) ? $totalTarget[$i] : 0;
                $perc = ($tBgt > 0) ? ($tAct / $tBgt) * 100 : 0;

                $bg = "";
                if ($perc < 80) $bg = "background-color:pink";
                else if ($perc >= 100) $bg = "background-color:lightblue";

                $html .= "<td align=right style='" . $bg . "'>" . number_format($perc, 2, ',', '.') . "%</td>";
            } else {
                $sum = isset($totalCol[$i]) ? $totalCol[$i] : 0;
                $ftTotal += $sum;
                $html .= "<td align=right>" . number_format($sum, $decimals, ',', '.') . "</td>";
            }
        }

        // Grand Total (Pojok Kanan Bawah)
        if ($isPercent && $compareData) {
            global $totalActual, $totalTarget;
            $gAct = array_sum($totalActual);
            $gBgt = array_sum($totalTarget);
            $gPerc = ($gBgt > 0) ? ($gAct / $gBgt) * 100 : 0;

            $bg = "";
            if ($gPerc < 80) $bg = "background-color:pink";
            else if ($gPerc >= 100) $bg = "background-color:lightblue";

            $html .= "<td align=right style='" . $bg . "'>" . number_format($gPerc, 2, ',', '.') . "%</td>";
        } else {
            $html .= "<td align=right>" . number_format($ftTotal, $decimals, ',', '.') . "</td>";
        }
        $html .= "</tr>";
    }
    
    $html .= "</tbody></table>";
    return $html;
}

if ($proses == 'excel') {
    $border = "border=1";
}
$stream = "";

// Validasi Blok (Mengambil Blok Aktif & Tahun Tanam)
$stream = "";

foreach($unitIds as $uid) {
    if($uid == '') continue;
    $namaUnitStr = isset($namaOrg[$uid]) ? $namaOrg[$uid] : '';
    
    if ($proses == 'excel') {
        $stream .= "<table border=0><tr><td colspan=14 style='font-size:16px; font-weight:bold; background-color:#ffe600;'>UNIT: " . $namaUnitStr . " (" . $uid . ")</td></tr></table><br>";
    } else {
        $stream .= "<div style='font-size:16px; font-weight:bold; background-color:#ffe600; padding:10px; margin-bottom:10px;'>UNIT: " . $namaUnitStr . " (" . $uid . ")</div>";
    }

$kodeblok = array();
$str = "SELECT kodeorg, tahuntanam, luasareaproduktif, intiplasma 
        FROM " . $dbname . ".setup_blok 
        WHERE kodeorg LIKE '" . $uid . "%' " . $inplas . " AND statusblok='TM' 
        ORDER BY substr(kodeorg,1,6) ASC, tahuntanam ASC";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
while ($bar = $res->fetch(PDO::FETCH_OBJ)) {
    $kodeblok[$bar->kodeorg] = $bar->kodeorg;
}

// Array Initialization (Default empty)
$aggData = array();
$totalTarget = array();
$totalActual = array();
$gradingData = array();
$tandanData = array();
$bruttoHistData = array();

if (isset($kodeblok) && !empty($kodeblok)) {
    // Array Utama Penampung Data (Dimensi: Tahun Tanam -> Jenis Data -> Bulan)
    // $aggData = array(); // Already init
    // $totalTarget = array();
    // $totalActual = array();

    // QUERY 1: TARGET PRODUKSI (TABEL 1)
    // Mengambil data target (kg01..kg12) dari bgt_produksi_kebun
    $strTarget = "SELECT tahuntanam, 
                         SUM(kg01) AS kg01, SUM(kg02) AS kg02, SUM(kg03) AS kg03, 
                         SUM(kg04) AS kg04, SUM(kg05) AS kg05, SUM(kg06) AS kg06,
                         SUM(kg07) AS kg07, SUM(kg08) AS kg08, SUM(kg09) AS kg09,
                         SUM(kg10) AS kg10, SUM(kg11) AS kg11, SUM(kg12) AS kg12
                  FROM " . $dbname . ".bgt_produksi_kebun
                  WHERE tahunbudget = '" . $periode . "' AND kodeblok LIKE '" . $uid . "%'
                  $inplas
                  GROUP BY tahuntanam
                  ORDER BY tahuntanam";
    
    // echo "DEBUG QUERY 1 (TARGET):<br>" . $strTarget . "<br><br>";
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

    // QUERY 2: AKTUAL PRODUKSI BRUTTO (TABEL 2 & 3)
    // Mengambil data berat bersih dari tabel pabrik_timbangan
    $strB = "SELECT tahuntanam, LEFT(tanggal, 7) as periode, SUM(beratbersih + kgpotsortasi) as total_brutto 
             FROM " . $dbname . ".pabrik_timbangan 
             WHERE kodeorg LIKE '" . $uid . "%' AND tanggal LIKE '" . $periode . "%' 
             $inplas
             GROUP BY tahuntanam, LEFT(tanggal, 7)";
    // exit('warning '.$strB);
    // echo "DEBUG QUERY 2 (ACTUAL):<br>" . $strB . "<br><br>";
    $resB = $owlPDO->query($strB);
    if ($resB) {
        while ($barB = $resB->fetch(PDO::FETCH_OBJ)) {
            $y = $barB->tahuntanam;
            $m = intval(substr($barB->periode, 5, 2));
            if (!empty($y) && $y != '0') {
                // Masukkan ke array utama
                if (!isset($aggData[$y]['actual'][$m])) $aggData[$y]['actual'][$m] = 0;
                $aggData[$y]['actual'][$m] += $barB->total_brutto;

                if (!isset($totalActual[$m])) $totalActual[$m] = 0;
                $totalActual[$m] += $barB->total_brutto;
            }
        }
    }

    // Normalisasi Array (Pastikan semua slot terisi 0 jika kosong)
    foreach ($aggData as $y => &$d) {
        for ($i = 1; $i <= 12; $i++) {
            if (!isset($d['actual'][$i])) $d['actual'][$i] = 0;
            if (!isset($d['budget'][$i])) $d['budget'][$i] = 0;
        }
    }
    ksort($aggData);
    ksort($totalActual); 
    
    // --- SAVE DATA TO SESSION FOR CHARTS ---
    $_SESSION['mr_trenProduksiTbs_aggData'][$uid] = $aggData;
} // End if(isset($kodeblok))
    
    // --- OUTPUT TABEL 1, 2, 3 ---
    
    // START SIDE-BY-SIDE LAYOUT
    $stream .= "<div style='display:flex; flex-direction:row; gap:20px; align-items:flex-start; width:max-content; margin-bottom:20px;'>";
    
    // ================== [KIRI: TABEL 1 - TARGET PRODUKSI] ==================
    $stream .= "<div style='flex: 0 0 75vw; padding-right:10px;'>"; 
    $stream .= generateTable("Target Produksi Kebun " . $periode, 'budget', $aggData, $totalTarget, $months);
    $stream .= "</div>";

    // ================== [KANAN: TABEL % SEBARAN] ==================
    // Logika perhitungan % Sebaran
    // Kolom: Jan-Dec, Total, TW1-4, SM1-2
    
    $sebaranData = array();
    $sebaranTotalCol = array_fill(1, 12, 0); // 1-12
    $sebaranTotalTW = array_fill(1, 4, 0);   // 1-4
    $sebaranTotalSM = array_fill(1, 2, 0);   // 1-2
    $sebaranGrandTotal = 0;
    
    // Hitung Sebaran per Baris (Tahun Tanam)
    foreach ($aggData as $thn => $d) {
        $rowTotalBudget = 0;
        // Hitung total setahun dulu untuk penyebut
        for($i=1; $i<=12; $i++) {
            $rowTotalBudget += isset($d['budget'][$i]) ? $d['budget'][$i] : 0;
        }

        // Hitung % per bulan
        for($i=1; $i<=12; $i++) {
            $val = isset($d['budget'][$i]) ? $d['budget'][$i] : 0;
            // Rumus: (Value / AvailableTotalYear) * 100
            $perc = ($rowTotalBudget > 0) ? ($val / $rowTotalBudget) * 100 : 0;
            
            $sebaranData[$thn]['monthly'][$i] = $perc;
        }
    }
    
    $grandTotalBudget = array_sum($totalTarget);
    for($i=1; $i<=12; $i++) {
        $colVal = isset($totalTarget[$i]) ? $totalTarget[$i] : 0;
        $perc = ($grandTotalBudget > 0) ? ($colVal / $grandTotalBudget) * 100 : 0;
        $sebaranTotalCol[$i] = $perc;
    }

    // --- Generate HTML Tabel Sebaran ---
    $stream .= "<div style='flex: 0 0 auto;'>";
    if ($proses == 'excel') {
        $stream .= "<table border=1 cellpadding=5>";
        $stream .= "<tr><td colspan=19 style='border:none; height:10px;'></td></tr>"; // Spacing
        $stream .= "<tr><td colspan=19 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff;'>% SEBARAN (" . $periode . " Target)</td></tr>";
        $stream .= "<thead>";
    } else {
        $stream .= "<br><table class=sortable cellspacing=1 border=0 cellpadding=5>";
        $stream .= "<thead>";
        $stream .= "<tr><td colspan=19 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff; padding:5px;'>% SEBARAN</td></tr>";
    }
    // Note: Table opening tag moved inside if/else or removed if redundant. 
    // The previous code had a duplicate table tag after this block. We remove it by consuming it in TargetContent.
    
    // Header
    $stream .= "<tr class=rowheader style='background-color:#444; color:#fff;'>";
    foreach ($months as $mon) $stream .= "<td align=center style='width:50px'>".$mon."</td>";
    $stream .= "<td align=center style='width:60px'>TOTAL</td>";
    $stream .= "<td align=center style='width:50px'>TW-1</td>";
    $stream .= "<td align=center style='width:50px'>TW-2</td>";
    $stream .= "<td align=center style='width:50px'>TW-3</td>";
    $stream .= "<td align=center style='width:50px'>TW-4</td>";
    $stream .= "<td align=center style='width:50px'>SM-1</td>";
    $stream .= "<td align=center style='width:50px'>SM-2</td>";
    $stream .= "</tr></thead><tbody>";
    
    // Body
    if(empty($sebaranData)){
       $stream .= "<tr class=rowcontent><td colspan=19 align=center>Data Empty for ".$unit." ".$periode."</td></tr>";
    }
    foreach($sebaranData as $thn => $d) {
        $stream .= "<tr class=rowcontent>";
        
        $rowSum = 0;
        $tw = array(1=>0, 2=>0, 3=>0, 4=>0);
        $sm = array(1=>0, 2=>0);
        
        // Loop Bulan
        for($i=1; $i<=12; $i++) {
            $val = $d['monthly'][$i];
            $rowSum += $val;
            
            // TW & SM Accumulation
            if($i<=3) $tw[1] += $val;
            elseif($i<=6) $tw[2] += $val;
            elseif($i<=9) $tw[3] += $val;
            else $tw[4] += $val;
            
            if($i<=6) $sm[1] += $val;
            else $sm[2] += $val;
            
            $stream .= "<td align=right>".number_format($val, 2, ',', '.')."</td>";
        }
        
        // Total (Harusnya 100)
        $stream .= "<td align=right style='font-weight:bold; background-color:#eee;'>".number_format($rowSum, 2, ',', '.')."</td>"; // 100
        
        // Output TW
        for($k=1; $k<=4; $k++) $stream .= "<td align=right>".number_format($tw[$k], 2, ',', '.')."</td>";
        
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[1], 2, ',', '.')."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[2], 2, ',', '.')."</td>";
        
        $stream .= "</tr>";
    }
    
    // Footer Total
    /*
    $stream .= "<tr class=rowcontent style='font-weight:bold; background-color:#eee;'>";
    $ftSum = 0;
    
    $ftTw = array(1=>0, 2=>0, 3=>0, 4=>0);
    $ftSm = array(1=>0, 2=>0);
    
    for($i=1; $i<=12; $i++) {
        $val = $sebaranTotalCol[$i];
        $ftSum += $val;
        
        if($i<=3) $ftTw[1] += $val;
        elseif($i<=6) $ftTw[2] += $val;
        elseif($i<=9) $ftTw[3] += $val;
        else $ftTw[4] += $val;
        
        if($i<=6) $ftSm[1] += $val;
        else $ftSm[2] += $val;
        
        $stream .= "<td align=right>".number_format($val, 2, ',', '.')."</td>";
    }
    
    $stream .= "<td align=right>".number_format($ftSum, 2, ',', '.')."</td>";
    
    for($k=1; $k<=4; $k++) $stream .= "<td align=right>".number_format($ftTw[$k], 2, ',', '.')."</td>";
    
    $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($ftSm[1], 2, ',', '.')."</td>";
    $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($ftSm[2], 2, ',', '.')."</td>";
    
    $stream .= "</tr>";
    */
    $stream .= "</tbody></table>";
    $stream .= "</div>"; // End Right Div
    
    $stream .= "</div>"; // End Wrapper
    
    // Lanjut ke tabel lainnya (Tabel 2 dst) tetap di bawah layout side-by-side ini
    
    // Tabel 2: Aktual (Brutto)
    
    // START SIDE-BY-SIDE LAYOUT (ACTUAL)
    $stream .= "<div style='display:flex; flex-direction:row; gap:20px; align-items:flex-start; width:max-content; margin-bottom:20px;'>";
    
    // ================== [KIRI: TABEL 2 - AKTUAL PRODUKSI] ==================
    $stream .= "<div style='flex: 0 0 75vw; padding-right:10px;'>"; 
    $stream .= generateTable("Tren Produksi Kebun Aktual " . $periode . " (BRUTTO)", 'actual', $aggData, $totalActual, $months);
    $stream .= "</div>";

    // ================== [KANAN: TABEL % SEBARAN AKTUAL] ==================
    // Logic sama dengan Target, tapi pakai data Actual (Brutto)
    
    $sebaranActData = array();
    $sebaranActTotalCol = array_fill(1, 12, 0); 
    $sebaranActTotalTW = array_fill(1, 4, 0);   
    $sebaranActTotalSM = array_fill(1, 2, 0);   
    
    // Hitung Sebaran per Baris (Tahun Tanam)
    foreach ($aggData as $thn => $d) {
        $rowTotalAct = 0;
        // Hitung total setahun
        for($i=1; $i<=12; $i++) {
            $rowTotalAct += isset($d['actual'][$i]) ? $d['actual'][$i] : 0;
        }

        // Hitung % per bulan
        for($i=1; $i<=12; $i++) {
            $val = isset($d['actual'][$i]) ? $d['actual'][$i] : 0;
            $perc = ($rowTotalAct > 0) ? ($val / $rowTotalAct) * 100 : 0;
            
            $sebaranActData[$thn]['monthly'][$i] = $perc;
        }
    }
    
    // Footer Weighted Average
    $grandTotalAct = array_sum($totalActual);
    for($i=1; $i<=12; $i++) {
        $colVal = isset($totalActual[$i]) ? $totalActual[$i] : 0;
        $perc = ($grandTotalAct > 0) ? ($colVal / $grandTotalAct) * 100 : 0;
        $sebaranActTotalCol[$i] = $perc;
    }

    // --- Generate HTML Tabel Sebaran Actual ---
    $stream .= "<div style='flex: 0 0 auto;'>";
    if ($proses == 'excel') {
        $stream .= "<table border=1 cellpadding=5>";
        $stream .= "<tr><td colspan=19 style='border:none; height:10px;'></td></tr>"; // Spacing
        $stream .= "<tr><td colspan=19 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff;'>% SEBARAN (Actual Brutto)</td></tr>";
        $stream .= "<thead>";
    } else {
        $stream .= "<br><table class=sortable cellspacing=1 border=0 cellpadding=5>";
        $stream .= "<thead>";
        $stream .= "<tr><td colspan=19 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff; padding:5px;'>% SEBARAN</td></tr>";
    }
    
    // Header
    $stream .= "<tr class=rowheader style='background-color:#444; color:#fff;'>";
    foreach ($months as $mon) $stream .= "<td align=center style='width:50px'>".$mon."</td>";
    $stream .= "<td align=center style='width:60px'>TOTAL</td>";
    $stream .= "<td align=center style='width:50px'>TW-1</td>";
    $stream .= "<td align=center style='width:50px'>TW-2</td>";
    $stream .= "<td align=center style='width:50px'>TW-3</td>";
    $stream .= "<td align=center style='width:50px'>TW-4</td>";
    $stream .= "<td align=center style='width:50px'>SM-1</td>";
    $stream .= "<td align=center style='width:50px'>SM-2</td>";
    $stream .= "</tr></thead><tbody>";
    
    // Body
    if(empty($sebaranActData)){
       $stream .= "<tr class=rowcontent><td colspan=19 align=center>Data Empty for ".$unit." ".$periode."</td></tr>";
    }
    foreach($sebaranActData as $thn => $d) {
        $stream .= "<tr class=rowcontent>";
        
        $rowSum = 0;
        $tw = array(1=>0, 2=>0, 3=>0, 4=>0);
        $sm = array(1=>0, 2=>0);
        
        for($i=1; $i<=12; $i++) {
            $val = $d['monthly'][$i];
            $rowSum += $val;
            
            if($i<=3) $tw[1] += $val;
            elseif($i<=6) $tw[2] += $val;
            elseif($i<=9) $tw[3] += $val;
            else $tw[4] += $val;
            
            if($i<=6) $sm[1] += $val;
            else $sm[2] += $val;
            
            $stream .= "<td align=right>".number_format($val, 2, ',', '.')."</td>";
        }
        
        $stream .= "<td align=right style='font-weight:bold; background-color:#eee;'>".number_format($rowSum, 2, ',', '.')."</td>"; // 100
        
        for($k=1; $k<=4; $k++) $stream .= "<td align=right>".number_format($tw[$k], 2, ',', '.')."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[1], 2, ',', '.')."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[2], 2, ',', '.')."</td>";
        
        $stream .= "</tr>";
    }
    
    // Footer Total
    /*
    $stream .= "<tr class=rowcontent style='font-weight:bold; background-color:#eee;'>";
    $ftSum = 0;
    
    $ftTw = array(1=>0, 2=>0, 3=>0, 4=>0);
    $ftSm = array(1=>0, 2=>0);
    
    for($i=1; $i<=12; $i++) {
        $val = $sebaranActTotalCol[$i];
        $ftSum += $val;
        
        if($i<=3) $ftTw[1] += $val;
        elseif($i<=6) $ftTw[2] += $val;
        elseif($i<=9) $ftTw[3] += $val;
        else $ftTw[4] += $val;
        
        if($i<=6) $ftSm[1] += $val;
        else $ftSm[2] += $val;
        
        $stream .= "<td align=right>".number_format($val, 2, ',', '.')."</td>";
    }
    
    $stream .= "<td align=right>".number_format($ftSum, 2, ',', '.')."</td>"; // 100
    
    for($k=1; $k<=4; $k++) $stream .= "<td align=right>".number_format($ftTw[$k], 2, ',', '.')."</td>";
    $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($ftSm[1], 2, ',', '.')."</td>";
    $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($ftSm[2], 2, ',', '.')."</td>";
    
    $stream .= "</tr>";
    */
    $stream .= "</tbody></table>";
    $stream .= "</div>"; // End Right Div
    
    $stream .= "</div>"; // End Wrapper
    
    // Tabel 3: Persentase (Actual vs Target)
    $stream .= generateTable("Tren % Produksi Kebun Actual vs Target " . $periode, 'percent', $aggData, null, $months, true, true);


    // QUERY 3: DATA GRADING TBS (TABEL 4, 5, 6 - Ton/Ha)
    // Diperlukan sebagai pembagi untuk menghitung Ton/Ha
    $gradingData = array();
    $totalGrading = array();
    
    $strG = "SELECT tahuntanam, LEFT(tanggal, 7) as periode, SUM(kgpotsortasi) as total_grading
             FROM " . $dbname . ".pabrik_timbangan
             WHERE kodeorg LIKE '" . $uid . "%' AND tanggal LIKE '" . $periode . "%'
             $inplas
             GROUP BY tahuntanam, LEFT(tanggal, 7)";
             
    // echo "DEBUG QUERY 3 (GRADING):<br>" . $strG . "<br><br>";
    $resG = $owlPDO->query($strG);
    if ($resG) {
        while ($bar = $resG->fetch(PDO::FETCH_OBJ)) {
            $y = $bar->tahuntanam;
            $m = intval(substr($bar->periode, 5, 2));
            if (!empty($y) && $y != '0') {
                $gradingData[$y][$m] = $bar->total_grading;
                if (!isset($totalGrading[$m])) $totalGrading[$m] = 0;
                $totalGrading[$m] += $bar->total_grading;
            }
        }
    }

    // Kalkulasi Ton/Ha (Target, Actual, Variance)
    $aggDataHa = array();
    $totalTargetHa = array();
    $totalActualHa = array();
    $totalDiffHa = array();

    foreach ($aggData as $thn => $d) {
        for ($i = 1; $i <= 12; $i++) {
            // Data Grading (Pembagi) dalam Ton
            $gradKg = isset($gradingData[$thn][$i]) ? $gradingData[$thn][$i] : 0;
            $divisor = ($gradKg > 0) ? ($gradKg / 1000) : 0; 

            // Hitung Target Ton/Ha
            $bgtKg = isset($d['budget'][$i]) ? $d['budget'][$i] : 0;
            $aggDataHa[$thn]['budget'][$i] = ($divisor > 0) ? ($bgtKg / 1000) / $divisor : 0;

            // Hitung Aktual Ton/Ha
            $actKg = isset($d['actual'][$i]) ? $d['actual'][$i] : 0;
            $actTonHa = ($divisor > 0) ? ($actKg / 1000) / $divisor : 0;
            $aggDataHa[$thn]['actual'][$i] = $actTonHa;

            // Hitung Variance
            $aggDataHa[$thn]['diff'][$i] = $actTonHa - $aggDataHa[$thn]['budget'][$i];
        }
    }

    // Hitung Total Footer Ton/Ha (Rata-rata Tertimbang)
    for ($i = 1; $i <= 12; $i++) {
        $totGradKg = isset($totalGrading[$i]) ? $totalGrading[$i] : 0;
        $divFooter = ($totGradKg > 0) ? ($totGradKg / 1000) : 0;

        $totBgtKg = isset($totalTarget[$i]) ? $totalTarget[$i] : 0;
        $totalTargetHa[$i] = ($divFooter > 0) ? ($totBgtKg / 1000) / $divFooter : 0;

        $totActKg = isset($totalActual[$i]) ? $totalActual[$i] : 0;
        $actHaFooter = ($divFooter > 0) ? ($totActKg / 1000) / $divFooter : 0;
        $totalActualHa[$i] = $actHaFooter;
        
        $totalDiffHa[$i] = $actHaFooter - $totalTargetHa[$i];
    }
    
    // Tabel 4: Target Ton/Ha
    
    // START SIDE-BY-SIDE LAYOUT (TARGET TON/HA)
    $stream .= "<div style='display:flex; flex-direction:row; gap:20px; align-items:flex-start; width:max-content; margin-bottom:20px;'>";
    
    // ================== [KIRI: TABEL 4 - TARGET TON/HA] ==================
    $stream .= "<div style='flex: 0 0 75vw; padding-right:10px;'>"; 
    $stream .= generateTable("Target Produksi Kebun " . $periode . " Ton/Ha", 'budget', $aggDataHa, $totalTargetHa, $months, false, null, 2);
    $stream .= "</div>";

    // ================== [KANAN: TABEL % SEBARAN TARGET TON/HA] ==================    
    $sebaranHaData = array();
    $sebaranHaTotalCol = array_fill(1, 12, 0); 
    
    // Hitung Sebaran per Baris (Tahun Tanam)
    foreach ($aggDataHa as $thn => $d) {
        $rowTotalHa = 0;

        for($i=1; $i<=12; $i++) {
            $rowTotalHa += isset($d['budget'][$i]) ? $d['budget'][$i] : 0;
        }

        // Hitung % per bulan
        for($i=1; $i<=12; $i++) {
            $val = isset($d['budget'][$i]) ? $d['budget'][$i] : 0;
            $perc = ($rowTotalHa > 0) ? ($val / $rowTotalHa) * 100 : 0;
            
            $sebaranHaData[$thn]['monthly'][$i] = $perc;
        }
    }
    
    // Footer Weighted Average
    $grandTotalHa = array_sum($totalTargetHa);
    for($i=1; $i<=12; $i++) {
        $colVal = isset($totalTargetHa[$i]) ? $totalTargetHa[$i] : 0;
        $perc = ($grandTotalHa > 0) ? ($colVal / $grandTotalHa) * 100 : 0;
        $sebaranHaTotalCol[$i] = $perc;
    }

    // --- Generate HTML Tabel Sebaran Target Ton/Ha ---
    $stream .= "<div style='flex: 0 0 auto;'>";
    if ($proses == 'excel') {
        $stream .= "<table border=1 cellpadding=5>";
        $stream .= "<tr><td colspan=19 style='border:none; height:10px;'></td></tr>"; // Spacing
        $stream .= "<tr><td colspan=19 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff;'>% SEBARAN (" . $periode . " Target Ton/Ha)</td></tr>";
        $stream .= "<thead>";
    } else {
        $stream .= "<br><table class=sortable cellspacing=1 border=0 cellpadding=5>";
        $stream .= "<thead>";
        $stream .= "<tr><td colspan=19 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff; padding:5px;'>% SEBARAN</td></tr>";
    }
    
    // Header
    $stream .= "<tr class=rowheader style='background-color:#444; color:#fff;'>";
    foreach ($months as $mon) $stream .= "<td align=center style='width:50px'>".$mon."</td>";
    $stream .= "<td align=center style='width:60px'>TOTAL</td>";
    $stream .= "<td align=center style='width:50px'>TW-1</td>";
    $stream .= "<td align=center style='width:50px'>TW-2</td>";
    $stream .= "<td align=center style='width:50px'>TW-3</td>";
    $stream .= "<td align=center style='width:50px'>TW-4</td>";
    $stream .= "<td align=center style='width:50px'>SM-1</td>";
    $stream .= "<td align=center style='width:50px'>SM-2</td>";
    $stream .= "</tr></thead><tbody>";
    
    // Body
    if(empty($sebaranHaData)){
       $stream .= "<tr class=rowcontent><td colspan=19 align=center>Data Empty for ".$unit." ".$periode."</td></tr>";
    }
    foreach($sebaranHaData as $thn => $d) {
        $stream .= "<tr class=rowcontent>";
        
        $rowSum = 0;
        $tw = array(1=>0, 2=>0, 3=>0, 4=>0);
        $sm = array(1=>0, 2=>0);
        
        for($i=1; $i<=12; $i++) {
            $val = $d['monthly'][$i];
            $rowSum += $val;
            
            if($i<=3) $tw[1] += $val;
            elseif($i<=6) $tw[2] += $val;
            elseif($i<=9) $tw[3] += $val;
            else $tw[4] += $val;
            
            if($i<=6) $sm[1] += $val;
            else $sm[2] += $val;
            
            $stream .= "<td align=right>".number_format($val, 2, ',', '.')."</td>";
        }
        
        $stream .= "<td align=right style='font-weight:bold; background-color:#eee;'>".number_format($rowSum, 2, ',', '.')."</td>"; // 100
        
        for($k=1; $k<=4; $k++) $stream .= "<td align=right>".number_format($tw[$k], 2, ',', '.')."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[1], 2, ',', '.')."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[2], 2, ',', '.')."</td>";
        
        $stream .= "</tr>";
    }
    
    // Footer Total
    /*
    $stream .= "<tr class=rowcontent style='font-weight:bold; background-color:#eee;'>";
    $ftSum = 0;
    
    $ftTw = array(1=>0, 2=>0, 3=>0, 4=>0);
    $ftSm = array(1=>0, 2=>0);
    
    for($i=1; $i<=12; $i++) {
        $val = $sebaranHaTotalCol[$i];
        $ftSum += $val;
        
        if($i<=3) $ftTw[1] += $val;
        elseif($i<=6) $ftTw[2] += $val;
        elseif($i<=9) $ftTw[3] += $val;
        else $ftTw[4] += $val;
        
        if($i<=6) $ftSm[1] += $val;
        else $ftSm[2] += $val;
        
        $stream .= "<td align=right>".number_format($val, 2, ',', '.')."</td>";
    }
    
    $stream .= "<td align=right>".number_format($ftSum, 2, ',', '.')."</td>"; // 100
    
    for($k=1; $k<=4; $k++) $stream .= "<td align=right>".number_format($ftTw[$k], 2, ',', '.')."</td>";
    $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($ftSm[1], 2, ',', '.')."</td>";
    $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($ftSm[2], 2, ',', '.')."</td>";
    
    $stream .= "</tr>";
    */
    $stream .= "</tbody></table>";
    $stream .= "</div>"; // End Right Div
    
    $stream .= "</div>"; // End Wrapper
    
    // Tabel 5: Actual Ton/Ha
    
    // START SIDE-BY-SIDE LAYOUT (ACTUAL TON/HA)
    $stream .= "<div style='display:flex; flex-direction:row; gap:20px; align-items:flex-start; width:max-content; margin-bottom:20px;'>";
    
    // ================== [KIRI: TABEL 5 - ACTUAL TON/HA] ==================
    $stream .= "<div style='flex: 0 0 75vw; padding-right:10px;'>"; 
    $stream .= generateTable("Tren Produksi Kebun " . $periode . " Ton/Ha", 'actual', $aggDataHa, $totalActualHa, $months, false, null, 2);
    $stream .= "</div>";

    // ================== [KANAN: TABEL % SEBARAN ACTUAL TON/HA] ==================
    $sebaranActHaData = array();
    $sebaranActHaTotalCol = array_fill(1, 12, 0); 
    
    foreach ($aggDataHa as $thn => $d) {
        $rowTotalActHa = 0;
        for($i=1; $i<=12; $i++) {
            $rowTotalActHa += isset($d['actual'][$i]) ? $d['actual'][$i] : 0;
        }

        for($i=1; $i<=12; $i++) {
            $val = isset($d['actual'][$i]) ? $d['actual'][$i] : 0;
            $perc = ($rowTotalActHa > 0) ? ($val / $rowTotalActHa) * 100 : 0;
            $sebaranActHaData[$thn]['monthly'][$i] = $perc;
        }
    }
    
    $grandTotalActHa = array_sum($totalActualHa);
    for($i=1; $i<=12; $i++) {
        $colVal = isset($totalActualHa[$i]) ? $totalActualHa[$i] : 0;
        $perc = ($grandTotalActHa > 0) ? ($colVal / $grandTotalActHa) * 100 : 0;
        $sebaranActHaTotalCol[$i] = $perc;
    }

    $stream .= "<div style='flex: 0 0 auto;'>";
    if ($proses == 'excel') {
        $stream .= "<table border=1 cellpadding=5>";
        $stream .= "<tr><td colspan=19 style='border:none; height:10px;'></td></tr>"; // Spacing
        $stream .= "<thead>";
        $stream .= "<tr><td colspan=19 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff;'>% SEBARAN (" . $periode . " Actual Ton/Ha)</td></tr>";
    } else {
        $stream .= "<br><div style='margin-top:20px; font-weight:bold; font-size:14px; background-color:#333; color:#fff; padding:5px; text-align:center;'>% SEBARAN</div>";
        $stream .= "<table class=sortable cellspacing=1 border=0 cellpadding=5>";
        $stream .= "<thead>";
    }
    
    // Header
    $stream .= "<tr class=rowheader style='background-color:#444; color:#fff;'>";
    foreach ($months as $mon) $stream .= "<td align=center style='width:50px'>".$mon."</td>";
    $stream .= "<td align=center style='width:60px'>TOTAL</td>";
    $stream .= "<td align=center style='width:50px'>TW-1</td>";
    $stream .= "<td align=center style='width:50px'>TW-2</td>";
    $stream .= "<td align=center style='width:50px'>TW-3</td>";
    $stream .= "<td align=center style='width:50px'>TW-4</td>";
    $stream .= "<td align=center style='width:50px'>SM-1</td>";
    $stream .= "<td align=center style='width:50px'>SM-2</td>";
    $stream .= "</tr></thead><tbody>";
    
    // Body
    if(empty($sebaranActHaData)){
       $stream .= "<tr class=rowcontent><td colspan=19 align=center>Data Empty for ".$unit." ".$periode."</td></tr>";
    }
    foreach($sebaranActHaData as $thn => $d) {
        $stream .= "<tr class=rowcontent>";
        
        $rowSum = 0;
        $tw = array(1=>0, 2=>0, 3=>0, 4=>0);
        $sm = array(1=>0, 2=>0);
        
        for($i=1; $i<=12; $i++) {
            $val = $d['monthly'][$i];
            $rowSum += $val;
            
            if($i<=3) $tw[1] += $val;
            elseif($i<=6) $tw[2] += $val;
            elseif($i<=9) $tw[3] += $val;
            else $tw[4] += $val;
            
            if($i<=6) $sm[1] += $val;
            else $sm[2] += $val;
            
            $stream .= "<td align=right>".number_format($val, 2, ',', '.')."</td>";
        }
        
        $stream .= "<td align=right style='font-weight:bold; background-color:#eee;'>".number_format($rowSum, 2, ',', '.')."</td>"; 
        
        for($k=1; $k<=4; $k++) $stream .= "<td align=right>".number_format($tw[$k], 2, ',', '.')."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[1], 2, ',', '.')."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[2], 2, ',', '.')."</td>";
        $stream .= "</tr>";
    }
    
    // Footer
    /*
    $stream .= "<tr class=rowcontent style='font-weight:bold; background-color:#eee;'>";
    $ftSum = 0;
    $ftTw = array(1=>0, 2=>0, 3=>0, 4=>0);
    $ftSm = array(1=>0, 2=>0);
    
    for($i=1; $i<=12; $i++) {
        $val = $sebaranActHaTotalCol[$i];
        $ftSum += $val;
        
        if($i<=3) $ftTw[1] += $val;
        elseif($i<=6) $ftTw[2] += $val;
        elseif($i<=9) $ftTw[3] += $val;
        else $ftTw[4] += $val;
        if($i<=6) $ftSm[1] += $val;
        else $ftSm[2] += $val;
        
        $stream .= "<td align=right>".number_format($val, 2, ',', '.')."</td>";
    }
    $stream .= "<td align=right>".number_format($ftSum, 2, ',', '.')."</td>"; 
    for($k=1; $k<=4; $k++) $stream .= "<td align=right>".number_format($ftTw[$k], 2, ',', '.')."</td>";
    $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($ftSm[1], 2, ',', '.')."</td>";
    $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($ftSm[2], 2, ',', '.')."</td>";
    $stream .= "</tr>";
    */
    $stream .= "</tbody></table>";
    $stream .= "</div>"; // End Right Div
    $stream .= "</div>"; // End Wrapper
    
    // Tabel 6: Variance
    $stream .= generateTable("Tren % Produksi Kebun Actual vs Target " . $periode . " Ha/Ton", 'diff', $aggDataHa, $totalDiffHa, $months, false, null, 2);


    // QUERY 4: DATA TANDAN (TABEL 7)
    $tandanData = array();
    $totalTandan = array();
    
    $strT = "SELECT LEFT(tanggal, 4) as tahun, LEFT(tanggal, 7) as periode, SUM(jumlahtandan1) as jjg 
             FROM " . $dbname . ".pabrik_timbangan 
             WHERE kodeorg LIKE '" . $uid . "%' AND LEFT(tanggal, 4) <= '" . $periode . "'
             GROUP BY LEFT(tanggal, 4), LEFT(tanggal, 7)
             ORDER BY LEFT(tanggal, 4) ASC";
             
    // echo "DEBUG QUERY 4 (TANDAN BY TAHUN TRANSAKSI - HISTORI):<br>" . $strT . "<br><br>";
    $resT = $owlPDO->query($strT);
    if ($resT) {
        while ($bar = $resT->fetch(PDO::FETCH_OBJ)) {
            $y = $bar->tahun;
            $m = intval(substr($bar->periode, 5, 2));

            $tandanData[$y]['actual'][$m] = $bar->jjg;
            if (!isset($totalTandan[$m])) $totalTandan[$m] = 0;
            $totalTandan[$m] += $bar->jjg;
        }
    }
    // Normalisasi Array
    // ksort($tandanData); // Sudah sorted by query ASC
    foreach ($tandanData as $y => &$d) {
        for ($i = 1; $i <= 12; $i++) {
            if (!isset($d['actual'][$i])) $d['actual'][$i] = 0;
            $d['budget'][$i] = 0; 
        }
    }
    // --- OUTPUT TABEL Tandan ---
    // START SIDE-BY-SIDE LAYOUT (TANDAN)
    $stream .= "<div style='display:flex; flex-direction:row; gap:20px; align-items:flex-start; width:max-content; margin-bottom:20px;'>";
    
    // ================== [KIRI: TABEL 7 - TANDAN] ==================
    $stream .= "<div style='flex: 0 0 75vw; padding-right:10px;'>"; 
    $stream .= generateTable("Tren Produksi Kebun - Tandan", 'actual', $tandanData, null, $months, false, null, 0, "Tahun Histori");
    $stream .= "</div>";

    // ================== [KANAN: TABEL % SEBARAN TANDAN] ==================
    $sebaranTandanData = array();
    $sebaranTandanTotalCol = array_fill(1, 12, 0); 
    
    foreach ($tandanData as $thn => $d) {
        $rowTotalTandan = 0;
        for($i=1; $i<=12; $i++) $rowTotalTandan += isset($d['actual'][$i]) ? $d['actual'][$i] : 0;

        for($i=1; $i<=12; $i++) {
            $val = isset($d['actual'][$i]) ? $d['actual'][$i] : 0;
            $perc = ($rowTotalTandan > 0) ? ($val / $rowTotalTandan) * 100 : 0;
            $sebaranTandanData[$thn]['monthly'][$i] = $perc;
        }
    }
    
    $grandTotalTandan = array_sum($totalTandan);
    for($i=1; $i<=12; $i++) {
        $colVal = isset($totalTandan[$i]) ? $totalTandan[$i] : 0;
        $perc = ($grandTotalTandan > 0) ? ($colVal / $grandTotalTandan) * 100 : 0;
        $sebaranTandanTotalCol[$i] = $perc;
    }

    $stream .= "<div style='flex: 0 0 auto;'>";
    if ($proses == 'excel') {
        $stream .= "<table border=1 cellpadding=5>";
        $stream .= "<tr><td colspan=19 style='border:none; height:10px;'></td></tr>"; // Spacing
        $stream .= "<thead>";
        $stream .= "<tr><td colspan=19 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff;'>% SEBARAN (Tandan)</td></tr>";
    } else {
        $stream .= "<br><div style='margin-top:20px; font-weight:bold; font-size:14px; background-color:#333; color:#fff; padding:5px; text-align:center;'>% SEBARAN</div>";
        $stream .= "<table class=sortable cellspacing=1 border=0 cellpadding=5>";
        $stream .= "<thead>";
    }
    
    $stream .= "<tr class=rowheader style='background-color:#444; color:#fff;'>";
    foreach ($months as $mon) $stream .= "<td align=center style='width:50px'>".$mon."</td>";
    $stream .= "<td align=center style='width:60px'>TOTAL</td>";
    $stream .= "<td align=center style='width:50px'>TW-1</td>";
    $stream .= "<td align=center style='width:50px'>TW-2</td>";
    $stream .= "<td align=center style='width:50px'>TW-3</td>";
    $stream .= "<td align=center style='width:50px'>TW-4</td>";
    $stream .= "<td align=center style='width:50px'>SM-1</td>";
    $stream .= "<td align=center style='width:50px'>SM-2</td>";
    $stream .= "</tr></thead><tbody>";
    
    if(empty($sebaranTandanData)){
       $stream .= "<tr class=rowcontent><td colspan=19 align=center>Data Empty for ".$unit." ".$periode."</td></tr>";
    }
    foreach($sebaranTandanData as $thn => $d) {
        $stream .= "<tr class=rowcontent>";
        $rowSum = 0;
        $tw = array(1=>0, 2=>0, 3=>0, 4=>0);
        $sm = array(1=>0, 2=>0);
        
        for($i=1; $i<=12; $i++) {
            $val = $d['monthly'][$i];
            $rowSum += $val;
            if($i<=3) $tw[1] += $val; elseif($i<=6) $tw[2] += $val; elseif($i<=9) $tw[3] += $val; else $tw[4] += $val;
            if($i<=6) $sm[1] += $val; else $sm[2] += $val;
            $stream .= "<td align=right>".number_format($val, 2, ',', '.')."</td>";
        }
        $stream .= "<td align=right style='font-weight:bold; background-color:#eee;'>".number_format($rowSum, 2, ',', '.')."</td>"; 
        for($k=1; $k<=4; $k++) $stream .= "<td align=right>".number_format($tw[$k], 2, ',', '.')."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[1], 2, ',', '.')."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[2], 2, ',', '.')."</td>";
        $stream .= "</tr>";
    }
    
    $stream .= "</tbody></table>";
    $stream .= "</div>"; // End Right Div
    $stream .= "</div>"; // End Wrapper


    // QUERY 5: DATA TONASE BRUTTO (TABEL 8)
    // Grouping: Transaction Year (LEFT(tanggal, 4))
    
    $bruttoHistData = array(); 
    $totalBruttoHist = array();
    
    $selectedYear = intval($periode);
    $startYear = $selectedYear - 2; 

    // Query: Filter range (StartYear - SelectedYear) AND VALID PLANTING YEAR (to match Table 2)
    $strBh = "SELECT LEFT(tanggal, 4) as tahun, LEFT(tanggal, 7) as periode_bln, SUM(beratbersih) as kg
             FROM " . $dbname . ".pabrik_timbangan 
             WHERE kodeorg LIKE '" . $uid . "%' 
               AND LEFT(tanggal, 4) >= '" . $startYear . "' 
               AND LEFT(tanggal, 4) <= '" . $selectedYear . "'
               AND tahuntanam != '' AND tahuntanam != '0' 
             GROUP BY LEFT(tanggal, 4), LEFT(tanggal, 7)
             ORDER BY LEFT(tanggal, 4) ASC, LEFT(tanggal, 7) ASC";
             
    $resBh = $owlPDO->query($strBh);
    if ($resBh) {
        while ($bar = $resBh->fetch(PDO::FETCH_OBJ)) {
            $y = $bar->tahun; // Transaction Year (e.g. 2023, 2024, 2025)
            $m = intval(substr($bar->periode_bln, 5, 2));
            $ton = $bar->kg / 1000;

            $bruttoHistData[$y]['actual'][$m] = $ton;
            
            // For historical table, footer usually sums columns.
            if (!isset($totalBruttoHist[$m])) $totalBruttoHist[$m] = 0;
            $totalBruttoHist[$m] += $ton;
        }
    }
    
    // Normalisasi Array (Simple loop, no sync with Planting Year tables)
    foreach ($bruttoHistData as $y => $d) {
        for ($i = 1; $i <= 12; $i++) {
            if (!isset($bruttoHistData[$y]['actual'][$i])) $bruttoHistData[$y]['actual'][$i] = 0;
            $bruttoHistData[$y]['budget'][$i] = 0; 
        }
    }
    
    // --- SAVE HISTORICAL DATA TO SESSION FOR CHART 2 ---
    $_SESSION['mr_trenProduksiTbs_bruttoHistData'][$uid] = $bruttoHistData;
    
    // START OUTPUT TABEL 8 ---
    
    // --- OUTPUT TABEL 8 ---
    // Header "Tahun Histori"
    $stream .= "<div style='display:flex; flex-direction:row; gap:20px; align-items:flex-start; width:max-content; margin-bottom:20px;'>";
    
    // ================== [KIRI: TABEL 8 - BRUTTO] ==================
    $stream .= "<div style='flex: 0 0 75vw; padding-right:10px;'>"; 
    $stream .= generateTable("Tren Produksi Kebun Per Tahun (Tonase Brutto)", 'actual', $bruttoHistData, null, $months, false, null, 2, "Tahun Histori");
    $stream .= "</div>";

    // ================== [KANAN: TABEL % SEBARAN BRUTTO HIST] ==================
    $sebaranBHData = array();
    $sebaranBHTotalCol = array_fill(1, 12, 0); 
    
    foreach ($bruttoHistData as $thn => $d) {
        $rowTotalBH = 0;
        for($i=1; $i<=12; $i++) $rowTotalBH += isset($d['actual'][$i]) ? $d['actual'][$i] : 0;

        for($i=1; $i<=12; $i++) {
            $val = isset($d['actual'][$i]) ? $d['actual'][$i] : 0;
            $perc = ($rowTotalBH > 0) ? ($val / $rowTotalBH) * 100 : 0;
            $sebaranBHData[$thn]['monthly'][$i] = $perc;
        }
    }
    
    $grandTotalBH = array_sum($totalBruttoHist);
    for($i=1; $i<=12; $i++) {
        $colVal = isset($totalBruttoHist[$i]) ? $totalBruttoHist[$i] : 0;
        $perc = ($grandTotalBH > 0) ? ($colVal / $grandTotalBH) * 100 : 0;
        $sebaranBHTotalCol[$i] = $perc;
    }

    $stream .= "<div style='flex: 0 0 auto;'>";
    if ($proses == 'excel') {
        $stream .= "<table border=1 cellpadding=5>";
        $stream .= "<tr><td colspan=19 style='border:none; height:10px;'></td></tr>"; // Spacing
        $stream .= "<thead>";
        $stream .= "<tr><td colspan=19 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff;'>% SEBARAN (Tonase Brutto)</td></tr>";
    } else {
        $stream .= "<br><div style='margin-top:20px; font-weight:bold; font-size:14px; background-color:#333; color:#fff; padding:5px; text-align:center;'>% SEBARAN</div>";
        $stream .= "<table class=sortable cellspacing=1 border=0 cellpadding=5>";
        $stream .= "<thead>";
    }
    
    $stream .= "<thead><tr class=rowheader style='background-color:#444; color:#fff;'>";
    foreach ($months as $mon) $stream .= "<td align=center style='width:50px'>".$mon."</td>";
    $stream .= "<td align=center style='width:60px'>TOTAL</td>";
    $stream .= "<td align=center style='width:50px'>TW-1</td>";
    $stream .= "<td align=center style='width:50px'>TW-2</td>";
    $stream .= "<td align=center style='width:50px'>TW-3</td>";
    $stream .= "<td align=center style='width:50px'>TW-4</td>";
    $stream .= "<td align=center style='width:50px'>SM-1</td>";
    $stream .= "<td align=center style='width:50px'>SM-2</td>";
    $stream .= "</tr></thead><tbody>";
    
    if(empty($sebaranBHData)){
       $stream .= "<tr class=rowcontent><td colspan=19 align=center>Data Empty for ".$unit." ".$periode."</td></tr>";
    }
    foreach($sebaranBHData as $thn => $d) {
        $stream .= "<tr class=rowcontent>";
        $rowSum = 0;
        $tw = array(1=>0, 2=>0, 3=>0, 4=>0);
        $sm = array(1=>0, 2=>0);
        
        for($i=1; $i<=12; $i++) {
            $val = $d['monthly'][$i];
            $rowSum += $val;
            if($i<=3) $tw[1] += $val; elseif($i<=6) $tw[2] += $val; elseif($i<=9) $tw[3] += $val; else $tw[4] += $val;
            if($i<=6) $sm[1] += $val; else $sm[2] += $val;
            $stream .= "<td align=right>".number_format($val, 0)."</td>";
        }
        $stream .= "<td align=right style='font-weight:bold; background-color:#eee;'>".number_format($rowSum, 0)."</td>"; 
        for($k=1; $k<=4; $k++) $stream .= "<td align=right>".number_format($tw[$k], 0)."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[1], 0)."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[2], 0)."</td>";
        $stream .= "</tr>";
    }
    
    $stream .= "</tbody></table>";
    $stream .= "</div>"; // End Right Div
    $stream .= "</div>"; // End Wrapper


    // KALKULASI: BJR (TABEL 9)
    // Updated: Hitung Manual (Brutto Kg / Tandan) berdasarkan Tahun Histori
    // Formula: (Tonase Brutto * 1000) / Tandan
    
    $bjrData = array();
    $totalBjr = array();
    
    // Ambil semua tahun dari kedua dataset (Tandan & Brutto)
    $allYearsBJR = array_unique(array_merge(array_keys($tandanData), array_keys($bruttoHistData)));
    sort($allYearsBJR);
    
    foreach ($allYearsBJR as $y) {
        for ($i = 1; $i <= 12; $i++) {
            // Ambil Tandan
            $jjg = isset($tandanData[$y]['actual'][$i]) ? $tandanData[$y]['actual'][$i] : 0;
            
            // Ambil Brutto (Ton) convert to Kg
            $ton = isset($bruttoHistData[$y]['actual'][$i]) ? $bruttoHistData[$y]['actual'][$i] : 0;
            $kg = $ton * 1000;
            
            // Hitung BJR
            $valBjr = ($jjg > 0) ? $kg / $jjg : 0;
            
            $bjrData[$y]['actual'][$i] = $valBjr;
            $bjrData[$y]['budget'][$i] = 0;
        }
    }
    
    // Footer BJR (Rata-rata Tertimbang setahun) = Total Kg Setahun / Total Janjang Setahun
    for ($i = 1; $i <= 12; $i++) {
        $totJjg = isset($totalTandan[$i]) ? $totalTandan[$i] : 0;
        $totTon = isset($totalBruttoHist[$i]) ? $totalBruttoHist[$i] : 0;
        $totKg = $totTon * 1000;
        
        $totalBjr[$i] = ($totJjg > 0) ? $totKg / $totJjg : 0;
    }

    // --- OUTPUT TABEL 9 ---
    // START SIDE-BY-SIDE LAYOUT (BJR)
    $stream .= "<div style='display:flex; flex-direction:row; gap:20px; align-items:flex-start; width:max-content; margin-bottom:20px;'>";
    
    // ================== [KIRI: TABEL 9 - BJR] ==================
    $stream .= "<div style='flex: 0 0 75vw; padding-right:10px;'>"; 
    $stream .= generateTable("Tren Produksi Kebun - BJR", 'actual', $bjrData, null, $months, false, null, 2, "Tahun Histori");
    $stream .= "</div>";

    // ================== [KANAN: TABEL % SEBARAN BJR] ==================
    $sebaranBJRData = array();
    $sebaranBJRTotalCol = array_fill(1, 12, 0); 
    
    foreach ($bjrData as $thn => $d) {
        $rowTotalBJR = 0;
        for($i=1; $i<=12; $i++) $rowTotalBJR += isset($d['actual'][$i]) ? $d['actual'][$i] : 0;

        for($i=1; $i<=12; $i++) {
            $val = isset($d['actual'][$i]) ? $d['actual'][$i] : 0;
            $perc = ($rowTotalBJR > 0) ? ($val / $rowTotalBJR) * 100 : 0;
            $sebaranBJRData[$thn]['monthly'][$i] = $perc;
        }
    }
    
    $grandTotalBJR = array_sum($totalBjr);
    for($i=1; $i<=12; $i++) {
        $colVal = isset($totalBjr[$i]) ? $totalBjr[$i] : 0;
        $perc = ($grandTotalBJR > 0) ? ($colVal / $grandTotalBJR) * 100 : 0;
        $sebaranBJRTotalCol[$i] = $perc;
    }

    $stream .= "<div style='flex: 0 0 auto;'>";
    if ($proses == 'excel') {
        $stream .= "<table border=1 cellpadding=5>";
        $stream .= "<tr><td colspan=19 style='border:none; height:10px;'></td></tr>"; // Spacing
        $stream .= "<thead>";
        $stream .= "<tr><td colspan=19 align=center style='font-size:14px; font-weight:bold; background-color:#333; color:#fff;'>% SEBARAN (BJR)</td></tr>";
    } else {
        $stream .= "<br><div style='margin-top:20px; font-weight:bold; font-size:14px; background-color:#333; color:#fff; padding:5px; text-align:center;'>% SEBARAN</div>";
        $stream .= "<table class=sortable cellspacing=1 border=0 cellpadding=5>";
        $stream .= "<thead>";
    }
    
    $stream .= "<tr class=rowheader style='background-color:#444; color:#fff;'>";
    foreach ($months as $mon) $stream .= "<td align=center style='width:50px'>".$mon."</td>";
    $stream .= "<td align=center style='width:60px'>TOTAL</td>";
    $stream .= "<td align=center style='width:50px'>TW-1</td>";
    $stream .= "<td align=center style='width:50px'>TW-2</td>";
    $stream .= "<td align=center style='width:50px'>TW-3</td>";
    $stream .= "<td align=center style='width:50px'>TW-4</td>";
    $stream .= "<td align=center style='width:50px'>SM-1</td>";
    $stream .= "<td align=center style='width:50px'>SM-2</td>";
    $stream .= "</tr></thead><tbody>";
    
    if(empty($sebaranBJRData)){
       $stream .= "<tr class=rowcontent><td colspan=19 align=center>Data Empty for ".$unit." ".$periode."</td></tr>";
    }
    foreach($sebaranBJRData as $thn => $d) {
        $stream .= "<tr class=rowcontent>";
        $rowSum = 0;
        $tw = array(1=>0, 2=>0, 3=>0, 4=>0);
        $sm = array(1=>0, 2=>0);
        
        for($i=1; $i<=12; $i++) {
            $val = $d['monthly'][$i];
            $rowSum += $val;
            if($i<=3) $tw[1] += $val; elseif($i<=6) $tw[2] += $val; elseif($i<=9) $tw[3] += $val; else $tw[4] += $val;
            if($i<=6) $sm[1] += $val; else $sm[2] += $val;
            $stream .= "<td align=right>".number_format($val, 2, ',', '.')."</td>";
        }
        $stream .= "<td align=right style='font-weight:bold; background-color:#eee;'>".number_format($rowSum, 2, ',', '.')."</td>"; 
        for($k=1; $k<=4; $k++) $stream .= "<td align=right>".number_format($tw[$k], 2, ',', '.')."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[1], 2, ',', '.')."</td>";
        $stream .= "<td align=right style='background-color:#ffe4c4'>".number_format($sm[2], 2, ',', '.')."</td>";
        $stream .= "</tr>";
    }
    
    $stream .= "</tbody></table>";
    $stream .= "</div>"; // End Right Div
    $stream .= "</div>"; // End Wrapper

    // Data Sebaran Persentase (Standard)
    $sebaranData = array(6.745979734, 7.173113727, 7.685969321, 8.924026753, 9.25610709, 8.827819127, 8.691440674, 8.748010427, 8.204249104, 8.064988386, 8.367831727, 9.310463929);
    
    // Data Reference
    $refNorma = array(
        'TOPAZ' => array(
            3=>19.40, 4=>28.70, 5=>33.00, 6=>32.10, 7=>31.50, 8=>30.60, 9=>33.90, 10=>32.10,
            11=>34.70, 12=>32.20, 13=>31.60, 14=>31.80, 15=>33.30, 16=>33.60, 17=>32.20, 18=>30.80,
            19=>29.40, 20=>28.00, 21=>26.60, 22=>25.20, 23=>23.80, 24=>22.40, 25=>21.00
        ),
        'SIMALUNGUN' => array(
            3=>9.00, 4=>15.00, 5=>18.00, 6=>21.10, 7=>26.00, 8=>30.00, 9=>31.00, 10=>31.00,
            11=>31.00, 12=>31.00, 13=>31.00, 14=>30.00, 15=>27.90, 16=>27.10, 17=>26.00, 18=>24.90,
            19=>24.10, 20=>23.10, 21=>21.90, 22=>19.80, 23=>18.90, 24=>18.10, 25=>17.10
        ),
        'SRIWIJAYA' => array(
            3=>15.10, 4=>21.80, 5=>24.20, 6=>27.90, 7=>30.90, 8=>31.50, 9=>31.60, 10=>33.40,
            11=>35.90, 12=>35.90, 13=>35.20, 14=>35.00, 15=>32.90, 16=>31.80, 17=>30.90, 18=>29.50,
            19=>28.90, 20=>28.70, 21=>28.50, 22=>28.20, 23=>27.90, 24=>27.30, 25=>27.00
        )
    );

    $normaData = array();
    $totalNorma = array();
    $normaCompareData = array();
    $totalNormaCompare = array();

    // Isi Data Norma & Hitung Comparasi
    foreach ($aggDataHa as $thn => $d) {
        // Hitung Usia Tanam
        $currentYear = intval($periode); // 2026
        $plantingYear = intval($thn);    // 2010
        $umur = $currentYear - $plantingYear; 
        // echo $currentYear.' - '. $plantingYear.' = '. $umur.'<br>';
        // Ambil Nilai Norma (Ton/Ha per Tahun)
        // Jika belum ada data di reference, asumsi 0
        $normaYearVal = isset($refNorma[$sumberNorma][$umur]) ? $refNorma[$sumberNorma][$umur] : 0;
        
        // Norma diberikan dalam Ton/Ha/Tahun.
        // Distribusi Bulanan mengikuti pola $sebaranData (Persentase)
        // Rumus: (NormaTahun * %SebaranBulan) / 100

        for ($i = 1; $i <= 12; $i++) {
            // Ambil Sebaran % (Array 0-indexed, $i 1-12)
            $percSebaran = isset($sebaranData[$i-1]) ? $sebaranData[$i-1] : 0;
            
            // Hitung Norma Bulan Ini
            $normaMonthVal = ($normaYearVal * $percSebaran) / 100;
            // echo $normaMonthVal. ' = '.$normaYearVal.' * '.$percSebaran.' / 100<br>';
            
            $normaData[$thn]['actual'][$i] = $normaMonthVal; 
            
            if (!isset($totalNorma[$i])) $totalNorma[$i] = 0;
        }
        
        // Loop lagi untuk Compare
        for ($i = 1; $i <= 12; $i++) {
            $act = isset($d['actual'][$i]) ? $d['actual'][$i] : 0; // Actual Ton/Ha
            
            // % Pencapaian vs Norma
            $normaMonthVal = isset($normaData[$thn]['actual'][$i]) ? $normaData[$thn]['actual'][$i] : 0;
            $val = ($normaMonthVal > 0) ? ($act / $normaMonthVal) : 0;
            // echo 'BULAN '.$i.' = '.$val. ' = '.$act.' / '.$normaMonthVal.'<br>';
            $normaCompareData[$thn]['actual'][$i] = $val;
        }
    }
    
    // Footer Comparisons
    for ($i = 1; $i <= 12; $i++) {
        $totAct = isset($totalActualHa[$i]) ? $totalActualHa[$i] : 0;
        $totNorm = isset($totalNorma[$i]) ? $totalNorma[$i] : 0;
        $totalNormaCompare[$i] = ($totNorm > 0) ? ($totAct / $totNorm) * 100 : 0;
    }

    // --- OUTPUT TABEL 10 & 11 ---
    $judulNorma = "Norma PPKS / " . $sumberNorma . " (Ton/Ha)";
    $judulCompare = "Perbandingan Aktual dengan Norma PPKS / " . $sumberNorma . " (Ton/Ha)";
    
    $stream .= generateTable($judulNorma, 'actual', $normaData, null, $months, false, null, 2);
    $stream .= generateTable($judulCompare, 'actual', $normaCompareData, null, $months, false, null, 2);

    // --- GRAFIK (DUMMY SIDE-BY-SIDE) ---
    // Hanya Tampilkan di Preview (Web), Jangan di Excel
    if ($proses != 'excel') {
        $stream .= "<br><br>";
        $stream .= "<div style='width:100%; display:flex; justify-content:center; gap:20px; flex-wrap:wrap;'>";
        
        // Chart 1: Target vs Realisasi (Bar)
        $stream .= "<div style='flex: 0 0 auto; overflow-x:auto;'>";
        $stream .= "<iframe src='mr_slave_trenProduksiTbs.php?proses=grafik&typ=bar&periode=".$periode."&unit=".$uid."' width='950' height='400' frameborder='0' scrolling='no'></iframe>";
        $stream .= "</div>";
        
        // Chart 2: Tren Produksi Kebun (Line)
        $stream .= "<div style='flex: 0 0 auto; overflow-x:auto;'>";
        $stream .= "<iframe src='mr_slave_trenProduksiTbs.php?proses=grafik&typ=line&periode=".$periode."&unit=".$uid."' width='950' height='400' frameborder='0' scrolling='no'></iframe>";
        $stream .= "</div>";

        $stream .= "</div>";
        
        // Chart 3: Budget vs Realisasi (Combo + Table)
        $stream .= "<br><br>";
        $stream .= "<div style='width:100%; text-align:center; overflow-x:auto;'>";
        $stream .= "<iframe src='mr_slave_trenProduksiTbs.php?proses=grafik&typ=combo&periode=".$periode."&unit=".$uid."' width='900' height='550' frameborder='0' scrolling='auto'></iframe>";
        $stream .= "</div>";

        // Chart 4: Budget vs Aktual Monthly (Blue/Grey)
        $stream .= "<br>"; // Reduced spacing
        $stream .= "<div style='width:100%; text-align:center; overflow-x:auto;'>";
        $stream .= "<iframe src='mr_slave_trenProduksiTbs.php?proses=grafik&typ=monthly_ton&periode=".$periode."&unit=".$uid."' width='900' height='400' frameborder='0' scrolling='no'></iframe>";
        $stream .= "</div>";

        // Chart 5: Tren Produksi Kebun Aktual Per Tahun Tanam (Grouped Bar)
        $stream .= "<br><br>";
        $stream .= "<div style='width:100%; text-align:center; overflow-x:auto;'>";
        $stream .= "<iframe src='mr_slave_trenProduksiTbs.php?proses=grafik&typ=monthly_tanam&periode=".$periode."&unit=".$uid."' width='900' height='400' frameborder='0' scrolling='no'></iframe>";
        $stream .= "</div>";
    }

// } End if(isset($kodeblok)) - REMOVED
} // END FOREACH UNIT

// BAGIAN 4: OUTPUT HANDLER
switch ($proses) {
    case 'preview':
        echo $stream;
        break;
        
    case 'excel':
        $nop_ = "Trend_Produksi_" . $unit . "_" . $periode;
        if (strlen($stream) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $stream)) {
                echo "<script language=javascript1.2>
                        parent.window.alert('Gagal konversi ke Excel');
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
}
?>
