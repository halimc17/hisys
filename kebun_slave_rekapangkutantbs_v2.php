<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

if (count($_POST) > 0) {
	$param = $_POST;
} else {
	$param = $_GET;
}

$method      = checkPostGet('method', '');
$kodeorg     = checkPostGet('kodeorg', '');
$periode     = checkPostGet('periode', '');
$spk         = checkPostGet('spk', '');
$nospb       = checkPostGet('nospb', '');
$tiket       = checkPostGet('tiket', '');
$blok        = checkPostGet('blok', '');
$tujuan      = checkPostGet('tujuan', '');
$harga_muat  = checkPostGet('harga_muat', '');
$harga_angkut = checkPostGet('harga_angkut', '');
$tanggal     = tanggalsystemn(checkPostGet('tanggal', ''));
$tglheader   = tanggalsystemn(checkPostGet('tgl', ''));
$tglsch      = checkPostGet('tglsch', '');
$divsch      = checkPostGet('divsch', '');
$divisi      = checkPostGet('divisi', '');
$kgwbdet     = checkPostGet('kgwbdet', '');
$rp_muat     = checkPostGet('rp_muat', '');
$rp_muat2    = checkPostGet('rp_muat2', '');
$rp_muat3    = checkPostGet('rp_muat3', '');
$rp_muat4    = checkPostGet('rp_muat4', '');
$rp_muat5    = checkPostGet('rp_muat5', '');
$rp_muat6    = checkPostGet('rp_muat6', '');
$rp_muat7    = checkPostGet('rp_muat7', '');
$addrp_muat  = checkPostGet('addrp_muat', '');
$rp_angkut   = checkPostGet('rp_angkut', '');
$rp_angkut2  = checkPostGet('rp_angkut2', '');
$rp_angkut3  = checkPostGet('rp_angkut3', '');
$rp_angkut4  = checkPostGet('rp_angkut4', '');
$rp_angkut5  = checkPostGet('rp_angkut5', '');
$rp_angkut6  = checkPostGet('rp_angkut6', '');
$rp_angkut7  = checkPostGet('rp_angkut7', '');
$addrp_angkut = checkPostGet('addrp_angkut', '');
$kgwb        = checkPostGet('kgwb', '');
$kegangkut   = checkPostGet('kegangkut', '');
$kegmuat     = checkPostGet('kegmuat', '');
$periodebyr  = checkPostGet('periodebyr', '');
$nobapp      = checkPostGet('nobapp', '');
$jnskend     = checkPostGet('jnskend', '');
$pkstujuan   = checkPostGet('pkstujuan', '');
$kgwbpks     = checkPostGet('kgwbpks', '');
$kgbrd       = checkPostGet('kgbrd', '');
$potonganrp  = checkPostGet('potonganrp', '');
$ttlrowfee   = checkPostGet('ttlrowfee', '');
$nospkcr     = checkPostGet('nospkcr', '');
$kontrakcr   = checkPostGet('kontrakcr', '');
$jenistampil = checkPostGet('jenis', '');
$tglmulai 	 = tanggalsystemn(checkPostGet('tglmulai', ''));
$tglselesai  = tanggalsystemn(checkPostGet('tglselesai', ''));

if ($kgwbdet == '') {
	$kgwbdet = 0;
}

$kodept = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "length(kodeorganisasi)='4'");

$divsch = checkPostGet('divsch', '');
$bloksch = checkPostGet('bloksch', '');
$unitexp = checkPostGet('unitexp', '');
$perexp = checkPostGet('perexp', '');
$bjr    = checkPostGet('bjr', '');
$kgkebun = checkPostGet('kgkebun', '');

$tanggal1 = $tglmulai;
$tanggal2 = $tglselesai;

$sql = "SELECT * FROM " . $dbname . ".keu_5akun";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$optjnsfee[$bar['noakun']] = $bar['namaakun'];
}

$sql = "SELECT * FROM " . $dbname . ".setup_kegiatan";
$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
$qry->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $qry->fetch()) {
	$optjnsfee[$bar['kodekegiatan']] = $bar['namakegiatan'];
}

$jab  = getPostingJabatan('panen');
$nmorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$nmjns = makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
$nmjns += array("GLOBAL" => "GLOBAL");
$arrmuat = array('tphpks1' => 'TPH-PKS 1', 'tphpks2' => 'TPH-PKS 2', 'tphpks3' => 'TPH-PKS 3', 'tphpks4'  => 'TPH-PKS 4', 'tphpks5' => 'TPH-PKS 5', 'tphpks6' => 'TPH-PKS 6', 'tphpks7' => 'TPH-PKS 7');
switch ($method) {
	case 'getnospk':
		$namasupp = array();
		$optsupp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sql = "SELECT a.* FROM " . $dbname . ".log_spkht a 
		left join " . $dbname . ".lgl_pengajuanspkht b on a.nopengajuan=b.notransaksi 
		where a.posting='0' and b.close='0' and b.jenis='ANGKUTTBS' and a.kodeorg='" . $param['kodeorg'] . "' and substr(a.dari,1,7)<='" . $periode . "' and substr(a.sampai,1,7)>='" . $periode . "' order by a.notransaksi asc";
		$res = fetchdata($sql);
		foreach ($res as $bar) {
			$namasupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $bar['koderekanan'] . "'");

			$optsupp .= "<option value='" . $bar['notransaksi'] . "'>" . $bar['notransaksi'] . " - " . $namasupp[$bar['koderekanan']] . "</option>";
		}
		echo $optsupp;
		break;
	case 'deletedetail':
		$str = "delete from " . $dbname . ".kebun_rekapangkutantbsdt where nospb='" . $nospb . "' and blok='".$blok."' ";
		try {
			$owlPDO->exec($str);
            
            $tes = "select sum(totalkgwb) as ttltonase from " . $dbname . ".kebun_rekapangkutantbsht where kodeorg = '" . $kodeorg . "' and periode = '" . $periode . "' and nospb = '" . $nospb . "' and periodebyr='".$periodebyr."' and spk='".$spk."'";
            $resq= fetchdata($tes);

            $where = "nospb='" . $nospb . "' and kodeorg='" . $kodeorg . "' and periodebyr='".$periodebyr."' and spk='".$spk."'";
            $esqiel = "update " . $dbname . ".kebun_rekapangkutantbsht set totalkgwb = '".($resq[0]['ttltonase']-$kgwb)."' where ".$where."";
            try {
                $owlPDO->exec($esqiel);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'insert':
		try {
			$owlPDO->beginTransaction();
			if ($param['tujuan'] != '') {
				$cek = "select distinct periodebyr from " . $dbname . ".kebun_rekapangkutantbsht where kodeorg = '" . $kodeorg . "' and periode = '" . $periode . "'";
				$rcek = fetchdata($cek);
				foreach ($rcek as $resc => $barc) {
					if ($periodebyr == '0' and ($barc['periodebyr'] == '1' or $barc['periodebyr'] == '2')) {
						throw new PDOException("Periode bayar sebelumnya sudah pernah di pilih Pertama (Tanggal : 1 s/d 15) atau Kedua (Tanggal : 16 s/d 30), untuk melanjutkan silahkan pilih periode bayar Pertama atau Kedua.");
					}
					if ($barc['periodebyr'] == '0' and ($periodebyr == '1' or $periodebyr == '2')) {
						throw new PDOException("Periode bayar sebelumnya sudah pernah di pilih Sebulan (Tanggal : 1 s/d 30), untuk melanjutkan silahkan pilih periode bayar Sebulan.");
					}
				}

				$arrdata = array('muat' => 'tphpks1', 'muat' => 'tphpks2', 'muat' => 'tphpks3', 'muat' => 'tphpks4', 'muat' => 'tphpks5', 'muat' => 'tphpks6', 'muat' => 'tphpks7', 'angkut' => 'tphpks1', 'angkut' => 'tphpks2', 'angkut' => 'tphpks3', 'angkut' => 'tphpks4', 'angkut' => 'tphpks5', 'angkut' => 'tphpks6', 'angkut' => 'tphpks7');

				$str = "select * from " . $dbname . ".kebun_rekapangkutantbsht where kodeorg = '" . $kodeorg . "' and periode = '" . $periode . "' and nospb = '" . $nospb . "' and periodebyr='".$periodebyr."'";
				$xx = $xxxx = '';
				if (count(fetchdata($str)) == 0) {
					#jika belum ada di ht, insert dulu
					$kgwb = 0;
					$str = "select * from " . $dbname . ".kebun_spb_vw4 where nospb='" . $nospb . "' and blok like '".substr($blok,0,4)."%'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					while ($bar = $res->fetch()) {
						$divisi = $bar['divisi'];
						$kgwb += $bar['kgwb'];
						$tanggal = $bar['tanggal'];
					}
					$data = array();
					$data = array(
						'kodeorg'   => $kodeorg,
						'divisi'    => $divisi,
						'periode'   => $periode,
						'periodebyr' => $periodebyr,
						'tanggal'   => $tanggal,
						'tgldari'   => $tglmulai,
						'tglsampai' => $tglselesai,
						'nospb'     => $nospb,
						'spk'       => $spk,
						'pkstujuan' => $pkstujuan,
						'jenisvhc'  => $jnskend,
						'posting'   => '0',
						// 'totalkgwb' => $kgwb,
						'createby'  => $_SESSION['standard']['userid'],
						'createtime' => date('Y-m-d H:i:s'),
						'updateby'  => $_SESSION['standard']['userid']
					);

					$cols = array();
					foreach ($data as $key => $row) {
						$cols[] = $key;
					}
					$total = $rp = $rp2 = $rp3 = $rp4 = $rp5 = $rp6 = $rp7 = '0';
					foreach ($arrdata as $key => $val) {
						if ($key == 'muat') {
							$addrp    = $addrp_muat;
							$pekerjaan = $kegmuat;
							switch ($param['tujuan']) {
								case 'tphpks1':
									$rp       = $rp_muat;
									break;
								case 'tphpks2':
									$rp2      = $rp_muat2;
									break;
								case 'tphpks3':
									$rp3      = $rp_muat3;
									break;
								case 'tphpks4':
									$rp4      = $rp_muat4;
									break;
								case 'tphpks5':
									$rp5      = $rp_muat5;
									break;
								case 'tphpks6':
									$rp6      = $rp_muat6;
									break;
								case 'tphpks7':
									$rp7      = $rp_muat7;
									break;
							}
						} else {
							$addrp    = $addrp_angkut;
							$pekerjaan = $kegangkut;
							switch ($param['tujuan']) {
								case 'tphpks1':
									$rp       = $rp_angkut;
									break;
								case 'tphpks2':
									$rp2      = $rp_angkut2;
									break;
								case 'tphpks3':
									$rp3      = $rp_angkut3;
									break;
								case 'tphpks4':
									$rp4      = $rp_angkut4;
									break;
								case 'tphpks5':
									$rp5      = $rp_angkut5;
									break;
								case 'tphpks6':
									$rp6      = $rp_angkut6;
									break;
								case 'tphpks7':
									$rp7      = $rp_angkut7;
									break;
							}
						}

						$total += (floatval($rp) + floatval($rp2) + floatval($rp3) + floatval($rp4) + floatval($rp5) + floatval($rp6) + floatval($rp7));
						$total += floatval($addrp);
					}
					if ($pekerjaan != '' and $val != '' and $kgwbdet != '0') {
						$str = insertQuery($dbname, 'kebun_rekapangkutantbsht', $data, $cols);
						$owlPDO->exec($str);
					}
				}

				foreach ($arrmuat as $kiy => $vil) {
					foreach ($arrdata as $key => $val) {
						if ($key == 'muat') {
							$addrpz[$kiy][$key]    = $addrp_muat;
							$pekerjaanz[$kiy][$key] = $kegmuat;
							switch ($param['tujuan']) {
								case 'tphpks1':
									$rpz[$kiy][$key]       = $rp_muat;
									break;
								case 'tphpks2':
									$rpz[$kiy][$key]      = $rp_muat2;
									break;
								case 'tphpks3':
									$rpz[$kiy][$key]      = $rp_muat3;
									break;
								case 'tphpks4':
									$rpz[$kiy][$key]      = $rp_muat4;
									break;
								case 'tphpks5':
									$rpz[$kiy][$key]      = $rp_muat5;
									break;
								case 'tphpks6':
									$rpz[$kiy][$key]      = $rp_muat6;
									break;
								case 'tphpks7':
									$rpz[$kiy][$key]      = $rp_muat7;
									break;
							}
						}
						if ($key == 'angkut') {
							$addrpz[$kiy][$key]    = $addrp_angkut;
							$pekerjaanz[$kiy][$key] = $kegangkut;
							switch ($param['tujuan']) {
								case 'tphpks1':
									$rpz[$kiy][$key]       = $rp_angkut;
									break;
								case 'tphpks2':
									$rpz[$kiy][$key]      = $rp_angkut2;
									break;
								case 'tphpks3':
									$rpz[$kiy][$key]      = $rp_angkut3;
									break;
								case 'tphpks4':
									$rpz[$kiy][$key]      = $rp_angkut4;
									break;
								case 'tphpks5':
									$rpz[$kiy][$key]      = $rp_angkut5;
									break;
								case 'tphpks6':
									$rpz[$kiy][$key]      = $rp_angkut6;
									break;
								case 'tphpks7':
									$rpz[$kiy][$key]      = $rp_angkut7;
									break;
							}
						}
					}
				}

				$str = "select sum(totalkg) as totalkg,sum(brondolan) as brondolan from " . $dbname . ".kebun_spb_vw2 where nospb = '" . $nospb . "' and blok='" . $blok . "'";
				$res = fetchdata($str);
				$kgttl = $res[0]['totalkg'] + ($res[0]['totalkg'] == 0 ? $res[0]['brondolan'] : 0);

				$str1x = '';

				foreach ($arrdata as $key => $value) {

					if ($pekerjaanz[$param['tujuan']][$key] != '' and $param['tujuan'] != '' and $kgwbdet != '0' and ($rpz[$param['tujuan']][$key] > 0 or $addrpz[$param['tujuan']][$key] > 0)) {
						$str = "delete from " . $dbname . ".kebun_rekapangkutantbsdt where nospb = '" . $nospb . "' and jenis='" . $key . "' and blok = '" . $blok . "'";
						$owlPDO->exec($str);

						$str = "select sum(kgwb) as kgwb from " . $dbname . ".kebun_rekapangkutantbsdt where nospb = '" . $nospb . "' and jenis='" . $key . "' and blok='" . $blok . "'";
						$res = fetchdata($str);
						$kgtersave = $res[0]['kgwb'];

						$selisih = $kgttl - ($kgtersave + $kgwbpks);
						if (abs($selisih) > 0.9) {
							throw new PDOException("Kg melebihi total Kg di SPB :\nTotal Kg SPB = " . number_format($kgttl) . "\nKg tersimpan = " . number_format($kgtersave) . "\nKg diinput = " . number_format($kgwbpks) . "\nSelisih = " . number_format($selisih) . "");
						}

						if ($key == 'angkut') {
							$potrp = $potonganrp;
						} else {
							$potrp = 0;
						}

						$datadt = array(
							'nospb'        => $nospb,
							'nospk'        => $spk,
							'jeniskegiatan' => $pekerjaanz[$param['tujuan']][$key],
							'jenis'        => $key,
							'tujuan'       => $param['tujuan'],
							'blok'         => $blok,
							'kgtotal'      => $kgwbpks,
							'kgbrd'        => $kgbrd,
							'kgwb'         => $kgwbdet,
							'potonganrp'   => $potrp,
							'rupiah'       => ($rpz[$param['tujuan']][$key] + $addrpz[$param['tujuan']][$key]),
							'rppokok'      => $rpz[$param['tujuan']][$key],
							'rpadd'        => $addrpz[$param['tujuan']][$key]
						);

						$colsdt = array();
						foreach ($datadt as $kuy => $row) {
							$colsdt[] = $kuy;
						}
						$str1x .= insertQuery($dbname, 'kebun_rekapangkutantbsdt', $datadt, $colsdt) . ";";
						// $xxxx.=$str;

                        //UPDATE HT Karena bisa diinput satu-satu per blok
                        //ambil total kg per spb yg sudah tersimpan
						$str = "select sum(kgwb) as kgwb from " . $dbname . ".kebun_rekapangkutantbsdt where nospb = '" . $nospb . "' and jenis='" . $key . "' and nospk='".$spk."'";
						$res = fetchdata($str);
						$kgtersimpan = $res[0]['kgwb'];
                        $where = "nospb='" . $nospb . "' and kodeorg='" . $kodeorg . "' and periodebyr='".$periodebyr."'";
                        $esqiel = "update " . $dbname . ".kebun_rekapangkutantbsht set totalkgwb = '".($kgtersimpan + $kgwbpks)."' where ".$where."";
                        $owlPDO->exec($esqiel);
					}
				}

				if ($str1x != '') {
					$owlPDO->exec($str1x);
				}
			} #tutup if tujuan

			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warningsystem, " . addslashes($e->getMessage());
			die();
		}
		break;

	case 'getharga':
		$str = "select * from " . $dbname . ".kebun_spbht where nospb  = '" . $param['nospb'] . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$tanggal = $bar['tanggal'];
			$periode = substr($bar['tanggal'], 0, 7);
		}

		# curhat dikit ah,
		# gara - gara BPJ ini jadi bertingkat gak jelas

		$addrpmuat = $addrpangkut = $rpangkut = $rpmuat = 0;
		$str = "select * from " . $dbname . ".kebun_5hargaangkut where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jnskend . "' and tanggalberlaku = (SELECT MAX(`tanggalberlaku`) FROM `kebun_5hargaangkut` where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jnskend . "' and tanggalberlaku <= '" . $tanggal . "' and tanggalberlaku like '" . $periode . "%' and nospk='" . $spk . "') and nospk='" . $spk . "'";
		if (count(fetchdata($str)) == 0) {
			$str = "select * from " . $dbname . ".kebun_5hargaangkut where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jnskend . "' and tanggalberlaku = (SELECT MAX(`tanggalberlaku`) FROM `kebun_5hargaangkut` where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jnskend . "' and tanggalberlaku <= '" . $tanggal . "' and tanggalberlaku like '" . $periode . "%' and nospk='') and nospk=''";
			if (count(fetchdata($str)) == 0) {
				$str = "select * from " . $dbname . ".kebun_5hargaangkut where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jnskend . "' and tanggalberlaku = (SELECT MAX(`tanggalberlaku`) FROM `kebun_5hargaangkut` where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jnskend . "' and tanggalberlaku <= '" . $tanggal . "' and nospk='" . $spk . "') and nospk='" . $spk . "'";
				if (count(fetchdata($str)) == 0) {
					$str = "select * from " . $dbname . ".kebun_5hargaangkut where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jnskend . "' and tanggalberlaku = (SELECT MAX(`tanggalberlaku`) FROM `kebun_5hargaangkut` where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jnskend . "' and tanggalberlaku <= '" . $tanggal . "' and nospk='') and nospk=''";
					if (count(fetchdata($str)) == 0) {
						exit("Warningsystem : Harga blok " . $blok . " - " . getIndukBlok($blok) . ",<br>PKS Tujuan " . $pkstujuan . ",<br>Jenis Kendaraan " . $jnskend . ",<br>Jenis " . $tujuan . "<br>belum ada, silahkan di tambah melalui menu : Kebun - Setup - Harga Loading dan Angkut TBS");
					}
				}
			}
		}

		$res = fetchdata($str);
		foreach ($res as $bar) {
			if ($bar['tujuan'] == $tujuan) {
				if ($bar['posting'] != '1') {
					exit("Warningsystem : Harga belum disetujui.");
				}
				if ($bar['postingadd'] != '1') {
					exit("Warningsystem : 1. Harga tambahan belum disetujui.");
				}
				if ($bar['jenis'] == 'muat') {
					$rpmuat = $bar['harga'];
					$kegmuat = $bar['kodekeg'];
				}
				if ($bar['jenis'] == 'angkut') {
					$rpangkut = $bar['harga'];
					$kegangkut = $bar['kodekeg'];
				}

				$sql = "select * from " . $dbname . ".kebun_5hargaangkut_additional where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jnskend . "' and tanggalberlaku='" . $bar['tanggalberlaku'] . "' and tglawal<='" . $tanggal . "' and tglakhir>='" . $tanggal . "' and nospk='" . $spk . "'";
				if (count(fetchdata($sql)) == 0) {
					$sql = "select * from " . $dbname . ".kebun_5hargaangkut_additional where blok = '" . $blok . "' and pkstujuan='" . $pkstujuan . "' and jenisvhc='" . $jnskend . "' and tanggalberlaku='" . $bar['tanggalberlaku'] . "' and tglawal<='" . $tanggal . "' and tglakhir>='" . $tanggal . "' and nospk=''";
				}
				$req = fetchdata($sql);
				foreach ($req as $val) {
					if ($val['posting'] != '1') {
						exit("Warningsystem : 2. Harga tambahan belum disetujui.");
					}

					if ($val['tujuan'] == $tujuan) {
						if ($val['jenis'] == 'muat') {
							$addrpmuat = $val['harga'];
						}
						if ($val['jenis'] == 'angkut') {
							$addrpangkut = $val['harga'];
						}
					}
				}
			}
		}

		$str = "select * from " . $dbname . ".setup_kegiatan where kodekegiatan in ('" . $kegmuat . "','" . $kegangkut . "')";
		$jlh = fetchdata($str);
		if (count($jlh) < 1 and $tujuan != '') {
			// exit("Warningsystem : Kode kegiatan : ".$kegmuat." tidak terdaftar di Setup - Kegiatan");
			exit("Warningsystem : Upah " . ucfirst($bar['jenis']) . " " . strtoupper($tujuan) . " di Blok " . getIndukBlok($blok) . " belum disetupkan di menu KONTRAK > SETUP > HARGA LOADING DAN ANGKUT TBS.");
		}

		echo $rpmuat . "##" . $rpangkut . "##" . $kegmuat . "##" . $kegangkut . "##" . number_format($addrpmuat, 1) . "##" . number_format($addrpangkut, 1);
		break;
	case 'getformnospb':
		$tab = "<table>
				<tr>
					<td>Tiket Timbang</td>
					<td>:</td>
					<td><input type=text id=tiketcrx style=width:145px class=myinputtext></td>
					
					<td>" . $_SESSION['lang']['nospb'] . "</td>
					<td>:</td>
					<td><input type=text id=nospbcrx style=width:145px class=myinputtext></td>
					
					<td>" . $_SESSION['lang']['nopol'] . "</td>
					<td>:</td>
					<td><input type=text id=nopolcrx style=width:145px class=myinputtext></td>
				</tr>
				<tr>	
					<td>" . $_SESSION['lang']['unit'] . "</td>
					<td>:</td>
					<td><input type=text id=unitcrx style=width:145px class=myinputtext></td>
					
					<td hidden>Tanggal</td>
					<td hidden>:</td>
					<td hidden><input type=text readonly=readonly  class=myinputtext style='width:145px;' id=tanggalcr onmousemove=setCalendar(this.id); onblur=return false; maxlength=10></td>
					
					<td>" . $_SESSION['lang']['sopir'] . "</td>
					<td>:</td>
					<td><input type=text id=sopircrx style=width:145px class=myinputtext></td>
				</tr>
				<tr>					
					<td align=center colspan='2' style=height:25px></td>
					<td align=left colspan='2' style=height:25px>
						<button onclick=\"getnospb()\" class=mybutton>" . $_SESSION['lang']['preview'] . "</button>
					</td>
				</tr>
				
			</table>
			";

        
        $tab.="<table class='sortable' cellspacing=1 cellpadding=3 border=0 width=100%>";
        $tab.="<thead><tr class=rowheader>";
        $tab.="<th align=center>".$_SESSION['lang']['nomor']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['nospb']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['nopol']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['sopir']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['noTiket']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
        $tab.="<th align=center>Induk ".$_SESSION['lang']['blok']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['kodeblok']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['blok']."</th>";
        $tab.="<th align=center>".$_SESSION['lang']['berat']."</th>";
        $tab.="<th align=center width=30px>Action<br><input type=checkbox id=clickall onchange=clickall()></th>";
        $tab.="</tr></thead>";
        $tab.="
			<tbody id=formnospb></tbody>
		</table>";
		echo $tab;
		break;
	case 'getnopol':
		$str = "select * from " . $dbname . ".log_spknopol where notransaksi='" . $spk . "' and status='A'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$no++;
		}

		$per = ceil($no / 4);
		$no = $urut = 0;
		echo "<fieldset style=float:left>
			<legend>Nopol Terdaftar:</legend>";
		foreach ($res as $bar) {
			$no++;
			$urut++;
			if ($no == $per) {
				$no = 0;
				echo "<span class='badge badge-info badge-smaller'>" . $urut . ". " . $bar['nopol'] . " " . $bar['supir'] . "</span><br><br>";
			} else {
				echo "<span class='badge badge-info badge-smaller'>" . $urut . ". " . $bar['nopol'] . " " . $bar['supir'] . "</span>&nbsp;";
			}
		}
		echo "</fieldset>";

		break;
	case 'getnospb':
		$optjns = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$sql = "SELECT * FROM " . $dbname . ".vhc_5jenisvhc";
		$res = fetchdata($sql);
		foreach ($res as $bar) {
			$optnamajns[$bar['jenisvhc']] = $bar['namajenisvhc'];
		}
		$optnamajns['GLOBAL'] = "GLOBAL";
		$sql = "SELECT distinct jenisvhc  FROM " . $dbname . ".kebun_5hargaangkut where blok like '" . $kodeorg . "%'";
		$res = fetchdata($sql);
		foreach ($res as $bar) {
			if ($bar['jenisvhc'] == 'GLOBAL') {
				$optjns .= "<option value=" . $bar['jenisvhc'] . " selected>" . $bar['jenisvhc'] . " - " . $optnamajns[$bar['jenisvhc']] . "</option>";
			} else {
				$optjns .= "<option value=" . $bar['jenisvhc'] . ">" . $bar['jenisvhc'] . " - " . $optnamajns[$bar['jenisvhc']] . "</option>";
			}
		}

		#ambil nopol dari kontrakcr
		$str = "select * from " . $dbname . ".log_spknopol where notransaksi='" . $spk . "' and status='A'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nopol[strtoupper(str_replace(' ', '', $bar['nopol']))] = strtoupper(str_replace(' ', '', $bar['nopol']));
		}
		if (count($res) == 0) {
			exit("Warningsystem : Tidak ada nomor polisi / nomor kendaraan terdaftar atas SPK nomor : " . $spk . "");
		}

		$where = $where2="";
		if ($param['tiket'] != '') {
			$where2 .= " and notiket like '%" . $param['tiket'] . "%'";
		}
		if ($param['nospb'] != '') {
			$where2 .= " and nospb like '%" . $param['nospb'] . "%'";
		}
		if ($param['unit'] != '') {
			$where2 .= " and kodeorg like '%" . $param['unit'] . "%'";
		}
		if ($param['tanggal'] != '') {
			$where2 .= " and tanggal = '" . tanggalsystemn($param['tanggal']) . "'";
		}

		if ($param['nopol'] != '') {
			$where2 .= " and lower(REPLACE(nokendaraan,' ','')) like '%" . strtoupper(str_replace(' ', '', $param['nopol'])) . "%'";
		}
		if ($param['sopir'] != '') {
			$where2 .= " and supir like '%" . $param['sopir'] . "%'";
		}

		$tglbl = tglbulanlalu($tanggal1);

		$where2 .= " and lower(REPLACE(nokendaraan,' ','')) in ('" . implode("','", $nopol) . "')";

        //Laporan timbang per blok
    $tglTemp='';
    $sData = "select distinct * from ".$dbname.".kebun_spb_vw4 vw 
				where tanggal between '" . $tglbl . "' and '" . $tanggal2 . "' ".$where2."
				and posting='1' 
				and penerimatbs in (select kodeorganisasi from ".$dbname.".organisasi where tipe ='PABRIK') 
				and (indukblok not in (select blok from " . $dbname . ".kebun_rekapangkutantbsdt where nospb = vw.nospb))  
				order by tanggal,nospb,blok,tanggalpanen asc";
	$qData = $owlPDO->query($sData) or die(print " Gagal: ".PDOException::getMessage());
	$qData->setFetchMode(PDO::FETCH_ASSOC);
	$rowDta = owlBaris($qData);
	
	if ($rowDta > 0) {
		$totJjg = $totKgwb = $totLuasprd = $totJmlh = 0;
		$afdC = false; $blankC = false;
		$tglTemp = ''; $nospbatas = '';
		$dtNo = 0; $no = 0;
		$subTotKgwb = 0; // <-- variabel subtotal per nospb
        $subtotperkbn=array();
		while ($rData = $qData->fetch()) {
			// Jika ganti nospb, tampilkan total sebelumnya
			if ($nospbatas != '' && $nospbatas != $rData['nospb']) {
				$tab .= "<tr class=rowcontent style='font-weight:bold; background-color:#eef;'>
							<td colspan='8' align='right'>Total NO SPB " . $nospbatas . "</td>
                            <td colspan=2 align=center>(";
                            foreach ($subtotperkbn[$nospbatas] as $kbn => $kg) {
                                @$noo++;
                                if(count($subtotperkbn[$nospbatas])>1 && $noo != count($subtotperkbn[$nospbatas])){
                                    $tab.=$kbn." = ".number_format($kg,2)." + ";
                                }else{
                                    $tab.=$kbn." = ".number_format($kg,2);
                                }
                            }
                            $tab.=")</td> 
                            <td align='right'>" . number_format($subTotKgwb, 2) . "</td>
                            <td align='right'></td>
						</tr>";
				$subTotKgwb = 0; // reset subtotal
                $subtotperkbn[$rData['nospb']][substr($rData['indukblok'],0,4)] =0;
			}

			// ambil jumlah baris per nospb untuk rowspan
			if ($nospbatas != $rData['nospb']) {
				$jumlahBarisdt = 0;
				$sEspebe = "select count(*) as jumlahbarisdt from ".$dbname.".kebun_spbdt_detail 
							where nospb='".$rData['nospb']."'  
							and (indukblok not in (select blok from " . $dbname . ".kebun_rekapangkutantbsdt where nospb = kebun_spbdt_detail.nospb))";
				$qEspebe = $owlPDO->query($sEspebe) or die(print " Gagal: ".PDOException::getMessage());
				$qEspebe->setFetchMode(PDO::FETCH_ASSOC);
				$rEspebe = $qEspebe->fetch();
				$jumlahBarisdt = $rEspebe['jumlahbarisdt'];
				$dtNo++;
			}

			$no++;
			$totJjg += $rData['jjg'];
			$totBrd += $rData['brondolan'];
			$totKgwb += $rData['kgwb'];
			$subTotKgwb += $rData['kgwb']; // tambah subtotal per nospb
            $subtotperkbn[$rData['nospb']][substr($rData['indukblok'],0,4)] +=$rData['kgwb'];
			if ($rData['tanggal'] != $tglTemp) {
				$afdC = false;
				$tglTemp = $rData['tanggal'];
			}

			$tab .= "<tr class=rowcontent>";
			if ($nospbatas != $rData['nospb']) {    
				$tab .= "<td align=center rowspan='".$jumlahBarisdt."' valign='top'>".$dtNo."</td>";
				if ($proses=='excel') {
					$tab .= "<td rowspan='".$jumlahBarisdt."' valign='top'>".$rData['tanggal']."</td>";
				} else {
					$tab .= "<td rowspan='".$jumlahBarisdt."' valign='top'>".tanggalnormal($rData['tanggal'])."</td>";
				}

				$tab .= "<td rowspan='".$jumlahBarisdt."' valign='top'>".$rData['nospb']."</td>";
				$tab .= "<td rowspan='".$jumlahBarisdt."' valign='top'>".$rData['nokendaraan']."</td>";
				$tab .= "<td rowspan='".$jumlahBarisdt."' valign='top'>".$rData['supir']."</td>";
				$tab .= "<td rowspan='".$jumlahBarisdt."' valign='top' id=tiket_" . $no . " name=tiket[]>".$rData['notiket']."</td>";
			}

			$tab .= "<td>".tanggalnormal($rData['tanggalpanen'])."</td>";
			$tab .= "<td id=blokbesar_" . $no . " name=blokbesar[]>".$rData['indukblok']."</td>";
			$tab .= "<td>".$rData['blok']."</td>";
			$tab .= "<td>".getNamaOrg($rData['blok'])."</td>";
			$tab .= "<td align=right>".number_format($rData['kgwb'],2)."</td>";
			$tab .= "<td align=center>
						<input hidden name=cekharga[] value=''>
						<input type=checkbox id=click" . $no . " name=click[] onclick=\"hitungclick()\">
						<input hidden type=text id=nospb_" . $no . " name=nospb[] value=".$rData['nospb'].">
					</td>";
			$tab .= "<td align=center hidden><select style=\"width:150px;\" onchange=getjnskendall(" . $no . "); id=jnskendcr_" . $no . " name=jnskendcr[]>" . $optjns . "</select></td>";
			$tab .= "</tr>";

			$nospbatas = $rData['nospb'];   
		}

		// tampilkan total untuk nospb terakhir
		$tab .= "<tr class=rowcontent style='font-weight:bold; background-color:#eef;'>
					<td colspan='8' align='right'>Total NO SPB " . $nospbatas . "</td>
					<td colspan=2 align=center>(";
                    foreach ($subtotperkbn[$nospbatas] as $kbn => $kg) {
                        @$noo++;
                        if(count($subtotperkbn[$nospbatas])>1 && $noo != count($subtotperkbn[$nospbatas])){
                            $tab.=$kbn." = ".number_format($kg,2)." + ";
                        }else{
                            $tab.=$kbn." = ".number_format($kg,2);
                        }
                    }
                    $tab.=")</td> 
					<td align='right'>" . number_format($subTotKgwb, 2) . "</td>
					<td align='right'></td>
				</tr>";

	} else {
		$tab .= "<tr class=rowcontent><td colspan=12 align=center><b>".$_SESSION['lang']['dataempty']."</b></td></tr>";
	}

	// Tombol tambah di akhir
	$tab .= "<tr class=rowcontent>";
	$tab .= "<td align=right colspan=12>
			<button class=mybutton onclick=\"getdetailspb('" . $no . "')\">" . $_SESSION['lang']['tambah'] . "</button></td>";
	$tab .= "</tr>";


	echo $tab;
	break;
	case 'getdetailspb':
		$sql = "SELECT * FROM " . $dbname . ".vhc_5jenisvhc";
		$res = fetchdata($sql);
		foreach ($res as $bar) {
			$optnamajns[$bar['jenisvhc']] = $bar['namajenisvhc'];
		}
		$optnamajns['GLOBAL'] = "GLOBAL";


		$opttujuan = "<option value=''></option>";
		foreach ($arrmuat as $val => $key) {
			if (isset($param['jenisangkt']) == $val) {
				$opttujuan .= "<option value=" . $val . " selected>" . $key . "</option>";
			} else {
				$opttujuan .= "<option value=" . $val . ">" . $key . "</option>";
			}
		}
		if (!($param['blokbesar'])) {
			exit("Warningsystem : Silahkan check salah satu nomor SPB.");
		}
		$wh = $whr = "";

		if (isset($param['jenisangkt']) == 'undefined') {
			$whr .= " and nospb not in (select nospb from " . $dbname . ".kebun_rekapangkutantbsdt)";
		}

		foreach ($param['nospb'] as $key => $spb) {
			$nmspb[$spb] = $spb;
		}
		$wh .= " and nospb in ('" . implode("','", $nmspb) . "')";
		foreach ($param['blokbesar'] as $key => $blk) {
			$nmblok[$blk] = $blk;
		}
		$wh .= " and blok in ('" . implode("','", $nmblok) . "')";

		$str = "select tanggal,sum(kgwb) as kgwb,sum(kgwbnetto) as kgwbnetto,sum(brondolan) as brondolan,sum(totalkg) as totalkg,sum(kgbjr) as kgbjr,nospb,blok,kodeorg,notiket,nokendaraan,penerimatbs,kontanan,divisi,tahuntanamspbht,tahuntanamwb from " . $dbname . ".kebun_spb_vw2 where 1=1 " . $wh . " " . $whr . " and posting='1'  group by nospb, blok";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$no = '';
		$tab = "";
		$nourutspb = 0;
		$tempnourutspb = 0;
		while ($bar = $res->fetch()) {
			if ($tempnourutspb == $bar['nospb']) {
			} else {
				$nourutspb++;
			}
			$tempnourutspb = $bar['nospb'];

			$a = $nourutspb % 2;
			$xx = "";
			if ($a == 0) {
				$xx .= " style=background-color:#b1e3ea";
			}

			$jeniskendnya = 'GLOBAL'; //Karena kebanyakan untuk semua alat jadi dibuat gtu
			$optjns = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
			$sql = "SELECT distinct jenisvhc  FROM " . $dbname . ".kebun_5hargaangkut where blok like '" . $kodeorg . "%'";
			$req = fetchdata($sql);
			foreach ($req as $val) {
				// if($param['jeniskend'][$bar['notiket']]==$val['jenisvhc']){dikomen dulu biar cepet karena lagi presentasi
				if ($jeniskendnya == $val['jenisvhc']) {
					$optjns .= "<option value=" . $val['jenisvhc'] . " selected>" . $val['jenisvhc'] . " - " . $optnamajns[$val['jenisvhc']] . "</option>";
					$jeniskendnya = $val['jenisvhc'];
				} else {
					$optjns .= "<option value=" . $val['jenisvhc'] . ">" . $val['jenisvhc'] . " - " . $optnamajns[$val['jenisvhc']] . "</option>";
				}
			}


			$no++;
			$optdriver = makeOption($dbname, 'pabrik_timbangan', 'notransaksi,supir', "notransaksi='" . $bar['notiket'] . "'");
			$driver = $optdriver[$bar['notiket']];
			if ($nospb != 'ALL') {
				$notiket = $bar['notiket'];
				$driver = $optdriver[$bar['notiket']];
				$divisi = $bar['divisi'];
				$tanggal = $bar['tanggal'];
				$nopol = $bar['nokendaraan'];
				@$jjg += $bar['jjg'];
				@$kgwb += $bar['kgwb'];
				@$kgwbnetto += $bar['kgwbnetto'];
			} elseif ($nospb = 'ALL') {
				$notiket = $driver = $divisi = $nopol = $jjg = $kgwb = $_SESSION['lang']['all'];
				$tanggal = '00-00-0000';
			}

			$tab .= "<tr class=rowcontent " . $xx . " id=tr_" . $no . ">
				<td valign=top align=center>" . $no . "</select>
				<td nowrap valign=top id=nospb_" . $no . " align=center>" . $bar['nospb'] . "</select>
				<td nowrap valign=top align=center>" . $bar['nokendaraan'] . "</select>
				<td nowrap valign=top align=left>" . $driver . "</select>
				<td nowrap valign=top align=center>" . tglnmbln($tanggal, 'I', 'long') . "</select>
				<td nowrap valign=top hidden><input type=text class=myinputtext disabled id=blok_" . $no . " style=\"width:70px;\" value='" . $bar['blok'] . "'></td>
				<td valign=top>" . getIndukBlok($bar['blok']) . "</td>
				<td valign=top align=center style=\"width:35px;\"><input class=myinputtextnumber id=thntnm_" . $no . " disabled  style=\"width:40px;\" value='" . ($bar['tahuntanamspbht'] != $bar['tahuntanamwb'] ? $bar['tahuntanamwb'] : $bar['tahuntanamspbht']) . "'></td>
				<td valign=top><input class=myinputtext id=pkstujuan_" . $no . " disabled  style=\"width:40px;\" value='" . $bar['penerimatbs'] . "'></td>
				
				<td valign=top>
					<select style=\"width:70px;\" onchange=getharga('" . $no . "','','" . $bar['nospb'] . "'); id=jnskend_" . $no . ">" . $optjns . "</select>
				</td>
				
				<td valign=top><select style=\"width:80px;\" name=" . $bar['nospb'] . "[] onchange=getharga('" . $no . "','','" . $bar['nospb'] . "'); id=tujuan_" . $no . ">" . @$opttujuan . "</select></td>
				
				<td valign=top><input id=kgwbpks_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:45px;\" value='" . ($bar['totalkg'] == 0 ? 0 : $bar['totalkg']) . "'></td>
				
				<td valign=top><input id=kgbrd_" . $no . "  onkeyup=hitungrupiah('brd','" . $no . "'); class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:60px;\" value='0'></td>
				
				<td valign=top><input id=kgwb_" . $no . " onblur=hitungrupiah('kg','" . $no . "'); class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:50px;\" value='" . ($bar['totalkg'] == 0 ? 0 : $bar['totalkg']) . "'></td>
				
				<td valign=top>
					<input hidden id=kegmuat_" . $no . " value=" . $kegmuat . ">
					<input id=harga_muat_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_muat_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_muat2_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_muat2_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_muat3_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_muat3_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_muat4_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_muat4_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_muat5_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_muat5_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_muat6_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_muat6_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_muat7_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_muat7_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				
				<td valign=top hidden><input id=addharga_muat_" . $no . "  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\"></td>
				<td valign=top><input id=addrp_muat_" . $no . "  onkeyup=gettotal(" . $no . "); class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\"></td>
				
				<td valign=top>
					<input hidden id=kegangkut_" . $no . " value=" . $kegangkut . ">
					<input id=harga_angkut_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_angkut_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_angkut2_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_angkut2_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_angkut3_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_angkut3_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_angkut4_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_angkut4_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_angkut5_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_angkut5_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_angkut6_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_angkut6_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				<td valign=top>
					<input id=harga_angkut7_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:65px;\">
				</td>
				<td valign=top>
					<input id=rp_angkut7_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\">
				</td>
				
				
				<td valign=top hidden><input id=addharga_angkut_" . $no . "  class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:35px;\"></td>
				<td valign=top><input id=addrp_angkut_" . $no . "  onkeyup=gettotal(" . $no . "); class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:70px;\"></td>
				
				<td valign=top><input id=potonganrp_" . $no . " onkeyup=gettotal(" . $no . "); class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:55px;\"></td>
				
				
				<td valign=top><input id=ttlrp_" . $no . " disabled class=myinputtextnumber onkeypress=\"return angka_doang(event);\"  style=\"width:60px;\"></td>";
			$tab .= "<td valign=top align=center><img title='Simpan' class='zImgBtn' onclick=\"savedetail('" . $no . "')\" src='images/save.png'></td>
				
				<input type=hidden id=method value='insert'>
			</tr>
			";
			$nospbold = $bar['nospb'];
			$nmblk[$bar['blok']] = $bar['blok'];
		}
		$tab .= "<tr class=rowcontent style=font-weight:bold>
			<td colspan=46 align=left>
				<button id=tomboldetail class=mybutton onclick=\"loaddatadetail()\" >Refresh</button>
				<button id=tomboldetail class=mybutton onclick=\"saveAll('" . $no . "')\" >" . $_SESSION['lang']['saveall'] . "</button>
				<button id=tomboldetail class=mybutton onclick=\"detail()\" >" . $_SESSION['lang']['cancel'] . "</button>
			</td>
			</tr>";
		$tab .= "<input hidden id=jumlahrow value=" . $no . ">";

		echo $notiket . "##" . $driver . "##" . $divisi . "##" . tanggalnormal($tanggal) . "##" . $nopol . "##" . $jjg . "##" . $kgwb . "##" . $tab . "##" . count(fetchData($str)) . "##" . $nospbold . "##" . implode(",", $nmblk); #exit("error");
		break;
	case 'detail':
		$sql = "select * from " . $dbname . ".kebun_rekapangkutantbsht where kodeorg = '" . $kodeorg . "' and periode = '" . $periode . "' and tgldari='" . $tglmulai . "' and tglsampai='" . $tglselesai . "' and posting='1' and periodebyr = '" . $periodebyr . "' and spk='" . $spk . "'";
		if (count(fetchdata($sql)) > 0) {
			exit("Warningsystem : Transaksi untuk periode : " . $periode . " sudah diposting !");
		}

		#ambil nopol dari kontrakcr
		$str = "select * from " . $dbname . ".log_spknopol where notransaksi='" . $spk . "'";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			$nopol[$bar['nopol']] = $bar['nopol'];
		}
		if (count($res) == 0) {
			exit("Warningsystem : Tidak ada nomor polisi / nomor kendaraan terdaftar atas SPK nomor : " . $spk . "");
		}

		$optspb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$where = " and tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "'";
		$where .= " and nospb in (select nospb from " . $dbname . ".pabrik_timbangan where nokendaraan in ('" . implode("','", $nopol) . "'))";


		$sql = "select * from " . $dbname . ".kebun_spbht where kodeorg = '" . $kodeorg . "' and tanggal like '" . $periode . "%' and posting='1' and nospb not in (select nospb from " . $dbname . ".kebun_rekapangkutantbsht) " . $where . "";
		$res = fetchdata($sql);
		if (count($res) > 0) {
			$optspb .= "<option value='ALL'>" . strtoupper($_SESSION['lang']['all']) . "</option>";
		}
		foreach ($res as $bar) {
			$optdriver = makeOption($dbname, 'pabrik_timbangan', 'nospb,supir', "nospb='" . $bar['nospb'] . "'");
			$optnopol = makeOption($dbname, 'pabrik_timbangan', 'nospb,nokendaraan', "nospb='" . $bar['nospb'] . "'");
			$optspb .= "<option value=" . $bar['nospb'] . ">" . $bar['nospb'] . " - " . $optdriver[$bar['nospb']] . " - " . $optnopol[$bar['nospb']] . "</option>";
		}

		OPEN_BOX();
		echo "
			<table style=display:none>
				<tr>
					<td>" . $_SESSION['lang']['nospb'] . "</td>
					<td>:</td>
					<td></td>
					
					<td hidden>" . $_SESSION['lang']['divisi'] . "</td>
					<td hidden>:</td>
					<td hidden><input type=text class=myinputtext disabled id=divisi style=\"width:100px;\" />
					</td>
					
					<td hidden>" . $_SESSION['lang']['janjang'] . "</td>
					<td hidden>:</td>
					<td hidden><input type=text class=myinputtextnumber disabled id=jjg style=\"width:100px;\" />
					</td>
					
				</tr>
				<tr hidden>
					<td>No Tiket</td>
					<td>:</td>
					<td><input type=text class=myinputtext disabled id=notiket style=\"width:145px;\" />
					</td>
					
					<td>" . $_SESSION['lang']['tanggal'] . "</td>
					<td>:</td>
					<td><input type=text class=myinputtext disabled id=tanggal style=\"width:100px;\" />
					</td>
					
					<td>" . $_SESSION['lang']['kgwb'] . " " . $_SESSION['lang']['kebun'] . "</td>
					<td>:</td>
					<td><input type=text  class=myinputtextnumber disabled id=kgwbkebun style=\"width:100px;\" />
					</td>
					
				</tr>
				
				<tr hidden>
					<td>" . $_SESSION['lang']['sopir'] . "</td>
					<td>:</td>
					<td><input type=text class=myinputtext disabled id=sopir style=\"width:145px;\" />
					</td>
					
					<td>" . $_SESSION['lang']['nopol'] . "</td>
					<td>:</td>
					<td><input type=text class=myinputtext  disabled id=nopol style=\"width:100px;\" />
					</td>
					
					
					<td>" . $_SESSION['lang']['kgwb'] . " Pabrik</td>
					<td>:</td>
					<td><input type=text  class=myinputtextnumber disabled id=kgwb style=\"width:100px;\" />
					</td>
					
					
				</tr>
				
		</table>
		<div class=table-scroll>
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <th align=center rowspan='3' width=30px>No</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['nospb'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['nopol'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['sopir'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['tanggal'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['blok'] . "</th>
            <th align=center rowspan='3' width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
            <th align=center rowspan='3'>PKS<br>Tujuan</th>
            <th align=center rowspan='3'>Jenis Kend</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['jenis'] . "</th>
            <th align=center rowspan='3' width=50px>" . $_SESSION['lang']['notifbrutto'] . "</th>
            <th align=center rowspan='3' title=\"Brondolan tidak dikutip\">Potongan<br>Brondolan<br>Kg</th>
            <th align=center rowspan='3' width=50px>" . $_SESSION['lang']['notifbrutto'] . "</th>
            <th align=center colspan='" . (1 + (2 * count($arrmuat))) . "'>Muat</th>
            <th align=center colspan='" . (1 + (2 * count($arrmuat))) . "'>Angkut</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['potongan'] . "<br>Rp</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['total'] . "</th>
            <th align=center rowspan='3'>" . $_SESSION['lang']['action'] . "</th>
        </tr>
		<tr>";
		foreach ($arrmuat as $key => $v) {
			echo "<th align=center colspan=2>" . $v . "</th>";
		}
		echo "
            <th align=center colspan=1>Tambahan</th>";
		foreach ($arrmuat as $key => $v) {
			echo "<th align=center colspan=2>" . $v . "</th>";
		}
		echo "
            <th align=center colspan=1>Tambahan</th>
        </tr>
        <tr>";
		for ($i = 1; $i <= ((count($arrmuat))); $i++) {
			# code...
			echo "
				<th align=center>" . $_SESSION['lang']['harga'] . "</th>
				<th align=center>" . $_SESSION['lang']['rp'] . "</th>";
		}
		echo "<th align=center>" . $_SESSION['lang']['rp'] . "</th>";
		for ($i = 1; $i <= ((count($arrmuat))); $i++) {
			# code...
			echo "
				<th align=center>" . $_SESSION['lang']['harga'] . "</th>
				<th align=center>" . $_SESSION['lang']['rp'] . "</th>";
		}
		echo "<th align=center>" . $_SESSION['lang']['rp'] . "</th>";
		echo "
        </tr></thead>
		<thead>
			<tr class='rowheader' style=height:25px>
			<th align=center style='color:blue;font-size:18px;font-weigh:bold;width:100%;cursor:pointer;background-color:#AEB6BF;height:30px' colspan=46>
				<button id=tomboldetail style=height:35px class=mybutton onclick=\"getformnospb()\" ><b>CLICK DISINI UNTUK TAMBAH DATA NO SPB</b></button>
			</th>
		</tr>
		</thead>	
		<tbody id=inputharga> 
			<tr class=rowcontent style=font-weight:bold>
				<td colspan=46 align=right>
					<button id=tomboldetail class=mybutton onclick=\"loaddatadetail()\" >Refresh</button>
					<button id=tomboldetail class=mybutton onclick=\"saveAll('" . $no . "')\" >" . $_SESSION['lang']['saveall'] . "</button>
					<button id=tomboldetail class=mybutton onclick=\"detail()\" >" . $_SESSION['lang']['cancel'] . "</button>
				</td>
			</tr>		
		</tbody>
        </table></div>
        ";
		CLOSE_BOX();
		OPEN_BOX();
		echo "
        
			<div id='loaddatadetail' class='table-scroll'>
				<script>loaddatadetail()</script>
			</div>
			";
		CLOSE_BOX();
		break;

	case 'detailold':
		$sql = "select * from " . $dbname . ".kebun_rekapangkutantbsht where kodeorg = '" . $kodeorg . "' and periode = '" . $periode . "' and posting='1' and periodebyr = '" . $periodebyr . "' and spk='" . $spk . "'";
		if (count(fetchdata($sql)) > 0) {
			exit("Warningsystem : Transaksi untuk periode : " . $periode . " sudah diposting !");
		}

		$optspb = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$optspb .= "<option value='ALL'>" . strtoupper($_SESSION['lang']['all']) . "</option>";
		$where = " and tanggal between '" . $tanggal1 . "' and '" . $tanggal2 . "'";

		$sql = "select * from " . $dbname . ".kebun_spbht where kodeorg = '" . $kodeorg . "' and tanggal like '" . $periode . "%' and posting='1' and nospb not in (select nospb from " . $dbname . ".kebun_rekapangkutantbsht) " . $where . "";
		#exit("error".$sql);
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$optdriver = makeOption($dbname, 'pabrik_timbangan', 'nospb,supir', "nospb='" . $bar['nospb'] . "'");
			$optspb .= "<option value=" . $bar['nospb'] . ">" . $bar['nospb'] . " - " . $optdriver[$bar['nospb']] . "</option>";
		}

		$optjns = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		$optjns .= "<option value='GLOBAL' selected>GLOBAL</option>";
		$sql = "SELECT * FROM " . $dbname . ".vhc_5jenisvhc";
		$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$qry->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $qry->fetch()) {
			$optnamajns[$bar['jenisvhc']] = $bar['namajenisvhc'];
		}
		$sql = "SELECT distinct jenisvhc  FROM " . $dbname . ".kebun_5hargaangkut where blok like '" . $kodeorg . "%'";
		$res = fetchdata($sql);
		foreach ($res as $bar) {
			$optjns .= "<option value=" . $bar['jenisvhc'] . ">" . $bar['jenisvhc'] . " - " . $optnamajns[$bar['jenisvhc']] . "</option>";
		}

		OPEN_BOX();
		echo "
        <fieldset>
			<legend>Cari SPB</legend>
			<table>
				<tr>
					<td style=\"width:100px;\">" . $_SESSION['lang']['nospb'] . "</td>
					<td>:</td>
					<td><select style=\"width:150px;\" onchange=getdetailspb(); id=nospb>" . $optspb . "</select>
						<img title=Refresh style=vertical-align:center;width:10px;height:10px;cursor:pointer onclick=detail() src=images/refresh2.png>
						<img id='nospb' onclick=z.elSearch('nospb',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
					</td>
					
					<td>Jenis Kend</td>
					<td>:</td>
					<td><select style=\"width:150px;\" onchange=getdetailspb(); id=jeniskend>" . $optjns . "</select>
					</td>
					
					<td>" . $_SESSION['lang']['divisi'] . "</td>
					<td>:</td>
					<td><input type=text class=myinputtext disabled id=divisi style=\"width:60px;\" />
					</td>
					
					
					
				</tr>
				
				<tr>
					<td>No Tiket</td>
					<td>:</td>
					<td><input type=text class=myinputtext disabled id=notiket style=\"width:145px;\" />
					</td>
					
					<td>" . $_SESSION['lang']['tanggal'] . "</td>
					<td>:</td>
					<td><input type=text class=myinputtext disabled id=tanggal style=\"width:145px;\" />
					</td>
					
					<td>" . $_SESSION['lang']['janjang'] . "</td>
					<td>:</td>
					<td><input type=text class=myinputtextnumber disabled id=jjg style=\"width:60px;\" />
					</td>
					
				</tr>
				
				<tr>
					<td>" . $_SESSION['lang']['sopir'] . "</td>
					<td>:</td>
					<td><input type=text class=myinputtext disabled id=sopir style=\"width:145px;\" />
					</td>
					
					<td>" . $_SESSION['lang']['nopol'] . "</td>
					<td>:</td>
					<td><input type=text class=myinputtext  disabled id=nopol style=\"width:145px;\" />
					</td>
					
					
					<td>" . $_SESSION['lang']['kgwb'] . "</td>
					<td>:</td>
					<td><input type=text  class=myinputtextnumber disabled id=kgwb style=\"width:60px;\" />
					</td>
					
					
				</tr>
				
		</table>
		<hr>
        <table border=0 cellpadding=3 cellspacing=1 class=sortable>
        <thead><tr class=rowheader>
            <td align=center rowspan='2' width=30px>No</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nospb'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <td align=center rowspan='2' width=50px>" . $_SESSION['lang']['tahuntanam'] . "</td>
            <td align=center rowspan='2'>PKS<br>Tujuan</td>
            <td align=center rowspan='2'>Jenis Kend</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['jenis'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['notifbrutto'] . "</td>
            <td align=center rowspan='2' title=\"Brondolan tidak dikutip\">Potongan<br>Brondolan<br>Kg</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['notifbrutto'] . "</td>
            <td align=center colspan='2'>Muat</td>
            <td align=center colspan='2'>Angkut</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['potongan'] . "<br>Rp</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['total'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['action'] . "</td>
        </tr>
        <tr>
            <td align=center>" . $_SESSION['lang']['harga'] . "</td>
            <td align=center>" . $_SESSION['lang']['rp'] . "</td>
            <td align=center>" . $_SESSION['lang']['harga'] . "</td>
            <td align=center>" . $_SESSION['lang']['rp'] . "</td>
        </tr></thead>
		<tbody id=inputharga> 
			<tr class=rowcontent style=font-weight:bold>
				<td colspan=17 align=right>
					<button id=tomboldetail class=mybutton onclick=\"loaddatadetail()\" >Refresh</button>
					<button id=tomboldetail class=mybutton onclick=\"saveAll('" . $no . "')\" >" . $_SESSION['lang']['saveall'] . "</button>
					<button id=tomboldetail class=mybutton onclick=\"detail()\" >" . $_SESSION['lang']['cancel'] . "</button>
				</td>
			</tr>		
		</tbody>
        </table>
        </fieldset>";
		CLOSE_BOX();
		OPEN_BOX();
		echo "
			<div id='loaddatadetail' class='table-scroll'>
				<script>loaddatadetail()</script>
			</div>";
		CLOSE_BOX();
		break;
	case 'viewdetailx':
		if ($param['tipenya'] != 'html') {
			$brd = 1;
			$cell = 0;
		} else {
			$brd = 0;
			$cell = 1;
		}
		$tab = "<table border=" . $brd . " cellpadding=5 cellspacing=" . $cell . " class=sortable>
        <thead><tr class=rowheader>
            <th align=center>" . $_SESSION['lang']['nospb'] . "</th>
            <th align=center>" . $_SESSION['lang']['ticket'] . "</th>
            <th align=center>" . $_SESSION['lang']['kodeblok'] . "</th>
            <th align=center width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
            <th align=center>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['kegiatan'] . "</th>
            <th align=center>" . $_SESSION['lang']['jenis'] . "</th>
            <th align=center>" . $_SESSION['lang']['tujuan'] . "</th>
            <th align=center>" . $_SESSION['lang']['kg'] . " (Bruto)</th>
            <th align=center>Pokok (Rp)</th>
            <th align=center>Tambahan (Rp)</th>
            <th align=center>" . $_SESSION['lang']['harga'] . "</th>
            <th align=center>" . $_SESSION['lang']['rp'] . "</th>
        </tr>
        </thead>";
		$tab .= "<tbody>";
		//$oppotrp=makeOption($dbname,'kebun_rekapangkutantbsdt','nospb,potonganrp',"nospb='".$nospb."'");
		$no = $rowsp = 0;
		$bloktemp = $spbtempt = '';
		$str = "select * from " . $dbname . ".kebun_rekapangkutantbsdt a 
		left join " . $dbname . ".kebun_rekapangkutantbsht b on a.nospb=b.nospb where a.nospb = '" . $nospb . "' and nospk='".$spk."'";
		if (count(fetchData($str)) > 0) {
			foreach (fetchData($str) as $v) {
				$arrblok[$v['blok']] = $v['blok'];
			}
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				if ($bar['rupiah'] > 0) {
					$optkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='" . $bar['jeniskegiatan'] . "'");
					$notiket = makeOption($dbname, 'pabrik_timbangan', 'nospb,notransaksi', "nospb='" . $bar['nospb'] . "'");
					$qry = "select indukblok,kodeorg,tahuntanam from " . $dbname . ".setup_blok where kodeorg like '" . $bar['blok'] . "%'";
					$hsl = fetchdata($qry);
					foreach ($hsl as $v) {
						$tatan[$v['indukblok']][$v['tahuntanam']] = $v['tahuntanam'];
					}
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td align=center>" . $bar['nospb'] . "</td>";
					$tab .= "<td align=center>" . $notiket[$bar['nospb']] . "</td>";
					$tab .= "<td align=center>" . getIndukBlok($bar['blok']) . "</td>";
					$tab .= "<td align=center>";
					foreach ($tatan[$bar['blok']] as $v) {
						$tab .= $v . "<br>";
					}
					$tab .= "</td>";
					$tab .= "<td align=left>" . $bar['jeniskegiatan'] . " - " . $optkeg[$bar['jeniskegiatan']] . "</td>";
					$tab .= "<td align=center>" . strtoupper($bar['jenis']) . "</td>";
					$tab .= "<td align=center>" . $arrmuat[$bar['tujuan']] . "</td>";
					$tab .= "<td align=right>" . @number_format($bar['kgwb'], 2) . "</td>";
					$tab .= "<td align=right>" . @number_format($bar['rppokok'], 2) . "</td>";
					$tab .= "<td align=right>" . @number_format($bar['rpadd'], 2) . "</td>";
					$tab .= "<td align=right>" . @number_format($bar['rupiah'] / $bar['kgwb'], 2) . "</td>";
					$tab .= "<td align=right>" . @number_format($bar['rupiah'], 2) . "</td>";
					$tab .= "</tr>";
					$bloktemp = $bar['blok'];
					$spbtempt = $bar['nospb'];
					@$trppokok += $bar['rppokok'];
					@$trpadd += $bar['rpadd'];
					@$tluasplan += $bar['kgwb'];
					@$tluaspanen += $bar['rupiah'];
				}

				@$oppotrp[$bar['nospb']] += $bar['potonganrp'];
			}
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center colspan=11><b>" . $_SESSION['lang']['potongan'] . " Rupiah</td>";
			$tab .= "<td align=right><b>" . @number_format($oppotrp[$nospb], 2) . "</td>";
			$tab .= "</tr>";

			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center colspan=11><b>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</td>";
			$tab .= "<td align=right><b>" . @number_format($tluaspanen - $oppotrp[$nospb], 2) . "</td>";
			$tab .= "</tr>";
		} else {
			$tab .= "<tr class=rowcontent><td align=center colspan=12>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}
		$tab .= "</tbody>";
		$tab .= "</table>";
		if ($param['tipenya'] == 'html') {
			echo $tab;
		} else {
			$stream = $tab;
			$stream .= "</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
			$nop_ = "Rekap Angkutan TBS_PerSPB";
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
			}
		}
		break;
	case 'popupviewdetailx':
		if ($param['tipenya'] != 'html') {
			$brd = 1;
			$cell = 0;
		} else {
			$brd = 0;
			$cell = 1;
		}
		$tab = "<table border=" . $brd . " cellpadding=5 cellspacing=" . $cell . " class=sortable>
        <thead><tr class=rowheader>
            <th align=center>" . $_SESSION['lang']['nospb'] . "</th>
            <th align=center>" . $_SESSION['lang']['ticket'] . "</th>
            <th align=center>" . $_SESSION['lang']['kodeblok'] . "</th>
            <th align=center width=50px>" . $_SESSION['lang']['tahuntanam'] . "</th>
            <th align=center>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['kegiatan'] . "</th>
            <th align=center>" . $_SESSION['lang']['jenis'] . "</th>
            <th align=center>" . $_SESSION['lang']['tujuan'] . "</th>
            <th align=center>" . $_SESSION['lang']['kg'] . " (Bruto)</th>
            <th align=center>Pokok (Rp)</th>
            <th align=center>Tambahan (Rp)</th>
            <th align=center>" . $_SESSION['lang']['harga'] . "</th>
            <th align=center>" . $_SESSION['lang']['rp'] . "</th>
            <th align=center>" . $_SESSION['lang']['action'] . "</th>
        </tr>
        </thead>";
		$tab .= "<tbody>";
		//$oppotrp=makeOption($dbname,'kebun_rekapangkutantbsdt','nospb,potonganrp',"nospb='".$nospb."'");
		$no = $rowsp = 0;
		$bloktemp = $spbtempt = '';
		$str = "select * from " . $dbname . ".kebun_rekapangkutantbsdt a 
		left join " . $dbname . ".kebun_rekapangkutantbsht b on a.nospb=b.nospb and a.nospk=b.spk where a.nospb = '" . $nospb . "' and a.nospk='".$spk."' and b.periodebyr='".$periodebyr."'";
		if (count(fetchData($str)) > 0) {
			foreach (fetchData($str) as $v) {
				$arrblok[$v['blok']] = $v['blok'];
			}
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				if ($bar['rupiah'] > 0) {
					$optkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan', "kodekegiatan='" . $bar['jeniskegiatan'] . "'");
					$notiket = makeOption($dbname, 'pabrik_timbangan', 'nospb,notransaksi', "nospb='" . $bar['nospb'] . "'");
					$qry = "select indukblok,kodeorg,tahuntanam from " . $dbname . ".setup_blok where kodeorg like '" . $bar['blok'] . "%'";
					$hsl = fetchdata($qry);
					foreach ($hsl as $v) {
						$tatan[$v['indukblok']][$v['tahuntanam']] = $v['tahuntanam'];
					}
					$tab .= "<tr class=rowcontent>";
					$tab .= "<td align=center>" . $bar['nospb'] . "</td>";
					$tab .= "<td align=center>" . $notiket[$bar['nospb']] . "</td>";
					$tab .= "<td align=center>".$bar['blok']." - " . getIndukBlok($bar['blok']) . "</td>";
					$tab .= "<td align=center>";
					foreach ($tatan[$bar['blok']] as $v) {
						$tab .= $v . "<br>";
					}
					$tab .= "</td>";
					$tab .= "<td align=left>" . $bar['jeniskegiatan'] . " - " . $optkeg[$bar['jeniskegiatan']] . "</td>";
					$tab .= "<td align=center>" . strtoupper($bar['jenis']) . "</td>";
					$tab .= "<td align=center>" . $arrmuat[$bar['tujuan']] . "</td>";
					$tab .= "<td align=right>" . @number_format($bar['kgwb'], 2) . "</td>";
					$tab .= "<td align=right>" . @number_format($bar['rppokok'], 2) . "</td>";
					$tab .= "<td align=right>" . @number_format($bar['rpadd'], 2) . "</td>";
					$tab .= "<td align=right>" . @number_format($bar['rupiah'] / $bar['kgwb'], 2) . "</td>";
					$tab .= "<td align=right>" . @number_format($bar['rupiah'], 2) . "</td>";
					$tab .= "<td align=center width=25px>
					            <img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletedetail('" . $kodeorg . "','" . $periode . "','" . $nospb . "','".$spk."','".$periodebyr."','".$bar['blok']."','".$bar['kgwb']."');\" >
                            </td>";
					$tab .= "</tr>";
					$bloktemp = $bar['blok'];
					$spbtempt = $bar['nospb'];
					@$trppokok += $bar['rppokok'];
					@$trpadd += $bar['rpadd'];
					@$tluasplan += $bar['kgwb'];
					@$tluaspanen += $bar['rupiah'];
				}

				@$oppotrp[$bar['nospb']] += $bar['potonganrp'];
			}
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center colspan=11><b>" . $_SESSION['lang']['potongan'] . " Rupiah</td>";
			$tab .= "<td align=right><b>" . @number_format($oppotrp[$nospb], 2) . "</td>";
			$tab .= "<td align=right></td>";
			$tab .= "</tr>";

			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center colspan=11><b>" . $_SESSION['lang']['total'] . " " . $_SESSION['lang']['rupiah'] . "</td>";
			$tab .= "<td align=right><b>" . @number_format($tluaspanen - $oppotrp[$nospb], 2) . "</td>";
			$tab .= "<td align=right></td>";
			$tab .= "</tr>";
		} else {
			$tab .= "<tr class=rowcontent><td align=center colspan=12>" . $_SESSION['lang']['errdatanotexist'] . "</td></tr>";
		}
		$tab .= "</tbody>";
		$tab .= "</table>";
		if ($param['tipenya'] == 'html') {
			echo $tab;
		} else {
			$stream = $tab;
			$stream .= "</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
			$nop_ = "Rekap Angkutan TBS_PerSPB";
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
			}
		}
		break;
	case 'loaddatadetail':
		$arrjenis = array('muat', 'angkut');
		$arrtujuan = array('tphpks' => 'TPH - PKS', 'rampks' => 'RAMP - PKS');

		$tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable >
            <thead><tr class=rowheader>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nospb'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nopol'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['sopir'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</th>
			<th align=center rowspan='2'>PKS<br>Tujuan</th>
            <th align=center rowspan='2'>Jenis Kend</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['notifbrutto'] . "</th>
            <th align=center rowspan='2'>Potongan<br>Brondolan<br>Kg</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['notifbrutto'] . "</th>
            ";
		foreach ($arrjenis as $jenis) {
			$tab .= "<th align=center colspan='" . (count($arrmuat) + 1) . "'>" . $jenis . "</th>";
		}
		$tab .= "<th align=center rowspan='2'>" . $_SESSION['lang']['potongan'] . "<br>" . $_SESSION['lang']['rupiah'] . "</th>
              <th align=center rowspan='2'>" . $_SESSION['lang']['total'] . "<br>" . $_SESSION['lang']['rupiah'] . "</th>
            <th align=center rowspan='2' colspan=3>" . $_SESSION['lang']['action'] . "</th>
        </tr>";
		$tab .= "<tr>";
		foreach ($arrjenis as $jenis) {
			foreach ($arrmuat as $keytujuan => $valtujuan) {
				$tab .= "<th align=center>" . $valtujuan . "</th>";
			}
			$tab .= "<th align=center>Tambahan</th>";
		}

		$tab .= "</tr>";
		$tab .= "</thead>";
		$no = 0;
		$ttlfee = 0;
		$dataspb = $nospbht = array();
		$potonganrp = array();
		$str = "select a.*,b.jenis,b.tujuan,b.rupiah,b.kgtotal,b.kgbrd,b.potonganrp,b.blok from " . $dbname . ".kebun_rekapangkutantbsht a 
		left join " . $dbname . ".kebun_rekapangkutantbsdt b on a.nospb=b.nospb 
		where kodeorg = '" . $kodeorg . "' and a.periode='" . $periode . "' and 
		b.nospk='" . $spk . "' and a.periodebyr='" . $periodebyr . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$dataspb[$bar['nospb']] = $bar['nospb'];
			$tgl[$bar['nospb']] = $bar['tanggal'];
			$totalkgwb[$bar['nospb']] = $bar['totalkgwb'];
			$pkstujuanx[$bar['nospb']] = $bar['pkstujuan'];
			$jenisvhc[$bar['nospb']] = $bar['jenisvhc'];
			@$rupiah[$bar['nospb']][$bar['jenis']][$bar['tujuan']] += $bar['rupiah'];
			$nospbht[$bar['nospb']] = $bar['nospb'];
		}
		$kgtotal = $kgbrd = array();
		if (count($nospbht) > 0) {
			$str = "select * from " . $dbname . ".kebun_rekapangkutantbsdt 
			where nospb in ('" . implode("','", $nospbht) . "') and nospk='".$spk."' group by nospb, blok";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				@$kgnet[$bar['nospb']] += $bar['kgwb'];
				@$kgbrd[$bar['nospb']] += $bar['kgbrd'];
			}
			$str = "select nospb,jenis, sum(potonganrp) as potonganrp,sum(rpadd) as tambahan from " . $dbname . ".kebun_rekapangkutantbsdt 
			where nospb in ('" . implode("','", $nospbht) . "') group by nospb";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				@$potonganrp[$bar['nospb']] += $bar['potonganrp'];
				@$tambahan[$bar['nospb']][$bar['jenis']] += $bar['tambahan'];
			}
		}
		$no = 0;
		foreach ($dataspb as $nospb) {
			$optdriver = makeOption($dbname, 'pabrik_timbangan', 'nospb,supir', "nospb='" . $nospb . "'");
			$optnopol = makeOption($dbname, 'pabrik_timbangan', 'nospb,nokendaraan', "nospb='" . $nospb . "'");
			$no += 1;
			$i = "";
			$optdt = makeOption($dbname, 'kebun_rekapangkutantbsdt', 'nospb,nospb', "nospb='" . $nospb . "'");
			if (@$optdt[$nospb] == '') {
				$i = "style=background-color:red title=\"Detail transaksi tidak ada, silahkan di hapus\"";
			}

			$tab .= "<tr style=vertical-align:top class=rowcontent " . $i . ">";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=left>" . $nospb . "</td>";
			$tab .= "<td align=left>" . @$optnopol[$nospb] . "</td>";
			$tab .= "<td align=left>" . @$optdriver[$nospb] . "</td>";
			$tab .= "<td align=center>" . tanggalnormal($tgl[$nospb]) . "</td>";
			$tab .= "<td align=left>" . $pkstujuanx[$nospb] . "</td>";
			$tab .= "<td align=left>" . $nmjns[$jenisvhc[$nospb]] . "</td>";
			$tab .= "<td align=right>" . @number_Format($kgnet[$nospb]) . "</td>";
			$tab .= "<td align=right>" . @number_Format($kgbrd[$nospb]) . "</td>";
			$tab .= "<td align=right>" . @number_Format($kgnet[$nospb]) . "</td>";
			foreach ($arrjenis as $jenis) {
				foreach ($arrmuat as $keytujuan => $valtujuan) {
					$tab .= "<td align=right>" . @number_format($rupiah[$nospb][$jenis][$keytujuan]) . "</td>";
					@$ttlrp[$nospb] += $rupiah[$nospb][$jenis][$keytujuan];
					@$trp[$jenis][$keytujuan] += $rupiah[$nospb][$jenis][$keytujuan];
					@$gtrp += $rupiah[$nospb][$jenis][$keytujuan];
				}
				$tab .= "<td align=right>" . @number_format($tambahan[$nospb][$jenis]) . "</td>";
				@$tadd[$jenis] += $tambahan[$nospb][$jenis];
			}
			$tab .= "<td align=right>" . @number_Format($potonganrp[$nospb]) . "</td>";
			$tab .= "<td align=right>" . @number_Format($ttlrp[$nospb] - $potonganrp[$nospb]) . "</td>";


			$optjenisangkt = makeOption($dbname, 'kebun_rekapangkutantbsdt', 'nospb,tujuan', "nospb='" . $nospb . "'");
			$tab .= "<td align=center width=25px>
					<img src=images/application/application_edit.png class=zImgBtn style=display:none title='Edit' onclick=\"editdetail('" . $nospb . "','" . $jenisvhc[$nospb] . "','" . $optjenisangkt[$nospb] . "');\" ></td>";

			$tab .= "<td align=center width=25px>
					<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"popupdeletedetail('" . $kodeorg . "','" . $periode . "','" . $nospb . "','".$spk."','".$periodebyr."');\" ></td>";
			$tab .= "<td align=center width=25px>
					<img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"viewdetailx('" . $kodeorg . "','" . $periode . "','" . $nospb . "','html');\" >
				</td>";

			@$tkgnet += $kgnet[$nospb];
			@$tkgbrd += $kgbrd[$nospb];
			@$tkg += $totalkgwb[$nospb];
			@$tpotonganrp += $potonganrp[$nospb];
		}

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td align=center colspan=7><b>" . $_SESSION['lang']['total'] . "</b></td>";
		$tab .= "<td align=right><b>" . @number_format($tkgnet,2) . "</b></td>";
		$tab .= "<td align=right><b>" . @number_format($tkgbrd,2) . "</b></td>";
		$tab .= "<td align=right><b>" . @number_format($tkgnet,2) . "</b></td>";
		foreach ($arrjenis as $jenis) {
			foreach ($arrmuat as $keytujuan => $valtujuan) {
				$tab .= "<td align=right><b>" . @number_format($trp[$jenis][$keytujuan]) . "</b></td>";
			}
			$tab .= "<td align=right><b>" . @number_format($tadd[$jenis]) . "</b></td>";
		}
		$tab .= "<td align=right><b>" . @number_format($tpotonganrp) . "</b></td>";
		$tab .= "<td align=right><b>" . @number_format($gtrp - $tpotonganrp,2) . "</b></td>";
		#$tab.="<td colspan=3 align=right><b>" . @number_format($ttlfee) . "</b></td>";
		$tab .= "<td colspan=3></td>";
		$tab .= "</tr>";

		$tab .= "</table>";
		echo $tab;
		break;
	case 'loaddata':
		$where = "";
		$str = "select nilai from " . $dbname . ".setup_parameterappl where kodeparameter='JABKRN'";
		$res = fetchdata($str);
		@$arrjab = explode(',', $res[0]['nilai']);

		if ($_SESSION['empl']['subbagian'] == '' and in_array($_SESSION['empl']['kodejabatan'], $arrjab)) {
			$where .= " and a.kodeorg in (".getOrgDetail(2).") and a.createby ='" . $_SESSION['standard']['userid'] . "'";
		} else {
			$where .= " and a.kodeorg in (".getOrgDetail(2).")";
		}

		if ($divsch != '') {
			$where .= " and a.kodeorg='" . $divsch . "' ";
		}
		if ($tglsch != '') {
			$where .= " and a.periode like '" . $tglsch . "%' ";
		}

		if ($nospb != '') {
			$where .= " and a.nospb like '%" . $nospb . "%' ";
		}

		if ($param['bapp'] != '') {
			$where .= " and nobapp like '%" . $param['bapp'] . "%' ";
		}

		if ($kontrakcr != '') {
			$where .= " and a.spk in  (select notransaksi from " . $dbname . ".log_spkht where koderekanan in (select supplierid from " . $dbname . ".log_5supplier where namasupplier  like '%" . $kontrakcr . "%' ))";
		}

		if ($nospkcr != '') {
			$where .= " and a.spk like '%" . $nospkcr . "%' ";
		}
		$limit = 20;
		$page = 0;
		$_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
		if (isset($_POST['page'])) {
			$page = intval($_POST['page']);
			if ($page < 0)
				$page = 0;
		}
		$offset = $page * $limit;
		$maxdisplay = ($page * $limit);

		$arrjenis = array('muat', 'angkut');
		$arrtujuan = array('tphpks' => 'TPH - PKS', 'rampks' => 'RAMP - PKS');

		$no = 0;
		$dataspb = array();
		$sql = "select a.nospb,periodebyr,nobapp,posting,kodeorg,periode,spk,sum(totalkgwb) as totalkgwb from " . $dbname . ".kebun_rekapangkutantbsht a where 1=1 " . $where . " group by kodeorg,periode,spk,periodebyr";
		$jlhbrs = count(fetchdata($sql));
		$no = $maxdisplay;

		$datasupp = array();
		$posting = array();
		$nobapp = array();
		$prdbyr = array();
		$kgwb = array();
		$potongan = array();
		$rupiah = array();
		$str = "select a.nospb,periodebyr,nobapp,posting,kodeorg,periode,spk,sum(totalkgwb) as totalkgwb,tgldari,tglsampai from " . $dbname . ".kebun_rekapangkutantbsht a 
		where 1=1 " . $where . " group by kodeorg,periode,spk,periodebyr order by periode desc, periodebyr desc, spk desc limit " . $offset . "," . $limit . "";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$datasupp[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']] = $bar['spk'];
			$posting[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']] = $bar['posting'];
			$nobapp[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']] = $bar['nobapp'];
			@$prdbyr[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']] = $bar['periodebyr'];
			@$tgldr[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']] = $bar['tgldari'];
			@$tglsd[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']] = $bar['tglsampai'];
			@$kgwb[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']] += $bar['totalkgwb'];
		}
		$strcek = "select nospb from " . $dbname . ".kebun_rekapangkutantbsht a where 1=1 " . $where . "";
		$rescek = fetchdata($strcek);
		if (count($rescek) > 0) {
			#potongan
			$str = "select kodeorg,periode,spk,periodebyr, sum(potonganrp) as potonganrp from " . $dbname . ".kebun_rekapangkutantbsdt a 
			left join " . $dbname . ".kebun_rekapangkutantbsht b on a.nospb=b.nospb where a.nospb in (select nospb from " . $dbname . ".kebun_rekapangkutantbsht a where 1=1 " . $where . ") group by a.nospb, a.blok";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				@$potongan[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']] += $bar['potonganrp'];
			}

			$str = "SELECT * FROM $dbname.kebun_rekapangkutantbsdt WHERE b.nospb ij";


			$str = "select * from " . $dbname . ".kebun_rekapangkutantbsht a 
			left join " . $dbname . ".kebun_rekapangkutantbsdt b on a.nospb=b.nospb
			where a.nospb in (select nospb from " . $dbname . ".kebun_rekapangkutantbsht a where 1=1 " . $where . ")";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				@$rupiah[$bar['kodeorg']][$bar['periode']][$bar['nospk']][$bar['periodebyr']][$bar['jenis']][$bar['tujuan']] += $bar['rupiah'];
				@$rupiahtambahan[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']][$bar['jenis']] += $bar['rpadd'];
			}
			$str = "select *,sum(b.kgwb) as kgwb,sum(b.kgbrd) as kgbrd from " . $dbname . ".kebun_rekapangkutantbsht a 
			left join " . $dbname . ".kebun_rekapangkutantbsdt b on a.nospb=b.nospb
			where a.nospb in (select nospb from " . $dbname . ".kebun_rekapangkutantbsht a where 1=1 " . $where . ") group by a.nospb";
			$kgbrd = array();
			$kgnet = array();
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				@$kgbrd[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']] += $bar['kgbrd'];
				@$kgnet[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']] += $bar['kgwb'];
			}

			$str = "select * from " . $dbname . ".kebun_rekapangkutantbsdtfee a 
			left join " . $dbname . ".kebun_rekapangkutantbsht b on a.nospb=b.nospb
			where a.nospb in (select nospb from " . $dbname . ".kebun_rekapangkutantbsht a where 1=1 " . $where . ")";
			$rpfee = array();
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				@$rpfee[$bar['kodeorg']][$bar['periode']][$bar['spk']][$bar['periodebyr']] += $bar['rupiah'];
			}
		}


		if ($jlhbrs > 0) {
			foreach ($datasupp as $kdorg => $valprd) {
				foreach ($valprd as $prd => $valsupp) {
					foreach ($valsupp as $nospk => $valprdbyr) {
						foreach ($valprdbyr as $prdbyr => $kdspk) {
							$optkdorg = array();
							$kdsupp   = array();
							$namasupp = array();
							$optkdorg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi', "kodeorganisasi='" . $kdorg . "'");
							$kdsupp   = makeOption($dbname, 'log_spkht', 'notransaksi,koderekanan', "notransaksi='" . $nospk . "'");
							$kodeorgspk = makeOption($dbname, 'log_spkht', 'notransaksi,koderekanan', "notransaksi='" . $nospk . "'");
							@$namasupp = makeOption($dbname, 'log_5supplier', 'supplierid,namasupplier', "supplierid='" . $kdsupp[$nospk] . "'");
							if ($prdbyr == '0') {
								$prdbyrx = tanggalnormal($prd . "-01") . " s/d " . tanggalnormal(tglakhir($prd . "-01"));
							} elseif ($prdbyr == '1') {
								$prdbyrx = tanggalnormal($prd . "-01") . " s/d " . tanggalnormal($prd . "-15");
							} else {
								$prdbyrx = tanggalnormal($prd . "-16") . " s/d " . tanggalnormal(tglakhir($prd . "-01"));
							}

							// cek berapa yang sudah diposting
							$strdd = "select sum(jumlahrealisasi) as jumlahrealisasi, statusjurnal,nopengajuan,termin,tanggal,keterangan from " . $dbname . ".log_baspk where notransaksi = '" . $nospk . "' and keterangan = '" . $nobapp[$kdorg][$prd][$nospk][$prdbyr] . "' group by statusjurnal";
							$realdd = 0;
							$resdd = $owlPDO->query($strdd) or die(print " Gagal: " . PDOException::getMessage());
							$resdd->setFetchMode(PDO::FETCH_ASSOC);
							while ($bardd = $resdd->fetch()) {
								if ($bardd['statusjurnal'] == 1) {
									$realdd += $bardd['jumlahrealisasi'];
								}
								$nopengajuan = $bardd['nopengajuan'];
								$keterangan = $bardd['keterangan'];
								$termin = $bardd['termin'];
								$tglnya = $bardd['tanggal'];
								$statusjurnal = $bardd['statusjurnal'];
							}

							$no += 1;
							$tab .= "<tr class=rowcontent>";
							$tab .= "<td align=center>" . $no . "</td>";
							#$tab.="<td align=left>" . $kdorg. " - ".$optkdorg[$kdorg]."</td>";
							$tab .= "<td align=left>" . $kdorg . "</td>";
							$tab .= "<td align=center>" . $prd . "</td>";
							$tab .= "<td align=center>" . tanggalnormal($tgldr[$kdorg][$prd][$nospk][$prdbyr]) . "<br> s.d <br>" . tanggalnormal($tglsd[$kdorg][$prd][$nospk][$prdbyr]) . "</td>";
							$tab .= "<td align=left>" . $nospk . "</td>";
							$tab .= "<td align=left>" . @$namasupp[$kdsupp[$nospk]] . "</td>";
							$tab .= "<td align=right>" . number_Format($kgwb[$kdorg][$prd][$nospk][$prdbyr], 2) . "</td>";
							$tab .= "<td align=right>" . number_Format($kgbrd[$kdorg][$prd][$nospk][$prdbyr], 2) . "</td>";
							// $tab .= "<td align=right>" . number_Format($kgnet[$kdorg][$prd][$nospk][$prdbyr], 2) . "</td>";
							foreach ($arrjenis as $jenis) {
								foreach ($arrmuat as $keytujuan => $valtujuan) {
									$tab .= "<td align=right>" . @number_format($rupiah[$kdorg][$prd][$nospk][$prdbyr][$jenis][$keytujuan], 2) . "</td>";
									@$ttlrp[$kdorg][$prd][$nospk][$prdbyr] += $rupiah[$kdorg][$prd][$nospk][$prdbyr][$jenis][$keytujuan];
									@$trp[$jenis][$keytujuan][$prdbyr] += $rupiah[$kdorg][$prd][$nospk][$prdbyr][$jenis][$keytujuan];
									@$gtrp += $rupiah[$kdorg][$prd][$nospk][$prdbyr][$jenis][$keytujuan];
								}
								$tab .= "<td align=right>" . @number_format($rupiahtambahan[$kdorg][$prd][$nospk][$prdbyr][$jenis], 2) . "</td>";
							}
							$tab .= "<td align=right>" . @number_format($potongan[$kdorg][$prd][$nospk][$prdbyr], 2) . "</td>";
							$tab .= "<td align=right>" . @number_format($ttlrp[$kdorg][$prd][$nospk][$prdbyr] - $potongan[$kdorg][$prd][$nospk][$prdbyr], 2) . "</td>";
							$warna = 'blue';
							$judul = 'Click untuk melihat dan mengajuan BAPP';
							if ((($ttlrp[$kdorg][$prd][$nospk][$prdbyr] - $potongan[$kdorg][$prd][$nospk][$prdbyr]) - $realdd) != 0) { // ada beda antara rupiah vs bapp yang sudah diposting
								$warna = 'red';
								$judul = 'Ada BAPP yang belum diposting';
							}
							#$tab.="<td align=right>" . @number_format($rpfee[$kdorg][$prd][$nospk][$prdbyr]). "</td>";
							$tab .= "<td align=left style=color:" . $warna . ";cursor:pointer; title=\"" . $judul . "\" onclick=viewdetailbapp('" . $nospk . "','" . $kdorg . "','viewhtml','event','" . $nobapp[$kdorg][$prd][$nospk][$prdbyr] . "')>" . ($nobapp[$kdorg][$prd][$nospk][$prdbyr]) . " <!--" . $realdd . "--></td>";

							#$tab.="<td align=center>";
							if ($posting[$kdorg][$prd][$nospk][$prdbyr] == '0' || $posting[$kdorg][$prd][$nospk][$prdbyr] == '3') {
								$tab .= "<td width=20px align=center><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('" . $kdorg . "','" . $prd . "','" . $nospk . "','" . $prdbyr . "','" . tanggalnormal($tgldr[$kdorg][$prd][$nospk][$prdbyr]) . "','" . tanggalnormal($tglsd[$kdorg][$prd][$nospk][$prdbyr]) . "');\" ></td>";
								$tab .= "<td width=20px align=center><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('" . $kdorg . "','" . $prd . "','" . $nospk . "','" . $prdbyr . "');\" ></td>";

								$tab .= "<td width=20px align=center><img src=images/skyblue/posting.png class=zImgBtn class=zImgBtn height='30'  title='Posting' onclick=\"posting('" . $kdorg . "','" . $prd . "','" . $nospk . "','" . $prdbyr . "','" . $no . "');\" ></td>";
							} else {
								if (in_array($_SESSION['empl']['jabatan'], $jab)) {
									$icon = "images/icons/04/16/04.png";
									$title = "Unposting";
									$unpost = " onclick=\"unposting('" . $kdorg . "','" . $prd . "','" . $nospk . "','" . $nobapp[$kdorg][$prd][$nospk][$prdbyr] . "','" . $prdbyr . "','" . $no . "');\" ";
								} else {
									$icon = "images/icons/04/16/02.png";
									$title = "Posted";
									$unpost = '';
								}
								$tab .= "<td></td><td></td>";
								$tab .= "<td width=20px align=center><img src=" . $icon . " class=zImgBtn class=zImgBtn height='30'  title='" . $title . "' " . $unpost . " ></td>";
							}

							$tab .= "<td width=20px align=center><img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"html('" . $kdorg . "','" . $prd . "','" . $nospk . "','" . $prdbyr . "','html');\" ></td>";
							$tab .= "<td width=20px align=center><img src='images/skyblue/pdf.jpg' class='zImgBtn' onclick=\"detailPDF2('" . $nospk . "','" . $kdorg . "','viewpdf',event)\" title='Print Data Detail'></td>";
							$tab .= "<td width=20px align=center><img src=images/excel.jpg class=zImgBtn title='Excel' onclick=\"previewexcel('" . $kdorg . "','" . $prd . "','" . $nospk . "','" . $prdbyr . "','excel');\" ></td>";
							if ($statusjurnal == 1) {
								$tab .= "<td width=20px align=center><img src=images/excel.jpg class=zImgBtn title='Excel Per Blok' onclick=\"previewexcelall('" . $nopengajuan . "','" . $nospk . "','" . $keterangan . "','" . $kodeorgspk[$nospk] . "','" . $tglnya . "','" . $termin . "','" . $no . "','" . $nospk . "','" . tanggalnormal($tgldr[$kdorg][$prd][$nospk][$prdbyr]) . "','" . tanggalnormal($tglsd[$kdorg][$prd][$nospk][$prdbyr]) . "')\"></td>";
							} else {
								$tab .= "<td></td>";
							}
							#$tab.="&nbsp;</td>";
						}
					}
				}
			}
		}

		$tab .= "</tr>";

		$tab .= "</table>";


		$totrows = ceil($jlhbrs / $limit);
		if ($totrows == 0) {
			$totrows = 1;
		}
		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page == $er - 1) ? 'selected' : '';
			$isiRow .= "<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
		}
		$footd = createpaging($jlhbrs, $limit, $page, (22 + (count($arrmuat) * 2)), 'loaddata', 'getPage');
		echo $tab . "####" . $footd;
		break;

	case 'delete':
		$str = "delete from " . $dbname . ".kebun_rekapangkutantbsht where kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and spk='" . $spk . "' and periodebyr='" . $periodebyr . "' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		}
		break;
	case 'html':
		$arrjenis = array('muat', 'angkut');
		$arrtujuan = array('tphpks' => 'TPH - PKS', 'rampks' => 'RAMP - PKS');
		if ($jenistampil == 'html') {
			$tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable width=100%>";
		} else {
			$tab = "<table cellpadding=1 cellspacing=1 border=1 class=sortable width=100%>";
		}

		$tab .= "<thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nospb'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['ticket'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nopol'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['sopir'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</td>
			<td align=center rowspan='2'>PKS<br>Tujuan</td>
            <td align=center rowspan='2'>Jenis Kend</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['notifbrutto'] . "</td>
			<td align=center rowspan='2'>Potongan<br>Brondolan<br>Kg</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['notifbrutto'] . "</td>";
		foreach ($arrjenis as $jenis) {
			$tab .= "<td align=center colspan='" . (count($arrmuat) + 1) . "'>" . $jenis . "</td>";
		}

		$tab .= "<td align=center rowspan='2'>" . $_SESSION['lang']['potongan'] . "<br>" . $_SESSION['lang']['rupiah'] . "</td>
			<td align=center rowspan='2'>" . $_SESSION['lang']['total'] . "<br>" . $_SESSION['lang']['rupiah'] . "</td>";
		if ($jenistampil == 'html') {
			$tab .= "<td align=center rowspan='2' colspan=2>" . $_SESSION['lang']['action'] . "</td>";
		}
		$tab .= "</tr>";
		$tab .= "<tr>";
		foreach ($arrjenis as $jenis) {
			foreach ($arrmuat as $keytujuan => $valtujuan) {
				$tab .= "<td align=center>" . $valtujuan . "</td>";
			}
			$tab .= "<td align=center>Tambahan</td>";
		}

		$tab .= "</tr>";
		$tab .= "</thead>";
		$no = 0;
		$dataspb = $rupiah = $potonganrp = array();
		$str = "select a.*,b.jenis,b.tujuan,b.rupiah,b.rpadd from " . $dbname . ".kebun_rekapangkutantbsht a
		left join " . $dbname . ".kebun_rekapangkutantbsdt b  on a.nospb=b.nospb 
		where kodeorg = '" . $kodeorg . "' and periode='" . $periode . "' and spk='" . $spk . "' and periodebyr='" . $periodebyr . "' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$dataspb[$bar['nospb']] = $bar['nospb'];
			$tgl[$bar['nospb']] = $bar['tanggal'];
			$kgwbttl[$bar['nospb']] = $bar['totalkgwb'];
			$pkstujuanx[$bar['nospb']] = $bar['pkstujuan'];
			$jenisvhc[$bar['nospb']] = $bar['jenisvhc'];
			@$rupiah[$bar['nospb']][$bar['jenis']][$bar['tujuan']] += $bar['rupiah'];
			@$tambahan[$bar['nospb']][$bar['jenis']] += $bar['rpadd'];
			$nospbht[$bar['nospb']] = $bar['nospb'];
		}


		$kgtotal = $kgbrd = $kgnet = array();
		if (count($nospbht) > 0) {
			$str = "select * from " . $dbname . ".kebun_rekapangkutantbsdt 
			where nospb in ('" . implode("','", $nospbht) . "') group by nospb,blok";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				@$kgnet[$bar['nospb']] += $bar['kgwb'];
				@$kgbrd[$bar['nospb']] += $bar['kgbrd'];
			}
			$str = "select nospb, sum(potonganrp) as potonganrp from " . $dbname . ".kebun_rekapangkutantbsdt 
			where nospb in ('" . implode("','", $nospbht) . "') group by nospb, blok";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()) {
				@$potonganrp[$bar['nospb']] += $bar['potonganrp'];
			}
		}

		$no = 0;
		if (count($dataspb) > 0) {
			foreach ($dataspb as $nospb) {
				$optdriver = makeOption($dbname, 'pabrik_timbangan', 'nospb,supir', "nospb='" . $nospb . "'");
				$optnopol = makeOption($dbname, 'pabrik_timbangan', 'nospb,nokendaraan', "nospb='" . $nospb . "'");
				$no += 1;
				$i = "";
				$optdt = makeOption($dbname, 'kebun_rekapangkutantbsdt', 'nospb,nospb', "nospb='" . $nospb . "'");
				if (@$optdt[$nospb] == '') {
					$i = "style=background-color:red title=\"Detail transaksi tidak ada, silahkan di hapus\"";
				}
				$notiket = makeOption($dbname, 'pabrik_timbangan', 'nospb,notransaksi', "nospb='" . $nospb . "'");

				$tab .= "<tr class=rowcontent " . $i . " style=vertical-align:top>";
				$tab .= "<td align=center>" . $no . "</td>";
				$tab .= "<td align=left>" . $nospb . "</td>";
				$tab .= "<td align=left>" . @$notiket[$nospb] . "</td>";
				$tab .= "<td align=left>" . @$optnopol[$nospb] . "</td>";
				$tab .= "<td align=left>" . @$optdriver[$nospb] . "</td>";
				$tab .= "<td align=center>" . $tgl[$nospb] . "</td>";
				$tab .= "<td align=left>" . $pkstujuanx[$nospb] . "</td>";
				$tab .= "<td align=left>" . $nmjns[$jenisvhc[$nospb]] . "</td>";
				$tab .= "<td align=right>" . number_Format($kgwbttl[$nospb]) . "</td>";
				$tab .= "<td align=right>" . number_Format($kgbrd[$nospb]) . "</td>";
				$tab .= "<td align=right>" . number_Format($kgnet[$nospb]) . "</td>";
				foreach ($arrjenis as $jenis) {
					foreach ($arrmuat as $keytujuan => $valtujuan) {
						$tab .= "<td align=right>" . @number_format($rupiah[$nospb][$jenis][$keytujuan]) . "</td>";
						@$ttlrp[$nospb] += $rupiah[$nospb][$jenis][$keytujuan];
						@$trp[$jenis][$keytujuan] += $rupiah[$nospb][$jenis][$keytujuan];
						@$gtrp += $rupiah[$nospb][$jenis][$keytujuan];
					}
					$tab .= "<td align=right>" . @number_format($tambahan[$nospb][$jenis]) . "</td>";
					@$tadd[$jenis] += $tambahan[$nospb][$jenis];
				}
				$tab .= "<td align=right>" . number_Format($potonganrp[$nospb]) . "</td>";
				$tab .= "<td align=right>" . number_format($ttlrp[$nospb] - $potonganrp[$nospb]) . "</td>";
				if ($jenistampil == 'html') {
					$tab .= "<td align=center width=30px>
						<img src=images/skyblue/zoom.png class=zImgBtn  title='Preview' onclick=\"viewdetailx('" . $kodeorg . "','" . $periode . "','" . $nospb . "','html','".$spk."');\" >
					</td>";
					$tab .= "<td align=center width=30px>
						<img src=images/excel.jpg class=zImgBtn  title='Excel' onclick=\"viewdetailx('" . $kodeorg . "','" . $periode . "','" . $nospb . "');\" >
					</td>";
				}

				@$tkgnet += $kgnet[$nospb];
				@$tkgbrd += $kgbrd[$nospb];
				@$tkg += $kgwbttl[$nospb];
				@$tpotonganrp += $potonganrp[$nospb];
			}

			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center colspan=8><b>" . $_SESSION['lang']['total'] . "</td>";
			$tab .= "<td align=right><b>" . @number_format($tkg) . "</td>";
			$tab .= "<td align=right><b>" . @number_format($tkgbrd) . "</td>";
			$tab .= "<td align=right><b>" . @number_format($tkgnet) . "</td>";
			foreach ($arrjenis as $jenis) {
				foreach ($arrmuat as $keytujuan => $valtujuan) {
					$tab .= "<td align=right><b>" . @number_format($trp[$jenis][$keytujuan]) . "</td>";
				}
				$tab .= "<td align=right><b>" . @number_format($tadd[$jenis]) . "</td>";
			}
			$tab .= "<td align=right><b>" . @number_format($tpotonganrp) . "</td>";
			$tab .= "<td align=right><b>" . @number_format($gtrp - $tpotonganrp) . "</td>";
			if ($jenistampil == 'html') {
				$tab .= "<td colspan=2></td>";
			}
			$tab .= "</tr>";
		}

		$tab .= "</table>";

		if ($jenistampil == 'html') {
			echo $tab;
		} else {

			$stream = $tab;
			$stream .= "</table>Print Time:" . date('YmdHis') . "<br>By:" . $_SESSION['empl']['name'];
			$tempnm = explode("/", $_SERVER['PHP_SELF']);
			$nop_ = "Rekap Angkutan TBS_" . $kodeorg . "_" . $periode . "_" . $periodebyr;
			if (strlen($stream) > 0) {
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/' . $file);
						}
					}
					closedir($handle);
				}
				$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
				if (!fwrite($handle, $stream)) {
					echo "<script language=javascript1.2>
							parent.window.alert('Can't convert to excel format');
							</script>";
					exit;
				} else {
					echo "<script language=javascript1.2>
							window.location='tempExcel/" . $nop_ . ".xls';
							</script>";
				}
				fclose($handle);
			}
		}

		break;

	case 'posting':
		try {
			$owlPDO->beginTransaction();

			#buat nomor termin
			$str = "select max(termin) as termin from " . $dbname . ".log_baspk where notransaksi='" . $spk . "'";
			$str = "select distinct keterangan, termin from " . $dbname . ".log_baspk where notransaksi='" . $spk . "'  group by keterangan, termin";
			$res = fetchdata($str);
			$noter = count($res);
			$notermin = $periodebyr;
			$arrthn = explode("-", $periode);
			$tahun = $arrthn[0];
			$bulan = $arrthn[1];

			#buat nomor BAPP Format : 001/BAPP/BPJE/2019
			$str = "select max(substr(keterangan,1,3)) as bapp from " . $dbname . ".log_baspk where notransaksi = '" . $spk . "' limit 1"; #exit("error");
			$res = fetchdata($str);
			$noba = intval($res[0]['bapp']);
			if ($noba == 0) {
				$nobap = "001";
			} else {
				$nobap = addZero($noba + 1, 3);
			}
			$dtnospk = explode("/", $spk);
			$nobapp = $nobap . "/" . $dtnospk[0] . "/BAPP/" . $kodeorg . "/" . $bulan . "/" . $tahun;

			#ambil data
			$str = "select b.*,a.tanggal,a.nospb as nospbht from " . $dbname . ".kebun_rekapangkutantbsht a 
            left join " . $dbname . ".kebun_rekapangkutantbsdt b on a.nospb=b.nospb 
            where 1=1 and a.kodeorg = '" . $kodeorg . "' and a.periode='" . $periode . "' and b.nospk='" . $spk . "' and periodebyr='" . $periodebyr . "'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$data = $rupiah = $datadt = $rupiahdt = array();
			while ($bar = $res->fetch()) {
				#$data[$bar['blok']][$bar['jeniskegiatan']][$bar['jenis']."#".$bar['tujuan']]+=$bar['kgwb'];
				#$rupiah[$bar['blok']][$bar['jeniskegiatan']][$bar['jenis']."#".$bar['tujuan']]+=$bar['rupiah']-$bar['potonganrp'];
				if ($bar['rupiah'] > 0) {
					$data[$bar['blok']][$bar['jeniskegiatan']] += $bar['kgwb'];
					$rupiah[$bar['blok']][$bar['jeniskegiatan']] += $bar['rupiah'] - $bar['potonganrp'];

					$datadt[$bar['blok']][$bar['jeniskegiatan']][$bar['nospb']][$bar['tanggal']][$bar['jenis'] . "#" . $bar['tujuan']] += $bar['kgwb'];
					$rupiahdt[$bar['blok']][$bar['jeniskegiatan']][$bar['nospb']][$bar['tanggal']][$bar['jenis'] . "#" . $bar['tujuan']] += $bar['rupiah'] - $bar['potonganrp'];
				}

				if ($bar['nospb'] == "") {
					throw new PDOException("No SPB " . $bar['nospbht'] . " tidak ada pada detail transaksi");
				}
			}

			#log_baspk
			$totalrupiah = 0;
			foreach ($data as $blok => $valkeg) {
				foreach ($valkeg as $kegiatan => $kgwb) {
					$data = array();
					if ($rupiah[$blok][$kegiatan] != '') {
						$data = array(
							'notransaksi'        => $spk,
							'kodeblok'           => $blok,
							'kodekegiatan'       => $kegiatan,
							'tanggal'            => '0000-00-00',
							'hasilkerjarealisasi' => $kgwb,
							'jumlahrealisasi'    => $rupiah[$blok][$kegiatan],
							'posting'            => '0',
							'statusjurnal'       => '0',
							'statuspengajuan'    => '0',
							'blokspkdt'          => $blok,
							'termin'             => $notermin,
							'keterangan'         => $nobapp
						);

						$cols = array();
						foreach ($data as $key => $row) {
							$cols[] = $key;
						}

						$str = insertQuery($dbname, 'log_baspk', $data, $cols);
						$owlPDO->exec($str);

						$totalrupiah += $rupiah[$blok][$kegiatan];
						#====================================

						$jlh = 0;
						$where = "notransaksi='" . $spk . "' and kodeblok='" . $blok . "' and kodekegiatan='" . $kegiatan . "'";
						#cek dulu ada atau tidak, jika ada update jika tidak insert
						$str = "select * from " . $dbname . ".log_spkdt where " . $where . "";
						$res = fetchdata($str);
						$jlh = count($res);
						# exit('error'.$str);
						if ($jlh > 0) {
							#hapus dulu
							$str = "delete from " . $dbname . ".log_spkdt where " . $where . "";
							$owlPDO->exec($str);
						}

						$str = "select sum(hasilkerjarealisasi) as hasilkerjarealisasi, sum(jumlahrealisasi) as jumlahrealisasi from " . $dbname . ".log_baspk where " . $where . "";
						$res = fetchdata($str);
						$hasilkerja = $res[0]['hasilkerjarealisasi'];
						$rupiahhasil = $res[0]['jumlahrealisasi'];
						if ($rupiahhasil != 0) {
							$rppersatuan = $rupiahhasil / $hasilkerja;
						} else {
							$rppersatuan = 0;
						}


						$dataspk = array();
						$dataspk = array(
							'notransaksi'     => $spk,
							'kodeblok'        => $blok,
							'kodekegiatan'    => $kegiatan,
							'hk'              => '0',
							'hasilkerjajumlah' => $hasilkerja,
							'satuan'          => 'KG',
							'jumlahrp'        => $rupiahhasil,
							'rupiahpersatuan' => $rppersatuan
						);

						$colsspk = array();
						foreach ($dataspk as $key => $row) {
							$cols[] = $key;
						}

						$str = insertQuery($dbname, 'log_spkdt', $dataspk, $colsspk);
						$owlPDO->exec($str);
						#===================================
					}
				}
			}

			#update nilai di spkht
			$str = "select sum(jumlahrp) as jumlahrp from " . $dbname . ".log_spkdt where notransaksi='" . $spk . "'";
			$res = fetchdata($str);
			$nilaikontrak = $res[0]['jumlahrp'];

			$where = "notransaksi='" . $spk . "' and kodeorg='" . $kodeorg . "'";
			$str = "update " . $dbname . ".log_spkht set nilaikontrak = '" . $nilaikontrak . "' where " . $where . "";
			$owlPDO->exec($str);

			#log_baspkdt
			foreach ($datadt as $blokdt => $valkegdt) {
				foreach ($valkegdt as $kegdt => $valspbdt) {
					foreach ($valspbdt as $nospbdt => $valtgldt) {
						foreach ($valtgldt as $tgldt => $valjenis) {
							foreach ($valjenis as $jenis => $kgwbdt) {
								$data = array();
								if ($rupiahdt[$blokdt][$kegdt][$nospbdt][$tgldt][$jenis] != '') {
									$data = array(
										'notransaksi'        => $spk,
										'kodeblok'           => $blokdt,
										'kodekegiatan'       => $kegdt,
										'tanggal'            => $tgldt,
										'hasilkerjarealisasi' => $kgwbdt,
										'jumlahrealisasi'    => $rupiahdt[$blokdt][$kegdt][$nospbdt][$tgldt][$jenis],
										'termin'             => $notermin,
										'keterangan'         => $nobapp,
										'keterangan2'        => $nospbdt
									);

									$cols = array();
									foreach ($data as $key => $row) {
										$cols[] = $key;
									}
									$str = insertQuery($dbname, 'log_baspkdt', $data, $cols);
									$owlPDO->exec($str);
								}
							}
						}
					}
				}
			}


			#update nilai di kebun_rekapangkutantbsht
			$updaterekap = array(
				'posting' => '1',
				'nobapp' => $nobapp
			);

			$where = "periode='" . $periode . "' and kodeorg='" . $kodeorg . "' and periodebyr='" . $periodebyr . "' and spk ='" . $spk . "'";
			$str = updateQuery($dbname, 'kebun_rekapangkutantbsht', $updaterekap, $where);
			$owlPDO->exec($str);


			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}

		break;
	case 'unposting':
		try {
			$owlPDO->beginTransaction();

			#cek BAPP sudah diajukan atau belum
			$statuspengajuan = $posting = '0';
			$str = "select * from " . $dbname . ".log_baspk where notransaksi='" . $spk . "' and keterangan ='" . $nobapp . "'";
			$ttp = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$ttp->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $ttp->fetch()) {
				$statuspengajuan = $bar['statuspengajuan'];
				$posting = $bar['posting'];
			}
			if ($posting == '1') {
				#sudah posting
				throw new PDOException("BAPP sudah diposting !");
			}

			if ($statuspengajuan != '3' and $statuspengajuan != '0') {
				#jika status BUKAN ditolak atau sudah diajukan
				throw new PDOException("BAPP sudah dalam proses persetujuan dan atau sudah disetujui !");
			}

			#hapus log_baspk
			$str = "delete from " . $dbname . ".log_baspk where notransaksi='" . $spk . "' and keterangan ='" . $nobapp . "'";
			$owlPDO->exec($str);

			#hapus log_baspkdt
			$str = "delete from " . $dbname . ".log_baspkdt where notransaksi='" . $spk . "' and keterangan ='" . $nobapp . "'";
			$owlPDO->exec($str);

			#hapus log_baspkdt_detail
			$str = "delete from " . $dbname . ".log_baspkdt_detail where notransaksi='" . $spk . "' and keterangan ='" . $nobapp . "'";
			$owlPDO->exec($str);

			#update nilai di kebun_rekapangkutantbsht
			$updaterekap = array(
				'posting' => '0',
				'nobapp' => ''
			);

			$where = "periode='" . $periode . "' and kodeorg='" . $kodeorg . "' and periodebyr='" . $periodebyr . "' and spk='" . $spk . "'";
			$str = updateQuery($dbname, 'kebun_rekapangkutantbsht', $updaterekap, $where);
			$owlPDO->exec($str);

			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}

		break;

	##==============================================================================================

	case 'excel':
		$tab = "<table cellpadding=1 cellspacing=1 border=1 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
            <td align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['tanggal'] . "</td>    
            <td align=center rowspan='2'>" . $_SESSION['lang']['kodeblok'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['tahuntanam'] . "</td>
            <td align=center colspan='2'>" . $_SESSION['lang']['luas'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['lapPersonel'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['jjg'] . "<br>" . $_SESSION['lang']['panen'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['bjr'] . "</td>
            <td align=center rowspan='2'>" . $_SESSION['lang']['kg'] . " " . $_SESSION['lang']['kebun'] . "</td>
            <td align=center colspan='2'>" . $_SESSION['lang']['afkir'] . "</td>
        </tr>
        <tr>
            <td align=center>" . $_SESSION['lang']['luasareaproduktif'] . "</td>
            <td align=center>" . $_SESSION['lang']['panen'] . "</td>
            <td align=center>" . $_SESSION['lang']['jjg'] . "</td>
            <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
            </tr></thead>";
		$no = 0;
		$str = "select * from " . $dbname . ".kebun_rekappnn where divisi like '" . $unitexp . "%' "
			. " and tanggal like '" . $perexp . "%' order by tanggal asc,blok asc ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$no += 1;
			$tab .= "<tr class=rowcontent>";
			$tab .= "<td align=center>" . $no . "</td>";
			$tab .= "<td align=left>" . tanggalnormal($bar['tanggal']) . "</td>";
			$tab .= "<td align=left>" . $bar['blok'] . "</td>";
			$tab .= "<td align=right>" . $bar['tahuntanam'] . "</td>";
			$tab .= "<td align=right>" . @number_format($bar['luasproduksi'], 2) . "</td>";
			$tab .= "<td align=right>" . @number_format($bar['luaspanen'], 2) . "</td>";
			$tab .= "<td align=right>" . @number_format($bar['tenagakerja'], 2) . "</td>";
			$tab .= "<td align=right>" . @number_format($bar['jjgpanen']) . "</td>";
			$tab .= "<td align=right>" . @number_format($bar['bjr'], 2) . "</td>";
			$tab .= "<td align=right>" . @number_format($bar['kgkebun']) . "</td>";
			$tab .= "<td align=right>" . @number_format($bar['jjgafkir']) . "</td>";
			$tab .= "<td align=left>" . $bar['keterangan'] . "</td>";
			@$tluasplan += $bar['luasproduksi'];
			@$tluaspanen += $bar['luaspanen'];
			@$ttk += $bar['tenagakerja'];
			@$tjjgpnn += $bar['jjgpanen'];
			@$tjjgafkir += $bar['jjgafkir'];
			@$tkgkebun += $bar['kgkebun'];
		}
		$tab .= "</tr>";
		$tab .= "<tr class=rowcontent>";
		$tab .= "<td align=right colspan=4><b>" . $_SESSION['lang']['total'] . "</td>";
		$tab .= "<td align=right><b>" . @number_format($tluasplan, 2) . "</td>";
		$tab .= "<td align=right><b>" . @number_format($tluaspanen, 2) . "</td>";
		$tab .= "<td align=right><b>" . @number_format($ttk, 2) . "</td>";
		$tab .= "<td align=right><b>" . @number_format($tjjgpnn) . "</td>";
		$tab .= "<td align=right><b></td>";
		$tab .= "<td align=right><b>" . @number_format($tkgkebun) . "</td>";
		$tab .= "<td align=right><b>" . @number_format($tjjgafkir) . "</td>";
		$tab .= "<td></td>";
		$tab .= "</tr>";
		$tab .= "</table>";
		$stream = $tab;
		$nop_ = "Laporan_Rekap_Panen" . date('Ymd_His');
		if (strlen($stream) > 0) {
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						@unlink('tempExcel/' . $file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
			if (!fwrite($handle, $stream)) {
				echo "<script language=javascript1.2>
                            parent.window.alert('Cant convert to excel format');
                            </script>";
				exit;
			} else {
				echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls';
                            </script>";
			}
			closedir($handle);
		}
		break;



	case 'getdata':
		$sql = "select * from " . $dbname . ".setup_blok where kodeorg = '" . $blok . "' and statusblok='TM'";
		$res = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		$thntnm = $bar['tahuntanam'];
		$luas = $bar['luasareaproduktif'];
		$tgl = tanggalnormal($tgl);
		$tgl = explode('-', $tgl);
		$tglbjr2 = $tgl[2] . "-" . $tgl[1];
		#BJR diambil dari setup BJR
		$str = "select bjr from " . $dbname . ".kebun_5bjr where kodeorg='" . $blok . "' and periode = '" . $tglbjr2 . "'";

		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while ($bar = $res->fetch()) {
			$bjr = $bar['bjr'];
		}
		if ($blok != '') {
			echo $thntnm . "##" . $luas . "##" . $bjr;
		}
		break;
	case 'previewexcelall':
		$tab = "<table border=1 class=sortable cellspacing='1' style='width:100%'>
			<thead><tr class=rowheader>
			<td align=center width=20px>No</td>
			<td align=center>No BAPP</td>
			<td align=center>No SPB</td>
			<td align=center>" . $_SESSION['lang']['kegiatan'] . "</td>
			<td align=center>" . $_SESSION['lang']['blok'] . "</td>
			<td align=center>Hasil Kerja</td>
			<td align=center>" . $_SESSION['lang']['jumlah'] . " (Rp)</td>
			";
		$tab .= "</tr>
		</thead>";

		$listSPB = '';
		$str = "SELECT *FROM $dbname.kebun_rekapangkutantbsht WHERE spk = '" . $spk . "' and tgldari = '" . $tglmulai . "' and tglsampai = '" . $tglselesai . "'";
		$res = fetchData($str);
		foreach ($res as $val) {
			if ($listSPB == '') {
				$listSPB = "'" . $val['nospb'] . "'";
			} else {
				$listSPB .= ",'" . $val['nospb'] . "'";
			}

			$listNobapp[$val['nospb']] = $val['nospb'];
		}

		if ($listSPB != '') {
			$whereIN = "and nospb in (" . $listSPB . ")";
		}

		$str = "SELECT *FROM $dbname.kebun_rekapangkutantbsdt WHERE 1=1 " . $whereIN . "";
		$res = fetchData($str);
		foreach ($res as $val) {
			$listData[$val['nospb']][$val['jeniskegiatan']][$val['blok']][$val['jenis']][$val['tujuan']] = $val['nospb'];

			$listKgtotal[$val['nospb']][$val['jeniskegiatan']][$val['blok']][$val['jenis']][$val['tujuan']] = $val['kgtotal'];
			$listKgwb[$val['nospb']][$val['jeniskegiatan']][$val['blok']][$val['jenis']][$val['tujuan']] = $val['kgwb'];
			$listPotonganrp[$val['nospb']][$val['jeniskegiatan']][$val['blok']][$val['jenis']][$val['tujuan']] = $val['potonganrp'];
			$listRupiah[$val['nospb']][$val['jeniskegiatan']][$val['blok']][$val['jenis']][$val['tujuan']] = $val['rupiah'];
		}

		$no = 0;
		foreach ($listData as $nospb => $bar1) {
			foreach ($bar1 as $jeniskegiatan => $bar2) {
				foreach ($bar2 as $blok => $bar3) {
					foreach ($bar3 as $jenis => $bar4) {
						foreach ($bar4 as $tujuan => $value) {
							$no += 1;
							$tab .= "<tr class=rowcontent>";
							$tab .= "<td align=center >" . $no . "</td>";
							$tab .= "<td align=center >" . $listNobapp[$nospb] . "</td>";
							$tab .= "<td align=center >" . $nospb . "</td>";
							$tab .= "<td align=center >" . getNamaKeg($jeniskegiatan) . "</td>";
							$tab .= "<td align=center >" . $blok . "</td>";
							$tab .= "<td align=right >" . number_format($listKgwb[$nospb][$jeniskegiatan][$blok][$jenis][$tujuan], 2) . "</td>";
							$tab .= "<td align=right >" . number_format($listRupiah[$nospb][$jeniskegiatan][$blok][$jenis][$tujuan], 2) . "</td>";
							$tab .= "</tr>";

							$totalKgwb += $listKgwb[$nospb][$jeniskegiatan][$blok][$jenis][$tujuan];
							$totalRuph += $listRupiah[$nospb][$jeniskegiatan][$blok][$jenis][$tujuan];
						}
					}
				}
			}
		}

		$tab .= "<tr class=rowcontent>";
		$tab .= "<td align=center colspan=5 style='background-color:#ccc'><b>" . strtoupper($_SESSION['lang']['total']) . "</b></td>";
		$tab .= "<td align=right style='background-color:#ccc'><b>" . number_format($totalKgwb, 2) . "<b></td>";
		$tab .= "<td align=right style='background-color:#ccc'><b>" . number_format($totalRuph, 2) . "<b></td>";
		$tab .= "</tr>";
		$tab .= "</table>";

		$stream = $tab;
		$nop_ = "Laporan_Rekap_Angkutan_TBS_PerBlokKecil_" . getNamaSupplier($optsup[$param['notransaksi']]);
		if (strlen($stream) > 0) {
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != "index.html") {
						@unlink('tempExcel/' . $file);
					}
				}
				closedir($handle);
			}
			$handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
			if (!fwrite($handle, $stream)) {
				echo "<script language=javascript1.2>
                            parent.window.alert('Cant convert to excel format');
                            </script>";
				exit;
			} else {
				echo "<script language=javascript1.2>
                            window.location='tempExcel/" . $nop_ . ".xls';
                            </script>";
			}
			closedir($handle);
		}
		break;
}
