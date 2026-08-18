<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method', '');
$param = $_POST;
$defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

$tab = '';

$tanggal = $param['periode'] . "-28";
$str = "select * from " . $dbname . ".organisasi where  kodeorganisasi = '" . $param['kodeorg'] . "'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$kodept = $bar['induk'];
}

$str = "select * from " . $dbname . ".sdm_5jabatan";
$res = fetchdata($str);
foreach ($res as $bar) {
	$namajabatan[$bar['kodejabatan']] = $bar['namajabatan'];
}
$kodeorg = $param['kodeorg'];
$str = "select tanggalmulai,tanggalsampai from " . $dbname . ".sdm_5periodegaji 
    where kodeorg='" . $kodeorg . "'
    and periode='" . $param['periode'] . "'";
$tgmulai = '';
$tgsampai = '';
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while ($bar = $res->fetch()) {
	$tgsampai = $bar->tanggalsampai;
	$tgmulai = $bar->tanggalmulai;
}
// exit("Error:$method");

switch ($method) {


	case 'delete':
		$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'];
		$str = "delete from " . $dbname . ".keu_jurnalht where nojurnal like '" . $nojurnal . "%' and tanggal='" . $tanggal . "' and noreferensi='ALK_GAJI_LBR'";
		// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;


	case 'save':

		try {

			$owlPDO->beginTransaction();

			#= load setup_kegiatan to populate $akunbkm
			$sqlquery = "select * from " . $dbname . ".setup_kegiatan";
			$reqquery = fetchData($sqlquery);
			foreach ($reqquery as $bar) {
				$akunbkm[$bar['kodekegiatan']] = $bar['noakun'];
			}

			#= gaji > 0
			#=============================================================
			#=============================================================

			$lastAkunSdm = $lastAkunBkm = $lastBlok = $lastKegBkm = "";

			#BKM RAWAT
			$rupiah = 0;
			$totalbkm = array();
			$sql1 = "select a.*,c.* from " . $dbname . ".kebun_prestasi_detail a 
				left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi 
				left join " . $dbname . ".kebun_kehadiran_detail c on a.notransaksi=c.notransaksi and a.nourut=c.nourut and c.nik=a.nikpemel
				where b.tanggal like '" . $param['periode'] . "%' and a.nikpemel='" . $param['karyawanid'] . "'";
			$req1 = fetchData($sql1);
			foreach ($req1 as $bar) {
				$rupiah = $bar['umr'] + $bar['insentif'];
				if ($rupiah > 0) {
					$totalbkm[$bar['kodekegiatan']][$bar['kodeorg']] += $rupiah;
					$gtbkm += $rupiah;
				}
				$lastAkunBkm = $akunbkm[$bar['kodekegiatan']];
				$lastKegBkm = $bar['kodekegiatan'];
				$lastBlok = $bar['kodeorg'];

			}


			#BKM PANEN
			$str1 = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='PNN02' limit 1";
			$res1 = fetchdata($str1);
			foreach ($res1 as $bar) {
				// $kegpanen=$bar['noakundebet'].'02'; # Di Palma 01
				$kegpanen = $bar['noakundebet'] . '01';
			}

			$rupiah = 0;
			$sql = "select a.* from " . $dbname . ".kebun_prestasi_detail a left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi where b.tanggal like '" . $param['periode'] . "%' and a.nik='" . $param['karyawanid'] . "'"; #exit("error".$sql);
			$req = fetchData($sql);
			#exit("error".$sql);
			foreach ($req as $bar) {
				$rupiah = $bar['upahkerja'] + $bar['upahpremi'] + $bar['upahpremilebihbasis'] + $bar['upahpremilebihbasis2'] + $bar['premibasis'] + $bar['premibasis2'] + $bar['premibrondol'];
				if ($rupiah > 0) {
					$totalbkm[$kegpanen][$bar['kodeorg']] += $bar['upahkerja'] + $bar['upahpremi'] + $bar['upahpremilebihbasis'] + $bar['upahpremilebihbasis2'] + $bar['premibasis'] + $bar['premibasis2'] + $bar['premibrondol'];
					$gtbkm += $bar['upahkerja'] + $bar['upahpremi'] + $bar['upahpremilebihbasis'] + $bar['upahpremilebihbasis2'] + $bar['premibasis'] + $bar['premibasis2'] + $bar['premibrondol'];
				}
				$lastAkunBkm = $akunbkm[$kegpanen];
				$lastKegBkm = $kegpanen;
				$lastBlok = $bar['kodeorg'];

			}

			$totalsdm = array();
			$sqlxxx = "select * from " . $dbname . ".sdm_absensidt where tanggal like '" . $param['periode'] . "%' and karyawanid='" . $param['karyawanid'] . "'";
			$req = fetchData($sqlxxx);
			foreach ($req as $bar) {
				if ($bar['noakun'] != '') {
					$totalNilai = $bar['umr'] + $bar['premi'] + $bar['insentif'] + $bar['insentiflibur'];
					if ($totalNilai == 0) {
						$totalNilai = getUpahKary($param['periode'], $param['karyawanid']);
					}

					$totalsdm[$bar['noakun']][$bar['tanggal']] += $totalNilai;
					$lastAkunSdm = $bar['noakun'];
					$gtsdm += $totalNilai;
				}
			}
			#=============================================================

			$kodejurnal1 = "";
			#= jurnal gaji sisa
			if ($param['gajisisa'] > 0) {
				#= ambil parameter jurnal
				$akundebet = '';
				$akunkredit = '';
				$kodejurnal = 'KBNB0';
				$dataRes = array();

				$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "' limit 1";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$akundebet = $bar['noakundebet'];
					$akunkredit = $bar['noakunkredit'];
				}

				#proses data
				#======================== Nomor Jurnal =============================
				# Get Journal Counter
				$queryJ = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodeorg='" . $kodept . "' and kodekelompok='" . $kodejurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
				);
				$tmpKonter = fetchData($queryJ);
				$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . $konter;
				// exit("Error:".$akundebet._.$akunkredit);

				#= update counter jurnal
				$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
					kodeorg='" . $kodept . "' and kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $param['periode'] . "' ";
				$owlPDO->exec($str);

				# Prep Header
				$dataRes['header'] = array(
					'nojurnal' => $nojurnal,
					'kodejurnal' => $kodejurnal,
					'tanggal' => $tanggal,
					'tanggalentry' => date('Ymd'),
					'posting' => 1,
					'totaldebet' => $param['gajisisa'],
					'totalkredit' => -1 * $param['gajisisa'],
					'amountkoreksi' => '0',
					'noreferensi' => 'ALK_GAJI_LBR',
					'autojurnal' => '1',
					'matauang' => 'IDR',
					'kurs' => '1',
					'revisi' => '0'
				);

				$kodejurnal1 = $kodejurnal;
				# Data Detail
				$noUrut = 1;
				$gtsdm = $varsdmsisa = 0;
				#jika karyawan pernah bekerja di umum maka masukkan biaya ke umum, tapi jika karyawan tidak pernah bekerja diumum maka alokasikan berdasarkan dia bekerja


				$sqlquery = "select * from " . $dbname . ".setup_kegiatan"; #exit("error".$sql);
				$reqquery = fetchData($sqlquery);
				foreach ($reqquery as $bar) {
					$akunbkm[$bar['kodekegiatan']] = $bar['noakun'];
				}

				$varsdmsisa = 0;
				$varsdmsisa = $param['gajisisa'] - $gtsdm;
				# + berarti masih ada sisa
				# - berarti di-sdm lebih banyak
				$porsibkm = $porsisdm = 0;
				if ($varsdmsisa > 0 and $gtbkm > 0) {
					$porsibkm = $varsdmsisa;
					$porsisdm = $gtsdm;
					$xxxx = "x";
				} else {
					$xxxx = "y";
					$porsibkm = 0;
					$porsisdm = $param['gajisisa'];
				}

				// if($param['karyawanid']=='0000007277'){
				// echo "<pre>";
				// print_r($totalbkm);
				// echo "<br>";
				// echo $param['gajisisa']." sisa<br>";
				// echo $varsdmsisa." varsdmsisa<br>";
				// echo $gtsdm." gtsdm<br>";
				// echo $gtbkm." gtbkm<br>";
				// echo $porsibkm." bkm<br>";
				// echo $porsisdm." sdm<br>";
				// echo $xxxx." xx<br>";

				// exit("error");
				// }

				$gtsisa = 0;
				#jatah kebun
				if ($porsibkm > 0 and count($totalbkm) > 0) {
					$valuesisa = $totalsisa = 0;
					foreach ($totalbkm as $kdkeg => $val1) {
						foreach ($val1 as $blok => $value) {
							$valuesisa = floor(fixnan($value / $gtbkm * $porsibkm));
							# Debet
							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $tanggal,
								'nourut' => $noUrut,
								'noakun' => $akunbkm[$kdkeg],
								'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
								'jumlah' => $valuesisa,
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $param['kodeorg'],
								'kodekegiatan' => $kdkeg,
								'kodeasset' => '',
								'kodebarang' => '',
								'nik' => $param['karyawanid'],
								'kodecustomer' => '',
								'kodesupplier' => '',
								'noreferensi' => 'ALK_GAJI_LBR',
								'noaruskas' => '',
								'kodevhc' => '',
								'nodok' => '',
								'kodeblok' => $blok,
								'revisi' => '0',
								'kodesegment' => $defSegment
							);
							$totalsisa += $valuesisa;
							$gtsisa += $valuesisa;
							$noUrut++;

							$akunkebun = $akunbkm[$kdkeg];
							$kegkebun = $kdkeg;
							#ada case kegiatan tanaman tapi bloknya terisi divisi
							$tempblok[substr($akunbkm[$kdkeg], 0, 3)] = $blok;
						}
					}
					if (($porsibkm - $totalsisa) > 0) {
						# Debet
						if (!empty($tempblok[substr($akunkebun, 0, 3)])) {
							$blok = $tempblok[substr($akunkebun, 0, 3)];
						} else {
							$blok = $param['subbagian'];
						}

						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $akunkebun,
							'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
							'jumlah' => fixnan($porsibkm - $totalsisa),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $param['kodeorg'],
							'kodekegiatan' => $kegkebun,
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => $param['karyawanid'],
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => 'ALK_GAJI_LBR',
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => '',
							'kodeblok' => $blok,
							'revisi' => '0',
							'kodesegment' => $defSegment
						);
						$gtsisa += fixnan($porsibkm - $totalsisa);
						$noUrut++;
					}
				}


				if ($porsisdm > 0 and count($totalsdm) > 0) {
					foreach ($totalsdm as $noakun => $val1) {
						foreach ($val1 as $tglsdm => $value) {

							$valuesisa = (int) floor(fixnan($value / $gtsdm * $porsisdm));

							# Debet

							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $tanggal,
								'nourut' => $noUrut,
								'noakun' => $noakun,
								'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'] . ', tanggal ' . $tglsdm,
								'jumlah' => $valuesisa,
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $param['kodeorg'],
								'kodekegiatan' => '',
								'kodeasset' => '',
								'kodebarang' => '',
								'nik' => $param['karyawanid'],
								'kodecustomer' => '',
								'kodesupplier' => '',
								'noreferensi' => 'ALK_GAJI_LBR',
								'noaruskas' => '',
								'kodevhc' => '',
								'nodok' => '',
								'kodeblok' => $param['subbagian'],
								'revisi' => '0',
								'kodesegment' => $defSegment
							);
							$totalsisa += $valuesisa;
							$gtsisa += $valuesisa;
							$noUrut++;

							$akunsdm = $noakun;
						}
					}
					if (($porsisdm - $totalsisa) > 0) {
						# Debet
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $akunsdm,
							'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
							'jumlah' => fixnan($porsisdm - $totalsisa),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $param['kodeorg'],
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => $param['karyawanid'],
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => 'ALK_GAJI_LBR',
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => '',
							'kodeblok' => $param['subbagian'],
							'revisi' => '0',
							'kodesegment' => $defSegment
						);
						$noUrut++;
						$gtsisa += fixnan($porsisdm - $totalsisa);
					}
				} else {
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akundebet,
						'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
						'jumlah' => $porsisdm,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $param['karyawanid'],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_GAJI_LBR',
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => $param['subbagian'],
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;
					$gtsisa += $porsisdm;
				}
				#JAGA JAGA JIKA ADA KURANG ALOKASI
				if (abs($param['gajisisa'] - $gtsisa) > 0) {
					$blok = $param['subbagian'];
					$kodekegkebun = "";
					if ($akunkebun != '') {
						$akunsisa = $akunkebun;
						if (!empty($tempblok[substr($akunkebun, 0, 3)])) {
							$blok = $tempblok[substr($akunkebun, 0, 3)];
						}
						$kodekegkebun = $kegkebun;
					} elseif ($akunsdm != '') {
						$akunsisa = $akunsdm;
					} else {
						$akunsisa = $akundebet;
					}

					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunsisa,
						'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
						'jumlah' => fixnan($param['gajisisa'] - $gtsisa),
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => $kodekegkebun,
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $param['karyawanid'],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_GAJI_LBR',
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => $blok,
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;
				}

				# Kredit
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggal,
					'nourut' => $noUrut,
					'noakun' => $akunkredit,
					'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
					'jumlah' => $param['gajisisa'] * -1,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $param['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => $param['karyawanid'],
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => 'ALK_GAJI_LBR',
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => '',
					'kodeblok' => $param['subbagian'],
					'revisi' => '0',
					'kodesegment' => $defSegment
				);
				$noUrut++;



				$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
				$owlPDO->exec($queryH);

				$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
				$owlPDO->exec($queryD);

				// if($param['karyawanid']=='0000007251'){					
				// //exit("Error:".$queryH._.$queryD);
				// }
			}

			if ($param['gajisisa'] < 0) {
				#= ambil parameter jurnal
				$akundebet = '';
				$akunkredit = '';
				$kodejurnal = 'KBNB0';
				$dataRes = array();

				$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "' limit 1";
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$akundebet = $bar['noakundebet'];
					$akunkredit = $bar['noakunkredit'];
				}

				#proses data
				#======================== Nomor Jurnal =============================
				# Get Journal Counter
				$queryJ = selectQuery(
					$dbname,
					'keu_5kelompokjurnal',
					'nokounter',
					"kodeorg='" . $kodept . "' and kodekelompok='" . $kodejurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
				);
				$tmpKonter = fetchData($queryJ);
				$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

				# Transform No Jurnal dari No Transaksi
				$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . $konter;
				// exit("Error:".$akundebet._.$akunkredit);

				#= update counter jurnal
				$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
					kodeorg='" . $kodept . "' and kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $param['periode'] . "' ";
				$owlPDO->exec($str);

				# Prep Header
				$dataRes['header'] = array(
					'nojurnal' => $nojurnal,
					'kodejurnal' => $kodejurnal,
					'tanggal' => $tanggal,
					'tanggalentry' => date('Ymd'),
					'posting' => 1,
					'totaldebet' => abs($param['gajisisa']),
					'totalkredit' => -1 * abs($param['gajisisa']),
					'amountkoreksi' => '0',
					'noreferensi' => 'ALK_GAJI_LBR',
					'autojurnal' => '1',
					'matauang' => 'IDR',
					'kurs' => '1',
					'revisi' => '0'
				);

				$kodejurnal1 = $kodejurnal;
				# Data Detail
				$noUrut = 1;
				$gtsdm = $varsdmsisa = 0;
				#jika karyawan pernah bekerja di umum maka masukkan biaya ke umum, tapi jika karyawan tidak pernah bekerja diumum maka alokasikan berdasarkan dia bekerja


				$sqlquery = "select * from " . $dbname . ".setup_kegiatan"; #exit("error".$sql);
				$reqquery = fetchData($sqlquery);
				foreach ($reqquery as $bar) {
					$akunbkm[$bar['kodekegiatan']] = $bar['noakun'];
				}


				#BKM RAWAT
				$rupiah = 0;
				$totalbkm = array();
				$sql1 = "select a.*,c.* from " . $dbname . ".kebun_prestasi_detail a 
				left join " . $dbname . ".kebun_aktifitas b on a.notransaksi=b.notransaksi 
				left join " . $dbname . ".kebun_kehadiran_detail c on a.notransaksi=c.notransaksi and a.nourut=c.nourut and c.nik=a.nikpemel
				where b.tanggal like '" . $param['periode'] . "%' and a.nikpemel='" . $param['karyawanid'] . "'"; #exit("error".$sql);
				$req1 = fetchData($sql1);
				foreach ($req1 as $bar) {
					$rupiah = $bar['umr'] + $bar['insentif'];
					if ($rupiah > 0) {
						$totalbkm[$bar['kodekegiatan']][$bar['kodeorg']] += $rupiah;
						$gtbkm += $rupiah;
					}
				}


				// #BKM PANEN
				// $str1="select noakundebet,noakunkredit from ".$dbname.".keu_5parameterjurnal where jurnalid='PNN02' limit 1";
				// $res1=fetchdata($str1);
				// foreach($res1 as $bar){
				// 	// $kegpanen=$bar['noakundebet'].'02'; # Di Palma 01
				// 	$kegpanen=$bar['noakundebet'].'01';
				// }

				// $rupiah=0;
				// $sql = "select a.* from ".$dbname.".kebun_prestasi_detail a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where b.tanggal like '".$param['periode']."%' and a.nik='".$param['karyawanid']."'"; #exit("error".$sql);
				// $req = fetchData($sql);
				// foreach($req as $bar){
				// 	$rupiah=$bar['upahkerja']+$bar['upahpremi']+$bar['upahpremilebihbasis']+$bar['upahpremilebihbasis2']+$bar['premibasis']+$bar['premibasis2']+$bar['premibrondol'];
				// 	if($rupiah>0){	
				// 		$totalbkm[$kegpanen][$bar['kodeorg']]+=$bar['upahkerja']+$bar['upahpremi']+$bar['upahpremilebihbasis']+$bar['upahpremilebihbasis2']+$bar['premibasis']+$bar['premibasis2']+$bar['premibrondol'];
				// 		$gtbkm+=$bar['upahkerja']+$bar['upahpremi']+$bar['upahpremilebihbasis']+$bar['upahpremilebihbasis2']+$bar['premibasis']+$bar['premibasis2']+$bar['premibrondol'];
				// 	}
				// }

				$totalsdm = array();
				$sql = "select * from " . $dbname . ".sdm_absensidt where tanggal like '" . $param['periode'] . "%' and karyawanid='" . $param['karyawanid'] . "'"; #exit("error".$sql);
				$req = fetchData($sql);
				foreach ($req as $bar) {
					if ($bar['noakun'] != '') {
						$totalNilai = $bar['umr'] + $bar['premi'] + $bar['insentif'] + $bar['insentiflibur'];
						if ($totalNilai == 0) {
							$totalNilai = getUpahKary($param['periode'], $param['karyawanid']);
						}

						$totalsdm[$bar['noakun']][$bar['tanggal']] += $totalNilai;
						$gtsdm += $totalNilai;
					}
				}
				$varsdmsisa = 0;
				$varsdmsisa = $param['gajisisa'] + $gtsdm;
				# + berarti masih ada sisa
				# - berarti di-sdm lebih banyak
				$porsibkm = $porsisdm = 0;
				if (abs($varsdmsisa) > 0 and $gtbkm > 0) {
					$porsibkm = $varsdmsisa;
					$porsisdm = $gtsdm;
					$xxxx = "x";
				} else {
					$xxxx = "y";
					$porsibkm = 0;
					$porsisdm = $param['gajisisa'];
				}

				// if($param['karyawanid']=='0000007277'){
				// echo "<pre>";
				// print_r($totalbkm);
				// echo "<br>";
				// echo $param['gajisisa']." sisa<br>";
				// echo $varsdmsisa." varsdmsisa<br>";
				// echo $gtsdm." gtsdm<br>";
				// echo $gtbkm." gtbkm<br>";
				// echo $porsibkm." bkm<br>";
				// echo $porsisdm." sdm<br>";
				// echo $xxxx." xx<br>";

				// exit("error");
				// }

				$gtsisa = 0;
				#jatah kebun
				if (abs($porsibkm) > 0 and count($totalbkm) > 0) {
					$valuesisa = $totalsisa = 0;
					foreach ($totalbkm as $kdkeg => $val1) {
						foreach ($val1 as $blok => $value) {
							$valuesisa = (int) floor(fixnan($value / $gtbkm * $porsibkm));

							# Debet
							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $tanggal,
								'nourut' => $noUrut,
								'noakun' => $akunbkm[$kdkeg],
								'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
								'jumlah' => $valuesisa,
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $param['kodeorg'],
								'kodekegiatan' => $kdkeg,
								'kodeasset' => '',
								'kodebarang' => '',
								'nik' => $param['karyawanid'],
								'kodecustomer' => '',
								'kodesupplier' => '',
								'noreferensi' => 'ALK_GAJI_LBR',
								'noaruskas' => '',
								'kodevhc' => '',
								'nodok' => '',
								'kodeblok' => $blok,
								'revisi' => '0',
								'kodesegment' => $defSegment
							);
							$totalsisa += $valuesisa;
							$gtsisa += $valuesisa;
							$noUrut++;

							$akunkebun = $akunbkm[$kdkeg];
							$kegkebun = $kdkeg;
							#ada case kegiatan tanaman tapi bloknya terisi divisi
							$tempblok[substr($akunbkm[$kdkeg], 0, 3)] = $blok;
						}
					}
					if (abs($porsibkm - $totalsisa) > 0) {
						# Debet
						if (!empty($tempblok[substr($akunkebun, 0, 3)])) {
							$blok = $tempblok[substr($akunkebun, 0, 3)];
						} else {
							$blok = $param['subbagian'];
						}

						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $akunkebun,
							'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
							'jumlah' => fixnan($porsibkm - $totalsisa),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $param['kodeorg'],
							'kodekegiatan' => $kegkebun,
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => $param['karyawanid'],
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => 'ALK_GAJI_LBR',
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => '',
							'kodeblok' => $blok,
							'revisi' => '0',
							'kodesegment' => $defSegment
						);
						$gtsisa += fixnan($porsibkm - $totalsisa);
						$noUrut++;
					}
				}

				if ($porsisdm > 0 and count($totalsdm) > 0) {
					foreach ($totalsdm as $noakun => $val1) {
						foreach ($val1 as $tglsdm => $value) {
							$valuesisa = (int) floor($value / $gtsdm * $porsisdm);

							# Debet

							$dataRes['detail'][] = array(
								'nojurnal' => $nojurnal,
								'tanggal' => $tanggal,
								'nourut' => $noUrut,
								'noakun' => $noakun,
								'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'] . ', tanggal ' . $tglsdm,
								'jumlah' => $valuesisa,
								'matauang' => 'IDR',
								'kurs' => '1',
								'kodeorg' => $param['kodeorg'],
								'kodekegiatan' => '',
								'kodeasset' => '',
								'kodebarang' => '',
								'nik' => $param['karyawanid'],
								'kodecustomer' => '',
								'kodesupplier' => '',
								'noreferensi' => 'ALK_GAJI_LBR',
								'noaruskas' => '',
								'kodevhc' => '',
								'nodok' => '',
								'kodeblok' => $param['subbagian'],
								'revisi' => '0',
								'kodesegment' => $defSegment
							);
							$totalsisa += $valuesisa;
							$gtsisa += $valuesisa;
							$noUrut++;

							$akunsdm = $noakun;
						}
					}
					if (($porsisdm - $totalsisa) > 0) {
						# Debet
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $akunsdm,
							'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
							'jumlah' => fixnan($porsisdm - $totalsisa),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $param['kodeorg'],
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => $param['karyawanid'],
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => 'ALK_GAJI_LBR',
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => '',
							'kodeblok' => $param['subbagian'],
							'revisi' => '0',
							'kodesegment' => $defSegment
						);
						$noUrut++;
						$gtsisa += fixnan($porsisdm - $totalsisa);
					}
				} else {
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akundebet,
						'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
						'jumlah' => $porsisdm,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $param['karyawanid'],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_GAJI_LBR',
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => $param['subbagian'],
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;
					$gtsisa += $porsisdm;
				}

				#JAGA JAGA JIKA ADA KURANG ALOKASI
				if (abs($param['gajisisa'] - $gtsisa) > 0) {
					$blok = $param['subbagian'];
					$kodekegkebun = "";
					if ($akunkebun != '') {
						$akunsisa = $akunkebun;
						if (!empty($tempblok[substr($akunkebun, 0, 3)])) {
							$blok = $tempblok[substr($akunkebun, 0, 3)];
						}
						$kodekegkebun = $kegkebun;
					} elseif ($akunsdm != '') {
						$akunsisa = $akunsdm;
					} else {
						$akunsisa = $akundebet;
					}

					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunsisa,
						'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
						'jumlah' => fixnan($param['gajisisa'] - $gtsisa),
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => $kodekegkebun,
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $param['karyawanid'],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_GAJI_LBR',
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => $blok,
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;
				}

				# Kredit
				$dataRes['detail'][] = array(
					'nojurnal' => $nojurnal,
					'tanggal' => $tanggal,
					'nourut' => $noUrut,
					'noakun' => $akunkredit,
					'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
					'jumlah' => $param['gajisisa'] * -1,
					'matauang' => 'IDR',
					'kurs' => '1',
					'kodeorg' => $param['kodeorg'],
					'kodekegiatan' => '',
					'kodeasset' => '',
					'kodebarang' => '',
					'nik' => $param['karyawanid'],
					'kodecustomer' => '',
					'kodesupplier' => '',
					'noreferensi' => 'ALK_GAJI_LBR',
					'noaruskas' => '',
					'kodevhc' => '',
					'nodok' => '',
					'kodeblok' => $param['subbagian'],
					'revisi' => '0',
					'kodesegment' => $defSegment
				);
				$noUrut++;



				$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
				$owlPDO->exec($queryH);

				$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
				$owlPDO->exec($queryD);

				// if($param['karyawanid']=='0000007251'){					
				// //exit("Error:".$queryH._.$queryD);
				// }
			}



			#= bpjs > 0			
			#= ambil blok dan pekerjaan BKM rawat
			#= M10 PNN10
			#= masuk sini jika bpjs>0
			if ($param['gajisisabpjs'] > 0) {
				$arrblokpanen = array();
				$arrblokrawat = array();
				$arrblok = [];
				$datakarx = 0;
				$str = "select * from " . $dbname . ".kebun_kehadiran_detail_vw where 
					karyawanid='" . $param['karyawanid'] . "' and unit='" . $param['kodeorg'] . "' and tanggal like '" . $param['periode'] . "%' ";
				// echo $str;exit("Error:A");
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$datakarx = 1;

					#= untuk proporsi data 
					$arrblok[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatan[$bar['kodekegiatan']] = $bar['kodekegiatan'];
					$lsdata[$bar['kodeorg']][$bar['kodekegiatan']] = $bar['kodekegiatan'];

					#= Upah + Premi
					$totupahpremi += ($bar['umr'] + $bar['insentif']);
					$upahpremi[$bar['kodeorg']] += ($bar['umr'] + $bar['insentif']);

					#= untuk array insert jurnal
					$arrblokrawat[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatanrawat[$bar['kodekegiatan']] = $bar['kodekegiatan'];
					$lsdatarawat[$bar['kodeorg']][$bar['kodekegiatan']] = $bar['kodekegiatan'];

					#= Upah + Premi
					$totupahpremirawat += ($bar['umr'] + $bar['insentif']);
					$upahpremirawat[$bar['kodeorg']][$bar['kodekegiatan']] += ($bar['umr'] + $bar['insentif']);
				}

				#= ambil blok dan pekerjaan BKM panen
				$str = "select * from " . $dbname . ".kebun_prestasi_detail_vw where 
					karyawanid='" . $param['karyawanid'] . "' and unit='" . $param['kodeorg'] . "' and tanggal like '" . $param['periode'] . "%' ";
				// echo $str;exit("Error:A");	
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$datakarx = 1;
					#= untuk proporsi data 
					$arrblok[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatan['611010201'] = '611010201';
					$lsdata[$bar['kodeorg']]['611010201'] = '611010201';

					#= Upah + Premi
					$totupahpremi += ($bar['upahkerja'] + $bar['upahpremilebihbasis'] + $bar['premibrondol']);
					$upahpremi[$bar['kodeorg']] += ($bar['upahkerja'] + $bar['upahpremilebihbasis'] + $bar['premibrondol']);

					#= untuk array insert jurnal
					$arrblokpanen[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatanpanen['611010201'] = '611010201';
					$lsdatapanen[$bar['kodeorg']]['611010201'] = '611010201';

					#= Upah + Premi Panen
					$totupahpremipanen += ($bar['upahkerja'] + $bar['upahpremilebihbasis'] + $bar['premibrondol']);
					$upahpremipanen[$bar['kodeorg']]['611010201'] += ($bar['upahkerja'] + $bar['upahpremilebihbasis'] + $bar['premibrondol']);
				}

				# Tambah Untuk Sipil
				$sql = "select * from " . $dbname . ".vhc_spl_kehadiran_vw where 
					nik='" . $param['karyawanid'] . "' and kodeorg='" . $param['kodeorg'] . "' and tanggal like '" . $param['periode'] . "%' ";
				$res = fetchdata($sql);
				foreach ($res as $bar) {
					$datakarx = 1;
					#= untuk proporsi data 
					$arrblok[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatan[$bar['kodekegiatan']] = $bar['kodekegiatan'];
					$lsdata[$bar['kodeorg']][$bar['kodekegiatan']] = $bar['kodekegiatan'];

					#= Upah + Premi
					$totupahpremi += ($bar['umr'] + $bar['premi']);
					$upahpremi[$bar['kodeorg']] += ($bar['umr'] + $bar['premi']);

					#= untuk array insert jurnal
					$arrbloksipil[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatansipil[$bar['kodekegiatan']] = $bar['kodekegiatan'];
					$lsdatasipil[$bar['kodeorg']][$bar['kodekegiatan']] = $bar['kodekegiatan'];

					#= Upah + Premi
					$totupahpremisipil += ($bar['umr'] + $bar['premi']);
					$upahpremisipil[$bar['kodeorg']][$bar['kodekegiatan']] += ($bar['umr'] + $bar['premi']);
				}
				if ($param['karyawanid'] == '0000008535') {
					echo '1 : ' . $datakarx . '<br>';
					//exit('Error');
				}
				if ($datakarx == 0) {

					$str = "select * from " . $dbname . ".kebun_proporsitahuntanam where 
					karyawanid='" . $param['karyawanid'] . "' and kodeorg like '" . $param['kodeorg'] . "%' and tanggal like '" . $param['periode'] . "%' ";
					// echo $str;exit("Error:A");	
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$datakarx = 1;
						#= untuk proporsi data 
						$arrblok[$bar['blokkecil']] = $bar['blokkecil'];
						$arrkegiatan['611010201'] = '611010201';
						$lsdata[$bar['blokkecil']]['611010201'] = '611010201';

						#= Upah + Premi
						$totupahpremi += ($bar['upahkerja'] + $bar['jjg'] + $bar['brondolan']);
						$upahpremi[$bar['blokkecil']] += ($bar['jjg'] + $bar['brondolan']);

						#= untuk array insert jurnal
						$arrblokpanen[$bar['blokkecil']] = $bar['blokkecil'];
						$arrkegiatanpanen['611010201'] = '611010201';
						$lsdatapanen[$bar['blokkecil']]['611010201'] = '611010201';

						#= Upah + Premi Panen
						$totupahpremipanen += ($bar['jjg'] + $bar['brondolan']);
						$upahpremipanen[$bar['blokkecil']]['611010201'] += ($bar['jjg'] + $bar['brondolan']);
					}

					if ($param['karyawanid'] == '0000008535') {
						echo '1 : ' . $totupahpremi . '<br>';
						//exit('Error');
					}
				}
				// echo "<pre>";
				// print_r($arrkegiatanrawat);

				// echo "<pre>";
				// print_r($totupahpremi);
				// exit('warning');

				// echo "==========================="."<br/>";

				// echo "<pre>";
				// print_r($arrkegiatanpanen);

				// echo "==========================="."<br/>";
				// echo "<pre>";
				// print_r($arrblok);

				// echo "<pre>";
				// print_r($arrkegiatansipil);

				// echo "==========================="."<br/>";

				// exit('warning');

				#= porsi total
				$porsitotal = $porsirawat = $porsipanen = 0;

				foreach ($arrblok as $dtblok) {
					foreach ($arrkegiatan as $dtkegiatan) {
						if ($lsdata[$dtblok][$dtkegiatan]) {
							$porsitotal++;
						}
					}
				}

				#= porsi rawat
				foreach ($arrblokrawat as $dtblok) {
					foreach ($arrkegiatanrawat as $dtkegiatan) {
						if ($lsdatarawat[$dtblok][$dtkegiatan] != '') {
							$porsirawat++;

							# Nilai Panen (UP Panen / Total UP Panen)
							$upporsirawat[$dtblok][$dtkegiatan] = fixnan(($upahpremirawat[$dtblok][$dtkegiatan] / $totupahpremi) * $param['gajisisabpjs']);
						}
					}
				}

				#= porsi panen
				foreach ($arrblokpanen as $dtblok) {
					foreach ($arrkegiatanpanen as $dtkegiatan) {
						if ($lsdatapanen[$dtblok][$dtkegiatan] != '') {
							$porsipanen++;

							# Nilai Panen (UP Panen / Total UP Panen)
							$upporsipanen[$dtblok][$dtkegiatan] = fixnan(($upahpremipanen[$dtblok][$dtkegiatan] / $totupahpremi) * $param['gajisisabpjs']);
						}
					}
				}

				#= porsi sipil
				foreach ($arrbloksipil as $dtblok) {
					foreach ($arrkegiatansipil as $dtkegiatan) {
						if ($lsdatasipil[$dtblok][$dtkegiatan] != '') {
							$porsisipil++;

							# Nilai Panen (UP Panen / Total UP Panen)
							$upporsisipil[$dtblok][$dtkegiatan] = fixnan(($upahpremisipil[$dtblok][$dtkegiatan] / $totupahpremi) * $param['gajisisabpjs']);
						}
					}
				}

				# Sisa Pembulatan New Perhitungan
				# Proporsi berdasarkan Gaji dan Premi sesuai besaran Gaji dan Premi di Blok itu

				@$nilairupiah = fixnan($param['gajisisabpjs'] / $totupahpremi);
				@$totalnilairawat = fixnan($nilairupiah * $totupahpremirawat); #= untuk jurnal ht
				@$totalnilaipanen = fixnan($nilairupiah * $totupahpremipanen); #= untuk jurnal ht
				@$totalnilaisipil = fixnan($nilairupiah * $totupahpremisipil); #= untuk jurnal ht

				// echo "<pre>";
				// echo "1 :::<br>";
				// echo $param['gajisisabpjs'] . " / " . $totupahpremi . " = " . $nilairupiah . "<br/>";
				// echo $nilairupiah . " * " . $totupahpremirawat . " = " . $totalnilairawat . "<br/>";
				// echo $nilairupiah . " * " . $totupahpremipanen . " = " . $totalnilaipanen . "<br/>";
				// echo $nilairupiah . " * " . $totupahpremisipil . " = " . $totalnilaisipil . "<br/>";
				// exit("error");


				#=========================================================#
				# COMMENT YANG LAMA
				#=========================================================#
				// @$nilairupiah=floor($param['gajisisabpjs']/$porsitotal);
				// @$totalnilairawat=$nilairupiah*$porsirawat; #= untuk jurnal ht
				// @$totalnilaipanen=$nilairupiah*$porsipanen; #= untuk jurnal ht

				$zw = $param['gajisisabpjs'] - ($totalnilaipanen + $totalnilairawat + $totalnilaisipil);
				$selisihpembulatan = (abs($zw) <= 100 ? $zw : 0);
				$pembulatanrawat = $pembulatanpnn = $pembulatansipil = 0;
				// #= jika ada rawat, dimasukan ke rawat
				if ($porsisipil > 0) {
					$pembulatansipil = $selisihpembulatan;
				} else if ($porsirawat > 0) {
					$pembulatanrawat = $selisihpembulatan;
				} else if ($porsipanen > 0) {
					$pembulatanpnn = $selisihpembulatan;
				}

				// echo "Selisih Pembulatan : ".$selisihpembulatan."<br>";
				// echo "Pembulatan Rawat : ".$pembulatanrawat."<br>";
				// echo "Pembulatan Panen : ".$pembulatanpnn."<br>";
				// echo "Pembulatan Sipil : ".$pembulatansipil."<br>";
				// exit("error");
				#=========================================================#
				# END COMMENT YANG LAMA
				#=========================================================#


				// exit("Error:".$nilairupiah._.$totalnilairawat._.$totalnilaipanen._.$param['gajisisabpjs']._.$selisihpembulatan._.$pembulatanrawat._.$pembulatanpnn);


				#======================== proses data jurnal =============================
				// exit("Error:".$pembulatanrawat._.$no._.$porsirawat._.$selisihpembulatan._.$pembulatanpnn);

				$ftkrwt = false;
				#= jurnal Rawat
				if ($porsirawat > 0) {
					$no = $tnilairupiah = 0;
					$akundebet = '';
					$akunkredit = '';
					$kodejurnal = 'M10';
					$dataRes = array();

					$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "' limit 1";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$akundebet = $bar['noakundebet'];
						$akunkredit = $bar['noakunkredit'];
					}

					$queryJ = selectQuery(
						$dbname,
						'keu_5kelompokjurnal',
						'nokounter',
						"kodeorg='" . $kodept . "' and kodekelompok='" . $kodejurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
					);
					$tmpKonter = fetchData($queryJ);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . $konter;
					// exit("Error:".$akundebet._.$akunkredit);

					#= update counter jurnal
					$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
						kodeorg='" . $kodept . "' and kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $param['periode'] . "' ";
					$owlPDO->exec($str);

					# Prep Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodejurnal,
						'tanggal' => $tanggal,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => ($totalnilairawat + $pembulatanrawat),
						'totalkredit' => -1 * ($totalnilairawat + $pembulatanrawat),
						// 'totaldebet'   => ($totalnilairawat),
						// 'totalkredit'  => -1 * ($totalnilairawat),
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_GAJI_LBR',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);

					# Data Detail
					$noUrut = 1;

					# Kredit
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunkredit,
						'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS Biaya Rawat ' . $param['periode'],
						'jumlah' => ($totalnilairawat + $pembulatanrawat) * -1,
						// 'jumlah'      => ($totalnilairawat) * -1,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $param['karyawanid'],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_GAJI_LBR',
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;

					# Debet
					foreach ($arrblokrawat as $dtblok) {
						foreach ($arrkegiatanrawat as $dtkegiatan) {
							if ($lsdatarawat[$dtblok][$dtkegiatan] != '') {
								$no++;
								if ($no != 1) {
									$pembulatanrawat = 0;
								}
								$dataRes['detail'][] = array(
									'nojurnal' => $nojurnal,
									'tanggal' => $tanggal,
									'nourut' => $noUrut,
									'noakun' => substr($dtkegiatan, 0, 7),
									'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS (TK) Biaya Rawat ' . $param['periode'],
									// 'jumlah'      =>($nilairupiah+$pembulatanrawat),
									// 'jumlah'      =>($upporsirawat[$dtblok]+$pembulatanrawat),
									'jumlah' => ($upporsirawat[$dtblok][$dtkegiatan] + $pembulatanrawat),
									'matauang' => 'IDR',
									'kurs' => '1',
									'kodeorg' => $param['kodeorg'],
									'kodekegiatan' => $dtkegiatan,
									'kodeasset' => $dtblok,
									'kodebarang' => '',
									'nik' => $param['karyawanid'],
									'kodecustomer' => '',
									'kodesupplier' => '',
									'noreferensi' => 'ALK_GAJI_LBR',
									'noaruskas' => '',
									'kodevhc' => '',
									'nodok' => '',
									'kodeblok' => $dtblok,
									'revisi' => '0',
									'kodesegment' => $defSegment
								);
								$noUrut++;
							}
						}
					}


					$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($queryH);

					$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
					$owlPDO->exec($queryD);

					$ftkrwt = true;
				}
				#= tutup jurnal rawat

				#= jurnal Panen
				$ftkpn = false;
				if ($porsipanen > 0) {
					$no = $tnilairupiah = 0;
					$akundebet = '';
					$akunkredit = '';
					$kodejurnal = 'PNN10';
					$dataRes = array();

					$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "' limit 1";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$akundebet = $bar['noakundebet'];
						$akunkredit = $bar['noakunkredit'];
					}

					$queryJ = selectQuery(
						$dbname,
						'keu_5kelompokjurnal',
						'nokounter',
						"kodeorg='" . $kodept . "' and kodekelompok='" . $kodejurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
					);
					$tmpKonter = fetchData($queryJ);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . $konter;
					// exit("Error:".$akundebet._.$akunkredit);

					#= update counter jurnal
					$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
						kodeorg='" . $kodept . "' and kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $param['periode'] . "' ";
					$owlPDO->exec($str);

					# Prep Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodejurnal,
						'tanggal' => $tanggal,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $totalnilaipanen + $pembulatanpnn,
						'totalkredit' => -1 * ($totalnilaipanen + $pembulatanpnn),
						// 'totaldebet' => $totalnilaipanen,
						// 'totalkredit' => -1 * ($totalnilaipanen),
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_GAJI_LBR',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);

					# Data Detail
					$noUrut = 1;

					# Kredit
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunkredit,
						'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS (TK) Biaya Panen ' . $param['periode'],
						'jumlah' => ($totalnilaipanen + $pembulatanpnn) * -1,
						// 'jumlah' => ($totalnilaipanen) * -1,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $param['karyawanid'],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_GAJI_LBR',
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;

					# Debet
					foreach ($arrblokpanen as $dtblok) {
						foreach ($arrkegiatanpanen as $dtkegiatan) {
							if ($lsdatapanen[$dtblok][$dtkegiatan] != '') {
								$no++;
								if ($no != 1) {
									$pembulatanpnn = 0;
								}
								$dataRes['detail'][] = array(
									'nojurnal' => $nojurnal,
									'tanggal' => $tanggal,
									'nourut' => $noUrut,
									'noakun' => substr($dtkegiatan, 0, 7),
									'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS (TK) Biaya Panen ' . $param['periode'],
									// 'jumlah'=>($nilairupiah+$pembulatanpnn),
									'jumlah' => ($upporsipanen[$dtblok][$dtkegiatan] + $pembulatanpnn),
									'matauang' => 'IDR',
									'kurs' => '1',
									'kodeorg' => $param['kodeorg'],
									'kodekegiatan' => $dtkegiatan,
									'kodeasset' => '',
									'kodebarang' => '',
									'nik' => $param['karyawanid'],
									'kodecustomer' => '',
									'kodesupplier' => '',
									'noreferensi' => 'ALK_GAJI_LBR',
									'noaruskas' => '',
									'kodevhc' => '',
									'nodok' => '',
									'kodeblok' => $dtblok,
									'revisi' => '0',
									'kodesegment' => $defSegment
								);
								$noUrut++;
							}
						}
					}

					$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($queryH);

					$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
					$owlPDO->exec($queryD);

					$ftkpn = true;
				} #= tutup jurnal panen

				$ftksip = false;
				#= jurnal sipil
				if ($porsisipil > 0) {
					$no = $tnilairupiah = 0;
					$akundebet = '';
					$akunkredit = '';
					$kodejurnal = 'M10';
					$dataRes = array();

					$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "' limit 1";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$akundebet = $bar['noakundebet'];
						$akunkredit = $bar['noakunkredit'];
					}

					$queryJ = selectQuery(
						$dbname,
						'keu_5kelompokjurnal',
						'nokounter',
						"kodeorg='" . $kodept . "' and kodekelompok='" . $kodejurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
					);
					$tmpKonter = fetchData($queryJ);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . $konter;
					// exit("Error:".$akundebet._.$akunkredit);

					#= update counter jurnal
					$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
						kodeorg='" . $kodept . "' and kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $param['periode'] . "' ";
					$owlPDO->exec($str);

					# Prep Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodejurnal,
						'tanggal' => $tanggal,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => ($totalnilaisipil + $pembulatansipil),
						'totalkredit' => -1 * ($totalnilaisipil + $pembulatansipil),
						// 'totaldebet'   => ($totalnilaisipil),
						// 'totalkredit'  => -1 * ($totalnilaisipil),
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_GAJI_LBR',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);

					# Data Detail
					$noUrut = 1;

					# Kredit
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunkredit,
						'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS (TK) Biaya sipil ' . $param['periode'],
						'jumlah' => ($totalnilaisipil + $pembulatansipil) * -1,
						// 'jumlah'      => ($totalnilaisipil) * -1,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $param['karyawanid'],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_GAJI_LBR',
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;

					# Debet
					foreach ($arrbloksipil as $dtblok) {
						foreach ($arrkegiatansipil as $dtkegiatan) {
							if ($lsdatasipil[$dtblok][$dtkegiatan] != '') {
								$no++;
								if ($no != 1) {
									$pembulatansipil = 0;
								}
								$dataRes['detail'][] = array(
									'nojurnal' => $nojurnal,
									'tanggal' => $tanggal,
									'nourut' => $noUrut,
									'noakun' => substr($dtkegiatan, 0, 7),
									'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS (TK) Biaya sipil ' . $param['periode'],
									// 'jumlah'      =>($nilairupiah+$pembulatansipil),
									// 'jumlah'      =>($upporsisipil[$dtblok]+$pembulatansipil),
									'jumlah' => ($upporsisipil[$dtblok][$dtkegiatan] + $pembulatansipil),
									'matauang' => 'IDR',
									'kurs' => '1',
									'kodeorg' => $param['kodeorg'],
									'kodekegiatan' => $dtkegiatan,
									'kodeasset' => $dtblok,
									'kodebarang' => '',
									'nik' => $param['karyawanid'],
									'kodecustomer' => '',
									'kodesupplier' => '',
									'noreferensi' => 'ALK_GAJI_LBR',
									'noaruskas' => '',
									'kodevhc' => '',
									'nodok' => '',
									'kodeblok' => $dtblok,
									'revisi' => '0',
									'kodesegment' => $defSegment
								);
								$noUrut++;
							}
						}
					}


					$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($queryH);

					$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
					$owlPDO->exec($queryD);
					$ftksip = true;
				}
				#= tutup jurnal sipil


				if (!$ftkrwt && !$ftkpn && !$ftksip) {
					#JAGA JAGA JIKA ADA KURANG ALOKASI
					if (abs($selisihpembulatan) > 0) {
						// $blok = $param['subbagian'];
						// $kodekegkebun = "";
						// if ($akunkebun != '') {
						// 	$akunsisa = $akunkebun;
						// 	if (!empty($tempblok[substr($akunkebun, 0, 3)])) {
						// 		$blok = $tempblok[substr($akunkebun, 0, 3)];
						// 	}
						// 	$kodekegkebun = $kegkebun;
						// } elseif ($akunsdm != '') {
						// 	$akunsisa = $akunsdm;
						// } else {
						// 	$akunsisa = $akundebet;
						// }
						$akundebet = '';
						$akunkredit = '';
						$kodejurnal1 = 'KBNB0';
						$dataRes = array();

						// if ($param['karyawanid'] == '0000000706') {
						// 	echo $lastAkunBkm." => lastAkunBkm<br>";
						// 	echo $lastAkunSdm." => lastAkunSdm<br>";
						// 	echo $kegsisa." => kegsisa<br>";
						// 	echo $bloksisa." => bloksisa<br>";
						// 	echo $akunsisa." => akunsisa<br>";



						// 	exit("error 222 ".$sqlxxx);
						// }

						$akunsisa = ($lastAkunSdm == '' ? $lastAkunBkm : $lastAkunSdm);
						$kegsisa = $lastKegBkm;
						$bloksisa = $lastBlok;

						if (getNamaOrg($param['subbagian'], "tipe") == "TRAKSI" && $akunsisa == "") {
							$kodejurnal1 = "VHCG0";
						}

						$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal1 . "' limit 1";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$akundebet = $bar['noakundebet'];
							$akunkredit = $bar['noakunkredit'];

							if (getNamaOrg($param['subbagian'], "tipe") == "TRAKSI" && $akunsisa == "") {
								$akunsisa = $bar['noakundebet'];
							}
						}


						$queryJ = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodeorg='" . $kodept . "' and kodekelompok='" . $kodejurnal1 . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
						);
						$tmpKonter = fetchData($queryJ);
						$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

						# Transform No Jurnal dari No Transaksi
						$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal1 . "/" . $konter;


						#= update counter jurnal
						$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
					kodeorg='" . $kodept . "' and kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal1 . "' and periode='" . $param['periode'] . "' ";
						$owlPDO->exec($str);

						# Prep Header
						$dataRes['header'] = array(
							'nojurnal' => $nojurnal,
							'kodejurnal' => $kodejurnal1,
							'tanggal' => $tanggal,
							'tanggalentry' => date('Ymd'),
							'posting' => 1,
							'totaldebet' => fixnan($selisihpembulatan),
							'totalkredit' => -1 * fixnan($selisihpembulatan),
							'amountkoreksi' => '0',
							'noreferensi' => 'ALK_GAJI_LBR',
							'autojurnal' => '1',
							'matauang' => 'IDR',
							'kurs' => '1',
							'revisi' => '0'
						);

						$noUrut = 1;
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $akunsisa,
							'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
							'jumlah' => fixnan($selisihpembulatan),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $param['kodeorg'],
							'kodekegiatan' => $kegsisa,
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => $param['karyawanid'],
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => 'ALK_GAJI_LBR',
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => '',
							'kodeblok' => $bloksisa,
							'revisi' => '0',
							'kodesegment' => $defSegment
						);
						$noUrut++;



						# Kredit
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $akunkredit,
							'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
							'jumlah' => fixnan($selisihpembulatan) * -1,
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $param['kodeorg'],
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => $param['karyawanid'],
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => 'ALK_GAJI_LBR',
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => '',
							'kodeblok' => $param['subbagian'],
							'revisi' => '0',
							'kodesegment' => $defSegment
						);
						$noUrut++;

						// if ($param['karyawanid'] == '0000000706') {
						// 	echo "<pre>";
						// 	print_r($dataRes['header']);
						// 	print_r($dataRes['detail']);

						// 	exit("error 222");
						// }

						$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);

						$owlPDO->exec($queryH);
						// exit ("ERROR");

						$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
						$owlPDO->exec($queryD);
					}


				} #= tutup jurnal bpjs
			}
			#= masuk sini jika bpjs>0
			if ($param['gajisisabpjskes'] > 0) {
				$arrblokpanen = array();
				$arrblokrawat = array();
				$arrblok = [];
				$datakarx = 0;
				$str = "select * from " . $dbname . ".kebun_kehadiran_detail_vw where 
					karyawanid='" . $param['karyawanid'] . "' and unit='" . $param['kodeorg'] . "' and tanggal like '" . $param['periode'] . "%' ";
				// echo $str;exit("Error:A");
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$datakarx = 1;
					#= untuk proporsi data 
					$arrblok[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatan[$bar['kodekegiatan']] = $bar['kodekegiatan'];
					$lsdata[$bar['kodeorg']][$bar['kodekegiatan']] = $bar['kodekegiatan'];

					#= Upah + Premi
					$totupahpremi += ($bar['umr'] + $bar['insentif']);
					$upahpremi[$bar['kodeorg']] += ($bar['umr'] + $bar['insentif']);

					#= untuk array insert jurnal
					$arrblokrawat[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatanrawat[$bar['kodekegiatan']] = $bar['kodekegiatan'];
					$lsdatarawat[$bar['kodeorg']][$bar['kodekegiatan']] = $bar['kodekegiatan'];

					#= Upah + Premi
					$totupahpremirawat += ($bar['umr'] + $bar['insentif']);
					$upahpremirawat[$bar['kodeorg']][$bar['kodekegiatan']] += ($bar['umr'] + $bar['insentif']);
				}

				#= ambil blok dan pekerjaan BKM panen
				$str = "select * from " . $dbname . ".kebun_prestasi_detail_vw where 
					karyawanid='" . $param['karyawanid'] . "' and unit='" . $param['kodeorg'] . "' and tanggal like '" . $param['periode'] . "%' ";
				// echo $str;exit("Error:A");	
				$res = fetchdata($str);
				foreach ($res as $bar) {
					$datakarx = 1;
					#= untuk proporsi data 
					$arrblok[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatan['611010201'] = '611010201';
					$lsdata[$bar['kodeorg']]['611010201'] = '611010201';

					#= Upah + Premi
					$totupahpremi += ($bar['upahkerja'] + $bar['upahpremilebihbasis'] + $bar['premibrondol']);
					$upahpremi[$bar['kodeorg']] += ($bar['upahkerja'] + $bar['upahpremilebihbasis'] + $bar['premibrondol']);

					#= untuk array insert jurnal
					$arrblokpanen[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatanpanen['611010201'] = '611010201';
					$lsdatapanen[$bar['kodeorg']]['611010201'] = '611010201';

					#= Upah + Premi Panen
					$totupahpremipanen += ($bar['upahkerja'] + $bar['upahpremilebihbasis'] + $bar['premibrondol']);
					$upahpremipanen[$bar['kodeorg']]['611010201'] += ($bar['upahkerja'] + $bar['upahpremilebihbasis'] + $bar['premibrondol']);
				}

				# Tambah Untuk Sipil
				$sql = "select * from " . $dbname . ".vhc_spl_kehadiran_vw where 
					nik='" . $param['karyawanid'] . "' and kodeorg='" . $param['kodeorg'] . "' and tanggal like '" . $param['periode'] . "%' ";
				$res = fetchdata($sql);
				foreach ($res as $bar) {
					$datakarx = 1;
					#= untuk proporsi data 
					$arrblok[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatan[$bar['kodekegiatan']] = $bar['kodekegiatan'];
					$lsdata[$bar['kodeorg']][$bar['kodekegiatan']] = $bar['kodekegiatan'];

					#= Upah + Premi
					$totupahpremi += ($bar['umr'] + $bar['premi']);
					$upahpremi[$bar['kodeorg']] += ($bar['umr'] + $bar['premi']);

					#= untuk array insert jurnal
					$arrbloksipil[$bar['kodeorg']] = $bar['kodeorg'];
					$arrkegiatansipil[$bar['kodekegiatan']] = $bar['kodekegiatan'];
					$lsdatasipil[$bar['kodeorg']][$bar['kodekegiatan']] = $bar['kodekegiatan'];

					#= Upah + Premi
					$totupahpremisipil += ($bar['umr'] + $bar['premi']);
					$upahpremisipil[$bar['kodeorg']][$bar['kodekegiatan']] += ($bar['umr'] + $bar['premi']);
				}
				if ($param['karyawanid'] == '0000008535') {
					echo '2 ' . $datakarx . '<br>';
					//exit('Error');
				}
				if ($datakarx == 0) {
					$str = "select * from " . $dbname . ".kebun_proporsitahuntanam where 
					karyawanid='" . $param['karyawanid'] . "' and kodeorg like '" . $param['kodeorg'] . "%' and tanggal like '" . $param['periode'] . "%' ";
					// echo $str;exit("Error:A");	
					$res = fetchdata($str);
					foreach ($res as $bar) {
						#= untuk proporsi data 
						$arrblok[$bar['blokkecil']] = $bar['blokkecil'];
						$arrkegiatan['611010201'] = '611010201';
						$lsdata[$bar['blokkecil']]['611010201'] = '611010201';

						#= Upah + Premi
						$totupahpremi += ($bar['upahkerja'] + $bar['jjg'] + $bar['brondolan']);
						$upahpremi[$bar['blokkecil']] += ($bar['jjg'] + $bar['brondolan']);

						#= untuk array insert jurnal
						$arrblokpanen[$bar['blokkecil']] = $bar['blokkecil'];
						$arrkegiatanpanen['611010201'] = '611010201';
						$lsdatapanen[$bar['blokkecil']]['611010201'] = '611010201';

						#= Upah + Premi Panen
						$totupahpremipanen += ($bar['upahkerja'] + $bar['jjg'] + $bar['brondolan']);
						$upahpremipanen[$bar['blokkecil']]['611010201'] += ($bar['jjg'] + $bar['brondolan']);
					}
				}
				// echo "<pre>";
				// print_r($arrkegiatanrawat);

				// echo "<pre>";
				// print_r($totupahpremi);
				// exit('warning');

				// echo "==========================="."<br/>";

				// echo "<pre>";
				// print_r($arrkegiatanpanen);

				// echo "==========================="."<br/>";
				// echo "<pre>";
				// print_r($arrblok);

				// echo "<pre>";
				// print_r($arrkegiatansipil);

				// echo "==========================="."<br/>";

				// exit('warning');

				#= porsi total
				$porsitotal = $porsirawat = $porsipanen = 0;

				foreach ($arrblok as $dtblok) {
					foreach ($arrkegiatan as $dtkegiatan) {
						if ($lsdata[$dtblok][$dtkegiatan]) {
							$porsitotal++;
						}
					}
				}

				#= porsi rawat
				foreach ($arrblokrawat as $dtblok) {
					foreach ($arrkegiatanrawat as $dtkegiatan) {
						if ($lsdatarawat[$dtblok][$dtkegiatan] != '') {
							$porsirawat++;

							# Nilai Panen (UP Panen / Total UP Panen)
							$upporsirawat[$dtblok][$dtkegiatan] = ($upahpremirawat[$dtblok][$dtkegiatan] / $totupahpremi) * $param['gajisisabpjskes'];
						}
					}
				}

				#= porsi panen
				foreach ($arrblokpanen as $dtblok) {
					foreach ($arrkegiatanpanen as $dtkegiatan) {
						if ($lsdatapanen[$dtblok][$dtkegiatan] != '') {
							$porsipanen++;

							# Nilai Panen (UP Panen / Total UP Panen)
							$upporsipanen[$dtblok][$dtkegiatan] = ($upahpremipanen[$dtblok][$dtkegiatan] / $totupahpremi) * $param['gajisisabpjskes'];
						}
					}
				}

				#= porsi sipil
				foreach ($arrbloksipil as $dtblok) {
					foreach ($arrkegiatansipil as $dtkegiatan) {
						if ($lsdatasipil[$dtblok][$dtkegiatan] != '') {
							$porsisipil++;

							# Nilai Panen (UP Panen / Total UP Panen)
							$upporsisipil[$dtblok][$dtkegiatan] = ($upahpremisipil[$dtblok][$dtkegiatan] / $totupahpremi) * $param['gajisisabpjskes'];
						}
					}
				}

				# Sisa Pembulatan New Perhitungan
				# Proporsi berdasarkan Gaji dan Premi sesuai besaran Gaji dan Premi di Blok itu
				@$nilairupiah = fixnan($param['gajisisabpjskes'] / $totupahpremi);
				@$totalnilairawat = fixnan($nilairupiah * $totupahpremirawat); #= untuk jurnal ht
				@$totalnilaipanen = fixnan($nilairupiah * $totupahpremipanen); #= untuk jurnal ht
				@$totalnilaisipil = fixnan($nilairupiah * $totupahpremisipil); #= untuk jurnal ht

				// echo "<pre>";
				// echo "2 :::<br>";
				// echo $param['gajisisabpjskes'] . " / " . $totupahpremi . " = " . $nilairupiah . "<br/>";
				// echo $nilairupiah . " * " . $totupahpremirawat . " = " . $totalnilairawat . "<br/>";
				// echo $nilairupiah . " * " . $totupahpremipanen . " = " . $totalnilaipanen . "<br/>";
				// echo $nilairupiah . " * " . $totupahpremisipil . " = " . $totalnilaisipil . "<br/>";



				#=========================================================#
				# COMMENT YANG LAMA
				#=========================================================#
				// @$nilairupiah=floor($param['gajisisabpjs']/$porsitotal);
				// @$totalnilairawat=$nilairupiah*$porsirawat; #= untuk jurnal ht
				// @$totalnilaipanen=$nilairupiah*$porsipanen; #= untuk jurnal ht

				$zwi = $param['gajisisabpjskes'] - ($totalnilaipanen + $totalnilairawat + $totalnilaisipil);
				$selisihpembulatan = (abs($zwi) <= 100 ? $zwi : 0);
				$pembulatanrawat = $pembulatanpnn = $pembulatansipil = 0;
				#= jika ada rawat, dimasukan ke rawat
				if ($porsisipil > 0) {
					$pembulatansipil = $selisihpembulatan;
				} else if ($porsirawat > 0) {
					$pembulatanrawat = $selisihpembulatan;
				} else if ($porsipanen > 0) {
					$pembulatanpnn = $selisihpembulatan;
				}

				// echo "Selisih Pembulatan : ".$selisihpembulatan."<br>";
				// echo "Pembulatan Rawat : ".$pembulatanrawat."<br>";
				// echo "Pembulatan Panen : ".$pembulatanpnn."<br>";
				// echo "Pembulatan Sipil : ".$pembulatansipil."<br>";

				// if($param['karyawanid']=='0000000708'){
				// exit("error");
				// }

				#=========================================================#
				# END COMMENT YANG LAMA
				#=========================================================#


				// exit("Error:".$nilairupiah._.$totalnilairawat._.$totalnilaipanen._.$param['gajisisabpjs']._.$selisihpembulatan._.$pembulatanrawat._.$pembulatanpnn);


				#======================== proses data jurnal =============================
				// exit("Error:".$pembulatanrawat._.$no._.$porsirawat._.$selisihpembulatan._.$pembulatanpnn);

				$fkesrwt = false;
				#= jurnal Rawat
				if ($porsirawat > 0) {
					$no = $tnilairupiah = 0;
					$akundebet = '';
					$akunkredit = '';
					$kodejurnal = 'M11';
					$dataRes = array();

					$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "' limit 1";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$akundebet = $bar['noakundebet'];
						$akunkredit = $bar['noakunkredit'];
					}

					$queryJ = selectQuery(
						$dbname,
						'keu_5kelompokjurnal',
						'nokounter',
						"kodeorg='" . $kodept . "' and kodekelompok='" . $kodejurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
					);
					$tmpKonter = fetchData($queryJ);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . $konter;
					// exit("Error:".$akundebet._.$akunkredit);

					#= update counter jurnal
					$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
						kodeorg='" . $kodept . "' and kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $param['periode'] . "' ";
					$owlPDO->exec($str);

					# Prep Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodejurnal,
						'tanggal' => $tanggal,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => ($totalnilairawat + $pembulatanrawat),
						'totalkredit' => -1 * ($totalnilairawat + $pembulatanrawat),
						// 'totaldebet'   => ($totalnilairawat),
						// 'totalkredit'  => -1 * ($totalnilairawat),
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_GAJI_LBR',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);

					# Data Detail
					$noUrut = 1;

					# Kredit
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunkredit,
						'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS (Kes) Biaya Rawat ' . $param['periode'],
						'jumlah' => ($totalnilairawat + $pembulatanrawat) * -1,
						// 'jumlah'      => ($totalnilairawat) * -1,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $param['karyawanid'],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_GAJI_LBR',
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;

					# Debet
					foreach ($arrblokrawat as $dtblok) {
						foreach ($arrkegiatanrawat as $dtkegiatan) {
							if ($lsdatarawat[$dtblok][$dtkegiatan] != '') {
								$no++;
								if ($no != 1) {
									$pembulatanrawat = 0;
								}
								$dataRes['detail'][] = array(
									'nojurnal' => $nojurnal,
									'tanggal' => $tanggal,
									'nourut' => $noUrut,
									'noakun' => substr($dtkegiatan, 0, 7),
									'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS (Kes) Biaya Rawat ' . $param['periode'],
									// 'jumlah'      =>($nilairupiah+$pembulatanrawat),
									// 'jumlah'      =>($upporsirawat[$dtblok]+$pembulatanrawat),
									'jumlah' => ($upporsirawat[$dtblok][$dtkegiatan] + $pembulatanrawat),
									'matauang' => 'IDR',
									'kurs' => '1',
									'kodeorg' => $param['kodeorg'],
									'kodekegiatan' => $dtkegiatan,
									'kodeasset' => $dtblok,
									'kodebarang' => '',
									'nik' => $param['karyawanid'],
									'kodecustomer' => '',
									'kodesupplier' => '',
									'noreferensi' => 'ALK_GAJI_LBR',
									'noaruskas' => '',
									'kodevhc' => '',
									'nodok' => '',
									'kodeblok' => $dtblok,
									'revisi' => '0',
									'kodesegment' => $defSegment
								);
								$noUrut++;
							}
						}
					}


					$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($queryH);

					$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
					$owlPDO->exec($queryD);

					$fkesrwt = true;
				}
				#= tutup jurnal rawat

				$fkespnn = false;
				#= jurnal Panen
				if ($porsipanen > 0) {
					$no = $tnilairupiah = 0;
					$akundebet = '';
					$akunkredit = '';
					$kodejurnal = 'PNN11';
					$dataRes = array();

					$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "' limit 1";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$akundebet = $bar['noakundebet'];
						$akunkredit = $bar['noakunkredit'];
					}

					$queryJ = selectQuery(
						$dbname,
						'keu_5kelompokjurnal',
						'nokounter',
						"kodeorg='" . $kodept . "' and kodekelompok='" . $kodejurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
					);
					$tmpKonter = fetchData($queryJ);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . $konter;
					// exit("Error:".$akundebet._.$akunkredit);

					#= update counter jurnal
					$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
						kodeorg='" . $kodept . "' and kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $param['periode'] . "' ";
					$owlPDO->exec($str);

					# Prep Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodejurnal,
						'tanggal' => $tanggal,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => $totalnilaipanen + $pembulatanpnn,
						'totalkredit' => -1 * ($totalnilaipanen + $pembulatanpnn),
						// 'totaldebet' => $totalnilaipanen,
						// 'totalkredit' => -1 * ($totalnilaipanen),
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_GAJI_LBR',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);

					# Data Detail
					$noUrut = 1;

					# Kredit
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunkredit,
						'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS (Kes) Biaya Panen ' . $param['periode'],
						'jumlah' => ($totalnilaipanen + $pembulatanpnn) * -1,
						// 'jumlah' => ($totalnilaipanen) * -1,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $param['karyawanid'],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_GAJI_LBR',
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;

					# Debet
					foreach ($arrblokpanen as $dtblok) {
						foreach ($arrkegiatanpanen as $dtkegiatan) {
							if ($lsdatapanen[$dtblok][$dtkegiatan] != '') {
								$no++;
								if ($no != 1) {
									$pembulatanpnn = 0;
								}
								$dataRes['detail'][] = array(
									'nojurnal' => $nojurnal,
									'tanggal' => $tanggal,
									'nourut' => $noUrut,
									'noakun' => substr($dtkegiatan, 0, 7),
									'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS (Kes) Biaya Panen ' . $param['periode'],
									// 'jumlah'=>($nilairupiah+$pembulatanpnn),
									'jumlah' => ($upporsipanen[$dtblok][$dtkegiatan] + $pembulatanpnn),
									'matauang' => 'IDR',
									'kurs' => '1',
									'kodeorg' => $param['kodeorg'],
									'kodekegiatan' => $dtkegiatan,
									'kodeasset' => '',
									'kodebarang' => '',
									'nik' => $param['karyawanid'],
									'kodecustomer' => '',
									'kodesupplier' => '',
									'noreferensi' => 'ALK_GAJI_LBR',
									'noaruskas' => '',
									'kodevhc' => '',
									'nodok' => '',
									'kodeblok' => $dtblok,
									'revisi' => '0',
									'kodesegment' => $defSegment
								);
								$noUrut++;
							}
						}
					}

					$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($queryH);

					$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
					$owlPDO->exec($queryD);

					$fkespnn = true;
				} #= tutup jurnal panen

				$fkesip = false;
				#= jurnal sipil
				if ($porsisipil > 0) {
					$no = $tnilairupiah = 0;
					$akundebet = '';
					$akunkredit = '';
					$kodejurnal = 'M11';
					$dataRes = array();

					$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "' limit 1";
					$res = fetchdata($str);
					foreach ($res as $bar) {
						$akundebet = $bar['noakundebet'];
						$akunkredit = $bar['noakunkredit'];
					}

					$queryJ = selectQuery(
						$dbname,
						'keu_5kelompokjurnal',
						'nokounter',
						"kodeorg='" . $kodept . "' and kodekelompok='" . $kodejurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
					);
					$tmpKonter = fetchData($queryJ);
					$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

					# Transform No Jurnal dari No Transaksi
					$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . $konter;
					// exit("Error:".$akundebet._.$akunkredit);

					#= update counter jurnal
					$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
						kodeorg='" . $kodept . "' and kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $param['periode'] . "' ";
					$owlPDO->exec($str);

					# Prep Header
					$dataRes['header'] = array(
						'nojurnal' => $nojurnal,
						'kodejurnal' => $kodejurnal,
						'tanggal' => $tanggal,
						'tanggalentry' => date('Ymd'),
						'posting' => 1,
						'totaldebet' => ($totalnilaisipil + $pembulatansipil),
						'totalkredit' => -1 * ($totalnilaisipil + $pembulatansipil),
						// 'totaldebet'   => ($totalnilaisipil),
						// 'totalkredit'  => -1 * ($totalnilaisipil),
						'amountkoreksi' => '0',
						'noreferensi' => 'ALK_GAJI_LBR',
						'autojurnal' => '1',
						'matauang' => 'IDR',
						'kurs' => '1',
						'revisi' => '0'
					);

					# Data Detail
					$noUrut = 1;

					# Kredit
					$dataRes['detail'][] = array(
						'nojurnal' => $nojurnal,
						'tanggal' => $tanggal,
						'nourut' => $noUrut,
						'noakun' => $akunkredit,
						'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS (Kes) Biaya sipil ' . $param['periode'],
						'jumlah' => ($totalnilaisipil + $pembulatansipil) * -1,
						// 'jumlah'      => ($totalnilaisipil) * -1,
						'matauang' => 'IDR',
						'kurs' => '1',
						'kodeorg' => $param['kodeorg'],
						'kodekegiatan' => '',
						'kodeasset' => '',
						'kodebarang' => '',
						'nik' => $param['karyawanid'],
						'kodecustomer' => '',
						'kodesupplier' => '',
						'noreferensi' => 'ALK_GAJI_LBR',
						'noaruskas' => '',
						'kodevhc' => '',
						'nodok' => '',
						'kodeblok' => '',
						'revisi' => '0',
						'kodesegment' => $defSegment
					);
					$noUrut++;

					# Debet
					foreach ($arrbloksipil as $dtblok) {
						foreach ($arrkegiatansipil as $dtkegiatan) {
							if ($lsdatasipil[$dtblok][$dtkegiatan] != '') {
								$no++;
								if ($no != 1) {
									$pembulatansipil = 0;
								}
								$dataRes['detail'][] = array(
									'nojurnal' => $nojurnal,
									'tanggal' => $tanggal,
									'nourut' => $noUrut,
									'noakun' => substr($dtkegiatan, 0, 7),
									'keterangan' => 'Alokasi Gaji(Unalocated) Porsi BPJS (Kes) Biaya sipil ' . $param['periode'],
									// 'jumlah'      =>($nilairupiah+$pembulatansipil),
									// 'jumlah'      =>($upporsisipil[$dtblok]+$pembulatansipil),
									'jumlah' => ($upporsisipil[$dtblok][$dtkegiatan] + $pembulatansipil),
									'matauang' => 'IDR',
									'kurs' => '1',
									'kodeorg' => $param['kodeorg'],
									'kodekegiatan' => $dtkegiatan,
									'kodeasset' => $dtblok,
									'kodebarang' => '',
									'nik' => $param['karyawanid'],
									'kodecustomer' => '',
									'kodesupplier' => '',
									'noreferensi' => 'ALK_GAJI_LBR',
									'noaruskas' => '',
									'kodevhc' => '',
									'nodok' => '',
									'kodeblok' => $dtblok,
									'revisi' => '0',
									'kodesegment' => $defSegment
								);
								$noUrut++;
							}
						}
					}


					$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
					$owlPDO->exec($queryH);

					$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
					$owlPDO->exec($queryD);

					$fkesip = true;
				}
				#= tutup jurnal sipil

				if (!$fkesrwt && !$fkespnn && !$fkesip) {
					#JAGA JAGA JIKA ADA KURANG ALOKASI
					if (abs($selisihpembulatan) > 0) {
						// $blok = $param['subbagian'];
						// $kodekegkebun = "";
						// if ($akunkebun != '') {
						// 	$akunsisa = $akunkebun;
						// 	if (!empty($tempblok[substr($akunkebun, 0, 3)])) {
						// 		$blok = $tempblok[substr($akunkebun, 0, 3)];
						// 	}
						// 	$kodekegkebun = $kegkebun;
						// } elseif ($akunsdm != '') {
						// 	$akunsisa = $akunsdm;
						// } else {
						// 	$akunsisa = $akundebet;
						// }

						$akundebet = '';
						$akunkredit = '';
						$kodejurnal1 = 'KBNB0';
						$dataRes = array();

						
						$akunsisa = ($lastAkunSdm == '' ? $lastAkunBkm : $lastAkunSdm);
						$kegsisa = $lastKegBkm;
						$bloksisa = $lastBlok;

						if (getNamaOrg($param['subbagian'], "tipe") == "TRAKSI" && $akunsisa == "") {
							$kodejurnal1 = "VHCG0";
						}

						$str = "select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal1 . "' limit 1";
						$res = fetchdata($str);
						foreach ($res as $bar) {
							$akundebet = $bar['noakundebet'];
							$akunkredit = $bar['noakunkredit'];

							if (getNamaOrg($param['subbagian'], "tipe") == "TRAKSI" && $akunsisa == "") {
								$akunsisa = $bar['noakundebet'];
							}
						}

						$queryJ = selectQuery(
							$dbname,
							'keu_5kelompokjurnal',
							'nokounter',
							"kodeorg='" . $kodept . "' and kodekelompok='" . $kodejurnal1 . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "
						);
						$tmpKonter = fetchData($queryJ);
						$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);

						# Transform No Jurnal dari No Transaksi
						$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal1 . "/" . $konter;
						// exit("Error:".$akundebet._.$akunkredit);


						#= update counter jurnal
						$str = "update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where 
					kodeorg='" . $kodept . "' and kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal1 . "' and periode='" . $param['periode'] . "' ";
						$owlPDO->exec($str);

						# Prep Header
						$dataRes['header'] = array(
							'nojurnal' => $nojurnal,
							'kodejurnal' => $kodejurnal1,
							'tanggal' => $tanggal,
							'tanggalentry' => date('Ymd'),
							'posting' => 1,
							'totaldebet' => fixnan($selisihpembulatan),
							'totalkredit' => -1 * fixnan($selisihpembulatan),
							'amountkoreksi' => '0',
							'noreferensi' => 'ALK_GAJI_LBR',
							'autojurnal' => '1',
							'matauang' => 'IDR',
							'kurs' => '1',
							'revisi' => '0'
						);

						$noUrut = 1;
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $akunsisa,
							'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
							'jumlah' => fixnan($selisihpembulatan),
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $param['kodeorg'],
							'kodekegiatan' => $kegsisa,
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => $param['karyawanid'],
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => 'ALK_GAJI_LBR',
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => '',
							'kodeblok' => $lastBlok,
							'revisi' => '0',
							'kodesegment' => $defSegment
						);
						$noUrut++;

						// $queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
						// $owlPDO->exec($queryH);

						// $queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
						// $owlPDO->exec($queryD);


						# Kredit
						$dataRes['detail'][] = array(
							'nojurnal' => $nojurnal,
							'tanggal' => $tanggal,
							'nourut' => $noUrut,
							'noakun' => $akunkredit,
							'keterangan' => 'Alokasi Gaji(Unalocated) ' . $param['periode'],
							'jumlah' => fixnan($selisihpembulatan) * -1,
							'matauang' => 'IDR',
							'kurs' => '1',
							'kodeorg' => $param['kodeorg'],
							'kodekegiatan' => '',
							'kodeasset' => '',
							'kodebarang' => '',
							'nik' => $param['karyawanid'],
							'kodecustomer' => '',
							'kodesupplier' => '',
							'noreferensi' => 'ALK_GAJI_LBR',
							'noaruskas' => '',
							'kodevhc' => '',
							'nodok' => '',
							'kodeblok' => $param['subbagian'],
							'revisi' => '0',
							'kodesegment' => $defSegment
						);
						$noUrut++;

						$queryH = insertQuery($dbname, 'keu_jurnalht', $dataRes['header']);
						$owlPDO->exec($queryH);

						$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
						$owlPDO->exec($queryD);
					}




				} #= tutup jurnal bpjs
			}
			// echo"<pre>";
			// print_r($queryH);
			// print_r($queryD);
			// print_r($dataRes);
			// exit("error");
			// #eksekusi 
			// $owlPDO->exec($str);	

			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}

		break;



	case 'list':
		// $whereKary = " AND karyawanid='0000006018'";

		#2 Ambil subunit setiap karyawan
		#$str="select * from ".$dbname.".datakaryawan where lokasitugas='".$param['kodeorg']."'";
		$str = "select * from " . $dbname . ".datakaryawan_hist where lokasitugas='" . $param['kodeorg'] . "' and periodegaji='" . $param['periode'] . "' and version_type='B' {$whereKary}";
		// echo"<pre>";
		// print_r($param);
		// exit("error");

		$res = fetchdata($str);
		foreach ($res as $bar) {
			$subbagiankaryawan[$bar['karyawanid']] = $bar['subbagian'];
			$namakaryawan[$bar['karyawanid']] = $bar['namakaryawan'];
			$nikkaryawan[$bar['karyawanid']] = $bar['nik'];
			$kodejabatankaryawan[$bar['karyawanid']] = $bar['kodejabatan'];
		}

		$str = "select * from " . $dbname . ".sdm_5bpjs";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrbpjsplus[$bar['jenisbpjsplus']] = $bar['jenisbpjsplus'];
		}


		$str = "select * from " . $dbname . ".sdm_gajidetail_vw where 
				kodeorg='" . $param['kodeorg'] . "' and plus=1 and 
				periodegaji='" . $param['periode'] . "' {$whereKary}";
		// echo $str;
		// periodegaji='".$param['periode']."' and karyawanid in ('0000004717','0000004970')";	   
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$arrkaryawanid[$bar['karyawanid']] = $bar['karyawanid'];
			if (in_array($bar['idkomponen'], $arrbpjsplus)) {
				if ($bar['idkomponen'] == 71) {
					@$gajibpjskes[$bar['karyawanid']] += $bar['jumlah'];
				} else {
					@$gajibpjs[$bar['karyawanid']] += $bar['jumlah'];
				}
			} else {
				@$gaji[$bar['karyawanid']] += $bar['jumlah'];
			}
		}


		#= ambil data karyawan yang sudah dialokasi
		#= Tambah kodejurnal KK,KM,BK,BM ini not in karena ada yang kontanan
		#= Tambah noreferensi not like KK,BK karena ada pembayaran kekurangan gaji
		$str = "select * from " . $dbname . ".keu_jurnaldt_vw where 
				kodeorg='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' and noakun='2130101' and noreferensi!='ALK_GAJI_LBR' and keterangan not like '%denda%' 
				and kodejurnal not in ('KK','KM','BK','BM','POT') and (noreferensi not like '%/KK/%' and noreferensi not like '%/BK/%')
				";

		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$gajisudahalokasi[$bar['nik']] += $bar['jumlah'];
		}

		// echo "<pre>";
		// print_r($gajisudahalokasi);

		#= ambil data karyawan yang sudah dialokasi
		$str = "select * from " . $dbname . ".keu_jurnaldt_vw where 
				kodeorg='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' and noakun='2130102' 
				and kodejurnal!='POT' and noreferensi!='ALK_GAJI_LBR'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$gajisudahalokasibpjs[$bar['nik']] += $bar['jumlah'];
		}
		#= ambil data karyawan yang sudah dialokasi kes
		$str = "select * from " . $dbname . ".keu_jurnaldt_vw where 
				kodeorg='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' and noakun='2130104' 
				and kodejurnal!='POT' and noreferensi!='ALK_GAJI_LBR'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$gajisudahalokasibpjskes[$bar['nik']] += $bar['jumlah'];
		}

		#= ambil data karyawan yang sudah dialokasi khusu bm tbs
		$str = "select * from " . $dbname . ".keu_jurnaldt_vw where 
				kodeorg='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' and kodejurnal='BM01' and nik!='' and jumlah>0
				and kodejurnal!='POT' and noreferensi!='ALK_GAJI_LBR'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			@$gajisudahalokasi[$bar['nik']] += ($bar['jumlah'] * -1);
		}


		@$debhid = " hidden ";
		$tab .= "<table class=sortable cellpadding=5 cellspacing=1 border=0 id=mytable>
			  <thead>
			<thead>
			
		<tr class=rowheader>	
			<th rowspan=2>No</th>
			<th colspan=5 align=center>" . $_SESSION['lang']['karyawan'] . "</th>
			<th colspan=3 align=center>" . $_SESSION['lang']['gaji'] . "</th>
			<th colspan=3 align=center>" . $_SESSION['lang']['alokasirp'] . "</th>
			<th colspan=3 rowspan=2 align=center " . @$debhid . ">WAKWAW</th>

			<th colspan=4 align=center> " . $_SESSION['lang']['blmAlokasi'] . "</th>
		</tr>
		<tr class=rowheader>
				<th align=center>" . $_SESSION['lang']['karyawanid'] . "</th>
				<th align=center>" . $_SESSION['lang']['nik'] . "</th>
				<th align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>
				<th align=center>" . $_SESSION['lang']['jabatan'] . "</th>
				<th align=center>" . $_SESSION['lang']['subbagian'] . "</th>
				<th align=center>" . $_SESSION['lang']['gaji'] . " (+)</th>
				<th align=center>" . $_SESSION['lang']['bpjs'] . " Ket (+)</th>
				<th align=center>" . $_SESSION['lang']['bpjs'] . " Kes (+)</th>
				<th align=center>" . $_SESSION['lang']['gaji'] . "</th>
				<th align=center>" . $_SESSION['lang']['bpjs'] . " Ket</th>
				<th align=center>" . $_SESSION['lang']['bpjs'] . " Kes</th>
				<th align=center>" . $_SESSION['lang']['gaji'] . "</th>
				<th align=center>" . $_SESSION['lang']['bpjs'] . " Ket</th>
				<th align=center>" . $_SESSION['lang']['bpjs'] . " Kes</th>
				<th align=center>" . $_SESSION['lang']['total'] . "</th>
				</tr>
		</thead><tbody>";
		$no = 0;

		// echo "<pre>";
		// print_r($gajisudahalokasi['0000000758']);

		foreach ($arrkaryawanid as $dtkaryawanid) {

			@$gajisisa[$dtkaryawanid] = $gaji[$dtkaryawanid] + $gajisudahalokasi[$dtkaryawanid];
			// @$gajisisa[$dtkaryawanid] = number_format($gajisisa[$dtkaryawanid]);
			// @$gajisisa[$dtkaryawanid] = str_replace(',', '', $gajisisa[$dtkaryawanid]);

			@$gajisisabpjs[$dtkaryawanid] = $gajibpjs[$dtkaryawanid] + $gajisudahalokasibpjs[$dtkaryawanid];
			// @$gajisisabpjs[$dtkaryawanid] = number_format($gajisisabpjs[$dtkaryawanid]);
			// @$gajisisabpjs[$dtkaryawanid] = str_replace(',', '', $gajisisabpjs[$dtkaryawanid]);

			@$gajisisabpjskes[$dtkaryawanid] = $gajibpjskes[$dtkaryawanid] + $gajisudahalokasibpjskes[$dtkaryawanid];
			// @$gajisisabpjskes[$dtkaryawanid] = number_format($gajisisabpjskes[$dtkaryawanid]);
			// @$gajisisabpjskes[$dtkaryawanid] = str_replace(',', '', $gajisisabpjskes[$dtkaryawanid]);


			if (abs($gajisisa[$dtkaryawanid]) > 0 or $gajisisabpjs[$dtkaryawanid] > 0 or $gajisisabpjskes[$dtkaryawanid] > 0) {
				@$no++;
				$tab .= "<tr class=rowcontent id='row" . $no . "'>";
				$tab .= "<td align=center>" . $no . "</td>";
				$tab .= "<td align=left id=karyawanid" . $no . ">" . $dtkaryawanid . "</td>";
				$tab .= "<td align=left>" . $nikkaryawan[$dtkaryawanid] . "</td>";
				$tab .= "<td align=left>" . $namakaryawan[$dtkaryawanid] . "</td>";
				$tab .= "<td align=left>" . $namajabatan[$kodejabatankaryawan[$dtkaryawanid]] . "</td>";
				$tab .= "<td align=right id=subbagian" . $no . ">" . $subbagiankaryawan[$dtkaryawanid] . "</td>";
				$tab .= "<td align=right>" . number_format(@$gaji[$dtkaryawanid]) . "</td>";
				$tab .= "<td align=right>" . number_format(@$gajibpjs[$dtkaryawanid]) . "</td>";
				$tab .= "<td align=right>" . number_format(@$gajibpjskes[$dtkaryawanid]) . "</td>";

				$tab .= "<td align=right>" . number_format(@$gajisudahalokasi[$dtkaryawanid]) . "</td>";
				$tab .= "<td align=right>" . number_format(@$gajisudahalokasibpjs[$dtkaryawanid]) . "</td>";
				$tab .= "<td align=right>" . number_format(@$gajisudahalokasibpjskes[$dtkaryawanid]) . "</td>";

				$tab .= "<td align=right " . @$debhid . " id=gajisisa" . $no . ">" . @$gajisisa[$dtkaryawanid] . "</td>";
				$tab .= "<td align=right " . @$debhid . " id=gajisisabpjs" . $no . ">" . @$gajisisabpjs[$dtkaryawanid] . "</td>";
				$tab .= "<td align=right " . @$debhid . " id=gajisisabpjskes" . $no . ">" . @$gajisisabpjskes[$dtkaryawanid] . "</td>";

				$tab .= "<td align=right >" . str_replace(',', '', number_format(@$gajisisa[$dtkaryawanid])) . "</td>";
				$tab .= "<td align=right >" . str_replace(',', '', number_format(@$gajisisabpjs[$dtkaryawanid])) . "</td>";
				$tab .= "<td align=right >" . str_replace(',', '', number_format(@$gajisisabpjskes[$dtkaryawanid])) . "</td>";

				$total = @$gajisisa[$dtkaryawanid] + @$gajisisabpjs[$dtkaryawanid] + @$gajisisabpjskes[$dtkaryawanid];

				$tab .= "<td " . @$debhid . " align=right>" . $total . "</td>";
				$tab .= "<td align=right>" . str_replace(',', '', number_format($total)) . "</td>";

				$tab .= "</tr>";

				@$tgaji += $gaji[$dtkaryawanid];
				@$tgajibpjs += $gajibpjs[$dtkaryawanid];
				@$tgajibpjskes += $gajibpjskes[$dtkaryawanid];
				@$tgajisudahalokasi += $gajisudahalokasi[$dtkaryawanid];
				@$tgajisudahalokasibpjs += $gajisudahalokasibpjs[$dtkaryawanid];
				@$tgajisudahalokasibpjskes += $gajisudahalokasibpjskes[$dtkaryawanid];
				@$tgajisisa += $gajisisa[$dtkaryawanid];
				@$tgajisisabpjs += $gajisisabpjs[$dtkaryawanid];
				@$tgajisisabpjskes += $gajisisabpjskes[$dtkaryawanid];
			}
		}
		if ($no == 0) {
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center colspan=13>Data sudah teralokasi seluruhnya, silahkan lanjut ke proses lainnya.</td>";
			$tab .= "</tr>";
		} else {
			$tab .= "<tr class=rowcontent id='row" . $no . "'>";
			$tab .= "<td align=center colspan=6>" . $_SESSION['lang']['total'] . "</td>";
			$tab .= "<td align=right>" . number_format($tgaji) . "</td>";
			$tab .= "<td align=right>" . number_format($tgajibpjs) . "</td>";
			$tab .= "<td align=right>" . number_format($tgajibpjskes) . "</td>";
			$tab .= "<td align=right>" . number_format($tgajisudahalokasi) . "</td>";
			$tab .= "<td align=right>" . number_format($tgajisudahalokasibpjs) . "</td>";
			$tab .= "<td align=right>" . number_format($tgajisudahalokasibpjskes) . "</td>";
			$tab .= "<td " . @$debhid . " align=right>" . $tgajisisa . "</td>";
			$tab .= "<td " . @$debhid . " align=right>" . $tgajisisabpjs . "</td>";
			$tab .= "<td " . @$debhid . " align=right>" . $tgajisisabpjskes . "</td>";
			$tab .= "<td align=right>" . number_format($tgajisisa) . "</td>";
			$tab .= "<td align=right>" . number_format($tgajisisabpjs) . "</td>";
			$tab .= "<td align=right>" . number_format($tgajisisabpjskes) . "</td>";
			$tab .= "<td " . @$debhid . " align=right>" . ($tgajisisa + $tgajisisabpjs) . "</td>";
			$tab .= "<td align=right>" . number_format($tgajisisa + $tgajisisabpjs) . "</td>";

			$tab .= "</tr>";
			$tab .= "<button class=mybutton onclick=savegajibelumalokasi(" . $no . ") id=btnproses>Process</button><button class=mybutton onclick=exportTableToExcel()>Excel</button><br>";
		}
		$tab .= "</table>";

		echo $tab;
		break;
	default:
		break;
}
