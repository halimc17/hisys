<?php
#Excel list BAPP Kontraktor, mengikuti seluruh filter list
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/zMysql.php');
require_once('lib/HtmlExcel.php');
require_once('log_realisasispkx_filter.php');

$dft = spkDaftar($_GET);
if ($dft['flt']['error'] != '') {
	exit($dft['flt']['error']);
}
$rows = $dft['rows'];
$namaRekanan = spkNamaRekanan($rows);
$petaPekerjaan = spkPekerjaan($rows);
$ptkode = getindukPT($_SESSION['empl']['lokasitugas']);
$hd = setheadreport($ptkode, $ptkode);
$logourl = '';
if (file_exists($hd['logo'])) {
	$skema = (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
	$logourl = $skema . "://" . @$_SERVER['HTTP_HOST'] . rtrim(dirname(@$_SERVER['SCRIPT_NAME']), '/') . "/" . $hd['logo'];
}
$h = function ($v) {
	return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};
$kolom = 13;
$tab = "<table>
<tr><td colspan=" . $kolom . " height='70' style='height:52pt'>" . ($logourl != '' ? "<img src='" . $logourl . "' height='60'>" : "") . "</td></tr>
<tr><td colspan=" . $kolom . "><b>" . $h($hd['nama']) . "</b></td></tr>
<tr><td colspan=" . $kolom . "><b>BAPP KONTRAKTOR</b></td></tr>
<tr><td colspan=" . $kolom . ">" . $h($dft['flt']['info'] != '' ? $dft['flt']['info'] : 'Seluruh data') . "</td></tr>
<tr><td colspan=" . $kolom . ">Ditarik oleh " . $h($_SESSION['empl']['name']) . " (" . $h($_SESSION['standard']['username']) . ") pada " . date('d-m-Y H:i:s') . "</td></tr>
<tr><td colspan=" . $kolom . ">&nbsp;</td></tr>
</table>
<table border=1>
<tr>
	<td bgcolor=#DEDEDE align=center>No.</td>
	<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['unit'] . "</td>
	<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['notransaksi'] . "</td>
	<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['tanggal'] . "</td>
	<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['subunit'] . "</td>
	<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['koderekanan'] . "</td>
	<td bgcolor=#DEDEDE align=center>Pekerjaan</td>
	<td bgcolor=#DEDEDE align=center>Periode SPK</td>
	<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['nilaikontrak'] . "</td>
	<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['matauang'] . "</td>
	<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['jumlahrealisasi'] . "</td>
	<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['status'] . "</td>
	<td bgcolor=#DEDEDE align=center>" . $_SESSION['lang']['keterangan'] . "</td>
</tr>";
$no = 0;
$tot = array();
foreach ($rows as $val) {
	$no++;
	$mu = $val['matauang'];
	if (!isset($tot[$mu])) {
		$tot[$mu] = array('nilai' => 0, 'aktual' => 0);
	}
	$tot[$mu]['nilai'] += $val['nilaikontrak'];
	$tot[$mu]['aktual'] += $val['st']['realisasi'];
	$tab .= "<tr>
	<td>" . $no . "</td>
	<td>" . $h($val['kodeorg']) . "</td>
	<td>" . $h($val['notransaksi']) . "</td>
	<td>" . tanggalnormal($val['tanggal']) . "</td>
	<td>" . $h($val['divisi']) . "</td>
	<td>" . $h(isset($namaRekanan[$val['koderekanan']]) ? $namaRekanan[$val['koderekanan']] : '') . "</td>
	<td>" . $h(spkPekerjaanTeks($val, $petaPekerjaan)) . "</td>
	<td>" . spkPeriodeTeks($val) . "</td>
	<td align=right>" . round($val['nilaikontrak'], 2) . "</td>
	<td align=center>" . $h($mu) . "</td>
	<td align=right>" . round($val['st']['realisasi'], 2) . "</td>
	<td>" . $h(spkTeks($val['st']['posting'])) . "</td>
	<td>" . $h(spkTeks($val['st']['tagihan'])) . "</td>
	</tr>";
}
if ($no == 0) {
	$tab .= "<tr><td colspan=" . $kolom . " align=center>Data tidak ditemukan</td></tr>";
} else {
	foreach ($tot as $mu => $t) {
		$tab .= "<tr>
		<td colspan=8 align=center bgcolor=#F0F0F0><b>TOTAL " . $h($mu) . "</b></td>
		<td align=right bgcolor=#F0F0F0><b>" . round($t['nilai'], 2) . "</b></td>
		<td bgcolor=#F0F0F0></td>
		<td align=right bgcolor=#F0F0F0><b>" . round($t['aktual'], 2) . "</b></td>
		<td colspan=2 bgcolor=#F0F0F0></td>
		</tr>";
	}
}
$tab .= "</table>";

$xls = new HtmlExcel();
$xls->setCss('');
$xls->addSheet("bapp_kontraktor", $tab);
$xls->headers("BAPP_Kontraktor_" . date('Ymd_His') . ".xls");
echo $xls->buildFile();
