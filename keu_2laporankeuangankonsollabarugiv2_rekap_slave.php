<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;

$method = checkPostGet('method','');
$param = $_POST;

$newperiode = tglakhirbulan(substr($param['periode'],5,2))." ".numToMonth(substr($param['periode'],5,2),"I","long")." ".substr($param['periode'],0,4);

$cparam=count($param);
if($cparam==0){
	$param=$_GET;
}

if($param['periode']=='' || $param['kodept']==''){
	exit("Warning:Periode / PT masih kosong");
}

$dgt=$param['digit'];

#= ambil jumlah
#= ambil jumlah
$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$param['kodept']."' or induk='".$param['kodept']."'";
$res=fetchdata($str);
foreach($res as $bar){
	$namaorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

if($param['kodeunit']!=''){
	$whereunit=" and kodeorganisasi='".$param['kodeunit']."'";
	$judulunit="<br>".$namaorg[$param['kodeunit']]."";
}


#= daftar unit dalam 1 pt
@$where=" and substr(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."'  ".$whereunit.")";


$kodelaporan='LABARUGINEW';

#= untuk judul laporan
$str="select * from ".$dbname.".keu_5mesinlaporanht where namalaporan='".$kodelaporan."'";
$res=fetchdata($str);
foreach($res as $bar){
	$judullaporan=$bar['ket1'];
}
/*
data tahun lalu adalah januari bulan param, misal, param maret 2021, 
maka periodelalu untuk desember 2020, tapi data yang akan diambil januari 2021,
karna konsep neraca adalah saldo akhir bulan dipilih atau saldo awal bulan depan
contoh maret 2021

tampilan untuk periode lalu adalah desember 2020, namun datanya adalah januari 2021
tampilan untuk periode lalu adalah januari 2021, namun datanya adalah februari 2021
tampilan untuk periode lalu adalah februari 2021, namun datanya adalah maret 2021
*/

$qwe=explode('-',$param['periode']);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$perlalu=$tahunlalu.'-'.$qwe[1];

#= bentuk array bulan
$arrperjudul=month_inbetween($tahun.'-01',$param['periode']);
// array_push($arrper,month_inbetween($tahunlalu.'-01',$perlalu));


// echo $qwe[1];

$dataper=(float)$qwe[1];
// echo $dataper;
$cspan=0;
for($i=1;$i<=1;$i++){ ## INI UNTUK PERIODE BULAN 01 DAN AMBIL SALDO AWAL BUKAN KREDIT - DEBET
	if(strlen($i)<2){
		$i='0'.$i;
	}
	$arrper[$tahun.'-'.$i]=$tahun.'-'.$i;
	$arrper[$tahunlalu.'-'.$i]=$tahunlalu.'-'.$i;
	$arrper[$i]=$i;
	$cspan++;
}
for($i=2;$i<=(float)$qwe[1];$i++){## INI DIBUAT UNTUK BUAT PERIODE HANYA LEBIH BESAR DARI PERIODE BULAN 01 BUAT DIAMBIL DEBET - KREDIT BUKAN SALDO AWAL
	if(strlen($i)<2){
		$i='0'.$i;
	}
	$arrper[$tahun.'-'.$i]=$tahun.'-'.$i;
	$arrper[$tahunlalu.'-'.$i]=$tahunlalu.'-'.$i;
	$arrper[$i]=$i;
	$cspan++;
}
/*
for($i=2;$i<=(float)$qwe[1];$i++){## INI DIBUAT UNTUK BUAT PERIODE HANYA LEBIH BESAR DARI PERIODE BULAN 01 BUAT DIAMBIL DEBET - KREDIT BUKAN SALDO AWAL
	if(strlen($i)<2){
		$i='0'.$i;
	}
	$arrpernext[$tahun.'-'.$i]=$tahun.'-'.$i;
	$arrpernext[$tahunlalu.'-'.$i]=$tahunlalu.'-'.$i;
	$arrpernext[$i]=$i;
	$cspan++;
}
*/
// echo"<pre>";
// print_r($arrpernext);
// exit();

$nouruttemp='';
$daftarakun=array();
$daftartotal=array();
$jumlahdaftar=array();

#= ambil list laporan
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan."'";
$res=fetchdata($str);
foreach($res as $bar){
	$arrnourut[$bar['nourut']]=$bar['nourut'];
	$namanourut[$bar['nourut']]=$bar['keterangandisplay'];
	$noakuntotalnourut[$bar['nourut']]=$bar['noakundisplay'];
	$tipenourut[$bar['nourut']]=$bar['tipe'];
	$posisi[$bar['nourut']]=$bar['posisi'];
}

#= ambil jumlah
$str="select count(*) as jumlah,nourut from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' group by nourut";
$res=fetchdata($str);
foreach($res as $bar){
	$jumlahdaftar[$bar['nourut']]=$bar['jumlah'];
}

#= Ambil data nilai
$sql = "select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."'";
$res=fetchdata($sql);
foreach($res as $bar){
	$listakun[$bar['noakun']]=$bar['nourut'];
}

#= ambil daftar noakun
$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' order by nourut asc";
$res=fetchdata($str);
foreach($res as $bar){
	if($nouruttemp==$bar['nourut']){
		$no++;	
	}else{
		$no=1;
	}
	
	if($nouruttemp==$bar['nourut']){
		if($no<$jumlahdaftar[$bar['nourut']]){
			$daftarakun[$bar['nourut']].=$bar['noakun'].',';
		}else{
			$daftarakun[$bar['nourut']].=$bar['noakun'];
		}
	}else{
		if($jumlahdaftar[$bar['nourut']]==1){ #= hanya 1 akun saja
			@$daftarakun[$bar['nourut']].=$bar['noakun'];
		} else{
			@$daftarakun[$bar['nourut']].=$bar['noakun'].',';
		}
	}
	$nouruttemp=$bar['nourut'];
}

#= Noakun
$sql = "select noakun,sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where noakun IN ('".implode("','",$daftarakun)."') group by noakun";
$res = fetchData($sql);
foreach($res as $val):
	$data[$listakun[$val['noakun']]]=$val['jumlah'];
endforeach;

# Buat Cek Per Nourut
foreach($listakun as $akun => $val):
	if(!isset($data[$val])){
		$data[$val] = 0;
	}
endforeach;

// echo "<pre>";
// print_r($data);

if($param['tipe'] == 'pdf'){
	$fsize = '9px';
	$cell = '0.5';
	$cspc = '0';
	$brd = '0.1';
	$sty = 'border-top:0.1px solid black;border-bottom:0.1px solid black;font-weight:bold;';
}else{
	$cell = '0';
	$cspc = '1';
	$brd = '0';
	$sty = '';
}

$stream.="<p>";
	$stream.="<b>";
		$stream.=$namaorg[$param['kodept']]."";
			$stream.="<br/>";
		$stream.="LAPORAN LABA RUGI";
			$stream.="<br/>";
		$stream.="UNTUK BULAN YANG BERAKHIR ".tglakhirbulan(substr($param['periode'],5,2))." ".numToMonth(substr($param['periode'],5,2),"I","long")." ".substr($param['periode'],0,4);
	$stream.="</b>";
$stream.="</p>";

$stream.="<table class=freezetbl border=0 cellspacing=".@$cspc." cellpadding=3 style='width:100%;".@$fsize."'>";
	$stream.="<thead>";
		$stream.="<tr class=rowheader>";
			$stream.= "<th style='width:520px' align=center colspan=3>".$_SESSION['lang']['keterangan']."</th>";
			$stream.= "<th style='width:120px' align=center colspan=2>S/D ".$newperiode."</th>";
		$stream.="</tr>";
	$stream.="</thead>";
	
	$stream.="<tbody>";
		foreach($arrnourut as $nourut => $val):
			if($tipenourut[$nourut]=='Header'):
				$stream.="<tr class=rowcontent>";
					$stream.="<td style='width:10px'></td>";
					$stream.="<td colspan=5><b>".$namanourut[$nourut]."</b></td>";
				$stream.="</tr>";
			elseif($tipenourut[$nourut]=='Detail'):
			$stream.="<tr class=rowcontent>";
				$stream.="<td colspan=2></td>";
				$stream.="<td> - ".$namanourut[$nourut]."</td>";
				$stream.="<td align=right>".number_format($data[$nourut],$dgt)."</td>";
			$stream.="</tr>";
			else: # Adalah Total
				$stream.="<tr class=rowcontent>";
					$stream.="<td colspan=2></td>";
					$stream.="<td><b>".$namanourut[$nourut]."</b></td>";
					$stream.="<td align=right><b>".number_format($total[$nourut],$dgt)."</b></td>";
				$stream.="</tr>";

				$stream.="<tr class=rowcontent><td colspan=5></td></tr>";
			endif;
		endforeach;
	$stream.="</tbody>";
		
	
	
$stream.="</table>";	


if($param['tipe']=='excel'){
	$nop=$kodelaporan."_".$param['kodept']."_".$param['periode'].".xls";
	$xls = new HtmlExcel();
	$xls->setCss($css);
	$xls->addSheet("LABARUGIKONSOL", $stream);
	// $xls->addSheet("Report", $tab2);
	$xls->headers($nop);
	echo $xls->buildFile();
} else if ($param['tipe']=='pdf') {
	$dompdf = new Dompdf();
	$dompdf->loadHtml($stream);
	$dompdf->setPaper('A4', 'landscape');
	$dompdf->render();
	$dompdf->stream("LABARUGIKONSOL",array("Attachment"=>0));
} else {
	echo $stream;
}


?>