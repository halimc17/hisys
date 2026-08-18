<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses = ($_GET['proses'] == '' ? $_POST['proses'] : $_GET['proses']);
$param = $_POST;

$sPeriode = "select distinct * from " . $dbname . ".setup_periodeakuntansi where kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $param['unitId'] . "' and tipe like 'GUDANG%') and periode='" . $param['periodeId'] . "' order by periode asc limit 1";
$rPeriode = fetchData($sPeriode);
$awal = $rPeriode[0]['tanggalmulai'];
$akhir = $rPeriode[0]['tanggalsampai'];


$OPTnamabarang = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
$OPTorganisasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

// gudang dari notransaksi
$kodegudangNTRX = makeOption($dbname, 'log_transaksiht', 'notransaksi,kodegudang');
$tipetransaksiNTRX = makeOption($dbname, 'log_transaksiht', 'notransaksi,tipetransaksi');

$style = "
<style>
.sticky-header {
    position: -webkit-sticky; 
    position: sticky; 
    top: 0; 
    background-color: white;
    z-index: 1000; 
}
.btn-icon {
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 18px;
    color: white;
    transition: 0.3s;
}

.btn-refresh {
    background-color: white;
}
.btn-refresh:hover {
    background-color: aqua;
}


</style>
";
// joki
// AMBIL AKUN PERSEDIAAN
if ($param['akunpersediaan'] != '') {
    $whereakunpersediaan = " and substr(kodebarang,1,3) in (SELECT kode FROM log_5klbarang WHERE noakun = '" . $param['akunpersediaan'] . "') ";
} else {
    $whereakunpersediaan = "";
}

if ($param['akunpersediaan'] != '') {
    $sTrns_akunpersediaan = "SELECT noakun FROM log_5klbarang WHERE noakun = '" . $param['akunpersediaan'] . "' ";
} else {
    $sTrns_akunpersediaan = "SELECT noakun FROM log_5klbarang WHERE noakun like '115%' ";
}

$kamusjenis['0'] = 'Mutasi dalam perjalanan';
$kamusjenis['1'] = 'Penerimaan';
$kamusjenis['2'] = 'Pengembalian pengeluaran';
$kamusjenis['3'] = 'Penerimaan mutasi';
$kamusjenis['4'] = 'Penerimaan Adjustment';
$kamusjenis['5'] = 'Pengeluaran';
$kamusjenis['6'] = 'Pengembalian penerimaan';
$kamusjenis['7'] = 'Pengeluaran mutasi';
$kamusjenis['8'] = 'Pengeluaran Adjustment';


switch ($proses) {
    #=cek gudang divisi harus 0
    case '1':

        if ($param['akunpersediaan'] != '') {
            exit("warning: Pilih Kelompok Barang Seluruhnya...");
        }

        // === TOMBOL PROSES DI PALING ATAS ===
        $tab  = "<button class='mybutton' onclick=\"ProsesSemuaGudangDivisi('" . $param['periodeId'] . "')\">
                Proses Mutasi Ke Gudang Central
             </button><br><br>";

        // === TABLE HEADER ===
        $tab .= "<table class=sortable cellspacing=1 " . $brd . " cellpadding=5 style='width:100%'>
    <thead class=sticky-header>
        <tr class=rowcontent>
            <td align='center' colspan='6'>CEK SALDO GUDANG DIVISI</td>
        </tr>
        <tr class=rowheader style='text-align:center'>
            <th>No.</th>
            <th>Gudang</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Periode</th>
            <th>Saldo</th>
        </tr>
    </thead>
    <tbody>";

        // === QUERY ===
        $str = "select *  
            from `" . $dbname . "`.`log_5saldobulanan` 
            where periode='" . $param['periodeId'] . "' 
            and saldoakhirqty>0 
            and kodegudang in (
                select kodeorganisasi 
                from " . $dbname . ".organisasi 
                where tipe='GUDANGTEMP' 
                and induk='" . $param['unitId'] . "'
            )
            " . $whereakunpersediaan . "
            order by kodegudang, kodebarang";

        $res = fetchdata($str);

        if (count($res) > 0) {

            // $style = 'background-color:red;color:white';
            $no = 0;
            $currentGudang = '';

            foreach ($res as $bar) {

                // === GROUPING PER GUDANG ===
                if ($currentGudang != $bar['kodegudang']) {

                    $currentGudang = $bar['kodegudang'];

                    $tab .= "
                    <tr style='background:aqua;font-weight:bold;'>
                        <td colspan='6'>GUDANG: " . $currentGudang . " - " . $OPTorganisasi[$currentGudang] . "</td>
                    </tr>
                ";
                }

                $no++;

                // === ID UNIK UNTUK QUERYSELECTOR ===
                // getidgudangdivisi_GUDANG_KODEBARANG
                $rowId = "getidgudangdivisi_" . $bar['kodegudang'] . "_" . $bar['kodebarang'];

                // === ROW DATA ===
                $tab .= "
                <tr class=rowcontent style='" . $style . "' id='" . $rowId . "'>
                    <td align='center'>" . $no . "</td>
                    <td align='center'>" . $bar['kodegudang'] . "</td>
                    <td align='center'>" . $bar['kodebarang'] . "</td>
                    <td align='left'>" . $OPTnamabarang[$bar['kodebarang']] . "</td>
                    <td align='center'>" . $bar['periode'] . "</td>
                    <td align='center'>" . $bar['saldoakhirqty'] . "</td>
                </tr>
            ";
            }
        } else {

            // === JIKA SALDO SUDAH 0 ===
            $style = 'background-color:green;color:white';
            $tab .= "
            <tr class=rowcontent style='" . $style . "'>
                <td align='center' colspan='6'>SALDO DIVISI SUDAH 0</td>
            </tr>
        ";
        }

        $tab .= "</tbody></table>";

        echo $tab;
        break;


    #=cek BKM pakai material belum posting
    case '2':
        $tab = '';
        $tab = $style;
        $tab .= "<table class=sortable cellspacing=1 " . $brd . " cellpadding=5 style='width:100%'>
    <thead class=sticky-header>

        <tr class=rowcontent>
            <td align='center' colspan='7'>CEK BLM BELUM POSTING</td>
        </tr>
        <tr class=rowheader style='text-align:center'>
            <th>No.</th>
            <th>" . $_SESSION['lang']['notransaksi'] . "</th>
            <th>" . $_SESSION['lang']['gudang'] . "</th>
            <th>" . $_SESSION['lang']['kodebarang'] . "</th>
            <th>" . $_SESSION['lang']['namabarang'] . "</th>
            <th>" . $_SESSION['lang']['tanggal'] . "</th>
            <th>" . $_SESSION['lang']['jumlah'] . "</th>
        </tr>  
    </thead><tbody>";

        $iBkm = "select * from " . $dbname . ".kebun_pakai_material_vw where jurnal=0 and tanggal like '" . $param['periodeId'] . "%' and"
            . " left(kodegudang,4)='" . $param['unitId'] . "' ";
        $res_cekbkm = fetchdata($iBkm);
        if (count($res_cekbkm) > 0) {

            $style = 'background-color:red;color:white';

            foreach ($res_cekbkm as $bar) :
                $no += 1;
                $tab .= " 
                    <tr class=rowcontent style='" . $style . "'>
                        <td align='center'>" . $no . "</td>
                        <td align='center'>" . $bar['notransaksi'] . "</td>
                        <td align='center'>" . $bar['kodegudang'] . " - " . $OPTorganisasi[$bar['kodegudang']] . "</td>
                        <td align='center'>" . $bar['kodebarang'] . "</td>
                        <td align='center'>" . $OPTnamabarang[$bar['kodebarang']] . "</td>
                        <td align='center'>" . $bar['tanggal'] . "</td>
                        <td align='center'>" . $bar['kwantitas'] . "</td>
                    </tr>
                ";
            endforeach;
        } else {
            $style = 'background-color:green;color:white';

            $tab .= " 
        <tr class=rowcontent style='" . $style . "'>
            <td align='center' colspan='7'>TRANSAKSI BKM PAKAI MATERIAL SUDAH TERPOSTING</td>
        </tr>
    ";
        }
        $tab .= "</tbody>
    </table>";

        echo $tab;

        break;

    #=cek Transaksi Gudang belum posting
    case '3':
        $tab = '';
        $tab = $style;
        $tab .= "<table class=sortable cellspacing=1 " . $brd . " cellpadding=5 style='width:100%'>
    <thead class=sticky-header>

        <tr class=rowcontent>
            <td align='center' colspan='7'>CEK TRANSAKSI GUDANG BELUM POSTING</td>
        </tr>
        <tr class=rowheader style='text-align:center'>
            <th>No.</th>
            <th>" . $_SESSION['lang']['notransaksi'] . "</th>
            <th>" . $_SESSION['lang']['gudang'] . "</th>
            <th>" . $_SESSION['lang']['kodebarang'] . "</th>
            <th>" . $_SESSION['lang']['namabarang'] . "</th>
            <th>" . $_SESSION['lang']['tanggal'] . "</th>
            <th>" . $_SESSION['lang']['jumlah'] . "</th>
        </tr>  
    </thead><tbody>";

        $sTrns = "select * from " . $dbname . ".log_transaksi_vw where left(kodegudang,4)='" . $param['unitId'] . "' and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "'
    and (post=0 or statussaldo=0) ";
        $res_cektransaksigudang = fetchData($sTrns);
        if (count($res_cektransaksigudang) > 0) {

            $style = 'background-color:red;color:white';

            foreach ($res_cektransaksigudang as $bar) :
                $no += 1;
                $tab .= " 
                    <tr class=rowcontent style='" . $style . "'>
                        <td align='center'>" . $no . "</td>
                        <td align='center'>" . $bar['notransaksi'] . "</td>
                        <td align='center'>" . $bar['kodegudang'] . " - " . $OPTorganisasi[$bar['kodegudang']] . "</td>
                        <td align='center'>" . $bar['kodebarang'] . "</td>
                        <td align='center'>" . $OPTnamabarang[$bar['kodebarang']] . "</td>
                        <td align='center'>" . $bar['tanggal'] . "</td>
                        <td align='center'>" . $bar['jumlah'] . "</td>
                    </tr>
                ";
            endforeach;
        } else {
            $style = 'background-color:green;color:white';

            $tab .= " 
        <tr class=rowcontent style='" . $style . "'>
            <td align='center' colspan='7'>TRANSAKSI GUDANG SUDAH TERPOSTING</td>
        </tr>
    ";
        }
        $tab .= "</tbody>
    </table>";

        echo $tab;

        break;

    case '4':
        $tab = '';
        $tab = $style;
        $tab .= "<table class=sortable cellspacing=1 " . $brd . " cellpadding=5 style='width:100%'>
    <thead class=sticky-header>

        <tr class=rowcontent>
            <td align='center' colspan='7'>CEK TRANSAKSI MUTASI BELUM DITERIMAKAN</td>
        </tr>
        <tr class=rowheader style='text-align:center'>
            <th>No.</th>
            <th>" . $_SESSION['lang']['notransaksi'] . "</th>
            <th>" . $_SESSION['lang']['gudang'] . "</th>
            <th>" . $_SESSION['lang']['kodebarang'] . "</th>
            <th>" . $_SESSION['lang']['namabarang'] . "</th>
            <th>" . $_SESSION['lang']['tanggal'] . "</th>
            <th>" . $_SESSION['lang']['jumlah'] . "</th>
        </tr>  
    </thead><tbody>";

        $sTrns = "select * from " . $dbname . ".log_transaksi_vw where tipetransaksi=7 and gudangx like '" . $param['unitId'] . "%' and tanggal>='" . $awal . "' and tanggal<='" . $akhir . "' and (notransaksireferensi='' or notransaksireferensi is null)";
        $res_cektransaksigudang = fetchData($sTrns);
        if (count($res_cektransaksigudang) > 0) {

            $style = 'background-color:red;color:white';

            foreach ($res_cektransaksigudang as $bar) :
                $no += 1;
                $tab .= " 
                    <tr class=rowcontent style='" . $style . "'>
                        <td align='center'>" . $no . "</td>
                        <td align='center'>" . $bar['notransaksi'] . "</td>
                        <td align='center'>" . $bar['kodegudang'] . " - " . $OPTorganisasi[$bar['kodegudang']] . "</td>
                        <td align='center'>" . $bar['kodebarang'] . "</td>
                        <td align='center'>" . $OPTnamabarang[$bar['kodebarang']] . "</td>
                        <td align='center'>" . $bar['tanggal'] . "</td>
                        <td align='center'>" . $bar['jumlah'] . "</td>
                    </tr>
                ";
            endforeach;
        } else {
            $style = 'background-color:green;color:white';

            $tab .= " 
        <tr class=rowcontent style='" . $style . "'>
            <td align='center' colspan='7'>TRANSAKSI MUTASI SUDAH DITERIMAKAN</td>
        </tr>
    ";
        }
        $tab .= "</tbody>
    </table>";

        echo $tab;

        break;

    case '5':
        $tab  = $style;
        $tab .= "
    <table class='sortable' cellspacing='1' {$brd} cellpadding='5' style='width:100%'>
        <thead class='sticky-header'>
            <tr class='rowcontent'>
                <td align='center' colspan='7'><b>CEK SALDO AWAL GUDANG VS AKUNTING VS TRANSAKSI GUDANG</b></td>
            </tr>
            <tr class='rowheader' style='text-align:center'>
                <th>No.</th>
                <th>Keterangan</th>
                <th>Gudang</th>
                <th>Akunting / Transaksi</th>
                <th>Selisih</th>
                <th>Status</th>
                <th>Perbaikan</th>
            </tr>
        </thead>
        <tbody>";

        // fungsi bantu
        function getStatus($selisih)
        {
            if (round($selisih, 2) != 0) {
                return [
                    'icon' => "<td align='center' style='background:red;color:#fff'>&#10007;</td>",
                    'ok'   => false
                ];
            } else {
                return [
                    'icon' => "<td align='center' style='background:green;color:#fff'>&#10003;</td>",
                    'ok'   => true
                ];
            }
        }

        function row($no, $ket, $gudang, $akunting, $selisih, $icon, $aksi = '')
        {
            return "
        <tr class='rowcontent'>
            <td align='center'>{$no}</td>
            <td>{$ket}</td>
            <td align='right'>" . number_format($gudang, 2) . "</td>
            <td align='right'>" . number_format($akunting, 2) . "</td>
            <td align='right'>" . number_format($selisih, 2) . "</td>
            {$icon}
            <td align='center'>{$aksi}</td>
        </tr>";
        }

        $res = fetchData($sTrns_akunpersediaan);

        foreach ($res as $bar) {

            // ambil kelompok akun
            $akun = fetchData("
            SELECT kode,kelompok 
            FROM log_5klbarang 
            WHERE noakun='{$bar['noakun']}' LIMIT 1
        ")[0];

            $kode_akun = $akun['kode'];
            $nama_kelompok = $akun['kelompok'];

            $tab .= "
        <tr class='rowcontent'>
            <td colspan='7' style='font-weight:bold'>
                {$kode_akun} - {$nama_kelompok} ({$bar['noakun']})
            </td>
        </tr>";

            // ================= SALDO BULANAN =================
            $data = fetchData("
            SELECT 
                SUM(nilaisaldoakhir) rpakhir,
                SUM(qtymasukxharga) msk,
                SUM(qtykeluarxharga) klr,
                SUM(nilaisaldoawal) awal
            FROM log_5saldobulanan
            WHERE periode='{$param['periodeId']}'
            AND LEFT(kodebarang,3) IN (
                SELECT kode FROM log_5klbarang WHERE noakun='{$bar['noakun']}'
            )
            AND kodegudang LIKE '{$param['unitId']}%'
        ")[0];

            $awal_gdg = $data['awal'];
            $msk_gdg  = $data['msk'];
            $klr_gdg  = $data['klr'];

            // ================= AKUNTING =================
            $periode = str_replace('-', '', $param['periodeId']);
            $bulan   = substr($param['periodeId'], 5, 2);

            $keu = fetchData("
            SELECT 
                awal{$bulan} awal,
                debet{$bulan} debet,
                kredit{$bulan} kredit
            FROM keu_saldobulanan
            WHERE kodeorg='{$param['unitId']}'
            AND periode='{$periode}'
            AND noakun='{$bar['noakun']}'
        ")[0];

            $awal_akun = $keu['awal'];

            // ================= 1. SALDO AWAL =================
            $selisih = $awal_gdg - $awal_akun;
            $st = getStatus($selisih);

            $aksi = !$st['ok'] ? "
            <button class='mybutton'
                onclick=\"prosesSaldoAwal('{$bar['noakun']}','{$awal_gdg}','{$param['periodeId']}','{$param['unitId']}')\">
                Proses
            </button>"
                : "<span style='color:green'>OK</span>";

            $tab .= row(1, 'Saldo Awal', $awal_gdg, $awal_akun, $selisih, $st['icon'], $aksi);

            // ================= 2. MASUK =================
            $masuk_trx = fetchData("
            SELECT SUM(jumlah*hargasatuan) total
            FROM log_transaksi_vw
            WHERE kodegudang LIKE '{$param['unitId']}%'
            AND tanggal LIKE '{$param['periodeId']}%'
            AND tipetransaksi <= '4'
            AND LEFT(kodebarang,3) IN (
                SELECT kode FROM log_5klbarang WHERE noakun='{$bar['noakun']}'
            )
        ")[0]['total'];

            $selisih = $msk_gdg - $masuk_trx;
            $st = getStatus($selisih);

            $tab .= row(2, 'Masuk x Harga', $msk_gdg, $masuk_trx, $selisih, $st['icon']);

            // ================= 3. KELUAR =================
            $keluar_trx = fetchData("
            SELECT SUM(jumlah*hargarata) total
            FROM log_transaksi_vw
            WHERE kodegudang LIKE '{$param['unitId']}%'
            AND tanggal LIKE '{$param['periodeId']}%'
            AND tipetransaksi >= '5'
            AND LEFT(kodebarang,3) IN (
                SELECT kode FROM log_5klbarang WHERE noakun='{$bar['noakun']}'
            )
        ")[0]['total'];

            $selisih = $klr_gdg - $keluar_trx;
            $st = getStatus($selisih);

            $tab .= row(3, 'Keluar x Harga', $klr_gdg, $keluar_trx, $selisih, $st['icon']);

            // ================= 4. TOTAL =================
            $total_gdg = $awal_gdg + $msk_gdg - $klr_gdg;
            $total_akun = $awal_akun + $masuk_trx - $keluar_trx;

            $selisih = $total_gdg - $total_akun;
            $st = getStatus($selisih);

            $tab .= row(4, 'Total (1+2-3)', $total_gdg, $total_akun, $selisih, $st['icon']);
        }

        $tab .= "</tbody></table>";

        echo $tab;
        break;


    case '6':

        $tab = '';
        $tab = $style;
        $tab .= "<table class=sortable cellspacing=1 " . $brd . " cellpadding=5 style='width:100%'>
    <thead class=sticky-header>

        <tr class=rowcontent>
            <td align='center' colspan='8'>CEK TRANSAKSI MASUK VS JURNAL</td>
        </tr>
        <tr class=rowheader style='text-align:center'>
            <th rowspan='2'>No.</th>
            <th rowspan='2'>SUMBER TRANSAKSI</th>
            <th rowspan='2'>NO. TRANSAKSI GUDANG</th>
            <th rowspan='2'>NILAI GUDANG</th>
            <th rowspan='2'>NILAI JURNAL</th>
            <th rowspan='2'>SELISIH</th>
            <th rowspan='2'>STATUS</th>
            <th rowspan='2'>Perbaikan</th>
        </tr>  
    </thead><tbody>";

        // AMBIL AKUN PERSEDIAAN
        $res_cekakunpersediaan = fetchData($sTrns_akunpersediaan);
        $arrTransaksiMasuk = array();
        foreach ($res_cekakunpersediaan as $bar) :

            // AMBIL JURNAL MASUK
            $sTrns_1 = "SELECT noreferensi,SUM(jumlah) AS itung
        FROM `keu_jurnaldt_vw`
        WHERE noakun='" . $bar['noakun'] . "' AND nojurnal like '%INVM%' AND tanggal like '" . $param['periodeId'] . "%' and kodeorg='" . $param['unitId'] . "' GROUP BY noreferensi";
            $res_masukjurnal = fetchData($sTrns_1);
            foreach ($res_masukjurnal as $val) {
                $arrTransaksiMasuk[$bar['noakun']][$val['noreferensi']]['nilaijurnal'] = $val['itung'];
            }

            // AMBIL MASUK DARI GUDANG (tipe transaksi 1)
            $sTrns_2 = "SELECT notransaksi,SUM(jumlah * hargasatuan) AS itung FROM log_transaksi_vw WHERE tipetransaksi='1' AND LEFT(kodebarang,3) IN (SELECT kode FROM log_5klbarang WHERE noakun='" . $bar['noakun'] . "')  
        AND tanggal like '" . $param['periodeId'] . "%'  AND kodegudang LIKE '" . $param['unitId'] . "%' GROUP BY notransaksi";
            $res_masukgudang_1 = fetchData($sTrns_2);
            foreach ($res_masukgudang_1 as $val) {
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['nilaitransaksi'] = $val['itung'];
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['notransaksigudang'] = $val['notransaksi'];
            }

            // AMBIL MASUK DARI GUDANG (tipe transaksi 2)
            $sTrns_3 = "SELECT notransaksi,SUM(jumlah * hargasatuan) AS itung FROM log_transaksi_vw WHERE tipetransaksi='2' AND notransaksi NOT LIKE '%GI%'  
        AND LEFT(kodebarang,3) IN (SELECT kode FROM log_5klbarang WHERE noakun='" . $bar['noakun'] . "') AND tanggal LIKE '" . $param['periodeId'] . "%' AND kodegudang LIKE '" . $param['unitId'] . "%' GROUP BY notransaksi";
            $res_masukgudang_2 = fetchData($sTrns_3);
            foreach ($res_masukgudang_2 as $val) {
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['nilaitransaksi'] = $val['itung'];
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['notransaksigudang'] = $val['notransaksi'];
            }

            // AMBIL MASUK DARI GUDANG (tipe transaksi 3)
            $sTrns_4 = "SELECT notransaksi,SUM(jumlah * hargasatuan) AS itung FROM log_transaksi_vw WHERE tipetransaksi='3' AND LEFT(gudangx,4)<>LEFT(kodegudang,4) AND notransaksi NOT LIKE '%GI%'  
        AND LEFT(kodebarang,3) IN (SELECT kode FROM log_5klbarang WHERE noakun='" . $bar['noakun'] . "') AND tanggal LIKE '" . $param['periodeId'] . "%' AND kodegudang LIKE '" . $param['unitId'] . "%' GROUP BY notransaksi";
            $res_masukgudang_3 = fetchData($sTrns_4);
            foreach ($res_masukgudang_3 as $val) {
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['nilaitransaksi'] = $val['itung'];
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['notransaksigudang'] = $val['notransaksi'];
            }

            // AMBIL MASUK DARI GUDANG (tipe transaksi 4)
            $sTrns_5 = "SELECT notransaksi,SUM(jumlah * hargasatuan) AS itung FROM log_transaksi_vw WHERE tipetransaksi='4' AND notransaksi NOT LIKE '%GI%'  
        AND LEFT(kodebarang,3) IN (SELECT kode FROM log_5klbarang WHERE noakun='" . $bar['noakun'] . "') AND tanggal LIKE '" . $param['periodeId'] . "%' AND kodegudang LIKE '" . $param['unitId'] . "%' GROUP BY notransaksi";
            $res_masukgudang_4 = fetchData($sTrns_5);
            foreach ($res_masukgudang_4 as $val) {
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['nilaitransaksi'] = $val['itung'];
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['notransaksigudang'] = $val['notransaksi'];
            }

        endforeach;


        foreach ($arrTransaksiMasuk as $val1 => $val2) {
            // AMBIL DATA BERDASARKAN AKUN
            $sTrns_AKUN = "SELECT kode,kelompok FROM `log_5klbarang` WHERE noakun = '" . $val1 . "' ";
            $res_AKUN = fetchData($sTrns_AKUN);
            $kode_akun = $res_AKUN[0]['kode'];
            $nama_kelompok_akun = $res_AKUN[0]['kelompok'];

            $tab .= "<tr class=rowcontent>
            <td align='left' colspan='8' style='font-weight:bold'> " . $kode_akun . " - " . $nama_kelompok_akun . " (" . $val1 . ")</td>
            </tr>";

            $no = 0;
            foreach ($val2 as $val3 => $val4) {
                $no += 1;

                $total_no6_selisih = $val4['nilaitransaksi'] - $val4['nilaijurnal'];

                if (number_format($total_no6_selisih, 2) != 0 || number_format($total_no6_selisih, 2) != '0') {
                    // Button untuk perbaikan (refresh)
                    $Perbaikan = "<td align='center'>
                        <button class='btn-icon btn-refresh' onclick=\"previewPosting(" . $tipetransaksiNTRX[$val4['notransaksigudang']] . ",'" . $val4['notransaksigudang'] . "','" . $kodegudangNTRX[$val4['notransaksigudang']] . "','" . $_SESSION['gudang'][$kodegudangNTRX[$val4['notransaksigudang']]]['start'] . "','" . $_SESSION['gudang'][$kodegudangNTRX[$val4['notransaksigudang']]]['end'] . "',event);\">&#128260;</button>
                    </td>";

                    $icon = "<td align='center' style='background-color:red'><span class='icon' style='font-size:20px;color:white;'>&#10007;</span></td>";
                } else {
                    $icon = "<td align='center' style='background-color:green'><span class='icon' style='font-size:20px;color:white;'>&#10003;</span></td>";
                    $Perbaikan = "<td align='center'></td>";
                }

                $tab .= "<tr class=rowcontent>
                        <td align='center'> " . $no . "</td>
                        <td align='center'> " . $val3 . "</td>
                        <td align='center'> " . $val4['notransaksigudang'] . "</td>
                        <td align='center'> " . number_format($val4['nilaitransaksi'], 2) . "</td>
                        <td align='center'> " . number_format($val4['nilaijurnal'], 2) . "</td>
                        <td align='center'> " . number_format($total_no6_selisih, 2) . "</td>
                        " . $icon . "
                        " . $Perbaikan . "
                     </tr>";
            }
        }

        echo $tab;


        break;


    case '7':
        $tab = '';
        $tab = $style;
        $tab .= "<table class=sortable cellspacing=1 " . $brd . " cellpadding=5 style='width:100%'>
    <thead class=sticky-header>

        <tr class=rowcontent>
            <td align='center' colspan='8'>CEK TRANSAKSI KELUAR VS JURNAL</td>
        </tr>
        <tr class=rowheader style='text-align:center'>
            <th rowspan='2'>No.</th>
            <th rowspan='2'>SUMBER TRANSAKSI</th>
            <th rowspan='2'>NO. TRANSAKSI GUDANG</th>
            <th rowspan='2'>NILAI GUDANG</th>
            <th rowspan='2'>NILAI JURNAL</th>
            <th rowspan='2'>SELISIH</th>
            <th rowspan='2'>STATUS</th>
            <th rowspan='2'>PERBAIKAN</th>
        </tr>  
    </thead><tbody>";

        // AMBIL AKUN PERSEDIAAN
        $res_cekakunpersediaan = fetchData($sTrns_akunpersediaan);
        $arrTransaksiMasuk = array();
        foreach ($res_cekakunpersediaan as $bar) :

            // AMBIL JURNAL KELUAR
            $sTrns_1 = "select noreferensi,sum(jumlah) as itung
        FROM `keu_jurnaldt_vw`
        WHERE noakun='" . $bar['noakun'] . "' and nojurnal like '%INVK%' and tanggal like '" . $param['periodeId'] . "%' and kodeorg like '" . $param['unitId'] . "%'  group by noreferensi";
            $res_keluarjurnal = fetchData($sTrns_1);
            foreach ($res_keluarjurnal as $val) {
                $arrTransaksiMasuk[$bar['noakun']][$val['noreferensi']]['nilaijurnal'] = $val['itung'] * -1;
            }


            // AMBIL PEMAKAIN BUKAN DARI GUDANG
            $sTrns_2 = "select notransaksi,notransaksireferensi,sum(jumlah*hargarata) as itung from log_transaksi_vw where tipetransaksi=5 and notransaksireferensi is not null 
        and left(kodebarang,3) in (select kode from log_5klbarang where noakun='" . $bar['noakun'] . "')  and tanggal like '" . $param['periodeId'] . "%'  and kodegudang like '" . $param['unitId'] . "%' group by notransaksireferensi";
            $res_pemakainnongudang = fetchData($sTrns_2);
            foreach ($res_pemakainnongudang as $val) {
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksireferensi']]['nilaitransaksi'] = $val['itung'];
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksireferensi']]['notransaksigudang'] = $val['notransaksi'];
            }

            // AMBIL PEMAKAIN DARI GUDANG
            if (getNamaOrg($param['unitId'], 'tipe') == 'KEBUN') {
                // $notlikeM = " and notransaksi not like '%M%' ";
                $notlikeM = " and keterangan not like '%Material %' ";
            } else {
                $notlikeM = "";
            }
            $sTrns_3 = "select notransaksi,sum(jumlah*hargarata) as itung from log_transaksi_vw where tipetransaksi=5 " . $notlikeM . " and
        left(kodebarang,3) in (select kode from log_5klbarang where noakun='" . $bar['noakun'] . "') and tanggal like '" . $param['periodeId'] . "%' and kodegudang like '" . $param['unitId'] . "%' group by notransaksi";
            $res_pemakaingudang = fetchData($sTrns_3);
            foreach ($res_pemakaingudang as $val) {
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['nilaitransaksi'] = $val['itung'];
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['notransaksigudang'] = $val['notransaksi'];
            }

            // AMBIL MUTASI KELUAR
            $sTrns_4 = "select notransaksi,sum(jumlah*hargarata) as itung from log_transaksi_vw where tipetransaksi=7 and left(gudangx,4)<>left(kodegudang,4) " . $notlikeM . "
        and left(kodebarang,3) in (select kode from log_5klbarang where noakun='" . $bar['noakun'] . "') and tanggal like '" . $param['periodeId'] . "%' and kodegudang like '" . $param['unitId'] . "%' group by notransaksi";
            $res_mutasigudang = fetchData($sTrns_4);
            foreach ($res_mutasigudang as $val) {
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['nilaitransaksi'] = $val['itung'];
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['notransaksigudang'] = $val['notransaksi'];
            }

            // ADJ STOK
            $sTrns_3 = "select notransaksi,sum(jumlah*hargarata) as itung from log_transaksi_vw where tipetransaksi=8 " . $notlikeM . " and 
        left(kodebarang,3) in (select kode from log_5klbarang where noakun='" . $bar['noakun'] . "') and tanggal like '" . $param['periodeId'] . "%' and kodegudang like '" . $param['unitId'] . "%' group by notransaksi";
            $res_pemakaingudang = fetchData($sTrns_3);
            foreach ($res_pemakaingudang as $val) {
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['nilaitransaksi'] = $val['itung'];
                $arrTransaksiMasuk[$bar['noakun']][$val['notransaksi']]['notransaksigudang'] = $val['notransaksi'];
            }

        endforeach;


        foreach ($arrTransaksiMasuk as $val1 => $val2) {
            // AMBIL DATA BERDASARKAN AKUN
            $sTrns_AKUN = "SELECT kode,kelompok FROM `log_5klbarang` WHERE noakun = '" . $val1 . "' ";
            $res_AKUN = fetchData($sTrns_AKUN);
            $kode_akun = $res_AKUN[0]['kode'];
            $nama_kelompok_akun = $res_AKUN[0]['kelompok'];

            $tab .= "<tr class=rowcontent>
            <td align='left' colspan='8' style='font-weight:bold'> " . $kode_akun . " - " . $nama_kelompok_akun . " (" . $val1 . ")</td>
            </tr>";

            $no = 0;
            foreach ($val2 as $val3 => $val4) {
                $no += 1;

                $total_no7_selisih = $val4['nilaitransaksi'] - $val4['nilaijurnal'];

                if (number_format($total_no7_selisih, 2) != 0 || number_format($total_no7_selisih, 2) != '0') {
                    // Button untuk perbaikan (refresh)
                    $Perbaikan = "<td align='center'>
                        <button class='btn-icon btn-refresh' onclick=\"previewPosting(" . $tipetransaksiNTRX[$val4['notransaksigudang']] . ",'" . $val4['notransaksigudang'] . "','" . $kodegudangNTRX[$val4['notransaksigudang']] . "','" . $_SESSION['gudang'][$kodegudangNTRX[$val4['notransaksigudang']]]['start'] . "','" . $_SESSION['gudang'][$kodegudangNTRX[$val4['notransaksigudang']]]['end'] . "',event);\">&#128260;</button>
                    </td>";

                    $icon = "<td align='center' style='background-color:red'><span class='icon' style='font-size:20px;color:white;'>&#10007;</span></td>";
                } else {
                    $icon = "<td align='center' style='background-color:green'><span class='icon' style='font-size:20px;color:white;'>&#10003;</span></td>";
                    $Perbaikan = "<td align='center'></td>";
                }

                $tab .= "<tr class=rowcontent>
                        <td align='center'> " . $no . "</td>
                        <td align='center'> " . $val3 . "</td>
                        <td align='center'> " . $val4['notransaksigudang'] . "</td>
                        <td align='center'> " . number_format($val4['nilaitransaksi'], 2) . "</td>
                        <td align='center'> " . number_format($val4['nilaijurnal'], 2) . "</td>
                        <td align='center'> " . number_format($total_no7_selisih, 2) . "</td>
                        " . $icon . "
                        " . $Perbaikan . "
                     </tr>";
            }
        }

        echo $tab;

        break;


    case '8':
        $tab = '';
        $tab = $style;

        $tab .= "<table class=sortable cellspacing=1 " . $brd . " cellpadding=5 style='width:100%'>
    <thead class=sticky-header>
        <tr class=rowcontent>
            <td align='center' colspan='13'>CEK HARGARATA BARANG BELUM SESUAI GUDANG " . $param['divisiId'] . "</td>
        </tr>
        <tr class=rowheader style='text-align:center'>
            <th>No.</th>
            <th>Gudang</th>
            <th>Kode Barang</th>
            <th>Nama Barang</th>
            <th>Periode</th>
            <th>Saldo Qty Awal</th>
            <th>Nilai Saldo Awal</th>
            <th>Qty Masuk x Harga</th>
            <th>Qty Keluar x Harga Rata</th>
            <th>Nilai Saldo Akhir (Perhitungan)</th>
            <th>Selisih</th>
            <th>Status</th>
            <th>Info <br> (Cek Laporan Persediaan Fisik)</th>
        </tr>  
    </thead><tbody>";

        $str = "SELECT * FROM `" . $dbname . "`.`log_5saldobulanan` 
        WHERE periode='" . $param['periodeId'] . "' 
        AND kodegudang='" . $param['divisiId'] . "'  " . $whereakunpersediaan;

        $res_cekdivisi = fetchdata($str);

        if (count($res_cekdivisi) > 0) {
            foreach ($res_cekdivisi as $bar) {

                // ================= SALDO AWAL =================
                $saldoAwalQty   = (float)$bar['saldoawalqty'];
                $nilaiSaldoAwal = (float)$bar['nilaisaldoawal'];
                $hargaRataBenar = ($saldoAwalQty > 0 ? (float)$bar['hargaratasaldoawal'] : 0);

                $totalQty   = $saldoAwalQty;
                $totalNilai = $nilaiSaldoAwal;

                $sumMasuk  = 0;
                $sumKeluar = 0;

                $infoLog = [];

                // ================= AMBIL TRANSAKSI =================
                $sqlTrx = "SELECT * FROM log_transaksi_vw
                WHERE kodebarang='" . $bar['kodebarang'] . "'
                AND tanggal LIKE '" . $param['periodeId'] . "%'
                AND kodegudang='" . $param['divisiId'] . "'
                ORDER BY tanggal ASC, waktutransaksi ASC";

                $listTrx = fetchData($sqlTrx);
                $adaErData = false;
                // ================= LOOP TRANSAKSI =================
                foreach ($listTrx as $row) {

                    $jumlah      = (float)$row['jumlah'];
                    $hargasatuan = (float)$row['hargasatuan'];

                    // ===== MASUK =====
                    if (in_array($row['tipetransaksi'], ['1', '2', '3', '4'])) {

                        $nilaiMasuk = $jumlah * $hargasatuan;

                        $totalNilai += $nilaiMasuk;
                        $totalQty   += $jumlah;

                        if ($totalQty > 0) {
                            $hargaRataBenar = $totalNilai / $totalQty;
                            // $hargaRataBenar = ($totalQty > 0 ? $totalNilai / $totalQty : 0);
                        }

                        $sumMasuk += $nilaiMasuk;
                    } else {

                        // ===== KELUAR =====

                        // ERROR: qty sudah minus sebelum transaksi
                        if ($totalQty < 0) {
                            $infoLog[] = "QTY SUDAH MINUS sebelum " . $row['notransaksi'];
                            continue;
                        }

                        // WARNING: harga rata nol
                        if ($hargaRataBenar <= 0) {
                            $infoLog[] = "Harga=0 atau tanggal tidak sesuai di " . $row['notransaksi'];
                            continue;
                        }

                        $nilaiKeluar = $jumlah * $hargaRataBenar;

                        $sumKeluar += $nilaiKeluar;

                        $totalQty -= $jumlah;

                        $toleranceqty = 0.0005;

                        if ($totalQty < -$toleranceqty) {
                            $infoLog[] = "QTY MINUS setelah " . $row['notransaksi'];
                        }

                        // INFO: stok habis normal
                        if ($totalQty == 0) {
                            $infoLog[] = "INFO: STOK HABIS di " . $row['notransaksi'];
                        }

                        $totalNilai = $hargaRataBenar * $totalQty;
                    }
                }

                // ================= HITUNG AKHIR =================
                $nilaiSaldoHitung = ($nilaiSaldoAwal + $sumMasuk) - $sumKeluar;
                $selisih = (float)$bar['nilaisaldoakhir'] - $nilaiSaldoHitung;

                $tolerance = 10;

                if (abs($selisih) > $tolerance) {
                    $status = "<td align='center' style='background-color:red'><span style='font-size:20px;color:white;'>&#10007;</span></td>";
                    $styleRow = "background-color:red;color:white";
                } else {
                    $status = "<td align='center' style='background-color:green'><span style='font-size:20px;color:white;'>&#10003;</span></td>";
                    $styleRow = "";
                }

                $infoText = (count($infoLog) > 0)
                    ? implode("<br>", array_unique($infoLog))
                    : "OK";

                if ($infoText != "OK") {
                    $adaErrorData = true;
                }
                $no++;

                $tab .= "<tr class=rowcontent style='" . $styleRow . "'>
                <td align='center'>" . $no . "</td>
                <td align='center'>" . $bar['kodegudang'] . " - " . $OPTorganisasi[$bar['kodegudang']] . "</td>
                <td align='center'>" . $bar['kodebarang'] . "</td>
                <td align='center'>" . $OPTnamabarang[$bar['kodebarang']] . "</td>
                <td align='center'>" . $bar['periode'] . "</td>
                <td align='right'>" . number_format($saldoAwalQty, 2) . "</td>
                <td align='right'>" . number_format($nilaiSaldoAwal, 2) . "</td>
                <td align='right'>" . number_format($sumMasuk, 2) . "</td>
                <td align='right'>" . number_format($sumKeluar, 2) . "</td>
                <td align='right'>" . number_format($nilaiSaldoHitung, 2) . "</td>
                <td align='right' style='font-weight:bold'>" . number_format($selisih, 2) . "</td>
                " . $status . "
                <td style='font-size:11px'>" . $infoText . "</td>
            </tr>";
            }
        }

        // ================= VALIDASI =================
        $infoGagal = cekGudangPostingAll($param['divisiId'], $param['periodeId']);

        if ($infoGagal != "" || $adaErrorData) {
            $tab .= "<p style='background-color:red;padding:10px;color:yellow;font-weight:bold;'>
                Tidak Bisa Proses Karena Masih Terdapat Masalah Data / Info Di Atas!!!
            </p>";

            $tab .= "<button class=mybutton onclick=PerbaikiSaldoBulanan('" . $param['divisiId'] . "','" . $param['periodeId'] . "');>
                " . $_SESSION['lang']['proses'] . "
            </button>";
        } else {
            $tab .= "<button class=mybutton onclick=PerbaikiSaldoBulanan('" . $param['divisiId'] . "','" . $param['periodeId'] . "');>
                " . $_SESSION['lang']['proses'] . "
            </button>";
        }

        $tab .= "</tbody></table>";

        echo $tab;
        break;


    case '8.1':
        //=============================
        /* * ************************************************************
        * [START] Rekalkulasi harga ******************
        * ************************************************************ */
        $tab = '';
        $tab = $style;
        $tab .= "<table class=sortable cellspacing=1 " . $brd . " cellpadding=5 style='width:100%'>
    <thead class=sticky-header>

        <tr class=rowcontent>
            <td align='center' colspan='13'>CEK HARGARATA</td>
        </tr>
        <tr class=rowheader style='text-align:center'>
            <th rowspan='2'>No.</th>
            <th rowspan='2'>Tipe TRANSAKSI</th>
            <th rowspan='2'>Tanggal TRANSAKSI</th>
            <th rowspan='2'>SUMBER TRANSAKSI</th>
            <th rowspan='2'>NO. TRANSAKSI GUDANG</th>
            <th rowspan='2'>Blok</th>
            <th rowspan='2'>Kegiatan</th>
            <th rowspan='2'>Jumlah</th>
            <th rowspan='2'>Harga Satuan</th>
            <th rowspan='2'>Harga Rata</th>
            <th rowspan='2'>Total</th>
            <th rowspan='2'>Harga Rata Seharusnya</th>
            <th rowspan='2'>STATUS <br> (Toleransi 1rp per trx)</th>
        </tr>  
    </thead><tbody>";

        // AMBIL AKUN PERSEDIAAN
        $sTrns_transaksivw = "SELECT * FROM log_transaksi_vw 
        WHERE kodebarang = '" . $param['barangId'] . "' 
        AND tanggal LIKE '" . $param['periodeId'] . "%' 
        AND kodegudang = '" . $param['divisiId'] . "' 
        ORDER BY tanggal ASC, FIELD(tipetransaksi, '1','2','3','4','5','6','7','8')";
        $res_cektransaksivw = fetchData($sTrns_transaksivw);


        // cek 5 saldobulanan
        $str = "select * from " . $dbname . ".log_5saldobulanan where periode='" . $param['periodeId'] . "' and kodegudang='" . $param['divisiId'] . "' and kodebarang='" . $param['barangId'] . "'";
        $res = fetchdata($str);
        $nilaisaldoawal = $res[0]['nilaisaldoawal'];
        $saldoawalqty = $res[0]['saldoawalqty'];
        $hargaratasaldoawal = $res[0]['hargaratasaldoawal'];

        $tab .= "<tr class=rowcontent>
            <td align='center' colspan='2'>Saldo Awal</td>
            <td align='center'>" . $param['periodeId'] . "</td>
            <td align='center'>-</td>
            <td align='center'>-</td>
            <td align='center'>-</td>
            <td align='center'>-</td>
            <td align='center'>" . $saldoawalqty . "</td>
            <td align='center'>" . $hargaratasaldoawal . "</td>
            <td align='center'>-</td>
            <td align='center'>" . $saldoakhirqty . "</td>
            <td align='center'>-</td>
            <td align='center'>-</td>
            </tr>";
        // $totalNilai = 0;
        // $totalQty = 0;
        // $sumHargaSatuan = 0;
        // $sumHargaRata = 0;
        // $sumHargaRataBenar = 0;
        // $hargaRataBenar = 0; // inisialisasi awal
        // $totalqtymasuk = 0;
        // $totalqtykeluar = 0;

        $totalNilai = $nilaisaldoawal;
        $totalQty   = $saldoawalqty;
        $hargaRataBenar = ($saldoawalqty > 0 ? $hargaratasaldoawal : 0);

        $sumHargaSatuan = 0;
        $sumHargaRata = 0;
        $sumHargaRataBenar = 0;
        $totalqtymasuk = $saldoawalqty;
        $totalqtykeluar = 0;

        $proses = 1;
        foreach ($res_cektransaksivw as $bar) {
            $no++;

            $jumlah = $bar['jumlah'];
            $hargasatuan = $bar['hargasatuan'];
            $hargarata = $bar['hargarata'];
            $hartot = $bar['hartot'];

            $hargaRataCheck = "-";
            $icon = "";
            $Hargaa = "";

            if (in_array($bar['tipetransaksi'], ['1', '2', '3', '4'])) {
                // Masuk
                if ($totalQty == 0) {
                    // Stok kosong, harga rata = harga masuk
                    $hargaRataBenar = $hargasatuan;
                    $totalNilai = $jumlah * $hargasatuan;
                    $totalQty = $jumlah;
                } else {
                    // Stok ada, hitung ulang harga rata-rata
                    $totalNilai += $jumlah * $hargasatuan;
                    $totalQty += $jumlah;
                    $hargaRataBenar = $totalNilai / $totalQty;
                }

                $Hargaa = "
                        <td align='right' id='hargarataawal_{$no}'>" . $hargasatuan . "</td>
                        <td align='right'></td>
                    ";

                $sumHargaSatuan += $jumlah * $hargasatuan;

                $totalqtymasuk += $jumlah;

                $icon = "<td align='center' style='background-color:green;color:white !important;font-weight:bold'>TRANSAKSI MASUK</td>";
            } elseif (in_array($bar['tipetransaksi'], ['5', '6', '7', '8'])) {
                // Keluar
                $Hargaa = "
                        <td align='right'></td>
                        <td align='right' id='hargarataawal_{$no}'>" . $hargarata . "</td>
                    ";

                $sumHargaRata += $hargarata * $jumlah;
                $sumHargaRataBenar += $hargaRataBenar * $jumlah;

                $hargaRataCheck = $hargaRataBenar;
                $tolerance = 1;

                if (abs($hargarata - $hargaRataBenar) > $tolerance) {
                    $icon = "<td align='center' style='background-color:red'><span class='icon' style='font-size:20px;color:white;'>&#10007;</span></td>";
                } else {
                    $icon = "<td align='center' style='background-color:green'><span class='icon' style='font-size:20px;color:white;'>&#10003;</span></td>";
                }

                // Update stok keluar
                $totalQty -= $jumlah;
                $totalNilai = $hargaRataBenar * $totalQty;

                if ($totalQty <= 0) {
                    $totalQty = 0;
                    $totalNilai = 0;
                }
                $totalqtykeluar += $jumlah;
            }

            if ($hartot == '0') {
                $hartot = " <span style='color:red;font-weight:bold;text-align:center !important'> Belum Posting </span> ";
                $proses = 0;
            }

            if ((is_numeric($hargaRataCheck) ? $hargaRataCheck : '-') == '0') {
                $hartot = " <span style='color:red;font-weight:bold;text-align:center !important'> Minus!!! </span> ";
                $proses = 0;
            }


            $tab .= "<tr class=rowcontent>
                    <td align='center' id='baris{$no}'>{$no}</td>
                    <td align='center'>{$kamusjenis[$bar['tipetransaksi']]}</td>
                    <td align='center'>{$bar['tanggal']}</td>
                    <td align='center' id='notransaksireferensi_{$no}'>{$bar['notransaksireferensi']}</td>
                    <td align='center' id='notransaksi_{$no}'>{$bar['notransaksi']}</td>
                    <td align='center' id='kodeblok_{$no}'>{$bar['kodeblok']}</td>
                    <td align='center' id='kodekegiatan_{$no}'>{$bar['kodekegiatan']}</td>
                    <td align='right' id='jumlah_{$no}'>" . $jumlah . "</td>
                    " . $Hargaa . "
                    <td align='right'>" . $hartot . "</td>
                    <td align='right' id='hargaratabenar_{$no}' style='background-color:aqua;font-weight:bold'>" . (is_numeric($hargaRataCheck) ? $hargaRataCheck : '-') . "</td>
                    " . $icon . "
                </tr>";
        }

        if ($totalqtymasuk >= $totalqtykeluar) {
            if ($proses == 0) {
                $tab .= "<p style='background-color:red;padding:10px;color:yellow;font-weight:bold;'>ADA TRANSAKSI YANG BELUM DI POSTING ATAU MINUS!!!</p>";
            } else {
                $tab .= "<button class=mybutton onclick=saveAllHarga('1','1','" . $no . "');>" . $_SESSION['lang']['proses'] . "</button>";
            }
        } else {
            $tab .= "<p style='background-color:red;padding:10px;color:yellow;font-weight:bold;'>TIDAK BISA PROSES KARENA : TOTAL QTY MASUK " . $totalqtymasuk . " LEBIH KECIL DARI TOTAL QTY KELUAR " . $totalqtykeluar . " </p>";
        }



        // TOTAL
        $tab .= "<tr class=rowcontent style='font-weight:bold;background-color:lightgrey'>
            <td colspan='8' align='center'>Harga * Jumlah</td>
            <td align='right'>" . $sumHargaSatuan . "</td>
            <td align='right'>" . $sumHargaRata . "</td>
            <td colspan='1'></td>
            <td colspan='1' align='right'>" . $sumHargaRataBenar . "</td>
            <td colspan='2'></td>
        </tr>";
        $tab .= "<tr class=rowcontent style='font-weight:bold;background-color:green;color:white'>
            <td colspan='8' align='center'>Nilai Saldo Awal</td>
            <td align='right'>" . $nilaisaldoawal . "</td>
            <td colspan='4' align='center'></td>
        </tr>";
        $tab .= "<tr class=rowcontent style='font-weight:bold;background-color:green;color:white'>
            <td colspan='8' align='center'>Qty Masuk x Harga (Perbaiki)</td>
            <td align='right' id='masukxharga_perbaiki'>" . $sumHargaSatuan . "</td>
            <td colspan='4' align='center'></td>
        </tr>";
        $tab .= "<tr class=rowcontent style='font-weight:bold;background-color:green;color:white'>
            <td colspan='8' align='center'>Qty Keluar x Harga (Perbaiki)</td>
            <td align='right' id='keluarxharga_perbaiki'>" . $sumHargaRataBenar . "</td>
            <td colspan='4' align='center'></td>
        </tr>";
        $tab .= "<tr class=rowcontent style='font-weight:bold;background-color:green;color:white'>
            <td colspan='8' align='center'>Nilai Saldo Akhir (Perbaiki)</td>
            <td align='right' id='nilaisaldoakhir_perbaiki'>" . (($nilaisaldoawal + $sumHargaSatuan) - $sumHargaRataBenar) . "</td>
            <td colspan='4' align='center'></td>
        </tr>";
        // AKHIR TOTAL



        $tab .= "</tbody></table>";


        echo $tab;

        /* * ************************************************************
        * [END] Rekalkulasi harga ********************
        * ************************************************************ */

        break;


    case '9':
        break;


    case 'GetDivisiBarang':
        $optdivisi = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        $optbarang = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

        if ($param['unitId'] != '') {
            ## GET DIVISI
            $str = "select distinct kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='" . $param['unitId'] . "' and tipe like 'GUDANG%' order by kodeorganisasi asc";
            $res = fetchdata($str);
            foreach ($res as $val) {
                $optdivisi .= "<option value='" . $val['kodeorganisasi'] . "'>" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
            }

            ## GET Barang
            $str = "select distinct kodebarang,namabarang from " . $dbname . ".log_5masterbarang where left(kelompokbarang,3) in (select kode from log_5klbarang where noakun='" . $param['akunpersediaan'] . "') order by kodebarang desc";
            $res = fetchdata($str);
            foreach ($res as $val) {
                $optbarang .= "<option value='" . $val['kodebarang'] . "'>" . $val['kodebarang'] . " - " . $val['namabarang'] . "</option>";
            }

            echo $optdivisi . "####" . $optbarang;
        }
        break;


    case 'ProsesSemuaGudangDivisi':
        // =====================================================
        // ============= PROSES SEMUA GUDANG DIVISI ============
        // =====================================================
    case 'ProsesSemuaGudangDivisi':
        if (!isset($_POST['data'])) {
            echo "DATA TIDAK DITEMUKAN";
            exit;
        }
        // Tangkap JSON dari JS
        $data = json_decode($_POST['data'], true);

        if (!is_array($data)) {
            echo "FORMAT DATA TIDAK VALID";
            exit;
        }
        /*
            Struktur $data setelah decode JSON:
            $data = [
                "BR1E" => ["400000003", "400000007"],
                "BR2E" => ["400000010"],
            ];
        */
        $totalGudang = 0;
        $totalBarangProses = 0;

        echo "<pre>";
        print_r($data);

        foreach ($data as $gudang => $listBarang) {
            $totalGudang++;

            $hasilLog .= "Memproses Gudang: $gudang\n";

            foreach ($listBarang as $kodebarang) {
                $totalBarangProses++;

                // =====================================
                // == ACTION / QUERY PROSES MU DI SINI ==
                // =====================================

                /*
                CONTOH:
                $str = "update log_5saldobulanan
                        set saldoakhirqty = 0
                        where kodegudang='$gudang'
                        and kodebarang='$kodebarang'
                        and periode='$periode'";

                $res = mysql_query($str) or die(mysql_error());
                */

                // untuk debug:
            }
        }

        exit("warning:a");
        break;
    case 'getperiodegudang':
        if ($param['unitId'] != '') {
            $str_0 = "select distinct kodeorganisasi from " . $dbname . ".organisasi where induk='" . $param['unitId'] . "' and tipe = 'GUDANG' order by kodeorganisasi asc";
            $res_0 = fetchdata($str_0);
            $kodeorganisasi_0 = $res_0[0]['kodeorganisasi'];

            if ($kodeorganisasi_0 == '') {
                $str_0 = "select distinct kodeorganisasi from " . $dbname . ".organisasi where kodeorganisasi like '" . $param['unitId'] . "%' and tipe = 'GUDANGTEMP' order by kodeorganisasi asc";
                $res_0 = fetchdata($str_0);
                $kodeorganisasi_0 = $res_0[0]['kodeorganisasi'];
            }

            $str = "select distinct periode from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $kodeorganisasi_0 . "' group by periode";
            $res = fetchdata($str);
            foreach ($res as $val) {
                $optGudang .= "<option value='" . $val['periode'] . "'>" . $val['periode'] . "</option>";
            }
            echo $optGudang;
        }
        break;


    case 'savedataHarga':
        if ($param['barangId'] == '') {
            exit("warning: Data notransaksi atau kodebarang masih kosong.");
        }

        // if ($param['baris'] == '1') {
        //     $update5saldobulanan = "UPDATE " . $dbname . ".log_5saldobulanan 
        //                                            SET qtykeluarxharga = '0'
        //                                            WHERE periode='" . $param['periode'] . "' and kodegudang='" . $param['divisiId'] . "' and kodebarang ='" . $param['barangId'] . "'
        //                                            ";
        //     try {
        //         $owlPDO->exec($update5saldobulanan); // Update harga rata
        //     } catch (PDOException $e) {
        //         exit("Gagal update: " . $e->getMessage());
        //     }
        // }


        if ($param['hargaratabenar'] != '-') {
            if ($param['notransaksi'] != '-') {
                if ($param['hargaratabenar'] != '') {
                    if ($param['notransaksi'] != '') {

                        // ambil 5saldobulanan
                        $qtykeluarxharga = 0;
                        $qtymasukxharga = 0;
                        $nilaisaldoakhir = 0;
                        $hargarataawal = 0;
                        $hartot = 0;
                        $jumlah = 0;
                        $qtykeluarxhargaKuranghartot = 0;
                        $nilaisaldoakhirnew = 0;

                        $str = "select qtymasukxharga, qtykeluarxharga, nilaisaldoakhir from " . $dbname . ".log_5saldobulanan where periode='" . $param['periode'] . "' and kodegudang='" . $param['divisiId'] . "' and kodebarang ='" . $param['barangId'] . "'  ";
                        $res = fetchdata($str);
                        $qtymasukxharga = $res[0]['qtymasukxharga'];
                        $qtykeluarxharga = $res[0]['qtykeluarxharga'];
                        $nilaisaldoakhir = $res[0]['nilaisaldoakhir'];
                        $hargarataawal = $param['hargarataawal'];


                        $notransaksi = $param['notransaksi'];
                        $notransaksireferensi = $param['notransaksireferensi'];
                        $kodebarang = $param['barangId'];
                        $jumlah = floatval(str_replace(',', '', $param['jumlah']));
                        $hargaBaru = floatval(str_replace(',', '', $param['hargaratabenar']));

                        $hartot = $jumlah * $hargaBaru;

                        $masukxharga_perbaiki = floatval(str_replace(',', '', $param['masukxharga_perbaiki']));
                        $keluarxharga_perbaiki = floatval(str_replace(',', '', $param['keluarxharga_perbaiki']));
                        $nilaisaldoakhir_perbaiki = floatval(str_replace(',', '', $param['nilaisaldoakhir_perbaiki']));

                        // $qtykeluarxhargaKuranghartot = $qtykeluarxharga - ($jumlah * $hargarataawal);

                        // $qtykeluarxhargaKuranghartotnew = $qtykeluarxhargaKuranghartot + $hartot;

                        $qtykeluarxhargaKuranghartotnew = $qtykeluarxharga + $hartot;

                        // echo $qtykeluarxhargaKuranghartotnew;

                        $nilaisaldoakhirnew = $qtymasukxharga - $qtykeluarxhargaKuranghartotnew;

                        if ($nilaisaldoakhirnew <= 0) {
                            $nilaisaldoakhirnew = 0;
                        }



                        // 1. Update hargarata pada log_transaksidt
                        $str = "UPDATE " . $dbname . ".log_transaksidt 
                            SET hargarata = '" . $hargaBaru . "' 
                            WHERE notransaksi = '" . $notransaksi . "' 
                            AND kodebarang = '" . $kodebarang . "' and kodeblok = '" . $param['kodeblok'] . "' and kodekegiatan='" . $param['kodekegiatan'] . "' ";

                        // 2. Ambil data jurnaldt (nojurnal)
                        $str2 = "SELECT nojurnal FROM " . $dbname . ".keu_jurnaldt 
                            WHERE noreferensi = '" . $notransaksi . "' 
                            AND kodebarang = '" . $kodebarang . "' 
                            AND nojurnal LIKE '%INVK1%' 
                            AND noakun LIKE '115%'";

                        try {
                            $owlPDO->exec($str); // Update harga rata

                            $query = $owlPDO->query($str2);
                            $result = $query->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($result as $row) {
                                $nojurnal = $row['nojurnal'];

                                // 3. Update baris dengan jumlah negatif
                                $strUpdateNeg = "UPDATE " . $dbname . ".keu_jurnaldt 
                                            SET jumlah = '" . ($hartot * -1) . "'
                                            WHERE nojurnal = '" . $nojurnal . "' 
                                            AND noreferensi = '" . $notransaksi . "' 
                                            AND kodebarang = '" . $kodebarang . "' 
                                            AND jumlah < 0";

                                // 4. Update baris dengan jumlah positif
                                $strUpdatePos = "UPDATE " . $dbname . ".keu_jurnaldt 
                                            SET jumlah = '" . $hartot . "'
                                            WHERE nojurnal = '" . $nojurnal . "' 
                                            AND noreferensi = '" . $notransaksi . "' 
                                            AND kodebarang = '" . $kodebarang . "' 
                                            AND jumlah >= 0";

                                $owlPDO->exec($strUpdateNeg);
                                $owlPDO->exec($strUpdatePos);
                            }

                            // 5. Cek jika data ada di kebun_pakaimaterial
                            $strCekPakai = "SELECT COUNT(*) as jumlah FROM " . $dbname . ".kebun_pakaimaterial 
                                        WHERE notransaksi = '" . $notransaksireferensi . "' 
                                        AND kodebarang = '" . $kodebarang . "' AND kodeorg = '" . $param['kodeblok'] . "' AND kodekegiatan='" . $param['kodekegiatan'] . "'
                                        AND kwantitas = '" . $jumlah . "'";

                            $cekPakai = $owlPDO->query($strCekPakai)->fetch(PDO::FETCH_ASSOC);

                            if ($cekPakai['jumlah'] > 0) {
                                $str3 = "
                                    SELECT nojurnal 
                                    FROM (
                                        SELECT
                                            nojurnal,
                                            MAX(CASE WHEN nourut = 1 THEN kodebarang END) AS kodebarang,
                                            MAX(CASE WHEN nourut = 2 THEN kodekegiatan END) AS kodekegiatan,
                                            MAX(CASE WHEN nourut = 2 THEN kodeblok END) AS kodeblok
                                        FROM " . $dbname . ".keu_jurnaldt
                                        WHERE 
                                            noreferensi = '" . $notransaksireferensi . "'
                                            AND nojurnal LIKE '%INVK1%'
                                        GROUP BY nojurnal
                                    ) AS x
                                    WHERE 
                                        x.kodebarang = '" . $kodebarang . "'
                                        AND x.kodekegiatan = '" . $param['kodekegiatan'] . "'
                                        AND x.kodeblok = '" . $param['kodeblok'] . "'
                                    ";
                                $query3 = $owlPDO->query($str3);
                                $result3 = $query3->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($result3 as $row) {
                                    $nojurnal = $row['nojurnal'];

                                    $strUpdatePakai = "UPDATE " . $dbname . ".kebun_pakaimaterial 
                                                   SET hargasatuan = '" . $hargaBaru . "' 
                                                   WHERE notransaksi = '" . $notransaksireferensi . "' 
                                                   AND kodebarang = '" . $kodebarang . "'  AND kodeorg = '" . $param['kodeblok'] . "' AND kodekegiatan='" . $param['kodekegiatan'] . "'
                                                   AND kwantitas = '" . $jumlah . "'  ";
                                    $owlPDO->exec($strUpdatePakai);

                                    // 3. Update baris dengan jumlah negatif
                                    $strUpdateNeg = "UPDATE " . $dbname . ".keu_jurnaldt 
                                                SET jumlah = '" . ($hartot * -1) . "'
                                                WHERE nojurnal = '" . $nojurnal . "' 
                                                AND noreferensi = '" . $notransaksireferensi . "' 
                                                AND kodebarang = '" . $kodebarang . "' 
                                                AND jumlah < 0";

                                    // 4. Update baris dengan jumlah positif
                                    $strUpdatePos = "UPDATE " . $dbname . ".keu_jurnaldt 
                                                SET jumlah = '" . $hartot . "'
                                                WHERE nojurnal = '" . $nojurnal . "' 
                                                AND noreferensi = '" . $notransaksireferensi . "' 
                                                AND keterangan like '%Material BKM%'
                                                AND jumlah >= 0";

                                    $owlPDO->exec($strUpdateNeg);
                                    $owlPDO->exec($strUpdatePos);
                                }
                            }

                            // update 5saldo bulanan                                 
                            $update5saldobulanan = "UPDATE " . $dbname . ".log_5saldobulanan 
                                                   SET qtykeluarxharga = '" . $keluarxharga_perbaiki . "', nilaisaldoakhir = '" . $nilaisaldoakhir_perbaiki . "', 	qtymasukxharga = '" . $masukxharga_perbaiki . "'
                                                   WHERE periode='" . $param['periode'] . "' and kodegudang='" . $param['divisiId'] . "' and kodebarang ='" . $param['barangId'] . "'
                                                   ";
                            $owlPDO->exec($update5saldobulanan);
                        } catch (PDOException $e) {
                            exit("Gagal update: " . $e->getMessage());
                        }
                    }
                }
            }
        }

        break;


    case 'prosesSaldoAwal':

        try {
            $owlPDO->beginTransaction();

            $periodeInput = $param['periodeId'];   // contoh: 2025-08
            $unit         = $param['unitId'];
            $noakun       = $param['noakun'];
            $nilai        = str_replace(',', '', $param['saldogudang']);

            if ($periodeInput == '' || $unit == '' || $noakun == '') {
                throw new Exception("Data tidak lengkap");
            }

            // Ubah 2025-08 jadi 202508
            $periode = str_replace('-', '', $periodeInput);

            // Validasi format akhir harus 6 digit
            if (!preg_match('/^[0-9]{6}$/', $periode)) {
                throw new Exception("Format periode salah");
            }

            // Ambil bulan
            $bulan = substr($periode, -2);   // 08
            $fieldAwal = "awal" . $bulan;      // awal08

            // 1. Cek data
            $strCek = "SELECT COUNT(*) as jml FROM " . $dbname . ".keu_saldobulanan WHERE kodeorg = '" . $unit . "' AND periode = '" . $periode . "' AND noakun = '" . $noakun . "'";
            $query  = $owlPDO->query($strCek);
            $result = $query->fetch(PDO::FETCH_ASSOC);

            if ($result['jml'] > 0) {
                // UPDATE
                $strUpdate = "UPDATE " . $dbname . ".keu_saldobulanan SET " . $fieldAwal . " = '" . $nilai . "' WHERE kodeorg = '" . $unit . "' AND periode = '" . $periode . "' AND noakun = '" . $noakun . "'";
                $owlPDO->exec($strUpdate);
            } else {
                // INSERT
                $strInsert = "INSERT INTO " . $dbname . ".keu_saldobulanan (kodeorg, periode, noakun, " . $fieldAwal . ") VALUES ('" . $unit . "', '" . $periode . "', '" . $noakun . "', '" . $nilai . "')";
                $owlPDO->exec($strInsert);
            }

            $owlPDO->commit();
        } catch (Exception $e) {
            $owlPDO->rollback();
            exit("Error : " . $e->getMessage());
        }
        break;

    case 'infoBisaCek':
        echo cekGudangPostingAll($param['gudang'], $param['periodeId']);
        break;
    case 'praTutupBuku':
        echo infotutupgudang($param['gudang'], $param['periodeId']);
        break;
    case 'PerbaikiSaldoBulanan':
        //=============================
        /* * ************************************************************
            //  * [START] Rekalkulasi stock fisik dan harga ******************
            * ************************************************************ */
        try {
            $owlPDO->beginTransaction();
            $gudang = $_POST['gudang'];
            $user = $_SESSION['standard']['userid'];
            $period = $_POST['periode'];
            $awal = $_POST['tanggalmulai'];
            $akhir = $_POST['tanggalsampai'];

            $sPeriode = "select distinct * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $gudang . "' and periode='" . $period . "' order by periode asc limit 1";
            $rPeriode = fetchData($sPeriode);
            $awal = $rPeriode[0]['tanggalmulai'];
            $akhir = $rPeriode[0]['tanggalsampai'];


            $dtAdd = explode("-", $period);
            $bulan = $dtAdd[1];
            $x = str_replace("-", "", $period);
            $x = str_replace("/", "", $x);
            $x = mktime(0, 0, 0, intval(substr($x, 4, 2)) + 1, 15, substr($x, 0, 4));
            $prefper = $period; // periode ini pakai prefer
            $period = date('Y-m', $x); //periode jadi periode depan 


            #ambil saldo awal
            $str = "select a.kodebarang,a.saldoawalqty,a.saldoakhirqty,a.hargarata,a.nilaisaldoawal,b.namabarang,b.satuan,a.qtymasukxharga,a.qtykeluarxharga from " . $dbname . ".log_5saldobulanan a 
                left join " . $dbname . ".log_5masterbarang b on a.kodebarang=b.kodebarang where a.kodegudang='" . $gudang . "' and a.periode='" . $prefper . "'";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {
                $sAkun = "select noakun from " . $dbname . ".log_5klbarang where kode='" . substr($bar->kodebarang, 0, 3) . "'";
                $rAkun = fetchData($sAkun);
                // fix saldo nolkoma 311010011 KSPE57 0.0000000000036379788070917
                $Dt['saldoawalqty'][$bar->kodebarang] = round($bar->saldoawalqty, 5);
                $Dt['nilaisaldoawal'][$bar->kodebarang] = $bar->nilaisaldoawal;
                $Dt['saldoakhirqty'][$bar->kodebarang] = $bar->saldoakhirqty;
                $Dt['hargarata'][$bar->kodebarang] = $bar->hargarata;
                $Dt['namabarang'][$bar->kodebarang] = $bar->namabarang;
                $Dt['satuan'][$bar->kodebarang] = $bar->satuan;
            }

            #ambil data masuk
            $str = "select kodebarang,sum(jumlah) as jumlah,sum(jumlah*hargasatuan) as rpmasuk from " . $dbname . ".log_transaksi_vw where kodegudang='" . $gudang . "' and tanggal>='" . $awal . "' and tanggal <='" . $akhir . "'
                            and tipetransaksi<5 and statussaldo=1 group by kodebarang";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {
                // sum(0.5;0.2;0.1)=0.7999999999999999? fix pake round,5
                $masuk[$bar->kodebarang] = round($bar->jumlah, 5);
                $rpmasuk[$bar->kodebarang] += $bar->rpmasuk;
            }

            /*#ambil rupiah per barang per gudang menjadi tambahan rpmasuk    
                $sJrn = "select kodebarang,jumlah from " . $dbname . ".keu_jurnaldt where  nojurnal like '%EXP01%' and tanggal between '" . $awal . "' and '" . $akhir . "' and right(noreferensi,6)='" . $gudang . "' and kodebarang!=''";
                $qJrn=$owlPDO->query($sJrn) or die(print " Gagal: ".PDOException::getMessage());
                $qJrn->setFetchMode(PDO::FETCH_ASSOC);
                while ($rJrn = $qJrn->fetch()) {
                    $rpmasuk[$rJrn['kodebarang']]+=$rJrn['jumlah'];
                }*/

            #ambil data keluar
            $str = "select kodebarang,sum(jumlah) as jumlah,sum(jumlah*hargarata) as rpkeluar from " . $dbname . ".log_transaksi_vw where kodegudang='" . $gudang . "' and tanggal>='" . $awal . "' and tanggal <='" . $akhir . "'
                            and tipetransaksi>4 and statussaldo=1 group by kodebarang";
            $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while ($bar = $res->fetch()) {
                // sum(0.5;0.2;0.1)=0.7999999999999999? fix pake round,5
                $keluar[$bar->kodebarang] = round($bar->jumlah, 5);
                @$rpkeluar[$bar->kodebarang] += $bar->rpkeluar;
            }
            #hilangkan blank
            $fixdata = array();
            if (!empty($Dt['hargarata'])) {
                foreach ($Dt['hargarata'] as $key => $val) {
                    if (!isset($masuk[$key])) {
                        $masuk[$key] = 0;
                    }
                    if (!isset($keluar[$key])) {
                        $keluar[$key] = 0;
                    }

                    $seharusnya = $Dt['saldoawalqty'][$key] + $masuk[$key] - $keluar[$key];
                    //if($seharusnya!=$Dt['saldoakhirqty'][$key]){
                    if ((@$seharusnya != @$Dt['saldoakhirqty'][$key]) || (@$rpmasuk[$key] != @$Dt['qtymasukxharga'][$key]) || (@$rpkeluar[$key] != @$Dt['qtykeluarxharga'][$key])) {
                        $fixdata['saldoawal'][$key] = $Dt['saldoawalqty'][$key];
                        $fixdata['saldoakhir'][$key] = $Dt['saldoakhirqty'][$key];
                        $fixdata['masuk'][$key] = $masuk[$key];
                        $fixdata['keluar'][$key] = $keluar[$key];
                        $fixdata['seharusnya'][$key] = $seharusnya;

                        $fixdatarp['masuk'][$key] = floatval(@$rpmasuk[$key]) > 0 ? $rpmasuk[$key] : 0;
                        $fixdatarp['keluar'][$key] = floatval(@$rpkeluar[$key]) > 0 ? $rpkeluar[$key] : 0;
                        $fixdatarp['saldoakhir'][$key] = round($Dt['nilaisaldoawal'][$key] + $fixdatarp['masuk'][$key] - $fixdatarp['keluar'][$key], 4);
                        $fixdatarp['hargarata'][$key] = floatval($fixdata['seharusnya'][$key]) > 0 ? $fixdatarp['saldoakhir'][$key] / $fixdata['seharusnya'][$key] : 0;

                        #= jika nilai saldoakhir rupiah sudah 0, maka saldoqty di 0 kan
                        if ($fixdatarp['saldoakhir'][$key] == '0') {
                            $fixdata['seharusnya'][$key] = '0';
                        }
                    }
                }

                if (count($fixdata) > 0) {
                    foreach ($fixdata['saldoawal'] as $key => $val) {
                        #update log_5saldobulanan
                        $str = "update log_5saldobulanan set saldoakhirqty=" . $fixdata['seharusnya'][$key] . ",qtymasuk=" . $fixdata['masuk'][$key] . ",qtykeluar=" . $fixdata['keluar'][$key] . ",
                                    hargarata=" . $fixdatarp['hargarata'][$key] . ", qtymasukxharga=" . $fixdatarp['masuk'][$key] . ",qtykeluarxharga=" . $fixdatarp['keluar'][$key] . ",
                                    nilaisaldoakhir=" . $fixdatarp['saldoakhir'][$key] . " where kodebarang='" . $key . "' and kodegudang='" . $gudang . "'
                                    and periode='" . $prefper . "'";
                        $owlPDO->exec($str);

                        // if(substr($key,0,3)=='360'){
                        // 	echo $str = "update log_5saldobulanan set saldoakhirqty=" . $fixdata['seharusnya'][$key] . ",qtymasuk=" . $fixdata['masuk'][$key] . ",qtykeluar=" . $fixdata['keluar'][$key] . ",
                        // 	hargarata=" . $fixdatarp['hargarata'][$key] . ", qtymasukxharga=" . $fixdatarp['masuk'][$key] . ",qtykeluarxharga=" . $fixdatarp['keluar'][$key] . ",
                        // 	nilaisaldoakhir=" . $fixdatarp['saldoakhir'][$key] . " where kodebarang='" . $key . "' and kodegudang='" . $gudang . "'
                        // 	and periode='" . $prefper . "';";
                        // }

                        #update log_5masterbarangdt
                        $str = "update " . $dbname . ".log_5masterbarangdt set saldoqty=" . $fixdata['seharusnya'][$key] . " where kodebarang='" . $key . "' and kodegudang='" . $gudang . "'";
                        $owlPDO->exec($str);
                    }
                }
            }
            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollback();
            echo "Error, " . addslashes($e->getMessage());
        }
        /* * ************************************************************
            * [END] Rekalkulasi stock fisik dan harga ********************
             * ************************************************************ */
        break;
}

// CEK SUDAH BISA TUTUP GUDANG
function infotutupgudang($unit, $periode)
{
    $param['unitId'] = substr($unit, 0, 4);
    $param['periodeId'] = $periode;
    global $dbname;
    global $owlPDO;
    global $param;


    $sPeriode = "select distinct * from " . $dbname . ".setup_periodeakuntansi where kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . $param['unitId'] . "' and tipe like 'GUDANG%') and periode='" . $param['periodeId'] . "' order by periode asc limit 1";
    $rPeriode = fetchData($sPeriode);
    $awal = $rPeriode[0]['tanggalmulai'];
    $akhir = $rPeriode[0]['tanggalsampai'];


    $textwarn = "";
    $x = str_replace("-", "", $param['periodeId']);
    $x = str_replace("/", "", $x);
    $x = mktime(0, 0, 0, intval(substr($x, 4, 2)) + 1, 15, substr($x, 0, 4));
    $prefper = date('Y-m', $x);

    /* * ************************************************************
        * [START] Cek Nilai Material VS Jurnal ***********************
        * ************************************************************ */

    $tipeorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
    if ($tipeorg[$param['unitId']] == 'KANWIL') {

        $optKel = makeOption($dbname, 'log_5klbarang', "kode,noakun", "noakun!='' and noakun like '11504%'");
        $listKel = $listAkun = array();
        foreach ($optKel as $kode => $akun) {
            $listKel[] = $kode;
            $listAkun[$akun] = $akun;
        }

        // Get Nilai Material, log_5saldobulanan
        $qSaldoMat = "  SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
                            FROM " . $dbname . ".log_5saldobulanan 
                            WHERE left(kodebarang,3) in ('" . implode("','", $listKel) . "') and kodegudang like '" . $param['unitId'] . "%' and periode='" . $param['periodeId'] . "' GROUP BY left(kodebarang,3)
                        ";
        //echo $qSaldoMat."<p>";
        $resSaldoMat = fetchData($qSaldoMat);
        $optSaldoMat = array();
        foreach ($resSaldoMat as $row) {
            if (!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
                $optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
            } else {
                $optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
            }
        }
        $dtAdd = explode("-", $param['periodeId']);
        $periodeKuangan = $dtAdd[0] . $dtAdd[1];
        // Get Nilai Jurnal, keu_saldobulanan
        $qSaldoJ = "SELECT awal" . $dtAdd[1] . " as saldoawal,noakun
                        FROM " . $dbname . ".keu_saldobulanan
                        WHERE kodeorg='" . $param['unitId'] . "' and periode='" . $periodeKuangan . "'
                            and noakun in ('" . implode("','", $listAkun) . "')
                        ";
        //echo $qSaldoJ."<p>";
        $resSaldoJ = fetchData($qSaldoJ);
        $optSaldoJ = array();
        foreach ($resSaldoJ as $row) {
            $optSaldoJ[$row['noakun']] = $row['saldoawal'];
        }
        $lstAkun2 = array();
        // Get Transaksi Jurnal
        $qTrans = "SELECT sum(debet - kredit) as saldotrans, noakun
                        FROM " . $dbname . ".keu_jurnaldt_vw
                        WHERE kodeorg='" . $param['unitId'] . "' and tanggal>='" . $awal . "' and tanggal <='" . $akhir . "'
                            and noakun in ('" . implode("','", $listAkun) . "')
                        GROUP BY noakun
                    ";
        //echo $qTrans;
        //echo $qTrans."<p>";
        $resTrans = fetchData($qTrans);
        foreach ($resTrans as $row) {
            if (!isset($optSaldoJ[$row['noakun']]))
                $optSaldoJ[$row['noakun']] = 0;
            $optSaldoJ[$row['noakun']] += $row['saldotrans'];
        }
    } else {
        // Get Kelompok Barang yang ada Akun
        $optKel = makeOption($dbname, 'log_5klbarang', "kode,noakun", "noakun!='' and noakun like '11501%'");
        $listKel = $listAkun = array();
        foreach ($optKel as $kode => $akun) {
            $listKel[] = $kode;
            $listAkun[$akun] = $akun;
        }

        // Get Nilai Material, log_5saldobulanan
        $qSaldoMat = "SELECT SUM(nilaisaldoakhir) as saldo, left(kodebarang,3) as klbarang
			  FROM " . $dbname . ".log_5saldobulanan 
			  WHERE left(kodebarang,3) in ('" . implode("','", $listKel) . "') and kodegudang like '" . $param['unitId'] . "%' and periode='" . $param['periodeId'] . "' GROUP BY left(kodebarang,3)";
        //echo $qSaldoMat."<p>";
        $resSaldoMat = fetchData($qSaldoMat);
        $optSaldoMat = array();
        foreach ($resSaldoMat as $row) {
            if (!isset($optSaldoMat[$optKel[$row['klbarang']]])) {
                $optSaldoMat[$optKel[$row['klbarang']]] = $row['saldo'];
            } else {
                $optSaldoMat[$optKel[$row['klbarang']]] += $row['saldo'];
            }
        }
        $dtAdd = explode("-", $param['periodeId']);
        $periodeKuangan = $dtAdd[0] . $dtAdd[1];
        // Get Nilai Jurnal, keu_saldobulanan
        $qSaldoJ = "SELECT awal" . $dtAdd[1] . " as saldoawal,noakun
			  FROM " . $dbname . ".keu_saldobulanan
			  WHERE kodeorg='" . $param['unitId'] . "' and periode='" . $periodeKuangan . "'
				and noakun in ('" . implode("','", $listAkun) . "')";
        //echo $qSaldoJ."<p>";
        $resSaldoJ = fetchData($qSaldoJ);
        $optSaldoJ = array();
        foreach ($resSaldoJ as $row) {
            $optSaldoJ[$row['noakun']] = $row['saldoawal'];
        }
        $lstAkun2 = array();
        // Get Transaksi Jurnal
        $qTrans = "SELECT sum(debet - kredit) as saldotrans, noakun
			  FROM " . $dbname . ".keu_jurnaldt_vw
			  WHERE kodeorg='" . $param['unitId'] . "' and tanggal>='" . $awal . "' and tanggal <='" . $akhir . "'
				and noakun in ('" . implode("','", $listAkun) . "')
			  GROUP BY noakun";
        //echo $qTrans;
        //echo $qTrans."<p>";
        $resTrans = fetchData($qTrans);
        foreach ($resTrans as $row) {
            if (!isset($optSaldoJ[$row['noakun']]))
                $optSaldoJ[$row['noakun']] = 0;
            $optSaldoJ[$row['noakun']] += $row['saldotrans'];
        }
    }

    // Cek All Akun
    $notBal = "";
    foreach ($listAkun as $akun) {
        if (!isset($optSaldoMat[$akun]))
            $optSaldoMat[$akun] = 0;
        if (!isset($optSaldoJ[$akun]))
            $optSaldoJ[$akun] = 0;

        $selisih = abs(abs($optSaldoMat[$akun]) - abs($optSaldoJ[$akun]));
        if ($selisih > 300) {
            $lstAkun2[$akun] = $akun;
            // $lstNilai[$akun] = "Angka Selisih : " . number_format($selisih) . ", Angka Material : " . number_format(abs($optSaldoMat[$akun])) . ", Angka Jurnal : " . number_format(abs($optSaldoJ[$akun]));
            $lstNilai[$akun] = "Angka Selisih : " . number_format($selisih);
            $notBal .= $akun . " = " . number_format($selisih) . "___" . abs($optSaldoMat[$akun]) . "____" . abs($optSaldoJ[$akun]) . "\n";
        }
    }
    if ($textwarn != "") {
        echo $textwarn;
        if (!empty($notBal)) {
            $tab = "<br>Ada Akun Belum Balance";
            $tab .= "<table cellpadding=1 cellspacing=1 class=sortable style='font-size:10px' width=100%>";
            $tab .= "<thead><tr class=rowheader>";
            $tab .= "<th>" . $_SESSION['lang']['noakun'] . "</th>";
            $tab .= "<th>" . $_SESSION['lang']['namaakun'] . "</th>";
            $tab .= "<th>" . $_SESSION['lang']['nilai'] . "</th>";
            $tab .= "</tr></thead><tbody>";
            foreach ($lstAkun2 as $key) {
                $optNmAkun = makeOption($dbname, "keu_5akun", "noakun,namaakun", "noakun='" . $key . "' and aktif=1");
                $tab .= "<tr class=rowcontent>";
                $tab .= "<td>" . $key . "</td>";
                $tab .= "<td>" . $optNmAkun[$key] . "</td>";
                $tab .= "<td>" . $lstNilai[$key] . "</td>";
                $tab .= "</tr>";
            }
        }
    } else {
        //  echo"<pre>";
        //  print_r($lstAkun2);
        //  echo"</pre>";
        // Alert Jika ada yang belum balance

        $tab .= "<table cellpadding=1 cellspacing=1 class=sortable style='font-size:10px' width=100%>";
        $tab .= "<thead><tr class=rowheader>";
        $tab .= "<th>" . $_SESSION['lang']['noakun'] . "</th>";
        $tab .= "<th>" . $_SESSION['lang']['namaakun'] . "</th>";
        $tab .= "<th>" . $_SESSION['lang']['nilai'] . "</th>";
        $tab .= "</tr></thead><tbody>";
        if (!empty($notBal)) {
            foreach ($lstAkun2 as $key) {
                $optNmAkun = makeOption($dbname, "keu_5akun", "noakun,namaakun", "noakun='" . $key . "' and aktif=1");
                $tab .= "<tr class=rowcontent>";
                $tab .= "<td>" . $key . "</td>";
                $tab .= "<td>" . $optNmAkun[$key] . "</td>";
                $tab .= "<td>" . $lstNilai[$key] . "</td>";
                $tab .= "</tr>";
            }
        } else {
            $tab .= "<tr class=rowcontent>";
            $tab .= "<td colspan=4>Silakan lanjutkan Tutup Buku</td>
            </tr>";
        }
        $tab .= "</tbody></table>";
    }

    return $tab;
    /* * ************************************************************
    //  * [END] Cek Nilai Material VS Jurnal *************************
     * ************************************************************ */
}


function cekGudangPostingAll($gudang, $periode)
{
    global $dbname;
    global $owlPDO;
    global $param;

    $infogagal = "";

    // cek saldo divisi
    $str = "select *  from `" . $dbname . "`.`log_5saldobulanan` where periode='" . $periode . "' and  saldoakhirqty>0 and 
        kodegudang in (select kodeorganisasi from " . $dbname . ".organisasi where tipe='GUDANGTEMP' and induk='" . substr($gudang, 0, 4) . "')";
    $res_cekdivisi = fetchdata($str);
    if (count($res_cekdivisi) > 0) {
        foreach ($res_cekdivisi as $bar) :
            $infogagal1 = "Tipe 1 - Masih Ada Saldo Digudang Divisi di unit " . substr($gudang, 0, 4) . " <br>";
        endforeach;
    }

    // cek pakai material belum posting
    $iBkm = "select * from " . $dbname . ".kebun_pakai_material_vw where jurnal=0 and tanggal like '" . $periode . "%' and"
        . " left(kodegudang,4)='" . substr($gudang, 0, 4) . "' ";
    $res_cekbkm = fetchdata($iBkm);
    if (count($res_cekbkm) > 0) {
        foreach ($res_cekbkm as $bar) :
            $infogagal2 = "Masih ada BKM belum posting di unit " . substr($gudang, 0, 4) . " <img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=" . $_SESSION['lang']['find'] . " onclick=\"viewInfo('2')\"> <br>";
        endforeach;
    }

    // apakah sudah posting all
    $sTrns = "select * from " . $dbname . ".log_transaksi_vw where left(kodegudang,4)='" . substr($gudang, 0, 4) . "' and tanggal like '%" . $periode . "%'
    and (post=0 or statussaldo=0) ";
    $res_cektransaksigudang = fetchData($sTrns);
    if (count($res_cektransaksigudang) > 0) {
        foreach ($res_cektransaksigudang as $bar) :
            $infogagal3 = "Masih ada Transaksi Gudang belum posting di unit " . substr($gudang, 0, 4) . " <img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=" . $_SESSION['lang']['find'] . " onclick=\"viewInfo('3')\"> <br>";
        endforeach;
    }

    $sTrns = "select * from " . $dbname . ".log_transaksi_vw where tipetransaksi=7 and gudangx like '" . substr($gudang, 0, 4) . "%' and tanggal like '%" . $periode . "%' and (notransaksireferensi='' or notransaksireferensi is null)";
    $res_cektransaksigudang = fetchData($sTrns);
    if (count($res_cektransaksigudang) > 0) {
        foreach ($res_cektransaksigudang as $bar) :
            $infogagal4 = "Masih ada Transaksi mutasi belum terimakan di unit " . substr($gudang, 0, 4) . " <img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=" . $_SESSION['lang']['find'] . " onclick=\"viewInfo('4')\"> <br>";
        endforeach;
    }

    // cek saldo awal gudang salah
    $selisih = 0;
    $tolerance = 10;
    $str = "select *  from `" . $dbname . "`.`log_5saldobulanan` where periode='" . $periode . "' and kodegudang in (select kodeorganisasi from " . $dbname . ".organisasi where tipe IN ('GUDANGTEMP','GUDANG') and induk='" . substr($gudang, 0, 4) . "')";
    $res_SALDOAWALSALAH = fetchdata($str);
    if (count($res_SALDOAWALSALAH) > 0) {
        foreach ($res_SALDOAWALSALAH as $bar) :


            $selisih = ($bar['saldoawalqty'] * $bar['hargaratasaldoawal']) - $bar['nilaisaldoawal'];

            if (abs($selisih) > $tolerance) {
                $infoGagal5Title = '<span style=color:red;font-weight:bold>Saldo Awal Barang Salah Unit ' . substr($gudang, 0, 4) . ' Periode ' . $periode . ' : </span> <br>';
                $infogagal5 .= "- Saldo Awal Atas Barang " . $bar['kodebarang'] . " Di Gudang " . $bar['kodegudang'] . " Salah!!! <br>";
            }
        endforeach;
    }

    $infogagal = $infogagal1 . $infogagal2 . $infogagal3 . $infogagal4;

    return $infogagal;
}
// end joki
// apache_child_terminate