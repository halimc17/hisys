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


if($param['kodebarang']!=''){
	$wherekodebarang=" and kodebarang='".$param['kodebarang']."'";
}

if($param['tanggal1']=='' || $param['tanggal2']==''){
	exit("Warning:Tanggal tidak boleh kosong");
}




$arrtanggal=rangeTanggalarr(tanggalsystemn($param['tanggal1']),tanggalsystemn($param['tanggal2']));
$tanggalkemarin=tglkemarin(tanggalsystemn($param['tanggal1']));

$str="select * from ".$dbname.".pmn_kontrakjual_vw where tanggalkontrak between '".tanggalsystemn($param['tanggal1'])."' and '".tanggalsystemn($param['tanggal2'])."' ".$wherekodebarang." order by tanggalkontrak desc";
// echo $str;exit();
$res=fetchdata($str);
foreach($res as $bar){
	$arrkodept[$bar['kodept']]=$bar['kodept'];
	$arrperiode[substr($bar['tanggalkontrak'],0,7)]=substr($bar['tanggalkontrak'],0,7);
	$dtqty[substr($bar['tanggalkontrak'],0,7)][$bar['kodept']]+=$bar['kuantitaskontrak'];
	$dtrp[substr($bar['tanggalkontrak'],0,7)][$bar['kodept']]+=$bar['nilaikontrak'];
} 



$cspanpt=count($arrkodept);

if($param['tipe']=='html'){
	$border='border=0';
}else{
	$border='border=1';
}

	$stream.="<table class=sortable cellspacing=1 ".$border." width=100%>";
	$stream.="<thead>";
		$stream.="<tr class=rowheader>";		
			$stream.="<th rowspan=3 align=center>".$_SESSION['lang']['periode']."</th>";
			$stream.="<th align=center colspan=".($cspanpt*3).">".$_SESSION['lang']['pt']."</th>";
			$stream.="<th rowspan=2 colspan=3 align=center>".$_SESSION['lang']['total']."</th>";
		$stream.="</tr>";
		$stream.="<tr class=rowheader>";
			foreach($arrkodept as $dtkodept){
				$stream.="<th colspan=3  align=center>".$dtkodept."</th>";
			}
		$stream.="</tr>";
		$stream.="<tr class=rowheader>";
			foreach($arrkodept as $dtkodept){
				$stream.="<th align=center>".$_SESSION['lang']['kuantitas']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['nilai']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['rerata']."</th>";
			}
			
			$stream.="<th align=center>".$_SESSION['lang']['kuantitas']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['nilai']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['rerata']."</th>";
		$stream.="</tr>";
		$stream.="</thead>";
		$stream.="<tbody>";
		
		foreach($arrperiode as $dtper){
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>".$dtper."</td>";
				foreach($arrkodept as $dtkodept){
					$stream.="<td align=right>".hidezerodecimal($dtqty[$dtper][$dtkodept])."</td>";
					$stream.="<td align=right>".hidezerodecimal($dtrp[$dtper][$dtkodept])."</td>";
					$stream.="<td align=right>".hidezerodecimal(fixnan($dtrp[$dtper][$dtkodept]/$dtqty[$dtper][$dtkodept]),2)."</td>";
					
					@$tdtqtyper[$dtper]+=$dtqty[$dtper][$dtkodept];
					@$tdtdtrpper[$dtper]+=$dtrp[$dtper][$dtkodept];
					
					@$tdtqtykodept[$dtkodept]+=$dtqty[$dtper][$dtkodept];
					@$tdtrpkodept[$dtkodept]+=$dtrp[$dtper][$dtkodept];
					
					@$ttdtqty+=$dtqty[$dtper][$dtkodept];
					@$ttdtrp+=$dtrp[$dtper][$dtkodept];
				}
				$stream.="<td align=right>".hidezerodecimal($tdtqtyper[$dtper])."</td>";
				$stream.="<td align=right>".hidezerodecimal($tdtdtrpper[$dtper])."</td>";
				$stream.="<td align=right>".hidezerodecimal(fixnan($tdtdtrpper[$dtper]/$tdtqtyper[$dtper]),2)."</td>";
			$stream.="</tr>";
		}
			$stream.="<td align=center>Total</td>";
		foreach($arrkodept as $dtkodept){
			$stream.="<td align=right>".hidezerodecimal($tdtqtykodept[$dtkodept])."</td>";
			$stream.="<td align=right>".hidezerodecimal($tdtrpkodept[$dtkodept])."</td>";
			$stream.="<td align=right>".hidezerodecimal(fixnan($tdtrpkodept[$dtkodept]/$tdtqtykodept[$dtkodept]),2)."</td>";
		}
		$stream.="<td align=right>".hidezerodecimal($ttdtqty)."</td>";
			$stream.="<td align=right>".hidezerodecimal($ttdtrp)."</td>";
			$stream.="<td align=right>".hidezerodecimal(fixnan($ttdtrp/$ttdtqty),2)."</td>";
	$stream.="</tbody>";
	$stream.="</table>";




switch ($method) {
	
	case'preview':
		switch($param['tipe']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "Summary_Kontrak_".$param['tanggal1']."_sampai_".$param['tanggal2'].".xls";
				$xls = new HtmlExcel();
				$xls->setCss($css);
				$xls->addSheet("data", $stream);
				$xls->headers($nop);
				echo $xls->buildFile();
			break;
			case'pdf':
				$dompdf = new Dompdf();
				$dompdf->loadHtml($stream);
				$dompdf->setPaper('A4', 'landscape');
				$dompdf->render();
				$dompdf->stream("Stok",array("Attachment"=>0));
			break;
		}
	break;
	
	
	
}



?>