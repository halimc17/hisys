<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('pmn_spk_nospk_slave.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;


$method          = checkPostGet('method','');
$unit            = checkPostGet('unit','');
$kodetangki      = checkPostGet('kodetangki','');
$suhu            = checkPostGet('suhu','');
$tinggi          = checkPostGet('tinggi','');
$tanggal         = tanggalsystemn(checkPostGet('tanggal',''));
$tanggalmulai    = tanggalsystemn(checkPostGet('tanggalmulai',''));
$tanggalselesai  = tanggalsystemn(checkPostGet('tanggalselesai',''));
$notransaksi     = checkPostGet('notransaksi','');
$tipe            = checkPostGet('tipe','');
$kodetangkitujuan= checkPostGet('kodetangkitujuan','');
$nokontrak       = checkPostGet('nokontrak','');
$suhu1           = checkPostGet('suhu1','');
$tinggi1         = checkPostGet('tinggi1','');
$jumlah          = checkPostGet('jumlah','');
$jumlah1         = checkPostGet('jumlah1','');
$ffa1            = checkPostGet('ffa1','');
$moisture1       = checkPostGet('moisture1','');
$dirt1           = checkPostGet('dirt1','');
$dobi1           = checkPostGet('dobi1','');
$broken1         = checkPostGet('broken1','');
$keterangan1     = checkPostGet('keterangan1','');
$keterangan2     = checkPostGet('keterangan2','');
$keterangan      = checkPostGet('keterangan','');
$suhu2           = checkPostGet('suhu2','');
$tinggi2         = checkPostGet('tinggi2','');
$jumlah2         = checkPostGet('jumlah2','');
$ffa2            = checkPostGet('ffa2','');
$moisture2       = checkPostGet('moisture2','');
$dirt2           = checkPostGet('dirt2','');
$dobi2           = checkPostGet('dobi2','');
$broken2         = checkPostGet('broken2','');
$noreferensi     = checkPostGet('noreferensi','');

$jmmulai         =checkPostGet('jmmulai','');
$mnmulai         =checkPostGet('mnmulai','');

$jmselesai       =checkPostGet('jmselesai','');
$mnselesai       =checkPostGet('mnselesai','');

$jumlah          =checkPostGet('jumlah','');

$waktumulai      =$tanggalmulai." ".$jmmulai.":".$mnmulai.":00";
$waktuselesai    =$tanggalselesai." ".$jmselesai.":".$mnselesai.":00";

$kodept          =checkPostGet('kodept','');

$nmkomoditi      =makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$arrinisial      =makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
$nmpt            =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
// $kodept       =makeOption($dbname,'organisasi','kodeorganisasi,induk');
$namaorganisasi  =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PABRIK'");

$tanggalmulaisch=checkPostGet('tanggalmulaisch','');
$tanggalselesaisch=checkPostGet('tanggalselesaisch','');
if($tanggalmulaisch==''){
	$tanggalmulaisch='';
}else{
	$tanggalmulaisch = tanggalsystemn(checkPostGet('tanggalmulaisch',''));	
}

if($tanggalselesaisch==''){
	$tanggalselesaisch='';
}else{
	$tanggalselesaisch=tanggalsystemn(checkPostGet('tanggalselesaisch',''));
}
$notransaksisch=checkPostGet('notransaksisch','');
$kodetangkisch=checkPostGet('kodetangkisch','');



$table='pabrik_transferproduk';

$optbuyer=$optbarang=$opttipe=$optunit=$opttangki="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";




switch ($method) {
	
	case'printpdf':
		
	
		$str = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tipe=$bar['tipe'];
			$tanggalmulai=$bar['tanggalmulai'];
			$tanggalselesai=$bar['tanggalselesai'];
			$unit=$bar['unit'];
			$kodetangki=$bar['kodetangki'];
			$kodetangkitujuan=$bar['kodetangkitujuan'];
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
			// if($tipe=='OUT'){
				// $qty=$bar['jumlah1']-$bar['jumlah2'];
			// }else{
				// $qty=$bar['jumlah2']-$bar['jumlah1'];
			// }
						
			$ket1[$kodetangki]=$keterangan1;
			$ket2[$kodetangki]=$keterangan2;
			$sawal[$kodetangki]=$jumlah1;
			$salak[$kodetangki]=$jumlah2;
			$mutasi[$kodetangki]=$jumlah;
			
			

			
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
		
		$str=" select komoditi from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."' and kodetangki='".$kodetangki."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$komoditi=$bar['komoditi'];
			
		#notransaksi noreferensi	
		$str = "select * from ".$dbname.".".$table." where notransaksi='".$noreferensi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumlahreferensi=$bar['jumlah'];	
		
		if($tipe=='IN'){
			$judul="BERITA ACARA PENERIMAAN ".$komoditi." TRANSFER";
			$kata2tangki="Terima dari ";
		}else{
			$judul="BERITA ACARA PENGIRIMAN  ".$komoditi."  TRANSFER";
			$kata2tangki="Transfer ke ";
		}
		$tab.="<div style='page-break-after: always;'>";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0>";
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight:bold;text-align:center;font-size:20px'><b><u>".$judul."</u></b></td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight;text-align:center;font-size:14px'>".$notransaksi."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
	
		$tab.="<br>";
		$tab.="<br>";
		
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>No. Tangki</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$namatangki[$kodetangki]."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>Tanggal Mulai</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".tglnmbln(substr($tanggalmulai,0,10),'','')."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>Jam Mulai</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".substr($tanggalmulai,11,10)."</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$kata2tangki." Tangki</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$namatangki[$kodetangkitujuan]."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>Tanggal Selesai</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".tglnmbln(substr($tanggalselesai,0,10),'','')."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>Jam Selesai</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".substr($tanggalselesai,11,10)."</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Untuk Kontrak</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$nokontrak."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>Qty Transfer</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".number_format($jumlah)." Kg</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
			$tab.="</tr>";	
		$tab.="</table>";
		
		
		$tab.="<br>";
		
		
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
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$ket1[$bar['kodetangki']]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Stock Akhir</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($salak[$bar['kodetangki']])."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$ket2[$bar['kodetangki']]."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Jumlah</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($mutasi[$bar['kodetangki']])."</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
			$tab.="</tr>";
		}
		
		
		if($tipe=='IN'){
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' colspan=2>Total Jumlah</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($jumlah)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;' rowspan=3></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' colspan=2>Transfer dari Tangki</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($jumlahreferensi)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' colspan=2>Selisih</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($jumlah-$jumlahreferensi)."</td>"; 
			$tab.="</tr>";
		}else{
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' colspan=2>Total Jumlah</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($jumlah)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
			$tab.="</tr>";
		}
		
		
		$tab.="</table>";
		
		$tab.="<br>";
		
		$tab.="<table border=0 width:100%>";
		$tab.="<tr>";
			$tab.="<td style='width:75px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;vertical-align:top'>Keterangan</td>"; 
			$tab.="<td style='width:600px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>".$keterangan."</td>"; 
		$tab.="</tr>";
		$tab.="<tr>";
			$tab.="<td style='width:75px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;vertical-align:top'>&nbsp;</td>"; 
			$tab.="<td style='width:600px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
		$tab.="</tr>";
		$tab.="</table>";
	
		$tab.="<br>";
	
		
		$cellpadding=2;
		
		$cellpadding=0.5;	
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
		
		

		
		// $tab.="<footer>";
			// $cellpadding=1;	
			// $tab.="<table style='font-size:12px' border=0 cellpadding=".$cellpadding.">";	
				// $tab.="<tr>";
					// $tab.="<td align=left style='width:700px;border-bottom:0.5px solid #000000'><b>".$namapt."</b></td>"; 
				// $tab.="</tr>";
				// $tab.="<tr>";
					// $tab.="<td align=left><b>".$kotaro." Office</b> : ".$alamatro." Tel : ".$telpro." Fax : ".$faxro."</td>"; 
				// $tab.="</tr>";
			// $tab.="</table>";
		// $tab.="</footer>";	
		
		$tab.="</div>";
		
			if($tipe=='IN'){
			$judul="BERITA ACARA PENERIMAAN ".$komoditi." TRANSFER";
		}else{
			$judul="BERITA ACARA PENGIRIMAN  ".$komoditi."  TRANSFER";
		}
	
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0>";
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight:bold;text-align:center;font-size:20px'><b><u>".$judul."</u></b></td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		$tab.="<br>";
		$tab.="<br>";
	
		$cellpadding=2;
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0>";
		
		$tab.="<tr>";
			$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' colspan=2>Mulai Pompa</td>"; 
			$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' colspan=2>TGL : ".tanggalnormal(substr($tanggalmulai,0,10))."</td>"; 
			$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>JAM : ".substr($tanggalmulai,11,10)."</td>"; 
						$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>S/D</td>"; 
			$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;' colspan=3>TGL : ".tanggalnormal(substr($tanggalselesai,0,10))."</td>"; 
			$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;' colspan=2>JAM : ".substr($tanggalselesai,11,10)."</td>"; 
		$tab.="</tr>";		
			$tab.="<tr>";
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>KONDISI TANGKI</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>NOMOR TANGKI</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>SOUNDING (CM)</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>TABLE MEJA UKUR (CM)</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>SOUNDING KOREKSI(CM)</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>VOLUME PERHITUNGAN</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>KOREKSI FAKTOR SUHU (C-DEG)</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>VOLUME KOREKSI (CM)</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>SUHU (C-DEG)</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>BERAT JENIS</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>STOCK AKHIR (KG)</td>"; 
			$tab.="</tr>";
			


			$param['kodeorg']=$unit;
			$param['kodetangki']=$kodetangki;
			$param['tinggi']=$tinggi1;
			$param['suhu']=$suhu1;


			$str="select nilai from ".$dbname.".pabrik_5mejaukur where 
					kodeorg='".$param['kodeorg']."' and tangki='".$param['kodetangki']."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
				$mejaukur=$bar['nilai'];
				
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
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$kodetangki."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($tinggi1,2)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($mejaukur,2)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($param['tinggi'],2)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($volTing)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$nilaikoreksi."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($volTangki)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($param['suhu'])."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$rSh['berat_jenis']."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($jumlah1)."</td>"; 

			$tab.="</tr>";	
			
			
			
			$param['kodeorg']=$unit;
			$param['kodetangki']=$kodetangki;
			$param['tinggi']=$tinggi2;
			$param['suhu']=$suhu2;


			$str="select nilai from ".$dbname.".pabrik_5mejaukur where 
					kodeorg='".$param['kodeorg']."' and tangki='".$param['kodetangki']."'";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
				$mejaukur=$bar['nilai'];
				
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
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$kodetangki."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($tinggi2,2)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($mejaukur,2)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($param['tinggi'],2)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($volTing)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$nilaikoreksi."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($volTangki)."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($param['suhu'])."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$rSh['berat_jenis']."</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($jumlah2)."</td>"; 

			$tab.="</tr>";	
			
			
			
			
			$tab.="<tr>";
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;' colspan=10></td>"; 
				// $tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>TOTAL</td>"; 
				$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($jumlah)."</td>"; 
			$tab.="</tr>";
			$tab.="</table>";	
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
					$tab.="<td align=center>Manager ".ucwords(strtolower($namaorganisasi[$unit]))."</td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td align=center>".ucwords(strtolower($namaorganisasi[$unit]))." Senior BLK Supervisor</td>"; 
					$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
					$tab.="<td align=center>".ucwords(strtolower($namaorganisasi[$unit]))." Supervisor</td>"; 
				$tab.="</tr>";
			$tab.="</table>";
		
		
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
								<input type=text id=daftarnoreferensi  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
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
                    </tr></thead>";

                    if($noreferensi!=''){
						$str="select * from ".$dbname.".pabrik_transferproduk where 
							tipe='OUT' and posting=1 and notransaksi like '%".$noreferensi."%' and noreferensi=''";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch()){
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movecarinoreferensi('".$bar['notransaksi']."','".$bar['jumlah']."');\">
								<td>".$no."</td>
								<td>".$bar['notransaksi']."</td> 
								<td>".$bar['jumlah']."</td> 
                            </tr>";
                        }
					}
                    echo"</table>
        </fieldset>";
	
    break; 
	
	
	case'gettangki':
		$str="select * from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."'";
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$opttangki.="<option value='".$bar['kodetangki']."'>[".$bar['komoditi']."]&nbsp;&nbsp;&nbsp;".$bar['keterangan']."</option>";
		}
		echo $opttangki;
	break;
	
	case'posting':
	
		if($tipe=='IN'){
			$str = "update ".$dbname.".".$table." set 
					posting='1',postingby='".$_SESSION['standard']['userid']."' where notransaksi='".$notransaksi."' ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}

			$str = "update ".$dbname.".".$table." set 
				noreferensi='".$notransaksi."' where notransaksi='".$noreferensi."' ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
			
		}
		if($tipe=='OUT'){
			$str = "update ".$dbname.".".$table." set 
					posting='1',postingby='".$_SESSION['standard']['userid']."' where notransaksi='".$notransaksi."' ";
			try {
				$owlPDO->exec($str);
			} catch (PDOException $e) {
				echo " Gagal," . addslashes($e->getMessage());
			}
		}
	
			
	break;
	
	case'deleteht':
		$str = "delete from ".$dbname.".".$table." where notransaksi='".$notransaksi."'";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
		
	case 'insert':
			// exit("Error:A");
		#generet nokontrak	
		$notransaksi = generatenobatransferproduk();	

		#= pemasangan trap saat input
		
		
		#= jika tipe in harus mengisikan noreferensi dan mengosongkan nomor kontrak
		if($tipe=='IN'){
			if($noreferensi==''){
				exit("Warning:No. Referensi harus terisi");
			}
			if($nokontrak!=''){
				exit("Warning:No. Transaksi harus kosong");
			}
		}
		
		if($tipe=='OUT'){
			if($noreferensi!=''){
				exit("Warning:No. Referensi harus kosong");
			}
		}
		
		if($kodetangki==$kodetangkitujuan){
			exit("Warning:Tangki tidak boleh sama");
		}
		
		$str=" select komoditi from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."' and kodetangki='".$kodetangki."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$komoditi1=$bar['komoditi'];
			
		$str=" select komoditi from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."' and kodetangki='".$kodetangkitujuan."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$komoditi2=$bar['komoditi'];	
		
		if($komoditi1!=$komoditi2){
			exit("Warning:Komoditi tidak boleh berbeda");
		}
		
		if($komoditi1=='CPO'){
			$kodebarang='40000001';
		}
		
		if($komoditi1=='KER'){
			$kodebarang='40000002';
		}
		
		
		
		// if($tipe=='IN'){
			// if($jumlah1>$jumlah2){
				// exit("Warning:Tipe Masuk, QTY akhir tidak boleh kurang dari QTY awal");
			// }
		// } else{
			// if($jumlah2>$jumlah1){
				// exit("Warning:Tipe Keluar, QTY awal tidak boleh kurang dari QTY akhir");
			// }
		// }
		#================

		// if($tipe=='OUT'){
			// $jumlah=$jumlah1-$jumlah2;
		// }else{
			// $jumlah=$jumlah2-$jumlah1;
		// }		
	
		if($jumlah<0){
			exit("Warning:Nilai mutasi dibawah 0");
		}
		
		
		$data = array(
			'kodebarang'=>$kodebarang,
			'notransaksi'=>$notransaksi,
			'tipe'=>$tipe,
			'tanggal'=>$tanggal,
			'tanggalmulai'=>$waktumulai,
			'tanggalselesai'=>$waktuselesai,
			'unit'=>$unit,
			// 'kodept'=>$kodept[$unit],
			'kodept'=>$kodept,
			'kodetangki'=>$kodetangki,
			'kodetangkitujuan'=>$kodetangkitujuan,
			'nokontrak'=>$nokontrak,
			'suhu1'=>$suhu1,
			'tinggi1'=>$tinggi1,
			'jumlah1'=>$jumlah1,
			'ffa1'=>$ffa1,
			'moisture1'=>$moisture1,
			'dirt1'=>$dirt1,
			'dobi1'=>$dobi1,
			'broken1'=>$broken1,
			'keterangan1'=>$keterangan1,
			'suhu2'=>$suhu2,
			'tinggi2'=>$tinggi2,
			'jumlah2'=>$jumlah2,
			'ffa2'=>$ffa2,
			'moisture2'=>$moisture2,
			'dirt2'=>$dirt2,
			'dobi2'=>$dobi2,
			'broken2'=>$broken2,
			'keterangan2'=>$keterangan2,
			'noreferensi'=>$noreferensi,
			'jumlah'=>$jumlah,
			'keterangan'=>$keterangan,
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
	
	case 'update':
	
		// if($tipe=='OUT'){
			// $jumlah=$jumlah1-$jumlah2;
		// }else{
			// $jumlah=$jumlah2-$jumlah1;
		// }		
		
		if($jumlah<0){
			exit("Warning:Nilai mutasi dibawah 0");
		}
		
		if($tipe=='IN'){
			if($noreferensi==''){
				exit("Warning:No. Referensi harus terisi");
			}
		}
		
		if($tipe=='OUT'){
			if($noreferensi!=''){
				exit("Warning:No. Referensi harus kosong");
			}
		}
	
		$str = "update ".$dbname.".".$table." set 
			tanggalmulai='".$waktumulai."',
			tanggalselesai='".$waktuselesai."',
			unit='".$unit."',
			kodept='".$kodept."',
			kodetangki='".$kodetangki."',
			kodetangkitujuan='".$kodetangkitujuan."',
			nokontrak='".$nokontrak."',
			suhu1='".$suhu1."',
			tinggi1='".$tinggi1."',
			jumlah1='".$jumlah1."',
			ffa1='".$ffa1."',
			moisture1='".$moisture1."',
			dirt1='".$dirt1."',
			dobi1='".$dobi1."',
			broken1='".$broken1."',
			keterangan1='".$keterangan1."',
			suhu2='".$suhu2."',
			tinggi2='".$tinggi2."',
			jumlah2='".$jumlah2."',
			ffa2='".$ffa2."',
			moisture2='".$moisture2."',
			dirt2='".$dirt2."',
			dobi2='".$dobi2."',
			broken2='".$broken2."',
			keterangan2='".$keterangan2."',
			jumlah='".$jumlah."',
			keterangan='".$keterangan."',
			noreferensi='".$noreferensi."',
			updateby='".$_SESSION['standard']['userid']."'
			where notransaksi = '".$notransaksi."' ";#exit("error".$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	
	break;

   case'loaddata':
	
	$unit=$_SESSION['empl']['lokasitugas'];
   
   
	// if($tanggalselesaisch=='' or $tanggalmulaisch==''){
		// exit("Warning:Tanggal harus terisi");
	// }

	if($tanggalselesaisch!='' and $tanggalmulaisch!=''){
		$where.=" and tanggal between '".$tanggalmulaisch."' and '".$tanggalselesaisch."'";
	}

	if($notransaksisch!=''){
		$where.=" and notransaksi like '%".$notransaksisch."%'";
	}
	if($kodetangkisch!=''){
		$where.=" and kodetangki='".$kodetangkisch."'";
	}
   
	
		$limit = 10;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
		$maxdisplay=($page*$limit);
	
		
		$offset = $page * $limit;
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where unit='".$unit."' ".$where." ";
			// exit("Error".$str);
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar = $res->fetch();
            $jumrow = $bar['jumrow'];
			
			
		$no = 0;
		$no=$maxdisplay;
		$str = "select * from ".$dbname.".".$table."  where unit='".$unit."' ".$where."  order by tanggal desc limit " . $offset . "," . $limit . " ";
		// echo $str;
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td valign=top>".$bar['notransaksi']."</td>";
				$tab.="<td valign=top>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td valign=top>".$bar['unit']." - ".getNamaOrg($bar['unit'])."</td>";
				$tab.="<td valign=top>".$bar['kodept']." - ".getNamaOrg($bar['kodept'])."</td>";
				$tab.="<td valign=top>".$bar['tipe']."</td>";
				$tab.="<td valign=top>".$bar['kodetangki']."</td>";
				$tab.="<td valign=top align=right>".number_format($bar['jumlah1'])."</td>";
				$tab.="<td valign=top align=right>".number_format($bar['jumlah2'])."</td>";
				$tab.="<td valign=top align=right>".number_format($bar['jumlah'])."</td>";
				$tab.="<td valign=top>".getNamaKaryawan($bar['updateby'])."</td>";
		$tab.="<td align=center  valign=top>";
				if($bar['posting']==0){
					 $tab.="<img src=images/application/application_edit.png class=resicon  caption='Edit' 
				onclick=\"fillField('".$bar['notransaksi']."','".$bar['jenis']."');\">";
				
					$tab.="&nbsp;<img src=images/application/application_delete.png class=resicon  caption='Delete' 
						onclick=\"deleteht('".$bar['notransaksi']."');\">";		
$tab.="&nbsp;<img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['notransaksi']."','".$bar['noreferensi']."','".$bar['tipe']."');\">";							
				} else{
					$tab.="&nbsp;<img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting' >";
				}
		
			  $tab.="&nbsp;<img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print SPK ".$bar['notransaksi']."' onclick=\"printpdf('".$bar['notransaksi']."');\">";	
								$tab.="</td>";
            $tab.="</tr>";
        }
		$tab.="</table>";
		
		$str = "select count(*) as jumrow from ".$dbname.".".$table." where nokontrak=''";
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
            <tr><td colspan=21 align=center>
            <button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getpage()\">" . $isiRow . "</select>
            <button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td>
            </tr>";
        echo $tab . "####" . $footd;
	break;
	
	case'getEditData':
	
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$notransaksi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		
		// exit("Error:$str");
		
		echo $bar['notransaksi']."###".$bar['tipe']."###".tanggalnormal(substr($bar['tanggalmulai'],0,10))."###".substr($bar['tanggalmulai'],11,2)."###".substr($bar['tanggalmulai'],14,2)
		."###".tanggalnormal(substr($bar['tanggalselesai'],0,10))."###".substr($bar['tanggalselesai'],11,2)."###".substr($bar['tanggalselesai'],14,2)
		."###".$bar['unit']."###".$bar['kodetangki']."###".$bar['kodetangkitujuan']
		."###".$bar['suhu1']."###".$bar['tinggi1']."###".$bar['jumlah1']."###".$bar['ffa1']
		."###".$bar['moisture1']."###".$bar['dirt1']."###".$bar['dobi1']."###".$bar['broken1']."###".$bar['keterangan1']
		."###".$bar['suhu2']."###".$bar['tinggi2']."###".$bar['jumlah2']."###".$bar['ffa2']
		."###".$bar['moisture2']."###".$bar['dirt2']."###".$bar['dobi2']."###".$bar['broken2']."###".$bar['keterangan2']
		."###".$bar['nokontrak']."###".$bar['keterangan']."###".$bar['noreferensi']."###".tanggalnormal($bar['tanggal'])."###".$bar['jumlah']."###".$bar['kodept'];
		// exit("Error:a");
	break;
    default:
	break;
}
?>
