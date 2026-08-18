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
			$tglinv=$bar['tanggal'];
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
			$npwppt=$bar['npwp'];
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
			$penandatangan=$bar['penandatangan'];	
			$npwpcust=$bar['npwp'];	
			$faxcustomer=$bar['fax'];		
			$kotacustomer=$bar['kota'];		
 
		$str="select * from ".$dbname.".log_5masterbarang where kodebarang='".$kodebarang."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$namabarang=$bar['namabarang'];
			$satuanbarang=$bar['satuan'];
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
				$tab.="<td style='width:10px;font-weight;text-align:center;font-size:14px'><b>FAKTUR PAJAK</b></td>"; 
			$tab.="</tr>";
		$tab.="</table>";
		
		
		$tab.="<br>";
		
		$border=0;	
		$cellpadding=2;
        $fontsize=15;
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border='".$border."'>";
			$tab.="<tr>";
				$tab.="<td colspan=3 valign=top style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'>Kode dan Nomor Seri Faktur Pajak : ".$nofakturpajak."</td>";   
			$tab.="</tr>";
			$tab.="<tr>"; 
			$tab.="<td colspan=3  style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'>Pengusaha Kena Pajak</td>"; 
			$tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td colspan=3  style='height:20px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$_SESSION['lang']['nama']." : ".$namapt."</td>";  
     
            $tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td colspan=3  style='height:20px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$_SESSION['lang']['alamat']." : ".$alamatpt." </td>"; 
     
            $tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td colspan=3  style='height:20px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$_SESSION['lang']['npwp']." : ".$npwppt."</td>"; 
            $tab.="</tr>";
            $tab.="<tr>"; 
		    	$tab.="<td colspan=3  style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'>Pembeli Barang Kena Pajak / Penerima Jasa Kena Pajak</td>"; 
			$tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td colspan=3  style='height:20px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$_SESSION['lang']['nama']." : ".$namacustomer."</td>";
            $tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td colspan=3  style='height:20px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$_SESSION['lang']['alamat']." : ".$alamatcustomer."</td>"; 
     
            $tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td colspan=3  style='height:90px;font-weight;text-align:left;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;border-bottom:0.5px solid #000000;'>".$_SESSION['lang']['npwp']." : ".$npwpcust."</td>"; 
            $tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td style='width:10px;font-weight;text-align:center;font-size:12px;valign:left;border:0.5px solid #000000'>No. </td>"; 
                $tab.="<td style='font-weight;text-align:center;font-size:12px;valign:left;border:0.5px solid #000000'>Nama Barang Kena Pajak / Jasa Kena Pajak </td>"; 
                $tab.="<td style='font-weight;text-align:center;font-size:12px;valign:left;border:0.5px solid #000000'> Harga Jual/ Penggantian/Uang Muka/Termin</td>"; 
    
            $tab.="</tr>";

			$str="SELECT * from ".$dbname.".keu_penagihandt where noinvoice ='".$column."' group by tahuntanam";
			$res=fetchData($str);
			foreach ($res as $key ) {
				$no++;
				$thntanam=$key['tahuntanam'];
				$kgnetto=$key['kgnetto'];
				$rpkg=$key['rpkg'];
				$ttlrp=$key['totalrp'];
				$sumttlrp+=$key['totalrp'];
				// $rupiahnya=$kgnetto*$rpkg;
				// echo"<pre>";
				// print_r($key);
				// exit;
				
				$tab.="<tr>"; 
                $tab.="<td style='font-weight;text-align:center;font-size:12px;valign:left;border:0.5px solid #000000'>".$no." </td>"; 
                $tab.="<td style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'>".$namabarang." TT ".$thntanam." <br/> Rp ".number_format($rpkg,2)." x ".number_format($kgnetto,2)."</td>"; 
                $tab.="<td style='font-weight;text-align:right;font-size:12px;valign:left;border:0.5px solid #000000'>".number_format($ttlrp,2)."</td>"; 
				
				$tab.="</tr>";
			}
            // $tab.="<tr>"; 
            //     $tab.="<td style='font-weight;text-align:center;font-size:12px;valign:left;border:0.5px solid #000000'>2 </td>"; 
            //     $tab.="<td style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'>Tandan Buah Segar (TBS) TT 2012 <br/> Rp 2.000.212 </td>"; 
            //     $tab.="<td style='font-weight;text-align:right;font-size:12px;valign:left;border:0.5px solid #000000'>1.000</td>"; 
    
            // $tab.="</tr>";
            // $tab.="<tr>"; 
            //     $tab.="<td style='font-weight;text-align:center;font-size:12px;valign:left;border:0.5px solid #000000'>3 </td>"; 
            //     $tab.="<td style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'>Tandan Buah Segar (TBS) TT 2012 <br/> Rp 2.000.212 </td>"; 
            //     $tab.="<td style='font-weight;text-align:right;font-size:12px;valign:left;border:0.5px solid #000000'>1.000</td>"; 
    
            // $tab.="</tr>";
            // $tab.="<tr>"; 
            //     $tab.="<td style='font-weight;text-align:center;font-size:12px;valign:left;border:0.5px solid #000000'>4 </td>"; 
            //     $tab.="<td style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'>Tandan Buah Segar (TBS) TT 2012 <br/> Rp 2.000.212 </td>"; 
            //     $tab.="<td style='font-weight;text-align:right;font-size:12px;valign:left;border:0.5px solid #000000'>1.000</td>"; 
    
            // $tab.="</tr>";
			$rupiahppn=($sumttlrp*11)/100;
            $tab.="<tr>"; 
                $tab.="<td colspan=2 style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'>Harga Jual/Penggantian</td>"; 
                $tab.="<td style='font-weight;text-align:right;font-size:12px;valign:left;border:0.5px solid #000000'>".number_format($sumttlrp,2)." </td>";  
    
            $tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td colspan=2 style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'> Dikurangi Potongan Harga </td>"; 
                $tab.="<td style='font-weight;text-align:right;font-size:12px;valign:left;border:0.5px solid #000000'>0,00 </td>";  
    
            $tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td colspan=2 style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'> Dikurangi  Uang Muka   </td>"; 
                $tab.="<td style='font-weight;text-align:right;font-size:12px;valign:left;border:0.5px solid #000000'>0,00 </td>";  
    
            $tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td colspan=2 style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'> Dasar Pengenaan Pajak</td>"; 
                $tab.="<td style='font-weight;text-align:right;font-size:12px;valign:left;border:0.5px solid #000000'>".number_format($sumttlrp,2)."</td>";  
    
            $tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td colspan=2 style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'> Total PPN</td>"; 
                $tab.="<td style='font-weight;text-align:right;font-size:12px;valign:left;border:0.5px solid #000000'> ".number_format($rupiahppn,2)."</td>";  
    
            $tab.="</tr>";
            $tab.="<tr>"; 
                $tab.="<td colspan=2 style='font-weight;text-align:left;font-size:12px;valign:left;border:0.5px solid #000000'> Total PPnBBM (Pajak Penjualan Barang Mewah) </td>"; 
                $tab.="<td style='font-weight;text-align:right;font-size:12px;valign:left;border:0.5px solid #000000'>0 </td>";  
    
            $tab.="</tr>";
			
			#= buat termin dengan pengecekan sudah ada invoice atau belum
			#= algoritma select count(*) as jumlah where nokontrak and tanggal invoice < tanggal param
            // update request pak surminto: 2021-08-16 DI SINI KHUSUS TBS, SELAINNYA TETEP PAKE keu_slave_print_penagihan.php
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
 
 
				
				// $tab.="<tr>";
				// 		$tab.="<td style='font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				// 		$tab.="<td style='width:75px;height:20px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'>".$_SESSION['lang']['total']." ".number_format($gtotalkgnetto)."&nbsp;&nbsp;</td>"; 
				// 		$tab.="<td style='width:300px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;' colspan=3></td>"; 
				// 		// $tab.="<td style='width:75px;font-weight;text-align:center;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				// 		$tab.="<td style='width:100px;font-weight;text-align:right;font-size:12px;vertical-align:top;border-left:0.5px solid #000000;border-right:0.5px solid #000000;'></td>"; 
				// 	$tab.="</tr>";
 
			// $tab.="</tr>";
		$tab.="</table>";
 
		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border='".$border."'>";
			$tab.="<tr>";
				$tab.="<td style='width:10px;font-weight;text-align:left;font-size:12px'> Sesuai dengan ketentuan yang berlaku, Direktorat Jenderal Pajak mengatur bahwa Faktur Pajak ini telah ditandatangani secara elektronik sehirtidak diperlukan tanda tangan basah pada Faktur Pajak ini.</td>";
			$tab.="</tr>";
				
		$tab.="</table>"; 

		# QR CODE
		$folder = "images/qrcode/";
		$file_name = $noinvoice.".png";
		$file = $folder.$file_name;
		# END

		$tab.="<table width=100% cellpadding=".$cellpadding." cellspacing=0 border='".$border."'>";
			$tab.="<tr>";
				$tab.="<td style='width:10px;font-weight;text-align:left;font-size:12px'><img src='".$file."' style='margin-left:60px!important;' /></td>";
				$tab.="<td style='width:10px;font-weight;text-align:left;font-size:12px'> ".tanggalbulan($tglinv)."</td>";
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='width:10px;font-weight;text-align:left;font-size:12px'> </td>";
				$tab.="<td style='width:10px;height:10%;font-weight;text-align:left;font-size:12px'> &nbsp;</td>";
			$tab.="</tr>"; 
			$tab.="<tr>";
				$tab.="<td style='width:10px;font-weight;text-align:left;font-size:12px;'><span style='margin-left:80px!important;'>".$column."</span></td>";
				$tab.="<td style='width:10px;font-weight;text-align:left;font-size:12px'> ".$penandatangan." </td>";
			$tab.="</tr>";
				
		$tab.="</table>"; 

		$cellpadding=10;
		 
		
		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	
 
?>