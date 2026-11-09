<?php
/**
 * Slave PPh Pasal 21 v3  (SDM > Proses > PPh Pasal 21)
 *
 * proses=preview    : tampilkan perhitungan (tipe=excel -> unduh Excel)
 *                     - Jan-Nov  : PPh 21 = penghasilan bruto bulan berjalan x TER (kategori A/B/C)
 *                     - Desember : PPh 21 setahun (tarif Pasal 17) dikurangi PPh 21 Jan-Nov
 * proses=prosespph  : simpan hasil per karyawan ke sdm_gaji (komponen 42 / 153) dan sdm_pph21
 */

ini_set('display_errors', 0);
error_reporting(0);

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/HtmlExcel.php');

#=== Parameter (teks di-escape, angka dibersihkan) ===
$proses       = checkPostGet('proses', '');
$per          = addslashes(trim(checkPostGet('per', '')));
$unit         = addslashes(trim(checkPostGet('unit', '')));
$karyawanid   = addslashes(trim(checkPostGet('karyawanid', '')));
$tipe         = checkPostGet('tipe', '');
$baris        = checkPostGet('baris', '');
$pph21x       = pphAngka(checkPostGet('pph21x', ''));
$tjpph21x     = pphAngka(checkPostGet('tjpph21x', ''));

switch ($proses) {
	case 'prosespph':
		pphCekInput($per, $unit);
		pphSimpan($per, $unit, $karyawanid, $baris, $pph21x, $tjpph21x);
		break;

	case 'preview':
		pphCekInput($per, $unit);
		pphPreview($per, $unit, $tipe);
		break;

	default:
		break;
}


#=== Fungsi bantu ===

/** Angka dari input: koma ribuan dibuang, bukan angka -> 0 */
function pphAngka($nilai)
{
	$nilai = str_replace(',', '', $nilai);
	return is_numeric($nilai) ? $nilai : 0;
}

/** Validasi input: unit wajib & termasuk unit yang boleh diakses user, periode format yyyy-mm */
function pphCekInput($per, $unit)
{
	if ($unit == '') {
		exit('Error, Unit belum dipilih.');
	}
	if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $per)) {
		exit('Error, Periode belum dipilih atau tidak valid.');
	}
	$unitBoleh = getOrgDetail(1);
	if (!isset($unitBoleh[$unit])) {
		exit('Error, Unit ' . htmlspecialchars($unit) . ' tidak boleh diakses.');
	}
}

/**
 * Status tutup buku periode: gaji harus sudah ditutup dan periode akuntansi belum ditutup
 * agar PPh 21 boleh diproses. Mengembalikan array pesan + flag boleh proses.
 */
function pphStatusPeriode($per, $unit)
{
	global $dbname;

	$gaji = pphAmbilSemua("select sudahproses from " . $dbname . ".sdm_5periodegaji where kodeorg='" . $unit . "' and periode='" . $per . "'");
	$gajiAda = count($gaji) > 0;
	$gajiTutup = $gajiAda;
	foreach ($gaji as $bar) {
		if ($bar['sudahproses'] == 0) {
			$gajiTutup = false;
		}
	}

	$akun = pphAmbilSemua("select tutupbuku from " . $dbname . ".setup_periodeakuntansi where kodeorg='" . $unit . "' and periode='" . $per . "'");
	$akunAda = count($akun) > 0;
	$akunTutup = $akunAda && $akun[0]['tutupbuku'] == 1;

	$pesan = array();
	$pesan[] = $gajiTutup ? array('ok', 'Periode gaji ' . $per . ' sudah ditutup (OK)')
		: array('salah', $gajiAda ? 'Periode gaji ' . $per . ' BELUM ditutup, tutup dulu di SDM - Setup - Periode Penggajian Unit' : 'Periode gaji ' . $per . ' belum ada untuk unit ini');
	$pesan[] = (!$akunTutup) ? array('ok', 'Periode akuntansi ' . $per . ' belum ditutup (OK)')
		: array('salah', 'Periode akuntansi ' . $per . ' SUDAH ditutup, PPh 21 tidak bisa diproses ulang');

	return array('boleh' => ($gajiTutup && !$akunTutup), 'pesan' => $pesan, 'gajiAda' => $gajiAda, 'gajiTutup' => $gajiTutup, 'akunTutup' => $akunTutup);
}

/** Bulan Januari-November yang belum ada PPh 21 (komponen 42) di unit ini; dipakai peringatan di Desember */
function pphBulanBelumDiproses($per, $unit)
{
	global $dbname;

	$tahun = substr($per, 0, 4);
	$ada = array();
	foreach (pphAmbilSemua("select distinct periodegaji from " . $dbname . ".sdm_gaji where kodeorg='" . $unit . "' and idkomponen='42' and periodegaji >= '" . $tahun . "-01' and periodegaji <= '" . $tahun . "-11'") as $bar) {
		$ada[$bar['periodegaji']] = true;
	}
	$kosong = array();
	for ($bln = 1; $bln <= 11; $bln++) {
		$periode = $tahun . '-' . sprintf('%02d', $bln);
		if (!isset($ada[$periode])) {
			$kosong[] = $periode;
		}
	}
	return $kosong;
}

/** Jalankan query; jika gagal tampilkan pesan umum (detail ke log, tidak ke layar) */
function pphQuery($sql)
{
	global $owlPDO;
	try {
		return $owlPDO->query($sql);
	} catch (PDOException $e) {
		error_log('sdm_slave_3pph21_v3: ' . $e->getMessage());
		exit('Error, terjadi kesalahan pada database.');
	}
}

/** Semua baris hasil query sebagai array asosiatif */
function pphAmbilSemua($sql)
{
	$res = pphQuery($sql);
	$res->setFetchMode(PDO::FETCH_ASSOC);
	return $res->fetchAll();
}

/**
 * Karyawan dianggap punya NPWP bila NPWP terisi valid atau NIK (No. KTP) 16 digit,
 * karena sejak 2024 NIK berfungsi sebagai NPWP orang pribadi. Tanpa keduanya: PPh 21 lebih tinggi 20%.
 */
function pphPunyaNpwp($npwp, $noktp)
{
	$npwp = trim((string)$npwp);
	if ($npwp != '' && $npwp != '00.000.000.0-000.000') {
		return true;
	}
	$nik = preg_replace('/[^0-9]/', '', (string)$noktp);
	return strlen($nik) == 16;
}

/** PPh 21 setahun dari PKP memakai lapisan tarif Pasal 17 (batas atas, penambah, persen per lapisan) */
function pphHitungPasal17($pkp, $batas, $penambah, $persen)
{
	if ($pkp <= 0) {
		return 0;
	}
	$jumlah = count($batas);
	for ($i = 0; $i < $jumlah; $i++) {
		if ($i == $jumlah - 1 || $pkp <= $batas[$i]) {
			if ($i == 0) {
				return $pkp * $persen[0];
			}
			return $penambah[$i] + ($pkp - $batas[$i - 1]) * $persen[$i];
		}
	}
	return 0;
}


#=== Simpan hasil PPh 21 satu karyawan ===
function pphSimpan($per, $unit, $karyawanid, $baris, $pph21x, $tjpph21x)
{
	global $owlPDO, $dbname;

	try {
		$owlPDO->beginTransaction();

		## Validasi tutup buku: gaji sudah ditutup, periode akuntansi belum ditutup
		$statusPeriode = pphStatusPeriode($per, $unit);
		foreach ($statusPeriode['pesan'] as $pesan) {
			if ($pesan[0] == 'salah') {
				throw new PDOException($pesan[1] . '.');
			}
		}

		## Baris pertama: hapus hasil proses sebelumnya untuk unit & periode ini
		if ($baris == '1') {
			$owlPDO->exec("delete from " . $dbname . ".sdm_gaji where kodeorg='" . $unit . "' and idkomponen='42' and periodegaji='" . $per . "'");
			$owlPDO->exec("delete from " . $dbname . ".sdm_gaji where kodeorg='" . $unit . "' and idkomponen='153' and periodegaji='" . $per . "'");
			$owlPDO->exec("delete from " . $dbname . ".sdm_pph21 where kodeorg='" . $unit . "' and periode='" . $per . "'");
		}

		## Potongan PPh 21
		if ($pph21x != 0) {
			$owlPDO->exec("insert into " . $dbname . ".sdm_gaji (`karyawanid`,`kodeorg`,`idkomponen`,`jumlah`,`pengali`,`hk`,`periodegaji`) values ('" . $karyawanid . "','" . $unit . "','42','" . $pph21x . "','1','0','" . $per . "') ");
			$owlPDO->exec("insert into " . $dbname . ".sdm_pph21 (`karyawanid`,`kodeorg`,`tipepph`,`nilai`,`periode`) values ('" . $karyawanid . "','" . $unit . "','1','" . $pph21x . "','" . $per . "') ");
		}

		## Tunjangan PPh 21
		if ($tjpph21x != 0) {
			$owlPDO->exec("insert into " . $dbname . ".sdm_gaji (`karyawanid`,`kodeorg`,`idkomponen`,`jumlah`,`pengali`,`hk`,`periodegaji`) values ('" . $karyawanid . "','" . $unit . "','153','" . $tjpph21x . "','1','0','" . $per . "') ");
		}

		$owlPDO->commit();
	} catch (PDOException $e) {
		$owlPDO->rollback();
		echo "Error, " . addslashes($e->getMessage());
		die();
	}
}


#=== Tampilan / Excel perhitungan PPh 21 ===
function pphPreview($per, $unit, $tipe)
{
	global $dbname;

	#--- 1. Pastikan gaji periode ini sudah diproses
	$cek = pphAmbilSemua("select count(*) as jmlhrow from " . $dbname . ".sdm_gaji where periodegaji = '" . $per . "' and kodeorg = '" . $unit . "'");
	if ($cek[0]['jmlhrow'] == 0) {
		exit("Warning : Data gaji tidak ada untuk periode " . $per . " di unit " . $unit . ", Silahkan proses gaji dan tutup periode penggajian...");
	}

	#--- 2. Pengaturan: biaya jabatan, PTKP, lapisan tarif Pasal 17
	$rowJabatan    = @pphAmbilSemua("select * from " . $dbname . ".sdm_ho_pph21jabatan");
	$persenjabatan = @$rowJabatan[0]['persen'];
	$maxjabatan    = @$rowJabatan[0]['max'];

	$ptkpdata = array();
	foreach (pphAmbilSemua("select id,status,ptkp from " . $dbname . ".sdm_5ptkp_pph21") as $bar) {
		$ptkpdata[$bar['status']] = $bar['ptkp'];
	}

	$pphbatas = array();
	$pphpenambah = array();
	$pphpersen = array();
	foreach (pphAmbilSemua("select level,percent,upto,penambah from " . $dbname . ".sdm_ho_pph21_kontribusi order by level") as $bar) {
		$pphbatas[]    = $bar['upto'];
		$pphpenambah[] = $bar['penambah'];
		$pphpersen[]   = $bar['percent'] / 100;
	}

	#--- 3. Daftar karyawan (histori bulanan bila ada, selain itu data karyawan saat ini)
	$adaHistori = pphAmbilSemua("select karyawanid from " . $dbname . ".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='" . $unit . "' and periodegaji='" . $per . "' and statuspajak <> '' and tipekaryawan !=0 ");
	if (count($adaHistori) > 0) {
		$sqlKaryawan = "select * from " . $dbname . ".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='" . $unit . "' and periodegaji='" . $per . "' and statuspajak <> ''  and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and tipekaryawan !=0";
	} else {
		$sqlKaryawan = "select * from " . $dbname . ".datakaryawan where lokasitugas='" . $unit . "' and statuspajak <> '' and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and tipekaryawan !=0 ";
	}

	#--- 4. Komponen gaji: yang tidak masuk perhitungan PPh 21 dikeluarkan
	$daftarKecuali = array();
	# Pengecualian: iuran JHT (67) dan JP (81) yang dibayar karyawan boleh mengurangi penghasilan neto (Desember),
	# walau komponennya ditandai tidak masuk perhitungan PPh 21 di setup komponen
	$komponenPengurangPajak = "'67','81'";
	foreach (pphAmbilSemua("select id from " . $dbname . ".sdm_ho_component where pph21='0' and id not in (" . $komponenPengurangPajak . ")") as $bar) {
		$daftarKecuali[] = "'" . $bar['id'] . "'";
	}
	$expph = count($daftarKecuali) > 0 ? implode(',', $daftarKecuali) : "''";

	$bulan = substr($per, 5, 2);
	$tahunberjalan = substr($per, 0, 4);

	$dtkomplus = array();   # komponen pendapatan yang muncul
	$dtkomin = array();     # komponen pengurang yang muncul
	$rupiah = array();      # [karyawanid][idkomponen] => jumlah
	$pph21_jan_nov = array();

	if ($bulan == '12') {
		## Desember: seluruh komponen Jan-Des
		$sqlKomponen = "select a.*,plus from " . $dbname . ".sdm_gaji a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id
			where 1=1 and idkomponen not in (" . $expph . ") and (periodegaji <= '" . $per . "' and periodegaji >= '" . $tahunberjalan . "-01" . "') and kodeorg='" . $unit . "' and jumlah > 0";
	} else {
		## Jan-Nov: komponen periode berjalan
		$sqlKomponen = "select a.*,plus from " . $dbname . ".sdm_gaji a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id
			where 1=1 and idkomponen not in (" . $expph . ") and periodegaji = '" . $per . "' and kodeorg='" . $unit . "' and jumlah > 0 ";
	}
	foreach (pphAmbilSemua($sqlKomponen) as $bar) {
		if ($bar['plus'] == 1) {
			$dtkomplus[$bar['idkomponen']] = $bar['idkomponen'];
		} else {
			$dtkomin[$bar['idkomponen']] = $bar['idkomponen'];
		}
		if (!isset($rupiah[$bar['karyawanid']][$bar['idkomponen']])) {
			$rupiah[$bar['karyawanid']][$bar['idkomponen']] = 0;
		}
		$rupiah[$bar['karyawanid']][$bar['idkomponen']] += $bar['jumlah'];
	}

	if ($bulan == '12') {
		## PPh 21 yang sudah dipotong Jan-Nov (komponen 42)
		$sqlJanNov = "select a.*,plus from " . $dbname . ".sdm_gaji a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id
			where 1=1 and idkomponen = '42' and (periodegaji <= '" . $tahunberjalan . "-11" . "' and periodegaji >= '" . $tahunberjalan . "-01" . "') and kodeorg='" . $unit . "' and jumlah > 0 ";
		foreach (pphAmbilSemua($sqlJanNov) as $bar) {
			if (!isset($pph21_jan_nov[$bar['karyawanid']])) {
				$pph21_jan_nov[$bar['karyawanid']] = 0;
			}
			$pph21_jan_nov[$bar['karyawanid']] += $bar['jumlah'];
		}
	}

	#--- 5. Data pendukung tampilan
	$nmgolongan    = makeOption($dbname, 'sdm_5golongan', 'kodegolongan,namagolongan');
	$nmjabatan     = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
	$optNmKomponen = makeOption($dbname, 'sdm_ho_component', 'id,name');
	$optKategori   = makeOption($dbname, 'sdm_5tarifpph21', 'status,kategori');

	$dtkarid = array();
	$nik = array();
	$nmkar = array();
	$golongan = array();
	$statuspajak = array();
	$bagian = array();
	$jabatan = array();
	$ptkpnya = array();
	$punyaNpwp = array();
	foreach (pphAmbilSemua($sqlKaryawan) as $bar) {
		$id = $bar['karyawanid'];
		$dtkarid[$id]      = $id;
		$nik[$id]          = $bar['nik'];
		$nmkar[$id]        = $bar['namakaryawan'];
		$golongan[$id]     = $bar['kodegolongan'];
		$statuspajak[$id]  = $bar['statuspajak'];
		$bagian[$id]       = $bar['bagian'];
		$jabatan[$id]      = $bar['kodejabatan'];
		$ptkpnya[$id]      = @$ptkpdata[$bar['statuspajak']];
		$punyaNpwp[$id]    = pphPunyaNpwp(@$bar['npwp'], @$bar['noktp']);
	}

	$jmlKomplus = count($dtkomplus);
	$jmlKomin   = count($dtkomin);
	$styleUpper = "style='text-transform: uppercase;'";
	$adaExcel   = ($tipe == 'excel' || $tipe == 'pdf');   # tanpa elemen khusus layar

	#--- 6. Huruf kolom hasil hitung (dipakai di judul & rumus), urut sesuai tampilan
	$urutHuruf = 0;
	$huruf = function () use (&$urutHuruf) {
		return chr(65 + $urutHuruf++);
	};
	if ($bulan == '12') {
		$hBruto = $huruf();
		$hTj = $huruf();
		$hBiaya = $huruf();
		if ($jmlKomin > 0) {
			$hPeng = $huruf();
			$hNeto = $huruf();
		}
		$hPtkp = $huruf();
		$hPkp = $huruf();
		$hPkpBulat = $huruf();
		$hPphSetahun = $huruf();
		$hTambah = $huruf();
		$hTerhutang = $huruf();
		$hTahunan = $huruf();
		$hJanNov = $huruf();
		$hFinal = $huruf();
		$rumusNeto = $hBruto . ' + ' . $hTj . ' - ' . $hBiaya . ($jmlKomin > 0 ? ' - ' . $hPeng : '');
	} else {
		$hPend = $huruf();
		$hTj = $huruf();
		if ($jmlKomin > 0) {
			$hPeng = $huruf();
			$hNeto = $huruf();
		}
		$hBruto = $huruf();
		$hKategori = $huruf();
		$hTer = $huruf();
		$hFinal = $huruf();
	}

	#--- 7. Keterangan cara hitung dan header tabel
	$stream = '';
	if ($adaExcel) {
		$stream .= "<table class=sortable cellspacing=1 border=1>";
	} else {
		$stream .= "<style>
			#tabelpph21 tbody tr:nth-child(even) td {background-color:#F3F8FC}
			#tabelpph21 tbody tr:hover td {background-color:#FFF7CC}
			#tabelpph21 .rumus {display:block;font-weight:normal;font-size:11px;text-transform:none;color:#FFE699}
			#tabelpph21 td {white-space:nowrap}
		</style>";
		if ($bulan == '12') {
			$caraHitung = "Cara hitung Desember: PPh 21 setahun (neto - PTKP, tarif Pasal 17) dikurangi PPh 21 Januari-November.";
		} else {
			$caraHitung = "Cara hitung Januari-November: PPh 21 = bruto bulan ini x tarif efektif rata-rata (TER) kategori A/B/C.";
		}
		$stream .= "<div style='margin:0 2px 6px 2px;font-size:11px;color:#555'>" . $caraHitung . " Kolom berhuruf dihitung dengan rumus di bawah judulnya.</div>";
		$stream .= "<div class='table-scroll' style='height:900px; overflow-y:auto;'><table id=tabelpph21 class=sortable cellspacing=1 cellpadding=5 style='width:100%;'>";
	}

	# th bentuk 1: <th align=center style rowspan/colspan>
	$th = function ($teks, $atribut) use ($styleUpper) {
		return "<th align=center " . $styleUpper . " " . $atribut . ">" . $teks . "</th>";
	};
	# th kolom hasil hitung: (huruf) judul + rumus di bawahnya
	$thRumus = function ($judul, $hrf, $rumus, $atribut) use ($styleUpper, $adaExcel) {
		$rumusHtml = $adaExcel ? "<br>" . $rumus : "<span class=rumus>" . $rumus . "</span>";
		return "<th align=center " . $styleUpper . " " . $atribut . ">(" . $hrf . ") " . $judul . $rumusHtml . "</th>";
	};
	# th bentuk 2 (nama komponen): <th style align=center rowspan=2>
	$thKomponen = function ($teks) use ($styleUpper) {
		return "<th " . $styleUpper . " align=center rowspan=2>" . $teks . "</th>";
	};

	$stream .= "<thead>";
	$stream .= "<tr class=rowcontent>";
	$stream .= $th('NO', 'rowspan=3');
	$stream .= $th(' NIK ', 'rowspan=3');
	$stream .= $th($_SESSION['lang']['namakaryawan'], 'rowspan=3');
	$stream .= $th('Golongan', 'rowspan=3');
	$stream .= $th($_SESSION['lang']['statuspajak'], 'rowspan=3');
	$stream .= $th($_SESSION['lang']['bagian'], 'rowspan=3');
	$stream .= $th($_SESSION['lang']['jabatan'], 'rowspan=3');
	$stream .= $th('PENDAPATAN', 'colspan=' . $jmlKomplus);

	if ($bulan == '12') {
		$stream .= $thRumus('GAJI BRUTO SETAHUN', $hBruto, 'Jumlah pendapatan Jan-Des', 'rowspan=3');
		$stream .= $thRumus('TJ PPh 21', $hTj, 'Tunjangan pajak', 'rowspan=3');
		$stream .= $thRumus('BIAYA JABATAN', $hBiaya, number_format($persenjabatan) . '% x (' . $hBruto . ' + ' . $hTj . '), maks ' . number_format($maxjabatan * 12) . '/th', 'rowspan=3');
	} else {
		$stream .= $thRumus('TOTAL PENDAPATAN', $hPend, 'Jumlah pendapatan bulan ini', 'rowspan=3');
		$stream .= $thRumus('TJ PAJAK', $hTj, 'Tunjangan pajak', 'rowspan=3');
	}

	if ($jmlKomin > 0) {
		$stream .= $th($bulan == '12' ? 'PENGURANG PAJAK' : 'PENGURANG', 'colspan=' . $jmlKomin);
		$stream .= $thRumus('TOTAL PENGURANG', $hPeng, 'Jumlah pengurang', 'rowspan=3');
		if ($bulan == '12') {
			$stream .= $thRumus('PENGHASILAN NETO', $hNeto, $rumusNeto, 'rowspan=3');
		} else {
			$stream .= $thRumus('TOTAL GAJI NETTO', $hNeto, $hPend . ' + ' . $hTj . ' - ' . $hPeng, 'rowspan=3');
		}
	}

	if ($bulan == '12') {
		$rumusPkp = ($jmlKomin > 0 ? $hNeto : '(' . $rumusNeto . ')') . ' - ' . $hPtkp;
		$stream .= $thRumus('PTKP', $hPtkp, 'Sesuai status pajak', 'rowspan=3');
		$stream .= $thRumus('PKP', $hPkp, $rumusPkp, 'rowspan=3');
		$stream .= $thRumus('PKP PEMBULATAN', $hPkpBulat, $hPkp . ' dibulatkan ke bawah (ribuan)', 'rowspan=3');
		$stream .= $thRumus('PPH 21 SETAHUN', $hPphSetahun, 'Tarif Pasal 17 x ' . $hPkpBulat, 'rowspan=3');
		$stream .= $thRumus('TAMBAHAN 20% TANPA NPWP', $hTambah, '20% x ' . $hPphSetahun . ' (bila tanpa NPWP/NIK)', 'rowspan=3');
		$stream .= $thRumus('PPH 21 TERHUTANG', $hTerhutang, $hPphSetahun . ' + ' . $hTambah, 'rowspan=3');
		$stream .= $th('PERBANDINGAN PPH 21', 'colspan=2');
		$stream .= $thRumus('PPH 21 DESEMBER', $hFinal, $hTahunan . ' - ' . $hJanNov, 'rowspan=3');
	} else {
		$stream .= $th('PERHITUNGAN PPH 21 (TER)', 'colspan=3');
		$stream .= $thRumus('PPH 21 BULAN INI', $hFinal, $hBruto . ' x ' . $hTer . ' (+20% bila tanpa NPWP/NIK)', 'rowspan=3');
	}
	$stream .= "</tr>";

	$stream .= "<tr class=rowcontent>";
	foreach ($dtkomplus as $komplus) {
		$stream .= $thKomponen($optNmKomponen[$komplus]);
	}
	foreach ($dtkomin as $kommin) {
		$stream .= $thKomponen($optNmKomponen[$kommin]);
	}
	if ($bulan == '12') {
		$stream .= $thRumus('TERHUTANG SETAHUN', $hTahunan, '= ' . $hTerhutang, 'rowspan=2');
		$stream .= $thRumus('SUDAH DIPOTONG JAN-NOV', $hJanNov, 'PPh 21 Jan-Nov', 'rowspan=2');
	} else {
		$stream .= $thRumus('GAJI BRUTO', $hBruto, $hPend . ' + ' . $hTj, 'rowspan=2');
		$stream .= $thRumus('KATEGORI TER', $hKategori, 'A/B/C sesuai status pajak', 'rowspan=2');
		$stream .= $thRumus('TARIF TER (%)', $hTer, 'Tabel TER PMK 168/2023', 'rowspan=2');
	}
	$stream .= "</tr>";
	$stream .= "<tr class=rowcontent>";
	$stream .= "</tr>";
	$stream .= "</thead>";

	#--- 8. Baris per karyawan
	$no = 0;
	$cacheTarifTer = array();   # tabel TER per status pajak
	$tunjangan_pph21 = array();  # gross-up tunjangan pajak belum diaktifkan (selalu 0)
	$dataPdf = array();         # baris ringkas untuk tarikan PDF

	# total
	$gttdtkompus = 0;
	$gttdtkommin = 0;
	$gtTjpajak = 0;
	$gtbiayajabatan = 0;
	$gtnetto = 0;
	$gtgajiBruto = 0;
	$gtpph21terutanghitung = 0;
	$gtpph21_jan_nov = 0;
	$gtPph21 = 0;

	foreach ($dtkarid as $karid) {
		$no++;
		$stream .= "<tr class=rowcontent id=row" . $no . ">";

		$tunjangan = @$tunjangan_pph21[$karid] ? $tunjangan_pph21[$karid] : 0;

		## Tabel TER kategori karyawan (hanya Jan-Nov)
		$tarifData = array();
		if ($bulan != '12') {
			$status = $statuspajak[$karid];
			if (!isset($cacheTarifTer[$status])) {
				$cacheTarifTer[$status] = pphAmbilSemua("select * from " . $dbname . ".sdm_5tarifpph21 where status = '" . addslashes($status) . "' and kategori = '" . addslashes(@$optKategori[$status]) . "' order by minim");
			}
			$tarifData = $cacheTarifTer[$status];
		}

		if (!$adaExcel) {
			$stream .= "<td hidden id=karyawanid" . $no . ">" . $karid . "</td>";
		}
		$stream .= "<td align=center>" . $no . "</td>";
		$stream .= "<td>" . $nik[$karid] . "</td>";
		$stream .= "<td>" . $nmkar[$karid] . "</td>";
		$stream .= "<td align=center>" . @$nmgolongan[$golongan[$karid]] . "</td>";
		$stream .= "<td align=center>" . $statuspajak[$karid] . "</td>";
		$stream .= "<td align=center>" . $bagian[$karid] . "</td>";
		$stream .= "<td>" . @$nmjabatan[$jabatan[$karid]] . "</td>";

		## Pendapatan (kolom per komponen + total)
		$totalPendapatan = 0;
		foreach ($dtkomplus as $komplus) {
			$nilai = isset($rupiah[$karid][$komplus]) ? $rupiah[$karid][$komplus] : 0;
			$stream .= "<td align=right >" . number_format($nilai, 0) . "</td>";
			$totalPendapatan += $nilai;
			$gttdtkompus += $nilai;
		}
		$stream .= "<td align=right>" . number_format($totalPendapatan) . "</td>";

		## Total pengurang (dipakai kedua mode)
		$totalPengurang = 0;
		foreach ($dtkomin as $kommin) {
			$totalPengurang += isset($rupiah[$karid][$kommin]) ? $rupiah[$karid][$kommin] : 0;
		}

		if ($bulan == '12') {
			#### DESEMBER: perhitungan setahun ####

			$stream .= "<td align=right id='tjpph21x" . $no . "'>" . number_format($tunjangan, 0) . "</td>";
			$gtTjpajak += $tunjangan;

			## Biaya jabatan (persen dari pendapatan setahun, maksimal per bulan x 12)
			$biayaJabatan = ($totalPendapatan + $tunjangan) * $persenjabatan / 100;
			if ($biayaJabatan > ($maxjabatan * 12)) {
				$biayaJabatan = $maxjabatan * 12;
			}
			$stream .= "<td align=right>" . number_format($biayaJabatan) . "</td>";
			$gtbiayajabatan += $biayaJabatan;

			## Penghasilan neto setahun = bruto - biaya jabatan - pengurang
			$gajiNetto = $totalPendapatan + $tunjangan - $biayaJabatan - $totalPengurang;

			if ($jmlKomin > 0) {
				foreach ($dtkomin as $kommin) {
					$nilai = isset($rupiah[$karid][$kommin]) ? $rupiah[$karid][$kommin] : 0;
					$stream .= "<td align=right >" . number_format($nilai, 0) . "</td>";
					$gttdtkommin += $nilai;
				}
				$stream .= "<td align=right >" . number_format($totalPengurang, 0) . "</td>";
				$stream .= "<td align=right >" . number_format($gajiNetto, 0) . " </td>";
				$gtnetto += $gajiNetto;
			}

			$pkpHitung = $gajiNetto - $ptkpnya[$karid];

			$stream .= "<td align=right >" . number_format($ptkpnya[$karid], 0) . "</td>";

			## PKP dan pembulatan ke bawah (kelipatan 1.000)
			$pkpSetahun = 0;
			if ($pkpHitung < 0) {
				$pkpHitung = 0;
			} else {
				$pkpSetahun = $pkpHitung;
				$pkpHitung = intval(($pkpHitung / 1000)) * 1000;
			}
			$stream .= "<td align=right >" . number_format($pkpSetahun, 0) . " </td>";
			$stream .= "<td align=right >" . number_format($pkpHitung, 0) . " </td>";

			## PPh 21 setahun (tarif Pasal 17)
			$pph21Setahun = pphHitungPasal17($pkpHitung, $pphbatas, $pphpenambah, $pphpersen);
			$stream .= "<td align=right >" . number_format($pph21Setahun, 0) . " </td>";

			## Tambahan 20% bagi yang tidak punya NPWP / NIK
			$denda = 0;
			if (!$punyaNpwp[$karid]) {
				$denda = round(($pph21Setahun * 20 / 100));
			}
			$stream .= "<td align=right >" . number_format($denda, 0) . "</td>";

			$pph21Terhutang = $pph21Setahun + $denda;
			$stream .= "<td align=right >" . number_format($pph21Terhutang, 0) . " </td>";
			$gtpph21terutanghitung += $pph21Terhutang;

			$stream .= "<td align=right >" . number_format($pph21Terhutang, 0) . " </td>";
			$janNov = isset($pph21_jan_nov[$karid]) ? $pph21_jan_nov[$karid] : 0;
			$stream .= "<td align=right >" . number_format($janNov, 0) . " </td>";
			$gtpph21_jan_nov += $janNov;

			## PPh 21 Desember = terhutang setahun - yang sudah dipotong Jan-Nov
			$pph21 = $pph21Terhutang - $janNov;
			if ($pph21 < 0) {
				$pph21 = 0;
			}
			$stream .= "<td align=right id='pph21x" . $no . "' style='font-weight:bold;background-color:#FFF2CC'>" . number_format($pph21, 0) . "</td>";
			$gtPph21 += $pph21;
			$dataPdf[] = array($no, $nik[$karid], $nmkar[$karid], $statuspajak[$karid], $totalPendapatan + $tunjangan, $biayaJabatan, $totalPengurang, $gajiNetto, $ptkpnya[$karid], $pkpHitung, $pph21Setahun + $denda, $janNov, $pph21);
		} else {
			#### JAN-NOV: tarif efektif rata-rata (TER) ####

			$gajiBruto = $totalPendapatan + $tunjangan;
			$gtgajiBruto += $gajiBruto;

			## Cari TER sesuai lapisan penghasilan bruto
			$tarifTer = 0;
			foreach ($tarifData as $bar) {
				# batas atas 0 = lapisan paling atas (tanpa batas)
				if ($gajiBruto >= $bar['minim'] && ($bar['maxim'] == 0 || $gajiBruto <= $bar['maxim'])) {
					$tarifTer = $bar['tarif'];
					break;
				}
			}

			$stream .= "<td align=right id='tjpph21x" . $no . "'>" . number_format($tunjangan, 0) . "</td>";
			$gtTjpajak += $tunjangan;

			if ($jmlKomin > 0) {
				foreach ($dtkomin as $kommin) {
					$nilai = isset($rupiah[$karid][$kommin]) ? $rupiah[$karid][$kommin] : 0;
					$stream .= "<td align=right >" . number_format($nilai, 0) . "</td>";
					$gttdtkommin += $nilai;
				}
				$stream .= "<td align=right >" . number_format($totalPengurang, 0) . "</td>";
				$stream .= "<td align=right >" . number_format($gajiBruto - $totalPengurang, 0) . "</td>";
				$gtnetto += $gajiBruto - $totalPengurang;
			}

			$stream .= "<td align=right >" . number_format($gajiBruto, 0) . "</td>";
			$stream .= "<td align=center >" . @$optKategori[$statuspajak[$karid]] . "</td>";
			## Tidak punya NPWP / NIK: PPh 21 lebih tinggi 20%
			$tambahanNpwp = $punyaNpwp[$karid] ? '' : ' (+20%)';
			$stream .= "<td align=center>" . number_format($tarifTer, 2) . $tambahanNpwp . "</td>";

			$pph21 = $gajiBruto * $tarifTer / 100;
			if (!$punyaNpwp[$karid]) {
				$pph21 = $pph21 * 1.2;
			}
			$stream .= "<td align=right id='pph21x" . $no . "' style='font-weight:bold;background-color:#FFF2CC'>" . number_format($pph21, 0) . "</td>";
			$gtPph21 += $pph21;
			$dataPdf[] = array($no, $nik[$karid], $nmkar[$karid], $statuspajak[$karid], @$optKategori[$statuspajak[$karid]], $totalPendapatan, $tunjangan, $gajiBruto, $tarifTer, $pph21);
		}

		$stream .= "</tr>";
	}

	#--- 9. Baris total
	$stream .= "<tr class=rowcontent>";
	$colspanplus = $jmlKomplus + 7;

	$stream .= "<td align=center colspan=" . $colspanplus . "><b>TOTAL</b></td>";
	$selTotal = function ($nilai) {
		return "<td align=center><b>" . $nilai . "</b></td>";
	};

	if ($bulan == '12') {
		$stream .= $selTotal(number_format($gttdtkompus));
		$stream .= $selTotal(number_format($gtTjpajak));
		$stream .= $selTotal(number_format($gtbiayajabatan));
		if ($jmlKomin > 0) {
			$stream .= "<td align=center colspan=" . $jmlKomin . "><b></b></td>";
			$stream .= $selTotal(number_format($gttdtkommin));
			$stream .= $selTotal(number_format($gtnetto));
		}
		# PTKP, PKP, PKP pembulatan, PPh setahun, tambahan 20% (tidak dijumlahkan)
		for ($i = 0; $i < 5; $i++) {
			$stream .= $selTotal('');
		}
		$stream .= $selTotal(number_format($gtpph21terutanghitung));
		$stream .= $selTotal(number_format($gtpph21terutanghitung));
		$stream .= $selTotal(number_format($gtpph21_jan_nov));
		$stream .= $selTotal(number_format($gtPph21));
	} else {
		$stream .= $selTotal(number_format($gttdtkompus));
		$stream .= $selTotal(number_format($gtTjpajak));
		if ($jmlKomin > 0) {
			$stream .= "<td align=center colspan=" . $jmlKomin . "><b></b></td>";
			$stream .= $selTotal(number_format($gttdtkommin));
			$stream .= $selTotal(number_format($gtnetto));
		}
		$stream .= $selTotal(number_format($gtgajiBruto));
		$stream .= $selTotal('');
		$stream .= $selTotal('');
		$stream .= $selTotal(number_format($gtPph21));
	}
	$stream .= "</tr>";
	$stream .= "</table></div>";

	#--- 10. Keluaran
	if ($tipe == 'excel') {
		pphKeluarExcel($stream, $unit, $per, $bulan);
		return;
	}
	if ($tipe == 'pdf') {
		pphKeluarPdf($dataPdf, $unit, $per, $bulan);
		return;
	}

	## Tampilan layar: status tutup buku di atas tabel, tombol Proses hanya jika periode boleh diproses
	$statusPeriode = pphStatusPeriode($per, $unit);
	$stream = pphBannerStatus($statusPeriode, $per, $unit, $bulan) . $stream;
	if ($statusPeriode['boleh']) {
		$stream .= "<button class=mybutton onclick=prosespph(" . $no . ");>" . $_SESSION['lang']['proses'] . "</button>";
	}
	echo $stream . "####" . $no;
}


/** Status tutup buku ringkas (satu baris lencana) di atas hasil perhitungan; tanpa kata Error/Warning/Gagal agar tidak dianggap respons gagal */
function pphBannerStatus($status, $per, $unit, $bulan)
{
	$lencana = function ($teks, $jenis, $judul = '') {
		$gaya = array(
			'ok' => 'background:#E3F4E8;color:#1a7f37;border:1px solid #b7dfc3',
			'salah' => 'background:#FCE8E6;color:#b42318;border:1px solid #f1b8b2',
			'catatan' => 'background:#FFF4D6;color:#8a5a00;border:1px solid #f0d99a',
		);
		return "<span title='" . htmlspecialchars($judul, ENT_QUOTES) . "' style='display:inline-block;padding:2px 9px;margin:0 6px 4px 0;border-radius:10px;font-size:12px;" . $gaya[$jenis] . "'>" . $teks . "</span>";
	};

	$html = "<div style='margin:6px 2px 2px 2px'>";
	$html .= $lencana('Periode gaji: ' . ($status['gajiTutup'] ? 'sudah ditutup' : ($status['gajiAda'] ? 'belum ditutup' : 'belum ada')), $status['gajiTutup'] ? 'ok' : 'salah', $status['pesan'][0][1]);
	$html .= $lencana('Periode akuntansi: ' . ($status['akunTutup'] ? 'sudah ditutup' : 'belum ditutup'), $status['akunTutup'] ? 'salah' : 'ok', $status['pesan'][1][1]);
	$html .= $lencana($status['boleh'] ? 'Bisa diproses' : 'Tidak bisa diproses', $status['boleh'] ? 'ok' : 'salah', $status['boleh'] ? 'Syarat tutup buku terpenuhi' : 'Tombol Proses disembunyikan sampai syarat tutup buku terpenuhi');
	if ($bulan == '12') {
		$kosong = pphBulanBelumDiproses($per, $unit);
		if (count($kosong) > 0) {
			$html .= $lencana('PPh 21 Jan-Nov belum lengkap (' . count($kosong) . ' bulan)', 'catatan', 'PPh 21 belum ada di periode: ' . implode(', ', $kosong) . '. Hitungan Desember memakai PPh Januari-November yang tersedia.');
		}
	}
	return $html . "</div>";
}

#=== Kop laporan (nama PT dari induk unit) untuk Excel dan PDF ===
function pphKop($unit, $per, $bulan)
{
	global $dbname;

	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $unit . "'");
	$pt = isset($induk[$unit]) ? $induk[$unit] : $_SESSION['empl']['kodeorganisasi'];
	$hdpt = setheadreport($unit, $pt);

	return array(
		'nama' => $hdpt['nama'],
		'logo' => isset($hdpt['logo']) ? $hdpt['logo'] : '',
		'judul' => 'PERHITUNGAN PPH PASAL 21 - PERIODE ' . $per,
		'info' => 'Unit: ' . $unit . ' - ' . getNamaOrg($unit) . ' | Periode: ' . $per . ' | Metode: ' . ($bulan == '12' ? 'Perhitungan setahun (Desember)' : 'TER bulanan'),
		'tarik' => 'Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s'),
	);
}


#=== Excel: seluruh kolom perhitungan dengan kop ===
function pphKeluarExcel($stream, $unit, $per, $bulan)
{
	$kop = pphKop($unit, $per, $bulan);
	$kolom = 10;
	$kopHtml = "<table>
		<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($kop['nama']) . "</b></td></tr>
		<tr><td colspan=" . $kolom . "><b>" . htmlspecialchars($kop['judul']) . "</b></td></tr>
		<tr><td colspan=" . $kolom . ">" . htmlspecialchars($kop['info']) . "</td></tr>
		<tr><td colspan=" . $kolom . ">" . htmlspecialchars($kop['tarik']) . "</td></tr>
		<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
		</table>";

	$xls = new HtmlExcel();
	$xls->addSheet("PPh21", $kopHtml . $stream);
	$xls->headers("PPh21_" . $unit . "_" . $per . "_" . date('YmdHis') . ".xls");
	echo $xls->buildFile();
	exit;
}


/** Potong teks agar muat di sel PDF */
function pphFit($pdf, $teks, $lebar, $ukuran = 7)
{
	$teks = utf8_decode($teks);
	$pdf->SetFont('Arial', '', $ukuran);
	if ($pdf->GetStringWidth($teks) <= $lebar - 2) {
		return $teks;
	}
	while (strlen($teks) > 1 && $pdf->GetStringWidth($teks . '...') > $lebar - 2) {
		$teks = substr($teks, 0, -1);
	}
	return $teks . '...';
}


#=== PDF: ringkasan per karyawan (landscape A4) ===
function pphKeluarPdf($rows, $unit, $per, $bulan)
{
	$kop = pphKop($unit, $per, $bulan);

	if (!class_exists('PDFPph21')) {
		class PDFPph21 extends FPDF
		{
			public $kop = array();
			public $judulKolom = array();
			public $lebar = array();

			function Header()
			{
				$width = $this->w - $this->lMargin - $this->rMargin;
				if ($this->kop['logo'] != '' && file_exists($this->kop['logo'])) {
					$this->Image($this->kop['logo'], $this->lMargin, $this->tMargin, 18);
				}
				$this->SetFont('Arial', 'B', 11);
				$this->SetXY($this->lMargin + 22, $this->tMargin + 2);
				$this->Cell(160, 6, utf8_decode($this->kop['nama']), 0, 1, 'L');
				$this->SetY($this->tMargin + 15);
				$this->SetFont('Arial', 'B', 13);
				$this->Cell($width, 7, utf8_decode($this->kop['judul']), 0, 1, 'C');
				$this->SetFont('Arial', '', 8);
				$this->Cell($width, 5, utf8_decode($this->kop['info']), 0, 1, 'C');
				$this->Cell($width, 5, utf8_decode($this->kop['tarik']), 0, 1, 'C');
				$this->Ln(1);
				$this->SetFillColor(220, 220, 220);
				$this->SetFont('Arial', 'B', 7);
				foreach ($this->judulKolom as $i => $judul) {
					$this->Cell($this->lebar[$i], 8, $judul, 1, 0, 'C', true);
				}
				$this->Ln();
			}

			function Footer()
			{
				$this->SetY(-12);
				$this->SetFont('Arial', 'I', 7);
				$this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
			}
		}
	}

	if ($bulan == '12') {
		$judulKolom = array('No', 'NIK', 'Nama', 'Status', 'Bruto Setahun', 'Biaya Jabatan', 'Pengurang', 'Neto', 'PTKP', 'PKP', 'PPh Terhutang', 'Dipotong Jan-Nov', 'PPh Desember');
		$lebar = array(8, 20, 42, 11, 23, 19, 19, 23, 19, 22, 20, 20, 21);
		$catatan = 'Neto = Bruto - Biaya Jabatan - Pengurang | PKP = Neto - PTKP (dibulatkan ke bawah ribuan) | PPh Terhutang = tarif Pasal 17 x PKP (+20% bila tanpa NPWP/NIK) | PPh Desember = PPh Terhutang - Dipotong Jan-Nov';
		$kolomAngka = array(4, 5, 6, 7, 8, 9, 10, 11, 12);
		$kolomJumlah = array(4, 5, 6, 7, 10, 11, 12);
	} else {
		$judulKolom = array('No', 'NIK', 'Nama', 'Status', 'Kategori TER', 'Pendapatan', 'TJ Pajak', 'Gaji Bruto', 'TER (%)', 'PPh 21');
		$lebar = array(10, 26, 70, 18, 22, 30, 26, 30, 18, 27);
		$catatan = 'Gaji Bruto = Pendapatan + TJ Pajak | PPh 21 = Gaji Bruto x TER (+20% bila tanpa NPWP/NIK)';
		$kolomAngka = array(5, 6, 7, 8, 9);
		$kolomJumlah = array(5, 6, 7, 9);
	}

	$pdf = new PDFPph21('L', 'mm', 'A4');
	$pdf->kop = $kop;
	$pdf->judulKolom = $judulKolom;
	$pdf->lebar = $lebar;
	$pdf->AliasNbPages();
	$pdf->SetAutoPageBreak(false, 16);
	$pdf->AddPage();

	$tinggi = 6;
	$jumlah = array();
	foreach ($rows as $baris) {
		if ($pdf->GetY() + $tinggi > $pdf->h - $pdf->bMargin) {
			$pdf->AddPage();
		}
		foreach ($baris as $i => $nilai) {
			if (in_array($i, $kolomAngka)) {
				$tampil = number_format($nilai, ($bulan != '12' && $i == 8) ? 2 : 0);
				$rata = 'R';
			} else {
				$tampil = pphFit($pdf, (string)$nilai, $lebar[$i]);
				$rata = ($i == 2) ? 'L' : 'C';
			}
			$pdf->SetFont('Arial', '', 7);
			$pdf->Cell($lebar[$i], $tinggi, $tampil, 1, 0, $rata);
			if (in_array($i, $kolomJumlah)) {
				$jumlah[$i] = (isset($jumlah[$i]) ? $jumlah[$i] : 0) + $nilai;
			}
		}
		$pdf->Ln();
	}

	if (count($rows) == 0) {
		$pdf->Cell(array_sum($lebar), $tinggi, 'Data tidak ditemukan', 1, 1, 'C');
	} else {
		if ($pdf->GetY() + $tinggi > $pdf->h - $pdf->bMargin) {
			$pdf->AddPage();
		}
		$pdf->SetFont('Arial', 'B', 7);
		$lebarLabel = 0;
		$kolomPertamaJumlah = min($kolomJumlah);
		for ($i = 0; $i < $kolomPertamaJumlah; $i++) {
			$lebarLabel += $lebar[$i];
		}
		$pdf->Cell($lebarLabel, $tinggi, 'TOTAL', 1, 0, 'C');
		for ($i = $kolomPertamaJumlah; $i < count($lebar); $i++) {
			$pdf->Cell($lebar[$i], $tinggi, isset($jumlah[$i]) ? number_format($jumlah[$i], 0) : '', 1, 0, 'R');
		}
		$pdf->Ln();
	}

	$pdf->Ln(2);
	$pdf->SetFont('Arial', 'I', 7);
	$pdf->MultiCell(0, 4, utf8_decode($catatan));
	$pdf->Output();
	exit;
}