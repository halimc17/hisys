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


foreach($arrnourut as $nourut){

	if($tipenourut[$nourut]=='Detail' and (@$jumlahdaftar[$nourut]>0 || @$jumlahdaftar[$nourut]!='')){
		$listakun='';
		if(@$jumlahdaftar[$nourut]>0){
			$listakun=" and noakun in (".$daftarakun[$nourut].")";
		}
		#= periode ini
		foreach($arrper as $per){
			$str="select sum(jumlah) as dtthnini from ".$dbname.".keu_jurnaldt_vw where 1=1  ".$where."  ".$listakun."  and periode='".$per."'";
			// echo $str.';';
			$res=fetchdata($str);
			foreach($res as $bar){
				// if(substr($nourut,0,3)=='111'){
					// $bar['dtthnini']=$bar['dtthnini']*-1;
				// }
				// @$dtthnini[$nourut][$per]+=$bar['dtthnini'];
				@$dtthnini[$nourut][$per]+=($bar['dtthnini']*$posisi[$nourut]);
				// @$ytdthn[$nourut][substr($per,0,4)]+=$bar['dtthnini'];
				@$ytdthn[$nourut][substr($per,0,4)]+=($bar['dtthnini']*$posisi[$nourut]);
			}
			$i=explode('-',$per);
			@$dtthnini[$nourut][$i[1]]=($dtthnini[$nourut][$tahun.'-'.$i[1]]-$dtthnini[$nourut][$tahunlalu.'-'.$i[1]])/$dtthnini[$nourut][$tahunlalu.'-'.$i[1]]*100;
			//tahunini-tahunlalu/tahunlalu
			### Rumus Pak Hadi
			if ($dtthnini[$nourut][$tahunlalu.'-'.$i[1]]==0 && $dtthnini[$nourut][$tahun.'-'.$i[1]]!=0) {
				$asuini[$nourut][$i[1]]=100;
			}else{
				$asuini[$nourut][$i[1]]=($dtthnini[$nourut][$tahun.'-'.$i[1]]-$dtthnini[$nourut][$tahunlalu.'-'.$i[1]])/($dtthnini[$nourut][$tahunlalu.'-'.$i[1]])*100;
			}
			@$changeini[$nourut][$i[1]]=$asuini[$nourut][$i[1]];
			### Rumus Pak Hadi
			if ($ytdthn[$nourut][$tahunlalu]==0 && $ytdthn[$nourut][$tahun]!=0) {
				$asu[$nourut]=100;
			}else{
				$asu[$nourut]=($ytdthn[$nourut][$tahun]-$ytdthn[$nourut][$tahunlalu])/$ytdthn[$nourut][$tahunlalu]*100;
			}

			@$change[$nourut]=$asu[$nourut];
			
		}
	}
}
// echo"<pre>";
// print_r($dtthnini);

#= buat total
foreach($arrnourut as $nourut){
	if($tipenourut[$nourut]=='Total'){
		$daftartotal=explode(',',$noakuntotalnourut[$nourut]);
		foreach($daftartotal as $key){
			if($key!=''){
				$amin=substr($key,0,1);
				if($amin=='-'){
					$keydata=substr($key,1,5);
					foreach($arrper as $per){
						@$dtthnini[$nourut][$per]-=$dtthnini[$keydata][$per];
						@$ytdthn[$nourut][substr($per,0,4)]-=$dtthnini[$keydata][$per];
						$i=explode('-',$per);
						@$dtthnini[$nourut][$i[1]]=($dtthnini[$nourut][$tahun.'-'.$i[1]]-$dtthnini[$nourut][$tahunlalu.'-'.$i[1]])/$dtthnini[$nourut][$tahunlalu.'-'.$i[1]]*100;
						### Rumus Pak Hadi
						if ($dtthnini[$nourut][$tahunlalu.'-'.$i[1]]==0 && $dtthnini[$nourut][$tahun.'-'.$i[1]]!=0) {
							$asuini[$nourut][$i[1]]=100;
						}else{
							$asuini[$nourut][$i[1]]=($dtthnini[$nourut][$tahun.'-'.$i[1]]-$dtthnini[$nourut][$tahunlalu.'-'.$i[1]])/($dtthnini[$nourut][$tahunlalu.'-'.$i[1]])*100;
						}
						@$changeini[$nourut][$i[1]]=$asuini[$nourut][$i[1]];
						### Rumus Pak Hadi
						if ($ytdthn[$nourut][$tahunlalu]==0 && $ytdthn[$nourut][$tahun]!=0) {
							$asu[$nourut]=100;
						}else{
							$asu[$nourut]=($ytdthn[$nourut][$tahun]-$ytdthn[$nourut][$tahunlalu])/$ytdthn[$nourut][$tahunlalu]*100;
						}
						@$change[$nourut]=$asu[$nourut];
					}
					
				}else{
					foreach($arrper as $per){
						@$dtthnini[$nourut][$per]+=$dtthnini[$key][$per];
						@$ytdthn[$nourut][substr($per,0,4)]+=$dtthnini[$key][$per];
						$i=explode('-',$per);
						@$dtthnini[$nourut][$i[1]]=($dtthnini[$nourut][$tahun.'-'.$i[1]]-$dtthnini[$nourut][$tahunlalu.'-'.$i[1]])/$dtthnini[$nourut][$tahunlalu.'-'.$i[1]]*100;
						### Rumus Pak Hadi
						if ($dtthnini[$nourut][$tahunlalu.'-'.$i[1]]==0 && $dtthnini[$nourut][$tahun.'-'.$i[1]]!=0) {
							$asuini[$nourut][$i[1]]=100;
						}else{
							$asuini[$nourut][$i[1]]=($dtthnini[$nourut][$tahun.'-'.$i[1]]-$dtthnini[$nourut][$tahunlalu.'-'.$i[1]])/($dtthnini[$nourut][$tahunlalu.'-'.$i[1]])*100;
						}
						@$changeini[$nourut][$i[1]]=$asuini[$nourut][$i[1]];
						### Rumus Pak Hadi
						if ($ytdthn[$nourut][$tahunlalu]==0 && $ytdthn[$nourut][$tahun]!=0) {
							$asu[$nourut]=100;
						}else{
							$asu[$nourut]=($ytdthn[$nourut][$tahun]-$ytdthn[$nourut][$tahunlalu])/$ytdthn[$nourut][$tahunlalu]*100;
						}
						@$change[$nourut]=$asu[$nourut];
					}
				}
				
				
			}
		}
	}
}
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
$stream.="<table class=freezetbl border=0 cellspacing=".@$cspc." cellpadding=1 style='width:100%;".@$fsize."'>";
	$stream.="<thead>";
$stream.="<tr class=rowheader>";
// $stream.="<th align=center style='".$sty."' colspan='".(($cspan*3)+2)."'><b>".strtoupper($namaorg[$param['kodept']]."<br>".$judullaporan)."</b></th>";
$stream.="<th align=left style='".$sty."' colspan='".(($cspan*3)+2+3)."'><b>".strtoupper($namaorg[$param['kodept']]."".@$judulunit."<br>".$judullaporan)."</b></th>";
$stream.="</tr>";
	$stream.="<tr class=rowheader>";
        $stream.="<th align=center style='".$sty."' colspan=2 rowspan=2><b>".$_SESSION['lang']['keterangan']."</b></th>";
		foreach($arrperjudul as $per){
			$stream.="<th align=center style='".$sty."' colspan=3><b>".numToMonth(floatval(substr($per,5,2)),'I','long')."</b></th>";
		}
		$stream.="<th align=center style='width:5%;".$sty."' rowspan=2><b>YTD ".$tahun."</b></th>";
		$stream.="<th align=center style='width:5%;".$sty."' rowspan=2><b>YTD ".$tahunlalu."</b></th>";
		$stream.="<th align=center style='width:5%;".$sty."' rowspan=2><b>Change<br>(%)</b></th>";
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
				$stream.="<td align=left style='width:1%'></td>"; 
				$stream.="<td align=left style='width:20%'>".$namanourut[$nourut]."</td>"; 
			}
			if($tipenourut[$nourut]=='Total'){
				@$style="style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'";
				// $stream.="<td align=left>&nbsp;</td>"; 
				$stream.="<td align=left ".$style."  colspan=2><b>".$namanourut[$nourut]."</b></td>"; 
			}
			
			//
			#= data untuk nilainya
			#= tahun sekarang
			foreach($arrper as $per){
				$tahunjudul=substr($per,0,4);
				if($tipenourut[$nourut]=='Detail'){
					$style="style=cursor:pointer; title='Click untuk melihat detail' onclick=\"detail('".@$nourut."','".@$per."','".@$param['kodept']."','".@$regional."','".@$param['kodeunit']."','html','event','".$param['digit']."');\"";
				}
				if(strlen($tahunjudul)<'4'){
					$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan(@$changeini[$nourut][$per]),$dgt)."</td>"; 
				}else{
					$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan(@$dtthnini[$nourut][$per]),$dgt)."</td>"; 
				}
			}
			
			if($tipenourut[$nourut]=='Total'){
				@$style="style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'";
			}
			$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan(@$ytdthn[$nourut][$tahun]),$dgt)."</td>"; 
			$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan(@$ytdthn[$nourut][$tahunlalu]),$dgt)."</td>"; 
			$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan(@$change[$nourut]),$dgt)."</td>"; 
			
		$stream.="</tr>";
	}
	
	
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