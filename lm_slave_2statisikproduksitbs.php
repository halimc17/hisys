<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$pt = $_POST['pt'];
$periode = $_POST['periode'];
$sd = $periode;

$tahun = substr($periode, 0, 4);
$awalTahun = $tahun . "-01-01";
$awalPeriode = $tahun . "-01";

$awalTahunLalu = ($tahun - 1) . "-01-01";
$awalPeriodeLY = ($tahun - 1) . "-01";
$intiplasma=checkPostGet('intiplasma','');

$inplas="";
if($intiplasma!=''){
    $inplas=" AND sb.intiplasma='".$intiplasma."'";
}

//1. LUAS TM (MASTER TAHUN TANAM) & DIVISI
$sqlTM = "
    SELECT 
        sb.tahuntanam,
        SUM(sb.luasareaproduktif) AS luas_tm,
        GROUP_CONCAT(
            DISTINCT CONCAT('Divisi ', RIGHT(LEFT(sb.kodeorg,6),2))
            ORDER BY RIGHT(LEFT(sb.kodeorg,6),2)
            SEPARATOR ', '
        ) AS daftar_divisi
    FROM {$dbname}.setup_blok sb
    JOIN {$dbname}.organisasi o 
        ON sb.kodeorg = o.kodeorganisasi
    WHERE o.induk LIKE '{$pt}%' {$inplas}
    AND sb.tahuntanam != '0'
    GROUP BY sb.tahuntanam
    ORDER BY sb.tahuntanam
";

$dataTM = fetchData($sqlTM);

//2. PANEN
/* sqlPanen dinonaktifkan sementara, pakai sama dengan target
$sqlPanen = "
    SELECT 
        sb.tahuntanam,
        SUM(kp.luaspanen) AS total_hasilkerja
    FROM {$dbname}.kebun_prestasi_detail kp
    JOIN {$dbname}.setup_blok sb 
        ON kp.kodeorg = sb.kodeorg
    WHERE sb.kodeorg LIKE '{$pt}%' {$inplas}
        AND kp.notransaksi LIKE '%PNN%'
    GROUP BY sb.tahuntanam
    ORDER BY sb.tahuntanam;
";
// exit('warning: '.$sqlPanen);

$tmpPanen = fetchData($sqlPanen);
$panen = [];
foreach ($tmpPanen as $r) {
    $panen[$r['tahuntanam']] = $r['total_hasilkerja'];
}
*/

//3. TARGET
$sqlTarget = "
    SELECT
        sb.tahuntanam,
        SUM(bb.hathnini) AS target_luas
    FROM {$dbname}.bgt_blok bb
    JOIN {$dbname}.setup_blok sb 
        ON bb.kodeblok = sb.kodeorg
    JOIN {$dbname}.organisasi o 
        ON sb.kodeorg = o.kodeorganisasi
    WHERE o.induk LIKE '{$pt}%' {$inplas}
    AND sb.tahuntanam != '0'
    GROUP BY sb.tahuntanam
";
$tmpTarget = fetchData($sqlTarget);
$target = [];
foreach ($tmpTarget as $r) {
    $target[$r['tahuntanam']] = $r['target_luas'];
}

//4. DIVISI (Sudah digabung ke query 1)


//5. TABEL
echo "
<table border='1' cellspacing='0' cellpadding='5' width='100%'>
<thead>
<tr style='background:#ccffcc;font-weight:bold;text-align:center;'>
    <th rowspan='2'>Kode<br>Tanam</th>
    <th rowspan='2'>Tahun Tanam</th>
    <th colspan='3'>Hektaran</th>
    <th rowspan='2'>Populasi</th>
    <th rowspan='2'>Populasi/Ha</th>
    <th rowspan='2'>Divisi</th>
    <th rowspan='2'>Rotasi Panen</th>
</tr>
<tr style='background:#ccffcc;font-weight:bold;text-align:center;'>
    <th>Luas TM</th>
    <th>Panen</th>
    <th>Target</th>
</tr>
</thead>
<tbody>
";

$gt_luas = $gt_panen = $gt_target = $gt_pop = 0;

foreach ($dataTM as $r) {
    $tt = $r['tahuntanam'];
    $luas = $r['luas_tm'];

    $luasTM[$tt] = $luas;

    $tg = $target[$tt] ?? 0;
    // $pn = $panen[$tt] ?? 0;
    $pn = $tg;
    $dv = $r['daftar_divisi'] ?? '-';

    //Masih Status Populasi /ha
    $pop_ha = 142;
    $pop = $luas * $pop_ha;

    //Rotasi Belum Jelas darimana
    $rotasi = 9;

    echo "
    <tr>
        <td align='center'>Rekap</td>
        <td align='center'>{$tt}</td>
        <td align='right'>" . number_format($luas, 2) . "</td>
        <td align='right'>" . number_format($pn, 2) . "</td>
        <td align='right'>" . number_format($tg, 2) . "</td>
        <td align='right'>" . number_format($pop, 0) . "</td>
        <td align='center'>{$pop_ha}</td>
        <td align='left'>{$dv}</td>
        <td align='center'>{$rotasi}</td>
    </tr>";

    $gt_luas += $luas;
    $gt_panen += $pn;
    $gt_target += $tg;
    $gt_pop += $pop;
}

//6. GRAND TOTAL
echo "
<tr style='font-weight:bold;background:#00cfff;'>
    <td colspan='2'>GRAND TOTAL</td>
    <td align='right'>" . number_format($gt_luas, 2) . "</td>
    <td align='right'>" . number_format($gt_panen, 2) . "</td>
    <td align='right'>" . number_format($gt_target, 2) . "</td>
    <td align='right'>" . number_format($gt_pop, 0) . "</td>
    <td align='center'>142</td>
    <td align='center'></td>
    <td align='center'></td>
</tr>
</tbody>
</table>
<br><br>";

//Produksi TBS Netto Tahun Ini (Bulan Ini & SD Bulan Ini)
$akhirSD = date('Y-m-t', strtotime($sd . '-01'));

$NettoTahunIni = "
    SELECT 
        sb.tahuntanam,
        SUM(CASE WHEN spb.tanggalpanen LIKE '{$periode}%' THEN spb.kgwbnetto ELSE 0 END) AS total_kgwbnetto,
        SUM(spb.kgwbnetto) AS total_kgwbnetto_sd
    FROM kebun_spbdt_detail spb
    JOIN setup_blok sb ON spb.indukblok = sb.indukblok
    JOIN organisasi o ON sb.kodeorg = o.kodeorganisasi
    WHERE o.kodeorganisasi LIKE '{$pt}%' {$inplas}
      AND sb.tahuntanam <> '0'
      AND spb.tanggalpanen BETWEEN '{$awalTahun}' AND '{$akhirSD}'
    GROUP BY sb.tahuntanam
";
// exit('warning:'.$NettoTahunIni);

$nettoBulanIni = [];
$nettoSDBulanIni = [];
$tmp = fetchData($NettoTahunIni);
foreach ($tmp as $r) {
    $nettoBulanIni[$r['tahuntanam']] = $r['total_kgwbnetto'];
    $nettoSDBulanIni[$r['tahuntanam']] = $r['total_kgwbnetto_sd'];
}

//TahunLalu
$periodeThnLalu = date('Y-m', strtotime('-1 year', strtotime($periode . '-01')));
$sdThnLalu = date('Y-m', strtotime('-1 year', strtotime($sd . '-01')));
$akhirSDThnLalu = date('Y-m-t', strtotime($sdThnLalu . '-01'));

//Produksi TBS Netto Tahun Lalu (Bulan Ini & SD Bulan Ini)
$NettoTahunLalu = "
    SELECT 
        sb.tahuntanam,
        SUM(CASE WHEN spb.tanggalpanen LIKE '{$periodeThnLalu}%' THEN spb.kgwbnetto ELSE 0 END) AS total_kgwbnetto_bulanini,
        SUM(spb.kgwbnetto) AS total_kgwbnetto_bulanini_sd
    FROM kebun_spbdt_detail spb
    JOIN setup_blok sb ON spb.indukblok = sb.indukblok
    JOIN organisasi o ON sb.kodeorg = o.kodeorganisasi
    WHERE o.kodeorganisasi LIKE '{$pt}%' {$inplas}
      AND sb.tahuntanam <> '0'
      AND spb.tanggalpanen BETWEEN '{$awalTahunLalu}' AND '{$akhirSDThnLalu}'
    GROUP BY sb.tahuntanam
";
// exit('warning:'.$NettoTahunLalu);

$nettoBulanIniThnLalu = [];
$nettoSDBulanIniThnLalu = [];
$tmp = fetchData($NettoTahunLalu);
foreach ($tmp as $r) {
    $nettoBulanIniThnLalu[$r['tahuntanam']] = $r['total_kgwbnetto_bulanini'];
    $nettoSDBulanIniThnLalu[$r['tahuntanam']] = $r['total_kgwbnetto_bulanini_sd'];
}

//Target TBS 5

list($tahun, $bulan) = explode('-', $periode);
$kolomKg = 'kg' . str_pad($bulan, 2, '0', STR_PAD_LEFT);

$targetBulanIni = "
    SELECT 
        sb.tahuntanam,
        COALESCE(SUM(bpk.$kolomKg), 0) AS targetbulanini
    FROM setup_blok sb
    JOIN organisasi o
        ON sb.kodeorg = o.kodeorganisasi
    LEFT JOIN bgt_produksi_kebun bpk
        ON bpk.kodeblok = sb.kodeorg
    AND bpk.tahunbudget = '$tahun'
    WHERE o.kodeorganisasi LIKE '{$pt}%' {$inplas}
    GROUP BY sb.tahuntanam
    ORDER BY sb.tahuntanam;
";
// exit('warning:'.$targetBulanIni);

$targetbulanini = [];
$tmp = fetchData($targetBulanIni);
foreach ($tmp as $r) {
    $targetbulanini[$r['tahuntanam']] = $r['targetbulanini'];
}

$start = DateTime::createFromFormat('Y-m', $awalPeriode);
$end = DateTime::createFromFormat('Y-m', $sd);

$range = [];
$tmp = clone $start;

while ($tmp <= $end) {
    $y = $tmp->format('Y');
    $m = $tmp->format('m');
    $range[$y][] = 'kg' . $m;
    $tmp->modify('+1 month');
}

$sumExpr = [];

foreach ($range as $year => $cols) {
    $sumExpr[] = "
        SUM(
            CASE 
                WHEN bpk.tahunbudget = '$year' 
                THEN " . implode(' + ', array_map(fn($c) => "COALESCE(bpk.$c,0)", $cols)) . "
                ELSE 0 
            END
        )
    ";
}

$sumSQL = implode(' + ', $sumExpr);

$targetSDBulanIni = "
    SELECT 
        sb.tahuntanam,
        COALESCE($sumSQL, 0) AS targetbulanini_sd
    FROM setup_blok sb
    JOIN organisasi o
        ON sb.kodeorg = o.kodeorganisasi
    LEFT JOIN bgt_produksi_kebun bpk
        ON bpk.kodeblok = sb.kodeorg
    WHERE o.kodeorganisasi LIKE '{$pt}%' {$inplas}
    GROUP BY sb.tahuntanam
    ORDER BY sb.tahuntanam;
";
$targetsdbulanini = [];
$tmp = fetchData($targetSDBulanIni);
foreach ($tmp as $r) {
    $targetsdbulanini[$r['tahuntanam']] = $r['targetbulanini_sd'];
}


//Target Tahunan
$tahunTarget = date('Y', strtotime($periode . '-01'));
$targetTahun = "
    SELECT 
        sb.tahuntanam,
        sum(totalkg) as targettahun
    FROM setup_blok sb
    JOIN organisasi o
        ON sb.kodeorg = o.kodeorganisasi
    LEFT JOIN bgt_produksi_kebun bpk
        ON bpk.kodeblok = sb.kodeorg
    AND bpk.tahunbudget = '{$tahunTarget}'
    WHERE o.kodeorganisasi LIKE '{$pt}%' {$inplas}
    AND sb.tahuntanam <> '0'
    GROUP BY sb.tahuntanam
    ORDER BY sb.tahuntanam;
";
$targettahun = [];
$tmp = fetchData($targetTahun);
foreach ($tmp as $r) {
    $targettahun[$r['tahuntanam']] = $r['targettahun'];
}

//Produksi TBS/1 orang pemanen Aktual Bulan Ini 

$sqlAktualPerSatuOrang = "
SELECT
    tahuntanam,
    SUM(avg_hasilkerjakg) / COUNT(nik) AS avg_semua_nik 
FROM (
    SELECT
        sb.tahuntanam,
        kp.nik,
        AVG(kp.hasilkerjakg) AS avg_hasilkerjakg
    FROM {$dbname}.kebun_prestasi kp
    JOIN {$dbname}.kebun_aktifitas ka
        ON ka.notransaksi = kp.notransaksi
    JOIN {$dbname}.setup_blok sb
        ON sb.indukblok = kp.kodeorg
    JOIN {$dbname}.organisasi o
        ON o.kodeorganisasi = sb.kodeorg
    WHERE kp.notransaksi LIKE '%PNN%'
      AND kp.hasilkerjakg <> 0
      AND ka.tanggal LIKE '{$periode}%'
      AND o.induk LIKE '{$pt}%' {$inplas}
      AND sb.tahuntanam <> '0'
    GROUP BY sb.tahuntanam, kp.nik
) x
GROUP BY tahuntanam
ORDER BY tahuntanam;
";

$aktualpersatuorang = [];
$tmptaktualpersatuorang = fetchData($sqlAktualPerSatuOrang);
foreach ($tmptaktualpersatuorang as $r) {
    $aktualpersatuorang[$r['tahuntanam']] = $r['avg_semua_nik'];
}

//Produksi TBS/1 orang pemanen Aktual SD Bulan Ini 

$sqlAktualPerSatuOrangSD = "
SELECT
    tahuntanam,
    SUM(avg_hasilkerjakg) / COUNT(nik) AS avg_semua_niksd
FROM (
    SELECT
        sb.tahuntanam,
        kp.nik,
        AVG(kp.hasilkerjakg) AS avg_hasilkerjakg
    FROM {$dbname}.kebun_prestasi kp
    JOIN {$dbname}.kebun_aktifitas ka
        ON ka.notransaksi = kp.notransaksi
    JOIN {$dbname}.setup_blok sb
        ON sb.indukblok = kp.kodeorg
    JOIN {$dbname}.organisasi o
        ON o.kodeorganisasi = sb.kodeorg
    WHERE kp.notransaksi LIKE '%PNN%'
      AND kp.hasilkerjakg <> 0
      AND ka.tanggal BETWEEN '{$awalTahun}' AND '{$akhirSD}'
      AND o.induk LIKE '{$pt}%' {$inplas}
      AND sb.tahuntanam <> '0'
    GROUP BY sb.tahuntanam, kp.nik
) x
GROUP BY tahuntanam
ORDER BY tahuntanam;
";

$aktualpersatuorangsd = [];
$tmptaktualpersatuorangsd = fetchData($sqlAktualPerSatuOrangSD);
foreach ($tmptaktualpersatuorangsd as $r) {
    $aktualpersatuorangsd[$r['tahuntanam']] = $r['avg_semua_niksd'];
}

$periodeLY = date('Y-m', strtotime($periode.' -1 year'));
$akhirSDLY = date('Y-m-t', strtotime($akhirSD.' -1 year'));


$sqlAktualPerSatuOrangLY = "
SELECT
    tahuntanam,
    SUM(avg_hasilkerjakg) / COUNT(nik) AS avg_semua_nik_ly
FROM (
    SELECT
        sb.tahuntanam,
        kp.nik,
        AVG(kp.hasilkerjakg) AS avg_hasilkerjakg
    FROM {$dbname}.kebun_prestasi kp
    JOIN {$dbname}.kebun_aktifitas ka
        ON ka.notransaksi = kp.notransaksi
    JOIN {$dbname}.setup_blok sb
        ON sb.indukblok = kp.kodeorg
    JOIN {$dbname}.organisasi o
        ON o.kodeorganisasi = sb.kodeorg
    WHERE kp.notransaksi LIKE '%PNN%'
      AND kp.hasilkerjakg <> 0
      AND ka.tanggal LIKE '{$periodeLY}%'
      AND o.induk LIKE '{$pt}%' {$inplas}
      AND sb.tahuntanam <> '0'
    GROUP BY sb.tahuntanam, kp.nik
) x
GROUP BY tahuntanam
ORDER BY tahuntanam;
";

$aktualpersatuorangly = [];
$tmp = fetchData($sqlAktualPerSatuOrangLY);
foreach ($tmp as $r) {
    $aktualpersatuorangly[$r['tahuntanam']] = $r['avg_semua_nik_ly'];
}

$sqlAktualPerSatuOrangSDLY = "
SELECT
    tahuntanam,
    SUM(avg_hasilkerjakg) / COUNT(nik) AS avg_semua_nik_sd_ly
FROM (
    SELECT
        sb.tahuntanam,
        kp.nik,
        AVG(kp.hasilkerjakg) AS avg_hasilkerjakg
    FROM {$dbname}.kebun_prestasi kp
    JOIN {$dbname}.kebun_aktifitas ka
        ON ka.notransaksi = kp.notransaksi
    JOIN {$dbname}.setup_blok sb
        ON sb.indukblok = kp.kodeorg
    JOIN {$dbname}.organisasi o
        ON o.kodeorganisasi = sb.kodeorg
    WHERE kp.notransaksi LIKE '%PNN%'
      AND kp.hasilkerjakg <> 0
      AND ka.tanggal BETWEEN '{$awalTahunLalu}' AND '{$akhirSDLY}'
      AND o.induk LIKE '{$pt}%' {$inplas}
      AND sb.tahuntanam <> '0'
    GROUP BY sb.tahuntanam, kp.nik
) x
GROUP BY tahuntanam
ORDER BY tahuntanam;
";

$aktualpersatuorangsdly = [];
$tmp = fetchData($sqlAktualPerSatuOrangSDLY);
foreach ($tmp as $r) {
    $aktualpersatuorangsdly[$r['tahuntanam']] = $r['avg_semua_nik_sd_ly'];
}

$sqlCount = "
    SELECT COUNT(*) AS jml
    FROM datakaryawan
    WHERE kodejabatan = '15'
      AND tanggalkeluar = '0000-00-00'
      AND kodeorganisasi = '{$pt}'
";
$tmp = fetchData($sqlCount);
$jmlKaryawan = $tmp[0]['jml'] ?? 0;


echo "
<table border='1' cellspacing='0' cellpadding='5' width='100%'>
<thead>

<tr style='background:#ccffcc;font-weight:bold;text-align:center;'>
    <th rowspan='3'>Kode<br>Tanam</th>
    <th rowspan='3'>Tahun Tanam</th>

    <th colspan='7'>Produksi TBS</th>
    <th colspan='7'>Produksi TBS / Ha</th>
    <th colspan='7'>Produksi TBS/1 orang pemanen</th>
</tr>

<tr style='background:#ccffcc;font-weight:bold;text-align:center;'>
    <!-- PRODUKSI TBS -->
    <th colspan='2'>Aktual (Netto)</th>
    <th colspan='2'>Bulan yang Sama Tahun Lalu</th>
    <th colspan='3'>Target</th>

    <!-- PRODUKSI TBS / HA -->
    <th colspan='2'>Aktual</th>
    <th colspan='2'>Bulan yang Sama Tahun Lalu</th>
    <th colspan='3'>Target</th>

     <!-- Produksi TBS/1 orang pemanen -->
    <th colspan='2'>Aktual</th>
    <th colspan='2'>Bulan yang Sama Tahun Lalu</th>
    <th colspan='3'>Target</th>
</tr>

<tr style='background:#ccffcc;font-weight:bold;text-align:center;'>
    <!-- TBS -->
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Tahunan</th>

    <!-- TBS / HA -->
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Tahunan</th>

     <!-- Produksi TBS/1 orang pemanen -->
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Tahunan</th>
</tr>

</thead>

<tbody>
";

$gtHa = [
    'nbi' => 0,
    'nsd' => 0,
    'nbi_ly' => 0,
    'nsd_ly' => 0,
    'tbi' => 0,
    'tsd' => 0,
    'thn' => 0
];

$gt = [
  'nbi'=>0,'nsd'=>0,'nbi_ly'=>0,'nsd_ly'=>0,'tbi'=>0,'tsd'=>0,'thn'=>0
];

$gtHaProd = [
    'nbi' => 0,
    'nsd' => 0,
    'nbi_ly' => 0,
    'nsd_ly' => 0,
    'tbi' => 0,
    'tsd' => 0,
    'thn' => 0
];

$gtHaLuas = 0;

$gtOrg = ['tbi'=>0,'tsd'=>0,'thn'=>0];

foreach ($dataTM as $r) {


    $tt = $r['tahuntanam'];

    $luasTT = $luasTM[$tt] ?? 0;
    $luasTT = ($luasTT > 0) ? $luasTT : 1;

    $tgHa = $target[$tt] ?? 0;
    $tgHa = ($tgHa > 0) ? $tgHa : 1;

    $nbi = $nettoBulanIni[$tt] ?? 0;
    $nbiLY = $nettoBulanIniThnLalu[$tt] ?? 0;
    $nsd = $nettoSDBulanIni[$tt] ?? 0;
    $nsdLY = $nettoSDBulanIniThnLalu[$tt] ?? 0;
    $tbi = $targetbulanini[$tt] ?? 0;
    $tsd = $targetsdbulanini[$tt] ?? 0;
    $thn = $targettahun[$tt] ?? 0;

    $apo      = $aktualpersatuorang[$tt] ?? 0;
    $apoSD    = $aktualpersatuorangsd[$tt] ?? 0;
    $apoLY    = $aktualpersatuorangly[$tt] ?? 0;
    $apoSDLY  = $aktualpersatuorangsdly[$tt] ?? 0;

    if ($jmlKaryawan > 0) {
        $tbiOrg = $tbi / $jmlKaryawan;
        $tsdOrg = $tsd / $jmlKaryawan;
        $thnOrg = $thn / $jmlKaryawan;
    } else {
        $tbiOrg = 0;
        $tsdOrg = 0;
        $thnOrg = 0;
    }




    echo "
    <tr>
        <td align='center'>Rekap</td>
        <td align='center'>{$tt}</td>

        <td align='right'>" . number_format($nbi, 2) . "</td>
        <td align='right'>" . number_format($nsd, 2) . "</td>

        <td align='right'>" . number_format($nbiLY, 2) . "</td>
        <td align='right'>" . number_format($nsdLY, 2) . "</td>

        <td align='right'>" . number_format($tbi, 2) . "</td>
        <td align='right'>" . number_format($tsd, 2) . "</td>
        <td align='right'>" . number_format($thn, 2) . "</td>

         <!-- PRODUKSI TBS / HA -->
        <td align='right'>" . number_format($nbi / $luasTT, 2) . "</td>
        <td align='right'>" . number_format($nsd / $luasTT, 2) . "</td>
        <td align='right'>" . number_format($nbiLY / $tgHa, 2) . "</td>
        <td align='right'>" . number_format($nsdLY / $tgHa, 2) . "</td>
        <td align='right'>" . number_format($tbi / $luasTT, 2) . "</td>
        <td align='right'>" . number_format($tsd / $luasTT, 2) . "</td>
        <td align='right'>" . number_format($thn / $luasTT, 2) . "</td>

        <!-- Produksi TBS/1 orang pemanen -->
        <td align='right'>" . number_format($aktualpersatuorang[$tt] ?? 0, 2) . "</td>
        <td align='right'>" . number_format($aktualpersatuorangsd[$tt] ?? 0, 2) . "</td>

        <td align='right'>" . number_format($aktualpersatuorangly[$tt] ?? 0, 2) . "</td>
        <td align='right'>" . number_format($aktualpersatuorangsdly[$tt] ?? 0, 2) . "</td>

        <td align='right'>" . number_format($tbiOrg, 2) . "</td>
        <td align='right'>" . number_format($tsdOrg, 2) . "</td>
        <td align='right'>" . number_format($thnOrg, 2) . "</td>

    </tr>

    ";

    $gtHa['nbi'] += ($nbi / $luasTT);
    $gtHa['nsd'] += ($nsd / $luasTT);
    $gtHa['nbi_ly'] += ($nbiLY / $tgHa);
    $gtHa['nsd_ly'] += ($nsdLY / $tgHa);
    $gtHa['tbi'] += ($tbi / $luasTT);
    $gtHa['tsd'] += ($tsd / $luasTT);
    $gtHa['thn'] += ($thn / $luasTT);

    $gt['nbi'] += $nbi;
    $gt['nbi_ly'] += $nbiLY;
    $gt['nsd'] += $nsd;
    $gt['nsd_ly'] += $nsdLY;
    $gt['tbi'] += $tbi;
    $gt['tsd'] += $tsd;
    $gt['thn'] += $thn;

    // akumulasi produksi
    $gtHaProd['nbi']    += $nbi;
    $gtHaProd['nsd']    += $nsd;
    $gtHaProd['nbi_ly'] += $nbiLY;
    $gtHaProd['nsd_ly'] += $nsdLY;
    $gtHaProd['tbi']    += $tbi;
    $gtHaProd['tsd']    += $tsd;
    $gtHaProd['thn']    += $thn;

    // akumulasi luas
    $gtHaLuas += $luasTT;
    $gtOrg['tbi'] += $tbi;
    $gtOrg['tsd'] += $tsd;
    $gtOrg['thn'] += $thn;

}

echo "
<tr style='background:#00cfff;font-weight:bold;'>
    <td colspan='2'>GRAND TOTAL</td>
    <td align='right'>" . number_format($gt['nbi'], 2) . "</td>
    <td align='right'>" . number_format($gt['nsd'], 2) . "</td>
    <td align='right'>" . number_format($gt['nbi_ly'], 2) . "</td>
    <td align='right'>" . number_format($gt['nsd_ly'], 2) . "</td>
    <td align='right'>" . number_format($gt['tbi'], 2) . "</td>
    <td align='right'>" . number_format($gt['tsd'], 2) . "</td>
    <td align='right'>" . number_format($gt['thn'], 2) . "</td>

    <!-- PRODUKSI TBS / HA -->

    <td align='right'>" . number_format($gtHa['nbi'], 2) . "</td>
    <td align='right'>" . number_format($gtHa['nsd'], 2) . "</td>
    <td align='right'>" . number_format($gtHa['nbi_ly'], 2) . "</td>
    <td align='right'>" . number_format($gtHa['nsd_ly'], 2) . "</td>
    <td align='right'>" . number_format($gtHa['tbi'], 2) . "</td>
    <td align='right'>" . number_format($gtHa['tsd'], 2) . "</td>
    <td align='right'>" . number_format($gtHa['thn'], 2) . "</td>

     <!-- PRODUKSI TBS / 1 org pemanen -->
    <td align='right'>" . number_format($gtHaProd['nbi'] / $gtHaLuas, 2) . "</td>
    <td align='right'>" . number_format($gtHaProd['nsd'] / $gtHaLuas, 2) . "</td>
    <td align='right'>" . number_format($gtHaProd['nbi_ly'] / $gtHaLuas, 2) . "</td>
    <td align='right'>" . number_format($gtHaProd['nsd_ly'] / $gtHaLuas, 2) . "</td>
    <td align='right'>" . number_format(($jmlKaryawan>0?$gtOrg['tbi']/$jmlKaryawan:0), 2) . "</td>
    <td align='right'>" . number_format(($jmlKaryawan>0?$gtOrg['tsd']/$jmlKaryawan:0), 2) . "</td>
    <td align='right'>" . number_format(($jmlKaryawan>0?$gtOrg['thn']/$jmlKaryawan:0), 2) . "</td>
</tr>
</tbody>
</table> <br><br>";

//TABLE 3
$sqlBiayaPanen = "
SELECT
    sb.tahuntanam,

    SUM(CASE 
        WHEN jv.periode = '{$periode}' AND jv.noakun LIKE '61101%'
        THEN jv.jumlah ELSE 0 END
    ) AS mu_bln,

    SUM(CASE 
        WHEN jv.periode BETWEEN '{$awalPeriode}' AND '{$periode}' AND jv.noakun LIKE '61101%'
        THEN jv.jumlah ELSE 0 END
    ) AS mu_sd,

    SUM(CASE 
        WHEN jv.periode = '{$periode}' AND jv.noakun LIKE '61102%'
        THEN jv.jumlah ELSE 0 END
    ) AS tr_bln,

    SUM(CASE 
        WHEN jv.periode BETWEEN '{$awalPeriode}' AND '{$periode}' AND jv.noakun LIKE '61102%'
        THEN jv.jumlah ELSE 0 END
    ) AS tr_sd

FROM keu_jurnaldt_vw jv
JOIN setup_blok sb 
    ON jv.kodeblok = sb.kodeorg
WHERE jv.perusahaan = '{$pt}' {$inplas}
  AND (jv.noakun LIKE '61101%' OR jv.noakun LIKE '61102%')
  AND jv.periode BETWEEN '{$awalPeriode}' AND '{$periode}'
  AND sb.tahuntanam != '0'
GROUP BY sb.tahuntanam
ORDER BY sb.tahuntanam
";
// exit('warning:'.$sqlBiayaPanen);

$matUpah = [];
$transport = [];
$tmpBiaya = fetchData($sqlBiayaPanen);
foreach ($tmpBiaya as $r) {
    $matUpah[$r['tahuntanam']] = [
        'bln' => $r['mu_bln'],
        'sd' => $r['mu_sd']
    ];
    $transport[$r['tahuntanam']] = [
        'bln' => $r['tr_bln'],
        'sd' => $r['tr_sd']
    ];
}


$periodeLY = date('Y-m', strtotime($periode . '-01 -1 year'));
$sdLY      = date('Y-m', strtotime($sd . '-01 -1 year'));


$sqlBrondolJJG = "
SELECT
    sb.tahuntanam,

    SUM(CASE WHEN DATE_FORMAT(spb.tanggalpanen,'%Y-%m') = '{$periode}'
        THEN spb.brondolan ELSE 0 END) AS br_bln,

    SUM(CASE WHEN DATE_FORMAT(spb.tanggalpanen,'%Y-%m') BETWEEN '{$awalPeriode}' AND '{$periode}'
        THEN spb.brondolan ELSE 0 END) AS br_sd,

    SUM(CASE WHEN DATE_FORMAT(spb.tanggalpanen,'%Y-%m') = '{$periodeLY}'
        THEN spb.brondolan ELSE 0 END) AS br_bln_ly,

    SUM(CASE WHEN DATE_FORMAT(spb.tanggalpanen,'%Y-%m') BETWEEN '{$awalPeriodeLY}' AND '{$periodeLY}'
        THEN spb.brondolan ELSE 0 END) AS br_sd_ly,

    SUM(CASE WHEN DATE_FORMAT(spb.tanggalpanen,'%Y-%m') = '{$periode}'
        THEN spb.jjg ELSE 0 END) AS jjg_bln,

    SUM(CASE WHEN DATE_FORMAT(spb.tanggalpanen,'%Y-%m') BETWEEN '{$awalPeriode}' AND '{$periode}'
        THEN spb.jjg ELSE 0 END) AS jjg_sd,

    SUM(CASE WHEN DATE_FORMAT(spb.tanggalpanen,'%Y-%m') = '{$periodeLY}'
        THEN spb.jjg ELSE 0 END) AS jjg_bln_ly,

    SUM(CASE WHEN DATE_FORMAT(spb.tanggalpanen,'%Y-%m') BETWEEN '{$awalPeriodeLY}' AND '{$periodeLY}'
        THEN spb.jjg ELSE 0 END) AS jjg_sd_ly

FROM kebun_spb_vw spb
JOIN setup_blok sb ON spb.blok = sb.indukblok
WHERE spb.blok LIKE '{$pt}%' {$inplas}
GROUP BY sb.tahuntanam
";
// exit('warning:'.$sqlBrondolJJG);

$brondol = [];
$jjg = [];
foreach (fetchData($sqlBrondolJJG) as $r) {
    $brondol[$r['tahuntanam']] = $r;
    $jjg[$r['tahuntanam']] = $r;
}



echo "
<table border='1' cellspacing='0' cellpadding='5' width='100%'>
<thead>

<!-- ROW 1 : JUDUL BESAR -->
<tr style='background:#ccffcc;font-weight:bold;text-align:center;'>
    <th rowspan='4'>Kode Tanam</th>
    <th rowspan='4'>Tahun Tanam</th>

    <th colspan='4'>Total Biaya (Rp)</th>
    <th colspan='2'>Biaya Panen</th>
    <th colspan='2'>Biaya Panen (Rp/Kg)</th>

    <th colspan='4'>Total Brondol</th>
    <th colspan='4'>Total JJG</th>

    <th colspan='2'>Rata-rata Kg/Buah</th>
</tr>

<!-- ROW 2 : SUB KELOMPOK -->
<tr style='background:#ffff99;font-weight:bold;text-align:center;'>
    <th colspan='2'>Material dan Upah</th>
    <th colspan='2'>Transport</th>

    <th rowspan='2'>Bulan Ini</th>
    <th rowspan='2'>S/D Bulan Ini</th>

    <th rowspan='2'>Bulan Ini</th>
    <th rowspan='2'>S/D Bulan Ini</th>

    <th colspan='2'>Aktual</th>
    <th colspan='2'>Bulan yang Sama Tahun Lalu</th>

    <th colspan='2'>Aktual</th>
    <th colspan='2'>Bulan yang Sama Tahun Lalu</th>

    <th rowspan='2'>Bulan Ini</th>
    <th rowspan='2'>S/D Bulan Ini</th>
</tr>

<!-- ROW 3 : DETAIL BULAN -->
<tr style='background:#ffff99;font-weight:bold;text-align:center;'>
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>

    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>

    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
    <th>Bulan Ini</th>
    <th>S/D Bulan Ini</th>
</tr>

</thead>
<tbody>
";


//Table Data
$gt = [
    'mu' => 0,
    'mu_sd' => 0,
    'tr' => 0,
    'tr_sd' => 0,
    'panen' => 0,
    'panen_sd' => 0,

    // BRONDOL
    'br_bln'=>0,'br_sd'=>0,'br_bln_ly'=>0,'br_sd_ly'=>0,

    // JJG
    'jjg_bln'=>0,'jjg_sd'=>0,'jjg_bln_ly'=>0,'jjg_sd_ly'=>0
];

foreach ($dataTM as $r) {
    $tt = $r['tahuntanam'];

    $mu = $matUpah[$tt]['bln'] ?? 0;
    $muSD = $matUpah[$tt]['sd'] ?? 0;

    $tr = $transport[$tt]['bln'] ?? 0;
    $trSD = $transport[$tt]['sd'] ?? 0;

    $panen = $mu + $tr;
    $panenSD = $muSD + $trSD;

    $br = $brondol[$tt] ?? [];
    $jg = $jjg[$tt] ?? [];

    $nbi = $nettoBulanIni[$tt] ?? 0;
    $nsd = $nettoSDBulanIni[$tt] ?? 0;

    // Biaya Panen (Rp/Kg)
    $rpkg = $nbi > 0 ? ($panen / $nbi) : 0;
    $rpkgSD = $nsd > 0 ? ($panenSD / $nsd) : 0;


    // Rata-rata Kg/Buah
    $kg_buah_bln = ($jg['jjg_bln'] ?? 0) > 0 ? ($nbi / $jg['jjg_bln']) : 0;
    $kg_buah_sd = ($jg['jjg_sd'] ?? 0) > 0 ? ($nsd / $jg['jjg_sd']) : 0;

    echo "
<tr>
    <td align='center'>Rekap</td>
    <td align='center'>{$tt}</td>

    <!-- BIAYA -->
    <td align='right'>" . number_format($mu, 0) . "</td>
    <td align='right'>" . number_format($muSD, 0) . "</td>
    <td align='right'>" . number_format($tr, 0) . "</td>
    <td align='right'>" . number_format($trSD, 0) . "</td>

    <td align='right'>" . number_format($panen, 0) . "</td>
    <td align='right'>" . number_format($panenSD, 0) . "</td>

    <td align='right'>" . number_format($rpkg, 2) . "</td>
    <td align='right'>" . number_format($rpkgSD, 2) . "</td>

    <!-- TOTAL BRONDOL -->
    <td align='right'>" . number_format($br['br_bln'] ?? 0, 0) . "</td>
    <td align='right'>" . number_format($br['br_sd'] ?? 0, 0) . "</td>
    <td align='right'>" . number_format($br['br_bln_ly'] ?? 0, 0) . "</td>
    <td align='right'>" . number_format($br['br_sd_ly'] ?? 0, 0) . "</td>

    <!-- TOTAL JJG -->
    <td align='right'>" . number_format($jg['jjg_bln'] ?? 0, 0) . "</td>
    <td align='right'>" . number_format($jg['jjg_sd'] ?? 0, 0) . "</td>
    <td align='right'>" . number_format($jg['jjg_bln_ly'] ?? 0, 0) . "</td>
    <td align='right'>" . number_format($jg['jjg_sd_ly'] ?? 0, 0) . "</td>

    <!-- RATA-RATA KG/BUAH -->
    <td align='right'>" . number_format($kg_buah_bln, 2) . "</td>
    <td align='right'>" . number_format($kg_buah_sd, 2) . "</td>
</tr>";


    $gt['mu'] += $mu;
    $gt['mu_sd'] += $muSD;
    $gt['tr'] += $tr;
    $gt['tr_sd'] += $trSD;
    $gt['panen'] += $panen;
    $gt['panen_sd'] += $panenSD;


    // ====== GRAND TOTAL BRONDOL ======
    $gt['br_bln']     += $br['br_bln']     ?? 0;
    $gt['br_sd']      += $br['br_sd']      ?? 0;
    $gt['br_bln_ly']  += $br['br_bln_ly']  ?? 0;
    $gt['br_sd_ly']   += $br['br_sd_ly']   ?? 0;

    // ====== GRAND TOTAL JJG ======
    $gt['jjg_bln']    += $jg['jjg_bln']    ?? 0;
    $gt['jjg_sd']     += $jg['jjg_sd']     ?? 0;
    $gt['jjg_bln_ly'] += $jg['jjg_bln_ly'] ?? 0;
    $gt['jjg_sd_ly']  += $jg['jjg_sd_ly']  ?? 0;


}

$gtKgBuahBln = ($gt['jjg_bln'] > 0) ? ($gt['panen'] / $gt['jjg_bln']) : 0;
$gtKgBuahSD  = ($gt['jjg_sd']  > 0) ? ($gt['panen_sd'] / $gt['jjg_sd']) : 0;

$gtRpkg   = ($gt['jjg_bln'] > 0) ? ($gt['panen'] / $gt['jjg_bln']) : 0;
$gtRpkgSD = ($gt['jjg_sd']  > 0) ? ($gt['panen_sd'] / $gt['jjg_sd']) : 0;


//GrandTotal
echo "
<tr style='font-weight:bold;background:#00cfff;'>
    <td colspan='2'>GRAND TOTAL</td>

    <!-- BIAYA -->
    <td align='right'>" . number_format($gt['mu'], 0) . "</td>
    <td align='right'>" . number_format($gt['mu_sd'], 0) . "</td>
    <td align='right'>" . number_format($gt['tr'], 0) . "</td>
    <td align='right'>" . number_format($gt['tr_sd'], 0) . "</td>

    <td align='right'>" . number_format($gt['panen'], 0) . "</td>
    <td align='right'>" . number_format($gt['panen_sd'], 0) . "</td>

    <td align='right'>" . number_format($gtRpkg, 2) . "</td>
    <td align='right'>" . number_format($gtRpkgSD, 2) . "</td>

    <!-- TOTAL BRONDOL -->
    <td align='right'>" . number_format($gt['br_bln'], 0) . "</td>
    <td align='right'>" . number_format($gt['br_sd'], 0) . "</td>
    <td align='right'>" . number_format($gt['br_bln_ly'], 0) . "</td>
    <td align='right'>" . number_format($gt['br_sd_ly'], 0) . "</td>

    <!-- TOTAL JJG -->
    <td align='right'>" . number_format($gt['jjg_bln'], 0) . "</td>
    <td align='right'>" . number_format($gt['jjg_sd'], 0) . "</td>
    <td align='right'>" . number_format($gt['jjg_bln_ly'], 0) . "</td>
    <td align='right'>" . number_format($gt['jjg_sd_ly'], 0) . "</td>

    <!-- RATA-RATA KG/BUAH -->
    <td align='right'>" . number_format($gtKgBuahBln, 2) . "</td>
    <td align='right'>" . number_format($gtKgBuahSD, 2) . "</td>
</tr>
</tbody>
</table>";