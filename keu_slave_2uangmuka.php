<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/nangkoelib.php');
include_once('lib/HtmlExcel.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
error_reporting(0);

$stream='';
$method = checkPostGet('method', '');
$param = $_POST;
$unit = checkPostGet('unit', '');
$pt = checkPostGet('pt','');
$noakun = checkPostGet('noakun', '');

$nodoc= checkPostGet('nodoc','');
$tipe= checkPostGet('tipe','');
// exit('error:'.$tipe);

$tgl1=tanggalsystemn(checkPostGet('tgl1',''));
$tgl2=tanggalsystemn(checkPostGet('tgl2',''));

// echo "<pre>";
// print_r($noakun);
// echo "</pre>";

if($tgl1=='--'){
    $tgl1='';
}
if($tgl2=='--'){
    $tgl2='';
}
$where="";

if($unit!='' && $unit!='all') {
	$where.=" and kodeorg = '".$unit."'";
}

switch ($method) {
	case 'getUnit':

		$str="select * from ".$dbname.".organisasi where induk='".$pt."'";
		
		$res=fetchdata($str);
		$optunit="<option value='all'>".$_SESSION['lang']['all']."</option>"; 
		foreach ($res as $key => $val) {
			$optunit.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']." ".$val['namaorganisasi']."</option>";
		}
		echo $optunit;

	break;


######PREVIEW
    case 'preview':
	
		if($tipe=='excel' or $tipe=='pdf'){
			$border=1;
			$cellspacing=0;

		} else {
			$border=0;
			$cellspacing=1;
		}
		if (substr($tgl1, 0,7) != substr($tgl2, 0,7)){
			Exit ('Warning Bulan dan tahun harus sama');
		}
		
		
		// $stream.="Laporan Uang Muka<br><br>";
	
		//<td align='center'>".$_SESSION['lang']['pembayaran']."</td>
		// $stream.="<div class='table-scroll'>";
		if($tipe=='excel'){
			// $stream.="<tr>";
				$stream.="<center><h3><b>LAPORAN UANG MUKA ".$pt."<b></h3></center>";
			// $stream.="<tr>";
		}
		if($tipe=='pdf'){
			// $stream.="<tr>";
				$stream.="<h3><b>LAPORAN UANG MUKA ".$pt."<b></h3>";
			// $stream.="<tr>";
		}

		$stream.="<div style='overflow:auto;height:300px;'>";
		$stream.="<table  class=sortable cellspacing='".$cellspacing."' border='".$border."'>";
		$stream.="
			<thead>
				<tr class=rowheader>
					<th align=center>".$_SESSION['lang']['nourut']."</th>
					<th align=center style=min-width:150px>".$_SESSION['lang']['nodok']."</th>
					<th align=center style=min-width:50px>".$_SESSION['lang']['unit']."</th>
					<th align=center style=min-width:100px>".$_SESSION['lang']['debet']."</th>
					<th align=center style=min-width:100px>".$_SESSION['lang']['kredit']."</th>
					<th align=center style=min-width:100px>".$_SESSION['lang']['selisih']."</th>
					
				</tr>
			</thead>
		 <tbody>";

		#= data 
		$str="SELECT DISTINCT nodok,nojurnal,kodeorg,debet,kredit FROM ".$dbname.".keu_jurnaldt_vw where tanggal between '".$tgl1."' and '".$tgl2."' and noakun='".$noakun."' 
			".$where."";
		// exit('error:'.$str);
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrnodok[$bar['nodok']]=$bar['nodok'];
			$arrnojurnal[$bar['nojurnal']]=$bar['nojurnal'];
			$lsnojurnal[$bar['kodeorg']][$bar['nodok']]=$bar['nojurnal'];
			$kodeorg[$bar['kodeorg']][$bar['nodok']]=$bar['kodeorg'];

			$org[$bar['kodeorg']]=$bar['kodeorg'];

			// $debet[$bar['kodeorg']][$bar['nodok']]=$bar['debet'];
			// $kredit[$bar['kodeorg']][$bar['nodok']]=$bar['kredit'];
			// $selisih[$bar['kodeorg']]=$debet[$bar['kodeorg']][$bar['nodok']] - $kredit[$bar['kodeorg']][$bar['nodok']];

			$noref[$bar['kodeorg']][$bar['nodok']]=$bar['noreferensi'];
			$kodesupplier[$bar['kodeorg']][$bar['nodok']]=$bar['kodesupplier'];
			$arrsupplier[$bar['kodesupplier']]=$bar['kodesupplier'];
			$tanggal[$bar['kodeorg']][$bar['nodok']]=$bar['tanggal'];
			$keterangan[$bar['kodeorg']][$bar['nodok']]=$bar['keterangan'];
			// @$stjumlah[$bar['nodok']]+=$bar['jumlah'];
		}
		foreach($org as $arrorg){
			foreach($arrnodok as $nodoc){
				$str="SELECT DISTINCT sum(debet) as debet, sum(kredit) as kredit,(sum(debet)-sum(kredit)) as selisih FROM ".$dbname.".keu_jurnaldt_vw where nodok='".$nodoc."' and kodeorg='".$arrorg."' and tanggal between '".$tgl1."' and '".$tgl2."' and noakun='".$noakun."' ".$where."";
				// exit('error:'.$str);
				$res=fetchdata($str);
				foreach($res as $bar){
					$debet[$arrorg][$nodoc]=$bar['debet'];
					$kredit[$arrorg][$nodoc]=$bar['kredit'];
					$selisih[$arrorg][$nodoc]=$bar['selisih'];
					// @$stjumlah[$bar['nodok']]+=$bar['jumlah'];
				}
			}
		}
		if(count($arrsupplier)>0){
			$str="select * from ".$dbname.".log_5supplier where  supplierid in ('".implode("','",$arrsupplier)."') ";
			$res=fetchdata($str);
			foreach($res as $bar){
				$namasupplier[$bar['supplierid']]=$bar['namasupplier'];
			}
		}
	
		$no=0;
		foreach($org as $arrorg){
		foreach($arrnodok as $nodoc){
				if(@$lsnojurnal[$arrorg][$nodoc]!=''){
					$no++;
					$stream.="<tr class=rowcontent>";
						$stream.="<td align=center style='cursor:pointer' onclick=\"popup_ap('".$nodoc."','".$tgl1."','".$tgl2."','".$kodeorg[$arrorg][$nodoc]."','".$noakun."')\">
								 ".$no."</td>";

						$stream.="<td style='cursor:pointer' onclick=\"popup_ap('".$nodoc."','".$tgl1."','".$tgl2."','".$kodeorg[$arrorg][$nodoc]."','".$noakun."')\">
								 ".$nodoc."</td>";

						$stream.="<td align=center style='cursor:pointer' onclick=\"popup_ap('".$nodoc."','".$tgl1."','".$tgl2."','".$kodeorg[$arrorg][$nodoc]."','".$noakun."')\">
								 ".$kodeorg[$arrorg][$nodoc]."</td>";

						$stream.="<td align=right style='cursor:pointer' onclick=\"popup_ap('".$nodoc."','".$tgl1."','".$tgl2."','".$kodeorg[$arrorg][$nodoc]."','".$noakun."')\">
								 ".number_format($debet[$arrorg][$nodoc],2)."</td>";

						$stream.="<td align=right style='cursor:pointer' onclick=\"popup_ap('".$nodoc."','".$tgl1."','".$tgl2."','".$kodeorg[$arrorg][$nodoc]."','".$noakun."')\">
								 ".number_format($kredit[$arrorg][$nodoc],2)."</td>";

						 // $selisih[$arrorg]+=$debet[$arrorg][$nodoc] - $kredit[$arrorg][$nodoc];

						$stream.="<td align=right style='cursor:pointer' onclick=\"popup_ap('".$nodoc."','".$tgl1."','".$tgl2."','".$kodeorg[$arrorg][$nodoc]."','".$noakun."')\">
								 ".number_format($selisih[$arrorg][$nodoc],2)."</td>";

						// $stream.="<td>".tanggalnormal($tanggal[$nodoc][$nojurnal])."</td>";
						// $stream.="<td>".@$namasupplier[$kodesupplier[$nodoc][$nojurnal]]."</td>";
						// $stream.="<td>".$keterangan[$nodoc][$nojurnal]."</td>";
						// $stream.="<td align=right>".number_format($jumlah[$nodoc][$nojurnal])."</td>";
					$stream.="</tr>";
					$jmldebet[$arrorg]+=$debet[$arrorg][$nodoc];
					$jmlkredit[$arrorg]+=$kredit[$arrorg][$nodoc];
					$jmlselisih[$arrorg]+=$selisih[$arrorg][$nodoc];
				}
			}
			$stream.="<tr class=rowcontent>";
				$stream.="<td colspan=3 align=right><b>".$_SESSION['lang']['subtotal']." ".$arrorg."</td>";
				$stream.="<td align=right><b>".number_format($jmldebet[$arrorg],2)."</b></td>";
				$stream.="<td align=right><b>".number_format($jmlkredit[$arrorg],2)."</b></td>";
				$stream.="<td align=right><b>".number_format($jmlselisih[$arrorg],2)."</b></td>";

				$total1+=$jmldebet[$arrorg];
				$total2+=$jmlkredit[$arrorg];
				$total3+=$jmlselisih[$arrorg];
			$stream.="</tr>";
		}
		$stream.="<tr class=rowcontent>";
			$stream.="<td colspan=3 align=center><b>".$_SESSION['lang']['total']."</td>";
			$stream.="<td align=right><b>".number_format($total1,2)."</b></td>";
			$stream.="<td align=right><b>".number_format($total2,2)."</b></td>";
			$stream.="<td align=right><b>".number_format($total3,2)."</b></td>";
		$stream.="</tr>";

		$stream.="</tbody></table></div>";

		if($tipe=='excel'){
			$nop = "Laporan_uangmuka_.".$pt.".xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("Rekap_uang_muka", $stream);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else if($tipe=='pdf'){
			$dompdf = new Dompdf();
            $dompdf->loadHtml($stream);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream("form survey",array("Attachment"=>0));
		}else{
			echo $stream;
		}

	break;

	case 'popup_ap':
		$tab = '';

		$tab.="<table cellspacing=1 border=0 >
				<thead><tr class=rowheader>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['nodok']."</td>
					<td align=center>".$_SESSION['lang']['nojurnal']."</td>
					<td align=center>".$_SESSION['lang']['jumlah']."</td>
					
				</tr>
				</thead>";
		
		$str="select * from ".$dbname.".keu_jurnaldt_vw where nodok='".$nodoc."' and tanggal between '".$param['dari1']."' and '".$param['dari2']."' and kodeorg= '".$param['org']."' and noakun= '".$param['akun']."'";
		$res=fetchdata($str);
		// echo "<pre>";
		// print_r($param['akun']);
		// echo "</pre>";

		// exit('error:'.$str);

		foreach ($res as $bar) {
			$arrnodok[$bar['nodok']]=$bar['nodok'];
			$nourut[$bar['nourut']]=$bar['nourut'];

			$dok[$bar['nodok']][$bar['nourut']]=$bar['nodok'];
			$nojur[$bar['nodok']][$bar['nourut']]=$bar['nojurnal'];
			$jmlh[$bar['nodok']][$bar['nourut']]=$bar['jumlah'];
   	 	}
   	 	foreach ($arrnodok as $nodoc) {
   	 	foreach ($nourut as $urut) {
			$no++;
			$tab.="<tr class=rowcontent>
						<td align=center>".$no."</td>
						<td style=min-width:130px>".$dok[$nodoc][$urut]."</td>
						<td style=min-width:160px>".$nojur[$nodoc][$urut]." </b></td>
						<td align=right style=min-width:100px>".number_format($jmlh[$nodoc][$urut],2)." </b></td>
					</tr>";
			
   	 		}
   	 	}
			$tab.="<tr class=rowcontent>
					<td align=right style=min-width:100px>&nbsp</td>
				</tr>";

		$tab.="</table>";
	        echo $tab;
	
	break;
	
	case'':
	break;
}
?>