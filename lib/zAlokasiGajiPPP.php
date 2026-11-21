<?php
# Alokasi komponen gaji untuk cabang PPP (Proses Akhir Bulan > Gaji Karyawan Tidak Langsung).
# Dipakai oleh keu_slave_prosesAlokasiGajiAkhirbulan_ppp.php (proses) dan keu_slave_3gajikaryawan_ppp.php (informasi di list).
# Urutan penentuan akun debet (paling spesifik dulu):
#   1. No Akun per karyawan di Pendapatan Lain (sdm_pendapatanlaindt.noakun, hanya yang sudah diposting)
#   2. Akun debet per komponen dari Setup Alokasi Komponen Gaji (keu_5alokasikomponen.noakundebet)
#   3. Akun default dari parameter jurnal kelompok jurnalnya (keu_5parameterjurnal)
# Kelompok jurnal (jurnalid) ditentukan dari bagian karyawan (awalan, ditentukan kode) dan jenis biaya komponen
# (dari Setup, atau pemetaan bawaan kalau komponen belum ada di Setup).

#jenis biaya yang bisa dipilih di Setup
function jenisBiayaPPP()
{
	return array(
		'GAJI'    => 'Gaji',
		'PREMI'   => 'Premi / Lembur',
		'TUNJ'    => 'Tunjangan Lain',
		'THR'     => 'THR',
		'BONUS'   => 'Bonus',
		'OBAT'    => 'Pengobatan',
		'BPJSTK'  => 'BPJS Ketenagakerjaan',
		'BPJSKES' => 'BPJS Kesehatan'
	);
}

#jenis biaya -> jurnalid untuk tiap bagian; bagian baru cukup ditambah di sini.
#Sama dengan pemetaan asli: di Bengkel dan Traksi semua BPJS (termasuk Kesehatan) dikumpulkan di kelompok BPJS yang sama.
function jurnalidBagianPPP($bagian)
{
	$peta = array(
		'KBNB' => array('GAJI' => 'KBNB0', 'PREMI' => 'KBNB1', 'TUNJ' => 'KBNB2', 'THR' => 'KBNB3', 'BONUS' => 'KBNB4', 'OBAT' => 'KBNB5', 'BPJSTK' => 'KBNB6', 'BPJSKES' => 'KBNB7'),
		'WSG'  => array('GAJI' => 'WSG0', 'PREMI' => 'WSG1', 'TUNJ' => 'WSG2', 'THR' => 'WSG3', 'BONUS' => 'WSG4', 'OBAT' => 'WSG5', 'BPJSTK' => 'WSG6', 'BPJSKES' => 'WSG6'),
		'VHCG' => array('GAJI' => 'VHCG0', 'PREMI' => 'VHCG1', 'TUNJ' => 'VHCG2', 'THR' => 'VHCG3', 'BONUS' => 'VHCG4', 'OBAT' => 'VHCG5', 'BPJSTK' => 'VHCG6', 'BPJSKES' => 'VHCG6'),
		'VHCU' => array('GAJI' => 'VHCU0', 'PREMI' => 'VHCU1', 'TUNJ' => 'VHCU2', 'THR' => 'VHCU3', 'BONUS' => 'VHCU4', 'OBAT' => 'VHCU5', 'BPJSTK' => 'VHCU6', 'BPJSKES' => 'VHCU6'),
		'GJHO' => array('GAJI' => 'GJHO1', 'PREMI' => 'GJHO2', 'TUNJ' => 'GJHO3', 'THR' => 'GJHO4', 'BONUS' => 'GJHO5', 'OBAT' => 'GJHO6', 'BPJSTK' => 'GJHO7', 'BPJSKES' => 'KBNB7')
	);
	return isset($peta[$bagian]) ? $peta[$bagian] : array();
}

#pemetaan bawaan (dulu hardcode): dipakai untuk semua komponen yang TIDAK ada di Setup. Setup hanya diisi untuk yang berbeda dari bawaan.
function jenisBiayaBawaanPPP($komponen)
{
	$komponen = (int)$komponen;
	if ($komponen == 1 || $komponen == 14) return 'GAJI';
	if ($komponen == 16 || $komponen == 32 || $komponen == 33) return 'PREMI';
	if ($komponen == 28) return 'THR';
	if ($komponen == 26) return 'BONUS';
	if ($komponen == 21) return 'OBAT';
	if (in_array($komponen, array(70, 72, 73, 80))) return 'BPJSTK';
	if ($komponen == 71) return 'BPJSKES';
	return 'TUNJ';
}

#isi Setup untuk satu PT: "kodejabatan|idkomponen" => array(jenisbiaya, noakundebet); kodejabatan 0 = semua jabatan.
#kosong kalau tabel belum ada
function setupAlokasiPPP($kodept)
{
	global $dbname, $owlPDO;
	static $cache = array();
	if (isset($cache[$kodept])) {
		return $cache[$kodept];
	}
	$cache[$kodept] = array();
	try {
		$stm = $owlPDO->prepare("select kodejabatan,idkomponen,jenisbiaya,noakundebet from " . $dbname . ".keu_5alokasikomponen where kodept=? and aktif=1");
		if ($stm && $stm->execute(array($kodept))) {
			foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $r) {
				$cache[$kodept][(int)$r['kodejabatan'] . '|' . (int)$r['idkomponen']] = array($r['jenisbiaya'], trim($r['noakundebet']));
			}
		}
	} catch (PDOException $e) {
		$cache[$kodept] = array();
	}
	return $cache[$kodept];
}

#cache per request (satu proses PHP): dipakai bersama oleh list Gaji (banyak baris) dan proses (satu baris)
function &cacheJabatanPPP()
{
	static $c = array();
	return $c;
}
function &cacheAkunPlPPP()
{
	static $c = array();
	return $c;
}

#jabatan karyawan pada periode itu: histori periode (versi B) kalau ada, kalau tidak data master; 0 kalau tidak ketemu
function jabatanKaryawanPPP($karyawanid, $kodeorg, $periode)
{
	global $dbname, $owlPDO;
	$cache = &cacheJabatanPPP();
	$kunci = (int)$karyawanid . '|' . $kodeorg . '|' . $periode;
	if (isset($cache[$kunci])) {
		return $cache[$kunci];
	}
	$jabatan = 0;
	$stm = $owlPDO->prepare("select kodejabatan from " . $dbname . ".datakaryawan_hist where karyawanid=? and periodegaji=? and version_type='B' and lokasitugas=? limit 1");
	if ($stm && $stm->execute(array($karyawanid, $periode, $kodeorg))) {
		$jabatan = (int)$stm->fetchColumn();
	}
	if ($jabatan == 0) {
		$stm = $owlPDO->prepare("select kodejabatan from " . $dbname . ".datakaryawan where karyawanid=? limit 1");
		if ($stm && $stm->execute(array($karyawanid))) {
			$jabatan = (int)$stm->fetchColumn();
		}
	}
	$cache[$kunci] = $jabatan;
	return $jabatan;
}

#akun harus akun detail berawalan 7 yang ada di master akun; true kalau sah (tidak melempar error). Hasil disimpan per akun.
function akunSah7PPP($noakun)
{
	global $dbname, $owlPDO;
	static $cache = array();
	if (isset($cache[$noakun])) {
		return $cache[$noakun];
	}
	$sah = false;
	if (substr($noakun, 0, 1) == '7') {
		$stm = $owlPDO->prepare("select noakun from " . $dbname . ".keu_5akun where noakun=? and detail=1");
		$sah = ($stm && $stm->execute(array($noakun)) && $stm->fetchColumn()) ? true : false;
	}
	$cache[$noakun] = $sah;
	return $sah;
}

#muat No Akun Pendapatan Lain (sudah diposting) satu unit + periode dalam SATU query; dipanggil otomatis, tidak perlu dipanggil manual.
#Kalau kolom noakun belum ada (SQL migrasi belum dijalankan) hasilnya kosong dan proses tetap jalan.
function muatAkunPendapatanLainPPP($kodeorg, $periode)
{
	global $dbname, $owlPDO;
	$cache = &cacheAkunPlPPP();
	$kunci = $kodeorg . '|' . $periode;
	if (isset($cache[$kunci])) {
		return;
	}
	$cache[$kunci] = array();
	try {
		$stm = $owlPDO->prepare("select karyawanid,idkomponen,noakun from " . $dbname . ".sdm_pendapatanlaindt
			where kodeorg=? and periodegaji=? and posting=1 and noakun<>''");
		if ($stm && $stm->execute(array($kodeorg, $periode))) {
			foreach ($stm->fetchAll(PDO::FETCH_NUM) as $r) {
				$cache[$kunci][(int)$r[0] . '|' . (int)$r[1]] = trim((string)$r[2]);
			}
		}
	} catch (PDOException $e) {
		#kolom noakun belum ada: anggap tidak ada No Akun
	}
}

#No Akun dari Pendapatan Lain untuk karyawan + komponen + periode; '' kalau tidak ada
function akunPendapatanLainPPP($kodeorg, $periode, $karyawanid, $komponen)
{
	muatAkunPendapatanLainPPP($kodeorg, $periode);
	$cache = &cacheAkunPlPPP();
	$kunci = (int)$karyawanid . '|' . (int)$komponen;
	return isset($cache[$kodeorg . '|' . $periode][$kunci]) ? $cache[$kodeorg . '|' . $periode][$kunci] : '';
}

#untuk list Gaji (banyak baris sekaligus): muat jabatan semua karyawan unit dalam 2 query (histori periode lalu master)
#dan No Akun Pendapatan Lain dalam 1 query, supaya tiap baris tidak query lagi. Jabatan dimuat hanya kalau Setup ada isinya.
function muatCachePPP($kodept, $kodeorg, $periode)
{
	global $dbname, $owlPDO;
	muatAkunPendapatanLainPPP($kodeorg, $periode);
	if (count(setupAlokasiPPP($kodept)) == 0) {
		return;
	}
	$cache = &cacheJabatanPPP();
	$stm = $owlPDO->prepare("select karyawanid,kodejabatan from " . $dbname . ".datakaryawan_hist where periodegaji=? and version_type='B' and lokasitugas=?");
	if ($stm && $stm->execute(array($periode, $kodeorg))) {
		foreach ($stm->fetchAll(PDO::FETCH_NUM) as $r) {
			if ((int)$r[1] > 0) {
				$cache[(int)$r[0] . '|' . $kodeorg . '|' . $periode] = (int)$r[1];
			}
		}
	}
	$stm = $owlPDO->prepare("select karyawanid,kodejabatan from " . $dbname . ".datakaryawan where lokasitugas=?");
	if ($stm && $stm->execute(array($kodeorg))) {
		foreach ($stm->fetchAll(PDO::FETCH_NUM) as $r) {
			$kunci = (int)$r[0] . '|' . $kodeorg . '|' . $periode;
			if (!isset($cache[$kunci]) && (int)$r[1] > 0) {
				$cache[$kunci] = (int)$r[1];
			}
		}
	}
}

#hasil alokasi satu komponen karyawan:
#  jurnalid  = kelompok jurnal
#  akundebet = akun debet pengganti ('' = pakai akun default parameter jurnal)
#  sumber    = asal akun debet (untuk informasi)
#  jenis     = jenis biaya
#Urutan (paling spesifik menang): No Akun Pendapatan Lain karyawan > Setup jabatan+komponen > Setup jabatan+semua komponen (bukan BPJS) > Setup semua jabatan+komponen > default.
#Jenis biaya: Setup jabatan+komponen (kalau jenisnya diisi) > Setup semua jabatan > pemetaan bawaan.
#$bolehAkun = false untuk bagian yang debetnya akun transit (bengkel/traksi): hanya kelompok jurnal yang ikut Setup
function alokasiKomponenPPP($bagian, $kodept, $kodeorg, $periode, $karyawanid, $komponen, $bolehAkun)
{
	try {
		return alokasiKomponenPPPInti($bagian, $kodept, $kodeorg, $periode, $karyawanid, $komponen, $bolehAkun);
	} catch (Exception $e) {
		#pengaman: setup tidak terbaca/bermasalah -> pemetaan bawaan di kode tetap dipakai supaya proses jalan
		$peta = jurnalidBagianPPP($bagian);
		$jenis = jenisBiayaBawaanPPP($komponen);
		if (!isset($peta[$jenis])) {
			$jenis = 'TUNJ';
		}
		return array('jurnalid' => $peta[$jenis], 'akundebet' => '', 'sumber' => 'Default parameter jurnal [PERHATIAN: setup tidak terbaca: ' . $e->getMessage() . ']', 'jenis' => $jenis);
	}
}

function alokasiKomponenPPPInti($bagian, $kodept, $kodeorg, $periode, $karyawanid, $komponen, $bolehAkun)
{
	$setup = setupAlokasiPPP($kodept);
	$komponen = (int)$komponen;
	$jabatan = empty($setup) ? 0 : jabatanKaryawanPPP($karyawanid, $kodeorg, $periode);
	$aturanSemua = isset($setup['0|' . $komponen]) ? $setup['0|' . $komponen] : null;
	$aturanJabatan = ($jabatan > 0 && isset($setup[$jabatan . '|' . $komponen])) ? $setup[$jabatan . '|' . $komponen] : null;
	#aturan "semua komponen" untuk jabatan itu (idkomponen 0): hanya akun debet, tidak berlaku untuk komponen BPJS
	$aturanJabatanSemua = ($jabatan > 0 && !in_array($komponen, array(70, 71, 72, 73, 80)) && isset($setup[$jabatan . '|0'])) ? $setup[$jabatan . '|0'] : null;

	$jenis = jenisBiayaBawaanPPP($komponen);
	if ($aturanSemua !== null && $aturanSemua[0] != '') {
		$jenis = $aturanSemua[0];
	}
	if ($aturanJabatan !== null && $aturanJabatan[0] != '') {
		$jenis = $aturanJabatan[0];
	}
	$peta = jurnalidBagianPPP($bagian);
	if (!isset($peta[$jenis])) {
		$jenis = 'TUNJ';
	}
	$hasil = array('jurnalid' => $peta[$jenis], 'akundebet' => '', 'sumber' => 'Default parameter jurnal', 'jenis' => $jenis);
	if ($aturanSemua !== null || $aturanJabatan !== null) {
		$hasil['sumber'] = 'Setup komponen (akun default jurnal)';
	}
	if ($bolehAkun) {
		#kandidat akun debet, urut paling spesifik dulu; yang tidak sah dilewati (proses tetap jalan dengan akun bawaan) dan dicatat di sumber
		$kandidat = array(
			array(akunPendapatanLainPPP($kodeorg, $periode, $karyawanid, $komponen), 'No Akun Pendapatan Lain', 'No Akun Pendapatan Lain'),
			array(($aturanJabatan !== null ? $aturanJabatan[1] : ''), 'Setup jabatan + komponen', 'Setup jabatan ' . $jabatan . ' komponen ' . $komponen),
			array(($aturanJabatanSemua !== null ? $aturanJabatanSemua[1] : ''), 'Setup jabatan (semua komponen)', 'Setup jabatan ' . $jabatan . ' semua komponen'),
			array(($aturanSemua !== null ? $aturanSemua[1] : ''), 'Setup komponen', 'Setup komponen ' . $komponen)
		);
		$catatan = array();
		foreach ($kandidat as $k) {
			if ($k[0] == '') {
				continue;
			}
			if (akunSah7PPP($k[0])) {
				$hasil['akundebet'] = $k[0];
				$hasil['sumber'] = $k[1];
				break;
			}
			$catatan[] = $k[2] . ': akun ' . $k[0] . ' tidak sah, dilewati';
		}
		if (count($catatan) > 0) {
			$hasil['sumber'] .= ' [PERHATIAN: ' . implode('; ', $catatan) . ']';
		}
	}
	return $hasil;
}

#validasi format parameter yang masuk ke query pada proses dan list Gaji cabang PPP (hanya identitas, bukan teks bebas);
#kembalian: pesan peringatan, atau '' kalau semua parameter yang ada berformat sah
function validasiParamGajiPPP($p)
{
	$aturan = array(
		'kodeorg'    => '/^[A-Za-z0-9]{1,10}$/',
		'periode'    => '/^[0-9]{4}-[0-9]{2}$/',
		'karyawanid' => '/^[0-9]{1,10}$/',
		'komponen'   => '/^[0-9]{1,10}$/',
		'row'        => '/^[0-9]{1,6}$/',
		'jumlah'     => '/^-?[0-9]+(\.[0-9]+)?([eE][-+]?[0-9]+)?$/'
	);
	foreach ($aturan as $nama => $pola) {
		if (!isset($p[$nama]) || $p[$nama] === '') {
			continue;
		}
		if (!is_string($p[$nama]) || !preg_match($pola, $p[$nama])) {
			return "Warning : Parameter " . $nama . " tidak valid.";
		}
	}
	return '';
}

#validasi parameter proses langkah 3 (Traksi/Bengkel): komponen berupa daftar "1###57###..." dan tiap komponen punya jumlah{id}
function validasiParamGajiTrkPPP($p)
{
	$q = $p;
	unset($q['komponen']);
	$pesan = validasiParamGajiPPP($q);
	if ($pesan != '') {
		return $pesan;
	}
	foreach (array('kodeorg', 'periode', 'karyawanid', 'komponen', 'row') as $wajib) {
		if (!isset($p[$wajib]) || !is_string($p[$wajib]) || $p[$wajib] === '') {
			return "Warning : Parameter " . $wajib . " wajib diisi.";
		}
	}
	$aturan = array(
		'mesin'          => '/^[A-Za-z0-9 ._\/-]{0,40}$/',
		'subbagian'      => '/^[A-Za-z0-9 ._\/-]{0,40}$/',
		'tipeorganisasi' => '/^[A-Za-z ]{0,20}$/'
	);
	foreach ($aturan as $nama => $pola) {
		if (isset($p[$nama]) && (!is_string($p[$nama]) || !preg_match($pola, $p[$nama]))) {
			return "Warning : Parameter " . $nama . " tidak valid.";
		}
	}
	if (!preg_match('/^[0-9]{1,10}(###[0-9]{1,10})*$/', $p['komponen'])) {
		return "Warning : Parameter komponen tidak valid.";
	}
	foreach (explode('###', $p['komponen']) as $idk) {
		$kunci = 'jumlah' . $idk;
		if (!isset($p[$kunci]) || !is_string($p[$kunci]) || !preg_match('/^-?[0-9]+(\.[0-9]+)?([eE][-+]?[0-9]+)?$/', $p[$kunci])) {
			return "Warning : Jumlah komponen " . $idk . " tidak valid.";
		}
	}
	return '';
}

#validasi parameter langkah 4 (Gaji Karyawan Belum Teralokasi): method list/save/delete
function validasiParamGajiBlmPPP($p)
{
	$method = isset($p['method']) ? $p['method'] : '';
	if (!in_array($method, array('', 'list', 'save', 'delete'), true)) {
		return "Warning : Parameter method tidak valid.";
	}
	$pesan = validasiParamGajiPPP($p);
	if ($pesan != '') {
		return $pesan;
	}
	foreach (array('kodeorg', 'periode') as $wajib) {
		if (!isset($p[$wajib]) || !is_string($p[$wajib]) || $p[$wajib] === '') {
			return "Warning : Parameter " . $wajib . " wajib diisi.";
		}
	}
	if ($method == 'save' && (!isset($p['karyawanid']) || !is_string($p['karyawanid']) || $p['karyawanid'] === '')) {
		return "Warning : Parameter karyawanid wajib diisi.";
	}
	if (isset($p['subbagian']) && (!is_string($p['subbagian']) || !preg_match('/^[A-Za-z0-9 ._\/-]{0,40}$/', $p['subbagian']))) {
		return "Warning : Parameter subbagian tidak valid.";
	}
	foreach (array('gajisisa', 'gajisisabpjs', 'gajisisabpjskes') as $nama) {
		if (isset($p[$nama]) && $p[$nama] !== '' && (!is_string($p[$nama]) || !preg_match('/^-?[0-9]+(\.[0-9]+)?([eE][-+]?[0-9]+)?$/', $p[$nama]))) {
			return "Warning : Parameter " . $nama . " tidak valid.";
		}
	}
	return '';
}

#Sisa gaji yang belum teralokasi per karyawan (gaji plus dikurangi jurnal gaji yang sudah terbentuk selain ALK_GAJI_LBR).
#Dipakai bersama oleh list dan proses simpan supaya angka jurnal dihitung server, bukan dipercaya dari browser.
#$karyawanid kosong = semua karyawan unit itu. Hasil: karyawanid => array(gaji, gajibpjs, gajibpjskes, sudah, sudahbpjs, sudahkes, sisa, sisabpjs, sisakes)
#kehadiran wakar/mandor (vhc_runhk posisi 3 dan 4) di Pekerjaan Traksi pada satu periode, untuk dibebankan ke kendaraan oleh Proses Akhir Bulan.
#Hasil: satu baris per transaksi (tanggal, kodevhc, premi) dengan bobotgaji = gaji pokok / 25, dibagi rata bila pada hari itu ada lebih dari satu kendaraan.
function kehadiranWakarMandorPPP($kodeorg, $periode, $karyawanid)
{
	global $dbname;
	$kodeorg = addslashes($kodeorg);
	$periode = addslashes($periode);
	$karyawanid = addslashes($karyawanid);
	$rows = fetchData("select h.tanggal,h.kodevhc,k.premi from " . $dbname . ".vhc_runhk k inner join " . $dbname . ".vhc_runht h on h.notransaksi=k.notransaksi
		where k.idkaryawan='" . $karyawanid . "' and k.posisi in (3,4) and h.kodeorg='" . $kodeorg . "' and h.tanggal like '" . $periode . "%' and h.kodevhc<>''
		order by h.tanggal,h.kodevhc");
	if (count($rows) == 0) {
		return array();
	}
	$gp = fetchData("select jumlah from " . $dbname . ".sdm_5gajipokok where karyawanid='" . $karyawanid . "' and tahun='" . $periode . "' and idkomponen='1'");
	$harian = (count($gp) > 0 && $gp[0]['jumlah'] > 0) ? $gp[0]['jumlah'] / 25 : 0;
	$perHari = array();
	foreach ($rows as $r) {
		$perHari[$r['tanggal']] = (isset($perHari[$r['tanggal']]) ? $perHari[$r['tanggal']] : 0) + 1;
	}
	foreach ($rows as $i => $r) {
		$rows[$i]['bobotgaji'] = $harian / $perHari[$r['tanggal']];
	}
	return $rows;
}

function hitungSisaGajiBlmAlokasiPPP($kodeorg, $periode, $karyawanid = '')
{
	global $dbname;
	$wKar = ($karyawanid !== '') ? " and karyawanid='" . addslashes($karyawanid) . "'" : '';
	$wNik = ($karyawanid !== '') ? " and nik='" . addslashes($karyawanid) . "'" : '';
	$kodeorg = addslashes($kodeorg);
	$periode = addslashes($periode);

	$arrbpjsplus = array();
	foreach (fetchdata("select * from " . $dbname . ".sdm_5bpjs") as $bar) {
		$arrbpjsplus[$bar['jenisbpjsplus']] = $bar['jenisbpjsplus'];
	}

	$gaji = $gajibpjs = $gajibpjskes = $arrkaryawanid = array();
	foreach (fetchdata("select karyawanid,idkomponen,jumlah from " . $dbname . ".sdm_gajidetail_vw where
			kodeorg='" . $kodeorg . "' and plus=1 and periodegaji='" . $periode . "'" . $wKar) as $bar) {
		$arrkaryawanid[$bar['karyawanid']] = $bar['karyawanid'];
		if (in_array($bar['idkomponen'], $arrbpjsplus)) {
			if ($bar['idkomponen'] == 71) {
				$gajibpjskes[$bar['karyawanid']] = (isset($gajibpjskes[$bar['karyawanid']]) ? $gajibpjskes[$bar['karyawanid']] : 0) + $bar['jumlah'];
			} else {
				$gajibpjs[$bar['karyawanid']] = (isset($gajibpjs[$bar['karyawanid']]) ? $gajibpjs[$bar['karyawanid']] : 0) + $bar['jumlah'];
			}
		} else {
			$gaji[$bar['karyawanid']] = (isset($gaji[$bar['karyawanid']]) ? $gaji[$bar['karyawanid']] : 0) + $bar['jumlah'];
		}
	}

	$rentang = sqlRentangPeriode($periode);
	$sudah = $sudahbpjs = $sudahkes = array();
	#sudah dialokasi gaji (kodejurnal KK,KM,BK,BM,POT tidak dihitung karena kontanan/pembayaran kekurangan)
	foreach (fetchdata("select nik,jumlah from " . $dbname . ".keu_jurnaldt_vw where
			kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and " . $rentang . " and noakun='2130101' and noreferensi!='ALK_GAJI_LBR' and keterangan not like '%denda%'
			and kodejurnal not in ('KK','KM','BK','BM','POT') and (noreferensi not like '%/KK/%' and noreferensi not like '%/BK/%')" . $wNik . "
			order by nojurnal, nourut") as $bar) {
		$sudah[$bar['nik']] = (isset($sudah[$bar['nik']]) ? $sudah[$bar['nik']] : 0) + $bar['jumlah'];
	}
	foreach (fetchdata("select nik,jumlah from " . $dbname . ".keu_jurnaldt_vw where
			kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and " . $rentang . " and noakun='2130102'
			and kodejurnal!='POT' and noreferensi!='ALK_GAJI_LBR'" . $wNik . " order by nojurnal, nourut") as $bar) {
		$sudahbpjs[$bar['nik']] = (isset($sudahbpjs[$bar['nik']]) ? $sudahbpjs[$bar['nik']] : 0) + $bar['jumlah'];
	}
	foreach (fetchdata("select nik,jumlah from " . $dbname . ".keu_jurnaldt_vw where
			kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and " . $rentang . " and noakun='2130104'
			and kodejurnal!='POT' and noreferensi!='ALK_GAJI_LBR'" . $wNik . " order by nojurnal, nourut") as $bar) {
		$sudahkes[$bar['nik']] = (isset($sudahkes[$bar['nik']]) ? $sudahkes[$bar['nik']] : 0) + $bar['jumlah'];
	}
	#khusus BM TBS
	foreach (fetchdata("select nik,jumlah from " . $dbname . ".keu_jurnaldt_vw where
			kodeorg='" . $kodeorg . "' and periode='" . $periode . "' and " . $rentang . " and kodejurnal='BM01' and nik!='' and jumlah>0
			and kodejurnal!='POT' and noreferensi!='ALK_GAJI_LBR'" . $wNik . " order by nojurnal, nourut") as $bar) {
		$sudah[$bar['nik']] = (isset($sudah[$bar['nik']]) ? $sudah[$bar['nik']] : 0) + ($bar['jumlah'] * -1);
	}

	$hasil = array();
	foreach ($arrkaryawanid as $id) {
		$g = isset($gaji[$id]) ? $gaji[$id] : 0;
		$gb = isset($gajibpjs[$id]) ? $gajibpjs[$id] : 0;
		$gk = isset($gajibpjskes[$id]) ? $gajibpjskes[$id] : 0;
		$sd = isset($sudah[$id]) ? $sudah[$id] : 0;
		$sb = isset($sudahbpjs[$id]) ? $sudahbpjs[$id] : 0;
		$sk = isset($sudahkes[$id]) ? $sudahkes[$id] : 0;
		$hasil[$id] = array('gaji' => $g, 'gajibpjs' => $gb, 'gajibpjskes' => $gk, 'sudah' => $sd, 'sudahbpjs' => $sb, 'sudahkes' => $sk,
			'sisa' => round($g + $sd, 2), 'sisabpjs' => round($gb + $sb, 2), 'sisakes' => round($gk + $sk, 2)); #dibulatkan 2 desimal: sisa selisih hitung desimal (mis. 0,00000001) dianggap nol
	}
	return $hasil;
}

#validasi parameter proses Depresiasi (langkah 6): hanya unit, periode, kode aset dan nomor baris yang dipakai; angka dan akun dihitung server
function validasiParamDepresiasiPPP($p)
{
	$aturan = array(
		'kodeorg'   => '/^[A-Za-z0-9]{1,10}$/',
		'periode'   => '/^[0-9]{4}-[0-9]{2}$/',
		'kodeasset' => '/^[A-Za-z0-9 ._\/-]{1,60}$/',
		'currRow'   => '/^[0-9]{1,6}$/'
	);
	foreach ($aturan as $nama => $pola) {
		if (!isset($p[$nama]) || !is_string($p[$nama]) || $p[$nama] === '') {
			return "Warning : Parameter " . $nama . " wajib diisi.";
		}
		if (!preg_match($pola, $p[$nama])) {
			return "Warning : Parameter " . $nama . " tidak valid.";
		}
	}
	return '';
}

#Depresiasi aset (langkah 6): daftar aset yang disusutkan pada periode itu beserta jumlah jurnal, kode jurnal dan akun debet/kredit.
#Dipakai bersama oleh list dan proses simpan supaya jumlah dan akun dihitung server, bukan dipercaya dari browser.
#$kodeasset kosong = semua aset unit itu. Hasil: kodeasset => array(...)
function hitungDepresiasiAssetPPP($kodeorg, $periode, $kodeasset = '')
{
	global $dbname;
	$kodeorg = addslashes($kodeorg);
	$periode = addslashes($periode);
	$tipeorg = '';
	foreach (fetchData("select tipe from " . $dbname . ".organisasi where kodeorganisasi = '" . $kodeorg . "'") as $r) {
		$tipeorg = $r['tipe'];
	}
	$zz = (isset($_SESSION['language']) && $_SESSION['language'] == 'EN') ? "b.namatipe1 as namatipe" : "b.namatipe";
	$wAset = ($kodeasset !== '') ? " and a.kodeasset='" . addslashes($kodeasset) . "'" : '';
	$res = fetchData("select a.namasset,a.kodeasset, a.tipeasset,a.jlhblnpenyusutan,a.awalpenyusutan,a.bulanan,a.hargaperolehan," . $zz . ",a.jenis_biaya, a.tipelokasi,
			left(a.tanggaldisposal,7) as periodenonaktif
		from " . $dbname . ".sdm_daftarasset a left join " . $dbname . ".sdm_5tipeasset b on a.tipeasset=b.kodetipe
		where a.kodeorg='" . $kodeorg . "' and status=1 and a.awalpenyusutan <= '" . $periode . "' and persendecline=0" . $wAset);

	$akunKode = array();
	$hasil = array();
	foreach ($res as $bar) {
		$x = mktime(0, 0, 0, intval(substr($bar['awalpenyusutan'], 5, 2) + ($bar['jlhblnpenyusutan'])), 15, intval(substr($bar['awalpenyusutan'], 0, 4)));
		$maxperiod = date('Y-m', $x);
		if ($bar['periodenonaktif'] != "0000-00") {
			if ($periode >= $bar['periodenonaktif']) {
				continue;
			}
		}
		if ($tipeorg == 'HOLDING' || $tipeorg == 'KANWIL') {
			$kodejurnal = 'DPH' . substr($bar['tipeasset'], 0, 2);
		} else {
			$kodejurnal = 'DEP' . substr($bar['tipeasset'], 0, 2);
		}
		if (!isset($akunKode[$kodejurnal])) {
			$akunKode[$kodejurnal] = array('', '');
			foreach (fetchData("select noakundebet,noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='" . $kodejurnal . "'") as $ra) {
				$akunKode[$kodejurnal] = array($ra['noakundebet'], $ra['noakunkredit']);
				break;
			}
		}
		if ($periode < $maxperiod) {
			$totalsudahsusut = ($bar['bulanan'] * $bar['jlhblnpenyusutan']);
			$selisihsusut = $bar['hargaperolehan'] - $totalsudahsusut;
			$selisihJurnal = ($periode != periodelalu($maxperiod)) ? 0 : $selisihsusut;
			$hasil[$bar['kodeasset']] = array(
				'tipeasset' => $bar['tipeasset'], 'namatipe' => $bar['namatipe'], 'kodeasset' => $bar['kodeasset'], 'namasset' => $bar['namasset'],
				'awal' => $bar['awalpenyusutan'], 'jlhbln' => $bar['jlhblnpenyusutan'], 'akhir' => periodelalu($maxperiod),
				'hargaperolehan' => $bar['hargaperolehan'], 'bulanan' => $bar['bulanan'],
				'totalsusut' => $totalsudahsusut, 'selisih' => $selisihsusut, 'selisihjurnal' => $selisihJurnal,
				'jumlah' => round($bar['bulanan'] + $selisihJurnal, 2), #sama dengan angka 2 desimal yang sebelumnya dikirim browser
				'kodejurnal' => $kodejurnal, 'debet' => $akunKode[$kodejurnal][0], 'kredit' => $akunKode[$kodejurnal][1]
			);
		}
	}
	return $hasil;
}

#validasi parameter langkah 7 (Transaksi Berulang): unit, periode (bulan 01-12) dan proses list/post
function validasiParamRutinPPP($p, $proses)
{
	if (!in_array($proses, array('list', 'post'), true)) {
		return "Warning : Parameter proses tidak valid.";
	}
	foreach (array('kodeorg', 'periode') as $wajib) {
		if (!isset($p[$wajib]) || !is_string($p[$wajib]) || $p[$wajib] === '') {
			return "Warning : Parameter " . $wajib . " wajib diisi.";
		}
	}
	if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $p['kodeorg'])) {
		return "Warning : Parameter kodeorg tidak valid.";
	}
	if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/', $p['periode'])) {
		return "Warning : Parameter periode tidak valid.";
	}
	return '';
}

#Transaksi berulang (langkah 7) yang dijurnalkan pada periode itu, lengkap dengan jumlah jurnalnya.
#Dipakai bersama oleh list dan proses simpan supaya angka dan daftar transaksinya sama.
#Hasil: array('baris' => array(...), 'bermasalah' => array(pesan...))
function hitungTransaksiRutinPPP($kodeorg, $periode)
{
	global $dbname;
	$res = fetchData("SELECT * FROM " . $dbname . ".keu_transaksi_rutin WHERE kodeorg='" . addslashes($kodeorg) . "' and posting in ('1','2')
		and tanggalmulai<='" . addslashes($periode) . "-31' and tanggalselesai>='" . addslashes($periode) . "-01'");
	$baris = array();
	$bermasalah = array();
	foreach ($res as $row) {
		if ($row['posting'] == 2) {
			if ($periode >= substr($row['tanggalstop'], 0, 7)) {
				continue; #sudah dihentikan
			}
		}
		if (!is_numeric($row['tenor']) || $row['tenor'] <= 0) {
			$bermasalah[] = "Transaksi " . $row['notransaksi'] . ": tenor kosong atau nol, tidak bisa dijurnalkan";
			continue;
		}
		$rpperbulan = $row['harga_barang'] / $row['tenor'];
		$perBulan = ($rpperbulan < 0) ? ceil($rpperbulan) : floor($rpperbulan); #bagian bulat, sama dengan angka sebelum titik desimal
		$nilaisisa = 0;
		if (substr($row['tanggalselesai'], 0, 7) == $periode) {
			$nilaisisa = $row['harga_barang'] - ($perBulan * $row['tenor']);
		}
		$row['rpperbulan'] = $rpperbulan;
		$row['perbulan'] = $perBulan;
		$row['nilaisisa'] = $nilaisisa;
		$row['jumlahjurnal'] = $perBulan + $nilaisisa;
		$baris[] = $row;
	}
	return array('baris' => $baris, 'bermasalah' => $bermasalah);
}

#validasi parameter proses Alokasi Traksi dan Bengkel (langkah 8): hanya unit, periode, kendaraan, jenis dan nomor urut yang dibaca dari browser
function validasiParamAlokasiTraksiPPP($p)
{
	$aturan = array(
		'kodeorg' => '/^[A-Za-z0-9]{1,10}$/',
		'periode' => '/^[0-9]{4}-(0[1-9]|1[0-2])$/',
		'kodevhc' => '/^[A-Za-z0-9 ._\/-]{1,40}$/',
		'jenis'   => '/^(BYWS|BYWSMM|ALKJAM)$/',
		'nourut'  => '/^[0-9]{1,6}$/'
	);
	foreach ($aturan as $nama => $pola) {
		if (!isset($p[$nama]) || !is_string($p[$nama]) || $p[$nama] === '') {
			return "Warning : Parameter " . $nama . " wajib diisi.";
		}
		if (!preg_match($pola, $p[$nama])) {
			return "Warning : Parameter " . $nama . " tidak valid.";
		}
	}
	return '';
}

#Hitung alokasi traksi dan bengkel satu unit untuk satu periode (dipakai bersama oleh list dan proses simpan, supaya jumlah jurnal
#dihitung server, bukan dipercaya dari browser). $cekSiap = true menjalankan pemeriksaan kesiapan data (khusus tampilan list).
#Hasil: array('pesan' => teks bila harus berhenti, ...data) ; pesan kosong = sukses.
function hitungAlokasiTraksiPPP($kodeorg, $periode, $cekSiap = true)
{
	global $dbname;
	$kodeorg = addslashes($kodeorg);
	$periode = addslashes($periode);
	$h = array('pesan' => '');

	$tipeUnit = '';
	foreach (fetchData("select tipe from " . $dbname . ".organisasi where kodeorganisasi = '" . $kodeorg . "'") as $r) {
		$tipeUnit = $r['tipe'];
	}
	#cek proses penarikan potongan gaji sudah diproses atau belum
	if ($cekSiap && $tipeUnit != "HOLDING") {
		$rPot = fetchData("select * from " . $dbname . ".sdm_gaji where periodegaji='" . $periode . "' and kodeorg='" . $kodeorg . "'
			and idkomponen in (select id from " . $dbname . ".sdm_ho_component where plus=0)");
		if (count($rPot) != 0) {
			$rCek = fetchData("select * from " . $dbname . ".keu_jurnalht where nojurnal like '%POT%' and " . sqlRentangPeriode($periode) . " and nojurnal like '%" . $kodeorg . "%'");
			if (count($rCek) == 0) {
				$h['pesan'] = 'warning: Jalankan Proses Penarikan Potongan Gaji';
				return $h;
			}
		}
	}
	#1. ambil periode akuntansi
	$tgmulai = '';
	$tgsampai = '';
	foreach (fetchData("select tanggalmulai,tanggalsampai,periode from " . $dbname . ".setup_periodeakuntansi where kodeorg ='" . $kodeorg . "' and tutupbuku=0 and periode='" . $periode . "'") as $r) {
		$tgsampai = $r['tanggalsampai'];
		$tgmulai = $r['tanggalmulai'];
	}
	if ($tgmulai == '' || $tgsampai == '') {
		$h['pesan'] = "Error: Accounting period is not registered";
		return $h;
	}
	$h['tgmulai'] = $tgmulai;
	$h['tgsampai'] = $tgsampai;

	#= daftar kendaraan yang service di bengkel dan yang bekerja (untuk cek tutup buku kendaraan)
	$arrkdvhc = array();
	foreach (fetchData("select distinct(kodevhc) as kodevhc from " . $dbname . ".msvhc_by_operator where notransaksi like '" . $kodeorg . "%' and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'") as $r) {
		$arrkdvhc[$r['kodevhc']] = $r['kodevhc'];
	}
	foreach (fetchData("select distinct(kodevhc) as kodevhc from " . $dbname . ".vhc_runht where kodevhc in(select kodevhc from " . $dbname . ".vhc_5master where kodetraksi like '" . $kodeorg . "%') and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'") as $r) {
		$arrkdvhc[$r['kodevhc']] = $r['kodevhc'];
	}
	if ($cekSiap) {
		$dtvhchist = array();
		foreach (fetchData("select * from " . $dbname . ".vhc_5master_hist where kodevhc in ('" . implode("','", array_map('addslashes', $arrkdvhc)) . "') and periode='" . $periode . "'") as $r) {
			$dtvhchist[$r['kodevhc']] = $r['kodevhc'];
		}
		$dtvhcmastertraksi = array();
		foreach (fetchData("select * from " . $dbname . ".vhc_5master where kodevhc in ('" . implode("','", array_map('addslashes', $arrkdvhc)) . "')") as $r) {
			$dtvhcmastertraksi[$r['kodevhc']] = $r['kodetraksi'];
		}
		$nohist = 0;
		$dterror = '';
		foreach ($arrkdvhc as $dtkdvhc) {
			if (!isset($dtvhchist[$dtkdvhc]) || $dtvhchist[$dtkdvhc] != $dtkdvhc) {
				$nohist++;
				$dterror .= "Kendaraan " . htmlspecialchars($dtkdvhc) . " belum terdaftar ditutup buku kendaraan, silahkan informasikan/proses closing kendaraan traksi unit : " . htmlspecialchars(substr(isset($dtvhcmastertraksi[$dtkdvhc]) ? $dtvhcmastertraksi[$dtkdvhc] : '', 0, 4)) . " periode " . htmlspecialchars(stripslashes($periode)) . " di menu : Traksi->Proses->Tutup Buku Kendaraan<br><br>";
			}
		}
		if ($nohist > 0) {
			$h['pesan'] = "<fieldset style=float:left><label style=font-size:14px;color:blue;>" . $dterror . "</label></fieldset>";
			return $h;
		}
	}

	#ambil kamus traksi
	$kamus = array();
	foreach (fetchData("select kodetraksi,kodevhc from " . $dbname . ".vhc_5master_hist where periode='" . $periode . "'") as $r) {
		$kamus[$r['kodevhc']] = $r['kodetraksi'];
	}
	$h['kamus'] = $kamus;

	#pastikan semua kegiatan ada noakun pada saat entry (antisipasi penggantian kegiatan traksi)
	if ($cekSiap) {
		$tanpaAkun = fetchData("select distinct a.notransaksi,a.jenispekerjaan from " . $dbname . ".vhc_rundt a
			left join " . $dbname . ".vhc_runht b on a.notransaksi=b.notransaksi
			where " . sqlRentangPeriode($periode, 'b.tanggal') . " and kodeorg='" . $kodeorg . "'
			and a.jenispekerjaan not in (SELECT kodekegiatan FROM " . $dbname . ".vhc_kegiatan)");
		if (count($tanpaAkun) > 0) {
			$teks = "Error : There are Vehicle activity that do not have Acount Number, Please contact administrator\n";
			foreach ($tanpaAkun as $barf) {
				$teks .= htmlspecialchars(print_r($barf, true));
			}
			$h['pesan'] = $teks;
			return $h;
		}
	}

	#2. ambil biaya workshop: kode parameter WS1, ambil semua noakun biaya bengkel
	$dariakun = '';
	$sampaiakun = '';
	foreach (fetchData("select noakundebet,sampaidebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='WS1'") as $r) {
		$dariakun = $r['noakundebet'];
		$sampaiakun = $r['sampaidebet'];
	}
	if ($dariakun == '' or $sampaiakun == '') {
		$h['pesan'] = 'Eror: Journalid for WS1 not found';
		return $h;
	}
	$bybengkel = 0;
	foreach (fetchData("select sum(debet-kredit) as jumlah from " . $dbname . ".keu_jurnaldt_vw
		where noakun >='" . addslashes($dariakun) . "' and noakun<='" . addslashes($sampaiakun) . "'
		and kodeorg like '" . $kodeorg . "%'
		and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'
		and (noreferensi not in('ALK_KERJA_AB','ALK_BY_WS','ALK_MAINTENANCE') or noreferensi is NULL)") as $r) {
		$bybengkel = $r['jumlah'];
	}

	#3 periksa apakah sudah posting semua
	if ($cekSiap) {
		$resS = fetchData("select * from " . $dbname . ".msvhc_by_operator where posting=0
			and kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist where kodetraksi like '" . $kodeorg . "%' and periode= '" . $periode . "')
			and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'");
		$resP = fetchData("select * from " . $dbname . ".vhc_runht where posting=0
			and kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist where kodetraksi like '" . $kodeorg . "%' and periode= '" . $periode . "')
			and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'");
		if (count($resS) > 0 or count($resP) > 0) {
			$teks = "There are transactions that have not posted => Service :\n";
			$no = 0;
			foreach ($resS as $bart) {
				$no += 1;
				$teks .= $no . ". No " . htmlspecialchars($bart['notransaksi']) . " => " . tanggalnormal($bart['tanggal']) . "\n";
			}
			$teks .= "There are transactions that have not posted => Pekerjaan :\n";
			$no = 0;
			foreach ($resP as $bart) {
				$no += 1;
				$teks .= $no . ". No " . htmlspecialchars($bart['notransaksi']) . " => " . tanggalnormal($bart['tanggal']) . "\n";
			}
			$h['pesan'] = $teks . "warning";
			return $h;
		}
	}

	#4 ambil semua kendaraan yang diservice pada periode berjalan
	$kend = array();
	$byrinci = array();
	$arrkodevhc = array();
	$totaljamservice = 0;
	foreach (fetchData("select sum(downtime) as dt,kodevhc from " . $dbname . ".msvhc_by_operator
		where notransaksi like '" . $kodeorg . "%' and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "' and posting=1
		group by kodevhc") as $r) {
		$arrkodevhc[$r['kodevhc']] = $r['kodevhc'];
		$totaljamservice += $r['dt'];
		$kend[$r['kodevhc']] = $r['dt'];
	}
	#= 4 ambil daftar station pabrik
	foreach (fetchData("select sum(jumlahjamperbaikan) as dt,statasiun as kodevhc
		from " . $dbname . ".pabrik_rawatmesinht where 0=0 and
		pabrik='" . $kodeorg . "' and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'
		group by statasiun order by statasiun asc") as $r) {
		$totaljamservice += $r['dt'];
		$kend[$r['kodevhc']] = $r['dt'];
	}

	#biaya bengkel dibagi ke kendaraan/stasiun sesuai jam servis, sisa pembulatan masuk ke yang jamnya paling banyak
	$jamterbanyak = -1;
	$tbyrinci = 0;
	$kendaraanpembulatanbengkel = null;
	foreach ($kend as $key => $val) {
		$byrinci[$key] = ($totaljamservice > 0) ? floor(($val / $totaljamservice) * $bybengkel) : 0;
		$tbyrinci += $byrinci[$key];
		if ($val > $jamterbanyak) {
			$jamterbanyak = $val;
			$kendaraanpembulatanbengkel = $key;
		}
	}
	$jumlahpembulatanbengkel = array();
	if ($kendaraanpembulatanbengkel !== null) {
		$jumlahpembulatanbengkel[$kendaraanpembulatanbengkel] = round($bybengkel - $tbyrinci, 0);
	}

	#4.5 ambil noakun biaya kendaraan
	$akunkdari = '';
	$akunksampai = '';
	foreach (fetchData("select distinct noakundebet,sampaidebet from " . $dbname . ".keu_5parameterjurnal where jurnalid='LPVHC'") as $r) {
		$akunkdari = $r['noakundebet'];
		$akunksampai = $r['sampaidebet'];
	}
	if ($akunkdari == '' or $akunksampai == '') {
		$h['pesan'] = "Error: Journal parameter for LPVHC not found";
		return $h;
	}
	#5 ambil biaya perkendaraan dari data yang sudah dijurnal
	$biayattlkend = array();
	foreach (fetchData("select sum(debet-kredit) as jlh,kodevhc from " . $dbname . ".keu_jurnaldt_vw where
		kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist where kodetraksi like '" . $kodeorg . "%' and periode= '" . $periode . "')
		and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'
		and nojurnal like '%" . $kodeorg . "%'
		and (noakun between '" . addslashes($akunkdari) . "' and '" . addslashes($akunksampai) . "')
		and (noreferensi not in('ALK_KERJA_AB','ALK_BY_WS','ALK_WS_GYMH') or noreferensi is NULL)
		group by kodevhc") as $r) {
		if (!isset($biayattlkend[$r['kodevhc']])) $biayattlkend[$r['kodevhc']] = 0;
		$biayattlkend[$r['kodevhc']] += $r['jlh'];
	}

	#mengambil biaya service di bengkel unit lain
	$noakunAlkBengkel = '';
	foreach (fetchData("select noakunkredit from " . $dbname . ".keu_5parameterjurnal where jurnalid='WS2'") as $r) {
		$noakunAlkBengkel = $r['noakunkredit'];
	}
	if ($noakunAlkBengkel != '') {
		foreach (fetchData("select sum(jumlah*-1) as service,kodevhc from " . $dbname . ".keu_jurnaldt_vw where
			kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist where kodetraksi like '" . $kodeorg . "%' and periode= '" . $periode . "')
			and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "'
			and kodeorg not like '%" . $kodeorg . "%'
			and noakun= '" . addslashes($noakunAlkBengkel) . "'
			and (noreferensi not in('ALK_KERJA_AB','ALK_TRK_GYMH','ALK_BY_WS','ALK_WS_GYMH')  or noreferensi is NULL)
			group by kodevhc") as $r) {
			if (!isset($biayattlkend[$r['kodevhc']])) $biayattlkend[$r['kodevhc']] = 0;
			$biayattlkend[$r['kodevhc']] += $r['service'];
		}
	}

	#6 ambil semua jam kerja kendaraan per unit
	$biayaperjam = array();
	$jamkerja = array();
	$biayakendaraan = array();
	$talokasibiayakendaraan = array();
	foreach (fetchData("select sum(a.jumlah) as jlhjam,kodevhc from " . $dbname . ".vhc_rundt a
		left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
		left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi
		where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' and alokasibiaya!=''
		and jenispekerjaan!=''  and
		kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist where kodetraksi like '" . $kodeorg . "%' and periode= '" . $periode . "')
		group by kodevhc") as $r) {
		$arrkodevhc[$r['kodevhc']] = $r['kodevhc'];
		if (!isset($byrinci[$r['kodevhc']])) {
			$byrinci[$r['kodevhc']] = 0;
		}
		$ttlKend = isset($biayattlkend[$r['kodevhc']]) ? $biayattlkend[$r['kodevhc']] : 0;
		#biaya kendaraan ditambah biaya bengkel yang masuk ke masing-masing kendaraan
		$biayaperjam[$r['kodevhc']] = ($r['jlhjam'] != 0) ? floor(($ttlKend + $byrinci[$r['kodevhc']]) / $r['jlhjam']) : 0;
		$jamkerja[$r['kodevhc']] = $r['jlhjam'];
		$biayakendaraan[$r['kodevhc']] = $ttlKend + $byrinci[$r['kodevhc']];
		$talokasibiayakendaraan[$r['kodevhc']] = $biayaperjam[$r['kodevhc']] * $r['jlhjam'];
	}

	$h['byrinci'] = $byrinci;
	$h['kend'] = $kend; #jam servis per kendaraan/stasiun
	$h['jamkerja'] = $jamkerja;
	$h['biayattlkend'] = $biayattlkend; #biaya langsung kendaraan
	$h['bybengkel'] = $bybengkel;
	$h['totaljamservice'] = $totaljamservice;
	$h['kendaraanpembulatanbengkel'] = $kendaraanpembulatanbengkel;
	$h['akunbengkel'] = array($dariakun, $sampaiakun);
	$h['akunkendaraan'] = array($akunkdari, $akunksampai);
	$h['jumlahpembulatanbengkel'] = $jumlahpembulatanbengkel;
	$h['biayaperjam'] = $biayaperjam;
	$h['biayakendaraan'] = $biayakendaraan;
	$h['talokasibiayakendaraan'] = $talokasibiayakendaraan;
	return $h;
}

#Baris alokasi (urutan sama dengan list lama): BYWS/BYWSMM per kendaraan/stasiun lalu ALKJAM per kendaraan.
function barisAlokasiTraksiPPP($h)
{
	$baris = array();
	$no = $nobyws = $nobywsmm = $nobyperjam = 0;
	$kamus = isset($h['kamus']) ? $h['kamus'] : array();
	foreach ($h['byrinci'] as $key => $val) {
		if (isset($kamus[$key]) && $kamus[$key] != '') {
			$jenis = 'BYWS';
			$nobyws += 1;
			$notampil = $nobyws;
		} else {
			$jenis = 'BYWSMM';
			$nobywsmm += 1;
			$notampil = $nobywsmm;
		}
		$no += 1;
		$baris[] = array('no' => $no, 'kodevhc' => $key, 'jenis' => $jenis, 'org' => isset($kamus[$key]) ? $kamus[$key] : '',
			'jumlah' => number_format($val, 0, '.', ''),
			'jumlahpembulatan' => number_format(isset($h['jumlahpembulatanbengkel'][$key]) ? $h['jumlahpembulatanbengkel'][$key] : 0, 10, '.', ''),
			'jumlahbiayakendaraan' => '', 'nourut' => $notampil,
			'jam' => isset($h['kend'][$key]) ? $h['kend'][$key] : 0, 'bagian' => $val,
			'pembulatanbengkel' => isset($h['jumlahpembulatanbengkel'][$key]) ? $h['jumlahpembulatanbengkel'][$key] : 0);
	}
	foreach ($h['biayaperjam'] as $key => $jlh) {
		$no += 1;
		$nobyperjam += 1;
		$bk = isset($h['biayakendaraan'][$key]) ? $h['biayakendaraan'][$key] : 0;
		$ta = isset($h['talokasibiayakendaraan'][$key]) ? $h['talokasibiayakendaraan'][$key] : 0;
		$pb = isset($h['jumlahpembulatanbengkel'][$key]) ? $h['jumlahpembulatanbengkel'][$key] : 0;
		$baris[] = array('no' => $no, 'kodevhc' => $key, 'jenis' => 'ALKJAM', 'org' => isset($kamus[$key]) ? $kamus[$key] : '',
			'jumlah' => number_format(fixnan($jlh), 0, '.', ''),
			'jumlahpembulatan' => number_format(($bk - $ta + $pb), 2, '.', ''),
			'jumlahbiayakendaraan' => $bk, 'nourut' => $nobyperjam,
			'jam' => isset($h['jamkerja'][$key]) ? $h['jamkerja'][$key] : 0, 'langsung' => isset($h['biayattlkend'][$key]) ? $h['biayattlkend'][$key] : 0,
			'bagian' => isset($h['byrinci'][$key]) ? $h['byrinci'][$key] : 0, 'total' => $bk, 'perjam' => fixnan($jlh), 'dialokasi' => $ta, 'pembulatanbengkel' => $pb,
			'sisa' => ($bk - $ta + $pb));
	}
	return $baris;
}

#validasi parameter langkah 10 (Alokasi bibit ke Lapangan): proses list/getnoakun/prosesjurnal; proses jurnal hanya membaca blok asal, blok tujuan, jenis tanam, kegiatan dan nomor baris dari browser
function validasiParamBibitAlokasiPPP($p, $proses)
{
	if (!in_array($proses, array('', 'list', 'getnoakun', 'prosesjurnal'), true)) {
		return "Warning : Parameter proses tidak valid.";
	}
	if ($proses == 'getnoakun') {
		if (!isset($p['kegiatan']) || !is_string($p['kegiatan']) || !preg_match('/^[A-Za-z0-9.]{1,20}$/', $p['kegiatan'])) {
			return "Warning : Parameter kegiatan tidak valid.";
		}
		return '';
	}
	$aturan = array('kodeorg' => '/^[A-Za-z0-9]{1,10}$/', 'periode' => '/^[0-9]{4}-(0[1-9]|1[0-2])$/');
	if ($proses == 'prosesjurnal') {
		$aturan += array('blokbbt' => '/^[A-Za-z0-9]{1,20}$/', 'bloktnm' => '/^[A-Za-z0-9]{1,20}$/', 'jenis' => '/^[A-Za-z0-9]{1,10}$/',
			'kegiatan' => '/^[A-Za-z0-9.]{1,20}$/', 'currRow' => '/^[0-9]{1,6}$/');
	}
	foreach ($aturan as $nama => $pola) {
		if (!isset($p[$nama]) || !is_string($p[$nama]) || $p[$nama] === '') {
			return "Warning : Parameter " . $nama . " wajib diisi.";
		}
		if (!preg_match($pola, $p[$nama])) {
			return "Warning : Parameter " . $nama . " tidak valid.";
		}
	}
	return '';
}

#Alokasi bibit ke lapangan (langkah 10): daftar transaksi PNB periode itu dan harga rata-rata per blok asal dan jenis tanam.
#Dipakai bersama oleh list dan proses jurnal supaya rupiah dihitung server, bukan dipercaya dari browser.
#Hasil: array('pesan' => teks bila harus berhenti, 'baris' => array(...), ...)
function hitungBibitAlokasiPPP($kodeorg, $periode)
{
	global $dbname;
	$kodeorg = addslashes($kodeorg);
	$periode = addslashes($periode);
	$h = array('pesan' => '', 'baris' => array(), 'transaksi' => array(), 'paramjurnal' => array(), 'blokNol' => array(), 'total' => 0);
	$induk = makeOption($dbname, 'organisasi', 'kodeorganisasi,induk');
	if (!isset($induk[$kodeorg])) {
		$h['pesan'] = 'Warning : Unit ' . htmlspecialchars(stripslashes($kodeorg)) . ' tidak ditemukan.';
		return $h;
	}

	#1. ambil periode akuntansi (harus terdaftar dan belum tutup buku)
	$tgmulai = '';
	$tgsampai = '';
	foreach (fetchdata("select * from " . $dbname . ".setup_periodeakuntansi where kodeorg ='" . $kodeorg . "' and tutupbuku=0 and periode='" . $periode . "' ") as $bar) {
		$tgsampai = $bar['tanggalsampai'];
		$tgmulai = $bar['tanggalmulai'];
	}
	if ($tgmulai == '' || $tgsampai == '') {
		$h['pesan'] = "Error: Accounting period " . stripslashes($periode) . " is not registered or has been closed";
		return $h;
	}
	$h['tgmulai'] = $tgmulai;
	$h['tgsampai'] = $tgsampai;

	$noblok = array();
	$nobloktujuan = array();
	$jenistanam = array();
	$subttl = array();
	$total = 0;
	foreach (fetchdata("select * from " . $dbname . ".bibitan_mutasi where kodeorg like '" . $kodeorg . "%' and tanggal>='" . $tgmulai . "' and tanggal<='" . $tgsampai . "' and kodetransaksi='PNB'") as $bar) {
		if ($bar['post'] == '0') {
			$h['pesan'] = "Error : Ada transaksi yang belum diposting.";
			return $h;
		}
		$noblok[$bar['kodeorg']] = $bar['kodeorg'];
		$nobloktujuan[$bar['kodeorg']][$bar['afdeling']][$bar['jenistanam']] = $bar['jenistanam'];
		$jenistanam[$bar['jenistanam']] = $bar['jenistanam'];
		if (!isset($subttl[$bar['kodeorg']][$bar['afdeling']][$bar['jenistanam']])) $subttl[$bar['kodeorg']][$bar['afdeling']][$bar['jenistanam']] = 0;
		$subttl[$bar['kodeorg']][$bar['afdeling']][$bar['jenistanam']] += abs($bar['jumlah']);
		$total += abs($bar['jumlah']);
		$h['transaksi'][] = $bar;
	}
	$h['total'] = $total;
	if (empty($h['transaksi'])) {
		$h['pesan'] = "Warning: Tidak ada transaksi penanaman bibit (PNB) pada periode " . stripslashes($periode) . " untuk unit " . htmlspecialchars(stripslashes($kodeorg)) . ", tidak ada yang diproses.";
		return $h;
	}
	$inBlok = "'" . implode("','", array_map('addslashes', $noblok)) . "'";

	$sumM = array();
	foreach (fetchData("SELECT kodeorg, SUM(jumlah) as nilai FROM " . $dbname . ".bibitan_mutasi WHERE kodeorg in (" . $inBlok . ") and LEFT(tanggal,7)<='" . $periode . "' group by kodeorg") as $bar) {
		if (!isset($sumM[$bar['kodeorg']])) $sumM[$bar['kodeorg']] = 0;
		$sumM[$bar['kodeorg']] += $bar['nilai'];
	}
	$sumN = array();
	foreach (fetchData("SELECT kodeorg, SUM(jumlah) as nilai FROM " . $dbname . ".bibitan_mutasi WHERE kodeorg in (" . $inBlok . ") and kodetransaksi='PNB' and LEFT(tanggal,7)='" . $periode . "' group by kodeorg") as $bar) {
		if (!isset($sumN[$bar['kodeorg']])) $sumN[$bar['kodeorg']] = 0;
		$sumN[$bar['kodeorg']] += abs($bar['nilai']);
	}

	$sumX = array();
	$sumH = array();
	$indukInduk = isset($induk[$induk[$kodeorg]]) ? $induk[$induk[$kodeorg]] : '';
	foreach ($jenistanam as $jenistnm) {
		$resPj = fetchData("SELECT noakundebet,noakunkredit,sampaidebet,sampaikredit FROM " . $dbname . ".keu_5parameterjurnal WHERE kodeorg='" . addslashes($indukInduk) . "' and jurnalid='PNB' and kodeaplikasi ='" . addslashes($jenistnm) . "'");
		if (empty($resPj[0])) {
			$h['pesan'] = "Error : Parameter Jurnal untuk PNB jenis tanam " . htmlspecialchars($jenistnm) . " belum ada (Keuangan - Setup - Parameter Jurnal, kode aplikasi " . htmlspecialchars($jenistnm) . ")";
			return $h;
		}
		$paramJurnal = $resPj[0];
		$h['paramjurnal'][$jenistnm] = $paramJurnal;
		foreach (fetchData("SELECT kodeblok, SUM(jumlah) as nilai FROM " . $dbname . ".keu_jurnaldt WHERE LEFT(tanggal,7)<='" . $periode . "' and noakun >= '" . addslashes($paramJurnal['sampaidebet']) . "' and noakun <='" . addslashes($paramJurnal['sampaikredit']) . "' and kodeorg='" . $kodeorg . "' and kodeblok in (" . $inBlok . ") group by kodeblok") as $bar) {
			if (!isset($sumX[$bar['kodeblok']][$jenistnm])) $sumX[$bar['kodeblok']][$jenistnm] = 0;
			$sumX[$bar['kodeblok']][$jenistnm] += $bar['nilai'];
		}
		foreach (fetchData("SELECT kodeblok, SUM(jumlah) as nilai FROM " . $dbname . ".keu_jurnaldt WHERE LEFT(tanggal,7)<'" . $periode . "' and noakun = '" . addslashes($paramJurnal['noakunkredit']) . "' and kodeorg='" . $kodeorg . "' and kodeblok in (" . $inBlok . ") group by kodeblok") as $bar) {
			if (!isset($sumH[$bar['kodeblok']][$jenistnm])) $sumH[$bar['kodeblok']][$jenistnm] = 0;
			$sumH[$bar['kodeblok']][$jenistnm] += abs($bar['nilai']);
		}
	}

	#harga rata-rata per blok asal dan jenis tanam
	$sumY = array();
	$sumZ = array();
	foreach ($noblok as $blok) {
		$sumY[$blok] = (isset($sumM[$blok]) ? $sumM[$blok] : 0) + (isset($sumN[$blok]) ? $sumN[$blok] : 0);
		foreach ($jenistanam as $jenistnm) {
			$x = isset($sumX[$blok][$jenistnm]) ? $sumX[$blok][$jenistnm] : 0;
			$hh = isset($sumH[$blok][$jenistnm]) ? $sumH[$blok][$jenistnm] : 0;
			if ($sumY[$blok] != 0) {
				$sumZ[$blok][$jenistnm] = ($x - $hh) / $sumY[$blok];
			} else {
				$sumZ[$blok][$jenistnm] = 0;
				$h['blokNol'][$blok] = $blok; #jumlah bibit nol: harga rata-rata tidak bisa dihitung
			}
		}
	}

	$no = 0;
	foreach ($nobloktujuan as $blok => $v1) {
		foreach ($v1 as $tujuan => $v2) {
			foreach ($v2 as $jenis) {
				$no++;
				$jml = abs($subttl[$blok][$tujuan][$jenis]);
				$x = isset($sumX[$blok][$jenis]) ? $sumX[$blok][$jenis] : 0;
				$hh = isset($sumH[$blok][$jenis]) ? $sumH[$blok][$jenis] : 0;
				$h['baris'][] = array('no' => $no, 'blok' => $blok, 'tujuan' => $tujuan, 'jenis' => $jenis, 'jumlah' => $jml,
					'sumX' => $x, 'sumH' => $hh, 'sumY' => $sumY[$blok], 'sumZ' => $sumZ[$blok][$jenis],
					'rupiah' => number_format($jml * $sumZ[$blok][$jenis], 0, '.', ''),
					'kredit' => $h['paramjurnal'][$jenis]['noakunkredit']);
			}
		}
	}
	return $h;
}

#validasi parameter langkah 11 (Selisih Kurs): proses list/post, unit dan periode (bulan 01-12)
function validasiParamKursPPP($p, $proses)
{
	if (!in_array($proses, array('list', 'post'), true)) {
		return "Warning : Parameter proses tidak valid.";
	}
	foreach (array('kodeorg', 'periode') as $wajib) {
		if (!isset($p[$wajib]) || !is_string($p[$wajib]) || $p[$wajib] === '') {
			return "Warning : Parameter " . $wajib . " wajib diisi.";
		}
	}
	if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $p['kodeorg'])) {
		return "Warning : Parameter kodeorg tidak valid.";
	}
	if (!preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/', $p['periode'])) {
		return "Warning : Parameter periode tidak valid.";
	}
	return '';
}

#Selisih kurs (langkah 11). Kurs yang dipakai = kurs yang berlaku pada akhir periode dan akhir periode sebelumnya (kurs terbaru dengan
#daritanggal sampai tanggal itu), bukan sembarang baris tanggal 1 atau akhir bulan dari periode lain.
#Dipakai bersama oleh list dan proses jurnal. Hasil: array('pesan' => teks bila harus berhenti, 'kurs' => ..., 'baris' => ...)
function hitungSelisihKursPPP($kodeorg, $periode)
{
	global $dbname;
	$kodeorgQ = addslashes($kodeorg);
	$tahunbulan = str_replace('-', '', $periode);
	$bulan = substr($tahunbulan, 4, 2);
	$akhir = date('Y-m-t', strtotime($periode . '-01'));
	$akhirLalu = date('Y-m-t', strtotime($periode . '-01 -1 month'));
	$h = array('pesan' => '', 'akhir' => $akhir, 'akhirLalu' => $akhirLalu, 'kurs' => array(), 'kursLalu' => array(), 'baris' => array(), 'peringatan' => array(), 'total' => 0);

	#mutasi kas/bank bermata uang asing bulan ini: jumlah valas dan nilai rupiah menurut kurs transaksi
	$data = array();
	foreach (fetchData("SELECT noakun,tipetransaksi,matauang,sum(jumlah) as jumlah FROM " . $dbname . ".keu_kasbankht
		WHERE left(notransaksi,6) = '" . $tahunbulan . "' and matauang != 'IDR' and kodeorg='" . $kodeorgQ . "'
		GROUP BY noakun,tipetransaksi,matauang") as $row) {
		if (!isset($data[$row['matauang']][$row['noakun']])) {
			$data[$row['matauang']][$row['noakun']] = 0;
		}
		if ($row['tipetransaksi'] == 'M') {
			$data[$row['matauang']][$row['noakun']] += $row['jumlah'];
		} elseif ($row['tipetransaksi'] == 'K') {
			$data[$row['matauang']][$row['noakun']] -= $row['jumlah'];
		}
	}
	$dataMutasi = array();
	foreach (fetchData("SELECT noakun,tipetransaksi,matauang,sum(jumlah*kurs) as jumlah FROM " . $dbname . ".keu_kasbankht
		WHERE left(notransaksi,6) = '" . $tahunbulan . "' and matauang != 'IDR' and kodeorg='" . $kodeorgQ . "'
		GROUP BY noakun,tipetransaksi,matauang") as $row) {
		if (!isset($dataMutasi[$row['matauang']][$row['noakun']])) {
			$dataMutasi[$row['matauang']][$row['noakun']] = 0;
		}
		if ($row['tipetransaksi'] == 'M') {
			$dataMutasi[$row['matauang']][$row['noakun']] += $row['jumlah'];
		} elseif ($row['tipetransaksi'] == 'K') {
			$dataMutasi[$row['matauang']][$row['noakun']] -= $row['jumlah'];
		}
	}
	if (empty($data)) {
		return $h;
	}

	#kurs yang berlaku pada akhir periode dan akhir periode sebelumnya
	$kursAkhir = array();
	$kursLalu = array();
	foreach (fetchData("select kode,daritanggal,kurs from " . $dbname . ".setup_matauangrate where daritanggal <= '" . $akhir . "' order by daritanggal asc") as $r) {
		$kursAkhir[$r['kode']] = array($r['daritanggal'], $r['kurs']);
		if ($r['daritanggal'] <= $akhirLalu) {
			$kursLalu[$r['kode']] = array($r['daritanggal'], $r['kurs']);
		}
	}
	$optAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun', "kasbank=1");

	foreach ($data as $mtUang => $row1) {
		if (!isset($kursAkhir[$mtUang]) || $kursAkhir[$mtUang][1] <= 0 || !isset($kursLalu[$mtUang]) || $kursLalu[$mtUang][1] <= 0) {
			$h['pesan'] = "Error: Kurs " . htmlspecialchars($mtUang) . " yang berlaku pada " . $akhir . " dan/atau " . $akhirLalu . " belum ada di Setup Mata Uang Rate. Isi kurs dulu, selisih kurs tidak dihitung dengan kurs nol.";
			return $h;
		}
		$h['kurs'][$mtUang] = $kursAkhir[$mtUang];
		$h['kursLalu'][$mtUang] = $kursLalu[$mtUang];
		foreach (array(array($kursAkhir[$mtUang][0], $akhir, 'akhir periode'), array($kursLalu[$mtUang][0], $akhirLalu, 'akhir periode sebelumnya')) as $c) {
			if ((strtotime($c[1]) - strtotime($c[0])) > 35 * 86400) {
				$h['peringatan'][$mtUang . '|' . $c[2]] = "Kurs " . $mtUang . " " . $c[2] . " (" . $c[1] . ") memakai kurs tanggal " . $c[0] . ", lebih dari 35 hari sebelumnya. Periksa Setup Mata Uang Rate.";
			}
		}
		foreach ($row1 as $akun => $jumlahValas) {
			$sawal = 0;
			foreach (fetchData("select sum(awal" . $bulan . ") as sawal from " . $dbname . ".keu_saldobulanan where kodeorg='" . $kodeorgQ . "' and periode='" . $tahunbulan . "' and noakun='" . addslashes($akun) . "'") as $rs) {
				$sawal = $rs['sawal'] ? $rs['sawal'] : 0;
			}
			$mutasiRp = isset($dataMutasi[$mtUang][$akun]) ? $dataMutasi[$mtUang][$akun] : 0;
			$sawalValas = $sawal / $kursLalu[$mtUang][1];
			$akhirValas = $sawalValas + $jumlahValas;
			$akhirRpSeharusnya = $akhirValas * $kursAkhir[$mtUang][1];
			$akhirRpBuku = $sawal + $mutasiRp;
			$selisih = round($akhirRpSeharusnya - $akhirRpBuku, 2);
			$h['baris'][] = array('matauang' => $mtUang, 'noakun' => $akun, 'namaakun' => isset($optAkun[$akun]) ? $optAkun[$akun] : '',
				'sawal' => $sawal, 'sawalValas' => $sawalValas, 'mutasiValas' => $jumlahValas, 'akhirValas' => $akhirValas,
				'mutasiRp' => $mutasiRp, 'akhirRpSeharusnya' => $akhirRpSeharusnya, 'akhirRpBuku' => $akhirRpBuku, 'selisih' => $selisih);
			$h['total'] += $selisih;
		}
	}
	return $h;
}
