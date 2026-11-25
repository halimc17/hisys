<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zDetailPekerjaanTraksi.php');

# Traksi > Proses > Bentuk Ulang Detail Pekerjaan
# Bentuk ulang vhc_rundt_detail transaksi Pekerjaan Kendaraan yang sudah diposting, memakai logika posting yang sama
# (lib/zDetailPekerjaanTraksi.php). Hanya user Sendi.bagaskara.
#   method=cek    : tampilkan transaksi yang detailnya akan berubah (tidak mengubah data)
#   method=proses : bentuk ulang detail transaksi yang berubah (per transaksi, dalam transaksi database).
#                   Tidak ada cadangan di aplikasi: backup database dulu sebelum menjalankan Proses.
# Tabel lain (vhc_runht, vhc_rundt, vhc_runhk, jurnal) tidak diubah; status dan waktu posting tetap.

$userBentukUlang = 'sendi.bagaskara';
$userLogin = isset($_SESSION['standard']['username']) ? trim($_SESSION['standard']['username']) : '';
if (strtolower($userLogin) !== $userBentukUlang) {
    exit('Warning: Menu ini hanya dapat dijalankan oleh user Sendi.bagaskara.');
}

$method  = checkPostGet('method', '');
$unit    = trim(checkPostGet('unit', ''));
$periode = trim(checkPostGet('periode', ''));
if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $unit)) {
    exit('Warning: Unit wajib dipilih.');
}
if (!preg_match('/^\d{4}-\d{2}$/', $periode)) {
    exit('Warning: Periode wajib dipilih.');
}

$per = fetchData("select tanggalmulai,tanggalsampai,tutupbuku from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and periode='" . $periode . "'");
if (count($per) == 0) {
    exit('Warning: Periode akuntansi ' . $periode . ' untuk unit ' . $unit . ' tidak ada.');
}
$tgMulai = $per[0]['tanggalmulai'];
$tgSampai = $per[0]['tanggalsampai'];
$tutupBuku = ($per[0]['tutupbuku'] == 1);

#jam per kegiatan + blok induk tiap transaksi (blok kecil dikembalikan ke induknya)
function jamPerKegiatanBU($rows, $induk)
{
    $o = array();
    foreach ($rows as $r) {
        if ($r['jumlah'] <= 0) {
            continue;
        }
        $ab = isset($induk[$r['alokasibiaya']]) ? $induk[$r['alokasibiaya']] : $r['alokasibiaya'];
        $k = trim($r['jenispekerjaan']) . '|' . $ab;
        $o[$r['notransaksi']][$k] = (isset($o[$r['notransaksi']][$k]) ? $o[$r['notransaksi']][$k] : 0) + $r['jumlah'];
    }
    return $o;
}
function samaJamBU($a, $b)
{
    foreach (array_unique(array_merge(array_keys($a), array_keys($b))) as $k) {
        if (abs((isset($a[$k]) ? $a[$k] : 0) - (isset($b[$k]) ? $b[$k] : 0)) > 0.001) {
            return false;
        }
    }
    return true;
}
function ambilBU($arr, $n)
{
    return isset($arr[$n]) ? $arr[$n] : array();
}

#transaksi yang sudah diposting dalam periode
$trx = array();
foreach (fetchData("select notransaksi,kodevhc,tanggal from " . $dbname . ".vhc_runht where kodeorg='" . $unit . "' and posting=1
        and tanggal>='" . $tgMulai . "' and tanggal<='" . $tgSampai . "' order by tanggal,notransaksi") as $r) {
    $trx[$r['notransaksi']] = $r;
}
if (count($trx) == 0) {
    exit('Warning: Tidak ada transaksi Pekerjaan Kendaraan yang sudah diposting untuk unit ' . $unit . ' periode ' . $periode . '.');
}
$inTrx = "'" . implode("','", array_map('addslashes', array_keys($trx))) . "'";
$induk = array();
foreach (fetchData("select kodeorg,indukblok from " . $dbname . ".setup_blok where indukblok!='' and indukblok in
        (select distinct alokasibiaya from " . $dbname . ".vhc_rundt where notransaksi in (" . $inTrx . "))") as $r) {
    $induk[$r['kodeorg']] = $r['indukblok'];
}
$IN = jamPerKegiatanBU(fetchData("select notransaksi,jenispekerjaan,alokasibiaya,jumlah from " . $dbname . ".vhc_rundt where notransaksi in (" . $inTrx . ")"), $induk);
$DT = jamPerKegiatanBU(fetchData("select notransaksi,jenispekerjaan,alokasibiaya,jumlah from " . $dbname . ".vhc_rundt_detail where notransaksi in (" . $inTrx . ")"), $induk);

#detail baru (dihitung, belum ditulis) dan daftar transaksi yang berubah
$BARU = array();
$berubah = array();
foreach ($trx as $n => $t) {
    $rows = array();
    foreach (susunDetailPekerjaanTraksi($n) as $d) {
        $rows[] = array('notransaksi' => $n, 'jenispekerjaan' => $d['jenispekerjaan'], 'alokasibiaya' => $d['alokasibiaya'], 'jumlah' => $d['jumlah']);
    }
    $x = jamPerKegiatanBU($rows, $induk);
    $BARU[$n] = ambilBU($x, $n);
    if (!samaJamBU(ambilBU($DT, $n), $BARU[$n])) {
        $berubah[] = $n;
    }
}
$cocokSekarang = 0;
$cocokBaru = 0;
foreach ($trx as $n => $t) {
    if (samaJamBU(ambilBU($IN, $n), ambilBU($DT, $n))) {
        $cocokSekarang++;
    }
    if (samaJamBU(ambilBU($IN, $n), $BARU[$n])) {
        $cocokBaru++;
    }
}

function tabelBU($daftar, $trx, $IN, $DT, $BARU)
{
    $h = "<table class=sortable cellpadding=4 cellspacing=1 border=0>
        <thead><tr class=rowheader>
            <td align=center>No</td><td align=center>No Transaksi</td><td align=center>Tanggal</td><td align=center>Kendaraan</td>
            <td align=center>Jam Input</td><td align=center>Jam Detail Sekarang</td><td align=center>Jam Detail Baru</td>
        </tr></thead><tbody>";
    $no = 0;
    foreach ($daftar as $n) {
        $no++;
        $h .= "<tr class=rowcontent><td align=center>" . $no . "</td><td>" . htmlspecialchars($n) . "</td><td>" . tanggalnormal($trx[$n]['tanggal']) . "</td>
            <td>" . htmlspecialchars($trx[$n]['kodevhc']) . "</td>
            <td align=right>" . number_format(array_sum(ambilBU($IN, $n)), 2) . "</td>
            <td align=right>" . number_format(array_sum(ambilBU($DT, $n)), 2) . "</td>
            <td align=right>" . number_format(array_sum(ambilBU($BARU, $n)), 2) . "</td></tr>";
    }
    if ($no == 0) {
        $h .= "<tr class=rowcontent><td colspan=7 align=center>Semua detail sudah sesuai dengan jam input.</td></tr>";
    }
    return $h . "</tbody></table>";
}
$ringkas = "<table cellpadding=3>
    <tr><td>Unit / periode</td><td>: " . htmlspecialchars($unit) . " / " . htmlspecialchars($periode) . " (" . tanggalnormal($tgMulai) . " s/d " . tanggalnormal($tgSampai) . ")" . ($tutupBuku ? " <b>- sudah tutup buku</b>" : "") . "</td></tr>
    <tr><td>Transaksi diposting</td><td>: " . count($trx) . "</td></tr>
    <tr><td>Detail sekarang sama dengan jam input</td><td>: " . $cocokSekarang . " transaksi</td></tr>
    <tr><td>Detail baru sama dengan jam input</td><td>: " . $cocokBaru . " transaksi</td></tr>
    <tr><td>Transaksi yang detailnya berubah</td><td>: <b>" . count($berubah) . "</b></td></tr>
    </table>";

switch ($method) {
    case 'cek':
        echo $ringkas . "<br>" . tabelBU($berubah, $trx, $IN, $DT, $BARU);
        break;

    case 'proses':
        if ($tutupBuku) {
            exit('Warning: Periode ' . $periode . ' unit ' . $unit . ' sudah tutup buku. Buka periodenya dulu.');
        }
        if (count($berubah) == 0) {
            echo $ringkas . "<br>Semua detail sudah sesuai dengan jam input. Tidak ada yang diproses.";
            break;
        }
        $berhasil = 0;
        $tidakBerhasil = array();
        foreach ($berubah as $n) {
            try {
                $owlPDO->beginTransaction();
                simpanDetailPekerjaanTraksi($n);
                $owlPDO->commit();
                $berhasil++;
            } catch (Exception $e) {
                if ($owlPDO->inTransaction()) {
                    $owlPDO->rollBack();
                }
                $tidakBerhasil[] = $n;
            }
        }
        if (count($tidakBerhasil) > 0) {
            exit('Warning: ' . count($tidakBerhasil) . ' transaksi tidak berhasil dibentuk ulang dan tidak diubah: ' . implode(', ', $tidakBerhasil)
                . '. ' . $berhasil . ' transaksi lain sudah dibentuk ulang.');
        }
        $DT2 = jamPerKegiatanBU(fetchData("select notransaksi,jenispekerjaan,alokasibiaya,jumlah from " . $dbname . ".vhc_rundt_detail where notransaksi in (" . $inTrx . ")"), $induk);
        $cocokAkhir = 0;
        foreach ($trx as $n => $t) {
            if (samaJamBU(ambilBU($IN, $n), ambilBU($DT2, $n))) {
                $cocokAkhir++;
            }
        }
        echo "<b>Selesai.</b> " . $berhasil . " transaksi dibentuk ulang. Detail sama dengan jam input: " . $cocokAkhir . " dari " . count($trx) . " transaksi.<br>
            Langkah berikutnya: Proses Akhir Bulan no 3 lalu no 8 untuk periode " . htmlspecialchars($periode) . ".<br><br>"
            . tabelBU($berubah, $trx, $IN, $DT, $BARU);
        break;

    default:
        exit('Warning: Aksi tidak dikenal.');
}
