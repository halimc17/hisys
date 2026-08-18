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
$pelabuhantujuan = checkPostGet('pelabuhantujuan','');
$transportir = checkPostGet('transportir','');
$namakapal = checkPostGet('namakapal','');
$namaponton = checkPostGet('namaponton','');
$keteranganht = checkPostGet('keteranganht','');
$tanggalberangkat = tanggalsystemn(checkPostGet('tanggalberangkat',''));
					error_reporting(0);
	$jmberangkat = checkPostGet('jmberangkat','');
	$mnberangkat = checkPostGet('mnberangkat','');
	$waktuberangkat=$tanggalberangkat." ".$jmberangkat.":".$mnberangkat.":00";
$nokontrak = checkPostGet('nokontrak','');
$kodept = checkPostGet('kodept','');
$kodecustomer = checkPostGet('kodecustomer','');
$kodebarang = checkPostGet('kodebarang','');
$unit = checkPostGet('unit','');
$kodetangki = checkPostGet('kodetangki','');
$keterangandt = checkPostGet('keterangandt','');
$jumlah = checkPostGet('jumlah','');
$tanggalpasang1 = tanggalsystemn(checkPostGet('tanggalpasang1',''));
	$jmpasang1 = checkPostGet('jmpasang1','');
	$mnpasang1 = checkPostGet('mnpasang1','');
	$waktupasang1=$tanggalpasang1." ".$jmpasang1.":".$mnpasang1.":00";
$tanggalpasang2 = tanggalsystemn(checkPostGet('tanggalpasang2',''));
	$jmpasang2 = checkPostGet('jmpasang2','');
	$mnpasang2 = checkPostGet('mnpasang2','');
	$waktupasang2=$tanggalpasang2." ".$jmpasang2.":".$mnpasang2.":00";
$tanggalmuat1 = tanggalsystemn(checkPostGet('tanggalmuat1',''));
	$jmmuat1 = checkPostGet('jmmuat1','');
	$mnmuat1 = checkPostGet('mnmuat1','');
	$waktumuat1=$tanggalmuat1." ".$jmmuat1.":".$mnmuat1.":00";
$tanggalmuat2 = tanggalsystemn(checkPostGet('tanggalmuat2',''));
	$jmmuat2 = checkPostGet('jmmuat2','');
	$mnmuat2 = checkPostGet('mnmuat2','');
	$waktumuat2=$tanggalmuat2." ".$jmmuat2.":".$mnmuat2.":00";
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
$selisih = checkPostGet('selisih','');


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




$tanggalpasang1sch=checkPostGet('tanggalpasang1sch','');
$tanggalpasang2sch=checkPostGet('tanggalpasang2sch','');
if($tanggalpasang1sch==''){
	$tanggalpasang1sch='';
}else{
	$tanggalpasang1sch = tanggalsystemn(checkPostGet('tanggalpasang1sch',''));	
}

if($tanggalpasang2sch==''){
	$tanggalpasang2sch='';
}else{
	$tanggalpasang2sch=tanggalsystemn(checkPostGet('tanggalpasang2sch',''));
}


$tanggalmuat1sch=checkPostGet('tanggalmuat1sch','');
$tanggalmuat2sch=checkPostGet('tanggalmuat2sch','');
if($tanggalmuat1sch==''){
	$tanggalmuat1sch='';
}else{
	$tanggalmuat1sch = tanggalsystemn(checkPostGet('tanggalmuat1sch',''));	
}

if($tanggalmuat2sch==''){
	$tanggalmuat2sch='';
}else{
	$tanggalmuat2sch=tanggalsystemn(checkPostGet('tanggalmuat2sch',''));
}



$notransaksisch=checkPostGet('notransaksisch','');
$kodetangkisch=checkPostGet('kodetangkisch','');
$nokontraksch=checkPostGet('nokontraksch','');

$table='pmn_bapengiriman';

$optbuyer=$optbarang=$opttipe=$optunit=$opttangki="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";



$nmkomoditi=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kelompokbarang='400'");
$arrinisial=makeOption($dbname,'log_5masterbarang','kodebarang,inisial',"kelompokbarang='400'");
$nmpt=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PT'");
$namaorganisasi=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"(tipe='PABRIK' or tipe='BULKING')");
$nmcustsomer=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmkapalponton=makeOption($dbname,'pmn_5kapalponton','kode,nama');
$nmfranco=makeOption($dbname,'pmn_5franco','id_franco,franco_name');

// exit("Error:".$method);
switch ($method) {
	
	case'pdfexternal';
		
		$PK="PK";
		if (strpos($notransaksi,$PK)) {
			$str = "select sum(jumlah) as jumlah, unit, notransaksi, kodecustomer, kodebarang, tanggal, pelabuhantujuan, nokontrak, namaponton, namakapal, kodept, tanggalmuat1, tanggalmuat2, tanggalberangkat
				from ".$dbname.".".$table."  
				where notransaksi='".$notransaksi."' group by nokontrak ";
		}else{
			$str = "select * from ".$dbname.".".$table."  
				where notransaksi='".$notransaksi."'";
		}
		// echo $str;
		// exit();
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$unit=$bar['unit'];
			@$totalqty+=$bar['jumlah'];
			// @$arraytot[$bar['nokontrak']]=$bar['jumlah'];
			$notransaksi=$bar['notransaksi'];
			$kodecustomer=$bar['kodecustomer'];
			$kodebarang=$bar['kodebarang'];
			$tanggal=$bar['tanggal'];
			$pelabuhantujuan=$bar['pelabuhantujuan'];
			$arrnokontrak[$bar['nokontrak']]=$bar['nokontrak'];
			$namaponton=$nmkapalponton[$bar['namaponton']];
			$namakapal=$nmkapalponton[$bar['namakapal']];
			if($bar['kodept']!=''){
				$kodept=$bar['kodept'];
			}
			$tanggalmuat1=$bar['tanggalmuat1'];
			$tanggalmuat2=$bar['tanggalmuat2'];
			$tanggalberangkat=$bar['tanggalberangkat'];
		}
	// print_r($arrnokontrak);exit();
	
		$tab="<style>
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
	
	if($kodebarang=='40000001'){
		$cellpadding=0.75;
	}
	
	if($kodebarang=='40000002'){
		$cellpadding=3;
	}
	
		
	
		$judul="LAPORAN PEMUATAN ".$nmkomoditi[$kodebarang];
			
		$tab.="<div style='page-break-after: always;'>";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0>";
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight:bold;text-align:center;font-size:20px'><b><u>LAPORAN PEMUATAN ".$nmkomoditi[$kodebarang]."</u></b></td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight;text-align:center;font-size:14px'>No : ".$notransaksi."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
	
		
		$tab.="<br>";
		
		
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
			$tab.="<tr>";
				$tab.="<td>Telah dilakukan pemuatan : ".$nmkomoditi[$kodebarang]." sebagai berikut :</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		
		
		foreach($arrnokontrak as $nokontrak){
			@$listnokontrak.=$nokontrak."&nbsp;&nbsp;&nbsp;";
		}
		$nokontrak=
		
		$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
			$tab.="<tr>";
				$no=1;
				$tab.="<td style='width:20px' align=center valign=top>".$no.".</td>"; 
				$tab.="<td style='width:150px' valign=top>".$_SESSION['lang']['NoKontrak']."</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'>".$listnokontrak."</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='width:20px' align=center valign=top>".$no.".</td>"; 
				$tab.="<td style='width:150px' valign=top>".$_SESSION['lang']['Pembeli']."</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'>".@$nmcustsomer[$kodecustomer]."</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='width:20px' align=center valign=top>".$no.".</td>"; 
				$tab.="<td style='width:150px' valign=top>".$_SESSION['lang']['kuantitas']."</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'>".number_format($totalqty)." Kg</td>"; 
			$tab.="</tr>";
			
			// $no++;
			// $tab.="<tr>";
				// $tab.="<td style='width:20px' align=center valign=top>".$no.".</td>"; 
				// $tab.="<td style='width:150px' valign=top>".$_SESSION['lang']['komoditi']."</td>"; 
				// $tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				// $tab.="<td style='width:400px'>".$nmkomoditi[$kodebarang]."</td>"; 
			// $tab.="</tr>";
			
			
			
			if($namaponton!='' and $namakapal!=''){
				$kapalponton=$namakapal." / ".$namaponton;
			}else{
				$kapalponton=$namakapal." ".$namaponton;
			}
			
			
			$no++;
			$tab.="<tr>";
				$tab.="<td style='width:20px' align=center valign=top>".$no.".</td>"; 
				$tab.="<td style='width:150px' valign=top>".$_SESSION['lang']['namakapal']." / ".$_SESSION['lang']['namaponton']."</td>"; 
				$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
				$tab.="<td style='width:400px'>".$kapalponton."</td>"; 
			$tab.="</tr>";
			
			$no++;
			$tab.="<tr>";
				$tab.="<td align=center valign=top>".$no.".</td>"; 
				$tab.="<td valign=top>".$_SESSION['lang']['pelabuhantujuan']."</td>"; 
				$tab.="<td align=center valign=top>:</td>"; 
				$tab.="<td>".$nmfranco[$pelabuhantujuan]."</td>"; 
			$tab.="</tr>";
			
			// $no++;
			// $tab.="<tr>";
				// $tab.="<td align=center valign=top>".$no.".</td>"; 
				// $tab.="<td valign=top>".$_SESSION['lang']['']."</td>"; 
				// $tab.="<td align=center valign=top>:</td>"; 
				// $tab.="<td>".$a."</td>"; 
			// $tab.="</tr>";
			
		$tab.="</table>";
		
		
		
		
	
		
		#= if kodebarang 1
		if($kodebarang=='40000001'){
			$tab.="<br>";
			$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
				$tab.="<tr>";
					$tab.="<td>Pemuatan ke Kapal / Vesel :</td>"; 
				$tab.="</tr>";
			$tab.="</table>";
			$nourut=0;
			$str = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."' order by nokontrak asc";
			$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$nourut++;$tab.="<br>";
				$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
					$tab.="<tr>";
						$tab.="<td colspan=3>&nbsp;&nbsp;".$nourut.".1.&nbsp;&nbsp;".$_SESSION['lang']['NoKontrak']."</td>"; 
						$tab.="<td colspan=3>".$bar['nokontrak']."</td>"; 
					$tab.="</tr>";
					$tab.="<tr>";
						$tab.="<td colspan=3>&nbsp;&nbsp;".$nourut.".2.&nbsp;&nbsp;Pasang Selang</td>"; 
						$tab.="<td colspan=7>"; 
							$tab.="Tgl : ".tanggalnormal(substr($bar['tanggalpasang1'],0,10))." &nbsp;&nbsp;"; 
							$tab.="Jam : ".substr($bar['tanggalpasang1'],11,10)." &nbsp;&nbsp;"; 
							$tab.="&nbsp;&nbsp; S/D &nbsp;&nbsp;"; 
							$tab.="Tgl : ".tanggalnormal(substr($bar['tanggalpasang2'],0,10))." &nbsp;&nbsp;"; 
							$tab.="Jam : ".substr($bar['tanggalpasang2'],11,10)." &nbsp;&nbsp;"; 
						$tab.="</td>"; 
					$tab.="</tr>";
					
					$tab.="<tr>";
						$tab.="<td colspan=3>&nbsp;&nbsp;".$nourut.".3.&nbsp;&nbsp;Mulai Pompa</td>"; 
						$tab.="<td colspan=7>"; 
							$tab.="Tgl : ".tanggalnormal(substr($bar['tanggalmuat1'],0,10))." &nbsp;&nbsp;"; 
							$tab.="Jam : ".substr($bar['tanggalmuat1'],11,10)." &nbsp;&nbsp;"; 
							$tab.="&nbsp;&nbsp; S/D &nbsp;&nbsp;"; 
							$tab.="Tgl : ".tanggalnormal(substr($bar['tanggalmuat2'],0,10))." &nbsp;&nbsp;"; 
							$tab.="Jam : ".substr($bar['tanggalmuat2'],11,10)." &nbsp;&nbsp;"; 
						$tab.="</td>"; 
					$tab.="</tr>";
					
					$tab.="<tr>";
						$tab.="<td colspan=4>&nbsp;&nbsp;".$nourut.".4.&nbsp;&nbsp;Komoditi yang dimuat sbb :</td>"; 
					$tab.="</tr>";
					
					$tab.="<tr>";
						$tab.="<td> </td>"; 
					$tab.="</tr>";
					
					
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
									
					if(@$bar['ffa1']!=0){
						$tampilankualitas.=",FFA ".$bar['ffa1']." %";
					}
					if(@$bar['moisture1']!=0){
						$tampilankualitas.=", Moisture ".$bar['moisture1']." %";
					}
					if(@$bar['dirt1']!=0){
						$tampilankualitas.=", Dirt ".$bar['dirt1']." %";
					}
					if(@$bar['dobi1']!=0){
						$tampilankualitas.=", Dobi ".$bar['dobi1']." %";
					}
					if(@$bar['broken1']!=0){
						$tampilankualitas.=", Broken ".$bar['broken1']." %";
					}
					if(@$bar['impurities1']!=0){
						$tampilankualitas.=", Impurities ".$bar['impurities1']." %";
					}
					
					$tab.="<tr>";
						$tab.="<td colspan=3>&nbsp;&nbsp;".$nourut.".5.&nbsp;&nbsp;Final Pemuatan :</td>"; 
						$tab.="<td colspan=8>".number_format($bar['jumlah'])." Kg</td>"; 
					$tab.="</tr>";
				
					$tab.="<tr>";
						$tab.="<td colspan=11>&nbsp;&nbsp;".$nourut.".5.&nbsp;&nbsp;Hasil analisa sample dari Lab PT. Sucofindo  : ".$tampilankualitas."</td>"; 
					$tab.="</tr>";
					
				$tab.="</table>";
			}
		} 
		
		
		
			
		if($kodebarang=='40000002'){
			
			
			// $str = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."' order by nokontrak asc";
			// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// while($bar=$res->fetch()){
				
			$spasi="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";	
			$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
				$tab.="<tr>";
					$tab.="<td colspan=5>Pemuatan ke Kapal/TK</td>"; 
				$tab.="</tr>";
				
				$tab.="<tr>";
					$tab.="<td style='width:30px' align=center>&nbsp;</td>"; 
					$tab.="<td style='width:150px'> - Mulai Tanggal</td>"; 
					$tab.="<td style='width:10px'>:</td>"; 
					$tab.="<td>".tanggalnormal(substr($tanggalmuat1,0,10))." ".$spasi." Jam : ".substr($tanggalmuat1,11,10)."</td>"; 
				$tab.="</tr>";
				
				$tab.="<tr>";
					$tab.="<td>&nbsp;</td>"; 
					$tab.="<td>- Selesai Tanggal</td>"; 
					$tab.="<td>:</td>"; 
					$tab.="<td>".tanggalnormal(substr($tanggalmuat2,0,10))." ".$spasi." Jam : ".substr($tanggalmuat2,11,10)."</td>"; 
				$tab.="</tr>";
				
				$tab.="<tr>";
					$tab.="<td>&nbsp;</td>"; 
					$tab.="<td>- Quantity yang dimuat</td>"; 
					$tab.="<td>:</td>"; 
					$tab.="<td>".number_format($totalqty)." Kg</td>"; 
				$tab.="</tr>";
			$tab.="</table>";


			$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
				$tab.="<tr>";
					$no++;
					$tab.="<td style='width:20px' align=center valign=top>".$no.".</td>"; 
					$tab.="<td style='width:180px' valign=top>Kapal Berangkat</td>"; 
					$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
					$tab.="<td>".tanggalnormal(substr($tanggalberangkat,0,10))." ".$spasi." Jam : ".substr($tanggalberangkat,11,10)."</td>"; 
				$tab.="</tr>";
				$tab.="<tr>";
					$no++;
					$tab.="<td style='width:20px' align=center valign=top>".$no.".</td>"; 
					$tab.="<td style='width:180px' valign=top>Hasil Analisa Sample Surveyor</td>"; 
					$tab.="<td align=center style='width:10px' valign=top>:</td>"; 
					$tab.="<td></td>"; 
				$tab.="</tr>";
			$tab.="</table>";		
			
			
			$tab.="<table cellpadding=".$cellpadding." cellspacing=0 style='font-size:12px;' width=100% border=0>";
				// $str = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."' order by nokontrak asc";
				$PK="PK";
				if (strpos($notransaksi,$PK)) {
					$str = "select sum(jumlah) as jumlah, unit, notransaksi, kodecustomer, kodebarang, tanggal, pelabuhantujuan, nokontrak, namaponton, namakapal, kodept, tanggalmuat1, tanggalmuat2, tanggalberangkat
						from ".$dbname.".".$table."  
						where notransaksi='".$notransaksi."' group by nokontrak order by nokontrak asc";
				}else{
					$str = "select * from ".$dbname.".".$table."  
						where notransaksi='".$notransaksi."' order by nokontrak asc";
				}
				$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$tab.="<tr>";
						$tab.="<td style='width:100px'>&nbsp;</td>"; 
						$tab.="<td valign=top style='width:100px'>".$_SESSION['lang']['NoKontrak']."</td>"; 
						$tab.="<td valign=top align=center style='width:10px'>:</td>"; 
						$tab.="<td valign=top>".$bar['nokontrak']."</td>"; 
						$tab.="<td></td>"; 
					$tab.="</tr>";
					$tab.="<tr>";
						$tab.="<td style='width:100px'>&nbsp;</td>"; 
						$tab.="<td valign=top style='width:100px'>Brutto</td>"; 
						$tab.="<td valign=top align=center style='width:10px'>:</td>"; 
						$tab.="<td valign=top></td>"; 
						$tab.="<td></td>"; 
					$tab.="</tr>";
					$tab.="<tr>";
						$tab.="<td style='width:100px'>&nbsp;</td>"; 
						$tab.="<td valign=top style='width:100px'>Gross</td>"; 
						$tab.="<td valign=top align=center style='width:10px'>:</td>"; 
						$tab.="<td valign=top></td>"; 
						$tab.="<td></td>"; 
					$tab.="</tr>";
					$tab.="<tr>";
						$tab.="<td style='width:100px'>&nbsp;</td>"; 
						$tab.="<td valign=top style='width:100px'>Netto</td>"; 
						$tab.="<td valign=top align=center style='width:10px'>:</td>"; 
						$tab.="<td valign=top>".number_format($bar['jumlah'])." Kg</td>"; 
						$tab.="<td></td>"; 
					$tab.="</tr>";
					$tab.="<tr>";
						$tab.="<td style='width:100px'>&nbsp;</td>"; 
					$tab.="</tr>";
				}
			$tab.="</table>";		
			
		}
		
			
		
		$kota='Pontianak';
		$tab.="<table width=100% style='font-size:12px' cellpadding=".$cellpadding.">";
		
			$tab.="<tr>";
				$tab.="<td>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=6></td>"; 
				$tab.="<td colspan=3 align=center>".$kota.", ".tglnmbln(@$tanggal,'long','I')."</td>"; 
			$tab.="</tr>";
			// exit("Error:ASD");
			
		for($i=1;$i<6;$i++){
			$tab.="<tr>";
				$tab.="<td>&nbsp;</td>"; 
			$tab.="</tr>";
			}
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000' align=center>Nama & Cap</td>"; 
				$tab.="<td style='width:50px' align=center>&nbsp;</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000' align=center>Nama & Cap</td>"; 
				$tab.="<td style='width:50px' align=center>&nbsp;</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000' align=center>Security</td>"; 
				$tab.="<td style='width:50px' align=center>&nbsp;</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000' align=center>Staff</td>"; 
				$tab.="<td style='width:50px' align=center>&nbsp;</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000' align=center>Manager</td>"; 
			$tab.="</tr>";

		$tab.="</table>";	
		
		$tab.="<br>";
	
		$table='';
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	
	
	break;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
#### HERE GSW ####	
	case'pdfinternal':
		$nmKapal=makeOption($dbname,'pmn_5kapalponton','kode,nama');
		$nmPengiriman=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
	
		$str = "SELECT kodept, unit, notransaksi, kodebarang, tanggal, kodetangki, nokontrak, namakapal,
				kodecustomer, tanggalmuat1, tanggalmuat2, tanggalberangkat, tipe, transportir, keterangandt, keteranganht, 
				SUM(suhu1) as suhu1, SUM(tinggi1) as tinggi1, SUM(jumlah1) as jumlah1, SUM(jumlah2) as jumlah2, SUM(jumlah) as jumlah
				FROM ".$dbname.".pmn_bapengiriman 
				WHERE notransaksi = '".$notransaksi."'
				GROUP BY kodept";
		$result=fetchData($str);

		foreach ($result as $bar) {
			$unit=$bar['unit'];
			$notransaksi=$bar['notransaksi'];
			$kodebarang=$bar['kodebarang'];
			$tanggal=$bar['tanggal'];
			$kodetangki=$bar['kodetangki'];
			$nokontrak=$bar['nokontrak'];
			$noko[$bar['nokontrak']]=$bar['nokontrak'];

			$cb=count($noko);

			$suhu1=$bar['suhu1'];
			$tinggi1=$bar['tinggi1'];
			$jumlah1=$bar['jumlah1'];
			$jumlah2=$bar['jumlah2'];
			$jumlah=$bar['jumlah'];
			$kodecustomer=$bar['kodecustomer'];
			$tanggalmuat1=$bar['tanggalmuat1'];
			$tanggalmuat2=$bar['tanggalmuat2'];
			$tanggalberangkat=$bar['tanggalberangkat'];
			$tipe=$bar['tipe'];
			$transportir=$bar['transportir'];
			$keterangandt=$bar['keterangandt'];
			$keteranganht=$bar['keteranganht'];
			$kodept=$bar['kodept'];

			$namakapal=$bar['namakapal'];
			@$sawal[$kodetangki]=$jumlah1;
			@$salak[$kodetangki]=$jumlah2;
			@$mutasi[$kodetangki]=$jumlah;
		}
			
		
		// $str = "select sum(jumlah) as jumlah,notransaksi,unit,kodebarang,tanggal,posting from ".$dbname.".".$table."  
		// 	where unit='".$unitW."' ".$where."  group by notransaksi";
		// 	echo $str;exit();
		// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();
		// 	$unit=$bar['unit'];
		// 	$notransaksi=$bar['notransaksi'];
		// 	$kodebarang=$bar['kodebarang'];
		// 	$tanggal=$bar['tanggal'];
			
			
			
			
			
			
			// $kodetangki=$bar['kodetangki'];
			// $nokontrak=$bar['nokontrak'];
			// $suhu1=$bar['suhu1'];
			// $tinggi1=$bar['tinggi1'];
			// $jumlah1=$bar['jumlah1'];
			// $jumlah=$bar['jumlah'];
			// $ffa1=$bar['ffa1'];
			// $moisture1=$bar['moisture1'];
			// $dirt1=$bar['dirt1'];
			// $dobi1=$bar['dobi1'];
			// $broken1=$bar['broken1'];
			// $keterangan1=$bar['keterangan1'];
			// $keterangan2=$bar['keterangan2'];
			// $suhu2=$bar['suhu2'];
			// $tinggi2=$bar['tinggi2'];
			// $jumlah2=$bar['jumlah2'];
			// $ffa2=$bar['ffa2'];
			// $moisture2=$bar['moisture2'];
			// $dirt2=$bar['dirt2'];
			// $dobi2=$bar['dobi2'];
			// $broken2=$bar['broken2'];
			// $keterangan=$bar['keterangan'];
			// $keterangan2=$bar['keterangan2'];
			// $noreferensi=$bar['noreferensi'];
			
			if($tipe=='OUT'){
				$qty=$bar['jumlah1']-$bar['jumlah2'];
			}else{
				$qty=$bar['jumlah2']-$bar['jumlah1'];
			}
						
			// @$ket1[$kodetangki]=$keterangan1;
			// @$ket2[$kodetangki]=$keterangan2;
			// @$sawal[$kodetangki]=$jumlah1;
			// @$salak[$kodetangki]=$jumlah2;
			// @$mutasi[$kodetangki]=$jumlah;
			
			

			
			// $keterangan=$keterangan1." ".$keterangan2;
			
						// // print_r($keterangan1);
						// exit("Error:$keterangan");



		$str = "select * from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."' and kodetangki='".$kodetangki."'";
		// echo $str;exit();
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
		// echo $str;exit();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$komoditi=$bar['komoditi'];
			// echo "<pre>";
			// print_r($komoditi);
			// echo "</pre>";
			// exit();
			
		#notransaksi noreferensi	
		$str = "select * from ".$dbname.".".$table." where notransaksi='".@$noreferensi."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jumlahreferensi=$bar['jumlah'];	
		
		if(@$tipe=='IN'){
			$judul="BERITA ACARA PENERIMAAN ".$komoditi." TRANSFER";
		}else{
			$judul="BERITA ACARA PENGIRIMAN  ".$komoditi." TRANSFER"; ### PDF INTERNAL
		}
#### DIV Untuk perulangan ####	

	# code...
for ($i=1; $i < 3; $i++) { 
	echo $i;
	// exit();

	$tab.="<div style='page-break-after: always;'>";
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0>";
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight:bold;text-align:center;font-size:20px'><b><u>".$judul."</u></b></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight;text-align:center;font-size:14px'><b>PT. ".$kodept."</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='width:100px;font-weight;text-align:center;font-size:14px'><b>".$notransaksi."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
	
		$tab.="<br>";
		$tab.="<br>";
		
// $tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".tglnmbln(substr(@$tanggalmulai,0,10),'','')."</td>"; 

		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;'>Data Kapal</td>";
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Nama Kapal</td>"; ## GSW
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".@$nmKapal[$namakapal]."</td>"; 

				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>Pembeli</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$nmcustsomer[$kodecustomer]."</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Tanggal Tiba</td>"; ## GSW
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$namatangki[$kodetangkitujuan]."</td>"; 

				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>Tanggal Muat</td>"; ## GSW
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".tglnmbln(substr($tanggalmuat1,0,10),'','')."  Jam ".substr($tanggalmuat1,11,10)."</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>No. Kontrak</td>"; ## GSW
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$nokontrak."</td>"; 

				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>Tanggal Selesai</td>"; 
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".tglnmbln(substr($tanggalmuat2,0,10),'','')."  Jam ".substr($tanggalmuat2,11,10)."</td>"; 
			$tab.="</tr>";	
			$tab.="<tr>";
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Tgl. Berangkat</td>"; ## GSW
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".tglnmbln(substr($tanggalberangkat,0,10),'','')."  Jam ".substr($tanggalberangkat,11,10)."</td>"; 

				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>Qty Delivery</td>"; ## GSW
				$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".number_format($qty)." Kg</td>"; 
			$tab.="</tr>";	
		$tab.="</table>";
		
		
		$tab.="<br>Data Stock PK";
		
		foreach($result as $row){

			$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0>";
			$tab.="<tr>";
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'></td>"; ## GSW
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Tonnes</td>"; 
				$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Keterangan</td>"; 
			$tab.="</tr>";

			// $str = "select * from ".$dbname.".pabrik_5tangki where kodeorg='".$unit."' and komoditi='".$komoditi."'";
			// $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
			// $res->setFetchMode(PDO::FETCH_ASSOC);
			// while($bar=$res->fetch()){
			// 	$no++;
			// 	$tab.="<tr>";
			// 		$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' rowspan=3>Data Stok ".$no."".$namatangki[$bar['kodetangki']]."</td>"; 
			// 		$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Stock Awal</td>"; 
			// 		$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($sawal[$bar['kodetangki']])."</td>"; 
			// 		$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$ket1[$bar['kodetangki']]."</td>"; 
			// 	$tab.="</tr>";
			// 	$tab.="<tr>";
			// 		$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Stock Akhir</td>"; 
			// 		$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($salak[$bar['kodetangki']])."</td>"; 
			// 		$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$ket2[$bar['kodetangki']]."</td>"; 
			// 	$tab.="</tr>";
			// 	$tab.="<tr>";
			// 		$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Jumlah</td>"; 
			// 		$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($mutasi[$bar['kodetangki']])."</td>"; 
			// 		$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
			// 	$tab.="</tr>";
			// }
		
		
		// if($tipe=='IN'){
			// $tab.="<tr>";
			// 	$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top'>Stock Awal</td>"; 
			// 	$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($sawal[$kodetangki])."</td>"; 
			// 	$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Tonnes</td>"; 
			// 	$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Ket</td>";
			// $tab.="</tr>";
			// $tab.="<tr>";
			// 	$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' colspan=2>Transfer dari Tangki</td>"; 
			// 	$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($jumlahreferensi)."</td>"; 
			// $tab.="</tr>";
			// $tab.="<tr>";
			// 	$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top' colspan=2>Selisih</td>"; 
			// 	$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".number_format($jumlah-$jumlahreferensi)."</td>"; 
			// $tab.="</tr>";
		// }else{
			$arr = array("1" =>"Stock Awal","2" =>"Pengiriman","3" =>"Buyer Received","4" =>"Selisih","5" =>"Stock Akhir" );
			$arrCol = array("1" =>number_format($sawal[$row['kodetangki']])." Kg","2" =>$nmPengiriman[$row['transportir']],"3" =>"-","4" =>"-","5" =>number_format($salak[$row['kodetangki']])." Kg" );
			$arrKet = array("1" =>$keterangandt,"2" =>"","3" =>"","4" =>"","5" =>"" );
			
			foreach ($arr as $key => $value) {
				$tab.="<tr>";
					$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;vertical-align:top'>".$value."</td>"; 
					$tab.="<td style='text-align:right;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$arrCol[$key]."</td>";

					$tab.="<td style='text-align:center;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$arrKet[$key]."</td>";
				$tab.="</tr>";	
			}
			$tab.="</table><br>";
		}

		$tab.="<br>";

		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border=0>";
		$tab.="<tr>";
		$tab.="<td style='text-align:left;font-size:12px;border-left:0.5px solid #000000;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Keterangan</td>";
		$tab.="<td style='text-align:left;font-size:12px;border-left:0px solid #000000;border-top:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$keteranganht."</td>";
		$tab.="</tr>";

		foreach($result as $row){
			$str="select * from ".$dbname.".pabrik_stokbulking where kodept='".$row['kodept']."' and tanggal='".substr($row['tanggalmuat2'],0,10)."' ";
			$res=fetchData($str);
			foreach ($res as $bar) {
				// $pt=$bar['kodept'];
				$jmlpt = $bar['jumlah'];
			}

			$tab.="<tr>";
			$tab.="<td style='text-align:left;font-size:12px;border-left:0.5px solid #000000;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>PT. ".$row['kodept']."</td>"; ## GSW
			$tab.="<td style='text-align:left;font-size:12px;border-left:0px solid #000000;border-top:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$jmlpt."</td>"; 
			$tab.="</tr>";

			@$totjmlpt += $jmlpt;

			// $arrDet = array("ket" =>"Keterangan", $row['kodept'] => "PT. ".$row['kodept'], "tot" =>"Total" );	
			// $arrColDet = array("ket" =>$row['keteranganht'], $row['kodept'] =>number_format($jmlpt)." Kg","tot" =>number_format($tot+=$jmlpt)." Kg" );
			// // $arrColDet = array("ket" =>$keterangandt,"ksp" =>"ksp","sdk" =>"sdk","bpj" =>"bpj","tot" =>"total" );
			// foreach ($arrDet as $key1 => $value1) {
			// 	$tab.="<tr>";
			// 		$tab.="<td style='text-align:left;font-size:12px;border-left:0.5px solid #000000;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$value1."</td>"; ## GSW
			// 		$tab.="<td style='text-align:left;font-size:12px;border-left:0px solid #000000;border-top:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$arrColDet[$key1]."</td>"; 
			// 	$tab.="</tr>";	
			// }
		}

		$tab.="<tr>";
		$tab.="<td style='text-align:left;font-size:12px;border-left:0.5px solid #000000;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Total</td>";
		$tab.="<td style='text-align:left;font-size:12px;border-left:0px solid #000000;border-top:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$totjmlpt."</td>";
		$tab.="</tr>";
		// echo "<pre>";
		// print_r($arrColDet[$key1]);
		// echo "</pre>";
		// exit();
			// $tab.="<tr>";
			// 	$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>PT. KSP</td>"; ## GSW
			// 	$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$namatangki[$kodetangkitujuan]."</td>"; 

			// $tab.="</tr>";	
			// $tab.="<tr>";
			// 	$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>PT. SDK</td>"; ## GSW
			// 	$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$nokontrak."</td>"; 

			// $tab.="</tr>";	
			// $tab.="<tr>";
			// 	$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>PT. BPJ</td>"; ## GSW
			// 	$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$nokontrak."</td>"; 

			// $tab.="</tr>";
			// $tab.="<tr>";
			// 	$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>Total</td>"; ## GSW
			// 	$tab.="<td style='text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>: ".$nokontrak."</td>"; 
			// $tab.="</tr>";	
		$tab.="</table>";

####
		$tab.="<br>";
		
		// $tab.="<table border=0 width:100>";
		// $tab.="<tr>";
		// 	$tab.="<td style='width:75px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;vertical-align:top'>Keterangan</td>"; 
		// 	$tab.="<td style='width:600px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'>".$keterangan."</td>"; 
		// $tab.="</tr>";
		// $tab.="<tr>";
		// 	$tab.="<td style='width:75px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-bottom:0px solid #000000;vertical-align:top'>&nbsp;</td>"; 
		// 	$tab.="<td style='width:600px;text-align:left;font-size:12px;border-top:0px solid #000000;border-left:0px solid #000000;border-right:0px solid #000000;border-bottom:0.5px solid #000000;'></td>"; 
		// $tab.="</tr>";
		// $tab.="</table>";
	
		$tab.="<br>";
	
		
		$cellpadding=2;
		
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
				$tab.="<td align=center>".ucwords(strtolower($namaorganisasi[$unit]))." BLK Supervisor</td>"; 
				$tab.="<td style='width:150px' align=center>&nbsp;</td>"; 
				$tab.="<td align=center>Petugas Bulking ".ucwords(strtolower($namaorganisasi[$unit]))."</td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		
	$tab.="</div>";
}
#### DIV Untuk perulangan ####

		
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
		
## START LEMBAR KEDUA ####		
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
## END LEMBAR KEDUA ####		
		
		
	
	
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	

	break;
	
	
	
	
	
	
	 case'carinokontrak':
        echo"<fieldset  style='float:left;' >
                <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['NoKontrak']."</legend>
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>".$_SESSION['lang']['NoKontrak']."</td>
                            <td colspan=5>: 
								<input type=text id=daftarnokontrak value='".$nokontrak."' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
								<button class=mybutton onclick=caridaftarnokontrak()>cari</button>
                            <td>
                        <tr>
                    </table>
                    <table>
                    <thead>
                    <tr class=rowheader>
                            <td align=center>".$_SESSION['lang']['nourut']."</td>
                            <td align=center>".$_SESSION['lang']['NoKontrak']."</td>
                            <td align=center>".$_SESSION['lang']['tanggal']."<br>".$_SESSION['lang']['kontrak']."</td>
                            <td align=center>".$_SESSION['lang']['Pembeli']."</td>
                            <td align=center>".$_SESSION['lang']['kuantitas']."<br>".$_SESSION['lang']['timbangan']."<br>(Khusus IBW)</td>
                    </tr></thead>";
					$no=0;
                    if($nokontrak!=''){
						$str="select * from ".$dbname.".pmn_kontrakjual where nokontrak like '%".$nokontrak."%'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch()){
							
							$sisaqty=0;
							if($unit=='KSBW'){
								#= jika PK dan unit adalah KSBW maka ambil data timbangan
								$str1="select sum(beratbersih) as qty from ".$dbname.".pabrik_timbangan where
									nokontrak='".$bar['nokontrak']."' and tanggal <= '".substr($tanggal,0,10)."' ";
									// echo $str1;
								$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
								$res1->setFetchMode(PDO::FETCH_ASSOC);
								$bar1=$res1->fetch();
								
								$str2="select sum(jumlah) as qty from ".$dbname.".pmn_bapengiriman where
									nokontrak='".$bar['nokontrak']."' and tanggal <= '".substr($tanggal,0,10)."' ";
									// echo $str1;
								$res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
								$res2->setFetchMode(PDO::FETCH_ASSOC);
								$bar2=$res2->fetch();
								
								#= qty sisa
								$sisaqty=$bar1['qty']-$bar2['qty'];
							}
							
                            $no++;
                            echo"<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movecarinokontrak('".$bar['nokontrak']."','".$bar['kodept']."','".$bar['tanggalkontrak']."','".$bar['koderekanan']."','".$bar['kodebarang']."','".$sisaqty."');\">
								<td>".$no."</td>
								<td>".$bar['nokontrak']."</td> 
								<td>".tanggalnormal($bar['tanggalkontrak'])."</td> 
								<td>".$bar['koderekanan']."</td> 
								<td align=right>".number_format($sisaqty)."</td> 
                            </tr>";
                        }
					}
                    echo"</table>
        </fieldset>";
	
    break; 
	
	
	
	
	
	
	
	case'posting':
		$str = "update ".$dbname.".".$table." set 
				posting='1',postingby='".$_SESSION['standard']['userid']."' where notransaksi='".$notransaksi."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}
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
		$notransaksi = generatenobapengiriman();	
		echo $notransaksi;
	break;
		
	case 'insert':
		$str="select * from ".$dbname.".pmn_bapengiriman where notransaksi = '".$notransaksi."' and nokontrak = '".$nokontrak."' and kodetangki = '".$kodetangki."' ";
		$res=fetchData($str);
		$count=count($res);
		if ($count>0) {
			exit('Warning : Data detail sudah ada, mohon dicek dilist Data !');
		}
	
		$data = array(
			'notransaksi'=>$notransaksi,
			'tanggal'=>$tanggal,
			'pelabuhantujuan'=>$pelabuhantujuan,
			'transportir'=>$transportir,
			'namakapal'=>$namakapal,
			'namaponton'=>$namaponton,
			'keteranganht'=>$keteranganht,
			'tanggalberangkat'=>$waktuberangkat,
			'nokontrak'=>$nokontrak,
			'kodept'=>$kodept,
			'kodecustomer'=>$kodecustomer,
			'kodebarang'=>$kodebarang,
			'unit'=>$unit,
			'kodetangki'=>$kodetangki,
			'keterangandt'=>$keterangandt,
			'jumlah'=>$jumlah,
			'tanggalpasang1'=>$waktupasang1,
			'tanggalpasang2'=>$waktupasang2,
			'tanggalmuat1'=>$waktumuat1,
			'tanggalmuat2'=>$waktumuat2,
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
			'selisih'=>$selisih,
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
		// namauser	kodeorganisasi
	   
   		// cek apakah user biasa pindah2?
		/*
   		$loktrakhir='';
		$str = "select namauser, kodeorganisasi from ".$dbname.".user_orgdetail where namauser = '".$_SESSION['standard']['username']."' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$loktrakhir=$bar['kodeorganisasi'];
		}
		if($loktrakhir!=''){ 	// kalo bisa pindah2, pake ini
			$where=" unit in (select kodeorganisasi from ".$dbname.".user_orgdetail where namauser='".$_SESSION['standard']['username']."')";
		}else{ 					// kalo ga bisa pindah2, ambil lokasi tugas aja
			$where=" unit = '".$_SESSION['empl']['lokasitugas']."' ";
		}
		*/
		
		$arrunit=array();
		$arrunit=getOrgDetail(13);
		foreach($arrunit as $val=>$nama){
			$dtunit[$val]=$val;
		} 
		
		$where="1=1 and  unit in ('".implode("','",$dtunit)."') ";
		
		if($notransaksisch!=''){
			$where.=" and notransaksi like '%".$notransaksisch."%'";
		}
		
		if($nokontraksch!=''){
			$where.=" and nokontrak like '%".$nokontraksch."%'";
		}
		
		if($tanggalselesaisch!='' and $tanggalmulaisch!=''){
			$where.=" and tanggal between '".$tanggalmulaisch."' and '".$tanggalselesaisch."'";
		}
		
			
		if($tanggalmuat1sch!='' and $tanggalmuat2sch!=''){
			$where.=" and tanggalmuat1 >= '".$tanggalmuat1sch."' and tanggalmuat2 <= '".$tanggalmuat2sch."'";
		}
		
			
		if($tanggalpasang1sch!='' and $tanggalpasang2sch!=''){
			$where.=" and tanggalpasang1 >= '".$tanggalpasang1sch."' and tanggalpasang2 <='".$tanggalpasang2sch."'";
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
		
		$no = 0;
		$no=$maxdisplay;
		$str = "select sum(jumlah) as jumlah,notransaksi,unit,kodebarang,tanggal,posting,updateby from ".$dbname.".".$table."  
			where ".$where." group by notransaksi  order by tanggal desc limit " . $offset . "," . $limit . " ";
			// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['notransaksi']."</td>";
				$tab.="<td>".tanggalnormal($bar['tanggal'])."</td>";
				$tab.="<td>".$bar['unit']."</td>";
				$tab.="<td>".$nmkomoditi[$bar['kodebarang']]."</td>";
				$tab.="<td align=right>".number_format($bar['jumlah'])."</td>";
				$tab.="<td>".getNamaKaryawan($bar['updateby'])."</td>";
				
				if($bar['posting']==0){
					$tab.="<td align=center width=25px><img src=images/application/application_edit.png class=resicon  caption='Edit' 
						onclick=\"editht('".$bar['notransaksi']."');\"></td>";
					$tab.="<td align=center width=25px><img src=images/application/application_delete.png class=resicon  caption='Delete' 
						onclick=\"deleteht('".$bar['notransaksi']."','".@$bar['jenis']."');\"></td>";		
					$tab.="<td align=center width=25px><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' onclick=\"posting('".$bar['notransaksi']."');\"></td>";							
				} else{
					$tab.="<td valign=top align=center width=25px></td>";
					$tab.="<td valign=top align=center width=25px></td>";
					$tab.="<td align=center width=25px><img src=images/icons/04/16/02.png class=resicon class=zImgBtn height='30'  title='Posting' ></td>";
				}
				$tab.="<td align=center width=25px><img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print Internal ".$bar['notransaksi']."' onclick=\"pdfinternal('".$bar['notransaksi']."');\"></td>";	
				$tab.="<td align=center width=25px><img src=images/pdf.jpg class=resicon  caption='PDF'  title='Print External ".$bar['notransaksi']."' onclick=\"pdfexternal('".$bar['notransaksi']."');\">";	
				$tab.="</td>";
			$tab.="</tr>";
        }
		
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
		
		echo $bar['notransaksi']."###".tanggalnormal($bar['tanggal'])."###".$bar['pelabuhantujuan']
		."###".$bar['namakapal']."###".$bar['namaponton']."###".$bar['keteranganht']
		."###".tanggalnormal(substr($bar['tanggalberangkat'],0,10))."###".substr($bar['tanggalberangkat'],11,2)."###".substr($bar['tanggalberangkat'],14,2)
		."###".$bar['unit']."###".$bar['kodebarang']."###".$bar['transportir'];
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
			selisih='".$selisih."',
			updateby='".$_SESSION['standard']['userid']."'
			where notransaksi = '".$notransaksi."' and nokontrak='".$nokontrak."' and kodetangki='".$kodetangki."'";#exit("error".$str);
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			echo " Gagal," . addslashes($e->getMessage());
		}	
	break;
	
	case'geteditdt':
	
		$str = "select * from ".$dbname.".".$table." where notransaksi='".$notransaksi."' and nokontrak='".$nokontrak."' and kodetangki='".$kodetangki."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar = $res->fetch();
		
		// exit("Error:$str");
		
		echo $bar['nokontrak']."###".$bar['kodecustomer']."###".$bar['kodept']
		."###".$bar['kodetangki']."###".$bar['keterangandt']."###".number_format($bar['jumlah'])
		."###".tanggalnormal(substr($bar['tanggalpasang1'],0,10))."###".substr($bar['tanggalpasang1'],11,2)."###".substr($bar['tanggalpasang1'],14,2)
		."###".tanggalnormal(substr($bar['tanggalpasang2'],0,10))."###".substr($bar['tanggalpasang2'],11,2)."###".substr($bar['tanggalpasang2'],14,2)
		."###".tanggalnormal(substr($bar['tanggalmuat1'],0,10))."###".substr($bar['tanggalmuat1'],11,2)."###".substr($bar['tanggalmuat1'],14,2)
		."###".tanggalnormal(substr($bar['tanggalmuat2'],0,10))."###".substr($bar['tanggalmuat2'],11,2)."###".substr($bar['tanggalmuat2'],14,2)
		."###".$bar['suhu1']."###".$bar['tinggi1']."###".number_format($bar['jumlah1'])."###".$bar['ffa1']
		."###".$bar['moisture1']."###".$bar['dirt1']."###".$bar['dobi1']."###".$bar['broken1']
		."###".$bar['suhu2']."###".$bar['tinggi2']."###".number_format($bar['jumlah2'])."###".$bar['ffa2']
		."###".$bar['moisture2']."###".$bar['dirt2']."###".$bar['dobi2']."###".$bar['broken2']."###".$bar['selisih'];
		// exit("Error:a");
	break;
	
	case'loaddatadt':
		$str = "select * from ".$dbname.".".$table."  where notransaksi='".$notransaksi."' ";
		$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td>".$bar['nokontrak']."</td>";
				$tab.="<td>".$bar['kodept']."</td>";
				$tab.="<td>".$bar['kodecustomer']."</td>";
				$tab.="<td>".$bar['kodetangki']."</td>";
				$tab.="<td>".number_format($bar['jumlah'])."</td>";
				$tab.="<td align=center  valign=top>";
				$tab.="<img src=images/application/application_edit.png class=resicon  caption='Edit' 
				onclick=\"editdt('".$bar['notransaksi']."','".$bar['nokontrak']."','".$bar['kodetangki']."');\">";
				$tab.="&nbsp;<img src=images/application/application_delete.png class=resicon  caption='Delete' 
				onclick=\"deletedt('".$bar['notransaksi']."','".$bar['nokontrak']."','".$bar['kodetangki']."');\">";	
				$tab.="</td>";
            $tab.="</tr>";
			@$tjumlah+=$bar['jumlah'];
        }
		$tab.="<tr class=rowcontent>";
		$tab.="<td align=center colspan=5>".$_SESSION['lang']['total']."</td>";
		$tab.="<td>".number_format($tjumlah)."</td>";
		$tab.="<td></td>";
		echo $tab;
	break;
	
	case'deletedt':
		$str = "delete from ".$dbname.".".$table." where 
			notransaksi='".$notransaksi."' and nokontrak='".$nokontrak."' and kodetangki='".$kodetangki."'";
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
