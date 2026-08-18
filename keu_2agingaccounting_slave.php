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
		if($param['noakun']==''){
			exit("Warningsystem:Noakun tidak boleh kosong");
		}
		if($param['bulan']==''){
			exit("Warningsystem:Jumlah bulan tidak boleh kosong");
		}
		if($param['periode']==''){
			exit("Warningsystem:periode tidak boleh kosong");
		}
		if($param['subledger']==''){
			exit("Warningsystem:subledger tidak boleh kosong");
		}
		
		#= filter where yang bisa seluruhnya
		$where='';
		if($param['nodok']!=''){
			$where.=" and nodok like '%".$param['nodok']."%'";
		}
		if($param['kodesupplier']!=''){
			$where.=" and kodesupplier='".$param['kodesupplier']."'";
		}
		if($param['kodeunit']!=''){
			$where.=" and kodeorg='".$param['kodeunit']."'";
		}

		#= bentuk tanggal akhir dan awal
		$tanggalawal=$param['periode'].'-01';
		$tanggalakhir=tglakhir($param['periode'].'-01');
		
		#= bentuk array bulan
		for($i=0;$i<=$param['bulan'];$i++){
			$arrbulan[$i]=$i;
		}
		
		#= ambil nodok sebelum periode ini dan nilai tidak 0 (masih ada saldo di periode ini)
		$str="select sum(jumlah) as jumlah,nodok,".$param['subledger']." as subledger,tanggal,substr(tanggal,1,7) as periode from ".$dbname.".keu_jurnaldt where  noakun='".$param['noakun']."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."') and tanggal < '".$tanggalawal."' ".$where." group by nodok having jumlah!=0 order by tanggal asc";
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrsubledger[$bar['subledger']]=$bar['subledger'];
			$arrnodok[$bar['nodok']]=$bar['nodok'];
			$dtperiodedokumen[$bar['nodok']]=$bar['periode'];
		} 
		
		
		#= ambil nodok diperiode ini
		$str="select sum(jumlah) as jumlah,nodok,".$param['subledger']." as subledger,tanggal,substr(tanggal,1,7) as periode from ".$dbname.".keu_jurnaldt where  noakun='".$param['noakun']."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."') and substr(tanggal,1,7)='".$param['periode']."' and nodok!='' ".$where." group by nodok  order by tanggal asc";		
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrsubledger[$bar['subledger']]=$bar['subledger'];
			$arrnodok[$bar['nodok']]=$bar['nodok'];
			// if($dtperiodedokumen[$bar['nodok']]==''){
				// $dtperiodedokumen[$bar['nodok']]=$bar['periode'];
			// }
		}

		#= Patok untuk di hidden 
		#= Pak Sabinus diskusi dengan Kak Juli untuk hilangkan nojurnal itu agar menggambarkan nilainya di neraca saldo
		#= Kak Juli tanggal 21-11-2022
		if ($param['noakun'] == "1130101") {
			$wherex .= " and nojurnal != '20220901/BPRO/M/002'";
		}
		
		#= query ambil data berdasarkan nomor dokumen
		$str="select *,".$param['subledger']." as subledger from ".$dbname.".keu_jurnaldt where  nodok in ('".implode("','",$arrnodok)."') and noakun='".$param['noakun']."' and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."') and substr(tanggal,1,7) <= '".$param['periode']. "' ".$wherex." ".$where." order by tanggal asc";
		// echo $str;exit();
		$arrnodok=array();// dikosongkan arrnodok buat bentuk urutan by tanggal
		$res=fetchdata($str);
		foreach($res as $bar){
			
			#= ini dipatok dlu, lakukan pengecekan ulang kspe, 1160205 2021-12
			if($param['noakun']=='1160205'){
				$arrsubledger[$bar['subledger']]=$bar['subledger'];
			}
			
			$dtketerangan[$bar['subledger']][$bar['nodok']][$bar['tanggal']][$bar['nojurnal']][$bar['nourut']]=$bar['keterangan'];
			
			if(@$dtperiodedokumen[$bar['nodok']]==''){
				$dtperiodedokumen[$bar['nodok']]=substr($bar['tanggal'],0,7);
			}
			
			$dtselisihperiode[$bar['subledger']][$bar['nodok']][$bar['tanggal']][$bar['nojurnal']][$bar['nourut']]=selisihperiode($dtperiodedokumen[$bar['nodok']],$param['periode']);
			
			if($dtselisihperiode[$bar['subledger']][$bar['nodok']][$bar['tanggal']][$bar['nojurnal']][$bar['nourut']]>=$param['bulan']){
				@$dtjumlah[$bar['subledger']][$bar['nodok']][$bar['tanggal']][$bar['nojurnal']][$bar['nourut']][$param['bulan']]+=$bar['jumlah'];
			}else{
				@$dtjumlah[$bar['subledger']][$bar['nodok']][$bar['tanggal']][$bar['nojurnal']][$bar['nourut']][$dtselisihperiode[$bar['subledger']][$bar['nodok']][$bar['tanggal']][$bar['nojurnal']][$bar['nourut']]]+=$bar['jumlah'];
			}
		}
		// echo $str;exit();
		// echo"<pre>";
		// print_r($dtperiodedokumen);
		// exit();
		#= array untuk supplier
		$str="select * from ".$dbname.".log_5supplier where  supplierid in ('".implode("','",$arrsubledger)."')";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namasubledger[$bar['supplierid']]=$bar['namasupplier'];
		}
		
		$str="select * from ".$dbname.".datakaryawan where karyawanid in ('".implode("','",$arrsubledger)."')";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namasubledger[$bar['karyawanid']]=$bar['namakaryawan'];
			$niksubledger[$bar['karyawanid']]=$bar['nik'];
		}
		
		$str="select * from ".$dbname.".pmn_4customer where  kodecustomer in ('".implode("','",$arrsubledger)."')";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namasubledger[$bar['kodecustomer']]=$bar['namacustomer'];
		}
		
		#= array nama akun
		$str="select * from ".$dbname.".keu_5akun where noakun like '2%'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namaakun[$bar['noakun']]=$bar['namaakun'];
		}
		
		#= array nama unit
		$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$param['kodept']."' or induk='".$param['kodept']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namaorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
		}


		if($param['tipe']=='html'){
			$stylekolom='border=0 cellspacing=1';
		}else if($param['tipe']=='pdf'){
			$stylekolom='border=0 cellspacing=0';
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
		$stream.="<table class=sortable ".$stylekolom." >";
		$stream.="<thead>";
			if($param['tipe']!='html'){
				$stream.="<tr class=rowheader>";	
					$stream.="<th colspan=3 align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$namaorganisasi[$param['kodept']]."</th>";
					$stream.="<th colspan=5 align=center  style='font-size:20px;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>AP Aging ".tanggalnormal(tglakhir($param['periode'].'-01'))."</th>";
				$stream.="</tr>";	
				$stream.="<tr class=rowheader>";	
					$stream.="<th colspan='3' align=left style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>&nbsp;</th>";
				$stream.="</tr>";	
				$stream.="<tr class=rowheader>";	
					$stream.="<th align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['lokasi']."</th>";
					$stream.="<th colspan='".($param['bulan']+4)."' align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>: ".$namaorganisasi[$param['kodeunit']]."</th>";
				$stream.="</tr>";	
				$stream.="<tr class=rowheader>";	
					$stream.="<th align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['noakun']."</th>";
					$stream.="<th colspan='".($param['bulan']+4)."'  align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>: ".$param['noakun']." - ".$namaakun[$param['noakun']]."</th>";
				$stream.="</tr>";
				$stream.="<tr class=rowheader>";	
					$stream.="<th align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['supplier']."</th>";
					$stream.="<th colspan='".($param['bulan']+4)."' align=left style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>: ".$param['kodesupplier']." - ".$namasubledger[$param['kodesupplier']]."</th>";
				$stream.="</tr>";	
			}
			if($param['tipe']=='excel'){
				$stream.="<tr class=rowheader>";		
					$stream.="<th align=center>".$_SESSION['lang']['nodok']."</th>";
					$stream.="<th align=center>".$_SESSION['lang']['tanggal']."</th>";
					$stream.="<th align=center>".$_SESSION['lang']['nojurnal']."</th>";
					$stream.="<th align=center>".$_SESSION['lang']['keterangan']."</th>";
					foreach($arrbulan as $dtbulan){
						$stream.="<th align=center>".($dtbulan*30)."-Days</th>";
					}
					$stream.="<th align=center>".$_SESSION['lang']['total']."</th>";
				$stream.="</tr>";	
			}else{
				$stream.="<tr class=rowheader>";		
					$stream.="<th align=center style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['nodok']."</th>";
					$stream.="<th align=center align=center style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['tanggal']."</th>";
					$stream.="<th align=center align=center style=';border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['nojurnal']."</th>";
					$stream.="<th align=center align=center style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['keterangan']."</th>";
					foreach($arrbulan as $dtbulan){
						$stream.="<th align=center align=center style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".($dtbulan*30)."-Days</th>";
					}
					$stream.="<th align=center align=center style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['total']."</th>";
				$stream.="</tr>";	
				
			}
			
			$stream.="</thead>";
			$stream.="<tbody>";
			
			
			foreach($dtjumlah as $dtkodesupplier => $key1){
				$stream.="<tr class=rowcontent>";		
					if($param['subledger']=='nik'){
						$stream.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>".$niksubledger[$dtkodesupplier]."</td>";
					}else{
						$stream.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>".$dtkodesupplier."</td>";
					}
					
					$stream.="<td  colspan='".($param['bulan']+5)."' style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;border-right:0px solid #000000;'>".$namasubledger[$dtkodesupplier]."</td>";
				$stream.="</tr>";
				//@$dtjumlah[$bar['subledger']][$bar['nodok']][$bar['tanggal']][$bar['nojurnal']][$param['bulan']][norut]+=$bar['jumlah'];
				foreach($key1 as $dtnodok => $key2){
					foreach($key2 as $dttanggal => $key3){

						#= Patok untuk di hidden 
						#= Pak Sabinus diskusi dengan Kak Juli untuk hilangkan nojurnal itu agar menggambarkan di neraca saldo
						#= Kak Juli tanggal 21-11-2022 (Ganti di atas, di Query)
						// if($param['noakun'] == "1130101") {
						// 	unset($key3["20220901/BPRO/M/002"]);
						// }
						
						foreach($key3  as $dtnojurnal => $key4){
							foreach($key4 as $dtnourut => $key5){
								$stream.="<tr class=rowcontent>";		
									$stream.="<td valign=top>".$dtnodok."</td>";
									$stream.="<td valign=top align=center>".tanggalnormal($dttanggal)."</td>";
									$stream.="<td valign=top>".$dtnojurnal."</td>";
									$stream.="<td valign=top>".$dtketerangan[$dtkodesupplier][$dtnodok][$dttanggal][$dtnojurnal][$dtnourut]."</td>";
									foreach($arrbulan as $dtbulan){
										$stream.="<td align=right valign=top>".hidezerodecimal(@$dtjumlah[$dtkodesupplier][$dtnodok][$dttanggal][$dtnojurnal][$dtnourut][$dtbulan])."</td>";
										@$tdtjumlah[$dtkodesupplier][$dtnodok][$dttanggal][$dtnojurnal][$dtnourut]+=$dtjumlah[$dtkodesupplier][$dtnodok][$dttanggal][$dtnojurnal][$dtnourut][$dtbulan];//total kanan
										@$stdtjumlahbulan[$dtkodesupplier][$dtbulan]+=$dtjumlah[$dtkodesupplier][$dtnodok][$dttanggal][$dtnojurnal][$dtnourut][$dtbulan];//buat subtotal
									}
									$stream.="<td align=right valign=top>".hidezerodecimal(@$tdtjumlah[$dtkodesupplier][$dtnodok][$dttanggal][$dtnojurnal][$dtnourut])."</td>";
								$stream.="</tr>";	
							 }
						}
					}
				}
				#= bentuk persupplier
				$stream.="<tr class=rowcontent>";		
					$stream.="<td colspan=3 style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'></td>";
					$stream.="<td style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['subtotal']."</td>";
					foreach($arrbulan as $dtbulan){
						$stream.="<td align=right style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".hidezerodecimal(@$stdtjumlahbulan[$dtkodesupplier][$dtbulan])."</td>";
						@$tstdtjumlahday[$dtkodesupplier]+=$stdtjumlahbulan[$dtkodesupplier][$dtbulan];//total kanan
						@$gtdtjumlahday[$dtbulan]+=$stdtjumlahbulan[$dtkodesupplier][$dtbulan];//buat gt
					}
					$stream.="<td align=right  style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".hidezerodecimal(@$tstdtjumlahday[$dtkodesupplier])."</td>";
				$stream.="</tr>";
					
			}
			
			
			$stream.="<tr class=rowcontent>";		
				$stream.="<td colspan=3 style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'></td>";
				$stream.="<td style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".$_SESSION['lang']['grnd_total']."</td>";
				foreach($arrbulan as $dtbulan){
					$stream.="<td align=right  style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".hidezerodecimal(@$gtdtjumlahday[$dtbulan])."</td>";
					@$tgtdtjumlahday+=$gtdtjumlahday[$dtbulan];
				}
				$stream.="<td align=right  style='border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0px solid #000000;'>".hidezerodecimal(@$tgtdtjumlahday)."</td>";
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
	break;
}



?>