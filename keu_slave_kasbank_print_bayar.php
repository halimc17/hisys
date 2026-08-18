<?php
/*ini_set('display_errors',0);
ini_set("session.auto_start", 0);
error_reporting(0);*/
session_start();
include_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/formTable.php');
include_once('lib/zPdfMaster.php');
include_once('lib/terbilang.php');
include_once('lib/fpdf.php');

require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;

$proses = checkPostGet('proses','');
$notransaksi = checkPostGet('notransaksi','');
$kodeorg = checkPostGet('kodeorg','');
$tipetransaksi = checkPostGet('tipetransaksi','');
$noakun = checkPostGet('noakun','');
$cgttu = checkPostGet('cgttu','');
$rek = checkPostGet('rek','');
$nocekx = checkPostGet('nocekx','');


$nmMt=  makeOption($dbname, 'setup_matauang', 'kode,matauang');
$nmorg=  makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmakun=  makeOption($dbname, 'keu_5akun', 'noakun,namaakun');
$nmket=  makeOption($dbname, 'keu_5keterangan', 'id_ket,keterangan');
$nmbank=  makeOption($dbname, 'keu_5akunbank_vw', 'noakun,namabank');




switch($proses) {
	
	case'pdfnew':
	
	
	
		$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$notransaksi."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();

			$tanggalht=$bar['tanggal'];
			$novoucherht=$bar['novoucher'];
			$noakunht=$bar['noakun'];
			$rekeninght=$bar['rekening'];
			$cgttuht=$bar['cgttu'];
			$nocekht=$bar['nocek'];
			$jumlahht=$bar['jumlah'];
			$tipetransaksiht=$bar['tipetransaksi'];
			$kodeorght=$bar['kodeorg'];
			// $serah=$bar['bayarkepada'];
			
			$namabankht=$bar['namabank'];
			$rekeningextht=$bar['rekeningext'];
			$anrekeningextht=$bar['anrekeningext'];
			
			if($anrekeningextht!=''){
				$anrekeningextht=$anrekeningextht;
				$serahkiri=$anrekeningextht;
				$anrekeningextht.=' / ';
			}
			if($namabankht!=''){
				$namabankht=$namabankht.' / ';
			}
			if($rekeningextht!=''){
				$rekeningextht=$rekeningextht;
			}
			
			$serah=$anrekeningextht.$namabankht.$rekeningextht;
			if($serah==''){
				$serah=$bar['bayarkepada'];
			}
			
			if($serahkiri==''){
				$serahkiri=$bar['bayarkepada'];
			}
			
			
		
		$str = "select * from ".$dbname.".organisasi where kodeorganisasi='".$kodeorght."'";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();		
				$wilayahkota=$bar['wilayahkota'];
				
		$str = "select * from ".$dbname.".keu_5akunbank where noakun='".$rekeninght."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$norekeningsetup=$bar['rekening'];
			$kodebanksetup=$bar['namabank'];
			$atasnamasetup=$bar['atasnama'];
			$cabang=$bar['cabang'];

			// $serah=$atasnamasetup."-".$norekeningsetup;
			
			
		#= data dt
		$str = "select * from ".$dbname.".keu_kasbankdt where notransaksi='".$notransaksi."' and kodesupplier!=''";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();			
			$kodesupplier=$bar['kodesupplier'];	

		#= supplier
		$str = "select * from ".$dbname.".log_5supplier where supplierid='".$kodesupplier."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$namasupplier=$bar['namasupplier'];		
	
			if ($cgttu=='Cheque') {
				if ($nmbank[$rek]=='Bank MANDIRI') {
					#chequemandiri
					
					$panjang='230';
					$setx='12';
					
					$pdf=new FPDF('L','mm',array(70,$panjang));
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();
					$height=6;
					$sizefont='8';
					$pdf->SetFont('Arial','',$sizefont);
					
					$pdf->SetY(0);
					$pdf->SetX($setx);
					// $pdf->Cell(160,$height,$novoucherht,0,0,'L');
					$pdf->MultiCell(24.5,3,$novoucherht);
					
					$pdf->SetXY(190,0);
					$pdf->Cell(50,$height,$wilayahkota.', '.tglnmbln($tanggalht,'','l'),0,1,'L');
					
					$pdf->Ln(2);
					$pdf->SetX($setx);
					$pdf->Cell(100,$height,tglnmbln($tanggalht,'','l'),0,0,'L');
					
					$pdf->Cell(200,$height,$serah,0,1,'L');
					
					$sizefont='8';
					$pdf->SetFont('Arial','',$sizefont);
					// $pdf->Cell(200,$height,number_format($jumlahht),0,1,'L');
					$pdf->Ln(2);
					$pdf->SetX($setx);
					$pdf->Cell(50,$height,$serahkiri,0,0,'L');

					// $pdf->Cell(200,$height,ucwords(terbilang($jumlahht,'','')." Rupiah"),0,1,'L');
					$pdf->MultiCell(160,$height,ucwords('                                                         '.terbilang($jumlahht,'','')." Rupiah"));
					
					
					$pdf->SetX($setx);
					$pdf->Cell(200,$height,number_format($jumlahht),0,1,'L');
				
					
					
					$pdf->SetXY(180,23);
					$pdf->Cell(50,$height,number_format($jumlahht),0,1,'L');
					
					
						$pdf->Output();
					/*
					
					*/
					
						// exit("Error:A");
					
				}elseif ($nmbank[$rek]=='Bank BNI') {
					#chequebni
					$pdf=new FPDF('L','mm',array(70,177));
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();


					$path="images/cek-bni.jpg";
					$pdf->Image($path,0,0,177,70);

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(128);
					$pdf->Cell(200,35,tglnmbln($tanggalh,'','L'),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(53);
					$pdf->Cell(200,48,$serah,0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(47);
					$pdf->Cell(200,60,ucwords(terbilang($jumlahht,'','')." Rupiah"),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(130);
					$pdf->Cell(200,71,number_format($jumlahht),0,1,'L');
					$pdf->Output();
				}elseif ($nmbank[$rek]=='Bank KALBAR') {
					#chequekalbar
					$pdf=new FPDF('L','mm',array(70,177));
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();


					$path="images/cek-kalbar.jpg";
					$pdf->Image($path,0,0,177,70);

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(128);
					$pdf->Cell(200,35,tglnmbln($tanggalh,'','L'),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(64);
					$pdf->Cell(200,48,$serah,0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(56);
					$pdf->Cell(200,60,ucwords(terbilang($jumlahht,'','')." Rupiah"),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(120);
					$pdf->Cell(200,71,number_format($jumlahht),0,1,'L');
					$pdf->Output();
				}


				
			}elseif ($cgttu=='Giro') {
				// echo $rek;
				if ($nmbank[$rek]=='Bank MANDIRI') {
					
					#chequemandiri
					$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$notransaksi."'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
						$namabankht=$bar['namabank'];
						$rekeningextht=$bar['rekeningext'];
						$anrekeningextht=$bar['anrekeningext'];
					
						if($anrekeningextht==''){
							$anrekeningextht=$bar['bayarkepada'];
						}
						
					
					$panjang='230';
					$setx='13';
					
					$pdf=new FPDF('L','mm',array(70,$panjang));
					
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();
					$height=6;
					$sizefont='8';
					$pdf->SetFont('Arial','',$sizefont);
					
					$pdf->SetY(0);
					$pdf->SetX($setx);
					// $pdf->Cell(160,$height,$novoucherht,0,0,'L');
					$pdf->MultiCell(24.5,3,$novoucherht);
					
					$pdf->SetXY(175,0);
					$pdf->Cell(50,$height,$wilayahkota.', '.tglnmbln($tanggalht,'','l'),0,1,'L');
					
					$pdf->Ln(1);
					$pdf->SetX($setx);
					$pdf->Cell(100,$height,tglnmbln($tanggalht,'','l'),0,0,'L');
					$pdf->Cell(200,$height,tglnmbln($tanggalht,'','l'),0,1,'L');
					
					// $pdf->Cell(200,$height,number_format($jumlahht),0,1,'L');
					$pdf->Ln(0);
					$pdf->SetX($setx);
					// $pdf->Cell(50,$height,$anrekeningextht,0,0,'L');
					$yawal=$pdf->GetY();					
					$pdf->MultiCell(30,4,$anrekeningextht);
					
					
					#= terbilang2
					$pdf->SetXY(60,$yawal);
					$pdf->MultiCell(150,$height-1,ucwords('                                                                                                                         '.terbilang($jumlahht,'','')." Rupiah"));
					
				
					
					
					
					$yawal=$pdf->GetY();
					$pdf->SetY($yawal-2);
					$pdf->SetFont('Arial','',$sizefont-0.5);
					$pdf->Cell(88,$height,'',0,0,'L');
					$pdf->Cell(50,$height,$rekeningextht,0,0,'L');
					$pdf->Cell(50,$height,$anrekeningextht,0,0,'L');
					$pdf->Cell(10,$height,$namabankht,0,1,'L');
					
					$pdf->Ln(1);
					$pdf->SetX($setx+12);
					$pdf->SetFont('Arial','',$sizefont);
					$pdf->Cell(100,$height,number_format($jumlahht),0,1,'L');
					
					#= sebelah terbilang
					$pdf->SetXY(100,12);
					$pdf->Cell(200,$height,number_format($jumlahht),0,1,'L');
					
					$pdf->Output();
					
					
				}elseif ($nmbank[$rek]=='Bank BNI') {
					#girobni
					$pdf=new FPDF('L','mm',array(70,177));
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();

					$path="images/giro-bni.jpg";
					$pdf->Image($path,0,0,177,70);

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(60);
					$pdf->Cell(200,38,tanggalnormal($tanggalht),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(129);
					$pdf->Cell(200,30,$tgl,0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(42);
					$pdf->Cell(150,50,number_format($jumlahht),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(82);
					$pdf->Cell(200,50,ucwords(terbilang($jumlahht,'','')." Rupiah"),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(40);
					$pdf->Cell(200,73,$norekeningsetup,0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(85);
					$pdf->Cell(200,73,$atasnamasetup,0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(145);
					$pdf->Cell(200,73,$cabang,0,1,'L');

					$pdf->Output();
				}	
			}elseif ($cgttu=='Transfer') {
				#transfer
				$pdf=new FPDF('L','mm',array(169,205));
				$pdf->SetAutoPageBreak(false);
				$pdf->AddPage();
				
				$path="images/slip-mandiri.jpg";
				$pdf->Image($path,0,0,205,169);

				$pdf->SetFont('Arial','',7);
				$pdf->SetY(0);
				$pdf->SetX(135);
				$pdf->Cell(200,105,$atasnamasetup,0,1,'L');

				$pdf->SetFont('Arial','',7);
				$pdf->SetY(0);
				$pdf->SetX(120);
				$pdf->Cell(200,222,ucwords(terbilang($jumlahht,'','')." Rupiah"),0,1,'L');

				$pdf->SetFont('Arial','',7);
				$pdf->SetY(0);
				$pdf->SetX(150);
				$pdf->Cell(200,210,number_format($jumlahht),0,1,'L');
				$pdf->Output();	
			}

	
	break;

	case'pdfnewall':
	
	
	
		$str = "select * from ".$dbname.".keu_kasbankht where nocek='".$nocekx."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$tanggalht=trim($bar['tanggal']);
			$novoucherht=trim($bar['novoucher']);
			$noakunht=trim($bar['noakun']);
			$rekeninght=trim($bar['rekening']);
			$cgttuht=trim($bar['cgttu']);
			$nocekht=trim($bar['nocek']);
			$jumlahht+=trim($bar['jumlah']);
			$tipetransaksiht=trim($bar['tipetransaksi']);
			$kodeorght=trim($bar['kodeorg']);
			$serahx=$bar['bayarkepada'];
			
			$namabankht=trim($bar['namabank']);
			$rekeningextht=trim($bar['rekeningext']);
			$anrekeningextht=trim($bar['anrekeningext']);
		}
			
			
			if($anrekeningextht!=''){
				$anrekeningextht=$anrekeningextht;
				$serahkiri=$anrekeningextht;
				$anrekeningextht.=' / ';
			}
			if($namabankht!=''){
				$namabankht=$namabankht.' / ';
			}
			if($rekeningextht!=''){
				$rekeningextht=$rekeningextht;
			}
			
			$anbankx = makeOption($dbname, 'log_5rekbank', 'rekening,an',"rekening='".$rekeningextht."'");
			$kdbankx = makeOption($dbname, 'log_5rekbank', 'rekening,idbank',"rekening='".$rekeningextht."'");
			$nmbankx = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');
			$serah=$anrekeningextht.$namabankht.$rekeningextht;
			
			if ($rekeningextht!='') {
					$tanpabank=$nmbankx[$kdbankx[$rekeningextht]];
					$tanpabank=str_replace("Bank ","",$tanpabank);
					$tanpabank=str_replace("BANK ","",$tanpabank);									
				$serah=$anbankx[$rekeningextht].' / '.$tanpabank.' / '.$rekeningextht;
			}
			
			$serahkiri=$anbankx[$rekeningextht];
			
			
			
			#= jika tidak ada supplier maka ambil bayarkepada
			
			if($serah==''){
				$serah=$serahx;
			}
		
			if($serahkiri==''){
				$serahkiri=$serahx;
			}
			
		
		
		$str = "select * from ".$dbname.".organisasi where kodeorganisasi='".$kodeorght."'";
		// echo $str;
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();		
				$wilayahkota=$bar['wilayahkota'];
				
		$str = "select * from ".$dbname.".keu_5akunbank where noakun='".$rekeninght."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$norekeningsetup=$bar['rekening'];
			$kodebanksetup=$bar['namabank'];
			$atasnamasetup=$bar['atasnama'];
			$cabang=$bar['cabang'];

			// $serah=$atasnamasetup."-".$norekeningsetup;
			
			
		#= data dt
		$str = "select * from ".$dbname.".keu_kasbankdt where notransaksi='".$notransaksi."' and kodesupplier!=''";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();			
			$kodesupplier=$bar['kodesupplier'];	

		#= supplier
		$str = "select * from ".$dbname.".log_5supplier where supplierid='".$kodesupplier."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();	
			$namasupplier=$bar['namasupplier'];		
	
			if ($cgttu=='Cheque') {
				if (strtoupper($nmbank[$rek])=='BANK MEGA') {
					#chequemandiri
					
					$setx='6';
					
					$pdf=new FPDF('P','mm',array(230,70));
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();
					$height=6;
					$sizefont='7.5';
					$pdf->SetFont('Arial','',$sizefont);
					
					$pdf->SetY(5);
					$pdf->SetX($setx-3);
					$pdf->MultiCell(24.5,3,$novoucherht);
					
					$pdf->SetXY(160,7);
					$pdf->Cell(50,$height,$wilayahkota.', '.tglnmbln($tanggalht,'','l'),0,1,'L');
					
					$pdf->Ln(2);
					$pdf->SetX($setx-3);
					$pdf->Cell(88,$height,tglnmbln($tanggalht,'','l'),0,0,'L');
					
					$sizefont='8.5';
					$pdf->SetFont('Arial','',$sizefont);
					$pdf->Cell(200,$height,$serah,0,1,'L');
					
					$sizefont='7.5';
					$pdf->SetFont('Arial','',$sizefont);
					
					$pdf->Ln(2);
					$pdf->SetX($setx-3);
					// $pdf->Cell(58,$height,$serahkiri,0,0,'L');
					$pdf->MultiCell(30,4,$serahkiri.'');
					
					// $pdf->SetXY(65,15);
					// $pdf->MultiCell(163,$height,ucwords('                                     '.$serahx));
					$pdf->SetXY(3,26);
					$pdf->MultiCell(163,$height,ucwords($serahx));

					// $pdf->SetX(55);
					$pdf->SetXY(65,24);
					$pdf->MultiCell(130,$height,ucwords('                                '.terbilang($jumlahht,'','')." Rupiah"));
					
					$sizefont='9';
					$pdf->SetFont('Arial','',$sizefont);
					$pdf->SetXY(2.9,30);
					$pdf->Cell(200,$height,number_format($jumlahht),0,1,'L');
				
					
					
					$pdf->SetXY(135,30);
					$pdf->Cell(50,$height,number_format($jumlahht),0,1,'L');
					
					ob_clean();
						$pdf->Output();
					/*
					
					*/
					
						// exit("Error:A");
					
				} else if (strtoupper($nmbank[$rek])=='BANK MANDIRI') {
					#chequemandiri
					
					$setx='6';
					
					$pdf=new FPDF('P','mm',array(230,70));
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();
					$height=6;
					$sizefont='7.5';
					$pdf->SetFont('Arial','',$sizefont);
					
					$pdf->SetY(5);
					$pdf->SetX($setx-3);
					$pdf->MultiCell(24.5,3,$novoucherht);
					
					$pdf->SetXY(160,7);
					$pdf->Cell(50,$height,$wilayahkota.', '.tglnmbln($tanggalht,'','l'),0,1,'L');
					
					$pdf->Ln(2);
					$pdf->SetX($setx-3);
					$pdf->Cell(88,$height,tglnmbln($tanggalht,'','l'),0,0,'L');
					$sizefont='8.5';
					$pdf->SetFont('Arial','',$sizefont);
					$pdf->Cell(200,$height,$serah,0,1,'L');
					
					$sizefont='7.5';
					$pdf->SetFont('Arial','',$sizefont);
					
					$pdf->Ln(2);
					$pdf->SetX($setx-3);
					// $pdf->Cell(58,$height,$serahkiri,0,0,'L');
					$pdf->MultiCell(30,4,$serahkiri);

					// $pdf->SetX(55);
					$pdf->SetXY(58,23);
					$pdf->MultiCell(140,$height,ucwords('                                '.terbilang($jumlahht,'','')." Rupiah"));
					
					$sizefont='9';
					$pdf->SetFont('Arial','',$sizefont);
					
					$pdf->SetXY(2.9,30);
					$pdf->Cell(200,$height,number_format($jumlahht),0,1,'L');
				
					
					
					$pdf->SetXY(175,29);
					$pdf->Cell(50,$height,number_format($jumlahht),0,1,'L');
					
					
						$pdf->Output();
					/*
					
					*/
					
						// exit("Error:A");
					
				}elseif (strtoupper($nmbank[$rek])=='BANK BNI') {
					#chequebni
					$pdf=new FPDF('L','mm',array(70,177));
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();


					// $path="images/cek-bni.jpg";
					// $pdf->Image($path,0,0,177,70);

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(128);
					$pdf->Cell(200,35,tglnmbln($tanggalh,'','L'),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(53);
					$pdf->Cell(200,48,$serah,0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(47);
					$pdf->Cell(200,60,ucwords(terbilang($jumlahht,'','')." Rupiah"),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(130);
					$pdf->Cell(200,71,number_format($jumlahht),0,1,'L');
					$pdf->Output();
				}elseif (strtoupper($nmbank[$rek])=='BANK KALBAR') {
					#chequekalbar
					$pdf=new FPDF('L','mm',array(70,177));
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();


					// $path="images/cek-kalbar.jpg";
					// $pdf->Image($path,0,0,177,70);

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(128);
					$pdf->Cell(200,35,tglnmbln($tanggalh,'','L'),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(64);
					$pdf->Cell(200,48,$serah,0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(56);
					$pdf->Cell(200,60,ucwords(terbilang($jumlahht,'','')." Rupiah"),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(120);
					$pdf->Cell(200,71,number_format($jumlahht),0,1,'L');
					$pdf->Output();
				}


				
			}elseif (strtoupper($cgttu)=='GIRO') {
				// echo $rek;
				if (strtoupper($nmbank[$rek])=='BANK MANDIRI') {
					
					#chequemandiri
					$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$notransaksi."'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
						$namabankht=$bar['namabank'];
						$rekeningextht=$bar['rekeningext'];
						$anrekeningextht=$bar['anrekeningext'];
						$nmbankxan = makeOption($dbname, 'log_5rekbank', 'rekening,an');
					
						if($anrekeningextht==''){
							// $anrekeningextht=$bar['bayarkepada'];
							$anrekeningextht=$nmbankxan[$rekeningextht];
						}
						
					
					$setx='5';
					
					$pdf=new FPDF('P','mm',array(230,70));
					
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();
					$height=6;
					$sizefont='7.9';
					$pdf->SetFont('Arial','',$sizefont);
					
					$pdf->SetY(5);
					$pdf->SetX($setx);
					// $pdf->Cell(160,$height,$novoucherht,0,0,'L');
					$pdf->MultiCell(24.5,3,$novoucherht);
					
					$pdf->SetXY(167,7);
					$pdf->Cell(50,$height+2,$wilayahkota.', '.tglnmbln($tanggalht,'','l'),0,1,'L');
					
					$pdf->SetXY(167,5);
					$pdf->Cell(50,$height+2,'',0,1,'L');
					$pdf->Ln(1);
					$pdf->SetX($setx);
					$pdf->Cell(100,$height+2,tglnmbln($tanggalht,'','l'),0,0,'L');
					$pdf->Cell(200,$height+2,tglnmbln($tanggalht,'','l'),0,1,'L');
					
					// $pdf->Cell(200,$height,number_format($jumlahht),0,1,'L');
					$pdf->Ln(0);
					$pdf->SetX($setx);
					// $pdf->Cell(50,$height,$anrekeningextht,0,0,'L');
					$yawal=$pdf->GetY();					
					$pdf->MultiCell(30,6,$anrekeningextht);
					
					
					#= terbilang2
					$pdf->SetXY(53,$yawal-1);
					$pdf->MultiCell(150,$height-2,ucwords('                                                                                                          '.terbilang($jumlahht,'','')." Rupiah"));
					
				
					
						$nmbankx2 = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');
						$nmbankx = makeOption($dbname, 'log_5rekbank', 'rekening,idbank');
					
					$yawal=$pdf->GetY();
					$yawal=29;
					$pdf->SetY($yawal-2+5);
					$pdf->SetFont('Arial','',$sizefont-0.5);
					$pdf->Cell(70,$height-5,'',0,0,'L');
					$pdf->Cell(30,$height-5,$rekeningextht,0,0,'L');
					$pdf->Cell(70,$height-5,$anrekeningextht,0,0,'C');
					//$pdf->Cell(10,$height-5,$namabankht,0,1,'L');
					$tanpabank=$nmbankx2[$nmbankx[$rekeningextht]];
					$tanpabank=str_replace("Bank ","",$tanpabank);
					$tanpabank=str_replace("BANK ","",$tanpabank);					
					$pdf->Cell(10,$height-5,$tanpabank,0,1,'L');
					
					$pdf->Ln(4);
					$pdf->SetX($setx+5);
					
					$sizefont='10';
					$pdf->SetFont('Arial','',$sizefont);
					
					// $pdf->SetFont('Arial','',$sizefont);
					$pdf->Cell(100,$height,number_format($jumlahht),0,1,'L');
					
					#= sebelah terbilang
					$pdf->SetXY(93,12+4);
					$pdf->Cell(200,$height+8,number_format($jumlahht),0,1,'L');
					
					$pdf->Output();
				}  else if (strtoupper($nmbank[$rek])=='BANK MEGA') {
					
					#chequemandiri
					$str = "select * from ".$dbname.".keu_kasbankht where notransaksi='".$notransaksi."'";
					$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$res->setFetchMode(PDO::FETCH_ASSOC);
					$bar=$res->fetch();
						$namabankht=$bar['namabank'];
						$rekeningextht=$bar['rekeningext'];
						$anrekeningextht=$bar['anrekeningext'];
						$nmbankx2 = makeOption($dbname, 'keu_5daftarbank', 'kodebank,namabank');
						$nmbankx = makeOption($dbname, 'log_5rekbank', 'rekening,idbank');
						$nmbankxan = makeOption($dbname, 'log_5rekbank', 'rekening,an');
					
						if($anrekeningextht==''){
							// $anrekeningextht=$bar['bayarkepada'];
							$anrekeningextht=$nmbankxan[$rekeningextht];
						}
						
					
					$setx='5';
					
					$pdf=new FPDF('P','mm',array(230,70));
					
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();
					$height=6;
					$sizefont='7';
					$pdf->SetFont('Arial','',$sizefont);
					
					$pdf->SetY(5);
					$pdf->SetX($setx);
					// $pdf->Cell(160,$height,$novoucherht,0,0,'L');
					$pdf->MultiCell(24.5,3,$novoucherht);
					
					$pdf->SetXY(167,8);
					$pdf->Cell(50,$height+2,$wilayahkota.', '.tglnmbln($tanggalht,'','l'),0,1,'L');
					
					$pdf->Ln(0);
					$pdf->SetX($setx);
					$pdf->Cell(100,$height+2,tglnmbln($tanggalht,'','l'),0,0,'L');
					$pdf->Cell(200,$height+2,tglnmbln($tanggalht,'','l'),0,1,'L');
					
					// $pdf->Cell(200,$height,number_format($jumlahht),0,1,'L');
					$pdf->Ln(0);
					$pdf->SetX($setx);
					// $pdf->Cell(50,$height,$anrekeningextht,0,0,'L');
					$yawal=$pdf->GetY();					
					$pdf->MultiCell(19,4,$anrekeningextht);
					
					
					#= terbilang2
					$pdf->SetXY(133,$yawal+1);
					$sizefont='10';
					$pdf->SetFont('Arial','',$sizefont);
					$pdf->MultiCell(150,$height-3,number_format($jumlahht));
					
				// $pdf->MultiCell(150,$height-3,ucwords('                                                                                                          '.terbilang($jumlahht,'','')." Rupiah"));
					
					
					
					// $yawal=$pdf->GetY();
					// $pdf->SetY($yawal-2+5);
					$sizefont='7';
					$pdf->SetFont('Arial','',$sizefont);
					$pdf->Ln(0);
						$pdf->Cell(200,$height+2,ucwords('                                                           '.terbilang($jumlahht,'','')." Rupiah"),0,1,'L');
					$pdf->Ln(0);
					
					$pdf->SetFont('Arial','',$sizefont-0.5);
					$sizefont='10';
					$pdf->SetFont('Arial','',$sizefont);
					$pdf->Cell(70,$height-5,number_format($jumlahht),0,0,'L');
					
					$sizefont='7';
					$pdf->SetFont('Arial','',$sizefont);
					
					$pdf->Cell(40,$height-5,$rekeningextht,0,0,'L');
					$pdf->Cell(50,$height-5,$anrekeningextht,0,0,'L');
					$tanpabank=$nmbankx2[$nmbankx[$rekeningextht]];
					$tanpabank=str_replace("Bank ","",$tanpabank);
					$tanpabank=str_replace("BANK ","",$tanpabank);
					$pdf->Cell(10,$height-5,$tanpabank,0,1,'L');
					//$pdf->Cell(10,$height-5,$namabankht,0,1,'L');
					
					// $pdf->Ln(1);
					// $pdf->SetX($setx+5);
					// $pdf->SetFont('Arial','',$sizefont);
					// $pdf->Cell(100,$height,,0,1,'L');
					
					#= sebelah terbilang
					// $pdf->SetXY(93,12+5);
					// $pdf->Cell(200,$height+8,number_format($jumlahht),0,1,'L');
					
					$pdf->Output();
					
				}elseif (strtoupper($nmbank[$rek])=='BANK BNI') {
					#girobni
					$pdf=new FPDF('L','mm',array(70,177));
					$pdf->SetAutoPageBreak(false);
					$pdf->AddPage();

					// $path="images/giro-bni.jpg";
					// $pdf->Image($path,0,0,177,70);

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(60);
					$pdf->Cell(200,38,tanggalnormal($tanggalht),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(129);
					$pdf->Cell(200,30,$tgl,0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(42);
					$pdf->Cell(150,50,number_format($jumlahht),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(82);
					$pdf->Cell(200,50,ucwords(terbilang($jumlahht,'','')." Rupiah"),0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(40);
					$pdf->Cell(200,73,$norekeningsetup,0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(85);
					$pdf->Cell(200,73,$atasnamasetup,0,1,'L');

					$pdf->SetFont('Arial','',7);
					$pdf->SetY(0);
					$pdf->SetX(145);
					$pdf->Cell(200,73,$cabang,0,1,'L');

					$pdf->Output();
				}	
			}elseif (strtoupper($cgttu)=='TRANSFER') {
				#transfer
				$pdf=new FPDF('L','mm',array(169,205));
				$pdf->SetAutoPageBreak(false);
				$pdf->AddPage();
				
				$path="images/slip-mandiri.jpg";
				$pdf->Image($path,0,0,205,169);

				$pdf->SetFont('Arial','',7);
				$pdf->SetY(0);
				$pdf->SetX(135);
				$pdf->Cell(200,105,$atasnamasetup,0,1,'L');

				$pdf->SetFont('Arial','',7);
				$pdf->SetY(0);
				$pdf->SetX(120);
				$pdf->Cell(200,222,ucwords(terbilang($jumlahht,'','')." Rupiah"),0,1,'L');

				$pdf->SetFont('Arial','',7);
				$pdf->SetY(0);
				$pdf->SetX(150);
				$pdf->Cell(200,210,number_format($jumlahht),0,1,'L');
				$pdf->Output();	
			}else{ 
				#kas
				exit("<label hidden>Warning</label> Print Bayar tampil jika memakai Akun Bank");
			}

	
	break;
	
    default:
    break;
}
?>