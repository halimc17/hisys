<?php
ini_set('display_errors', 0);
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');
require_once('lib/fpdf.php');


$proses   = checkPostGet('proses', '');
$unit     = checkPostGet('unit', '');
$afd      = checkPostGet('afd', '');
$prd      = checkPostGet('prd', '');
$tahap    = checkPostGet('tahap', '');
$tgl1     = tanggalsystemn(checkPostGet('tgl1', ''));
$tgl2     = tanggalsystemn(checkPostGet('tgl2', ''));

$tglawal1 = explode("-", $tgl1);
$tglawal2 = explode("-", $tgl2);

$prdtgl1  = $tglawal1[0] . "-" . $tglawal1[1];
$prdtgl2  = $tglawal2[0] . "-" . $tglawal2[1];

if ($unit == '') {
	exit("Warning : Unit wajib diisi ");
}
if ($tgl1 == '') {
	exit("Warning : Tanggal wajib diisi ");
}
if ($afd == '') {
	exit("Warning : Divisi wajib diisi ");
}


if ($prd != $prdtgl1 or $prd != $prdtgl2) {
	exit("Warning : Periode dan tanggal tidak sesuai.");
}


if ($prd < '2024-12') {
	exit("Warning : Proses hanya bisa dilakukan untuk periode Desember 2024 keatas.");
}

@$nmkar      = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
@$nikkar     = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$nmorg2       = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmorg       = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok');



#Cek Periode gaji
$str = "select max(sudahproses) as prd from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $unit . "' and periode='" . $prd . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$prdgaji = $bar['prd'];
}

#Cek Periode akutansi
$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and periode='" . $prd . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$prdakt = $bar['tutupbuku'];
}

## Ambil proporsi panen tahun tanam (JJG)
$str = "select a.notransaksi,a.tanggal,a.karyawanid,a.tahuntanam, a.jjg as jjgpro, a.brondolan as bronpro,a.blokkecil,a.kodeorg,a.banjir from " . $dbname . ".kebun_proporsitahuntanam a  
		left  join " . $dbname . ".datakaryawan c on a.karyawanid=c.karyawanid  
		where	1=1 and a.tanggal= '" . $tgl1 . "' and c.karyawanid in (select karyawanid from datakaryawan where lokasitugas = '" . $unit . "' and subbagian ='" . $afd . "')	 
		order by a.tanggal,a.tahuntanam asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$karyawanid[$bar['karyawanid']] = $bar['karyawanid'];

	## jjg dan brondolan blok besar 
	@$list[$bar['tanggal']][$bar['karyawanid']][$bar['kodeorg']] += $bar['jjgpro'];
	@$listbrondol[$bar['tanggal']][$bar['karyawanid']][$bar['kodeorg']] += $bar['bronpro'];

	## jjg dan brondolan blok kecil
	@$listjjg_blokkecil[$bar['tanggal']][$bar['karyawanid']][$bar['kodeorg']][$bar['blokkecil']] += $bar['jjgpro'];
	@$listbrondol_blokkecil[$bar['tanggal']][$bar['karyawanid']][$bar['kodeorg']][$bar['blokkecil']] += $bar['bronpro'];

	## Tahun Tanam
	@$tahunTanam[$bar['tanggal']][$bar['karyawanid']][$bar['kodeorg']][$bar['blokkecil']] = $bar['tahuntanam'];

	## Status banjir per blok kecil
	@$statusBanjir[$bar['tanggal']][$bar['karyawanid']][$bar['kodeorg']][$bar['blokkecil']] = (int)$bar['banjir'];

	## Jumalh Blok yang dipanen
	@$jumlahBlokPanen[$bar['tanggal']][$bar['karyawanid']] += 1;

	## Get Periode From Tanggal
	$exptgl = explode("-", $bar['tanggal']);
	$prdgj = $exptgl[0] . "-" . $exptgl[1];
}

if (count($list) == 0) {
	exit("warning : Data kegiatan panen tidak ada untuk  Unit " . $unit . " Tanggal " . $tgl1 . ", <b> Pastikan kegiatan panen sudah diposting </b>");
}

$arrtopo = array();
$jlhtopo = array();

# Ambil data Hektar panen
$str = "select a.* from " . $dbname . ".kebun_rekaphancakpanen a
	left join " . $dbname . ".datakaryawan b on a.nik=b.karyawanid
	where a.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' and b.subbagian like '" . $afd . "%' and posting = 1 order by tanggal asc";
// exit("Warning: " . $str);
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$Hektarpanen[$bar['tanggal']][$bar['nik']] += $bar['hapanen'];
	$Hektarpanenx[$bar['tanggal']][$bar['kodeorg']][$bar['nik']] += $bar['hapanen'];
}

if (count($Hektarpanen) == 0) {
	exit("warning : Data rekap ancak panen tidak ada untuk  Unit " . $unit . " Tanggal " . $tgl1 . ", <b> Pastikan rekap ancak panen sudah diposting </b>");
}

$datapanentgl = array();
$datapanentglxz = array();


if ($proses == 'excel') {
	$brd = "border=1";
} else {
	$brd = '';
}


$sDenda = "SELECT id AS nourut, kodedenda, deskripsi, status FROM " . $dbname . ".kebun_5kodedendapanen order by id asc";
$rDenda = fetchData($sDenda);
$kodeurut = array();
foreach ($rDenda as $val) {
	$kodeurut[$val['nourut']] = $val['kodedenda'];
}

$sDenda = "SELECT kodedenda, denda FROM " . $dbname . ".kebun_5dendapanen where kodeorg='" . $unit . "' order by kodedenda asc";
$rDenda = fetchData($sDenda);
$pengalidenda = array();
foreach ($rDenda as $val) {
	$pengalidenda[$val['kodedenda']] = $val['denda'];
}

## Denda Panen Ancak Panen
$dendapanen = array();
$str = "select a.* from " . $dbname . ".kebun_rekapmutuhancakpanen a
	left join " . $dbname . ".datakaryawan b on a.nik=b.karyawanid
	where a.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' and b.subbagian like '" . $unit . "%'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$dendapanen[$bar['tanggal']][$bar['nik']] += (($bar['penalti1'] * $pengalidenda[$kodeurut['1']]) + ($bar['penalti2'] * $pengalidenda[$kodeurut['2']]) + ($bar['penalti3'] * $pengalidenda[$kodeurut['3']]) + ($bar['penalti4'] * $pengalidenda[$kodeurut['4']]) + ($bar['penalti5'] * $pengalidenda[$kodeurut['5']]) + ($bar['penalti6'] * $pengalidenda[$kodeurut['6']]) + ($bar['penalti7'] * $pengalidenda[$kodeurut['7']]) + ($bar['penalti8'] * $pengalidenda[$kodeurut['8']]) + ($bar['penalti9'] * $pengalidenda[$kodeurut['9']]) + ($bar['penalti10'] * $pengalidenda[$kodeurut['10']]) + ($bar['penalti11'] * $pengalidenda[$kodeurut['11']]) + ($bar['penalti12'] * $pengalidenda[$kodeurut['12']]));
}


$w = "";
if (count($tglpnn) != 0) {
	$w = " and (b.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "' or b.tanggal in ('" . implode("','", $tglpnn) . "'))";
} else {
	$w = " and b.tanggal between '" . $tgl1 . "' and '" . $tgl2 . "'";
}

## kegiatan panen 
$str = "select a.notransaksi,b.nikmandor,b.nikmandor1,b.nikasisten,a.nourut,a.kodeorg,a.tph,a.sesi,b.tanggal,a.nik,a.rupiahpenalty,a.luaspanen,b.jenispanen,b.kontanan from " . $dbname . ".kebun_prestasi a  
		left  join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
		left  join " . $dbname . ".datakaryawan c on a.nik=c.karyawanid  
		where	1=1 " . $w . " and c.lokasitugas='" . $unit . "' and c.subbagian like '" . $afd . "%'
		and ((b.noreferensi='' and b.deviceid is null) or (b.noreferensi!='' and b.deviceid!='')) 
		and b.tipetransaksi='PNN'
		group by a.notransaksi,a.nourut,a.kodeorg,a.tph,a.sesi,b.tanggal 
		order by b.tanggal asc";
// exit("Warning: " . $str);
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {

	## Rupiah Penalty Kegiatan Panen
	@$dendapanen[$bar['tanggal']][$bar['nik']] += $bar['rupiahpenalty'];

	## HA Panen
	// Jika dari HA Panen sudah ada 0 kan pakai luas panen dari BKM
	if (isset($Hektarpanenx[$bar['tanggal']][$bar['kodeorg']][$bar['nik']])) {
		$Hektarpanenx[$bar['tanggal']][$bar['kodeorg']][$bar['nik']] = 0;
	}
	$Hektarpanenx[$bar['tanggal']][$bar['kodeorg']][$bar['nik']] += $bar['luaspanen'];

	## Mandor Panen
	$mandor[$bar['tanggal']][$bar['kodeorg']][$bar['nik']] = $bar['nikmandor'];

	## Mandor 1
	$mandor1[$bar['tanggal']][$bar['kodeorg']][$bar['nik']] = $bar['nikmandor1'];

	## Kerani Panen
	$kerani[$bar['tanggal']][$bar['kodeorg']][$bar['nik']] = $bar['nikasisten'];

	## No BKM
	$nobkm[$bar['tanggal']][$bar['kodeorg']][$bar['nik']] = $bar['notransaksi'];

	## Jenis Panen
	$jenispanen[$bar['tanggal']][$bar['nik']][$bar['kodeorg']] = $bar['jenispanen'];

	if (strtoupper(trim($bar['kontanan'])) == 'KONTAN') {
		$kontanan[$bar['tanggal']][$bar['nik']][$bar['kodeorg']] = 'KONTAN';
	} elseif (!isset($kontanan[$bar['tanggal']][$bar['nik']][$bar['kodeorg']])) {
		$kontanan[$bar['tanggal']][$bar['nik']][$bar['kodeorg']] = '';
	}
}

#ambil kg wb
$w = "";
$w = " and tanggalpanen between '" . $tgl1 . "' and '" . $tgl2 . "'";
if (getNamaOrg($unit, 'induk') == 'CAR' or getNamaOrg($unit, 'induk') == 'LAN') {
	$dataunitx = '';
	$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='CAR' and tipe in ('KEBUN')";
	$res = fetchdata($str);
	foreach ($res as $val) {
		if ($dataunitx == "") {
			$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
		} else {
			$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
		}
	}

	$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='LAN' and tipe in ('KEBUN')";
	$res = fetchdata($str);
	foreach ($res as $val) {
		if ($dataunitx == "") {
			$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
		} else {
			$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
		}
	}
}

if (getNamaOrg($unit, 'induk') == 'DMA' or getNamaOrg($unit, 'induk') == 'MHA') {
	$dataunitx = '';
	$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='DMA' and tipe in ('KEBUN')";
	$res = fetchdata($str);
	foreach ($res as $val) {
		if ($dataunitx == "") {
			$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
		} else {
			$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
		}
	}

	$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='MHA' and tipe in ('KEBUN')";
	$res = fetchdata($str);
	foreach ($res as $val) {
		if ($dataunitx == "") {
			$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
		} else {
			$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
		}
	}
}

## Ambil KG WB
$str = "select * from " . $dbname . ".kebun_spbdt_detail where	1=1 " . $w . " and substr(blok,1,4) in (" . $dataunitx . ") and JJG > 0  order by nospb asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {

	@$kgwbnetto[$bar['tanggalpanen']][$bar['indukblok']] += $bar['kgwbnetto'];
	@$ttwb[$bar['tanggalpanen']][$bar['indukblok']] = $bar['blok'];
	@$jjgkirim[$bar['tanggalpanen']][$bar['indukblok']] += $bar['jjg'];
	@$brdkirim[$bar['tanggalpanen']][$bar['indukblok']] += $bar['brondolan'];

	@$nospb_panen[$bar['tanggalpanen']][$bar['indukblok']] = $bar['nospb'];
}

if (count($kgwbnetto) == 0) {
	exit("warning : Data spb tidak ada untuk tanggal " . $tgl1 . ", <b> Pastikan sudah proses ambil kg timbangan V2 </b>");
}


## Ambil JJG Panen
$str = "select * from " . $dbname . ".kebun_proporsitahuntanam where 1=1 and tanggal= '" . $tgl1 . "' and substr(kodeorg,1,4) in (" . $dataunitx . ") and JJG > 0 ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$jjgPanen[$bar['tanggal']][$bar['kodeorg']] += $bar['jjg'];
	@$brdKirim[$bar['tanggal']][$bar['kodeorg']] += $bar['brondolan'];
}


$tab .= "<table class=sortable cellspacing=1 cellpadding=5 $brd>";
$tab .= "<thead><tr class=rowheader>";
$tab .= "<td align=center colspan =6>HITUNG BJR PER BLOK</td>";
$tab .= "</tr>";
$tab .= "<tr class=rowheader>";
$tab .= "<td align=center rowspan=2 width=50px>Tanggal Panen</td>";
$tab .= "<td align=center rowspan=2 width=75px>Blok Besar</td>";
$tab .= "<td align=center  width=75px>PANEN</td>";
$tab .= "<td align=center colspan = 3 width=75px>SPB</td>";
$tab .= "</tr>";
$tab .= "<tr class=rowheader>";
$tab .= "<td align=center width=75px>JJG PANEN</td>";
$tab .= "<td align=center width=75px>JJG KIRIM</td>";
$tab .= "<td align=center width=75px>KG TIMBANGAN (NETTO)</td>";
$tab .= "<td align=center width=75px>BJR BLOK</td>";
$tab .= "</tr>";


$tab .= "</thead>";
$bjrspb = array();

foreach (@$ttwb as $tglpanen => $key1) {
	foreach (@$key1 as $blok => $value) {
		if ($kgwbnetto[$tglpanen][$blok] > 0 and $jjgkirim[$tglpanen][$blok] > 0) {
			$bjrspb[$tglpanen][$blok] = round($kgwbnetto[$tglpanen][$blok] / $jjgkirim[$tglpanen][$blok], 2);
		} else {
			$bjrspb[$tglpanen][$blok] = 0;
		}

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td align=center>" . tanggalnormal($tglpanen) . "</td>";
		$tab .= "<td align=center>" . $nmorg[$blok] . " - " . $blok . "</td>";
		$tab .= "<td align=right>" . @number_format($jjgPanen[$tglpanen][$blok], 2) . "</td>";
		$tab .= "<td align=right>" . @number_format($jjgkirim[$tglpanen][$blok], 2) . "</td>";
		$tab .= "<td align=right>" . @number_format($kgwbnetto[$tglpanen][$blok], 2) . "</td>";
		$tab .= "<td align=right>" . @number_format($bjrspb[$tglpanen][$blok], 2) . "</td>";
		$tab .= "</tr>";

		$bjrBlokKecil[$tglpanen][$blok] = $bjrspb[$tglpanen][$blok];

		// $dataxx[$tglpanen][$blok]['bjr'] = $bjrspb[$tglpanen][$blok];
		// $dataxx[$tglpanen][$blok]['kgwb'] = $kgwbnetto[$tglpanen][$blok];
		// $dataxx[$tglpanen][$blok]['jjg'] = $jjgkirim[$tglpanen][$blok];
	}
}

$tab .= "</table><div style=clear:both></div><hr>";



if (getNamaOrg($unit, 'induk') == 'CAR' or getNamaOrg($unit, 'induk') == 'LAN') {
	$dataunitx = '';
	$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='CAR' and tipe in ('KEBUN')";
	$res = fetchdata($str);
	foreach ($res as $val) {
		if ($dataunitx == "") {
			$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
		} else {
			$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
		}
	}

	$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='LAN' and tipe in ('KEBUN')";
	$res = fetchdata($str);
	foreach ($res as $val) {
		if ($dataunitx == "") {
			$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
		} else {
			$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
		}
	}

	$str = "select * from " . $dbname . ".kebun_spbdt_detail where	1=1 " . $w . " and substr(blok,1,4) in (" . $dataunitx . ") and brondolan > 0 order by nospb asc";
} else {
	$str = "select * from " . $dbname . ".kebun_spbdt_detail where	1=1 " . $w . " and blok like '" . $afd . "%' and blok like '" . $unit . "%' and brondolan > 0 order by nospb asc";
}

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	if (($bar['kgwbnetto'] - $bar['brondolan']) < 0) {
		@$kgwbnetto_brondolan[$bar['nospb']][$bar['tanggalpanen']][$bar['indukblok']][$bar['blok']] += ($bar['kgwbnetto']);
	} else {
		@$kgwbnetto_brondolan[$bar['nospb']][$bar['tanggalpanen']][$bar['indukblok']][$bar['blok']] += ($bar['kgwbnetto'] - $bar['brondolan']);
	}

	@$ttwbx[$bar['nospb']][$bar['tanggalpanen']][$bar['indukblok']][$bar['blok']] = $bar['blok'];
	@$brondolankirim[$bar['nospb']][$bar['tanggalpanen']][$bar['indukblok']][$bar['blok']] += $bar['brondolan'];

	@$nospb_panen_brondolan[$bar['tanggalpanen']][$bar['indukblok']][$bar['blok']] = $bar['nospb'];
}

$basiskg = array();
if (getNamaOrg($unit, 'induk') == 'CAR' or getNamaOrg($unit, 'induk') == 'LAN') {
	$dataunitx = '';
	$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='CAR' and tipe in ('KEBUN')";
	$res = fetchdata($str);
	foreach ($res as $val) {
		if ($dataunitx == "") {
			$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
		} else {
			$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
		}
	}

	$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='LAN' and tipe in ('KEBUN')";
	$res = fetchdata($str);
	foreach ($res as $val) {
		if ($dataunitx == "") {
			$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
		} else {
			$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
		}
	}

	$str = "
			SELECT a.*
			FROM " . $dbname . ".kebun_5basispanen3 a
			INNER JOIN (
				SELECT kodeorg, jenishari, tahuntanam, banjir, MAX(periode) AS periode
				FROM " . $dbname . ".kebun_5basispanen3
				WHERE periode <= '" . $prd . "'
				AND kodeorg IN (" . $dataunitx . ")
				GROUP BY kodeorg, jenishari, tahuntanam, banjir
			) b ON a.kodeorg = b.kodeorg
				AND a.jenishari = b.jenishari
				AND a.tahuntanam = b.tahuntanam
				AND a.banjir = b.banjir
				AND a.periode = b.periode
		";
}

if (getNamaOrg($unit, 'induk') == 'MHA' or getNamaOrg($unit, 'induk') == 'DMA') {
	$dataunitx = '';
	$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='MHA' and tipe in ('KEBUN')";
	$res = fetchdata($str);
	foreach ($res as $val) {
		if ($dataunitx == "") {
			$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
		} else {
			$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
		}
	}

	$str = "select kodeorganisasi,namaorganisasi from " . $dbname . ".organisasi where induk='DMA' and tipe in ('KEBUN')";
	$res = fetchdata($str);
	foreach ($res as $val) {
		if ($dataunitx == "") {
			$dataunitx .= "'" . $val['kodeorganisasi'] . "'";
		} else {
			$dataunitx .= ",'" . $val['kodeorganisasi'] . "'";
		}
	}

	$str = "
			SELECT a.*
			FROM " . $dbname . ".kebun_5basispanen3 a
			INNER JOIN (
				SELECT kodeorg, jenishari, tahuntanam, banjir, MAX(periode) AS periode
				FROM " . $dbname . ".kebun_5basispanen3
				WHERE periode <= '" . $prd . "'
				AND kodeorg IN (" . $dataunitx . ")
				GROUP BY kodeorg, jenishari, tahuntanam, banjir
			) b ON a.kodeorg = b.kodeorg
				AND a.jenishari = b.jenishari
				AND a.tahuntanam = b.tahuntanam
				AND a.banjir = b.banjir
				AND a.periode = b.periode
		";
}
// exit($str);
$texa = $str;
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$flagBanjir = (int)$bar['banjir'];
	$basiskg[$bar['kodeorg']][$bar['jenishari']][$bar['tahuntanam']][$flagBanjir] = $bar['basiskg'];
	$basisha[$bar['kodeorg']][$bar['jenishari']][$bar['tahuntanam']][$flagBanjir] = $bar['basisha'];
	$rplb1[$bar['kodeorg']][$bar['jenishari']][$bar['tahuntanam']][$flagBanjir] = $bar['premilebihbasis'];
	$rpbrd[$bar['kodeorg']][$bar['jenishari']][$bar['tahuntanam']][$flagBanjir] = $bar['premibrondolan'];
	$rpkesulitan[$bar['kodeorg']][$bar['jenishari']][$bar['tahuntanam']][$flagBanjir] = $bar['premikesulitan'];
	$rpkekehadiran[$bar['kodeorg']][$bar['jenishari']][$bar['tahuntanam']][$flagBanjir] = $bar['premikehadiran'];
}

if ($proses == 'excel') {
	$tab .= "<table class=sortable cellspacing=1 border=1>";
} else {
	$tab .= "<div class=table-scroll><table class=sortable cellspacing=1 cellpadding=5>";
}

$tab .= "<thead>";
$tab .= "<tr class=rowheader>";
$tab .= "<th rowspan=2 align=center>" . $_SESSION['lang']['nourut'] . "</th>";
$tab .= "<th rowspan=2 align=center>Tanggal Panen</th>";
$tab .= "<th rowspan=2 align=center>Jenis Hari</th>";
$tab .= "<th rowspan=2 align=center>No.BKM</th>";
$tab .= "<th rowspan=2 align=center>Mandor 1</th>";
$tab .= "<th rowspan=2 align=center>Mandor Panen</th>";
$tab .= "<th rowspan=2 align=center>Kerani Panen</th>";
$tab .= "<th rowspan=2 align=center>" . $_SESSION['lang']['nik2'] . "</th>";
$tab .= "<th rowspan=2 align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>";
$tab .= "<th rowspan=2 align=center>" . $_SESSION['lang']['blok'] . " Besar</th>";
$tab .= "<th rowspan=2 align=center>" . $_SESSION['lang']['blok'] . " Kecil</th>";
$tab .= "<th rowspan=2 align=center>" . $_SESSION['lang']['tahuntanam'] . "</th>";
$tab .= "<th rowspan=2 align=center>HA Panen</th>";
$tab .= "<th rowspan=2 align=center>" . $_SESSION['lang']['jjg'] . "</th>";
$tab .= "<th rowspan=2 align=center>" . $_SESSION['lang']['bjr'] . "</th>";
$tab .= "<th rowspan=2 align=center>Total Kg</th>";
$tab .= "<th rowspan=2 align=center>Basis <br> Tahun Tanam</th>";
$tab .= "<th rowspan=2 align=center>Basis % </th>";
$tab .= "<th rowspan=2 align=center>HK </th>";
$tab .= "<th rowspan=2 align=center>POT HK </th>";
$tab .= "<th rowspan=2 align=center>Basis Pakai</th>";
$tab .= "<th rowspan=2 align=center>Basis Baru </th>";
$tab .= "<th rowspan=2 align=center>Lebih Basis </th>";
$tab .= "<th colspan=2 align=center> Rupiah HK </th>";
$tab .= "<th rowspan=2 align=center> Total Upah  (Rp) </th>";
$tab .= "<th colspan=3 align=center> Premi </th>";
$tab .= "<th rowspan=2 align=center> Total Premi  (Rp) </th>";
$tab .= "<th rowspan=2 align=center> Brondolan </th>";
$tab .= "<th rowspan=2 align=center>Total Brondol (Rp)</th>";
$tab .= "<th rowspan=2 align=center>Denda Panen</th>";
$tab .= "<th rowspan=2 align=center> TOTAL </th>";
$tab .= "</tr>";
$tab .= "<tr>";
$tab .= "<th align=center> Upah 1 HK  </th>";
$tab .= "<th align=center> Potongan Upah </th>";

$tab .= "<th align=center> Lebih Basis </th>";
$tab .= "<th align=center> Premi Kesulitan </th>";
$tab .= "<th align=center> Premi Kehadiran </th>";

$tab .= "</tr>";
$tab .= "</thead><tbody>";

$dataKarid = array();
$dataBlok = array();
$dataJjg = array();
$dataTahunTanam = array();
$dataBanjir = array();
$dataBasis = array();
$persenkg = array();
$jumlahDataBlok = array();

foreach ($listjjg_blokkecil as $tgl => $val1) {

	$jenispremi = getjenisharikerjaV2($unit, $tgl);
	if ($jenispremi == 'JUMAT') {
		$jenispremi = 'KERJA';
	}
	if ($jenispremi == 'LIBUR NASIONAL') {
		$jenispremi = 'LN';
	}
	if ($jenispremi == 'HARI MINGGU') {
		$jenispremi = 'HL';
	}

	## Nentukan basis baru
	## Prioritas:
	## 1. JJG paling besar
	## 2. Jika JJG sama, ambil basis panen paling kecil
	foreach ($val1 as $karid => $val2) {
		foreach ($val2 as $kdorg => $val3) {
			foreach ($val3 as $blokkecil => $jjg) {

				## Data kandidat blok
				$tahunTanamKandidat = $tahunTanam[$tgl][$karid][$kdorg][$blokkecil];

				$banjirKandidat = isset($statusBanjir[$tgl][$karid][$kdorg][$blokkecil])
					? (int)$statusBanjir[$tgl][$karid][$kdorg][$blokkecil]
					: 0;

				$kodeUnitBasis = substr($kdorg, 0, 4);

				## Ambil basis dari blok kandidat
				if ($jenispanen[$tgl][$karid][$kdorg] == '1') {
					$basisKandidat = isset($basisha[$kodeUnitBasis][$jenispremi][$tahunTanamKandidat][$banjirKandidat])
						? $basisha[$kodeUnitBasis][$jenispremi][$tahunTanamKandidat][$banjirKandidat]
						: 0;
				} else {
					$basisKandidat = isset($basiskg[$kodeUnitBasis][$jenispremi][$tahunTanamKandidat][$banjirKandidat])
						? $basiskg[$kodeUnitBasis][$jenispremi][$tahunTanamKandidat][$banjirKandidat]
						: 0;
				}

				## Tentukan apakah blok ini yang akan dipakai
				$pilihBlok = false;

				## Belum ada blok yang dipilih
				if (!isset($dataJjg[$karid])) {
					$pilihBlok = true;
				}
				## JJG kandidat lebih besar
				elseif ($jjg > $dataJjg[$karid]) {
					$pilihBlok = true;
				}
				## JJG sama, ambil basis yang lebih kecil
				elseif (
					$jjg == $dataJjg[$karid]
					&& $basisKandidat < $dataBasis[$tgl][$karid]
				) {
					$pilihBlok = true;
				}

				if ($pilihBlok) {
					$dataKarid[$karid] = $karid;
					$dataBlok[$karid] = $blokkecil;
					$dataJjg[$karid] = $jjg;
					$dataTahunTanam[$karid] = $tahunTanamKandidat;
					$dataBanjir[$karid] = $banjirKandidat;

					## Basis yang dipakai
					$dataBasis[$tgl][$karid] = $basisKandidat;

					## Premi kesulitan mengikuti blok/basis yang terpilih
					$dataPremikesulitan[$tgl][$karid] =
						isset($rpkesulitan[$kodeUnitBasis][$jenispremi][$tahunTanamKandidat][$banjirKandidat])
						? $rpkesulitan[$kodeUnitBasis][$jenispremi][$tahunTanamKandidat][$banjirKandidat]
						: 0;

					## Premi kehadiran mengikuti blok/basis yang terpilih
					$dataPremiKehadiran[$tgl][$karid] =
						isset($rpkekehadiran[$kodeUnitBasis][$jenispremi][$tahunTanamKandidat][$banjirKandidat])
						? $rpkekehadiran[$kodeUnitBasis][$jenispremi][$tahunTanamKandidat][$banjirKandidat]
						: 0;
				}
			}
		}
	}


	## Ambil Total KG dan Persen Basis
	foreach ($val1 as $karid => $val2) {
		foreach ($val2 as $kdorg => $val3) {
			foreach ($val3 as $blokkecil => $jjg) {
				// Kalau belum diset atau ditemukan jjg yang lebih besar, update
				// if (!isset($dataJjg[$karid]) || $jjg > $dataJjg[$karid]) {
				// 	$dataKarid[$karid] = $karid;
				// 	$dataBlok[$karid] = $blokkecil;
				// 	$dataJjg[$karid] = $jjg;
				// 	$dataTahunTanam[$karid] = $tahunTanam[$tgl][$karid][$kdorg][$blokkecil];
				// 	$dataBasis[$tgl][$karid] = $basiskg[substr($kdorg,0,4)][$jenispremi][$dataTahunTanam[$karid]];
				// }

				@$totalKg_Kirim[$tgl][$karid][$kdorg][$blokkecil] = $jjg * $bjrBlokKecil[$tgl][$kdorg];

				if ($dataBasis[$tgl][$karid] > 0) {
					if ($jenispanen[$tgl][$karid][$kdorg] == '1') {
						$persenkg[$tgl][$karid][$kdorg][$blokkecil] = fixnan($Hektarpanenx[$tgl][$kdorg][$karid] / $dataBasis[$tgl][$karid]);
						$persenkgxz[$tgl][$karid][$blokkecil] = fixnan($Hektarpanenx[$tgl][$kdorg][$karid] / $dataBasis[$tgl][$karid]);
					} else {
						// if($totalKg_Kirim[$tgl][$karid][$kdorg][$blokkecil]>0){
						$persenkg[$tgl][$karid][$kdorg][$blokkecil] = fixnan($totalKg_Kirim[$tgl][$karid][$kdorg][$blokkecil] / $dataBasis[$tgl][$karid]);
						$persenkgxz[$tgl][$karid][$blokkecil] = fixnan($totalKg_Kirim[$tgl][$karid][$kdorg][$blokkecil] / $dataBasis[$tgl][$karid]);
						// }
					}
				} else {
					$persenkg[$tgl][$karid][$kdorg][$blokkecil] = 0;
					$persenkgxz[$tgl][$karid][$blokkecil] = 0;
				}

				$ttlpersenkg[$tgl][$karid] += $persenkg[$tgl][$karid][$kdorg][$blokkecil];
				$ttlpersenkg2[$tgl][$karid] += $persenkg[$tgl][$karid][$kdorg][$blokkecil];

				$totalKgblok[$tgl][$karid] += $jjg * $bjrBlokKecil[$tgl][$kdorg];
			}
		}
	}
}

## Proporsi HK
foreach ($listjjg_blokkecil as $tgl => $val1) {
	$jenispremi = getjenisharikerjaV2($unit, $tgl);
	if ($jenispremi == 'JUMAT') {
		$jenispremi = 'KERJA';
	}
	if ($jenispremi == 'LIBUR NASIONAL') {
		$jenispremi = 'LN';
	}
	if ($jenispremi == 'HARI MINGGU') {
		$jenispremi = 'HL';
	}

	foreach ($val1 as $karid => $val2) {
		$jumlahBarisHK = 0;
		foreach ($val2 as $val3) {
			$jumlahBarisHK += count($val3);
		}

		$urutHK = 0;

		if ($ttlpersenkg[$tgl][$karid] >= 1) {
			$sisaHK[$tgl][$karid] = 1.00;
		} else {
			$sisaHK[$tgl][$karid] = round($ttlpersenkg[$tgl][$karid], 2);
		}

		foreach ($val2 as $kdorg => $val3) {
			foreach ($val3 as $blokkecil => $jjg) {
				$urutHK += 1;
				$isDataTerakhir = ($urutHK == $jumlahBarisHK);

				## Libur nasional tetap mendapatkan total 1 HK meskipun basis 0.
				if ($jenispremi == 'LN') {
					if (!isset($sisaHKLibur[$tgl][$karid])) {
						$sisaHKLibur[$tgl][$karid] = 1.00;
					}

					if ($totalKgblok[$tgl][$karid] > 0) {
						if ($isDataTerakhir) {
							$hkPro[$tgl][$karid][$kdorg][$blokkecil] = round($sisaHKLibur[$tgl][$karid], 2);
						} else {
							$proporsiHKLibur = fixnan($totalKg_Kirim[$tgl][$karid][$kdorg][$blokkecil] / $totalKgblok[$tgl][$karid]);
							$hkPro[$tgl][$karid][$kdorg][$blokkecil] = round($proporsiHKLibur, 2);
							$sisaHKLibur[$tgl][$karid] = round($sisaHKLibur[$tgl][$karid] - $hkPro[$tgl][$karid][$kdorg][$blokkecil], 2);
						}
					} else {
						$hkPro[$tgl][$karid][$kdorg][$blokkecil] = 0;
					}

					$persenkg[$tgl][$karid][$kdorg][$blokkecil] = $hkPro[$tgl][$karid][$kdorg][$blokkecil];
					@$totalHK[$tgl][$karid] += $hkPro[$tgl][$karid][$kdorg][$blokkecil];
					$jumlahDataBlok[$tgl][$karid] += 1;
					continue;
				}

				## Hari Minggu tidak mendapatkan HK, hanya premi.
				if ($jenispremi == 'HL') {
					$hkPro[$tgl][$karid][$kdorg][$blokkecil] = 0;
					$persenkg[$tgl][$karid][$kdorg][$blokkecil] = 0;
					@$totalHK[$tgl][$karid] += 0;
					$jumlahDataBlok[$tgl][$karid] += 1;
					continue;
				}

				if ($isDataTerakhir) {
					$hkPro[$tgl][$karid][$kdorg][$blokkecil] = round($sisaHK[$tgl][$karid], 2);
				} else {
					if ($ttlpersenkg[$tgl][$karid] >= 1) {
						$proporsiHK = fixnan($persenkg[$tgl][$karid][$kdorg][$blokkecil] / $ttlpersenkg[$tgl][$karid]);
					} else {
						$proporsiHK = $persenkg[$tgl][$karid][$kdorg][$blokkecil];
					}

					$hkPro[$tgl][$karid][$kdorg][$blokkecil] = round($proporsiHK, 2);
					$sisaHK[$tgl][$karid] = round($sisaHK[$tgl][$karid] - $hkPro[$tgl][$karid][$kdorg][$blokkecil], 2);
				}

				if ($ttlpersenkg[$tgl][$karid] < 1) {
					$persenkg[$tgl][$karid][$kdorg][$blokkecil] = $hkPro[$tgl][$karid][$kdorg][$blokkecil];
				}

				@$totalHK[$tgl][$karid] += $hkPro[$tgl][$karid][$kdorg][$blokkecil];
				$jumlahDataBlok[$tgl][$karid] += 1;
			}
		}
	}
}

## Prporsi premi kesulitan
foreach ($listjjg_blokkecil as $tgl => $val1) {
	foreach ($val1 as $karid => $val2) {

		// JUMLAH KDORG (DATA YANG DIBAGI)
		$jumlahData = count($val2);
		if ($jumlahData == 0) continue;

		// TOTAL PREMI SATU KALI PER KARID
		$totalPremi = (int) ($dataPremikesulitan[$tgl][$karid] ?? 0);
		if ($totalPremi <= 0) continue;

		$premiPerData = intdiv($totalPremi, $jumlahData);
		$sisa = $totalPremi % $jumlahData;

		$i = 0;
		foreach ($val2 as $kdorg => $val3) {

			// foreach ($val3 as $blokkecil => $jjg) {
			// 	$hasilPremiKesulitan[$tgl][$karid][$kdorg][$blokkecil] =
			// 		($i === 0)
			// 			? $premiPerData + $sisa
			// 			: $premiPerData;
			// }

			$jatahBlokBesar = ($i === 0)
				? $premiPerData + $sisa
				: $premiPerData;

			$totalKgBlokBesar = 0;
			foreach ($val3 as $blokkecil => $jjg) {
				$totalKgBlokBesar += ($jjg * $bjrBlokKecil[$tgl][$kdorg]);
			}
			if ($totalKgBlokBesar <= 0) {
				foreach ($val3 as $blokkecil => $jjg) {
					$hasilPremiKesulitan[$tgl][$karid][$kdorg][$blokkecil] = 0;
				}

				$i++;
				continue;
			}

			$alokasi = array();
			$totalAwal = 0;

			foreach ($val3 as $blokkecil => $jjg) {
				$kgBlokKecil = ($jjg * $bjrBlokKecil[$tgl][$kdorg]);
				$ideal = ($jatahBlokBesar * $kgBlokKecil) / $totalKgBlokBesar;
				$nilaiAwal = floor($ideal);

				$alokasi[$blokkecil] = array(
					'nilai' => $nilaiAwal,
					'sisa' => $ideal - $nilaiAwal
				);

				$totalAwal += $nilaiAwal;
			}

			$sisaBulat = $jatahBlokBesar - $totalAwal;

			uasort($alokasi, function ($a, $b) {
				if ($a['sisa'] == $b['sisa']) return 0;
				return ($a['sisa'] < $b['sisa']) ? 1 : -1;
			});

			foreach ($alokasi as $blokkecil => $dataAlokasi) {
				$hasilPremiKesulitan[$tgl][$karid][$kdorg][$blokkecil] = $dataAlokasi['nilai'];

				if ($sisaBulat > 0) {
					$hasilPremiKesulitan[$tgl][$karid][$kdorg][$blokkecil] += 1;
					$sisaBulat--;
				}
			}

			$i++;
		}
	}
}


## Proporsi Premi Kehadiran
foreach ($listjjg_blokkecil as $tgl => $val1) {
	foreach ($val1 as $karid => $val2) {

		// JUMLAH KDORG (DATA YANG DIBAGI)
		$jumlahData = count($val2);
		if ($jumlahData == 0) continue;

		// TOTAL PREMI SATU KALI PER KARID
		$totalPremi = (int) ($dataPremiKehadiran[$tgl][$karid] ?? 0);
		if ($totalPremi <= 0) continue;

		$premiPerData = intdiv($totalPremi, $jumlahData);
		$sisa = $totalPremi % $jumlahData;

		$i = 0;
		foreach ($val2 as $kdorg => $val3) {

			$jatahBlokBesar = ($i === 0)
				? $premiPerData + $sisa
				: $premiPerData;

			$totalKgBlokBesar = 0;
			foreach ($val3 as $blokkecil => $jjg) {
				$totalKgBlokBesar += ($jjg * $bjrBlokKecil[$tgl][$kdorg]);
			}
			if ($totalKgBlokBesar <= 0) {
				foreach ($val3 as $blokkecil => $jjg) {
					$hasilPremiKehadiran[$tgl][$karid][$kdorg][$blokkecil] = 0;
				}

				$i++;
				continue;
			}

			$alokasi = array();
			$totalAwal = 0;

			foreach ($val3 as $blokkecil => $jjg) {
				$kgBlokKecil = ($jjg * $bjrBlokKecil[$tgl][$kdorg]);
				$ideal = ($jatahBlokBesar * $kgBlokKecil) / $totalKgBlokBesar;
				$nilaiAwal = floor($ideal);

				$alokasi[$blokkecil] = array(
					'nilai' => $nilaiAwal,
					'sisa' => $ideal - $nilaiAwal
				);

				$totalAwal += $nilaiAwal;
			}

			$sisaBulat = $jatahBlokBesar - $totalAwal;

			uasort($alokasi, function ($a, $b) {
				if ($a['sisa'] == $b['sisa']) return 0;
				return ($a['sisa'] < $b['sisa']) ? 1 : -1;
			});

			foreach ($alokasi as $blokkecil => $dataAlokasi) {
				$hasilPremiKehadiran[$tgl][$karid][$kdorg][$blokkecil] = $dataAlokasi['nilai'];

				if ($sisaBulat > 0) {
					$hasilPremiKehadiran[$tgl][$karid][$kdorg][$blokkecil] += 1;
					$sisaBulat--;
				}
			}

			$i++;
		}
	}
}

$no = 0;
foreach ($listjjg_blokkecil as $tgl => $val1) {

	$jenispremi = getjenisharikerjaV2($unit, $tgl);

	if ($jenispremi == 'JUMAT') {
		$jenispremi = 'KERJA';
	}
	if ($jenispremi == 'LIBUR NASIONAL') {
		$jenispremi = 'LN';
	}
	if ($jenispremi == 'HARI MINGGU') {
		$jenispremi = 'HL';
	}

	foreach ($val1 as $karid => $val2) {
		foreach ($val2 as $kdorg => $val3) {
			foreach ($val3 as $blokkecil => $jjg) {
				$no++;
				$tab .= "<tr class=rowcontent  id=row" . $no . ">";
				$tab .= "<td align=center>" . $no . "</td>";

				## Ambil kodeorg dan divisi dari bloknya
				$kodeOrgx = substr($blokkecil, 0, 4);
				$kodeDivx = substr($blokkecil, 0, 6);
				$tahunTanamBlok = $tahunTanam[$tgl][$karid][$kdorg][$blokkecil];
				$banjirBlok = isset($statusBanjir[$tgl][$karid][$kdorg][$blokkecil]) ? (int)$statusBanjir[$tgl][$karid][$kdorg][$blokkecil] : 0;

				if ($proses != 'excel') {
					$tab .= "<td hidden id=prd_" . $no . " align=center>" . $prd . "</td>";
					$tab .= "<td hidden id=unit_" . $no . " align=center>" . $kodeOrgx . "</td>";
					$tab .= "<td hidden id=afd_" . $no . " align=center>" . $kodeDivx . "</td>";
					$notr2 = str_replace("-", "", $tgl) . "/" . $afd . "/PNN02/001";
					$tab .= "<td hidden id=notransaksi_" . $no . " align=center>" . $notr2 . "</td>";
					$tab .= "<td hidden id=karid_" . $no . " align=center>" . $karid . "</td>";
					$tab .= "<td hidden id=banjir_" . $no . " align=center>" . $banjirBlok . "</td>";
				}


				$tab .= "<td id=tglpnn_" . $no . " align=center>" . $tgl . "</td>";
				$tab .= "<td id=jenispremi_" . $no . " align=center>" . $jenispremi . "</td>";


				if ($proses != 'excel') {
					$tab .= "<td hidden id=nospb_" . $no . " " . $validasix . " align=center>" . $nospb_panen[$tgl][$kdorg][$blokkecil] . "</td>";
				}

				$tab .= "<td align=center>" . $nobkm[$tgl][$kdorg][$karid] . "</td>";

				if ($proses != 'excel') {
					$tab .= "<td hidden id=mandor1_" . $no . " align=center>" . $mandor1[$tgl][$kdorg][$karid] . "</td>";
					$tab .= "<td hidden id=mandor_" . $no . " align=center>" . $mandor[$tgl][$kdorg][$karid] . "</td>";
					$tab .= "<td hidden id=kerani_" . $no . " align=center>" . $kerani[$tgl][$kdorg][$karid] . "</td>";
				}

				$tab .= "<td  align=center>" . getNamaKaryawan($mandor1[$tgl][$kdorg][$karid]) . "</td>";
				$tab .= "<td  align=center>" . getNamaKaryawan($mandor[$tgl][$kdorg][$karid]) . "</td>";
				$tab .= "<td  align=center>" . getNamaKaryawan($kerani[$tgl][$kdorg][$karid]) . "</td>";

				$tab .= "<td align=center>" . getNik($karid) . "</td>";

				$tab .= "<td align=center>" . getNamaKaryawan($karid) . "</td>";
				$tab .= "<td id=blokbesar_" . $no . " align=center>" . $kdorg . "</td>";
				$tab .= "<td id=blokkecil_" . $no . " align=center>" . $blokkecil . "</td>";
				$tab .= "<td id=tahuntanam_" . $no . " align=center>" . $tahunTanamBlok . "</td>";

				$hektarproprosi = 0;
				$hektarproprosi = $Hektarpanenx[$tgl][$kdorg][$karid];
				unset($Hektarpanenx[$tgl][$kdorg][$karid]);

				$tab .= "<td id=hektarpanen_" . $no . " align=center>" . $hektarproprosi . "</td>";

				if ($jjg == '' || $jjg == 0) {
					$validasi = "style='background-color:red;cursor:pointer;' title='JJG Kosong !''";
				} else {
					$validasi = '';
				}

				if ($bjrBlokKecil[$tgl][$kdorg] == '' || $bjrBlokKecil[$tgl][$kdorg] == 0) {
					$validasiy = "style='background-color:red;cursor:pointer;' title='JJG Kosong !''";
				} else {
					$validasiy = '';
				}

				$tab .= "<td id=jjg_" . $no . " " . $validasi . " align=center>" . $jjg . "</td>";
				$tab .= "<td id=bjr_" . $no . " " . $validasiy . " align=center>" . number_format($bjrBlokKecil[$tgl][$kdorg], 2) . "</td>";

				@$totalKg[$tgl][$karid][$kdorg][$blokkecil] = ($jjg * $bjrBlokKecil[$tgl][$kdorg]);

				if ($proses != 'excel') {
					$tab .= "<td hidden id=totalkg_" . $no . " align=center>" . $totalKg[$tgl][$karid][$kdorg][$blokkecil] . "</td>";
				}

				$tab .= "<td align=center>" . number_format($totalKg[$tgl][$karid][$kdorg][$blokkecil], 2) . "</td>";

				if ($jenispanen[$tgl][$karid][$kdorg] == '1') {
					$basisTahunTanam = isset($basisha[$kodeOrgx][$jenispremi][$tahunTanamBlok][$banjirBlok])
						? $basisha[$kodeOrgx][$jenispremi][$tahunTanamBlok][$banjirBlok]
						: 0;
				} else {
					$basisTahunTanam = isset($basiskg[$kodeOrgx][$jenispremi][$tahunTanamBlok][$banjirBlok])
						? $basiskg[$kodeOrgx][$jenispremi][$tahunTanamBlok][$banjirBlok]
						: 0;
				}
				$tab .= "<td id=basistahuntanam_" . $no . " align=center>" . $basisTahunTanam . "</td>";

				//if($ttlpersenkg[$tgl][$karid] >= 1){
				$tab .= "<td id=persenkg_" . $no . " align=center>" . number_format($persenkg[$tgl][$karid][$kdorg][$blokkecil], 2) . "</td>";
				//}else{
				//	$tab.="<td id=persenkg_".$no." align=center>".(intval($persenkg[$tgl][$karid][$kdorg][$blokkecil]*100)/100)."</td>";
				//}

				// if($totalKg[$tgl][$karid][$kdorg][$blokkecil] == 0 and $hkPro[$tgl][$karid][$kdorg][$blokkecil] < 0 ){
				// 	$hkPro[$tgl][$karid][$kdorg][$blokkecil] =0;
				// }

				$tab .= "<td id=hk_" . $no . " align=center>" . number_format(fixnan($hkPro[$tgl][$karid][$kdorg][$blokkecil]), 2) . "</td>";

				## Potongan HK
				if ($jenispremi == 'LN' || $jenispremi == 'HL') {
					$potHK[$tgl][$karid][$kdorg][$blokkecil] = 0;
				} elseif ($ttlpersenkg[$tgl][$karid] > 1) {
					$potHK[$tgl][$karid][$kdorg][$blokkecil] = 0;
				} else {
					$potHK[$tgl][$karid][$kdorg][$blokkecil] = ($persenkg[$tgl][$karid][$kdorg][$blokkecil] / $ttlpersenkg[$tgl][$karid]) * (1 - $totalHK[$tgl][$karid]);
				}

				$tab .= "<td  id=pothk_" . $no . " align=center>" . number_format(fixnan($potHK[$tgl][$karid][$kdorg][$blokkecil]), 2) . "</td>";

				## Basis Pakai
				$tab .= "<td id=basispakai_" . $no . " align=center>" . number_format($dataBasis[$tgl][$karid], 2) . "</td>";

				## Basis Baru
				$basisBaru[$tgl][$karid][$kdorg][$blokkecil] =  $totalKg[$tgl][$karid][$kdorg][$blokkecil] / $totalKgblok[$tgl][$karid] * $dataBasis[$tgl][$karid];

				$tab .= "<td id=basisbaru_" . $no . "  align=center>" . number_format(fixnan($basisBaru[$tgl][$karid][$kdorg][$blokkecil]), 2) . "</td>";

				## Lebih basis
				$isKontanan = isset($kontanan[$tgl][$karid][$kdorg])
					&& strtoupper(trim($kontanan[$tgl][$karid][$kdorg])) == 'KONTAN';
				$isHariLibur = ($jenispremi == 'HL' || $jenispremi == 'LN');
				if ($isKontanan || $isHariLibur) {
					$lebihBasis[$tgl][$karid][$kdorg][$blokkecil] = $totalKg[$tgl][$karid][$kdorg][$blokkecil];
				} elseif ($jenispanen[$tgl][$karid][$kdorg] == '1') {
					$lebihBasis[$tgl][$karid][$kdorg][$blokkecil] = 0;
				} else {
					if ($ttlpersenkg[$tgl][$karid] > 1) {
						$lebihBasis[$tgl][$karid][$kdorg][$blokkecil] = ($totalKg[$tgl][$karid][$kdorg][$blokkecil] - $basisBaru[$tgl][$karid][$kdorg][$blokkecil]);
						if ($jenispremi == 'KERJA') {
							$lebihBasis[$tgl][$karid][$kdorg][$blokkecil] = ($totalKg[$tgl][$karid][$kdorg][$blokkecil] - $basisBaru[$tgl][$karid][$kdorg][$blokkecil]);
						}
					} else {
						$lebihBasis[$tgl][$karid][$kdorg][$blokkecil] = 0;
					}
				}

				if ($dataBasis[$tgl][$karid] == 0) {
					$lebihBasis[$tgl][$karid][$kdorg][$blokkecil] = $totalKg[$tgl][$karid][$kdorg][$blokkecil];
				}

				if ($lebihBasis[$tgl][$karid][$kdorg][$blokkecil] < 0) {
					$lebihBasis[$tgl][$karid][$kdorg][$blokkecil] = 0;
				}

				$tab .= "<td id=lebihbasis_" . $no . "  align=center>" . number_format($lebihBasis[$tgl][$karid][$kdorg][$blokkecil], 2) . "</td>";

				## 1 HK
				$upah1hk[$tgl][$karid][$kdorg][$blokkecil] = getUpahKary(substr($tgl, 0, 7), $karid);
				$tab .= "<td align=right>" . number_format($upah1hk[$tgl][$karid][$kdorg][$blokkecil], 2) . "</td>";

				## Potongan Rupiah
				if ($ttlpersenkg[$tgl][$karid] > 1) {
					$rupiahPotHk[$tgl][$karid][$kdorg][$blokkecil] = 0;
				} else {
					$rupiahPotHk[$tgl][$karid][$kdorg][$blokkecil] = $upah1hk[$tgl][$karid][$kdorg][$blokkecil] * $potHK[$tgl][$karid][$kdorg][$blokkecil];
				}

				$tab .= "<td  id=upahpot_" . $no . " align=right>" . number_format(fixnan($rupiahPotHk[$tgl][$karid][$kdorg][$blokkecil]), 2) . "</td>";

				## Rupiah Upah
				if ($jenispremi == 'HL') {
					$rupiahHK[$tgl][$karid][$kdorg][$blokkecil] = 0;
				} elseif ($jenispremi == 'LN') {
					$rupiahHK[$tgl][$karid][$kdorg][$blokkecil] = $upah1hk[$tgl][$karid][$kdorg][$blokkecil] * $hkPro[$tgl][$karid][$kdorg][$blokkecil];
				} elseif ($dataBasis[$tgl][$karid] == 0) {
					$rupiahHK[$tgl][$karid][$kdorg][$blokkecil] = 0;
				} else {
					$rupiahHK[$tgl][$karid][$kdorg][$blokkecil] = $upah1hk[$tgl][$karid][$kdorg][$blokkecil] * $hkPro[$tgl][$karid][$kdorg][$blokkecil];
				}
				$tab .= "<td  id=upah_" . $no . " align=right>" . number_format(fixnan($rupiahHK[$tgl][$karid][$kdorg][$blokkecil]), 2) . "</td>";

				## Rp Lebih Basis
				$premiLebihBasisSatuan = isset($rplb1[$kodeOrgx][$jenispremi][$tahunTanamBlok][$banjirBlok])
					? $rplb1[$kodeOrgx][$jenispremi][$tahunTanamBlok][$banjirBlok]
					: 0;
				$rupiahLebihBasis[$tgl][$karid][$kdorg][$blokkecil] = $premiLebihBasisSatuan * $lebihBasis[$tgl][$karid][$kdorg][$blokkecil];

				$tab .= "<td  id=upahlb_" . $no . "  align=right>" . number_format($rupiahLebihBasis[$tgl][$karid][$kdorg][$blokkecil], 2) . "</td>";

				## Premi kesulitan tanpa memandang basis untuk semua unit
				$premikesulitan[$tgl][$karid][$kdorg][$blokkecil] = $hasilPremiKesulitan[$tgl][$karid][$kdorg][$blokkecil];

				## Premi kesulitan	
				$tab .= "<td  id=premiks_" . $no . " align=right>" . number_format($premikesulitan[$tgl][$karid][$kdorg][$blokkecil], 2) . "</td>";

				## Premi kehadiran
				$premikehadiran[$tgl][$karid][$kdorg][$blokkecil] = $hasilPremiKehadiran[$tgl][$karid][$kdorg][$blokkecil];
				$tab .= "<td  id=premikh_" . $no . " align=right>" . number_format(fixnan($premikehadiran[$tgl][$karid][$kdorg][$blokkecil]), 2) . "</td>";

				$totalPremi[$tgl][$karid][$kdorg][$blokkecil] = $premikesulitan[$tgl][$karid][$kdorg][$blokkecil] + $premikehadiran[$tgl][$karid][$kdorg][$blokkecil] + $rupiahLebihBasis[$tgl][$karid][$kdorg][$blokkecil];

				$tab .= "<td align=center>" . number_format($totalPremi[$tgl][$karid][$kdorg][$blokkecil], 2) . "</td>";
				$tab .= "<td id=brondol_" . $no . " align=center>" . number_format($listbrondol_blokkecil[$tgl][$karid][$kdorg][$blokkecil], 2) . "</td>";


				$premiBrondolanSatuan = isset($rpbrd[$kodeOrgx][$jenispremi][$tahunTanamBlok][$banjirBlok])
					? $rpbrd[$kodeOrgx][$jenispremi][$tahunTanamBlok][$banjirBlok]
					: 0;
				$rupiahBrondol[$tgl][$karid][$kdorg][$blokkecil] = $listbrondol_blokkecil[$tgl][$karid][$kdorg][$blokkecil] * $premiBrondolanSatuan;
				// Jika DMA atau MHA dan jenis panen 1, maka brondolan dihapuskan
				if ((getNamaOrg($unit, 'induk') == 'DMA' or getNamaOrg($unit, 'induk') == 'MHA') && $jenispanen[$tgl][$karid][$kdorg] == '1') {
					$rupiahBrondol[$tgl][$karid][$kdorg][$blokkecil] = 0;
				}
				$tab .= "<td  id=upahbro_" . $no . " align=right>" . number_format($rupiahBrondol[$tgl][$karid][$kdorg][$blokkecil], 2) . "</td>";

				## Denda Panen
				if ($dendapanen[$tgl][$karid] > 0) {
					$proporsiDendaPanen[$tgl][$karid][$kdorg][$blokkecil] = $hkPro[$tgl][$karid][$kdorg][$blokkecil] * $dendapanen[$tgl][$karid];
				} else {
					$proporsiDendaPanen[$tgl][$karid][$kdorg][$blokkecil] =  0;
				}

				$tab .= "<td  id=dendapn_" . $no . " align=right>" . number_format(($proporsiDendaPanen[$tgl][$karid][$kdorg][$blokkecil] * -1), 2) . "</td>";


				## TOTALSEMUA NYA
				$total_gaes[$tgl][$karid][$kdorg][$blokkecil] = $rupiahHK[$tgl][$karid][$kdorg][$blokkecil] + $rupiahLebihBasis[$tgl][$karid][$kdorg][$blokkecil]  + $premikesulitan[$tgl][$karid][$kdorg][$blokkecil] + $premikehadiran[$tgl][$karid][$kdorg][$blokkecil] + $rupiahBrondol[$tgl][$karid][$kdorg][$blokkecil] - $proporsiDendaPanen[$tgl][$karid][$kdorg][$blokkecil];
				$tab .= "<td  id=totalupah_" . $no . " align=right>" . number_format(fixnan($total_gaes[$tgl][$karid][$kdorg][$blokkecil]), 2) . "</td>";


				$tab .= "</tr>";

				$totalHA[$tgl][$karid] 		+= $hektarproprosi;
				$totalJJG[$tgl][$karid] 	+= $jjg;
				$totalKG[$tgl][$karid] 		+= $totalKg[$tgl][$karid][$kdorg][$blokkecil];
				$ttlpersenkgx[$tgl][$karid]  += $persenkg[$tgl][$karid][$kdorg][$blokkecil];
				$totalHKX[$tgl][$karid] 	+= $hkPro[$tgl][$karid][$kdorg][$blokkecil];
				$totalPOTHKX[$tgl][$karid] 	+= $potHK[$tgl][$karid][$kdorg][$blokkecil];
				$totalLebihBasis[$tgl][$karid] 	+= $lebihBasis[$tgl][$karid][$kdorg][$blokkecil];
				$totalRupiahHk[$tgl][$karid] 	+= $rupiahHK[$tgl][$karid][$kdorg][$blokkecil];
				$totalRupiahPotHk[$tgl][$karid] += $rupiahPotHk[$tgl][$karid][$kdorg][$blokkecil];
				$totalRupiahLebihBasis[$tgl][$karid] 	+= $rupiahLebihBasis[$tgl][$karid][$kdorg][$blokkecil];
				$totalRupiahpk[$tgl][$karid] 	+= $premikesulitan[$tgl][$karid][$kdorg][$blokkecil];
				$totalRupiahkh[$tgl][$karid] 	+= $premikehadiran[$tgl][$karid][$kdorg][$blokkecil];
				$totalDendaPanen[$tgl][$karid] 	+= $proporsiDendaPanen[$tgl][$karid][$kdorg][$blokkecil] * -1;
				$totalBrondolanPanen[$tgl][$karid] 	+= $listbrondol_blokkecil[$tgl][$karid][$kdorg][$blokkecil];
				$totalBrondolanPengali[$tgl][$karid] 	+= $totalBrondolan[$tgl][$karid][$kdorg][$blokkecil];
				$totalkgbrondolan[$tgl][$karid] 	+= $kgwbnetto_brondolan[$nospb_panen_brondolan[$tgl][$kdorg][$blokkecil]][$tgl][$kdorg][$blokkecil];
				$totalrpbrondolan[$tgl][$karid] 	+= $rupiahBrondol[$tgl][$karid][$kdorg][$blokkecil];
				$totalKabehe[$tgl][$karid] 		+= $total_gaes[$tgl][$karid][$kdorg][$blokkecil];

				$gTotalPremi[$tgl][$karid] 	+= $totalPremi[$tgl][$karid][$kdorg][$blokkecil];
			}
		}

		$tab .= "<tr class=rowcontent  style='font-weight:bold;background-color:cyan;'>";
		$tab .= "<td align=center colspan=12>TOTAL : " . strtoupper(getNamaKaryawan($karid)) . "</td>";
		$tab .= "<td align=center >" . number_format($totalHA[$tgl][$karid], 2) . "</td>";
		$tab .= "<td align=center >" . $totalJJG[$tgl][$karid] . "</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td align=center >" . number_format($totalKG[$tgl][$karid], 2) . "</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td align=center >" . number_format($ttlpersenkgx[$tgl][$karid], 2) . "</td>";
		$tab .= "<td align=center >" . number_format(fixnan($totalHKX[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center >" . number_format(fixnan($totalPOTHKX[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td align=center>" . number_format(fixnan($totalLebihBasis[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center></td>";
		$tab .= "<td align=center >" . number_format(fixnan($totalRupiahPotHk[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center >" . number_format(fixnan($totalRupiahHk[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center >" . number_format(fixnan($totalRupiahLebihBasis[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center >" . number_format(fixnan($totalRupiahpk[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center >" . number_format(fixnan($totalRupiahkh[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center >" . number_format(fixnan($gTotalPremi[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center >" . number_format(fixnan($totalBrondolanPanen[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center >" . number_format(fixnan($totalrpbrondolan[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center >" . number_format(fixnan($totalDendaPanen[$tgl][$karid]), 2) . "</td>";
		$tab .= "<td align=center >" . number_format(fixnan($totalKabehe[$tgl][$karid]), 2) . "</td>";
		$tab .= "</tr>";

		$GT_HAA += $totalHA[$tgl][$karid];
		$GT_JJG += $totalJJG[$tgl][$karid];
		$GT_KGG += $totalKG[$tgl][$karid];
		$GT_HKK += $totalHKX[$tgl][$karid];
		$GT_POT_HKK += $totalPOTHKX[$tgl][$karid];
		$GT_LB_BASIS += $totalLebihBasis[$tgl][$karid];
		$GT_POT_UPAH += $totalRupiahPotHk[$tgl][$karid];
		$GT_UPAH += $totalRupiahHk[$tgl][$karid];
		$GT_UPAH_LB += $totalRupiahLebihBasis[$tgl][$karid];
		$GT_UPAH_PK += $totalRupiahpk[$tgl][$karid];
		$GT_UPAH_KH += $totalRupiahkh[$tgl][$karid];
		$GT_PREMI += $gTotalPremi[$tgl][$karid];
		$GT_BRONDOL += $totalBrondolanPanen[$tgl][$karid];
		$GT_BRONDOL_RP += $totalrpbrondolan[$tgl][$karid];
		$GT_DENDA += $totalDendaPanen[$tgl][$karid];
		$GT_TOTAL += $totalKabehe[$tgl][$karid];
	}

	$tab .= "<tr class=rowcontent  style='font-weight:bold;background-color:yellow;'>";
	$tab .= "<td align=center colspan=12>GRAND TOTAL</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_HAA), 2) . "</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_JJG), 2) . "</td>";
	$tab .= "<td align=center ></td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_KGG), 2) . "</td>";
	$tab .= "<td align=center colspan=2></td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_HKK), 2) . "</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_POT_HKK), 2) . "</td>";
	$tab .= "<td align=center colspan=2></td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_LB_BASIS), 2) . "</td>";
	$tab .= "<td align=center></td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_POT_UPAH), 2) . "</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_UPAH), 2) . "</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_UPAH_LB), 2) . "</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_UPAH_PK), 2) . "</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_UPAH_KH), 2) . "</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_PREMI), 2) . "</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_BRONDOL), 2) . "</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_BRONDOL_RP), 2) . "</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_DENDA), 2) . "</td>";
	$tab .= "<td align=center >" . number_format(fixnan($GT_TOTAL), 2) . "</td>";
	$tab .= "</tr>";
}

$tab .= "</tbody></table></div>";

if ($proses != 'excel') {
	$tab .= "<button class=mybutton id=proses onclick=saveAll(" . $no . ");>" . $_SESSION['lang']['proses'] . "</button>";
	$tab .= "<button class=mybutton onclick=previewexcel();>" . $_SESSION['lang']['excel'] . "</button>";
}

function nb_format($e, $i = 0, $proses = 'preview')
{
	if ($proses == 'preview' or $proses == 'excel') {
		$n = round($e, $i);
	} else {
		$n = round($e, $i);
	}
	return $n;
}

switch ($proses) {
	case 'preview':
		echo $tab;
		break;
	######EXCEL
	case 'excel':
		$stream = $tab;;
		$stream .= "Print Time : " . date('H:i:s, d/m/Y') . "<br>By : " . $_SESSION['empl']['name'];
		$tglSkrg = date("Ymd");
		$nop_ = "Daftar_Premi_Pemanen";
		if (strlen($stream) > 0) {
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
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
	default:
}
