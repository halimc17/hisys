<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$proses = checkPostGet('proses','');

//$arr="##pt##prd_start##prd_end";
$pt=checkPostGet('pt','');
$prd_start=checkPostGet('prd_start','');
$prd_end=checkPostGet('prd_end','');

if($prd_start=='' || $prd_end=='' || $pt=='') {
    exit("Error : Periode atau pt tidak boleh kosong");
}

if ($prd_start > $prd_end) {
    exit("Error : Periode awal tidak boleh lebih besar dari periode akhir");
}

function generatePeriode($start, $end) {
    $result = [];

    $startDate = DateTime::createFromFormat('Ym', $start);
    $endDate   = DateTime::createFromFormat('Ym', $end);

    while ($startDate <= $endDate) {
        $result[] = $startDate->format('Ym');
        $startDate->modify('+1 month');
    }

    return $result;
}

// contoh pakai
$rangePeriode = generatePeriode($prd_start, $prd_end);

// exit(implode(',', $rangePeriode));

$kodekebun = [];
$str = selectQuery($dbname,'organisasi','kodeorganisasi', "induk = '".$pt."' AND tipe = 'KEBUN'");
// exit($str);
$res = fetchData($str);
foreach($res as $row) {
    $kodekebun[] = $row['kodeorganisasi'];
}

$str = "SELECT 
    substr(a.notransaksi, 1, 6) as notransaksi,
    a.kodeorg,
    b.tahuntanam,
    b.luasareaproduktif,
    b.jumlahpokok,
    SUM(a.hasilkerja) as jjg,
    SUM(a.hasilkerjakg) as kg,
    SUM(a.jumlahhk) as hk
FROM ".$dbname.".kebun_prestasi_detail a
JOIN ".$dbname.".setup_blok_tahunan b 
    ON a.kodeorg = b.kodeorg
WHERE 
    SUBSTR(a.kodeorg,1,4) IN ('".implode("','",$kodekebun)."')
    AND SUBSTR(a.notransaksi,1,6) IN ('".implode("','",$rangePeriode)."')
    AND b.tahun LIKE '".$periode."%'
GROUP BY a.kodeorg, substr(a.notransaksi, 1, 6)
ORDER BY substr(a.notransaksi, 1, 6), a.kodeorg
";
// exit($str);
$res = fetchData($str);

$data = [];

foreach ($res as $row) {
    $periode = $row['notransaksi']; // YYYYMM
    $kodeorg = $row['kodeorg'];
    $tahuntanam = $row['tahuntanam'];

    $data[$tahuntanam][$kodeorg]['info'] = [
        'luasareaproduktif' => $row['luasareaproduktif'],
        'jumlahpokok'       => $row['jumlahpokok'],
    ];

    $data[$tahuntanam][$kodeorg][$periode] = [
        'jjg' => $row['jjg'],
        'kg'  => $row['kg'],
        'hk'  => $row['hk']
    ];
}

ksort($data);

//////////////////////
// Mulai Header Table
/////////////////////

$tab = "";

if ($proses == 'excel') {
    $tab.= "<table class=sortable cellspacing=1 border=1>";
} else {
    $tab.= "<table class=sortable cellspacing=0 cellpadding=5 border=1>";
}

$bgHeader='';
if($proses=='excel'){
    $border=1;
    $bgHeader="bgcolor=#DEDEDE align=center";
    $tab.="<thead><tr><td colspan=9>".strtoupper("Laporan Produksi Bulanan ".$pt)."</td></tr></thead>";
}

$tab .= "<thead>
            <tr class=rowheader>
                <th rowspan=2 ".$bgHeader.">NO</th>
                <th rowspan=2 ".$bgHeader.">BLOK</th>
                <th rowspan=2 ".$bgHeader.">LUAS Ha</th>
                <th rowspan=2 ".$bgHeader.">DIVISI</th>
                <th rowspan=2 ".$bgHeader.">JUMLAH POKOK</th>";

$bulan = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

foreach ($rangePeriode as $periode) {
    $tab .= "   <th colspan='6' $bgHeader>".substr($periode, 0, 4) . '-' . substr($periode, 4, 2)."</th>";
}

$tab .= "       <th colspan='6' $bgHeader>s/d ".substr($prd_end, 0, 4) . '-' . substr($prd_end, 4, 2)."</th>";

$tab .="    </tr>";

$tab .= "<tr class=rowheader>";
for ($i = 1; $i <= count($rangePeriode)+1; $i++) {
    $tab .= "   <th $bgHeader>JJG</th>
                <th $bgHeader>BJR</th>
                <th $bgHeader>KG</th>
                <th $bgHeader>TK</th>
                <th $bgHeader>Potensi / Hk</th>
                <th $bgHeader>Potensi / Ha</th>";
}
$tab .="    </tr>
        </thead>";

$tab .= "<tbody>";

$tt = '';
$no = 0;

$grandLuas = 0;
$grandPokok = 0;

foreach ($data as $tahuntanam => $blokData) {

    $tahunLuas = 0;
    $tahunPokok = 0;

    $tahunJjg = [];
    $tahunKg = [];
    $tahunHk = [];

    $totalJjgSd = 0;
    $totalKgSd = 0;
    $totalHkSd = 0;
    
    // header tahuntanam
    if ($tt != $tahuntanam) {
        $tab .= "<tr class='rowcontent'>
                    <td colspan=".(count($rangePeriode) * 6 + 11)." style='background-color:#f0f0f0;font-weight:bold;'>
                        TAHUN TANAM ".$tahuntanam."
                    </td>
                 </tr>";
        $tt = $tahuntanam;
    }

    foreach ($blokData as $kodeorg => $periodeData) {

        $no++;

        $tab .= "<tr class='rowcontent'>";
        $tab .= "<td align='center'>{$no}</td>";
        $tab .= "<td align='center'>".$kodeorg."</td>";
        $tab .= "<td align='right'>" . number_format($periodeData['info']['luasareaproduktif'], 2) . "</td>";
        $tab .= "<td align='center'>" . substr($kodeorg, 0, 6) . "</td>";
        $tab .= "<td align='right'>" . number_format($periodeData['info']['jumlahpokok'], 0) . "</td>";

        $totalJjg = 0;
        $totalKg = 0;
        $totalHk = 0;

        foreach ($rangePeriode as $periode) {
            $bjr = ($periodeData[$periode]['jjg'] != 0) ? $periodeData[$periode]['kg'] / $periodeData[$periode]['jjg'] : 0;

            $potensiHk = ($periodeData[$periode]['hk'] != 0) ? $periodeData[$periode]['kg'] / $periodeData[$periode]['hk'] : 0;

            $potensiHa = ($periodeData['info']['luasareaproduktif'] != 0) ? $periodeData[$periode]['kg'] / $periodeData['info']['luasareaproduktif'] : 0;

            $tab .= "<td align='right'>" . number_format($periodeData[$periode]['jjg'], 2) . "</td>";
            $tab .= "<td align='right'>" . number_format($bjr, 2) . "</td>";
            $tab .= "<td align='right'>" . number_format($periodeData[$periode]['kg'], 2) . "</td>";
            $tab .= "<td align='right'>" . number_format($periodeData[$periode]['hk'], 2) . "</td>";
            $tab .= "<td align='right'>" . number_format($potensiHk, 2) . "</td>";
            $tab .= "<td align='right'>" . number_format($potensiHa, 2) . "</td>";

            $totalJjg += $periodeData[$periode]['jjg'];
            $totalKg += $periodeData[$periode]['kg'];
            $totalHk += $periodeData[$periode]['hk'];

            $tahunJjg[$periode] += $periodeData[$periode]['jjg'];
            $tahunKg[$periode]  += $periodeData[$periode]['kg'];
            $tahunHk[$periode]  += $periodeData[$periode]['hk'];

            $grandJjg[$periode] += $periodeData[$periode]['jjg'];
            $grandKg[$periode]  += $periodeData[$periode]['kg'];
            $grandHk[$periode]  += $periodeData[$periode]['hk'];
        }

        // total s/d periode akhir
        $bjr = ($totalJjg != 0) ? $totalKg / $totalJjg : 0;

        $potensiHk = ($totalHk != 0) ? $totalKg / $totalHk : 0;

        $potensiHa = ($periodeData['info']['luasareaproduktif'] != 0) ? $totalKg / $periodeData['info']['luasareaproduktif'] : 0;

        $tab .= "<td align='right'>" . number_format($totalJjg, 2) . "</td>";
        $tab .= "<td align='right'>" . number_format($bjr, 2) . "</td>";
        $tab .= "<td align='right'>" . number_format($totalKg, 2) . "</td>";
        $tab .= "<td align='right'>" . number_format($totalHk, 2) . "</td>";
        $tab .= "<td align='right'>" . number_format($potensiHk, 2) . "</td>";
        $tab .= "<td align='right'>" . number_format($potensiHa, 2) . "</td>";

        $totalJjgSd += $totalJjg;
        $totalKgSd += $totalKg;
        $totalHkSd += $totalHk;
        
        $tahunLuas += $periodeData['info']['luasareaproduktif'];
        $tahunPokok += $periodeData['info']['jumlahpokok'];
    }

    $tab .= "<tr style='font-weight:bold;background:#e8f5e9;'>";
    $tab .= "<td colspan='2' align='center'>JUMLAH</td>";
    $tab .= "<td align='center'>".number_format($tahunLuas,2)."</td>";
    $tab .= "<td align='center'></td>";
    $tab .= "<td align='center'>".number_format($tahunPokok,2)."</td>";
    foreach ($rangePeriode as $periode) {
        $bjr = ($tahunJjg[$periode] != 0) ? $tahunKg[$periode] / $tahunJjg[$periode] : 0;

        $potensiHk = ($tahunHk[$periode] != 0) ? $tahunKg[$periode] / $tahunHk[$periode] : 0;

        $potensiHa = ($tahunLuas != 0) ? $tahunKg[$periode] / $tahunLuas : 0;

        $tab .= "<td align='right'>".number_format($tahunJjg[$periode],2)."</td>";
        $tab .= "<td align='right'>".number_format($bjr,2)."</td>";
        $tab .= "<td align='right'>".number_format($tahunKg[$periode],2)."</td>";
        $tab .= "<td align='right'>".number_format($tahunHk[$periode],2)."</td>";
        $tab .= "<td align='right'>".number_format($potensiHk,2)."</td>";
        $tab .= "<td align='right'>".number_format($potensiHa,2)."</td>";
    }

    $bjr = ($totalJjgSd != 0) ? $totalKgSd / $totalJjgSd : 0;

    $potensiHk = ($totalHkSd != 0) ? $totalKgSd / $totalHkSd : 0;

    $potensiHa = ($tahunLuas != 0) ? $totalKgSd / $tahunLuas : 0;

    $tab .= "<td align='right'>".number_format($totalJjgSd,2)."</td>";
    $tab .= "<td align='right'>".number_format($bjr,2)."</td>";
    $tab .= "<td align='right'>".number_format($totalKgSd,2)."</td>";
    $tab .= "<td align='right'>".number_format($totalHkSd,2)."</td>";
    $tab .= "<td align='right'>".number_format($potensiHk,2)."</td>";
    $tab .= "<td align='right'>".number_format($potensiHa,2)."</td>";

    $tab .= "</tr>";

    $grandLuas += $tahunLuas;
    $grandPokok += $tahunPokok;

    $grandJjgSd += $totalJjgSd;
    $grandKgSd += $totalKgSd;
    $grandHkSd += $totalHkSd;
}

$tab .= "<tr style='font-weight:bold;background:#ffe0b2;'>";
$tab .= "<td colspan='2' align='center'>TOTAL</td>";
$tab .= "<td align='right'>".number_format($grandLuas,2)."</td>";
$tab .= "<td ></td>";
$tab .= "<td align='right'>".number_format($grandPokok,2)."</td>";

foreach ($rangePeriode as $periode) {
    $bjr = ($grandJjg[$periode] != 0) ? $grandKg[$periode] / $grandJjg[$periode] : 0;

    $potensiHk = ($grandHk[$periode] != 0) ? $grandKg[$periode] / $grandHk[$periode] : 0;

    $potensiHa = ($grandLuas != 0) ? $grandKg[$periode] / $grandLuas : 0;

    $tab .= "<td align='right'>".number_format($grandJjg[$periode],2)."</td>";
    $tab .= "<td align='right'>".number_format($bjr,2)."</td>";
    $tab .= "<td align='right'>".number_format($grandKg[$periode],2)."</td>";
    $tab .= "<td align='right'>".number_format($grandHk[$periode],2)."</td>";
    $tab .= "<td align='right'>".number_format($potensiHk,2)."</td>";
    $tab .= "<td align='right'>".number_format($potensiHa,2)."</td>";
}

$bjr = ($grandJjgSd != 0) ? $grandKgSd / $grandJjgSd : 0;

$potensiHk = ($grandHkSd != 0) ? $grandKgSd / $grandHkSd : 0;

$potensiHa = ($grandLuas != 0) ? $grandKgSd / $grandLuas : 0;

$tab .= "<td align='right'>".number_format($grandJjgSd,2)."</td>";
$tab .= "<td align='right'>".number_format($bjr,2)."</td>";
$tab .= "<td align='right'>".number_format($grandKgSd,2)."</td>";
$tab .= "<td align='right'>".number_format($grandHkSd,2)."</td>";
$tab .= "<td align='right'>".number_format($potensiHk,2)."</td>";
$tab .= "<td align='right'>".number_format($potensiHa,2)."</td>";
$tab .= "</tr>";

switch($proses)
{
	case'preview':
        $tab .= "</tbody></table></fieldset>";
	    echo $tab;
	break;
	case'excel':
        $tab.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $namaFileExcel="Laporan Produksi Bulanan_".$pt;
        if(strlen($tab)>0)
        {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/'.$file);
                    }
                }	
                closedir($handle);
            }
            $handle=fopen("tempExcel/".$namaFileExcel.".xls",'w');
            if(!fwrite($handle,$tab))
            {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            }
            else
            {
                echo "<script language=javascript1.2>
                window.location='tempExcel/".$namaFileExcel.".xls';
                </script>";
            }
            fclose($handle);
        }
	break;
	default:
	break;
}
?>
