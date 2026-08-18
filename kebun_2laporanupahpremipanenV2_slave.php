<?php
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

if ($proses == "preview" || $proses == "excel") {
	#= Convert Data
	$nik = makeOption($dbname, "datakaryawan", "karyawanid,nik", "", "nik asc");
	$namakaryawan = makeOption($dbname, "datakaryawan", "karyawanid,namakaryawan");
	$subbagian = makeOption($dbname, "datakaryawan", "karyawanid,subbagian");
	$divisi = makeOption($dbname, "organisasi", "kodeorganisasi,namaorganisasi");
	$kegiatan = makeOption($dbname, "setup_kegiatan", "kodekegiatan,namakegiatan");
	$satuan = makeOption($dbname, "setup_kegiatan", "kodekegiatan,satuan");

	#= Filter tanggal by Notransaksi
	$tglAwal = explode("-", $param['tgl']);
	$tglAkhir = explode("-", $param['tglx']);

	$tglAwal = $tglAwal[2] . $tglAwal[1] . $tglAwal[0];
	$tglAkhir = $tglAkhir[2] . $tglAkhir[1] . $tglAkhir[0];

	$whereKp = "";
	$wherePnn = "";
	$whereBKM = "";
	$whereBMTBS = "";
	$whereTRAKSI = "";
	$whereSDM = "";

	if ($param['unit'] == "") {
		exit("Warning : Unit wajib diisi ! ");
	}

	if ($param['unit'] != "") {
		$whereKp .= " and a.kodeorg LIKE '" . $param['unit'] . "%'";
		$whereBKM .= " and a.unit LIKE '" . $param['unit'] . "%'";
		$whereBMTBS .= " and a.kodeorg LIKE '" . $param['unit'] . "%'";
		$whereTRAKSI .= " and a.kodeorg LIKE '" . $param['unit'] . "%'";
		$whereSDM .= " and a.kodeorg LIKE '" . $param['unit'] . "%'";
	}

	if ($param['div'] != "") {
		$whereKp .= " and a.divisi LIKE '%" . $param['div'] . "%'";
		$wherePnn .= " and a.kodeorg like '" . $param['div'] . "%' ";
		$whereBKM .= " and a.kodeorg like '" . $param['div'] . "%' ";
		$whereBMTBS .= " and a.divisi like '" . $param['div'] . "%' ";
		$whereTRAKSI .= " and a.kodeorg like '" . $param['div'] . "%'";
		$whereSDM .= " and a.kodeorg like '" . $param['div'] . "%'";
	} else {
		$wherePnn .= " and a.kodeorg like '" . $param['unit'] . "%' ";
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

	#= Ambil HA panen untuk tabel validasi
	$str = "select a.* from " . $dbname . ".kebun_rekaphancakpanen a
		where a.tanggal between '" . $tglAwal . "' and '" . $tglAkhir . "' " . $wherePnn . "
		order by a.tanggal asc";
	$res = $owlPDO->query($str) or die(" Gagal: " . $owlPDO->errorInfo()[2]);
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$Hektarpanenx[$bar['tanggal']][$bar['kodeorg']][$bar['nik']] += $bar['hapanen'];
	}

	#= Ambil data premi panen lama, jika kosong gunakan tabel V2
	$query = "select a.*, b.namakaryawan from " . $dbname . ".kebun_3premipemanen a
		left join datakaryawan b on a.karyawanid=b.karyawanid
		where 1=1 " . $whereKp . "
		order by a.tanggalpanen asc, b.namakaryawan asc, a.blok asc";
	$result = fetchData($query);

	if (count($result) == 0) {
		$query = "select
			a.notransaksi,
			a.kodeorg,
			a.divisi,
			a.periode,
			a.tanggalpanen,
			a.nospb,
			a.mandor,
			a.kerani,
			a.karyawanid,
			a.tahuntanam,
			a.indukblok as blok,
			a.hapanen,
			a.jjg as jjgbuahbesar,
			0 as jjgbuahkecil,
			a.bjr as bjrbesar,
			0 as bjrkecil,
			a.kg as kgbuahbesar,
			0 as kgbuahkecil,
			a.basis as basisbuahbesar,
			0 as basisbuahkecil,
			a.hk as hkbuahbesar,
			0 as hkbuahkecil,
			a.upah as rphkbuahbesar,
			0 as rphkbuahkecil,
			a.pothk as hkbuahbesarpot,
			0 as hkbuahkecilpot,
			a.potupah as rphkbuahbesarpot,
			0 as rphkbuahkecilpot,
			a.lbbasis as lbbuahbesar,
			0 as lbbuahkecil,
			a.premilb as rplbbuahbesar,
			0 as rplbbuahkecil,
			a.premikehadiran,
			a.premikesulitan,
			a.brondol as brondolan,
			a.upahbro as rpbrondolan,
			a.denda as dendapanen,
			a.totalupah as total,
			a.jurnal,
			a.posting,
			a.postingdate,
			b.namakaryawan
		from " . $dbname . ".kebun_3premipemanen_v2 a
		left join datakaryawan b on a.karyawanid=b.karyawanid
		where 1=1 " . $whereKp . "
		order by a.tanggalpanen asc, b.namakaryawan asc, a.indukblok asc";
		$result = fetchData($query);
	}

	foreach ($result as $val) {
		$karyid = $val['karyawanid'];
		$tgl = $val['tanggalpanen'];
		$kodeorg = $val['kodeorg'];
		$spb = $val['nospb'];
		$blok = $val['blok'];

		$jjgBesar = isset($val['jjgbuahbesar']) ? $val['jjgbuahbesar'] : 0;
		$jjgKecil = isset($val['jjgbuahkecil']) ? $val['jjgbuahkecil'] : 0;
		$rpLbBesar = isset($val['rplbbuahbesar']) ? $val['rplbbuahbesar'] : 0;
		$rpLbKecil = isset($val['rplbbuahkecil']) ? $val['rplbbuahkecil'] : 0;
		$premiKehadiran = isset($val['premikehadiran']) ? $val['premikehadiran'] : 0;
		$premiKesulitan = isset($val['premikesulitan']) ? $val['premikesulitan'] : 0;

		if (!isset($datapanentgl[$tgl][$karyid])) {
			$datapanentgl[$tgl][$karyid] = 0;
		}
		if (!isset($datapanentglxz[$tgl][$karyid][$blok])) {
			$datapanentglxz[$tgl][$karyid][$blok] = 0;
		}

		$datapanentgl[$tgl][$karyid] += $jjgBesar + $jjgKecil;
		$datapanentglxz[$tgl][$karyid][$blok] += $jjgBesar + $jjgKecil;

		$karyawan[$karyid][$tgl][$kodeorg][$spb][$blok] = $karyid;
		$notransaksix[$karyid][$tgl][$kodeorg][$spb][$blok] = $val['notransaksi'];
		$nikmandor[$karyid][$tgl][$kodeorg][$spb][$blok] = $val['mandor'];
		$nikkerani[$karyid][$tgl][$kodeorg][$spb][$blok] = $val['kerani'];
		$divisix[$karyid][$tgl][$kodeorg][$spb][$blok] = $val['divisi'];
		$blokx[$karyid][$tgl][$kodeorg][$spb][$blok] = $blok;
		$nospb[$karyid][$tgl][$kodeorg][$spb][$blok] = $spb;
		$luaspanen[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['hapanen']) ? $val['hapanen'] : 0;

		$jjgbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok] = $jjgBesar;
		$jjgbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok] = $jjgKecil;
		$bjrpanenbesar[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['bjrbesar']) ? $val['bjrbesar'] : 0;
		$bjrpanenkecil[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['bjrkecil']) ? $val['bjrkecil'] : 0;

		$kgbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['kgbuahbesar']) ? $val['kgbuahbesar'] : 0;
		$kgbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['kgbuahkecil']) ? $val['kgbuahkecil'] : 0;
		$basisbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['basisbuahbesar']) ? $val['basisbuahbesar'] : 0;
		$basisbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['basisbuahkecil']) ? $val['basisbuahkecil'] : 0;
		$hkbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['hkbuahbesar']) ? $val['hkbuahbesar'] : 0;
		$hkbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['hkbuahkecil']) ? $val['hkbuahkecil'] : 0;
		$rphkbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['rphkbuahbesar']) ? $val['rphkbuahbesar'] : 0;
		$rphkbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['rphkbuahkecil']) ? $val['rphkbuahkecil'] : 0;
		$hkbuahbesarpot[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['hkbuahbesarpot']) ? $val['hkbuahbesarpot'] : 0;
		$hkbuahkecilpot[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['hkbuahkecilpot']) ? $val['hkbuahkecilpot'] : 0;
		$rphkbuahbesarpot[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['rphkbuahbesarpot']) ? $val['rphkbuahbesarpot'] : 0;
		$rphkbuahkecilpot[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['rphkbuahkecilpot']) ? $val['rphkbuahkecilpot'] : 0;
		$lbbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['lbbuahbesar']) ? $val['lbbuahbesar'] : 0;
		$lbbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['lbbuahkecil']) ? $val['lbbuahkecil'] : 0;
		$rplbbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok] = $rpLbBesar;
		$rplbbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok] = $rpLbKecil;
		$premikehadiranpanen[$karyid][$tgl][$kodeorg][$spb][$blok] = $premiKehadiran;
		$premikesulitanpanen[$karyid][$tgl][$kodeorg][$spb][$blok] = $premiKesulitan;
		$totalpremipanen[$karyid][$tgl][$kodeorg][$spb][$blok] = $rpLbBesar + $rpLbKecil + $premiKehadiran + $premiKesulitan;
		$brondolan[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['brondolan']) ? $val['brondolan'] : 0;
		$rpbrondolan[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['rpbrondolan']) ? $val['rpbrondolan'] : 0;
		$dendapanen[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['dendapanen']) ? $val['dendapanen'] : 0;
		$totalrppanen[$karyid][$tgl][$kodeorg][$spb][$blok] = isset($val['total']) ? $val['total'] : 0;

		#= Grand total seluruh data panen
		$gtotluaspanen += $luaspanen[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotjjgbuahbesar += $jjgbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotjjgbuahkecil += $jjgbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotkgbuahbesar += $kgbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotkgbuahkecil += $kgbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtothkbuahbesar += $hkbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtothkbuahkecil += $hkbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotrphkbuahbesar += $rphkbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotrphkbuahkecil += $rphkbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtothkbuahbesarpot += $hkbuahbesarpot[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtothkbuahkecilpot += $hkbuahkecilpot[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotrphkbuahbesarpot += $rphkbuahbesarpot[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotrphkbuahkecilpot += $rphkbuahkecilpot[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotlbbuahbesar += $lbbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotlbbuahkecil += $lbbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotrplbbuahbesar += $rplbbuahbesar[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotrplbbuahkecil += $rplbbuahkecil[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotpremikehadiran += $premikehadiranpanen[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotpremikesulitan += $premikesulitanpanen[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtottotalpremipanen += $totalpremipanen[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotbrondolan += $brondolan[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotrpbrondolan += $rpbrondolan[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtotdendapanen += $dendapanen[$karyid][$tgl][$kodeorg][$spb][$blok];
		$gtottotalrppanen += $totalrppanen[$karyid][$tgl][$kodeorg][$spb][$blok];
	}

	#= Ambil kegiatan rawat
	#= Referensi summary upah panen: simpan juga per karyawan + tanggal agar dapat ditampilkan pada detail/subtotal
	$query = "select a.*, b.namakaryawan from " . $dbname . ".kebun_kehadiran_vw a
		left join datakaryawan b on a.karyawanid=b.karyawanid
		where 1=1 " . $whereBKM . "
		order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
	$resultRawat = fetchData($query);
	foreach ($resultRawat as $val) {
		$karyid = $val['karyawanid'];
		$tgl = $val['tanggal'];

		$hk_rawat[$karyid][$tgl] += $val['jhk'];
		$umr_rawat[$karyid][$tgl] += $val['umr'];
		$premi_rawat[$karyid][$tgl] += $val['insentif'];
		$total_pertgl_rawat[$karyid][$tgl] += $val['umr'] + $val['insentif'];

		$jhkRawat[$karyid] += $val['jhk'];
		$upahRawat[$karyid] += $val['umr'];
		$premiRawat[$karyid] += $val['insentif'];
		$totalRAWAT[$karyid] += $val['umr'] + $val['insentif'];

		$gtottotalrjhkRawat += $val['jhk'];
		$gtottotalrumrRawat += $val['umr'];
		$gtottotalrpremiRawat += $val['insentif'];
		$gtRAWAT += $val['umr'] + $val['insentif'];

		#= Jika pada tanggal tersebut tidak ada panen, buat baris khusus agar kegiatan non panen tetap tampil
		if (!isset($karyawan[$karyid][$tgl])) {
			$karyawan[$karyid][$tgl]['__NONPANEN__']['__NONPANEN__']['__NONPANEN__'] = $karyid;
		}
	}

	#= Ambil kegiatan BM TBS
	$query = "select a.*, b.namakaryawan from " . $dbname . ".kebun_3premibmtbs a
		left join datakaryawan b on a.karyawanid=b.karyawanid
		where 1=1 " . $whereBMTBS . "
		order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
	$resultBMTBS = fetchData($query);
	foreach ($resultBMTBS as $val) {
		$karyid = $val['karyawanid'];
		$tgl = $val['tanggal'];

		$hk_bmtbs[$karyid][$tgl] += $val['hk'];
		$umr_bmtbs[$karyid][$tgl] += $val['rphk'];
		$premi_bmtbs[$karyid][$tgl] += $val['rppremi'];
		$total_pertgl_bmtbs[$karyid][$tgl] += $val['rphk'] + $val['rppremi'];

		$hkBMTBS[$karyid] += $val['hk'];
		$rphkBMTBS[$karyid] += $val['rphk'];
		$premiBMTBS[$karyid] += $val['rppremi'];
		$totalBMTBS[$karyid] += $val['rphk'] + $val['rppremi'];

		$gtottotalHKBMTBS += $val['hk'];
		$gtottotalRPHKBMTBS += $val['rphk'];
		$gtottotalPREMIBMTBS += $val['rppremi'];
		$gtBMTBS += $val['rphk'] + $val['rppremi'];

		if (!isset($karyawan[$karyid][$tgl])) {
			$karyawan[$karyid][$tgl]['__NONPANEN__']['__NONPANEN__']['__NONPANEN__'] = $karyid;
		}
	}

	#= Ambil kegiatan traksi
	$query = "select a.*, b.namakaryawan from " . $dbname . ".vhc_runhk_vw a
		left join datakaryawan b on a.idkaryawan=b.karyawanid
		where 1=1 " . $whereTRAKSI . "
		order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
	$resultTraksi = fetchData($query);
	foreach ($resultTraksi as $val) {
		$karyid = $val['idkaryawan'];
		$tgl = $val['tanggal'];

		$hk_traksi[$karyid][$tgl] += $val['hk'];
		$umr_traksi[$karyid][$tgl] += $val['upah'];
		$premi_traksi[$karyid][$tgl] += $val['premi'];
		$total_pertgl_traksi[$karyid][$tgl] += $val['upah'] + $val['premi'];

		$HK_TRAKSI[$karyid] += $val['hk'];
		$RP_TRAKSI[$karyid] += $val['upah'];
		$PREMI_TRAKSI[$karyid] += $val['premi'];
		$totalTRAKSI[$karyid] += $val['upah'] + $val['premi'];

		$gtottotalHK_TRAKSI += $val['hk'];
		$gtottotalRP_TRAKSI += $val['upah'];
		$gtottotalPREMI_TRAKSI += $val['premi'];
		$gtTRAKSI += $val['upah'] + $val['premi'];

		if (!isset($karyawan[$karyid][$tgl])) {
			$karyawan[$karyid][$tgl]['__NONPANEN__']['__NONPANEN__']['__NONPANEN__'] = $karyid;
		}
	}

	#= Ambil kegiatan umum
	$query = "select a.*, b.namakaryawan from " . $dbname . ".sdm_absensidt a
		left join datakaryawan b on a.karyawanid=b.karyawanid
		where 1=1 " . $whereSDM . "
		order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
	$resultSDM = fetchData($query);
	foreach ($resultSDM as $val) {
		$karyid = $val['karyawanid'];
		$tgl = $val['tanggal'];

		$hk_sdm[$karyid][$tgl] += $val['hk'];
		$umr_sdm[$karyid][$tgl] += $val['umr'];
		$premi_sdm[$karyid][$tgl] += $val['premi'];
		$total_pertgl_sdm[$karyid][$tgl] += $val['umr'] + $val['premi'];

		$HK_SDM[$karyid] += $val['hk'];
		$RP_SDM[$karyid] += $val['umr'];
		$PREMI_SDM[$karyid] += $val['premi'];
		$totalSDM[$karyid] += $val['umr'] + $val['premi'];

		$gtottotalHK_SDM += $val['hk'];
		$gtottotalRP_SDM += $val['umr'];
		$gtottotalPREMI_SDM += $val['premi'];
		$gtSDM += $val['umr'] + $val['premi'];

		if (!isset($karyawan[$karyid][$tgl])) {
			$karyawan[$karyid][$tgl]['__NONPANEN__']['__NONPANEN__']['__NONPANEN__'] = $karyid;
		}
	}

	#= Tabel validasi HA
	if ($proses == "excel") {
		$view .= "<table class=sortable cellspacing=1 border=1>";
	} else {
		$view .= "<div><table class=sortable cellspacing=1 border=0 width=50%>";
	}

	@$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
	@$nikkar = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
	$nmorg = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok');

	$view .= "<thead><tr class=rowheader><td align='center' colspan='5'>HA Panen Jajang 0 Tapi Memiliki Janjang di Blok Lain</td></tr>";
	$view .= "<tr class=rowheader>";
	$view .= "<td align=center width=100px>Tanggal Panen</td>";
	$view .= "<td align=center width=120px>NIK</td>";
	$view .= "<td align=center width=120px>Nama Karyawan</td>";
	$view .= "<td align=center width=120px>BLOK</td>";
	$view .= "<td align=center width=75px>HA</td>";
	$view .= "</tr></thead>";

	foreach ($Hektarpanenx as $tglpnn => $key) {
		foreach ($key as $kdblok => $key2) {
			foreach ($key2 as $kary => $val) {
				if ($datapanentgl[$tglpnn][$kary] > 0 && !isset($datapanentglxz[$tglpnn][$kary][$kdblok])) {
					$view .= "<tr class=rowcontent>";
					$view .= "<td align=center>" . tanggalnormal($tglpnn) . "</td>";
					$view .= "<td align=center>" . $nikkar[$kary] . "</td>";
					$view .= "<td align=center>" . $nmkar[$kary] . "</td>";
					$view .= "<td align=center>" . $nmorg[$kdblok] . "</td>";
					$view .= "<td align=right>" . number_format($val, 2) . "</td>";
					$view .= "</tr>";
				}
			}
		}
	}
	$view .= "</table></div>";

	#= Awal tabel utama
	if ($proses == "excel") {
		$view .= "<table class=sortable cellspacing=1 border=1>";
	} else {
		$view .= "<div><table class='sortable z-freeze-target' cellspacing=1 cellpadding=5 border=0 width=100%>";
	}

	$view .= "<thead>";
	$view .= "<tr class=rowheader>";
	$view .= "<th align=center rowspan=3>No</th>";
	$view .= "<th align=center rowspan=3>" . $_SESSION['lang']['notransaksi'] . "</th>";
	$view .= "<th align=center rowspan=3>" . $_SESSION['lang']['tanggal'] . " " . $_SESSION['lang']['panen'] . "</th>";
	$view .= "<th align=center rowspan=3>NIK</th>";
	$view .= "<th align=center rowspan=3>" . $_SESSION['lang']['nikmandor'] . "</th>";
	$view .= "<th align=center rowspan=3>NIK</th>";
	$view .= "<th align=center rowspan=3>" . $_SESSION['lang']['nikkerani'] . "</th>";
	$view .= "<th align=center rowspan=3>NIK</th>";
	$view .= "<th align=center rowspan=3>" . $_SESSION['lang']['namakaryawan'] . "</th>";
	$view .= "<th align=center rowspan=3>Divisi</th>";
	$view .= "<th align=center colspan=30>KEGIATAN PANEN</th>";
	$view .= "<th align=center colspan=4>KEGIATAN RAWAT</th>";
	$view .= "<th align=center colspan=4>KEGIATAN BM TBS</th>";
	$view .= "<th align=center colspan=4>KEGIATAN TRAKSI</th>";
	$view .= "<th align=center colspan=4>KEGIATAN UMUM</th>";
	$view .= "<th align=center rowspan=3>TOTAL</th>";
	$view .= "</tr>";

	$view .= "<tr class=rowheader>";
	$view .= "<th align=center rowspan=2>" . $_SESSION['lang']['nospb'] . "</th>";
	$view .= "<th align=center rowspan=2>Blok</th>";
	$view .= "<th align=center rowspan=2>" . $_SESSION['lang']['luaspanen'] . "</th>";
	$view .= "<th align=center colspan=2>JJG</th>";
	$view .= "<th align=center colspan=2>BJR</th>";
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

	$view .= "<th align=center rowspan=2>HK</th><th align=center rowspan=2>Upah</th><th align=center rowspan=2>Premi</th><th align=center rowspan=2>Total Pendapatan RAWAT</th>";
	$view .= "<th align=center rowspan=2>HK</th><th align=center rowspan=2>Upah</th><th align=center rowspan=2>Premi</th><th align=center rowspan=2>Total Pendapatan BM TBS</th>";
	$view .= "<th align=center rowspan=2>HK</th><th align=center rowspan=2>Upah</th><th align=center rowspan=2>Premi</th><th align=center rowspan=2>Total Pendapatan TRAKSI</th>";
	$view .= "<th align=center rowspan=2>HK</th><th align=center rowspan=2>Upah</th><th align=center rowspan=2>Premi</th><th align=center rowspan=2>Total Pendapatan UMUM</th>";
	$view .= "</tr>";

	$view .= "<tr class=rowheader>";
	for ($i = 0; $i < 10; $i++) {
		$view .= "<th>Buah Besar</th><th>Buah Kecil</th>";
	}
	$view .= "</tr></thead><tbody>";

	#= Grand total seluruh data
	$view .= "<tr style='background-color:#c3f3fa'>";
	$view .= "<td align=center colspan=12><b>GRANDTOTAL</b></td>";
	$view .= "<td align=right>" . number_format($gtotluaspanen, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotjjgbuahbesar, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotjjgbuahkecil, 2) . "</td>";
	$view .= "<td align=right></td><td align=right></td>";
	$view .= "<td align=right>" . number_format($gtotkgbuahbesar, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotkgbuahkecil, 2) . "</td>";
	$view .= "<td align=right></td><td align=right></td>";
	$view .= "<td align=right>" . number_format($gtothkbuahbesar, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtothkbuahkecil, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotrphkbuahbesar, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotrphkbuahkecil, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtothkbuahbesarpot, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtothkbuahkecilpot, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotrphkbuahbesarpot, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotrphkbuahkecilpot, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotlbbuahbesar, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotlbbuahkecil, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotrplbbuahbesar, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotrplbbuahkecil, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotpremikehadiran, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotpremikesulitan, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalpremipanen, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotbrondolan, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotrpbrondolan, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotdendapanen, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalrppanen, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalrjhkRawat, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalrumrRawat, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalrpremiRawat, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtRAWAT, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalHKBMTBS, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalRPHKBMTBS, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalPREMIBMTBS, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtBMTBS, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalHK_TRAKSI, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalRP_TRAKSI, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalPREMI_TRAKSI, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtTRAKSI, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalHK_SDM, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalRP_SDM, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalPREMI_SDM, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtSDM, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalrppanen + $gtRAWAT + $gtBMTBS + $gtTRAKSI + $gtSDM, 2) . "</td>";
	$view .= "</tr>";

	$subtotal = array();
	$GTtotal = array();
	$no = 0;

	if (count($karyawan) > 0) {
		foreach ($karyawan as $karykey => $arrtglx) {
			ksort($arrtglx);
			$aktivitasSudahTampil = array();
			foreach ($arrtglx as $tglbkmx => $valTanggal) {
				foreach ($valTanggal as $kodeorgx => $valKodeorg) {
					foreach ($valKodeorg as $spbx => $valSpb) {
						foreach ($valSpb as $kdblok => $dummy) {
							$no++;
							$isNonPanenRow = ($kodeorgx == '__NONPANEN__');
							$tampilAktivitas = !isset($aktivitasSudahTampil[$tglbkmx]);

							$view .= "<tr class=rowcontent>";
							$view .= "<td align=center>" . $no . "</td>";

							if ($isNonPanenRow) {
								$view .= "<td align=left>-</td>";
								$view .= "<td align=left>" . tanggalnormal($tglbkmx) . "</td>";
								$view .= "<td align=left></td>";
								$view .= "<td align=left></td>";
								$view .= "<td align=left></td>";
								$view .= "<td align=left></td>";
								$view .= "<td align=left>" . $nik[$karykey] . "</td>";
								$view .= "<td align=left>" . $namakaryawan[$karykey] . "</td>";
								$view .= "<td align=left>" . ($subbagian[$karykey] == "" ? "KANTOR" : ($subbagian[$karykey] == "UMUM" ? "UMUM" : $divisi[$subbagian[$karykey]])) . "</td>";
								$view .= "<td align=left>-</td>";
								$view .= "<td align=left>-</td>";
								for ($i = 0; $i < 28; $i++) {
									$view .= "<td align=right></td>";
								}
							} else {
								$view .= "<td align=left>" . $notransaksix[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok] . "</td>";
								$view .= "<td align=left>" . tanggalnormal($tglbkmx) . "</td>";
								$view .= "<td align=left>" . $nik[$nikmandor[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok]] . "</td>";
								$view .= "<td align=left>" . $namakaryawan[$nikmandor[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok]] . "</td>";
								$view .= "<td align=left>" . $nik[$nikkerani[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok]] . "</td>";
								$view .= "<td align=left>" . $namakaryawan[$nikkerani[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok]] . "</td>";
								$view .= "<td align=left>" . $nik[$karykey] . "</td>";
								$view .= "<td align=left>" . $namakaryawan[$karykey] . "</td>";
								$view .= "<td align=left>" . ($subbagian[$karykey] == "" ? "KANTOR" : ($subbagian[$karykey] == "UMUM" ? "UMUM" : $divisi[$subbagian[$karykey]])) . "</td>";
								$view .= "<td align=left>" . ($nospb[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok] == "0" ? "-" : $nospb[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok]) . "</td>";
								$view .= "<td align=left>" . getIndukBlok($kdblok) . "</td>";
								$view .= "<td align=right>" . number_format($luaspanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($jjgbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($jjgbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($bjrpanenbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($bjrpanenkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($kgbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($kgbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($basisbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($basisbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($hkbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($hkbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($rphkbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($rphkbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($hkbuahbesarpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($hkbuahkecilpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($rphkbuahbesarpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($rphkbuahkecilpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($lbbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($lbbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($rplbbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($rplbbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($premikehadiranpanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($premikesulitanpanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($totalpremipanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($brondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($rpbrondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($dendapanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
								$view .= "<td align=right>" . number_format($totalrppanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";

								#= Subtotal per karyawan dan tanggal - khusus data panen
								$subtotal[$karykey][$tglbkmx]['luas'] += $luaspanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['jjgBesar'] += $jjgbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['jjgKecil'] += $jjgbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['buahBesar'] += $kgbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['buahKecil'] += $kgbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['hkBesar'] += $hkbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['hkKecil'] += $hkbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['rpBesar'] += $rphkbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['rpKecil'] += $rphkbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['hkpotBesar'] += $hkbuahbesarpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['hkpotKecil'] += $hkbuahkecilpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['potBesar'] += $rphkbuahbesarpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['potKecil'] += $rphkbuahkecilpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['lbBesar'] += $lbbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['lbKecil'] += $lbbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['premiBesar'] += $rplbbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['premiKecil'] += $rplbbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['premikehadiran'] += $premikehadiranpanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['premikesulitan'] += $premikesulitanpanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['totalpremi'] += $totalpremipanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['brondolan'] += $brondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['rpBrondolan'] += $rpbrondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['denda'] += $dendapanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$subtotal[$karykey][$tglbkmx]['totalrppanen'] += $totalrppanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];

								#= Grand total per karyawan - khusus data panen
								$GTtotal[$karykey]['luas'] += $luaspanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['jjgBesar'] += $jjgbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['jjgKecil'] += $jjgbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['buahBesar'] += $kgbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['buahKecil'] += $kgbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['hkBesar'] += $hkbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['hkKecil'] += $hkbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['rpBesar'] += $rphkbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['rpKecil'] += $rphkbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['hkpotBesar'] += $hkbuahbesarpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['hkpotKecil'] += $hkbuahkecilpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['potBesar'] += $rphkbuahbesarpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['potKecil'] += $rphkbuahkecilpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['lbBesar'] += $lbbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['lbKecil'] += $lbbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['premiBesar'] += $rplbbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['premiKecil'] += $rplbbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['premikehadiran'] += $premikehadiranpanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['premikesulitan'] += $premikesulitanpanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['totalpremi'] += $totalpremipanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['brondolan'] += $brondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['rpBrondolan'] += $rpbrondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['denda'] += $dendapanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
								$GTtotal[$karykey]['totalrppanen'] += $totalrppanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							}

							#= 16 kolom kegiatan non panen + 1 kolom TOTAL
							if ($tampilAktivitas) {
								$view .= "<td align=right>" . number_format($hk_rawat[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($umr_rawat[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($premi_rawat[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($total_pertgl_rawat[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($hk_bmtbs[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($umr_bmtbs[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($premi_bmtbs[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($total_pertgl_bmtbs[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($hk_traksi[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($umr_traksi[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($premi_traksi[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($total_pertgl_traksi[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($hk_sdm[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($umr_sdm[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($premi_sdm[$karykey][$tglbkmx], 2) . "</td>";
								$view .= "<td align=right>" . number_format($total_pertgl_sdm[$karykey][$tglbkmx], 2) . "</td>";
								$totalDetail = ($isNonPanenRow ? 0 : $totalrppanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok])
									+ $total_pertgl_rawat[$karykey][$tglbkmx]
									+ $total_pertgl_bmtbs[$karykey][$tglbkmx]
									+ $total_pertgl_traksi[$karykey][$tglbkmx]
									+ $total_pertgl_sdm[$karykey][$tglbkmx];
								$aktivitasSudahTampil[$tglbkmx] = true;
							} else {
								for ($i = 0; $i < 16; $i++) {
									$view .= "<td align=right></td>";
								}
								$totalDetail = $isNonPanenRow ? 0 : $totalrppanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							}
							$view .= "<td align=right>" . number_format($totalDetail, 2) . "</td>";
							$view .= "</tr>";
						}
					}
				}

				#= Sub total per tanggal
				$view .= "<tr class=rowcontent style='background-color:orange'>";
				$view .= "<td align=center colspan=12><b>SUB TOTAL</b></td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['luas'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['jjgBesar'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['jjgKecil'], 2) . "</td>";
				$view .= "<td align=right></td><td align=right></td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['buahBesar'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['buahKecil'], 2) . "</td>";
				$view .= "<td align=right></td><td align=right></td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['hkBesar'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['hkKecil'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['rpBesar'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['rpKecil'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['hkpotBesar'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['hkpotKecil'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['potBesar'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['potKecil'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['lbBesar'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['lbKecil'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['premiBesar'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['premiKecil'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['premikehadiran'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['premikesulitan'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['totalpremi'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['brondolan'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['rpBrondolan'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['denda'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($subtotal[$karykey][$tglbkmx]['totalrppanen'], 2) . "</td>";
				$view .= "<td align=right>" . number_format($hk_rawat[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($umr_rawat[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($premi_rawat[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($total_pertgl_rawat[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($hk_bmtbs[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($umr_bmtbs[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($premi_bmtbs[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($total_pertgl_bmtbs[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($hk_traksi[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($umr_traksi[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($premi_traksi[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($total_pertgl_traksi[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($hk_sdm[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($umr_sdm[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($premi_sdm[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format($total_pertgl_sdm[$karykey][$tglbkmx], 2) . "</td>";
				$view .= "<td align=right>" . number_format(
					$subtotal[$karykey][$tglbkmx]['totalrppanen']
						+ $total_pertgl_rawat[$karykey][$tglbkmx]
						+ $total_pertgl_bmtbs[$karykey][$tglbkmx]
						+ $total_pertgl_traksi[$karykey][$tglbkmx]
						+ $total_pertgl_sdm[$karykey][$tglbkmx],
					2
				) . "</td>";
				$view .= "</tr>";
			}

			#= Grand total per karyawan
			$view .= "<tr class=rowcontent style='background-color:cyan'>";
			$view .= "<td align=center colspan=12><b>GRAND TOTAL ( " . $namakaryawan[$karykey] . " )</b></td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['luas'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['jjgBesar'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['jjgKecil'], 2) . "</td>";
			$view .= "<td align=right></td><td align=right></td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['buahBesar'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['buahKecil'], 2) . "</td>";
			$view .= "<td align=right></td><td align=right></td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['hkBesar'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['hkKecil'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['rpBesar'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['rpKecil'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['hkpotBesar'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['hkpotKecil'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['potBesar'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['potKecil'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['lbBesar'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['lbKecil'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['premiBesar'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['premiKecil'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['premikehadiran'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['premikesulitan'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['totalpremi'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['brondolan'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['rpBrondolan'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['denda'], 2) . "</td>";
			$view .= "<td align=right>" . number_format($GTtotal[$karykey]['totalrppanen'], 2) . "</td>";

			$view .= "<td align=right>" . number_format($jhkRawat[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($upahRawat[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($premiRawat[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($totalRAWAT[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($hkBMTBS[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($rphkBMTBS[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($premiBMTBS[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($totalBMTBS[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($HK_TRAKSI[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($RP_TRAKSI[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($PREMI_TRAKSI[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($totalTRAKSI[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($HK_SDM[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($RP_SDM[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($PREMI_SDM[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format($totalSDM[$karykey], 2) . "</td>";
			$view .= "<td align=right>" . number_format(
				$GTtotal[$karykey]['totalrppanen'] +
					$totalRAWAT[$karykey] +
					$totalBMTBS[$karykey] +
					$totalTRAKSI[$karykey] +
					$totalSDM[$karykey],
				2
			) . "</td>";
			$view .= "</tr>";
		}
	} else {
		$view .= "<tr class=rowcontent>";
		$view .= "<td align=center colspan=57><b style=color:red>Tidak ada data</b></td>";
		$view .= "</tr>";
	}

	$view .= "</tbody></table></div>";
}

switch ($proses) {
	case "getDivisi":
		$optDiv = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$query = "select * from " . $dbname . ".organisasi where 1=1 and induk='" . $param['unit'] . "' and tipe in ('AFDELING','BIBITAN')";
		$result = fetchData($query);
		foreach ($result as $val) {
			$optDiv .= "<option value=" . $val['kodeorganisasi'] . ">" . $val['kodeorganisasi'] . " - " . $val['namaorganisasi'] . "</option>";
		}
		echo $optDiv;
		break;

	case "preview":
		if ($param['tgl'] == "" || $param['tglx'] == "") {
			exit("Warning : Tanggal wajib di Isi !");
		}
		if ($param['unit'] == "") {
			exit('Warning: Unit usaha harus dipilih');
		}
		echo $view;
		break;

	case "excel":
		$nop = "Laporan Upah Dan Premi Panen.xls";
		$xls = new HtmlExcel();
		$xls->setCss($css);
		$xls->addSheet('Laporan Upah dan Premi Panen', $view);
		$xls->headers($nop);
		echo $xls->buildFile();
		break;
}
