<?php
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$bulansekarang    = date('m');
$tahunsekarang    = date('Y');
$tanggal_sekarang = date('Y-m-d');

// =========================================================================
// 1. FUNGSI BANTUAN (HELPER FUNCTIONS)
// =========================================================================

/**
 * Mengambil setup hak cuti berdasarkan prioritas hirarki
 */
function getHakCutiSetup($setups, $unit, $gol, $level, $type) 
{
    // Kumpulkan dari hierarki tertinggi ke terendah
    $prioritas = [
        isset($setups[1][$unit][$gol][$level][$type]) ? $setups[1][$unit][$gol][$level][$type] : null,
        isset($setups[2][$unit][$gol][$type])         ? $setups[2][$unit][$gol][$type] : null,
        isset($setups[3][$unit][$level][$type])       ? $setups[3][$unit][$level][$type] : null,
        isset($setups[4][$unit][$type])               ? $setups[4][$unit][$type] : null,
    ];

    // Cek mana yang cocok duluan, lalu cari tahu apakah dia pakai AwalTahun atau TMK
    foreach ($prioritas as $setup) {
        if ($setup) {
            if (isset($setup['AwalTahun'])) return ['tipe_mulai' => 'AwalTahun', 'data' => $setup['AwalTahun']];
            if (isset($setup['TMK']))       return ['tipe_mulai' => 'TMK', 'data' => $setup['TMK']];
            if (isset($setup['TanggalPengangkatan'])) return ['tipe_mulai' => 'TanggalPengangkatan', 'data' => $setup['TanggalPengangkatan']];
        }
    }
    
    return null;
}

/**
 * Menghitung jatah prorata cuti tahunan untuk karyawan baru
 */
function hitungCutiTahunanAwalTahun($tmk, $hakcuti, $tahunProses) 
{
    $thn_tmk = (int) date('Y', strtotime($tmk));
    $bln_tmk = (int) date('m', strtotime($tmk));
    
    if ($tahunProses == $thn_tmk) {
        return 0; // Tahun pertama belum dapat (sesuaikan dengan kebijakan)
    } elseif ($tahunProses - $thn_tmk == 1) {
        return round((12 - $bln_tmk + 1) / 12 * $hakcuti, 2); // Prorata tahun kedua
    } else {
        return round($hakcuti, 2); // Full
    }
}

function hitungCutiTahunanTMK($tmk, $hakcuti, $tahunProses) 
{
    $thn_tmk = (int) date('Y', strtotime($tmk));
    $bln_tmk = (int) date('m', strtotime($tmk));
    
    if ($tahunProses == $thn_tmk) {
        return 0; // Tahun pertama belum
    } elseif ($tahunProses - $thn_tmk == 1) {
        return round(($bln_tmk / 12) * $hakcuti, 2); // Prorata versi TMK
    } else {
        return round($hakcuti, 2); // Full
    }
}

function hitungCutiTahunanTPK($tpk, $hakcuti, $tahunProses) 
{
    $thn_tpk = (int) date('Y', strtotime($tpk));
    $bln_tpk = (int) date('m', strtotime($tpk));
    
    if ($tahunProses == $thn_tpk) {
        return 0; // Tahun pertama belum
    } elseif ($tahunProses - $thn_tpk == 1) {
        return round(($bln_tpk / 12) * $hakcuti, 2); // Prorata versi TMK
    } else {
        return round($hakcuti, 2); // Full
    }
}

/**
 * Menentukan tanggal kadaluarsa hak cuti
 */
function hitungMasaBerlaku($tahunDari, $masaBerlaku) 
{
    // exit('warning:'.$tahunDari.'___'.$masaBerlaku);
    // if ($masaBerlaku == 1) {
    //     return date('Y-12-31', strtotime($tahunDari));
    // }
    // if ($masaBerlaku == 2) {
    //     return date('Y-12-31', strtotime('+1 year', strtotime($tahunDari)));
    // }
    // if ($masaBerlaku == 3) {
    //     return date('Y-12-31', strtotime('+2 year', strtotime($tahunDari)));
    // }

    return date(
            'Y-m-d',
            strtotime("+{$masaBerlaku} months", strtotime($tahunDari))
        );
    
    return '0000-00-00';
}


// =========================================================================
// 2. AMBIL DATA SETUP MASTER
// =========================================================================

$setupHakCuti = [1 => [], 2 => [], 3 => [], 4 => []];

$str = "SELECT * FROM ".$dbname.".sdm_5hakcuti";
$res = fetchdata($str);

foreach ($res as $b) {
    $dt = [
        'hakcuti'     => $b['hakcuti'], 
        'masaberlaku' => $b['masaberlaku']
    ];
    
    if ($b['kodegolongan'] != "" && $b['levelkaryawan'] != "") {
        $setupHakCuti[1][$b['kodeorg']][$b['kodegolongan']][$b['levelkaryawan']][$b['type']][$b['bulanmulai']] = $dt;
    }
    if ($b['kodegolongan'] != "" && $b['levelkaryawan'] == "") {
        $setupHakCuti[2][$b['kodeorg']][$b['kodegolongan']][$b['type']][$b['bulanmulai']] = $dt;
    }
    if ($b['kodegolongan'] == "" && $b['levelkaryawan'] != "") {
        $setupHakCuti[3][$b['kodeorg']][$b['levelkaryawan']][$b['type']][$b['bulanmulai']] = $dt;
    }
    if ($b['kodegolongan'] == "" && $b['levelkaryawan'] == "") {
        $setupHakCuti[4][$b['kodeorg']][$b['type']][$b['bulanmulai']] = $dt;
    }
}


// =========================================================================
// 3. SIAPKAN QUERY UPSERT (PREPARED STATEMENTS) ANTI DOUBLE
// =========================================================================

try {
    $owlPDO->beginTransaction();

    // Query Upsert sdm_cutiht
    $sql_upsert_ht = "
        INSERT INTO ".$dbname.".sdm_cutiht 
            (kodeorg, karyawanid, periodecuti, keterangan, dari, sampai, hakcuti, cutitambahan, adjs_hakcuti, diambil, sisa) 
        VALUES 
            (?, ?, ?, '', ?, ?, ?, 0, 0, 0, ?)
        ON DUPLICATE KEY UPDATE 
            hakcuti = VALUES(hakcuti), 
            sampai  = VALUES(sampai), 
            sisa    = (VALUES(hakcuti) + cutitambahan + adjs_hakcuti) - diambil
    ";
    $stmt_upsert_ht = $owlPDO->prepare($sql_upsert_ht);

    // Query Upsert sdm_cutibulananht
    $sql_upsert_bln = "
        INSERT INTO ".$dbname.".sdm_cutibulananht 
            (kodeorg, karyawanid, periodecuti, keterangan, dari, sampai, hakcuti, diambil, sisa) 
        VALUES 
            (?, ?, ?, '', ?, ?, ?, 0, ?)
        ON DUPLICATE KEY UPDATE 
            hakcuti = VALUES(hakcuti), 
            sisa    = VALUES(hakcuti) - diambil
    ";
    $stmt_upsert_bln = $owlPDO->prepare($sql_upsert_bln);


    // =========================================================================
    // 4. PROSES KARYAWAN (SINGLE LOOP)
    // =========================================================================
    
   // =========================================================================
    // 4. PROSES KARYAWAN (SINGLE LOOP)
    // =========================================================================
    
    $str_kar = "
        SELECT karyawanid, lokasitugas, kodegolongan, levelkaryawan, tanggalmasuk, tanggalpengangkatan 
        FROM ".$dbname.".datakaryawan 
        WHERE (tanggalkeluar = '0000-00-00' OR tanggalkeluar >= '".$tanggal_sekarang."') 
    ";
    $res_kar = fetchdata($str_kar);

    foreach ($res_kar as $kar) {
        $id   = $kar['karyawanid'];
        $unit = $kar['lokasitugas'];
        $gol  = $kar['kodegolongan'];
        $lvl  = $kar['levelkaryawan'];
        $tmk  = $kar['tanggalmasuk'];
        $tpk  = $kar['tanggalpengangkatan'];

        // $tmk_asli = $kar['tanggalmasuk'];
        // $tgl_angkat = $kar['tanggalpengangkatan'] ?? '0000-00-00';

        // // Pakai tanggal pengangkatan kalau ada
        // Uncomen ini kalau pakek tanggal pengangkatan
        // $tmk = ($tgl_angkat != '0000-00-00' && $tgl_angkat != '') ? $tgl_angkat : $tmk_asli;

        
        $bln_tmk = date('m', strtotime($tmk));
        $thn_tmk = date('Y', strtotime($tmk));

        $bln_tpk = date('m', strtotime($tpk));
        $thn_tpk = date('Y', strtotime($tpk));

        // ---------------------------------------------------------
        // A. Cek Setup Tahunan
        // ---------------------------------------------------------
        $setupTahunan = getHakCutiSetup($setupHakCuti, $unit, $gol, $lvl, 'Tahunan');

        
        if ($setupTahunan) {
            $tipe_mulai = $setupTahunan['tipe_mulai'];
            $data_cuti  = $setupTahunan['data'];

            if ($tipe_mulai == 'AwalTahun' && $bulansekarang == '01') {
                $hakcuti = hitungCutiTahunanAwalTahun($tmk, $data_cuti['hakcuti'], $tahunsekarang);
                if ($hakcuti > 0) {
                    $dari   = date("$tahunsekarang-01-d", strtotime($tmk));
                    $sampai = hitungMasaBerlaku($dari, $data_cuti['masaberlaku']);
                    $stmt_upsert_ht->execute([$unit, $id, $tahunsekarang, $dari, $sampai, $hakcuti, $hakcuti]);
                }
            } 
            elseif ($tipe_mulai == 'TMK' && $bulansekarang == $bln_tmk) {
                $hakcuti = hitungCutiTahunanTMK($tmk, $data_cuti['hakcuti'], $tahunsekarang);
                if ($hakcuti > 0) {
                    $dari   = date("$tahunsekarang-m-d", strtotime($tmk)); // Mengikuti bulan TMK
                    $sampai = hitungMasaBerlaku($dari, $data_cuti['masaberlaku']);
                    $stmt_upsert_ht->execute([$unit, $id, $tahunsekarang, $dari, $sampai, $hakcuti, $hakcuti]);
                }
            }
            elseif ($tipe_mulai == 'TanggalPengangkatan' && $bulansekarang == $bln_tpk) {
                $hakcuti = hitungCutiTahunanTPK($tpk, $data_cuti['hakcuti'], $tahunsekarang);
                if ($hakcuti > 0) {
                    $dari   = date("$tahunsekarang-m-d", strtotime($tpk)); // Mengikuti bulan TMK
                    $sampai = hitungMasaBerlaku($dari, $data_cuti['masaberlaku']);
                    $stmt_upsert_ht->execute([$unit, $id, $tahunsekarang, $dari, $sampai, $hakcuti, $hakcuti]);
                }
            }
        }

        // ---------------------------------------------------------
        // B. Cek Setup Bulanan (Sudah Diperbaiki untuk Karyawan Lama)
        // ---------------------------------------------------------
        $setupBulanan = getHakCutiSetup($setupHakCuti, $unit, $gol, $lvl, 'Bulanan');
        
        if ($setupBulanan) {
            $tipe_mulai     = $setupBulanan['tipe_mulai'];
            $data_cuti      = $setupBulanan['data'];
            $bulan_berjalan = (int) $bulansekarang;

            if ($tipe_mulai == 'AwalTahun') {
                // Logika AwalTahun: Selalu mulai dari Januari (Bulan 1)
                $total_hakcuti_bln = round(($bulan_berjalan / 12) * $data_cuti['hakcuti'], 2);
                $dari              = date("$tahunsekarang-01-d", strtotime($tmk));
                $sampai            = hitungMasaBerlaku($dari, $data_cuti['masaberlaku']);

                $stmt_upsert_ht->execute([$unit, $id, $tahunsekarang, $dari, $sampai, $total_hakcuti_bln, $total_hakcuti_bln]);

                for ($i = 1; $i <= $bulan_berjalan; $i++) {
                    $dari_bln   = date("$tahunsekarang-$i-d", strtotime($tmk));
                    $sampai_bln = hitungMasaBerlaku($dari_bln, $data_cuti['masaberlaku']);
                    $hakcuti_per_bln = round($data_cuti['hakcuti'] / 12, 2);
                    if ($i == 12) $hakcuti_per_bln = $data_cuti['hakcuti'] - (round($data_cuti['hakcuti'] / 12, 2) * 11);

                    $stmt_upsert_bln->execute([$unit, $id, $tahunsekarang, $dari_bln, $sampai_bln, $hakcuti_per_bln, $hakcuti_per_bln]);
                }
            } 
            elseif ($tipe_mulai == 'TMK') {
                $thn_tmk = (int) date('Y', strtotime($tmk));
                $bln_tmk = (int) date('m', strtotime($tmk));

                // Penentuan titik awal (Start) dan jumlah bulan aktif
                if ($thn_tmk < (int)$tahunsekarang) {
                    // KARYAWAN LAMA: Mulai hitung dari Januari (Bulan 1)
                    $bulan_aktif = $bulan_berjalan;
                    $start_loop  = 1;
                } else {
                    // KARYAWAN BARU (Tahun TMK == Tahun Sekarang): Mulai dari bulan masuknya
                    $bulan_aktif = ($bulan_berjalan >= $bln_tmk) ? ($bulan_berjalan - $bln_tmk + 1) : 0;
                    $start_loop  = $bln_tmk;
                }

                if ($bulan_aktif > 0) {
                    $total_hakcuti_bln = round(($bulan_aktif / 12) * $data_cuti['hakcuti'], 2);
                    $dari   = date("$tahunsekarang-01-01"); 
                    $sampai = hitungMasaBerlaku($dari, $data_cuti['masaberlaku']);

                    // Update Header
                    $stmt_upsert_ht->execute([$unit, $id, $tahunsekarang, $dari, $sampai, $total_hakcuti_bln, $total_hakcuti_bln]);

                    // Generate Detail Bulanan
                    for ($i = $start_loop; $i <= $bulan_berjalan; $i++) {
                        $dari_bln   = date("$tahunsekarang-$i-d", strtotime($tmk));
                        $sampai_bln = hitungMasaBerlaku($dari_bln, $data_cuti['masaberlaku']);
                        $hakcuti_per_bln = round($data_cuti['hakcuti'] / 12, 2);

                        $stmt_upsert_bln->execute([$unit, $id, $tahunsekarang, $dari_bln, $sampai_bln, $hakcuti_per_bln, $hakcuti_per_bln]);
                    }
                }
            }
        }
    }


    // =========================================================================
    // 5. PROSES HANGUS / ADJUSTMENT CUTI KADALUARSA
    // =========================================================================
    
    $bulanLalu = substr(date('Y-m-d'), 0, 7);
    
    // Potong Cuti Bulanan yang kadaluarsa
    $sql_hangus_bln = "
        SELECT * FROM ".$dbname.".sdm_cutibulananht 
        WHERE sisa > 0 AND LEFT(sampai, 7) <= '$bulanLalu'
    ";
    $res_hangus_bln = fetchdata($sql_hangus_bln);
    
    $sql_adjs_insert = "
        INSERT INTO ".$dbname.".sdm_5cutiadjsment 
            (kodeorg, karyawanid, periodecuti, adjs_hakcuti, keterangan, createtime, flag) 
        VALUES 
            (?, ?, ?, ?, 'Automatic System Expired', NOW(), 1)
    ";
    $stmt_adjs_insert = $owlPDO->prepare($sql_adjs_insert);
    
    $sql_ht_kurangi = "
        UPDATE ".$dbname.".sdm_cutiht 
        SET sisa = sisa - ?, adjs_hakcuti = adjs_hakcuti - ? 
        WHERE kodeorg = ? AND karyawanid = ? AND periodecuti = ?
    ";
    $stmt_ht_kurangi = $owlPDO->prepare($sql_ht_kurangi);
    
    $sql_bln_hangus = "
        UPDATE ".$dbname.".sdm_cutibulananht 
        SET sisa = 0, diambil = diambil + ? 
        WHERE kodeorg = ? AND karyawanid = ? AND periodecuti = ? AND dari = ? AND sampai = ?
    ";
    $stmt_bln_hangus = $owlPDO->prepare($sql_bln_hangus);

    foreach ($res_hangus_bln as $hangus) {
        $sisa_hangus = $hangus['sisa'];
        $k_id        = $hangus['karyawanid'];
        $k_org       = $hangus['kodeorg'];
        $k_per       = $hangus['periodecuti'];

        // 1. Catat ke tabel adjustment (minus)
        $stmt_adjs_insert->execute([$k_org, $k_id, $k_per, ($sisa_hangus * -1)]);
        
        // 2. Kurangi total sisa di header
        $stmt_ht_kurangi->execute([$sisa_hangus, $sisa_hangus, $k_org, $k_id, $k_per]);
        
        // 3. Nol-kan sisa di detail bulanan
        $stmt_bln_hangus->execute([$sisa_hangus, $k_org, $k_id, $k_per, $hangus['dari'], $hangus['sampai']]);
    }

    // Eksekusi semua transaksi
    $owlPDO->commit();
    echo "Sukses: Pembaruan Hak Cuti Selesai!";
    
} catch (PDOException $e) {
    $owlPDO->rollback();
    echo "Error Database: " . addslashes($e->getMessage());
    die();
} catch (Exception $e) {
    $owlPDO->rollback();
    echo "Error Logic: " . addslashes($e->getMessage());
    die();
}
?>