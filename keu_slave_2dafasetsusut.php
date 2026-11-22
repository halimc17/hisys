<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

#Laporan Daftar Aset dan Penyusutan: per aset, harga perolehan, hasil penyusutan (jurnal terposting), nilai buku (sisa nilai), dan sisa umur.
#Hasil penyusutan dibaca dari jurnal penyusutan Proses Akhir Bulan yang tercatat (keu_jurnaldt: debet dengan kodeasset, noreferensi = kodeasset, keterangan 'Penyusutan [..]');
#status posting jurnal ditampilkan di popup jurnal, tidak dipakai sebagai filter.
$method = checkPostGet('method', '');
if (!in_array($method, array('list', 'excellist', 'pdflist', 'unit', 'jurnal'), true)) {
	exit("Warning : Method tidak dikenal.");
}

#semua unit di bawah satu PT (induk bertingkat)
function scopeOrgAD($root)
{
	global $dbname, $owlPDO;
	$scope = array($root);
	$antri = array($root);
	$stm = $owlPDO->prepare("select kodeorganisasi from " . $dbname . ".organisasi where induk=?");
	while (!empty($antri)) {
		$induk = array_shift($antri);
		$stm->execute(array($induk));
		foreach ($stm->fetchAll(PDO::FETCH_COLUMN) as $k) {
			if (!in_array($k, $scope, true)) {
				$scope[] = $k;
				$antri[] = $k;
			}
		}
	}
	return $scope;
}

#filter yang sudah dibersihkan; keluar dengan pesan bila tidak sah
function filterAD()
{
	global $dbname, $owlPDO;
	$f = array('pt' => '', 'unit' => '', 'periode' => '', 'tipe' => '', 'status' => '', 'cari' => '');
	$pt = trim((string)checkPostGet('pt', ''));
	if ($pt == '' || !preg_match('/^[A-Za-z0-9]{1,10}$/', $pt)) {
		exit("Warning : PT wajib dipilih.");
	}
	$stm = $owlPDO->prepare("select kodeorganisasi from " . $dbname . ".organisasi where kodeorganisasi=? and tipe='PT'");
	$stm->execute(array($pt));
	if (!$stm->fetchColumn()) {
		exit("Warning : PT tidak ditemukan.");
	}
	$f['pt'] = $pt;
	$periode = trim((string)checkPostGet('periode', ''));
	if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periode)) {
		exit("Warning : Periode wajib dipilih.");
	}
	$f['periode'] = $periode;
	$f['scope'] = scopeOrgAD($pt);
	$unit = trim((string)checkPostGet('unit', ''));
	if ($unit != '') {
		if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $unit) || !in_array($unit, $f['scope'], true)) {
			exit("Warning : Unit tidak valid untuk PT ini.");
		}
		$f['unit'] = $unit;
	}
	$tipe = trim((string)checkPostGet('tipe', ''));
	if ($tipe != '') {
		if (!preg_match('/^[A-Za-z0-9]{1,8}$/', $tipe)) {
			exit("Warning : Tipe aset tidak valid.");
		}
		$f['tipe'] = $tipe;
	}
	$status = trim((string)checkPostGet('status', ''));
	if ($status != '') {
		if (!in_array($status, array('0', '1'), true)) {
			exit("Warning : Status tidak valid.");
		}
		$f['status'] = $status;
	}
	$f['cari'] = trim((string)checkPostGet('cari', ''));
	if (strlen($f['cari']) > 40) {
		$f['cari'] = substr($f['cari'], 0, 40);
	}
	$f['awal'] = $periode . '-01';
	$f['akhir'] = date('Y-m-t', strtotime($f['awal']));
	return $f;
}

function infoFilterAD($f)
{
	global $dbname;
	$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
	$nmTipe = makeOption($dbname, 'sdm_5tipeasset', 'kodetipe,namatipe');
	return 'PT: ' . $f['pt'] . ' - ' . (string)@$nmOrg[$f['pt']] .
		' | Unit: ' . ($f['unit'] == '' ? 'Semua' : $f['unit'] . ' - ' . (string)@$nmOrg[$f['unit']]) .
		' | Periode: ' . substr($f['periode'], 5, 2) . '-' . substr($f['periode'], 0, 4) .
		' | Tipe Aset: ' . ($f['tipe'] == '' ? 'Semua' : (string)@$nmTipe[$f['tipe']]) .
		' | Status: ' . ($f['status'] === '1' ? 'Aktif' : ($f['status'] === '0' ? 'Tidak Aktif' : 'Semua')) .
		' | Cari: ' . ($f['cari'] == '' ? '-' : $f['cari']);
}

#bulan berjalan penyusutan sampai akhir periode (awal penyusutan YYYY-MM sampai periode, termasuk keduanya), dibatasi umur
function bulanBerjalanAD($awalSusut, $periode, $umur)
{
	if (!preg_match('/^(\d{4})-(\d{2})$/', (string)$awalSusut, $a) || !preg_match('/^(\d{4})-(\d{2})$/', $periode, $p)) {
		return 0;
	}
	$n = ($p[1] * 12 + $p[2]) - ($a[1] * 12 + $a[2]) + 1;
	if ($n < 0) {
		$n = 0;
	}
	if ($umur > 0 && $n > $umur) {
		$n = (int)$umur;
	}
	return $n;
}

#data per aset dikelompokkan per tipe aset; maksimal 3000 aset
function dataAD($f, &$terpotong)
{
	global $dbname, $owlPDO;
	$tempat = implode(',', array_fill(0, count($f['scope']), '?'));
	$par = $f['scope'];
	$where = " a.kodeorg in (" . $tempat . ") and a.tanggalperolehan<=? and (a.tanggaldisposal is null or a.tanggaldisposal='0000-00-00' or a.tanggaldisposal>?)";
	$par[] = $f['akhir'];
	$par[] = $f['akhir'];
	if ($f['unit'] != '') {
		$where .= " and a.kodeorg=?";
		$par[] = $f['unit'];
	}
	if ($f['tipe'] != '') {
		$where .= " and a.tipeasset=?";
		$par[] = $f['tipe'];
	}
	if ($f['status'] !== '') {
		$where .= " and a.status=?";
		$par[] = (int)$f['status'];
	}
	if ($f['cari'] != '') {
		$like = '%' . str_replace(array('%', '_'), array('\\%', '\\_'), $f['cari']) . '%';
		$where .= " and (a.kodeasset like ? or a.namasset like ?)";
		$par[] = $like;
		$par[] = $like;
	}
	try {
		$stm = $owlPDO->prepare("select a.kodeasset,a.kodeorg,a.tipeasset,a.tanggalperolehan,a.namasset,a.hargaperolehan,a.jlhblnpenyusutan,a.bulanan,a.awalpenyusutan,t.namatipe
			from " . $dbname . ".sdm_daftarasset a left join " . $dbname . ".sdm_5tipeasset t on t.kodetipe=a.tipeasset
			where" . $where . " order by t.namatipe, a.tipeasset, a.kodeasset limit 3001");
		$stm->execute($par);
		$res = $stm->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $e) {
		exit("Gagal : Data aset tidak bisa dibaca.");
	}
	$terpotong = count($res) > 3000;
	$res = array_slice($res, 0, 3000);

	#hasil penyusutan terposting dari jurnal: akumulasi sampai akhir periode dan penyusutan periode itu
	$jurnal = array();
	$kode = array();
	foreach ($res as $r) {
		$kode[] = $r['kodeasset'];
	}
	foreach (array_chunk($kode, 500) as $potong) {
		$tp = implode(',', array_fill(0, count($potong), '?'));
		$p2 = array($f['akhir'], $f['awal'], $f['akhir'], $f['akhir']);
		$stm = $owlPDO->prepare("select kodeasset,
				sum(case when tanggal<=? then jumlah else 0 end) as akum,
				sum(case when tanggal>=? and tanggal<=? then jumlah else 0 end) as susut
			from " . $dbname . ".keu_jurnaldt force index(kodeasset)
			where tanggal<=? and jumlah>0 and noreferensi=kodeasset and keterangan like 'Penyusutan [%' and kodeasset in (" . $tp . ")
			group by kodeasset");
		$stm->execute(array_merge($p2, $potong));
		foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $r) {
			$jurnal[$r['kodeasset']] = $r;
		}
	}

	$grup = array();
	foreach ($res as $r) {
		$tp = trim($r['tipeasset']);
		$nama = trim((string)$r['namatipe']) != '' ? trim($r['namatipe']) : $tp;
		if (!isset($grup[$tp])) {
			$grup[$tp] = array('nama' => $nama, 'baris' => array());
		}
		$harga = (float)$r['hargaperolehan'];
		$umur = (int)$r['jlhblnpenyusutan'];
		$bulanan = (float)$r['bulanan'];
		$akum = isset($jurnal[$r['kodeasset']]) ? (float)$jurnal[$r['kodeasset']]['akum'] : 0;
		$susut = isset($jurnal[$r['kodeasset']]) ? (float)$jurnal[$r['kodeasset']]['susut'] : 0;
		$berjalan = bulanBerjalanAD($r['awalpenyusutan'], $f['periode'], $umur);
		$grup[$tp]['baris'][] = array(
			'kode' => $r['kodeasset'], 'nama' => $r['namasset'], 'unit' => $r['kodeorg'], 'tgl' => $r['tanggalperolehan'],
			'harga' => $harga, 'umur' => $umur, 'bulanan' => $bulanan, 'susut' => $susut, 'akum' => $akum,
			'nilai' => $harga - $akum, 'sisa' => max(0, $umur - $berjalan)
		);
	}
	return $grup;
}

function totalAD($baris)
{
	$t = array('harga' => 0, 'bulanan' => 0, 'susut' => 0, 'akum' => 0, 'nilai' => 0);
	foreach ($baris as $b) {
		foreach ($t as $k => $v) {
			$t[$k] += $b[$k];
		}
	}
	return $t;
}

function angkaAD($v)
{
	return abs($v) < 0.5 ? '-' : number_format($v, 0);
}

function tglAD($t)
{
	return ($t == '' || $t == '0000-00-00') ? '' : date('d-m-Y', strtotime($t));
}

$kolomJudul = array('No', 'Kode Aset', 'Nama Aset', 'Unit', 'Tgl Perolehan', 'Harga Perolehan', 'Umur (Bln)', 'Susut / Bln', 'Susut Periode', 'Akumulasi Susut', 'Nilai Buku (Sisa Nilai)', 'Sisa Umur (Bln)');

switch ($method) {
	case 'unit':
		#pilihan unit di bawah PT yang dipilih (untuk filter bertingkat)
		$pt = trim((string)checkPostGet('pt', ''));
		if (!preg_match('/^[A-Za-z0-9]{1,10}$/', $pt)) {
			exit("<option value=''>Semua</option>");
		}
		$nmOrg = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
		$opt = "<option value=''>Semua Unit</option>";
		foreach (scopeOrgAD($pt) as $k) {
			if ($k != $pt) {
				$opt .= "<option value='" . htmlspecialchars($k, ENT_QUOTES) . "'>" . htmlspecialchars($k . ' - ' . (string)@$nmOrg[$k]) . "</option>";
			}
		}
		echo $opt;
		break;

	case 'jurnal':
		#popup jurnal penyusutan satu aset sampai akhir periode laporan
		$f = filterAD();
		$kodeAset = trim((string)checkPostGet('kodeasset', ''));
		if (!preg_match('/^[A-Za-z0-9._\-\/]{1,40}$/', $kodeAset)) {
			exit("Warning : Kode aset tidak valid.");
		}
		$tempat = implode(',', array_fill(0, count($f['scope']), '?'));
		$stm = $owlPDO->prepare("select a.kodeasset,a.kodeorg,a.namasset,a.tanggalperolehan,a.hargaperolehan,a.jlhblnpenyusutan,a.bulanan,t.namatipe
			from " . $dbname . ".sdm_daftarasset a left join " . $dbname . ".sdm_5tipeasset t on t.kodetipe=a.tipeasset
			where a.kodeasset=? and a.kodeorg in (" . $tempat . ")");
		$stm->execute(array_merge(array($kodeAset), $f['scope']));
		$aset = $stm->fetch(PDO::FETCH_ASSOC);
		if (!$aset) {
			exit("Warning : Aset tidak ditemukan pada PT ini.");
		}
		$stm = $owlPDO->prepare("select d.nojurnal,d.tanggal,d.nourut,d.noakun,d.keterangan,d.jumlah,h.posting
			from " . $dbname . ".keu_jurnaldt d force index(kodeasset) left join " . $dbname . ".keu_jurnalht h on h.nojurnal=d.nojurnal
			where d.kodeasset=? and d.noreferensi=d.kodeasset and d.keterangan like 'Penyusutan [%' and d.tanggal<=?
			order by d.tanggal, d.nojurnal, d.nourut limit 601");
		$stm->execute(array($kodeAset, $f['akhir']));
		$jr = $stm->fetchAll(PDO::FETCH_ASSOC);
		$lebih = count($jr) > 600;
		$jr = array_slice($jr, 0, 600);
		$nmAkun = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
		$totD = 0;
		$totK = 0;
		$bar = '';
		$no = 0;
		foreach ($jr as $r) {
			$no++;
			$d = $r['jumlah'] > 0 ? $r['jumlah'] : 0;
			$k = $r['jumlah'] < 0 ? -$r['jumlah'] : 0;
			$totD += $d;
			$totK += $k;
			$bar .= "<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td align=center nowrap>" . tglAD($r['tanggal']) . "</td>
				<td nowrap>" . htmlspecialchars($r['nojurnal']) . "</td>
				<td nowrap>" . htmlspecialchars($r['noakun']) . "</td>
				<td>" . htmlspecialchars((string)@$nmAkun[$r['noakun']]) . "</td>
				<td align=right>" . angkaAD($d) . "</td>
				<td align=right>" . angkaAD($k) . "</td>
				<td align=center>" . ($r['posting'] == 1 ? 'Sudah' : 'Belum') . "</td>
				<td>" . htmlspecialchars($r['keterangan']) . "</td>
			</tr>";
		}
		if ($no == 0) {
			$bar = "<tr class=rowcontent><td colspan=9 align=center>Belum ada jurnal penyusutan yang tercatat untuk aset ini sampai " . htmlspecialchars(substr($f['periode'], 5, 2) . '-' . substr($f['periode'], 0, 4)) . ".</td></tr>";
		} else {
			$bar .= "<tr class=rowcontent style='font-weight:bold'><td colspan=5 align=right>Total</td><td align=right>" . angkaAD($totD) . "</td><td align=right>" . angkaAD($totK) . "</td><td colspan=2></td></tr>";
		}
		echo "<div style='font-size:12px;margin-bottom:6px'><b>" . htmlspecialchars($aset['kodeasset']) . "</b> - " . htmlspecialchars($aset['namasset']) . " | " . htmlspecialchars((string)$aset['namatipe']) . " | Unit " . htmlspecialchars($aset['kodeorg']) . "<br>
			Perolehan " . tglAD($aset['tanggalperolehan']) . ": Rp " . angkaAD((float)$aset['hargaperolehan']) . " | Umur " . (int)$aset['jlhblnpenyusutan'] . " bulan | Susut / bulan Rp " . angkaAD((float)$aset['bulanan']) . "<br>
			Jurnal penyusutan sampai " . htmlspecialchars(substr($f['periode'], 5, 2) . '-' . substr($f['periode'], 0, 4)) . ": Rp " . angkaAD($totD) . "</div>
			<div style='max-height:340px;overflow:auto'><table class=sortable cellspacing=1 cellpadding=4 border=0 style='width:100%'>
			<thead><tr class=rowheader><td align=center>No</td><td align=center>Tanggal</td><td align=center>No. Jurnal</td><td align=center>No. Akun</td><td align=center>Nama Akun</td><td align=center>Debet</td><td align=center>Kredit</td><td align=center>Posting</td><td align=center>Keterangan</td></tr></thead>
			<tbody>" . $bar . "</tbody></table></div>" . ($lebih ? "<div style='font-size:11px;color:#b42318'>Ditampilkan 600 baris pertama.</div>" : '');
		break;

	case 'list':
		$f = filterAD();
		$terpotong = false;
		$grup = dataAD($f, $terpotong);
		$jml = 0;
		foreach ($grup as $g) {
			$jml += count($g['baris']);
		}
		$out = "<div style='font-size:12px;margin:4px 0'>" . htmlspecialchars(infoFilterAD($f)) . "</div>";
		if ($terpotong) {
			$out .= "<div style='font-size:12px;color:#b42318;margin:2px 0'>Ditampilkan 3000 aset pertama; persempit filter untuk data selengkapnya.</div>";
		}
		$out .= "<table class=sortable cellspacing=1 cellpadding=4 border=0 style='width:100%'><thead><tr class=rowheader>";
		foreach ($kolomJudul as $i => $j) {
			$out .= "<td align=center>" . htmlspecialchars($j) . "</td>";
		}
		$out .= "</tr></thead><tbody>";
		if ($jml == 0) {
			$out .= "<tr class=rowcontent><td colspan=12 align=center>Tidak ada data aset untuk filter ini.</td></tr>";
		}
		$grand = array('harga' => 0, 'bulanan' => 0, 'susut' => 0, 'akum' => 0, 'nilai' => 0);
		foreach ($grup as $g) {
			$out .= "<tr class=rowcontent style='font-weight:bold;background:#d9e8fb'><td colspan=12>" . htmlspecialchars($g['nama']) . "</td></tr>";
			$no = 0;
			foreach ($g['baris'] as $b) {
				$no++;
				$out .= "<tr class=rowcontent style='cursor:pointer' title='Klik untuk melihat jurnal penyusutan' onclick=\"jurnalAD('" . htmlspecialchars($b['kode'], ENT_QUOTES) . "',event)\">
					<td align=center>" . $no . "</td>
					<td nowrap style='color:#1a4fa0;text-decoration:underline'>" . htmlspecialchars($b['kode']) . "</td>
					<td>" . htmlspecialchars($b['nama']) . "</td>
					<td align=center>" . htmlspecialchars($b['unit']) . "</td>
					<td align=center nowrap>" . tglAD($b['tgl']) . "</td>
					<td align=right>" . angkaAD($b['harga']) . "</td>
					<td align=center>" . ($b['umur'] > 0 ? $b['umur'] : '-') . "</td>
					<td align=right>" . angkaAD($b['bulanan']) . "</td>
					<td align=right>" . angkaAD($b['susut']) . "</td>
					<td align=right>" . angkaAD($b['akum']) . "</td>
					<td align=right>" . angkaAD($b['nilai']) . "</td>
					<td align=center>" . $b['sisa'] . "</td>
				</tr>";
			}
			$t = totalAD($g['baris']);
			foreach ($grand as $k => $v) {
				$grand[$k] += $t[$k];
			}
			$out .= "<tr class=rowcontent style='font-weight:bold'><td colspan=5 align=right>Subtotal " . htmlspecialchars($g['nama']) . " (" . count($g['baris']) . " aset)</td>
				<td align=right>" . angkaAD($t['harga']) . "</td><td></td><td align=right>" . angkaAD($t['bulanan']) . "</td>
				<td align=right>" . angkaAD($t['susut']) . "</td><td align=right>" . angkaAD($t['akum']) . "</td>
				<td align=right>" . angkaAD($t['nilai']) . "</td><td></td></tr>";
		}
		if ($jml > 0) {
			$out .= "<tr class=rowcontent style='font-weight:bold;background:#eef3fa'><td colspan=5 align=right>TOTAL (" . $jml . " aset)</td>
				<td align=right>" . angkaAD($grand['harga']) . "</td><td></td><td align=right>" . angkaAD($grand['bulanan']) . "</td>
				<td align=right>" . angkaAD($grand['susut']) . "</td><td align=right>" . angkaAD($grand['akum']) . "</td>
				<td align=right>" . angkaAD($grand['nilai']) . "</td><td></td></tr>";
		}
		$out .= "</tbody></table>
			<div style='font-size:11px;color:#555;margin-top:4px'>Klik baris aset untuk melihat jurnal penyusutannya. Susut Periode dan Akumulasi Susut = penyusutan yang sudah tercatat di jurnal sampai akhir periode. Nilai Buku = Harga Perolehan - Akumulasi Susut.</div>";
		echo $out;
		break;

	case 'excellist':
		include_once('lib/HtmlExcel.php');
		$f = filterAD();
		$terpotong = false;
		$grup = dataAD($f, $terpotong);
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		$kol = 12;
		$stream = "<table>
			<tr><td colspan=" . $kol . "><b>" . htmlspecialchars($hdpt['nama']) . "</b></td></tr>
			<tr><td colspan=" . $kol . "><b>DAFTAR ASET DAN PENYUSUTAN</b></td></tr>
			<tr><td colspan=" . $kol . ">" . htmlspecialchars(infoFilterAD($f) . ($terpotong ? ' | Catatan: ditampilkan 3000 aset pertama' : '')) . "</td></tr>
			<tr><td colspan=" . $kol . ">Ditarik oleh " . htmlspecialchars($_SESSION['empl']['name']) . " (" . htmlspecialchars($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
			<tr><td colspan=" . $kol . ">&nbsp;</td></tr></table>";
		$stream .= "<table class=sortable cellspacing=1 cellpadding=3 border=1><thead><tr>";
		foreach ($kolomJudul as $j) {
			$stream .= "<th align=center bgcolor=#CCCCCC>" . htmlspecialchars($j) . "</th>";
		}
		$stream .= "</tr></thead><tbody>";
		$jml = 0;
		$grand = array('harga' => 0, 'bulanan' => 0, 'susut' => 0, 'akum' => 0, 'nilai' => 0);
		foreach ($grup as $g) {
			$stream .= "<tr><td colspan=" . $kol . " bgcolor=#D9E8FB><b>" . htmlspecialchars($g['nama']) . "</b></td></tr>";
			$no = 0;
			foreach ($g['baris'] as $b) {
				$no++;
				$jml++;
				$stream .= "<tr>
					<td align=center>" . $no . "</td>
					<td>" . htmlspecialchars($b['kode']) . "</td>
					<td>" . htmlspecialchars($b['nama']) . "</td>
					<td align=center>" . htmlspecialchars($b['unit']) . "</td>
					<td align=center>" . tglAD($b['tgl']) . "</td>
					<td align=right>" . round($b['harga'], 2) . "</td>
					<td align=center>" . $b['umur'] . "</td>
					<td align=right>" . round($b['bulanan'], 2) . "</td>
					<td align=right>" . round($b['susut'], 2) . "</td>
					<td align=right>" . round($b['akum'], 2) . "</td>
					<td align=right>" . round($b['nilai'], 2) . "</td>
					<td align=center>" . $b['sisa'] . "</td>
				</tr>";
			}
			$t = totalAD($g['baris']);
			foreach ($grand as $k => $v) {
				$grand[$k] += $t[$k];
			}
			$stream .= "<tr><td colspan=5 align=right><b>Subtotal " . htmlspecialchars($g['nama']) . " (" . count($g['baris']) . " aset)</b></td>
				<td align=right><b>" . round($t['harga'], 2) . "</b></td><td></td><td align=right><b>" . round($t['bulanan'], 2) . "</b></td>
				<td align=right><b>" . round($t['susut'], 2) . "</b></td><td align=right><b>" . round($t['akum'], 2) . "</b></td>
				<td align=right><b>" . round($t['nilai'], 2) . "</b></td><td></td></tr>";
		}
		if ($jml == 0) {
			$stream .= "<tr><td colspan=" . $kol . " align=center>Tidak ada data aset untuk filter ini</td></tr>";
		} else {
			$stream .= "<tr><td colspan=5 align=right><b>TOTAL (" . $jml . " aset)</b></td>
				<td align=right><b>" . round($grand['harga'], 2) . "</b></td><td></td><td align=right><b>" . round($grand['bulanan'], 2) . "</b></td>
				<td align=right><b>" . round($grand['susut'], 2) . "</b></td><td align=right><b>" . round($grand['akum'], 2) . "</b></td>
				<td align=right><b>" . round($grand['nilai'], 2) . "</b></td><td></td></tr>";
		}
		$stream .= "</tbody></table>";
		$xls = new HtmlExcel();
		$xls->addSheet("DaftarAsetSusut", $stream);
		$xls->headers("DaftarAsetPenyusutan_" . $f['pt'] . "_" . $f['periode'] . "_" . date('YmdHis') . ".xls");
		echo $xls->buildFile();
		exit;

	case 'pdflist':
		require_once('lib/fpdf.php');
		$f = filterAD();
		$terpotong = false;
		$grup = dataAD($f, $terpotong);
		$hdpt = setheadreport($_SESSION['empl']['kodeorganisasi'], $_SESSION['empl']['kodeorganisasi']);
		if (!class_exists('PDFAsetSusut')) {
			class PDFAsetSusut extends FPDF
			{
				public $colW = array();
				public $judul = array();
				public $kopNama = '';
				public $kopLogo = '';
				public $kopInfo = '';
				public $kopDitarik = '';

				function Header()
				{
					$lebar = $this->w - $this->lMargin - $this->rMargin;
					if ($this->kopLogo != '' && file_exists($this->kopLogo)) {
						$this->Image($this->kopLogo, $this->lMargin, $this->tMargin, 18);
					}
					$this->SetFont('Arial', 'B', 11);
					$this->SetXY($this->lMargin + 22, $this->tMargin + 2);
					$this->Cell(160, 6, utf8_decode($this->kopNama), 0, 1, 'L');
					$this->SetY($this->tMargin + 15);
					$this->SetFont('Arial', 'B', 13);
					$this->Cell($lebar, 7, 'DAFTAR ASET DAN PENYUSUTAN', 0, 1, 'C');
					$this->SetFont('Arial', '', 8);
					$this->Cell($lebar, 5, utf8_decode($this->kopInfo), 0, 1, 'C');
					$this->Cell($lebar, 5, utf8_decode($this->kopDitarik), 0, 1, 'C');
					$this->Ln(1);
					$this->SetFillColor(220, 220, 220);
					$this->SetFont('Arial', 'B', 7);
					foreach ($this->judul as $i => $j) {
						$this->Cell($this->colW[$i], 8, utf8_decode($j), 1, 0, 'C', true);
					}
					$this->Ln();
				}

				function Footer()
				{
					$this->SetY(-12);
					$this->SetFont('Arial', 'I', 7);
					$this->Cell(0, 8, 'Halaman ' . $this->PageNo() . ' / {nb}', 0, 0, 'R');
				}

				function fit($txt, $w, $f = 7)
				{
					$txt = utf8_decode($txt);
					$this->SetFont('Arial', '', $f);
					if ($this->GetStringWidth($txt) <= $w - 2) {
						return $txt;
					}
					while (strlen($txt) > 1 && $this->GetStringWidth($txt . '...') > $w - 2) {
						$txt = substr($txt, 0, -1);
					}
					return rtrim($txt) . '...';
				}
			}
		}
		$pdf = new PDFAsetSusut('L', 'mm', 'A4');
		$pdf->kopNama = $hdpt['nama'];
		$pdf->kopLogo = isset($hdpt['logo']) ? $hdpt['logo'] : '';
		$pdf->kopInfo = infoFilterAD($f) . ($terpotong ? ' | 3000 aset pertama' : '');
		$pdf->kopDitarik = 'Ditarik oleh ' . $_SESSION['empl']['name'] . ' (' . $_SESSION['standard']['username'] . ') pada ' . date('d-m-Y H:i:s');
		$pdf->judul = array('No', 'Kode Aset', 'Nama Aset', 'Unit', 'Tgl Perolehan', 'Harga Perolehan', 'Umur (Bln)', 'Susut / Bln', 'Susut Periode', 'Akumulasi Susut', 'Nilai Buku', 'Sisa Umur');
		$pdf->AliasNbPages();
		$pdf->SetAutoPageBreak(false, 16);
		$pdf->colW = array(8, 28, 68, 14, 20, 25, 10, 18, 20, 25, 25, 12);
		$pdf->AddPage();
		$h = 6;
		$c = $pdf->colW;
		$cekHal = function () use ($pdf, $h) {
			if ($pdf->GetY() + $h > $pdf->h - $pdf->bMargin) {
				$pdf->AddPage();
			}
		};
		$jml = 0;
		$grand = array('harga' => 0, 'bulanan' => 0, 'susut' => 0, 'akum' => 0, 'nilai' => 0);
		foreach ($grup as $g) {
			$cekHal();
			$pdf->SetFont('Arial', 'B', 8);
			$pdf->SetFillColor(217, 232, 251);
			$pdf->Cell(array_sum($c), $h, utf8_decode($g['nama']), 1, 1, 'L', true);
			$no = 0;
			foreach ($g['baris'] as $b) {
				$no++;
				$jml++;
				$cekHal();
				$pdf->SetFont('Arial', '', 7);
				$pdf->Cell($c[0], $h, $no, 1, 0, 'C');
				$pdf->Cell($c[1], $h, $pdf->fit($b['kode'], $c[1]), 1, 0, 'L');
				$pdf->Cell($c[2], $h, $pdf->fit($b['nama'], $c[2]), 1, 0, 'L');
				$pdf->Cell($c[3], $h, $pdf->fit($b['unit'], $c[3]), 1, 0, 'C');
				$pdf->Cell($c[4], $h, tglAD($b['tgl']), 1, 0, 'C');
				$pdf->Cell($c[5], $h, angkaAD($b['harga']), 1, 0, 'R');
				$pdf->Cell($c[6], $h, $b['umur'] > 0 ? $b['umur'] : '-', 1, 0, 'C');
				$pdf->Cell($c[7], $h, angkaAD($b['bulanan']), 1, 0, 'R');
				$pdf->Cell($c[8], $h, angkaAD($b['susut']), 1, 0, 'R');
				$pdf->Cell($c[9], $h, angkaAD($b['akum']), 1, 0, 'R');
				$pdf->Cell($c[10], $h, angkaAD($b['nilai']), 1, 0, 'R');
				$pdf->Cell($c[11], $h, $b['sisa'], 1, 1, 'C');
			}
			$t = totalAD($g['baris']);
			foreach ($grand as $k => $v) {
				$grand[$k] += $t[$k];
			}
			$cekHal();
			$pdf->SetFont('Arial', 'B', 7);
			$pdf->Cell(array_sum(array_slice($c, 0, 5)), $h, utf8_decode('Subtotal ' . $g['nama'] . ' (' . count($g['baris']) . ' aset)'), 1, 0, 'R');
			$pdf->Cell($c[5], $h, angkaAD($t['harga']), 1, 0, 'R');
			$pdf->Cell($c[6], $h, '', 1, 0, 'C');
			$pdf->Cell($c[7], $h, angkaAD($t['bulanan']), 1, 0, 'R');
			$pdf->Cell($c[8], $h, angkaAD($t['susut']), 1, 0, 'R');
			$pdf->Cell($c[9], $h, angkaAD($t['akum']), 1, 0, 'R');
			$pdf->Cell($c[10], $h, angkaAD($t['nilai']), 1, 0, 'R');
			$pdf->Cell($c[11], $h, '', 1, 1, 'C');
		}
		if ($jml == 0) {
			$pdf->Cell(array_sum($c), $h, 'Tidak ada data aset untuk filter ini', 1, 1, 'C');
		} else {
			$cekHal();
			$pdf->SetFont('Arial', 'B', 7);
			$pdf->SetFillColor(238, 243, 250);
			$pdf->Cell(array_sum(array_slice($c, 0, 5)), $h, 'TOTAL (' . $jml . ' aset)', 1, 0, 'R', true);
			$pdf->Cell($c[5], $h, angkaAD($grand['harga']), 1, 0, 'R', true);
			$pdf->Cell($c[6], $h, '', 1, 0, 'C', true);
			$pdf->Cell($c[7], $h, angkaAD($grand['bulanan']), 1, 0, 'R', true);
			$pdf->Cell($c[8], $h, angkaAD($grand['susut']), 1, 0, 'R', true);
			$pdf->Cell($c[9], $h, angkaAD($grand['akum']), 1, 0, 'R', true);
			$pdf->Cell($c[10], $h, angkaAD($grand['nilai']), 1, 0, 'R', true);
			$pdf->Cell($c[11], $h, '', 1, 1, 'C', true);
		}
		$pdf->Output();
		exit;
}
