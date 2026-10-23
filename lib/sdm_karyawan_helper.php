<?php
# Fungsi pembantu khusus halaman Data Karyawan (handler tab keluarga, pendidikan, pengalaman, training, alamat, keanggotaan, upload, reward).
# Dipakai lewat require_once setelah lib/nangkoelib.php (butuh tanggalnormal dan tanggalsystem).
#============ helper handler data karyawan (tab keluarga, pendidikan, pengalaman, training, alamat, keanggotaan) ============
if (!function_exists('sdmId')) {
	# nilai harus angka bulat positif (karyawanid, nomor, kode, id); selain itu 0
	function sdmId($v)
	{
		return preg_match('/^[0-9]{1,10}$/', (string)$v) ? (int)$v : 0;
	}
}
if (!function_exists('sdmH')) {
	# teks untuk ditampilkan di HTML
	function sdmH($v)
	{
		return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE);
	}
}
if (!function_exists('sdmJs')) {
	# nilai untuk parameter berkutip tunggal di atribut onclick yang berkutip ganda
	function sdmJs($v)
	{
		return htmlspecialchars(addslashes((string)$v), ENT_QUOTES | ENT_SUBSTITUTE);
	}
}
if (!function_exists('sdmTgl')) {
	# tanggal Y-m-d dari database menjadi dd-mm-Y; kosong atau 0000-00-00 menjadi teks kosong
	function sdmTgl($v)
	{
		if ($v === null || $v === '' || substr((string)$v, 0, 10) == '0000-00-00') {
			return '';
		}
		return tanggalnormal($v);
	}
}
if (!function_exists('sdmTglSistem')) {
	# dd-mm-Y dari form menjadi Y-m-d untuk database; tidak valid atau kosong menjadi null
	function sdmTglSistem($v)
	{
		$t = tanggalsystem((string)$v);
		if (preg_match('/^[0-9]{8}$/', $t) && checkdate((int)substr($t, 4, 2), (int)substr($t, 6, 2), (int)substr($t, 0, 4))) {
			return substr($t, 0, 4) . '-' . substr($t, 4, 2) . '-' . substr($t, 6, 2);
		}
		return null;
	}
}
if (!function_exists('sdmGalat')) {
	# pesan galat ringkas untuk pengguna; galat SQL asli hanya masuk log server
	function sdmGalat($e, $duplikat = 'Data yang sama sudah ada.')
	{
		error_log('data karyawan: ' . $e->getMessage());
		$kode = isset($e->errorInfo[1]) ? $e->errorInfo[1] : 0;
		if ($kode == 1062) {
			return " Gagal  !: " . $duplikat . "\n";
		}
		if ($kode == 1452) {
			return " Gagal  !: Data tidak lengkap atau karyawan tidak ditemukan.\n";
		}
		return " Gagal  !: Data tidak dapat disimpan.\n";
	}
}
if (!function_exists('sdmUmur')) {
	# umur dari tanggal lahir (Y-m-d): "5 Thn 2 Bln", "3 Bln", atau "10 Hari"; kosong bila tanggal tidak ada atau di masa depan
	function sdmUmur($tgl)
	{
		if ($tgl === null || $tgl === '' || substr((string)$tgl, 0, 10) == '0000-00-00') {
			return '';
		}
		try {
			$lahir = new DateTime(substr($tgl, 0, 10));
		} catch (Exception $e) {
			return '';
		}
		$hariini = new DateTime(date('Y-m-d'));
		if ($lahir > $hariini) {
			return '';
		}
		$d = $lahir->diff($hariini);
		if ($d->y > 0) {
			return $d->y . ' Thn' . ($d->m > 0 ? ' ' . $d->m . ' Bln' : '');
		}
		if ($d->m > 0) {
			return $d->m . ' Bln';
		}
		return $d->days . ' Hari';
	}
}
if (!function_exists('sdmDurasiBulan')) {
	# selisih dua bulan berformat MM-YYYY menjadi "2 Thn 6 Bln"; kosong bila format salah atau keluar lebih awal dari masuk
	function sdmDurasiBulan($masuk, $keluar)
	{
		if (!preg_match('/^(\d{2})-(\d{4})$/', (string)$masuk, $a) || !preg_match('/^(\d{2})-(\d{4})$/', (string)$keluar, $b)) {
			return '';
		}
		$bln = ((int)$b[2] * 12 + (int)$b[1]) - ((int)$a[2] * 12 + (int)$a[1]);
		if ($bln < 0) {
			return '';
		}
		$thn = intdiv($bln, 12);
		$sisa = $bln % 12;
		if ($thn == 0) {
			return $sisa . ' Bln';
		}
		return $thn . ' Thn' . ($sisa > 0 ? ' ' . $sisa . ' Bln' : '');
	}
}
if (!function_exists('sdmUang')) {
	# angka dengan pemisah ribuan titik; desimal hanya bila ada pecahan; kosong bila tidak ada nilai
	function sdmUang($v)
	{
		if ($v === null || $v === '') {
			return '';
		}
		$v = (float)$v;
		return floor($v) == $v ? number_format($v, 0, ',', '.') : number_format($v, 2, ',', '.');
	}
}
if (!function_exists('sdmXml')) {
	# nilai aman untuk isi XML: byte bukan UTF-8 diperbaiki (dianggap ISO-8859-1), karakter & < > " ' di-escape
	function sdmXml($v)
	{
		$v = (string)$v;
		if (!preg_match('//u', $v)) {
			$v = utf8_encode($v);
		}
		return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}
}
