<?php
error_reporting(0);
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$proses = checkPostGet('proses', '');
$tipe = checkPostGet('tipe', '');
$jenis = checkPostGet('jenis', '');
$param = $_POST;
if (count($param) == 0) {
	$param = $_GET;
}

if ($_SESSION['language'] == 'EN') {
	$optKegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan1');
} else {
	$optKegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');
}
$jab = getPostingJabatan('rawatkebun');

$optSatKegiatan = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan');
$optNamaKary = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optNIKary = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$optNamaBrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang');
// $optGudang=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

$optkodeorg = makeOption($dbname, 'kebun_aktifitas', 'notransaksi,kodeorg', "notransaksi='" . $param['notransaksi'] . "'");

$str = "select * from " . $dbname . ".organisasi where kodeorganisasi like '" . $optkodeorg[$param['notransaksi']] . "%'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optGudang[$bar['kodeorganisasi']] = $bar['namaorganisasi'];
}

$str = "select * from " . $dbname . ".organisasi where indukblok like '" . $optkodeorg[$param['notransaksi']] . "%'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optGudang[$bar['indukblok']] = $bar['namaindukblok'];
}

$str = "select * from " . $dbname . ".project where kodeorg like '" . $optkodeorg[$param['notransaksi']] . "%'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$optGudang[$bar['kode']] = $bar['nama'];
}

/** Report Prep **/
$cols = array();

# Prestasi
//$col1 = 'nik,kodekegiatan,kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
$col1 = 'tanggal,kodekegiatan,a.kodeorg,hasilkerja,jumlahhk,upahkerja,upahpremi,umr';
$cols[] = explode(',', $col1);
//$query = selectQuery($dbname,'kebun_prestasi',$col1,
//    "notransaksi='".$param['notransaksi']."'");
$query = "select " . $col1 . " from " . $dbname . ".kebun_prestasi a left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi where a.notransaksi='" . $param['notransaksi'] . "'";
//exit("Error".$query);
$data[] = fetchData($query);
$align[] = explode(",", "L,L,L,R,R,R,R,R");
$length[] = explode(",", "10,10,15,10,10,15,15,15");

# Kehadiran
$col2 = 'nik,absensi,jhk,umr,insentif';
$cols[] = explode(',', $col2);
$query = selectQuery(
	$dbname,
	'kebun_kehadiran',
	$col2,
	"notransaksi='" . $param['notransaksi'] . "'"
);
$data[] = fetchData($query);
$align[] = explode(",", "L,L,R,R,R");
$length[] = explode(",", "20,20,20,20,20");

# Pakai Material
$col3 = 'kodeorg,kodebarang,kwantitas,kwantitasha,hargasatuan';
$cols[] = explode(',', $col3);
$query = selectQuery(
	$dbname,
	'kebun_pakaimaterial',
	$col3,
	"notransaksi='" . $param['notransaksi'] . "'"
);
$data[] = fetchData($query);
$align[] = explode(",", "L,L,R,R,R");
$length[] = explode(",", "20,20,20,20,20");

//getNamakaryawan
$sDtKaryawn = "select karyawanid,namakaryawan from " . $dbname . ".datakaryawan order by namakaryawan asc";
$rData = fetchData($sDtKaryawn);
foreach ($rData as $brKary => $rNamakaryawan) {
	$RnamaKary[$rNamakaryawan['karyawanid']] = $rNamakaryawan['namakaryawan'];
}

switch ($tipe) {
	case "LC":
		$title = strtoupper("Land Clearing");
		break;
	case "BBT":
		$title = strtoupper($_SESSION['lang']['pembibitan']);
		break;
	case "TBM":
		$title = strtoupper("UPKEEP-" . $_SESSION['lang']['tbm']);
		break;
	case "TM":
		$title = strtoupper("UPKEEP-" . $_SESSION['lang']['tm']);
		break;
	case "PNN":
		$title = strtoupper($_SESSION['lang']['panen']);
		break;
	case "TB":
		$title = strtoupper("UPKEEP-" . $_SESSION['lang']['tbm']);
		break;
	case "BKM":
		$title = strtoupper("BUKU KEGIATAN MANDOR");
		break;
	default:
		echo "Error : Atribut not Defined";
		exit;
		break;
}
$titleDetail = array($_SESSION['lang']['prestasi'], $_SESSION['lang']['material']);

/** Output Format **/

$theme = $_SESSION['theme'];
if ($theme == 'skyblue' || $theme == '') {
	$men = 'menu.css';
	$gen = 'generic.css';
} else if ($theme == 'red') {
	$men = 'menuRed.css';
	$gen = 'genericRed.css';
} else {
	$men = 'menuGray.css';
	$gen = 'genericGray.css';
}
$tab = '';

$tab .= "<link rel=stylesheet type=text/css href=style/" . $gen . ">";
$border = "border=0 cellspacing=1";

$opttgl = makeOption($dbname, 'kebun_aktifitas', 'notransaksi,tanggal', "notransaksi='" . $param['notransaksi'] . "'");
$tab .= "<table cellpadding=5 " . $border . " class=sortable>";
$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['kodeorganisasi'] . "</td><td> :</td><td> " . getNamaOrg($optkodeorg[$param['notransaksi']]) . "</td></tr>";
$optbkm = makeOption($dbname, 'kebun_aktifitas', 'notransaksi,nobkm', "notransaksi='" . $param['notransaksi'] . "'");
$tab .= "<tr class=rowcontent><td>No BKM</td><td> :</td><td><b> " . @$optbkm[$param['notransaksi']] . "</b></td></tr>";
$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['notransaksi'] . "</td><td> :</td><td><b> " . $param['notransaksi'] . "</b></td></tr>";
$tab .= "<tr class=rowcontent><td>" . $_SESSION['lang']['tanggal'] . "</td><td> :</td><td> " . tanggalnormal($opttgl[$param['notransaksi']]) . "</td></tr>";
$tab .= "</table>";

$verifbkm = makeOption($dbname, "kebun_5verifikasibkm", "karyawanid,karyawanid", "status='1' AND kodeorg='" . substr($param['notransaksi'], 9, 4) . "'");
if (in_array($_SESSION['standard']['userid'], $verifbkm)) {
	$vCek = selectQuery($dbname, "kebun_verifikasibkm", "*", "notransaksi='" . $param['notransaksi'] . "' and statusverifikasi='1' and verifiedby='" . $_SESSION['standard']['userid'] . "'");
	$rvCek = fetchdata($vCek);
	$countVerif = count($rvCek);
	if ($countVerif == 0) {
		$tab .= "<br>";
		// $tab.="<button style='float:right' class=mybutton onclick=\"insertVerifikasi('".$param['notransaksi']."')\">".$_SESSION['lang']['verifikasi']."</button>";
		// $tab.="<button style='float:right' class=mybutton onclick=\"detailVerifikasi('".$param['notransaksi']."')\">".$_SESSION['lang']['verifikasi']."</button>";
		$tab .= "<button style='float:right' class=mybutton onclick=\"validasiVerifikasi('" . $param['notransaksi'] . "')\">" . $_SESSION['lang']['verifikasi'] . "</button>";
		$tab .= "<br>";
	}
}

$tab .= "<br /><b>" . $titleDetail[0] . "<b><br />";
$tab .= "<table cellpadding=5 " . $border . " class=sortable width=100%><thead>";
$tab .= "<tr class=rowheader>";
$tab .= "<th align=center>No</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['notransaksi'] . "</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['divisi'] . "</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['namakegiatan'] . "</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['satuan'] . "</th>";
$tab .= "<th align=center>Induk " . $_SESSION['lang']['blok'] . "</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['blok'] . " Kecil</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['tahuntanam'] . "</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['hasilkerjad'] . "</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['jhk'] . "</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['umr'] . "</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['upahpremi'] . "</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['total'] . "</th>";
$tab .= "</tr></thead><tbody>";

if ($param['blok'] != '') {
	$wh .= " and b.kodeorg='" . $param['blok'] . "'";
}
if ($param['kegiatan'] != '') {
	$wh .= " and b.kodekegiatan='" . $param['kegiatan'] . "'";
}

$hasilkerjax = $hkkaryx = $nikkaryx = $indukorgx = $kegiatanx = $notransx = $upahpremix = $umrx = $blokkecilx = $nourutx = $tahuntanamx = array();
$hasilkerjadt = $stblokdt = $hkkarydt = $upahpremidt = $umrdt = array();

$dtLoop = "SELECT a.notransaksi, a.nobkm, a.nikpemel, a.kodekegiatan, a.kodeorg, a.hasilkerja, a.jumlahhk,a.nourut, b.umr, a.upahpremi, c.tanggal
            FROM $dbname.kebun_prestasi a LEFT JOIN $dbname.kebun_kehadiran b on a.notransaksi = b.notransaksi and a.nikpemel=b.nik and a.nourut=b.nourut
			LEFT JOIN $dbname.kebun_aktifitas c on a.notransaksi=c.notransaksi 
            WHERE a.notransaksi='" . $param['notransaksi'] . "' " . $wh . "";
$reslopp = fetchData($dtLoop);
foreach ($reslopp as $val) {
	$indukorgx[$val['kodeorg']] = $val['kodeorg'];
	$kegiatanx[$val['kodeorg']][$val['kodekegiatan']] = $val['kodekegiatan'];
	$nikkaryx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['nikpemel'];
	$hasilkerjax[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['hasilkerja'];
	$hkkaryx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['jumlahhk'];
	$notransx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['notransaksi'];
	$nobkmx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['nobkm'];
	$upahpremix[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['upahpremi'];
	$umrx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['umr'];
	$nourutx[$notransx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']]] = 0;
	// $nourutx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']] = $val['nourut'];

	$tgl = tanggalnormal($val['tanggal']);
	$tgl2 = $val['tanggal'];
	$periode = substr($val['tanggal'], 0, 7);
}

$tahunbulan = substr($param['notransaksi'], 0, 6);
$kdorgxx = substr($param['notransaksi'], 9, 4);
$strbloktahunan = "SELECT * FROM setup_blok_tahunan
		where tahun = '" . $tahunbulan . "' and kodeorg like '%" . $kdorgxx . "%' ";
$resbloktahunan = fetchdata($strbloktahunan);

$dbaseblok = 'setup_blok';
$whereblok = " AND b.status='A'";
$whereblok2 = " AND b.status='A'";
if (count($resbloktahunan) > 0) {
	$dbaseblok = 'setup_blok_tahunan';
	$whereblok = " and b.tahun = '" . $tahunbulan . "'";
	$whereblok = " and b.tahun = '" . $tahunbulan . "'";
}

$dtBlok = "SELECT a.notransaksi, a.nobkm, a.nikpemel, a.kodekegiatan, a.kodeorg, b.kodeorg as blokkecil, b.tahuntanam,
				SUM(b.luasareaproduktif + b.luasareanonproduktif) as luasareaproduktif, b.lc, b.luasbloking, b.statusblok
				FROM $dbname.kebun_prestasi a LEFT JOIN " . $dbname . ".setup_kegiatan c ON c.kodekegiatan=a.kodekegiatan
				LEFT JOIN " . $dbname . "." . $dbaseblok . " b ON a.kodeorg = b.indukblok and IF (c.kelompok = 'PNN', 'TM', c.kelompok) = b.statusblok
				WHERE a.notransaksi = '" . $param['notransaksi'] . "' and b.statusblok IN (SELECT kelompok FROM $dbname.setup_kegiatan) " . $whereblok . " 
				group by a.kodekegiatan,a.nikpemel,b.kodeorg
				HAVING SUM(b.luasareaproduktif + b.luasareanonproduktif + b.lc + b.luasbloking) > 0";
$resBlok = fetchData($dtBlok);
foreach ($resBlok as $val) {
	$blokkecilx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] .= $val['blokkecil'];
	if ($val['luasareaproduktif'] == 0) {
		if ($val['lc'] == 0) {
			if ($val['luasbloking'] > 0) {
				$hasilkerjadt[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] = $val['luasbloking'];
			} else {
				$hasilkerjadt[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] = 0;
			}
		} else {
			$hasilkerjadt[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] = $val['lc'];
		}
	} else {
		$hasilkerjadt[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] = $val['luasareaproduktif'];
	}

	$tahuntanamx[$val['kodeorg']][$val['kodekegiatan']][$val['nikpemel']][$val['blokkecil']] = $val['tahuntanam'];
	$stblokdt[$val['kodeorg']][$val['kodekegiatan']][$val['blokkecil']] = $val['statusblok'];
}

foreach ($indukorgx as $kdorg) {
	foreach ($kegiatanx[$kdorg] as $kdkeg) {
		foreach ($nikkaryx[$kdorg][$kdkeg] as $nik) {
			foreach ($blokkecilx[$kdorg][$kdkeg][$nik] as $blkcl) {
				// $totalxx = $umrx[$kdorg][$kdkeg][$nik] + $upahpremix[$kdorg][$kdkeg][$nik];
				$totalhasilkerjadt[$kdorg][$nik][$kdkeg] += $hasilkerjadt[$kdorg][$kdkeg][$nik][$blkcl];

				$jmlhblok[$kdorg][$kdkeg][$nik] = count($blokkecilx[$kdorg][$kdkeg][$nik]);
			}
		}
	}
}

$urutblok = array();
$sisa = array();
$sisahasil = array();
$sisapremi = array();

$no = $thk = $tumr = $tpremi = 0;
foreach ($indukorgx as $kdorg) {
	foreach ($kegiatanx[$kdorg] as $kdkeg) {
		foreach ($nikkaryx[$kdorg][$kdkeg] as $nik) {
			foreach ($blokkecilx[$kdorg][$kdkeg][$nik] as $blkcl) {
				#=== cek apakah di setup ada materialnya ===
				# Ambil data dari  kebun_pakaimaterial
				$queryM = selectQuery($dbname, 'kebun_pakaimaterial', "*", "notransaksi='" . $param['notransaksi'] . "' and kodekegiatan='" . $kegiatanx[$kdorg][$kdkeg] . "' and kodeorg='" . $indukorgx[$kdorg] . "'");
				$dataM = fetchData($queryM);
				$queryK = selectQuery($dbname, 'setup_kegiatannorma', "*", "kodekegiatan='" . $kegiatanx[$kdorg][$kdkeg] . "' and kelompok='" . $stblokdt[$kdorg][$kdkeg][$blkcl] . "'");
				// exit("Warning: ".$queryK);
				$dataK = fetchData($queryK);
				$c = "";
				$title = "";
				if (empty($dataM) and !empty($dataK)) {
					$c = "color:red;";
					$title = "title='Material Belum Diinput !!!'";
				}

				$luasdiproporsi[$kdorg][$kdkeg][$nik] = $hasilkerjadt[$kdorg][$kdkeg][$nik][$blkcl] / $totalhasilkerjadt[$kdorg][$nik][$kdkeg];

				@$urutblok[$kdorg][$kdkeg][$nik]++;
				if ($urutblok[$kdorg][$kdkeg][$nik] == $jmlhblok[$kdorg][$kdkeg][$nik]) {
					$hkproporsi[$kdorg][$kdkeg][$nik][$blkcl] 			= $hkkaryx[$kdorg][$kdkeg][$nik] - $sisa[$kdorg][$nik][$kdkeg];
					$hasilkerjaproporsi[$kdorg][$kdkeg][$nik][$blkcl] 	= $hasilkerjax[$kdorg][$kdkeg][$nik] - $sisahasil[$kdorg][$nik][$kdkeg];
					$premiproporsi[$kdorg][$kdkeg][$nik][$blkcl]		= $upahpremix[$kdorg][$kdkeg][$nik] - $sisapremi[$kdorg][$nik][$kdkeg];
				} else {
					$hkproporsi[$kdorg][$kdkeg][$nik][$blkcl]			= floor($luasdiproporsi[$kdorg][$kdkeg][$nik] * $hkkaryx[$kdorg][$kdkeg][$nik] * 100) / 100;
					$hasilkerjaproporsi[$kdorg][$kdkeg][$nik][$blkcl] 	= floor($luasdiproporsi[$kdorg][$kdkeg][$nik] * $hasilkerjax[$kdorg][$kdkeg][$nik] * 100) / 100;
					$premiproporsi[$kdorg][$kdkeg][$nik][$blkcl]		= floor($luasdiproporsi[$kdorg][$kdkeg][$nik] * $upahpremix[$kdorg][$kdkeg][$nik] * 100) / 100;
				}

				$sisa[$kdorg][$nik][$kdkeg]	+= $hkproporsi[$kdorg][$kdkeg][$nik][$blkcl];
				// if($nik=='0000000838' and $kdkeg=='621060101' and $blkcl=='PPPE02D18F'){
				// 	echo $kdorg.' : '.$blkcl.' : '.$sisa[$kdorg][$nik][$kdkeg].' dengan HK TOTAL :.'.$hkkaryx[$kdorg][$kdkeg][$nik].'  blok ke : '.$urutblok[$kdorg][$kdkeg][$nik].' total sisa : '.$sisa[$kdorg][$nik][$kdkeg].'<br>';
				// }
				$sisahasil[$kdorg][$nik][$kdkeg]	+= $hasilkerjaproporsi[$kdorg][$kdkeg][$nik][$blkcl];
				$sisapremi[$kdorg][$nik][$kdkeg]	+= $premiproporsi[$kdorg][$kdkeg][$nik][$blkcl];

				// echo "<pre>";
				// print_r($hkproporsi);
				// echo "</pre>";

				$gajipokokkary[$nik] = getUpahKary($periode, $nik);
				$umrproporsi[$kdorg][$kdkeg][$nik][$blkcl] = $gajipokokkary[$nik] * $hkproporsi[$kdorg][$kdkeg][$nik][$blkcl];

				$totalxx = $umrproporsi[$kdorg][$kdkeg][$nik][$blkcl] + $premiproporsi[$kdorg][$kdkeg][$nik][$blkcl];

				if ($nourutx[$notransx[$kdorg][$kdkeg][$nik]] == 0 || $nourutx[$notransx[$kdorg][$kdkeg][$nik]] == "") {
					$nourutx[$notransx[$kdorg][$kdkeg][$nik]] = 1;
				} else {
					$nourutx[$notransx[$kdorg][$kdkeg][$nik]] += 1;
				}

				$no += 1;
				$tab .= "<tr class=rowcontent style='vertical-align:top;" . $c . "' " . $title . ">";
				$tab .= "<td align=center>" . $no . "</td>";
				$tab .= "<td id=notransx_" . $no . ">" . $notransx[$kdorg][$kdkeg][$nik] . "</td>";
				$tab .= "<td id=nobkmx_" . $no . " hidden>" . $nobkmx[$kdorg][$kdkeg][$nik] . "</td>";
				$tab .= "<td id=nourutx_" . $no . " hidden>" . $nourutx[$notransx[$kdorg][$kdkeg][$nik]] . "</td>";
				$tab .= "<td id=divisix_" . $no . " hidden>" . substr($kdorg, 0, 6) . "</td>";
				$tab .= "<td>" . @$optGudang[substr($kdorg, 0, 6)] . "</td>";
				$tab .= "<td id=nikx_" . $no . " hidden>" . $nikkaryx[$kdorg][$kdkeg][$nik] . "</td>";
				$tab .= "<td>" . @$optNamaKary[$nikkaryx[$kdorg][$kdkeg][$nik]] . "</td>";
				$tab .= "<td id=kegx_" . $no . " hidden>" . @$kdkeg . " </td>";
				$tab .= "<td>" . @$kdkeg . " - " . @$optKegiatan[$kdkeg] . "</td>";
				$tab .= "<td>" . @$optSatKegiatan[$kdkeg] . "</td>";
				$tab .= "<td id=indukblokx_" . $no . " hidden>" . $kdorg . "</td>";
				$tab .= "<td>" . @$optGudang[$kdorg] . "</td>";
				$tab .= "<td id=blokkecilx_" . $no . ">" . @$blokkecilx[$kdorg][$kdkeg][$nik][$blkcl] . "</td>";
				$tab .= "<td>" . @$tahuntanamx[$kdorg][$kdkeg][$nik][$blkcl] . "</td>";
				$tab .= "<td id=hasilkerjax_" . $no . " align=right>" . @$hasilkerjaproporsi[$kdorg][$kdkeg][$nik][$blkcl] . "</td>";
				$tab .= "<td id=hkx_" . $no . " align=right>" . @$hkproporsi[$kdorg][$kdkeg][$nik][$blkcl] . "</td>";
				$tab .= "<td align=right>" . @hidezerodecimal($umrproporsi[$kdorg][$kdkeg][$nik][$blkcl], 2) . "</td>";
				$tab .= "<td align=right>" . @hidezerodecimal($premiproporsi[$kdorg][$kdkeg][$nik][$blkcl], 2) . "</td>";
				$tab .= "<td hidden id=umrx_" . $no . " align=right>" . $umrproporsi[$kdorg][$kdkeg][$nik][$blkcl] . "</td>";
				$tab .= "<td hidden id=premix_" . $no . " align=right>" . $premiproporsi[$kdorg][$kdkeg][$nik][$blkcl] . "</td>";
				$tab .= "<td align=right>" . @hidezerodecimal($totalxx, 0) . "</td>";

				// $thk += $hkproporsi[$blkcl];
				$thk 	+= $hkproporsi[$kdorg][$kdkeg][$nik][$blkcl];
				$tumr += $umrproporsi[$kdorg][$kdkeg][$nik][$blkcl];
				$tpremi += $premiproporsi[$kdorg][$kdkeg][$nik][$blkcl];
				$tpres += $hasilkerjaproporsi[$kdorg][$kdkeg][$nik][$blkcl];
			}
		}
	}
}
$tab .= "<td hidden><input type=hidden id='rows_pres' value='" . $no . "'></td>";

$tab .= "<tr class=rowcontent style=background-color:#AED6F1>";
$tab .= "<td align=center colspan=10><b>Sub Total BKM</b></td>";
$tab .= "<td  align=right>" . hidezerodecimal($thk, 2) . "</td>";
$tab .= "<td  align=right>" . @hidezerodecimal($tumr, 2) . "</td>";
$tab .= "<td  align=right>" . @hidezerodecimal($tpremi, 2) . "</td>";
$tab .= "<td  align=right>" . @hidezerodecimal($tumr + $tpremi, 2) . "</td>";
$tab .= "</tr>";

$kdjurnal = "KBNB0";
$optakun = makeOption($dbname, 'keu_5parameterjurnal', 'jurnalid,noakundebet', "jurnalid='" . $kdjurnal . "'");
$akun = $optakun[$kdjurnal];

$dataabs = $dataabskary = $noakun = array();
$str = "select * from " . $dbname . ".sdm_absensidt where norefrensi='" . $param['notransaksi'] . "'";
$res = fetchdata($str);
foreach ($res as $bar) {
	if ($bar['noakun'] == '') {
		$bar['noakun'] = $akun;
	}
	if (getKary($bar['karyawanid'], 'tipekaryawan') == 4) {
		@$umrabs[$bar['noakun']] += $bar['umr'];
		@$umrabskary[$bar['karyawanid']] += $bar['umr'];
	}

	$dataabs[$bar['noakun']] = $bar['noakun'];
	@$jhkabs[$bar['noakun']] += $bar['hk'];
	@$premiabs[$bar['noakun']] += $bar['premi'];

	$noakun[$bar['karyawanid']] = $bar['noakun'];
	$dataabskary[$bar['karyawanid']] = $bar['karyawanid'];
	@$jhkabskary[$bar['karyawanid']] += $bar['hk'];
	@$premiabskary[$bar['karyawanid']] += $bar['premi'];
	@$kdabsensi[$bar['karyawanid']] = $bar['absensi'];
}


$ttlhkabs = 0;
$kodeabsen = makeOption($dbname, 'sdm_5absensi', 'kodeabsen,keterangan');
foreach (@$dataabs as $absen) {
	foreach ($dataabskary as $kary) {
		$no++;
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td align=center>" . $no . "</td>";
		$tab .= "<td>" . $tgl . "</td>";
		$tab .= "<td colspan=2>" . getNamaKaryawan($dataabskary[$kary]) . "</td>";
		$tab .= "<td>" . $absen . " - " . getNamaAkun($absen) . "</td>";
		$tab .= "<td>HK</td>";
		$tab .= "<td></td>";
		$tab .= "<td></td>";
		$tab .= "<td></td>";
		$tab .= "<td></td>";
		$tab .= "<td align=right>" . hidezerodecimal($jhkabskary[$kary], 2) . "</td>";
		$tab .= "<td align=right>" . hidezerodecimal($umrabskary[$kary]) . "</td>";
		$tab .= "<td align=right>" . hidezerodecimal($premiabskary[$kary]) . "</td>";
		$tab .= "<td align=right>" . hidezerodecimal($umrabskary[$kary] + $premiabskary[$kary]) . "</td>";
		$tab .= "</tr>";
	}

	@$ttlhkabs += $jhkabs[$absen];
	@$ttlumrabs += $umrabs[$absen];
	@$ttlpreabs += $premiabs[$absen];
}

$tab .= "<tr class=rowcontent style=background-color:#AED6F1>";
$tab .= "<td align=center colspan=10><b>Sub Total Absensi</b></td>";
$tab .= "<td align=right>" . ($ttlhkabs) . "</td>";
$tab .= "<td align=right>" . @hidezerodecimal($ttlumrabs, 2) . "</td>";
$tab .= "<td align=right>" . @hidezerodecimal($ttlpreabs, 2) . "</td>";
$tab .= "<td align=right>" . @hidezerodecimal($ttlumrabs + $ttlpreabs, 2) . "</td>";
$tab .= "</tr>";

$tab .= "<tr class=rowcontent style=background-color:#A3E4D7>";
$tab .= "<td align=center colspan=9><b>Total (BKM + Absensi)</b></td>";
$tab .= "<td  align=right>" . @hidezerodecimal($tpres, 2) . "</td>";
$tab .= "<td  align=right>" . @hidezerodecimal($thk + $ttlhkabs, 2) . "</td>";
$tab .= "<td  align=right>" . @hidezerodecimal($tumr + $ttlumrabs, 2) . "</td>";
$tab .= "<td  align=right>" . @hidezerodecimal($tpremi + $ttlpreabs, 2) . "</td>";
$tab .= "<td  align=right>" . @hidezerodecimal($tumr + $ttlumrabs + $tpremi + $ttlpreabs, 2) . "</td>";
$tab .= "</tr>";

$tab .= "</tbody></table>";

$tab .= "<br /><b>" . $titleDetail[1] . "</b><br />";

$rows = "rowspan=2";
$tab .= "<table cellpadding=5 " . $border . " class=sortable width=100%>";
$tab .= "<thead>";
$tab .= "<tr class=rowheader>";
$tab .= "<th align=center " . $rows . ">No</th>";
$tab .= "<th align=center " . $rows . ">" . $_SESSION['lang']['notransaksi'] . "</th>";
$tab .= "<th align=center " . $rows . ">" . $_SESSION['lang']['kodekegiatan'] . "</th>";
$tab .= "<th align=center " . $rows . ">" . $_SESSION['lang']['kegiatan'] . "</th>";
$tab .= "<th align=center " . $rows . ">Induk " . $_SESSION['lang']['blok'] . "</th>";
$tab .= "<th align=center " . $rows . ">" . $_SESSION['lang']['blok'] . " Kecil</th>";
$tab .= "<th align=center " . $rows . ">" . $_SESSION['lang']['tahuntanam'] . "</th>";
$tab .= "<th align=center " . $rows . ">" . $_SESSION['lang']['gudang'] . "</th>";
$tab .= "<th align=center " . $rows . ">" . $_SESSION['lang']['namabarang'] . "</th>";
$tab .= "<th align=center colspan=2>" . $_SESSION['lang']['material'] . "</th>";
$tab .= "</tr>";

$tab .= "<tr>";
$tab .= "<th align=center>" . $_SESSION['lang']['satuan'] . "</th>";
$tab .= "<th align=center>" . $_SESSION['lang']['jumlah'] . "</th>";
$tab .= "</tr>";

$tab .= "</thead><tbody>";

$datamat = $kdgdng = $jlhmat = $jlhmatha = $datakdmat = $kegmat = $kdorgmat = $brgmat = array();
$blokkecilx = $tahuntanamx = $hasilkerjadt2 = array();

$str = "select * from " . $dbname . ".kebun_pakaimaterial where 1=1 and notransaksi='" . $param['notransaksi'] . "' order by kodekegiatan, kodeorg";
$res = fetchdata($str);
foreach ($res as $bar) {
	$kegmat[$bar['kodekegiatan']] = $bar['kodekegiatan'];
	$kdorgmat[$bar['kodekegiatan']][$bar['kodeorg']] = $bar['kodeorg'];
	$brgmat[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']] = $bar['kodebarang'];

	$datakdmat[$bar['kodebarang']] = $bar['kodebarang'];
	$datamat[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']] = $bar['kodebarang'];
	$kdgdng[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']] = $bar['kodegudang'];
	@$jlhmat[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']] += $bar['kwantitas'];
	@$jlhmatha[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']] += $bar['kwantitasha'];
	$notrans[$bar['kodekegiatan']][$bar['kodeorg']][$bar['kodebarang']] = $bar['notransaksi'];
}

if (count($datamat) == 0) {
	$tab .= "<tr class=rowcontent><td colspan=11 style='text-align:center'>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
}

$dtBlok = "SELECT a.notransaksi, a.kodekegiatan, a.kodebarang, a.kodeorg, a.kwantitas, a.kodegudang, 
				b.kodeorg as blokkecil, b.tahuntanam, SUM(b.luasareaproduktif + b.luasareanonproduktif) as luasareaproduktif, 
				b.lc, b.luasbloking,b.statusblok
				FROM $dbname.kebun_pakaimaterial a LEFT JOIN " . $dbname . ".setup_kegiatan c ON c.kodekegiatan=a.kodekegiatan
				LEFT JOIN " . $dbname . "." . $dbaseblok . " b ON a.kodeorg = b.indukblok and IF (c.kelompok = 'PNN', 'TM', c.kelompok) = b.statusblok
				WHERE a.notransaksi = '" . $param['notransaksi'] . "' and b.statusblok IN (SELECT kelompok FROM $dbname.setup_kegiatan) " . $whereblok . " 
				group by a.kodekegiatan,a.kodebarang,b.kodeorg
				HAVING SUM(b.luasareaproduktif + b.luasareanonproduktif + b.lc + b.luasbloking) > 0";
$resBlok = fetchData($dtBlok);
foreach ($resBlok as $val) {
	$blokkecilx[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] .= $val['blokkecil'];
	$tahuntanamx[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] = $val['tahuntanam'];
	if ($val['luasareaproduktif'] == 0) {
		if ($val['lc'] == 0) {
			if ($val['luasbloking'] > 0) {
				$hasilkerjadt2[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] = $val['luasbloking'];
			} else {
				$hasilkerjadt2[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] = 0;
			}
		} else {
			$hasilkerjadt2[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] = $val['lc'];
		}
	} else {
		$hasilkerjadt2[$val['kodekegiatan']][$val['kodeorg']][$val['kodebarang']][$val['blokkecil']] = $val['luasareaproduktif'];
	}
}

foreach ($kegmat as $kdkeg) {
	foreach ($kdorgmat[$kdkeg] as $kdorg) {
		foreach ($brgmat[$kdkeg][$kdorg] as $kdbrg) {
			foreach ($blokkecilx[$kdkeg][$kdorg][$kdbrg] as $blk) {
				$totalhasilkerjadt2[$kdkeg][$kdorg][$kdbrg] += $hasilkerjadt2[$kdkeg][$kdorg][$kdbrg][$blk];

				$jmlhblok[$kdkeg][$kdorg][$kdbrg] = count($blokkecilx[$kdkeg][$kdorg][$kdbrg]);
			}
		}
	}
}

// echo "<pre>";
// print_r($totalhasilkerjadt2);
// echo "</pre>";

$urutblokm 	= array();
$sisam		= array();
$sisaha		= array();

$no = 0;
foreach ($kegmat as $kdkeg) {
	foreach ($kdorgmat[$kdkeg] as $kdorg) {
		foreach ($brgmat[$kdkeg][$kdorg] as $kdbrg) {
			foreach ($blokkecilx[$kdkeg][$kdorg][$kdbrg] as $blk) {
				$xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] = $hasilkerjadt2[$kdkeg][$kdorg][$kdbrg][$blk] / $totalhasilkerjadt2[$kdkeg][$kdorg][$kdbrg];

				$urutblokm[$kdkeg][$kdorg][$kdbrg]++;
				if (@$urutblokm[$kdkeg][$kdorg][$kdbrg] == $jmlhblok[$kdkeg][$kdorg][$kdbrg]) {
					// $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] = $oldproporsi[$kdkeg][$kdorg][$kdbrg][$blk];
					$jmlbrgproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= $jlhmat[$kdkeg][$kdorg][$kdbrg] - $sisam[$kdkeg][$kdorg][$kdbrg];
					$jmlhaproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= $jlhmatha[$kdkeg][$kdorg][$kdbrg] - $sisaha[$kdkeg][$kdorg][$kdbrg];
				} else {
					$jmlbrgproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmat[$kdkeg][$kdorg][$kdbrg];
					$jmlhaproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmatha[$kdkeg][$kdorg][$kdbrg];

					// $jmlbrgproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= round($xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmat[$kdkeg][$kdorg][$kdbrg],5);
					// $jmlhaproporsi[$kdkeg][$kdorg][$kdbrg][$blk]	= round($xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmatha[$kdkeg][$kdorg][$kdbrg],5);
				}

				// echo "<pre>";
				// print_r($totalhasilkerjadt2);
				// echo "</pre>";

				$sisam[$kdkeg][$kdorg][$kdbrg] 	+= $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmat[$kdkeg][$kdorg][$kdbrg];
				$sisaha[$kdkeg][$kdorg][$kdbrg] += $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmatha[$kdkeg][$kdorg][$kdbrg];
				// $jmlbrgproporsi[$blk]	= round($xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmat[$kdkeg][$kdorg][$kdbrg],2);

				// $jmlbrgproporsi[$blk]	= $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmat[$kdkeg][$kdorg][$kdbrg];
				// $jmlhaproporsi[$blk]	= $xproporsi[$kdkeg][$kdorg][$kdbrg][$blk] * $jlhmatha[$kdkeg][$kdorg][$kdbrg];

				$no += 1;
				$nmsatbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan', "kodebarang='" . $kdbrg . "'");
				$nmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $kdbrg . "'");
				$nmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='" . $kdkeg . "'");
				$nmsat = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,satuan', "kodekegiatan='" . $kdkeg . "'");
				$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kdgdng[$kdkeg][$kdorg][$kdbrg] . "'");
				$nminduk = makeOption($dbname, 'organisasi', 'indukblok,namaindukblok', "indukblok='" . $kdorg . "'");

				$strv = "select sum(hasilkerja) as hasilkerja from " . $dbname . ".kebun_prestasi where notransaksi='" . $notrans[$kdkeg][$kdorg][$kdbrg] . "' and kodekegiatan='" . $kdkeg . "' and kodeorg='" . $kdorg . "'"; //exit('error'.$strv);
				$barv = fetchdata($strv);

				$tab .= "<tr class=rowcontent style=height:25px>";
				$tab .= "<td align=center>" . $no . "</td>";
				$tab .= "<td id=notransm_" . $no . ">" . $notrans[$kdkeg][$kdorg][$kdbrg] . "</td>";
				$tab .= "<td id=kegiatanm_" . $no . ">" . $kdkeg . "</td>";
				$tab .= "<td>" . $nmkeg[$kdkeg] . "</td>";
				$tab .= "<td id=indukblokm_" . $no . " hidden>" . $kdorg . "</td>";
				$tab .= "<td>" . $nminduk[$kdorg] . "</td>";
				$tab .= "<td id=blkclm_" . $no . ">" . $blokkecilx[$kdkeg][$kdorg][$kdbrg][$blk] . "</td>";
				$tab .= "<td >" . $tahuntanamx[$kdkeg][$kdorg][$kdbrg][$blk] . "</td>";
				$tab .= "<td id=kdgdgm_" . $no . " hidden>" . $kdgdng[$kdkeg][$kdorg][$kdbrg] . "</td>";
				$tab .= "<td>" . $kdgdng[$kdkeg][$kdorg][$kdbrg] . " - " . $nmorg[$kdgdng[$kdkeg][$kdorg][$kdbrg]] . "</td>";
				$tab .= "<td id=kdbrgm_" . $no . " hidden>" . $kdbrg . "</td>";
				$tab .= "<td>" . $kdbrg . " - " . $nmbrg[$kdbrg] . "</td>";
				$tab .= "<td align=center>" . $nmsatbrg[$kdbrg] . "</td>";
				// $tab.="<td id=jmlbrgm_".$no." align=right>".number_format($jmlbrgproporsi[$kdkeg][$kdorg][$kdbrg][$blk],2)."</td>";
				$tab .= "<td id=jmlbrgm_" . $no . " align=right>" . $jmlbrgproporsi[$kdkeg][$kdorg][$kdbrg][$blk] . "</td>";
				$tab .= "<td id=jmlham_" . $no . " hidden align=right>" . $jmlhaproporsi[$kdkeg][$kdorg][$kdbrg][$blk] . "</td>";
				$tab .= "</tr>";

				$ttljlhmat[$kdbrg] += $jmlbrgproporsi[$kdkeg][$kdorg][$kdbrg][$blk];
			}
		}
	}
}
$tab .= "<td hidden><input type=hidden id='rows_matr' value='" . $no . "'></td>";

$no = 0;
foreach ($datakdmat as $kodemat) {
	$nmsatbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,satuan', "kodebarang='" . $kodemat . "'");
	$nmbrg = makeOption($dbname, 'log_5masterbarang', 'kodebarang,namabarang', "kodebarang='" . $kodemat . "'");

	$no++;
	$tab .= "<tr class=rowcontent style=background-color:#A3E4D7;height:25px>";
	if ($no == 1) {
		$tab .= "<td align=center rowspan=" . count($datakdmat) . " colspan=8><b>REKAPITULASI</b></td>";
	}
	$tab .= "<td>" . $kodemat . " - " . $nmbrg[$kodemat] . "</td>";
	$tab .= "<td align=center>" . $nmsatbrg[$kodemat] . "</td>";
	$tab .= "<td align=right>" . $ttljlhmat[$kodemat] . "</td>";
	$tab .= "</tr>";
}

$tab .= "</tbody>";
$tab .= "</table>";

$arrnik = array();
foreach ($indukorgx as $kdorg) {
	foreach ($kegiatanx[$kdorg] as $kdkeg) {
		foreach ($nikkaryx[$kdorg][$kdkeg] as $nik) {
			$jumlahhk = array();
			$transno = '';
			// Cek Apakah melebihi 1 HK
			$cekHk = "select a.notransaksi,a.nik,a.jhk as jhk from " . $dbname . ".kebun_kehadiran a
				left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi
				where b.tanggal='" . $tgl2 . "' and nik='" . $nik . "';";
			$resHk = fetchData($cekHk);
			foreach ($resHk as $bar) {
				$nikex = $bar['nik'];
				$arrnik[$bar['nik']] = $bar['nik'];
				if ($bar['jhk'] == '') {
					$bar['jhk'] = 0;
				}
				$jumlahhk[$bar['nik']] += $bar['jhk'];
				$transno .= "No => " . $bar['notransaksi'] . " => " . $bar['jhk'] . " HK<br>";
			}
		}
	}
}


if (round(floatval($jumlahhk[$nikex]), 2) > 1.00) {
	$tab .= "<br>";
	$tab .= "<table>";

	foreach ($arrnik as $vnik) {
		// Cek apakah jumlah HK lebih dari 1.00
		if (round(floatval($jumlahhk[$vnik]), 2) > 1.00) {
			$tab .= "<tr class='rowcontent' style='color:red; font-weight:bold;'>";
			$tab .= "<td colspan='12'>";
			$tab .= "<br>Jumlah HK karyawan " . getNamaKaryawan($vnik) . " lebih dari 1, HK yang sudah tersimpan sebesar = " . $jumlahhk[$vnik] . " HK<br><br> " . $transno;
			$tab .= "</td>";
			$tab .= "</tr>";
		}
	}
	$tab .= "</table>";
} else {
	$vCek = selectQuery($dbname, "kebun_verifikasibkm", "*", "notransaksi='" . $param['notransaksi'] . "' and statusverifikasi='1'");
	$rvCek = fetchdata($vCek);
	$countVerif = count($rvCek);
	if ($countVerif > 0) {
		$tab .= "<br/><br/>";
		if (in_array($_SESSION['empl']['jabatan'], $jab)) {
		}
	}
	$tab .= "<button id=postingbtn class=mybutton onclick=postingDataBkm('" . $param['notransaksi'] . "')>" . $_SESSION['lang']['posting'] . "</button>";
}

echo $tab;
