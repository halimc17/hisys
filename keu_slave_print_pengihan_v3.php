<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('lib/fpdf.php');
include_once('lib/zMysql.php');
include_once('lib/terbilang.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$table = $_GET['table'];
$column = $_GET['column'];
$where = $_GET['cond'];
$tglInv = "";
$urlefil=checkPostGet('urlefil','0');
$optnmcust=makeOption($dbname, 'pmn_4customer', 'kodecustomer,namacustomer');
$optnmakun=makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$nmkapal=makeOption($dbname, 'pmn_5kapalponton', 'kode,nama');


// $nmPt=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
//=============
		$str = "select * from ".$dbname.".".$table." where noinvoice='".$column."' ";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$kodept=$bar['kodept'];
			$kodecustomer=$bar['kodecustomer'];
			$nokontrak=$bar['nokontrak'];
			$noinvoice=$bar['noinvoice'];
			$tanggalinvoice=$bar['tanggal'];
			$kuantitas=$bar['kuantitas'];
			$kodebarang=$bar['kodebarang'];
			$nilaiinvoice=$bar['nilaiinvoice'];
			$matauang=$bar['matauang'];
			$noakun=$bar['bayarke'];
			$ttd=$bar['ttd'];
			$jenis=$bar['jenis'];
			$berikat=$bar['berikat'];
			$createby=$bar['createby'];
			$jenisinvoice=$bar['jenisinvoice'];
			$transport=$bar['transport'];
			// $hargasatuan=@($nilaiinvoice/$kuantitas);
			$nilaippn=$bar['nilaippn'];
			$keterangantambahan=$bar['keterangantambahan'];
			$nodo=$bar['nodo'];
			$nofakturpajak=$bar['nofakturpajak'];
			$noreferensi=$bar['noreferensi'];
			$npwppt=$bar['npwpunit'];
			// $totalnilaiinvoice=$nilaiinvoice+$nilaippn;
		
			
			
				$keteranganpinalti1=$bar['keterangan1'];
				$rupiahpinalti1=$bar['rupiah1']*-1;
				$keteranganpinalti2=$bar['keterangan2'];
				$rupiahpinalti2=$bar['rupiah2']*-1;
				$keteranganpinalti3=$bar['keterangan3'];
				$rupiahpinalti3=$bar['rupiah3']*-1;
				$keteranganpinalti4=$bar['keterangan4'];
				$rupiahpinalti4=$bar['rupiah4']*-1;
				$keteranganpinalti5=$bar['keterangan5'];
				$rupiahpinalti5=$bar['rupiah5']*-1;
				$keteranganpinalti6=$bar['keterangan6'];
				$rupiahpinalti6=$bar['rupiah6']*-1;
				$keteranganpinalti7=$bar['keterangan7'];
				$rupiahpinalti7=$bar['rupiah7']*-1;
				$keteranganpinalti8=$bar['keterangan8'];
				$rupiahpinalti8=$bar['rupiah8'];
				
				$totalpinalti=$rupiahpinalti1+$rupiahpinalti2+$rupiahpinalti3+$rupiahpinalti4+$rupiahpinalti5+$rupiahpinalti6+$rupiahpinalti7+$rupiahpinalti8;
				
				// exit("Error:".$ppnpinalti);
		
			
			if($tanggalinvoice<'2022-04-01'){
			$persentasesatu='1.1';
			$persentasedua='0.1';
			$persentasekata='10%';
		}else{
			$persentasesatu='1.11';
			$persentasedua='0.11';
			$persentasekata='11%';
		}	
			
		$str="select count(*) as jumlah,sum(nilaiinvoice) as nilaiinvoice from ".$dbname.".keu_penagihanht 
				where nokontrak='".$nokontrak."' and tanggal<'".$tanggalinvoice."' ";
		// echo $str;exit();
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);	
		$bar=$res->fetch();
			$jumlahinvoice=$bar['jumlah'];
			$jumlahrpinvoice=$bar['nilaiinvoice'];
		
		#= data datakaryawan
		$str="select * from ".$dbname.".datakaryawan where karyawanid='".$ttd."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$namakaryawan=$bar['namakaryawan'];
			
		#= data kontrak
		$str="select * from ".$dbname.".pmn_kontrakjual where nokontrak='".$nokontrak."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$tanggalkontrak=$bar['tanggalkontrak'];
			$kuantitaskontrak=$bar['kuantitaskontrak'];
			$franco=$bar['franco'];
			$hargasatuan=$bar['hargasatuan'];
			$tipepenjualan=$bar['tipepenjualan'];
			$ppnkontrak=$bar['ppn'];
			if($ppnkontrak==1){
				$hargasatuan=$hargasatuan/$persentasesatu;
			}
			
		$str="select sum(jumlah) as kg from ".$dbname.".pmn_bast where nokontrak='".$nokontrak."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			if($jenisinvoice=='PL'){
				$kuantitas=$bar['kg'];
			}
			
			
		#= data nodo	
		
		$str="select * from ".$dbname.".keu_penagihandt_kapalponton where noinvoice='".$column."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			if($bar['jenis']=='KPL'){
				$namakapal.="<br> ".$nmkapal[$bar['kode']];
			}
			if($bar['jenis']=='PNT'){
				$namaponton.="<br> ".$nmkapal[$bar['kode']];
			}
			if($bar['jenis']=='TRK'){
				$namatruck.="<br> ".$nmkapal[$bar['kode']];
			}
		}
			
		
			
		#= data franco	
		$str="select * from ".$dbname.".pmn_5franco where id_franco='".$franco."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$namafranco=$bar['franco_name'];
			
			if($namafranco==''){
				$namafranco='&nbsp;';
			}
		
		$str="select sum(nilairupiah) as totx from ".$dbname.".keu_penagihandt where noinvoice='".$column."' group by noinvoice";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$totnilrup=$bar['totx'];

		#= query data pt	
		$str="select * from ".$dbname.".organisasi where kodeorganisasi='".$kodept."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$alamatpt=$bar['alamat'];
			$wilayahkotapt=$bar['wilayahkota'];
			$kodepospt=$bar['kodepos'];
			$teleponpt=$bar['telepon'];
			$namapt=$bar['namaorganisasi'];
			
		#= npwp	
		// $str="select * from ".$dbname.".setup_org_npwp where kodeorg='".$kodept."' and inisial='JKT'";
		// $str="select * from ".$dbname.".setup_org_npwp where kodeorg='".$kodept."' and defaultppn='1'";
		// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		// $res->setFetchMode(PDO::FETCH_ASSOC);
		// $bar=$res->fetch();	
			// $npwppt=$bar['npwp'];

		#= query akun bank
		$str="select * from ".$dbname.".keu_5akunbank where noakun='".$noakun."'";
		
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$namabank=$bar['namabank'];
			$rekening=$bar['rekening'];
			$cabang=$bar['cabang'];
			$atasnama =$bar['atasnama'];

			
		#= query akun bank
		$str="select * from ".$dbname.".keu_5daftarbank where kodebank='".$namabank."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$namabank=$bar['namabank'];
			
		$str = "select * from ".$dbname.".pmn_4customer where kodecustomer='".$kodecustomer."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$alamatcustomer=$bar['alamat'];	
			$namacustomer=$bar['namacustomer'];		
			$telpcustomer=$bar['telepon'];	
			$faxcustomer=$bar['fax'];		
			$kotacustomer=$bar['kota'];		
			
		#= query nama barang	
		$str="select * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$namabarang[$bar['kodebarang']]=$bar['namabarang'];
			$satuanbarang[$bar['kodebarang']]=$bar['satuan'];
		}

		#= query mata uang
		$str="select * from ".$dbname.".setup_matauang";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$namamatauang[$bar['kode']]=$bar['matauang'];
		}
	
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
				
			</style>";
			
		
			/*
			background-color: #03a9f4;
					color: white;
					text-align: center;
					line-height: 35px;
			*/
			
			
		$border=0;	
		$cellpadding=1.5;
		
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border='".$border."'>";
			$tab.="<tr>";
				$tab.="<td colspan=2 style='width:300px;font-weight:bold;text-align:left;font-size:20px'><b>".$namapt."</b></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:150px;font-weight;text-align:left;font-size:12px'>".$alamatpt."</td>"; 
				$tab.="<td style='width:200px;font-weight:bold;text-align:left;font-size:14px'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
			if($kodepospt==''){
				$tab.="<td style='width:10px;font-weight;text-align:left;font-size:12px'>".$wilayahkotapt."</td>"; 
			}else{
				$tab.="<td style='width:10px;font-weight;text-align:left;font-size:12px'>".$wilayahkotapt." - ".$kodepospt."</td>"; 
			}
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:200px;font-weight:bold;text-align:left;font-size:14px'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
			
			
					// $len=$ptptg='';
					// @$ptptgnpwp = $npwppt;
					// $len=strlen($ptptgnpwp);
					// for ($i=0;$i<$len;$i++){
						// $tab.="<td style=width:15px align=center>".substr($ptptgnpwp,$i,1)."</td>";
					// }
			
				$tab.="<td colspan=2 style='width:10px;font-weight;text-align:center;font-size:14px'>"; 
				
				$tab.="<table>";
					$tab.="<tr>";	
					$tab.="<td  style='border:0;width:15px' align=center >N.P.W.P&nbsp;&nbsp;:&nbsp;&nbsp;</td>";
					$len=$ptptg='';
					@$ptptgnpwp = $npwppt;
					$len=strlen($ptptgnpwp);
					for ($i=0;$i<$len;$i++){
						if(substr($ptptgnpwp,$i,1)=='.' || substr($ptptgnpwp,$i,1)=='-'){
							$tab.="<td  style='border:0;width:12px' align=center >&nbsp;</td>";
						} else {
							$tab.="<td  style='border:0.5px solid #000000;' align=center;width:12px>".substr($ptptgnpwp,$i,1)."</td>";
						}
					}
					$tab.="</tr>";
				$tab.="</table>";
				
				
				$tab.="</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:200px;font-weight:bold;text-align:left;font-size:14px'>&nbsp;</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td colspan=2 style='width:10px;font-weight;text-align:center;font-size:14px'><b>I N V O I C E</b></td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		
		$tab.="<br>";
		
		$border=0;	
		$cellpadding=2;
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border='".$border."'>";
			$tab.="<tr>";
				$tab.="<td rowspan=2 valign=top style='width:25px;font-weight;text-align:left;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;'>TO : </td>"; 
				$tab.="<td rowspan=2 colspan=2 valign=top style='width:250px;font-weight;text-align:left;font-size:12px;border-right:0.5px solid #000000;border-top:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$namacustomer."<br>".$alamatcustomer."</td>"; 
				$tab.="<td colspan=2 style='width:100px;font-weight;text-align:center;font-size:12px;border-top:0.5px solid #000000;border:0.5px solid #000000;'>Ref. No<br>".$noinvoice."<br>".$nofakturpajak."</td>"; 
				$tab.="<td style='width:100px;font-weight;text-align:center;font-size:12px;border:0.5px solid #000000'>Date :<br>".tglnmbln($tanggalinvoice,'i','long')."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
			
			
			#= indra
			if($transport=='AIR'){
				$kataairdarat='SHIPMENT';
				$katatransport='VESSEL';
			}else{
				$kataairdarat='TRUCKING';
				$katatransport='TRUCKING';
			}
			
			if($jenisinvoice=='UM'){
				$tab.="<td colspan=2 style='font-weight;text-align:center;font-size:12px;valign:center;border:0.5px solid #000000'>".$kataairdarat." FROM :<br>&nbsp;</td>"; 
			}else{
				$tab.="<td colspan=2 style='font-weight;text-align:center;font-size:12px;valign:center;border:0.5px solid #000000'>".$kataairdarat." FROM :<br>".$namafranco."</td>"; 
			}
			$tab.="<td style='font-weight;text-align:center;font-size:12px;valign:center;border:0.5px solid #000000'>".$tipepenjualan."<br>".$namafranco."</td>"; 
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td colspan=2 style='font-weight;text-align:center;valign:top;font-size:12px;border:0.5px solid #000000'>CONTRACT NO :<br>".$nokontrak."</td>"; 
				$tab.="<td style='font-weight;text-align:center;valign:top;font-size:12px;border:0.5px solid #000000'>DATE OF CONTRACT :<br>".tglnmbln($tanggalkontrak,'i','long')."</td>"; 
				$tab.="<td colspan=2 style='font-weight;text-align:center;valign:top;font-size:12px;border:0.5px solid #000000'>".$katatransport." NAME : ".$namakapal." ".$namaponton." ".$namatruck."</td>"; 
				$tab.="<td style='font-weight;text-align:center;valign:top;font-size:12px;border:0.5px solid #000000;border:0.5px solid #000000'>".$kataairdarat." NO :<br>1</td>"; 
			$tab.="</tr>";
		
			$tab.="<tr>";
				$tab.="<td style='font-weight;text-align:center;font-size:12px;valign:center;border:0.5px solid #000000'>ITEM</td>"; 
				$tab.="<td style='font-weight;text-align:center;font-size:12px;valign:center;border:0.5px solid #000000'>QUANTITY<br>(Kg)</td>"; 
				$tab.="<td style='font-weight;text-align:center;font-size:12px;valign:center;border:0.5px solid #000000' colspan=3>DESCRIPTION</td>"; 
				// $tab.="<td style='font-weight;text-align:center;font-size:12px;valign:center;border:0.5px solid #000000'>JUMLAH<br>(RP)</td>"; 
				$tab.="<td style='font-weight;text-align:center;font-size:12px;valign:center;border:0.5px solid #000000'>AMOUNT</td>"; 
			$tab.="</tr>";
			
			
			$tab.="<tr>";
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>&nbsp;</td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=3></td>";  
				// $tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				$tab.="<td style='border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
			$tab.="</tr>";
			
		
			
			#= buat termin dengan pengecekan sudah ada invoice atau belum
			#= algoritma select count(*) as jumlah where nokontrak and tanggal invoice < tanggal param
			if($kodebarang=='40000003'){ // update request pak surminto: 2021-08-16 DI SINI KHUSUS TBS, SELAINNYA TETEP PAKE keu_slave_print_penagihan.php
				#= jika TBS
				// $str="select 
				// 	sum(totalrp) as totalrp,
				// 	sum(kgnetto) as kgnetto,
				// 	periode,rpkg,intiplasma,tanggalreferensi
				// from ".$dbname.".keu_penagihandt where noinvoice='".$noinvoice."' group by periode,rpkg";
				// $str="select * from ".$dbname.".keu_penagihandt where noinvoice='".$noinvoice."' ";
				$str = "select sum(totalrp) as totalrp,sum(kgnetto) as kgnetto,notransaksi,tanggalreferensi,tanggaltbs1,tanggaltbs2,intiplasma from ".$dbname.".keu_penagihandt where noinvoice='".$noinvoice."' group by notransaksi ";
				// echo $str;exit("Error:A");
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					// $arrperdt[$bar['periode']]=$bar['periode'];
					// $arrrpkgdt[$bar['rpkg']]=$bar['rpkg'];
					// $listrpkgdt[$bar['periode']][$bar['rpkg']]=$bar['rpkg'];
					// $totalkgnetto[$bar['periode']][$bar['rpkg']]=$bar['kgnetto'];
					// $totalrpdt[$bar['periode']][$bar['rpkg']]=$bar['totalrp'];
					// $tanggalreferensi=$bar['tanggalreferensi'];
					// $intiplasma[$bar['periode']][$bar['rpkg']]=$bar['intiplasma'];
					// $namamatauang[$bar['kode']]=$bar['matauang'];

					$kunci=$bar['notransaksi'];
					$listkunci[$kunci]=$kunci;
					$dzdata[$kunci]['tanggalreferensi']=$bar['tanggalreferensi'];
					// $dzdata[$kunci]['nospb']=$bar['nospb']; mill
					// $dzdata[$kunci]['nospb']=$bar['nospb']; kud
					// $dzdata[$kunci]['nospb']=$bar['nospb']; estate
					$dzdata[$kunci]['intiplasma']=$bar['intiplasma'];
					$dzdata[$kunci]['tanggaltbs1']=$bar['tanggaltbs1'];
					$dzdata[$kunci]['tanggaltbs2']=$bar['tanggaltbs2'];
					$dzdata[$kunci]['kgnetto']+=$bar['kgnetto'];
					$dzdata[$kunci]['totalrp']+=floor($bar['totalrp']);
				}
				// echo"</pre>";
				// print_r($dzdata);
				// echo"</pre>";
				
				// exit("Error:A");
				
				#= cari min max tahun
				$str="select max(tahuntanam) as maxthntnm,min(tahuntanam) as minthntnm,rpkg,periode 
						from ".$dbname.".keu_penagihandt 
						where noinvoice='".$noinvoice."' group by periode,rpkg";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$maxthntnm[$bar['periode']][$bar['rpkg']]=$bar['maxthntnm'];
					$minthntnm[$bar['periode']][$bar['rpkg']]=$bar['minthntnm'];
					// $namamatauang[$bar['kode']]=$bar['matauang'];
				}
				
				$no=0;
				foreach($listkunci as $kunci){
					
					if($dzdata[$kunci]['intiplasma']=='kud'){
						$dzdata[$kunci]['intiplasma']='PETANI';
					}
					if($dzdata[$kunci]['intiplasma']=='ext'){
						$dzdata[$kunci]['intiplasma']='';
					}
					
					$no+=1;
					$tab.="<tr>";
						$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$no."</td>"; 
						$tab.="<td style='width:75px;height:20px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".number_format($dzdata[$kunci]['kgnetto'])."&nbsp;&nbsp;</td>";  
						$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=3>JUAL TBS ".strtoupper($dzdata[$kunci]['intiplasma'])." PERIODE ".tanggalnormal($dzdata[$kunci]['tanggaltbs1'])." - ".tanggalnormal($dzdata[$kunci]['tanggaltbs2'])."</td>"; 
						// $tab.="<td style='width:75px;height:20px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".number_format($dzdata[$kunci]['totalrp'],2)."&nbsp;&nbsp;</td>"; 
						$tab.="<td style='width:75px;height:20px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".number_format($dzdata[$kunci]['totalrp'])."&nbsp;&nbsp;</td>"; 
					$tab.="</tr>";
					$gtotalkgnetto+=$dzdata[$kunci]['kgnetto'];	
					$gtotalrpdt+=$dzdata[$kunci]['totalrp'];

				}

				// foreach($arrperdt as $perdt){
				// 	$nodt=0;
				// 	$tab.="<tr>";
				// 		$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				// 		$tab.="<td style='width:75px;height:20px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				// 		$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2><u>Periode ".romawi($perdt)."</u></td>"; 
				// 		$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				// 		$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				// 	$tab.="</tr>";
				// 	foreach($arrrpkgdt as $rpkgdt){
				// 		if($listrpkgdt[$perdt][$rpkgdt]!=''){
				// 			// $keterangandt="";
							
				// 			if($intiplasma[$perdt][$rpkgdt]=='kud'){
				// 				#jika pt snp maka TKD bukan tbs petani, karna pengaruh pajak kata pam surminto  lie, ada crf pertanggal 21/01/2021
				// 				$sPt=makeOption($dbname,"keu_penagihanht","noinvoice,kodept","noinvoice='".$noinvoice."'");
				// 				if($sPt[$noinvoice]=='SNP'){
				// 					$intiplasmadata="TBS TKD ";
				// 				}else{
				// 					$intiplasmadata="TBS Petani ";
				// 				}
				// 			}else{
				// 				$intiplasmadata="TBS Inti ";
				// 			}
				// 			$expltanggalreferensi=explode('-',$tanggalreferensi);
							
				// 			$keterangandt=" ".$intiplasmadata." - ".numToMonth(intval($expltanggalreferensi[1]),'I','long')." ".$expltanggalreferensi[0]." 
				// 							tahun  tanam ".$minthntnm[$perdt][$rpkgdt]." s/d ".$maxthntnm[$perdt][$rpkgdt]." ";
				// 			$nodt++;
				// 			$tab.="<tr>";
				// 				$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$nodt."</td>"; 
				// 				$tab.="<td style='width:75px;height:20px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".number_format($totalkgnetto[$perdt][$rpkgdt])."&nbsp;&nbsp;</td>"; 
				// 				$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2>".$keterangandt."</td>"; 
				// 				$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".number_format($rpkgdt,2)."</td>"; 
				// 				$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".number_format($totalrpdt[$perdt][$rpkgdt])."</td>"; 
				// 			$tab.="</tr>";
							
				// 			$ttotalrpdt[$perdt]+=$totalrpdt[$perdt][$rpkgdt];
				// 			$ttotalkgnetto[$perdt]+=$totalkgnetto[$perdt][$rpkgdt];
							
				// 		}
				// 	}
					
				// 	$tab.="<tr>";
				// 		$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				// 		$tab.="<td style='width:75px;height:20px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$_SESSION['lang']['subtotal']." ".number_format($ttotalkgnetto[$perdt])."</td>"; 
				// 		$tab.="<td style='width:300px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2></td>"; 
				// 		$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				// 		$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".number_format($ttotalrpdt[$perdt])."</td>"; 
				// 	$tab.="</tr>";
					
				// 	@$gtotalkgnetto+=$ttotalkgnetto[$perdt];
				// 	@$gtotalrpdt+=$ttotalrpdt[$perdt];
					
				// }
				
				$tab.="<tr>";
						$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
						$tab.="<td style='width:75px;height:20px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$_SESSION['lang']['total']." ".number_format($gtotalkgnetto)."&nbsp;&nbsp;</td>"; 
						$tab.="<td style='width:300px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=3></td>"; 
						// $tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
						$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="</tr>";

				
			
			}else{
				
				if($jenisinvoice=='UM'){
					$kataumpl="Uang Muka";
				}else{
					$kataumpl="Penjualan";
				}
					
				
				$nilaiuangmuka=0;
				$keteranganuangmuka='';
				// if($jumlahinvoice=='0'){
					// $termin=$kuantitas/$kuantitaskontrak*100;
					// $keterangan=" Pembayaran ".$kataumpl." ".$namabarang[$kodebarang]." sesuai dengan kontrak, yaitu :<br>";
					// $keterangan.=" ".number_format($termin)." % ( ".number_format($kuantitaskontrak)." x Rp. ".number_format($hargasatuan,2)." )";
				// } else {
					// // jumlahnoinvoice
					// $keterangan=" ".$kataumpl." ".$namabarang[$kodebarang]."";
					// $nilaiinvoice=$hargasatuan*$kuantitas;
					// $nilaiuangmuka=$jumlahrpinvoice*-1;
					// if($nilaiuangmuka!=0){
						// $keteranganuangmuka="Pembayaran uang muka";
						// // $tampilanuangmuka=$tampilanrupiahpinalti1="(".number_format(abs($nilaiuangmuka)).")";
						// $tampilanuangmuka="(".number_format(abs($nilaiuangmuka)).")";
					// }
					
				// }
				
				
				if($jenisinvoice=='UM'){
					$termin=$kuantitas/$kuantitaskontrak*100;
					$keterangan=" Pembayaran ".$kataumpl." ".$namabarang[$kodebarang]." sesuai dengan kontrak, yaitu :<br>";
					$keterangan.=" ".number_format($termin,2)." % ( ".number_format($kuantitaskontrak)." x Rp. ".number_format($hargasatuan,2)." )";
				} else {
					// jumlahnoinvoice
					$keterangan=" ".$kataumpl." ".$namabarang[$kodebarang]."";
					$nilaiinvoice=$hargasatuan*$kuantitas;
					$nilaiuangmuka=$jumlahrpinvoice*-1;
					if($nilaiuangmuka!=0){
						$keteranganuangmuka="Pembayaran uang muka";
						// $tampilanuangmuka=$tampilanrupiahpinalti1="(".number_format(abs($nilaiuangmuka)).")";
						$tampilanuangmuka="(".number_format(abs($nilaiuangmuka)).")";
					}
					
				}
				
				//indra ppnpinalti
					
				$tab.="<tr>";
					$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>1</td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".number_format($kuantitas)."&nbsp;&nbsp;</td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2>".$keterangan."</td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".number_format($hargasatuan,2)."</td>"; 
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".number_format($nilaiinvoice)."</td>"; 
				$tab.="</tr>";
			}
		
			#= ini untuk pinalti mutu
				if($rupiahpinalti1!=0){
					$tab.="<tr>";
					$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2>Pinalti ".$keteranganpinalti1."</td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					if($rupiahpinalti1<0){
						$tampilanrupiahpinalti1="(".number_format(abs($rupiahpinalti1)).")";
					}
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$tampilanrupiahpinalti1."</td>"; 
					$tab.="</tr>";
				}
				if($rupiahpinalti2!=0){
					$tab.="<tr>";
					$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2>Pinalti ".$keteranganpinalti2."</td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					if($rupiahpinalti2<0){
						$tampilanrupiahpinalti2="(".number_format(abs($rupiahpinalti2)).")";
					}
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$tampilanrupiahpinalti2."</td>"; 
					$tab.="</tr>";
				}
				if($rupiahpinalti3!=0){
					$tab.="<tr>";
					$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2>Pinalti ".$keteranganpinalti3."</td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					if($rupiahpinalti3<0){
						$tampilanrupiahpinalti3="(".number_format(abs($rupiahpinalti3)).")";
					}
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$tampilanrupiahpinalti3."</td>"; 
					$tab.="</tr>";
				}
				if($rupiahpinalti4!=0){
					$tab.="<tr>";
					$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2>Pinalti ".$keteranganpinalti4."</td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					if($rupiahpinalti4<0){
						$tampilanrupiahpinalti4="(".number_format(abs($rupiahpinalti4)).")";
					}
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$tampilanrupiahpinalti4."</td>"; 
					$tab.="</tr>";
				}
				if($rupiahpinalti5!=0){
					$tab.="<tr>";
					$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2>Pinalti ".$keteranganpinalti5."</td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					if($rupiahpinalti5<0){
						$tampilanrupiahpinalti5="(".number_format(abs($rupiahpinalti5)).")";
					}
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$tampilanrupiahpinalti5."</td>"; 
					$tab.="</tr>";
				}
				if($rupiahpinalti6!=0){
					$tab.="<tr>";
					$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2>Pinalti ".$keteranganpinalti6."</td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					if($rupiahpinalti6<0){
						$tampilanrupiahpinalti6="(".number_format(abs($rupiahpinalti6)).")";
					}
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$tampilanrupiahpinalti6."</td>"; 
					$tab.="</tr>";
				}
				if($rupiahpinalti7!=0){
					$tab.="<tr>";
					$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2>Pinalti ".$keteranganpinalti7."</td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					if($rupiahpinalti7<0){
						$tampilanrupiahpinalti7="(".number_format(abs($rupiahpinalti7)).")";
					}
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$tampilanrupiahpinalti7."</td>"; 
					$tab.="</tr>";
				}
				if($rupiahpinalti8!=0){
					$tab.="<tr>";
					$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2>Pinalti ".$keteranganpinalti8."</td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					// if($rupiahpinalti8<0){
						// $tampilanrupiahpinalti8="(".number_format(abs($rupiahpinalti8)).")";
					// }else{
						$tampilanrupiahpinalti8=number_format($rupiahpinalti8);
					// }
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$tampilanrupiahpinalti8."</td>"; 
					$tab.="</tr>";
				}
		
			#= ini untuk uang muka
			if($nilaiuangmuka!=0){
					$tab.="<tr>";
					$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2>".$keteranganuangmuka."</td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$tampilanuangmuka."</td>"; 
					$tab.="</tr>";
				}
			
			
			// $ppntotalpinalti=0.1*$totalpinalti;
			$nilaiinvoice=$nilaiinvoice+$totalpinalti+$nilaiuangmuka;
			#= untuk tampilan 
			#= jika berikat inputan tidak ada ppn, tapi ditampilan pdf harus ada ppn-nya
			#= sehingga buat if untuk tambahan jika ada claim
			if($berikat==1){
				$nilaippn=floor($persentasedua*$nilaiinvoice)+floor($persentasedua*$totalpinalti);
			} else {
				$nilaippn=$nilaippn+floor($persentasedua*$totalpinalti);
			}

			$totalnilaiinvoice=$nilaiinvoice+$nilaippn;

		
		
			if($kodebarang=='40000003'){
				$tab.="<tr>";
					$tab.="<td style='height:20px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=3></td>"; 
					// $tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				$tab.="</tr>";
			}else{
				$tab.="<tr>";
					$tab.="<td style='height:250px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:300px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=2></td>"; 
					$tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
					$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				$tab.="</tr>";
			}
			
			$tab.="<tr>";
				$tab.="<td rowspan=2  colspan=3 style='vertical-align:top;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>";
					$tab.="<table>";
					$tab.="<tr>";
						$tab.="<td style='vertical-align:top;width:60px'>Terbilang :</td>";
						$tab.="<td align=left><i># ".terbilang($totalnilaiinvoice,2)." rupiah #</i></td>";
					$tab.="</tr>";
					$tab.="</table>";
				
				
				$tab.="</td>"; 
				$tab.="<td style='width:100px;font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;text-align:left'>&nbsp;&nbsp;&nbsp;CURRENCY</td>"; 
				$tab.="<td style='font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;text-align:center'>SUBTOTAL</td>";  
				$tab.="<td style='font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;border-right:0.5px solid #000000;text-align:right'>".number_format($nilaiinvoice)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				
				$tab.="<td style='width:100px;font-size:12px;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;text-align:left'>&nbsp;&nbsp;&nbsp;Rp</td>"; 
				$tab.="<td style='font-size:12px;border-bottom:0.5px solid #000000;border-left:0.5px solid #000000;border-right:0.5px solid #000000;text-align:center'>PPN ".$persentasekata."</td>";  
				$tab.="<td style='font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;border-right:0.5px solid #000000;text-align:right'>".number_format($nilaippn)."</td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='font-size:12px;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=5><b>OUR BANKER :</b></td>"; 
				$tab.="<td style='font-size:12px;border-top:0.5px solid #000000;border-left:0.5px solid #000000;border-bottom:0.5px solid #000000;border-right:0.5px solid #000000;text-align:right'>".number_format($totalnilaiinvoice)."</td>"; 
			$tab.="</tr>";

			$tab.="<tr>";
				$tab.="<td style='font-size:12px;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=5><b>".$atasnama."<br>".$namabank." ".$cabang."<br>A/C No : ".$rekening."</b></td>";  
				$tab.="<td style='font-size:12px;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>";  
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='font-size:12px;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;' colspan=5>&nbsp;</td>";  
				$tab.="<td style='font-size:12px;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>&nbsp;</td>";  
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='font-size:12px;border:0' colspan=5>&nbsp;</td>";  
				$tab.="<td style='font-size:12px;border:0;text-align:center;height:25'>".tglnmbln($tanggalinvoice,'i','long')."</td>";  
			$tab.="</tr>";
			
			$tab.="<tr>";
				$tab.="<td style='font-size:12px;border:0' colspan=5>&nbsp;</td>";  
				$tab.="<td style='font-size:12px;border:0;text-align:center;height:80;vertical-align:bottom'>".$namakaryawan."</td>";  
			$tab.="</tr>";
		$tab.="</table>";
		
		$cellpadding=10;
		
	
	// createby
			
	
	// atasnama
	
	
	
	
	
	
	
		
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
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	

# Print Out
// if($urlefil=='0'){
	// $pdf->Output();
// }else{
	// $pdf->Output($urlefil);
// }
?>