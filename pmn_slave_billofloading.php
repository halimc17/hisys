<?php

require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$proses = checkPostGet('proses', '');
$pages = checkPostGet('page','');

$pt = checkPostGet('pt', '');
$kontrak = checkPostGet('kontrak', '');
$kontrakcari = checkPostGet('kontrakcari', '');
$bilcari = checkPostGet('bilcari', '');
// $tgl = tanggalsystemn(checkPostGet('tgl', ''));
$tgl = checkPostGet('tgl', '');
$cust = checkPostGet('cust', '');
$bil = checkPostGet('bil', '');
$kg = checkPostGet('kg', '');
$tglcari = checkPostGet('tglcari', '');
$tglbastcari = checkPostGet('tglbastcari', '');
$nobast = checkPostGet('nobast', '');
$page = checkPostGet('page', '');
$tipe = checkPostGet('tipe', '');

// $tglbast = tanggalsystemn(checkPostGet('tglbast', ''));
$tglbast = checkPostGet('tglbast', '');
$ffa = checkPostGet('ffa', '');
$moisture = checkPostGet('moisture', '');
$dirt = checkPostGet('dirt', '');
$dobi = checkPostGet('dobi', '');
$broken = checkPostGet('broken', '');
$mdani = checkPostGet('mdani', '');
$impurities = checkPostGet('impurities', '');
$kota = checkPostGet('kota', '');
$rpkgclaim = checkPostGet('rpkgclaim', '');



$namafile=checkPostGet('namafile','');
$kriteriaefil=checkPostGet('kriteriaefil','');
$createtime=date('Y-m-d H:i:s');
$path   = "fileupload/billofloading/";
$urlefil=checkPostGet('urlefil','0');

$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='400' ";
$res=fetchdata($str);
foreach($res as $bar){
	$arrinisial[$bar['kodebarang']]=$bar['inisial'];
	$namabarang[$bar['kodebarang']]=$bar['namabarang'];
}

$str="select * from ".$dbname.".organisasi where tipe='PT'";
$res=fetchdata($str);
foreach($res as $bar){
	$namapt[$bar['kodeorganisasi']]=$bar['namaorganisasi'];
}

$nmkapalponton=makeOption($dbname,'pmn_5kapalponton','kode,nama');
$matauang=makeOption($dbname,'setup_matauang','kode,simbol');

switch($proses){
	case'pdf':
		$tab="<style>
			@page {
				margin-top: 50px;
				margin-left: 75px;
				margin-right: 75px;
				margin-bottom: 75px;
			}
			body {
				font-family: Serif, Times-Roman;
			}
			
			
			
			footer {
				position: fixed; 
				bottom: -20px; 
				left: 0px; 
				right: 0px;
				height: 50px; 
			}
			
		</style>";
		
			// font-family: Tahoma, Verdana, Segoe, sans-serif;
		
		$str="select * from ".$dbname.".pmn_billofloading where nobl='".$nobast."' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$nokontrak=$bar['nokontrak'];
			$kodept=$bar['pt'];
			$kodecustomer=$bar['kodecustomer'];
			$tanggal=$bar['tanggal']; //tanggal BL
			$tanggalbast=$bar['tanggalbast'];
			$periodejurnal=substr($bar['tanggal'],0,7);
			$kgbast=$bar['kg'];
			$closebast=$bar['close'];
			
			$ffabast=$bar['ffa'];
			$dobibast=$bar['dobi'];	
			$mdanibast=$bar['mdani'];
			$moistbast=$bar['moisture'];		
			$dirtbast=$bar['dirt'];
			$brokenbast=$bar['broken'];
			$impuritiesbast=$bar['impurities'];
			$kotabast=$bar['kota'];
			$rpkgclaim=$bar['rpkgclaim'];
		}
		$nobast=0;
		if($ffabast!=0){
			$nobast++;
			$nilaikualitasbast[$nobast]='max. '.hidezerodecimal($ffabast,3).' %';
			$textkualitasbast[$nobast]='Free Fatty Acid (FFA)';
		}
		if($brokenbast!=0){
			$nobast++;
			$nilaikualitasbast[$nobast]='max. '.hidezerodecimal($brokenbast,3).' %';
			$textkualitasbast[$nobast]='Broken';
		}
		if($dobibast!=0){
			$nobast++;
			$nilaikualitasbast[$nobast]='max. '.hidezerodecimal($dobibast,3).' %';
			$textkualitasbast[$nobast]='Dobi Content';
		}
		if($mdanibast!=0){
			$nobast++;
			$nilaikualitasbast[$nobast]='max. '.hidezerodecimal($mdanibast,3).' %';
			$textkualitasbast[$nobast]='Moisture & Impurities (M&I)';
		}
		if($moistbast!=0){
			$nobast++;
			$nilaikualitasbast[$nobast]='max. '.hidezerodecimal($moistbast,3).' %';
			$textkualitasbast[$nobast]='Moisture Content';
		}
		if($dirtbast!=0){
			$nobast++;
			$nilaikualitasbast[$nobast]='max. '.hidezerodecimal($dirtbast,3).' %';
			$textkualitasbast[$nobast]='Dirt Content';
		}
		if($impuritiesbast!=0){
			$nobast++;
			$nilaikualitasbast[$nobast]='max. '.hidezerodecimal($impuritiesbast,3).' %';
			$textkualitasbast[$nobast]='Impurities Content';
		}
		
		
		#= ambil data dari kontrakjual
		$str="select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$nokontrak."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$kodebarang=$bar['kodebarang'];
			$hargasatuan=$bar['hargasatuan'];
			$persenppn=$bar['persenppn']; // sudah mewakili berikat / tidak , jika ppn 0 => berikat; jika 10 maka tidak berikat
			$ffakontrak=$bar['ffa'];
			$dobikontrak=$bar['dobi'];	
			$mdanikontrak=$bar['mdani'];
			$moistkontrak=$bar['moist'];		
			$dirtkontrak=$bar['dirt'];
			$impuritieskontrak=$bar['grading'];
			$penandatangan=$bar['penandatangan'];
			$satuanbarang=$bar['satuan'];
			$matauang=$matauang[$bar['matauang']];
			$ppnpersen=$bar['ppnpersen'];
			$defaultpersenppn=$bar['defaultpersenppn'];
			// $kontrak=$bar[''];
		}
		
		#= jabatan ttd
		$str="select * from ".$dbname.".pmn_5ttd where nama='".$penandatangan."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$jabatanpenandatangan=$bar['jabatan'];
		}
			
		
		
		$no=0;
		if($ffakontrak!=0){
			$no++;
			$nilaikualitas[$no]='max. '.hidezerodecimal($ffakontrak,3).' %';
			$textkualitas[$no]='Free Fatty Acid (FFA)';
		}
		if($dobikontrak!=0){
			$no++;
			$nilaikualitas[$no]='max. '.hidezerodecimal($dobikontrak,3).' %';
			$textkualitas[$no]='Dobi Content';
		}
		if($mdanikontrak!=0){
			$no++;
			$nilaikualitas[$no]='max. '.hidezerodecimal($mdanikontrak,3).' %';
			$textkualitas[$no]='Moisture & Impurities (M&I)';
		}
		if($moistkontrak!=0){
			$no++;
			$nilaikualitas[$no]='max. '.hidezerodecimal($moistkontrak,3).' %';
			$textkualitas[$no]='Moisture Content';
		}
		if($dirtkontrak!=0){
			$no++;
			$nilaikualitas[$no]='max. '.hidezerodecimal($dirtkontrak,3).' %';
			$textkualitas[$no]='Dirt Content';
		}
		if($impuritieskontrak!=0){
			$no++;
			$nilaikualitas[$no]='max. '.hidezerodecimal($impuritieskontrak,3).' %';
			$textkualitas[$no]='Impurities Content';
		}
		
		
		$str="select * from ".$dbname.".pmn_4customer where kodecustomer='".$kodecustomer."' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$namacustomer=$bar['namacustomer'];
			$penandatangancustomer=$bar['penandatangan'];
			$jabatancustomer=$bar['jabatan'];
		}
		
		#= ambil data ba pengiriman
		#= pakai tanggal (tanggal BL)
		$str="select  sum(jumlah) as jumlah,tanggal,namaponton,namakapal from ".$dbname.".pmn_bapengiriman where nokontrak='".$nokontrak."' and tanggal <= '".$tanggal."' order by tanggal desc limit 1  ";		
		$res=fetchdata($str);
		foreach($res as $bar){
			// $namacustomer=$bar['namacustomer'];
			// $jumlahbapengiriman=$bar['jumlah'];
			// $tanggalbapengiriman=$bar['tanggal'];
			$namaponton=$nmkapalponton[$bar['namaponton']];
			$namakapal=$nmkapalponton[$bar['namakapal']];
		}
		
		if($namaponton!='' and $namakapal!=''){
			$namakapalponton=$namakapal." / ".$namaponton;
		}else{
			$namakapalponton=$namakapal." ".$namaponton;
		}
		
		
		
		$arrkodept = setheadreport('',$kodept);
		$cellpadding=1;
		$cellspacing=1;
		$sizefont='14';
		// print_r($arrkodept);exit();
	
		$tab.="<div style='page-break-after: always;'>";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";//logoheight logowidth
			$tab.="<tr>";
				$tab.="<td style='width:50px;' align=center><img src=".$arrkodept['logo']." style='width:".$arrkodept['logowidth'].";height:".$arrkodept['logoheight']."'></td>"; 
				$tab.="<td style='width:350px;text-align:center;font-size:".($sizefont+14)."px'>".$arrkodept['nama']."</td>"; 
				$tab.="<td style='width:50px;'>&nbsp;</td>";
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:center;font-size:".$sizefont."px' colspan=3>BERITA ACARA PENERIMAAN KOMODITI</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:center;font-size:".$sizefont."px' colspan=3>".$namabarang[$kodebarang]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:center;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;font-size:".$sizefont."px'  colspan=3>KONTRAK NOMOR ".$nokontrak."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
		$tab.="<tr>";
				$tab.="<td style='text-align:left;'  colspan=3>Telah diterima oleh ".$namacustomer." dari ".$namakapalponton." tanggal ".tglnmbln($tanggal,'i','l')." muatan ".$namabarang[$kodebarang]." dengan perincian sebagai berikut :</td>"; 
		$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
		for($i=1;$i<=$no;$i++){
			$tab.="<tr>";
				if($i==1){
					$tab.="<td rowspan='".$no."' valign=top style='text-align:left;width:100px;'>Mutu Standar Komoditi :</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$textkualitas[$i]."</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$nilaikualitas[$i]."</td>"; 
				}else{
					$tab.="<td style='text-align:left;width:150px;'>".$textkualitas[$i]."</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$nilaikualitas[$i]."</td>";
				}
				
			$tab.="</tr>";
		}
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>"; 
					$tab.="<td style='text-align:left;width:250px;'>Jumlah komoditi yang diterima adalah</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".number_format($kgbast)." ".$satuanbarang."</td>";
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;' colspan=3>Mutu barang yang diterima :</td>"; 
			$tab.="</tr>";
			for($i=1;$i<=$nobast;$i++){
			$tab.="<tr>";
				if($i==1){
					$tab.="<td rowspan='".$nobast."' valign=top style='text-align:left;width:100px;'>Mutu Komoditi :</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$textkualitasbast[$i]."</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$nilaikualitasbast[$i]."</td>"; 
				}else{
					$tab.="<td style='text-align:left;width:150px;'>".$textkualitasbast[$i]."</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$nilaikualitasbast[$i]."</td>";
				}
				
			$tab.="</tr>";
		}
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>Perhitungan Penalti akibat penyimpangan mutu dari standar akan diperhitungkan dalam pembayaran.</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>Demikian Berita Acara ini dibuat.</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;' colspan=3>".$kotabast.", ".tglnmbln($tanggalbast,'i','l')."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>".$_SESSION['lang']['dibuat']."</td>"; 
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
					$tab.="<td style='text-align:left;'>".$_SESSION['lang']['diterimaoleh']."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>".$namapt[$kodept]."</td>"; 
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
					$tab.="<td style='text-align:left;'>".$namacustomer."</td>"; 
			$tab.="</tr>";
			for($i=0;$i<=4;$i++){
				$tab.="<tr>";
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
				$tab.="</tr>";
			}
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>".ucwords(strtolower(getKary($penandatangan)))."</td>"; 
				$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
				$tab.="<td style='text-align:left;'>".ucwords(strtolower($penandatangancustomer))."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>".ucwords(strtolower($jabatanpenandatangan))."</td>"; 
				$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
				$tab.="<td style='text-align:left;'>".ucwords(strtolower($jabatancustomer))."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		
		
		$tab.="<footer>";
		$cellpadding=1;	
		$tab.="<table style='font-size:10px' border=0 cellpadding=".$cellpadding." width=100%>";	
			$tab.="<tr>";
				$tab.="<td align=center><b>Alamat Korespondensi :</b></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td align=center><b>".$arrkodept['alamat']."</b></td>"; 
				
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td align=center><b>Telp. ".$arrkodept['telepon']." Fax. ".$arrkodept['fax']."</b></td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		$tab.="</footer>";
		$tab.="</div>";
		
		
		#================================= page 2
		#================================= page 2
		
		$tab.="<div style='page-break-after: always;'>";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0>";//logoheight logowidth
			$tab.="<tr>";
				$tab.="<td style='width:50px;' align=center><img src=".$arrkodept['logo']." style='width:".$arrkodept['logowidth'].";height:".$arrkodept['logoheight']."'></td>"; 
				$tab.="<td style='width:350px;text-align:center;font-size:".($sizefont+14)."px'>".$arrkodept['nama']."</td>"; 
				$tab.="<td style='width:50px;'>&nbsp;</td>";
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:center;font-size:".$sizefont."px' colspan=3>SURAT KETERANGAN PEMBAYARAN</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:center;font-size:".$sizefont."px' colspan=3>".$namabarang[$kodebarang]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:center;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;font-size:".$sizefont."px'  colspan=3>KONTRAK NOMOR ".$nokontrak."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
		$tab.="<tr>";
				$tab.="<td style='text-align:left;'  colspan=3>Telah diterima oleh ".$namacustomer." dari ".$namakapalponton." tanggal ".tglnmbln($tanggal,'i','l')." muatan ".$namabarang[$kodebarang]." dengan perincian sebagai berikut :</td>"; 
		$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
		for($i=1;$i<=$no;$i++){
			$tab.="<tr>";
				if($i==1){
					$tab.="<td rowspan='".$no."' valign=top style='text-align:left;width:100px;'>Mutu Standar Komoditi :</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$textkualitas[$i]."</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$nilaikualitas[$i]."</td>"; 
				}else{
					$tab.="<td style='text-align:left;width:150px;'>".$textkualitas[$i]."</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$nilaikualitas[$i]."</td>";
				}
				
			$tab.="</tr>";
		}
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>"; 
					$tab.="<td style='text-align:left;width:250px;'>Jumlah komoditi yang diterima adalah</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".number_format($kgbast)." ".$satuanbarang."</td>";
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;' colspan=3>Mutu barang yang diterima :</td>"; 
			$tab.="</tr>";
			for($i=1;$i<=$nobast;$i++){
			$tab.="<tr>";
				if($i==1){
					$tab.="<td rowspan='".$nobast."' valign=top style='text-align:left;width:100px;'>Mutu Komoditi :</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$textkualitasbast[$i]."</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$nilaikualitasbast[$i]."</td>"; 
				}else{
					$tab.="<td style='text-align:left;width:150px;'>".$textkualitasbast[$i]."</td>"; 
					$tab.="<td style='text-align:left;width:150px;'>".$nilaikualitasbast[$i]."</td>";
				}
				
			$tab.="</tr>";
		}
		$tab.="</table>";
		
		$tab.="<br>";
		
		
		#= pembayaran uang muka
		#= ambil COA
		$str="select * from ".$dbname.".keu_5jenispenagihandt where kodebarang='".$kodebarang."' and kodejenis='CIPP'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$noakunsales=$bar['noakunsales'];
			$noakunuangmuka=$bar['noakunuangmuka'];
			$noakunppn=$bar['noakunppn'];
			$noakunpiutang=$bar['noakunpiutang'];
		}
		$noum=0;
		#= cari sisa uang muka, dengan cara sum debet-kredit  jurnal where nodok=param nomor kontrak
		$str="select  sum(jumlah) as jumlah,noreferensi from ".$dbname.".keu_jurnaldt_vw where nodok='".$nokontrak."' and noakun='".$noakunuangmuka."' and tanggal<'".$tanggal."' group by noreferensi";
		$res=fetchdata($str);
		foreach($res as $bar){
			$noum++;
			$nilaiuangmuka[$noum]=$bar['jumlah'];
			$tnilaiuangmuka+=$bar['jumlah'];
		}
		
		$nilaiclaim=$rpkgclaim*$kgbast;
		
		if($persenppn==0){
			$persenppn=$defaultpersenppn;
		}
		$nilaipenjualan=$kgbast*$hargasatuan;
		$nilaisisa=$nilaipenjualan+$tnilaiuangmuka-$nilaiclaim;;
		$nilaippn=$persenppn/100*$nilaisisa;
		$nilaitagihan=$nilaisisa+$nilaippn;
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;' colspan=4>Jumlah tagihan kami kepada perusahaan Saudara adalah sebagai berikut :</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;width:325px;'>Nilai penjualan ".hidezerodecimal($kgbast)." ".$satuanbarang." x ".$matauang." ".hidezerodecimal($hargasatuan,2)." / ".$satuanbarang."</td>"; 
				$tab.="<td style='text-align:right;width:20px;'>".$matauang."</td>"; 
				
				$tab.="<td style='text-align:right;width:100px;'>".hidezerodecimal($nilaipenjualan,2)."</td>"; 
				$tab.="<td style='text-align:left;width:75px;'>&nbsp;</td>"; 
			$tab.="</tr>";
			if($nilaiclaim>0){
				$tab.="<tr>";
					$tab.="<td style='text-align:left;'>Penalty Mutu ".hidezerodecimal($kgbast)." ".$satuanbarang." x ".$matauang." ".hidezerodecimal($rpkgclaim,2)." / ".$satuanbarang." ".$matauang." ( ".hidezerodecimal($nilaiclaim,2)." )</td>"; 
					$tab.="<td style='text-align:right;'></td>"; 
					$tab.="<td style='text-align:right'></td>"; 
					$tab.="<td>&nbsp;</td>"; 
				$tab.="</tr>";
			}
			for($i=1;$i<=$noum;$i++){
				$tab.="<tr>";
					$tab.="<td style='text-align:left'>Dikurangi Pembayaran ke - ".$i." ".$matauang." ( ".hidezerodecimal(abs($nilaiuangmuka[$i]),2)." )</td>"; 
					$tab.="<td style='text-align:right'>&nbsp;</td>"; 
					
					$tab.="<td style='text-align:right;'>&nbsp;</td>"; 
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
				$tab.="</tr>";
			}
			$tab.="<tr>";
				$tab.="<td style='text-align:left'>&nbsp;</td>"; 
				$tab.="<td style='text-align:right'>&nbsp;</td>"; 
				$tab.="<td  style='text-align:center;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000'>&nbsp;</td>"; 
				$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left'>Sisa tagihan menjadi</td>"; 
				$tab.="<td style='text-align:right'>".$matauang."</td>"; 
				$tab.="<td style='text-align:right;'>".hidezerodecimal($nilaisisa,2)."</td>"; 
				$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left'>PPN ".$ppnpersen." % yang harus dibayar</td>"; 
				$tab.="<td style='text-align:right'>".$matauang."</td>"; 
				$tab.="<td style='text-align:right;'>".hidezerodecimal($nilaippn,2)."</td>"; 
				$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left'>&nbsp;</td>"; 
				$tab.="<td style='text-align:right'>&nbsp;</td>"; 
				$tab.="<td  style='text-align:center;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000'>&nbsp;</td>"; 
				$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left'>Tagihan yang harus dibayar</td>"; 
				$tab.="<td style='text-align:right'>".$matauang."</td>"; 
				$tab.="<td style='text-align:right;'>".hidezerodecimal($nilaitagihan,2)."</td>"; 
				$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left'>&nbsp;</td>"; 
				$tab.="<td style='text-align:right'>&nbsp;</td>"; 
				$tab.="<td style='text-align:right'>&nbsp;</td>"; 
				$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left'>Demikian keterangan ini dibuat.</td>"; 
				$tab.="<td style='text-align:right'></td>"; 
				$tab.="<td style='text-align:right;'></td>"; 
				$tab.="<td style='text-align:left;'></td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=".$cellspacing." border=0 style='font-size:".$sizefont."px;'>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;' colspan=3>".$kotabast.", ".tglnmbln($tanggalbast,'i','l')."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>".$_SESSION['lang']['dibuat']."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
					$tab.="<td style='text-align:left;'>".$namapt[$kodept]."</td>"; 
			$tab.="</tr>";
			for($i=0;$i<=4;$i++){
				$tab.="<tr>";
					$tab.="<td style='text-align:left;'>&nbsp;</td>"; 
				$tab.="</tr>";
			}
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>".ucwords(strtolower(getKary($penandatangan)))."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>".ucwords(strtolower($jabatanpenandatangan))."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		
		$tab.="<br>";
		
		
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		
		$dompdf->render();
		if($urlefil=='0'){
			$dompdf->stream("Print_BAST_".$nobast,array("Attachment"=>0));
		}else{
			file_put_contents($urlefil, $dompdf->output());
		}	
	break;
	
	case'viewlistfile':
		$param=$_POST;
		$form="<fieldset>
		<legend>".$_SESSION['lang']['list']."</legend>
		<table class='sortable' cellspacing='1' border='0' width=100%>
			<thead>
			<tr class=rowheader>
				<td align='center'>".$_SESSION['lang']['nourut']."</td>
				<td align='center'>File Type</td>
				<td align='center'>Kriteria</td>
				<td align='center'>Filename</td>
				<td align='center'>Action</td>
			</tr>
			</thead>
			
		";
		$str="select * from ".$dbname.".listfileupload where notransaksi='".$bil."' ";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$form.= "<tr class=rowcontent >";
				$form.="<td style='text-align:center'>".$no."</td>";
				$form.="<td align='center'><img src=".$icon." class=zImgBtn></a></td>";
				$form.= "<td>".getcriterianame($bar['kriteriaefil'])."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download>".$bar['namafile']."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a></td>";
			$form.= "<tr>";
		}
		$form.="</table>
		</fieldset>";
		echo $form;
    break;  
	
	case'getpt':
		$str="select kodept,koderekanan from ".$dbname.".pmn_kontrakjual where nokontrak='".$kontrak."'";
		$res=fetchdata($str);
		foreach($res as $val){
            $optcusto=makeOption($dbname,"pmn_4customer","kodecustomer,namacustomer","kodecustomer='".$val['koderekanan']."'");
            $optpte=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$val['kodept']."'");
             $optpt.="<option value='".$val['kodept']."'>".$optpte[$val['kodept']]."</option>";
             $optcust.="<option value='".$val['koderekanan']."'>".$optcusto[$val['koderekanan']]."</option>";
            /*  $optpt=$val['kodept']."-". $optpte[$val['kodept']];
			  $optcust=$val['koderekanan']."-". $optcusto[$val['koderekanan']];
                $optpt1=$val['kodept'];
              $optcust1=$val['koderekanan'];*/

		}
		echo $optpt."##".$optcust;


    break;

    case'getnobl':
		echo $bil;
		if($bil==''){
			$sCek = "select kodebarang from " . $dbname . ".pmn_kontrakjual where nokontrak='" . $kontrak . "' ";
			$res = $owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while ($bar = $res->fetch()){
				$brg=$bar['kodebarang'];
			}
	 
			$nokontrak=$kontrak;
			$kodept=$pt;
			$tanggal=tanggalsystemn($tglbast);
			$table='pmn_billofloading';
			$jenis='BAST';
			$arrinisial=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
			$kodebarang=$brg;

			#generet nobl  
			$nobl = generatenobl();

			echo $nobl;
		}



    break;

    case'LoadData':
		$tab="";
        $limit = 10;
        $page = 0;
        if(isset($pages)){
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=@($page*$limit);
		$no=@(($page*$limit));
		
		$where="";
		if($kontrakcari!=''){
			$where.=" and nokontrak like '%".$kontrakcari."%'";
		}
		if($bilcari!=''){
			$where.=" and nobl like '%".$bilcari."%'";
		}
		if($tglcari!=''){
			$where.=" and tanggal='".tanggalsystemn($tglcari)."' ";
		}
		if($tglbastcari!=''){
			$where.=" and tanggalbast='".tanggalsystemn($tglbastcari)."' ";
		}

        $str = "select count(*) as jmlhrow from ".$dbname.".pmn_billofloading where 1=1 ".$where." order by `createtime` desc";

		$res=fetchdata($str);
		$jlhbrs = $res[0]['jmlhrow'];
		
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='13' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$no = 0;
			
			$str = "select * from ".$dbname.".pmn_billofloading where 1=1 ".$where." order by createtime desc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
				$optpt=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$val['pt']."'");
				$optcusto=makeOption($dbname,"pmn_4customer","kodecustomer,namacustomer","kodecustomer='".$val['kodecustomer']."'");
				$optstatus= array('1' =>'Aktif' ,'0' =>'Tidak Aktif' );
				
				$tab.="<tr class=rowcontent id='tr_".$no."'>
					<td align=center>".$no."</td>
					<td>".$optpt[$val['pt']]."</td>
					<td>".@$optcusto[$val['kodecustomer']]."</td>
                    <td>".$val['nokontrak']."</td>
					<td align=right>".number_format($val['kg'])."</td>
					<td>".$val['nobl']."</td>
                    <td style='min-width:80px;text-align:center'>".tanggalnormal($val['tanggal'])."</td>
                    <td style='min-width:80px;text-align:center'>".$val['createtime']."</td>";
				$tab.="<td style='text-align:center'>";	
				if($val['posting']==0){
					$tab.="
						<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$val['nokontrak']."','".$val['nobl']."','".$val['kodecustomer']."','".$val['pt']."');\">&nbsp;
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('".$val['nokontrak']."','".$val['nobl']."');\">&nbsp;
						<img src=images/".$_SESSION['theme']."/posting.png class=resicon  title='posting' onclick=\"posting('".$val['nobl']."','".$page."');\">";
                }else{
					$tab.="
						<img src=images/skyblue/posted.png class=resicon  title='posting'>";
				}
				 $tab.="&nbsp;<img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"viewlistfile(event,'".$val['nobl']."')\" src='images/upload-2-xxl.png'/>"; 
				 $tab.="&nbsp;<img src=images/pdf.jpg class=zImgBtn  caption='PDF'  title='Print PDF Nomor BAST : ".$val['nobl']."' onclick=\"pdf('".$val['nobl']."');\">";
				 $tab.="</td>";    
				
				
				$tab.="</tr>";
			}
			
			## PAGING
			$tab.=createpaging($jlhbrs,$limit,$page,'13','loadData','getPage');
			$tab.="</table>";
		}
		
		echo $tab;
	break;

    case'insert':
        
		if($kg == ''){
			exit("Warningsistem:Kg BAST kosong");
		}
		if($tgl == ''){
			exit("Warningsistem:Tanggal BL masih kosong");
		}
		if($tglbast == ''){
			exit("Warningsistem:Tanggal BAST masih kosong");
		}

        $createdtime=date("Y-m-d H:i:s");
        $sCek = "select nobl,createtime from " . $dbname . ".pmn_billofloading where nokontrak='" . $kontrak . "'  and nobl='" . $bil . "'";
		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
        if ($rCek < 1) {
            $sIns = "insert into " . $dbname . ".pmn_billofloading 
				(nokontrak, pt, kodecustomer, nobl, tanggal, kg,posting,close,createdby,createtime,jenis,tanggalbast,
				ffa,moisture,dirt,dobi,broken,mdani,impurities,kota,rpkgclaim) 
				values ('" . $kontrak . "','" . $pt . "','" . $cust . "','" . $bil . "','" . tanggalsystemn($tgl) . "','" . $kg . "','0','0','" . $_SESSION['standard']['userid'] . "','" . $createdtime . " ','BAST','" . tanggalsystemn($tglbast) . "',
				'".$ffa."','".$moisture."','".$dirt."','".$dobi."','".$broken."','".$mdani."','".$impurities."','".$kota."','".$rpkgclaim."')";            
            try{
				$owlPDO->exec($sIns); 
			}
			catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
			}
        }
        else {
            echo"Warningsistem:Data Already Entry";
            exit();
        }
	break;

    case'showData':
        $sql = "select * from " . $dbname . ".pmn_billofloading where nokontrak='" . $kontrak . "' and nobl='" . $bil . "'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
        $res = $query->fetch();
        echo $res['kodecustomer'] . "###" . tanggalnormal($res['tanggal']) . "###" . number_format($res['kg']). "###" . $res['pt']
		 . "###" . tanggalnormal($res['tanggalbast'])."###".$res['kota']."###".$res['ffa']."###".$res['moisture']."###".$res['dirt']
		 ."###".$res['dobi']."###".$res['broken']."###".$res['impurities']."###".$res['mdani']."###".$res['rpkgclaim'];

        break;
    case'update':
       
        $str = "update " . $dbname . ".pmn_billofloading set  
			tanggal='" .tanggalsystemn($tgl). "',tanggalbast='" .tanggalsystemn($tglbast). "', nokontrak='" . $kontrak . "', 
			pt='" . $pt . "', kodecustomer='" . $cust . "', kg='" . $kg . "',
			ffa='".$ffa."',moisture='".$moisture."',dirt='".$dirt."',dobi='".$dobi."',broken='".$broken."',mdani='".$mdani."',impurities='".$impurities."',
			kota='".$kota."',rpkgclaim='".$rpkgclaim."',
			updateby='".$_SESSION['standard']['userid']."' where  nokontrak='" . $kontrak . "' and nobl='" . $bil . "'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
		}

	break;

    case'delData':
        $sDel = "delete from " . $dbname . ".pmn_billofloading where  nokontrak='" . $kontrak . "' and nobl='" . $bil . "'";
        try{
			$owlPDO->exec($sDel); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
		}
		
		$str="select * from ".$dbname.".listfileupload where notransaksi='".$bil."'"; //exit('error'.$str);
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $pathx = $path.$bar['namafile'];
            unlink($pathx);
        }
		
		$str="delete from ".$dbname.".listfileupload where notransaksi='".$bil."'";
        try{
            $owlPDO->exec($str);
        }catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
		
	break;
	
	case'submitfile':
		$param=$_POST;
        $tgl = date("YmdHis");
        $his = date("His");
        $nmTemp=str_replace('-','',str_replace('/','',$param['notransaksi']));
        // echo"<pre>";
        // print_r($_FILES['file']);
        // echo"</pre>";
        // exit('error');
		if($param['fileupload']!=''){
			if($_FILES['file']['error']==0){    
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $kriteriaefil."_".$nmTemp."_".$his."".$filetype;
				// exit("Error:".$filename);
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
				// listfile_keu_kasbank
				// listfileupload
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfileupload values ('','".$bil."','".$filename."','".$filetype."','".$kriteriaefil."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
					// exit("Error:$str");
					try{
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				}else{
					exit("Warning : Format file upload tidak boleh ".$filetype);
				}
			}
		}
    break;
	
	
	case 'deletefile':
        $param=$_POST;
        $str="delete from ".$dbname.".listfileupload where notransaksi='".$param['bil']."' and namafile='".$param['namafile']."'"; 
		// exit('error'.$str);
        try{
            $owlPDO->exec($str);
            $pathx = $path.str_replace('/','',$param['namafile']);
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
	break;
	
	case'loadfiles':
		$param=$_POST;
		$form='';
		$str="select * from ".$dbname.".listfileupload where notransaksi='".$bil."' ";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$form.= "<tr class=rowcontent >";
				$form.="<td style='text-align:center'>".$no."</td>";
				$form.="<td align='center'><img src=".$icon." class=zImgBtn></a></td>";
				$form.= "<td>".getcriterianame($bar['kriteriaefil'])."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download>".$bar['namafile']."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a>&nbsp<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletefile('".$bar['notransaksi']."','".$bar['namafile']."');\" ></td>";
			$form.= "<tr>";
		}
		echo $form;
    break; 

	case'posting':
		$theme=$_SESSION['theme'];
		if($theme=='skyblue' || $theme==''){
		  $men='menu.css';
		  $gen='generic.css';
		}else if($theme=='red'){
		  $men='menuRed.css';
		  $gen='genericRed.css';  
		}else{
		  $men='menuGray.css';
		  $gen='genericGray.css';  
		}
		
		$status= array(''=>$_SESSION['lang']['pilihdata'],'0' =>'Posting' ,'1' =>'Posting & Close Contract' );
		foreach ($status as $sts=>$val) {
			@$optstatus.="<option value='".$sts."'>".$val."</option>";
		}
		
		$tab.="<link rel=stylesheet type=text/css href=style/".$gen.">";
		$tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>";
		$tab.="<tr class=rowcontent>";
			$tab.="<td>".$_SESSION['lang']['bast']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><input disabled type=text class=myinputtext  style='width:200px;' id=nobast name=nobast value='".$nobast."' onkeypress=return tanpa_kutip(event) style=width:145px; maxlength=100 /></td>";
		$tab.="</tr>";
		$tab.="<tr class=rowcontent>";
			$tab.="<td>".$_SESSION['lang']['tipetransaksi']."</td>";
			$tab.="<td>:</td>";
			$tab.="<td><select id='tipe' name='tipe' style='width:200px;'>".$optstatus."</select></td>";
		$tab.="</tr>";
		$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=3><button class=mybutton onclick=saveposting('".$page."')>".$_SESSION['lang']['save']."</button></td>";
		$tab.="</tr>";
		$tab.="</table>";
		// exit("Error:$tab");
		echo $tab;
	break;
	
	
	 case'saveposting':
		if($tipe==''){
			exit("Warningsistem:Tipe Posting tidak boleh kosong");
		}
	 
		 try {
			$owlPDO->beginTransaction();
			switch($tipe){
				case'0':
					$str = "update " . $dbname . ".pmn_billofloading set  posting='1',close='0' where  nobl='" . $nobast . "'";
					$owlPDO->exec($str);
				break;
				case'1':
					$str = "update " . $dbname . ".pmn_billofloading set  posting='1',close='1' where  nobl='" . $nobast . "'";
					$owlPDO->exec($str);
				break;
			}
		$owlPDO->commit();
				
		} catch(PDOException $e) {
			$owlPDO->rollback();
			echo "Warningsistem: Gagal melakukan posting data \n" . addslashes($e->getMessage());
		}
	 break;
	
	
     case'savepostingJURNAL':
		#= nanti akan jadi jurnal disini

		if($tipe==''){
			exit("Warningsistem:Tipe Posting tidak boleh kosong");
		}
		
		
		$str="select * from ".$dbname.".pmn_billofloading where nobl='".$nobast."' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$nokontrak=$bar['nokontrak'];
			$kodept=$bar['pt'];
			$kodecustomer=$bar['kodecustomer'];
			$tanggal=$bar['tanggal'];
			$periodejurnal=substr($bar['tanggal'],0,7);
			$kgbast=$bar['kg'];
			$closebast=$bar['close'];
			$rpkgclaim=$bar['rpkgclaim'];
		}
		
		// exit("Error:$tanggal");
		
		#= ambil unit RO, karna jurnal sales di round
		$str="select * from ".$dbname.".organisasi where induk='".$kodept."' and tipe='KANWIL' ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$kodeunit=$bar['kodeorganisasi'];
		}
		
		
		#= ambil data dari kontrakjual
		$str="select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$nokontrak."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$kodebarang=$bar['kodebarang'];
			$hargasatuan=$bar['hargasatuan'];
			$persenppn=$bar['persenppn']; // sudah mewakili berikat / tidak , jika ppn 0 => berikat; jika 10 maka tidak berikat
		}
		
		#= ambil COA
		$str="select * from ".$dbname.".keu_5jenispenagihandt where kodebarang='".$kodebarang."' and kodejenis='CIPP'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$noakunsales=$bar['noakunsales'];
			$noakunuangmuka=$bar['noakunuangmuka'];
			$noakunppn=$bar['noakunppn'];
			$noakunpiutang=$bar['noakunpiutang'];
			$noakunclaim=$bar['noakunclaim'];
		}
		
		$kodejurnal='SLE';
		
		#= bentuk nilai
		#= uang muka / sales = kg * hargasatuan
		#= untuk tipe posting
		
		
		// exit("Error:$tipe");
		
		
		
		#= bentuk jurnal disini
		
		try {
			$owlPDO->beginTransaction();
			
			#= code disini
			$query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',
			"kodekelompok='".$kodejurnal."' and kodeunit='".$kodeunit."' and periode='".$periodejurnal."'");
			$tmpKonter = fetchData($query);
			$konter = addZero($tmpKonter[0]['nokounter']+1,3);
			# Prep No Jurnal
			$nojurnal = str_replace('-','',$tanggal)."/".$kodeunit."/".$kodejurnal."/".$konter;
			$noUrut=0;
			
			$dataRes['header'][] = array(
				'nojurnal'=>$nojurnal,
				'kodejurnal'=>$kodejurnal,
				'tanggal'=>$tanggal,
				'tanggalentry'=>date('Ymd'),
				'posting'=>'0',
				'totaldebet'=>'0',
				'totalkredit'=>'0',
				'amountkoreksi'=>'0',
				'noreferensi'=>$nobast,
				'autojurnal'=>'1',
				'matauang'=>'IDR',
				'kurs'=>'1',
				'revisi'=>'0'
			);
			
			switch($tipe){
				
				case'0':
					$str = "update " . $dbname . ".pmn_billofloading set  posting='1',close='0' where  nobl='" . $nobast . "'";
					$owlPDO->exec($str);
				
					$nilaiuangmuka=$nilaisales=$kgbast*$hargasatuan;
					
					#= debet uang muka
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$tanggal,
						'nourut'=>$noUrut,
						'noakun'=>$noakunuangmuka,
						'keterangan'=>'Berita Acara Serah Terima '.$nobast.', No. Kontrak : '.$nokontrak,
						'jumlah'=>$nilaiuangmuka,
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$kodeunit,
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>$kodebarang,
						'nik'=>'',
						'kodecustomer'=>$kodecustomer,
						'kodesupplier'=>'',
						'noreferensi'=>$nobast,
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$nokontrak,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' =>''
					);
					
					#= kredit sales
					#= debet uang muka
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$tanggal,
						'nourut'=>$noUrut,
						'noakun'=>$noakunsales,
						'keterangan'=>'Berita Acara Serah Terima '.$nobast.', No. Kontrak : '.$nokontrak,
						'jumlah'=>$nilaisales*-1,
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$kodeunit,
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>$kodebarang,
						'nik'=>'',
						'kodecustomer'=>$kodecustomer,
						'kodesupplier'=>'',
						'noreferensi'=>$nobast,
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$nokontrak,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' =>''
					);
					
				break;
				
				case'1':
				
					$str = "update " . $dbname . ".pmn_billofloading set  posting='1',close='1' where  nobl='" . $nobast . "'";
					$owlPDO->exec($str);
				
					// exit("Warningsistem:A");
					
					#= cari nilai penjualan
					$nilaisales=$kgbast*$hargasatuan;
					$nilaiclaim=$kgbast*$rpkgclaim;
					
					#= cari sisa uang muka, dengan cara sum debet-kredit  jurnal where nodok=param nomor kontrak
					$str="select  sum(jumlah) as jumlah from ".$dbname.".keu_jurnaldt_vw where nodok='".$nokontrak."' and noakun='".$noakunuangmuka."'";
					$res=fetchdata($str);
					foreach($res as $bar){
						$nilaiuangmuka=abs($bar['jumlah']); //karna nilai minus
					}

					$nilaisisa=$nilaisales-$nilaiuangmuka-$nilaiclaim;
					$nilaippn=0;
					if($persenppn>0){
						$nilaippn=$nilaisisa*0.1;
					}
					$nilaipiutang=$nilaisisa+$nilaippn;
					
					
					#= debet piutang
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$tanggal,
						'nourut'=>$noUrut,
						'noakun'=>$noakunpiutang,
						'keterangan'=>'Berita Acara Serah Terima '.$nobast.', No. Kontrak : '.$nokontrak,
						'jumlah'=>$nilaipiutang,
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$kodeunit,
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>$kodebarang,
						'nik'=>'',
						'kodecustomer'=>$kodecustomer,
						'kodesupplier'=>'',
						'noreferensi'=>$nobast,
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$nokontrak,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' =>''
					);
					
					#= debet uangmuka
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$tanggal,
						'nourut'=>$noUrut,
						'noakun'=>$noakunuangmuka,
						'keterangan'=>'Berita Acara Serah Terima '.$nobast.', No. Kontrak : '.$nokontrak,
						'jumlah'=>$nilaiuangmuka,
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$kodeunit,
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>$kodebarang,
						'nik'=>'',
						'kodecustomer'=>$kodecustomer,
						'kodesupplier'=>'',
						'noreferensi'=>$nobast,
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$nokontrak,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' =>''
					);
					
					#= jika ada claim
					#= posisi debet
					if($nilaiclaim>0){
					$noUrut++;
						$dataRes['detail'][] = array(
							'nojurnal'=>$nojurnal,
							'tanggal'=>$tanggal,
							'nourut'=>$noUrut,
							'noakun'=>$noakunclaim,
							'keterangan'=>'Berita Acara Serah Terima '.$nobast.', No. Kontrak : '.$nokontrak,
							'jumlah'=>$nilaiclaim,
							'matauang'=>'IDR',
							'kurs'=>'1',
							'kodeorg'=>$kodeunit,
							'kodekegiatan'=>'',
							'kodeasset'=>'',
							'kodebarang'=>$kodebarang,
							'nik'=>'',
							'kodecustomer'=>$kodecustomer,
							'kodesupplier'=>'',
							'noreferensi'=>$nobast,
							'noaruskas'=>'',
							'kodevhc'=>'',
							'nodok'=>$nokontrak,
							'kodeblok'=>'',
							'revisi'=>'0',
							'kodesegment' =>''
						);
					}
					
					#= kredit sales
					$noUrut++;
					$dataRes['detail'][] = array(
						'nojurnal'=>$nojurnal,
						'tanggal'=>$tanggal,
						'nourut'=>$noUrut,
						'noakun'=>$noakunsales,
						'keterangan'=>'Berita Acara Serah Terima '.$nobast.', No. Kontrak : '.$nokontrak,
						'jumlah'=>$nilaisales*-1,
						'matauang'=>'IDR',
						'kurs'=>'1',
						'kodeorg'=>$kodeunit,
						'kodekegiatan'=>'',
						'kodeasset'=>'',
						'kodebarang'=>$kodebarang,
						'nik'=>'',
						'kodecustomer'=>$kodecustomer,
						'kodesupplier'=>'',
						'noreferensi'=>$nobast,
						'noaruskas'=>'',
						'kodevhc'=>'',
						'nodok'=>$nokontrak,
						'kodeblok'=>'',
						'revisi'=>'0',
						'kodesegment' =>''
					);
					
					#= kredit ppn keluaran
					if($nilaippn>0){
						$noUrut++;
						$dataRes['detail'][] = array(
							'nojurnal'=>$nojurnal,
							'tanggal'=>$tanggal,
							'nourut'=>$noUrut,
							'noakun'=>$noakunppn,
							'keterangan'=>'PPN Keluaran Berita Acara Serah Terima '.$nobast.', No. Kontrak : '.$nokontrak,
							'jumlah'=>$nilaippn*-1,
							'matauang'=>'IDR',
							'kurs'=>'1',
							'kodeorg'=>$kodeunit,
							'kodekegiatan'=>'',
							'kodeasset'=>'',
							'kodebarang'=>$kodebarang,
							'nik'=>'',
							'kodecustomer'=>$kodecustomer,
							'kodesupplier'=>'',
							'noreferensi'=>$nobast,
							'noaruskas'=>'',
							'kodevhc'=>'',
							'nodok'=>$nokontrak,
							'kodeblok'=>'',
							'revisi'=>'0',
							'kodesegment' =>''
						);
					}
					
					
					
					
				break;				
			}
			
			#= update counter jurnal
			$str="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where 
				kodeunit='".$kodeunit."' and kodekelompok='".$kodejurnal."' and periode='".$periodejurnal."' ";	
			$owlPDO->exec($str);
			
			$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
			$owlPDO->exec($queryH);

			$queryD = insertQuery($dbname,'keu_jurnaldt',$dataRes['detail']);
			$owlPDO->exec($queryD);
			
			$owlPDO->commit();
			
		} catch(PDOException $e) {
		
		$owlPDO->rollback();
			echo "Warningsistem: Gagal melakukan posting data \n" . addslashes($e->getMessage());

		}

        
    break;
    default:
        break;
}
?>