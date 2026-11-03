<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');
require_once('lib/fpdf.php');

$method = checkPostGet('method', '');
$optNma = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$namasupplier = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier');

#=== Filter Daftar Penerimaan Barang serta tarikan Excel/PDF (dipakai bersama oleh list, Excel, dan PDF) ===
#filter dibersihkan dulu: teks bebas di-escape, tanggal dd-mm-yyyy, status 0/1, kode alfanumerik, tahun 4 digit (kosong = semua)
if (!function_exists('dpbFilter')) {
    function dpbFilter()
    {
        global $owlPDO;
        $f = array('notrans' => '', 'nopo' => '', 'nopp' => '', 'barang' => '', 'tgl' => '', 'tahun' => '', 'gudang' => '', 'supplier' => '', 'status' => '');
        $teks = array('notrans' => 'txtNoTransaksi', 'nopo' => 'txtSearch', 'barang' => 'nmBrg');
        foreach ($teks as $kunci => $nama) {
            $v = trim((string)checkPostGet($nama, ''));
            if ($v != '' and $v != 'undefined' and $v != 'null') {
                $f[$kunci] = substr($owlPDO->quote($v), 1, -1);
            }
        }
        $nopp = trim((string)checkPostGet('nopp', ''));
        if ($nopp != '' and $nopp != 'undefined' and $nopp != 'null') {
            if (strlen($nopp) < 3) {
                exit("Error: masukan nopp min 3 karakter");
            }
            $f['nopp'] = substr($owlPDO->quote($nopp), 1, -1);
        }
        $tgl = trim((string)checkPostGet('tglCari', ''));
        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $tgl, $m)) {
            $f['tgl'] = $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        $th = trim((string)checkPostGet('filterTahun', ''));
        if (preg_match('/^\d{4}$/', $th)) {
            $f['tahun'] = $th;
        }
        $gd = trim((string)checkPostGet('kdGudang', ''));
        if ($gd != '' and preg_match('/^[A-Za-z0-9_\-\.]+$/', $gd)) {
            $f['gudang'] = $gd;
        }
        $sp = trim((string)checkPostGet('kodesupplier', ''));
        if ($sp != '' and preg_match('/^[A-Za-z0-9_\-\.]+$/', $sp)) {
            $f['supplier'] = $sp;
        }
        $st = (string)checkPostGet('statusposting', '');
        if (in_array($st, array('0', '1'), true)) {
            $f['status'] = $st;
        }
        return $f;
    }
}
if (!function_exists('dpbWhere')) {
    function dpbWhere()
    {
        global $dbname;
        $f = dpbFilter();
        #hanya gudang pada unit yang boleh diakses user (berlaku juga untuk Excel/PDF)
        $where = " and substr(a.kodegudang,1,4) in (" . getOrgDetail(2) . ")";
        if ($f['notrans'] != '') {
            $where .= " and a.notransaksi like '%" . $f['notrans'] . "%'";
        }
        if ($f['nopp'] != '') {
            $where .= " and a.nopo in (select distinct nopo from " . $dbname . ".log_podt where nopp like '" . $f['nopp'] . "%')";
        } elseif ($f['nopo'] != '') {
            $where .= " and a.nopo like '%" . $f['nopo'] . "%'";
        }
        if ($f['barang'] != '') {
            $where .= " and exists (select 1 from " . $dbname . ".log_transaksidt d where d.notransaksi=a.notransaksi and d.kodebarang in (select kodebarang from " . $dbname . ".log_5masterbarang where namabarang like '%" . $f['barang'] . "%'))";
        }
        if ($f['gudang'] != '') {
            $where .= " and a.kodegudang='" . $f['gudang'] . "'";
        }
        if ($f['supplier'] != '') {
            $where .= " and a.idsupplier='" . $f['supplier'] . "'";
        }
        if ($f['tgl'] != '') {
            $where .= " and a.tanggal='" . $f['tgl'] . "'";
        }
        if ($f['tahun'] != '') {
            $where .= " and a.tanggal>='" . $f['tahun'] . "-01-01' and a.tanggal<='" . $f['tahun'] . "-12-31'";
        }
        if ($f['status'] != '') {
            $where .= ($f['status'] == '1') ? " and a.post=1" : " and a.post=0";
        }
        return $where;
    }
}
#satu penerimaan beserta ringkasan barangnya (jumlah item)
if (!function_exists('dpbSelect')) {
    function dpbSelect()
    {
        global $dbname;
        return "select a.notransaksi,a.tanggal,a.nopo,a.kodegudang,a.idsupplier,a.nosj,a.nofaktur,a.post,
            (select count(*) from " . $dbname . ".log_transaksidt d where d.notransaksi=a.notransaksi) as jmlitem
            from " . $dbname . ".log_transaksiht a where a.tipetransaksi=1";
    }
}
#qty tanpa nol di belakang koma (10, 2.5, 0.125)
if (!function_exists('dpbQty')) {
    function dpbQty($v)
    {
        $s = rtrim(rtrim(number_format((float)$v, 5, '.', ','), '0'), '.');
        return $s === '' ? '0' : $s;
    }
}
#barang tiap penerimaan: notransaksi => daftar (kodebarang, namabarang, satuan, jumlah, nopp)
if (!function_exists('dpbBarang')) {
    function dpbBarang($daftarNoTransaksi)
    {
        global $owlPDO, $dbname;
        $hasil = array();
        if (count($daftarNoTransaksi) == 0) {
            return $hasil;
        }
        $in = implode(',', array_fill(0, count($daftarNoTransaksi), '?'));
        $stmt = $owlPDO->prepare("select d.notransaksi,d.kodebarang,d.satuan,d.jumlah,d.nopp,ifnull(m.namabarang,d.kodebarang) as namabarang
            from " . $dbname . ".log_transaksidt d left join " . $dbname . ".log_5masterbarang m on m.kodebarang=d.kodebarang
            where d.notransaksi in (" . $in . ") order by d.notransaksi, d.kodebarang");
        $stmt->execute(array_values($daftarNoTransaksi));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
            $hasil[$b['notransaksi']][] = $b;
        }
        return $hasil;
    }
}
#satu baris per barang untuk tarikan Excel/PDF (dibatasi supaya tidak berat); filter nama barang berlaku per baris barang
if (!function_exists('dpbDataBaris')) {
    function dpbDataBaris($maks = 5000)
    {
        global $owlPDO, $dbname, $dpbTerpotong;
        $f = dpbFilter();
        $where = dpbWhere();
        if ($f['barang'] != '') {
            $where .= " and m.namabarang like '%" . $f['barang'] . "%'";
        }
        $res = $owlPDO->query("select a.notransaksi,a.tanggal,a.nopo,a.kodegudang,a.idsupplier,a.nosj,a.post,d.kodebarang,d.satuan,d.jumlah,d.nopp,ifnull(m.namabarang,d.kodebarang) as namabarang
            from " . $dbname . ".log_transaksiht a
            join " . $dbname . ".log_transaksidt d on d.notransaksi=a.notransaksi
            left join " . $dbname . ".log_5masterbarang m on m.kodebarang=d.kodebarang
            where a.tipetransaksi=1 " . $where . " order by a.tanggal desc, a.notransaksi desc, d.kodebarang limit " . ((int)$maks + 1))
            or die(print " Gagal: " . PDOException::getMessage());
        $rows = $res->fetchAll(PDO::FETCH_ASSOC);
        $dpbTerpotong = count($rows) > $maks;
        return array_slice($rows, 0, $maks);
    }
}
if (!function_exists('dpbStatus')) {
    function dpbStatus($bar)
    {
        return ($bar['post'] == 1) ? 'Sudah diposting' : 'Belum diposting';
    }
}
#warna status: hijau sudah diposting, kuning belum diposting
if (!function_exists('dpbWarna')) {
    function dpbWarna($status)
    {
        $w = array('Sudah diposting' => array('#c6efce', '#006100'), 'Belum diposting' => array('#ffeb9c', '#9c5700'));
        return isset($w[$status]) ? $w[$status] : array('', '');
    }
}
#keterangan filter yang aktif untuk kop tarikan
if (!function_exists('dpbInfoFilter')) {
    function dpbInfoFilter()
    {
        global $optNma, $namasupplier, $dpbTerpotong;
        $f = dpbFilter();
        $info = array();
        $info[] = 'Tahun: ' . ($f['tahun'] != '' ? $f['tahun'] : 'Semua');
        $info[] = 'Gudang: ' . ($f['gudang'] != '' ? (isset($optNma[$f['gudang']]) ? $optNma[$f['gudang']] : $f['gudang']) : 'Semua');
        $info[] = 'Status: ' . ($f['status'] === '1' ? 'Sudah diposting' : ($f['status'] === '0' ? 'Belum diposting' : 'Semua'));
        if ($f['supplier'] != '') {
            $info[] = 'Supplier: ' . (isset($namasupplier[$f['supplier']]) ? trim($namasupplier[$f['supplier']]) : $f['supplier']);
        }
        if ($f['notrans'] != '') {
            $info[] = 'No. Transaksi: ' . stripslashes($f['notrans']);
        }
        if ($f['nopo'] != '') {
            $info[] = 'No. PO/SO: ' . stripslashes($f['nopo']);
        }
        if ($f['nopp'] != '') {
            $info[] = 'No. PR/SR: ' . stripslashes($f['nopp']);
        }
        if ($f['barang'] != '') {
            $info[] = 'Barang: ' . stripslashes($f['barang']);
        }
        if ($f['tgl'] != '') {
            $info[] = 'Tanggal: ' . tanggalnormal($f['tgl']);
        }
        if (!empty($dpbTerpotong)) {
            $info[] = 'Dibatasi 5000 baris barang terbaru, persempit filter bila perlu';
        }
        return implode(' | ', $info);
    }
}
#teks yang kepanjangan dipotong dan diberi "..." supaya tidak tumpang tindih ke kolom sebelah
if (!function_exists('dpbFit')) {
    function dpbFit($pdf, $txt, $w, $f = 7)
    {
        $txt = utf8_decode($txt);
        $pdf->SetFont('Arial', '', $f);
        if ($pdf->GetStringWidth($txt) <= $w - 2) {
            return $txt;
        }
        while (strlen($txt) > 1 && $pdf->GetStringWidth($txt . '...') > $w - 2) {
            $txt = substr($txt, 0, -1);
        }
        return rtrim($txt) . '...';
    }
}

switch ($method) {
    case 'list_new_data':
    case 'loadData':
        $limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = (int)$_POST['page'];
            if ($page < 0) {
                $page = 0;
            }
        }
        $offset = $page * $limit;
        $where = dpbWhere();

        $query2 = $owlPDO->query("select count(*) as jmlhrow from " . $dbname . ".log_transaksiht a where a.tipetransaksi=1 " . $where) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs = (int)$query2->fetchColumn();

        $res = $owlPDO->query(dpbSelect() . $where . " order by a.tanggal desc, a.notransaksi desc limit " . $offset . "," . $limit) or die(print " Gagal: " . PDOException::getMessage());
        $baris = $res->fetchAll(PDO::FETCH_ASSOC);
        if (count($baris) == 0) {
            echo "<tr class=rowcontent><td colspan=11>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
            if ($page > 0) {
                echo "<tr class=rowheader><td colspan=11 align=center><button class=mybutton onclick=cariPage(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button></td></tr>";
            }
            break;
        }
        $barang = dpbBarang(array_column($baris, 'notransaksi'));
        $no = $offset;
        foreach ($baris as $bar) {
            $no += 1;
            $st = dpbStatus($bar);
            $wrn = dpbWarna($st);
            $sup = isset($namasupplier[$bar['idsupplier']]) ? trim($namasupplier[$bar['idsupplier']]) : $bar['idsupplier'];
            #ringkasan barang: tiga barang pertama beserta qty, sisanya lewat tombol detail
            $ringkas = '';
            $daftarBrg = isset($barang[$bar['notransaksi']]) ? $barang[$bar['notransaksi']] : array();
            foreach (array_slice($daftarBrg, 0, 3) as $b) {
                $ringkas .= "&bull; " . htmlspecialchars($b['namabarang']) . " : <b>" . dpbQty($b['jumlah']) . "</b> " . htmlspecialchars($b['satuan']) . "<br>";
            }
            if (count($daftarBrg) > 3) {
                $ringkas .= "<i>+ " . (count($daftarBrg) - 3) . " barang lagi (klik detail)</i>";
            }
            $noTrans = htmlspecialchars($bar['notransaksi'], ENT_QUOTES);
            echo "<tr class=rowcontent>
                <td align=center>" . $no . "</td>
                <td id=td_" . $no . ">" . htmlspecialchars($bar['notransaksi']) . "</td>
                <td align=center>" . tanggalnormal($bar['tanggal']) . "</td>
                <td>" . htmlspecialchars($bar['nopo']) . "</td>
                <td>" . htmlspecialchars($sup) . "</td>
                <td>" . htmlspecialchars(isset($optNma[$bar['kodegudang']]) ? $optNma[$bar['kodegudang']] : $bar['kodegudang']) . "</td>
                <td>" . htmlspecialchars($bar['nosj']) . "</td>
                <td align=center>" . $bar['jmlitem'] . "</td>
                <td style='font-size:11px'><div style='display:flex;justify-content:space-between;align-items:flex-start;gap:6px'><div>" . $ringkas . "</div><img src=images/zoom.png title='Detail barang' style='cursor:pointer;flex:none' onclick=\"detailBarangDPB('" . $noTrans . "', event);\"></div></td>
                <td align=center style='background-color:" . $wrn[0] . ";color:" . $wrn[1] . ";font-weight:bold'>" . $st . "</td>
                <td align=center nowrap>
                    <button class=mybutton onclick=\"previewBapb('" . $noTrans . "', event);\">" . $_SESSION['lang']['print'] . "</button>
                </td>
                </tr>";
        }
        $akhir = min(($page + 1) * $limit, $jlhbrs);
        echo "<tr class=rowheader><td colspan=11 align=center>
            " . ($offset + 1) . " to " . $akhir . " Of " . $jlhbrs . "<br />
            <button class=mybutton onclick=cariPage(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <button class=mybutton onclick=cariPage(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td></tr>";
        break;

    case 'detailbarang':
        #rincian barang satu penerimaan (hanya untuk gudang pada unit yang boleh diakses)
        $nt = trim((string)checkPostGet('notransaksi', ''));
        $stmt = $owlPDO->prepare(dpbSelect() . " and a.notransaksi=? and substr(a.kodegudang,1,4) in (" . getOrgDetail(2) . ")");
        $stmt->execute(array($nt));
        $hdr = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$hdr) {
            exit("Data penerimaan tidak ditemukan atau di luar akses anda.");
        }
        $barang = dpbBarang(array($nt));
        $sup = isset($namasupplier[$hdr['idsupplier']]) ? trim($namasupplier[$hdr['idsupplier']]) : $hdr['idsupplier'];
        echo "<div style='font-size:12px;margin-bottom:6px;'>
            <b>No. Transaksi</b> : " . htmlspecialchars($hdr['notransaksi']) . " &nbsp;|&nbsp; <b>Tanggal</b> : " . tanggalnormal($hdr['tanggal']) . "
            &nbsp;|&nbsp; <b>No. PO/SO</b> : " . htmlspecialchars($hdr['nopo']) . "<br>
            <b>Supplier</b> : " . htmlspecialchars($sup) . " &nbsp;|&nbsp; <b>Gudang</b> : " . htmlspecialchars(isset($optNma[$hdr['kodegudang']]) ? $optNma[$hdr['kodegudang']] : $hdr['kodegudang']) . "
            &nbsp;|&nbsp; <b>No. Surat Jalan</b> : " . htmlspecialchars($hdr['nosj']) . " &nbsp;|&nbsp; <b>Status</b> : " . dpbStatus($hdr) . "
            </div>
            <table class=sortable cellpadding=4 cellspacing=1 border=0 style='width:100%'>
            <thead><tr class=rowheader>
                <th style='width:30px'>No.</th><th style='width:110px'>Kode Barang</th><th>Nama Barang</th><th style='width:70px'>Satuan</th><th style='width:90px'>Qty</th><th style='width:190px'>No. PR/SR</th>
            </tr></thead><tbody>";
        $n = 0;
        foreach (isset($barang[$nt]) ? $barang[$nt] : array() as $b) {
            $n++;
            echo "<tr class=rowcontent>
                <td align=center>" . $n . "</td>
                <td>" . htmlspecialchars($b['kodebarang']) . "</td>
                <td>" . htmlspecialchars($b['namabarang']) . "</td>
                <td align=center>" . htmlspecialchars($b['satuan']) . "</td>
                <td align=right>" . dpbQty($b['jumlah']) . "</td>
                <td>" . htmlspecialchars($b['nopp']) . "</td>
                </tr>";
        }
        if ($n == 0) {
            echo "<tr class=rowcontent><td colspan=6 align=center>Tidak ada rincian barang</td></tr>";
        }
        echo "</tbody></table>";
        break;

    case 'excellist':
        $rows = dpbDataBaris();
        $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
        $kolom = 12;
        $stream = "<table>
            <tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
            <tr><td colspan=" . $kolom . "><b>DAFTAR PENERIMAAN BARANG</b></td></tr>
            <tr><td colspan=" . $kolom . ">" . htmlspecialchars(dpbInfoFilter()) . "</td></tr>
            <tr><td colspan=" . $kolom . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
            <tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
            </table>";
        $stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1>
            <thead><tr>
                <th align=center bgcolor=#CCCCCC>No</th>
                <th align=center bgcolor=#CCCCCC>No. Transaksi</th>
                <th align=center bgcolor=#CCCCCC>Tanggal</th>
                <th align=center bgcolor=#CCCCCC>No. PO/SO</th>
                <th align=center bgcolor=#CCCCCC>Supplier</th>
                <th align=center bgcolor=#CCCCCC>Gudang</th>
                <th align=center bgcolor=#CCCCCC>No. Surat Jalan</th>
                <th align=center bgcolor=#CCCCCC>Kode Barang</th>
                <th align=center bgcolor=#CCCCCC>Nama Barang</th>
                <th align=center bgcolor=#CCCCCC>Satuan</th>
                <th align=center bgcolor=#CCCCCC>Qty</th>
                <th align=center bgcolor=#CCCCCC>Status</th>
            </tr></thead><tbody>";
        $no = 0;
        $daftarTrans = array();
        foreach ($rows as $bar) {
            $no++;
            $daftarTrans[$bar['notransaksi']] = 1;
            $st = dpbStatus($bar);
            $wrn = dpbWarna($st);
            $stream .= "<tr>
                <td align=center>" . $no . "</td>
                <td>" . htmlspecialchars($bar['notransaksi']) . "</td>
                <td align=center>" . tanggalnormal($bar['tanggal']) . "</td>
                <td>" . htmlspecialchars($bar['nopo']) . "</td>
                <td>" . htmlspecialchars(isset($namasupplier[$bar['idsupplier']]) ? trim($namasupplier[$bar['idsupplier']]) : $bar['idsupplier']) . "</td>
                <td>" . htmlspecialchars(isset($optNma[$bar['kodegudang']]) ? $optNma[$bar['kodegudang']] : $bar['kodegudang']) . "</td>
                <td>" . htmlspecialchars($bar['nosj']) . "</td>
                <td>" . htmlspecialchars($bar['kodebarang']) . "</td>
                <td>" . htmlspecialchars($bar['namabarang']) . "</td>
                <td align=center>" . htmlspecialchars($bar['satuan']) . "</td>
                <td align=right>" . dpbQty($bar['jumlah']) . "</td>
                <td align=center bgcolor=" . $wrn[0] . " style='color:" . $wrn[1] . ";font-weight:bold'>" . $st . "</td>
            </tr>";
        }
        if ($no == 0) {
            $stream .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
        } else {
            $stream .= "<tr><td colspan=" . $kolom . "><b>Jumlah penerimaan: " . count($daftarTrans) . " | Jumlah baris barang: " . $no . "</b></td></tr>";
        }
        $stream .= "</tbody></table>";
        $xls = new HtmlExcel();
        $xls->addSheet("DaftarPenerimaan", $stream);
        $xls->headers("DaftarPenerimaanBarang_" . date('YmdHis') . ".xls");
        echo $xls->buildFile();
        exit;

    case 'pdflist':
        $rows = dpbDataBaris();
        $hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
        $infoFilter = dpbInfoFilter();
        if (!class_exists('PDFListDPB')) {
            class PDFListDPB extends FPDF
            {
                public $colW = array();

                function Header()
                {
                    global $hdpt, $infoFilter;
                    $width = $this->w - $this->lMargin - $this->rMargin;
                    if (file_exists($hdpt['logo'])) {
                        $this->Image($hdpt['logo'], $this->lMargin, $this->tMargin, 18);
                    }
                    $this->SetFont('Arial', 'B', 11);
                    $this->SetXY($this->lMargin + 22, $this->tMargin + 2);
                    $this->Cell(160, 6, utf8_decode($hdpt['nama']), 0, 1, 'L');
                    $this->SetY($this->tMargin + 15);
                    $this->SetFont('Arial', 'B', 13);
                    $this->Cell($width, 7, 'DAFTAR PENERIMAAN BARANG', 0, 1, 'C');
                    $this->SetFont('Arial', '', 8);
                    $this->Cell($width, 5, utf8_decode($infoFilter), 0, 1, 'C');
                    $this->Cell($width, 5, utf8_decode('Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s')), 0, 1, 'C');
                    $this->Ln(1);
                    $this->SetFillColor(220, 220, 220);
                    $this->SetFont('Arial', 'B', 8);
                    $judul = array('No', 'No. Transaksi', 'Tanggal', 'No. PO/SO', 'Supplier', 'Gudang', 'Kode Barang', 'Nama Barang', 'Satuan', 'Qty', 'Status');
                    foreach ($judul as $i => $j) {
                        $this->Cell($this->colW[$i], 6, $j, 1, 0, 'C', true);
                    }
                    $this->Ln();
                }

                function Footer()
                {
                    $this->SetY(-12);
                    $this->SetFont('Arial', 'I', 7);
                    $this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
                }
            }
        }
        $pdf = new PDFListDPB('L', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(false, 16);
        $width = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $colFixed = array(7, 34, 17, 36, 44, 34, 19, 41, 14, 15); #semua kolom kecuali Status
        $pdf->colW = array_merge($colFixed, array($width - array_sum($colFixed)));
        $pdf->AddPage();
        $h = 6;
        $no = 0;
        $daftarTrans = array();
        $terakhir = '';
        foreach ($rows as $bar) {
            $no++;
            $daftarTrans[$bar['notransaksi']] = 1;
            if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
                $pdf->AddPage();
                $terakhir = ''; #halaman baru: ulangi data penerimaan di baris pertama
            }
            $c = $pdf->colW;
            #data penerimaan hanya ditulis di baris barang pertama tiap penerimaan
            $barisPertama = ($bar['notransaksi'] != $terakhir);
            $terakhir = $bar['notransaksi'];
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell($c[0], $h, $no, 1, 0, 'C');
            $pdf->Cell($c[1], $h, $barisPertama ? dpbFit($pdf, $bar['notransaksi'], $c[1]) : '', 1, 0, 'L');
            $pdf->Cell($c[2], $h, $barisPertama ? tanggalnormal($bar['tanggal']) : '', 1, 0, 'C');
            $pdf->Cell($c[3], $h, $barisPertama ? dpbFit($pdf, $bar['nopo'], $c[3]) : '', 1, 0, 'L');
            $pdf->Cell($c[4], $h, $barisPertama ? dpbFit($pdf, isset($namasupplier[$bar['idsupplier']]) ? trim($namasupplier[$bar['idsupplier']]) : $bar['idsupplier'], $c[4]) : '', 1, 0, 'L');
            $pdf->Cell($c[5], $h, $barisPertama ? dpbFit($pdf, isset($optNma[$bar['kodegudang']]) ? $optNma[$bar['kodegudang']] : $bar['kodegudang'], $c[5]) : '', 1, 0, 'L');
            $pdf->Cell($c[6], $h, dpbFit($pdf, $bar['kodebarang'], $c[6]), 1, 0, 'L');
            $pdf->Cell($c[7], $h, dpbFit($pdf, $bar['namabarang'], $c[7]), 1, 0, 'L');
            $pdf->Cell($c[8], $h, dpbFit($pdf, $bar['satuan'], $c[8]), 1, 0, 'C');
            $pdf->Cell($c[9], $h, dpbQty($bar['jumlah']), 1, 0, 'R');
            $st = dpbStatus($bar);
            $wrn = dpbWarna($st);
            if ($barisPertama && $wrn[0] != '') {
                sscanf($wrn[0], '#%02x%02x%02x', $br, $bg, $bb);
                sscanf($wrn[1], '#%02x%02x%02x', $fr, $fg, $fb);
                $pdf->SetFillColor($br, $bg, $bb);
                $pdf->SetTextColor($fr, $fg, $fb);
            }
            $pdf->Cell($c[10], $h, $barisPertama ? dpbFit($pdf, $st == 'Sudah diposting' ? 'Diposting' : 'Belum', $c[10]) : '', 1, 1, 'L', $barisPertama && $wrn[0] != '');
            $pdf->SetTextColor(0, 0, 0);
        }
        if ($no == 0) {
            $pdf->Cell(array_sum($pdf->colW), $h, 'Data tidak ditemukan', 1, 1, 'C');
        } else {
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->Cell(array_sum($pdf->colW), $h, 'Jumlah penerimaan: ' . count($daftarTrans) . ' | Jumlah baris barang: ' . $no, 1, 1, 'L');
        }
        $pdf->Output();
        exit;

    default:
        break;
}
?>