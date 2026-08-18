<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/fpdf.php');
require_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
include_once('lib/HtmlExcel.php');
use Dompdf\Dompdf;
error_reporting(0);
$method = checkPostGet('method','');
$param = $_POST;
$cparam=count($param);
if($cparam==0){
	$param=$_GET;
}

if($param['periode']==''){
	exit("Warning:Periode masih kosong");
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



$qwe=explode('-',$param['periode']);
$tahun=$qwe[0];
$tahunlalu=$tahun-1;
$perlalu=$tahunlalu.'-'.$qwe[1];
$awaltahun=$tahun.'-01';
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



$wh='';
$wh1='';
$wh2='';
$wh3='';
if ($param['kodept']!='') {
	$whx=" and a.kodeorganisasi='".$param['kodept']."'";
	$wh=" and kodeorganisasi='".$param['kodept']."'";
	$wh1=" and kodept='".$param['kodept']."'";
	$wh2=" and unit in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."')";
	$wh3=" and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."')";
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
$CLS='Gray';
$stream.="<table class=sortable border=0 cellspacing=".$cspc." cellpadding=1 style='width:100%;".$fsize."'>";
	//$stream.="<thead>";
$stream.="<tr class=rowheader>";
//$stream.="<th align=center style='".$sty."' colspan='".(($cspan*3)+2)."'><b>".strtoupper($namaorg[$param['kodept']]."<br>".$judullaporan)."</b></th>";
$stream.="<td bgcolor=".$CLS." align=left style='".$sty."' colspan=2><b>KONSOLIDASI <br> FINANCIAL HIGHLIGHTS <br> ".numToMonth(substr($param['periode'],5,2),'E','long')." ".substr($param['periode'],0,4)."</b></td>";
$stream.="<td bgcolor=".$CLS." align=center style='".$sty."'><b>KSP <br> KONSOLIDASI</b></td>";
//daftar pt
$str="select namaorganisasi,a.kodeorganisasi from ".$dbname.".organisasi a left join keu_5pt_urut b on a.kodeorganisasi=b.kodeorganisasi where tipe='PT' ".$whx." group by a.kodeorganisasi order by nourut";
$res=fetchdata($str);
foreach($res as $bar){
	$pete[$bar['kodeorganisasi']]=$bar['kodeorganisasi'];
	
}
foreach ($pete as $pt) {
$tmbh='';
	if ($pt=='KSP') {
		$tmbh="Holding";
	}
	$stream.="<td bgcolor=".$CLS." align=center style='".$sty."'><b>PT. ".$pt." <br> ".$tmbh."</b></td>";
}
$stream.="<td bgcolor=".$CLS." align=center style='".$sty."'><b>PT. C <br> Konsolidasi</b></td>";
$stream.="<td bgcolor=".$CLS." align=center style='".$sty."'><b>PT. D <br> Konsolidasi</b></td>";
$CL='Orange Red';
$stream.="</tr>";
//$stream.="</thead>";
$stream.="<tr class=rowheader>";
$stream.="<td bgcolor=".$CL." align=center style='".$sty."' colspan=2><b>D E S C R I P T I O N S</b></td>";
$stream.="<td align=center bgcolor=".$CL." style='".$sty."'><b>YTD <br> Actual</b></td>";
//daftar pt
$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where tipe='PT'  ".$wh." group by kodeorganisasi";
$res=fetchdata($str);
foreach($res as $bar){

	$stream.="<td bgcolor=".$CL." align=center style='".$sty."'><b>YTD <br> Actual</b></td>";
}
$stream.="<td bgcolor=".$CL." align=center style='".$sty."'><b>YTD <br> Actual</b></td>";
$stream.="<td bgcolor=".$CL." align=center style='".$sty."'><b>YTD <br> Actual</b></td>";
$stream.="</tr>";
//$stream.="</thead>";
$CLR='Yellow';
###### I ######


// ambil dari laporan neraca konsol
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
$where="";
#= daftar unit dalam 1 pt
if ($param['kodept']!='') {
	$where=" and substr(kodeorg,1,4) in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$param['kodept']."'  ".$whereunit.")";
}




$kodelaporan1='LAPORANGROUP2';

#= untuk judul laporan
$str="select * from ".$dbname.".keu_5mesinlaporanht where namalaporan='".$kodelaporan1."'";
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
$str="select * from ".$dbname.".keu_5mesinlaporandt where namalaporan='".$kodelaporan1."'";
$res=fetchdata($str);
foreach($res as $bar){
	$arrnourut[$bar['nourut']]=$bar['nourut'];
	$namanourut[$bar['nourut']]=$bar['keterangandisplay'];
	$noakuntotalnourut[$bar['nourut']]=$bar['noakundisplay'];
	$tipenourut[$bar['nourut']]=$bar['tipe'];
	$noakundari[$bar['nourut']]=$bar['noakundari'];
	$noakunsampai[$bar['nourut']]=$bar['noakunsampai'];

	$noakun[$bar['nourut']]=$bar['noakundari'].'xx'.$bar['noakunsampai'];
}

#= ambil jumlah
$str="select count(*) as jumlah,nourut from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan1."' group by nourut";
$res=fetchdata($str);
foreach($res as $bar){
	$jumlahdaftar[$bar['nourut']]=$bar['jumlah'];
}


#= ambil daftar noakun
$str="select * from ".$dbname.".keu_5mesinlaporandt_akun where namalaporan='".$kodelaporan1."' order by nourut asc";
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
			$daftarjab[$bar['nourut']].=$bar['keterangan'].',';
		}else{
			$daftarakun[$bar['nourut']].=$bar['noakun'];
			$daftarjab[$bar['nourut']].=$bar['keterangan'];
		}
	}else{
		if($jumlahdaftar[$bar['nourut']]==1){ #= hanya 1 akun saja
			@$daftarakun[$bar['nourut']].=$bar['noakun'];
			@$daftarjab[$bar['nourut']].=$bar['keterangan'];
		} else{
			@$daftarakun[$bar['nourut']].=$bar['noakun'].',';
			@$daftarjab[$bar['nourut']].=$bar['keterangan'].',';
		}
	}
	$nouruttemp=$bar['nourut'];
}


$str="select kodept,sum(jumlah) as jumlah,kodebarang from ".$dbname.".pmn_bast where substr(tanggalbl,1,7) between '".$awaltahun."' and '".$param['periode']."' ".$wh1." group by kodept,kodebarang";
$res=fetchdata($str);
foreach($res as $bar){
	if ($bar['kodebarang']=='40000001') {
	
		$dtthnini[$bar['kodept']]['2010001']+=$bar['jumlah']/1000;
	}
	if ($bar['kodebarang']=='40000002') {
		//$jumpk[$bar['kodept']]+=$bar['jumlah']/1000;
		$dtthnini[$bar['kodept']]['2010002']+=$bar['jumlah']/1000;
	}

}

$strx="select induk,sum(kgnetto) as kgnetto from ".$dbname.".kebun_tbsjual a left join ".$dbname.".organisasi b on a.unit=b.kodeorganisasi  where substr(tanggal,1,7) between '".$awaltahun."' and '".$param['periode']."' ".$wh2." group by induk";
$resx=fetchdata($strx);
foreach($resx as $barx){
    //$jumffb[$barx['induk']]=$barx['kgnetto']/1000;
    $dtthnini[$bar['kodept']]['2010003']+=$bar['jumlah']/1000;
}

$optopr  = makeOption($dbname,'keu_5mesinlaporandt','nourut,operator',"namalaporan='".$kodelaporan1."'");


foreach($arrnourut as $nourut){

	$per=$param['periode'];
	$perdata=periodeberikut($per);
	$explodeperdata=explode('-',$perdata);
	$bulanperdata=$explodeperdata[1];
	$kolomthnini="awal".$bulanperdata;
	$perdata=str_replace("-", "",$perdata);	//periode depan karna diambil dari saldo akhir berjalan, misal data periode 3, maka ambil sawal periode 4

	$kdbrg='';
	if ($nourut=='3011001') {
		$kdbrg=" and kodebarang ='40000001'";
	}
	if ($nourut=='3011002') {
		$kdbrg=" and kodebarang ='40000002'";
	}

	if($tipenourut[$nourut]=='Detail' and (@$jumlahdaftar[$nourut]>0 || @$jumlahdaftar[$nourut]!='')){
		$listakun='';
		if(@$jumlahdaftar[$nourut]>0){
			$listakun=" and a.noakun in (".$daftarakun[$nourut].")";
			$listakun2=" and kodejabatan in (".$daftarakun[$nourut].")";
			$listjab=" and bagian in ('".$daftarjab[$nourut]."')";
		}
		#= periode ini

			

			if (substr($nourut,0,1)=='4') {
				
				$str="select induk as kodept,sum(".$kolomthnini.") as dtthnini from ".$dbname.".keu_saldobulanan a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where 1=1  ".$where."  ".$listakun."  and periode between '".$awaltahun."' and '".$perdata."' group by kodept";
				$res=fetchdata($str);
				foreach($res as $bar){
				

					if ($optopr[$nourut]=='-') {
						@$dtthnini[$bar['kodept']][$nourut]-=$bar['dtthnini'];
					}
					else
					{
						@$dtthnini[$bar['kodept']][$nourut]+=$bar['dtthnini'];
					}

				}
				
			}
			else if (substr($nourut,0,1)=='5') {
					$str="select count(karyawanid) as jum,kodeorganisasi from ".$dbname.".datakaryawan where 1=1 ".$listjab." ".$listakun2." and tanggalkeluar='0000-00-00' group by kodeorganisasi";

					$res=fetchdata($str);
					foreach($res as $bar){

						if ($optopr[$nourut]=='-') {
							@$dtthnini[$bar['kodeorganisasi']][$nourut]-=$bar['jum'];
						}
						else
						{
							@$dtthnini[$bar['kodeorganisasi']][$nourut]+=$bar['jum'];
						}

					}

			}
			else
			{

				$str="select induk as kodept,sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where 1=1  ".$where."  ".$listakun."  and periode between '".$awaltahun."' and '".$perdata."' group by kodept";
				$res=fetchdata($str);
				foreach($res as $bar){
					if ($optopr[$nourut]=='-') {
						@$dtthnini[$bar['kodept']][$nourut]-=$bar['jumlah'];
					}
					else
					{
						@$dtthnini[$bar['kodept']][$nourut]+=$bar['jumlah'];
					}

				}
			}
			


	}

/*	if ($nourut=='4011001') {
		echo "<pre>";
		print_r($dtthninix);
		echo "</pre>";
		exit('error');
	}*/

	if ($tipenourut[$nourut]=='Detail' and $noakundari[$nourut]!='') {

		
		if (substr($nourut,0,1)=='4') {
				$str="select induk as kodept,sum(".$kolomthnini.") as dtthnini from ".$dbname.".keu_saldobulanan a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where 1=1  ".$where."  and a.noakun between '".$noakundari[$nourut]."' and '".$noakunsampai[$nourut]."' and periode between '".$awaltahun."' and '".$perdata."' group by kodept";
				$res=fetchdata($str);
				foreach($res as $bar){

					if ($optopr[$nourut]=='-') {
						@$dtthnini[$bar['kodept']][$nourut]-=$bar['dtthnini'];
					}
					else
					{
						@$dtthnini[$bar['kodept']][$nourut]+=$bar['dtthnini'];
					}
					
				}
		}
		else if (substr($nourut,0,1)=='5') {
			$str="select count(karyawanid) as jum,kodeorganisasi from ".$dbname.".datakaryawan where 1=1 ".$listjab." and a.kodejabatan between '".$noakundari[$nourut]."' and '".$noakunsampai[$nourut]."' and tanggalkeluar='0000-00-00' group by kodeorganisasi";

					$res=fetchdata($str);
					foreach($res as $bar){

						if ($optopr[$nourut]=='-') {
							@$dtthnini[$bar['kodeorganisasi']][$nourut]-=$bar['jum'];
						}
						else
						{
							@$dtthnini[$bar['kodeorganisasi']][$nourut]+=$bar['jum'];
						}
					}

		}
		else
		{
			$str="select induk as kodept,sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw a left join ".$dbname.".organisasi b on a.kodeorg=b.kodeorganisasi where 1=1  ".$where."  and a.noakun between '".$noakundari[$nourut]."' and '".$noakunsampai[$nourut]."'  and periode between '".$awaltahun."' and '".$perdata."' ".$kdbrg." group by kodept";
			$res=fetchdata($str);
			foreach($res as $bar){
				if ($optopr[$nourut]=='-') {
					@$dtthnini[$bar['kodept']][$nourut]-=$bar['jumlah'];
				}
				else
				{
					@$dtthnini[$bar['kodept']][$nourut]+=$bar['jumlah'];
				}


			}

		}
	}
}

#= buat total
foreach($arrnourut as $nourut){
	if($tipenourut[$nourut]=='Total'){
		$daftartotal=explode(',',$noakuntotalnourut[$nourut]);
		foreach($daftartotal as $key){
			//foreach($arrper as $per){
			foreach ($pete as $pt) {

				$amin=substr($key,0,1);
				$key=str_replace('-','', $key);

                                
				if ($amin=='-') {
					@$dtthnini[$pt][$nourut]-=$dtthnini[$pt][$key];
				}
				else
				{
					@$dtthnini[$pt][$nourut]+=$dtthnini[$pt][$key];
				}
				

			}
				
			//}
		}
	}
}


foreach($arrnourut as $nourut){
	$style='';
	$stream.="<tr class=rowcontent>";
	if($tipenourut[$nourut]=='Header'){
		if ($nourut=='1000000' || $nourut=='2000000' || $nourut=='3000000' || $nourut=='4000000' || $nourut=='5000000' || $nourut=='6000000') {
			$COLR='bgcolor=Yellow';

		}
		else
		{
			$COLR='';
		}
		$no=0;
		foreach ($pete as $pt) {
			$no+=1;
		}
		$cols=$no+4;
		$stream.="<td align=left ".$COLR." style='width:1%'></td>"; 
		$stream.="<td ".$COLR." align=left colspan=".$cols."><b>".$namanourut[$nourut]."</b></td>"; 

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
	//foreach($arrper as $per){
		foreach ($pete as $pt) {
			if ($nourut=='3031001') {
				//$dtthnini[$pt][$nourut]=$dtthnini[$pt]['3011999']/$dtthnini[$pt]['3011001'];
				$dtthnini[$pt][$nourut]=$dtthnini[$pt]['3011999']/$dtthnini[$pt]['3011001'];
			}
			if ($nourut=='3031002') {
				//$dtthnini[$pt][$nourut]=$dtthnini[$pt]['3011999']/$dtthnini[$pt]['3011002'];
				$dtthnini[$pt][$nourut]=$dtthnini[$pt]['3011999']/$dtthnini[$pt]['3011002'];
			}

				if($tipenourut[$nourut]=='Detail' ){
			       $konsdtthnini[$nourut]+=$dtthnini[$pt][$nourut];
			     }
		}


		if($tipenourut[$nourut]=='Detail' ){
			
			$style="";
			$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan($konsdtthnini[$nourut]),2)."</td>"; 
			
			
		}
		if ($tipenourut[$nourut]=='Total') {
			@$style="style='border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'";
			foreach ($pete as $pt) {
			$totkonsdtthnini[$nourut]+=$dtthnini[$pt][$nourut];
			}

			$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan($totkonsdtthnini[$nourut]))."</td>"; 
			$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan(0))."</td>"; 
			$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan(0))."</td>"; 
		}
		

		
		foreach ($pete as $pt) {
		
			if ($tipenourut[$nourut]=='Detail') {
				if ($nourut=='3031001') {
					$dtthnini[$pt][$nourut]=$dtthnini[$pt]['3011999']/$dtthnini[$pt]['3011001'];
				}
				if ($nourut=='3031002') {
					$dtthnini[$pt][$nourut]=$dtthnini[$pt]['3011999']/$dtthnini[$pt]['3011002'];
				}
				if ($nourut=='3031003') {
					$dtthnini[$pt][$nourut]=$dtthnini[$pt]['3011999']/$dtthnini[$pt]['3011003'];
				}

				if ($nourut=='6010001') {
					$dtthnini[$pt][$nourut]=$dtthnini[$pt]['4011000']/$dtthnini[$pt]['4021999'];
				}

				if ($nourut=='6010009') {
					$dtthnini[$pt][$nourut]=$dtthnini[$pt]['3011999']/$dtthnini[$pt]['4019999'];
				}

				if ($nourut=='6010010') {
					$dtthnini[$pt][$nourut]=($dtthnini[$pt]['4021999']+$dtthnini[$pt]['4022999'])/$dtthnini[$pt]['4023199'];
				}

				if ($nourut=='6010011') {
					$dtthnini[$pt][$nourut]=($dtthnini[$pt]['4021999']+$dtthnini[$pt]['4022999'])/$dtthnini[$pt]['4019999'];
				}

				if ($nourut=='6010012') {
					$dtthnini[$pt][$nourut]=$dtthnini[$pt]['511999']/$dtthnini[$pt]['4023199'];
				}

				if ($nourut=='6010013') {
					$dtthnini[$pt][$nourut]=$dtthnini[$pt]['511999']/$dtthnini[$pt]['4019999'];
				}


				if ($nourut=='6020001') {
					$dtthnini[$pt][$nourut]=$dtthnini[$pt]['3030000']/$dtthnini[$pt]['3011999'];
				}

				if ($nourut=='6020002') {
					$dtthnini[$pt][$nourut]=($dtthnini[$pt]['3030000']+$dtthnini[$pt]['3049999']+$dtthnini[$pt]['3059999'])/$dtthnini[$pt]['3011999'];
				}

				if ($nourut=='6020003') {
					$dtthnini[$pt][$nourut]=$dtthnini[$pt]['3139999']/$dtthnini[$pt]['3011999'];
				}

				if ($nourut=='6020005') {
					$dtthnini[$pt][$nourut]=$dtthnini[$pt]['3030000']/$dtthnini[$pt]['2019999'];
				}

				$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan($dtthnini[$pt][$nourut]),2)."</td>"; 
			}
			if ($tipenourut[$nourut]=='Total') {
				
				$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan($dtthnini[$pt][$nourut]),2)."</td>"; 
			}	
			
		}

		if($tipenourut[$nourut]=='Detail' ){
			
			$style="";
			$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan(0),2)."</td>"; 
			$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan(0),2)."</td>"; 
			
			
		}




		//$stream.="<td align=right ".$style.">".hidezerodecimal(fixnan($dtthnini[$nourut][$per]))."</td>"; 
	//}
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