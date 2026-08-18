<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses'])){
	$proses=$_POST['proses'];
}else{
	$proses=$_GET['proses'];
}

$periode = checkPostGet('periodeId4','');
if($periode=='') $periode = date('Y-m'); 
$tahun = substr($periode,0,4);
$limitBulan = (int)substr($periode, 5, 2);

$unit = checkPostGet('unitId4','');

$unitIds = explode(',', $unit);
$unitConds = [];
foreach($unitIds as $uid) {
    if($uid != '') {
        $unitConds[] = "b.kodeorg LIKE '".$uid."%'";
    }
}
if(count($unitConds) > 0) {
    $whereUnit = "(".implode(" OR ", $unitConds).")";
} else {
    $whereUnit = "b.kodeorg LIKE '%'";
}
$intiplasma = checkPostGet('intiplasma4','');

if($intiplasma!=''){
    $inplas=" AND b.intiplasma='".$intiplasma."'";
}

$border = ($proses=='excel') ? 1 : 0;

$bgHeader = ($proses=='excel') ? 'bgcolor=#DEDEDE' : '';

if($proses=='excel'){
    $bgHeader = 'bgcolor=#DEDEDE';
    $tabelHtml = "<table cellspacing=1 cellpadding=1 border=0>";
    $tabelHtml .= "<tr><td colspan=60>Detail Produksi TBS (Dummy Data)</td></tr>";
    $tabelHtml .= "</table>";
}

$tabelHtml .= "<table cellspacing=1 cellpadding=5 border='".$border."' class=sortable>";
$tabelHtml .= "<thead>";

$tabelHtml .= "<tr class=rowheader>";

$tabelHtml .= "<th rowspan=3 align=center ".$bgHeader.">NO</th>";
$tabelHtml .= "<th colspan=2 rowspan=3 align=center ".$bgHeader.">BLOK</th>";
$tabelHtml .= "<th rowspan=3 align=center ".$bgHeader.">Ha</th>";
$tabelHtml .= "<th rowspan=3 align=center ".$bgHeader.">DIV</th>";

$allMonths = array(
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

$months = array_slice($allMonths, 0, $limitBulan);

$yrSuffix = substr($periode, 2, 2);

foreach($months as $mon) {
    $monName = $mon . "-" . $yrSuffix;
    $tabelHtml .= "<th colspan=4 align=center ".$bgHeader.">".$monName."</th>";
}
// Total Column
$tabelHtml .= "<th colspan=4 align=center ".$bgHeader.">TOTAL</th>";
$tabelHtml .= "</tr>";

// --- HEADER ROW 2 ---
$tabelHtml .= "<tr class=rowheader>";
for($i=0; $i<=$limitBulan; $i++) { // months + 1 total
    $tabelHtml .= "<th colspan=2 align=center ".$bgHeader.">Bruto</th>";
    $tabelHtml .= "<th colspan=2 align=center ".$bgHeader.">Netto</th>";
}
$tabelHtml .= "</tr>";

// --- HEADER ROW 3 ---
$tabelHtml .= "<tr class=rowheader>";
for($i=0; $i<=$limitBulan; $i++) { // months + 1 total
    $tabelHtml .= "<th align=center ".$bgHeader.">Kg</th><th align=center ".$bgHeader.">Kg/Ha</th>";
    $tabelHtml .= "<th align=center ".$bgHeader.">Kg</th><th align=center ".$bgHeader.">Kg/Ha</th>";
}
$tabelHtml .= "</tr>";
$tabelHtml .= "</thead><tbody>";

// --- DATA ROWS ---

// --- DATA QUERY & PROCESSING ---

$namaOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

// Array untuk menyimpan data group by Unit -> Tahun Tanam -> Blok
$data = array();
$blockDetails = array(); // Store Luas, Divisi, Kode Blok

$sQuery = "SELECT 
                LEFT(a.tanggalpanen,7) AS periode,
                b.kodeorg,
                b.tahuntanam,
                b.luasareaproduktif,
                SUM(a.kgwb) AS total_brutto,
                SUM(a.kgwbnetto) AS total_netto
            FROM ".$dbname.".kebun_spbdt_detail a 
            JOIN ".$dbname.".setup_blok b ON a.blok = b.kodeorg 
            WHERE LEFT(a.tanggalpanen,7) BETWEEN '".$tahun."-01' AND '".$periode."'
            AND " . $whereUnit . "
            $inplas
            GROUP BY 
                LEFT(a.tanggalpanen,7),
                b.kodeorg,
                b.tahuntanam,
                b.luasareaproduktif
            ORDER BY b.tahuntanam, b.kodeorg, periode;";

// exit('warning: '.$sQuery);

try {
    $qData = $owlPDO->query($sQuery);
    while($r = $qData->fetch(PDO::FETCH_ASSOC)) {
        $tt = $r['tahuntanam'];
        $kodeOrg = $r['kodeorg'];
        $bln = substr($r['periode'], 5, 2); // 01, 02...
        $monthIdx = (int)$bln - 1; // 0-11
        
        if(!isset($blockDetails[$kodeOrg])) {
            // Parse KodeOrg: MHAE01C06I
            // 1-4: Unit (MHAE)
            // 5-6: Divisi
            // 7-9: Blok 1
            // 10: Blok 2
            $divisi = substr($kodeOrg, 4, 2);
            $blok1 = substr($kodeOrg, 6, 3);
            $blok2 = substr($kodeOrg, 9, 1);
            
            $blockDetails[$kodeOrg] = array(
                'luas' => $r['luasareaproduktif'],
                'div' => $divisi,
                'blok1' => $blok1,
                'blok2' => $blok2
            );
        }
        
        // Inisialisasi data bulanan
        $unitId = substr($kodeOrg, 0, 4);
        if(!isset($data[$unitId][$tt][$kodeOrg])) {
            $data[$unitId][$tt][$kodeOrg]['bruto'] = array_fill(0, $limitBulan, 0);
            $data[$unitId][$tt][$kodeOrg]['netto'] = array_fill(0, $limitBulan, 0);
        }
        
        $data[$unitId][$tt][$kodeOrg]['bruto'][$monthIdx] = $r['total_brutto'];
        $data[$unitId][$tt][$kodeOrg]['netto'][$monthIdx] = $r['total_netto'];
    }
} catch (PDOException $e) {
    echo "Query Error: " . $e->getMessage();
}

// --- RENDER TABLE ---

if(empty($data)) {
    $tabelHtml .= "<tr class=rowcontent><td colspan=60 align=center>Data Empty for ".$unit." ".$tahun."</td></tr>";
} else {
    // Sort unit
    ksort($data);

    // Global Totals
    $globalTotalHa = 0;
    $globalTotalKgBruto = array_fill(0, $limitBulan, 0);
    $globalTotalKgNetto = array_fill(0, $limitBulan, 0);
    $globalTotalGrandBruto = 0;
    $globalTotalGrandNetto = 0;
    
    foreach($data as $unitId => $thnTanamData) {
        $namaUnitStr = isset($namaOrg[$unitId]) ? $namaOrg[$unitId] : '';
        $tabelHtml .= "<tr class=rowcontent><td colspan=57 align=left style='font-size:14px; font-weight:bold; background-color:#ffe600;'>&nbsp;&nbsp;UNIT: ".$namaUnitStr." (".$unitId.")</td></tr>";

        // Unit Totals
        $unitTotalHa = 0;
        $unitTotalKgBruto = array_fill(0, $limitBulan, 0);
        $unitTotalKgNetto = array_fill(0, $limitBulan, 0);
        $unitTotalGrandBruto = 0;
        $unitTotalGrandNetto = 0;

        // Sort tahun tanam
        ksort($thnTanamData);
        foreach($thnTanamData as $tt => $blocks) {
            // TAHUN TANAM Header Row
            $rowStyle = "style='font-weight:bold; background-color:#EFEFEF;'";
            
            $tabelHtml .= "<tr class=rowcontent>";
            $tabelHtml .= "<td colspan=57 align=left ".$rowStyle.">&nbsp;&nbsp;TAHUN TANAM ".$tt."</td>";
            $tabelHtml .= "</tr>";

        // Variable untuk tahunan Subtotal
        $subTotalHa = 0;
        $subTotalKgBruto = array_fill(0, $limitBulan, 0);
        $subTotalKgNetto = array_fill(0, $limitBulan, 0);
        $subTotalKgHaBruto = array_fill(0, $limitBulan, 0); 
        $subTotalKgHaNetto = array_fill(0, $limitBulan, 0); 
        
        $subTotalGrandBruto = 0;
        $subTotalGrandNetto = 0;

        $no = 0;
        foreach($blocks as $kodeOrg => $vals) {
            $no++;
            $det = $blockDetails[$kodeOrg];
            $luas = $det['luas'];
            
            $subTotalHa += $luas;
            $unitTotalHa += $luas;
            $globalTotalHa += $luas;

            $tabelHtml .= "<tr class=rowcontent>";
            $tabelHtml .= "<td align=center>".$no."</td>";
            $tabelHtml .= "<td align=center>".$det['blok1']."</td>"; // C06
            $tabelHtml .= "<td align=center>".$det['blok2']."</td>"; // I
            $tabelHtml .= "<td align=right>".number_format($luas,2,',','.')."</td>";
            $tabelHtml .= "<td align=center>".$det['div']."</td>";   // 01

            // Data Bulanan
            $rowTotalBruto = 0;
            $rowTotalNetto = 0;

            for($m=0; $m<$limitBulan; $m++) {
                $kgBruto = $vals['bruto'][$m];
                $kgNetto = $vals['netto'][$m];
                
                $kgHaBruto = ($luas>0) ? $kgBruto / $luas : 0;
                $kgHaNetto = ($luas>0) ? $kgNetto / $luas : 0;

                $rowTotalBruto += $kgBruto;
                $rowTotalNetto += $kgNetto;

                // Akumulasi Bulanan
                $subTotalKgBruto[$m] += $kgBruto;
                $subTotalKgNetto[$m] += $kgNetto;
                $unitTotalKgBruto[$m] += $kgBruto;
                $unitTotalKgNetto[$m] += $kgNetto;
                $globalTotalKgBruto[$m] += $kgBruto;
                $globalTotalKgNetto[$m] += $kgNetto;

                $tabelHtml .= "<td align=right>".number_format($kgBruto,0,',','.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($kgHaBruto,2,',','.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($kgNetto,0,',','.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($kgHaNetto,2,',','.')."</td>";
            }

            // Total Baris
            $rowTotalKgHaBruto = ($luas>0) ? $rowTotalBruto / $luas : 0;
            $rowTotalKgHaNetto = ($luas>0) ? $rowTotalNetto / $luas : 0;
            
            // Akumulasi Tahunan
            $subTotalGrandBruto += $rowTotalBruto;
            $subTotalGrandNetto += $rowTotalNetto;
            $unitTotalGrandBruto += $rowTotalBruto;
            $unitTotalGrandNetto += $rowTotalNetto;
            $globalTotalGrandBruto += $rowTotalBruto;
            $globalTotalGrandNetto += $rowTotalNetto;

            $tabelHtml .= "<td align=right>".number_format($rowTotalBruto,0,',','.')."</td>";
            $tabelHtml .= "<td align=right>".number_format($rowTotalKgHaBruto,2,',','.')."</td>";
            $tabelHtml .= "<td align=right>".number_format($rowTotalNetto,0,',','.')."</td>";
            $tabelHtml .= "<td align=right>".number_format($rowTotalKgHaNetto,2,',','.')."</td>";

            $tabelHtml .= "</tr>";
        }

        // --- TOTAL TAHUN TANAM ROW ---
        $tabelHtml .= "<tr class=rowcontent style='font-weight:bold; background-color:#EFEFEF;'>";
        
        $tabelHtml .= "<td colspan=3 align=center>TOTAL</td>";
        
        $tabelHtml .= "<td align=right>".number_format($subTotalHa,2,',','.')."</td>";
        $tabelHtml .= "<td></td>"; // Div column empty

        for($m=0; $m<$limitBulan; $m++) {
            $kgSubBruto = $subTotalKgBruto[$m];
            $kgSubNetto = $subTotalKgNetto[$m];
            
            // Hitung ulang Kg/Ha untuk Total Row menggunakan Total Kg / Total Ha
            $kgHaSubBruto = ($subTotalHa>0) ? $kgSubBruto / $subTotalHa : 0;
            $kgHaSubNetto = ($subTotalHa>0) ? $kgSubNetto / $subTotalHa : 0;

            $tabelHtml .= "<td align=right>".number_format($kgSubBruto,0,',','.')."</td>";
            $tabelHtml .= "<td align=right>".number_format($kgHaSubBruto,2,',','.')."</td>";
            $tabelHtml .= "<td align=right>".number_format($kgSubNetto,0,',','.')."</td>";
            $tabelHtml .= "<td align=right>".number_format($kgHaSubNetto,2,',','.')."</td>";
        }
        
        // Grand Total Columns untuk tahun
        $kgHaGrandBruto = ($subTotalHa>0) ? $subTotalGrandBruto / $subTotalHa : 0;
        $kgHaGrandNetto = ($subTotalHa>0) ? $subTotalGrandNetto / $subTotalHa : 0;
        
        $tabelHtml .= "<td align=right>".number_format($subTotalGrandBruto,0,',','.')."</td>";
        $tabelHtml .= "<td align=right>".number_format($kgHaGrandBruto,2,',','.')."</td>";
        $tabelHtml .= "<td align=right>".number_format($subTotalGrandNetto,0,',','.')."</td>";
        $tabelHtml .= "<td align=right>".number_format($kgHaGrandNetto,2,',','.')."</td>";

        $tabelHtml .= "</tr>";
        }

        // --- TOTAL UNIT ROW ---
        $tabelHtml .= "<tr class=rowcontent style='font-weight:bold; background-color:#FFEBAA;'>";
        $tabelHtml .= "<td colspan=3 align=center>TOTAL UNIT ".$unitId."</td>";
        $tabelHtml .= "<td align=right>".number_format($unitTotalHa,2,',','.')."</td>";
        $tabelHtml .= "<td></td>";

        for($m=0; $m<$limitBulan; $m++) {
            $kgSubBruto = $unitTotalKgBruto[$m];
            $kgSubNetto = $unitTotalKgNetto[$m];
            
            $kgHaSubBruto = ($unitTotalHa>0) ? $kgSubBruto / $unitTotalHa : 0;
            $kgHaSubNetto = ($unitTotalHa>0) ? $kgSubNetto / $unitTotalHa : 0;

            $tabelHtml .= "<td align=right>".number_format($kgSubBruto,0,',','.')."</td>";
            $tabelHtml .= "<td align=right>".number_format($kgHaSubBruto,2,',','.')."</td>";
            $tabelHtml .= "<td align=right>".number_format($kgSubNetto,0,',','.')."</td>";
            $tabelHtml .= "<td align=right>".number_format($kgHaSubNetto,2,',','.')."</td>";
        }
        
        $kgHaGrandBruto = ($unitTotalHa>0) ? $unitTotalGrandBruto / $unitTotalHa : 0;
        $kgHaGrandNetto = ($unitTotalHa>0) ? $unitTotalGrandNetto / $unitTotalHa : 0;
        
        $tabelHtml .= "<td align=right>".number_format($unitTotalGrandBruto,0,',','.')."</td>";
        $tabelHtml .= "<td align=right>".number_format($kgHaGrandBruto,2,',','.')."</td>";
        $tabelHtml .= "<td align=right>".number_format($unitTotalGrandNetto,0,',','.')."</td>";
        $tabelHtml .= "<td align=right>".number_format($kgHaGrandNetto,2,',','.')."</td>";
        $tabelHtml .= "</tr>";

    }

    // --- GRAND TOTAL ROW ---
    $tabelHtml .= "<tr class=rowcontent style='font-weight:bold; background-color:#FFD700;'>";
    $tabelHtml .= "<td colspan=3 align=center>GRAND TOTAL</td>";
    $tabelHtml .= "<td align=right>".number_format($globalTotalHa,2,',','.')."</td>";
    $tabelHtml .= "<td></td>";

    for($m=0; $m<$limitBulan; $m++) {
        $kgSubBruto = $globalTotalKgBruto[$m];
        $kgSubNetto = $globalTotalKgNetto[$m];
        
        $kgHaSubBruto = ($globalTotalHa>0) ? $kgSubBruto / $globalTotalHa : 0;
        $kgHaSubNetto = ($globalTotalHa>0) ? $kgSubNetto / $globalTotalHa : 0;

        $tabelHtml .= "<td align=right>".number_format($kgSubBruto,0,',','.')."</td>";
        $tabelHtml .= "<td align=right>".number_format($kgHaSubBruto,2,',','.')."</td>";
        $tabelHtml .= "<td align=right>".number_format($kgSubNetto,0,',','.')."</td>";
        $tabelHtml .= "<td align=right>".number_format($kgHaSubNetto,2,',','.')."</td>";
    }
    
    $kgHaGrandBruto = ($globalTotalHa>0) ? $globalTotalGrandBruto / $globalTotalHa : 0;
    $kgHaGrandNetto = ($globalTotalHa>0) ? $globalTotalGrandNetto / $globalTotalHa : 0;
    
    $tabelHtml .= "<td align=right>".number_format($globalTotalGrandBruto,0,',','.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($kgHaGrandBruto,2,',','.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($globalTotalGrandNetto,0,',','.')."</td>";
    $tabelHtml .= "<td align=right>".number_format($kgHaGrandNetto,2,',','.')."</td>";
    $tabelHtml .= "</tr>";

}

$tabelHtml .= "</tbody></table>";

switch($proses)
{
	case'preview':
	    echo $tabelHtml;
	break;
	case'excel':
        $tabelHtml.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $namaFileExcel="Detail_Produksi_TBS_".$periode;
        if(strlen($tabelHtml)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }
            $handle=fopen("tempExcel/".$namaFileExcel.".xls",'w');
            if(!fwrite($handle,$tabelHtml))
            {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            }
            else
            {
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$namaFileExcel.".xls';
                </script>";
            }
            fclose($handle);
        }
	break;
	default:
	break;
}
?>
