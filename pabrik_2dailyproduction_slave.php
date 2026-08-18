<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');

use Dompdf\Dompdf;
$stream='';

$method = checkPostGet('method','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}



switch ($method) {

	case'preview':
	
		$where='';
	
		if($param['periode']==''){
			exit("Warning:periode tidak boleh kosong");
		}
		
		if($param['kodeunit']!=''){
			$where=" and kodeorg='".$param['kodeunit']."'";
			$wheretimbangan=" and millcode='".$param['kodeunit']."'";
			
			
		}
		
		
		
		$tanggal1=$param['periode'].'-01';
		$tanggal2=tglakhir($param['periode']);
		$arrtanggal=rangeTanggalarr($tanggal1, $tanggal2);
		
		
		#= Terima Tbs Internal #= b1 b2 b3
		$str="select sum(beratbersih/".$param['satuan'].") as bruto,sum(kgpotsortasi/".$param['satuan'].") as sortasi,sum(beratbersih/".$param['satuan'].")-sum(kgpotsortasi/".$param['satuan'].") as netto,intex,intiplasma,tanggal
				from ".$dbname.".pabrik_timbangan_vw   where kodebarang='40000003' and tanggal like '".$param['periode']."%' ".$wheretimbangan." group by intex,intiplasma,tanggal";
				// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			if($bar['intex']==1){
				if($bar['intiplasma']=='INTI'){
					@$dttbsintinetto[$bar['tanggal']]+=$bar['bruto'];
					@$dttbsintisortasi[$bar['tanggal']]+=$bar['sortasi'];
				}else{
					@$dttbsplasmanetto[$bar['tanggal']]+=$bar['bruto'];
					@$dttbsplasmasortasi[$bar['tanggal']]+=$bar['sortasi'];
				}
			}
			if($bar['intex']==2){
				if($bar['intiplasma']=='INTI'){
					@$dttbsintinetto[$bar['tanggal']]+=$bar['bruto'];
					@$dttbsintisortasi[$bar['tanggal']]+=$bar['sortasi'];
				}else{
					@$dttbsplasmanetto[$bar['tanggal']]+=$bar['bruto'];
					@$dttbsplasmasortasi[$bar['tanggal']]+=$bar['sortasi'];
				}
			}
			if($bar['intex']==0){
				@$dttbsextnetto[$bar['tanggal']]+=$bar['bruto'];
				@$dttbsextsortasi[$bar['tanggal']]+=$bar['sortasi'];
			}
		}
		
		
	
		if($param['tipe']=='html'){
			$stylekolom='border=0 cellspacing=1';
		}else if($param['tipe']=='pdf'){
			$stylekolom='border=1 cellspacing=0';
		}else if($param['tipe']=='excel'){
			$stylekolom='border=1 cellspacing=1';
		}
		
		$explodeperiode=explode("-",$param['periode']);
		
		$stream.="Daily Prod ".$param['kodeunit']." - ".numToMonth(intval($explodeperiode[1]),'I','long')." ".$explodeperiode[0]." ";
		$stream.="<table class=sortable ".$stylekolom." width=100%>";
		$stream.="<thead>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center>&nbsp;</th>";
				foreach($arrtanggal as $dttgl){
					$stream.="<th align=center>".substr($dttgl,-2)."</th>";
				}
			$stream.="</tr>";
			$stream.="</thead>";
			$stream.="<tbody>";
			
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>Inti</td>";
				foreach($arrtanggal as $dttgl){
					$stream.="<td align=right>".hidezerodecimal($dttbsintinetto[$dttgl])."</td>";
				}
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>Plasma</td>";
				foreach($arrtanggal as $dttgl){
					$stream.="<td align=right>".hidezerodecimal($dttbsplasmanetto[$dttgl])."</td>";
				}
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>Swadaya/External</td>";
				foreach($arrtanggal as $dttgl){
					$stream.="<td align=right>".hidezerodecimal($dttbsextnetto[$dttgl])."</td>";
				}
			$stream.="</tr>";
			
			#= grading
			
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>Grading Inti</td>";
				foreach($arrtanggal as $dttgl){
					$stream.="<td align=right>".hidezerodecimal($dttbsintisortasi[$dttgl],1)."</td>";
				}
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>Grading Plasma</td>";
				foreach($arrtanggal as $dttgl){
					$stream.="<td align=right>".hidezerodecimal($dttbsplasmasortasi[$dttgl],1)."</td>";
				}
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>Grading Swadaya/External</td>";
				foreach($arrtanggal as $dttgl){
					$stream.="<td align=right>".hidezerodecimal($dttbsextsortasi[$dttgl],1)."</td>";
				}
			$stream.="</tr>";
		$stream.="</tbody>";
			
		$stream.="</table>";
		$stream.="<br>";
		$stream.="<br>";
		
		
		
		#======== cpo/pk
		
		#= Terima Tbs Internal #= b1 b2 b3
		$str="SELECT oer/".$param['satuan']." as oer,oerpk/".$param['satuan']." as oerpk,tanggal from ".$dbname.".pabrik_produksi  where tanggal like '".$param['periode']."%' ".$where."";
		$res=fetchdata($str);
		foreach($res as $bar){
			$dtcpo[$bar['tanggal']]=$bar['oer'];
			$dtpk[$bar['tanggal']]=$bar['oerpk'];
		}
		
		#= cpo/pk
		$stream.="Daily Prod CPO & PK ".$param['kodeunit']." - ".numToMonth(intval($explodeperiode[1]),'I','long')." ".$explodeperiode[0]." ";
		$stream.="<table class=sortable ".$stylekolom." width=100%>";
		$stream.="<thead>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center>&nbsp;</th>";
				foreach($arrtanggal as $dttgl){
					$stream.="<th align=center>".substr($dttgl,-2)."</th>";
				}
			$stream.="</tr>";
			$stream.="</thead>";
			$stream.="<tbody>";
			
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>CPO</td>";
				foreach($arrtanggal as $dttgl){
					$stream.="<td align=right>".hidezerodecimal($dtcpo[$dttgl])."</td>";
				}
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>PK</td>";
				foreach($arrtanggal as $dttgl){
					$stream.="<td align=right>".hidezerodecimal($dtpk[$dttgl])."</td>";
				}
			
			$stream.="</tbody>";
				
		$stream.="</table>";
		
		
		$stream.="<br>";
		$stream.="<br>";
		
		
		
		#======== cpo/pk oer
		
		#= Terima Tbs Internal #= b1 b2 b3
		$str="SELECT (sum(oer/".$param['satuan'].")/sum(tbsdiolah/".$param['satuan'].")*100) as oer,(sum(oerpk/".$param['satuan'].")/sum(tbsdiolah/".$param['satuan'].")*100) as oerpk,tanggal,avg(ffa) as ffa from ".$dbname.".pabrik_produksi  where tanggal like '".$param['periode']."%' ".$where." group by tanggal";
		$res=fetchdata($str);
		foreach($res as $bar){
			$dtcpo[$bar['tanggal']]=$bar['oer'];
			$dtpk[$bar['tanggal']]=$bar['oerpk'];
			$dtffa[$bar['tanggal']]=$bar['ffa'];
		}
		
		#= cpo/pk
		$stream.="Daily Prod OER, KER & FFA ".$param['kodeunit']." - ".numToMonth(intval($explodeperiode[1]),'I','long')." ".$explodeperiode[0]." ";
		$stream.="<table class=sortable ".$stylekolom." width=100%>";
		$stream.="<thead>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center>&nbsp;</th>";
				foreach($arrtanggal as $dttgl){
					$stream.="<th align=center>".substr($dttgl,-2)."</th>";
				}
			$stream.="</tr>";
			$stream.="</thead>";
			$stream.="<tbody>";
			
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>CPO</td>";
				foreach($arrtanggal as $dttgl){
					$stream.="<td align=right>".hidezerodecimal($dtcpo[$dttgl],1)."</td>";
				}
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>PK</td>";
				foreach($arrtanggal as $dttgl){
					$stream.="<td align=right>".hidezerodecimal($dtpk[$dttgl],1)."</td>";
				}
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>FFA</td>";
				foreach($arrtanggal as $dttgl){
					$stream.="<td align=right>".hidezerodecimal($dtffa[$dttgl],1)."</td>";
				}
			$stream.="</tr>";
			$stream.="</tbody>";
				
		$stream.="</table>";
		
		
		
		
		
		
		switch($param['tipe']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "daily_produksi_".$param['kodeunit']."_".$param['periode'].".xls";
				$xls = new HtmlExcel();
				$xls->setCss($css);
				$xls->addSheet("data", $stream);
				$xls->headers($nop);
				echo $xls->buildFile();
			break;
			// break;
			case'pdf':
			$dompdf = new Dompdf();
			$dompdf->load_html($stream);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("Biaya_Admin_".$param['kodeunit']."_".$param['tanggalmulai']."_".$param['tanggalsampai']."_",array("Attachment"=>0));
			break;
		}
	break;
	
}



?>