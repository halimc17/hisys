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


if ($proses == "preview" || $proses == "excel") {
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
		$wherePnn .= " and kodeorg like '" . $param['div'] . "%' ";
		$whereBKM .= " and kodeorg like '" . $param['div'] . "%' ";
		$whereBMTBS .= " and a.divisi like '" . $param['div'] . "%' ";
		$whereTRAKSI .= " and a.kodeorg like '" . $param['div'] . "%'";
		$whereSDM .= " and a.kodeorg like '" . $param['div'] . "%'";
	} else {
		$wherePnn .= " and kodeorg like '" . $param['unit'] . "%' ";
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


	$str = "select * from " . $dbname . ".kebun_rekaphancakpanen where tanggal between '" . $tglAwal . "' and '" . $tglAkhir . "' " . $wherePnn . "  order by tanggal asc";
	$res = $owlPDO->query($str) or die(" Gagal: " . $owlPDO->errorInfo()[2]);
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {
		$Hektarpanenx[$bar['tanggal']][$bar['kodeorg']][$bar['nik']] += $bar['hapanen'];
	}

	$query = "select a.*, b.namakaryawan from " . $dbname . ".kebun_3premipemanen a
left join datakaryawan b on a.karyawanid=b.karyawanid
where 1=1 " . $whereKp . " order by a.tanggalpanen asc, b.namakaryawan asc, a.blok asc";
	if (count(fetchData($query)) == 0) {
		$query = "select a.notransaksi, a.kodeorg, a.divisi, a.periode, a.tanggalpanen, a.nospb, a.mandor, a.kerani, a.karyawanid, a.tahuntanam, a.indukblok as blok, a.hapanen, a.jjg as jjgbuahbesar, a.bjr as bjrbesar, a.kg as kgbuahbesar, a.basis as basisbuahbesar, a.hk as hkbuahbesar, a.upah as rphkbuahbesar, a.pothk as hkbuahbesarpot, a.potupah as rphkbuahbesarpot, a.lbbasis as lbbuahbesar, a.premilb as rplbbuahbesar, a.brondol as brondolan, a.upahbro as rpbrondolan, a.denda as dendapanen, a.totalupah as total, a.jurnal, a.posting, a.postingdate, b.namakaryawan from " . $dbname . ".kebun_3premipemanen_v2 a
	left join datakaryawan b on a.karyawanid=b.karyawanid
	where 1=1 " . $whereKp . " order by a.tanggalpanen asc, b.namakaryawan asc, a.indukblok asc";
	}
	// exit('warning: '.$query);
	$result = fetchData($query);
	foreach ($result as $val) {
		if (!isset($datapanentgl[$val['tanggalpanen']][$val['karyawanid']])) {
			$datapanentgl[$val['tanggalpanen']][$val['karyawanid']] = 0;
		}
		if (!isset($datapanentglxz[$val['tanggalpanen']][$val['karyawanid']][$val['blok']])) {
			$datapanentglxz[$val['tanggalpanen']][$val['karyawanid']][$val['blok']] = 0;
		}
		$datapanentgl[$val['tanggalpanen']][$val['karyawanid']] += ($val['jjgbuahbesar'] + $val['jjgbuahkecil']);
		$datapanentglxz[$val['tanggalpanen']][$val['karyawanid']][$val['blok']] += ($val['jjgbuahbesar'] + $val['jjgbuahkecil']);

		$karyawan[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['karyawanid'];
		$notransaksix[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['notransaksi'];
		$nikmandor[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['mandor'];
		$nikkerani[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['kerani'];
		$divisix[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['divisi'];
		$blokx[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['blok'];
		$nospb[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['nospb'];
		$luaspanen[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['hapanen'];

		$jjgbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['jjgbuahbesar'];
		$jjgbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['jjgbuahkecil'];
		$bjrpanenbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['bjrbesar'];
		$bjrpanenkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['bjrkecil'];

		$kgbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['kgbuahbesar'];
		$kgbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['kgbuahkecil'];

		$basisbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['basisbuahbesar'];
		$basisbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['basisbuahkecil'];

		$hkbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['hkbuahbesar'];
		$hkbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['hkbuahkecil'];

		$rphkbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['rphkbuahbesar'];
		$rphkbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['rphkbuahkecil'];

		$hkbuahbesarpot[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['hkbuahbesarpot'];
		$hkbuahkecilpot[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['hkbuahkecilpot'];

		$rphkbuahbesarpot[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['rphkbuahbesarpot'];
		$rphkbuahkecilpot[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['rphkbuahkecilpot'];

		$lbbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['lbbuahbesar'];
		$lbbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['lbbuahkecil'];

		$rplbbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['rplbbuahbesar'];
		$rplbbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['rplbbuahkecil'];

		$brondolan[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['brondolan'];
		$rpbrondolan[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['rpbrondolan'];

		$dendapanen[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['dendapanen'];
		$totalrppanen[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']] = $val['total'];

		// GRAND TOTAL
		$gtotluaspanen 		+= $luaspanen[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotjjgbuahbesar 	+= $jjgbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotjjgbuahkecil 	+= $jjgbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotbjrpanen		+= $bjrpanen[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotkgbuahbesar	+= $kgbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotkgbuahkecil	+= $kgbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotbasisbuahbesar	+= $basisbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotbasisbuahkecil	+= $basisbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtothkbuahbesar	+= $hkbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtothkbuahkecil	+= $hkbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotrphkbuahbesar	+= $rphkbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotrphkbuahkecil	+= $rphkbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtothkbuahbesarpot	+= $hkbuahbesarpot[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtothkbuahkecilpot	+= $hkbuahkecilpot[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotrphkbuahbesarpot += $rphkbuahbesarpot[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotrphkbuahkecilpot += $rphkbuahkecilpot[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotlbbuahbesar	+= $lbbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotlbbuahkecil	+= $lbbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotrplbbuahbesar	+= $rplbbuahbesar[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotrplbbuahkecil	+= $rplbbuahkecil[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotbrondolan		+= $brondolan[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotrpbrondolan	+= $rpbrondolan[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtotdendapanen		+= $dendapanen[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
		$gtottotalrppanen	+= $totalrppanen[$val['karyawanid']][$val['tanggalpanen']][$val['kodeorg']][$val['nospb']][$val['blok']];
	}



	## Ambil Kegiatan Rawat nya 
	$query = "select a.*, b.namakaryawan from " . $dbname . ".kebun_kehadiran_vw a
	left join datakaryawan b on a.karyawanid=b.karyawanid
	where 1=1 " . $whereBKM . " order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
	$result = fetchData($query);
	foreach ($result as $val) {
		$jhkRawat[$val['karyawanid']] += $val['jhk'];
		$upahRawat[$val['karyawanid']] += $val['umr'];
		$premiRawat[$val['karyawanid']] += $val['insentif'];

		$totalRAWAT[$val['karyawanid']] += $val['umr'] + $val['insentif'];

		$gtottotalrjhkRawat		+= $val['jhk'];
		$gtottotalrumrRawat		+= $val['umr'];
		$gtottotalrpremiRawat	+= $val['insentif'];

		$gtRAWAT += $val['umr'] + $val['insentif'];
	}


	## Ambil Kegiatan bm tbs nya 
	$query = "select a.*, b.namakaryawan from " . $dbname . ".kebun_3premibmtbs a
	left join datakaryawan b on a.karyawanid=b.karyawanid
	where 1=1 " . $whereBMTBS . " order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
	$result = fetchData($query);
	foreach ($result as $val) {
		$hkBMTBS[$val['karyawanid']] += $val['hk'];
		$rphkBMTBS[$val['karyawanid']] += $val['rphk'];
		$premiBMTBS[$val['karyawanid']] += $val['rppremi'];

		$totalBMTBS[$val['karyawanid']] +=  $val['rphk'] + $val['rppremi'];

		$gtottotalHKBMTBS	+= $val['hk'];
		$gtottotalRPHKBMTBS	+= $val['rphk'];
		$gtottotalPREMIBMTBS	+= $val['rppremi'];

		$gtBMTBS += $val['rphk'] + $val['rppremi'];
	}


	## Ambil kegiatan traksi 
	$query = "select a.*, b.namakaryawan from " . $dbname . ".vhc_runhk_vw a
	left join datakaryawan b on a.idkaryawan=b.karyawanid
	where 1=1 " . $whereTRAKSI . " order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
	$result = fetchData($query);
	foreach ($result as $val) {
		$HK_TRAKSI[$val['idkaryawan']] += $val['hk'];
		$RP_TRAKSI[$val['idkaryawan']] += $val['upah'];
		$PREMI_TRAKSI[$val['idkaryawan']] += $val['premi'];

		$totalTRAKSI[$val['idkaryawan']] += $val['upah'] + $val['premi'];

		$gtottotalHK_TRAKSI	+= $val['hk'];
		$gtottotalRP_TRAKSI	+= $val['upah'];
		$gtottotalPREMI_TRAKSI	+= $val['premi'];

		$gtTRAKSI += $val['upah'] + $val['premi'];
	}

	## Kegiatan Umum
	$query = "select a.*, b.namakaryawan from " . $dbname . ".sdm_absensidt a
	left join datakaryawan b on a.karyawanid=b.karyawanid
	where 1=1 " . $whereSDM . " order by a.tanggal asc, b.namakaryawan asc, a.kodeorg asc";
	$result = fetchData($query);
	foreach ($result as $val) {
		$HK_SDM[$val['karyawanid']] += $val['hk'];
		$RP_SDM[$val['karyawanid']] += $val['umr'];
		$PREMI_SDM[$val['karyawanid']] += $val['premi'];

		$totalSDM += $val['umr'] + $val['premi'];

		$gtottotalHK_SDM	+= $val['hk'];
		$gtottotalRP_SDM	+= $val['umr'];
		$gtottotalPREMI_SDM	+= $val['premi'];

		$gtSDM += $val['umr'] + $val['premi'];
	}


	#= Awal Tabel

	if ($proses == "excel") {
		$view .= "<table class=sortable cellspacing=1 border=1>";
	} else {
		$view .= "<div> <table  class=sortable cellspacing=1 border=0 width=50%>";
	}

	@$nmkar      = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
	@$nikkar     = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
	$nmorg       = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok');

	$view .= "<thead><tr class=rowheader><td align='center' colspan='5'>HA Panen Jajang 0 Tapi Memiliki Janjang di Blok Lain</td></tr>
	<tr class=rowheader>";
	$view .= "<td align=center width=100px>Tanggal Panen</td>
			  <td align=center width=120px>NIK</td>
			  <td align=center width=120px>Nama Karyawan</td>
			  <td align=center width=120px>BLOK</td>
			  <td align=center width=75px>HA</td>";
	$view .= "</tr>";
	$view .= "</thead>";

	foreach ($Hektarpanenx as $tglpnn => $key) {
		foreach ($key as $kdblok => $key2) {
			foreach ($key2 as $kary => $val) {
				if ($datapanentgl[$tglpnn][$kary] > 0 and !isset($datapanentglxz[$tglpnn][$kary][$kdblok])) {
					$view .= "<tr class=rowcontent>";
					$view .= "<td align=center>" . tanggalnormal($tglpnn) . "</td>";
					$view .= "<td align=center>" . $nikkar[$kary] . "</td>";
					$view .= "<td align=center>" . $nmkar[$kary] . "</td>";
					$view .= "<td align=center>" . $nmorg[$kdblok] . "</td>";
					$view .= "<td align=center>" . @number_format($val, 2) . "</td>";
					$view .= "</tr>";
				}
			}
		}
	}
	$view .= "</table></div>";

	if ($proses == "excel") {
		$view .= "<table class=sortable cellspacing=1 border=1>";
	} else {
		$view .= "<div> <table class=sortable cellspacing=1 cellpadding = 5 border=0 width=100%>";
	}
	#= Tabel Header
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
	$view .= "<th align=center colspan=27>KEGIATAN PANEN</th>";
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
	$view .= "<th align=center rowspan=2>" . $_SESSION['lang']['brondol'] . "</th>";
	$view .= "<th align=center rowspan=2>Rp " . $_SESSION['lang']['brondol'] . "</th>";
	$view .= "<th align=center rowspan=2>" . $_SESSION['lang']['dendapanen'] . "</th>";
	$view .= "<th align=center rowspan=2>Total Pendapatan PANEN</th>";

	$view .= "<th align=center rowspan=2>HK</th>";
	$view .= "<th align=center rowspan=2>Upah</th>";
	$view .= "<th align=center rowspan=2>Premi</th>";

	$view .= "<th align=center rowspan=2>Total Pendapatan RAWAT</th>";

	$view .= "<th align=center rowspan=2>HK</th>";
	$view .= "<th align=center rowspan=2>Upah</th>";
	$view .= "<th align=center rowspan=2>Premi</th>";

	$view .= "<th align=center rowspan=2>Total Pendapatan BM TBS</th>";

	$view .= "<th align=center rowspan=2>HK</th>";
	$view .= "<th align=center rowspan=2>Upah</th>";
	$view .= "<th align=center rowspan=2>Premi</th>";

	$view .= "<th align=center rowspan=2>Total Pendapatan TRAKSI</th>";

	$view .= "<th align=center rowspan=2>HK</th>";
	$view .= "<th align=center rowspan=2>Upah</th>";
	$view .= "<th align=center rowspan=2>Premi</th>";

	$view .= "<th align=center rowspan=2>Total Pendapatan UMUM</th>";

	$view .= "</tr>";

	$view .= "<tr>";
	$view .= "<th>Buah Besar</th>";
	$view .= "<th>Buah Kecil</th>";

	$view .= "<th>Buah Besar</th>";
	$view .= "<th>Buah Kecil</th>";

	$view .= "<th>Buah Besar</th>";
	$view .= "<th>Buah Kecil</th>";

	$view .= "<th>Buah Besar</th>";
	$view .= "<th>Buah Kecil</th>";

	$view .= "<th>Buah Besar</th>";
	$view .= "<th>Buah Kecil</th>";

	$view .= "<th>Buah Besar</th>";
	$view .= "<th>Buah Kecil</th>";

	$view .= "<th>Buah Besar</th>";
	$view .= "<th>Buah Kecil</th>";

	$view .= "<th>Buah Besar</th>";
	$view .= "<th>Buah Kecil</th>";

	$view .= "<th>Buah Besar</th>";
	$view .= "<th>Buah Kecil</th>";

	$view .= "<th>Buah Besar</th>";
	$view .= "<th>Buah Kecil</th>";
	$view .= "</tr>";
	$view .= "</thead>";

	#= Tabel Body
	$view .= "<tbody>";

	#= Grand Total 
	$view .= "<tr style='background-color:#c3f3fa'>";
	$view .= "<td align=center colspan=12><b>GRANDTOTAL</b></td>";
	$view .= "<td align=right>" . number_format($gtotluaspanen, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotjjgbuahbesar, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotjjgbuahkecil, 2) . "</td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right>" . number_format($gtotkgbuahbesar, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotkgbuahkecil, 2) . "</td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
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
	$view .= "<td align=right>" . number_format($gtotbrondolan, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotrpbrondolan, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtotdendapanen, 2) . "</td>";
	$view .= "<td align=right>" . number_format($gtottotalrppanen, 2) . "</td>";

	// $view .= "<td align=right>".number_format($gtottotalrjhkRawat,2)."</td>";
	// $view .= "<td align=right>".number_format($gtottotalrumrRawat,2)."</td>";
	// $view .= "<td align=right>".number_format($gtottotalrpremiRawat,2)."</td>";

	// $view .= "<td align=right>".number_format($gtRAWAT,2)."</td>";

	// $view .= "<td align=right>".number_format($gtottotalHKBMTBS,2)."</td>";
	// $view .= "<td align=right>".number_format($gtottotalRPHKBMTBS,2)."</td>";
	// $view .= "<td align=right>".number_format($gtottotalPREMIBMTBS,2)."</td>";

	// $view .= "<td align=right>".number_format($gtBMTBS,2)."</td>";

	// $view .= "<td align=right>".number_format($gtottotalHK_TRAKSI,2)."</td>";
	// $view .= "<td align=right>".number_format($gtottotalRP_TRAKSI,2)."</td>";
	// $view .= "<td align=right>".number_format($gtottotalPREMI_TRAKSI,2)."</td>";

	// $view .= "<td align=right>".number_format($gtTRAKSI,2)."</td>";

	// $view .= "<td align=right>".number_format($gtottotalHK_SDM,2)."</td>";
	// $view .= "<td align=right>".number_format($gtottotalRP_SDM,2)."</td>";
	// $view .= "<td align=right>".number_format($gtottotalPREMI_SDM,2)."</td>";

	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";


	// $view .= "<td align=right>".number_format($gtSDM,2)."</td>";

	// $view .= "<td align=right>".number_format($gtottotalrppanen+$gtRAWAT+$gtBMTBS+$gtTRAKSI+$gtSDM,2)."</td>";
	$view .= "<td align=right></td>";
	$view .= "<td align=right></td>";

	$view .= "</tr>";
	#= Akhir Grand Total

	$subtotal = array();
	$GTtotal = array();

	$tampung = array();
	$no = 0;
	if (count($karyawan) > 0) {
		foreach ($karyawan as $karykey => $arrtglx) {
			foreach ($arrtglx as $tglbkmx => $val) {
				foreach ($val as $kodeorgx => $valx) {
					foreach ($valx as $spbx => $spbno) {
						foreach ($spbno as $kdblok => $blk) {
							$no += 1;
							$view .= "<tr class=rowcontent>";
							$view .= "<td align=center>" . $no . "</td>";
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
							$view .= "<td align=left>" . $luaspanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok] . "</td>";
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
							$view .= "<td align=right>" . number_format($brondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
							$view .= "<td align=right>" . number_format($rpbrondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
							$view .= "<td align=right>" . number_format($dendapanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";
							$view .= "<td align=right>" . number_format($totalrppanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok], 2) . "</td>";

							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";

							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";

							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";

							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";
							$view .= "<td align=left></td>";

							$view .= "<td align=left></td>";

							$view .= "</tr>";

							## Panen
							$subtotal[$karykey][$tglbkmx]['luas'] +=  $luaspanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['jjgBesar'] +=  $jjgbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['jjgKecil'] +=  $jjgbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['buahBesar'] +=  $kgbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['buahKecil'] +=  $kgbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['hkBesar'] +=  $hkbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['hkKecil'] +=  $hkbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['rpBesar'] +=  $rphkbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['rpKecil'] +=  $rphkbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['hkpotBesar'] +=  $hkbuahbesarpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['hkpotKecil'] +=  $hkbuahkecilpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['potBesar'] +=  $rphkbuahbesarpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['potKecil'] +=  $rphkbuahkecilpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['lbBesar'] +=  $lbbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['lbKecil'] +=  $lbbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['premiBesar'] +=  $rplbbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['premiKecil'] +=  $rplbbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['brondolan'] +=  $brondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['rpBrondolan'] +=  $rpbrondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['denda'] +=  $dendapanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$subtotal[$karykey][$tglbkmx]['totalrppanen'] +=  $totalrppanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];


							$GTtotal[$karykey]['luas'] +=  $luaspanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['jjgBesar'] +=  $jjgbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['jjgKecil'] +=  $jjgbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['buahBesar'] +=  $kgbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['buahKecil'] +=  $kgbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['hkBesar'] +=  $hkbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['hkKecil'] +=  $hkbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['rpBesar'] +=  $rphkbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['rpKecil'] +=  $rphkbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['hkpotBesar'] +=  $hkbuahbesarpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['hkpotKecil'] +=  $hkbuahkecilpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['potBesar'] +=  $rphkbuahbesarpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['potKecil'] +=  $rphkbuahkecilpot[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['lbBesar'] +=  $lbbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['lbKecil'] +=  $lbbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['premiBesar'] +=  $rplbbuahbesar[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['premiKecil'] +=  $rplbbuahkecil[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['brondolan'] +=  $brondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['rpBrondolan'] +=  $rpbrondolan[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['denda'] +=  $dendapanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
							$GTtotal[$karykey]['totalrppanen'] +=  $totalrppanen[$karykey][$tglbkmx][$kodeorgx][$spbx][$kdblok];
						}
					}
				}
				$view .= "<tr class=rowcontent style=background-color:orange>";
				$view .= "<td align=center colspan = 12><b>SUB TOTAL</b></td>";
				$view .= "<td align=center>" . $subtotal[$karykey][$tglbkmx]['luas'] . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['jjgBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['jjgKecil'], 2) . "</td>";
				$view .= "<td align=center ></td>";
				$view .= "<td align=center ></td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['buahBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['buahKecil'], 2) . "</td>";
				$view .= "<td align=center ></td>";
				$view .= "<td align=center ></td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['hkBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['hkKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['rpBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['rpKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['hkpotBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['hkpotKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['potBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['potKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['lbBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['lbKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['premiBesar'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['premiKecil'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['brondolan'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['rpBrondolan'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['dendapanen'], 2) . "</td>";
				$view .= "<td align=center>" . number_format($subtotal[$karykey][$tglbkmx]['totalrppanen'], 2) . "</td>";

				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";

				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";

				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";

				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";
				$view .= "<td align=center></td>";

				$view .= "<td align=center></td>";
				$view .= "</tr>";
			}
			$view .= "<tr class=rowcontent style=background-color:cyan>";
			$view .= "<td align=center colspan = 12><b>GRAND TOTAL ( " . $namakaryawan[$karykey] . " )</b></td>";
			$view .= "<td align=center>" . $GTtotal[$karykey]['luas'] . "</td>";
			$view .= "<td align=center>" . number_format($GTtotal[$karykey]['jjgBesar'], 2) . "</td>";
			$view .= "<td align=center>" . number_format($GTtotal[$karykey]['jjgKecil'], 2) . "</td>";
			$view .= "<td align=center ></td>";
			$view .= "<td align=center ></td>";
			$view .= "<td align=center>" . number_format($GTtotal[$karykey]['buahBesar'], 2) . "</td>";
			$view .= "<td align=center>" . number_format($GTtotal[$karykey]['buahKecil'], 2) . "</td>";
			$view .= "<td align=center ></td>";
			$view .= "<td align=center ></td>";
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
			$view .= "<td align=center>" . number_format($GTtotal[$karykey]['brondolan'], 2) . "</td>";
			$view .= "<td align=center>" . number_format($GTtotal[$karykey]['rpBrondolan'], 2) . "</td>";
			$view .= "<td align=center>" . number_format($GTtotal[$karykey]['dendapanen'], 2) . "</td>";
			$view .= "<td align=center>" . number_format($GTtotal[$karykey]['totalrppanen'], 2) . "</td>";

			$view .= "<td align=center>" . number_format($jhkRawat[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($upahRawat[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($premiRawat[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($totalRAWAT[$karykey], 2) . "</td>";

			$view .= "<td align=center>" . number_format($hkBMTBS[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($rphkBMTBS[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($premiBMTBS[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($totalBMTBS[$karykey], 2) . "</td>";

			$view .= "<td align=center>" . number_format($HK_TRAKSI[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($RP_TRAKSI[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($PREMI_TRAKSI[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($totalTRAKSI[$karykey], 2) . "</td>";

			$view .= "<td align=center>" . number_format($HK_SDM[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($RP_SDM[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($PREMI_SDM[$karykey], 2) . "</td>";
			$view .= "<td align=center>" . number_format($totalSDM[$karykey], 2) . "</td>";

			$view .= "<td align=center>" . number_format($GTtotal[$karykey]['totalrppanen'] + $totalRAWAT[$karykey] + $totalBMTBS[$karykey] + $totalTRAKSI[$karykey] + $totalSDM[$karykey], 2) . "</td>";
			$view .= "</tr>";
		}
	} else {
		$view .= "<tr class=rowcontent>";
		$view .= "<td align=center colspan=35><b style=color:red>Tidak ada data</b></td>";
		$view .= "</tr>";
	}

	$view .= "</tbody>";

	$view .= "</table></div>";
}
#= Akhir Tabel
// echo "<pre>";
// print_r($karyawan);

switch ($proses) {
	case "getDivisi":

		$optDiv = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
		$query = "select * from " . $dbname . ".organisasi  where 1=1 and induk='" . $param['unit'] . "' and tipe in ('AFDELING','BIBITAN')";
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
