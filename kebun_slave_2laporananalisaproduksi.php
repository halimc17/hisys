<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ============================================================================
// INPUT VALIDATION & INITIALIZATION
// ============================================================================

$proses = checkPostGet('proses', '');
$kode_org = checkPostGet('kodeorg', '');
$tanggal = checkPostGet('tanggal', '');

// Validasi input
if ($tanggal == '' || $kode_org == '') {
    exit("Error : PT atau Tanggal tidak boleh kosong");
}

$tanggaldate = new DateTime($tanggal);
$tglKemarin = clone $tanggaldate;
$tglKemarin->modify('-1 day');

$where = "";

if($kode_org == 'all'){
    $where .= " tipe='AFDELING'";
}else{
    $where .= " induk LIKE '" . $kode_org . "%' AND tipe='AFDELING'";
}

$kdorg = "";
$arrAfd = [];

$str = selectQuery($dbname,'organisasi','induk, kodeorganisasi, namaorganisasi', $where);
$res = fetchData($str);
foreach($res as $key => $val){
    $afd[$val['induk']][$val['kodeorganisasi']] = $val['namaorganisasi'];
    $kdorg .= "'" . $val['kodeorganisasi'] . "', ";
    $arrAfd[] = $val['kodeorganisasi'];
}
// exit(print_r($afd));

$kdorg = substr($kdorg, 0, -2);
// exit('warning: '.$tanggal);

$str = "SELECT afdeling, haesok, hkdigunakan, jmlhpokok, jjgmasak, persenbuahmatang, bjr
FROM ".$dbname.".kebun_taksasi
WHERE `afdeling` IN (".$kdorg.") AND `tanggal` = '".$tanggaldate->format('Y-m-d')."'";
$res = fetchData($str);
for($i=0; $i<count($res); $i++){
    $rencana[$res[$i]['afdeling']]['haesok'] += $res[$i]['haesok'];
    $rencana[$res[$i]['afdeling']]['hkdigunakan'] += $res[$i]['hkdigunakan'];
    $rencana[$res[$i]['afdeling']]['jmlhpokok'] += $res[$i]['jmlhpokok'];
    $rencana[$res[$i]['afdeling']]['jjgmasak'] += $res[$i]['jjgmasak'];

    $rencana[$res[$i]['afdeling']]['bjr'] += $res[$i]['bjr'];

    $kg = $res[$i]['jjgmasak'] * $res[$i]['bjr'];
    $rencana[$res[$i]['afdeling']]['kg'] += $kg;
}




// ambil data kebun_rekappnn_vw untuk restan kemarin, realisasi, restan hari ini
$str = "SELECT divisi, blok as indukblok, tahuntanam, substr(tanggal,1,7) as periode, tanggal, SUM(luaspanen) as ha, SUM(tenagakerja) as tk, SUM(jjgpanen) as jjgpanen, SUM(jjgafkir) as jjgafkir FROM ".$dbname.".kebun_rekappnn_vw where tanggal in ('".$tanggaldate->format('Y-m-d')."','".$tglKemarin->format('Y-m-d')."') and divisi in (".$kdorg.") group by indukblok, tanggal ";

// exit('warning : '.$str);

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $jjgBersih = $bar['jjgpanen'] - $bar['jjgafkir'];

    if($bar['tanggal'] == $tanggaldate->format('Y-m-d')){
        $realisasi[$bar['divisi']]['ha'] += $bar['ha'];
        $realisasi[$bar['divisi']]['tk'] += $bar['tk'];
        $realisasi[$bar['divisi']]['jjgpanen'] += $bar['jjgpanen'];
        
        
        $restan['jjg'][$bar['divisi']]['hi'] += $jjgBersih;
        
    }else{
        $restan['jjg'][$bar['divisi']]['kemarin'] += $jjgBersih;
    }

    $tempRestan['jjg'][$bar['divisi']][$bar['indukblok']][$bar['periode']][$bar['tanggal']] += $jjgBersih; // untuk cari restan kg
    $tahuntanam[$bar['divisi']][] = $bar['tahuntanam'];
    $listBlok[$bar['divisi']][] = $bar['indukblok'];
}

// sort tahun tanam
if (!empty($tahuntanam)) {
    foreach ($tahuntanam as $divisi => $tahunList) {
        $tahuntanam[$divisi] = array_values(array_unique($tahunList));
        sort($tahuntanam[$divisi], SORT_NUMERIC);
    }
}

if (!empty($listBlok)) {
    foreach ($listBlok as $divisi => $blokList) {
        $listBlok[$divisi] = array_values(array_unique($blokList));
        sort($listBlok[$divisi]);
    }
}

// kebun_spb_vw4 pengurang restan
$str=" select sum(jjg) as jjg, sum(kgwbnetto) as kgwbnetto, divisi,indukblok, substr(tanggal,1,7) as periode, tanggal from ".$dbname.".kebun_spb_vw4 where 1=1 and divisi in (".$kdorg.") and tanggal in ('".$tanggaldate->format('Y-m-d')."','".$tglKemarin->format('Y-m-d')."') group by divisi, periode, tanggal, indukblok ";
// exit('warning: '.$str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){

    if($bar['tanggal'] == $tanggaldate->format('Y-m-d')){        
        $restan['jjg'][$bar['divisi']]['hi'] -= $bar['jjg'];
    }else{
        $restan['jjg'][$bar['divisi']]['kemarin'] -= $bar['jjg'];
        // $restan['kg'][$bar['divisi']]['kemarin'] -= $bar['kgwbnetto'];
    }

    $realjjg[$bar['divisi']][$bar['indukblok']][$bar['periode']][$bar['tanggal']] += $tempRestan['jjg'][$bar['divisi']][$bar['indukblok']][$bar['periode']][$bar['tanggal']];

    $tempRestan['jjg'][$bar['divisi']][$bar['indukblok']][$bar['periode']][$bar['tanggal']] -= $bar['jjg']; // untuk cari restan kg

}


// kebun_spbdt_detail untuk mencari bjr
$str = "select substr(indukblok,1,6) as divisi, indukblok, SUBSTR(tanggalpanen,1,7) as periode, bjr from ".$dbname.".kebun_spbdt_detail where tanggalpanen <= '".$tanggaldate->format('Y-m-d')."' and substr(indukblok,1,6) in (".$kdorg.") group by indukblok, periode";
// exit($str);
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $dataBjr[$bar['divisi']][$bar['indukblok']][$bar['periode']] = $bar['bjr'];
}

foreach($tempRestan['jjg'] as $divisi => $indukblok){
    foreach($indukblok as $indukblok => $periode){
        foreach($periode as $periode => $tanggal){
            foreach($tanggal as $tanggal => $value){
                if ($tanggal == $tanggaldate->format('Y-m-d')) {
                    $restan['kg'][$divisi]['hi'] += $value * $dataBjr[$divisi][$indukblok][$periode];
                    $realisasi[$divisi]['kg'] += $realjjg[$divisi][$indukblok][$periode][$tanggal] * $dataBjr[$divisi][$indukblok][$periode];
                }else{
                    $restan['kg'][$divisi]['kemarin'] += $value * $dataBjr[$divisi][$indukblok][$periode];
                }
            }
        }
    }
}

// ambil data kebun prestasi untuk mencari tk
$str = "SELECT SUBSTR(kodeorg,1,6) as afdeling, COUNT(DISTINCT karyawanid) as jml_tk FROM ".$dbname.".kebun_prestasi_pnn_detail_vw where tanggal = '".$tanggaldate->format('Y-m-d')."' and SUBSTR(kodeorg,1,6) in (".$kdorg.") group by afdeling";

$tk = array();
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $tk[$bar['afdeling']] = $bar['jml_tk'];
}


// ambil data jumlah pokok di setup blok tahunan untuk akp
$str = "SELECT SUBSTR(indukblok,1,6) as divisi, tahun, sum(luasareaproduktif) as luasareaproduktif, sum(jumlahpokok) as jumlahpokok from ".$dbname.".setup_blok_tahunan where tahun = '".$tanggaldate->format('Ym')."' and substr(kodeorg,1,6) in (".$kdorg.") group by divisi ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $setupblok[$bar['divisi']]['luasareaproduktif'] += $bar['luasareaproduktif'];
    $setupblok[$bar['divisi']]['jumlahpokok'] += $bar['jumlahpokok'];
}

foreach($arrAfd as $key => $val){
    if($setupblok[$val]['jumlahpokok'] == 0){
        $akp[$val] = 0;
    }else{
        $akp[$val] = $realisasi[$val]['jjgpanen'] / $setupblok[$val]['jumlahpokok'] * 100;
    }
}

// data spb untuk kolom tbs kirim ke mill
$str=" SELECT SUBSTR(indukblok,1,6) as divisi, sum(jjg) as jjg, sum(kgwbnetto) as kgwbnetto FROM ".$dbname.".kebun_spbdt_detail where 1=1 and substr(indukblok,1,6) in (".$kdorg.") and tanggalpanen = '".$tanggaldate->format('Y-m-d')."' group by divisi ";

$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $tbskirim[$bar['divisi']]['jjg'] = $bar['jjg'];
    $tbskirim[$bar['divisi']]['kgwbnetto'] = $bar['kgwbnetto'];
}


// datakaryawan
$str = "SELECT karyawanid, subbagian FROM ".$dbname.".datakaryawan_hist where subbagian in (".$kdorg.") and periodegaji = '".$tanggaldate->format('Y-m')."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $datakaryawan[$bar['karyawanid']] = $bar['subbagian'];
}


// sdm_absensidt
$str = "SELECT karyawanid, tanggal, absensi FROM ".$dbname.".sdm_absensidt where tanggal = '".$tanggaldate->format('Y-m-d')."' ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $dataabsen[$datakaryawan[$bar['karyawanid']]][$bar['absensi']] += 1;
}

// sdm_5absensi
$str = "SELECT kodeabsen,keterangan FROM ".$dbname.".sdm_5absensi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while($bar=$res->fetch()){
    $kodeabsen[$bar['kodeabsen']] = $bar['keterangan'];
}

// ============================================================================
// TABLE INITIALIZATION
// ============================================================================

$stream = '';
$border = '0';
$bgHeader = '';

if ($proses == 'excel') {
    $stream = "<table class=\"sortable\" cellspacing=\"1\" border=\"1\">";
    $border = 1;
    $bgHeader = "bgcolor=\"#DEDEDE\" align=\"center\"";
} else {
    $stream = "<table cellspacing=\"3\" cellpadding=\"5\" border=\"".$border."\" class=\"sortable\">";
}

$html = "";

// Table header untuk excel
if ($proses == 'excel') {
    $html .= "<thead>";
    $html .= "<tr><td colspan=\"17\">".strtoupper("Analisis Biaya Kebun ".$tanggal)."</td></tr>";
    $html .= "</thead>";
}

$column = ['Ha', 'Tk', 'Jjg', 'Akp', 'Bjr', 'Kg'];

// Header columns
$headerTabel = "<thead>
                    <tr class=\"rowheader\">
                        <th rowspan=\"2\" colspan=\"1\" {$bgHeader}>Kebun</th>
                        <th rowspan=\"2\" colspan=\"1\" width=\"100px\" {$bgHeader}>Afd</th>
                        <th rowspan=\"1\" colspan=\"6\" {$bgHeader}>Rencana</th>
                        <th rowspan=\"1\" colspan=\"2\" {$bgHeader}>Restan Sebelumnya</th>
                        <th rowspan=\"1\" colspan=\"6\" {$bgHeader}>Realisasi</th>
                        <th rowspan=\"1\" colspan=\"2\" {$bgHeader}>TBS Kirim ke Mill</th>
                        <th rowspan=\"1\" colspan=\"2\" {$bgHeader}>Restan Hi</th>
                        <th rowspan=\"1\" colspan=\"3\" {$bgHeader}>Output/TK</th>
                    </tr>
                    <tr class=\"rowheader\">";
                    foreach($column as $key => $val){
                        $headerTabel .= "<th {$bgHeader}>{$val}</th>";
                    }
                    $headerTabel .= "<th {$bgHeader}>Jjg</th>";
                    $headerTabel .= "<th {$bgHeader}>Kg</th>";
                    foreach($column as $key => $val){
                        $headerTabel .= "<th {$bgHeader}>{$val}</th>";
                    }
                    $headerTabel .= "<th {$bgHeader}>Jjg</th>";
                    $headerTabel .= "<th {$bgHeader}>Kg Hi</th>";
                    $headerTabel .= "<th {$bgHeader}>Jjg</th>";
                    $headerTabel .= "<th {$bgHeader}>Kg</th>";
                    $headerTabel .= "<th {$bgHeader}>Ha</th>";
                    $headerTabel .= "<th {$bgHeader}>Jjg</th>";
                    $headerTabel .= "<th {$bgHeader}>Kg</th>";
                    $headerTabel .= "</tr>   
                </thead>";

$html .= $stream;
$html .= $headerTabel;
$html .= "<tbody>";

foreach($afd as $kodeorg => $listAfdeling){
    $rowspan = count($listAfdeling) + 1;
    $first = true;

    foreach($listAfdeling as $afdkode => $afdName){
        $html .= "<tr class='rowcontent'>";

        if($first){
            $html .= "<td rowspan='".$rowspan."' align='center'>".getNamaOrg($kodeorg)."</td>";
            $first = false;
        }
        
        // rencana

        $rencana_akp[$afdkode] = ($rencana[$afdkode]['jmlhpokok'] == 0 ? 0 : $rencana[$afdkode]['jjgmasak'] / $rencana[$afdkode]['jmlhpokok'] * 100);
        $rencana_bjr[$afdkode] = ($rencana[$afdkode]['jjgmasak'] == 0 ? 0 : $rencana[$afdkode]['kg'] / $rencana[$afdkode]['jjgmasak']);

        $html .= "<td>".$afdkode." - ".$afdName."</td>";
        $html .= "<td>".number_format($rencana[$afdkode]['haesok'],2,',','.')."</td>";
        $html .= "<td>".number_format($rencana[$afdkode]['hkdigunakan'],2,',','.')."</td>";
        $html .= "<td>".number_format($rencana[$afdkode]['jjgmasak'],2,',','.')."</td>";
        $html .= "<td>".number_format($rencana_akp[$afdkode],2,',','.')."%</td>";
        $html .= "<td>".number_format($rencana_bjr[$afdkode],2,',','.')."</td>";
        $html .= "<td>".number_format($rencana[$afdkode]['kg'],2,',','.')."</td>";

        // restan kemarin
        $style = "";
        if ($restan['jjg'][$afdkode]['kemarin'] < 0){
            $style = "style='background-color:red'; title='Restan tidak boleh kurang dari 0';";
        }
        $style2 = "";
        if ($restan['kg'][$afdkode]['kemarin'] < 0){
            $style2 = "style='background-color:red'; title='Restan tidak boleh kurang dari 0';";
        }
        $html .= "<td {$style}>".number_format($restan['jjg'][$afdkode]['kemarin'],2,',','.')."</td>";
        $html .= "<td {$style2}>".number_format($restan['kg'][$afdkode]['kemarin'],2,',','.')."</td>";

        // realisasi
        $html .= "<td>".number_format($realisasi[$afdkode]['ha'],2,',','.')."</td>";
        $html .= "<td>".number_format($tk[$afdkode],2,',','.')."</td>";
        $html .= "<td>".number_format($realisasi[$afdkode]['jjgpanen'],2,',','.')."</td>";
        $html .= "<td>".number_format($akp[$afdkode],2,',','.')."%</td>";
        $html .= "<td>".number_format(($realisasi[$afdkode]['jjgpanen'] == 0 ? 0 : $realisasi[$afdkode]['kg'] / $realisasi[$afdkode]['jjgpanen']),2,',','.')."</td>";
        $html .= "<td>".number_format($realisasi[$afdkode]['kg'],2,',','.')."</td>";

        // tbs kirim ke mill
        $html .= "<td>".number_format($tbskirim[$afdkode]['jjg'],2,',','.')."</td>";
        $html .= "<td>".number_format($tbskirim[$afdkode]['kgwbnetto'],2,',','.')."</td>";

        // restan hi
        $sisa_jjg = $realisasi[$afdkode]['jjgpanen'] - $tbskirim[$afdkode]['jjg'];
        $sisa_kg = $realisasi[$afdkode]['kg'] - $tbskirim[$afdkode]['kgwbnetto'];
        $html .= "<td " . ($sisa_jjg < 0 ? "style='background-color:red'; title='Restan tidak boleh kurang dari 0';" : "") . ">" . number_format($sisa_jjg, 2, ',', '.') . "</td>";
        $html .= "<td " . ($sisa_kg < 0 ? "style='background-color:red'; title='Restan tidak boleh kurang dari 0';" : "") . ">" . number_format($sisa_kg, 2, ',', '.') . "</td>";

        // output/tk
        $output_ha = ($tk[$afdkode] == 0 ? 0 : $realisasi[$afdkode]['ha'] / $tk[$afdkode]);
        $output_jjg = ($tk[$afdkode] == 0 ? 0 : $realisasi[$afdkode]['jjgpanen'] / $tk[$afdkode]);
        $output_kg = ($tk[$afdkode] == 0 ? 0 : $realisasi[$afdkode]['kg'] / $tk[$afdkode]);
        $html .= "<td>" . number_format($output_ha, 2, ',', '.') . "</td>";
        $html .= "<td>" . number_format($output_jjg, 2, ',', '.') . "</td>";
        $html .= "<td>" . number_format($output_kg, 2, ',', '.') . "</td>";

        $html .= "</tr>";

        $total_ha[$kodeorg] += $rencana[$afdkode]['haesok'];
        $total_tk[$kodeorg] += $rencana[$afdkode]['hkdigunakan'];
        $total_pokok[$kodeorg] += $rencana[$afdkode]['jmlhpokok'];
        $total_jjg[$kodeorg] += $rencana[$afdkode]['jjgmasak'];
        // $total_akp[$kodeorg] += $rencana[$afdkode]['persenbuahmatang'];
        // $total_bjr[$kodeorg] += $rencana[$afdkode]['bjr'];
        $total_kg[$kodeorg] += $rencana[$afdkode]['kg'];
        $total_restan_jjg[$kodeorg] += $restan['jjg'][$afdkode]['kemarin'];
        $total_restan_kg[$kodeorg] += $restan['kg'][$afdkode]['kemarin'];
        $total_realisasi_ha[$kodeorg] += $realisasi[$afdkode]['ha'];
        $total_realisasi_tk[$kodeorg] += $realisasi[$afdkode]['tk'];
        $total_realisasi_jjg[$kodeorg] += $realisasi[$afdkode]['jjgpanen'];
        // $total_realisasi_akp[$kodeorg] += $akp[$afdkode];
        $total_realisasi_pokok[$kodeorg] += $setupblok[$afdkode]['jumlahpokok'];
        // $total_realisasi_bjr[$kodeorg] += $realisasi[$afdkode]['bjr'];
        $total_realisasi_kg[$kodeorg] += $realisasi[$afdkode]['kg'];
        $total_tbskirim_jjg[$kodeorg] += $tbskirim[$afdkode]['jjg'];
        $total_tbskirim_kg[$kodeorg] += $tbskirim[$afdkode]['kgwbnetto'];
        $total_sisa_jjg[$kodeorg] += $sisa_jjg;
        $total_sisa_kg[$kodeorg] += $sisa_kg;
        $total_output_ha[$kodeorg] += $output_ha;
        $total_output_jjg[$kodeorg] += $output_jjg;
        $total_output_kg[$kodeorg] += $output_kg;
    }

    // akp
    $rencana_akp_kebun = ($total_pokok[$kodeorg] == 0 ? 0 : $total_jjg[$kodeorg] / $total_pokok[$kodeorg]) * 100;
    $akp_kebun[$kodeorg] = ($total_realisasi_pokok[$kodeorg] == 0 ? 0 : $total_realisasi_jjg[$kodeorg] / $total_realisasi_pokok[$kodeorg]) * 100;
    // bjr
    $rencana_bjr_kebun = ($total_jjg[$kodeorg] == 0 ? 0 : $total_kg[$kodeorg] / $total_jjg[$kodeorg]);
    $bjr_kebun[$kodeorg] = ($total_realisasi_jjg[$kodeorg] == 0 ? 0 : $total_realisasi_kg[$kodeorg] / $total_realisasi_jjg[$kodeorg]);
    
    $html .= "<tr class='rowcontent' style='background-color:#e6e6e6;'>";
    $html .= "<td align='center'></td>";
    $html .= "<td>" . number_format($total_ha[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_tk[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_jjg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($rencana_akp_kebun, 2, ',', '.') . "%</td>";
    $html .= "<td>" . number_format($rencana_bjr_kebun, 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_kg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_restan_jjg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_restan_kg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_realisasi_ha[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_realisasi_tk[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_realisasi_jjg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($akp_kebun[$kodeorg], 2, ',', '.') . "%</td>";
    $html .= "<td>" . number_format($bjr_kebun[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_realisasi_kg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_tbskirim_jjg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_tbskirim_kg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_sisa_jjg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_sisa_kg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_output_ha[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_output_jjg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "<td>" . number_format($total_output_kg[$kodeorg], 2, ',', '.') . "</td>";
    $html .= "</tr>";
}

// Total
$rencana_akp_total = (array_sum($total_pokok) == 0 ? 0 : array_sum($total_jjg) / array_sum($total_pokok)) * 100;
$rencana_bjr_total = (array_sum($total_jjg) == 0 ? 0 : array_sum($total_kg) / array_sum($total_jjg));
$akp_total = (array_sum($total_realisasi_pokok) == 0 ? 0 : array_sum($total_realisasi_jjg) / array_sum($total_realisasi_pokok)) * 100;
$bjr_total = (array_sum($total_realisasi_jjg) == 0 ? 0 : array_sum($total_realisasi_kg) / array_sum($total_realisasi_jjg));

$html .= "<tr class='rowcontent' style='background-color:#ADD8E6;'>";
$html .= "<td colspan='2' align='center'>Total</td>";
$html .= "<td>" . number_format(array_sum($total_ha), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_tk), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_jjg), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format($rencana_akp_total, 2, ',', '.') . "%</td>";
$html .= "<td>" . number_format($rencana_bjr_total, 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_kg), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_restan_jjg), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_restan_kg), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_realisasi_ha), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_realisasi_tk), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_realisasi_jjg), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format($akp_total, 2, ',', '.') . "%</td>";
$html .= "<td>" . number_format($bjr_total, 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_realisasi_kg), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_tbskirim_jjg), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_tbskirim_kg), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_sisa_jjg), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_sisa_kg), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_output_ha), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_output_jjg), 2, ',', '.') . "</td>";
$html .= "<td>" . number_format(array_sum($total_output_kg), 2, ',', '.') . "</td>";
$html .= "</tr>";

$html .= "</tbody>";
$html .= "</table>";

$html .= "<div style='clear:both; margin-top:20px;'></div>";

// Header columns
$head = "<thead>
                    <tr class=\"rowheader\">
                        <th rowspan=\"2\" colspan=\"1\" {$bgHeader}>Kebun</th>
                        <th rowspan=\"2\" colspan=\"1\" width=\"100px\" {$bgHeader}>Afd</th>
                        <th rowspan=\"2\" colspan=\"1\" {$bgHeader}>TT</th>
                        <th rowspan=\"2\" colspan=\"1\" {$bgHeader}>Blok</th>
                        <th rowspan=\"1\" colspan=\"6\" {$bgHeader}>Analisa %</th>
                        <th rowspan=\"2\" colspan=\"1\" {$bgHeader}>Keterangan</th>
                    </tr>
                    <tr class=\"rowheader\">";
                    $head .= "<th {$bgHeader}>Ha</th>";
                    $head .= "<th {$bgHeader}>Tk</th>";
                    $head .= "<th {$bgHeader}>Jjg</th>";
                    $head .= "<th {$bgHeader}>Akp</th>";
                    $head .= "<th {$bgHeader}>Bjr</th>";
                    $head .= "<th {$bgHeader}>Kg</th>";
                    $head .= "</tr>   
                </thead>";

$html .= "<table cellspacing=\"3\" cellpadding=\"5\" border=\"".$border."\">";
$html .= $head;
$html .= "<tbody>";

foreach($afd as $kodeorg => $listAfdeling){
    $rowspan = count($listAfdeling);
    $first = true;

    foreach($listAfdeling as $afdkode => $afdName){
        $html .= "<tr class='rowcontent'>";

        if($first){
            $html .= "<td rowspan='".$rowspan."' align='center'>".getNamaOrg($kodeorg)."</td>";
            $first = false;
        }

        $html .= "<td>".$afdName."</td>";
        $html .= "<td>".implode(", ", $tahuntanam[$afdkode])."</td>";
        $html .= "<td width=400px>".implode(", ", $listBlok[$afdkode])."</td>";

        //ha
        $html .= "<td>".($rencana[$afdkode]['haesok'] == 0 ? 0 : number_format($realisasi[$afdkode]['ha'] / $rencana[$afdkode]['haesok'] * 100, 2, ',', '.'))."%</td>";

        //tk
        $html .= "<td>".($rencana[$afdkode]['hkdigunakan'] == 0 ? 0 : number_format($realisasi[$afdkode]['tk'] / $rencana[$afdkode]['hkdigunakan'] * 100, 2, ',', '.'))."%</td>";

        //jjg
        $html .= "<td>".($rencana[$afdkode]['jjgmasak'] == 0 ? 0 : number_format($realisasi[$afdkode]['jjgpanen'] / $rencana[$afdkode]['jjgmasak'] * 100, 2, ',', '.'))."%</td>";

        //akp
        $html .= "<td>".($rencana_akp[$afdkode] == 0 ? 0 : number_format($akp[$afdkode] / $rencana_akp[$afdkode] * 100, 2, ',', '.') )."%</td>";

        //bjr
        $real_bjr = ($realisasi[$afdkode]['jjgpanen'] == 0 ? 0 : $realisasi[$afdkode]['kg'] / $realisasi[$afdkode]['jjgpanen']);
        $html .= "<td>".($rencana_bjr[$afdkode] == 0 ? 0 : number_format($real_bjr / $rencana_bjr[$afdkode] * 100, 2, ',', '.') )."%</td>";

        //kg
        $html .= "<td>".($rencana[$afdkode]['kg'] == 0 ? 0 : number_format($realisasi[$afdkode]['kg'] / $rencana[$afdkode]['kg'] * 100, 2, ',', '.') )."%</td>";

        
        //keterangan
        $ket = [];
        if(isset($dataabsen[$afdkode])){
            foreach($dataabsen[$afdkode] as $kode => $jumlah){
                if($kode == 'H'){
                    continue;
                }
                $keterangan = $kodeabsen[$kode] ?? $kode; // fallback kalau tidak ada
                $ket[] = $jumlah." HK ".$keterangan;
            }
        }

        $html .= "<td>".implode(', ', $ket)."</td>";
        $html .= "</tr>";
    }
}

// Total
$analisaha = (array_sum($total_ha) == 0 ? 0 : array_sum($total_realisasi_ha) / array_sum($total_ha) * 100);
$analisatk = (array_sum($total_tk) == 0 ? 0 : array_sum($total_realisasi_tk) / array_sum($total_tk) * 100);
$analisajjg = (array_sum($total_jjg) == 0 ? 0 : array_sum($total_realisasi_jjg) / array_sum($total_jjg) * 100);
$analisaakp = ($rencana_akp_total == 0 ? 0 : $akp_total / $rencana_akp_total * 100);
$analisabjr = ($rencana_bjr_total == 0 ? 0 : $bjr_total / $rencana_bjr_total * 100);
$analisakg = (array_sum($total_kg) == 0 ? 0 : array_sum($total_realisasi_kg) / array_sum($total_kg) * 100);

$html .= "<tr class='rowcontent' style='background-color:#ADD8E6;'>";
$html .= "<td colspan='4' align='center'>Total</td>";

// Analisa %
// ha
$html .= "<td>".number_format($analisaha, 2, ',', '.')."%</td>";
// tk
$html .= "<td>".number_format($analisatk, 2, ',', '.')."%</td>";
// jjg
$html .= "<td>".number_format($analisajjg, 2, ',', '.')."%</td>";
// akp
$html .= "<td>".number_format($analisaakp, 2, ',', '.')."%</td>";
// bjr
$html .= "<td>".number_format($analisabjr, 2, ',', '.')."%</td>";
// kg
$html .= "<td>".number_format($analisakg, 2, ',', '.')."%</td>";

// keterangan
$html .= "<td></td>";
$html .= "</tr>";


$html .= "</tbody>";
$html .= "</table>";

switch($proses)
{
	case'preview':
	    echo $html;
	break;
	case'excel':
        $html.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $namaFileExcel="Laporan Analisa Produksi_".$tanggal;
        if(strlen($html)>0)
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
            if(!fwrite($handle,$html))
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