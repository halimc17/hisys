<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
include_once('lib/terbilang.php');

# INSIALISASI
$stream = '';
$proses = checkPostGet('proses', '');
$param = $_POST;
if (count($param) == 0) {
    $param = $_GET;
}
$arrPeriode = explode("-", $param['periode']);
$bulan = $arrPeriode[1];
$tahun = $arrPeriode[0];

switch ($proses) {
    case 'getUnit':
        $optorg = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $sql = "SELECT distinct kodeorganisasi,namaorganisasi FROM " . $dbname . ".organisasi where 1=1 and kodeorganisasi IN (" . getOrgDetail(2) . ") AND induk = '{$param['kodept']}' ORDER BY induk ASC, kodeorganisasi ASC";
        $qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
        $qry->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $qry->fetch()) {
            $optorg .= "<option value='" . $bar['kodeorganisasi'] . "'>" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
        }

        echo $optorg;
        break;
    case 'getAkun':
        $optakunsch = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

        $tipeorganisasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe')[$param['kodeorg']] ?? '';
        $str = "SELECT a.noakun, a.namaakun, a.pemilik, b.noakun AS noakununit, b.kodeunit FROM keu_5akun a LEFT JOIN keu_5akununit b ON a.noakun = b.noakun WHERE a.kasbank = 1 AND a.detail = 1 AND a.aktif = 1 AND a.level = '5' AND a.noakun LIKE '11101%' AND ((b.kodeunit IS NOT NULL AND b.kodeunit IN ('" . $param['kodeorg'] . "')) OR (b.kodeunit IS NULL AND (a.pemilik = 'GLOBAL' OR a.pemilik = '{$tipeorganisasi}' OR a.pemilik IN ('" . $param['kodeorg'] . "')))) GROUP BY a.noakun";
        $res = fetchdata($str);
        foreach ($res as $bar) {
            $optakunsch .= "<option value='" . $bar['noakun'] . "'>" . $bar['noakun'] . " - " . $bar['namaakun'] . "</option>";
        }

        echo $optakunsch;
        break;
    case 'preview':
        $stream = '';
        if ($param['tipelaporan'] == 1) {
            $stream = previewDefault($param);
        } else if ($param['tipelaporan'] == 2) {
            $stream = previewKebun($param);
        }

        if ($param['tipe'] == 'excel') {
            $nop = "Report Plasma.xls";
            $xls = new HtmlExcel();
            $xls->setCss($css);
            $xls->addSheet('Report Plasma', $stream);
            $xls->headers($nop);
            echo $xls->buildFile();
        } else {
            echo $stream;
        }
        break;
}

function previewDefault($param)
{
    global $dbname;
    global $bulan;

    $weeks = getWeeksInPeriod($param['periode']);
    $countWeek = count($weeks);
    $weekHeader = $weekHeader2 = $weekPlaceholder = $weekPlaceholderMinusOne = $weekBody = '';

    foreach ($weeks as $week) {
        $weekHeader .= "<th colspan='2'>Minggu Ke-{$week['week']}<br><small>({$week['start_date']} s/d {$week['end_date']})</small></th>";

        $weekHeader2 .= "<th align='center'>Jumlah</th>";
        $weekHeader2 .= "<th align='center'>Nominal</th>";

        $weekPlaceholder .= "<td></td><td></td>";
    }

    // Diisi sebanyak (countWeek - 1) kali, minimal 1
    $repeatCount = max(1, $countWeek - 1);
    $weekPlaceholderMinusOne = str_repeat("<td></td><td></td>", $repeatCount);

    # Get data Jumlah Fisik dan Nominal
    $qCashopname = "SELECT a.mingguke, a.noakun, a.periode, a.unit, SUM(b.jumlah) as jumlah, SUM(b.nominal) as nominal, b.jumlahfisik FROM keu_cashopnameht a LEFT JOIN keu_cashopnamedt b ON a.notransaksi = b.notransaksi WHERE a.unit = '{$param['kodeorg']}' AND a.periode = '{$param['periode']}' AND a.noakun='{$param['noakun']}' GROUP BY a.mingguke, a.noakun, a.periode, a.unit, b.jumlahfisik ORDER BY a.mingguke, b.jumlahfisik";
    $rCashopname = fetchData($qCashopname);
    $dataCashOpname = [];
    foreach ($rCashopname as $row) {
        $dataCashOpname[$row['mingguke']][$row['jumlahfisik']] = [
            'jumlahfisik' => $row['jumlahfisik'],
            'jumlah'  => $row['jumlah'],
            'nominal' => $row['nominal'],
        ];
    }

    $qJumlahFisik = selectQuery($dbname, "keu_5jumlahfisik", "jumlahfisik", "1=1 ORDER BY jumlahfisik ASC");
    $rJumlahFisik = fetchData($qJumlahFisik);
    foreach ($rJumlahFisik as $row) {
        $weekBody .= "<tr class='rowcontent'>";
        $weekBody .= "<td align='right'>" . number_format($row['jumlahfisik'], 2) . "</td>";
        foreach ($weeks as $week) {
            $jumlah = isset($dataCashOpname[$week['week']][$row['jumlahfisik']]) ? $dataCashOpname[$week['week']][$row['jumlahfisik']]['jumlah'] : 0;
            $nominal = isset($dataCashOpname[$week['week']][$row['jumlahfisik']]) ? $dataCashOpname[$week['week']][$row['jumlahfisik']]['nominal'] : 0;

            $weekBody .= "<td align='right'>" . number_format($jumlah, 2) . "</td>";
            $weekBody .= "<td align='right'>" . number_format($nominal, 2) . "</td>";

            $dataCashOpname[$week['week']]['totaljumlah'] += $jumlah;
            $dataCashOpname[$week['week']]['totalnominal'] += $nominal;

            $dataCashOpname['grandtotaljumlah'] += $jumlah;
            $dataCashOpname['grandtotalnominal'] += $nominal;
        }
        $weekBody .= "</tr>";
    }

    $weekFooter = "<tr class='rowcontent' style='font-weight:bold;'>";
    $weekFooter .= "<td style='background-color:yellow'>Total :</td>";
    foreach ($weeks as $week) {
        $totalJumlah = isset($dataCashOpname[$week['week']]['totaljumlah']) ? $dataCashOpname[$week['week']]['totaljumlah'] : 0;
        $totalNominal = isset($dataCashOpname[$week['week']]['totalnominal']) ? $dataCashOpname[$week['week']]['totalnominal'] : 0;

        $weekFooter .= "<td align='right'>" . number_format($totalJumlah, 2) . "</td>";
        $weekFooter .= "<td align='right'>" . number_format($totalNominal, 2) . "</td>";
    }
    $weekFooter .= "</tr>";

    # Get Data Outstanding Kas (Saldo Akhir Kas)
    $qSaldoBulanan = "SELECT SUM(awal{$bulan}) as awal, SUM(debet{$bulan}) as debet, SUM(kredit{$bulan}) as kredit
        FROM keu_saldobulanan
        WHERE kodeorg = '{$param['kodeorg']}' AND periode = '" . str_replace("-", "", $param['periode']) . "' AND noakun = '{$param['noakun']}'";
    $rSaldoBulanan = fetchData($qSaldoBulanan);
    $saldoAwalKas = isset($rSaldoBulanan[0]['awal']) ? $rSaldoBulanan[0]['awal'] : 0;

    # Mutasi Kas
    $qMutasi = selectQuery($dbname, "keu_kasbankht", "SUM(CASE WHEN tipetransaksi='K' THEN jumlah*kurs ELSE 0 END) AS kredit, SUM(CASE WHEN tipetransaksi='M' THEN jumlah*kurs ELSE 0 END) AS debet", "kodeorg = '{$param['kodeorg']}' AND tanggal LIKE '{$param['periode']}%' AND noakun = '{$param['noakun']}'");
    $rMutasi = fetchData($qMutasi);
    $debetKas = isset($rMutasi[0]['debet']) ? $rMutasi[0]['debet'] : 0;
    $kreditKas = isset($rMutasi[0]['kredit']) ? $rMutasi[0]['kredit'] : 0;

    $saldoAkhirKas = $saldoAwalKas + $debetKas - $kreditKas;

    # Outstanding Kas Bon (Data PDO sudah ada Kasbank yang sudah terbayarkan)
    $qDataOutstandingKasBon = "SELECT 
                h.nopdo, 
                h.notransaksi, 
                h.kodeorg, 
                d.rincian, 
                d.rupiahdiajukan
            FROM keu_pdoht h
            INNER JOIN keu_pdodt d 
                ON h.nopdo = d.nopdo 
            INNER JOIN keu_kasbankdt kd 
                ON h.notransaksi = kd.keterangan1 
                AND d.nodok = kd.nodok 
            INNER JOIN keu_kasbankht kh 
                ON kd.notransaksi = kh.notransaksi
            WHERE 
                h.kodeorg = '{$param['kodeorg']}' 
                AND h.periode = '{$param['periode']}' 
                AND d.tipekasbank = 'KAS'
                AND (kh.novoucher != '' AND kh.novoucher IS NOT NULL)
                AND kh.pembayaran='1'";
    $rDataOutstandingKasBon = fetchData($qDataOutstandingKasBon);
    if (!empty($rDataOutstandingKasBon)) {
        $outstandingKasBonBody = '';
        foreach ($rDataOutstandingKasBon as $row) {
            $outstandingKasBonBody .= "<tr class='rowcontent'>";
            $outstandingKasBonBody .= "<td>{$row['rincian']}</td>";
            $outstandingKasBonBody .= $weekPlaceholderMinusOne;
            $outstandingKasBonBody .= "<td align='right'></td>";
            $outstandingKasBonBody .= "<td align='right'>" . number_format($row['rupiahdiajukan'], 2) . "</td>";
            $outstandingKasBonBody .= "</tr>";
        }
    } else {
        $outstandingKasBonBody = "<tr class='rowcontent'><td colspan='" . (count($weeks) * 2 + 1) . "' style='font-weight:bold;'>Data Outstanding Kas Bon tidak ditemukan</td></tr>";
    }

    $totalOutstandingKasBon = array_sum(array_column($rDataOutstandingKasBon, 'rupiahdiajukan'));
    $jumlahFisik = $saldoAkhirKas - $totalOutstandingKasBon;
    $selisihLebih = $jumlahFisik - $dataCashOpname['grandtotalnominal'];

    # Rencana Pembayaran (Data PDO tapi belum ada kasbanknya)
    $qDataRencanaPembayaran = "SELECT 
            h.nopdo, 
            h.notransaksi, 
            h.kodeorg, 
            h.periode,
            d.rincian, 
            d.rupiahdiajukan,
            d.tipekasbank
        FROM keu_pdoht h
        INNER JOIN keu_pdodt d 
            ON h.nopdo = d.nopdo
        LEFT JOIN keu_kasbankdt kd 
            ON h.notransaksi = kd.keterangan1 
            AND d.nodok = kd.nodok
        WHERE 
            h.kodeorg = '{$param['kodeorg']}' 
            AND h.periode = '{$param['periode']}' 
            AND kd.notransaksi IS NULL";
    $rDataRencanaPembayaran = fetchData($qDataRencanaPembayaran);
    if (!empty($rDataRencanaPembayaran)) {
        $rencanaPembayaranKasBody = $rencanaPembayaranBankBody = '';
        $totalRencanaPembayaran = 0;
        foreach ($rDataRencanaPembayaran as $row) {
            if ($row['tipekasbank'] == 'KAS') {
                $rencanaPembayaranKasBody .= "<tr class='rowcontent'>";
                $rencanaPembayaranKasBody .= "<td>{$row['rincian']}</td>";
                $rencanaPembayaranKasBody .= $weekPlaceholderMinusOne;
                $rencanaPembayaranKasBody .= "<td align='right'></td>";
                $rencanaPembayaranKasBody .= "<td align='right'>" . number_format($row['rupiahdiajukan'], 2) . "</td>";
                $rencanaPembayaranKasBody .= "</tr>";
            } else if ($row['tipekasbank'] == 'BANK') {
                $rencanaPembayaranBankBody .= "<tr class='rowcontent'>";
                $rencanaPembayaranBankBody .= "<td>{$row['rincian']}</td>";
                $rencanaPembayaranBankBody .= $weekPlaceholderMinusOne;
                $rencanaPembayaranBankBody .= "<td align='right'></td>";
                $rencanaPembayaranBankBody .= "<td align='right'>" . number_format($row['rupiahdiajukan'], 2) . "</td>";
                $rencanaPembayaranBankBody .= "</tr>";
            }
            $totalRencanaPembayaran += $row['rupiahdiajukan'];
        }

        if ($rencanaPembayaranKasBody == '') {
            $rencanaPembayaranKasBody = "<tr class='rowcontent'><td colspan='" . (count($weeks) * 2 + 1) . "' style='font-weight:bold;'>Data Rencana Pembayaran Kas tidak ditemukan</td></tr>";
        }

        if ($rencanaPembayaranBankBody == '') {
            $rencanaPembayaranBankBody = "<tr class='rowcontent'><td colspan='" . (count($weeks) * 2 + 1) . "' style='font-weight:bold;'>Data Rencana Pembayaran Bank tidak ditemukan</td></tr>";
        }
    }


    $gapRow = "<tr class='rowcontent'><td colspan='" . (count($weeks) * 2 + 1) . "'>&nbsp;</td></tr>";

    $stream = "
        <table style='width:100%' cellpadding='5px' cellspacing='1' class='sortable'>
            <thead>
                <tr class='rowheader'>
                    <th rowspan='2'>URAIAN</th>
                    {$weekHeader}
                </tr>
                <tr class='rowheader'>
                    {$weekHeader2}
                </tr>
            </thead>
            <tbody>
                <tr class='rowcontent'>
                    <td style='font-weight:bold'>Jumlah Fisik :</td>
                    {$weekPlaceholder}
                </tr>
                {$weekBody}
                {$weekFooter}

                {$gapRow}

                <tr class='rowcontent'>
                    <td>Outstanding (Kas P. Bun)</td>
                    {$weekPlaceholderMinusOne}
                    <td align='right'></td>
                    <td align='right'>" . number_format($saldoAkhirKas, 2) . "</td>
                </tr>
                <tr class='rowcontent'>
                    <td>Outstanding</td>
                    {$weekPlaceholderMinusOne}
                    <td align='right'></td>
                    <td align='right'>" . number_format(0, 2) . "</td>
                </tr>
                <tr class='rowcontent'>
                    <td>Total</td>
                    {$weekPlaceholderMinusOne}
                    <td align='right'></td>
                    <td align='right'>" . number_format(0, 2) . "</td>
                </tr>

                {$gapRow}

                <tr class='rowcontent' style='font-weight:bold'>
                    <td>Saldo Kas Menurut Buku</td>
                    {$weekPlaceholderMinusOne}
                    <td align='right'></td>
                    <td align='right'>" . number_format($saldoAkhirKas, 2) . "</td>
                </tr>
                <tr class='rowcontent' style='font-weight:bold'>
                    <td>Outstanding Kas Bon :</td>
                    {$weekPlaceholder}
                </tr>
                {$outstandingKasBonBody}
                <tr class='rowcontent' style='font-weight:bold'>
                    <td>Jumlah Fisik</td>
                    {$weekPlaceholderMinusOne}
                    <td align='right'></td>
                    <td align='right'>" . number_format($jumlahFisik, 2) . "</td>
                </tr>
                <tr class='rowcontent' style='font-weight:bold'>
                    <td>Selisih Lebih (Kurang)</td>
                    {$weekPlaceholderMinusOne}
                    <td align='right'></td>
                    <td align='right'>" . number_format($selisihLebih, 2) . "</td>
                </tr>

                {$gapRow}

                <tr class='rowcontent'>
                    <td>Saldo Fisik</td>
                    {$weekPlaceholderMinusOne}
                    <td align='right'></td>
                    <td align='right'>" . number_format($dataCashOpname['grandtotalnominal'], 2) . "</td>
                </tr>
                
                {$gapRow}

                <tr class='rowcontent' style='font-weight:bold'>
                    <td>Rencana Pembayaran :</td>
                    {$weekPlaceholder}
                </tr>
                <tr class='rowcontent' style='font-weight:bold'>
                    <td>Rencana Pembayaran BANK :</td>
                    {$weekPlaceholder}
                </tr>
                {$rencanaPembayaranBankBody}
                <tr class='rowcontent' style='font-weight:bold'>
                    <td>Rencana Pembayaran KAS :</td>
                    {$weekPlaceholder}
                </tr>
                {$rencanaPembayaranKasBody}
                <tr class='rowcontent' style='font-weight:bold;background-color:yellow;'>
                    <td>Total Rencana Pembayaran :</td>
                    {$weekPlaceholderMinusOne}
                    <td align='right'></td>
                    <td align='right'>" . number_format($totalRencanaPembayaran, 2) . "</td>
                </tr>

                {$gapRow}

                <tr class='rowcontent' style='font-weight:bold;background-color:lightgreen;'>
                    <td>Selisih Rencana Pembayaran dgn Saldo :</td>
                    {$weekPlaceholderMinusOne}
                    <td align='right'></td>
                    <td align='right'>" . number_format(0 - $totalRencanaPembayaran, 2) . "</td>
                </tr>
            </tbody>
        </table>";
    return $stream;
}

function previewKebun($param)
{
    global $dbname;
    global $bulan;

    # Get Data Outstanding Kas (Saldo Akhir Kas)
    $qSaldoBulanan = "SELECT SUM(awal{$bulan}) as awal, SUM(debet{$bulan}) as debet, SUM(kredit{$bulan}) as kredit
        FROM keu_saldobulanan
        WHERE kodeorg = '{$param['kodeorg']}' AND periode = '" . str_replace("-", "", $param['periode']) . "' AND noakun = '{$param['noakun']}'";
    $rSaldoBulanan = fetchData($qSaldoBulanan);
    $saldoAwalKas = isset($rSaldoBulanan[0]['awal']) ? $rSaldoBulanan[0]['awal'] : 0;

    # Mutasi Kas
    $qMutasi = selectQuery($dbname, "keu_kasbankht", "tipetransaksi, jumlah, keterangan", "kodeorg = '{$param['kodeorg']}' AND tanggal LIKE '{$param['periode']}%' AND noakun = '{$param['noakun']}' AND pembayaran='1' AND novoucher != '' ORDER BY tipetransaksi ASC");
    $rMutasi = fetchData($qMutasi);

    $penerimaanKasBody = $pengeluaranKasBody = "";
    $countPenerimaanKas = $countPengeluaranKas = 1;
    $jumlahPenerimaanKas = $jumlahPengeluaranKas = 0;
    foreach ($rMutasi as $key => $val) {
        if ($val['tipetransaksi'] == 'M') {
            $penerimaanKasBody .= "<tr class='rowcontent'>";
            $penerimaanKasBody .= "<td>" . $countPenerimaanKas++ . "</td>";
            $penerimaanKasBody .= "<td>{$val['keterangan']}</td>";
            $penerimaanKasBody .= "<td>Rp.</td>";
            $penerimaanKasBody .= "<td align='right'>" . number_format($val['jumlah'], 2) . "</td>";
            $penerimaanKasBody .= "</tr>";

            $jumlahPenerimaanKas += $val['jumlah'];
        } else if ($val['tipetransaksi'] == 'K') {
            $pengeluaranKasBody .= "<tr class='rowcontent'>";
            $pengeluaranKasBody .= "<td>" . $countPengeluaranKas++ . "</td>";
            $pengeluaranKasBody .= "<td>{$val['keterangan']}</td>";
            $pengeluaranKasBody .= "<td>Rp.</td>";
            $pengeluaranKasBody .= "<td align='right'>" . number_format($val['jumlah'], 2) . "</td>";
            $pengeluaranKasBody .= "</tr>";

            $jumlahPengeluaranKas += $val['jumlah'];
        }
    }

    $saldoAkhirKas = $saldoAwalKas + $jumlahPenerimaanKas - $jumlahPengeluaranKas;

    # Get data Jumlah Fisik dan Nominal
    $qCashopname = "SELECT a.noakun, a.periode, a.unit, SUM(b.jumlah) as jumlah, SUM(b.nominal) as nominal, b.jumlahfisik FROM keu_cashopnameht a LEFT JOIN keu_cashopnamedt b ON a.notransaksi = b.notransaksi WHERE a.unit = '{$param['kodeorg']}' AND a.periode = '{$param['periode']}' AND a.noakun='{$param['noakun']}' GROUP BY a.noakun, a.periode, a.unit, b.jumlahfisik ORDER BY b.jumlahfisik";
    $rCashopname = fetchData($qCashopname);
    $dataCashOpname = [];
    foreach ($rCashopname as $row) {
        $dataCashOpname[$row['jumlahfisik']] = [
            'jumlahfisik' => $row['jumlahfisik'],
            'jumlah'  => $row['jumlah'],
            'nominal' => $row['nominal'],
        ];
    }

    $qJumlahFisik = selectQuery($dbname, "keu_5jumlahfisik", "jumlahfisik", "1=1 ORDER BY jumlahfisik ASC");
    $rJumlahFisik = fetchData($qJumlahFisik);
    $countJumlahFisik = 1;
    $fisikUangBody = "<table cellpadding='5px' cellspacing='1' class='sortable' border='0' style='border-collapse:collapse;width:55%'>";
    foreach ($rJumlahFisik as $row) {
        $jumlah = isset($dataCashOpname[$row['jumlahfisik']]) ? $dataCashOpname[$row['jumlahfisik']]['jumlah'] : 0;
        $nominal = isset($dataCashOpname[$row['jumlahfisik']]) ? $dataCashOpname[$row['jumlahfisik']]['nominal'] : 0;
        $satuan = getSatuanUang($row['jumlahfisik']);

        $fisikUangBody .= "<tr class='rowcontent'>";
        $fisikUangBody .= "<td align='center' style='width:10px'>" . $countJumlahFisik++ . "</td>";
        $fisikUangBody .= "<td>" . terbilang($row['jumlahfisik'], 3) . "</td>";
        $fisikUangBody .= "<td align='center' style='width:25px'>{$satuan}</td>";
        $fisikUangBody .= "<td align='center' style='width:25px'>{$jumlah}</td>";
        $fisikUangBody .= "<td align='center' style='width:10px'>x</td>";
        $fisikUangBody .= "<td align='right' style='width:50px'>" . number_format($row['jumlahfisik'], 2) . "</td>";
        $fisikUangBody .= "<td align='center' style='width:10px'>=</td>";
        $fisikUangBody .= "<td align='center' style='width:10px'>Rp.</td>";
        $fisikUangBody .= "<td align='right'>" . number_format($nominal, 2) . "</td>";
        $fisikUangBody .= "</tr>";

        $dataCashOpname['totaljumlah'] += $jumlah;
        $dataCashOpname['totalnominal'] += $nominal;
    }
    $fisikUangBody .= "</table>";

    $selisih = $saldoAkhirKas - $dataCashOpname['totalnominal'];

    $gapRow = "<tr class='rowcontent'><td colspan='4'>&nbsp;</td></tr>";

    $stream = "<table style='width:100%' cellpadding='5px' cellspacing='1' class='sortable'>
            <tbody>
                <tr class='rowcontent' style='font-weight:bold;'>
                    <td colspan='2'>Saldo Awal</td>
                    <td>Rp.</td>
                    <td align='right'>" . number_format($saldoAwalKas, 2) . "</td>
                </tr>
                {$gapRow}

                <tr class='rowcontent''>
                    <td colspan='4'>Penerimaan Kas :</td>
                </tr>
                {$gapRow}
                {$penerimaanKasBody}
                <tr class='rowcontent' style='font-weight:bold;'>
                    <td>&nbsp;</td>
                    <td>Jumlah Penerimaan Kas</td>
                    <td>Rp.</td>
                    <td align='right'>" . number_format($jumlahPenerimaanKas, 2) . "</td>
                </tr>

                <tr class='rowcontent''>
                    <td colspan='4'>Pengeluaran Kas :</td>
                </tr>
                {$gapRow}
                {$pengeluaranKasBody}
                <tr class='rowcontent' style='font-weight:bold;'>
                    <td>&nbsp;</td>
                    <td>Jumlah Pengeluaran Kas</td>
                    <td>Rp.</td>
                    <td align='right'>" . number_format($jumlahPengeluaranKas, 2) . "</td>
                </tr>

                {$gapRow}
                <tr class='rowcontent' style='font-weight:bold;'>
                    <td>&nbsp;</td>
                    <td>Saldo Akhir</td>
                    <td>Rp.</td>
                    <td align='right'>" . number_format($saldoAkhirKas, 2) . "</td>
                </tr>

                {$gapRow}
                {$gapRow}
                <tr class='rowcontent'>
                    <td colspan='4'>Jumlah Fisik Uang</td>
                </tr>
                <tr class='rowcontent'>
                    <td colspan='4'>{$fisikUangBody}</td>
                </tr>
                <tr class='rowcontent' style='font-weight:bold;'>
                    <td>&nbsp;</td>
                    <td>Jumlah Fisik Uang</td>
                    <td>Rp.</td>
                    <td align='right'>" . number_format($dataCashOpname['totalnominal'], 2) . "</td>
                </tr>
                <tr class='rowcontent' style='font-weight:bold;'>
                    <td>&nbsp;</td>
                    <td>Selisih (Lebih/Kurang)</td>
                    <td>Rp.</td>
                    <td align='right'>" . number_format($selisih, 2) . "</td>
                </tr>
            </tbody>
        </table>";
    return $stream;
}

function getSatuanUang($nominal)
{
    return $nominal < 1000 ? 'keping' : 'lembar';
}

/**
 * Mendapatkan daftar minggu dalam satu bulan berdasarkan periode.
 *
 * @param string $period Format: 'YYYY-MM' (contoh: '2026-03')
 * @return array         Array berisi minggu dengan start_date dan end_date
 * @throws \InvalidArgumentException Jika format periode tidak valid
 */
function getWeeksInPeriod(string $period): array
{
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
        throw new \InvalidArgumentException("Format periode tidak valid: '{$period}'. Gunakan format 'YYYY-MM'.");
    }

    [$year, $month] = array_map('intval', explode('-', $period));

    $totalDays = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
    $weeks     = [];
    $weekIndex = 1;
    $weekStart = null;

    for ($day = 1; $day <= $totalDays; $day++) {
        $date      = \DateTime::createFromFormat('Y-n-j', "{$year}-{$month}-{$day}");
        $dayOfWeek = (int) $date->format('N'); // 1 = Senin, 7 = Minggu

        if ($weekStart === null) {
            $weekStart = $date->format('Y-m-d');
        }

        // Akhir minggu = hari Minggu (N=7) atau hari terakhir bulan
        if ($dayOfWeek === 7 || $day === $totalDays) {
            $weeks[] = [
                'week'       => $weekIndex,
                'start_date' => $weekStart,
                'end_date'   => $date->format('Y-m-d'),
            ];
            $weekIndex++;
            $weekStart = null;
        }
    }

    return $weeks;
}
