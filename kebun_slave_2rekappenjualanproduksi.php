<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$kdpt = checkPostGet('kdpt', checkPostGet('kdorg', ''));
$periode = checkPostGet('periode', '');

if ($kdpt == '') {
    exit("Warning: PT harus dipilih");
}
if ($periode == '') {
    exit("Warning: Periode harus dipilih");
}

$tahun = substr($periode, 0, 4);
if (!$tahun || !is_numeric($tahun)) {
    $tahun = '2026';
}
$tahunLalu = (int)$tahun - 1;

$bulanAngka = (int)substr($periode, 5, 2);
$arrBulan = [
    1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
    5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
    9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER'
];
$namaBulan = $arrBulan[$bulanAngka] ?? 'JANUARI';

// 1. Ambil data nama PT
$strPT = "SELECT kodeorganisasi, namaorganisasi, alamat FROM " . $dbname . ".organisasi WHERE kodeorganisasi = '" . $kdpt . "'";
$resPT = $owlPDO->query($strPT);
$barPT = $resPT->fetch(PDO::FETCH_ASSOC);
$namaPT = $barPT['namaorganisasi'] ?? $kdpt;
$alamatPT = $barPT['alamat'] ?? $kdpt;

// 2. Ambil semua unit kebun yang terkait dengan PT yang dipilih
$arrUnitOrg = [];
$strU = "SELECT kodeorganisasi, namaorganisasi FROM " . $dbname . ".organisasi 
         WHERE induk = '" . $kdpt . "' AND tipe = 'KEBUN' 
         ORDER BY kodeorganisasi ASC";
$resU = $owlPDO->query($strU);
while ($bar = $resU->fetch(PDO::FETCH_ASSOC)) {
    $arrUnitOrg[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
}
if (empty($arrUnitOrg)) {
    $strU2 = "SELECT kodeorganisasi, namaorganisasi FROM " . $dbname . ".organisasi 
              WHERE induk = '" . $kdpt . "' ORDER BY kodeorganisasi ASC";
    $resU2 = $owlPDO->query($strU2);
    while ($bar = $resU2->fetch(PDO::FETCH_ASSOC)) {
        $arrUnitOrg[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
    }
    if (empty($arrUnitOrg)) {
        $arrUnitOrg[$kdpt] = $namaPT;
    }
}

$displayUnits = $arrUnitOrg;
$inUnits = "'" . implode("','", array_keys($displayUnits)) . "'";

// 3. Realisasi Produksi Berjalan dari pabrik_timbangan & kebun_spbht
$sqlReal = "SELECT a.kodeorg,
                   COALESCE(NULLIF(b.penerimatbs, ''), NULLIF(a.kodecustomer, ''), a.millcode) as customer,
                   SUM(a.beratbersih) as bruto,
                   SUM(a.kgpotsortasi) as pot_mutu,
                   SUM(a.beratbersih - a.kgpotsortasi) as netto
            FROM " . $dbname . ".pabrik_timbangan a
            LEFT JOIN " . $dbname . ".kebun_spbht b ON a.nospb = b.nospb
            WHERE a.tanggal LIKE '" . $periode . "%' 
              AND a.kodebarang = '40000003'
              AND a.kodeorg IN (" . $inUnits . ")
            GROUP BY a.kodeorg, customer";
$optCust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$optMill = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "tipe='PABRIK'");

$resReal = $owlPDO->query($sqlReal);
$dataProd = [];
$dataPKS = [];
$listPenerima = [];
while ($r = $resReal->fetch(PDO::FETCH_ASSOC)) {
    $u = $r['kodeorg'];
    $cust = strtoupper(trim($r['customer']));
    if ($cust === '') {
        $cust = 'LAINNYA';
    }
    if (!in_array($cust, $listPenerima)) {
        $listPenerima[] = $cust;
    }

    if (!isset($dataProd[$u])) {
        $dataProd[$u] = ['bruto' => 0, 'pot_mutu' => 0, 'netto' => 0];
    }
    $dataProd[$u]['bruto'] += (float)$r['bruto'];
    $dataProd[$u]['pot_mutu'] += (float)$r['pot_mutu'];
    $dataProd[$u]['netto'] += (float)$r['netto'];

    if (!isset($dataPKS[$u])) {
        $dataPKS[$u] = [];
    }
    if (!isset($dataPKS[$u][$cust])) {
        $dataPKS[$u][$cust] = 0;
    }
    // Rincian tonase penjualan PKS dihitung dari tonase netto yang diterima pabrik
    $dataPKS[$u][$cust] += (float)$r['netto'];
}
sort($listPenerima);

// 4. RAT (Budget Produksi) dari bgt_produksi_kbn_kg_vw
$kolomBgt = 'kg' . str_pad($bulanAngka, 2, '0', STR_PAD_LEFT);
$sqlRat = "SELECT kodeunit, SUM(" . $kolomBgt . ") as rat 
           FROM " . $dbname . ".bgt_produksi_kbn_kg_vw 
           WHERE tahunbudget = '" . $tahun . "' AND kodeunit IN (" . $inUnits . ")
           GROUP BY kodeunit";
$resRat = $owlPDO->query($sqlRat);
$dataRat = [];
while ($r = $resRat->fetch(PDO::FETCH_ASSOC)) {
    $dataRat[$r['kodeunit']] = (float)$r['rat'];
}

// 5. Sensus Produksi dari kebun_rencanapanen
$sqlSensus = "SELECT SUBSTRING(kodeorg, 1, 4) as unit, SUM(kgsensus) as sensus 
              FROM " . $dbname . ".kebun_rencanapanen 
              WHERE tahun = '" . $tahun . "' AND (bulan = '" . $bulanAngka . "' OR bulan = '" . str_pad($bulanAngka, 2, '0', STR_PAD_LEFT) . "')
                AND SUBSTRING(kodeorg, 1, 4) IN (" . $inUnits . ")
              GROUP BY SUBSTRING(kodeorg, 1, 4)";
$resSensus = $owlPDO->query($sqlSensus);
$dataSensus = [];
while ($r = $resSensus->fetch(PDO::FETCH_ASSOC)) {
    $dataSensus[$r['unit']] = (float)$r['sensus'];
}

// 6. Realisasi Periode Sama Tahun Lalu dari pabrik_timbangan
$periodeLalu = $tahunLalu . '-' . str_pad($bulanAngka, 2, '0', STR_PAD_LEFT);
$sqlLalu = "SELECT kodeorg, 
                   SUM(beratbersih) as bruto_lalu, 
                   SUM(beratbersih - kgpotsortasi) as netto_lalu 
            FROM " . $dbname . ".pabrik_timbangan 
            WHERE tanggal LIKE '" . $periodeLalu . "%' 
              AND kodebarang = '40000003'
              AND kodeorg IN (" . $inUnits . ")
            GROUP BY kodeorg";
$resLalu = $owlPDO->query($sqlLalu);
$dataLalu = [];
while ($r = $resLalu->fetch(PDO::FETCH_ASSOC)) {
    $dataLalu[$r['kodeorg']] = [
        'bruto' => (float)$r['bruto_lalu'],
        'netto' => (float)$r['netto_lalu']
    ];
}

// Susun array data untuk Tabel 1 dan Tabel 2
$dataTabel1 = [];
$dataTabel2 = [];
$no = 0;
foreach ($displayUnits as $kd => $nm) {
    $no++;
    $bruto = $dataProd[$kd]['bruto'] ?? 0;
    $pot = $dataProd[$kd]['pot_mutu'] ?? 0;
    $netto = $dataProd[$kd]['netto'] ?? 0;
    $rat = $dataRat[$kd] ?? 0;
    $sensus = $dataSensus[$kd] ?? 0;
    $brutoLalu = $dataLalu[$kd]['bruto'] ?? 0;
    $nettoLalu = $dataLalu[$kd]['netto'] ?? 0;

    $dataTabel1[] = [
        'no' => ($no == 1) ? 1 : '',
        'bulan' => $namaBulan,
        'kebun' => $nm,
        'bruto' => $bruto,
        'pot_mutu' => $pot,
        'netto' => $netto,
        'rat' => $rat,
        'sensus' => $sensus,
        'real_bruto_lalu' => $brutoLalu,
        'real_netto_lalu' => $nettoLalu,
        'ket' => '-'
    ];

    // Distribusi PKS untuk Tabel 2 (Tonase Netto per Penerima TBS)
    $dataTabel2[] = [
        'no' => ($no == 1) ? 1 : '',
        'bulan' => $namaBulan,
        'kebun' => $nm,
        'kdorg' => $kd,
        'penerima' => $dataPKS[$kd] ?? []
    ];
}

// Styling border dan background header tabel
$border = ($proses == 'excel') ? "border='1'" : "border='0'";
$excelHeaderBg = ($proses == 'excel') ? "bgcolor='#275370' style='color:#FFFFFF; font-weight:bold; background-color:#275370;'" : "";

$stream = "";

// Header Judul Laporan
$stream .= "<table border='0' width='100%' style='width:100%; margin-bottom:12px; font-family:sans-serif;'>
    <tr><td colspan='19' align='left' style='font-size:12px; font-weight:bold;'>Perusahaan Perkebunan Kelapa Sawit</td></tr>
    <tr><td colspan='19' align='left' style='font-size:13px; font-weight:bold;'>" . $namaPT . "</td></tr>
    <tr><td colspan='19' align='left' style='font-size:11px; font-weight:bold;'>" . $alamatPT . "</td></tr>
    <tr><td colspan='19'>&nbsp;</td></tr>
    <tr><td colspan='19' align='center' style='text-align:center; font-size:14px; font-weight:bold;'><center>REALISASI DAN ANALISA PRODUKSI TAHUN " . $tahun . "</center></td></tr>
    <tr><td colspan='19' align='center' style='text-align:center; font-size:12px; font-weight:bold;'><center>PERIODE : " . $namaBulan . " " . $tahun . "</center></td></tr>
</table>";

// TABEL 1: Realisasi dan Analisa Produksi
$stream .= "<table cellspacing='1' cellpadding='3' class='sortable' " . $border . " style='border-collapse:collapse; font-size:11px; width:100%;'>
    <thead style='position:static !important;'>
        <tr class='rowheader'>
            <th rowspan='3' align='center' " . $excelHeaderBg . ">NO</th>
            <th rowspan='3' align='center' " . $excelHeaderBg . ">BULAN</th>
            <th rowspan='3' align='center' " . $excelHeaderBg . ">KEBUN</th>
            <th colspan='7' align='center' " . $excelHeaderBg . ">PRODUKSI " . $tahun . "</th>
            <th colspan='4' align='center' " . $excelHeaderBg . ">PRESENTASE PRODUKSI BRUTO</th>
            <th colspan='4' align='center' " . $excelHeaderBg . ">PRESENTASE PRODUKSI NETTO</th>
            <th rowspan='3' align='center' " . $excelHeaderBg . ">Ket</th>
        </tr>
        <tr class='rowheader'>
            <th colspan='3' align='center' " . $excelHeaderBg . ">REALISASI " . $tahun . "</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">RAT " . $tahun . "</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">SENSUS " . $tahun . "</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">REALISASI BRUTO " . $tahunLalu . "</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">REALISASI NETTO " . $tahunLalu . "</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">% Pot Mutu<br>Bruto</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">% RAT " . $tahun . "</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">% SENSUS<br>" . $tahun . "</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">% REALISASI<br>" . $tahunLalu . "</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">% Pot Mutu<br>Netto</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">% RAT " . $tahun . "</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">% SENSUS<br>" . $tahun . "</th>
            <th rowspan='2' align='center' " . $excelHeaderBg . ">% REALISASI<br>" . $tahunLalu . "</th>
        </tr>
        <tr class='rowheader'>
            <th align='center' " . $excelHeaderBg . ">Bruto</th>
            <th align='center' " . $excelHeaderBg . ">Pot.Mutu</th>
            <th align='center' " . $excelHeaderBg . ">Netto</th>
        </tr>
    </thead>
    <tbody>";

$tot_bruto = 0;
$tot_pot_mutu = 0;
$tot_netto = 0;
$tot_rat = 0;
$tot_sensus = 0;
$tot_real_bruto_lalu = 0;
$tot_real_netto_lalu = 0;

$no1 = 0;
foreach ($dataTabel1 as $row) {
    $no1++;
    $tot_bruto += $row['bruto'];
    $tot_pot_mutu += $row['pot_mutu'];
    $tot_netto += $row['netto'];
    $tot_rat += $row['rat'];
    $tot_sensus += $row['sensus'];
    $tot_real_bruto_lalu += $row['real_bruto_lalu'];
    $tot_real_netto_lalu += $row['real_netto_lalu'];

    // Hitung persentase baris
    $pct_pot_bruto = ($row['bruto'] > 0) ? ($row['pot_mutu'] / $row['bruto']) * 100 : 0;
    $pct_rat_bruto = ($row['rat'] > 0) ? ($row['bruto'] / $row['rat']) * 100 : 0;
    $pct_sensus_bruto = ($row['sensus'] > 0) ? ($row['bruto'] / $row['sensus']) * 100 : 0;
    $pct_lalu_bruto = ($row['real_bruto_lalu'] > 0) ? ($row['bruto'] / $row['real_bruto_lalu']) * 100 : 0;

    $pct_pot_netto = ($row['netto'] > 0) ? ($row['pot_mutu'] / $row['netto']) * 100 : 0;
    $pct_rat_netto = ($row['rat'] > 0) ? ($row['netto'] / $row['rat']) * 100 : 0;
    $pct_sensus_netto = ($row['sensus'] > 0) ? ($row['netto'] / $row['sensus']) * 100 : 0;
    $pct_lalu_netto = ($row['real_netto_lalu'] > 0) ? ($row['netto'] / $row['real_netto_lalu']) * 100 : 0;

    $stream .= "<tr class='rowcontent' id='row_t1_" . $no1 . "' onclick='getmark(this.id);'>
        <td align='center'>" . $row['no'] . "</td>
        <td align='center'>" . $row['bulan'] . "</td>
        <td align='left'>" . $row['kebun'] . "</td>
        <td align='right'>" . (($row['bruto'] > 0) ? number_format($row['bruto'], 0) : '-') . "</td>
        <td align='right'>" . (($row['pot_mutu'] > 0) ? number_format($row['pot_mutu'], 0) : '-') . "</td>
        <td align='right'>" . (($row['netto'] > 0) ? number_format($row['netto'], 0) : '-') . "</td>
        <td align='right'>" . (($row['rat'] > 0) ? number_format($row['rat'], 0) : '-') . "</td>
        <td align='right'>" . (($row['sensus'] > 0) ? number_format($row['sensus'], 0) : '-') . "</td>
        <td align='right'>" . (($row['real_bruto_lalu'] > 0) ? number_format($row['real_bruto_lalu'], 0) : '-') . "</td>
        <td align='right'>" . (($row['real_netto_lalu'] > 0) ? number_format($row['real_netto_lalu'], 0) : '-') . "</td>
        <td align='right'>" . (($pct_pot_bruto > 0) ? number_format($pct_pot_bruto, 2) . '%' : '-') . "</td>
        <td align='right'>" . (($pct_rat_bruto > 0) ? number_format($pct_rat_bruto, 2) . '%' : '-') . "</td>
        <td align='right'>" . (($pct_sensus_bruto > 0) ? number_format($pct_sensus_bruto, 2) . '%' : '-') . "</td>
        <td align='right'>" . (($pct_lalu_bruto > 0) ? number_format($pct_lalu_bruto, 2) . '%' : '-') . "</td>
        <td align='right'>" . (($pct_pot_netto > 0) ? number_format($pct_pot_netto, 2) . '%' : '-') . "</td>
        <td align='right'>" . (($pct_rat_netto > 0) ? number_format($pct_rat_netto, 2) . '%' : '-') . "</td>
        <td align='right'>" . (($pct_sensus_netto > 0) ? number_format($pct_sensus_netto, 2) . '%' : '-') . "</td>
        <td align='right'>" . (($pct_lalu_netto > 0) ? number_format($pct_lalu_netto, 2) . '%' : '-') . "</td>
        <td align='center'>" . $row['ket'] . "</td>
    </tr>";
}

// Total JUMLAH Tabel 1
$tot_pct_pot_bruto = ($tot_bruto > 0) ? ($tot_pot_mutu / $tot_bruto) * 100 : 0;
$tot_pct_rat_bruto = ($tot_rat > 0) ? ($tot_bruto / $tot_rat) * 100 : 0;
$tot_pct_sensus_bruto = ($tot_sensus > 0) ? ($tot_bruto / $tot_sensus) * 100 : 0;
$tot_pct_lalu_bruto = ($tot_real_bruto_lalu > 0) ? ($tot_bruto / $tot_real_bruto_lalu) * 100 : 0;

$tot_pct_pot_netto = ($tot_netto > 0) ? ($tot_pot_mutu / $tot_netto) * 100 : 0;
$tot_pct_rat_netto = ($tot_rat > 0) ? ($tot_netto / $tot_rat) * 100 : 0;
$tot_pct_sensus_netto = ($tot_sensus > 0) ? ($tot_netto / $tot_sensus) * 100 : 0;
$tot_pct_lalu_netto = ($tot_real_netto_lalu > 0) ? ($tot_netto / $tot_real_netto_lalu) * 100 : 0;

$stream .= "<tr class='rowcontent' bgcolor='#F2F2F2' style='font-weight:bold; background-color:#F2F2F2;'>
    <td align='left' colspan='3'><b>JUMLAH</b></td>
    <td align='right'><b>" . number_format($tot_bruto, 0) . "</b></td>
    <td align='right'><b>" . number_format($tot_pot_mutu, 0) . "</b></td>
    <td align='right'><b>" . number_format($tot_netto, 0) . "</b></td>
    <td align='right'><b>" . number_format($tot_rat, 0) . "</b></td>
    <td align='right'><b>" . number_format($tot_sensus, 0) . "</b></td>
    <td align='right'><b>" . number_format($tot_real_bruto_lalu, 0) . "</b></td>
    <td align='right'><b>" . number_format($tot_real_netto_lalu, 0) . "</b></td>
    <td align='right'><b>" . number_format($tot_pct_pot_bruto, 2) . "%</b></td>
    <td align='right'><b>" . number_format($tot_pct_rat_bruto, 2) . "%</b></td>
    <td align='right'><b>" . number_format($tot_pct_sensus_bruto, 2) . "%</b></td>
    <td align='right'><b>" . number_format($tot_pct_lalu_bruto, 2) . "%</b></td>
    <td align='right'><b>" . number_format($tot_pct_pot_netto, 2) . "%</b></td>
    <td align='right'><b>" . number_format($tot_pct_rat_netto, 2) . "%</b></td>
    <td align='right'><b>" . number_format($tot_pct_sensus_netto, 2) . "%</b></td>
    <td align='right'><b>" . number_format($tot_pct_lalu_netto, 2) . "%</b></td>
    <td align='center'>-</td>
</tr>";
$stream .= "</tbody></table>";

// TABEL 2: Rincian Penjualan Produksi per PKS / Customer (Dinamis sesuai penerima TBS)
if (empty($listPenerima)) {
    $listPenerima = ['-'];
}
$colSpanT2 = max(5, count($listPenerima) + 4);

$stream .= "<br><br>";
$stream .= "<table cellspacing='1' cellpadding='3' class='sortable' " . $border . " style='border-collapse:collapse; font-size:11px; width:100%;'>
    <thead style='position:static !important;'>
        <tr class='rowheader'>
            <th colspan='" . $colSpanT2 . "' align='center' " . $excelHeaderBg . ">PRODUKSI " . $tahun . "</th>
        </tr>
        <tr class='rowheader'>
            <th align='center' " . $excelHeaderBg . ">No</th>
            <th align='center' " . $excelHeaderBg . ">Bulan</th>
            <th align='center' " . $excelHeaderBg . ">Kebun</th>";

foreach ($listPenerima as $cust) {
    $custLabel = $cust;
    if (!empty($optCust[$cust])) {
        $custLabel = $cust . "<br><span style='font-size:9px; font-weight:normal;'>" . $optCust[$cust] . "</span>";
    } else if (!empty($optMill[$cust])) {
        $custLabel = $cust . "<br><span style='font-size:9px; font-weight:normal;'>" . $optMill[$cust] . "</span>";
    }
    $stream .= "<th align='center' " . $excelHeaderBg . ">" . $custLabel . "</th>";
}

$stream .= "<th align='center' " . $excelHeaderBg . ">Total</th>
        </tr>
    </thead>
    <tbody>";

$totColPenerima = [];
$totGrandPKS = 0;

$no2 = 0;
foreach ($dataTabel2 as $row) {
    $no2++;
    $rowTotal = 0;

    $stream .= "<tr class='rowcontent' id='row_pks_" . $no2 . "' onclick='getmark(this.id);'>
        <td align='center'>" . $row['no'] . "</td>
        <td align='center'>" . $row['bulan'] . "</td>
        <td align='left'>" . $row['kebun'] . "</td>";

    foreach ($listPenerima as $cust) {
        $val = $row['penerima'][$cust] ?? 0;
        $rowTotal += $val;
        if (!isset($totColPenerima[$cust])) {
            $totColPenerima[$cust] = 0;
        }
        $totColPenerima[$cust] += $val;

        $strVal = ($val > 0) ? number_format($val, 0) : '-';
        $stream .= "<td align='right'>" . $strVal . "</td>";
    }

    $totGrandPKS += $rowTotal;
    $strTotal = ($rowTotal > 0) ? number_format($rowTotal, 0) : '-';
    $stream .= "<td align='right' style='font-weight:bold;'>" . $strTotal . "</td>
    </tr>";
}

// Baris kosong placeholder sebelum JUMLAH seperti pada template
$stream .= "<tr class='rowcontent'>
    <td>&nbsp;</td>
    <td></td>
    <td></td>";
foreach ($listPenerima as $cust) {
    $stream .= "<td></td>";
}
$stream .= "<td align='center'>-</td>
</tr>";

// Baris JUMLAH Tabel 2
$stream .= "<tr class='rowcontent' bgcolor='#F2F2F2' style='font-weight:bold; background-color:#F2F2F2;'>
    <td align='left' colspan='3'><b>JUMLAH</b></td>";

foreach ($listPenerima as $cust) {
    $colSum = $totColPenerima[$cust] ?? 0;
    $strColSum = ($colSum > 0) ? number_format($colSum, 0) : '-';
    $stream .= "<td align='right'><b>" . $strColSum . "</b></td>";
}

$stream .= "<td align='right'><b>" . number_format($totGrandPKS, 0) . "</b></td>
</tr>";

$stream .= "</tbody></table>";

if ($proses == 'excel') {
    // TABEL TANDA TANGAN DINAMIS DARI SETUP_2TTD
    $arrBlnTtd = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $tglPrintTtd = date('d') . ' ' . ($arrBlnTtd[(int)date('m')] ?? date('F')) . ' ' . date('Y');
    $lokasiTtd = (stripos($kdpt, 'kebun') !== false) ? $kdpt : 'Kebun ' . $kdpt;
    $headerTglTtd = $lokasiTtd . ', ' . $tglPrintTtd;

    // Ambil menuid dari tabel menu untuk kebun_2rekappenjualanproduksi
    $menuIdRekap = '';
    try {
        $sMenu = "SELECT id FROM " . $dbname . ".menu WHERE action LIKE '%kebun_2rekappenjualanproduksi%' LIMIT 1";
        $qMenu = $owlPDO->query($sMenu);
        if ($qMenu && $rMenu = $qMenu->fetch(PDO::FETCH_ASSOC)) {
            $menuIdRekap = $rMenu['id'];
        }
    } catch (Exception $e) {}

    // Cari kode unit HO dari PT (misal DMA -> DMHO)
    $kodeHo = '';
    try {
        $sHo = "SELECT kodeorganisasi FROM " . $dbname . ".organisasi 
                WHERE induk = '" . $kdpt . "' 
                  AND (tipe = 'HOLDING' OR tipe = 'KANWIL' OR kodeorganisasi LIKE '%HO') 
                ORDER BY CASE WHEN kodeorganisasi LIKE '%HO' THEN 0 ELSE 1 END LIMIT 1";
        $qHo = $owlPDO->query($sHo);
        if ($qHo && $rHo = $qHo->fetch(PDO::FETCH_ASSOC)) {
            $kodeHo = $rHo['kodeorganisasi'];
        }
    } catch (Exception $e) {}

    if (empty($kodeHo)) {
        $kodeHo = (strlen($kdpt) >= 3 ? substr($kdpt, 0, 2) : $kdpt) . 'HO';
    }

    // Query data tanda tangan dari setup_2ttd khusus unit HO dari PT
    $arrTtd = [];
    try {
        $whereMenu = "a.menuid = 'kebun_2rekappenjualanproduksi'";
        if (!empty($menuIdRekap)) {
            $whereMenu = "(a.menuid = '" . $menuIdRekap . "' OR a.menuid = 'kebun_2rekappenjualanproduksi')";
        }

        $whereUnit = "(a.kodeunit = '" . $kodeHo . "' OR a.kodeunit = '" . $kdpt . "')";

        $sTtd = "SELECT a.id, a.judul, a.jabatan, a.karyawanid, b.namakaryawan 
                 FROM " . $dbname . ".setup_2ttd a 
                 LEFT JOIN " . $dbname . ".datakaryawan b ON a.karyawanid = b.karyawanid 
                 WHERE " . $whereMenu . " AND " . $whereUnit . " 
                 ORDER BY CASE WHEN a.kodeunit = '" . $kodeHo . "' THEN 0 ELSE 1 END, a.id ASC";
        $qTtd = $owlPDO->query($sTtd);
        if ($qTtd) {
            while ($rTtd = $qTtd->fetch(PDO::FETCH_ASSOC)) {
                $arrTtd[] = $rTtd;
            }
        }
    } catch (Exception $e) {}

    // Default 4 kolom jika setup belum ada
    $defaultCols = [
        0 => ['judul' => 'Diketahui', 'nama' => '', 'jabatan' => ''],
        1 => ['judul' => 'Diketahui', 'nama' => '', 'jabatan' => ''],
        2 => ['judul' => 'Diperiksa', 'nama' => '', 'jabatan' => ''],
        3 => ['judul' => 'Dibuat',    'nama' => '', 'jabatan' => '']
    ];

    for ($k = 0; $k < 4; $k++) {
        if (isset($arrTtd[$k])) {
            if (!empty($arrTtd[$k]['judul'])) {
                $defaultCols[$k]['judul'] = $arrTtd[$k]['judul'];
            }
            if (!empty($arrTtd[$k]['namakaryawan'])) {
                $defaultCols[$k]['nama'] = $arrTtd[$k]['namakaryawan'];
            }
            if (!empty($arrTtd[$k]['jabatan'])) {
                $defaultCols[$k]['jabatan'] = $arrTtd[$k]['jabatan'];
            }
        }
    }

    // Fallback kolom 'Dibuat': Jika tidak di-setup di setup_2ttd, otomatis ambil nama & jabatan user yang login/konversi Excel
    if (empty($defaultCols[3]['nama']) && !empty($_SESSION['standard']['userid'])) {
        $loginNama = getNamaKaryawan($_SESSION['standard']['userid']);
        $loginJabatan = getJabatanKaryawan($_SESSION['standard']['userid']);
        if (!empty($loginNama)) {
            $defaultCols[3]['nama'] = $loginNama;
            $defaultCols[3]['jabatan'] = (!empty($loginJabatan)) ? $loginJabatan : '';
        }
    }

    $stream .= "<br><br>";
    $stream .= "<table border='0' cellpadding='1' cellspacing='0' style='border-collapse:collapse; font-size:11px; width:100%; font-family:sans-serif;'>
        <tr>
            <td colspan='15'></td>
            <td colspan='4' align='center'>" . $headerTglTtd . "</td>
        </tr>
        <tr>
            <td colspan='4' align='center'>" . $defaultCols[0]['judul'] . "</td>
            <td></td>
            <td colspan='4' align='center'>" . $defaultCols[1]['judul'] . "</td>
            <td></td>
            <td colspan='4' align='center'>" . $defaultCols[2]['judul'] . "</td>
            <td></td>
            <td colspan='4' align='center'>" . $defaultCols[3]['judul'] . "</td>
        </tr>
        <tr>
            <td colspan='4' style='height:20px;'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td>
        </tr>
        <tr>
            <td colspan='4' style='height:20px;'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td>
        </tr>
        <tr>
            <td colspan='4' style='height:20px;'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td>
        </tr>
        <tr>
            <td colspan='4' style='height:20px;'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td><td></td>
            <td colspan='4'>&nbsp;</td>
        </tr>
        <tr>
            <td colspan='4' align='center'>" . (!empty($defaultCols[0]['nama']) ? "<u><b>" . $defaultCols[0]['nama'] . "</b></u>" : "&nbsp;") . "</td>
            <td></td>
            <td colspan='4' align='center'>" . (!empty($defaultCols[1]['nama']) ? "<u><b>" . $defaultCols[1]['nama'] . "</b></u>" : "&nbsp;") . "</td>
            <td></td>
            <td colspan='4' align='center'>" . (!empty($defaultCols[2]['nama']) ? "<u><b>" . $defaultCols[2]['nama'] . "</b></u>" : "&nbsp;") . "</td>
            <td></td>
            <td colspan='4' align='center'>" . (!empty($defaultCols[3]['nama']) ? "<u><b>" . $defaultCols[3]['nama'] . "</b></u>" : "&nbsp;") . "</td>
        </tr>
        <tr>
            <td colspan='4' align='center'>" . (!empty($defaultCols[0]['jabatan']) ? $defaultCols[0]['jabatan'] : "&nbsp;") . "</td>
            <td></td>
            <td colspan='4' align='center'>" . (!empty($defaultCols[1]['jabatan']) ? $defaultCols[1]['jabatan'] : "&nbsp;") . "</td>
            <td></td>
            <td colspan='4' align='center'>" . (!empty($defaultCols[2]['jabatan']) ? $defaultCols[2]['jabatan'] : "&nbsp;") . "</td>
            <td></td>
            <td colspan='4' align='center'>" . (!empty($defaultCols[3]['jabatan']) ? $defaultCols[3]['jabatan'] : "&nbsp;") . "</td>
        </tr>
    </table>";
}

switch ($proses) {
    case 'preview':
        echo $stream;
        break;

    case 'excel':
        $nop_ = "rekap_penjualan_produksi_" . $kdpt . "_" . $periode;
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
                        parent.window.alert('Can\'t convert to excel format');
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