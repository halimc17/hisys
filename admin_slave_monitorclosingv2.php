<?php
require_once 'master_validation.php';
require_once 'lib/zLib.php';
include_once 'lib/HtmlExcel.php';

$proses = checkPostGet('proses', '');
$prd = checkPostGet('prd', '');
$pt = checkPostGet('pt', '');
$tipe = checkPostGet('tipe', '');

$arrbi = explode('-', $prd);
$tahun = $arrbi[0];
$bulan = $arrbi[1];
$periode1 = $tahun . "-01";
$periode2 = $tahun . "-12";
$periode2 = $prd;

$arrjenis = array(
    'acct' => 'Keu - Proses - Tutup Periode Acct',
);

if ($prd == '') {
    exit("Warning : Periode harus diisi.");
}

if ($pt == '') {
    exit("Warning : Perusahaan harus diisi.");
}

$wh = "";
if ($pt != '') {
    $wh .= " and induk='" . $pt . "'";
}
if ($tipe != '') {
    $wh .= " and tipe='" . $tipe . "'";
}

$str = "select * from " . $dbname . ".organisasi where length(kodeorganisasi)='4' " . $wh . " and induk not in ('LCK','SDP') and namaorganisasi not like '%plasma%' order by induk";
$res = fetchdata($str);
foreach ($res as $bar) {
    $unit[$bar['kodeorganisasi']] = $bar['kodeorganisasi'];
    $listunit[$bar['induk']] += 1;
    $dtpt[$bar['induk']] = $bar['induk'];
}
if ($proses != 'excel') {
    $tab .= "<table class='sortable' cellspacing='1' cellpadding='5'>";
} else {
    $tab .= "<table border='1' class='sortable' cellspacing='1' cellpadding='5'>";
}
$tab .= "
	<thead>
		<tr class='rowheader'>
			<th align=center>" . $_SESSION['lang']['nourut'] . "</th>
			<th align=center>Kebun/Pabrik</th>
			<th align=center>User Closing</th>
			<th align=center>Proses</th>
			<th align=center>Last Periode Closing</th>
			<th align=center>Closing Date</th>
			<th align=center>Closing Time</th>
			<th align=center>Status</th>
		</tr>
	</thead>
	<tbody>";

$str = "select * from " . $dbname . ".setup_periodeakuntansi a left join " . $dbname . ".keu_setup_watu_tutup b on a.periode=b.periode and a.kodeorg=b.kodeorg where a.periode='" . $prd . "' and a.tutupbuku='1' AND LENGTH(a.kodeorg) = '4'";
$res = fetchdata($str);
foreach ($res as $bar) {
    $data[$bar['kodeorg']]['acct']['user'] = $bar['username'];
    $arrTanggal = explode("-", explode(" ", $bar['waktu'])[0]);
    $tanggal = "{$arrTanggal[2]}-" . numToMonth($arrTanggal[1], "I") . "-{$arrTanggal[0]}";
    $data[$bar['kodeorg']]['acct']['closedate'] = $tanggal;
    $data[$bar['kodeorg']]['acct']['closetime'] = explode(" ", $bar['waktu'])[1];
}

# Last Close
$period = DateTime::createFromFormat('Y-m', $prd);
$period->modify('-1 month');
$lastPeriode = $period->format('Y-m');
$str = "select waktu, a.kodeorg from " . $dbname . ".setup_periodeakuntansi a left join " . $dbname . ".keu_setup_watu_tutup b on a.periode=b.periode and a.kodeorg=b.kodeorg where a.periode='" . $lastPeriode . "' and a.tutupbuku='1' AND LENGTH(a.kodeorg) = '4'";
$res = fetchdata($str);
foreach ($res as $bar) {
    $arrTanggal = explode("-", explode(" ", $bar['waktu'])[0]);
    $periode = "" . numToMonth($arrTanggal[1], "I", "long") . " ,{$arrTanggal[0]}";
    $data[$bar['kodeorg']]['acct']['lastclose'] = $periode;
}

foreach ($arrjenis as $kodejns => $nmjenis) {
    $no = 1;
    foreach ($unit as $kdorg) {
        $tab .= "<tr class='rowcontent'>";
        $tab .= "<td align='center'>" . $no++ . "</td>";
        $tab .= "<td align='center'>{$kdorg} - " . getNamaOrg($kdorg) . "</td>";
        $tab .= "<td align='center'>{$data[$kdorg][$kodejns]['user']}</td>";
        $tab .= "<td align='center'>{$nmjenis}</td>";
        $tab .= "<td align='center'>{$data[$kdorg][$kodejns]['lastclose']}</td>";
        $tab .= "<td align='center'>{$data[$kdorg][$kodejns]['closedate']}</td>";
        $tab .= "<td align='center'>{$data[$kdorg][$kodejns]['closetime']}</td>";
        $hasil = $stl = "";
        if (!empty($data[$kdorg][$kodejns]) && count($data[$kdorg][$kodejns]) > 1) {
            $hasil = "CLOSE";
            $stl = "style=background-color:green;color:yellow;font-size:10px;font-weight:bold;";
        }
        $tab .= "<td align='center' " . $stl . ">" . $hasil . "</td>";
    }
    $tab .= "</tr>";
}
$tab .= "</tbody></table>";

switch ($proses) {
    case 'preview':
        echo $tab;
        break;

    case 'excel':
        $nop = "monitoringclosing.xls";
        $xls = new HtmlExcel();
        $xls->setCss($css);
        $xls->addSheet('monitoringclosing', $tab);
        $xls->headers($nop);
        echo $xls->buildFile();
        break;
}
