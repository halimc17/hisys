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

$tanggalsystem1 = tanggalsystemn($param['tanggal1']);
$tanggalsystem2 = tanggalsystemn($param['tanggal2']);

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
		
		#= validasi data harus terisi
		if($param['kodept']==''){
			exit("Warningsystem:PT tidak boleh kosong");
		}
		if($param['tanggal1']==''){
			exit("Warningsystem:Tanggal tidak boleh kosong");
		}
		if($param['tanggal2']==''){
			exit("Warningsystem:Tanggal tidak boleh kosong");
		}
		
		#= filter where yang bisa seluruhnya
		$where='';
		
		if($param['kodecustomer']!=''){
			$where.=" and kodecustomer='".$param['kodecustomer']."'";
		}
		if($param['kodeunit']!=''){
			$where.=" and unit='".$param['kodeunit']."'";
		}
		
		
		
		#= array untuk customer
		$str="select * from ".$dbname.".pmn_4customer";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namacustomer[$bar['kodecustomer']]=$bar['namacustomer'];
		}
		
		#= array nama unit
		$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$param['kodept']."' or induk='".$param['kodept']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namaorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
		}

        # Data Kebun SPB Vw
        $sql = "SELECT * FROM {$dbname}.kebun_spb_vw WHERE tanggalpanen BETWEEN '".$tanggalsystem1."' AND '".$tanggalsystem2."'";
        $res = fetchData($sql,"OBJECT");
        
        foreach($res as $v) {
            $nospb[$v->nospb] = $v->nospb;
            $arrdataspb[$v->nospb][$v->tanggalpanen][$v->blok][$v->tph] = $v->nospb;
            $tahuntanamspb[$v->nospb][$v->tanggalpanen][$v->blok][$v->tph] = $v->tahuntanamspb;
            $jjgspb[$v->nospb][$v->tanggalpanen][$v->blok][$v->tph] = $v->jjg;
            $bjrspb[$v->nospb][$v->tanggalpanen][$v->blok][$v->tph] = $v->bjr;
            $kgbrutospb[$v->nospb][$v->tanggalpanen][$v->blok][$v->tph] = $v->kgwb;
            $kgnettospb[$v->nospb][$v->tanggalpanen][$v->blok][$v->tph] = $v->kgwbnetto;
        }
        
        # Data Pabrik Vw
        $sql = "SELECT * FROM {$dbname}.pabrik_timbangan_vw WHERE nospb IN ('".implode("','",$nospb)."')";
        $res = fetchData($sql,"OBJECT");
        
        foreach($res as $v) {
            $tanggaltimbang[$v->nospb] = $v->tanggal;
        } 

        // echo "<pre>";
        // print_r($res);
        // exit("warning");

		$stylekolomdt='';
		if($param['tipe']=='html'){
			$stylekolom='border=0 cellspacing=1';
		}else if($param['tipe']=='pdf'){
			$stylekolom='border=0 cellspacing=0 cellpadding=2';
			$stylekolomdt="style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;border-right:0.5px solid #000000;'";
		}else if($param['tipe']=='excel'){
			$stylekolom='border=1 cellspacing=1';
		}
		if($param['tipe']=='pdf'){
			$stream="<style>
				footer .pagenum:before {
				  content: counter(page);
				}
				
				@page {
					margin-top: 50px;
					margin-left: 50px;
					margin-right: 50px;
					margin-bottom: 50px;
				}
				body {
					font-family: Tahoma, Verdana, Segoe, sans-serif;
				}
				
				footer {
					position: fixed; 
					bottom: -20px; 
					left: 0px; 
					right: 0px;
					height: 50px; 
				}
				
			</style>";
		}
		
		// $border='border=0';
		$stream.="<table class=sortable ".$stylekolom." width=100%>";
		$stream.="<thead>";
			if($param['tipe']!='html'){
				$stream.="<tr class=rowheader>";	
					$stream.="<th colspan=3 align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$namaorganisasi[$param['kodept']]."</th>";
					$stream.="<th align=center colspan=6 style='font-size:20px;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>Penjualan ".tanggalnormal($param['tanggal1'])." s/d ".tanggalnormal($param['tanggal2'])."</th>";
				$stream.="</tr>";	
				$stream.="<tr class=rowheader>";	
					$stream.="<th colspan='3' align=left style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>&nbsp;</th>";
				$stream.="</tr>";	
				$stream.="<tr class=rowheader>";	
					$stream.="<th align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['lokasi']."</th>";
					$stream.="<th colspan='".($param['bulan']+4)."' align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>: ".$namaorganisasi[$param['kodeunit']]."</th>";
				$stream.="</tr>";	
			
				$stream.="<tr class=rowheader>";	
					$stream.="<th align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['customer']."</th>";
					$stream.="<th colspan='".($param['bulan']+4)."' align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>: ".$param['kodecustomer']." - ".$namacustomer[$param['kodecustomer']]."</th>";
				$stream.="</tr>";	
			}
			

			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center ".$stylekolomdt.">NO</th>";
				$stream.="<th align=center ".$stylekolomdt.">BULAN</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['tanggal']." PANEN</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['tanggal']." TIMBANG</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['tahuntanam']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">".$_SESSION['lang']['divisi']."</th>";
				$stream.="<th align=center ".$stylekolomdt.">BLOK</th>";
				$stream.="<th align=center ".$stylekolomdt.">TBS</th>";
				$stream.="<th align=center ".$stylekolomdt.">BJR</th>";
				$stream.="<th align=center ".$stylekolomdt.">KG BRUTO</th>";
				$stream.="<th align=center ".$stylekolomdt.">KG NETTO</th>";
			$stream.="</tr>";	
			
			
			$stream.="</thead>";
			$stream.="<tbody>";
			
		$no = 0;
		foreach($arrdataspb as $nospb => $valn) {
            foreach($valn as $tglpanen => $valt) {
                foreach($valt as $blok => $valb) {
                    foreach($valb as $tph => $valtp) {
                        $no++;

                        $stream.="<tr class=rowcontent>";		
                            $stream.="<td valign=top ".$stylekolomdt." align=center>".$no."</td>";
                            $stream.="<td valign=top ".$stylekolomdt." align=center>".substr($tglpanen,5,2)."</td>";
                            $stream.="<td valign=top ".$stylekolomdt." align=center>".tanggalnormal($tglpanen)."</td>";
                            $stream.="<td valign=top ".$stylekolomdt." align=center>".tanggalnormal($tanggaltimbang[$nospb])."</td>";
                            $stream.="<td valign=top ".$stylekolomdt." align=center>".$tahuntanamspb[$nospb][$tglpanen][$blok][$tph]."</td>";
                            $stream.="<td valign=top ".$stylekolomdt." align=center>".substr($blok,0,6)."</td>";
                            $stream.="<td valign=top ".$stylekolomdt." align=center>".$blok."</td>";
                            $stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($jjgspb[$nospb][$tglpanen][$blok][$tph])."</td>";
                            $stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($bjrspb[$nospb][$tglpanen][$blok][$tph])."</td>";
                            $stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($kgbrutospb[$nospb][$tglpanen][$blok][$tph])."</td>";
                            $stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($kgnettospb[$nospb][$tglpanen][$blok][$tph])."</td>";
                        $stream.="</tr>";		

                        $totalTbs+=$jjgspb[$nospb][$tglpanen][$blok][$tph];
                        $totalKgbruto+=$kgbrutospb[$nospb][$tglpanen][$blok][$tph];
                        $totalKgnetto+=$kgnettospb[$nospb][$tglpanen][$blok][$tph];
                    }
                }
            }
        }

        $stream.="<tr class=rowcontent>";		
            $stream.="<td colspan=7 valign=top ".$stylekolomdt." align=right></td>";
            $stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($totalTbs)."</td>";
            $stream.="<td valign=top ".$stylekolomdt." align=right></td>";
            $stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($totalKgbruto)."</td>";
            $stream.="<td valign=top ".$stylekolomdt." align=right>".hidezerodecimal($totalKgnetto)."</td>";
        $stream.="</tr>";

		$stream.="</tbody>";

		$stream.="</table>";

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
				$dompdf->setPaper('A4', 'landscape');
				// $customPaper = array(0,0,2000,1300);
				// $dompdf->set_paper($customPaper,'landscape');
				$dompdf->render();
				$dompdf->stream("Penualan_".$param['kodept']."_",array("Attachment"=>0));
				// $canvas = $dompdf->get_canvas();
				// if (file_exists($filepdf)){
					// unlink($filepdf);
				// // }
				// $filepdf='a.pdf';
				// file_put_contents($filepdf, $dompdf->output());
			break;
		}
	break;
}



?>