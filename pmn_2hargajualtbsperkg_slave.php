<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$method = checkPostGet('method', '');
$customer = checkPostGet('customer', '');
$unit = checkPostGet('unit', '');
$tipe = checkPostGet('tipe', '');
$tanggal1=tanggalsystemn(checkPostGet('tanggal1',''));
$tanggal2=tanggalsystemn(checkPostGet('tanggal2',''));
$expltanggal1=explode('-',$tanggal1);
$tahun=$expltanggal1[0];

$str="select * from ".$dbname.".organisasi where tipe in ('KEBUN','PT') ";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmorganisasi[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

$str="select induk,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$unit."' ";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$kodept[$bar['kodeorganisasi']]=$bar['induk'];
}

$str = "SELECT * FROM " . $dbname . ".pmn_4customer";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmcustomer[$bar['kodecustomer']]=$bar['namacustomer'];
}
			
if($tanggal1=='--'){
    $tanggal1='';
}
if($tanggal2=='--'){
    $tanggal2='';
}


// echo $tanggal1;
if($tanggal1==''){
	exit("Warning:Tanggal awal Masih Kosong");
}

if($tanggal2==''){
	exit("Warning:Tanggal sampai Masih Kosong");
}

// exit("Error:$method");
#= query
$stream='';
		$judul="Harga TBS ".$nmorganisasi[$kodept[$unit]]."  Tanggal ".tanggalnormal($tanggal1)." s/d ".tanggalnormal($tanggal2)." <br><br> ";
		$stream.=$judul;

switch ($method) {
	case'detail':
	
		
		if($tipe!='html'){
			$border="border=1 cellspacing=0 cellpadding=0   width='100%'";
		}else{
			$border="border=0 cellspacing=1 cellpadding=5";
		}
		if($customer!==''){
			$where=" and kodecustomer='".$customer."'";
		}
		
		$str="select * from ".$dbname.".pmn_hargajualtbs where kodeorg='".$unit."' and tanggal>='".$tanggal1."' and tanggal2<='".$tanggal2."' ".$where." order by tanggal asc";
		// echo $str;exit();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrkdsup[$bar['kodecustomer']]=$bar['kodecustomer'];
			$arrthntnm[$bar['tahuntanam']]=$bar['tahuntanam'];
			$hargadisbun[$bar['kodecustomer']][$bar['tahuntanam']]=$bar['hargadisbun'];
			$hargarealisasi[$bar['kodecustomer']][$bar['tahuntanam']]=$bar['harga'];
		}
		
		
		$str="select max(harga) as harga from ".$dbname.".pmn_hargajualtbs where 
		kodeorg='".$unit."' and tanggal>='".$tanggal1."' and tanggal2<='".$tanggal2."' order by tanggal asc";		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$hargamax=$bar['harga'];
		
		
		// echo"<pre>";
		if(count($arrthntnm)<1){
			exit("Warning:Data Kosong");
		}
		$cthntnm=count($arrthntnm);
		// style=font-size:7px
		array_multisort($arrthntnm,SORT_ASC);
		$stream.="Harga TBS Inti ".$nmorganisasi[$unit]."<br>";
		$stream.="<table class=sortable ".$border.">";
		$stream.="<thead>";
		$stream.="<tr class=rowheader>";
			$stream.="<th align=center rowspan=3>".$_SESSION['lang']['kodecustomer']."</th>";
			$stream.="<th align=center colspan='".($cthntnm*2)."'>".$_SESSION['lang']['tahuntanam']."</th>";
		$stream.="</tr>";
		$stream.="<tr class=rowheader>";
			foreach($arrthntnm as $thntnm){
				$stream.="<th align=center colspan=2>".$thntnm."</th>";
			}
		$stream.="</tr>";
		$stream.="<tr class=rowheader>";
			foreach($arrthntnm as $thntnm){
				$stream.="<th align=center>Disbun</th>";
				$stream.="<th align=center>Realisasi</th>";
			}
		$stream.="</tr>";
		$stream.="</thead>";
		foreach($arrkdsup as $kdsup){
			$stream.="<tr class=rowcontent>";
				
			  $stream.="<td>".$nmcustomer[$kdsup]."</td>";
			
				foreach($arrthntnm as $thntnm){
					$stream.="<td align=center>".number_format($hargadisbun[$kdsup][$thntnm],2)."</td>";
					
					if($hargarealisasi[$kdsup][$thntnm]==$hargamax){
							$stream.="<td align=right><font color=red>".number_format($hargarealisasi[$kdsup][$thntnm],2)."</font></td>";
						}else{
							$stream.="<td align=right>".number_format($hargarealisasi[$kdsup][$thntnm],2)."</td>";
						}
				}
			$stream.="</tr>"; 
		}
			

		$stream.= "</tbody></tfoot></tfoot></table>";
		if($tipe=='excel'){
			$nop_="detail_harga_tbs-".$unit."_".$tanggal1;
			if(strlen($stream)>0)
			{
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				 $handle=fopen("tempExcel/".$nop_.".xls",'w');
				 if(!fwrite($handle,$stream))
				 {
				  echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
				   exit;
				 }
				 else
				 {
				  echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
			}
		} else if ($tipe=='pdf') {
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('A4', 'landscape');
			$dompdf->render();
			$dompdf->stream("Neraca",array("Attachment"=>0));
		} else {
			echo $stream;
		}
	break;
	
	
	case'rekap':
		$border="border=0";
		if($tipe!='html'){
			$border="border=1 cellspacing=0 cellpadding=0   width='100%'";
		}else{
			$border="border=0 cellspacing=1 cellpadding=5";
		}
		
		$stream.="<style>
			@page {
				margin-top: 30px;
				margin-left: 30px;
				margin-right: 30px;
				margin-bottom: 30px;
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
			div.page_break {
				page-break-before: always;
			}
		</style>";
		
		if($customer!==''){
			$where=" and kodecustomer='".$customer."'";
		}
		
		
		#=== inti 
		$no=0;
		$arrthntnm=array();
		$str="select * from ".$dbname.".pmn_hargajualtbs where kodeorg='".$unit."' and tanggal>='".$tanggal1."' and tanggal2<='".$tanggal2."' ".$where." order by tanggal asc";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrthntnm[$bar['kodecustomer']][$bar['tahuntanam']]=$bar['tahuntanam'];
			$hargadisbun[$bar['kodecustomer']][$bar['tahuntanam']]=$bar['hargadisbun'];
			$hargarealisasi[$bar['kodecustomer']][$bar['tahuntanam']]=$bar['harga'];
		}
		
		$str="select max(harga) as harga from ".$dbname.".pmn_hargajualtbs where kodeorg='".$unit."' 
		and tanggal>='".$tanggal1."' and tanggal2<='".$tanggal2."'  ".$where." order by tanggal asc";			
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$hargamax=$bar['harga'];
		
		
		$cthntnm=count($arrthntnm);
		array_multisort($arrthntnm,SORT_ASC);
		
		$stream.="<div style='page-break-after: always;'>";
		$stream.="Harga TBS Inti ".$nmorganisasi[$unit]."<br>";
		$stream.="<table class=sortable ".$border.">";
		$stream.="<thead>";
		$stream.="<tr class=rowheader>";
			$stream.="<th align=center>".$_SESSION['lang']['nourut']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['tahuntanam']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['umur']."</th>";
			$stream.="<th align=center>".$_SESSION['lang']['harga']." Disbun</th>";
			$stream.="<th align=center>".$_SESSION['lang']['harga']." Realisasi</th>";
		$stream.="</tr>";
		$stream.="</thead>";
			foreach($arrthntnm as $customer => $val1){
				$no=0;
				$stream.="<tr class=rowcontent>";
				$stream.="<td colspan=5>".$nmcustomer[$customer]."</td>";
				$stream.="</tr>"; 
				foreach($val1 as $thntnm){
					$no++;
					$stream.="<tr class=rowcontent>";
					$stream.="<td align=center>".$no."</td>";
					$stream.="<td align=center>".$thntnm."</td>";
					$stream.="<td align=center>".($tahun-$thntnm)."</td>";
					$stream.="<td align=right>".number_format($hargadisbun[$customer][$thntnm],2)."</td>";
					if($hargarealisasi[$customer][$thntnm]==$hargamax){
						$stream.="<td align=right><font color=red>".number_format($hargarealisasi[$customer][$thntnm],2)."</font></td>";
					}else{
						$stream.="<td align=right>".number_format($hargarealisasi[$customer][$thntnm],2)."</td>";
					}
					
					$stream.="</tr>"; 
				}
			}
			

		$stream.= "</table>";
		$stream.="</div>";
		
		
		$stream.= "</tbody></tfoot></tfoot></table>";
		$stream.="</div>";
		
		if($tipe=='excel'){
			$nop_="rekap_harga_tbs-".$unit."_".$tanggal1;
			if(strlen($stream)>0)
			{
				if ($handle = opendir('tempExcel')) {
					while (false !== ($file = readdir($handle))) {
						if ($file != "." && $file != ".." && $file != "index.html") {
							@unlink('tempExcel/'.$file);
						}
					}	
				   closedir($handle);
				}
				 $handle=fopen("tempExcel/".$nop_.".xls",'w');
				 if(!fwrite($handle,$stream))
				 {
				  echo "<script language=javascript>
						parent.window.alert('Can't convert to excel format');
						</script>";
				   exit;
				 }
				 else
				 {
				  echo "<script language=javascript>
						window.location='tempExcel/".$nop_.".xls';
						</script>";
				 }
				fclose($handle);
			}
		} else if ($tipe=='pdf') {
			// exit("Error:A");
			$dompdf = new Dompdf();
			$dompdf->loadHtml($stream);
			$dompdf->setPaper('A4', 'potrait');
			$dompdf->render();
			$dompdf->stream("Rekap",array("Attachment"=>0));
		} else {
			echo $stream;
		}
	break;
	
}



?>