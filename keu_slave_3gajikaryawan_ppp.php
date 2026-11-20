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
  $nmAkunInfo = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun like '7%' or noakun like '21%'");
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

  $tab = "
        <fieldset style='margin-bottom:8px'><legend><b>Informasi Proses</b></legend>
        <div style='display:flex;flex-wrap:wrap;align-items:stretch;gap:6px;font-size:12px'>
          <div style='flex:1 1 190px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>1. Ambil gaji</b><br>Komponen gaji tiap karyawan periode " . htmlspecialchars($param['periode']) . " (unit " . htmlspecialchars($kodeorg) . ").</div>
          <div style='align-self:center;font-size:18px'>&rarr;</div>
          <div style='flex:1 1 190px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>2. Kurangi yang sudah teralokasi</b><br>Gaji yang sudah masuk lewat BKM/prestasi/kehadiran dikurangi. Kalau habis, karyawan tidak tampil.</div>
          <div style='align-self:center;font-size:18px'>&rarr;</div>
          <div style='flex:1 1 230px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>3. Tentukan akun debet</b><br>Urutan: <b>absensi</b> (gaji pokok, premi/lembur) &rarr; <b>No Akun</b> Pendapatan Lain &rarr; <b>Setup</b> jabatan/komponen &rarr; <b>Setup Bawaan</b>.</div>
          <div style='align-self:center;font-size:18px'>&rarr;</div>
          <div style='flex:1 1 210px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>4. Buat jurnal</b><br>Satu jurnal per baris. <b>Debet</b> akun biaya, <b>Kredit</b> Gaji/Upah YMH Dibayar (BPJS: hutang BPJS).</div>
        </div>
        <div style='font-size:12px;margin-top:6px;line-height:1.5'>
          &bull; Karyawan Traksi/Bengkel diproses di langkah <i>3. Gaji Karyawan Tidak Langsung - (Traksi,Bengkel)</i>.<br>
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
      if (($tipe[$subunit[$key] ?? ''] ?? '') != 'TRAKSI' and ($tipe[$subunit[$key] ?? ''] ?? '') != 'WORKSHOP') {
        $no += 1;
        #PPP: tujuan alokasi baris ini
        try {
          $alokInfo = alokasiKomponenPPP($bagianInfo, $orgpt, $kodeorg, $param['periode'], $key, $val, true);
          $pjInfo = isset($optParamJurnal[$alokInfo['jurnalid']]) ? $optParamJurnal[$alokInfo['jurnalid']] : array('noakundebet' => '', 'noakunkredit' => '');
          $akunDbInfo = ($alokInfo['akundebet'] != '') ? $alokInfo['akundebet'] : $pjInfo['noakundebet'];
          $sumberInfo = $alokInfo['sumber'];
          $selAlokasiInfo = "<td align=center>" . htmlspecialchars($alokInfo['jurnalid']) . "</td>"
            . "<td>" . htmlspecialchars($akunDbInfo . ($akunDbInfo != '' ? " - " . @$nmAkunInfo[$akunDbInfo] : '(parameter jurnal belum ada)')) . "</td>"
            . "<td>" . htmlspecialchars($sumberInfo) . "</td>";
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
                        <td align=right>" . $info[$key][$val] . "</td>
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
