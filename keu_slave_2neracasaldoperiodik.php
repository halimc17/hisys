<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('keu_2neracasaldoperiodik_filter.php');

$method = checkPostGet('method', '');

switch ($method) {
	case 'preview':
		$flt = nspFilter($_POST);
		if ($flt['error'] != '') {
			exit($flt['error']);
		}
		$data = nspData($flt);

		$bulanLabel = array();
		for ($m = 1; $m <= 12; $m++) {
			$nn = addZero($m, 2);
			$bulanLabel[$nn] = numToMonth($m, ($_SESSION['language'] == 'ID' ? 'I' : 'E'));
		}

		$ptSel = addslashes($flt['pt']);
		$tab = "<table class=sortable border=0 cellspacing=1 cellpadding=3 style='width:100%'>
			<colgroup><col style='width:34px'><col style='width:95px'><col style='width:270px'></colgroup>
			<thead>
			<tr class=rowheader>
				<th rowspan=2 align=center nowrap>" . $_SESSION['lang']['nourut'] . "</th>
				<th rowspan=2 align=center nowrap>" . $_SESSION['lang']['noakun'] . "</th>
				<th rowspan=2 align=center>" . $_SESSION['lang']['namaakun'] . "</th>";
		foreach ($bulanLabel as $nn => $lbl) {
			$tab .= "<th colspan=" . ($nn == '12' ? 4 : 3) . " align=center>" . $lbl . "</th>";
		}
		$tab .= "</tr><tr class=rowheader>";
		foreach ($bulanLabel as $nn => $lbl) {
			$tab .= "<th align=center>" . $_SESSION['lang']['saldoawal'] . "</th><th align=center>" . $_SESSION['lang']['debet'] . "</th><th align=center>" . $_SESSION['lang']['kredit'] . "</th>";
			if ($nn == '12') {
				$tab .= "<th align=center>" . $_SESSION['lang']['saldoakhir'] . "</th>";
			}
		}
		$tab .= "</tr></thead><tbody>";

		$no = 0;
		$totBulan = array();
		foreach ($bulanLabel as $nn => $lbl) {
			$totBulan[$nn] = array('awal' => 0, 'debet' => 0, 'kredit' => 0);
		}
		$totSaldoAkhir = 0;
		if (count($data) == 0) {
			$tab .= "<tr class=rowcontent><td colspan='40' align=center>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
		} else {
			foreach ($data as $noakun => $d) {
				$no++;
				$totSaldoAkhir += $d['saldoakhir'];
				$tab .= "<tr class=rowcontent style='vertical-align:top'><td align=center>" . $no . "</td><td nowrap>" . $noakun . "</td><td>" . $d['namaakun'] . "</td>";
				foreach ($d['bulan'] as $nn => $b) {
					$totBulan[$nn]['awal'] += $b['awal'];
					$totBulan[$nn]['debet'] += $b['debet'];
					$totBulan[$nn]['kredit'] += $b['kredit'];
					$tab .= "<td align=right nowrap>" . number_format($b['awal'], 2) . "</td>"
						. "<td align=right nowrap style='cursor:pointer' title='Klik untuk lihat rincian jurnal' onclick=\"lihatDetailNsp('" . $noakun . "','" . $nn . "',event)\">" . number_format($b['debet'], 2) . "</td>"
						. "<td align=right nowrap style='cursor:pointer' title='Klik untuk lihat rincian jurnal' onclick=\"lihatDetailNsp('" . $noakun . "','" . $nn . "',event)\">" . number_format($b['kredit'], 2) . "</td>";
					if ($nn == '12') {
						$tab .= "<td align=right nowrap><b>" . number_format($d['saldoakhir'], 2) . "</b></td>";
					}
				}
				$tab .= "</tr>";
			}
		}
		$tab .= "<tr class=rowcontent><td colspan=3 align=center><b>" . $_SESSION['lang']['total'] . "</b></td>";
		foreach ($bulanLabel as $nn => $lbl) {
			$tab .= "<td align=right><b>" . number_format($totBulan[$nn]['awal'], 2) . "</b></td>"
				. "<td align=right><b>" . number_format($totBulan[$nn]['debet'], 2) . "</b></td>"
				. "<td align=right><b>" . number_format($totBulan[$nn]['kredit'], 2) . "</b></td>";
			if ($nn == '12') {
				$tab .= "<td align=right><b>" . number_format($totSaldoAkhir, 2) . "</b></td>";
			}
		}
		$tab .= "</tr></tbody></table>";
		#simpan noakun+bulan yang tampil sekarang, dipakai form drilldown supaya id noakun tidak perlu ditaruh di tiap sel
		echo "<input type=hidden id=nspPt value='" . $ptSel . "'><input type=hidden id=nspRegional value='" . addslashes(checkPostGet('regional', '')) . "'><input type=hidden id=nspGudang value='" . addslashes(checkPostGet('gudang', '')) . "'><input type=hidden id=nspTahun value='" . addslashes($flt['tahun']) . "'><input type=hidden id=nspRevisi value='" . (int)$flt['revisi'] . "'>" . $tab;
		break;

	case 'detailjurnal':
		$noakun = checkPostGet('noakun', '');
		$bulan = checkPostGet('bulan', '');
		$pt = checkPostGet('pt', '');
		$tahun = checkPostGet('tahun', '');
		$revisi = (int)checkPostGet('revisi', '0');
		if ($noakun == '' || !preg_match('/^\d{2}$/', $bulan) || !preg_match('/^\d{4}$/', $tahun)) {
			exit('Error : Parameter tidak lengkap.');
		}
		$scope = nspWhereUnit($pt, checkPostGet('regional', ''), checkPostGet('gudang', ''));
		$periode = $tahun . '-' . $bulan;
		$nmAkun = fetchData("select namaakun from " . $dbname . ".keu_5akun where noakun='" . addslashes($noakun) . "'");

		$rows = fetchData("select tanggal,nojurnal,kodeorg,nodok,noreferensi,keterangan,debet,kredit from " . $dbname . ".keu_jurnaldt_vw
			where noakun='" . addslashes($noakun) . "' and periode='" . addslashes($periode) . "' and revisi <= '" . $revisi . "' " . $scope['where'] . "
			order by tanggal,nojurnal");

		$tab = "<div style='margin-bottom:6px'>Akun <b>" . $noakun . " - " . (count($nmAkun) > 0 ? $nmAkun[0]['namaakun'] : '') . "</b>"
			. " | Periode <b>" . $periode . "</b> | " . $scope['info'] . "</div>";
		$tab .= "<table cellspacing=1 border=0 style='width:100%'><thead><tr class=rowheader>
			<td align=center>No</td><td align=center>Tanggal</td><td align=center>Unit</td><td align=center>No Jurnal</td>
			<td align=center>No Dokumen</td><td align=center>No Referensi</td><td align=center>Keterangan</td>
			<td align=center>Debet</td><td align=center>Kredit</td></tr></thead><tbody>";
		$no = 0;
		$totD = $totK = 0;
		foreach ($rows as $r) {
			$no++;
			$totD += $r['debet'];
			$totK += $r['kredit'];
			$tab .= "<tr class=rowcontent>
				<td align=center>" . $no . "</td>
				<td align=center>" . tanggalnormal($r['tanggal']) . "</td>
				<td align=center>" . $r['kodeorg'] . "</td>
				<td>" . $r['nojurnal'] . "</td>
				<td>" . $r['nodok'] . "</td>
				<td>" . $r['noreferensi'] . "</td>
				<td>" . $r['keterangan'] . "</td>
				<td align=right>" . number_format($r['debet'], 2) . "</td>
				<td align=right>" . number_format($r['kredit'], 2) . "</td>
			</tr>";
		}
		if ($no == 0) {
			$tab .= "<tr class=rowcontent><td colspan=9 align=center>" . $_SESSION['lang']['dataempty'] . "</td></tr>";
		} else {
			$tab .= "<tr class=rowcontent><td colspan=7 align=center><b>" . $_SESSION['lang']['total'] . "</b></td>
				<td align=right><b>" . number_format($totD, 2) . "</b></td>
				<td align=right><b>" . number_format($totK, 2) . "</b></td></tr>";
		}
		$tab .= "</tbody></table>";
		echo $tab;
		break;

	default:
		exit('Error : method tidak dikenal.');
}
