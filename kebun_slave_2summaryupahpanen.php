<?
error_reporting(0);
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/utilities.php');
include_once('lib/HtmlExcel.php');

$proses = checkPostGet('proses', '');
$view = "";

#= Get Param
$param = $_POST;
if (count($param) == 0) $param = $_GET;

#= Handle Parameters for Tabs
$tabIndex = -1;
for ($i = 0; $i < 6; $i++) {
	if (isset($param['unit' . $i])) {
		$tabIndex = $i;
		$param['unit'] = $param['unit' . $i];
		$param['div'] = $param['div' . $i];
		$param['tgl'] = $param['tgl' . $i];
		$param['tglx'] = $param['tglx' . $i];
		break;
	}
}

#= Convert Data
$nik = makeOption($dbname, "datakaryawan", "karyawanid,nik", "", "nik asc");
$namakaryawan = makeOption($dbname, "datakaryawan", "karyawanid,namakaryawan");
$subbagian = makeOption($dbname, "datakaryawan", "karyawanid,subbagian");
$divisi = makeOption($dbname, "organisasi", "kodeorganisasi,namaorganisasi");
$kegiatan = makeOption($dbname, "setup_kegiatan", "kodekegiatan,namakegiatan");
$satuan = makeOption($dbname, "setup_kegiatan", "kodekegiatan,satuan");

#= Filter tanggal by Notransaksi
#= Karena tidak ada kolom tanggal
$tglAwal = explode("-", $param['tgl']);
$tglAkhir = explode("-", $param['tglx']);

$tglAwal = $tglAwal[2] . $tglAwal[1] . $tglAwal[0];
$tglAkhir = $tglAkhir[2] . $tglAkhir[1] . $tglAkhir[0];

// exit('warning'.$tglAwal);
#= Get Data Prestasi
$whereKp = "";
$wherePnn = "";
$whereBKM = "";
$whereBMTBS = "";
$whereTRAKSI = "";
$whereSDM = "";


if ($param['unit'] == "") {
	exit("Warning : Unit wajib diisi ! ");
}


if ($param['div'] != "") {
	$whereKp .= " and (a.divisi LIKE '" . $param['div'] . "%' or b.subbagian = '" . $param['div'] . "')";
	$wherePnn .= " and (a.kodeorg like '" . $param['div'] . "%' or b.subbagian = '" . $param['div'] . "') ";
	$whereBKM .= " and (a.kodeorg like '" . $param['div'] . "%' or b.subbagian = '" . $param['div'] . "') ";
	$whereBMTBS .= " and (a.divisi like '" . $param['div'] . "%' or b.subbagian = '" . $param['div'] . "') ";
	$whereTRAKSI .= " and (a.kodeorg like '" . $param['div'] . "%' or b.subbagian = '" . $param['div'] . "%')";
	$whereSDM .= " and (a.kodeorg like '" . $param['div'] . "%' or b.subbagian = '" . $param['div'] . "%')";
}

if ($param['unit'] != "") {
	$wherePnn .= " and (a.kodeorg like '" . $param['unit'] . "%' or b.lokasitugas = '" . $param['unit'] . "') ";
	$whereKp .= " and (a.kodeorg LIKE '" . $param['unit'] . "%' or b.lokasitugas = '" . $param['unit'] . "')";
	$whereBKM .= " and (a.unit LIKE '" . $param['unit'] . "%' or b.lokasitugas = '" . $param['unit'] . "')";
	$whereBMTBS .= " and (a.kodeorg LIKE '" . $param['unit'] . "%' or b.lokasitugas = '" . $param['unit'] . "')";
	$whereTRAKSI .= " and (a.kodeorg LIKE '" . $param['unit'] . "%' or b.lokasitugas = '" . $param['unit'] . "')";
	$whereSDM .= " and (a.kodeorg LIKE '" . $param['unit'] . "%' or b.lokasitugas = '" . $param['unit'] . "')";
}

if ($param['tgl'] != "" && $param['tglx'] != "") {
	$whereKp .= " and a.tanggalpanen between '" . $tglAwal . "' and '" . $tglAkhir . "'";
	$whereBKM .= " and a.tanggal between '" . tanggalsystemn($param['tgl']) . "' and '" . tanggalsystemn($param['tglx']) . "'";
	$whereBMTBS .= " and a.tanggal between '" . tanggalsystemn($param['tgl']) . "' and '" . tanggalsystemn($param['tglx']) . "'";
	$whereTRAKSI .= " and a.tanggal between '" . tanggalsystemn($param['tgl']) . "' and '" . tanggalsystemn($param['tglx']) . "'";
	$whereSDM .= " and a.tanggal between '" . tanggalsystemn($param['tgl']) . "' and '" . tanggalsystemn($param['tglx']) . "'";
}


$datapanentgl = array();
$datapanentglxz = array();

$str = "select a.* from " . $dbname . ".kebun_rekaphancakpanen a 
	left join datakaryawan b on a.nik=b.karyawanid 
	where a.tanggal between '" . $tglAwal . "' and '" . $tglAkhir . "' " . $wherePnn . "  order by a.tanggal asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$div = substr($bar['kodeorg'], 0, 6);
	$Hektarpanenx[$bar['tanggal']][$bar['nik']][$div] += $bar['hapanen'];
}

## Kegiatan Kebun
$query = "select a.*, b.namakaryawan from " . $dbname . ".kebun_3premipemanen a
left join datakaryawan b on a.karyawanid=b.karyawanid
where 1=1 " . $whereKp . " order by a.tanggalpanen asc, b.namakaryawan asc, a.blok asc";
// exit("warning:".$query);
$result = fetchData($query);

if (count($result) == 0) {
	$query = "select
		a.notransaksi, a.kodeorg, a.divisi, a.periode, a.tanggalpanen, a.nospb,
		a.mandor, a.kerani, a.karyawanid, a.tahuntanam, a.hapanen,
		a.indukblok as blok,
		a.jjg      as jjgbuahbesar,
		a.bjr,
		a.kg       as kgbuahbesar,
		a.basis    as basisbuahbesar,
		a.hk       as hkbuahbesar,
		a.upah     as rphkbuahbesar,
		a.pothk    as hkbuahbesarpot,
		a.potupah  as rphkbuahbesarpot,
		a.lbbasis  as lbbuahbesar,
		a.premilb  as rplbbuahbesar,
		a.premikehadiran,
		a.premikesulitan,
		a.brondol  as brondolan,
		a.upahbro  as rpbrondolan,
		a.denda    as dendapanen,
		a.totalupah as total,
		b.namakaryawan
	from " . $dbname . ".kebun_3premipemanen_v2 a
	left join datakaryawan b on a.karyawanid=b.karyawanid
	where 1=1 " . $whereKp . " order by a.tanggalpanen asc, b.namakaryawan asc, a.indukblok asc";
	$result = fetchData($query);
	// exit("warning:".$query);
}

foreach ($result as $val) {
	$karyid = $val['karyawanid'];
	$tgl = $val['tanggalpanen'];
	$div = $val['divisi'];

	$karyawan[$karyid][$tgl][$div] = $karyid;
	$notransaksix[$karyid][$tgl][$div] += $val['notransaksi'];
	$nikmandor[$karyid][$tgl][$div] += $val['mandor'];
	$nikkerani[$karyid][$tgl][$div] += $val['kerani'];
	$blokx[$karyid][$tgl][$div] += $val['blok'];
	$nospb[$karyid][$tgl][$div] += $val['nospb'];

	$jjgbuahbesar[$karyid][$tgl][$div] += $val['jjgbuahbesar'];
	$jjgbuahkecil[$karyid][$tgl][$div] += $val['jjgbuahkecil'];
	$bjrpanen[$karyid][$tgl][$div] += $val['bjr'];

	$kgbuahbesar[$karyid][$tgl][$div] += $val['kgbuahbesar'];
	$kgbuahkecil[$karyid][$tgl][$div] += $val['kgbuahkecil'];

	$basisbuahbesar[$karyid][$tgl][$div] += $val['basisbuahbesar'];
	$basisbuahkecil[$karyid][$tgl][$div] += $val['basisbuahkecil'];

	$hkbuahbesar[$karyid][$tgl][$div] += $val['hkbuahbesar'];
	$hkbuahkecil[$karyid][$tgl][$div] += $val['hkbuahkecil'];

	$rphkbuahbesar[$karyid][$tgl][$div] += $val['rphkbuahbesar'];
	$rphkbuahkecil[$karyid][$tgl][$div] += $val['rphkbuahkecil'];

	$hkbuahbesarpot[$karyid][$tgl][$div] += $val['hkbuahbesarpot'];
	$hkbuahkecilpot[$karyid][$tgl][$div] += $val['hkbuahkecilpot'];

	$rphkbuahbesarpot[$karyid][$tgl][$div] += $val['rphkbuahbesarpot'];
	$rphkbuahkecilpot[$karyid][$tgl][$div] += $val['rphkbuahkecilpot'];

	$lbbuahbesar[$karyid][$tgl][$div] += $val['lbbuahbesar'];
	$lbbuahkecil[$karyid][$tgl][$div] += $val['lbbuahkecil'];

	$rplbbuahbesar[$karyid][$tgl][$div] += $val['rplbbuahbesar'];
	$rplbbuahkecil[$karyid][$tgl][$div] += $val['rplbbuahkecil'];
	$premikehadiranpanen[$karyid][$tgl][$div] += isset($val['premikehadiran']) ? $val['premikehadiran'] : 0;
	$premikesulitanpanen[$karyid][$tgl][$div] += isset($val['premikesulitan']) ? $val['premikesulitan'] : 0;
	$totalpremipanen[$karyid][$tgl][$div] +=
		(isset($val['rplbbuahbesar']) ? $val['rplbbuahbesar'] : 0) +
		(isset($val['rplbbuahkecil']) ? $val['rplbbuahkecil'] : 0) +
		(isset($val['premikehadiran']) ? $val['premikehadiran'] : 0) +
		(isset($val['premikesulitan']) ? $val['premikesulitan'] : 0);

	$brondolan[$karyid][$tgl][$div] += $val['brondolan'];
	$rpbrondolan[$karyid][$tgl][$div] += $val['rpbrondolan'];

	$dendapanen[$karyid][$tgl][$div] += $val['dendapanen'];
	$totalrppanen[$karyid][$tgl][$div] += $val['total'];
}

foreach ($Hektarpanenx as $tglpnn => $key) {
	foreach ($key as $kary => $divs) {
		foreach ($divs as $div => $val) {
			$luaspanen[$kary][$tglpnn][$div] += $val;
		}
	}
}

## Ambil Kegiatan Rawat
$query = "select a.*, b.namakaryawan,b.subbagian from " . $dbname . ".kebun_kehadiran_vw a
	left join datakaryawan b on a.karyawanid=b.karyawanid
	where 1=1 and subbagian != '' and subbagian not like '%TK' and subbagian not like '%WS' " . $whereBKM . " order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
// echo"kegiatan rawat ".$query;
// exit("Warning: " . $query);
$result = fetchData($query);
foreach ($result as $val) {
	$karyid = $val['karyawanid'];
	$tgl = $val['tanggal'];
	$div = substr($val['kodeorg'], 0, 6);

	$karyawan[$karyid][$tgl][$div] = $karyid;
	$hk_rawat[$karyid][$tgl][$div] += $val['jhk'];
	$umr_rawat[$karyid][$tgl][$div] += $val['umr'];
	$premi_rawat[$karyid][$tgl][$div] += $val['insentif'];
	$total_pertgl_rawat[$karyid][$tgl][$div] += $val['umr'] + $val['insentif'];

	$jhkRawat[$karyid] += $val['jhk'];
	$upahRawat[$karyid] += $val['umr'];
	$premiRawat[$karyid] += $val['insentif'];
	$totalRAWAT[$karyid] += $val['umr'] + $val['insentif'];

	$gtottotalrjhkRawat += $val['jhk'];
	$gtottotalrumrRawat += $val['umr'];
	$gtottotalrpremiRawat += $val['insentif'];
	$gtRAWAT += $val['umr'] + $val['insentif'];
}


## Ambil Kegiatan bm tbs nya 
$query = "select a.*, b.namakaryawan,b.subbagian from " . $dbname . ".kebun_3premibmtbs a
	left join datakaryawan b on a.karyawanid=b.karyawanid
	where 1=1 and subbagian != '' and subbagian not like '%TK' and subbagian not like '%WS' " . $whereBMTBS . " order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
$result = fetchData($query);
foreach ($result as $val) {
	$karyid = $val['karyawanid'];
	$tgl = $val['tanggal'];
	$div = substr($val['kodeorg'], 0, 6);

	$karyawan[$karyid][$tgl][$div] = $karyid;
	$hk_bmtbs[$karyid][$tgl][$div] += $val['hk'];
	$umr_bmtbs[$karyid][$tgl][$div] += $val['rphk'];
	$premi_bmtbs[$karyid][$tgl][$div] += $val['rppremi'];
	$total_pertgl_bmtbs[$karyid][$tgl][$div] += $val['rphk'] + $val['rppremi'];

	$hkBMTBS[$karyid] += $val['hk'];
	$rphkBMTBS[$karyid] += $val['rphk'];
	$premiBMTBS[$karyid] += $val['rppremi'];
	$totalBMTBS[$karyid] +=  $val['rphk'] + $val['rppremi'];

	$gtottotalHKBMTBS	+= $val['hk'];
	$gtottotalRPHKBMTBS	+= $val['rphk'];
	$gtottotalPREMIBMTBS	+= $val['rppremi'];
	$gtBMTBS += $val['rphk'] + $val['rppremi'];
}

## Ambil kegiatan traksi 
$query = "select a.*, b.namakaryawan,b.subbagian from " . $dbname . ".vhc_runhk_vw a
	left join datakaryawan b on a.idkaryawan=b.karyawanid
	where 1=1 and subbagian != '' and subbagian not like '%TK' and subbagian not like '%WS'  " . $whereTRAKSI . " order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
$result = fetchData($query);
foreach ($result as $val) {
	$karyid = $val['idkaryawan'];
	$tgl = $val['tanggal'];
	$div = substr($val['kodeorg'], 0, 6);

	$karyawan[$karyid][$tgl][$div] = $val['karyawanid'];
	$hk_traksi[$karyid][$tgl][$div] += $val['hk'];
	$umr_traksi[$karyid][$tgl][$div] += $val['upah'];
	$premi_traksi[$karyid][$tgl][$div] += $val['premi'];
	$total_pertgl_traksi[$karyid][$tgl][$div] += $val['upah'] + $val['premi'];

	$HK_TRAKSI[$karyid] += $val['hk'];
	$RP_TRAKSI[$karyid] += $val['upah'];
	$PREMI_TRAKSI[$karyid] += $val['premi'];
	$totalTRAKSI[$karyid] += $val['upah'] + $val['premi'];

	$gtottotalHK_TRAKSI	+= $val['hk'];
	$gtottotalRP_TRAKSI	+= $val['upah'];
	$gtottotalPREMI_TRAKSI	+= $val['premi'];
	$gtTRAKSI += $val['upah'] + $val['premi'];
}

## Kegiatan Umum
$query = "select a.*, b.namakaryawan,b.subbagian from " . $dbname . ".sdm_absensidt a
	left join datakaryawan b on a.karyawanid=b.karyawanid
	where 1=1 and subbagian != '' and subbagian not like '%TK' and subbagian not like '%WS' " . $whereSDM . " order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
$result = fetchData($query);
foreach ($result as $val) {
	$karyid = $val['karyawanid'];
	$tgl = $val['tanggal'];
	$div = substr($val['kodeorg'], 0, 6);

	$karyawan[$karyid][$tgl][$div] = $karyid;
	$hk_sdm[$karyid][$tgl][$div] += $val['hk'];
	$umr_sdm[$karyid][$tgl][$div] += $val['umr'];
	$premi_sdm[$karyid][$tgl][$div] += $val['premi'];
	$total_pertgl_sdm[$karyid][$tgl][$div] += $val['umr'] + $val['premi'];

	$HK_SDM[$karyid] += $val['hk'];
	$RP_SDM[$karyid] += $val['umr'];
	$PREMI_SDM[$karyid] += $val['premi'];
	$totalSDM[$karyid] += $val['umr'] + $val['premi'];

	$gtottotalHK_SDM	+= $val['hk'];
	$gtottotalRP_SDM	+= $val['umr'];
	$gtottotalPREMI_SDM	+= $val['premi'];
	$gtSDM += $val['umr'] + $val['premi'];
}


if ($proses == 'previewPanen' || ($proses == 'excel' && $tabIndex == 0)) $tabMode = 'panen';
elseif ($proses == 'previewRawat' || ($proses == 'excel' && $tabIndex == 1)) $tabMode = 'rawat';
elseif ($proses == 'previewBMTBS' || ($proses == 'excel' && $tabIndex == 2)) $tabMode = 'bmtbs';
elseif ($proses == 'previewTraksi' || ($proses == 'excel' && $tabIndex == 3)) $tabMode = 'traksi';
elseif ($proses == 'previewUmum' || ($proses == 'excel' && $tabIndex == 4)) $tabMode = 'umum';
else $tabMode = 'all';

#= Awal Tabel
if ($proses == "excel") {
	$view .= "<table class=sortable cellspacing=1 border=1>";
} else {
	$view .= "<div> <table class='sortable' cellspacing=1 cellpadding = 5 border=0 width=100%>";
}

@$nmkar      = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
@$nikkar     = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$nmorg       = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok');

#= Tabel Header
$view .= "<thead>";

// Total columns count
$totalCol = 7;
if ($tabMode == 'all') $totalCol += 43;
elseif ($tabMode == 'panen') $totalCol += 27;
else $totalCol += 4;

if ($tabMode != 'all') {
	$title = "KEGIATAN " . strtoupper($tabMode);
	if ($tabMode == 'bmtbs') $title = "KEGIATAN BM TBS";
	$view .= "<tr class=rowheader><th align=center colspan=" . $totalCol . ">" . $title . "</th></tr>";
}

$view .= "<tr class=rowheader>";
$rowspanIdent = ($tabMode == 'all') ? 3 : 2;
$view .= "<th align=center rowspan=" . $rowspanIdent . ">No</th>";
$view .= "<th align=center rowspan=" . $rowspanIdent . ">" . $_SESSION['lang']['tanggal'] . "</th>";
$view .= "<th align=center rowspan=" . $rowspanIdent . ">NIK</th>";
$view .= "<th align=center rowspan=" . $rowspanIdent . ">" . $_SESSION['lang']['namakaryawan'] . "</th>";
$view .= "<th align=center rowspan=" . $rowspanIdent . ">Divisi Asal</th>";
$view .= "<th align=center rowspan=" . $rowspanIdent . ">Divisi Tugas</th>";

if ($tabMode == 'all') {
	$view .= "<th align=center colspan=27>KEGIATAN PANEN</th>";
	$view .= "<th align=center colspan=4>KEGIATAN RAWAT</th>";
	$view .= "<th align=center colspan=4>KEGIATAN BM TBS</th>";
	$view .= "<th align=center colspan=4>KEGIATAN TRAKSI</th>";
	$view .= "<th align=center colspan=4>KEGIATAN UMUM</th>";
	$view .= "<th align=center rowspan=3>TOTAL</th>";
	$view .= "</tr><tr class=rowheader>";
}

if ($tabMode == 'all' || $tabMode == 'panen') {
	$view .= "<th align=center rowspan=2>" . $_SESSION['lang']['luaspanen'] . "</th>";
	$view .= "<th align=center colspan=2>JJG</th>";
	$view .= "<th align=center rowspan=2>BJR</th>";
	$view .= "<th align=center colspan=2>KG</th>";
	$view .= "<th align=center colspan=2>Basis</th>";
	$view .= "<th align=center colspan=2>HK</th>";
	$view .= "<th align=center colspan=2>Rp/HK</th>";
	$view .= "<th align=center colspan=2>HK " . $_SESSION['lang']['potongan'] . "</th>";
	$view .= "<th align=center colspan=2>Rp/HK " . $_SESSION['lang']['potongan'] . "</th>";
	$view .= "<th align=center colspan=2>" . $_SESSION['lang']['lebihbasis'] . "</th>";
	$view .= "<th align=center colspan=2>" . $_SESSION['lang']['premlebihbasis'] . " (Rp)</th>";
	$view .= "<th align=center rowspan=2>Premi Kehadiran</th>";
	$view .= "<th align=center rowspan=2>Premi Kesulitan</th>";
	$view .= "<th align=center rowspan=2>Total Premi</th>";
	$view .= "<th align=center rowspan=2>" . $_SESSION['lang']['brondol'] . "</th>";
	$view .= "<th align=center rowspan=2>Rp " . $_SESSION['lang']['brondol'] . "</th>";
	$view .= "<th align=center rowspan=2>" . $_SESSION['lang']['dendapanen'] . "</th>";
	$view .= "<th align=center rowspan=2>Total Pendapatan PANEN</th>";
}

if ($tabMode == 'all' || $tabMode == 'rawat') {
	$view .= "<th align=center rowspan=2>HK</th>";
	$view .= "<th align=center rowspan=2>Upah</th>";
	$view .= "<th align=center rowspan=2>Premi</th>";
	$view .= "<th align=center rowspan=2>Total Pendapatan RAWAT</th>";
}

if ($tabMode == 'all' || $tabMode == 'bmtbs') {
	$view .= "<th align=center rowspan=2>HK</th>";
	$view .= "<th align=center rowspan=2>Upah</th>";
	$view .= "<th align=center rowspan=2>Premi</th>";
	$view .= "<th align=center rowspan=2>Total Pendapatan BM TBS</th>";
}

if ($tabMode == 'all' || $tabMode == 'traksi') {
	$view .= "<th align=center rowspan=2>HK</th>";
	$view .= "<th align=center rowspan=2>Upah</th>";
	$view .= "<th align=center rowspan=2>Premi</th>";
	$view .= "<th align=center rowspan=2>Total Pendapatan TRAKSI</th>";
}

if ($tabMode == 'all' || $tabMode == 'umum') {
	$view .= "<th align=center rowspan=2>HK</th>";
	$view .= "<th align=center rowspan=2>Upah</th>";
	$view .= "<th align=center rowspan=2>Premi</th>";
	$view .= "<th align=center rowspan=2>Total Pendapatan UMUM</th>";
}

if ($tabMode != 'all') {
	$view .= "<th align=center rowspan=2>TOTAL</th>";
}

$view .= "</tr>";

$view .= "<tr class=rowheader>";
if ($tabMode == 'all' || $tabMode == 'panen') {
	$view .= "<th>Buah Besar</th><th>Buah Kecil</th>";
	$view .= "<th>Buah Besar</th><th>Buah Kecil</th>";
	$view .= "<th>Buah Besar</th><th>Buah Kecil</th>";
	$view .= "<th>Buah Besar</th><th>Buah Kecil</th>";
	$view .= "<th>Buah Besar</th><th>Buah Kecil</th>";
	$view .= "<th>Buah Besar</th><th>Buah Kecil</th>";
	$view .= "<th>Buah Besar</th><th>Buah Kecil</th>";
	$view .= "<th>Buah Besar</th><th>Buah Kecil</th>";
	$view .= "<th>Buah Besar</th><th>Buah Kecil</th>";
}
$view .= "</tr>";
$view .= "</thead>";


#= Tabel Body
$view .= "<tbody>";
$subtotal = array();
$GTtotal = array();
$no = 0;
$adaData = false;
if (count($karyawan) > 0) {
	foreach ($karyawan as $karykey => $arrtglx) {
		#= Kelompokkan data per Divisi Tugas agar subtotal divisi tampil berurutan per karyawan
		$arrDivisiKaryawan = array();
		foreach ($arrtglx as $tglbkmx => $arrdiv) {
			foreach ($arrdiv as $divisi_key => $dummy) {
				$arrDivisiKaryawan[$divisi_key][$tglbkmx] = $dummy;
			}
		}
		ksort($arrDivisiKaryawan);

		$GTtotal[$karykey] = array();
		$adaDataKaryawan = false;

		foreach ($arrDivisiKaryawan as $divisi_key => $arrTanggal) {
			ksort($arrTanggal);
			$subtotal[$karykey][$divisi_key] = array();
			$adaDataDivisi = false;

			foreach ($arrTanggal as $tglbkmx => $dummy) {
				#= Jangan tampilkan baris jika semua nilai pada mode laporan yang aktif adalah 0
				$nilaiRow = array();
				if ($tabMode == 'all' || $tabMode == 'panen') {
					$nilaiRow[] = $luaspanen[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $jjgbuahbesar[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $jjgbuahkecil[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $bjrpanen[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $kgbuahbesar[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $kgbuahkecil[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $basisbuahbesar[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $basisbuahkecil[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $hkbuahbesar[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $hkbuahkecil[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $rphkbuahbesar[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $rphkbuahkecil[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $hkbuahbesarpot[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $hkbuahkecilpot[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $rphkbuahbesarpot[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $rphkbuahkecilpot[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $lbbuahbesar[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $lbbuahkecil[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $rplbbuahbesar[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $rplbbuahkecil[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $premikehadiranpanen[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $premikesulitanpanen[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $totalpremipanen[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $brondolan[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $rpbrondolan[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $dendapanen[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $totalrppanen[$karykey][$tglbkmx][$divisi_key];
				}
				if ($tabMode == 'all' || $tabMode == 'rawat') {
					$nilaiRow[] = $hk_rawat[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $umr_rawat[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $premi_rawat[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $total_pertgl_rawat[$karykey][$tglbkmx][$divisi_key];
				}
				if ($tabMode == 'all' || $tabMode == 'bmtbs') {
					$nilaiRow[] = $hk_bmtbs[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $umr_bmtbs[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $premi_bmtbs[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $total_pertgl_bmtbs[$karykey][$tglbkmx][$divisi_key];
				}
				if ($tabMode == 'all' || $tabMode == 'traksi') {
					$nilaiRow[] = $hk_traksi[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $umr_traksi[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $premi_traksi[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $total_pertgl_traksi[$karykey][$tglbkmx][$divisi_key];
				}
				if ($tabMode == 'all' || $tabMode == 'umum') {
					$nilaiRow[] = $hk_sdm[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $umr_sdm[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $premi_sdm[$karykey][$tglbkmx][$divisi_key];
					$nilaiRow[] = $total_pertgl_sdm[$karykey][$tglbkmx][$divisi_key];
				}

				$adaNilai = false;
				foreach ($nilaiRow as $nilai) {
					if ((float)$nilai != 0) {
						$adaNilai = true;
						break;
					}
				}
				if (!$adaNilai) continue;

				$adaData = true;
				$adaDataKaryawan = true;
				$adaDataDivisi = true;
				$no += 1;

				$view .= "<tr class=rowcontent>";
				$view .= "<td align=center>" . $no . "</td>";
				$view .= "<td align=left>" . tanggalnormal($tglbkmx) . "</td>";
				$view .= "<td align=left>" . $nik[$karykey] . "</td>";
				$view .= "<td align=left>" . $namakaryawan[$karykey] . "</td>";
				$view .= "<td align=left>" . $subbagian[$karykey] . "</td>";
				$view .= "<td align=left>" . $divisi_key . "</td>";

				if ($tabMode == 'all' || $tabMode == 'panen') {
					$view .= "<td align=left>" . $luaspanen[$karykey][$tglbkmx][$divisi_key] . "</td>";
					$view .= "<td align=right>" . number_format($jjgbuahbesar[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($jjgbuahkecil[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($bjrpanen[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($kgbuahbesar[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($kgbuahkecil[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($basisbuahbesar[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($basisbuahkecil[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($hkbuahbesar[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($hkbuahkecil[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($rphkbuahbesar[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($rphkbuahkecil[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($hkbuahbesarpot[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($hkbuahkecilpot[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($rphkbuahbesarpot[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($rphkbuahkecilpot[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($lbbuahbesar[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($lbbuahkecil[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($rplbbuahbesar[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($rplbbuahkecil[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($premikehadiranpanen[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($premikesulitanpanen[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($totalpremipanen[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($brondolan[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($rpbrondolan[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($dendapanen[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($totalrppanen[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
				}

				if ($tabMode == 'all' || $tabMode == 'rawat') {
					$view .= "<td align=right>" . number_format($hk_rawat[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($umr_rawat[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($premi_rawat[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($total_pertgl_rawat[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
				}

				if ($tabMode == 'all' || $tabMode == 'bmtbs') {
					$view .= "<td align=right>" . number_format($hk_bmtbs[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($umr_bmtbs[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($premi_bmtbs[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($total_pertgl_bmtbs[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
				}

				if ($tabMode == 'all' || $tabMode == 'traksi') {
					$view .= "<td align=right>" . number_format($hk_traksi[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($umr_traksi[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($premi_traksi[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($total_pertgl_traksi[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
				}

				if ($tabMode == 'all' || $tabMode == 'umum') {
					$view .= "<td align=right>" . number_format($hk_sdm[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($umr_sdm[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($premi_sdm[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
					$view .= "<td align=right>" . number_format($total_pertgl_sdm[$karykey][$tglbkmx][$divisi_key], 2) . "</td>";
				}

				$totalRow = 0;
				if ($tabMode == 'all' || $tabMode == 'panen') $totalRow += $totalrppanen[$karykey][$tglbkmx][$divisi_key];
				if ($tabMode == 'all' || $tabMode == 'rawat') $totalRow += $total_pertgl_rawat[$karykey][$tglbkmx][$divisi_key];
				if ($tabMode == 'all' || $tabMode == 'bmtbs') $totalRow += $total_pertgl_bmtbs[$karykey][$tglbkmx][$divisi_key];
				if ($tabMode == 'all' || $tabMode == 'traksi') $totalRow += $total_pertgl_traksi[$karykey][$tglbkmx][$divisi_key];
				if ($tabMode == 'all' || $tabMode == 'umum') $totalRow += $total_pertgl_sdm[$karykey][$tglbkmx][$divisi_key];

				$view .= "<td align=right>" . number_format($totalRow, 2) . "</td>";
				$view .= "</tr>";

				#= Nilai yang ditotal per Divisi Tugas dan Grand Total per karyawan
				$summaryRow = array(
					'luas' => $luaspanen[$karykey][$tglbkmx][$divisi_key],
					'jjgBesar' => $jjgbuahbesar[$karykey][$tglbkmx][$divisi_key],
					'jjgKecil' => $jjgbuahkecil[$karykey][$tglbkmx][$divisi_key],
					'buahBesar' => $kgbuahbesar[$karykey][$tglbkmx][$divisi_key],
					'buahKecil' => $kgbuahkecil[$karykey][$tglbkmx][$divisi_key],
					'hkBesar' => $hkbuahbesar[$karykey][$tglbkmx][$divisi_key],
					'hkKecil' => $hkbuahkecil[$karykey][$tglbkmx][$divisi_key],
					'rpBesar' => $rphkbuahbesar[$karykey][$tglbkmx][$divisi_key],
					'rpKecil' => $rphkbuahkecil[$karykey][$tglbkmx][$divisi_key],
					'hkpotBesar' => $hkbuahbesarpot[$karykey][$tglbkmx][$divisi_key],
					'hkpotKecil' => $hkbuahkecilpot[$karykey][$tglbkmx][$divisi_key],
					'potBesar' => $rphkbuahbesarpot[$karykey][$tglbkmx][$divisi_key],
					'potKecil' => $rphkbuahkecilpot[$karykey][$tglbkmx][$divisi_key],
					'lbBesar' => $lbbuahbesar[$karykey][$tglbkmx][$divisi_key],
					'lbKecil' => $lbbuahkecil[$karykey][$tglbkmx][$divisi_key],
					'premiBesar' => $rplbbuahbesar[$karykey][$tglbkmx][$divisi_key],
					'premiKecil' => $rplbbuahkecil[$karykey][$tglbkmx][$divisi_key],
					'premikehadiran' => $premikehadiranpanen[$karykey][$tglbkmx][$divisi_key],
					'premikesulitan' => $premikesulitanpanen[$karykey][$tglbkmx][$divisi_key],
					'totalpremi' => $totalpremipanen[$karykey][$tglbkmx][$divisi_key],
					'brondolan' => $brondolan[$karykey][$tglbkmx][$divisi_key],
					'rpBrondolan' => $rpbrondolan[$karykey][$tglbkmx][$divisi_key],
					'denda' => $dendapanen[$karykey][$tglbkmx][$divisi_key],
					'totalrppanen' => $totalrppanen[$karykey][$tglbkmx][$divisi_key],
					'hk_rawat' => $hk_rawat[$karykey][$tglbkmx][$divisi_key],
					'umr_rawat' => $umr_rawat[$karykey][$tglbkmx][$divisi_key],
					'premi_rawat' => $premi_rawat[$karykey][$tglbkmx][$divisi_key],
					'total_rawat' => $total_pertgl_rawat[$karykey][$tglbkmx][$divisi_key],
					'hk_bmtbs' => $hk_bmtbs[$karykey][$tglbkmx][$divisi_key],
					'umr_bmtbs' => $umr_bmtbs[$karykey][$tglbkmx][$divisi_key],
					'premi_bmtbs' => $premi_bmtbs[$karykey][$tglbkmx][$divisi_key],
					'total_bmtbs' => $total_pertgl_bmtbs[$karykey][$tglbkmx][$divisi_key],
					'hk_traksi' => $hk_traksi[$karykey][$tglbkmx][$divisi_key],
					'umr_traksi' => $umr_traksi[$karykey][$tglbkmx][$divisi_key],
					'premi_traksi' => $premi_traksi[$karykey][$tglbkmx][$divisi_key],
					'total_traksi' => $total_pertgl_traksi[$karykey][$tglbkmx][$divisi_key],
					'hk_sdm' => $hk_sdm[$karykey][$tglbkmx][$divisi_key],
					'umr_sdm' => $umr_sdm[$karykey][$tglbkmx][$divisi_key],
					'premi_sdm' => $premi_sdm[$karykey][$tglbkmx][$divisi_key],
					'total_sdm' => $total_pertgl_sdm[$karykey][$tglbkmx][$divisi_key]
				);

				foreach ($summaryRow as $keySummary => $nilaiSummary) {
					$subtotal[$karykey][$divisi_key][$keySummary] += $nilaiSummary;
					$GTtotal[$karykey][$keySummary] += $nilaiSummary;
				}
			}

			#= Sub Total per Divisi Tugas untuk masing-masing karyawan
			if ($adaDataDivisi) {
				$view .= "<tr class=rowcontent style='background-color:#fff2cc; font-weight:bold;'>";
				$view .= "<td align=center colspan=6><b>SUB TOTAL DIVISI TUGAS ( " . $divisi_key . " ) - " . $namakaryawan[$karykey] . "</b></td>";

				if ($tabMode == 'all' || $tabMode == 'panen') {
					$view .= "<td align=center>" . $subtotal[$karykey][$divisi_key]['luas'] . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['jjgBesar'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['jjgKecil'], 2) . "</td>";
					$view .= "<td align=center></td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['buahBesar'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['buahKecil'], 2) . "</td>";
					$view .= "<td align=center></td>";
					$view .= "<td align=center></td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['hkBesar'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['hkKecil'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['rpBesar'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['rpKecil'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['hkpotBesar'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['hkpotKecil'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['potBesar'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['potKecil'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['lbBesar'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['lbKecil'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['premiBesar'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['premiKecil'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['premikehadiran'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['premikesulitan'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['totalpremi'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['brondolan'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['rpBrondolan'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['denda'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['totalrppanen'], 2) . "</td>";
				}

				if ($tabMode == 'all' || $tabMode == 'rawat') {
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['hk_rawat'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['umr_rawat'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['premi_rawat'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['total_rawat'], 2) . "</td>";
				}

				if ($tabMode == 'all' || $tabMode == 'bmtbs') {
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['hk_bmtbs'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['umr_bmtbs'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['premi_bmtbs'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['total_bmtbs'], 2) . "</td>";
				}

				if ($tabMode == 'all' || $tabMode == 'traksi') {
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['hk_traksi'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['umr_traksi'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['premi_traksi'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['total_traksi'], 2) . "</td>";
				}

				if ($tabMode == 'all' || $tabMode == 'umum') {
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['hk_sdm'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['umr_sdm'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['premi_sdm'], 2) . "</td>";
					$view .= "<td align=center>" . number_format($subtotal[$karykey][$divisi_key]['total_sdm'], 2) . "</td>";
				}

				$subTotalRow = 0;
				if ($tabMode == 'all' || $tabMode == 'panen') $subTotalRow += $subtotal[$karykey][$divisi_key]['totalrppanen'];
				if ($tabMode == 'all' || $tabMode == 'rawat') $subTotalRow += $subtotal[$karykey][$divisi_key]['total_rawat'];
				if ($tabMode == 'all' || $tabMode == 'bmtbs') $subTotalRow += $subtotal[$karykey][$divisi_key]['total_bmtbs'];
				if ($tabMode == 'all' || $tabMode == 'traksi') $subTotalRow += $subtotal[$karykey][$divisi_key]['total_traksi'];
				if ($tabMode == 'all' || $tabMode == 'umum') $subTotalRow += $subtotal[$karykey][$divisi_key]['total_sdm'];

				$view .= "<td align=center>" . number_format($subTotalRow, 2) . "</td>";
				$view .= "</tr>";
			}
		}

		#= Grand Total per karyawan dari seluruh Divisi Tugas yang memiliki nilai
		if ($adaDataKaryawan) {
			$view .= "<tr class=rowcontent style='background-color:cyan; font-weight:bold;'>";
			$view .= "<td align=center colspan=6><b>GRAND TOTAL ( " . $namakaryawan[$karykey] . " )</b></td>";

			if ($tabMode == 'all' || $tabMode == 'panen') {
				$view .= "<td align=center>" . $GTtotal[$karykey]['luas'] . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['jjgBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['jjgKecil'], 2) . "</td>";
				$view .= "<td align=center></td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['buahBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['buahKecil'], 2) . "</td>";
				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['hkBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['hkKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['rpBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['rpKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['hkpotBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['hkpotKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['potBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['potKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['lbBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['lbKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['premiBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['premiKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['premikehadiran'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['premikesulitan'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['totalpremi'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['brondolan'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['rpBrondolan'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['denda'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['totalrppanen'], 2) . "</td>";
			}

			if ($tabMode == 'all' || $tabMode == 'rawat') {
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['hk_rawat'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['umr_rawat'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['premi_rawat'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['total_rawat'], 2) . "</td>";
			}

			if ($tabMode == 'all' || $tabMode == 'bmtbs') {
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['hk_bmtbs'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['umr_bmtbs'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['premi_bmtbs'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['total_bmtbs'], 2) . "</td>";
			}

			if ($tabMode == 'all' || $tabMode == 'traksi') {
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['hk_traksi'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['umr_traksi'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['premi_traksi'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['total_traksi'], 2) . "</td>";
			}

			if ($tabMode == 'all' || $tabMode == 'umum') {
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['hk_sdm'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['umr_sdm'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['premi_sdm'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($GTtotal[$karykey]['total_sdm'], 2) . "</td>";
			}

			$gtRow = 0;
			if ($tabMode == 'all' || $tabMode == 'panen') $gtRow += $GTtotal[$karykey]['totalrppanen'];
			if ($tabMode == 'all' || $tabMode == 'rawat') $gtRow += $GTtotal[$karykey]['total_rawat'];
			if ($tabMode == 'all' || $tabMode == 'bmtbs') $gtRow += $GTtotal[$karykey]['total_bmtbs'];
			if ($tabMode == 'all' || $tabMode == 'traksi') $gtRow += $GTtotal[$karykey]['total_traksi'];
			if ($tabMode == 'all' || $tabMode == 'umum') $gtRow += $GTtotal[$karykey]['total_sdm'];

			$view .= "<td align=center>" . number_format($gtRow, 2) . "</td>";
			$view .= "</tr>";
		}
	}
}

if (!$adaData) {
	$colspan = 7;
	if ($tabMode == 'all') $colspan += 43;
	elseif ($tabMode == 'panen') $colspan += 27;
	else $colspan += 4;

	$view .= "<tr class=rowcontent>";
	$view .= "<td align=center colspan=" . $colspan . "><b style=color:red>Tidak ada data</b></td>";
	$view .= "</tr>";
}

$view .= "</tbody>";

$view .= "</table></div>";
#= Akhir Tabel

switch ($proses) {
	case "getDivisi":
		$optDiv = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$where = "induk='" . $param['unit'] . "'";
		$where .= " AND tipe in ('AFDELING','BIBITAN')";
		$query = selectQuery($dbname, "organisasi", "kodeorganisasi,namaorganisasi", $where);
		$result = fetchData($query);
		foreach ($result as $val) {
			$optDiv .= "<option value=" . $val['kodeorganisasi'] . ">" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
		}
		echo $optDiv;
		break;

	case "preview":
	case "previewPanen":
	case "previewRawat":
	case "previewBMTBS":
	case "previewTraksi":
	case "previewUmum":
		if ($param['tgl'] == "" || $param['tglx'] == "") {
			exit("Warning : Tanggal wajib di Isi !");
		}
		if ($param['unit'] == "") {
			exit('Warning: Unit usaha harus dipilih');
		}
		echo $view;
		break;

	case "excel":
		$tabTitle = "Semua Data";
		if ($tabMode == 'panen') $tabTitle = "Kegiatan Panen";
		elseif ($tabMode == 'rawat') $tabTitle = "Kegiatan Rawat";
		elseif ($tabMode == 'bmtbs') $tabTitle = "Kegiatan BM TBS";
		elseif ($tabMode == 'traksi') $tabTitle = "Kegiatan Traksi";
		elseif ($tabMode == 'umum') $tabTitle = "Kegiatan Umum";

		$nop = "Laporan_Summary_Upah_Panen_" . $tabTitle . ".xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet($tabTitle, $view);
		$xls->headers($nop);
		echo $xls->buildFile();
		break;
}
