<?php
/**
 * Slave PPh Pasal 21 v3  (SDM > Proses > PPh Pasal 21)
 *
 * proses=preview    : tampilkan perhitungan (tipe=excel -> unduh Excel)
 *                     - Jan-Nov  : PPh 21 = penghasilan bruto bulan berjalan x TER (kategori A/B/C)
 *                     - Desember : PPh 21 setahun (tarif Pasal 17) dikurangi PPh 21 Jan-Nov
 * sistem=Bulanan|Harian : wajib; karyawan dipilah menurut sistem gaji di data karyawan, berlaku untuk preview, Excel, PDF, dan proses
 * proses=panellain / templatelain / uploadlain / hapuslain : penghasilan lain (jasa produksi, dana motivasi, dll) lewat upload Excel, khusus PT PPP
 * proses=prosessemua : hitung ulang di server lalu simpan seluruh karyawan sekaligus (satu transaksi) ke sdm_gaji (komponen 42 / 153) dan sdm_pph21
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
$sistem       = checkPostGet('sistem', '');   # sistem gaji karyawan yang dihitung / diproses: Bulanan atau Harian (wajib)
# kode khusus di kolom kodejenis sdm_pph21_penghasilanlain untuk PPh 21 manual (bukan penghasilan; dikurangkan dari PPh 21 sistem)
define('PPH_KODE_MANUAL', 'PPH21M');
define('PPH_KODE_TUNJPAJAK', 'TP');   # jenis penghasilan lain 'Tunjangan Pajak': tidak jadi kolom lain, tapi masuk kolom TJ PAJAK dan disimpan sebagai komponen 153
# karyawan Harian / tidak tetap (KHL): penghasilan bulanan sampai batas ini tidak kena PPh 21; di atas batas dikenai tarif ini atas selisihnya
define('PPH_KHL_BATAS', 4500000);
define('PPH_KHL_TARIF', 5);
$baris        = checkPostGet('baris', '');
$pph21x       = pphAngka(checkPostGet('pph21x', ''));
$tjpph21x     = pphAngka(checkPostGet('tjpph21x', ''));

switch ($proses) {
	case 'prosessemua':
		pphCekInput($per, $unit);
		pphCekSistem($sistem);
		pphProsesSemua($per, $unit);
		break;

	case 'preview':
		pphCekInput($per, $unit);
		pphCekSistem($sistem);
		pphPreview($per, $unit, $tipe);
		break;

	case 'panelpjd':
		pphCekInput($per, $unit);
		if (getindukPT($unit) != 'PPP') {
			exit('Error, Panel ini khusus unit PT PPP.');
		}
		pphPanelPjd($unit, $per);
		break;

	case 'panellain':
		pphCekInput($per, $unit);
		pphCekSistem($sistem);
		pphCekPpp($unit);
		pphPanelLain($unit, $per, $sistem);
		break;

	case 'templatelain':
		pphCekInput($per, $unit);
		pphCekSistem($sistem);
		pphTemplateLain($unit, $per, $sistem);
		break;

	case 'uploadlain':
		pphCekInput($per, $unit);
		pphCekSistem($sistem);
		pphUploadLain($per, $unit, $sistem);
		break;

	case 'hapuslain':
		pphCekInput($per, $unit);
		pphCekSistem($sistem);
		pphHapusLain($per, $unit, $sistem);
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

/** Sistem gaji wajib dipilih: Bulanan atau Harian */
function pphCekSistem($sistem)
{
	if (!in_array($sistem, array('Bulanan', 'Harian'), true)) {
		exit('Error, Sistem gaji belum dipilih (Bulanan atau Harian).');
	}
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


/**
 * PJD (khusus PT PPP) yang masuk perhitungan PPh 21 periode ini: realisasi selesai, biaya yang ditanggung karyawan
 * sudah diverifikasi HRD (nilai = jumlahhrd), dan klaim (penyelesaian) sudah dibayar di kas bank dan diposting.
 * Bulan klaim dibayar menentukan periode: Jan-Nov bulan periode itu, Desember seluruh tahun.
 * Satu baris = satu PJD per jenis biaya.
 */
function pphPjdPeriode($unit, $per)
{
	global $dbname;

	$tahun = substr($per, 0, 4);
	if (substr($per, 5, 2) == '12') {
		$awal = $tahun . '-01-01';
		$akhir = $tahun . '-12-31';
	} else {
		$awal = $per . '-01';
		$akhir = date('Y-m-t', strtotime($per . '-01'));
	}
	$sql = "select h.notransaksi, h.karyawanid, h.tgldinasdarireal, h.tgldinassampaireal, k.nik, k.namakaryawan, k.tipekaryawan, k.statuspajak,
			d.jenisbiaya, sum(d.jumlahhrd) as jumlah,
			(select max(kd.tanggal) from " . $dbname . ".keu_kasbankdt kd
				where kd.nodok = h.notransaksi and kd.lainnya = concat('claimpjd#', h.notransaksi)
				and exists (select 1 from " . $dbname . ".keu_kasbankht kh where kh.notransaksi=kd.notransaksi and kh.kodeorg=kd.kodeorg and kh.posting=1)
			) as tglbayar
		from " . $dbname . ".sdm_pjdinasht h
		join " . $dbname . ".sdm_pjdinasdt d on d.notransaksi=h.notransaksi
		left join " . $dbname . ".datakaryawan k on k.karyawanid=h.karyawanid
		where h.kodeorg='" . $unit . "' and h.statuspengajuan='1' and h.statusrealisasi='1'
			and d.sumber='1' and d.tanggungan='1' and d.statusverifikasihrd='1' and d.jumlahhrd>0
		group by h.notransaksi, d.jenisbiaya
		having tglbayar is not null and tglbayar >= '" . $awal . "' and tglbayar <= '" . $akhir . "'
		order by tglbayar, h.notransaksi, d.jenisbiaya";
	return pphAmbilSemua($sql);
}

/** Panel di atas tabel PPh 21 (hanya baca): PJD yang ikut dihitung sebagai penghasilan periode ini */
function pphPanelPjd($unit, $per)
{
	global $dbname;

	$nmJenis = makeOption($dbname, 'sdm_5jenisbiayapjdinas', 'id,keterangan');
	$perPjd = array();
	foreach (pphPjdPeriode($unit, $per) as $b) {
		## karyawan staff (tipe 0) atau tanpa status pajak tidak ada di perhitungan PPh 21 v3: PJD-nya tidak ditampilkan
		if ($b['tipekaryawan'] == '0' || $b['statuspajak'] == '') {
			continue;
		}
		$no = $b['notransaksi'];
		if (!isset($perPjd[$no])) {
			$perPjd[$no] = array('nik' =>$b['nik'], 'nama' => $b['namakaryawan'], 'dari' => $b['tgldinasdarireal'], 'sampai' => $b['tgldinassampaireal'], 'tglbayar' => $b['tglbayar'], 'rincian' => array(), 'total' => 0);
		}
		$namaJenis = isset($nmJenis[$b['jenisbiaya']]) ? $nmJenis[$b['jenisbiaya']] : 'Jenis ' . $b['jenisbiaya'];
		$perPjd[$no]['rincian'][] = $namaJenis . ': ' . number_format($b['jumlah'], 0);
		$perPjd[$no]['total'] += $b['jumlah'];
	}

	$keterangan = (substr($per, 5, 2) == '12')
		? 'Desember: seluruh PJD yang klaimnya dibayar di kas bank Januari-Desember ' . substr($per, 0, 4) . '.'
		: 'PJD yang klaimnya dibayar di kas bank pada bulan ' . $per . '.';

	$html = "<fieldset style='margin:6px 0'><legend>Penghasilan Perjalanan Dinas masuk perhitungan (khusus PT PPP)</legend>";
	$html .= "<details><summary style='cursor:pointer;font-weight:bold'>" . count($perPjd) . " PJD (klik untuk melihat rincian)</summary>";
	$html .= "<div style='margin:0 2px 6px 2px;font-size:11px;color:#555'>" . htmlspecialchars($keterangan) . " Sudah realisasi dan diverifikasi HRD (nilai = jumlah HRD). Otomatis masuk kolom Perjalanan Dinas di tabel di bawah; tidak masuk slip gaji dan aktual rekap gaji.</div>";
	$html .= "<table class=sortable cellspacing=1 cellpadding=4 border=0 style='width:100%'><thead><tr class=rowheader>
		<th align=center>No</th><th align=center>NIK</th><th align=center>Nama</th><th align=center>No. PJD</th>
		<th align=center>Tgl Dinas</th><th align=center>Tgl Bayar Kas Bank</th><th align=center>Rincian Biaya</th><th align=center>Jumlah</th></tr></thead><tbody>";
	$no = 0;
	$total = 0;
	foreach ($perPjd as $nomor => $p) {
		$no++;
		$total += $p['total'];
		$tglDinas = ($p['dari'] != '' && $p['dari'] != '0000-00-00') ? tanggalnormal($p['dari']) : '';
		if ($p['sampai'] != '' && $p['sampai'] != '0000-00-00' && $p['sampai'] != $p['dari']) {
			$tglDinas .= ' s/d ' . tanggalnormal($p['sampai']);
		}
		$html .= "<tr class=rowcontent>
			<td align=center>" . $no . "</td>
			<td>" . htmlspecialchars($p['nik']) . "</td>
			<td>" . htmlspecialchars($p['nama']) . "</td>
			<td>" . htmlspecialchars($nomor) . "</td>
			<td align=center>" . $tglDinas . "</td>
			<td align=center>" . tanggalnormal($p['tglbayar']) . "</td>
			<td>" . htmlspecialchars(implode('; ', $p['rincian'])) . "</td>
			<td align=right>" . number_format($p['total'], 0) . "</td></tr>";
	}
	if ($no == 0) {
		$html .= "<tr class=rowcontent><td colspan=8 align=center>Tidak ada PJD yang klaimnya dibayar pada periode ini</td></tr>";
	} else {
		$html .= "<tr class=rowcontent><td colspan=7 align=center><b>TOTAL</b></td><td align=right><b>" . number_format($total, 0) . "</b></td></tr>";
	}
	$html .= "</tbody></table></details></fieldset>";
	echo $html;
}

#=== Proses PPh 21 seluruh karyawan sekaligus ===
/**
 * Hitung ulang di server (nilai dari browser tidak dipakai), lalu simpan semua karyawan dalam satu transaksi:
 * hasil proses sebelumnya untuk unit dan periode ini diganti. Gagal di tengah = tidak ada yang berubah.
 */
function pphProsesSemua($per, $unit)
{
	global $owlPDO, $dbname, $pphHasil, $sistem, $pphRincian;

	## Validasi tutup buku: gaji sudah ditutup, periode akuntansi belum ditutup
	$statusPeriode = pphStatusPeriode($per, $unit);
	foreach ($statusPeriode['pesan'] as $pesan) {
		if ($pesan[0] == 'salah') {
			exit('Error, ' . $pesan[1] . '.');
		}
	}
	$adaRincian = pphTabelRincianAda();   # snapshot rincian pendapatan (sql_pph21_v3.sql)

	## Hitung dengan rumus yang sama dengan tampilan (keluaran tampilan dibuang)
	ob_start();
	pphPreview($per, $unit, 'proses');
	ob_end_clean();
	if (count($pphHasil) == 0) {
		exit('Error, Tidak ada karyawan yang dihitung untuk unit dan periode ini.');
	}

	$jumlahKaryawan = 0;
	$totalPph = 0;
	$jumlahPph = 0;
	$totalTj = 0;
	try {
		$owlPDO->beginTransaction();
		## hanya hasil karyawan yang dihitung ulang (sistem gaji terpilih) yang diganti, sistem gaji lain tidak disentuh
		$idHitung = array();
		## seluruh karyawan sistem gaji ini (termasuk yang kini tanpa pendapatan), agar hasil lama yang sudah tidak berlaku ikut terhapus
		foreach (array_keys(pphKaryawanScope($per, $unit, $sistem)) as $idKar) {
			$idHitung[] = $owlPDO->quote((string) $idKar);
		}
		foreach (array_chunk($idHitung, 200) as $potong) {
			$in = implode(',', $potong);
			$owlPDO->exec("delete from " . $dbname . ".sdm_gaji where kodeorg='" . $unit . "' and idkomponen in ('42','153') and periodegaji='" . $per . "' and karyawanid in (" . $in . ")");
			$owlPDO->exec("delete from " . $dbname . ".sdm_pph21 where kodeorg='" . $unit . "' and periode='" . $per . "' and karyawanid in (" . $in . ")");
			if ($adaRincian) {
				$owlPDO->exec("delete from " . $dbname . ".sdm_pph21_rincian where kodeorg='" . $unit . "' and periode='" . $per . "' and karyawanid in (" . $in . ")");
			}
		}

		$stmtGaji =$owlPDO->prepare("insert into " . $dbname . ".sdm_gaji (`karyawanid`,`kodeorg`,`idkomponen`,`jumlah`,`pengali`,`hk`,`periodegaji`) values (?, ?, ?, ?, '1', '0', ?)");
		$stmtPph = $owlPDO->prepare("insert into " . $dbname . ".sdm_pph21 (`karyawanid`,`kodeorg`,`tipepph`,`nilai`,`periode`) values (?, ?, 'regular', ?, ?)");
		foreach ($pphHasil as $karyawanid => $hasil) {
			$pph21 = $hasil[0];
			$tj = $hasil[1];
			if ($pph21 != 0) {
				$stmtGaji->execute(array($karyawanid, $unit, '42', $pph21, $per));
				$stmtPph->execute(array($karyawanid, $unit, $pph21, $per));
				$totalPph += $pph21;
				$jumlahPph++;
			}
			if ($tj != 0) {
				$stmtGaji->execute(array($karyawanid, $unit, '153', $tj, $per));
				$totalTj += $tj;
			}
			$jumlahKaryawan++;
		}
		$totalLebih = 0;
		foreach ($pphRincian as $rLebih) {
			$totalLebih += $rLebih['lebih'];
		}

		## Snapshot rincian pendapatan yang dipakai pada hitungan ini (satu baris per karyawan yang dihitung)
		if ($adaRincian) {
			$stmtRincian = $owlPDO->prepare("insert into " . $dbname . ".sdm_pph21_rincian (`kodeorg`,`periode`,`karyawanid`,`sistemgaji`,`metode`,`pendapatangaji`,`pjd`,`penghasilanlain`,`rincianlain`,`totalpendapatan`,`tunjanganpajak`,`pengurang`,`tarifter`,`punyanpwp`,`pph21terhutang`,`pph21jannov`,`pph21sistem`,`pph21manual`,`pph21lebih`,`pph21`,`diprosesoleh`,`diprosespada`) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, now())");
			foreach ($pphRincian as $karyawanid => $r) {
				$lainTeks = array();   # rincian per jenis, format KODE=jumlah dipisah titik koma (mis. JP=3000000;DM=250000)
				foreach ($r['lain'] as $kodeLain => $jmlLain) {
					$lainTeks[] = $kodeLain . '=' . $jmlLain;
				}
				$stmtRincian->execute(array($unit, $per, (string) $karyawanid, $r['sistem'], $r['metode'], $r['gaji'], $r['pjd'], array_sum($r['lain']), implode(';', $lainTeks), $r['total'], $r['tunjangan'], $r['pengurang'], $r['tarif'], $r['npwp'], $r['terhutang'], $r['jannov'], $r['sistem21'], $r['manual'], $r['lebih'], $r['pph21'], $_SESSION['standard']['userid']));
			}
		}
		$owlPDO->commit();
	} catch (PDOException $e) {
		if ($owlPDO->inTransaction()) {
			$owlPDO->rollBack();
		}
		error_log('sdm_slave_3pph21_v3 proses: ' . $e->getMessage());
		exit('Error, PPh 21 tidak dapat disimpan; tidak ada data yang berubah.');
	}
	echo 'Berhasil, ' . $jumlahKaryawan . ' karyawan ' . $sistem . ' diproses, ' . $jumlahPph . ' karyawan terkena PPh 21. Total PPh 21: Rp ' . number_format($totalPph, 0) . ($totalTj != 0 ? ' | Total tunjangan PPh 21: Rp ' . number_format($totalTj, 0) : '') . '.'
		. ($totalLebih > 0 ? ' Lebih bayar (tidak dipotong): Rp ' . number_format($totalLebih, 0) . '.' : '')
		. ($adaRincian ? ' Rincian pendapatan tersimpan.' : ' Catatan: rincian pendapatan belum tersimpan karena tabel snapshot belum dibuat (jalankan sql_pph21_v3.sql).')
		## ringkasan terstruktur untuk notifikasi di layar (teks di atas tetap sebagai cadangan)
		. '@@RINGKAS@@' . json_encode(array(
			'unit' => $unit,
			'periode' => $per,
			'sistem' => $sistem,
			'diproses' => $jumlahKaryawan,
			'terkena' => $jumlahPph,
			'total' => $totalPph,
			'tunjangan' => $totalTj,
			'rincian' => $adaRincian,
			'lebih' => $totalLebih,
		));
}

/** Tabel snapshot rincian pendapatan sudah dibuat */
function pphTabelRincianAda()
{
	global $dbname;
	$a = pphAmbilSemua("show tables from `" . $dbname . "` like 'sdm_pph21_rincian'");
	return count($a) > 0;
}

#=== Penghasilan lain lewat upload Excel (khusus PT PPP): jasa produksi, dana motivasi, dll ===

/** Fitur ini khusus unit PT PPP */
function pphCekPpp($unit)
{
	if (getindukPT($unit) != 'PPP') {
		exit('Error, Fitur ini khusus unit PT PPP.');
	}
}

/** Tabel penghasilan lain sudah dibuat (jalankan sql_pph21_v3.sql bila belum) */
function pphTabelLainAda()
{
	global $dbname;
	$a = pphAmbilSemua("show tables from `" . $dbname . "` like 'sdm_pph21_penghasilanlain'");
	return count($a) > 0;
}

/** Kolom template / upload: jenis penghasilan lain + kolom PPh 21 Manual (disimpan di tabel yang sama dengan kode khusus) */
function pphJenisTemplate()
{
	$hasil = pphJenisLain();
	$hasil[PPH_KODE_MANUAL] = 'PPh 21 Manual';
	return $hasil;
}

/**
 * Jenis penghasilan lain yang bisa diunggah: kode => nama (urut = urutan kolom template Excel).
 * Jenis baru cukup ditambah satu baris di sini; kode maksimal 10 karakter (huruf/angka), jangan diganti bila sudah ada data.
 */
function pphJenisLain($semua = false)
{
	return array(
		'JP' => 'Jasa Produksi',
		'DM' => 'Dana Motivasi',
		PPH_KODE_TUNJPAJAK => 'Tunjangan Pajak',
		);
}

/**
 * Penghasilan lain yang masuk perhitungan periode ini: [karyawanid][kodejenis] => jumlah.
 * Januari-November = upload periode itu, Desember = seluruh upload Januari-Desember (sama seperti PJD).
 */
function pphLainPeriode($unit, $per)
{
	global $dbname;

	$tahun = substr($per, 0, 4);
	if (substr($per, 5, 2) == '12') {
		$awal = $tahun . '-01';
		$akhir = $tahun . '-12';
	} else {
		$awal = $per;
		$akhir = $per;
	}
	$hasil = array();
	foreach (pphAmbilSemua("select karyawanid, kodejenis, sum(jumlah) as jumlah from " . $dbname . ".sdm_pph21_penghasilanlain
		where kodeorg='" . $unit . "' and periode >= '" . $awal . "' and periode <= '" . $akhir . "' and kodejenis <> '" . PPH_KODE_MANUAL . "'
		group by karyawanid, kodejenis having sum(jumlah) > 0") as $b) {
		$hasil[$b['karyawanid']][$b['kodejenis']] = $b['jumlah'];
	}
	return $hasil;
}

/** Tunjangan Pajak (upload) pada periode ini saja (bukan Januari-Desember): [karyawanid] => jumlah. Inilah yang disimpan sebagai komponen 153. */
function pphTunjanganPajakBulan($unit, $per)
{
	global $dbname;

	$hasil = array();
	foreach (pphAmbilSemua("select karyawanid, sum(jumlah) as jumlah from " . $dbname . ".sdm_pph21_penghasilanlain where kodeorg='" . $unit . "' and periode='" . $per . "' and kodejenis='" . PPH_KODE_TUNJPAJAK . "' group by karyawanid having sum(jumlah) > 0") as $b) {
		$hasil[$b['karyawanid']] = $b['jumlah'];
	}
	return $hasil;
}

/**
 * PPh 21 manual (sudah dilaporkan secara manual, khusus PT PPP): 'ini' = periode yang dihitung,
 * 'jannov' = jumlah Januari-November (hanya diisi untuk periode Desember; dihitung sebagai PPh 21 yang sudah dibayar).
 * Bentuk: [karyawanid] => jumlah.
 */
function pphManualPeriode($unit, $per)
{
	global $dbname;

	$hasil = array('ini' => array(), 'jannov' => array());
	$tahun = substr($per, 0, 4);
	foreach (pphAmbilSemua("select karyawanid, sum(jumlah) as jumlah from " . $dbname . ".sdm_pph21_penghasilanlain where kodeorg='" . $unit . "' and periode='" . $per . "' and kodejenis='" . PPH_KODE_MANUAL . "' group by karyawanid") as $b) {
		$hasil['ini'][$b['karyawanid']] = $b['jumlah'];
	}
	if (substr($per, 5, 2) == '12') {
		foreach (pphAmbilSemua("select karyawanid, sum(jumlah) as jumlah from " . $dbname . ".sdm_pph21_penghasilanlain where kodeorg='" . $unit . "' and periode >= '" . $tahun . "-01' and periode <= '" . $tahun . "-11' and kodejenis='" . PPH_KODE_MANUAL . "' group by karyawanid") as $b) {
			$hasil['jannov'][$b['karyawanid']] = $b['jumlah'];
		}
	}
	return $hasil;
}

/** Karyawan yang ikut perhitungan unit & periode ini pada sistem gaji terpilih: [karyawanid] => baris (nik, nama, statuspajak) */
function pphKaryawanScope($per, $unit, $sistem)
{
	$sqlDasar = pphSqlKaryawan($per, $unit);
	if ($sistem == 'Harian' && getindukPT($unit) == 'PPP') {
		$sqlDasar = preg_replace("/\s+and statuspajak <> ''/", '', $sqlDasar);   # KHL tidak memakai status pajak
	}
	$sql = preg_replace('/^select \*/', 'select karyawanid, nik, namakaryawan, statuspajak', $sqlDasar) . " and sistemgaji='" . $sistem . "' order by namakaryawan";
	$hasil = array();
	foreach (pphAmbilSemua($sql) as $b) {
		$hasil[$b['karyawanid']] = $b;
	}
	return $hasil;
}

/** Nilai upload periode ini (periode yang dipilih saja) untuk karyawan tertentu: [karyawanid][kodejenis] => jumlah */
function pphLainTersimpan($unit, $per, $idKaryawan)
{
	global $dbname, $owlPDO;

	$hasil = array();
	if (count($idKaryawan) == 0) {
		return $hasil;
	}
	foreach (array_chunk($idKaryawan, 300) as $potong) {
		$in = array();
		foreach ($potong as $id) {
			$in[] = $owlPDO->quote((string) $id);
		}
		foreach (pphAmbilSemua("select karyawanid, kodejenis, jumlah from " . $dbname . ".sdm_pph21_penghasilanlain
			where kodeorg='" . $unit . "' and periode='" . $per . "' and karyawanid in (" . implode(',', $in) . ")") as $b) {
			$hasil[$b['karyawanid']][$b['kodejenis']] = $b['jumlah'];
		}
	}
	return $hasil;
}

/** Panel upload: unduh template, upload Excel, hapus data, dan ringkasan nilai yang sudah diunggah */
function pphPanelLain($unit, $per, $sistem)
{
	$h = function ($teks) {
		return htmlspecialchars($teks, ENT_QUOTES);
	};
	$gaya = "margin:8px 2px 10px 2px;padding:10px 16px;background:#fff;border:1px solid #c9d6e2;border-left:5px solid #2f6fb0;border-radius:6px;font-size:13px";

	if (!pphTabelLainAda()) {
		echo "<div style='" . $gaya . ";border-left-color:#b42318'><b>Penghasilan Lain (Upload Excel)</b><div style='color:#b42318;margin-top:4px'>Tabel belum dibuat di database. Jalankan sql_pph21_v3.sql terlebih dahulu.</div></div>";
		return;
	}

	$status = pphStatusPeriode($per, $unit);
	$jenis = pphJenisTemplate();
	$karyawan = pphKaryawanScope($per, $unit, $sistem);
	$tersimpan = pphLainTersimpan($unit, $per, array_keys($karyawan));

	$total = array();
	$jumlahOrang = array();
	foreach ($tersimpan as $kid => $perJenis) {
		foreach ($perJenis as $kode => $jml) {
			if ($jml > 0) {
				$total[$kode] = (isset($total[$kode]) ? $total[$kode] : 0) + $jml;
				$jumlahOrang[$kode] = (isset($jumlahOrang[$kode]) ? $jumlahOrang[$kode] : 0) + 1;
			}
		}
	}
	$semuaJenis = pphJenisTemplate();

	$html = "<details style='" . $gaya . "'>";
	$html .= "<summary style='cursor:pointer'><b style='font-size:14px'>Penghasilan Lain &amp; PPh 21 Manual (Upload Excel)</b> <span style='font-size:12px;color:#555'>" . $h(implode(', ', $jenis)) . " &middot; " . $h($unit) . " &middot; " . $h($per) . " &middot; sistem gaji " . $h($sistem) . " &middot; " . (count($total) > 0 ? number_format(count($total)) . " jenis terisi" : "belum ada angka") . " (klik untuk membuka)</span></summary>";
	$html .= "<div style='display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-top:8px'>";
	$html .= "<button class=mybutton type=button onclick='unduhTemplateLain()' title='Template berisi daftar karyawan, tinggal isi angka'>Unduh Template</button>";
	if (!$status['akunTutup']) {
		$html .= "<input type=file id=fileLain accept='.xlsx' style='max-width:230px;font-size:12px'>";
		$html .= "<button class=mybutton type=button id=btnUploadLain onclick='uploadLain()'>Upload</button>";
		if (count($total) > 0) {
			$html .= "<button class=mybutton type=button onclick='hapusLain()' title='Hapus seluruh angka yang diunggah untuk periode dan sistem gaji ini'>Hapus Data Upload</button>";
		}
	}
	$html .= "</div>";

	if ($status['akunTutup']) {
		$html .= "<div style='margin-top:6px;font-size:12px;color:#b42318'>Periode akuntansi sudah ditutup: upload dan hapus data dinonaktifkan.</div>";
	} else {
		$html .= "<div style='margin-top:6px;font-size:12px;color:#555'>Cara pakai: Unduh Template, isi angka per karyawan pada kolom jenis (kosongkan bila tidak ada), simpan sebagai .xlsx, lalu Upload. Kolom ID Karyawan, NIK, dan Nama jangan diubah. Upload ulang akan mengganti angka karyawan yang ada di file. Kolom PPh 21 Manual diisi bila PPh 21 sudah dilaporkan secara manual: saat proses, PPh 21 sistem dikurangi angka ini (potongan tidak pernah minus; bila manual melebihi hitungan sistem, kelebihannya dicatat sebagai lebih bayar).</div>";
	}

	if (count($total) > 0) {
		$html .= "<table class=sortable cellspacing=1 cellpadding=4 style='margin-top:8px'><thead><tr class=rowheader><th>Jenis</th><th>Karyawan</th><th>Total</th></tr></thead><tbody>";
		foreach ($total as $kode => $nilai) {
			$html .= "<tr class=rowcontent><td>" . $h(isset($semuaJenis[$kode]) ? $semuaJenis[$kode] : $kode) . "</td><td align=right>" . number_format($jumlahOrang[$kode]) . "</td><td align=right>" . number_format($nilai) . "</td></tr>";
		}
		$html .= "</tbody></table>";

		$html .= "<details style='margin-top:6px'><summary style='cursor:pointer'>Rincian per karyawan (" . count($tersimpan) . ")</summary><table class=sortable cellspacing=1 cellpadding=4 style='margin-top:4px'><thead><tr class=rowheader><th>NIK</th><th>Nama</th>";
		foreach ($jenis as $kode => $nama) {
			$html .= "<th>" . $h($nama) . "</th>";
		}
		$html .= "</tr></thead><tbody>";
		foreach ($karyawan as $kid => $k) {
			if (!isset($tersimpan[$kid])) {
				continue;
			}
			$html .= "<tr class=rowcontent><td>" . $h($k['nik']) . "</td><td>" . $h($k['namakaryawan']) . "</td>";
			foreach ($jenis as $kode => $nama) {
				$html .= "<td align=right>" . (isset($tersimpan[$kid][$kode]) ? number_format($tersimpan[$kid][$kode]) : '') . "</td>";
			}
			$html .= "</tr>";
		}
		$html .= "</tbody></table></details>";
	} else {
		$html .= "<div style='margin-top:8px;font-size:12px;color:#8a5a00'>Belum ada angka yang diunggah untuk periode dan sistem gaji ini.</div>";
	}
	if (substr($per, 5, 2) == '12') {
		$html .= "<div style='margin-top:6px;font-size:12px;color:#555'>Desember: yang dihitung adalah total upload Januari-Desember " . $h(substr($per, 0, 4)) . " (pajak Januari-November yang sudah dipotong dikurangkan pada hitungan setahun).</div>";
	}
	echo $html . "</details>";
}

/** Unduh template Excel: daftar karyawan unit/periode/sistem gaji + satu kolom per jenis penghasilan, tinggal isi angka */
function pphTemplateLain($unit, $per, $sistem)
{
	pphCekPpp($unit);
	if (!pphTabelLainAda()) {
		exit('Error, Tabel penghasilan lain belum dibuat di database.');
	}
	$jenis = pphJenisTemplate();
	if (count($jenis) == 0) {
		exit('Error, Belum ada jenis penghasilan lain yang aktif.');
	}
	$karyawan = pphKaryawanScope($per, $unit, $sistem);
	if (count($karyawan) == 0) {
		exit('Error, Tidak ada karyawan sistem gaji ' . $sistem . ' pada unit dan periode ini.');
	}
	$tersimpan = pphLainTersimpan($unit, $per, array_keys($karyawan));

	require_once('lib/xlsxwriter.class.php');
	$tipe = array('ID Karyawan' => 'string', 'NIK' => 'string', 'Nama Karyawan' => 'string');
	$lebar = array(14, 22, 38);
	foreach ($jenis as $kode => $nama) {
		$tipe[$nama . ' [' . $kode . ']'] = '#,##0';
		$lebar[] = 22;
	}
	$w = new XLSXWriter();
	$w->setTitle('Template Penghasilan Lain PPh 21');
	$w->writeSheetHeader('Template', $tipe, array('widths' => $lebar, 'freeze_rows' => 1, 'font-style' => 'bold', 'fill' => '#DDEBF7'));
	foreach ($karyawan as $kid => $k) {
		$baris = array((string) $kid, (string) $k['nik'], (string) $k['namakaryawan']);
		foreach ($jenis as $kode => $nama) {
			$baris[] = isset($tersimpan[$kid][$kode]) ? (float) $tersimpan[$kid][$kode] : '';
		}
		$w->writeSheetRow('Template', $baris);
	}
	$w->writeSheetHeader('Petunjuk', array('Petunjuk pengisian' => 'string'), array('widths' => array(110), 'font-style' => 'bold'));
	$petunjuk = array(
		'Template ini untuk unit ' . $unit . ', periode ' . $per . ', sistem gaji ' . $sistem . '.',
		'Isi angka (rupiah, tanpa teks) pada kolom jenis penghasilan di sheet Template. Kosongkan bila karyawan tidak menerima.',
		'Jangan mengubah kolom ID Karyawan, NIK, Nama Karyawan, dan judul kolom (kode di dalam tanda [ ] dipakai sistem).',
		'Angka tidak boleh negatif. Karyawan yang tidak ada di daftar tidak bisa ditambahkan; hubungi HRD bila ada yang kurang.',
		'Simpan sebagai .xlsx lalu Upload di halaman PPh 21. Upload ulang mengganti angka karyawan yang ada di file.',
		'Jasa Produksi dan Dana Motivasi masuk total pendapatan PPh 21 pada kolom tersendiri (Januari-November: bulan ini, Desember: total Januari-Desember).',
		'Tunjangan Pajak masuk kolom TJ PAJAK (menambah bruto) dan, saat Proses PPh 21, disimpan sebagai komponen gaji Tunjangan Pajak (153) pada periode itu; di Desember bruto setahun memakai total Januari-Desember, yang disimpan hanya angka Desember.',
		'PPh 21 Manual adalah PPh 21 yang sudah dilaporkan secara manual (bukan penghasilan). Saat proses, PPh 21 hitungan sistem dikurangi angka ini dan selisihnya menjadi potongan yang diproses (minimal 0). Bila manual melebihi hitungan sistem, kelebihannya tidak dipotong dan dicatat sebagai lebih bayar di snapshot. Untuk Desember, PPh 21 Manual bulan Januari-November ikut dihitung sebagai PPh 21 yang sudah dibayar.',
	);
	foreach ($petunjuk as $teks) {
		$w->writeSheetRow('Petunjuk', array($teks));
	}

	$nama = 'Template_PenghasilanLain_' . $unit . '_' . $per . '_' . $sistem . '.xlsx';
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment; filename="' . $nama . '"');
	header('Cache-Control: max-age=0');
	$w->writeToStdOut();
	exit;
}

/** Teks dari file/isian untuk pesan: hanya huruf, angka, spasi, dan tanda baca sederhana, maksimal 60 karakter */
function pphBersih($teks)
{
	return preg_replace('/[^A-Za-z0-9 _.\-\[\]]/', '', substr((string) $teks, 0, 60));
}

/** Nomor kolom (0 = A) dari referensi sel seperti D12 */
function pphIndeksKolom($ref)
{
	$n = 0;
	if (preg_match('/^([A-Z]+)/', strtoupper($ref), $m)) {
		foreach (str_split($m[1]) as $ch) {
			$n = $n * 26 + (ord($ch) - 64);
		}
	}
	return $n - 1;
}

/**
 * Baca sheet pertama file .xlsx (tanpa library luar, hanya ZipArchive + SimpleXML).
 * Mengembalikan [nomorBaris][indeksKolom] => teks. Keluar dengan pesan Error bila file tidak wajar.
 */
function pphBacaXlsx($file)
{
	if (!class_exists('ZipArchive')) {
		exit('Error, Server belum mendukung pembacaan file Excel (ekstensi zip).');
	}
	$zip = new ZipArchive();
	if ($zip->open($file) !== true) {
		exit('Error, File bukan Excel (.xlsx) yang valid.');
	}
	if (PHP_VERSION_ID < 80000) {
		libxml_disable_entity_loader(true);
	}
	$bacaXml = function ($nama) use ($zip) {
		$st = $zip->statName($nama);
		if ($st === false) {
			return null;
		}
		if ($st['size'] > 20 * 1024 * 1024) {
			exit('Error, Isi file Excel terlalu besar.');
		}
		$xml = $zip->getFromName($nama);
		if ($xml === false || stripos($xml, '<!DOCTYPE') !== false || stripos($xml, '<!ENTITY') !== false) {
			exit('Error, Isi file Excel tidak dikenali.');
		}
		return preg_replace('/xmlns="[^"]*"/', '', $xml, 1);   # hilangkan namespace bawaan agar xpath sederhana
	};

	## sheet pertama = nomor terkecil
	$sheet = null;
	$nomorTerkecil = null;
	for ($i = 0; $i < $zip->numFiles; $i++) {
		$nama = $zip->getNameIndex($i);
		if (preg_match('#^xl/worksheets/sheet(\d+)\.xml$#', $nama, $m) && ($nomorTerkecil === null || (int) $m[1] < $nomorTerkecil)) {
			$nomorTerkecil = (int) $m[1];
			$sheet = $nama;
		}
	}
	if ($sheet === null) {
		exit('Error, Sheet data tidak ditemukan di file Excel.');
	}

	$teksBersama = array();
	$xmlTeks = $bacaXml('xl/sharedStrings.xml');
	if ($xmlTeks !== null) {
		$sx = simplexml_load_string($xmlTeks, 'SimpleXMLElement', LIBXML_NONET);
		if ($sx === false) {
			exit('Error, Isi file Excel tidak dikenali.');
		}
		foreach ($sx->si as $si) {
			$t = '';
			foreach ($si->xpath('.//t') as $bagian) {
				$t .= (string) $bagian;
			}
			$teksBersama[] = $t;
		}
	}

	$xmlSheet = $bacaXml($sheet);
	$sx = simplexml_load_string($xmlSheet, 'SimpleXMLElement', LIBXML_NONET);
	if ($sx === false || !isset($sx->sheetData)) {
		exit('Error, Isi sheet Excel tidak dikenali.');
	}
	$hasil = array();
	$jumlahBaris = 0;
	foreach ($sx->sheetData->row as $row) {
		if (++$jumlahBaris > 6000) {
			exit('Error, Jumlah baris di file terlalu banyak (maksimal 6000).');
		}
		$no = (int) $row['r'];
		foreach ($row->c as $c) {
			$indeks = pphIndeksKolom((string) $c['r']);
			if ($indeks < 0 || $indeks > 60) {
				continue;
			}
			$tipe = (string) $c['t'];
			if ($tipe == 's') {
				$nilai = isset($teksBersama[(int) $c->v]) ? $teksBersama[(int) $c->v] : '';
			} elseif ($tipe == 'inlineStr') {
				$nilai = '';
				foreach ($c->xpath('.//t') as $bagian) {
					$nilai .= (string) $bagian;
				}
			} else {
				$nilai = (string) $c->v;
			}
			$hasil[$no][$indeks] = trim($nilai);
		}
	}
	$zip->close();
	return $hasil;
}

/** Angka dari sel Excel (angka murni atau teks seperti 1.500.000 / 1,500,000 / Rp 1500000). false = tidak valid, '' = kosong */
function pphAngkaExcel($teks)
{
	$teks = trim(str_ireplace(array('Rp', ' ', "\xC2\xA0"), '', $teks));
	if ($teks === '') {
		return '';
	}
	if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $teks)) {
		$teks = str_replace(',', '.', str_replace('.', '', $teks));
	} elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $teks)) {
		$teks = str_replace(',', '', $teks);
	}
	if (!preg_match('/^-?\d+(\.\d+)?([eE][-+]?\d+)?$/', $teks)) {
		return false;
	}
	return (float) $teks;
}

/** Upload Excel penghasilan lain: semua baris divalidasi dulu; ada satu salah = tidak ada yang disimpan */
function pphUploadLain($per, $unit, $sistem)
{
	global $owlPDO, $dbname;

	pphCekPpp($unit);
	if (!pphTabelLainAda()) {
		exit('Error, Tabel penghasilan lain belum dibuat di database.');
	}
	$status = pphStatusPeriode($per, $unit);
	if ($status['akunTutup']) {
		exit('Error, Periode akuntansi ' . $per . ' SUDAH ditutup, data tidak bisa diubah.');
	}
	if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
		exit('Error, File belum dipilih atau gagal diunggah.');
	}
	$tmp = $_FILES['file']['tmp_name'];
	if (!is_uploaded_file($tmp)) {
		exit('Error, File tidak valid.');
	}
	if (strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)) !== 'xlsx') {
		exit('Error, Format file harus .xlsx (pakai template dari tombol Unduh Template).');
	}
	if ($_FILES['file']['size'] > 2 * 1024 * 1024) {
		exit('Error, Ukuran file maksimal 2 MB.');
	}
	if (file_get_contents($tmp, false, null, 0, 4) !== "PK\x03\x04") {
		exit('Error, File bukan Excel (.xlsx) yang valid.');
	}

	$baris = pphBacaXlsx($tmp);
	if (count($baris) < 2) {
		exit('Error, File kosong atau tidak berisi data.');
	}
	ksort($baris);
	$nomorKepala = key($baris);
	$kepala = $baris[$nomorKepala];
	unset($baris[$nomorKepala]);

	## Judul kolom: kolom A = ID Karyawan, kolom jenis berformat "Nama [KODE]"
	if (!isset($kepala[0]) || stripos($kepala[0], 'ID Karyawan') === false) {
		exit('Error, Format file tidak sesuai: kolom pertama harus ID Karyawan. Gunakan template dari tombol Unduh Template.');
	}
	$jenisAktif = pphJenisTemplate();
	$kolomJenis = array();   # indeks kolom => kode jenis
	foreach ($kepala as $indeks => $judul) {
		if ($indeks < 3) {
			continue;
		}
		if ($judul === '') {
			continue;
		}
		if (!preg_match('/\[([A-Za-z0-9_\-]{1,10})\]\s*$/', $judul, $m) || !isset($jenisAktif[$m[1]])) {
			exit('Error, Judul kolom "' . pphBersih($judul) . '" tidak dikenali. Gunakan template terbaru dan jangan ubah judul kolom.');
		}
		if (in_array($m[1], $kolomJenis, true)) {
			exit('Error, Jenis ' . pphBersih($m[1]) . ' muncul lebih dari satu kali di judul kolom.');
		}
		$kolomJenis[$indeks] = $m[1];
	}
	if (count($kolomJenis) == 0) {
		exit('Error, Tidak ada kolom jenis penghasilan di file.');
	}

	$scope = pphKaryawanScope($per, $unit, $sistem);
	$scopeInt = array();
	foreach ($scope as $kid => $k) {
		$scopeInt[(int) $kid] = $kid;
	}

	$salah = array();
	$data = array();   # [karyawanid][kode] => jumlah
	foreach ($baris as $no => $sel) {
		$idTeks = isset($sel[0]) ? $sel[0] : '';
		$adaIsi = false;
		foreach ($kolomJenis as $indeks => $kode) {
			if (isset($sel[$indeks]) && $sel[$indeks] !== '') {
				$adaIsi = true;
			}
		}
		if ($idTeks === '' && !$adaIsi) {
			continue;   # baris kosong
		}
		if (!preg_match('/^\d{1,10}$/', $idTeks) || !isset($scopeInt[(int) $idTeks])) {
			$salah[] = 'Baris ' . $no . ': ID Karyawan "' . pphBersih($idTeks) . '" tidak termasuk karyawan unit ' . $unit . ' sistem gaji ' . $sistem . '.';
			continue;
		}
		$kid = $scopeInt[(int) $idTeks];
		if (isset($data[$kid])) {
			$salah[] = 'Baris ' . $no . ': karyawan ' . pphBersih($idTeks) . ' muncul lebih dari satu kali.';
			continue;
		}
		$data[$kid] = array();
		foreach ($kolomJenis as $indeks => $kode) {
			$nilai = pphAngkaExcel(isset($sel[$indeks]) ? $sel[$indeks] : '');
			if ($nilai === false) {
				$salah[] = 'Baris ' . $no . ' kolom ' . pphBersih($kode) . ': angka tidak valid.';
			} elseif ($nilai !== '' && ($nilai < 0 || $nilai > 1000000000000)) {
				$salah[] = 'Baris ' . $no . ' kolom ' . pphBersih($kode) . ': angka harus 0 atau lebih dan wajar.';
			} else {
				$data[$kid][$kode] = ($nilai === '') ? 0 : $nilai;
			}
		}
	}
	if (count($salah) > 0) {
		$tampil = array_slice($salah, 0, 12);
		if (count($salah) > 12) {
			$tampil[] = '... dan ' . (count($salah) - 12) . ' kesalahan lain.';
		}
		exit('Error, File tidak disimpan karena ada kesalahan:' . "\n" . implode("\n", $tampil));
	}
	if (count($data) == 0) {
		exit('Error, Tidak ada baris karyawan yang bisa dibaca di file.');
	}

	$totalJenis = array();
	$jumlahNilai = 0;
	try {
		$owlPDO->beginTransaction();
		$idHitung = array();
		foreach (array_keys($data) as $kid) {
			$idHitung[] = $owlPDO->quote((string) $kid);
		}
		$kodeIn = array();
		foreach ($kolomJenis as $kode) {
			$kodeIn[] = $owlPDO->quote($kode);
		}
		foreach (array_chunk($idHitung, 200) as $potong) {
			$owlPDO->exec("delete from " . $dbname . ".sdm_pph21_penghasilanlain where kodeorg='" . $unit . "' and periode='" . $per . "' and kodejenis in (" . implode(',', $kodeIn) . ") and karyawanid in (" . implode(',', $potong) . ")");
		}
		$stmt = $owlPDO->prepare("insert into " . $dbname . ".sdm_pph21_penghasilanlain (`kodeorg`,`periode`,`karyawanid`,`kodejenis`,`jumlah`,`diinputoleh`,`diinputpada`) values (?, ?, ?, ?, ?, ?, now())");
		foreach ($data as $kid => $perJenis) {
			foreach ($perJenis as $kode => $jml) {
				if ($jml > 0) {
					$stmt->execute(array($unit, $per, (string) $kid, $kode, $jml, $_SESSION['standard']['userid']));
					$totalJenis[$kode] = (isset($totalJenis[$kode]) ? $totalJenis[$kode] : 0) + $jml;
					$jumlahNilai++;
				}
			}
		}
		$owlPDO->commit();
	} catch (PDOException $e) {
		if ($owlPDO->inTransaction()) {
			$owlPDO->rollBack();
		}
		error_log('sdm_slave_3pph21_v3 upload lain: ' . $e->getMessage());
		exit('Error, Data tidak dapat disimpan; tidak ada data yang berubah.');
	}

	$rincian = array();
	foreach ($totalJenis as $kode => $jml) {
		$rincian[] = $jenisAktif[$kode] . ' Rp ' . number_format($jml, 0);
	}
	echo 'Berhasil, ' . count($data) . ' karyawan dibaca, ' . $jumlahNilai . ' angka disimpan' . (count($rincian) > 0 ? ' (' . implode(', ', $rincian) . ')' : '') . '.';
}

/** Hapus seluruh angka upload unit & periode ini untuk karyawan pada sistem gaji terpilih */
function pphHapusLain($per, $unit, $sistem)
{
	global $owlPDO, $dbname;

	pphCekPpp($unit);
	if (!pphTabelLainAda()) {
		exit('Error, Tabel penghasilan lain belum dibuat di database.');
	}
	$status = pphStatusPeriode($per, $unit);
	if ($status['akunTutup']) {
		exit('Error, Periode akuntansi ' . $per . ' SUDAH ditutup, data tidak bisa dihapus.');
	}
	$scope = pphKaryawanScope($per, $unit, $sistem);
	$terhapus = 0;
	try {
		$owlPDO->beginTransaction();
		$idHitung = array();
		foreach (array_keys($scope) as $kid) {
			$idHitung[] = $owlPDO->quote((string) $kid);
		}
		foreach (array_chunk($idHitung, 200) as $potong) {
			$terhapus += $owlPDO->exec("delete from " . $dbname . ".sdm_pph21_penghasilanlain where kodeorg='" . $unit . "' and periode='" . $per . "' and karyawanid in (" . implode(',', $potong) . ")");
		}
		$owlPDO->commit();
	} catch (PDOException $e) {
		if ($owlPDO->inTransaction()) {
			$owlPDO->rollBack();
		}
		error_log('sdm_slave_3pph21_v3 hapus lain: ' . $e->getMessage());
		exit('Error, Data tidak dapat dihapus; tidak ada data yang berubah.');
	}
	echo 'Berhasil, ' . $terhapus . ' angka upload dihapus.';
}

/** SQL daftar karyawan yang ikut perhitungan (histori bulanan bila ada, selain itu data karyawan saat ini); tanpa filter sistem gaji */
function pphSqlKaryawan($per, $unit)
{
	global $dbname;

	$adaHistori = pphAmbilSemua("select karyawanid from " . $dbname . ".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='" . $unit . "' and periodegaji='" . $per . "' and statuspajak <> '' and tipekaryawan !=0 ");
	if (count($adaHistori) > 0) {
		return "select * from " . $dbname . ".datakaryawan_hist where approval_status='8' and version_type='B' and lokasitugas='" . $unit . "' and periodegaji='" . $per . "' and statuspajak <> ''  and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and tipekaryawan !=0";
	}
	return "select * from " . $dbname . ".datakaryawan where lokasitugas='" . $unit . "' and statuspajak <> '' and (periodeakhirgaji>='" . $per . "' or periodeakhirgaji='') and ( tanggalmasuk<='" . $per . "-31' or tanggalmasuk='0000-00-00' or tanggalmasuk is null) and tipekaryawan !=0 ";
}


#=== Tampilan / Excel perhitungan PPh 21 ===
/**
 * Tampilan, Excel, dan PDF hanya memuat karyawan yang kena PPh 21 (hasil hitungan tidak nol) atau yang masih punya
 * hasil tersimpan tidak nol (supaya selisihnya terlihat). Dihitung dua kali: pertama tanpa filter untuk tahu siapa
 * yang kena PPh 21, lalu tampilan dengan filter. Mode 'proses' (simpan) menghitung semua karyawan tanpa filter.
 */
function pphPreview($per, $unit, $tipe)
{
	global $dbname, $pphHasil, $pphFilterId, $pphRincian, $sistem;

	$pphFilterId = null;
	#PPP: karyawan Harian (KHL) unit PT PPP memakai rumus tersendiri (batas Rp 4.500.000, tarif 5%), bukan TER/tahunan seperti Bulanan; perusahaan lain tidak berubah
	$hitung = ($sistem == 'Harian' && getindukPT($unit) == 'PPP') ? 'pphPreviewKhl' : 'pphPreviewInti';
	if ($tipe == 'proses') {
		$hitung($per, $unit, 'proses');
		return;
	}

	ob_start();
	$hitung($per, $unit, 'proses');
	ob_end_clean();
	$filter = array();
	foreach ($pphHasil as $kid => $hasil) {
		if ($hasil[0] != 0 || $hasil[1] != 0 || (isset($pphRincian[$kid]) && ($pphRincian[$kid]['sistem21'] != 0 || $pphRincian[$kid]['manual'] != 0 || $pphRincian[$kid]['lebih'] != 0))) {
			$filter[$kid] = true;
		}
	}
	foreach (pphAmbilSemua("select distinct karyawanid from " . $dbname . ".sdm_gaji where kodeorg='" . $unit . "' and idkomponen='42' and periodegaji='" . $per . "' and jumlah <> 0") as $b) {
		$filter[$b['karyawanid']] = true;
	}
	$pphFilterId = $filter;
	$hitung($per, $unit, $tipe);
}

/**
 * Pengelompokan kolom pendapatan seperti Excel PPh 21: [kategori, kode kolom].
 * Tetap = angkanya tidak berfluktuasi (gaji pokok, tunjangan tetap, natura, tunjangan BPJS); Tidak Tetap = sisanya yang berfluktuasi
 * (premi, lembur, tunjangan lain, THR, bonus, perjalanan dinas, jasa produksi, dana motivasi, tunjangan produksi).
 */
function pphKelompokKomponen($id, $nama, $tipe, $tidakTeratur, $setupTetap = array())
{
	$id = (string) $id;
	if (in_array($id, $tidakTeratur, true)) {
		return array('TIDAK', 'THR');   # THR, bonus, dan sejenisnya termasuk pendapatan tidak tetap
	}
	if (isset($setupTetap[$id])) {
		return array('TETAP', 'TUNJ');   # komponen di Setup Premi Tetap per Komponen: nominal tetap tiap bulan
	}
	if ($id == '1') {
		return array('TETAP', 'POKOK');
	}
	if ($id == '118') {
		return array('TETAP', 'NATURA');
	}
	if (in_array($id, array('70', '71', '72', '73', '80', '126'), true)) {
		return array('TETAP', 'BPJS');
	}
	if ($id == '40' || $tipe == 'basic') {
		return array('TETAP', 'TUNJ');   # Premi Tetap dan tunjangan bertipe basic
	}
	if (stripos($nama, 'premi') === 0) {
		return array('TIDAK', 'PREMI');
	}
	if (stripos($nama, 'lembur') === 0) {
		return array('TIDAK', 'LEMBUR');
	}
	return array('TIDAK', 'LAIN');
}

function pphNamaKolom($kode)
{
	$nama = array('POKOK' => 'GAJI POKOK', 'TUNJ' => 'TUNJANGAN TETAP', 'NATURA' => 'NATURA & KENIKMATAN', 'BPJS' => 'TUNJANGAN BPJS',
		'PREMI' => 'PREMI', 'LEMBUR' => 'LEMBUR', 'LAIN' => 'TUNJANGAN LAINNYA', 'THR' => 'THR / BONUS');
	return isset($nama[$kode]) ? $nama[$kode] : $kode;
}

/** Komponen yang disetup di Setup Premi Tetap per Komponen untuk unit itu (nominal tetap tiap bulan): id => true. Kosong bila tabel belum ada. */
function pphKomponenSetupTetap($unit)
{
	global $dbname, $owlPDO;
	$hasil = array();
	if (getindukPT($unit) != 'PPP') {
		return $hasil;   # Setup Komponen Tetap khusus PT PPP
	}
	try {
		$stm = $owlPDO->prepare("select distinct idkomponen from " . $dbname . ".sdm_5premitetapkomp where kodeorg=? and aktif=1");
		if ($stm && $stm->execute(array($unit))) {
			foreach ($stm->fetchAll(PDO::FETCH_COLUMN) as $idk) {
				$hasil[(string) (int) $idk] = true;
			}
		}
	} catch (PDOException $e) {
		$hasil = array();
	}
	return $hasil;
}

/** Perhitungan PPh 21 per karyawan + tampilan; dipanggil lewat pphPreview */
function pphPreviewInti($per, $unit, $tipe)
{
	global $dbname, $pphHasil, $sistem, $pphFilterId, $pphRincian;
	$pphHasil = array();   # [karyawanid] => array(PPh 21, tunjangan PPh 21) dibulatkan; dipakai pphProsesSemua
	$pphRincian = array(); # [karyawanid] => rincian pendapatan yang dipakai (snapshot); dipakai pphProsesSemua

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
	$sqlKaryawan = pphSqlKaryawan($per, $unit);

	## Karyawan yang sistem gajinya belum diisi (tidak masuk Bulanan maupun Harian): hanya dihitung untuk peringatan
	$jmlKosong = 0;
	$hitungKosong = pphAmbilSemua(preg_replace('/^select \*/', 'select count(*) as n', $sqlKaryawan) . " and sistemgaji not in ('Bulanan','Harian')");
	if (count($hitungKosong) > 0) {
		$jmlKosong = (int) $hitungKosong[0]['n'];
	}

	## Hanya karyawan dengan sistem gaji terpilih (Bulanan / Harian) menurut data karyawan
	$sqlKaryawan .= " and sistemgaji='" . $sistem . "'";

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
	#PPP: pengurang hanya iuran JHT (67) dan JP (81) yang dibayar karyawan (sesuai Excel perhitungan PPh 21).
	# Potongan natura (134) mengurangi natura (118) langsung, dan koreksi Lebih HK (133) mengurangi Lebih HK (132) langsung: keduanya bukan kolom pengurang.
	foreach (pphAmbilSemua($sqlKomponen) as $bar) {
		if ($bar['plus'] == 1) {
			$dtkomplus[$bar['idkomponen']] = $bar['idkomponen'];
		} elseif (in_array((string) $bar['idkomponen'], array('67', '81'), true)) {
			$dtkomin[$bar['idkomponen']] = $bar['idkomponen'];
		}
		if (!isset($rupiah[$bar['karyawanid']][$bar['idkomponen']])) {
			$rupiah[$bar['karyawanid']][$bar['idkomponen']] = 0;
		}
		$rupiah[$bar['karyawanid']][$bar['idkomponen']] += $bar['jumlah'];
	}
	foreach ($rupiah as $kidNet => $perKomp) {
		foreach (array(134 => 118, 133 => 132) as $idPot => $idAsal) {
			if (isset($perKomp[$idPot])) {
				if (isset($rupiah[$kidNet][$idAsal])) {
					$rupiah[$kidNet][$idAsal] = max(0, $rupiah[$kidNet][$idAsal] - $perKomp[$idPot]);
				}
				unset($rupiah[$kidNet][$idPot]);
			}
		}
	}
	unset($dtkomplus[134], $dtkomplus[133], $dtkomin[134], $dtkomin[133]);

	#PPP: pendapatan dipisah seperti Harian: Tetap (komponen basic: gaji pokok, tunjangan tetap), Tidak Tetap (komponen tambahan lain: premi, lembur,
	# natura, tunjangan BPJS, ditambah perjalanan dinas), THR / Bonus / Lain (komponen 26/28 atau bertanda tidak teratur, ditambah upload jasa produksi dan dana motivasi)
	$tidakTeratur = array('26', '28');
	foreach (pphAmbilSemua("select id from " . $dbname . ".sdm_ho_component where irregular=1") as $barIrr) {
		$tidakTeratur[] = (string) $barIrr['id'];
	}
	$tipeKomp = makeOption($dbname, 'sdm_ho_component', 'id,type');
	$namaKompAwal = makeOption($dbname, 'sdm_ho_component', 'id,name');
	$setupTetapPph = pphKomponenSetupTetap($unit);   # komponen di Setup Premi Tetap per Komponen = Pendapatan Tetap
	$komGrup = array();     # idkomponen => array(kategori, kode kolom)
	$dtkomTetap = array();
	$dtkomTidak = array();
	$dtkomThr = array();
	$kolGabung = array('TETAP' => array(), 'TIDAK' => array());   # kolom ringkas per kategori
	$urutKode = array('POKOK', 'TUNJ', 'NATURA', 'BPJS', 'PREMI', 'LEMBUR', 'LAIN', 'THR');
	foreach ($dtkomplus as $idKomp) {
		$kk = pphKelompokKomponen($idKomp, isset($namaKompAwal[$idKomp]) ? $namaKompAwal[$idKomp] : '', isset($tipeKomp[$idKomp]) ? $tipeKomp[$idKomp] : '', $tidakTeratur, $setupTetapPph);
		$komGrup[$idKomp] = $kk;
		$kolGabung[$kk[0]][$kk[1]] = $kk[1];
		if ($kk[0] == 'TETAP') {
			$dtkomTetap[$idKomp] = $idKomp;
		} elseif ($kk[0] == 'TIDAK') {
			$dtkomTidak[$idKomp] = $idKomp;
		} else {
			$dtkomThr[$idKomp] = $idKomp;
		}
	}
	foreach ($kolGabung as $katUrut => $kodeUrut) {
		$kolGabung[$katUrut] = array_values(array_intersect($urutKode, $kodeUrut));
	}
	$daftarDt = array('TETAP' => $dtkomTetap, 'TIDAK' => $dtkomTidak);

	if ($bulan == '12') {
		## PPh 21 yang sudah dipotong Jan-Nov (komponen 42)
		$sqlJanNov = "select a.*,plus from " . $dbname . ".sdm_gaji a left join " . $dbname . ".sdm_ho_component b on a.idkomponen=b.id
			where 1=1 and idkomponen = '42' and (periodegaji <= '" . $tahunberjalan . "-11" . "' and periodegaji >= '" . $tahunberjalan . "-01" . "') and kodeorg='" . $unit . "' and jumlah <> 0 ";
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

	#--- Penghasilan perjalanan dinas (khusus PT PPP): PJD yang klaimnya sudah dibayar di kas bank (diposting) pada periode ini.
	#    Dihitung langsung dari data PJD dan kas bank, tidak lewat sdm_gaji, jadi tidak masuk slip gaji dan aktual rekap gaji.
	#    Jan-Nov: klaim dibayar di bulan periode; Desember: seluruh tahun (Jan-Des), sehingga yang belum terhitung bulanan ikut.
	$pjdKaryawan = array();   # penghasilan PJD per karyawan (kolom sendiri, ikut total pendapatan)
	$adaPjd = false;
	if (getindukPT($unit) == 'PPP') {
		$pjdPerKaryawan = array();
		foreach (pphPjdPeriode($unit, $per) as $barisPjd) {
			if (!isset($pjdPerKaryawan[$barisPjd['karyawanid']])) {
				$pjdPerKaryawan[$barisPjd['karyawanid']] = 0;
			}
			$pjdPerKaryawan[$barisPjd['karyawanid']] += $barisPjd['jumlah'];
		}
		foreach ($pjdPerKaryawan as $kidPjd => $jmlPjd) {
			if ($jmlPjd > 0) {
				$adaPjd = true;
				$pjdKaryawan[$kidPjd] = $jmlPjd;
			}
		}
			}
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
	$sistemKar = array();
	foreach (pphAmbilSemua($sqlKaryawan) as $bar) {
		$id = $bar['karyawanid'];
		$sistemKar[$id]   = $bar['sistemgaji'];
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

	## Penghasilan lain dari upload Excel (khusus PT PPP): satu kolom per jenis yang ada nilainya, masuk total pendapatan
	$kolLain = array();        # kodejenis => nama kolom
	$lainKaryawan = array();   # [karyawanid][kodejenis] => jumlah
	$tpKaryawan = array();     # [karyawanid] => Tunjangan Pajak (Jan-Nov: bulan ini, Desember: Jan-Des)
	$tpBulanIni = array();     # [karyawanid] => Tunjangan Pajak periode ini saja (yang disimpan sebagai komponen 153)
	$gtLain = array();
	if (getindukPT($unit) == 'PPP' && pphTabelLainAda()) {
		$namaLain = pphJenisLain(true);
		foreach (pphLainPeriode($unit, $per) as $kidLain => $perJenisLain) {
			if (isset($dtkarid[$kidLain])) {
				$lainKaryawan[$kidLain] = $perJenisLain;
			}
		}
		## Tunjangan Pajak dipisah: masuk kolom TJ PAJAK, bukan kolom penghasilan lain
		foreach ($lainKaryawan as $kidTp => $perJenisTp) {
		if (isset($perJenisTp[PPH_KODE_TUNJPAJAK])) {
		$tpKaryawan[$kidTp] = $perJenisTp[PPH_KODE_TUNJPAJAK];
		unset($lainKaryawan[$kidTp][PPH_KODE_TUNJPAJAK]);
		if (count($lainKaryawan[$kidTp]) == 0) {
		unset($lainKaryawan[$kidTp]);
		}
		}
		}
		$tpBulanIni = pphTunjanganPajakBulan($unit, $per);
		unset($namaLain[PPH_KODE_TUNJPAJAK]);
		foreach ($namaLain as $kodeLain => $nmLain) {
		foreach ($lainKaryawan as $perJenisLain) {
		if (isset($perJenisLain[$kodeLain]) && $perJenisLain[$kodeLain] > 0) {
		$kolLain[$kodeLain] = $nmLain;
					$gtLain[$kodeLain] = 0;
					break;
				}
			}
		}
	}

	## PPh 21 manual (khusus PT PPP): PPh 21 yang sudah dilaporkan manual. Dikurangkan dari hitungan sistem (hasil bisa plus atau minus);
	## untuk Desember, manual Jan-Nov dihitung sebagai PPh 21 yang sudah dibayar.
	$manualPeriode = array();   # [karyawanid] => PPh 21 manual periode ini
	$manualJanNov = array();    # [karyawanid] => PPh 21 manual Januari-November (Desember)
	if (getindukPT($unit) == 'PPP' && pphTabelLainAda()) {
		$dataManual = pphManualPeriode($unit, $per);
		$manualPeriode = $dataManual['ini'];
		$manualJanNov = $dataManual['jannov'];
	}
	$adaManual = (count(array_intersect_key($manualPeriode, $dtkarid)) > 0);

	## Karyawan tanpa pendapatan periode ini (komponen pendapatan, PJD, penghasilan lain semuanya 0) tidak dihitung;
	## pada tampilan / Excel / PDF karyawan yang tidak kena PPh 21 juga disembunyikan (lihat pphPreview)
	$jmlTanpaPendapatan = 0;
	foreach ($dtkarid as $kidCek) {
		$pendapatanCek = isset($pjdKaryawan[$kidCek]) ? $pjdKaryawan[$kidCek] : 0;
		foreach ($dtkomplus as $komCek) {
			$pendapatanCek += isset($rupiah[$kidCek][$komCek]) ? $rupiah[$kidCek][$komCek] : 0;
		}
		if (isset($lainKaryawan[$kidCek])) {
		$pendapatanCek += array_sum($lainKaryawan[$kidCek]);
		}
		$pendapatanCek += isset($tpKaryawan[$kidCek]) ? $tpKaryawan[$kidCek] : 0;
		if ($pendapatanCek <= 0 || ($pphFilterId !== null && !isset($pphFilterId[$kidCek]))) {
			unset($dtkarid[$kidCek]);
			$jmlTanpaPendapatan++;
		}
	}

	$jmlKomplus = count($dtkomplus);
	$jmlKomin   = count($dtkomin);
	$styleUpper = "style='text-transform: uppercase;'";
	$adaExcel   = ($tipe == 'excel' || $tipe == 'pdf');   # tanpa elemen khusus layar
	$kelasRinci = $adaExcel ? '' : " class='kolRinci'";   # kolom rincian komponen bisa dilipat di layar
	$kelasGabung = $adaExcel ? '' : " class='kolGabung'"; # kolom ringkas per kelompok (tampil saat rincian komponen disembunyikan)

	#--- 6. Huruf kolom hasil hitung (dipakai di judul & rumus), urut sesuai tampilan
	$urutHuruf = 0;
	$huruf = function () use (&$urutHuruf) {
		return chr(65 + $urutHuruf++);
	};
	$hTetap = $huruf();
	$hTidak = $huruf();
	if ($bulan == '12') {
		$hBruto = $huruf();
		$hTj = $huruf();
		$hBiaya = $huruf();
		$hPeng = $huruf();
		$hNeto = $huruf();
		$hPtkp = $huruf();
		$hPkp = $huruf();
		$hPkpBulat = $huruf();
		$hPphSetahun = $huruf();
		$hTambah = $huruf();
		$hTerhutang = $huruf();
		$hTahunan = $huruf();
		$hJanNov = $huruf();
		$hFinal = $huruf();
		$rumusNeto = $hBruto . ' + ' . $hTj . ' - ' . $hPeng;
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
				#tabelpph21.sembunyiRinci .kolRinci {display:none}
				#tabelpph21:not(.sembunyiRinci) .kolGabung {display:none}
				#ringkasPph {display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;align-items:stretch}
				#ringkasPph .kartu {display:flex;flex-direction:column;justify-content:flex-start;gap:2px;padding:8px 14px;border:1px solid #c9d6e2;border-radius:6px;background:#F7FAFD;box-sizing:border-box}
				#ringkasPph .kartu span {display:block;font-size:11px;color:#555}
				#ringkasPph .kartu b {font-size:16px;line-height:1.3}
				#barPph {display:flex;align-items:center;gap:18px;flex-wrap:wrap;margin:0 2px 10px 2px;padding:8px 14px;background:#fff;border:1px solid #c9d6e2;border-radius:6px;font-size:13px}
				#barPph button {height:30px;margin:0}
				#barPph label {display:flex;align-items:center;gap:6px;cursor:pointer;color:#333}
				#barPph input[type=checkbox] {margin:0;width:16px;height:16px;cursor:pointer}
				#barPph .cari {margin-left:auto;display:flex;align-items:center;gap:10px}
				#barPph .cari input {width:250px;height:30px;padding:0 10px;border:1px solid #b9c7d6;border-radius:5px;box-sizing:border-box;font-size:13px}
				#barPph #jmlTampil {font-size:12px;color:#555}
		</style>";
		if ($bulan == '12') {
			$caraHitung = "Cara hitung Desember: PPh 21 setahun (neto - PTKP, tarif Pasal 17) dikurangi PPh 21 Januari-November.";
		} else {
			$caraHitung = "Cara hitung Januari-November: PPh 21 = bruto bulan ini x tarif efektif rata-rata (TER) kategori A/B/C.";
		}
		$stream .= "<div style='margin:0 2px 6px 2px;font-size:11px;color:#555'>" . $caraHitung . " Kolom berhuruf dihitung dengan rumus di bawah judulnya.</div>";
		$stream .= "<div class='table-scroll' style='height:900px; overflow-y:auto;'><table id=tabelpph21 class='sortable sembunyiRinci' cellspacing=1 cellpadding=5 style='width:100%;'>";
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
	$thKomponen = function ($teks) use ($styleUpper, $kelasRinci) {
		return "<th " . $styleUpper . " align=center rowspan=2" . $kelasRinci . ">" . $teks . "</th>";
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
	$namaKat = array('TETAP' => 'PENDAPATAN TETAP', 'TIDAK' => 'PENDAPATAN TIDAK TETAP');
	$hKat = array('TETAP' => $hTetap, 'TIDAK' => $hTidak);
	$rumusKat = array(
		'TETAP' => 'Gaji pokok, tunjangan tetap, natura, tunjangan BPJS (angka tidak berfluktuasi)',
		'TIDAK' => 'Premi, lembur, tunjangan lain, THR, bonus, dana motivasi, tunjangan produksi' . ($adaPjd ? ', Perjalanan Dinas' : '') . (count($kolLain) > 0 ? ', ' . implode(', ', $kolLain) : ''),
	);
	foreach (array('TETAP', 'TIDAK') as $kat) {
		if (count($kolGabung[$kat]) > 0) {
			$stream .= $th($namaKat[$kat], 'colspan=' . count($kolGabung[$kat]) . $kelasGabung);
		}
		if (count($daftarDt[$kat]) > 0) {
			$stream .= $th($namaKat[$kat] . ' (RINCIAN KOMPONEN)', 'colspan=' . count($daftarDt[$kat]) . $kelasRinci);
		}
		if ($kat == 'TIDAK') {
			if ($adaPjd) {
				$stream .= $th('PERJALANAN DINAS', 'rowspan=3');
			}
			foreach ($kolLain as $nmLain) {
				$stream .= $th(strtoupper(htmlspecialchars($nmLain, ENT_QUOTES)), 'rowspan=3 title="Upload Excel (penghasilan lain), masuk pendapatan tidak tetap"');
			}
		}
		$stream .= $thRumus($namaKat[$kat], $hKat[$kat], $rumusKat[$kat], 'rowspan=3');
	}
	$ketPjd = '';

	if ($bulan == '12') {
		$stream .= $thRumus('GAJI BRUTO SETAHUN', $hBruto, $hTetap . ' + ' . $hTidak . ' (Jan-Des)', 'rowspan=3');
		$stream .= $thRumus('TJ PPh 21', $hTj, 'Tunjangan pajak (upload Penghasilan Lain)', 'rowspan=3');
	} else {
		$stream .= $thRumus('TOTAL PENDAPATAN', $hPend, $hTetap . ' + ' . $hTidak, 'rowspan=3');
		$stream .= $thRumus('TJ PAJAK', $hTj, 'Tunjangan pajak (upload Penghasilan Lain)', 'rowspan=3');
	}

	if ($bulan == '12') {
		#PENGURANG PAJAK: biaya jabatan + iuran JHT + iuran JP (sesuai Excel). Saat rincian komponen disembunyikan, grup hanya selebar kolom biaya jabatan (diatur toggleRinci).
		# satu grup: biaya jabatan + iuran. Di layar rincian tersembunyi lebih dulu, jadi lebarnya awal hanya biaya jabatan (toggleRinci menyesuaikan)
		$stream .= $th('PENGURANG PAJAK', 'colspan=' . ($adaExcel ? 1 + $jmlKomin : 1) . ($adaExcel ? '' : " class='grupPengurang' data-dasar=1 data-rinci=" . $jmlKomin));
		$stream .= $thRumus('TOTAL PENGURANG', $hPeng, $hBiaya . ' + iuran JHT + iuran JP', 'rowspan=3');
		$stream .= $thRumus('PENGHASILAN NETO', $hNeto, $rumusNeto, 'rowspan=3');
	} elseif ($jmlKomin > 0) {
		$stream .= $th('PENGURANG', 'colspan=' . $jmlKomin . $kelasRinci);
		$stream .= $thRumus('TOTAL PENGURANG', $hPeng, 'Jumlah pengurang', 'rowspan=3');
		$stream .= $thRumus('TOTAL GAJI NETTO', $hNeto, $hPend . ' + ' . $hTj . ' - ' . $hPeng, 'rowspan=3');
	}

	if ($bulan == '12') {
		$rumusPkp = $hNeto . ' - ' . $hPtkp;
		$stream .= $thRumus('PTKP', $hPtkp, 'Sesuai status pajak', 'rowspan=3');
		$stream .= $thRumus('PKP', $hPkp, $rumusPkp, 'rowspan=3');
		$stream .= $thRumus('PKP PEMBULATAN', $hPkpBulat, $hPkp . ' dibulatkan ke bawah (ribuan)', 'rowspan=3');
		$stream .= $thRumus('PPH 21 SETAHUN', $hPphSetahun, 'Tarif Pasal 17 x ' . $hPkpBulat, 'rowspan=3');
		$stream .= $thRumus('TAMBAHAN 20% TANPA NPWP', $hTambah, '20% x ' . $hPphSetahun . ' (bila tanpa NPWP/NIK)', 'rowspan=3');
		$stream .= $thRumus('PPH 21 TERHUTANG', $hTerhutang, $hPphSetahun . ' + ' . $hTambah, 'rowspan=3');
		$stream .= $th('PERBANDINGAN PPH 21', 'colspan=2');
		if ($adaManual) {
			$stream .= $th('PPH 21 SISTEM', 'rowspan=3 title="PPh 21 hitungan sistem (terhutang setahun dikurangi Jan-Nov), sebelum dikurangi PPh 21 manual"');
			$stream .= $th('PPH 21 MANUAL', 'rowspan=3 title="PPh 21 yang sudah dilaporkan secara manual (upload Excel, kolom PPh 21 Manual)"');
			$stream .= $thRumus('SELISIH PPH 21 DIPROSES', $hFinal, 'Sistem - Manual (min. 0)', 'rowspan=3');
			$stream .= $th('LEBIH BAYAR', 'rowspan=3 title="PPh 21 manual yang melebihi hitungan sistem: tidak dipotong, perlu dikembalikan atau dikoreksi"');
		} else {
			$stream .= $thRumus('PPH 21 DESEMBER', $hFinal, $hTahunan . ' - ' . $hJanNov . ' (min. 0)', 'rowspan=3');
			$stream .= $th('LEBIH BAYAR', 'rowspan=3 title="Jumlah PPh 21 Jan-Nov melebihi PPh 21 terhutang setahun: tidak dipotong lagi, perlu dikembalikan atau dikoreksi (menunggu keputusan tim pajak)"');
		}
	} else {
		$stream .= $th('PERHITUNGAN PPH 21 (TER)', 'colspan=3');
		if ($adaManual) {
			$stream .= $th('PPH 21 SISTEM', 'rowspan=3 title="PPh 21 hitungan sistem (bruto x TER), sebelum dikurangi PPh 21 manual"');
			$stream .= $th('PPH 21 MANUAL', 'rowspan=3 title="PPh 21 yang sudah dilaporkan secara manual (upload Excel, kolom PPh 21 Manual)"');
			$stream .= $thRumus('SELISIH PPH 21 DIPROSES', $hFinal, 'Sistem - Manual (min. 0)', 'rowspan=3');
			$stream .= $th('LEBIH BAYAR', 'rowspan=3 title="PPh 21 manual yang melebihi hitungan sistem: tidak dipotong, perlu dikembalikan atau dikoreksi"');
		} else {
			$stream .= $thRumus('PPH 21 BULAN INI', $hFinal, $hBruto . ' x ' . $hTer . ' (+20% bila tanpa NPWP/NIK)', 'rowspan=3');
		}
	}
	if (!$adaExcel) {
		$stream .= $th('TERSIMPAN', 'rowspan=3 title="Hasil proses sebelumnya yang tersimpan (komponen PPh 21 di gaji)"');
	}
	$stream .= "</tr>";

	$stream .= "<tr class=rowcontent>";
	$thGabung = function ($teks) use ($styleUpper, $kelasGabung) {
		return "<th " . $styleUpper . " align=center rowspan=2" . $kelasGabung . ">" . $teks . "</th>";
	};
	foreach (array('TETAP', 'TIDAK') as $kat) {
		foreach ($kolGabung[$kat] as $kodeKol) {
			$stream .= $thGabung(pphNamaKolom($kodeKol));
		}
		foreach ($daftarDt[$kat] as $komplus) {
			$stream .= $thKomponen($optNmKomponen[$komplus]);
		}
	}
	if ($bulan == '12') {
		$stream .= $thRumus('BIAYA JABATAN', $hBiaya, number_format($persenjabatan) . '% x (' . $hBruto . ' + ' . $hTj . '), maks ' . number_format($maxjabatan * 12) . '/th', 'rowspan=2');
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
	$tunjangan_pph21 = $tpKaryawan;  # Tunjangan Pajak dari upload Penghasilan Lain (PT PPP); tanpa upload = 0
	$dataPdf = array();         # baris ringkas untuk tarikan PDF

	# total
	$gttdtkompus = 0;
	$gtPjdKolom = 0;
	$gtTetapKolom = 0;
	$gtTidakKolom = 0;
	$gtThrKolom = 0;
	$gttdtkommin = 0;
	$gtTjpajak = 0;
	$gtbiayajabatan = 0;
	$gtnetto = 0;
	$gtgajiBruto = 0;
	$gtpph21terutanghitung = 0;
	$gtpph21_jan_nov = 0;
	$gtPph21 = 0;
	$gtSistem21 = 0;
	$gtManual21 = 0;
	$gtLebih = 0;
	$gtTersimpan = 0;

	## PPh 21 yang sudah tersimpan dari proses sebelumnya (pembanding di layar)
	$tersimpan = array();
	if (!$adaExcel) {
		foreach (pphAmbilSemua("select karyawanid, sum(jumlah) as jumlah from " . $dbname . ".sdm_gaji where kodeorg='" . $unit . "' and idkomponen='42' and periodegaji='" . $per . "' group by karyawanid") as $bar) {
			$tersimpan[$bar['karyawanid']] = $bar['jumlah'];
		}
	}

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
		$gabKar = array('TETAP' => array(), 'TIDAK' => array());
		$subKat = array('TETAP' => 0, 'TIDAK' => 0);
		foreach ($komGrup as $idKomp => $kk) {
			$nilaiKomp = isset($rupiah[$karid][$idKomp]) ? $rupiah[$karid][$idKomp] : 0;
			$gabKar[$kk[0]][$kk[1]] = (isset($gabKar[$kk[0]][$kk[1]]) ? $gabKar[$kk[0]][$kk[1]] : 0) + $nilaiKomp;
			$subKat[$kk[0]] += $nilaiKomp;
		}
		## Perjalanan Dinas (PT PPP) dan penghasilan lain dari upload (jasa produksi, dana motivasi): kolom sendiri, masuk pendapatan tidak tetap
		$pjdKar = ($adaPjd && isset($pjdKaryawan[$karid])) ? $pjdKaryawan[$karid] : 0;
		$lainKar = 0;
		foreach ($kolLain as $kodeLain => $nmLain) {
			$lainKar += isset($lainKaryawan[$karid][$kodeLain]) ? $lainKaryawan[$karid][$kodeLain] : 0;
		}
		foreach (array('TETAP', 'TIDAK') as $kat) {
			foreach ($kolGabung[$kat] as $kodeKol) {
				$stream .= "<td align=right" . $kelasGabung . " >" . number_format(isset($gabKar[$kat][$kodeKol]) ? $gabKar[$kat][$kodeKol] : 0, 0) . "</td>";
			}
			foreach ($daftarDt[$kat] as $komplus) {
				$nilai = isset($rupiah[$karid][$komplus]) ? $rupiah[$karid][$komplus] : 0;
				$stream .= "<td align=right" . $kelasRinci . " >" . number_format($nilai, 0) . "</td>";
			}
			if ($kat == 'TIDAK') {
				if ($adaPjd) {
					$stream .= "<td align=right >" . number_format($pjdKar, 0) . "</td>";
					$gtPjdKolom += $pjdKar;
				}
				foreach ($kolLain as $kodeLain => $nmLain) {
					$nilaiLain = isset($lainKaryawan[$karid][$kodeLain]) ? $lainKaryawan[$karid][$kodeLain] : 0;
					$stream .= "<td align=right >" . number_format($nilaiLain, 0) . "</td>";
					$gtLain[$kodeLain] += $nilaiLain;
				}
			}
			$stream .= "<td align=right >" . number_format($subKat[$kat] + ($kat == 'TIDAK' ? $pjdKar + $lainKar : 0), 0) . "</td>";
		}
		$pendapatanGaji = $subKat['TETAP'] + $subKat['TIDAK'];   # jumlah komponen pendapatan dari gaji (untuk snapshot)
		$totalPendapatan = $pendapatanGaji + $pjdKar + $lainKar;
		$gtTetapKolom += $subKat['TETAP'];
		$gtTidakKolom += $subKat['TIDAK'] + $pjdKar + $lainKar;
		$gttdtkompus += $totalPendapatan;
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

			foreach ($dtkomin as $kommin) {
				$nilai = isset($rupiah[$karid][$kommin]) ? $rupiah[$karid][$kommin] : 0;
				$stream .= "<td align=right" . $kelasRinci . " >" . number_format($nilai, 0) . "</td>";
				$gttdtkommin += $nilai;
			}
			$stream .= "<td align=right >" . number_format($biayaJabatan + $totalPengurang, 0) . "</td>";
			$stream .= "<td align=right >" . number_format($gajiNetto, 0) . " </td>";
			$gtnetto += $gajiNetto;

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
			$janNov = (isset($pph21_jan_nov[$karid]) ? $pph21_jan_nov[$karid] : 0) + (isset($manualJanNov[$karid]) ? $manualJanNov[$karid] : 0);   # hasil proses Jan-Nov + PPh 21 manual Jan-Nov
			$stream .= "<td align=right >" . number_format($janNov, 0) . " </td>";
			$gtpph21_jan_nov += $janNov;

			## PPh 21 Desember = terhutang setahun - yang sudah dipotong Jan-Nov
			$pph21 = $pph21Terhutang - $janNov;
			$lebihAwal = ($pph21 < 0) ? -$pph21 : 0;   # sudah dibayar Jan-Nov melebihi terhutang setahun
			if ($pph21 < 0) {
				$pph21 = 0;
			}
			## PPh 21 manual (sudah dilaporkan) dikurangkan dari hitungan sistem; hasil bisa plus atau minus
			$pph21Sistem = $pph21;
			$manualKar = isset($manualPeriode[$karid]) ? $manualPeriode[$karid] : 0;
			$selisihMentah = $pph21Sistem - $manualKar;
			$pph21 = max(0, $selisihMentah);                          # potongan di slip tidak pernah minus
			$lebihBayar = $lebihAwal + max(0, -$selisihMentah);       # kelebihan bayar: tidak dipotong, dicatat di snapshot
			$gtLebih += round($lebihBayar);
			$gtSistem21 += round($pph21Sistem);
			$gtManual21 += $manualKar;
			if ($adaManual) {
				$stream .= "<td align=right >" . number_format($pph21Sistem, 0) . "</td>";
				$stream .= "<td align=right >" . number_format($manualKar, 0) . "</td>";
			}
			$stream .= "<td align=right id='pph21x" . $no . "' style='font-weight:bold;background-color:#FFF2CC'>" . number_format($pph21, 0) . "</td>";
			$stream .= "<td align=right" . ($lebihBayar > 0 ? " style='color:#b42318;font-weight:bold'" : "") . ">" . number_format($lebihBayar, 0) . "</td>";   # Desember: kolom lebih bayar selalu tampil
			$gtPph21 += round($pph21);   # dijumlah per karyawan dalam rupiah bulat, sama dengan yang disimpan
			$pphHasil[$karid] = array(round($pph21), round(isset($tpBulanIni[$karid]) ? $tpBulanIni[$karid] : 0));   # yang disimpan sebagai komponen 153 = Tunjangan Pajak periode ini saja
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
					$stream .= "<td align=right" . $kelasRinci . " >" . number_format($nilai, 0) . "</td>";
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

			$lebihAwal = 0;
			$pph21 = $gajiBruto * $tarifTer / 100;
			if (!$punyaNpwp[$karid]) {
				$pph21 = $pph21 * 1.2;
			}
			## PPh 21 manual (sudah dilaporkan) dikurangkan dari hitungan sistem; hasil bisa plus atau minus
			$pph21Sistem = $pph21;
			$manualKar = isset($manualPeriode[$karid]) ? $manualPeriode[$karid] : 0;
			$selisihMentah = $pph21Sistem - $manualKar;
			$pph21 = max(0, $selisihMentah);                          # potongan di slip tidak pernah minus
			$lebihBayar = $lebihAwal + max(0, -$selisihMentah);       # kelebihan bayar: tidak dipotong, dicatat di snapshot
			$gtLebih += round($lebihBayar);
			$gtSistem21 += round($pph21Sistem);
			$gtManual21 += $manualKar;
			if ($adaManual) {
				$stream .= "<td align=right >" . number_format($pph21Sistem, 0) . "</td>";
				$stream .= "<td align=right >" . number_format($manualKar, 0) . "</td>";
			}
			$stream .= "<td align=right id='pph21x" . $no . "' style='font-weight:bold;background-color:#FFF2CC'>" . number_format($pph21, 0) . "</td>";
			if ($adaManual) {
				$stream .= "<td align=right" . ($lebihBayar > 0 ? " style='color:#b42318;font-weight:bold'" : "") . ">" . number_format($lebihBayar, 0) . "</td>";
			}
			$gtPph21 += round($pph21);   # dijumlah per karyawan dalam rupiah bulat, sama dengan yang disimpan
			$pphHasil[$karid] = array(round($pph21), round(isset($tpBulanIni[$karid]) ? $tpBulanIni[$karid] : 0));   # yang disimpan sebagai komponen 153 = Tunjangan Pajak periode ini saja
			$dataPdf[] = array($no, $nik[$karid], $nmkar[$karid], $statuspajak[$karid], @$optKategori[$statuspajak[$karid]], $totalPendapatan, $tunjangan, $gajiBruto, $tarifTer, $pph21);
		}

		## Rincian yang dipakai pada hitungan ini (disimpan sebagai snapshot saat Proses PPh 21)
		$pjdSnap = isset($pjdKaryawan[$karid]) ? $pjdKaryawan[$karid] : 0;
		$lainSnap = array();
		if (isset($lainKaryawan[$karid])) {
			foreach ($lainKaryawan[$karid] as $kodeSnap => $jmlSnap) {
				if ($jmlSnap > 0) {
					$lainSnap[$kodeSnap] = $jmlSnap;
				}
			}
		}
		$pphRincian[$karid] = array(
			'sistem' => isset($sistemKar[$karid]) ? $sistemKar[$karid] : $sistem,
			'metode' => ($bulan == '12') ? 'TAHUNAN' : 'TER',
			'gaji' => $pendapatanGaji,
			'pjd' => $pjdSnap,
			'lain' => $lainSnap,
			'total' => $totalPendapatan,
			'tunjangan' => $tunjangan,
			'pengurang' => $totalPengurang,
			'tarif' => ($bulan == '12') ? null : $tarifTer,
			'npwp' => $punyaNpwp[$karid] ? 1 : 0,
			'terhutang' => ($bulan == '12') ? $pph21Terhutang : $pph21,
			'jannov' => ($bulan == '12') ? $janNov : 0,
			'sistem21' => round($pph21Sistem),
			'manual' => $manualKar,
			'lebih' => round($lebihBayar),
			'pph21' => round($pph21),
		);

		if (!$adaExcel) {
			$nilaiSimpan = isset($tersimpan[$karid]) ? $tersimpan[$karid] : 0;
			$gtTersimpan += $nilaiSimpan;
			$beda = (count($tersimpan) > 0 && round($pph21) != round($nilaiSimpan));
			$stream .= "<td align=right" . ($beda ? " style='background-color:#FCE8E6' title='Beda dengan hasil proses sebelumnya, perlu diproses ulang'" : "") . ">" . ($nilaiSimpan > 0 ? number_format($nilaiSimpan, 0) : '-') . "</td>";
		}
		$stream .= "</tr>";
	}

	#--- 9. Baris total
	$stream .= "<tr class=rowcontent>";
	$stream .= "<td align=center colspan=7><b>TOTAL</b></td>";
	$selTotal = function ($nilai) {
		return "<td align=center><b>" . $nilai . "</b></td>";
	};
	foreach (array('TETAP', 'TIDAK') as $kat) {
		if (count($kolGabung[$kat]) > 0) {
			$stream .= "<td align=center colspan=" . count($kolGabung[$kat]) . "" . $kelasGabung . "></td>";
		}
		if (count($daftarDt[$kat]) > 0) {
			$stream .= "<td align=center colspan=" . count($daftarDt[$kat]) . "" . $kelasRinci . "></td>";
		}
		if ($kat == 'TIDAK') {
			if ($adaPjd) {
				$stream .= $selTotal(number_format($gtPjdKolom));
			}
			foreach ($kolLain as $kodeLain => $nmLain) {
				$stream .= $selTotal(number_format($gtLain[$kodeLain]));
			}
		}
		$stream .= $selTotal(number_format($kat == 'TETAP' ? $gtTetapKolom : $gtTidakKolom));
	}
	if ($bulan == '12') {
		$stream .= $selTotal(number_format($gttdtkompus));
		$stream .= $selTotal(number_format($gtTjpajak));
		$stream .= $selTotal(number_format($gtbiayajabatan));
		if ($jmlKomin > 0) {
			$stream .= "<td align=center colspan=" . $jmlKomin . "" . $kelasRinci . "><b></b></td>";
		}
		$stream .= $selTotal(number_format($gtbiayajabatan + $gttdtkommin));
		$stream .= $selTotal(number_format($gtnetto));
		# PTKP, PKP, PKP pembulatan, PPh setahun, tambahan 20% (tidak dijumlahkan)
		for ($i = 0; $i < 5; $i++) {
			$stream .= $selTotal('');
		}
		$stream .= $selTotal(number_format($gtpph21terutanghitung));
		$stream .= $selTotal(number_format($gtpph21terutanghitung));
		$stream .= $selTotal(number_format($gtpph21_jan_nov));
		if ($adaManual) {
			$stream .= $selTotal(number_format($gtSistem21));
			$stream .= $selTotal(number_format($gtManual21));
		}
		$stream .= $selTotal(number_format($gtPph21));
		$stream .= $selTotal(number_format($gtLebih));
	} else {
		$stream .= $selTotal(number_format($gttdtkompus));
		$stream .= $selTotal(number_format($gtTjpajak));
		if ($jmlKomin > 0) {
			$stream .= "<td align=center colspan=" . $jmlKomin . "" . $kelasRinci . "><b></b></td>";
			$stream .= $selTotal(number_format($gttdtkommin));
			$stream .= $selTotal(number_format($gtnetto));
		}
		$stream .= $selTotal(number_format($gtgajiBruto));
		$stream .= $selTotal('');
		$stream .= $selTotal('');
		if ($adaManual) {
			$stream .= $selTotal(number_format($gtSistem21));
			$stream .= $selTotal(number_format($gtManual21));
		}
		$stream .= $selTotal(number_format($gtPph21));
		if ($adaManual) {
			$stream .= $selTotal(number_format($gtLebih));
		}
	}
	if (!$adaExcel) {
		$stream .= $selTotal(number_format($gtTersimpan));
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

	## Tampilan layar: status tutup buku, ringkasan angka, dan bilah aksi (Proses, rincian komponen, cari) di atas tabel
	$statusPeriode = pphStatusPeriode($per, $unit);
	$kartu = function ($judul, $nilai, $catatan = '') {
		return "<div class=kartu><span>" . $judul . "</span><b>" . $nilai . "</b>" . ($catatan != '' ? "<span>" . $catatan . "</span>" : "") . "</div>";
	};
	$ringkas = "<div id=ringkasPph style='margin:8px 2px 10px 2px'>";
	$ketSistem = 'Sistem gaji ' . $sistem . ($jmlTanpaPendapatan > 0 ? ' | ' . number_format($jmlTanpaPendapatan) . ' tanpa PPh 21 tidak ditampilkan' : '');
	$ringkas .= $kartu('Karyawan', number_format($no), $ketSistem);
	$ringkas .= $kartu($bulan == '12' ? 'Bruto setahun' : 'Total pendapatan', number_format($gttdtkompus), 'hanya karyawan yang tampil');
	$ringkas .= $kartu('Pendapatan tetap', number_format($gtTetapKolom), 'gaji pokok, tunjangan tetap, natura, tunjangan BPJS');
	$ringkas .= $kartu('Pendapatan tidak tetap', number_format($gtTidakKolom), 'premi, lembur, THR, bonus, dana motivasi, tunjangan produksi, perjalanan dinas');
	if ($adaPjd) {
		$ringkas .= $kartu('Perjalanan Dinas', number_format($gtPjdKolom), 'sudah termasuk total pendapatan');
	}
	foreach ($kolLain as $kodeLain => $nmLain) {
		$ringkas .= $kartu(htmlspecialchars($nmLain, ENT_QUOTES), number_format($gtLain[$kodeLain]), 'upload Excel, sudah termasuk total pendapatan');
	}
	if ($adaManual) {
		$ringkas .= $kartu('PPh 21 sistem', number_format($gtSistem21), 'hitungan penuh dari sistem');
		$ringkas .= $kartu('PPh 21 manual', number_format($gtManual21), 'sudah dilaporkan, dikurangkan');
		$ringkas .= $kartu('Selisih diproses', number_format($gtPph21), 'sistem - manual, minimal 0');
	} else {
		$ringkas .= $kartu($bulan == '12' ? 'PPh 21 Desember (hitungan)' : 'PPh 21 bulan ini (hitungan)', number_format($gtPph21));
	}
	if ($gtLebih > 0) {
		$ringkas .= $kartu('Lebih bayar', number_format($gtLebih), 'tidak dipotong, perlu dikembalikan / dikoreksi');
	}
	$sudahProses = (count(array_intersect_key($tersimpan, $dtkarid)) > 0);
	$selisih = round($gtPph21) - round($gtTersimpan);
	$ringkas .= $kartu('PPh 21 tersimpan', $sudahProses ? number_format($gtTersimpan) : 'belum diproses', $sudahProses ? ($selisih == 0 ? 'sama dengan hitungan' : 'selisih ' . number_format($selisih) . ' dari hitungan') : 'periode ini belum pernah diproses');
	$ringkas .= "</div>";

	$bar = "<div id=barPph>";
	if ($statusPeriode['boleh']) {
		$bar .= "<button class=mybutton id=btnProsesPph onclick=prosesPph(" . $no . ")>" . $_SESSION['lang']['proses'] . " PPh 21 (" . $no . " karyawan)</button>";
	}
	$bar .= "<label title='Tampilkan kolom rincian pendapatan dan pengurang per komponen'><input type=checkbox id=chkRinci onclick='toggleRinci(this.checked)'> Tampilkan rincian komponen</label>";
	$bar .= "<div class=cari><span id=jmlTampil></span><input type=text id=cariKaryawan placeholder='Cari NIK / nama karyawan' onkeyup='cariKaryawan()'></div>";
	$bar .= "</div>";

	$catatanSistem = array();
	if ($sistem == 'Harian') {
		$catatanSistem[] = array('Sistem gaji Harian', 'Perhitungan khusus karyawan harian belum dibedakan: sementara memakai rumus yang sama dengan Bulanan.');
	}
	if ($jmlKosong > 0) {
		$catatanSistem[] = array(number_format($jmlKosong) . ' karyawan belum diisi sistem gajinya', 'Karyawan ini tidak ikut Bulanan maupun Harian. Lengkapi sistem gajinya di data karyawan agar ikut diproses.');
	}

	echo pphBannerStatus($statusPeriode, $per, $unit, $bulan, $catatanSistem) . "@@VALIDASI@@" . $ringkas . $bar . $stream . "####" . $no;
}


/** Status tutup buku ringkas (satu baris lencana) di atas hasil perhitungan; tanpa kata Error/Warning/Gagal agar tidak dianggap respons gagal */
function pphBannerStatus($status, $per, $unit, $bulan, $catatanLain = array())
{
	$h = function ($teks) {
		return htmlspecialchars($teks, ENT_QUOTES);
	};
	$gaya = array(
		'ok' => array('#1a7f37', '&#10003;', '#E3F4E8'),
		'salah' => array('#b42318', '&#10007;', '#FCE8E6'),
		'catatan' => array('#8a5a00', '!', '#FFF4D6'),
	);
	$baris = function ($jenis, $judul, $keterangan) use ($gaya, $h) {
		$g = $gaya[$jenis];
		return "<div style='display:flex;align-items:flex-start;gap:10px;padding:6px 0;border-top:1px solid #eef1f5'>"
			. "<span style='flex:0 0 22px;height:22px;line-height:22px;text-align:center;border-radius:50%;font-weight:bold;font-size:13px;background:" . $g[2] . ";color:" . $g[0] . "'>" . $g[1] . "</span>"
			. "<div><div style='font-weight:bold;color:#222'>" . $h($judul) . "</div><div style='font-size:12px;color:#555'>" . $h($keterangan) . "</div></div></div>";
	};

	$boleh = $status['boleh'];
	$warna = $boleh ? '#1a7f37' : '#b42318';
	#bisa dilipat (bawaan terlipat): judul dan lencana status tetap terlihat, rincian validasi dibuka dengan klik
	$html = "<details style='margin:8px 2px 10px 2px;padding:8px 16px 6px 16px;background:#fff;border:1px solid #c9d6e2;border-left:5px solid " . $warna . ";border-radius:6px;font-size:13px'>";
	$html .= "<summary style='cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding-bottom:6px'>"
		. "<span style='font-weight:bold;font-size:14px;color:#222'>Validasi Proses PPh 21 &middot; " . $h($unit) . " &middot; " . $h($per) . " <span style='font-weight:normal;font-size:11px;color:#777'>(klik untuk melihat rincian)</span></span>"
		. "<span style='padding:3px 12px;border-radius:12px;font-size:12px;font-weight:bold;background:" . ($boleh ? '#E3F4E8' : '#FCE8E6') . ";color:" . $warna . "'>"
		. ($boleh ? 'Siap diproses' : 'Belum bisa diproses') . "</span></summary>";

	$html .= $baris($status['gajiTutup'] ? 'ok' : 'salah', 'Periode gaji ' . ($status['gajiTutup'] ? 'sudah ditutup' : ($status['gajiAda'] ? 'belum ditutup' : 'belum ada')), $status['pesan'][0][1]);
	$html .= $baris($status['akunTutup'] ? 'salah' : 'ok', 'Periode akuntansi ' . ($status['akunTutup'] ? 'sudah ditutup' : 'masih terbuka'), $status['pesan'][1][1]);
	if ($bulan == '12') {
		$kosong = pphBulanBelumDiproses($per, $unit);
		if (count($kosong) > 0) {
			$html .= $baris('catatan', 'PPh 21 Januari-November belum lengkap (' . count($kosong) . ' bulan)', 'Belum ada di periode: ' . implode(', ', $kosong) . '. Hitungan Desember memakai PPh 21 Januari-November yang tersedia.');
		} else {
			$html .= $baris('ok', 'PPh 21 Januari-November lengkap', 'Dipakai sebagai pengurang pada hitungan Desember.');
		}
	}
	foreach ($catatanLain as $cat) {
		$html .= $baris('catatan', $cat[0], $cat[1]);
	}
	if (!$boleh) {
		$html .= "<div style='padding:6px 0 4px 32px;font-size:12px;color:#b42318;border-top:1px solid #eef1f5'>Tombol Proses PPh 21 disembunyikan sampai syarat tutup buku terpenuhi. Hitungan di bawah tetap bisa dilihat dan ditarik ke Excel / PDF.</div>";
	}
	return $html . "</details>";
}

#=== Kop laporan (nama PT dari induk unit) untuk Excel dan PDF ===
function pphKop($unit, $per, $bulan)
{
	global $dbname, $sistem;

	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk', "kodeorganisasi='" . $unit . "'");
	$pt = isset($induk[$unit]) ? $induk[$unit] : $_SESSION['empl']['kodeorganisasi'];
	$hdpt = setheadreport($unit, $pt);

	return array(
		'nama' => $hdpt['nama'],
		'logo' => isset($hdpt['logo']) ? $hdpt['logo'] : '',
		'judul' => 'PERHITUNGAN PPH PASAL 21 - PERIODE ' . $per,
		'info' => 'Unit: ' . $unit . ' - ' . getNamaOrg($unit) . ' | Periode: ' . $per . ' | Sistem gaji: ' . $sistem . ' | Metode: ' . ($bulan == 'KHL' ? 'Harian/KHL: 5% x (pendapatan - Rp 4.500.000)' : ($bulan == '12' ? 'Perhitungan setahun (Desember)' : 'TER bulanan')),
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

	if ($bulan == 'KHL') {
		$judulKolom = array('No', 'NIK', 'Nama', 'Pendapatan Tetap', 'Pendapatan Tidak Tetap', 'Total', 'Kena PPh 21', 'PPh 21');
		$lebar = array(10, 28, 66, 35, 35, 35, 35, 33);
		$catatan = 'Pendapatan Tidak Tetap = premi, lembur, THR, bonus, dana motivasi, tunjangan produksi, perjalanan dinas | Total = Tetap + Tidak Tetap | Kena PPh 21 = Total - Rp 4.500.000 (min. 0) | PPh 21 = 5% x Kena PPh 21 | Tunjangan BPJS dan natura tidak dihitung; potongan tidak mengurangi';
		$kolomAngka = array(3, 4, 5, 6, 7);
		$kolomJumlah = array(3, 4, 5, 6, 7);
	} elseif ($bulan == '12') {
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
				$tampil = number_format($nilai, ($bulan != '12' && $bulan != 'KHL' && $i == 8) ? 2 : 0);
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


/**
 * Perhitungan PPh 21 karyawan Harian / tidak tetap (KHL), mengikuti Excel "PPh 21 Karyawan - Pegawai Tidak Tetap (KHL)":
 *   Total = Pendapatan Tetap + Pendapatan Tidak Tetap + THR/Bonus/Lain
 *   PPh 21 = 0 bila Total <= Rp 4.500.000; selebihnya 5% x (Total - Rp 4.500.000)
 * Tanpa TER, tanpa status pajak/PTKP, tanpa tambahan 20% tanpa NPWP, tanpa perhitungan ulang Desember, tanpa PPh 21 manual.
 * Pendapatan Tetap = komponen bertipe basic (gaji pokok dan tunjangan tetap); Pendapatan Tidak Tetap = komponen tambahan lain (premi, lembur, dll);
 * THR/Bonus/Lain = THR, bonus, komponen tidak teratur, perjalanan dinas, dan penghasilan lain dari upload (jasa produksi, dana motivasi).
 * Tunjangan BPJS dan natura tidak dihitung; komponen pengurang (potongan HK, potongan natura, dll) tidak mengurangi.
 * Hasil dipakai kembali oleh pphProsesSemua lewat $pphHasil dan $pphRincian, seperti pphPreviewInti.
 */
function pphPreviewKhl($per, $unit, $tipe)
{
	global $dbname, $pphHasil, $sistem, $pphFilterId, $pphRincian;
	$pphHasil = array();
	$pphRincian = array();

	$cek = pphAmbilSemua("select count(*) as jmlhrow from " . $dbname . ".sdm_gaji where periodegaji = '" . $per . "' and kodeorg = '" . $unit . "'");
	if ($cek[0]['jmlhrow'] == 0) {
		exit("Warning : Data gaji tidak ada untuk periode " . $per . " di unit " . $unit . ", Silahkan proses gaji dan tutup periode penggajian...");
	}

	#--- Karyawan Harian (status pajak tidak disyaratkan: KHL tidak memakai PTKP)
	$sqlDasar = preg_replace("/\s+and statuspajak <> ''/", '', pphSqlKaryawan($per, $unit));
	$jmlKosong = 0;
	$hitungKosong = pphAmbilSemua(preg_replace('/^select \*/', 'select count(*) as n', $sqlDasar) . " and sistemgaji not in ('Bulanan','Harian')");
	if (count($hitungKosong) > 0) {
		$jmlKosong = (int) $hitungKosong[0]['n'];
	}
	$sqlKaryawan = $sqlDasar . " and sistemgaji='Harian'";

	#--- Komponen pendapatan: yang kena PPh 21 dan bertanda penambah; tunjangan BPJS dan natura dikecualikan (sesuai Excel KHL)
	$kecuali = array('70', '71', '72', '73', '80', '126', '118', '156');
	$tidakTeratur = array('26', '28');
	$setupTetapPph = pphKomponenSetupTetap($unit);   # komponen di Setup Premi Tetap per Komponen = Pendapatan Tetap
	$komp = array();   # id => array(kategori TETAP|TIDAK, kode kolom ringkas)
	$tipeKomp = makeOption($dbname, 'sdm_ho_component', 'id,type');
	$namaKompAwal = makeOption($dbname, 'sdm_ho_component', 'id,name');
	foreach (pphAmbilSemua("select id,type,irregular from " . $dbname . ".sdm_ho_component where pph21=1 and plus=1") as $bar) {
		if (in_array((string) $bar['id'], $kecuali, true)) {
			continue;
		}
		$komp[$bar['id']] = pphKelompokKomponen($bar['id'], isset($namaKompAwal[$bar['id']]) ? $namaKompAwal[$bar['id']] : '', $bar['type'], $bar['irregular'] == 1 ? array_merge($tidakTeratur, array((string) $bar['id'])) : $tidakTeratur, $setupTetapPph);
	}
	$rupKomp = array();       # [karyawanid][idkomponen] => jumlah
	$adaKomp = array('TETAP' => array(), 'TIDAK' => array());   # komponen yang muncul per kategori
	$kolGabung = array('TETAP' => array(), 'TIDAK' => array());   # kolom ringkas per kategori
	if (count($komp) > 0) {
		$in = "'" . implode("','", array_map('addslashes', array_keys($komp))) . "'";
		foreach (pphAmbilSemua("select karyawanid,idkomponen,sum(jumlah) as jumlah from " . $dbname . ".sdm_gaji where periodegaji = '" . $per . "' and kodeorg = '" . $unit . "' and jumlah > 0 and idkomponen in (" . $in . ") group by karyawanid,idkomponen") as $bar) {
			$kat = $komp[$bar['idkomponen']][0];
			$rupKomp[$bar['karyawanid']][$bar['idkomponen']] = $bar['jumlah'];
			$adaKomp[$kat][$bar['idkomponen']] = $bar['idkomponen'];
			$kolGabung[$kat][$komp[$bar['idkomponen']][1]] = $komp[$bar['idkomponen']][1];
		}
	}
	$urutKode = array('POKOK', 'TUNJ', 'NATURA', 'BPJS', 'PREMI', 'LEMBUR', 'LAIN', 'THR');
	foreach ($kolGabung as $katUrut => $kodeUrut) {
		$kolGabung[$katUrut] = array_values(array_intersect($urutKode, $kodeUrut));
	}

	#--- Koreksi Lebih HK (133) mengurangi Lebih HK (132) langsung, bukan kolom pengurang
	foreach (pphAmbilSemua("select karyawanid,sum(jumlah) as jumlah from " . $dbname . ".sdm_gaji where periodegaji = '" . $per . "' and kodeorg = '" . $unit . "' and idkomponen = '133' and jumlah > 0 group by karyawanid") as $barKor) {
		if (isset($rupKomp[$barKor['karyawanid']][132], $komp[132])) {
			$potong = min($rupKomp[$barKor['karyawanid']][132], $barKor['jumlah']);
			$rupKomp[$barKor['karyawanid']][132] -= $potong;
		}
	}

	#--- Perjalanan dinas dan penghasilan lain (khusus PT PPP): masuk kolom THR/Bonus/Lain
	$pjdKaryawan = array();
	$lainKaryawan = array();
	if (getindukPT($unit) == 'PPP') {
		foreach (pphPjdPeriode($unit, $per) as $barisPjd) {
			if ($barisPjd['jumlah'] > 0) {
				$pjdKaryawan[$barisPjd['karyawanid']] = (isset($pjdKaryawan[$barisPjd['karyawanid']]) ? $pjdKaryawan[$barisPjd['karyawanid']] : 0) + $barisPjd['jumlah'];
			}
		}
		if (pphTabelLainAda()) {
			$namaLain = pphJenisLain(true);
			foreach (pphLainPeriode($unit, $per) as $kidLain => $perJenisLain) {
				foreach ($perJenisLain as $kodeLain => $jmlLain) {
					if (isset($namaLain[$kodeLain]) && $jmlLain > 0) {
						$lainKaryawan[$kidLain][$kodeLain] = $jmlLain;
					}
				}
			}
		}
	}

	$nmKomponen = makeOption($dbname, 'sdm_ho_component', 'id,name');
	$adaPjd = (count($pjdKaryawan) > 0);
	$kolLain = array();   # kodejenis => nama kolom (hanya yang ada nilainya)
	if (count($lainKaryawan) > 0) {
		$namaLain = pphJenisLain(true);
		foreach ($lainKaryawan as $perJenisLain) {
			foreach ($perJenisLain as $kodeLain => $jmlLain) {
				$kolLain[$kodeLain] = isset($namaLain[$kodeLain]) ? $namaLain[$kodeLain] : $kodeLain;
			}
		}
	}
	$jmlLain = count($kolLain);
	#--- Data karyawan
	$nmjabatan = makeOption($dbname, 'sdm_5jabatan', 'kodejabatan,namajabatan');
	$dtkarid = array();
	$nik = array();
	$nmkar = array();
	$bagian = array();
	$jabatan = array();
	foreach (pphAmbilSemua($sqlKaryawan) as $bar) {
		$id = $bar['karyawanid'];
		$dtkarid[$id] = $id;
		$nik[$id] = $bar['nik'];
		$nmkar[$id] = $bar['namakaryawan'];
		$bagian[$id] = $bar['bagian'];
		$jabatan[$id] = $bar['kodejabatan'];
	}

	$bulan = 'KHL';
	$adaExcel = ($tipe == 'excel' || $tipe == 'pdf');
	$styleUpper = "style='text-transform: uppercase;'";
	$th = function ($teks, $atribut) use ($styleUpper) {
		return "<th align=center " . $styleUpper . " " . $atribut . ">" . $teks . "</th>";
	};
	$thRumus = function ($judul, $hrf, $rumus) use ($styleUpper, $adaExcel) {
		$rumusHtml = $adaExcel ? "<br>" . $rumus : "<span class=rumus>" . $rumus . "</span>";
		return "<th align=center " . $styleUpper . ">(" . $hrf . ") " . $judul . $rumusHtml . "</th>";
	};
	$batasTeks = number_format(PPH_KHL_BATAS);
	$kelasRinci = $adaExcel ? '' : " class='kolRinci'";   # kolom rincian komponen bisa dilipat di layar
	$kelasGabung = $adaExcel ? '' : " class='kolGabung'"; # kolom ringkas per kelompok (tampil saat rincian disembunyikan)

	$stream = '';
	if ($adaExcel) {
		$stream .= "<table class=sortable cellspacing=1 border=1>";
	} else {
		$stream .= "<style>
			#tabelpph21 tbody tr:nth-child(even) td {background-color:#F3F8FC}
			#tabelpph21 tbody tr:hover td {background-color:#FFF7CC}
			#tabelpph21 .rumus {display:block;font-weight:normal;font-size:11px;text-transform:none;color:#FFE699}
			#tabelpph21 td {white-space:nowrap}
				#tabelpph21.sembunyiRinci .kolRinci {display:none}
				#tabelpph21:not(.sembunyiRinci) .kolGabung {display:none}
				#barPph label {display:flex;align-items:center;gap:6px;cursor:pointer;color:#333}
				#barPph input[type=checkbox] {margin:0;width:16px;height:16px;cursor:pointer}
				#ringkasPph {display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;align-items:stretch}
				#ringkasPph .kartu {display:flex;flex-direction:column;justify-content:flex-start;gap:2px;padding:8px 14px;border:1px solid #c9d6e2;border-radius:6px;background:#F7FAFD;box-sizing:border-box}
				#ringkasPph .kartu span {display:block;font-size:11px;color:#555}
				#ringkasPph .kartu b {font-size:16px;line-height:1.3}
				#barPph {display:flex;align-items:center;gap:18px;flex-wrap:wrap;margin:0 2px 10px 2px;padding:8px 14px;background:#fff;border:1px solid #c9d6e2;border-radius:6px;font-size:13px}
				#barPph button {height:30px;margin:0}
				#barPph .cari {margin-left:auto;display:flex;align-items:center;gap:10px}
				#barPph .cari input {width:250px;height:30px;padding:0 10px;border:1px solid #b9c7d6;border-radius:5px;box-sizing:border-box;font-size:13px}
				#barPph #jmlTampil {font-size:12px;color:#555}
		</style>";
		$stream .= "<div style='margin:0 2px 6px 2px;font-size:11px;color:#555'>Cara hitung Harian (KHL): PPh 21 = " . PPH_KHL_TARIF . "% x (total pendapatan - Rp " . $batasTeks . "); tidak ada PPh 21 bila total tidak lebih dari Rp " . $batasTeks . ". Kolom berhuruf dihitung dengan rumus di bawah judulnya.</div>";
		$stream .= "<div class='table-scroll' style='height:900px; overflow-y:auto;'><table id=tabelpph21 class='sortable sembunyiRinci' cellspacing=1 cellpadding=5 style='width:100%;'>";
	}
	$thB = function ($teks, $atribut) use ($styleUpper) {
		return "<th align=center " . $styleUpper . " " . $atribut . ">" . $teks . "</th>";
	};
	$thRumusB = function ($judul, $hrf, $rumus) use ($styleUpper, $adaExcel) {
		$rumusHtml = $adaExcel ? "<br>" . $rumus : "<span class=rumus>" . $rumus . "</span>";
		return "<th align=center " . $styleUpper . " rowspan=2>(" . $hrf . ") " . $judul . $rumusHtml . "</th>";
	};
	$stream .= "<thead><tr class=rowcontent>";
	$stream .= $thB('NO', 'rowspan=2') . $thB(' NIK ', 'rowspan=2') . $thB($_SESSION['lang']['namakaryawan'], 'rowspan=2') . $thB($_SESSION['lang']['bagian'], 'rowspan=2') . $thB($_SESSION['lang']['jabatan'], 'rowspan=2');
	$namaKat = array('TETAP' => 'PENDAPATAN TETAP', 'TIDAK' => 'PENDAPATAN TIDAK TETAP');
	$hrfKat = array('TETAP' => 'A', 'TIDAK' => 'B');
	$rumusKat = array(
		'TETAP' => 'Gaji pokok dan tunjangan tetap (angka tidak berfluktuasi)',
		'TIDAK' => 'Premi, lembur, tunjangan lain, THR, bonus, dana motivasi, tunjangan produksi' . ($adaPjd ? ', Perjalanan Dinas' : '') . ($jmlLain > 0 ? ', ' . implode(', ', $kolLain) : ''),
	);
	foreach (array('TETAP', 'TIDAK') as $kat) {
		if (count($kolGabung[$kat]) > 0) {
			$stream .= $thB($namaKat[$kat], 'colspan=' . count($kolGabung[$kat]) . $kelasGabung);
		}
		if (count($adaKomp[$kat]) > 0) {
			$stream .= $thB($namaKat[$kat] . ' (RINCIAN KOMPONEN)', 'colspan=' . count($adaKomp[$kat]) . $kelasRinci);
		}
		if ($kat == 'TIDAK') {
			if ($adaPjd) {
				$stream .= $thB('PERJALANAN DINAS', 'rowspan=2');
			}
			foreach ($kolLain as $nmLain) {
				$stream .= $thB(strtoupper(htmlspecialchars($nmLain, ENT_QUOTES)), 'rowspan=2 title="Upload Excel (penghasilan lain), masuk pendapatan tidak tetap"');
			}
		}
		$stream .= $thRumusB($namaKat[$kat], $hrfKat[$kat], $rumusKat[$kat]);
	}
	$stream .= $thRumusB('TOTAL PENDAPATAN', 'C', 'A + B');
	$stream .= $thRumusB('KENA PPH 21', 'D', 'C - ' . $batasTeks . ' (minimal 0)');
	$stream .= $thRumusB('PPH 21 BULAN INI', 'E', PPH_KHL_TARIF . '% x D');
	if (!$adaExcel) {
		$stream .= $thB('TERSIMPAN', 'rowspan=2 title="Hasil proses sebelumnya yang tersimpan (komponen PPh 21 di gaji)"');
	}
	$stream .= "</tr><tr class=rowcontent>";
	$thKomp = function ($teks) use ($styleUpper, $kelasRinci) {
		return "<th " . $styleUpper . " align=center" . $kelasRinci . ">" . $teks . "</th>";
	};
	$thGabung = function ($teks) use ($styleUpper, $kelasGabung) {
		return "<th " . $styleUpper . " align=center" . $kelasGabung . ">" . $teks . "</th>";
	};
	foreach (array('TETAP', 'TIDAK') as $katHdr) {
		foreach ($kolGabung[$katHdr] as $kodeKol) {
			$stream .= $thGabung(pphNamaKolom($kodeKol));
		}
		foreach ($adaKomp[$katHdr] as $idKompHdr) {
			$stream .= $thKomp(isset($nmKomponen[$idKompHdr]) ? $nmKomponen[$idKompHdr] : $idKompHdr);
		}
	}
	$stream .= "</tr></thead>";

	$tersimpan = array();
	if (!$adaExcel) {
		foreach (pphAmbilSemua("select karyawanid, sum(jumlah) as jumlah from " . $dbname . ".sdm_gaji where kodeorg='" . $unit . "' and idkomponen='42' and periodegaji='" . $per . "' group by karyawanid") as $bar) {
			$tersimpan[$bar['karyawanid']] = $bar['jumlah'];
		}
	}

	$no = 0;
	$jmlTanpaPendapatan = 0;
	$dataPdf = array();
	$gtTetap = $gtTidak = $gtTotal = $gtKena = $gtPph21 = $gtTersimpan = 0;
	foreach ($dtkarid as $karid) {
		$gabKar = array('TETAP' => array(), 'TIDAK' => array());
		$subKomp = array('TETAP' => 0, 'TIDAK' => 0);
		if (isset($rupKomp[$karid])) {
			foreach ($rupKomp[$karid] as $idKomp => $nilaiKomp) {
				$kk = $komp[$idKomp];
				$gabKar[$kk[0]][$kk[1]] = (isset($gabKar[$kk[0]][$kk[1]]) ? $gabKar[$kk[0]][$kk[1]] : 0) + $nilaiKomp;
				$subKomp[$kk[0]] += $nilaiKomp;
			}
		}
		$pjdKar = isset($pjdKaryawan[$karid]) ? $pjdKaryawan[$karid] : 0;
		$lainSnap = isset($lainKaryawan[$karid]) ? $lainKaryawan[$karid] : array();
		$tetap = $subKomp['TETAP'];
		$tidak = $subKomp['TIDAK'] + $pjdKar + array_sum($lainSnap);
		$total = $tetap + $tidak;
		if ($total <= 0 || ($pphFilterId !== null && !isset($pphFilterId[$karid]))) {
			$jmlTanpaPendapatan++;
			continue;
		}
		$no++;
		$kena = max(0, $total - PPH_KHL_BATAS);
		$pph21 = $kena * PPH_KHL_TARIF / 100;

		$stream .= "<tr class=rowcontent id=row" . $no . ">";
		if (!$adaExcel) {
			$stream .= "<td hidden id=karyawanid" . $no . ">" . $karid . "</td>";
		}
		$stream .= "<td align=center>" . $no . "</td>";
		$stream .= "<td>" . $nik[$karid] . "</td>";
		$stream .= "<td>" . $nmkar[$karid] . "</td>";
		$stream .= "<td align=center>" . $bagian[$karid] . "</td>";
		$stream .= "<td>" . @$nmjabatan[$jabatan[$karid]] . "</td>";
		foreach (array('TETAP', 'TIDAK') as $kat) {
			foreach ($kolGabung[$kat] as $kodeKol) {
				$stream .= "<td align=right" . $kelasGabung . ">" . number_format(isset($gabKar[$kat][$kodeKol]) ? $gabKar[$kat][$kodeKol] : 0) . "</td>";
			}
			foreach ($adaKomp[$kat] as $idKompRow) {
				$stream .= "<td align=right" . $kelasRinci . ">" . number_format(isset($rupKomp[$karid][$idKompRow]) ? $rupKomp[$karid][$idKompRow] : 0) . "</td>";
			}
			if ($kat == 'TIDAK') {
				if ($adaPjd) {
					$stream .= "<td align=right>" . number_format($pjdKar) . "</td>";
				}
				foreach ($kolLain as $kodeLainRow => $nmLainRow) {
					$stream .= "<td align=right>" . number_format(isset($lainSnap[$kodeLainRow]) ? $lainSnap[$kodeLainRow] : 0) . "</td>";
				}
			}
			$stream .= "<td align=right>" . number_format($kat == 'TETAP' ? $tetap : $tidak) . "</td>";
		}
		$stream .= "<td align=right>" . number_format($total) . "</td>";
		$stream .= "<td align=right>" . number_format($kena) . "</td>";
		$stream .= "<td align=right id='pph21x" . $no . "' style='font-weight:bold;background-color:#FFF2CC'>" . number_format($pph21, 0) . "</td>";
		if (!$adaExcel) {
			$nilaiSimpan = isset($tersimpan[$karid]) ? $tersimpan[$karid] : 0;
			$gtTersimpan += $nilaiSimpan;
			$beda = (count($tersimpan) > 0 && round($pph21) != round($nilaiSimpan));
			$stream .= "<td align=right" . ($beda ? " style='background-color:#FCE8E6' title='Beda dengan hasil proses sebelumnya, perlu diproses ulang'" : "") . ">" . ($nilaiSimpan > 0 ? number_format($nilaiSimpan, 0) : '-') . "</td>";
		}
		$stream .= "</tr>";

		$gtTetap += $tetap;
		$gtTidak += $tidak;
		$gtTotal += $total;
		$gtKena += $kena;
		$gtPph21 += round($pph21);
		$pphHasil[$karid] = array(round($pph21), 0);
		$dataPdf[] = array($no, $nik[$karid], $nmkar[$karid], $tetap, $tidak, $total, $kena, round($pph21));
		$pphRincian[$karid] = array(
			'sistem' => 'Harian',
			'metode' => 'KHL',
			'gaji' => $subKomp['TETAP'] + $subKomp['TIDAK'],
			'pjd' => $pjdKar,
			'lain' => $lainSnap,
			'total' => $total,
			'tunjangan' => 0,
			'pengurang' => 0,
			'tarif' => PPH_KHL_TARIF,
			'npwp' => 0,
			'terhutang' => $pph21,
			'jannov' => 0,
			'sistem21' => round($pph21),
			'manual' => 0,
			'lebih' => 0,
			'pph21' => round($pph21),
		);
	}

	$selTotal = function ($nilai) {
		return "<td align=center><b>" . $nilai . "</b></td>";
	};
	$stream .= "<tr class=rowcontent><td align=center colspan=5><b>TOTAL</b></td>";
	foreach (array('TETAP', 'TIDAK') as $kat) {
		if (count($kolGabung[$kat]) > 0) {
			$stream .= "<td align=center colspan=" . count($kolGabung[$kat]) . $kelasGabung . "></td>";
		}
		if (count($adaKomp[$kat]) > 0) {
			$stream .= "<td align=center colspan=" . count($adaKomp[$kat]) . $kelasRinci . "></td>";
		}
		if ($kat == 'TIDAK') {
			$jmlPolos = ($adaPjd ? 1 : 0) + $jmlLain;
			if ($jmlPolos > 0) {
				$stream .= "<td align=center colspan=" . $jmlPolos . "></td>";
			}
		}
		$stream .= $selTotal(number_format($kat == 'TETAP' ? $gtTetap : $gtTidak));
	}
	$stream .= $selTotal(number_format($gtTotal)) . $selTotal(number_format($gtKena)) . $selTotal(number_format($gtPph21));
	if (!$adaExcel) {
		$stream .= $selTotal(number_format($gtTersimpan));
	}
	$stream .= "</tr></table></div>";

	if ($tipe == 'excel') {
		pphKeluarExcel($stream, $unit, $per, $bulan);
		return;
	}
	if ($tipe == 'pdf') {
		pphKeluarPdf($dataPdf, $unit, $per, $bulan);
		return;
	}
	if ($tipe == 'proses') {
		return;
	}

	#--- Tampilan layar: status periode, ringkasan, dan bilah aksi
	$statusPeriode = pphStatusPeriode($per, $unit);
	$kartu = function ($judul, $nilai, $catatan = '') {
		return "<div class=kartu><span>" . $judul . "</span><b>" . $nilai . "</b>" . ($catatan != '' ? "<span>" . $catatan . "</span>" : "") . "</div>";
	};
	$ringkas = "<div id=ringkasPph style='margin:8px 2px 10px 2px'>";
	$ringkas .= $kartu('Karyawan', number_format($no), 'Sistem gaji Harian' . ($jmlTanpaPendapatan > 0 ? ' | ' . number_format($jmlTanpaPendapatan) . ' tanpa PPh 21 tidak ditampilkan' : ''));
	$ringkas .= $kartu('Pendapatan tetap', number_format($gtTetap), 'hanya karyawan yang tampil');
	$ringkas .= $kartu('Pendapatan tidak tetap', number_format($gtTidak), 'premi, lembur, THR, bonus, dana motivasi, tunjangan produksi, perjalanan dinas');
	$ringkas .= $kartu('PPh 21 bulan ini (hitungan)', number_format($gtPph21));
	$sudahProses = (count(array_intersect_key($tersimpan, $dtkarid)) > 0);
	$selisih = round($gtPph21) - round($gtTersimpan);
	$ringkas .= $kartu('PPh 21 tersimpan', $sudahProses ? number_format($gtTersimpan) : 'belum diproses', $sudahProses ? ($selisih == 0 ? 'sama dengan hitungan' : 'selisih ' . number_format($selisih) . ' dari hitungan') : '');
	$ringkas .= "</div>";

	$bar = "<div id=barPph>";
	if ($statusPeriode['boleh']) {
		$bar .= "<button class=mybutton id=btnProsesPph onclick=prosesPph(" . $no . ")>" . $_SESSION['lang']['proses'] . " PPh 21 (" . $no . " karyawan)</button>";
	}
	$bar .= "<label title='Tampilkan kolom rincian pendapatan per komponen'><input type=checkbox id=chkRinci onclick='toggleRinci(this.checked)'> Tampilkan rincian komponen</label>";
	$bar .= "<div class=cari><span id=jmlTampil></span><input type=text id=cariKaryawan placeholder='Cari NIK / nama karyawan' onkeyup='cariKaryawan()'></div>";
	$bar .= "</div>";

	$catatanSistem = array();
	$catatanSistem[] = array('Sistem gaji Harian (KHL)', 'PPh 21 = ' . PPH_KHL_TARIF . '% x (total pendapatan - Rp ' . $batasTeks . '), tanpa TER dan tanpa perhitungan ulang Desember. Tunjangan BPJS dan natura tidak dihitung; potongan (HK, natura, dll) tidak mengurangi.');
	if ($jmlKosong > 0) {
		$catatanSistem[] = array(number_format($jmlKosong) . ' karyawan belum diisi sistem gajinya', 'Karyawan ini tidak ikut Bulanan maupun Harian. Lengkapi sistem gajinya di data karyawan agar ikut diproses.');
	}

	echo pphBannerStatus($statusPeriode, $per, $unit, 'KHL', $catatanSistem) . "@@VALIDASI@@" . $ringkas . $bar . $stream . "####" . $no;
}
