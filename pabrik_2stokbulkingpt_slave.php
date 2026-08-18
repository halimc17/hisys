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

if($param['unit']==''){
	exit("Warning:Unit tidak boleh kosong");
}

if($param['kodebarang']==''){
	exit("Warning:Komoditi tidak boleh kosong");
}

if($param['tanggal1']=='' || $param['tanggal2']==''){
	exit("Warning:Tanggal tidak boleh kosong");
}

$arrtanggal=rangeTanggalarr(tanggalsystemn($param['tanggal1']),tanggalsystemn($param['tanggal2']));
$tanggalkemarin=tglkemarin(tanggalsystemn($param['tanggal1']));

$str="select distinct(kodept) as kodept from ".$dbname.".pabrik_stokbulking where  kodeunit='".$param['unit']."' and kodebarang='".$param['kodebarang']."' ";
$res=fetchdata($str);
foreach($res as $bar){
	$arrkodept[$bar['kodept']]=$bar['kodept'];
} 


#= query stok tangki
$str="select * from ".$dbname.".pabrik_stokbulking where  kodeunit='".$param['unit']."' and tanggal between '".tglkemarin(tanggalsystemn($param['tanggal1']))."' and  '".tanggalsystemn($param['tanggal2'])."' and kodebarang='".$param['kodebarang']."' ";
$res=fetchdata($str);
foreach($res as $bar){
	$dtkgakhir[$bar['tanggal']][$bar['kodept']]=$bar['jumlah'];
	$dtkgawal[$bar['tanggal']][$bar['kodept']]=$bar['jumlah'];
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
			$stream.="<th rowspan=3 align=center>".$_SESSION['lang']['tanggal']."</th>";
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
				$stream.="<th align=center>".$_SESSION['lang']['saldoawal']."</th>";
				$stream.="<th align=center>Mutasi Stok</th>";
				$stream.="<th align=center>".$_SESSION['lang']['saldoakhir']."</th>";
			}
			$stream.="<th align=center>".$_SESSION['lang']['saldoawal']."</th>";
			$stream.="<th align=center>Mutasi Stok</th>";
			$stream.="<th align=center>".$_SESSION['lang']['saldoakhir']."</th>";
		$stream.="</tr>";
		$stream.="</thead>";
		$stream.="<tbody>";
		
		foreach($arrtanggal as $dttgl){
			$stream.="<tr class=rowcontent>";		
				$stream.="<td>".tanggalnormal($dttgl)."</td>";
				foreach($arrkodept as $dtkodept){
					$stream.="<td align=right>".number_format($dtkgawal[tglkemarin($dttgl)][$dtkodept])."</td>";
						@$dtkgmutasi[$dttgl][$dtkodept]=$dtkgakhir[$dttgl][$dtkodept]-$dtkgawal[tglkemarin($dttgl)][$dtkodept];
					$stream.="<td align=right>".number_format($dtkgmutasi[$dttgl][$dtkodept])."</td>";
					$stream.="<td align=right>".number_format($dtkgakhir[$dttgl][$dtkodept])."</td>";
					
					@$tdtkgawal[$dttgl]+=$dtkgawal[tglkemarin($dttgl)][$dtkodept];
					@$tdtkgmutasi[$dttgl]+=$dtkgmutasi[$dttgl][$dtkodept];
					@$tdtkgakhir[$dttgl]+=$dtkgakhir[$dttgl][$dtkodept];
				}
				$stream.="<td align=right>".number_format($tdtkgawal[$dttgl])."</td>";
				$stream.="<td align=right>".number_format($tdtkgmutasi[$dttgl])."</td>";
				$stream.="<td align=right>".number_format($tdtkgakhir[$dttgl])."</td>";
			$stream.="</tr>";
		}
	$stream.="</tbody>";
	$stream.="</table>";




switch ($method) {
	
	case'preview':
		switch($param['tipe']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "STOK_".$param['tanggal1']."_sampai_".$param['tanggal2'].".xls";
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