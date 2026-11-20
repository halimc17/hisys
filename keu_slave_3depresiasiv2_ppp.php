<?php
# CABANG PPP dari keu_slave_3depresiasiv2.php (Proses Akhir Bulan > 6. Depresiasi). Dipanggil otomatis oleh js/keu_3posting.js (tujuanProses)
# hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_3depresiasiv2.php. Perubahan khusus PPP tandai "#PPP".
# Aset yang disusutkan dan jumlah jurnalnya sama dengan file asli (dihitung oleh hitungDepresiasiAssetPPP, fungsi yang sama dengan proses simpan).
# Blok lama "double declining" yang sudah dikomentari tidak disalin.
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zPosting.php');
require_once('lib/zAlokasiGajiPPP.php');

$param = $_POST;
#PPP: format unit dan periode divalidasi sebelum dipakai di query
$pesanParam = validasiParamGajiPPP($param);
if ($pesanParam == '' && (!isset($param['kodeorg']) || $param['kodeorg'] === '' || !isset($param['periode']) || $param['periode'] === '')) {
	$pesanParam = 'Warning : Unit dan periode wajib diisi.';
}
if ($pesanParam != '') {
	exit($pesanParam);
}
$kodeorg  = $param['kodeorg'];

$tipeorganisasi = makeOption($dbname, "organisasi", "kodeorganisasi,tipe");
if (!isset($tipeorganisasi[$kodeorg])) {
	exit('Warning : Unit ' . htmlspecialchars($kodeorg) . ' tidak ditemukan.');
}
$tipeLokasiTugas = isset($tipeorganisasi[$_SESSION['empl']['lokasitugas']]) ? $tipeorganisasi[$_SESSION['empl']['lokasitugas']] : '';
if ($tipeLokasiTugas != 'HOLDING' and $tipeLokasiTugas != 'KANWIL') {
	exit('<label hidden>Warning</label> Untuk Melihat List Data Depresiasi Asset, hanya lokasi tugas Holding (HO) atau KANWIL (RO) yang bisa lakukan Proses');
}

$dtAset = hitungDepresiasiAssetPPP($kodeorg, $param['periode']);
$nmAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun like '7%' or noakun like '1%'");

$no = 0;
$tbulan = 0;
$tselisih = 0;
$tnilaijurnal = 0;
$barisTab = "";
foreach ($dtAset as $a) {
	$no++;
	$barisTab .= "<tr class=rowcontent id='row" . $no . "'>
		<td>" . $no . "</td>
		<td id='tipeasset" . $no . "'>" . htmlspecialchars($a['tipeasset']) . "</td>
		<td id='keterangan" . $no . "'>" . htmlspecialchars($a['namatipe']) . "</td>
		<td id='kodeasset" . $no . "'>" . htmlspecialchars($a['kodeasset']) . "</td>
		<td id='namaaset" . $no . "'>" . htmlspecialchars($a['namasset']) . "</td>
		<td>" . htmlspecialchars($a['awal']) . "</td>
		<td align=right>" . $a['jlhbln'] . "</td>
		<td>" . htmlspecialchars($a['akhir']) . "</td>
		<td align=right>" . number_format($a['hargaperolehan'], 2) . "</td>
		<td align=right>" . number_format($a['totalsusut'], 2) . "</td>
		<td align=right>" . number_format($a['selisih'], 2) . "</td>
		<td id='kodejurnal" . $no . "'>" . htmlspecialchars($a['kodejurnal']) . "</td>
		<td align=right>" . number_format($a['bulanan'], 2) . "</td>
		<td align=right>" . number_format($a['selisihjurnal'], 2) . "</td>
		<td align=right id='jumlah" . $no . "'>" . number_format($a['bulanan'] + $a['selisihjurnal'], 2) . "</td>
		<td id='debet" . $no . "'>" . htmlspecialchars($a['debet']) . "</td>
		<td id='kredit" . $no . "'>" . htmlspecialchars($a['kredit']) . "</td>
		<td>" . htmlspecialchars(isset($nmAkun[$a['debet']]) ? $nmAkun[$a['debet']] : '') . "</td>
		<td>" . htmlspecialchars(isset($nmAkun[$a['kredit']]) ? $nmAkun[$a['kredit']] : '') . "</td>
	</tr>";
	$tbulan += $a['bulanan'];
	$tselisih += $a['selisihjurnal'];
	$tnilaijurnal += $a['bulanan'] + $a['selisihjurnal'];
}

if ($no == 0) {
	echo "<table class=sortable cellspacing=1 border=0 id=mytable><tbody>
		<tr class=rowcontent><td align=center>Tidak ada aset yang perlu disusutkan untuk unit " . htmlspecialchars($kodeorg) . " pada periode " . htmlspecialchars($param['periode']) . ".</td></tr>
		</tbody></table>";
	exit;
}

$jenisJurnal = ($tipeorganisasi[$kodeorg] == 'HOLDING' || $tipeorganisasi[$kodeorg] == 'KANWIL') ? 'DPH' : 'DEP';

echo "
	<fieldset style='margin-bottom:8px'><legend><b>Informasi Proses</b></legend>
	<div style='display:flex;flex-wrap:wrap;align-items:stretch;gap:6px;font-size:12px'>
		<div style='flex:1 1 210px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>1. Ambil aset</b><br>Aset aktif unit " . htmlspecialchars($kodeorg) . " yang sudah mulai disusut sampai periode " . htmlspecialchars($param['periode']) . ", masa susutnya belum habis, dan belum dinonaktifkan.</div>
		<div style='align-self:center;font-size:18px'>&rarr;</div>
		<div style='flex:1 1 230px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>2. Hitung penyusutan</b><br>Penyusutan <b>bulanan</b> tiap aset. Pada bulan terakhir masa susut ditambah <b>selisih</b> (harga perolehan dikurangi bulanan &times; jumlah bulan).</div>
		<div style='align-self:center;font-size:18px'>&rarr;</div>
		<div style='flex:1 1 250px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>3. Buat jurnal per aset</b><br>Satu jurnal per aset, kode jurnal <b>" . $jenisJurnal . "</b> + tipe aset. <b>Debet</b> dan <b>Kredit</b> dari Parameter Jurnal. Jurnal dibuat belum diposting.</div>
	</div>
	<div style='font-size:12px;margin-top:6px;line-height:1.5'>
		&bull; Terdapat <b>" . $no . "</b> aset dengan total penyusutan <b>" . number_format($tnilaijurnal, 2) . "</b>.<br>
		&bull; Angka jurnal dan akun dihitung ulang oleh server saat Process, jadi tidak tergantung angka yang tampil di layar.<br>
		<span style='background:#fff3cd;padding:1px 6px;border:1px solid #e0c36a'><b>Perhatian:</b> Process ulang untuk periode yang sama menghapus jurnal penyusutan unit ini pada periode itu lalu membuatnya lagi. Kalau periode akuntansi sudah tutup buku, tidak ada yang dihapus.</span>
	</div>
	</fieldset>
	<style>
		.tombolDepPPP { display:inline-block; padding:7px 22px; margin:0 8px 8px 0; border-radius:6px; font-size:13px; font-weight:bold; cursor:pointer; }
		#btnproses.tombolDepPPP { background:#2e7d32; color:#fff; border:1px solid #1b5e20; }
		#btnproses.tombolDepPPP:hover { background:#388e3c; }
		#btnproses.tombolDepPPP:disabled { background:#9e9e9e; border-color:#757575; color:#eee; cursor:not-allowed; }
		#btnexcel.tombolDepPPP { background:#fff; color:#2e7d32; border:1px solid #2e7d32; }
		#btnexcel.tombolDepPPP:hover { background:#e8f5e9; }
	</style>
	<div>
		<button class='tombolDepPPP' onclick=savedep(" . $no . ") id=btnproses title='Buat jurnal penyusutan untuk semua baris di bawah'>&#9654; Process</button>
		<button class='tombolDepPPP' onclick=exportTableToExcel() id=btnexcel title='Unduh tabel ini ke Excel'>&#11015; Excel</button>
	</div>
	<table class=sortable cellspacing=1 border=0 id=mytable>
	<thead>
	<tr class=rowheader>
		<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
		<th align=center>Asset Type</th>
		<th align=center>" . $_SESSION['lang']['keterangan'] . "</th>
		<th align=center>" . $_SESSION['lang']['kodeasset'] . "</th>
		<th align=center>" . $_SESSION['lang']['nama'] . "</th>
		<th align=center>" . $_SESSION['lang']['awalpenyusutan'] . "</th>
		<th align=center>" . $_SESSION['lang']['jumlahbulan'] . "</th>
		<th align=center>" . $_SESSION['lang']['akhirpenyusutan'] . "</th>
		<th align=center>" . $_SESSION['lang']['hargaperolehan'] . "</th>
		<th align=center>" . $_SESSION['lang']['total'] . "<br>(" . $_SESSION['lang']['bulanan'] . " * " . $_SESSION['lang']['jumlahbulan'] . ")</th>
		<th align=center>" . $_SESSION['lang']['selisih'] . " (Akan Ditambahkan diakhir periode susut)</th>
		<th align=center>Journal Code</th>
		<th align=center>" . $_SESSION['lang']['bulanan'] . "</th>
		<th align=center>" . $_SESSION['lang']['nilai'] . "<br>" . $_SESSION['lang']['selisih'] . "<br>(" . $_SESSION['lang']['bulanakhir'] . ")</th>
		<th align=center>" . $_SESSION['lang']['jumlah'] . " " . $_SESSION['lang']['jurnal'] . " " . htmlspecialchars($param['periode']) . " (Bulanan+selisih jika akhir periode susut)</th>
		<th align=center>" . $_SESSION['lang']['noakundebet'] . "</th>
		<th align=center>" . $_SESSION['lang']['noakunkredit'] . "</th>
		<th align=center>Nama Akun Debet</th>
		<th align=center>Nama Akun Kredit</th>
	</tr>
	</thead>
	<tbody>" . $barisTab . "
	<tr class=rowcontent>
		<td colspan=12>" . $_SESSION['lang']['total'] . "</td>
		<td align=right>" . number_format($tbulan, 2) . "</td>
		<td align=right>" . number_format($tselisih, 2) . "</td>
		<td align=right>" . number_format($tnilaijurnal, 2) . "</td>
		<td colspan=4></td>
	</tr>
	</tbody></table>";
