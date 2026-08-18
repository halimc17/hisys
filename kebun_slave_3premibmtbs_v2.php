<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

//error_reporting(0);

# Cek Datakaryawan History
$jumlahkaryhist = 0;
$str = "select count(karyawanid) as jlh from " . $dbname . ".datakaryawan_hist where 5=5 and version_type='B' and lokasitugas='" . $unit . "' and periodegaji='" . $prd . "' ";
$res = fetchdata($str);
$jumlahkaryhist = $res[0]['jlh'];

$proses = checkPostGet('proses', '');
$unit = checkPostGet('unit', '');
$afd = checkPostGet('afd', '');
$prd = checkPostGet('prd', '');
$kontanan = checkPostGet('kontanan', '');
$tglkontan = tanggalsystemn(checkPostGet('tglkontan', ''));
$nmkeg = makeOption($dbname, 'setup_kegiatan', 'kodekegiatan,namakegiatan');

if ($jumlahkaryhist > 0) {
	$nmkar = makeOption($dbname, 'datakaryawan_hist', 'karyawanid,namakaryawan');
	$nikkar = makeOption($dbname, 'datakaryawan_hist', 'karyawanid,nik');
	$jabkar = makeOption($dbname, 'datakaryawan_hist', 'karyawanid,kodejabatan');
} else {
	$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
	$nikkar = makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
	$jabkar = makeOption($dbname, 'datakaryawan', 'karyawanid,kodejabatan');
}

$namatipe = makeOption($dbname, 'sdm_5tipekaryawan', 'id,tipe');

//Cek Periode gaji
$str = "select max(sudahproses) as prd from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $unit . "' and periode='" . $prd . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$prdgaji = $bar['prd'];
}
//Cek Periode akutansi
$str = "select * from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and periode='" . $prd . "'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$prdakt = $bar['tutupbuku'];
}


//Cek sudah ada yg posting belum
$postingxx = 0;
$str = "select posting from " . $dbname . ".kebun_3premibmtbs where kodeorg='" . $unit . "' and divisi='" . $afd . "' and periode='" . $prd . "' and posting='1' and kontanan='" . $kontanan . "' and notransaksi like '" . str_replace("-", "", $tglkontan) . "%'";
$res = fetchdata($str);
if (count($res) > 0) {
	//exit("Warning : Transaksi sudah ada yang di posting !!!");
	$postingxx = 1;
}

if ($afd == '' || $unit == '' || $prd == '') {
	exit("Warning : Tanggal, Unit Kerja dan Divisi wajib di isi !");
}

$where = '';
if ($kontanan == 'KONTAN') {
	$where = " and tanggal='" . $tglkontan . "'";
	if ($tglkontan == '--' or $tglkontan == '0000-00-00' or $tglkontan == '') {
		exit("Warning : Tanggal wajib diisi !");
	}
}


$ambilNoSpb = array();
# Ambil TK BM
$str = "select *,a.tanggal as tanggalx,a.kontanan as kontananx,b.tanggal as tanggalspb from " . $dbname . ".kebun_spbbm a 
        left join " . $dbname . ".kebun_spb_vw4 b on a.nospb=b.nospb
        where	a.tanggal like '" . $prd . "%'  and posting='1' 
        and b.kontanan='" . $kontanan . "' and a.karyawanid in (select karyawanid from datakaryawan_hist where lokasitugas = '" . $unit . "' and subbagian = '" . $afd . "' )  " . $where . "  order by a.tanggal asc, a.nospb,a.kegiatan, a.sesi asc";

$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {

	$tanggal[] = new DateTime($bar['tanggalspb']);
	$karyawanid[$bar['karyawanid']] = $bar['karyawanid'];
	#$listtgl[$bar['kerani']][$bar['karyawanid']][$bar['tanggal']]=$bar['tanggal'];

	$list[$bar['tanggalx']][$bar['nospb']] = 1;
	$tanggalSPB[$bar['nospb']] = $bar['tanggalspb'];
	$listtgl[$bar['tanggalx']][$bar['nospb']][$bar['kegiatan']][$bar['sesi']][$bar['karyawanid']][$bar['kontananx']] = $bar['kegiatan'];

	$jlhhk[$bar['karyawanid']][$bar['tanggalx']] = 1;
	$jlhkarykg[$bar['karyawanid']][$bar['nospb']][$bar['kegiatan']][$bar['sesi']] = floatval($bar['jjg_angkut']);
	$jlhkarybrd[$bar['karyawanid']][$bar['nospb']][$bar['kegiatan']][$bar['sesi']] = floatval($bar['brondolan_angkut']);
	@$jlhkary[$bar['nospb']][$bar['kegiatan']][$bar['sesi']] += floatval($bar['jjg_angkut']);

	$ambilNoSpb[$bar['nospb']] = $bar['nospb'];
}

$listNospb = "'" . implode("','", $ambilNoSpb) . "'";

# Ambil KG TImbangan
$str = "SELECT * FROM " . $dbname . ".kebun_spb_vw4 WHERE nospb in (" . $listNospb . ") AND posting = '1'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	@$kgwb[$bar['nospb']] += $bar['kgwb'];
	@$kgwbx[$bar['nospb']] += $bar['kgwb'];
	@$jjgwb[$bar['nospb']] += $bar['jjg'];
	@$brondolanspb[$bar['nospb']] += $bar['brondolan'];
	$jenis[$bar['nospb']] = $bar['kerani'];
}


# ambil basis dan harga
$str = "select * from " . $dbname . ".kebun_5premibmtbs where kodeorg ='" . $unit . "' and tanggalberlaku<='" . $prd . "-01'";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
	$rpkg[$bar['kegiatan']][$bar['jenispremi']] = $bar['harga'];
}

#cek transaksi belum posting
$row = '';
$str = "select distinct(nospb) as nospb, a.tanggal from " . $dbname . ".kebun_spbht a where	tanggal like '" . $prd . "%' and nospb like '%" . $afd . "%' and kodeorg='" . $unit . "' and posting='0' and a.kontanan='" . $kontanan . "'";
$res = fetchdata($str);
$row = count($res);
$ttp = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$ttp->setFetchMode(PDO::FETCH_ASSOC);
while ($dspb = $ttp->fetch()) {
	$tglx[substr($dspb['tanggal'], 8, 2)] = substr($dspb['tanggal'], 8, 2);
}

$stream = '';
$tglTemp = tglakhir($prd);
$tglTemp = str_replace('-', '', $tglTemp);

if ($kontanan == 'KERJA') {
	$newnotrans = $tglTemp . "/" . $afd . "/BM01/001";
} else {
	$tglTemp = str_replace("-", "", $tglkontan);
	$newnotrans = $tglTemp . "/" . $afd . "/BM01/002";
}

$stream .= "<table>";
if ($prdgaji == '0' || @$prdakt == '0' || $postingxx == 0) {
	$stream .= "<tr><td><b>" . $_SESSION['lang']['notransaksi'] . "</b></td><td><b>:</b></td>
						 <td ><input disabled class=myinputtext style=width:170px value='" . $newnotrans . "' id=notransaksi></td>
						 <td>Untuk <b>menyimpan</b> silahkan click tombol <b>Proses</b> di bawah.</td><td></td>
						 </tr>";
}
if ($row != '') {
	$stream .= "<tr><td colspan=10><font color=red>Info : Ada transaksi SPB yang belum di posting sebanyak = " . $row . " transaksi, tanggal : " . @implode(",", $tglx) . " " . substr(tanggalbulan($prd . "-01"), 3, 99) . "</font></td></tr>";
}
$stream .= "</table>";

if ($proses == 'excel') {
	$stream .= "<table class=sortable cellspacing=1 border=1>";
} else {
	$stream .= "<table class=sortable cellspacing=1 cellpadding= 5>";
}

$stream .= "<thead>";
$stream .= "<tr class=rowheader>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nospb'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['brondol'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['jjg'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['kg'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['bjr'] . "</td>";
$stream .= "</tr>";
$stream .= "<tr></tr>";
$stream .= "</thead>";

$cxlist = 0;
foreach ($list as $tanggal => $key) {
	foreach ($key as $spb => $jjg) {
		$cxlist++;
		$stream .= "<tr class=rowcontent>";
		$stream .= "<td align=center>" . $cxlist . "</td>";
		$stream .= "<td align=center>" . $spb . "</td>";
		$stream .= "<td align=center>" . $brondolanspb[$spb] . "</td>";
		$stream .= "<td align=center>" . $jjgwb[$spb] . "</td>";
		$stream .= "<td align=center>" . $kgwb[$spb] . "</td>";
		$stream .= "<td align=center>" . number_format((fixnan($kgwb[$spb] / $jjgwb[$spb])), 2) . "</td>";
		$stream .= "</tr>";
	}
}
$stream .= "</table></br>";

if ($proses == 'excel') {
	$stream .= "<table class=sortable cellspacing=1 border=1>";
} else {
	$stream .= "<table class=sortable cellspacing=1 cellpadding= 5>";
}


$stream .= "<thead>";
$stream .= "<tr class=rowheader>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nospb'] . "</td>";
$stream .= "<td align=center rowspan=2> Tanggal Spb</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['jenis'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['sesi'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nik2'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['namakaryawan'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['tipekaryawan'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['tanggal'] . " Muat</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['kontanan'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['jjg'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['bjr'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['kg'] . "</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['brondol'] . "</td>";
$stream .= "<td align=center rowspan=2>Jenis Hari</td>";
$stream .= "<td align=center rowspan=2>Rp/Kg</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['total'] . " Rp</td>";
$stream .= "<td align=center rowspan=2>" . $_SESSION['lang']['nilai'] . " 1 HK Rp</td>";
$stream .= "<td align=center rowspan=2 width=30px>" . $_SESSION['lang']['jumlahhk'] . "</td>";
$stream .= "<td align=center rowspan=2 width=75px>" . $_SESSION['lang']['rupiah'] . " HK</td>";
$stream .= "<td align=center rowspan=2 width=75px>" . $_SESSION['lang']['premlebihbasis'] . "</td>";
$stream .= "</tr>";
$stream .= "<tr></tr>";
$stream .= "</thead>";

if (@$karyawanid == '') {
	exit('Error ' . $_SESSION['lang']['errdatanotexist']);
}

$jmlhk = array();
$jumlahhk = array();
$nokar = $no = 0;
foreach ($listtgl as $ltgl => $key) {
	$jenispremi = getjenisharikerja($unit, $ltgl);
	if ($jenispremi == 'JUMAT') {
		$jenispremi = 'KERJA';
	}
	foreach ($key as $spb => $key2) {
		foreach ($key2 as $keg => $key3) {
			foreach ($key3 as $sesi => $key4) {
				foreach ($key4 as $kary => $key5) {

					// Cek Hist Karyawannya untuk membaca periode gaji apakah ada perubahan
					$cekHistNik = getCekHistKary($kary, substr($ltgl, 0, 7), "kodejabatan");
					$countHisNik =  count($cekHistNik);

					// Jika ada perubahan data history di periode gaji tersebut maka munculkan yang versi historynya
					if ($countHisNik == "1") {
						$jabkar[$kary] = $cekHistNik;
					}

					foreach ($key5 as $kontanan => $val) {

						if ($kontanan == '') {
							$kontanan = 'KERJA';
						}

						$arrtem = 0;

						$no++;
						$fontc = $bgclr = 0;

						if ($jumlahkaryhist > 0) {
							$tipekar = makeOption($dbname, 'datakaryawan_hist', 'karyawanid,tipekaryawan', "karyawanid='" . $kary . "'");
						} else {
							$tipekar = makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan', "karyawanid='" . $kary . "'");
						}

						# perhitungan premi untuk pemuat tbs berdasarkan jenis unit/kendaraan/mobil yang kedua dan seterusnya							
						# cek apakah ada kerja panen atau tidak ??

						$title[$kary][$ltgl] = '';
						$pnnx = " disabled ";
						$str = "select * from " . $dbname . ".kebun_prestasi_vs_hk where tanggal ='" . $ltgl . "' and karyawanid='" . $kary . "'";
						$pnn = fetchdata($str);
						if (count($pnn) > 0) {
							$pnnx = '';
							$title[$kary][$ltgl] .= " title=\"Sudah ada di kegiatan panen, dengan notransaksi : " . $pnn[0]['notransaksi'] . "\" style=background-color:#7FFF00;cursor:pointer;";
							$jmlhk[$kary][$ltgl] += $pnn[0]['nilaihk'];
						}

						$str = "select * from " . $dbname . ".kebun_kehadiran_vw where tanggal ='" . $ltgl . "' and karyawanid='" . $kary . "'";
						$pnn = fetchdata($str);
						if (count($pnn) > 0) {
							$pnnx = '';
							$title[$kary][$ltgl] .= " title=\"Sudah ada di kegiatan rawat, dengan notransaksi : " . $pnn[0]['notransaksi'] . "\" style=background-color:#7FFF00;cursor:pointer;";
							$jmlhk[$kary][$ltgl] += $pnn[0]['jhk'];
						}

						$str = "select * from " . $dbname . ".sdm_absensidt_vw where tanggal ='" . $ltgl . "' and karyawanid='" . $kary . "'";
						$pnn = fetchdata($str);
						if (count($pnn) > 0) {
							$pnnx = '';
							$title[$kary][$ltgl] .= " title=\"Sudah ada di kegiatan umum\" style=background-color:#7FFF00;cursor:pointer;";
							$jmlhk[$kary][$ltgl] += $pnn[0]['hkpanenperhari'];
						}

						$str = "select * from " . $dbname . ".vhc_runhk_vw where tanggal ='" . $ltgl . "' and idkaryawan='" . $kary . "' and upah > 0";
						$pnn = fetchdata($str);
						if (count($pnn) > 0) {
							$pnnx = '';
							$title[$kary][$ltgl] .= " title=\"Sudah ada di kegiatan traksi, dengan notransaksi : " . $pnn[0]['notransaksi'] . "\" style=background-color:#7FFF00;cursor:pointer;";
							$jmlhk[$kary][$ltgl] += $pnn[0]['hk'];
						}

						$str = "select * from " . $dbname . ".vhc_spl_kehadiran_vw where tanggal ='" . $ltgl . "' and nik ='" . $kary . "' and umr > 0";
						$pnn = fetchdata($str);
						if (count($pnn) > 0) {
							$pnnx = '';
							$title[$kary][$ltgl] .= " title=\"Sudah ada di kegiatan traksi, dengan notransaksi : " . $pnn[0]['notransaksi'] . "\" style=background-color:#7FFF00;cursor:pointer;";
							$jmlhk[$kary][$ltgl] += $pnn[0]['jhk'];
						}
					}
				}
			}
		}
	}
}

foreach ($listtgl as $ltgl => $key) {
	$jenispremi = getjenisharikerja($unit, $ltgl);

	if ($jenispremi == 'JUMAT') {
		$jenispremi = 'KERJA';
	}
	if ($jenispremi == 'LIBUR') {
		$jenispremix = getjenisharikerja($unit, $ltgl);

		if ($jenispremix == 'LIBUR NASIONAL') {
			$jenispremi = 'LIBUR NASIONAL';
		}
	}


	foreach ($key as $spb => $key2) {
		foreach ($key2 as $keg => $key3) {
			foreach ($key3 as $sesi => $key4) {
				foreach ($key4 as $kary => $key5) {
					foreach ($key5 as $kontanan => $val) {

						if ($kontanan == '') {
							$kontanan = 'KERJA';
						}

						$arrtem = 0;

						$no++;
						$fontc = $bgclr = 0;

						if ($jumlahkaryhist > 0) {
							$tipekar = makeOption($dbname, 'datakaryawan_hist', 'karyawanid,tipekaryawan', "karyawanid='" . $kary . "'");
						} else {
							$tipekar = makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan', "karyawanid='" . $kary . "'");
						}


						# ambil gajipokok
						$str = "select * from " . $dbname . ".sdm_5gajipokok where tahun ='" . $prd . "' and idkomponen='1' and karyawanid='" . $kary . "'";
						$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						$bar = $res->fetch();
						$gjperhari[$kary] = $bar['jumlah'] / 25;

						// Ambil 7 karakter pertama: 'YYYY-MM'
						// bandingin antara tanggal muat dan spb beda periode gak
						$ym1 = substr($tanggalSPB[$spb], 0, 7);
						$ym2 = substr($ltgl, 0, 7); // 2025-04

						// Cek apakah beda bulan/tahun


						$bjrx = $kgwb[$spb] / $jjgwb[$spb];

						$kgx = fixnan($bjrx * $jlhkarykg[$kary][$spb][$keg][$sesi]);
						$totalrupiah = (($kgx + $jlhkarybrd[$kary][$spb][$keg][$sesi]) * $rpkg[$keg][$jenispremi]);

						$premi[$keg][$kary][$ltgl][$sesi] = 0;
						if (!isset($jmlhk[$kary][$ltgl])) {
							$jmlhk[$kary][$ltgl] = 0;
						}

						// if($jabkar[$kary]!='16'){
						// 	$jmlhk[$kary][$ltgl]=1;
						// }

						if ($jenispremi == 'LIBUR') {
							$jmlhk[$kary][$ltgl] = 1;
						}

						if (!isset($jumlahhk[$spb][$keg][$kary][$ltgl][$sesi])) {
							$jumlahhk[$spb][$keg][$kary][$ltgl][$sesi] = 0;
						}



						if ($jmlhk[$kary][$ltgl] >= 1) {

							$jumlahhk[$spb][$keg][$kary][$ltgl][$sesi] = 0;
							$premi[$keg][$kary][$ltgl][$sesi] = $totalrupiah;
							$jmlhk[$kary][$ltgl] += $jumlahhk[$spb][$keg][$kary][$ltgl][$sesi];
						} else {
							if ($totalrupiah >= $gjperhari[$kary]) {
								if (($jmlhk[$kary][$ltgl] + 1) > 1) {
									$jumlahhk[$spb][$keg][$kary][$ltgl][$sesi] = (1 - $jmlhk[$kary][$ltgl]);
									$premi[$keg][$kary][$ltgl][$sesi] = (1 - $jmlhk[$kary][$ltgl]) * $totalrupiah;
								} else {
									$jumlahhk[$spb][$keg][$kary][$ltgl][$sesi] = 1;
									$premi[$keg][$kary][$ltgl][$sesi] = 0;
								}

								$jmlhk[$kary][$ltgl] += $jumlahhk[$spb][$keg][$kary][$ltgl][$sesi];
							} else {

								$hkex = $totalrupiah / $gjperhari[$kary];

								if (($jmlhk[$kary][$ltgl] + $hkex) >= 1) {
									$jumlahhk[$spb][$keg][$kary][$ltgl][$sesi] = (1 - $jmlhk[$kary][$ltgl]);
									$premi[$keg][$kary][$ltgl][$sesi] = (($hkex - (1 - $jmlhk[$kary][$ltgl]))) * $totalrupiah;
								} else {
									$jumlahhk[$spb][$keg][$kary][$ltgl][$sesi] = $hkex;
									$premi[$keg][$kary][$ltgl][$sesi] = 0;
								}

								$jmlhk[$kary][$ltgl] += $jumlahhk[$spb][$keg][$kary][$ltgl][$sesi];
							}
						}
					}
				}
			}
		}
	}
}

foreach ($listtgl as $ltgl => $key) {
	$jenispremi = getjenisharikerja($unit, $ltgl);

	if ($jenispremi == 'JUMAT') {
		$jenispremi = 'KERJA';
	}
	if ($jenispremi == 'LIBUR') {
		$jenispremix = getjenisharikerja($unit, $ltgl);

		if ($jenispremix == 'LIBUR NASIONAL') {
			$jenispremi = 'LIBUR NASIONAL';
		}
	}

	foreach ($key as $spb => $key2) {
		foreach ($key2 as $keg => $key3) {
			foreach ($key3 as $sesi => $key4) {
				foreach ($key4 as $kary => $key5) {
					foreach ($key5 as $kontanan => $val) {

						if ($kontanan == '') {
							$kontanan = 'KERJA';
						}

						$arrtem = 0;

						$no++;
						$fontc = $bgclr = 0;

						if ($jumlahkaryhist > 0) {
							$tipekar = makeOption($dbname, 'datakaryawan_hist', 'karyawanid,tipekaryawan', "karyawanid='" . $kary . "'");
						} else {
							$tipekar = makeOption($dbname, 'datakaryawan', 'karyawanid,tipekaryawan', "karyawanid='" . $kary . "'");
						}



						# ambil gajipokok
						$str = "select * from " . $dbname . ".sdm_5gajipokok where tahun ='" . $prd . "' and idkomponen='1' and karyawanid='" . $kary . "'";
						$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
						$res->setFetchMode(PDO::FETCH_ASSOC);
						$bar = $res->fetch();
						$gjperhari[$kary] = $bar['jumlah'] / 25;

						// Ambil 7 karakter pertama: 'YYYY-MM'
						// bandingin antara tanggal muat dan spb beda periode gak
						$ym1 = substr($tanggalSPB[$spb], 0, 7);
						$ym2 = substr($ltgl, 0, 7); // 2025-04

						// Cek apakah beda bulan/tahun
						if ($ym1 !== $ym2) {
							$stylex = 'style="color:red;"';
						} else {
							$stylex = '';
						}

						$nokar++;
						$stream .= "<tr class=rowcontent " . $bgclr . " " . $title[$kary][$ltgl] . " id=row" . $no . ">";
						$stream .= "<td align=center>" . $nokar . "</td>";
						$stream .= "<td align=center id=rownospb_" . $no . ">" . $spb . "</td>";
						$stream .= "<td align=center " . $fontc . " " . $stylex . " >" . $tanggalSPB[$spb] . "</td>";
						if ($proses != 'excel') {
							$stream .= "<td hidden id=rowkeg_" . $no . ">" . $keg . "</td>";
						}

						$stream .= "<td>" . $keg . " - " . $nmkeg[$keg] . "</td>";
						if ($proses != 'excel') {
							$stream .= "<td hidden id=rowkary_" . $no . ">" . $kary . "</td>";
						}

						$stream .= "<td id=rowsesi_" . $no . ">" . $sesi . "</td>";
						$stream .= "<td>" . $nikkar[$kary] . "</td>";
						$stream .= "<td>" . $nmkar[$kary] . "</td>";
						$stream .= "<td align=center>" . $namatipe[$tipekar[$kary]] . "</td>";

						if ($proses != 'excel') {
							$stream .= "<td hidden id=rowtgl_" . $no . ">" . $ltgl . "</td>";
						}

						if ($rpkg[$keg][$jenispremi] == '') {
							$SS = "style='background-color:red;cursor:pointer;' title='Setup Kosong !''";
						} else {
							$SS = '';
						}

						$bjrx = fixnan($kgwb[$spb] / $jjgwb[$spb]);
						$kgx = fixnan($bjrx * $jlhkarykg[$kary][$spb][$keg][$sesi]);
						$totalrupiah = (($kgx + $jlhkarybrd[$kary][$spb][$keg][$sesi]) * $rpkg[$keg][$jenispremi]);

						$stream .= "<td><font " . $fontc . " " . $stylex . ">" . $ltgl . "</font></td>";
						$stream .= "<td align=center id=kontanan_" . $no . ">" . $kontanan . "</td>";
						$stream .= "<td align=right id=jjgkry_" . $no . ">" . number_format($jlhkarykg[$kary][$spb][$keg][$sesi]) . "</td>";
						$stream .= "<td align=right id=bjrwb_" . $no . ">" . number_format($bjrx, 2) . "</td>";
						$stream .= "<td align=right id=rowkgwb_" . $no . ">" . number_format($kgx, 2) . "</td>";

						$stream .= "<td align=right id=brondkry_" . $no . ">" . number_format($jlhkarybrd[$kary][$spb][$keg][$sesi]) . "</td>";

						$stream .= "<td align=center >" . $jenispremi . "</td>";
						$stream .= "<td " . $SS . " align=right id=rpkg" . $no . ">" . $rpkg[$keg][$jenispremi] . "</td>";
						$stream .= "<td align=right id=totalrupiah" . $no . ">" . number_format($totalrupiah, 2) . "</td>";
						$stream .= "<td align=right id=nilai1hk_" . $no . ">" . number_format($gjperhari[$kary]) . "</td>";

						if (!isset($jmlhk2[$kary][$ltgl]) and $jmlhk[$kary][$ltgl] < 1) {
							$jmlhk2[$kary][$ltgl] = ceil($jmlhk[$kary][$ltgl] * 100) / 100;
						}

						if ($jmlhk2[$kary][$ltgl] > 0) {
							$jumlahhk[$spb][$keg][$kary][$ltgl][$sesi] = ceil($jumlahhk[$spb][$keg][$kary][$ltgl][$sesi] * 100) / 100;
							if ($jmlhk2[$kary][$ltgl] > $jumlahhk[$spb][$keg][$kary][$ltgl][$sesi]) {
								$jmlhk2[$kary][$ltgl] = $jmlhk2[$kary][$ltgl] - $jumlahhk[$spb][$keg][$kary][$ltgl][$sesi];
							} else {
								$jumlahhk[$spb][$keg][$kary][$ltgl][$sesi] = $jmlhk2[$kary][$ltgl];
								$jmlhk2[$kary][$ltgl] = 0;
							}
						}

						if ($jmlhk[$kary][$ltgl] >= 1) {
							$jumlahhk[$spb][$keg][$kary][$ltgl][$sesi] = round($jumlahhk[$spb][$keg][$kary][$ltgl][$sesi], 2);
						}
						$stream .= "<td align=right  id=rowhk_" . $no . ">" . formatAngka($jumlahhk[$spb][$keg][$kary][$ltgl][$sesi]) . "</td>";

						$hajiperkary = (formatAngka($jumlahhk[$spb][$keg][$kary][$ltgl][$sesi]) * $gjperhari[$kary]);
						if ($jmlhk[$kary][$ltgl] >= 1) {
							$premi[$keg][$kary][$ltgl][$sesi] = $totalrupiah - $hajiperkary;
						}

						if ($premi[$keg][$kary][$ltgl][$sesi] <= 0) {
							$premi[$keg][$kary][$ltgl][$sesi] = 0;
						}

						$stream .= "<td align=right >" . hidezerodecimal($hajiperkary, 2) . "</td>";
						$stream .= "<td align=right >" . hidezerodecimal($premi[$keg][$kary][$ltgl][$sesi], 2) . "</td>";
						if ($proses != 'excel') {
							$stream .= "<td align=right hidden id=rowrphk_" . $no . ">" . $hajiperkary . "</td>";
							$stream .= "<td align=right hidden id=rowrpprmi_" . $no . ">" . $premi[$keg][$kary][$ltgl][$sesi] . "</td>";
						}




						$stream .= "</tr>";

						@$ttljjg[$spb][$keg][$sesi] += $jlhkarykg[$kary][$spb][$keg][$sesi];
						@$ttlbrd[$spb][$keg][$sesi] += $jlhkarybrd[$kary][$spb][$keg][$sesi];
						@$ttlkg[$spb][$keg][$sesi] += $kgx;
						@$ttlrp[$spb][$keg][$sesi] += $totalrupiah;
						@$ttlhk[$spb][$keg][$sesi] += $jumlahhk[$spb][$keg][$kary][$ltgl][$sesi];
						@$ttlgj[$spb][$keg][$sesi] += $hajiperkary;
						@$ttlpremi[$spb][$keg][$sesi] += $premi[$keg][$kary][$ltgl][$sesi];
					}
				}
				$stream .= "<tr class=rowcontent>";
				$stream .= "<td align=center colspan=10 bgcolor=cyan><b>Total</b></td>";
				$stream .= "<td align=right bgcolor=cyan><b>" . hidezerodecimal($ttljjg[$spb][$keg][$sesi], 2) . "</b></td>";
				$stream .= "<td align=right bgcolor=cyan></td>";
				$stream .= "<td align=right bgcolor=cyan><b>" . hidezerodecimal($ttlkg[$spb][$keg][$sesi], 2) . "</b></td>";
				$stream .= "<td align=right bgcolor=cyan><b>" . hidezerodecimal($ttlbrd[$spb][$keg][$sesi], 2) . "</b></td>";
				$stream .= "<td align=right bgcolor=cyan></td>";
				$stream .= "<td align=right bgcolor=cyan><b>" . hidezerodecimal($ttlrp[$spb][$keg][$sesi], 2) . "</b></td>";
				$stream .= "<td align=right bgcolor=cyan></td>";
				$stream .= "<td align=right bgcolor=cyan></td>";
				$stream .= "<td align=right bgcolor=cyan><b>" . hidezerodecimal($ttlhk[$spb][$keg][$sesi], 2) . "</b></td>";
				$stream .= "<td align=right bgcolor=cyan><b>" . hidezerodecimal($ttlgj[$spb][$keg][$sesi]) . "</b></td>";
				$stream .= "<td align=right bgcolor=cyan id=ttlkglb><b>" . hidezerodecimal($ttlpremi[$spb][$keg][$sesi], 2) . "</b></td>";
				$stream .= "</tr>";
			}
		}
	}
}

$stream .= "</table></br>";
if ($proses != 'excel') {
	$stream .= "<button class=mybutton onclick=deleteTrans(" . $no . ");>" . $_SESSION['lang']['proses'] . "</button>";
}

switch ($proses) {

	case 'preview':
		echo $stream;
		break;

	######EXCEL	
	case 'excel':
		$stream .= "Print Time : " . date('H:i:s, d/m/Y') . "<br>By : " . $_SESSION['empl']['name'];
		$tglSkrg = date("Ymd");
		$nop_ = "daftar_premi_bmtbs";
		if (strlen($stream) > 0) {
			if ($handle = opendir('tempExcel')) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
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
		break;

	default:
}



function formatAngka($angka, $digit = 8)
{
	if (is_infinite($angka) || abs($angka) < pow(10, -$digit)) {
		return '0';
	} else {
		return $angka;
	}
}
