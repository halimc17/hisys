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

$arrHead = setheadreport('',$dataH['kodeorg']);
$path=$arrHead['logopalma'];
$pathnonpalma='images/logo/KOP INVOICE.png';

    $str = "select * from ".$dbname.".".$table." where noinvoice='".$column."' ";
    $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $bar=$res->fetch();
        $kodept=$bar['kodept'];
        $kodecustomer=$bar['kodecustomer'];
        $nokontrak=$bar['nokontrak'];
        $noinvoice=$bar['noinvoice'];
        $tanggalinvoice=$bar['tanggal'];
        $bulaninvoice=substr($bar['tanggal'],5,2);
        $namabulaninvoice=numToMonth(substr($bar['tanggal'],5,2),'I','long');
        $tahuninvoice=substr($bar['tanggal'],0,4);
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
			$jabatankaryawan=$bar['jabatan'];
			
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
			
			
        $border=1;	
        // $border=0;	
		$cellpadding=1.5;

		if ($kodept=='PPP'){

			$tab.="<table style='width:95%;margin:0 auto!important;' cellpadding=".$cellpadding." cellspacing=0 border='0'>";
			$tab.="<tr>";
			$tab.="<td align=center style='width:180px;'><img src='".$path."' style='width:180px;height:100px;' /></td>"; 
			$tab.="<td style='font-weight;text-align:center;font-size:16px;margin-top:30px!important;'><b>I N V O I C E</b><p style='font-weight;text-align:center;font-size:14px;'>No : $noinvoice</p></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
			if($kodepospt==''){
				$tab.="<td style='font-weight;text-align:left;font-size:12px'>".$wilayahkotapt."</td>"; 
                $tab.="<td></td>";
			}else{
				$tab.="<td style='font-weight;text-align:left;font-size:12px'>".$wilayahkotapt." - ".$kodepospt."</td>"; 
                $tab.="<td></td>";
			}
			$tab.="</tr>";
			$tab.="</table>";
        
			$tab.="<br/>";
        
			$tab.="<table style='width:95%;margin:0 auto!important;' cellpadding=".$cellpadding." cellspacing=0 border='0'>";
            $tab.="<tr>";
				$tab.="<td style='font-size: 14px;'>Kepada Yth:</td>";
			$tab.="</tr>";
			$tab.="<tr>";
                $tab.="<td style='font-size: 14px;'>".$namacustomer."</td>";
			$tab.="</tr>";
			$tab.="<tr>";
                $tab.="<td style='font-size: 14px;'>DI -</td>";
			$tab.="</tr>";
			$tab.="<tr>";
                $tab.="<td style='font-size: 14px;margin-left:30px!important;'>Tempat</td>";
			$tab.="</tr>";
            # Line
			$tab.="<tr>";
                $tab.="<td><span style=color:#FFF!important;>LINE</span></td>";
			$tab.="</tr>";
            # End Line
            $tab.="<tr>";
                $tab.="<td style='font-size: 14px;'>Dengan Hormat,</td>";
			$tab.="</tr>";
            $tab.="<tr>";
                $tab.="<td style='font-size: 14px;'>Berdasarkan Berita Acara Serah Terima Barang atas nama $namapt (Kode Vendor: PPPX) untuk periode $namabulaninvoice $tahuninvoice adalah sebagai berikut:</td>";
			$tab.="</tr>";
			$tab.="</table>";
			
			$tab.="<br/>";

			$tab.="<table style='width:90%;margin:0 auto!important;' cellpadding=".$cellpadding." cellspacing=0 border='".$border."'>";
            $tab.="<tr id=header style='background-color:#C3C3C3;'>";
                $tab.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['tahuntanam']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['jumlah']."<br/>(kg)</td>";
                $tab.="<td align=center>".$_SESSION['lang']['harga']."</td>";
                $tab.="<td align=center>".$_SESSION['lang']['jumlah']."<br/>(Rp)</td>";
            $tab.="</tr>";

            # Arr Potongan & Penambah
            $arrpotpen = array('dpp' => 'DPP', 'ppn' => 'PPN', 'pph22' => 'PPH 22', 'ttlinv' => 'Total Invoice');
            // $arrbayar  = array('bank' => 'Bank', 'unit' => 'Unit', 'norek' => 'Nomor Rekening', 'an' => 'Atas Nama');
            $arrbayar  = array('bank' => 'Bank', 'norek' => 'Nomor Rekening', 'an' => 'Atas Nama');
            $total = array();

			if($jenis=='TBS'){
            	$sql = selectQuery($dbname,"keu_penagihandt","tanggaltbs1, tanggaltbs2, SUM(kgnetto) as jumlahkg, SUM(totalrp) as jumlahrp, tahuntanam, rpkg as harga","noinvoice='".$noinvoice."' GROUP BY tahuntanam");
			}else{
				$sql = selectQuery($dbname,"keu_penagihandt","tanggaltbs1, tanggaltbs2, SUM(kgnetto) as jumlahkg, SUM(totalrp) as jumlahrp, tahuntanam, rpkg as harga","noinvoice='".$noinvoice."' GROUP BY tanggaltbs1");
			}
			
			$res = fetchData($sql,"OBJECT");
			
            if(count($res) == 0) {
                $tab.="<tr>";
                    $tab.="<td colspan=5 align=center>Tidak Ada Data</td>";
                $tab.="</tr>";
                foreach($arrpotpen as $key => $val) {

                    if($key=='ttlinv') {
                        $bold = "style='font-weight:bold'";
                    }

                    $tab.="<tr id=datapenambah>";
					$tab.="<td colspan=3></td>";
					$tab.="<td $bold>$val</td>";
                        $tab.="<td $bold></td>";
						$tab.="</tr>";
                }
            } else {
                $no=0;
                foreach($res as $val) {
                    $no++;
                    $tab.="<tr id=data>";
                        $tab.="<td align=center>".$no."</td>";
                        $tab.="<td align=center>TT. ".$val->tanggaltbs1."</td>";
                        $tab.="<td align=right>".number_format($val->jumlahkg,2)."</td>";
                        $tab.="<td>";
						$tab.="<table width=100%;>";
						$tab.="<tr>";
                                    $tab.="<td align=left>Rp</td>";
                                    $tab.="<td align=right>".number_format($val->harga)."</td>";
									$tab.="</tr>";
                            $tab.="</table>";
                        $tab.="</td>";
                        $tab.="<td>";
                            $tab.="<table width=100%;>";
                                $tab.="<tr>";
                                    $tab.="<td align=left>Rp</td>";
                                    $tab.="<td align=right>".number_format($val->jumlahrp)."</td>";
                                $tab.="</tr>";
                            $tab.="</table>";
                        $tab.="</td>";
                    $tab.="</tr>";

                    $totalkg+=$val->jumlahkg;
                    $total['dpp']+=$val->jumlahrp;
                    // $total['ppn']=$total['dpp']*11/100;
                    $total['ppn']=$nilaippn;
                    $total['pph22']=$total['dpp']*(0.25/100);
                    $total['ttlinv']=$total['dpp']+$total['ppn']-$total['pph22'];
                }

                $tab.="<tr id=datajumkg>";
                    $tab.="<td colspan=2></td>";
                    $tab.="<td align=right>".number_format($totalkg,2)."</td>";
                    $tab.="<td colspan=2></td>";
					$tab.="</tr>";

                foreach($arrpotpen as $key => $val) {

                    if($key=='ttlinv') {
                        $bold = "style='font-weight:bold'";
                    }

                    $tab.="<tr id=datapenambah>";
                        $tab.="<td 
                        style='
                        border-right:none!important;
                        border-left:none!important;
                        border-top:none!important;
                        border-bottom:none!important;
                        border:none!important;
                        box-shadow:none!important;
                        outline:none!important;' 
                        colspan=3></td>";
                        $tab.="<td $bold>$val</td>";
                        $tab.="<td $bold>";
                            $tab.="<table width=100%;>";
                                $tab.="<tr>";
                                    $tab.="<td align=left>Rp</td>";
                                    $tab.="<td align=right>".hidezerodecimal($total[$key])."</td>";
                                $tab.="</tr>";
                            $tab.="</table>";
                        $tab.="</td>";
                    $tab.="</tr>";
                }
            }
			$tab.="</table>";

			$tab.="<br/>";
			
			$tab.="<table style='width:90%;margin:0 auto!important;' cellpadding=".$cellpadding." cellspacing=0 border='0'>";
            $tab.="<tr id=pembyaran>";
                $tab.="<td style='font-wight:bold;font-size:14px;' colspan=4>Mohon dibayarkan kepada kami melalui:</td>";
            $tab.="</tr>";

            $databayar['norek']=$rekening;
            $databayar['bank']=$namabank;
            // $databayar['unit']="HOLDING";
            $databayar['an']=$namapt;
            
            foreach($arrbayar as $key => $val) {   
                $tab.="<tr>";
                    $tab.="<td style='font-wight:bold;font-size:14px;' width=10%;></td>";
                    $tab.="<td style='font-wight:bold;font-size:14px;' width=20%>$val</td>";
                    $tab.="<td style='font-wight:bold;font-size:14px;' width=5%; align=center>:</td>";
                    $tab.="<td style='font-wight:bold;font-size:14px;'>".$databayar[$key]."</td>";
					$tab.="</tr>";
            }
			$tab.="</table>";   
			
			# Tanda Tangan
			$tab.="<table style='width:100%;margin:0 auto!important;' cellpadding=".$cellpadding." cellspacing=0 border='0'>";
            $tab.="<tr id=tandatangan>";
                $tab.="<td style='color:#fff;' align=center>Jakarta, ".$tanggalinvoice."</td>";
                $tab.="<td style='font-wight:bold;font-size:14px;' align=center>Jakarta, ".substr($tanggalinvoice,8,2)." ".$namabulaninvoice." ".$tahuninvoice."</td>";
            $tab.="</tr>";
            $tab.="<tr id=kolomttd>";
                $tab.="<td style='color:#fff;' align=center>Jakarta, ".$tanggalinvoice."</td>";
                $tab.="<td style='height:120px;' align=center></td>";
            $tab.="</tr>";
            $tab.="<tr id=namattd>";
                $tab.="<td style='color:#fff;' align=center>Jakarta, ".$tanggalinvoice."</td>";
                $tab.="<td style='font-wight:bold;font-size:14px;' align=center>".$namakaryawan."</td>";
            $tab.="</tr>";
            $tab.="<tr id=jabatanttd>";
                $tab.="<td style='color:#fff;' align=center>Jakarta, ".substr($tanggalinvoice,8,2)." ".$namabulaninvoice." ".$tahuninvoice."</td>";
                $tab.="<td style='font-wight:bold;font-size:14px;' align=center>".getNamaJabatan(getKary($ttd,"kodejabatan"))."</td>";
				$tab.="</tr>";
				$tab.="</table>";
		
		#Kondisi jika PDF selain Palma
		} else {

			$tab.="<table style='width:95%;margin:0 auto!important;' cellpadding=".$cellpadding." cellspacing=0 border='0'>";
			$tab.="<tr>";
			$tab.="<td align=center style='width:180px;'><img src='".$pathnonpalma."' style='width:680px;height:100px;' /></td>"; 
			$tab.="</tr>";
			$tab.="<tr>";
			$tab .= '<div style="
				text-align: center;
				font-family: Times New Roman, serif;
				font-size: 28pt;
				font-weight: 900;
				letter-spacing: 12px;
				text-transform: uppercase;
			">
				INVOICE
			</div>';

			$tab.="</tr>";
			  
			$tab.="</table>";
			$tab.="<br/>";
        
			$tab.="<table style='width:90%;margin:0 auto!important;' cellpadding=".$cellpadding." cellspacing=0 border='0'>";
            $tab.="<tr>";
				$tab.="<td style='font-size: 14px;'><b>No Invoice : $noinvoice</b></td>";
			$tab.="</tr>";
            $tab.="<tr>";
				$tab.="<td style='font-size: 14px;'><b>Kepada : </b></td>";
			$tab.="</tr>";
            $tab.="<tr>";
				$tab.="<td style='font-size: 14px;'>$namacustomer </td>";
			$tab.="</tr>";
            $tab.="<tr>";
				$tab.="<td style='font-size: 14px;'>$alamatcustomer </td>";
			$tab.="</tr>";
			$tab.="<tr>";
				$tab.="<td style='font-size: 14px;height:10px;'> </td>";
			$tab.="</tr>";
            $tab.="<tr>";
			$tab.="<td style='font-size: 14px;'><b>No. Klaim :</b></td>";
			$tab.="</tr>";
            $tab.="<tr>";
			$tab.="<td style='font-size: 14px;'><b>No. SPK :</b></td>";
			$tab.="</tr>";

			$tab.="<tr>";
				$tab.="<td style='font-size: 14px;height:10px;'> </td>";
			$tab.="</tr>";

			$tab.="<tr>";
				$tab.="<td style='font-size: 14px;height:10px;'>Dengan Hormat, </td>";
			$tab.="</tr>";

			$tab.="<tr>";
				$tab.="<td style='font-size: 14px;height:10px;'>Bersama ini kami sampaikan tagihan pengiriman TBS ke kebun ".getNamaOrg($kodept)." periode <b>".substr(tanggalbulan($tanggalinvoice),3,9)." </b> dengan rincian sebagai berikut:</td>";
			$tab.="</tr>";
			
  
			$tab.="</table>";
			
			$tab.="<br/>";

			$tab.="<table style='width:90%;margin:0 auto!important;' cellpadding=".$cellpadding." cellspacing=0 border='".$border."'>";
			$tab.="<tr id=header style='background-color:#8DB4E2;font-weight:bold;'>";

                $tab.="<td align=center>".$_SESSION['lang']['nourut']."</td>";
                $tab.="<td align=center>Jenis Pekerjaan</td>";
                $tab.="<td align=center>".$_SESSION['lang']['periode']."</td>";
                $tab.="<td align=center>Jenis Klaim</td>";
                $tab.="<td align=center>".$_SESSION['lang']['berat']."Netto<br/>(Kg)</td>";
                $tab.="<td align=center>Harga<br/>(Rp,-Kg)</td>";
                $tab.="<td align=center>Jumlah<br/>(Rp,-)</td>";
            $tab.="</tr>";

            # Arr Potongan & Penambah
            $arrpotpen = array('dpp' => 'DPP', 'ppn' => 'PPN', 'pph22' => 'PPH 22', 'ttlinv' => 'Total Invoice');
            // $arrbayar  = array('bank' => 'Bank', 'unit' => 'Unit', 'norek' => 'Nomor Rekening', 'an' => 'Atas Nama');
            $arrbayar  = array('bank' => 'Bank', 'norek' => 'Nomor Rekening', 'an' => 'Atas Nama');
            $total = array();

			if($jenis=='TBS'){
            	$sql = selectQuery($dbname,"keu_penagihandt","tanggaltbs1, tanggaltbs2, SUM(kgnetto) as jumlahkg, SUM(totalrp) as jumlahrp, tahuntanam, rpkg as harga","noinvoice='".$noinvoice."' GROUP BY tahuntanam");
			}else{
				$sql = selectQuery($dbname,"keu_penagihandt","tanggaltbs1, tanggaltbs2, SUM(kgnetto) as jumlahkg, SUM(totalrp) as jumlahrp, tahuntanam, rpkg as harga","noinvoice='".$noinvoice."' GROUP BY tanggaltbs1");
			}
			
			$res = fetchData($sql,"OBJECT");
			
            if(count($res) == 0) {
                $tab.="<tr>";
                    $tab.="<td colspan=5 align=center>Tidak Ada Data</td>";
                $tab.="</tr>";
                foreach($arrpotpen as $key => $val) {

                    if($key=='ttlinv') {
                        $bold = "style='font-weight:bold'";
                    }

                    $tab.="<tr id=datapenambah>";
					$tab.="<td colspan=3></td>";
					$tab.="<td $bold>$val</td>";
                        $tab.="<td $bold></td>";
						$tab.="</tr>";
                }
            } else {
                $no=0;
                foreach($res as $val) {
                    $no++;
                    $tab.="<tr id=data>";
                        $tab.="<td align=center>".$no."</td>";
                        $tab.="<td align=center>Penjualan Tandan Buah Segar</td>";
						$tab.="<td align=center>".str_replace('-', '/', tanggalnormal($val->tanggaltbs1))."</td>";
                        $tab.="<td align=center>TBS</td>";
                        $tab.="<td align=right>".number_format($val->jumlahkg,0)."</td>";
                        $tab.="<td align=right>".number_format($val->harga,0)."</td>";
                        $tab.="<td align=right>".number_format($val->jumlahrp,0)."</td>";

                    $totalkg+=$val->jumlahkg;
                    $totalsblm+=$val->jumlahrp;
					$totaldpp = $totalsblm*11/12;
					$totalppn = $totaldpp * 0.12;
					$totalpph22 = $totaldpp*(0.25/100);
					$totalinvoice= $totaldpp+$total['ppn']-$total['pph22'];

					$rpkgrata=$val->jumlahrp/$val->jumlahkg;
 
                }

                $tab.="<tr id=datajumkg>";
                    $tab.="<td align='center' colspan=4><b>T O T A L</b></td>";
                    $tab.="<td align=right>".number_format($totalkg,0)."</td>";
                    $tab.="<td align=right>".number_format($rpkgrata,0)."</td>";
                    $tab.="<td align=right>".number_format($totalsblm,0)."</td>"; 
				$tab.="</tr>";

                $tab.="<tr>";
                    $tab.="<td align='left' colspan=4><b>Harga Jual (Dasar Pengenaan Pajak /DPP)</b></td>";
                    $tab.="<td colspan='3' align=right>".number_format($totaldpp,0)."</td>"; 
				$tab.="</tr>";

                $tab.="<tr>";
                    $tab.="<td align='left' colspan=4><b>PPN</b></td>";
                    $tab.="<td colspan='3' align=right>".number_format($totalppn,0)."</td>"; 
				$tab.="</tr>";

                $tab.="<tr>";
                    $tab.="<td align='left' colspan=4><b>PPh 22</b></td>";
                    $tab.="<td colspan='3' align=right>".number_format($totalpph22,0)."</td>"; 
				$tab.="</tr>";

				$tab.="<tr>";
                    $tab.="<td align='center' colspan=4><b><i>T O T A L</i></b></td>";
                    $tab.="<td colspan='3' align=right>".number_format($totalinvoice,0)."</td>"; 
				$tab.="</tr>";

				$tab.="<tr>";
                    $tab.="<td style='font-size:13px;' colspan='7' align=left>Terbilang : ".terbilang($totalinvoice,4)."</td>"; 
				$tab.="</tr>"; 

				$tab.="<tr>";
                    $tab.="<td style='font-size:14px;' colspan='4' align=left>";
						$tab.="<table style='width:100%;margin:0 auto!important;' cellpadding=".$cellpadding." cellspacing=0 border='0'>";
							$tab.="<tr>"; 
								$tab.="<td colspan=2 align=left>Pembayaran Tunai / Cek/ Bilyet Giro</td>";
							$tab.="</tr>";
							$tab.="<tr>"; 
								$tab.="<td style='font-weight:bold;' align=left>Bank </td>";
								$tab.="<td style='font-weight:bold;' align=left>: $namabank</td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$tab.="<td style='font-weight:bold;' align=left>Cabang</td>"; 
								$tab.="<td style='font-weight:bold;' align=left>: ".$cabang."</td>"; 
							$tab.="</tr>";
							$tab.="<tr>";
								$tab.="<td style='font-weight:bold;' align=left>Atas Nama</td>"; 
								$tab.="<td style='font-weight:bold;' align=left>: ".getNamaOrg($kodept)."</td>"; 
							$tab.="</tr>";
							$tab.="<tr>";
								$tab.="<td style='font-weight:bold;' align=left>Acc No</td>"; 
								$tab.="<td style='font-weight:bold;' align=left>: ".$rekening."</td>"; 
							$tab.="</tr>";
						$tab.="</table>";
					$tab.="</td>";

                    $tab.="<td style='font-size:14px;' colspan='3' align=left>";
						$tab.="<table style='width:100%;margin:0 auto!important;' cellpadding=".$cellpadding." cellspacing=0 border='0'>";
							$tab.="<tr>";
							$tab.="<td height=100px;></td>";
							$tab.="</tr>";
							
							$tab.="<tr>";
								$tab.="<td align='center'><b>$namakaryawan</b></td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$tab.="<td align='center' style='border-bottom:1px solid #000;'></td>";
							$tab.="</tr>";
							$tab.="<tr>";
								$tab.="<td align='center'>".getNamaJabatan(getKary($ttd,"kodejabatan"))."</td>";
							$tab.="</tr>";
							$tab.="</tr>";
						$tab.="</table>";
					$tab.="</td>"; 
				$tab.="</tr>"; 
            }
			$tab.="</table>";

		}

		$dompdf = new Dompdf();
		$dompdf->loadHtml($tab);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$dompdf->stream($table,array("Attachment"=>0));	

?>