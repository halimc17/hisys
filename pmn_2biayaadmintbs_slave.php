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
	
		if($param['kodeunit']==''){
			exit("Warning:Unit tidak boleh kosong");
		}
	
		if($param['tanggalmulai']==''){
			exit("Warning:PT tidak boleh kosong");
		}

		if($param['tanggalsampai']==''){
			exit("Warning:tanggalsampai tidak boleh kosong");
		}
		
		
		
		if ($param['tipetbs'] == 'SUPPLIERTBSAFI') {
			$table = 'kebun_tbsafiliasi';
			$where=" and pemilik='".$param['kodeunit']."'";
			$judullaporan='ALOKASI BIAYA ADMINISTRASI AFILIASI';
		} else if ($param['tipetbs'] == 'SUPPLIERTBSEXT') {
			$table = 'kebun_tbsexternal';
			$where=" and unit='".$param['kodeunit']."'";
			$judullaporan='ALOKASI BIAYA ADMINISTRASI SWADAYA';
		} else if ($param['tipetbs'] == 'SUPPLIERTBSKUD') {
			$table = 'kebun_tbskud';
			$where=" and pemilik='".$param['kodeunit']."'";
			$judullaporan='ALOKASI BIAYA ADMINISTRASI KUD';
		} 
		
		#= jika external pakai unit= ; jika afiliasi dan kud pakai pemilik
		
		
		#= tbs
		$str="select sum(kgnetto) as kgnetto,sum(totalrp) as totalrp,supplier from ".$dbname.".".$table." where 1=1 ".$where." and tanggaltbs1>='".tanggalsystemn($param['tanggalmulai'])."' and tanggaltbs2<='".tanggalsystemn($param['tanggalsampai'])."' group by supplier";
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrkodesupplier[$bar['supplier']]=$bar['supplier'];
			$dtkgnetto[$bar['supplier']]=$bar['kgnetto'];
			// $dtrptbs[$bar['supplier']]=$bar['totalrp'];
		} 
		
		
		#= fee
		$str="select sum(totalrp) as totalrp,kodesupplier,rekening from ".$dbname.".pmn_feetbs where kodesupplier in ('".implode("','",$arrkodesupplier)."') and  tanggaltbs1>='".tanggalsystemn($param['tanggalmulai'])."' and tanggaltbs2<='".tanggalsystemn($param['tanggalsampai'])."' group by kodesupplier,rekening";
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrrekening[$bar['rekening']]=$bar['rekening'];
			$dtrpfee[$bar['kodesupplier']][$bar['rekening']]=$bar['totalrp'];
			$lsrekening[$bar['kodesupplier']][$bar['rekening']]=$bar['rekening'];
		} 
		
		#= nama supplier
		$str="select * from ".$dbname.".log_5supplier where  supplierid in ('".implode("','",$arrkodesupplier)."')";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namasupplier[$bar['supplierid']]=$bar['namasupplier'];
		}
		
		
		
		#= nama supplier
		$str="select * from ".$dbname.".log_5rekbank where  supplierid in ('".implode("','",$arrkodesupplier)."')";
		$res=fetchdata($str);
		foreach($res as $bar){
			$idbank[$bar['rekening']]=$bar['idbank'];
			$anrekening[$bar['rekening']]=$bar['an'];
		}
		
		$str="select * from ".$dbname.".keu_5daftarbank";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namabank[$bar['kodebank']]=$bar['namabank'];
		}

		if($param['tipe']=='html'){
			$stylekolom='border=0 cellspacing=1';
		}else if($param['tipe']=='pdf'){
			$stylekolom='border=1 cellspacing=0';
			
			$stream.="<table class=sortable  width=100% border=0>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6><b>".$judullaporan."<b></th>";
			$stream.="</tr>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6><b>PERIODE ".tanggalnormal($param['tanggalmulai'])." s/d ".tanggalnormal($param['tanggalsampai'])."<b></th>";
			$stream.="</tr>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6>&nbsp;</th>";
			$stream.="</tr>";
			$stream.="</table>";
			
		}else if($param['tipe']=='excel'){
			$stylekolom='border=1 cellspacing=1';
			
			$stream.="<table class=sortable  width=100% border=0>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6><b>".$judullaporan."<b></th>";
			$stream.="</tr>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6><b>PERIODE ".$param['tanggalmulai']." s/d ".$parram['tanggalsampai']."<b></th>";
			$stream.="</tr>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center colspan=6>&nbsp;</th>";
			$stream.="</tr>";
			$stream.="</table>";
		}
		/*
		$stream.="<th align=center style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['nodok']."</th>";
		*/
		// $border='border=0';
		$stream.="<table class=sortable ".$stylekolom." width=100%>";
		$stream.="<thead>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['supplier']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['namawkak']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['netto']." (Kg)</th>";
				$stream.="<th align=center>".$_SESSION['lang']['subsidi']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['potongan']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['total']." (Rp)</th>";
				$stream.="<th align=center>".$_SESSION['lang']['rekening']."</th>";
				
			$stream.="</tr>";
			$stream.="</thead>";
			$stream.="<tbody>";
			$no=0;
			foreach($arrkodesupplier as $dtkodesupplier){
				foreach($arrrekening as $dtrekening){
						if($lsrekening[$dtkodesupplier][$dtrekening]!=''){
						$no++;
						$stream.="<tr class=rowcontent>";		
							$stream.="<td align=center>".$no."</td>";
							$stream.="<td>".$namasupplier[$dtkodesupplier]."</td>";
							$stream.="<td>".$anrekening[$dtrekening]."</td>";
							$stream.="<td align=right>".hidezerodecimal($dtkgnetto[$dtkodesupplier])."</td>";
							$stream.="<td align=right>".hidezerodecimal($dtrpfee[$dtkodesupplier][$dtrekening]/$dtkgnetto[$dtkodesupplier],2)."</td>";
							$stream.="<td align=right></td>";
							$stream.="<td align=right>".hidezerodecimal($dtrpfee[$dtkodesupplier][$dtrekening])."</td>";
							$stream.="<td>".$namabank[$idbank[$dtrekening]]." ".$dtrekening."</td>";
							@$stdtkgnetto+=$dtkgnetto[$dtkodesupplier];
							@$stdtrpfee+=$dtrpfee[$dtkodesupplier][$dtrekening];
						$stream.="</tr>";
					}
				}
			}
			$stream.="<tr class=rowcontent>";		
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="<td></td>";
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="<td></td>";
				$stream.="<td align=right><b>".hidezerodecimal($stdtkgnetto)."</b></td>";
				$stream.="<td align=right><b>".hidezerodecimal($stdtrpfee/$stdtkgnetto,2)."</b></td>";
				$stream.="<td align=right></td>";
				$stream.="<td align=right><b>".hidezerodecimal($stdtrpfee,2)."</b></td>";
				$stream.="<td align=right></td>";
			$stream.="</tr>";
		$stream.="</tbody>";
		$stream.="</table>";

		$stream.=getketeranganttd('pmn_2biayaadmintbs',$param['tipe'],$param['kodeunit']);	

		
		switch($param['tipe']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "Biaya_Admin_".$param['kodeunit']."_".$param['tanggalmulai']."_".$param['tanggalsampai'].".xls";
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