<?php
# CABANG PPP dari keu_slave_3traksi.php (Proses Akhir Bulan > 8. Alokasi Traksi dan Bengkel). Dipanggil otomatis oleh js/keu_3posting.js
# (tujuanProses) hanya untuk unit yang induknya PPP; unit lain tetap memakai keu_slave_3traksi.php. Perubahan khusus PPP tandai "#PPP".
# Perhitungan jumlah dipindah ke hitungAlokasiTraksiPPP() (lib/zAlokasiGajiPPP.php) yang juga dipakai proses simpan, jadi jumlah jurnal dihitung server.
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
if ($pesanParam == '' && !preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/', $param['periode'])) {
	$pesanParam = 'Warning : Parameter periode tidak valid.';
}
if ($pesanParam != '') {
	exit($pesanParam);
}
$kodeorg = $param['kodeorg'];

$hs = hitungAlokasiTraksiPPP($kodeorg, $param['periode'], true);
if ($hs['pesan'] != '') {
	exit($hs['pesan']);
}
$tgmulai = $hs['tgmulai'];
$tgsampai = $hs['tgsampai'];

$optOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$periodeQ = addslashes($param['periode']);
$kodeorgQ = addslashes($kodeorg);

#ambil lokasi kerja: biaya kendaraan/bengkel unit ini yang dikirim ke unit lain
$datakend = array();
$adacaco = 0;
$terimacaco = 0;
$tutup2 = '';
foreach (fetchdata("select kodevhc,substr(alokasibiaya,1,4) as alkbyy, sum(jumlah) as hm from " . $dbname . ".vhc_rundt a
	left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
	left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi
	where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' and alokasibiaya!=''
	and jenispekerjaan!=''  and
	kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist where kodetraksi like '" . $kodeorgQ . "%'  and periode= '" . $periodeQ . "')
	group by kodevhc,alkbyy") as $bar) {
	if (isset($optOrg[$bar['alkbyy']])) {
		if (!isset($datakend['Kegiatan'][$bar['kodevhc']][$bar['alkbyy']])) $datakend['Kegiatan'][$bar['kodevhc']][$bar['alkbyy']] = 0;
		$datakend['Kegiatan'][$bar['kodevhc']][$bar['alkbyy']] += $bar['hm'];
		if ($bar['alkbyy'] != $kodeorg) {
			$adacaco++;
		}
	}
}
foreach (fetchdata("select a.kodevhc,substr(b.kodetraksi,1,4) as alkbyy, sum(downtime) as hm from " . $dbname . ".vhc_penggantianht a
	left join " . $dbname . ".vhc_5master_hist b on a.kodevhc=b.kodevhc
	where a.tanggal>='" . $tgmulai . "' and a.tanggal <='" . $tgsampai . "' and  a.kodeorg like  '" . $kodeorgQ . "%' and
	b.kodetraksi not like '" . $kodeorgQ . "%'  and b.periode= '" . $periodeQ . "'  group by kodevhc,alkbyy") as $bar) {
	if (isset($optOrg[$bar['alkbyy']])) {
		if (!isset($datakend['Workshop'][$bar['kodevhc']][$bar['alkbyy']])) $datakend['Workshop'][$bar['kodevhc']][$bar['alkbyy']] = 0;
		$datakend['Workshop'][$bar['kodevhc']][$bar['alkbyy']] += $bar['hm'];
		if ($bar['alkbyy'] != $kodeorg) {
			$adacaco++;
		}
	}
}

$ocJs = function ($v) {
	return htmlspecialchars(addslashes($v), ENT_QUOTES);
};
$labelJenis = array('Kegiatan' => 'Kegiatan kendaraan', 'Workshop' => 'Servis bengkel');
$kirimDariUnit = $datakend; #kendaraan unit ini yang dialokasikan ke unit lain (Kegiatan) / servis kendaraan unit lain di bengkel unit ini (Workshop)

#dari unit lain ke unit anda
$datakend = array();
$cacheKdTrak = array();
foreach (fetchdata("select kodevhc,substr(alokasibiaya,1,4) as alkbyy, sum(jumlah) as hm from " . $dbname . ".vhc_rundt a
	left join " . $dbname . ".vhc_kegiatan b on a.jenispekerjaan=b.kodekegiatan
	left join " . $dbname . ".vhc_runht c on a.notransaksi=c.notransaksi
	where tanggal>='" . $tgmulai . "' and tanggal <='" . $tgsampai . "' and alokasibiaya!=''
	and jenispekerjaan!=''  and
	kodevhc in(select kodevhc from " . $dbname . ".vhc_5master_hist where kodetraksi not like '" . $kodeorgQ . "%')
	group by kodevhc,alkbyy") as $bar) {
	if (!isset($cacheKdTrak[$bar['kodevhc']])) {
		$kdtrak = makeOption($dbname, 'vhc_5master_hist', 'kodevhc,kodetraksi', "kodevhc='" . addslashes($bar['kodevhc']) . "'");
		$cacheKdTrak[$bar['kodevhc']] = isset($kdtrak[$bar['kodevhc']]) ? $kdtrak[$bar['kodevhc']] : '';
	}
	if (isset($optOrg[$bar['alkbyy']])) {
		if (substr($cacheKdTrak[$bar['kodevhc']], 0, 4) != $kodeorg) {
			if ($bar['alkbyy'] == $kodeorg) {
				$k4 = substr($cacheKdTrak[$bar['kodevhc']], 0, 4);
				if (!isset($datakend['Kegiatan'][$bar['kodevhc']][$k4])) $datakend['Kegiatan'][$bar['kodevhc']][$k4] = 0;
				$datakend['Kegiatan'][$bar['kodevhc']][$k4] += $bar['hm'];
				$terimacaco++;
			}
		}
	}
}
foreach (fetchdata("select a.kodevhc,substr(a.kodeorg,1,4) as alkbyy, sum(downtime) as hm from " . $dbname . ".vhc_penggantianht a
	left join " . $dbname . ".vhc_5master_hist b on a.kodevhc=b.kodevhc
	where a.tanggal>='" . $tgmulai . "' and a.tanggal <='" . $tgsampai . "' and  a.kodeorg not like  '" . $kodeorgQ . "%' and
	b.kodetraksi like '" . $kodeorgQ . "%' group by kodevhc,alkbyy") as $bar) {
	if (isset($optOrg[$bar['alkbyy']])) {
		if ($bar['alkbyy'] != $kodeorg) {
			if (!isset($datakend['Workshop'][$bar['kodevhc']][$bar['alkbyy']])) $datakend['Workshop'][$bar['kodevhc']][$bar['alkbyy']] = 0;
			$datakend['Workshop'][$bar['kodevhc']][$bar['alkbyy']] += $bar['hm'];
			$terimacaco++;
		}
	}
}
$terimaDariUnit = $datakend;

#PPP: tabel kiriman biaya antar unit dirapikan: dikelompokkan per unit, nopol dan kendaraan terpisah, status periode unit tujuan, subtotal HM/KM
$tabCaco = "";
$cacheTutup = array();
$cacheNopol = array();
$infoVhc = function ($kodevhc) use (&$cacheNopol) {
	if (!isset($cacheNopol[$kodevhc])) {
		$cacheNopol[$kodevhc] = array(getNopol($kodevhc, 'nopol'), getNopol($kodevhc, 'd'));
	}
	return $cacheNopol[$kodevhc];
};
$statusUnit = function ($unitTujuan) use (&$cacheTutup, $dbname, $periodeQ) {
	if (!isset($cacheTutup[$unitTujuan])) {
		$cacheTutup[$unitTujuan] = 'terbuka';
		$resT = fetchdata("select tutupbuku from " . $dbname . ".setup_periodeakuntansi where periode='" . $periodeQ . "' and kodeorg='" . addslashes($unitTujuan) . "'");
		if (isset($resT[0]['tutupbuku']) && $resT[0]['tutupbuku'] == '1') {
			$cacheTutup[$unitTujuan] = 'tutup';
		}
	}
	return $cacheTutup[$unitTujuan];
};
$styleTabel = "<style>
	.caco-box { background:#fff; border:1px solid #8aa; border-radius:6px; padding:6px 10px; margin:0 0 6px 0; font-size:12px; line-height:1.5; }
	.caco-tutup { color:#b00020; font-weight:bold; }
	.caco-buka { color:#2e7d32; }
	.caco-sub td { font-weight:bold; background:#eef3f8; }
</style><div style='clear:both'></div>";
$bangunCaco = function ($data, $arah) use ($kodeorg, $optOrg, $ocJs, $param, $labelJenis, $infoVhc, $statusUnit, &$tutup2) {
	#kumpulkan baris per unit lawan
	$perUnit = array();
	foreach ($data as $jenis => $vkdvhc) {
		foreach ($vkdvhc as $kodevhc => $vunit) {
			foreach ($vunit as $kdunit => $jlhhm) {
				if ($kdunit != $kodeorg) {
					$perUnit[$kdunit][] = array($jenis, $kodevhc, $jlhhm);
				}
			}
		}
	}
	ksort($perUnit);
	$isi = "";
	$no = 0;
	$grand = 0;
	foreach ($perUnit as $kdunit => $baris) {
		$subtotal = 0;
		$status = $statusUnit(substr($kdunit, 0, 4));
		foreach ($baris as $bt) {
			list($jenis, $kodevhc, $jlhhm) = $bt;
			$no++;
			$subtotal += $jlhhm;
			$iv = $infoVhc($kodevhc);
			$argumen = ($arah == 'kirim')
				? "'" . $ocJs($kodevhc) . "','" . $ocJs($kodeorg) . "','" . $ocJs($kdunit) . "'"
				: "'" . $ocJs($kodevhc) . "','" . $ocJs($kdunit) . "','" . $ocJs($kodeorg) . "'";
			$isi .= "<tr class=rowcontent style=cursor:pointer title='Klik untuk melihat detail transaksi' onclick=\"getdetcaco(" . $argumen . ",'" . $ocJs($param['periode']) . "','" . $ocJs($arah) . "','" . $ocJs($jenis) . "');\">
				<td>" . $no . "</td>
				<td>" . htmlspecialchars($kdunit . " - " . (isset($optOrg[$kdunit]) ? $optOrg[$kdunit] : '')) . "</td>
				<td>" . htmlspecialchars(isset($labelJenis[$jenis]) ? $labelJenis[$jenis] : $jenis) . "</td>
				<td>" . htmlspecialchars($kodevhc) . "</td>
				<td>" . htmlspecialchars($iv[0]) . "</td>
				<td>" . htmlspecialchars($iv[1]) . "</td>
				<td align=right>" . number_format($jlhhm, 2) . "</td>
				<td>" . ($status == 'tutup' ? "<span class=caco-tutup>Periode akuntansi sudah ditutup</span>" : "<span class=caco-buka>Periode terbuka</span>") . "</td>
			</tr>";
		}
		if ($status == 'tutup' && $arah == 'kirim') {
			$tutup2 .= "tutup ";
		}
		$grand += $subtotal;
		$isi .= "<tr class='rowcontent caco-sub'><td></td><td colspan=5>Subtotal " . htmlspecialchars($kdunit) . "</td><td align=right>" . number_format($subtotal, 2) . "</td><td></td></tr>";
	}
	if ($no > 0) {
		$isi .= "<tr class='rowcontent caco-sub'><td></td><td colspan=5>Total HM/KM</td><td align=right>" . number_format($grand, 2) . "</td><td></td></tr>";
	}
	return array($isi, $no, $grand);
};
$headerCaco = "<thead><tr class=rowheader style='text-align:center'><th>No</th><th>Unit</th><th>Jenis</th><th>KodeVhc</th><th>Nopol</th><th>Kendaraan</th><th>HM/KM</th><th>Status Periode Unit</th></tr></thead>";

if ($adacaco > 0) {
	list($isiK, $noK, $grandK) = $bangunCaco($kirimDariUnit, 'kirim');
	$tabCaco .= $styleTabel . "<div class=caco-box><b>Kiriman biaya dari unit " . htmlspecialchars($kodeorg) . " ke unit lain</b> (" . $noK . " baris, total " . number_format($grandK, 2) . " HM/KM)<br>
		<b>Kegiatan kendaraan</b>: kendaraan unit " . htmlspecialchars($kodeorg) . " yang bekerja di unit lain, HM/KM = jam kerja di unit itu. <b>Servis bengkel</b>: kendaraan milik unit lain yang diservis di bengkel " . htmlspecialchars($kodeorg) . ", HM/KM = lama servis.<br>
		Biayanya dikirim ke unit tujuan lewat akun <b>interco/intraco</b> sebanding HM/KM. Harap hubungi KTU unit terkait. Unit tujuan <b>tidak boleh sudah tutup buku</b>, kalau tutup Process dinonaktifkan. Klik baris untuk detail transaksi.</div>
		<table class=sortable cellpadding=5 cellspacing=1 border=0 style=min-width:700px>" . $headerCaco . "<tbody>" . $isiK . "</tbody></table><br>";
}
if ($terimacaco > 0) {
	list($isiT, $noT, $grandT) = $bangunCaco($terimaDariUnit, 'terima');
	$tabCaco .= $styleTabel . "<div class=caco-box><b>Kiriman biaya dari unit lain ke unit " . htmlspecialchars($kodeorg) . "</b> (" . $noT . " baris, total " . number_format($grandT, 2) . " HM/KM)<br>
		<b>Kegiatan kendaraan</b>: kendaraan unit lain yang bekerja untuk unit " . htmlspecialchars($kodeorg) . ". <b>Servis bengkel</b>: kendaraan unit " . htmlspecialchars($kodeorg) . " yang diservis di bengkel unit lain. Biayanya diterima unit " . htmlspecialchars($kodeorg) . " lewat akun interco/intraco. Klik baris untuk detail transaksi.</div>
		<table class=sortable cellpadding=5 cellspacing=1 border=0 style=min-width:700px>" . $headerCaco . "<tbody>" . $isiT . "</tbody></table><br>";
}
#PPP: baris alokasi dari fungsi yang sama dengan proses simpan; baris bernilai nol tidak ditampilkan (proses lama juga melewatinya)
$barisHs = barisAlokasiTraksiPPP($hs);
$jenisKet = array('BYWS' => 'Biaya bengkel ke kendaraan', 'BYWSMM' => 'Biaya bengkel ke stasiun', 'ALKJAM' => 'Biaya kendaraan ke lokasi kerja (per jam)');
$rp = function ($v) {
	return number_format($v, 0);
};
$jamF = function ($v) {
	return number_format($v, 2);
};
$tampil = 0;
$barisNol = 0;
$hitJenis = array('BYWS' => 0, 'BYWSMM' => 0, 'ALKJAM' => 0);
$barisA = "";
$barisB = "";
$tA = array('jam' => 0, 'bagian' => 0, 'pembulatan' => 0);
$tB = array('jam' => 0, 'langsung' => 0, 'bagian' => 0, 'total' => 0, 'dialokasi' => 0, 'sisa' => 0);
foreach ($barisHs as $b) {
	if ($b['jumlah'] == '0') {
		$barisNol++;
		continue;
	}
	$tampil++;
	$hitJenis[$b['jenis']]++;
	$awal = "<tr class=rowcontent id='row" . $tampil . "'>
		<td>" . $tampil . "</td>
		<td id='periode" . $tampil . "'>" . htmlspecialchars($param['periode']) . "</td>
		<td id='kodevhc" . $tampil . "'>" . htmlspecialchars($b['kodevhc']) . "</td>
		<td>" . htmlspecialchars(getNopol($b['kodevhc'])) . "</td>
		<td>" . htmlspecialchars($b['org']) . "</td>
		<td id='jenis" . $tampil . "'>" . $b['jenis'] . "</td>";
	$akhir = "<td hidden class=kolom-sembunyi id='jumlahpembulatan" . $tampil . "' align=right>" . $b['jumlahpembulatan'] . "</td>
		<td hidden class=kolom-sembunyi id='jumlahbiayakendaraan" . $tampil . "'>" . $b['jumlahbiayakendaraan'] . "</td>
		<td hidden class=kolom-sembunyi id='nourut" . $tampil . "'>" . $b['nourut'] . "</td>
	</tr>";
	if ($b['jenis'] == 'ALKJAM') {
		$tB['jam'] += $b['jam'];
		$tB['langsung'] += $b['langsung'];
		$tB['bagian'] += $b['bagian'];
		$tB['total'] += $b['total'];
		$tB['dialokasi'] += $b['dialokasi'];
		$tB['sisa'] += $b['sisa'];
		$barisB .= $awal . "
		<td align=right>" . $jamF($b['jam']) . "</td>
		<td align=right>" . $rp($b['langsung']) . "</td>
		<td align=right>" . $rp($b['bagian']) . "</td>
		<td align=right>" . $rp($b['total']) . "</td>
		<td align=right id='jumlah" . $tampil . "'>" . $b['jumlah'] . "</td>
		<td align=right>" . $rp($b['dialokasi']) . "</td>
		<td align=right>" . $rp($b['sisa']) . "</td>" . $akhir;
	} else {
		$tA['jam'] += $b['jam'];
		$tA['bagian'] += $b['bagian'];
		$tA['pembulatan'] += $b['pembulatanbengkel'];
		$barisA .= $awal . "
		<td align=right>" . $jamF($b['jam']) . "</td>
		<td align=right id='jumlah" . $tampil . "'>" . $b['jumlah'] . "</td>
		<td align=right>" . $rp($b['pembulatanbengkel']) . "</td>
		<td align=right>" . $rp($b['bagian'] + $b['pembulatanbengkel']) . "</td>
		<td colspan=3></td>" . $akhir;
	}
}
#satu tabel (id mytable, supaya Excel memuat semuanya): bagian A bengkel, bagian B kendaraan; urutan baris sama dengan nomor proses
$kolomKiri = "<th>No</th><th>Period</th><th>KodeVhc</th><th>Kendaraan</th><th>Organization</th><th>Type</th>";
$tabelUtama = "";
if ($barisA != '') {
	$tabelUtama .= "<tr class=rowheader><th colspan=13 align=left>A. Biaya bengkel ke kendaraan / stasiun (BYWS, BYWSMM)</th></tr>
		<tr class=rowheader>" . $kolomKiri . "<th>Jam Servis</th><th>Bagian Bengkel (Rp)</th><th>Pembulatan (Rp)</th><th>Total Dialokasikan (Rp)</th><th colspan=3></th></tr>"
		. $barisA . "
		<tr class=rowcontent style='font-weight:bold'><td colspan=6>Total A</td><td align=right>" . $jamF($tA['jam']) . "</td><td align=right>" . $rp($tA['bagian']) . "</td><td align=right>" . $rp($tA['pembulatan']) . "</td><td align=right>" . $rp($tA['bagian'] + $tA['pembulatan']) . "</td><td colspan=3></td></tr>";
}
if ($barisB != '') {
	$tabelUtama .= "<tr class=rowheader><th colspan=13 align=left>B. Biaya kendaraan ke lokasi kerja (ALKJAM)</th></tr>
		<tr class=rowheader>" . $kolomKiri . "<th>Jam Kerja</th><th>Biaya Langsung (Rp)</th><th>Bagian Bengkel (Rp)</th><th>Total Biaya (Rp)</th><th>Biaya per Jam (Rp)</th><th>Dialokasikan (Rp)</th><th>Sisa Pembulatan (Rp)</th></tr>"
		. $barisB . "
		<tr class=rowcontent style='font-weight:bold'><td colspan=6>Total B</td><td align=right>" . $jamF($tB['jam']) . "</td><td align=right>" . $rp($tB['langsung']) . "</td><td align=right>" . $rp($tB['bagian']) . "</td><td align=right>" . $rp($tB['total']) . "</td><td></td><td align=right>" . $rp($tB['dialokasi']) . "</td><td align=right>" . $rp($tB['sisa']) . "</td></tr>";
}
if ($tabelUtama == '') {
	$tabelUtama = "<tr class=rowcontent><td colspan=13 align=center>Tidak ada alokasi traksi/bengkel yang perlu diproses pada periode ini.</td></tr>";
}

#rumusan dengan angka periode ini
$namaVhcPembulatan = ($hs['kendaraanpembulatanbengkel'] !== null) ? htmlspecialchars($hs['kendaraanpembulatanbengkel']) : '-';
$akB = $hs['akunbengkel'];
$akK = $hs['akunkendaraan'];
$pembulatanBengkelTotal = 0;
foreach ($hs['jumlahpembulatanbengkel'] as $vP) {
	$pembulatanBengkelTotal += $vP;
}

echo $tabCaco;
echo "
	<fieldset style='margin-bottom:8px'><legend><b>Informasi dan Rumusan Proses</b></legend>
	<div style='display:flex;flex-wrap:wrap;align-items:stretch;gap:6px;font-size:12px'>
		<div style='flex:1 1 250px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>A. Biaya bengkel ke kendaraan/stasiun</b><br>
			Biaya bengkel unit " . htmlspecialchars($kodeorg) . " periode " . htmlspecialchars($param['periode']) . " = jumlah (debet &minus; kredit) akun <b>" . htmlspecialchars($akB[0]) . " s/d " . htmlspecialchars($akB[1]) . "</b> (parameter WS1), tidak termasuk jurnal alokasi sebelumnya (ALK_KERJA_AB, ALK_BY_WS, ALK_MAINTENANCE) = <b>Rp " . $rp($hs['bybengkel']) . "</b>.<br>
			<b>Bagian per kendaraan/stasiun</b> = &lfloor; jam servis &divide; total jam servis &times; biaya bengkel &rfloor;. Total jam servis = <b>" . $jamF($hs['totaljamservice']) . "</b> jam.<br>
			<b>Pembulatan</b> = biaya bengkel &minus; jumlah semua bagian = <b>Rp " . $rp($pembulatanBengkelTotal) . "</b>, masuk ke yang jam servisnya terbanyak (<b>" . $namaVhcPembulatan . "</b>).</div>
		<div style='flex:1 1 280px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>B. Biaya kendaraan ke lokasi kerja</b><br>
			<b>Biaya langsung</b> = jumlah (debet &minus; kredit) akun <b>" . htmlspecialchars($akK[0]) . " s/d " . htmlspecialchars($akK[1]) . "</b> (parameter LPVHC) kendaraan unit ini periode berjalan, tidak termasuk jurnal alokasi, ditambah biaya servis di bengkel unit lain.<br>
			<b>Total biaya</b> = biaya langsung + bagian bengkel (A).<br>
			<b>Biaya per jam</b> = &lfloor; total biaya &divide; jam kerja kendaraan &rfloor;. <b>Dialokasikan</b> = biaya per jam &times; jam kerja. <b>Sisa pembulatan</b> = total biaya &minus; dialokasikan + pembulatan bengkel.<br>
			Saat Process, total biaya kendaraan dibagi ke setiap lokasi kerja (blok, aset, pabrikasi, unit lain) menurut jam di lokasi itu: &lfloor; jam lokasi &divide; total jam &times; total biaya &rfloor;, sisa pembulatan masuk ke lokasi terakhir.</div>
		<div style='flex:1 1 230px;background:#fff;border:1px solid #8aa;border-radius:6px;padding:6px 8px'><b>Jurnal</b><br>Satu proses per baris (kendaraan atau stasiun). Biaya yang dikirim ke unit lain lewat akun <b>interco/intraco</b>; unit tujuan tidak boleh sudah tutup buku. Kolom <i>Type</i>: BYWS = bengkel ke kendaraan, BYWSMM = bengkel ke stasiun, ALKJAM = kendaraan ke lokasi kerja.</div>
	</div>
	<div style='font-size:12px;margin-top:6px;line-height:1.5'>
		&bull; Terdapat <b>" . $tampil . "</b> baris: " . $hitJenis['BYWS'] . " bengkel-kendaraan, " . $hitJenis['BYWSMM'] . " bengkel-stasiun, " . $hitJenis['ALKJAM'] . " per jam kendaraan" . ($barisNol > 0 ? ". Baris bernilai nol (" . $barisNol . " baris) tidak ditampilkan karena tidak dijurnalkan" : "") . ".<br>
		&bull; Jumlah jurnal dihitung ulang oleh server saat Process, jadi tidak tergantung angka yang tampil di layar.<br>
		<span style='background:#fff3cd;padding:1px 6px;border:1px solid #e0c36a'><b>Perhatian:</b> Process ulang menghapus jurnal alokasi kendaraan yang sama pada periode itu lalu membuatnya lagi. Kalau periode akuntansi sudah tutup buku, prosesnya ditolak.</span>
	</div>
	</fieldset>";

if ($tutup2 != '') {
	echo "<fieldset style=float:left><label style=font-size:14px;color:red;>Ada unit penerima alokasi biaya sudah melakukan tutup buku sehingga proses tidak bisa dilanjutkan,<br> silahkan koordinasikan dengan unit tujuan atau anda rubah transaksi alokasi biaya tersebut.</label></fieldset>
	<div style=clear:both></div>";
}

echo "
	<style>
		.tombolAlkPPP { display:inline-block; padding:7px 22px; margin:0 8px 8px 0; border-radius:6px; font-size:13px; font-weight:bold; cursor:pointer; }
		#btnproses.tombolAlkPPP { background:#2e7d32; color:#fff; border:1px solid #1b5e20; }
		#btnproses.tombolAlkPPP:hover { background:#388e3c; }
		#btnproses.tombolAlkPPP:disabled { background:#9e9e9e; border-color:#757575; color:#eee; cursor:not-allowed; }
		#btnexcel.tombolAlkPPP { background:#fff; color:#2e7d32; border:1px solid #2e7d32; }
		#btnexcel.tombolAlkPPP:hover { background:#e8f5e9; }
	</style>
	<div>
		<button class='tombolAlkPPP' id=btnproses title='Buat jurnal alokasi untuk semua baris di bawah' " . (($tutup2 != '' || $tampil == 0) ? "disabled" : "onclick=prosesAlokasi(1)") . ">&#9654; Process</button>
		<button class='tombolAlkPPP' onclick=exportTableToExcel() id=btnexcel title='Unduh tabel ini ke Excel'>&#11015; Excel</button>
	</div>
	<table class=sortable cellpadding=5 cellspacing=1 border=0 style=min-width:700px id=mytable>
	<tbody>" . $tabelUtama . "</tbody><tfoot></tfoot></table>";
