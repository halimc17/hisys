<?php
// Koreksi proporsi pemakaian barang ke blok (log_transaksidt_detail) beserta jurnal INVK-nya.
// 1. Qty: sebelum perbaikan log_slave_saveBast.php blok terakhir tidak menerima sisa pembulatan,
//    sehingga total qty detail blok lebih besar/kecil dari qty transaksi (contoh 1.00001 untuk qty 1).
// 2. Harga: hargarata detail blok disamakan dengan hargarata transaksi (log_transaksidt) per notransaksi dan barang.
//
// Pakai:  perbaikan_proporsi_blok.php?unit=PPPE&periode=2026-10&noakun=1150123            (cek saja, tidak menyimpan)
//         perbaikan_proporsi_blok.php?unit=PPPE&periode=2026-10&noakun=1150123&mode=proses&konfirmasi=YA
// Opsional: &notransaksi=20261000091-GI-PPPEGC untuk satu transaksi saja.
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$allowed_users = ['0000000001','0000000002','0000000003','0000000004','0000000005','0000000006','0000000007','0000000008','0000010163'];
if (!in_array($_SESSION['standard']['userid'], $allowed_users)) {
    exit("warning: No access");
}

$unit        = isset($_GET['unit']) ? $_GET['unit'] : '';
$periode     = isset($_GET['periode']) ? $_GET['periode'] : '';
$noakun      = isset($_GET['noakun']) ? $_GET['noakun'] : '';
$notransaksi = isset($_GET['notransaksi']) ? $_GET['notransaksi'] : '';
$proses      = (isset($_GET['mode']) && $_GET['mode'] == 'proses' && isset($_GET['konfirmasi']) && $_GET['konfirmasi'] == 'YA');

function h($teks)
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}
if (!preg_match('/^[A-Z0-9]{4}$/', $unit) || !preg_match('/^[0-9]{4}-[0-9]{2}$/', $periode) || !preg_match('/^[0-9]*$/', $noakun)) {
    exit("warning: Isi unit (4 huruf), periode (YYYY-MM) dan noakun (boleh kosong = semua akun persediaan), contoh ?unit=PPPE&periode=2026-10&noakun=1150123");
}
if ($notransaksi != '' && !preg_match('/^[A-Za-z0-9\-\/]+$/', $notransaksi)) {
    exit("warning: Format notransaksi tidak valid");
}

$owlPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// periode yang sudah ditutup tidak boleh diubah jurnalnya
$stmt = $owlPDO->prepare("select count(*) from " . $dbname . ".setup_periodeakuntansi where kodeorg like ? and periode=? and tutupbuku=1");
$stmt->execute([$unit . '%', $periode]);
if ($stmt->fetchColumn() > 0) {
    exit("warning: Periode " . $periode . " unit " . $unit . " sudah tutup buku, tidak boleh diubah.");
}

$namaBarang = array();
$stNamaBarang = $owlPDO->prepare("select namabarang from " . $dbname . ".log_5masterbarang where kodebarang=?");
function namaBarang($kode)
{
    global $stNamaBarang, $namaBarang;
    if (!isset($namaBarang[$kode])) {
        $stNamaBarang->execute([$kode]);
        $nm = $stNamaBarang->fetchColumn();
        $namaBarang[$kode] = ($nm === false ? '' : $nm);
    }
    return $namaBarang[$kode];
}

echo "<div style='font-size:12px;margin:5px 0;padding:8px 10px;background:#fffbe6;border:1px solid #e0c97f;'>
    <b>Perbaiki Proporsi Blok</b><br>
    Membetulkan sisa pembulatan pembagian pemakaian barang ke blok: total qty blok disamakan dengan qty transaksi dan harga blok disamakan dengan harga transaksi, lalu jurnal (INVK) blok yang berubah ikut disesuaikan.
    Hanya selisih kecil akibat pembulatan yang dikoreksi; selisih besar atau jurnal yang polanya berbeda dilewati untuk dicek manual.<br>
    <b>Unit</b> : " . h($unit) . " &nbsp;|&nbsp; <b>Periode</b> : " . h($periode) . " &nbsp;|&nbsp; <b>Akun</b> : " . h($noakun == '' ? 'semua akun persediaan (115%)' : $noakun) . " &nbsp;|&nbsp; <b>Mode</b> : "
    . ($proses ? "<span style='background:#d9534f;color:white;padding:0 6px;'>PROSES (menyimpan)</span>" : "<span style='background:#337ab7;color:white;padding:0 6px;'>CEK (belum menyimpan)</span>") . "
</div>";

// transaksi pemakaian yang qty atau harga detail bloknya mungkin tidak sama dengan transaksi
$sql = "select t.notransaksi,t.kodebarang,t.kodeblok,t.kodekegiatan,t.kodemesin,t.jumlah,t.hargarata
        from " . $dbname . ".log_transaksidt t
        join " . $dbname . ".log_transaksiht h on h.notransaksi=t.notransaksi
        where h.kodegudang like ? and h.tanggal like ?
        and left(t.kodebarang,3) in (select kode from " . $dbname . ".log_5klbarang where noakun like ?)";
$paramSql = [$unit . '%', $periode . '%', ($noakun == '' ? '115%' : $noakun)];
if ($notransaksi != '') {
    $sql .= " and t.notransaksi=?";
    $paramSql[] = $notransaksi;
}
$sql .= " order by t.notransaksi,t.kodebarang";
$stmt = $owlPDO->prepare($sql);
$stmt->execute($paramSql);
$listTrx = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stDetail = $owlPDO->prepare("select * from " . $dbname . ".log_transaksidt_detail
    where notransaksi=? and kodebarang=? and kodekegiatan <=> ? and kodemesin=? and kodeblok like ?");
$stJurnal = $owlPDO->prepare("select nojurnal,nourut,jumlah,keterangan from " . $dbname . ".keu_jurnaldt
    where noreferensi=? and kodebarang=? and kodeblok=? and kodekegiatan <=> ? and nojurnal like '%INVK%' order by nourut");
$stHeader = $owlPDO->prepare("select totaldebet,totalkredit from " . $dbname . ".keu_jurnalht where nojurnal=?");
$stPic = $owlPDO->prepare("select jumlah,presentase from " . $dbname . ".log_pemakaianpresentase where notransaksi=? and kodebarang=?");

$updDetail = $owlPDO->prepare("update " . $dbname . ".log_transaksidt_detail set jumlah=?,hargarata=?
    where notransaksi=? and nopp=? and kodebarang=? and kodeblok=? and kodemesin=? and kodesegment=?");
$updJurnal = $owlPDO->prepare("update " . $dbname . ".keu_jurnaldt set jumlah=?,keterangan=? where nojurnal=? and nourut=?");
$updHeader = $owlPDO->prepare("update " . $dbname . ".keu_jurnalht set totaldebet=?,totalkredit=? where nojurnal=?");

$batasNilai = 50; // batas perubahan nilai jurnal per baris blok (rupiah); di atas ini dianggap selisih besar
$plan = array();
$skip = array();
$groupSkip = array();
foreach ($listTrx as $t) {
    $stDetail->execute([$t['notransaksi'], $t['kodebarang'], $t['kodekegiatan'], $t['kodemesin'], $t['kodeblok'] . '%']);
    $details = $stDetail->fetchAll(PDO::FETCH_ASSOC);
    if (count($details) < 2) {
        continue;
    }
    $totalDetail = 0;
    $dMax = $details[0];
    foreach ($details as $d) {
        $totalDetail += $d['jumlah'];
        if ($d['jumlah'] > $dMax['jumlah']) {
            $dMax = $d;
        }
    }
    $selisih = round($totalDetail - $t['jumlah'], 5);
    $kode = $t['notransaksi'] . " " . $t['kodebarang'];

    // pemakaian dengan PIC karyawan: jurnal blok hanya bagian perusahaan (faktor k dari log_pemakaianpresentase, sama seperti saat posting)
    $stPic->execute([$t['notransaksi'], $t['kodebarang']]);
    $pics = $stPic->fetchAll(PDO::FETCH_ASSOC);
    $faktor = 1;
    if (count($pics) > 0) {
        if ($t['jumlah'] <= 0) {
            $skip[] = array($kode, "", "qty transaksi tidak valid untuk pemakaian PIC (cek manual)");
            $groupSkip[$kode] = true;
            continue;
        }
        $sumPic = 0;
        $sumBagianPerusahaan = 0;
        foreach ($pics as $pc) {
            $sumPic += $pc['jumlah'];
            $sumBagianPerusahaan += $pc['jumlah'] * (1 - $pc['presentase'] / 100);
        }
        $faktor = ($sumBagianPerusahaan + ($t['jumlah'] - $sumPic)) / $t['jumlah'];
    }

    // perubahan tiap baris blok: qty (hanya blok terbesar yang menampung sisa) dan harga (disamakan dengan transaksi)
    $groupPlan = array();
    $groupOk = true;
    foreach ($details as $d) {
        $qtyBaru = $d['jumlah'];
        if (abs($selisih) >= 0.000005 && $d === $dMax) {
            $qtyBaru = round($d['jumlah'] - $selisih, 5);
        }
        $hrBaru = $d['hargarata'];
        if ($t['hargarata'] > 0 && abs($d['hargarata'] - $t['hargarata']) > 0.000001) {
            $hrBaru = $t['hargarata'];
        }
        if ($qtyBaru == $d['jumlah'] && $hrBaru == $d['hargarata']) {
            continue;
        }
        if ($qtyBaru <= 0 || $hrBaru <= 0) {
            $skip[] = array($kode, $d['kodeblok'], "qty/harga baru tidak valid");
            $groupOk = false;
            break;
        }

        // pemakaian PIC: jurnal PIC karyawan dihitung dari harga, jadi harga tidak boleh diubah otomatis di sini
        if ($faktor != 1 && $hrBaru != $d['hargarata']) {
            $skip[] = array($kode, $d['kodeblok'], "harga berbeda pada pemakaian PIC, jurnal PIC karyawan perlu dicek manual");
            $groupOk = false;
            break;
        }

        // jurnal baris detail blok yang akan dikoreksi harus tepat sepasang (debet/kredit) bernilai qty x harga rata (x faktor PIC)
        $stJurnal->execute([$t['notransaksi'], $t['kodebarang'], $d['kodeblok'], $t['kodekegiatan']]);
        $lines = $stJurnal->fetchAll(PDO::FETCH_ASSOC);
        $nilaiLama = $d['jumlah'] * $d['hargarata'] * $faktor;
        if (count($lines) != 2 || $lines[0]['nojurnal'] != $lines[1]['nojurnal']
            || abs($lines[0]['jumlah'] - $nilaiLama) > 0.01 || abs($lines[1]['jumlah'] + $nilaiLama) > 0.01) {
            $skip[] = array($kode, $d['kodeblok'], "jurnal tidak sesuai pola qty x harga rata (cek manual)");
            $groupOk = false;
            break;
        }
        // selisih besar bukan akibat pembulatan: jangan dikoreksi otomatis, harus dicek manual
        $nilaiBaru = $qtyBaru * $hrBaru * $faktor;
        if (abs($nilaiBaru - $nilaiLama) > $batasNilai) {
            $skip[] = array($kode, $d['kodeblok'], "perubahan nilai " . round($nilaiBaru - $nilaiLama, 2) . " melebihi batas Rp " . $batasNilai . " (selisih besar, cek manual)");
            $groupOk = false;
            break;
        }
        $nojurnal = $lines[0]['nojurnal'];
        $stHeader->execute([$nojurnal]);
        $head = $stHeader->fetch(PDO::FETCH_ASSOC);
        if (!$head || abs($head['totaldebet'] - $nilaiLama) > 0.01 || abs($head['totalkredit'] + $nilaiLama) > 0.01) {
            $skip[] = array($kode, $d['kodeblok'], "total header jurnal " . $nojurnal . " tidak sama dengan baris (cek manual)");
            $groupOk = false;
            break;
        }

        $groupPlan[] = array(
            'kode' => $kode, 'detail' => $d, 'qtylama' => $d['jumlah'], 'qtybaru' => $qtyBaru,
            'hrlama' => $d['hargarata'], 'hrbaru' => $hrBaru,
            'nojurnal' => $nojurnal, 'lines' => $lines, 'head' => $head,
            'nilailama' => $nilaiLama, 'nilaibaru' => $nilaiBaru,
            'ketlama' => (string)(float)$d['jumlah'], 'ketbaru' => (string)(float)$qtyBaru,
        );
    }
    if (!$groupOk) {
        $groupSkip[$kode] = true; // semua perubahan kelompok ini dibatalkan agar totalnya tidak setengah-setengah
        continue;
    }
    foreach ($groupPlan as $gp) {
        $plan[] = $gp;
    }
}

$totalPerubahan = 0;
$jmlQty = 0;
$jmlHarga = 0;
$kolom = 12;
$tbl = "<table class=sortable cellspacing=1 cellpadding=5 style='width:100%'>
    <thead>
        <tr class=rowcontent><td align='center' colspan='" . $kolom . "'><b>RENCANA KOREKSI PROPORSI BLOK</b></td></tr>
        <tr class=rowheader style='text-align:center'>
            <th>No.</th><th>Transaksi</th><th>Barang</th><th>Blok</th><th>Qty Lama</th><th>Qty Baru</th>
            <th>Harga Lama</th><th>Harga Baru</th><th>No. Jurnal</th><th>Nilai Jurnal Lama</th><th>Nilai Jurnal Baru</th><th>Selisih</th>
        </tr>
    </thead><tbody>";
$no = 0;
$sorot = "background:#fff59d;font-weight:bold;";
foreach ($plan as $p) {
    $no++;
    $ubahQty = ($p['qtylama'] != $p['qtybaru']);
    $ubahHr = ($p['hrlama'] != $p['hrbaru']);
    $jmlQty += $ubahQty ? 1 : 0;
    $jmlHarga += $ubahHr ? 1 : 0;
    $beda = $p['nilaibaru'] - $p['nilailama'];
    $totalPerubahan += $beda;
    $kb = explode(' ', $p['kode']);
    $tbl .= "<tr class=rowcontent>
        <td align='center'>" . $no . "</td>
        <td align='center'>" . h($kb[0]) . "</td>
        <td>" . h($kb[1]) . " - " . h(namaBarang($kb[1])) . "</td>
        <td align='center'>" . h($p['detail']['kodeblok']) . "</td>
        <td align='right'>" . number_format($p['qtylama'], 5) . "</td>
        <td align='right' style='" . ($ubahQty ? $sorot : "") . "'>" . number_format($p['qtybaru'], 5) . "</td>
        <td align='right'>" . number_format($p['hrlama'], 4) . "</td>
        <td align='right' style='" . ($ubahHr ? $sorot : "") . "'>" . number_format($p['hrbaru'], 4) . "</td>
        <td>" . h($p['nojurnal']) . "</td>
        <td align='right'>" . number_format($p['nilailama'], 2) . "</td>
        <td align='right'>" . number_format($p['nilaibaru'], 2) . "</td>
        <td align='right'>" . number_format($beda, 2) . "</td>
    </tr>";
}
if ($no == 0) {
    $tbl .= "<tr class=rowcontent><td align='center' colspan='" . $kolom . "' style='background-color:green;color:white'>TIDAK ADA YANG PERLU DIKOREKSI</td></tr>";
}
$tbl .= "</tbody></table>";
echo $tbl;
echo "<div style='font-size:12px;margin:6px 0;'><b>Jumlah koreksi</b> : " . count($plan) . " baris blok (qty: " . $jmlQty . ", harga: " . $jmlHarga . ")
    &nbsp;|&nbsp; <b>Perubahan nilai jurnal (sisi debet)</b> : " . number_format($totalPerubahan, 2) . "
    &nbsp;|&nbsp; <span style='background:#fff59d;padding:0 6px;'>kuning</span> = nilai yang berubah</div>";

if (count($skip) > 0) {
    $tblSkip = "<table class=sortable cellspacing=1 cellpadding=5 style='width:100%;margin-top:8px'>
        <thead>
            <tr class=rowcontent><td align='center' colspan='5' style='background:orange;color:white'><b>DILEWATI (" . count($skip) . ") - PERLU DICEK MANUAL</b></td></tr>
            <tr class=rowheader style='text-align:center'><th>No.</th><th>Transaksi</th><th>Barang</th><th>Blok</th><th>Alasan</th></tr>
        </thead><tbody>";
    $nos = 0;
    foreach ($skip as $s) {
        $nos++;
        $kb = explode(' ', $s[0]);
        $tblSkip .= "<tr class=rowcontent>
            <td align='center'>" . $nos . "</td><td align='center'>" . h($kb[0]) . "</td>
            <td>" . h($kb[1]) . " - " . h(namaBarang($kb[1])) . "</td><td align='center'>" . h($s[1]) . "</td><td>" . h($s[2]) . "</td></tr>";
    }
    echo $tblSkip . "</tbody></table>";
}

if (!$proses) {
    if (count($plan) > 0) {
        echo "<div style='margin:10px 0;'>";
        if (isset($dariCekSelisih)) {
            // dipanggil dari halaman Cek Selisih Gudang (tipe Perbaiki Proporsi Blok)
            echo "<button class=\"mybutton\" onclick=\"prosesProporsiBlok()\">Proses: simpan " . count($plan) . " koreksi</button> <span style='font-size:11px'>Belum ada data yang diubah. Pastikan sudah backup sebelum menyimpan.</span>";
        } else {
            $urlProses = "perbaikan_proporsi_blok.php?unit=" . urlencode($unit) . "&periode=" . urlencode($periode) . "&noakun=" . urlencode($noakun)
                . "&notransaksi=" . urlencode($notransaksi) . "&mode=proses&konfirmasi=YA";
            echo "<a href=\"" . $urlProses . "\" onclick=\"return confirm('Simpan " . count($plan) . " koreksi? Pastikan sudah backup.');\" style=\"font-size:14px;font-weight:bold\">[ PROSES: simpan koreksi ]</a>";
        }
        echo "</div>";
    }
    exit;
}
if (count($plan) == 0) {
    exit;
}
$rollback = array();
try {
    $owlPDO->beginTransaction();
    foreach ($plan as $p) {
        $d = $p['detail'];
        $updDetail->execute([$p['qtybaru'], $p['hrbaru'], $d['notransaksi'], $d['nopp'], $d['kodebarang'], $d['kodeblok'], $d['kodemesin'], $d['kodesegment']]);
        if ($updDetail->rowCount() != 1) {
            throw new Exception("Detail " . $p['kode'] . " blok " . $d['kodeblok'] . " tidak terupdate (rowCount " . $updDetail->rowCount() . ")");
        }
        $rollback[] = "update log_transaksidt_detail set jumlah=" . $p['qtylama'] . ",hargarata=" . $p['hrlama'] . " where notransaksi=" . $owlPDO->quote($d['notransaksi'])
            . " and nopp=" . $owlPDO->quote($d['nopp']) . " and kodebarang=" . $owlPDO->quote($d['kodebarang'])
            . " and kodeblok=" . $owlPDO->quote($d['kodeblok']) . " and kodemesin=" . $owlPDO->quote($d['kodemesin'])
            . " and kodesegment=" . $owlPDO->quote($d['kodesegment']) . ";";

        foreach ($p['lines'] as $ln) {
            $nilai = ($ln['jumlah'] > 0 ? 1 : -1) * $p['nilaibaru'];
            $ket = str_replace(' ' . $p['ketlama'] . ' ', ' ' . $p['ketbaru'] . ' ', $ln['keterangan']);
            $updJurnal->execute([$nilai, $ket, $ln['nojurnal'], $ln['nourut']]);
            $rollback[] = "update keu_jurnaldt set jumlah=" . $ln['jumlah'] . ",keterangan=" . $owlPDO->quote($ln['keterangan'])
                . " where nojurnal=" . $owlPDO->quote($ln['nojurnal']) . " and nourut=" . $ln['nourut'] . ";";
        }
        $updHeader->execute([$p['nilaibaru'], -1 * $p['nilaibaru'], $p['nojurnal']]);
        $rollback[] = "update keu_jurnalht set totaldebet=" . $p['head']['totaldebet'] . ",totalkredit=" . $p['head']['totalkredit']
            . " where nojurnal=" . $owlPDO->quote($p['nojurnal']) . ";";
    }
    // verifikasi akhir: total qty detail sama dengan qty transaksi dan harga detail sama dengan harga transaksi
    foreach ($listTrx as $t) {
        $kodeCek = $t['notransaksi'] . " " . $t['kodebarang'];
        if (isset($groupSkip[$kodeCek])) {
            continue;
        }
        $stDetail->execute([$t['notransaksi'], $t['kodebarang'], $t['kodekegiatan'], $t['kodemesin'], $t['kodeblok'] . '%']);
        $rows = $stDetail->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) < 2) {
            continue;
        }
        $tot = 0;
        foreach ($rows as $r) {
            $tot += $r['jumlah'];
            if ($t['hargarata'] > 0 && abs($r['hargarata'] - $t['hargarata']) > 0.000001) {
                throw new Exception("Verifikasi harga gagal untuk " . $kodeCek . " blok " . $r['kodeblok']);
            }
        }
        if (abs(round($tot - $t['jumlah'], 5)) >= 0.000005) {
            throw new Exception("Verifikasi qty gagal untuk " . $kodeCek . " (total detail " . $tot . " vs qty " . $t['jumlah'] . ")");
        }
    }
    $owlPDO->commit();
    echo "<div style='margin:10px 0;padding:8px 10px;background:green;color:white;font-weight:bold;'>SELESAI: " . count($plan) . " baris blok dikoreksi dan sudah disimpan.</div>";
    echo "<div style='font-size:12px;'><b>SQL untuk mengembalikan</b> (salin dan simpan, jalankan bila perlu membatalkan):</div>
        <textarea readonly rows='8' style='width:100%;font-family:monospace;font-size:11px;' onclick='this.select()'>" . h(implode("\n", $rollback)) . "</textarea>";
} catch (Exception $e) {
    $owlPDO->rollBack();
    exit("GAGAL, semua perubahan dibatalkan : " . $e->getMessage());
}
