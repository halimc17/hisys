<? //@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param = $_POST;

if ($param['cgttu'] == 'Transfer') {
	# Cek tidak boleh kosong
	if ($param['nocekinput'] == '') {
		exit("<label hidden>Warning</label> Untuk Tipe Bayar <b>Transfer</b> Bukti Bayar Internet Banking / MCM belum diiisi!");
	}
	# Cek harus kosong
	if ($param['nocek'] != '') {
		exit("<label hidden>Warning</label> Untuk Tipe Bayar <b>Transfer</b> tidak dapat mengisi Bukti Pembayaran dari Nomor Cek (Buku Cek)");
	}
	# Cek jika tidak kosong, param['nocek'] ganti valuenya dengan param['nocekinput']
	if ($param['nocekinput'] != '') {

		#================================#
		# JIKA TIPE TRANSFER
		# TUKAR POSISI
		# TAMPUNG PARAM['NOCEK'] => PARAM['NOCEKORI']
		# UBAH VALUE PARAM['NOCEK'] => PARAM['NOCEKINPUT']
		# UBAH VALUE PARAM['NOCEKINPUT'] => PARAM['NOCEKORI']
		# JADINYA SEPERTI INI :
		# PARAM['NOCEK'] <==> PARAM['NOCEKINPUT'] TUKAR VALUE
		# KARENA, AGAR TIDAK MENGUBAH BANYAK VARIABEL
		#================================#

		# Backup param['nocek'] ke nocekori
		# Sebelum di ubah menjadi input
		$param['nocekori'] = $param['nocek'];

		# Ubah nocek, menajadi nocekinput
		$param['nocek'] = $param['nocekinput'];

		# Ubah nocekinput menjadi nocek dari nocekori
		$param['nocekinput'] = $param['nocekori'];
	}
}
// else {
// 	# Cek tidak boleh kosong
// 	if($param['nocek'] == '') {
// 		exit("<label hidden>Warning</label> Untuk Tipe Bayar <b>".$param['cgttu']."</b> Bukti Pembayaran dari Nomor Cek (Buku Cek) belum diisi!");
// 	}
// 	# Cek harus kosong
// 	if($param['nocekinput'] != '') {
// 		exit("<label hidden>Warning</label> Untuk Tipe Bayar <b>".$param['cgttu']."</b> tidak dapat mengisi Bukti Pembayaran dari Internet Banking / MCM");
// 	}
// }

// echo "<pre>";
// print_r($param);
// exit('warning');

$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $param['notransaksi'] . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$pembayaran = $bar['pembayaran'];

#= pengecekan jika status pembayaran sudah 1 maka exit;	
if ($pembayaran == '1') {
	exit("Warning:Transaksi kasir sudah dilakukan, silahkan klik List data untuk melakukan refresh transaksi");
}


// if($param['tipetransaksi']=='K'){
validasiInput(substr($param['kodeorg'], 0, 4), '', 'KSR', tanggalsystemn($param['tglpost']), $exit = '1');
// }

// exit("Error".$param['tglpost']);
$tglpost = tanggalsystem($param['tglpost']);
$periodejurnal = substr(tanggalsystemn($param['tglpost']), 0, 7);



#= bentuk data kodept	
$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)=4";
$res = $owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$kodept[$bar['kodeorganisasi']] = $bar['induk'];
}



$sPeriode = "select * from " . $dbname . ".setup_periodeakuntansi 
		   where kodeorg='" . $param['kodeorg'] . "' and periode='" . $periodejurnal . "' and tutupbuku=0 order by periode desc";
$rPeriode = fetchdata($sPeriode);
$tglakutansi = str_replace("-", "", $rPeriode[0]['tanggalmulai']);
if ($tglakutansi > $tglpost) {
	exit('Warning:Gagal posting, Tanggal voucher dibawah periode aktif unit ' . $param['kodeorg'] . ', tanggal voucher ' . tanggalnormal($tglpost) . ', periode aktif : ' . $rPeriode[0]['periode'] . ' (' . tanggalnormal($rPeriode[0]['tanggalmulai']) . ' s/d ' . tanggalnormal($rPeriode[0]['tanggalsampai']) . ') ');
}



$whereAKB = "kodeaplikasi='GL' and aktif=1 and jurnalid!= 'M'";
$queryAKB = selectQuery(
	$dbname,
	'keu_5parameterjurnal',
	'jurnalid,noakundebet,sampaidebet,noakunkredit,sampaikredit',
	$whereAKB
);
$optAKB = fetchData($queryAKB);
$tipe = "";
foreach ($optAKB as $row) {
	if ($param['tipetransaksi'] == 'K') {
		if ($param['noakun'] >= $row['noakunkredit'] and $param['noakun'] <= $row['sampaikredit']) {
			$tipe = $row['jurnalid'];
		}
	} else {
		if ($param['noakun'] >= $row['noakundebet'] and $param['noakun'] <= $row['sampaidebet']) {
			$tipe = $row['jurnalid'];
		}
	}
}

if ($tipe == 'BK' || $tipe == 'BM') {
	if ($param['rekening'] == '') {
		exit("Warning:Jika Bank Masuk/Keluar harap rekening diisi");
	}
}

// cek apakah sudah ada jurnal atas no transaksi ini
$adajurnal = '';
$str = "select nojurnal from " . $dbname . ".keu_jurnalht where noreferensi='" . $param['notransaksi'] . "'";
$res = $owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$adajurnal .= $bar['nojurnal'] . ', ';
}
if ($adajurnal != '') {
	exit("Error: Sudah ada jurnal atas notransaksi " . $param['notransaksi'] . " :\n " . $adajurnal . " \n Silakan periksa kembali. ");
}


// print_r($_SESSION['bgnotrans'][0]['notransaksi']);exit("error:A");

$str = "SELECT * FROM " . $dbname . ".keu_kasbankht where notransaksi='" . $_SESSION['bgnotrans'][0]['notransaksi'] . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$novoucher = $bar['novoucher'];

#= cek apakah sudah ada nomor voucher untuk transaksi terkirim

#= jika nomor vocer sudah ada 
// echo "<pre><br>";
// echo "BLAA ::".$novoucher;

if ($novoucher == '') {

	if ($param['rekening'] != '') {
		$str = "SELECT inisialurut FROM " . $dbname . ".keu_5akunbank where noakun='" . $param['rekening'] . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$inisialurut = $bar['inisialurut'];

		$novoucherlike = substr($tglpost, 2, 4)."/" . $param['kodeorg'] . "/B" . $param['tipetransaksi'] . "/" . $inisialurut . "/";
		$str = "SELECT novoucher FROM " . $dbname . ".keu_kasbankht where 
			novoucher like '%" . $novoucherlike . "%' and
			tanggal like '" . substr(tanggalsystemn($param['tglpost']), 0, 7) . "%' 
			order by novoucher desc limit 1";

		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$datanovoucher = $bar['novoucher'];
	} else {
		if ($param['noakun2a'] == '1112104') {
			$inisialurut = 'COH';
		} else {
			$inisialurut = 'KAS';
		}
		$novoucherlike = substr($tglpost, 2, 4)."/" . $param['kodeorg'] . "/K" . $param['tipetransaksi'] . "/" . $inisialurut . "/";
		$str = "SELECT novoucher FROM " . $dbname . ".keu_kasbankht where 
		novoucher like '%" . $novoucherlike . "%' and
		tanggal like '" . substr(tanggalsystemn($param['tglpost']), 0, 7) . "%' 
		order by novoucher desc limit 1";

		// exit("Error:$str");
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$datanovoucher = $bar['novoucher'];
	}

	if ($datanovoucher == '') {
		$nourutvoucher = 1;
	} else {
		$expldatanovoucher = explode('/', $datanovoucher);
		$nourutvoucher = $expldatanovoucher[4] + 1;
	}

	$explnotran = explode('/', $param['notransaksi']);

	$novoucher = substr($tglpost, 2, 4) . '/' . $explnotran[1] . '/' . $explnotran[2] . '/' . $inisialurut . '/' . addZero($nourutvoucher, 5);

	// echo "<br>tglpost ::".$tglpost;

	// echo "<br>BLAA2 ::".print_r($explnotran);

	// exit ($novoucher." ==== ".$verivnovocher."    ERROR");

	#= cek data novoucher
	if ($datanovoucher != '') {
		$expldatanovocher = explode('/', $datanovoucher);
		$verivnovocher = $expldatanovocher[0] . '/' . $expldatanovocher[1] . '/' . $expldatanovocher[2] . '/' . $expldatanovocher[3] . '/' . addZero(($expldatanovocher[4] + 1), 5);
		if ($novoucher != $verivnovocher) {
			exit("Warning:Novoucher tidak sesuai, silahkan posting ulang transaksi | Voucher terbentuk : " . $novoucher . " ; Voucher seharusnya " . $verivnovocher . " ");
		}
	}
}





if ($novoucher == '') {
	exit("Warning:Novoucher tidak terbentuk, silahkan posting ulang transaksi");
}


$varcounter = array();

try {

	$owlPDO->beginTransaction();

	foreach ($_SESSION['bgnotrans'] as $key => $row) {

		$param['notransaksi'] = $row['notransaksi'];

		#= bentuk nojurnal pengirim
		$arrunitdt[$param['kodeorg']] = $param['kodeorg'];
		$str = "select distinct(pemilikhutang1) as pemilikhutang1,kode from " . $dbname . ".keu_kasbankdtht_vw where notransaksi='" . $param['notransaksi'] . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			if ($bar['pemilikhutang1'] != '') {
				$arrunitdt[$bar['pemilikhutang1']] = $bar['pemilikhutang1'];
			}
			$kodejurnal = $bar['kode'];
		}

		#1. Data Header
		# Get Journal Counter
		foreach ($arrunitdt as $unitdt) {

			#= cek periode akuntansi
			$str = "select * from " . $dbname . ".setup_periodeakuntansi where 
				kodeorg='" . $unitdt . "' and tutupbuku=0 order by periode asc limit 1 ";
			$res = fetchdata($str);
			$tanggalmulai = $res[0]['tanggalmulai'];
			$tanggalsampai = $res[0]['tanggalsampai'];


			if ($tanggalmulai > $tglpost) {
				throw new PDOException("Tanggal Transaksi : " . $param['tanggal'] . " melebihi periode aktif, periode aktif untuk unit " . $unitdt . " : " . tanggalnormal($tanggalmulai) . " s/d " . tanggalnormal($tanggalsampai) . " ");
			}

			$varcounter[$unitdt]++;


			if ($param['kodeorg'] == $unitdt) {
				$query = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodekelompok='" . $kodejurnal . "' and kodeunit='" . $unitdt . "' and periode='" . $periodejurnal . "'"
				);

				$res = fetchdata($query);
				$countdata = count($res);

				if ($countdata <= 0) {
					exit("warning : " . $kodejurnal . " pada unit " . $unitdt . " dan periode " . $periodejurnal . " belum terdaftar!");
				}

				// cek no urut si jurnalht -> bandingin siapa yg lebih tinggi(kel.jurnal) -> 
				//jika jurnal ht > kel.jurnal = update no counter. kel.jurnal
				//jika kel.jurnal > jurnalht => ... 

				$nilaikounter = (isset($res[0]['nokounter'])) ? (int)$res[0]['nokounter'] : 0;

				$strjurnalht = selectQuery(
					$dbname,
					'keu_jurnalht',
					'nojurnal',
					" kodejurnal = '" . $kodejurnal . "' AND nojurnal LIKE '%/" . $unitdt . "/%' AND tanggal LIKE '" . $periodejurnal . "%' ORDER BY nojurnal DESC LIMIT 1"
				);

				$resjurht = fetchData($strjurnalht);
				$konterht = 0;
				if (isset($resjurht[0]['nojurnal'])) {
					$parts = explode('/', $resjurht[0]['nojurnal']);
					$konterht = (int)end($parts);
				}

				if ($konterht > $nilaikounter) {
					$konterupdtstr = str_pad($konterht, 3, '0', STR_PAD_LEFT);
					$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konterupdtstr . "' where 
					kodeunit='" . $unitdt . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $periodejurnal . "' ";
					$owlPDO->exec($str);
					// $tmpKonter = fetchData($query);
				}

				$tmpKonter = fetchData($query);
				$konter = addZero($tmpKonter[0]['nokounter'] + $varcounter[$unitdt], 3);
				# Prep No Jurnal
				$nojurnaltemp = str_replace('-', '', tanggalsystemn($param['tglpost'])) . "/" . $unitdt . "/" . $kodejurnal . "/" . $konter;
				$nojurnal[$unitdt] = $nojurnaltemp;

				$dataRes['header'][] = array(
					'nojurnal' => $nojurnal[$unitdt],
					'kodejurnal' => $kodejurnal,
					'tanggal' => tanggalsystemn($param['tglpost']),
					'tanggalentry' => date('Ymd'),
					'posting' => '0',
					'totaldebet' => '0',
					'totalkredit' => '0',
					'amountkoreksi' => '0',
					'noreferensi' => $param['notransaksi'],
					'autojurnal' => '1',
					'matauang' => 'IDR',
					'kurs' => '1',
					'revisi' => '0'
				);

				#= update counter jurnal
				$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
					kodeunit='" . $unitdt . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $periodejurnal . "' ";
				$owlPDO->exec($str);
			} else {
				$kodejurnal = 'M';
				$query = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodekelompok='" . $kodejurnal . "' and kodeunit='" . $unitdt . "' and periode='" . $periodejurnal . "'"
				);

				$res = fetchdata($query);
				$countdata =  count($res);
				if ($countdata <= 0) {
					exit("warning : " . $kodejurnal . " pada unit " . $unitdt . " dan periode " . $periodejurnal . " belum terdaftar!");
				}

				// cek no urut si jurnalht -> bandingin siapa yg lebih tinggi(kel.jurnal) -> 
				//jika jurnal ht > kel.jurnal = update no counter. kel.jurnal
				//jika kel.jurnal > jurnalht => ... 

				// $strkeljurnal = selectQuery(
				// 	$dbname, 
				// 	'keu_5kelompokjurnal', 
				// 	'nokounter',
				// 	"kodekelompok='" . $kodejurnal . "' and kodeunit='" . $unitdt . "' and periode='" . $periodejurnal . "'");

				$nilaikounter = (isset($res[0]['nokounter'])) ? (int)$res[0]['nokounter'] : 0;

				$strjurnalht = selectQuery(
					$dbname,
					'keu_jurnalht',
					'nojurnal',
					" kodejurnal = '" . $kodejurnal . "' AND nojurnal LIKE '%/" . $unitdt . "/%' AND tanggal LIKE '" . $periodejurnal . "%' ORDER BY CAST(SUBSTRING_INDEX(nojurnal, '/', -1) AS UNSIGNED) DESC LIMIT 1"
				);

				$resjurht = fetchData($strjurnalht);

				$konterht = 0;
				if (isset($resjurht[0]['nojurnal'])) {
					$parts = explode('/', $resjurht[0]['nojurnal']);
					$konterht = (int)end($parts);
				}

				if ($konterht > $nilaikounter) {
					$konterupdtstr = str_pad($konterht, 3, '0', STR_PAD_LEFT);
					$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konterupdtstr . "' where 
					kodeunit='" . $unitdt . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $periodejurnal . "' ";
					$owlPDO->exec($str);
					$tmpKonter = fetchData($query);
				}

				$tmpKonter = fetchData($query);
				$konter = addZero($tmpKonter[0]['nokounter'] + $varcounter[$unitdt], 3);
				# Prep No Jurnal
				$nojurnaltemp = str_replace('-', '', tanggalsystemn($param['tglpost'])) . "/" . $unitdt . "/" . $kodejurnal . "/" . $konter;
				$nojurnal[$unitdt] = $nojurnaltemp;

				$dataRes['header'][] = array(
					'nojurnal' => $nojurnal[$unitdt],
					'kodejurnal' => $kodejurnal,
					'tanggal' => tanggalsystemn($param['tglpost']),
					'tanggalentry' => date('Ymd'),
					'posting' => '0',
					'totaldebet' => '0',
					'totalkredit' => '0',
					'amountkoreksi' => '0',
					'noreferensi' => $param['notransaksi'],
					'autojurnal' => '1',
					'matauang' => 'IDR',
					'kurs' => '1',
					'revisi' => '0'
				);

				#= update counter jurnal
				$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
					kodeunit='" . $unitdt . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $periodejurnal . "' ";
				$owlPDO->exec($str);
			}
		}

		// print_r($nojurnal);exit("Error:A");


		$noUrut = $noUrutcaco = 0;

		#= data detail total
		#= Jurnal Kas/Bank hanya 1
		// $str="SELECT sum(jumlah) as jumlah,kodeorg,hutangunit1,pemilikhutang1,tipetransaksi,kodesegment,notransaksi,keterangan,noakun2a,kurs
		// 		FROM ".$dbname.".keu_kasbankdtht_vw WHERE notransaksi='".$param['notransaksi']."'";
		// FROM ".$dbname.".keu_kasbankdtht_vw WHERE notransaksi='".$param['notransaksi']."' group by pemilikhutang1";
		# End

		#= Jurnal Kas/Bank Per Detail
		$str = "SELECT jumlah,kodeorg,hutangunit1,pemilikhutang1,tipetransaksi,kodesegment,notransaksi,keterangan,noakun2a,kurs
				FROM " . $dbname . ".keu_kasbankdtht_vw WHERE notransaksi='" . $param['notransaksi'] . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {

			if ($bar['tipetransaksi'] == 'K') {
				$bar['jumlah'] = $bar['jumlah'] * -1;
			}

			$noUrut++;
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal[$bar['kodeorg']],
				'tanggal' => tanggalsystemn($param['tglpost']),
				'nourut' => $noUrut,
				'noakun' => $bar['noakun2a'],
				'keterangan' => $bar['keterangan'],
				// 'jumlah'=>round($bar['jumlah']*$bar['kurs'],0),
				'jumlah' => $bar['jumlah'] * $bar['kurs'], # Hilangkan Rounding
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $bar['kodeorg'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => $bar['notransaksi'],
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $bar['kodesegment']
			);
		}

		#= data detail
		$str = "select * from " . $dbname . ".keu_kasbankdtht_vw where notransaksi='" . $param['notransaksi'] . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {

			if ($bar['noaruskas'] == '') {
				throw new PDOException("Ada data dengan aruskasnya belum terisi, silahkan dicek kembali data detail transaksi");
			}

			if ($bar['tipetransaksi'] == 'M') {
				$bar['jumlah'] = $bar['jumlah'] * -1;
			}
			if ($bar['hutangunit1'] == 0) {
				$noUrut++;
				#= jurnal pengirim
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal[$bar['kodeorg']],
					'tanggal' => tanggalsystemn($param['tglpost']),
					'nourut' => $noUrut,
					'noakun' => $bar['noakun'],
					'keterangan' => $bar['keterangan2'],
					// 'jumlah'=>round($bar['jumlah']*$bar['kurs']),
					'jumlah' => $bar['jumlah'] * $bar['kurs'], # Hilangkan Rounding
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $bar['kodeorg'],
					'kodekegiatan' => $bar['kodekegiatan'],
					'kodeasset' => $bar['kodeasset'],
					'kodebarang' => $bar['kodebarang'],
					'nik' => $bar['nik'],
					'kodecustomer' => $bar['kodecustomer'],
					'kodesupplier' => $bar['kodesupplier'],
					'noreferensi' => $bar['notransaksi'],
					'noaruskas' => $bar['noaruskas'],
					'kodevhc' => $bar['kodevhc'],
					'nodok' => $bar['nodok'],
					'kodeblok' => $bar['orgalokasi'],
					'revisi' => '0',
					'kodesegment' => $bar['kodesegment']
				);
			} else {

				#= jurnal pengirim
				#= akun ganti CACO

				if ($kodept[$bar['pemilikhutang1']] == $kodept[$bar['kodeorg']]) $jenisinduk = 'intra';
				else $jenisinduk = 'inter';

				$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $bar['pemilikhutang1'] . "'";
				$query = selectQuery($dbname, 'keu_5caco', 'akunpiutang', $whereNocaco);
				$noKon = fetchData($query);
				$noakuncaco = $noKon[0]['akunpiutang'];

				if ($noakuncaco == '') {
					throw new PDOException("No. Akun Interco/Intraco masih kosong untuk " . $bar['kodeorg'] . " ke " . $bar['pemilikhutang1'] . " atau sebaliknya, Hubungi Pihak Accounting / IT ");
				}

				$noUrut++;
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal[$bar['kodeorg']],
					'tanggal' => tanggalsystemn($param['tglpost']),
					'nourut' => $noUrut,
					'noakun' => $noakuncaco,
					'keterangan' => $bar['keterangan2'],
					// 'jumlah'=>round($bar['jumlah']*$bar['kurs']),
					'jumlah' => $bar['jumlah'] * $bar['kurs'], # Hilangkan Rounding
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $bar['kodeorg'],
					'kodekegiatan' => $bar['kodekegiatan'],
					'kodeasset' => $bar['kodeasset'],
					'kodebarang' => $bar['kodebarang'],
					'nik' => $bar['nik'],
					'kodecustomer' => $bar['kodecustomer'],
					'kodesupplier' => $bar['kodesupplier'],
					'noreferensi' => $bar['notransaksi'],
					'noaruskas' => $bar['noaruskas'],
					'kodevhc' => $bar['kodevhc'],
					'nodok' => $bar['nodok'],
					'kodeblok' => $bar['orgalokasi'],
					'revisi' => '0',
					'kodesegment' => $bar['kodesegment']
				);

				#= jurnal penerima
				#= akun CACO
				$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $bar['kodeorg'] . "'";
				$query = selectQuery($dbname, 'keu_5caco', 'akunhutang', $whereNocaco);
				$noKon = fetchData($query);
				$noakuncaco = $noKon[0]['akunhutang'];

				if ($noakuncaco == '') {
					throw new PDOException("No. Akun Interco/Intraco masih kosong untuk " . $bar['kodeorg'] . " ke " . $bar['pemilikhutang1'] . " atau sebaliknya, Hubungi Pihak Accounting / IT ");
				}

				$noUrutcaco++;
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal[$bar['pemilikhutang1']],
					'tanggal' => tanggalsystemn($param['tglpost']),
					'nourut' => $noUrutcaco,
					'noakun' => $noakuncaco,
					'keterangan' => $bar['keterangan2'],
					// 'jumlah'=>round($bar['jumlah']*$bar['kurs'],0)*-1,
					'jumlah' => ($bar['jumlah'] * $bar['kurs']) * -1, # Hilangkan Rounding
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $bar['pemilikhutang1'],
					'kodekegiatan' => $bar['kodekegiatan'],
					'kodeasset' => $bar['kodeasset'],
					'kodebarang' => $bar['kodebarang'],
					'nik' => $bar['nik'],
					'kodecustomer' => $bar['kodecustomer'],
					'kodesupplier' => $bar['kodesupplier'],
					'noreferensi' => $bar['notransaksi'],
					'noaruskas' => $bar['noaruskas'],
					'kodevhc' => $bar['kodevhc'],
					'nodok' => $bar['nodok'],
					'kodeblok' => $bar['orgalokasi'],
					'revisi' => '0',
					'kodesegment' => $bar['kodesegment']
				);
				#= akun biayanya
				$noUrutcaco++;
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal[$bar['pemilikhutang1']],
					'tanggal' => tanggalsystemn($param['tglpost']),
					'nourut' => $noUrutcaco,
					'noakun' => $bar['noakun'],
					'keterangan' => $bar['keterangan2'],
					// 'jumlah'=>round($bar['jumlah']*$bar['kurs'],0),
					'jumlah' => $bar['jumlah'] * $bar['kurs'], # Hilangkan Rounding	
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $bar['pemilikhutang1'],
					'kodekegiatan' => $bar['kodekegiatan'],
					'kodeasset' => $bar['kodeasset'],
					'kodebarang' => $bar['kodebarang'],
					'nik' => $bar['nik'],
					'kodecustomer' => $bar['kodecustomer'],
					'kodesupplier' => $bar['kodesupplier'],
					'noreferensi' => $bar['notransaksi'],
					'noaruskas' => $bar['noaruskas'],
					'kodevhc' => $bar['kodevhc'],
					'nodok' => $bar['nodok'],
					'kodeblok' => $bar['orgalokasi'],
					'revisi' => '0',
					'kodesegment' => $bar['kodesegment']
				);
			}
		}

		#= update no vocer
		// $str="update ".$dbname.".keu_kasbankht set novoucher='".$novoucher."',pembayaran=1, where 
		// notransaksi='".$param['notransaksi']."'";	
		$str = "update " . $dbname . ".keu_kasbankht set 
		noakun='" . $param['noakun2a'] . "',
		tanggal='" . tanggalsystemn($param['tglpost']) . "',
		rekening='" . $param['rekening'] . "',
		cgttu='" . $param['cgttu'] . "',
		nocek='" . $param['nocek'] . "',
		namabank='" . $param['namabank'] . "',
		rekeningext='" . $param['rekeningext'] . "',
		anrekeningext='" . $param['anrekeningext'] . "',
		kasir='" . $_SESSION['standard']['userid'] . "',
		pembayaran=1,
		novoucher='" . $novoucher . "'
		where notransaksi = '" . $param['notransaksi'] . "' and noakun='" . $param['noakun'] . "' 
		and tipetransaksi='" . $param['tipetransaksi'] . "' and kodeorg='" . $param['kodeorg'] . "' ";
		// exit("Error:$str");		
		$owlPDO->exec($str);

		/** NOTIF ****************************************************/

		#= cek disini jika detail adalah akun intra/interco
		#= untuk notifikasi ke lawannya

		/*
	cek disini jika detail adalah akun intra/interco
	untuk notifikasi ke lawannya
	kondisinya : 
	1. jika hutang unit didetail = 1
	2. jika akun detail terisi akun intraco/interco
	*/

		#= buat daftar akun caco
		$str = "select * from " . $dbname . ".keu_5caco";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$akuncaco[$bar['akunpiutang']] = $bar['akunpiutang'];
			$akuncaco[$bar['akunhutang']] = $bar['akunhutang'];
			$unitcaco[$bar['akunpiutang']] = $bar['kodeorg'];
			$unitcaco[$bar['akunhutang']] = $bar['kodeorg'];
		}

		$tipeorganisasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');

		$str = "select * from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {

			#= jika noakun termasuk caco
			if (in_array($bar['noakun'], $akuncaco)) {

				$tporg = $tipeorganisasi[$unitcaco[$bar['noakun']]];
				// exit("Error:".$tporg);
				if ($tporg == 'HOLDING') {
					$stru = "select * from " . $dbname . ".datakaryawan where 
				bagian in (select kodedepartement from " . $dbname . ".setup_notification_dt where kodejenis='KBCACO')  ";
					$resu = $owlPDO->query($stru) or die(print " Gagal: " . PDOException::getMessage());
					$resu->setFetchMode(PDO::FETCH_ASSOC);
					while ($baru = $resu->fetch()) {
						$notrk = $bar['notransaksi'];
						$tipe = 'KBCACO';
						$msgdt = "Notification/Pemberitahuan adanya biaya yang dikirim dari unit " . $param['kodeorg'] . " untuk dijurnal alokasi, sejumlah " . $bar['jumlah'] . "; Nomor transaksi : " . $param['notransaksi'] . "; Keterangan : " . $dataH[0]['keterangan'] . " ";
						$createby = $baru['karyawanid'];
						$tanggal = $tglpost;
						createnotif($notrk, $tipe, $msgdt, $createby, $tanggal);
					}
				} else if ($tporg == 'KANWIL') {
					$stru = "select * from " . $dbname . ".datakaryawan where 
				bagian in (select kodedepartement from " . $dbname . ".setup_notification_dt where kodejenis='KBCACO')  ";
					$resu = $owlPDO->query($stru) or die(print " Gagal: " . PDOException::getMessage());
					$resu->setFetchMode(PDO::FETCH_ASSOC);
					while ($baru = $resu->fetch()) {
						$notrk = $bar['notransaksi'];
						$tipe = 'KBCACO';
						$msgdt = "Notification/Pemberitahuan adanya biaya yang dikirim dari unit " . $param['kodeorg'] . " untuk dijurnal alokasi, sejumlah " . $bar['jumlah'] . "; Nomor transaksi : " . $param['notransaksi'] . "; Keterangan : " . $dataH[0]['keterangan'] . " ";
						$createby = $baru['karyawanid'];
						$tanggal = $tglpost;
						createnotif($notrk, $tipe, $msgdt, $createby, $tanggal);
					}
				} else { //unit
					$stru = "select * from " . $dbname . ".datakaryawan where 
						(kodejabatan in (select kodejabatan from " . $dbname . ".setup_notification_dt where kodejenis='KBCACO')
						or karyawanid in (select karyawanid from " . $dbname . ".setup_notification_dt where kodejenis='KBCACO')
						or tipekaryawan in (select karyawanid from " . $dbname . ".setup_notification_dt where kodejenis='KBCACO'))
						and lokasitugas = '" . $unitcaco[$bar['noakun']] . "'";
					$resu = $owlPDO->query($stru) or die(print " Gagal: " . PDOException::getMessage());
					$resu->setFetchMode(PDO::FETCH_ASSOC);
					while ($baru = $resu->fetch()) {
						$notrk = $bar['notransaksi'];
						$tipe = 'KBCACO';
						$msgdt = "Notification/Pemberitahuan adanya biaya yang dikirim dari unit " . $param['kodeorg'] . " untuk dijurnal alokasi, sejumlah " . $bar['jumlah'] . " ; Nomor transaksi : " . $param['notransaksi'] . "; Keterangan : " . $dataH[0]['keterangan'] . " ";
						$createby = $baru['karyawanid'];
						$tanggal = $tglpost;
						createnotif($notrk, $tipe, $msgdt, $createby, $tanggal);
					}
				}
			}
			#= tutup jika noakun termasuk caco

			#= jika parameter hutang unit 1
			if ($bar['hutangunit1'] == 1) {
				$tporg = $tipeorganisasi[$bar['pemilikhutang1']];
				#= get user pendapat notif
				if ($tporg == 'HOLDING') {
					$stru = "select * from " . $dbname . ".datakaryawan where 
				bagian in (select kodedepartement from " . $dbname . ".setup_notification_dt where kodejenis='KBCACO')  ";
					$resu = $owlPDO->query($stru) or die(print " Gagal: " . PDOException::getMessage());
					$resu->setFetchMode(PDO::FETCH_ASSOC);
					while ($baru = $resu->fetch()) {
						$notrk = $bar['notransaksi'];
						$tipe = 'KBCACO';
						$msgdt = "Notification/Pemberitahuan adanya biaya yang dikirim dari unit " . $param['kodeorg'] . " yang sudah dialokasi otomatis, sejumlah " . $bar['jumlah'] . "; Nomor transaksi : " . $param['notransaksi'] . "; Keterangan : " . $dataH[0]['keterangan'] . " ";
						$createby = $baru['karyawanid'];
						$tanggal = $tglpost;
						createnotif($notrk, $tipe, $msgdt, $createby, $tanggal);
					}
				} else if ($tporg == 'KANWIL') {
					$stru = "select * from " . $dbname . ".datakaryawan where 
				bagian in (select kodedepartement from " . $dbname . ".setup_notification_dt where kodejenis='KBCACO')  ";
					$resu = $owlPDO->query($stru) or die(print " Gagal: " . PDOException::getMessage());
					$resu->setFetchMode(PDO::FETCH_ASSOC);
					while ($baru = $resu->fetch()) {
						$notrk = $bar['notransaksi'];
						$tipe = 'KBCACO';
						$msgdt = "Notification/Pemberitahuan adanya biaya yang dikirim dari unit " . $param['kodeorg'] . " yang sudah dialokasi otomatis, sejumlah " . $bar['jumlah'] . "	; Nomor transaksi : " . $param['notransaksi'] . "; Keterangan : " . $dataH[0]['keterangan'] . " ";
						$createby = $baru['karyawanid'];
						$tanggal = $tglpost;
						createnotif($notrk, $tipe, $msgdt, $createby, $tanggal);
					}
				} else { //unit
					$stru = "select * from " . $dbname . ".datakaryawan where 
						(kodejabatan in (select kodejabatan from " . $dbname . ".setup_notification_dt where kodejenis='KBCACO')
						or karyawanid in (select karyawanid from " . $dbname . ".setup_notification_dt where kodejenis='KBCACO')
						or tipekaryawan in (select karyawanid from " . $dbname . ".setup_notification_dt where kodejenis='KBCACO'))
						and lokasitugas = '" . $bar['pemilikhutang1'] . "'";
					$resu = $owlPDO->query($stru) or die(print " Gagal: " . PDOException::getMessage());
					$resu->setFetchMode(PDO::FETCH_ASSOC);
					while ($baru = $resu->fetch()) {
						$notrk = $bar['notransaksi'];
						$tipe = 'KBCACO';
						$msgdt = "Notification/Pemberitahuan adanya biaya yang dikirim dari unit " . $param['kodeorg'] . "yang sudah dialokasi otomatis, sejumlah " . $bar['jumlah'] . "; Nomor transaksi : " . $param['notransaksi'] . "; Keterangan : " . $dataH[0]['keterangan'] . " ";
						$createby = $baru['karyawanid'];
						$tanggal = $tglpost;
						createnotif($notrk, $tipe, $msgdt, $createby, $tanggal);
					}
				}
			}
			#=tutup jika parameter hutang unit 1
		}
	}

	$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
	$owlPDO->exec($queryH);

	$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
	$owlPDO->exec($queryD);

	$owlPDO->commit();
} catch (PDOException $e) {
	$owlPDO->rollback();
	echo "Warning \n" . addslashes($e->getMessage());
}
