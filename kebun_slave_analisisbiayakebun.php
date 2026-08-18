<?php
/**
 * Laporan Analisis Biaya Kebun
 * 
 * File ini menghasilkan laporan analisis biaya kebun dengan perbandingan:
 * - Bulan Ini vs Bulan Lalu vs S/D Bulan Ini
 * - Versus Budget dan Tahun Lalu
 * 
 * Output bisa dalam format Preview atau Excel
 */

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
    $inplas=" and intiplasma='".$intiplasma."'";
    if ($intiplasma == 'I') {
        $inplas2 = " and b.inti = '1'";
    } elseif ($intiplasma == 'P') {
        $inplas2 = " and b.inti = '0'";
    }
}

// Validasi input
if ($periode == '' || $kode_org_raw == '') {
    exit("Error : Periode atau PT tidak boleh kosong");
}

if ($divisi_raw != '') {
    $orgArr = explode(',', $divisi_raw);
    $unitList = "'".implode("','", $orgArr)."'";
    $whereOrg = "substr(kodeorg,1,6) IN (".$unitList.")";
    $whereOrgKaloBlok = "substr(kodeblok,1,6) IN (".$unitList.")";
    $len = 6;
} else {
    $orgArr = explode(',', $kode_org_raw);
    $unitList = "'".implode("','", $orgArr)."'";
    $whereOrg = "substr(kodeorg,1,4) IN (".$unitList.")";
    $whereOrgKaloBlok = "substr(kodeblok,1,4) IN (".$unitList.")";
    $len = 4;
}

$nojurnalParts = [];
foreach($orgArr as $u) {
    $nojurnalParts[] = "a.nojurnal LIKE '%".$u."%'";
}
$whereNoJurnal = "(".implode(" OR ", $nojurnalParts).")";
$kode_org = $orgArr[0]; // Fallback for single value usage if any


// Convert string periode ke DateTime object
$periode = new DateTime($periode);
$date_bulan_lalu = (clone $periode)->modify('-1 month');
$date_bulan_ini_tahun_lalu = (clone $periode)->modify('-1 year');
$date_bulan_depan = (clone $periode)->modify('+1 month');
$date_tahun_lalu_bulan_selanjutnya = (clone $date_bulan_ini_tahun_lalu)->modify('+1 month');

// Format tanggal untuk query
$p_bulan_ini = $periode->format('Ym');
$p_date_bulan_lalu = $date_bulan_lalu->format('Ym');
$p_tahun_lalu = $date_bulan_ini_tahun_lalu->format('Ym');
$tahun_ini = $periode->format('Y');
$tahun_lalu = $date_bulan_ini_tahun_lalu->format('Y');

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Format row HTML untuk table dengan 17 kolom data (Fixed Width)
 * 
 * @param string $label - Deskripsi/Label baris
 * @param array $values - Array dengan 17 nilai: [bulanIniAkt, bulanIniRat, bulanIniRab, ...]
 * @param string $class - CSS class untuk row
 * @param int $colspan - Colspan untuk label (default 1)
 * @return string - HTML <tr> code
 */
function buildTableRow($label, $values, $class = 'rowcontent', $colspan = 1) {
    $html = "<tr class=\"{$class}\">";
    
    // Label column
    if ($colspan > 1) {
        $html .= "<td colspan=\"{$colspan}\">".htmlspecialchars($label)."</td>";
    } else {
        $html .= "<td>".htmlspecialchars($label)."</td>";
    }
    
    // Data columns (17 columns: 4+4+4+1+1+1)
    foreach ($values as $value) {
        $html .= "<td align=\"right\">" . (is_null($value) ? 'SDBI TL' : number_format($value, 2)) . "</td>";
    }
    
    $html .= "</tr>";
    return $html;
}

/**
 * Format row HTML dengan unit dan style khusus
 * Untuk: Hektar, Ton, Rp, dll
 */
function buildTableRowWithUnit($label, $unit, $values, $class = 'rowcontent', $style = '') {
    $styleAttr = !empty($style) ? " style=\"{$style}\"" : '';
    $html = "<tr class=\"{$class}\"{$styleAttr}>";
    $html .= "<td>".htmlspecialchars($label)."</td>";
    $html .= "<td align=\"center\">".htmlspecialchars($unit)."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['aktual_bulan_ini'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['rat_bulan_ini'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['rab_bulan_ini'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['persen_bulan_ini'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['aktual_bulan_lalu'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['rat_bulan_lalu'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['rab_bulan_lalu'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['persen_bulan_lalu'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['aktual_bulan_sdbi'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['rat_bulan_sdbi'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['rab_bulan_sdbi'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['persen_bulan_sdbi'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['sdbi_tahun_lalu'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['anggaran_tahun_ini'])."</td>";
    $html .= "<td align=\"right\">".htmlspecialchars($values['persen'])."</td>";
    
    // foreach ($values as $value) {
    //     $html .= "<td align=\"right\">";
    //     if (is_null($value)) {
    //         $html .= 'SDBI TL';
    //     } elseif (is_string($value)) {
    //         $html .= htmlspecialchars($value);
    //     } else {
    //         $html .= number_format($value, 2);
    //     }
    //     $html .= "</td>";
    // }
    
    $html .= "</tr>";
    return $html;
}

function executeQuery($sql) {
    global $owlPDO;
    try {
        return $owlPDO->query($sql)->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get safe value dengan fallback
 */
function getVal($data, $key, $default = 0) {
    return isset($data[$key]) ? (float)$data[$key] : $default;
}

/**
 * Convert ton dari kilogram
 */
function tonFromKg($kg) {
    return $kg / 1000;
}

/**
 * Calculate percentage dengan safety check
 */
function calcPercent($numerator, $denominator) {
    return ($denominator != 0) ? ($numerator / $denominator) * 100 : 0;
}

/**
 * Calculate ton per hektar
 */
function calcTonHa($ton, $ha) {
    return ($ha != 0) ? ($ton / $ha) : 0;
}

/**
 * Hitung biaya per hektar
 */
function calcBiayaPerHa($biaya, $ha) {
    return ($ha != 0) ? ($biaya / $ha) : 0;
}

/**
 * Hitung biaya per ton
 */
function calcBiayaPerTon($biaya, $ton) {
    return ($ton != 0) ? ($biaya / $ton) : 0;
}


// ============================================================================
// DATA QUERIES
// ============================================================================

// 1. Query Data Luas TM (Tanaman Menghasilkan)
$sql_luas_tm = "SELECT 
    SUM(CASE WHEN tahun = '".$p_bulan_ini."' THEN luasareaproduktif ELSE 0 END) AS luas_tm_ini,
    SUM(CASE WHEN tahun = '".$p_date_bulan_lalu."' THEN luasareaproduktif ELSE 0 END) AS luas_tm_lalu,
    SUM(CASE WHEN tahun = '".$p_tahun_lalu."' THEN luasareaproduktif ELSE 0 END) AS luas_tm_sdbi_tl
FROM ".$dbname.".setup_blok_tahunan 
WHERE ".$whereOrg."
AND statusblok = 'TM' ".$inplas."
AND tahun IN ('".$p_bulan_ini."', '".$p_date_bulan_lalu."', '".$p_tahun_lalu."')";

// exit('warning: ' . $sql_luas_tm);\

$data_luas = executeQuery($sql_luas_tm);
$luas_tm_bulan_ini = getVal($data_luas, 'luas_tm_ini');
$luas_tm_bulan_lalu = getVal($data_luas, 'luas_tm_lalu');
$luas_tm_sdbi_tahun_lalu = getVal($data_luas, 'luas_tm_sdbi_tl');

// 2. Query Budget
$sql_budget_luas_tm = "SELECT 
    SUM(CASE WHEN tahunbudget = '".$tahun_ini."' THEN hathnini ELSE 0 END) AS bgt_ini,
    SUM(CASE WHEN tahunbudget = '".$tahun_lalu."' THEN hathnini ELSE 0 END) AS bgt_lalu
FROM ".$dbname.".bgt_blok 
WHERE ".$whereOrgKaloBlok."
AND statusblok='TM' ".$inplas."
AND tahunbudget IN ('".$tahun_ini."', '".$tahun_lalu."')";

// exit('warning: '.$sql_budget_luas_tm);

$data_budget_luas = executeQuery($sql_budget_luas_tm);
$budget_luas_tm_bulan_ini = getVal($data_budget_luas, 'bgt_ini');
$budget_luas_tm_bulan_lalu = getVal($data_budget_luas, 'bgt_lalu');

// 3. Query Produksi Buah (dalam Ton)
$bulan_depan_str = $date_bulan_depan->format('Y-m-d');
$sql_produksi_buah = "SELECT 
    SUM(CASE WHEN a.tanggal >= '".$periode->format('Y-m-d')."' AND a.tanggal < '".$bulan_depan_str."' THEN a.beratbersih ELSE 0 END) AS buah_bulan_ini,
    SUM(CASE WHEN a.tanggal >= '".$periode->format('Y-01-01')."' AND a.tanggal < '".$periode->format('Y-m-d')."' THEN a.beratbersih ELSE 0 END) AS buah_bulan_lalu,
    SUM(CASE WHEN a.tanggal >= '".$periode->format('Y-01-01')."' AND a.tanggal < '".$bulan_depan_str."' THEN a.beratbersih ELSE 0 END) AS buah_sdbi,
    SUM(CASE WHEN a.tanggal >= '".$tahun_lalu.'-01-01'."' AND a.tanggal < '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m-d')."' THEN a.beratbersih ELSE 0 END) AS buah_sdbi_tl
FROM ".$dbname.".pabrik_timbangan a 
LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
WHERE ".$whereOrg." ".$inplas2."
AND a.tanggal BETWEEN '".$tahun_lalu.'-01-01'."' AND '".$bulan_depan_str."'";

// exit('warning: '.$sql_produksi_buah);

$data_produksi_buah = executeQuery($sql_produksi_buah);
$produksi_buah_bulan_ini = tonFromKg(getVal($data_produksi_buah, 'buah_bulan_ini'));
$produksi_buah_bulan_lalu = tonFromKg(getVal($data_produksi_buah, 'buah_bulan_lalu'));
$produksi_buah_sdbi = tonFromKg(getVal($data_produksi_buah, 'buah_sdbi'));
$produksi_buah_sdbi_tl = tonFromKg(getVal($data_produksi_buah, 'buah_sdbi_tl'));

// 4. RAT & RAB data
$array_kode_rab_by_bulan = [
    '01' => 'kg01', '02' => 'kg02', '03' => 'kg03', '04' => 'kg04',
    '05' => 'kg05', '06' => 'kg06', '07' => 'kg07', '08' => 'kg08',
    '09' => 'kg09', '10' => 'kg10', '11' => 'kg11', '12' => 'kg12'
];

$m_bulan_ini = $array_kode_rab_by_bulan[$periode->format('m')];
$m_bulan_lalu = $array_kode_rab_by_bulan[$date_bulan_lalu->format('m')];

// Build dynamic RAB query untuk S/D bulan ini
$start = new DateTime($periode->format('Y').'-01-01');
$end = (clone $periode)->modify('first day of this month');
$sum_parts = [];

while ($start <= $end) {
    $tahun = $start->format('Y');
    $bulan = $start->format('m');
    $sum_parts[$tahun][] = $array_kode_rab_by_bulan[$bulan];
    $start->modify('+1 month');
}

$case_sql_sdbi = [];
foreach ($sum_parts as $tahun => $kolom) {
    if (!empty($kolom)) {
        $case_sql_sdbi[] = "SUM(CASE WHEN tahunbudget = '".$tahun."' THEN ".implode('+',$kolom)." ELSE 0 END)";
    }
}
$rab_sdbi_query = empty($case_sql_sdbi) ? "0" : implode(' + ', $case_sql_sdbi);
$rat_sdbi_query = "SUM(CASE WHEN tahunbudget = '".$tahun_ini."' THEN totalkg ELSE 0 END)";

$sql_budget_produksi = "SELECT 
    SUM(CASE WHEN tahunbudget = '".$tahun_ini."' THEN totalkg ELSE 0 END) AS rat_ini,
    SUM(CASE WHEN tahunbudget = '".$tahun_ini."' THEN totalkg ELSE 0 END) AS rat_lalu,
    ".$rat_sdbi_query." AS rat_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun_ini."' THEN ".$m_bulan_ini." ELSE 0 END) AS rab_ini,
    SUM(CASE WHEN tahunbudget = '".$tahun_ini."' THEN ".$m_bulan_lalu." ELSE 0 END) AS rab_lalu,
    ".$rab_sdbi_query." AS rab_sdbi
FROM 
    ".$dbname.".bgt_produksi_kebun ".$inplas."
WHERE tahunbudget IN ('".$tahun_ini."', '".$tahun_lalu."')
    AND ".$whereOrgKaloBlok;

// exit('warning: '.$sql_budget_produksi);

$data_budget_produksi = executeQuery($sql_budget_produksi);
$rat_budget_produksi_bulan_ini = tonFromKg(getVal($data_budget_produksi, 'rat_ini'));
$rat_budget_produksi_bulan_lalu = tonFromKg(getVal($data_budget_produksi, 'rat_lalu'));
$rat_budget_produksi_sdbi = tonFromKg(getVal($data_budget_produksi, 'rat_sdbi'));

$rab_budget_produksi_bulan_ini = tonFromKg(getVal($data_budget_produksi, 'rab_ini'));
$rab_budget_produksi_bulan_lalu = tonFromKg(getVal($data_budget_produksi, 'rab_lalu'));
$rab_budget_produksi_sdbi = tonFromKg(getVal($data_budget_produksi, 'rab_sdbi'));

// 5. Hitung persentase
$persenBulanIni = calcPercent($luas_tm_bulan_ini, $budget_luas_tm_bulan_ini);
$persenBulanLalu = calcPercent($luas_tm_bulan_lalu, $budget_luas_tm_bulan_lalu);
$persen = calcPercent($luas_tm_bulan_ini, $luas_tm_bulan_ini);

$persenBuahBulanIni = calcPercent($produksi_buah_bulan_ini, $rab_budget_produksi_bulan_ini);
$persenBuahBulanLalu = calcPercent($produksi_buah_bulan_lalu, $rab_budget_produksi_bulan_lalu);
$persenBuahPer1toPer2 = calcPercent($produksi_buah_sdbi, $rab_budget_produksi_sdbi);
$persen_buah_inti = calcPercent($produksi_buah_sdbi, $rat_budget_produksi_bulan_ini);

// Ton/Ha TBS
$aktual_ton_ha_bulan_ini = calcTonHa($produksi_buah_bulan_ini, $luas_tm_bulan_ini);
$aktual_ton_ha_bulan_lalu = calcTonHa($produksi_buah_bulan_lalu, $luas_tm_bulan_lalu);
$aktual_ton_ha_sdbi = calcTonHa($produksi_buah_sdbi, $luas_tm_bulan_ini);
$aktual_ton_ha_sdbi_tl = calcTonHa($produksi_buah_sdbi_tl, $luas_tm_sdbi_tahun_lalu);

// 6. Query Biaya Umum
$sql_biaya_umum = "SELECT 
    SUM(CASE WHEN a.periode = '".$periode->format('Y-m')."' THEN a.jumlah ELSE 0 END) AS biaya_umum_ini,
    SUM(CASE WHEN a.periode >= '".$periode->format('Y-01')."' AND a.periode <= '".$date_bulan_lalu->format('Y-m')."' THEN a.jumlah ELSE 0 END) AS biaya_umum_lalu,
    SUM(CASE WHEN a.periode >= '".$periode->format('Y-01')."' AND a.periode <= '".$periode->format('Y-m')."' THEN a.jumlah ELSE 0 END) AS biaya_umum_sdbi,
    SUM(CASE WHEN a.periode >= '".$tahun_lalu.'-01'."' AND a.periode < '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' THEN a.jumlah ELSE 0 END) AS biaya_umum_sdbi_tl
FROM ".$dbname.".keu_jurnaldt_vw a LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi 
WHERE a.periode >= '".$tahun_lalu.'-01'."' AND a.periode <= '".$periode->format('Y-m')."'
AND ".$whereNoJurnal." ".$inplas2."
AND a.noakun BETWEEN '7110100' AND '7199999'
AND a.noakun NOT IN ('7120800', '71308', '7130804', '7130805')";

// exit('warning: '.$sql_biaya_umum);

$data_biaya_umum = executeQuery($sql_biaya_umum);
$biaya_umum_bulan_ini = getVal($data_biaya_umum, 'biaya_umum_ini');
$biaya_umum_bulan_lalu = getVal($data_biaya_umum, 'biaya_umum_lalu');
$biaya_umum_sdbi = getVal($data_biaya_umum, 'biaya_umum_sdbi');
$biaya_umum_sdbi_tl = getVal($data_biaya_umum, 'biaya_umum_sdbi_tl');


// Query budget biaya umum
// $sql_budget_biaya = "SELECT rupiah AS rat_umum_bi 
// FROM ".$dbname.".bgt_budget_detail  
// WHERE
//     tahunbudget = '".$tahun_ini."'
//     AND kodeorg = '".$kode_org."'
//     AND noakun BETWEEN '7110100' AND '7199999'
//     AND noakun NOT IN ('7120800', '71308', '7130804', '7130805')";

// // exit('warning: '.$sql_budget_biaya);

// #1 thn
// #sdthn
$addstrsdbi="(";
for($i=1;$i<=intval($periode->format('m'));$i++)
{
    if($i<10)
    {
        $isi="a.rp0".$i;
    }
    else 
    {
        $isi="a.rp".$i;
    }
    if($i<intval($periode->format('m')))
    {
        $addstrsdbi.=$isi."+";
    }
    else
    {
        $addstrsdbi.=$isi;
    }
}
$addstrsdbi.=")";

$addstrsdbl="(";
for($i=1;$i<=intval($date_bulan_lalu->format('m'));$i++)
{
    if($i<10)
    {
        $isi="a.rp0".$i;
    }
    else 
    {
        $isi="a.rp".$i;
    }
    if($i<intval($date_bulan_lalu->format('m')))
    {
        $addstrsdbl.=$isi."+";
    }
    else
    {
        $addstrsdbl.=$isi;
    }
}
$addstrsdbl.=")";


// Budget Biaya Umum
$str="
SELECT 
    ".$addstrsdbi." AS sdbi, 
    ".$addstrsdbl." AS sdbl, 
    a.rp".$periode->format('m')." AS bi, 
    SUM(CASE WHEN a.tahunbudget='".$tahun_lalu."' THEN ".$addstrsdbi." ELSE 0 END) AS sdbi_tl,    
    SUM(a.rupiah) as setahun
from 
    ".$dbname.".bgt_budget_detail a
left join 
    ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi
where 
    a.tahunbudget='".$tahun."' 
    and a.kodeorg IN (".$unitList.")
    ".$inplas2."
    and a.tipebudget = 'ESTATE'
    and a.kodebudget = 'UMUM'
    AND a.noakun BETWEEN '7110100' AND '7199999'
    AND a.noakun NOT IN ('7120800', '71308', '7130804', '7130805')";

// exit('warning: '.$str); 

$data_biaya_umum_bgt = executeQuery($str);
$budget_biaya_umum_bulan_ini = getVal($data_biaya_umum_bgt, 'bi');
$budget_biaya_umum_bulan_lalu = getVal($data_biaya_umum_bgt, 'sdbl');
$budget_biaya_umum_sdbi = getVal($data_biaya_umum_bgt, 'sdbi');
$budget_biaya_umum_sdbi_tl = getVal($data_biaya_umum_bgt, 'sdbi_tl');
$budget_tahun_ini = getVal($data_biaya_umum_bgt, 'setahun');

$persen_biaya_umum = calcPercent($biaya_umum_sdbi, $budget_tahun_ini);

// BIAYA UMUM per HEKTAR
$aktual_biaya_umum_per_ha_bulan_ini = calcBiayaPerHa($biaya_umum_bulan_ini, $luas_tm_bulan_ini);
$rat_biaya_umum_per_ha_bulan_ini = calcBiayaPerHa($budget_biaya_umum_bulan_ini, $budget_luas_tm_bulan_ini);
$rab_biaya_umum_per_ha_bulan_ini = calcBiayaPerHa($budget_biaya_umum_bulan_ini, $budget_luas_tm_bulan_ini);
$persen_biaya_umum_per_ha_bulan_ini = calcPercent($aktual_biaya_umum_per_ha_bulan_ini, $rab_biaya_umum_per_ha_bulan_ini);

$aktual_biaya_umum_per_ha_bulan_lalu = calcBiayaPerHa($biaya_umum_bulan_lalu, $luas_tm_bulan_lalu);
$rat_biaya_umum_per_ha_bulan_lalu = calcBiayaPerHa($budget_biaya_umum_bulan_lalu, $budget_luas_tm_bulan_lalu);
$rab_biaya_umum_per_ha_bulan_lalu = calcBiayaPerHa($budget_biaya_umum_bulan_lalu, $budget_luas_tm_bulan_lalu);
$persen_biaya_umum_per_ha_bulan_lalu = calcPercent($aktual_biaya_umum_per_ha_bulan_lalu, $rab_biaya_umum_per_ha_bulan_lalu);

$aktual_biaya_umum_per_ha_sdbi = calcBiayaPerHa($biaya_umum_sdbi, $luas_tm_bulan_ini);
$rat_biaya_umum_per_ha_sdbi = calcBiayaPerHa($budget_biaya_umum_sdbi, $budget_luas_tm_bulan_ini);
$rab_biaya_umum_per_ha_sdbi = calcBiayaPerHa($budget_biaya_umum_sdbi, $budget_luas_tm_bulan_ini);
$persen_biaya_umum_per_ha_sdbi = calcPercent($aktual_biaya_umum_per_ha_sdbi, $rab_biaya_umum_per_ha_sdbi);

$biaya_umum_per_ha_sdbi_tl = calcBiayaPerHa($biaya_umum_sdbi_tl, $luas_tm_sdbi_tahun_lalu);

$budget_biaya_umum_per_ha_tahun_ini = calcBiayaPerHa($budget_tahun_ini, $budget_luas_tm_bulan_ini);

$persen_biaya_umum_per_ha = calcPercent($aktual_biaya_umum_per_ha_sdbi, $budget_biaya_umum_per_ha_tahun_ini);


// Biaya Perawatan
// $sql_biaya_perawatan = "SELECT 
//     SUM(CASE WHEN a.periode = '".$periode->format('Y-m')."' AND b.kodejurnal LIKE 'INV%' THEN jumlah ELSE 0 END) AS biaya_material_bi,
//     SUM(CASE WHEN a.periode >= '".$periode->format('Y-01')."' AND a.periode <= '".$date_bulan_lalu->format('Y-m')."' AND b.kodejurnal LIKE 'INV%' THEN jumlah ELSE 0 END) AS biaya_material_sdbl,
//     SUM(CASE WHEN a.periode >= '".$periode->format('Y-01')."' AND a.periode <= '".$periode->format('Y-m')."' AND b.kodejurnal LIKE 'INV%' THEN jumlah ELSE 0 END) AS biaya_material_sdbi,
//     SUM(CASE WHEN a.periode >= '".$tahun_lalu.'-01'."' AND a.periode < '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND b.kodejurnal LIKE 'INV%' THEN jumlah ELSE 0 END) AS biaya_material_sdbi_tl,

//     SUM(CASE WHEN a.periode = '".$periode->format('Y-m')."' AND b.kodejurnal NOT LIKE 'INV%' AND b.kodejurnal NOT LIKE 'VHC%' AND b.kodejurnal NOT LIKE 'SPK%' THEN jumlah ELSE 0 END) AS biaya_upah_bi,
//     SUM(CASE WHEN a.periode >= '".$periode->format('Y-01')."' AND a.periode <= '".$date_bulan_lalu->format('Y-m')."' AND b.kodejurnal NOT LIKE 'INV%' AND b.kodejurnal NOT LIKE 'VHC%' AND b.kodejurnal NOT LIKE 'SPK%' THEN jumlah ELSE 0 END) AS biaya_upah_sdbl,
//     SUM(CASE WHEN a.periode >= '".$periode->format('Y-01')."' AND a.periode <= '".$periode->format('Y-m')."' AND b.kodejurnal NOT LIKE 'INV%' AND b.kodejurnal NOT LIKE 'VHC%' AND b.kodejurnal NOT LIKE 'SPK%' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi,
//     SUM(CASE WHEN a.periode >= '".$tahun_lalu.'-01'."' AND a.periode < '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND b.kodejurnal NOT LIKE 'INV%' AND b.kodejurnal NOT LIKE 'VHC%' AND b.kodejurnal NOT LIKE 'SPK%' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi_tl,
    
//     SUM(CASE WHEN a.periode = '".$periode->format('Y-m')."' AND b.kodejurnal LIKE 'VHC%' THEN jumlah ELSE 0 END) AS biaya_transport_bi,
//     SUM(CASE WHEN a.periode >= '".$periode->format('Y-01')."' AND a.periode <= '".$date_bulan_lalu->format('Y-m')."' AND b.kodejurnal LIKE 'VHC%' THEN jumlah ELSE 0 END) AS biaya_transport_sdbl,
//     SUM(CASE WHEN a.periode >= '".$periode->format('Y-01')."' AND a.periode <= '".$periode->format('Y-m')."' AND b.kodejurnal LIKE 'VHC%' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi,
//     SUM(CASE WHEN a.periode >= '".$tahun_lalu.'-01'."' AND a.periode < '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND b.kodejurnal LIKE 'VHC%' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi_tl

// FROM 
//     ".$dbname.".keu_jurnaldt_vw a
//     left join ".$dbname.".keu_jurnalht b on a.nojurnal=b.nojurnal 
// WHERE 
//     a.periode >= '".$tahun_lalu.'-01'."' 
//     AND a.periode <= '".$periode->format('Y-m')."'
//     AND a.nojurnal LIKE '%".$kode_org."%' 
//     AND a.noakun BETWEEN '6210101' AND '6211101'
//     AND a.noakun NOT LIKE '62108%' ";

// exit('warning: '.$sql_biaya_perawatan);

$sql_biaya_perawatan = "WITH base AS(
    SELECT 
        a.periode,
        a.jumlah,
        CASE
            WHEN h.kodejurnal LIKE 'INV%' THEN 'material'
            WHEN h.kodejurnal LIKE 'VHC%' THEN 'transport'
            WHEN h.kodejurnal LIKE 'SPK%' THEN 'spk'
            ELSE 'upah'
        END AS kategori
    FROM ".$dbname.".keu_jurnaldt_vw a
    LEFT JOIN ".$dbname.".keu_jurnalht h ON a.nojurnal=h.nojurnal
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE 
        a.periode >= '".$tahun_lalu.'-01'."'
        AND a.periode <= '".$periode->format('Y-m')."'
        AND ".$whereNoJurnal." ".$inplas2."
        AND a.noakun BETWEEN '6210101' AND '6211101'
        AND a.noakun NOT LIKE '62108%'
)

SELECT 
    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi_tl

FROM base";

// exit('warning: '.$sql_biaya_perawatan);

$data_aktual_biaya_perawatan = executeQuery($sql_biaya_perawatan);
// exit($sql_biaya_perawatan);
$aktual_biaya_perawatan_bi = getVal($data_aktual_biaya_perawatan, 'biaya_total_bi');
$aktual_biaya_perawatan_sdbl = getVal($data_aktual_biaya_perawatan, 'biaya_total_sdbl');
$aktual_biaya_perawatan_sdbi = getVal($data_aktual_biaya_perawatan, 'biaya_total_sdbi');
$aktual_biaya_perawatan_sdbi_tl = getVal($data_aktual_biaya_perawatan, 'biaya_total_sdbi_tl');

$aktual_perawatan_material_bi = getVal($data_aktual_biaya_perawatan, 'biaya_material_bi');
$aktual_perawatan_material_sdbl = getVal($data_aktual_biaya_perawatan, 'biaya_material_sdbl');
$aktual_perawatan_material_sdbi = getVal($data_aktual_biaya_perawatan, 'biaya_material_sdbi');
$aktual_perawatan_material_sdbi_tl = getVal($data_aktual_biaya_perawatan, 'biaya_material_sdbi_tl');

$aktual_perawatan_upah_bi = getVal($data_aktual_biaya_perawatan, 'biaya_upah_bi');
$aktual_perawatan_upah_sdbl = getVal($data_aktual_biaya_perawatan, 'biaya_upah_sdbl');
$aktual_perawatan_upah_sdbi = getVal($data_aktual_biaya_perawatan, 'biaya_upah_sdbi');
$aktual_perawatan_upah_sdbi_tl = getVal($data_aktual_biaya_perawatan, 'biaya_upah_sdbi_tl');

$aktual_perawatan_transport_bi = getVal($data_aktual_biaya_perawatan, 'biaya_transport_bi');
$aktual_perawatan_transport_sdbl = getVal($data_aktual_biaya_perawatan, 'biaya_transport_sdbl');
$aktual_perawatan_transport_sdbi = getVal($data_aktual_biaya_perawatan, 'biaya_transport_sdbi');
$aktual_perawatan_transport_sdbi_tl = getVal($data_aktual_biaya_perawatan, 'biaya_transport_sdbi_tl');

// BUDGET PERAWATAN
$str="WITH base AS (
        SELECT
            a.tahunbudget,
            a.rupiah,
            a.rp".$periode->format('m')." as rpbi,
            ".$addstrsdbl." as rpsdbl,
            ".$addstrsdbi." as rpsdbi,
        CASE 
            WHEN a.kodebudget = 'SUPERVISI' OR substr(a.kodebudget,1,3)='SDM' THEN 'upah'
            WHEN substr(a.kodebudget,1,3) = 'VHC' THEN 'transport'
            WHEN a.kodebudget = 'TOOL' OR substr(a.kodebudget,1,2)='M-' THEN 'material'
        END AS kategori
    FROM ".$dbname.".bgt_budget_detail a
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE a.tahunbudget IN ('".$tahun."','".$tahun_lalu."')
        ".$inplas2."
        AND a.kodeorg IN (".$unitList.")
        AND a.noakun BETWEEN '6210101' AND '6211101'
        AND a.noakun NOT LIKE '62108%'
)

SELECT
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpbi ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbl ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbi ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rupiah ELSE 0 END) AS biaya_material_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpbi ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbl ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbi ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rupiah ELSE 0 END) AS biaya_upah_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpbi ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbl ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbi ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rupiah ELSE 0 END) AS biaya_transport_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' THEN rpbi ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' THEN rpsdbl ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' THEN rpsdbi ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' THEN rupiah ELSE 0 END) AS biaya_total_setahun
    
FROM base";

// exit('warning: '.$str);     

$data_budget_perawatan = executeQuery($str);
$budget_perawatan_material_bi = getVal($data_budget_perawatan, 'biaya_material_bi');
$budget_perawatan_material_sdbl = getVal($data_budget_perawatan, 'biaya_material_sdbl');
$budget_perawatan_material_sdbi = getVal($data_budget_perawatan, 'biaya_material_sdbi');
$budget_perawatan_material_setahun = getVal($data_budget_perawatan, 'biaya_material_setahun');

$budget_perawatan_upah_bi = getVal($data_budget_perawatan, 'biaya_upah_bi');
$budget_perawatan_upah_sdbl = getVal($data_budget_perawatan, 'biaya_upah_sdbl');
$budget_perawatan_upah_sdbi = getVal($data_budget_perawatan, 'biaya_upah_sdbi');
$budget_perawatan_upah_setahun = getVal($data_budget_perawatan, 'biaya_upah_setahun');

$budget_perawatan_transport_bi = getVal($data_budget_perawatan, 'biaya_transport_bi');
$budget_perawatan_transport_sdbl = getVal($data_budget_perawatan, 'biaya_transport_sdbl');
$budget_perawatan_transport_sdbi = getVal($data_budget_perawatan, 'biaya_transport_sdbi');
$budget_perawatan_transport_setahun = getVal($data_budget_perawatan, 'biaya_transport_setahun');

$budget_perawatan_total_bi = getVal($data_budget_perawatan, 'biaya_total_bi');
$budget_perawatan_total_sdbl = getVal($data_budget_perawatan, 'biaya_total_sdbl');
$budget_perawatan_total_sdbi = getVal($data_budget_perawatan, 'biaya_total_sdbi');
$budget_perawatan_total_setahun = getVal($data_budget_perawatan, 'biaya_total_setahun');   

$persen_perawatan_material_bi = calcPercent($aktual_perawatan_material_bi,$budget_perawatan_material_bi);
$persen_perawatan_material_sdbl = calcPercent($aktual_perawatan_material_sdbl,$budget_perawatan_material_sdbl);
$persen_perawatan_material_sdbi = calcPercent($aktual_perawatan_material_sdbi,$budget_perawatan_material_sdbi);
$persen_perawatan_material_setahun = calcPercent($aktual_perawatan_material_sdbi,$budget_perawatan_material_setahun);

$persen_perawatan_upah_bi = calcPercent($aktual_perawatan_upah_bi,$budget_perawatan_upah_bi);
$persen_perawatan_upah_sdbl = calcPercent($aktual_perawatan_upah_sdbl,$budget_perawatan_upah_sdbl);
$persen_perawatan_upah_sdbi = calcPercent($aktual_perawatan_upah_sdbi,$budget_perawatan_upah_sdbi);
$persen_perawatan_upah_setahun = calcPercent($aktual_perawatan_upah_sdbi,$budget_perawatan_upah_setahun);

$persen_perawatan_transport_bi = calcPercent($aktual_perawatan_transport_bi,$budget_perawatan_transport_bi);
$persen_perawatan_transport_sdbl = calcPercent($aktual_perawatan_transport_sdbl,$budget_perawatan_transport_sdbl);
$persen_perawatan_transport_sdbi = calcPercent($aktual_perawatan_transport_sdbi,$budget_perawatan_transport_sdbi);
$persen_perawatan_transport_setahun = calcPercent($aktual_perawatan_transport_sdbi,$budget_perawatan_transport_setahun);

$persen_perawatan_total_bi = calcPercent($aktual_biaya_perawatan_bi,$budget_perawatan_total_bi);
$persen_perawatan_total_sdbl = calcPercent($aktual_biaya_perawatan_sdbl,$budget_perawatan_total_sdbl);
$persen_perawatan_total_sdbi = calcPercent($aktual_biaya_perawatan_sdbi,$budget_perawatan_total_sdbi);
$persen_perawatan_total_setahun = calcPercent($aktual_biaya_perawatan_sdbi_tl,$budget_perawatan_total_setahun);

$aktual_perawatan_per_ha_bi = calcBiayaPerHa($aktual_biaya_perawatan_bi,$luas_tm_bulan_ini);
$aktual_perawatan_per_ha_sdbl = calcBiayaPerHa($aktual_biaya_perawatan_sdbl,$luas_tm_bulan_lalu);
$aktual_perawatan_per_ha_sdbi = calcBiayaPerHa($aktual_biaya_perawatan_sdbi,$luas_tm_bulan_ini);
$aktual_perawatan_per_ha_sdbi_tl = calcBiayaPerHa($aktual_biaya_perawatan_sdbi_tl,$luas_tm_sdbi_tahun_lalu);

$budget_perawatan_per_ha_bi = calcBiayaPerHa($budget_perawatan_total_bi,$budget_luas_tm_bulan_ini);
$budget_perawatan_per_ha_sdbl = calcBiayaPerHa($budget_perawatan_total_sdbl,$budget_luas_tm_bulan_lalu);
$budget_perawatan_per_ha_sdbi = calcBiayaPerHa($budget_perawatan_total_sdbi,$budget_luas_tm_bulan_ini);
$budget_perawatan_per_ha_setahun = calcBiayaPerHa($budget_perawatan_total_setahun,$budget_luas_tm_bulan_ini);

$persen_perawatan_per_ha_bi = calcPercent($aktual_perawatan_per_ha_bi,$budget_perawatan_per_ha_bi);
$persen_perawatan_per_ha_sdbl = calcPercent($aktual_perawatan_per_ha_sdbl,$budget_perawatan_per_ha_sdbl);
$persen_perawatan_per_ha_sdbi = calcPercent($aktual_perawatan_per_ha_sdbi,$budget_perawatan_per_ha_sdbi);
$persen_perawatan_per_ha_setahun = calcPercent($aktual_perawatan_per_ha_sdbi_tl,$budget_perawatan_per_ha_setahun);

// Biaya Pupuk
$sql_biaya_pupuk = "WITH base AS(
    SELECT 
        a.periode,
        a.jumlah,
        CASE
            WHEN h.kodejurnal LIKE 'INV%' THEN 'material'
            WHEN h.kodejurnal LIKE 'VHC%' THEN 'transport'
            WHEN h.kodejurnal LIKE 'SPK%' THEN 'spk'
            ELSE 'upah'
        END AS kategori
    FROM ".$dbname.".keu_jurnaldt_vw a
    LEFT JOIN ".$dbname.".keu_jurnalht h ON a.nojurnal=h.nojurnal
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE 
        a.periode >= '".$tahun_lalu.'-01'."'
        AND a.periode <= '".$periode->format('Y-m')."'
        AND ".$whereNoJurnal." ".$inplas2."
        AND a.noakun LIKE '62108%'
)

SELECT 
    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi_tl

FROM base";

// exit($sql_biaya_pupuk);

$data_biaya_pupuk = executeQuery($sql_biaya_pupuk);

$aktual_biaya_pupuk_bi = getVal($data_biaya_pupuk, 'biaya_total_bi');
$aktual_biaya_pupuk_sdbl = getVal($data_biaya_pupuk, 'biaya_total_sdbl');
$aktual_biaya_pupuk_sdbi = getVal($data_biaya_pupuk, 'biaya_total_sdbi');
$aktual_biaya_pupuk_sdbi_tl = getVal($data_biaya_pupuk, 'biaya_total_sdbi_tl');

$aktual_pupuk_material_bi = getVal($data_biaya_pupuk, 'biaya_material_bi');
$aktual_pupuk_material_sdbl = getVal($data_biaya_pupuk, 'biaya_material_sdbl');
$aktual_pupuk_material_sdbi = getVal($data_biaya_pupuk, 'biaya_material_sdbi');
$aktual_pupuk_material_sdbi_tl = getVal($data_biaya_pupuk, 'biaya_material_sdbi_tl');

$aktual_pupuk_upah_bi = getVal($data_biaya_pupuk, 'biaya_upah_bi');
$aktual_pupuk_upah_sdbl = getVal($data_biaya_pupuk, 'biaya_upah_sdbl');
$aktual_pupuk_upah_sdbi = getVal($data_biaya_pupuk, 'biaya_upah_sdbi');
$aktual_pupuk_upah_sdbi_tl = getVal($data_biaya_pupuk, 'biaya_upah_sdbi_tl');

$aktual_pupuk_transport_bi = getVal($data_biaya_pupuk, 'biaya_transport_bi');
$aktual_pupuk_transport_sdbl = getVal($data_biaya_pupuk, 'biaya_transport_sdbl');
$aktual_pupuk_transport_sdbi = getVal($data_biaya_pupuk, 'biaya_transport_sdbi');
$aktual_pupuk_transport_sdbi_tl = getVal($data_biaya_pupuk, 'biaya_transport_sdbi_tl');


// BUDGET PUPUK
$str="WITH base AS (
        SELECT
            a.tahunbudget,
            a.rupiah,
            a.rp".$periode->format('m')." as rpbi,
            ".$addstrsdbl." as rpsdbl,
            ".$addstrsdbi." as rpsdbi,
        CASE 
            WHEN a.kodebudget = 'SUPERVISI' OR substr(a.kodebudget,1,3)='SDM' THEN 'upah'
            WHEN substr(a.kodebudget,1,3) = 'VHC' THEN 'transport'
            WHEN a.kodebudget = 'TOOL' OR substr(a.kodebudget,1,2)='M-' THEN 'material'
        END AS kategori
    FROM ".$dbname.".bgt_budget_detail a
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE a.tahunbudget IN ('".$tahun."','".$tahun_lalu."')
        AND a.kodeorg IN (".$unitList.")
        ".$inplas2."
        AND a.noakun LIKE '62108%'
)

SELECT
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpbi ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbl ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbi ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rupiah ELSE 0 END) AS biaya_material_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpbi ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbl ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbi ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rupiah ELSE 0 END) AS biaya_upah_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpbi ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbl ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbi ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rupiah ELSE 0 END) AS biaya_transport_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpbi ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpsdbl ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpsdbi ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rupiah ELSE 0 END) AS biaya_total_setahun
    
FROM base";

// exit($str);

$data_budget_pupuk = executeQuery($str);
$budget_pupuk_material_bi = getVal($data_budget_pupuk, 'biaya_material_bi');
$budget_pupuk_material_sdbl = getVal($data_budget_pupuk, 'biaya_material_sdbl');
$budget_pupuk_material_sdbi = getVal($data_budget_pupuk, 'biaya_material_sdbi');
$budget_pupuk_material_setahun = getVal($data_budget_pupuk, 'biaya_material_setahun');

$budget_pupuk_upah_bi = getVal($data_budget_pupuk, 'biaya_upah_bi');
$budget_pupuk_upah_sdbl = getVal($data_budget_pupuk, 'biaya_upah_sdbl');
$budget_pupuk_upah_sdbi = getVal($data_budget_pupuk, 'biaya_upah_sdbi');
$budget_pupuk_upah_setahun = getVal($data_budget_pupuk, 'biaya_upah_setahun');

$budget_pupuk_transport_bi = getVal($data_budget_pupuk, 'biaya_transport_bi');
$budget_pupuk_transport_sdbl = getVal($data_budget_pupuk, 'biaya_transport_sdbl');
$budget_pupuk_transport_sdbi = getVal($data_budget_pupuk, 'biaya_transport_sdbi');
$budget_pupuk_transport_setahun = getVal($data_budget_pupuk, 'biaya_transport_setahun');

$budget_pupuk_total_bi = getVal($data_budget_pupuk, 'biaya_total_bi');
$budget_pupuk_total_sdbl = getVal($data_budget_pupuk, 'biaya_total_sdbl');
$budget_pupuk_total_sdbi = getVal($data_budget_pupuk, 'biaya_total_sdbi');
$budget_pupuk_total_setahun = getVal($data_budget_pupuk, 'biaya_total_setahun');   

$persen_pupuk_material_bi = calcPercent($aktual_pupuk_material_bi,$budget_pupuk_material_bi);
$persen_pupuk_material_sdbl = calcPercent($aktual_pupuk_material_sdbl,$budget_pupuk_material_sdbl);
$persen_pupuk_material_sdbi = calcPercent($aktual_pupuk_material_sdbi,$budget_pupuk_material_sdbi);
$persen_pupuk_material_setahun = calcPercent($aktual_pupuk_material_sdbi,$budget_pupuk_material_setahun);

$persen_pupuk_upah_bi = calcPercent($aktual_pupuk_upah_bi,$budget_pupuk_upah_bi);
$persen_pupuk_upah_sdbl = calcPercent($aktual_pupuk_upah_sdbl,$budget_pupuk_upah_sdbl);
$persen_pupuk_upah_sdbi = calcPercent($aktual_pupuk_upah_sdbi,$budget_pupuk_upah_sdbi);
$persen_pupuk_upah_setahun = calcPercent($aktual_pupuk_upah_sdbi,$budget_pupuk_upah_setahun);

$persen_pupuk_transport_bi = calcPercent($aktual_pupuk_transport_bi,$budget_pupuk_transport_bi);
$persen_pupuk_transport_sdbl = calcPercent($aktual_pupuk_transport_sdbl,$budget_pupuk_transport_sdbl);
$persen_pupuk_transport_sdbi = calcPercent($aktual_pupuk_transport_sdbi,$budget_pupuk_transport_sdbi);
$persen_pupuk_transport_setahun = calcPercent($aktual_pupuk_transport_sdbi,$budget_pupuk_transport_setahun);

$persen_pupuk_total_bi = calcPercent($aktual_biaya_pupuk_bi,$budget_pupuk_total_bi);
$persen_pupuk_total_sdbl = calcPercent($aktual_biaya_pupuk_sdbl,$budget_pupuk_total_sdbl);
$persen_pupuk_total_sdbi = calcPercent($aktual_biaya_pupuk_sdbi,$budget_pupuk_total_sdbi);
$persen_pupuk_total_setahun = calcPercent($aktual_biaya_pupuk_sdbi,$budget_pupuk_total_setahun);

$aktual_pupuk_per_ha_bi = calcBiayaPerHa($aktual_biaya_pupuk_bi,$luas_tm_bulan_ini);
$aktual_pupuk_per_ha_sdbl = calcBiayaPerHa($aktual_biaya_pupuk_sdbl,$luas_tm_bulan_lalu);
$aktual_pupuk_per_ha_sdbi = calcBiayaPerHa($aktual_biaya_pupuk_sdbi,$luas_tm_bulan_ini);
$aktual_pupuk_per_ha_sdbi_tl = calcBiayaPerHa($aktual_biaya_pupuk_sdbi_tl,$luas_tm_sdbi_tahun_lalu);

$budget_pupuk_per_ha_bi = calcBiayaPerHa($budget_pupuk_total_bi,$budget_luas_tm_bulan_ini);
$budget_pupuk_per_ha_sdbl = calcBiayaPerHa($budget_pupuk_total_sdbl,$budget_luas_tm_bulan_lalu);
$budget_pupuk_per_ha_sdbi = calcBiayaPerHa($budget_pupuk_total_sdbi,$budget_luas_tm_bulan_ini);
$budget_pupuk_per_ha_setahun = calcBiayaPerHa($budget_pupuk_total_setahun,$budget_luas_tm_bulan_ini);

$persen_pupuk_per_ha_bi = calcPercent($aktual_pupuk_per_ha_bi,$budget_pupuk_per_ha_bi);
$persen_pupuk_per_ha_sdbl = calcPercent($aktual_pupuk_per_ha_sdbl,$budget_pupuk_per_ha_sdbl);
$persen_pupuk_per_ha_sdbi = calcPercent($aktual_pupuk_per_ha_sdbi,$budget_pupuk_per_ha_sdbi);
$persen_pupuk_per_ha_setahun = calcPercent($aktual_pupuk_per_ha_setahun,$budget_pupuk_per_ha_setahun);



// Total Biaya TM 
$aktual_total_biaya_tm_bi = $biaya_umum_bulan_ini + $aktual_biaya_perawatan_bi + $aktual_biaya_pupuk_bi;
$aktual_total_biaya_tm_sdbl = $biaya_umum_bulan_lalu + $aktual_biaya_perawatan_sdbl + $aktual_biaya_pupuk_sdbl;
$aktual_total_biaya_tm_sdbi = $biaya_umum_sdbi + $aktual_biaya_perawatan_sdbi + $aktual_biaya_pupuk_sdbi;
$aktual_total_biaya_tm_sdbi_tl = $biaya_umum_sdbi_tl + $aktual_biaya_perawatan_sdbi_tl + $aktual_biaya_pupuk_sdbi_tl;

$budget_total_biaya_tm_bi = $budget_biaya_umum_bulan_ini + $budget_perawatan_total_bi + $budget_pupuk_total_bi;
$budget_total_biaya_tm_sdbl = $budget_biaya_umum_bulan_lalu + $budget_perawatan_total_sdbl + $budget_pupuk_total_sdbl;
$budget_total_biaya_tm_sdbi = $budget_biaya_umum_sdbi + $budget_perawatan_total_sdbi + $budget_pupuk_total_sdbi;
$budget_total_biaya_tm_setahun = $budget_tahun_ini + $budget_perawatan_total_setahun + $budget_pupuk_total_setahun;

$persen_total_biaya_tm_bi = calcPercent($aktual_total_biaya_tm_bi,$budget_total_biaya_tm_bi);
$persen_total_biaya_tm_sdbl = calcPercent($aktual_total_biaya_tm_sdbl,$budget_total_biaya_tm_sdbl);
$persen_total_biaya_tm_sdbi = calcPercent($aktual_total_biaya_tm_sdbi,$budget_total_biaya_tm_sdbi);
$persen_total_biaya_tm_setahun = calcPercent($aktual_total_biaya_tm_sdbi,$budget_total_biaya_tm_setahun);

$aktual_total_biaya_tm_per_ha_bi = calcBiayaPerHa($aktual_total_biaya_tm_bi,$luas_tm_bulan_ini);
$aktual_total_biaya_tm_per_ha_sdbl = calcBiayaPerHa($aktual_total_biaya_tm_sdbl,$luas_tm_bulan_lalu);
$aktual_total_biaya_tm_per_ha_sdbi = calcBiayaPerHa($aktual_total_biaya_tm_sdbi,$luas_tm_bulan_ini);
$aktual_total_biaya_tm_per_ha_sdbi_tl = calcBiayaPerHa($aktual_total_biaya_tm_sdbi_tl,$luas_tm_sdbi_tahun_lalu);

$budget_total_biaya_tm_per_ha_bi = calcBiayaPerHa($budget_total_biaya_tm_bi,$budget_luas_tm_bulan_ini);
$budget_total_biaya_tm_per_ha_sdbl = calcBiayaPerHa($budget_total_biaya_tm_sdbl,$budget_luas_tm_bulan_lalu);
$budget_total_biaya_tm_per_ha_sdbi = calcBiayaPerHa($budget_total_biaya_tm_sdbi,$budget_luas_tm_bulan_ini);
$budget_total_biaya_tm_per_ha_setahun = calcBiayaPerHa($budget_total_biaya_tm_setahun,$budget_luas_tm_bulan_ini);

$persen_total_biaya_tm_per_ha_bi = calcPercent($aktual_total_biaya_tm_per_ha_bi,$budget_total_biaya_tm_per_ha_bi);
$persen_total_biaya_tm_per_ha_sdbl = calcPercent($aktual_total_biaya_tm_per_ha_sdbl,$budget_total_biaya_tm_per_ha_sdbl);
$persen_total_biaya_tm_per_ha_sdbi = calcPercent($aktual_total_biaya_tm_per_ha_sdbi,$budget_total_biaya_tm_per_ha_sdbi);
$persen_total_biaya_tm_per_ha_setahun = calcPercent($aktual_total_biaya_tm_per_ha_sdbi,$budget_total_biaya_tm_per_ha_setahun);


// Biaya Panen
$sql_biaya_panen = "WITH base AS(
    SELECT 
        a.periode,
        a.jumlah,
        CASE
            WHEN h.kodejurnal LIKE 'INV%' THEN 'material'
            WHEN h.kodejurnal LIKE 'VHC%' THEN 'transport'
            WHEN h.kodejurnal LIKE 'SPK%' THEN 'spk'
            ELSE 'upah'
        END AS kategori
    FROM ".$dbname.".keu_jurnaldt_vw a
    LEFT JOIN ".$dbname.".keu_jurnalht h ON a.nojurnal=h.nojurnal
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE 
        a.periode >= '".$tahun_lalu.'-01'."'
        AND a.periode <= '".$periode->format('Y-m')."'
        AND ".$whereNoJurnal." ".$inplas2."
        AND a.noakun LIKE '611%' 
        AND a.noakun NOT LIKE '61102%'
)

SELECT 
    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi_tl

FROM base";

// exit($sql_biaya_panen);

$data_biaya_panen = executeQuery($sql_biaya_panen);

$aktual_biaya_panen_bi = getVal($data_biaya_panen, 'biaya_total_bi');
$aktual_biaya_panen_sdbl = getVal($data_biaya_panen, 'biaya_total_sdbl');
$aktual_biaya_panen_sdbi = getVal($data_biaya_panen, 'biaya_total_sdbi');
$aktual_biaya_panen_sdbi_tl = getVal($data_biaya_panen, 'biaya_total_sdbi_tl');

$aktual_panen_material_bi = getVal($data_biaya_panen, 'biaya_material_bi');
$aktual_panen_material_sdbl = getVal($data_biaya_panen, 'biaya_material_sdbl');
$aktual_panen_material_sdbi = getVal($data_biaya_panen, 'biaya_material_sdbi');
$aktual_panen_material_sdbi_tl = getVal($data_biaya_panen, 'biaya_material_sdbi_tl');

$aktual_panen_upah_bi = getVal($data_biaya_panen, 'biaya_upah_bi');
$aktual_panen_upah_sdbl = getVal($data_biaya_panen, 'biaya_upah_sdbl');
$aktual_panen_upah_sdbi = getVal($data_biaya_panen, 'biaya_upah_sdbi');
$aktual_panen_upah_sdbi_tl = getVal($data_biaya_panen, 'biaya_upah_sdbi_tl');

$aktual_panen_transport_bi = getVal($data_biaya_panen, 'biaya_transport_bi');
$aktual_panen_transport_sdbl = getVal($data_biaya_panen, 'biaya_transport_sdbl');
$aktual_panen_transport_sdbi = getVal($data_biaya_panen, 'biaya_transport_sdbi');
$aktual_panen_transport_sdbi_tl = getVal($data_biaya_panen, 'biaya_transport_sdbi_tl');


// Budget Panen
$str="WITH base AS (
        SELECT
            a.tahunbudget,
            a.rupiah,
            a.rp".$periode->format('m')." as rpbi,
            ".$addstrsdbl." as rpsdbl,
            ".$addstrsdbi." as rpsdbi,
        CASE 
            WHEN a.kodebudget = 'SUPERVISI' OR substr(a.kodebudget,1,3)='SDM' THEN 'upah'
            WHEN substr(a.kodebudget,1,3) = 'VHC' THEN 'transport'
            WHEN a.kodebudget = 'TOOL' OR substr(a.kodebudget,1,2)='M-' THEN 'material'
        END AS kategori
    FROM ".$dbname.".bgt_budget_detail a
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE a.tahunbudget IN ('".$tahun."','".$tahun_lalu."')
        AND a.kodeorg IN (".$unitList.")
        ".$inplas2."
        AND a.noakun LIKE '611%'
        AND a.noakun NOT LIKE '61102%'
)

SELECT
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpbi ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbl ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbi ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rupiah ELSE 0 END) AS biaya_material_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpbi ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbl ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbi ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rupiah ELSE 0 END) AS biaya_upah_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpbi ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbl ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbi ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rupiah ELSE 0 END) AS biaya_transport_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpbi ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpsdbl ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpsdbi ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rupiah ELSE 0 END) AS biaya_total_setahun
    
FROM base";

// exit($str);

$data_budget_panen = executeQuery($str);
$budget_panen_material_bi = getVal($data_budget_panen, 'biaya_material_bi');
$budget_panen_material_sdbl = getVal($data_budget_panen, 'biaya_material_sdbl');
$budget_panen_material_sdbi = getVal($data_budget_panen, 'biaya_material_sdbi');
$budget_panen_material_setahun = getVal($data_budget_panen, 'biaya_material_setahun');

$budget_panen_upah_bi = getVal($data_budget_panen, 'biaya_upah_bi');
$budget_panen_upah_sdbl = getVal($data_budget_panen, 'biaya_upah_sdbl');
$budget_panen_upah_sdbi = getVal($data_budget_panen, 'biaya_upah_sdbi');
$budget_panen_upah_setahun = getVal($data_budget_panen, 'biaya_upah_setahun');

$budget_panen_transport_bi = getVal($data_budget_panen, 'biaya_transport_bi');
$budget_panen_transport_sdbl = getVal($data_budget_panen, 'biaya_transport_sdbl');
$budget_panen_transport_sdbi = getVal($data_budget_panen, 'biaya_transport_sdbi');
$budget_panen_transport_setahun = getVal($data_budget_panen, 'biaya_transport_setahun');

$budget_panen_total_bi = getVal($data_budget_panen, 'biaya_total_bi');
$budget_panen_total_sdbl = getVal($data_budget_panen, 'biaya_total_sdbl');
$budget_panen_total_sdbi = getVal($data_budget_panen, 'biaya_total_sdbi');
$budget_panen_total_setahun = getVal($data_budget_panen, 'biaya_total_setahun');   

$persen_panen_material_bi = calcPercent($aktual_panen_material_bi,$budget_panen_material_bi);
$persen_panen_material_sdbl = calcPercent($aktual_panen_material_sdbl,$budget_panen_material_sdbl);
$persen_panen_material_sdbi = calcPercent($aktual_panen_material_sdbi,$budget_panen_material_sdbi);
$persen_panen_material_setahun = calcPercent($aktual_panen_material_sdbi,$budget_panen_material_setahun);

$persen_panen_upah_bi = calcPercent($aktual_panen_upah_bi,$budget_panen_upah_bi);
$persen_panen_upah_sdbl = calcPercent($aktual_panen_upah_sdbl,$budget_panen_upah_sdbl);
$persen_panen_upah_sdbi = calcPercent($aktual_panen_upah_sdbi,$budget_panen_upah_sdbi);
$persen_panen_upah_setahun = calcPercent($aktual_panen_upah_sdbi,$budget_panen_upah_setahun);

$persen_panen_transport_bi = calcPercent($aktual_panen_transport_bi,$budget_panen_transport_bi);
$persen_panen_transport_sdbl = calcPercent($aktual_panen_transport_sdbl,$budget_panen_transport_sdbl);
$persen_panen_transport_sdbi = calcPercent($aktual_panen_transport_sdbi,$budget_panen_transport_sdbi);
$persen_panen_transport_setahun = calcPercent($aktual_panen_transport_sdbi,$budget_panen_transport_setahun);

$persen_panen_total_bi = calcPercent($aktual_biaya_panen_bi,$budget_panen_total_bi);
$persen_panen_total_sdbl = calcPercent($aktual_biaya_panen_sdbl,$budget_panen_total_sdbl);
$persen_panen_total_sdbi = calcPercent($aktual_biaya_panen_sdbi,$budget_panen_total_sdbi);
$persen_panen_total_setahun = calcPercent($aktual_biaya_panen_sdbi,$budget_panen_total_setahun);

$aktual_panen_per_ton_bi = calcBiayaPerTon($aktual_biaya_panen_bi,$produksi_buah_bulan_ini);
$aktual_panen_per_ton_sdbl = calcBiayaPerTon($aktual_biaya_panen_sdbl,$produksi_buah_bulan_lalu);
$aktual_panen_per_ton_sdbi = calcBiayaPerTon($aktual_biaya_panen_sdbi,$produksi_buah_sdbi);
$aktual_panen_per_ton_sdbi_tl = calcBiayaPerTon($aktual_biaya_panen_sdbi_tl,$produksi_buah_sdbi_tl);

$budget_panen_per_ton_bi = calcBiayaPerTon($budget_panen_total_bi,$rab_budget_produksi_bulan_ini);
$budget_panen_per_ton_sdbl = calcBiayaPerTon($budget_panen_total_sdbl,$rab_budget_produksi_bulan_lalu);
$budget_panen_per_ton_sdbi = calcBiayaPerTon($budget_panen_total_sdbi,$rab_budget_produksi_sdbi);
$budget_panen_per_ton_setahun = calcBiayaPerTon($budget_panen_total_setahun,$rat_budget_produksi_bulan_ini);

$persen_panen_per_ton_bi = calcPercent($aktual_panen_per_ton_bi,$budget_panen_per_ton_bi);
$persen_panen_per_ton_sdbl = calcPercent($aktual_panen_per_ton_sdbl,$budget_panen_per_ton_sdbl);
$persen_panen_per_ton_sdbi = calcPercent($aktual_panen_per_ton_sdbi,$budget_panen_per_ton_sdbi);
$persen_panen_per_ton_setahun = calcPercent($aktual_panen_per_ton_sdbi,$budget_panen_per_ton_setahun);

// Biaya Angkut
$sql_biaya_angkut = "WITH base AS(
    SELECT 
        a.periode,
        a.jumlah,
        CASE
            WHEN h.kodejurnal LIKE 'INV%' THEN 'material'
            WHEN h.kodejurnal LIKE 'VHC%' THEN 'transport'
            WHEN h.kodejurnal LIKE 'SPK%' THEN 'spk'
            ELSE 'upah'
        END AS kategori
    FROM ".$dbname.".keu_jurnaldt_vw a
    LEFT JOIN ".$dbname.".keu_jurnalht h ON a.nojurnal=h.nojurnal
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE 
        a.periode >= '".$tahun_lalu.'-01'."'
        AND a.periode <= '".$periode->format('Y-m')."'
        AND ".$whereNoJurnal." ".$inplas2."
        AND a.noakun LIKE '61102%'
)

SELECT 
    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi_tl

FROM base";

// exit($sql_biaya_angkut);

$data_biaya_angkut = executeQuery($sql_biaya_angkut);

$aktual_biaya_angkut_bi = getVal($data_biaya_angkut, 'biaya_total_bi');
$aktual_biaya_angkut_sdbl = getVal($data_biaya_angkut, 'biaya_total_sdbl');
$aktual_biaya_angkut_sdbi = getVal($data_biaya_angkut, 'biaya_total_sdbi');
$aktual_biaya_angkut_sdbi_tl = getVal($data_biaya_angkut, 'biaya_total_sdbi_tl');

$aktual_angkut_material_bi = getVal($data_biaya_angkut, 'biaya_material_bi');
$aktual_angkut_material_sdbl = getVal($data_biaya_angkut, 'biaya_material_sdbl');
$aktual_angkut_material_sdbi = getVal($data_biaya_angkut, 'biaya_material_sdbi');
$aktual_angkut_material_sdbi_tl = getVal($data_biaya_angkut, 'biaya_material_sdbi_tl');

$aktual_angkut_upah_bi = getVal($data_biaya_angkut, 'biaya_upah_bi');
$aktual_angkut_upah_sdbl = getVal($data_biaya_angkut, 'biaya_upah_sdbl');
$aktual_angkut_upah_sdbi = getVal($data_biaya_angkut, 'biaya_upah_sdbi');
$aktual_angkut_upah_sdbi_tl = getVal($data_biaya_angkut, 'biaya_upah_sdbi_tl');

$aktual_angkut_transport_bi = getVal($data_biaya_angkut, 'biaya_transport_bi');
$aktual_angkut_transport_sdbl = getVal($data_biaya_angkut, 'biaya_transport_sdbl');
$aktual_angkut_transport_sdbi = getVal($data_biaya_angkut, 'biaya_transport_sdbi');
$aktual_angkut_transport_sdbi_tl = getVal($data_biaya_angkut, 'biaya_transport_sdbi_tl');

// Budget Angkut
$str="WITH base AS (
        SELECT
            a.tahunbudget,
            a.rupiah,
            a.rp".$periode->format('m')." as rpbi,
            ".$addstrsdbl." as rpsdbl,
            ".$addstrsdbi." as rpsdbi,
        CASE 
            WHEN a.kodebudget = 'SUPERVISI' OR substr(a.kodebudget,1,3)='SDM' THEN 'upah'
            WHEN substr(a.kodebudget,1,3) = 'VHC' THEN 'transport'
            WHEN a.kodebudget = 'TOOL' OR substr(a.kodebudget,1,2)='M-' THEN 'material'
        END AS kategori
    FROM ".$dbname.".bgt_budget_detail a
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE a.tahunbudget IN ('".$tahun."','".$tahun_lalu."')
        AND a.kodeorg IN (".$unitList.")
        ".$inplas2."
        AND a.noakun LIKE '61102%'
)

SELECT
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpbi ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbl ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbi ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rupiah ELSE 0 END) AS biaya_material_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpbi ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbl ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbi ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rupiah ELSE 0 END) AS biaya_upah_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpbi ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbl ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbi ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rupiah ELSE 0 END) AS biaya_transport_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpbi ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpsdbl ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpsdbi ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rupiah ELSE 0 END) AS biaya_total_setahun
    
FROM base";

// exit($str);

$data_budget_angkut = executeQuery($str);
$budget_angkut_material_bi = getVal($data_budget_angkut, 'biaya_material_bi');
$budget_angkut_material_sdbl = getVal($data_budget_angkut, 'biaya_material_sdbl');
$budget_angkut_material_sdbi = getVal($data_budget_angkut, 'biaya_material_sdbi');
$budget_angkut_material_setahun = getVal($data_budget_angkut, 'biaya_material_setahun');

$budget_angkut_upah_bi = getVal($data_budget_angkut, 'biaya_upah_bi');
$budget_angkut_upah_sdbl = getVal($data_budget_angkut, 'biaya_upah_sdbl');
$budget_angkut_upah_sdbi = getVal($data_budget_angkut, 'biaya_upah_sdbi');
$budget_angkut_upah_setahun = getVal($data_budget_angkut, 'biaya_upah_setahun');

$budget_angkut_transport_bi = getVal($data_budget_angkut, 'biaya_transport_bi');
$budget_angkut_transport_sdbl = getVal($data_budget_angkut, 'biaya_transport_sdbl');
$budget_angkut_transport_sdbi = getVal($data_budget_angkut, 'biaya_transport_sdbi');
$budget_angkut_transport_setahun = getVal($data_budget_angkut, 'biaya_transport_setahun');

$budget_angkut_total_bi = getVal($data_budget_angkut, 'biaya_total_bi');
$budget_angkut_total_sdbl = getVal($data_budget_angkut, 'biaya_total_sdbl');
$budget_angkut_total_sdbi = getVal($data_budget_angkut, 'biaya_total_sdbi');
$budget_angkut_total_setahun = getVal($data_budget_angkut, 'biaya_total_setahun');   

$persen_angkut_material_bi = calcPercent($aktual_angkut_material_bi,$budget_angkut_material_bi);
$persen_angkut_material_sdbl = calcPercent($aktual_angkut_material_sdbl,$budget_angkut_material_sdbl);
$persen_angkut_material_sdbi = calcPercent($aktual_angkut_material_sdbi,$budget_angkut_material_sdbi);
$persen_angkut_material_setahun = calcPercent($aktual_angkut_material_sdbi,$budget_angkut_material_setahun);

$persen_angkut_upah_bi = calcPercent($aktual_angkut_upah_bi,$budget_angkut_upah_bi);
$persen_angkut_upah_sdbl = calcPercent($aktual_angkut_upah_sdbl,$budget_angkut_upah_sdbl);
$persen_angkut_upah_sdbi = calcPercent($aktual_angkut_upah_sdbi,$budget_angkut_upah_sdbi);
$persen_angkut_upah_setahun = calcPercent($aktual_angkut_upah_sdbi,$budget_angkut_upah_setahun);

$persen_angkut_transport_bi = calcPercent($aktual_angkut_transport_bi,$budget_angkut_transport_bi);
$persen_angkut_transport_sdbl = calcPercent($aktual_angkut_transport_sdbl,$budget_angkut_transport_sdbl);
$persen_angkut_transport_sdbi = calcPercent($aktual_angkut_transport_sdbi,$budget_angkut_transport_sdbi);
$persen_angkut_transport_setahun = calcPercent($aktual_angkut_transport_sdbi,$budget_angkut_transport_setahun);

$persen_angkut_total_bi = calcPercent($aktual_biaya_angkut_bi,$budget_angkut_total_bi);
$persen_angkut_total_sdbl = calcPercent($aktual_biaya_angkut_sdbl,$budget_angkut_total_sdbl);
$persen_angkut_total_sdbi = calcPercent($aktual_biaya_angkut_sdbi,$budget_angkut_total_sdbi);
$persen_angkut_total_setahun = calcPercent($aktual_biaya_angkut_sdbi,$budget_angkut_total_setahun);

$aktual_angkut_per_ton_bi = calcBiayaPerTon($aktual_biaya_angkut_bi,$produksi_buah_bulan_ini);
$aktual_angkut_per_ton_sdbl = calcBiayaPerTon($aktual_biaya_angkut_sdbl,$produksi_buah_bulan_lalu);
$aktual_angkut_per_ton_sdbi = calcBiayaPerTon($aktual_biaya_angkut_sdbi,$produksi_buah_sdbi);
$aktual_angkut_per_ton_sdbi_tl = calcBiayaPerTon($aktual_biaya_angkut_sdbi_tl,$produksi_buah_sdbi_tl);

$budget_angkut_per_ton_bi = calcBiayaPerTon($budget_angkut_total_bi,$rab_budget_produksi_bulan_ini);
$budget_angkut_per_ton_sdbl = calcBiayaPerTon($budget_angkut_total_sdbl,$rab_budget_produksi_bulan_lalu);
$budget_angkut_per_ton_sdbi = calcBiayaPerTon($budget_angkut_total_sdbi,$rab_budget_produksi_sdbi);
$budget_angkut_per_ton_setahun = calcBiayaPerTon($budget_angkut_total_setahun,$rat_budget_produksi_bulan_ini);

$persen_angkut_per_ton_bi = calcPercent($aktual_angkut_per_ton_bi,$budget_angkut_per_ton_bi);
$persen_angkut_per_ton_sdbl = calcPercent($aktual_angkut_per_ton_sdbl,$budget_angkut_per_ton_sdbl);
$persen_angkut_per_ton_sdbi = calcPercent($aktual_angkut_per_ton_sdbi,$budget_angkut_per_ton_sdbi);
$persen_angkut_per_ton_setahun = calcPercent($aktual_angkut_per_ton_sdbi,$budget_angkut_per_ton_setahun);

// Biaya Sosial
$sql_biaya_sosial = "WITH base AS(
    SELECT 
        a.periode,
        a.jumlah,
        CASE
            WHEN h.kodejurnal LIKE 'INV%' THEN 'material'
            WHEN h.kodejurnal LIKE 'VHC%' THEN 'transport'
            WHEN h.kodejurnal LIKE 'SPK%' THEN 'spk'
            ELSE 'upah'
        END AS kategori
    FROM ".$dbname.".keu_jurnaldt_vw a
    LEFT JOIN ".$dbname.".keu_jurnalht h ON a.nojurnal=h.nojurnal
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE 
        a.periode >= '".$tahun_lalu.'-01'."'
        AND a.periode <= '".$periode->format('Y-m')."'
        AND ".$whereNoJurnal." ".$inplas2."
        AND (
            a.noakun LIKE '7120800%' 
            OR a.noakun LIKE '71308%'
            OR a.noakun LIKE '7130804%'
            OR a.noakun LIKE '7130805%'
        )
)

SELECT 
    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi_tl

FROM base";

// exit($sql_biaya_sosial);

$data_biaya_sosial = executeQuery($sql_biaya_sosial);

$aktual_biaya_sosial_bi = getVal($data_biaya_sosial, 'biaya_total_bi');
$aktual_biaya_sosial_sdbl = getVal($data_biaya_sosial, 'biaya_total_sdbl');
$aktual_biaya_sosial_sdbi = getVal($data_biaya_sosial, 'biaya_total_sdbi');
$aktual_biaya_sosial_sdbi_tl = getVal($data_biaya_sosial, 'biaya_total_sdbi_tl');

$aktual_sosial_material_bi = getVal($data_biaya_sosial, 'biaya_material_bi');
$aktual_sosial_material_sdbl = getVal($data_biaya_sosial, 'biaya_material_sdbl');
$aktual_sosial_material_sdbi = getVal($data_biaya_sosial, 'biaya_material_sdbi');
$aktual_sosial_material_sdbi_tl = getVal($data_biaya_sosial, 'biaya_material_sdbi_tl');

$aktual_sosial_upah_bi = getVal($data_biaya_sosial, 'biaya_upah_bi');
$aktual_sosial_upah_sdbl = getVal($data_biaya_sosial, 'biaya_upah_sdbl');
$aktual_sosial_upah_sdbi = getVal($data_biaya_sosial, 'biaya_upah_sdbi');
$aktual_sosial_upah_sdbi_tl = getVal($data_biaya_sosial, 'biaya_upah_sdbi_tl');

$aktual_sosial_transport_bi = getVal($data_biaya_sosial, 'biaya_transport_bi');
$aktual_sosial_transport_sdbl = getVal($data_biaya_sosial, 'biaya_transport_sdbl');
$aktual_sosial_transport_sdbi = getVal($data_biaya_sosial, 'biaya_transport_sdbi');
$aktual_sosial_transport_sdbi_tl = getVal($data_biaya_sosial, 'biaya_transport_sdbi_tl');

// Budget Sosial
$str="WITH base AS (
        SELECT
            a.tahunbudget,
            a.rupiah,
            a.rp".$periode->format('m')." as rpbi,
            ".$addstrsdbl." as rpsdbl,
            ".$addstrsdbi." as rpsdbi,
        CASE 
            WHEN a.kodebudget = 'SUPERVISI' OR substr(a.kodebudget,1,3)='SDM' THEN 'upah'
            WHEN substr(a.kodebudget,1,3) = 'VHC' THEN 'transport'
            WHEN a.kodebudget = 'TOOL' OR substr(a.kodebudget,1,2)='M-' THEN 'material'
        END AS kategori
    FROM ".$dbname.".bgt_budget_detail a
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE a.tahunbudget IN ('".$tahun."','".$tahun_lalu."')
        AND a.kodeorg IN (".$unitList.")
        ".$inplas2."
        AND (
            a.noakun LIKE '7120800%' 
            OR a.noakun LIKE '71308%'
            OR a.noakun LIKE '7130804%'
            OR a.noakun LIKE '7130805%'
        )
)

SELECT
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpbi ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbl ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbi ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rupiah ELSE 0 END) AS biaya_material_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpbi ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbl ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbi ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rupiah ELSE 0 END) AS biaya_upah_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpbi ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbl ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbi ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rupiah ELSE 0 END) AS biaya_transport_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpbi ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpsdbl ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpsdbi ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rupiah ELSE 0 END) AS biaya_total_setahun
    
FROM base";

// exit($str);

$data_budget_sosial = executeQuery($str);
$budget_sosial_material_bi = getVal($data_budget_sosial, 'biaya_material_bi');
$budget_sosial_material_sdbl = getVal($data_budget_sosial, 'biaya_material_sdbl');
$budget_sosial_material_sdbi = getVal($data_budget_sosial, 'biaya_material_sdbi');
$budget_sosial_material_setahun = getVal($data_budget_sosial, 'biaya_material_setahun');

$budget_sosial_upah_bi = getVal($data_budget_sosial, 'biaya_upah_bi');
$budget_sosial_upah_sdbl = getVal($data_budget_sosial, 'biaya_upah_sdbl');
$budget_sosial_upah_sdbi = getVal($data_budget_sosial, 'biaya_upah_sdbi');
$budget_sosial_upah_setahun = getVal($data_budget_sosial, 'biaya_upah_setahun');

$budget_sosial_transport_bi = getVal($data_budget_sosial, 'biaya_transport_bi');
$budget_sosial_transport_sdbl = getVal($data_budget_sosial, 'biaya_transport_sdbl');
$budget_sosial_transport_sdbi = getVal($data_budget_sosial, 'biaya_transport_sdbi');
$budget_sosial_transport_setahun = getVal($data_budget_sosial, 'biaya_transport_setahun');

$budget_sosial_total_bi = getVal($data_budget_sosial, 'biaya_total_bi');
$budget_sosial_total_sdbl = getVal($data_budget_sosial, 'biaya_total_sdbl');
$budget_sosial_total_sdbi = getVal($data_budget_sosial, 'biaya_total_sdbi');
$budget_sosial_total_setahun = getVal($data_budget_sosial, 'biaya_total_setahun');   

$persen_sosial_material_bi = calcPercent($aktual_sosial_material_bi,$budget_sosial_material_bi);
$persen_sosial_material_sdbl = calcPercent($aktual_sosial_material_sdbl,$budget_sosial_material_sdbl);
$persen_sosial_material_sdbi = calcPercent($aktual_sosial_material_sdbi,$budget_sosial_material_sdbi);
$persen_sosial_material_setahun = calcPercent($aktual_sosial_material_sdbi,$budget_sosial_material_setahun);

$persen_sosial_upah_bi = calcPercent($aktual_sosial_upah_bi,$budget_sosial_upah_bi);
$persen_sosial_upah_sdbl = calcPercent($aktual_sosial_upah_sdbl,$budget_sosial_upah_sdbl);
$persen_sosial_upah_sdbi = calcPercent($aktual_sosial_upah_sdbi,$budget_sosial_upah_sdbi);
$persen_sosial_upah_setahun = calcPercent($aktual_sosial_upah_sdbi,$budget_sosial_upah_setahun);

$persen_sosial_transport_bi = calcPercent($aktual_sosial_transport_bi,$budget_sosial_transport_bi);
$persen_sosial_transport_sdbl = calcPercent($aktual_sosial_transport_sdbl,$budget_sosial_transport_sdbl);
$persen_sosial_transport_sdbi = calcPercent($aktual_sosial_transport_sdbi,$budget_sosial_transport_sdbi);
$persen_sosial_transport_setahun = calcPercent($aktual_sosial_transport_sdbi,$budget_sosial_transport_setahun);

$persen_sosial_total_bi = calcPercent($aktual_biaya_sosial_bi,$budget_sosial_total_bi);
$persen_sosial_total_sdbl = calcPercent($aktual_biaya_sosial_sdbl,$budget_sosial_total_sdbl);
$persen_sosial_total_sdbi = calcPercent($aktual_biaya_sosial_sdbi,$budget_sosial_total_sdbi);
$persen_sosial_total_setahun = calcPercent($aktual_biaya_sosial_sdbi,$budget_sosial_total_setahun);

$aktual_sosial_per_ha_bi = calcBiayaPerHa($aktual_biaya_sosial_bi,$luas_tm_bulan_ini);
$aktual_sosial_per_ha_sdbl = calcBiayaPerHa($aktual_biaya_sosial_sdbl,$luas_tm_bulan_lalu);
$aktual_sosial_per_ha_sdbi = calcBiayaPerHa($aktual_biaya_sosial_sdbi,$luas_tm_bulan_ini);
$aktual_sosial_per_ha_sdbi_tl = calcBiayaPerHa($aktual_biaya_sosial_sdbi_tl,$luas_tm_sdbi_tahun_lalu);

$budget_sosial_per_ha_bi = calcBiayaPerHa($budget_sosial_total_bi,$budget_luas_tm_bulan_ini);
$budget_sosial_per_ha_sdbl = calcBiayaPerHa($budget_sosial_total_sdbl,$budget_luas_tm_bulan_lalu);
$budget_sosial_per_ha_sdbi = calcBiayaPerHa($budget_sosial_total_sdbi,$budget_luas_tm_bulan_ini);
$budget_sosial_per_ha_setahun = calcBiayaPerHa($budget_sosial_total_setahun,$budget_luas_tm_bulan_ini);

$persen_sosial_per_ha_bi = calcPercent($aktual_sosial_per_ha_bi,$budget_sosial_per_ha_bi);
$persen_sosial_per_ha_sdbl = calcPercent($aktual_sosial_per_ha_sdbl,$budget_sosial_per_ha_sdbl);
$persen_sosial_per_ha_sdbi = calcPercent($aktual_sosial_per_ha_sdbi,$budget_sosial_per_ha_sdbi);
$persen_sosial_per_ha_setahun = calcPercent($aktual_sosial_per_ha_sdbi,$budget_sosial_per_ha_setahun);

// Biaya Investasi
$sql_biaya_investasi = "WITH base AS(
    SELECT 
        a.periode,
        a.jumlah,
        CASE
            WHEN h.kodejurnal LIKE 'INV%' THEN 'material'
            WHEN h.kodejurnal LIKE 'VHC%' THEN 'transport'
            WHEN h.kodejurnal LIKE 'SPK%' THEN 'spk'
            ELSE 'upah'
        END AS kategori
    FROM ".$dbname.".keu_jurnaldt_vw a
    LEFT JOIN ".$dbname.".keu_jurnalht h ON a.nojurnal=h.nojurnal
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE 
        a.periode >= '".$tahun_lalu.'-01'."'
        AND a.periode <= '".$periode->format('Y-m')."'
        AND ".$whereNoJurnal." ".$inplas2."
        AND a.noakun LIKE '12903%'
)

SELECT 
    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'material' THEN jumlah ELSE 0 END) AS biaya_material_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'upah' THEN jumlah ELSE 0 END) AS biaya_upah_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori = 'transport' THEN jumlah ELSE 0 END) AS biaya_transport_sdbi_tl,

    SUM(CASE WHEN periode = '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$date_bulan_lalu->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN periode BETWEEN '".$periode->format('Y-01')."' AND '".$periode->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN periode BETWEEN '".$tahun_lalu.'-01'."' AND '".$date_tahun_lalu_bulan_selanjutnya->format('Y-m')."' AND kategori != 'spk' THEN jumlah ELSE 0 END) AS biaya_total_sdbi_tl

FROM base";

// exit($sql_biaya_investasi);

$data_biaya_investasi = executeQuery($sql_biaya_investasi);

$aktual_biaya_investasi_bi = getVal($data_biaya_investasi, 'biaya_total_bi');
$aktual_biaya_investasi_sdbl = getVal($data_biaya_investasi, 'biaya_total_sdbl');
$aktual_biaya_investasi_sdbi = getVal($data_biaya_investasi, 'biaya_total_sdbi');
$aktual_biaya_investasi_sdbi_tl = getVal($data_biaya_investasi, 'biaya_total_sdbi_tl');

$aktual_investasi_material_bi = getVal($data_biaya_investasi, 'biaya_material_bi');
$aktual_investasi_material_sdbl = getVal($data_biaya_investasi, 'biaya_material_sdbl');
$aktual_investasi_material_sdbi = getVal($data_biaya_investasi, 'biaya_material_sdbi');
$aktual_investasi_material_sdbi_tl = getVal($data_biaya_investasi, 'biaya_material_sdbi_tl');

$aktual_investasi_upah_bi = getVal($data_biaya_investasi, 'biaya_upah_bi');
$aktual_investasi_upah_sdbl = getVal($data_biaya_investasi, 'biaya_upah_sdbl');
$aktual_investasi_upah_sdbi = getVal($data_biaya_investasi, 'biaya_upah_sdbi');
$aktual_investasi_upah_sdbi_tl = getVal($data_biaya_investasi, 'biaya_upah_sdbi_tl');

$aktual_investasi_transport_bi = getVal($data_biaya_investasi, 'biaya_transport_bi');
$aktual_investasi_transport_sdbl = getVal($data_biaya_investasi, 'biaya_transport_sdbl');
$aktual_investasi_transport_sdbi = getVal($data_biaya_investasi, 'biaya_transport_sdbi');
$aktual_investasi_transport_sdbi_tl = getVal($data_biaya_investasi, 'biaya_transport_sdbi_tl');

// Budget Investasi
$str="WITH base AS (
        SELECT
            a.tahunbudget,
            a.rupiah,
            a.rp".$periode->format('m')." as rpbi,
            ".$addstrsdbl." as rpsdbl,
            ".$addstrsdbi." as rpsdbi,
        CASE 
            WHEN a.kodebudget = 'SUPERVISI' OR substr(a.kodebudget,1,3)='SDM' THEN 'upah'
            WHEN substr(a.kodebudget,1,3) = 'VHC' THEN 'transport'
            WHEN a.kodebudget = 'TOOL' OR substr(a.kodebudget,1,2)='M-' THEN 'material'
        END AS kategori
    FROM ".$dbname.".bgt_budget_detail a
    LEFT JOIN ".$dbname.".organisasi b ON a.kodeorg=b.kodeorganisasi
    WHERE a.tahunbudget IN ('".$tahun."','".$tahun_lalu."')
        AND a.kodeorg IN (".$unitList.")
        ".$inplas2."
        AND a.noakun LIKE '12903%'
)

SELECT
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpbi ELSE 0 END) AS biaya_material_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbl ELSE 0 END) AS biaya_material_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rpsdbi ELSE 0 END) AS biaya_material_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'material' THEN rupiah ELSE 0 END) AS biaya_material_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpbi ELSE 0 END) AS biaya_upah_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbl ELSE 0 END) AS biaya_upah_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rpsdbi ELSE 0 END) AS biaya_upah_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'upah' THEN rupiah ELSE 0 END) AS biaya_upah_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpbi ELSE 0 END) AS biaya_transport_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbl ELSE 0 END) AS biaya_transport_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rpsdbi ELSE 0 END) AS biaya_transport_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori = 'transport' THEN rupiah ELSE 0 END) AS biaya_transport_setahun,
    
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpbi ELSE 0 END) AS biaya_total_bi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpsdbl ELSE 0 END) AS biaya_total_sdbl,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rpsdbi ELSE 0 END) AS biaya_total_sdbi,
    SUM(CASE WHEN tahunbudget = '".$tahun."' AND kategori IN ('material','upah','transport') THEN rupiah ELSE 0 END) AS biaya_total_setahun
    
FROM base";

// exit($str);

$data_budget_investasi = executeQuery($str);
$budget_investasi_material_bi = getVal($data_budget_investasi, 'biaya_material_bi');
$budget_investasi_material_sdbl = getVal($data_budget_investasi, 'biaya_material_sdbl');
$budget_investasi_material_sdbi = getVal($data_budget_investasi, 'biaya_material_sdbi');
$budget_investasi_material_setahun = getVal($data_budget_investasi, 'biaya_material_setahun');

$budget_investasi_upah_bi = getVal($data_budget_investasi, 'biaya_upah_bi');
$budget_investasi_upah_sdbl = getVal($data_budget_investasi, 'biaya_upah_sdbl');
$budget_investasi_upah_sdbi = getVal($data_budget_investasi, 'biaya_upah_sdbi');
$budget_investasi_upah_setahun = getVal($data_budget_investasi, 'biaya_upah_setahun');

$budget_investasi_transport_bi = getVal($data_budget_investasi, 'biaya_transport_bi');
$budget_investasi_transport_sdbl = getVal($data_budget_investasi, 'biaya_transport_sdbl');
$budget_investasi_transport_sdbi = getVal($data_budget_investasi, 'biaya_transport_sdbi');
$budget_investasi_transport_setahun = getVal($data_budget_investasi, 'biaya_transport_setahun');

$budget_investasi_total_bi = getVal($data_budget_investasi, 'biaya_total_bi');
$budget_investasi_total_sdbl = getVal($data_budget_investasi, 'biaya_total_sdbl');
$budget_investasi_total_sdbi = getVal($data_budget_investasi, 'biaya_total_sdbi');
$budget_investasi_total_setahun = getVal($data_budget_investasi, 'biaya_total_setahun');   

$persen_investasi_material_bi = calcPercent($aktual_investasi_material_bi,$budget_investasi_material_bi);
$persen_investasi_material_sdbl = calcPercent($aktual_investasi_material_sdbl,$budget_investasi_material_sdbl);
$persen_investasi_material_sdbi = calcPercent($aktual_investasi_material_sdbi,$budget_investasi_material_sdbi);
$persen_investasi_material_setahun = calcPercent($aktual_investasi_material_sdbi,$budget_investasi_material_setahun);

$persen_investasi_upah_bi = calcPercent($aktual_investasi_upah_bi,$budget_investasi_upah_bi);
$persen_investasi_upah_sdbl = calcPercent($aktual_investasi_upah_sdbl,$budget_investasi_upah_sdbl);
$persen_investasi_upah_sdbi = calcPercent($aktual_investasi_upah_sdbi,$budget_investasi_upah_sdbi);
$persen_investasi_upah_setahun = calcPercent($aktual_investasi_upah_sdbi,$budget_investasi_upah_setahun);

$persen_investasi_transport_bi = calcPercent($aktual_investasi_transport_bi,$budget_investasi_transport_bi);
$persen_investasi_transport_sdbl = calcPercent($aktual_investasi_transport_sdbl,$budget_investasi_transport_sdbl);
$persen_investasi_transport_sdbi = calcPercent($aktual_investasi_transport_sdbi,$budget_investasi_transport_sdbi);
$persen_investasi_transport_setahun = calcPercent($aktual_investasi_transport_sdbi,$budget_investasi_transport_setahun);

$persen_investasi_total_bi = calcPercent($aktual_biaya_investasi_bi,$budget_investasi_total_bi);
$persen_investasi_total_sdbl = calcPercent($aktual_biaya_investasi_sdbl,$budget_investasi_total_sdbl);
$persen_investasi_total_sdbi = calcPercent($aktual_biaya_investasi_sdbi,$budget_investasi_total_sdbi);
$persen_investasi_total_setahun = calcPercent($aktual_biaya_investasi_sdbi,$budget_investasi_total_setahun);

$aktual_investasi_per_ha_bi = calcBiayaPerHa($aktual_biaya_investasi_bi,$luas_tm_bulan_ini);
$aktual_investasi_per_ha_sdbl = calcBiayaPerHa($aktual_biaya_investasi_sdbl,$luas_tm_bulan_lalu);
$aktual_investasi_per_ha_sdbi = calcBiayaPerHa($aktual_biaya_investasi_sdbi,$luas_tm_bulan_ini);
$aktual_investasi_per_ha_sdbi_tl = calcBiayaPerHa($aktual_biaya_investasi_sdbi_tl,$luas_tm_sdbi_tahun_lalu);

$budget_investasi_per_ha_bi = calcBiayaPerHa($budget_investasi_total_bi,$budget_luas_tm_bulan_ini);
$budget_investasi_per_ha_sdbl = calcBiayaPerHa($budget_investasi_total_sdbl,$budget_luas_tm_bulan_lalu);
$budget_investasi_per_ha_sdbi = calcBiayaPerHa($budget_investasi_total_sdbi,$budget_luas_tm_bulan_ini);
$budget_investasi_per_ha_setahun = calcBiayaPerHa($budget_investasi_total_setahun,$budget_luas_tm_bulan_ini);

$persen_investasi_per_ha_bi = calcPercent($aktual_investasi_per_ha_bi,$budget_investasi_per_ha_bi);
$persen_investasi_per_ha_sdbl = calcPercent($aktual_investasi_per_ha_sdbl,$budget_investasi_per_ha_sdbl);
$persen_investasi_per_ha_sdbi = calcPercent($aktual_investasi_per_ha_sdbi,$budget_investasi_per_ha_sdbi);
$persen_investasi_per_ha_setahun = calcPercent($aktual_investasi_per_ha_sdbi,$budget_investasi_per_ha_setahun);

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

$tabelHtml = "";

// Table header untuk excel
if ($proses == 'excel') {
    $tabelHtml .= "<thead>";
    $tabelHtml .= "<tr><td colspan=\"17\">".strtoupper("Analisis Biaya Kebun ".$kode_org." ".$periode->format('Y-m'))."</td></tr>";
    $tabelHtml .= "</thead>";
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
                        <th rowspan=\"2\" colspan=\"1\" {$bgHeader}>Anggaran ".$periode->format('Y')."</th>
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

$tabelHtml .= "<table cellspacing=\"3\" cellpadding=\"5\" border=\"".$border."\" class=\"sortable\">";
$tabelHtml .= $headerTabel;
$tabelHtml .= "<tbody>";

// ============================================================================
// BUILD TABLE CONTENT - SECTION: HEKTAR
// ============================================================================

$tabelHtml .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
                    <td>Hektar</td>
                </tr>";

$tabelHtml .= buildTableRowWithUnit(
    'Tanaman Menghasilkan',
    'Hektar',
    [
        'aktual_bulan_ini' => $luas_tm_bulan_ini,
        'rat_bulan_ini' => $budget_luas_tm_bulan_ini,
        'rab_bulan_ini' => $budget_luas_tm_bulan_ini,
        'persen_bulan_ini' => $persenBulanIni.'%',
        'aktual_bulan_lalu' => $luas_tm_bulan_lalu,
        'rat_bulan_lalu' => $budget_luas_tm_bulan_lalu,
        'rab_bulan_lalu' => $budget_luas_tm_bulan_lalu,
        'persen_bulan_lalu' => $persenBulanLalu.'%',
        'aktual_bulan_sdbi' => $luas_tm_bulan_ini,
        'rat_bulan_sdbi' => $budget_luas_tm_bulan_ini,
        'rab_bulan_sdbi' => $budget_luas_tm_bulan_ini,
        'persen_bulan_sdbi' => $persenBulanIni.'%',
        'sdbi_tahun_lalu' => $luas_tm_sdbi_tahun_lalu,
        'anggaran_tahun_ini' => $budget_luas_tm_bulan_ini,
        'persen' => $persen.'%'
    ],
    'rowcontent'
);

$tabelHtml .= buildTableRowWithUnit(
    'Total Luasan Tertanam Inti',
    'Hektar',
    [
        'aktual_bulan_ini' => $luas_tm_bulan_ini,
        'rat_bulan_ini' => $budget_luas_tm_bulan_ini,
        'rab_bulan_ini' => $budget_luas_tm_bulan_ini,
        'persen_bulan_ini' => $persenBulanIni.'%',
        'aktual_bulan_lalu' => $luas_tm_bulan_lalu,
        'rat_bulan_lalu' => $budget_luas_tm_bulan_lalu,
        'rab_bulan_lalu' => $budget_luas_tm_bulan_lalu,
        'persen_bulan_lalu' => $persenBulanLalu.'%',
        'aktual_bulan_sdbi' => $luas_tm_bulan_ini,
        'rat_bulan_sdbi' => $budget_luas_tm_bulan_ini,
        'rab_bulan_sdbi' => $budget_luas_tm_bulan_ini,
        'persen_bulan_sdbi' => $persenBulanIni.'%',
        'sdbi_tahun_lalu' => $luas_tm_sdbi_tahun_lalu,
        'anggaran_tahun_ini' => $luas_tm_bulan_ini,
        'persen' => $persen.'%'
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

// ============================================================================
// BUILD TABLE CONTENT - SECTION: PRODUKSI BUAH
// ============================================================================

$tabelHtml .= "<tr style=\"height:10px;\"></tr>";

$tabelHtml .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
                    <td>Produksi Buah (dalam Ton)</td>
                </tr>";

$tabelHtml .= buildTableRowWithUnit(
    'Buah Inti',
    'Ton',
    [
        'aktual_bulan_ini' => $produksi_buah_bulan_ini, 
        'rat_bulan_ini' => $rat_budget_produksi_bulan_ini, 
        'rab_bulan_ini' => $rab_budget_produksi_bulan_ini, 
        'persen_bulan_ini' => $persenBuahBulanIni.'%',
        'aktual_bulan_lalu' => $produksi_buah_bulan_lalu, 
        'rat_bulan_lalu' => $rat_budget_produksi_bulan_lalu, 
        'rab_bulan_lalu' => $rab_budget_produksi_bulan_lalu, 
        'persen_bulan_lalu' => $persenBuahBulanLalu.'%',
        'aktual_bulan_sdbi' => $produksi_buah_sdbi, 
        'rat_bulan_sdbi' => $rat_budget_produksi_sdbi, 
        'rab_bulan_sdbi' => $rab_budget_produksi_sdbi, 
        'persen_bulan_sdbi' => $persenBuahPer1toPer2.'%',
        'sdbi_tahun_lalu' => $produksi_buah_sdbi_tl, 
        'anggaran_tahun_ini' => $rat_budget_produksi_bulan_ini, 
        'persen' => $persen_buah_inti.'%'
    ],
    'rowcontent'
);

$tabelHtml .= buildTableRowWithUnit(
    'Total TBS',
    'Ton',
    [
        'aktual_bulan_ini' => $produksi_buah_bulan_ini, 
        'rat_bulan_ini' => $rat_budget_produksi_bulan_ini, 
        'rab_bulan_ini' => $rab_budget_produksi_bulan_ini, 
        'persen_bulan_ini' => $persenBuahBulanIni.'%',
        'aktual_bulan_lalu' => $produksi_buah_bulan_lalu, 
        'rat_bulan_lalu' => $rat_budget_produksi_bulan_lalu, 
        'rab_bulan_lalu' => $rab_budget_produksi_bulan_lalu, 
        'persen_bulan_lalu' => $persenBuahBulanLalu.'%',
        'aktual_bulan_sdbi' => $produksi_buah_sdbi, 
        'rat_bulan_sdbi' => $rat_budget_produksi_sdbi, 
        'rab_bulan_sdbi' => $rab_budget_produksi_sdbi, 
        'persen_bulan_sdbi' => $persenBuahPer1toPer2.'%',
        'sdbi_tahun_lalu' => $produksi_buah_sdbi_tl, 
        'anggaran_tahun_ini' => $rat_budget_produksi_bulan_ini, 
        'persen' => $persen_buah_inti.'%'
    ],
    'rowcontent',
    'font-weight:bold;'
);

$tabelHtml .= buildTableRowWithUnit(
    'Ton/Ha TBS',
    'Ton/Ha',
    [
        'aktual_bulan_ini' => $aktual_ton_ha_bulan_ini, 
        'rat_bulan_ini' => $rat_budget_produksi_bulan_ini, 
        'rab_bulan_ini' => $rab_budget_produksi_bulan_ini, 
        'persen_bulan_ini' => $persenBuahBulanIni.'%',
        'aktual_bulan_lalu' => $aktual_ton_ha_bulan_lalu, 
        'rat_bulan_lalu' => $rat_budget_produksi_bulan_lalu, 
        'rab_bulan_lalu' => $rab_budget_produksi_bulan_lalu, 
        'persen_bulan_lalu' => $persenBuahBulanLalu.'%',
        'aktual_bulan_sdbi' => $aktual_ton_ha_sdbi, 
        'rat_bulan_sdbi' => $rat_budget_produksi_sdbi, 
        'rab_bulan_sdbi' => $rab_budget_produksi_sdbi, 
        'persen_bulan_sdbi' => $persenBuahPer1toPer2.'%',
        'sdbi_tahun_lalu' => $aktual_ton_ha_sdbi_tl, 
        'anggaran_tahun_ini' => $rat_budget_produksi_bulan_ini, 
        'persen' => $persen_buah_inti.'%'
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

// ============================================================================
// BUILD TABLE CONTENT - SECTION: BIAYA UMUM
// ============================================================================

$tabelHtml .= "<tr style=\"height:10px;\"></tr>";

$tabelHtml .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
                    <td>Biaya Langsung:</td>
                </tr>";

$tabelHtml .= buildTableRowWithUnit(
    'Biaya Umum',
    'Rp',
    [
        'aktual_bulan_ini' => $biaya_umum_bulan_ini, 
        'rat_bulan_ini' => $budget_biaya_umum_bulan_ini, 
        'rab_bulan_ini' => $budget_biaya_umum_bulan_ini, 
        'persen_bulan_ini' => $persen_biaya_umum_per_ha_bulan_ini.'%',
        'aktual_bulan_lalu' => $biaya_umum_bulan_lalu, 
        'rat_bulan_lalu' => $budget_biaya_umum_bulan_lalu, 
        'rab_bulan_lalu' => $budget_biaya_umum_bulan_lalu, 
        'persen_bulan_lalu' => $persen_biaya_umum_per_ha_bulan_lalu.'%',
        'aktual_bulan_sdbi' => $biaya_umum_sdbi, 
        'rat_bulan_sdbi' => $budget_biaya_umum_sdbi, 
        'rab_bulan_sdbi' => $budget_biaya_umum_sdbi, 
        'persen_bulan_sdbi' => $persen_biaya_umum_per_ha_sdbi.'%',
        'sdbi_tahun_lalu' => $biaya_umum_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_tahun_ini, 
        'persen' => $persen_biaya_umum.'%'
    ],
    'rowcontent',
    'font-weight:bold;'
);

$tabelHtml .= buildTableRowWithUnit(
    'Biaya per Hektar',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_biaya_umum_per_ha_bulan_ini, 
        'rat_bulan_ini' => $rat_biaya_umum_per_ha_bulan_ini, 
        'rab_bulan_ini' => $rab_biaya_umum_per_ha_bulan_ini, 
        'persen_bulan_ini' => $persen_biaya_umum_per_ha_bulan_ini.'%',
        'aktual_bulan_lalu' => $aktual_biaya_umum_per_ha_bulan_lalu, 
        'rat_bulan_lalu' => $rat_biaya_umum_per_ha_bulan_lalu, 
        'rab_bulan_lalu' => $rab_biaya_umum_per_ha_bulan_lalu, 
        'persen_bulan_lalu' => $persen_biaya_umum_per_ha_bulan_lalu.'%',
        'aktual_bulan_sdbi' => $aktual_biaya_umum_per_ha_sdbi, 
        'rat_bulan_sdbi' => $rat_biaya_umum_per_ha_sdbi, 
        'rab_bulan_sdbi' => $rab_biaya_umum_per_ha_sdbi, 
        'persen_bulan_sdbi' => $persen_biaya_umum_per_ha_sdbi.'%',
        'sdbi_tahun_lalu' => $biaya_umum_per_ha_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_biaya_umum_per_ha_tahun_ini, 
        'persen' => $persen_biaya_umum_per_ha.'%'
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

$tabelHtml .= "<tr style=\"height:10px;\"></tr>";



// Material, Upah, Transport - biaya per ton
$costItems = [
    ['Material', ''],
    ['Upah', ''],
    ['Transport', '']
];

// ============================================================================
// BUILD TABLE CONTENT - SECTION: BIAYA PERAWATAN
// ============================================================================

$tabelHtml .= buildTableRowWithUnit(
    'Biaya Perawatan',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_biaya_perawatan_bi, 
        'rat_bulan_ini' => $budget_perawatan_total_bi, 
        'rab_bulan_ini' => $budget_perawatan_total_bi, 
        'persen_bulan_ini' => $persen_perawatan_total_bi."%",
        'aktual_bulan_lalu' => $aktual_biaya_perawatan_sdbl, 
        'rat_bulan_lalu' => $budget_perawatan_total_sdbl, 
        'rab_bulan_lalu' => $budget_perawatan_total_sdbl, 
        'persen_bulan_lalu' => $persen_perawatan_total_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_biaya_perawatan_sdbi, 
        'rat_bulan_sdbi' => $budget_perawatan_total_sdbi, 
        'rab_bulan_sdbi' => $budget_perawatan_total_sdbi, 
        'persen_bulan_sdbi' => $persen_perawatan_total_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_biaya_perawatan_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_perawatan_total_setahun, 
        'persen' => $persen_perawatan_total_setahun."%"
    ],
    'rowcontent',
    'font-weight:bold;'
);

$tabelHtml .= buildTableRowWithUnit(
    'Material',
    '',
    [
        'aktual_bulan_ini' => $aktual_perawatan_material_bi, 
        'rat_bulan_ini' => $budget_perawatan_material_bi, 
        'rab_bulan_ini' => $budget_perawatan_material_bi, 
        'persen_bulan_ini' => $persen_perawatan_material_bi."%",
        'aktual_bulan_lalu' => $aktual_perawatan_material_sdbl, 
        'rat_bulan_lalu' => $budget_perawatan_material_sdbl, 
        'rab_bulan_lalu' => $budget_perawatan_material_sdbl, 
        'persen_bulan_lalu' => $persen_perawatan_material_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_perawatan_material_sdbi, 
        'rat_bulan_sdbi' => $budget_perawatan_material_sdbi, 
        'rab_bulan_sdbi' => $budget_perawatan_material_sdbi, 
        'persen_bulan_sdbi' => $persen_perawatan_material_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_perawatan_material_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_perawatan_material_setahun, 
        'persen' => $persen_perawatan_material_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Upah',
    '',
    [
        'aktual_bulan_ini' => $aktual_perawatan_upah_bi, 
        'rat_bulan_ini' => $budget_perawatan_upah_bi, 
        'rab_bulan_ini' => $budget_perawatan_upah_bi, 
        'persen_bulan_ini' => $persen_perawatan_upah_bi."%",
        'aktual_bulan_lalu' => $aktual_perawatan_upah_sdbl, 
        'rat_bulan_lalu' => $budget_perawatan_upah_sdbl, 
        'rab_bulan_lalu' => $budget_perawatan_upah_sdbl, 
        'persen_bulan_lalu' => $persen_perawatan_upah_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_perawatan_upah_sdbi, 
        'rat_bulan_sdbi' => $budget_perawatan_upah_sdbi, 
        'rab_bulan_sdbi' => $budget_perawatan_upah_sdbi, 
        'persen_bulan_sdbi' => $persen_perawatan_upah_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_perawatan_upah_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_perawatan_upah_setahun, 
        'persen' => $persen_perawatan_upah_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Transport',
    '',
    [
        'aktual_bulan_ini' => $aktual_perawatan_transport_bi, 
        'rat_bulan_ini' => $budget_perawatan_transport_bi, 
        'rab_bulan_ini' => $budget_perawatan_transport_bi, 
        'persen_bulan_ini' => $persen_perawatan_transport_bi."%",
        'aktual_bulan_lalu' => $aktual_perawatan_transport_sdbl, 
        'rat_bulan_lalu' => $budget_perawatan_transport_sdbl, 
        'rab_bulan_lalu' => $budget_perawatan_transport_sdbl, 
        'persen_bulan_lalu' => $persen_perawatan_transport_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_perawatan_transport_sdbi, 
        'rat_bulan_sdbi' => $budget_perawatan_transport_sdbi, 
        'rab_bulan_sdbi' => $budget_perawatan_transport_sdbi, 
        'persen_bulan_sdbi' => $persen_perawatan_transport_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_perawatan_transport_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_perawatan_transport_setahun, 
        'persen' => $persen_perawatan_transport_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Biaya per Hektar',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_perawatan_per_ha_bi, 
        'rat_bulan_ini' => $budget_perawatan_per_ha_bi, 
        'rab_bulan_ini' => $budget_perawatan_per_ha_bi, 
        'persen_bulan_ini' => $persen_perawatan_per_ha_bi.'%',
        'aktual_bulan_lalu' => $aktual_perawatan_per_ha_sdbl, 
        'rat_bulan_lalu' => $budget_perawatan_per_ha_sdbl, 
        'rab_bulan_lalu' => $budget_perawatan_per_ha_sdbl, 
        'persen_bulan_lalu' => $persen_perawatan_per_ha_sdbl.'%',
        'aktual_bulan_sdbi' => $aktual_perawatan_per_ha_sdbi, 
        'rat_bulan_sdbi' => $budget_perawatan_per_ha_sdbi, 
        'rab_bulan_sdbi' => $budget_perawatan_per_ha_sdbi, 
        'persen_bulan_sdbi' => $persen_perawatan_per_ha_sdbi.'%',
        'sdbi_tahun_lalu' => $aktual_perawatan_per_ha_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_perawatan_per_ha_setahun, 
        'persen' => $persen_perawatan_per_ha.'%'
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

// ============================================================================
// BUILD TABLE CONTENT - SECTION: BIAYA PUPUK
// ============================================================================

$tabelHtml .= "<tr style=\"height:10px;\"></tr>";

$tabelHtml .= buildTableRowWithUnit(
    'Biaya Pupuk',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_biaya_pupuk_bi, 
        'rat_bulan_ini' => $budget_pupuk_total_bi, 
        'rab_bulan_ini' => $budget_pupuk_total_bi, 
        'persen_bulan_ini' => $persen_pupuk_total_bi."%",
        'aktual_bulan_lalu' => $aktual_biaya_pupuk_sdbl, 
        'rat_bulan_lalu' => $budget_pupuk_total_sdbl, 
        'rab_bulan_lalu' => $budget_pupuk_total_sdbl, 
        'persen_bulan_lalu' => $persen_pupuk_total_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_biaya_pupuk_sdbi, 
        'rat_bulan_sdbi' => $budget_pupuk_total_sdbi, 
        'rab_bulan_sdbi' => $budget_pupuk_total_sdbi, 
        'persen_bulan_sdbi' => $persen_pupuk_total_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_biaya_pupuk_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_pupuk_total_setahun, 
        'persen' => $persen_pupuk_total_setahun."%"
    ],
    'rowcontent',
    'font-weight:bold;'
);

$tabelHtml .= buildTableRowWithUnit(
    'Material',
    '',
    [
        'aktual_bulan_ini' => $aktual_pupuk_material_bi, 
        'rat_bulan_ini' => $budget_pupuk_material_bi, 
        'rab_bulan_ini' => $budget_pupuk_material_bi, 
        'persen_bulan_ini' => $persen_pupuk_material_bi."%",
        'aktual_bulan_lalu' => $aktual_pupuk_material_sdbl, 
        'rat_bulan_lalu' => $budget_pupuk_material_sdbl, 
        'rab_bulan_lalu' => $budget_pupuk_material_sdbl, 
        'persen_bulan_lalu' => $persen_pupuk_material_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_pupuk_material_sdbi, 
        'rat_bulan_sdbi' => $budget_pupuk_material_sdbi, 
        'rab_bulan_sdbi' => $budget_pupuk_material_sdbi, 
        'persen_bulan_sdbi' => $persen_pupuk_material_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_pupuk_material_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_pupuk_material_setahun, 
        'persen' => $persen_pupuk_material_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Upah',
    '',
    [
        'aktual_bulan_ini' => $aktual_pupuk_upah_bi, 
        'rat_bulan_ini' => $budget_pupuk_upah_bi, 
        'rab_bulan_ini' => $budget_pupuk_upah_bi, 
        'persen_bulan_ini' => $persen_pupuk_upah_bi."%",
        'aktual_bulan_lalu' => $aktual_pupuk_upah_sdbl, 
        'rat_bulan_lalu' => $budget_pupuk_upah_sdbl, 
        'rab_bulan_lalu' => $budget_pupuk_upah_sdbl, 
        'persen_bulan_lalu' => $persen_pupuk_upah_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_pupuk_upah_sdbi, 
        'rat_bulan_sdbi' => $budget_pupuk_upah_sdbi, 
        'rab_bulan_sdbi' => $budget_pupuk_upah_sdbi, 
        'persen_bulan_sdbi' => $persen_pupuk_upah_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_pupuk_upah_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_pupuk_upah_setahun, 
        'persen' => $persen_pupuk_upah_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Transport',
    '',
    [
        'aktual_bulan_ini' => $aktual_pupuk_transport_bi, 
        'rat_bulan_ini' => $budget_pupuk_transport_bi, 
        'rab_bulan_ini' => $budget_pupuk_transport_bi, 
        'persen_bulan_ini' => $persen_pupuk_transport_bi."%",
        'aktual_bulan_lalu' => $aktual_pupuk_transport_sdbl, 
        'rat_bulan_lalu' => $budget_pupuk_transport_sdbl, 
        'rab_bulan_lalu' => $budget_pupuk_transport_sdbl, 
        'persen_bulan_lalu' => $persen_pupuk_transport_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_pupuk_transport_sdbi, 
        'rat_bulan_sdbi' => $budget_pupuk_transport_sdbi, 
        'rab_bulan_sdbi' => $budget_pupuk_transport_sdbi, 
        'persen_bulan_sdbi' => $persen_pupuk_transport_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_pupuk_transport_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_pupuk_transport_setahun, 
        'persen' => $persen_pupuk_transport_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Biaya per Hektar',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_pupuk_per_ha_bi, 
        'rat_bulan_ini' => $budget_pupuk_per_ha_bi, 
        'rab_bulan_ini' => $budget_pupuk_per_ha_bi, 
        'persen_bulan_ini' => $persen_pupuk_per_ha_bi."%",
        'aktual_bulan_lalu' => $aktual_pupuk_per_ha_sdbl, 
        'rat_bulan_lalu' => $budget_pupuk_per_ha_sdbl, 
        'rab_bulan_lalu' => $budget_pupuk_per_ha_sdbl, 
        'persen_bulan_lalu' => $persen_pupuk_per_ha_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_pupuk_per_ha_sdbi, 
        'rat_bulan_sdbi' => $budget_pupuk_per_ha_sdbi, 
        'rab_bulan_sdbi' => $budget_pupuk_per_ha_sdbi, 
        'persen_bulan_sdbi' => $persen_pupuk_per_ha_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_pupuk_per_ha_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_pupuk_per_ha_setahun, 
        'persen' => $persen_pupuk_per_ha_setahun."%"
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

// ============================================================================
// BUILD TABLE CONTENT - SECTION: TOTAL BIAYA
// ============================================================================

$tabelHtml .= "<tr style=\"height:10px;\"></tr>";

$tabelHtml .= buildTableRowWithUnit(
    'Total Biaya Tanaman Menghasilkan',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_total_biaya_tm_bi, 
        'rat_bulan_ini' => $budget_total_biaya_tm_bi, 
        'rab_bulan_ini' => $budget_total_biaya_tm_bi, 
        'persen_bulan_ini' => $persen_total_biaya_tm_bi."%",
        'aktual_bulan_lalu' => $aktual_total_biaya_tm_sdbl, 
        'rat_bulan_lalu' => $budget_total_biaya_tm_sdbl, 
        'rab_bulan_lalu' => $budget_total_biaya_tm_sdbl, 
        'persen_bulan_lalu' => $persen_total_biaya_tm_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_total_biaya_tm_sdbi, 
        'rat_bulan_sdbi' => $budget_total_biaya_tm_sdbi, 
        'rab_bulan_sdbi' => $budget_total_biaya_tm_sdbi, 
        'persen_bulan_sdbi' => $persen_total_biaya_tm_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_total_biaya_tm_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_total_biaya_tm_setahun, 
        'persen' => $persen_total_biaya_tm_setahun."%"
    ],
    'rowcontent',
    'font-weight:bold;'
);

$tabelHtml .= buildTableRowWithUnit(
    'Biaya per Hektar',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_total_biaya_tm_per_ha_bi, 
        'rat_bulan_ini' => $budget_total_biaya_tm_per_ha_bi, 
        'rab_bulan_ini' => $budget_total_biaya_tm_per_ha_bi, 
        'persen_bulan_ini' => $persen_total_biaya_tm_per_ha_bi."%",
        'aktual_bulan_lalu' => $aktual_total_biaya_tm_per_ha_sdbl, 
        'rat_bulan_lalu' => $budget_total_biaya_tm_per_ha_sdbl, 
        'rab_bulan_lalu' => $budget_total_biaya_tm_per_ha_sdbl, 
        'persen_bulan_lalu' => $persen_total_biaya_tm_per_ha_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_total_biaya_tm_per_ha_sdbi, 
        'rat_bulan_sdbi' => $budget_total_biaya_tm_per_ha_sdbi, 
        'rab_bulan_sdbi' => $budget_total_biaya_tm_per_ha_sdbi, 
        'persen_bulan_sdbi' => $persen_total_biaya_tm_per_ha_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_total_biaya_tm_per_ha_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_total_biaya_tm_per_ha_setahun, 
        'persen' => $persen_total_biaya_tm_per_ha_setahun."%"
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

// ============================================================================
// BUILD TABLE CONTENT - SECTION: BIAYA PANEN
// ============================================================================

$tabelHtml .= "<tr style=\"height:10px;\"></tr>";

$tabelHtml .= buildTableRowWithUnit(
    'Biaya Panen',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_biaya_panen_bi, 
        'rat_bulan_ini' => $budget_panen_total_bi, 
        'rab_bulan_ini' => $budget_panen_total_bi, 
        'persen_bulan_ini' => $persen_panen_total_bi."%",
        'aktual_bulan_lalu' => $aktual_biaya_panen_sdbl, 
        'rat_bulan_lalu' => $budget_panen_total_sdbl, 
        'rab_bulan_lalu' => $budget_panen_total_sdbl, 
        'persen_bulan_lalu' => $persen_panen_total_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_biaya_panen_sdbi, 
        'rat_bulan_sdbi' => $budget_panen_total_sdbi, 
        'rab_bulan_sdbi' => $budget_panen_total_sdbi, 
        'persen_bulan_sdbi' => $persen_panen_total_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_biaya_panen_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_panen_total_setahun, 
        'persen' => $persen_panen_total_setahun."%"
    ],
    'rowcontent',
    'font-weight:bold;'
);


$tabelHtml .= buildTableRowWithUnit(
    'Material',
    '',
    [
        'aktual_bulan_ini' => $aktual_panen_material_bi, 
        'rat_bulan_ini' => $budget_panen_material_bi, 
        'rab_bulan_ini' => $budget_panen_material_bi, 
        'persen_bulan_ini' => $persen_panen_material_bi."%",
        'aktual_bulan_lalu' => $aktual_panen_material_sdbl, 
        'rat_bulan_lalu' => $budget_panen_material_sdbl, 
        'rab_bulan_lalu' => $budget_panen_material_sdbl, 
        'persen_bulan_lalu' => $persen_panen_material_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_panen_material_sdbi, 
        'rat_bulan_sdbi' => $budget_panen_material_sdbi, 
        'rab_bulan_sdbi' => $budget_panen_material_sdbi, 
        'persen_bulan_sdbi' => $persen_panen_material_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_panen_material_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_panen_material_setahun, 
        'persen' => $persen_panen_material_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Upah',
    '',
    [
        'aktual_bulan_ini' => $aktual_panen_upah_bi, 
        'rat_bulan_ini' => $budget_panen_upah_bi, 
        'rab_bulan_ini' => $budget_panen_upah_bi, 
        'persen_bulan_ini' => $persen_panen_upah_bi."%",
        'aktual_bulan_lalu' => $aktual_panen_upah_sdbl, 
        'rat_bulan_lalu' => $budget_panen_upah_sdbl, 
        'rab_bulan_lalu' => $budget_panen_upah_sdbl, 
        'persen_bulan_lalu' => $persen_panen_upah_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_panen_upah_sdbi, 
        'rat_bulan_sdbi' => $budget_panen_upah_sdbi, 
        'rab_bulan_sdbi' => $budget_panen_upah_sdbi, 
        'persen_bulan_sdbi' => $persen_panen_upah_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_panen_upah_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_panen_upah_setahun, 
        'persen' => $persen_panen_upah_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Transport',
    '',
    [
        'aktual_bulan_ini' => $aktual_panen_transport_bi, 
        'rat_bulan_ini' => $budget_panen_transport_bi, 
        'rab_bulan_ini' => $budget_panen_transport_bi, 
        'persen_bulan_ini' => $persen_panen_transport_bi."%",
        'aktual_bulan_lalu' => $aktual_panen_transport_sdbl, 
        'rat_bulan_lalu' => $budget_panen_transport_sdbl, 
        'rab_bulan_lalu' => $budget_panen_transport_sdbl, 
        'persen_bulan_lalu' => $persen_panen_transport_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_panen_transport_sdbi, 
        'rat_bulan_sdbi' => $budget_panen_transport_sdbi, 
        'rab_bulan_sdbi' => $budget_panen_transport_sdbi, 
        'persen_bulan_sdbi' => $persen_panen_transport_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_panen_transport_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_panen_transport_setahun, 
        'persen' => $persen_panen_transport_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Biaya per Ton TBS',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_panen_per_ton_bi, 
        'rat_bulan_ini' => $budget_panen_per_ton_bi, 
        'rab_bulan_ini' => $budget_panen_per_ton_bi, 
        'persen_bulan_ini' => $persen_panen_per_ton_bi."%",
        'aktual_bulan_lalu' => $aktual_panen_per_ton_sdbl, 
        'rat_bulan_lalu' => $budget_panen_per_ton_sdbl, 
        'rab_bulan_lalu' => $budget_panen_per_ton_sdbl, 
        'persen_bulan_lalu' => $persen_panen_per_ton_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_panen_per_ton_sdbi, 
        'rat_bulan_sdbi' => $budget_panen_per_ton_sdbi, 
        'rab_bulan_sdbi' => $budget_panen_per_ton_sdbi, 
        'persen_bulan_sdbi' => $persen_panen_per_ton_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_panen_per_ton_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_panen_per_ton_setahun, 
        'persen' => $persen_panen_per_ton_setahun."%"
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

// ============================================================================
// BUILD TABLE CONTENT - SECTION: BIAYA ANGKUT
// ============================================================================

$tabelHtml .= "<tr style=\"height:10px;\"></tr>";

$tabelHtml .= buildTableRowWithUnit(
    'Biaya Angkut',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_biaya_angkut_bi, 
        'rat_bulan_ini' => $budget_angkut_total_bi, 
        'rab_bulan_ini' => $budget_angkut_total_bi, 
        'persen_bulan_ini' => $persen_angkut_total_bi."%",
        'aktual_bulan_lalu' => $aktual_biaya_angkut_sdbl, 
        'rat_bulan_lalu' => $budget_angkut_total_sdbl, 
        'rab_bulan_lalu' => $budget_angkut_total_sdbl, 
        'persen_bulan_lalu' => $persen_angkut_total_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_biaya_angkut_sdbi, 
        'rat_bulan_sdbi' => $budget_angkut_total_sdbi, 
        'rab_bulan_sdbi' => $budget_angkut_total_sdbi, 
        'persen_bulan_sdbi' => $persen_angkut_total_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_biaya_angkut_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_angkut_total_setahun, 
        'persen' => $persen_angkut_total_setahun."%"
    ],
    'rowcontent',
    'font-weight:bold;'
);


$tabelHtml .= buildTableRowWithUnit(
    'Material',
    '',
    [
        'aktual_bulan_ini' => $aktual_angkut_material_bi, 
        'rat_bulan_ini' => $budget_angkut_material_bi, 
        'rab_bulan_ini' => $budget_angkut_material_bi, 
        'persen_bulan_ini' => $persen_angkut_material_bi."%",
        'aktual_bulan_lalu' => $aktual_angkut_material_sdbl, 
        'rat_bulan_lalu' => $budget_angkut_material_sdbl, 
        'rab_bulan_lalu' => $budget_angkut_material_sdbl, 
        'persen_bulan_lalu' => $persen_angkut_material_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_angkut_material_sdbi, 
        'rat_bulan_sdbi' => $budget_angkut_material_sdbi, 
        'rab_bulan_sdbi' => $budget_angkut_material_sdbi, 
        'persen_bulan_sdbi' => $persen_angkut_material_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_angkut_material_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_angkut_material_setahun, 
        'persen' => $persen_angkut_material_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Upah',
    '',
    [
        'aktual_bulan_ini' => $aktual_angkut_upah_bi, 
        'rat_bulan_ini' => $budget_angkut_upah_bi, 
        'rab_bulan_ini' => $budget_angkut_upah_bi, 
        'persen_bulan_ini' => $persen_angkut_upah_bi."%",
        'aktual_bulan_lalu' => $aktual_angkut_upah_sdbl, 
        'rat_bulan_lalu' => $budget_angkut_upah_sdbl, 
        'rab_bulan_lalu' => $budget_angkut_upah_sdbl, 
        'persen_bulan_lalu' => $persen_angkut_upah_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_angkut_upah_sdbi, 
        'rat_bulan_sdbi' => $budget_angkut_upah_sdbi, 
        'rab_bulan_sdbi' => $budget_angkut_upah_sdbi, 
        'persen_bulan_sdbi' => $persen_angkut_upah_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_angkut_upah_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_angkut_upah_setahun, 
        'persen' => $persen_angkut_upah_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Transport',
    '',
    [
        'aktual_bulan_ini' => $aktual_angkut_transport_bi, 
        'rat_bulan_ini' => $budget_angkut_transport_bi, 
        'rab_bulan_ini' => $budget_angkut_transport_bi, 
        'persen_bulan_ini' => $persen_angkut_transport_bi."%",
        'aktual_bulan_lalu' => $aktual_angkut_transport_sdbl, 
        'rat_bulan_lalu' => $budget_angkut_transport_sdbl, 
        'rab_bulan_lalu' => $budget_angkut_transport_sdbl, 
        'persen_bulan_lalu' => $persen_angkut_transport_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_angkut_transport_sdbi, 
        'rat_bulan_sdbi' => $budget_angkut_transport_sdbi, 
        'rab_bulan_sdbi' => $budget_angkut_transport_sdbi, 
        'persen_bulan_sdbi' => $persen_angkut_transport_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_angkut_transport_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_angkut_transport_setahun, 
        'persen' => $persen_angkut_transport_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Biaya per Ton TBS',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_angkut_per_ton_bi, 
        'rat_bulan_ini' => $budget_angkut_per_ton_bi, 
        'rab_bulan_ini' => $budget_angkut_per_ton_bi, 
        'persen_bulan_ini' => $persen_angkut_per_ton_bi."%",
        'aktual_bulan_lalu' => $aktual_angkut_per_ton_sdbl, 
        'rat_bulan_lalu' => $budget_angkut_per_ton_sdbl, 
        'rab_bulan_lalu' => $budget_angkut_per_ton_sdbl, 
        'persen_bulan_lalu' => $persen_angkut_per_ton_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_angkut_per_ton_sdbi, 
        'rat_bulan_sdbi' => $budget_angkut_per_ton_sdbi, 
        'rab_bulan_sdbi' => $budget_angkut_per_ton_sdbi, 
        'persen_bulan_sdbi' => $persen_angkut_per_ton_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_angkut_per_ton_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_angkut_per_ton_setahun, 
        'persen' => $persen_angkut_per_ton_setahun."%"
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

// ============================================================================
// BUILD TABLE CONTENT - Total Biaya Panen dan Angkut
// ============================================================================

$tabelHtml .= "<tr style=\"height:10px;\"></tr>";

$total_aktual_panen_angkut_bi = $aktual_biaya_panen_bi + $aktual_biaya_angkut_bi;
$total_aktual_panen_angkut_sdbl = $aktual_biaya_panen_sdbl + $aktual_biaya_angkut_sdbl;
$total_aktual_panen_angkut_sdbi = $aktual_biaya_panen_sdbi + $aktual_biaya_angkut_sdbi;
$total_aktual_panen_angkut_sdbi_tl = $aktual_biaya_panen_sdbi_tl + $aktual_biaya_angkut_sdbi_tl;
$total_aktual_panen_angkut_setahun = $aktual_biaya_panen_setahun + $aktual_biaya_angkut_setahun;

$total_budget_panen_angkut_bi = $budget_panen_total_bi + $budget_angkut_total_bi;
$total_budget_panen_angkut_sdbl = $budget_panen_total_sdbl + $budget_angkut_total_sdbl;
$total_budget_panen_angkut_sdbi = $budget_panen_total_sdbi + $budget_angkut_total_sdbi;
$total_budget_panen_angkut_sdbi_tl = $budget_panen_total_sdbi_tl + $budget_angkut_total_sdbi_tl;
$total_budget_panen_angkut_setahun = $budget_panen_total_setahun + $budget_angkut_total_setahun;

$tabelHtml .= buildTableRowWithUnit(
    'Total Biaya Panen dan Angkut',
    'Rp',
    [
        'aktual_bulan_ini' => $total_aktual_panen_angkut_bi, 
        'rat_bulan_ini' => $total_budget_panen_angkut_bi, 
        'rab_bulan_ini' => $total_budget_panen_angkut_bi, 
        'persen_bulan_ini' => calcPercent($total_aktual_panen_angkut_bi, $total_budget_panen_angkut_bi )."%",
        'aktual_bulan_lalu' => $total_aktual_panen_angkut_sdbl, 
        'rat_bulan_lalu' => $total_budget_panen_angkut_sdbl, 
        'rab_bulan_lalu' => $total_budget_panen_angkut_sdbl, 
        'persen_bulan_lalu' => calcPercent($total_aktual_panen_angkut_sdbl, $total_budget_panen_angkut_sdbl )."%",
        'aktual_bulan_sdbi' => $total_aktual_panen_angkut_sdbi, 
        'rat_bulan_sdbi' => $total_budget_panen_angkut_sdbi, 
        'rab_bulan_sdbi' => $total_budget_panen_angkut_sdbi, 
        'persen_bulan_sdbi' => calcPercent($total_aktual_panen_angkut_sdbi, $total_budget_panen_angkut_sdbi )."%",
        'sdbi_tahun_lalu' => $total_aktual_panen_angkut_sdbi_tl, 
        'anggaran_tahun_ini' => $total_budget_panen_angkut_setahun, 
        'persen' => calcPercent($total_aktual_panen_angkut_sdbi, $total_budget_panen_angkut_setahun )."%"
    ],
    'rowcontent',
    'font-weight:bold;'
);

$tabelHtml .= buildTableRowWithUnit(
    'Biaya per Ton TBS',
    'Rp',
    [
        'aktual_bulan_ini' => calcBiayaPerTon($total_aktual_panen_angkut_bi, $produksi_buah_bulan_ini), 
        'rat_bulan_ini' => calcBiayaPerTon($total_budget_panen_angkut_bi, $rat_budget_produksi_bulan_ini), 
        'rab_bulan_ini' => calcBiayaPerTon($total_budget_panen_angkut_bi, $rab_budget_produksi_bulan_ini), 
        'persen_bulan_ini' => calcPercent($total_aktual_panen_angkut_bi, $total_budget_panen_angkut_bi )."%",
        'aktual_bulan_lalu' => calcBiayaPerTon($total_aktual_panen_angkut_sdbl, $produksi_buah_bulan_lalu), 
        'rat_bulan_lalu' => calcBiayaPerTon($total_budget_panen_angkut_sdbl, $rat_budget_produksi_bulan_lalu), 
        'rab_bulan_lalu' => calcBiayaPerTon($total_budget_panen_angkut_sdbl, $rab_budget_produksi_bulan_lalu), 
        'persen_bulan_lalu' => calcPercent($total_aktual_panen_angkut_sdbl, $total_budget_panen_angkut_sdbl )."%",
        'aktual_bulan_sdbi' => calcBiayaPerTon($total_aktual_panen_angkut_sdbi, $produksi_buah_sdbi), 
        'rat_bulan_sdbi' => calcBiayaPerTon($total_budget_panen_angkut_sdbi, $rat_budget_produksi_sdbi), 
        'rab_bulan_sdbi' => calcBiayaPerTon($total_budget_panen_angkut_sdbi, $rab_budget_produksi_sdbi), 
        'persen_bulan_sdbi' => calcPercent($total_aktual_panen_angkut_sdbi, $total_budget_panen_angkut_sdbi )."%",
        'sdbi_tahun_lalu' => calcBiayaPerTon($total_aktual_panen_angkut_sdbi_tl, $produksi_buah_sdbi_tl), 
        'anggaran_tahun_ini' => calcBiayaPerTon($total_budget_panen_angkut_setahun, $rat_budget_produksi_bulan_ini), 
        'persen' => calcPercent($total_aktual_panen_angkut_sdbi, $total_budget_panen_angkut_setahun )."%"
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

$total_aktual_biaya_langsung_bi = $aktual_total_biaya_tm_bi + $total_aktual_panen_angkut_bi;
$total_aktual_biaya_langsung_sdbl = $aktual_total_biaya_tm_sdbl + $total_aktual_panen_angkut_sdbl;
$total_aktual_biaya_langsung_sdbi = $aktual_total_biaya_tm_sdbi + $total_aktual_panen_angkut_sdbi;
$total_aktual_biaya_langsung_sdbi_tl = $aktual_total_biaya_tm_sdbi_tl + $total_aktual_panen_angkut_sdbi_tl;
$total_aktual_biaya_langsung_setahun = $aktual_total_biaya_tm_setahun + $total_aktual_panen_angkut_setahun;

$total_budget_biaya_langsung_bi = $budget_total_biaya_tm_bi + $total_budget_panen_angkut_bi;
$total_budget_biaya_langsung_sdbl = $budget_total_biaya_tm_sdbl + $total_budget_panen_angkut_sdbl;
$total_budget_biaya_langsung_sdbi = $budget_total_biaya_tm_sdbi + $total_budget_panen_angkut_sdbi;
$total_budget_biaya_langsung_sdbi_tl = $budget_total_biaya_tm_sdbi_tl + $total_budget_panen_angkut_sdbi_tl;
$total_budget_biaya_langsung_setahun = $budget_total_biaya_tm_setahun + $total_budget_panen_angkut_setahun;

$tabelHtml .= buildTableRowWithUnit(
    'Total Biaya Langsung',
    'Rp',
    [
        'aktual_bulan_ini' => $total_aktual_biaya_langsung_bi, 
        'rat_bulan_ini' => $total_budget_biaya_langsung_bi, 
        'rab_bulan_ini' => $total_budget_biaya_langsung_bi, 
        'persen_bulan_ini' => calcPercent($total_aktual_biaya_langsung_bi, $total_budget_biaya_langsung_bi )."%",
        'aktual_bulan_lalu' => $total_aktual_biaya_langsung_sdbl, 
        'rat_bulan_lalu' => $total_budget_biaya_langsung_sdbl, 
        'rab_bulan_lalu' => $total_budget_biaya_langsung_sdbl, 
        'persen_bulan_lalu' => calcPercent($total_aktual_biaya_langsung_sdbl, $total_budget_biaya_langsung_sdbl )."%",
        'aktual_bulan_sdbi' => $total_aktual_biaya_langsung_sdbi, 
        'rat_bulan_sdbi' => $total_budget_biaya_langsung_sdbi, 
        'rab_bulan_sdbi' => $total_budget_biaya_langsung_sdbi, 
        'persen_bulan_sdbi' => calcPercent($total_aktual_biaya_langsung_sdbi, $total_budget_biaya_langsung_sdbi )."%",
        'sdbi_tahun_lalu' => $total_aktual_biaya_langsung_sdbi_tl, 
        'anggaran_tahun_ini' => $total_budget_biaya_langsung_setahun, 
        'persen' => calcPercent($total_aktual_biaya_langsung_sdbi, $total_budget_biaya_langsung_setahun )."%"
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

// ============================================================================
// Biaya Tidak Langsung
// ============================================================================

$tabelHtml .= "<tr style=\"height:10px;\"></tr>";

$tabelHtml .= "<tr class=\"rowcontent\" style=\"font-weight:bold;\">
                    <td>Biaya Tidak Langsung:</td>
                </tr>";

// Biaya Sosial
$tabelHtml .= buildTableRowWithUnit(
    'Biaya Sosial',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_biaya_sosial_bi, 
        'rat_bulan_ini' => $budget_sosial_total_bi, 
        'rab_bulan_ini' => $budget_sosial_total_bi, 
        'persen_bulan_ini' => $persen_sosial_total_bi."%",
        'aktual_bulan_lalu' => $aktual_biaya_sosial_sdbl, 
        'rat_bulan_lalu' => $budget_sosial_total_sdbl, 
        'rab_bulan_lalu' => $budget_sosial_total_sdbl, 
        'persen_bulan_lalu' => $persen_sosial_total_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_biaya_sosial_sdbi, 
        'rat_bulan_sdbi' => $budget_sosial_total_sdbi, 
        'rab_bulan_sdbi' => $budget_sosial_total_sdbi, 
        'persen_bulan_sdbi' => $persen_sosial_total_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_biaya_sosial_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_sosial_total_setahun, 
        'persen' => $persen_sosial_total_setahun."%"
    ],
    'rowcontent',
    'font-weight:bold;'
);

$tabelHtml .= buildTableRowWithUnit(
    'Material',
    '',
    [
        'aktual_bulan_ini' => $aktual_sosial_material_bi, 
        'rat_bulan_ini' => $budget_sosial_material_bi, 
        'rab_bulan_ini' => $budget_sosial_material_bi, 
        'persen_bulan_ini' => $persen_sosial_material_bi."%",
        'aktual_bulan_lalu' => $aktual_sosial_material_sdbl, 
        'rat_bulan_lalu' => $budget_sosial_material_sdbl, 
        'rab_bulan_lalu' => $budget_sosial_material_sdbl, 
        'persen_bulan_lalu' => $persen_sosial_material_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_sosial_material_sdbi, 
        'rat_bulan_sdbi' => $budget_sosial_material_sdbi, 
        'rab_bulan_sdbi' => $budget_sosial_material_sdbi, 
        'persen_bulan_sdbi' => $persen_sosial_material_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_sosial_material_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_sosial_material_setahun, 
        'persen' => $persen_sosial_material_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Upah',
    '',
    [
        'aktual_bulan_ini' => $aktual_sosial_upah_bi, 
        'rat_bulan_ini' => $budget_sosial_upah_bi, 
        'rab_bulan_ini' => $budget_sosial_upah_bi, 
        'persen_bulan_ini' => $persen_sosial_upah_bi."%",
        'aktual_bulan_lalu' => $aktual_sosial_upah_sdbl, 
        'rat_bulan_lalu' => $budget_sosial_upah_sdbl, 
        'rab_bulan_lalu' => $budget_sosial_upah_sdbl, 
        'persen_bulan_lalu' => $persen_sosial_upah_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_sosial_upah_sdbi, 
        'rat_bulan_sdbi' => $budget_sosial_upah_sdbi, 
        'rab_bulan_sdbi' => $budget_sosial_upah_sdbi, 
        'persen_bulan_sdbi' => $persen_sosial_upah_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_sosial_upah_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_sosial_upah_setahun, 
        'persen' => $persen_sosial_upah_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Transport',
    '',
    [
        'aktual_bulan_ini' => $aktual_sosial_transport_bi, 
        'rat_bulan_ini' => $budget_sosial_transport_bi, 
        'rab_bulan_ini' => $budget_sosial_transport_bi, 
        'persen_bulan_ini' => $persen_sosial_transport_bi."%",
        'aktual_bulan_lalu' => $aktual_sosial_transport_sdbl, 
        'rat_bulan_lalu' => $budget_sosial_transport_sdbl, 
        'rab_bulan_lalu' => $budget_sosial_transport_sdbl, 
        'persen_bulan_lalu' => $persen_sosial_transport_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_sosial_transport_sdbi, 
        'rat_bulan_sdbi' => $budget_sosial_transport_sdbi, 
        'rab_bulan_sdbi' => $budget_sosial_transport_sdbi, 
        'persen_bulan_sdbi' => $persen_sosial_transport_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_sosial_transport_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_sosial_transport_setahun, 
        'persen' => $persen_sosial_transport_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Biaya per Hektar',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_sosial_per_ha_bi, 
        'rat_bulan_ini' => $budget_sosial_per_ha_bi, 
        'rab_bulan_ini' => $budget_sosial_per_ha_bi, 
        'persen_bulan_ini' => $persen_sosial_per_ha_bi."%",
        'aktual_bulan_lalu' => $aktual_sosial_per_ha_sdbl, 
        'rat_bulan_lalu' => $budget_sosial_per_ha_sdbl, 
        'rab_bulan_lalu' => $budget_sosial_per_ha_sdbl, 
        'persen_bulan_lalu' => $persen_sosial_per_ha_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_sosial_per_ha_sdbi, 
        'rat_bulan_sdbi' => $budget_sosial_per_ha_sdbi, 
        'rab_bulan_sdbi' => $budget_sosial_per_ha_sdbi, 
        'persen_bulan_sdbi' => $persen_sosial_per_ha_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_sosial_per_ha_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_sosial_per_ha_setahun, 
        'persen' => $persen_sosial_per_ha_setahun."%"
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

$tabelHtml .= "<tr style=\"height:10px;\"></tr>";

// Biaya Investasi
$tabelHtml .= buildTableRowWithUnit(
    'Biaya Investasi',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_biaya_investasi_bi, 
        'rat_bulan_ini' => $budget_investasi_total_bi, 
        'rab_bulan_ini' => $budget_investasi_total_bi, 
        'persen_bulan_ini' => $persen_investasi_total_bi."%",
        'aktual_bulan_lalu' => $aktual_biaya_investasi_sdbl, 
        'rat_bulan_lalu' => $budget_investasi_total_sdbl, 
        'rab_bulan_lalu' => $budget_investasi_total_sdbl, 
        'persen_bulan_lalu' => $persen_investasi_total_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_biaya_investasi_sdbi, 
        'rat_bulan_sdbi' => $budget_investasi_total_sdbi, 
        'rab_bulan_sdbi' => $budget_investasi_total_sdbi, 
        'persen_bulan_sdbi' => $persen_investasi_total_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_biaya_investasi_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_investasi_total_setahun, 
        'persen' => $persen_investasi_total_setahun."%"
    ],
    'rowcontent',
    'font-weight:bold;'
);

$tabelHtml .= buildTableRowWithUnit(
    'Material',
    '',
    [
        'aktual_bulan_ini' => $aktual_investasi_material_bi, 
        'rat_bulan_ini' => $budget_investasi_material_bi, 
        'rab_bulan_ini' => $budget_investasi_material_bi, 
        'persen_bulan_ini' => $persen_investasi_material_bi."%",
        'aktual_bulan_lalu' => $aktual_investasi_material_sdbl, 
        'rat_bulan_lalu' => $budget_investasi_material_sdbl, 
        'rab_bulan_lalu' => $budget_investasi_material_sdbl, 
        'persen_bulan_lalu' => $persen_investasi_material_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_investasi_material_sdbi, 
        'rat_bulan_sdbi' => $budget_investasi_material_sdbi, 
        'rab_bulan_sdbi' => $budget_investasi_material_sdbi, 
        'persen_bulan_sdbi' => $persen_investasi_material_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_investasi_material_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_investasi_material_setahun, 
        'persen' => $persen_investasi_material_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Upah',
    '',
    [
        'aktual_bulan_ini' => $aktual_investasi_upah_bi, 
        'rat_bulan_ini' => $budget_investasi_upah_bi, 
        'rab_bulan_ini' => $budget_investasi_upah_bi, 
        'persen_bulan_ini' => $persen_investasi_upah_bi."%",
        'aktual_bulan_lalu' => $aktual_investasi_upah_sdbl, 
        'rat_bulan_lalu' => $budget_investasi_upah_sdbl, 
        'rab_bulan_lalu' => $budget_investasi_upah_sdbl, 
        'persen_bulan_lalu' => $persen_investasi_upah_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_investasi_upah_sdbi, 
        'rat_bulan_sdbi' => $budget_investasi_upah_sdbi, 
        'rab_bulan_sdbi' => $budget_investasi_upah_sdbi, 
        'persen_bulan_sdbi' => $persen_investasi_upah_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_investasi_upah_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_investasi_upah_setahun, 
        'persen' => $persen_investasi_upah_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Transport',
    '',
    [
        'aktual_bulan_ini' => $aktual_investasi_transport_bi, 
        'rat_bulan_ini' => $budget_investasi_transport_bi, 
        'rab_bulan_ini' => $budget_investasi_transport_bi, 
        'persen_bulan_ini' => $persen_investasi_transport_bi."%",
        'aktual_bulan_lalu' => $aktual_investasi_transport_sdbl, 
        'rat_bulan_lalu' => $budget_investasi_transport_sdbl, 
        'rab_bulan_lalu' => $budget_investasi_transport_sdbl, 
        'persen_bulan_lalu' => $persen_investasi_transport_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_investasi_transport_sdbi, 
        'rat_bulan_sdbi' => $budget_investasi_transport_sdbi, 
        'rab_bulan_sdbi' => $budget_investasi_transport_sdbi, 
        'persen_bulan_sdbi' => $persen_investasi_transport_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_investasi_transport_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_investasi_transport_setahun, 
        'persen' => $persen_investasi_transport_setahun."%"
    ],
    'rowcontent',
    ''
);

$tabelHtml .= buildTableRowWithUnit(
    'Biaya per Hektar',
    'Rp',
    [
        'aktual_bulan_ini' => $aktual_investasi_per_ha_bi, 
        'rat_bulan_ini' => $budget_investasi_per_ha_bi, 
        'rab_bulan_ini' => $budget_investasi_per_ha_bi, 
        'persen_bulan_ini' => $persen_investasi_per_ha_bi."%",
        'aktual_bulan_lalu' => $aktual_investasi_per_ha_sdbl, 
        'rat_bulan_lalu' => $budget_investasi_per_ha_sdbl, 
        'rab_bulan_lalu' => $budget_investasi_per_ha_sdbl, 
        'persen_bulan_lalu' => $persen_investasi_per_ha_sdbl."%",
        'aktual_bulan_sdbi' => $aktual_investasi_per_ha_sdbi, 
        'rat_bulan_sdbi' => $budget_investasi_per_ha_sdbi, 
        'rab_bulan_sdbi' => $budget_investasi_per_ha_sdbi, 
        'persen_bulan_sdbi' => $persen_investasi_per_ha_sdbi."%",
        'sdbi_tahun_lalu' => $aktual_investasi_per_ha_sdbi_tl, 
        'anggaran_tahun_ini' => $budget_investasi_per_ha_setahun, 
        'persen' => $persen_investasi_per_ha_setahun."%"
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

$total_aktual_biaya_tidak_langsung_bi = $aktual_biaya_sosial_bi + $aktual_biaya_investasi_bi;
$total_aktual_biaya_tidak_langsung_sdbl = $aktual_biaya_sosial_sdbl + $aktual_biaya_investasi_sdbl;
$total_aktual_biaya_tidak_langsung_sdbi = $aktual_biaya_sosial_sdbi + $aktual_biaya_investasi_sdbi;
$total_aktual_biaya_tidak_langsung_sdbi_tl = $aktual_biaya_sosial_sdbi_tl + $aktual_biaya_investasi_sdbi_tl;

$total_budget_biaya_tidak_langsung_bi = $budget_sosial_total_bi + $budget_investasi_total_bi;
$total_budget_biaya_tidak_langsung_sdbl = $budget_sosial_total_sdbl + $budget_investasi_total_sdbl;
$total_budget_biaya_tidak_langsung_sdbi = $budget_sosial_total_sdbi + $budget_investasi_total_sdbi;
$total_budget_biaya_tidak_langsung_sdbi_tl = $budget_sosial_total_setahun + $budget_investasi_total_setahun;

$tabelHtml .= buildTableRowWithUnit(
    'Total Biaya Tidak Langsung',
    'Rp',
    [
        'aktual_bulan_ini' => $total_aktual_biaya_tidak_langsung_bi, 
        'rat_bulan_ini' => $total_budget_biaya_tidak_langsung_bi, 
        'rab_bulan_ini' => $total_budget_biaya_tidak_langsung_bi, 
        'persen_bulan_ini' => calcPercent($total_aktual_biaya_tidak_langsung_bi, $total_budget_biaya_tidak_langsung_bi )."%",
        'aktual_bulan_lalu' => $total_aktual_biaya_tidak_langsung_sdbl, 
        'rat_bulan_lalu' => $total_budget_biaya_tidak_langsung_sdbl, 
        'rab_bulan_lalu' => $total_budget_biaya_tidak_langsung_sdbl, 
        'persen_bulan_lalu' => calcPercent($total_aktual_biaya_tidak_langsung_sdbl, $total_budget_biaya_tidak_langsung_sdbl )."%",
        'aktual_bulan_sdbi' => $total_aktual_biaya_tidak_langsung_sdbi, 
        'rat_bulan_sdbi' => $total_budget_biaya_tidak_langsung_sdbi, 
        'rab_bulan_sdbi' => $total_budget_biaya_tidak_langsung_sdbi, 
        'persen_bulan_sdbi' => calcPercent($total_aktual_biaya_tidak_langsung_sdbi, $total_budget_biaya_tidak_langsung_sdbi )."%",
        'sdbi_tahun_lalu' => $total_aktual_biaya_tidak_langsung_sdbi_tl, 
        'anggaran_tahun_ini' => $total_budget_biaya_tidak_langsung_setahun, 
        'persen' => calcPercent($total_aktual_biaya_tidak_langsung_sdbi, $total_budget_biaya_tidak_langsung_setahun )."%"
    ],
    'rowcontent',
    'background-color: #f2f2f2; font-weight:bold;'
);

$total_biaya_aktual_bi = $total_aktual_biaya_langsung_bi + $total_aktual_biaya_tidak_langsung_bi;
$total_biaya_aktual_sdbl = $total_aktual_biaya_langsung_sdbl + $total_aktual_biaya_tidak_langsung_sdbl;
$total_biaya_aktual_sdbi = $total_aktual_biaya_langsung_sdbi + $total_aktual_biaya_tidak_langsung_sdbi;
$total_biaya_aktual_sdbi_tl = $total_aktual_biaya_langsung_sdbi_tl + $total_aktual_biaya_tidak_langsung_sdbi_tl;

$total_biaya_budget_bi = $total_budget_biaya_langsung_bi + $total_budget_biaya_tidak_langsung_bi;
$total_biaya_budget_sdbl = $total_budget_biaya_langsung_sdbl + $total_budget_biaya_tidak_langsung_sdbl;
$total_biaya_budget_sdbi = $total_budget_biaya_langsung_sdbi + $total_budget_biaya_tidak_langsung_sdbi;
$total_biaya_budget_sdbi_tl = $total_budget_biaya_langsung_sdbi_tl + $total_budget_biaya_tidak_langsung_sdbi_tl;

$tabelHtml .= buildTableRowWithUnit(
    'Total Biaya',
    'Rp',
    [
        'aktual_bulan_ini' => $total_biaya_aktual_bi, 
        'rat_bulan_ini' => $total_biaya_budget_bi, 
        'rab_bulan_ini' => $total_biaya_budget_bi, 
        'persen_bulan_ini' => calcPercent($total_biaya_aktual_bi, $total_biaya_budget_bi )."%",
        'aktual_bulan_lalu' => $total_biaya_aktual_sdbl, 
        'rat_bulan_lalu' => $total_biaya_budget_sdbl, 
        'rab_bulan_lalu' => $total_biaya_budget_sdbl, 
        'persen_bulan_lalu' => calcPercent($total_biaya_aktual_sdbl, $total_biaya_budget_sdbl )."%",
        'aktual_bulan_sdbi' => $total_biaya_aktual_sdbi, 
        'rat_bulan_sdbi' => $total_biaya_budget_sdbi, 
        'rab_bulan_sdbi' => $total_biaya_budget_sdbi, 
        'persen_bulan_sdbi' => calcPercent($total_biaya_aktual_sdbi, $total_biaya_budget_sdbi )."%",
        'sdbi_tahun_lalu' => $total_biaya_aktual_sdbi_tl, 
        'anggaran_tahun_ini' => $total_biaya_budget_sdbi_tl, 
        'persen' => calcPercent($total_biaya_aktual_sdbi, $total_biaya_budget_sdbi_tl )."%"
    ],
    'rowcontent',
    'background-color: #b4b4b4b4; font-weight:bold;'
);


$tabelHtml .= "</tbody></table>";

// ============================================================================
// OUTPUT HANDLER - PREVIEW vs EXCEL
// ============================================================================

switch ($proses) {
    case 'preview':
        $tabelHtml .= "</tbody></table></fieldset>";
        echo $tabelHtml;
        echo "<script>renderChart();</script>";
        break;
        
    case 'excel':
        $tabelHtml .= "Print Time: ".date('Y-m-d H:i:s')."<br>By: ".$_SESSION['empl']['name'];	
        $namaFileExcel = "Laporan_Analisis_Biaya_Kebun_".str_replace(',', '_', $kode_org_raw)."_".$periode->format('Y-m');
        
        if (strlen($tabelHtml) > 0) {
            // Clear temp directory
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }
            
            // Write file
            $handle = fopen("tempExcel/".$namaFileExcel.".xls", 'w');
            if (!fwrite($handle, $tabelHtml)) {
                echo "<script>
                parent.window.alert('Gagal convert ke excel format');
                </script>";
                exit;
            } else {
                echo "<script>
                window.location='tempExcel/".$namaFileExcel.".xls';
                </script>";
            }
            fclose($handle);
        }
        break;
        
    default:
        // No output for unknown process
        break;
}