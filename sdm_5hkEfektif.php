<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
require_once('lib/zSelect2.php');
?>
<script language=javascript src=js/zTools.js></script>
<script language="javascript" src="js/zSelect2.js?ver=1"></script>
<script language=javascript src='js/sdm_5hkEfektif.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
#HKE (Hari Kerja Efektif) per PT: pembagi upah harian karyawan sistem gaji Bulanan, dan komponen yang ikut dasar upah harian.
#Setup bisa diisi per PT; pemakaian di transaksi hanya untuk PT yang diaktifkan di hkePTAktif() (saat ini PPP). Dipakai getUpahHarian() di lib/nangkoelib.php.
$daftarPT = getOrgDetail(3);

OPEN_BOX('', '<span class=judul>' . strtoupper($_SESSION['lang']['hkefektif']) . '</span>');
if (!is_array($daftarPT) || count($daftarPT) == 0) {
	echo "<div style='padding:8px'>Anda tidak punya akses ke PT mana pun.</div>";
	CLOSE_BOX();
	echo close_body();
	exit;
}

#pilihan PT: PPP bila ada, selain itu PT pertama
$optPT = '';
$ptAwal = isset($daftarPT['PPP']) ? 'PPP' : key($daftarPT);
foreach ($daftarPT as $kd => $nm) {
	$optPT .= "<option value='" . htmlspecialchars($kd) . "'" . ($kd == $ptAwal ? " selected" : "") . ">" . htmlspecialchars($kd . " - " . $nm) . "</option>";
}

#periode: 3 bulan lalu sampai 17 bulan ke depan (YYYY-MM)
$optPeriode = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
for ($i = -3; $i < 18; $i++) {
	$optPeriode .= "<option value='" . date('Y-m', strtotime(date('Y-m-01') . " $i month")) . "'>" . date('Y-m', strtotime(date('Y-m-01') . " $i month")) . "</option>";
}

echo "<style>
	.hkeForm td {padding:3px 5px;vertical-align:middle}
	.hkeForm td.lbl {white-space:nowrap}
	.hkeForm td.lbl {width:110px}
	.hkeAkt td.lbl {width:170px}
	.hkeBadge {display:inline-block;padding:3px 12px;border-radius:12px;font-weight:bold;font-size:12px;text-transform:uppercase;background:#e5e7eb;color:#4b5563}
	.hkeBadge.run {background:#d1fae5;color:#065f46}
	.hkeBadge.soon {background:#fef3c7;color:#92400e}
	table.sortable td {text-transform:uppercase}
	.hkeNote {margin:2px 0 8px 0}
	.hkeTbl {width:100%;max-width:900px}
	.hkeTbl td {padding:6px 12px;vertical-align:middle}
	.hkeWarn {margin-top:6px;padding:6px 10px;max-width:900px;color:#b42318;background:#fdecea;border:1px solid #f1b8b3;border-radius:4px}
	.dsrNote {margin:2px 0 8px 0;max-width:900px}
	.dsrForm td {padding:3px 6px;vertical-align:middle}
	.dsrKomp td {padding:6px 12px;vertical-align:middle}
	.dsrKomp input.kompChk {margin:0;vertical-align:middle;cursor:pointer}
	.dsrKomp label {cursor:pointer}
</style>";

#------------------------- PILIHAN PT (berlaku untuk semua tab) -------------------------
echo "<table class=hkeForm style='margin-bottom:8px'><tr>
	<td class=lbl>PT</td>
	<td><select id=hkePT class=select2 style='width:300px' onchange=\"hkeMuat();\">" . $optPT . "</select></td>
	<td style='padding-left:12px'><span id=hkeStatus class='hkeBadge off'>-</span></td>
</tr></table>";

#------------------------- HKE PER PERIODE -------------------------
$frm[0] = "<div class=hkeNote>HKE (Hari Kerja Efektif) menjadi pembagi upah harian karyawan sistem gaji <b>Bulanan</b> pada PT yang dipilih, menggantikan angka 25. Karyawan Harian tidak berubah.
	HKE baru dipakai di transaksi setelah diaktifkan di tab Pengaktifan, mulai periode yang dipilih.</div>
	<table class=hkeForm>
		<tr><td class=lbl>" . $_SESSION['lang']['periode'] . "</td><td><select id=hkePeriode class=select2 style='width:300px' onchange=\"hkeSaranLoad();\">" . $optPeriode . "</select></td></tr>
		<tr><td class=lbl>" . $_SESSION['lang']['hariminggu'] . "</td><td><input type=text class=myinputtextnumber id=hkeMinggu onblur=\"hkeHitung();\" onkeypress=\"return angka_doang(event);\" style='width:296px' maxlength=1></td></tr>
		<tr><td class=lbl>" . $_SESSION['lang']['harilibur'] . "</td><td><input type=text class=myinputtextnumber id=hkeLibur onblur=\"hkeHitung();\" onkeypress=\"return angka_doang(event);\" style='width:296px' maxlength=2></td></tr>
		<tr><td class=lbl>" . $_SESSION['lang']['hkefektif'] . "</td><td><input type=text class=myinputtextnumber id=hkeHke style='width:296px' disabled></td></tr>
		<tr><td class=lbl>" . $_SESSION['lang']['catatan'] . "</td><td><input type=text class=myinputtext id=hkeCatatan maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:296px'></td></tr>
		<tr><td></td><td><button class=mybutton onclick=hkeSimpan()>" . $_SESSION['lang']['save'] . "</button> <button class=mybutton onclick=hkeBatal()>" . $_SESSION['lang']['cancel'] . "</button></td></tr>
	</table>
	<div id=hkeSaran style='margin-top:6px'></div>
	<table class='sortable hkeTbl' cellspacing=1 border=0 style='margin-top:8px'>
		<thead><tr class=rowheader>
			<td align=center style='width:40px'>No</td><td align=center>" . $_SESSION['lang']['periode'] . "</td><td align=center>" . $_SESSION['lang']['hariminggu'] . "</td>
			<td align=center>" . $_SESSION['lang']['harilibur'] . "</td><td align=center>" . $_SESSION['lang']['hkefektif'] . "</td><td align=center>" . $_SESSION['lang']['catatan'] . "</td>
			<td align=center>Diubah</td><td align=center style='width:80px'>" . $_SESSION['lang']['action'] . "</td>
		</tr></thead>
		<tbody id=hkeContainer></tbody>
	</table>";

#------------------------- DASAR UPAH HARIAN -------------------------
$frm[1] = "<div class=dsrNote>Komponen yang dicentang ikut menjadi dasar upah harian karyawan sistem gaji <b>Bulanan</b> pada PT yang dipilih (jumlah komponen dibagi HKE), berlaku di semua transaksi.
	Tanpa pengaturan, dasar upah hanya Gaji Pokok. Pengaturan tidak bisa diubah untuk periode yang payroll-nya sudah diproses.</div>
	<div class=dsrNote>Berlaku bulan ini (" . date('Y-m') . "): <b><span id=kompBerlaku></span></b></div>
	<table class=dsrForm><tr>
		<td>Berlaku mulai periode</td>
		<td><select id=kompMulai class=select2 style='width:120px'>" . $optPeriode . "</select></td>
	</tr></table>
	<table class='sortable dsrKomp' cellspacing=1 border=0 style='width:660px;margin:6px 0'>
		<thead><tr class=rowheader><td align=center style='width:60px'>Ikut</td><td align=center>Komponen</td><td align=center style='width:190px'>Karyawan Bulanan (periode terakhir)</td></tr></thead>
		<tbody id=kompForm></tbody>
	</table>
	<div class=dsrNote style='margin:4px 0 10px 0'>Klik jumlah karyawan untuk melihat daftarnya.<br>
	<b>Catu:</b> nilainya dari kelompok catu tiap karyawan dikali harga catu per kg. Bila dicentang, catu masuk upah harian gabungan, potongan catu (134) tidak dibuat, dan hari tidak dibayar dipotong lewat upah harian gabungan itu.</div>
	<button class=mybutton onclick=kompSimpan()>" . $_SESSION['lang']['save'] . "</button>
	<button class=mybutton onclick=kompBatal()>" . $_SESSION['lang']['cancel'] . "</button>
	<div style='margin:12px 0 4px 0'><b>" . $_SESSION['lang']['list'] . "</b></div>
	<table class='sortable hkeTbl' cellspacing=1 border=0>
		<thead><tr class=rowheader><td align=center style='width:40px'>No</td><td align=center style='width:130px'>Berlaku mulai</td><td align=center>Komponen dasar upah</td><td align=center style='width:80px'>" . $_SESSION['lang']['action'] . "</td></tr></thead>
		<tbody id=kompContainer></tbody>
	</table>";
#------------------------- PENGAKTIFAN -------------------------
$frm[2] = "<div class=dsrNote>Pengaktifan menentukan mulai kapan HKE dipakai di transaksi (absensi, cuti, finger, dan seterusnya) untuk karyawan Bulanan PT yang dipilih.
	Transaksi pada periode sebelum periode mulai tetap memakai pembagi 25, jadi tidak ada bulan yang bercampur. Syarat: HKE periode mulai sudah diisi di tab HKE.
	Pengaturan tidak bisa diubah bila payroll periode itu atau sesudahnya sudah diproses.</div>
	<table class='hkeForm hkeAkt'>
		<tr><td class=lbl>Pemakaian di transaksi</td><td><select id=hkeAktif class=select2 style='width:300px'><option value='0'>Tidak aktif</option><option value='1'>Aktif</option></select></td></tr>
		<tr><td class=lbl>Berlaku mulai periode</td><td><select id=hkeMulai class=select2 style='width:300px'>" . $optPeriode . "</select></td></tr>
		<tr><td></td><td><button class=mybutton onclick=hkeAktifSimpan()>" . $_SESSION['lang']['save'] . "</button></td></tr>
	</table>";
$hfrm[0] = 'HKE';
$hfrm[1] = 'Dasar Upah Harian';
$hfrm[2] = 'Pengaktifan';
drawTab('HKE', $hfrm, $frm, 170, '100%');
CLOSE_BOX();
echo "<script>hkeMuat();</script>";
echo close_body();
