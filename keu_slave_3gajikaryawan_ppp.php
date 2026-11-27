<?php
# CABANG PPP dari keu_slave_3gajikaryawan.php (salinan awal, belum ada perbedaan). Dipanggil otomatis oleh js/keu_3posting.js (tujuanProses)
# hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_3gajikaryawan.php. Perubahan khusus PPP tandai "#PPP".
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
require_once('lib/zAlokasiGajiPPP.php'); #PPP: tujuan alokasi komponen

#==========================================konfigurasi database
# KBNB0	Gaji BTL Kebun/Pabrik
# KBNB1	Premi/Lebur BTL Kebun/Pabrik
# KBNB2	Tunjangan Lain
# KBNB3	THR BTL
# KBNB4	Bonus BTL
# KBNB5	Pengobatan BTL
# VHCG0	Gaji Kendaraan/A.Berat
# VHCG1	Biaya Lebur Kendaraan/A.Berat
# VHCG2	Biaya Tunjangan Lain Kend./A.Berat
# VHCG3	THR Kend./A.Berat
# VHCG4	Bonus Kend. A.Berat
# VHCG5	Pengobatan Kend./A.Berat
# WSG0	Biaya Gaji Bengkel
# WSG1	Biaya Premi/Lembur Bengkel
# WSG2	Tunjangan Lain Bengkel
# WSG3	THR Traksi
# WSG4	Bonus TraksiTrak
# WSG5	Pengobatan Traksi
# KBNL0	Biaya pengawasan BBT
# KBNL1	Biaya pengawasan TBM
# KBNL2	Biaya pengawasan TM
# KBNL3	Biaya Pengawasan Panen
#============================================konfigurasi database

#==Komfigurasi komponen gaji
# 1	Gaji Pokok
# 2	Tunjangan Jabatan
# 14	Rapel
# 16	Premi Pengawasan
# 21	Klaim Pengobatan
# 26	Bonus
# 27	Tunjangan Fasilitas
# 28	THR
# 30	Tunjangan Profesi
# 31	Tunjangan Masa Kerja
# 32	Premi
# 33	Lembur
# 34	Penalti
#

$param = $_POST;
#PPP: format unit dan periode divalidasi sebelum dipakai di query
$pesanParam = validasiParamGajiPPP($param);
if ($pesanParam != '') {
	exit($pesanParam);
}
$tahunbulan = implode("", explode('-', $param['periode']));
#ambil periode akuntansi
$kodeorg  = $param['kodeorg'];
$str = "select tanggalmulai,tanggalsampai from " . $dbname . ".sdm_5periodegaji 
    where kodeorg='" . $kodeorg . "'
    and periode='" . $param['periode'] . "'";
$tgmulai = '';
$tgsampai = '';
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  $tgsampai   = $bar->tanggalsampai;
  $tgmulai    = $bar->tanggalmulai;
}
if ($tgmulai == '' || $tgsampai == '')
  exit("Error: Accounting period is not registered");

/**
 * Validasi apakah proses gaji telah ditutup
 */
$qGaji = selectQuery($dbname, 'sdm_5periodegaji', 'sudahproses,jenisgaji', "kodeorg='" . $kodeorg . "' and periode='" . $param['periode'] . "'");
$resGaji = fetchData($qGaji);
$optGaji = array();
foreach ($resGaji as $row) {
  $optGaji[$row['jenisgaji']] = $row['sudahproses'];
}

// 1. Validasi Empty
if (empty($optGaji)) exit('Warning: Periode Gaji ' . $param['periode'] . " belum ada");
if (!isset($optGaji['H'])) exit('Warning: Periode Gaji Harian ' . $param['periode'] . " belum ada");
if (!isset($optGaji['B'])) exit('Warning: Periode Gaji Bulanan ' . $param['periode'] . " belum ada");

// 2. Validasi Proses Gaji
if ($optGaji['H'] == 0) exit('Warning: Periode Gaji Harian ' . $param['periode'] . " belum ditutup");
if ($optGaji['B'] == 0) exit('Warning: Periode Gaji Bulanan ' . $param['periode'] . " belum ditutup");

// $whereKary = " AND karyawanid='0000003686'";

#---------------------------------------------------------------
#ambil potongan HK
#---------------------------------------------------------------
#1. check component : Potongan HK(idkomponen:37) di mapping potongan karyawan
$str = "select count(idkomponen) as counts from " . $dbname . ".keu_5pengakuanpotongan where idkomponen=37";
$qry = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_OBJ);
$res = $qry->fetch();
$count37 = $res->counts;

#2. check component : Denda dibawah jam kerja(idkomponen:41) di mapping potongan karyawan
$str = "select count(idkomponen) as counts from " . $dbname . ".keu_5pengakuanpotongan where idkomponen=41";
$qry = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_OBJ);
$res = $qry->fetch();
$count41 = $res->counts;

$str = "select sum(jumlah) as jumlah,idkomponen,karyawanid from " . $dbname . ".sdm_gajidetail_vw 
       where kodeorg like '" . $kodeorg . "%' 
       and idkomponen in(37) and periodegaji='" . $param['periode'] . "' {$whereKary} group by idkomponen,karyawanid";
$potx = array();
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
if ($count37 < 1) {
  while ($barx = $res->fetch()) {
    $potx[$barx->karyawanid] = $barx->jumlah;
  }
}

$str = "select sum(jumlah) as jumlah,idkomponen,karyawanid from " . $dbname . ".sdm_gajidetail_vw 
       where kodeorg like '" . $kodeorg . "%' 
       and idkomponen in(41) and periodegaji='" . $param['periode'] . "' {$whereKary} group by idkomponen,karyawanid";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
if ($count41 < 1) {
  while ($barx = $res->fetch()) {
    if (isset($potx[$barx->karyawanid])) {
      $potx[$barx->karyawanid] += $barx->jumlah;
    } else {
      $potx[$barx->karyawanid] = $barx->jumlah;
    }
  }
}
#---------------------------------------------------------------
#ambil semua gaji per karyawan
#---------------------------------------------------------------
#1. Ambil gaji total per karyawan pada unit bersangkutan
$str = "select jumlah,idkomponen,karyawanid from " . $dbname . ".sdm_gajidetail_vw 
        where kodeorg like '" . $kodeorg . "%' 
        and plus=1 and periodegaji='" . $param['periode'] . "' {$whereKary} and name not like '%BPJS%'";
$gaji = array();
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  if ($bar->idkomponen == 1) {
    @$gaji[$bar->karyawanid][$bar->idkomponen] = $bar->jumlah - $potx[$bar->karyawanid]; //dikurangkan dengan potongan HK
  } else {
    @$gaji[$bar->karyawanid][$bar->idkomponen] = $bar->jumlah;
  }

  @$info[$bar->karyawanid][$bar->idkomponen] = 'GAJI';
}

#= 1.1 bpjs disini
# Kenapa di komen
# karena KSP memakai BPJS dengan format tulisan :
# BPJS JKK (-), BPJS Kesehatan (-), BPJS KESEHATAN (+) jadi like 'BPJS%' bisa
// $str="select jumlah,idkomponen,karyawanid from ".$dbname.".sdm_gajidetail_vw 
//       where kodeorg like '".$kodeorg."%' 
//       and plus=1 and periodegaji='".$param['periode']."' and name like 'BPJS%'";

# Palma Pakai yang like '%BPJS%'
$str = "select jumlah,idkomponen,karyawanid from " . $dbname . ".sdm_gajidetail_vw 
        where kodeorg like '" . $kodeorg . "%' 
        and plus=1 and periodegaji='" . $param['periode'] . "' {$whereKary} and name like '%BPJS%'";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {

  @$gaji[$bar->karyawanid][$bar->idkomponen] = $bar->jumlah;
  @$info[$bar->karyawanid][$bar->idkomponen] = 'BPJS';
}

#2 Ambil subunit setiap karyawan
// $str="select subbagian,karyawanid,namakaryawan from ".$dbname.".datakaryawan 
// where lokasitugas='".$kodeorg."'";
$str = "select subbagian,karyawanid,namakaryawan from " . $dbname . ".datakaryawan_hist 
       where lokasitugas='" . $kodeorg . "' and periodegaji='" . $param['periode'] . "' and version_type='B'";
$subunit = array();
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  $subunit[$bar->karyawanid] = $bar->subbagian;
  $namakaryawan[$bar->karyawanid] = $bar->namakaryawan;
}
#3 ambil semua organisasi yang traksi atau workshop
$str = "select distinct kodeorganisasi,tipe from " . $dbname . ".organisasi 
        where kodeorganisasi like '" . $kodeorg . "%'";
$tipe = array();
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  $tipe[$bar->kodeorganisasi] = $bar->tipe;
}

#==========================================================================================  

#= ambil daftar karyawan yang terdaftar di kebun aktifitas
$str = "select * from " . $dbname . ".kebun_aktifitas where  
		tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' and kodeorg='" . $kodeorg . "' and jurnal=1";
$res = fetchdata($str);
foreach ($res as $bar) {
  $pejabatbkm[$bar['nikmandor']] = $bar['nikmandor'];
  $pejabatbkm[$bar['nikmandor1']] = $bar['nikmandor1'];
  $pejabatbkm[$bar['nikasisten']] = $bar['nikasisten'];
  $pejabatbkm[$bar['keranimuat']] = $bar['keranimuat'];
}

// echo"<pre>";
// print_r($pejabatbkm);
// echo"</pre>";

#PPP HKE: komponen dasar upah karyawan Bulanan dijurnalkan satu kali sebagai komponen 1 (lihat gabungDasarUpahPPP)
$namaGabung = array();
gabungDasarUpahPPP($gaji, $info, $kodeorg, $param['periode'], $namaGabung);

$GJ = $gaji;
#buang karyawan yang gajinya sudah teralokasi

$str = "select karyawanid from " . $dbname . ".kebun_kehadiran_vw
          where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' 
          and unit='" . $kodeorg . "' and jurnal=1";
// echo $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  if (!in_array($bar->karyawanid, $pejabatbkm)) {
    unset($gaji[$bar->karyawanid]);
  }
  // if(in_array($bar->karyawanid],$pejabatbkm)){
  // }else{
  // unset($gaji[$bar->karyawanid]);
  // }
  // unset($gaji[$bar->karyawanid]);
}
#buang karyawan yang tanggalmasuknya > dari tanggal akhir periode
$str1 = "select karyawanid from " . $dbname . ".datakaryawan where 
           lokasitugas='" . $kodeorg . "'
           and tanggalmasuk>'" . $tgsampai . "'";
$res = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar1 = $res->fetch()) {
  unset($gaji[$bar1->karyawanid]);
}
#b. ambil prestasi kebun
$str = "select karyawanid from " . $dbname . ".kebun_prestasi_vw
          where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' 
          and unit='" . $kodeorg . "' and jurnal=1";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  if (!in_array($bar->karyawanid, $pejabatbkm)) {
    unset($gaji[$bar->karyawanid]);
  }
  // unset($gaji[$bar->karyawanid]);
}

# Get Karyawan Traksi atau Workshop
foreach ($gaji as $key => $row) {
  if (($tipe[$subunit[$key] ?? ''] ?? '') == 'TRAKSI' || ($tipe[$subunit[$key] ?? ''] ?? '') == 'WORKSHOP') {
    # Unset komponen selain BPJS plus
    foreach ($row as $val => $jlh) {
      if ($info[$key][$val] != 'BPJS') {
        unset($gaji[$key][$val]);
      }
    }
  }
}

#==========================================================================================
#ambil kendaraan atau mesin yang menempel pada orang
$str = "select vhc,karyawanid from " . $dbname . ".vhc_5operator where aktif='1'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  $ken[$bar->karyawanid] = $bar->vhc;
}
#ambil komponen gaji
$str = "select id,name from " . $dbname . ".sdm_ho_component";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  $komponen[$bar->id] = $bar->id;
  $namakomponen[$bar->id] = $bar->name;
}


#ambil gaji yang sudah teralokasi per karyawan
#++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
// Init
$potongan = array();
//and unit='".$kodeorg."'
#a.kehadiran kebun
$str = "select sum(umr) as umr, sum(insentif) as insentif,karyawanid from " . $dbname . ".kebun_kehadiran_vw
          where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' 
           and jurnal=1 group by karyawanid";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  if (!isset($potongan[$bar->karyawanid])) $potongan[$bar->karyawanid] = array(1 => 0, 32 => 0);
  $potongan[$bar->karyawanid][1] += $bar->umr; //potongan gaji pokok
  $potongan[$bar->karyawanid][32] += $bar->insentif; //potongan premi    
}
//and unit='".$kodeorg."'
#b. ambil prestasi kebun
$str = "select sum(upahkerja) as umr, sum(upahpremilebihbasis) as insentif,sum(rupiahpenalty) as penalty,
          karyawanid from " . $dbname . ".kebun_prestasi_vw
          where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' 
           and jurnal=1 group by karyawanid";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  if (!isset($potongan[$bar->karyawanid])) $potongan[$bar->karyawanid] = array(1 => 0, 32 => 0);
  $potongan[$bar->karyawanid][1] += $bar->umr - $bar->penalty; //potongan gaji pokok
  $potongan[$bar->karyawanid][32] += $bar->insentif; //potongan premi 
}

#b. ambil prestasi kebun
$str = "select sum(premikehadiran+premikesulitan) as insentif,
          karyawanid from " . $dbname . ".kebun_3premipemanen_v2
          where 5=5 and periode='" . $param['periode'] . "'group by karyawanid";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  if (!isset($potongan[$bar->karyawanid])) $potongan[$bar->karyawanid] = array(32 => 0);
  $potongan[$bar->karyawanid][32] += $bar->insentif; //potongan premi 
}

## Untuk Kutip Brondolan
$str = "select sum(rphk) as umr, sum(rppremi) as insentif,karyawanid from " . $dbname . ".kebun_3premibmtbs
          where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' 
           and posting=1 group by karyawanid";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
  if (!isset($potongan[$bar->karyawanid])) $potongan[$bar->karyawanid] = array(1 => 0, 32 => 0);
  $potongan[$bar->karyawanid][1] += $bar->umr; //potongan gaji pokok
  $potongan[$bar->karyawanid][32] += $bar->insentif; //potongan premi    
}

## Untuk VHC SPL Kehadiran
if ($param['periode'] > '2025-07') {
  $str = "select sum(umr) as umr, sum(premi) as insentif,nik from " . $dbname . ".vhc_spl_kehadiran_vw
            where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' 
             and posting=1 group by nik";
  $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
  $res->setFetchMode(PDO::FETCH_OBJ);
  while ($bar = $res->fetch()) {
    if (!isset($potongan[$bar->nik])) $potongan[$bar->nik] = array(1 => 0, 32 => 0);
    $potongan[$bar->nik][1] += $bar->umr; //potongan gaji pokok
    $potongan[$bar->nik][32] += $bar->insentif; //potongan premi    
  }
}

## GET KEGIATAN YANG TIDAK DIALOKASI GAJI DLL
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='SNALOC' and kodeorg='" . $param['kodeorg'] . "'";
$res = fetchdata($str);
$wherekegx = "";
if ($res[0]['nilai'] == '') {
  $wherekegx = "";
} else {
  $wherekegx = " and kodekegiatan in (" . $res[0]['nilai'] . ")";
}

# Add NEW
# Case : Ada Karyawan Plasma Supervisi, untuk Premi Brondolan di bentuk Jurnalnya saat Premi Pemanen / Kemandoran
# Cek apakah sudah ada Jurnal Premi Brondolan yang sudah Terbentuk
$sql = "select nik,sum(jumlah) as jumlah from " . $dbname . ".keu_jurnaldt_vw where 5=5 " . $wherekegx . " and debet > 0 and periode='" . $param['periode'] . "' and " . sqlRentangPeriode($param['periode']) . " and kodejurnal='PNN03' and nojurnal in (select nojurnal from " . $dbname . ".keu_jurnalht where kodejurnal='PNN03' and " . sqlRentangPeriode($param['periode']) . ") and noreferensi like '%PNN02%' GROUP BY nik";
$res = fetchData($sql, "OBJECT");
foreach ($res as $val):
  // if(in_array($val->nik,$pejabatbkm)): # Cek apakah Pejabat BKM / Karyawan Supervisi
  if (!isset($potongan[$val->nik])) $potongan[$val->nik] = array(32 => 0);
  $potongan[$val->nik][32] += $val->jumlah;
// endif;
endforeach;
# End
#++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

#kurangkan gaji yang ada dengan yang sudah dialokasi
$gajiblmalokasi = $GJ;
foreach ($GJ as $key => $row) {
  if (!isset($potongan[$key])) $potongan[$key] = array(1 => 0, 32 => 0);
  if (!isset($gajiblmalokasi[$key][1])) $gajiblmalokasi[$key][1] = 0;
  if (!isset($gajiblmalokasi[$key][32])) $gajiblmalokasi[$key][32] = 0;
  $gajiblmalokasi[$key][1] -= fixnan($potongan[$key][1]);
  $gajiblmalokasi[$key][32] -= fixnan($potongan[$key][32]);
}
#ambil selisih kekurangan 
$kekurangan = 0;
foreach ($gajiblmalokasi as $key) {
  foreach ($key as $row => $cell) {
    if ($cell < 0)
      $kekurangan += $cell;
  }
}


// echo"<pre>";
// print_r($potongan['0000003625']);
// echo"</pre>";

#=======================================================================================================  

#echo $kekurangan; Buang escape ini untuk mengetahui selisih gaji yang belum teralokasi 
if (empty($gaji))
  exit('Error: No Salary data found');
else {
  #PPP: informasi proses dan tujuan alokasi tiap komponen (urutan sama dengan proses jurnalnya)
  $optParamJurnal = array();
  foreach (fetchData("select jurnalid,noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal") as $barPj) {
    $optParamJurnal[$barPj['jurnalid']] = $barPj;
  }
  $nmAkunInfo = makeOption($dbname, 'keu_5akun', 'noakun,namaakun'); #PPP: semua akun, karena tujuan bisa akun pengawasan/kegiatan/transit kendaraan
  $orgpt = getindukPT($kodeorg); #PT induk unit (kunci Setup Alokasi Komponen Gaji)
  $bagianInfo = (($tipe[$kodeorg] ?? '') == 'HOLDING' || ($tipe[$kodeorg] ?? '') == 'KANWIL') ? 'GJHO' : 'KBNB';
  muatCachePPP($orgpt, $kodeorg, $param['periode']); #PPP: No Akun Pendapatan Lain dan jabatan dimuat sekali untuk semua baris (tanpa query per baris)
  #PPP: data detail karyawan untuk tampilan (histori periode kalau ada, kalau tidak data master karyawan)
  $infoKar = array();
  foreach (array_chunk(array_keys($gaji), 400) as $potong) {
    $in = "'" . implode("','", array_map('addslashes', $potong)) . "'";
    foreach (fetchData("select karyawanid,nik,namakaryawan,kodejabatan,kodegolongan,tipekaryawan,subbagian from " . $dbname . ".datakaryawan_hist
        where karyawanid in (" . $in . ") and periodegaji='" . addslashes($param['periode']) . "' and version_type='B' and lokasitugas='" . addslashes($kodeorg) . "'") as $barK) {
      $infoKar[$barK['karyawanid']] = $barK;
    }
    foreach (fetchData("select karyawanid,nik,namakaryawan,kodejabatan,kodegolongan,tipekaryawan,subbagian from " . $dbname . ".datakaryawan where karyawanid in (" . $in . ")") as $barK) {
      if (!isset($infoKar[$barK['karyawanid']])) {
        $infoKar[$barK['karyawanid']] = $barK;
      }
    }
  }
  $nmJabatanInfo = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
  $nmGolonganInfo = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
  $nmTipeKarInfo = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
  $nmOrgInfo = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

  #PPP: data kolom tujuan alokasi, supaya sama dengan jurnal Proses Akhir Bulan no 1 (keu_slave_prosesAlokasiGajiAkhirbulan_ppp.php):
  # genset Setup Premi Tetap (prosesGajiVhcPremiPPP), mandor/kerani/asisten afdeling & bibitan ke blok yang diawasi di BKM (prosesGajiAfdeling),
  # bagian Sipil (prosesGajiSipil), lalu biaya umum dengan akun di absensi/lembur dan Pekerjaan Traksi (prosesGajiKebun)
  $ctxInfo = array(
    'kodeorg' => $kodeorg,
    'periode' => $param['periode'],
    'orgpt' => $orgpt,
    'bagian' => $bagianInfo,
    'tipunit' => isset($tipe[$kodeorg]) ? $tipe[$kodeorg] : '',
    'pj' => $optParamJurnal,
    'bkm' => array(),
    'akunkeg' => array(),
    'absen' => array(),
    'lembur' => array(),
    'wakar' => array()
  );
  if ($ctxInfo['tipunit'] == 'KEBUN') {
    $pgB = fetchData("select tanggalmulai,tanggalsampai from " . $dbname . ".sdm_5periodegaji where periode='" . addslashes($param['periode']) . "' and kodeorg='" . addslashes($kodeorg) . "' and jenisgaji='B'");
    if (count($pgB) > 0) {
      $snaloc = fetchData("select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='SNALOC' and kodeorg='" . addslashes($kodeorg) . "'");
      $wherekegInfo = (count($snaloc) > 0 && $snaloc[0]['nilai'] != '') ? " and b.kodekegiatan not in (" . $snaloc[0]['nilai'] . ")" : "";
      $strBkm = "select distinct a.nikmandor,a.nikmandor1,a.keranimuat,a.nikasisten,b.kodekegiatan,a.tipetransaksi,b.kodeorg,b.kodesegment,c.luasareaproduktif,c.statusblok,
          case when a.tipetransaksi in ('TM','TBM','TB') and c.statusblok='TB' then c.lc+c.luasbloking else c.luasareaproduktif end as luasalokasi
          from " . $dbname . ".kebun_aktifitas a
          left join " . $dbname . ".kebun_prestasi_detail b on a.notransaksi=b.notransaksi
          left join " . $dbname . ".setup_blok c on b.kodeorg=c.kodeorg
          where a.kodeorg='" . addslashes($kodeorg) . "' and a.tanggal>='" . $pgB[0]['tanggalmulai'] . "' and a.tanggal<='" . $pgB[0]['tanggalsampai'] . "'" . $wherekegInfo . "
          having kodeorg is not null and luasalokasi > 0";
      $sudahBkm = array();
      foreach (fetchData($strBkm) as $barB) {
        $kunciB = $barB['kodekegiatan'] . '|' . $barB['tipetransaksi'] . '|' . $barB['kodeorg'] . '|' . $barB['kodesegment'] . '|' . $barB['luasareaproduktif'] . '|' . $barB['statusblok'] . '|' . $barB['luasalokasi'];
        foreach (array('nikmandor', 'nikmandor1', 'keranimuat', 'nikasisten') as $kolB) {
          $pj = $barB[$kolB];
          if ($pj == '' || isset($sudahBkm[$pj][$kunciB])) {
            continue;
          }
          $sudahBkm[$pj][$kunciB] = 1;
          $ctxInfo['bkm'][$pj][] = $barB;
        }
      }
      $ctxInfo['akunkeg'] = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,noakun');
    }
  }
  foreach (array_chunk(array_keys($gaji), 400) as $potong) {
    $in = "'" . implode("','", array_map('addslashes', $potong)) . "'";
    foreach (fetchData("select karyawanid,noakun,alokasi,sum(case when umr>0 then umr else 0 end) as umr,
        sum(case when ifnull(premi,0)+ifnull(insentif,0)+ifnull(insentiflibur,0)>0 then ifnull(premi,0)+ifnull(insentif,0)+ifnull(insentiflibur,0) else 0 end) as pre
        from " . $dbname . ".sdm_absensidt where tanggal like '" . addslashes($param['periode']) . "%' and karyawanid in (" . $in . ") group by karyawanid,noakun,alokasi") as $barA) {
      $ctxInfo['absen'][$barA['karyawanid']][] = $barA;
    }
    foreach (fetchData("select karyawanid,noakun,alokasi,sum(case when uangkelebihanjam>0 then uangkelebihanjam else 0 end) as lembur
        from " . $dbname . ".sdm_lemburdt where tanggal like '" . addslashes($param['periode']) . "%' and karyawanid in (" . $in . ") group by karyawanid,noakun,alokasi") as $barA) {
      $ctxInfo['lembur'][$barA['karyawanid']][] = $barA;
    }
  }

  $tab = "
        <fieldset style='margin-bottom:8px'><legend><b>Informasi Proses</b></legend>
        <div style='display:flex;flex-wrap:wrap;align-items:stretch;gap:6px;font-size:12px'>
          <div style='flex:1 1 190px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>1. Ambil gaji</b><br>Komponen gaji tiap karyawan periode " . htmlspecialchars($param['periode']) . " (unit " . htmlspecialchars($kodeorg) . ").</div>
          <div style='align-self:center;font-size:18px'>&rarr;</div>
          <div style='flex:1 1 190px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>2. Kurangi yang sudah teralokasi</b><br>Gaji yang sudah masuk lewat BKM/prestasi/kehadiran dikurangi. Kalau habis, karyawan tidak tampil.</div>
          <div style='align-self:center;font-size:18px'>&rarr;</div>
          <div style='flex:1 1 230px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>3. Tentukan akun debet</b><br>Urutan: <b>No Akun</b> Pendapatan Lain &rarr; <b>Setup</b> jabatan/komponen &rarr; <b>absensi</b> (gaji pokok, premi/lembur) &rarr; <b>Setup Bawaan</b>.</div>
          <div style='align-self:center;font-size:18px'>&rarr;</div>
          <div style='flex:1 1 210px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>4. Buat jurnal</b><br>Satu jurnal per baris. <b>Debet</b> akun biaya, <b>Kredit</b> Gaji/Upah YMH Dibayar (BPJS: hutang BPJS).</div>
        </div>
        <div style='font-size:12px;margin-top:6px;line-height:1.5'>
          &bull; Karyawan Traksi/Bengkel diproses di langkah <i>3. Gaji Karyawan Tidak Langsung - (Traksi,Bengkel)</i>.<br>
          &bull; Mandor/kerani/asisten Afdeling dan Bibitan yang tercatat di BKM: gaji (selain BPJS) dibagi ke blok yang diawasi menurut luas (Biaya Pengawasan); kolom Akun Debet menampilkan rupiah per akun.<br>
          &bull; Komponen yang tidak ada di setup memakai Setup Bawaan; komponen baru yang belum diatur dianggap Tunjangan Lain.<br>
          <span style='background:#fff3cd;padding:1px 6px;border:1px solid #e0c36a'><b>Perhatian:</b> Process ulang untuk periode yang sama menghapus jurnal gaji periode itu lalu membuatnya lagi.</span>
        </div>
        </fieldset>
        <style>
          .tombolGajiPPP { display:inline-block; padding:7px 22px; margin:0 8px 8px 0; border-radius:6px; font-size:13px; font-weight:bold; cursor:pointer; }
          #btnproses.tombolGajiPPP { background:#2e7d32; color:#fff; border:1px solid #1b5e20; }
          #btnproses.tombolGajiPPP:hover { background:#388e3c; }
          #btnproses.tombolGajiPPP:disabled { background:#9e9e9e; border-color:#757575; color:#eee; cursor:not-allowed; }
          #btnexcel.tombolGajiPPP { background:#fff; color:#2e7d32; border:1px solid #2e7d32; }
          #btnexcel.tombolGajiPPP:hover { background:#e8f5e9; }
        </style>
        <div>
          <button class='tombolGajiPPP' onclick=prosesGaji(1) id=btnproses title='Buat jurnal untuk semua baris di bawah'>&#9654; Process</button>
          <button class='tombolGajiPPP' onclick=exportTableToExcel() id=btnexcel title='Unduh tabel ini ke Excel'>&#11015; Excel</button>
        </div>
                  <table class=sortable cellpadding=5 cellspacing=1 border=0 id=mytable>
                  <thead>
                    <tr class=rowheader>
                    <th>No</th>
                    <th>Periode</th>
                    <th>NIK</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan</th>
                    <th>Golongan</th>
                    <th>Tipe Karyawan</th>
                    <th>Divisi</th>
                    <th>ID Komponen</th>
                    <th>Nama Komponen</th>
                    <th>" . $_SESSION['lang']['jumlah'] . "</th>
                    <th>" . $_SESSION['lang']['info'] . "</th>
                    <th>Jurnal</th>
                    <th>Akun Debet</th>
                    <th>Sumber Akun</th>
                    <th hidden class=kolom-sembunyi>karyawanid</th>
                    <th hidden class=kolom-sembunyi>namakaryawan</th>
                    <th hidden class=kolom-sembunyi>subbagian</th>
                    <th hidden class=kolom-sembunyi>tipeorganisasi</th>
                    <th hidden class=kolom-sembunyi>" . $_SESSION['lang']['kendaraan'] . "</th>
                    </tr>
                  </thead>
                  <tbody>";

  $no = 0;
  $ttl = 0;
  $ttlgaji = 0;
  $ttlbpjs = 0;
  foreach ($gaji as $key => $baris) {
    foreach ($baris as $val => $jlh) {

      $sken = "";
      if (abs($jlh - ($potongan[$key][$val] ?? 0)) < 0.005) {
        continue; #noise hitung (di bawah setengah sen); selisih sen tetap dijurnalkan agar tidak ada sisa alokasi
      }
      if (($tipe[$subunit[$key] ?? ''] ?? '') != 'TRAKSI' and ($tipe[$subunit[$key] ?? ''] ?? '') != 'WORKSHOP') {
        $no += 1;
        #PPP: tujuan alokasi baris ini
        try {
          $tujuanInfo = tujuanInfoBarisPPP($ctxInfo, $key, $val, $jlh - ($potongan[$key][$val] ?? 0), isset($tipe[$subunit[$key] ?? '']) ? str_replace(' ', '', $tipe[$subunit[$key] ?? '']) : '');
          if (count($tujuanInfo['akun']) == 1) {
            $akunDbInfo = $tujuanInfo['akun'][0]['akun'];
            $selAkunInfo = htmlspecialchars($akunDbInfo . ($akunDbInfo != '' ? " - " . @$nmAkunInfo[$akunDbInfo] : '(parameter jurnal belum ada)') . $tujuanInfo['akun'][0]['ket']);
          } else {
            #lebih dari satu akun debet: tampilkan rupiah per akun seperti jurnalnya
            $selAkunInfo = '';
            foreach ($tujuanInfo['akun'] as $baInfo) {
              $selAkunInfo .= ($selAkunInfo != '' ? '<br>' : '') . htmlspecialchars(($baInfo['akun'] != '' ? $baInfo['akun'] . " - " . @$nmAkunInfo[$baInfo['akun']] : '(akun belum ada)') . $baInfo['ket'] . ": " . number_format($baInfo['rp']));
            }
          }
          $selAlokasiInfo = "<td align=center>" . htmlspecialchars($tujuanInfo['jurnal']) . "</td>"
            . "<td>" . $selAkunInfo . "</td>"
            . "<td>" . htmlspecialchars($tujuanInfo['sumber']) . "</td>";
        } catch (Exception $eInfo) {
          $selAlokasiInfo = "<td colspan=3 style='color:red'>" . htmlspecialchars($eInfo->getMessage()) . "</td>";
        }
        #PPP: detail karyawan yang ditampilkan (karyawanid tidak ditampilkan, hanya disimpan tersembunyi untuk proses)
        $kr = isset($infoKar[$key]) ? $infoKar[$key] : array();
        $divisiInfo = (isset($kr['subbagian']) && $kr['subbagian'] != '') ? (isset($nmOrgInfo[$kr['subbagian']]) ? $nmOrgInfo[$kr['subbagian']] : $kr['subbagian']) : 'KANTOR';
        $tab .= "<tr class=rowcontent id='row" . $no . "'>
                        <td>" . $no . "</td>
                        <td id='periode" . $no . "'>" . $_POST['periode'] . "</td>
                        <td>" . htmlspecialchars(isset($kr['nik']) ? $kr['nik'] : '') . "</td>
                        <td>" . htmlspecialchars(isset($kr['namakaryawan']) ? $kr['namakaryawan'] : '') . "</td>
                        <td>" . htmlspecialchars(isset($kr['kodejabatan'], $nmJabatanInfo[$kr['kodejabatan']]) ? $nmJabatanInfo[$kr['kodejabatan']] : '') . "</td>
                        <td>" . htmlspecialchars(isset($kr['kodegolongan'], $nmGolonganInfo[$kr['kodegolongan']]) ? $nmGolonganInfo[$kr['kodegolongan']] : '') . "</td>
                        <td>" . htmlspecialchars(isset($kr['tipekaryawan'], $nmTipeKarInfo[$kr['tipekaryawan']]) ? $nmTipeKarInfo[$kr['tipekaryawan']] : '') . "</td>
                        <td>" . htmlspecialchars($divisiInfo) . "</td>
                        <td id='komponen" . $no . "'>" . $val . "</td>
                        <td id='namakomponen" . $no . "'>" . ($namakomponen[$val] ?? '') . "</td>
                        <td align=right id='jumlah" . $no . "'>" . ($jlh - ($potongan[$key][$val] ?? 0)) . "</td>
                        <td align=right>" . $info[$key][$val] . htmlspecialchars(($val == 1 && isset($namaGabung[$key])) ? ' (termasuk ' . implode(', ', array_map(function ($c) use ($namakomponen) { return isset($namakomponen[$c]) ? $namakomponen[$c] : $c; }, $namaGabung[$key])) . ')' : '') . "</td>
                        " . $selAlokasiInfo . "
                        <td hidden class=kolom-sembunyi id='karyawanid" . $no . "'>" . $key . "</td>
                        <td hidden class=kolom-sembunyi id='namakaryawan" . $no . "'>" . ($namakaryawan[$key] ?? '') . "</td>
                        <td hidden class=kolom-sembunyi id='subbagian" . $no . "'>" . (isset($subunit[$key]) ? $subunit[$key] : '') . "</td>
                        <td hidden class=kolom-sembunyi id='tipeorganisasi" . $no . "'>" . (isset($tipe[$subunit[$key]]) ? $tipe[$subunit[$key]] : '') . "</td>
                        <td hidden class=kolom-sembunyi id='mesin" . $no . "'>" . (isset($ken[$key]) ? $ken[$key] : '') . "</td>
                        </tr>";

        if ($info[$key][$val] == 'BPJS') {
          $ttlbpjs += ($jlh);
        } else {
          $ttlgaji += $jlh;
        }

        $ttl += $jlh;
      }
    }
  }

  $tab .= "<tr class=rowcontent>
                    <td colspan=10>Total Gaji</td>
                    <td align=right>" . number_format($ttlgaji) . "</td>
                    <td colspan=4></td></tr>";
  $tab .= "<tr class=rowcontent>
                    <td colspan=10>Total BPJS</td>
                    <td align=right>" . number_format($ttlbpjs) . "</td>
                    <td colspan=4></td></tr>";
  $tab .= "<tr class=rowcontent>
                    <td colspan=10>Total</td>
                    <td align=right>" . number_format($ttl) . "</td>
                    <td colspan=4></td></tr>";
  $tab .= "</tbody><tfoot></tfoot></table>";
  echo $tab;
}
#----------------------------------------------------------------

#PPP: tujuan jurnal satu baris (karyawan + komponen) untuk kolom Jurnal / Akun Debet / Sumber Akun di list.
#Urutan dan aturannya mengikuti keu_slave_prosesAlokasiGajiAkhirbulan_ppp.php (unit KEBUN); kalau aturan proses berubah, fungsi ini ikut diubah.
#Hasil: jurnal (kelompok jurnal), akun (daftar akun debet: akun, rp, ket), sumber (asal akun)
function tujuanInfoBarisPPP(&$ctx, $karyawanid, $komponen, $jumlah, $tipeOrg)
{
  $komponen = (int)$komponen;
  $pj = $ctx['pj'];
  $akunPj = function ($jurnalid) use ($pj) {
    return isset($pj[$jurnalid]['noakundebet']) ? $pj[$jurnalid]['noakundebet'] : '';
  };

  #1. genset di Setup Premi Tetap per Komponen (prosesGajiVhcPremiPPP): dibagi menurut hari tercatat, sisa pembulatan ke genset pertama
  if (premiTetapSetupPPP($ctx['kodeorg'], $karyawanid, $komponen)) {
    $hari = hariTraksiVhcPPP($ctx['kodeorg'], $ctx['periode'], $karyawanid);
    if (count($hari) > 0) {
      $alok = alokasiKomponenPPP('VHCG', $ctx['orgpt'], $ctx['kodeorg'], $ctx['periode'], $karyawanid, $komponen, false);
      $akun = $akunPj($alok['jurnalid']);
      $totalHari = array_sum($hari);
      $daftar = array();
      $sisa = $jumlah;
      foreach ($hari as $kodevhc => $hr) {
        $rp = floor($jumlah * $hr / $totalHari);
        $sisa -= $rp;
        $daftar[] = array('akun' => $akun, 'rp' => $rp, 'ket' => ' (' . $kodevhc . ', ' . $hr . ' hari)');
      }
      $daftar[0]['rp'] += $sisa;
      return array('jurnal' => $alok['jurnalid'], 'akun' => $daftar, 'sumber' => 'Setup Premi Tetap: genset di Pekerjaan Traksi');
    }
  }

  #2. afdeling/bibitan yang tercatat sebagai mandor/kerani/asisten di BKM (prosesGajiAfdeling), kecuali BPJS:
  # dibagi ke blok yang diawasi menurut luas, sisa pembulatan ke baris terakhir
  $komponenBpjs = array(70, 71, 72, 73, 80);
  if ($ctx['tipunit'] == 'KEBUN' && in_array($tipeOrg, array('AFDELING', 'BIBITAN')) && !in_array($komponen, $komponenBpjs) && !empty($ctx['bkm'][$karyawanid])) {
    $rows = $ctx['bkm'][$karyawanid];
    $barisPerBlok = array();
    foreach ($rows as $r) {
      $barisPerBlok[$r['kodeorg']] = (isset($barisPerBlok[$r['kodeorg']]) ? $barisPerBlok[$r['kodeorg']] : 0) + 1;
    }
    $totalLuas = 0;
    foreach ($rows as $r) {
      $totalLuas += $r['luasalokasi'] / $barisPerBlok[$r['kodeorg']];
    }
    $perAkun = array();
    $kelompok = array();
    $umum = array();
    $tporsi = 0;
    $n = count($rows);
    $blok = array();
    foreach ($rows as $i => $r) {
      $porsi = floor(($r['luasalokasi'] / $barisPerBlok[$r['kodeorg']]) / $totalLuas * $jumlah);
      $tporsi += $porsi;
      if ($i == $n - 1) {
        $porsi += $jumlah - $tporsi;
      }
      if ($porsi == 0) {
        continue;
      }
      $tipeAlok = $r['tipetransaksi'];
      if (in_array($tipeAlok, array('TM', 'TBM', 'TB')) && in_array($r['statusblok'], array('TM', 'TBM', 'TB', 'BBT'))) {
        $tipeAlok = ($r['statusblok'] == 'TB') ? 'TBM' : $r['statusblok'];
      }
      $peta = array('BBT' => 'KBNL0', 'TBM' => 'KBNL1', 'TB' => 'KBNL1', 'TM' => 'KBNL2', 'PNN' => 'KBNL3');
      if (!isset($peta[$tipeAlok])) {
        $umum[$r['tipetransaksi']] = 1;
        continue;
      }
      $group = $peta[$tipeAlok];
      $akun = $akunPj($group);
      if ($group != 'KBNL3' && $r['tipetransaksi'] != 'PNN') {
        $akunKeg = isset($ctx['akunkeg'][$r['kodekegiatan']]) ? $ctx['akunkeg'][$r['kodekegiatan']] : '';
        $pakaiAkunParameter = in_array($group, array('KBNL1', 'KBNL2')) && $akun != '' && substr($akunKeg, 0, 3) != '611';
        if (!$pakaiAkunParameter) {
          $akun = $akunKeg;
        }
      }
      $kelompok[$group] = 1;
      $perAkun[$akun] = (isset($perAkun[$akun]) ? $perAkun[$akun] : 0) + $porsi;
      $blok[$r['kodeorg']] = 1;
    }
    ksort($perAkun);
    $daftar = array();
    foreach ($perAkun as $akun => $rp) {
      $daftar[] = array('akun' => $akun, 'rp' => $rp, 'ket' => '');
    }
    ksort($kelompok);
    $sumber = 'BKM pengawasan (' . count($blok) . ' blok, dibagi luas)';
    if (count($umum) > 0) {
      $sumber .= ' [PERHATIAN: BKM jenis ' . implode(',', array_keys($umum)) . ' tidak dikenal, proses melempar ke biaya umum]';
    }
    if (count($daftar) > 0) {
      return array('jurnal' => 'ALK_WAS ' . implode(',', array_keys($kelompok)), 'akun' => $daftar, 'sumber' => $sumber);
    }
  }

  #3. bagian Sipil (prosesGajiSipil)
  if ($ctx['tipunit'] == 'KEBUN' && $tipeOrg == 'SIPIL') {
    return array('jurnal' => 'SIPL1', 'akun' => array(array('akun' => $akunPj('SIPL1'), 'rp' => $jumlah, 'ket' => '')), 'sumber' => 'Bagian Sipil (parameter jurnal SIPL1)');
  }

  #4. biaya umum (prosesGajiKebun): gaji pokok/premi/lembur (1, 14, 16, 32, 33) dibagi menurut alokasi di absensi/lembur dan Pekerjaan Traksi;
  # akun: No Akun Pendapatan Lain / Setup menang atas akun absensi; tanpa Setup = akun absensi (kosong = default parameter jurnal).
  $alok = alokasiKomponenPPP($ctx['bagian'], $ctx['orgpt'], $ctx['kodeorg'], $ctx['periode'], $karyawanid, $komponen, true);
  $group = $alok['jurnalid'];
  $akunDefault = $akunPj($group);
  if (in_array($komponen, array(1, 14, 16, 32, 33)) && ($group == 'KBNB0' || $group == 'KBNB1')) {
    $akunSetup = $alok['akundebet'];
    $akunData = function ($noakun) use ($akunDefault, $akunSetup) {
      if ($akunSetup != '') {
        return $akunSetup;
      }
      if ($noakun == '') {
        return $akunDefault;
      }
      return (strlen($noakun) > 7) ? substr($noakun, 0, 7) : $noakun; #kolom noakun bisa berisi kode kegiatan
    };
    #digabung per akun + alokasi seperti $data[akun][alokasi] di proses, supaya pembulatan rupiah sama
    $gabung = array();
    $tambah = function ($noakun, $alokasi, $nilai) use (&$gabung, $akunData, $akunSetup, $akunDefault) {
      $kunciAkun = ($akunSetup != '') ? $akunSetup : ($noakun == '' ? $akunDefault : $noakun);
      $k = $kunciAkun . '|' . $alokasi;
      if (!isset($gabung[$k])) {
        $gabung[$k] = array($akunData($noakun), 0, '');
      }
      $gabung[$k][1] += $nilai;
    };
    foreach ((isset($ctx['absen'][$karyawanid]) ? $ctx['absen'][$karyawanid] : array()) as $r) {
      $nilai = ($group == 'KBNB0') ? $r['umr'] : $r['pre'];
      if ($nilai > 0) {
        $tambah($r['noakun'], $r['alokasi'], $nilai);
      }
    }
    if ($group == 'KBNB1') {
      foreach ((isset($ctx['lembur'][$karyawanid]) ? $ctx['lembur'][$karyawanid] : array()) as $r) {
        if ($r['lembur'] > 0) {
          $tambah($r['noakun'], $r['alokasi'], $r['lembur']);
        }
      }
    }
    $data = array_values($gabung);
    $adaVhc = false;
    if ($group == 'KBNB0' || $komponen == 32) {
      $akunVhc = $akunPj($group == 'KBNB0' ? 'VHCG0' : 'VHCG1');
      if (strlen($akunVhc) == 7) {
        if (!isset($ctx['wakar'][$karyawanid])) {
          $ctx['wakar'][$karyawanid] = kehadiranWakarMandorPPP($ctx['kodeorg'], $ctx['periode'], $karyawanid);
        }
        $perVhc = array();
        foreach ($ctx['wakar'][$karyawanid] as $hkv) {
          $nilai = ($group == 'KBNB0') ? $hkv['bobotgaji'] : $hkv['premi'];
          if ($nilai > 0) {
            $perVhc[$hkv['kodevhc']] = (isset($perVhc[$hkv['kodevhc']]) ? $perVhc[$hkv['kodevhc']] : 0) + $nilai;
          }
        }
        foreach ($perVhc as $kodevhc => $nilai) {
          $data[] = array($akunVhc, $nilai, ' (' . $kodevhc . ')');
          $adaVhc = true;
        }
      }
    }
    if (count($data) > 0) {
      $total = 0;
      foreach ($data as $d) {
        $total += $d[1];
      }
      $perAkun = array();
      $sudah = 0;
      foreach ($data as $d) {
        $rp = floor($d[1] / $total * $jumlah);
        $sudah += $rp;
        $k = $d[0] . '|' . $d[2];
        if (!isset($perAkun[$k])) {
          $perAkun[$k] = array('akun' => $d[0], 'rp' => 0, 'ket' => $d[2]);
        }
        $perAkun[$k]['rp'] += $rp;
      }
      $perAkun[$k]['rp'] += $jumlah - $sudah; #sisa pembulatan ke akun terakhir
      $sumber = ($akunSetup != '') ? $alok['sumber'] . ' (dibagi alokasi absensi' . ($group == 'KBNB1' ? '/lembur' : '') . ')' : 'Akun di absensi' . ($group == 'KBNB1' ? '/lembur' : '');
      return array('jurnal' => $group . ($adaVhc ? ' + kendaraan' : ''), 'akun' => array_values($perAkun), 'sumber' => $sumber . ($adaVhc ? ' dan Pekerjaan Traksi' : ''));
    }
  }
  $akun = ($alok['akundebet'] != '') ? $alok['akundebet'] : $akunDefault;
  return array('jurnal' => $group, 'akun' => array(array('akun' => $akun, 'rp' => $jumlah, 'ket' => '')), 'sumber' => $alok['sumber']);
}
