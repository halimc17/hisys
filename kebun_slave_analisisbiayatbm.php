<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ============================================================================
// INPUT VALIDATION & INITIALIZATION
// ============================================================================

$proses = checkPostGet('proses', '');

if($proses == 'getDivisi') {
    $optdivisi = "<option value=''>".$_SESSION['lang']['all']."</option>";
    $unit = checkPostGet('kodeorg', '');
    $whereUnit = "";
    if($unit != '') {
        $arrUnit = explode(',', $unit);
        $unitList = "'".implode("','", $arrUnit)."'";
        $whereUnit = " and induk in (".$unitList.")";
    }
    $str = "select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe = 'AFDELING' ".$whereUnit." order by kodeorganisasi";
    $res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    while($bar = $res->fetch()){
        $optdivisi .= "<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
    }
    echo $optdivisi;
    exit();
}

$kode_org_raw = checkPostGet('kodeorg', '');
$divisi_raw = checkPostGet('divisi', '');
$periode = checkPostGet('periode', ''); // YYYY-MM format
$intiplasma = checkPostGet('intiplasma', '');

$inplas="";
$inplas2="";
if ($intiplasma!='') {
    $inplas=" and a.intiplasma='".$intiplasma."'";
    if ($intiplasma == 'I') {
        $inplas2 = " and b.inti = '1'";
    } elseif ($intiplasma == 'P') {
        $inplas2 = " and b.inti = '0'";
    }
}

// Validasi input
if ($periode == '' || $kode_org_raw == '') {
    exit("Error : Periode atau Unit tidak boleh kosong");
}

if ($divisi_raw != '') {
    $orgArr = explode(',', $divisi_raw);
    $unitList = "'".implode("','", $orgArr)."'";
    $whereOrg = "substr(a.kodeorg,1,6) IN (".$unitList.")";
    $whereOrgKaloBlok = "substr(a.kodeblok,1,6) IN (".$unitList.")";
    $len = 6;
} else {
    $orgArr = explode(',', $kode_org_raw);
    $unitList = "'".implode("','", $orgArr)."'";
    $whereOrg = "substr(a.kodeorg,1,4) IN (".$unitList.")";
    $whereOrgKaloBlok = "substr(a.kodeblok,1,4) IN (".$unitList.")";
    $len = 4;
}

$nojurnalParts = [];
foreach($orgArr as $u) {
    $nojurnalParts[] = "a.nojurnal LIKE '%".$u."%'";
}
$whereNoJurnal = "(".implode(" OR ", $nojurnalParts).")";
$kode_org = $orgArr[0]; // Fallback for single value usage if any

$prd = new DateTime($periode); 

$tahun_ini = $prd->format('Y');
$tahun_lalu = $prd->format('Y') - 1;

$date_bulan_lalu = clone $prd;
$date_bulan_lalu->modify('-1 month');

$bulan_depan = clone $prd;
$bulan_depan->modify('+1 month');

$date_tahun_lalu_bulan_ini = clone $prd;
$date_tahun_lalu_bulan_ini->modify('-1 year');

$date_tahun_lalu_bulan_selanjutnya = clone $bulan_depan;
$date_tahun_lalu_bulan_selanjutnya->modify('-1 year');

$luas = [];

// 1. Query Data Luas TM (Tanaman Menghasilkan)
$sql_luas_tm = "SELECT 
    SUM(CASE WHEN a.tahun = '".$prd->format('Ym')."' THEN a.luasareaproduktif ELSE 0 END) AS luas_tm_ini,
    SUM(CASE WHEN a.tahun = '".$date_bulan_lalu->format('Ym')."' THEN a.luasareaproduktif ELSE 0 END) AS luas_tm_lalu,
    SUM(CASE WHEN a.tahun = '".$date_tahun_lalu_bulan_ini->format('Ym')."' THEN a.luasareaproduktif ELSE 0 END) AS luas_tm_sdbi_tl
FROM ".$dbname.".setup_blok_tahunan a
WHERE ".$whereOrg."
AND a.statusblok = 'TBM' ".$inplas."
AND a.tahun IN ('".$prd->format('Ym')."', '".$date_bulan_lalu->format('Ym')."', '".$date_tahun_lalu_bulan_ini->format('Ym')."')";

// exit('warning: ' . $sql_luas_tm);

$data_luas = executeQuery($sql_luas_tm);
$luas['aktual']['bi'] = ($data_luas[0]['luas_tm_ini']) ? $data_luas[0]['luas_tm_ini'] : 0;
$luas['aktual']['sdbl'] = ($data_luas[0]['luas_tm_lalu']) ? $data_luas[0]['luas_tm_lalu'] : 0;
$luas['aktual']['sdbi_tl'] = ($data_luas[0]['luas_tm_sdbi_tl']) ? $data_luas[0]['luas_tm_sdbi_tl'] : 0;

// 2. Query Budget
$sql_budget_luas_tm = "SELECT 
    SUM(CASE WHEN a.tahunbudget = '".$tahun_ini."' THEN a.hathnini ELSE 0 END) AS bgt_ini,
    SUM(CASE WHEN a.tahunbudget = '".$tahun_lalu."' THEN a.hathnini ELSE 0 END) AS bgt_thn_lalu
FROM ".$dbname.".bgt_blok a
WHERE ".$whereOrgKaloBlok."
AND a.statusblok='TBM' ".$inplas."
AND a.tahunbudget IN ('".$tahun_ini."', '".$tahun_lalu."')";

// exit('warning: '.$sql_budget_luas_tm);

$data_budget_luas = executeQuery($sql_budget_luas_tm);
$luas['budget']['bi'] = ($data_budget_luas[0]['bgt_ini']) ? $data_budget_luas[0]['bgt_ini'] : 0;
$luas['budget']['tl'] = ($data_budget_luas[0]['bgt_thn_lalu']) ? $data_budget_luas[0]['bgt_thn_lalu'] : 0;

$biaya = [];

$jenisList = ['perawatan','pupuk','panen','angkut'];
$kategoriList = ['material','upah','transport'];
$tipeList = ['bi','sdbl','sdbi','sdbi_tl'];

foreach($jenisList as $j){
    foreach($kategoriList as $k){
        foreach($tipeList as $t){
            $biaya[$j][$k][$t] = 0;
        }
    }
}

function getJenisBiaya($noakun, $kategori){

    // Pupuk (12608xxxx)
    if(substr($noakun,0,5) == '12608'){
        return 'pupuk';
    }

    // Panen & Angkut (COA sama)
    if($noakun == '1261004'){
        // if($kategori == 'transport'){
        //     return 'angkut';
        // } else {
            return 'panen';
        // }
    }

    // Perawatan (selain pupuk)
    if($noakun >= '1260101' && $noakun <= '1261801'){
        return 'perawatan';
    }

    return null;
}

// function getTipeBiaya($periode, $periode_target){
//     if($periode == $periode_target){
//         return 'bi';
//     }
//     if($periode == $date_bulan_lalu->format('Y-m')){
//         return 'sdbl';
//     }
//     if($periode >= $date_bulan_ini_tahun_lalu->format('Y-m') && $periode <= $periode_target){
//         return 'sdbi';
//     }
//     if($periode >= $date_bulan_ini_tahun_lalu->format('Y-m') && $periode <= $date_tahun_lalu_bulan_selanjutnya->format('Y-m')){
//         return 'sdbi_tl';
//     }
//     return null;
// }

function executeQuery($sql) {
    global $owlPDO;
    try {
        return $owlPDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

function xPerY($x, $y){
    return ($y != 0) ? ($x / $y) : 0;
}

function getPersen($x, $y){
    return ($y != 0) ? ($x / $y) * 100 : 0;
}

$sql_biaya = "SELECT 
    a.periode,
    a.jumlah,
    a.noakun,
    a.kodejurnal,
    CASE
        WHEN a.kodejurnal LIKE 'INV%' THEN 'material'
        WHEN a.kodejurnal LIKE 'VHC%' THEN 'transport'
        WHEN a.kodejurnal LIKE 'SPK%' THEN 'spk'
        ELSE 'upah'
    END AS kategori
FROM ".$dbname.".keu_jurnaldt_vw a
LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg = b.kodeorganisasi
WHERE 
    a.periode >= '".$tahun_lalu."-01'
    AND a.periode <= '".$prd->format('Y-m')."'
    AND ".$whereNoJurnal." ".$inplas2."
    AND a.noakun BETWEEN '1260101' AND '1261801'";

// exit($sql_biaya);

$res = executeQuery($sql_biaya);

foreach($res as $row){
    // $jenis = getJenisBiaya($row['noakun']);
    // $kategori = $row['kategori'];
    // $tipe = getTipeBiaya($row['periode'], $periode->format('Y-m'));
    // $biaya[$jenis][$kategori][$tipe] += $row['jumlah'];

    $noakun = $row['noakun'];
    $kategori = $row['kategori'];

    // skip SPK
    if($kategori == 'spk') continue;

    $jenis = getJenisBiaya($noakun, $kategori);
    if(!$jenis) continue;

    $nilai = floatval($row['jumlah']);
    $periodeRow = $row['periode'];

    // BI (bulan ini)
    if($periodeRow == $prd->format('Y-m')){
        $biaya[$jenis][$kategori]['bi'] += $nilai;
        $biaya[$jenis]['total']['bi'] += $nilai;
    }

    // SDBL (s/d bulan lalu)
    if($periodeRow >= $prd->format('Y-01') && $periodeRow <= $date_bulan_lalu->format('Y-m')){
        $biaya[$jenis][$kategori]['sdbl'] += $nilai;
        $biaya[$jenis]['total']['sdbl'] += $nilai;
    }

    // SDBI (s/d bulan ini)
    if($periodeRow >= $prd->format('Y-01') && $periodeRow <= $prd->format('Y-m')){
        $biaya[$jenis][$kategori]['sdbi'] += $nilai;
        $biaya[$jenis]['total']['sdbi'] += $nilai;
    }

    // Tahun lalu (s/d bulan yang sama)
    if($periodeRow >= $tahun_lalu.'-01' && $periodeRow < $date_tahun_lalu_bulan_selanjutnya->format('Y-m')){
        $biaya[$jenis][$kategori]['sdbi_tl'] += $nilai;
        $biaya[$jenis]['total']['sdbi_tl'] += $nilai;
    }
}

$sql_budget = "SELECT 
        a.tahunbudget, 
        a.kodeorg, 
        CASE 
            WHEN a.kodebudget = 'SUPERVISI' OR substr(a.kodebudget,1,3)='SDM' THEN 'upah'
            WHEN substr(a.kodebudget,1,3) = 'VHC' THEN 'transport'
            WHEN a.kodebudget = 'TOOL' OR substr(a.kodebudget,1,2)='M-' THEN 'material'
        END AS kategori, 
        a.noakun, 
        a.rupiah, 
        a.rp01, a.rp02, a.rp03, a.rp04, a.rp05, a.rp06, a.rp07, a.rp08, a.rp09, a.rp10, a.rp11, a.rp12
    FROM ".$dbname.".bgt_budget_detail a
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg = b.kodeorganisasi
    WHERE ".$whereOrg."
    AND a.tahunbudget = '".$tahun_ini."'
    ".$inplas2."
    AND a.noakun BETWEEN '1260101' AND '1261801'";

$res_budget = executeQuery($sql_budget);

foreach($res_budget as $row){

    $kategori = $row['kategori'];
    if(!$kategori) continue;

    $noakun = $row['noakun'];
    $jenis = getJenisBiaya($noakun, $kategori);
    if(!$jenis) continue;

    $budget[$jenis][$kategori]['rat'] += floatval($row['rupiah']);
    $budget[$jenis]['total']['rat'] += floatval($row['rupiah']);

    // =========================
    // 2. LOOP BULAN (rp01-rp12)
    // =========================
    for($i=1; $i<=12; $i++){

        $bln = str_pad($i, 2, '0', STR_PAD_LEFT);
        $field = 'rp'.$bln;

        $nilai = floatval($row[$field]);

        if($nilai == 0) continue;

        $periodeLoop = $tahun.'-'.$bln;

        // BI (bulan ini)
        if($periodeLoop == $prd->format('Y-m')){
            $budget[$jenis][$kategori]['bi'] += $nilai;
            $budget[$jenis]['total']['bi'] += $nilai;
        }

        // SDBL
        if($periodeLoop >= $prd->format('Y-01') && $periodeLoop <= $date_bulan_lalu->format('Y-m')){
            $budget[$jenis][$kategori]['sdbl'] += $nilai;
            $budget[$jenis]['total']['sdbl'] += $nilai;
        }

        // SDBI
        if($periodeLoop >= $prd->format('Y-01') && $periodeLoop <= $prd->format('Y-m')){
            $budget[$jenis][$kategori]['sdbi'] += $nilai;
            $budget[$jenis]['total']['sdbi'] += $nilai;
        }
    }
}


$persen = [];

foreach($jenisList as $jenis){

    foreach($kategoriList as $kategori){

        // =========================
        // PER KATEGORI
        // =========================
        $persen[$jenis][$kategori]['bi'] = ($budget[$jenis][$kategori]['bi'] > 0) ? ($biaya[$jenis][$kategori]['bi'] / $budget[$jenis][$kategori]['bi']) * 100 : 0;

        $persen[$jenis][$kategori]['sdbl'] = ($budget[$jenis][$kategori]['sdbl'] > 0) ? ($biaya[$jenis][$kategori]['sdbl'] / $budget[$jenis][$kategori]['sdbl']) * 100 : 0;

        $persen[$jenis][$kategori]['sdbi'] = ($budget[$jenis][$kategori]['sdbi'] > 0) ? ($biaya[$jenis][$kategori]['sdbi'] / $budget[$jenis][$kategori]['sdbi']) * 100 : 0;

        // setahun (pakai RAT)
        $persen[$jenis][$kategori]['setahun'] = ($budget[$jenis][$kategori]['rat'] > 0) ? ($biaya[$jenis][$kategori]['sdbi'] / $budget[$jenis][$kategori]['rat']) * 100 : 0;

    }

    // =========================
    // TOTAL PER JENIS
    // =========================
    $persen[$jenis]['total']['bi'] = ($budget[$jenis]['total']['bi'] > 0) ? ($biaya[$jenis]['total']['bi'] / $budget[$jenis]['total']['bi']) * 100 : 0;

    $persen[$jenis]['total']['sdbl'] = ($budget[$jenis]['total']['sdbl'] > 0) ? ($biaya[$jenis]['total']['sdbl'] / $budget[$jenis]['total']['sdbl']) * 100 : 0;

    $persen[$jenis]['total']['sdbi'] = ($budget[$jenis]['total']['sdbi'] > 0) ? ($biaya[$jenis]['total']['sdbi'] / $budget[$jenis]['total']['sdbi']) * 100 : 0;

    $persen[$jenis]['total']['setahun'] = ($budget[$jenis]['total']['rat'] > 0) ? ($biaya[$jenis]['total']['sdbi'] / $budget[$jenis]['total']['rat']) * 100 : 0;
}

// ============================================================================
// TABLE INITIALIZATION
// ============================================================================

$stream = '';
$border = '0';
$bgHeader = '';

if ($proses == 'excel') {
    $stream = "<table class=\"sortable\" cellspacing=\"1\" border=\"1\">";
    $border = 1;
    $bgHeader = "bgcolor=\"#DEDEDE\" align=\"center\"";
} else {
    $stream = "<table class=\"sortable\" cellspacing=\"1\">";
}

$html = "";

// Table header untuk excel
if ($proses == 'excel') {
    $html .= "<thead>";
    $html .= "<tr><td colspan=\"17\">".strtoupper("Analisis Biaya Kebun ".$kode_org." ".$prd->format('Y-m'))."</td></tr>";
    $html .= "</thead>";
}

// Header columns
$headerTabel = "<thead>
                    <tr class=\"rowheader\">
                        <th rowspan=\"2\" colspan=\"1\" {$bgHeader}>Deskripsi</th>
                        <th rowspan=\"2\" colspan=\"1\" width=\"100px\" {$bgHeader}>Unit</th>
                        <th rowspan=\"1\" colspan=\"4\" {$bgHeader}>Bulan Ini</th>
                        <th rowspan=\"1\" colspan=\"4\" {$bgHeader}>S/D Bulan Lalu</th>
                        <th rowspan=\"1\" colspan=\"4\" {$bgHeader}>S/D Bulan Ini</th>
                        <th rowspan=\"2\" colspan=\"1\" {$bgHeader}>S/D Bulan Ini di Tahun Lalu</th>
                        <th rowspan=\"2\" colspan=\"1\" {$bgHeader}>Anggaran ".$tahun_ini."</th>
                        <th rowspan=\"2\" colspan=\"1\" {$bgHeader}>%</th>
                    </tr>
                    <tr class=\"rowheader\">
                        <th {$bgHeader}>Aktual</th>
                        <th {$bgHeader}>RAT</th>
                        <th {$bgHeader}>RAB</th>
                        <th {$bgHeader}>%</th>
                        <th {$bgHeader}>Aktual</th>
                        <th {$bgHeader}>RAT</th>
                        <th {$bgHeader}>RAB</th>
                        <th {$bgHeader}>%</th>
                        <th {$bgHeader}>Aktual</th>
                        <th {$bgHeader}>RAT</th>
                        <th {$bgHeader}>RAB</th>
                        <th {$bgHeader}>%</th>
                    </tr>   
                </thead>";

$html .= "<table cellspacing=\"3\" cellpadding=\"5\" border=\"".$border."\" class=\"sortable\">";
$html .= $headerTabel;
$html .= "<tbody>";

$html .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
                    <td>Hektar:</td>
                </tr>";

$html .= "<tr class=\"rowcontent\">
                    <td>Tanaman Belum Menghasilkan</td>
                    <td align=\"center\">Hektar</td>
                    <!-- Bulan Ini -->
                    <td align=\"right\">".number_format($luas['aktual']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'] > 0 ? ($luas['aktual']['bi'] / $luas['budget']['bi']) * 100 : 0,2)."%</td>
                    <!-- S/D Bulan Lalu -->
                    <td align=\"right\">".number_format($luas['aktual']['sdbl'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'] > 0 ? ($luas['aktual']['sdbl'] / $luas['budget']['bi']) * 100 : 0,2)."%</td>
                    <!-- S/D Bulan Ini -->
                    <td align=\"right\">".number_format($luas['aktual']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'] > 0 ? ($luas['aktual']['bi'] / $luas['budget']['bi']) * 100 : 0,2)."%</td>
                    <!-- S/D Bulan Ini di Tahun Lalu -->
                    <td align=\"right\">".number_format($luas['aktual']['sdbi_tl'],2)."</td>
                    <!-- Anggaran Tahun Ini -->
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <!-- Persen -->
                    <td align=\"right\">".number_format($luas['budget']['bi'] > 0 ? ($luas['aktual']['sdbi_tl'] / $luas['budget']['bi']) * 100 : 0,2)."%</td>
                </tr>";

$html .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
                    <td>Total Luasan Tertanam Inti</td>
                    <td align=\"center\">Hektar</td>
                    <!-- Bulan Ini -->
                    <td align=\"right\">".number_format($luas['aktual']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['persen']['bi'],2)."%</td>
                    <!-- S/D Bulan Lalu -->
                    <td align=\"right\">".number_format($luas['aktual']['sdbl'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['persen']['sdbl'],2)."%</td>
                    <!-- S/D Bulan Ini -->
                    <td align=\"right\">".number_format($luas['aktual']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <td align=\"right\">".number_format($luas['persen']['sdbi'],2)."%</td>
                    <!-- S/D Bulan Ini di Tahun Lalu -->
                    <td align=\"right\">".number_format($luas['aktual']['sdbi_tl'],2)."</td>
                    <!-- Anggaran Tahun Ini -->
                    <td align=\"right\">".number_format($luas['budget']['bi'],2)."</td>
                    <!-- Persen -->
                    <td align=\"right\">".number_format($luas['persen']['tahun_lalu'],2)."%</td>
                </tr>";

$html .= "<tr style=\"height:10px;\"></tr>";

// ============================================================================
// PRODUKSI BUAH
// ============================================================================

$html .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
                    <td>Produksi Buah:</td>
                </tr>";

$html .= "<tr class=\"rowcontent\">
                    <td>Buah Inti</td>
                    <td align=\"center\">Ton</td>
                    <!-- Bulan Ini -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Lalu -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Ini -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Ini di Tahun Lalu -->
                    <td align=\"right\"></td>
                    <!-- Anggaran Tahun Ini -->
                    <td align=\"right\"></td>
                    <!-- Persen -->
                    <td align=\"right\"></td>
                </tr>";

$html .= "<tr class=\"rowcontent\">
                    <td>Total TBS</td>
                    <td align=\"center\">Ton</td>
                    <!-- Bulan Ini -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Lalu -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Ini -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Ini di Tahun Lalu -->
                    <td align=\"right\"></td>
                    <!-- Anggaran Tahun Ini -->
                    <td align=\"right\"></td>
                    <!-- Persen -->
                    <td align=\"right\"></td>
                </tr>";

$html .= "<tr class=\"rowcontent\">
                    <td>Ton/Ha TBS</td>
                    <td align=\"center\">Ton/Ha</td>
                    <!-- Bulan Ini -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Lalu -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Ini -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Ini di Tahun Lalu -->
                    <td align=\"right\"></td>
                    <!-- Anggaran Tahun Ini -->
                    <td align=\"right\"></td>
                    <!-- Persen -->
                    <td align=\"right\"></td>
                </tr>";

$html .= "<tr style=\"height:10px;\"></tr>";

// ============================================================================
// BIAYA
// ============================================================================

$total = [];

$html .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
                    <td>Biaya Langsung:</td>
                </tr>";

foreach($jenisList as $jenis){
    $html .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
                <td>Biaya ".ucfirst($jenis)."</td>
                <td align=\"center\">Rp</td>
                <!-- Bulan Ini -->
                <td align=\"right\">".number_format($biaya[$jenis]['total']['bi'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis]['total']['rat'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis]['total']['bi'],0)."</td>
                <td align=\"right\">".number_format($persen[$jenis]['total']['bi'],0)."%</td>
                <!-- S/D Bulan Lalu -->
                <td align=\"right\">".number_format($biaya[$jenis]['total']['sdbl'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis]['total']['rat'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis]['total']['sdbl'],0)."</td>
                <td align=\"right\">".number_format($persen[$jenis]['total']['sdbl'],0)."%</td>
                <!-- S/D Bulan Ini -->
                <td align=\"right\">".number_format($biaya[$jenis]['total']['sdbi'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis]['total']['rat'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis]['total']['sdbi'],0)."</td>
                <td align=\"right\">".number_format($persen[$jenis]['total']['sdbi'],0)."%</td>
                <!-- S/D Bulan Ini di Tahun Lalu -->
                <td align=\"right\">".number_format($biaya[$jenis]['total']['sdbi_tl'],0)."</td>
                <!-- Anggaran Tahun Ini -->
                <td align=\"right\">".number_format($budget[$jenis]['total']['rat'],0)."</td>
                <!-- Persen -->
                <td align=\"right\">".number_format($persen[$jenis]['total']['setahun'],0)."%</td>
            </tr>";

            $nama = "";

            if ($jenis == 'perawatan' || $jenis == 'pupuk'){
                $nama = "rawatpupuk";
            } else {
                $nama = "panenangkut";
            }

            $total[$nama]['aktualbi'] += $biaya[$jenis]['total']['bi'];
            $total[$nama]['rabbi'] += $budget[$jenis]['total']['bi'];
            $total[$nama]['aktualsdbl'] += $biaya[$jenis]['total']['sdbl'];
            $total[$nama]['rabsdbl'] += $budget[$jenis]['total']['sdbl'];
            $total[$nama]['aktualsdbi'] += $biaya[$jenis]['total']['sdbi'];
            $total[$nama]['rabsdbi'] += $budget[$jenis]['total']['sdbi'];
            $total[$nama]['aktualsdbi_tl'] += $biaya[$jenis]['total']['sdbi_tl'];
            $total[$nama]['rat'] += $budget[$jenis]['total']['rat'];

            $total['all']['aktualbi'] += $biaya[$jenis]['total']['bi'];
            $total['all']['rabbi'] += $budget[$jenis]['total']['bi'];
            $total['all']['aktualsdbl'] += $biaya[$jenis]['total']['sdbl'];
            $total['all']['rabsdbl'] += $budget[$jenis]['total']['sdbl'];
            $total['all']['aktualsdbi'] += $biaya[$jenis]['total']['sdbi'];
            $total['all']['rabsdbi'] += $budget[$jenis]['total']['sdbi'];
            $total['all']['aktualsdbi_tl'] += $biaya[$jenis]['total']['sdbi_tl'];
            $total['all']['rat'] += $budget[$jenis]['total']['rat'];
    
    foreach($kategoriList as $kategori){
        $html .= "<tr class=\"rowcontent\">
                <td align=right>".ucfirst($kategori)."</td>
                <td align=\"center\">Rp</td>
                <!-- Bulan Ini -->
                <td align=\"right\">".number_format($biaya[$jenis][$kategori]['bi'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis][$kategori]['rat'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis][$kategori]['bi'],0)."</td>
                <td align=\"right\">".number_format($persen[$jenis][$kategori]['bi'],0)."%</td>
                <!-- S/D Bulan Lalu -->
                <td align=\"right\">".number_format($biaya[$jenis][$kategori]['sdbl'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis][$kategori]['rat'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis][$kategori]['sdbl'],0)."</td>
                <td align=\"right\">".number_format($persen[$jenis][$kategori]['sdbl'],0)."%</td>
                <!-- S/D Bulan Ini -->
                <td align=\"right\">".number_format($biaya[$jenis][$kategori]['sdbi'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis][$kategori]['rat'],0)."</td>
                <td align=\"right\">".number_format($budget[$jenis][$kategori]['sdbi'],0)."</td>
                <td align=\"right\">".number_format($persen[$jenis][$kategori]['sdbi'],0)."%</td>
                <!-- S/D Bulan Ini di Tahun Lalu -->
                <td align=\"right\">".number_format($biaya[$jenis][$kategori]['sdbi_tl'],0)."</td>
                <!-- Anggaran Tahun Ini -->
                <td align=\"right\">".number_format($budget[$jenis][$kategori]['rat'],0)."</td>
                <!-- Persen -->
                <td align=\"right\">".number_format($persen[$jenis][$kategori]['setahun'],0)."%</td>
            </tr>";
    }
    if ($jenis == 'perawatan' || $jenis == 'pupuk') {
        $html .= "<tr class=\"rowcontent\">
                    <td>Biaya per Hektar</td>
                    <td align=\"center\">Rp</td>
                    <td align=\"right\">".number_format(xPerY($biaya[$jenis]['total']['bi'], $luas['aktual']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($budget[$jenis]['total']['rat'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($budget[$jenis]['total']['bi'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(getPersen($biaya[$jenis]['total']['bi'], $budget[$jenis]['total']['bi']),0)."%</td>
                    <!-- S/D Bulan Lalu -->
                    <td align=\"right\">".number_format(xPerY($biaya[$jenis]['total']['sdbl'], $luas['aktual']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($budget[$jenis]['total']['rat'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($budget[$jenis]['total']['sdbl'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(getPersen($biaya[$jenis]['total']['sdbl'], $budget[$jenis]['total']['sdbl']),0)."%</td>
                    <!-- S/D Bulan Ini -->
                    <td align=\"right\">".number_format(xPerY($biaya[$jenis]['total']['sdbi'], $luas['aktual']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($budget[$jenis]['total']['rat'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($budget[$jenis]['total']['sdbi'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(getPersen($biaya[$jenis]['total']['sdbi'], $budget[$jenis]['total']['sdbi']),0)."%</td>
                    <!-- S/D Bulan Ini di Tahun Lalu -->
                    <td align=\"right\">".number_format(xPerY($biaya[$jenis]['total']['sdbi_tl'], $luas['aktual']['tl']),0)."</td>
                    <!-- Anggaran Tahun Ini -->
                    <td align=\"right\">".number_format(xPerY($budget[$jenis]['total']['rat'], $luas['budget']['bi']),0)."</td>
                    <!-- Persen -->
                    <td align=\"right\">".number_format(getPersen($biaya[$jenis]['total']['sdbi'], $budget[$jenis]['total']['rat']),0)."%</td>
                </tr>";
        if ($jenis == 'pupuk') {
            $html .= "<tr style=\"height:10px;\"></tr>";
            $html .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
                    <td>Total Biaya Tanaman Menghasilkan</td>
                    <td align=\"center\">Rp</td>
                    <td align=\"right\">".number_format($total['rawatpupuk']['aktualbi'],0)."</td>
                    <td align=\"right\">".number_format($total['rawatpupuk']['rat'],0)."</td>
                    <td align=\"right\">".number_format($total['rawatpupuk']['rabbi'],0)."</td>
                    <td align=\"right\">".number_format(getPersen($total['rawatpupuk']['aktualbi'], $total['rawatpupuk']['rabbi']),0)."%</td>
                    <!-- S/D Bulan Lalu -->
                    <td align=\"right\">".number_format($total['rawatpupuk']['aktualsdbl'],0)."</td>
                    <td align=\"right\">".number_format($total['rawatpupuk']['rat'],0)."</td>
                    <td align=\"right\">".number_format($total['rawatpupuk']['rabsdbl'],0)."</td>
                    <td align=\"right\">".number_format(getPersen($total['rawatpupuk']['aktualsdbl'], $total['rawatpupuk']['rabsdbl']),0)."%</td>
                    <!-- S/D Bulan Ini -->
                    <td align=\"right\">".number_format($total['rawatpupuk']['aktualsdbi'],0)."</td>
                    <td align=\"right\">".number_format($total['rawatpupuk']['rat'],0)."</td>
                    <td align=\"right\">".number_format($total['rawatpupuk']['rabsdbi'],0)."</td>
                    <td align=\"right\">".number_format(getPersen($total['rawatpupuk']['aktualsdbi'], $total['rawatpupuk']['rabsdbi']),0)."%</td>
                    <!-- S/D Bulan Ini di Tahun Lalu -->
                    <td align=\"right\">".number_format($total['rawatpupuk']['aktualsdbi_tl'],0)."</td>
                    <!-- Anggaran Tahun Ini -->
                    <td align=\"right\">".number_format($total['rawatpupuk']['rat'],0)."</td>
                    <!-- Persen -->
                    <td align=\"right\">".number_format(getPersen($total['rawatpupuk']['aktualsdbi'], $total['rawatpupuk']['rat']),0)."%</td>
                </tr>";
            $html .= "<tr class=\"rowcontent\">
                    <td>Biaya per Hektar</td>
                    <td align=\"center\">Rp</td>
                    <td align=\"right\">".number_format(xPerY($total['rawatpupuk']['aktualbi'], $luas['aktual']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($total['rawatpupuk']['rat'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($total['rawatpupuk']['rabbi'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(getPersen(xPerY($total['rawatpupuk']['aktualbi'], $luas['aktual']['bi']), xPerY($total['rawatpupuk']['rabbi'], $luas['budget']['bi'])),0)."%</td>
                    <!-- S/D Bulan Lalu -->
                    <td align=\"right\">".number_format(xPerY($total['rawatpupuk']['aktualsdbl'], $luas['aktual']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($total['rawatpupuk']['rat'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($total['rawatpupuk']['rabsdbl'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(getPersen(xPerY($total['rawatpupuk']['aktualsdbl'], $luas['aktual']['bi']), xPerY($total['rawatpupuk']['rabsdbl'], $luas['budget']['bi'])),0)."%</td>
                    <!-- S/D Bulan Ini -->
                    <td align=\"right\">".number_format(xPerY($total['rawatpupuk']['aktualsdbi'], $luas['aktual']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($total['rawatpupuk']['rat'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(xPerY($total['rawatpupuk']['rabsdbi'], $luas['budget']['bi']),0)."</td>
                    <td align=\"right\">".number_format(getPersen(xPerY($total['rawatpupuk']['aktualsdbi'], $luas['aktual']['bi']), xPerY($total['rawatpupuk']['rabsdbi'], $luas['budget']['bi'])),0)."%</td>
                    <!-- S/D Bulan Ini di Tahun Lalu -->
                    <td align=\"right\">".number_format(xPerY($total['rawatpupuk']['aktualsdbi_tl'], $luas['aktual']['sdbi_tl']),0)."</td>
                    <!-- Anggaran Tahun Ini -->
                    <td align=\"right\">".number_format(xPerY($total['rawatpupuk']['rat'], $luas['budget']['bi']),0)."</td>
                    <!-- Persen -->
                    <td align=\"right\">".number_format(getPersen(xPerY($total['rawatpupuk']['aktualsdbi'], $luas['aktual']['bi']), xPerY($total['rawatpupuk']['rat'], $luas['budget']['bi'])),0)."%</td>
                </tr>";
        }
    } else {
        $html .= "<tr class=\"rowcontent\">
                    <td>Biaya per Ton TBS</td>
                    <td align=\"center\">Rp</td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Lalu -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Ini -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Ini di Tahun Lalu -->
                    <td align=\"right\"></td>
                    <!-- Anggaran Tahun Ini -->
                    <td align=\"right\"></td>
                    <!-- Persen -->
                    <td align=\"right\"></td>
                </tr>";
        
        if ($jenis == 'angkut') {
            $html .= "<tr style=\"height:10px;\"></tr>";
            $html .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
                    <td>Total Biaya Panen dan Angkut</td>
                    <td align=\"center\">Rp</td>
                    <td align=\"right\">".number_format($total['panenangkut']['aktualbi'],0)."</td>
                    <td align=\"right\">".number_format($total['panenangkut']['rat'],0)."</td>
                    <td align=\"right\">".number_format($total['panenangkut']['rabbi'],0)."</td>
                    <td align=\"right\">".number_format(getPersen($total['panenangkut']['aktualbi'], $total['panenangkut']['rabbi']),0)."%</td>
                    <!-- S/D Bulan Lalu -->
                    <td align=\"right\">".number_format($total['panenangkut']['aktualsdbl'],0)."</td>
                    <td align=\"right\">".number_format($total['panenangkut']['rat'],0)."</td>
                    <td align=\"right\">".number_format($total['panenangkut']['rabsdbl'],0)."</td>
                    <td align=\"right\">".number_format(getPersen($total['panenangkut']['aktualsdbl'], $total['panenangkut']['rabsdbl']),0)."%</td>
                    <!-- S/D Bulan Ini -->
                    <td align=\"right\">".number_format($total['panenangkut']['aktualsdbi'],0)."</td>
                    <td align=\"right\">".number_format($total['panenangkut']['rat'],0)."</td>
                    <td align=\"right\">".number_format($total['panenangkut']['rabsdbi'],0)."</td>
                    <td align=\"right\">".number_format(getPersen($total['panenangkut']['aktualsdbi'], $total['panenangkut']['rabsdbi']),0)."%</td>
                    <!-- S/D Bulan Ini di Tahun Lalu -->
                    <td align=\"right\">".number_format($total['panenangkut']['aktualsdbi_tl'],0)."</td>
                    <!-- Anggaran Tahun Ini -->
                    <td align=\"right\">".number_format($total['panenangkut']['rat'],0)."</td>
                    <!-- Persen -->
                    <td align=\"right\">".number_format(getPersen($total['panenangkut']['aktualsdbi'], $total['panenangkut']['rat']),0)."%</td>
                </tr>";
            $html .= "<tr class=\"rowcontent\">
                    <td>Biaya per Ton TBS</td>
                    <td align=\"center\">Rp</td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Lalu -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Ini -->
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <td align=\"right\"></td>
                    <!-- S/D Bulan Ini di Tahun Lalu -->
                    <td align=\"right\"></td>
                    <!-- Anggaran Tahun Ini -->
                    <td align=\"right\"></td>
                    <!-- Persen -->
                    <td align=\"right\"></td>
                </tr>";
        }
    }
    $html .= "<tr style=\"height:10px;\"></tr>";
}

$html .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
            <td>Total Biaya Langsung</td>
            <td align=\"center\">Rp</td>
            <td align=\"right\">".number_format($total['all']['aktualbi'],0)."</td>
            <td align=\"right\">".number_format($total['all']['rat'],0)."</td>
            <td align=\"right\">".number_format($total['all']['bi'],0)."</td>
            <td align=\"right\">".number_format(getPersen($total['all']['aktualbi'], $total['all']['bi']),0)."%</td>
            <!-- S/D Bulan Lalu -->
            <td align=\"right\">".number_format($total['all']['aktualsdbl'],0)."</td>
            <td align=\"right\">".number_format($total['all']['rat'],0)."</td>
            <td align=\"right\">".number_format($total['all']['sdbl'],0)."</td>
            <td align=\"right\">".number_format(getPersen($total['all']['aktualsdbl'], $total['all']['sdbl']),0)."%</td>
            <!-- S/D Bulan Ini -->
            <td align=\"right\">".number_format($total['all']['aktualsdbi'],0)."</td>
            <td align=\"right\">".number_format($total['all']['rat'],0)."</td>
            <td align=\"right\">".number_format($total['all']['sdbi'],0)."</td>
            <td align=\"right\">".number_format(getPersen($total['all']['aktualsdbi'], $total['all']['sdbi']),0)."%</td>
            <!-- S/D Bulan Ini di Tahun Lalu -->
            <td align=\"right\">".number_format($total['all']['aktualsdbi_tl'],0)."</td>
            <!-- Anggaran Tahun Ini -->
            <td align=\"right\">".number_format($total['all']['rat'],0)."</td>
            <!-- Persen -->
            <td align=\"right\">".number_format(getPersen($total['all']['aktualsdbi'], $total['all']['rat']),0)."%</td>
        </tr>";



$html .= "</tbody>";
$html .= "</table>";

echo $html;