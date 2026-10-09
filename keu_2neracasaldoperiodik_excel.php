<?php
#Excel Neraca Saldo Periodik, mengikuti filter yang sama dengan preview
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/HtmlExcel.php');
require_once('keu_2neracasaldoperiodik_filter.php');

$flt = nspFilter($_GET);
if ($flt['error'] != '') {
	exit($flt['error']);
}
$data = nspData($flt);

$bulanLabel = array();
for ($m = 1; $m <= 12; $m++) {
	$nn = addZero($m, 2);
	$bulanLabel[$nn] = numToMonth($m, ($_SESSION['language'] == 'ID' ? 'I' : 'E'));
}

$hd = setheadreport($flt['pt'], $flt['pt']);
$kolom = 3 + 12 * 3 + 1;
$h = function ($v) {
	return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};

$tab = "<table>
<tr><td colspan=" . $kolom . "><b>" . $h($hd['nama']) . "</b></td></tr>
<tr><td colspan=" . $kolom . "><b>NERACA SALDO PERIODIK</b></td></tr>
<tr><td colspan=" . $kolom . ">" . $h($flt['info']) . "</td></tr>
<tr><td colspan=" . $kolom . ">Ditarik oleh " . $h($_SESSION['empl']['name']) . " (" . $h($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
</table>
<table border=1>
<tr>
	<td rowspan=2 bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['nourut'] . "</td>
	<td rowspan=2 bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['noakun'] . "</td>
	<td rowspan=2 bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['namaakun'] . "</td>";
foreach ($bulanLabel as $nn => $lbl) {
	$tab .= "<td colspan=" . ($nn == '12' ? 4 : 3) . " bgcolor=#DEDEDE align=center>" . $h($lbl) . "</td>";
}
$tab .= "</tr><tr>";
foreach ($bulanLabel as $nn => $lbl) {
	$tab .= "<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['saldoawal'] . "</td><td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['debet'] . "</td><td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['kredit'] . "</td>";
	if ($nn == '12') {
		$tab .= "<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['saldoakhir'] . "</td>";
	}
}
$tab .= "</tr>";

$no = 0;
$totBulan = array();
foreach ($bulanLabel as $nn => $lbl) {
	$totBulan[$nn] = array('awal' => 0, 'debet' => 0, 'kredit' => 0);
}
$totSaldoAkhir = 0;
if (count($data) == 0) {
	$tab .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
} else {
	foreach ($data as $noakun => $d) {
		$no++;
		$totSaldoAkhir += $d['saldoakhir'];
		$tab .= "<tr><td>" . $no . "</td><td>" . $h($noakun) . "</td><td>" . $h($d['namaakun']) . "</td>";
		foreach ($d['bulan'] as $nn => $b) {
			$totBulan[$nn]['awal'] += $b['awal'];
			$totBulan[$nn]['debet'] += $b['debet'];
			$totBulan[$nn]['kredit'] += $b['kredit'];
			$tab .= "<td align=right>" . round($b['awal'], 2) . "</td><td align=right>" . round($b['debet'], 2) . "</td><td align=right>" . round($b['kredit'], 2) . "</td>";
			if ($nn == '12') {
				$tab .= "<td align=right><b>" . round($d['saldoakhir'], 2) . "</b></td>";
			}
		}
		$tab .= "</tr>";
	}
	$tab .= "<tr><td colspan=3 align=center bgcolor=#F0F0F0><b>" . $_SESSION['lang']['total'] . "</b></td>";
	foreach ($bulanLabel as $nn => $lbl) {
		$tab .= "<td align=right bgcolor=#F0F0F0><b>" . round($totBulan[$nn]['awal'], 2) . "</b></td><td align=right bgcolor=#F0F0F0><b>" . round($totBulan[$nn]['debet'], 2) . "</b></td><td align=right bgcolor=#F0F0F0><b>" . round($totBulan[$nn]['kredit'], 2) . "</b></td>";
		if ($nn == '12') {
			$tab .= "<td align=right bgcolor=#F0F0F0><b>" . round($totSaldoAkhir, 2) . "</b></td>";
		}
	}
	$tab .= "</tr>";
}
$tab .= "</table>";

$xls = new HtmlExcel();
$xls->setCss('');
$xls->addSheet("neraca_saldo_periodik", $tab);
$xls->headers("NeracaSaldoPeriodik_" . $flt['pt'] . "_" . $flt['tahun'] . "_" . date('Ymd_His') . ".xls");
echo $xls->buildFile();
