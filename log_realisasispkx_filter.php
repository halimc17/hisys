<?php
#filter dan status list BAPP Kontraktor (SPK), dipakai list, Excel, dan PDF supaya hasilnya selalu sama

#batas data: unit sesuai detail akses user dan bukan SPK jenis PO/SO, beli TBS, jual TBS
function spkScope()
{
	global $dbname;
	return " and kodeorg IN (" . getOrgDetail(2) . ") and nopengajuan in (select notransaksi from " . $dbname . ".lgl_pengajuanspkht where jenis not in ('PO/SO','BELITBS','JUALTBS'))";
}

#pilihan status untuk filter (kode => teks)
function spkOpsiStatusPosting()
{
	return array(
		'belumajuan' => 'Belum Diajukan',
		'belumposting' => 'Belum Diposting',
		'posted' => 'Posted',
		'bermasalah' => 'Jurnal Bermasalah',
		'kosong' => 'Belum Ada BAPP'
	);
}
function spkOpsiStatusTagihan()
{
	return array(
		'belumtagih' => 'Belum Tagih',
		'belumbayar' => 'Belum Dibayar',
		'sebagian' => 'Sebagian Dibayar',
		'lunas' => 'Lunas'
	);
}

#hasil: where = tambahan "and ..." untuk query log_spkht, info = keterangan filter untuk kop, error = pesan bila filter tidak valid,
#statuspos/statustag = filter status (dihitung setelah data diambil)
function spkFilter($p)
{
	global $dbname;
	$g = function ($k) use ($p) {
		return isset($p[$k]) && is_string($p[$k]) ? trim($p[$k]) : '';
	};
	$where = '';
	$info = array();
	$error = '';

	if ($g('notransaksicr') != '') {
		$where .= " and notransaksi like '%" . addslashes($g('notransaksicr')) . "%'";
		$info[] = 'No. Transaksi: ' . $g('notransaksicr');
	}
	if ($g('unitcr') != '') {
		$where .= " and kodeorg='" . addslashes($g('unitcr')) . "'";
		$info[] = 'Unit: ' . $g('unitcr');
	}
	if ($g('koderekanancr') != '') {
		$where .= " and koderekanan in (select supplierid from " . $dbname . ".log_5supplier where namasupplier like '%" . addslashes($g('koderekanancr')) . "%')";
		$info[] = 'Rekanan: ' . $g('koderekanancr');
	}
	if ($g('subunitcr') != '') {
		$where .= " and divisi like '%" . addslashes($g('subunitcr')) . "%'";
		$info[] = 'Sub Unit: ' . $g('subunitcr');
	}
	if ($g('periodecr') != '' && preg_match('/^\d{4}-\d{2}$/', $g('periodecr'))) {
		$where .= " and left(tanggal,7)='" . $g('periodecr') . "'";
		$info[] = 'Periode: ' . $g('periodecr');
	}
	if ($g('tglcr') != '' && $g('tglsampaicr') == '') {
		$where .= " and tanggal='" . tanggalsystemn($g('tglcr')) . "'";
		$info[] = 'Tanggal: ' . $g('tglcr');
	} elseif ($g('tglsampaicr') != '') {
		$where .= " and tanggal>='" . tanggalsystemn($g('tglcr')) . "' and tanggal<='" . tanggalsystemn($g('tglsampaicr')) . "'";
		$info[] = 'Tanggal: ' . $g('tglcr') . ' s/d ' . $g('tglsampaicr');
	}
	if ($g('tglsampaicr') != '' && $g('tglcr') == '') {
		$error = "warning : Jika tanggal sampai terisi maka tanggal dari nya harus terisi!!! ";
	}

	$statuspos = $g('statusposcr');
	$opsiPos = spkOpsiStatusPosting();
	if (!isset($opsiPos[$statuspos])) {
		$statuspos = '';
	} else {
		$info[] = 'Status Posting: ' . $opsiPos[$statuspos];
	}
	$statustag = $g('statustagcr');
	$opsiTag = spkOpsiStatusTagihan();
	if (!isset($opsiTag[$statustag])) {
		$statustag = '';
	} else {
		$info[] = 'Status Tagihan: ' . $opsiTag[$statustag];
	}

	return array('where' => $where, 'info' => implode(' | ', $info), 'error' => $error, 'statuspos' => $statuspos, 'statustag' => $statustag);
}

#angka status BAPP, tagihan, dan pembayaran untuk sekumpulan SPK (satu kali query per kelompok, bukan per baris)
function spkStatusBatch($rows)
{
	global $dbname;
	$hasil = array();
	$daftar = array();
	foreach ($rows as $r) {
		$daftar[] = "'" . addslashes($r['notransaksi']) . "'";
		$hasil[$r['notransaksi']] = array('realisasi' => 0, 'belumajuan' => 0, 'belumposting' => 0, 'anomali' => 0, 'sudah' => 0, 'tagihan' => 0, 'bayar' => 0);
	}
	foreach (array_chunk($daftar, 400) as $chunk) {
		$in = implode(',', $chunk);
		#jurnal dianggap ada bila ditemukan di ledger (keu_jurnaldt.nodok = keterangan BAPP)
		$ada = "exists (select 1 from " . $dbname . ".keu_jurnaldt d where d.nodok=b.keterangan)";
		$sql = "select b.notransaksi,
			sum(case when b.statusjurnal='1' and " . $ada . " then b.jumlahrealisasi else 0 end) as realisasi,
			count(distinct case when b.statuspengajuan='0' then b.keterangan end) as belumajuan,
			count(distinct case when b.statuspengajuan='1' and b.statusjurnal='0' then b.keterangan end) as belumposting,
			count(distinct case when b.statuspengajuan='1' and b.statusjurnal='1' and not " . $ada . " then b.keterangan end) as anomali,
			count(distinct case when b.statuspengajuan='1' and b.statusjurnal='1' and " . $ada . " then b.keterangan end) as sudah
			from " . $dbname . ".log_baspk b where b.notransaksi in (" . $in . ") group by b.notransaksi";
		foreach (fetchData($sql) as $r) {
			foreach (array('realisasi', 'belumajuan', 'belumposting', 'anomali', 'sudah') as $k) {
				$hasil[$r['notransaksi']][$k] = $r[$k] == '' ? 0 : $r[$k];
			}
		}
		foreach (fetchData("select nopo,sum(nilaiinvoice) as total from " . $dbname . ".keu_tagihanht where nopo in (" . $in . ") group by nopo") as $r) {
			$hasil[$r['nopo']]['tagihan'] = $r['total'] == '' ? 0 : $r['total'];
		}
		foreach (fetchData("select nodok,sum(jumlah) as total from " . $dbname . ".keu_kasbankdtht_vw where nodok in (" . $in . ") and jumlah>0 group by nodok") as $r) {
			$hasil[$r['nodok']]['bayar'] = $r['total'] == '' ? 0 : $r['total'];
		}
	}
	return $hasil;
}

#status posting dan status tagihan satu SPK: teks berwarna (html) dan kode untuk filter
function spkStatusRow($val, $st)
{
	$baris = array();
	$kodePos = array();
	if ($st['anomali'] > 0) {
		$baris[] = "<span style='color:red;font-weight:bold' title='statusjurnal=1 tapi jurnal tidak ditemukan di ledger'>Jurnal Bermasalah (" . $st['anomali'] . ")</span>";
		$kodePos[] = 'bermasalah';
	}
	if ($st['belumajuan'] > 0) {
		$baris[] = "<span style='color:gray'>Belum Diajukan (" . $st['belumajuan'] . ")</span>";
		$kodePos[] = 'belumajuan';
	}
	if ($st['belumposting'] > 0) {
		$baris[] = "<span style='color:orange'>Belum Diposting (" . $st['belumposting'] . ")</span>";
		$kodePos[] = 'belumposting';
	}
	if ($st['sudah'] > 0) {
		$baris[] = "<span style='color:green'>Posted (" . $st['sudah'] . ")</span>";
		$kodePos[] = 'posted';
	}
	if (count($baris) == 0) {
		$kodePos[] = 'kosong';
	}
	$statusposting = (count($baris) > 0) ? implode("<br>", $baris) : "<span style='color:gray'>Belum Ada BAPP</span>";

	$totalTagihan = $st['tagihan'];
	$totalBayar = $st['bayar'];
	$persenTertagih = ($val['nilaikontrak'] > 0) ? min(($totalTagihan / $val['nilaikontrak']) * 100, 100) : 0;
	if ($totalTagihan == 0) {
		$kodeTag = 'belumtagih';
		$statusLunas = "<span style='color:gray;'>Belum Tagih</span>";
	} else {
		$persenBayar = ($totalTagihan > 0) ? min(($totalBayar / $totalTagihan) * 100, 100) : 0;
		if ($totalBayar >= $totalTagihan && $totalBayar >= $val['nilaikontrak']) {
			$kodeTag = 'lunas';
			$statusLunas = "<span style='color:green; font-weight:bold;'>Lunas (100%)</span>";
		} else if ($totalBayar > 0) {
			$kodeTag = 'sebagian';
			$statusLunas = "<span style='color:orange; font-weight:bold;'>SPK " . number_format($persenTertagih, 0) . "% &middot; Tagihan " . number_format($persenBayar, 0) . "%</span>";
		} else {
			$kodeTag = 'belumbayar';
			$statusLunas = "<span style='color:red; font-weight:bold;'>SPK " . number_format($persenTertagih, 0) . "% &middot; Belum Dibayar</span>";
		}
	}
	return array('realisasi' => $st['realisasi'], 'posting' => $statusposting, 'kodepos' => $kodePos, 'tagihan' => $statusLunas, 'kodetag' => $kodeTag);
}

#versi teks polos (untuk Excel dan PDF)
function spkTeks($html)
{
	$t = str_replace('<br>', ' / ', $html);
	$t = html_entity_decode(strip_tags($t), ENT_QUOTES, 'UTF-8');
	return trim(preg_replace('/\s+/', ' ', $t));
}

#uraian pekerjaan SPK: keterangan SPK, bila kosong diambil dari pengajuan SPK. hasil: peta nopengajuan => pekerjaan
function spkPekerjaan($rows)
{
	global $dbname;
	$peta = array();
	$daftar = array();
	foreach ($rows as $r) {
		if (trim($r['keterangan']) == '' && trim($r['nopengajuan']) != '') {
			$daftar[$r['nopengajuan']] = "'" . addslashes($r['nopengajuan']) . "'";
		}
	}
	foreach (array_chunk($daftar, 400) as $chunk) {
		foreach (fetchdata("select notransaksi,project from " . $dbname . ".lgl_pengajuanspkht where notransaksi in (" . implode(',', $chunk) . ")") as $r) {
			$peta[$r['notransaksi']] = trim($r['project']);
		}
	}
	return $peta;
}
function spkPekerjaanTeks($r, $peta)
{
	#nama pekerjaan ditampilkan huruf besar semua
	if (trim($r['keterangan']) != '') {
		return mb_strtoupper(trim($r['keterangan']), 'UTF-8');
	}
	return isset($peta[$r['nopengajuan']]) ? mb_strtoupper($peta[$r['nopengajuan']], 'UTF-8') : '';
}
#periode SPK (dari s/d sampai)
function spkPeriodeTeks($r)
{
	$d = ($r['dari'] != '' && $r['dari'] != '0000-00-00') ? tanggalnormal($r['dari']) : '';
	$s = ($r['sampai'] != '' && $r['sampai'] != '0000-00-00') ? tanggalnormal($r['sampai']) : '';
	return ($d != '' || $s != '') ? $d . ' s/d ' . $s : '';
}

#nama rekanan untuk sekumpulan SPK
function spkNamaRekanan($rows)
{
	global $dbname;
	$peta = array();
	$daftar = array();
	foreach ($rows as $r) {
		$daftar[$r['koderekanan']] = "'" . addslashes($r['koderekanan']) . "'";
	}
	foreach (array_chunk($daftar, 400) as $chunk) {
		foreach (fetchData("select supplierid,namasupplier from " . $dbname . ".log_5supplier where supplierid in (" . implode(',', $chunk) . ")") as $r) {
			$peta[$r['supplierid']] = $r['namasupplier'];
		}
	}
	return $peta;
}

#daftar SPK sesuai filter, tiap baris berisi kunci 'st' (status). $hal/$limit dipakai untuk list; $hal = null berarti semua baris (Excel dan PDF).
#hasil: rows = baris untuk ditampilkan, total = jumlah seluruh baris setelah filter, flt = hasil spkFilter
function spkDaftar($p, $hal = null, $limit = 15)
{
	global $dbname;
	$flt = spkFilter($p);
	$out = array('rows' => array(), 'total' => 0, 'flt' => $flt);
	if ($flt['error'] != '') {
		return $out;
	}
	$semua = fetchData("select * from " . $dbname . ".log_spkht where 1=1 " . spkScope() . $flt['where'] . " order by tanggal desc, notransaksi desc");
	$adaFilterStatus = ($flt['statuspos'] != '' || $flt['statustag'] != '');
	#status seluruh baris hanya dihitung bila dibutuhkan (filter status atau export)
	if ($adaFilterStatus || $hal === null) {
		$peta = spkStatusBatch($semua);
		$lolos = array();
		foreach ($semua as $r) {
			$r['st'] = spkStatusRow($r, $peta[$r['notransaksi']]);
			if ($flt['statuspos'] != '' && !in_array($flt['statuspos'], $r['st']['kodepos'])) {
				continue;
			}
			if ($flt['statustag'] != '' && $r['st']['kodetag'] != $flt['statustag']) {
				continue;
			}
			$lolos[] = $r;
		}
		$semua = $lolos;
	}
	$out['total'] = count($semua);
	if ($hal === null) {
		$out['rows'] = $semua;
		return $out;
	}
	$halaman = array_slice($semua, $hal * $limit, $limit);
	if (!$adaFilterStatus) {
		$peta = spkStatusBatch($halaman);
		foreach ($halaman as $k => $r) {
			$halaman[$k]['st'] = spkStatusRow($r, $peta[$r['notransaksi']]);
		}
	}
	$out['rows'] = $halaman;
	return $out;
}
