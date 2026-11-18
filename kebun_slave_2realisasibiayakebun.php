<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt = trim(isset($_POST['pt']) ? $_POST['pt'] : (isset($_GET['pt']) ? $_GET['pt'] : ''));
$periode = trim(isset($_POST['periode']) ? $_POST['periode'] : (isset($_GET['periode']) ? $_GET['periode'] : ''));
$proses = trim(isset($_GET['proses']) ? $_GET['proses'] : (isset($_POST['proses']) ? $_POST['proses'] : 'preview'));

if (!preg_match('/^[A-Za-z0-9_-]{1,20}$/', $pt) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
    exit('Warning: PT atau Periode tidak valid!');
}
if (!in_array($proses, ['preview', 'excel'], true)) {
    exit('Warning: Proses tidak valid!');
}

function jalankanQuery(PDO $pdo, $sql, array $parameter = []) {
    try {
        $statement = $pdo->prepare($sql);
        $statement->execute($parameter);
        return $statement;
    } catch (Exception $exception) {
        error_log('Realisasi biaya kebun gagal: ' . $exception->getMessage());
        exit('Error SQL: ' . $exception->getMessage() . ' | Query: ' . $sql);
    }
}

$arrBulan = [
    '01' => 'JANUARI', '02' => 'FEBRUARI', '03' => 'MARET', '04' => 'APRIL',
    '05' => 'MEI', '06' => 'JUNI', '07' => 'JULI', '08' => 'AGUSTUS',
    '09' => 'SEPTEMBER', '10' => 'OKTOBER', '11' => 'NOVEMBER', '12' => 'DESEMBER'
];
list($thn, $bln) = explode('-', $periode);
$blnInt = (int)$bln;
$blnLaluInt = $blnInt - 1;
$namaBulan = $arrBulan[$bln];
$tanggalAwalTahun = $thn . '-01-01';
$tanggalAwalBulan = $periode . '-01';
$tanggalAwalBulanBerikut = date('Y-m-d', strtotime($tanggalAwalBulan . ' +1 month'));

$hasilNamaPt = jalankanQuery($owlPDO, "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi = :pt limit 1", [':pt' => $pt]);
$dataNamaPt = $hasilNamaPt->fetch(PDO::FETCH_ASSOC);
$namaPt = $dataNamaPt ? $dataNamaPt['namaorganisasi'] : $pt;

$hasilUnit = jalankanQuery($owlPDO, "select kodeorganisasi from " . $dbname . ".organisasi where induk = :pt", [':pt' => $pt]);
$daftarUnit = $hasilUnit->fetchAll(PDO::FETCH_COLUMN);
if (empty($daftarUnit)) {
    $daftarUnit = [$pt];
}
$placeholderUnit = implode(',', array_fill(0, count($daftarUnit), '?'));

$luasHa             = 0.00;
$jmlPokok           = 0;
$prodRatKg          = 0.00;
$prodRabBlnIniKg    = 0.00;
$prodRabBlnLaluKg   = 0.00;
$prodRabSdBlnIniKg  = 0.00;
$prodRealBlnIniKg   = 0.00;
$prodRealBlnLaluKg  = 0.00;
$prodRealSdBlnIniKg = 0.00;

$kondisiKodeOrg = [];
$parameterBlok = [];
foreach ($daftarUnit as $indeks => $kodeUnit) {
    $kondisiKodeOrg[] = "substr(kodeorg,1,4) = ?";
    $parameterBlok[] = $kodeUnit;
}
$sqlKondisiBlok = implode(' or ', $kondisiKodeOrg);

$parameterTm = array_merge($parameterBlok, ['TM', '%TM%']);
$hasilBlok = jalankanQuery($owlPDO, "select sum(luasareaproduktif) as luas, sum(jumlahpokok) as pokok from " . $dbname . ".setup_blok where (" . $sqlKondisiBlok . ") and (statusblok in (?) or statusblok like ?)", $parameterTm);
if ($dataBlok = $hasilBlok->fetch(PDO::FETCH_ASSOC)) {
    $luasHa   = (float)$dataBlok['luas'];
    $jmlPokok = (float)$dataBlok['pokok'];
}

if ($luasHa <= 0) {
    $hasilBlok = jalankanQuery($owlPDO, "select sum(luasareaproduktif) as luas, sum(jumlahpokok) as pokok from " . $dbname . ".setup_blok where " . $sqlKondisiBlok, $parameterBlok);
    if ($dataBlok = $hasilBlok->fetch(PDO::FETCH_ASSOC)) {
        $luasHa   = (float)$dataBlok['luas'];
        $jmlPokok = (float)$dataBlok['pokok'];
    }
}

$luasTbm     = 0.00;
$jmlPokokTbm = 0;
$parameterTbm = array_merge($parameterBlok, ['TBM']);
$hasilTbm = jalankanQuery($owlPDO, "select sum(luasareaproduktif) as luas, sum(jumlahpokok) as pokok from " . $dbname . ".setup_blok where (" . $sqlKondisiBlok . ") and statusblok in (?)", $parameterTbm);
if ($dataTbm = $hasilTbm->fetch(PDO::FETCH_ASSOC)) {
    $luasTbm     = (float)$dataTbm['luas'];
    $jmlPokokTbm = (float)$dataTbm['pokok'];
}

$kolomBudgetProduksi = ", sum(kg" . $bln . ") as bi";
$kolomBulanLaluSql = [];
$kolomSampaiBulanIniSql = [];
if ($blnLaluInt > 0) {
    for ($i = 1; $i <= $blnInt; $i++) {
        $kolomSampaiBulanIniSql[] = "kg" . str_pad($i, 2, '0', STR_PAD_LEFT);
        if ($i < $blnInt) {
            $kolomBulanLaluSql[] = "kg" . str_pad($i, 2, '0', STR_PAD_LEFT);
        }
    }
    $kolomBudgetProduksi = ", sum(" . implode('+', $kolomSampaiBulanIniSql) . ") as sdbi, sum(" . implode('+', $kolomBulanLaluSql) . ") as blnlalu";
} else {
    $kolomBudgetProduksi = ", sum(kg" . $bln . ") as sdbi, 0 as blnlalu";
}

$kondisiKodeBlok = [];
$parameterBudgetProduksi = [$thn];
foreach ($daftarUnit as $kodeUnit) {
    $kondisiKodeBlok[] = "substr(kodeblok,1,4) = ?";
    $parameterBudgetProduksi[] = $kodeUnit;
}
$kolomKgSetahun = [];
for ($i = 1; $i <= 12; $i++) {
    $kolomKgSetahun[] = "kg" . str_pad($i, 2, '0', STR_PAD_LEFT);
}
$sqlBudgetProduksi = "select sum(" . implode('+', $kolomKgSetahun) . ") as rat " . $kolomBudgetProduksi . " from " . $dbname . ".bgt_produksi_kebun where tahunbudget = ? and (" . implode(' or ', $kondisiKodeBlok) . ")";
$hasilBudgetProduksi = jalankanQuery($owlPDO, $sqlBudgetProduksi, $parameterBudgetProduksi);
if ($dataBudgetProduksi = $hasilBudgetProduksi->fetch(PDO::FETCH_ASSOC)) {
    $prodRatKg         = (float)$dataBudgetProduksi['rat'];
    $prodRabBlnIniKg   = (float)$dataBudgetProduksi['bi'];
    $prodRabBlnLaluKg  = isset($dataBudgetProduksi['blnlalu']) ? (float)$dataBudgetProduksi['blnlalu'] : 0.00;
    $prodRabSdBlnIniKg = (float)$dataBudgetProduksi['sdbi'];
}

$parameterSpbBlnIni = array_merge([$periode . '%'], $daftarUnit);
$sqlSpbBlnIni = "select sum(kgbjr) as kg from " . $dbname . ".kebun_spbdt where nospb in (select nospb from " . $dbname . ".kebun_spbht where tanggal like ? and kodeorg in (" . $placeholderUnit . "))";
$hasilSpbBlnIni = jalankanQuery($owlPDO, $sqlSpbBlnIni, $parameterSpbBlnIni);
if ($dataSpbBlnIni = $hasilSpbBlnIni->fetch(PDO::FETCH_ASSOC)) {
    $prodRealBlnIniKg = (float)$dataSpbBlnIni['kg'];
}

if ($blnLaluInt > 0) {
    $parameterSpbLalu = array_merge([$tanggalAwalTahun, $tanggalAwalBulan], $daftarUnit);
    $sqlSpbLalu = "select sum(kgbjr) as kg from " . $dbname . ".kebun_spbdt where nospb in (select nospb from " . $dbname . ".kebun_spbht where tanggal >= ? and tanggal < ? and kodeorg in (" . $placeholderUnit . "))";
    $hasilSpbLalu = jalankanQuery($owlPDO, $sqlSpbLalu, $parameterSpbLalu);
    if ($dataSpbLalu = $hasilSpbLalu->fetch(PDO::FETCH_ASSOC)) {
        $prodRealBlnLaluKg = (float)$dataSpbLalu['kg'];
    }
}
$prodRealSdBlnIniKg = $prodRealBlnIniKg + $prodRealBlnLaluKg;


$realisasiBulanIniPerAkun = [];
$realisasiBulanLaluPerAkun = [];
$realisasiBulanIniPerJenis = [];
$realisasiBulanLaluPerJenis = [];
$budgetTahunanPerAkun = [];
$budgetBulanIniPerAkun = [];
$budgetBulanLaluPerAkun = [];
$budgetTahunanPerJenis = [];
$budgetBulanIniPerJenis = [];
$budgetBulanLaluPerJenis = [];

$sqlRealisasi = "select j.noakun, coalesce(m.jenisvhc, '') as jenisvhc, sum(j.jumlah) as jml
                 from " . $dbname . ".keu_jurnaldt j
                 left join " . $dbname . ".vhc_5master m on m.kodevhc = j.kodevhc
                 where %s and j.kodeorg in (" . $placeholderUnit . ")
                 group by j.noakun, coalesce(m.jenisvhc, '')";
$hasilRealisasiBulanIni = jalankanQuery($owlPDO, sprintf($sqlRealisasi, "j.tanggal like ?"), array_merge([$periode . '%'], $daftarUnit));
while ($data = $hasilRealisasiBulanIni->fetch(PDO::FETCH_ASSOC)) {
    $akun = $data['noakun'];
    $jenis = $data['jenisvhc'];
    $jumlah = (float)$data['jml'];
    $realisasiBulanIniPerAkun[$akun] = isset($realisasiBulanIniPerAkun[$akun]) ? $realisasiBulanIniPerAkun[$akun] + $jumlah : $jumlah;
    if ($jenis !== '') $realisasiBulanIniPerJenis[$jenis][$akun] = $jumlah;
}

if ($blnLaluInt > 0) {
    $hasilRealisasiBulanLalu = jalankanQuery($owlPDO, sprintf($sqlRealisasi, "j.tanggal >= ? and j.tanggal < ?"), array_merge([$tanggalAwalTahun, $tanggalAwalBulan], $daftarUnit));
    while ($data = $hasilRealisasiBulanLalu->fetch(PDO::FETCH_ASSOC)) {
        $akun = $data['noakun'];
        $jenis = $data['jenisvhc'];
        $jumlah = (float)$data['jml'];
        $realisasiBulanLaluPerAkun[$akun] = isset($realisasiBulanLaluPerAkun[$akun]) ? $realisasiBulanLaluPerAkun[$akun] + $jumlah : $jumlah;
        if ($jenis !== '') $realisasiBulanLaluPerJenis[$jenis][$akun] = $jumlah;
    }
}

$kolomBudgetBulanIni = "rp" . $bln;
$sqlJumlahBudgetBulanLalu = "0";
if ($blnLaluInt > 0) {
    $kolomBudgetBulanLalu = [];
    for ($bi = 1; $bi < $blnInt; $bi++) $kolomBudgetBulanLalu[] = "b.rp" . str_pad($bi, 2, '0', STR_PAD_LEFT);
    $sqlJumlahBudgetBulanLalu = "sum(" . implode('+', $kolomBudgetBulanLalu) . ")";
}

$kondisiKodeOrgBudget = [];
$parameterBudgetDetail = [$thn];
foreach ($daftarUnit as $kodeUnit) {
    $kondisiKodeOrgBudget[] = "substr(b.kodeorg,1,4) = ?";
    $parameterBudgetDetail[] = $kodeUnit;
}
$sqlBudget = "select b.noakun, coalesce(m.jenisvhc, '') as jenisvhc, sum(b.rupiah) as rat,
                     sum(b." . $kolomBudgetBulanIni . ") as rab_bi, " . $sqlJumlahBudgetBulanLalu . " as rab_lalu
              from " . $dbname . ".bgt_budget_detail b
              left join " . $dbname . ".vhc_5master m on m.kodevhc = b.kodevhc
              where b.tahunbudget = ? and (" . implode(' or ', $kondisiKodeOrgBudget) . ")
              group by b.noakun, coalesce(m.jenisvhc, '')";
$hasilBudget = jalankanQuery($owlPDO, $sqlBudget, $parameterBudgetDetail);
while ($data = $hasilBudget->fetch(PDO::FETCH_ASSOC)) {
    $akun = $data['noakun'];
    $jenis = $data['jenisvhc'];
    $rat = (float)$data['rat'];
    $bulanIni = (float)$data['rab_bi'];
    $bulanLalu = (float)$data['rab_lalu'];
    $budgetTahunanPerAkun[$akun] = isset($budgetTahunanPerAkun[$akun]) ? $budgetTahunanPerAkun[$akun] + $rat : $rat;
    $budgetBulanIniPerAkun[$akun] = isset($budgetBulanIniPerAkun[$akun]) ? $budgetBulanIniPerAkun[$akun] + $bulanIni : $bulanIni;
    $budgetBulanLaluPerAkun[$akun] = isset($budgetBulanLaluPerAkun[$akun]) ? $budgetBulanLaluPerAkun[$akun] + $bulanLalu : $bulanLalu;
    if ($jenis !== '') {
        $budgetTahunanPerJenis[$jenis][$akun] = $rat;
        $budgetBulanIniPerJenis[$jenis][$akun] = $bulanIni;
        $budgetBulanLaluPerJenis[$jenis][$akun] = $bulanLalu;
    }
}

$semuaAkun = array_unique(array_merge(array_keys($realisasiBulanIniPerAkun), array_keys($realisasiBulanLaluPerAkun), array_keys($budgetTahunanPerAkun)));

$transportVehicleTypes = [];
$hasilVhcKd = jalankanQuery($owlPDO, "select jenisvhc, namajenisvhc from " . $dbname . ".vhc_5jenisvhc where kelompokvhc = 'KD' order by jenisvhc asc");
while ($rowVhc = $hasilVhcKd->fetch(PDO::FETCH_ASSOC)) $transportVehicleTypes[$rowVhc['jenisvhc']] = $rowVhc['namajenisvhc'];


// =========================================================================
// 6. HELPER FUNCTIONS UNTUK MESIN LAPORAN & KALKULASI
// =========================================================================

/**
 * Mengambil struktur definisi laporan dan pemetaan akun dari keu_5mesinlaporan.
 */
function getMesinLaporanStructure($owlPDO, $dbname, $namalaporan) {
    $hasilMesinLaporan = jalankanQuery($owlPDO, "select * from " . $dbname . ".keu_5mesinlaporandt where namalaporan = ? order by nourut asc", [$namalaporan]);
    $strukturMesin = [];
    while ($dataMesinLaporan = $hasilMesinLaporan->fetch(PDO::FETCH_ASSOC)) {
        $strukturMesin[$dataMesinLaporan['nourut']] = $dataMesinLaporan;
    }

    $hasilAkunMesin = jalankanQuery($owlPDO, "select nourut, noakun from " . $dbname . ".keu_5mesinlaporandt_akun where namalaporan = ? order by nourut asc", [$namalaporan]);
    $daftarAkun = [];
    while ($dataAkunMesin = $hasilAkunMesin->fetch(PDO::FETCH_ASSOC)) {
        $daftarAkun[$dataAkunMesin['nourut']][] = $dataAkunMesin['noakun'];
    }

    return [$strukturMesin, $daftarAkun];
}

/**
 * Menghitung nilai RAT, RAB, dan Realisasi per baris berdasarkan akun yang dipetakan
 */
function hitungDataMesin($strukturMesin, $daftarAkun, $semuaAkun, $budgetTahunanPerAkun, $budgetBulanIniPerAkun, $budgetBulanLaluPerAkun, $realisasiBulanIniPerAkun, $realisasiBulanLaluPerAkun) {
    $nilaiLaporan = [];

    // Langkah 1: Hitung Baris Detail
    foreach ($strukturMesin as $nourut => $row) {
        $nilaiLaporan[$nourut] = [
            'd' => 0.00, // RAT
            'e' => 0.00, // RAB Bulan Ini
            'f' => 0.00, // RAB Bulan Lalu
            'h' => 0.00, // Realisasi Bulan Ini
            'i' => 0.00  // Realisasi Bulan Lalu
        ];

        if ($row['tipe'] == 'Detail') {
            if (!empty($daftarAkun[$nourut])) {
                foreach ($daftarAkun[$nourut] as $ak) {
                    if (isset($budgetTahunanPerAkun[$ak]))   $nilaiLaporan[$nourut]['d'] += $budgetTahunanPerAkun[$ak];
                    if (isset($budgetBulanIniPerAkun[$ak]))    $nilaiLaporan[$nourut]['e'] += $budgetBulanIniPerAkun[$ak];
                    if (isset($budgetBulanLaluPerAkun[$ak]))  $nilaiLaporan[$nourut]['f'] += $budgetBulanLaluPerAkun[$ak];
                    if (isset($realisasiBulanIniPerAkun[$ak]))   $nilaiLaporan[$nourut]['h'] += $realisasiBulanIniPerAkun[$ak];
                    if (isset($realisasiBulanLaluPerAkun[$ak])) $nilaiLaporan[$nourut]['i'] += $realisasiBulanLaluPerAkun[$ak];
                }
            }
        }
    }

    // Langkah 2: Hitung Baris Total
    foreach ($strukturMesin as $nourut => $row) {
        if ($row['tipe'] == 'Total') {
            $keys = explode(',', $row['noakundisplay']);
            $totalRat = 0; $totalRabBulanIni = 0; $totalRabBulanLalu = 0; $totalRealisasiBulanIni = 0; $totalRealisasiBulanLalu = 0;
            foreach ($keys as $k) {
                $k = trim($k);
                if ($k === '') continue;
                $pengali = 1;
                if (substr($k, 0, 1) === '-') {
                    $pengali = -1;
                    $k = substr($k, 1);
                }
                if (isset($nilaiLaporan[$k])) {
                    $totalRat += ($nilaiLaporan[$k]['d'] * $pengali);
                    $totalRabBulanIni += ($nilaiLaporan[$k]['e'] * $pengali);
                    $totalRabBulanLalu += ($nilaiLaporan[$k]['f'] * $pengali);
                    $totalRealisasiBulanIni += ($nilaiLaporan[$k]['h'] * $pengali);
                    $totalRealisasiBulanLalu += ($nilaiLaporan[$k]['i'] * $pengali);
                }
            }
            $nilaiLaporan[$nourut] = [
                'd' => $totalRat,
                'e' => $totalRabBulanIni,
                'f' => $totalRabBulanLalu,
                'h' => $totalRealisasiBulanIni,
                'i' => $totalRealisasiBulanLalu
            ];
        }
    }

    return $nilaiLaporan;
}

function hitungTotalMesin($strukturMesin, $nilaiLaporan) {
    foreach ($strukturMesin as $nourut => $row) {
        if ($row['tipe'] != 'Total') continue;
        $nilaiLaporan[$nourut] = ['d' => 0.00, 'e' => 0.00, 'f' => 0.00, 'h' => 0.00, 'i' => 0.00];
        foreach (explode(',', $row['noakundisplay']) as $komponen) {
            $komponen = trim($komponen);
            if ($komponen === '') continue;
            $pengali = substr($komponen, 0, 1) === '-' ? -1 : 1;
            $key = $pengali < 0 ? substr($komponen, 1) : $komponen;
            if (!isset($nilaiLaporan[$key])) continue;
            foreach (['d', 'e', 'f', 'h', 'i'] as $kolom) $nilaiLaporan[$nourut][$kolom] += $nilaiLaporan[$key][$kolom] * $pengali;
        }
    }
    return $nilaiLaporan;
}

function fmtRp($val) {
    if ($val === '' || $val === null) return '-';
    if ($val == 0) return '0.00';
    return number_format($val, 2, '.', ',');
}

function fmtNum($val, $dec = 2) {
    if ($val === '' || $val === null) return '-';
    if ($val == 0) return '0';
    return number_format($val, $dec, '.', ',');
}

function fmtPct($val) {
    if ($val === '' || $val === null || is_nan($val) || is_infinite($val)) return '-';
    return number_format($val, 2, '.', ',') . '%';
}


// =========================================================================
// 7. SETUP DEFINISI BARIS UNTUK MASING-MASING TABEL
// =========================================================================

// TABEL 1: REALISASI BIAYA KEBUN (MAIN REPORT)
// 7. SETUP DEFINISI BARIS UNTUK MASING-MASING TABEL (DIAMBIL LANGSUNG DARI DATABASE TANPA AUTO-SEED)

// Muat Struktur dan Hitung Data Masing-masing Tabel
list($strukturMesin1, $akunMesin1) = getMesinLaporanStructure($owlPDO, $dbname, 'REALISASI BIAYA KEBUN');

$nourutTransport = null;
foreach ($strukturMesin1 as $nourut => $row) {
    if ($row['tipe'] == 'Header' && strtoupper(trim($row['keterangandisplay'])) == 'PENGANGKUTAN') {
        $nourutTransport = $nourut;
        break;
    }
}
if ($nourutTransport === null || empty($akunMesin1[$nourutTransport])) {
    exit('Warning: Akun PENGANGKUTAN belum dikonfigurasi pada mesin laporan!');
}

$nilaiLaporan1 = hitungDataMesin($strukturMesin1, $akunMesin1, $semuaAkun, $budgetTahunanPerAkun, $budgetBulanIniPerAkun, $budgetBulanLaluPerAkun, $realisasiBulanIniPerAkun, $realisasiBulanLaluPerAkun);
$nilaiTransportPerJenis = [];
foreach ($transportVehicleTypes as $kodeJenis => $namaJenis) {
    $nilai = ['d' => 0.00, 'e' => 0.00, 'f' => 0.00, 'h' => 0.00, 'i' => 0.00];
    foreach ($akunMesin1[$nourutTransport] as $akun) {
        if (isset($budgetTahunanPerJenis[$kodeJenis][$akun])) $nilai['d'] += $budgetTahunanPerJenis[$kodeJenis][$akun];
        if (isset($budgetBulanIniPerJenis[$kodeJenis][$akun])) $nilai['e'] += $budgetBulanIniPerJenis[$kodeJenis][$akun];
        if (isset($budgetBulanLaluPerJenis[$kodeJenis][$akun])) $nilai['f'] += $budgetBulanLaluPerJenis[$kodeJenis][$akun];
        if (isset($realisasiBulanIniPerJenis[$kodeJenis][$akun])) $nilai['h'] += $realisasiBulanIniPerJenis[$kodeJenis][$akun];
        if (isset($realisasiBulanLaluPerJenis[$kodeJenis][$akun])) $nilai['i'] += $realisasiBulanLaluPerJenis[$kodeJenis][$akun];
    }
    $nilaiTransportPerJenis[$kodeJenis] = ['nama' => $namaJenis, 'nilai' => $nilai];
}
$nilaiLaporan1[$nourutTransport] = ['d' => 0.00, 'e' => 0.00, 'f' => 0.00, 'h' => 0.00, 'i' => 0.00];
foreach ($nilaiTransportPerJenis as $transport) {
    foreach (['d', 'e', 'f', 'h', 'i'] as $kolom) $nilaiLaporan1[$nourutTransport][$kolom] += $transport['nilai'][$kolom];
}
$nilaiLaporan1 = hitungTotalMesin($strukturMesin1, $nilaiLaporan1);

list($strukturMesin2, $akunMesin2) = getMesinLaporanStructure($owlPDO, $dbname, 'REALISASI BIAYA TBM 1');
$nilaiLaporan2 = hitungDataMesin($strukturMesin2, $akunMesin2, $semuaAkun, $budgetTahunanPerAkun, $budgetBulanIniPerAkun, $budgetBulanLaluPerAkun, $realisasiBulanIniPerAkun, $realisasiBulanLaluPerAkun);

list($strukturMesin3, $akunMesin3) = getMesinLaporanStructure($owlPDO, $dbname, 'REALISASI BIAYA MESS DIREKSI');
$nilaiLaporan3 = hitungDataMesin($strukturMesin3, $akunMesin3, $semuaAkun, $budgetTahunanPerAkun, $budgetBulanIniPerAkun, $budgetBulanLaluPerAkun, $realisasiBulanIniPerAkun, $realisasiBulanLaluPerAkun);


// =========================================================================
// 8. PENGATURAN TAMPILAN & STYLING
// =========================================================================
$brdr = 0;
$bgcoloraja = '';
$bgSub = "style='background-color:#F2F2F2; font-weight:bold;'";
$bgTot = "style='background-color:#DEDEDE; font-weight:bold;'";
$bgCat = "style='background-color:#E2EFDA; font-weight:bold;'";

if ($proses == 'excel') {
    $brdr = 1;
    $bgcoloraja = " bgcolor=#DEDEDE ";
    $bgSub = " bgcolor=#F2F2F2 style='font-weight:bold;' ";
    $bgTot = " bgcolor=#DEDEDE style='font-weight:bold;' ";
    $bgCat = " bgcolor=#E2EFDA style='font-weight:bold;' ";
}

function renderNilaiTransport($no, $keterangan, $nilai, $luasHa, $prodRatKg, $prodRealBlnIniKg, $prodRealBlnLaluKg, $prodRealSdBlnIniKg, $jmlPokok) {
    $d = $nilai['d']; $e = $nilai['e']; $f = $nilai['f']; $g = $e + $f;
    $h = $nilai['h']; $i = $nilai['i']; $j = $h + $i;
    $angka = [$d, $e, $f, $g, $h, $i, $j,
        $luasHa > 0 ? $d / $luasHa : 0, $luasHa > 0 ? $h / $luasHa : 0, $luasHa > 0 ? $i / $luasHa : 0, $luasHa > 0 ? $j / $luasHa : 0,
        $prodRatKg > 0 ? $d / $prodRatKg : 0, $prodRealBlnIniKg > 0 ? $h / $prodRealBlnIniKg : 0, $prodRealBlnLaluKg > 0 ? $i / $prodRealBlnLaluKg : 0, $prodRealSdBlnIniKg > 0 ? $j / $prodRealSdBlnIniKg : 0,
        $jmlPokok > 0 ? $d / $jmlPokok : 0, $jmlPokok > 0 ? $h / $jmlPokok : 0, $jmlPokok > 0 ? $i / $jmlPokok : 0, $jmlPokok > 0 ? $j / $jmlPokok : 0,
        $d - $j, $g - $j, $d > 0 ? $j / $d * 100 : 0, $e > 0 ? $h / $e * 100 : 0];
    $html = "<tr class=rowcontent><td align=center>" . htmlspecialchars($no) . "</td><td align=left>&nbsp;&nbsp;" . htmlspecialchars($keterangan) . "</td>";
    foreach ($angka as $index => $angkaKolom) {
        if ($index < 7 || $index == 19 || $index == 20) $hasil = fmtRp($angkaKolom);
        elseif ($index > 20) $hasil = fmtPct($angkaKolom);
        else $hasil = fmtNum($angkaKolom, $index >= 11 && $index <= 14 ? 2 : 0);
        $html .= "<td align=right>" . $hasil . "</td>";
    }
    return $html . "</tr>";
}

function renderTable26Col($htmlJudul, $strukturMesin, $nilaiLaporan, $luasHa, $prodRatKg, $prodRabBlnIniKg, $prodRabBlnLaluKg, $prodRabSdBlnIniKg, $prodRealBlnIniKg, $prodRealBlnLaluKg, $prodRealSdBlnIniKg, $jmlPokok, $brdr, $bgcoloraja, $bgSub, $bgTot, $bgCat, $nilaiTransportPerJenis = []) {
    $html = "";
    if ($htmlJudul != '') {
        $html .= $htmlJudul;
    }

    $html .= "<table cellspacing=1 border='" . $brdr . "' class=sortable>";
    $html .= "<thead class=rowheader>";

    // Header Baris 1
    $html .= "<tr class=rowheader " . $bgcoloraja . " align=center>";
    $html .= "<th rowspan=4 width=30 align=center>NO</th>";
    $html .= "<th rowspan=4 width=260 align=center>KETERANGAN</th>";
    $html .= "<th rowspan=2 colspan=1 width=110 align=center>JUMLAH</th>";
    $html .= "<th colspan=6 align=center>BIAYA</th>";
    $html .= "<th colspan=4 align=center>COST/HA</th>";
    $html .= "<th colspan=4 align=center>COST/KG</th>";
    $html .= "<th colspan=4 align=center>COST/Pkk</th>";
    $html .= "<th rowspan=4 width=110 align=center>SISA BIAYA RAT</th>";
    $html .= "<th rowspan=4 width=110 align=center>SISA BIAYA RAB</th>";
    $html .= "<th rowspan=2 width=65 align=center>%</th>";
    $html .= "<th rowspan=2 width=65 align=center>%</th>";
    $html .= "</tr>";

    // Header Baris 2
    $html .= "<tr class=rowheader " . $bgcoloraja . " align=center>";
    $html .= "<th colspan=3 align=center>RAB</th>";
    $html .= "<th colspan=3 align=center>REALISASI</th>";
    $html .= "<th colspan=4 align=center>Luas: " . fmtNum($luasHa) . " Ha</th>";
    $html .= "<th colspan=4 align=center>Prod RAT: " . fmtNum($prodRatKg) . " Kg</th>";
    $html .= "<th colspan=4 align=center>Pokok: " . fmtNum($jmlPokok, 0) . " Pkk</th>";
    $html .= "</tr>";

    // Header Baris 3
    $html .= "<tr class=rowheader " . $bgcoloraja . " align=center>";
    $html .= "<th align=center>Luas: " . fmtNum($luasHa) . "</th>";
    $html .= "<th align=center>BULAN INI</th>";
    $html .= "<th align=center>BULAN LALU</th>";
    $html .= "<th align=center>SAMPAI BULAN INI</th>";
    $html .= "<th align=center>BULAN INI</th>";
    $html .= "<th align=center>BULAN LALU</th>";
    $html .= "<th align=center>SAMPAI BULAN INI</th>";
    $html .= "<th align=center>RAT</th>";
    $html .= "<th align=center>Bulan Ini</th>";
    $html .= "<th align=center>Bulan Lalu</th>";
    $html .= "<th align=center>S/d Bulan Ini</th>";
    $html .= "<th align=center>RAT</th>";
    $html .= "<th align=center>Bulan Ini</th>";
    $html .= "<th align=center>Bulan Lalu</th>";
    $html .= "<th align=center>S/d Bulan Ini</th>";
    $html .= "<th align=center>RAT</th>";
    $html .= "<th align=center>Bulan Ini</th>";
    $html .= "<th align=center>Bulan Lalu</th>";
    $html .= "<th align=center>S/d Bulan Ini</th>";
    $html .= "<th align=center>REALISASI</th>";
    $html .= "<th align=center>REALISASI</th>";
    $html .= "</tr>";

    // Header Baris 4
    $html .= "<tr class=rowheader " . $bgcoloraja . " align=center>";
    $html .= "<th align=center>" . fmtNum($prodRatKg) . "</th>";
    $html .= "<th align=center>" . fmtNum($prodRabBlnIniKg) . "</th>";
    $html .= "<th align=center>" . fmtNum($prodRabBlnLaluKg) . "</th>";
    $html .= "<th align=center>" . fmtNum($prodRabSdBlnIniKg) . "</th>";
    $html .= "<th align=center>" . fmtNum($prodRealBlnIniKg) . "</th>";
    $html .= "<th align=center>" . fmtNum($prodRealBlnLaluKg) . "</th>";
    $html .= "<th align=center>" . fmtNum($prodRealSdBlnIniKg) . "</th>";
    $html .= "<th colspan=4 align=center>Rp / Ha</th>";
    $html .= "<th colspan=4 align=center>Rp / Kg</th>";
    $html .= "<th colspan=4 align=center>Rp / Pkk</th>";
    $html .= "<th align=center>BIAYA RAT</th>";
    $html .= "<th align=center>BIAYA RAB</th>";
    $html .= "</tr>";

    $html .= "</thead><tbody>";

    foreach ($strukturMesin as $nourut => $r) {
        $tipe = $r['tipe'];
        $keterangan  = $r['keterangandisplay'];

        $no = '';
        if ($tipe == 'Detail' || $tipe == 'Header') {
            if (!empty($r['noakundisplay']) && strpos($r['noakundisplay'], ',') === false) {
                $no = $r['noakundisplay'];
            }
        }

        // A. Baris Category Header
        if ($tipe == 'Header') {
            if ($no != '') {
                $html .= "<tr class=rowcontent style='font-weight:bold;'>";
                $html .= "<td align=center>" . htmlspecialchars($no) . "</td>";
                $html .= "<td colspan=25><b>" . htmlspecialchars($keterangan) . "</b></td>";
                $html .= "</tr>";
            } else {
                $html .= "<tr " . $bgCat . ">";
                $html .= "<td></td>";
                $html .= "<td colspan=25><b>" . htmlspecialchars($keterangan) . "</b></td>";
                $html .= "</tr>";
            }
            if (strtoupper(trim($keterangan)) == 'PENGANGKUTAN') {
                $indexTransport = 0;
                foreach ($nilaiTransportPerJenis as $transport) {
                    $label = chr(97 + ($indexTransport % 26)) . '.';
                    $html .= renderNilaiTransport($label, strtoupper($transport['nama']), $transport['nilai'], $luasHa, $prodRatKg, $prodRealBlnIniKg, $prodRealBlnLaluKg, $prodRealSdBlnIniKg, $jmlPokok);
                    $indexTransport++;
                }
            }
            continue;
        }

        // B. Baris Detail atau Total
        $d = isset($nilaiLaporan[$nourut]['d']) ? (float)$nilaiLaporan[$nourut]['d'] : 0.00;
        $e = isset($nilaiLaporan[$nourut]['e']) ? (float)$nilaiLaporan[$nourut]['e'] : 0.00;
        $f = isset($nilaiLaporan[$nourut]['f']) ? (float)$nilaiLaporan[$nourut]['f'] : 0.00;
        $g = $e + $f;
        $h = isset($nilaiLaporan[$nourut]['h']) ? (float)$nilaiLaporan[$nourut]['h'] : 0.00;
        $i = isset($nilaiLaporan[$nourut]['i']) ? (float)$nilaiLaporan[$nourut]['i'] : 0.00;
        $j = $h + $i;

        $k = $luasHa > 0 ? ($d / $luasHa) : 0.00;
        $l = $luasHa > 0 ? ($h / $luasHa) : 0.00;
        $m = $luasHa > 0 ? ($i / $luasHa) : 0.00;
        $n = $luasHa > 0 ? ($j / $luasHa) : 0.00;

        $o = $prodRatKg > 0 ? ($d / $prodRatKg) : 0.00;
        $p = $prodRealBlnIniKg > 0 ? ($h / $prodRealBlnIniKg) : 0.00;
        $q = $prodRealBlnLaluKg > 0 ? ($i / $prodRealBlnLaluKg) : 0.00;
        $biayaPerKgSampaiBulanIni = $prodRealSdBlnIniKg > 0 ? ($j / $prodRealSdBlnIniKg) : 0.00;

        $s = $jmlPokok > 0 ? ($d / $jmlPokok) : 0.00;
        $t = $jmlPokok > 0 ? ($h / $jmlPokok) : 0.00;
        $u = $jmlPokok > 0 ? ($i / $jmlPokok) : 0.00;
        $v = $jmlPokok > 0 ? ($j / $jmlPokok) : 0.00;

        $w = $d - $j;
        $x = $g - $j;
        $y = $d > 0 ? (($j / $d) * 100) : 0.00;
        $z_col = $e > 0 ? (($h / $e) * 100) : 0.00;

        $kelasBaris = "class=rowcontent";
        $atributBaris  = "";
        if ($tipe == 'Total') {
            if ($nourut == 100 || stripos($keterangan, 'TOTAL') !== false || stripos($keterangan, 'JUMLAH') !== false) {
                $kelasBaris = "class=rowheader";
                $atributBaris  = $bgTot;
            } else {
                $kelasBaris = "class=rowcontent";
                $atributBaris  = $bgSub;
            }
        }

        $keteranganDisplay = htmlspecialchars($keterangan);
        if ($no == 'a.' || $no == 'b.' || substr($no, 0, 1) == 'a' || substr($no, 0, 1) == 'b') {
            $keteranganDisplay = "&nbsp;&nbsp;" . $keteranganDisplay;
        }

        $html .= "<tr " . $kelasBaris . " " . $atributBaris . ">";
        $html .= "<td align=center>" . htmlspecialchars($no) . "</td>";
        $html .= "<td align=left>" . ($tipe == 'Total' ? "<b>" . $keteranganDisplay . "</b>" : $keteranganDisplay) . "</td>";
        $html .= "<td align=right>" . fmtRp($d) . "</td>";
        $html .= "<td align=right>" . fmtRp($e) . "</td>";
        $html .= "<td align=right>" . fmtRp($f) . "</td>";
        $html .= "<td align=right>" . fmtRp($g) . "</td>";
        $html .= "<td align=right>" . fmtRp($h) . "</td>";
        $html .= "<td align=right>" . fmtRp($i) . "</td>";
        $html .= "<td align=right>" . fmtRp($j) . "</td>";
        $html .= "<td align=right>" . fmtNum($k, 0) . "</td>";
        $html .= "<td align=right>" . fmtNum($l, 0) . "</td>";
        $html .= "<td align=right>" . fmtNum($m, 0) . "</td>";
        $html .= "<td align=right>" . fmtNum($n, 0) . "</td>";
        $html .= "<td align=right>" . fmtNum($o, 2) . "</td>";
        $html .= "<td align=right>" . fmtNum($p, 2) . "</td>";
        $html .= "<td align=right>" . fmtNum($q, 2) . "</td>";
        $html .= "<td align=right>" . fmtNum($biayaPerKgSampaiBulanIni, 2) . "</td>";
        $html .= "<td align=right>" . fmtNum($s, 0) . "</td>";
        $html .= "<td align=right>" . fmtNum($t, 0) . "</td>";
        $html .= "<td align=right>" . fmtNum($u, 0) . "</td>";
        $html .= "<td align=right>" . fmtNum($v, 0) . "</td>";
        $html .= "<td align=right>" . fmtRp($w) . "</td>";
        $html .= "<td align=right>" . fmtRp($x) . "</td>";
        $html .= "<td align=right>" . fmtPct($y) . "</td>";
        $html .= "<td align=right>" . fmtPct($z_col) . "</td>";
        $html .= "</tr>";
    }

    $html .= "</tbody></table>";
    return $html;
}

$htmlLaporan = "";


// =========================================================================
// 9. RENDER TABEL 1: REALISASI BIAYA KEBUN (MAIN REPORT)
// =========================================================================
$title1 = "<table border=0 cellspacing=0 cellpadding=1 style='font-size:12px;margin-bottom:10px;'>";
$title1 .= "<tr><td colspan=26><b>KEBUN " . htmlspecialchars($namaPt) . "</b></td></tr>";
$title1 .= "<tr><td colspan=26><b>REALISASI BIAYA " . htmlspecialchars($thn) . "</b></td></tr>";
$title1 .= "<tr><td colspan=26><b>PERIODE " . htmlspecialchars($namaBulan) . " " . htmlspecialchars($thn) . "</b></td></tr>";
$title1 .= "</table>";

$htmlLaporan .= renderTable26Col(
    $title1,
    $strukturMesin1,
    $nilaiLaporan1,
    $luasHa,
    $prodRatKg,
    $prodRabBlnIniKg,
    $prodRabBlnLaluKg,
    $prodRabSdBlnIniKg,
    $prodRealBlnIniKg,
    $prodRealBlnLaluKg,
    $prodRealSdBlnIniKg,
    $jmlPokok,
    $brdr,
    $bgcoloraja,
    $bgSub,
    $bgTot,
    $bgCat,
    $nilaiTransportPerJenis
);


// =========================================================================
// 10. RENDER TABEL 2: REALISASI BIAYA TBM 1
// =========================================================================
$title2 = "<br><br><table border=0 cellspacing=0 cellpadding=1 style='font-size:12px;margin-bottom:10px;'>";
$title2 .= "<tr><td colspan=26><b>KEBUN " . htmlspecialchars($namaPt) . "</b></td></tr>";
$title2 .= "<tr><td colspan=26><b>REALISASI BIAYA TBM 1 " . htmlspecialchars($thn) . "</b></td></tr>";
$title2 .= "<tr><td colspan=26><b>PERIODE " . htmlspecialchars($namaBulan) . " " . htmlspecialchars($thn) . "</b></td></tr>";
$title2 .= "</table>";

$htmlLaporan .= renderTable26Col(
    $title2,
    $strukturMesin2,
    $nilaiLaporan2,
    $luasTbm,
    0.00,
    0.00,
    0.00,
    0.00,
    0.00,
    0.00,
    0.00,
    $jmlPokokTbm,
    $brdr,
    $bgcoloraja,
    $bgSub,
    $bgTot,
    $bgCat
);


// =========================================================================
// 11. RENDER TABEL 3: REALISASI BIAYA MESS DIREKSI
// =========================================================================
$title3 = "<br><br><table border=0 cellspacing=0 cellpadding=1 style='font-size:12px;margin-bottom:10px;'>";
$title3 .= "<tr><td colspan=26><b>KEBUN " . htmlspecialchars($namaPt) . "</b></td></tr>";
$title3 .= "<tr><td colspan=26><b>REALISASI BIAYA MESS DIREKSI</b></td></tr>";
$title3 .= "<tr><td colspan=26><b>PERIODE " . htmlspecialchars($namaBulan) . " " . htmlspecialchars($thn) . "</b></td></tr>";
$title3 .= "</table>";

$htmlLaporan .= renderTable26Col(
    $title3,
    $strukturMesin3,
    $nilaiLaporan3,
    0.00,
    0.00,
    0.00,
    0.00,
    0.00,
    0.00,
    0.00,
    0.00,
    0.00,
    $brdr,
    $bgcoloraja,
    $bgSub,
    $bgTot,
    $bgCat
);


// =========================================================================
// 12. TANDA TANGAN (SESUAI DOKUMEN EXCEL)
// =========================================================================
// $htmlLaporan .= "<br><br>";
// $htmlLaporan .= "<table border=0 cellspacing=0 cellpadding=2 style='font-size:11px;' width='100%'>";
// $htmlLaporan .= "<tr><td colspan=26 align=right style='padding-right:80px;'><b>Kebun " . htmlspecialchars($namaPt) . ", " . date('d') . " " . htmlspecialchars($namaBulan) . " " . htmlspecialchars($thn) . "</b></td></tr>";
// $htmlLaporan .= "<tr align=center style='font-weight:bold;'><td colspan=8>Disetujui oleh,</td><td colspan=10>Diketahui oleh,</td><td colspan=8>Dibuat oleh,</td></tr>";
// $htmlLaporan .= "<tr height=60><td colspan=26>&nbsp;</td></tr>";
// $htmlLaporan .= "<tr align=center style='font-weight:bold;'><td colspan=8>( .................................... )</td><td colspan=10>( .................................... )</td><td colspan=8>( .................................... )</td></tr>";
// $htmlLaporan .= "</table>";


// =========================================================================
// 13. SWITCH OUTPUT BERDASARKAN PARAMETER PROSES
// =========================================================================
switch ($proses) {
    case 'preview':
        echo $htmlLaporan;
        break;

    case 'excel':
        $htmlLaporan .= "<br><br><table border=0><tr><td colspan=5>Printed By: " . (isset($_SESSION['standard']['username']) ? $_SESSION['standard']['username'] : 'User') . " | Date: " . date('d-m-Y H:i:s') . "</td></tr></table>";
        $namaFile = "Realisasi_Biaya_Kebun_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $pt) . "_" . $periode;
        if (strlen($htmlLaporan) > 0) {
            $direktoriTemp = 'tempExcel';
            if (!is_dir($direktoriTemp)) {
                if (!mkdir($direktoriTemp, 0777, true)) {
                    exit('Warning: Tidak dapat membuat direktori temporary excel!');
                }
            }
            $handleDirektori = opendir($direktoriTemp);
            if ($handleDirektori) {
                while (false !== ($berkasLama = readdir($handleDirektori))) {
                    if ($berkasLama != "." && $berkasLama != ".." && $berkasLama != "index.html") {
                        $jalurBerkas = $direktoriTemp . '/' . $berkasLama;
                        if (is_file($jalurBerkas) && (time() - filemtime($jalurBerkas) > 86400)) {
                            @unlink($jalurBerkas);
                        }
                    }
                }
                closedir($handleDirektori);
            }
            $jalurTujuanFile = $direktoriTemp . "/" . $namaFile . ".xls";
            $fileHandle = fopen($jalurTujuanFile, 'w');
            if ($fileHandle === false) {
                exit('Warning: Tidak dapat membuka file untuk ekspor Excel!');
            }
            if (fwrite($fileHandle, $htmlLaporan) === false) {
                fclose($fileHandle);
                echo "<script language=javascript1.2>
                parent.window.alert('Can\'t convert to excel format');
                </script>";
                exit;
            } else {
                fclose($fileHandle);
                echo "<script language=javascript1.2>
                window.location='" . $jalurTujuanFile . "';
                </script>";
            }
        }
        break;

    default:
        echo $htmlLaporan;
        break;
}
?>
