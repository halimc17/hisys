<?php
require_once 'master_validation.php';
require_once 'config/connection.php';
require_once 'lib/nangkoelib.php';
include_once 'lib/zLib.php';
require_once 'lib/fpdf.php';
include_once 'lib/zMysql.php';
include_once 'lib/terbilang.php';
require_once 'dompdf/autoload.inc.php';

use Dompdf\Dompdf;
// use Dompdf\Options; 
$nosk = $_GET['nosk'];
$tipe = strtoupper(substr($nosk, 4, 2));

//=============
//create Header
$optPtx = makeOption($dbname,'organisasi','kodeorganisasi,induk');
$namakota = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
// $res->setFetchMode(PDO::FETCH_OBJ);
// while ($bar = $res->fetch()) {
//     $ke=$optPtx[$bar->kekodeorg];
//     $tglsk=$bar->tanggalsk;
// }
$str = "select * from " . $dbname . ".sdm_riwayatjabatan where nomorsk='" . $nosk . "'";
$res= fetchData($str);
foreach ($res as $bar) {
    $ke=$optPtx[$bar['kekodeorg']];
    $tglsk=$bar['tanggalsk'];
}
$namakaryawan=getKary($bar['karyawanid'], "namakaryawan");
$nikkaryawan=getKary($bar['karyawanid'], "nik");
$jabatankaryawan=getKary($bar['karyawanid'], "kodejabatan");
$departemen=getKary($bar['karyawanid'], "bagian");


$logoPath = 'images/logo/'.$ke.'.jpg';

$html = '
<style>
    @page { margin: 20mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; line-height: 0.8; }
    table { border-collapse: collapse; width: 100%; }
    td, th { padding: 6px 4px; vertical-align: top; }
    .bordered td, .bordered th { border: 1px solid #000; }
    .center { text-align: center; }
    .bold { font-weight: bold; }
    .field { border-bottom: 1px solid #000; min-height: 14px; }
    .spacer { height: 1px; }
</style>

<!-- HEADER -->
<table style="width:100%; margin-bottom:5px; line-height:0.5;" border=0>
    <tr>
        <td width="10%" align="center" valign="middle"><img src="'.$logoPath.'" style="height:60px;width:100px; vertical-align:middle;"></td>
        <td width=45%;> </td> 
        <td>
            <table style="width:auto; text-align:left; border-collapse:collapse; line-height:0.1;">
                <tr>
                    <td style="padding-right:5px;">No Doc</td>
                    <td style="padding-right:5px;">:</td>
                    <td>'.$nosk.'</td>
                </tr>
                <tr>
                    <td>Rev</td>
                    <td>:</td>
                    <td>'.$rev.'</td>
                </tr>
                <tr>
                    <td>Update</td>
                    <td>:</td>
                    <td>'.tanggalbulan($tglsk,2).'</td>
                </tr>
            </table>
        </td> 
    </tr>
</table>

<table class="bordered" style="margin-bottom:8px;">

    <tr>
        <th align=center colspan=2 style=" background-color:#ffc000;">FORM REKOMENDASI MUTASI,PROMOSI,DEMOSI</th> 
        <td style="">Tanggal Pengajuan :</td> 
    </tr>
    <tr>
        <th align=center style="width:25%;">IDENTITAS</th>
        <th align=center style="width:25%;">KARYAWAN YANG DIAJUKAN</th>
        <th align=center style="width:25%;">ATASAN YANG MENGAJUKAN</th>
     </tr>
    <tr>
        <td>Nama</td>
        <td class="field">'.$namakaryawan.'</td>
        <td></td>
     </tr>
    <tr>
        <td>NIK</td>
        <td class="field">'.$nikkaryawan.'</td>
        <td></td>
     </tr>
    <tr>
        <td>Jabatan</td>
        <td class="field">'.getNamaJabatan($jabatankaryawan).'</td>
        <td></td>
     </tr>
    <tr>
        <td>Lokasi Penempatan</td>
        <td class="field">Jakarta</td>
        <td></td>
     </tr>
    <tr>
        <td>Divisi / Departemen</td>
        <td class="field">'.$departemen.'</td>
        <td></td>
     </tr>
</table>
 
<div class="bold" style="margin-bottom:4px;">Tabel Penilaian **)</div>
<table class="bordered" style="margin-bottom:8px;">
    <tr>
        <th align=center style="background-color:#ffc000;"width:2%;">No</th>
        <th align=center style="background-color:#ffc000;"width:40%;">Komponen Penilaian</th>
        <th align=center style="background-color:#ffc000;"width:10%;">Tahun</th>
        <th align=center style="background-color:#ffc000;"width:10%;">Nilai</th>
        <th style="width:35%;">Kategori dan Range Rata - Rata Nilai <br> (Centang di kotak sesuai total nilai)</th>
    </tr>
    <tr>
        <td class="center">1</td>
        <td>Performance Appraisal</td>
        <td></td>
        <td></td>
        <td style="text-align:left; vertical-align:middle;">
            <label style="display: flex; align-items: center; gap: 4px;">
                <span style="display:inline-block; width:12px; height:12px; border:1px solid #000;"></span>
                <span style="display:inline-block;padding-left:18px;"><b>Exceed Target = 95-100</b></span>
            </label>
        </td>
    </tr>
    <tr>
        <td class="center">2</td>
        <td>Hasil Assessment</td>
        <td></td>
        <td></td>
        <td style="text-align:left; vertical-align:middle;">
            <label style="display: flex; align-items: center; gap: 4px;">
                <span style="display:inline-block; width:12px; height:12px; border:1px solid #000;"></span>
                <span style="display:inline-block;padding-left:18px;"><b>Meet Target = 80-95</b></span>
            </label>
        </td>
    </tr>
    <tr>
        <td class="center">3</td>
        <td>Nilai Panel</td>
        <td></td>
        <td></td>
        <td style="text-align:left; vertical-align:middle;">
            <label style="display: flex; align-items: center; gap: 4px;">
                <span style="display:inline-block; width:12px; height:12px; border:1px solid #000;"></span>
                <span style="display:inline-block;padding-left:18px;"><b>Need Improvement = 60-79</b></span>
            </label>
        </td>
    </tr>
    <tr>
        <td class="center"></td>
        <td></td>
        <td></td>
        <td></td>
        <td style="text-align:left; vertical-align:middle;">
            <label style="display: flex; align-items: center; gap: 4px;">
                <span style="display:inline-block; width:12px; height:12px; border:1px solid #000;"></span>
                <span style="display:inline-block;padding-left:18px;"><b>Below Target = 21-40</b></span>
            </label>
        </td>
    </tr>
    <tr> 
        <td height=35px; colspan=3 style="text-decoration: underline; font-weight: bold;">Kekuatan :</td>
        <td colspan=2 style="text-decoration: underline; font-weight: bold;">Kelemahan :</td>
    </tr>
<tr> 
    <td align=center colspan="2">Tingkat Pendidikan: <br/> (SMA / S1 / S2 *)</td>
    <td colspan="3">
        Asal Sekolah/Universitas:<br>
        Jurusan:
        <span style="margin-left: 250px;">IPK:</span>
    </td> 
</tr>

</table>

<!-- RIWAYAT --> 
<table class="bordered" style="margin-bottom:8px;">
    <tr>
        <th align=center colspan=3>Catatan Riwayat Karyawan</th>
        <th align=center colspan=3>Riwayat Perubahan </th>
        <th align=center>Diajukan Oleh,</th>
    </tr>
    <tr>
        <th align="center" valign="middle">Uraian Masa Kerja </th>
        <th align="center" valign="middle">Tgl</th>
        <th align="center" valign="middle">Masa Kerja</th>
        <th align="center" valign="middle">No</th>
        <th align="center" valign="middle">Jenis Perubahan</th>
        <th align="center" valign="middle">Tgl-Thn</th>
        <th rowspan="4" valign="middle"></th>
    </tr>
    <tr>
        <td class="left">Total Masa Kerja</td>
        <td></td>
        <td></td>
        <td align=center>1</td>
        <td></td>
        <td></td> 
    </tr>  
    <tr>
        <td>Periode THL</td>
        <td class="center"></td>
        <td></td>
        <td align=center>2</td>
        <td></td>
        <td></td>
    </tr>  
    <tr>
        <td>Periode PKWT</td>
        <td class="center"></td>
        <td></td>
        <td align=center>3</td>
        <td></td>
        <td></td>
    </tr>  
    <tr>
        <td>Periode Tetap</td>
        <td class="center"></td>
        <td></td>
        <td align=center>4</td>
        <td></td>
        <td></td>
        <td align=center><b>User</b></td>
    </tr>  
 
</table>

<!-- PENYESUAIAN -->
<div class="bold">Tabel Penyesuaian **)</div>
<table class="bordered" style="margin-bottom:8px;">
    <tr  style="background-color:#ffc000;">
        <th align=center style="width:5%;">No</th>
        <th align=center style="width:35%;">Uraian Pendapatan</th>
        <th align=center style="width:20%;">Saat ini</th>
        <th align=center style="width:20%;">Yang Diusulkan</th>
        <th align=center style="width:20%;">Keterangan</th>
    </tr>
    <tr>
        <td class="center">1</td>
        <td>Gaji Pokok</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="center">2</td>
        <td>Tunjangan Lokasi</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="center">3</td>
        <td>Tunjangan Lain-lain</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="center">4</td>
        <td>Tunjangan Komunikasi</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="center">5</td>
        <td>Tunjangan Masa Kerja</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="center">6</td>
        <td>Tunjangan...</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="center">7</td>
        <td>Kelas Jabatan</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
</table>

<div style="margin-bottom:8px;">
    Berdasarkan hasil penilaian dan catatan riwayat karyawan dengan ini mengusulkan perubahan status karyawan diatas untuk di: <b>Promosi / Demosi/ Mutasi / PKWT/ Tetap / Lainnya:__*)</b> Sebagai ___________________________________________________
</div>
<div style="border-bottom:2px solid #000; height:40px; margin-bottom:8px;">Alasan:</div>

<!-- TANDA TANGAN -->
<table style="width:100%; text-align:center; margin-top:20px; border:1px solid #000; border-collapse:collapse;">
    <tr>
        <td style="border:none;">Diperiksa Oleh,<br><br><br><br>
            <b>Furqon Mazka</b><br>
            <span>HCBP</span>
        </td>
        <td style="border:none;">Disetujui Oleh,<br><br><br><br>
            <b>Monika Elza Trianda</b><br>
            <span>HHR Strategic Manager</span>
        </td>
        <td style="border:none;">Disetujui Oleh,<br><br><br><br>
            <b>Fajria Putri Adriani</b><br>
            <span>Director Of HCM</span>
        </td>
        <td style="border:none;">Disetujui Oleh,<br><br><br><br>
            <b>Nadia Putri Asrini Jenie</b><br>
            <span>Direktur Utama</span>
        </td>
    </tr>
</table>

<div style="margin-top:8px; font-size:11px;">*) Lingkari yang dipilih. <br> **) Diisi Oleh HCBP</div>
';
 


$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('Legal', 'portrait');
$dompdf->render();

// if ($urlefil == '0') {
    $dompdf->stream("Print_SK", array("Attachment" => false));
// } else {
//     file_put_contents($urlefil, $dompdf->output());
// }

// $options = new Options();
// $options->set('isHtml5ParserEnabled', true);
// $options->set('isFontSubsettingEnabled', true);
// $options->set('defaultFont', 'DejaVu Sans');
// $dompdf = new Dompdf($options);
// $dompdf->setPaper('A4', 'portrait');
// $dompdf->addHeader($kodept);
// $dompdf->loadHtml($tab);
// $dompdf->render();
// $dompdf->stream($table, array("Attachment" => 0));
