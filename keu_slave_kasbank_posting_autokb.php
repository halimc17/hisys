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

#= bentuk data kodept	
$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)=4";
$res = $owlPDO->query($str);
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$kodept[$bar['kodeorganisasi']] = $bar['induk'];
}
// exit("Error:masukautokab");

// echo"<pre>";
// // print_r($_SESSION['bgnotrans']);
// print_r($param);
// echo"</pre>";
// exit("Error:A");

// if($param['cgttu']!='Cash' and $param['nocek']==''){
// exit("Warning:Nomor Bukti Bayar tidak boleh kosong");
// }

$str = "select * from " . $dbname . ".keu_kasbankht where notransaksi='" . $param['notransaksi'] . "' ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
$bar = $res->fetch();
$pembayaran = $bar['pembayaran'];

#= pengecekan jika status pembayaran sudah 1 maka exit;	
if ($pembayaran == '1') {
	exit("Warning:Transaksi kasir sudah dilakukan, silahkan klik List data untuk melakukan refresh transaksi");
}

#= filter jika ada akun header berbeda ditiap2 detailnya
$str = "SELECT noakun2a FROM " . $dbname . ".keu_kasbankdt where 
		notransaksi='" . $param['notransaksi'] . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$arrakun[$bar['noakun2a']] = $bar['noakun2a'];
}

if (count($arrakun) > 1) {
	exit("Warning:Transaksi detail bermasalah, harap hapus transaksi detail, lalu input kembali");
}


# Simpan dulu
// function parameterappl($newtable,$kodeappl,$kodeparam,$newvalue) {
// 	global $dbname;
// 	global $owlPDO;

// 	try {
// 		$owlPDO->beginTransaction();

// 		$sql = selectQuery($dbname,$newtable,"*","kodeaplikasi='".$kodeappl."' AND kodeparameter='".$kodeparam."'");
// 		$res = fetchData($sql);

// 		if(count($res) <= 0) {
// 			# Susun Data
// 			$data = array(
// 				'kodeaplikasi'	=> $kodeappl,
// 				'kodeparameter' => $kodeparam,
// 				'kodeorg'		=> $_SESSION['empl']['lokasitugas'],
// 				'keterangan' 	=> 'FUNGSI AUTO INSERT',
// 				'typenilai'		=> '1', // Default
// 				'nilai'			=> $newvalue 
// 			);

// 			$sql = insertQuery($dbname,$newtable,$data,array_keys($data));
// 			$owlPDO->exec($sql);
// 		}

// 		$owlPDO->commit();
// 	} catch (PDOException $e) {

// 		$owlPDO->rollback();
// 		echo " Gagal," . addslashes($e->getMessage());
// 	}	
// }

#= jika akun bukan kas
$sqlnewnoakunkas = selectQuery($dbname, "setup_parameterappl", "*", "kodeaplikasi='KS' AND kodeparameter='NEWKAS4'");
$resnewnoakunkas = fetchData($sqlnewnoakunkas, "OBJECT")[0]->nilai;

// if(substr($param['noakun'],0,5)!='11121'){
if (substr($param['noakun'], 0, 5) != $resnewnoakunkas) {
	if ($param['cgttu'] != 'Cash') {
		if ($param['rekening'] == '') {
			exit("Warning:Rekening tidak boleh kosong");
		}
		if ($param['nocek'] == '' and $param['cgttu'] != 'Transfer') {
			exit("Warning:nomor bukti pembayaran tidak boleh kosong");
		}
	}
}





#= cek periode akuntansi
/*
foreach($_SESSION['bgnotrans'] as $key=>$row){
	
	$param['notransaksi']=$row['notransaksi'];
	#= ambil unit dt tiap2 transaksi
	$str = "SELECT kodeorg FROM ".$dbname.".keu_kasbankht where 
		notransaksi='".$param['notransaksi']."'"; 		
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
		$kodeorg=$bar['kodeorg'];
	
	$str = "SELECT * FROM ".$dbname.".setup_periodeakuntansi where 
		notransaksi='".$param['notransaksi']."'"; 		
	$res = $owlPDO->query($str)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();
	
	$periodepemilik=makeOption($dbname,'setup_periodeakuntansi','kodeorg,tanggalmulai',"kodeorg = '".$pemilikhutang."' and tutupbuku = 0");
	$tglMulaiPemilik=str_replace("-", "", $periodepemilik[$pemilikhutang]);
	$tglPosting=tanggalsystem($param['tglpost']);
	if($tglMulaiPemilik>$tglPosting){
		exit("Warning : ".$_SESSION['lang']['tanggal']." < ".$_SESSION['lang']['periodeakuntansi']." ".$pemilikhutang.", ".$_SESSION['lang']['tanggalmulai']." ".$periodepemilik[$pembayarhutang]);
	}
	
}
*/




// exit("Error:AAA");
foreach ($_SESSION['bgnotrans'] as $key => $row) {
	// if($row['notransaksi']=='20190930/BPJM/BK/00001'){
	// exit("Error:".$row['notransaksi']);
	// }
	// $notransaksixx.=$row['notransaksi']."___";
	// exit("Error:".$row['notransaksi']);
	$param['notransaksi'] = $row['notransaksi'];
	$tglpost = tanggalsystem($param['tglpost']);

	// exit("Error:".$param['notransaksi']);
	$kegiatan = $owlPDO->query("SELECT * FROM " . $dbname . ".setup_parameterappl WHERE kodeaplikasi = 'TX'");
	$kegiatan->setFetchMode(PDO::FETCH_ASSOC);
	while ($res = $kegiatan->fetch()) {
		$excludeacc[$res['nilai']] = $res['nilai'];
	}

	#========= replace data ======================================================
	#========= update data ht ====================================================

	// exit("Error:".$param['tipetransaksi']);
	// exit("Error:A");

	#= replace novoucher
	#= buat nomor voucher
	// if($param['noakun2a']=='1110101'){
	if ($param['rekening'] != '') {



		/*
	[file] => undefined
    [fileupload] => 
    [notransaksi] => 20191115/SDRO/BK/00017
    [kodeorg] => SDRO
    [noakun] => 1110101
    [tipetransaksi] => K
    [novoucher] => 
    [tglpost] => 29-11-2019
    [noakun2a] => 1110101
    [rekening] => 1460012362500
    [namabank] => BCA
    [rekeningext] => 347-0200-323
    [anrekeningext] => ANEKA MAKMUR SEJAHTERA, PT
    [cgttu] => Cheque
    [nocek] => HY 899634
    [efill] => 0
	*/
		// exit("Error:A");
		$str = "SELECT inisialurut FROM " . $dbname . ".keu_5akunbank where noakun='" . $param['rekening'] . "'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$inisialurut = $bar['inisialurut'];


		/*
		$str = "SELECT novoucher FROM ".$dbname.".keu_kasbankht where 
		rekening='".$param['rekening']."' and novoucher!='' and 
		tipetransaksi='".$param['tipetransaksi']."' and 
		tanggal like '".substr(tanggalsystemn($param['tglpost']),0,7)."%' 
		order by novoucher desc limit 1"; 		
		*/

		// '%/SDRO/BK/MDR25/%'
		$novoucherlike = "/" . $param['kodeorg'] . "/B" . $param['tipetransaksi'] . "/" . $inisialurut . "/";
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
		/*
	$str = "SELECT novoucher FROM ".$dbname.".keu_kasbankht where 
		noakun='".$param['noakun2a']."' and novoucher like '%".$inisialurut."%' and
		kodeorg='".$param['kodeorg']."' and tipetransaksi='".$param['tipetransaksi']."' and
		tanggal like '".substr(tanggalsystemn($param['tglpost']),0,7)."%' 
		order by novoucher desc limit 1"; 
	*/
		$novoucherlike = "/" . $param['kodeorg'] . "/K" . $param['tipetransaksi'] . "/" . $inisialurut . "/";
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

	#= cek data novoucher
	if ($datanovoucher != '') {
		$expldatanovocher = explode('/', $datanovoucher);
		$verivnovocher = $expldatanovocher[0] . '/' . $expldatanovocher[1] . '/' . $expldatanovocher[2] . '/' . $expldatanovocher[3] . '/' . addZero(($expldatanovocher[4] + 1), 5);
		if ($novoucher != $verivnovocher) {
			exit("Warning:Novoucher tidak sesuai, silahkan posting ulang transaksi | Voucher terbentuk : " . $novoucher . " ; Voucher seharusnya " . $verivnovocher . " ");
		}
	}

	if ($novoucher == '') {
		exit("Warning:Novoucher tidak terbentuk, silahkan posting ulang transaksi");
	}
	// exit("Error:".$novoucher._.$verivnovocher);


	// $novoucher=$tglpost.'/'.$param['kodeorg'].'/'.;

	// exit("Error:$novoucher");
	$str = "update " . $dbname . ".keu_kasbankht set 
		noakun='" . $param['noakun2a'] . "',
		rekening='" . $param['rekening'] . "',
		cgttu='" . $param['cgttu'] . "',
		nocek='" . $param['nocek'] . "',
		
		namabank='" . $param['namabank'] . "',
		rekeningext='" . $param['rekeningext'] . "',
		anrekeningext='" . $param['anrekeningext'] . "',
		kasir='" . $_SESSION['standard']['userid'] . "',
		
		novoucher='" . $novoucher . "'
	where notransaksi = '" . $param['notransaksi'] . "' and noakun='" . $param['noakun'] . "' 
	and tipetransaksi='" . $param['tipetransaksi'] . "' and kodeorg='" . $param['kodeorg'] . "' ";
	// exit("error".$str);
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		echo " Gagal," . addslashes($e->getMessage());
	}


	$str = "update " . $dbname . ".keu_kasbankdt set 
		noakun2a='" . $param['noakun2a'] . "'
	where notransaksi = '" . $param['notransaksi'] . "' and noakun2a='" . $param['noakun'] . "' 
	and tipetransaksi='" . $param['tipetransaksi'] . "' and kodeorg='" . $param['kodeorg'] . "' ";
	// exit("error".$str);
	try {
		$owlPDO->exec($str);
	} catch (PDOException $e) {
		echo " Gagal," . addslashes($e->getMessage());
	}


	#= karna sudah direplace jadi tukar
	$param['noakun'] = $param['noakun2a'];
	$param['novoucher'] = $novoucher;

	#=============================================================================


	$queryH = selectQuery($dbname, 'keu_kasbankht', "*", "notransaksi='" .
		$param['notransaksi'] . "' and kodeorg='" . $param['kodeorg'] .
		"' and noakun='" . $param['noakun'] . "' and tipetransaksi='" . $param['tipetransaksi'] . "' limit 1");
	$dataH = fetchData($queryH);

	# Detail
	$queryD = selectQuery($dbname, 'keu_kasbankdt', "*", "notransaksi='" .
		$param['notransaksi'] . "' and kodeorg='" . $param['kodeorg'] .
		"' and noakun2a='" . $param['noakun'] . "' and tipetransaksi='" . $param['tipetransaksi'] . "' ");
	$dataD = fetchData($queryD);

	#=== Cek Jumlah Detail dan Header harus sama ===
	$tmpJml = 0;
	foreach ($dataD as $row) {
		$tmpJml += $row['jumlah'];
	}
	$selisih = abs($tmpJml - $dataH[0]['jumlah']);
	if ($selisih > 0.01) {
		echo "Warning : Amount on header difference to the amount in detail\n";
		echo "Posting Failed";
		exit;
	}


	if ($dataH[0]['autokb'] == 1 and $dataH[0]['tipetransaksi'] == 'K') {

		#= buat data ht dilawannya

		$queryH = selectQuery($dbname, 'keu_kasbankht', "*", "notransaksi='" .
			$param['notransaksi'] . "' and kodeorg='" . $param['kodeorg'] .
			"' and noakun='" . $param['noakun'] . "' and tipetransaksi='" . $param['tipetransaksi'] . "' limit 1");
		$dataH = fetchData($queryH);

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
			// if($dataH[0]['tipetransaksi']=='K') {
			// if($dataH[0]['noakun2']>=$row['noakunkredit'] and $dataH[0]['noakun2']<=$row['sampaikredit']) {
			// $tipe = $row['jurnalid'];
			// }
			// } else {
			if ($dataH[0]['noakun2'] >= $row['noakundebet'] and $dataH[0]['noakun2'] <= $row['sampaidebet']) {
				$tipe = $row['jurnalid'];
			}
			// }
		}

		#= notransaksi awal tidak melihat tipe untuk membentuk nomor terakhir
		$noTrans = "/" . $dataH[0]['namapenerima'] . "/" . $tipe . "/";
		// $qTrans = selectQuery($dbname,'keu_kasbankht','notransaksi',"notransaksi like '".$noTrans."%' and tanggalinput like '".substr($dataH[0]['tanggalinput'],0,7)."%'","notransaksi desc",true,1,1);
		$qTrans = selectQuery($dbname, 'keu_kasbankht', 'max(right(notransaksi,5)) as notransaksi', "notransaksi like '%" . $noTrans . "%' and tanggalinput like '" . substr($dataH[0]['tanggalinput'], 0, 7) . "%'", "notransaksi desc");
		$resTrans = fetchData($qTrans);


		if (empty($resTrans)) {
			$tmpTrans = 1;
		} else {
			$tmpTrans = $resTrans[0]['notransaksi'];
			$tmpTrans++;
		}

		$notransaksibaru = str_replace('-', '', $dataH[0]['tanggalinput']) . $noTrans . str_pad($tmpTrans, 5, '0', STR_PAD_LEFT);

		#======================================================
		#======================================================


		if ($dataH[0]['norekpenerima'] != '') {
			$str = "SELECT inisialurut FROM " . $dbname . ".keu_5akunbank where noakun='" . $dataH[0]['norekpenerima'] . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$inisialurut = $bar['inisialurut'];

			$novoucherlike = "/" . $dataH[0]['namapenerima'] . "/BM/" . $inisialurut . "/";
			$str = "SELECT novoucher FROM " . $dbname . ".keu_kasbankht where 
			novoucher like '%" . $novoucherlike . "%' and
			tanggal like '" . substr(tanggalsystemn($param['tglpost']), 0, 7) . "%' 
			order by novoucher desc limit 1";

			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar = $res->fetch();
			$datanovoucher = $bar['novoucher'];
		} else {
			if ($dataH[0]['noakun2'] == '1112104') {
				$inisialurut = 'COH';
			} else {
				$inisialurut = 'KAS';
			}

			$novoucherlike = "/" . $dataH[0]['namapenerima'] . "/KM/" . $inisialurut . "/";
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

		$explnotran = explode('/', $notransaksibaru);

		$novoucherbaru = substr($tglpost, 2, 4) . '/' . $explnotran[1] . '/' . $explnotran[2] . '/' . $inisialurut . '/' . addZero($nourutvoucher, 5);
		// exit("Error".$novoucher);
		#= cek data novoucher
		if ($datanovoucher != '') {
			$expldatanovocher = explode('/', $datanovoucher);
			$verivnovocher = $expldatanovocher[0] . '/' . $expldatanovocher[1] . '/' . $expldatanovocher[2] . '/' . $expldatanovocher[3] . '/' . addZero(($expldatanovocher[4] + 1), 5);
			if ($novoucherbaru != $verivnovocher) {
				exit("Warning:Novoucher tidak sesuai, silahkan posting ulang transaksi | Voucher terbentuk : " . $novoucherbaru . " ; Voucher seharusnya " . $verivnovocher . " ");
			}
		}


		if ($novoucherbaru == '') {
			exit("Warning:Novoucher tidak terbentuk, silahkan posting ulang transaksi");
		}


		#======================================================
		#======================================================

		// exit("Error:".$dataH);

		#= coa ayat silang
		$str = "select * from " . $dbname . ".setup_parameterappl where kodeaplikasi='GL' and kodeparameter='GLAS'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$noakunayatsilang = $bar['nilai'];
		}

		# Detail
		$queryD = selectQuery($dbname, 'keu_kasbankdt', "*", "notransaksi='" .
			$param['notransaksi'] . "' and kodeorg='" . $param['kodeorg'] .
			"' and noakun2a='" . $param['noakun'] . "' and tipetransaksi='" . $param['tipetransaksi'] . "' and noakun='" . $noakunayatsilang . "' ");
		$dataD = fetchData($queryD);


		$str = "insert into " . $dbname . ".keu_kasbankht 
			(notransaksi,noakun,tanggalinput,matauang,kurs,
			tipetransaksi,jumlah,cgttu,keterangan,kodeorg,
			rekening,tanggal,userid,createby,createtime,
			noreferensi,pembayaran,novoucher,kasir)
		values ('" . $notransaksibaru . "','" . $dataH[0]['noakun2'] . "','" . $dataH[0]['tanggalinput'] . "','" . $dataH[0]['matauang'] . "','" . $dataH[0]['kurs'] . "',
		'M','" . $dataD[0]['jumlah'] . "','" . $dataH[0]['cgttu'] . "','" . $dataH[0]['keterangan'] . "','" . $dataH[0]['namapenerima'] . "',
		'" . $dataH[0]['norekpenerima'] . "','" . $dataH[0]['tanggal'] . "','" . $dataH[0]['userid'] . "','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "',
		'" . $dataH[0]['notransaksi'] . "','1','" . $novoucherbaru . "','" . $_SESSION['standard']['userid'] . "')";
		// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}



		$str = "insert into " . $dbname . ".keu_kasbankdt 
			(notransaksi,noakun,tipetransaksi,tanggal,jumlah,
			noakun2a,kode,matauang,
			kurs,kurs2,noaruskas,kodeorg,tahun,
			bulan,keterangan2,keterangan3)
		values ('" . $notransaksibaru . "','" . $dataD[0]['noakun'] . "','M','" . $dataH[0]['tanggalinput'] . "','" . $dataD[0]['jumlah'] . "',
		'" . $dataH[0]['noakun2'] . "','" . $tipe . "','" . $dataH[0]['matauang'] . "',
		'" . $dataH[0]['kurs'] . "','" . $dataH[0]['kurs'] . "','141001','" . $dataH[0]['namapenerima'] . "','" . $dataD[0]['tahun'] . "',
		'" . $dataD[0]['bulan'] . "','" . $dataD[0]['keterangan2'] . "','" . $dataD[0]['keterangan3'] . "')";
		// exit("Error:".$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}


		#= update noreferensi untuk pembuat
		$str = "update " . $dbname . ".keu_kasbankht set noreferensi='" . $notransaksibaru . "' where notransaksi='" . $param['notransaksi'] . "'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}


		// $stra="select * from ".$dbname.".keu_kasbankdt where notransaksi in ('".$dataH[0]['notransaksi']."','".$notransaksibaru."') ";
		// $stra="select * from ".$dbname.".keu_kasbankdt where notransaksi in ('".$param['notransaksi']."','".$notransaksibaru."') ";
		$stra = "select * from " . $dbname . ".keu_kasbankdt where notransaksi in ('" . $dataH[0]['notransaksi'] . "','" . $notransaksibaru . "') ";
	} else {

		$stra = "select * from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "' ";
	}

	$tampung['notransaksi'] = $param['notransaksi'];
	$tampung['kodeorg'] = $param['kodeorg'];
	$tampung['noakun'] = $param['noakun'];
	$tampung['tipetransaksi'] = $param['tipetransaksi'];
	// exit("Error:$stra");
	$resa = $owlPDO->query($stra) or die(print " Gagal: " . PDOException::getMessage());
	$resa->setFetchMode(PDO::FETCH_ASSOC);
	while ($bardata = $resa->fetch()) {


		if ($bardata['notransaksi'] == $notransaksibaru) {
			$param['notransaksi'] = $notransaksibaru;
			$param['kodeorg'] = $dataH[0]['namapenerima'];
			$param['noakun'] = $dataH[0]['noakun2'];
			$param['tipetransaksi'] = 'M';
		} else {
			$param['notransaksi'] = $tampung['notransaksi'];
			$param['kodeorg'] = $tampung['kodeorg'];
			$param['noakun'] = $tampung['noakun'];
			$param['tipetransaksi'] = $tampung['tipetransaksi'];
		}

		#=== Get Data ===
		# Header	
		$queryH = selectQuery($dbname, 'keu_kasbankht', "*", "notransaksi='" .
			$param['notransaksi'] . "' and kodeorg='" . $param['kodeorg'] .
			"' and noakun='" . $param['noakun'] . "' and tipetransaksi='" . $param['tipetransaksi'] . "' limit 1");
		$dataH = fetchData($queryH);

		# Detail
		$queryD = selectQuery($dbname, 'keu_kasbankdt', "*", "notransaksi='" . $param['notransaksi'] . "' and kodeorg='" . $param['kodeorg'] .
			"' and noakun2a='" . $param['noakun'] . "' and noakun='" . $bardata['noakun'] . "' 
			and tipetransaksi='" . $param['tipetransaksi'] . "' and keterangan1='" . $bardata['keterangan1'] . "' and keterangan3='" . $bardata['keterangan3'] . "' and nourut='" . $bardata['nourut'] . "' ");
		// echo $queryD;exit("Error:A");
		$dataD = fetchData($queryD);

		#=== Cek if posted ===
		$error0 = "";
		# cek jurnal
		$queryJ = selectQuery($dbname, 'keu_jurnaldt_vw', "*", "noreferensi='" . $param['notransaksi'] . "' and noreferensi not in (select notransaksi from " . $dbname . ".keu_kasbankdt where kodeorg='" . $param['kodeorg'] . "' )");
		$dataJ = fetchData($queryJ);

		if ($dataH[0]['posting'] == 1 and count($dataJ) > 0) {
			$error0 .= $_SESSION['lang']['errisposted'];
		}
		if ($error0 != '') {
			echo "Data Error :\n" . $error0;
			exit;
		}

		# manupulasi tanggal menjadi tanggal input
		$dataH[0]['tanggal'] = tanggalsystem($param['tglpost']);
		$periodejurnal = substr(tanggalsystemn($param['tglpost']), 0, 7);
		// exit("Error".$periodejurnal);
		#====cek periode
		// $tgl = str_replace("-","",$dataH[0]['tanggal']);
		// if($_SESSION['org']['period']['start']>$tgl)
		//     exit('Error:Date beyond active period');

		#=== Cek if data not exist ===
		$error1 = "";
		if (count($dataH) == 0) {
			$error1 .= $_SESSION['lang']['errheadernotexist'] . "\n";
		}
		if (count($dataD) == 0) {
			$error1 .= $_SESSION['lang']['errdetailnotexist'] . "\n";
		}
		if ($error1 != '') {
			echo "Data Error 302 :\n" . $error1;
			exit;
		}
		#=======cek kurs mata uang header dan detail

		$ceko = 0;
		foreach ($dataD as $rowdt => $isiData) {
			if ($dataH[0]['matauang'] != $isiData['matauang']) {
				$ceko += 1;
			}
		}
		if ($ceko != 0) {
			exit('warning: Matauang header dan detail berbeda!!');
		}

		// $dataH[0]['tanggal']=$dataH[0]['tanggalinput'];
		$thn =  substr($dataH[0]['tanggal'], 0, 4);
		$bln =  substr($dataH[0]['tanggal'], 4, 2);

		if ($bln == 12) {
			$nextbln = '01';
			$nextthn = $thn + 1;
		} else {
			$nextbln = addZero(intval($bln + 1), 2);
			$nextthn = $thn;
		}


		if ($param['noakun'] == '1110101' || $param['noakun'] == '1111101') {
			$str = " select * from " . $dbname . ".keu_saldobank where kodeorg='" . $param['kodeorg'] . "' and periode='" . $nextthn . $nextbln . "' and norek='" . $dataH['rekening'] . "'";
			$saldobank = fetchData($str);

			if (count($saldobank) > 0) {
				exit("error : Transaksi Bank periode " . $nextbln . "-" . $nextthn . " sudah ada.");
			}
		} else {
			$str = " select * from " . $dbname . ".keu_jurnaldt_vw where kodejurnal='CLSM' and kodeorg='" . $param['kodeorg'] . "' and periode='" . $nextthn . $nextbln . "'";
			$saldo = fetchData($str);

			if (count($saldo) > 0) {
				exit("error : Periode akuntansi " . $nextbln . "-" . $nextthn . " sudah tutup buku.");
			}
		}

		#=== Cek if hutang unit ========================================================
		// if($dataH[0]['hutangunit']==1) {
		if ($dataD[0]['hutangunit1'] == 1) {
			$pembayarhutang = $param['kodeorg'];
			$pemilikhutang = $dataD[0]['pemilikhutang1'];



			#cek jika pemilik hutang dengan kodeorg pemilik akun piutang sama atau tidak
			$rwError = 0;
			$sCek = "select distinct noakun from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "' and hutangunit1=1  and kodeorg='" . $param['kodeorg'] .
				"' and noakun like '1210%' and tipetransaksi='" . $param['tipetransaksi'] . "'";
			$qCek = $owlPDO->query($sCek);
			$qCek->setFetchMode(PDO::FETCH_ASSOC);
			while ($rCek = $qCek->fetch()) {
				$whrdt = "kodeorg='" . $pemilikhutang . "'";
				$optCek = makeOption($dbname, 'keu_5caco', 'kodeorg,akunpiutang', $whrdt);
				if ($optCek[$pemilikhutang] != $rCek['noakun']) {
					$rwError += 1;
					$dtAkun[$rCek['noakun']] = $rCek['noakun'];
				}
			}

			if ($rwError != 0) {
				exit('warning: Noakun diatas bukan milik ' . $pemilikhutang);
			}

			$periodepemilik = makeOption($dbname, 'setup_periodeakuntansi', 'kodeorg,tanggalmulai', "kodeorg = '" . $pemilikhutang . "' and tutupbuku = 0");
			$tglMulaiPemilik = str_replace("-", "", $periodepemilik[$pemilikhutang]);
			$tglPosting = tanggalsystem($param['tglpost']);
			if ($tglMulaiPemilik > $tglPosting) {
				exit("Warning : " . $_SESSION['lang']['tanggal'] . " < " . $_SESSION['lang']['periodeakuntansi'] . " " . $pemilikhutang . ", " . $_SESSION['lang']['tanggalmulai'] . " " . $periodepemilik[$pembayarhutang]);
			}

			$noakunhutang = $dataD[0]['noakun'];
			$kodejurnal = 'M';
			$tanggal = $dataH[0]['tanggal'];
			$tanggal = tanggalnormal($tanggal);
			$tanggal = tanggalsystem($tanggal);

			#=============== Get Induk Pemilik Hutang
			$whereNomilhut = "kodeorganisasi='" .
				$pemilikhutang . "'";
			$query = selectQuery(
				$dbname,
				'organisasi',
				'induk',
				$whereNomilhut
			);
			$noKon = fetchData($query);
			$indukpemilikhutang = $noKon[0]['induk'];

			#=============== Get Induk Pembayar Hutang
			$whereNoyarhut = "kodeorganisasi='" .
				$param['kodeorg'] . "'";
			$query = selectQuery(
				$dbname,
				'organisasi',
				'induk',
				$whereNoyarhut
			);
			$noKon = fetchData($query);
			$indukpembayarhutang = $noKon[0]['induk'];

			if ($indukpemilikhutang == $indukpembayarhutang) $jenisinduk = 'intra';
			else $jenisinduk = 'inter';

			#=============== Get Nomor Jurnal Otomatis (pemilikhutang)
			//    $whereNo = "kodekelompok='".$kodejurnal."' and kodeorg='".
			//        $pemilikhutang."'";
			$whereNoindukph = "kodekelompok='" . $kodejurnal . "' and kodeorg='" . $indukpemilikhutang . "' 
					and kodeunit='" . $pemilikhutang . "' and periode='" . $periodejurnal . "' ";
			$query = selectQuery(
				$dbname,
				'keu_5kelompokjurnal',
				'nokounter',
				$whereNoindukph
			);

			$noKon = fetchData($query);

			//Untuk mengecek sinkron nokounter jurnalht -> kodejurnal
			$nilaikounter = (isset($noKon[0]['nokounter'])) ? (int)$noKon[0]['nokounter'] : 0;

			$strjurnalht = selectQuery(
				$dbname,
				'keu_jurnalht',
				'nojurnal',
				" kodejurnal = '" . $kodejurnal . "' AND nojurnal LIKE '%/" . $pemilikhutang . "/%' AND tanggal LIKE '" . $periodejurnal . "%' ORDER BY nojurnal DESC LIMIT 1"
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
					kodeunit='" . $pemilikhutang . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $periodejurnal . "' ";
				$owlPDO->exec($str);
				$noKon = fetchData($query);
			}

			$tmpC = $noKon[0]['nokounter'];
			$tmpC++;
			$konteroto = addZero($tmpC, 3);
			$nojuroto = $tanggal . "/" .
				$pemilikhutang . "/" . $kodejurnal . "/" .
				$konteroto;

			#=============== Get Nomor Akun Caco
			// ini ga dipake soale dipilih secara manual sama usernya pas nginput kasbank
			$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" .
				$pemilikhutang . "'";
			$query = selectQuery(
				$dbname,
				'keu_5caco',
				'akunpiutang',
				$whereNocaco
			);
			$noKon = fetchData($query);
			$noakuncaco = $noKon[0]['akunpiutang'];
			// exit("Error:".$noakuncaco);

			if ($noakuncaco == '') {
				exit("Warning : Account intraco or interco not available for " . $pemilikhutang . ". Please setting on menu Finance > setup > COA for Intra/Interco.");
			}

			#=============== Get Nomor Akun Caco Lawannya
			// ini yang dipake
			$whereNocacol = "jenis='" . $jenisinduk . "' and kodeorg='" .
				$pembayarhutang . "'";
			$query = selectQuery(
				$dbname,
				'keu_5caco',
				'akunpiutang',
				$whereNocacol
			);
			$noKon = fetchData($query);
			$noakuncacol = $noKon[0]['akunpiutang'];

			if ($noakuncacol == '') {
				exit("Warning : Account intraco or interco not available for " . $pembayarhutang . ". Please setting on menu Finance > setup > COA for Intra/Interco.");
			}
			// exit("Error:".$nojuroto._.$noakuncaco._.$noakuncacol);
		}



		/*
		else{
			#cek jika detail ada hutang unit tetapi headernya belum tercentang
			$sCek="select * from ".$dbname.".keu_kasbankdt where notransaksi='".$param['notransaksi']."' and hutangunit1=1  and kodeorg='".$param['kodeorg'].
			"' and noakun2a='".$param['noakun']."' and tipetransaksi='".$param['tipetransaksi']."'";
			$qCek=$owlPDO->query($sCek);
			$rCek=owlBaris($qCek);
			if($rCek>0){
				exit('warning: Hutang unit pada form header belum tersimpan.');
			}
		}
		*/



		/*
		## INSERT BUKTI PEMBAYARAN ##
		if($param['efill']=='1'){
			if($param['fileupload']!=''){
				if($_FILES['file']['error']==0){
					$nmTemp=str_replace('-','',str_replace('/','',$param['notransaksi']));
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = "Bukti Pembayaran ".$nmTemp."".$filetype;
					$file_tmpname = $_FILES['file']['tmp_name'];		
					
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')||($filetype=='.rar')){
						if($_FILES['file']['size'] <= 250000){
							$strefil="select * from ".$dbname.".filemanager where namafile='".$param['notransaksi']."'";
							$resefil=fetchdata($strefil);
							$idindukefil = $resefil[0]['id'];
							
							$strefil="delete from ".$dbname.".filemanager where induk='".$idindukefil."' and sourceid='EBP'";
							$owlPDO->exec($strefil);
							
							$strefil="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime,sourceid) values('".$idindukefil."','6','".$filename."','".$filetype."','','1','','".$createdtime."','','".$createdtime."','EBP')";
							$owlPDO->exec($strefil);
							
							$structureefil = setlocationfile($idindukefil)."/".$filename;
							move_uploaded_file($file_tmpname,$structureefil);
						}else{
							exit("warning : Ukuran file upload maksimal 250kb");
						}
					}else{
						exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
					}
				}
			}
		}
		*/











		// $a.=$no.'-';



		// Default Segment
		$defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

		#=== Transform Data ===
		$dataRes['header'] = array();
		$dataRes['detail'] = array();
		$dataResoto['header'] = array();
		$dataResoto['detail'] = array();

		#1. Data Header
		# Get Journal Counter
		//Fetch kounter kelompok jurnal
		$queryJ = selectQuery(
			$dbname,
			'keu_5kelompokjurnal',
			'nokounter',
			"kodeorg='" . $kodept[$dataH[0]['kodeorg']] . "' and kodekelompok='" . $dataD[0]['kode'] . "'
				and kodeunit='" . $dataH[0]['kodeorg'] . "' and periode='" . $periodejurnal . "'"
		);
		$tmpKonter = fetchData($queryJ);
		// $konter = addZero($tmpKonter[0]['nokounter']+1,3);

		//Pengecekan kelompok jurnal
		$res = fetchdata($queryJ);
		$countdata = count($res);

		if ($countdata <= 0) {
			exit("warning : " . $dataD[0]['kode'] . " pada unit " . $dataH[0]['kodeorg'] . " dan periode " . $periodejurnal . " belum terdaftar!");
		}

		//Validasi pengecekan nokounter
		$nilaikounter = (isset($tmpKonter[0]['nokounter'])) ? (int)$tmpKonter[0]['nokounter'] : 0;

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
		$tmpKonter = fetchData($queryJ);
		$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

		// exit("Error:$queryJ");
		# Prep No Jurnal
		$nojurnal = str_replace('-', '', $dataH[0]['tanggal']) . "/" . $dataH[0]['kodeorg'] . "/" .
			$dataD[0]['kode'] . "/" . $konter;


		# Prep Header
		$dataRes['header'] = array(
			'nojurnal' => $nojurnal,
			'kodejurnal' => $dataD[0]['kode'],
			'tanggal' => $dataH[0]['tanggal'],
			'tanggalentry' => date('Ymd'),
			'posting' => '0',
			'totaldebet' => '0',
			'totalkredit' => '0',
			'amountkoreksi' => '0',
			'noreferensi' => $dataH[0]['notransaksi'],
			'autojurnal' => '1',
			'matauang' => 'IDR',
			'kurs' => '1',
			'revisi' => '0'
		);

		# Prep Header Otomatis =========================================================
		if (isset($nojuroto)) {
			$dataResoto['header'] = array(
				'nojurnal' => $nojuroto,
				'kodejurnal' => $kodejurnal,
				'tanggal' => $dataH[0]['tanggal'],
				'tanggalentry' => date('Ymd'),
				'posting' => '0',
				'totaldebet' => '0',
				'totalkredit' => '0',
				'amountkoreksi' => '0',
				'noreferensi' => $pembayarhutang . $dataH[0]['notransaksi'],
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);
		}

		// Jika mata uang selain IDR, cek apakah ada selisih kurs
		/**
		 * [START] Jurnal Selisih Kurs
		 */
		$noUrut = 1;
		$totalJumlah = 0;
		if ($dataH[0]['matauang'] != 'IDR') {
			$invRp = $invKurs = $invList = $invSupp = $invSegment = array();
			foreach ($dataD as $row) {
				setIt($invRp[$row['keterangan1']], 0);
				$invRp[$row['keterangan1']] += $row['jumlah'];
				$invList[$row['keterangan1']] = $row['keterangan1'];
				$invKurs[$row['keterangan1']] = $row['kurs'];
				$invSupp[$row['keterangan1']] = $row['kodesupplier'];
				$invSegment[$row['keterangan1']] = $row['kodesegment'];
			}

			// Get Kurs PO
			$invPoKurs = makeOption(
				$dbname,
				'keu_tagihanht',
				'noinvoice,kurs',
				"noinvoice in ('" . implode("','", $invList) . "')"
			);
			if (empty($invPoKurs)) {
				$invPoKurs = makeOption(
					$dbname,
					'keu_penagihanht',
					'noinvoice,kurs',
					"noinvoice in ('" . implode("','", $invList) . "')"
				);
			}

			// Iterasi tiap Invoice
			foreach ($invPoKurs as $invoice => $kurs) {
				if ($kurs > 0 and $kurs != $invKurs[$invoice]) {
					// Get Akun Selisih Kurs
					$qParam = selectQuery(
						$dbname,
						'keu_5parameterjurnal',
						"noakundebet,noakunkredit",
						"kodeaplikasi='KURS' and jurnalid='KRS01'"
					);
					$resParam = fetchData($qParam);
					if (empty($resParam)) {
						exit("Warning: Akun selisih kurs belum ada\n
							 Silahkan hubungi IT dengan melampirkan pesan error ini");
					} else {
						$kursDebet = $resParam[0]['noakundebet'];
						$kursKredit = $resParam[0]['noakunkredit'];
					}
					$selKurs = abs($kurs - $invKurs[$invoice]);
					echo $kurs . '|';
					echo $invKurs[$invoice] . '|';
					if ($dataH[0]['tipetransaksi'] == 'K') {
						/**
						 * Transaksi Keluar
						 */
						if ($kurs < $invKurs[$invoice]) {
							// Trans Keluar Selisih Rugi
							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $dataH[0]['tanggal'],
								'nourut' => $noUrut,
								'noakun' => $kursDebet,
								'keterangan' => 'Selisih Kurs Invoice ' . $invoice,
								'jumlah' => $invRp[$invoice] * $selKurs,
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => '',
								'kodekegiatan' => '',
								'kodeasset' => '',
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $invSupp[$invoice],
								'noreferensi' => $dataH[0]['notransaksi'],
								'noaruskas' => '',
								'kodevhc' => '',
								'nodok' => $invoice,
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => $invSegment[$invoice]
							);
							$totalJumlah += $invRp[$invoice] * $selKurs;
							$noUrut++;
						} else {
							// Trans Keluar Selisih Untung
							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $dataH[0]['tanggal'],
								'nourut' => $noUrut,
								'noakun' => $kursKredit,
								'keterangan' => 'Selisih Kurs Invoice ' . $invoice,
								'jumlah' => $invRp[$invoice] * $selKurs * (-1),
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => '',
								'kodekegiatan' => '',
								'kodeasset' => '',
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $invSupp[$invoice],
								'noreferensi' => $dataH[0]['notransaksi'],
								'noaruskas' => '',
								'kodevhc' => '',
								'nodok' => $invoice,
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => $invSegment[$invoice]
							);
							$noUrut++;
							$totalJumlah += $invRp[$invoice] * $selKurs * (-1);
						}
					} elseif ($dataH[0]['tipetransaksi'] == 'M') {
						/**
						 * Transaksi Masuk
						 */
						if ($kurs < $invKurs[$invoice]) {
							// Trans Masuk Selisih Untung
							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $dataH[0]['tanggal'],
								'nourut' => $noUrut,
								'noakun' => $kursKredit,
								'keterangan' => 'Selisih Kurs Invoice ' . $invoice,
								'jumlah' => $invRp[$invoice] * $selKurs * (-1),
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => '',
								'kodekegiatan' => '',
								'kodeasset' => '',
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $invSupp[$invoice],
								'noreferensi' => $dataH[0]['notransaksi'],
								'noaruskas' => '',
								'kodevhc' => '',
								'nodok' => $invoice,
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => $invSegment[$invoice]
							);
							$noUrut++;
							$totalJumlah += $invRp[$invoice] * $selKurs * (-1);
						} else {
							// Trans Masuk Selisih Rugi
							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $dataH[0]['tanggal'],
								'nourut' => $noUrut,
								'noakun' => $kursDebet,
								'keterangan' => 'Selisih Kurs Invoice ' . $invoice,
								'jumlah' => $invRp[$invoice] * $selKurs,
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => '',
								'kodekegiatan' => '',
								'kodeasset' => '',
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $invSupp[$invoice],
								'noreferensi' => $dataH[0]['notransaksi'],
								'noaruskas' => '',
								'kodevhc' => '',
								'nodok' => $invoice,
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => $invSegment[$invoice]
							);
							$noUrut++;
							$totalJumlah += $invRp[$invoice] * $selKurs;
						}
					}
				}
			}
		}

		/**
		 * [END] Jurnal Selisih Kurs
		 */

		#2. Data Detail
		# Detail (Many)
		foreach ($dataD as $row) {

			if (substr($row['kode'], 1, 1) == 'M') {
				$jumlah = $row['jumlah'] * (-1);
			} else {
				$jumlah = $row['jumlah'];
			}

			if (substr($row['kodeasset'], 0, 2) == 'PB') {
				$row['orgalokasi'] = $row['kodeasset'];
			}

			$dKurs = 1;
			$dMtUang = 'IDR';
			if ($row['matauang'] != 'IDR') {
				if (isset($invPoKurs[$row['keterangan1']])) {
					$dKurs = $invPoKurs[$row['keterangan1']];
				} else {
					$dKurs = $row['kurs'];
				}
				$jumlah = $jumlah * $dKurs;
			}


			if ($row['hutangunit1'] == 1) {
				$row['noakun'] = $noakuncaco;
			}


			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $dataH[0]['tanggal'],
				'nourut' => $noUrut,
				'noakun' => $row['noakun'],
				'keterangan' => $row['keterangan2'],
				'jumlah' => $jumlah,
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $row['kodeorg'],
				'kodekegiatan' => $row['kodekegiatan'],
				'kodeasset' => $row['kodeasset'],
				'kodebarang' => $row['kodebarang'],
				'nik' => $row['nik'],
				'kodecustomer' => $row['kodecustomer'],
				'kodesupplier' => $row['kodesupplier'],
				'noreferensi' => $dataH[0]['notransaksi'],
				'noaruskas' => $row['noaruskas'],
				'kodevhc' => $row['kodevhc'],
				'nodok' => $row['nodok'],
				'kodeblok' => $row['orgalokasi'],
				'revisi' => '0',
				'kodesegment' => $row['kodesegment']
			);
			$totalJumlah += $jumlah;
			$noUrut++;
		}

		# Detail (One)
		$dataRes['detail'][] = array(
			'nojurnal' => $nojurnal,
			'tanggal' => $dataH[0]['tanggal'],
			'nourut' => $noUrut,
			'noakun' => $dataH[0]['noakun'],
			'keterangan' => $dataH[0]['keterangan'],
			'jumlah' => $totalJumlah * (-1),
			'matauang' => 'IDR',
			'kurs' => '1',
			'kodeorg' => $dataH[0]['kodeorg'],
			'kodekegiatan' => '',
			'kodeasset' => '',
			'kodebarang' => '',
			'nik' => '',
			'kodecustomer' => '',
			'kodesupplier' => '',
			'noreferensi' => $dataH[0]['notransaksi'],
			'noaruskas' => '',
			'kodevhc' => '',
			'nodok' => '',
			'kodeblok' => '',
			'revisi' => '0',
			'kodesegment' => $row['kodesegment']
		);




		#2. Data Detail Otomatis =======================================================
		# Detail (Many)
		$noUrut = 1;
		$totalJumlahOto = 0;
		foreach ($dataD as $row) {

			// default: lempar ke unit
			$ok = true;
			if (!empty($excludeacc)) foreach ($excludeacc as $acc) {
				if (substr($row['noakun'], 0, 3) == $acc) {
					// kalo exclude, jangan lempar ke unit
					$ok = false;
				}
			}

			// kalo detailnya bukan hutang unit, jangan lempar ke unit
			if ($row['hutangunit1'] == 0) $ok = false;

			// kalo OK, lempar ke unit
			if ($ok) {
				if (substr($row['kode'], 1, 1) == 'M') {
					$jumlah = $row['jumlah'] * (-1);
				} else {
					$jumlah = $row['jumlah'];
				}
				$dKurs = 1;
				$dMtUang = 'IDR';
				if ($row['matauang'] != 'IDR') {
					//$dMtUang=$row['matauang'];
					$dKurs = $row['kurs'];
					$jumlah = $jumlah * $dKurs;
				}
				$dataResoto['detail'][] = array(
					'nojurnal' => $nojuroto,
					'tanggal' => $dataH[0]['tanggal'],
					'nourut' => $noUrut,
					'noakun' => $noakunhutang,
					'keterangan' => $row['keterangan2'],
					'jumlah' => $jumlah,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $pemilikhutang,
					'kodekegiatan' => $row['kodekegiatan'],
					'kodeasset' => $row['kodeasset'],
					'kodebarang' => $row['kodebarang'],
					'nik' => $row['nik'],
					'kodecustomer' => $row['kodecustomer'],
					'kodesupplier' => $row['kodesupplier'],
					'noreferensi' => $pembayarhutang . $dataH[0]['notransaksi'],
					'noaruskas' => $row['noaruskas'],
					'kodevhc' => $row['kodevhc'],
					'nodok' => $row['nodok'],
					'kodeblok' => $row['orgalokasi'],
					'revisi' => '0',
					'kodesegment' => $row['kodesegment']
				);
				$totalJumlahOto += $jumlah;
				$noUrut++;
			}
		}


		# Detail (One) Otomatis ========================================================
		if (isset($nojuroto)) {
			$dataResoto['detail'][] = array(
				'nojurnal' => $nojuroto,
				'tanggal' => $dataH[0]['tanggal'],
				'nourut' => $noUrut,
				'noakun' => $noakuncacol,
				'keterangan' => $dataH[0]['keterangan'],
				'jumlah' => $totalJumlahOto * (-1),
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $pemilikhutang,
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => '',
				'kodesupplier' => '',
				'noreferensi' => $pembayarhutang . $dataH[0]['notransaksi'],
				'noaruskas' => '',
				'kodevhc' => '',
				'nodok' => '',
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => $row['kodesegment']
			);
		}

		# Total D/K
		$dataRes['header']['totaldebet'] = $totalJumlah;
		$dataRes['header']['totalkredit'] = $totalJumlah * (-1);
		$dataResoto['header']['totaldebet'] = $totalJumlahOto;
		$dataResoto['header']['totalkredit'] = $totalJumlahOto * (-1);

		// exit("Error:");

		#=== Insert Data ===
		$errorDB = "";

		// echo "<pre>";
		// print_r($dataResoto);
		// echo "<pre>";
		// exit('warning : ');

		# Header
		$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
		// exit('warning : '.$queryH);
		try {
			$owlPDO->exec($queryH);
		} catch (PDOException $e) {
			$errorDB .= "Header :" . $e->getMessage();
		}

		# Header Otomatis ==============================================================
		if ($dataD[0]['hutangunit1'] == 1) {
			$queryH = insertQuery($dbname, 'keu_jurnalht', $dataResoto['header']);
			try {
				$owlPDO->exec($queryH);
			} catch (PDOException $e) {
				$errorDB .= "Header Hutang unit:" . $e->getMessage();
			}
		}

		# Detail
		if ($errorDB == '') {
			//print_r($dataRes['detail']);exit("Error:A");
			foreach ($dataRes['detail'] as $key => $dataDet) {
				$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
				try {
					$owlPDO->exec($queryD);
				} catch (PDOException $e) {
					$errorDB .= "Detail: " . $key . " " . $e->getMessage();
				}
			}

			#=== Switch Jurnal to 1 ===
			# Cek if already posted
			$queryJ = selectQuery($dbname, 'keu_kasbankht', "posting", "notransaksi='" .
				$param['notransaksi'] . "' and kodeorg='" . $param['kodeorg'] . "'");
			$isJ = fetchData($queryJ);
			// if($isJ[0]['posting']==1) {
			// $errorDB .= "Data changed by other user";
			// } else {
			/*
				$queryToJ = updateQuery($dbname,'keu_kasbankht',array('posting'=>1,'tanggal'=>$dataH[0]['tanggal'],'novoucher'=>$param['novoucher']),
					"notransaksi='".$dataH[0]['notransaksi']."' and kodeorg='".$dataH[0]['kodeorg']."' and tanggal='".$dataH[0]['tanggal']."'");
				*/
			$queryToJ = updateQuery(
				$dbname,
				'keu_kasbankht',
				array(
					'posting' => 1,
					'postingby' => $_SESSION['standard']['userid'],
					'postingtime' => date('Y-m-d H:i'),
					'tanggal' => $dataH[0]['tanggal']
				),
				// 'tanggal'=>$dataH[0]['tanggal'],'novoucher'=>$param['novoucher']),
				"notransaksi='" . $dataH[0]['notransaksi'] . "' and kodeorg='" . $dataH[0]['kodeorg'] . "'"
			);
			try {
				$owlPDO->exec($queryToJ);
			} catch (PDOException $e) {
				$errorDB .= "Posting Flag Error" . $e->getMessage();
			}
			//}
		}

		# Detail Otomatis ==============================================================
		if ($dataD[0]['hutangunit1'] == 1) {
			if ($errorDB == '') {
				foreach ($dataResoto['detail'] as $key => $dataDet) {
					$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
					try {
						$owlPDO->exec($queryD);
					} catch (PDOException $e) {
						$errorDB .= "Detail hutang unit" . $key . " :" . $e->getMessage();
					}
				}
			}
		}

		if ($errorDB != "") {
			// Rollback
			$where = "nojurnal='" . $nojurnal . "'";
			$queryRB = "delete from `" . $dbname . "`.`keu_jurnalht` where " . $where;
			try {
				$owlPDO->exec($queryRB);
			} catch (PDOException $e) {
				$errorDB .= "Rollback 1 Error  :" . $e->getMessage();
			}
			$queryRB2 = updateQuery(
				$dbname,
				'keu_kasbankht',
				array('posting' => '1', 'novoucher' => '', 'pembayaran' => '0', 'nocek' => '0'),
				"notransaksi='" . $dataH[0]['notransaksi'] . "' and kodeorg='" . $dataH[0]['kodeorg'] . "'"
			);
			try {
				$owlPDO->exec($queryRB2);
			} catch (PDOException $e) {
				$errorDB .= "Rollback 2 Error  :" . $e->getMessage();
			}

			// Rollback Otomatis =======================================================

			if ($dataD[0]['hutangunit1'] == 1) {
				$whereoto = "nojurnal='" . $nojuroto . "'";
				$queryRBoto = "delete from `" . $dbname . "`.`keu_jurnalht` where " . $whereoto;
				try {
					$owlPDO->exec($queryRBoto);
				} catch (PDOException $e) {
					$errorDB .= "Rollback 3 Error  :" . $e->getMessage();
				}
			}

			echo "DB Error :\n" . $errorDB;
			exit;
		} else {
			// Posting Success
			#=== Add Counter Jurnal ===
			//Script simpan data dari hasil 'simpan' jika sukses
			$queryJ = updateQuery(
				$dbname,
				'keu_5kelompokjurnal',
				array('nokounter' => $tmpKonter[0]['nokounter'] + 1),
				"kodeorg='" . $kodept[$dataH[0]['kodeorg']] . "' and kodekelompok='" . $dataD[0]['kode'] . "' 
				and kodeunit='" . $dataH[0]['kodeorg'] . "' and periode='" . $periodejurnal . "' "
			);
			$errCounter = "";
			try {
				$owlPDO->exec($queryJ);
			} catch (PDOException $e) {
				$errCounter .= "Update Counter Parameter Jurnal Error :" . $e->getMessage();
			}

			if ($errCounter != "") {
				$queryJRB = updateQuery(
					$dbname,
					'keu_5kelompokjurnal',
					array('nokounter' => $tmpKonter[0]['nokounter']),
					"kodeorg='" . $kodept[$dataH[0]['kodeorg']] . "' and kodekelompok='" . $dataD[0]['kode'] . "' 
				and kodeunit='" . $dataH[0]['kodeorg'] . "' and periode='" . $periodejurnal . "' "
				);
				$errCounter = "";
				try {
					$owlPDO->exec($queryJRB);
				} catch (PDOException $e) {
					$errorJRB .= "Rollback Parameter Jurnal Error :" . $e->getMessage();
				}
				echo "DB Error :\n" . $errorJRB;
				exit;
			}

			//Kemungkinan ke - 1

			#=== Add Counter Jurnal Otomatis === =======================================
			if ($dataD[0]['hutangunit1'] == 1) {
				$queryJ = updateQuery(
					$dbname,
					'keu_5kelompokjurnal',
					array('nokounter' => $konteroto),
					"kodeorg='" . $indukpemilikhutang . "' and kodekelompok='" . $kodejurnal . "'
				and kodeunit='" . $pemilikhutang . "' and periode='" . $periodejurnal . "'"
				);

				$errCounter = "";
				try {
					$owlPDO->exec($queryJ);
				} catch (PDOException $e) {
					$errCounter .= "Update Counter Parameter Jurnal Error :" . $e->getMessage();
				}

				if ($errCounter != "") {
					$queryJRB = updateQuery(
						$dbname,
						'keu_5kelompokjurnal',
						array($noKon[0]['nokounter']),
						"kodeorg='" . $indukpemilikhutang . "' and kodekelompok='" . $kodejurnal . "'
					and kodeunit='" . $pemilikhutang . "' and periode='" . $periodejurnal . "'"
					);
					$errCounter = "";
					try {
						$owlPDO->exec($queryJRB);
					} catch (PDOException $e) {
						$errorJRB .= "Rollback Parameter Jurnal Error :" . $e->getMessage();
					}
					echo "DB Error :\n" . $errorJRB;
					exit;
				}
			}





			#jika pengobatan maka update sdm_pengobatan
			#kolom disi : kasbank,tanggalkasbank,jumlahkasbank
			#cek dlu apakah ini transaksi pembayaran pengobatan
			$str = "select * from " . $dbname . ".keu_kasbankdt where notransaksi='" . $dataD[0]['notransaksi'] . "' ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$strv = "select count(*) as jumtran from " . $dbname . ".sdm_pengobatanht where  notransaksi='" . $bar['nodok'] . "' ";
				$resv = $owlPDO->query($strv) or die(print " Gagal: " . PDOException::getMessage());
				$resv->setFetchMode(PDO::FETCH_ASSOC);
				$barv = $resv->fetch();
				$jumtran = $barv['jumtran'];
				if ($jumtran > 0) {
					$stru = " update " . $dbname . ".sdm_pengobatanht set 
					kasbank='" . $bar['notransaksi'] . "',
					jumlahkasbank='" . $bar['jumlah'] . "',		
					tanggalkasbank='" . $bar['tanggal'] . "' 
					where notransaksi='" . $bar['nodok'] . "' ";
					try {
						$owlPDO->exec($stru);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n";
						die();
					}
				}
			}


			#jika pjdinas maka update sdm_pjdinasht
			#kolom disi : dibayar,tglbayar
			#cek dlu apakah ini transaksi pembayaran pjdinas
			$str = "select * from " . $dbname . ".keu_kasbankdt where notransaksi='" . $dataD[0]['notransaksi'] . "' ";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				$strv = "select count(*) as jumtran from " . $dbname . ".sdm_pjdinasht where  notransaksi='" . $bar['nodok'] . "' ";
				$resv = $owlPDO->query($strv) or die(print " Gagal: " . PDOException::getMessage());
				$resv->setFetchMode(PDO::FETCH_ASSOC);
				$barv = $resv->fetch();
				$jumtran = $barv['jumtran'];
				if ($jumtran > 0) {
					$stru = " update " . $dbname . ".sdm_pjdinasht set 
					dibayar='" . $bar['jumlah'] . "',		
					tglbayar='" . date('Y-m-d') . "' 
					where notransaksi='" . $bar['nodok'] . "' ";
					try {
						$owlPDO->exec($stru);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n";
						die();
					}
				}
			}
		}


		#= jika berhasil update pembayaran=1
		$strupdate = " update " . $dbname . ".keu_kasbankht set  pembayaran='1' where notransaksi='" . $param['notransaksi'] . "' ";
		try {
			$owlPDO->exec($strupdate);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
	}


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
		$akuncacao[$bar['akunpiutang']] = $bar['akunpiutang'];
		$akuncacao[$bar['akunhutang']] = $bar['akunhutang'];
		$unitcaco[$bar['akunpiutang']] = $bar['kodeorg'];
		$unitcaco[$bar['akunhutang']] = $bar['kodeorg'];
	}

	$tipeorganisasi = makeOption($dbname, 'organisasi', 'kodeorganisasi,tipe');

	$str = "select * from " . $dbname . ".keu_kasbankdt where notransaksi='" . $param['notransaksi'] . "' ";
	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $res->fetch()) {

		#= jika noakun termasuk caco
		if (in_array($bar['noakun'], $akuncacao)) {

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

// exit("Error:".$a);
