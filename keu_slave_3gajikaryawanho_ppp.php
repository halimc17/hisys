<?php
# CABANG PPP dari keu_slave_3gajikaryawanho.php (Proses Akhir Bulan > 12. Penggajian HO). Dipanggil otomatis oleh js/keu_3posting.js (tujuanProses)
# hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_3gajikaryawanho.php.
#PPP: menu ini tidak dipakai lagi untuk PPP. File asli tidak bisa berjalan: membaca parameter dari $_GET padahal dikirim lewat POST, hanya
# mengizinkan unit Holding berwilayah JAKARTA (PPHO tidak), dan memakai view sdm_gajidetailho_vw yang tidak ada di database. Penggajian HO PPP
# diproses di langkah 1 (Gaji Karyawan Tidak Langsung): unit bertipe Holding/Kanwil memakai kelompok jurnal GJHO lewat Setup Alokasi Komponen Gaji.
require_once('master_validation.php');

exit("Warning: Penggajian HO PPP tidak diproses di menu ini lagi.\nGunakan menu \"1. Gaji Karyawan Tidak Langsung\" dan pilih unit HO (misalnya PPHO).");
