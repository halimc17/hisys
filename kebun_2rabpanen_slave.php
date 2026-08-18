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
            $wh="left(kodeorg,4) ='".$unit."'";
            $wh2="left(kodeblok,4) ='".$unit."'";
        }else {
            $wh="left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
            $wh2="left(kodeblok,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')";
        }
        $tahunprdbfr=$tahunprd-1;
		
		

## Start Queries

		#=bgt_budget TM
		$str="select *,left(noakun,5) as headakun from ".$dbname.".bgt_budget where 1=1 and ".$wh." and tahunbudget='".$tahunprd."' and tipebudget='ESTATE' and noakun like '611%' order by noakun asc";
        $res=fetchData($str);
		foreach ($res as $bar) {
			$headakun[$bar['headakun']]=$bar['headakun'];
			$isiakun[$bar['headakun']][$bar['noakun']]=$bar['noakun'];
			$rp[$bar['headakun']][$bar['noakun']]+=$bar['rupiah'];
			$grand[$bar['tahunbudget']]+=$bar['rupiah'];
            $totgrand=$grand[$bar['tahunbudget']];
		}

        #=bgt_blok
        $str="select * from ".$dbname.".bgt_blok where 1=1 and ".$wh2." and tahunbudget='".$tahunprd."' and statusblok='TM'";
        $res=fetchData($str);
        foreach ($res as $bar) {
            $tm[$bar['tahunbudget']]+=$bar['hathnini'];
            $pokokproduksi[$bar['tahunbudget']]+=$bar['pokokproduksi'];
            $pokokthnini[$bar['tahunbudget']]+=$bar['pokokthnini'];
            $lad=$tm[$bar['tahunbudget']];
            $pokok=$pokokproduksi[$bar['tahunbudget']];
            $thnini=$pokokthnini[$bar['tahunbudget']];
        }
	
		// echo"<pre>";
		// print_r($grand);
		// echo"</pre>";
		// echo"<pre>";
		// print_r($totkapital);
		// echo"</pre>";
		// exit();
		
## End Queries

## Start Row header
		if ($tipeprint=='excel') {
			$tab.= "<table border=1 class=sortable cellpading=0 cellspacing=1>";
		}else{
			$tab.= "<table border=0 class=sortable cellpading=0 cellspacing=1>";
		}
		$tab.= "<thead><tr class=rowheader >";
		$tab.= "<th align=center rowspan=2 >".$_SESSION['lang']['noakun']."</th>";
		$tab.= "<th align=center rowspan=2 >".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['pekerjaan']."</th>";
		$tab.= "<th align=center colspan=2>Anggaran ".$tahunprd."</th>";
			// $tab.= "<th align=center >Anggaran  ".$tahunprdbfr."</th>";
		$tab.= "</tr>";
		$tab.= "<tr class=rowheader >";
		$tab.= "<th align=center>".$_SESSION['lang']['total']."</th>";
		$tab.= "<th align=center>Rp/Kg</th>";
			// $tab.= "<th align=center>".$_SESSION['lang']['total']."</th>";
		$tab.= "</tr>";
		// 		
## End Row header
## Start Row content
        $nmheadakun  = makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
		$tab.= "</tehead>";
		$tab.= "<tbody>";
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td align=right></td>";
        $tab.= "<td align=left><b>".$_SESSION['lang']['luas']." TM</td>";
        $tab.= "<td align=right><b>".number_format($lad,2)."</td>";
        $tab.= "<td align=center><b>Ha</td>";
        $tab.= "</tr>";

        $tab.= "<tr class=rowcontent>";
        $tab.= "<td align=right></td>";
        $tab.= "<td align=left><b>Produksi</td>";
        $tab.= "<td align=right><b>".number_format($pokok,2)."</td>";
        $tab.= "<td align=center><b>Ton</td>";
        $tab.= "</tr>";
        
        $tab.= "<tr class=rowcontent>";
        $d7=$pokok/$lad;
        $tab.= "<td align=right></td>";
        $tab.= "<td align=left><b>Produktivitas</td>";
        $tab.= "<td align=right><b>".number_format($d7,2)."</td>";
        $tab.= "<td align=center><b>Ton/Ha</td>";
        $tab.= "</tr>";

        $tab.= "<tr class=rowcontent>";
        $tab.= "<td align=right></td>";
        $tab.= "<td align=left><b>Pengangkutan</td>";
        $tab.= "<td align=right><b>".number_format($thnini,2)."</td>";
        $tab.= "<td align=center><b>Ton</td>";
        $tab.= "</tr>";
		
		foreach ($headakun as $head) {
		    foreach ($isiakun[$head] as $isi) {
                $tab.= "<tr class=rowcontent>";
                $tab.= "<td align=left>".$isi."</td>";
                $tab.= "<td align=left>".$nmheadakun[$isi]."</td>";
                $tab.= "<td align=right>".number_format($rp[$head][$isi],2)."</td>";
                $tab.= "<td align=right>".number_format($rp[$head][$isi]/$pokok,2)."</td>";
                $tab.= "</tr>";
                $subtotisi[$head]+=$rp[$head][$isi];
                $subtotisid7[$head]+=$rp[$head][$isi]/$pokok;
            }
            $tab.= "<tr class=rowcontent>";
			$tab.= "<td align=left colspan=2><b>".$_SESSION['lang']['subtotal']." ( ".$head." - ".$nmheadakun[$head]." )</td>";
			$tab.= "<td align=right><b>".number_format($subtotisi[$head],2)."</td>";
			$tab.= "<td align=right><b>".number_format($subtotisid7[$head],2)."</td>";
			$tab.= "</tr>";

            $grandtot+=$subtotisi[$head];
            $grandtotd7+=$subtotisid7[$head];
		}
        $tab.= "<tr class=rowcontent>";
        $tab.= "<td align=left colspan=2><b>Grand ".$_SESSION['lang']['total']."</td>";
        $tab.= "<td align=right><b>".number_format($grandtot,2)."</td>";
        $tab.= "<td align=right><b>".number_format($grandtotd7,2)."</td>";
        $tab.= "</tr>";
## End Row Content
       
		if($tipeprint=='html'){
			echo $tab;			
		}else if($tipeprint=='excel'){
			$tab.="</tbody></table>";
			
			$nop = "Laporan RAB Biaya Panen ".$unit.".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("1", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}

	break;	
}

?>