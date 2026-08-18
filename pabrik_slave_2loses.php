<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

$alphabet = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');

$proses = checkPostGet('proses', '');
$tgl_1 = checkPostGet('tgl1', '');
$tgl_2 = checkPostGet('tgl2', '');
$tanggl = checkPostGet('tglRe', '');
$kdPabrik = checkPostGet('kdPabrik', '');
if ($tgl_1 == '' || $tgl_2 == '') {
    exit("Error:Date required");
}
if ($kdPabrik == '') {
    exit("Error: Mill code required");
}
if (strlen($tgl_1) != 10 || strlen($tgl_2) != 10) {
    exit("Error: Invalid date format");
}
$tgl1 = $tgl_1;
$tgl22 = $tgl_2;
$optSupp = makeOption($dbname, 'log_5supplier', 'kodetimbangan,namasupplier');
$optNm = makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$tgl = explode("-", $tgl_1);
$tgl_1 = $tgl[2] . "-" . $tgl[1] . "-" . $tgl[0];
$tgl2 = explode("-", $tgl_2);
$tgl_2 = $tgl2[2] . "-" . $tgl2[1] . "-" . $tgl2[0];

$digit = 3;
$dzArr = array();
$kmrn = strtotime('-1 day', strtotime($tgl_1));
$kmrn = date('Y-m-d', $kmrn);

function dates_inbetween($date1, $date2)
{
    $day = 60 * 60 * 24;
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);
    $days_diff = round(($date2 - $date1) / $day); // Unix time difference devided by 1 day to get total days in between
    $dates_array = array();
    $dates_array[] = date('Y-m-d', $date1);
    for ($x = 1; $x < $days_diff; $x++) {
        $dates_array[] = date('Y-m-d', ($date1 + ($day * $x)));
    }
    $dates_array[] = date('Y-m-d', $date2);
    return $dates_array;
}

if (($tgl_1 != '') && ($tgl_2 != '')) {
    $tgl1 = tanggalsystem($tgl1);
    $tgl22 = tanggalsystem($tgl22);
}
$test = dates_inbetween($tgl1, $tgl22);

$sData = "select distinct * from " . $dbname . ".pabrik_produksi where 
        kodeorg='" . $kdPabrik . "' and tanggal between '" . $tgl_1 . "' and '" . $tgl_2 . "' order by tanggal asc";
$qData = $owlPDO->query($sData) or die(print " Gagal: " . PDOException::getMessage());
$qData->setFetchMode(PDO::FETCH_ASSOC);
while ($rData = $qData->fetch()) {
    $dtTgl[$rData['tanggal']] = $rData['tanggal'];
    $dtTbsDiolah[$rData['tanggal']] = $rData['tbsdiolahnetto'];
    $dtFruit[$rData['tanggal']] = $rData['fruitineb'];
    $dtFibre[$rData['tanggal']] = $rData['fibre'];
    $dtEbstalk[$rData['tanggal']] = $rData['ebstalk'];
    $dtNut[$rData['tanggal']] = $rData['nut'];
    $dtEffluent[$rData['tanggal']] = $rData['effluent'];
    $dtSolid[$rData['tanggal']] = $rData['soliddecanter'];
    $dtusbcpo[$rData['tanggal']] = $rData['dobi']; //untuk dobi

    $dtFruitinebker[$rData['tanggal']] = $rData['fruitinebker'];
    $dtCyclone[$rData['tanggal']] = $rData['cyclone'];
    $dtLtds[$rData['tanggal']] = $rData['ltds'];
    $dtClaybath[$rData['tanggal']] = $rData['claybath'];
    $dtusbpk[$rData['tanggal']] = $rData['usbpk'];
    $dthydrocyclone[$rData['tanggal']] = $rData['hydrocyclone']; //untuk Centrifuge

    // $dtFruitinebker[$rData['tanggal']]=$rData['fruitinebker'];
    // $dtCyclone[$rData['tanggal']]=$rData['cyclone'];
    // $dtLtds[$rData['tanggal']]=$rData['ltds'];
    // $dtClaybath[$rData['tanggal']]=$rData['claybath'];

    $dtoer[$rData['tanggal']] = $rData['oer'];
    $dtffa[$rData['tanggal']] = $rData['ffa'];
    $dtkadarkotoran[$rData['tanggal']] = $rData['kadarkotoran'];
    $dtkadarair[$rData['tanggal']] = $rData['kadarair'];

    $dtoerpk[$rData['tanggal']] = $rData['oerpk'];
    $dtffapk[$rData['tanggal']] = $rData['ffapk'];
    $dtkadarkotoranpk[$rData['tanggal']] = $rData['kadarkotoranpk'];
    $dtkadarairpk[$rData['tanggal']] = $rData['kadarairpk'];
}


$str = "select * from " . $dbname . ".pabrik_mr_roa where 
        unit='" . $kdPabrik . "' and tanggal between '" . $tgl_1 . "' and '" . $tgl_2 . "' order by tanggal asc";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $kodejenis[substr($bar['parameter'], 0, 1)] = substr($bar['parameter'], 0, 1);
    $kodeparameter[$bar['parameter']] = $bar['parameter'];
    $listparam[substr($bar['parameter'], 0, 1)][$bar['parameter']] = $bar['parameter'];

    $dthi[$bar['tanggal']][substr($bar['parameter'], 0, 1)][$bar['parameter']] = $bar['nilai'];
}

$str = "select * from " . $dbname . ".pabrik_5mr_roa_parameter where status='1'  ";
$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()) {
    $cspanjenis[substr($bar['parameter'], 0, 1)] += 1;
}

$nmjenis = makeOption($dbname, 'pabrik_5mr_roa_jenis', 'jenis,nama');
$nmparam = makeOption($dbname, 'pabrik_5mr_roa_parameter', 'parameter,nama');
// $cspanjenis=count(


/*
echo"<pre>";
print_r($cspanjenis);
echo"</pre>";
*/

$brdr = 0;
$bgclr = "";
if ($proses == 'excel') {
    $bgclr = " bgcolor=#DEDEDE";
    $brdr = 1;
}
if ($proses == 'preview' || $proses == 'excel') {
    $tab .= "<div class='table-scroll'> <table cellpadding=2 cellspacing=1 border=" . $brdr . " class=sortable ><thead>";
    $tab .= "<tr>";
    $tab .= "<th rowspan=3 " . $bgclr . ">" . $_SESSION['lang']['tanggal'] . "</th>";
    $tab .= "<th colspan=2 rowspan=2 align=center " . $bgclr . ">FFB Processing (Kg) </th>";
    $tab .= "<th  colspan=6 align=center  " . $bgclr . ">CPO Quality</th>";
    $tab .= "<th  colspan=5 align=center  " . $bgclr . ">KER Quality</th>";

    $spansdhi = $spansdhi + 2;
    foreach ($kodejenis as $kdjenis) {
        $tab .= "<th align=center colspan=" . (($cspanjenis[$kdjenis] * 2) + 2) . "  " . $bgclr . ">" . $nmjenis[$kdjenis] . "</th>";
        @$spansdhi += ($cspanjenis[$kdjenis]);
    }


    // $tab.="<th  colspan=16 align=center  ".$bgclr.">CPO Loses</th>";
    // $tab.="<th  colspan=10 align=center  ".$bgclr.">Kernel Loses</th>";
    $tab .= "</tr>";


    $tab .= "<tr>";
    $tab .= "<th align=center rowspan=2   " . $bgclr . ">" . $_SESSION['lang']['cpo'] . " (Kg)</th>
        <th align=center rowspan=2  " . $bgclr . ">" . $_SESSION['lang']['oer'] . " (%)</th>
        <th align=center rowspan=2  " . $bgclr . ">(FFa)(%)</th>
        <th align=center rowspan=2  " . $bgclr . ">" . $_SESSION['lang']['kotoran'] . " (%)</th>
        <th align=center rowspan=2  " . $bgclr . ">" . $_SESSION['lang']['kadarair'] . " (%)</th>
        <th align=center rowspan=2  " . $bgclr . ">Dobi (%)</th>";

    $tab .= " <th align=center rowspan=2  " . $bgclr . ">" . $_SESSION['lang']['kernel'] . " (Kg)</th>
		   <th align=center rowspan=2  " . $bgclr . ">KER (%)</th>
		   <th align=center rowspan=2  " . $bgclr . ">(Broken) (%)</th>
		   <th align=center rowspan=2  " . $bgclr . ">" . $_SESSION['lang']['kotoran'] . " (%)</th>
		   <th align=center rowspan=2  " . $bgclr . ">" . $_SESSION['lang']['kadarair'] . " (%)</th>";


    foreach ($kodejenis as $kdjenis) {
        foreach ($kodeparameter as $kdparam) {
            if ($listparam[$kdjenis][$kdparam]) {
                $tab .= "<th colspan=2 align=center>" . $nmparam[$kdparam] . "</th>";
            }
        }
        $tab .= "<th colspan=2 align=center>Total</th>";
    }

    /*		   
$tab.="<th colspan=2 align=center ".$bgclr.">Fruit in EB.</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">EB Stalk</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">Fibre Press</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">Nut</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">Effluent</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">Solid Decanter</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">Centrifuge</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">Total</th>";
//$tab.="<th colspan=2>Sludge Centrifuge</th>";
//$tab.="<th colspan=2>USB</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">Fruit in EB</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">Fibre Cyclone</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">LthS</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">Claybath</th>";
//$tab.="<th colspan=2 align=center ".$bgclr.">Usb</th>";
//$tab.="<th colspan=2 align=center ".$bgclr.">Hydro Cyclone</th>";
$tab.="<th colspan=2 align=center ".$bgclr.">Total</th>";
*/
    $tab .= "</tr><tr>";
    if (empty($listparam)) {
        $spansdhi = 0;
    }
    for ($arre = 0; $arre <= $spansdhi; $arre++) {
        $tab .= "<th align=center " . $bgclr . ">" . $_SESSION['lang']['hi'] . "</th>";
        $tab .= "<th align=center " . $bgclr . ">" . $_SESSION['lang']['sdhi'] . "</th>";
    }
    $tab .= "</tr></thead><tbody>";
    $ared = 0;
    $tgl = 1;
    foreach ($test as $ar => $dtTanggal) {
        $ared++;
        $tglkmrn = strtotime('-1 day', strtotime($dtTanggal));
        $tglkmrn2 = date('Y-m-d', $tglkmrn);

        setIt($des[$tglkmrn2], 0);
        $des[$dtTanggal] = $des[$tglkmrn2] + $dtTbsDiolah[$dtTanggal];

        $tab .= "<tr class=rowcontent>";
        $tab .= "<td align=left>" . tanggalnormal($dtTanggal) . "</td>";
        $tab .= "<td align=right>" . hidezerodecimal($dtTbsDiolah[$dtTanggal], 2) . "</td>";
        $tab .= "<td align=right>" . hidezerodecimal($des[$dtTanggal], 2) . "</td>";
        @$oerprsn = $dtoer[$dtTanggal] / $dtTbsDiolah[$dtTanggal] * 100;
        $tab .= "<td align=right>" . hidezerodecimal($dtoer[$dtTanggal], 2) . "</td>";
        $tab .= "<td align=right>" . hidezerodecimal(fixnan($oerprsn), 2) . "</td>";
        $tab .= "<td align=right>" . $dtffa[$dtTanggal] . "</td>";
        $tab .= "<td align=right>" . $dtkadarkotoran[$dtTanggal] . "</td>";
        $tab .= "<td align=right>" . $dtkadarair[$dtTanggal] . "</td>";
        $tab .= "<td align=right>" . $dtusbcpo[$dtTanggal] . "</td>"; //dobi
        @$oerpkprsn = $dtoerpk[$dtTanggal] / $dtTbsDiolah[$dtTanggal] * 100;
        $tab .= "<td align=right>" . hidezerodecimal($dtoerpk[$dtTanggal], 3) . "</td>";
        // $tab.="<td align=right>".number_format($oerpkprsn,3)."</td>";
        $tab .= "<td align=right>" . hidezerodecimal(fixnan($oerpkprsn), 2) . "</td>";
        $tab .= "<td align=right>" . $dtffapk[$dtTanggal] . "</td>";
        $tab .= "<td align=right>" . $dtkadarkotoranpk[$dtTanggal] . "</td>";
        $tab .= "<td align=right>" . $dtkadarairpk[$dtTanggal] . "</td>";


        foreach ($kodejenis as $kdjenis) {
            foreach ($kodeparameter as $kdparam) {
                if ($listparam[$kdjenis][$kdparam]) {
                    @$dtsdhi[$dtTanggal][$kdjenis][$kdparam] = fixnan((($dthi[$dtTanggal][$kdjenis][$kdparam] * $dtTbsDiolah[$dtTanggal]) + ($dtsdhi[$tglkmrn2][$kdjenis][$kdparam] * $des[$tglkmrn2])) / $des[$dtTanggal]);
                    @$ttlhi[$dtTanggal][$kdjenis] += $dthi[$dtTanggal][$kdjenis][$kdparam];
                    $tab .= "<td align=center>" . hidezerodecimal($dthi[$dtTanggal][$kdjenis][$kdparam], $digit) . "</td>";
                    $tab .= "<td align=center>" . hidezerodecimal($dtsdhi[$dtTanggal][$kdjenis][$kdparam], $digit) . "</td>";
                }
            }
            @$ttlsdhi[$dtTanggal][$kdjenis] = fixnan((($ttlhi[$dtTanggal][$kdjenis] * $dtTbsDiolah[$dtTanggal]) + ($ttlsdhi[$tglkmrn2][$kdjenis] * $des[$tglkmrn2])) / $des[$dtTanggal]);
            $tab .= "<td align=center>" . hidezerodecimal($ttlhi[$dtTanggal][$kdjenis], $digit) . "</td>";
            $tab .= "<td align=center>" . hidezerodecimal($ttlsdhi[$dtTanggal][$kdjenis], $digit) . "</td>";
        }
    }


    $tab .= "</tbody></table></div>";
}
switch ($proses) {
    case 'preview':
        echo $tab;
        break;

    case 'excel':
        $tab .= "Print Time:" . date('Y-m-d H:i:s') . "<br>By:" . $_SESSION['empl']['name'];
        $tglSkrg = date("Ymd");
        $nop_ = "cpo_kernel_loses";
        if (strlen($tab) > 0) {
            if ($handle = opendir('tempExcel')) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != ".." && $file != "index.html") {
                        @unlink('tempExcel/' . $file);
                    }
                }
                closedir($handle);
            }
            $handle = fopen("tempExcel/" . $nop_ . ".xls", 'w');
            if (!fwrite($handle, $tab)) {
                echo "<script language=javascript1.2>
                parent.window.alert('Can't convert to excel format');
                </script>";
                exit;
            } else {
                echo "<script language=javascript1.2>
                window.location='tempExcel/" . $nop_ . ".xls';
                </script>";
            }
            fclose($handle);
        }
        break;
    case 'getKodeorg':
        $optorg = "<option value=''>" . $_SESSION['lang']['all'] . "</option>";
        if ($tipeIntex == 1) {
            //$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk in(select induk from ".$dbname.".organisasi where tipe='PABRIK') order by namaorganisasi asc";
            $sOrg = "SELECT namaorganisasi,kodeorganisasi FROM " . $dbname . ".organisasi WHERE tipe='KEBUN' and induk ='PMO' order by namaorganisasi asc";
        } elseif ($tipeIntex == 0) {
            $sOrg = "SELECT namasupplier,`kodetimbangan` FROM " . $dbname . ".log_5supplier WHERE substring(kodekelompok,1,1)='S' and kodetimbangan!='NULL' order by namasupplier asc"; //echo "warning:".$sOrg;
        } elseif ($tipeIntex == 2) {
            //$sOrg="SELECT namaorganisasi,kodeorganisasi FROM ".$dbname.".organisasi WHERE tipe='KEBUN' and induk not in(select induk from ".$dbname.".organisasi where tipe='PABRIK') order by namaorganisasi asc";
            $sOrg = "SELECT namaorganisasi,kodeorganisasi FROM " . $dbname . ".organisasi WHERE tipe='KEBUN' and induk <>'PMO' order by namaorganisasi asc";
        }
        //echo "warning".$sOrg;exit();
        if ($tipeIntex != 3) {
            $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
            $qOrg->setFetchMode(PDO::FETCH_ASSOC);
            while ($rOrg = $qOrg->fetch()) {
                if ($tipeIntex != 0) {
                    $optorg .= "<option value=" . $rOrg['kodeorganisasi'] . ">" . $rOrg['namaorganisasi'] . "</option>";
                } else {
                    $optorg .= "<option value=" . $rOrg['kodetimbangan'] . ">" . $rOrg['namasupplier'] . "</option>";
                }
            }
        }
        echo $optorg;
        break;
    default:
        break;
}
