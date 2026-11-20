<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
include('master_mainMenu.php');
include_once('lib/zLib.php');
require_once('lib/zSelect2.php');
require_once('lib/zAlokasiGajiPPP.php');
?>
<script>
	$(document).ready(function() {
		$('.select2').select2({
			dropdownAutoWidth: true
		});
	});
</script>
<script language=javascript1.2 src='js/keu_5alokasikomponen.js?v=<?php echo time(); ?>'></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
<?
#Setup Alokasi Komponen Gaji per PT (PT = PT pengguna yang login); satu setup untuk semua kebutuhan alokasi
$optKomponen = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option><option value='0'>Semua komponen (kecuali BPJS) - khusus per jabatan</option>";
foreach (fetchData("select id,name from " . $dbname . ".sdm_ho_component where plus=1 order by trim(name), id") as $bar) {
	$optKomponen .= "<option value='" . $bar['id'] . "'>" . htmlspecialchars(trim($bar['name'])) . " (" . $bar['id'] . ")</option>";
}
$optJabatan = "<option value='0'>Semua jabatan</option>";
foreach (fetchData("select kodejabatan,namajabatan from " . $dbname . ".sdm_5jabatan order by namajabatan") as $bar) {
	$optJabatan .= "<option value='" . $bar['kodejabatan'] . "'>" . htmlspecialchars($bar['namajabatan']) . "</option>";
}
$optJenis = "<option value=''>(ikut aturan semua jabatan)</option>";
foreach (jenisBiayaPPP() as $kd => $nm) {
	$optJenis .= "<option value='" . $kd . "'>" . $nm . "</option>";
}
$optAkun = "<option value=''>Default parameter jurnal</option>";
foreach (fetchData("select noakun,namaakun from " . $dbname . ".keu_5akun where noakun like '7%' and detail=1 order by noakun") as $bar) {
	$optAkun .= "<option value='" . $bar['noakun'] . "'>" . htmlspecialchars($bar['noakun'] . " - " . $bar['namaakun']) . "</option>";
}

#akun default tiap jenis biaya per bagian, dibaca dari parameter jurnal (supaya tidak perlu membuka menu Parameter Jurnal)
$paramJurnalPage = array();
foreach (fetchData("select jurnalid,noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal") as $barPj) {
	$paramJurnalPage[$barPj['jurnalid']] = $barPj;
}
$nmAkunPage = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "noakun like '7%' or noakun like '2%' or noakun like '4%' or noakun like '8%'");
$namaBagianPage = array('KBNB' => 'Kebun', 'GJHO' => 'HO', 'WSG' => 'Bengkel', 'VHCU' => 'Traksi umum', 'VHCG' => 'Traksi');
$defJenisPage = array();
foreach (jenisBiayaPPP() as $kdJenis => $nmJenis) {
	$baris = array();
	foreach ($namaBagianPage as $kdBagian => $nmBagian) {
		$pt = jurnalidBagianPPP($kdBagian);
		$gid = isset($pt[$kdJenis]) ? $pt[$kdJenis] : '';
		$pj = isset($paramJurnalPage[$gid]) ? $paramJurnalPage[$gid] : array('noakundebet' => '', 'noakunkredit' => '');
		$baris[] = $nmBagian . ' (' . $gid . '): Debet ' . ($pj['noakundebet'] != '' ? $pj['noakundebet'] . ' ' . @$nmAkunPage[$pj['noakundebet']] : '-') . ' | Kredit ' . ($pj['noakunkredit'] != '' ? $pj['noakunkredit'] . ' ' . @$nmAkunPage[$pj['noakunkredit']] : '-');
	}
	$defJenisPage[$kdJenis] = $baris;
}

OPEN_BOX('', '<span class=judul>' . getMenu('keu_5alokasikomponen') . '</span>');
echo "<script>var defaultJenis5 = " . json_encode($defJenisPage) . ";</script>";echo "<fieldset style='width:520px;float:left'><legend><b>Form</b></legend>
	<table border=0 cellpadding=3>
		<tr><td>PT</td><td>:</td><td><b>" . htmlspecialchars($_SESSION['empl']['kodeorganisasi']) . "</b></td></tr>
		<tr>
			<td>Komponen</td><td>:</td>
			<td><select id=idkomponen class=select2 style='width:340px'>" . $optKomponen . "</select></td>
		</tr>
		<tr>
			<td>Jabatan</td><td>:</td>
			<td><select id=kodejabatan class=select2 style='width:340px'>" . $optJabatan . "</select></td>
		</tr>
		<tr>
			<td>Jenis Biaya</td><td>:</td>
			<td><select id=jenisbiaya class=select2 style='width:340px' onchange=tampilDefault5alokasi()>" . $optJenis . "</select></td>
		</tr>
		<tr>
			<td></td><td></td>
			<td><div id=infoDefault style='font-size:11px;color:#333;max-width:380px'></div></td>
		</tr>
		<tr>
			<td>Akun Debet</td><td>:</td>
			<td><select id=noakundebet class=select2 style='width:340px'>" . $optAkun . "</select></td>
		</tr>
		<tr>
			<td>Aktif</td><td>:</td>
			<td><select id=aktif class=select2 style='width:100px'><option value='1'>Ya</option><option value='0'>Tidak</option></select></td>
		</tr>
		<tr><td></td><td></td><td>
			<button class=mybutton onclick=simpan5alokasi()>" . $_SESSION['lang']['save'] . "</button>
			<button class=mybutton onclick=batal5alokasi()>" . $_SESSION['lang']['cancel'] . "</button>
		</td></tr>
	</table>
</fieldset>
<fieldset style='width:700px'><legend><b>Cara Kerja</b></legend>
	<div style='font-size:12px;line-height:1.5'>
		Dipakai <b>Proses Akhir Bulan &gt; Gaji Karyawan Tidak Langsung</b> untuk unit PT ini. <b>Isi hanya yang berbeda dari Setup Bawaan</b> (tabel di bawah); komponen yang tidak diisi memakai Setup Bawaan.
		<table class=sortable cellspacing=1 cellpadding=4 border=0 style='margin:6px 0'>
			<thead><tr class=rowheader><td align=center>Komponen</td><td align=center>Jabatan</td><td align=center>Hasil</td></tr></thead>
			<tbody>
				<tr class=rowcontent><td>Komponen tertentu</td><td>Semua jabatan</td><td>Ubah jenis biaya / akun komponen itu untuk semua jabatan</td></tr>
				<tr class=rowcontent><td>Komponen tertentu</td><td>Jabatan tertentu</td><td>Ubah jenis biaya / akun komponen itu khusus jabatan itu</td></tr>
				<tr class=rowcontent><td>Semua komponen</td><td>Jabatan tertentu</td><td>Ubah akun debet seluruh komponen jabatan itu sekaligus (BPJS tidak ikut)</td></tr>
			</tbody>
		</table>
		<b>Isian:</b> <i>Jenis Biaya</i> menentukan kelompok jurnal (kosong = ikut aturan semua jabatan / Setup Bawaan). <i>Akun Debet</i> kosong = akun default Parameter Jurnal; kalau diisi harus akun detail berawalan 7.<br>
		<b>Urutan yang menang:</b> 1) No Akun karyawan di <i>Pendapatan Lain</i> &rarr; 2) komponen + jabatan &rarr; 3) semua komponen + jabatan &rarr; 4) komponen semua jabatan &rarr; 5) Setup Bawaan.<br>
		<b>Catatan:</b> gaji pokok (1, 14) dan premi/lembur (16, 32, 33) karyawan yang punya data absensi/lembur SDM <b>didahulukan</b> mengikuti akun absensi, tidak diubah setup ini. Akun Debet hanya berlaku untuk karyawan kebun dan HO; bengkel dan traksi tetap memakai akun transit.
	</div>
</fieldset>";
CLOSE_BOX();

OPEN_BOX();
echo "<fieldset style='clear:both'><legend><b>Setup Pengecualian</b> <span style='font-weight:normal'>(hanya yang berbeda dari Setup Bawaan)</span></legend>
	<table class=sortable cellspacing=1 cellpadding=5 border=0>
		<thead>
			<tr class=rowheader>
				<td align=center>No</td>
				<td align=center>ID Komponen</td>
				<td align=center>Nama Komponen</td>
				<td align=center>Jabatan</td>
				<td align=center>Jenis Biaya</td>
				<td align=center>Kelompok Jurnal (Kebun)</td>
				<td align=center>Akun Debet</td>
				<td align=center>Akun Kredit</td>
				<td align=center>Aktif</td>
				<td align=center>Dibuat Oleh</td>
				<td align=center>Waktu Dibuat</td>
				<td align=center>Diubah Oleh</td>
				<td align=center>Waktu Diubah</td>
				<td align=center colspan=2>" . $_SESSION['lang']['action'] . "</td>
			</tr>
		</thead>
		<tbody id=container></tbody>
	</table>
</fieldset>";
echo "<script>loadData5alokasi()</script>";
CLOSE_BOX();

#Setup Bawaan (read-only): berlaku untuk semua komponen yang tidak ada di Setup Pengecualian
OPEN_BOX();
$nmKomponenPage = makeOption($dbname, 'sdm_ho_component', 'id,name');
$teksAkunPage = function ($noakun) use ($nmAkunPage) {
	return $noakun == '' ? '(belum diatur)' : $noakun . ' - ' . (isset($nmAkunPage[$noakun]) ? $nmAkunPage[$noakun] : '');
};
$barisBawaan = '';
$daftarBawaan = array(1, 14, 16, 32, 33, 28, 26, 21, 70, 72, 73, 80, 71, 0);
foreach ($daftarBawaan as $idBw) {
	$jenisBw = jenisBiayaBawaanPPP($idBw == 0 ? 999999 : $idBw);
	$ptKebun = jurnalidBagianPPP('KBNB');
	$gidBw = $ptKebun[$jenisBw];
	$pjBw = isset($paramJurnalPage[$gidBw]) ? $paramJurnalPage[$gidBw] : array('noakundebet' => '', 'noakunkredit' => '');
	$bagianBw = array();
	foreach ($namaBagianPage as $kdBagian => $nmBagian) {
		$pt = jurnalidBagianPPP($kdBagian);
		$gid = isset($pt[$jenisBw]) ? $pt[$jenisBw] : '';
		$pj = isset($paramJurnalPage[$gid]) ? $paramJurnalPage[$gid] : array('noakundebet' => '', 'noakunkredit' => '');
		$bagianBw[] = $nmBagian . ' ' . $gid . ': D ' . $teksAkunPage($pj['noakundebet']) . ' / K ' . $teksAkunPage($pj['noakunkredit']);
	}
	$barisBawaan .= "<tr class=rowcontent title=\"" . htmlspecialchars(implode("\n", $bagianBw), ENT_QUOTES) . "\">
		<td align=center>" . ($idBw == 0 ? '-' : $idBw) . "</td>
		<td>" . ($idBw == 0 ? "<i>Semua komponen lain (termasuk komponen baru)</i>" : htmlspecialchars((string)@$nmKomponenPage[$idBw])) . "</td>
		<td>" . htmlspecialchars(jenisBiayaPPP()[$jenisBw]) . "</td>
		<td align=center>" . htmlspecialchars($gidBw) . "</td>
		<td>" . htmlspecialchars($teksAkunPage($pjBw['noakundebet'])) . "</td>
		<td>" . htmlspecialchars($teksAkunPage($pjBw['noakunkredit'])) . "</td>
	</tr>";
}
echo "<fieldset><legend><b>Setup Bawaan</b> <span style='font-weight:normal'>(tidak perlu disetup, hanya untuk dilihat)</span></legend>
	<div style='font-size:11px;margin-bottom:4px'>Berlaku untuk komponen yang tidak ada di Setup Pengecualian di atas. Arahkan kursor ke baris untuk melihat akun default bagian lain (HO, Bengkel, Traksi). Akun dibaca dari Parameter Jurnal.</div>
	<table class=sortable cellspacing=1 cellpadding=5 border=0>
		<thead><tr class=rowheader>
			<td align=center>ID Komponen</td>
			<td align=center>Nama Komponen</td>
			<td align=center>Jenis Biaya</td>
			<td align=center>Kelompok Jurnal (Kebun)</td>
			<td align=center>Akun Debet</td>
			<td align=center>Akun Kredit</td>
		</tr></thead>
		<tbody>" . $barisBawaan . "</tbody>
	</table>
</fieldset>";
CLOSE_BOX();
echo close_body();
?>
