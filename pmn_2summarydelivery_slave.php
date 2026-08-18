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

#= organisasi
$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
$res=fetchdata($str);
foreach($res as $bar){
	$namaorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
} 

#= master barang
$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
$res=fetchdata($str);
foreach($res as $bar){
	$inisialbarang[$bar['kodebarang']]=$bar['inisial'];
}


#= master barang
$str="select * from ".$dbname.".pmn_4customer";
$res=fetchdata($str);
foreach($res as $bar){
	$namacustomer[$bar['kodecustomer']]=$bar['namacustomer'];
}
		
switch ($method) {
	
	case'detail':
		// print_r($param);
		if($param['tipe']=='html') {
			$stylekolom='border=0 cellspacing=1';
		}else{
			$stylekolom='border=1 cellspacing=1';
		}
		
		
		$stream.="<table class=sortable ".$stylekolom." width=100%>";
		$stream.="<thead>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['bast']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['NoKontrak']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['customer']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['tanggal']." BL</th>";
				$stream.="<th align=center>".$_SESSION['lang']['kuantitas']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['rpkg']."</th>";
				$stream.="<th align=center>Gross Sales</th>";
				$stream.="<th align=center>Claim Mutu</th>";
				$stream.="<th align=center>Nett Sales</th>";
				$stream.="<th align=center>Claim Demurrage</th>";
			$stream.="</tr>";
		$stream.="</thead>";	
		
		$no=0;
		$str="select *,rpclaimffa+rpclaimmoisture+rpclaimdirt+rpclaimdobi+rpclaimbroken+rpclaimmdani+rpclaimimpurities as rpclaimmutu,
		rpbast-(rpclaimffa+rpclaimmoisture+rpclaimdirt+rpclaimdobi+rpclaimbroken+rpclaimmdani+rpclaimimpurities) as rpbastnett from ".$dbname.".pmn_bast where 1=1 and kodept='".$param['kodept']."' and tanggalbl like '".$param['periode']."%' and kodebarang='".$param['kodebarang']."' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			
			$no++;
			$stream.="<tr class=rowcontent>";		
				$stream.="<td align=center>".$no."</td>";
				$stream.="<td align=left>".$bar['notransaksi']."</td>";
				$stream.="<td align=left>".$bar['nokontrak']."</td>";
				$stream.="<td align=left>".$namacustomer[$bar['kodecustomer']]."</td>";
				$stream.="<td align=left>".tanggalnormal($bar['tanggalbl'])."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['jumlah'])."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['rpkg'])."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['rpbast'])."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['rpclaimmutu'])."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['rpbastnett'])."</td>";
				$stream.="<td align=right>".hidezerodecimal($bar['rpclaimlain'])."</td>";
			$stream.="</tr>";		
			@$tjumlah+=$bar['jumlah'];
			@$trpbast+=$bar['rpbast'];
			@$trpbastnett+=$bar['rpbastnett'];
			@$trpclaimmutu+=$bar['rpclaimmutu'];
			@$trpclaimlain+=$bar['rpclaimlain'];
		} 
		$stream.="<tr class=rowcontent>";		
			$stream.="<td align=center colspan=5>".$_SESSION['lang']['total']."</td>";
			$stream.="<td align=right>".hidezerodecimal($tjumlah)."</td>";
			$stream.="<td align=right>".hidezerodecimal(fixnan($trpbast/$tjumlah))."</td>";
			$stream.="<td align=right>".hidezerodecimal($trpbast)."</td>";
			$stream.="<td align=right>".hidezerodecimal($trpclaimmutu)."</td>";
			$stream.="<td align=right>".hidezerodecimal($trpbastnett)."</td>";
			$stream.="<td align=right>".hidezerodecimal($trpclaimlain)."</td>";
		$stream.="</tr>";	
		$stream.="</table>";
		if($param['tipe']=='html'){
			$stream.="<button class=mybutton onclick=detail('".$param['kodept']."','".$param['kodebarang']."','".$param['periode']."','excel')>".$_SESSION['lang']['excel']."</button>";
		}
		
		switch($param['tipe']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "Detail_Delivery_".$param['kodept']."_".$param['kodebarang']."_".$param['periode'].".xls";
				$xls = new HtmlExcel();
				$xls->setCss($css);
				$xls->addSheet("data", $stream);
				$xls->headers($nop);
				echo $xls->buildFile();
			break;
		}
	break;
	
	
	
	case'preview':
	
		$where='';
		
		if($param['kodebarang']==''){
			exit("Warning:Komoditi/Kodebarang tidak boleh kosong");
		}
	
		if($param['tahun']==''){
			exit("Warning:Tahun tidak boleh kosong");
		}

		#= tbs
		$str="select sum(jumlah) as kgbast,sum(rpbast) as rpbast,substr(tanggalbl,6,2) as periode,kodept,sum(rpclaimffa+rpclaimmoisture+rpclaimdirt+rpclaimdobi+rpclaimbroken+rpclaimmdani+rpclaimimpurities) as rpclaimmutu,sum(rpclaimlain) as rpclaimlain,sum(rpbast)-sum(rpclaimffa+rpclaimmoisture+rpclaimdirt+rpclaimdobi+rpclaimbroken+rpclaimmdani+rpclaimimpurities) as rpbastnett from ".$dbname.".pmn_bast where 1=1 and tanggalbl like '".$param['tahun']."%' and kodebarang='".$param['kodebarang']."' group by periode,kodept";
		$res=fetchdata($str);
		foreach($res as $bar){
			$arrperiode[$bar['periode']]=$bar['periode'];
			$arrkodept[$bar['kodept']]=$bar['kodept'];
			$kgbast[$bar['periode']][$bar['kodept']]=$bar['kgbast'];
			$rpbast[$bar['periode']][$bar['kodept']]=$bar['rpbast'];
			$rpclaimmutu[$bar['periode']][$bar['kodept']]=$bar['rpclaimmutu'];
			$rpclaimlain[$bar['periode']][$bar['kodept']]=$bar['rpclaimlain'];
			$rpbastnett[$bar['periode']][$bar['kodept']]=$bar['rpbastnett'];
		} 
		
		$countkodept=count($arrkodept);
		if($countkodept<1){
			echo"Warningsistem:Data Kosong";
			exit();
		}
		
		if($param['tipe']=='html') {
			$stylekolom='border=0 cellspacing=1';
		}else if($param['tipe']=='pdf') {
			
			
			// $stream.="<style>
				// @page {
					// margin-top: 5px;
					// margin-left: 5px;
					// margin-right: 5px;
					// margin-bottom: 5px;
				// }
				// body {
					// font-family: Tahoma, Verdana, Segoe, sans-serif;
				// }
				
				// footer {
					// position: fixed; 
					// bottom: -20px; 
					// left: 0px; 
					// right: 0px;
					// height: 50px; 
				// }
				
			// </style>";

				
			// $stylekolom="border=1 cellspacing=0 style='font-size:6px'";
			$stylekolom="border=1 cellspacing=0 ";
			$stream.="<table class=sortable  width=100% border=0'>";
				$stream.="<tr class=rowheader>";		
					$stream.="<th align=left colspan=6><b>".$_SESSION['org']['holding']."<b></th>";
				$stream.="</tr>";
				$stream.="<tr class=rowheader>";		
					$stream.="<th align=left colspan=6><b>ACTUAL ".$inisialbarang[$param['kodebarang']]." DELIVERY ".$param['tahun']." YEAR (STANDARD + OFFSPEC)<b></th>";
				$stream.="</tr>";// KSP CPO ACTUAL DELIVERY 2021 YEAR
			$stream.="</table>";
			$stream.="<br>";
		} else if($param['tipe']=='excel'){
			$stylekolom="border=1 cellspacing=1";
			$stream.="<table class=sortable  width=100% border=0>";
				$stream.="<tr class=rowheader>";		
					$stream.="<th align=left colspan=6><b>".$_SESSION['org']['holding']."<b></th>";
				$stream.="</tr>";
				$stream.="<tr class=rowheader>";		
					$stream.="<th align=left colspan=6><b>ACTUAL ".$inisialbarang[$param['kodebarang']]." DELIVERY ".$param['tahun']." YEAR (STANDARD + OFFSPEC)<b></th>";
				$stream.="</tr>";
			$stream.="</table>";
			$stream.="<br>";
		}
		
		$stream.="<table class=sortable ".$stylekolom." width=100% >";
		$stream.="<thead>";
			$stream.="<tr class=rowheader>";		
				$stream.="<th align=center rowspan=3>".$_SESSION['lang']['bulan']."</th>";
				foreach($arrkodept as $dtkodept){
					$stream.="<th align=center colspan='7'>".$namaorganisasi[$dtkodept]."</th>";
				}
				$stream.="<th align=center colspan=7>".$_SESSION['org']['holding']."</th>";
				// $stream.="<th align=center colspan='".(3*($countkodept+1))."'>".$param['tahun']."</th>";
			$stream.="</tr>";
			
			
			$stream.="<tr class=rowheader>";		
				foreach($arrkodept as $dtkodept){
					$stream.="<th align=center colspan='3'>Gross Sales</th>";
					$stream.="<th align=center rowspan='2'>Claim Mutu</th>";
					$stream.="<th align=center colspan='2'>Nett Sales</th>";
					$stream.="<th align=center rowspan='2'>Claim Demurrage</th>";
				}
				$stream.="<th align=center colspan='3'>Gross Sales</th>";
				$stream.="<th align=center rowspan='2'>Claim Mutu</th>";
				$stream.="<th align=center colspan='2'>Nett Sales</th>";
				$stream.="<th align=center rowspan='2'>Claim Demurrage</th>";
			$stream.="</tr>";
			
			$stream.="<tr class=rowheader>";		
			for($i=1;$i<=($countkodept+1);$i++){
				$stream.="<th align=center>".$_SESSION['lang']['kuantitas']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['rpkg']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['rp']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['rpkg']."</th>";
				$stream.="<th align=center>".$_SESSION['lang']['rp']."</th>";
			}
			$stream.="</tr>";
			
			$stream.="</thead>";
			$stream.="<tbody>";
			$no=0;
			foreach($arrperiode as $dtperiode){
				$no++;
				$stream.="<tr class=rowcontent>";		
					$stream.="<td align=left>".numToMonth($dtperiode,'I','long')."</td>";
					foreach($arrkodept as $dtkodept){
						
						$detaildata="style='cursor:pointer'  title='view detail ".$dtkodept." ".$param['tahun']."-".$dtperiode."' onclick=detail('".$dtkodept."','".$param['kodebarang']."','".$param['tahun']."-".$dtperiode."','html')";
						
						$stream.="<td align=right ".$detaildata.">".hidezerodecimal($kgbast[$dtperiode][$dtkodept])."</td>";
						$stream.="<td align=right ".$detaildata.">".hidezerodecimal(fixnan($rpbast[$dtperiode][$dtkodept]/$kgbast[$dtperiode][$dtkodept]))."</td>";
						$stream.="<td align=right ".$detaildata.">".hidezerodecimal($rpbast[$dtperiode][$dtkodept])."</td>";
						
						$stream.="<td align=right ".$detaildata.">".hidezerodecimal($rpclaimmutu[$dtperiode][$dtkodept])."</td>";
						$stream.="<td align=right ".$detaildata.">".hidezerodecimal(fixnan($rpbastnett[$dtperiode][$dtkodept]/$kgbast[$dtperiode][$dtkodept]))."</td>";
						$stream.="<td align=right ".$detaildata.">".hidezerodecimal($rpbastnett[$dtperiode][$dtkodept])."</td>";
						$stream.="<td align=right ".$detaildata.">".hidezerodecimal($rpclaimlain[$dtperiode][$dtkodept])."</td>";
						
						
						@$tkgbast[$dtperiode]+=$kgbast[$dtperiode][$dtkodept];
						@$trpbast[$dtperiode]+=$rpbast[$dtperiode][$dtkodept];
							@$trpbastnett[$dtperiode]+=$rpbastnett[$dtperiode][$dtkodept];
							@$trpclaimmutu[$dtperiode]+=$rpclaimmutu[$dtperiode][$dtkodept];
							@$trpclaimlain[$dtperiode]+=$rpclaimlain[$dtperiode][$dtkodept];
							
						@$stkgbast[$dtkodept]+=$kgbast[$dtperiode][$dtkodept];
						@$strpbast[$dtkodept]+=$rpbast[$dtperiode][$dtkodept];
						
						@$strpbastnett[$dtkodept]+=$rpbastnett[$dtperiode][$dtkodept];
						@$strpclaimmutu[$dtkodept]+=$rpclaimmutu[$dtperiode][$dtkodept];
						@$strpclaimlain[$dtkodept]+=$rpclaimlain[$dtperiode][$dtkodept];
						
					}
					$stream.="<td align=right>".hidezerodecimal($tkgbast[$dtperiode])."</td>";
					$stream.="<td align=right>".hidezerodecimal(fixnan($trpbast[$dtperiode]/$tkgbast[$dtperiode]))."</td>";
					$stream.="<td align=right>".hidezerodecimal($trpbast[$dtperiode])."</td>";
					$stream.="<td align=right>".hidezerodecimal($trpclaimmutu[$dtperiode])."</td>";
					
						$stream.="<td align=right>".hidezerodecimal(fixnan($trpbastnett[$dtperiode]/$tkgbast[$dtperiode]))."</td>";
						$stream.="<td align=right>".hidezerodecimal($trpbastnett[$dtperiode])."</td>";
						$stream.="<td align=right>".hidezerodecimal($trpclaimlain[$dtperiode])."</td>";
					
				$stream.="</tr>";
			}
			$stream.="<tr class=rowcontent>";	
				$stream.="<td colspan='".((7*($countkodept+1))+1)."'></td>";
			$stream.="</tr>";
			$stream.="<tr class=rowcontent>";		
				$stream.="<td></td>";
				foreach($arrkodept as $dtkodept){
					$stream.="<td align=right>".hidezerodecimal($stkgbast[$dtkodept])."</td>";
					$stream.="<td align=right>".hidezerodecimal($strpbast[$dtkodept]/$stkgbast[$dtkodept])."</td>";
					$stream.="<td align=right>".hidezerodecimal($strpbast[$dtkodept])."</td>";
					
					$stream.="<td align=right>".hidezerodecimal($strpclaimmutu[$dtkodept])."</td>";
					$stream.="<td align=right>".hidezerodecimal(fixnan($strpbastnett[$dtkodept]/$stkgbast[$dtkodept]))."</td>";
					$stream.="<td align=right>".hidezerodecimal($strpbastnett[$dtkodept])."</td>";
					$stream.="<td align=right>".hidezerodecimal($strpclaimlain[$dtkodept])."</td>";
					
					@$tstkgbast+=$stkgbast[$dtkodept];
					@$tstrpbast+=$strpbast[$dtkodept];
					
					@$tstrpclaimmutu+=$strpclaimmutu[$dtkodept];
					@$tstrpbastnett+=$strpbastnett[$dtkodept];
					@$tstrpclaimlain+=$strpclaimlain[$dtkodept];
				}
				$stream.="<td align=right>".hidezerodecimal($tstkgbast)."</td>";
				$stream.="<td align=right>".hidezerodecimal($tstrpbast/$tstkgbast)."</td>";
				$stream.="<td align=right>".hidezerodecimal($tstrpbast)."</td>";
				
				$stream.="<td align=right>".hidezerodecimal($tstrpclaimmutu)."</td>";
				$stream.="<td align=right>".hidezerodecimal($tstrpbastnett/$tstkgbast)."</td>";
				$stream.="<td align=right>".hidezerodecimal($tstrpbastnett)."</td>";
				$stream.="<td align=right>".hidezerodecimal($tstrpclaimlain)."</td>";
				
			$stream.="</tr>";
		$stream.="</tbody>";
		$stream.="</table>";

	
		switch($param['tipe']){
			case'html':
				echo $stream;
			break;
			case'excel':
				$nop = "Rekap_Delivery_".$param['kodebarang']."_".$param['tahun'].".xls";
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
			// $dompdf->setPaper('A4', 'landscape');
				$customPaper = array(0,0,800,2000);
				$dompdf->set_paper($customPaper,'landscape');
			$dompdf->render();
			$dompdf->stream("Rekap_Delivery_".$param['kodebarang']."_".$param['tahun'],array("Attachment"=>0));
			break;
		}
	break;
	
}



?>