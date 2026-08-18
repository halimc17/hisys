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

$periode = checkPostGet('periodeId4','');
$unit = checkPostGet('unitId4','');
$intiplasma = checkPostGet('intiplasma4','');

if($intiplasma!=''){
    $inplas=" AND sb.intiplasma='".$intiplasma."'";
}

$border = ($proses=='excel') ? 1 : 0;

$bgHeader = ($proses=='excel') ? 'bgcolor=#DEDEDE' : '';

if($proses=='excel'){
    $bgHeader = 'bgcolor=#DEDEDE';
    $tabelHtml = "<table cellspacing=1 cellpadding=1 border=0>";
    $tabelHtml .= "<tr><td colspan=60>Detail Produksi Brondol</td></tr>";
    $tabelHtml .= "</table>";
}

$tabelHtml .= "<table cellspacing=1 cellpadding=5 border='".$border."' class=sortable>";
$tabelHtml .= "<thead>";

$tabelHtml .= "<tr class=rowheader>";

$tabelHtml .= "<th rowspan=3 align=center ".$bgHeader.">NO</th>";
$tabelHtml .= "<th colspan=2 rowspan=3 align=center ".$bgHeader.">BLOK</th>";
$tabelHtml .= "<th rowspan=3 align=center ".$bgHeader.">Ha</th>";
$tabelHtml .= "<th rowspan=3 align=center ".$bgHeader.">DIV</th>";

$months = array(
        $_SESSION['lang']['jan'], 
        $_SESSION['lang']['peb'], 
        $_SESSION['lang']['mar'], 
        $_SESSION['lang']['apr'], 
        $_SESSION['lang']['mei'], 
        $_SESSION['lang']['jun'], 
        $_SESSION['lang']['jul'], 
        $_SESSION['lang']['agt'], 
        $_SESSION['lang']['sep'], 
        $_SESSION['lang']['okt'], 
        $_SESSION['lang']['nov'], 
        $_SESSION['lang']['dec']
    );

$yrSuffix = substr($periode, 2, 2);

foreach($months as $mon) {
    $tabelHtml .= "<th colspan=2 align=center ".$bgHeader.">".$mon. '-'.$yrSuffix."</th>";
}
// Total Column
$tabelHtml .= "<th colspan=2 align=center ".$bgHeader.">TOTAL PROD BRONDOLAN</th>";
$tabelHtml .= "</tr>";

// --- HEADER ROW 2 ---
$tabelHtml .= "<tr class=rowheader>";
for($i=0; $i<=12; $i++) { // 12 months + 1 total
    $tabelHtml .= "<th align=center ".$bgHeader.">Netto</th>";
    $tabelHtml .= "<th align=center ".$bgHeader.">Bruto</th>";
}
$tabelHtml .= "</tr>";
$tabelHtml .= "</thead><tbody>";

// --- DATA ROWS ---

// --- DATA QUERY & PROCESSING ---

if($periode=='') $periode = date('Y-m'); 
$tahun = substr($periode,0,4);

// Array untuk menyimpan data group by Tahun Tanam -> Blok
$data = array();
$blockDetails = array(); // Store Luas, Divisi, Kode Blok

$sQuery = "SELECT 
                LEFT(ksd.tanggal,7) AS periode,
                sb.kodeorg,
                sb.tahuntanam,
                sb.luasareaproduktif,
                SUM(ksd.brondolan) AS brondolan
            FROM ".$dbname.".kebun_spb_detail_vw ksd
            JOIN ".$dbname.".setup_blok sb 
                ON ksd.blok = sb.kodeorg 
            WHERE ksd.tanggal LIKE '".$tahun."%'
            AND ksd.kodeorg LIKE '".$unit."%'
            $inplas
            GROUP BY 
                LEFT(ksd.tanggal,7),
                sb.kodeorg,
                sb.tahuntanam,
                sb.luasareaproduktif
            ORDER BY sb.tahuntanam, sb.kodeorg, periode;";

// echo 'DEBUG QUERY: '.'<br>' . $sQuery;

try {
    $qData = $owlPDO->query($sQuery);
    while($r = $qData->fetch(PDO::FETCH_ASSOC)) {
        $tt = $r['tahuntanam'];
        $kodeOrg = $r['kodeorg'];
        $bln = substr($r['periode'], 5, 2); // 01, 02...
        $monthIdx = (int)$bln - 1; // 0-11
        
        if(!isset($blockDetails[$kodeOrg])) {
            // Parse KodeOrg: MHAE01C06I
            // 1-4: Unit (MHAE)
            // 5-6: Divisi
            // 7-9: Blok 1
            // 10: Blok 2
            $divisi = substr($kodeOrg, 4, 2);
            $blok1 = substr($kodeOrg, 6, 3);
            $blok2 = substr($kodeOrg, 9, 1);
            
            $blockDetails[$kodeOrg] = array(
                'luas' => $r['luasareaproduktif'],
                'div' => $divisi,
                'blok1' => $blok1,
                'blok2' => $blok2
            );
        }
        
        // Inisialisasi data bulanan
        if(!isset($data[$tt][$kodeOrg])) {
            $data[$tt][$kodeOrg]['bruto'] = array_fill(0, 12, 0);
            $data[$tt][$kodeOrg]['netto'] = array_fill(0, 12, 0);
        }
        
        $data[$tt][$kodeOrg]['bruto'][$monthIdx] = $r['brondolan'];
        $data[$tt][$kodeOrg]['netto'][$monthIdx] = $r['brondolan'];
    }
} catch (PDOException $e) {
    echo "Query Error: " . $e->getMessage();
}

// --- RENDER TABLE ---

if(empty($data)) {
    $tabelHtml .= "<tr class=rowcontent><td colspan=31 align=center>Data Empty for ".$unit." ".$tahun."</td></tr>";
} else {
    // Sort tahun tanam
    ksort($data);
    
    foreach($data as $tt => $blocks) {
        // TAHUN TANAM Header Row
        $rowStyle = "style='font-weight:bold; background-color:#EFEFEF;'";
        
        $tabelHtml .= "<tr class=rowcontent>";
        $tabelHtml .= "<td colspan=31 align=left ".$rowStyle.">&nbsp;&nbsp;TAHUN TANAM ".$tt."</td>";
        $tabelHtml .= "</tr>";

        // Variable untuk tahunan Subtotal
        $subTotalHa = 0;
        $subTotalKgBruto = array_fill(0, 12, 0);
        $subTotalKgNetto = array_fill(0, 12, 0);
        $subTotalKgHaBruto = array_fill(0, 12, 0); 
        $subTotalKgHaNetto = array_fill(0, 12, 0); 
        
        $subTotalGrandBruto = 0;
        $subTotalGrandNetto = 0;

        $no = 0;
        foreach($blocks as $kodeOrg => $vals) {
            $no++;
            $det = $blockDetails[$kodeOrg];
            $luas = $det['luas'];
            
            $subTotalHa += $luas;

            $tabelHtml .= "<tr class=rowcontent>";
            $tabelHtml .= "<td align=center>".$no."</td>";
            $tabelHtml .= "<td align=center>".$det['blok1']."</td>"; // C06
            $tabelHtml .= "<td align=center>".$det['blok2']."</td>"; // I
            $tabelHtml .= "<td align=right>".number_format($luas,2,',','.')."</td>";
            $tabelHtml .= "<td align=center>".$det['div']."</td>";   // 01

            // Data Bulanan
            $rowTotalBruto = 0;
            $rowTotalNetto = 0;

            for($m=0; $m<12; $m++) {
                $kgBruto = $vals['bruto'][$m];
                $kgNetto = $vals['netto'][$m];
                
                $rowTotalBruto += $kgBruto;
                $rowTotalNetto += $kgNetto;

                // Akumulasi Bulanan
                $subTotalKgBruto[$m] += $kgBruto;
                $subTotalKgNetto[$m] += $kgNetto;

                $tabelHtml .= "<td align=right>".number_format($kgNetto,0,',','.')."</td>";
                $tabelHtml .= "<td align=right>".number_format($kgBruto,0,',','.')."</td>";
            }

            // Total Baris
            // Akumulasi Tahunan
            $subTotalGrandBruto += $rowTotalBruto;
            $subTotalGrandNetto += $rowTotalNetto;

            $tabelHtml .= "<td align=right>".number_format($rowTotalNetto,0,',','.')."</td>";
            $tabelHtml .= "<td align=right>".number_format($rowTotalBruto,0,',','.')."</td>";

            $tabelHtml .= "</tr>";
        }

        // --- TOTAL TAHUN TANAM ROW ---
        $tabelHtml .= "<tr class=rowcontent style='font-weight:bold; background-color:#EFEFEF;'>";
        
        $tabelHtml .= "<td colspan=3 align=center>TOTAL</td>";
        
        $tabelHtml .= "<td align=right>".number_format($subTotalHa,2,',','.')."</td>";
        $tabelHtml .= "<td></td>"; // Div column empty

        for($m=0; $m<12; $m++) {
            $kgSubBruto = $subTotalKgBruto[$m];
            $kgSubNetto = $subTotalKgNetto[$m];
            
            $tabelHtml .= "<td align=right>".number_format($kgSubNetto,0,',','.')."</td>";
            $tabelHtml .= "<td align=right>".number_format($kgSubBruto,0,',','.')."</td>";
        }
        
        // Grand Total Columns untuk tahun
        $tabelHtml .= "<td align=right>".number_format($subTotalGrandNetto,0,',','.')."</td>";
        $tabelHtml .= "<td align=right>".number_format($subTotalGrandBruto,0,',','.')."</td>";

        $tabelHtml .= "</tr>";
    }
}

$tabelHtml .= "</tbody></table>";

switch($proses)
{
	case'preview':
	    echo $tabelHtml;
	break;
	case'excel':
        $tabelHtml.="Print Time:".date('Y-m-d H:i:s')."<br>By:".$_SESSION['empl']['name'];	
        $namaFileExcel="Detail_Produksi_Brondol_".$periode;
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
