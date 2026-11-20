<?php
# CABANG PPP dari keu_slave_prosesAlokasiGajiAkhirbulanho.php (proses simpan menu 12. Penggajian HO). Dipanggil otomatis oleh js/keu_3posting.js
# (tujuanProses) hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_prosesAlokasiGajiAkhirbulanho.php.
#PPP: menu 12 tidak dipakai lagi untuk PPP (lihat keu_slave_3gajikaryawanho_ppp.php). Proses ini juga ditutup supaya tidak bisa dipanggil langsung:
# file asli memakai counter jurnal dari sesi tanpa unit/periode (bisa menimpa counter banyak unit), menerima jumlah dan komponen dari browser,
# dan tidak memeriksa periode tutup buku. Penggajian HO PPP diproses di langkah 1 (Gaji Karyawan Tidak Langsung).
require_once('master_validation.php');

exit("Warning: Penggajian HO PPP tidak diproses di menu ini lagi.\nGunakan menu \"1. Gaji Karyawan Tidak Langsung\" dan pilih unit HO (misalnya PPHO).");
