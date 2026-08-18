<?php //@Copy nangkoelframework
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
try {

	$owlPDO->beginTransaction();
	$kodejurnal = "TGH01";
	$noinvoice = $_POST['noinvoice'];
	$kodePt = makeOption($dbname, "organisasi", "kodeorganisasi,induk");
	$franco = makeOption($dbname, "setup_franco", "id_franco,kodeunit");

	// $tanggaljurnal=tanggalsystemn(checkPostGet('tanggaljurnal','')); // KAGAK JADI: vienny + sabi 20211117
	$sTipe = "select * from " . $dbname . ".keu_tagihanht where noinvoice='" . $noinvoice . "'";
	$dataH = fetchData($sTipe);
	$tanggaljurnal = $dataH[0]['tanggalinvoice'];

	$sqlpo = selectQuery($dbname, "log_poht", "*", "nopo='" . $dataH[0]['nopo'] . "'");
	$respo = fetchData($sqlpo)[0];

	// $dataH[0]['tanggalinvoice']=$tanggaljurnal; // sebelumnya ambil tanggal tagihan, ganti jadi tanggal jurnal // KAGAK JADI: vienny + sabi 20211117
	//dirubah sesuai email pak yanto
	$arrPtCaco = array();
	$rppPPh = array();
	$totPPh = 0;
	$countdtppn = $totRpppn = 0;
	$countdtumuka = $totdtum = 0;
	$countdtpph = 0;
	$lstPPNBknHo = array();

	// [] => ffb 

	// print_r();exit("Error:A");

	// GRIR 2021
	$nilaidpp = 0;
	if ($dataH[0]['tipeinvoice'] == 'ffbe') {
		$noakungrir = '2111501'; # GRIR TBS
	} else {
		$noakungrir = '2111401'; # GRIR PO
	}

	// khusus jenis tagihan p (PO Barang Umum), pon (PO Non Inventory)
	// ambil dpp-nya
	// untuk GRIR, lempar ke RO
	if (($dataH[0]['tipeinvoice'] == 'p') || ($dataH[0]['tipeinvoice'] == 'pon') || ($dataH[0]['tipeinvoice'] == 'pocbd') || ($dataH[0]['tipeinvoice'] == 'rtg') || ($dataH[0]['tipeinvoice'] == 'rtn') || ($dataH[0]['tipeinvoice'] == 'ffbe')) {
		$nilaidpp = $dataH[0]['nilaidpp'];
		$franco = makeOption($dbname, "setup_franco", "id_franco,kodeunit");

		$sql = selectQuery($dbname, "log_poht", "*", "nopo='" . $dataH[0]['nopo'] . "'");
		$res = fetchData($sql)[0];
		$dataH[0]['unit'] = $franco[$res['idFrancoinvc']];
	}
	$trxnya = '';
	// end GRIR 2021

	#cek tipe unit
	$optTipeUnit = makeOption($dbname, "organisasi", "kodeorganisasi,tipe", "kodeorganisasi='" . $dataH[0]['unit'] . "'");
	$sTipe2 = "select * from " . $dbname . ".keu_tagihandt where noinvoice='" . $noinvoice . "' ";
	$dataD = fetchData($sTipe2);
	foreach ($dataD as $row) {
		if ($row['notransaksi'] != '') {
			$trxnya .= $row['notransaksi'] . ',';
		}
		// if(substr($row['noakun'],0,3)!='213'){

		if (substr($row['noakun'], 0, 3) != '212') {
			$totRp += $row['nilai'];
		}
		if (($row['noakun'] == '') || is_null($row['noakun'])) {
			if ($dataH[0]['tipeinvoice'] != 'ffb') {
				exit('Warning: Noakun Kosong');
			}
		}
		#khusus ppn digunakan di po nanti
		// if($row['noakun']=='1170111'){
		if ($row['noakun'] == '1160101') {
			$countdtppn += 1;
			$totRpppn += $row['nilai'];
			// if($optTipeUnit[$dataH[0]['unit']]!='HOLDING'){
			$lstPPNBknHo['unit'] = $dataH[0]['unit'];
			$lstPPNBknHo['pt'] = $dataH[0]['kodeorg'];
			$lstPPNBknHo['nilaiPPN'] = $totRpppn;
			$lstPPNBknHo['noinvoice'] = $dataH[0]['noinvoice'];
			$lstPPNBknHo['noakunppn'] = $row['noakun'];
			$lstPPNBknHo['kodesupplier'] = $dataH[0]['kodesupplier'];
			// }
		}

		if (substr($row['noakun'], 0, 5) == '11801') {
			$countdtumuka += 1;
			$totdtum += $row['nilai'];
			$akunum = $row['noakun'];
			$asset = $row['kodeasset'];

			// GRIR 2021
			if (($dataH[0]['tipeinvoice'] == 'p') || ($dataH[0]['tipeinvoice'] == 'pon') || ($dataH[0]['tipeinvoice'] == 'pocbd') || ($dataH[0]['tipeinvoice'] == 'rtg') || ($dataH[0]['tipeinvoice'] == 'rtn')) {
				$nilaidpp -= $row['nilai']; // nilai um tersimpan minus di dt
			}
			// end GRIR 2021
		}

		// if(($dataH[0]['tipeinvoice']!='tck')&&($dataH[0]['tipeinvoice']!='tpk')){
		// if(substr($row['noakun'], 0,3)=='213'){
		# Default
		// if(substr($row['noakun'], 0,3)=='212'){
		// 	$countdtpph+=1;
		// 	$rppPPh[$row['noakun']]+=($row['nilai']*$dataH[0]['kurs']);
		// 	$totPPh+=($row['nilai']*$dataH[0]['kurs']);
		// }
		# End KSP
		if ($dataH[0]['tipeinvoice'] == 'p' || $dataH[0]['tipeinvoice'] == 'pon' || $dataH[0]['tipeinvoice'] == 'pocbd') {

			if ($respo['penambahpph22'] == '1') {
				if ($row['noakun'] == '1160103') { # PPh 22
					$countdtpph += 1;
					$rppPPh[$row['noakun']] += ($row['nilai'] * $dataH[0]['kurs']);
					$totPPh += ($row['nilai'] * $dataH[0]['kurs']);
				}
			} else {
				if ($row['noakun'] == '2120801') { # PPh 22
					$countdtpph += 1;
					$rppPPh[$row['noakun']] += ($row['nilai'] * $dataH[0]['kurs']);
					$totPPh += ($row['nilai'] * $dataH[0]['kurs']);

					# jika akun hutang kurangi pph maupun ppn
					$nilaidpp -= ($row['nilai'] * $dataH[0]['kurs']);
				}
			}

			if ($row['noakun'] == '1160104') { # PPh 23
				$countdtpph += 1;
				$rppPPh[$row['noakun']] += ($row['nilai'] * $dataH[0]['kurs']);
				$totPPh += ($row['nilai'] * $dataH[0]['kurs']);
			}
		} else {
			if (substr($row['noakun'], 0, 3) == '212') {
				$countdtpph += 1;
				$rppPPh[$row['noakun']] += ($row['nilai'] * $dataH[0]['kurs']);
				$totPPh += ($row['nilai'] * $dataH[0]['kurs']);
			}
		}
		// }

		$akunkebun = (substr($row['noakun'], 0, 3) == '128' or substr($row['noakun'], 0, 3) == '126' or substr($row['noakun'], 0, 3) == '621' or substr($row['noakun'], 0, 3) == '611');
		if ($akunkebun and $row['kodeblok'] == '' and $dataH[0]['tipeinvoice'] == 'ot') {
			echo "Akun Tanaman Harus di Lengkapi dengan Blok\n";
			echo "Warningsistem";
			exit;
		}
		if ($akunkebun and $optTipeUnit[$dataH[0]['unit']] != 'KEBUN' and $dataH[0]['tipeinvoice'] == 'ot') {
			echo "Kode Unit Akun Tanaman harus di KEBUN\n";
			echo "Warningsistem";
			exit;
		}
	}
	if ($trxnya != '') {
		$trxnya = substr($trxnya, 0, -1);
	}

	if ($dataH[0]['tipeinvoice'] == 'um' && $countdtumuka == 0) {
		exit('Warning : Jika jenis invoice uang muka, pada detail harus ada uang muka.');
	}

	$sKode = "select * from " . $dbname . ".keu_5jenistagihan where kode='" . $dataH[0]['tipeinvoice'] . "'";
	$rKode = fetchData($sKode);
	//exit('warning'.$sKode.'__'.$rKode[0]['jurnal']);
	$statJurnal = $rKode[0]['jurnal'];
	$optSupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $dataH[0]['kodesupplier'] . "'");
	$optTipeSupp = makeOption($dbname, 'log_5supkelompok', 'supplierid,tipe', "supplierid='" . $dataH[0]['kodesupplier'] . "'");
	$sklsup = "select * from " . $dbname . ".log_5klsupplier";
	$rklsup = fetchData($sklsup);
	foreach ($rklsup as $key => $value) {
		$optAkun[$value['tipe']] = $value['noakun'];
	}


	$dataRes['header'] = array();
	$dataRes['detail'] = array();
	$dataRes['headercaco'] = array();
	$dataRes['detailcaco'] = array();
	$dataRes['headerrk'] = array();
	$dataRes['detailrk'] = array();
	#=== Cek if posted ===
	$error0 = "";
	if ($dataH[0]['posting'] == 1) {
		$error0 .= $_SESSION['lang']['errisposted'];
	}
	if ($error0 != '') {
		echo "Data Error :\n" . $error0;
		exit;
	}


	#= jika NVM maka tanggal jurnal yang dipakai adalah tanggal invoice
	#= 17 Maret 2020
	if ($statJurnal == '1') {
		// $dataH[0]['tanggalinvoice']=$dataH[0]['tanggalinvoice']; disesuaikan dengan tampilan form invoice
	}


	if ($dataH[0]['tanggalinvoice'] == '0000-00-00' || $dataH[0]['tanggalinvoice'] == '') {
		exit("Warning:Format Tanggal Salah");
	}



	#1. Data Header
	# Get Journal Counter
	$queryJ = selectQuery(
		$dbname,
		'keu_5kelompokjurnal',
		'nokounter',
		"kodeorg='" . $dataH[0]['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "' 
		and kodeunit='" . $dataH[0]['unit'] . "' and periode='" . substr($dataH[0]['tanggalinvoice'], 0, 7) . "'"
	);
	$tmpKonter = fetchData($queryJ);
	if (empty($tmpKonter)) {
		exit("Warning: Kelompok Jurnal {$kodejurnal} pada unit {$dataH[0]['unit']} dan periode " . substr($dataH[0]['tanggalinvoice'], 0, 7) . " belum ada. Silahkan setupkan di <b>KEUANGAN > SETUP > KELOMPOK JURNAL</b>.</b>");
	}
	$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
	# Prep No Jurnal
	$nojurnal = str_replace('-', '', $dataH[0]['tanggalinvoice']) . "/" . $dataH[0]['unit'] . "/" . $kodejurnal . "/" . $konter;

	$str = "select count(*) as jumlah from " . $dbname . ".keu_tagihandt where noinvoice='" . $dataH[0]['noinvoice'] . "' and noaruskas=''";
	$res = fetchdata($str);
	$noaruskaskosong = $res[0]['jumlah'];
	if ($noaruskaskosong > '0') {
		exit("Warning:Data detail masih ada nomor arus kas kosong");
	}

	// if($_SESSION['standard']['username']=='tim.owl3'){
	// exit("error:".$noaruskaskosong);			
	// }


	switch ($statJurnal) {
		case '1':
			#=== Cek   Detail dan Header harus sama ===
			$tmpJml = 0;
			foreach ($dataD as $row) {
				// if($row['nilai']>0)
				$tmpJml += $row['nilai'];
			}
			$selisih = abs($tmpJml - $dataH[0]['nilaiinvoice']);
			// exit('warning'.$selisih."__".$tmpJml."__".$dataH[0]['nilaiinvoice']);
			if ($selisih > 0.01) {
				echo "Warning : Jumlah Header dan Detail Tidak Balance\n";
				echo "Header:" . number_format($dataH[0]['nilaiinvoice']) . "\n";
				echo "Detail:" . number_format($tmpJml) . "\n";
				echo "Posting Gagal";
				exit;
			}

			// exit("Error:".$dataH[0]['nilaiinvoice']._.$tmpJml);

			# Prep Header
			$dataRes['header'] = array(
				'nojurnal' => $nojurnal,
				'kodejurnal' => $kodejurnal,
				'tanggal' => $dataH[0]['tanggalinvoice'],
				'tanggalentry' => date('Ymd'),
				'posting' => '0',
				'totaldebet' => '0',
				'totalkredit' => '0',
				'amountkoreksi' => '0',
				'noreferensi' => $dataH[0]['noinvoice'],
				'autojurnal' => '1',
				'matauang' => 'IDR',
				'kurs' => '1',
				'revisi' => '0'
			);
			#====cek periode
			$tgl = str_replace("-", "", $dataH[0]['tanggalinvoice']);
			$sPeriode = "select * from " . $dbname . ".setup_periodeakuntansi 
			           where kodeorg='" . $dataH[0]['unit'] . "' and tutupbuku=0 order by periode desc";
			$rPeriode = fetchdata($sPeriode);
			$tglakutansi = str_replace("-", "", $rPeriode[0]['tanggalmulai']);
			if ($tglakutansi > $tgl) {
				exit("Warning:Tanggal invoice diluar periode aktif, Periode invoice : " . substr($dataH[0]['tanggalinvoice'], 0, 7) . " ; Periode Aktif : " . $rPeriode[0]['periode'] . " ");
			}

			#=== Cek if data not exist ===
			$error1 = "";
			if (count($dataH) == 0) {
				$error1 .= $_SESSION['lang']['errheadernotexist'] . "\n";
			}
			if (count($dataD) == 0) {
				$error1 .= $_SESSION['lang']['errdetailnotexist'] . "\n";
			}
			if ($error1 != '') {
				echo "Data Error :\n" . $error1;
				exit;
			}

			// $akunKredit=$optAkun[$optTipeSupp[$dataH[0]['kodesupplier']]];
			// if(substr($dataH[0]['kodesupplier'],0,1)=='R'){
			// 	$akunKredit=$optAkun[$optTipeSupp[$dataH[0]['kodesupplier']]];
			// }	
			$akunKredit = $dataH[0]['noakun'];

			#total debet
			$noUrut = 1;
			foreach ($dataD as $row) {
				$nmAkun = makeOption($dbname, "keu_5akun", "noakun,namaakun", "noakun='" . $row['noakun'] . "'");
				$namaAkun = $nmAkun[$row['noakun']];
				// $ketDef='Beban Biaya  '.$namaAkun.', tagihan  jenis '.$rKode[0]['namajenis'].' a/n: '.$optSupp[$dataH[0]['kodesupplier']].',noinvoice supplier :'.$dataH[0]['noinvoicesupplier'].',No Faktur Pajak :'.$dataH[0]['nofp'];
				$ketDef = $optSupp[$dataH[0]['kodesupplier']] . ', PO/SO/Dok: ' . $dataH[0]['nopo'] . ', FP: ' . $dataH[0]['nofp'] . ', Trx: ' . $trxnya;
				if (substr($row['noakun'], 0, 4) == '2130') {
					//$ketDef='Pengakuan '.$namaAkun.' a/n : '.$optSupp[$dataH[0]['kodesupplier']].',noinvoice supplier :'.$dataH[0]['noinvoicesupplier'].',No Faktur Pajak :'.$dataH[0]['nofp'].', Jenis Tagihan :'.$rKode[0]['namajenis'];
					//$dataH[0]['nilaiinvoice']=$dataH[0]['nilaiinvoice']+$row['nilai'];
				}
				// elseif($row['nilai']<0){
				// if(substr($row['noakun'],0,5)!='11803'){
				// $dataH[0]['nilaiinvoice']=$dataH[0]['nilaiinvoice']+$row['nilai'];
				// }
				// }
				// if($row['noakun']=='1170111'){
				if ($row['noakun'] == '1160101') {
					// $ketDef='Biaya '.$namaAkun.' a/n : '.$optSupp[$dataH[0]['kodesupplier']].',noinvoice supplier :'.$dataH[0]['noinvoicesupplier'].',No Faktur Pajak :'.$dataH[0]['nofp'].', Jenis Tagihan :'.$rKode[0]['namajenis'];
					$ketDef = $optSupp[$dataH[0]['kodesupplier']] . ', PO/SO/Dok: ' . $dataH[0]['nopo'] . ', FP: ' . $dataH[0]['nofp'] . ', Trx: ' . $trxnya;
				}

				//substr($row['noakun'],0,5)=='11803'
				// if(substr($row['noakun'],0,5)=='11803' || substr($row['noakun'],0,3)=='213'){
				// if(substr($row['noakun'],0,5)=='11803'){
				if (substr($row['noakun'], 0, 5) == '11801') {
					continue;
				}

				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $dataH[0]['tanggalinvoice'],
					'nourut' => $noUrut,
					'noakun' => $row['noakun'],
					'keterangan' => $ketDef,
					'jumlah' => ($row['nilai'] * $dataH[0]['kurs']),
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $dataH[0]['unit'],
					'kodekegiatan' => $row['kodekegiatan'],
					'kodeasset' => $row['kodeasset'],
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => '',
					'kodesupplier' => $dataH[0]['kodesupplier'],
					'noreferensi' => $dataH[0]['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => $row['kodevhc'],
					'nodok' => $dataH[0]['nopo'],
					'kodeblok' => $row['kodeblok'],
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
			}
			$nmAkun = makeOption($dbname, "keu_5akun", "noakun,namaakun", "noakun='" . $akunKredit . "'");
			$namaAkun = $nmAkun[$akunKredit];
			// 'keterangan'=>'Pengakuan '.$namaAkun.', a/n '.$optSupp[$dataH[0]['kodesupplier']].',noinvoice supplier :'.$dataH[0]['noinvoicesupplier'].',No Faktur Pajak :'.$dataH[0]['nofp'].', Jenis Tagihan :'.$rKode[0]['namajenis'],
			$dataRes['detail'][] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $dataH[0]['tanggalinvoice'],
				'nourut' => $noUrut,
				'noakun' => $akunKredit,
				'keterangan' => $optSupp[$dataH[0]['kodesupplier']] . ', PO/SO/Dok: ' . $dataH[0]['nopo'] . ', FP: ' . $dataH[0]['nofp'] . ', Trx: ' . $trxnya,
				'jumlah' => ($dataH[0]['nilaiinvoice'] * $dataH[0]['kurs']) * (-1),
				'matauang' => 'IDR',
				'kurs' => '1',
				'kodeorg' => $dataH[0]['unit'],
				'kodekegiatan' => '',
				'kodeasset' => '',
				'kodebarang' => '',
				'nik' => '',
				'kodecustomer' => '',
				'kodesupplier' => $dataH[0]['kodesupplier'],
				'noreferensi' => $dataH[0]['noinvoice'],
				'noaruskas' => '',
				'kodevhc' => $row['kodevhc'],
				'nodok' => $dataH[0]['nopo'],
				'kodeblok' => '',
				'revisi' => '0',
				'kodesegment' => ''
			);
			$noUrut++;

			if ($countdtumuka > 0) {

				$whrum = "";
				if ($dataH[0]['noinvoiceum'] != '') {
					$whrum = " and noinvoice='" . $dataH[0]['noinvoiceum'] . "'";
				} else {
					$whrum = " and nopo='" . $dataH[0]['nopo'] . "'";
				}
				$resht = array();
				$optIndukByr = array();
				$pt = "";
				$unittrans = "";
				//Check jika ada akun R/K
				$strht = "select noinvoice,unit,kodeorg from " . $dbname . ".keu_tagihanht where tipeinvoice='um' " . $whrum;
				//exit('warning'.$strht);
				$resht = fetchData($strht);
				#cek sudah dikasbank/belum
				if (count($resht) != 0) {
					$sKb = "select posting,a.notransaksi,a.kodeorg from " . $dbname . ".keu_kasbankdt a left join " . $dbname . ".keu_kasbankht b on a.notransaksi=b.notransaksi
					      where a.keterangan1='" . $resht[0]['noinvoice'] . "'";
					$rKb = fetchData($sKb);
					if (count($rKb) == 0) {
						exit('warning: Belum ada pembayaran atas no tagihan ini.');
					}
					if ($rKb[0]['posting'] == 0) {
						exit('warning: No.Transaksi ' . $rKb[0]['notransaksi'] . ' belum diposting.');
					}
					$optIndukByr = makeOption($dbname, "organisasi", "kodeorganisasi,induk", "kodeorganisasi='" . $rKb[0]['kodeorg'] . "'");
					$pt = $optIndukByr[$rKb[0]['kodeorg']];
					$unittrans = $rKb[0]['kodeorg'];
				}

				#cari pt pembayar jika beda dengan unit uang muka pada tagihanht

				$akunrkpiutang = "";
				$akunrkhutang = "";
				$nojurnalrk = '';
				$rescaco = array();
				$rescaco2 = array();
				if ($pt != "") {
					if ($dataH[0]['kodeorg'] == $pt) {
						if ($dataH[0]['unit'] != $unittrans) {
							$arrPtCaco[$dataH[0]['kodeorg']] = $dataH[0]['kodeorg'];
							$strcaco = "select akunpiutang from " . $dbname . ".keu_5caco where kodeorg='" . $dataH[0]['unit'] . "' and jenis='intra'";
							$rescaco = fetchData($strcaco);
							$akunrkpiutang = $rescaco[0]['akunpiutang'];

							if ($akunrkpiutang == '') {
								exit("Warning : Account intraco or interco not available for " . $dataH[0]['unit'] . ". Please setting on menu Finance > setup > COA for Intra/Interco.");
							}

							$strcaco2 = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . $unittrans . "' and jenis='intra'";
							$rescaco2 = fetchData($strcaco2);
							$akunrkhutang = $rescaco2[0]['akunhutang'];

							if ($akunrkhutang == '') {
								exit("Warning : Account intraco or interco not available for " . $unittrans . ". Please setting on menu Finance > setup > COA for Intra/Interco.");
							}
						}
					} else {
						$arrPtCaco[$pt] = $pt;
						$strcaco = "select akunpiutang from " . $dbname . ".keu_5caco where kodeorg='" . $dataH[0]['unit'] . "' and jenis='inter'";
						$rescaco = fetchData($strcaco);
						$akunrkpiutang = $rescaco[0]['akunpiutang'];

						if ($akunrkpiutang == '') {
							exit("Warning : Account intraco or interco not available for " . $dataH[0]['unit'] . ". Please setting on menu Finance > setup > COA for Intra/Interco.");
						}

						$strcaco2 = "select akunhutang from " . $dbname . ".keu_5caco where kodeorg='" . $unittrans . "' and jenis='inter'";
						$rescaco2 = fetchData($strcaco2);
						$akunrkhutang = $rescaco2[0]['akunhutang'];

						if ($akunrkhutang == '') {
							exit("Warning : Account intraco or interco not available for " . $unittrans . ". Please setting on menu Finance > setup > COA for Intra/Interco.");
						}

						#1. Data Header untuk beda pt
						# Get Journal Counter
						$queryJ = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodeorg='" . $pt . "' and kodekelompok='" . $kodejurnal . "' and kodeunit='" . $unittrans . "' and periode='" . substr($dataH[0]['tanggalinvoice'], 0, 7) . "'"
						);
						$tmpKonter = fetchData($queryJ);
						if (empty($tmpKonter)) {
							exit("Warning: Kelompok Jurnal {$kodejurnal} pada unit {$unittrans} dan periode " . substr($dataH[0]['tanggalinvoice'], 0, 7) . " belum ada. Silahkan setupkan di <b>KEUANGAN > SETUP > KELOMPOK JURNAL</b>.</b>");
						}
						$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
						# Prep No Jurnal
						$nojurnalrk = str_replace('-', '', $dataH[0]['tanggalinvoice']) . "/" . $unittrans . "/" .
							$kodejurnal . "/" . $konter;
					}
				}

				if ($pt != "") {
					if ($akunrkpiutang == '') {
						// koq kalo kosong bikin detail?
						// $dataRes['detail'][] = array(
						// 	'nojurnal'=>$nojurnal,
						// 	'tanggal'=>$dataH[0]['tanggalinvoice'],
						// 	'nourut'=>$noUrut,
						// 	'noakun'=>$akunrkpiutang,
						// 	'keterangan'=>'R/K piutang berdasarkan uang muka atas, No Invoice:'.$dataH[0]['noinvoiceum'],
						// 	'jumlah'=>$totdtum*(-1),
						// 	'matauang'=>'IDR',
						// 	'kurs'=>'1',
						// 	'kodeorg'=>$dataH[0]['unit'],
						// 	'kodekegiatan'=>'',
						// 	'kodeasset'=>'',
						// 	'kodebarang'=>'',
						// 	'nik'=>'',
						// 	'kodecustomer'=>'',
						// 	'kodesupplier'=>$dataH[0]['kodesupplier'],
						// 	'noreferensi'=>$dataH[0]['noinvoice'],
						// 	'noaruskas'=>'',
						// 	'kodevhc'=>$row['kodevhc'],
						// 	'nodok'=>$dataH[0]['nopo'],
						// 	'kodeblok'=>'',
						// 	'revisi'=>'0',
						// 	'kodesegment' => ''
						// );
						// $noUrut++;
					}
				}
				if ($pt != "") {
					if ($akunrkhutang != '') {
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $dataH[0]['tanggalinvoice'],
							'nourut' => $noUrut,
							'noakun' => $akunrkhutang,
							'keterangan' => 'R/K hutang berdasarkan uang muka atas, No Invoice:' . $dataH[0]['noinvoiceum'],
							'jumlah' => $totdtum,
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $dataH[0]['unit'],
							'kodekegiatan' => $row['kodekegiatan'],
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $dataH[0]['kodesupplier'],
							'noreferensi' => $dataH[0]['noinvoice'],
							'noaruskas' => '',
							'kodevhc' => $row['kodevhc'],
							'nodok' => $dataH[0]['nopo'],
							'kodeblok' => $row['kodeblok'],
							'revisi' => '0',
							'kodesegment' => ''
						);
						$noUrut++;
					}
				}
				if ($pt != "") {
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $dataH[0]['tanggalinvoice'],
						'nourut' => $noUrut,
						'noakun' => $akunKredit,
						'keterangan' => 'Debit hutang berdasarkan uang muka atas, No Invoice:' . $dataH[0]['noinvoiceum'],
						'jumlah' => $totdtum * (-1),
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $dataH[0]['unit'],
						'kodekegiatan' => $row['kodekegiatan'],
						'kodeasset' => $asset,
						'kodebarang' => '',
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $dataH[0]['kodesupplier'],
						'noreferensi' => $dataH[0]['noinvoice'],
						'noaruskas' => '',
						'kodevhc' => $row['kodevhc'],
						'nodok' => $dataH[0]['nopo'],
						'kodeblok' => $row['kodeblok'],
						'revisi' => '0',
						'kodesegment' => ''
					);
					$noUrut++;
				}
				if ($akunrkpiutang != '') {
					$noUrut = 1;
					if ($nojurnalrk == '') {
						# Get Journal Counter
						$konterrk = $konter + 1;
						# Prep No Jurnal
						$nojurnalrk = str_replace('-', '', $dataH[0]['tanggalinvoice']) . "/" . $unittrans . "/" . $kodejurnal . "/" . $konterrk;
					}

					# Prep Header
					$dataRes['headerrk'] = array(
						'nojurnal' => $nojurnalrk,
						'kodejurnal' => $kodejurnal,
						'tanggal' => $dataH[0]['tanggalinvoice'],
						'tanggalentry' => date('Ymd'),
						'posting' => '0',
						'totaldebet' => '0',
						'totalkredit' => '0',
						'amountkoreksi' => '0',
						'noreferensi' => $dataH[0]['noinvoice'],
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);


					$dataRes['detailrk'][] = array(
						'nojurnal' => $nojurnalrk,
						'tanggal' => $dataH[0]['tanggalinvoice'],
						'nourut' => $noUrut,
						'noakun' => $akunrkpiutang,
						'keterangan' => 'R/K piutang berdasarkan uang muka atas, No Invoice:' . $dataH[0]['noinvoiceum'],
						'jumlah' => $totdtum * (-1),
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $unittrans,
						'kodekegiatan' => $row['kodekegiatan'],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $dataH[0]['kodesupplier'],
						'noreferensi' => $dataH[0]['noinvoice'],
						'noaruskas' => '',
						'kodevhc' => $row['kodevhc'],
						'nodok' => $dataH[0]['nopo'],
						'kodeblok' => $row['kodeblok'],
						'revisi' => '0',
						'kodesegment' => ''
					);
					$noUrut++;


					$dataRes['detailrk'][] = array(
						'nojurnal' => $nojurnalrk,
						'tanggal' => $dataH[0]['tanggalinvoice'],
						'nourut' => $noUrut,
						'noakun' => $akunum,
						'keterangan' => 'Kredit uang muka atas, No Invoice:' . $dataH[0]['noinvoiceum'],
						'jumlah' => $totdtum,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $unittrans,
						'kodekegiatan' => $row['kodekegiatan'],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $dataH[0]['kodesupplier'],
						'noreferensi' => $dataH[0]['noinvoice'],
						'noaruskas' => '',
						'kodevhc' => $row['kodevhc'],
						'nodok' => $dataH[0]['nopo'],
						'kodeblok' => $row['kodeblok'],
						'revisi' => '0',
						'kodesegment' => ''
					);
					$noUrut++;
				}
				if ($pt == "") {
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $dataH[0]['tanggalinvoice'],
						'nourut' => $noUrut,
						'noakun' => $akunKredit,
						'keterangan' => 'Debit hutang berdasarkan akun uang muka ',
						'jumlah' => $totdtum * (-1),
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $dataH[0]['unit'],
						'kodekegiatan' => $row['kodekegiatan'],
						'kodeasset' => $asset,
						'kodebarang' => '',
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $dataH[0]['kodesupplier'],
						'noreferensi' => $dataH[0]['noinvoice'],
						'noaruskas' => '',
						'kodevhc' => $row['kodevhc'],
						'nodok' => $dataH[0]['nopo'],
						'kodeblok' => $row['kodeblok'],
						'revisi' => '0',
						'kodesegment' => ''
					);
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $dataH[0]['tanggalinvoice'],
						'nourut' => $noUrut,
						'noakun' => $akunum,
						'keterangan' => 'Kredit atas Akun Hutang',
						'jumlah' => $totdtum,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $dataH[0]['unit'],
						'kodekegiatan' => $row['kodekegiatan'],
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $dataH[0]['kodesupplier'],
						'noreferensi' => $dataH[0]['noinvoice'],
						'noaruskas' => '',
						'kodevhc' => $row['kodevhc'],
						'nodok' => $dataH[0]['nopo'],
						'kodeblok' => $row['kodeblok'],
						'revisi' => '0',
						'kodesegment' => ''
					);
					$noUrut++;
				}
			}


			break;
		case '0':
			// echo"<pre>";
			// print_r($dataD);
			// exit("Error:A");
			#= validasi data
			#= validasi nilai invoice harus sama dengan total dt
			foreach ($dataD as $row) {
				// if($row['nilai']>0)
				$tmpJml += $row['nilai'];
			}
			#= uang muka ditambahkan juga
			if ($dataH[0]['tipeinvoice'] == 'um') {
				$selisih = abs($tmpJml - $dataH[0]['nilaiinvoice']);
			} else {
				// $selisih = abs($tmpJml-$totdtum - $dataH[0]['nilaiinvoice']);
				$selisih = abs($tmpJml - $totdtum - $dataH[0]['nilaiinvoice']);
			}

			// exit('warning'.$selisih."__".$tmpJml."__".$dataH[0]['nilaiinvoice']);
			if ($selisih > 0.01) {
				echo "Warning : nilai invoice header dan total nilai detail tidak balance\n";
				echo "Header:" . number_format($dataH[0]['nilaiinvoice']) . "\n";
				echo "Detail:" . number_format($tmpJml) . "\n";
				echo "Posting Gagal";
				exit;
			}


			// if($dataH[0]['tipeinvoice']=='um'){
			// #continue;
			// }
			// $akunKredit=$optAkun[$optTipeSupp[$dataH[0]['kodesupplier']]];	
			$akunKredit = $dataH[0]['noakun'];
			$noUrut = $noUrutcaco = 1;
			$namaAkunData = "";
			$totRpppn = 0;
			// exit("Error:".$totdtum);
			if ($countdtppn > 0 or $countdtumuka > 0 or $countdtpph > 0 or $nilaidpp > 0) { // nilaidpp untuk bikin GRIR 2021
				##ht
				$dataRes['header'] = array(
					'nojurnal' => $nojurnal,
					'kodejurnal' => $kodejurnal,
					'tanggal' => $dataH[0]['tanggalinvoice'],
					'tanggalentry' => date('Ymd'),
					'posting' => '0',
					'totaldebet' => '0',
					'totalkredit' => '0',
					'amountkoreksi' => '0',
					'noreferensi' => $dataH[0]['noinvoice'],
					'autojurnal' => '1',
					'matauang' => 'IDR',
					'kurs' => '1',
					'revisi' => '0'
				);
			}

			// GRIR 2021
			// $ketGRIR='GRIR a/n : '.$optSupp[$dataH[0]['kodesupplier']].', No PO :'.$dataH[0]['nopo'].', Jenis Tagihan :'.$rKode[0]['namajenis'];
			// $ketbalikum='Balik UM a/n : '.$optSupp[$dataH[0]['kodesupplier']].', No PO :'.$dataH[0]['nopo'].', Jenis Tagihan :'.$rKode[0]['namajenis'];
			$ketGRIR = 'GRIR a/n: ' . $optSupp[$dataH[0]['kodesupplier']] . ', PO/SO/Dok: ' . $dataH[0]['nopo'] . ', Trx: ' . $trxnya;
			$ketbalikum = 'Balik UM a/n: ' . $optSupp[$dataH[0]['kodesupplier']] . ', PO/SO/Dok: ' . $dataH[0]['nopo'] . ', Trx: ' . $trxnya;
			if ((($dataH[0]['tipeinvoice'] == 'p' || $dataH[0]['tipeinvoice'] == 'pon' || $dataH[0]['tipeinvoice'] == 'pocbd') and ($nilaidpp >= 0)) or ($dataH[0]['tipeinvoice'] == 'rtg' || $dataH[0]['tipeinvoice'] == 'rtn') or ($dataH[0]['tipeinvoice'] == 'ffbe')) {


				if ($nilaidpp == 0) {
					#= kasusnya hanya membalikkan angka uang muka, jika uang muka full, jadi ambil data nilai uang muka yang di abskan
					// exit("Error:".$totdtum);
					$nilaibalikuangmuka = $totdtum * -1;
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $dataH[0]['tanggalinvoice'],
						'nourut' => $noUrut,
						'noakun' => $noakungrir,
						'keterangan' => $ketGRIR,
						'jumlah' => ($nilaibalikuangmuka),
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $dataH[0]['unit'],
						'kodekegiatan' => '',
						'kodeasset' => $row['kodeasset'],
						'kodebarang' => '',
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $dataH[0]['kodesupplier'],
						'noreferensi' => $dataH[0]['noinvoice'],
						'noaruskas' => '',
						'kodevhc' => $row['kodevhc'],
						'nodok' => $dataH[0]['nopo'],
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => ''
					);
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $dataH[0]['tanggalinvoice'],
						'nourut' => $noUrut,
						'noakun' => $akunKredit,
						'keterangan' => $ketGRIR,
						'jumlah' => ($nilaibalikuangmuka) * (-1),
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $dataH[0]['unit'],
						'kodekegiatan' => '',
						'kodeasset' => $row['kodeasset'],
						'kodebarang' => '',
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $dataH[0]['kodesupplier'],
						'noreferensi' => $dataH[0]['noinvoice'],
						'noaruskas' => '',
						'kodevhc' => $row['kodevhc'],
						'nodok' => $dataH[0]['nopo'],
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => ''
					);
					$noUrut++;
				} else {
					if (($dataH[0]['tipeinvoice'] == 'p' || $dataH[0]['tipeinvoice'] == 'pon' || $dataH[0]['tipeinvoice'] == 'pocbd')) {
						# Cek jika franco terima dan franco pembayaran beda di PO
						$qPO = selectQuery($dbname, "log_poht", "nopo, idFranco, idFrancoinvc", "nopo='" . $dataH[0]['nopo'] . "'");
						$resPO = fetchData($qPO)[0];
						$unitFranco = $franco[$resPO['idFranco']];
						$unitFrancoInv = $franco[$resPO['idFrancoinvc']];

						if ($unitFranco != $unitFrancoInv) {
							# Jurnal Pengirim
							$jenisinduk = 'inter';
							if ($kodePt[$unitFranco] == $kodePt[$unitFrancoInv]) $jenisinduk = 'intra';

							$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $unitFranco . "'";
							$query = selectQuery($dbname, 'keu_5caco', 'akunpiutang', $whereNocaco);
							$noKon = fetchData($query);
							$noakuncaco = $noKon[0]['akunpiutang'];

							if ($noakuncaco == '') {
								throw new PDOException("No. Akun Interco/Intraco masih kosong untuk " . $unitFranco . " ke " . $unitFrancoInv . " atau sebaliknya, Hubungi Pihak Accounting / IT ");
							}

							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $dataH[0]['tanggalinvoice'],
								'nourut' => $noUrut,
								'noakun' => $noakuncaco,
								'keterangan' => $ketGRIR,
								'jumlah' => ($nilaidpp),
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $dataH[0]['unit'],
								'kodekegiatan' => '',
								'kodeasset' => $row['kodeasset'],
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $dataH[0]['kodesupplier'],
								'noreferensi' => $dataH[0]['noinvoice'],
								'noaruskas' => '',
								'kodevhc' => $row['kodevhc'],
								'nodok' => $dataH[0]['nopo'],
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => ''
							);
							$noUrut++;
							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $dataH[0]['tanggalinvoice'],
								'nourut' => $noUrut,
								'noakun' => $akunKredit,
								'keterangan' => $ketGRIR,
								'jumlah' => ($nilaidpp) * (-1),
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $dataH[0]['unit'],
								'kodekegiatan' => '',
								'kodeasset' => $row['kodeasset'],
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $dataH[0]['kodesupplier'],
								'noreferensi' => $dataH[0]['noinvoice'],
								'noaruskas' => '',
								'kodevhc' => $row['kodevhc'],
								'nodok' => $dataH[0]['nopo'],
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => ''
							);
							$noUrut++;

							#= jurnal penerima
							#= akun CACO
							$whereNocaco = "jenis='" . $jenisinduk . "' and kodeorg='" . $unitFrancoInv . "'";
							$query = selectQuery($dbname, 'keu_5caco', 'akunhutang', $whereNocaco);
							$noKon = fetchData($query);
							$noakuncaco = $noKon[0]['akunhutang'];

							if ($noakuncaco == '') {
								throw new PDOException("No. Akun Interco/Intraco masih kosong untuk " . $unitFrancoInv . " ke " . $unitFranco . " atau sebaliknya, Hubungi Pihak Accounting / IT ");
							}

							# Get Journal Counter
							$kodejurnalPenerima = "M";
							$queryJ = selectQuery(
								$dbname,
								'keu_5kelompokjurnal',
								'nokounter',
								"kodeorg='" . $kodePt[$unitFranco] . "' and kodekelompok='" . $kodejurnalPenerima . "' and kodeunit='" . $unitFranco . "' and periode='" . substr($dataH[0]['tanggalinvoice'], 0, 7) . "'"
							);

							$tmpKonterPenerima = fetchData($queryJ);
							$konterPenerima = addZero($tmpKonterPenerima[0]['nokounter'] + 1, 4);

							## START CEK KONTER
							# TEMPNOJURNAL
							$TempJurnal = $nojurnalPenerima = str_replace('-', '', $dataH[0]['tanggalinvoice']) . "/" . $unitFranco . "/" . $kodejurnalPenerima . "/";

							## CEK KELOMPOK JURNAL
							$queryJro = selectQuery(
								$dbname,
								'keu_5kelompokjurnal',
								'nokounter',
								"kodeorg = '" . $kodePt[$unitFranco] . "' 
												AND kodekelompok = '" . $kodejurnalPenerima . "' 
												AND kodeunit = '" . $unitFranco . "' 
												AND periode = '" . substr($dataH[0]['tanggalinvoice'], 0, 7) . "'"
							);
							$tmpKonterex = fetchData($queryJro);
							$konterex = $tmpKonterex[0]['nokounter'];

							## CEK COUNTER TERAKHIR JURNAL HT
							$strCounter5 = "
									SELECT 
										MAX(CAST(RIGHT(nojurnal, 4) AS UNSIGNED)) AS last_counter
									FROM " . $dbname . ".keu_jurnalht
									WHERE nojurnal LIKE '%" . $TempJurnal . "%'
									AND kodejurnal = '" . $kodejurnalPenerima . "' ";
							$resCounter5      = fetchdata($strCounter5);
							$counterTerakhir5 = (int) ($resCounter5[0]['last_counter'] ?? 0);

							if ($konterex < $counterTerakhir5) {
								$konterPenerima = $counterTerakhir5 + 1;

								#= update counter jurnal
								$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konterPenerima . "' where kodeunit='" . $unitFranco . "' and kodekelompok='" . $kodejurnalPenerima . "' and periode='" . substr($dataH[0]['tanggalinvoice'], 0, 7) . "' ";
								$owlPDO->exec($str);
							}

							## END CEK KONTER

							# Prep No Jurnal
							$nojurnalPenerima = str_replace('-', '', $dataH[0]['tanggalinvoice']) . "/" . $unitFranco . "/" . $kodejurnalPenerima . "/" . $konterPenerima;

							##ht
							$dataRes['headercaco'] = array(
								'nojurnal' => $nojurnalPenerima,
								'kodejurnal' => $kodejurnalPenerima,
								'tanggal' => $dataH[0]['tanggalinvoice'],
								'tanggalentry' => date('Ymd'),
								'posting' => '0',
								'totaldebet' => '0',
								'totalkredit' => '0',
								'amountkoreksi' => '0',
								'noreferensi' => $dataH[0]['noinvoice'],
								'autojurnal' => '1',
								'matauang' => 'IDR',
								'kurs' => '1',
								'revisi' => '0'
							);

							$dataRes['detailcaco'][] = array(
								'nojurnal' => $nojurnalPenerima,
								'tanggal' => $dataH[0]['tanggalinvoice'],
								'nourut' => $noUrutcaco,
								'noakun' => $noakungrir,
								'keterangan' => $ketGRIR,
								'jumlah' => ($nilaidpp),
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $unitFranco,
								'kodekegiatan' => '',
								'kodeasset' => $row['kodeasset'],
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $dataH[0]['kodesupplier'],
								'noreferensi' => $dataH[0]['noinvoice'],
								'noaruskas' => '',
								'kodevhc' => $row['kodevhc'],
								'nodok' => $dataH[0]['nopo'],
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => ''
							);
							$noUrutcaco++;
							$dataRes['detailcaco'][] = array(
								'nojurnal' => $nojurnalPenerima,
								'tanggal' => $dataH[0]['tanggalinvoice'],
								'nourut' => $noUrutcaco,
								'noakun' => $noakuncaco,
								'keterangan' => $ketGRIR,
								'jumlah' => ($nilaidpp) * (-1),
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $unitFranco,
								'kodekegiatan' => '',
								'kodeasset' => $row['kodeasset'],
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $dataH[0]['kodesupplier'],
								'noreferensi' => $dataH[0]['noinvoice'],
								'noaruskas' => '',
								'kodevhc' => $row['kodevhc'],
								'nodok' => $dataH[0]['nopo'],
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => ''
							);
							$noUrutcaco++;

							#= update counter jurnal
							$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konterPenerima . "' where kodeunit='" . $unitFranco . "' and kodekelompok='" . $kodejurnalPenerima . "' and periode='" . substr($dataH[0]['tanggalinvoice'], 0, 7) . "' ";
							$owlPDO->exec($str);
						} else {
							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $dataH[0]['tanggalinvoice'],
								'nourut' => $noUrut,
								'noakun' => $noakungrir,
								'keterangan' => $ketGRIR,
								'jumlah' => ($nilaidpp),
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $dataH[0]['unit'],
								'kodekegiatan' => '',
								'kodeasset' => $row['kodeasset'],
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $dataH[0]['kodesupplier'],
								'noreferensi' => $dataH[0]['noinvoice'],
								'noaruskas' => '',
								'kodevhc' => $row['kodevhc'],
								'nodok' => $dataH[0]['nopo'],
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => ''
							);
							$noUrut++;
							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $dataH[0]['tanggalinvoice'],
								'nourut' => $noUrut,
								'noakun' => $akunKredit,
								'keterangan' => $ketGRIR,
								'jumlah' => ($nilaidpp) * (-1),
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $dataH[0]['unit'],
								'kodekegiatan' => '',
								'kodeasset' => $row['kodeasset'],
								'kodebarang' => '',
								'nik' => '',
								'kodecustomer' => '',
								'kodesupplier' => $dataH[0]['kodesupplier'],
								'noreferensi' => $dataH[0]['noinvoice'],
								'noaruskas' => '',
								'kodevhc' => $row['kodevhc'],
								'nodok' => $dataH[0]['nopo'],
								'kodeblok' => '',
								'revisi' => '0',
								'kodesegment' => ''
							);
							$noUrut++;
						}
					} else {
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $dataH[0]['tanggalinvoice'],
							'nourut' => $noUrut,
							'noakun' => $noakungrir,
							'keterangan' => $ketGRIR,
							'jumlah' => ($nilaidpp),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $dataH[0]['unit'],
							'kodekegiatan' => '',
							'kodeasset' => $row['kodeasset'],
							'kodebarang' => '',
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $dataH[0]['kodesupplier'],
							'noreferensi' => $dataH[0]['noinvoice'],
							'noaruskas' => '',
							'kodevhc' => $row['kodevhc'],
							'nodok' => $dataH[0]['nopo'],
							'kodeblok' => '',
							'revisi' => '0',
							'kodesegment' => ''
						);
						$noUrut++;
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $dataH[0]['tanggalinvoice'],
							'nourut' => $noUrut,
							'noakun' => $akunKredit,
							'keterangan' => $ketGRIR,
							'jumlah' => ($nilaidpp) * (-1),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $dataH[0]['unit'],
							'kodekegiatan' => '',
							'kodeasset' => $row['kodeasset'],
							'kodebarang' => '',
							'nik' => '',
							'kodecustomer' => '',
							'kodesupplier' => $dataH[0]['kodesupplier'],
							'noreferensi' => $dataH[0]['noinvoice'],
							'noaruskas' => '',
							'kodevhc' => $row['kodevhc'],
							'nodok' => $dataH[0]['nopo'],
							'kodeblok' => '',
							'revisi' => '0',
							'kodesegment' => ''
						);
						$noUrut++;
					}
				}
			}
			// kalo ada uang muka, balik uang muka atas hutang supp
			// if(($dataH[0]['tipeinvoice']=='p'||$dataH[0]['tipeinvoice']=='pon')and($totdtum<0)){ // um tersimpan minus
			if ($totdtum < 0) { // um tersimpan minus
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $dataH[0]['tanggalinvoice'],
					'nourut' => $noUrut,
					'noakun' => $akunKredit,
					'keterangan' => $ketbalikum,
					'jumlah' => ($totdtum * (-1)),
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $dataH[0]['unit'],
					'kodekegiatan' => '',
					'kodeasset' => $row['kodeasset'],
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => '',
					'kodesupplier' => $dataH[0]['kodesupplier'],
					'noreferensi' => $dataH[0]['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => $row['kodevhc'],
					'nodok' => $dataH[0]['nopo'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $dataH[0]['tanggalinvoice'],
					'nourut' => $noUrut,
					'noakun' => $akunum,
					'keterangan' => $ketbalikum,
					'jumlah' => ($totdtum),
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $dataH[0]['unit'],
					'kodekegiatan' => '',
					'kodeasset' => $row['kodeasset'],
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => '',
					'kodesupplier' => $dataH[0]['kodesupplier'],
					'noreferensi' => $dataH[0]['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => $row['kodevhc'],
					'nodok' => $dataH[0]['nopo'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
			}
			// end GRIR 2021    		

			if ($countdtppn > 0) {


				#====cek periode
				$tgl = str_replace("-", "", $dataH[0]['tanggalinvoice']);
				$sPeriode = "select * from " . $dbname . ".setup_periodeakuntansi 
				           where kodeorg='" . $dataH[0]['unit'] . "' and tutupbuku=0 order by periode desc";
				$rPeriode = fetchdata($sPeriode);
				$tglakutansi = str_replace("-", "", $rPeriode[0]['tanggalmulai']);
				if ($tglakutansi > $tgl) {
					// exit('Error:Date beyond active period');
					exit("Warning:Tanggal invoice diluar periode aktif, Periode invoice : " . substr($dataH[0]['tanggalinvoice'], 0, 7) . " ; Periode Aktif : " . $rPeriode[0]['periode'] . " ");
				}

				#=== Cek if data not exist ===
				$error1 = "";
				if (count($dataH) == 0) {
					$error1 .= $_SESSION['lang']['errheadernotexist'] . "\n";
				}
				if (count($dataD) == 0) {
					$error1 .= $_SESSION['lang']['errdetailnotexist'] . "\n";
				}
				if ($error1 != '') {
					echo "Data Error :\n" . $error1;
					exit;
				}
				##dt

				foreach ($dataD as $row) {


					$nmAkun = makeOption($dbname, "keu_5akun", "noakun,namaakun", "noakun='" . $row['noakun'] . "'");
					$namaAkun = $nmAkun[$row['noakun']];
					// if(substr($row['noakun'],0,4)=='2120'){
					// 	$ketDef='Pengakuan '.$namaAkun.' a/n : '.$optSupp[$dataH[0]['kodesupplier']].',noinvoice supplier :'.$dataH[0]['noinvoicesupplier'].',No Faktur Pajak :'.$dataH[0]['nofp'].', Jenis Tagihan :'.$rKode[0]['namajenis'];
					// 	$totRpppn=$totRpppn+$row['nilai'];
					// }
					// if($row['noakun']=='1170111'){//noakun ppn
					if ($row['noakun'] == '1160101') { //noakun ppn
						// $ketDef='Biaya '.$namaAkun.' a/n : '.$optSupp[$dataH[0]['kodesupplier']].',noinvoice supplier :'.$dataH[0]['noinvoicesupplier'].',No Faktur Pajak :'.$dataH[0]['nofp'].', Jenis Tagihan :'.$rKode[0]['namajenis'];
						// $ketDefGRIR='GRIR '.$namaAkun.' a/n : '.$optSupp[$dataH[0]['kodesupplier']].',noinvoice supplier :'.$dataH[0]['noinvoicesupplier'].',No Faktur Pajak :'.$dataH[0]['nofp'].', Jenis Tagihan :'.$rKode[0]['namajenis'];
						$ketDef = $optSupp[$dataH[0]['kodesupplier']] . ', PO/SO/Dok: ' . $dataH[0]['nopo'] . ', FP: ' . $dataH[0]['nofp'] . ', Trx: ' . $trxnya;
						$ketDefGRIR = 'GRIR a/n: ' . $optSupp[$dataH[0]['kodesupplier']] . ', PO/SO/Dok: ' . $dataH[0]['nopo'] . ', FP: ' . $dataH[0]['nofp'] . ', Trx: ' . $trxnya;
						$totRpppn += ($row['nilai'] * $dataH[0]['kurs']);
					}
					if ($noUrut != 1) {
						$namaAkunData .= "," . $namaAkun;
					} else {
						$namaAkunData = $namaAkun;
					}

					// exit("Error:".$dataH[0]['tipeinvoice']._.$dataH[0]['nopo']);

					// if($row['noakun']!='1170111'){//noakun ppn
					if ($row['noakun'] != '1160101') { //noakun ppn
						continue;
					}

					// exit("Error:".$row['nilai']._.$row['noakun']);
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $dataH[0]['tanggalinvoice'],
						'nourut' => $noUrut,
						'noakun' => $row['noakun'],
						'keterangan' => $ketDef,
						'jumlah' => ($row['nilai'] * $dataH[0]['kurs']),
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $dataH[0]['unit'],
						'kodekegiatan' => '',
						'kodeasset' => $row['kodeasset'],
						'kodebarang' => '',
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $dataH[0]['kodesupplier'],
						'noreferensi' => $dataH[0]['noinvoice'],
						'noaruskas' => '',
						'kodevhc' => $row['kodevhc'],
						'nodok' => $dataH[0]['nopo'],
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => ''
					);
					$noUrut++;
					// // GRIR 2021
					// // bikin GRIR atas PPN khusus p dan pon
					// if(($dataH[0]['tipeinvoice']=='p'||$dataH[0]['tipeinvoice']=='pon')and($nilaidpp>0)){
					// $dataRes['detail'][] = array(
					// 	'nojurnal'=>$nojurnal,
					// 	'tanggal'=>$dataH[0]['tanggalinvoice'],
					// 	'nourut'=>$noUrut,
					// 	'noakun'=>$noakungrir,
					// 	'keterangan'=>$ketDefGRIR,
					// 	'jumlah'=>($row['nilai']*$dataH[0]['kurs']*(-1)),
					// 	'matauang'=>'IDR',
					// 	'kurs'=>'1',
					// 	'kodeorg'=>$dataH[0]['unit'],
					// 	'kodekegiatan'=>'',
					// 	'kodeasset'=>$row['kodeasset'],
					// 	'kodebarang'=>'',
					// 	'nik'=>'',
					// 	'kodecustomer'=>'',
					// 	'kodesupplier'=>$dataH[0]['kodesupplier'],
					// 	'noreferensi'=>$dataH[0]['noinvoice'],
					// 	'noaruskas'=>'',
					// 	'kodevhc'=>$row['kodevhc'],
					// 	'nodok'=>$dataH[0]['nopo'],
					// 	'kodeblok'=>'',
					// 	'revisi'=>'0',
					// 	'kodesegment' => ''
					// 	);
					// $noUrut++;
					// $dataRes['detail'][] = array(
					// 	'nojurnal'=>$nojurnal,
					// 	'tanggal'=>$dataH[0]['tanggalinvoice'],
					// 	'nourut'=>$noUrut,
					// 	'noakun'=>$noakungrir,
					// 	'keterangan'=>$ketDefGRIR,
					// 	'jumlah'=>($row['nilai']*$dataH[0]['kurs']),
					// 	'matauang'=>'IDR',
					// 	'kurs'=>'1',
					// 	'kodeorg'=>$dataH[0]['unit'],
					// 	'kodekegiatan'=>'',
					// 	'kodeasset'=>$row['kodeasset'],
					// 	'kodebarang'=>'',
					// 	'nik'=>'',
					// 	'kodecustomer'=>'',
					// 	'kodesupplier'=>$dataH[0]['kodesupplier'],
					// 	'noreferensi'=>$dataH[0]['noinvoice'],
					// 	'noaruskas'=>'',
					// 	'kodevhc'=>$row['kodevhc'],
					// 	'nodok'=>$dataH[0]['nopo'],
					// 	'kodeblok'=>'',
					// 	'revisi'=>'0',
					// 	'kodesegment' => ''
					// 	);
					// $noUrut++;
					// }
					// // end GRIR 2021
				}
			}


			if (count($rppPPh) != 0) {
				foreach ($rppPPh as $row => $nildt) {

					// exit("Error:".$nildt);
					$nmAkun = makeOption($dbname, "keu_5akun", "noakun,namaakun", "noakun='" . $row . "'");
					$namaAkun = $nmAkun[$row];
					// 'keterangan'=>'Pencatatan Kredit '.$namaAkun.'  a/n '.$optSupp[$dataH[0]['kodesupplier']].',noinvoice supplier :'.$dataH[0]['noinvoicesupplier'].',No Faktur Pajak :'.$dataH[0]['nofp'].', Jenis Tagihan :'.$rKode[0]['namajenis'],
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $dataH[0]['tanggalinvoice'],
						'nourut' => $noUrut,
						'noakun' => $row,
						'keterangan' => $optSupp[$dataH[0]['kodesupplier']] . ', PO/SO/Dok: ' . $dataH[0]['nopo'] . ', FP: ' . $dataH[0]['nofp'] . ', Trx: ' . $trxnya,
						'jumlah' => $nildt,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $dataH[0]['unit'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => '',
						'kodecustomer' => '',
						'kodesupplier' => $dataH[0]['kodesupplier'],
						'noreferensi' => $dataH[0]['noinvoice'],
						'noaruskas' => '',
						'kodevhc' => $row['kodevhc'],
						'nodok' => $dataH[0]['nopo'],
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => ''
					);
					$noUrut++;
				}
			}

			if ($dataH[0]['tipeinvoice'] == 'tck' || $dataH[0]['tipeinvoice'] == 'tpk') {
				$akunKredit = "2111201";
			}

			# =============================================================#
			# DEFAULT
			// if($countdtppn>0 || count($rppPPh)!=0){
			// 		// 'keterangan'=>'Pembebanan Biaya '.$namaAkunData.'  a/n '.$optSupp[$dataH[0]['kodesupplier']].',nodokumen :'.$dataH[0]['nopo'].',No Faktur Pajak :'.$dataH[0]['nofp'].', Jenis Tagihan :'.$rKode[0]['namajenis'],
			// 	$dataRes['detail'][] = array(
			// 		'nojurnal'=>$nojurnal,
			// 		'tanggal'=>$dataH[0]['tanggalinvoice'],
			// 		'nourut'=>$noUrut,
			// 		'noakun'=>$akunKredit,
			// 		'keterangan'=>$optSupp[$dataH[0]['kodesupplier']].', PO/SO/dok: '.$dataH[0]['nopo'].', FP: '.$dataH[0]['nofp'].', Trx: '.$trxnya,
			// 		'jumlah'=>($totRpppn+$totPPh)*(-1),
			// 		'matauang'=>'IDR',
			// 		'kurs'=>'1',
			// 		'kodeorg'=>$dataH[0]['unit'],
			// 		'kodekegiatan'=>'',
			// 		'kodeasset'=>'',
			// 		'kodebarang'=>'',
			// 		'nik'=>'',
			// 		'kodecustomer'=>'',
			// 		'kodesupplier'=>$dataH[0]['kodesupplier'],
			// 		'noreferensi'=>$dataH[0]['noinvoice'],
			// 		'noaruskas'=>'',
			// 		'kodevhc'=>$row['kodevhc'],
			// 		'nodok'=>$dataH[0]['nopo'],
			// 		'kodeblok'=>'',
			// 		'revisi'=>'0',
			// 		'kodesegment' => ''
			// 	);
			// 	$noUrut++;
			// }
			# END DEFAULT
			# =============================================================#
			if ($countdtppn > 0) {
				// 'keterangan'=>'Pembebanan Biaya '.$namaAkunData.'  a/n '.$optSupp[$dataH[0]['kodesupplier']].',nodokumen :'.$dataH[0]['nopo'].',No Faktur Pajak :'.$dataH[0]['nofp'].', Jenis Tagihan :'.$rKode[0]['namajenis'],
				# PPn
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $dataH[0]['tanggalinvoice'],
					'nourut' => $noUrut,
					'noakun' => $akunKredit,
					'keterangan' => $optSupp[$dataH[0]['kodesupplier']] . ', PO/SO/dok: ' . $dataH[0]['nopo'] . ', FP: ' . $dataH[0]['nofp'] . ', Trx: ' . $trxnya,
					'jumlah' => ($totRpppn) * (-1),
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $dataH[0]['unit'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => '',
					'kodesupplier' => $dataH[0]['kodesupplier'],
					'noreferensi' => $dataH[0]['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => $row['kodevhc'],
					'nodok' => $dataH[0]['nopo'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
			}

			if (count($rppPPh) != 0) {
				// $akunKreditpph = "2120801";

				# PPh
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $dataH[0]['tanggalinvoice'],
					'nourut' => $noUrut,
					'noakun' => $akunKredit,
					'keterangan' => $optSupp[$dataH[0]['kodesupplier']] . ', PO/SO/dok: ' . $dataH[0]['nopo'] . ', FP: ' . $dataH[0]['nofp'] . ', Trx: ' . $trxnya,
					'jumlah' => ($totPPh) * (-1),
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $dataH[0]['unit'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => '',
					'kodecustomer' => '',
					'kodesupplier' => $dataH[0]['kodesupplier'],
					'noreferensi' => $dataH[0]['noinvoice'],
					'noaruskas' => '',
					'kodevhc' => $row['kodevhc'],
					'nodok' => $dataH[0]['nopo'],
					'kodeblok' => '',
					'revisi' => '0',
					'kodesegment' => ''
				);
				$noUrut++;
			}
			break;
	}

	#=== Insert Data ===
	$errorDB = "";
	$errorDBRK = "";

	# Cek jika noinvoice sudah pernah ada dijurnal
	$queryjurnal = selectQuery($dbname, 'keu_jurnalht', "noreferensi", "noreferensi='" . $dataH[0]['noinvoice'] . "' and kodejurnal='" . $kodejurnal . "'");
	$isJurnal = fetchData($queryjurnal);
	if (count($isJurnal) > 0) {
		$queryToJ = updateQuery(
			$dbname,
			'keu_tagihanht',
			array('posting' => 1, "postingby" => $_SESSION['standard']['userid'], 'postingdate' => date('Y-m-d')),
			"noinvoice='" . $dataH[0]['noinvoice'] . "'"
		);
		try {
			$owlPDO->exec($queryToJ);
		} catch (PDOException $e) {
			$errorDB .= "Detail: " . $queryToJ . " " . $e->getMessage();
		}

		exit();
	}

	# Header
	if (!empty($dataRes['header'])) {
		$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
		try {
			$owlPDO->exec($queryH);
		} catch (PDOException $e) {
			$errorDB .= "Header :" . $e->getMessage();
		}

		if (!empty($dataRes['headercaco'])) {
			$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['headercaco']);
			try {
				$owlPDO->exec($queryH);
			} catch (PDOException $e) {
				$errorDB .= "Header Caco :" . $e->getMessage();
			}
		}

		#Headerrk
		if (!empty($dataRes['headerrk'])) {
			$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['headerrk']);
			try {
				$owlPDO->exec($queryH);
			} catch (PDOException $e) {
				$errorDBRK .= "Headerrk :" . $e->getMessage();
			}
		}

		# Detail
		if ($errorDB == '') {

			if (!empty($dataRes['detail'])) {
				foreach ($dataRes['detail'] as $key => $dataDet) {
					$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
					try {
						$owlPDO->exec($queryD);
					} catch (PDOException $e) {
						$errorDB .= "Detail: " . $key . " " . $e->getMessage();
					}
				}
			}

			if (!empty($dataRes['detailcaco'])) {
				foreach ($dataRes['detailcaco'] as $key => $dataDet) {
					$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
					try {
						$owlPDO->exec($queryD);
					} catch (PDOException $e) {
						$errorDB .= "Detail Caco: " . $key . " " . $e->getMessage();
					}
				}
			}

			# Detailrk
			if (!empty($dataRes['detailrk'])) {
				foreach ($dataRes['detailrk'] as $key => $dataDet) {
					$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataDet);
					try {
						$owlPDO->exec($queryD);
					} catch (PDOException $e) {
						$errorDBRK .= "Detailrk: " . $key . " " . $e->getMessage();
					}
				}
			}

			#=== Switch Jurnal to 1 ===
			# Cek if already posted
			$queryJ = selectQuery($dbname, 'keu_tagihanht', "posting,postingby", "noinvoice='" . $noinvoice . "'");
			$isJ = fetchData($queryJ);
			if ($isJ[0]['posting'] == 1) {
				$errorDB .= "Data changed by other user";
			} else {
				$queryToJ = updateQuery(
					$dbname,
					'keu_tagihanht',
					array('posting' => 1, "postingby" => $_SESSION['standard']['userid'], 'postingdate' => date('Y-m-d')),
					"noinvoice='" . $dataH[0]['noinvoice'] . "'"
				);
				try {
					$owlPDO->exec($queryToJ);
				} catch (PDOException $e) {
					$errorDB .= "Posting Flag Error" . $e->getMessage();
				}
				// Posting Success
				#=== Add Counter Jurnal ===

				$kounter = 1;
				if (count($arrPtCaco) == 1) {
					$kounter = 2;
				}
				if (empty($arrPtCaco)) {
					##jika uang muka tidak muncul R/K
					$queryJ = updateQuery(
						$dbname,
						'keu_5kelompokjurnal',
						array('nokounter' => $tmpKonter[0]['nokounter'] + $kounter),
						"kodeorg='" . $dataH[0]['kodeorg'] . "' and kodeunit='" . $dataH[0]['unit'] . "' and periode='" . substr($dataH[0]['tanggalinvoice'], 0, 7) . "' and kodekelompok='" . $kodejurnal . "'"
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
							"kodeorg='" . $dataH[0]['kodeorg'] . "' and kodeunit='" . $dataH[0]['unit'] . "' and periode='" . substr($dataH[0]['tanggalinvoice'], 0, 7) . "' and kodekelompok='" . $kodejurnal . "'"
						);
						try {
							$owlPDO->exec($queryJRB);
							echo "warning" . $errCounter;
							exit();
						} catch (PDOException $e) {
							$errorJRB .= "Rollback Parameter Jurnal Error :" . $e->getMessage();
						}
						echo "DB Error :\n" . $errorJRB;
						exit;
					}
				} else {
					##jika uang muka muncul R/K
					foreach ($arrPtCaco as $key) { #= mentok ini dlu indra $unittrans
						$queryJ = updateQuery(
							$dbname,
							'keu_5kelompokjurnal',
							array('nokounter' => $tmpKonter[0]['nokounter'] + $kounter),
							"kodeorg='" . $key . "' and kodeunit='" . $unittrans . "' and  periode='" . substr($dataH[0]['tanggalinvoice'], 0, 7) . "' and kodekelompok='" . $kodejurnal . "'"
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
								"kodeorg='" . $key . "' and kodeunit='" . $unittrans . "' and  periode='" . substr($dataH[0]['tanggalinvoice'], 0, 7) . "' and kodekelompok='" . $kodejurnal . "'"
							);
							try {
								$owlPDO->exec($queryJRB);
								echo "warning" . $errCounter;
								exit();
							} catch (PDOException $e) {
								$errorJRB .= "Rollback Parameter Jurnal Error :" . $e->getMessage();
							}
							echo "DB Error :\n" . $errorJRB;
							exit;
						}
					}
				}
			}
		} else {
			$where = "nojurnal='" . $nojurnal . "'";
			$queryRB = "delete from `" . $dbname . "`.`keu_jurnalht` where " . $where;
			try {
				$owlPDO->exec($queryRB);
			} catch (PDOException $e) {
				print "Error : Rollback 1  :" . $e->getMessage();
			}

			$wherePenerima = "nojurnal='" . $nojurnalPenerima . "'";
			$queryRB = "delete from `" . $dbname . "`.`keu_jurnalht` where " . $wherePenerima;
			try {
				$owlPDO->exec($queryRB);
			} catch (PDOException $e) {
				print "Error : Rollback 1  :" . $e->getMessage();
			}
			$queryRB2 = updateQuery(
				$dbname,
				'keu_tagihanht',
				array('posting' => 0, 'postingby' => 0),
				"noinvoice='" . $dataH[0]['noinvoice'] . "'"
			);
			try {
				$owlPDO->exec($queryRB2);
			} catch (PDOException $e) {
				print "Error : Rollback 2  :" . $e->getMessage();
			}
			echo "warning" . $errorDB;
			exit();
		}
	} else {
		#update status
		$queryJ = selectQuery($dbname, 'keu_tagihanht', "posting,postingby", "noinvoice='" . $noinvoice . "'");
		$isJ = fetchData($queryJ);
		if ($isJ[0]['posting'] == 1) {
			$errorDB .= "Data changed by other user";
		} else {
			$queryToJ = updateQuery(
				$dbname,
				'keu_tagihanht',
				array('posting' => 1, "postingby" => $_SESSION['standard']['userid'], 'postingdate' => date('Y-m-d')),
				"noinvoice='" . $dataH[0]['noinvoice'] . "'"
			);
			try {
				$owlPDO->exec($queryToJ);
			} catch (PDOException $e) {
				$errorDB .= "Posting Flag Error" . $e->getMessage();
			}
		}
	}

	$owlPDO->commit();
} catch (PDOException $e) {

	$owlPDO->rollback();
	echo "Warning: Gagal melakukan penyimpanan data \n" . addslashes($e->getMessage());
}
