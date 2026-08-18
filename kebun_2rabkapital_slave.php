<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;
error_reporting(0);

$pt = checkPostGet('pt','');
$unit = checkPostGet('unit','');
$tahunprd = checkPostGet('tahunprd','');
$method = checkPostGet('method','');
$tipeprint= checkPostGet('tipeprint','');

switch ($method) {
    case 'getunit':
        $optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
        $str="select * from ".$dbname.".organisasi where induk='".$pt."' and tipe='kebun'";
        $res=fetchData($str);
        foreach ($res as $bar) {
            $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
        }
        echo $optunit;
        break;
	case 'preview':
		$tab = '';
		$rowspan = '';
        if ($tahunprd=='') {
            exit("Warning Periode Kosong !");
        }
        if ($unit!='') {
            $wh="left(kodeunit,4) ='".$unit."'";
        }else {
            $wh="left(kodeunit,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
        }
        $tahunprdbfr=$tahunprd-1;
		
		

## Start Queries

		#=bgt_budget TM
		$str="select *,left(aruskas,3) as headakun from ".$dbname.".bgt_kapital where 1=1 and ".$wh." and tahunbudget='".$tahunprd."' order by aruskas asc";
        $res=fetchData($str);
		foreach ($res as $bar) {
			$headakun[$bar['headakun']]=$bar['headakun'];
			$isiakun[$bar['headakun']][$bar['aruskas']]=$bar['aruskas'];
            $keterangan[$bar['headakun']][$bar['aruskas']][$bar['keterangan']]=$bar['keterangan'];

			$rp[$bar['headakun']][$bar['aruskas']][$bar['keterangan']]+=$bar['hargatotal'];
			$hargasatuan[$bar['headakun']][$bar['aruskas']][$bar['keterangan']]=$bar['hargasatuan'];
			$jmlh[$bar['headakun']][$bar['aruskas']][$bar['keterangan']]+=$bar['jumlah'];

			// $grand[$bar['tahunbudget']]+=$bar['hargatotal'];
            // $totgrand=$grand[$bar['tahunbudget']];
		}

## End Queries

## Start Row header
		if ($tipeprint=='excel') {
			$tab.= "<table border=1 class=sortable cellpading=0 cellspacing=1>";
		}else{
			$tab.= "<table border=0 class=sortable cellpading=0 cellspacing=1>";
		}
		$tab.= "<thead><tr class=rowheader >";
		$tab.= "<th align=center rowspan=2 >".$_SESSION['lang']['noakun']."</th>";
		$tab.= "<th align=center rowspan=2 >".$_SESSION['lang']['keterangan']."</th>";
		$tab.= "<th align=center colspan=3>Anggaran ".$tahunprd."</th>";
			// $tab.= "<th align=center >Anggaran  ".$tahunprdbfr."</th>";
		$tab.= "</tr>";
		$tab.= "<tr class=rowheader >";
		$tab.= "<th align=center>".$_SESSION['lang']['unit']."</th>";
		$tab.= "<th align=center>Rp/Unit</th>";
		$tab.= "<th align=center>".$_SESSION['lang']['total']."</th>";
			// $tab.= "<th align=center>".$_SESSION['lang']['total']."</th>";
		$tab.= "</tr>";
		// 		
## End Row header
## Start Row content
        $nmheadakun  = makeOption($dbname, 'keu_5aruskas', 'noaruskas,nama_aruskas');
		$tab.= "</tehead>";
		$tab.= "<tbody>";
		
		foreach ($headakun as $head) {
		    foreach ($isiakun[$head] as $isi) {
		        foreach ($keterangan[$head][$isi] as $ket) {
                    $tab.= "<tr class=rowcontent>";
                    $tab.= "<td align=left>".$isi."</td>";
                    $tab.= "<td align=left>".$ket."</td>";
                    $tab.= "<td align=right>".number_format($jmlh[$head][$isi][$ket],2)."</td>";
                    $tab.= "<td align=right>".number_format($hargasatuan[$head][$isi][$ket],2)."</td>";
                    $tab.= "<td align=right>".number_format($rp[$head][$isi][$ket],2)."</td>";
                    $tab.= "</tr>";

                    $subtotisi1[$head]+=$jmlh[$head][$isi][$ket];
                    // $subtotisi2[$head]+=$hargasatuan[$head][$isi][$ket];
                    $subtotisi3[$head]+=$rp[$head][$isi][$ket];
                }
            $tab.= "<tr class=rowcontent>";
			$tab.= "<td align=left colspan=2><b>".$_SESSION['lang']['subtotal']." ( ".$head." - ".$nmheadakun[$head]." )</td>";
			$tab.= "<td align=right><b>".number_format($subtotisi1[$head],2)."</td>";
			$tab.= "<td align=right><b></td>";
			$tab.= "<td align=right><b>".number_format($subtotisi3[$head],2)."</td>";
			$tab.= "</tr>";
            $grandtot1+=$subtotisi1[$head];
            // $grandtot2+=$subtotisi2[$head];
            $grandtot3+=$subtotisi3[$head];
            }
		}
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td align=left colspan=2><b>Grand ".$_SESSION['lang']['total']."</td>";
        $tab.= "<td align=right><b>".number_format($grandtot1,2)."</td>";
        $tab.= "<td align=right><b></td>";
        $tab.= "<td align=right><b>".number_format($grandtot3,2)."</td>";
        $tab.= "</tr>";
## End Row Content
       
		if($tipeprint=='html'){
			echo $tab;			
		}else if($tipeprint=='excel'){
			$tab.="</tbody></table>";
			
			$nop = "Laporan RAB Biaya Kapiral ".$unit.".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("1", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}

	break;	
}

?>