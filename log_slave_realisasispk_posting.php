<? //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zFunction.php');


$param = $_POST;


$postJabatan = getPostingJabatan('baspk');
$kodept = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "length(kodeorganisasi)='4'");

if ($_SESSION['empl']['tipelokasitugas'] != 'HOLDING') {
	if (!in_array($_SESSION['empl']['kodejabatan'], $postJabatan)) {
		exit("Error:Anda tidak memiliki hak untuk melakukan posting");
	}
}

//hilangkan koma
$param['jumlahrealisasi'] = str_replace(",", "", $param['jumlahrealisasi']);

#=== Get Data ===
# Get PT
$pt = getPT($dbname, $param['kodeorg']);
if ($pt == false) {
	$pt = getHolding($dbname, $param['kodeorg']);
}

# Convert Tanggal
$tgl = tanggalsystem($param['tanggal']);
$tglsystemn = tanggalsystemn($param['tanggal']);


#periksa tanggal periode akuntansi===============
if ($_SESSION['org']['period']['start'] > $tgl)
	exit('Error:Tanggal ' . $param['tanggal'] . ' diluar periode aktif ' . $_SESSION['org']['period']['start']);


// SPK
$queryH = selectQuery($dbname, 'log_spkht', "*", "notransaksi='" . $param['notransaksi'] . "'");
$resH = fetchData($queryH);

$queryO = selectQuery($dbname, 'organisasi', "*", "kodeorganisasi='" . substr($param['blokalokasi'], 0, 4) . "' AND tipe != 'PABRIK'");
$resO = fetchData($queryO);
if (count($resO) > 0) {
	$blokparam = substr($param['blokalokasi'], 0, 9);
} else {
	$blokparam = $param['blokalokasi'];
}
# BASPK
$query = selectQuery(
	$dbname,
	'log_baspk',
	"*",
	"notransaksi='" . $param['notransaksi'] .
		"' and kodeblok='" . $blokparam .
		"' and kodekegiatan='" . $param['kodekegiatan'] .
		"' and tanggal='" . $tglsystemn .
		"' and termin='" . $param['termin'] .
		"' and keterangan='" . $param['nobapp'] .
		"' and kodesegment='" . $param['kodesegment'] . "'"
);
$data = fetchData($query);

$vNodok = "";
if ($resH[0]['divisi'] == 'S') {
	$vNodok = $data[0]['kodeblok'];
} else if ($resH[0]['divisi'] == 'P') {
	$vNodok = $data[0]['kodeblok'];
} else {
	$vNodok = "";
}

if ($vNodok == '') {
	$vNodok = $data[0]['keterangan'];
}

#=== Cek if posted ===
$error0 = "";
if ($data[0]['statusjurnal'] == 1) {
	$error0 .= $_SESSION['lang']['errisposted'];
}
if ($error0 != '') {
	echo "Data Error :\n" . $error0;
	exit;
}

#=== Cek if data not exist ===
$error1 = "";
if (count($data) == 0) {
	$error1 .= $_SESSION['lang']['errdetailnotexist'] . "\n";
}
if ($error1 != '') {
	echo "Data Error :\n" . $error1;
	exit;
}

# Get Akun
if (substr($param['blokalokasi'], 0, 2) == 'PB') {
	$kodeJurnal = 'PBRK';

	$str = "select noakundebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodeJurnal . "' ";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	$noakundb = $bar['noakundebet'];
	$optKeg[$param['kodekegiatan']] = $noakundb;
	$optSupp = makeOption(
		$dbname,
		'log_5supkelompok',
		'supplierid,noakun',
		"supplierid='" . $param['koderekanan'] . "' and tipe='KONTRAKTOR'"
	);
} else {
	$scekjns = "select a.jenissupplier,a.jenis,a.unit from " . $dbname . ".lgl_pengajuanspkht a 
	          left join " . $dbname . ".log_spkht b on a.notransaksi=b.nopengajuan where b.notransaksi='" . $param['notransaksi'] . "'";
	$rcekjns = fetchData($scekjns);
	if (is_null($rcekjns[0]['jenissupplier']) || ($rcekjns[0]['jenissupplier'] == '')) {
		exit('Warning: jenis supplier pada pengajuan spk kosong');
	}
	$tipeKon = $rcekjns[0]['jenissupplier'];
	$kodeJurnal = 'SPK1';
	$optKeg = makeOption(
		$dbname,
		'setup_kegiatan',
		'kodekegiatan,noakun',
		"kodekegiatan='" . $param['kodekegiatan'] . "'"
	);
	$optKegtrk = makeOption(
		$dbname,
		'vhc_kegiatan',
		'kodekegiatan,noakun',
		"kodekegiatan='" . $param['kodekegiatan'] . "'"
	);
	$optSupp = makeOption(
		$dbname,
		'log_5supkelompok',
		'supplierid,noakun',
		"supplierid='" . $param['koderekanan'] . "' and tipe='" . $tipeKon . "'"
	);
}

if ($rcekjns[0]['jenis'] == 'ANGKUTTBS') {
	//ambil dari spb
	$str = "SELECT kgwbnetto,blok,nospb,indukblok FROM $dbname.kebun_spbdt_detail WHERE nospb IN (SELECT keterangan2 FROM $dbname.log_baspkdt WHERE keterangan='$vNodok') ";
	$res = fetchData($str);
	foreach ($res as $val) {
		$blokkecil[$val['blok']] = $val['blok'];
		$ttlblokkecil[$val['blok']] += $val['kgwbnetto'];
		$ttlblokbesar += $val['kgwbnetto'];
		$jlhblk++;
	}
}

if ($optSupp[$param['koderekanan']] == '' or $optSupp[$param['koderekanan']] == null or strlen($optSupp[$param['koderekanan']]) != 7) {
	exit("Warning : Nomor akun Kontraktor belum ada atau salah.");
}

#======================== Nomor Jurnal =============================
# Get Journal Counter
$queryJ = selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', "kodeorg='" . $kodept[$param['kodeorg']] . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . substr($tglsystemn, 0, 7) . "' ");
$tmpKonter = fetchData($queryJ);
$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
if (count($tmpKonter) == 0) {
	$sql = "select * from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodeJurnal . "'";
	$req = fetchData($sql);

	$data = array(
		'kodeorg'     => $kodept[$param['kodeorg']],
		'kodeunit'    => $param['kodeorg'],
		'kodekelompok' => $kodeJurnal,
		'periode'     => substr($tglsystemn, 0, 7),
		'keterangan'  => $req[0]['keterangan'],
		'nokounter'   => 1
	);

	$query = insertQuery($dbname, 'keu_5kelompokjurnal', $data, array_keys($data));
	try {
		$owlPDO->exec($query);
	} catch (PDOException $e) {
		print " Gagal  !: " . $e->getMessage() . "\n";
		die();
	}
}



# Transform No Jurnal dari No Transaksi
$nojurnal = $tgl . "/" . $param['kodeorg'] . "/" . $kodeJurnal . "/" . $konter;
#======================== /Nomor Jurnal ============================
# Alokasi Blok
if (strlen($param['blokalokasi']) > 5) { //di edit ama ginting dari 10 jadi 5
	$blok = $param['blokalokasi'];
} else {
	$blok = '';
}

#kusus jika project
$kodeasset = '';



//if(substr($param['blokalokasi'],0,2)=='AK' or substr($param['blokalokasi'],0,2)=='PB')
if (substr($param['blokalokasi'], 0, 2) == 'AK') {
	#ambil akun aktiva dalam konstruksi
	$tipeasset = substr($param['blokalokasi'], 3, 2);
	$tipeasset =  str_replace("0", "", $tipeasset);
	$str = "select akunak from " . $dbname . ".sdm_5tipeasset where kodetipe='" . $tipeasset . "'";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	if (owlBaris($res) < 1) {
		exit(" Error: Akun aktiva dalam konstruksi untuk " . $tipeasset . "  belum disetting dari keuangan->setup->tipeasset");
	} else {
		while ($bar = $res->fetch()) {
			if ($bar->akunak == '') {
				exit(" Error: Akun aktiva dalam konstruksi untuk " . $tipeasset . " belum disetting dari keuangan->setup->tipeasset");
			} else {
				$kodeasset = $param['blokalokasi'];
				$blok = '';
				$optKeg[$param['kodekegiatan']] = $bar->akunak;
			}
		}
	}
}




## cek kurs jika bukan idr

$str = "select matauang,posting from " . $dbname . ".log_spkht where notransaksi='" . $param['notransaksi'] . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$mtuang = $bar['matauang'];
$statusspk = $bar['posting'];

$str = "select kurs from " . $dbname . ".setup_matauangrate where daritanggal='" . $tgl . "' and kode='" . $mtuang . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$kurs = $bar['kurs'];

if ($mtuang != 'IDR') {
	if ($kurs == 0 || $kurs == '') {
		exit("Warning : Please Input Kurs " . $kode . " in " . tanggalnormal($tgl) . " ");
	} else {
		$kurs = $kurs;
	}
} else {
	$kurs = 1;
}

$param['jumlahrealisasi'] = $param['jumlahrealisasi'] * $kurs;

if ($statusspk == '2') {
	exit("Gagal, Tidak dapat diposting, SPK sudah di ditutup.");
}

$str = "select * from " . $dbname . ".organisasi";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmblok[$bar['kodeorganisasi']] = $bar['kodeorganisasi'];
}
$str = "select * from " . $dbname . ".vhc_5master";
$res = fetchdata($str);
foreach ($res as $bar) {
	$nmvhc[$bar['kodevhc']] = $bar['kodevhc'];
}


// Get Akun Ppn
$qAkun = selectQuery(
	$dbname,
	'setup_parameterappl',
	"nilai",
	"kodeaplikasi='TX' and kodeparameter='PPNINV'"
);
$resAkun = fetchData($qAkun);
$akunPpn = empty($resAkun) ? "" : $resAkun[0]['nilai'];

// Get Nilai PPn & Pph
$whr = " left(noakun,3)!='212' ";
$optPajak = makeOption($dbname, 'log_spk_tax', "noakun,nilai", "notransaksi='" .
	$param['notransaksi'] . "' and kodeorg='" . $param['kodeorg'] . "'", $whr);

// Proporsi Rupiah
$proporsi = @fixnan($param['jumlahrealisasi'] / $resH[0]['nilaikontrak']);

// Pisah Ppn dan Pph
$ppn = $pph = 0;
foreach ($optPajak as $noakun => $nilai) {
	if ($noakun == $akunPpn) {
		$ppn += $nilai * $proporsi;
	} else {
		$pph += $nilai * $proporsi;
	}
}


$ppn = 0;
$pph = 0;
$hutang = $param['jumlahrealisasi'] + $ppn - $pph;

# Prep Header
$dataRes['header'] = array(
	'nojurnal'     => $nojurnal,
	'kodejurnal'   => $kodeJurnal,
	'tanggal'      => $tgl,
	'tanggalentry' => date('Ymd'),
	'posting'      => 0,
	'totaldebet'   => $param['jumlahrealisasi'],
	'totalkredit'  => -1 * $param['jumlahrealisasi'],
	'amountkoreksi' => '0',
	'noreferensi'  => $param['notransaksi'],
	'autojurnal'   => '1',
	'matauang'     => 'IDR',
	'kurs'         => '1',
	'revisi'       => '0'
);
$dataResRk['header'] = array();
// $temp="";
if ($param['kodeorg'] != substr($param['blokalokasi'], 0, 4)) {
	// 	$temp=substr($blok,0,4);
	$kodeJurnalMemo = 'M';

	# Get Journal Counter
	$queryJ = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodeorg='" . getindukPT($param['kodeorg']) . "' and kodekelompok='" . $kodeJurnalMemo . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . substr($tglsystemn, 0, 7) . "'"
	);
	$tmpKonter2 = fetchData($queryJ);
	if (count($tmpKonter2) == 0) {
		exit("Error, Kelompok Jurnal M untuk kodeorg " . $param['kodeorg'] . ", kodeunit : " . $param['kodeorg'] . ", periode : " . substr($tglsystemn, 0, 7) . " silahkan tambah melalui menu : Keuangan - Setup - Kelompok Jurnal.");
	}

	@$konter2 = addZero($tmpKonter2[0]['nokounter'] + 1, 3);
	@$counterDt[substr($param['blokalokasi'], 0, 4)] = intval($tmpKonter2[0]['nokounter']) + 1;
	# Transform No Jurnal dari No Transaksi
	$tmpNoJurnal = explode('/', $param['notransaksi']);
	$nojurnal2 = $tgl . "/" . substr($param['blokalokasi'], 0, 4) . "/" . $kodeJurnalMemo . "/" . $konter2;

	#cek apakah nomor ini sudah ada di keu_jurnalht atau belum ???
	$str = "SELECT * FROM " . $dbname . ".`keu_jurnalht` WHERE nojurnal='" . $nojurnal2 . "'";
	$res = fetchdata($str);
	if (count($res) > 0) {
		$str = "select max(convert(substring_index(nojurnal,'/',-1),unsigned integer)) as nomor from keu_jurnalht where kodejurnal='" . $kodeJurnalMemo . "' and nojurnal like '%" . substr($param['blokalokasi'], 0, 4) . "%' and tanggal like '" . substr($tglsystemn, 0, 7) . "%'";
		$res = fetchdata($str);
		$konter2 = addZero($res[0]['nomor'] + 1, 3);
		$nojurnal2 = $tgl . "/" . substr($param['blokalokasi'], 0, 4) . "/" . $kodeJurnalMemo . "/" . $konter2;
	}





	// echo $temp."<br/>";
	// echo substr($blok,0,4);
	// exit('warning');

	// #1. Data Header
	// $dataResRk['header'][] = array(
	// 	'nojurnal'     =>$nojurnal2,
	// 	'kodejurnal'   =>$kodeJurnal,
	// 	'tanggal'      =>$tgl,
	// 	'tanggalentry' =>date('Ymd'),
	// 	'posting'      =>'1',
	// 	'totaldebet'   =>'0',
	// 	'totalkredit'  =>'0',
	// 	'amountkoreksi'=>'0',
	// 	'noreferensi'  =>$param['notransaksi'],
	// 	'autojurnal'   =>'1',
	// 	'matauang'     =>'IDR',
	// 	'kurs'         =>'1',
	// 	'revisi'       =>'0'
	// );

	# Prep Header
	$dataResRk['header'] = array(
		'nojurnal'     => $nojurnal2,
		'kodejurnal'   => 'M',
		'tanggal'      => $tgl,
		'tanggalentry' => date('Ymd'),
		'posting'      => 0,
		'totaldebet'   => $param['jumlahrealisasi'],
		'totalkredit'  => -1 * $param['jumlahrealisasi'],
		'amountkoreksi' => '0',
		'noreferensi'  => $param['notransaksi'],
		'autojurnal'   => '1',
		'matauang'     => 'IDR',
		'kurs'         => '1',
		'revisi'       => '0'
	);
}
$statusbloknya = array();
if (strlen($blok) == 10) {
	$whrcol = "kodeorg";
} else {
	$whrcol = "indukblok";
}

#pastikan jika kegiatan blok harus ada bloknya dan 10 digit !
$kelbyy = substr($param['kodekegiatan'], 0, 3);

if ($kelbyy == '126') {
	$whblok = " and statusblok='TBM' and luasareaproduktif>0  and " . $whrcol . " like '" . $blok . "%'";
} elseif ($kelbyy == '128') {
	if (substr($param['kodekegiatan'], 0, 5) == '12801') {
		$whblok = " and statusblok='BBT' and kodeorg like '%PN%'";
	} else {
		$whblok = " and statusblok='BBT' and kodeorg like '%MN%'";
	}
} elseif ($kelbyy == '621') {
	$whblok = " and statusblok='TM' and luasareaproduktif>0  and " . $whrcol . " like '" . $blok . "%'";
} elseif ($kelbyy == '611') {
	$whblok = " and statusblok='TM' and luasareaproduktif>0  and " . $whrcol . " like '" . $blok . "%'";
}

// $str="select indukblok,statusblok,kodeorg,tahuntanam from ".$dbname.".setup_blok where ".$whrcol."='".$blok."' ".$whblok."";
$str = "select indukblok,statusblok,kodeorg,tahuntanam from " . $dbname . ".setup_blok where " . $whrcol . "='" . $blok . "'";
$res = fetchdata($str);
foreach ($res as $v) {
	$statusbloknya[$v['statusblok']] = $v['statusblok'];
	$tahuntanamnya[$whrcol] = $v['tahuntanam'];
}

if ($kelbyy == '126') {

	$whblok = " and statusblok in ('TBM','TB','LC')
	and (luasareaproduktif > 0 OR lc > 0 OR luasbloking > 0)
	and " . $whrcol . " like '" . $blok . "%'";

	if (!array_intersect(['TBM', 'TB', 'LC'], $statusbloknya)) {
    exit("Gagal, Kegiatan " . getNamaKeg($param['kodekegiatan']) . " harus menggunakan blok TBM, TB, atau LC.");
	}

	if (!array_intersect(['TBM', 'TB', 'LC'], $statusbloknya)) {
    exit("Gagal, Kegiatan " . getNamaKeg($param['kodekegiatan']) . " harus menggunakan blok TBM, TB, atau LC.");
	}

} elseif ($kelbyy == '128') {
	if (substr($param['kodekegiatan'], 0, 5) == '12801') {
		$whblok = " and statusblok='BBT' and kodeorg like '%PN%'";
	} else {
		$whblok = " and statusblok='BBT' and kodeorg like '%MN%'";
	}

	if (!in_array('BBT', $statusbloknya)) {
		exit("Gagal, Kegiatan " . getNamaKeg($param['kodekegiatan']) . " harus menggunakan blok Bibitan.");
	}
} elseif ($kelbyy == '621') {

	$whblok = " and statusblok='TM' and luasareaproduktif>0  and " . $whrcol . " like '" . $blok . "%'";
	if (!in_array('TM', $statusbloknya)) {
		$ttnya = (date('Y') - $tahuntanamnya[$blok]);
		if ($ttnya < 3) {
			exit("Gagal, Kegiatan " . getNamaKeg($param['kodekegiatan']) . " harus menggunakan blok TM.");
		}
	}
} elseif ($kelbyy == '611') {

	$whblok = " and statusblok='TM' and luasareaproduktif>0  and " . $whrcol . " like '" . $blok . "%'";
	if (!in_array('TM', $statusbloknya)) {
		$ttnya = (date('Y') - $tahuntanamnya[$blok]);
		if ($ttnya < 3) {
			exit("Gagal, Kegiatan " . getNamaKeg($param['kodekegiatan']) . " harus menggunakan blok TM.");
		}
	}

} else {
	$whblok = " and kodeorg=''";
}

$listblok = array();
$str = "select * from " . $dbname . ".setup_blok where 1=1 " . $whblok . " and kodeorg like '" . $blok . "%'";
$res = fetchdata($str);
foreach ($res as $val) {
	$listblok[$val['kodeorg']] = $val['kodeorg'];
	$luasblok[$val['kodeorg']] += $val['luasareaproduktif'];
	$totalluas += $val['luasareaproduktif'];
	$jumlahblok++;
}

# Data Detail
$noUrut = 1;
$noUrut2 = 1;
# Debet
if (!empty($listblok)) {
	$temptotal = $temphasilkerja = 0;
	foreach ($listblok as $kodeblok) {
		$nomor++;

		// if($rcekjns[0]['jenis'] == 'ANGKUTTBS'){
		// 	if($nomor<$jlhblk){
		// 		$nilaidebet=round($ttlblokkecil[$kodeblok]/$ttlblokbesar*$param['jumlahrealisasi']);
		// 		$temptotal+=$nilaidebet;
		// 	}else{
		// 		$nilaidebet=round($param['jumlahrealisasi']-$temptotal);
		// 	}
		// }else{
		if ($nomor < $jumlahblok) {
			$nilaidebet = round($luasblok[$kodeblok] / $totalluas * $param['jumlahrealisasi'], 2);
			$temptotal += $nilaidebet;
			$nilaihasilkerja = round($luasblok[$kodeblok] / $totalluas * $param['hasilkerjarealisasi'], 2);
			$temphasilkerja += $nilaihasilkerja;
		} else {
			$nilaidebet = round($param['jumlahrealisasi'] - $temptotal, 2);
			$nilaihasilkerja = round($param['hasilkerjarealisasi'] - $temphasilkerja, 2);
		}
		// }
		if ($rcekjns[0]['jenis'] == 'ANGKUTTBS' || $rcekjns[0]['jenis'] == 'SEWA.HM') {
			if (substr($blok, 0, 4) == $rcekjns[0]['unit']) { //jika inti
				$dataRes['detail'][] = array(
					'nojurnal'    => $nojurnal,
					'tanggal'     => $tgl,
					'nourut'      => $noUrut,
					'noakun'      => ($optKeg[$param['kodekegiatan']] == null ? $optKegtrk[$param['kodekegiatan']] : $optKeg[$param['kodekegiatan']]),
					'keterangan'  => 'Realisasi SPK ' . $param['kodeorg'] . '/' . $param['notransaksi'],
					'jumlah'      => $nilaidebet,
					'matauang'    => 'IDR',
					'kurs'        => '1',
					'kodeorg'     => $param['kodeorg'],
					'kodekegiatan' => $param['kodekegiatan'],
					'kodeasset'   => $kodeasset,
					'kodebarang'  => '',
					'nik'         => '',
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => trim($param['notransaksi']),
					'noaruskas'   => '',
					'kodevhc'     => ($nmvhc[$kodeblok] != '' ? $kodeblok : ''),
					'nodok'       => trim($vNodok),
					'kodeblok'    => ($nmblok[$kodeblok] != '' ? $kodeblok : ''),
					'revisi'      => '0',
					'kodesegment' => $param['kodesegment']
				);
				$noUrut++;

				# Kredit
				$dataRes['detail'][] = array(
					'nojurnal'    => $nojurnal,
					'tanggal'     => $tgl,
					'nourut'      => $noUrut,
					'noakun'      => $optSupp[$param['koderekanan']],
					'keterangan'  => 'Realisasi SPK ' . $param['kodeorg'] . '/' . $param['notransaksi'],
					'jumlah'      => -1 * round($nilaidebet, 2),
					'matauang'    => 'IDR',
					'kurs'        => '1',
					'kodeorg'     => $param['kodeorg'],
					'kodekegiatan' => $param['kodekegiatan'],
					'kodeasset'   => '',
					'kodebarang'  => '',
					'nik'         => '',
					'kodecustomer' => '',
					'kodesupplier' => $param['koderekanan'],
					'noreferensi' => trim($param['notransaksi']),
					'noaruskas'   => '',
					'kodevhc'     => ($nmvhc[$kodeblok] != '' ? $kodeblok : ''),
					'nodok'       => trim($data[0]['keterangan']),
					'kodeblok'    => ($nmblok[$kodeblok] != '' ? $kodeblok : ''),
					'revisi'      => '0',
					'kodesegment' => $param['kodesegment']
				);
				$noUrut++;
			} else {
				#jurnal intra/interco
				// $lstPt[$_SESSION['empl']['kodeorganisasi']]=$_SESSION['empl']['kodeorganisasi'];
				// if(count($lstUnit)!=0){
				// foreach($lstUnit as $rw=>$rwUnit){
				// if(substr($blok,0,4)!=$rcekjns[0]['unit']){
				// if($lstPt[substr($blok,0,4)]!=$_SESSION['empl']['kodeorganisasi']){
				// 	$jenis="inter";
				// }else if($lstPt[substr($blok,0,4)]==$_SESSION['empl']['kodeorganisasi']){
				// 	if(substr($blok,0,4)!=$rcekjns[0]['unit']){
				$jenis = "intra";
				// 	}
				// }
				$aknPt = makeOption($dbname, 'keu_5caco', 'kodeorg,akunpiutang', "kodeorg='" . substr($blok, 0, 4) . "' and jenis='" . $jenis . "'");
				$aknHtg = makeOption($dbname, 'keu_5caco', 'kodeorg,akunhutang', "kodeorg='" . $rcekjns[0]['unit'] . "' and jenis='" . $jenis . "'");

				#debet
				if ($nilaidebet != 0) {
					$dataRes['detail'][] = array(
						'nojurnal'    => $nojurnal,
						'tanggal'     => $tgl,
						'nourut'      => $noUrut,
						'noakun'      => $aknPt[substr($blok, 0, 4)],
						'keterangan'  => 'Kirim RK Biaya Ke Plasma ' . $param['kodeorg'] . '/' . $param['notransaksi'],
						'jumlah'      => @$nilaidebet,
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => $rcekjns[0]['unit'],
						'kodekegiatan' => '',
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => $param['notransaksi'],
						'noaruskas'   => '',
						'kodevhc'     => '',
						'nodok'       => trim($data[0]['keterangan']),
						'kodeblok'    => '',
						'revisi'      => '0',
						'kodesegment' => $param['kodesegment']
					);

					$noUrut += 1;

					# Detail (Kredit) 
					$dataRes['detail'][] = array(
						'nojurnal'    => $nojurnal,
						'tanggal'     => $tgl,
						'nourut'      => $noUrut,
						'noakun'      => $optSupp[$param['koderekanan']],
						'keterangan'  => 'Realisasi SPK ' . $param['kodeorg'] . '/' . $param['notransaksi'],
						'jumlah'      => $nilaidebet * (-1),
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => $rcekjns[0]['unit'],
						'kodekegiatan' => $param['kodekegiatan'],
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer' => '',
						'kodesupplier' => $param['koderekanan'],
						'noreferensi' => trim($param['notransaksi']),
						'noaruskas'   => '',
						'kodevhc'     => ($nmvhc[$kodeblok] != '' ? $kodeblok : ''),
						'nodok'       => trim($data[0]['keterangan']),
						'kodeblok'    => ($nmblok[$kodeblok] != '' ? $kodeblok : ''),
						'revisi'      => '0',
						'kodesegment' => $param['kodesegment']
					);
					$noUrut += 1;
				}


				#debet disisi pemilik karyaawan
				if ($nilaidebet != 0) {
					$dataResRk['detail'][] = array(
						'nojurnal'    => $nojurnal2,
						'tanggal'     => $tgl,
						'nourut'      => $noUrut2,
						'noakun'      => $aknHtg[$rcekjns[0]['unit']],
						'keterangan'  => 'Terima RK Dari Inti ' . $param['kodeorg'] . '/' . $param['notransaksi'],
						'jumlah'      => @$nilaidebet * (-1),
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => substr($blok, 0, 4),
						'kodekegiatan' => '',
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => $param['notransaksi'],
						'noaruskas'   => '',
						'kodevhc'     => '',
						'nodok'       => trim($data[0]['keterangan']),
						'kodeblok'    => '',
						'revisi'      => '0',
						'kodesegment' => $param['kodesegment']
					);
					$noUrut2 += 1;

					# Detail (Kredit) 
					$dataResRk['detail'][] = array(
						'nojurnal'    => $nojurnal2,
						'tanggal'     => $tgl,
						'nourut'      => $noUrut2,
						'noakun'      => ($optKeg[$param['kodekegiatan']] == null ? $optKegtrk[$param['kodekegiatan']] : $optKeg[$param['kodekegiatan']]),
						'keterangan'  => 'Realisasi SPK ' . $param['kodeorg'] . '/' . $param['notransaksi'],
						'jumlah'      => $nilaidebet,
						'matauang'    => 'IDR',
						'kurs'        => '1',
						'kodeorg'     => substr($kodeblok, 0, 4),
						'kodekegiatan' => $param['kodekegiatan'],
						'kodeasset'   => '',
						'kodebarang'  => '',
						'nik'         => '',
						'kodecustomer' => '',
						'kodesupplier' => $param['koderekanan'],
						'noreferensi' => trim($param['notransaksi']),
						'noaruskas'   => '',
						'kodevhc'     => ($nmvhc[$kodeblok] != '' ? $kodeblok : ''),
						'nodok'       => trim($data[0]['keterangan']),
						'kodeblok'    => ($nmblok[$kodeblok] != '' ? $kodeblok : ''),
						'revisi'      => '0',
						'kodesegment' => $param['kodesegment']
					);
					$noUrut2 += 1;
				}
				// }
				$comment = '';
				$str = "select * from " . $dbname . ".setup_periodeakuntansi where tutupbuku=1 and kodeorg='" . substr($blok, 0, 4) . "' and periode='" . substr($dataH[0]['tanggal'], 0, 7) . "'";
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_OBJ);
				while ($bar = $res->fetch()) {
					$comment .= "Unit " . $bar->kodeorg . " periode " . $bar->periode . " has been closed\n";
				}
				// }
				#Cek apakah unit penerima RK sudah tutup buku
				if ($comment != '') {
					throw new PDOException($comment);
				}
				// }
			}
		} else { //SPK Selain ANGKUTTBS & SEWA.HM
			$dataRes['detail'][] = array(
				'nojurnal'    => $nojurnal,
				'tanggal'     => $tgl,
				'nourut'      => $noUrut,
				'noakun'      => $optKeg[$param['kodekegiatan']],
				'keterangan'  => 'Realisasi SPK ' . $param['kodeorg'] . '/' . $param['notransaksi'],
				'jumlah'      => $nilaidebet,
				'matauang'    => 'IDR',
				'kurs'        => '1',
				'kodeorg'     => $param['kodeorg'],
				'kodekegiatan' => $param['kodekegiatan'],
				'kodeasset'   => $kodeasset,
				'kodebarang'  => '',
				'nik'         => '',
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => trim($param['notransaksi']),
				'noaruskas'   => '',
				'kodevhc'     => ($nmvhc[$kodeblok] != '' ? $kodeblok : ''),
				'nodok'       => trim($vNodok),
				'kodeblok'    => ($nmblok[$kodeblok] != '' ? $kodeblok : ''),
				'revisi'      => '0',
				'kodesegment' => $param['kodesegment']
			);
			$noUrut++;

			# Kredit
			$dataRes['detail'][] = array(
				'nojurnal'    => $nojurnal,
				'tanggal'     => $tgl,
				'nourut'      => $noUrut,
				'noakun'      => $optSupp[$param['koderekanan']],
				'keterangan'  => 'Realisasi SPK ' . $param['kodeorg'] . '/' . $param['notransaksi'],
				'jumlah'      => -1 * round($nilaidebet, 2),
				'matauang'    => 'IDR',
				'kurs'        => '1',
				'kodeorg'     => $param['kodeorg'],
				'kodekegiatan' => $param['kodekegiatan'],
				'kodeasset'   => '',
				'kodebarang'  => '',
				'nik'         => '',
				'kodecustomer' => '',
				'kodesupplier' => $param['koderekanan'],
				'noreferensi' => trim($param['notransaksi']),
				'noaruskas'   => '',
				'kodevhc'     => ($nmvhc[$kodeblok] != '' ? $kodeblok : ''),
				'nodok'       => trim($data[0]['keterangan']),
				'kodeblok'    => ($nmblok[$kodeblok] != '' ? $kodeblok : ''),
				'revisi'      => '0',
				'kodesegment' => $param['kodesegment']
			);
			$noUrut++;
		}

		$sql = "select keterangan2,tanggal,hasilkerjarealisasi,jumlahrealisasi from $dbname.log_baspkdt where notransaksi='" . $param['notransaksi'] . "' and kodeblok='" . $blokparam . "' and kodekegiatan='" . $param['kodekegiatan'] . "' and termin='" . $param['termin'] . "' and keterangan='" . $param['nobapp'] . "' group by tanggal ";
		$hsl = fetchData($sql);
		foreach ($hsl as $val) {
			$qry = selectQuery($dbname, 'kebun_spbdt_detail', '*', "nospb='" . $val['keterangan2'] . "' and blok like '" . substr($kodeblok, 0, 9) . "%'");
			$rst = fetchData($qry);
			foreach ($rst as $v) {
				$hasilkerjakcl[$v['blok']][$v['nospb']] += $v['totalkg'];
			}
			if ($hasilkerjakcl[$kodeblok][$val['keterangan2']] == '') {
				$hasilkerjarealisasi = $val['hasilkerjarealisasi'];
			} else {
				$hasilkerjarealisasi = $hasilkerjakcl[$kodeblok][$val['keterangan2']];
			}
			if ($nilaihasilkerja == '' || $nilaihasilkerja == 0) {
				$nilaihslkrj = $val['jumlahrealisasi'];
			} else {
				$nilaihslkrj = $nilaihasilkerja;
			}
			$datadetail 	= array(
				'notransaksi'    		=> $param['notransaksi'],
				'kodeblok'    			=> ($nmblok[$kodeblok] != '' ? $kodeblok : ''),
				'kodekegiatan'      	=> $param['kodekegiatan'],
				'tanggal'      			=> $val['tanggal'],
				'hasilkerjarealisasi'  	=> $hasilkerjarealisasi,
				'hkrealisasi'      		=> $data[0]['hkrealisasi'],
				'jumlahrealisasi'    	=> (round($nilaidebet / $nilaihslkrj, 2) * $hasilkerjarealisasi),
				'termin'        		=> $param['termin'],
				'keterangan'     		=> $param['nobapp'],
				'keterangan2'			=> $val['keterangan2']
			);

			$colsdt = array();
			foreach ($datadetail as $kuy => $row) {
				$colsdt[] = $kuy;
			}

			$insDetail = insertQuery($dbname, 'log_baspkdt_detail', $datadetail, $colsdt);
			try {
				$owlPDO->exec($insDetail);
			} catch (PDOException $e) {
				echo "Insert Detail log_baspkdt_detail : " . $e->getMessage() . "\n" . $insDetail;
				exit;
			}
		}
	}
} else {
	$dataRes['detail'][] = array(
		'nojurnal'    => $nojurnal,
		'tanggal'     => $tgl,
		'nourut'      => $noUrut,
		'noakun'      => ($optKeg[$param['kodekegiatan']] == null ? $optKegtrk[$param['kodekegiatan']] : $optKeg[$param['kodekegiatan']]),
		'keterangan'  => 'Realisasi SPK ' . $param['kodeorg'] . '/' . $param['notransaksi'],
		'jumlah'      => round($param['jumlahrealisasi']),
		'matauang'    => 'IDR',
		'kurs'        => '1',
		'kodeorg'     => $param['kodeorg'],
		'kodekegiatan' => $param['kodekegiatan'],
		'kodeasset'   => $kodeasset,
		'kodebarang'  => '',
		'nik'         => '',
		'kodecustomer' => '',
		'kodesupplier' => '',
		'noreferensi' => trim($param['notransaksi']),
		'noaruskas'   => '',
		'kodevhc'     => ($nmvhc[$blok] != '' ? $blok : ''),
		'nodok'       => trim($vNodok),
		'kodeblok'    => ($nmblok[$blok] != '' ? $blok : ''),
		'revisi'      => '0',
		'kodesegment' => $param['kodesegment']
	);
	$noUrut++;

	# Kredit
	$dataRes['detail'][] = array(
		'nojurnal'    => $nojurnal,
		'tanggal'     => $tgl,
		'nourut'      => $noUrut,
		'noakun'      => $optSupp[$param['koderekanan']],
		'keterangan'  => 'Realisasi SPK ' . $param['kodeorg'] . '/' . $param['notransaksi'],
		'jumlah'      => -1 * round($hutang),
		'matauang'    => 'IDR',
		'kurs'        => '1',
		'kodeorg'     => $param['kodeorg'],
		'kodekegiatan' => $param['kodekegiatan'],
		'kodeasset'   => '',
		'kodebarang'  => '',
		'nik'         => '',
		'kodecustomer' => '',
		'kodesupplier' => $param['koderekanan'],
		'noreferensi' => trim($param['notransaksi']),
		'noaruskas'   => '',
		'kodevhc'     => ($nmvhc[$blok] != '' ? $blok : ''),
		'nodok'       => trim($data[0]['keterangan']),
		'kodeblok'    => ($nmblok[$blok] != '' ? $blok : ''),
		'revisi'      => '0',
		'kodesegment' => $param['kodesegment']
	);
	$noUrut++;
}


/*
foreach($optPajak as $noakun=>$nilai) {
	if($noakun!=$akunPpn) {
		$nilai = $nilai * (-1);
		$strPajak = 'Pph';
	} else {
		$strPajak = 'PPn';
	}
	$nilai = $nilai * $proporsi;
	
	$dataRes['detail'][] = array(
		'nojurnal'=>$nojurnal,
		'tanggal'=>$tgl,
		'nourut'=>$noUrut,
		'noakun'=>$noakun,
		'keterangan'=>$strPajak.' BASPK '.$param['kodeorg'].'/'.$param['notransaksi'],
		'jumlah'=>$nilai,
		'matauang'=>'IDR',
		'kurs'=>'1',
		'kodeorg'=>$param['kodeorg'],
		'kodekegiatan'=>$param['kodekegiatan'],
		'kodeasset'=>'',
		'kodebarang'=>'',
		'nik'=>'',
		'kodecustomer'=>'',
		'kodesupplier'=>$param['koderekanan'],
		'noreferensi'=>$param['notransaksi'],
		'noaruskas'=>'',
		'kodevhc'=>'',
		// 'nodok'=>$vNodok,
		'nodok'=>$param['notransaksi'],
		'kodeblok'=>$blok,
		'revisi'=>'0',
		'kodesegment' => $param['kodesegment']
	);
	$noUrut++;
}
*/

$tipenya = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');
$queri = selectQuery($dbname, 'organisasi', "kodeorganisasi,tipe,indukblok", "left(kodeorganisasi,9)='" . $blokparam . "'");
$hasil = fetchData($queri);
# Total D/K
$dataRes['header']['totaldebet'] = $hutang + $pph;
$dataRes['header']['totalkredit'] = $hutang + $pph;
if ($param['kodeorg'] != substr($param['blokalokasi'], 0, 4)) {
	if ($hasil[0]['tipe'] == 'BLOK') {
		$dataResRk['header']['totaldebet'] = $hutang + $pph;
		$dataResRk['header']['totalkredit'] = $hutang + $pph;
	}
}

// echo"<pre>";
// print_r($dataRes);
// exit("error");

#========================== Proses Insert dan Update ==========================
#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Header
$headErr = '';
$headErrRk = '';
$insHead = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
try {
	$owlPDO->exec($insHead);
} catch (PDOException $e) {
	$headErr .= 'Insert Header Error : ' . $e->getMessage() . "\n";
}

if ($headErr == '') {
	#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
	$detailErr = '';
	foreach ($dataRes['detail'] as $row) {
		$insDet = insertQuery($dbname, 'keu_jurnaldt', $row);

		try {
			$owlPDO->exec($insDet);
		} catch (PDOException $e) {
			$detailErr .= "Insert Detail Error : " . $e->getMessage() . "\n" . $insDet;
			break;
		}
	}

	if ($detailErr == '') {
		# Header and Detail inserted
		#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal

		$updJurnal = updateQuery(
			$dbname,
			'keu_5kelompokjurnal',
			array('nokounter' => $konter),
			"kodeorg='" . $kodept[$param['kodeorg']] . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . substr($tglsystemn, 0, 7) . "' "
		);
		try {
			$owlPDO->exec($updJurnal);

			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Status Posting
			$updTrans = updateQuery(
				$dbname,
				'log_baspk',
				array('statusjurnal' => 1),
				"notransaksi='" . $param['notransaksi'] .
					"' and kodeblok='" . $param['blokalokasi'] .
					"' and kodekegiatan='" . $param['kodekegiatan'] .
					"' and tanggal='" . $tgl .
					"' and termin='" . $param['termin'] .
					"' and kodesegment='" . $param['kodesegment'] . "'"
			);
			try {
				$owlPDO->exec($updTrans);
				echo '1';
			} catch (PDOException $e) {
				echo "Update Status Jurnal Error : " . $e->getMessage() . "\n";
				# Rollback if Update Failed
				$RBDet = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal . "'");
				try {
					$owlPDO->exec($RBDet);
				} catch (PDOException $e) {
					echo "Rollback Delete Header Error : " . $e->getMessage() . "\n";
					exit;
				}

				$RBJurnal = updateQuery(
					$dbname,
					'keu_5kelompokjurnal',
					array('nokounter' => $konter - 1),
					"kodeorg='" . $kodept[$param['kodeorg']] . "' and kodekelompok='" . $kodeJurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . substr($tglsystemn, 0, 7) . "' "
				);
				try {
					$owlPDO->exec($RBJurnal);
				} catch (PDOException $e) {
					echo "Rollback Update Jurnal Error : " . $e->getMessage() . "\n";
					exit;
				}
				exit;
			}
		} catch (PDOException $e) {
			echo "Update Kode Jurnal Error : " . $e->getMessage() . "\n";
			# Rollback if Update Failed
			$RBDet = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal . "'");
			try {
				$owlPDO->exec($RBDet);
			} catch (PDOException $e) {
				echo "Rollback Delete Header Error : " . $e->getMessage() . "\n";
				exit;
			}
			exit;
		}
	} else {
		echo $detailErr;
		# Rollback, Delete Header
		$RBDet = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal . "'");
		try {
			$owlPDO->exec($RBDet);
		} catch (PDOException $e) {
			echo "Rollback Delete Header Error : " . $e->getMessage();
			exit;
		}
	}
} else {
	echo $headErr;
	exit;
}

if ($hasil[0]['tipe'] == 'BLOK') {
	if (count($dataResRk['header']) != '' || count($dataResRk['header']) > 0) {
		$insHeadRk = insertQuery($dbname, 'keu_jurnalht', $dataResRk['header']);
		try {
			$owlPDO->exec($insHeadRk);
		} catch (PDOException $e) {
			$headErrRk .= 'Insert Header Error : ' . $e->getMessage() . "\n";
		}
	}
	$insDetRk = '';
	if ($headErrRk == '' && (count($dataResRk['header']) != '' || count($dataResRk['header']) > 0)) {
		#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Insert Detail
		$detailErrRk = '';
		foreach ($dataResRk['detail'] as $row) {
			$insDetRk .= insertQuery($dbname, 'keu_jurnaldt', $row) . ";";
		}
		if ($insDetRk != '') {
			try {
				$owlPDO->exec($insDetRk);
			} catch (PDOException $e) {
				$detailErrRk .= "Insert Detail Error : " . $e->getMessage() . "\n" . $insDetRk;
				// break;
			}
		}

		if ($detailErrRk == '') {
			# Header and Detail inserted
			#>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Kode Jurnal

			$updJurnal = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $konter2),
				"kodeorg='" . $kodept[substr($blok, 0, 4)] . "' and kodekelompok='" . $kodeJurnalMemo . "' and kodeunit='" . substr($blok, 0, 4) . "' and periode='" . substr($tglsystemn, 0, 7) . "' "
			);
			try {
				$owlPDO->exec($updJurnal);

				// #>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> Update Status Posting
				// $updTrans = updateQuery($dbname,'log_baspk',array('statusjurnal'=>1),
				//     "notransaksi='".$param['notransaksi'].
				//     "' and kodeblok='".$param['blokalokasi'].
				//     "' and kodekegiatan='".$param['kodekegiatan'].
				//     "' and tanggal='".$tgl.
				// 	"' and termin='".$param['termin'].
				// 	"' and kodesegment='".$param['kodesegment']."'");
				// try{
				// 	$owlPDO->exec($updTrans); 
				// 	echo '1';
				// }catch (PDOException $e){
				// 	echo "Update Status Jurnal Error : ".$e->getMessage()."\n";
				//     # Rollback if Update Failed
				//     $RBDet = deleteQuery($dbname,'keu_jurnalht',"nojurnal='".$nojurnal."'");
				// 	try{
				// 		$owlPDO->exec($RBDet); 
				// 	}catch (PDOException $e){
				// 		echo "Rollback Delete Header Error : ".$e->getMessage()."\n";
				//         exit;
				// 	}

				//     $RBJurnal = updateQuery($dbname,'keu_5kelompokjurnal',array('nokounter'=>$konter-1),
				//          "kodeorg='".$kodept[substr($blok,0,4)]."' and kodekelompok='".$kodeJurnal."' and kodeunit='".substr($blok,0,4)."' and periode='".substr($tglsystemn,0,7)."' ");
				// 	try{
				// 		$owlPDO->exec($RBJurnal); 
				// 	}catch (PDOException $e){
				// 		echo "Rollback Update Jurnal Error : ".$e->getMessage()."\n";
				//         exit;
				// 	}
				//     exit;
				// }
			} catch (PDOException $e) {
				echo "Update Kode Jurnal Error : " . $e->getMessage() . "\n";
				# Rollback if Update Failed
				$RBDet = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal2 . "'");
				try {
					$owlPDO->exec($RBDet);
				} catch (PDOException $e) {
					echo "Rollback Delete Header Error : " . $e->getMessage() . "\n";
					exit;
				}
				exit;
			}
		} else {
			echo $detailErrRk;
			# Rollback, Delete Header
			$RBDet = deleteQuery($dbname, 'keu_jurnalht', "nojurnal='" . $nojurnal2 . "'");
			try {
				$owlPDO->exec($RBDet);
			} catch (PDOException $e) {
				echo "Rollback Delete Header Error : " . $e->getMessage();
				exit;
			}
		}
	} else {
		echo $headErrRk;
		exit;
	}
}
