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

if($param['periode']==''){
	exit("Warning:Komoditi tidak boleh kosong");
}

if($param['tipe']=='html'){
	$border='border=0';
}else{
	$border='border=1';
}

$arrtanggal=rangeTanggalarr(tanggalsystemn($param['tanggal1']),tanggalsystemn($param['tanggal2']));
$tanggalkemarin=tglkemarin(tanggalsystemn($param['tanggal1']));

$str="select * from ".$dbname.".organisasi where tipe in ('PABRIK','BULKING')";
$res=fetchdata($str);
foreach($res as $bar){
	$arrkodept[$bar['induk']]=$bar['induk'];
	if($bar['tipe']=='PABRIK'){
		$arrkodepabrik[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	}
	if($bar['tipe']=='BULKING'){
		$arrkodebulking[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	}
}

$cspanpt=count($arrkodept);
$cspantotal=$cspanpt+1;

$tahun=substr($param['periode'],0,4);
$arrperiode=month_inbetween($tahun.'-01',$tahun.'-12');

$tdtkirim=$tdtterima=$dtkirim=$dtterima=$dtpenyerahanbulking=$dtpenyerahanpabrik=$tdtpenyerahanbulking=$tdtpenyerahanpabrik=$dtselisih=$tdtselisih=array();


#= ambil data ba koreksi stok
$str="select sum(jumlah) as jumlah,kodept,substr(tanggal,1,7) as periode,unit,tipe from ".$dbname.".pabrik_bakoreksistok_vw where kodebarang='40000002' and tanggal like '".substr($param['periode'],0,4)."%'  group by kodept,periode";
// echo $str;
$res=fetchdata($str);
foreach($res as $bar){
	// #= kirim dari pks
	// if(in_array($bar['unit'],$arrkodepabrik)){
		// $dtpenyerahanpabrik[$bar['periode']][$bar['kodept']]=$bar['jumlah'];
	// }
	#= kirim dari bulking
	if(in_array($bar['unit'],$arrkodebulking)){
		if($bar['tipe']=='IN'){
			$dtkoreksistok[$bar['periode']][$bar['kodept']]=$bar['jumlah'];
		}else{
			$dtkoreksistok[$bar['periode']][$bar['kodept']]=($bar['jumlah']*-1);
		}
	}
}


#data pabrik_bamutasi
$str="select sum(jumlah) as jumlah,kodept,tipe,substr(tanggal,1,7) as periode from ".$dbname.".pabrik_bamutasi where unitreferensi!='' and kodebarang='40000001' and tanggal like '".substr($param['periode'],0,4)."%' group by kodept,tipe,periode";
$res=fetchdata($str);
foreach($res as $bar){
	if($bar['tipe']=='OUT'){
		$dtkirim[$bar['periode']][$bar['kodept']]=$bar['jumlah'];
	}
	if($bar['tipe']=='IN'){
		// $dtterima[$bar['periode']][$bar['kodept']]=$bar['jumlah'];
		$dtterima[$bar['periode']][$bar['kodept']]=$bar['jumlah']+$dtkoreksistok[$bar['periode']][$bar['kodept']];
	}
	$dtselisih[$bar['periode']][$bar['kodept']]=$dtterima[$bar['periode']][$bar['kodept']]-$dtkirim[$bar['periode']][$bar['kodept']];
} 

#= data penyerahan dari pmn_bapengiriman

$str="select sum(jumlah) as jumlah,kodept,tipe,substr(tanggal,1,7) as periode,unit from ".$dbname.".pmn_bapengiriman_vw where kodebarang='40000001' and tanggal like '".substr($param['periode'],0,4)."%' group by kodept,tipe,periode";
// echo $str;
$res=fetchdata($str);
foreach($res as $bar){
	#= kirim dari pks
	if(in_array($bar['unit'],$arrkodepabrik)){
		$dtpenyerahanpabrik[$bar['periode']][$bar['kodept']]=$bar['jumlah'];
	}
	#= kirim dari bulking
	if(in_array($bar['unit'],$arrkodebulking)){
		$dtpenyerahanbulking[$bar['periode']][$bar['kodept']]=$bar['jumlah'];
	}
}



$stream1.="";
$stream1.="<b>Table 1.1 CPO Throughput - Summary ".$tahun."<br></b>";
$stream1.="<b>Periode ".$param['periode']."</b><br>";
$stream1.="<table class=sortable cellspacing=1 ".$border." width=100%>";
$stream1.="<thead>";
	$stream1.="<tr class=rowheader>";		
		$stream1.="<td rowspan=3 align=center>".$_SESSION['lang']['bulan']."</td>";
		$stream1.="<td colspan=".($cspantotal)." align=center>Transfer dari / Qty yg bongkar ke tanki</td>";
		$stream1.="<td colspan=".($cspantotal)." align=center>Terima di Bulking</td>";
		$stream1.="<td rowspan=3 align=center>Selisih Transfer / Sounding</td>";
		$stream1.="<td colspan=".($cspantotal*2)." align=center>Selisih Penerimaan</td>";
		$stream1.="<td colspan=".($cspantotal)." align=center>".$_SESSION['lang']['penyerahan']." di Bulking</td>";
		$stream1.="<td colspan=".($cspantotal)." align=center>".$_SESSION['lang']['penyerahan']." ".$_SESSION['lang']['pabrik']."</td>";
	$stream1.="</tr>";
	
	$stream1.="<tr class=rowheader>";		
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=center>".$dtkodept."</td>";
		}
		$stream1.="<td align=center>".$_SESSION['lang']['total']."</td>";
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=center>".$dtkodept."</td>";
		}
		$stream1.="<td align=center>".$_SESSION['lang']['total']."</td>";
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=center colspan=2>".$dtkodept."</td>";
		}
		$stream1.="<td align=center colspan=2>".$_SESSION['lang']['total']."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=center>".$dtkodept."</td>";
		}
		$stream1.="<td align=center>".$_SESSION['lang']['total']."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=center>".$dtkodept."</td>";
		}
		$stream1.="<td align=center>".$_SESSION['lang']['total']."</td>";
		
	$stream1.="</tr>";
	$stream1.="<tr class=rowheader>";		
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		}
		$stream1.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		}
		$stream1.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=center>".$_SESSION['lang']['kg']."</td>";
			$stream1.="<td align=center>".$_SESSION['lang']['persen']."</td>";
		}
		$stream1.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		$stream1.="<td align=center>".$_SESSION['lang']['persen']."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		}
		$stream1.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		}
		$stream1.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		
	$stream1.="</tr>";
$stream1.="</thead>";
$stream1.="<tbody>";
	
#= data

foreach($arrperiode as $dtperiode){
	$stream1.="<tr class=rowcontent>";	
		$stream1.="<td align=center>".$dtperiode."</td>";
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=right>".number_format($dtkirim[$dtperiode][$dtkodept])."</td>";
			$tdtkirim[$dtperiode]+=$dtkirim[$dtperiode][$dtkodept];
		}
		$stream1.="<td align=right>".number_format($tdtkirim[$dtperiode])."</td>";
		
		#= terima dibulking
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=right>".number_format($dtterima[$dtperiode][$dtkodept])."</td>";
			$tdtterima[$dtperiode]+=$dtterima[$dtperiode][$dtkodept];
		}
		$stream1.="<td align=right>".number_format($tdtterima[$dtperiode])."</td>";
		
		$stream1.="<td align=right></td>";
		foreach($arrkodept as $dtkodept){
			$dtpersentaseselisih[$dtperiode][$dtkodept]=$dtselisih[$dtperiode][$dtkodept]/$dtkirim[$dtperiode][$dtkodept]*100;
			$stream1.="<td align=right>".number_format($dtselisih[$dtperiode][$dtkodept])."</td>";
			$stream1.="<td align=right>".number_format(fixnan($dtpersentaseselisih[$dtperiode][$dtkodept]),2)."</td>";
			$tdtselisih[$dtperiode]+=$dtselisih[$dtperiode][$dtkodept];
		}
		$tdtpersentaseselisih[$dtperiode]=$tdtselisih[$dtperiode]/$tdtkirim[$dtperiode]*100;
		$stream1.="<td align=right>".number_format($tdtselisih[$dtperiode])."</td>";
		$stream1.="<td align=right>".number_format(fixnan($tdtpersentaseselisih[$dtperiode]),2)."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=right>".number_format($dtpenyerahanbulking[$dtperiode][$dtkodept])."</td>";
			$tdtpenyerahanbulking[$dtperiode]+=$dtpenyerahanbulking[$dtperiode][$dtkodept];
		}
		$stream1.="<td align=right>".number_format($tdtpenyerahanbulking[$dtperiode])."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream1.="<td align=right>".number_format($dtpenyerahanpabrik[$dtperiode][$dtkodept])."</td>";
			$tdtpenyerahanpabrik[$dtperiode]+=$dtpenyerahanpabrik[$dtperiode][$dtkodept];
		}
		$stream1.="<td align=right>".number_format($tdtpenyerahanpabrik[$dtperiode])."</td>";
		
		
	$stream1.="</tr>";
}
	
	
	
$stream1.="</tbody>";
$stream1.="</table>";


#======== tutup 1







#======== buka  2



$tdtkirim=$tdtterima=$dtkirim=$dtterima=$dtpenyerahanbulking=$dtpenyerahanpabrik=$tdtpenyerahanbulking=$tdtpenyerahanpabrik=$dtselisih=$tdtselisih=array();


#= ambil data ba koreksi stok
$str="select sum(jumlah) as jumlah,kodept,substr(tanggal,1,7) as periode,unit,tipe from ".$dbname.".pabrik_bakoreksistok_vw where kodebarang='40000002' and tanggal like '".substr($param['periode'],0,4)."%'  group by kodept,periode";
// echo $str;
$res=fetchdata($str);
foreach($res as $bar){
	// #= kirim dari pks
	// if(in_array($bar['unit'],$arrkodepabrik)){
		// $dtpenyerahanpabrik[$bar['periode']][$bar['kodept']]=$bar['jumlah'];
	// }
	#= kirim dari bulking
	if(in_array($bar['unit'],$arrkodebulking)){
		if($bar['tipe']=='IN'){
			$dtkoreksistok[$bar['periode']][$bar['kodept']]=$bar['jumlah'];
		}else{
			$dtkoreksistok[$bar['periode']][$bar['kodept']]=($bar['jumlah']*-1);
		}
	}
}


// echo"<pre>";
// print_r($dtkoreksistok);
// echo"</pre>";

#data pabrik_bamutasi
$str="select sum(jumlah) as jumlah,kodept,tipe,substr(tanggal,1,7) as periode from ".$dbname.".pabrik_bamutasi where unitreferensi!='' and kodebarang='40000002' and tanggal like '".substr($param['periode'],0,4)."%' group by kodept,tipe,periode";
// echo $str;
$res=fetchdata($str);
foreach($res as $bar){
	if($bar['tipe']=='OUT'){
		$dtkirim[$bar['periode']][$bar['kodept']]=$bar['jumlah'];
	}
	if($bar['tipe']=='IN'){
		$dtterima[$bar['periode']][$bar['kodept']]=$bar['jumlah']+$dtkoreksistok[$bar['periode']][$bar['kodept']];
	}
	$dtselisih[$bar['periode']][$bar['kodept']]=$dtterima[$bar['periode']][$bar['kodept']]-$dtkirim[$bar['periode']][$bar['kodept']];
} 

#= data penyerahan dari pmn_bapengiriman

$str="select sum(jumlah) as jumlah,kodept,tipe,substr(tanggal,1,7) as periode,unit from ".$dbname.".pmn_bapengiriman_vw where kodebarang='40000002' and tanggal like '".substr($param['periode'],0,4)."%' group by kodept,tipe,periode";
// echo $str;
$res=fetchdata($str);
foreach($res as $bar){
	#= kirim dari pks
	if(in_array($bar['unit'],$arrkodepabrik)){
		$dtpenyerahanpabrik[$bar['periode']][$bar['kodept']]=$bar['jumlah'];
	}
	#= kirim dari bulking
	if(in_array($bar['unit'],$arrkodebulking)){
		$dtpenyerahanbulking[$bar['periode']][$bar['kodept']]=$bar['jumlah'];
	}
}



$stream2.="";
$stream2.="<b>Table 1.2 PK Throughput - Summary ".$tahun."<br></b>";
$stream2.="<b>Periode ".$param['periode']."</b><br>";

$stream2.="<table class=sortable cellspacing=1 ".$border." width=100%>";
$stream2.="<thead>";
	$stream2.="<tr class=rowheader>";		
		$stream2.="<td rowspan=3 align=center>".$_SESSION['lang']['bulan']."</td>";
		$stream2.="<td colspan=".($cspantotal)." align=center>Transfer dari / Qty yg bongkar ke tanki</td>";
		$stream2.="<td colspan=".($cspantotal)." align=center>Terima di Bulking</td>";
		$stream2.="<td rowspan=3 align=center>Selisih Transfer / Sounding</td>";
		$stream2.="<td colspan=".($cspantotal*2)." align=center>Selisih Penerimaan</td>";
		$stream2.="<td colspan=".($cspantotal)." align=center>".$_SESSION['lang']['penyerahan']." di Bulking</td>";
		$stream2.="<td colspan=".($cspantotal)." align=center>".$_SESSION['lang']['penyerahan']." ".$_SESSION['lang']['pabrik']."</td>";
	$stream2.="</tr>";
	
	$stream2.="<tr class=rowheader>";		
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=center>".$dtkodept."</td>";
		}
		$stream2.="<td align=center>".$_SESSION['lang']['total']."</td>";
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=center>".$dtkodept."</td>";
		}
		$stream2.="<td align=center>".$_SESSION['lang']['total']."</td>";
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=center colspan=2>".$dtkodept."</td>";
		}
		$stream2.="<td align=center colspan=2>".$_SESSION['lang']['total']."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=center>".$dtkodept."</td>";
		}
		$stream2.="<td align=center>".$_SESSION['lang']['total']."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=center>".$dtkodept."</td>";
		}
		$stream2.="<td align=center>".$_SESSION['lang']['total']."</td>";
		
	$stream2.="</tr>";
	$stream2.="<tr class=rowheader>";		
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		}
		$stream2.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		}
		$stream2.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=center>".$_SESSION['lang']['kg']."</td>";
			$stream2.="<td align=center>".$_SESSION['lang']['persen']."</td>";
		}
		$stream2.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		$stream2.="<td align=center>".$_SESSION['lang']['persen']."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		}
		$stream2.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		}
		$stream2.="<td align=center>".$_SESSION['lang']['kg']."</td>";
		
	$stream2.="</tr>";
$stream2.="</thead>";
$stream2.="<tbody>";
	
#= data

foreach($arrperiode as $dtperiode){
	$stream2.="<tr class=rowcontent>";	
		$stream2.="<td align=center>".$dtperiode."</td>";
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=right>".number_format($dtkirim[$dtperiode][$dtkodept])."</td>";
			$tdtkirim[$dtperiode]+=$dtkirim[$dtperiode][$dtkodept];
		}
		$stream2.="<td align=right>".number_format($tdtkirim[$dtperiode])."</td>";
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=right>".number_format($dtterima[$dtperiode][$dtkodept])."</td>";
			$tdtterima[$dtperiode]+=$dtterima[$dtperiode][$dtkodept];
		}
		$stream2.="<td align=right>".number_format($tdtterima[$dtperiode])."</td>";
		$stream2.="<td align=right></td>";
		foreach($arrkodept as $dtkodept){
			$dtpersentaseselisih[$dtperiode][$dtkodept]=$dtselisih[$dtperiode][$dtkodept]/$dtkirim[$dtperiode][$dtkodept]*100;
			$stream2.="<td align=right>".number_format($dtselisih[$dtperiode][$dtkodept])."</td>";
			$stream2.="<td align=right>".number_format(fixnan($dtpersentaseselisih[$dtperiode][$dtkodept]),2)."</td>";
			$tdtselisih[$dtperiode]+=$dtselisih[$dtperiode][$dtkodept];
		}
		$tdtpersentaseselisih[$dtperiode]=$tdtselisih[$dtperiode]/$tdtkirim[$dtperiode]*100;
		$stream2.="<td align=right>".number_format($tdtselisih[$dtperiode])."</td>";
		$stream2.="<td align=right>".number_format(fixnan($tdtpersentaseselisih[$dtperiode]),2)."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=right>".number_format($dtpenyerahanbulking[$dtperiode][$dtkodept])."</td>";
			$tdtpenyerahanbulking[$dtperiode]+=$dtpenyerahanbulking[$dtperiode][$dtkodept];
		}
		$stream2.="<td align=right>".number_format($tdtpenyerahanbulking[$dtperiode])."</td>";
		
		foreach($arrkodept as $dtkodept){
			$stream2.="<td align=right>".number_format($dtpenyerahanpabrik[$dtperiode][$dtkodept])."</td>";
			$tdtpenyerahanpabrik[$dtperiode]+=$dtpenyerahanpabrik[$dtperiode][$dtkodept];
		}
		$stream2.="<td align=right>".number_format($tdtpenyerahanpabrik[$dtperiode])."</td>";
		
		
	$stream2.="</tr>";
}
	
$stream2.="</tbody>";
$stream2.="</table>";

#======== tutup 2


switch ($method) {
	
	case'preview':
		switch($param['tipe']){
			case'html':
				echo $stream1."<br>".$stream2;
			break;
			case'excel':
				$nop = "bulking_monthlyreport_".$param['periode'].".xls";
				$xls = new HtmlExcel();
				$xls->setCss($css);
				$xls->addSheet("1.1", $stream1);
				$xls->addSheet("1.2", $stream2);
				$xls->headers($nop);
				echo $xls->buildFile();
			break;
			// case'pdf':
				// $dompdf = new Dompdf();
				// $dompdf->loadHtml($stream);
				// $dompdf->setPaper('A4', 'landscape');
				// $dompdf->render();
				// $dompdf->stream("Stok",array("Attachment"=>0));
			// break;
		}
	break;
	
	
	
}



?>