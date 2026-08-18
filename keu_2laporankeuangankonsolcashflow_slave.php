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
$cparam=count($param);
if($cparam==0){
	$param=$_GET;
}

if($param['periode']=='' || $param['kodept']==''){
	exit("Warning:Periode / PT masih kosong");
}


#= ambil jumlah
$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$param['kodept']."'";
$res=fetchdata($str);
foreach($res as $bar){
	$namaorg[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

if($param['kodeunit']!=''){
	$whereunit=" and kodeorganisasi='".$param['kodeunit']."'";
}

#= daftar unit dalam 1 pt
$where=" and substr(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."'  ".$whereunit.")";
$whereadjust=" and substr(kodeunit,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."'  ".$whereunit.")";


$kodelaporan='CASHFLOWKONSOL';

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
for($i=1;$i<=(float)$qwe[1];$i++){
	if(strlen($i)<2){
		$i='0'.$i;
	}
	$arrper[$tahun.'-'.$i]=$tahun.'-'.$i;
	$arrper[$tahunlalu.'-'.$i]=$tahunlalu.'-'.$i;
	$arrper[$i]=$i;
	$cspan++;
}


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
}

#= ambil jumlah
$str="select count(*) as jumlah,nourut from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan."' group by nourut";
$res=fetchdata($str);
foreach($res as $bar){
	$jumlahdaftar[$bar['nourut']]=$bar['jumlah'];
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
// echo"<pre>";
// print_r($arrper);

foreach($arrnourut as $nourut){
	
	if($tipenourut[$nourut]=='Detail'){
		$listakun='';
		if(@$jumlahdaftar[$nourut]>0){
			$listakun=" and noakun in (".$daftarakun[$nourut].")";
		}
		#= periode ini
		foreach($arrper as $per){
			if(strlen($per)>'2'){ // yang bulan saja untuk change tidak masuk ke query
				$perdata=$per;
				$explodeperdata=explode('-',$perdata);
				$bulanperdata=$explodeperdata[1];
				$awal="awal".$bulanperdata;
				$debet="debet".$bulanperdata;
				$kredit="kredit".$bulanperdata;
				$perdata=str_replace("-", "",$perdata);
				#= saldo akhir - saldo awal periode berjalan
				#= saldo awal + debet - kredit  - saldo awal
				#= debet - kredit
				if(@$jumlahdaftar[$nourut]>0 || @$jumlahdaftar[$nourut]!=''){
					if($nourut<='31001'){
						$str="select ((sum(".$debet.")-sum(".$kredit."))*-1) as dtthnini from ".$dbname.".keu_saldobulanan where 1=1  ".$where."  ".$listakun."  and periode='".$perdata."'";
					} else if($nourut=='50001'){
						$str="select sum(".$awal.") as dtthnini from ".$dbname.".keu_saldobulanan where 1=1  ".$where."  ".$listakun."  and periode='".$perdata."'";
					}else{
						$str="select sum(".$debet.")-sum(".$kredit.") as dtthnini from ".$dbname.".keu_saldobulanan where 1=1  ".$where."  ".$listakun."  and periode='".$perdata."'";
						/*$str="select sum(".$awal.")+sum(".$debet.")-sum(".$kredit.")-sum(".$awal.") as dtthnini from ".$dbname.".keu_saldobulanan where 1=1  ".$where."  ".$listakun."  and periode='".$perdata."'";*/
					}
					$res=fetchdata($str);
					foreach($res as $bar){
						@$dtthnini[$nourut][$per]+=$bar['dtthnini'];
					}
				}
				
				#= keu_adjustmentlaporankeuangan
				$str="select  sum(jumlah) as dtthnini,code from ".$dbname.".keu_adjustmentlaporankeuangan where 1=1  ".$whereadjust." and jenis='".$kodelaporan."' and code='".$nourut."' and periode='".$per."'";
				$res=fetchdata($str);
				foreach($res as $bar){
					@$dtthnini[$nourut][$per]+=$bar['dtthnini'];
				}
				
				$i=explode('-',$per);
				// @$dtthnini[$nourut][$i[1]]=($dtthnini[$nourut][$tahun.'-'.$i[1]]-$dtthnini[$nourut][$tahunlalu.'-'.$i[1]])/$dtthnini[$nourut][$tahunlalu.'-'.$i[1]]*100;
				@$dtthnini[$nourut][$i[1]]=($dtthnini[$nourut][$tahun.'-'.$i[1]]-$dtthnini[$nourut][$tahunlalu.'-'.$i[1]])/$dtthnini[$nourut][$tahunlalu.'-'.$i[1]]*100;
			}
		}
	}
}


#= buat total
foreach($arrnourut as $nourut){
	if($tipenourut[$nourut]=='Total'){
		$daftartotal=explode(',',$noakuntotalnourut[$nourut]);
		foreach($daftartotal as $key){
			foreach($arrper as $per){
				@$dtthnini[$nourut][$per]+=$dtthnini[$key][$per];
			}
			foreach($arrper as $per){
				$i=explode('-',$per);
				@$dtthnini[$nourut][$i[1]]=($dtthnini[$nourut][$tahun.'-'.$i[1]]-$dtthnini[$nourut][$tahunlalu.'-'.$i[1]])/$dtthnini[$nourut][$tahunlalu.'-'.$i[1]]*100;
			}
		}
	}
}

// echo"<pre>";
// // print_r($dtthnini);
// echo"</pre>";


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
$stream.="<table class=sortable border=0 cellspacing=".$cspc." cellpadding=1 style='width:100%;".$fsize."'>";
	$stream.="<thead>";
$stream.="<tr class=rowheader>";
$stream.="<th align=center style='".$sty."' colspan='".(($cspan*3)+2)."'><b>".strtoupper($namaorg[$param['kodept']]."<br>".$judullaporan)."</b></th>";
$stream.="</tr>";
	$stream.="<tr class=rowheader>";
        $stream.="<th align=center style='".$sty."' colspan=2 rowspan=2><b>".$_SESSION['lang']['keterangan']."</b></th>";
		foreach($arrperjudul as $per){
			$stream.="<th align=center style='".$sty."' colspan=3><b>".numToMonth(floatval(substr($per,5,2)),'I','long')."</b></th>";
		}
	$stream.="</tr>";
	$stream.="<tr class=rowheader>";
		foreach($arrper as $per){
			$tahunjudul=substr($per,0,4);
			if(strlen($tahunjudul)<'4'){
				$tahunjudul='Change<br>(%)';
			}
			$stream.="<th align=center style='width:5%;".$sty."'><b>".$tahunjudul."</b></th>";
		}
	$stream.="</tr>";
	$stream.="</thead>";
	foreach($arrnourut as $nourut){
		$style='';
		$stream.="<tr class=rowcontent>";
			if($tipenourut[$nourut]=='Header'){
				$stream.="<td align=left colspan=2><b>".$namanourut[$nourut]."</b></td>"; 
			}
			if($tipenourut[$nourut]=='Detail'){
				$stream.="<td align=left style='width:1%'>".$nourut."</td>"; 
				$stream.="<td align=left style='width:20%'>".$namanourut[$nourut]."</td>"; 
			}
			if($tipenourut[$nourut]=='Total'){
				@$style="style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'";
				$stream.="<td align=left>".$nourut."</td>"; 
				$stream.="<td align=left ".$style."><b>".$namanourut[$nourut]."</b></td>"; 
			}
			
			//
			#= data untuk nilainya
			#= tahun sekarang
		
			foreach($arrper as $per){
				if($tipenourut[$nourut]=='Detail'){
					$style="style=cursor:pointer; title='Click untuk melihat detail ".$per."' ' onclick=\"detail('".$nourut."','".$per."','".$param['kodept']."','".$regional."','".$param['kodeunit']."','html','event');\"";
				}
				
				if(strlen($per)==2){ //buat hilangin click detail
					$style='';
				}
				
				if($tipenourut[$nourut]=='Total'){ //gara-gara buat hilangin click detail, ketika nourut total garisnya hilang, maka dibuat lagi
					@$style="style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'";
				}
				
				$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan($dtthnini[$nourut][$per]),2)."</td>"; 
			}
		$stream.="</tr>";
	}
	
	
$stream.="</table>";	


if($param['tipe']=='excel'){
	$nop=$kodelaporan."_".$param['kodept']."_".$param['periode'].".xls";
	$xls = new HtmlExcel();
	$xls->setCss($css);
	$xls->addSheet($namalaporan, $stream);
	// $xls->addSheet("Report", $tab2);
	$xls->headers($nop);
	echo $xls->buildFile();
} else if ($param['tipe']=='pdf') {
	$dompdf = new Dompdf();
	$dompdf->loadHtml($stream);
	$dompdf->setPaper('A4', 'landscape');
	$dompdf->render();
	$dompdf->stream($namalaporan.$kodept,array("Attachment"=>0));
} else {
	echo $stream;
}


?>