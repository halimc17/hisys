<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$method = checkPostGet('method', '');
$unit = checkPostGet('unit', '');
$tipe = checkPostGet('tipe', '');
$jenis = checkPostGet('jenis', '');
$tanggal1=tanggalsystemn(checkPostGet('tanggal1',''));
$tanggal2=tanggalsystemn(checkPostGet('tanggal2',''));

$jam1 = checkPostGet('jam1', '');
$jam2 = checkPostGet('jam2', '');
$menit1 = checkPostGet('menit1', '');
$menit2 = checkPostGet('menit2', '');

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

$str = "SELECT a.supplierid,a.namasupplier FROM " . $dbname . ".log_5supplier a
left join log_5supkelompok b on a.supplierid=b.supplierid
where a.status=1 and b.tipe in ('SUPPLIERTBSEXT','SUPPLIERTBSKUD','SUPPLIERTBSAFI') order by a.namasupplier asc";
$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);
while ($bar = $res->fetch()){
	$nmsupplier[$bar['supplierid']]=$bar['namasupplier'];
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
		// $expltgl1=explode('-',$tanggal1);
		// if($expltgl1[2]=='01'){
			// $periodejudul='I';
		// }else{
			// $periodejudul='II';
		// }
		// if($tipe=='excel'){
			// $stream.="Harga TBS ".$nmorganisasi[$kodept[$unit]]."  Periode ".$periodejudul." <br><br> ";
			$judul="Harga TBS ".$nmorganisasi[$kodept[$unit]]."  Tanggal ".tanggalnormal($tanggal1)." ".$jam1.":".$menit1." s/d ".tanggalnormal($tanggal2)." ".$jam2.":".$menit2."<br> ";
		// }
		
		$stream.=$judul;

switch ($method) {
	case'detail':
	
		$border="border=0";
		if($tipe!='html'){
			$border="border=1";
		}
		
		$tanggal1=$tanggal1." ".$jam1.":".$menit1.":00";
		$tanggal2=$tanggal2." ".$jam2.":".$menit2.":00";
		
		$str="select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$unit."' and tanggal>='".$tanggal1."' and tanggal2<='".$tanggal2."' order by tanggal asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$arrkdsup[$bar['supplierid']]=$bar['supplierid'];
			$arrthntnm[$bar['tahuntanam']]=$bar['tahuntanam'];
			$hargadisbun[$bar['supplierid']][$bar['tahuntanam']]=$bar['hargadisbun'];
			$hargarealisasi[$bar['supplierid']][$bar['tahuntanam']]=$bar['harga'];
		}
		
		
		$str="select max(harga) as harga from ".$dbname.".pmn_hargabelitbs where 
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
		$stream.="<table class=sortable ".$border." cellspacing=1 cellpadding=1 style:width='200%'>";
		$stream.="<thead>";
		$stream.="<tr class=rowheader>";
			$stream.="<td align=center rowspan=3>".$_SESSION['lang']['supplier']."</td>";
			$stream.="<td align=center colspan='".($cthntnm*2)."'>".$_SESSION['lang']['tahuntanam']."</td>";
		$stream.="</tr>";
		$stream.="<tr class=rowheader>";
			foreach($arrthntnm as $thntnm){
				$stream.="<td align=center colspan=2>".$thntnm."</td>";
			}
		$stream.="</tr>";
		$stream.="<tr class=rowheader>";
			foreach($arrthntnm as $thntnm){
				$stream.="<td align=center>Disbun</td>";
				$stream.="<td align=center>Realisasi</td>";
			}
		$stream.="</tr>";
		$stream.="</thead>";
		foreach($arrkdsup as $kdsup){
			$stream.="<tr class=rowcontent>";
				if(strlen($kdsup)>6){
				  $stream.="<td>".$nmsupplier[$kdsup]."</td>";
				}else{
				  $stream.="<td>".$nmorganisasi[$kdsup]."</td>";
				}	
						
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
		$tanggal1=$tanggal1." ".$jam1.":".$menit1.":00";
		$tanggal2=$tanggal2." ".$jam2.":".$menit2.":00";
		
		// echo $tanggal1._.$tanggal2;
		if($tipe!='html'){
			$border="border=1 cellspacing=0 cellpadding=2";
		}else{
			$border="border=0 cellspacing=1 cellpadding=2";
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
		
		switch($jenis){
			case'KUD':
				#==================== kud
				#==================== kud
				$no=0;
				$arrthntnm=array();
				
				$str="select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$unit."' and 
				tanggal>='".$tanggal1."' and tanggal2<='".$tanggal2."' and supplierid not in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN')
				order by tanggal asc";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$arrthntnm[$bar['tahuntanam']]=$bar['tahuntanam'];
					$hargadisbun[$bar['tahuntanam']]=$bar['hargadisbun'];
					$hargarealisasi[$bar['tahuntanam']]=$bar['harga'];
				}
				
				$str="select max(harga) as harga from ".$dbname.".pmn_hargabelitbs where kodeorg='".$unit."' 
				and tanggal>='".$tanggal1."' and tanggal2<='".$tanggal2."' order by tanggal asc";		
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					$hargamax=$bar['harga'];
				
				// echo"<pre>";
				// if(count($arrthntnm)<1){
					// exit("Warning:Data Kosong");
				// }
				$cthntnm=count($arrthntnm);
				array_multisort($arrthntnm,SORT_ASC);
				$stream=$judul;
				$stream.="Harga TBS KUD dan EXT<br>";
				$stream.="<table class=sortable ".$border.">";
				$stream.="<thead>";
				$stream.="<tr class=rowheader>";
					$stream.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
					$stream.="<td align=center>".$_SESSION['lang']['tahuntanam']."</td>";
					$stream.="<td align=center>".$_SESSION['lang']['umur']."</td>";
					$stream.="<td align=center>".$_SESSION['lang']['harga']." Disbun</td>";
					$stream.="<td align=center>".$_SESSION['lang']['harga']." Realisasi</td>";
				$stream.="</tr>";
				$stream.="</thead>";
					foreach($arrthntnm as $thntnm){
						@$no++;
						$stream.="<tr class=rowcontent>";
								$stream.="<td align=center>".$no."</td>";
								$stream.="<td align=center>".$thntnm."</td>";
								$stream.="<td align=center>".@(@$tahun-@$thntnm)."</td>";
								$stream.="<td align=right>".number_format($hargadisbun[$thntnm],2)."</td>";
								if($hargarealisasi[$thntnm]==$hargamax){
									$stream.="<td align=right><font color=red>".number_format($hargarealisasi[$thntnm],2)."</font></td>";
								}else{
									$stream.="<td align=right>".number_format($hargarealisasi[$thntnm],2)."</td>";
								}
								
						$stream.="</tr>"; 
				}
					

				$stream.= "</tbody></tfoot></tfoot></table>";
				$stream.="</div>";
				
			break;
			
			case'INT':
				#=== inti 
				$no=0;
				$arrthntnm=array();
				$str="select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$unit."' and 
				tanggal>='".$tanggal1."' and tanggal2<='".$tanggal2."' and supplierid  in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN') and tipe='SUPPLIERTBSINT'
				order by tanggal asc";
				// echo $str;
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$arrthntnm[$bar['tahuntanam']]=$bar['tahuntanam'];
					$hargadisbun[$bar['tahuntanam']]=$bar['hargadisbun'];
					$hargarealisasi[$bar['tahuntanam']]=$bar['harga'];
				}
				
				$str="select max(harga) as harga from ".$dbname.".pmn_hargabelitbs where kodeorg='".$unit."' 
				and tanggal>='".$tanggal1."' and tanggal2<='".$tanggal2."' order by tanggal asc";		
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					$hargamax=$bar['harga'];
				
				// echo"<pre>";
				// if(count($arrthntnm)<1){
					// exit("Warning:Data Kosong");
				// }
				$cthntnm=count($arrthntnm);
				array_multisort($arrthntnm,SORT_ASC);
				$stream="";
				$stream.="<div style='page-break-after: always;'>";
				// $stream.="<br>";
				// $stream.="<hr>";
				$stream.=$judul;
				$stream.="Harga TBS Inti<br>";
				$stream.="<table class=sortable ".$border.">";
				$stream.="<thead>";
				$stream.="<tr class=rowheader>";
					$stream.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
					$stream.="<td align=center>".$_SESSION['lang']['tahuntanam']."</td>";
					$stream.="<td align=center>".$_SESSION['lang']['umur']."</td>";
					$stream.="<td align=center>".$_SESSION['lang']['harga']." Disbun</td>";
					$stream.="<td align=center>".$_SESSION['lang']['harga']." Realisasi</td>";
				$stream.="</tr>";
				$stream.="</thead>";
					foreach($arrthntnm as $thntnm){
						@$no++;
						$stream.="<tr class=rowcontent>";
								$stream.="<td align=center>".$no."</td>";
								$stream.="<td align=center>".$thntnm."</td>";
								$stream.="<td align=center>".($tahun-$thntnm)."</td>";
								$stream.="<td align=right>".number_format($hargadisbun[$thntnm],2)."</td>";
								if($hargarealisasi[$thntnm]==$hargamax){
									$stream.="<td align=right><font color=red>".number_format($hargarealisasi[$thntnm],2)."</font></td>";
								}else{
									$stream.="<td align=right>".number_format($hargarealisasi[$thntnm],2)."</td>";
								}
								
						$stream.="</tr>"; 
				}
					

				$stream.= "</table>";
				$stream.="</div>";
				
				#=== afiliasi 
				$no=0;
				$arrthntnm=array();
				$str="select * from ".$dbname.".pmn_hargabelitbs where kodeorg='".$unit."' and 
				tanggal>='".$tanggal1."' and tanggal2<='".$tanggal2."' and supplierid  in (select kodeorganisasi from ".$dbname.".organisasi where tipe='KEBUN') and tipe='SUPPLIERTBSAFI'
				order by tanggal asc";
				// echo $str;
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$arrthntnm[$bar['tahuntanam']]=$bar['tahuntanam'];
					$hargadisbun[$bar['tahuntanam']]=$bar['hargadisbun'];
					$hargarealisasi[$bar['tahuntanam']]=$bar['harga'];
				}
				
				$str="select max(harga) as harga from ".$dbname.".pmn_hargabelitbs where kodeorg='".$unit."' 
				and tanggal>='".$tanggal1."' and tanggal2<='".$tanggal2."' order by tanggal asc";		
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$bar=$res->fetch();
					$hargamax=$bar['harga'];
				
				// echo"<pre>";
				// if(count($arrthntnm)<1){
					// exit("Warning:Data Kosong");
				// }
				$cthntnm=count($arrthntnm);
				array_multisort($arrthntnm,SORT_ASC);
				
				$stream.="<div style='page-break-after: always;'>";
				$stream.="<br>";
				$stream.="<hr>";
				$stream.=$judul;
				$stream.="Harga TBS Afiliasi<br>";
				$stream.="<table class=sortable ".$border.">";
				$stream.="<thead>";
				$stream.="<tr class=rowheader>";
					$stream.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
					$stream.="<td align=center>".$_SESSION['lang']['tahuntanam']."</td>";
					$stream.="<td align=center>".$_SESSION['lang']['umur']."</td>";
					$stream.="<td align=center>".$_SESSION['lang']['harga']." Disbun</td>";
					$stream.="<td align=center>".$_SESSION['lang']['harga']." Realisasi</td>";
				$stream.="</tr>";
				$stream.="</thead>";
					foreach($arrthntnm as $thntnm){
						@$no++;
						$stream.="<tr class=rowcontent>";
								$stream.="<td align=center>".$no."</td>";
								$stream.="<td align=center>".$thntnm."</td>";
								$stream.="<td align=center>".($tahun-$thntnm)."</td>";
								$stream.="<td align=right>".number_format($hargadisbun[$thntnm],2)."</td>";
								if($hargarealisasi[$thntnm]==$hargamax){
									$stream.="<td align=right><font color=red>".number_format($hargarealisasi[$thntnm],2)."</font></td>";
								}else{
									$stream.="<td align=right>".number_format($hargarealisasi[$thntnm],2)."</td>";
								}
								
						$stream.="</tr>"; 
				}
					

				$stream.= "</table>";
				$stream.="</div>";
				
			break;
		}
		
		
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