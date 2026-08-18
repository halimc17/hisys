<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
error_reporting(0);
use Dompdf\Dompdf;
$stream='';

$method = checkPostGet('method','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}


// echo"<pre>";
// print_r($stdtjumlahbulan);


switch ($method) {
	
	case'getunit':
		$optunit="<option value=''>". $_SESSION['lang']['all']."</option>";
		$str="select * from ".$dbname.".organisasi where induk='".$param['kodept']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
		  $optunit.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
		} 
		echo $optunit;
	break;
	
	
	case'preview':
	
		 
		if($param['tipe']=='html'){
			$stylekolom='border=0 cellspacing=1';
		} else if($param['tipe']=='excel'){
			$stylekolom='border=1 cellspacing=1';
		}
		
		
		
	
		// $border='border=0';
		$stream.="<table class=sortable ".$stylekolom." >";
		$stream.="<thead>";
			$stream.="<tr class=rowheader>";
				$stream.="<th align=center>Nomor</th>";
				$stream.="<th align=center>Tanggal Terima Dokumen</th>";
				$stream.="<th align=center>No Invoice</th>";
				$stream.="<th align=center>Supplier</th>";
				$stream.="<th align=center>NPWP</th>";
				$stream.="<th align=center>Jenis Transaksi</th>";
				$stream.="<th align=center>Tanggal PO</th>";
				$stream.="<th align=center>No Dokumen</th>";
				$stream.="<th align=center>Nilai Transaksi</th>";
				$stream.="<th align=center>PPN Masukan</th>";
				$stream.="<th align=center>PPh</th>";
				$stream.="<th align=center>Total</th>";
				$stream.="<th align=center>Tanggal Jatuh Tempo</th>";
$stream.="<th align=center>".$_SESSION['lang']['kasbank']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['sisa']."</th>";
				
				$stream.="<th align=center>Belum Jatuh Tempo</th>";
				$stream.="<th align=center>1-30 Hari</th>";
				$stream.="<th align=center>31-60 Hari</th>";
				$stream.="<th align=center>61-90 Hari</th>";
				$stream.="<th align=center>90- Hari</th>";
				$stream.="<th align=center>Jumlah Hari Terlambat</th>";
			$stream.="<tr>";			
			
			$stream.="</thead>";
			$stream.="<tbody>";
			
			

			if ($param['kodept']!='') {
				$where.=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."')";
			}

			if ($param['kodeunit']!='') {
				$where.=" and kodeorg='".$param['kodeunit']."'";
			}
			
			if ($param['kodesupplier']!='') {
				$where.=" and kodesupplier='".$param['kodesupplier']."'";
			}
			
			if ($param['noakun']!='') {
				$where.=" and noakun='".$param['noakun']."'";
			}
			
			if ($param['nodok']!='') {
				$where.=" and nodok like '%".$param['nodok']."%'";
			}
			if ($param['tanggal']!='') {
				$where.=" and tanggal <= '".tanggalsystemn($param['tanggal'])."'";
				@$wheretglkas.=" and tanggal <= '".tanggalsystemn($param['tanggal'])."'";
			}
			
			
			$str="select noakun,sum(jumlah) as jumlah,sum(debet) as debet,sum(kredit) as kredit,nodok,kodesupplier,tanggal,substr(tanggal,1,7) as periode from ".$dbname.".keu_jurnaldt_vw where 1=1 ".$where." group by nodok having jumlah!=0";
			$res=fetchdata($str);
			foreach($res as $bar){
				$arrnodok[$bar['nodok']]=$bar['nodok'];
				$dtnilaijurnal[$bar['nodok']]=$bar['jumlah'];
			}
			
			$str="select * from ".$dbname.".keu_tagihanht where nopo  in ('".implode("','",$arrnodok)."')";
			$res=fetchdata($str);
			foreach($res as $bar){
				$arrnoinvoice[$bar['noinvoice']]=$bar['noinvoice'];
				$arrkodesupplier[$bar['kodesupplier']]=$bar['kodesupplier'];
				$lsnoinvoice[$bar['nopo']][$bar['noinvoice']]=$bar['noinvoice'];
				$dttglinvoice[$bar['noinvoice']]=$bar['tanggal'];
				$dtkodesupplier[$bar['noinvoice']]=$bar['kodesupplier'];
				$dtnpwp[$bar['noinvoice']]=$bar['npwp'];
				$dttipeinvoice[$bar['noinvoice']]=$bar['tipeinvoice'];
				$dtnilaiinvoice[$bar['noinvoice']]=$bar['nilaiinvoice'];
				$dtnilaidpp[$bar['noinvoice']]=$bar['nilaiinvoice'];
				$dtjatuhtempo[$bar['noinvoice']]=$bar['jatuhtempo'];
			}
			
			$str="select * from ".$dbname.".log_poht where nopo  in ('".implode("','",$arrnodok)."')";
			$res=fetchdata($str);
			foreach($res as $bar){
				$dttglnodok[$bar['nopo']]=$bar['tanggal'];
			}
			
			$str="select * from ".$dbname.".log_5supplier where supplierid  in ('".implode("','",$arrkodesupplier)."')";
			$res=fetchdata($str);
			foreach($res as $bar){
				$dtnamasupplier[$bar['supplierid']]=$bar['namasupplier'];
			}
			
			$str="select * from ".$dbname.".keu_tagihandt where noinvoice  in ('".implode("','",$arrnoinvoice)."')";
			$res=fetchdata($str);
			foreach($res as $bar){
				if(substr($bar['noakun'],0,3)=='117'){ // ppn
					$dtnilaippn[$bar['noinvoice']]+=$bar['nilai'];
				}
				if(substr($bar['noakun'],0,3)=='213'){ // ppn
					$dtnilaipph[$bar['noinvoice']]+=($bar['nilai'])*(-1);
				}
			}
			
			
			#= dari invoice
			// $str="select * from ".$dbname.".keu_kasbankdtht_vw where keterangan1 in ('".implode("','",$arrnoinvoice)."')";
			$str="select * from ".$dbname.".keu_kasbankdtht_vw where keterangan1 in ('".implode("','",$arrnoinvoice)."') ".$wheretglkas."";
			$res=fetchdata($str);
			foreach($res as $bar){
				if($bar['keterangan1']!=''){
					@$dtnilaikb[$bar['keterangan1']]+=$bar['jumlah'];
				}
			}
			
			
			
			foreach($arrnodok as $dtnodok){
				@$no++;
				$stream.="<tr class=rowcontent>";		
					$stream.="<td valign=top align=left>".$no."</td>";
					$stream.="<td valign=top align=left colspan=10>".$dtnodok."</td>";
					$stream.="<td valign=top align=right>".hidezerodecimal($dtnilaijurnal[$dtnodok])."</td>";
					$stream.="<td valign=top align=left colspan=9></td>";
				$stream.="</tr>";			
				foreach($arrnoinvoice as $dtnoinvoice){
					if($lsnoinvoice[$dtnodok][$dtnoinvoice]!=''){
						
						$dtnilaisisa[$dtnoinvoice]=$dtnilaiinvoice[$dtnoinvoice]-$dtnilaikb[$dtnoinvoice];
						
						$berapahari=0;
						if($dtnilaisisa[$dtnoinvoice]>0){
							$berapahari=selisiharitanpaabsolute($dtjatuhtempo[$dtnoinvoice],tanggalsystemn($param['tanggal']));
							if($berapahari<=0){
								$berapahari=0;
							}
							if($berapahari<1){
								@$dthari0[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
							}else if($berapahari<31){
								@$dthari1[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
							}else if($berapahari<61){
								@$dthari2[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
							}else if($berapahari<91){
								@$dthari3[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
							}else{
								@$dthari4[$dtnoinvoice]+=$dtnilaisisa[$dtnoinvoice];
							}
						}
						
						$stream.="<tr class=rowcontent>";	
							$stream.="<td valign=top align=left></td>";
							$stream.="<td valign=top align=left>".$dttglinvoice[$dtnoinvoice]."</td>";
							$stream.="<td valign=top align=left>".$dtnoinvoice."</td>";
							$stream.="<td valign=top align=left>".$dtnamasupplier[$dtkodesupplier[$dtnoinvoice]]."</td>";
							$stream.="<td valign=top align=left>".$dtnpwp[$dtnoinvoice]."</td>";
							$stream.="<td valign=top align=left>".$dttipeinvoice[$dtnoinvoice]."</td>";
							$stream.="<td valign=top align=left>".$dttglnodok[$dtnodok]."</td>";
							$stream.="<td valign=top align=left>".$dtnodok."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dtnilaidpp[$dtnoinvoice])."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dtnilaippn[$dtnoinvoice])."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dtnilaipph[$dtnoinvoice])."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dtnilaiinvoice[$dtnoinvoice])."</td>";
							$stream.="<td valign=top align=left>".$dtjatuhtempo[$dtnoinvoice]."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dtnilaikb[$dtnoinvoice])."</td>";
							
							$stream.="<td valign=top align=right>".hidezerodecimal($dtnilaisisa[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dthari0[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dthari1[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dthari2[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dthari3[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".hidezerodecimal($dthari4[$dtnoinvoice],2)."</td>";
							$stream.="<td valign=top align=right>".$berapahari."</td>";
							
						$stream.="</tr>";		
					}
				}
				@$tdtnilaijurnal+=$dtnilaijurnal[$dtnodok];
			}
			
			$stream.="<tr class=rowcontent>";	
				$stream.="<td valign=top align=center colspan=11><b>".$_SESSION['lang']['total']."</b></td>";
				$stream.="<td valign=top align=right><b>".hidezerodecimal($tdtnilaijurnal,2)."</b></td>";
			
					$stream.="<td valign=top align=left colspan=9></td>";
				
			$stream.="</tr>";	
			
			if($param['tipe']=='pdf'){
				$stream.="<footer>";
					$stream.="<div class=pagenum-container>Page <span class=pagenum></span></div>";
					$stream.=" ".date('d-m-Y')." ";
				$stream.="</footer>";	
			}
	
		switch($param['tipe']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "Aging_sampai_".$param['kodept']."_".$param['periode'].".xls";
				$xls = new HtmlExcel();
				$xls->setCss($css);
				$xls->addSheet("data", $stream);
				$xls->headers($nop);
				echo $xls->buildFile();
			break;
			
			// case'pdf':
				// $dompdf = new Dompdf();
				// $dompdf->loadHtml($stream);
				// $dompdf->setPaper('A3', 'landscape');
				// $dompdf->render();
				// $dompdf->stream("Aging_",array("Attachment"=>0));
			// break;
			
			case'pdf':
				$dompdf = new Dompdf();
				$dompdf->load_html($stream);
				$customPaper = array(0,0,2000,1300);
				$dompdf->set_paper($customPaper,'landscape');
				//$dompdf->setPaper('A4', 'landscape');
				$dompdf->render();
				$dompdf->stream("Aging_",array("Attachment"=>0));
				// $canvas = $dompdf->get_canvas();
				// if (file_exists($filepdf)){
					// unlink($filepdf);
				// // }
				// $filepdf='a.pdf';
				// file_put_contents($filepdf, $dompdf->output());
			break;
		}		



}

?>