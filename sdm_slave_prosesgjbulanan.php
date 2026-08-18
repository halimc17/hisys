<?php
error_reporting(0);
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$proses = $_GET['proses'];
$param = $_POST;
$namakar = array();
$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

$method = checkPostGet('method', '');
$tgl1 = tanggalsystemn(checkPostGet('tgl1', ''));
$tgl2 = tanggalsystemn(checkPostGet('tgl2', ''));
$kodeorg = checkPostGet('unit', '');

$getPT = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
$tpkar = " and b.sistemgaji='Bulanan' and b.tipekaryawan != 0";
$tpkar1 = " and sistemgaji='Bulanan' and tipekaryawan != 0";
$tpkar2 = " sistemgaji='Bulanan' and tipekaryawan != 0";

$sStr = selectQuery($dbname, "organisasi", "induk", "kodeorganisasi='" . $param['kodeorg'] . "'");
$qStr = fetchData($sStr);
$DMA = false;
if ($qStr[0]['induk'] != 'PPP') {
	$DMA = true;
}

# Get Period Range
$qPeriod = selectQuery($dbname, 'sdm_5periodegaji', 'tanggalmulai,tanggalsampai', "periode='" . $param['periodegaji'] . "' and kodeorg='" . $param['kodeorg'] . "' and jenisgaji='B'");
$resPeriod = fetchData($qPeriod);
@$tanggal1 = $resPeriod[0]['tanggalmulai'];

$str = "select karyawanid,nik,namakaryawan,nourut from " . $dbname . ".datakaryawan_hist where approval_status='9'  and lokasitugas = '" . $param['kodeorg'] . "' and periodegaji ='" . $param['periodegaji'] . "' and sistemgaji='Bulanan' ";
$res = fetchdata($str);
if (count($res) > 0) {
	$datatmpl = "";
	$nodasa = 0;
	foreach ($res as $brs => $val) {
		$nodasa += 1;
		$sAkhir = "select * from " . $dbname . ".approval where notransaksi='" . $val['nourut'] . "' and status='0'  order by level desc limit 1";
		$rAkhir = fetchData($sAkhir);
		$optnm = makeOption($dbname, "datakaryawan", "karyawanid,namakaryawan", "karyawanid='" . $rAkhir[0]['karyawanid'] . "'");
		$nmkary = $optnm[$rAkhir[0]['karyawanid']];
		$datatmpl .= $nodasa . ". " . $val['nik'] . " - " . $val['namakaryawan'] . " - " . $nmkary . "<br>";
	}
	exit("Warning : Masih terdapat perubahan/buat baru datakaryawan pada periode ini yang belum di approved<br>No . NIK - Nama - Penyetuju Terakhir :<br>" . $datatmpl . "");
}

$str = "select karyawanid,nik,namakaryawan,nourut from " . $dbname . ".datakaryawan_hist where approval_status='7'  and lokasitugas = '" . $param['kodeorg'] . "' and periodegaji ='" . $param['periodegaji'] . "' and tipekaryawan='" . $param['tipekar'] . "' ";
$res = fetchdata($str);
if (count($res) > 0) {
	exit("Warning : Masih terdapat datakaryawan pada periode ini yang belum di posting");
}


#cek tutup atau belum periode gaji
$sCekPeriode = "select distinct * from " . $dbname . ".sdm_5periodegaji where periode='" . $param['periodegaji'] . "' 
              and kodeorg='" . $param['kodeorg'] . "' and sudahproses=1 and jenisgaji='B'";
$qCekPeriode = $owlPDO->query($sCekPeriode) or die(print " Gagal: " . PDOException::getMessage());
$qCekPeriode->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($qCekPeriode);
if ($numrows > 0) {
	$aktif2 = false;
} else {
	$aktif2 = true;
}

if (!$aktif2 and $method != 'estgaji') {
	exit("Payroll period has been closed");
} elseif (!$aktif2) {
	echo "close";
	exit();
}


#ambil datakaryawan
$query = "select * from " . $dbname . ".datakaryawan a where 1=1 " . $tpkar1 . " and lokasitugas='" . $param['kodeorg'] . "' and (tanggalkeluar>='" . $tanggal1 . "' or tanggalkeluar='0000-00-00') and alokasi in ('0','1')  group by a.karyawanid";
$res = fetchdata($query);
foreach ($res as $val) {
	$datakaryawan[$val['karyawanid']] = $val['karyawanid'];
}

#ambil data dari hist
$datahist = array();
$str = "select karyawanid from " . $dbname . ".datakaryawan_hist where 1=1 " . $tpkar1 . " and alokasi in ('0','1') and approval_status='8' and version_type='B' and lokasitugas='" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "'";
$res = fetchdata($str);
$jumlahhist = count($res);
foreach ($res as $val) {
	$datahist[$val['karyawanid']] = $val['karyawanid'];
}

$jumlahkaryhist = 0;
$str = "select count(karyawanid) as jlh from " . $dbname . ".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' ";
$res = fetchdata($str);
$jumlahkaryhist = $res[0]['jlh'];
if ($res[0]['jlh'] == 0) {
	exit("Data karyawan tidak ditemukan, silahkan lakukan POSTING melalaui menu : SDM - Transaksi - Data Karyawan.");
}

// $jumlahkarydt=0;
// $str = "select count(karyawanid) as jlh from ".$dbname.".datakaryawan where lokasitugas='".$param['kodeorg']."'"; 
// $res = fetchdata($str);
// $jumlahkarydt=$res[0]['jlh'];


// if($jumlahkarydt != $jumlahkaryhist){
// 	exit("Ada data karyawan baru, silahkan lakukan POSTING ulang melalaui menu : SDM - Transaksi - Data Karyawan.<br>".$jumlahkarydt." - ".$jumlahkaryhist."");
// }

#periksa apakah sudah tutup buku
$str = "select * from " . $dbname . ".setup_periodeakuntansi where periode='" . $param['periodegaji'] . "' and 
      kodeorg='" . $param['kodeorg'] . "' and tutupbuku=1";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
$numrows = owlBaris($res);

if ($numrows > 0) {
	$aktif = false;
} else {
	$aktif = true;
}

if (!$aktif and $method != 'estgaji') {
	exit("Accounting perid has been closed");
} elseif (!$aktif) {
	echo "close";
}

# Get Period Range
$qPeriod = selectQuery($dbname, 'sdm_5periodegaji', 'tanggalmulai,tanggalsampai', "periode='" . $param['periodegaji'] . "' 
          and kodeorg='" . $param['kodeorg'] . "' and jenisgaji='B'");
$resPeriod = fetchData($qPeriod);
$tanggal1 = $resPeriod[0]['tanggalmulai'];
$tanggal2 = $resPeriod[0]['tanggalsampai'];

if ($method == 'estgaji') {
	$tanggal1 = $tgl1;
	$tanggal2 = $tgl2;
}

# === hitung selisih hari
$t1     = $tanggal1 . " 00:00:01"; #awal
$t2     = $tanggal2 . " 23:59:59"; #sampai
$endd   = strtotime($t2);
$startd = strtotime($t1);
$jlhhari = round(abs($endd - $startd) / 60 / 60 / 24);

$dakarbulanan = 0;
$str = "select karyawanid from " . $dbname . ".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "'";
$res = fetchdata($str);
if (count($res) == 0) {
	exit("Data karyawan tidak ditemukan.");
	//$dakarbulanan=1;
}
#2. Get Karyawan bulanan yang penggajian=bulanan dan alokasi in ('0','1')
$query1 = "select a.tanggallahir, a.nik,a.karyawanid, a.kodecatu,a.tmkjamsostek,statuspajak,tipekaryawan,namakaryawan,jms,bpjs,pensiun, lokasitugas,tanggalkeluar, a.jumlahtanggungan as jmltanggungan,a.kodecatu as kodecatu from " . $dbname . ".datakaryawan_hist a where 1=1 " . $tpkar1 . " and lokasitugas='" . $param['kodeorg'] . "' and (tanggalkeluar>='" . $tanggal1 . "' or tanggalkeluar='0000-00-00') and alokasi in ('0','1') and ( tanggalmasuk<='" . $tanggal2 . "' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and a.periodegaji='" . $param['periodegaji'] . "' and a.version_type='B' group by a.karyawanid order by a.namakaryawan asc";
// echo $query1;
$absRes = fetchData($query1);
# Error empty karyawan
if (empty($absRes)) {
	// echo "Error : There is no prsence(kehadiran) on this period";
	exit("Data karyawan tidak ditemukan, silahkan lakukan POSTING melalaui menu : SDM - Transaksi - Data Karyawan.");
}

$id = array();
$idaaaa = '';
foreach ($absRes as $row => $kar) {
	$karydidatakary[$kar['karyawanid']] = $kar['karyawanid'];
	$id[$kar['karyawanid']][] = $kar['karyawanid'];
	$namakar[$kar['karyawanid']] = $kar['namakaryawan'];
	$statuspajak[$kar['karyawanid']] = $kar['statuspajak'];
	$kodecatu[$kar['karyawanid']] = $kar['kodecatu'];
	$nikkary[$kar['karyawanid']] = $kar['nik'];
	$lokasitugas[$kar['karyawanid']] = $kar['lokasitugas'];
	$tglkeluar[$kar['karyawanid']] = $kar['tanggalkeluar'];
	$tmkjamsostek[$kar['karyawanid']] = $kar['tmkjamsostek'];
	$tpkarid[$kar['karyawanid']] = $kar['tipekaryawan'];

	if ($kar['kodecatu'] != '0') {
		$kodecatu[$kar['karyawanid']] = $kar['kodecatu'];
	}

	if ($idaaaa == '') {
		$idaaaa = "'" . $kar['karyawanid'] . "'";
	} else {
		$idaaaa .= ",'" . $kar['karyawanid'] . "'";
	}

	## BPJS TENAGA KERJA
	$bpjstenaga[$kar['karyawanid']] = trim($kar['jms']); // JKK JKM JHT

	# BPJS PENSIUN
	$diff = (strtotime($tanggal1) - strtotime($kar['tanggallahir']));
	$umur = floor($diff / (60 * 60 * 24 * 365));
	if ($umur > 57) {
		$bpjspensiun[$kar['karyawanid']] = ""; // JP
	} else {
		$bpjspensiun[$kar['karyawanid']] = trim($kar['pensiun']); // JP
	}

	# BPJS PENSIUN
	$bpjskes[$kar['karyawanid']] = trim($kar['bpjs']); // KESEHATAN
	$bpjskestanggungan[$kar['karyawanid']] = $kar['jmltanggungan'] + 1;
}


#pastikan absen NS penuh selama sebulan
$arrayhkxx = array();
$str = "select a.tanggal,a.karyawanid,sum(a.jhk) as jumlahhk from " . $dbname . ".kebun_kehadiran_vw a left join 
	" . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
	where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
	and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
	and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
	and sistemgaji='Bulanan'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'  
	and a.notransaksi not like '%BOR%' group by a.karyawanid,a.tanggal";
$res = fetchData($str);
foreach ($res as $bar) {
	$cekabsen[$bar['karyawanid']][$bar['tanggal']] = $bar['tanggal'];
	if (!isset($arrayhkxx[$bar['karyawanid']][$bar['tanggal']])) {
		$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] = 0;
	}
	$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] += $bar['jumlahhk'];
}

$str = "select a.tanggal,a.karyawanid,sum(a.hkpanenperhari) as jumlahhk from " . $dbname . ".kebun_prestasi_vs_hk a left join 
	" . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
	where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
	and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
	and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
	and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   
	and a.keterangan !='KONTAN' group by a.tanggal, a.karyawanid";
$res = fetchData($str);
foreach ($res as $bar) {
	$cekabsen[$bar['karyawanid']][$bar['tanggal']] = $bar['tanggal'];
	if (!isset($arrayhkxx[$bar['karyawanid']][$bar['tanggal']])) {
		$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] = 0;
	}
	$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] += $bar['jumlahhk'];
}

## Simpan HK nya
$str = "select a.tanggal,a.karyawanid,sum(a.hk) as jumlahhk from " . $dbname . ".kebun_3premibmtbs a left join 
	" . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
	where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
	and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
	and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
	and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B' group by a.tanggal, a.karyawanid";
$res = fetchData($str);
foreach ($res as $bar) {
	// $cekabsen[$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];
	// if(!isset($arrayhkxx[$bar['karyawanid']][$bar['tanggal']])){
	// 	$arrayhkxx[$bar['karyawanid']][$bar['tanggal']]=0;
	// }
	$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] += $bar['jumlahhk'];
}


## Hitung Kehadiran nya
$str = "select a.tanggal,a.karyawanid from " . $dbname . ".kebun_spbbm a 
	left join " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
	where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
	and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
	and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
	and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B' group by a.tanggal, a.karyawanid";
$res = fetchData($str);
foreach ($res as $bar) {
	$cekabsen[$bar['karyawanid']][$bar['tanggal']] = $bar['tanggal'];
	if (!isset($arrayhkxx[$bar['karyawanid']][$bar['tanggal']])) {
		$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] = 0;
	}
}


$str = "select a.tanggal,a.idkaryawan as karyawanid,sum(a.hk) as jumlahhk from " . $dbname . ".vhc_runhk_vw a 
	left join " . $dbname . ".datakaryawan_hist b on a.idkaryawan=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
	where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
	and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
	and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
	and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   group by a.idkaryawan,a.tanggal";
$res = fetchData($str);
foreach ($res as $bar) {
	$cekabsen[$bar['karyawanid']][$bar['tanggal']] = $bar['tanggal'];
	if (!isset($arrayhkxx[$bar['karyawanid']][$bar['tanggal']])) {
		$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] = 0;
	}
	$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] += $bar['jumlahhk'];
}

$str = "select a.tanggal, a.nik as karyawanid,sum(a.jhk) as jumlahhk from " . $dbname . ".vhc_spl_kehadiran_vw a left join 
	" . $dbname . ".datakaryawan_hist b on a.nik=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
	where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' ) and
	(b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
	and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
	and sistemgaji='Bulanan'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'  group by a.nik,a.tanggal";
$res = fetchData($str);
foreach ($res as $bar) {
	$cekabsen[$bar['karyawanid']][$bar['tanggal']] = $bar['tanggal'];
	if (!isset($arrayhkxx[$bar['karyawanid']][$bar['tanggal']])) {
		$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] = 0;
	}
	$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] += $bar['jumlahhk'];
}

$str = "select a.tanggal,a.absensi,a.karyawanid,sum(a.premi+a.insentif) as premi,sum(a.hk) as jumlahhk from " . $dbname . ".sdm_absensidt a 
	left join " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
	where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
	and (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
	and sistemgaji='Bulanan'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'    
	and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' group by a.karyawanid,a.tanggal";
$res = fetchData($str);
foreach ($res as $bar) {
	$cekabsen[$bar['karyawanid']][$bar['tanggal']] = $bar['tanggal'];
	if (!isset($arrayhkxx[$bar['karyawanid']][$bar['tanggal']])) {
		$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] = 0;
	}
	$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] += $bar['jumlahhk'];
}

$str = "select a.tanggal,a.nikmandor as karyawanid from " . $dbname . ".kebun_aktifitas a 
	left join " . $dbname . ".datakaryawan_hist b on a.nikmandor=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
	where 1=1 " . $tpkar . "  and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
	and (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
	and sistemgaji='Bulanan'  
	and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'    
	and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' group by nikmandor,a.tanggal";
$res = fetchData($str);
foreach ($res as $bar) {
	$cekabsen[$bar['karyawanid']][$bar['tanggal']] = $bar['tanggal'];
	$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] += 1;
}

$str = "select a.tanggal,a.nikmandor1 as karyawanid from " . $dbname . ".kebun_aktifitas a 
	left join " . $dbname . ".datakaryawan_hist b on a.nikmandor1=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
	where 1=1 " . $tpkar . "  and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
	and (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
	and sistemgaji='Bulanan'  
	and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'    
	and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' group by nikmandor1,a.tanggal";
$res = fetchData($str);
foreach ($res as $bar) {
	$cekabsen[$bar['karyawanid']][$bar['tanggal']] = $bar['tanggal'];
	if (!isset($arrayhkxx[$bar['karyawanid']][$bar['tanggal']])) {
		$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] = 0;
	}
	$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] += 1;
}

$str = "select a.tanggal,a.keranimuat as karyawanid from " . $dbname . ".kebun_aktifitas a 
	left join " . $dbname . ".datakaryawan_hist b on a.keranimuat=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
	where 1=1 " . $tpkar . "  and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
	and (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
	and sistemgaji='Bulanan'  
	and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'    
	and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' group by keranimuat,a.tanggal";
$res = fetchData($str);
foreach ($res as $bar) {
	$cekabsen[$bar['karyawanid']][$bar['tanggal']] = $bar['tanggal'];
	if (!isset($arrayhkxx[$bar['karyawanid']][$bar['tanggal']])) {
		$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] = 0;
	}
	$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] += 1;
}

$str = "select a.tanggal,a.nikasisten as karyawanid from " . $dbname . ".kebun_aktifitas a 
	left join " . $dbname . ".datakaryawan_hist b on a.nikasisten=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
	where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
	and (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
	and sistemgaji='Bulanan'  
	and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'    
	and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' group by nikasisten,a.tanggal";
$res = fetchData($str);
foreach ($res as $bar) {
	$cekabsen[$bar['karyawanid']][$bar['tanggal']] = $bar['tanggal'];
	if (!isset($arrayhkxx[$bar['karyawanid']][$bar['tanggal']])) {
		$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] = 0;
	}
	$arrayhkxx[$bar['karyawanid']][$bar['tanggal']] += 1;
}

$blmabsen = "";
$nx = 0;
$blmabsen .= "<table border='1' cellpadding='5' cellspacing='0'>";
$blmabsen .= "<tr><th>No.</th><th>Nama Karyawan</th><th>Jumlah Kehadiran</th></tr>";

foreach ($karydidatakary as $karyid) {
	if (count($cekabsen[$karyid]) < $jlhhari) {
		$nx++;
		$blmabsen .= "<tr>";
		$blmabsen .= "<td>" . $nx . "</td>";
		$blmabsen .= "<td>" . $namakar[$karyid] . "</td>";
		$blmabsen .= "<td align=center>" . count($cekabsen[$karyid]) . "</td>";
		$blmabsen .= "</tr>";
	}
}

$blmabsen .= "</table>";

$arrdatahhkjumlah = array();
foreach ($arrayhkxx as $karid => $arrtanggal) {
	foreach ($arrtanggal as $tanggal => $hka) {
		if ($hka > 1) {
			$hka = 1;
		}
		if (!isset($arrdatahhkjumlah[$karid])) {
			$arrdatahhkjumlah[$karid] = 0;
		}
		$arrdatahhkjumlah[$karid] += $hka;
	}
}

// if($nx != '0'){
// exit("<b> MASIH ADA KARYAWAN GAJI BULANAN BELUM MEMLIKI ABSEN SEBULUAN PENUH (" . $jlhhari . ")</b> <br><br>" . $blmabsen);
// }

# ambil semua komponen dari gajipokok=====================

## Parameter aplikasi
#= ambil komponen yang termasuk di gaji pokok
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRGAPOK'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$arrgapok = explode(',', $bar['nilai']);
foreach ($arrgapok as $key) {
	$arrcombpjs[] = $key;
}


## Ambil karyawan yang suda proses gaji kecil
$karyawan_gajikecil = array();
$str = "select karyawanid from " . $dbname . ".sdm_gaji_kecil where periodegaji = '" . $param['periodegaji'] . "' and kodeorg = '" . $param['kodeorg'] . "' group by karyawanid";
$res = fetchData($str);
foreach ($res as $idx => $val) {
	$karyawan_gajikecil[] = $val['karyawanid'];
}

$tipekaryawan = array();
$str1 = "select a.*,b.namakaryawan,b.tipekaryawan from " . $dbname . ".sdm_5gajipokok a left join " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
			 where a.tahun='" . $param['periodegaji'] . "' " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' 
			 and (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B' and 
			 idkomponen in (select id from " . $dbname . ".sdm_ho_component where type='basic' and id not in ('59'))";
$res1 = fetchData($str1);
foreach ($res1 as $idx => $val) {
	$karydigp[$val['karyawanid']] = $val['karyawanid'];
	if ($id[$val['karyawanid']][0] == $val['karyawanid']) {

		$value = isset($arrdatahhkjumlah[$val['karyawanid']]) ? $arrdatahhkjumlah[$val['karyawanid']] : 0;

		# add to ready data ===============================================
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $val['karyawanid'],
			'idkomponen' => $val['idkomponen'],
			'jumlah' => $val['jumlah'],
			'pengali' => 1,
			'hk' => $value
		);
	}


	if (!in_array($val['karyawanid'], $karyawan_gajikecil)) {
		if (in_array($val['idkomponen'], $arrcombpjs)) {
			$umrbulanan[$val['karyawanid']] += $val['jumlah'];
		}
	}

	if ($val['idkomponen'] == 1) {
		$gajiperhari[$val['karyawanid']] = ($val['jumlah'] / 25);
		$gapokKaryawan[$val['karyawanid']] = $val['jumlah'];
	}
}

#cek pastikan semua kary memiliki gapok
$blmadagp = "";
$ngpblmada = 0;
foreach ($karydidatakary as $dtkary) {
	if ($karydigp[$dtkary] == '0' or $karydigp[$dtkary] == '') {
		$ngpblmada += 1;
		$blmadagp .= "<tr>
			<td>" . $ngpblmada . ".</td>
			<td>" . $karydidatakary[$dtkary] . "</td>
			<td>" . $nikkary[$dtkary] . "</td>
			<td>" . $namakar[$dtkary] . "</td>
			<td>" . getNamaTipekaryawan($karydidatakary[$dtkary]) . "</td>
			<td>" . getJabatanKaryawan($karydidatakary[$dtkary]) . "</td>
		</tr>\n";
	}
}

if (!empty($blmadagp)) {
	exit(" <b> WARNING : ADA KARYAWAN BELUM ADA GAPOK (SDM->SETUP->UPAH) </b> <br><hr>.\n 
	  <table border='0' cellspacing=1 cellpadding=5 >\n
		<tr align=center>
			<th>NO.</th>
			<th>KARYAWANID</th>
			<th>NIK</th>
			<th>NAMA</th>
			<th>TIPE KARYAWAN</th>
			<th>JABATAN</th>
		</tr>\n" . $blmadagp . "</table>");
}


## Potongan HK

$tdkdibayar = array();
$hktdkbyr = array();
$hk = array();

#ambil jumlah hk tidak dibayar untuk KHT dan total tidak dibayar
$strgjh = "select  count(*) as jlh,b.karyawanid from " . $dbname . ".sdm_hktdkdibayar_vw a left join 
				  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
				   where 1=1 " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' 
				   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
				   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
				   and sistemgaji='Bulanan'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B' 
				   group by a.karyawanid";

$resgjh = fetchData($strgjh);
foreach ($resgjh as $idx => $val) {
	@$tdkdibayar[$val['karyawanid']] += $gajiperhari[$val['karyawanid']] * $val['jlh']; #jumlah tidak dibayar
	@$hktdkbyr[$val['karyawanid']] += $val['jlh'];
	@$lstKary[$val['karyawanid']] = $val['karyawanid'];
}

## End Potongan HK

## Start catu/tunjangan keluarga
$RpNatura = 0;
$sNatura = "select * from " . $dbname . ".sdm_5hargacatukg where unit='" . $param['kodeorg'] . "' and status='1'";
$rNatura = fetchData($sNatura);
if (count($rNatura) == 0) {
	exit('warning: Harga catu per kg belum diinput silahkan input pada setup harga catu per kg');
} else {
	$RpNatura = $rNatura[0]['nilai'];
}

$RpNaturaperkelompok = array();
$PotRpNaturaperkelompok = array();
$sNatura = "select * from " . $dbname . ".sdm_5catu where kodeorg='" . $param['kodeorg'] . "' and tahun='" . substr($param['periodegaji'], 0, 4) . "'";
$rNatura = fetchData($sNatura);
if (count($rNatura) == 0) {
	exit('warning: Kelompok Catu Kg belum disetup, silahkan disetup terlebih dahulu');
} else {
	foreach ($rNatura as $key  => $val) {
		$RpNaturaperkelompok[$val['kelompok']] = ($val['jumlah'] * $RpNatura);
		$PotRpNaturaperkelompok[$val['kelompok']] = (($val['jumlah'] * $RpNatura) / 25);
	}
}

$tjkeluarga = 0;
$dtcatu = count($kodecatu);
if ($dtcatu > 0) {
	foreach ($kodecatu as $bar => $value) {
		$tjkeluarga = $RpNaturaperkelompok[$value];

		## Potongan Natura
		if ($hktdkbyr[$bar] != '') {
			$potNatura = ($PotRpNaturaperkelompok[$value] * $hktdkbyr[$bar]);

			$readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $bar,
				'idkomponen' => 134, //pot. tj keluarga
				'jumlah' => $potNatura,
				'pengali' => 1,
				'hk' => 0
			);
		}

		## Tunjangan Natura
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $bar,
			'idkomponen' => 118, //tj. keluarga
			'jumlah' => $tjkeluarga,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

## End catu/tunjangan keluarga

#3. Get Lembur Data
$where2 = " a.kodeorg like '" . $param['kodeorg'] . "%' and (tanggal>='" . $tanggal1 . "' and tanggal<='" . $tanggal2 . "')";
$query2 = "select a.karyawanid,sum(a.uangkelebihanjam) as lembur from " . $dbname . ".sdm_lemburdt a left join " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
			 where 1=1 " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' 
			 and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			 and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'  and " . $where2 . " group by a.karyawanid";
$lbrRes = fetchData($query2);
foreach ($lbrRes as $idx => $row) {
	if (isset($id[$row['karyawanid']])) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $row['karyawanid'],
			'idkomponen' => 33,
			'jumlah' => $row['lembur'],
			'pengali' => 1,
			'hk' => 0
		);
	} else {
		//abaikan jika tidak terdaftar pada karyawanid  
	}
}

#4. Get Potongan Data============================================================
$where3 = " kodeorg like '%" . $param['kodeorg'] . "%' and a.periodegaji='" . $param['periodegaji'] . "'";
$query3 = "select a.nik as karyawanid,sum(jumlahpotongan) as potongan,tipepotongan from " . $dbname . ".sdm_potongandt a left join 
			" . $dbname . ".datakaryawan_hist b on a.nik=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B' where 1=1 " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' 
			 and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and sistemgaji='Bulanan'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'                    
			 and " . $where3 . " group by a.nik,a.tipepotongan";
$potRes = fetchData($query3);
foreach ($potRes as $idx => $row) {
	if (isset($id[$row['karyawanid']])) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $row['karyawanid'],
			'idkomponen' => $row['tipepotongan'],
			'jumlah' => $row['potongan'],
			'pengali' => 1,
			'hk' => 0
		);
	} else {
		//abaikan jika tidak terdaftar pada karyawanid  
	}
}

if ($DMA) {
	$where4 = "(a.status=1 OR (a.status=3 AND a.periode_close = '" . $param['periodegaji'] . "'))";
} else {
	$where4 = "(a.status=1)";
}

$query4 = "select a.notransaksi,a.end,lokasitugas,a.karyawanid,jenis,sum(jumlah) as bulanan from " . $dbname . ".sdm_angsuran a 
left join " . $dbname . ".sdm_angsurandt c on a.notransaksi=c.notransaksi
left join " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
					where 1=1 " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' 
					and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'  
					and " . $where4 . " and bulan='" . $param['periodegaji'] . "' group by jenis,karyawanid";
$angRes = fetchData($query4);
foreach ($angRes as $idx => $row) {
	if ($DMA && $row['end'] == $param['periodegaji'] && $proses == 'post') {
		$data = array(
			'status' => '3',
			'periode_close' => $param['periodegaji'],
		);

		$sUpt = updateQuery($dbname, 'sdm_angsuran', $data, "notransaksi='" . $row['notransaksi'] . "'");
		$owlPDO->exec($sUpt);
	}
	if ($id[$row['karyawanid']][0] == $row['karyawanid']) {
		#add to ready data================================================
		$readyData[] = array(
			'kodeorg' => $row['lokasitugas'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $row['karyawanid'],
			'idkomponen' => $row['jenis'],
			'jumlah' => $row['bulanan'],
			'pengali' => 1,
			'hk' => 0
		);
	}
}

#6 Premi dan penalty =======================================================================
#6.0 periksa posting transaksi

#posting perawatan
$stru1 = "select distinct(tanggal) from " . $dbname . ".kebun_kehadiran_vw a left join 
		   " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		   where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
		   and (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		   and a.jurnal=0 
		   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'    
		   and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   and a.notransaksi not like '%BOR%' order by tanggal";
$resu1 = $owlPDO->query($stru1) or die(print " Gagal: " . PDOException::getMessage());
$resu1->setFetchMode(PDO::FETCH_OBJ);
$numrows1 = owlBaris($resu1);

#posting panen
$stru2 = "select distinct(tanggal) from " . $dbname . ".kebun_prestasi_vw a left join 
		   " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		   where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
		   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		   and a.jurnal=0
		   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
		   and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   order by tanggal";
$resu2 = $owlPDO->query($stru2) or die(print " Gagal: " . PDOException::getMessage());
$resu2->setFetchMode(PDO::FETCH_OBJ);
$numrows2 = owlBaris($resu2);

#posting traksi
$stru3 = "select distinct(tanggal) from " . $dbname . ".vhc_runhk_vw a left join 
		   " . $dbname . ".datakaryawan_hist b on a.idkaryawan=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		   where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
		   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
		   and posting=0 and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   order by tanggal";
$resu3 = $owlPDO->query($stru3) or die(print " Gagal: " . PDOException::getMessage());
$resu3->setFetchMode(PDO::FETCH_OBJ);
$numrows3 = owlBaris($resu3);

#posting bkm sipil
$stru5 = "select distinct(tanggal) from " . $dbname . ".vhc_spl_kehadiran_vw a left join 
		   " . $dbname . ".datakaryawan_hist b on a.nik=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		   where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
		   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
		   and posting=0 and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   order by tanggal";
$resu5 = $owlPDO->query($stru5) or die(print " Gagal: " . PDOException::getMessage());
$resu5->setFetchMode(PDO::FETCH_OBJ);
$numrows5 = owlBaris($resu5);

#posting premimbtbs
$stru6 = "select distinct(tanggal) from " . $dbname . ".kebun_3premibmtbs a left join 
		   " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		   where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
		   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
		   and posting=0 and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   order by tanggal";
$resu6 = $owlPDO->query($stru6) or die(print " Gagal: " . PDOException::getMessage());
$resu6->setFetchMode(PDO::FETCH_OBJ);
$numrows6 = owlBaris($resu6);

#posting sdm lembur
$stru4 = "select * from " . $dbname . ".sdm_lemburht 
			   where tanggal>='" . $tanggal1 . "' and tanggal<='" . $tanggal2 . "'     
			   and posting=0 and kodeorg like '" . $param['kodeorg'] . "%'  order by tanggal";
$resu4 = $owlPDO->query($stru4) or die(print " Gagal: " . PDOException::getMessage());
$resu4->setFetchMode(PDO::FETCH_OBJ);
$numrows4 = owlBaris($resu4);

#posting sdm pesangon
$stru99 = "select * from " . $dbname . ".sdm_pesangon 
		   where tanggal>='" . $tanggal1 . "' and tanggal<='" . $tanggal2 . "'     
		   and posting=0 and kodeunit like '" . $param['kodeorg'] . "%'  order by tanggal";
$resu99 = $owlPDO->query($stru99) or die(print " Gagal: " . PDOException::getMessage());
$resu99->setFetchMode(PDO::FETCH_OBJ);
$numrows99 = owlBaris($resu99);

## Posting finger
$struFP = "select * from " . $dbname . ".upload_absensi 
		   where tanggalabsen>='" . $tanggal1 . "' and tanggalabsen<='" . $tanggal2 . "'     
		   and posting=0 and kodeorg = '" . $param['kodeorg'] . "'  group by tanggalabsen  order by tanggalabsen";
$resuFP = $owlPDO->query($struFP) or die(print " Gagal: " . PDOException::getMessage());
$resuFP->setFetchMode(PDO::FETCH_OBJ);
$numrowsFP = owlBaris($resuFP);



if ($numrows1 > 0 || $numrows2 > 0 || $numrows3 > 0 || $numrows5 > 0 || $numrows6 > 0) {
	echo "Masih ada data yang belum di posting/There still unconfirmed transaction:";
	echo "<table class=sortable border=0 cellspacing=1 cellpadding=5>
				<thead>
					<tr class=rowheader>
						<td>" . $_SESSION['lang']['jenis'] . "</td>
						<td>" . $_SESSION['lang']['tanggal'] . "</td>
					</tr>
				</thead>
				<tbody>";

	while ($bar = $resu1->fetch()) {
		echo "<tr class=rowcontent>
					<td>Perawatan Kebun</td>
					<td>" . tanggalnormal($bar->tanggal) . "</td>
				 </tr>";
	}

	while ($bar = $resu2->fetch()) {
		echo "<tr class=rowcontent>
					<td>Kegiatan Panen</td>
					<td>" . tanggalnormal($bar->tanggal) . "</td>
				  </tr>";
	}

	while ($bar = $resu3->fetch()) {
		echo "<tr class=rowcontent>
					<td>Traksi Pekerjaan</td>
					<td>" . tanggalnormal($bar->tanggal) . "</td>
				  </tr>";
	}

	while ($bar = $resu5->fetch()) {
		echo "<tr class=rowcontent>
					<td>BKM Sipil</td>
					<td>" . tanggalnormal($bar->tanggal) . "</td>
				  </tr>";
	}
	while ($bar = $resu6->fetch()) {
		echo "<tr class=rowcontent>
					<td>Premi BM TBS</td>
					<td>" . tanggalnormal($bar->tanggal) . "</td>
				  </tr>";
	}

	echo "</tbody><tfoot></tfoot></table>";
	exit(); // keluar dari proses
}

if ($numrows4 > 0 || $numrows99 > 0 || $numrowsFP > 0) {
	echo "Masih ada data yang belum di posting/There still unconfirmed transaction:";
	echo "<table class=sortable border=0 cellspacing=1 cellpadding=5>
				<thead>
					<tr class=rowheader>
						<td>" . $_SESSION['lang']['jenis'] . "</td>
						<td>" . $_SESSION['lang']['kodeorg'] . "</td>
						<td>" . $_SESSION['lang']['tanggal'] . "</td>
					</tr>
				</thead>
				<tbody>";

	while ($bar = $resu4->fetch()) {
		echo "<tr class=rowcontent>
					<td>SDM Lembur</td>
					<td>" . $bar->kodeorg . "</td>
					<td>" . tanggalnormal($bar->tanggal) . "</td>
				  </tr>";
	}

	while ($bar = $resu99->fetch()) {
		echo "<tr class=rowcontent>
					<td>SDM Pesangon</td>
					<td>" . $bar->kodeunit . "</td>
					<td>" . tanggalnormal($bar->tanggal) . "</td>
				  </tr>";
	}

	while ($bar = $resuFP->fetch()) {
		if ($bar->subbagian == '') {
			$div = 'KANTOR';
		} else {
			$div = $bar->subbagian;
		}
		echo "<tr class=rowcontent>
					<td>SDM Proses Finger</td>
					<td>" . $div . "</td>
					<td>" . tanggalnormal($bar->tanggalabsen) . "</td>
				  </tr>";
	}

	echo "</tbody><tfoot></tfoot></table>";
	exit(); // keluar dari proses
}

#6.3.1 Get Pesangon 
$query5xx = "select a.* from " . $dbname . ".sdm_pesangon a left join 
			  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
			   where 1=1 " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "' 
			   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			   and a.kodeunit like '" . $param['kodeorg'] . "%'
			   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
			   and b.sistemgaji='Bulanan'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B' and posting='1' ";
//echo $query5xx;
$pesangonxx = fetchData($query5xx);
foreach ($pesangonxx as $idx => $val) {
	if ($val['jenispesangon'] == 'Pesangon') {
		$pesangon[$val['karyawanid']] += $val['rupiahditerima'];
	} elseif ($val['jenispesangon'] == 'Kompensasi') {
		$kompensasi[$val['karyawanid']] += $val['rupiahditerima'];
	} elseif ($val['jenispesangon'] == 'Uang Pisah') {
		$uangpisah[$val['karyawanid']] += $val['rupiahditerima'];
	}
}

#6.3.1 Get Premi Kegiatan Perawatan non kontan
$query5 = "select a.tanggal,sum(a.umr) as gaji,a.karyawanid,sum(a.insentif) as premi from " . $dbname . ".kebun_kehadiran_vw a left join 
			  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
			   where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
			   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and a.kontanan !='kontan'
			   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
			   and sistemgaji='Bulanan'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'  and a.notransaksi not like '%BOR%' group by a.karyawanid";
$premRes = fetchData($query5);
foreach ($premRes as $idx => $val) {
	if ($val['premi'] > 0) {
		$premi[$val['karyawanid']] += $val['premi'];
	}

	@$upahLebih[$val['karyawanid']] += $val['gaji'];
	@$potupahLebih[$val['karyawanid']] += $val['gaji'];
}

#6.3.1 Get Premi Kegiatan Perawatan Kontan
$query5 = "select a.tanggal,sum(a.umr) as gaji,a.karyawanid,sum(a.insentif) as premi from " . $dbname . ".kebun_kehadiran_vw a left join 
			  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
			   where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
			   and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') and a.kontanan ='kontan'
			   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
			   and sistemgaji='Bulanan'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'  and a.notransaksi not like '%BOR%' group by a.karyawanid";
$premRes = fetchData($query5);
foreach ($premRes as $idx => $val) {
	if ($val['premi'] > 0 or $val['gaji']) {
		@$premikontanan[$val['karyawanid']] += $val['premi'] + $val['gaji'];
		if (!$DMA) {
			@$potkontanan[$val['karyawanid']] += $val['premi'] + $val['gaji'];
		}
	}
}

#6.3.2 Get Premi Kegiatan Panen non kontan
$query6 = "select a.tanggal, sum(a.upahkerja) as upahkerja,a.karyawanid,sum(a.tpremi) as premi,sum(a.rupiahpenalty) as penalty,
			  sum(hkpanenperhari) as hk ,sum(a.upahpenalty) as upahpenalty from " . $dbname . ".kebun_prestasi_vs_hk a left join 
			  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
			  where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' ) 
			  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1') 
			  and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
			  and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B' and a.keterangan!='KONTAN' and a.noreferensi like '%PNN%'  group by a.tanggal, a.karyawanid";
$premRes1 = fetchData($query6);
foreach ($premRes1 as $idx => $val) {
	@$premi[$val['karyawanid']] += $val['premi'];
	@$penalty[$val['karyawanid']] += $val['penalty'] + $val['upahpenalty'];
	@$upahLebih[$val['karyawanid']] += $val['upahkerja'];
	@$potupahLebih[$val['karyawanid']] += $val['upahkerja'];
}

#6.3.2 Get Premi dan Upah Kegiatan Panen Kontan
$query66 = "select a.tanggal, sum(a.upahkerja) as upahkerja,a.karyawanid,sum(a.tpremi) as premi,sum(a.rupiahpenalty) as penalty,
			  sum(hkpanenperhari) as hk ,sum(a.upahpenalty) as upahpenalty from " . $dbname . ".kebun_prestasi_vs_hk a left join 
			  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
			  where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
			  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			  and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
			  and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B' and a.kontanan='KONTAN' and a.noreferensi like '%PNN%' group by a.tanggal, a.karyawanid";
$premRes16 = fetchData($query66);
foreach ($premRes16 as $idx => $val) {
	@$premikontanan[$val['karyawanid']] += $val['premi'] + $val['upahkerja'];
	if (!$DMA) {
		@$potkontanan[$val['karyawanid']] += $val['premi'] + $val['upahkerja'] + $val['upahpenalty'] + $val['rupiahpenalty'];
	}
}

#6.3.3 Get Premi traksi non kontan
$query7 = "select a.tanggal,sum(a.upah) as upah,a.idkaryawan as karyawanid,sum(a.premi) as premi,sum(a.penalty) as penalty,sum(hk) as hk
			  from " . $dbname . ".vhc_runhk_vw a left join " . $dbname . ".datakaryawan_hist b on a.idkaryawan=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
			  where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
			  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			  and substr(a.notransaksi,1,4)='" . $param['kodeorg'] . "' and a.kontanan != 'KONTAN'
			  and a.tanggal >='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
			  and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   group by a.idkaryawan";
$premRes2 = fetchData($query7);
foreach ($premRes2 as $idx => $val) {
	@$premi[$val['karyawanid']] += $val['premi'];
	@$penalty[$val['karyawanid']] += $val['penalty'];
	@$hk[$val['karyawanid']] += $val['hk'];
}

#6.3.3 Get Premi traksi kontan
$query7 = "select a.tanggal,sum(a.upah) as upah,a.idkaryawan as karyawanid,sum(a.premi) as premi,sum(a.penalty) as penalty,sum(hk) as hk
			  from " . $dbname . ".vhc_runhk_vw a left join " . $dbname . ".datakaryawan_hist b on a.idkaryawan=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
			  where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
			  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			  and substr(a.notransaksi,1,4)='" . $param['kodeorg'] . "' and a.kontanan = 'KONTAN'
			  and a.tanggal >='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "'     
			  and sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   group by a.idkaryawan";
$premRes2 = fetchData($query7);
foreach ($premRes2 as $idx => $val) {
	@$premikontanan[$val['karyawanid']] += $val['premi'] + $val['upah'];
	if (!$DMA) {
		@$potkontanan[$val['karyawanid']] += $val['premi'] + $val['upah'];
	}

	@$penalty[$val['karyawanid']] += $val['penalty'];
	@$hk[$val['karyawanid']] += $val['hk'];
}

#6.3.4 Get Premi Kemandoran
$query8 = "select sum(a.premiinput) as premi,a.karyawanid from " . $dbname . ".kebun_premikemandoran a left join 
		  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		  where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
		  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		  and a.kodeorg='" . $param['kodeorg'] . "' and a.periode = '" . $param['periodegaji'] . "'     
		  and b.sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   and a.kontanan!='KONTAN' group by a.karyawanid";
$premRes2 = fetchData($query8);
foreach ($premRes2 as $idx => $val) {
	$premi[$val['karyawanid']] += $val['premi'];
}

$query88 = "select sum(a.premiinput) as premi,a.karyawanid from " . $dbname . ".kebun_premikemandoran a left join 
		  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		  where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
		  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		  and a.kodeorg='" . $param['kodeorg'] . "' and a.periode = '" . $param['periodegaji'] . "'     
		  and b.sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   and a.kontanan='KONTAN' group by a.karyawanid";
$premRes28 = fetchData($query88);
foreach ($premRes28 as $idx => $val) {
	@$premikontanan[$val['karyawanid']] += $val['premi'];
	if (!$DMA) {
		@$potkontanan[$val['karyawanid']] += $val['premi'];
	}
}

#= premi bongkar muat tbs non kontan
$query20 = "select sum(a.rppremi) as premi , sum(a.rphk) as rphk,a.nilai1hk as nilai1hk,sum(a.hk) as hk,a.karyawanid,a.tanggal
		  from " . $dbname . ".kebun_3premibmtbs a left join 
		  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		  where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
		  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		  and a.periode = '" . $param['periodegaji'] . "'     
		  and b.sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   and a.kontanan !='KONTAN' group by a.karyawanid,a.tanggal";
$res20 = fetchData($query20);
foreach ($res20 as $idx => $val) {
	@$premi[$val['karyawanid']] += $val['premi'];

	if ($val['hk'] == 0 and $val['premi'] > 0) {
	} else {
		@$penalty[$val['karyawanid']] += $val['nilai1hk'] - $val['rphk'];
	}

	@$upahLebih[$val['karyawanid']] += $val['rphk'];
	@$potupahLebih[$val['karyawanid']] += $val['rphk'];
}

#= premi bongkar muat tbs kontan
$query202 = "select sum(a.rppremi) as premi , sum(a.rphk) as rphk,sum(a.nilai1hk) as nilai1hk,a.karyawanid
		  from " . $dbname . ".kebun_3premibmtbs a left join 
		  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		  where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' )
		  and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		  and a.periode = '" . $param['periodegaji'] . "'     
		  and b.sistemgaji='Bulanan' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   and a.kontanan ='KONTANAN' group by a.karyawanid";
$res202 = fetchData($query202);
foreach ($res202 as $idx => $val) {
	$premikontanan[$val['karyawanid']] += $val['premi'] + $val['nilai1hk'];
	if (!$DMA) {
		$potkontanan[$val['karyawanid']] += $val['premi'] + $val['nilai1hk'];
	}
}

#6.3.5 Get Premi dari BKM Sipil Non Kontan
$query7 = "select a.tanggal,sum(a.umr) as upah,a.nik as karyawanid,sum(a.premi) as premi from " . $dbname . ".vhc_spl_kehadiran_vw a left join 
		" . $dbname . ".datakaryawan_hist b on a.nik=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' ) and 
		(b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' and kontanan != 'KONTAN'     
		and sistemgaji='Bulanan'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'  group by a.nik";
$premRes2 = fetchData($query7);
foreach ($premRes2 as $idx => $val) {
	if ($val['premi'] > 0) {
		@$premi[$val['karyawanid']] += $val['premi'];
	}

	@$upahLebih[$val['karyawanid']] += $val['upah'];
	@$potupahLebih[$val['karyawanid']] += $val['upah'];
}

#6.3.6 Get Premi dari BKM Sipil Kontan
$query7 = "select a.tanggal,sum(a.umr) as upah,a.nik as karyawanid,sum(a.premi) as premi from " . $dbname . ".vhc_spl_kehadiran_vw a left join 
		" . $dbname . ".datakaryawan_hist b on a.nik=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' ) and 
		(b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		and a.tanggal>='" . $tanggal1 . "' and a.tanggal <= '" . $tanggal2 . "' and kontanan = 'KONTAN'     
		and sistemgaji='Bulanan'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'  group by a.nik";
$premRes2 = fetchData($query7);
foreach ($premRes2 as $idx => $val) {
	if ($val['premi'] > 0 or $val['upah'] > 0) {
		@$premikontanan[$val['karyawanid']] += $val['premi'] + $val['upah'];
		if (!$DMA) {
			@$potkontanan[$val['karyawanid']] += $val['premi'] + $val['upah'];
		}
	}
}

#premi tetap dari absensi==========================================
$stkh = "select a.tanggal,a.absensi,a.karyawanid,sum(a.premi+a.insentif) as premi, sum(a.umr) as umr, b.kodejabatan, b.tipekaryawan from " . $dbname . ".sdm_absensidt a 
		   left join " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		   where 1=1 " . $tpkar . " and b.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $param['kodeorg'] . "' and periodegaji='" . $param['periodegaji'] . "' and version_type='B' ) 
		   and (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		   and a.kodeorg like '" . $param['kodeorg'] . "%' and sistemgaji='Bulanan'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'    
		   and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' group by a.karyawanid";
$reskh = $owlPDO->query($stkh) or die(print " Gagal: " . PDOException::getMessage());
$reskh->setFetchMode(PDO::FETCH_OBJ);
while ($barky = $reskh->fetch()) {
	@$premi[$barky->karyawanid] += $barky->premi;

	# Khusus Pemanen DMA; Jabatan: Pemanen; Tipe: KHT dan SKU
	if ($DMA && $barky->kodejabatan == '15' && ($barky->tipekaryawan == '3' || $barky->tipekaryawan == '8')) {
		@$upahLebih[$barky->karyawanid] += $barky->umr;
		@$potupahLebih[$barky->karyawanid] += $barky->umr;
	}
}
#end premi tetap dari absensi========================================== 

@$dtkarhktdkbayar = count($lstKary);
if (@$dtkarhktdkbayar > 0) {
	foreach (@$lstKary as $idx => $val) {

		if ($hktdkbyr[$val] == '') {
			@$hktdkbyr[$val] = 0;
		}

		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $val,
			'idkomponen' => 37, //potongan hk
			'jumlah' => $tdkdibayar[$val] + $upahpenalty[$val],
			'pengali' => 1,
			'hk' => $hktdkbyr[$val]
		);
	}
}

foreach ($upahLebih as $idx => $row) {
	$rupiah = 0;
	if ($row > $gapokKaryawan[$idx]) {
		$rupiah = $row - $gapokKaryawan[$idx];
	}

	#add to ready data================================================
	if ($rupiah > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $idx,
			'idkomponen' => 132,
			'jumlah' => $rupiah,
			'pengali' => 1,
			'hk' => 0
		);

		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $idx,
			'idkomponen' => 133,
			'jumlah' => $rupiah,
			'pengali' => 1,
			'hk' => 0
		);
	}
}




foreach ($premi as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $idx,
			'idkomponen' => 32,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

foreach ($pesangon as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $idx,
			'idkomponen' => 89,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

foreach ($kompensasi as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $idx,
			'idkomponen' => 98,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

foreach ($uangpisah as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $idx,
			'idkomponen' => 97,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}


if (!empty($premikontanan)) foreach ($premikontanan as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $idx,
			'idkomponen' => 31,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

if (!empty($potkontanan)) foreach ($potkontanan as $idx => $row) {
	#add to ready data================================================
	if ($row > 0) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $idx,
			'idkomponen' => 43,
			'jumlah' => $row,
			'pengali' => 1,
			'hk' => 0
		);
	}
}

@$cpenalty = count($penalty);
if ($cpenalty > 0) {
	foreach (@$penalty as $idx => $row) {
		#add to ready data================================================
		if ($row > 0) {
			$readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $idx,
				'idkomponen' => 34,
				'jumlah' => $row,
				'pengali' => 1,
				'hk' => 0
			);
		}
	}
}

#penalty kehadiran dari absensi
$stkh = "select a.karyawanid,sum(a.penaltykehadiran) as penaltykehadiran from " . $dbname . ".sdm_absensidt a left join 
		  " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
		  where 1=1 " . $tpkar . " and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   and b.lokasitugas='" . $param['kodeorg'] . "' 
		  and (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
		  and a.kodeorg like '" . $param['kodeorg'] . "%' and sistemgaji='Bulanan'  
		  and a.tanggal>='" . $tanggal1 . "' and a.tanggal<='" . $tanggal2 . "' group by a.karyawanid";
$reskh = $owlPDO->query($stkh) or die(print " Gagal: " . PDOException::getMessage());
$reskh->setFetchMode(PDO::FETCH_OBJ);
while ($barkh = $reskh->fetch()) {
	if ($barkh->penaltykehadiran > 0)
		$penaltykehadiran[$barkh->karyawanid] = $barkh->penaltykehadiran;
}

@$cpenaltykehadiran = count($penaltykehadiran);
if ($cpenaltykehadiran > 0) {
	foreach (@$penaltykehadiran as $idx => $row) {
		#add to ready data================================================
		if ($row > 0) {
			$readyData[] = array(
				'kodeorg' => $param['kodeorg'],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $idx,
				'idkomponen' => 41,
				'jumlah' => $row,
				'pengali' => 1,
				'hk' => 0
			);
		}
	}
}

#7. premi : sdm_premi
$str = "select a.karyawanid,a.premi,a.jenis from " . $dbname . ".sdm_premi a left join " . $dbname . ".datakaryawan_hist b on a.karyawanid=b.karyawanid and b.approval_status='8' and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'
			where  1=1 " . $tpkar . " and b.lokasitugas='" . $param['kodeorg'] . "'  and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'  
			and  (b.tanggalkeluar>='" . $tanggal1 . "' or b.tanggalkeluar='0000-00-00') and b.alokasi in ('0','1')
			and sistemgaji='Bulanan' and periode='" . $param['periodegaji'] . "' group by a.karyawanid,a.jenis";
$res = fetchData($str);
foreach ($res as $idx => $row) {
	if (isset($id[$row['karyawanid']])) {
		$readyData[] = array(
			'kodeorg' => $param['kodeorg'],
			'periodegaji' => $param['periodegaji'],
			'karyawanid' => $row['karyawanid'],
			'idkomponen' => $row['jenis'],
			'jumlah' => $row['premi'],
			'pengali' => 1,
			'hk' => 0
		);
	} else {
		//abaikan jika tidak terdaftar pada karyawanid  
	}
}

$rapelgaji = array();
$i = "select * from " . $dbname . ".sdm_pendapatanlaindt a where kodeorg='" . $param['kodeorg'] . "' and a.periodegaji='" . $param['periodegaji'] . "' "
	. " and karyawanid in (select karyawanid from " . $dbname . ".datakaryawan_hist b where 1=1 " . $tpkar1 . " and"
	. " (tanggalkeluar>='" . $tanggal1 . "' or tanggalkeluar='0000-00-00') and alokasi in ('0','1') and sistemgaji='Bulanan' and"
	. " (tanggalmasuk<='" . $tanggal2 . "' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and b.periodegaji='" . $param['periodegaji'] . "' and b.version_type='B'   ) and posting='1'";
$n = fetchData($i);
foreach ($n as $idx => $val) {
	$readyData[] = array(
		'kodeorg' => $param['kodeorg'],
		'periodegaji' => $param['periodegaji'],
		'karyawanid' => $val['karyawanid'],
		'idkomponen' => $val['idkomponen'],
		'jumlah' => $val['jumlah'],
		'pengali' => 1,
		'hk' => 0
	);
	if ($val['idkomponen'] == '14') {
		$rapelgaji[$val['karyawanid']] = $val['jumlah'];
	}
}

#######################################################################################################################
###tambahan sendi disini, memasukan bpjs kesehatan (jms) dan bpjs kesehatan
##algoritma : jika kolom jms dan bpjs di datakaryawan_hist terisi maka akan memotong

$optTipe = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$tipeOrg = $optTipe[$param['kodeorg']];
$bpjsorg = $tipeOrg;


## Ambil Ump Daerah
$sUmpDaerah = "select distinct jumlah from " . $dbname . ".sdm_5gajipokok where tahun='" . substr($param['periodegaji'], 0, 4) . "' and idkomponen='87' and kodeorg='" . $param['kodeorg'] . "'";
$rUmpDaerah = fetchData($sUmpDaerah);
$umpDaerah = $rUmpDaerah[0]['jumlah']; #UMP Daerah

if ($umpDaerah == '' or $umpDaerah == 0) {
	exit("Warning UMP Daerah belum disetup (SDM->SETUP->ABSENSI DAN PENGGAJIAN->UMP DAERAH) Tahun : " . substr($param['periodegaji'], 0, 4) . ", Unit : " . $param['kodeorg'] . " ");
}

#= parameter aplikasi 
#= kerja
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSKER'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$arrbpjs = explode(',', $bar['nilai']);
foreach ($arrbpjs as $key) {
	$arrker[] = $key;
}


#= kesehatan
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSKES'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$arrbpjs = explode(',', $bar['nilai']);
foreach ($arrbpjs as $key) {
	$arrkes[] = $key;
}


#= pensiun
$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeaplikasi='HR' and kodeparameter='HRBPJSPEN'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$arrbpjs = explode(',', $bar['nilai']);
foreach ($arrbpjs as $key) {
	$arrpen[] = $key;
}


## Tambah Natura
foreach ($readyData as $val) {
	if ($val['idkomponen'] == '118') {
		$natura_karyawan[$val['karyawanid']] = $val['jumlah'];
	}
}


$kdpt = $getPT[$param['kodeorg']];


#= Check apakah ada tipekaryawan yang tidak terdaftar di komponen bpjs
$str = "select * from " . $dbname . ".sdm_5komponenbpjspengali where kodeorg='" . $kdpt . "' and status='1' ";
$resbpjs = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$komponenx = array();
$komponen = array();
foreach ($resbpjs as $rPlbr) {
	$komponenx = explode(',', $rPlbr['komponengaji']);
	foreach ($komponenx as $key => $val) {
		$komponen[$rPlbr['tipekaryawan']][$val] = $val;
	}
}

# Kondisi ambil nilai pengganti selain UMP yang di setupkan di komponen bpjs
if (!empty($komponen)) {
	$nilaiPengganti = array();
	$sbpjs = "select jumlah, karyawanid, idkomponen from " . $dbname . ".sdm_5gajipokok where tahun='" . $param['periodegaji'] . "' and kodeorg='" . $param['kodeorg'] . "' and karyawanid in ('" . implode("','", $karydidatakary) . "')";
	$rbpjs = fetchData($sbpjs);
	foreach ($rbpjs as $barbar) {
		if (!empty($komponen[$tpkarid[$barbar['karyawanid']]][$barbar['idkomponen']])) {

			$nilaiPengganti[$barbar['karyawanid']] += $barbar['jumlah'];
		}
	}
}
// echo"<pre>";
// print_r($umrbulanan);

$str = "select * from " . $dbname . ".sdm_5bpjs where lokasibpjs='" . $bpjsorg . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	#= kerja
	if (in_array($bar['jenisbpjs'], $arrker)) {
		foreach ($umrbulanan as $key => $nilai) {
			if ($bpjstenaga[$key] != '') {


				$nilai = $nilai;

				#Kondisi baca tipe karyawan Perhitungan BPJS selain PALMA
				if (!empty($nilaiPengganti[$key])) {
					$nilai = $nilaiPengganti[$key] + $natura_karyawan[$key];
				}


				## Jika gaji < dari ump daerah maka gunakan ump daerah
				if ($nilai < $umpDaerah) {
					$nilai = $umpDaerah;
				}

				if ($nilai > $bar['maxgaji']) {
					if ($bar['maxgaji'] > 0) {
						$nilai = $bar['maxgaji'];
					} else {
						$nilai = $nilai;
					}
				} else {

					$nilai = $nilai;
				}




				$bebankaryawan = $bar['bebankaryawan'];
				$bebanperusahaan = $bar['bebanperusahaan'];

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => $param['periodegaji'],
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjs'],
					'jumlah' => ($bebankaryawan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => $param['periodegaji'],
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjsplus'],
					'jumlah' => ($bebanperusahaan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);
			}
		}
	}

	#= kesehatan
	if (in_array($bar['jenisbpjs'], $arrkes)) {

		foreach ($umrbulanan as $key => $nilai) {
			if ($bpjskes[$key] != '') {


				$nilai = $nilai;

				#Kondisi baca tipe karyawan Perhitungan BPJS selain PALMA

				if (!empty($nilaiPengganti[$key])) {
					$nilai = $nilaiPengganti[$key] + $natura_karyawan[$key];
				}

				## Jika gaji < dari ump daerah maka pakek ump daerah
				if ($nilai < $umpDaerah) {
					$nilai = $umpDaerah;
				}

				if ($nilai > $bar['maxgaji']) {
					if ($bar['maxgaji'] > 0) {
						$nilai = $bar['maxgaji'];
					} else {
						$nilai = $nilai;
					}
				} else {
					$nilai = $nilai;
				}


				$bebankaryawan = $bar['bebankaryawan'];
				$bebanperusahaan = $bar['bebanperusahaan'];

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => $param['periodegaji'],
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjs'],
					'jumlah' => ($bebankaryawan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => $param['periodegaji'],
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjsplus'],
					'jumlah' => ($bebanperusahaan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);
			}
		}
	}

	#= pensiun
	if (in_array($bar['jenisbpjs'], $arrpen)) {
		foreach ($umrbulanan as $key => $nilai) {
			if ($bpjspensiun[$key] != '') {

				$nilai = $nilai;

				#Kondisi baca tipe karyawan Perhitungan BPJS selain PALMA
				if (!empty($nilaiPengganti[$key])) {
					$nilai = $nilaiPengganti[$key] + $natura_karyawan[$key];
				}

				## Jika gaji < dari ump daerah maka pakek ump daerah
				if ($nilai < $umpDaerah) {
					$nilai = $umpDaerah;
				}

				if ($nilai > $bar['maxgaji']) {
					if ($bar['maxgaji'] > 0) {
						$nilai = $bar['maxgaji'];
					} else {
						$nilai = $nilai;
					}
				} else {
					$nilai = $nilai;
				}

				$bebankaryawan = $bar['bebankaryawan'];
				$bebanperusahaan = $bar['bebanperusahaan'];

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => $param['periodegaji'],
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjs'],
					'jumlah' => ($bebankaryawan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);

				$readyData[] = array(
					'kodeorg' => $lokasitugas[$key],
					'periodegaji' => $param['periodegaji'],
					'karyawanid' => $key,
					'idkomponen' => $bar['jenisbpjsplus'],
					'jumlah' => ($bebanperusahaan / 100 * ($nilai)),
					'pengali' => 1,
					'hk' => 0
				);
			}
		}
	}
}

// } #tutup bpjs

###################################################################################################################################################
###################################################################################################################################################


$stryy = "select * FROM " . $dbname . ".sdm_gaji_kecil where periodegaji = '" . $param['periodegaji'] . "' and kodeorg ='" . $param['kodeorg'] . "' and idkomponen in ('142','131')";
$comYy = fetchData($stryy);
$adaGajiKecil = count($comYy);
foreach ($comYy as $idx => $rowx) {
	$readyData[] = array(
		'kodeorg' => $rowx['kodeorg'],
		'periodegaji' => $rowx['periodegaji'],
		'karyawanid' => $rowx['karyawanid'],
		'idkomponen' => $rowx['idkomponen'],
		'jumlah' => $rowx['jumlah'],
		'pengali' => 1,
		'hk' => 0
	);
}


## Cek Periode Gaji Kecil
if ($adaGajiKecil > 0) {
	$sCekPeriode = "select distinct * from " . $dbname . ".sdm_5periodegaji_kecil where periode='" . $param['periodegaji'] . "' and kodeorg='" . $param['kodeorg'] . "' and sudahproses=1";
	$res = $owlPDO->query($sCekPeriode) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$numrows = owlBaris($res);
	if ($numrows == 0) {
		exit("Warning : Silahkan tutup periode gaji kecil terlebih dahulu UNIT : " . $param['kodeorg'] . " ,  PERIODE : " . $param['periodegaji'] . " ");
	}
}

$idkomponen_list = [];
foreach ($readyData as $item) {
	$idkomponen_list[] = $item['idkomponen'];
}

$idkomponen_unique = array_unique($idkomponen_list);

// Menyusun string dengan pemisah koma dan tanda kutip
$inkomponen = implode(",", array_map(function ($item) {
	return "'" . $item . "'";
}, $idkomponen_unique));


$strx = "select * FROM " . $dbname . ".sdm_ho_component where id in (" . $inkomponen . ")";
$res = fetchData($strx);
foreach ($res as $bar) {
	if ($bar['plus'] == 1) {
		@$dtkomplus[$bar['id']] = $bar['id'];
	} else {
		@$dtkommin[$bar['id']] = $bar['id'];
	}
	$nmkom[$bar['id']] = $bar['name'];
}


$new_jumlah = [];
foreach ($readyData as $item) {
	$karyawanid = $item['karyawanid'];
	$idkomponen = $item['idkomponen'];
	$jumlah = $item['jumlah'];

	if (!isset($new_jumlah[$karyawanid])) {
		$new_jumlah[$karyawanid] = [];
	}

	// BPJS
	$ids = array('3', '61', '67', '44', '81', '70', '71', '72', '73', '80');
	if (in_array($idkomponen, $ids)) {
		$new_jumlah[$karyawanid][$idkomponen] = $jumlah;
	} else {
		$new_jumlah[$karyawanid][$idkomponen] = $jumlah;
	}
}

$colspan_komplus = count($dtkomplus);
$colspan_komin = count($dtkommin);

array_multisort($dtkomplus, SORT_ASC);
array_multisort($dtkommin, SORT_ASC);

$namakomponen = makeOption($dbname, "sdm_ho_component", "id,name");

$listbutton = "<fieldset style='margin-top: 10px; padding: 10px;'><legend>
			<button class=mybutton name=postBtn id=postBtn onclick=post()>Proses</button>
			<button onclick=excel() class=mybutton name=excel id=excel>" . $_SESSION['lang']['excel'] . "</button>
			</legend></fieldset>";


## Periksa Gaji Minus
$negatif = false;
$list1 = '';
$listx = "<fieldset style='margin-top: 10px; padding: 10px;'><legend><b>TIDAK DAPAT PROSES GAJI, MASIH ADA GAJI DIBAWAH 0 : </b></legend></fieldset>";
$list2 = '';
$list3 = '';
$no = 0;

if ($readyData < 1) {
	exit("Error:Data Kosong");
}

$strx = "select id as komponen, case plus when 0 then -1 else plus end as pengali,name as nakomp FROM " . $dbname . ".sdm_ho_component";
$comRes = fetchData($strx);
$comp = array();
$nakomp = array();
foreach ($comRes as $idx => $row) {
	$comp[$row['komponen']] = $row['pengali'];
	$nakomp[$row['komponen']] = $row['nakomp'];
}

foreach ($id as $key => $val) {
	$sisa[$val[0]] = 0;

	foreach ($readyData as $dat => $bar) {
		if ($val[0] == $bar['karyawanid']) {
			$idsxc = array('70', '71', '72', '73', '80');
			if (!in_array($bar['idkomponen'], $idsxc)) {
				$sisa[$val[0]] += $bar['jumlah'] * $comp[$bar['idkomponen']];
			}
		} else {
			continue;
		}
	}

	## Jika Gaji Minus Tampilkan yang minus saja
	if (round($sisa[$val[0]], 2) < 0) {

		$nox++;
		if ($nox == 1) {
			$list1 = "<table class=sortable border=0 cellspacing=1 cellpadding=5><thead>";
			$list1 .= "<tr align=center class=rowheader>";
			$list1 .= "<th rowspan=2 >" . $_SESSION['lang']['nomor'] . "</th>";
			$list1 .= "<th rowspan=2 >" . $_SESSION['lang']['periodegaji'] . "</th>";
			$list1 .= "<th rowspan=2 >" . $_SESSION['lang']['namakaryawan'] . "</th>";
			$list1 .= "<th rowspan=2 >" . $_SESSION['lang']['statuspajak'] . "</th>";
			$list1 .= "<th rowspan=2 >Kode Catu</th>";

			$list1 .= "<th colspan = " . ($colspan_komplus) . " >" . $_SESSION['lang']['penambah'] . "</th>";

			$list1 .= "<th rowspan=2>Total Penambah</th>";
			$list1 .= "<th colspan = " . $colspan_komin . " >" . $_SESSION['lang']['pengurang'] . "</th>";
			$list1 .= "<th rowspan=2>Total Pengurang</th>";
			$list1 .= "<th rowspan=2>Total Gaji</th>";
			$list1 .= "</tr>";
			$list1 .= "<tr>";
			foreach ($dtkomplus as $komplus) {
				$list1 .= "<th align=center >" . $nmkom[$komplus] . "</th>";
			}




			foreach ($dtkommin as $kommin) {
				$list1 .= "<th align=center >" . $nmkom[$kommin] . "</th>";
			}
			$list1 .= "</tr>";
			$list1 .= "</thead><tbody>";
		}

		$list1 .= "<tr class=rowcontent>";
		$list1 .= "<td align=center>" . $nox . "</td>";
		$list1 .= "<td align=center>" . $param['periodegaji'] . "</td>";
		$list1 .= "<td>" . $namakar[$val[0]] . "</td>";
		$list1 .= "<td align='center'>" . $statuspajak[$val[0]] . "</td>";
		$list1 .= "<td align='center'>" . $kodecatu[$val[0]] . "</td>";
		foreach ($dtkomplus as $komplus) {

			$idsxc = array('70', '71', '72', '73', '80');
			if (!in_array($komplus, $idsxc)) {
				$tt_jumkomplusxc[$val[0]] += $new_jumlah[$val[0]][$komplus];
			}
		}


		foreach ($dtkommin as $kommin) {
			$tt_jumkominxc[$val[0]] += $new_jumlah[$val[0]][$kommin];
		}

		$totalgaji[$val[0]] = round(($tt_jumkomplusxc[$val[0]] - $tt_jumkominxc[$val[0]]), 0);
		$dibelakang[$val[0]] = intval(substr(strval($totalgaji[$val[0]]), -2));

		foreach ($dtkomplus as $komplus) {
			$list1 .= "<td align=right>" . number_format($new_jumlah[$val[0]][$komplus], 0) . "</td>";

			$idsxc = array('70', '71', '72', '73', '80');
			if (!in_array($komplus, $idsxc)) {
				$tt_jumkomplus[$val[0]] += $new_jumlah[$val[0]][$komplus];
			}
			$grandtotalplus[$komplus] += $new_jumlah[$val[0]][$komplus];
		}

		$list1 .= "<td align=right>" . number_format($tt_jumkomplus[$val[0]], 0) . "</td>";
		$grandtotalkomplus += $tt_jumkomplus[$val[0]];

		foreach ($dtkommin as $kommin) {
			$list1 .= "<td align=right>" . number_format($new_jumlah[$val[0]][$kommin], 0) . "</td>";
			$tt_jumkomin[$val[0]] += $new_jumlah[$val[0]][$kommin];
			$grandtotalmin[$kommin] += $new_jumlah[$val[0]][$kommin];
		}

		$list1 .= "<td align=right>" . number_format($tt_jumkomin[$val[0]], 0) . "</td>";
		$grandtotalkommin += $tt_jumkomin[$val[0]];

		$ttt_gaji[$val[0]] = $tt_jumkomplus[$val[0]] - $tt_jumkomin[$val[0]];

		$list1 .= "<td style = color:red align=right>" . number_format($ttt_gaji[$val[0]], 0) . "</td>";
		$grandtotalgaji += $ttt_gaji[$val[0]];

		$negatif = true;

		## Jika Gaji Tidak Minus
	} else {
		$no += 1;


		if ($no == 1) {
			$list2 = "<table class=sortable border=0 cellspacing=1 cellpadding=5><thead>";
			$list2 .= "<tr align=center class=rowheader>";
			$list2 .= "<th rowspan=2 >" . $_SESSION['lang']['nomor'] . "</th>";
			$list2 .= "<th rowspan=2 >" . $_SESSION['lang']['periodegaji'] . "</th>";
			$list2 .= "<th rowspan=2 >" . $_SESSION['lang']['namakaryawan'] . "</th>";
			$list2 .= "<th rowspan=2 >" . $_SESSION['lang']['statuspajak'] . "</th>";
			$list2 .= "<th rowspan=2 >Kode Catu</th>";

			$list2 .= "<th colspan = " . ($colspan_komplus + 1) . " >" . $_SESSION['lang']['penambah'] . "</th>";

			$list2 .= "<th rowspan=2>Total Penambah</th>";
			$list2 .= "<th colspan = " . $colspan_komin . " >" . $_SESSION['lang']['pengurang'] . "</th>";
			$list2 .= "<th rowspan=2>Total Pengurang</th>";
			$list2 .= "<th rowspan=2>Total Gaji</th>";
			$list2 .= "</tr>";
			$list2 .= "<tr>";
			foreach ($dtkomplus as $komplus) {
				$list2 .= "<th align=center >" . $nmkom[$komplus] . "</th>";
			}

			$list2 .= "<th align=center >Pembulatan Gaji</th>";

			foreach ($dtkommin as $kommin) {
				$list2 .= "<th align=center >" . $nmkom[$kommin] . "</th>";
			}
			$list2 .= "</tr>";
			$list2 .= "</thead><tbody>";
		}

		$list2 .= "<tr class=rowcontent>";
		$list2 .= "<td align=center>" . $no . "</td>";
		$list2 .= "<td align=center>" . $param['periodegaji'] . "</td>";
		$list2 .= "<td>" . $namakar[$val[0]] . "</td>";
		$list2 .= "<td align='center'>" . $statuspajak[$val[0]] . "</td>";
		$list2 .= "<td align='center'>" . $kodecatu[$val[0]] . "</td>";

		foreach ($dtkomplus as $komplus) {

			$idsxc = array('70', '71', '72', '73', '80');
			if (!in_array($komplus, $idsxc)) {
				$tt_jumkomplusxc[$val[0]] += $new_jumlah[$val[0]][$komplus];
			}
		}


		foreach ($dtkommin as $kommin) {
			$tt_jumkominxc[$val[0]] += $new_jumlah[$val[0]][$kommin];
		}

		$totalgaji[$val[0]] = round(($tt_jumkomplusxc[$val[0]] - $tt_jumkominxc[$val[0]]), 0);
		$dibelakang[$val[0]] = intval(substr(strval($totalgaji[$val[0]]), -2));
		//echo $totalgaji[$val[0]].'<br>';

		if ($adaGajiKecil > 0) {

			foreach ($dtkomplus as $komplus) {
				$list2 .= "<td align=right>" . number_format($new_jumlah[$val[0]][$komplus], 0) . "</td>";
				$idsxc = array('70', '71', '72', '73', '80');
				if (!in_array($komplus, $idsxc)) {
					$tt_jumkomplus[$val[0]] += $new_jumlah[$val[0]][$komplus];
				}
				$grandtotalplus[$komplus] += $new_jumlah[$val[0]][$komplus];
			}
		} else {

			if ($dibelakang[$val[0]] == 0) {
				$penambahdibelakang[$val[0]] = 0;
			} else {
				$penambahdibelakang[$val[0]] = 100 - $dibelakang[$val[0]];
			}

			$readyData[] = array(
				'kodeorg' => $lokasitugas[$val[0]],
				'periodegaji' => $param['periodegaji'],
				'karyawanid' => $val[0],
				'idkomponen' => '131',
				'jumlah' => $penambahdibelakang[$val[0]],
				'pengali' => 1,
				'hk' => 0
			);


			foreach ($dtkomplus as $komplus) {
				$list2 .= "<td align=right>" . number_format($new_jumlah[$val[0]][$komplus], 0) . "</td>";
				$idsxc = array('70', '71', '72', '73', '80');
				if (!in_array($komplus, $idsxc)) {
					$tt_jumkomplus[$val[0]] += $new_jumlah[$val[0]][$komplus];
				}
				$grandtotalplus[$komplus] += $new_jumlah[$val[0]][$komplus];
			}

			$tt_jumkomplus[$val[0]] += $penambahdibelakang[$val[0]];
			$grandtotalplus['131'] += $penambahdibelakang[$val[0]];

			$list2 .= "<td align=right>" . number_format($penambahdibelakang[$val[0]], 0) . "</td>";
		}



		$list2 .= "<td align=right>" . number_format($tt_jumkomplus[$val[0]], 0) . "</td>";
		$grandtotalkomplus += $tt_jumkomplus[$val[0]];

		foreach ($dtkommin as $kommin) {
			$list2 .= "<td align=right>" . number_format($new_jumlah[$val[0]][$kommin], 0) . "</td>";
			$tt_jumkomin[$val[0]] += $new_jumlah[$val[0]][$kommin];
			$grandtotalmin[$kommin] += $new_jumlah[$val[0]][$kommin];
		}

		$list2 .= "<td align=right>" . number_format($tt_jumkomin[$val[0]], 0) . "</td>";
		$grandtotalkommin += $tt_jumkomin[$val[0]];

		$ttt_gaji[$val[0]] = $tt_jumkomplus[$val[0]] - $tt_jumkomin[$val[0]];

		$list2 .= "<td align=right>" . number_format($ttt_gaji[$val[0]], 0) . "</td>";
		$grandtotalgaji += $ttt_gaji[$val[0]];

		$list2 .= "</tr>";
	}
}

##grand total
$list2 .= "<tr class=rowcontent>";
$list2 .= "<th colspan=5>GRAND TOTAL</th>";

// exit('warning:'.print_r($grandtotalplus));

foreach ($dtkomplus as $komplus) {
	$list2 .= "<th align=center >" . number_format($grandtotalplus[$komplus], 0) . "</th>";
}
$list2 .= "<th align=center>" . number_format($grandtotalplus['131'], 0) . "</th>";
$list2 .= "<th align=center>" . number_format($grandtotalkomplus, 0) . "</th>";


foreach ($dtkommin as $kommin) {
	$list2 .= "<th align=center >" . number_format($grandtotalmin[$kommin], 0) . "</th>";
}
$list2 .= "<th align=center>" . number_format($grandtotalkommin, 0) . "</th>";
$list2 .= "<th align=center>" . number_format($grandtotalgaji, 0) . "</th>";
$list2 .= "</tr>";

$list3 = "</tbody><table>";
switch ($proses) {
	case 'list':
		if ($negatif) {
			echo $listx . $list0 . $list1 . $list3;
		} else {
			echo $listbutton . $list0 . $list2 . $list3;
		}
		break;
	case 'excel':
		$stream = '';
		if ($negatif) {
			$stream .= $listx . $list0 . $list1 . $list3;
		} else {
			$stream .= $listbutton . $list0 . $list2 . $list3;
		}

		$tglSkrg = date("Ymd");
		$nop_ = "GAJI BULANAN";
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
						parent.window.alert('Can't convert to excel format');
						</script>";
				exit;
			} else {
				echo "<script language=javascript1.2>
						window.location='tempExcel/" . $nop_ . ".xls';
						</script>";
			}
			fclose($handle);
		}

		break;

	case 'post':
		try {
			$owlPDO->beginTransaction();

			$karyawanProsesSql = array();
			foreach (array_keys($id) as $karyawanidProses) {
				$karyawanProsesSql[] = $owlPDO->quote($karyawanidProses);
			}

			if (count($karyawanProsesSql) > 0) {
				$sdelStale = "delete from " . $dbname . ".sdm_gaji
					where periodegaji='" . $param['periodegaji'] . "'
					and kodeorg='" . $param['kodeorg'] . "'
					and sumber!='UPLOAD'
					and karyawanid in (
						select distinct karyawanid from " . $dbname . ".datakaryawan
						where sistemgaji='Bulanan'
						and tipekaryawan != 0
						and lokasitugas='" . $param['kodeorg'] . "'
					)
					and karyawanid not in (" . implode(',', $karyawanProsesSql) . ")";
				$owlPDO->exec($sdelStale);
			}
			# Insert All ready data
			// if($dakarbulanan==0){
			//   $sdel="delete from ".$dbname.".sdm_gaji "
			//    . " where idkomponen not in ('26','28') "
			//     . " and periodegaji='".$param['periodegaji']."' and kodeorg='".$param['kodeorg']."' "
			//    . " and karyawanid in (select distinct karyawanid from ".$dbname.".datakaryawan "
			//     . " where sistemgaji='Bulanan' and lokasitugas='".$param['kodeorg']."')  and sumber!='UPLOAD'";
			// }else{
			//   $sdel="delete from ".$dbname.".sdm_gaji "
			//    . " where idkomponen not in ('26','28') "
			//     . " and periodegaji='".$param['periodegaji']."' and kodeorg='".$param['kodeorg']."' "
			//    . " and karyawanid in (select distinct karyawanid from ".$dbname.".datakaryawan_hist "
			//     . " where sistemgaji='Bulanan' and lokasitugas='".$param['kodeorg']."' and periodegaji='".$param['periodegaji']."' and version_type='B'  )  and sumber!='UPLOAD'";
			// }

			#delete dulu baru insert        
			// $owlPDO->exec($sdel);

			$insError = "";
			foreach ($readyData as $row) {
				$row['sumber'] = "";

				if ($row['jumlah'] == 0 or $row['jumlah'] == '' or $row['karyawanid'] == '') {
					continue;
				} else {

					if (!in_array($row['karyawanid'], $karyawan_gajikecil)) {

						## Hapus semua kalau gak ada proses gaji kecil
						$str = "delete from " . $dbname . ".sdm_gaji where idkomponen='" . $row['idkomponen'] . "' 
							and karyawanid='" . $row['karyawanid'] . "' and periodegaji='" . $row['periodegaji'] . "' and sumber!='UPLOAD'";
						$owlPDO->exec($str);
					} else {
						$ids = array('3', '61', '67', '44', '81', '70', '71', '72', '73', '80');

						## Jangan hapus bpjs nya karena dari proses gaji kecil
						if (!in_array($row['idkomponen'], $ids)) {
							$str = "delete from " . $dbname . ".sdm_gaji where idkomponen='" . $row['idkomponen'] . "' 
								and karyawanid='" . $row['karyawanid'] . "' and periodegaji='" . $row['periodegaji'] . "' and sumber!='UPLOAD'";
							$owlPDO->exec($str);
						}
					}

					$queryIns = insertQuery($dbname, 'sdm_gaji', $row);
					$owlPDO->exec($queryIns);
				}
			}


			#= delete 
			$qPeriod = selectQuery($dbname, 'sdm_5periodegaji', 'tanggalmulai,tanggalsampai', "periode='" . $param['periodegaji'] . " ' 
          	and kodeorg='" . $param['kodeorg'] . "' and jenisgaji='B'");
			$resPeriod = fetchData($qPeriod);
			$tanggal1 = $resPeriod[0]['tanggalmulai'];
			$tanggal2 = $resPeriod[0]['tanggalsampai'];

			#= eksekusi datakaryawan site tsb
			$str = "select * from " . $dbname . ".datakaryawan where lokasitugas='" . $param['kodeorg'] . "' and tanggalmasuk<='" . $tanggal2 . "' and tanggalsk<='" . $tanggal2 . "' ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {

				$sqlxa = "delete from " . $dbname . ".datakaryawan_bulanan where periode='" . $param['periodegaji'] . "' and karyawanid='" . $bar['karyawanid'] . "'";
				$owlPDO->exec($sqlxa);

				$sql = "insert into " . $dbname . ".datakaryawan_bulanan(
			  `karyawanid`,`nik`,`namakaryawan`,
			  `tempatlahir`,`tanggallahir`,
			  `warganegara`,`jeniskelamin`,
			  `statusperkawinan`,`tanggalmenikah`,
			  `agama`,`golongandarah`,
			  `levelpendidikan`,`alamataktif`,
			  `provinsi`,`kota`,`kodepos`,
			  `noteleponrumah`,`nohp`,
			  `norekeningbank`,`namabank`,
			  `sistemgaji`,`no_keluarga`,
			  `noktp`,`notelepondarurat`,
			  `tanggalmasuk`,`tanggalpengangkatan`,`tanggalkeluar`,
			  `tipekaryawan`,`jumlahanak`,
			  `jumlahtanggungan`,`statuspajak`,
			  `npwp`,`bpjs`,`lokasipenerimaan`,`kodeorganisasi`,
			  `bagian`,`kodejabatan`,`kodegolongan`,`pensiun`,
			  `lokasitugas`,`email`,`alokasi`,`subbagian`,`jms`,kodecatu,statpremi,suku,statuskaryawan,sim,updateby,periode,levelkaryawan)
			   values('" . $bar['karyawanid'] . "','" . $bar['nik'] . "','" . $bar['namakaryawan'] . "','" . $bar['tempatlahir'] . "','" . $bar['tanggallahir'] . "','" . $bar['warganegara'] . "','" . $bar['jeniskelamin'] . "',
			'" . $bar['statusperkawinan'] . "','" . $bar['tanggalmenikah'] . "','" . $bar['agama'] . "','" . $bar['golongandarah'] . "','" . $bar['levelpendidikan'] . "',
			'" . $bar['alamataktif'] . "','" . $bar['provinsi'] . "','" . $bar['kota'] . "','" . $bar['kodepos'] . "','" . $bar['noteleponrumah'] . "','" . $bar['nohp'] . "',
			'" . $bar['norekeningbank'] . "','" . $bar['namabank'] . "','" . $bar['sistemgaji'] . "','" . $bar['no_keluarga'] . "','" . $bar['noktp'] . "',
			'" . $bar['notelepondarurat'] . "','" . $bar['tanggalmasuk'] . "','" . $bar['tanggalpengangkatan'] . "','" . $bar['tanggalkeluar'] . "','" . $bar['tipekaryawan'] . "',
			'" . $bar['jumlahanak'] . "','" . $bar['jumlahtanggungan'] . "','" . $bar['statuspajak'] . "','" . $bar['npwp'] . "','" . $bar['bpjs'] . "','" . $bar['lokasipenerimaan'] . "',
			'" . $bar['kodeorganisasi'] . "','" . $bar['bagian'] . "','" . $bar['kodejabatan'] . "','" . $bar['kodegolongan'] . "','" . $bar['pensiun'] . "',
			'" . $bar['lokasitugas'] . "','" . $bar['email'] . "','" . $bar['alokasi'] . "','" . $bar['subbagian'] . "','" . $bar['jms'] . "','" . $bar['kodecatu'] . "',
			'" . $bar['statpremi'] . "','" . $bar['suku'] . "','" . $bar['statuskaryawan'] . "','" . $bar['sim'] . "','" . $bar['updateby'] . "','" . $param['periodegaji'] . "','" . $bar['levelkaryawan'] . "')";

				$owlPDO->exec($sql);
			}

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, datakaryawan bulanan " . addslashes($e->getMessage());
			die();
		}


		break;
	default:
		break;
}
