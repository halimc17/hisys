<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$periode = $_GET['periode'];
$kodeorg = $_GET['kodeorg'];
$rs = $_GET['rs'];
$kary = $_GET['kary'];
$optJabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
$hariini = date("Y-m-d");
$tahunini = date("Y");

function getAge($tdate, $dob) {
    $age = 0;
    while ($tdate > $dob = strtotime('+1 year', $dob)) {
        ++$age;
    }
    return $age;
}

if ($periode == '')
    $periode = date('Y');

$str = "select a.*, b.*,g.tipe as tipekaryawan,c.namakaryawan,d.diagnosa as ketdiag, c.lokasitugas as loktug,c.kodejabatan, nama,
        c.jeniskelamin as sex,c.tanggalmasuk as masuk, c.tanggalkeluar as keluar, c.tanggallahir as lahir, c.subbagian as subbag,
        a.jasars as byrs,a.jasadr as bydr,a.jasalab as bylab,a.byobat as byobat,a.bypendaftaran as byadmin
        from " . $dbname . ".sdm_pengobatanht a 
        left join " . $dbname . ".sdm_5rs b on a.rs=b.id 
        left join " . $dbname . ".datakaryawan c on a.karyawanid=c.karyawanid
        left join " . $dbname . ".sdm_5diagnosa d on a.diagnosa=d.id
        left join " . $dbname . ".sdm_karyawankeluarga f on a.ygsakit=f.nomor
        left join " . $dbname . ".sdm_5tipekaryawan g on c.tipekaryawan=g.id
        where a.periode like '" . $periode . "%'
        and b.namars like '" . $rs . "%' and a.karyawanid like '" . $kary . "%' and c.lokasitugas like '" . $kodeorg . "%'
        order by a.updatetime desc, a.tanggal desc";
//echo "error".$str;
//	  and a.kodeorg='".substr($_SESSION['empl']['lokasitugas'],0,4)."'

$stream = "Laporan Klaim Pengobatan Periode " . $periode . " " . $kodeorg . " 
    <table border=1>
    <thead>
    <tr>
        <td bgcolor=#dedede>No</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['notransaksi'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['periode'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['tanggal'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['lokasitugas'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['tipekaryawan'] . "</td>   
        <td bgcolor=#dedede>" . $_SESSION['lang']['namakaryawan'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['jeniskelamin'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['usia'] . "(" . $_SESSION['lang']['tahun'] . ")</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['tanggalmasuk'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['tanggalkeluar'] . "</td>   
        <td bgcolor=#dedede>" . $_SESSION['lang']['masakerja'] . "(" . $_SESSION['lang']['tahun'] . ")</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['jabatan'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['pasien'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['nama'] . " " . $_SESSION['lang']['pasien'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['rumahsakit'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['jenisbiayapengobatan'] . "</td>
        <td bgcolor=#dedede>Biaya Rumah Sakit</td>
        <td bgcolor=#dedede>Biaya Pendaftaran</td>  
        <td bgcolor=#dedede>Biaya Lab.</td>  
        <td bgcolor=#dedede>Biaya Obat</td>  
        <td bgcolor=#dedede>Jasa Dokter</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['nilaiklaim'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['dibayar'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['perusahaan'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['karyawan'] . "</td>
        <td bgcolor=#dedede>Jamsostek</td>            
        <td bgcolor=#dedede>" . $_SESSION['lang']['diagnosa'] . "</td>
        <td bgcolor=#dedede>" . $_SESSION['lang']['keterangan'] . "</td>
    </tr>
    </thead>
    <tbody>";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$no = 0;
while ($bar = $res->fetch()) {
    $no+=1;

    $masakerja = getAge(strtotime($hariini), strtotime($bar->masuk));
    $usia = getAge(strtotime($tahunini), strtotime($bar->lahir)) + 1;

    $pasien = '';
    //get hubungan keluarga
    $stru = "select hubungankeluarga from " . $dbname . ".sdm_karyawankeluarga where nomor=" . $bar->ygsakit;
    $resu=$owlPDO->query($stru) or die(print " Gagal: ".PDOException::getMessage());
	$resu->setFetchMode(PDO::FETCH_OBJ);
	while ($baru = $resu->fetch()) {
        $pasien = $baru->hubungankeluarga;
    }
    if ($pasien == '')
        $pasien = 'AsIs';

    $stream.="<tr>
        <td>" . $no . "</td>
        <td>" . $bar->notransaksi . "</td>
        <td>" . substr($bar->periode, 5, 2) . "-" . substr($bar->periode, 0, 4) . "</td>
        <td>" . tanggalnormal($bar->tanggal) . "</td>";
    if ($bar->subbag == '')
        $stream.="<td>" . $bar->loktug . "</td>";
    else
        $stream.="<td>" . $bar->subbag . "</td>";
    $stream.="<td>" . $bar->tipekaryawan . "</td>    
        <td>" . $bar->namakaryawan . "</td>
        <td>" . $bar->sex . "</td>
        <td align=right>" . $usia . "</td>    
        <td>" . $bar->masuk . "</td>";
    if ($bar->keluar == '0000-00-00')
        $stream.="<td></td>";
    else
        $stream.="<td>" . $bar->keluar . "</td>";
    $stream.="<td align=right>" . $masakerja . "</td>  
        <td>" . $optJabatan[$bar->kodejabatan] . "</td>
        <td>" . $pasien . "</td>
        <td>" . $bar->nama . "</td>
        <td>" . $bar->namars . "[" . $bar->kota . "]" . "</td>
        <td>" . $bar->kodebiaya . "</td>
        <td align=right>" . number_format($bar->byrs, 2, '.', ',') . "</td>
        <td align=right>" . number_format($bar->byadmin, 2, '.', ',') . "</td>
        <td align=right>" . number_format($bar->bylab, 2, '.', ',') . "</td>
        <td align=right>" . number_format($bar->byobat, 2, '.', ',') . "</td>
        <td align=right>" . number_format($bar->bydr, 2, '.', ',') . "</td>        
        <td align=right>" . $bar->totalklaim . "</td>
        <td align=right>" . $bar->jlhbayar . "</td>
        <td align=right>" . $bar->bebanperusahaan . "</td>
        <td align=right>" . $bar->bebankaryawan . "</td>
        <td align=right>" . $bar->bebanjamsostek . "</td>            
        <td>" . $bar->ketdiag . "</td>
        <td>" . $bar->keterangan . "</td>
    </tr>";
}
$stream.="</tbody>
    <tfoot>
    </tfoot>
    </table>";
//write exel   
$nop_ = "LaporanKlaimPengobatan-" . $periode . $kodeorg . '_' . $method . '_';
if (strlen($stream) > 0) {
    if ($handle = opendir('tempExcel')) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && $file != "index.html") {
                @unlink('tempExcel/' . $file);
            }
        }
        closedir($handle);
    }
    $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
    if (!fwrite($handle, $stream)) {
        echo "<script language=javascript1.2>
            parent.window.alert('Cant convert to excel format');
            </script>";
        exit;
    } else {
        echo "<script language=javascript1.2>
            window.location='tempExcel/" . $nop_ . ".xls';
            </script>";
    }
    fclose($handle);
}
?>
