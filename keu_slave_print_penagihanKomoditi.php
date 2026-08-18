<?php
require_once 'config/connection.php';
require_once 'lib/nangkoelib.php';
require_once 'lib/zLib.php';
require_once 'dompdf/autoload.inc.php';


require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
require_once('phpqrcode/qrlib.php');

require('lib/fpdf.php');
require('lib/htmlparser.inc');
require('lib/htmltofpdf.php');

use Dompdf\Dompdf;

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
$tglInv = "";
$urlefil = checkPostGet('urlefil', '0');
$optnmcust = makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$optnmakun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$nmkapal = makeOption($dbname, 'pmn_5kapalponton', 'kode,nama');

$arrHead = setheadreport('', $dataH['kodeorg']);
$path = $arrHead['logopalma'];
$pathnonpalma = 'images/logo/KOP INVOICE.png';

$str = "select * from " . $dbname . "." . $table . " where noinvoice='" . $column . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$kodept = $bar['kodept'];
$kodecustomer = $bar['kodecustomer'];
$nokontrak = $bar['nokontrak'];
$noinvoice = $bar['noinvoice'];
$tanggalinvoice = $bar['tanggal'];
$bulaninvoice = substr($bar['tanggal'], 5, 2);
$namabulaninvoice = numToMonth(substr($bar['tanggal'], 5, 2), 'I', 'long');
$tahuninvoice = substr($bar['tanggal'], 0, 4);
$kuantitas = $bar['kuantitas'];
$kodebarang = $bar['kodebarang'];
$nilaiinvoice = $bar['nilaiinvoice'];
$nilaiplinv = $bar['nilaiinvoice'];
$matauang = $bar['matauang'];
$noakun = $bar['bayarke'];
$ttd = $bar['ttd'];
$jenis = $bar['jenis'];
$berikat = $bar['berikat'];
$createby = $bar['createby'];
$jenisinvoice = $bar['jenisinvoice'];
$transport = $bar['transport'];
// $hargasatuan=@($nilaiinvoice/$kuantitas);
$nilaippn = $bar['nilaippn'];
$ppnpl = $bar['nilaippn'];
$keterangantambahan = $bar['keterangantambahan'];
$nodo = $bar['nodo'];
$nofakturpajak = $bar['nofakturpajak'];
$noreferensi = $bar['noreferensi'];
$npwppt = $bar['npwpunit'];

$sql = "select nilaiinvoice from " . $dbname . "." . $table . " where nokontrak='" . $nokontrak . "' and jenisinvoice='UM' ";
$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$uminvoice = $bar['nilaiinvoice'];
// $totalnilaiinvoice=$nilaiinvoice+$nilaippn;





$keteranganpinalti1 = $bar['keterangan1'];
$rupiahpinalti1 = $bar['rupiah1'] * -1;
$keteranganpinalti2 = $bar['keterangan2'];
$rupiahpinalti2 = $bar['rupiah2'] * -1;
$keteranganpinalti3 = $bar['keterangan3'];
$rupiahpinalti3 = $bar['rupiah3'] * -1;
$keteranganpinalti4 = $bar['keterangan4'];
$rupiahpinalti4 = $bar['rupiah4'] * -1;
$keteranganpinalti5 = $bar['keterangan5'];
$rupiahpinalti5 = $bar['rupiah5'] * -1;
$keteranganpinalti6 = $bar['keterangan6'];
$rupiahpinalti6 = $bar['rupiah6'] * -1;
$keteranganpinalti7 = $bar['keterangan7'];
$rupiahpinalti7 = $bar['rupiah7'] * -1;
$keteranganpinalti8 = $bar['keterangan8'];
$rupiahpinalti8 = $bar['rupiah8'];

$totalpinalti = $rupiahpinalti1 + $rupiahpinalti2 + $rupiahpinalti3 + $rupiahpinalti4 + $rupiahpinalti5 + $rupiahpinalti6 + $rupiahpinalti7 + $rupiahpinalti8;

// exit("Error:".$ppnpinalti);


if ($tanggalinvoice < '2022-04-01') {
  $persentasesatu = '1.1';
  $persentasedua = '0.1';
  $persentasekata = '10%';
} else {
  $persentasesatu = '1.11';
  $persentasedua = '0.11';
  $persentasekata = '11%';
}

#= data datakaryawan
$str = "select * from " . $dbname . ".datakaryawan where karyawanid='" . $ttd . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$namakaryawan = $bar['namakaryawan'];
$jabatankaryawan = $bar['jabatan'];

#= data kontrak
$str = "select * from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $nokontrak . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$tanggalkontrak = $bar['tanggalkontrak'];
$kuantitaskontrak = $bar['kuantitaskontrak'];
$franco = $bar['franco'];
$hargasatuan = $bar['hargasatuan'];
$tipepenjualan = $bar['tipepenjualan'];
$ppnkontrak = $bar['ppn'];
[$persenDp, $persenPelunasan] = explode(":", $bar['kdtermin']);
if ($ppnkontrak == 1) {
  $hargasatuan = $hargasatuan / $persentasesatu;
}


$str = "SELECT SUM(kgpembeli) AS kg,
            SUM(
                rpclaimffa +
                rpclaimmoisture +
                rpclaimdirt +
                rpclaimdobi +
                rpclaimbroken +
                rpclaimmdani +
                rpclaimimpurities
            ) AS totalclaim
        FROM " . $dbname . ".pmn_bast 
        WHERE nokontrak='" . $nokontrak . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();

if ($jenisinvoice == 'PL') {
  $kuantitas = $bar['kg'];
  $totalclaim = $bar['totalclaim'];
}

if ($jenisinvoice == 'PL') {
  $nilaiinvoice = $hargasatuan * $kuantitas;
}
// echo $hargasatuan;
// echo"<br>";
// echo $nilaiinvoice;
// exit;
#= data nodo	

$str = "select * from " . $dbname . ".keu_penagihandt_kapalponton where noinvoice='" . $column . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {

  if ($bar['jenis'] == 'KPL') {
    $namakapal .= "<br> " . $nmkapal[$bar['kode']];
  }
  if ($bar['jenis'] == 'PNT') {
    $namaponton .= "<br> " . $nmkapal[$bar['kode']];
  }
  if ($bar['jenis'] == 'TRK') {
    $namatruck .= "<br> " . $nmkapal[$bar['kode']];
  }
}



#= data franco	
$str = "select * from " . $dbname . ".pmn_5franco where id_franco='" . $franco . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$namafranco = $bar['franco_name'];

if ($namafranco == '') {
  $namafranco = '&nbsp;';
}

$str = "select sum(nilairupiah) as totx from " . $dbname . ".keu_penagihandt where noinvoice='" . $column . "' group by noinvoice";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$totnilrup = $bar['totx'];

#= query data pt	
$str = "select * from " . $dbname . ".organisasi where kodeorganisasi='" . $kodept . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$alamatpt = $bar['alamat'];
$wilayahkotapt = $bar['wilayahkota'];
$kodepospt = $bar['kodepos'];
$teleponpt = $bar['telepon'];
$namapt = $bar['namaorganisasi'];
#= query akun bank
$str = "select * from " . $dbname . ".keu_5akunbank where noakun='" . $noakun . "'";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$namabank = $bar['namabank'];
$rekening = $bar['rekening'];
$cabang = $bar['cabang'];
$atasnama = $bar['atasnama'];


#= query akun bank
$str = "select * from " . $dbname . ".keu_5daftarbank where kodebank='" . $namabank . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$namabank = $bar['namabank'];

$str = "select * from " . $dbname . ".pmn_4customer where kodecustomer='" . $kodecustomer . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$alamatcustomer = $bar['alamat'];
$namacustomer = $bar['namacustomer'];
$telpcustomer = $bar['telepon'];
$faxcustomer = $bar['fax'];
$kotacustomer = $bar['kota'];

#= query nama barang	
$str = "select * from " . $dbname . ".log_5masterbarang where kelompokbarang='400'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
  $namabarang[$bar['kodebarang']] = $bar['namabarang'];
  $satuanbarang[$bar['kodebarang']] = $bar['satuan'];
}

#= query mata uang
$str = "select * from " . $dbname . ".setup_matauang";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
  $namamatauang[$bar['kode']] = $bar['matauang'];
}
$arrKet = [
  "UM" => "DP {$persenDp}%",
  "PL" => "Pelunasan",
];

$company = [
  'name'    => $namapt,
  'address' => $alamatpt,
];

$customer = [
  'name'    => $namacustomer,
  'address' => $alamatcustomer,
];

$meta = [
  'lembar_ke'   => '1',
  'no_invoice'  => '198-VI/CA-SPJB/2025',
  'no_spjb'     => '060/CA-GPCT/PERJ/CPO/V/2025',
  'tgl_spjb'    => '23 Mei 2025',
  'syarat_bayar' => '5 Hari Setelah SPJB Disetujui',
  'ttd_tanggal' => '10 Juni 2025',
  'ttd_nama'    => 'Rinarti Adiati',
];

$items = [
  // [no, nama, satuan, qty, harga]
  [1, 'CPO', 'Kg', 105000, 13344],
];

$bank = [
  'bank'   => 'BANK MANDIRI CABANG JATINEGARA',
  'an'     => 'PT CANDI ARTHA',
  'acc'    => '006-00-0971441-5',
];

$notes = [
  'Invoice Dianggap Sah Apabila Pembayaran Sudah Masuk Dalam Rekening PT. Candi Artha',
  'DP 70%',
];

function rupiah($n)
{
  return number_format((float)$n, 2, ',', '.');
}

// terbilang sederhana
function penyebut($nilai)
{
  $nilai = abs($nilai);
  $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
  if ($nilai < 12) return " " . $huruf[$nilai];
  elseif ($nilai < 20) return penyebut($nilai - 10) . " Belas";
  elseif ($nilai < 100) return penyebut(intval($nilai / 10)) . " Puluh" . penyebut($nilai % 10);
  elseif ($nilai < 200) return " Seratus" . penyebut($nilai - 100);
  elseif ($nilai < 1000) return penyebut(intval($nilai / 100)) . " Ratus" . penyebut($nilai % 100);
  elseif ($nilai < 2000) return " Seribu" . penyebut($nilai - 1000);
  elseif ($nilai < 1000000) return penyebut(intval($nilai / 1000)) . " Ribu" . penyebut($nilai % 1000);
  elseif ($nilai < 1000000000) return penyebut(intval($nilai / 1000000)) . " Juta" . penyebut($nilai % 1000000);
  elseif ($nilai < 1000000000000) return penyebut(intval($nilai / 1000000000)) . " Milyar" . penyebut($nilai % 1000000000);
  else return "";
}
function terbilang($nilai)
{
  $nilai = (int)$nilai;
  $t = trim(penyebut($nilai));
  $t = preg_replace('/\s+/', ' ', $t);
  return "# " . trim($t) . " Rupiah #";
}

$subTotal = 0;
foreach ($items as $it) {
  $subTotal += $it[3] * $it[4];
}

// Mengikuti contoh: DPP Nilai Lain = 11/12 x Subtotal; PPN = 12% x DPP
$dppNilaiLain = 0;
if ($nilaippn > 0) {
  $dppNilaiLain = round($nilaiinvoice * (11 / 12));
  $nilaippn         = round($dppNilaiLain * 0.12);
}
$grandTotal  = $nilaiinvoice + $nilaippn;

$notesHtml = '<ol style="padding-left:18px; margin:0;">';
foreach ($notes as $n) {
  $notesHtml .= '<li>' . htmlspecialchars($n) . '</li>';
}
$notesHtml .= '</ol>';

/* =========================
   HTML (HEREDOC di dalam PHP)
   ========================= */
$html = '<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Invoice</title>
<style>
  @page { margin: 24px 24px 28px 24px; size: A4 portrait; }
  body { font-family: Arial, Helvetica, sans-serif; font-size: 1px; color:#000; }
  .wrap { 
    border:1.5px solid #000; padding:12px; 
        border-bottom:none; 
  }
  .header-top { display:flex; justify-content:space-between; align-items:flex-start; }
  .brand { font-size:26px; font-weight:700; color:#0aa74a; letter-spacing:1px; }
  .addr { font-size:13px; max-width:72%; line-height:1.35; margin-top:2px; }
  .lembar { font-size:13px; }
  .title { text-align:center; font-size:22px; letter-spacing:3px; font-weight:700; margin:8px 0 12px; }
  .meta { font-size:13px; width:100%; border-collapse:collapse; }
  .meta td { padding:3px 2px; vertical-align:top; }
  .meta .left { width:52%; }
  .block { border:1px solid #000; padding:8px; min-height:86px; }
  .btitle { font-weight:bold; margin-bottom:6px; }
  .kv td:first-child{ width:110px; }
  .kv td:nth-child(2){ width:5px; }
  .kv td{ padding:1px 0; }
  
    .table { 
    width:102.2%; 
    border-collapse:collapse; 
    margin-top:10px; 
    margin-bottom:-20px; 
    margin-left:-9.5px; 
        font-size: 13px; /* atur ukuran font di sini */

    }
    .table th, 
    .table td { 
    border:1.6px solid #000;
    padding:6px; 
    }
    .table th { 
    text-align:center; 
    font-weight:bold; 
    }

  .num { text-align:right; } 
  .center { text-align:center; }
  .totals { width:100%; border:1px solid #000; border-top:none; padding:6px 8px 2px 50px; }
  .totals table { width:100%; border-collapse:collapse; }
  .totals td { padding:2px 0; border:1px solid #000; }
  .line { border-bottom:1px solid #000; }
  .terbilang { border:1px solid #000; border-top:none; padding:6px 8px; font-style:italic; }
  .payment { border:1px solid #000; border-top:none; padding:10px 8px; line-height:1.5; }
  .notes-sign { display:flex; justify-content:space-between; gap:8px; margin-top:8px; }
  .notes { width:62%; border:1px solid #000; min-height:130px; padding:8px; }
  .sign  { width:38%; border:1px solid #000; padding:8px; position:relative; min-height:130px; }
  .sign .date { text-align:right; margin-bottom:20px; }
  .sign .company { margin-top:8px; font-weight:bold; }
  .sign .name { position:absolute; bottom:8px; left:8px; font-weight:bold; }
  .footer { font-size:11px; margin-top:6px; }
.catatanbawah {
  font-size: 13px;
  border: none;
  line-height: 1.2;  
  margin-left: 12px;  

}

.tanda-tangan { 
  display: flex;
  flex-direction: column;
  align-items: center;  /* Tengah horizontal */
  text-align: center;
  line-height: 1;  

}

.tanda-tangan .hormat { 
  line-height: 1;
  
}
.tanda-tangan .tanggal { 
  margin-bottom: 12px;  
}

.ruang-ttd {
  height: 100px; /* ruang untuk tanda tangan */
      line-height: 1;  

}


</style>
</head>
<body>
    <div>
        <div class="brand">' . $company['name'] . '</div>
        <div class="addr">' . $company['address'] . '</div>
    </div>
    <div class="lembar" style="text-align:right;display:none">Lembar Ke : ' . $meta['lembar_ke'] . '</div>
  <div class="wrap">
    <div class="header-top"></div>

    <div class="title">INVOICE</div>

    <table class="meta">
      <tr>
        <td class="left">
          <div>
            <div class="btitle">Kepada Yth :</div>
            <div style="font-weight:bold;">' . $customer['name'] . '</div>
            <div>' . $customer['address'] . '</div>
          </div>
        </td>
        <td>
          <div>
            <table class="kv">
              <tr><td>No. Invoice</td><td>:</td><td>' . $noinvoice . '</td></tr>
              <tr><td>No. SPJB</td><td>:</td><td>' . $nokontrak . '</td></tr>
              <tr><td>Tanggal SPJB</td><td>:</td><td>' . tanggalbulan($tanggalkontrak, 2) . '</td></tr>
              <tr><td>Syarat Pembayaran</td><td>:</td><td>' . $meta['syarat_bayar'] . '</td></tr>
            </table>
          </div>
        </td>
      </tr>
    </table>

    <table class="table">
      <thead>
        <tr style="background-color: #d3effa;">
          <th style="width:35px;">No.</th>
          <th>Nama Barang</th>
          <th style="width:70px;">Satuan</th>
          <th style="width:90px;">Qty (Kg)</th>
          <th style="width:110px;">Harga (Rp)</th>
          <th style="width:130px;">Jumlah (Rp)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="center" style="height:50px;">' . htmlspecialchars($it[0]) . '</td>
          <td class="center" style="height:50px;">' . getNamaBrg($kodebarang) . '</td>
          <td class="center" style="height:50px;">' . htmlspecialchars($it[2]) . '</td>
          <td class="num" style="height:50px;">' . number_format($kuantitas) . '</td>
          <td class="num" style="height:50px;">' . rupiah($hargasatuan) . '</td>
          <td class="num" style="height:50px;">' . rupiah($nilaiinvoice) . '</td>
        </tr>';
if ($jenisinvoice == 'UM') {
  $html .= '<tr>
            <td colspan=5 style="text-align:left; padding-right:81px;">Sub Total</td>
            <td class="num">' . rupiah($nilaiinvoice) . '</td>
          </tr>
          <tr>
            <td colspan=5 style="text-align:left; padding-right:81px;">Pembayaran DP</td>
            <td class="num">' . rupiah($nilaiinvoice) . '</td>
          </tr>
          <tr>
            <td colspan=5 style="text-align:left; padding-right:81px;">Dasar Pengenaan Pajak Nilai Lain</td>
            <td class="num">' . rupiah($dppNilaiLain) . '</td>
          </tr>';

  $html .= '<tr>
            <td colspan=5 style="text-align:left; padding-right:81px;">PPN 12% Dari Dasar Pengenaan Pajak</td>
            <td class="num">' . rupiah($nilaippn) . '</td>
          </tr>';

  $html .= '<tr style="font-weight: bold;">
            <td colspan=5 style="text-align:left; padding-right:81px;">Jumlah Yang Harus Dibayar</td>
            <td class="num">' . rupiah($grandTotal) . '</td>
          </tr>
          <tr style="background-color: #d3effa;">
            <td colspan=6 style="text-align:left; padding-right:1px;">
              <p><b>Terbilang: </b></p>
              <br> <p align="center" style="margin-top:-10px; padding:0;"><i>' . terbilang($grandTotal) . '</i></p>
            </td> 
          </tr>';
} else {

  $html .= '<tr>
          <td colspan=5 style="text-align:left; padding-right:81px;">Sub Total</td>
          <td class="num">' . rupiah($nilaiinvoice) . '</td>
        </tr>
        <tr>
          <td colspan=5 style="text-align:left; padding-right:81px;">Dikurangi DP</td>
          <td class="num">(' . rupiah($uminvoice) . ')</td>
        </tr>';

  if ($totalclaim > 0) {
    $html .= '<tr>
                <td colspan=5 style="text-align:left; padding-right:81px;">Dikurangi Claim</td>
                <td class="num">(' . rupiah($totalclaim) . ')</td>
              </tr> 
            </tr>';
  }
  if ($nilaippn > 0) {
    $nilailainpl = $nilaiplinv * 11 / 12;
  }
  $grandtotalPL = $nilaiplinv + $ppnpl;
  $html .= '
        <tr>
          <td colspan=5 style="text-align:left; padding-right:81px;">Total Harga</td>
          <td class="num">' . rupiah($nilaiplinv) . '</td>
        </tr>
        <tr>
          <td colspan=5 style="text-align:left; padding-right:81px;">Dasar Pengenaan Pajak Nilai Lain</td>
          <td class="num">' . rupiah($nilailainpl) . '</td>
        </tr>
        <tr>
          <td colspan=5 style="text-align:left; padding-right:81px;">PPN 12% Dari Dasar Pengenaan Pajak</td>
          <td class="num">' . rupiah($ppnpl) . '</td>
        </tr>
        <tr style="font-weight: bold;">
          <td colspan=5 style="text-align:left; padding-right:81px;">Jumlah Yang Harus Dibayar</td>
          <td class="num">' . rupiah($grandtotalPL) . '</td>
        </tr>
        <tr style="background-color: #d3effa;">
          <td colspan=6 style="text-align:left; padding-right:1px;">
            <p><b>Terbilang: </b></p>
            <br> <p align="center" style="margin-top:-10px; padding:0;"><i>' . terbilang($grandtotalPL) . '</i></p>
          </td> 
        </tr>';
}
$html .= '<tr>
          <td colspan=6 style="text-align:center; padding-right:81px;">Pembayaran Mohon Ditransfer Ke Rekening :<br>
          ' . $namabank . ' CABANG ' . $cabang . '<br> 
          <b>A/N: ' . $atasnama . '</b><br>
          <b>A/C : ' . $rekening . '</b>
          </td> 
        </tr>
        <tr>
          <td colspan="4" style="vertical-align: top; padding:0;">
            <div class="catatanbawah">
              <div class="meta">
                <p><b>Catatan :</b></p>
                <ol>
                  <li>Invoice sudah dianggap sah apabila sudah masuk dalam rekening ' . $atasnama . '</li>
                  <li>' . $arrKet[$jenisinvoice] . '</li>
                </ol>
              </div>
            </div>
          </td>

          <!-- Kolom kanan -->
          <td colspan="2" style="vertical-align: top; padding:0;">
            <div class="catatanbawah tanda-tangan">
              <p class="tanggal">' . tanggalbulan($tanggalinvoice) . '</p>
              <div class="ttd-area">
                <p class="hormat"><b>Hormat Kami</b></p>
                <div class="ruang-ttd"></div>
                <p class="hormat"><b>(' . $namakaryawan . ')</b></p>
                <p class="hormat">Tanda Tangan & Nama Terang</p>
              </div>
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="6">Lembar Ke 1 : Customer/Pelanggan <br/>
          Lembar Ke 2 : Bagian Keuangan <br/>
          Lembar Ke 3 : Bagian Pajak 
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</body>
</html>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream($table, array("Attachment" => 0));
