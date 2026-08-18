<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');

if(isset($_POST['proses'])){
	$proses=$_POST['proses'];
}else{
	$proses=$_GET['proses'];
}

$periode = checkPostGet('periodeId3','');
$unit = checkPostGet('unitId3','');
$intiplasma = checkPostGet('intiplasma3','');

if($periode=='') {
    exit("Error : Periode tidak boleh kosong");
}

$namaOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');

// Helper to get total days in month
$tahun = substr($periode,0,4);
$bulan = substr($periode,5,2);
$jumlahHari = date('t', strtotime($periode."-01"));

// Get the previous month
$prevMonthDate = date('Y-m-d', strtotime($periode."-01 -1 month"));
$tglMulaiPrefix = substr($prevMonthDate, 0, 7);
$tglMulai = $tglMulaiPrefix."-01"; // Start of previous month
$tglSelesai = $periode."-".$jumlahHari;

$border='0';
$bgHeader='';
$tabelHtml = "";
if($proses=='excel'){
    $border=1;
    $bgHeader="bgcolor=#DEDEDE align=center";
    $tabelHtml.="<table cellspacing=1 cellpadding=1 border=0>";
    $tabelHtml.="<tr><td colspan=".($jumlahHari+10)." >".strtoupper("ROTASI PANEN")."</td></tr>";
    $tabelHtml.="<tr><td colspan=".($jumlahHari+10).">".$_SESSION['lang']['periode']." : ".$periode."</td></tr></table>";
}

$sHarvest = "select blok, tanggalpanen from ".$dbname.".kebun_spb_vw4 
             where blok like '".$unit."%' and tanggalpanen between '".$tglMulai."' and '".$tglSelesai."'
             order by blok, tanggalpanen asc";
$qHarvest = $owlPDO->query($sHarvest) or die(" Gagal Query Panen");
$qHarvest->setFetchMode(PDO::FETCH_ASSOC);
$harvestDates = array();
$groupedHarvestDates = array();
while($rHarvest = $qHarvest->fetch()){
    $harvestDates[$rHarvest['blok']][$rHarvest['tanggalpanen']] = true;
    $divisi = substr($rHarvest['blok'],4,2);
    $groupedHarvestDates[$divisi][$rHarvest['blok']][$rHarvest['tanggalpanen']] = true;
}
ksort($groupedHarvestDates);

// Fetch Previous Harvest
$prevHarvests = array();
$sPrev = "select blok, max(tanggalpanen) as last_date from ".$dbname.".kebun_spb_vw4 
          where blok like '".$unit."%' and tanggalpanen < '".$tglMulai."'
          group by blok";

$qPrev = $owlPDO->query($sPrev) or die(" Gagal Query Panen Sebelumnya");
$qPrev->setFetchMode(PDO::FETCH_ASSOC);
while($rPrev = $qPrev->fetch()){
    $prevHarvests[$rPrev['blok']] = $rPrev['last_date'];
}

$headerTabel = "<thead>
            <tr class=rowheader>
                <th rowspan=3 ".$bgHeader.">Div.</th>
                <th rowspan=3 ".$bgHeader.">Blok</th>
                <th rowspan=3 ".$bgHeader.">HP</th>
                <th rowspan=3 ".$bgHeader.">ROTASI</th>
                <th rowspan=3 ".$bgHeader.">Uraian</th>
                <th colspan=3 ".$bgHeader.">3 HARI TERAKHIR BULAN LALU</th>
                <th colspan='".$jumlahHari."' ".$bgHeader.">TANGGAL</th>
                <th rowspan=3 ".$bgHeader.">TOTAL HP</th>
                <th rowspan=3 ".$bgHeader.">TOTAL Rotasi</th>
            </tr>
            <tr class=rowheader>";
            for($i=2; $i>=0; $i--) {
                $t = strtotime($periode."-01 -".($i+1)." days");
                $d = date('d', $t);
                $headerTabel .= "<th ".$bgHeader.">".$d."</th>";
            }
            for($i=1; $i<=$jumlahHari; $i++) {
                $headerTabel .= "<th ".$bgHeader.">".$i."</th>";
            }
$headerTabel .= "   </tr>
            <tr class=rowheader>";
            for($i=2; $i>=0; $i--) {
                $t = strtotime($periode."-01 -".($i+1)." days");
                $dayName = strtoupper(substr(hari(date('Y-m-d', $t)),0,3));
                $headerTabel .= "<th ".$bgHeader.">".$dayName."</th>";
            }
            for($i=1; $i<=$jumlahHari; $i++) {
                $t = strtotime($periode."-".sprintf('%02d',$i));
                $dayName = strtoupper(substr(hari(date('Y-m-d', $t)),0,3));
                $headerTabel .= "<th ".$bgHeader.">".$dayName."</th>";
            }
$headerTabel .= "   </tr>
        </thead>";

// $tabelHtml .= "<br><fieldset><legend><b>DIVISI: ".$kodeDivisi."</b></legend>";
$tabelHtml .= "<table cellspacing=1 cellpadding=5 border='".$border."' class=sortable>";
$tabelHtml .= $headerTabel;
$tabelHtml .= "<tbody>";

if(empty($groupedHarvestDates)) {
    $tabelHtml .= "<tr class=rowcontent><td colspan='".($jumlahHari + 10)."' align=center>".$_SESSION['lang']['dataempty']."</td></tr>";
    $tabelHtml .= "</tbody></table>";
} else {
    foreach($groupedHarvestDates as $kodeDivisi => $daftarBlok) {
               
        $totalKeseluruhanPutaranPanen = 0;
        $totalKeseluruhanInterval = 0;
        $totalKeseluruhanHariPanen = 0;
        $totalBaris = 0;
        $totalRataRataRotasi = 0;

        foreach($daftarBlok as $kodeBlok => $dataHarian) {
            $blok = $kodeBlok;
            
            $rowRotasi = array();
            $rowHP = array();
            $putaranPanen = 0; // Total for selected month
            $jumlahHariPanen = 0; // Total days for selected month
            $totalInterval = 0; // Total interval for selected month
            
            $lastHarvest = isset($prevHarvests[$kodeBlok]) ? $prevHarvests[$kodeBlok] : null;
            $anchorInterval = 0;
            $firstHarvestOverall = true;
            
            // All calculation days (Full previous month + current month)
            $calcDays = array();
            $startCalc = new DateTime($tglMulai);
            $endCalc = new DateTime($tglSelesai);
            $intervalIter = new DateInterval('P1D');
            $periodIter = new DatePeriod($startCalc, $intervalIter, $endCalc->modify('+1 day'));
            foreach($periodIter as $dt) {
                $calcDays[] = $dt->format("Y-m-d");
            }
            
            // Monthly HP tracker
            $monthlyHP = 0;
            $currentTraversedMonth = "";
            
            foreach($calcDays as $tanggalSekarang) {
                $monthOfDate = substr($tanggalSekarang, 0, 7);
                if($monthOfDate != $currentTraversedMonth) {
                    $monthlyHP = 0; // Reset HP at month boundary
                    $currentTraversedMonth = $monthOfDate;
                }
                
                $isSelectedMonth = ($monthOfDate == $periode);
                
                if(isset($dataHarian[$tanggalSekarang])) {
                    $rawInterval = 0;
                    if($lastHarvest != null) {
                        $diff = strtotime($tanggalSekarang) - strtotime($lastHarvest);
                        $rawInterval = round($diff / (60 * 60 * 24));
                    }
                    
                    $isConsecutive = ($rawInterval <= 1 && !$firstHarvestOverall);
                    
                    if(!$isConsecutive) {
                        $monthlyHP++;
                        if($isSelectedMonth) {
                            $putaranPanen++;
                        }
                        $interval = $rawInterval;
                    } else {
                        $interval = $anchorInterval + 1;
                    }
                    
                    if($isSelectedMonth) {
                        $jumlahHariPanen++;
                        $totalInterval += $interval;
                    }
                    
                    $rowRotasi[$tanggalSekarang] = $interval;
                    $rowHP[$tanggalSekarang] = ($monthlyHP > 0) ? $monthlyHP : "";
                    
                    $anchorInterval = $interval;
                    $lastHarvest = $tanggalSekarang;
                    $firstHarvestOverall = false;
                } else {
                    $rowRotasi[$tanggalSekarang] = "";
                    $rowHP[$tanggalSekarang] = "";
                }
            }
            
            // Days to display in table
            $displayDays = array();
            for($i=2; $i>=0; $i--) $displayDays[] = date('Y-m-d', strtotime($periode."-01 -".($i+1)." days"));
            for($i=1; $i<=$jumlahHari; $i++) $displayDays[] = $periode."-".sprintf('%02d',$i);
            
            $rataRataRotasi = ($jumlahHariPanen > 0) ? round($totalInterval / $jumlahHariPanen, 0) : 0;
            
            $totalRataRataRotasi += $rataRataRotasi;
            $totalKeseluruhanPutaranPanen += $putaranPanen;
            $totalKeseluruhanInterval += $totalInterval;
            $totalKeseluruhanHariPanen += $jumlahHariPanen;
            
            // --- Row 1: ROTASI ---
            $tabelHtml .= "<tr class=rowcontent>
                        <td>".$kodeDivisi."</td>
                        <td>".substr($blok,8,2)."</td>
                        <td></td>
                        <td align=right>".$putaranPanen."</td>
                        <td>Rotasi</td>";
            foreach($displayDays as $tanggalSekarang) {
                $tabelHtml .= "<td align=center>".$rowRotasi[$tanggalSekarang]."</td>";
            }
            $tabelHtml .= "   <td align=center>".$putaranPanen." Kali</td>
                        <td align=right>".$rataRataRotasi."</td>
                    </tr>";
            
            // --- Row 2: H.P ---
            $tabelHtml .= "<tr class=rowcontent>
                        <td></td>
                        <td></td>
                        <td align=right>".$rataRataRotasi."</td>
                        <td></td>
                        <td>H.P</td>";
            foreach($displayDays as $tanggalSekarang) {
                $tabelHtml .= "<td align=center>".$rowHP[$tanggalSekarang]."</td>";
            }
            $tabelHtml .= "   <td></td>
                        <td></td>
                    </tr>";

            $totalBaris++;
        }

        // --- Footer Row: TOTAL & RATA-RATA per Divisi ---
        $rataRataKeseluruhanPutaranPanen = ($totalKeseluruhanPutaranPanen > 0) ? round($totalKeseluruhanPutaranPanen / $totalBaris, 0) : 0;
        $rataRataKeseluruhanRotasi = ($totalKeseluruhanInterval > 0) ? round($totalRataRataRotasi / $totalBaris, 0) : 0;

        $tabelHtml .= "<tr class=rowcontent>
                    <td colspan=".($jumlahHari+8)." align=center style='font-weight:bold; background-color: #d3d3d3;'>".$_SESSION['lang']['total'] ." ".strtoupper($_SESSION['lang']['divisi'])." ".$kodeDivisi."</td>
                    <td align=center style='font-weight:bold; background-color: #d3d3d3;'>".number_format($totalKeseluruhanPutaranPanen,0)." Kali</td>
                    <td align=right style='font-weight:bold; background-color: #d3d3d3;'></td>
                </tr>";

        $tabelHtml .= "<tr class=rowcontent>
                    <td colspan=".($jumlahHari+8)." align=center style='font-weight:bold; background-color: #d3d3d3;'>".$_SESSION['lang']['rerata']." ".strtoupper($_SESSION['lang']['divisi'])." ".$kodeDivisi."</td>
                    <td align=center style='font-weight:bold; background-color: #d3d3d3;'>".number_format($rataRataKeseluruhanPutaranPanen,0)." Kali</td>
                    <td align=right style='font-weight:bold; background-color: #d3d3d3;'>".number_format($rataRataKeseluruhanRotasi,0)."</td>
                </tr>";

    }
}



switch($proses)
{
	case'preview':
        $tabelHtml .= "</tbody></table></fieldset>";
	    echo $tabelHtml;
	break;
	case'excel':
        $tabelHtml.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $namaFileExcel="Rotasi_Panen_".$unit."_".$periode;
        if(strlen($tabelHtml)>0)
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
            if(!fwrite($handle,$tabelHtml))
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
