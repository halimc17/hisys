<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt      = isset($_POST['pt']) ? trim($_POST['pt']) : (isset($_GET['pt']) ? trim($_GET['pt']) : '');
$periode = isset($_POST['periode']) ? trim($_POST['periode']) : (isset($_GET['periode']) ? trim($_GET['periode']) : '');
$proses  = isset($_GET['proses']) ? trim($_GET['proses']) : (isset($_POST['proses']) ? trim($_POST['proses']) : 'preview');

if ($pt == '' || $periode == '') {
    exit("Warning: PT dan Periode harus dipilih!");
}

// 1. Get Nama PT dari database
$namaPt = $pt;
if ($pt != '') {
    $sPt = "select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi = '" . $pt . "' limit 1";
    $qPt = $owlPDO->query($sPt);
    if ($qPt && $rPt = $qPt->fetch(PDO::FETCH_ASSOC)) {
        $namaPt = $rPt['namaorganisasi'];
    }
}

// 2. Format Periode (contoh: 2026-08 -> AGUSTUS 2026)
$arrBulan = [
    '01' => 'JANUARI', '02' => 'FEBRUARI', '03' => 'MARET',     '04' => 'APRIL',
    '05' => 'MEI',     '06' => 'JUNI',     '07' => 'JULI',      '08' => 'AGUSTUS',
    '09' => 'SEPTEMBER','10' => 'OKTOBER', '11' => 'NOVEMBER',  '12' => 'DESEMBER'
];
$expPer = explode('-', $periode);
$thn = isset($expPer[0]) ? $expPer[0] : date('Y');
$bln = isset($expPer[1]) ? str_pad($expPer[1], 2, '0', STR_PAD_LEFT) : date('m');
$blnInt = (int)$bln;
$blnLaluInt = $blnInt - 1;
$blnLalu = str_pad($blnLaluInt, 2, '0', STR_PAD_LEFT);
$namaBulan = isset($arrBulan[$bln]) ? $arrBulan[$bln] : $bln;

// 3. Unit-unit di bawah PT
$listUnit = [];
$sUnit = "select kodeorganisasi from " . $dbname . ".organisasi where induk = '" . $pt . "'";
$qUnit = $owlPDO->query($sUnit);
if ($qUnit) {
    while ($rU = $qUnit->fetch(PDO::FETCH_ASSOC)) {
        $listUnit[] = "'" . $rU['kodeorganisasi'] . "'";
    }
}
if (empty($listUnit)) {
    $listUnit[] = "'" . $pt . "'";
}
$strUnitIn = implode(',', $listUnit);

// 4. Parameter Dasar (Luas Ha, Jumlah Pokok, Produksi) dari Master Data
$luasHa             = 0.00;
$jmlPokok           = 0;
$prodRatKg          = 0.00;
$prodRabBlnIniKg    = 0.00;
$prodRabBlnLaluKg   = 0.00;
$prodRabSdBlnIniKg  = 0.00;
$prodRealBlnIniKg   = 0.00;
$prodRealBlnLaluKg  = 0.00;
$prodRealSdBlnIniKg = 0.00;

// Query Luas dan Pokok TM dari setup_blok
try {
    $sBlok = "select sum(luasareaproduktif) as luas, sum(jumlahpokok) as pokok 
              from " . $dbname . ".setup_blok 
              where substr(kodeorg,1,4) in (" . $strUnitIn . ") and (statusblok in ('TM') or statusblok like '%TM%')";
    $qBlok = $owlPDO->query($sBlok);
    if ($qBlok && $rB = $qBlok->fetch(PDO::FETCH_ASSOC)) {
        $luasHa   = (float)$rB['luas'];
        $jmlPokok = (float)$rB['pokok'];
    }
} catch (Exception $e) {}

// Fallback jika belum terbagi TM atau masih umum
if ($luasHa <= 0) {
    try {
        $sBlok = "select sum(luasareaproduktif) as luas, sum(jumlahpokok) as pokok 
                  from " . $dbname . ".setup_blok 
                  where substr(kodeorg,1,4) in (" . $strUnitIn . ")";
        $qBlok = $owlPDO->query($sBlok);
        if ($qBlok && $rB = $qBlok->fetch(PDO::FETCH_ASSOC)) {
            $luasHa   = (float)$rB['luas'];
            $jmlPokok = (float)$rB['pokok'];
        }
    } catch (Exception $e) {}
}

// Query Luas & Pokok TBM dari setup_blok
$luasTbm     = 0.00;
$jmlPokokTbm = 0;
try {
    $sTbm = "select sum(luasareaproduktif) as luas, sum(jumlahpokok) as pokok from " . $dbname . ".setup_blok 
             where substr(kodeorg,1,4) in (" . $strUnitIn . ") and (statusblok in ('TBM'))";
    $qTbm = $owlPDO->query($sTbm);
    if ($qTbm && $rTbm = $qTbm->fetch(PDO::FETCH_ASSOC)) {
        $luasTbm     = (float)$rTbm['luas'];
        $jmlPokokTbm = (float)$rTbm['pokok'];
    }
} catch (Exception $e) {}

// Query Produksi Budget (RAT & RAB)
try {
    $sProdBgt = "select sum(kgsetahun) as rat, sum(kg" . $bln . ") as bi ";
    if ($blnLaluInt > 0) {
        $arrBlnLalu = [];
        $arrSdbi = [];
        for ($i = 1; $i <= $blnInt; $i++) {
            $arrSdbi[] = "kg" . str_pad($i, 2, '0', STR_PAD_LEFT);
            if ($i < $blnInt) {
                $arrBlnLalu[] = "kg" . str_pad($i, 2, '0', STR_PAD_LEFT);
            }
        }
        $sProdBgt .= ", sum(" . implode('+', $arrSdbi) . ") as sdbi, sum(" . implode('+', $arrBlnLalu) . ") as blnlalu ";
    } else {
        $sProdBgt .= ", sum(kg" . $bln . ") as sdbi, 0 as blnlalu ";
    }
    $sProdBgt .= "from " . $dbname . ".bgt_produksi_kebun 
                  where tahunbudget = '" . $thn . "' and substr(kodeblok,1,4) in (" . $strUnitIn . ")";
    $qProdBgt = $owlPDO->query($sProdBgt);
    if ($qProdBgt && $rPB = $qProdBgt->fetch(PDO::FETCH_ASSOC)) {
        $prodRatKg         = (float)$rPB['rat'];
        $prodRabBlnIniKg   = (float)$rPB['bi'];
        $prodRabBlnLaluKg  = (float)$rPB['blnlalu'];
        $prodRabSdBlnIniKg = (float)$rPB['sdbi'];
    }
} catch (Exception $e) {}

// Query Realisasi Produksi (Kg) dari SPB
try {
    $sRealBi = "select sum(kgbjr) as kg from " . $dbname . ".kebun_spbdt 
                where nospb in (select nospb from " . $dbname . ".kebun_spbht where kodeorg in (" . $strUnitIn . ") and tanggal like '" . $periode . "%')";
    $qRealBi = $owlPDO->query($sRealBi);
    if ($qRealBi && $rRBi = $qRealBi->fetch(PDO::FETCH_ASSOC)) {
        $prodRealBlnIniKg = (float)$rRBi['kg'];
    }

    if ($blnLaluInt > 0) {
        $sRealLalu = "select sum(kgbjr) as kg from " . $dbname . ".kebun_spbdt 
                      where nospb in (select nospb from " . $dbname . ".kebun_spbht where kodeorg in (" . $strUnitIn . ") and tanggal >= '" . $thn . "-01-01' and tanggal < '" . $periode . "-01')";
        $qRealLalu = $owlPDO->query($sRealLalu);
        if ($qRealLalu && $rRL = $qRealLalu->fetch(PDO::FETCH_ASSOC)) {
            $prodRealBlnLaluKg = (float)$rRL['kg'];
        }
    }
    $prodRealSdBlnIniKg = $prodRealBlnIniKg + $prodRealBlnLaluKg;
} catch (Exception $e) {}


// =========================================================================
// 5. CACHE DATA GROUPED JURNAL & BUDGET (Hanya 1x query untuk semua tabel)
// =========================================================================
$arrRealBi   = [];
$arrRealLalu = [];
$arrRabRat   = [];
$arrRabBi    = [];
$arrRabLalu  = [];

// 5A. Realisasi Bulan Ini (keu_jurnaldt)
try {
    $sRealBi = "select noakun, sum(jumlah) as jml 
                from " . $dbname . ".keu_jurnaldt 
                where kodeorg in (" . $strUnitIn . ") and tanggal like '" . $periode . "%' 
                group by noakun";
    $qRealBi = $owlPDO->query($sRealBi);
    if ($qRealBi) {
        while ($rR = $qRealBi->fetch(PDO::FETCH_ASSOC)) {
            $arrRealBi[$rR['noakun']] = (float)$rR['jml'];
        }
    }
} catch (Exception $e) {}

// 5B. Realisasi Bulan Lalu (Januari s/d Bulan Lalu)
if ($blnLaluInt > 0) {
    try {
        $sRealLalu = "select noakun, sum(jumlah) as jml 
                      from " . $dbname . ".keu_jurnaldt 
                      where kodeorg in (" . $strUnitIn . ") and tanggal >= '" . $thn . "-01-01' and tanggal < '" . $periode . "-01' 
                      group by noakun";
        $qRealLalu = $owlPDO->query($sRealLalu);
        if ($qRealLalu) {
            while ($rRL = $qRealLalu->fetch(PDO::FETCH_ASSOC)) {
                $arrRealLalu[$rRL['noakun']] = (float)$rRL['jml'];
            }
        }
    } catch (Exception $e) {}
}

// 5C. Budget (bgt_budget_detail)
$colBlnBgt = "rp" . $bln;
$sumBgtLaluSql = "0";
if ($blnLaluInt > 0) {
    $arrBgtLalu = [];
    for ($bi = 1; $bi < $blnInt; $bi++) {
        $arrBgtLalu[] = "rp" . str_pad($bi, 2, '0', STR_PAD_LEFT);
    }
    $sumBgtLaluSql = "sum(" . implode('+', $arrBgtLalu) . ")";
}

try {
    $sBgt = "select noakun, sum(rupiah) as rat, sum(" . $colBlnBgt . ") as rab_bi, " . $sumBgtLaluSql . " as rab_lalu 
             from " . $dbname . ".bgt_budget_detail 
             where tahunbudget = '" . $thn . "' and substr(kodeorg,1,4) in (" . $strUnitIn . ") 
             group by noakun";
    $qBgt = $owlPDO->query($sBgt);
    if ($qBgt) {
        while ($rBgt = $qBgt->fetch(PDO::FETCH_ASSOC)) {
            $arrRabRat[$rBgt['noakun']]  = (float)$rBgt['rat'];
            $arrRabBi[$rBgt['noakun']]   = (float)$rBgt['rab_bi'];
            $arrRabLalu[$rBgt['noakun']] = (float)$rBgt['rab_lalu'];
        }
    }
} catch (Exception $e) {}

$allAkuns = array_unique(array_merge(
    array_keys($arrRealBi),
    array_keys($arrRealLalu),
    array_keys($arrRabRat)
));


// =========================================================================
// 6. HELPER FUNCTIONS UNTUK MESIN LAPORAN & KALKULASI
// =========================================================================

/**
 * Mengambil struktur definisi laporan dan pemetaan akun dari keu_5mesinlaporan.
 * Jika belum ada di tabel, dilakukan auto-seed agar template otomatis tersedia.
 */
function getMesinLaporanStructure($owlPDO, $dbname, $namalaporan, $defaultRows = []) {
    $sCekDt = "select count(*) as jml from " . $dbname . ".keu_5mesinlaporandt where namalaporan = '" . $namalaporan . "'";
    $qCekDt = $owlPDO->query($sCekDt);
    $rCekDt = $qCekDt ? $qCekDt->fetch(PDO::FETCH_ASSOC) : ['jml' => 0];

    if ($rCekDt['jml'] == 0 && !empty($defaultRows)) {
        // Cek & insert header jika belum ada
        $sCekHt = "select count(*) as jml from " . $dbname . ".keu_5mesinlaporanht where namalaporan = '" . $namalaporan . "'";
        $qCekHt = $owlPDO->query($sCekHt);
        $rCekHt = $qCekHt ? $qCekHt->fetch(PDO::FETCH_ASSOC) : ['jml' => 0];
        if ($rCekHt['jml'] == 0) {
            $owlPDO->exec("
                insert into " . $dbname . ".keu_5mesinlaporanht (kodeorg, namalaporan, periode, ket1, aktif, createby, createtime)
                values ('HHO', '" . $namalaporan . "', 'Bulanan', '" . $namalaporan . "', 1, 'antigravity', NOW())
            ");
        }

        // Insert detail baris
        $insStmt = $owlPDO->prepare("
            insert into " . $dbname . ".keu_5mesinlaporandt (kodeorg, namalaporan, nourut, tipe, noakundari, noakunsampai, noakundisplay, keterangandisplay, tampil, detail)
            values ('HHO', ?, ?, ?, ?, ?, ?, ?, 1, 1)
        ");
        $insAkunStmt = $owlPDO->prepare("
            insert ignore into " . $dbname . ".keu_5mesinlaporandt_akun (namalaporan, nourut, noakun)
            values (?, ?, ?)
        ");

        foreach ($defaultRows as $ir) {
            $insStmt->execute([
                $namalaporan,
                $ir['nourut'],
                $ir['tipe'],
                $ir['dari'],
                $ir['sampai'],
                $ir['disp'],
                $ir['ket']
            ]);

            // Auto-populate keu_5mesinlaporandt_akun jika ada range akun
            if ($ir['tipe'] == 'Detail' && $ir['dari'] != '' && $ir['sampai'] != '') {
                try {
                    $sAk = "select noakun from " . $dbname . ".keu_5akun where noakun between '" . $ir['dari'] . "' and '" . $ir['sampai'] . "'";
                    $qAk = $owlPDO->query($sAk);
                    if ($qAk) {
                        while ($rAk = $qAk->fetch(PDO::FETCH_ASSOC)) {
                            $insAkunStmt->execute([$namalaporan, $ir['nourut'], $rAk['noakun']]);
                        }
                    }
                } catch (Exception $e) {}
            }
        }
    }

    // Ambil data baris laporan
    $sMesin = "select * from " . $dbname . ".keu_5mesinlaporandt where namalaporan = '" . $namalaporan . "' order by nourut asc";
    $qMesin = $owlPDO->query($sMesin);
    $dtMesin = [];
    if ($qMesin) {
        while ($rM = $qMesin->fetch(PDO::FETCH_ASSOC)) {
            $dtMesin[$rM['nourut']] = $rM;
        }
    }

    // Ambil data pemetaan akun dari keu_5mesinlaporandt_akun
    $sAkun = "select nourut, noakun from " . $dbname . ".keu_5mesinlaporandt_akun where namalaporan = '" . $namalaporan . "' order by nourut asc";
    $qAkun = $owlPDO->query($sAkun);
    $daftarakun = [];
    if ($qAkun) {
        while ($rA = $qAkun->fetch(PDO::FETCH_ASSOC)) {
            $daftarakun[$rA['nourut']][] = "'" . $rA['noakun'] . "'";
        }
    }

    return [$dtMesin, $daftarakun];
}

/**
 * Menghitung nilai RAT, RAB, dan Realisasi per baris berdasarkan akun yang dipetakan
 */
function hitungDataMesin($dtMesin, $daftarakun, $allAkuns, $arrRabRat, $arrRabBi, $arrRabLalu, $arrRealBi, $arrRealLalu) {
    $valData = [];

    // Langkah 1: Hitung Baris Detail
    foreach ($dtMesin as $nourut => $row) {
        $valData[$nourut] = [
            'd' => 0.00, // RAT
            'e' => 0.00, // RAB Bulan Ini
            'f' => 0.00, // RAB Bulan Lalu
            'h' => 0.00, // Realisasi Bulan Ini
            'i' => 0.00  // Realisasi Bulan Lalu
        ];

        if ($row['tipe'] == 'Detail') {
            $matchedAccounts = [];
            if (!empty($daftarakun[$nourut])) {
                $matchedAccounts = $daftarakun[$nourut];
            }

            $dari = trim($row['noakundari']);
            $sampai = trim($row['noakunsampai']);

            foreach ($allAkuns as $ak) {
                $match = false;
                if (!empty($matchedAccounts)) {
                    if (in_array("'" . $ak . "'", $matchedAccounts) || in_array($ak, $matchedAccounts)) {
                        $match = true;
                    }
                }
                if (!$match && $dari != '' && $sampai != '') {
                    if ($ak >= $dari && $ak <= $sampai) {
                        $match = true;
                    }
                } elseif (!$match && $dari != '') {
                    if (strpos($ak, $dari) === 0) {
                        $match = true;
                    }
                }

                if ($match) {
                    if (isset($arrRabRat[$ak]))   $valData[$nourut]['d'] += $arrRabRat[$ak];
                    if (isset($arrRabBi[$ak]))    $valData[$nourut]['e'] += $arrRabBi[$ak];
                    if (isset($arrRabLalu[$ak]))  $valData[$nourut]['f'] += $arrRabLalu[$ak];
                    if (isset($arrRealBi[$ak]))   $valData[$nourut]['h'] += $arrRealBi[$ak];
                    if (isset($arrRealLalu[$ak])) $valData[$nourut]['i'] += $arrRealLalu[$ak];
                }
            }
        }
    }

    // Langkah 2: Hitung Baris Total
    foreach ($dtMesin as $nourut => $row) {
        if ($row['tipe'] == 'Total') {
            $keys = explode(',', $row['noakundisplay']);
            $totD = 0; $totE = 0; $totF = 0; $totH = 0; $totI = 0;
            foreach ($keys as $k) {
                $k = trim($k);
                if ($k === '') continue;
                $mult = 1;
                if (substr($k, 0, 1) === '-') {
                    $mult = -1;
                    $k = substr($k, 1);
                }
                if (isset($valData[$k])) {
                    $totD += ($valData[$k]['d'] * $mult);
                    $totE += ($valData[$k]['e'] * $mult);
                    $totF += ($valData[$k]['f'] * $mult);
                    $totH += ($valData[$k]['h'] * $mult);
                    $totI += ($valData[$k]['i'] * $mult);
                }
            }
            $valData[$nourut] = [
                'd' => $totD,
                'e' => $totE,
                'f' => $totF,
                'h' => $totH,
                'i' => $totI
            ];
        }
    }

    return $valData;
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

// TABEL 1: REALISASI BIAYA KEBUN
$initRows1 = [
    // I. BIAYA PERAWATAN TANAMAN
    ['nourut' => 10,  'tipe' => 'Header', 'dari' => '',        'sampai' => '',        'disp' => '',    'ket' => 'BIAYA PERAWATAN TANAMAN'],
    ['nourut' => 11,  'tipe' => 'Detail', 'dari' => '6210101', 'sampai' => '6210106', 'disp' => '01.', 'ket' => 'PEMELIHARAAN TANAMAN'],
    ['nourut' => 12,  'tipe' => 'Detail', 'dari' => '6210701', 'sampai' => '6210716', 'disp' => '02.', 'ket' => 'PEMBERATASAN HAMA & PENYAKIT'],
    ['nourut' => 13,  'tipe' => 'Detail', 'dari' => '6210201', 'sampai' => '6210204', 'disp' => '03.', 'ket' => 'PEMELIHARAAN JALAN & JEMBATAN'],
    ['nourut' => 14,  'tipe' => 'Detail', 'dari' => '6210104', 'sampai' => '6210104', 'disp' => '04.', 'ket' => 'PEMBASMIAN LALANG'],
    ['nourut' => 15,  'tipe' => 'Detail', 'dari' => '6210601', 'sampai' => '6210603', 'disp' => '05.', 'ket' => 'PENUNASAN'],
    ['nourut' => 16,  'tipe' => 'Detail', 'dari' => '6210801', 'sampai' => '6210808', 'disp' => '06.', 'ket' => 'PEMUPUKAN KELAPA SAWIT'],
    ['nourut' => 17,  'tipe' => 'Detail', 'dari' => '6210401', 'sampai' => '6210404', 'disp' => '07.', 'ket' => 'PEMELIHARAAN SALURAN AIR'],
    ['nourut' => 18,  'tipe' => 'Detail', 'dari' => '6210501', 'sampai' => '6210506', 'disp' => '08.', 'ket' => 'PENYISIPAN / SENSUS'],
    ['nourut' => 19,  'tipe' => 'Detail', 'dari' => '6211101', 'sampai' => '6211101', 'disp' => '09.', 'ket' => 'GAJI MANDOR BHL DIVISI'],
    ['nourut' => 20,  'tipe' => 'Total',  'dari' => '',        'sampai' => '',        'disp' => '11,12,13,14,15,16,17,18,19', 'ket' => 'JUMLAH BIAYA PERAWATAN'],

    // II. BIAYA PANEN
    ['nourut' => 30,  'tipe' => 'Header', 'dari' => '',        'sampai' => '',        'disp' => '',    'ket' => 'BIAYA PANEN'],
    ['nourut' => 31,  'tipe' => 'Detail', 'dari' => '6110101', 'sampai' => '6110104', 'disp' => '10.', 'ket' => 'P A N E N'],
    ['nourut' => 32,  'tipe' => 'Detail', 'dari' => '6110301', 'sampai' => '6110302', 'disp' => '11.', 'ket' => 'ALAT - ALAT PANEN'],
    ['nourut' => 33,  'tipe' => 'Header', 'dari' => '',        'sampai' => '',        'disp' => '12.', 'ket' => 'PENGANGKUTAN'],
    ['nourut' => 34,  'tipe' => 'Detail', 'dari' => '6110201', 'sampai' => '6110204', 'disp' => 'a.',  'ket' => 'DUMP TRUCK'],
    ['nourut' => 35,  'tipe' => 'Detail', 'dari' => '6110205', 'sampai' => '6110206', 'disp' => 'b.',  'ket' => 'WHEEL TRAKTOR'],
    ['nourut' => 39,  'tipe' => 'Total',  'dari' => '',        'sampai' => '',        'disp' => '31,32,34,35', 'ket' => 'JUMLAH BIAYA PANEN'],

    // III. JUMLAH PERAWATAN & PANEN
    ['nourut' => 40,  'tipe' => 'Total',  'dari' => '',        'sampai' => '',        'disp' => '20,39', 'ket' => 'JUMLAH BIAYA PERAWATAN & PANEN'],

    // IV. BIAYA UMUM / OVER HEAD
    ['nourut' => 50,  'tipe' => 'Header', 'dari' => '',        'sampai' => '',        'disp' => '',    'ket' => 'BIAYA UMUM / OVER HEAD'],
    ['nourut' => 51,  'tipe' => 'Detail', 'dari' => '7110101', 'sampai' => '7110201', 'disp' => '01.', 'ket' => 'GAJI KARYAWAN'],
    ['nourut' => 52,  'tipe' => 'Detail', 'dari' => '7110202', 'sampai' => '7110202', 'disp' => '02.', 'ket' => 'LEMBUR DAN PREMI'],
    ['nourut' => 53,  'tipe' => 'Detail', 'dari' => '7110107', 'sampai' => '7110107', 'disp' => '03.', 'ket' => 'THR'],
    ['nourut' => 54,  'tipe' => 'Detail', 'dari' => '7130101', 'sampai' => '7130225', 'disp' => '04.', 'ket' => 'BIAYA UMUM DAN ADMINISTRASI KANTOR KEBUN'],
    ['nourut' => 55,  'tipe' => 'Detail', 'dari' => '7140907', 'sampai' => '7140908', 'disp' => '05.', 'ket' => 'BIAYA PEMELIHARAAN KENDARAAN'],
    ['nourut' => 56,  'tipe' => 'Detail', 'dari' => '7140901', 'sampai' => '7140902', 'disp' => '06.', 'ket' => 'BIAYA PEMELIHARAAN BANGUNAN'],
    ['nourut' => 57,  'tipe' => 'Detail', 'dari' => '7140909', 'sampai' => '7140910', 'disp' => '07.', 'ket' => 'BIAYA PEMELIHARAAN MESIN DAN INSTALASI'],
    ['nourut' => 58,  'tipe' => 'Detail', 'dari' => '7110114', 'sampai' => '7110114', 'disp' => '08.', 'ket' => 'BIAYA KLINIK'],
    ['nourut' => 59,  'tipe' => 'Total',  'dari' => '',        'sampai' => '',        'disp' => '51,52,53,54,55,56,57,58', 'ket' => 'JUMLAH BIAYA UMUM / OVER HEAD'],

    // V. BIAYA ISPO
    ['nourut' => 60,  'tipe' => 'Header', 'dari' => '',        'sampai' => '',        'disp' => '',    'ket' => 'BIAYA ISPO'],
    ['nourut' => 61,  'tipe' => 'Detail', 'dari' => '1260101', 'sampai' => '1260103', 'disp' => '01.', 'ket' => 'BIAYA INVESTASI'],
    ['nourut' => 62,  'tipe' => 'Detail', 'dari' => '7130701', 'sampai' => '7130712', 'disp' => '02.', 'ket' => 'BIAYA NON INVESTASI'],
    ['nourut' => 69,  'tipe' => 'Total',  'dari' => '',        'sampai' => '',        'disp' => '61,62', 'ket' => 'JUMLAH BIAYA ISPO'],

    // VI. BIAYA GET PLASTIK
    ['nourut' => 70,  'tipe' => 'Header', 'dari' => '',        'sampai' => '',        'disp' => '',    'ket' => 'BIAYA GET PLASTIK'],
    ['nourut' => 71,  'tipe' => 'Detail', 'dari' => '7130112', 'sampai' => '7130113', 'disp' => '01.', 'ket' => 'BIAYA GET PLASTIK'],
    ['nourut' => 79,  'tipe' => 'Total',  'dari' => '',        'sampai' => '',        'disp' => '71',   'ket' => 'JUMLAH BIAYA GET PLASTIK'],

    // VII. BIAYA INVESTASI
    ['nourut' => 80,  'tipe' => 'Header', 'dari' => '',        'sampai' => '',        'disp' => '',    'ket' => 'BIAYA INVESTASI'],
    ['nourut' => 81,  'tipe' => 'Detail', 'dari' => '1200101', 'sampai' => '1200103', 'disp' => '01.', 'ket' => 'TANAH'],
    ['nourut' => 82,  'tipe' => 'Detail', 'dari' => '1260201', 'sampai' => '1260209', 'disp' => '02.', 'ket' => 'INFRASTRUKTUR ( JALAN & JEMBATAN)'],
    ['nourut' => 83,  'tipe' => 'Detail', 'dari' => '1260301', 'sampai' => '1260303', 'disp' => '03.', 'ket' => 'DRAINASE (PARIT OUTLET & PARIT RAJANGAN)'],
    ['nourut' => 84,  'tipe' => 'Detail', 'dari' => '1270401', 'sampai' => '1270403', 'disp' => '04.', 'ket' => 'ALAT BERAT & KENDARAAN'],
    ['nourut' => 85,  'tipe' => 'Detail', 'dari' => '1270501', 'sampai' => '1270502', 'disp' => '05.', 'ket' => 'MESIN & INSTALASI'],
    ['nourut' => 86,  'tipe' => 'Detail', 'dari' => '1270201', 'sampai' => '1270201', 'disp' => '06.', 'ket' => 'BANGUNAN'],
    ['nourut' => 87,  'tipe' => 'Detail', 'dari' => '7150101', 'sampai' => '7150101', 'disp' => '07.', 'ket' => 'INVENTARIS'],
    ['nourut' => 89,  'tipe' => 'Total',  'dari' => '',        'sampai' => '',        'disp' => '81,82,83,84,85,86,87', 'ket' => 'JUMLAH BIAYA INVESTASI'],

    // VIII. BIAYA ANGSURAN
    ['nourut' => 90,  'tipe' => 'Header', 'dari' => '',        'sampai' => '',        'disp' => '',    'ket' => 'BIAYA ANGSURAN'],
    ['nourut' => 91,  'tipe' => 'Detail', 'dari' => '1180106', 'sampai' => '1180106', 'disp' => '01.', 'ket' => 'KENDARAAN & ALAT BERAT'],
    ['nourut' => 99,  'tipe' => 'Total',  'dari' => '',        'sampai' => '',        'disp' => '91',   'ket' => 'JUMLAH BIAYA ANGSURAN'],

    // IX. TOTAL BIAYA RAT
    ['nourut' => 100, 'tipe' => 'Total',  'dari' => '',        'sampai' => '',        'disp' => '40,59,69,79,89,99', 'ket' => 'TOTAL BIAYA RAT']
];

// TABEL 2: REALISASI BIAYA TBM 1
$initRows2 = [
    ['nourut' => 0,   'tipe' => 'Header', 'dari' => '',        'sampai' => '',        'disp' => '',    'ket' => 'BIAYA PERAWATAN TANAMAN'],
    ['nourut' => 1,   'tipe' => 'Detail', 'dari' => '1260105', 'sampai' => '1260124', 'disp' => '01.', 'ket' => 'PEMELIHARAAN TANAMAN'],
    ['nourut' => 2,   'tipe' => 'Detail', 'dari' => '1260109', 'sampai' => '1260109', 'disp' => '02.', 'ket' => 'PEMBERATASAN HAMA & PENYAKIT'],
    ['nourut' => 3,   'tipe' => 'Detail', 'dari' => '1260201', 'sampai' => '1260209', 'disp' => '03.', 'ket' => 'PEMELIHARAAN JALAN & JEMBATAN'],
    ['nourut' => 4,   'tipe' => 'Detail', 'dari' => '1260107', 'sampai' => '1260107', 'disp' => '04.', 'ket' => 'PEMBASMIAN LALANG'],
    ['nourut' => 5,   'tipe' => 'Detail', 'dari' => '1260108', 'sampai' => '1260108', 'disp' => '05.', 'ket' => 'PENUNASAN'],
    ['nourut' => 6,   'tipe' => 'Detail', 'dari' => '1260801', 'sampai' => '1260808', 'disp' => '06.', 'ket' => 'PEMUPUKAN KELAPA SAWIT'],
    ['nourut' => 7,   'tipe' => 'Detail', 'dari' => '1260301', 'sampai' => '1260303', 'disp' => '07.', 'ket' => 'PEMELIHARAAN SALURAN AIR'],
    ['nourut' => 8,   'tipe' => 'Detail', 'dari' => '1260101', 'sampai' => '1260104', 'disp' => '08.', 'ket' => 'PENYISIPAN / SENSUS'],
    ['nourut' => 9,   'tipe' => 'Detail', 'dari' => '1261101', 'sampai' => '1261101', 'disp' => '09.', 'ket' => 'GAJI MANDOR BHL DIVISI'],
    ['nourut' => 10,  'tipe' => 'Total',  'dari' => '',        'sampai' => '',        'disp' => '1,2,3,4,5,6,7,8,9', 'ket' => 'TOTAL BIAYA RAT TBM 1']
];

// TABEL 3: REALISASI BIAYA MESS DIREKSI
$initRows3 = [
    ['nourut' => 1,  'tipe' => 'Detail', 'dari' => '7130100', 'sampai' => '7130115', 'disp' => '01.', 'ket' => 'BIAYA PEMBUATAN BANGUNAN MESS DIREKSI'],
    ['nourut' => 10, 'tipe' => 'Total',  'dari' => '',        'sampai' => '',        'disp' => '1',   'ket' => 'TOTAL BIAYA']
];

// Muat Struktur dan Hitung Data Masing-masing Tabel
list($dtMesin1, $akunMesin1) = getMesinLaporanStructure($owlPDO, $dbname, 'REALISASI BIAYA KEBUN', $initRows1);
$valData1                    = hitungDataMesin($dtMesin1, $akunMesin1, $allAkuns, $arrRabRat, $arrRabBi, $arrRabLalu, $arrRealBi, $arrRealLalu);

list($dtMesin2, $akunMesin2) = getMesinLaporanStructure($owlPDO, $dbname, 'REALISASI BIAYA TBM 1', $initRows2);
$valData2                    = hitungDataMesin($dtMesin2, $akunMesin2, $allAkuns, $arrRabRat, $arrRabBi, $arrRabLalu, $arrRealBi, $arrRealLalu);

list($dtMesin3, $akunMesin3) = getMesinLaporanStructure($owlPDO, $dbname, 'REALISASI BIAYA MESS DIREKSI', $initRows3);
$valData3                    = hitungDataMesin($dtMesin3, $akunMesin3, $allAkuns, $arrRabRat, $arrRabBi, $arrRabLalu, $arrRealBi, $arrRealLalu);


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

/**
 * Render Tabel 26 Kolom Berjenjang (Digunakan oleh Table 1 dan Table 3)
 */
function renderTable26Col($titleHtml, $dtMesin, $valData, $luasHa, $prodRatKg, $prodRabBlnIniKg, $prodRabBlnLaluKg, $prodRabSdBlnIniKg, $prodRealBlnIniKg, $prodRealBlnLaluKg, $prodRealSdBlnIniKg, $jmlPokok, $brdr, $bgcoloraja, $bgSub, $bgTot, $bgCat) {
    $out = "";
    if ($titleHtml != '') {
        $out .= $titleHtml;
    }

    $out .= "<table cellspacing=1 border='" . $brdr . "' class=sortable>";
    $out .= "<thead class=rowheader>";

    // Header Baris 1
    $out .= "<tr class=rowheader " . $bgcoloraja . " align=center>";
    $out .= "<th rowspan=4 width=30 align=center>NO</th>";
    $out .= "<th rowspan=4 width=260 align=center>KETERANGAN</th>";
    $out .= "<th rowspan=2 colspan=1 width=110 align=center>JUMLAH</th>";
    $out .= "<th colspan=6 align=center>BIAYA</th>";
    $out .= "<th colspan=4 align=center>COST/HA</th>";
    $out .= "<th colspan=4 align=center>COST/KG</th>";
    $out .= "<th colspan=4 align=center>COST/Pkk</th>";
    $out .= "<th rowspan=4 width=110 align=center>SISA BIAYA RAT</th>";
    $out .= "<th rowspan=4 width=110 align=center>SISA BIAYA RAB</th>";
    $out .= "<th rowspan=2 width=65 align=center>%</th>";
    $out .= "<th rowspan=2 width=65 align=center>%</th>";
    $out .= "</tr>";

    // Header Baris 2
    $out .= "<tr class=rowheader " . $bgcoloraja . " align=center>";
    $out .= "<th colspan=3 align=center>RAB</th>";
    $out .= "<th colspan=3 align=center>REALISASI</th>";
    $out .= "<th colspan=4 align=center>Luas: " . fmtNum($luasHa) . " Ha</th>";
    $out .= "<th colspan=4 align=center>Prod RAT: " . fmtNum($prodRatKg) . " Kg</th>";
    $out .= "<th colspan=4 align=center>Pokok: " . fmtNum($jmlPokok, 0) . " Pkk</th>";
    $out .= "</tr>";

    // Header Baris 3
    $out .= "<tr class=rowheader " . $bgcoloraja . " align=center>";
    $out .= "<th align=center>Luas: " . fmtNum($luasHa) . "</th>";
    $out .= "<th align=center>BULAN INI</th>";
    $out .= "<th align=center>BULAN LALU</th>";
    $out .= "<th align=center>SAMPAI BULAN INI</th>";
    $out .= "<th align=center>BULAN INI</th>";
    $out .= "<th align=center>BULAN LALU</th>";
    $out .= "<th align=center>SAMPAI BULAN INI</th>";
    $out .= "<th align=center>RAT</th>";
    $out .= "<th align=center>Bulan Ini</th>";
    $out .= "<th align=center>Bulan Lalu</th>";
    $out .= "<th align=center>S/d Bulan Ini</th>";
    $out .= "<th align=center>RAT</th>";
    $out .= "<th align=center>Bulan Ini</th>";
    $out .= "<th align=center>Bulan Lalu</th>";
    $out .= "<th align=center>S/d Bulan Ini</th>";
    $out .= "<th align=center>RAT</th>";
    $out .= "<th align=center>Bulan Ini</th>";
    $out .= "<th align=center>Bulan Lalu</th>";
    $out .= "<th align=center>S/d Bulan Ini</th>";
    $out .= "<th align=center>REALISASI</th>";
    $out .= "<th align=center>REALISASI</th>";
    $out .= "</tr>";

    // Header Baris 4
    $out .= "<tr class=rowheader " . $bgcoloraja . " align=center>";
    $out .= "<th align=center>" . fmtNum($prodRatKg) . "</th>";
    $out .= "<th align=center>" . fmtNum($prodRabBlnIniKg) . "</th>";
    $out .= "<th align=center>" . fmtNum($prodRabBlnLaluKg) . "</th>";
    $out .= "<th align=center>" . fmtNum($prodRabSdBlnIniKg) . "</th>";
    $out .= "<th align=center>" . fmtNum($prodRealBlnIniKg) . "</th>";
    $out .= "<th align=center>" . fmtNum($prodRealBlnLaluKg) . "</th>";
    $out .= "<th align=center>" . fmtNum($prodRealSdBlnIniKg) . "</th>";
    $out .= "<th colspan=4 align=center>Rp / Ha</th>";
    $out .= "<th colspan=4 align=center>Rp / Kg</th>";
    $out .= "<th colspan=4 align=center>Rp / Pkk</th>";
    $out .= "<th align=center>BIAYA RAT</th>";
    $out .= "<th align=center>BIAYA RAB</th>";
    $out .= "</tr>";

    $out .= "</thead><tbody>";

    foreach ($dtMesin as $nourut => $r) {
        $tipe = $r['tipe'];
        $ket  = $r['keterangandisplay'];

        $no = '';
        if ($tipe == 'Detail' || $tipe == 'Header') {
            if (!empty($r['noakundisplay']) && strpos($r['noakundisplay'], ',') === false) {
                $no = $r['noakundisplay'];
            }
        }

        // A. Baris Category Header
        if ($tipe == 'Header') {
            if ($no != '') {
                $out .= "<tr class=rowcontent style='font-weight:bold;'>";
                $out .= "<td align=center>" . htmlspecialchars($no) . "</td>";
                $out .= "<td colspan=25><b>" . htmlspecialchars($ket) . "</b></td>";
                $out .= "</tr>";
            } else {
                $out .= "<tr " . $bgCat . ">";
                $out .= "<td></td>";
                $out .= "<td colspan=25><b>" . htmlspecialchars($ket) . "</b></td>";
                $out .= "</tr>";
            }
            continue;
        }

        // B. Baris Detail atau Total
        $d = isset($valData[$nourut]['d']) ? (float)$valData[$nourut]['d'] : 0.00;
        $e = isset($valData[$nourut]['e']) ? (float)$valData[$nourut]['e'] : 0.00;
        $f = isset($valData[$nourut]['f']) ? (float)$valData[$nourut]['f'] : 0.00;
        $g = $e + $f;
        $h = isset($valData[$nourut]['h']) ? (float)$valData[$nourut]['h'] : 0.00;
        $i = isset($valData[$nourut]['i']) ? (float)$valData[$nourut]['i'] : 0.00;
        $j = $h + $i;

        $k = $luasHa > 0 ? ($d / $luasHa) : 0.00;
        $l = $luasHa > 0 ? ($h / $luasHa) : 0.00;
        $m = $luasHa > 0 ? ($i / $luasHa) : 0.00;
        $n = $luasHa > 0 ? ($j / $luasHa) : 0.00;

        $o = $prodRatKg > 0 ? ($d / $prodRatKg) : 0.00;
        $p = $prodRealBlnIniKg > 0 ? ($h / $prodRealBlnIniKg) : 0.00;
        $q = $prodRealBlnLaluKg > 0 ? ($i / $prodRealBlnLaluKg) : 0.00;
        $r_cost = $prodRealSdBlnIniKg > 0 ? ($j / $prodRealSdBlnIniKg) : 0.00;

        $s = $jmlPokok > 0 ? ($d / $jmlPokok) : 0.00;
        $t = $jmlPokok > 0 ? ($h / $jmlPokok) : 0.00;
        $u = $jmlPokok > 0 ? ($i / $jmlPokok) : 0.00;
        $v = $jmlPokok > 0 ? ($j / $jmlPokok) : 0.00;

        $w = $d - $j;
        $x = $g - $j;
        $y = $d > 0 ? (($j / $d) * 100) : 0.00;
        $z_col = $e > 0 ? (($h / $e) * 100) : 0.00;

        $trClass = "class=rowcontent";
        $trAttr  = "";
        if ($tipe == 'Total') {
            if ($nourut == 100 || stripos($ket, 'TOTAL') !== false || stripos($ket, 'JUMLAH') !== false) {
                $trClass = "class=rowheader";
                $trAttr  = $bgTot;
            } else {
                $trClass = "class=rowcontent";
                $trAttr  = $bgSub;
            }
        }

        $ketDisplay = htmlspecialchars($ket);
        if ($no == 'a.' || $no == 'b.' || substr($no, 0, 1) == 'a' || substr($no, 0, 1) == 'b') {
            $ketDisplay = "&nbsp;&nbsp;" . $ketDisplay;
        }

        $out .= "<tr " . $trClass . " " . $trAttr . ">";
        $out .= "<td align=center>" . htmlspecialchars($no) . "</td>";
        $out .= "<td align=left>" . ($tipe == 'Total' ? "<b>" . $ketDisplay . "</b>" : $ketDisplay) . "</td>";
        $out .= "<td align=right>" . fmtRp($d) . "</td>";
        $out .= "<td align=right>" . fmtRp($e) . "</td>";
        $out .= "<td align=right>" . fmtRp($f) . "</td>";
        $out .= "<td align=right>" . fmtRp($g) . "</td>";
        $out .= "<td align=right>" . fmtRp($h) . "</td>";
        $out .= "<td align=right>" . fmtRp($i) . "</td>";
        $out .= "<td align=right>" . fmtRp($j) . "</td>";
        $out .= "<td align=right>" . fmtNum($k, 0) . "</td>";
        $out .= "<td align=right>" . fmtNum($l, 0) . "</td>";
        $out .= "<td align=right>" . fmtNum($m, 0) . "</td>";
        $out .= "<td align=right>" . fmtNum($n, 0) . "</td>";
        $out .= "<td align=right>" . fmtNum($o, 2) . "</td>";
        $out .= "<td align=right>" . fmtNum($p, 2) . "</td>";
        $out .= "<td align=right>" . fmtNum($q, 2) . "</td>";
        $out .= "<td align=right>" . fmtNum($r_cost, 2) . "</td>";
        $out .= "<td align=right>" . fmtNum($s, 0) . "</td>";
        $out .= "<td align=right>" . fmtNum($t, 0) . "</td>";
        $out .= "<td align=right>" . fmtNum($u, 0) . "</td>";
        $out .= "<td align=right>" . fmtNum($v, 0) . "</td>";
        $out .= "<td align=right>" . fmtRp($w) . "</td>";
        $out .= "<td align=right>" . fmtRp($x) . "</td>";
        $out .= "<td align=right>" . fmtPct($y) . "</td>";
        $out .= "<td align=right>" . fmtPct($z_col) . "</td>";
        $out .= "</tr>";
    }

    $out .= "</tbody></table>";
    return $out;
}

$tab = "";


// =========================================================================
// 9. RENDER TABEL 1: REALISASI BIAYA KEBUN (MAIN REPORT)
// =========================================================================
$title1 = "<table border=0 cellspacing=0 cellpadding=1 style='font-size:12px;margin-bottom:10px;'>";
$title1 .= "<tr><td colspan=26><b>KEBUN " . htmlspecialchars($namaPt) . "</b></td></tr>";
$title1 .= "<tr><td colspan=26><b>REALISASI BIAYA " . htmlspecialchars($thn) . "</b></td></tr>";
$title1 .= "<tr><td colspan=26><b>PERIODE " . htmlspecialchars($namaBulan) . " " . htmlspecialchars($thn) . "</b></td></tr>";
$title1 .= "</table>";

$tab .= renderTable26Col(
    $title1,
    $dtMesin1,
    $valData1,
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
    $bgCat
);


// =========================================================================
// 10. RENDER TABEL 2: REALISASI BIAYA TBM 1
// =========================================================================
$title2 = "<br><br><table border=0 cellspacing=0 cellpadding=1 style='font-size:12px;margin-bottom:10px;'>";
$title2 .= "<tr><td colspan=26><b>KEBUN " . htmlspecialchars($namaPt) . "</b></td></tr>";
$title2 .= "<tr><td colspan=26><b>REALISASI BIAYA TBM 1 " . htmlspecialchars($thn) . "</b></td></tr>";
$title2 .= "<tr><td colspan=26><b>PERIODE " . htmlspecialchars($namaBulan) . " " . htmlspecialchars($thn) . "</b></td></tr>";
$title2 .= "</table>";

$tab .= renderTable26Col(
    $title2,
    $dtMesin2,
    $valData2,
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

$tab .= renderTable26Col(
    $title3,
    $dtMesin3,
    $valData3,
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
// $tab .= "<br><br>";
// $tab .= "<table border=0 cellspacing=0 cellpadding=2 style='font-size:11px;' width='100%'>";
// $tab .= "<tr><td colspan=26 align=right style='padding-right:80px;'><b>Kebun " . htmlspecialchars($namaPt) . ", " . date('d') . " " . htmlspecialchars($namaBulan) . " " . htmlspecialchars($thn) . "</b></td></tr>";
// $tab .= "<tr align=center style='font-weight:bold;'><td colspan=8>Disetujui oleh,</td><td colspan=10>Diketahui oleh,</td><td colspan=8>Dibuat oleh,</td></tr>";
// $tab .= "<tr height=60><td colspan=26>&nbsp;</td></tr>";
// $tab .= "<tr align=center style='font-weight:bold;'><td colspan=8>( .................................... )</td><td colspan=10>( .................................... )</td><td colspan=8>( .................................... )</td></tr>";
// $tab .= "</table>";


// =========================================================================
// 13. SWITCH OUTPUT BERDASARKAN PARAMETER PROSES
// =========================================================================
switch ($proses) {
    case 'preview':
        echo $tab;
        break;

    case 'excel':
        $tab .= "<br><br><table border=0><tr><td colspan=5>Printed By: " . (isset($_SESSION['standard']['username']) ? $_SESSION['standard']['username'] : 'User') . " | Date: " . date('d-m-Y H:i:s') . "</td></tr></table>";
        $nop_ = "Realisasi_Biaya_Kebun_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $pt) . "_" . $periode;
        if (strlen($tab) > 0) {
            if (!is_dir('tempExcel')) {
                @mkdir('tempExcel', 0777, true);
            }
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

    default:
        echo $tab;
        break;
}
?>
