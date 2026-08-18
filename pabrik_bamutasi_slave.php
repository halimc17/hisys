<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$method = checkPostGet('method','');
$notransaksi = checkPostGet('notransaksi','');
$tanggal = tanggalsystemn(checkPostGet('tanggal',''));
$tipe = checkPostGet('tipe','');
$transportasi = checkPostGet('transportasi','');
$transportir = checkPostGet('transportir','');
$namakapal = checkPostGet('namakapal','');
$namaponton = checkPostGet('namaponton','');
$keteranganht = checkPostGet('keteranganht','');
$tanggalberangkat = tanggalsystemn(checkPostGet('tanggalberangkat',''));
	$jmberangkat = checkPostGet('jmberangkat','');
	$mnberangkat = checkPostGet('mnberangkat','');
	$waktuberangkat=$tanggalberangkat." ".$jmberangkat.":".$mnberangkat.":59";
$nosip = checkPostGet('nosip','');
$nospk = checkPostGet('nospk','');
$kodept = checkPostGet('kodept','');
	
$kodebarang = checkPostGet('kodebarang','');
$unit = checkPostGet('unit','');
$kodetangki = checkPostGet('kodetangki','');
$keterangandt = checkPostGet('keterangandt','');
$jumlah = checkPostGet('jumlah','');
$tanggaltiba = tanggalsystemn(checkPostGet('tanggaltiba',''));
	$jmtiba = checkPostGet('jmtiba','');
	$mntiba = checkPostGet('mntiba','');
	$waktutiba=$tanggaltiba." ".$jmtiba.":".$mntiba.":59";
$tanggalbongkar1 = tanggalsystemn(checkPostGet('tanggalbongkar1',''));
	$jmbongkar1 = checkPostGet('jmbongkar1','');
	$mnbongkar1 = checkPostGet('mnbongkar1','');
	$waktubongkar1=$tanggalbongkar1." ".$jmbongkar1.":".$mnbongkar1.":59";
$tanggalbongkar2 = tanggalsystemn(checkPostGet('tanggalbongkar2',''));
	$jmbongkar2 = checkPostGet('jmbongkar2','');
	$mnbongkar2 = checkPostGet('mnbongkar2','');
	$waktubongkar2=$tanggalbongkar2." ".$jmbongkar2.":".$mnbongkar2.":59";

$noreferensi = checkPostGet('noreferensi','');
$unitreferensi = checkPostGet('unitreferensi','');

$suhu1 = checkPostGet('suhu1','');
$tinggi1 = checkPostGet('tinggi1','');
$jumlah1 = checkPostGet('jumlah1','');
$ffa1 = checkPostGet('ffa1','');
$moisture1 = checkPostGet('moisture1','');
$dirt1 = checkPostGet('dirt1','');
$dobi1 = checkPostGet('dobi1','');
$broken1 = checkPostGet('broken1','');
$suhu2 = checkPostGet('suhu2','');
$tinggi2 = checkPostGet('tinggi2','');
$jumlah2 = checkPostGet('jumlah2','');
$ffa2 = checkPostGet('ffa2','');
$moisture2 = checkPostGet('moisture2','');
$dirt2 = checkPostGet('dirt2','');
$dobi2 = checkPostGet('dobi2','');
$broken2 = checkPostGet('broken2','');



$tanggalmulai=checkPostGet('tanggalmulai','');
$tanggalselesai=checkPostGet('tanggalselesai','');

if($tanggalmulai==''){
	$tanggalmulai='';
}else{
	$tanggalmulai = tanggalsystemn(checkPostGet('tanggalmulai',''));	
}

if($tanggalselesai==''){
	$tanggalselesai='';
}else{
	$tanggalselesai=tanggalsystemn(checkPostGet('tanggalselesai',''));
}


$table='pabrik_bamutasi';

$optbuyer=$optbarang=$opttipe=$optunit=$opttangki="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";



$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$arrinisial=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
$kdpt=makeOption($dbname,'organisasi','kodeorganisasi,induk');
$namaorganisasi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PABRIK'");
$nmcustsomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmkapalponton=makeOption($dbname,'pmn_5kapalponton','kode,nama');
$nmfranco=makeOption($dbname,'pmn_5franco','id_franco,franco_name');

// exit("Error:".$unitreferensi);
switch ($method) {
	
	
	case'pdf':
		
	
		$str = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
		
		
		$str = "select sum(jumlah) as jumlah,notransaksi,unit,kodebarang,tanggal,posting,transportasi,
				namakapal,namaponton,keteranganht,tipe,noreferensi,
				tanggalbongkar1,tanggalbongkar2,tanggalberangkat,tanggaltiba,kodept,unitreferensi
				from ".$dbname.".".$table."  
			where notransaksi='".$notransaksi."'   group by notransaksi";
			// echo $str;exit();
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$unit=$bar['unit'];
			$unitreferensi=$bar['unitreferensi'];
			$kodebarang=$bar['kodebarang'];
			$tanggal=$bar['tanggal'];
			$namakapal=$nmkapalponton[$bar['namakapal']];
			$namaponton=$nmkapalponton[$bar['namaponton']];
			$transportasi=$bar['transportasi'];
			$tanggalbongkar2=$bar['tanggalbongkar2'];
			$tanggalbongkar1=$bar['tanggalbongkar1'];
			$tanggalberangkat=$bar['tanggalberangkat'];
			$tanggaltiba=$bar['tanggaltiba'];
			$tipe=$bar['tipe'];
			$totalqty=$bar['jumlah'];
			$keteranganht=$bar['keteranganht'];
			$noreferensi=$bar['noreferensi'];
			$kodept=$bar['kodept'];
			
		#notransaksi noreferensi	
		
		// if($kodebarang=='40000002'){
		if($transportasi=='DARAT'){
			#= untuk kernel pakai data timbangan
			#= algoritma, ambil tiket2 yang masuk di ibw / penerima, dihari tersebut, 
			#= query tiket terima sebagai referensi, seperti di hpp
			/*
			$str="select notransaksi,kodebarang,norefrensi from ".$dbname.".pabrik_timbangan where  tanggal like '".$tanggal."%' and millcode='".$unit."' and kodebarang in ('40000002') and pengirim='".$unitreferensi."'";
			*/
			$str="select notransaksi,kodebarang,norefrensi from ".$dbname.".pabrik_timbangan where  tanggal>='".$tanggalbongkar1."' and tanggal<='".$tanggalbongkar2."' and millcode='".$unit."' and kodebarang in ('40000002') and pengirim='".$unitreferensi."'";
				// echo $str;exit();
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$arrdata[$bar['norefrensi']]=$bar['norefrensi'];
			}
			 // echo $str;exit();
			@$carrdata=count($arrdata);
			$str="select sum(beratbersih) as kg from ".$dbname.".pabrik_timbangan where  notransaksi in ('".implode("','",$arrdata)."')";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$totalqtyreferensi=$bar['kg'];
			}
		}else{
			$str = "select sum(jumlah) as jumlah from ".$dbname.".".$table." where notransaksi='".$noreferensi."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
			$totalqtyreferensi=$bar['jumlah'];		
		}
	
			/*
			$kodetangki=$bar['kodetangki'];
			$nokontrak=$bar['nokontrak'];
			$suhu1=$bar['suhu1'];
			$tinggi1=$bar['tinggi1'];
			$jumlah1=$bar['jumlah1'];
			$jumlah=$bar['jumlah'];
			$ffa1=$bar['ffa1'];
			$moisture1=$bar['moisture1'];
			$dirt1=$bar['dirt1'];
			$dobi1=$bar['dobi1'];
			$broken1=$bar['broken1'];
			$keterangan1=$bar['keterangan1'];
			$keterangan2=$bar['keterangan2'];
			$suhu2=$bar['suhu2'];
			$tinggi2=$bar['tinggi2'];
			$jumlah2=$bar['jumlah2'];
			$ffa2=$bar['ffa2'];
			$moisture2=$bar['moisture2'];
			$dirt2=$bar['dirt2'];
			$dobi2=$bar['dobi2'];
			$broken2=$bar['broken2'];
			$keterangan=$bar['keterangan'];
			$keterangan2=$bar['keterangan2'];
			$noreferensi=$bar['noreferensi'];
			*/
			// if($tipe=='OUT'){
				// $qty=$bar['jumlah1']-$bar['jumlah2'];
			// }else{
				// $qty=$bar['jumlah2']-$bar['jumlah1'];
			// }
						

			
			// $keterangan=$keterangan1." ".$keterangan2;
			
						// // print_r($keterangan1);
						// exit("Error:$keterangan");

		$str = "select * from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$namatangki[$bar['kodetangki']]=$bar['keterangan'];
		}
	
		$tab="<style>
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
				div.page_break {
					page-break-before: always;
				}
				
				
				
			</style>";
			
		
			/*
			background-color: #03a9f4;
					color: white;
					text-align: center;
					line-height: 35px;
			*/
		$cellpadding=1.5;
		
		
			
		#notransaksi noreferensi	
		$str = "select * from ".$dbname.".".$table." where notransaksi='".$noreferensi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumlahreferensi=$bar['jumlah'];	
		
		if($tipe=='IN'){
			$judul="BERITA ACARA PENERIMAAN ".$arrinisial[$kodebarang]." ";
		}else{
			$judul="BERITA ACARA PENGIRIMAN  ".$arrinisial[$kodebarang]." ";
		}
		$tab.="<div style='page-break-after: always;'>";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0>";
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight:bold;text-align:center;font-size:20px'><b><u>".$judul."</u></b></td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight;text-align:center;font-size:14px'>".$nmpt[$kodept]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight;text-align:center;font-size:14px'>No : ".$notransaksi."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
	
		$tab.="<br>";
		$tab.="<br>";
		
		if($namaponton!='' and $namakapal!=''){
			$kapalponton=$namakapal." / ".$namaponton;
		}else{
			$kapalponton=$namakapal." ".$namaponton;
		}
		
		if($transportasi=='DARAT'){
			$kapalponton=" ".$transportasi." VIA TRUCK";
		}
		
		if($kodebarang=='40000001'){
			$komoditi='CPO';
		}
		if($kodebarang=='40000002'){
			$komoditi='KER';
		}
		
		
		
		
	

		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['namakapal']."/".$_SESSION['lang']['namaponton']."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$kapalponton."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['tanggaltiba']."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-bottom:0.5px solid #000000;border-right:0.5px solid #000000;'>: ".tglnmbln(substr($tanggaltiba,0,10),'','')." Jam : ".substr($tanggaltiba,11,10)."</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['nosipb']."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$nosip."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['tanggalmulai']." ".$_SESSION['lang']['bongkarmuat']."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".tglnmbln(substr($tanggalbongkar1,0,10),'','')." Jam : ".substr($tanggalbongkar1,11,10)."</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['tanggalberangkat']."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".tglnmbln(substr($tanggalberangkat,0,10),'','')."  Jam : ".substr($tanggalberangkat,11,10)."</td>"; 
								$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['tanggalselesai']." ".$_SESSION['lang']['bongkarmuat']."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".tglnmbln(substr($tanggalbongkar2,0,10),'','')." Jam : ".substr($tanggalbongkar2,11,10)."</td>"; 
			$tab.="</tr>";	
		$tab.="</table>";
		
		
		$tab.="<br>";
			
		#= data 
		$keterangandt=array();
		$str = "select * from ".$dbname.".".$table."  
			where notransaksi='".$notransaksi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$sawal[$bar['kodetangki']]=$bar['jumlah1'];
			$salak[$bar['kodetangki']]=$bar['jumlah2'];
			$mutasi[$bar['kodetangki']]=$bar['jumlah'];
			$keterangandt[$bar['kodetangki']]=$bar['keterangandt'];
		}
	
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0>";
			$tab.="<tr>";
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' colspan=2>Data Stok ".$komoditi."</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Qty</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Keterangan</td>"; 
			$tab.="</tr>";
		$str = "select * from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."' and komoditi='".$komoditi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' rowspan=3>Data Stok ".$namatangki[$bar['kodetangki']]."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Stock Awal</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($sawal[$bar['kodetangki']])."</td>"; 
				$tab.="<td valign=top style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;' rowspan=3>".$keterangandt[$bar['kodetangki']]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Stock Akhir</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($salak[$bar['kodetangki']])."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Jumlah</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($mutasi[$bar['kodetangki']])."</td>"; 
			$tab.="</tr>";
		}
		
		
		if($tipe=='IN'){
			
			$nilaiselisih=($totalqty-$totalqtyreferensi)/$totalqtyreferensi;
			
			// $keteranganselisih=
			
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' colspan=2>Total Penerimaan</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($totalqty)."</td>"; 
				$tab.="<td valign=bottom style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;' rowspan=3>Selisih sejumlah ".number_format($nilaiselisih*100,2)." %</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' colspan=2>Qty Delivery dari PMKS, No: ".$noreferensi."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($totalqtyreferensi)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' colspan=2>Selisih</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($totalqty-$totalqtyreferensi)."</td>"; 
			$tab.="</tr>";
		}else{
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' colspan=2>Total Jumlah</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($totalqty)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
			$tab.="</tr>";
		}
		
		
		$tab.="</table>";
		
		
		
		
		
		
		$tab.="<br>";
		
		$tab.="<table border=0 width:100%>";
		$tab.="<tr>";
			$tab.="<td style='width:75px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;vertical-align:top'>Keterangan</td>"; 
			$tab.="<td style='width:600px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>".$keteranganht."</td>"; 
		$tab.="</tr>";
		$tab.="<tr>";
			$tab.="<td style='width:75px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;vertical-align:top'>&nbsp;</td>"; 
			$tab.="<td style='width:600px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
		$tab.="</tr>";
		$tab.="</table>";
	
		$tab.="<br>";
	
		
		$cellpadding=2;
		
		$cellpadding=0.5;	
		
		
		
			$cellpadding=0.5;	
			$tab.="<table style='font-size:12px;width:100%' cellpadding=".$cellpadding.">";
				$tab.="<tr>";
					$tab.="<td style='width:100px' align=center>Acknowleged</td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td style='width:100px' align=center>Checked</td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td style='width:100px' align=center>Prepared</td>"; 
				$tab.="</tr>";
				
				for($i=1;$i<5;$i++){
					$tab.="<tr>";
					$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
				$tab.="</tr>";
				}
			
				$tab.="<tr>";
					$tab.="<td style='border-bottom:0.5px solid #000000' align=center><b></b></td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td style='border-bottom:0.5px solid #000000' align=center><b></b></td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td style='border-bottom:0.5px solid #000000' align=center><b></b></td>"; 
				$tab.="</tr>";
				$tab.="<tr>";
					$tab.="<td align=center>Manager ".ucwords(strtolower($namaorganisasi[$unit]))."</td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td align=center>".ucwords(strtolower($namaorganisasi[$unit]))." Senior BLK Supervisor</td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td align=center>".ucwords(strtolower($namaorganisasi[$unit]))." Supervisor</td>"; 
				$tab.="</tr>";
			$tab.="</table>";
		
		
		
		/*
		$tab.="<table style='font-size:12px;width:100%' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td style='width:150px' align=center>Acknowleged</td>"; 
				$tab.="<td style='width:200px' align=center>&nbsp;</td>"; 
				$tab.="<td style='width:150px' align=center>Prepared</td>"; 
			$tab.="</tr>";
			
			for($i=1;$i<5;$i++){
				$tab.="<tr>";
				$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
			$tab.="</tr>";
			}
		
			$tab.="<tr>";
				$tab.="<td style='border-bottom:0.5px solid #000000' align=center><b></b></td>"; 
				$tab.="<td align=center>&nbsp;</td>"; 
				$tab.="<td style='border-bottom:0.5px solid #000000' align=center><b></b></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td align=center>Manager ".ucwords(strtolower($namaorganisasi[$unit]))."</td>"; 
				$tab.="<td align=center>&nbsp;</td>"; 
				$tab.="<td align=center>".ucwords(strtolower($namaorganisasi[$unit]))." Supervisor</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		
		
		

		
		$tab.="<footer>";
			$cellpadding=1;	
			$tab.="<table style='font-size:12px' border=0 cellpadding=".$cellpadding.">";	
				$tab.="<tr>";
					$tab.="<td align=left style='width:700px;border-bottom:0.5px solid #000000'><b>".$namapt."</b></td>"; 
				$tab.="</tr>";
				$tab.="<tr>";
					$tab.="<td align=left><b>".$kotaro." Office</b> : ".$alamatro." Tel : ".$telpro." Fax : ".$faxro."</td>"; 
				$tab.="</tr>";
			$tab.="</table>";
		$tab.="</footer>";	
		*/
		$tab.="</div>";
		
		
		if($kodebarang=='40000001'){
		
		if($tipe=='IN'){
			$judul="BERITA ACARA PENERIMAAN ".$komoditi."";
		}else{
			$judul="BERITA ACARA PENGIRIMAN  ".$komoditi." ";
		}
	
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0>";
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight:bold;text-align:center;font-size:20px'><b><u>".$judul."</u></b></td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		$tab.="<br>";
	
	
		#= if kodebarang 1
		
			$tab.="<br>";
			$str = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."'";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$nourut++;
				$tab.="<br>";
				$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
				
					$tab.="<tr>";
						$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>KONDISI TANGKI</td>"; 
						$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>NOMOR TANGKI</td>"; 
						$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>SOUNDING (CM)</td>"; 
						$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>TABLE MEJA UKUR (CM)</td>"; 
						$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>SOUNDING KOREKSI(CM)</td>"; 
						$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>VOLUME PERHITUNGAN</td>"; 
						$tab.="<td  colspan=2 style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>KOREKSI FAKTOR SUHU (C-DEG)</td>"; 
						// $tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>VOLUME KOREKSI (CM)</td>"; 
						$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>SUHU (C-DEG)</td>"; 
						$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>BERAT JENIS</td>"; 
						$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>STOCK AKHIR (KG)</td>"; 
					$tab.="</tr>";
			
				

					$param['kodeorg']=$bar['unit'];
					$param['kodetangki']=$bar['kodetangki'];
					$param['tinggi']=$bar['tinggi1'];
					$param['suhu']=$bar['suhu1'];
					
					$str1="select nilai from ".$dbname.".pabrik_5mejaukur where 
							kodeorg='".$param['kodeorg']."' and tangki='".$param['kodetangki']."'";
					$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
					$res1->setFetchMode(PDO::FETCH_ASSOC);
					$bar1=$res1->fetch();
						$mejaukur=$bar1['nilai'];
					
					$param['tinggi']=$param['tinggi']+$mejaukur;
					$ting=explode(".",$param['tinggi']);
					$suhu=explode(".",$param['suhu']);
					$sSh="select berat_jenis,varian from ".$dbname.".pabrik_5suhu where millcode='".$param['kodeorg']."' 
						  and kodetangki='".$param['kodetangki']."' and suhu='".$suhu[0]."'";
					//exit('warning'.$sSh);
					$qSh=$owlPDO->query($sSh) or die(print " Gagal: ".PDOException::getMessage());
					$qSh->setFetchMode(PDO::FETCH_ASSOC);
					$rSh=$qSh->fetch();
					
					$sTng="select volume,beda from ".$dbname.".pabrik_5tinggitangki where millcode='".$param['kodeorg']."' 
						  and kodetangki='".$param['kodetangki']."' and tinggi='".$ting[0]."'";
					$qTng=$owlPDO->query($sTng) or die(print " Gagal: ".PDOException::getMessage());
					$qTng->setFetchMode(PDO::FETCH_ASSOC);
					$rTng=$qTng->fetch();
					
					$sSuhuKalibrasi="select suhu_kalibrasi from ".$dbname.".pabrik_5standardsuhu_kalibrasi 
									where millcode='".$param['kodeorg']."' and kodetangki='".$param['kodetangki']."' 
									order by periode desc limit 1";
					$qSuhuKalibrasi=$owlPDO->query($sSuhuKalibrasi) or die(print " Gagal: ".PDOException::getMessage());
					$qSuhuKalibrasi->setFetchMode(PDO::FETCH_ASSOC);
					$rSuhuKalibrasi=$qSuhuKalibrasi->fetch();
					
					
					#== ambil data faktor koreksi
					$strfk="select nilai,nilaiangka from ".$dbname.".pabrik_5faktorkoreksi where millcode='".$param['kodeorg']."'  and kodetangki='".$param['kodetangki']."'  ";
					$resfk=$owlPDO->query($strfk) or die(print " Gagal: ".PDOException::getMessage());
					$resfk->setFetchMode(PDO::FETCH_ASSOC);
					$barfk=$resfk->fetch();	
					$nilaikoreksi=$barfk['nilai'];
					
					@$volTing=$rTng['volume']+round((floatval("0.".$ting[1])*$rTng['beda']));
					$volTangki=$volTing*$nilaikoreksi;
					$volTangkiAll=round($volTangki*$rSh['berat_jenis']);//
					
					
					$tab.="<tr>";
						$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Sebelum</td>"; 
						$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$bar['kodetangki']."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($bar['tinggi1'],2)."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($mejaukur,2)."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($param['tinggi'],2)."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($volTing)."</td>"; 
						$tab.="<td colspan=2 style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$nilaikoreksi."</td>"; 
						// $tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($volTangki)."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($param['suhu'])."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($rSh['berat_jenis'],4)."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($bar['jumlah1'])."</td>"; 
					$tab.="</tr>";	
				
				
				
					$param['kodeorg']=$bar['unit'];
					$param['kodetangki']=$bar['kodetangki'];
					$param['tinggi']=$bar['tinggi2'];
					$param['suhu']=$bar['suhu2'];

					$str1="select nilai from ".$dbname.".pabrik_5mejaukur where 
							kodeorg='".$param['kodeorg']."' and tangki='".$param['kodetangki']."'";
					$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
					$res1->setFetchMode(PDO::FETCH_ASSOC);
					$bar1=$res1->fetch();
						$mejaukur=$bar1['nilai'];
					
					$param['tinggi']=$param['tinggi']+$mejaukur;
					$ting=explode(".",$param['tinggi']);
					$suhu=explode(".",$param['suhu']);
					$sSh="select berat_jenis,varian from ".$dbname.".pabrik_5suhu where millcode='".$param['kodeorg']."' 
						  and kodetangki='".$param['kodetangki']."' and suhu='".$suhu[0]."'";
					//exit('warning'.$sSh);
					$qSh=$owlPDO->query($sSh) or die(print " Gagal: ".PDOException::getMessage());
					$qSh->setFetchMode(PDO::FETCH_ASSOC);
					$rSh=$qSh->fetch();
					
					$sTng="select volume,beda from ".$dbname.".pabrik_5tinggitangki where millcode='".$param['kodeorg']."' 
						  and kodetangki='".$param['kodetangki']."' and tinggi='".$ting[0]."'";
					$qTng=$owlPDO->query($sTng) or die(print " Gagal: ".PDOException::getMessage());
					$qTng->setFetchMode(PDO::FETCH_ASSOC);
					$rTng=$qTng->fetch();
					
					$sSuhuKalibrasi="select suhu_kalibrasi from ".$dbname.".pabrik_5standardsuhu_kalibrasi 
									where millcode='".$param['kodeorg']."' and kodetangki='".$param['kodetangki']."' 
									order by periode desc limit 1";
					$qSuhuKalibrasi=$owlPDO->query($sSuhuKalibrasi) or die(print " Gagal: ".PDOException::getMessage());
					$qSuhuKalibrasi->setFetchMode(PDO::FETCH_ASSOC);
					$rSuhuKalibrasi=$qSuhuKalibrasi->fetch();
					
					
					#== ambil data faktor koreksi
					$strfk="select nilai,nilaiangka from ".$dbname.".pabrik_5faktorkoreksi where millcode='".$param['kodeorg']."'  and kodetangki='".$param['kodetangki']."'  ";
					$resfk=$owlPDO->query($strfk) or die(print " Gagal: ".PDOException::getMessage());
					$resfk->setFetchMode(PDO::FETCH_ASSOC);
					$barfk=$resfk->fetch();	
					$nilaikoreksi=$barfk['nilai'];
					
					@$volTing=$rTng['volume']+round((floatval("0.".$ting[1])*$rTng['beda']));
					$volTangki=$volTing*$nilaikoreksi;
					$volTangkiAll=round($volTangki*$rSh['berat_jenis']);//
					
					
					$tab.="<tr>";
						$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Sesudah</td>"; 
						$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$bar['kodetangki']."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($bar['tinggi2'],2)."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($mejaukur,2)."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($param['tinggi'],2)."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($volTing)."</td>"; 
						$tab.="<td  colspan=2 style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$nilaikoreksi."</td>"; 
						// $tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($volTangki)."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($param['suhu'])."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($rSh['berat_jenis'],4)."</td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($bar['jumlah2'])."</td>"; 

					$tab.="</tr>";	
				
					$tab.="<tr>";
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' colspan=10></td>"; 
						$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($bar['jumlah'])."</td>"; 
					$tab.="</tr>";
				
				
				
					$tampilankualitas="";
									
					if($bar['ffa1']!=0){
						$tampilankualitas.=",FFA ".$bar['ffa1']." %";
					}
					if($bar['moisture1']!=0){
						$tampilankualitas.=", Moisture ".$bar['moisture1']." %";
					}
					if($bar['dirt1']!=0){
						$tampilankualitas.=", Dirt ".$bar['dirt1']." %";
					}
					if($bar['dobi1']!=0){
						$tampilankualitas.=", Dobi ".$bar['dobi1']." %";
					}
					if($bar['broken1']!=0){
						$tampilankualitas.=", Broken ".$bar['broken1']." %";
					}
					if($bar['impurities1']!=0){
						$tampilankualitas.=", Impurities ".$bar['impurities1']." %";
					}
					
					$tab.="<tr>";
						$tab.="<td colspan=3>&nbsp;&nbsp;Final Pemuatan :</td>"; 
						$tab.="<td colspan=8>".number_format($bar['jumlah'])." Kg</td>"; 
					$tab.="</tr>";
				
					$tab.="<tr>";
						$tab.="<td colspan=11>&nbsp;&nbsp;Hasil analisa : ".$tampilankualitas."</td>"; 
					$tab.="</tr>";
					
				$tab.="</table>";
			}
		 
		
		
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
			$tab.="<br>";
		
		
			$cellpadding=0.5;	
			$tab.="<table style='font-size:12px;width:100%' cellpadding=".$cellpadding.">";
				$tab.="<tr>";
					$tab.="<td style='width:100px' align=center>Acknowleged</td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td style='width:100px' align=center>Checked</td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td style='width:100px' align=center>Prepared</td>"; 
				$tab.="</tr>";
				
				for($i=1;$i<5;$i++){
					$tab.="<tr>";
					$tab.="<td style='width:100px' align=center>&nbsp;</td>"; 
				$tab.="</tr>";
				}
			
				$tab.="<tr>";
					$tab.="<td style='border-bottom:0.5px solid #000000' align=center><b></b></td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td style='border-bottom:0.5px solid #000000' align=center><b></b></td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td style='border-bottom:0.5px solid #000000' align=center><b></b></td>"; 
				$tab.="</tr>";
				$tab.="<tr>";
					$tab.="<td align=center>Manager</td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td align=center>Senior Supervisor</td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td align=center>Supervisor</td>"; 
				$tab.="</tr>";
			$tab.="</table>";
		}
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	
	break;
	
	
	
	
	case'carinoreferensi':
        echo"<fieldset  style='float:left;' >
                <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['notransaksi']."</legend>
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>".$_SESSION['lang']['notransaksi']."</td>
                            <td colspan=5>: 
								<input type=text id=daftarnoreferensi value=".date('Y')." class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
								<button class=mybutton onclick=caridaftarnoreferensi()>cari</button>
                            <td>
                        <tr>
                    </table>
                    <table>
                    <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>".$_SESSION['lang']['notransaksi']."</td>
                            <td>".$_SESSION['lang']['jumlah']."</td>
                            <td>".$_SESSION['lang']['namakapal']."</td>
                            <td>".$_SESSION['lang']['namaponton']."</td>
                            <td>".$_SESSION['lang']['tanggalberangkat']."</td>
                    </tr></thead>";
					
					

                    if($noreferensi!=''){
						$str="select sum(jumlah) as jumlah,notransaksi,namakapal,namaponton,tanggalberangkat from ".$dbname.".pabrik_bamutasi where tipe='OUT' and posting=1 and unitreferensi='".$unit."' and notransaksi like '%".$noreferensi."%' and noreferensi='' group by notransaksi order by tanggal desc,notransaksi desc";
						$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch()){
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movecarinoreferensi('".$bar['notransaksi']."','".$bar['jumlah']."');\">
								<td>".$no."</td>
								<td>".$bar['notransaksi']."</td> 
								<td>".$bar['jumlah']."</td> 
								<td>".$nmkapalponton[$bar['namakapal']]."</td> 
								<td>".$nmkapalponton[$bar['namaponton']]."</td> 
								<td>".tanggalnormal($bar['tanggalberangkat'])."</td> 
                            </tr>";
                        }
					}
                    echo"</table>
        </fieldset>";
	
    break; 
	
	
	
	// case'carinosip':
	case'getsipb':
		$tab.="<fieldset><legend>".$_SESSION['lang']['form']."</legend>";
			$tab.="<table>";
			$tab.="
				<tr>
					<td>".$_SESSION['lang']['nospk']."</td>
					<td>:</td>
					<td>
						<input type=text id=nosipbfind class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
					<td>
					<td></td>
					<td></td>
					<td><button class=mybutton onclick=findsipb()>".$_SESSION['lang']['find']."</button></td>
				</tr>";
			$tab.="</table>";
		$tab.="</fieldset>";
		$tab.="<br>";
		
		$tab.=" <div class=table-scroll style='height:280px'>";
		$tab.="<table cellpadding=1 cellspacing=1 border=0 width=50% class=sortable><tbody class=rowcontent>";
		$tab.="<thead><tr class=rowheader>
				<th align=center>".$_SESSION['lang']['nospk']."</th>
				<th align=center>".$_SESSION['lang']['tanggaltiba']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['timbangan']." ".$_SESSION['lang']['penerimaan']."</th>
				<th align=center>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['bast']." ".$_SESSION['lang']['penerimaan']."</th>
				<th align=center>".$_SESSION['lang']['sisa']."</th>";
		$tab.="</tr>";
		$tab.="</thead>";
		$tab.="<tbody id=formcarinosip></tbody>";
		$tab.="</table>";
		$tab.="</div>";
	
		echo $tab;
		
	break;
	
	
	case'findsipb':
		
		#= darat pakai SPK ETC
		if($transportasi=='DARAT'){
			$str="select * from ".$dbname.".pmn_spk_etc where (nospk like '%".$nosip."%' OR nokontrak like '%".$nosip."%') AND kodept = '".$kodept."' and kodebarang='".$kodebarang."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				#= ambil data timbangan
				$jumlah=$jumlahsudahmasuk=0;
				$strdt="select sum(beratbersih) as jumlah from ".$dbname.".pabrik_timbangan_vw where nosipb='".$bar['nospk']."' and millcode='".$unit."' and tanggal='".$tanggaltiba."' and kodebarang='".$bar['kodebarang']."'";
				// echo $strdt;
				$resdt=fetchdata($strdt);
				foreach($resdt as $bardt){
					$jumlahtimbang=$bardt['jumlah'];
				}
				
				#= cek jumlah yang sudah diterimakan
				$strdt="select sum(jumlah) as jumlah from ".$dbname.".pabrik_bamutasi where nosip='".$bar['nospk']."' and unit='".$unit."' and tanggaltiba like '".$tanggaltiba."%' and kodebarang='".$bar['kodebarang']."'";
				// echo $strdt;
				$resdt=fetchdata($strdt);
				foreach($resdt as $bardt){
					$jumlahsudahmasuk=$bardt['jumlah'];
				}
				$sisa=$jumlahtimbang-$jumlahsudahmasuk;
				if($sisa>0){
					$tab.="<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movecarinosip('".$bar['nospk']."','".$bar['kodebarang']."','".$sisa."');\">";
				}else{
					$tab.="<tr class=rowcontent>";
				}
					$tab.="<td>".$bar['nospk']."</td>";
					$tab.="<td>".tanggalnormal($tanggaltiba)."</td>";
					$tab.="<td>".hidezerodecimal($jumlahtimbang)."</td>";
					$tab.="<td>".hidezerodecimal($jumlahsudahmasuk)."</td>";
					$tab.="<td>".hidezerodecimal($sisa)."</td>";
				$tab.="</tr>";
			}
		}
		#= air pakai SIP
		if($transportasi=='AIR'){
			$str="select * from ".$dbname.".pmn_suratperintahpengiriman where (nodo like '%".$nosip."%' OR nokontrak like '%".$nosip."%') AND pt = '".$kodept."' and kodebarang='".$kodebarang."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$tab.="<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movecarinosip('".$bar['nodo']."','".$bar['kodebarang']."','0');\">";
					$tab.="<td>".$bar['nodo']."</td>";
					$tab.="<td></td>";
					$tab.="<td>0</td>";
					$tab.="<td>0</td>";
					$tab.="<td>0</td>";
				$tab.="</tr>";	
			}
		}
		
		
		echo $tab;
	break;
	
	/*
	 case'carinosip':
        echo"<fieldset  style='float:left;' >
                <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['NoKontrak']."</legend>
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td>".$_SESSION['lang']['NoKontrak']."</td>
                            <td>: 
								<input type=text id=daftarnosip value='".$nosip."' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
								<button class=mybutton onclick=caridaftarnosip()>cari</button>
                            <td>
                        </tr>
                        <!--<tr>
                            <td>".$_SESSION['lang']['nospk']."</td>
                            <td>: 
								<input type=text id=daftarnospk  value='".$nospk."' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                            <td>
                        </tr>
                        <tr>
                            <td colspan=2 align=right> 
								<button class=mybutton onclick=caridaftarnosip()>cari</button>
                            <td>
                        </tr>-->
                    </table>

                    <table border=0 style=width:100%>
	                    <thead>
	                    <tr class=rowheader>
	                            <td align=center>".$_SESSION['lang']['nourut']."</td>
	                            <td align=center>".$_SESSION['lang']['nosipb']."</td>
	                            <td align=center>".$_SESSION['lang']['tanggal']."</td>
	                            <td align=center>".$_SESSION['lang']['sisa']."</td>
	                    </tr></thead>";
						$no=0;
	                    if($nosip!=''){
							$str="select * from ".$dbname.".pmn_suratperintahpengiriman where (nodo like '%".$nosip."%' OR nokontrak like '%".$nosip."%') AND pt = '".$kodept."'";
							// echo $str;exit();
	                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	                        $res->setFetchMode(PDO::FETCH_ASSOC);
	                        while($bar=$res->fetch()){
								$sisaqty=0;
								
									#= jika PK dan unit adalah KSBW maka ambil data timbangan
									$str1="select sum(beratbersih) as qty from ".$dbname.".pabrik_timbangan where
										nosipb='".$bar['nosip']."' and tanggal <= '".substr($tanggal,0,10)."' ";
										// echo $str1;
									$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
									$res1->setFetchMode(PDO::FETCH_ASSOC);
									$bar1=$res1->fetch();
									
									$str2="select sum(jumlah) as qty from ".$dbname.".pabrik_bamutasi where
										nosip='".$bar['nosip']."' and tanggal <= '".substr($tanggal,0,10)."' and tipe='OUT'";
										// echo $str1;
									$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
									$res2->setFetchMode(PDO::FETCH_ASSOC);
									$bar2=$res2->fetch();
									
									#= qty sisa
									$sisaqty=$bar1['qty']-$bar2['qty'];
								
	                            $no++;
								if($sisaqty>0){
									echo"<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movecarinosip('".$bar['nodo']."','".$bar['kodebarang']."','".$sisaqty."');\">
										<td>".$no."</td>
										<td>".$bar['nodo']."</td> 
										<td>".tanggalnormal($bar['tanggaldo'])."</td> 
										<td align=right>".number_format($sisaqty)."</td> 
									</tr>";
								}
	                        }

	                        $str="select * from ".$dbname.".pmn_spk_etc where (nospk like '%".$nosip."%' OR nokontrak like '%".$nosip."%') AND kodept = '".$kodept."'";
	                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	                        $res->setFetchMode(PDO::FETCH_ASSOC);
	                        while($bar=$res->fetch()){
								$sisaqty=0;
									
									
									#= jika PK dan unit adalah KSBW maka ambil data timbangan
									$str1="select sum(beratbersih) as qty from ".$dbname.".pabrik_timbangan where
										nosipb='".$bar['nosip']."' and tanggal <= '".substr($tanggal,0,10)."' ";
										// echo $str1;
									$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
									$res1->setFetchMode(PDO::FETCH_ASSOC);
									$bar1=$res1->fetch();
									
									$str2="select sum(jumlah) as qty from ".$dbname.".pabrik_bamutasi where
										nosip='".$bar['nosip']."' and tanggal <= '".substr($tanggal,0,10)."' and tipe='OUT'";
										// echo $str1;
									$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
									$res2->setFetchMode(PDO::FETCH_ASSOC);
									$bar2=$res2->fetch();
									
									#= qty sisa
									$sisaqty=$bar1['qty']-$bar2['qty'];
									
									
									#= ubah konsep ambil saja yang langsung dari timbangan
								
	                            $no++;
								// if($sisaqty>0){
									if($bar['nokontrak'] != ''){
										$nokon = $bar['nokontrak'];
									} else {
										$nokon = $bar['nospk'];
									}
									echo"<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movecarinosip('".$nokon."','".$bar['kodebarang']."','".$sisaqty."');\">
										<td>".$no."</td>
										<td>".$nokon."</td> 
										<td>".tanggalnormal($bar['tanggal'])."</td> 
										<td align=right>".number_format($sisaqty)."</td> 
									</tr>";
								// }
	                        }

	                        $str="select * from ".$dbname.".pmn_spk_ipkd where (nospk like '%".$nosip."%' OR nokontrak like '%".$nosip."%') AND kodept = '".$kodept."'";
	                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	                        $res->setFetchMode(PDO::FETCH_ASSOC);
	                        while($bar=$res->fetch()){
								$sisaqty=0;
								
									#= jika PK dan unit adalah KSBW maka ambil data timbangan
									$str1="select sum(beratbersih) as qty from ".$dbname.".pabrik_timbangan where
										nosipb='".$bar['nosip']."' and tanggal <= '".substr($tanggal,0,10)."' ";
										// echo $str1;
									$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
									$res1->setFetchMode(PDO::FETCH_ASSOC);
									$bar1=$res1->fetch();
									
									$str2="select sum(jumlah) as qty from ".$dbname.".pabrik_bamutasi where
										nosip='".$bar['nosip']."' and tanggal <= '".substr($tanggal,0,10)."' and tipe='OUT'";
										// echo $str1;
									$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
									$res2->setFetchMode(PDO::FETCH_ASSOC);
									$bar2=$res2->fetch();
									
									#= qty sisa
									$sisaqty=$bar1['qty']-$bar2['qty'];
								
	                            $no++;
								if($sisaqty>0){
									if($bar['nokontrak'] != ''){
										$nokon = $bar['nokontrak'];
									} else {
										$nokon = $bar['nospk'];
									}
									echo"<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movecarinosip('".$nokon."','".$bar['kodebarang']."','".$sisaqty."');\">
										<td>".$no."</td>
										<td>".$nokon."</td> 
										<td>".tanggalnormal($bar['tanggal'])."</td> 
										<td align=right>".number_format($sisaqty)."</td> 
									</tr>";
								}
	                        }
						}
	                    echo"</table>
	        </fieldset>";
	
    break; 
	*/
	
	case'posting':
	
		try {
			$owlPDO->beginTransaction();
			
			/*
			D:biaya transpor sales
			D:hutang transpor (claim)
			K:hutang transpor
			K:Gain loss In transit CPO / PK
			*/
			
			#=
			$str = "select * from ".$dbname.".organisasi  where length(kodeorganisasi)='4' and tipe='KANWIL'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$kodero[$bar['induk']]=$bar['kodeorganisasi'];
			}

			
			
			$str="select * from ".$dbname.".".$table." where  notransaksi='".$notransaksi."'"; 
			$res=fetchdata($str);
			foreach($res as $bar){
				$periode=substr($bar['tanggalberangkat'],0,7);
				$tanggal=substr($bar['tanggalberangkat'],0,10);
				$kodept=$bar['kodept'];
				$unit=$kodero[$bar['kodept']];
				@$kgjumlah+=$bar['jumlah'];
				$nosip=$bar['nosip'];
				$nospk=$bar['nosip'];
				$transportir=$bar['transportir'];
				$tipe=$bar['tipe'];
				$kodebarang=$bar['kodebarang'];
				$transportasi=$bar['transportasi'];
			}
			
			#= hanya tipe out yang jurnal
			if($tipe=='OUT'){
				if($transportasi=='AIR'){
					#= air ambil dari sip
					$str="select * from ".$dbname.".pmn_suratperintahpengiriman where  nodo='".$nospk."'"; 
					$res=fetchdata($str);
					foreach($res as $bar){
						$noakundebet=$bar['noakundebet'];
						$rpkg=$bar['harga'];
					}
				}else{
					#= darat ambil dari spk etc
					$str="select * from ".$dbname.".pmn_spk_etc where  nospk='".$nospk."'"; 
					$res=fetchdata($str);
					foreach($res as $bar){
						$noakundebet=$bar['noakundebet'];
						$rpkg=$bar['rpkg'];
					}
				}
				
				
				
				#= coa transportir
				$str="select * from ".$dbname.".log_5supkelompok where  supplierid='".$transportir."' and ((tipe like '%KONTRAKTOR%') or (tipe like '%TRANSPORTIR%')) "; 
				// exit("Error:$str");
				$res=fetchdata($str);
				foreach($res as $bar){
					$noakunkredit=$bar['noakun'];
				}
				if($noakunkredit==''){
					throw new PDOException("Warningsistem:Noakun kredit masih kosong, silahkan daftarkan di master supplier dengan tipe kontraktor atau transportir");
				}
				if($noakundebet==''){
					throw new PDOException("Warningsistem:Noakun debet masih kosong");
				}
				
			
				$kodejurnal='BATR';
				$query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',"kodekelompok='".$kodejurnal."' and kodeunit='".$unit."' and periode='".$periode."'");
				$tmpKonter = fetchData($query);
				$konter = addZero($tmpKonter[0]['nokounter']+1,3);
				# Prep No Jurnal
				$nojurnal = str_replace('-','',$tanggal)."/".$unit."/".$kodejurnal."/".$konter;
		
				
				$dataRes['header'][] = array(
					'nojurnal'=>$nojurnal,
					'kodejurnal'=>$kodejurnal,
					'tanggal'=>$tanggal,
					'tanggalentry'=>date('Ymd'),
					'posting'=>'0',
					'totaldebet'=>'0',
					'totalkredit'=>'0',
					'amountkoreksi'=>'0',
					'noreferensi'=>$notransaksi,
					'autojurnal'=>'1',
					'matauang'=>'IDR',
					'kurs'=>'1',
					'revisi'=>'0'
				);
				$noUrut=1;
			
				#= debet
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggal,
					'nourut'=>$noUrut,
					'noakun'=>$noakundebet,
					'keterangan'=>'Jurnal BA Transport : '.$notransaksi.' SPK '.$nospk,
					'jumlah'=>($rpkg*$kgjumlah),
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unit,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$kodebarang,
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>$transportir,
					'noreferensi'=>$notransaksi,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$nospk,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => '0000000001'
				);
				$noUrut++;
				
				
				#= kredit
				$dataRes['detail'][] = array(
					'nojurnal'=>$nojurnal,
					'tanggal'=>$tanggal,
					'nourut'=>$noUrut,
					'noakun'=>$noakunkredit,
					'keterangan'=>'Jurnal BA Transport : '.$notransaksi.' SPK '.$nospk,
					'jumlah'=>($rpkg*$kgjumlah)*-1,
					'matauang'=>'IDR',
					'kurs'=>'1',
					'kodeorg'=>$unit,
					'kodekegiatan'=>'',
					'kodeasset'=>'',
					'kodebarang'=>$kodebarang,
					'nik'=>'',
					'kodecustomer'=>'',
					'kodesupplier'=>$transportir,
					'noreferensi'=>$notransaksi,
					'noaruskas'=>'',
					'kodevhc'=>'',
					'nodok'=>$nospk,
					'kodeblok'=>'',
					'revisi'=>'0',
					'kodesegment' => '0000000001'
				);
				$noUrut++;
				
				#= update counter jurnal
				$str="update ".$dbname.".keu_5kelompokjurnal set nokounter='".$konter."' where kodeunit='".$unit."' and kodekelompok='".$kodejurnal."' and periode='".$periode."' ";	
				$owlPDO->exec($str);
				
				#= jurnalht
				$queryH = insertQuery($dbname,'keu_jurnalht',$dataRes['header']);
				$owlPDO->exec($queryH);
				
				#= jurnaldt
				$queryD = insertQuery($dbname,'keu_jurnaldt',$dataRes['detail']);
				$owlPDO->exec($queryD);
			
			}
			
			#= update flag posting
			$str = "update ".$dbname.".".$table." set posting='1',postingby='".$_SESSION['standard']['userid']."' where notransaksi='".$notransaksi."' ";
			$owlPDO->exec($str);
				
			$owlPDO->commit();
			
		} catch(PDOException $e) {
			
			$owlPDO->rollback();
			echo "Warning Posting Gagal \n" . addslashes($e->getMessage());

		}
	
	break;
	
	
	/*
	case'posting':
		$str = "update ".$dbname.".".$table." set 
				posting='1',postingby='".$_SESSION['standard']['userid']."' where notransaksi='".$notransaksi."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	*/
	
	case'unposting':
		exit("Warningsistem:Hubungi IT untuk melakukan unposting");
		/*
		$str = "update ".$dbname.".".$table." set 
				posting='0',postingby='' where notransaksi='".$notransaksi."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
		*/
	break;
	
	
	
	case'deleteht':
		$str = "delete from ".$dbname.".".$table." where notransaksi='".$notransaksi."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	
	
	case'notransaksi':
		$notransaksi = generatenobamutasi();	
		echo $notransaksi;
	break;
		
		
		
	case 'insert':
		#= jika darat maka kapal / ponton harus kosong
		if($transportasi=='DARAT'){
			if($namakapal!='' or $namaponton!=''){
				exit("Warning:Jika transportasi darat maka nama kapal dan nama ponton harus dikosongkan");
			}
		}
		
		$data = array(
			'notransaksi'=>$notransaksi,
			'tanggal'=>$tanggal,
			'tipe'=>$tipe,
			'transportasi'=>$transportasi,
			'transportir'=>$transportir,
			'namakapal'=>$namakapal,
			'namaponton'=>$namaponton,
			'keteranganht'=>$keteranganht,
			'tanggalberangkat'=>$waktuberangkat,
			'nosip'=>$nosip,
			'kodept'=>$kodept,
			'kodebarang'=>$kodebarang,
			'unit'=>$unit,
			'kodetangki'=>$kodetangki,
			'keterangandt'=>$keterangandt,
			'jumlah'=>$jumlah,
			'tanggaltiba'=>$waktutiba,
			'tanggalbongkar1'=>$waktubongkar1,
			'tanggalbongkar2'=>$waktubongkar2,
			'suhu1'=>$suhu1,
			'tinggi1'=>$tinggi1,
			'jumlah1'=>$jumlah1,
			'ffa1'=>$ffa1,
			'moisture1'=>$moisture1,
			'dirt1'=>$dirt1,
			'dobi1'=>$dobi1,
			'broken1'=>$broken1,
			'suhu2'=>$suhu2,
			'tinggi2'=>$tinggi2,
			'jumlah2'=>$jumlah2,
			'ffa2'=>$ffa2,
			'moisture2'=>$moisture2,
			'dirt2'=>$dirt2,
			'dobi2'=>$dobi2,
			'broken2'=>$broken2,
			'unitreferensi'=>$unitreferensi,
			'noreferensi'=>$noreferensi,
			'createby' => $_SESSION['standard']['userid'],
			'createtime' => date('Y-m-d H:i'),
			'updateby' => $_SESSION['standard']['userid']
		);
		
		$cols = array();
		foreach($data as $key=>$row) {
			$cols[] = $key;
		}
		$str = insertQuery($dbname,$table,$data,$cols); 	
		// exit("Error:$str");

		try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}

	break;
	
	
   case'loaddata':
	
		// $unit=$_SESSION['empl']['lokasitugas'];
		
		$arrunit=array();
		$arrunit=getOrgDetail(13);
		foreach($arrunit as $val=>$nama){
			$dtunit[$val]=$val;
		} 
		
		$where="1=1 and  unit in ('".implode("','",$dtunit)."') ";
	   
		if($tanggalselesai!='' and $tanggalmulai!=''){
			$where.=" and tanggal between '".$tanggalmulai."' and '".$tanggalselesai."'";
		}
		if($notransaksi!=''){
			$where.=" and notransaksi like '%".$notransaksi."%'";
		}
		if($unit!=''){
			$where.=" and unit='".$unit."'";
		}
		if($kodept!=''){
			$where.=" and kodept='".$kodept."'";
		}
		if($kodebarang!=''){
			$where.=" and kodebarang='".$kodebarang."'";
		}
		if($tipe!=''){
			$where.=" and tipe='".$tipe."'";
		}
		
		$limit = 20;
		$page = 0;
		if (isset($_POST['page'])) {
			$page = $_POST['page'];
			if ($page < 0)
				$page = 0;
		}
		$maxdisplay=($page*$limit);
	
		
		$offset = $page * $limit;
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where." group by notransaksi ";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
            $jumrow = $bar['jumrow'];
			
		$no = 0;
		$no=$maxdisplay;
		$str = "select sum(jumlah) as jumlah,notransaksi,unit,kodebarang,tanggal,posting,kodept,tipe,updateby,nosip,noreferensi  from ".$dbname.".".$table."  
			where  ".$where."  group by notransaksi order by tanggal desc,notransaksi desc limit " . $offset . "," . $limit . " ";
			// exit("Error".$str);
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['notransaksi']."</td>";
				$tab.="<td>".$bar['tipe']."</td>";
				$tab.="<td>".$bar['nosip']."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td>".$bar['unit']."</td>";
				$tab.="<td>".$bar['kodept']."</td>";
				$tab.="<td>".$nmkomoditi[$bar['kodebarang']]."</td>";
				$tab.="<td>".number_format($bar['jumlah'])."</td>";
				$tab.="<td>".$bar['noreferensi']."</td>";
				$tab.="<td>".@getNamaKaryawan($bar['updateby'])."</td>";
				$tab.="<td align=center>";
				if($bar['posting']==0){
					$tab.="<img src=images/application/application_edit.png class=resicon  caption='Edit' 
						onclick=\"editht('".$bar['notransaksi']."');\">";
					$tab.="&nbsp;<img src=images/application/application_delete.png class=resicon  caption='Delete' 
						onclick=\"deleteht('".$bar['notransaksi']."','".$bar['tipe']."');\">";		
					$tab.="&nbsp;<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['notransaksi']."');\">";							
				} else {
					// $tab.="&nbsp;<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting' >";
					$tab.="&nbsp;<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"unposting('".$bar['notransaksi']."');\">";			
				}
				$tab.="&nbsp;<img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print Document PDF ".$bar['notransaksi']."' onclick=\"pdf('".$bar['notransaksi']."');\">";	
				$tab.="</td>";
			$tab.="</tr>";
        }
		
		// $tab.="</table>";
			
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where ".$where."  group by notransaksi";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $bar = owlBaris($res);
        $totrows = ceil($bar / $limit);
		
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
			$sel = ($page==$er-1)? 'selected': '';
            $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd = "</tr>
            <tr><td colspan=22 align=center>
            <button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getpage()\">" . $isiRow . "</select>
            <button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td>
            </tr>";
        echo $tab . "####" . $footd;	
	break;
	
	case'geteditht':
	
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$notransaksi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		
		// exit("Error:$str");
		
		//transportasi
		
		echo $bar['notransaksi']."###".tanggalnormal($bar['tanggal'])."###".$bar['tipe']
		."###".$bar['namakapal']."###".$bar['namaponton']."###".$bar['keteranganht']
		."###".tanggalnormal(substr($bar['tanggalberangkat'],0,10))."###".substr($bar['tanggalberangkat'],11,2)."###".substr($bar['tanggalberangkat'],14,2)
		."###".$bar['unit']."###".$bar['kodebarang']."###".$bar['transportir']."###".$bar['transportasi']
		."###".tanggalnormal(substr($bar['tanggaltiba'],0,10))."###".substr($bar['tanggaltiba'],11,2)."###".substr($bar['tanggaltiba'],14,2)
		."###".tanggalnormal(substr($bar['tanggalbongkar1'],0,10))."###".substr($bar['tanggalbongkar1'],11,2)."###".substr($bar['tanggalbongkar1'],14,2)
		."###".tanggalnormal(substr($bar['tanggalbongkar2'],0,10))."###".substr($bar['tanggalbongkar2'],11,2)."###".substr($bar['tanggalbongkar2'],14,2)
		."###".$bar['kodept']."###".$bar['unitreferensi']."###".$bar['noreferensi'];
		// exit("Error:a");
	break;
	
	
	/********************** detail ***************************/
	
	case 'update':
		$str = "update ".$dbname.".".$table." set 
			keterangandt='".$keterangandt."',
			jumlah='".$jumlah."',
			tanggalpasang1='".$waktupasang1."',
			tanggalpasang2='".$waktupasang2."',
			tanggalmuat1='".$waktumuat1."',
			tanggalmuat2='".$waktumuat2."',
			suhu1='".$suhu1."',
			tinggi1='".$tinggi1."',
			jumlah1='".$jumlah1."',
			ffa1='".$ffa1."',
			moisture1='".$moisture1."',
			dirt1='".$dirt1."',
			dobi1='".$dobi1."',
			broken1='".$broken1."',
			suhu2='".$suhu2."',
			tinggi2='".$tinggi2."',
			jumlah2='".$jumlah2."',
			ffa2='".$ffa2."',
			moisture2='".$moisture2."',
			dirt2='".$dirt2."',
			dobi2='".$dobi2."',
			broken2='".$broken2."',
			updateby='".$_SESSION['standard']['userid']."',
			noreferensi='".$noreferensi."'
			where notransaksi = '".$notransaksi."' and nosip='".$nosip."' and kodetangki='".$kodetangki."'";#exit("error".$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	
	case'geteditdt':
	
		$str = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."' and nosip='".$nosip."' and kodetangki='".$kodetangki."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		
		echo $bar['nosip']."###".$bar['kodetangki']."###".$bar['keterangandt']."###".number_format($bar['jumlah'])
		."###".$bar['suhu1']."###".$bar['tinggi1']."###".number_format($bar['jumlah1'])."###".$bar['ffa1']
		."###".$bar['moisture1']."###".$bar['dirt1']."###".$bar['dobi1']."###".$bar['broken1']
		."###".$bar['suhu2']."###".$bar['tinggi2']."###".number_format($bar['jumlah2'])."###".$bar['ffa2']
		."###".$bar['moisture2']."###".$bar['dirt2']."###".$bar['dobi2']."###".$bar['broken2'];
	break;
	
	case'loaddatadt':
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$notransaksi."' ";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['nosip']."</td>";
				$tab.="<td>".$bar['kodetangki']."</td>";
				$tab.="<td>".number_format($bar['jumlah'])."</td>";
				$tab.="<td>".$bar['keterangandt']."</td>";
				$tab.="<td align=center  valign=top>";
				$tab.="<img src=images/application/application_edit.png class=resicon  caption='Edit' 
				onclick=\"editdt('".$bar['notransaksi']."','".$bar['nosip']."','".$bar['kodetangki']."');\">";
				$tab.="&nbsp;<img src=images/application/application_delete.png class=resicon  caption='Delete' 
				onclick=\"deletedt('".$bar['notransaksi']."','".$bar['nosip']."','".$bar['kodetangki']."');\">";	
				$tab.="</td>";
            $tab.="</tr>";
			@$tjumlah+=$bar['jumlah'];
        }
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=3>".$_SESSION['lang']['total']."</td>";
		$tab.="<td>".number_format($tjumlah)."</td>";
		$tab.="<td colspan=2></td>";
		echo $tab;
	break;
	
	case'deletedt':
		$str = "delete from ".$dbname.".".$table." where 
			notransaksi='".$notransaksi."' and nosip='".$nosip."' and kodetangki='".$kodetangki."'";
			// exit("Error:$str");
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	
    default:
	break;
}
?>
