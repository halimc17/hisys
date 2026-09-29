<?php
#helper Neraca Saldo Periodik: satu tempat untuk cakupan unit, kode CLM, data 12 bulan, dan info filter,
#dipakai bersama oleh slave (preview/drilldown), export Excel, dan export PDF supaya hasilnya selalu sama

#kode akun penampung laba tahun berjalan (CLM), dikecualikan dari daftar akun
function nspKodeCLM()
{
	global $dbname;
	$r = fetchData("select noakundebet from " . $dbname . ".keu_5parameterjurnal where kodeaplikasi='CLM'");
	return count($r) > 0 ? $r[0]['noakundebet'] : '';
}

#cakupan unit: PT saja, PT+regional, atau satu unit. hasil: where = tambahan "and kodeorg ..." untuk keu_saldobulanan/keu_jurnaldt_vw, info = keterangan cakupan untuk kop
function nspWhereUnit($pt, $regional, $gudang)
{
	global $dbname;
	if ($gudang != '') {
		$nmUnit = fetchData("select namaorganisasi from " . $dbname . ".organisasi where kodeorganisasi='" . addslashes($gudang) . "'");
		$info = 'Unit: ' . $gudang . (count($nmUnit) > 0 ? ' - ' . $nmUnit[0]['namaorganisasi'] : '');
		return array('where' => " and kodeorg='" . addslashes($gudang) . "'", 'info' => $info);
	}
	if ($regional != '') {
		$where = " and kodeorg in (select kodeunit from " . $dbname . ".bgt_regional_assignment where regional='" . addslashes($regional) . "'"
			. " and kodeunit in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . addslashes($pt) . "'))";
		return array('where' => $where, 'info' => 'Regional: ' . $regional);
	}
	$where = " and kodeorg in (select kodeorganisasi from " . $dbname . ".organisasi where induk='" . addslashes($pt) . "' and length(kodeorganisasi)=4)";
	return array('where' => $where, 'info' => 'Seluruh unit PT ' . $pt);
}

#filter dan validasi dasar. hasil: where berisi rentang akun + cakupan unit, info gabungan, error bila tidak valid
function nspFilter($p)
{
	global $dbname;
	$g = function ($k) use ($p) {
		return isset($p[$k]) ? trim($p[$k]) : '';
	};
	$pt = $g('pt');
	$tahun = $g('tahun');
	$error = '';
	if ($pt == '') {
		$error = 'warning : PT harus dipilih.';
	} elseif (!preg_match('/^\d{4}$/', $tahun)) {
		$error = 'warning : Tahun harus dipilih.';
	}

	$akundari = $g('akundari');
	$akunsampai = $g('akunsampai');
	$whereakun = '';
	if ($akundari != '' && $akunsampai != '') {
		$whereakun = " and noakun between '" . addslashes($akundari) . "' and '" . addslashes($akunsampai) . "'";
	}

	$scope = nspWhereUnit($pt, $g('regional'), $g('gudang'));
	$info = array('PT: ' . $pt, $scope['info'], 'Tahun: ' . $tahun);
	if ($akundari != '' || $akunsampai != '') {
		$info[] = 'No Akun: ' . ($akundari != '' ? $akundari : '...') . ' s/d ' . ($akunsampai != '' ? $akunsampai : '...');
	}
	$revisi = ($g('revisi') !== '') ? (int)$g('revisi') : 0;
	$info[] = 'Revisi: ' . $revisi;

	return array(
		'error' => $error,
		'pt' => $pt,
		'tahun' => $tahun,
		'revisi' => $revisi,
		'tampilanId' => $g('tampilanId'),
		'whereunit' => $scope['where'],
		'whereakun' => $whereakun,
		'info' => implode(' | ', $info),
	);
}

#data 12 bulan (saldo awal, debet, kredit) + saldo akhir tahun per akun untuk satu tahun+cakupan.
#hasil: array noakun => array('namaakun'=>..,'bulan'=>[NN=>['awal','debet','kredit']],'saldoakhir'=>..)
function nspData($flt)
{
	global $dbname;
	$clm = nspKodeCLM();
	$tahun = $flt['tahun'];
	$where = $flt['whereunit'] . $flt['whereakun'];

	#daftar akun (level 5/detail) dalam rentang, CLM dikecualikan
	$data = array();
	$rAkun = fetchData("select noakun,namaakun from " . $dbname . ".keu_5akun where level='5' and noakun!='" . addslashes($clm) . "' " . $flt['whereakun'] . " order by noakun");
	foreach ($rAkun as $ra) {
		$data[$ra['noakun']] = array('namaakun' => $ra['namaakun'], 'bulan' => array());
		for ($m = 1; $m <= 12; $m++) {
			$data[$ra['noakun']]['bulan'][addZero($m, 2)] = array('awal' => 0, 'debet' => 0, 'kredit' => 0);
		}
	}

	#saldo awal bulan 01: satu baris keu_saldobulanan per bulan, kolom awal01 hanya terisi pada baris bulan 01 itu sendiri,
	#jadi SUM atas seluruh baris setahun otomatis mengambil nilai yang benar (sama seperti laporan Neraca Periodik).
	#kolom awal02..awal12 TIDAK dipakai lagi karena ternyata tidak konsisten terisi di keu_saldobulanan;
	#saldo awal bulan 02-12 dihitung sendiri di bawah = saldo akhir bulan sebelumnya
	$sql = "select noakun, sum(awal01) as awal01 from " . $dbname . ".keu_saldobulanan where periode like '" . addslashes($tahun) . "%' and noakun!='" . addslashes($clm) . "' " . $where . " group by noakun";
	foreach (fetchData($sql) as $r) {
		if (!isset($data[$r['noakun']])) {
			continue;
		}
		$data[$r['noakun']]['bulan']['01']['awal'] = (float)$r['awal01'];
	}

	#debet/kredit per bulan dari jurnal (bukan dari keu_saldobulanan), satu query untuk 12 bulan sekaligus
	$sql = "select noakun, substr(periode,6,2) as bln, sum(debet) as debet, sum(kredit) as kredit from " . $dbname . ".keu_jurnaldt_vw
		where periode >= '" . addslashes($tahun) . "-01' and periode <= '" . addslashes($tahun) . "-12' and noakun!='" . addslashes($clm) . "'
		and revisi <= '" . (int)$flt['revisi'] . "' " . $where . " group by noakun, bln";
	foreach (fetchData($sql) as $r) {
		if (!isset($data[$r['noakun']]) || !isset($data[$r['noakun']]['bulan'][$r['bln']])) {
			continue;
		}
		$data[$r['noakun']]['bulan'][$r['bln']]['debet'] = (float)$r['debet'];
		$data[$r['noakun']]['bulan'][$r['bln']]['kredit'] = (float)$r['kredit'];
	}

	#saldo awal bulan 02-12 = saldo akhir bulan sebelumnya (awal+debet-kredit), dihitung berjalan (running);
	#saldoakhir bulan 12 disimpan terpisah untuk kolom Saldo Akhir di akhir tahun
	foreach ($data as $noakun => &$d) {
		for ($m = 2; $m <= 12; $m++) {
			$nn = addZero($m, 2);
			$nnSblm = addZero($m - 1, 2);
			$d['bulan'][$nn]['awal'] = $d['bulan'][$nnSblm]['awal'] + $d['bulan'][$nnSblm]['debet'] - $d['bulan'][$nnSblm]['kredit'];
		}
		$d['saldoakhir'] = $d['bulan']['12']['awal'] + $d['bulan']['12']['debet'] - $d['bulan']['12']['kredit'];
	}
	unset($d);

	#buang akun yang seluruh 12 bulannya nol, bila diminta
	if ($flt['tampilanId'] == '1') {
		foreach ($data as $noakun => $d) {
			$total = 0;
			foreach ($d['bulan'] as $b) {
				$total += abs($b['awal']) + abs($b['debet']) + abs($b['kredit']);
			}
			if ($total == 0) {
				unset($data[$noakun]);
			}
		}
	}

	return $data;
}
