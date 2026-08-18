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

$tabel_0='bgt_regional_assignment';
$tabel_1='setup_blok';
$tabel_2='pabrik_timbangan';
$tabel_3='bgt_produksi_kbn_kg_vw';
$tabel_4='bgt_blok';
$tabel_5='kebun_rencanapanen_vw';
$tabel_6='bgt_produksi_pks_vw';


switch ($method) {
    // case 'getunit':
    //     $optunit="<option value=''>".$_SESSION['lang']['all']."</option>";
    //     $str="select * from ".$dbname.".organisasi where induk='".$pt."' and tipe='kebun'";
    //     $res=fetchData($str);
    //     foreach ($res as $bar) {
    //         $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
    //     }
    //     echo $optunit;
    //     break;
	case 'preview':
		$tab = '';
		$rowspan = '';
        if ($tahunprd=='') {
            exit("Warning Periode Kosong !");
        }
        $tahunprdbfr=$tahunprd-1;
		
		

## Start Queries
		$uraian1 = array("TM"=>"TM");
		$uraian2 = array("TBM"=>"TBM 2,3,4");
		$uraian3 = array("TBM"=>"TBM 1");
		$tr=array("TBM"=>"TBM1");

		#=setup_blok
		$str="select *,left(kodeorg,4) as kodeblok from ".$dbname.".setup_blok where kodeorg in (select kodeblok from ".$dbname.".bgt_blok where tahunbudget='".$tahunprd."' and left(kodeblok,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."')) ";
        $res=fetchData($str);
		foreach ($res as $bar) {
			$umum[$bar['kodeblok']]+=$bar['umum'];
			$prasarana+=$umum[$bar['kodeblok']];
		}
		#=bgt_blok
        $str="select * from ".$dbname.".bgt_blok where left(kodeblok,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') and tahunbudget='".$tahunprd."'";
        $res=fetchData($str);
        foreach ($res as $bar) {
            $arr[$bar['statusblok']][$bar['topografi']]=$bar['topografi'];
            $arr1[$bar['statusblok']]=$bar['statusblok'];
            $lcthnini[$bar['tahunbudget']]=$bar['lcthnini'];
            $pokokthnini[$bar['tahunbudget']]=$bar['pokokthnini'];

			$thnbudget[$bar['tahunbudget']]=$bar['tahunbudget'];
			$tanaman[$bar['tahunbudget']]+=$bar['hathnini'];

            $tm[$bar['statusblok']]+=$bar['hathnini'];
			if ($bar['topografi']!='D1' && $bar['statusblok']=='TBM') {
				$tbm234[$bar['statusblok']]+=$bar['hathnini'];
			}
			if ($bar['topografi']=='D1' && $bar['statusblok']=='TBM') {
				$tbm1[$bar['statusblok']]+=$bar['hathnini'];
			}
			if($lcthnini[$bar['tahunbudget']]<=$tbm1['TBM']){
				$pengurang=$lcthnini[$bar['tahunbudget']]-$tbm1['TBM'];
			}
			
			// $areal=$tanaman[$bar['tahunbudget']]+($lcthnini[$bar['tahunbudget']]-);
        }
		#=bgt_budget TM
		$str="select * from ".$dbname.".bgt_budget where left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') and tahunbudget='".$tahunprd."' and tipebudget='ESTATE'";
        $res=fetchData($str);
		foreach ($res as $bar) {
			if (substr($bar['noakun'],0,1)=='7' && $bar['kodebudget']=='UMUM') {
				$umumeks7[$bar['tahunbudget']]+=$bar['rupiah'];
			}
			if (substr($bar['noakun'],0,1)=='8' && $bar['kodebudget']=='UMUM') {
				$umumeks8[$bar['tahunbudget']]+=$bar['rupiah'];
			}
			if (substr($bar['noakun'],0,1)=='9' && $bar['kodebudget']=='UMUM') {
				$umumeks9[$bar['tahunbudget']]+=$bar['rupiah'];
			}
			if (substr($bar['kegiatan'],0,3)=='611' && $bar['kodebudget']!='UMUM') {
				$paneneks[$bar['tahunbudget']]+=$bar['rupiah'];
			}
			if (substr($bar['kegiatan'],0,3)=='621' && $bar['noakun']!='6210103' && $bar['kodebudget']!='UMUM') {
				$pemeliharaaneks[$bar['tahunbudget']]+=$bar['rupiah'];
			}
			if (substr($bar['kegiatan'],0,3)=='621' && $bar['noakun']=='6210103' && $bar['kodebudget']!='UMUM') {
				$pemupukaneks[$bar['tahunbudget']]+=$bar['rupiah'];
			}
			$totalumumeks=$umumeks7[$bar['tahunbudget']]+$umumeks8[$bar['tahunbudget']]+$umumeks9[$bar['tahunbudget']];
			$pnn611=$paneneks[$bar['tahunbudget']];
			$pemeliharaan621=$pemeliharaaneks[$bar['tahunbudget']];
			$pemupukan621=$pemupukaneks[$bar['tahunbudget']];
		}
		#=bgt_budget TBM
		$str="select * from ".$dbname.".bgt_budget where left(kodeorg,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') and tahunbudget='".$tahunprd."' and tipebudget='ESTATE'";
        $res=fetchData($str);
		foreach ($res as $bar) {
			// if (substr($bar['noakun'],0,1)=='7' && $bar['kodebudget']=='UMUM') {
			// 	$umumeks7[$bar['tahunbudget']]+=$bar['rupiah'];
			// }
			// if (substr($bar['noakun'],0,1)=='8' && $bar['kodebudget']=='UMUM') {
			// 	$umumeks8[$bar['tahunbudget']]+=$bar['rupiah'];
			// }
			// if (substr($bar['noakun'],0,1)=='9' && $bar['kodebudget']=='UMUM') {
			// 	$umumeks9[$bar['tahunbudget']]+=$bar['rupiah'];
			// }
			// if (substr($bar['kegiatan'],0,3)=='611' && $bar['kodebudget']!='UMUM') {
			// 	$paneneks[$bar['tahunbudget']]+=$bar['rupiah'];
			// }
			if (substr($bar['kegiatan'],0,3)=='126' && $bar['noakun']!='1260108' && $bar['kodebudget']!='UMUM') {
				$pemeliharaaneksTBM[$bar['tahunbudget']]+=$bar['rupiah'];
			}
			if (substr($bar['kegiatan'],0,3)=='126' && $bar['noakun']=='1260108' && $bar['kodebudget']!='UMUM') {
				$pemupukaneksTBM[$bar['tahunbudget']]+=$bar['rupiah'];
			}
			// $totalumumeks=$umumeks7[$bar['tahunbudget']]+$umumeks8[$bar['tahunbudget']]+$umumeks9[$bar['tahunbudget']];
			// $pnn611=$paneneks[$bar['tahunbudget']];
			$pemeliharaan126=$pemeliharaaneksTBM[$bar['tahunbudget']];
			$pemupukan126=$pemupukaneksTBM[$bar['tahunbudget']];
		}
		#=bgt_kapital
		$str="select * from ".$dbname.".bgt_kapital where kodeunit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$pt."') and tahunbudget='".$tahunprd."'";
        $res=fetchData($str);
		foreach ($res as $bar) {
			$kapital[$bar['tahunbudget']]+=$bar['hargatotal'];
			$totkapital=$kapital[$bar['tahunbudget']];
		}
		// echo"<pre>";
		// print_r($kapital);
		// echo"</pre>";
		// echo"<pre>";
		// print_r($totkapital);
		// echo"</pre>";
		// exit();
		
## End Queries

## Start Row 
		if ($tipeprint=='excel') {
			$tab.= "<table border=1 class=sortable cellpading=0 cellspacing=1>";
		}else{
			$tab.= "<table border=0 class=sortable cellpading=0 cellspacing=1>";
		}
		$tab.= "<thead><tr class=rowheader >";
		$tab.= "<th align=center rowspan=2 >".$_SESSION['lang']['uraian']."</th>";
		$tab.= "<th align=center colspan=3>Anggaran  ".$tahunprd."</th>";
			// $tab.= "<th align=center >Anggaran  ".$tahunprdbfr."</th>";
		$tab.= "</tr>";
		$tab.= "<tr class=rowheader >";
		$tab.= "<th align=center>".$_SESSION['lang']['total']."</th>";
		$tab.= "<th align=center></th>";
		$tab.= "<th align=center></th>";
			// $tab.= "<th align=center>".$_SESSION['lang']['total']."</th>";
		$tab.= "</tr>";
		// 		

		$tab.= "</tehead>";
		$tab.= "<tbody>";
		foreach ($thnbudget as $key ) {
			$tab.= "<tr class=rowcontent>";
			$tab.= "<td align=left>Tanaman (Ha) -= 1)+2)+3)</td>";
			$tab.= "<td align=right>".number_format($tanaman[$key],2)."</td>";
			$tab.= "<td align=right></td>";
			$tab.= "<td align=right></td>";
			$tab.= "</tr>";
			$t1+=$tanaman[$key];
		}
		foreach ($uraian1 as $statblok => $val) {
			$tab.= "<tr class=rowcontent>";
			$tab.= "<td style=width:200px align=left>&nbsp;1) TM</td>";
			$tab.= "<td style=width:120px align=right>".number_format($tm[$statblok],2)."</td>";
			$tab.= "<td style=width:100px align=right></td>";
			$tab.= "<td style=width:100px align=right></td>";
			$tab.= "</tr>";
			$totTM+=$tm[$statblok];
		}
		foreach ($uraian2 as $statblok => $val) {
			$tab.= "<tr class=rowcontent>";
			$tab.= "<td align=left>&nbsp;2) TBM 2,3,4</td>";
			$tab.= "<td align=right>".number_format($tbm234[$statblok],2)."</td>";
			$tab.= "<td align=right></td>";
			$tab.= "<td align=right></td>";
			$tab.= "</tr>";
		}
		foreach ($uraian3 as $statblok => $val) {
			$tab.= "<tr class=rowcontent>";
			$tab.= "<td align=left>&nbsp;3) TBM 1</td>";
			$tab.= "<td align=right>".number_format($tbm1[$statblok],2)."</td>";
			$tab.= "<td align=right></td>";
			$tab.= "<td align=right></td>";
			$tab.= "</tr>";
			$totTBM1+=$tbm1[$statblok];
		}
		foreach ($uraian3 as $statblok => $val) {
			$tab.= "<tr class=rowcontent>";
			$tab.= "<td align=left>&nbsp;4) LC</td>";
			$tab.= "<td align=right>".number_format($tbm1[$statblok],2)."</td>";
			$tab.= "<td align=right></td>";
			$tab.= "<td align=right></td>";
			$tab.= "</tr>";
		}
		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left colspan=2>Bibitan</td>";
		$tab.= "<td align=right></td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>&nbsp; a. Luas (Ha)</td>";
		$tab.= "<td align=right>".number_format(0,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";
		foreach ($thnbudget as $key) {
			$tab.= "<tr class=rowcontent>";
			$tab.= "<td align=left>&nbsp; b. Jumlah Pokok (Pkk)</td>";
			$tab.= "<td align=right>".number_format($pokokthnini[$key],2)."</td>";
			$tab.= "<td align=right></td>";
			$tab.= "<td align=right></td>";
			$tab.= "</tr>";
		}
		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Prasarana (Ha)</td>";
		$tab.= "<td align=right>".number_format($prasarana,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>&nbsp;Areal Diusahakan</td>";
		$tab.= "<td align=right>".number_format($t1+$prasarana,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Produksi Total (Intern)</td>";
		$tab.= "<td align=right>".number_format(0,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";
		## Header 2 ##
		$tab.= "</tbody><thead><tr class=rowheader>";
		$tab.= "<th align=left></th>";
		$tab.= "<th align=center><b>Rp.000</th>";
		$tab.= "<th align=center><b>Ribu/Ha</th>";
		$tab.= "<th align=center><b>Rp/Kg</th>";
		$tab.= "</tr></thead><tbody>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left><b>EKSPLOITASI (Rp.000,-)</td>";
		$tab.= "<td align=right><b>".number_format($totalumumeks+$pnn611+$pemeliharaan621+$pemupukan621,2)."</td>";
		$tab.= "<td align=right><b>".number_format(($pnn611/$totTM)+($pemeliharaan621/$totTM)+($pemupukan621/$totTM),2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Umum</td>";
		$tab.= "<td align=right>".number_format($totalumumeks,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Panen</td>";
		$tab.= "<td align=right>".number_format($pnn611,2)."</td>";
		$tab.= "<td align=right>".number_format($pnn611/$totTM,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Pemeliharaan TM</td>";
		$tab.= "<td align=right>".number_format($pemeliharaan621,2)."</td>";
		$tab.= "<td align=right>".number_format($pemeliharaan621/$totTM,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Pemupukan TM</td>";
		$tab.= "<td align=right>".number_format($pemupukan621,2)."</td>";
		$tab.= "<td align=right>".number_format($pemupukan621/$totTM,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left><b>INVESTASI (Rp.000,-)</td>";
		$tab.= "<td align=right><b>".number_format($pemeliharaan126+$pemupukan12,2)."</td>";
		$tab.= "<td align=right><b>".number_format(($pemeliharaan126/$totTBM1)+($pemupukan126/$totTBM1),2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Umum</td>";
		$tab.= "<td align=right>".number_format(0,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Pembukaan Lahan & Tanaman Baru</td>";
		$tab.= "<td align=right>".number_format(0,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Bibitan (Rp. / Bibit)</td>";
		$tab.= "<td align=right>".number_format(0,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Pemeliharaan TBM </td>";
		$tab.= "<td align=right>".number_format($pemeliharaan126,2)."</td>";
		$tab.= "<td align=right>".number_format($pemeliharaan126/$totTBM1,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Pemupukan TBM</td>";
		$tab.= "<td align=right>".number_format($pemupukan126,2)."</td>";
		$tab.= "<td align=right>".number_format($pemupukan126/$totTBM1,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left>Kapital Non Tanaman</td>";
		$tab.= "<td align=right>".number_format($totkapital,2)."</td>";
		$tab.= "<td align=right></td>";
		$tab.= "<td align=right></td>";
		$tab.= "</tr>";

		$tab.= "<tr class=rowcontent>";
		$tab.= "<td align=left><b>Total Ekspolitasi & Investasi</td>";
		$tab.= "<td align=right><b>".number_format(($totalumumeks+$pnn611+$pemeliharaan621+$pemupukan621)+($pemeliharaan126+$pemupukan12),2)."</td>";
		$tab.= "<td align=right><b>".number_format((($pnn611/$totTM)+($pemeliharaan621/$totTM)+($pemupukan621/$totTM))+(($pemeliharaan126/$totTBM1)+($pemupukan126/$totTBM1)),2)."</td>";
		$tab.= "<td align=right><b></td>";
		$tab.= "</tr>";
## End Row Content
       
		if($tipeprint=='html'){
			echo $tab;			
		}else if($tipeprint=='excel'){
			$tab.="</tbody></table>";
			
			$nop = "LAPORAN REKAP PER KELOMPOK BIAYA.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("1", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}

	break;	
}

?>