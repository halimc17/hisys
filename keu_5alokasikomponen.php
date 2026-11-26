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
#Setup Alokasi Komponen Gaji per PT (PT = PT pengguna yang login). Dua tab:
#  1. Komponen Upah (Setup Upah, tipe basic): satu akun debet per jabatan untuk semua komponen upah jabatan itu
#  2. Pendapatan Lain (komponen tambahan): jenis biaya + akun debet (opsional) per komponen, semua jabatan atau jabatan tertentu
$pilih = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";

$namaUpah = array();
foreach (fetchData("select id,name from " . $dbname . ".sdm_ho_component where type='basic' and plus=1 order by id") as $bar) {
	$namaUpah[] = "<b>" . htmlspecialchars(strtoupper(trim($bar['name']))) . "</b>";
}
#PPP: komponen catu (nilai dari Setup Catu) ikut aturan jabatan di tab Komponen Upah, bukan di Pendapatan Lain
$idCatu5 = komponenCatuPPP();
$namaCatu = array();
foreach (fetchData("select id,name from " . $dbname . ".sdm_ho_component where id in (" . implode(',', array_map('intval', $idCatu5)) . ") order by id") as $bar) {
	$namaCatu[] = "<b>" . htmlspecialchars(strtoupper(trim($bar['name']))) . "</b>";
}
$optKomponen = $pilih;
foreach (fetchData("select id,name from " . $dbname . ".sdm_ho_component where plus=1 and type<>'basic' order by trim(name), id") as $bar) {
	if (in_array((int)$bar['id'], $idCatu5)) {
		continue;
	}
	$optKomponen .= "<option value='" . $bar['id'] . "'>" . htmlspecialchars(trim($bar['name'])) . " (" . $bar['id'] . ")</option>";
}
$optJabatan = $pilih;
$optJabatanLain = "<option value='0'>Semua jabatan</option>";
foreach (fetchData("select kodejabatan,namajabatan from " . $dbname . ".sdm_5jabatan order by namajabatan") as $bar) {
	$optJabatan .= "<option value='" . $bar['kodejabatan'] . "'>" . htmlspecialchars(strtoupper($bar['namajabatan'])) . "</option>";
	$optJabatanLain .= "<option value='" . $bar['kodejabatan'] . "'>" . htmlspecialchars(strtoupper($bar['namajabatan'])) . "</option>";
}
$optJenis = $pilih;
foreach (jenisBiayaPPP() as $kd => $nm) {
	$optJenis .= "<option value='" . $kd . "'>" . $nm . "</option>";
}
$optAkunWajib = $pilih;
$optAkunOpsi = "<option value=''>Ikut akun bawaan jenis biaya</option>";
foreach (fetchData("select noakun,namaakun from " . $dbname . ".keu_5akun where noakun like '7%' and detail=1 order by noakun") as $bar) {
	$o = "<option value='" . $bar['noakun'] . "'>" . htmlspecialchars($bar['noakun'] . " - " . $bar['namaakun']) . "</option>";
	$optAkunWajib .= $o;
	$optAkunOpsi .= $o;
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
echo "<script>var defaultJenis5 = " . json_encode($defJenisPage) . ";</script>";
echo "<style>
	#tabBar5 {margin:4px 0 6px 0}
	#tabBar5 button {margin-right:6px;padding:6px 18px;font-weight:bold}
	#tabBar5 button.aktif5 {background:#1F4E78;color:#fff}
	.info5 {font-size:12px;line-height:1.5;margin:4px 0 8px 0;max-width:900px}
	#formUpah5 td, #formLain5 td {padding:3px 4px;vertical-align:middle}
	#formUpah5 td.lbl, #formLain5 td.lbl {white-space:nowrap;width:90px}
	#formUpah5 td.sep, #formLain5 td.sep {width:10px;text-align:center}
</style>
<div class=info5>Menentukan ke akun mana biaya gaji dibebankan saat <b>Proses Akhir Bulan &gt; Gaji Karyawan Tidak Langsung</b> (PT <b>" . htmlspecialchars($_SESSION['empl']['kodeorganisasi']) . "</b>). Pilih tab sesuai jenis komponen. Yang tidak diatur di sini memakai akun bawaan.</div>
<div id=tabBar5>
	<button class='mybutton aktif5' id=btnTabUpah5 onclick=tampilTab5('upah')>1. Komponen Upah</button>
	<button class=mybutton id=btnTabLain5 onclick=tampilTab5('lain')>2. Pendapatan Lain</button>
</div>";
CLOSE_BOX();

#================ Tab 1: Komponen Upah ================
echo "<div id=tabUpah5>";
OPEN_BOX();
echo "<div class=info5>Berlaku untuk <b>komponen dari Setup Upah</b>: " . implode(', ', $namaUpah) . (count($namaCatu) > 0 ? ", serta <b>CATU</b> dari Setup Catu: " . implode(', ', $namaCatu) : "") . ". Semua komponen upah satu jabatan dibebankan ke <b>SATU AKUN</b> sebagai satu kesatuan (jenis biaya Gaji). Jabatan yang tidak diatur memakai akun bawaan (<b>CATU</b> tetap Tunjangan Lain).</div>
<fieldset style='width:520px;float:left;margin-right:12px'><legend><b>Atur per Jabatan</b></legend>
	<table id=formUpah5 border=0 cellpadding=3>
		<tr><td class=lbl>Jabatan</td><td class=sep>:</td><td><select id=jabatanUpah5 class=select2 style='width:340px'>" . $optJabatan . "</select></td></tr>
		<tr><td class=lbl>Akun Debet</td><td class=sep>:</td><td><select id=akunUpah5 class=select2 style='width:340px'>" . $optAkunWajib . "</select></td></tr>
		<tr><td class=lbl>Aktif</td><td class=sep>:</td><td><select id=aktifUpah5 class=select2 style='width:100px'><option value='1'>Ya</option><option value='0'>Tidak</option></select></td></tr>
		<tr><td></td><td></td><td>
			<button class=mybutton onclick=simpanUpah5()>" . $_SESSION['lang']['save'] . "</button>
			<button class=mybutton onclick=batalUpah5()>" . $_SESSION['lang']['cancel'] . "</button>
		</td></tr>
	</table>
</fieldset>
<fieldset style='min-width:520px'><legend><b>Aturan Komponen Upah</b></legend>
	<table class=sortable cellspacing=1 cellpadding=5 border=0 style='width:100%'>
		<thead><tr class=rowheader>
			<td align=center style='width:40px'>No</td>
			<td align=center>Jabatan</td>
			<td align=center>Akun Debet</td>
			<td align=center style='width:60px'>Aktif</td>
			<td align=center style='width:60px' colspan=2>" . $_SESSION['lang']['action'] . "</td>
		</tr></thead>
		<tbody id=containUpah5></tbody>
	</table>
</fieldset>";
CLOSE_BOX();
echo "</div>";

#================ Tab 2: Pendapatan Lain ================
echo "<div id=tabLain5 style='display:none'>";
OPEN_BOX();
echo "<div class=info5>Berlaku untuk <b>komponen tambahan dari Pendapatan Lain</b> (Premi Tetap, Uang Makan, THR, dan seterusnya). Pilih komponen, lalu jabatannya (<b>Semua jabatan</b> atau jabatan tertentu) dan jenis biayanya. Aturan untuk jabatan tertentu didahulukan dari aturan semua jabatan; jenis biayanya boleh dikosongkan (ikut aturan semua jabatan) asalkan akunnya diisi. Akun Debet kosong = ikut akun bawaan jenis biaya. No Akun yang diisi per karyawan di input Pendapatan Lain tetap <b>didahulukan</b>.</div>
<fieldset style='width:520px;float:left;margin-right:12px'><legend><b>Atur per Komponen dan Jabatan</b></legend>
	<table id=formLain5 border=0 cellpadding=3>
		<tr><td class=lbl>Komponen</td><td class=sep>:</td><td><select id=komponenLain5 class=select2 style='width:340px'>" . $optKomponen . "</select></td></tr>
		<tr><td class=lbl>Jabatan</td><td class=sep>:</td><td><select id=jabatanLain5 class=select2 style='width:340px'>" . $optJabatanLain . "</select></td></tr>
		<tr><td class=lbl>Jenis Biaya</td><td class=sep>:</td><td><select id=jenisLain5 class=select2 style='width:340px' onchange=tampilDefault5alokasi()>" . $optJenis . "</select></td></tr>
		<tr><td></td><td></td><td><div id=infoDefault style='font-size:11px;color:#333;max-width:380px'></div></td></tr>
		<tr><td class=lbl>Akun Debet</td><td class=sep>:</td><td><select id=akunLain5 class=select2 style='width:340px'>" . $optAkunOpsi . "</select></td></tr>
		<tr><td class=lbl>Aktif</td><td class=sep>:</td><td><select id=aktifLain5 class=select2 style='width:100px'><option value='1'>Ya</option><option value='0'>Tidak</option></select></td></tr>
		<tr><td></td><td></td><td>
			<button class=mybutton onclick=simpanLain5()>" . $_SESSION['lang']['save'] . "</button>
			<button class=mybutton onclick=batalLain5()>" . $_SESSION['lang']['cancel'] . "</button>
		</td></tr>
	</table>
</fieldset>
<fieldset style='min-width:560px'><legend><b>Aturan Pendapatan Lain</b></legend>
	<table class=sortable cellspacing=1 cellpadding=5 border=0 style='width:100%'>
		<thead><tr class=rowheader>
			<td align=center style='width:40px'>No</td>
			<td align=center>Komponen</td>
			<td align=center>Jabatan</td>
			<td align=center>Jenis Biaya</td>
			<td align=center>Akun Debet</td>
			<td align=center style='width:60px'>Aktif</td>
			<td align=center style='width:60px' colspan=2>" . $_SESSION['lang']['action'] . "</td>
		</tr></thead>
		<tbody id=containLain5></tbody>
	</table>
</fieldset>";
CLOSE_BOX();
echo "</div>";
echo "<script>muatSemua5()</script>";

#================ Akun Bawaan (hanya dilihat, dilipat) ================
OPEN_BOX();
$nmKomponenPage = makeOption($dbname, 'sdm_ho_component', 'id,name');
$teksAkunPage = function ($noakun) use ($nmAkunPage) {
	return $noakun == '' ? '(belum diatur)' : $noakun . ' - ' . (isset($nmAkunPage[$noakun]) ? $nmAkunPage[$noakun] : '');
};
$barisBawaan = '';
#0 = komponen upah (satu kesatuan, jenis Gaji); 999999 = komponen lain yang tidak disebut
$daftarBawaan = array('upah', 14, 16, 32, 33, 28, 26, 21, 70, 72, 73, 80, 71, 0);
foreach ($daftarBawaan as $idBw) {
	$jenisBw = ($idBw === 'upah') ? 'GAJI' : jenisBiayaBawaanPPP($idBw == 0 ? 999999 : $idBw);
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
	if ($idBw === 'upah') {
		$namaBw = "<b>Semua komponen upah</b> (" . implode(', ', $namaUpah) . ")";
	} elseif ($idBw == 0) {
		$namaBw = "<i>Komponen Pendapatan Lain lain (termasuk komponen baru)</i>";
	} else {
		$namaBw = htmlspecialchars((string)@$nmKomponenPage[$idBw]);
	}
	$barisBawaan .= "<tr class=rowcontent title=\"" . htmlspecialchars(implode("\n", $bagianBw), ENT_QUOTES) . "\">
		<td>" . $namaBw . "</td>
		<td>" . htmlspecialchars(jenisBiayaPPP()[$jenisBw]) . "</td>
		<td>" . htmlspecialchars($teksAkunPage($pjBw['noakundebet'])) . "</td>
		<td>" . htmlspecialchars($teksAkunPage($pjBw['noakunkredit'])) . "</td>
	</tr>";
}
echo "<details><summary style='cursor:pointer;font-weight:bold'>Lihat akun bawaan (tidak perlu diisi, hanya untuk dilihat)</summary>
	<div style='font-size:11px;margin:4px 0'>Dipakai bila komponen tidak diatur di atas. Arahkan kursor ke baris untuk melihat akun bagian lain (HO, Bengkel, Traksi). Akun dibaca dari Parameter Jurnal bagian Kebun.</div>
	<table class=sortable cellspacing=1 cellpadding=5 border=0>
		<thead><tr class=rowheader>
			<td align=center>Komponen</td>
			<td align=center>Jenis Biaya</td>
			<td align=center>Akun Debet</td>
			<td align=center>Akun Kredit</td>
		</tr></thead>
		<tbody>" . $barisBawaan . "</tbody>
	</table>
</details>";
CLOSE_BOX();
echo close_body();
?>
