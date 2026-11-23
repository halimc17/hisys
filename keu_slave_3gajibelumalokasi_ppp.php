<?php
# CABANG PPP dari keu_slave_3gajibelumalokasi.php (Proses Akhir Bulan > 4. Gaji Karyawan Belum Teralokasi). Dipanggil otomatis oleh js/keu_3posting.js (tujuanProses)
# hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_3gajibelumalokasi.php. Perubahan khusus PPP tandai "#PPP".
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
require_once('lib/zAlokasiGajiPPP.php'); #PPP: validasi parameter dan hitung sisa gaji di server

if (!function_exists('simpanJurnalBpjsLbr')) {
	# Satu jurnal BPJS gaji belum teralokasi: Dr akun BPJS (debet parameter jurnal), Cr hutang BPJS (kredit parameter jurnal)
	function simpanJurnalBpjsLbr($kodept, $tanggal, $param, $kodejurnal, $jumlah, $ket, $defSegment)
	{
		global $dbname;
		global $owlPDO;

		$res = fetchdata("select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "' limit 1");
		if (count($res) == 0 || $res[0]['noakundebet'] == '' || $res[0]['noakunkredit'] == '') {
			throw new PDOException('Akun parameter jurnal ' . $kodejurnal . ' belum diisi (keuangan -> setup -> parameter jurnal)');
		}
		$akundebet = $res[0]['noakundebet'];
		$akunkredit = $res[0]['noakunkredit'];

		$tmpKonter = fetchData(selectQuery($dbname, 'keu_5kelompokjurnal', 'nokounter', "kodeorg='" . $kodept . "' and kodekelompok='" . $kodejurnal . "' and kodeunit='" . $param['kodeorg'] . "' and periode='" . $param['periode'] . "' "));
		$konter = addZero($tmpKonter[0]['nokounter'] + 1, 3);
		$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . $konter;
		while (count(fetchdata("select nojurnal from " . $dbname . ".keu_jurnalht where nojurnal='" . $nojurnal . "'")) > 0) {
			$konter = addZero(intval($konter) + 1, 3);
			$nojurnal = str_replace("-", "", $tanggal) . "/" . $param['kodeorg'] . "/" . $kodejurnal . "/" . $konter;
		}
		$owlPDO->exec("update " . $dbname . ".keu_5kelompokjurnal set nokounter='" . $konter . "' where kodeorg='" . $kodept . "' and kodeunit='" . $param['kodeorg'] . "' and kodekelompok='" . $kodejurnal . "' and periode='" . $param['periode'] . "' ");

		$header = array(
			'nojurnal' => $nojurnal,
			'kodejurnal' => $kodejurnal,
			'tanggal' => $tanggal,
			'tanggalentry' => date('Ymd'),
			'posting' => 1,
			'totaldebet' => $jumlah,
			'totalkredit' => -1 * $jumlah,
			'amountkoreksi' => '0',
			'noreferensi' => 'ALK_GAJI_LBR',
			'autojurnal' => '1',
			'matauang' => 'IDR',
			'kurs' => '1',
			'revisi' => '0'
		);
		$detail = array();
		foreach (array(1 => array($akundebet, $jumlah), 2 => array($akunkredit, -1 * $jumlah)) as $urut => $d) {
			$detail[] = array(
				'nojurnal' => $nojurnal,
				'tanggal' => $tanggal,
				'nourut' => $urut,
				'noakun' => $d[0],
				'keterangan' => 'Alokasi Gaji(Unalocated) ' . $ket . ' ' . $param['periode'],
				'jumlah' => $d[1],
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
		}
		$owlPDO->exec(insertQuery($dbname, 'keu_jurnalht', $header));
		$owlPDO->exec(insertQuery($dbname, 'keu_jurnaldt', $detail));
	}
}

$method = checkPostGet('method', '');
$param = $_POST;
#PPP: format unit, periode, karyawan, subbagian dan jumlah divalidasi sebelum dipakai di query
$pesanParam = validasiParamGajiBlmPPP(array_merge($param, array('method' => $method)));
if ($pesanParam != '') {
	exit($pesanParam);
}
$defSegment = colDefaultValue($dbname, 'keu_5segment', 'kodesegment');

$tab = '';

$tanggal = $param['periode'] . "-28";
$kodept = '';
$str = "select * from " . $dbname . ".organisasi where  kodeorganisasi = '" . $param['kodeorg'] . "'";
$res = fetchdata($str);
foreach ($res as $bar) {
	$kodept = $bar['induk'];
}
if ($kodept == '') {
	exit('Warning : Unit ' . htmlspecialchars($param['kodeorg']) . ' tidak ditemukan.');
}
$namajabatan = array();

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

			#PPP: sisa gaji/BPJS dihitung ulang di server (nilai kiriman browser tidak dipercaya); subbagian juga dari data karyawan
			$sisaServer = hitungSisaGajiBlmAlokasiPPP($param['kodeorg'], $param['periode'], $param['karyawanid']);
			$dtSisaServer = isset($sisaServer[$param['karyawanid']]) ? $sisaServer[$param['karyawanid']] : array('sisa' => 0, 'sisabpjs' => 0, 'sisakes' => 0);
			$param['gajisisa'] = $dtSisaServer['sisa'];
			$param['gajisisabpjs'] = $dtSisaServer['sisabpjs'];
			$param['gajisisabpjskes'] = $dtSisaServer['sisakes'];
			foreach (fetchData("select subbagian from " . $dbname . ".datakaryawan_hist where lokasitugas='" . $param['kodeorg'] . "' and periodegaji='" . $param['periode'] . "' and version_type='B' and karyawanid='" . $param['karyawanid'] . "' limit 1") as $barSub) {
				$param['subbagian'] = $barSub['subbagian'];
			}
			if (!isset($param['subbagian'])) {
				$param['subbagian'] = '';
			}
			$gtbkm = 0;
			$gtsdm = 0;
			$gtsisa = 0;
			$totalsisa = 0;
			$akunsdm = '';
			$akunkebun = '';
			$kegkebun = '';
			$tempblok = array();
			$akunbkm = array();

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
					@$totalbkm[$bar['kodekegiatan']][$bar['kodeorg']] += $rupiah;
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
					@$totalbkm[$kegpanen][$bar['kodeorg']] += $bar['upahkerja'] + $bar['upahpremi'] + $bar['upahpremilebihbasis'] + $bar['upahpremilebihbasis2'] + $bar['premibasis'] + $bar['premibasis2'] + $bar['premibrondol'];
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

					@$totalsdm[$bar['noakun']][$bar['tanggal']] += $totalNilai;
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

							$valuesisa = ($gtsdm > 0) ? (int) floor(fixnan($value / $gtsdm * $porsisdm)) : 0;

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

				# buang baris detail bernilai nol (hasil pembagian porsi), jurnal tetap seimbang
				$dataRes['detail'] = array_values(array_filter($dataRes['detail'], function ($d) {
					return $d['jumlah'] != 0;
				}));
				foreach ($dataRes['detail'] as $k => $d) {
					$dataRes['detail'][$k]['nourut'] = $k + 1;
				}

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
						@$totalbkm[$bar['kodekegiatan']][$bar['kodeorg']] += $rupiah;
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

						@$totalsdm[$bar['noakun']][$bar['tanggal']] += $totalNilai;
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

				# buang baris detail bernilai nol (hasil pembagian porsi), jurnal tetap seimbang
				$dataRes['detail'] = array_values(array_filter($dataRes['detail'], function ($d) {
					return $d['jumlah'] != 0;
				}));
				foreach ($dataRes['detail'] as $k => $d) {
					$dataRes['detail'][$k]['nourut'] = $k + 1;
				}

				$queryD = insertQuery($dbname, 'keu_jurnaldt', $dataRes['detail']);
				$owlPDO->exec($queryD);

				// if($param['karyawanid']=='0000007251'){					
				// //exit("Error:".$queryH._.$queryD);
				// }
			}



			#= BPJS: satu jurnal per karyawan per jenis ke akun BPJS (parameter jurnal M10 = Ketenagakerjaan, M11 = Kesehatan),
			#= tidak lagi dibagi ke blok atau kegiatan
			if ($param['gajisisabpjs'] > 0) {
				simpanJurnalBpjsLbr($kodept, $tanggal, $param, 'M10', $param['gajisisabpjs'], 'BPJS (TK)', $defSegment);
			}
			if ($param['gajisisabpjskes'] > 0) {
				simpanJurnalBpjsLbr($kodept, $tanggal, $param, 'M11', $param['gajisisabpjskes'], 'BPJS (Kesehatan)', $defSegment);
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
		#PPP: sisa gaji dihitung oleh fungsi yang sama dengan proses simpan
		$sisaAll = hitungSisaGajiBlmAlokasiPPP($param['kodeorg'], $param['periode']);

		#data karyawan: histori periode (versi B) kalau ada, kalau tidak data master karyawan
		$infoKar = array();
		foreach (fetchdata("select karyawanid,nik,namakaryawan,kodejabatan,kodegolongan,tipekaryawan,subbagian from " . $dbname . ".datakaryawan_hist where lokasitugas='" . $param['kodeorg'] . "' and periodegaji='" . $param['periode'] . "' and version_type='B'") as $bar) {
			$infoKar[$bar['karyawanid']] = $bar;
		}
		foreach (array_chunk(array_keys($sisaAll), 400) as $potong) {
			$in = "'" . implode("','", array_map('addslashes', $potong)) . "'";
			foreach (fetchdata("select karyawanid,nik,namakaryawan,kodejabatan,kodegolongan,tipekaryawan,subbagian from " . $dbname . ".datakaryawan where karyawanid in (" . $in . ")") as $bar) {
				if (!isset($infoKar[$bar['karyawanid']])) {
					$infoKar[$bar['karyawanid']] = $bar;
				}
			}
		}
		$nmGolongan = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
		$nmTipeKar = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');
		$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

		$tab .= "
			<fieldset style='margin-bottom:8px'><legend><b>Informasi Proses</b></legend>
			<div style='display:flex;flex-wrap:wrap;align-items:stretch;gap:6px;font-size:12px'>
				<div style='flex:1 1 210px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>1. Hitung sisa gaji</b><br>Gaji (+) karyawan unit " . htmlspecialchars($param['kodeorg']) . " periode " . htmlspecialchars($param['periode']) . " dikurangi jurnal gaji yang sudah terbentuk (langkah 1 sampai 3, BKM, absensi). Sisanya yang tampil di <b>Belum Alokasi</b>.</div>
				<div style='align-self:center;font-size:18px'>&rarr;</div>
				<div style='flex:1 1 250px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>2. Alokasikan sisa gaji</b><br>Sisa gaji dibagi mengikuti <b>BKM</b> karyawan (kegiatan/blok) kalau ada, atau <b>akun absensi</b>; selebihnya ke akun debet <b>KBNB0</b> (parameter jurnal) per subbagian. Kredit akun hutang gaji.</div>
				<div style='align-self:center;font-size:18px'>&rarr;</div>
				<div style='flex:1 1 230px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>3. Jurnal BPJS</b><br>Sisa <b>BPJS Ketenagakerjaan</b> ke jurnal <b>M10</b> dan sisa <b>BPJS Kesehatan</b> ke <b>M11</b>: debet akun BPJS, kredit hutang BPJS. Tidak dibagi ke blok/kegiatan.</div>
			</div>
			<div style='font-size:12px;margin-top:6px;line-height:1.5'>
				&bull; Angka jurnal dihitung ulang oleh server saat Process, jadi tidak tergantung angka yang tampil di layar.<br>
				<span style='background:#fff3cd;padding:1px 6px;border:1px solid #e0c36a'><b>Perhatian:</b> Process ulang untuk periode yang sama menghapus jurnal gaji belum teralokasi periode itu lalu membuatnya lagi.</span>
			</div>
			</fieldset>";

		$barisTab = "";
		$no = 0;
		$tgaji = $tgajibpjs = $tgajibpjskes = $tsudah = $tsudahbpjs = $tsudahkes = $tgajisisa = $tgajisisabpjs = $tgajisisabpjskes = 0;
		foreach ($sisaAll as $dtkaryawanid => $d) {
			if (abs($d['sisa']) > 0 or $d['sisabpjs'] > 0 or $d['sisakes'] > 0) {
				$no++;
				$kr = isset($infoKar[$dtkaryawanid]) ? $infoKar[$dtkaryawanid] : array();
				$subbag = isset($kr['subbagian']) ? $kr['subbagian'] : '';
				$divisi = ($subbag != '') ? (isset($nmOrg[$subbag]) ? $nmOrg[$subbag] : $subbag) : 'KANTOR';
				$total = $d['sisa'] + $d['sisabpjs'] + $d['sisakes'];
				$barisTab .= "<tr class=rowcontent id='row" . $no . "'>
					<td align=center>" . $no . "</td>
					<td>" . htmlspecialchars(isset($kr['nik']) ? $kr['nik'] : '') . "</td>
					<td>" . htmlspecialchars(isset($kr['namakaryawan']) ? $kr['namakaryawan'] : '') . "</td>
					<td>" . htmlspecialchars((isset($kr['kodejabatan']) && isset($namajabatan[$kr['kodejabatan']])) ? $namajabatan[$kr['kodejabatan']] : '') . "</td>
					<td>" . htmlspecialchars((isset($kr['kodegolongan']) && isset($nmGolongan[$kr['kodegolongan']])) ? $nmGolongan[$kr['kodegolongan']] : '') . "</td>
					<td>" . htmlspecialchars((isset($kr['tipekaryawan']) && isset($nmTipeKar[$kr['tipekaryawan']])) ? $nmTipeKar[$kr['tipekaryawan']] : '') . "</td>
					<td>" . htmlspecialchars($divisi) . "</td>
					<td align=right>" . number_format($d['gaji']) . "</td>
					<td align=right>" . number_format($d['gajibpjs']) . "</td>
					<td align=right>" . number_format($d['gajibpjskes']) . "</td>
					<td align=right>" . number_format($d['sudah']) . "</td>
					<td align=right>" . number_format($d['sudahbpjs']) . "</td>
					<td align=right>" . number_format($d['sudahkes']) . "</td>
					<td align=right>" . number_format($d['sisa']) . "</td>
					<td align=right>" . number_format($d['sisabpjs']) . "</td>
					<td align=right>" . number_format($d['sisakes']) . "</td>
					<td align=right>" . number_format($total) . "</td>
					<td hidden class=kolom-sembunyi id=karyawanid" . $no . ">" . htmlspecialchars($dtkaryawanid) . "</td>
					<td hidden class=kolom-sembunyi id=subbagian" . $no . ">" . htmlspecialchars($subbag) . "</td>
					<td hidden class=kolom-sembunyi id=gajisisa" . $no . ">" . $d['sisa'] . "</td>
					<td hidden class=kolom-sembunyi id=gajisisabpjs" . $no . ">" . $d['sisabpjs'] . "</td>
					<td hidden class=kolom-sembunyi id=gajisisabpjskes" . $no . ">" . $d['sisakes'] . "</td>
				</tr>";
				$tgaji += $d['gaji'];
				$tgajibpjs += $d['gajibpjs'];
				$tgajibpjskes += $d['gajibpjskes'];
				$tsudah += $d['sudah'];
				$tsudahbpjs += $d['sudahbpjs'];
				$tsudahkes += $d['sudahkes'];
				$tgajisisa += $d['sisa'];
				$tgajisisabpjs += $d['sisabpjs'];
				$tgajisisabpjskes += $d['sisakes'];
			}
		}

		if ($no == 0) {
			$tab .= "<table class=sortable cellpadding=5 cellspacing=1 border=0 id=mytable><tbody>
				<tr class=rowcontent><td align=center colspan=17>Data sudah teralokasi seluruhnya, silahkan lanjut ke proses lainnya.</td></tr>
				</tbody></table>";
		} else {
			$tab .= "
			<style>
				.tombolBlmPPP { display:inline-block; padding:7px 22px; margin:0 8px 8px 0; border-radius:6px; font-size:13px; font-weight:bold; cursor:pointer; }
				#btnproses.tombolBlmPPP { background:#2e7d32; color:#fff; border:1px solid #1b5e20; }
				#btnproses.tombolBlmPPP:hover { background:#388e3c; }
				#btnproses.tombolBlmPPP:disabled { background:#9e9e9e; border-color:#757575; color:#eee; cursor:not-allowed; }
				#btnexcel.tombolBlmPPP { background:#fff; color:#2e7d32; border:1px solid #2e7d32; }
				#btnexcel.tombolBlmPPP:hover { background:#e8f5e9; }
			</style>
			<div>
				<button class='tombolBlmPPP' onclick=savegajibelumalokasi(" . $no . ") id=btnproses title='Buat jurnal untuk semua baris di bawah'>&#9654; Process</button>
				<button class='tombolBlmPPP' onclick=exportTableToExcel() id=btnexcel title='Unduh tabel ini ke Excel'>&#11015; Excel</button>
			</div>
			<table class=sortable cellpadding=5 cellspacing=1 border=0 id=mytable>
			<thead>
				<tr class=rowheader>
					<th rowspan=2>No</th>
					<th colspan=6 align=center>" . $_SESSION['lang']['karyawan'] . "</th>
					<th colspan=3 align=center>" . $_SESSION['lang']['gaji'] . "</th>
					<th colspan=3 align=center>" . $_SESSION['lang']['alokasirp'] . "</th>
					<th colspan=4 align=center>" . $_SESSION['lang']['blmAlokasi'] . "</th>
				</tr>
				<tr class=rowheader>
					<th align=center>" . $_SESSION['lang']['nik'] . "</th>
					<th align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>
					<th align=center>" . $_SESSION['lang']['jabatan'] . "</th>
					<th align=center>Golongan</th>
					<th align=center>Tipe Karyawan</th>
					<th align=center>Divisi</th>
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
			</thead>
			<tbody>" . $barisTab . "
				<tr class=rowcontent>
					<td align=center colspan=7>" . $_SESSION['lang']['total'] . "</td>
					<td align=right>" . number_format($tgaji) . "</td>
					<td align=right>" . number_format($tgajibpjs) . "</td>
					<td align=right>" . number_format($tgajibpjskes) . "</td>
					<td align=right>" . number_format($tsudah) . "</td>
					<td align=right>" . number_format($tsudahbpjs) . "</td>
					<td align=right>" . number_format($tsudahkes) . "</td>
					<td align=right>" . number_format($tgajisisa) . "</td>
					<td align=right>" . number_format($tgajisisabpjs) . "</td>
					<td align=right>" . number_format($tgajisisabpjskes) . "</td>
					<td align=right>" . number_format($tgajisisa + $tgajisisabpjs + $tgajisisabpjskes) . "</td>
				</tr>
			</tbody></table>";
		}

		echo $tab;
		break;
	default:
		break;
}
